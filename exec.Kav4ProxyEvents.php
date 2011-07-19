<?php
//if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}

$GLOBALS["KAV4PROXY_NOSESSION"]=true;

include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.status.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__).'/ressources/class.system.network.inc');
include_once(dirname(__FILE__).'/ressources/class.artica.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");
//server-syncronize-64.png

if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;}

writelogs("command-lines=".implode(" ;",$argv),__FUNCTION__,__FILE__,__LINE__);

$action=$argv[1];
$type=$argv[2];
$InfectedPath=$argv[3];
$ComputerName=$argv[4];
$VirusName=$argv[5];
$TaskName="HTTP Scan";
$zmd5=md5(implode("-",$argv));
$sql="INSERT INTO antivirus_events (zDate,TaskName,VirusName,InfectedPath,ComputerName,zmd5)
VALUES(NOW(),'$TaskName','$VirusName','$InfectedPath','$ComputerName','$zmd5')";
$q=new mysql();
$q->QUERY_SQL($sql,"artica_events");

$sock=new sockets();
$SquidAutoblock=$sock->GET_INFO("SquidAutoblock");
writelogs("SquidAutoblock=$SquidAutoblock",__FUNCTION__,__FILE__,__LINE__);
if($sock->GET_INFO("SquidAutoblock")==1){
	$InfectedPath=str_replace(basename($InfectedPath),"",$InfectedPath);
	$sql="INSERT INTO squid_block(uri,task_type,zDate)
	VALUES('$InfectedPath','autoblock $VirusName',NOW());";
	$q->QUERY_SQL($sql,"artica_backup");
	$sock->getFrameWork("cmd.php?squidnewbee=yes");
}
?>