<?php

include_once(dirname(__FILE__).'/framework/frame.class.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/ressources/class.http.pear.inc');
include_once(dirname(__FILE__).'/ressources/class.artica-meta.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__).'/ressources/class.system.network.inc');
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__).'/ressources/class.sockets.inc');

	
	if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
	if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
	if(preg_match("#--debug#",implode(" ",$argv))){$GLOBALS["VERBOSE2"]=true;}
	
	
	$unix=new unix();
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".MAIN.pid";
	if(!TestCron($pidfile)){die();}
	@file_put_contents($pidfile,getmypid());
		

	if(!is_file("/etc/artica-postfix/artica-meta.tasks")){
		if($GLOBALS["VERBOSE"]){echo "/etc/artica-postfix/artica-meta.tasks no such file\n";}
		events("/etc/artica-postfix/artica-meta.tasks no such file",__FUNCTION__,__FILE__,__LINE__);
		die();
	}
	
	$array=unserialize(base64_decode(@file_get_contents("/etc/artica-postfix/artica-meta.tasks")));
	if(!is_array($array)){
		events("Unable to parse content of tasks",__FUNCTION__,__FILE__,__LINE__);
		return;
	}

events(count($array)." Tasks to execute",__FUNCTION__,__FILE__,__LINE__);
$failed=false;
$finalTasks=$array;
while (list ($taskid, $TasksParams) = each ($array) ){
	$task_name=$TasksParams["TASK_NAME"];
	$task_value=$TasksParams["TASK_VALUE"];
	$unix=new unix();
	events("Check task $task_name",__FUNCTION__,__FILE__,__LINE__);
	switch ($task_name) {
		
		case "ADD_OU":
			if(TASK_ADD_OU($task_value)){unset($finalTasks[$taskid]);TASK_FINISH($taskid);}else{events("TASK_ADD_OU() \"$task_name\" FAILED",__FUNCTION__,__FILE__,__LINE__);$failed=true;}
		break;
		
		case "ADD_DOMAIN":
			if(TASK_ADD_DOMAIN($task_value)){unset($finalTasks[$taskid]);TASK_FINISH($taskid);}else{events("TASK_ADD_DOMAIN() \"$task_name\" FAILED",__FUNCTION__,__FILE__,__LINE__);$failed=true;}
		break;	
		
		
		case "DELETE_DOMAIN":
		if(TASK_DELETE_DOMAIN($task_value)){unset($finalTasks[$taskid]);TASK_FINISH($taskid);}else{events("TASK_DELETE_DOMAIN() \"$task_name\" FAILED",__FUNCTION__,__FILE__,__LINE__);$failed=true;}
		break;		
		
		
		case "UPDATE":
			if(TASK_UPDATE($task_value)){unset($finalTasks[$taskid]);TASK_FINISH($taskid);}else{events("TASK_UPDATE() \"$task_name\" FAILED",__FUNCTION__,__FILE__,__LINE__);$failed=true;}
		break;	
		
		case "SAVE_REMOTE_USER":
			if(TASK_USER_EDIT($task_value)){unset($finalTasks[$taskid]);TASK_FINISH($taskid);}else{events("TASK_USER_EDIT() \"$task_name\" FAILED",__FUNCTION__,__FILE__,__LINE__);$failed=true;}
		break;			
		
		case "EMAIL_ALIASES":
			if(TASK_EMAIL_ALIASES($task_value)){unset($finalTasks[$taskid]);TASK_FINISH($taskid);}else{events("TASK_EMAIL_ALIASES() \"$task_name\" FAILED",__FUNCTION__,__FILE__,__LINE__);$failed=true;}
		break;
		
		case "CHANGE_HOSTNAME":
			if(TASK_CHANGE_HOSTNAME($task_value)){unset($finalTasks[$taskid]);TASK_FINISH($taskid);}else{events("TASK_CHANGE_HOSTNAME() \"$task_name\" FAILED",__FUNCTION__,__FILE__,__LINE__);$failed=true;}
		break;	

		case "SETTINGS_SOCKETS":
			if(TASK_SETTINGS_SOCKETS($task_value)){unset($finalTasks[$taskid]);TASK_FINISH($taskid);}else{events("TASK_SETTINGS_SOCKETS() \"$task_name\" FAILED",__FUNCTION__,__FILE__,__LINE__);$failed=true;}
		break;			
		
		case "REBOOT":
			send_email_events("Success executing reboot operation",null,"CLOUD");
			unset($finalTasks[$taskid]);
			TASK_FINISH($taskid);
			shell_exec($unix->find_program("reboot"));
			$failed=true;
		break;	

		case "RESTART_NETWORK":
			if(TASK_RESTART_NETWORK($task_value)){unset($finalTasks[$taskid]);TASK_FINISH($taskid);}else{events("TASK_RESTART_NETWORK() \"$task_name\" FAILED",__FUNCTION__,__FILE__,__LINE__);$failed=true;}
		break;

		case "SQUID_CACHE":
			if(TASK_SQUID_CACHE($task_value)){unset($finalTasks[$taskid]);TASK_FINISH($taskid);}else{events("TASK_SQUID_CACHE() \"$task_name\" FAILED",__FUNCTION__,__FILE__,__LINE__);$failed=true;}
		break;	

		case "SQUID_DELETE_CACHE":
			if(SQUID_DELETE_CACHE($task_value)){unset($finalTasks[$taskid]);TASK_FINISH($taskid);}else{events("SQUID_DELETE_CACHE() \"$task_name\" FAILED",__FUNCTION__,__FILE__,__LINE__);$failed=true;}
		break;	

		case "DNS_ENTRY":
			if(DNS_ENTRY($task_value)){unset($finalTasks[$taskid]);TASK_FINISH($taskid);}else{events("DNS_ENTRY() \"$task_name\" FAILED",__FUNCTION__,__FILE__,__LINE__);$failed=true;}
		break;			
		
		case "DNS_DEL_ENTRY":
			if(DNS_DEL_ENTRY($task_value)){unset($finalTasks[$taskid]);TASK_FINISH($taskid);}else{events("DNS_DEL_ENTRY() \"$task_name\" FAILED",__FUNCTION__,__FILE__,__LINE__);$failed=true;}
		break;		
		
		case "FREEWEB_ADD":
			if(FREEWEB_ADD($task_value)){unset($finalTasks[$taskid]);TASK_FINISH($taskid);}else{events("FREEWEB_ADD() \"$task_name\" FAILED",__FUNCTION__,__FILE__,__LINE__);$failed=true;}
		break;	

		case "FREEWEB_DELETE":
			if(FREEWEB_DELETE($task_value)){unset($finalTasks[$taskid]);TASK_FINISH($taskid);}else{events("FREEWEB_DELETE() \"$task_name\" FAILED",__FUNCTION__,__FILE__,__LINE__);$failed=true;}
		break;	

		case "UPDATE_AWSTATS":
			if(UPDATE_AWSTATS($task_value)){unset($finalTasks[$taskid]);TASK_FINISH($taskid);}else{events("UPDATE_AWSTATS() \"$task_name\" FAILED",__FUNCTION__,__FILE__,__LINE__);$failed=true;}
		break;

		case "AWSTATS_RUN":
			if(AWSTATS_RUN($task_value)){unset($finalTasks[$taskid]);TASK_FINISH($taskid);}else{events("AWSTATS_RUN() \"$task_name\" FAILED",__FUNCTION__,__FILE__,__LINE__);$failed=true;}
		break;		
		
		case "ARTICA_MAKE":
			if(ARTICA_MAKE($task_value)){unset($finalTasks[$taskid]);TASK_FINISH($taskid);}else{events("ARTICA_MAKE() \"$task_name\" FAILED",__FUNCTION__,__FILE__,__LINE__);$failed=true;}
		break;
		
		case "NMAP_SCAN":
			if(NMAP_SCAN($task_value)){unset($finalTasks[$taskid]);TASK_FINISH($taskid);}else{events("NMAP_SCAN() \"$task_name\" FAILED",__FUNCTION__,__FILE__,__LINE__);$failed=true;}
		break;	

		case "ADD_GROUPWARE":
			if(ADD_GROUPWARE($task_value)){unset($finalTasks[$taskid]);TASK_FINISH($taskid);}else{events("ADD_GROUPWARE() \"$task_name\" FAILED",__FUNCTION__,__FILE__,__LINE__);$failed=true;}
		break;			

		case "GROUPWARE_DELETE":
			if(GROUPWARE_DELETE($task_value)){unset($finalTasks[$taskid]);TASK_FINISH($taskid);}else{events("ADD_GROUPWARE() \"$task_name\" FAILED",__FUNCTION__,__FILE__,__LINE__);$failed=true;}
		break;	

		case "STOP_VBOX":
			if(VBOX_CMD("STOP_VBOX",$task_value)){unset($finalTasks[$taskid]);TASK_FINISH($taskid);}else{events("VBOX_CMD() \"$task_name\" FAILED",__FUNCTION__,__FILE__,__LINE__);$failed=true;}
		break;			
		
		case "START_VBOX":
			if(VBOX_CMD("START_VBOX",$task_value)){unset($finalTasks[$taskid]);TASK_FINISH($taskid);}else{events("VBOX_CMD() \"$task_name\" FAILED",__FUNCTION__,__FILE__,__LINE__);$failed=true;}
		break;	

		case "RESTART_VBOX":
			if(VBOX_CMD("RESTART_VBOX",$task_value)){unset($finalTasks[$taskid]);TASK_FINISH($taskid);}else{events("VBOX_CMD() \"$task_name\" FAILED",__FUNCTION__,__FILE__,__LINE__);$failed=true;}
		break;	

		
		case "FETCHMAIL_EDIT":
			if(FETCHMAIL_EDIT($task_value)){unset($finalTasks[$taskid]);TASK_FINISH($taskid);}else{events("FETCHMAIL_EDIT() \"$task_name\" FAILED",__FUNCTION__,__FILE__,__LINE__);$failed=true;}
		break;	

		
		case "FETCHMAIL_DELETE":
			if(FETCHMAIL_DELETE($task_value)){unset($finalTasks[$taskid]);TASK_FINISH($taskid);}else{events("FETCHMAIL_EDIT() \"$task_name\" FAILED",__FUNCTION__,__FILE__,__LINE__);$failed=true;}
		break;

		case "SUPERADMIN":
			if(SUPERADMIN($task_value)){unset($finalTasks[$taskid]);TASK_FINISH($taskid);}else{events("SUPERADMIN() \"$task_name\" FAILED",__FUNCTION__,__FILE__,__LINE__);$failed=true;}
		break;	

		case "OPENVPN_SCRIPTS":
			if(OPENVPN_SCRIPTS($task_value)){unset($finalTasks[$taskid]);TASK_FINISH($taskid);}else{events("OPENVPN_SCRIPTS() \"$task_name\" FAILED",__FUNCTION__,__FILE__,__LINE__);$failed=true;}
		break;	
		
		
		case "EDIT_VPN_SITE":
			if(OPENVPN_EDIT_VPN_SITE($task_value)){unset($finalTasks[$taskid]);TASK_FINISH($taskid);}else{events("OPENVPN_SCRIPTS() \"$task_name\" FAILED",__FUNCTION__,__FILE__,__LINE__);$failed=true;}
		break;
		
		case "DELETE_VPN_SITE":
			if(OPENVPN_DELETE_VPN_SITE($task_value)){unset($finalTasks[$taskid]);TASK_FINISH($taskid);}else{events("OPENVPN_SCRIPTS() \"$task_name\" FAILED",__FUNCTION__,__FILE__,__LINE__);$failed=true;}
		break;

		case "SYNC_VPN_SITE":
			if(OPENVPN_SYNC_VPN_SITE($task_value)){unset($finalTasks[$taskid]);TASK_FINISH($taskid);}else{events("OPENVPN_SCRIPTS() \"$task_name\" FAILED",__FUNCTION__,__FILE__,__LINE__);$failed=true;}
		break;		
		
		case "OPENVPN_CONCENTRATEUR":
			if(OPENVPN_CONCENTRATEUR($task_value)){unset($finalTasks[$taskid]);TASK_FINISH($taskid);}else{events("OPENVPN_SCRIPTS() \"$task_name\" FAILED",__FUNCTION__,__FILE__,__LINE__);$failed=true;}
		break;	
		
		
		case "OPENVPN_REMOTE_SITE_EDIT":
			if(OPENVPN_REMOTE_SITE_EDIT($task_value)){unset($finalTasks[$taskid]);TASK_FINISH($taskid);}else{events("OPENVPN_SCRIPTS() \"$task_name\" FAILED",__FUNCTION__,__FILE__,__LINE__);$failed=true;}
		break;	
		
		case "SET_NIC_MASTER":
			if(NETWORK_MASTER_CARD($task_value)){unset($finalTasks[$taskid]);TASK_FINISH($taskid);}else{events("NETWORK_MASTER_CARD() \"$task_name\" FAILED",__FUNCTION__,__FILE__,__LINE__);$failed=true;}
		break;
		
		case "EDIT_ETH_ROUTES":
			if(NETWORK_MASTER_CARD_ROUTES($task_value)){unset($finalTasks[$taskid]);TASK_FINISH($taskid);}else{events("NETWORK_MASTER_CARD_ROUTES() \"$task_name\" FAILED",__FUNCTION__,__FILE__,__LINE__);$failed=true;}
		break;		

		case "EXEC_FRAMEWORK":
			if(EXEC_FRAMEWORK($task_value)){unset($finalTasks[$taskid]);TASK_FINISH($taskid);}else{events("EXEC_FRAMEWORK() \"$task_name\" FAILED",__FUNCTION__,__FILE__,__LINE__);$failed=true;}
		break;		
		
		case "USER_TO_GROUP":
			if(USER_TO_GROUP($task_value)){unset($finalTasks[$taskid]);TASK_FINISH($taskid);}else{events("USER_TO_GROUP() \"$task_name\" FAILED",__FUNCTION__,__FILE__,__LINE__);$failed=true;}
		break;

		case "USER_REMOVE_GROUP":
			if(USER_REMOVE_GROUP($task_value)){unset($finalTasks[$taskid]);TASK_FINISH($taskid);}else{events("USER_REMOVE_GROUP() \"$task_name\" FAILED",__FUNCTION__,__FILE__,__LINE__);$failed=true;}
		break;		
		
		
		case "GROUP_ADD":
			if(GROUP_ADD($task_value)){unset($finalTasks[$taskid]);TASK_FINISH($taskid);}else{events("GROUP_ADD() \"$task_name\" FAILED",__FUNCTION__,__FILE__,__LINE__);$failed=true;}
		break;	
		
		default:
			events("Unable to understand TASK \"$task_name\"",__FUNCTION__,__FILE__,__LINE__);
			$failed=true;
		break;
	}
	
	events(count($finalTasks)." Tasks to execute",__FUNCTION__,__FILE__,__LINE__);
	if(count($finalTasks)==0){@unlink("/etc/artica-postfix/artica-meta.tasks");die();}
	events("Save tasks cache of ".count($finalTasks)." row(s)",__FUNCTION__,__FILE__,__LINE__);
	@file_put_contents("/etc/artica-postfix/artica-meta.tasks",base64_encode(serialize($finalTasks)));
	if(!$failed){
		events("Execute new instance for others tasks in background mode",__FUNCTION__,__FILE__,__LINE__);
		sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." ".__FILE__);
	}

}
	die();

function TestCron($pidfile){
	
	$unix=new unix();
	$oldpid=trim(@file_get_contents($pidfile));
	if($unix->process_exists($oldpid)){
		$time=file_time_min($pidfile);
		if($time>30){
			events("Already executed pid $oldpid since {$time}Mn (timeout), kill this task",__FUNCTION__,__FILE__,__LINE__);
			$kill=$unix->find_program("kill");
			$cmd="$kill -9 $oldpid";
			events("$cmd",__FUNCTION__,__FILE__,__LINE__);
			exec("$kill -9 $oldpid",$results);
			sleep(2);
			if($unix->process_exists($oldpid)){
				events("kill $oldpid failed",__FUNCTION__,__FILE__,__LINE__);
				return false;
			}else{
				return true;
			}
		}		
		events("Already executed pid $oldpid since {$time}Mn (pid file:$pidfile), aborting",__FUNCTION__,__FILE__,__LINE__);
		return false;
	}	
	return true;
}

function TASK_FINISH($taskid){
	$meta=new artica_meta(true);
	events("Ask to remove task $taskid",__FUNCTION__,__FILE__,__LINE__);
	$http=new httpget();
	$datasToSend=base64_encode(serialize($meta->GLOBAL_ARRAY));
	$body=$http->send("$meta->ArticaMetaHostname/lic.query.server.php","post",array("DATAS"=>$datasToSend,"KILL_TASK"=>$taskid));
	events("body=$body",__FUNCTION__,__FILE__,__LINE__);	
}

function TASK_UPDATE($task_value){
	
	$unix=new unix();
	$pidfile="/etc/artica-postfix/pids/". basename(__FILE__).".TASK_UPDATE.$task_value.pid";
	$oldpid=trim(@file_get_contents($pidfile));
	if($unix->process_exists($oldpid)){return false;}
	@file_put_contents($pidfile,getmypid());	
	events("$task_value:: running pid:".getmypid(),__FUNCTION__,__FILE__,__LINE__);
	$nice=EXEC_NICE();
	
	switch ($task_value) {
		case "UPDATE_OFFICIAL":
			exec("/usr/share/artica-postfix/bin/artica-update --force --verbose 2>&1",$f);
			send_email_events("Success executing official artica update",@implode("\n",$f),"CLOUD");
			return true;
		break;

		case "UPDATE_NIGHTHLY":
			exec("/usr/share/artica-postfix/bin/artica-update --upgrade-nightly --force --verbose 2>&1",$f);
			send_email_events("Success executing artica nightly update",@implode("\n",$f),"CLOUD");
			return true;
		break;
				
		case "UPGRADE_SYSTEM":
			UPGRADE_SYSTEM_DEBIAN();
			UPGRADE_SYSTEM_SUSE();
			return true;
		break;		
	}
	
	events("$task_value:: Unable to understand TASK_UPDATE::$task_value ",__FUNCTION__,__FILE__,__LINE__);
	
}

function UPGRADE_SYSTEM_SUSE(){
	$unix=new unix();
	$apt=$unix->find_program("zypper");
	if(!is_file($apt)){return true;}
	$tmpf=$unix->FILE_TEMP();
	$cmd="$apt --non-interactive update && zypper --non-interactive patch >$tmpf 2>&1";
	events("UPGRADE_SYSTEM_DEBIAN:: -> $cmd",__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);
	send_email_events("Success executing upgrade system",@file_get_contents($tmpf),"CLOUD");
	@unlink("/etc/artica-postfix/artica-meta-apt-check.cache");
	@unlink($tmpf);		
}


function UPGRADE_SYSTEM_DEBIAN(){
	$unix=new unix();
	$apt=$unix->find_program("apt-get");
	if(!is_file($apt)){return true;}
	$tmpf=$unix->FILE_TEMP();
	$cmd="DEBIAN_FRONTEND=noninteractive $apt -o Dpkg::Options::=\"--force-confnew\" --force-yes --yes upgrade >$tmpf 2>&1";
	events("UPGRADE_SYSTEM_DEBIAN:: -> $cmd",__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);
	send_email_events("Success executing upgrade system",@file_get_contents($tmpf),"CLOUD");
	@unlink("/etc/artica-postfix/artica-meta-apt-check.cache");
	@unlink($tmpf);	
	
}


function TASK_ADD_OU($value){
	$meta=new artica_meta(true);
	events("get ou value from $value",__FUNCTION__,__FILE__,__LINE__);
	$http=new httpget();
	$datasToSend=base64_encode(serialize($meta->GLOBAL_ARRAY));
	$body=$http->send("$meta->ArticaMetaHostname/lic.query.server.php","post",array("DATAS"=>$datasToSend,"GET_OU_NAME"=>$value));	
	if(preg_match("#<RESULTS>(.+?)</RESULTS>#",$body,$re)){$ou=base64_decode($re[1]);}
	if($ou==null){
		events("$value has no name",__FUNCTION__,__FILE__,__LINE__);
		return false;
	}
	
	$ldap=new clladp();
	if($ldap->AddOrganization($ou)){
		send_email_events("Organization [$ou] was successfully added",null,"CLOUD");
		events("ADD Organization \"$ou\"",__FUNCTION__,__FILE__,__LINE__);
		$http=new httpget();
		$body=$http->send("$meta->ArticaMetaHostname/lic.query.server.php","post",array("DATAS"=>$datasToSend,"UNLOCK_OU"=>$value));
		return true;
	}else{
		events("$ou:: Failed to add to the LDAP Database",__FUNCTION__,__FILE__,__LINE__);
	}
}

function TASK_DELETE_DOMAIN($value){
	$array=unserialize(base64_decode($value));
	if(!is_array($array)){
		events("VALUE is not an array",__FUNCTION__,__FILE__,__LINE__);
		send_email_events("Domain: could not delete domain VALUE is not an array",null,"CLOUD");
		return true;
	}	
	
	$domain_name=$array["DOMAIN"];
	$ou=$array["ou"];
	
	$ldap=new clladp();
	$dn="cn=$domain_name,cn=transport_map,ou=$ou,dc=organizations,$ldap->suffix";
	if($ldap->ExistsDN($dn)){
		$ldap->ldap_delete($dn,false);
	}
	$dn="cn=@$domain_name,cn=relay_recipient_maps,ou=$ou,dc=organizations,$ldap->suffix";
	if($ldap->ExistsDN($dn)){
		$ldap->ldap_delete($dn,false);
	}
	$dn="cn=$domain_name,cn=relay_domains,ou=$ou,dc=organizations,$ldap->suffix";	
	if($ldap->ExistsDN($dn)){
		$ldap->ldap_delete($dn,false);
	}
	
	$ldap->DeleteLocadDomain($domain_name,$ou);
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-transport-maps=yes");		
	send_email_events("Domain: success delete $domain_name in $ou organization",null,"CLOUD");
	return true;
}

function TASK_ADD_DOMAIN($value){
	$meta=new artica_meta(true);
	events("$value:: get domain parameters from $value",__FUNCTION__,__FILE__,__LINE__);
	$http=new httpget();
	$datasToSend=base64_encode(serialize($meta->GLOBAL_ARRAY));
	$body=$http->send("$meta->ArticaMetaHostname/lic.query.server.php","post",array("DATAS"=>$datasToSend,"GET_SMTP_DOM_PARAMS"=>$value));	
	if(preg_match("#<RESULTS>(.+?)</RESULTS>#",$body,$re)){$array=unserialize(base64_decode($re[1]));}
	if(!is_array($array)){
		events("VALUE is not an array",__FUNCTION__,__FILE__,__LINE__);
		return false;
	}	
	
	
	$domain=$array["domain"];
	$ou=$array["ou"];
	$transport=$array["transport"];
	events("$value:: Adding/editing domain $domain for $ou ($transport)",__FUNCTION__,__FILE__,__LINE__);
	$ldap=new clladp();
		if($transport=="LOCAL"){
			if($ldap->AddDomainEntity($ou,$domain)){
				$http=new httpget();
				$body=$http->send("$meta->ArticaMetaHostname/lic.query.server.php","post",array("DATAS"=>$datasToSend,"UNLOCK_DOMAIN"=>$value));
				send_email_events("Domain [$domain] was successfully added",null,"CLOUD");
				return true;
			}
		}
		
		
		if($transport==null){
			send_email_events("Domain [$domain] was failed to be added, transport type is null",null,"CLOUD");
			events("$value:: Adding/editing domain $domain failed, no such transport",__FUNCTION__,__FILE__,__LINE__);
			return true;
		}
		
		if(!preg_match("#\[(.+?)\]:([0-9]+)#",$transport,$re)){
			send_email_events("Domain [$domain] was failed to be added, \"$transport\" pattern is corrupted",null,"CLOUD");
			return true;
		}
		
		
		
		if($ldap->AddDomainTransport($ou,$domain,$re[1],$re[2])){
			$http=new httpget();
			$body=$http->send("$meta->ArticaMetaHostname/lic.query.server.php","post",array("DATAS"=>$datasToSend,"UNLOCK_DOMAIN"=>$value));
			events("$value:: Adding/editing domain $domain ($transport) success",__FUNCTION__,__FILE__,__LINE__);
			send_email_events("Domain [$domain] -> $transport was successfully added",null,"CLOUD");
			return true;			
		}else{
			events("$value:: Adding/editing domain $domain failed",__FUNCTION__,__FILE__,__LINE__);
			return false;			
		}
}

function TASK_EMAIL_ALIASES($zmd5){
	$meta=new artica_meta(true);
	events("Get aliases informations from $zmd5",__FUNCTION__,__FILE__,__LINE__);	
	$http=new httpget();
	$datasToSend=base64_encode(serialize($meta->GLOBAL_ARRAY));
	$body=$http->send("$meta->ArticaMetaHostname/lic.query.server.php","post",array("DATAS"=>$datasToSend,"GET_ALIASES_INFO"=>$zmd5));

	events("Received request.... ". strlen($body)." bytes",__FUNCTION__,__FILE__,__LINE__);
	if(preg_match("#<RESULTS>(.+?)</RESULTS>#",$body,$re)){$array=unserialize(base64_decode($re[1]));}
	if(!is_array($array)){
		events("{$array["uid"]}:: Unable to perform user's aliases modifications...",__FUNCTION__,__FILE__,__LINE__);	
		send_email_events("{$array["uid"]}:: Unable to perform user's aliases modifications...",null,"CLOUD");
		return false;
	}
	include_once(dirname(__FILE__).'/ressources/class.user.inc');
	events("-> new user({$array["uid"]})",__FUNCTION__,__FILE__,__LINE__);
	$user=new user($array["uid"]);
	events("-> new ldap()",__FUNCTION__,__FILE__,__LINE__);
	$ldap=new clladp();	
	if(is_array($user->aliases)){
	while (list ($index, $mail) = each ($user->aliases) ){	
		$updatearray["mailAlias"]=$mail;
		if(!$ldap->Ldap_del_mod($user->dn,$updatearray)){echo $ldap->ldap_last_error;}
		unset($updatearray);
		}
	}
	if(is_array($array["ALIASES"])){
		while (list ($index, $mail) = each ($array["ALIASES"]) ){	
			$user->add_alias($mail);
		}
	}	
	$user=new user($array["uid"]);
	events("done -> exec.postfix.hashtables.php --aliases",__FUNCTION__,__FILE__,__LINE__);
	send_email_events("{$array["uid"]}:: Success adding ".count($array["ALIASES"])." aliases now user count ".count($user->aliases)." aliases",null,"CLOUD");
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.hashtables.php --aliases");
	return true;
	}

	
function USER_TO_GROUP($value){
	include_once(dirname(__FILE__).'/ressources/class.user.inc');
	include_once(dirname(__FILE__).'/ressources/class.groups.inc');
	$array=unserialize(base64_decode($value));
	if(!is_array($array)){
		send_email_events("Failed to link user to group, not an array","","CLOUD");
		return true;
	}
	
	$guid=$array["guid"];
	$ou=$array["ou"];
	$uid=$array["uid"];
	events("Get group $guid informations $ou/$uid",__FUNCTION__,__FILE__,__LINE__);	
	$group=new groups($guid);
	if($group->AddUsertoThisGroup($uid)){
		send_email_events("Success to link user $uid to group $guid","","CLOUD");
	}else{
		send_email_events("Failed to link user $uid to group $guid","","CLOUD");
	}
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.artica.meta.users.php --user \"$uid\"");
	return true;
}	

function USER_REMOVE_GROUP(){
	include_once(dirname(__FILE__).'/ressources/class.user.inc');
	include_once(dirname(__FILE__).'/ressources/class.groups.inc');
	$array=unserialize(base64_decode($value));
	if(!is_array($array)){
		send_email_events("Failed to unlink user from group, not an array","","CLOUD");
		return true;
	}
	
	$guid=$array["guid"];
	$ou=$array["ou"];
	$uid=$array["uid"];
	events("Get group $guid informations $ou/$uid",__FUNCTION__,__FILE__,__LINE__);	
	$group=new groups($guid);
	if($group->DeleteUserFromThisGroup($uid)){
		send_email_events("Success to unlink user $uid from group $guid","","CLOUD");
	}else{
		send_email_events("Failed to unlink user $uid from group $guid","","CLOUD");
	}
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.artica.meta.users.php --user \"$uid\"");
	return true;	
}

function GROUP_ADD($value){
	include_once(dirname(__FILE__).'/ressources/class.user.inc');
	include_once(dirname(__FILE__).'/ressources/class.groups.inc');	
	$array=unserialize(base64_decode($value));
	if(!is_array($array)){
		send_email_events("Failed to add group, not an array","","CLOUD");
		return true;
	}
	
	$guid=$array["gid"];
	$ou=$array["ou"];
	$groupname=$array["groupname"];	
	
	events("Adding new group \"$ou\" gid:$guid Ou:$ou ",__FUNCTION__,__FILE__,__LINE__);	
	$group=new groups();
	
	if($group->add_new_group($groupname,$ou,$guid)){
		send_email_events("Success to add group $groupname","","CLOUD");
		events("Adding new group success",__FUNCTION__,__FILE__,__LINE__);
	
	}else{
		send_email_events("Failed to add group $groupname","","CLOUD");
		events("Adding new group Failed",__FUNCTION__,__FILE__,__LINE__);
	}
		
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.artica.meta.users.php --export-all-groups");
	return true;
}


function TASK_USER_EDIT($zmd5){
	$meta=new artica_meta(true);
	include_once(dirname(__FILE__).'/ressources/class.user.inc');
	events("Get user informations from $zmd5",__FUNCTION__,__FILE__,__LINE__);	
	$http=new httpget();
	$datasToSend=base64_encode(serialize($meta->GLOBAL_ARRAY));
	$body=$http->send("$meta->ArticaMetaHostname/lic.query.server.php","post",array("DATAS"=>$datasToSend,"GET_USER_INFO"=>$zmd5));		
	if(preg_match("#<RESULTS>(.+?)</RESULTS>#",$body,$re)){$array=unserialize(base64_decode($re[1]));}
	
	if(!is_array($array)){
		events("Get user informations ERROR not an Array",__FUNCTION__,__FILE__,__LINE__);
		send_email_events("Failed to add user task id \"$zmd5\"","Error detected\nGet user informations ERROR not an Array","CLOUD");
		return true;
	}
	
	foreach($array as $key => $value) {$userArray[$key]=$value;}
	$user=new user($userArray["uid"]);
	$user->ou=$userArray["ou"];
	events("Get user informations {$userArray["uid"]} done",__FUNCTION__,__FILE__,__LINE__);	
	$ldap=new clladp();
	$ldap->AddOrganization($user->ou);
	
	
	$user->password=$userArray["userpassword"];
	$user->mail=$userArray["mail"];
	$user->DisplayName=$userArray["displayname"];
	$user->homeDirectory=$userArray["homedirectory"];
	$user->sn=$userArray["sn"];
	$user->group_id=$userArray["gidnumber"];
	$user->FTPDownloadBandwidth=$userArray["ftpdownloadbandwidth"];
	$user->FTPDownloadRatio=$userArray["ftpdownloadratio"];
	$user->FTPQuotaFiles=$userArray["ftpquotafiles"];
	$user->FTPQuotaMBytes=$userArray["ftpquotambytes"];
	$user->FTPUploadRatio=$userArray["ftpuploadratio"];
	$user->postalCode=$userArray["postalcode"];
	$user->postalAddress=$userArray["postaladdress"];
	$user->street=$userArray["street"];
	$user->givenName=$userArray["givenname"];
	$user->mobile=$userArray["mobile"];
	$user->telephoneNumber=$userArray["telephonenumber"];
	$user->zarafaQuotaHard=$userArray["zarafaQuotaHard"];
	$user->zarafaQuotaWarn=$userArray["zarafaQuotaWarn"];
	$user->zarafaQuotaSoft=$userArray["zarafaQuotaSoft"];	
	
	if(trim($userArray["mailboxsecurityparameters"])==null){
		$userArray["mailboxsecurityparameters"]="[mailbox]\nl=1\nr=1\ns=1\nw=1\ni=1\np=1\nc=1\nd=1\na=1";
	}
	
	if(trim($userArray["mailboxactive"])==null){$userArray["mailboxactive"]="TRUE";}
	if($userArray["mailboxactive"]==1){$userArray["mailboxactive"]="TRUE";}else{$userArray["mailboxactive"]="FALSE";}
	
	
	$user->MailboxSecurityParameters=$userArray["mailboxsecurityparameters"];
	$user->MailboxActive=$userArray["mailboxactive"];
	$user->MailBoxMaxSize=$userArray["mailboxmaxsize"];
	
	events("Saving user information...",__FUNCTION__,__FILE__,__LINE__);	
	
	if(!$user->add_user()){
		events("Failed to add user {$userArray["uid"]}",__FUNCTION__,__FILE__,__LINE__);	
		send_email_events("Failed to add {$userArray["uid"]}","reason $user->error","CLOUD");
		return false;
		
	}else{
		events("Call to unlock user",__FUNCTION__,__FILE__,__LINE__);	
		$http=new httpget();
		send_email_events("Success to add {$userArray["uid"]}","Adding this user:\n{$userArray["mail"]}\nOrganization:{$userArray["ou"]}\n","CLOUD");
		$body=$http->send("$meta->ArticaMetaHostname/lic.query.server.php","post",array("DATAS"=>$datasToSend,"UNLOCK_USER"=>$zmd5));
		return true;		
	}
	

	
}

function TASK_CHANGE_HOSTNAME($hostname){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?ChangeHostName=$hostname");
	$sock->SET_INFO("myhostname",$hostname);
	$users=new usersMenus();
	if($users->POSTFIX_INSTALLED){$sock->getFrameWork("cmd.php?postfix-others-values=yes");}
	send_email_events("Success to change hostname to $hostname",null,"CLOUD");
	return true;		
}

function TASK_RESTART_NETWORK($value){
	if(is_file("/etc/init.d/network")){
		$cmd="/etc/init.d/network";
	}else{
		if(is_file("/etc/init.d/networking")){
			$cmd="/etc/init.d/networking";
		}
	}
	
	if(!is_file($cmd)){
		send_email_events("Failed to restart network","Unable to find init.d command\n","CLOUD");
		return true;
	}
	
	exec("$cmd 2>&1",$results);
	send_email_events("Success restart network configuration",@implode("\n",$results),"CLOUD");
	return true;
	}

function TASK_SETTINGS_SOCKETS($key){
	$EXEC_NICE=EXEC_NICE();
	$meta=new artica_meta(true);
	events("Get key informations from $key",__FUNCTION__,__FILE__,__LINE__);	
	$http=new httpget();
	$datasToSend=base64_encode(serialize($meta->GLOBAL_ARRAY));
	$body=$http->send("$meta->ArticaMetaHostname/lic.query.server.php","post",array("DATAS"=>$datasToSend,"GET_SOCKETS_INFO"=>$key));		
	if(!preg_match("#<RESULTS>(.+?)</RESULTS>#",$body,$re)){
		events("Get key informations from $key FAILED",__FUNCTION__,__FILE__,__LINE__);	
		return false;
	}	
	
	$value=base64_decode($re[1]);
	$sock=new sockets();
	
	events("Saving key $key",__FUNCTION__,__FILE__,__LINE__);
	$filewrite=@file_put_contents("/etc/artica-postfix/settings/Daemons/$key",$value,LOCK_EX);
	if(!$filewrite){
		events("Save configuration settings [$key] = ". strlen($value) ." Failed",__FUNCTION__,__FILE__,__LINE__);
		send_email_events("failed to modify settings \"$key\"",null,"CLOUD");
	}
	events("Save configuration settings [$key] =$filewrite bytes SUCCESS",__FUNCTION__,__FILE__,__LINE__);
	
	
	
switch ($key) {
		case "NetworkScannerMasks":
			$sock->getFrameWork("cmd.php?squid-rebuild=yes");
			$sock->getFrameWork("cmd.php?postfix-networks=yes");
			send_email_events("Success to modify settings \"$key\"","Postfix or Squid was scheduled to be reloaded.\n","CLOUD");
		break;
		
		case "DDClientConfig":
			$sock->getFrameWork("cmd.php?squid-rebuild=yes");
			$sock->getFrameWork("cmd.php?ddclient=yes");
			send_email_events("Success to modify settings \"$key\"","DDClient And Squid was scheduled to be reloaded.\n","CLOUD");
		break;	

		case "EnableDDClient":
			$sock->getFrameWork("cmd.php?ddclient=yes");
			send_email_events("Success to modify settings \"$key\"","DDClient was scheduled to be reloaded.\n","CLOUD");
		break;		
		
}

$SQUID["SQUIDEnable"]=true;
$SQUID["ArticaSquidParameters"]=true;
$SQUID["hasProxyTransparent"]=true;
$SQUID["EnableClamavInCiCap"]=true;
$SQUID["EnableUfdbGuard"]=true;
$SQUID["EnableAdZapper"]=true;
$SQUID["EnableSquidClamav"]=true;
$SQUID["SquidBlockSites"]=true;
$SQUID["ArticaEnableKav4ProxyInSquid"]=true;
$SQUID["DansGuardianEnabled"]=true;
$SQUID["SquidExternalAuth"]=true;
$SQUID["SquidFTPParams"]=true;
$SQUID["OpenDNSConfig"]=true;
$SQUID["EnableOpenDNSInProxy"]=true;


$SAMBA["SambaSMBConf"]=true;

$COMMAND["PostfixBinInterfaces"]="cmd.php?postfix-interfaces=yes";
$COMMAND["PostfixMynetworks"]="cmd.php?postfix-networks=yes";
$COMMAND["myhostname"]="cmd.php?postfix-others-values=yes";
$COMMAND["HashMainCf"]="cmd.php?postfix-others-values=yes";
$COMMAND["smtp_bind_address6"]="cmd.php?postfix-interfaces=yes";
$COMMAND["PostfixEnableIpv6"]="cmd.php?postfix-interfaces=yes";
$COMMAND["EnableCluebringer"]="cmd.php?cluebringer-restart=yes";
$COMMAND["EnableASSP"]="cmd.php?restart-assp=yes";

$COMMAND["KasxFilterEnabled"]="cmd.php?SaveMaincf=yes";
$COMMAND["EnableArticaSMTPFilter"]="cmd.php?artica-filter-reload=yes";
$COMMAND["EnableAmavisDaemon"]="cmd.php?SaveMaincf=yes";
$COMMAND["SpamAssMilterEnabled"]="cmd.php?SaveMaincf=yess";
$COMMAND["kavmilterEnable"]="cmd.php?SaveMaincf=yes";
$COMMAND["SpamAssMilterEnabled"]="cmd.php?SaveMaincf=yes";
$COMMAND["EnableArticaPolicyFilter"]="cmd.php?artica-policy-restart=yes";

$COMMAND["ArticaOpenVPNSettings"]="cmd.php?restart-openvpn-server=yes";
$COMMAND["EnableOPenVPNServerMode"]="cmd.php?restart-openvpn-server=yes";
$COMMAND["OpenVPNRoutes"]="cmd.php?restart-openvpn-server=yes";



$RESTART_STATUS["ArticaOpenVPNSettings"]=true;
$RESTART_STATUS["EnableOPenVPNServerMode"]=true;

$REBUILD_OPENVPN_CERTS["ArticaOpenVPNSettings"]=true;
$REBUILD_OPENVPN_CERTS["EnableOPenVPNServerMode"]=true;


if($REBUILD_OPENVPN_CERTS[$key]){
	shell_exec("/bin/rm -rf /etc/artica-postfix/openvpn/keys/*");
	exec("/etc/init.d/artica-postfix restart openvpns",$results);
	send_email_events("OpenVPN certificate was rebuilded",@implode("\n",$results),"VPN");
	shell_exec(LOCATE_PHP5_BIN2()." ".dirname(__FILE__)."/exec.artica.meta.users.php --export-openvpn-users &");
}



if($SQUID[$key]){
	$sock->getFrameWork("cmd.php?squid-rebuild=yes");
	$sock->getFrameWork("cmd.php?squidnewbee=yes");
	send_email_events("Success to modify settings \"$key\"","Squid was scheduled to be reloaded.\n","CLOUD");
}

if($SAMBA[$key]){
	include_once(dirname(__FILE__)."/ressources/class.samba.inc");
	events("Loading samba class and save new samba configuration....",__FUNCTION__,__FILE__,__LINE__);
	$smb=new samba();
	$smb->SaveToLdap();
}


if($COMMAND[$key]<>null){
	$sock->getFrameWork($COMMAND[$key]);
	events("Execute {$COMMAND[$key]}",__FUNCTION__,__FILE__,__LINE__);
}else{
	events("No command for \"$key\"",__FUNCTION__,__FILE__,__LINE__);
}

if($RESTART_STATUS[$key]){
	events("restarting artica status....",__FUNCTION__,__FILE__,__LINE__);
	@unlink("/usr/share/artica-postfix/ressources/logs/global.status.ini");
	$sock->getFrameWork("cmd.php?restart-artica-status=yes");
}else{
	events("No need to restart artica-status....",__FUNCTION__,__FILE__,__LINE__);
}

sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." ".dirname(__FILE__)."/exec.artica.meta.users.php --export-all-settings");
events("FINISH, Return true",__FUNCTION__,__FILE__,__LINE__);
return true;	
	
}

function TASK_SQUID_CACHE($value){
	$value=unserialize(base64_decode($value));
	if(!is_array($value)){send_email_events("Failed to add/modify squid cache (not an array)\n","CLOUD");return true;}
	include_once(dirname(__FILE__).'/ressources/class.squid.inc');
	$sock=new sockets();
	$squid=new squidbee();
	
	if($value["cache_directory"]==$squid->CACHE_PATH){
		$squid->CACHE_SIZE=$value["cache_size"];
		$squid->CACHE_TYPE=$value["cache_type"];
		send_email_events("Success add/edit squid cache \"{$value["cache_directory"]}\"","Squid was scheduled to be reloaded.\n","CLOUD");
		$squid->SaveToLdap(true);
		$squid->SaveToServer(true);	
		$sock->getFrameWork("cmd.php?squid-build-caches=yes");
		shell_exec(LOCATE_PHP5_BIN2()." ". dirname(__FILE__)."/exec.artica.meta.php --force");
		return true;	
	}
	


	$squid->cache_list[$value["cache_directory"]]=array(
		"cache_type"=>$value["cache_type"],
		"cache_dir_level1"=>$value["cache_dir_level1"],
		"cache_dir_level2"=>$value["cache_dir_level2"],
		"cache_size"=>$value["cache_size"],
		);	
	
	
	
	$SquidCacheTasks=unserialize(base64_decode($sock->GET_INFO("SquidCacheTask")));
	$SquidCacheTasks[$value["cache_directory"]]=$value;
	$sock->SaveConfigFile(base64_encode(serialize($SquidCacheTasks)),"SquidCacheTask");
	$squid->SaveToLdap(true);
	$squid->SaveToServer(true);
	$sock->getFrameWork("cmd.php?squid-build-caches=yes");
	send_email_events("Success add/edit squid cache \"{$value["cache_directory"]}\"","Squid was scheduled to be reloaded.\n","CLOUD");
	shell_exec(LOCATE_PHP5_BIN2()." ". dirname(__FILE__)."/exec.artica.meta.php --force");	
	return true;
	
	
	
}

function SQUID_DELETE_CACHE($value){
	$value=unserialize(base64_decode($value));
	if(!is_array($value)){send_email_events("Failed to add/modify squid cache (not an array)",null,"CLOUD");return true;}
	include_once(dirname(__FILE__).'/ressources/class.squid.inc');
	$sock=new sockets();
	$squid=new squidbee();
	unset($squid->cache_list[$value["cache_directory"]]);
	$squid->SaveToLdap();
	$squid->SaveToServer();
	$sock=new sockets();
	$SquidCacheTasks=unserialize(base64_decode($sock->GET_INFO("SquidCacheTask")));
	$SquidCacheTasks[$value["cache_directory"]]=$_GET;
	$sock->SaveConfigFile(base64_encode(serialize($SquidCacheTasks)),"SquidCacheTask");	
	$sock->getFrameWork("cmd.php?squid-build-caches=yes");		
	send_email_events("Success removing squid cache \"{$value["cache_directory"]}\"","Squid was scheduled to be reloaded.\n","CLOUD");
	return true;
}

function DNS_ENTRY($value){
	$EXEC_NICE=EXEC_NICE();
	if(is_file("/usr/bin/nohup")){$nohup="/usr/bin/nohup ";}	
	$reload_datas=$nohup.$EXEC_NICE.LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.artica.meta.users.php --export-all-dns >/dev/null 2>&1 &";
	include_once(dirname(__FILE__) . "/ressources/class.pdns.inc");
	$array=unserialize(base64_decode($value));
	if(!is_array($array)){
		send_email_events("Failed to add/modify DNS entry (not an array)",null,"CLOUD");
		shell_exec($reload_datas);
		return true;
	}
	$sock=new sockets();
	$MAC=$array["MAC"];
	$computername=$array["hostname"];
	$DnsZoneName=$array["DOMAIN"];
	$ComputerIP=$array["IP"];
	$tbl=explode(".",$computername);
	$computername=$tbl[0];
	if($DnsZoneName==null){
		unset($tbl[0]);
		$DnsZoneName=@implode(".",$tbl);
	}
	
	
	writelogs("Adding New dns entry $computername ($ComputerIP) mac:$MAC in \"$DnsZoneName\" domain",__FUNCTION__,__FILE__,__LINE__);
	
	$pdns=new pdns($DnsZoneName);
	if(!$pdns->EditIPName($computername,$ComputerIP,"A",$MAC)){
		send_email_events("Failed to add/modify DNS entry $computername $pdns->last_error",null,"CLOUD");
		shell_exec($reload_datas);
		return true;
	}else{
		send_email_events("Success to add/modify DNS entry $computername ($ComputerIP)",null,"CLOUD");
		return true;
	}	
	
}

function FREEWEB_DELETE($value){
	$hostname=base64_decode($value);
	$EXEC_NICE=EXEC_NICE();
	if(is_file("/usr/bin/nohup")){$nohup="/usr/bin/nohup ";}	
	$reload_datas=$nohup.$EXEC_NICE.LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.artica.meta.users.php --export-freewebs >/dev/null 2>&1 &";
	include_once(dirname(__FILE__).'/ressources/class.freeweb.inc');
	$freeweb=new freeweb($hostname);
	$freeweb->delete();
	send_email_events("Success deleting $hostname web site",null,"CLOUD");
	shell_exec($reload_datas);
	return true;
	}
function GROUPWARE_DELETE($value){
	$hostname=base64_decode($value);
	$EXEC_NICE=EXEC_NICE();
	if(is_file("/usr/bin/nohup")){$nohup="/usr/bin/nohup ";}	
	$reload_datas=$nohup.$EXEC_NICE.LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.artica.meta.users.php --export-all-groupwares >/dev/null 2>&1 &";	
	$sock=new sockets();
	echo $sock->getFrameWork("cmd.php?vhost-delete=$hostname");
	send_email_events("Success deleting $hostname groupware",null,"CLOUD");
	shell_exec($reload_datas);
	return true;		
}

function FREEWEB_ADD($value){
	
	$array=unserialize(base64_decode($value));
	$EXEC_NICE=EXEC_NICE();
	if(is_file("/usr/bin/nohup")){$nohup="/usr/bin/nohup ";}	
	$reload_datas=$nohup.$EXEC_NICE.LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.artica.meta.users.php --export-freewebs >/dev/null 2>&1 &";
	$q=new mysql();
	
	$sql="SELECT servername FROM freeweb WHERE servername='{$array["servername"]}'";
	
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
	if($ligne["servername"]<>null){
		$sql="UPDATE freeweb SET 
			mysql_password='{$array["mysql_password"]}',
			mysql_username='{$array["mysql_username"]}',
			ftpuser='{$array["ftpuser"]}',
			ftppassword='{$array["ftppassword"]}',
			uid='{$array["uid"]}',
			useSSL='{$array["useSSL"]}' WHERE servername='{$array["servername"]}'
		";
	}else{
		$sql="INSERT INTO freeweb (mysql_password,mysql_username,ftpuser,ftppassword,useSSL,servername,mysql_database,uid)
		VALUES('{$array["mysql_password"]}','{$array["mysql_username"]}','{$array["ftpuser"]}',
		'{$array["ftppassword"]}','{$array["useSSL"]}','{$array["servername"]}','{$array["mysql_database"]}','{$array["uid"]}')";
	}	
	
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		events("$q->mysql_error for {$array["servername"]}",__FUNCTION__,__FILE__,__LINE__);
		send_email_events("Failed creating {$array["servername"]} web site",$q->mysql_error,"CLOUD");
		shell_exec($reload_datas);
		return true;
	}
	include_once('ressources/class.pure-ftpd.inc');	
	$pure=new pureftpd_user();
	if(!$pure->CreateUser($array["ftpuser"],$array["ftppassword"],$array["servername"])){
		events("Failed creating ftp account for {$array["servername"]}",__FUNCTION__,__FILE__,__LINE__);
		send_email_events("Failed creating ftp account for {$array["servername"]} web site",null,"CLOUD");
		return;
	}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?freeweb-restart=yes");	
	send_email_events("Success creating {$array["servername"]} web site",null,"CLOUD");
	return true;
}


function UPDATE_AWSTATS($value){
	$array=unserialize(base64_decode($value));
	$EXEC_NICE=EXEC_NICE();
	if(is_file("/usr/bin/nohup")){$nohup="/usr/bin/nohup ";}	
	$reload_datas=$nohup.$EXEC_NICE.LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.artica.meta.users.php --export-awstats >/dev/null 2>&1 &";

	if($array["servername"]==null){
		send_email_events("Failed updating {$array["servername"]} awstats config",null,"CLOUD");
		return true;
	}
	include_once('ressources/class.awstats.inc');	
	$aw=new awstats($array["servername"]);
	while (list ($key, $val) = each ($array) ){
		$aw->SET($key,$val);		
	}
	send_email_events("Success updating {$array["servername"]} awstats config ".count($array) ." items",null,"CLOUD");
	return true;
	
	
}

function ADD_GROUPWARE($value){
	$array=unserialize(base64_decode($value));
	$EXEC_NICE=EXEC_NICE();
	if(is_file("/usr/bin/nohup")){$nohup="/usr/bin/nohup ";}	
	include_once('ressources/class.apache.inc');
	$reload_datas=$nohup.$EXEC_NICE.LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.artica.meta.users.php --export-all-groupwares >/dev/null 2>&1 &";
	$vhosts=new vhosts();
	$hostname=$array["apacheservername"];
	/*	$pdns=new pdns($domain);
		$pdns->EditIPName($servername,$IP,"A",null);		
	}else{
		$hostname=$_GET["host"];
	}*/

	$vhosts=new vhosts();
	$vhosts->ou=$array["ou"];
	$vhosts->apachedomainname=$array["apachedomainname"];
	$vhosts->apacheIPAddress=$array["apacheIPAddress"];
	$vhosts->BuildRoot();
	$vhosts->WWWAppliPassword=$array["WWWAppliPassword"];
	$vhosts->WWWAppliUser=$array["wwwappliuser"];
	$vhosts->WWWMysqlUser=$array["wwwmysqluser"];
	$vhosts->WWWMysqlPassword=$array["wwwmysqlpassword"];
	$vhosts->WWWSSLMode=$array["wwwsslmode"];
	if(isset($array["WWWEnableAddressBook"])){$vhosts->WWWEnableAddressBook=$array["WWWEnableAddressBook"];}
	$vhosts->WWWMultiSMTPSender=$array["WWWMultiSMTPSender"];
	
	
	if($vhosts->Addhost($array["apacheservername"],$array["wwwservertype"])){
		send_email_events("Success Adding/editing {$array["apacheservername"]} groupware service",null,"CLOUD");
		events("Success Adding/editing {$array["apacheservername"]} groupware service",__FUNCTION__,__FILE__,__LINE__);
		return true;
		
	}else{
		send_email_events("Failed Adding/editing {$array["apacheservername"]} groupware service",null,"CLOUD");
		events("Failed Adding/editing {$array["apacheservername"]} groupware service",__FUNCTION__,__FILE__,__LINE__);
	}	

}

function OPENVPN_SCRIPTS(){
	$cmd=LOCATE_PHP5_BIN2()." ".dirname(__FILE__)."/exec.artica.meta.users.php --export-openvpn-users";
	shell_exec($cmd);
	send_email_events("Success launch task for exporting all openvpn scripts",null,"CLOUD");
	return true;
	}

function OPENVPN_SYNC_VPN_SITE(){
	OPENVPN_SCRIPTS();
	$EXEC_NICE=EXEC_NICE();
	if(is_file("/usr/bin/nohup")){$nohup="/usr/bin/nohup ";}	
	$reload_datas=$nohup.$EXEC_NICE.LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.artica.meta.users.php --export-openvpn-sites >/dev/null 2>&1 &";			
	return true;
}

function OPENVPN_REMOTE_SITE_EDIT($value){
	events("Decoding parameters",__FUNCTION__,__FILE__,__LINE__);
	$array=unserialize(base64_decode($value));
	$EXEC_NICE=EXEC_NICE();
	if(is_file("/usr/bin/nohup")){$nohup="/usr/bin/nohup ";}	
	$reload_datas=$nohup.$EXEC_NICE.LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.artica.meta.users.php --export-openvpn-sites >/dev/null 2>&1 &";	
	if(!is_array($array)){
		events("Corrupted data, aborting",__FUNCTION__,__FILE__,__LINE__);
		send_email_events("Failed to import remote site parameters from Artica Meta (corrupted datas)",null,"VPN");
		shell_exec($reload_datas);
		return true;
	}	
	
	if(!is_numeric($array["ID"])){
		events("Corrupted data, unable to find ID table identifier",__FUNCTION__,__FILE__,__LINE__);
		send_email_events("Failed to import remote site parameters from Artica Meta (unable to find ID table identifier)",null,"VPN");
		shell_exec($reload_datas);
		return true;		
	}
	
	if(isset($array["enabled"])){$f[]="`enabled`='{$array["enabled"]}'";}
	if(isset($array["ethlisten"])){$f[]="`ethlisten`='{$array["ethlisten"]}'";}
	if(isset($array["routes_additionnal"])){$f[]="`routes_additionnal`='{$array["routes_additionnal"]}'";}
	
	
	$sql="UPDATE vpnclient SET ". @implode(",",$f)." WHERE ID={$array["ID"]}";
	events($sql,__FUNCTION__,__FILE__,__LINE__);
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		events("Corrupted data, (mysql error) $q->mysql_error",__FUNCTION__,__FILE__,__LINE__);
		send_email_events("Failed to import remote site parameters from Artica Meta (mysql error)",$q->mysql_error,"VPN");
		shell_exec($reload_datas);
		return true;	
	}
	shell_exec($reload_datas);
	shell_exec("/etc/init.d/artica-postfix restart openvpn");
	send_email_events("Success to import remote site parameters from Artica Meta ID:{$array["ID"]}",null,"VPN");
	return true;
	
}

function OPENVPN_CONCENTRATEUR($value){
	$unix=new unix();
	$unzip=$unix->find_program("unzip");
	events("Decoding parameters",__FUNCTION__,__FILE__,__LINE__);
	$array=unserialize(base64_decode($value));
	$EXEC_NICE=EXEC_NICE();
	if(is_file("/usr/bin/nohup")){$nohup="/usr/bin/nohup ";}	
	$reload_datas=$nohup.$EXEC_NICE.LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.artica.meta.users.php --export-openvpn-sites >/dev/null 2>&1 &";			
	
	if(strlen($unzip)==0){
		events("Unable to stat unzip, aborting",__FUNCTION__,__FILE__,__LINE__);
		send_email_events("Failed to import concentrator parameters (unzip no such file)",null,"VPN");
		return true;		
	}
	
	if(!is_array($array)){
		events("Corrupted data, aborting",__FUNCTION__,__FILE__,__LINE__);
		send_email_events("Failed to import concentrator parameters (corrupted datas)",null,"VPN");
		return true;
	}
	
	$master=$array["MASTER"];
	$sitename=$array["SITENAME"];
	events("download configuration file for master:$master and site name: $sitename",__FUNCTION__,__FILE__,__LINE__);
	$meta=new artica_meta(true);
	$http=new httpget();
	$datasToSend=base64_encode(serialize($meta->GLOBAL_ARRAY));
	$body=$http->send("$meta->ArticaMetaHostname/lic.query.server.php","post",array("DATAS"=>$datasToSend,
	"GET_OVPN_REMOTE_SITE"=>"yes",
	"master_uuid"=>"$master",
	"sitename"=>"$sitename",
	));	
	
	if(strlen($body)<10){
		events("Corrupted data after downloading, aborting",__FUNCTION__,__FILE__,__LINE__);
		send_email_events("Failed to import concentrator parameters (corrupted datas after download)",null,"VPN");
		return true;
	}
	
	
	@mkdir("/tmp/$sitename",666,true);
	@file_put_contents("/tmp/$sitename/conf.zip",$body);
	shell_exec("$unzip -j -o /tmp/$sitename/conf.zip -d /tmp/$sitename/ >/tmp/$sitename/unzip.txt 2>&1");
	include_once("ressources/class.openvpn.inc");	
	$openvpn=new openvpn();
	$handle=opendir("/tmp/$sitename");
	$f=false;
	while (false !== ($file = readdir($handle))) {
		if(preg_match("#(.+?).ovpn$#",$file)){
			if(!$openvpn->ImportConcentrateur("/tmp/$sitename/$file",$master)){
				events("ImportConcentrateur() failed".@implode("\n",$openvpn->events),__FUNCTION__,__FILE__,__LINE__);
				send_email_events("Failed to import concentrator parameters ()",@implode("\n",$openvpn->events),"VPN");
				return true;
			}
		
		$f=true;
		}
	}
	
	if(!$f){
		events("Corrupted data after downloading, aborting",__FUNCTION__,__FILE__,__LINE__);
		send_email_events("Failed to import concentrator parameters (unable to find ovpn file after uncompress)",null,"VPN");
	}
	shell_exec($reload_datas);
	events("Restarting OpenVPN",__FUNCTION__,__FILE__,__LINE__);
	shell_exec("/etc/init.d/artica-postfix restart openvpn &");
	return true;		
	
}




function OPENVPN_DELETE_VPN_SITE($value){
	$ID=base64_decode($value);
	$EXEC_NICE=EXEC_NICE();
	if(is_file("/usr/bin/nohup")){$nohup="/usr/bin/nohup ";}	
	$reload_datas=$nohup.$EXEC_NICE.LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.artica.meta.users.php --export-openvpn-sites >/dev/null 2>&1 &";		
	
	$ID=base64_decode($value);
	events("Ask to delete remote vpn site $ID",__FUNCTION__,__FILE__,__LINE__);
	
	
	if($ID>0){
		$sql="DELETE FROM vpnclient WHERE ID=$ID";
		events("$sql",__FUNCTION__,__FILE__,__LINE__);
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){
			send_email_events("Failed to delete new remote site $ID (mysql error)",$q->mysql_error,"VPN");
			shell_exec($reload_datas);
			return true;
		}
	}
	send_email_events("Success executing deleting remote VPN ID $ID",null,"VPN");
	shell_exec($reload_datas);
}

function OPENVPN_EDIT_VPN_SITE($value){
	
	$array=unserialize(base64_decode($value));
	$EXEC_NICE=EXEC_NICE();
	if(is_file("/usr/bin/nohup")){$nohup="/usr/bin/nohup ";}	
	$reload_datas=$nohup.$EXEC_NICE.LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.artica.meta.users.php --export-openvpn-sites >/dev/null 2>&1 &";	
	
	if(!is_array($array)){
		send_email_events("Failed to add new VPN remote site (not an array)",null,"VPN");
		shell_exec($reload_datas);
		return true;
	}
	
	
	
	$ip="{$array["ip_1"]}.{$array["ip_2"]}.{$array["ip_3"]}.0";
	$mask="{$array["mask_1"]}.{$array["mask_2"]}.{$array["mask_3"]}.{$array["mask_4"]}";	
	
	
	
	$sql="SELECT ID FROM `vpnclient` WHERE `IP_START`='$ip' AND `netmask`='$mask'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$ID=$ligne["ID"];
	
	$sql="INSERT INTO vpnclient(`servername`,`sitename`,`IP_START`,`netmask`,`connexion_type`) VALUE
	('{$array["servername"]}','{$array["sitename"]}','$ip','$mask','1')";
	
	if($array["ID"]>0){$ID=$array["ID"];}
	
	
	if($ID>0){
		events("Edit remote vpn site $ip/$mask ({$array["sitename"]} ID:{$array["ID"]})",__FUNCTION__,__FILE__,__LINE__);
		$sql="UPDATE `vpnclient` SET `servername`='{$array["servername"]}',`sitename`='{$array["sitename"]}',`IP_START`='$ip', `netmask`='$mask' WHERE `ID`='$ID'";
	}else{
		events("Add remote vpn site $ip/$mask ({$array["sitename"]} ID:{$array["ID"]})",__FUNCTION__,__FILE__,__LINE__);
	}	
	
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		events("failed $q->mysql_error",__FUNCTION__,__FILE__,__LINE__);
		send_email_events("Failed to add new VPN remote site {$array["sitename"]} (mysql error)",$q->mysql_error,"VPN");
		shell_exec($reload_datas);
		return true;
	}
	events("Success operation on VPN remote site {$array["sitename"]}",__FUNCTION__,__FILE__,__LINE__);
	send_email_events("Success to add new VPN remote site {$array["sitename"]}",$q->mysql_error,"VPN");	
	shell_exec($reload_datas);
	return true;
	
}

function SUPERADMIN($value){
	$array=unserialize(base64_decode($value));
	$ldap=new clladp();
	$sock=new sockets();
	$username=$array["ACCOUNT"];
	$password=$array["PASSWORD"];
	$md5=md5($username.$password);	
	$md52=md5(trim($ldap->ldap_admin).trim($ldap->ldap_password));
	$ldap_server=$ldap->ldap_host;
	$ldap_port=$ldap->ldap_port;
	$suffix=$ldap->suffix;
	$change_ldap_server_settings="no";
	$password=base64_encode($password);
	writelogs("change_password $password",__FUNCTION__,__FILE__,__LINE__ );
	
	if($ldap_server==null){$ldap_server="127.0.0.1";}
	if($ldap_port==null){$ldap_port="389";}
	if($suffix==null){$suffix="dc=nodomain";}
	
	$cmd="cmd.php?ChangeLDPSSET=yes&ldap_server=$ldap_server&ldap_port=$ldap_port&suffix=$suffix";
	$cmd=$cmd."&change_ldap_server_settings=$change_ldap_server_settings&username=$username&password=$password";
	$datas=$sock->getFrameWork("$cmd");
	$results="success:$ldap_server:$ldap_port ($suffix)\n$username\n-------\"\"------";	
	send_email_events("Success change SuperAdmin to \"$username\"",$results,"CLOUD");
	return true;
}

function FETCHMAIL_DELETE($value){
	$array=unserialize(base64_decode($value));	
	$tasksynchro=shell_exec(LOCATE_PHP5_BIN2()." ".dirname(__FILE__)."/exec.artica.meta.users.php --export-fetchmail-rules");
	foreach($array as $key => $value) {$FINAL[$key]=$value;}
	include_once(dirname(__FILE__)."/ressources/class.fetchmail.inc");	
	$fetch=new Fetchmail_settings();
	if($fetch->DeleteRule($FINAL["ID"],$FINAL["uid"])){
		send_email_events("Success deleting rule {$FINAL["ID"]} for {$FINAL["uid"]}",null,"CLOUD");
		shell_exec($tasksynchro);
		
	}else{
		send_email_events("Failed deleting rule {$FINAL["ID"]} for {$FINAL["uid"]}",null,"CLOUD");
		shell_exec($tasksynchro);
	}	
	
	return true;
}

function FETCHMAIL_EDIT($value){
	$array=unserialize(base64_decode($value));
	$tasksynchro=shell_exec(LOCATE_PHP5_BIN2()." ".dirname(__FILE__)."/exec.artica.meta.users.php --export-fetchmail-rules");
	foreach($array as $key => $value) {$FINAL[$key]=$value;}
	include_once(dirname(__FILE__)."/ressources/class.fetchmail.inc");
	$fetch=new Fetchmail_settings();
	if($FINAL["ID"]>0){
		if($fetch->EditRule($FINAL,$FINAL["ID"])){
			send_email_events("Success Editing rule ID {$FINAL["ID"]} for {$FINAL["uid"]}",null,"CLOUD");
			return true;
		}else{
			send_email_events("Failed Editing rule ID {$FINAL["ID"]} for {$FINAL["uid"]}",null,"CLOUD");
			return true;
		}
	}
	
	if($fetch->AddRule($FINAL)){
		send_email_events("Success Adding new rule for {$FINAL["uid"]}",null,"CLOUD");
		shell_exec($tasksynchro);
		
	}else{
		send_email_events("Failed Adding new rule for {$FINAL["uid"]}",null,"CLOUD");
		shell_exec($tasksynchro);
	}
	
	return true;
	
}

function AWSTATS_RUN($value){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?awstats-perform=".base64_decode($value));
	send_email_events("Success scheduling awstats execution for ".base64_decode($value),null,"CLOUD");
	return true;
	}
	
function ARTICA_MAKE($value){
	$APP=base64_decode($value);
	$unix=new unix();
	$tmpstr=$unix->FILE_TEMP();
	$EXEC_NICE=EXEC_NICE();
	$t1=time();
	$cmd="$EXEC_NICE/usr/share/artica-postfix/bin/artica-make $APP >$tmpstr 2>&1";
	events("running $cmd",__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);
	$t2=time();
	$time_duration=distanceOfTimeInWords($t1,$t2);
	events("success executing {{$APP}} installation ",__FUNCTION__,__FILE__,__LINE__);
	send_email_events("success executing {{$APP}} installation ($time_duration)",@file_get_contents($tmpstr),"CLOUD");
	@unlink($tmpstr);
	return true;
	}
	
function NETWORK_MASTER_CARD($value){
	$EXEC_NICE=EXEC_NICE();
	if(is_file("/usr/bin/nohup")){$nohup="/usr/bin/nohup ";}		
	$reload_datas=$nohup.$EXEC_NICE.LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.artica.meta.php --status --force >/dev/null 2>&1 &";
	$array=unserialize(base64_decode($value));
	if(!is_array($array)){
		events("Failed, value is not an array",__FUNCTION__,__FILE__,__LINE__);
		shell_exec($reload_datas);
		return true;
	}
	
	if($array["NIC"]==null){
		shell_exec($reload_datas);
		return true;
	}
	
	if($array["BOOTPROTO"]=="static"){$dhcp="no";}else{$dhcp="yes";}
	$dns="{$array["DNS_1"]}\n{$array["DNS_2"]}";	
	$cmd="{$array["NIC"]} {$array["IPADDR"]} {$array["NETMASK"]} {$array["GATEWAY"]} $dhcp {$array["BROADCAST"]}";
	@file_put_contents("/etc/artica-postfix/resolv.{$array["NIC"]}.tmp",$dns);
	events("Execute /usr/share/artica-postfix/bin/artica-install --reconfigure-nic $cmd --verbose",__FUNCTION__,__FILE__,__LINE__);	
	exec("/usr/share/artica-postfix/bin/artica-install --reconfigure-nic $cmd --verbose",$results);
	events("success change ip address {$array["NIC"]}: {$array["IPADDR"]}/{$array["NETMASK"]}",__FUNCTION__,__FILE__,__LINE__);	
	send_email_events("success change ip address {$array["NIC"]}: {$array["IPADDR"]}/{$array["NETMASK"]} from Artica Meta",@implode("\n",$results),"system");
	shell_exec($reload_datas);
	return true;
	
	
}


function NETWORK_MASTER_CARD_ROUTES($value){
	$EXEC_NICE=EXEC_NICE();
	if(is_file("/usr/bin/nohup")){$nohup="/usr/bin/nohup ";}		
	$reload_datas=$nohup.$EXEC_NICE.LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.artica.meta.php --status --force >/dev/null 2>&1 &";
	$array=unserialize(base64_decode($value));
	if(!is_array($array)){
		events("Failed, value is not an array",__FUNCTION__,__FILE__,__LINE__);
		shell_exec($reload_datas);
		return true;
	}
	
	if($array["NIC"]==null){
		shell_exec($reload_datas);
		return true;
	}

	$unix=new unix();
	$unix->NETWORK_ADD_ROUTE($array["NIC"],$array["ROUTES"]);
	if(is_file("/etc/init.d/networking")){shell_exec("/etc/init.d/networking force-reload");}
	if(is_file("/etc/init.d/network")){shell_exec("/etc/init.d/network restart");}
	send_email_events("success reconfigure network interfaces for routes from Artica Meta",null,"system");
	shell_exec($reload_datas);
	return true;
	}
	
function NMAP_SCAN($value){
	$value=base64_decode($value);
	$EXEC_NICE=EXEC_NICE();
	$unix=new unix();
	$tmpstr=$unix->FILE_TEMP();	
	@unlink("/usr/share/artica-postfix/ressources/logs/nmap.log");
	$cmd=$EXEC_NICE.LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.scan-networks.php >$tmpstr 2>&1";
	events("running $cmd",__FUNCTION__,__FILE__,__LINE__);
	$t1=time();
	shell_exec($cmd);
	$t2=time();
	$time_duration=distanceOfTimeInWords($t1,$t2);
	events("success executing exec.scan-networks.php ",__FUNCTION__,__FILE__,__LINE__);	
	send_email_events("success executing network scanning ($time_duration)",@file_get_contents("/usr/share/artica-postfix/ressources/logs/nmap.log"),"CLOUD");
	@unlink($tmpstr);
	return true;
}

function EXEC_FRAMEWORK($value){
	$sock=new sockets();
	events("-> getFrameWork($value)",__FUNCTION__,__FILE__,__LINE__);
	$sock->getFrameWork($value);
	return true;
}

function VBOX_CMD($OPERATION,$value){
	$value=base64_decode($value);
	$sock=new sockets();
	
	if($OPERATION=="RESTART_VBOX"){
		$results="------------------- STOPPING ------------------\n".$sock->getFrameWork("cmd.php?virtualbox-stop=$value");
		$results=$results."\n------------------- STARTING ------------------\n".$sock->getFrameWork("cmd.php?virtualbox-start=$value");
		events("success executing restart virtual machine $value",__FUNCTION__,__FILE__,__LINE__);
		send_email_events("success executing restart virtual machine $value",$results,"CLOUD");
		return true;
	}
	
	if($OPERATION=="START_VBOX"){
		$results."\n".$sock->getFrameWork("cmd.php?virtualbox-start=$value");
		events("success executing start virtual machine $value",__FUNCTION__,__FILE__,__LINE__);
		send_email_events("success executing start virtual machine $value",$results,"CLOUD");
		return true;
	}	
	
	if($OPERATION=="STOP_VBOX"){
		$results."\n".$sock->getFrameWork("cmd.php?virtualbox-stop=$value");
		events("success executing stop virtual machine $value",__FUNCTION__,__FILE__,__LINE__);
		send_email_events("success executing stop virtual machine $value",$results,"CLOUD");
		return true;
	}		
	return true;	
}

function DNS_DEL_ENTRY($value){
	$hostname=base64_decode($value);
	$EXEC_NICE=EXEC_NICE();
	if(is_file("/usr/bin/nohup")){$nohup="/usr/bin/nohup ";}	
	$reload_datas=$nohup.$EXEC_NICE.LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.artica.meta.users.php --export-all-dns >/dev/null 2>&1 &";
	include_once(dirname(__FILE__) . "/ressources/class.pdns.inc");	
	$ldap=new clladp();
	$upd=array();
	
	$tbl=explode(".",$hostname);
	$dc="dc=".@implode(",dc=",$tbl);
	
	if($ldap->ExistsDN("$dc,ou=dns,$ldap->suffix")){
		if($ldap->ldap_delete("$dc,ou=dns,$ldap->suffix",true)){
			send_email_events("Success deleting DNS entry $hostname","DN removed was : $dc,ou=dns,$ldap->suffix","CLOUD");
			return true;
		}
	}else{
		events("unable to stat $dc,ou=dns,$ldap->suffix",__FUNCTION__,__FILE__,__LINE__);
	}
	
	$suffix="ou=dns,$ldap->suffix";
	$pattern="(&(objectclass=*)(associatedDomain=$hostname))";
	$sr = @ldap_search($ldap->ldap_connection,$suffix,"$pattern",array());	
	if($sr){
		$hash=ldap_get_entries($ldap->ldap_connection,$sr);
		for($i=0;$i<$hash["count"];$i++){
			$dn=$hash[$i]["dn"];
			if(strlen($dn)>0){
				$dns[]=$dn;
				events("removing  associateddomain=$hostname in $dn",__FUNCTION__,__FILE__,__LINE__);
				$upd["associateddomain"]=$hostname;
				if(!$ldap->Ldap_del_mod($dn,$upd)){$dns[]=$ldap->ldap_last_error;}				
			}
		}
	  send_email_events("Success executing remove DNS entry $hostname",@implode("\n",$dns),"CLOUD");
	  return true;
	}
	
	events("Failed -> notify",__FUNCTION__,__FILE__,__LINE__);
	send_email_events("Failed remove DNS entry $hostname does not exists",null,"CLOUD");
	shell_exec($reload_datas);
	return true;	
}



function casttoclass($class, $object){
  return unserialize(preg_replace('/^O:\d+:"[^"]++"/', 'O:' . strlen($class) . ':"' . $class . '"', serialize($object)));
}


function events($text,$function,$fileMe=null,$line=0){
		if($function==null){$function="MAIN";}
		$pid=@getmypid();
		if($fileMe==null){$fileMe=__FILE__;}
		$date=@date("H:i:s");
		$logFile="/var/log/artica-postfix/artica-meta-agent.log";
		$size=@filesize($logFile);
		if($size>100000){@unlink($logFile);}
		$f = @fopen($logFile, 'a');
		$fileMe=basename($fileMe);
		$text="[exec.artica.meta.tasks.php][$pid] $date $function:: $text (L.$line)\n";
		if($GLOBALS["VERBOSE"]){echo $text;}
		@fwrite($f, $text);
		@fclose($f);	
	}	

	
?>
