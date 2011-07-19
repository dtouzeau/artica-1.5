<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");
if(posix_getuid()<>0){
	die("Cannot be used in web server mode\n\n");
}


if(!is_file("/etc/hosts")){
	write_syslog("Unable to stat /etc/hosts, aborting",__FILE__);
	exit(0);
}


RoundRobin();


function RoundRobin(){
	$filename="/etc/artica-postfix/settings/Daemons/RoundRobinHosts";
	if(!is_file($filename)){
		write_syslog("no round robin servers set",__FILE__);
	}
	$DisableEtcHosts=@file_get_contents("/etc/artica-postfix/settings/Daemons/DisableEtcHosts");
	if($DisableEtcHosts==1){
		write_syslog("Unable to open /etc/hosts, DisableEtcHosts is active aborting",__FILE__);
		return;
	}
	
	
	RoundRobinClean();
	RoundRobinClean();
	$ini=new Bs_IniHandler($filename);
	if(!is_array($ini->_params)){return null;}
	while (list ($num, $ligne) = each ($ini->_params) ){
		if($num==null){continue;}
		if($ini->_params[$num]["IP"]<>null){
			$ips=explode(",",$ini->_params[$num]["IP"]);
			$server=$ini->_params[$num]["servername"];
			$array=RoundRobinIncrement($ips,$server,$array);
		}
	}
	
	if(is_array($array)){
		$hosts=file_get_contents("/etc/hosts");
		$hosts=str_replace("\n\n","\n",$hosts);
		$hosts=$hosts . "\n";
		$hosts=$hosts . "# Round Robin (added by Artica) dont touch this line !!...\n";
		$hosts=$hosts . implode("\n",$array);
		$hosts=$hosts . "\n";
		$hosts=$hosts . "# EOF Round Robin (added by Artica) dont touch this line !...\n";
		@file_put_contents("/etc/hosts",$hosts);
	}
	
	
	
}

function RoundRobinIncrement($ips,$servername,$array){
	while (list ($num, $ligne) = each ($ips) ){
		if($ligne==null){continue;}
		$array[]="$ligne      $servername      $servername";
		}
	return $array;
	}

function RoundRobinClean(){
	$DisableEtcHosts=trim(@file_get_contents("/etc/artica-postfix/settings/Daemons/DisableEtcHosts"));
	if($DisableEtcHosts==1){
		write_syslog("Unable to open /etc/hosts, DisableEtcHosts is active aborting",__FILE__);
		return;
	}	
	
$datas=@file_get_contents("/etc/hosts");
if(strlen($datas)==0){return null;}
$tbl=explode("\n",$datas);
$start=false;
while (list ($num, $ligne) = each ($tbl) ){
	if(preg_match("#^\#.*Round Robin#",$ligne)){$start=true;unset($tbl[$num]);}
	if($start){
		if(preg_match("#^\#.*EOF#",$ligne)){
			unset($tbl[$num]);
			break;
		}else{
			unset($tbl[$num]);
		}
		
		
	}
	
}

$file=implode("\n",$tbl);
@file_put_contents("/etc/hosts",$file);
shell_exec(LOCATE_PHP5_BIN2()." ". dirname(__FILE__)."/exec.samba.php --fix-etc-hosts");

}











?>