#!/bin/sh

upgrade=""

case "$1" in
    remove)
	;;
    upgrade)
	upgrade="--upgrade"
	;;
    *)
	exit 1
	;;
esac

/etc/init.d/kav4proxy status >/dev/null 2>&1 && /etc/init.d/kav4proxy stop

perl /opt/kaspersky/kav4proxy/lib/bin/setup/uninstall.pl deb $upgrade


test "$?" != 0 && exit 1

update-rc.d -f kav4proxy remove >/dev/null

