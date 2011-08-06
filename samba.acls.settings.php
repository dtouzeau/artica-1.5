<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.samba.inc');


	
	
	$user=new usersMenus();
	if($user->AsSambaAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}

	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["nt_acl_support"])){save();exit;}
	
js();
//fstablist

function js(){
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{ACLS_SUPPORT}","samba.index.php");
	$page=CurrentPageName();
	$html="
		function acls_settings_start(){
			YahooWin6('550','$page?popup=yes','$title');
		}
		
	   var x_acls_global_save=function (obj) {
			tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue);}
			acls_settings_start();
	    }			
		
		function acls_global_save(){
		var XHR = new XHRConnection();
		
		if(document.getElementById('nt acl support').checked){
			XHR.appendData('nt acl support','yes');}else{
			XHR.appendData('nt acl support','no');}
		
		
		if(document.getElementById('map acl inherit').checked){
			XHR.appendData('map acl inherit','yes');}else{
			XHR.appendData('map acl inherit','no');}
		

		if(document.getElementById('acl check permissions').checked){
			XHR.appendData('acl check permissions','yes');}else{
			XHR.appendData('acl check permissions','no');}
		
		
		if(document.getElementById('acl group control').checked){
			XHR.appendData('acl group control','yes');}else{
			XHR.appendData('acl group control','no');}
			
		if(document.getElementById('dos filemode').checked){
			XHR.appendData('dos filemode','yes');}else{
			XHR.appendData('dos filemode','no');}			
			
			
		

		if(document.getElementById('inherit permissions').checked){
			XHR.appendData('inherit permissions','yes');}else{
			XHR.appendData('inherit permissions','no');}
					
		if(document.getElementById('inherit acls').checked){
			XHR.appendData('inherit acls','yes');}else{
			XHR.appendData('inherit acls','no');}		
		
		document.getElementById('aclgeneral').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>'; 
		XHR.sendAndLoad('$page', 'GET',x_acls_global_save);
	}
		
	acls_settings_start();
	";
	echo $html;
}

function popup(){
	
	$smb=new samba();
	
	$html="
	<div class=explain>{ACLS_SUPPORT_EXPLAIN}</div>
	<hr>
	<div id='aclgeneral'>
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:12px'>{nt_acl_support}</td>
		<td>". Field_checkbox("nt acl support","yes",$smb->main_array["global"]["nt acl support"])."</td>
		<td>".help_icon("{nt_acl_support_text}")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:12px'>{acl_group_control}</td>
		<td>". Field_checkbox("acl group control","yes",$smb->main_array["global"]["acl group control"])."</td>
		<td>".help_icon("{acl_group_control_text}")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:12px'>{dos_filemode}</td>
		<td>". Field_checkbox("dos filemode","yes",$smb->main_array["global"]["dos filemode"])."</td>
		<td>".help_icon("{dos_filemode_text}")."</td>
	</tr>			
	<tr>
		<td class=legend style='font-size:12px'>{map_acl_inherit}</td>
		<td>". Field_checkbox("map acl inherit","yes",$smb->main_array["global"]["map acl inherit"])."</td>
		<td>".help_icon("{map_acl_inherit_text}")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:12px'>{acl_check_permissions}</td>
		<td>". Field_checkbox("acl check permissions","yes",$smb->main_array["global"]["acl check permissions"])."</td>
		<td>".help_icon("{acl_check_permissions_text}")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:12px'>{inherit_acls}</td>
		<td>". Field_checkbox("inherit acls","yes",$smb->main_array["global"]["inherit acls"])."</td>
		<td>".help_icon("{inherit_acls_text}")."</td>
	</tr>	

	<tr>
		<td class=legend style='font-size:12px'>{inherit_permissions}</td>
		<td>". Field_checkbox("inherit permissions","yes",$smb->main_array["global"]["inherit permissions"])."</td>
		<td>".help_icon("{inherit_permissions_text}")."</td>
	</tr>
	
	<tr>
		<td colspan=3 align='right'>
		<hr>
			". button("{apply}","acls_global_save()")."
		</td>
	</tr>
	</table>
	</div>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function save(){
	$samba=new samba();

$array["nt_acl_support"]="nt acl support";
$array["map_acl_inherit"]="map acl inherit";
$array["acl_check_permissions"]="acl check permissions";
$array["acl_group_control"]="acl group control";
$array["dos_filemode"]="dos filemode";

$array["inherit_permissions"]="inherit permissions";
$array["inherit_acls"]="inherit acls";
while (list ($key, $value) = each ($array) ){	
	$NEW_GET[$value]=$_GET[$key];
}
	
	

	while (list ($key, $value) = each ($NEW_GET) ){
		$samba->main_array["global"][$key]=$value;
	}
	$samba->SaveToLdap();
}
		

?>