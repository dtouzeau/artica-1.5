<?php
	session_start();
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');

	if(!isset($_SESSION["uid"])){echo "<H2><a href='logon.php'>Session out</a></H2>";exit();}
	
$tpl=new template_users();
$_GET["PRIVS"]=$tpl->_ParsePrivieleges($_SESSION["privileges"]["ArticaGroupPrivileges"]);


if(isset($_POST["branch_id"])){
	BuildBranches();exit;
}

echo "var LdapTreeStruct=[\n\t" . BuildLevel0() . "];";


function BuildSystem(){
	$mny=new usersMenus();
	if($mny->AsSystemAdministrator){
		$items=$items . "{\n";
		$items=$items . "\t\t'id' : 'applications:tasks',\n";
		$items=$items . "\t\t'txt' : 'Tasks',\n";
		$items=$items . "\t\t'img' : 'tasks-18.gif',\n";
		$items=$items . "\t\t'onopenpopulate' : myOpenPopulate,\n";
		$items=$items . "\t\t'openlink' : 'ldapTree.php',\n";
		$items=$items . "\t\t'canhavechildren' : false\n";
		$items=$items . "\t\t},";
		
		$items=$items . "{\n";
		$items=$items . "\t\t'id' : 'server:interfaces',\n";
		$items=$items . "\t\t'txt' : 'Interfaces',\n";
		$items=$items . "\t\t'img' : 'tree-network.gif',\n";
		$items=$items . "\t\t'onopenpopulate' : myOpenPopulate,\n";
		$items=$items . "\t\t'openlink' : 'ldapTree.php',\n";
		$items=$items . "\t\t'canhavechildren' : false\n";
		$items=$items . "\t\t},";	
		
		$items=$items . "{\n";
		$items=$items . "\t\t'id' : 'server:logmonitor',\n";
		$items=$items . "\t\t'txt' : 'Logs Monitor',\n";
		$items=$items . "\t\t'img' : 'log-monitor-20.gif',\n";
		$items=$items . "\t\t'onopenpopulate' : myOpenPopulate,\n";
		$items=$items . "\t\t'openlink' : 'ldapTree.php',\n";
		$items=$items . "\t\t'canhavechildren' : false\n";
		$items=$items . "\t\t},";					
		}
	
	
if($items[strlen($items)-1]==','){$items=substr($items,0,strlen($items)-1);}
return $items . "";			
		
	
}


function BuildLevel1($noitems=0){
$mny=new usersMenus();
	
	if($mny->AsSystemAdministrator){
		$items=$items . "{\n";
		$items=$items . "\t\t'id' : 'server:settings',\n";
		$items=$items . "\t\t'txt' : 'Local system',\n";
		$items=$items . "\t\t'img' : 'tree-server-1.gif',\n";
		$items=$items . "\t\t'onopenpopulate' : myOpenPopulate,\n";
		$items=$items . "\t\t'openlink' : 'ldapTree.php',\n";
		$items=$items . "\t\t'canhavechildren' : true\n";
		$items=$items . "\t\t},";	
		
	}
	
	if($mny->isTreeReadable()==true){
		$items=$items . "{\n";
		$items=$items . "\t\t'id' : 'server:applications',\n";
		$items=$items . "\t\t'txt' : 'Applications',\n";
		$items=$items . "\t\t'img' : 'tree-applications.gif',\n";
		$items=$items . "\t\t'onopenpopulate' : myOpenPopulate,\n";
		$items=$items . "\t\t'openlink' : 'ldapTree.php',\n";
		$items=$items . "\t\t'canhavechildren' : true\n";
		$items=$items . "\t\t},";	
		}
	
	
		$items=$items . "{\n";
		$items=$items . "\t\t'id' : 'server:organisations',\n";
		$items=$items . "\t\t'txt' : 'Organizations',\n";
		$items=$items . "\t\t'img' : 'organisation-22.gif',\n";
		$items=$items . "\t\t'onopenpopulate' : myOpenPopulate,\n";
		$items=$items . "\t\t'openlink' : 'ldapTree.php',\n";
		$items=$items . "\t\t'canhavechildren' : true\n";
		$items=$items . "\t\t},";			

	
if($items[strlen($items)-1]==','){$items=substr($items,0,strlen($items)-1);}
return $items . "";		
	
}

function BuildOus(){
	$ldap=new clladp();
	$mny=new usersMenus();
	if($mny->IsGlobalAdmin()){$hash=$ldap->hash_get_ou_full_datas();}else{$hash=$ldap->Hash_Get_ou_from_users($_SESSION["uid"]);}	
	
if(is_array($hash)){
	while (list ($num, $ligne) = each ($hash) ){
		$items=$items . "{\n";
		$items=$items . "\t\t'id' : 'ou:$ligne',\n";
		$items=$items . "\t\t'txt' : '$num',\n";
		$items=$items . "\t\t'img' : 'tree-server.gif',\n";
		$items=$items . "\t\t'onopenpopulate' : myOpenPopulate,\n";
		$items=$items . "\t\t'openlink' : 'ldapTree.php',\n";
		$items=$items . "\t\t'canhavechildren' : true\n";
		$items=$items . "\t\t},";
		}
	}
	
if($items[strlen($items)-1]==','){$items=substr($items,0,strlen($items)-1);}
return $items . "";			
	
}

function BuildBranches(){
	$branch_id=$_POST["branch_id"];
	writelogs("Buildbranches ->$branch_id",__FUNCTION__,basename(__FILE__));
	if(preg_match('#^ou:ou=(.+),#',$branch_id,$reg)){echo " items:[" . BuildLevel2($reg[1])."]";exit;}
	if(preg_match('#^group:([0-9]+)#',$branch_id,$reg)){echo " items:[" . BuildLevel3($reg[1])."]";exit;}
	
	if($branch_id=='applications:postfix'){echo " items:[" . BuildLevelPostfix()."]";exit;}
	if($branch_id=='settings:postfix:SecurityRules'){echo " items:[" . BuildLevelPostFixRules()."]";exit;}
	
	if(preg_match('#^settings:postfix:domains#',$branch_id,$reg)){echo " items:[" . BuildLevelPostfixDomains()."]";exit;}
	
	if(preg_match('#^applications:fechmail#',$branch_id,$reg)){echo " items:[" . BuildLevelFetchMail()."]";exit;}
	if(preg_match('#^applications:aveserver#',$branch_id,$reg)){echo " items:[" . BuildAveServer()."]";exit;}
	
	if(preg_match('#^server:settings#',$branch_id,$reg)){echo " items:[" . BuildSystem()."]";exit;}
	if(preg_match('#^server:applications#',$branch_id,$reg)){echo " items:[" . BuildLevelApplications()."]";exit;}
	if(preg_match('#^server:organisations#',$branch_id,$reg)){echo " items:[" . BuildOus()."]";exit;}
	
	if(preg_match('#^applications:kas3#',$branch_id,$reg)){echo " items:[" . BuildKas3()."]";exit;}
	
	if(preg_match('#^applications:cyrus#',$branch_id,$reg)){exit;}
	
	
	
	echo " items:[" . BuildLevel1(1) ."]";
	
}

function BuildLevelApplications(){
	$mny=new usersMenus();
	$pages=new HtmlPages();
	if($mny->AsPostfixAdministrator==true){
		$items=$items . "{\n";
		$items=$items . "\t\t'id' : 'applications:postfix',\n";
		$items=$items . "\t\t'txt' : 'Postfix MTA',\n";
		$items=$items . "\t\t'img' : 'tickets.gif',\n";
		$items=$items . "\t\t'onopenpopulate' : myOpenPopulate,\n";
		$items=$items . "\t\t'openlink' : 'ldapTree.php',\n";
		$items=$items . "\t\t'canhavechildren' : true\n";
		$items=$items . "\t\t},";
		
	}	
	if($pages->fetchmail_installed==true){
		if($mny->AsMailBoxAdministrator==true){
			$items=$items . "{\n";
			$items=$items . "\t\t'id' : 'applications:fechmail',\n";
			$items=$items . "\t\t'txt' : 'Fetchmail',\n";
			$items=$items . "\t\t'img' : 'updaterX-20.gif',\n";
			$items=$items . "\t\t'onopenpopulate' : myOpenPopulate,\n";
			$items=$items . "\t\t'openlink' : 'ldapTree.php',\n";
			$items=$items . "\t\t'canhavechildren' : true\n";
			$items=$items . "\t\t},";
			}
		}
		
	if($pages->aveserver_installed==true){
		if($mny->AsPostfixAdministrator==true){
			$items=$items . "{\n";
			$items=$items . "\t\t'id' : 'applications:aveserver',\n";
			$items=$items . "\t\t'txt' : 'Kaspersky For Mails Servers',\n";
			$items=$items . "\t\t'img' : 'protection-20.gif',\n";
			$items=$items . "\t\t'onopenpopulate' : myOpenPopulate,\n";
			$items=$items . "\t\t'openlink' : 'ldapTree.php',\n";
			$items=$items . "\t\t'canhavechildren' : true\n";
			$items=$items . "\t\t},";			
		}
	}

	if($pages->kas_installed==true){
		if($mny->AsPostfixAdministrator==true){
			$items=$items . "{\n";
			$items=$items . "\t\t'id' : 'applications:kas3',\n";
			$items=$items . "\t\t'txt' : 'Kaspersky Anti-spam',\n";
			$items=$items . "\t\t'img' : 'protection-20.gif',\n";
			$items=$items . "\t\t'onopenpopulate' : myOpenPopulate,\n";
			$items=$items . "\t\t'openlink' : 'ldapTree.php',\n";
			$items=$items . "\t\t'canhavechildren' : true\n";
			$items=$items . "\t\t},";			
		}
	}	

	
	if($pages->procmail_installed==true){
		if($mny->AsMailBoxAdministrator==true){
			$items=$items . "{\n";
			$items=$items . "\t\t'id' : 'applications:procmail',\n";
			$items=$items . "\t\t'txt' : 'Procmail',\n";
			$items=$items . "\t\t'img' : 'tree_loupe.gif',\n";
			$items=$items . "\t\t'onopenpopulate' : myOpenPopulate,\n";
			$items=$items . "\t\t'openlink' : 'ldapTree.php',\n";
			$items=$items . "\t\t'canhavechildren' : false\n";
			$items=$items . "\t\t},";			
		}
	}	
		
	if($pages->cyrus_imapd_installed==true){
		if($mny->AsMailBoxAdministrator==true){
			$items=$items . "{\n";
			$items=$items . "\t\t'id' : 'applications:cyrus',\n";
			$items=$items . "\t\t'txt' : 'Cyrus imap',\n";
			$items=$items . "\t\t'img' : 'tree-cyrus-20.gif',\n";
			$items=$items . "\t\t'onopenpopulate' : myOpenPopulate,\n";
			$items=$items . "\t\t'openlink' : 'ldapTree.php',\n";
			$items=$items . "\t\t'canhavechildren' : false\n";
			$items=$items . "\t\t},";			
		}
	}
	
	
	
	
	
	
if($items[strlen($items)-1]==','){$items=substr($items,0,strlen($items)-1);}
return $items . "";		
	
	
}
//key.gif
function BuildLevelPostfixDomains(){
$mny=new usersMenus();
	if($mny->AsPostfixAdministrator==false){return null;}

	$items=$items . "{\n";
		$items=$items . "\t\t'id' : 'settings:postfix:domains:auth',\n";
		$items=$items . "\t\t'txt' : 'SMTP Authentication',\n";
		$items=$items . "\t\t'img' : 'tree-auth-20.gif',\n";
		$items=$items . "\t\t'onopenpopulate' : myOpenPopulate,\n";
		$items=$items . "\t\t'openlink' : 'ldapTree.php',\n";
		$items=$items . "\t\t'editable' : false,\n";		
		$items=$items . "\t\t'canhavechildren' : false\n";
		$items=$items . "\t\t},";	

	if($items[strlen($items)-1]==','){$items=substr($items,0,strlen($items)-1);}
	return $items . "";		
}

function BuildLevelPostFixRules(){
	$mny=new usersMenus();
	if($mny->AsPostfixAdministrator==false){return null;}	
	
$items=$items . "{\n";
		$items=$items . "\t\t'id' : 'settings:postfix:rules',\n";
		$items=$items . "\t\t'txt' : 'Content Rules',\n";
		$items=$items . "\t\t'img' : 'tree-rules.gif',\n";
		$items=$items . "\t\t'onopenpopulate' : myOpenPopulate,\n";
		$items=$items . "\t\t'openlink' : 'ldapTree.php',\n";
		$items=$items . "\t\t'editable' : false,\n";
		$items=$items . "\t\t'onbeforeopen' : myBeforeOpen,\n";
		$items=$items . "\t\t'canhavechildren' : false\n";
		$items=$items . "\t\t},";	
		
		$items=$items . "{\n";
		$items=$items . "\t\t'id' : 'settings:postfix:smtpd_sender_restrictions',\n";
		$items=$items . "\t\t'txt' : 'Senders Rules',\n";
		$items=$items . "\t\t'img' : 'fw-20.gif',\n";
		$items=$items . "\t\t'onopenpopulate' : myOpenPopulate,\n";
		$items=$items . "\t\t'openlink' : 'ldapTree.php',\n";
		$items=$items . "\t\t'onbeforeopen' : myBeforeOpen,\n";
		$items=$items . "\t\t'editable' : false,\n";
		$items=$items . "\t\t'canhavechildren' : false\n";
		$items=$items . "\t\t},";			
		
		$items=$items . "{\n";
		$items=$items . "\t\t'id' : 'settings:postfix:smtpd_client_restrictions',\n";
		$items=$items . "\t\t'txt' : 'Clients Rules',\n";
		$items=$items . "\t\t'img' : 'fw-20.gif',\n";
		$items=$items . "\t\t'onopenpopulate' : myOpenPopulate,\n";
		$items=$items . "\t\t'openlink' : 'ldapTree.php',\n";
		$items=$items . "\t\t'onbeforeopen' : myBeforeOpen,\n";
		$items=$items . "\t\t'editable' : false,\n";
		$items=$items . "\t\t'canhavechildren' : false\n";
		$items=$items . "\t\t},";				
if($items[strlen($items)-1]==','){$items=substr($items,0,strlen($items)-1);}
	return $items . "";			
}


function BuildLevelPostfix(){
	$mny=new usersMenus();
	if($mny->AsPostfixAdministrator==false){return null;}
		$items=$items . "{\n";
		$items=$items . "\t\t'id' : 'settings:postfix:network',\n";
		$items=$items . "\t\t'txt' : 'Network',\n";
		$items=$items . "\t\t'img' : 'tree-network.gif',\n";
		$items=$items . "\t\t'onopenpopulate' : myOpenPopulate,\n";
		$items=$items . "\t\t'openlink' : 'ldapTree.php',\n";
		$items=$items . "\t\t'onbeforeopen' : myBeforeOpen,\n";
		$items=$items . "\t\t'editable' : false,\n";		
		$items=$items . "\t\t'canhavechildren' : false\n";
		$items=$items . "\t\t},";
		
		$items=$items . "{\n";
		$items=$items . "\t\t'id' : 'settings:postfix:domains',\n";
		$items=$items . "\t\t'txt' : 'Domains',\n";
		$items=$items . "\t\t'img' : 'tree-domains-20.gif',\n";
		$items=$items . "\t\t'onopenpopulate' : myOpenPopulate,\n";
		$items=$items . "\t\t'openlink' : 'ldapTree.php',\n";
		$items=$items . "\t\t'onbeforeopen' : myBeforeOpen,\n";
		$items=$items . "\t\t'editable' : false,\n";		
		$items=$items . "\t\t'canhavechildren' : true\n";
		$items=$items . "\t\t},";		
		
		
		
		$items=$items . "{\n";
		$items=$items . "\t\t'id' : 'settings:postfix:SecurityRules',\n";
		$items=$items . "\t\t'txt' : 'Security Rules',\n";
		$items=$items . "\t\t'img' : 'rules-20.gif',\n";
		$items=$items . "\t\t'onopenpopulate' : myOpenPopulate,\n";
		$items=$items . "\t\t'onbeforeopen' : myBeforeOpen,\n";
		$items=$items . "\t\t'openlink' : 'ldapTree.php',\n";
		$items=$items . "\t\t'editable' : false,\n";
		$items=$items . "\t\t'canhavechildren' : true\n";
		$items=$items . "\t\t},";			
		
		
		
		$items=$items . "{\n";
		$items=$items . "\t\t'id' : 'settings:postfix:tls',\n";
		$items=$items . "\t\t'txt' : 'TLS',\n";
		$items=$items . "\t\t'img' :'tree-lock-18.gif',\n";
		$items=$items . "\t\t'onopenpopulate' : myOpenPopulate,\n";
		$items=$items . "\t\t'openlink' : 'ldapTree.php',\n";
		$items=$items . "\t\t'editable' : false,\n";
		$items=$items . "\t\t'canhavechildren' : false\n";
		$items=$items . "\t\t},";			
		
		
	if($items[strlen($items)-1]==','){$items=substr($items,0,strlen($items)-1);}
	return $items . "";				


		
}

function BuildAveServer(){
$mny=new usersMenus();
if($mny->AsPostfixAdministrator==false){return null;}	
		$items=$items . "{\n";
		$items=$items . "\t\t'id' : 'settings:aveserver:licence',\n";
		$items=$items . "\t\t'txt' : 'Licence',\n";
		$items=$items . "\t\t'img' : 'key-18.gif',\n";
		$items=$items . "\t\t'onopenpopulate' : myOpenPopulate,\n";
		$items=$items . "\t\t'openlink' : 'ldapTree.php',\n";
		$items=$items . "\t\t'canhavechildren' : false\n";
		$items=$items . "\t\t},";
		
		$items=$items . "{\n";
		$items=$items . "\t\t'id' : 'settings:aveserver:update',\n";
		$items=$items . "\t\t'txt' : 'Update engine',\n";
		$items=$items . "\t\t'img' : 'updaterX-22.gif',\n";
		$items=$items . "\t\t'onopenpopulate' : myOpenPopulate,\n";
		$items=$items . "\t\t'openlink' : 'ldapTree.php',\n";
		$items=$items . "\t\t'canhavechildren' : false\n";
		$items=$items . "\t\t},";		

	if($items[strlen($items)-1]==','){$items=substr($items,0,strlen($items)-1);}
	return $items . "";		
}

function BuildKas3(){
$mny=new usersMenus();
if($mny->AsPostfixAdministrator==false){return null;}	
		$items=$items . "{\n";
		$items=$items . "\t\t'id' : 'settings:kas3:licence',\n";
		$items=$items . "\t\t'txt' : 'Licence',\n";
		$items=$items . "\t\t'img' : 'key-18.gif',\n";
		$items=$items . "\t\t'onopenpopulate' : myOpenPopulate,\n";
		$items=$items . "\t\t'openlink' : 'ldapTree.php',\n";
		$items=$items . "\t\t'canhavechildren' : false\n";
		$items=$items . "\t\t},";
		
		$items=$items . "{\n";
		$items=$items . "\t\t'id' : 'settings:kas3:update',\n";
		$items=$items . "\t\t'txt' : 'Update engine',\n";
		$items=$items . "\t\t'img' : 'updaterX-22.gif',\n";
		$items=$items . "\t\t'onopenpopulate' : myOpenPopulate,\n";
		$items=$items . "\t\t'openlink' : 'ldapTree.php',\n";
		$items=$items . "\t\t'canhavechildren' : false\n";
		$items=$items . "\t\t},";

		$items=$items . "{\n";
		$items=$items . "\t\t'id' : 'settings:kas3:generalSettings',\n";
		$items=$items . "\t\t'txt' : 'Anti-spam engine',\n";
		$items=$items . "\t\t'img' : 'settings-20.gif',\n";
		$items=$items . "\t\t'onopenpopulate' : myOpenPopulate,\n";
		$items=$items . "\t\t'openlink' : 'ldapTree.php',\n";
		$items=$items . "\t\t'canhavechildren' : false\n";
		$items=$items . "\t\t},";				

	if($items[strlen($items)-1]==','){$items=substr($items,0,strlen($items)-1);}
	return $items . "";		
}

function BuildLevelFetchMail(){
$mny=new usersMenus();
if($mny->AsMailBoxAdministrator==false){return null;}	
		$items=$items . "{\n";
		$items=$items . "\t\t'id' : 'settings:fetchmail:daemon',\n";
		$items=$items . "\t\t'txt' : 'Daemon',\n";
		$items=$items . "\t\t'img' : 'settings-20.gif',\n";
		$items=$items . "\t\t'onopenpopulate' : myOpenPopulate,\n";
		$items=$items . "\t\t'openlink' : 'ldapTree.php',\n";
		$items=$items . "\t\t'canhavechildren' : false\n";
		$items=$items . "\t\t},";


	if($items[strlen($items)-1]==','){$items=substr($items,0,strlen($items)-1);}
	return $items . "";	

}


function BuildLevel2($ou){
$ldap=new clladp();
$privileges=new usersMenus($_GET["PRIVS"]);
$HashDomains=$ldap->hash_get_domains_ou($ou);
$hash_group=$ldap->hash_groups($ou);
$hash_transport=$ldap->hash_load_transport();
$hash_users=$ldap->hash_get_users_Only_ou($ou);
writelogs("BuildLevel2 ->$ou users number=".count($hash_users),__FUNCTION__,basename(__FILE__));
if($privileges->AllowChangeDomains==true){
IF(is_array($HashDomains)){
	while (list ($num, $ligne) = each ($HashDomains) ){	
		$items=$items . "{\n";
		$items=$items . "\t\t\t'id' : 'domain:$ou,$num',\n";
		$items=$items . "\t\t\t'txt' : '$ligne',\n";
		
		if(isset($hash_transport[$ligne])){
		$items=$items . "\t\t\t'img' : 'alias-18.gif',\n";
		}else{
			$items=$items . "\t\t\t'img' : 'globe.gif',\n";
		}
		$items=$items . "\t\t\t'editable' : false,\n";
		$items=$items . "\t\t\t'draggable' : false\n";		
		$items=$items . "\t\t\t},";
	}
}}

if(is_array($hash_users)){if(count($hash_users)>50){
			$count=count($hash_users);
			$items=$items . "{\n";
			$items=$items . "\t\t\t'id' : 'users:$ou',\n";
			$items=$items . "\t\t\t'txt' : '$count users',\n";
			$items=$items . "\t\t'openlink' : 'ldapTree.php',\n";	
			$items=$items . "\t\t'draggable' : true,\n";	
			$items=$items . "\t\t\t'img' : 'family-20.gif'\n";
			$items=$items . "\t\t\t},";		
	}}
	
	
if(is_array($hash_group)){
while (list ($num, $ligne) = each ($hash_group) ){	
	$num=str_replace("'","`",$num);
	if(strlen($num)>20){$num=substr($num,0,17)."...";}
	$items=$items . "{\n";
	$items=$items . "\t\t\t'id' : 'group:{$ligne["gid"]}',\n";
	$items=$items . "\t\t\t'txt' : '$num',\n";
	$items=$items . "\t\t\t'img' : 'tree-groups',\n";
	$items=$items . "\t\t\t'editable' : true,\n";
	$items=$items . "\t\t\t'draggable' : false,\n";	
	$items=$items . "\t\t'onopenpopulate' : myOpenPopulate,\n";
	$items=$items . "\t\t'openlink' : 'ldapTree.php',\n";
	$items=$items . "\t\t'canhavechildren' : true\n";
	$items=$items . "\t\t\t},";
	}	
	
}

if(is_array($hash_users)){if(count($hash_users)<50){
		while (list ($num, $ligne) = each ($hash_users) ){	
			$userdatas=$ldap->UserDatas($ligne);
			$ligne=str_replace("'","\'",$ligne);
			$name=$userdatas["displayName"];
			$name=str_replace("'","`",$name);
			$items=$items . "{\n";
			$items=$items . "\t\t\t'id' : 'user:$ligne',\n";
			$items=$items . "\t\t\t'txt' : '$name',\n";
			$items=$items . "\t\t'openlink' : 'ldapTree.php',\n";	
			$items=$items . "\t\t'draggable' : true,\n";	
			$items=$items . "\t\t\t'img' : 'outicon_1002.gif'\n";
			$items=$items . "\t\t\t},";		
			}
	}
}




	
if($items[strlen($items)-1]==','){$items=substr($items,0,strlen($items)-1);}	
return $items;

}

function BuildLevel3($gid){
$ldap=new clladp();
$hash=$ldap->GroupDatas($gid);
$pages=new HtmlPages();
$hash=$hash["members"];

		$items=$items . "{\n";
		$items=$items . "\t\t\t'id' : 'kav:$gid',\n";
		$items=$items . "\t\t\t'txt' : 'Email Antivirus',\n";
		$items=$items . "\t\t'onclick' : TreeKavSelect,\n";	
		$items=$items . "\t\t'draggable' : false,\n";	
		$items=$items . "\t\t\t'editable' : false,\n";		
		$items=$items . "\t\t\t'img' : 'k.gif'\n";
		$items=$items . "\t\t\t},";	
		if($pages->kas_installed==true){
			$items=$items . "{\n";
			$items=$items . "\t\t\t'id' : 'kas:$gid',\n";
			$items=$items . "\t\t\t'txt' : 'Email Anti-spam',\n";
			$items=$items . "\t\t'onclick' : TreeKasSelect,\n";	
			$items=$items . "\t\t'draggable' : false,\n";	
			$items=$items . "\t\t\t'editable' : false,\n";		
			$items=$items . "\t\t\t'img' : 'k.gif'\n";
			$items=$items . "\t\t\t},";
		}

IF(is_array($hash)){
	while (list ($num, $ligne) = each ($hash) ){	
	$ligne=str_replace("'","`",$ligne);
	$num=str_replace("'","\'",$num);
	if($ligne==null){$ligne='unknown';}
	$items=$items . "{\n";
	$items=$items . "\t\t\t'id' : 'user:$num',\n";
	$items=$items . "\t\t\t'txt' : '$ligne',\n";
	$items=$items . "\t\t'openlink' : 'ldapTree.php',\n";	
	$items=$items . "\t\t'draggable' : true,\n";	
	$items=$items . "\t\t\t'img' : 'outicon_1002.gif'\n";
	$items=$items . "\t\t\t},";
		
	}}
	
			
	
if($items[strlen($items)-1]==','){$items=substr($items,0,strlen($items)-1);}	
return $items;

}

function BuildLevel0(){
	

	$html=$html . "{\n";
	$html=$html . "\t'id' : 'Root',\n";
	$html=$html . "\t'txt' : 'Server',\n";
	$html=$html . "\t'img' : 'tree-server.gif',\n";
	
	$html=$html . "\t'items' :[" . BuildLevel1() ."]\n";
	$html=$html . "\n\t}";

	return $html;
}

?>