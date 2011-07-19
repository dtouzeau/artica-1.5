<?php
$GLOBALS["OUTPUT"]=false;
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.samba.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');
include_once(dirname(__FILE__).'/samba.sid.php');


if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;$GLOBALS["OUTPUT"]=true;}
if($argv[1]=='--users'){SMBCHANGEUSERS();die();}
if($argv[1]=='--groups'){SMBGROUPS();die();}

exec('/usr/share/artica-postfix/bin/artica-install --samba-reconfigure 2>&1',$reconfigure);
$ldap=new clladp();
$samba=new samba();
$sid=$ldap->LOCAL_SID();
$samba->ChangeSID($sid);
SMBCHANGECOMPUTERS();
SMBGROUPS();
SMBCHANGEUSERS();
SMBRESTART();

$unix=new unix();
$net=$unix->find_program("net");
$adminpass=$samba->GetAdminPassword("administrator");
$adminpass=$unix->shellEscapeChars($adminpass);

$logs=@implode("\n",$GLOBALS["MEMORY_LOGS"]);
exec('/ect/init.d/artica-postfix restart samba 2>&1',$restart);

$text="
Samba was resetted with a new sid : $sid
Computers and users was rebuilded with this new SID and samba was restarted. 

Reconfigure :
------------------------------
". @implode("\n",$reconfigure)."

Restart :
------------------------------
". @implode("\n",$restart)."

Users/groups synchronize details:
-------------------------------
$logs
";

if($GLOBALS["OUTPUT"]){
	echo "\n\n\nSamba was synchronized with new sid $sid\n$text\n";
	
}
$unix=new unix();
$unix->send_email_events("Samba was synchronized with new sid $sid",$text,"samba");

shell_exec("$net groupmap add ntgroup=\"Domain Admins\" unixgroup=root rid=512 type=d -U administrator%$adminpass &");
shell_exec("$net groupmap add ntgroup=\"Domain Users\" unixgroup=users rid=513	 type=d -U administrator%$adminpass &");
shell_exec("$net groupmap add ntgroup=\"Domain Guests\" unixgroup=users rid=514 type=d -U administrator%$adminpass &");
die();


?>