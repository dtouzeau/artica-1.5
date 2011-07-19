<?php

include_once(dirname(__FILE__)."/frame.class.inc");
include_once(dirname(__FILE__)."/class.unix.inc");


if(isset($_GET["smbd-logs"])){smbdlogs();exit;}
if(isset($_GET["testparm"])){testparm();exit;}
if(isset($_GET["idof"])){idof();exit;}
if(isset($_GET["wins-list"])){winsdat();exit;}






while (list ($num, $line) = each ($_GET)){$f[]="$num=$line";}

writelogs_framework("unable to understand query !!!!!!!!!!!..." .@implode(",",$f),"main()",__FILE__,__LINE__);
die();



function idof(){
	$uid=$_GET["idof"];
$unix=new unix();
	$id=$unix->find_program("id");
	if(!is_file($id)){
		echo "<articadatascgi>". base64_encode("id, no such binary")."</articadatascgi>";
		return;
	}	
	
	$cmd="$id $uid 2>&1";
	exec($cmd,$results);
	writelogs_framework("$cmd = " . count($results)." rows",__FUNCTION__,__FILE__,__LINE__);	
	echo "<articadatascgi>". base64_encode(implode(" ",$results))."</articadatascgi>";	
	
}

function testparm(){
	$hostname=$_GET["testparm"];
	if($hostname<>null){$L=" -L $hostname ";}
	$unix=new unix();
	$testparm=$unix->find_program("testparm");
	if(!is_file($testparm)){
		echo "<articadatascgi>". base64_encode(serialize(array("testparm, no such binary")))."</articadatascgi>";
		return;
	}
	
	$cmd="$testparm -s -v $L 2>&1";
	exec($cmd,$results);
	writelogs_framework("$cmd = " . count($results)." rows",__FUNCTION__,__FILE__,__LINE__);	
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
	
}


function smbdlogs(){
	$unix=new unix();
	$search=base64_decode($_GET["search"]);
	$search=str_replace("***", "*", $search);
	$search=str_replace("**", "*", $search);
	$rows=$_GET["rows"];
	if($search=="*"){$search=null;}
	$tail=$unix->find_program("tail");
	$grep=$unix->find_program("grep");
	if($search==null){
		$cmd="$tail -n $rows /var/log/samba/log.smbd 2>&1";
	}else{
		$search=str_replace(".", "\.", $search);
		$search=str_replace("*", ".*?", $search);
		$search=str_replace("/", "\/", $search);
		$cmd="$grep -i -E \"$search\" /var/log/samba/log.smbd|$tail -n $rows 2>&1";
	}
	exec($cmd,$results);
	writelogs_framework("$cmd = " . count($results)." rows",__FUNCTION__,__FILE__,__LINE__);	
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";		
	
}

function winsdat(){
	$unix=new unix();
	$dat="/var/lib/samba/wins.dat";
	if(!is_file($dat)){
		echo "<articadatascgi>". base64_encode(serialize(array("\"Failed\" unable to stat $dat")))."</articadatascgi>";
		return;
		
	}
	$search=$_GET["search"];
	$search=str_replace("***", "*", $search);
	$search=str_replace("**", "*", $search);
	$rows=$_GET["rows"];
	if($search=="*"){$search=null;}
	$tail=$unix->find_program("tail");
	$grep=$unix->find_program("grep");
	if($search==null){
		$cmd="$tail -n 500 $dat 2>&1";
	}else{
		$search=str_replace(".", "\.", $search);
		$search=str_replace("*", ".*?", $search);
		$search=str_replace("/", "\/", $search);
		$cmd="$grep -i -E \"$search\" $dat|$tail -n 500 2>&1";
	}
	exec($cmd,$results);
	writelogs_framework("$cmd = " . count($results)." rows",__FUNCTION__,__FILE__,__LINE__);	
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";		
		
	
}

