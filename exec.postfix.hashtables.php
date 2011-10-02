<?php
$GLOBALS["EnablePostfixMultiInstance"]=0;
include_once(dirname(__FILE__) . '/ressources/class.ldap.inc');
include_once(dirname(__FILE__) . '/ressources/class.user.inc');
include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
include_once(dirname(__FILE__) . '/ressources/class.maincf.multi.inc');
include_once(dirname(__FILE__) . '/ressources/class.main_cf.inc');
include_once(dirname(__FILE__) . '/ressources/class.main.hashtables.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["DEBUG"]=true;$GLOBALS["VERBOSE"]=true;}
if(preg_match("#--reload#",implode(" ",$argv))){$GLOBALS["RELOAD"]=true;}

$sock=new sockets();
$unix=new unix();
$GLOBALS["EnablePostfixMultiInstance"]=$sock->GET_INFO("EnablePostfixMultiInstance");
if(!is_numeric($GLOBALS["EnablePostfixMultiInstance"])){$GLOBALS["EnablePostfixMultiInstance"]=0;}
$GLOBALS["EnableBlockUsersTroughInternet"]=$sock->GET_INFO("EnableBlockUsersTroughInternet");
$GLOBALS["postconf"]=$unix->find_program("postconf");
$GLOBALS["postmap"]=$unix->find_program("postmap");
$GLOBALS["newaliases"]=$unix->find_program("newaliases");
$GLOBALS["postalias"]=$unix->find_program("postalias");
$GLOBALS["postfix"]=$unix->find_program("postfix");
$GLOBALS["newaliases"]=$unix->find_program("newaliases");
$GLOBALS["virtual_alias_maps"]=array();
$GLOBALS["alias_maps"]=array();
$GLOBALS["relay_domains"]=array();
$GLOBALS["bcc_maps"]=array();
$GLOBALS["transport_maps"]=array();
$GLOBALS["smtp_generic_maps"]=array();
$GLOBALS["PHP5_BIN"]=$unix->LOCATE_PHP5_BIN();

if(!is_file($GLOBALS["postfix"])){die();}

$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".pid";
$pid=$unix->get_pid_from_file($pidfile);
if($unix->process_exists($pid,basename(__FILE__))){
	echo "Starting......: Already executed pid:$pid\n";
	$unix->send_email_events("Postfix user databases aborted (instance executed)", "Already instance pid $pid is executed", "postfix");
	die();
}

@file_put_contents($pidfile, getmypid());

$ldap=new clladp();
if($ldap->ldapFailed){
	echo "Starting......: failed connecting to ldap server $ldap->ldap_host\n";
	$unix->send_email_events("Postfix user databases aborted (ldap failed)", "The process has been scheduled to start in few seconds.", "postfix"); 
	$unix->THREAD_COMMAND_SET(trim($unix->LOCATE_PHP5_BIN()." ".__FILE__. " {$argv[1]}"));
	die();
}


if($GLOBALS["EnablePostfixMultiInstance"]==1){if($argv[1]=="--aliases"){system(LOCATE_PHP5_BIN2()." ". dirname(__FILE__)."/exec.postfix-multi.php --aliases");die();}system(LOCATE_PHP5_BIN2()." ". dirname(__FILE__)."/exec.postfix-multi.php");die();}
if($argv[1]=="--postmaster"){postmaster();die();}	


if($argv[1]=="--relayhost"){
	relayhost();
	perso_settings();
	shell_exec("{$GLOBALS["postfix"]} reload >/dev/null 2>&1");
	die();
}


if($argv[1]=="--bcc"){
	recipient_bcc_maps();
	recipient_bcc_domain_maps();
	recipient_bcc_maps_build();
	sender_bcc_maps();
	sender_bcc_maps_build();
	perso_settings();
	shell_exec("{$GLOBALS["postfix"]} reload >/dev/null 2>&1");
	die();
}

if($argv[1]=="--recipient-canonical"){
	recipient_canonical_maps_build();
	recipient_canonical_maps();
	perso_settings();
	shell_exec("{$GLOBALS["postfix"]} reload >/dev/null 2>&1");
	die();	
}

if($argv[1]=="--restricted-relais"){
	restrict_relay_domains();
	die();
}


if($argv[1]=="--transport"){
	LoadLDAPDBs();
	transport_maps_search();
	relais_domains_search();
	build_transport_maps();
	build_relay_domains();
	restrict_relay_domains();
	build_cyrus_lmtp_auth();	
	$hashT=new main_hash_table();
	$hashT->mydestination();	
	relayhost();
	perso_settings();
	echo "Starting......: Postfix reloading\n";
	shell_exec("{$GLOBALS["postfix"]} reload >/dev/null 2>&1");
	die();}
	
if($argv[1]=="--aliases"){
	LoadLDAPDBs();
	maillings_table();
	aliases_users();
	aliases();
	catch_all();
	build_aliases_maps();
	build_virtual_alias_maps();
	postmaster();
	perso_settings();
	echo "Starting......: Postfix reloading\n";
	shell_exec("{$GLOBALS["postfix"]} reload >/dev/null 2>&1");
	die();}
		
if($argv[1]=="--smtp-passwords"){
	sender_canonical_maps_build();
	sender_canonical_maps();
	smtp_generic_maps_build_global();
	smtp_generic_maps();
	sender_dependent_relayhost_maps();
	smtp_sasl_password_maps_build();
	smtp_sasl_password_maps();
	perso_settings();
	echo "Starting......: Postfix reloading\n";
	shell_exec("{$GLOBALS["postfix"]} reload >/dev/null 2>&1");
	die();}	
	
if($argv[1]=="--smtp-generic-maps"){
	sender_canonical_maps_build();
	smtp_generic_maps_build_global();
	smtp_generic_maps();
	perso_settings();
	echo "Starting......: Postfix reloading\n";
	shell_exec("{$GLOBALS["postfix"]} reload >/dev/null 2>&1");
	die();}		

LoadLDAPDBs();
maillings_table();
aliases_users();
aliases();
catch_all();


build_aliases_maps();
build_virtual_alias_maps();

relais_domains_search();
build_relay_domains();

relay_recipient_maps_build();

recipient_canonical_maps_build();
recipient_canonical_maps();

sender_canonical_maps_build();
sender_canonical_maps();

smtp_generic_maps_build_global();
smtp_generic_maps();


sender_dependent_relayhost_maps();
smtp_sasl_password_maps_build();
smtp_sasl_password_maps();

recipient_bcc_maps();
recipient_bcc_domain_maps();
recipient_bcc_maps_build();

sender_bcc_maps();
sender_bcc_maps_build();

build_local_recipient_maps();


$hashT=new main_hash_table();
$hashT->mydestination();

transport_maps_search();
build_transport_maps();
restrict_relay_domains();

relayhost();
postmaster();
build_cyrus_lmtp_auth();
perso_settings();

shell_exec("{$GLOBALS["postfix"]} reload >/dev/null 2>&1");

function perso_settings(){
	$main=new main_perso();
	$main->replace_conf("/etc/postfix/main.cf");
}


function recipient_bcc_maps(){
	
$ldap=new clladp();
	$filter="(&(objectClass=UserArticaClass)(RecipientToAdd=*))";
	$attrs=array("RecipientToAdd","mail");
	$dn="dc=organizations,$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);
	
	for($i=0;$i<$hash["count"];$i++){
		$mail=$hash[$i]["mail"][0];
		$RecipientToAdd=$hash[$i]["recipienttoadd"][0];
		$GLOBALS["bcc_maps"][]="$mail\t$RecipientToAdd";
		
	}	
	echo "Starting......: Postfix ". count($GLOBALS["bcc_maps"])." recipient(s) BCC\n"; 	
}
function sender_bcc_maps(){
$ldap=new clladp();
	$filter="(&(objectClass=UserArticaClass)(SenderBccMaps=*))";
	$attrs=array("SenderBccMaps","mail");
	$dn="dc=organizations,$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);
	
	for($i=0;$i<$hash["count"];$i++){
		$mail=$hash[$i]["mail"][0];
		$senderbccmaps=$hash[$i]["senderbccmaps"][0];
		$GLOBALS["sender_bcc_maps"][]="$mail\t$senderbccmaps";
		
	}	
	echo "Starting......: Postfix ". count($GLOBALS["sender_bcc_maps"])." Sender(s) BCC\n"; 	
}
function sender_bcc_maps_build(){
if(!is_array($GLOBALS["sender_bcc_maps"])){
		shell_exec("{$GLOBALS["sender_bcc_maps"]} -e \"sender_bcc_maps = \" >/dev/null 2>&1");
		return null;
		}
	
		shell_exec("{$GLOBALS["postconf"]} -e \"sender_bcc_maps =hash:/etc/postfix/sender_bcc\" >/dev/null 2>&1");
		echo "Starting......: Compiling Sender(s) BCC\n"; 
		@file_put_contents("/etc/postfix/sender_bcc",implode("\n",$GLOBALS["sender_bcc_maps"]));
		shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix/sender_bcc >/dev/null 2>&1");	
}


function recipient_bcc_domain_maps(){
	$sql="SELECT * FROM postfix_duplicate_maps";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	$c=0;
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($ligne["pattern"]==null){continue;}	
		
		$left="(.*)";
		$right='${1}';
		$leftNext="(.*)";
		$rightNext='${1}';
		$domain=$ligne["pattern"];
		$nextdomain=$ligne["nextdomain"];
		$nextdomain_transport=$ligne["nextdomain"];

		
		
		if(preg_match("#(.+?)@(.+)#",$ligne["pattern"],$re)){
			$nextHope_pattern=$ligne["pattern"];
			$domain=$re[2];
			$left=$re[1];
			$right=$re[1];
			$rightNext=$right;
			$left=str_replace(".","\.",$left);
			$right=str_replace(".","\.",$right);
			$leftNext=$left;
		}
		
		if(preg_match("#(.+?)@(.+)#",$ligne["nextdomain"],$re)){
			$right=$re[1];
			$nextdomain=$re[2];
			
		}		
		
		$md5=md5($domain);
		$domain_regex=str_replace(".","\.",$domain);
		$f[]="/^$left@$domain_regex$/   $right@$nextdomain";
		$t[]="$nextdomain_transport\tsmtp:[{$ligne["relay"]}]:{$ligne["port"]}";
		$c++;
	}
	echo "Starting......: ".count($f)." duplicated destination(s)\n"; 
	$f[]="";
	@file_put_contents("/etc/postfix/copy.pcre",implode("\n",$f));
	@file_put_contents("/etc/postfix/copy.transport",implode("\n",$t));
	shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix/copy.transport >/dev/null 2>&1");	
}
function recipient_bcc_maps_build(){
if(!is_array($GLOBALS["bcc_maps"])){
		shell_exec("{$GLOBALS["postconf"]} -e \"recipient_bcc_maps = pcre:/etc/postfix/copy.pcre\" >/dev/null 2>&1");
		return null;
		}
	
		shell_exec("{$GLOBALS["postconf"]} -e \"recipient_bcc_maps =hash:/etc/postfix/recipient_bcc,pcre:/etc/postfix/copy.pcre\" >/dev/null 2>&1");
		echo "Starting......: Compiling Recipient(s) BCC\n";
		@file_put_contents("/etc/postfix/recipient_bcc",implode("\n",$GLOBALS["bcc_maps"]));
		shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix/recipient_bcc >/dev/null 2>&1");	
}




function maillings_table(){
	$ldap=new clladp();
	$filter="(&(objectClass=MailingAliasesTable)(cn=*))";
	$attrs=array("cn","MailingListAddress","MailingListAddressGroup");
	$dn="dc=organizations,$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);
	
	for($i=0;$i<$hash["count"];$i++){
		$cn=$hash[$i]["cn"][0];
		$MailingListAddressGroup=$hash[$i]["mailinglistaddressgroup"][0];
		for($t=0;$t<$hash[$i]["mailinglistaddress"]["count"];$t++){
			$hash[$i]["mailinglistaddress"][$t]=trim($hash[$i]["mailinglistaddress"][$t]);
			if($hash[$i]["mailinglistaddress"][$t]==null){continue;}
			$mailinglistaddress[$hash[$i]["mailinglistaddress"][$t]]=$hash[$i]["mailinglistaddress"][$t];
		}
		
		if($MailingListAddressGroup==1){
			$uid=$ldap->uid_from_email($cn);
			$user=new user($uid);
			$array=$user->MailingGroupsLoadAliases();
			while (list ($num, $ligne) = each ($array) ){
    			if(trim($ligne)==null){continue;}  	
    			$mailinglistaddress[$ligne]=$ligne;
    		}	
		}
		
		if(is_array($mailinglistaddress)){
				while (list ($num, $ligne) = each ($mailinglistaddress) ){
					$final[]=$num;
				}
				if($GLOBALS["DEBUG"]){echo "DEBUG: maillings_table(): $cn = $cn\t". implode(",",$final)."\n";}
				$GLOBALS["virtual_alias_maps_emailing"][$cn]="$cn\t". implode(",",$final);
			}	
			
		unset($final);
		unset($mailinglistaddress);
		$MailingListAddressGroup=0;
	}
	
	

	
	$filter="(&(objectClass=ArticaMailManRobots)(cn=*))";
	$attrs=array("cn","MailManAliasPath");
	$dn="dc=organizations,$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);
	$sock=new sockets();
	if($sock->GET_INFO("MailManEnabled")==1){$GLOBALS["MAILMAN"]=true;}else{
		$GLOBALS["MAILMAN"]=false;
		return;
	}
	
	if($hash["count"]>0){$GLOBALS["MAILMAN"]=true;}else{$GLOBALS["MAILMAN"]=false;}
	

}


function catch_all(){
	$ldap=new clladp();
	$filter="(&(objectClass=AdditionalPostfixMaps)(cn=*))";
	$attrs=array("cn","CatchAllPostfixAddr");
	$dn="cn=catch-all,cn=artica,$ldap->suffix";
	
	if($GLOBALS["DEBUG"]){echo __FUNCTION__." -> open branch $dn $filter\n";}
	
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);
	if($GLOBALS["DEBUG"]){echo __FUNCTION__." -> found {$hash["count"]} entries\n";}
	for($i=0;$i<$hash["count"];$i++){
		$cn=$hash[$i]["cn"][0];
		for($t=0;$t<$hash[$i][strtolower("CatchAllPostfixAddr")]["count"];$t++){
			echo "Starting......: catch-all {$hash[$i][strtolower("CatchAllPostfixAddr")][$t]} for $cn\n";
			if(substr($cn,0,1)<>"@"){$cn=trim("@$cn");}
			if($GLOBALS["DEBUG"]){echo __FUNCTION__." -> virtual_alias_maps=$cn\t{$hash[$i][strtolower("CatchAllPostfixAddr")][$t]}\n";}
			$GLOBALS["virtual_alias_maps"][$cn]="$cn\t{$hash[$i][strtolower("CatchAllPostfixAddr")][$t]}";
		}
	}
}

function relais_domains_search(){
$ldap=new clladp();
	$filter="(&(objectClass=PostFixRelayDomains)(cn=*))";
	$attrs=array("cn");
	$dn="$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);
	for($i=0;$i<$hash["count"];$i++){
		$GLOBALS["relay_domains"][]=$hash[$i]["cn"][0]."\tOK";
		
	}


	
	
	echo "Starting......: Postfix ". count($GLOBALS["relay_domains"])." relay domain(s)\n";
}
	
function build_relay_domains(){
	if(!is_array($GLOBALS["relay_domains"])){
		shell_exec("{$GLOBALS["postconf"]} -e \"relay_domains = \" >/dev/null 2>&1");
		return null;
		}

	shell_exec("{$GLOBALS["postconf"]} -e \"relay_domains =hash:/etc/postfix/relay_domains\" >/dev/null 2>&1");
	@file_put_contents("/etc/postfix/relay_domains",implode("\n",$GLOBALS["relay_domains"]));
	shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix/relay_domains >/dev/null 2>&1");	
		
}



function LoadLDAPDBs(){
	$main=new maincf_multi("master","master");
	$databases_list=unserialize(base64_decode($main->GET_BIGDATA("ActiveDirectoryDBS")));	
	if(is_array($databases_list)){
		while (list ($dbindex, $array) = each ($databases_list) ){
			if($GLOBALS["DEBUG"]){echo __FUNCTION__."::LDAP:: {$array["database_type"]}; enabled={$array["enabled"]}\n";}
			if($array["enabled"]<>1){
				if($GLOBALS["DEBUG"]){echo __FUNCTION__."::LDAP:: {$array["database_type"]} is not enabled, skipping\n";}
				continue;
			}
			$targeted_file=$main->buidLdapDB("master",$dbindex,$array);
			if(!is_file($targeted_file)){
				if($GLOBALS["DEBUG"]){echo __FUNCTION__."::LDAP:: {$array["database_type"]} \"$targeted_file\" no such file, skipping\n";}
				continue;
			}
			
			
//$GLOBALS["REMOTE_SMTP_LDAPDB_ROUTING"]	

			if($array["resolv_domains"]==1){$domains=$main->buidLdapDBDomains($array);}
			
			$GLOBALS["LDAPDBS"][$array["database_type"]][]="ldap:$targeted_file";
			if($GLOBALS["DEBUG"]){echo __FUNCTION__."::LDAP:: GLOBALS[LDAPDBS][{$array["database_type"]}]=ldap:$targeted_file\n";}
		}	
	}
}





function aliases_users(){
	$ldap=new clladp();
	$users=new usersMenus();
	$filter="(&(objectClass=userAccount)(uid=*))";
	$attrs=array("uid","mail");
	$trap_uid="uid";
	$dn="dc=organizations,$ldap->suffix";
	
	if($ldap->EnableManageUsersTroughActiveDirectory){
		$ldapAD=new ldapAD();
		$filter="(&(objectClass=user)(samaccountname=*))";
		$attrs=array("samaccountname","mail");
		$trap_uid="samaccountname";
		$dn="$ldapAD->suffix";
		$hash=$ldapAD->Ldap_search($dn,$filter,$attrs);
	}else{
		$hash=$ldap->Ldap_search($dn,$filter,$attrs);
	}

	for($i=0;$i<$hash["count"];$i++){
		$uid=trim($hash[$i][$trap_uid][0]);
		if(strpos($uid,"$")>0){continue;}
		
		if($uid==null){continue;}
		for($t=0;$t<$hash[$i]["mail"]["count"];$t++){
			$mail=trim($hash[$i]["mail"][$t]);
			if($mail==null){continue;}
			
			
			
			if(!$GLOBALS["virtual_alias_maps_mem"][$mail]){
				if($GLOBALS["virtual_alias_maps_emailing"][$mail]==null){
					$GLOBALS["virtual_alias_maps"][$mail]="$mail\t$mail";
				}
			}
			$GLOBALS["virtual_alias_maps_mem"][$mail]=true;
			
			if(!$GLOBALS["alias_maps_mem"][$uid]){
				if(!preg_match("#.+?@#",$uid)){$GLOBALS["alias_maps"][]="$uid:$mail";}
				$GLOBALS["alias_maps_mem"][$uid]=true;	
			}
			
			$GLOBALS["virtual_mailbox"]="$mail\t$uid";
		}
	}

	$filter="(&(objectClass=transportTable)(cn=*@*))";
	$attrs=array("cn");
	$dn="cn=PostfixRobots,cn=artica,$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);
	for($i=0;$i<$hash["count"];$i++){
		$cn=$hash[$i]["cn"][0];
		if(preg_match("#(.+?)@#",$cn,$re)){
			$map=$re[1];
		if(!$GLOBALS["alias_maps_mem"][$map]){
			$GLOBALS["alias_maps"][]="$map:$cn";
			$GLOBALS["alias_maps_mem"][$map]=true;
			}
		}
	}
	
	$sock=new sockets();
	$PostfixPostmaster=trim($sock->GET_INFO("PostfixPostmaster"));
	if($PostfixPostmaster==null){return;}
	
	$myhostname=trim($sock->GET_INFO("myhostname"));
	if($myhostname==null){$myhostname=$users->hostname;}
	preg_match("#(.+?)@#",$PostfixPostmaster,$re);
	$PostfixPostmaster_prefix=$re[1];	
	
	
	$GLOBALS["virtual_alias_maps"]["$PostfixPostmaster_prefix@$myhostname"]="$PostfixPostmaster_prefix@$myhostname\t$PostfixPostmaster";
	$GLOBALS["virtual_alias_maps"][$PostfixPostmaster]="$PostfixPostmaster\t$PostfixPostmaster";
	$GLOBALS["virtual_alias_maps"]["root@$myhostname"]="root@$myhostname\t$PostfixPostmaster";
	$GLOBALS["virtual_alias_maps"]["postmaster"]="postmaster\t$PostfixPostmaster";
	$GLOBALS["virtual_alias_maps"]["MAILER-DAEMON"]="MAILER-DAEMON\t$PostfixPostmaster";
	$GLOBALS["virtual_alias_maps"]["root"]="root\t$PostfixPostmaster";
	
	/*$sql="SELECT `email` FROM postfix_relais_domains_users";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "$q->mysql_error\n";}	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
		$GLOBALS["virtual_alias_maps"][$ligne["email"]]="{$ligne["email"]}\t{$ligne["email"]}";
	}*/		
	
	
	$GLOBALS["alias_maps"][]="postmaster:$PostfixPostmaster";
	$GLOBALS["alias_maps"][]="MAILER-DAEMON:$PostfixPostmaster";
	$GLOBALS["alias_maps"][]="root:$PostfixPostmaster";
	if($PostfixPostmaster_prefix<>null){
		if(!$GLOBALS["alias_maps_mem"][$PostfixPostmaster_prefix]){$GLOBALS["alias_maps"][]="$PostfixPostmaster_prefix:$PostfixPostmaster";}
	}
	
	
	
	
}


function build_local_recipient_maps(){
if(!is_array($GLOBALS["local_recipient_maps"])){
		shell_exec("{$GLOBALS["postconf"]} -e \"local_recipient_maps = \" >/dev/null 2>&1");
		echo "Starting......: No recipients maps\n"; 
		return null;
		}	

echo "Starting......: Postfix ". count($GLOBALS["local_recipient_maps"])." local recipient(s)\n"; 
shell_exec("{$GLOBALS["postconf"]} -e \"local_recipient_maps =hash:/etc/postfix/local_recipients\" >/dev/null 2>&1");
file_put_contents("/etc/postfix/local_recipients",implode("\n",$GLOBALS["local_recipient_maps"]));
shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix/local_recipients >/dev/null 2>&1");		
	
}

function build_virtual_alias_maps(){
	
if($GLOBALS["DEBUG"]){echo __FUNCTION__." -> virtual_alias_maps=". count($GLOBALS["virtual_alias_maps"]) . " entries\n";}

	if(is_array($GLOBALS["virtual_alias_maps_emailing"])){	
			echo "Starting......: Postfix ". count($GLOBALS["virtual_alias_maps_emailing"])." distribution listes\n";	
			while (list ($num, $ligne) = each ($GLOBALS["virtual_alias_maps_emailing"]) ){
				$final[]=$ligne;
			}
		}	

	if(is_array($GLOBALS["virtual_alias_maps"])){
			echo "Starting......: Cleaning virtual aliase(s)\n"; 
			while (list ($num, $ligne) = each ($GLOBALS["virtual_alias_maps"]) ){
			if(preg_match("#x500:#",$ligne)){continue;}
			if(preg_match("#x400:#",$ligne)){continue;}
			$final[]=$ligne;
		}
	}
		
	if(!is_array($GLOBALS["LDAPDBS"]["virtual_alias_maps"])){
		$virtual_alias_maps_cf=$GLOBALS["LDAPDBS"]["virtual_alias_maps"];
	}
		
		$sql="SELECT * FROM postfix_aliases_domains";
		$q=new mysql();
		$pre='${1}';
		$li=array();
		$results=$q->QUERY_SQL($sql,"artica_backup");	
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
			$aliases=str_replace(".","\.",$ligne["alias"]);
			$domain=$ligne["domain"];
			$li[]="/^(.*)@$aliases$/\t$pre@$domain";
			$final[]="{$ligne["alias"]}\tDOMAIN";
		}	
	
	
		echo "Starting......: Postfix ". count($final)." virtual aliase(s)\n"; 	
		echo "Starting......: Postfix ". count($li)." virtual domain(s) aliases\n"; 	
		$virtual_alias_maps_cf[]="hash:/etc/postfix/virtual";
		$virtual_alias_maps_cf[]="pcre:/etc/postfix/virtual.domains";
		
		if($GLOBALS["DEBUG"]){echo __FUNCTION__." -> writing /etc/postfix/virtual\n";}			
		@file_put_contents("/etc/postfix/virtual",implode("\n",$final));
		@file_put_contents("/etc/postfix/virtual.domains",implode("\n",$li));
		
		echo "Starting......: Postfix compiling virtual aliase database /etc/postfix/virtual\n"; 
		if($GLOBALS["DEBUG"]){echo __FUNCTION__." -> {$GLOBALS["postmap"]} hash:/etc/postfix/virtual >/dev/null 2>&1\n";}	
		shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix/virtual >/dev/null 2>&1");
	

	
	if(!is_array($virtual_alias_maps_cf)){
		if($GLOBALS["DEBUG"]){echo __FUNCTION__." -> {$GLOBALS["postconf"]} -e \"virtual_alias_maps = \" >/dev/null 2>&1\n";}
		shell_exec("{$GLOBALS["postconf"]} -e \"virtual_alias_maps = \" >/dev/null 2>&1");
		echo "Starting......: Postfix No virtual aliases\n";
		return;
	}else{
		echo "Starting......: Postfix building virtual_alias_maps\n";
		shell_exec("{$GLOBALS["postconf"]} -e \"virtual_alias_maps = ". @implode(",",$virtual_alias_maps_cf)."\" >/dev/null 2>&1");
	}		
	
}


function build_aliases_maps(){
	
	if(is_array($GLOBALS["LDAPDBS"]["alias_maps"])){
		if($GLOBALS["VERBOSE"]){"LDAP:: alias_maps = \"".@implode(",",$GLOBALS["LDAPDBS"]["alias_maps"])."\n";}
		$alias_maps_cf=$GLOBALS["LDAPDBS"]["alias_maps"];
	}else{
		if($GLOBALS["DEBUG"]){echo __FUNCTION__."::LDAP:: GLOBALS[LDAPDBS][alias_maps]=not an array\n";}
	}
	
	if(is_array($GLOBALS["LDAPDBS"]["alias_database"])){
		$alias_database_cf=$GLOBALS["LDAPDBS"]["alias_database"];
	}

	if(is_array($GLOBALS["LDAPDBS"]["virtual_mailbox_maps"])){
		$virtual_mailbox_maps_cf=$GLOBALS["LDAPDBS"]["virtual_mailbox_maps"];
	}	
	
if(is_array($GLOBALS["alias_maps"])){
	if($GLOBALS["MAILMAN"]=true){
			echo "Starting......: Building mailman aliase(s)\n";
			if(mailman_aliases()){
				$hash_mailman=",hash:{$GLOBALS["MAILMAN_ALIASES"]}";
				$hash_mailman_virtual=",hash:/var/lib/mailman/data/virtual-mailman";
				}
		}
		echo "Starting......: Postfix ". count($GLOBALS["alias_maps"])." aliase(s)\n"; 
		$alias_maps_cf[]="hash:/etc/postfix/aliases$hash_mailman";
		$alias_database_cf[]="hash:/etc/postfix/aliases";
		
		
		
		@file_put_contents("/etc/postfix/aliases",implode("\n",$GLOBALS["alias_maps"]));
		shell_exec("{$GLOBALS["postalias"]} -c /etc/postfix hash:/etc/postfix/aliases >/dev/null 2>&1");
		shell_exec("{$GLOBALS["newaliases"]}");		
		
	
	}
	
	if(is_array($alias_maps_cf)){
		echo "Starting......: Postfix building alias_maps\n";
		shell_exec("{$GLOBALS["postconf"]} -e \"alias_maps =". @implode(",",$alias_maps_cf)."\" >/dev/null 2>&1");
	}else{
		if($GLOBALS["VERBOSE"]){__FUNCTION__.":: alias_maps = \$alias_maps_cf is not an array\n ( line:".__LINE__.")";}
		shell_exec("{$GLOBALS["postconf"]} -e \"aliases_maps = \" >/dev/null 2>&1");
	}
	
	if(is_array($alias_database_cf)){
		echo "Starting......: Postfix building alias_database\n";
		shell_exec("{$GLOBALS["postconf"]} -e \"alias_database =". @implode(",",$alias_database_cf)."\" >/dev/null 2>&1");
	}else{
		shell_exec("{$GLOBALS["postconf"]} -e \"alias_database = \" >/dev/null 2>&1");
	}

	if(is_array($virtual_mailbox_maps_cf)){	
		echo "Starting......: Postfix building virtual_mailbox_maps\n";
		shell_exec("{$GLOBALS["postconf"]} -e \"virtual_mailbox_maps =". @implode(",",$virtual_mailbox_maps_cf)."\" >/dev/null 2>&1");
	}else{
		shell_exec("{$GLOBALS["postconf"]} -e \"virtual_mailbox_maps = \" >/dev/null 2>&1");
	}

	
}

function mailman_aliases(){
	if(!is_file("/var/lib/mailman/bin/genaliases")){
		echo "Starting......: Unable to locate genaliases tool\n";
		return false;
	}
	
	shell_exec("/var/lib/mailman/bin/genaliases");
	
	if(is_file("/var/lib/mailman/data/aliases")){
		$GLOBALS["MAILMAN_ALIASES"]="/var/lib/mailman/data/aliases";
		
	
	if(is_file("/var/lib/mailman/data/virtual-mailman")){
		$GLOBALS["MAILMAN_VIRTUAL"]="/var/lib/mailman/data/virtual-mailman";
		return true;
	}
	}
}


function aliases(){
	$ldap=new clladp();
	if($ldap->EnableManageUsersTroughActiveDirectory){
		aliases_ad();
		return;
	}
	$filter="(&(objectClass=userAccount)(mailAlias=*))";
	$attrs=array("mail","mailAlias");
	$dn="dc=organizations,$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);

	for($i=0;$i<$hash["count"];$i++){
		$mail=trim($hash[$i]["mail"][0]);
		
		for($t=0;$t<$hash[$i]["mailalias"]["count"];$t++){
			$hash[$i]["mailalias"][$t]=trim($hash[$i]["mailalias"][$t]);
			if($hash[$i]["mailalias"][$t]==null){continue;}
			$GLOBALS["virtual_alias_maps"]["{$hash[$i]["mailalias"][$t]}"]="{$hash[$i]["mailalias"][$t]}\t$mail";
		}
	}

}

function aliases_ad(){
	$ldap=new ldapAD();
	$filter="(&(objectClass=user)(userPrincipalName=*))";
	$attrs=array("userPrincipalName","mail");
	$dn="$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);
	for($i=0;$i<$hash["count"];$i++){
		$mail=trim($hash[$i]["mail"][0]);
		$userPrincipalName=trim($hash[$i]["userprincipalname"][0]);
		$GLOBALS["virtual_alias_maps"][$userPrincipalName]="$userPrincipalName\t$mail";
		
	}
	
}



function build_transport_maps(){
	$main=new maincf_multi("master","master");
	$main->bann_destination_domains();
	if(!is_file("/etc/postfix/transport.throttle")){@file_put_contents("/etc/postfix/transport.throttle"," ");}
	
	if(!is_array($GLOBALS["transport_maps"])){
		shell_exec("{$GLOBALS["postconf"]} -e \"transport_maps = hash:/etc/postfix/transport.throttle\" >/dev/null 2>&1");
	}
	
	while (list ($num, $ligne) = each ($GLOBALS["transport_maps"]) ){
		if($ligne==null){continue;}
		$array[]="$num\t$ligne";
		
	}
	
	echo "Starting......: Postfix ". count($array)." routings rules\n"; 
	
	if(is_array($GLOBALS["transport_maps_AT"])){
	while (list ($num, $ligne) = each ($GLOBALS["transport_maps_AT"]) ){
		if($ligne==null){continue;}
		$array[]="$num\t$ligne";
		
	}}
	
	@file_put_contents("/etc/postfix/transport",implode("\n",$array));
	shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix/transport >/dev/null 2>&1");
	shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix/transport.throttle >/dev/null 2>&1");
	shell_exec("{$GLOBALS["postconf"]} -e \"transport_maps = hash:/etc/postfix/transport.throttle, hash:/etc/postfix/transport, hash:/etc/postfix/transport.banned,hash:/etc/postfix/copy.transport\" >/dev/null 2>&1");
	
}

function relay_recipient_maps_build(){
	$relay_recipient_maps=null;
	if(count($GLOBALS["LDAPDBS"]["relay_recipient_maps"])>0){
		$relay_recipient_maps=@implode(",",$GLOBALS["LDAPDBS"]["relay_recipient_maps"]);
	}
	shell_exec("{$GLOBALS["postconf"]} -e \"relay_recipient_maps = $relay_recipient_maps\" >/dev/null 2>&1");
}


function recipient_canonical_maps_build(){
	$ldap=new clladp();
	$filter="(&(objectClass=RecipientCanonicalMaps)(cn=*))";
	$attrs=array("cn","MailAlternateAddress");
	$dn="$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);

	for($i=0;$i<$hash["count"];$i++){
		$mail=$hash[$i]["cn"][0];
		$canonical=$hash[$i][strtolower("MailAlternateAddress")][0];
		$GLOBALS["recipient_canonical_maps"][]="$mail\t$canonical";
	}		
}

function smtp_sasl_password_maps_build(){
	$ldap=new clladp();
	
	$filter="(&(objectClass=PostfixSmtpSaslPaswordMaps)(cn=*))";
	$attrs=array("cn","SmtpSaslPasswordString");
	$dn="cn=smtp_sasl_password_maps,cn=artica,$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);
	for($i=0;$i<$hash["count"];$i++){
		$mail=$hash[$i]["cn"][0];
		$value=trim($hash[$i][strtolower("SmtpSaslPasswordString")][0]);
		if($value==null){
			if($GLOBALS["VERBOSE"]){echo "Starting......: skip  $mail (no password)\n";}
			continue;
		}
		if($value==":"){continue;}
		
		if($GLOBALS["VERBOSE"]){echo "Starting......: adding  $mail\n";}
		$smtp_sasl_password_maps[$mail]=$value;
	}

	$filter="(&(objectClass=SenderDependentSaslInfos)(cn=*))";
	$attrs=array("cn","SenderCanonicalRelayPassword");
	$dn="$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);
	for($i=0;$i<$hash["count"];$i++){
		$mail=$hash[$i]["cn"][0];
		$value=trim($hash[$i][strtolower("SenderCanonicalRelayPassword")][0]);
		if($value==null){
			if($GLOBALS["VERBOSE"]){echo "Starting......: skip  $mail (no password)\n";}
			continue;
		}
		if($value==":"){continue;}
		if($GLOBALS["VERBOSE"]){echo "Starting......: adding  $mail\n";}
		
		$smtp_sasl_password_maps[$mail]=$value;
	}

	if(is_array($smtp_sasl_password_maps)){
		while (list ($mail, $value) = each ($smtp_sasl_password_maps) ){
			$GLOBALS["smtp_sasl_password_maps"][]="$mail\t$value";
		}
	}
	

}

function smtp_sasl_password_maps(){
	smtp_sasl_password_maps_build();
	if(!is_array($GLOBALS["smtp_sasl_password_maps"])){
		
		
		echo "Starting......: 0 smtp password rule(s)\n"; 
		shell_exec("{$GLOBALS["postconf"]} -e \"smtp_sasl_password_maps =\" >/dev/null 2>&1");
		shell_exec("{$GLOBALS["postconf"]} -e \"smtp_sasl_auth_enable =no\" >/dev/null 2>&1");
		shell_exec("{$GLOBALS["postconf"]} -e \"smtp_sasl_password_maps =\" >/dev/null 2>&1");
		return;
	}
	reset($GLOBALS["smtp_sasl_password_maps"]);
	while (list ($index, $value) = each ($GLOBALS["smtp_sasl_password_maps"]) ){$newarray[$value]=$value;}
	while (list ($index, $value) = each ($newarray) ){$newarray2[]=$value;}		

	echo "Starting......: Postfix ". count($newarray2)." smtp password rule(s)\n"; 
	@file_put_contents("/etc/postfix/smtp_sasl_password",implode("\n",$newarray2));
	shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix/smtp_sasl_password >/dev/null 2>&1");
	shell_exec("{$GLOBALS["postconf"]} -e \"smtp_sasl_password_maps = hash:/etc/postfix/smtp_sasl_password\" >/dev/null 2>&1");
	shell_exec("{$GLOBALS["postconf"]} -e \"smtp_sasl_auth_enable = yes\" >/dev/null 2>&1");
}

function sender_dependent_relayhost_maps_build(){
	$ldap=new clladp();
	$filter="(&(objectClass=SenderDependentRelayhostMaps)(cn=*))";
	$attrs=array("cn","SenderRelayHost");
	$dn="cn=Sender_Dependent_Relay_host_Maps,cn=artica,$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);
	for($i=0;$i<$hash["count"];$i++){
		$mail=$hash[$i]["cn"][0];
		$value=trim($hash[$i][strtolower("SenderRelayHost")][0]);
		if($value==null){continue;}
		if($value==":"){continue;}
		$sender_dependent_relayhost_maps[$mail]=$value;
		//$GLOBALS["sender_dependent_relayhost_maps"][]="$mail\t$value";
	}
	
	$filter="(&(objectClass=userAccount)(mail=*))";
	$attrs=array("mail","AlternateSmtpRelay");
	$dn="dc=organizations,$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);
	for($i=0;$i<$hash["count"];$i++){
		$mail=$hash[$i]["mail"][0];
		$value=trim($hash[$i][strtolower("AlternateSmtpRelay")][0]);
		if($value==null){continue;}
		if($value==":"){continue;}
		$sender_dependent_relayhost_maps[$mail]=$value;
		//$GLOBALS["sender_dependent_relayhost_maps"][]="$mail\t$value";
	}	
	
	$filter="(&(objectClass=SenderDependentSaslInfos)(cn=*))";
	$attrs=array("cn","SenderCanonicalRelayHost");
	$dn="dc=organizations,$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);
	for($i=0;$i<$hash["count"];$i++){
		$mail=$hash[$i]["cn"][0];
		$value=trim($hash[$i][strtolower("SenderCanonicalRelayHost")][0]);
		if($value==null){continue;}
		if($value==":"){continue;}
		$sender_dependent_relayhost_maps[$mail]=$value;
	}
	
	
	$arr=array("SmtpSaslPasswordString");
	$filter="(&(objectclass=PostfixSmtpSaslPaswordMaps)(cn=*))";
	$dn="cn=smtp_sasl_password_maps,cn=artica,$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);
	for($i=0;$i<$hash["count"];$i++){
		$mail="{$hash[$i]["cn"][0]}";
		$value=trim($hash[$i][strtolower("SmtpSaslPasswordString")][0]);
		if($value==null){continue;}
		if($value==":"){continue;}
		$sender_dependent_relayhost_maps[$mail]=$value;
	}

	if(is_array($sender_dependent_relayhost_maps)){
		while (list ($mail, $value) = each ($sender_dependent_relayhost_maps) ){
			$GLOBALS["sender_dependent_relayhost_maps"][]="$mail\t$value";
		}
	}

}

function sender_dependent_relayhost_maps(){
	sender_dependent_relayhost_maps_build();
	if(!is_array($GLOBALS["sender_dependent_relayhost_maps"])){
		echo "Starting......: 0 sender dependent relayhost rule(s)\n"; 
		shell_exec("{$GLOBALS["postconf"]} -e \"sender_dependent_relayhost_maps =hash:/etc/postfix/sender_dependent_relayhost\" >/dev/null 2>&1");
		@file_put_contents("/etc/postfix/sender_dependent_relayhost","#");
		shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix/sender_dependent_relayhost >/dev/null 2>&1");
	}

	echo "Starting......: Postfix ". count($GLOBALS["sender_dependent_relayhost_maps"])." sender dependent relayhost rule(s)\n"; 
	@file_put_contents("/etc/postfix/sender_dependent_relayhost",implode("\n",$GLOBALS["sender_dependent_relayhost_maps"]));
	shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix/sender_dependent_relayhost >/dev/null 2>&1");
	shell_exec("{$GLOBALS["postconf"]} -e \"sender_dependent_relayhost_maps = hash:/etc/postfix/sender_dependent_relayhost\" >/dev/null 2>&1");
}


function sender_canonical_maps_build(){
	$ldap=new clladp();
	$filter="(&(objectClass=userAccount)(mail=*))";
	$attrs=array("mail","SenderCanonical");
	$dn="$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);

	for($i=0;$i<$hash["count"];$i++){
		$mail=$hash[$i]["mail"][0];
		$canonical=$hash[$i][strtolower("SenderCanonical")][0];
		if($canonical==null){continue;}
		$GLOBALS["sender_canonical_maps"][]="$mail\t$canonical";
		$GLOBALS["smtp_generic_maps"][]="$mail\t$canonical";
	}

	
	
			
}

function smtp_generic_maps_build_global(){
	$q=new mysql();
	$sql="SELECT * FROM smtp_generic_maps WHERE ou='POSTFIX_MAIN' ORDER BY generic_from";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if(trim($ligne["generic_from"])==null){continue;}
		if(trim($ligne["generic_to"])==null){continue;}		
		$GLOBALS["smtp_generic_maps"][]="{$ligne["generic_from"]}\t{$ligne["generic_to"]}";
	}
}


function sender_canonical_maps(){
	if(!is_array($GLOBALS["sender_canonical_maps"])){
		echo "Starting......: 0 sender retranslation rule(s)\n"; 
		shell_exec("{$GLOBALS["postconf"]} -e \"sender_canonical_maps =\" >/dev/null 2>&1");
	}

	echo "Starting......: Postfix ". count($GLOBALS["sender_canonical_maps"])." sender retranslation rule(s)\n"; 
	@file_put_contents("/etc/postfix/sender_canonical",implode("\n",$GLOBALS["sender_canonical_maps"]));
	shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix/sender_canonical >/dev/null 2>&1");
	shell_exec("{$GLOBALS["postconf"]} -e \"sender_canonical_maps = hash:/etc/postfix/sender_canonical\" >/dev/null 2>&1");
}

function smtp_generic_maps(){
	if(!is_array($GLOBALS["smtp_generic_maps"])){
		echo "Starting......: 0 SMTP generic retranslations rule(s)\n"; 
		shell_exec("{$GLOBALS["postconf"]} -e \"smtp_generic_maps =\" >/dev/null 2>&1");
	}	
	echo "Starting......: Postfix ". count($GLOBALS["smtp_generic_maps"])." SMTP generic retranslations rule(s)\n"; 
	@file_put_contents("/etc/postfix/smtp_generic_maps",implode("\n",$GLOBALS["smtp_generic_maps"]));
	shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix/smtp_generic_maps >/dev/null 2>&1");
	shell_exec("{$GLOBALS["postconf"]} -e \"smtp_generic_maps = hash:/etc/postfix/smtp_generic_maps\" >/dev/null 2>&1");
}






function recipient_canonical_maps(){
	if(!is_array($GLOBALS["recipient_canonical_maps"])){
		echo "Starting......: 0 recipient retranslation rule(s)\n"; 
		shell_exec("{$GLOBALS["postconf"]} -e \"recipient_canonical_maps =\" >/dev/null 2>&1");
		return;
	}

	echo "Starting......: Postfix ". count($GLOBALS["recipient_canonical_maps"])." recipient retranslation rule(s)\n"; 
	@file_put_contents("/etc/postfix/recipient_canonical",implode("\n",$GLOBALS["recipient_canonical_maps"]));
	shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix/recipient_canonical >/dev/null 2>&1");
	shell_exec("{$GLOBALS["postconf"]} -e \"recipient_canonical_maps = hash:/etc/postfix/recipient_canonical\" >/dev/null 2>&1");
	
}

function restrict_relay_domains(){
	$ldap=new clladp();
	$dn="dc=organizations,$ldap->suffix";
	$attr=array("cn");
	$pattern="(&(objectclass=PostfixRelayRecipientMaps)(cn=@*))";
	$sr =@ldap_search($ldap->ldap_connection,$dn,$pattern,$attr);
	$hash=ldap_get_entries($ldap->ldap_connection,$sr);
	$relaysdomains=$ldap->hash_get_relay_domains();
	
	for($i=0;$i<$hash["count"];$i++){
		$domain=$hash[$i]["cn"][0];
		if(preg_match("#^@(.+)#",$domain,$re)){$domain=$re[1];}
		unset($relaysdomains[$domain]);
	}
	
	unset($relaysdomains["localhost.localdomain"]);
	if(is_array($relaysdomains)){while (list ($num, $ligne) = each ($relaysdomains) ){$f[]="$num\tartica_restrict_relay_domains";}}
	echo "Starting......: Postfix ". count($f)." restricted relayed domains\n"; 
	@file_put_contents("/etc/postfix/relay_domains_restricted",implode("\n",$f));
	shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix/relay_domains_restricted >/dev/null 2>&1");
	
}



function transport_maps_search(){
	$ldap=new clladp();
	$filter="(&(objectClass=transportTable)(cn=*))";
	$attrs=array("cn","transport");
	$dn="$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);	
	for($i=0;$i<$hash["count"];$i++){
		$domain=$hash[$i]["cn"][0];
		$transport=$hash[$i]["transport"][0];
		//$transport=str_replace("relay:","smtp:",$transport);
		
		if(substr($domain,0,1)=="@"){$domain=substr($domain,1,strlen($domain));}
		if(!$GLOBALS["transport_mem"]["$domain"]){$GLOBALS["transport_maps"]["$domain"]="$transport";}
		
		if(strpos("  $domain","@")==0){$domain="@$domain";}
		if(!$GLOBALS["transport_mem"]["$domain"]){$GLOBALS["transport_maps_AT"]["$domain"]="$transport";}
		$GLOBALS["transport_mem"]["$domain"]=true;
	}
	
	$t=0;
	if(is_array($GLOBALS["REMOTE_SMTP_LDAPDB_ROUTING"])){
		while (list ($domain, $targeted_ip) = each ($GLOBALS["REMOTE_SMTP_LDAPDB_ROUTING"]) ){
			$transport="relay[$targeted_ip]:25";
			if(!$GLOBALS["transport_mem"]["@$domain"]){
					$t++;
					$GLOBALS["transport_maps"]["$domain"]="$transport";
					$GLOBALS["transport_maps_AT"]["$domain"]="$transport";
				}
			$GLOBALS["transport_mem"]["@$domain"]=true;
		}
	}	
	echo "Starting......: Postfix $t routed domains from external sources\n";
	
  	$dn="cn=artica_smtp_sync,cn=artica,$ldap->suffix";
  	$filter="(&(objectClass=InternalRecipients)(cn=*))";	
  	$attrs=array("cn","ArticaSMTPSenderTable");	
	$hash=$ldap->Ldap_search($dn,$filter,$attrs);	
	for($i=0;$i<$hash["count"];$i++){
		$email=$hash[$i]["cn"][0];
		$transport=$hash[$i][strtolower("ArticaSMTPSenderTable")][0];
		$uid=$ldap->uid_from_email($email);
		if($uid<>null){continue;}
		if(!$GLOBALS["transport_mem"]["$email"]){
			$GLOBALS["transport_maps"]["$email"]="$transport";
		}
		$GLOBALS["transport_mem"]["$email"]=true;
	}  
}

function relayhost(){
	$sock=new sockets();
	$PostfixRelayHost=trim($sock->GET_INFO("PostfixRelayHost"));
	
	if($PostfixRelayHost==null){
		$main=new main_cf();
		$main=new main_cf()	;
		$PostfixRelayHost=$main->main_array["relayhost"];
	}
	
	
	if($PostfixRelayHost==null){
		shell_exec("{$GLOBALS["postconf"]} -e \"relayhost =\" >/dev/null 2>&1");
		return null;
	}
	$tools=new DomainsTools();
	$hash=$tools->transport_maps_explode($PostfixRelayHost);
	if($hash[2]==null){$hash[2]=25;}
	$PostfixRelayHost_pattern="[{$hash[1]}]:{$hash[2]}";
	echo "Starting......: Relay host: $PostfixRelayHost_pattern\n"; 
	$ldap=new clladp();
	$sasl_password_string=$ldap->sasl_relayhost($hash[1]);
	if($sasl_password_string<>null){
		$relayhost_hash="$PostfixRelayHost_pattern\t$sasl_password_string\n";
		@file_put_contents("/etc/postfix/sasl_passwd",$relayhost_hash);
		shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix/sasl_passwd >/dev/null 2>&1");
		shell_exec("{$GLOBALS["postconf"]} -e \"smtp_sasl_password_maps = hash:/etc/postfix/sasl_passwd\" >/dev/null 2>&1");
	}
	
	
	shell_exec("{$GLOBALS["postconf"]} -e \"relayhost =$PostfixRelayHost_pattern\" >/dev/null 2>&1");
	
}

function postmaster(){
	$sock=new sockets();
	$users=new usersMenus();
	$hostname=$sock->GET_INFO("myhostname");
	if($hostname==null){$hostname=$users->hostname;}
	if($GLOBALS["DEBUG"]){echo "postmaster():: Hostname=$hostname\n";}
	$hosts=explode(".",$hostname);
	if(count($hosts)>0){$mydomain_default="\\\$myhostname";}else{$mydomain_default="localdomain";}
	
		
	$PostfixPostmaster=trim($sock->GET_INFO("PostfixPostmaster"));
	$PostfixPostmasterSender=trim($sock->GET_INFO("PostfixPostmasterSender"));
	if($PostfixPostmaster==null){
		$error_notice_recipient="postmaster";
		$delay_notice_recipient="postmaster";
		$empty_address_recipient="MAILER-DAEMON";
		$myorigin="\\\$myhostname";
	}else{
		$error_notice_recipient="$PostfixPostmaster";
		$delay_notice_recipient="$PostfixPostmaster";
		$empty_address_recipient="$PostfixPostmaster";
	}
	shell_exec("{$GLOBALS["postconf"]} -e \"error_notice_recipient =$error_notice_recipient\" >/dev/null 2>&1");
	shell_exec("{$GLOBALS["postconf"]} -e \"delay_notice_recipient =$delay_notice_recipient\" >/dev/null 2>&1");
	shell_exec("{$GLOBALS["postconf"]} -e \"empty_address_recipient =$empty_address_recipient\" >/dev/null 2>&1");
	
	$address_verify_sender="\\\$double_bounce_sender";
	$double_bounce_sender="double-bounce";
	$mydomain=$mydomain_default;
	
	if($PostfixPostmasterSender<>null){
		if(preg_match("#(.+?)@(.+)#",$PostfixPostmasterSender,$re)){
			$mydomain=$re[2];
			$myorigin="\$mydomain";
		}
		$address_verify_sender=$PostfixPostmasterSender;
		$double_bounce_sender=$PostfixPostmasterSender;
	
	}
	
	if($GLOBALS["DEBUG"]){echo "postmaster():: mydomain =$mydomain\n";}
	
	shell_exec("{$GLOBALS["postconf"]} -e \"address_verify_sender =$address_verify_sender\" >/dev/null 2>&1");
	shell_exec("{$GLOBALS["postconf"]} -e \"double_bounce_sender =$double_bounce_sender\" >/dev/null 2>&1");
	shell_exec("{$GLOBALS["postconf"]} -e \"mydomain =$mydomain\" >/dev/null 2>&1");	
}

function build_cyrus_lmtp_auth(){
	$users=new usersMenus();
	$disable=false;
	if($users->ZABBIX_INSTALLED){$disable=true;}else{
		if(!$users->cyrus_imapd_installed){$disable=true;}
	}
	
	if($disable){
		shell_exec("{$GLOBALS["postconf"]} -e \"lmtp_sasl_auth_enable =no\" >/dev/null 2>&1");
		shell_exec("{$GLOBALS["postconf"]} -e \"lmtp_sasl_password_maps =\" >/dev/null 2>&1");
		shell_exec("{$GLOBALS["postconf"]} -e \"lmtp_sasl_security_options =\" >/dev/null 2>&1");
		return;		
	}
	
	
	$sock=new sockets();
	$page=CurrentPageName();
	$CyrusEnableLMTPUnix=$sock->GET_INFO("CyrusEnableLMTPUnix");	
	if($CyrusEnableLMTPUnix==1){
		shell_exec("{$GLOBALS["postconf"]} -e \"lmtp_sasl_auth_enable =no\" >/dev/null 2>&1");
		shell_exec("{$GLOBALS["postconf"]} -e \"lmtp_sasl_password_maps =\" >/dev/null 2>&1");
		shell_exec("{$GLOBALS["postconf"]} -e \"lmtp_sasl_security_options =\" >/dev/null 2>&1");	
	}else{
		$ldap=new clladp();
		$CyrusLMTPListen=trim($sock->GET_INFO("CyrusLMTPListen"));
		$cyruspass=$ldap->CyrusPassword();
		if($CyrusLMTPListen==null){return;}
		@file_put_contents("/etc/postfix/lmtpauth","$CyrusLMTPListen\tcyrus:$cyruspass");
		shell_exec("{$GLOBALS["postmap"]} hash:/etc/postfix/lmtpauth >/dev/null 2>&1");
		shell_exec("{$GLOBALS["postconf"]} -e \"lmtp_sasl_auth_enable =yes\" >/dev/null 2>&1");
		shell_exec("{$GLOBALS["postconf"]} -e \"lmtp_sasl_password_maps = hash:/etc/postfix/lmtpauth\" >/dev/null 2>&1");
		shell_exec("{$GLOBALS["postconf"]} -e \"lmtp_sasl_mechanism_filter = plain, login\" >/dev/null 2>&1");
		shell_exec("{$GLOBALS["postconf"]} -e \"lmtp_sasl_security_options =\" >/dev/null 2>&1");
	}
	
}



?>
