<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.html.pages.inc');
	if(!isset($_SESSION["uid"])){echo "<H2><a href='logon.php'>Session out</a></H2>";exit();}
	if(isset($_GET["EditBranch"])){EditBranch();exit;}
	if(isset($_GET["SelectBranch"])){SelectBranch();exit;}
	if(isset($_POST["drag_id"])){MoveBranch();exit;}
	if(isset($_GET["DeleteBranch"])){DeleteBranch();exit;}
	if(isset($_GET["artica_ldap_settings"])){artica_ldap_settings();exit;}
	
function EditBranch(){
	
	
	
	$id=$_GET["EditBranch"];

	if(preg_match('#^user:(.+)#',$id,$reg)){
		return UpdateUser($reg[1]);
	}
	
	if(preg_match('#^group:([0-9]+)#',$id,$reg)){
		return UpdateGroup($reg[1]);
		}
}

function SelectBranch(){
	$id=$_GET["SelectBranch"];
	$pages=new HtmlPages();
	
	if($id=='Root'){echo $pages->PageRoot();exit;}
	if(preg_match('#^ou:(.+)#',$id,$reg)){echo $pages->PageOu($reg[1]);exit;}
	if(preg_match('#^user:(.+)#',$id,$reg)){echo $pages->PageUser(null,$reg[1],1);}
	if(preg_match('#^group:([0-9]+)#',$id,$reg)){echo $pages->PageGroup($reg[1]);exit ;}
	if(preg_match('#^domain:(.+),([0-9]+)#',$id,$reg)){echo $pages->PageDomain($reg[1],$reg[2]);}
	
	if(preg_match('#^applications:tasks#',$id)){echo $pages->PageTasks();}
	if(preg_match('#^applications:postfix$#',$id)){echo $pages->PagePostfixIndex();}
	if(preg_match('#^settings:postfix:rules#',$id)){echo $pages->PagePostfixRules();}
	if(preg_match('#^settings:postfix:tls#',$id)){echo $pages->PagePostfixTLS();}
	if(preg_match('#^settings:postfix:domains$#',$id)){echo $pages->PagePostfixDomains();}
	
	
	if($id=='settings:postfix:smtpd_client_restrictions'){echo $pages->PagePostfixsmtpd_client_restrictions();exit;}
	if($id=='settings:postfix:smtpd_sender_restrictions'){echo $pages->PagePostfixsmtpd_client_restrictions(1);exit;}
	if($id=='settings:postfix:domains:auth'){echo $pages->PagePostfixSMTPSaslAuth();exit;}
	
	if($id=='settings:postfix:SecurityRules'){echo $pages->PagePostfixSecurityRulesExplain();exit;}
	if($id=='applications:fetchmail'){echo $pages->PageFetchmail_status();exit;}
	if($id=='server:logmonitor'){echo $pages->PageLogMonitorIndex();exit;}

	if(preg_match('#^applications:fechmail#',$id)){echo $pages->PageFetchmail_status();}
	
	if(preg_match('#^applications:cyrus#',$id)){echo $pages->PageCyrus_status();exit;}
	
	if($id=='applications:cyrus'){echo $pages->PageCyrus_status();exit;}
	if($id=='applications:applications:cyrus2.2'){echo $pages->PageCyrus_status();exit;}
	if($id=='applications:applications:cyrus21'){echo $pages->PageCyrus_status();exit;}
	
	
	if($id=='applications:kas3'){echo $pages->PageKas3_status();exit;}
	
	if($id=='settings:kas3:generalSettings'){echo $pages->PageKas3ProcessServer();exit;}
	if($id=='settings:kas3:update'){echo $pages->PageKas3UpdateConfig();exit;}
	if($id=='settings:kas3:licence'){echo $pages->PageKas3Licence();exit;}
	
	if($id=='applications:procmail'){echo $pages->PageProcMailIntro();exit;}
	
	if(preg_match('#^settings:fetchmail:daemon#',$id)){echo $pages->PageFetchmail_Daemon();}
	
	
	if($id=='applications:aveserver'){echo $pages->PageAveServerStatus();exit;}
	if($id=='settings:aveserver:licence'){echo $pages->PageAveserverLicenceSection();exit;}
	if($id=='settings:aveserver:update'){echo $pages->PageAveServerUpdateConfig();exit();}
	
	
	if(preg_match('#^settings:postfix:network#',$id)){echo $pages->PagePostfix_maincf_interfaces();}
	if(preg_match('#^server:interfaces#',$id)){echo $pages->PageSystem_interfaces();}
	if(preg_match('#^server:applications#',$id)){echo $pages->PageSystem_applications_Status();}
	if(preg_match('#^server:organisations#',$id)){echo $pages->PageOrganisations();}
	
	
	
		
}
function DeleteBranch(){
	$id=$_GET["DeleteBranch"];
	$Parent=$_GET["PreviousBranch"];
	
	
	
	if(preg_match('#^ou:(.+)#',$id,$reg)){
		$ldap=new clladp();
		$ldap->ldap_delete($reg[1]);
		echo "Root";
	}
	if(preg_match('#^group:([0-9]+)#',$id,$reg)){
		$ldap=new clladp();
		$hash=$ldap->GroupDatas($reg[1]);
		$dn=$hash["dn"];
		$ou=$hash["ou"];
		$ldap->ldap_delete($dn);
		echo $Parent;
	}
	
	if(preg_match('#^user:(.+)#',$id,$reg)){
		$ldap=new clladp();
		$hash=$ldap->UserDatas($reg[1]);
		$ou=$hash["ou"];
		if(preg_match('#^group:([0-9]+)#',$Parent,$gp)){
			$ldap->GroupDeleteUser($gp[1],$reg[1]);
			echo $Parent;
			exit();
			}
		
		$ldap->ldap_delete($hash["dn"]);
		
		if(is_array($res["groups"])){
			while (list ($num, $ligne) = each ($res["groups"]) ){
				$ldap->GroupDeleteUser($ligne,$reg[1]);
			}
		}
		
		
		echo "ou:ou=$ou,dc=organizations,$ldap->suffix";
		}
		
	if(preg_match('#^domain:(.+),([0-9]+)#',$id,$reg)){
		$ldap=new clladp();
		$ldap->domainsDelete($reg[1],$reg[2]);
		echo $Parent;
		}		
	
}


function MoveBranch(){
	$dragged=$_POST["drag_id"];
	$dropped=$_POST["drop_id"];
	if(preg_match('#^user:(.+)#',$dragged,$reg)){
		if(preg_match('#^group:([0-9]+)#',$dropped,$reg2)){
			$ldap=new clladp();
			$hasgroup=$ldap->GroupDatas($reg2[1]);
			$update_array["memberUid"][]=$reg[1];
			$ldap->Ldap_add_mod($hasgroup["dn"],$update_array);
			if($ldap->ldap_last_error<>null){echo $ldap->ldap_last_error;exit;}
			echo "ok";exit;
			}
		}
		
	echo "No allowed"	;
		
	}
	
	
	



function UpdateGroup($gid){
	$newcn=$_GET["EditBranchValue"];
	$newcn=replace_accents($newcn);
	$ldap=new clladp();
	$hash=$ldap->GroupDatas($gid);	
	$dn=$hash["dn"];
	
	
	if(preg_match('#cn=([a-zA-Z0-9\.\-_\(\)\s]+)#',$dn,$reg)){
		$oldcn=$reg[1];
		
		if($oldcn<>$newcn){
			$ldap->ldap_group_rename($dn,"cn=$newcn");
			if($ldap->ldap_last_error<>null){echo "!Error";}else{echo $newcn;}
		}else{
			echo $newcn;return null;
			}
	}else {echo "!Error";}	
	
}

function UpdateUser($id){
	
	
	$DisplayName=$_GET["EditBranchValue"];
	$updateA["displayName"]=$DisplayName;
	$ldap=new clladp();
	
	$hash=$ldap->UserDatas($id);
	if(!is_array($hash)){echo "???";exit;}
	
	$ldap->Ldap_modify($hash["dn"],$updateA);
	if($ldap->ldap_last_error<>null){
		echo "!Error";
	}else{echo $DisplayName;}
}

function artica_ldap_settings(){
	$pages=new HtmlPages();
	echo $pages->PageRootArticaLdapSettings();
	
}
function save_ldap_settings(){
	
}
	
?>	