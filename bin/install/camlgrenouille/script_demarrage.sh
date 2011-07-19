#!/bin/bash
# /etc/rc.d/init.d/camlgrenouille
#
# Fichier de lancement de camlgrenouille au demarrage de GNU/Linux
#
# Realise par Loic Prouveze, le 24 mars 2003
# Debugge par Frederic Le Mouel, le 25 mars 2004

# chkconfig: 2345 96 16
# description: Démarrage de CamlGrenouille

#debug
#set -x

DAEMON=/usr/bin/camlgrenouille
DAEMON_CONF=/etc/camlgrenouille/user.config
DAEMON_LOG=/var/log/camlgrenouille.log
initdir=/etc/rc.d/init.d

PATH=/sbin:/usr/sbin:/bin:/usr/bin
export PATH



# Status

function status()
{
 ps axwww|grep "[0-9]:[0-9][0-9] $DAEMON" | (
 while read pid tt stat time command; do echo $command; done
 )
}

# Renvoie true s'il y a au moins un pid existant

function alive()
 {
 if [ -z "$*" ]; then
 return 1
 fi
 for i in $*; do
 if kill -0 $i 2> /dev/null; then
 return 0
 fi
 done

return 1
 }

# start/stop function

function start()
 {
 RETVAL=0
 # Verification que CamlGrenouille ne soit pas deja lance
 if [ ! -f /var/lock/subsys/camlgrenouille ]; then
 echo ''
 echo "Demarrage de CamlGrenouille..."
 echo ''
 $DAEMON -t -f $DAEMON_CONF >> $DAEMON_LOG &
 touch /var/lock/subsys/camlgrenouille
 fi
 return $RETVAL
 }

function stop()
 {
 echo ''
 echo "Arret de CamlGrenouille..."
 echo ''
 pids=$(/sbin/pidof $DAEMON)
 kill -TERM $pids 2> /dev/null && sleep 1
 RETVAL=$?
 count=1
 while alive $pids; do
 sleep 5
 count=$(expr $count + 1)
 if [ $count -gt 10 ]; then
 echo ''
 echo "Arret de CamlGrenouille..."
 echo ''
 break;
 fi
 echo ''
 echo "CamlGrenouille n'est pas encore arrete : nouvel essais...
(attempt
 $count)"
 echo ''
 done
 rm -f /var/lock/subsys/camlgrenouille
 return $RETVAL
 }

# See how we were called.

case "$1" in
 start)
 start
 ;;
 stop)
 stop
 ;;
 reload|restart)
 if [ ! -f /var/lock/subsys/camlgrenouille ]; then
 echo ''
 echo "CamlGrenouille n'est pas en cours de fonctionnement."
 echo ''
 exit 1
 fi
 echo ''
 echo "Verification des changements de configuration..."
 echo ''
 if [ -f /var/lock/subsys/camlgrenouille ] ; then
 stop
 start
 fi
 ;;
 status)
 status
 ;;
 *)
 echo "Usage: $initdir/camlgrenouille
 {start|stop|restart|reload|status}"
 exit 1
 esac

exit 0
