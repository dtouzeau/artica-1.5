<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.kav4proxy.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');



$kav=new Kav4Proxy();
$conf=$kav->build_config();
echo "Starting......: Kav4proxy building configuration done\n";
@file_put_contents("/etc/opt/kaspersky/kav4proxy.conf",$conf);


	
?>