<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.main_cf.inc');
	
	
$usersmenus=new usersMenus();
if($usersmenus->AsPostfixAdministrator==false){header('location:users.index.php');exit;}
$html=new HtmlPages();
$page=PageAveserverLicenceSection();
$tpl=new template_users('Kaspersky Anti-Virus {product_licence}',$page);
echo $tpl->web_page;


function PageAveserverLicenceSection(){
	$yum=new usersMenus();$tpl=new  templates();
	if($yum->AsPostfixAdministrator==false){return $tpl->_ENGINE_parse_body("<h3>{not allowed}</H3>");}	
	$lic=PageAveserverLicence() . "<br>" . PageAveServerLicenceExtraInfos();
	
	$html="
	<table style='width:100%'>
	<tr>
	<td width=1%' valign='top'><img src='img/bg_licence.jpg'>
	<TD>
	
	$lic<br><h4>{licence operations}</h4><center><input type='button' Onclick=\"javascript:s_PopUp('licencemanager.aveserver.php','550','550');\" value='&laquo;&nbsp;{add new licence}&nbsp;&raquo;'></center>
	</td>
	</tr>
	</table>";
	
	return $tpl->_ENGINE_parse_body($html);
	}
	
	function PageAveServerLicenceExtraInfos(){
		$yum=new usersMenus();$tpl=new  templates();
		if($yum->AsPostfixAdministrator==false){return $tpl->_ENGINE_parse_body("<h3>{not allowed}</H3>");}
		$sock=new sockets();
		$file=$sock->getfile("aveserver_licence_extra");
		$file=htmlentities($file);
		$table=explode("\n",$file);
		$html="<table style='width:100%'>";
		while (list ($num, $val) = each ($table) ){
			if(preg_match('#(.+)\:(.+)#',$val,$regs)){
				if($val<>null){$html=$html . "<tr><td nowrap align='right'><strong>{$regs[1]}:&nbsp;</strong><td style='border-bottom:1px solid #CCCCCC'>{$regs[2]}</td></tr>";}
			}
			
			
			
		}
		$html="<h4>{extra infos}</h4>$html</table>";
		return $tpl->_ENGINE_parse_body("$html");
	}
	function PageAveserverLicence(){
		$yum=new usersMenus();$tpl=new  templates();$tpl=new  templates();
		if($yum->AsPostfixAdministrator==false){return $tpl->_ENGINE_parse_body("<h3>{not allowed}</H3>");}
		$sock=new sockets();
		$file=$sock->getfile("aveserver_licence");
		$file=htmlentities($file);
		$table=explode("\n",$file);
		$html="<table style='width:100%'>";
		while (list ($num, $val) = each ($table) ){
			if(preg_match('#(.+)\:(.+)#',$val,$regs)){if($val<>null){$html=$html . "<tr><td nowrap align='right'><strong>{$regs[1]}:&nbsp;</strong><td style='border-bottom:1px solid #CCCCCC'>{$regs[2]}</td></tr>";}}
			if(preg_match('#Key file\s+(.+?)\.key#',$val,$regs)){$html=$html . "<tr><td nowrap align='right' colspan=2><input type='button' value='{delete} {$regs[1]}' OnClick=\"javascript:TreeAveServerLicenceDeleteKey('{$regs[1]}.key');\"></td></tr>";}			
			if(preg_match("#Serial#i",$val)){$html=$html . "<tr><td colspan=2 style='border-bottom:2px solid #CCCCCC'>&nbsp;</td></tr>";}
		}
		$html="<h4>{licence status}</h4>$html</table>";
		return $tpl->_ENGINE_parse_body("$html");
		
	}	