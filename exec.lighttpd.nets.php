<?php
include_once(dirname(__FILE__)."/ressources/class.mysql.inc");
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");

if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;$GLOBALS["debug"]=true;$GLOBALS["DEBUG"]=true;}
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}



$file="/etc/artica-postfix/settings/Daemons/LighttpdNets";
if(!is_file("/etc/artica-postfix/settings/Daemons/LighttpdNets")){
	@unlink("/etc/artica-postfix/lighttpd_nets");
	die();
}

$LighttpdNets=unserialize(base64_decode(@file_get_contents($file)));


if(is_array($LighttpdNets["IPS"])){
	while (list ($num, $ligne) = each ($LighttpdNets["IPS"]) ){
		if(trim($ligne)==null){continue;}
		if($GLOBALS["VERBOSE"]){echo "$ligne\n";}
		$nets[$ligne]=$ligne;
	}
}
if(is_array($LighttpdNets["NETS"])){
	while (list ($num, $ligne) = each ($LighttpdNets["NETS"]) ){
		if(trim($ligne)==null){continue;}
		if(preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+).([0-9]+)\/([0-9]+)#",$ligne,$re)){
			$newip="{$re[1]}.{$re[2]}.{$re[3]}.*";
			if($GLOBALS["VERBOSE"]){echo "$newip\n";}
			$nets[$newip]=$newip;
		}else{
			if($GLOBALS["VERBOSE"]){echo "No match $ligne\n";}
		}
	}
}

	$sql="SELECT * FROM glusters_clients ORDER BY ID DESC";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if(trim($ligne["client_ip"])==null){continue;}
		$nets[$ligne["client_ip"]]=$ligne["client_ip"];
	}

if(!is_array($nets)){
	@unlink("/etc/artica-postfix/lighttpd_nets");
	die();	
}

while (list ($num, $ligne) = each ($nets) ){
	$f[]=$ligne;
	
}

$content="\$HTTP[\"remoteip\"] !~ \"".@implode("|",$f)."\"{   url.access-deny = ( \"\" ) }";

if($GLOBALS["VERBOSE"]){echo $content."\n";}
@file_put_contents("/etc/artica-postfix/lighttpd_nets",$content);



?>