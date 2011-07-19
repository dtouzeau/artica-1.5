#!/usr/bin/ruby
# vi: ts=2 sw=2

require 'getoptlong'
require 'cgi'
require 'cairo'
require 'bdb'

class PNGWriter

	def initialize(fname)
		if fname != "-" then
			@fd = File.open(fname, "w")
		end
	end

	def write(data)
		if @fd then
			@fd.write(data)
		else
			print(data)
		end
		return data.length
	end
end


class Philesight

	def initialize(ringcount=4, size=640, use_gradient=true)
		@max_files_per_dir = 50
		@w = size
		@h = size
		@cx = @w / 2
		@cy = @h / 2
		@ringcount = ringcount
		@ringwidth = ((size-50)/2) / (ringcount+1) 
		@use_gradient = use_gradient
		@find_a = 0
		@find_r = 0
	end


	#
	# Open the database. Try read-write mode first, if this fails, re-open readonly
	#

	def db_open(fname)
		begin
			@db = BDB::Btree.open fname , nil, BDB::CREATE, 0644, "set_pagesize" => 1024, "set_cachesize" => [0, 32*1024,0]
		rescue
			@db = BDB::Btree.open fname , nil, BDB::RDONLY, 0644, "set_pagesize" => 1024, "set_cachesize" => [0, 32*1024,0]
		end
	end


	#
	# Dump database in human-readable form
	#

	def dump
		@db.keys.each do |f|
			puts "%s %s" % [ f, Marshal::load(@db[f]).inspect ]
		end
	end


	#
	# Concatenate a directory and a filename
	#

	def addpath(a, b)
		return a + b if(a =~ /\/$/)
		return a + "/" + b
	end


	# 
	# Set property in DB
	#

	def prop_set(key, val)
		@db["_prop_" + key] = val
	end


	# 
	# Get DB properry
	#

	def prop_get(key)
		@db["_prop_" + key]
	end


	#
	# Read a directory and add to the database; this function is recursive
	# for sub-directories
	#

	def readdir(dir,skip)

		size_file = {}
		size_dir = {}
		size_total = 0

		# Traverse the directory and collect the size of all files and
		# directories

		# STDERR.puts "Need to skip: #{dir}/#{skip}"
		begin
			Dir.foreach(dir) do |f|

				if(f != "." && f != "..") then

					if (f == skip) then
						STDERR.puts " skipping #{dir}/#{skip}"
						next
					end

					f_full = addpath(dir, f)
					stat = File.lstat(f_full)

					if (@start_dev and stat.dev != @start_dev) then
						STDERR.puts " skipping %s/%s" % [ dir, f ]
						next
					end

					if(!stat.symlink?) then

						if(stat.file?) then
							size = File.size(f_full) 
							size_file[f] = size
							size_total += size
						end

						if(stat.directory?) then
							size = readdir(f_full,skip) 
							if(size > 0) then
								size_dir[f] = size
								size_total += size
							end
						end
					end
				end  
			end
		rescue SystemCallError => errmsg
			puts errmsg
		end

		# If there are a lot of small files in this directory, group
		# the smallest into one entry to avoid clutter

		if(size_file.keys.length > @max_files_per_dir) then
			list = size_file.keys.select { |f| size_file[f] < size_total / @max_files_per_dir }
			rest = 0
			list.each do |f|
				rest += size_file[f]
				size_file.delete(f)
			end
			size_file['rest'] = rest
		end

		# Store the files in the database

		size_file.keys.each do |f|
			f_full = addpath(dir, f)
			@db[f_full] = Marshal::dump( [ size_file[f], [] ] )
		end

		# Store this directory with the list of children in the database

		size = size_dir.merge(size_file)
		children = size.keys.sort
		@db[dir] = Marshal::dump( [ size_total, children ] )
		return size_total
	end


	#
	# Index from given path
	#

	def index(path, skip, one_fs)
		if(one_fs) then
			stat = File.lstat(path)
			@start_dev = stat.dev
		end
		prop_set("root", path)
		readdir(path,skip)
	end


	#
	# Draw one section
	# 

	def draw_section(cr, ang_from, ang_to, r_from, r_to, brightness)

		ang_to, ang_from = ang_from, ang_to if (ang_to < ang_from)

		if(brightness > 0) then
			r, g, b = hsv2rgb((ang_from+ang_to)*0.5 / (Math::PI*2), 1.0-brightness, brightness/2+0.5)
		else
			r, g, b = 0.9, 0.9, 0.9
		end

		# Instead of using the r_from and r_to for the radial pattern, the stops
		# are calculated. This is to work around a bug in cairo

		r_total = ((@w - 50) / 2).to_f
		pat = Cairo::RadialPattern.new(@cx, @cy, 0, @cx, @cy, r_total)
		if @use_gradient then
			pat.add_color_stop_rgb(r_from / r_total, r*0.8, g*0.8, b*0.8)
			pat.add_color_stop_rgb(r_to / r_total, r*1.5, g*1.5, b*1.5)
		else
			pat.add_color_stop_rgb(0, r, g, b)
		end

		cr.new_path
		cr.arc(@cx, @cy, r_from, ang_from, ang_to)
		cr.arc_negative(@cx, @cy, r_to, ang_to, ang_from)
		cr.close_path

		cr.set_source(pat)
		cr.fill_preserve
	end


	#
	# Draw ring. This function is recursive for the outer rings
	# 

	def draw_ring(cr, level, ang_min, ang_max, path)

		ang_from = ang_min
		ang_to = ang_min
		ang_range = (ang_max - ang_min).to_f
		r_from = level * @ringwidth
		r_to   = r_from + @ringwidth

		unless(@db[path]) then
			return "/"
		end

		total_path, child_path = Marshal::load( @db[path] )

		# Draw a section proportional to the size of each file or subdir

		child_path.each do |f|

			f_full = addpath(path, f)
			total_f, child_f = Marshal::load( @db[f_full] )

			# Calculate start and end angles and draw section

			ang_from = ang_to
			ang_to += ang_range * total_f / total_path if(total_path > 0)
			brightness = r_from.to_f / @cx
			brightness = 0 if(f == 'rest')
			draw_section(cr, ang_from, ang_to, r_from, r_to, brightness) if(cr)

			# If we are looking for the path of an (x,y) pair, check if this section matches

			if( (@find_a >= ang_from) && (@find_a <= ang_to) && (@find_r >= r_from) && (@find_r <= r_to) ) then
				@find_path = f_full
			end

			# Draw outer rings

			if(level < @ringcount) then
				draw_ring(cr, level+1, ang_from, ang_to, f_full)
			else
				draw_section(cr, ang_from, ang_to, r_to, r_to+5, 0.5) if(cr && child_f.nitems > 0)
			end

			# Generate and save labels of filenames/sizes

			if(cr && (ang_to - ang_from > 0.2)) then
				size = filesize_readable(total_f)
				x, y = pol2xy((ang_from+ang_to)/2, (r_from+r_to)/2)
				label = {}
				label[:x] = x 
				label[:y] = y
				label[:text] = "%s\n%s" % [ f, size]
				@labels << label
			end
	end
	end


	#
	# Draw graph of the given path
	#

	def draw(path, fname)

		# Create drawing context, white background

		format = Cairo::FORMAT_ARGB32
		surf = Cairo::ImageSurface.new(format, @w, @h)
		cr = Cairo::Context.new(surf)
		writer = PNGWriter.new(fname)

		# Draw top level filename and size

		unless(@db[path]) then
			draw_text(cr, @cx, @cy, "Path '#{path}' not found in database", 12)
			cr.target.write_to_png(writer)
			return
		end

		total_path, child_path = Marshal::load( @db[path] )
		draw_text(cr, @cx, 10,  "%s (%s)" % [ path, filesize_readable(total_path) ], 14, true)
		draw_text(cr, @cx, @cy, "cd ..", 14, true)

		# Draw rings, recursively

		@labels = []
		draw_ring(cr, 1, 0, Math::PI*2, path)

		# Draw circles on ring borders

		cr.set_source_rgba(0, 0, 0, 0.7) 
		0.upto(@ringcount+1) do | level |
			cr.new_path
		cr.set_line_width(0.3)
		cr.arc(@cx, @cy, level * @ringwidth, 0, 2*Math::PI)
		cr.stroke
		end

		# Draw labels on top of graph

		@labels.each do |label|

			cr.select_font_face("Sans", Cairo::FONT_SLANT_NORMAL, Cairo::FONT_WEIGHT_NORMAL)
			cr.set_font_size(9)

			# Draw text 4 times in a dark color, one time white on top

			[[-1, 0, 0.2], [+1, 0, 0.2], [0, -1, 0.2], [0, +1, 0.2], [0, 0, 0.9]].each do |dx, dy, color|
				cr.set_source_rgba(color, color, color, 1.0)
				draw_text(cr, label[:x]+dx, label[:y]+dy, label[:text])
			end

		end

		# Generate PNG file

		cr.target.write_to_png(writer)
	end


	#
	# List files/dir of the given path
	#

	def listcontent(path)

		unless(@db[path]) then
			return nil
		end

		dircontent = []

		total_path, child_path = Marshal::load( @db[path] )

		currentdir = {}
		currentdir[:path] = path
		currentdir[:humansize] = filesize_readable(total_path)

		child_path.each do |f|
			f_full = addpath(path, f)
			total_f, child_f = Marshal::load( @db[f_full] )

			fileinfo = {}
			fileinfo[:path] = f_full
			fileinfo[:size] = Integer(total_f)
			fileinfo[:humansize] = filesize_readable(total_f)
			dircontent << fileinfo
		end

		return [currentdir, dircontent]
	end


	# 
	# Find the path belonging to a (x,y) position in the graph
	# 

	def find(path, x, y)
		@find_a, @find_r = xy2pol(x, y)
		@find_path = File.dirname(path)
		draw_ring(nil, 1, 0, Math::PI*2, path)
		@find_path
	end


	private


	#
	# Draw text on pos x,y
	#

	def draw_text(cr, x, y, text, size=11, bold=false)

		lines = text.count("\n") + 1
		y -= (lines-1) * (size+2) / 2.0

		cr.select_font_face("Sans", Cairo::FONT_SLANT_NORMAL, bold ? Cairo::FONT_WEIGHT_BOLD : Cairo::FONT_WEIGHT_NORMAL)
		cr.set_font_size(size)

		text.split("\n").each do |line|
			extents = cr.text_extents(line)
			w = extents.width
			h = extents.height
			cr.move_to(x - w/2, y + h/2)
			cr.show_text(line)
			y += size+2
		end
	end


	# 
	# convert color from (h,s,v) to (r,g,b) colorspace
	#

	def hsv2rgb(h, s, v)

		h = h.to_f
		s = s.to_f
		v = v.to_f

		h *= 6.0
		i = h.floor
		f = h - i
		f = 1-f if ((i & 1) == 0) 
		m = v * (1 - s)
		n = v * (1 - s * f)
		i=0 if(i<0) 
		i=6 if(i>6)

		case i
		when 0, 6 then 	r=v; g=n; b=m
		when 1 then 	r=n; g=v; b=m
		when 2 then 	r=m; g=v; b=n
		when 3 then 	r=m; g=n; b=v
		when 4 then 	r=n; g=m; b=v
		when 5 then 	r=v; g=m; b=n
		end

		[r, g, b]
	end


	# 
	# Convert polair (ang,radius) coordinate to cartesian (x,y)
	#

	def pol2xy(a, r)
		x = Math.cos(a) * r + @cx
		y = Math.sin(a) * r + @cy
		[x, y]
	end

	# 
	# Convert cartesian (x,y) coordinate to polair (ang, radius)
	#

	def xy2pol(x, y)
		x -= @cx;
		y -= @cy;
		a = Math.atan2(y, x)
		a += 2*Math::PI if(a<0)
		r = Math.sqrt(x*x + y*y)
		[a, r]
	end


	#
	# Convert a filesize in bytes to a human readable form
	#

	def filesize_readable(size)
		if(size > 1024*1024*1024) then
			return "%.1fG" % (size / (1024.0*1024*1024))
		elsif(size > 1024*1024) then
			return "%.1fM" % (size / (1024.0*1024))
		elsif(size > 1024) then
			return "%.1fK" % (size / (1024.0))
		end	
		size
	end

end

# 
# End
# 
