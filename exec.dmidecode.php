<?php
$GLOBALS["FORCE"]=false;
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__) . '/ressources/class.users.menus.inc');
include_once(dirname(__FILE__) . '/ressources/class.templates.inc');
include_once(dirname(__FILE__) . '/framework/class.unix.inc');
include_once(dirname(__FILE__) . '/framework/frame.class.inc');
include_once(dirname(__FILE__) . '/ressources/class.ldap.inc');
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;$GLOBALS["DEBUG"]=true;$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}
if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;}


if($argv[1]=="--chassis"){
	if(!$GLOBALS["FORCE"]){
		if(is_file("/etc/artica-postfix/dmidecode.cache")){
			$datas=@file_get_contents("/etc/artica-postfix/dmidecode.cache");
			$newdatas=urlencode(base64_encode($datas));
			@file_put_contents("/etc/artica-postfix/dmidecode.cache.url", $newdatas);
			die();
		}
	}
	
}


$cache_file="/etc/artica-postfix/dmidecode.cache";
if(!$GLOBALS["VERBOSE"]){
	if(is_file($cache_file)){
		$mem=file_time_min($cache_file);
		if($mem<240){return null;}
	}
}

$unix=new unix();
$dmidecode=$unix->find_program("dmidecode");
$virtwhat=$unix->find_program("virt-what");
if(is_file($dmidecode)){
	exec("$dmidecode --type 1 2>&1",$results);
	while (list ($index, $line) = each ($results) ){
		
		if(preg_match("#Manufacturer:\s+(.+)#",$line,$re)){
			$Manufacturer=$re[1];
		}
		
		if(preg_match("#Product Name:\s+(.+)#",$line,$re)){
			$ProductName=$re[1];
		}	
	}
	unset($results);
	exec("$dmidecode --type 3 2>&1",$results);
	while (list ($index, $line) = each ($results) ){
		
		if(preg_match("#Manufacturer:\s+(.+)#",$line,$re)){
			$chassisManufacturer=$re[1];
		}
		
		
	}
	
}
$PROCS=array();
unset($results);
$f=@explode("\n",@file_get_contents("/proc/cpuinfo"));
while (list ($index, $line) = each ($f) ){
	if(preg_match("#processor\s+:\s+([0-9]+)#",$line,$re)){
		$proc=$re[1];
	}
	
	if(preg_match("#model name\s+:\s+(.+)#",$line,$re)){
		$PROCS[$proc]["MODEL"]=trim($re[1]);
		$PROCS[$proc]["MODEL"]=str_replace("  "," ",$PROCS[$proc]["MODEL"]);
	}
	if(preg_match("#cpu MHz\s+:\s+([0-9]+)#",$line,$re)){
		$found=$re[1];
		if($GLOBALS["VERBOSE"]){echo "Proc:$proc -> $found MHZ\n";}
		$found=$found/1000;
		if($GLOBALS["VERBOSE"]){echo "Proc:$proc -> $found MHZ\n";}
		$PROCS[$proc]["MHZ"]=round($found,2);
		if($GLOBALS["VERBOSE"]){echo "Proc:$proc -> {$PROCS[$proc]["MHZ"]} GHZ\n";}
	}
}




$final_array["MANUFACTURER"]=$Manufacturer;
$final_array["PRODUCT"]=$ProductName;
$final_array["CHASSIS"]=$chassisManufacturer;
$final_array["PROCESSORS"]=count($PROCS);
$final_array["MHZ"]=$PROCS[0]["MHZ"];
$final_array["PROC_TYPE"]=$PROCS[0]["MODEL"];

if(is_file($virtwhat)){
	exec("$virtwhat 2>&1",$virtwhatA);
	$virtwhatB=trim(@implode("", $virtwhatA));
	
	if($virtwhatB<>null){
		if($GLOBALS["VERBOSE"]){echo "$virtwhat -> $virtwhatB\n";}
		$final_array["MANUFACTURER"]=$virtwhatB;
		$final_array["PRODUCT"]=$virtwhatB;
		$final_array["CHASSIS"]=$virtwhatB;
	}
}

if($GLOBALS["VERBOSE"]){print_r($final_array);}
$newdatas=urlencode(base64_encode(serialize($final_array)));
@file_put_contents("$cache_file",serialize($final_array));
@file_put_contents("/etc/artica-postfix/dmidecode.cache.url",$newdatas);



