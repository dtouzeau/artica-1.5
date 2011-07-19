#!/bin/sh
### BEGIN INIT INFO
# Provides:          pdns
# Required-Start:    $network $remote_fs $syslog
# Required-Stop:     $network $remote_fs $syslog
# Default-Start:     2 3 4 5
# Default-Stop:      0 1 6
# Should-Start:      slapd
# Should-Stop:       slapd
# Short-Description: PDNS is a versatile high performance authoritative nameserver.
### END INIT INFO

PATH=/bin:/sbin:/usr/bin:/usr/sbin
BINARYPATH=/usr/bin
SBINARYPATH=/usr/sbin
SOCKETPATH=/var/run
NAME=pdns-server

[ -f "$SBINARYPATH/pdns_server" ] || exit 0

if [ -r /etc/default/pdns ]; then
	. /etc/default/pdns
fi

# Make sure that /var/run exists
mkdir -p $SOCKETPATH
cd $SOCKETPATH
suffix=`basename $0 | awk -F- '{print $2}'`
if [ $suffix ]; then
	EXTRAOPTS=--config-name=$suffix
	PROGNAME=pdns-$suffix
else
	PROGNAME=pdns
fi

pdns_server="$SBINARYPATH/pdns_server $EXTRAOPTS"

doPC()
{
	ret=$($BINARYPATH/pdns_control $EXTRAOPTS $1 $2 2> /dev/null)
}

do_test_running()
{
	doPC ping
	NOTRUNNING=$?
}

do_start()
{
	do_test_running
	if [ "$NOTRUNNING" = "0" ] || [ "$START" = "no" ]; then
		echo "already running or disabled"
	else
		$pdns_server --daemon --guardian=yes
		if test "$?" = "0"; then
			sleep 2
			echo "started"
		fi
	fi
}


case "$1" in
	status)
		do_test_running
		if test "$NOTRUNNING" = "0"; then
			doPC status
			echo $ret
		else
			echo "not running"
		fi
	;;
	stop)
		echo -n "Stopping PowerDNS authoritative nameserver: "
		do_test_running
		if test "$NOTRUNNING" = "0"; then
			doPC quit
			echo $ret
		else
			echo "not running"
		fi
	;;
	force-stop)
		echo -n "Stopping PowerDNS authoritative nameserver: "
		killall -v -9 pdns_server
		echo "killed"
	;;
	start)
		echo -n "Starting PowerDNS authoritative nameserver: "
		do_start
	;;
	force-reload | restart)
		echo -n "Restarting PowerDNS authoritative nameserver: "
		echo -n stopping and waiting..
		doPC quit
		sleep 3
		echo done
		do_start
	;;
	reload)
		echo -n "Reloading PowerDNS authoritative nameserver: "
		do_test_running
		if test "$NOTRUNNING" = "0"; then
			doPC cycle
			echo requested reload
		else
			echo not running yet
			$0 start
		fi
	;;
	monitor)
		do_test_running
		if test "$NOTRUNNING" = "0"; then
			echo "already running"
		else
			$pdns_server --daemon=no --guardian=no --control-console --loglevel=9
		fi 
	;;		
	dump)
		do_test_running
		if test "$NOTRUNNING" = "0"; then
			doPC list
			echo $ret
		else
			echo "not running"
		fi 
	;;		
	show)
		if [ $# -lt 2 ]; then
			echo Insufficient parameters
			exit
		fi 
		do_test_running
		if test "$NOTRUNNING" = "0"; then
			echo -n "$2="
			doPC show $2 ; echo $ret
		else
			echo "not running"
		fi
	;;
	mrtg)
		if [ $# -lt 2 ]; then
			echo Insufficient parameters
			exit
		fi 
		do_test_running
		if test "$NOTRUNNING" = "0"; then
			doPC show $2 ; echo $ret
			if [ "$3x" != "x" ]; then
				doPC show $3 ; echo $ret
			else
				echo 0
			fi
			doPC uptime ; echo $ret
			echo PowerDNS daemon
		else
			echo "not running"
		fi
	;;
	cricket)
		if [ $# -lt 2 ]; then
			echo Insufficient parameters
			exit
		fi 
		do_test_running
		if test "$NOTRUNNING" = "0"; then
			doPC show $2 ; echo $ret
		else
			echo "not running"
		fi
	;;		

	*)
		echo pdns [start\|stop\|force-reload\|restart\|status\|dump\|show\|mrtg\|cricket\|monitor]
	;;
esac

exit 0

