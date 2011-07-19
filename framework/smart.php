<?php

include_once(dirname(__FILE__)."/frame.class.inc");
include_once(dirname(__FILE__)."/class.unix.inc");


if(isset($_GET["status"])){smartctl_i();exit;}
if(isset($_GET["health"])){smartctl_A();exit;}

while (list ($num, $line) = each ($_GET)){$f[]="$num=$line";}

writelogs_framework("unable to understand query !!!!!!!!!!!..." .@implode(",",$f),"main()",__FILE__,__LINE__);
die();


function smartctl_i(){
	$unix=new unix();
	$smartctl=$unix->find_program("smartctl");
	$cmd="$smartctl -i {$_GET["status"]} 2>&1";
	exec("$cmd",$results);
	writelogs_framework("$cmd ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	$array["{disk}"]=$_GET["status"];
	while (list ($num, $line) = each ($results)){
		if(preg_match("#Copyright#",$line)){continue;}
		if(preg_match("#(.+?):(.+)#",$line,$re)){$array[$re[1]]=$re[2];}
	}	
	writelogs_framework("RETURN -> ". count($array)." rows",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>".base64_encode(serialize($array))."</articadatascgi>";	
}

function smartctl_A(){
	$unix=new unix();
	$smartctl=$unix->find_program("smartctl");
	$cmd="$smartctl -A {$_GET["health"]} 2>&1";
	exec("$cmd",$results);
	writelogs_framework("$cmd ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);	
	while (list ($num, $line) = each ($results)){
		if(preg_match("#([0-9]+)\s+(.+?)\s+([0-9a-z]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+(.+?)\s+(.+?)\s+([0-9\-]+)\s+(.+?)$#",$line,$re)){
			$array[$re[1]]=array(
			"ATTRIBUTE"=>$re[2],
			"FLAG"=>$re[3],
			"VALUE"=>$re[4],
			"WORST"=>$re[5],
			"TRESH"=>$re[6],
			"TYPE"=>$re[7],
			"UPDATED"=>$re[8],
			"WHEN_FAILED"=>$re[9],
			"RAW_VALUE"=>$re[10]
			);
		}else{
			writelogs_framework("FAILED -> $line",__FUNCTION__,__FILE__,__LINE__);
		}
		
	}
	
	writelogs_framework("RETURN -> ". count($array)." rows",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>".base64_encode(serialize($array))."</articadatascgi>";	
}