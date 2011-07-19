<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');

$_GET["LOGFILE"]="/usr/share/artica-postfix/ressources/logs/web/interface-postfix.log";
if(!is_file("/usr/share/artica-postfix/ressources/settings.inc")){shell_exec("/usr/share/artica-postfix/bin/process1 --force --verbose");}
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["DEBUG"]=true;}
if(preg_match("#--reload#",implode(" ",$argv))){$GLOBALS["RELOAD"]=true;}

if($argv[1]=="route"){FixRoute();die();}

GetNets();
$unix=new unix();
	shell_exec("/etc/init.d/artica-postfix start hamachi");
	$sql="SELECT * FROM hamachi ORDER BY ID DESC";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$array=unserialize(base64_decode($ligne["pattern"]));
		connect($array);
	}
	
	$users=new usersMenus();
	exec($unix->find_program("hamachi")." -c /etc/hamachi set-nick $users->hostname",$l);
	FixRoute();
	

	
function GetNets(){
	$unix=new unix();
	exec($unix->find_program("hamachi")." -c /etc/hamachi list",$l);
	while (list ($num, $ligne) = each ($l) ){
		if(preg_match("#\[(.+?)\]#",$ligne,$re)){
			$GLOBALS["NETS"][$re[1]]=true;
		}
		
	}
	
}

	
function connect($array){
	if($GLOBALS["NETS"][$array["NETWORK"]]){return true;}
	
	
	switch ($array["TYPE"]) {
		case "JOIN_NET":JOIN_NET($array);break;
		case "CREATE_NET":CREATE_NET($array);break;
		default:
			;
		break;
	}
	
	
	
}

function JOIN_NET($array){
	$unix=new unix();
	exec($unix->find_program("hamachi")." -c /etc/hamachi login",$l);
	echo implode("\n",$l);
	
	exec($unix->find_program("hamachi")." -c /etc/hamachi join {$array["NETWORK"]} {$array["PASSWORD"]}",$l);
	echo implode("\n",$l);		
	
	exec($unix->find_program("hamachi")." -c /etc/hamachi go-online {$array["NETWORK"]}",$l);
	echo implode("\n",$l);			
	FixRoute();
	
}

function CREATE_NET($array){
	exec($unix->find_program("hamachi")." -c /etc/hamachi create {$array["NETWORK"]} {$array["PASSWORD"]}",$l);
}


function hamachi_currentIP(){
	
	$datas=explode("\n",@file_get_contents("/etc/hamachi/state"));
	while (list ($num, $ligne) = each ($datas) ){
		if(preg_match("#Identity\s+([0-9\.]+)#",$ligne,$re)){
			return $re[1];
			break;
		}
	}
	
}


function FixRoute(){
	$ip=hamachi_currentIP();
	if($ip==null){return;}
	
	$unix=new unix();
	exec($unix->find_program("route"),$results);
	while (list ($num, $ligne) = each ($results) ){
		if(preg_match("#([0-9\.]+)\s+([0-9\.\*]+).+?\s+([0-9\.]+)\s+[A-Z]+.+ham0#",$ligne,$re)){
			if(trim($re[2])<>$ip){
				shell_exec("route del -net 5.0.0.0 gw 0.0.0.0 netmask 255.0.0.0 dev ham0");
				shell_exec("route add -net 5.0.0.0 gw $ip netmask 255.0.0.0 dev ham0");
			}
			
		}
	}
}


?>