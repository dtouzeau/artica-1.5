<?php
include(dirname(__FILE__).'/ressources/class.qos.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/ressources/class.http.pear.inc');
include_once(dirname(__FILE__).'/ressources/class.artica-meta.inc');
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__).'/ressources/class.computers.inc');
include_once(dirname(__FILE__).'/ressources/class.sockets.inc');

	
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
if(preg_match("#--output#",implode(" ",$argv))){$GLOBALS["OUTPUT"]=true;}
if(preg_match("#--uid\s+([a-zA-Z0-9\.@\-_]+)#",implode(" ",$argv),$re)){$GLOBALS["USER_QUERY"]=$re[1];}
if($argv[1]=="--export-all"){export_all_users();exit;}
if($argv[1]=="--export-all-domains"){export_all_domains();exit;}
if($argv[1]=="--export-all-ou"){export_all_ou();export_all_groups();export_all_users();exit;}
if($argv[1]=="--export-all-groups"){export_all_groups();exit;}
if($argv[1]=="--export-all-settings"){export_all_settings();exit;}
if($argv[1]=="--export-all-computers"){export_all_computers();exit;}
if($argv[1]=="--export-all-dns"){export_dns();exit;}
if($argv[1]=="--export-freewebs"){export_freeweb();exit;}
if($argv[1]=="--export-awstats"){export_awstats();exit;}
if($argv[1]=="--export-awstats-files"){export_awstats_files();exit;}
if($argv[1]=="--export-all-groupwares"){export_all_groupwares();exit;}
if($argv[1]=="--export-postfix-events"){export_postfix_events();exit;}
if($argv[1]=="--export-virtualbox-logs"){export_virtualbox_logs();exit;}
if($argv[1]=="--export-fetchmail-rules"){export_fetchmail_rules();exit;}
if($argv[1]=="--export-openvpn-logs"){export_openvpn_logs();exit;}
if($argv[1]=="--export-openvpn-users"){export_openvpn_users();exit;}
if($argv[1]=="--export-openvpn-sites"){export_openvpn_rsites();exit;}
if($argv[1]=="--socks"){export_socks();exit;}
if($argv[1]=="--user"){export_user($argv[2]);exit;}
if($argv[1]=="--user-queue"){export_user_queue($argv[2]);exit;}
if($argv[1]=="--computer-queue"){export_computer_queue($argv[2]);exit;}
if($argv[1]=="--computer"){export_computer($argv[2]);exit;}
if($argv[1]=="--ovpn"){export_openvpn_single_user($argv[2]);exit;}
if($argv[1]=="--iptables"){export_iptables();exit;}

function export_user($uid){
	$file="/etc/artica-postfix/artica-meta-queue-socks/$uid.usr";
	$user=new user($uid);
	foreach($user as $key => $value) {$array[$key]=$value;}	
	if($GLOBALS["VERBOSE"]){print_r($array);}
	
	if(is_file($file)){
		@file_put_contents($file,base64_encode(serialize($array)));
		events("User $uid replication command is already scheduled",__FUNCTION__,__FILE__,__LINE__);
		return;
	}
	@file_put_contents($file,base64_encode(serialize($array)));
	if(export_user_perform($file)){@unlink($file);}
}

function export_computer($uid){
	$file="/etc/artica-postfix/artica-meta-queue-socks/$uid.comp";
	if(strpos($uid,'$')==0){$uid="$uid$";}
	$comp=new computers($uid);
	foreach($comp as $key => $value) {$array[$key]=$value;}	
	if(is_file($file)){
		@file_put_contents($file,base64_encode(serialize($array)));
		events("Computer $uid replication command is already scheduled",__FUNCTION__,__FILE__,__LINE__);
		return;
	}	
	
	@file_put_contents($file,base64_encode(serialize($array)));
	if(export_computer_perform($file)){@unlink($file);}
	
	
}


function export_openvpn_users(){
	$users=new usersMenus();
	if(!$users->OPENVPN_INSTALLED){
		events("OpenVPN is not installed",__FUNCTION__,__FILE__,__LINE__);
		return null;
	}
	$sock=new sockets();
	if($sock->EnableOPenVPNServerMode<>1){events("This server is not an OpenVPN server",__FUNCTION__,__FILE__,__LINE__);}
	$ldap=new clladp();
	$users=$ldap->Hash_GetALLUsers();
	while (list ($uid, $email) = each ($users) ){
		_export_openvpn_single_user($uid);
	}
	
	
}
function export_openvpn_single_user($uid){
	$users=new usersMenus();
	if(!$users->OPENVPN_INSTALLED){
		events("OpenVPN is not installed",__FUNCTION__,__FILE__,__LINE__);
		return null;
	}
	$sock=new sockets();
	if($sock->EnableOPenVPNServerMode<>1){events("This server is not an OpenVPN server",__FUNCTION__,__FILE__,__LINE__);}
	_export_openvpn_single_user($uid);
}

function _export_ovpn($uid){
		include_once(dirname(__FILE__).'/ressources/class.openvpn.inc');
		$vpn=new openvpn();
		$config=$vpn->BuildClientconf($uid);
		@file_put_contents("/etc/artica-postfix/settings/Daemons/$uid.ovpn",$config);
		@mkdir("/var/cache/openvpn-users",0666,true);
		$sock=new sockets();
		$datas=$sock->getFrameWork("openvpn.php?build-vpn-user=$uid&basepath=/var/cache/openvpn-users");
		
		$tbl=explode("\n",$datas);
		$tbl=array_reverse($tbl);
		while (list ($num, $line) = each ($tbl) ){
			if(trim($line)==null){continue;}
			events("$line",__FUNCTION__,__FILE__,__LINE__);
			$GLOBALS["html_logs"][]=$line;
		
	}		
		
		if(!is_file("/var/cache/openvpn-users/ressources/logs/$uid.zip")){return false;}
	@unlink("/etc/artica-postfix/settings/Daemons/$uid.ovpn");
	return "/var/cache/openvpn-users/ressources/logs/$uid.zip";
	
}

function _export_openvpn_single_user($uid){
	$ovpnfile=_export_ovpn($uid);
	if(!$ovpnfile){
			send_email_events("User $uid failed to export vpn configuration to global Management console (CORRUPTED) datas",@implode("\n",$GLOBALS["html_logs"]),"CLOUD");
			events("User $uid failed to export vpn configuration to global Management console (CORRUPTED) datas",__FUNCTION__,__FILE__,__LINE__);
			return;
		}
	
	$class_user=new user($uid);
	shell_exec("/bin/cp $ovpnfile /var/cache/openvpn-users/$class_user->mail.zip");
	@unlink($ovpnfile);
	$meta=new artica_meta();	
	$http=new httpget();
	$http->uploads["OVPN"]="/var/cache/openvpn-users/$class_user->mail.zip";
	$body=$http->send("$meta->ArticaMetaHostname/lic.users.import.php","post",array("DATAS"=>base64_encode(serialize($meta->GLOBAL_ARRAY)),"uid"=>$uid,"mail"=>$class_user->mail));
	@unlink("/var/cache/openvpn-users/$class_user->mail.zip");
	
	
	if(!preg_match("#<RESULTS>OK</RESULTS>#is",$body)){	
		return true;
		
	}	
	return false;
	
}


function export_user_perform($filename){
	$http=new httpget();
	$sock=new sockets();
	$meta=new artica_meta();	
	$array=unserialize(base64_decode(@file_get_contents($filename)));
	
	$http->uploads["SINGLE_USER"]=$filename;
	$body=$http->send("$meta->ArticaMetaHostname/lic.users.import.php","post",array("DATAS"=>base64_encode(serialize($meta->GLOBAL_ARRAY))));
	if(!preg_match("#<RESULTS>OK</RESULTS>#is",$body)){
		
		if(preg_match("#<RESULTS>CORRUPTED</RESULTS>#is",$body)){
			events("User {$array["uid"]} failed to be exported to global Management console (CORRUPTED) datas",__FUNCTION__,__FILE__,__LINE__);
			send_email_events("User {$array["uid"]} failed to be exported to global Management console (CORRUPTED) datas",null,"CLOUD");
			return true;
		}
		if(preg_match("#<RESULTS>ERROR:(.*?)</RESULTS>#is",$body,$re)){
			events("User {$array["uid"]} failed to be exported to global Management console {$re[1]}",__FUNCTION__,__FILE__,__LINE__);
			send_email_events("User {$array["uid"]} failed to be exported to global Management console (CORRUPTED) datas", $re[1],"CLOUD");
			return false;
		}
		
		
		events("User {$array["uid"]} failed to be exported to global Management console",__FUNCTION__,__FILE__,__LINE__);
		return false;
	}

	send_email_events("success exporting user {$array["uid"]} informations to global Management console",null,"CLOUD");
	return true;
	}

function export_computer_perform($filename){
	$http=new httpget();
	$sock=new sockets();
	$meta=new artica_meta();	
	$array=unserialize(base64_decode(@file_get_contents($filename)));
	
	$http->uploads["SINGLE_COMPUTER"]=$filename;
	$body=$http->send("$meta->ArticaMetaHostname/lic.users.import.php","post",array("DATAS"=>base64_encode(serialize($meta->GLOBAL_ARRAY))));
	if(!preg_match("#<RESULTS>OK</RESULTS>#is",$body)){
		
		if(preg_match("#<RESULTS>CORRUPTED</RESULTS>#is",$body)){
			events("Computer {$array["uid"]} failed to be exported to global Management console (CORRUPTED) datas",__FUNCTION__,__FILE__,__LINE__);
			send_email_events("Computer {$array["uid"]} failed to be exported to global Management console (CORRUPTED) datas",null,"CLOUD");
			return true;
		}
		if(preg_match("#<RESULTS>ERROR:(.*?)</RESULTS>#is",$body,$re)){
			events("Computer {$array["uid"]} failed to be exported to global Management console {$re[1]}",__FUNCTION__,__FILE__,__LINE__);
			send_email_events("Computer {$array["uid"]} failed to be exported to global Management console (CORRUPTED) datas", $re[1],"CLOUD");
			return false;
		}
		
		
		events("Computer {$array["uid"]} failed to be exported to global Management console",__FUNCTION__,__FILE__,__LINE__);
		return false;
	}

	send_email_events("success exporting Computer {$array["uid"]} informations to global Management console",null,"CLOUD");
	return true;
	}


function export_user_queue(){
	$unix=new unix();
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$oldpid=@file_get_contents($pidfile);
	if($unix->process_exists($oldpid)){		
		$ptime=$unix->PROCESS_TTL($oldpid);
		if($ptime>$GLOBALS["MAXTTL"]){
			events("export_user_queue():: killing process $oldpid ttl:$ptime minutes");
			shell_exec("/bin/kill -9 $oldpid");
		}else{
			events("export_user_queue():: already executed, process $oldpid");
			die();
		}
	}


	@file_put_contents($pidfile,getmypid());	
	foreach (glob("/etc/artica-postfix/artica-meta-queue-socks/*.usr") as $filename) {
		if(export_user_perform($filename)){@unlink($filename);}
	}		
}

function export_computer_queue(){
	$unix=new unix();
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$oldpid=@file_get_contents($pidfile);
	if($unix->process_exists($oldpid)){		
		$ptime=$unix->PROCESS_TTL($oldpid);
		if($ptime>$GLOBALS["MAXTTL"]){
			events("export_computer_queue():: killing process $oldpid ttl:$ptime minutes");
			shell_exec("/bin/kill -9 $oldpid");
		}else{
			events("export_computer_queue():: already executed, process $oldpid");
			die();
		}
	}


	@file_put_contents($pidfile,getmypid());	
	foreach (glob("/etc/artica-postfix/artica-meta-queue-socks/*.comp") as $filename) {
		if(export_computer_perform($filename)){@unlink($filename);}
	}		
	
}



function export_all_users(){
$unix=new unix();
$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
$oldpid=@file_get_contents($pidfile);
if($unix->process_exists($oldpid)){		
	$ptime=$unix->PROCESS_TTL($oldpid);
	if($ptime>$GLOBALS["MAXTTL"]){events("SendStatus():: killing process $oldpid ttl:$ptime minutes");shell_exec("/bin/kill -9 $oldpid");}else{events("SendStatus():: already executed, process $oldpid");die();}
}
@file_put_contents($pidfile,getmypid());

if(strlen($GLOBALS["USER_QUERY"])>0){$filter="(uid={$GLOBALS["USER_QUERY"]})";}
	
$ldap=new clladp();
$pattern="(&(objectclass=userAccount)$filter)";
$attr=array();
$sr =@ldap_search($ldap->ldap_connection,"dc=organizations,$ldap->suffix",$pattern,$attr);
$hash=ldap_get_entries($ldap->ldap_connection,$sr);
$unix=new unix();
$gzip=$unix->find_program("gzip");

$users_array=array();

if(is_array($hash)){
	for($i=0;$i<$hash["count"];$i++){
		$usersArray[]=$hash[$i]["uid"][0];
		
	}
}
	if(is_array($usersArray)){
		while (list ($index, $uid) = each ($usersArray) ){
			echo "Parsing $uid\n";
			$u=new user($uid);
			$array_user=array();
			foreach($u as $key => $value) {$array_user[$key]=$value;}
			$array_final[]=$array_user;
			unset($array_user);
		}
	}
	
	
	$exported="/etc/artica-postfix/allusers.cache";	
	$datas=base64_encode(serialize($array_final));
	
	@file_put_contents($exported,$datas);
	events("export: $exported ".str_replace("&nbsp;"," ",FormatBytes(strlen($exported)/1024)),__FUNCTION__,__FILE__,__LINE__);
	$gzipped="$exported.gz";
	shell_exec("$gzip -c $exported  > $gzipped");
	$taille=str_replace("&nbsp;"," ",FormatBytes(filesize($gzipped)/1024));
	events("$gzipped: compressed: $taille",__FUNCTION__,__FILE__,__LINE__);
	$http=new httpget();
	$sock=new sockets();
	$meta=new artica_meta();
	$http->uploads["ALL_USERS_ARRAY"]=$gzipped;
	$body=$http->send("$meta->ArticaMetaHostname/lic.users.import.php","post",array("DATAS"=>base64_encode(serialize($meta->GLOBAL_ARRAY))));
	echo $body;
	
	@unlink($gzipped);		
	
	
	events(count($array_final). "users exported to global Management console",__FUNCTION__,__FILE__,__LINE__);
	send_email_events(count($array_final). " users exported to global Management console","","CLOUD");
	export_freeweb();
	
}

function user_groups_list($uid){
			$ldap=new clladp();
			$attrs=array('gidNumber','displayName','cn');
			$sr =@ldap_search($ldap->ldap_connection,$ldap->suffix,"(&(objectClass=posixGroup)(memberUid=$uid))",$attrs);	
			if($sr){
				$result = ldap_get_entries($ldap->ldap_connection, $sr);
				if(!is_array($result)){return array();}
				for($i=0;$i<$result["count"];$i++){
					if($result[$i]["displayname"][0]==null){$result[$i]["displayname"][0]=$result[$i]["cn"][0];}
					$res[$result[$i]["gidnumber"][0]]=$result[$i]["displayname"][0];
				}
					
			}
	return $res;
				
}

function export_all_domains(){
	$unix=new unix();
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$oldpid=@file_get_contents($pidfile);
	if($unix->process_exists($oldpid)){die();}
	@file_put_contents($pidfile,getmypid());	
	$ldap=new clladp();
	$array_final=array();
	$array=$ldap->hash_get_all_domains();
	if(!is_array($array)){return;}
	while (list ($index, $none) = each ($array) ){
		$ou=$ldap->ou_by_smtp_domain($index);
		$array_final[trim(strtolower($index))]["ou"]=$ou;
		$array_final[trim(strtolower($index))]["TYPE"]="LOCAL";
		$ous[]=$ou;
	}
	
	if(!is_array($ous)){return null;}
	
	while (list ($index, $ou) = each ($ous) ){
		$hash=$ldap->Hash_relay_domains($ou);
		if(is_array($hash)){
			while (list ($domain, $transport) = each ($hash) ){
				$array_final[trim(strtolower($domain))]["TYPE"]=$transport;
			}
		}
	}
	
	if(!is_array($array_final)){return null;}
	echo "exporting ".count($array_final)." domains in \"/etc/artica-postfix/metadomains.cache\"\n";	
	$datas=base64_encode(serialize($array_final));
	@file_put_contents("/etc/artica-postfix/metadomains.cache",$datas);
	$http=new httpget();
	$sock=new sockets();
	$meta=new artica_meta();
	$http->uploads["ALL_DOMAINS"]="/etc/artica-postfix/metadomains.cache";
	$body=$http->send("$meta->ArticaMetaHostname/lic.users.import.php","post",array("DATAS"=>base64_encode(serialize($meta->GLOBAL_ARRAY))));
	events(count($array_final)." domains exported to global Management console",__FUNCTION__,__FILE__,__LINE__);
	send_email_events(count($array_final)." domains exported to global Management console",$body,"CLOUD");
	echo $body;	
	
}


function export_all_ou(){
	$unix=new unix();
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$oldpid=@file_get_contents($pidfile);
	if($unix->process_exists($oldpid)){
		events("$oldpid already running",__FUNCTION__,__FILE__,__LINE__);
		die();}
	@file_put_contents($pidfile,getmypid());	
	$ldap=new clladp();

	$ous=$ldap->hash_get_ou(true);;
	while (list ($index, $ou) = each ($ous) ){
		$array_final[trim(strtolower($index))]=$index;
		
	}
	if(!is_array($array_final)){return null;}
	echo "exporting ".count($array_final)." OUS in \"/etc/artica-postfix/metadous.cache\"\n";	
	$datas=base64_encode(serialize($array_final));
	@file_put_contents("/etc/artica-postfix/metadous.cache",$datas);
	$http=new httpget();
	$sock=new sockets();
	$meta=new artica_meta();
	$http->uploads["ALL_OUS"]="/etc/artica-postfix/metadous.cache";
	$body=$http->send("$meta->ArticaMetaHostname/lic.users.import.php","post",array("DATAS"=>base64_encode(serialize($meta->GLOBAL_ARRAY))));
	events(count($array_final)." organizations exported to global Management console",__FUNCTION__,__FILE__,__LINE__);
	send_email_events(count($array_final)." organizations exported to global Management console",$body,"CLOUD");
	echo $body;		
	
}

function export_all_groups(){
	$unix=new unix();
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$oldpid=@file_get_contents($pidfile);
	if($unix->process_exists($oldpid)){
		events("$oldpid already running",__FUNCTION__,__FILE__,__LINE__);
		die();
	}
	@file_put_contents($pidfile,getmypid());	
	$ldap=new clladp();

	$ous=$ldap->hash_get_ou(true);;
	while (list ($ou, $ou2) = each ($ous) ){
		if(trim($ou)==null){continue;}
		 $hash=$ldap->hash_groups($ou);
		 if(!is_array($hash)){return;}
		 while (list ($gid, $groupname) = each ($hash) ){
		 	$array_final[$gid]=array("ou"=>$ou,"name"=>$groupname);
		 }
	}	
	if(!is_array($array_final)){return null;}
	echo "exporting ".count($array_final)." groups in \"/etc/artica-postfix/metagroups.cache\"\n";	
	$datas=base64_encode(serialize($array_final));
	@file_put_contents("/etc/artica-postfix/metagroups.cache",$datas);
	$http=new httpget();
	$sock=new sockets();
	$meta=new artica_meta();
	$http->uploads["ALL_GROUPS"]="/etc/artica-postfix/metagroups.cache";
	$body=$http->send("$meta->ArticaMetaHostname/lic.users.import.php","post",array("DATAS"=>base64_encode(serialize($meta->GLOBAL_ARRAY))));
	events(count($array_final)." groups exported to global Management console",__FUNCTION__,__FILE__,__LINE__);
	send_email_events(count($array_final)." groups exported to global Management console",$body,"CLOUD");
	echo $body;		
	
	
}

function export_all_settings(){
	$unix=new unix();
	$gzip=$unix->find_program("gzip");
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$oldpid=@file_get_contents($pidfile);
	if($unix->process_exists($oldpid)){
		events("$oldpid already running",__FUNCTION__,__FILE__,__LINE__);
		if($GLOBALS["VERBOSE"]){"$oldpid already running";}
		die();
	}
	@file_put_contents($pidfile,getmypid());
	foreach (glob("/etc/artica-postfix/settings/Daemons/*") as $filename) {
		$key=basename($filename);
		$array_final[$key]=@file_get_contents($filename);
	}	
	events("exporting ".count($array_final)." parameters in \"/etc/artica-postfix/metasettings.cache\"",__FUNCTION__,__FILE__,__LINE__);
	if(!is_array($array_final)){return null;}
	
	$exported="/etc/artica-postfix/metasettings.cache";	
	$datas=base64_encode(serialize($array_final));
	@file_put_contents($exported,$datas);
	events("export: $exported ".str_replace("&nbsp;"," ",FormatBytes(strlen($exported)/1024)),__FUNCTION__,__FILE__,__LINE__);
	$gzipped="$exported.gz";
	shell_exec("$gzip -c $exported  > $gzipped");
	$taille=str_replace("&nbsp;"," ",FormatBytes(filesize($gzipped)/1024));
	events("$gzipped: compressed: $taille",__FUNCTION__,__FILE__,__LINE__);
	$http=new httpget();
	$sock=new sockets();
	$meta=new artica_meta();
	$http->uploads["ARTICA_SETTINGS"]=$gzipped;
	$body=$http->send("$meta->ArticaMetaHostname/lic.users.import.php","post",array("DATAS"=>base64_encode(serialize($meta->GLOBAL_ARRAY))));
	echo $body;
	
	@unlink($gzipped);	
	events(count($array_final)." settings exported to global Management console",__FUNCTION__,__FILE__,__LINE__);
	send_email_events(count($array_final)." settings exported to global Management console",$body,"CLOUD");
				
	
}

function export_all_groupwares(){
	include_once 'ressources/class.apache.inc';
	$apache=new vhosts();
	events("find installed groupwares",__FUNCTION__,__FILE__,__LINE__);	
	$array_final=$apache->AllVhosts();
	if(count($array_final)==0){
		events("No groupwares to export ask meta console to stop notify...",__FUNCTION__,__FILE__,__LINE__);	
		NotifyConsoleNoDatas("groupwares");	
		return;
	}
	
	$datas=base64_encode(serialize($array_final));
	@file_put_contents("/etc/artica-postfix/groupwares.cache",$datas);
	$http=new httpget();
	$sock=new sockets();
	$meta=new artica_meta();
	$http->uploads["ALL_GROUPWARES"]="/etc/artica-postfix/groupwares.cache";
	$body=$http->send("$meta->ArticaMetaHostname/lic.users.import.php","post",array("DATAS"=>base64_encode(serialize($meta->GLOBAL_ARRAY))));
	events(count($array_final)." groupwares exported to global Management console",__FUNCTION__,__FILE__,__LINE__);
	send_email_events(count($array_final)." groupwares exported to global Management console",$body,"CLOUD");
	echo $body;		
	
	
}

function export_all_computers(){
	$ldap=new clladp();
	$filter_search="(&(objectClass=ArticaComputerInfos)(gecos=computer))";
	$attr=array("uid");
	$sr =@ldap_search($ldap->ldap_connection,$ldap->suffix,$filter_search,$attr);
	$hash=ldap_get_entries($ldap->ldap_connection,$sr);
	if(!is_array($hash)){events("No computers with pattern $filter_search",__FUNCTION__,__FILE__,__LINE__);return;}
	events("{$hash["count"]} computers to export...",__FUNCTION__,__FILE__,__LINE__);
	if($hash["count"]==0){
		events("No computers to export ask meta console to stop notify...",__FUNCTION__,__FILE__,__LINE__);	
		NotifyConsoleNoDatas("computers");
		return;
	}
	
	
	for($i=0;$i<$hash["count"];$i++){	
		$uid=$hash[$i]["uid"][0];
		$comp=new computers($uid);	
		$ff=array();
		foreach($comp as $key => $value) {$ff[$key]=$value;}
		$array_final[]=$ff;
	}

	if(!is_array($array_final)){return null;}
	echo "exporting ".count($array_final)." computers in \"/etc/artica-postfix/computers.cache\"\n";	
	$datas=base64_encode(serialize($array_final));
	@file_put_contents("/etc/artica-postfix/computers.cache",$datas);
	$http=new httpget();
	$sock=new sockets();
	$meta=new artica_meta();
	$http->uploads["ALL_COMPUTERS"]="/etc/artica-postfix/computers.cache";
	$body=$http->send("$meta->ArticaMetaHostname/lic.users.import.php","post",array("DATAS"=>base64_encode(serialize($meta->GLOBAL_ARRAY))));
	events(count($array_final)." computers exported to global Management console",__FUNCTION__,__FILE__,__LINE__);
	send_email_events(count($array_final)." computers exported to global Management console",$body,"CLOUD");
	echo $body;			
	
}

function NotifyConsoleNoDatas($value){
	$http=new httpget();
	$sock=new sockets();
	$meta=new artica_meta();
	events("Ask to remove $value tasks",__FUNCTION__,__FILE__,__LINE__);
	$http=new httpget();
	$datasToSend=base64_encode(serialize($meta->GLOBAL_ARRAY));
	$body=$http->send("$meta->ArticaMetaHostname/lic.query.server.php","post",array("DATAS"=>$datasToSend,"NODATAFROM"=>$value));
	events("body=$body",__FUNCTION__,__FILE__,__LINE__);
	
}



function export_socks(){
	$http=new httpget();
	$sock=new sockets();
	$meta=new artica_meta();
	$cachefile="/etc/artica-postfix/settings.inc.cache";
	
	
	if(is_file($cachefile)){
		$array=unserialize(base64_decode(@file_get_contents($cachefile)));
	}
	
	foreach (glob("/etc/artica-postfix/artica-meta-queue-socks/*.sock") as $filename) {
		$key=@file_get_contents($filename);
		if($key==null){@unlink($filename);continue;}
		$array[$key]=$sock->GET_INFO($key);
		$logKey[]=$key;
		@unlink($filename);
		
	}
	
	if(!is_array($array)){return;}
	@file_put_contents($cachefile,base64_encode(serialize($array)));
	$http->uploads["SETTINGS_UNIQUE"]=$cachefile;
	$body=$http->send("$meta->ArticaMetaHostname/lic.users.import.php","post",array("DATAS"=>base64_encode(serialize($meta->GLOBAL_ARRAY))));
	if(!preg_match("#<RESULTS>OK</RESULTS>#is",$body)){
		events(count($array)." settings failed to be exported to global Management console",__FUNCTION__,__FILE__,__LINE__);
		return;
	}else{
		send_email_events(count($array)." settings exported to global Management console",@implode("\n",$logKey),"CLOUD");
		@unlink($cachefile);
	}
	
	
}

function export_dns(){
	
	
	
	$unix=new unix();
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$oldpid=@file_get_contents($pidfile);
	if($unix->process_exists($oldpid)){		
		$ptime=$unix->PROCESS_TTL($oldpid);
		if($ptime>$GLOBALS["MAXTTL"]){
			events("export_dns():: killing process $oldpid ttl:$ptime minutes");
			shell_exec("/bin/kill -9 $oldpid");
		}else{
			events("export_dns():: already executed, process $oldpid");
			die();
		}
	}	
	
	$time=file_time_min($pidfile);
	events("$pidfile={$time}Mn",__FUNCTION__,__FILE__,__LINE__);
	if($time==0){
		events("Cannot replicate DNS before one minute...",__FUNCTION__,__FILE__,__LINE__);
		return;
	}	
	
	@file_put_contents($pidfile,getmypid());
	
	
	$ldap=new clladp();
	$http=new httpget();
	$sock=new sockets();
	$meta=new artica_meta();	
	$pattern="(&(objectclass=*)(arecord=*))";
	$sr =ldap_search($ldap->ldap_connection,"ou=dns,$ldap->suffix",$pattern,array());
	$cachefile="/etc/artica-postfix/dns.cache";
	
	
	if($sr){
		$hash=ldap_get_entries($ldap->ldap_connection,$sr);
		for($i=0;$i<$hash["count"];$i++){
			$macrecord=$hash[$i]["macrecord"][0];
			$arecord=$hash[$i]["arecord"][0];
			if($arecord=="127.0.0.1"){continue;}
			if($arecord==null){continue;}
			echo "$macrecord $arecord\n";
			$array[$arecord]=array("MAC"=>$macrecord,"NAMES"=>$hash[$i]["associateddomain"]);
			$arecord=null;
			$macrecord=null;
		}
	}else{
		events("LDAP link failed",__FUNCTION__,__FILE__,__LINE__);
		return;
	}
	
	if(!is_array($array)){return;}
	@file_put_contents($cachefile,base64_encode(serialize($array)));
	$http->uploads["DNS_COMPUTERS"]=$cachefile;
	$body=$http->send("$meta->ArticaMetaHostname/lic.users.import.php","post",array("DATAS"=>base64_encode(serialize($meta->GLOBAL_ARRAY))));
	if(!preg_match("#<RESULTS>OK</RESULTS>#is",$body)){
		events(count($array)." DNS failed to be exported to global Management console",__FUNCTION__,__FILE__,__LINE__);
		return;
	}else{
		events(count($array)." DNS exported to global Management console",__FUNCTION__,__FILE__,__LINE__);
		send_email_events(count($array)." DNS exported to global Management console",null,"CLOUD");
		@unlink($cachefile);
	}	
	
}

function export_freeweb(){
	$sock=new sockets();
	$meta=new artica_meta();	
	$unix=new unix();
	$q=new mysql();
	$addons=array("uuid"=>$meta->uuid,"serial"=>$meta->serial);
	$exported=$q->BackupTable("freeweb","artica_backup",$addons,true);
	if($exported==null){return;}
	if(!$q->ok){echo $q->mysql_error;return;}
	$tmpf=$unix->FILE_TEMP();
	@file_put_contents($tmpf,base64_encode($exported));
	$http=new httpget();
	$http->uploads["FREE_WEBS"]=$tmpf;
	$body=$http->send("$meta->ArticaMetaHostname/lic.users.import.php","post",array("DATAS"=>base64_encode(serialize($meta->GLOBAL_ARRAY))));
	if(!preg_match("#<RESULTS>OK</RESULTS>#is",$body)){
		events("Freewebs failed to export to global Management console\n$body\n",__FUNCTION__,__FILE__,__LINE__);
		return;
	}else{
		
		send_email_events("FreeWebs success export to global Management console",null,"CLOUD");
		
	}		
	@unlink($tmpf);
}

function export_iptables(){
	$users=new usersMenus();
	$sock=new sockets();
	$meta=new artica_meta();	
	$unix=new unix();
	$q=new mysql();
	$gzip=$unix->find_program("gzip");
	$addons=array("uuid"=>$meta->uuid,"serial"=>$meta->serial);
	$exported=$q->BackupTable("iptables","artica_backup",$addons,true);
	if($exported==null){return;}
	if(!$q->ok){echo $q->mysql_error;return;}
	
	events("export: ".str_replace("&nbsp;"," ",FormatBytes(strlen($exported)/1024)),__FUNCTION__,__FILE__,__LINE__);
	$tmpf=$unix->FILE_TEMP();
	@file_put_contents($tmpf,$exported);
	$gzipped="$tmpf.gz";
	shell_exec("$gzip -c $tmpf  > $gzipped");
	$taille=str_replace("&nbsp;"," ",FormatBytes(filesize($gzipped)/1024));
	events("$tmpf compressed: $taille",__FUNCTION__,__FILE__,__LINE__);
	$http=new httpget();
	$http->uploads["IPTABLES"]=$gzipped;
	$body=$http->send("$meta->ArticaMetaHostname/lic.users.import.php","post",array("DATAS"=>base64_encode(serialize($meta->GLOBAL_ARRAY))));
	
	@unlink($tmpf);
	@unlink($gzipped);
	
	if(!preg_match("#<RESULTS>OK</RESULTS>#is",$body)){
		
		return;
	}else{
		send_email_events("Iptables: success exported $taille data length to global Management console",null,"CLOUD");
		events("export_iptables():: Failed to export $taille data to global Management console\n$body\n",__FUNCTION__,__FILE__,__LINE__);
	}
}

function export_awstats(){
	$sock=new sockets();
	$meta=new artica_meta();	
	$unix=new unix();
	$q=new mysql();
	$addons=array("uuid"=>$meta->uuid,"serial"=>$meta->serial);
	$exported=$q->BackupTable("awstats","artica_backup",$addons,true);
	if($exported==null){return;}
	if(!$q->ok){echo $q->mysql_error;return;}
	$tmpf=$unix->FILE_TEMP();
	@file_put_contents($tmpf,base64_encode($exported));
	$http=new httpget();
	$http->uploads["AWSTATS"]=$tmpf;
	$body=$http->send("$meta->ArticaMetaHostname/lic.users.import.php","post",array("DATAS"=>base64_encode(serialize($meta->GLOBAL_ARRAY))));
	if(!preg_match("#<RESULTS>OK</RESULTS>#is",$body)){
		events("awstats failed to export to global Management console\n$body\n",__FUNCTION__,__FILE__,__LINE__);
		return;
	}else{
		
		send_email_events("awstats: success export to global Management console",null,"CLOUD");
		
	}		
	@unlink($tmpf);	
	
}
function export_awstats_files(){
	$sock=new sockets();
	$meta=new artica_meta();	
	$unix=new unix();
	$q=new mysql();
	$addons=array("uuid"=>$meta->uuid,"serial"=>$meta->serial);
	$exported=$q->BackupTable("awstats_files","artica_backup",$addons,false);
	if($exported==null){return;}
	$length=strlen($exported);
	if(!$q->ok){echo $q->mysql_error;return;}
	$tmpf=$unix->FILE_TEMP();
	@file_put_contents($tmpf,base64_encode($exported));
	$http=new httpget();
	$http->uploads["AWSTATS_FILES"]=$tmpf;
	$body=$http->send("$meta->ArticaMetaHostname/lic.users.import.php","post",array("DATAS"=>base64_encode(serialize($meta->GLOBAL_ARRAY))));
	if(!preg_match("#<RESULTS>OK</RESULTS>#is",$body)){
		events("awstats failed to export to global Management console\n$body\n",__FUNCTION__,__FILE__,__LINE__);
		return;
	}else{
		
		send_email_events("awstats: success exported $length bytes to global Management console",null,"CLOUD");
		
	}		
	@unlink($tmpf);	
	
}

function export_postfix_events(){
	$users=new usersMenus();
	if(!$users->POSTFIX_INSTALLED){events("Postfix is not installed, aborting",__FUNCTION__,__FILE__,__LINE__);return;}
	$sock=new sockets();
	$meta=new artica_meta();	
	$unix=new unix();
	$q=new mysql();
	$gzip=$unix->find_program("gzip");
	$addons=array("uuid"=>$meta->uuid,"serial"=>$meta->serial);
	$exported=$q->BackupTable("mails_stats","artica_events",$addons,false,"WHERE `artica_meta`=0 ORDER BY `zDate` LIMIT 0,2000");
	if($exported==null){return;}
	if(!$q->ok){echo $q->mysql_error;return;}
	
	events("export: ".str_replace("&nbsp;"," ",FormatBytes(strlen($exported)/1024)),__FUNCTION__,__FILE__,__LINE__);
	$tmpf=$unix->FILE_TEMP();
	@file_put_contents($tmpf,$exported);
	$gzipped="$tmpf.gz";
	shell_exec("$gzip -c $tmpf  > $gzipped");
	$taille=str_replace("&nbsp;"," ",FormatBytes(filesize($gzipped)/1024));
	events("$tmpf compressed: $taille",__FUNCTION__,__FILE__,__LINE__);
	$http=new httpget();
	$http->uploads["SMTP_LOGS2"]=$gzipped;
	$body=$http->send("$meta->ArticaMetaHostname/lic.users.import.php","post",array("DATAS"=>base64_encode(serialize($meta->GLOBAL_ARRAY))));
	
	@unlink($tmpf);
	@unlink($gzipped);
	
	if(!preg_match("#<RESULTS>OK</RESULTS>#is",$body)){
		
		return;
	}else{
			
		send_email_events("postfix events: success exported $taille data length to global Management console",null,"CLOUD");
		events("export_postfix_events():: Failed to export $taille data to global Management console\n$body\n",__FUNCTION__,__FILE__,__LINE__);
		$sql="UPDATE `mails_stats` SET `artica_meta`='1' WHERE `artica_meta`=0 ORDER BY `zDate` LIMIT 2000";
		events("export_postfix_events():: $sql",__FUNCTION__,__FILE__,__LINE__);
		$q->QUERY_SQL($sql,"artica_events");
		if(!$q->ok){
			events("export_postfix_events():: failed $q->mysql_error",__FUNCTION__,__FILE__,__LINE__);
		}
	}		
	
	
}

function export_fetchmail_rules(){
	$sock=new sockets();
	$meta=new artica_meta();	
	$unix=new unix();
	$q=new mysql();
	$gzip=$unix->find_program("gzip");
	$addons=array("uuid"=>$meta->uuid,"serial"=>$meta->serial);	
	$exported=$q->BackupTable("fetchmail_rules","artica_backup",$addons,true,null);
	if(!$q->ok){echo $q->mysql_error;return;}
	events("export: ".str_replace("&nbsp;"," ",FormatBytes(strlen($exported)/1024)),__FUNCTION__,__FILE__,__LINE__);
	$tmpf=$unix->FILE_TEMP();
	@file_put_contents($tmpf,$exported);
	$gzipped="$tmpf.gz";
	shell_exec("$gzip -c $tmpf  > $gzipped");
	$taille=str_replace("&nbsp;"," ",FormatBytes(filesize($gzipped)/1024));
	events("$tmpf compressed: $taille",__FUNCTION__,__FILE__,__LINE__);
	$http=new httpget();
	$http->uploads["FETCHMAIL_RULES"]=$gzipped;
	$body=$http->send("$meta->ArticaMetaHostname/lic.users.import.php","post",array("DATAS"=>base64_encode(serialize($meta->GLOBAL_ARRAY))));
	
	@unlink($tmpf);
	@unlink($gzipped);
	
	if(!preg_match("#<RESULTS>OK</RESULTS>#is",$body)){return;}
}

function export_openvpn_rsites(){
	$sock=new sockets();
	$meta=new artica_meta();	
	$unix=new unix();
	$q=new mysql();
	$gzip=$unix->find_program("gzip");
	$addons=array("uuid"=>$meta->uuid,"serial"=>$meta->serial);	
	$exported=$q->BackupTable("vpnclient","artica_backup",$addons,true,null);
	if(!$q->ok){echo $q->mysql_error;return;}
	events("export: ".str_replace("&nbsp;"," ",FormatBytes(strlen($exported)/1024)),__FUNCTION__,__FILE__,__LINE__);
	$tmpf=$unix->FILE_TEMP();
	@file_put_contents($tmpf,$exported);
	$gzipped="$tmpf.gz";
	shell_exec("$gzip -c $tmpf  > $gzipped");
	$taille=str_replace("&nbsp;"," ",FormatBytes(filesize($gzipped)/1024));
	events("$tmpf compressed: $taille",__FUNCTION__,__FILE__,__LINE__);
	$http=new httpget();
	$http->uploads["OPENVPN_SITES"]=$gzipped;
	$body=$http->send("$meta->ArticaMetaHostname/lic.users.import.php","post",array("DATAS"=>base64_encode(serialize($meta->GLOBAL_ARRAY))));
	
	@unlink($tmpf);
	@unlink($gzipped);
	
	if(!preg_match("#<RESULTS>OK</RESULTS>#is",$body)){
		events("Exporting remote sites done (error) $body...",__FUNCTION__,__FILE__,__LINE__);
		return;
	}
	events("Exporting remote sites done...",__FUNCTION__,__FILE__,__LINE__);
	
	export_openvpn_sites_package();
	
}

function export_openvpn_sites_package(){
	$sock=new sockets();
	$q=new mysql();
	$sql="SELECT * FROM vpnclient WHERE connexion_type=1";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$sitename=$ligne["sitename"];
		$servername=$ligne["servername"];
		$IP_START=$ligne["IP_START"];
		$meta=new artica_meta();	
		$ovpnfile=_export_ovpn($sitename);
		if(!$ovpnfile){
			send_email_events("$sitename failed to export vpn configuration to global Management console (CORRUPTED) datas",@implode("\n",$GLOBALS["html_logs"]),"CLOUD");
			events("User $sitename failed to export vpn configuration to global Management console (CORRUPTED) datas",__FUNCTION__,__FILE__,__LINE__);
			continue;
		}
		$meta=new artica_meta();	
		$http=new httpget();
		$http->uploads["OVPNSITE"]=$ovpnfile;
		$body=$http->send("$meta->ArticaMetaHostname/lic.users.import.php","post",array("DATAS"=>base64_encode(serialize($meta->GLOBAL_ARRAY)),
		"sitename"=>$sitename,
		"servername"=>$servername,
		"IP_START"=>$IP_START
		));
		@unlink($ovpnfile);
	}
}


function export_openvpn_logs(){
	$users=new usersMenus();
	$sock=new sockets();
	$unix=new unix();
	$CacheDatasPath="/etc/artica-postfix/OpenVPNLogs.cache";
	$filepath="/var/log/openvpn/openvpn.log";
	$statusPath="/var/log/openvpn/openvpn-status.log";
	if(!is_file($filepath)){return;}
	if(!$users->OPENVPN_INSTALLED){return;}	
	$CacheDatas=unserialize(@file_get_contents($CacheDatasPath));
	$filetime=filemtime($filepath);
	$http=new httpget();
	$meta=new artica_meta();	
	
	$gzip=$unix->find_program("gzip");
	$http=new httpget();
	$meta=new artica_meta();	
	
	if($filetime<>$CacheDatas["$filepath"]){
		$filepath_gzipped="/tmp/openvon-$meta->uuid.gz";
		$CacheDatas[$filepath]=$filetime;
		shell_exec("$gzip -f -c \"$filepath\"  > \"$filepath_gzipped\"");
		$filesize=filesize($filepath_gzipped)/1024;		
		if($GLOBALS["VERBOSE"]){echo "Send to Artica Meta (". FormatBytes($filesize).")\n";}	
		$http->uploads["OPENVPN_SERVER_LOG"]=$filepath_gzipped;
		$body=$http->send("$meta->ArticaMetaHostname/lic.users.import.php","post",array("DATAS"=>base64_encode(serialize($meta->GLOBAL_ARRAY))));
		if(!preg_match("#<RESULTS>OK</RESULTS>#is",$body)){	return;}
		@file_put_contents($CacheDatasPath,serialize($CacheDatas));
	}
	
	
	if(!file_exists($statusPath)){
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__." $statusPath no such file\n";}
		return;}
	$filetime=filemtime($statusPath);
	
	$http=new httpget();
	$meta=new artica_meta();	
	$final=array();
	if($filetime<>$CacheDatas["$statusPath"]){
			$CacheDatas[$statusPath]=$filetime;
			$f=explode("\n",@file_get_contents("$statusPath"));
			while (list ($num, $line) = each ($f) ){
				if(!preg_match("#(.+?),([0-9\:\.]+),([0-9]+),([0-9]+),(.+?)$#",$line,$re)){continue;}
				if($GLOBALS["VERBOSE"]){echo __FUNCTION__." found $line\n";}
				$final[]=$re;
				}
			
			$filepath_gzipped="$statusPath.gz";
			if($GLOBALS["VERBOSE"]){echo __FUNCTION__." $statusPath.tmp ". count($final). " rows\n";}
			@file_put_contents("$statusPath.tmp",serialize($final));
			shell_exec("$gzip -f -c \"$statusPath.tmp\"  > \"$filepath_gzipped\"");
			
			if(!is_file($filepath_gzipped)){if($GLOBALS["VERBOSE"]){echo __FUNCTION__." $filepath_gzipped no such file\n";}return;}
			$http->uploads["OPENVPN_SERVER_STATUS"]=$filepath_gzipped;
			$body=$http->send("$meta->ArticaMetaHostname/lic.users.import.php","post",array("DATAS"=>base64_encode(serialize($meta->GLOBAL_ARRAY))));
			if(!preg_match("#<RESULTS>OK</RESULTS>#is",$body)){	return;}
			@file_put_contents($CacheDatasPath,serialize($CacheDatas));
			
		}
		
		
		
	}


function export_virtualbox_logs(){
	$users=new usersMenus();
	$sock=new sockets();
		
	if(!$users->VIRTUALBOX_INSTALLED){return;}
	$unix=new unix();
	$VBoxManage=$unix->find_program("VBoxManage");
	if($VBoxManage==null){if($GLOBALS["VERBOSE"]){echo "VBoxManage no such tool\n";}return;}

	$CacheDatas=unserialize(@file_get_contents("/etc/artica-postfix/vboxLogs.cache"));
	
	exec("$VBoxManage list -l vms 2>&1",$results);
	while (list($index,$line)=each($results)){	
		if(preg_match("#UUID:\s+(.+)#",$line,$re)){$uuid=$re[1];}
		if(preg_match("#Config file:\s+(.+)#",$line,$re)){
			$file=dirname(trim($re[1]))."/Logs/VBox.log";
			if(is_file($file)){$array[$uuid]=$file;}
			continue;
		}
}

	if(!is_array($array)){return;}
	$gzip=$unix->find_program("gzip");
	@mkdir("/tmp/gzip_vbox",666,true);
	while (list($uuid,$filepath)=each($array)){	
		$filetime=filemtime($filepath);
		if($filetime==$CacheDatas["$filepath"]){continue;}
		$CacheDatas["$filepath"]=$filetime;
		$filepath_gzipped="/tmp/gzip_vbox/$uuid.gz";
		if($GLOBALS["VERBOSE"]){echo "Compress $filepath to $filepath_gzipped\n";}
		shell_exec("$gzip -f -c \"$filepath\"  > \"$filepath_gzipped\"");
		if(is_file($filepath_gzipped)){$gzipeds[]="\"$filepath_gzipped\"";}
		}
		
		

		if(!is_array($gzipeds)){return;}
		
		$cmd="cd /tmp/gzip_vbox && tar -cjf VboxLogs.tar.gz * ";
		if($GLOBALS["VERBOSE"]){echo "$cmd\n";}	
		shell_exec($cmd);
		
	$http=new httpget();
	$meta=new artica_meta();
	$filesize=filesize("/tmp/gzip_vbox/VboxLogs.tar.gz")/1024;
	
	if($GLOBALS["VERBOSE"]){echo "Send to Artica Meta (". FormatBytes($filesize).")\n";}	
	$http->uploads["VBOXGUEST_LOGS"]="/tmp/gzip_vbox/VboxLogs.tar.gz";
	$body=$http->send("$meta->ArticaMetaHostname/lic.users.import.php","post",array("DATAS"=>base64_encode(serialize($meta->GLOBAL_ARRAY))));

	if(!preg_match("#<RESULTS>OK</RESULTS>#is",$body)){
		
		return;
	}else{	
		@file_put_contents("/etc/artica-postfix/vboxLogs.cache",serialize($CacheDatas));
		
	}
	
	
		
}



function events($text,$function,$file=null,$line=0){
		$file=basename(__FILE__);
		$pid=@getmypid();
		$date=@date("H:i:s");
		$logFile="/var/log/artica-postfix/artica-meta-agent.log";
		$size=@filesize($logFile);
		if($size>100000){@unlink($logFile);}
		$f = @fopen($logFile, 'a');
		$text="[$file][$pid] $date $function:: $text (L.$line)\n";
		if($GLOBALS["VERBOSE"]){echo $text;}
		@fwrite($f, $text);
		@fclose($f);	
		}	

	
	