<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.mimedefang.inc');
	$user=new usersMenus();
	if($user->AsPostfixAdministrator==false){header('location:users.index.php');exit();}
	if(isset($_GET["main"])){main_switch();exit;}
	if(isset($_GET["SaveGeneralSettings"])){SaveConf();exit;}
	if(isset($_GET["rewrite_headers"])){rewrite_headers();exit;}
	if(isset($_GET["add_headers"])){add_headers();exit;}
	if(isset($_GET["status"])){echo main_status_milter();exit;}
	if(isset($_GET["MimeDefangAddExt"])){Save_extdeny();exit;}
	if(isset($_GET["MimeDefangDeleteExt"])){Delete_extdeny();exit;}
	if(isset($_GET["ENABLE_DISCLAIMER"])){Save_disclaimer();exit;}
	if(isset($_GET["MimeDefangAddDisclamerAddress"])){Save_disclaimer_addr();}
	if(isset($_GET["MimeDefangDelDisclamerAddress"])){Delete_disclaimer_addr();}
	if(isset($_GET["mainTab"])){main_config_switch();exit;}
	
	
	main_page();
	
function main_page(){
	

	
	$html=
	"
<script language=\"JavaScript\">       
var timerID  = null;
var timerID1  = null;
var tant=0;
var reste=0;

function demarre(){
   tant = tant+1;
   reste=10-tant;
	if (tant < 10 ) {                           
      timerID = setTimeout(\"demarre()\",5000);
      } else {
               tant = 0;
               //document.getElementById('wait').innerHTML='<img src=img/wait.gif>';
               ChargeLogs();
               demarre();                                //la boucle demarre !
   }
}


function ChargeLogs(){
	LoadAjax('services_status','$page?status=yes&hostname={$_GET["hostname"]}');
	}
</script>	
	
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'><img src='img/mimedefang_bg.png' style='margin:10px;margin-right:30px'>	<p class=caption>{about}</p></td>
	<td valign='top'><div id='services_status'>". main_status_milter() . "</div></td>
	</tr>
	<tr>
		<td colspan=2 valign='top'><br>
			<div id='main_config'></div>
		</td>
	</tr>
	</table>
	<script>demarre();LoadAjax('main_config','$page?main=yes');</script>
	
	";
	
	$cfg["JS"][]='js/mimedefang.js';
	$tpl=new template_users('{APP_MIMEDEFANG}',$html,0,0,0,0,$cfg);
	echo $tpl->web_page;
	}	

function main_tabs(){
	if(!isset($_GET["main"])){$_GET["main"]="yes";};
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}
	$page=CurrentPageName();
	$array["yes"]='{main_settings}';
	$array["bad_exts"]='{bad_exts}';
	$array["disclaimer"]='{disclaimer}';
	$array["mysql"]='{database_stats}';
	$array["conf"]='{config}';
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["main"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('main_config','$page?main=$num&hostname=$hostname')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";		
}


function main_switch(){
	
	switch ($_GET["main"]) {
		case "yes":main_config();exit;break;
		case "logs":main_logs();exit;break;
		case "bad_exts":main_bad_exts();exit;break;
		case "disclaimer": echo main_disclaimer();exit;break;
		case "conf":echo main_conf();exit;break;
		case "plugins";echo main_plugins();exit;break;
		case "discladdress";echo main_disclaimer_ips();exit;break;
		case "mysql";echo main_statistics();exit;break;
		default:
			break;
	}
	
	
}	

function main_status_milter(){
if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}		
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString($sock->getfile('mimedefangstatus',$_GET["hostname"]));	
	if($ini->_params["MIMEDEFANG"]["running"]==0){
		$img="okdanger32.png";
		$rouage='rouage_on.png';
		$status="{stopped}";
	}else{
		$img="ok32.png";
		$status="running";
	}
	
	$status1="<table style='width:100%'>
	<tr>
	<td valign='top'>
		<img src='img/$img'>
	</td>
	<td valign='top'>
		<table style='width:100%'>
		<tr>
		<td align='right' nowrap class=legend>{APP_MIMEDEFANG}:</strong></td>
		<td><strong>$status</strong></td>
		</tr>		
		<tr>
		<td align='right'><strong>{pid}:</strong></td>
		<td><strong>{$ini->_params["MIMEDEFANG"]["master_pid"]}</strong></td>
		</tr>
		<tr>
		<td align='right'><strong>{memory}:</strong></td>
		<td nowrap class=legend>{$ini->_params["MIMEDEFANG"]["master_memory"]}&nbsp; kb</strong></td>
		</tr>
		<tr>
		<td align='right'><strong>{version}:</strong></td>
		<td><strong>{$ini->_params["MIMEDEFANG"]["master_version"]}</strong></td>
		</tr>				
		<tr><td colspan=2>$error</td></tr>	
		<tr><td colspan=2>&nbsp;</td></tr>	
		
		</table>		
	</td>
	</tr>
	</table>";
	
	$status1=RoundedLightGreen($status1);
	
if($ini->_params["MIMEDEFANGX"]["running"]==0){
		$img="okdanger32.png";
		$rouage='rouage_on.png';
		$status="{stopped}";
	}else{
		$img="ok32.png";
		$status="running";
	}
	
	$status2="<table style='width:100%'>
	<tr>
	<td valign='top'>
		<img src='img/$img'>
	</td>
	<td valign='top'>
		<table style='width:100%'>
		<tr>
		<td align='right' nowrap class=legend>{APP_MIMEDEFANGX}:</strong></td>
		<td><strong>$status</strong></td>
		</tr>		
		<tr>
		<td align='right'><strong>{pid}:</strong></td>
		<td><strong>{$ini->_params["MIMEDEFANGX"]["master_pid"]}</strong></td>
		</tr>
		<tr>
		<td align='right'><strong>{memory}:</strong></td>
		<td nowrap class=legend>{$ini->_params["MIMEDEFANGX"]["master_memory"]}&nbsp; kb</strong></td>
		</tr>
		<tr>
		<td align='right'><strong>{version}:</strong></td>
		<td><strong>{$ini->_params["MIMEDEFANGX"]["master_version"]}</strong></td>
		</tr>				
		<tr><td colspan=2>$error</td></tr>	
		<tr><td colspan=2>&nbsp;</td></tr>	
		
		</table>		
	</td>
	</tr>
	</table>";	
	
	
	$status2=RoundedLightGreen($status2);
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($status1."<br>".$status2);	
	
}


function main_config_tabs(){
	if(!isset($_GET["mainTab"])){$_GET["mainTab"]="main";};
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}
	$page=CurrentPageName();
	$array["main"]='{main_settings}';
	$array["antispam_features"]='{antispam_features}';
	$array["antivirus"]='{antivirus}';
	$array["backup"]='{backup_filters}';
	
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["mainTab"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('main_config_area','$page?mainTab=$num&hostname=$hostname')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";		
	
	
}



function main_config_main(){
$mime=new mimedefang();
$tab=main_config_tabs();	
$html="<H5>{main_settings}</h5>
	<form name='FFM_DANS2'>
	<input type='hidden' name='SaveGeneralSettings' value='yes'>
	<table style='width:100%'>
	
<tr>
	<td $style align='right' nowrap valign='top'><strong>{AdminAddress}:</strong></td>
	<td $style valign='top'>" . Field_text('AdminAddress',$mime->main_array["AdminAddress"],'width:70%',null,null,'{AdminAddress_text}')."</td>
	<td $style valign='top'>&nbsp;</td>
</tr>

<tr>
	<td $style align='right' nowrap valign='top'><strong>{AdminName}:</strong></td>
	<td $style valign='top'>" . Field_text('AdminName',$mime->main_array["AdminName"],'width:70%',null,null,'{AdminName_text}')."</td>
	<td $style valign='top'>&nbsp;</td>
</tr>

<tr>
	<td $style align='right' nowrap valign='top'><strong>{DaemonAddress}:</strong></td>
	<td $style valign='top'>" . Field_text('DaemonAddress',$mime->main_array["DaemonAddress"],'width:70%',null,null,'{DaemonAddress_text}')."</td>
	<td $style valign='top'>&nbsp;</td>
</tr>	
<tr>	
	<td $style align='right' nowrap valign='top'><strong>{AddWarningsInline}:</strong></td>
	<td $style valign='top'>" . Field_numeric_checkbox_img('AddWarningsInline',$mime->main_array["AddWarningsInline"],'{AddWarningsInline_text}')."</td>
	<td $style valign='top'>&nbsp;</td>
</tr>	
<tr>	
	<td $style align='right' nowrap valign='top'><strong>{CONVERT_TNEF}:</strong></td>
	<td $style valign='top'>" . Field_numeric_checkbox_img('CONVERT_TNEF',$mime->ScriptConf_array["BUILD"]["CONVERT_TNEF"],'{CONVERT_TNEF_text}')."</td>
	<td $style valign='top'>&nbsp;</td>
</tr>
<tr>	
	<td $style align='right' nowrap valign='top'><strong>{RECIPIENT_TRANSFORM}:</strong></td>
	<td $style valign='top'>" . Field_numeric_checkbox_img('RECIPIENT_TRANSFORM',$mime->ScriptConf_array["BUILD"]["RECIPIENT_TRANSFORM"],'{RECIPIENT_TRANSFORM_TEXT}')."</td>
	<td $style valign='top'>&nbsp;</td>
</tr>



	<tr>
	<td $style colspan=3 align='right' valign='top'>
	
	<input type='button' value='{save}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('FFM_DANS2','$page',true);LoadAjax('main_config','$page?main=yes');\"></td>
	</tr>
</table>
</form>


";

	$html=$tab."<br>".RoundedLightGrey($html);
	$tpl=new templates();
	return  $tpl->_ENGINE_parse_body($html);
	
}


function main_config_switch(){
	switch ($_GET["mainTab"]) {
		case "main":echo main_config_main();exit;break;
		case "antispam_features":echo main_config_antispam();exit;break;
		case "antivirus":echo main_config_antivirus();exit;break;
		case "backup":echo main_config_backup();exit;break;
		
		default:
			break;
	}	
	
	
}


function main_config(){
	$style="style='padding:3px;border-bottom:1px dotted #CCCCCC'";
	$users=new usersMenus();
	$page=CurrentPageName();
	
	$html=main_tabs()."<br>
	<div id='main_config_area'>" . main_config_main()."</div>";

	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function main_config_antispam(){
	
	
	$spamass=true;
	$clamav=true;
	$mime=new mimedefang();
	$users=new usersMenus();
	$users->LoadModulesEnabled();
	
	
	if(!$users->BOGOFILTER_INSTALLED){
		$bogofilter="
		<tr>
		<td align='right' width=1% nowrap class=legend>{ENABLE_BOGOFILTER}</td>
		<td align='left'><img src='img/ok24-grey.png'></td>
		<td>&nbsp;</td>
		</tr>";
		}else{
		$bogofilter="
		<tr>
		<td align='right' width=1% nowrap class=legend>{ENABLE_BOGOFILTER}:</td>
		<td align='left'>" . Field_numeric_checkbox_img('ENABLE_BOGO',$mime->ScriptConf_array["BUILD"]["ENABLE_BOGO"],'{enable_disable}')."</td>
		<td>&nbsp;</td>
		</tr>";			
			
		}	
	
	
	if($users->SPAMASS_MILTER_INSTALLED){
		if($users->SpamAssMilterEnabled==1){$spamass=false;}
	}else{
		$spamass=false;
	}
	
	
	
	if($users->kas_installed){
		$kas="
		<tr>
		<td align='right' width=1% nowrap class=legend>{DISCARD_SPAM_KAS3}:</td>
		<td align='left'>" . Field_text('DISCARD_SPAM_KAS3',$mime->ScriptConf_array["BUILD"]["DISCARD_SPAM_KAS3"],'width:50px')."</td>
		<td align='left'>%</td>
		</tr>
		
		";
		
		
	}
	
	if(!$spamass){
		
		$spamass_txt="
		<tr>
		<td align='right' width=1% nowrap class=legend>{ENABLE_SPAMASSASSIN}</td>
		<td align='left'><img src='img/ok24-grey.png'></td>
		<td>&nbsp;</td>
		</tr>
		$kas
		$bogofilter";
	}else{
		$spam=new spamassassin();
		$spamass_txt="
		<tr>
		<td align='right' width=1% nowrap class=legend>{ENABLE_SPAMASSASSIN}:</td>
		<td align='left'>" . Field_numeric_checkbox_img('ENABLE_SA',$mime->ScriptConf_array["BUILD"]["ENABLE_SA"],'{enable_disable}')."</td>
		<td>&nbsp;</td>
		</tr>
		$bogofilter
		<tr>
		<td align='right' width=1% nowrap class=legend>{spamass_score}:</td>
		<td align='left'><strong style='font-size:11px'>{$spam->main_array["required_score"]}</strong></td>
		<td>&nbsp;</td>
		</tr>		
		<tr>
		<td align='right' width=1% nowrap class=legend>{DISCARD_SPAM_SPAMASS}:</td>
		<td align='left'>" . Field_text('DISCARD_SPAM',$mime->ScriptConf_array["BUILD"]["DISCARD_SPAM_SPAMASS"],'width:50px')."</td>
		<td>&nbsp;</td>
		</tr>
		<tr>
		<td align='right' width=1% nowrap class=legend>{SA_RCPT_ENABLE}:</td>
		<td align='left' width=1%>
			<table style='width:100%'>
			<tr>
			<td width=1%>
				" . Field_numeric_checkbox_img('SA_RCPT_ENABLE',$mime->ScriptConf_array["BUILD"]["SA_RCPT_ENABLE"],'{enable_disable}')."
			</td>
				<td align='left'>".Field_text('SA_RCPT_EMAIL',$mime->ScriptConf_array["BUILD"]["SA_RCPT_EMAIL"],'width:180px',null,null)."
			</td>
			</tr>
			</table>
		</td>
		<td>" . help_icon('{SA_RCPT_EMAIL}')."</td>
		</tr>
		$kas
		";
		
	}



	
	$antispam="
	<H5>{antispam_features}</H5>
	<form name='FFM_DANS2'>
	<input type='hidden' name='SaveGeneralSettings' value='yes'>
	<table style='width:100%'>
	$spamass_txt
		<tr><td colspan=3>&nbsp;</td>
		<tr><td colspan=3><H3>{quarantines}</h3></td>
		<tr>
		<td align='right' width=1% nowrap class=legend>{QUARANTINE_SPAM}:</td>
		<td align='left'>" . Field_numeric_checkbox_img('QUARANTINE_SPAM',$mime->ScriptConf_array["BUILD"]["QUARANTINE_SPAM"],'{enable_disable}')."</td>
		<td>&nbsp;</td>
		</tr>
		<tr><td colspan=3>&nbsp;</td>
		<tr><td colspan=3><H3>{whitelists}</H3></td>
		<tr>
		<td align='right' width=1% nowrap class=legend>{AUTOWHITE_LIST}:</td>
		<td align='left'>" . Field_numeric_checkbox_img('AUTOWHITE_LIST',$mime->ScriptConf_array["BUILD"]["AUTOWHITE_LIST"],'{enable_disable}')."</td>
		<td>&nbsp;</td>
		</tr>				
	<tr>
	<td $style colspan=3 align='right' valign='top'>
	<input type='button' value='{save}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('FFM_DANS2','$page',true);LoadAjax('main_config','$page?main=yes');\"></td>
	</tr>	
	</table>
	</form>";	
	
	$tab=main_config_tabs();
	$html=$tab."<br>".RoundedLightGrey($antispam);
	$tpl=new templates();
	return  $tpl->_ENGINE_parse_body($html);	
	
	
}

function main_config_antivirus(){
	

	$spamass=true;
	$clamav=true;
	$mime=new mimedefang();
	$users=new usersMenus();
	$users->LoadModulesEnabled();	
	
if($users->CLAMAV_INSTALLED){
		if($users->ClamavMilterEnabled==1){$clamav=false;}
	}else{
		$clamav=false;
	}
	
	if(!$clamav){
		$clamav_txt="
		<tr>
		<td align='right' with=1% nowrap class=legend>{ENABLE_CLAMAV}</td>
		<td align='left'><img src='img/ok24-grey.png'></td>
		<td>&nbsp;</td>
		</tr>";
	}else{
		$clamav_txt="
		<tr>
		<td align='right' with=1% nowrap class=legend>{ENABLE_CLAMAV}:</td>
		<td align='left'>" . Field_numeric_checkbox_img('ENABLE_AV',$mime->ScriptConf_array["BUILD"]["ENABLE_AV"],'{enable_disable}')."</td>
		<td>&nbsp;</td>
		</tr>		";
		
	}

	
	$clam="
	<H5>{antivirus}</H5>
	<form name='FFM_DANS2'>
	<input type='hidden' name='SaveGeneralSettings' value='yes'>
	<table style='width:100%'>
	$clamav_txt
	<tr>
			<td $style colspan=3 align='right' valign='top'>
				<input type='button' value='{save}&nbsp;&raquo;' 
				OnClick=\"javascript:ParseForm('FFM_DANS2','$page',true);LoadAjax('main_config','$page?main=yes');\"></td>
	</tr>	
	</table>
	</form>
	
	";
	
	$tab=main_config_tabs();
	$html=$tab."<br>".RoundedLightGrey($clam);
	$tpl=new templates();
	return  $tpl->_ENGINE_parse_body($html);	
		
	
	
}


function main_config_backup(){
	
	$spamass=true;
	$clamav=true;
	$mime=new mimedefang();
	$users=new usersMenus();
	$users->LoadModulesEnabled();
	$tab=main_config_tabs();
	$backup_enabled=true;	

	$bightml="<tr>
		<td align='right' with=1% nowrap class=legend>{BIGHTML_ENABLED}:</td>
		<td align='left'>" . Field_numeric_checkbox_img('BIGHTML_ENABLED',$mime->ScriptConf_array["BUILD"]["BIGHTML_ENABLED"],'{enable_disable}')."</td>
		<td>&nbsp;</td>
		</tr>
		";
	
	
	$filtersenderto="<tr>
		<td align='right' with=1% nowrap class=legend>{FILTER_LOCAL_SENDER_ENABLED}:</td>
		<td align='left'>" . 
		Field_numeric_checkbox_img('FILTER_LOCAL_SENDER_ENABLED',$mime->ScriptConf_array["BUILD"]["FILTER_LOCAL_SENDER_ENABLED"],'{FILTER_LOCAL_SENDER_ENABLED_TEXT}')."
		</td>
		<td>&nbsp;</td>
		</tr>
		";	
	
	
	if(!$users->MHONARC_INSTALLED){$backup_enabled=false;}
	if($users->MailArchiverEnabled==1){$backup_enabled=false;}
	if($backup_enabled){
	  $backup="<tr>
		<td align='right' with=1% nowrap class=legend>{BACKUP_ENABLED}:</td>
		<td align='left'>" . Field_numeric_checkbox_img('BACKUP_ENABLED',$mime->ScriptConf_array["BUILD"]["BACKUP_ENABLED"],'{enable_disable}')."</td>
		<td>&nbsp;</td>
		</tr>";
	}else{
		$backup="<tr>
		<td align='right' with=1% nowrap class=legend>{BACKUP_ENABLED}:</td>
		<td align='left'><img src='img/status_ok-grey.gif'><input type='hidden' name='BACKUP_ENABLED' value='0' id='BACKUP_ENABLED'></td>
		<td>&nbsp;</td>
		</tr>";
	}
	$other="
	<form name='FFM_DANS2'>
	<input type='hidden' name='SaveGeneralSettings' value='yes'>
	<H5>{backup_filters}</H5>
	<table style='width:100%'>
		$filtersenderto
		$bightml
		$backup
		<tr>
			<td $style colspan=3 align='right' valign='top'>
				<input type='button' value='{save}&nbsp;&raquo;' 
				OnClick=\"javascript:ParseForm('FFM_DANS2','$page',true);LoadAjax('main_config','$page?main=yes');\">
			</td>
		</tr>	
	</table>
	</FORM>";
	
	
	$other=$tab."<br>".RoundedLightGrey($other);

	$tpl=new templates();	
	
return $tpl->_ENGINE_parse_body($other);
	
}


function main_disclaimer(){
$mime=new mimedefang();	
$page=CurrentPageName();
$html=main_tabs()."<br>

	<h5>{disclaimer}</H5>
	<p class=caption>{diclaimer_text}</p>
	<form name='FFM89'>
	" . RoundedLightGrey("
	<table style='width:100%'>
		<tr>
		<td align='right' with=1% nowrap class=legend>{ENABLE_DISCLAIMER}:</td>
		<td align='left'>" . Field_numeric_checkbox_img('ENABLE_DISCLAIMER',$mime->ScriptConf_array["BUILD"]["ENABLE_DISCLAIMER"],'{enable_disable}')."</td>
		<td>&nbsp;</td>
		</tr>
	<tr>
	<td colspan=3>
		<textarea style='background-color:#FFFFFF;background-image:none;width:100%;height:50px;border:1px solid #CCCCCC' name='disclaimer'>$mime->Disclaimer</textarea></td>
	</tr>
	<tr>
	<td $style colspan=3 align='right' valign='top'>
	<input type='button' value='{save}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('FFM89','$page',true);\"></td>
	</tr>	
	</table>")."
	
	</form>
	<br>
	<div id='discladdress'>" . main_disclaimer_ips()."</div>
	
	";	
	
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
	
	
}


function main_disclaimer_ips(){
	$mime=new mimedefang();
	$tbl=explode(";",$mime->ScriptConf_array["BUILD"]["DICLAIMER_ADDR"]);
	$html="
	<input type='hidden' id='disclaimer_servers_q' value='{disclaimer_servers_q}'>
	<H5>{disclaimer_servers_address}</H5>
	<p class=caption>{disclaimer_servers_address_text}</p>
	<div style='float:right'><input type='button' OnClick=\"javascript:MimeDefangAddDisclamerAddress();\" value='{disclaimer_servers_address}'></div>
	<br>";
	
	if(is_array($tbl)){
		$t="<table style='width:100%'>";
		while (list ($num, $ligne) = each ($tbl) ){
			if($ligne<>null){
			$t=$t."
	<tr " . CellRollOver().">
	<td width=1%'><img src='img/fw_bold.gif'></td>
	<td><strong style='font-size:11px'>$ligne</td>
	<td width=1%'>" . imgtootltip('ed_delete.gif',"{delete}","MimeDefangDelDisclamerAddress($num);")."</td>
	</tr>";
			
		}}
		
		$t=$t."</table>";
		
	}
	$t=RoundedLightGrey($t);
	
$tpl=new templates();
	return  $tpl->_ENGINE_parse_body($html.$t);		
	
}


function SaveConf(){
	$g=new mimedefang();
while (list ($num, $ligne) = each ($_GET) ){
	$g->main_array[$num]=$ligne;
	$g->ScriptConf_array["BUILD"][$num]=$ligne;
	
}
	
$g->SaveToLdap();	
}


function main_conf(){
	$h=new mimedefang();
	$page=CurrentPageName();
	$g=$h->global_conf;
	$g=nl2br($g);
	
	$i=$h->ScriptConf;
	$i=nl2br($i);
	
	$html=main_tabs()."<br>
	<h5>{config}</H5>
	<div style='padding:10px'>
	<code>$g</code>
	<hr>
	<code>$i</code>
	</div>";
		
$tpl=new templates();
	echo  $tpl->_ENGINE_parse_body($html);		
}


function main_statistics(){
	$mysql=new mysql();
	$quarantine_rows=$mysql->COUNT_ROWS('quarantine','artica_backup');
	$backup_rows=$mysql->COUNT_ROWS('storage','artica_backup');
	$database_size=$mysql->DATABASE_SIZE('artica_backup');
	
	$html=main_tabs()."<br>
	<H3>{database_stats}</H3>";
	$html=$html . RoundedLightGrey("
	<table style='width:100%;'>
		<tr>
		<td valign='top' width=1%><img src='img/database.png'></td>
		<td valign='top'>
			<table style='width:100%'>
			<tr>
				<td align='right' style='font-size:13px'><strong>{database_size}:</strong></td>
				<td style='font-size:13px'>$database_size</td>
			</tr>			
			<tr>
				<td align='right' style='font-size:13px'><strong>{quarantine_rows}:</strong></td>
				<td style='font-size:13px'>$quarantine_rows</td>
			</tr>
	
			<tr>
				<td align='right' style='font-size:13px'><strong>{backup_rows}:</strong></td>
				<td style='font-size:13px'>$backup_rows</td>
			</tr>
		</table>");
	
	
$tpl=new templates();
return $tpl->_ENGINE_parse_body($html);
	
	
}




function main_bad_exts(){
	$tablestyle="style='width:100px;margin-right:5px;border-right:1px solid #CCCCCC'";
	$mime=new mimedefang();
	$tbl=explode("\|",$mime->main_array["bad_exts"]);
$table="
<div>
<div style='float:left'><table $tablestyle>";
if(is_array($tbl)){

while (list ($num, $ligne) = each ($tbl) ){
	$count=$count+1;
	if($count>10){$table=$table . "
		</table>
			</div>
	<div style='float:left'>
		<table $tablestyle>";}
	$table=$table."
	<tr " . CellRollOver().">
	<td width=1%'><img src='img/fw_bold.gif'></td>
	<td><strong style='font-size:11px'>$ligne</td>
	<td width=1%'>" . imgtootltip('ed_delete.gif',"{delete}","MimeDefangDeleteExt($num);")."</td>
	</tr>";
	
}
}
$table=$table."</table></div></div>";

$html=main_tabs()."<br>
	<h5>{bad_exts}</H5>
<input type='hidden' id='add_deny_ext_prompt' value='{add_deny_ext_prompt}'>
<table style='width:100%'>
<tr>
	<td valign='top' width='550px'>$table</td>
	<td valign='top' width=1%>
	" . RoundedLightGrey(Paragraphe("red-pushpin-plus.png","{add_deny_ext}","{add_deny_ext_text}","javascript:MimeDefangAddExt();"))."
	</td>
</tr>
</table>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
	
}

function Save_extdeny(){
	$mime=new mimedefang();
	$tbl=explode("\|",$mime->main_array["bad_exts"]);
	$tbl[]=$_GET["MimeDefangAddExt"];
	$mime->main_array["bad_exts"]=implode('|',$tbl);
	$mime->SaveToLdap();
	
}

function Delete_extdeny(){
	$mime=new mimedefang();
	$tbl=explode("\|",$mime->main_array["bad_exts"]);
	unset($tbl[$_GET["MimeDefangDeleteExt"]]);
	$mime->main_array["bad_exts"]=implode('|',$tbl);
	$mime->SaveToLdap();	
	}

function main_logs(){
	$page=CurrentPageName();
	$tpl=new templates();
	$html=main_tabs() . "
	<H5>{events}</H5>
	<iframe src='miltergreylist.events.php' style='width:100%;height:500px;border:0px'></iframe>";
	echo $tpl->_ENGINE_parse_body($html);
	}
function Save_disclaimer(){
	$mime=new mimedefang();
	$mime->Disclaimer=$_GET["disclaimer"];
	$mime->ScriptConf_array["BUILD"]["ENABLE_DISCLAIMER"]=$_GET["ENABLE_DISCLAIMER"];
	$mime->SaveToLdap();
	}
function Save_disclaimer_addr(){
	$mime=new mimedefang();
	$tbl=explode(";",$mime->ScriptConf_array["BUILD"]["DICLAIMER_ADDR"]);
	$tbl[]=$_GET["MimeDefangAddDisclamerAddress"];
	$mime->ScriptConf_array["BUILD"]["DICLAIMER_ADDR"]=implode(";",$tbl);
	$mime->SaveToLdap();
}
function Delete_disclaimer_addr(){
$mime=new mimedefang();
	$tbl=explode(";",$mime->ScriptConf_array["BUILD"]["DICLAIMER_ADDR"]);
	unset($tbl[$_GET["MimeDefangDelDisclamerAddress"]]);
	$mime->ScriptConf_array["BUILD"]["DICLAIMER_ADDR"]=implode(";",$tbl);
	$mime->SaveToLdap();	
}
?>
