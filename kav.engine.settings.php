<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.main_cf.inc');
	include_once('ressources/kav4mailservers.inc');
	
$usersmenus=new usersMenus();
if($usersmenus->AsPostfixAdministrator==false){header('location:users.index.php');exit;}

if(isset($_GET["tab"])){echo EchoTab();exit;}
if(isset($_GET["KEY"])){echo SaveFunctions();exit;}
$html=new HtmlPages();
$ave=new kav4mailservers(1);



$cur=CurrentPageName();
$page="
<script>
function GeneralSettingsTab(num){
var XHR = new XHRConnection();
	XHR.appendData('tab',num);
	XHR.setRefreshArea('rightInfos');
	XHR.sendAndLoad('$cur', 'GET');	

}
</script>


<div id='rightInfos'>" .GeneralSettings() . "</div>";
$tpl=new template_users('{antivirus_engine}',$page);
echo $tpl->web_page;

function Tabs(){
	
	$array[0]='{general_settings}';
	$array[1]='{report_settings}';
	$array[2]='{read_conf}';
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["tab"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "
		<li>\n\t<a href=\"javascript:GeneralSettingsTab('$num');\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist style='margin-bottom:10px'>$html</div>\n";	
	
}


function read_conf(){
	$tpl=new Templates(null,null,null,1);		
$ave=new kav4mailservers(1,null,1);
$ave->file_data=str_replace("\n\n","\n",$ave->file_data);
$ave->file_data=htmlentities($ave->file_data);
$ave->file_data=nl2br($ave->file_data);
$html=Tabs()."
<fieldset><legend>{read_conf}</legend>
<div style='padding:10px'><code>$ave->file_data</code></div>
</fieldset>" ;
return $tpl->_ENGINE_parse_body($html);		
}

function ReportSettings(){
	$ave=new kav4mailservers(1,null,1);
	$page=CurrentPageName();
	$tpl=new Templates(null,null,null,1);	

	$html=Tabs()."
	<form name='ffm1'>
	<fieldset>
		<legend>{report_settings}</legend>
		
		<input type='hidden' name='KEY' value='smtpscan.report'>		
			<table style='width:100%'>
				<tr>
					<td align='right' nowrap><strong>{AVStatistics}:</strong></td>
					<td>" . Field_text('AVStatistics',$ave->array_conf["smtpscan.report"]["AVStatistics"])  . "</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td class='caption'>{AVStatistics_text}</td>
				</tr>		
		
				<tr><td colspan=2>&nbsp;</td></tr>
				<tr><td colspan=2 style='text-align:right;padding-right:10px'><input type='button' value='{submit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm1','$page',true);\"></td></tr>
			</table>
	</fieldset>
	</form>
		";
return $tpl->_ENGINE_parse_body($html);		
}

function GeneralSettings(){
	$ave=new kav4mailservers(1,null,1);
	$page=CurrentPageName();
	$tpl=new Templates(null,null,null,1);
	$html=Tabs()."
	<fieldset>
		<legend>{general_settings}</legend>
	<form name='ffm1'>		
	<table style='width:100%'>
	<input type='hidden' name='KEY' value='smtpscan.general'>
		<tr>
			<td align='right' nowrap><strong>{NotifyFromAddress}:</strong></td>
			<td>" . Field_text('NotifyFromAddress',$ave->array_conf["smtpscan.general"]["NotifyFromAddress"])  . "</td>
		</tr>
		<tr>
		<tr><td colspan=2>&nbsp;</td></tr>
		<tr>
			<td align='right' valign='top'>" . Field_yesno_checkbox_img('EHLOsupport8BITMIME',$ave->array_conf["smtpscan.general"]["EHLOsupport8BITMIME"],'{enable_disable}'). "</td>
			<td align='left' class='caption'><strong>:{EHLOsupport8BITMIME}</strong>{EHLOsupport8BITMIME_text}</td>
		</tr>
		
		<tr>
		<tr><td colspan=2>&nbsp;</td></tr>
		
		<tr>
			<td align='right' valign='top'>" . Field_yesno_checkbox_img('EHLOsupportDSN',$ave->array_conf["smtpscan.general"]["EHLOsupportDSN"],'{enable_disable}'). "</td>
			<td align='left' class=caption><strong>:{EHLOsupportDSN}</strong>{EHLOsupportDSN_text}</td>
		</tr>
		
		<tr><td colspan=2>&nbsp;</td></tr>
		<tr>
			<td align='right' valign='top'>" . Field_yesno_checkbox_img('EHLOsupportXFORWARD',$ave->array_conf["smtpscan.general"]["EHLOsupportXFORWARD"],'{enable_disable}'). "</td>
			<td align='left'><strong>:{EHLOsupportXFORWARD}</strong></td>
		</tr>
		<tr>
			<td align='right'><strong>{EHLOattrsXFORWARD}:</strong></td>
			<td align='right' valign='top'>" . Field_text('EHLOattrsXFORWARD',$ave->array_conf["smtpscan.general"]["EHLOattrsXFORWARD"]). "</td>
		</tr>		
		<tr>
		<td>&nbsp;</td>
		<td class='caption'>{EHLOsupportXFORWARD_text}<br><b>protocol:</b>{$ave->array_conf["smtpscan.general"]["Protocol"]} &laquo&nbsp;{$ave->array_conf["smtpscan.general"]["ForwardMailer"]}&nbsp;&raquo</td>
		</tr>
		<tr><td colspan=2 style='text-align:right;padding-right:10px'><input type='button' value='{submit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm1','$page',true);\"></td></tr>
	</table>
	</form>
	</fieldset>";
	
return $tpl->_ENGINE_parse_body($html);	
	
}
function EchoTab(){
	$tpl=new templates();
	switch ($_GET["tab"]) {
		case 0:return $tml=GeneralSettings();break;
		case 1:return $html=ReportSettings();break;
		case 2:return $html=read_conf();break;
	
		default:
			break;
	}
	echo $tpl->_ENGINE_parse_body($html);
	
	
}
function SaveFunctions(){
	$key=$_GET["KEY"];
	unset($_GET["KEY"]);
	$conf=new kav4mailservers(0,null,1);
	while (list ($num, $ligne) = each ($_GET) ){
		$conf->array_conf[$key][$num]=$ligne;
		
	}
	$conf->Save();
	$conf->SaveToServer();
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');
	
}
	
?>