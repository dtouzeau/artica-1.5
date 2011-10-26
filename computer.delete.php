<?php
session_start ();
include_once ('ressources/class.templates.inc');
include_once ('ressources/class.ldap.inc');
include_once ('ressources/class.users.menus.inc');



if(!Isright()){$tpl=new templates();echo "alert('".$tpl->javascript_parse_text('{ERROR_NO_PRIVS}')."');";die();}


js();



function js(){
	$uid=$_GET["uid"];
	
$html="	
var x_DeleteComputer= function (obj) {
	var results=obj.responseText;
	if (results.length>0){alert(results);}
	YahooUserHide();
	
	if(document.getElementById('computerlist')){BrowsComputersRefresh();}
	if(document.getElementById('DnsZoneName')){BindComputers(document.getElementById('DnsZoneName').value)}	
	if(document.getElementById('bind9_hosts_list')){BindRefresh();}
	if(document.getElementById('main-content')){Loadjs('start.php');}
	if(document.getElementById('main_config_dhcpd')){RefreshTab('main_config_dhcpd');}	
	
	
}
	
function DeleteComputer(uid){
	var XHR = new XHRConnection();
	XHR.appendData('DeleteComputer',uid);
	XHR.appendData('echo','yes');
	XHR.sendAndLoad('domains.edit.user.php', 'GET',x_DeleteComputer);
	}	
	

DeleteComputer('$uid');
";

echo $html;

}
function IsRight(){
	if(!isset($_GET["uid"])){return false;}
	$users=new usersMenus();
	if($users->AsArticaAdministrator){return true;}
	if($users->AsInventoryAdmin){return true;}
	if($users->AsSambaAdministrator){return true;}
	if($users->AllowAddUsers){return true;}
	if($users->AllowManageOwnComputers){return true;}
	return false;
	}
?>