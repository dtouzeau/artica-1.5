<?php

include_once(dirname(__FILE__)."/frame.class.inc");
include_once(dirname(__FILE__)."/class.unix.inc");


if(isset($_GET["access-logs"])){access_logs();exit;}






while (list ($num, $line) = each ($_GET)){$f[]="$num=$line";}

writelogs_framework("unable to understand query !!!!!!!!!!!..." .@implode(",",$f),"main()",__FILE__,__LINE__);
die();


function access_logs(){
	$unix=new unix();
	$tail=$unix->find_program("tail");
	$grep=$unix->find_program("grep");
	$search=$_GET["search"];
	if(strlen($search)>1){
		$search=str_replace("*", ".*", $search);
		$cmd="$tail -n 1000 /var/log/squid/access.log|$grep -E \"$search\" 2>&1";
	}else{
		$cmd="$tail -n 500 /var/log/squid/access.log 2>&1";
	}
	
	exec($cmd,$results);
	writelogs_framework($cmd ." ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	
	
	
	
}
