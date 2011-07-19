<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__) . '/ressources/class.users.menus.inc');
include_once(dirname(__FILE__) . '/ressources/class.templates.inc');
include_once(dirname(__FILE__) . '/framework/class.unix.inc');
include_once(dirname(__FILE__) . '/framework/frame.class.inc');
include_once(dirname(__FILE__) . '/ressources/class.ldap.inc');
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}

$cache_file="/etc/artica-postfix/dmidecode.cache";
if(!$GLOBALS["VERBOSE"]){
	if(is_file($cache_file)){
		$mem=file_time_min($cache_file);
		if($mem<240){return null;}
	}
}

$unix=new unix();
$dmidecode=$unix->find_program("dmidecode");
if(!is_file($dmidecode)){die();}


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
if($GLOBALS["VERBOSE"]){print_r($final_array);}
@file_put_contents("$cache_file",serialize($final_array));
