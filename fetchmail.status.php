<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.main_cf.inc');
	
	
$usersmenus=new usersMenus();
if($usersmenus->AsPostfixAdministrator==false){header('location:users.index.php');exit;}
$html=new HtmlPages();
$page="<div id='rightInfos'>" . PageFetchmail_status() . "</div>";
$tpl=new template_users('{fetchmail_status}',$page);
echo $tpl->web_page;


	function PageFetchmail_status(){
		include_once('ressources/class.fetchmail.inc');
		$tpl=new templates();
		$yum=new usersMenus();
		
		if($yum->AsMailBoxAdministrator==false){$html=$tpl->_ENGINE_parse_body("<h3>{not allowed}</H3>");return $html;}
		$status=new status(1);
		$stat=$status->fetchmail_satus();
		$html=$stat;
		
	
		
		
		if($usersmenus->AutomaticConfig==false){
			$html=$html."<br><fieldset><legend>{apply config}</legend>
					<center><input type='button' value='{apply config}' OnClick=\"javascript:TreeFetchMailApplyConfig()\"></center>
				</fieldset>";
			
		}
		
		$fetch=new fetchmail();
		if(is_array($fetch->array_servers)){
			
			$html=$html . "<fieldset>
				<legend>{servers_list}</legend>
				<center>
				<table style='width:90%;border:1px solid #CCCCCC'>
				<tr style='border-bottom:1px solid #CCCCCC'>
					<td colspan=2><strong>{servers_list}</td>
					<td align='center'><strong>{number_users}</strong></td>
				</tr>";
			while (list ($num, $val) = each ($fetch->array_servers) ){
					
					
					$html=$html . "<tr>
					<td width=1%><img src='img/webmin_on-22.gif'></td>
					<td><a href=\"javascript:TreeFetchmailShowServer('$num');\">$num</a></td>
					<td align='center'>" . count($val)."</td>
					</tr>";
			}
			
			$html=$html . "</table></center></fieldset>";
		}
		
		
		return $tpl->_ENGINE_parse_body("<div id=status>$html</div>");
		
	}
?>