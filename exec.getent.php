<?php
$GLOBALS["FORCE"]=false;
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__).'/ressources/class.system.network.inc');


if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}
if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;}

if($argv[1]=='--domains'){samba_domains_info();die();}

samba_domains_info();
getent();
die();


function samba_domains_info(){
	$unix=new unix();
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".". __FUNCTION__.".pid";
	$pidTime="/etc/artica-postfix/pids/".basename(__FILE__).".". __FUNCTION__.".time";
	$oldpid=$unix->get_pid_from_file($pidfile);
	if($unix->process_exists($oldpid,basename(__FILE__))){
		writelogs("Process $oldpid already exists",__FUNCTION__,__FILE__,__LINE__);
		return;
	}	
	@file_put_contents($pidfile, getmypid());
	$ttime=$unix->file_time_min($pidTime);
	if($GLOBALS["VERBOSE"]){echo "$pidTime = {$ttime}Mn\n";}
	if(!$GLOBALS["FORCE"]){if($unix->file_time_min($pidTime)<5){return;}}
	@unlink($pidTime);
	@file_put_contents($pidTime, time());	
	$wbinfo=$unix->find_program("wbinfo");
	if(!is_file($wbinfo)){return;}
	
	exec("$wbinfo -m 2>&1",$results);
	$q=new mysql();
	$q->QUERY_SQL("TRUNCATE TABLE `samba_domains_info`","artica_backup");
	while (list ($num, $domain) = each ($results) ){
		$array=samba_domains_info_domain($domain,$wbinfo);
		$domain=trim($domain);
		if(!is_array($array)){continue;}
		if(strtolower($array["Primary"])=="yes"){$array["Primary"]=1;}else{$array["Primary"]=0;}
		if(strtolower($array["AD"])=="yes"){$array["AD"]=1;}else{$array["AD"]=0;}
		if(strtolower($array["Native"])=="yes"){$array["Native"]=1;}else{$array["Native"]=0;}
		$sql="INSERT IGNORE INTO samba_domains_info (`domain`,`Alt_Name`,`SID`,`AD`,`Native`,`Primary`) 
		VALUES('$domain','{$array["Alt_Name"]}','{$array["SID"]}',{$array["AD"]},{$array["Native"]},{$array["Primary"]})";
		if($GLOBALS["VERBOSE"]){echo $sql."\n";}
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo $q->mysql_error."\n";}
		
	}
	
	
}

function samba_domains_info_domain($domain,$bin){
	exec("$bin -D $domain 2>&1",$results);
	while (list ($num, $line) = each ($results) ){
		if(preg_match("#Alt_Name.+?:\s+(.+)#", $line,$re)){$array["Alt_Name"]=trim($re[1]);continue;}
		if(preg_match("#SID.+?:\s+(.+)#", $line,$re)){$array["SID"]=trim($re[1]);continue;}
		if(preg_match("#Active Directory.+?:\s+(.+)#", $line,$re)){$array["AD"]=trim($re[1]);continue;}
		if(preg_match("#Native.+?:\s+(.+)#", $line,$re)){$array["Native"]=trim($re[1]);continue;}
		if(preg_match("#Primary.+?:\s+(.+)#", $line,$re)){$array["Primary"]=trim($re[1]);continue;}			
	}
	return $array;
}


function getent(){
	$unix=new unix();
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".pid";
	$pidTime="/etc/artica-postfix/pids/".basename(__FILE__).".time";
	$oldpid=$unix->get_pid_from_file($pidfile);
	if($unix->process_exists($oldpid,basename(__FILE__))){
		writelogs("Process $oldpid already exists",__FUNCTION__,__FILE__,__LINE__);
		return;
	}
	
	if(system_is_overloaded(basename(__FILE__))){
		writelogs("Overloaded system, aborting",__FUNCTION__,__FILE__,__LINE__);
		return;
	}
	
	@file_put_contents($pidfile, getmypid());
	
	if(!$GLOBALS["FORCE"]){if($unix->file_time_min($pidTime)<120){return;}}
	@unlink($pidTime);
	@file_put_contents($pidTime, time());
	
	
	
	$getent=$unix->find_program("getent");
	if($GLOBALS["VERBOSE"]){writelogs("[getent passwd]:: Executing, please wait....",__FUNCTION__,__FILE__,__LINE__);}
	exec("$getent passwd 2>&1",$results);
	if($GLOBALS["VERBOSE"]){writelogs("[getent passwd]:: parsing  ".count($results)." elements ",__FUNCTION__,__FILE__,__LINE__);}
	
	$prefix="INSERT IGNORE INTO getent_users (uid) VALUES ";
	$q=new mysql();
	$q->BuildTables();
	$q->QUERY_SQL("TRUNCATE TABLE `getent_users`","artica_backup");
	
	
	while (list ($num, $ligne) = each ($results) ){
		if(preg_match("#^(.+?):.+?:#", $ligne,$re)){
			$re[1]=addslashes(utf8_encode($re[1]));
			$sql[]="('{$re[1]}')";
			if(count($sql)>500){
				if($GLOBALS["VERBOSE"]){writelogs("[getent passwd]:: Inserting ".count($sql)." elements ",__FUNCTION__,__FILE__,__LINE__);}
				$sqlfinal=$prefix.@implode(",", $sql);
				$q->QUERY_SQL($sqlfinal,"artica_backup");
				if(!$q->ok){echo $q->mysql_error."\n";}
				$sql=array();
			}
		}
	}
	$results=array();
	$wbinfo=$unix->find_program("wbinfo");
	if(is_file($wbinfo)){
		exec("$wbinfo -m 2>&1",$cmd_domains_list);
		if($GLOBALS["VERBOSE"]){writelogs("[wbinfo -m]:: return ". count($cmd_domains_list)." elements",__FUNCTION__,__FILE__,__LINE__);}
		while (list ($num, $DOMAIN) = each ($cmd_domains_list) ){
			$DOMAIN=trim($DOMAIN);
			if(trim($DOMAIN)==null){continue;}
			$results=array();		
			if($GLOBALS["VERBOSE"]){writelogs("[wbinfo -u]::  Executing for $DOMAIN, please wait",__FUNCTION__,__FILE__,__LINE__);}
			exec("$wbinfo -u --domain=$DOMAIN 2>&1",$results);
			if($GLOBALS["VERBOSE"]){writelogs("[wbinfo -u]:: parsing  ".count($results)." elements for domain $DOMAIN",__FUNCTION__,__FILE__,__LINE__);}
			while (list ($num, $user) = each ($results) ){
				if(preg_match("#^Error looking#", $user,$re)){
					if($GLOBALS["VERBOSE"]){writelogs("[wbinfo -u]:: wbinfo (users) for $DOMAIN failed ",__FUNCTION__,__FILE__,__LINE__);}
					$unix->send_email_events("Error exporting users list with wbinfo", "wbinfo -u $DOMAIN report:\n$ligne\nWill try in next cycle", "system");
					break;
				}		
				$user=addslashes(utf8_encode($user));
				$sql[]="('$user')";
				if(count($sql)>500){
					if($GLOBALS["VERBOSE"]){writelogs("[wbinfo -u]:: Inserting ".count($sql)." elements ",__FUNCTION__,__FILE__,__LINE__);}
					$sqlfinal=$prefix.@implode(",", $sql);
					$q->QUERY_SQL($sqlfinal,"artica_backup");
					if(!$q->ok){echo $q->mysql_error."\n";}
					$sql=array();
				}		
			}
	}
}
	
if(count($sql)>1){
	if($GLOBALS["VERBOSE"]){writelogs("[USERS]:: Inserting ".count($sql)." elements ",__FUNCTION__,__FILE__,__LINE__);}
	$sqlfinal=$prefix.@implode(",", $sql);
	$q->QUERY_SQL($sqlfinal,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."\n";}
	$sql=array();
	}	
	
	if($GLOBALS["VERBOSE"]){writelogs("[getent group]::  Executing, please wait",__FUNCTION__,__FILE__,__LINE__);}	
	exec("$getent group 2>&1",$results);
	if($GLOBALS["VERBOSE"]){writelogs("[getent group]:: parsing  ".count($results)." elements ",__FUNCTION__,__FILE__,__LINE__);}
	$prefix="INSERT IGNORE INTO getent_groups (`group`,`gpid`) VALUES ";
	$q=new mysql();
	$q->QUERY_SQL("TRUNCATE TABLE `getent_groups`","artica_backup");
	while (list ($num, $ligne) = each ($results) ){
		if(preg_match("#^(.+?):.+?([0-9]+):#", $ligne,$re)){
			
			$GPIDADDED[trim($re[1])]=true;
			$re[1]=addslashes(utf8_encode(trim($re[1])));
			$gpid=$re[2];
			
			$sql[]="('{$re[1]}','$gpid')";
			if(count($sql)>500){
				if($GLOBALS["VERBOSE"]){writelogs("[getent group]:: Inserting ".count($sql)." elements ",__FUNCTION__,__FILE__,__LINE__);}
				$sqlfinal=$prefix.@implode(",", $sql);
				$q->QUERY_SQL($sqlfinal,"artica_backup");
				if(!$q->ok){echo $q->mysql_error." line:" . __LINE__."\n";}
				$sql=array();
			}
		}
	}
	
if(count($sql)>0){
	if($GLOBALS["VERBOSE"]){writelogs("[getent group]:: Inserting ".count($sql)." elements ",__FUNCTION__,__FILE__,__LINE__);}
	$sqlfinal=$prefix.@implode(",", $sql);
	$q->QUERY_SQL($sqlfinal,"artica_backup");
	if(!$q->ok){echo $q->mysql_error." line:" . __LINE__."\n";}
	$sql=array();
}	
	
	
	
if(is_ads_connected()){
	if($GLOBALS["VERBOSE"]){writelogs("[net ads]:: is_ads_connected -> TRUE",__FUNCTION__,__FILE__,__LINE__);}
	$net=$unix->find_program("net"); 
	$results=array();
	if($GLOBALS["VERBOSE"]){writelogs("[net ads group]::  Executing, please wait",__FUNCTION__,__FILE__,__LINE__);}	
	exec("$net ads group 2>&1",$results);
	$prefix="INSERT IGNORE INTO getent_groups (`group`) VALUES ";
	while (list ($num, $group) = each ($results) ){
		$group=trim($group);
		if($group==null){continue;}
		if(isset($GPIDADDED[$group])){continue;}
		$group=addslashes(utf8_encode($group));
		$sql[]="('$group')";
		if(count($sql)>500){
			if($GLOBALS["VERBOSE"]){writelogs("[net ads group]:: Inserting ".count($sql)." elements ",__FUNCTION__,__FILE__,__LINE__);}
			$sqlfinal=$prefix.@implode(",", $sql);
			$q->QUERY_SQL($sqlfinal,"artica_backup");
			if(!$q->ok){echo $q->mysql_error."\n";}
			$sql=array();
		}				
	}
}	
	$cmd_domains_list=array();
	$wbinfo=$unix->find_program("wbinfo");	
	$results=array();
	if(is_file($wbinfo)){
			exec("$wbinfo -m 2>&1",$cmd_domains_list);
			if($GLOBALS["VERBOSE"]){writelogs("[wbinfo -m]:: return ". count($cmd_domains_list)." elements",__FUNCTION__,__FILE__,__LINE__);}
			while (list ($num, $DOMAIN) = each ($cmd_domains_list) ){
			$DOMAIN=trim($DOMAIN);
			if(trim($DOMAIN)==null){continue;}
			$results=array();
			if($GLOBALS["VERBOSE"]){writelogs("[wbinfo -g]:: checks domain $DOMAIN, please wait...",__FUNCTION__,__FILE__,__LINE__);}
			exec("$wbinfo -g --domain=$DOMAIN 2>&1",$results);
			if($GLOBALS["VERBOSE"]){writelogs("[wbinfo -g]:: parsing  ".count($results)." elements for domain $DOMAIN,",__FUNCTION__,__FILE__,__LINE__);}
			while (list ($num, $group) = each ($results) ){
				if(preg_match("#^Error looking#", $group,$re)){$unix->send_email_events("Error exporting group list with wbinfo for $DOMAIN", "wbinfo -g  for domain $DOMAIN report:\n$ligne\nWill try in next cycle", "system");break;}
				$group=addslashes(utf8_encode($group));
				$sql[]="('$group')";
				if(count($sql)>500){
					if($GLOBALS["VERBOSE"]){writelogs("[wbinfo -g]:: Inserting ".count($sql)." elements ",__FUNCTION__,__FILE__,__LINE__);}
					$sqlfinal=$prefix.@implode(",", $sql);
					$q->QUERY_SQL($sqlfinal,"artica_backup");
					if(!$q->ok){echo $q->mysql_error."\n";}
					$sql=array();
				}		
			}	
		}
	}
	
if(count($sql)>1){
	if($GLOBALS["VERBOSE"]){writelogs("[GROUPS]:: Inserting ".count($sql)." elements ",__FUNCTION__,__FILE__,__LINE__);}
	$sqlfinal=$prefix.@implode(",", $sql);
	$q->QUERY_SQL($sqlfinal,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."\n";}
	$sql=array();
	}	
// ----------- FIN function
	
}	

function is_ads_connected(){
	$unix=new unix();
	$net=$unix->find_program("net");
	if(!is_file($net)){return false;}
	exec("$net ads info 2>&1",$results);
	while (list ($num, $line) = each ($results) ){
		if(preg_match("#LDAP server name:.+?#", $line)){
			if($GLOBALS["VERBOSE"]){"echo is_ads_connected:: YES -> $line\n";}
			return true;}
	}
	return false;
	
	
}

