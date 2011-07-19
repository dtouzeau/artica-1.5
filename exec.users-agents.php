<?php
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/ressources/class.users.menus.inc');
$GLOBALS["ADDLOG"]="/var/log/artica-postfix/".basename(__FILE__).".log";
$users=new usersMenus();
if(!$users->SQUID_INSTALLED){die();}
$file="/usr/share/artica-postfix/ressources/databases/UserAgents.txt";
if(!is_file($file)){die();}
$pidfile="/etc/artica-postfix/".basename(__FILE__).".pid";
$unix=new unix();
if($unix->process_exists(trim(@file_get_contents($pidfile)))){
	writelogs("Another instance ". @file_get_contents($pidfile). " Exists... abort","MAIN",__FILE__,__LINE__);
	die();
}
$pid=getmypid();
@file_put_contents($pidfile,$pid);

$time=file_time_min("/etc/artica-postfix/UserAgents.cache");
if($time<10080){die();}


$f=@file_get_contents($file);

$md5=md5($f);
$oldMd5=md5(trim(@file_get_contents("/etc/artica-postfix/UserAgents.cache")));
writelogs("$md5 == $oldMd5","MAIN",__FILE__,__LINE__);
if($md5==$oldMd5){
	writelogs("No changes","MAIN",__FILE__,__LINE__);
	die();
}
@file_put_contents("/etc/artica-postfix/UserAgents.cache","$md5");

$q=new mysql();
$q->BuildTables();
$datas=explode("\n",$f);
writelogs(count($f)." Lines to parse","MAIN",__FILE__,__LINE__);


while (list ($index, $line) = each ($datas) ){
	if(trim($line)==null){continue;}
	if(strpos($line,'*')==0){
	if(preg_match("#^([A-Za-z0-9\s\.]+)#",$line,$re)){
		$key=$re[1];
		echo $key."\n";
		continue;
	}}
	
	if(preg_match("#\s+\*(.+)#",$line,$re)){
		$array[$key][md5($re[1])]=$re[1];
		continue;
	}
	
}

if(!is_array($array)){die();}
$ct=0;
$sqlintro="INSERT INTO UserAgents(unique_key,browser,string) VALUES ";
while (list ($prodct, $newarray) = each ($array) ){
	while (list ($unique_key, $string) = each ($newarray) ){
		$fi[]="('$unique_key','$prodct','$string')";
		usleep(20000);
		$ct++;
	
		if($ct>500){
			$ct=0;
			$sql=$sqlintro.@implode(",",$fi);
			$q->QUERY_SQL($sql,"artica_backup");
			unset($fi);
			$fi=array();
		}
		
	}
	
}

if(is_array($fi)){
		writelogs(count($fi)." queries","MAIN",__FILE__,__LINE__);
		$sql=$sqlintro.@implode(",",$fi);
		$q->QUERY_SQL($sql,"artica_backup");
}





?>