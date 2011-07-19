<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
$user=new usersMenus();
if($user->AllowEditOuSecurity==false){header('location:users.index.php');}	
if(isset($_GET["ArticaMaxDayQuarantine"])){SaveSettings();exit;}
if(isset($_GET["template"])){Loadtemplate();exit;}
INDEX();


function INDEX(){
	$ldap=new clladp();
	$page=CurrentPageName();
	$hash=$ldap->OUDatas($_GET["ou"]);
	$hashD[1]="1 {day}";
	for($i=2;$i<91;$i++){
		$hashD[$i]="$i {days}";
	}
	$ArticaMaxDayQuarantine=Field_array_Hash($hashD,'ArticaMaxDayQuarantine',$hash["ArticaMaxDayQuarantine"]);
	$html="
		<input type='hidden' name='close_form' id='close_form' value='{close_form}'>
	<form name='FFMQ'>

	<input type='hidden' name='ou' id='ou' value='{$_GET["ou"]}'>
	" . RoundedLightGrey("<H5>{quarantine_max_day}</H5>
	<table style='width:100%'>
	<tr>
	<td align='left' class=caption>{quarantine_max_day_text}</td>
	<td>$ArticaMaxDayQuarantine</td>
	</tr>
	<tr>
	<td colspan=2 align='right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('FFMQ','$page',true);\"></td>
	</table>") ."
	</form><br>
	<div id=\"ButtonCloseIframe\" style='text-align:right'></div>
	<iframe id='template_forms' scrolling=\"no\" marginwidth=\"0\" marginheight=\"0\" frameborder=\"0\" vspace=\"0\" hspace=\"0\" style=\"overflow:visible; width:100%; display:none\">
	</iframe>" . TemplatesForms();
	
	
$cfg["JS"][]="js/quarantine.ou.js";
$tpl=new template_users('{global_quarantine_rules}',$html,0,0,0,0,$cfg);
echo $tpl->web_page;	
	
	
}
function SaveSettings(){
	$ou=$_GET["ou"];
	$ldap=new clladp();
	$dn="ou=$ou,dc=organizations,$ldap->suffix";
	$upd["ArticaMaxDayQuarantine"][0]=$_GET["ArticaMaxDayQuarantine"];
	if(!$ldap->Ldap_modify($dn,$upd)){
		echo $ldap->ldap_last_error;
		exit;
	}else{$tpl=new templates();echo $tpl->_ENGINE_parse_body('{success}');}
	
	
	
	
}


function TemplatesForms(){
	
//ArticaReportQuarantineTemplate
	
	
	$messages_deleted=RoundedLightGrey("<table style='width:100%'>
	<tr>
	<td valign='top'>". imgtootltip('icon_settings-64.png','{edit}',"LoadQuarantineTemplate('ArticaMaxDayTemplate')")."</td>
	<td valign='top'>
		<H5>{template_deleted}</h5>
		{template_deleted_text}
	
	</td>
	</tr>
	</table>",null,1);
	
	$messages_report=RoundedLightGrey("<table style='width:100%'>
	<tr>
	<td valign='top'>". imgtootltip('icon_settings-64.png','{edit}',"LoadQuarantineTemplate('ArticaReportQuarantineTemplate')")."</td>
	<td valign='top'>
		<H5>{ArticaReportQuarantineTemplate}</h5>
		{ArticaReportQuarantineTemplate_text}
	
	</td>
	</tr>
	</table>",null,1);	
	
	
	$intro="<H4>{templates}</h4>
	<p>{templates_explain}</p>
	<table style='width:100%'>
	<tr>
	<td width=50% valign='top'>$messages_deleted</td>
	<td width=50% valign='top'>$messages_report</td>
	</tr>
	</table>
	
	";	
	
	return "<br>" . $intro;
	
	
}

function Loadtemplate(){
	
	$ou=$_GET["ou"];
	$template_name=$_GET["template"];
	writequeries();
	
	
	
	
	$ldap=new clladp();
	$dn="cn=artica_quarantine_settings,ou=$ou,dc=organizations,$ldap->suffix";
	
	
	if(isset($_GET["ArticaMaxDayTemplate"])){
		$template_data="<SUBJECT>{$_GET["subject"]}</SUBJECT>
		<FROM>{$_GET["from"]}</FROM>
		<TEMPLATE>{$_GET["ArticaMaxDayTemplate"]}</TEMPLATE>";
		$upd[$template_name][0]=$template_data;
		if(!$ldap->Ldap_modify($dn,$upd)){echo "<H2>$ldap->ldap_last_error</H2>";exit;}
		
	}
	
	
	if(!$ldap->ExistsDN($dn)){
		$upd["cn"][]="artica_quarantine_settings";
		$upd["objectClass"][]='top';
		$upd["objectClass"][]='ArticaQuarantineTemplates';		
		$upd["$template_name"][]="DEFAULT";
		$ldap->ldap_add($dn,$upd);
		$template_data="DEFAULT";
		}
	else{
		
		
		$hash=$ldap->Ldap_read($dn,'(ObjectClass=ArticaQuarantineTemplates)',array(strtolower($template_name)));
		if(!is_array($hash[0][strtolower($template_name)])){
			unset($upd);
			$upd[$template_name]="DEFAULT";
			$ldap->Ldap_add_mod($dn,$upd);
			$hash=$ldap->Ldap_read($dn,'(ObjectClass=ArticaQuarantineTemplates)',array(strtolower($template_name)));
		}
		
		$template_data=$hash[0][strtolower($template_name)][0];
		
	}
	
	
	if($template_data=="DEFAULT"){
		$template_data=file_get_contents("ressources/databases/$template_name.cf");
		}
		
	if(preg_match('#<SUBJECT>(.+?)</SUBJECT>\s+<FROM>(.+?)</FROM>\s+<TEMPLATE>(.+?)</TEMPLATE>#is',$template_data,$reg)){
		$subject=$reg[1];
		$from=$reg[2];
		$template_d=$reg[3];
	}
		
	$tiny=TinyMce('ArticaMaxDayTemplate',$template_d);
	$html="
	<html>
	<head>
	<link href='css/styles_main.css' rel=\"styleSheet\" type='text/css' />
	<link href='css/styles_header.css' rel=\"styleSheet\" type='text/css' />
	<link href='css/styles_middle.css' rel=\"styleSheet\" type='text/css' />
	<link href='css/styles_forms.css' rel=\"styleSheet\" type='text/css' />
	<link href='css/styles_tables.css' rel=\"styleSheet\" type='text/css' />
	<script type='text/javascript' language='JavaScript' src='mouse.js'></script>
	<script type='text/javascript' language='javascript' src='XHRConnection.js'></script>
	<script type='text/javascript' language='javascript' src='default.js'></script>
	<script type='text/javascript' language='javascript' src='js/quarantine.ou.js'></script>	
	</head>
	<body width=100% style='background-color:white'> 
		<H5>{"."$template_name}</H5>
	<form name='FFM1'>
	<table style='width:100%;margin:10px'>
	<tr>
	<td align='right'><strong>{from}:</strong></td>
	<td><input type='text' name='from' value='$from'></td>
	</tr>
	<tr>
	<td align='right'><strong>{subject}:</strong></td>
	<td><input type='text' name='subject' value='$subject'></td>
	</tr>	
	</table>
	<div style='width:450px'>$tiny</div>
	<p class=caption>{template_token}</p>
	<input type='hidden' name='ou' value='$ou'>
	<input type='hidden' name='template' value='$template_name'>
	</form>
	</body>
	</html>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}



