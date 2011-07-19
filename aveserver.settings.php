<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.main_cf.inc');
	include_once('ressources/kav4mailservers.inc');
	
$usersmenus=new usersMenus();
if($usersmenus->AsPostfixAdministrator==false){header('location:users.index.php');exit;}
if(isset($_GET["NotifyFromAddress"])){SaveContent();}

INDEX();


function INDEX(){
	
$ave=new kav4mailservers_single($_GET["ou"]);
$html="
<table style='width:100%'>
<tr>
<td width=1%><img src='img/tank.jpg'></td>
<td>
<form name='ffm1' method=get>
<input type='hidden' name='ou' value='{$_GET["ou"]}'>
<div class='caption'>{aveserver_intro_global}</div>
<table style='width:100%'>

		<tr><td colspan=2><h4>{when_found_virus}</H4></td></tr>
		<tr>
			<td align='right' nowrap><strong>{delete_mail}:</strong></td>
			<td>" . Field_numeric_checkbox_img('DeleteDetectedVirus',$ave->main_array["DeleteDetectedVirus"],'{enable_disable}')  . "</td>
		</tr>	
		<tr>
			<td align='right' nowrap><strong>{ArchiveMail}:</strong></td>
			<td>" . Field_numeric_checkbox_img('ArchiveMail',$ave->main_array["ArchiveMail"],'{enable_disable}')  . "</td>
		</tr>			
		<tr>
			<td align='right' nowrap><strong>{NotifyFrom}:</strong></td>
			<td>" . Field_numeric_checkbox_img('NotifyFrom',$ave->main_array["NotifyFrom"],'{enable_disable}')  . "</td>
		</tr>	
		<tr>
			<td align='right' nowrap><strong>{NotifyTo}:</strong></td>
			<td>" . Field_numeric_checkbox_img('NotifyTo',$ave->main_array["NotifyTo"],'{enable_disable}')  . "</td>
		</tr>
			
</table>


</td>
</tr>
<tr>
<td colspan=2>
<table style='width:100%'>
<tr><td colspan=2><h4>{NotifyMessage}</H4></td></tr>	
		<tr>
			<td align='right' nowrap><strong>{NotifyFromAddress}:</strong></td>
			<td>" . Field_text('NotifyFromAddress',$ave->main_array["NotifyFromAddress"])  . "</td>
		</tr>
		<tr>
			<td align='right' nowrap><strong>{NotifyMessageSubject}:</strong></td>
			<td>" .Field_text('NotifyMessageSubject',$ave->main_array["NotifyMessageSubject"])  . "</td>
		</tr>		
		<tr><td colspan=2>" . TinyMce('NotifyMessageTemplate',$ave->main_array["NotifyMessageTemplate"]) . "</td></tr>					
		<tr><td colspan=2 align='right'><input type='submit' value='{edit}&nbsp;&raquo;'></td></tr>
</table>
</td>
</tr>
</table></form>";	

$tpl=new template_users('{antivirus_engine}',$html);
echo $tpl->web_page;
	
	
}
function SaveContent(){
	$ave=new kav4mailservers_single($_GET["ou"]);
	
	

	while (list ($key, $value) = each ($_GET) ){
		$value=str_replace("\n"," ",$value);
		$ave->main_array[$key]=$value;
		
		
	}
	
	
	$ave->SaveConf();
	
}


