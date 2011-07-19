<?php
if(posix_getuid()<>0){
	die("Cannot be used in web server mode\n\n");
}
include_once(dirname(__FILE__) . '/ressources/class.users.menus.inc');
include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
include_once(dirname(__FILE__) . '/ressources/class.user.inc');
include_once(dirname(__FILE__) . '/ressources/class.ini.inc');

CleanQuarantine();
CleanStorage();
die();


function CleanQuarantine(){
$users=new usersMenus();
$sock=new sockets();
$Enable=$sock->GET_INFO("QuarantineAutoCleanEnabled");
$MaxDay=$sock->GET_INFO("QuarantineMaxDayToLive");
if($MaxDay==null){$MaxDay=15;}
if($Enable==null){$Enable=1;}
if($Enable<>1){return;}


echo "\n\n####### Max day to live: $MaxDay days in quarantine #######\n\n";

$sql="SELECT count(MessageID) AS tcount FROM quarantine WHERE zDate<DATE_SUB(NOW(),INTERVAL $MaxDay DAY)";
$q=new mysql();
$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
if($ligne["tcount"]<1){
	echo("Nothing to do ....\n\n");
	return;
}
$count=$ligne["tcount"];
echo "{$ligne["tcount"]} messages to clean...\n";
$sql="DELETE FROM quarantine WHERE zDate<DATE_SUB(NOW(),INTERVAL $MaxDay DAY)";

$results=$q->QUERY_SQL($sql,"artica_backup");
if(!$q->ok){echo "Mysql error $q->mysql_error\n";}


$date=date('Y-m-d H:i:s');	
$filename="/var/log/artica-postfix/events/"+md5($date);
$ini=new Bs_IniHandler();
$ini->_params["LOG"]["processname"]=dirname(__FILE__);
$ini->_params["LOG"]["date"]=$date;
$ini->_params["LOG"]["context"]="system";
$ini->_params["LOG"]["text"]="$count quarantine mails has been cleaned from disk and database";
$ini->_params["LOG"]["text"]="[$date]$count quarantine mails has been cleaned";
file_put_contents($filename,$ini->toString());

write_syslog("{$ligne["tcount"]} messages cleaned...",__FILE__);
echo "messages cleaned in quarantine...\n";
}

function CleanStorage(){
$users=new usersMenus();
$sock=new sockets();
$Enable=$sock->GET_INFO("StorageAutoCleanEnabled");
$MaxDay=$sock->GET_INFO("StorageMaxDayToLive");
if($MaxDay==null){$MaxDay=60;}
if($Enable==null){$Enable=1;}
if($Enable<>1){return;}


echo "\n\n####### Max day to live: $MaxDay days  in storage #######\n\n";

$sql="SELECT count(MessageID) AS tcount FROM storage WHERE zDate<DATE_SUB(NOW(),INTERVAL $MaxDay DAY)";
$q=new mysql();
$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
if($ligne["tcount"]<1){
	echo("Nothing to do ....\n\n");
	return;
}
$count=$ligne["tcount"];
echo "{$ligne["tcount"]} messages to clean...\n";
$sql="DELETE FROM storage WHERE zDate<DATE_SUB(NOW(),INTERVAL $MaxDay DAY)";

$results=$q->QUERY_SQL($sql,"artica_backup");
if(!$q->ok){echo "Mysql error $q->mysql_error\n";}


$date=date('Y-m-d H:i:s');	
$filename="/var/log/artica-postfix/events/"+md5($date);
$ini=new Bs_IniHandler();
$ini->_params["LOG"]["processname"]=dirname(__FILE__);
$ini->_params["LOG"]["date"]=$date;
$ini->_params["LOG"]["context"]="system";
$ini->_params["LOG"]["text"]="$count storage mails has been cleaned from disk and database";
$ini->_params["LOG"]["text"]="[$date]$count storage mails has been cleaned";
file_put_contents($filename,$ini->toString());

write_syslog("{$ligne["tcount"]} messages cleaned...",__FILE__);
echo "messages cleaned in storage...\n";
}


?>