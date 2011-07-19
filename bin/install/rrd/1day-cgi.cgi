#!/usr/bin/rrdcgi
<HTML>
<HEAD>
<TITLE>Squid Stats</TITLE>
<META HTTP-EQUIV="Refresh" CONTENT="150">
</HEAD>
<BODY>

<RRD::GRAPH svctime.day.png --title="Service Time -- 1day"
        --start -1day
	--upper-limit 0.5
        --imgformat PNG
        --vertical-label "seconds"
        --width 300 --height 150
        DEF:http=svctime.rrd:http:AVERAGE
        DEF:dns=svctime.rrd:dns:AVERAGE
        AREA:http#0000FF:HTTP
        AREA:dns#00FF00:DNS
        >

<RRD::GRAPH connections.day.png --title="Connections -- 1day"
        --start -1day
        --imgformat PNG
        --vertical-label "req/sec"
        --width 300 --height 150
        DEF:http=connections.rrd:http:AVERAGE
        DEF:icp=connections.rrd:icp:AVERAGE
	CDEF:xicp=icp,10,/
        AREA:http#0000FF:HTTP
        LINE2:xicp#00FF00:ICP/10
        >

<RRD::GRAPH objects.day.png --title="Cached Objects -- 1day"
        --start -1day
        --imgformat PNG
        --vertical-label "count"
        --width 300 --height 150
        DEF:disk=objects.rrd:disk:AVERAGE
        DEF:mem=objects.rrd:mem:AVERAGE
	CDEF:xmem=mem,100,*
        AREA:disk#0000FF:Disk
        LINE2:xmem#00FF00:Memory(x100)
        >

<RRD::GRAPH fd.day.png --title="File Descriptors -- 1day"
        --start -1day
        --imgformat PNG
        --vertical-label "count"
        --width 300 --height 150
        DEF:all=fd.rrd:all:AVERAGE
        DEF:store=fd.rrd:store:AVERAGE
        AREA:all#0000FF:All
        AREA:store#7FFF7F:Store
        >

<RRD::GRAPH hitratio.day.png --title="Hit Ratio -- 1day"
        --start -1day
        --imgformat PNG
        --vertical-label "Percent"
        --width 300 --height 150
        DEF:count=hitratio.rrd:count:AVERAGE
        DEF:volume=hitratio.rrd:volume:AVERAGE
        AREA:count#0000FF:Request
        LINE2:volume#7FFF7F:Volume
        >

<RRD::GRAPH cpu.day.png --title="CPU Usage -- 1day"
        --start -1day
        --imgformat PNG
        --vertical-label "Percent"
        --width 300 --height 150
        DEF:usage=cpu.rrd:usage:AVERAGE
        AREA:usage#FF0000:Usage
        >

<RRD::GRAPH pagefaults.day.png --title="Page Faults -- 1day"
        --start -1day
        --imgformat PNG
        --vertical-label "Rate"
        --width 300 --height 150
        DEF:pf=pagefaults.rrd:pf:AVERAGE
        AREA:pf#FF00FF:Pagefaults
        >

<RRD::GRAPH diskd.day.png --title="DISKD -- 1day"
        --start -1day
        --imgformat PNG
        --vertical-label "Rate"
        --width 300 --height 150
        DEF:ma=diskd.rrd:max_away:AVERAGE
	DEF:ms=diskd.rrd:max_shmuse:AVERAGE
	DEF:ofql=diskd.rrd:open_fail_queue_len:AVERAGE
	DEF:bql=diskd.rrd:block_queue_len:AVERAGE
        AREA:ma#0000FF:Msg
        LINE2:ms#FF0000:Shm
	LINE2:ofql#00FF00:OpenFail
	LINE2:bql#FF00FF:Block
        >

<RRD::GRAPH memory.day.png --title="Memory -- 1day"
        --start -1day
        --imgformat PNG
        --vertical-label "Megabytes"
        --width 300 --height 150
        DEF:mallinfo=memory.rrd:mallinfo:AVERAGE
        DEF:accounted=memory.rrd:accounted:AVERAGE
        DEF:rss=memory.rrd:rss:AVERAGE
        DEF:sbrk=memory.rrd:sbrk:AVERAGE
        AREA:rss#FFFF00:MaxRSS  
        AREA:accounted#FF7F7F:Accounted
        LINE2:sbrk#0000FF:sbrk
        LINE2:mallinfo#00FF00:Mallinfo
        >

<RRD::GRAPH select.day.png --title="Select Stats -- 1day"
        --start -1day
        --imgformat PNG
        --vertical-label "rate"
        --width 300 --height 150
        DEF:sl=select.rrd:sl:AVERAGE
        DEF:sf=select.rrd:sf:AVERAGE
        DEF:asfp=select.rrd:asfp:AVERAGE
        DEF:msf=select.rrd:msf:AVERAGE
        LINE2:sl#0000FF:SelectLoops
        LINE2:sf#FF0000:SelectFDs
        LINE2:asfp#00FF00:AvgSelectFDPeriod
        LINE2:msf#FF00FF:MedianSelectFDs
        >

</center>
</BODY>
</HTML>
