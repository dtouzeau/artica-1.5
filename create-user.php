<?php
	session_start();
	if($_SESSION["uid"]==null){echo "window.location.href ='logoff.php';";die();}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.pure-ftpd.inc');
	include_once('ressources/class.user.inc');
	include_once('ressources/charts.php');
	include_once('ressources/class.mimedefang.inc');
	include_once('ressources/class.computers.inc');
	include_once('ressources/class.ini.inc');	
	

	if(isset($_GET["form"])){formulaire();exit;}
	if(isset($_GET["ch-groupid"])){groups_selected();exit;}
	if(isset($_GET["ch-domain"])){domain_selected();exit;}
	if(isset($_REQUEST["password"])){save();exit;}
	
	js();


$users=new usersMenus();
if(!$users->AllowAddUsers){die("alert('not allowed');");}
	
function js(){
$tpl=new templates();
$page=CurrentPageName();

$title=$tpl->_ENGINE_parse_body('{add user explain}');
$html="
var x_serid='';

function OpenAddUser(){
	YahooWin5('590','$page?form=yes','$title');
}

var x_ChangeFormValues= function (obj) {
	var tempvalue=obj.responseText;
	var internet_domain='';
	var ou=document.getElementById('organization').value;
	document.getElementById('select_groups').innerHTML=tempvalue;
	if(document.getElementById('internet_domain')){internet_domain=document.getElementById('internet_domain').value;}
  	 var XHR = new XHRConnection();
     	XHR.appendData('ou',ou);
		XHR.appendData('ch-domain',internet_domain);     	
        XHR.sendAndLoad('$page', 'GET',x_ChangeFormValues2);		
}


var x_SaveAddUser= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>3){
		alert(tempvalue);
		document.getElementById('ffform').innerHTML=\"<div style='width:100%;padding:15px'><center><img src='img/identity-add-96.png'></center></div>\";  
		return false;
	}
	YahooWin5Hide();
		if(document.getElementById('main_config_pptpd')){
			RefreshTab('main_config_pptpd');
			return;
		}
		
}

function SaveAddUser(){
  var gpid='';
  var internet_domain='';
  var ou=document.getElementById('organization').value;
  var email=document.getElementById('email').value;
  var firstname=document.getElementById('firstname').value;
  var lastname=document.getElementById('lastname').value;  
  var login=document.getElementById('login').value;
  var password=document.getElementById('password').value;
  x_serid=login;
  if(document.getElementById('groupid')){gpid=document.getElementById('groupid').value;}
  if(document.getElementById('internet_domain')){internet_domain=document.getElementById('internet_domain').value;}
  var EnableVirtualDomainsInMailBoxes=domain=document.getElementById('EnableVirtualDomainsInMailBoxes').value;
  if(EnableVirtualDomainsInMailBoxes==1){x_serid=email+'@'+internet_domain;}

  	 var XHR = new XHRConnection();
     XHR.appendData('ou',ou);
     XHR.appendData('internet_domain',internet_domain);
	 XHR.appendData('email',email);
     XHR.appendData('firstname',firstname);
     XHR.appendData('lastname',lastname);
     XHR.appendData('login',login);
     XHR.appendData('password',password);
     XHR.appendData('gpid',gpid);  
     AnimateDiv('ffform');                                    		      	
     XHR.sendAndLoad('$page', 'POST',x_SaveAddUser);		  
}



var x_ChangeFormValues2= function (obj) {
	var tempvalue=obj.responseText;
	var domain='';
	var email='';
	var login='';
	var ou=document.getElementById('organization').value;
	document.getElementById('select_domain').innerHTML=tempvalue;
	
	email=document.getElementById('email').value;
	login=document.getElementById('login').value;
	if(login.length==0){
		if(email.length>0){
			document.getElementById('login').value=email;
		}
	}
		
}
	

function ChangeFormValues(){
  var gpid='';
  var ou=document.getElementById('organization').value;

  if(document.getElementById('groupid')){gpid=document.getElementById('groupid').value;}
  		var XHR = new XHRConnection();
        XHR.appendData('ch-groupid',gpid);
        XHR.appendData('ou',ou);
        XHR.sendAndLoad('$page', 'GET',x_ChangeFormValues);	

}



OpenAddUser();";
echo $html;
}

function groups_selected(){
	$ldap=new clladp();
	$hash_groups=$ldap->hash_groups($_GET["ou"],1);
	$groups=Field_array_Hash($hash_groups,'groupid',$_GET["ch-groupid"],null,null,0,"font-size:14px;padding:3px");
	echo $groups;
	
}

function domain_selected(){
		$ldap=new clladp();
	$hash_domains=$ldap->hash_get_domains_ou($_GET["ou"]);
	$domains=Field_array_Hash($hash_domains,'internet_domain',$_GET["ch-domain"],null,null,0,"font-size:14px;padding:3px");
	echo $domains;
	
}

function formulaire(){
	$users=new usersMenus();
	$ldap=new clladp();
	$tpl=new templates();
	$page=CurrentPageName();	
	if($users->AsAnAdministratorGeneric){
		$hash=$ldap->hash_get_ou(false);
	}else{
		$hash=$ldap->Hash_Get_ou_from_users($_SESSION["uid"],1);
		
	}
	
	if(count($hash)==1){
		$org=$hash[0];
		$hash_groups=$ldap->hash_groups($org,1);
		$hash_domains=$ldap->hash_get_domains_ou($org);
		$groups=Field_array_Hash($hash_groups,'groupid',null,null,null,0,"font-size:14px;padding:3px");
		$domains=Field_array_Hash($hash_domains,'domain',null,null,null,0,"font-size:14px;padding:3px");
	}
	
	
	$artica=new artica_general();
	$EnableVirtualDomainsInMailBoxes=$artica->EnableVirtualDomainsInMailBoxes;	
	
	
	while (list ($num, $ligne) = each ($hash) ){
		$ous[$ligne]=$ligne;
	}
	
	$ou=Field_array_Hash($ous,'organization',null,"ChangeFormValues()",null,0,"font-size:14px;padding:3px");
	$form="
	
	<input type='hidden' id='EnableVirtualDomainsInMailBoxes' value='$EnableVirtualDomainsInMailBoxes'>
	<table style='width:100%' class=form>
		<tr>
			<td class=legend style='font-size:14px'>{organization}:</td>
			<td>$ou</td>
		</tr>
		<tr>
			<td class=legend style='font-size:14px'>{group}:</td>
			<td><span id='select_groups'>$groups</span>
		</tr>
		<tr>
		<tr>
			<td class=legend style='font-size:14px'>{firstname}:</td>
			<td>" . Field_text('firstname',null,'width:120px;font-size:14px;padding:3px',null,'ChangeFormValues()')."</td>
		</tr>		
		<tr>
			<td class=legend style='font-size:14px'>{lastname}:</td>
			<td>" . Field_text('lastname',null,'width:120px;font-size:14px;padding:3px',null,"ChangeFormValues()")."</td>
		</tr>		
			
		<tr>
			<td class=legend style='font-size:14px'>{email}:</td>
			<td>" . Field_text('email',null,'width:120px;font-size:14px;padding:3px',null,"ChangeFormValues()")."@<span id='select_domain'>$domains</span></td>
		</tr>
		<tr>
			<td class=legend style='font-size:14px'>{uid}:</td>
			<td>" . Field_text('login',null,'width:120px;font-size:14px;padding:3px')."</td>
		</tr>
		<tr>
			<td class=legend style='font-size:14px'>{password}:</td>
			<td>" .Field_password('password',null,"font-size:14px;padding:3px")."</td>
		</tr>	
		<tr><td colspan=2>&nbsp;</td></tr>
		<tr>
			<td colspan=2 align='right'><hr>". button("{add}","SaveAddUser()")."
				
			</td>
		</tr>
		
		</table>
	";
			
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top' width=1%><div id='ffform'><img src='img/identity-add-96.png'></div></td>
		<td valign='top' width=99%><div>$form</div></td>
	</tr>
	</table>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}


function save(){
	$tpl=new templates();     
     $users=new user($_GET["login"]);
     if($users->password<>null){
     	writelogs("User already exists {$_GET["login"]} ",__FUNCTION__,__FILE__);
     	echo($tpl->_ENGINE_parse_body('{account_already_exists}'));
     	exit;
     }
     $ou=$_REQUEST["ou"];
     $password=$_REQUEST["password"];
     writelogs("Add new user {$_REQUEST["login"]} {$_REQUEST["ou"]} {$_REQUEST["gpid"]}",__FUNCTION__,__FILE__);
     $users->ou=$_REQUEST["ou"];
     $users->password=$_REQUEST["password"];
     $users->mail="{$_REQUEST["email"]}@{$_REQUEST["internet_domain"]}";    
     $users->DisplayName="{$_REQUEST["firstname"]} {$_REQUEST["lastname"]}";
     $users->givenName=$_REQUEST["firstname"];
     $users->sn=$_REQUEST["lastname"];
     $users->group_id=$_REQUEST["gpid"];
     
	if(is_numeric($_REQUEST["gpid"])){
		$gp=new groups($_REQUEST["gpid"]);
		writelogs( "privileges: {$_REQUEST["gpid"]} -> AsComplexPassword = \"{$gp->Privileges_array["AsComplexPassword"]}\"", __FUNCTION__, __FILE__, __LINE__ );
		if($gp->Privileges_array["AsComplexPassword"]=="yes"){
			$ldap=new clladp();		
			$hash=$ldap->OUDatas($ou);	
			$privs=$ldap->_ParsePrivieleges($hash["ArticaGroupPrivileges"],array(),true);
			$policiespwd=unserialize(base64_decode($privs["PasswdPolicy"]));
			if(is_array($policiespwd)){
				$priv=new privileges();
				if(!$priv->PolicyPassword($password,$policiespwd)){return false;}
			}
		}
		
		return false;
	}     
     
     
	 $users->add_user();
    
}


?>