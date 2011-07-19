<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.main_cf.inc');
	include_once('ressources/class.artica.inc');
	

	
$usersmenus=new usersMenus();
$artica=new artica_general();
if($usersmenus->AllowChangeKas==false){header('location:users.index.php');exit;}
if($artica->EnableGroups=='no'){SingleGroup();exit;}


$html=new HtmlPages();



if($usersmenus->AsPostfixAdministrator==true OR $usersmenus->AsArticaAdministrator==true){
	$ldap=new clladp();
	$h=$ldap->HashAllGroupsForListBox();
	
}else{$h=$usersmenus->groupid;$h[0]="[{select}]";}
	$select=Field_array_Hash($h,'gid',0,'KasAdminSelectGroup()',null,0,'width:100%');
	$select_group="
	<script>
		function KasAdminSelectGroup(){
			var gid=document.getElementById('gid').value;
			if(gid==-100){return false;}
			if(gid==0){return false;}
			LoadKavTab(0,gid);
			}
	</script>
	<table style=width:100%;margin-bottom:10px' >
	<tr>
	<td align=right width=30%><strong>{select_group}:</strong></td>
	<td align=left>$select</td>
	</tr>
	</table>
	
	
	";


$page="
$select_group
<script type=\"text/javascript\" language=\"javascript\" src=\"js/users.kav.php.js\"></script>
<div id='rightInfos'><h5>{select_group_text}</H5></div>";
$tpl=new template_users('{antivirus_group_rules}',$page);
echo $tpl->web_page;


function SingleGroup(){
	$ldap=new clladp();
	$gid=$ldap->ArticaDefaultGroupGid();
$page="
$select_group
<script type=\"text/javascript\" language=\"javascript\" src=\"js/users.kav.php.js\"></script>
<div id='rightInfos'></div>
<script>LoadKavTab(0,'$gid');</script>";	
	
$tpl=new template_users('{antivirus_settings}',$page);
echo $tpl->web_page;	
	
	
}