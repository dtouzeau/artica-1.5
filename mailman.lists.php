<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.mailman.inc');
	
$usersmenus=new usersMenus();
if(isset($_GET["LoadLists"])){mailman_lists();exit;}	

page();	
function page(){



$page=CurrentPageName();
//mailman-add.png
$gb=bouton_globalsettings();
$html="
<input type='hidden' id='ou' value='{$_GET["ou"]}'>
<input type='hidden' id='add_mailman_prompt' value='{add_mailman_prompt}'>
<input type='hidden' id='MailManListAdminPassword_text' value='{MailManListAdminPassword_text}'>
<input type='hidden' id='MailManListAdministrator_text' value='{MailManListAdministrator_text}'>
<input type='hidden' id='are_you_sure_to_delete' value='{are_you_sure_to_delete}'>
<table style=width:100%'>
<tr>
	
	<td valign='top' width=60%>
		<H5>{mailman_lists}</H5>
			<div id=mailman_lists></div>
			<div style='text-align:right'>" . imgtootltip('icon_refresh-20.gif','{refresh}',"LoadAjax('mailman_lists','$page?LoadLists=yes');") . "</div>
	</td>
	<td valign='top'>".bouton_add().applysettings("mailman").$gb .bouton_gourl()."</td>
	
</tr>

</table>




<script>LoadAjax('mailman_lists','$page?LoadLists=yes&ou={$_GET[ou]}');</script>
";

$JS["JS"][]="js/mailman.js";
$tpl=new template_users('{manage_mailman_lists} (in construction don\'t use)',$html,0,0,0,0,$JS);
echo $tpl->web_page;
}


function bouton_add(){
	$html="<table style='width:100%'>
	<tr>
		<td valign='top'>" . imgtootltip('mailman-add.png','{add_mailman_text}',"mailman_add_newlist();")."</td>
		<td valign='top'><H5>{add_mailman}</H5>{add_mailman_text}</td>
	</tr>
	</table>
	
	";
	$tpl=new templates();
	
	return $tpl->_ENGINE_parse_body(RoundedLightGrey($html)) . "<br>";
	
}

function bouton_globalsettings(){
	
	$user=new usersMenus();
	if ($user->AsPostfixAdministrator==false){return null;}
	
$html="<table style='width:100%'>
	<tr>
		<td valign='top'>" . imgtootltip('global-settings.png','{global_settings}',"LoadMailmanGlobalSettings();")."</td>
		<td valign='top'><H5>{global_settings}</H5>{global_settings_text}</td>
	</tr>
	</table>
	
	";
	$tpl=new templates();
	
	return $tpl->_ENGINE_parse_body(RoundedLightGrey($html)) . "<br>";
		
	
	
}

function bouton_gourl(){
	
	$mailman=new mailman();
	$url=$mailman->main_default_array["MailManDefaultUrlPattern"] . "listinfo";
	$url=str_replace('%(hostname)s',$mailman->main_default_array["MailManDefaultUrlHost"],$url);
	$url=str_replace('%s',$_SERVER['SERVER_NAME'],$url);
	
$html="<table style='width:100%'>
	<tr>
		<td valign='top'>" . imgtootltip('web-site.png','{global_settings}',"MyHref('$url');")."</td>
		<td valign='top'><H5>{mailman_web_site}</H5>{mailman_web_site_text}</td>
	</tr>
	</table>
	
	";
	$tpl=new templates();
	
	return $tpl->_ENGINE_parse_body(RoundedLightGrey($html)) . "<br>";
		
	
	
}








function mailman_lists(){
	$ldap=new clladp();
	
	$user=new usersMenus();
	if ($user->AsPostfixAdministrator==false){
	$filter="(&(Objectclass=ArticaMailManClass)(cn=*)(mailmanouowner={$_GET["ou"]}))";}
	else{
		$filter="(&(Objectclass=ArticaMailManClass)(cn=*))";}
	
	$sr = @ldap_search($ldap->ldap_connection,"cn=mailman,cn=artica,$ldap->suffix",$filter,array("mailmanouowner","cn","MailmanListOperation"));
	if($sr){
		$html="<table style=width:100%'>
		<tr style='background-color:#CCCCCC'>
		<td>&nbsp;</td>
		<td><strong>{name}</strong></td>
		<td><strong>{organization}</strong></td>
		<td><strong>{address}</strong></td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		</tr>
		";
		
		
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);
			if($hash["count"]>0){	
			//	print_r($hash);
				for($i=0;$i<$hash["count"];$i++){
					$mailmanouowner=$hash[$i]["mailmanouowner"][0];
					$name=$hash[$i]["cn"][0];
					$MailmanListOperation=$hash[$i][strtolower("MailmanListOperation")][0];
					
					$delete="<td width=1%>" . imgtootltip('x.gif','{delete}',"mailman_delete_list('$name');") . "</td>";
					
					if($mailmanouowner<>"undefined"){
						$address="<td width=1% align='center'>" . imgtootltip('outicon_1002.gif',"$name {address}","mailman_addresses('$name')")."</td>";
					}else{
						$address="<td width=1% align='center'>" . imgtootltip('icon_mini_off.gif',"{error_no_ou_saved}","")."</td>";
					}
					
					$ico="status_ok.gif";
					$cell=CellRollOver("LoadMailmanListSettings('$name')");
					
					if($MailmanListOperation=="ADD"){
						$ico="status_warning.gif";
						$cell=CellRollOver("mailman_applysettings('$name')",'{mailman_waiting_replication}');
					}
					
					if($MailmanListOperation=="DEL"){
						$ico="status_warning.gif";
						$cell=CellRollOver("mailman_applysettings('$name')",'{mailman_waiting_replication}');
						$style="style='color:#CCCCCC'";
					}
										
					$html=$html . "
					<tr>
						<td width=1%><img src='img/$ico'></td>
						<td $cell><strong $style>$name</strong></td>
						<td $cell><strong $style>$mailmanouowner</strong></td>
						$address
						$delete
						<td width=1% align='center'>" . imgtootltip('icon_sync.gif',"$name {apply_settings}","mailman_applysettings('$name')")."</td>
					</tr>					
					
					";
					
					
				}
			}
			
$html=$html . "</table>";}	

		
if($html){
		$html=RoundedLightGrey($html);
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body($html);

}
	
	
}
	