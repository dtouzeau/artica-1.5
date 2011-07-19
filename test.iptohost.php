<?php
	include_once('ressources/class.computers.inc');
	include_once('ressources/class.pdns.inc');
	
	
	$ip=$argv[1];
	
	
	$pdns=new pdns();
	
	print_r($pdns->IpToHosts($ip));
	
	echo "gethostbyaddr():". gethostbyaddr($ip)."\n";
	
	
	
	
	
	
	
	
?>