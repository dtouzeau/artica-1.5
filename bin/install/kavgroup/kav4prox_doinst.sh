#!/bin/sh

upgrade=""

if [ "$1" = 'upgrade' ] ; then
    upgrade="--upgrade"
elif [ "$1" = 'configre' -a -n "$2" ] ; then
    upgrade="--upgrade"
fi

if [ -n "$upgrade" ] ; then
    /etc/init.d/kav4proxy start
fi

if [ "$1" = 'configure' ] ; then
    perl /opt/kaspersky/kav4proxy/lib/bin/setup/postinstall.pl deb $upgrade
fi

exit 0

update-rc.d kav4proxy start 50 2 3 4 5  . stop 30 0 1 6 . >/dev/null

