#!/usr/bin/perl

# mailgraph -- a postfix statistics rrdtool frontend
# copyright (c) 2000-2005 David Schweikert <dws@ee.ethz.ch>
# released under the GNU General Public License

use RRDs;
use POSIX qw(uname);
my $xpoints = 540;
my $points_per_sample = 3;
my $ypoints = 160;
my $ypoints_err = 96;
my $global_path="/opt/artica/var/rrd";
my $img_path="/opt/artica/share/www/squid/rrd";
my $cur_time = time();                # set current time
my $end_time = $cur_time;     	      # set end time to now
my $start_time = time() - 3600;   # set start 30 days in the past

system("/bin/rm -rf /opt/artica/share/www/squid/rrd/*.png");


RRDs::graph("$img_path/connections.day.png",
	'--title',"Connections -- 1day",
        '--start', '-1day',
        '--imgformat', 'PNG',
        '--vertical-label',"req/sec",
        '--width','200',
	'--height','150',
	'--lazy',
	'--color', 'SHADEA#ffffff',
	'--color', 'SHADEB#ffffff',
	'--color', 'BACK#ffffff',
        "DEF:http=$global_path/connections.rrd:http:AVERAGE",
        "DEF:icp=$global_path/connections.rrd:icp:AVERAGE",
	'CDEF:xicp=icp,10,/',
        'AREA:http#0000FF:HTTP',
        'LINE2:xicp#00FF00:ICP/10');

RRDs::graph("$img_path/connections.day.1.png",
	'--title',"Connections -- 1day",
        '--start', '-1day',
        '--imgformat', 'PNG',
        '--vertical-label',"req/sec",
        '--width','540',
	'--height','200',
	'--font','DEFAULT:8',
	'--lazy',
	'--color', 'SHADEA#ffffff',
	'--color', 'SHADEB#ffffff',
	'--color', 'BACK#ffffff',
        "DEF:http=$global_path/connections.rrd:http:AVERAGE",
        "DEF:icp=$global_path/connections.rrd:icp:AVERAGE",
	'CDEF:xicp=icp,10,/',
        'AREA:http#0000FF:HTTP',
        'LINE2:xicp#00FF00:ICP/10');

RRDs::graph("$img_path/connections.hour.png",
	'--title',"Connections this hour",
        '--start', "$start_time",
	'--end',"$end_time",
	'--step',"600",
      	'--lower-limit', '0',
	'--x-grid',"MINUTE:10:MINUTE:10:MINUTE:10:0:%M",
        '--imgformat', 'PNG',
        '--vertical-label',"req/sec",
        '--width','170',
	'--height','100',
	'--lazy',
	'--color', 'SHADEA#ffffff',
	'--color', 'SHADEB#ffffff',
	'--color', 'BACK#ffffff',
        "DEF:http=$global_path/connections.rrd:http:AVERAGE",
        "DEF:icp=$global_path/connections.rrd:icp:AVERAGE",
	'CDEF:xicp=icp,10,/',
        'AREA:http#0000FF:HTTP',
        'LINE2:xicp#00FF00:ICP/10');


RRDs::graph("$img_path/cpu.day.png",
	'--title',"CPU Usage -- 1day",
        '--start','-1day',
        '--imgformat', 'PNG',
        '--vertical-label',"Percent",
        '--width','200',
	'--height','150',
	'--lazy',
	'--color', 'SHADEA#ffffff',
	'--color', 'SHADEB#ffffff',
	'--color', 'BACK#ffffff',
        "DEF:usage=$global_path/cpu.rrd:usage:AVERAGE",
        "AREA:usage#FF0000:Usage");

RRDs::graph("$img_path/svctime.day.png",
 	'--title',"Service Time -- 1day",
        '--start','-1day',
	'--upper-limit','0.5',
        '--imgformat','PNG',
        '--vertical-label',"seconds",
        '--width','200',
	'--height','150',
	'--color', 'SHADEA#ffffff',
	'--color', 'SHADEB#ffffff',
	'--color', 'BACK#ffffff',
        "DEF:http=$global_path/svctime.rrd:http:AVERAGE",
        "DEF:dns=$global_path/svctime.rrd:dns:AVERAGE",
        "AREA:http#0000FF:HTTP",
        "AREA:dns#00FF00:DNS");

print "objects.day.png\n";
RRDs::graph("$img_path/objects.day.png",
	'--title',"Cached Objects -- 1day",
	'--start','-1day',
	'--imgformat','PNG',
        '--vertical-label', "count",
        '--width 300',
	'--height','150',
	'--color', 'SHADEA#ffffff',
	'--color', 'SHADEB#ffffff',
	'--color', 'BACK#ffffff',
        "DEF:disk=$global_path/objects.rrd:disk:AVERAGE",
        "DEF:mem=$global_path/objects.rrd:mem:AVERAGE",
	"CDEF:xmem=mem,100,*",
        "AREA:disk#0000FF:Disk",
        "LINE2:xmem#00FF00:Memory(x100)");



RRDs::graph("$img_path/fd.day.png",
	'--title',"File Descriptors -- 1day",
        '--start','-1day',
        '--imgformat', 'PNG',
        '--vertical-label', "count",
        '--width', '200',
	'--height','150',
	'--color', 'SHADEA#ffffff',
	'--color', 'SHADEB#ffffff',
	'--color', 'BACK#ffffff',
        "DEF:all=$global_path/fd.rrd:all:AVERAGE",
        "DEF:store=$global_path/fd.rrd:store:AVERAGE",
        "AREA:all#0000FF:All",
        "AREA:store#7FFF7F:Store");

RRDs::graph("$img_path/hitratio.day.png",
	'--title',"Hit Ratio -- 1day",
        '--start','-1day',
        '--imgformat','PNG',
        '--vertical-label',"Percent",
        '--width','200',
	'--height','150',
	'--color', 'SHADEA#ffffff',
	'--color', 'SHADEB#ffffff',
	'--color', 'BACK#ffffff',
        "DEF:count=$global_path/hitratio.rrd:count:AVERAGE",
        "DEF:volume=$global_path/hitratio.rrd:volume:AVERAGE",
        "AREA:count#0000FF:Request",
        "LINE2:volume#7FFF7F:Volume");


RRDs::graph("$img_path/pagefaults.day.png",
	'--title',"Page Faults -- 1day",
        '--start -1day',
        '--imgformat','PNG',
        '--vertical-label', "Rate",
        '--width','200',
	'--height','150',
	'--color', 'SHADEA#ffffff',
	'--color', 'SHADEB#ffffff',
	'--color', 'BACK#ffffff',
        "DEF:pf=$global_path/pagefaults.rrd:pf:AVERAGE",
        "AREA:pf#FF00FF:Pagefaults");




RRDs::graph("$img_path/diskd.day.png",
	'--title',"DISKD -- 1day",
        '--start','-1day',
        '--imgformat','PNG',
        '--vertical-label',"Rate",
        '--width','200',
	'--height','150',
	'--color', 'SHADEA#ffffff',
	'--color', 'SHADEB#ffffff',
	'--color', 'BACK#ffffff',
        "DEF:ma=$global_path/diskd.rrd:max_away:AVERAGE",
	"DEF:ms=$global_path/diskd.rrd:max_shmuse:AVERAGE",
	"DEF:ofql=$global_path/diskd.rrd:open_fail_queue_len:AVERAGE",
	"DEF:bql=$global_path/diskd.rrd:block_queue_len:AVERAGE",
        "AREA:ma#0000FF:Msg",
        "LINE2:ms#FF0000:Shm",
	"LINE2:ofql#00FF00:OpenFail",
	"LINE2:bql#FF00FF:Block");

RRDs::graph("$img_path/memory.day.png",
	'--title',"Memory -- 1day",
        '--start','-1day',
        '--imgformat','PNG',
        '--vertical-label', "Megabytes",
        '--width', '200',
	'--height','150',
	'--color', 'SHADEA#ffffff',
	'--color', 'SHADEB#ffffff',
	'--color', 'BACK#ffffff',
        "DEF:mallinfo=$global_path/memory.rrd:mallinfo:AVERAGE",
        "DEF:accounted=$global_path/memory.rrd:accounted:AVERAGE",
        "DEF:rss=$global_path/memory.rrd:rss:AVERAGE",
        "DEF:sbrk=$global_path/memory.rrd:sbrk:AVERAGE",
        "AREA:rss#FFFF00:MaxRSS",
        "AREA:accounted#FF7F7F:Accounted",
        "LINE2:sbrk#0000FF:sbrk",
        "LINE2:mallinfo#00FF00:Mallinfo");


RRDs::graph("$img_path/memory.hour.png",
	'--title',"Memory this hour",
        '--start','-1day',
        '--imgformat','PNG',
        '--vertical-label', "Megabytes",
        '--width', '200',
	'--height','150',
	'--color', 'SHADEA#ffffff',
	'--color', 'SHADEB#ffffff',
	'--color', 'BACK#ffffff',
        "DEF:mallinfo=$global_path/memory.rrd:mallinfo:AVERAGE",
        "DEF:accounted=$global_path/memory.rrd:accounted:AVERAGE",
        "DEF:rss=$global_path/memory.rrd:rss:AVERAGE",
        "DEF:sbrk=$global_path/memory.rrd:sbrk:AVERAGE",
        "AREA:rss#FFFF00:MaxRSS",
        "AREA:accounted#FF7F7F:Accounted",
        "LINE2:sbrk#0000FF:sbrk",
        "LINE2:mallinfo#00FF00:Mallinfo");


RRDs::graph("$img_path/select.day.png",
	'--title',"Select Stats -- 1day",
        '--start','-1day',
        '--imgformat','PNG',
        '--vertical-label',"rate",
        '--width','200',
	'--height','150',
	'--color', 'SHADEA#ffffff',
	'--color', 'SHADEB#ffffff',
	'--color', 'BACK#ffffff',
        "DEF:sl=$global_path/select.rrd:sl:AVERAGE",
        "DEF:sf=$global_path/select.rrd:sf:AVERAGE",
        "DEF:asfp=$global_path/select.rrd:asfp:AVERAGE",
        "DEF:msf=$global_path/select.rrd:msf:AVERAGE",
        "LINE2:sl#0000FF:SelectLoops",
        "LINE2:sf#FF0000:SelectFDs",
        "LINE2:asfp#00FF00:AvgSelectFDPeriod",
        "LINE2:msf#FF00FF:MedianSelectFDs");

