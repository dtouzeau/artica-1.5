<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__) . '/ressources/class.users.menus.inc');
include_once(dirname(__FILE__) . '/ressources/class.ini.inc');
include_once(dirname(__FILE__) . '/ressources/class.ldap.inc');
include_once(dirname(__FILE__) . '/framework/class.unix.inc');
if(is_array($argv)){if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}}


if(!is_file("/etc/artica-postfix/settings/Daemons/CyrusAVConfig")){
	echo "/etc/artica-postfix/settings/Daemons/CyrusAVConfig no such file...\n";
	die();

}

@mkdir("/etc/artica-postfix/pids");
$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".pid";
$unix=new unix();
if($unix->process_exists(@file_get_contents($pidfile))){
	echo "Process already exists...\n";
	die();
}

@file_put_contents($pidfile,getmypid());

$t1=time();
$time=date('Y-m-d')."_".date('h:i');
$ini=new Bs_IniHandler();
$ini->loadFile("/etc/artica-postfix/settings/Daemons/CyrusAVConfig");
$nice=EXEC_NICE();
$clamscan=$unix->find_program("clamscan");
if(!is_file($clamscan)){die();}
$partition_default=$unix->IMAPD_GET("partition-default");
if(!is_dir($partition_default)){
	send_email_events("Mailboxes antivirus scanning failed","partition-default: \"$partition_default\"\nno such directory","mailbox");
	echo "partition-default: no such directory\n";
	die();
}

@mkdir("/var/log/artica-postfix/antivirus/cyrus-imap",755,true);  
$time=date('Y-m-d')."_".date('h:I');
$cmd="$nice /usr/bin/clamscan --recursive=yes --infected ";
$cmd=$cmd."--max-filesize=10M --max-scansize=10M --max-recursion=5 --max-dir-recursion=10 ";
$cmd=$cmd."--log=/log/artica-postfix/antivirus/cyrus-imap/$time.scan $partition_default";
shell_exec($cmd);
$t2=time();
$time_duration=distanceOfTimeInWords($t1,$t2);

send_email_events("Mailboxes antivirus scan terminated: $time_duration",
@file_get_contents("/log/artica-postfix/antivirus/cyrus-imap/$time.scan")
,"mailbox");
?>
