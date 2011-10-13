<?php

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
$GLOBALS["AS_ROOT"]=true;
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");

if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}
if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;}

if($argv[1]=="--destroy"){destroy();die();}



function destroy(){
	remove_initd();
	if(is_file("/etc/cron.d/sendmail")){@unlink("/etc/cron.d/sendmail");}
	if(is_file("/usr/share/sendmail/sendmail")){@copy("/usr/share/sendmail/sendmail", "/usr/share/sendmail/sendmail.bak");@unlink("/usr/share/sendmail/sendmail");}
	if(is_file("/etc/init.d/sendmail")){
		if(!is_file("/etc/init.d/sendmail.bak")){
			@copy("/etc/init.d/sendmail", "/etc/init.d/sendmail.bak");
			erase_initd();
		}
	}
	
	if(!is_file("/usr/sbin/sendmail-msp")){
		@file_put_contents("/usr/sbin/sendmail-msp", "#!/bin/sh\n");
		shell_exec("/bin/chmod 755 /usr/sbin/sendmail-msp");
	}
	
}


function remove_initd(){
	if(!is_file("/etc/init.d/sendmail")){return;}
	$unix=new unix();
	$debianbin=$unix->find_program("update-rc.d");
	$redhatbin=$unix->find_program("chkconfig");
	echo "Starting......: sendmail remove /etc/init.d/sendmail\n";	
	if(is_file($redhatbin)){
		shell_exec("$redhatbin --del sendmail >/dev/null 2>&1");
		shell_exec("$redhatbin --level 2345 sendmail off >/dev/null 2>&1");
	}
	if(is_file($debianbin)){
		shell_exec("$debianbin -n -f sendmail remove >/dev/null 2>&1");
	}	
	
	
	
}

function erase_initd(){
$f[]="#!/bin/sh";
$f[]="### BEGIN INIT INFO";
$f[]="# Provides:          sendmail";
$f[]="# Required-Start:    \$remote_fs \$network \$syslog";
$f[]="# Required-Stop:     \$remote_fs \$network \$syslog";
$f[]="# Default-Start:     2 3 4 5";
$f[]="# Default-Stop:      1 ";
$f[]="# Short-Description: powerful, efficient, and scalable Mail Transport Agent";
$f[]="# Description:       Sendmail is an alternative Mail Transport Agent (MTA)";
$f[]="#                    for Debian. It is suitable for handling sophisticated";
$f[]="#                    mail configurations, although this means that its";
$f[]="#                    configuration can also be complex. Fortunately, simple";
$f[]="#                    thing can be done easily, and complex thing are possible,";
$f[]="#                    even if not easily understood ;)  Sendmail is the *ONLY*";
$f[]="#                    MTA with a Turing complete language to control *ALL*";
$f[]="#                    aspects of delivery!";
$f[]="### END INIT INFO";
$f[]="exit 0;";
@file_put_contents("/etc/init.d/sendmail", @implode("\n", $f));
	
}