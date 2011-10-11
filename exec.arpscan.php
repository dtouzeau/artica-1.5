<?php
$GLOBALS["FORCE"]=false;
$GLOBALS["VERBOSE"]=false;
$GLOBALS["FLUSH"]=false;
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;$GLOBALS["debug"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}
if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;}
if(preg_match("#--flush#",implode(" ",$argv))){$GLOBALS["FLUSH"]=true;}
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__) . '/ressources/class.users.menus.inc');
include_once(dirname(__FILE__) . '/ressources/class.dhcpd.inc');
include_once(dirname(__FILE__) . '/ressources/class.computers.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");
include_once(dirname(__FILE__).'/framework/class.unix.inc');

scanarp();
function scanarp(){
$GLOBALS["CLASS_USERS"]=new usersMenus();
$GLOBALS["CLASS_SOCKETS"]=new sockets();
if(!$GLOBALS["CLASS_USERS"]->ARPD_INSTALLED){if($GLOBALS["VERBOSE"]){echo __FUNCTION__." ARPD_INSTALLED = FALSE\n";}return;}
$EnableArpDaemon=$GLOBALS["CLASS_SOCKETS"]->GET_INFO("EnableArpDaemon");	
if(!is_numeric($EnableArpDaemon)){$EnableArpDaemon=1;}
if($EnableArpDaemon==0){if($GLOBALS["VERBOSE"]){echo __FUNCTION__." EnableArpDaemon = $EnableArpDaemon\n";}return;}
$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".pid";
$unix=new unix();

if($unix->process_exists(@file_get_contents($pidfile))){
	if($GLOBALS["VERBOSE"]){echo " --> Already executed.. ". @file_get_contents($pidfile). " aborting the process\n";}
	events(basename(__FILE__).":Already executed.. aborting the process",__FUNCTION__,__LINE__);
	die();
}
@file_put_contents($pidfile, getmypid());
if(!is_file("/var/lib/arpd/arpd.db")){die();}
$GLOBALS["CLASS_UNIX"]=$unix;
$GLOBALS["nmblookup"]=$unix->find_program("nmblookup");
$GLOBALS["arpd"]=$unix->find_program("arpd");
$GLOBALS["arp"]=$unix->find_program("arp");
$GLOBALS["ARP_DB"]="/var/lib/arpd/arpd.db";
$GLOBALS["CACHE_DB"]="/etc/artica-postfix/arpd.cache";


$ArpdArray=unserialize(base64_decode(@file_get_contents($GLOBALS["CACHE_DB"])));
if($GLOBALS["FLUSH"]){$ArpdArray=array();}
if(!is_array($ArpdArray)){$ArpdArray=array();}
if(!isset($ArpdArray["LAST"])){$ArpdArray["LAST"]=0;}

$last_modified = filemtime($GLOBALS["ARP_DB"]);
$TimeArpd=$ArpdArray["LAST"];
if($TimeArpd==$last_modified){events("$TimeArpd -> $last_modified No modification time",__FUNCTION__,__LINE__);return;}
events("Scanning ARP table....",__FUNCTION__,__LINE__);
$ArpdArray["LAST"]=$last_modified;

exec("{$GLOBALS["arpd"]} -l 2>&1",$results);
events("{$GLOBALS["arpd"]} -l return " . count($results)." element(s)",__FUNCTION__,__LINE__);
while (list ($num, $ligne) = each ($results) ){
	if(preg_match("#unexpected file type or format#", $ligne)){@unlink($GLOBALS["ARP_DB"]);@unlink($GLOBALS["CACHE_DB"]);shell_exec("/etc/init.d/arpd restart");die();}
	if(!preg_match("#^[0-9]+\s+\s+(.+?)\s+(.+)#", $ligne,$re)){if($GLOBALS["VERBOSE"]){echo "line: $num, unexpected line..\n";}continue;}
	if(preg_match("#FAILED:#", $re[2])){continue;}
	
	$mac=$re[2];
	$ipaddr=$re[1];
	if($GLOBALS["VERBOSE"]){echo "line: $num, MAC:$mac -> $ipaddr\n";}
	if(isset($ArpdArray["MACS"][$mac])){if($GLOBALS["VERBOSE"]){echo "MAC:$mac Already cached, aborting....\n";}continue;}
	$ArpdArray["MACS"][$mac]=true;
	$cmp=new computers();
	
	$uid=$cmp->ComputerIDFromMAC($mac);
	if($GLOBALS["VERBOSE"]){echo "line: $num, MAC:$mac -> $uid\n";}
	if($uid==null){
		$res2=array();
		$computer_name=null;
		events("It is time to add $mac/$ipaddr in database",__FUNCTION__,__LINE__);
		exec("{$GLOBALS["arp"]} -a $ipaddr 2>&1",$res2);
		if(preg_match("#^(.+?)\s+\(#",trim(@implode("", $res2)),$rz)){$computer_name=$rz[1];}
		if(strlen($computer_name)<3){$computer_name=$ipaddr;}
		$cmp->uid="$computer_name$";
		$cmp->ComputerIP=$ipaddr;
		$cmp->ComputerMacAddress=$mac;
		$cmp->Add();		
	}else{
		if($GLOBALS["FLUSH"]){
			$res2=array();
			$cmp=new computers($uid);
			$computer_name=null;
			events("It is time to edit $uid/$mac/$ipaddr in database",__FUNCTION__,__LINE__);
			
			exec("{$GLOBALS["arp"]} -a $ipaddr 2>&1",$res2);
			if($GLOBALS["VERBOSE"]){echo "{$GLOBALS["arp"]} -a $ipaddr 2>&1 = >". trim(@implode("", $res2));}
			if(preg_match("#^(.+?)\s+\(#",trim(@implode("", $res2)),$rz)){$computer_name=$rz[1];}else{if($GLOBALS["VERBOSE"]){echo "Unable to find computer name\n";}}
			if(strlen($computer_name)<3){$computer_name=$ipaddr;}
			if($GLOBALS["VERBOSE"]){echo "line: $num, UID:$mac -> $uid\n";}
			if($GLOBALS["VERBOSE"]){echo "line: $num, NAME:$computer_name -> $uid\n";}
			$cmp->ComputerIP=$ipaddr;
			$cmp->ComputerMacAddress=$mac;
			$cmp->Add();				
			}
		
	}
	
	
	
}


@file_put_contents($GLOBALS["CACHE_DB"], base64_encode(serialize($ArpdArray)));



}



function events($text,$function=null,$line=0){
	$filename=basename(__FILE__);
	if(!isset($GLOBALS["CLASS_UNIX"])){include_once(dirname(__FILE__)."/framework/class.unix.inc");$GLOBALS["CLASS_UNIX"]=new unix();}
	$GLOBALS["CLASS_UNIX"]->events("$filename $function:: $text (L.$line)",null);
}