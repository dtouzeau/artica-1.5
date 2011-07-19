<?php
include_once(dirname(__FILE__)."/frame.class.inc");
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
if(isset($_GET["cluster-key"])){CLUSTER_KEY();exit;}

$key=$_GET["key"];
$uid=$_GET["uid"];
$datas=@file_get_contents("/etc/artica-postfix/settings/Daemons/$key");
if($datas==null){
	sys_events(basename(__FILE__)."::{$_SERVER['REMOTE_ADDR']}/$uid:: $key (null)");
}
echo "<articadatascgi>$datas</articadatascgi>";


function CLUSTER_KEY(){
	$key=$_GET["cluster-key"];
	$datas=@file_get_contents("/etc/artica-cluster/$key");
	echo "<articadatascgi>$datas</articadatascgi>";
}

?>