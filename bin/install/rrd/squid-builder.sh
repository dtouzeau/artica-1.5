#!/bin/sh

START=""
PATH="/opt/artica/var/rrd"
RRDTOOL="/usr/bin/rrdtool"

RRAS="
RRA:AVERAGE:0.99:1:288
RRA:AVERAGE:0.99:6:336
RRA:AVERAGE:0.99:12:744
RRA:AVERAGE:0.99:288:365
RRA:AVERAGE:0.99:2016:520
"


test -f $PATH/connections.rrd || $RRDTOOL create $PATH/connections.rrd \
		$START \
		--step 300 \
		DS:http:DERIVE:600:0:U \
		DS:http_errors:DERIVE:600:0:U \
		DS:icp:DERIVE:600:0:U \
		DS:htcp:DERIVE:600:0:U \
		DS:snmp:DERIVE:600:0:U \
		$RRAS


test -f $PATH/objects.rrd || $RRDTOOL create $PATH/objects.rrd \
		$START \
		--step 300 \
		DS:disk:GAUGE:600:U:U \
		DS:mem:GAUGE:600:U:U \
		$RRAS

test -f $PATH/volume.rrd || $RRDTOOL create $PATH/volume.rrd \
		$START \
		--step 300 \
		DS:disk:GAUGE:600:U:U \
		DS:mem:GAUGE:600:U:U \
		$RRAS

test -f $PATH/memory.rrd || $RRDTOOL create $PATH/memory.rrd \
		$START \
		--step 300 \
		DS:mallinfo:GAUGE:600:U:U \
		DS:accounted:GAUGE:600:U:U \
		DS:rss:GAUGE:600:U:U \
		DS:sbrk:GAUGE:600:U:U \
		$RRAS

test -f $PATH/fd.rrd || $RRDTOOL create $PATH/fd.rrd \
		$START \
		--step 300 \
		DS:all:GAUGE:600:U:U \
		DS:store:GAUGE:600:U:U \
		$RRAS

test -f $PATH/pagefaults.rrd || $RRDTOOL create $PATH/pagefaults.rrd \
		$START \
		--step 300 \
		DS:pf:DERIVE:600:0:U \
		$RRAS

test -f $PATH/cpu.rrd || $RRDTOOL create $PATH/cpu.rrd \
		$START \
		--step 300 \
		DS:usage:GAUGE:600:U:U \
		$RRAS

test -f $PATH/replacement.rrd || $RRDTOOL create $PATH/replacement.rrd \
		$START \
		--step 300 \
		DS:thresh:GAUGE:600:U:U \
		$RRAS

test -f $PATH/svctime.rrd || $RRDTOOL create $PATH/svctime.rrd \
		$START \
		--step 300 \
		DS:http:GAUGE:600:U:U \
		DS:dns:GAUGE:600:U:U \
		DS:icp:GAUGE:600:U:U \
		DS:htcp:GAUGE:600:U:U \
		$RRAS

test -f $PATH/hitratio.rrd || $RRDTOOL create $PATH/hitratio.rrd \
		$START \
		--step 300 \
		DS:count:GAUGE:600:U:99 \
		DS:volume:GAUGE:600:U:99 \
		$RRAS

test -f $PATH/ip_proto.rrd || $RRDTOOL create $PATH/ip_proto.rrd \
		--step 300 \
		DS:icmpInMsgs:GAUGE:600:U:U \
		DS:icmpOutMsgs:GAUGE:600:U:U \
		DS:tcpInSegs:GAUGE:600:U:U \
		DS:tcpOutSegs:GAUGE:600:U:U \
		DS:udpInDatagrams:GAUGE:600:U:U \
		DS:udpOutDatagrams:GAUGE:600:U:U \
		$RRAS

test -f $PATH/if_octets.rrd || $RRDTOOL create $PATH/if_octets.rrd \
		--step 300 \
		DS:in:GAUGE:600:U:U \
		DS:out:GAUGE:600:U:U \
		$RRAS

test -f $PATH/diskd.rrd || $RRDTOOL create $PATH/diskd.rrd \
		--step 300 \
		DS:max_away:GAUGE:600:U:U \
		DS:max_shmuse:GAUGE:600:U:U \
		DS:open_fail_queue_len:DERIVE:600:0:U \
		DS:block_queue_len:DERIVE:600:0:U \
		$RRAS

test -f $PATH/store_io.rrd || $RRDTOOL create $PATH/store_io.rrd \
		--step 300 \
		DS:create_calls:DERIVE:600:0:U \
		DS:create_select_fail:DERIVE:600:0:U \
		DS:create_create_fail:DERIVE:600:0:U \
		DS:create_success:DERIVE:600:0:U \
		$RRAS

test -f $PATH/mempool.rrd || $RRDTOOL create $PATH/mempool.rrd \
		--step 300 \
		DS:alloc:DERIVE:600:0:U \
		DS:free:DERIVE:600:0:U \
		$RRAS

test -f $PATH/disk_pct_theoretical.rrd || $RRDTOOL create $PATH/disk_pct_theoretical.rrd \
		--step 300 \
		DS:d0:GAUGE:600:0:100 \
		DS:d1:GAUGE:600:0:100 \
		DS:d2:GAUGE:600:0:100 \
		DS:d3:GAUGE:600:0:100 \
		DS:d4:GAUGE:600:0:100 \
		DS:d5:GAUGE:600:0:100 \
		DS:d6:GAUGE:600:0:100 \
		DS:d7:GAUGE:600:0:100 \
		DS:d8:GAUGE:600:0:100 \
		DS:d9:GAUGE:600:0:100 \
		$RRAS

test -f $PATH/disk_pct_actual.rrd || $RRDTOOL create $PATH/disk_pct_actual.rrd \
		--step 300 \
		DS:d0:GAUGE:600:0:100 \
		DS:d1:GAUGE:600:0:100 \
		DS:d2:GAUGE:600:0:100 \
		DS:d3:GAUGE:600:0:100 \
		DS:d4:GAUGE:600:0:100 \
		DS:d5:GAUGE:600:0:100 \
		DS:d6:GAUGE:600:0:100 \
		DS:d7:GAUGE:600:0:100 \
		DS:d8:GAUGE:600:0:100 \
		DS:d9:GAUGE:600:0:100 \
		$RRAS

test -f $PATH/select.rrd || $RRDTOOL create $PATH/select.rrd \
		--step 300 \
		DS:sl:GAUGE:600:0:U \
		DS:sf:GAUGE:600:0:U \
		DS:asfp:GAUGE:600:0:U \
		DS:msf:GAUGE:600:0:U \
		$RRAS

test -f $PATH/store_log_tags.rrd || $RRDTOOL create $PATH/store_log_tags.rrd \
		--step 300 \
		DS:CREATE:DERIVE:600:0:U \
		DS:SWAPIN:DERIVE:600:0:U \
		DS:SWAPOUT:DERIVE:600:0:U \
		DS:RELEASE:DERIVE:600:0:U \
		DS:SO_FAIL:DERIVE:600:0:U \
		$RRAS

test -f $PATH/obj_size.rrd || $RRDTOOL create $PATH/obj_size.rrd \
		--step 300 \
		DS:mean:GAUGE:600:0:U \
		DS:median:GAUGE:600:0:U \
		$RRAS

test -f $PATH/bytes_in_out.rrd || $RRDTOOL create $PATH/bytes_in_out.rrd \
		--step 300 \
		DS:clt_in:DERIVE:600:0:U \
		DS:clt_out:DERIVE:600:0:U \
		DS:srv_in:DERIVE:600:0:U \
		DS:srv_out:DERIVE:600:0:U \
		$RRAS


PCTILES="
DS:p5:GAUGE:600:0:U
DS:p10:GAUGE:600:0:U
DS:p15:GAUGE:600:0:U
DS:p20:GAUGE:600:0:U
DS:p25:GAUGE:600:0:U
DS:p30:GAUGE:600:0:U
DS:p35:GAUGE:600:0:U
DS:p40:GAUGE:600:0:U
DS:p45:GAUGE:600:0:U
DS:p50:GAUGE:600:0:U
DS:p55:GAUGE:600:0:U
DS:p60:GAUGE:600:0:U
DS:p65:GAUGE:600:0:U
DS:p70:GAUGE:600:0:U
DS:p75:GAUGE:600:0:U
DS:p80:GAUGE:600:0:U
DS:p85:GAUGE:600:0:U
DS:p90:GAUGE:600:0:U
DS:p95:GAUGE:600:0:U
"

test -f $PATH/svctime_httphit_pctiles.rrd || $RRDTOOL create $PATH/svctime_httphit_pctiles.rrd \
                --step 300 \
		$PCTILES \
		$RRAS

test -f $PATH/svctime_httpmiss_pctiles.rrd || $RRDTOOL create $PATH/svctime_httpmiss_pctiles.rrd \
                --step 300 \
		$PCTILES \
                $RRAS

test -f $PATH/svctime_httpall_pctiles.rrd || $RRDTOOL create $PATH/svctime_httpall_pctiles.rrd \
                --step 300 \
		$PCTILES \
                $RRAS

test -f $PATH/svctime_dns_pctiles.rrd || $RRDTOOL create $PATH/svctime_dns_pctiles.rrd \
                --step 300 \
		$PCTILES \
		$RRAS

/bin/chmod 644 $PATH/*.rrd
