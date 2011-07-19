#!/usr/bin/ruby
#
# CGI-wrapper for philesight

require 'philesight'
require 'cgi'
require 'digest/md5'

##############################################################################
# Config variables
##############################################################################

$path_db = "/tmp/ps.db"
$img_size = 800
$img_rings = 4
$img_gradients = true
$show_list = true

# Uncomment the following lines to enable caching. Make sure the $path_cache
# directory is writable by the httpd user

# $path_cache = "/tmp/philesight"
# $cache_maxage = 60

##############################################################################
# End of configuration
##############################################################################


class PhilesightCGI

	def initialize()
		
		# Create philesight object and open database
		
		@ps = Philesight.new($img_rings, $img_size, $img_gradients)
		@ps.db_open($path_db)

		# Get parameters from environment and CGI. 

		cgi = CGI.new;
		cmd = cgi.params['cmd'][0]
		path = cgi.params['path'][0] || @ps.prop_get("root") || "/"

		# ISMAP image maps do not return a proper CGI parameter, but only the
		# coordinates appended after a question mark. If this is found in the
		# QUERY_STRING, assume the 'find' command

		qs = ENV["QUERY_STRING"]
		if(qs && qs =~ /\?(\d+,\d+)/ ) then
			find_pos = $1
			cmd = 'find'
		end

		# Check if the cache directory is given and writable

		if $path_cache
			stat = File.lstat($path_cache)
			if stat.directory? and stat.writable? then
				@do_caching = true
			end
		end

		# Perform action depending on 'cmd' parameter

		case cmd

			when "img" 
				do_img(path)

			when "find"
				if(find_pos =~  /(\d+),(\d+)/) then
					do_find(path, $1.to_i, $2.to_i)
				end

			else 
				do_show(path)

		end
	end


	#
	# Generate PNG image for given path
	#

	def do_img(path)
		puts "Content-type: image/png"
		puts "Cache-Control: no-cache, must-revalidate"
		puts "Expires: Sat, 26 Jul 1997 05:00:00 GMT"
		puts
		$stdout.flush

		if @do_caching then
			
			# First check if the image is in cache and fresh enough. If not,
			# generate new image

			now = Time.now()
			fname_img = $path_cache + "/cache-" + Digest::MD5.hexdigest(path)

			if ! File.readable?(fname_img) then
				@ps.draw(path, fname_img)
			else
				stat = File.lstat(fname_img)
				if now > stat.mtime + $cache_maxage then
					@ps.draw(path, fname_img)
				end
			end

			# Return the cached image

			fd = File.open(fname_img)
			while true do
				b = fd.read(4096)
				if b then
					print(b)
				else
					break
				end
			end
			fd.close

			# Clean all images in the cache directory older then $cache_maxage
			# seconds. Make sure only to cleanup files matching the name 'cache-'
			# followed by 32 chars (MD5 sum)

			Dir.foreach($path_cache) do |f|
				print(f)
				if f =~ /^cache-.{32}$/ then
					f_full = $path_cache + "/" + f
					stat = File.lstat(f_full)
					if now > stat.mtime + $cache_maxage then
						File.unlink(f_full)
					end
				end
			end

		else

			# Not caching, generate the PNG and send to stdout right away
			@ps.draw(path, "-")

		end
	end


	#
	# Find the path belonging to the ring and segment the user clicked
	# 

	def do_find(path, x, y)
		url = "?path=%s" % CGI.escape(@ps.find(path, x, y))
		puts "Content-type: text/html"
		puts "Cache-Control: no-cache, must-revalidate"
		puts "Expires: Sat, 26 Jul 1997 05:00:00 GMT"
		puts
		puts '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'
		puts '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" >'
		puts '<head>'
		puts '	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />'
		puts '	<meta http-equiv="refresh" content="0; url=' + "#{url}" + '">'
		puts '</head>'
		puts '<body></body>'
		puts '</html>'
	end


	#
	# Generate HTML page with list and graph
	#
	
	def do_show(path)
		random = ""
		puts "Content-type: text/html"
		puts "Cache-Control: no-cache, must-revalidate"
		puts "Expires: Sat, 26 Jul 1997 05:00:00 GMT"
		puts
		puts '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'
		puts '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" >'
		puts '<head>'
		puts '	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />'
		puts "	<title>Disk usage : #{path}</title>"
		puts '	<style type="text/css">'
		puts '	<!--'
		puts '		body {color:black;text-align:center;background:#FAFAFA;}'
		puts '		table {margin:auto;width:780px;}'
		puts '		table,td {border:0;}'
		puts '		td {padding:4px;text-align:left;}'
		puts '		td.size {text-align:right;}'
		puts '		thead td {font-weight:bold;border-bottom:1px solid black;background:#EEE;}'
		puts '		tbody td {background:#F0F0F0;}'
		puts '		tbody tr.parentdir td {background:#E5D0D0;}'
		puts '		tbody tr.evenrow td {background:#E4E4E4;}'
		puts '		'
		puts '	-->'
		puts '	</style>'
		puts '</head>'
		puts '<body>'
		puts '	<p><a href="' + "?path=" + CGI.escape(path) + "&amp;" + '">'
		puts '		<img style="border:0" width="' + $img_size.to_s + '" height="' + $img_size.to_s + '" src="?cmd=img&path=' + CGI.escape(path) + '" ismap="ismap" alt="' + "#{path}" + '" />'
		puts '	</a></p>'
	
		if $show_list then
			# Array of files
			content = @ps.listcontent(path)
			if(content && content[0]) then
				puts '	<table summary="File lists">'
				puts '		<thead>'
				puts '			<tr><td>Filename</td><td class="size">Size</td></tr>'
				puts '		</thead>'
				puts '		<tbody>'
				puts '			<tr class="parentdir"><td>' + content[0][:path].to_s + '</td><td class="size">' + content[0][:humansize].to_s + '</td></tr>'

				if(content[1].size > 0) then
					linenum = 0

					content[1] = content[1].sort_by { |f| - f[:size] }
					content[1].each do |f|
						if(linenum%2 == 0) then
							print '			<tr class="evenrow">'
						else
							print '			<tr>'
						end

						puts '<td><a href="?path='+ CGI.escape(f[:path].to_s) +'">' + f[:path].to_s + '</a></td><td class="size">' + f[:humansize].to_s + '</td></tr>'

						linenum += 1
					end
				end
				puts '		</tbody>'
				puts '	</table>'
			end
		end

		puts '</body>'
		puts '</html>'
	end

end


philesightcgi = PhilesightCGI.new

#
# vi: ts=4 sw=4
#

