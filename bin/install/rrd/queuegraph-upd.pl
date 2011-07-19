#!/usr/bin/perl -w
# queuegraph -- a postfix queue statistics rrdtool frontend
# based on mailgraph, which is
# copyright (c) 2000-2002 David Schweikert <dws@ee.ethz.ch>
# released under the GNU General Public License

use RRDs;
use POSIX qw(uname);

my $VERSION = "1.1";

my $host = (POSIX::uname())[1];
my $scriptname = 'queuegraph.cgi';
my $xpoints = 550;
my $points_per_sample = 3;
my $ypoints = 160;
my $ypoints_err = 80;
my $rrd = '/var/lib/queuegraph/queuegraph.rrd'; # path to where the RRD database is
my $tmp_dir = '/opt/artica/share/www/mailgraph'; # temporary directory where to store the images
my $rrdtool_1_0 = ($RRDs::VERSION < 1.199908);

my @graphs = (
	{ title => 'Day Graph',   seconds => 3600*24,        },
	{ title => 'Week Graph',  seconds => 3600*24*7,      },
	{ title => 'Month Graph', seconds => 3600*24*31,     },
	{ title => 'Year Graph',  seconds => 3600*24*365, },
);

my %color = (
	sent     => '000099', # rrggbb in hex
	received => '00FF00',
	rejected => '999999', 
	bounced  => '993399',
	virus    => 'FFFF00',
	spam     => 'FF0000',
);

sub graph($$$)
{
	my $range = shift;
	my $file = shift;
	my $title = shift;
	my $step = $range*$points_per_sample/$xpoints;
	my $date = localtime(time);
	$date =~ s|:|\\:|g unless $rrdtool_1_0;

	my ($graphret,$xs,$ys) = RRDs::graph($file,
		'--imgformat', 'PNG',
		'--width', $xpoints,
		'--height', $ypoints,
		'--start', "-$range",
		'--end', "-".int($range*0.01),
		'--vertical-label', 'queuefiles',
		'--title', $title,
		'--lazy',
 		$rrdtool_1_0 ? () : (
 			'--slope-mode'
 		),
 
        	"DEF:active=$rrd:active:AVERAGE",
        	"DEF:deferred=$rrd:deferred:AVERAGE",

        	'LINE2:active#00ff00:Active+Incoming+Maildrop\:',
		'GPRINT:active:MAX:Maximum\: %0.0lf ',
		'GPRINT:active:AVERAGE:Average\: %0.0lf/min\n',
					     
        	'LINE1:deferred#0000ff:Deferred\:',
		'GPRINT:deferred:MAX:Maximum\: %0.0lf ',
		'GPRINT:deferred:AVERAGE:Average\: %0.0lf/min\l',
					     
		'HRULE:0#000000',
        	'COMMENT:\n',
 		'COMMENT:['.$date.']\r',
     );
	my $ERR=RRDs::error;
	die "ERROR: $ERR\n" if $ERR;
}

sub print_html()
{
	print "Content-Type: text/html\n\n";

	print <<HEADER;
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<HTML>
<HEAD>
<TITLE>Queue Statistics for $host</TITLE>
</HEAD>
<BODY BGCOLOR="#FFFFFF">
HEADER

	print "<H1>Postfix Queue Statistics for $host</H1>\n";
	for my $n (0..$#graphs) {
		print "<H2>$graphs[$n]{title}</H2>\n";
		print "<P><IMG BORDER=\"0\" SRC=\"$scriptname/queuegraph_${n}.png\" ALT=\"queuegraph\">\n";
	}

	print <<FOOTER;
<table border="0" width="400"><tr><td align="left">
<A href="http://www.stahl.bau.tu-bs.de/~hildeb/postfix/queuegraph">queuegraph</A> $VERSION
by <A href="http://www.stahl.bau.tu-bs.de/~hildeb/">Ralf Hildebrandt</A>, 
based on <A href="http://people.ee.ethz.ch/~dws/software/mailgraph">mailgraph</A> 
by <A href="http://people.ee.ethz.ch/~dws/">David Schweikert</A></td>
<td ALIGN="right">
<a HREF="http://people.ee.ethz.ch/~oetiker/webtools/rrdtool/"><im
