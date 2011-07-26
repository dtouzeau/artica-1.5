<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.main_cf.inc');
include_once(dirname(__FILE__).'/ressources/class.amavis.inc');
include_once(dirname(__FILE__).'/ressources/class.samba.inc');
include_once(dirname(__FILE__).'/ressources/class.squid.inc');

$amavis=new amavis();
$amavis->Save();
$amavis->SaveToServer();

$samba=new samba();
$samba->SaveToLdap();

$squid=new squidbee();
$squid->SaveToLdap();
$squid->SaveToServer();

system('/etc/init.d/artica-postfix restart postfix');
system('/etc/init.d/artica-postfix restart squid');
system('/etc/init.d/artica-postfix restart samba');
system('/usr/share/artica-postfix/bin/artica-install --cyrus-checkconfig');
system('/etc/init.d/artica-postfix restart imap');
system('/etc/init.d/artica-postfix restart saslauthd');
system('/etc/init.d/artica-postfix restart zarafa');
system('/etc/init.d/artica-postfix restart artica-status');
system('/etc/init.d/artica-postfix restart artica-back');
system('/etc/init.d/artica-postfix restart artica-exec');
system('/usr/share/artica-postfix/bin/artica-install --nsswitch')


?>