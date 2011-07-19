<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.main_cf.inc');
	
	
$usersmenus=new usersMenus();
if($usersmenus->AsMailBoxAdministrator==false){header('location:users.index.php');exit;}

if(isset($_GET["js"])){js();exit;}
if(isset($_GET["popup"])){page_popup();exit;}
if(isset($_GET["postfix_security_rules"])){echo postfix_security_rules();exit;}


function js(){
$usersmenus=new usersMenus();	
if($usersmenus->AsPostfixAdministrator==false){echo "alert('no privileges');";die();}
$page=CurrentPageName();
$addons=file_get_contents("postfix.js");
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{security_rules}');
$html="
	$addons
	function LoadPSFirstPage(){
		YahooWinT('700','$page?popup=yes','$title');
	
	}
	
	LoadPSFirstPage();";
	
	echo $html;	
	
	
}

function page_popup(){
	
	$html="
	<H1>{security_rules}</H1>
	<div id='postfix_security_rules'>" . RoundedLightWhite(postfix_security_rules()) . "</div>
	
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

page();	
function page(){
	$main=new main_cf();
	
	
	
	$html="
	<script type=\"text/javascript\" language=\"javascript\" src=\"postfix.js\"></script>
	<div id='postfix_security_rules'>" . RoundedLightGrey(postfix_security_rules()) . "</div>";
	
	
	
	$tpl=new template_users('{security_rules}',$html);
	echo $tpl->web_page;
	}
	
	
function postfix_security_rules(){
	
	$html="<table width=100%>
	<tr>
	<td colspan=3><p>{security_rules_explain}</p></td>
	</tr>
	
	
	
	" . Listen($main).array_mynetworks($main).TLS().
	smtpd_reject_unlisted_recipient().
	smtpd_helo_required().restrictions_classes().
	RegexRules().
	Antispam().
	Antivirus().
	procmail()."
	</table>";
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
}


function restrictions_classes(){
	$html="<input type='button' value='{postfix_restrictions_classes}&nbsp;&raquo;' OnClick=\"javascript:MyHref('postfix.restrictions.classes.php');\">";
	
return BuildTable("icon_info.gif",'{postfix_restrictions_classes}',$html);	
	
}

function procmail(){

	
}

function TLS(){
	$main=new main_cf();
	$config="javascript:Loadjs('postfix.tls.php');";
	if($main->main_array["smtpd_tls_security_level"]<>'none'){
		
		if($main->main_array["smtp_tls_note_starttls_offer"]=="yes"){
			$smtp_tls_note_starttls_offer="{logging}:&nbsp;{smtp_tls_note_starttls_offer} ({enabled})";
			}else{$smtp_tls_note_starttls_offer="{logging}:&nbsp;{smtp_tls_note_starttls_offer} ({disabled})";}
		$html="<table  style='width:100%;'>
		
		
		<tr>
		<td width=1% valign='top'><img src='img/fw_bold.gif'></td>
		<td style='padding-left:5px'><a href=\"$config\">smtpd_tls_security_level&nbsp;=&nbsp;{$main->main_array["smtpd_tls_security_level"]}</a></td>
		</tr
		<tr>
		<td width=1% valign='top'><img src='img/fw_bold.gif'></td>
		<td style='padding-left:5px'><a href=\"$config\">{logging}:&nbsp;" . $main->array_field_tls_logging[$main->main_array["smtpd_tls_loglevel"]] ."</a></td>
		</tr>
		<tr>
		<td width=1% valign='top'><img src='img/fw_bold.gif'></td>
		<td style='padding-left:5px'><a href=\"$config\">$smtp_tls_note_starttls_offer</a></td>
		</tr>
		</table>";
		return BuildTable("status_ok.gif",'{tls_title}',$html);
	}else{
		return BuildTable("icon_info.gif",'{tls_title}',"<a href='$config'>{disable}</a>");
	}
	
	
}


function Antispam(){
	$p=new HtmlPages();
	if($p->kas_installed==true){
		return BuildTable("status_ok.gif",'Kaspersky Anti-Spam ','{installed}');
	}else{return BuildTable("status_critical.gif",'Kaspersky Anti-Spam ','{not_installed}');}
	}
	
function Antivirus(){
	$p=new HtmlPages();
	if($p->aveserver_installed==true){
		return BuildTable("status_ok.gif",'Kaspersky Antivirus ','{installed}');
	}else{return BuildTable("status_critical.gif",'Kaspersky Antivirus ','{not_installed}');}
	}	



function RegexRules(){
	include_once('ressources/class.main_cf_filtering.inc');
	$main=new main_header_check();
	$count=count($main->main_table);
	$html="<a href='smtp.rules.php'>{postfix_inrules_regex}: <strong>$count</strong></a>";
	return BuildTable("status_ok.gif",'{check_in_headers}',$html,"MyHref('smtp.rules.php');");			
}

function Listen(){
	$main=new main_cf();
	$array=$main->array_inet_interfaces;
	
if(!is_array($array)){
		writelogs("Error it seems that array_inet_interfaces is empty !!?? ",__FUNCTION__,__FILE__);
		$array_mynetworks_text='{error}';
		$array_mynetworks_img='status_critical.gif';
	}else{
		$array_mynetworks_img='status_ok.gif';
		$array_mynetworks_text=implode(', ',$array);
		
	
		}
	return BuildTable($array_mynetworks_img,'{daemon_listen_ip}',$array_mynetworks_text);		
}

function array_mynetworks(){
	$main=new main_cf();
	$array_mynetworks=$main->array_mynetworks;
	if(!is_array($array_mynetworks)){
		$array_mynetworks_text='{error_no_array_mynetworks}';
		$array_mynetworks_img='status_critical.gif';
	}else{
		$array_mynetworks_img='status_ok.gif';
		$array_mynetworks_text=implode(', ',$array_mynetworks) . '<span class=caption> {Authorized networks text}</span>';
		
	
		}
	return BuildTable($array_mynetworks_img,'{Authorized networks}',$array_mynetworks_text);	
	
}

function BuildTable($img,$title,$text,$link=null){
	
	if($link<>null){
		$link=imgtootltip('edit.gif','{edit}',$link);
	}
	$style="style='border-bottom:1px dotted #8E8785;'";
	return "<tr>
	<td $style width=1% valign='top'><img src='img/arrow-down-18.gif'></td>
	<td $style nowrap align='right' style='padding-left:4px' valign='top'><strong>$title</strong>:</td>
	<td $style valign='top'>
	<table class=none>
		<tr>
		<td width=1% valign='top'><img src='img/$img'></td>
		<td valign='top'>$text</td>
		</tr>
	</table>
	<td width=1%' valign='top'>$link</td>
	</td>
	</tr>";
	
}

function smtpd_reject_unlisted_recipient(){
	$main=new main_cf();
	if($main->main_array["smtpd_reject_unlisted_recipient"]=='yes'){
		$img="icon_ok.gif";
		$img2="status_ok.gif";
		$text="{enabled}";
	}else{
		$img="icon_mini_off.gif";
		$img2="status_critical.gif";
		$text="{disabled}";
		}
		
		$text="
		<table style='width:100%;' >
			<tr>
			<td width=1% valign='top'><img src='img/fw_bold.gif'>
			<td style='padding-left:5px'><img src='img/$img'>
			<td width=99%><strong>$text</strong></td>
			</tr>
		</table>";
			
return BuildTable("$img2",'{smtpd_reject_unlisted_recipient}',$text,"LoadSmtpdRejectUnlistedRecipient();");
	
}

function smtpd_helo_required(){
	$main=new main_cf();
	if($main->main_array["smtpd_helo_required"]=='yes'){
		$img="icon_ok.gif";
		$img2="status_ok.gif";
		$text="{enabled}";
	}else{
		$img="icon_mini_off.gif";
		$img2="status_critical.gif";
		$text="{disabled}";
		}
		
		$text="
		<table style='width:100%;' >
			<tr>
			<td width=1% valign='top'><img src='img/fw_bold.gif'>
			<td style='padding-left:5px'><img src='img/$img'>
			<td width=99%><strong>$text</strong></td>
			</tr>
		</table>";
			
return BuildTable("$img2",'{smtpd_helo_required}',$text,"LoadSmtpdHeloRequired();");	
	
	
}
