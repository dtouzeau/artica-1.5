<?php
	
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.mysql.inc');	
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.milter.greylist.inc');
	include_once('ressources/class.sqlgrey.inc');
	include_once('ressources/class.main_cf.inc');
	include_once(dirname(__FILE__) . '/ressources/class.kavmilterd.inc');
	$user=new usersMenus();
	$_GET["POSTFIX_VERSION"]=$user->POSTFIX_VERSION;	
	
	
	
	if($user->AsMailBoxAdministrator==false){header('location:users.index.php');exit();}
	if(isset($_GET["main"])){main_switch();exit;}
	if(isset($_GET["status"])){echo main_status();exit;}
	if(isset($_GET["SqlGreyEnabled"])){SqlGreyEnabled();exit;}
	if(isset($_GET["MilterGreyListEnabled"])){MilterGreyListEnabled();exit;}
	if(isset($_GET["ArticaFilterEnabled"])){ArticaFilterEnabled();exit;}
	if(isset($_GET["AmavisFilterEnabled"])){AmavisFilterEnabled();exit;}
	if(isset($_GET["KasxFilterEnabled"])){KasxFilterEnabled();exit;}
	if(isset($_GET["milter_enabled"])){milter_enabled();exit;}
	if(isset($_GET["MailFromdEnabled"])){MailFromdEnabled();exit;}
	if(isset($_GET["clamilter_enabled"])){clamilter_enabled();exit;}
	if(isset($_GET["SpamassMilter_enabled"])){SpamassMilter_enabled();exit;}
	if(isset($_GET["spfmilter_enabled"])){spfmilter_enabled();exit();}
	if(isset($_GET["MimeDefangEnabled"])){MimeDefangEnabled();exit;}
	if(isset($_GET["DkimFilterEnabled"])){DkimFilterEnabled();exit;}
	if(isset($_GET["fetchmail_enabled"])){fetchmail_enabled();exit;}
	if(isset($_GET["fdm_enabled"])){fdm_enabled();exit;}
	if(isset($_GET["p3scan_enabled"])){p3scan_enabled();exit;}
	if(isset($_GET["MailArchiverEnabled"])){MailArchive_enabled();exit;}
	if(isset($_GET["milterbgogom"])){milterbgogom_enabled();exit;}
	if(isset($_GET["mailspy_enabled"])){mailspy_enabled();exit;}
	if(isset($_GET["jcheckmail"])){jcheckmail_enabled();exit;}
	if(isset($_GET["script"])){bundle_js();exit;}
	if(isset($_GET["bundle"])){bundle_index();exit;}
	if(isset($_GET["bundle_save"])){bundle_save();exit;}
	if(isset($_GET["response"])){response();exit;}
	
	if(isset($_GET["js"])){page_js();exit;}
	if(isset($_GET["js-page"])){page_js_start();exit;}
	
	
	main_page();
	
	
	
function response(){
	$func=nl2br($_GET["response"]);
	echo RoundedLightYellow($func);
	
}
	
function page_js_start(){

$html="
<H1>{POSTFIX_PLUGINS}</H1>

		<table style='width:100%'>
		<tr>
		<td colspan=2 valign='top'><H3>{legend}</H3></td>
		<td width=1% valign='top'><img src='img/ok24.png'></td>
		<td valign='top' nowrap>{legend_ok}</td>
		<td width=1% valign='top'><img src='img/danger24.png'></td>
		<td valign='top' nowrap>{legend_disabled}</td>
		<td width=1% valign='top'><img src='img/ok24-grey.png'></td>
		<td valign='top' nowrap>{legend_uninstall}</td>
		</tr>
		</table>
<hr>
<table style='width:100%'>
<tr>
<td valign='top' width=1%>
" . RoundedLightWhite("
<img src='img/bg_applis-120.jpg' style='margin-right:3px'>
<p class=caption>{about}</p>")."

</td>
<td valign='top'>" . RoundedLightWhite("
	<div id='postfix_plugins_main' style='width:100%;height:400px;overflow:auto'></div>")."
</td>
</tr>
</table>

";

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
	
	
}
	
	
function page_js(){

	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{POSTFIX_PLUGINS}');
	$page=CurrentPageName();
	$content=file_get_contents("js/sqlgrey.js");
	$memory=$_COOKIE["postfix_plugin_main"];
	
	$html="
	var tab_selected_plugins_postfix='';
	$content
	
	function LoadPluginPage(){
		YahooWin2('800','$page?js-page=yes','$title');
		setTimeout(\"SwitchPlugin('$memory')\",1000);
		
		}
		
	function SwitchPlugin(tab){
			LoadAjax('postfix_plugins_main','$page?main='+tab);
		}
		
	var x_EnableModule= function (obj) {
		YahooWin3('300','$page?response='+obj.responseText,'$title');
		EnableModule2();
	}

	function EnableModule2(){
		SwitchPlugin(tab_selected_plugins_postfix);
		setTimeout(\"YahooWin3Hide()\",3000);
	}
		
		
	function EnableModule(page){
		tab_selected_plugins_postfix=document.getElementById('tab_selected').value;
		var XHR = new XHRConnection();
		document.getElementById('postfix_plugins_main').innerHTML=\"<center style='width:100%'><img src='img/wait_verybig.gif'></center>\";
		XHR.sendAndLoad(page, 'GET',x_EnableModule);
	
	}
		
		
		LoadPluginPage();
		
		
		";
		
	echo $html;	
	
}
	
	
	
function main_page(){
	if(preg_match('#([0-9])\.([0-9])#',$_GET["POSTFIX_VERSION"],$re)){
		$_GET["POSTFIX_VERSION"]="{$re[1]}.{$re[2]}";
	}
	

$defs="

<table style='width:100%'>
<tr>
<td colspan=2><H3>{legend}</H3></td></tr>
<tr>
<td width=1% valign='top'><img src='img/ok24.png'></td>
<td valign='top'>{legend_ok}</td>
</tr>
<tr>
<td width=1% valign='top'><img src='img/danger24.png'></td>
<td valign='top'>{legend_disabled}</td>
</tr>
<tr>
<td width=1% valign='top'><img src='img/ok24-grey.png'></td>
<td valign='top'>{legend_uninstall}</td>
</tr>
</table>
";
$defs=RoundedLightGrey($defs);
	
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
	<td width=1% valign='top'><img src='img/bg_applis-250.jpg' style='margin-right:80px'></td>
	<td valign='top'><p class=caption>{about}</p>$defs</td>
	</tr>
	<tr>
		<td colspan=2 valign='top'><br>
			<div id='main_config'></div>
		</td>
	</tr>
	</table>
	<script>LoadAjax('postfix_plugins_main','$page?main={$_COOKIE["postfix_plugin_main"]}');</script>
	
	";
	
	$cfg["JS"][]='js/sqlgrey.js';
	$tpl=new template_users('{POSTFIX_PLUGINS} '." (Postfix {$_GET["POSTFIX_VERSION"]}.x)",$html,0,0,0,0,$cfg);
	echo $tpl->web_page;
	}	

function main_tabs(){
	if(!isset($_GET["main"])){$_GET["main"]="yes";};
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}
	$page=CurrentPageName();
	$array["yes"]='{POSTFIX_PLUGINS}';
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["main"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('postfix_plugins_main','$page?main=$num')\" $class>$ligne</a></li>\n";
			
		}
	return "<input type='hidden' id='tab_selected' name='tab_selected' value='{$_GET["main"]}'>
	<div id=tablist>$html</div>";		
}


function main_switch(){
	cookies_main();
	switch ($_GET["main"]) {
		case "yes":postfix_plugins_main();exit;break;
		case "logs":main_logs();exit;break;
		case "conf":echo main_conf();exit;break;
		case "connections":
			postfix_plugins_main_connection();
			exit;
			break;	
			
		case "antispam":
			postfix_plugins_main_antispam();
			exit;
			break;	
			
		case "antivirus":
			postfix_plugins_main_antivirus();
			exit;
			break;

		case "mailbox":
			postfix_plugins_main_mailbox();
			exit;
			break;						
				
		default:
			break;
	}
	
	
}	

function main_status(){

$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}		
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString(base64_decode($sock->getFrameWork('cmd.php?cyrus-imap-status=yes')));
	if($ini->_params["CYRUSIMAP"]["running"]==0){
		$img="okdanger32.png";
		$status="{stopped}";
	}else{
		$img="ok32.png";
		$status="running";
		
	}
	$v='Mb';
	$size=round($ini->_params["CYRUSIMAP"]["partition_size"]/1000,2);
	if($size>1000){
			$size=round($size/1000,2);
			$v='Go';
	}
	
	
	$status="<table style='width:100%'>
	<tr>
	<td valign='top'>
		<img src='img/$img'>
	</td>
	<td valign='top'>
		<table style='width:100%'>
		<tr>
		<td align='right' nowrap><strong>{APP_CYRUS_IMAP}:</strong></td>
		<td><strong>$status</strong></td>
		</tr>		
		<tr>
		<td align='right'><strong>{pid}:</strong></td>
		<td><strong>{$ini->_params["CYRUSIMAP"]["master_pid"]}</strong></td>
		</tr>
		<tr>
		<td align='right'><strong>{memory}:</strong></td>
		<td nowrap><strong>{$ini->_params["CYRUSIMAP"]["master_memory"]}&nbsp; mb</strong></td>
		</tr>
		<tr>
		<td align='right'><strong>{version}:</strong></td>
		<td><strong>{$ini->_params["CYRUSIMAP"]["master_version"]}</strong></td>
		</tr>	
		<tr>
		<td align='right'><strong>{mailbox_size}:</strong></td>
		<td><strong>$size&nbsp;$v</td>
		</tr>					
		<tr><td colspan=2>$error</td></tr>	
		<tr><td colspan=2>&nbsp;</td></tr>	
		
		</table>		
	</td>
	</tr>
	</table>";
	
	$status=RoundedLightGreen($status);
	$status_serv=RoundedLightGrey(Paragraphe($rouage ,$rouage_title. " (squid)",$rouage_text,"javascript:$js"));
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($status);
	
}


function main_tabs_glob(){
	if(!isset($_GET["main"])){$_GET["main"]="connect";};
	$page=CurrentPageName();
	$array["connections"]='{connections_plugins}';
	$array["antispam"]='{antispam_plugins}';
	$array["antivirus"]='{antivirus_plugins}';
	
	$array["mailbox"]='{mailboxes_plugins}';
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["main"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('postfix_plugins_main','$page?main=$num&hostname=$hostname')\" $class>$ligne</a></li>\n";
			
		}
	$tpl=new templates();
	
	return $tpl->_ENGINE_parse_body("<input type='hidden' id='tab_selected' name='tab_selected' value='{$_GET["main"]}'><br><div id=tablist>$html</div><br>");		
}


function postfix_plugins_main_connection(){
	$sock=new sockets();
	$users=new usersMenus();
	$conf=$sock->getfile('daemons_status');
	$ini=new Bs_IniHandler();
	$ini->loadString($conf);	
	
$tab=main_tabs_glob();


	$html="$tab
	<table style='width:100%'>
	<tr>
		<th style='text-align:center' width=1% nowrap>{module_name}</th>
		<th style='text-align:center' width=1%>{enabled}</th>
		<th style='text-align:center'>{module_features}</th>
	</tr>";
	
	$html=$html . MailArchive($ini->_params);
	$html=$html . Miltergreylist($ini->_params);
	$html=$html . dkimfilter($ini->_params);
	$html=$html . spfmilter($ini->_params);	
	$html=$html . mailspy($ini->_params);	
	$html=$html . Amavis($ini->_params);
	
	$html=$html."</table>";
	 
	 
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function postfix_plugins_main_antispam(){
	$sock=new sockets();
	$users=new usersMenus();
	$conf=$sock->getfile('daemons_status');
	$ini=new Bs_IniHandler();
	$ini->loadString($conf);	
	
$tab=main_tabs_glob();


	$html="$tab
	<table style='width:100%'>
	<tr>
		<th style='text-align:center' width=1% nowrap>{module_name}</th>
		<th style='text-align:center' width=1%>{enabled}</th>
		<th style='text-align:center'>{module_features}</th>
	</tr>";
	
	$html=$html . Kas3($ini->_params);
	$html=$html . jcheckmail($ini->_params);
	$html=$html . SpamassMilter($ini->_params);
	$html=$html . milterbgogom($ini->_params);
	$html=$html . Amavis($ini->_params);
	$html=$html . MimeDefang($ini->_params);	
	$html=$html . mailfromd($ini->_params);
	
	$html=$html."</table>";
	 
	 
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}
function postfix_plugins_main_antivirus(){
	$sock=new sockets();
	$users=new usersMenus();
	$conf=$sock->getfile('daemons_status');
	$ini=new Bs_IniHandler();
	$ini->loadString($conf);	
	
$tab=main_tabs_glob();


	$html="$tab
	<table style='width:100%'>
	<tr>
		<th style='text-align:center' width=1% nowrap>{module_name}</th>
		<th style='text-align:center' width=1%>{enabled}</th>
		<th style='text-align:center'>{module_features}</th>

	</tr>";
	$html=$html . KavMilter($ini->_params);	
	$html=$html . jcheckmail($ini->_params);
	$html=$html . ClamMilter($ini->_params);	
	$html=$html . Amavis($ini->_params);
	$html=$html . MimeDefang($ini->_params);	
	
	$html=$html."</table>";
	 
	 
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function postfix_plugins_main_mailbox(){
	$sock=new sockets();
	$users=new usersMenus();
	$conf=$sock->getfile('daemons_status');
	$ini=new Bs_IniHandler();
	$ini->loadString($conf);	
	
$tab=main_tabs_glob();


	$html="$tab
	<table style='width:100%'>
	<tr>
		<th style='text-align:center' width=1% nowrap>{module_name}</th>
		<th style='text-align:center' width=1%>{enabled}</th>
		<th style='text-align:center'>{module_features}</th>
	</tr>";
	
	$html=$html . fetchmail($ini->_params);
	$html=$html . fdm($ini->_params);
	$html=$html . MimeDefang($ini->_params);	
	$html=$html . P3scan($ini->_params);	
	//$html=$html . Sqlgrey($ini->_params);
	$html=$html . Amavis($ini->_params);
	
	$html=$html."</table>";
	 
	 
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}


function postfix_plugins_main(){
	cookies_main();
	if($_GET["main"]==null){
		postfix_plugins_main_connection();
		exit;
	}
	
	switch ($_GET["main"]) {
		case "yes":
			$_GET["main"]="connections";
			postfix_plugins_main_connection();
			exit;
			break;
		
		case "connections":
			postfix_plugins_main_connection();
			exit;
			break;
			
			
		case "antispam":
			postfix_plugins_main_antispam();
			exit;
			break;
			
		
		case "antivirus":
			postfix_plugins_main_antivirus();
			exit;
			break;
			
		
		case "mailbox":
			postfix_plugins_main_mailbox();
			exit;
			break;			
			
			
		default:
			break;
	}
	

	
}



	
function ArticaFiter($array){
	$page=CurrentPageName();
	$artica=new artica_general();
	$js="LoadAjax('postfix_plugins_main','$page?ArticaFilterEnabled=$artica->ArticaFilterEnabled');";	
		if($artica->ArticaFilterEnabled==1){
			$img="ok24.png";
			
			
		}else{
			$img="danger24.png";
		}
		
	$html="
	<tr>
	<td nowrap style='padding:3px' valign='top'><strong>{APP_ARTICA_FILTER}</strong></td>
	<td width=1% align='center' style='padding:3px' valign='top'>" . imgtootltip($img,'{enable_disable}',$js)."</td>
	<td style='padding:3px' valign='top'>{APP_ARTICA_FILTER_DEF}</td>
	</tr>
	";
	
	return $html;	
	}
	
function mailfromd($array){
	$page=CurrentPageName();
	$artica=new artica_general();
	$js="LoadAjax('postfix_plugins_main','$page?MailFromdEnabled=$artica->MailFromdEnabled');";	
		if($artica->MailFromdEnabled==1){
			$img="ok24.png";
			
			
		}else{
			$img="danger24.png";
		}
		
$users=new usersMenus();
if(!$users->MAILFROMD_INSTALLED){
		$img="ok24-grey.png";
		$js=null;	
	}
		
		
	$html="
	<tr>
	<td nowrap style='padding:3px' valign='top'><strong>{APP_MAILFROMD}</strong></td>
	<td width=1% align='center' style='padding:3px' valign='top'>" . imgtootltip($img,'{enable_disable}',$js)."</td>
	<td style='padding:3px' valign='top'>{APP_MAILFROMD_DEF}</td>
	</tr>
		<tr><td colspan=3><hr></td></tr>
	";
	
	return $html;	
	}	
	
function dkimfilter($array){
	$page=CurrentPageName();
	$artica=new artica_general();
	$js="LoadAjax('postfix_plugins_main','$page?DkimFilterEnabled=$artica->DkimFilterEnabled');";	
	if($array["DKIM_FILTER"]["application_installed"]==1){	
		if($artica->DkimFilterEnabled==1){
			$img="ok24.png";
			
			
		}else{
			$img="danger24.png";
		}
	}else{
		$img="ok24-grey.png";
		$js=null;	
	}
		
	$html="
	<tr>
	<td nowrap style='padding:3px' valign='top'><strong>{APP_DKIM_FILTER}</strong></td>
	<td width=1% align='center' style='padding:3px' valign='top'>" . imgtootltip($img,'{enable_disable}',$js)."</td>
	<td style='padding:3px' valign='top'>{APP_DKIM_FILTER_DEF}</td>
	</tr>
		<tr><td colspan=3><hr></td></tr>
	";
	
	return $html;	
	}		
	
function Kas3($array){
	
	$page=CurrentPageName();
	$artica=new artica_general();
	$js="LoadAjax('postfix_plugins_main','$page?KasxFilterEnabled=$artica->KasxFilterEnabled');";

	if($array["KAS3"]["application_installed"]==1){	
		if($artica->KasxFilterEnabled==1){
			$img="ok24.png";
			
			
		}else{
			$img="danger24.png";
		}
	}else{
		$img="ok24-grey.png";	
	}
		
	$html="
	<tr>
	<td nowrap style='padding:3px' valign='top'><strong>{APP_KAS3}</strong></td>
	<td width=1% align='center' style='padding:3px' valign='top'>" . imgtootltip($img,'{enable_disable}',$js)."</td>
	<td style='padding:3px' valign='top'>{APP_KAS3_DEF}</td>
	</tr>
	<tr><td colspan=3><hr></td></tr>
	";
	
	return $html;	
	}
function KavMilter($array){
	$artica=new artica_general();
	$page=CurrentPageName();
	
	if($artica->kavmilterEnable=="no"){$artica->kavmilterEnable=0;}
	if($artica->kavmilterEnable=="yes"){$artica->kavmilterEnable=1;}
	
	$js="LoadAjax('postfix_plugins_main','$page?milter_enabled=$artica->kavmilterEnable');";

	if($array["KAVMILTER"]["application_installed"]==1){	
		if($artica->kavmilterEnable==1){
			$img="ok24.png";
			
			
		}else{
			$img="danger24.png";
		}
	}else{
		$img="ok24-grey.png";	
	}
		
	$html="
	<tr>
	<td nowrap style='padding:3px' valign='top'><strong>{APP_KAVMILTER}</strong></td>
	<td width=1% align='center' style='padding:3px' valign='top'>" . imgtootltip($img,'{enable_disable}',$js)."</td>
	<td style='padding:3px' valign='top'>{APP_KAVMILTER_DEF}</td>
	</tr>
	<tr><td colspan=3><hr></td></tr>
	";
	
	return $html;	
	}
	
	
function MailArchive($array){
$page=CurrentPageName();
	
	$sock=new sockets();
	$MailArchiverEnabled=$sock->GET_INFO("MailArchiverEnabled");
	$js="EnableModule('$page?MailArchiverEnabled=$MailArchiverEnabled);";

	if($array["MAILARCHIVER"]["application_installed"]==1){	
		if($MailArchiverEnabled==1){
			$img="ok24.png";
			
			
		}else{
			$img="danger24.png";
		}
	}else{
		$img="ok24-grey.png";	
	}
		
	$html="
	<tr>
	<td nowrap style='padding:3px' valign='top'><strong>{APP_MAILARCHIVER}</strong></td>
	<td width=1% align='center' style='padding:3px' valign='top'>" . imgtootltip($img,'{enable_disable}',$js)."</td>
	<td style='padding:3px' valign='top'>{APP_MAILARCHIVER_DEFS}</td>
	</tr>
	<tr><td colspan=3><hr></td></tr>
	";
	
	return $html;		
}


function MailArchive_enabled(){
	$sock=new sockets();
	if($_GET["MailArchiverEnabled"]==1){
		writelogs("MailArchiverEnabled - > Change to 0",__FUNCTION__,__FILE__);
		$sock->SET_INFO("MailArchiverEnabled",0);}
	else{$sock->SET_INFO("MailArchiverEnabled",1);}	
	
	
	$main=new main_cf();
	$main->save_conf();
	$main->save_conf_to_server();

	
	
}

function milterbgogom_enabled(){
	$artica=new artica_general();
	if($_GET["milterbgogom"]==1){
		writelogs("milterbgogom - > Change to 0",__FUNCTION__,__FILE__);
		$artica->EnableMilterBogom=0;}
	else{$artica->EnableMilterBogom=1;}	
	writelogs("EnableMilterBogom=$artica->EnableMilterBogom",__FUNCTION__,__FILE__);
	$artica->Save();
	$main=new main_cf();
	$main->save_conf();
	$main->save_conf_to_server();
	
}

function mailspy_enabled(){
	$artica=new artica_general();
	if($_GET["mailspy_enabled"]==1){
		writelogs("mailspy_enabled - > Change to 0",__FUNCTION__,__FILE__);
		$artica->EnableMilterSpyDaemon=0;}
	else{$artica->EnableMilterSpyDaemon=1;}	
	writelogs("EnableMilterSpyDaemon=$artica->EnableMilterSpyDaemon",__FUNCTION__,__FILE__);
	$artica->Save();
	$main=new main_cf();
	$main->save_conf();
	$main->save_conf_to_server();

	}


function ClamMilter($array){
$page=CurrentPageName();
	$artica=new artica_general();
	$js="EnableModule('$page?clamilter_enabled=$artica->ClamavMilterEnabled');";

	if($array["CLAMAV_MILTER"]["application_installed"]==1){	
		if($artica->ClamavMilterEnabled==1){
			$img="ok24.png";
			
			
		}else{
			$img="danger24.png";
		}
	}else{
		$img="ok24-grey.png";	
	}
		
	$html="
	<tr>
	<td nowrap style='padding:3px' valign='top'><strong>{APP_CLAMAV_MILTER}</strong></td>
	<td width=1% align='center' style='padding:3px' valign='top'>" . imgtootltip($img,'{enable_disable}',$js)."</td>
	<td style='padding:3px' valign='top'>{APP_CLAMAV_MILTER_DEFS}</td>
	</tr>
	<tr><td colspan=3><hr></td></tr>
	";
	
	return $html;		
	}
	
function cookies_main(){
	
	if($_GET["main"]==null){
		if($_COOKIE["postfix_plugin_main"]<>null){
			$_GET["main"]=$_COOKIE["postfix_plugin_main"];
		}else{
			$_GET["main"]="yes";
		}
	}else{
		setcookie('postfix_plugin_main',$_GET["main"], (time() + 3600));
		

	}
	
}	
	
function milterbgogom($array){
$page=CurrentPageName();
	$artica=new artica_general();
	$js="EnableModule('$page?milterbgogom=$artica->EnableMilterBogom');";

	if($array["BOGOFILTER"]["application_installed"]==1){	
		if($artica->EnableMilterBogom==1){
			$img="ok24.png";
			
			
		}else{
			$img="danger24.png";
		}
	}else{
		$img="ok24-grey.png";	
	}
		
	$html="
	<tr>
	<td nowrap style='padding:3px' valign='top'><strong>{APP_BOGOM}</strong></td>
	<td width=1% align='center' style='padding:3px' valign='top'>" . imgtootltip($img,'{enable_disable}',$js)."</td>
	<td style='padding:3px' valign='top'>{APP_BOGOM_DEFS}</td>
	</tr>
	<tr><td colspan=3><hr></td></tr>
	";
	
	return $html;		
	}	

function fdm($array){
$page=CurrentPageName();
	$artica=new artica_general();
	$js="EnableModule('$page?fdm_enabled=$artica->EnableFDMFetch');";

	if($array["FDM"]["application_installed"]==1){	
		if($artica->EnableFDMFetch==1){
			$img="ok24.png";
			
			
		}else{
			$img="danger24.png";
		}
	}else{
		$img="ok24-grey.png";	
	}
		
	$html="
	<tr>
	<td nowrap style='padding:3px' valign='top'><strong>{APP_FDM}</strong></td>
	<td width=1% align='center' style='padding:3px' valign='top'>" . imgtootltip($img,'{enable_disable}',$js)."</td>
	<td style='padding:3px' valign='top'>{APP_FDM_DEFS}</td>
	</tr>
	<tr><td colspan=3><hr></td></tr>
	";
	
	return $html;		
	
}
function Fetchmail($array){
$page=CurrentPageName();
	$artica=new artica_general();
	$js="EnableModule('$page?fetchmail_enabled=$artica->EnableFetchmail');";

	if($array["FETCHMAIL"]["application_installed"]==1){	
		if($artica->EnableFetchmail==1){
			$img="ok24.png";
			
			
		}else{
			$img="danger24.png";
		}
	}else{
		$img="ok24-grey.png";	
	}
		
	$html="
	<tr>
	<td nowrap style='padding:3px' valign='top'><strong>{APP_FETCHMAIL}</strong></td>
	<td width=1% align='center' style='padding:3px' valign='top'>" . imgtootltip($img,'{enable_disable}',$js)."</td>
	<td style='padding:3px' valign='top'>{APP_FETCHMAIL_DEFS}</td>
	</tr>
	<tr><td colspan=3><hr></td></tr>
	";
	
	return $html;		
	
}
function P3scan($array){
$page=CurrentPageName();
	$artica=new artica_general();
	$js="EnableModule('$page?p3scan_enabled=$artica->P3ScanEnabled');";

	if($array["P3SCAN"]["application_installed"]==1){	
		if($artica->P3ScanEnabled==1){
			$img="ok24.png";
			
			
		}else{
			$img="danger24.png";
		}
	}else{
		$img="ok24-grey.png";	
	}
		
	$html="
	<tr>
	<td nowrap style='padding:3px' valign='top'><strong>{APP_P3SCAN}</strong></td>
	<td width=1% align='center' style='padding:3px' valign='top'>" . imgtootltip($img,'{enable_disable}',$js)."</td>
	<td style='padding:3px' valign='top'>{APP_P3SCAN_DEFS}</td>
	</tr>
	<tr><td colspan=3><hr></td></tr>
	";
	
	return $html;		
	
}

function spfmilter($array){
$page=CurrentPageName();
	$artica=new artica_general();
	$js="EnableModule('$page?spfmilter_enabled=$artica->spfmilterEnabled');";

	if($array["SPFMILTER"]["application_installed"]==1){	
		if($artica->spfmilterEnabled==1){
			$img="ok24.png";
			
			
		}else{
			$img="danger24.png";
		}
	}else{
		$img="ok24-grey.png";	
	}
		
	$html="
	<tr>
	<td nowrap style='padding:3px' valign='top'><strong>{APP_SPFMILTER}</strong></td>
	<td width=1% align='center' style='padding:3px' valign='top'>" . imgtootltip($img,'{enable_disable}',$js)."</td>
	<td style='padding:3px' valign='top'>{APP_SPFMILTER_DEFS}</td>
	</tr>
	<tr><td colspan=3><hr></td></tr>
	";
	
	return $html;		
	
}

function mailspy($array){
$page=CurrentPageName();
	$artica=new artica_general();
	$js="EnableModule('$page?mailspy_enabled=$artica->EnableMilterSpyDaemon');";

	if($array["MAILSPY"]["application_installed"]==1){	
		if($artica->EnableMilterSpyDaemon==1){
			$img="ok24.png";
			
			
		}else{
			$img="danger24.png";
		}
	}else{
		$img="ok24-grey.png";	
	}
		
	$html="
	<tr>
	<td nowrap style='padding:3px' valign='top'><strong>{APP_MAILSPY}</strong></td>
	<td width=1% align='center' style='padding:3px' valign='top'>" . imgtootltip($img,'{enable_disable}',$js)."</td>
	<td style='padding:3px' valign='top'>{APP_MAILSPY_DEFS}</td>
	</tr>
	<tr><td colspan=3><hr></td></tr>
	";
	
	return $html;	
	
	
}

function jcheckmail($array){
$page=CurrentPageName();
	$artica=new artica_general();
	$js="EnableModule('$page?jcheckmail=$artica->JCheckMailEnabled');";

	if($array["JCHECKMAIL"]["application_installed"]==1){	
		if($artica->JCheckMailEnabled==1){
			$img="ok24.png";
			
			
		}else{
			$img="danger24.png";
		}
	}else{
		$img="ok24-grey.png";	
	}
		
	$html="
	<tr>
	<td nowrap style='padding:3px' valign='top'><strong>{APP_JCHECKMAIL}</strong></td>
	<td width=1% align='center' style='padding:3px' valign='top'>" . imgtootltip($img,'{enable_disable}',$js)."</td>
	<td style='padding:3px' valign='top'>{APP_JCHECKMAIL_DEFS}</td>
	</tr>
	<tr><td colspan=3><hr></td></tr>
	";
	
	return $html;		
	
}


function SpamassMilter($array){
$page=CurrentPageName();
	$artica=new artica_general();
	$js="EnableModule('$page?SpamassMilter_enabled=$artica->SpamAssMilterEnabled');";

	if($array["SPAMASS_MILTER"]["application_installed"]==1){	
		if($artica->SpamAssMilterEnabled==1){
			$img="ok24.png";
			
			
		}else{
			$img="danger24.png";
		}
	}else{
		$img="ok24-grey.png";	
	}
		
	$html="
	<tr>
	<td nowrap style='padding:3px' valign='top'><strong>{APP_SPAMASS_MILTER}</strong></td>
	<td width=1% align='center' style='padding:3px' valign='top'>" . imgtootltip($img,'{enable_disable}',$js)."</td>
	<td style='padding:3px' valign='top'>{APP_SPAMASS_MILTER_DEFS}</td>
	</tr>
	<tr><td colspan=3><hr></td></tr>
	";
	
	return $html;		
	
}
		
	
function Miltergreylist($array){
		$page=CurrentPageName();
		$artica=new artica_general();
			if($array["MILTER_GREYLIST"]["application_installed"]==1){	
				$js="EnableModule('$page?MilterGreyListEnabled=$artica->MilterGreyListEnabled');";	
					if($artica->MilterGreyListEnabled==1){
						$img="ok24.png";
						
						
					}else{
						$img="danger24.png";
					}
					
			}else{
				$img="ok24-grey.png";
			}
					
				$html="
				<tr>
				<td nowrap style='padding:3px' valign='top'><strong>{APP_MILTERGREYLIST}</strong></td>
				<td width=1% align='center' style='padding:3px' valign='top'>" . imgtootltip($img,'{enable_disable}',$js)."</td>
				<td style='padding:3px' valign='top'>{MILTERGREYLIST_DEF}</td>
				</tr>
				<tr><td colspan=3><hr></td></tr>
				";
				
		return $html;	
	}

	
function MimeDefang($array){
	$page=CurrentPageName();
	$artica=new artica_general();
	
			if($array["MIMEDEFANG"]["application_installed"]==1){	
				$js="EnableModule('$page?MimeDefangEnabled=$artica->MimeDefangEnabled');";	
					if($artica->MimeDefangEnabled==1){
						$img="ok24.png";
						
						
					}else{
						$img="danger24.png";
					}
					
			}else{
				$img="ok24-grey.png";
			}
			
			if($_GET["POSTFIX_VERSION"]<2.4){
				$img="ok24-grey.png";
				$js=null;
				$text_add="{need_to_upgrade_postfix}";
			}
			
					
				$html="
				<tr>
				<td nowrap style='padding:3px' valign='top'><strong>{APP_MIMEDEFANG}</strong></td>
				<td width=1% align='center' style='padding:3px' valign='top'>" . imgtootltip($img,'{enable_disable}',$js)."</td>
				<td style='padding:3px' valign='top'>$text_add{MIMEDEFANG_DEF}</td>
				</tr>
				<tr><td colspan=3><hr></td></tr>
				";
				
		return $html;		
	
	
}
	
function Amavis($array){
		$page=CurrentPageName();
		$sock=new sockets();
		$EnableAmavisDaemon=$sock->GET_INFO("EnableAmavisDaemon");
			if($array["AMAVISD"]["application_installed"]==1){	
				$js="EnableModule('$page?AmavisFilterEnabled=$EnableAmavisDaemon');";	
					if($EnableAmavisDaemon=="1"){
						$img="ok24.png";
						
						
					}else{
						$img="danger24.png";
					}
					
			}else{
				$img="ok24-grey.png";
			}
					
				$html="
				<tr>
				<td nowrap style='padding:3px' valign='top'><strong>{APP_AMAVISD_MILTER}</strong></td>
				<td width=1% align='center' style='padding:3px' valign='top'>" . imgtootltip($img,'{enable_disable}',$js)."</td>
				<td style='padding:3px' valign='top'>{AMAVIS_DEF}</td>
				</tr>
				<tr><td colspan=3><hr></td></tr>
				";
				
		return $html;	
	}	

function Sqlgrey($array){
	$page=CurrentPageName();
	$sqlgrey=new sqlgrey($users->hostname);
	$js="EnableModule('$page?SqlGreyEnabled=$sqlgrey->SqlGreyEnabled');";	
	
	if($array["SQLGREY"]["application_installed"]==1){
		if($sqlgrey->SqlGreyEnabled==1){
			$img="ok24.png";
		}else{
			$img="danger24.png";
		}
		
	}else{
		$img="ok24-grey.png";
	}
	
	$html="
	<tr>
	<td nowrap style='padding:3px' valign='top'><strong>{APP_SQLGREY}</strong></td>
	<td width=1% align='center' style='padding:3px'>" . imgtootltip($img,'{enable_disable}',$js)."</td>
	<td style='padding:3px' valign='top'>{SQLGREY_DEF}</td>
	</tr>
	<tr><td colspan=3><hr></td></tr>
	";
	
	return $html;
	}
	


function ArticaFilterEnabled(){
	$artica=new artica_general();
	if($_GET["ArticaFilterEnabled"]==1){
		writelogs("ArticaFilterEnabled - > Change to 0",__FUNCTION__,__FILE__);
		$artica->ArticaFilterEnabled=0;}
	else{$artica->ArticaFilterEnabled=1;}	
	writelogs("ArticaFilterEnabled=$artica->ArticaFilterEnabled",__FUNCTION__,__FILE__);
	$artica->Save();
	$main=new main_cf();
	$main->save_conf();
	$main->save_conf_to_server();

	}
	
function clamilter_enabled(){
	$artica=new artica_general();
	if($_GET["clamilter_enabled"]==1){
		writelogs("clamilter_enabled - > Change to 0",__FUNCTION__,__FILE__);
		$artica->ClamavMilterEnabled=0;}
	else{$artica->ClamavMilterEnabled=1;}	
	writelogs("ClamavMilterEnabled=$artica->ClamavMilterEnabled",__FUNCTION__,__FILE__);
	$artica->Save();
	$main=new main_cf();
	$main->save_conf();
	$main->save_conf_to_server();

	}
	
function SpamassMilter_enabled(){
	$artica=new artica_general();
	if($_GET["SpamassMilter_enabled"]==1){
		writelogs("SpamassMilter_enabled - > Change to 0",__FUNCTION__,__FILE__);
		$artica->SpamAssMilterEnabled=0;}
	else{$artica->SpamAssMilterEnabled=1;}	
	writelogs("SpamAssMilterEnabled=$artica->SpamAssMilterEnabled",__FUNCTION__,__FILE__);
	$artica->Save();
	$main=new main_cf();
	$main->save_conf();
	$main->save_conf_to_server();

}

function spfmilter_enabled(){
	$artica=new artica_general();
	if($_GET["spfmilter_enabled"]==1){
		writelogs("spfmilter_enabled - > Change to 0",__FUNCTION__,__FILE__);
		$artica->spfmilterEnabled=0;}
	else{$artica->spfmilterEnabled=1;}	
	writelogs("spfmilterEnabled=$artica->spfmilterEnabled",__FUNCTION__,__FILE__);
	$artica->Save();
	$main=new main_cf();
	$main->save_conf();
	$main->save_conf_to_server();

	
}

function MimeDefangEnabled(){
	$artica=new artica_general();
	if($_GET["MimeDefangEnabled"]==1){
		writelogs("MimeDefangEnabled - > Change to 0",__FUNCTION__,__FILE__);
		$artica->MimeDefangEnabled=0;}
	else{
		$artica->MimeDefangEnabled=1;
		$artica->JCheckMailEnabled=0;
		$sock=new sockets();
		$sock->SET_INFO("EnableAmavisDaemon","0");	
		}	
	writelogs("MimeDefangEnabled=$artica->MimeDefangEnabled",__FUNCTION__,__FILE__);
	$artica->Save();
	$main=new main_cf();
	$main->save_conf();
	$main->save_conf_to_server();

	
}

function DkimFilterEnabled(){
	$artica=new artica_general();
	if($_GET["DkimFilterEnabled"]==1){
		writelogs("DkimFilterEnabled - > Change to 0",__FUNCTION__,__FILE__);
		$artica->DkimFilterEnabled=0;}
	else{$artica->DkimFilterEnabled=1;}	
	writelogs("DkimFilterEnabled=$artica->DkimFilterEnabled",__FUNCTION__,__FILE__);
	$artica->Save();
	$main=new main_cf();
	$main->save_conf();
	$main->save_conf_to_server();

	
}

function fetchmail_enabled(){
	$artica=new artica_general();
	if($_GET["fetchmail_enabled"]==1){
		writelogs("fetchmail_enabled - > Change to 0",__FUNCTION__,__FILE__);
		$artica->EnableFetchmail=0;}
	else{$artica->EnableFetchmail=1;}	
	writelogs("EnableFetchmail=$artica->EnableFetchmail",__FUNCTION__,__FILE__);
	$artica->Save();

}

function jcheckmail_enabled(){
	$artica=new artica_general();
	if($_GET["jcheckmail"]==1){
		writelogs("jcheckmail - > Change to 0",__FUNCTION__,__FILE__);
		$artica->JCheckMailEnabled=0;}
	else{$artica->JCheckMailEnabled=1;}	
	writelogs("EnableFetchmail=$artica->JCheckMailEnabled",__FUNCTION__,__FILE__);
	$artica->Save();
	$main=new main_cf();
	$main->save_conf();
	$main->save_conf_to_server();	

	}

function fdm_enabled(){
	$artica=new artica_general();
	if($_GET["fdm_enabled"]==1){
		writelogs("fdm_enabled - > Change to 0",__FUNCTION__,__FILE__);
		$artica->EnableFDMFetch=0;}
	else{$artica->EnableFDMFetch=1;}	
	writelogs("fdm_enabled=$artica->EnableFDMFetch",__FUNCTION__,__FILE__);
	$artica->Save();

}

function p3scan_enabled(){
	$artica=new artica_general();
	if($_GET["p3scan_enabled"]==1){
		writelogs("p3scan_enabled - > Change to 0",__FUNCTION__,__FILE__);
		$artica->P3ScanEnabled=0;}
	else{$artica->P3ScanEnabled=1;}	
	writelogs("p3scan_enabled=$artica->P3ScanEnabled",__FUNCTION__,__FILE__);
	$artica->Save();
		
	
}
	
	
	
function KasxFilterEnabled(){
	$artica=new artica_general();
	if($_GET["KasxFilterEnabled"]==1){
		writelogs("KasxFilterEnabled - > Change to 0",__FUNCTION__,__FILE__);
		$artica->KasxFilterEnabled=0;}
	else{$artica->KasxFilterEnabled=1;}	
	writelogs("KasxFilterEnabled=$artica->KasxFilterEnabled",__FUNCTION__,__FILE__);
	$artica->Save();
	$main=new main_cf();
	$main->save_conf();
	$main->save_conf_to_server();
			
	}
	
function milter_enabled(){
	$artica=new artica_general();
if($_GET["milter_enabled"]=='yes'){
		writelogs("milter_enabled - > Change to 0",__FUNCTION__,__FILE__);
		$artica->kavmilterEnable="0";
		;}
		
if($_GET["milter_enabled"]=='no'){
		writelogs("milter_enabled - > Change to 1",__FUNCTION__,__FILE__);
		$artica->kavmilterEnable="1";
		;}
			
if($_GET["milter_enabled"]==0){
		writelogs("milter_enabled - > Change to 1",__FUNCTION__,__FILE__);
		$artica->kavmilterEnable="1";
		;}		

if($_GET["milter_enabled"]==1){
		writelogs("milter_enabled - > Change to 1",__FUNCTION__,__FILE__);
		$artica->kavmilterEnable="0";
		;}		
		
	$artica->Save();	
	$milter=new kavmilterd();
	$milter->SaveToLdap();
	$main=new main_cf();
	$main->save_conf();
	$main->save_conf_to_server();
		
	}
	
function MailFromdEnabled(){
	$artica=new artica_general();
	if($_GET["MailFromdEnabled"]==1){
		writelogs("MailFromdEnabled - > Change to 0",__FUNCTION__,__FILE__);
		$artica->MailFromdEnabled=0;}
	else{$artica->MailFromdEnabled=1;}	
	writelogs("MailFromdEnabled=$artica->MailFromdEnabled",__FUNCTION__,__FILE__);
	$artica->Save();
	$main=new main_cf();
	$main->save_conf();
	$main->save_conf_to_server();
		
	
	
}
function AmavisFilterEnabled(){
	$sock=new sockets();
   if($_GET["AmavisFilterEnabled"]==1){
		writelogs("AmavisFilterEnabled - > Change to 0",__FUNCTION__,__FILE__);
		$EnableAmavisDaemon=0;}
	else{
		$artica=new artica_general();
		$artica->MimeDefangEnabled=0;
		$artica->JCheckMailEnabled=0;
		$artica->Save();
		$EnableAmavisDaemon=1;
	}	
	writelogs("EnableAmavisDaemon=$EnableAmavisDaemon",__FUNCTION__,__FILE__);
	$sock->SET_INFO('EnableAmavisDaemon',$EnableAmavisDaemon);	
	$main=new main_cf();
	$main->save_conf();
	$main->save_conf_to_server();
		
	}
function SqlGreyEnabled(){
	$grey=new sqlgrey();
if($_GET["SqlGreyEnabled"]==1){
		writelogs("SqlGreyEnabled - > Change to 0",__FUNCTION__,__FILE__);
		$grey->SqlGreyEnabled=0;
		}
	else{$grey->SqlGreyEnabled=1;}	
	writelogs("SqlGreyEnabled=$grey->SqlGreyEnabled",__FUNCTION__,__FILE__);
	$grey->SaveToLdap();
	
	}
	
function MilterGreyListEnabled(){

	$sock=new sockets();
	$MilterGreyListEnabled=$sock->GET_INFO("MilterGreyListEnabled");
	
	if($MilterGreyListEnabled==1){
		writelogs("MilterGreyListEnabled - > Change to FALSE",__FUNCTION__,__FILE__);
		$MilterGreyListEnabled=0;
		}
	else{$MilterGreyListEnabled=1;}	
	$sock->SET_INFO('MilterGreyListEnabled',$MilterGreyListEnabled);
	ApplyToPostfix();
	
}

function ApplyToPostfix(){
	$main=new main_cf();
	$main->save_conf();
	$main->save_conf_to_server();		
	
}


function bundle_js(){
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{POSTFIX_PLUGINS}");
	$html="
		LoadIndex();
	
		function LoadIndex(){
			YahooWin(700,'postfix.plugins.php?bundle=yes','$title');
		
		}
		
var x_js_reload= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){alert(tempvalue)}
     LoadIndex();  
	
	}
		
function Disable(name){
		
		document.getElementById('mybundles').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		var XHR = new XHRConnection();
        XHR.appendData('bundle_save',name);
        XHR.appendData('bundle_enable','no');
        XHR.sendAndLoad('postfix.plugins.php', 'GET',x_js_reload);
		
		}
		
function Enable(name){
	   	document.getElementById('mybundles').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		var XHR = new XHRConnection();
        XHR.appendData('bundle_save',name);
        XHR.appendData('bundle_enable','yes');
        XHR.sendAndLoad('postfix.plugins.php', 'GET',x_js_reload);		
		
		}
		
		";
	
	echo $html;
}

function bundle_save(){
	
				$sock=new sockets();
	
	$name=$_GET["bundle_save"];
	$enable=$_GET["bundle_enable"];
	if($name=="kasper"){
		if($enable=="no"){
			writelogs("DISABLE KASPERSKY",__FUNCTION__,__FILE__);
			$artica=new artica_general();
			$artica->KasxFilterEnabled=0;
			$artica->kavmilterEnable=0;
			$artica->Save();
			$main=new main_cf();
			$main->save_conf();
			$main->save_conf_to_server();
		}else{
			$artica=new artica_general();
			$artica->KasxFilterEnabled=1;
			$artica->kavmilterEnable=1;
			$artica->Save();
			$main=new main_cf();
			$main->save_conf();
			$main->save_conf_to_server();
			
			
		}
		$tpl=new templates();
		echo ($tpl->_ENGINE_parse_body("{enable}:$enable ->{success}\n"));
		return null;	
	}
	
	if($name=="amavis"){
		if($enable=="no"){
			writelogs("DISABLE AMAVIS",__FUNCTION__,__FILE__);
			$sock->SET_INFO("EnableAmavisDaemon","0");
			$main=new main_cf();
			$main->save_conf();
			$main->save_conf_to_server();
		}else{
			$sock=new sockets();
			$sock->SET_INFO("EnableAmavisDaemon","1");
			$main=new main_cf();
			$main->save_conf();
			$main->save_conf_to_server();
			
			
		}
		$tpl=new templates();
		echo ($tpl->_ENGINE_parse_body("{enable}:$enable ->{success}\n"));
		return null;	
	}

	if($name=="addons"){
		if($enable=="no"){
			writelogs("DISABLE ADDONS",__FUNCTION__,__FILE__);
			$artica=new artica_general();
			$artica->MilterGreyListEnabled=0;
			$artica->Save();
			$main=new main_cf();
			$main->save_conf();
			$main->save_conf_to_server();
		}else{
			$artica=new artica_general();
			$artica->MilterGreyListEnabled=1;
			$artica->Save();
			$main=new main_cf();
			$main->save_conf();
			$main->save_conf_to_server();
		}
		$tpl=new templates();
		echo ($tpl->_ENGINE_parse_body("{enable}:$enable ->{success}\n"));
		return null;	
	}

	if($name=="opensource"){
		$sock=new sockets();
		if($enable=="no"){
			writelogs("DISABLE opensource",__FUNCTION__,__FILE__);
			$artica=new artica_general();
			$artica->SpamAssMilterEnabled=0;
			$artica->ClamavMilterEnabled=0;
			$artica->Save();
			$main=new main_cf();
			$main->save_conf();
			$main->save_conf_to_server();
		}else{
			$artica=new artica_general();
			$sock->SET_INFO("EnableAmavisDaemon",0);
			$artica->SpamAssMilterEnabled=1;
			$artica->ClamavMilterEnabled=1;
			$artica->Save();
			$main=new main_cf();
			$main->save_conf();
			$main->save_conf_to_server();
		}
		$tpl=new templates();
		echo ($tpl->_ENGINE_parse_body("{enable}:$enable ->{success}\n"));
		return null;	
	}	
	
	
}

function bundle_index(){
	
	$html="<H1>{POSTFIX_BUNDLE}</H1>
	<div id='mybundles'>
	<table style='width:100%'>
	<tr>
		<td>".bundle_kaspersky()."</td>
		<td>".bundle_addons()."</td>
	</tr>
	<tr>
		<td>".bundle_opensource()."</td>
		<td>" . bundle_full()."</td>
	</tr>

	</table>
	</div>
	
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}



function bundle_full(){
	$bundle=true;
	$firstbg="bg-coinds-gris-300.png";
	
	
	$users=new usersMenus();
	$users->LoadModulesEnabled();
	if(!$users->AMAVIS_INSTALLED){$bundle=false;}
	if(!$users->CLAMAV_INSTALLED){$bundle=false;}
	if(!$users->spamassassin_installed){$bundle=false;}
	
	if($bundle==false){
		$firstbg="bg-coinds-red-300.png";
	}else{
		if($users->EnableAmavisDaemon==1){
			$firstbg="bg-coinds-green-300.png";
			$button="<input type='button' OnClick=\"javascript:Disable('amavis');\" value='{disable}&nbsp;&raquo;'>";
		}else{
			$button="<input type='button' OnClick=\"javascript:Enable('amavis');\" value='{enable}&nbsp;&raquo;'>";
		}
	}
	
	$clamav_img="danger24.png";
	$text_clamav="{not_installed}";
	
	$spamass_img="danger24.png";
	$spamass_text="{not_installed}";
	
	$ama_img="danger24.png";
	$ama_text="{not_installed}";
	
	if($users->CLAMAV_INSTALLED){
		$clamav_img="ok24.png";
		$text_clamav="{installed}";
	}
	
	if($users->spamassassin_installed){
		$spamass_img="ok24.png";
		$spamass_text="{installed}";
	}
	
	if($users->AMAVIS_INSTALLED){
		if($users->EnableAmavisDaemon==1){
			$ama_img="ok24.png";
			$ama_text="{activated}";
		}else{
			$ama_img="warning24.png";
			$ama_text="{disabled}";
		}
	}
	
	
	$text="
	<table style='width:100%;background-color:transparent;background-image:none'>
	<tr>
		<td colspan=2><H3 style='font-size:15px'>{amavis_bundle}</td>
	</tr>
	<tr>
		<td class=legend>{performances}:</td>
		<td>" . bundle_barre(40)."</td>
	</tr>
	<tr>
		<td class=legend>{functionnalities}:</td>
		<td>" . bundle_barre(100)."</td>
	</tr>
	<tr>
		<td class=legend>{security}:</td>
		<td>" . bundle_barre(90)."</td>
	</tr>		
	</table>
	<br>
	<table style='width:100%;background-color:transparent;background-image:none'>
	<tr>
		<td valign='top' width=1%><img src='img/$ama_img'></td>
		<td valign='middle'><span style='font-size:13px;font-weight:bold'>{APP_AMAVISD_MILTER}</span></td>
		<td valign='middle'><span style='font-size:13px;font-weight:bold'>$ama_text</span></td>
	</tr>		
	<tr>
		<td valign='top' width=1%><img src='img/$clamav_img'></td>
		<td valign='middle'><span style='font-size:13px;font-weight:bold'>{APP_CLAMAV}</span></td>
		<td valign='middle'><span style='font-size:13px;font-weight:bold'>$text_clamav</span></td>
	</tr>
	<tr>
		<td valign='top' width=1%><img src='img/$spamass_img'></td>
		<td valign='middle'><span style='font-size:13px;font-weight:bold'>{APP_SPAMASSASSIN}</span></td>
		<td valign='middle'><span style='font-size:13px;font-weight:bold'>$spamass_text</span></td>
	</tr>	

	<tr>
		<td colspan=3 align='right'>$button</td>
	</table>
	
	
	<br>
	
	";
	
	
	$html="<div style='width:283px;height:230px;background-repeat:no-repeat;background-image:url(img/$firstbg);padding:10px;' 
		onmouseover='javascript:this.style.backgroundImage=\"url(img/bg-coinds-blanc-300.png)\"'
		onmouseout='javascript:this.style.backgroundImage=\"url(img/$firstbg)\"'
		>$text
		
		&nbsp;
		</div>
	
	
	";
	return $html;
	
}

function bundle_kaspersky(){
	$bundle=true;
	$firstbg="bg-coinds-gris-300.png";
	
	
	$users=new usersMenus();
	$users->LoadModulesEnabled();
	if(!$users->kas_installed){$bundle=false;}
	if(!$users->KAV_MILTER_INSTALLED){$bundle=false;}
	
	
	if($bundle==false){
		$firstbg="bg-coinds-red-300.png";
	}else{
		if(($users->KasxFilterEnabled==1) && ($users->KAVMILTER_ENABLED==1)){
			$firstbg="bg-coinds-green-300.png";
			$button="<input type='button' OnClick=\"javascript:Disable('kasper');\" value='{disable}&nbsp;&raquo;'>";
		}else{
			$button="<input type='button' OnClick=\"javascript:Enable('kasper');\" value='{enable}&nbsp;&raquo;'>";
		}
	}
	
	$kas_img="okdanger32.png";
	$kas_text="{not_installed}";
	
	$kavm_img="okdanger32.png";
	$kavm_text="{not_installed}";
	
	if($users->kas_installed){
		if($users->KasxFilterEnabled==1){
			$kas_img="ok32.png";
			$kas_text="{activated}";
		}else{
			$kas_img="warning32.png";
			$kas_text="{disabled}";
			}
	}
	
	if($users->KAV_MILTER_INSTALLED){
		if($users->KAVMILTER_ENABLED==1){
			$kavm_img="ok32.png";
			$kavm_text="{activated}";
		}else{
			$kavm_img="warning32.png";
			$kavm_text="{disabled}";	
		}
	}
	
	
	$text="
	<table style='width:100%;background-color:transparent;background-image:none'>
	<tr>
		<td colspan=2><H3 style='font-size:15px'>{kasper_bundle}</td>
	</tr>
	<tr>
		<td class=legend>{performances}:</td>
		<td>" . bundle_barre(90)."</td>
	</tr>
	<tr>
		<td class=legend>{functionnalities}:</td>
		<td>" . bundle_barre(20)."</td>
	</tr>
	<tr>
		<td class=legend>{security}:</td>
		<td>" . bundle_barre(80)."</td>
	</tr>		
	</table>
	<br>
	<table style='width:100%;background-color:transparent;background-image:none'>
	<tr>
		<td valign='top' width=1%><img src='img/$kas_img'></td>
		<td valign='middle'><span style='font-size:13px;font-weight:bold'>{APP_KAS3}</span></td>
		<td valign='middle'><span style='font-size:13px;font-weight:bold'>$kas_text</span></td>
	</tr>
	<tr>
		<td valign='top' width=1%><img src='img/$kavm_img'></td>
		<td valign='middle'><span style='font-size:13px;font-weight:bold'>{APP_KAVMILTER}</span></td>
		<td valign='middle'><span style='font-size:13px;font-weight:bold'>$kavm_text</span></td>
	</tr>	

	<tr>
		<td colspan=3 align='right'>$button</td>
	</table>
	
	
	<br>
	
	";
	
	
	$html="<div style='width:283px;height:230px;background-repeat:no-repeat;background-image:url(img/$firstbg);padding:10px;' 
		onmouseover='javascript:this.style.backgroundImage=\"url(img/bg-coinds-blanc-300.png)\"'
		onmouseout='javascript:this.style.backgroundImage=\"url(img/$firstbg)\"'
		>$text
		
		&nbsp;
		</div>
	
	
	";
	return $html;	
	
	
}

function bundle_addons(){
	$bundle=true;
	$firstbg="bg-coinds-gris-300.png";
	
	
	$users=new usersMenus();
	$users->LoadModulesEnabled();
	if(!$users->MILTERGREYLIST_INSTALLED){$bundle=false;}
	
	
	
	if($bundle==false){
		$firstbg="bg-coinds-red-300.png";
	}else{
		if(($users->MilterGreyListEnabled==1)){
			$firstbg="bg-coinds-green-300.png";
			$button="<input type='button' OnClick=\"javascript:Disable('addons');\" value='{disable}&nbsp;&raquo;'>";
		}else{
			$button="<input type='button' OnClick=\"javascript:Enable('addons');\" value='{enable}&nbsp;&raquo;'>";
		}
	}
	
	$clamav_img="okdanger32.png";
	$text_clamav="{not_installed}";
	
	
	
	if($users->MILTERGREYLIST_INSTALLED){
		if($users->MilterGreyListEnabled==1){
		$clamav_img="ok32.png";
		$text_clamav="{activated}";
		}else{
		$clamav_img="warning32.png";
		$text_clamav="{disabled}";			
		}
	}
	
	
	
	$text="
	<table style='width:100%;background-color:transparent;background-image:none'>
	<tr>
		<td colspan=2><H3 style='font-size:15px'>{addons_bundle}</td>
	</tr>
	<tr>
		<td class=legend>{performances}:</td>
		<td>" . bundle_barre(95)."</td>
	</tr>
	<tr>
		<td class=legend>{functionnalities}:</td>
		<td>" . bundle_barre(10)."</td>
	</tr>
	<tr>
		<td class=legend>{security}:</td>
		<td>" . bundle_barre(50)."</td>
	</tr>		
	</table>
	<br>
	<table style='width:100%;background-color:transparent;background-image:none'>
	<tr>
		<td valign='top' width=1%><img src='img/$clamav_img'></td>
		<td valign='middle'><span style='font-size:13px;font-weight:bold'>{APP_MILTERGREYLIST}</span></td>
		<td valign='middle'><span style='font-size:13px;font-weight:bold'>$text_clamav</span></td>
	</tr>
	<tr>
		<td colspan=3 align='right'>$button</td>
	</table>
	
	
	<br>
	
	";
	
	
	$html="<div style='width:283px;height:230px;background-repeat:no-repeat;background-image:url(img/$firstbg);padding:10px;' 
		onmouseover='javascript:this.style.backgroundImage=\"url(img/bg-coinds-blanc-300.png)\"'
		onmouseout='javascript:this.style.backgroundImage=\"url(img/$firstbg)\"'
		>$text
		
		&nbsp;
		</div>
	
	
	";
	return $html;
	
}


function bundle_opensource(){
	$bundle=true;
	$firstbg="bg-coinds-gris-300.png";
	
	
	$users=new usersMenus();
	$users->LoadModulesEnabled();
	if(!$users->SPAMASS_MILTER_INSTALLED){$bundle=false;}
	if(!$users->CLAMAV_MILTER_INSTALLED){$bundle=false;}
	
	
	if($bundle==false){
		$firstbg="bg-coinds-red-300.png";
	}else{
		if(($users->SpamAssMilterEnabled==1) && ($users->ClamavMilterEnabled==1)){
			$firstbg="bg-coinds-green-300.png";
			$button="<input type='button' OnClick=\"javascript:Disable('opensource');\" value='{disable}&nbsp;&raquo;'>";
		}else{
			$button="<input type='button' OnClick=\"javascript:Enable('opensource');\" value='{enable}&nbsp;&raquo;'>";
		}
	}
	
	$clamav_img="okdanger32.png";
	$text_clamav="{not_installed}";
	
	$spamass_img="okdanger32.png";
	$spamass_text="{not_installed}";
	
	if($users->CLAMAV_MILTER_INSTALLED){
		if($users->ClamavMilterEnabled==1){
		$clamav_img="ok32.png";
		$text_clamav="{activated}";
		}else{
		$clamav_img="warning32.png";
		$text_clamav="{disabled}";			
		}
	}
	
	if($users->SPAMASS_MILTER_INSTALLED){
		if($users->SpamAssMilterEnabled==1){
			$spamass_img="ok32.png";
			$spamass_text="{activated}";
		}else{
			$spamass_img="warning32.png";
			$spamass_text="{disabled}";
		}
	}
	
	
	$text="
	<table style='width:100%;background-color:transparent;background-image:none'>
	<tr>
		<td colspan=2><H3 style='font-size:15px'>{clamspam_bundle}</td>
	</tr>
	<tr>
		<td class=legend>{performances}:</td>
		<td>" . bundle_barre(60)."</td>
	</tr>
	<tr>
		<td class=legend>{functionnalities}:</td>
		<td>" . bundle_barre(30)."</td>
	</tr>
	<tr>
		<td class=legend>{security}:</td>
		<td>" . bundle_barre(70)."</td>
	</tr>		
	</table>
	<br>
	<table style='width:100%;background-color:transparent;background-image:none'>
	<tr>
		<td valign='top' width=1%><img src='img/$clamav_img'></td>
		<td valign='middle'><span style='font-size:13px;font-weight:bold'>{APP_CLAMAV_MILTER}</span></td>
		<td valign='middle'><span style='font-size:13px;font-weight:bold'>$text_clamav</span></td>
	</tr>
	<tr>
		<td valign='top' width=1%><img src='img/$spamass_img'></td>
		<td valign='middle'><span style='font-size:13px;font-weight:bold'>{APP_SPAMASS_MILTER}</span></td>
		<td valign='middle'><span style='font-size:13px;font-weight:bold'>$spamass_text</span></td>
	</tr>	

	<tr>
		<td colspan=3 align='right'>$button</td>
	</table>
	
	
	<br>
	
	";
	
	
	$html="<div style='width:283px;height:230px;background-repeat:no-repeat;background-image:url(img/$firstbg);padding:10px;' 
		onmouseover='javascript:this.style.backgroundImage=\"url(img/bg-coinds-blanc-300.png)\"'
		onmouseout='javascript:this.style.backgroundImage=\"url(img/$firstbg)\"'
		>$text
		
		&nbsp;
		</div>
	
	
	";
	return $html;
	
}

function bundle_barre($pourc){
	
	$html="<div style='width:100px;background-color:white;padding-left:0px;border:1px solid #5DD13D'>
			<div style='width:{$pourc}px;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:#5DD13D'><strong>{$pourc}%</strong></div>
		</div>
";
	
	return $html;
}



?>
