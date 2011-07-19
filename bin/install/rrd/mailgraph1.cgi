#!/usr/bin/perl

# mailgraph -- a postfix statistics rrdtool frontend
# copyright (c) 2000-2005 David Schweikert <dws@ee.ethz.ch>
# released under the GNU General Public License

use RRDs;
use POSIX qw(uname);

my $VERSION = "1.12.01";

my $host = (POSIX::uname())[1];
my $scriptname = 'mailgraph1.cgi';
my $xpoints = 540;
my $points_per_sample = 3;
my $ypoints = 160;
my $ypoints_err = 96;
my $rrd ="/var/lib/mailgraph/mailgraph.rrd";
my $rrd_virus = '/var/lib/mailgraph/mailgraph_virus.rrd';
my $tmp_dir = '/opt/artica/share/www/mailgraph';

my @graphs = (
	{ title => 'Day Graphs',   seconds => 3600*24,        },
	{ title => 'Week Graphs',  seconds => 3600*24*7,      },
	{ title => 'Month Graphs', seconds => 3600*24*31,     },
	{ title => 'Year Graphs',  seconds => 3600*24*365, },
);

my %color = (
	sent     => '000099', # rrggbb in hex
	received => '009900',
	rejected => 'AA0000', 
	bounced  => '000000',
	virus    => 'DDBB00',
	spam     => '999999',
);

sub rrd_graph(@)
{
	my ($range, $file, $ypoints, @rrdargs) = @_;
	my $step = $range*$points_per_sample/$xpoints;
	# choose carefully the end otherwise rrd will maybe pick the wrong RRA:
	my $end  = time; $end -= $end % $step;
	my $date = localtime(time);
	$date =~ s|:|\\:|g unless $RRDs::VERSION < 1.199908;

	my ($graphret,$xs,$ys) = RRDs::graph($file,
		'--imgformat', 'PNG',
		'--width', $xpoints,
		'--height', $ypoints,
		'--start', "-$range",
		'--end', $end,
		'--vertical-label', 'msgs/min',
		'--lower-limit', 0,
		'--units-exponent', 0, # don't show milli-messages/s
		'--lazy',
		'--color', 'SHADEA#ffffff',
		'--color', 'SHADEB#ffffff',
		'--color', 'BACK#ffffff',

		$RRDs::VERSION < 1.2002 ? () : (
			'--slope-mode'
		),

		@rrdargs,

		'COMMENT:['.$date.']\r',
	);

	my $ERR=RRDs::error;
	die "ERROR: $ERR\n" if $ERR;
}

sub graph($$)
{
	my ($range, $file) = @_;
	my $step = $range*$points_per_sample/$xpoints;
	rrd_graph($range, $file, $ypoints,
		"DEF:sent=$rrd:sent:AVERAGE",
		"DEF:msent=$rrd:sent:MAX",
		"CDEF:rsent=sent,60,*",
		"CDEF:rmsent=msent,60,*",
		"CDEF:dsent=sent,UN,0,sent,IF,$step,*",
		"CDEF:ssent=PREV,UN,dsent,PREV,IF,dsent,+",
		"AREA:rsent#$color{sent}:Sent    ",
		'GPRINT:ssent:MAX:total\: %8.0lf msgs',
		'GPRINT:rsent:AVERAGE:avg\: %5.2lf msgs/min',
		'GPRINT:rmsent:MAX:max\: %4.0lf msgs/min\l',

		"DEF:recv=$rrd:recv:AVERAGE",
		"DEF:mrecv=$rrd:recv:MAX",
		"CDEF:rrecv=recv,60,*",
		"CDEF:rmrecv=mrecv,60,*",
		"CDEF:drecv=recv,UN,0,recv,IF,$step,*",
		"CDEF:srecv=PREV,UN,drecv,PREV,IF,drecv,+",
		"LINE2:rrecv#$color{received}:Received",
		'GPRINT:srecv:MAX:total\: %8.0lf msgs',
		'GPRINT:rrecv:AVERAGE:avg\: %5.2lf msgs/min',
		'GPRINT:rmrecv:MAX:max\: %4.0lf msgs/min\l',
	);
}

sub graph_err($$)
{
	my ($range, $file) = @_;
	my $step = $range*$points_per_sample/$xpoints;
	rrd_graph($range, $file, $ypoints_err,
		"DEF:rejected=$rrd:rejected:AVERAGE",
		"DEF:mrejected=$rrd:rejected:MAX",
		"CDEF:rrejected=rejected,60,*",
		"CDEF:drejected=rejected,UN,0,rejected,IF,$step,*",
		"CDEF:srejected=PREV,UN,drejected,PREV,IF,drejected,+",
		"CDEF:rmrejected=mrejected,60,*",
		"LINE2:rrejected#$color{rejected}:Rejected",
		'GPRINT:srejected:MAX:total\: %8.0lf msgs',
		'GPRINT:rrejected:AVERAGE:avg\: %5.2lf msgs/min',
		'GPRINT:rmrejected:MAX:max\: %4.0lf msgs/min\l',

		"DEF:bounced=$rrd:bounced:AVERAGE",
		"DEF:mbounced=$rrd:bounced:MAX",
		"CDEF:rbounced=bounced,60,*",
		"CDEF:dbounced=bounced,UN,0,bounced,IF,$step,*",
		"CDEF:sbounced=PREV,UN,dbounced,PREV,IF,dbounced,+",
		"CDEF:rmbounced=mbounced,60,*",
		"AREA:rbounced#$color{bounced}:Bounced ",
		'GPRINT:sbounced:MAX:total\: %8.0lf msgs',
		'GPRINT:rbounced:AVERAGE:avg\: %5.2lf msgs/min',
		'GPRINT:rmbounced:MAX:max\: %4.0lf msgs/min\l',

		"DEF:virus=$rrd_virus:virus:AVERAGE",
		"DEF:mvirus=$rrd_virus:virus:MAX",
		"CDEF:rvirus=virus,60,*",
		"CDEF:dvirus=virus,UN,0,virus,IF,$step,*",
		"CDEF:svirus=PREV,UN,dvirus,PREV,IF,dvirus,+",
		"CDEF:rmvirus=mvirus,60,*",
		"STACK:rvirus#$color{virus}:Viruses ",
		'GPRINT:svirus:MAX:total\: %8.0lf msgs',
		'GPRINT:rvirus:AVERAGE:avg\: %5.2lf msgs/min',
		'GPRINT:rmvirus:MAX:max\: %4.0lf msgs/min\l',

		"DEF:spam=$rrd_virus:spam:AVERAGE",
		"DEF:mspam=$rrd_virus:spam:MAX",
		"CDEF:rspam=spam,60,*",
		"CDEF:dspam=spam,UN,0,spam,IF,$step,*",
		"CDEF:sspam=PREV,UN,dspam,PREV,IF,dspam,+",
		"CDEF:rmspam=mspam,60,*",
		"STACK:rspam#$color{spam}:Spam    ",
		'GPRINT:sspam:MAX:total\: %8.0lf msgs',
		'GPRINT:rspam:AVERAGE:avg\: %5.2lf msgs/min',
		'GPRINT:rmspam:MAX:max\: %4.0lf msgs/min\l',
	);
}




sub main()
{

if(!-e $rrd){
	exit(0);
}

my $uri = '0-n';
	$uri =~ s/\/[^\/]+$//;
	$uri =~ s/\//,/g;
	$uri =~ s/(\~|\%7E)/tilde,/g;
	mkdir $tmp_dir, 0777 unless -d $tmp_dir;
	mkdir "$tmp_dir", 0777 unless -d "$tmp_dir";

	my $img = '0-n';
	if(defined $img and $img =~ /\S/) {
		if($img =~ /^(\d+)-n$/) {
			my $file = "$tmp_dir/mailgraph_$1.png";
			graph($graphs[$1]{seconds}, $file);
		}
		elsif($img =~ /^(\d+)-e$/) {
			my $file = "$tmp_dir/mailgraph_$1_err.png";
			graph_err($graphs[$1]{seconds}, $file);
		}
		else {
			die "ERROR: invalid argument\n";
		}
	}
main1();
main2();
main3();
main4();
main5();
}



sub main1()
{
	my $uri = '0-e';
	$uri =~ s/\/[^\/]+$//;
	$uri =~ s/\//,/g;
	$uri =~ s/(\~|\%7E)/tilde,/g;
	mkdir $tmp_dir, 0777 unless -d $tmp_dir;
	mkdir "$tmp_dir", 0777 unless -d "$tmp_dir";

	my $img = '0-e';
	if(defined $img and $img =~ /\S/) {
		if($img =~ /^(\d+)-n$/) {
			my $file = "$tmp_dir/mailgraph_$1.png";
			graph($graphs[$1]{seconds}, $file);
		}
		elsif($img =~ /^(\d+)-e$/) {
			my $file = "$tmp_dir/mailgraph_$1_err.png";
			graph_err($graphs[$1]{seconds}, $file);
		}
		else {
			die "ERROR: invalid argument\n";
		}
	}
}

sub main2()
{
	my $uri = '1-n';
	$uri =~ s/\/[^\/]+$//;
	$uri =~ s/\//,/g;
	$uri =~ s/(\~|\%7E)/tilde,/g;
	mkdir $tmp_dir, 0777 unless -d $tmp_dir;
	mkdir "$tmp_dir", 0777 unless -d "$tmp_dir";

	my $img = '1-n';
	if(defined $img and $img =~ /\S/) {
		if($img =~ /^(\d+)-n$/) {
			my $file = "$tmp_dir/mailgraph_$1.png";
			graph($graphs[$1]{seconds}, $file);
		}
		elsif($img =~ /^(\d+)-e$/) {
			my $file = "$tmp_dir/mailgraph_$1_err.png";
			graph_err($graphs[$1]{seconds}, $file);
		}
		else {
			die "ERROR: invalid argument\n";
		}
	}
}

sub main4()
{
	my $uri = '2-n';
	$uri =~ s/\/[^\/]+$//;
	$uri =~ s/\//,/g;
	$uri =~ s/(\~|\%7E)/tilde,/g;
	mkdir $tmp_dir, 0777 unless -d $tmp_dir;
	mkdir "$tmp_dir", 0777 unless -d "$tmp_dir";

	my $img = '2-n';
	if(defined $img and $img =~ /\S/) {
		if($img =~ /^(\d+)-n$/) {
			my $file = "$tmp_dir/mailgraph_$1.png";
			graph($graphs[$1]{seconds}, $file);
		}
		elsif($img =~ /^(\d+)-e$/) {
			my $file = "$tmp_dir/mailgraph_$1_err.png";
			graph_err($graphs[$1]{seconds}, $file);
		}
		else {
			die "ERROR: invalid argument\n";
		}
	}
}
sub main5()
{
	my $uri = '3-n';
	$uri =~ s/\/[^\/]+$//;
	$uri =~ s/\//,/g;
	$uri =~ s/(\~|\%7E)/tilde,/g;
	mkdir $tmp_dir, 0777 unless -d $tmp_dir;
	mkdir "$tmp_dir", 0777 unless -d "$tmp_dir";

	my $img = '3-n';
	if(defined $img and $img =~ /\S/) {
		if($img =~ /^(\d+)-n$/) {
			my $file = "$tmp_dir/mailgraph_$1.png";
			graph($graphs[$1]{seconds}, $file);
		}
		elsif($img =~ /^(\d+)-e$/) {
			my $file = "$tmp_dir/mailgraph_$1_err.png";
			graph_err($graphs[$1]{seconds}, $file);
		}
		else {
			die "ERROR: invalid argument\n";
		}
	}
}

sub main3()
{
	my $uri = '1-e';
	$uri =~ s/\/[^\/]+$//;
	$uri =~ s/\//,/g;
	$uri =~ s/(\~|\%7E)/tilde,/g;
	mkdir $tmp_dir, 0777 unless -d $tmp_dir;
	mkdir "$tmp_dir", 0777 unless -d "$tmp_dir";

	my $img = '1-e';
	if(defined $img and $img =~ /\S/) {
		if($img =~ /^(\d+)-n$/) {
			my $file = "$tmp_dir/mailgraph_$1.png";
			graph($graphs[$1]{seconds}, $file);
		}
		elsif($img =~ /^(\d+)-e$/) {
			my $file = "$tmp_dir/mailgraph_$1_err.png";
			graph_err($graphs[$1]{seconds}, $file);
		}
		else {
			die "ERROR: invalid argument\n";
		}
	}
}

main;

