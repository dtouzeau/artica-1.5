<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.mysql.inc');
$GLOBALS["ICON_FAMILY"]="samba";	
	
$usersmenus=new usersMenus();
if(!$usersmenus->AsAnAdministratorGeneric){
	$tpl=new templates();
	$alert=$tpl->_ENGINE_parse_body('{ERROR_NO_PRIVS}');
	echo "alert('$alert');";
	die();	
}

if(isset($_GET["js"])){popup_js();exit;}
if(isset($_GET["popup"])){popup_index();exit;}
if(isset($_GET["main"])){main_switch();exit;}
if(isset($_GET["status"])){services_status();exit;}
if(isset($_GET["popup-milter-behavior"])){milter_behavior_popup();exit;}
if(isset($_GET["enable_as_modules"])){antispam_popup_save();exit;}
if(isset($_GET["enable_milter"])){milter_behavior_save();exit;}
if(isset($_GET["smb-status"])){services_status();exit;}

die("wrong query");



function popup_js(){
	if(GET_CACHED(__FILE__,__FUNCTION__,null)){return true;}
	$page=CurrentPageName();
	$js1=file_get_contents("js/samba.js");
	$js2=file_get_contents("js/json.js");
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{APP_SAMBA}');
	$idmd='SambaIndex_';
	
	

	//smb-status //dialogS_content
	
$html="var {$idmd}timerID  = null;
var {$idmd}timerID1  = null;
var {$idmd}tant=0;
var {$idmd}reste=0;
var {$idmd}timeout=0;

	function {$idmd}demarre(){
		if(!document.getElementById('main_samba_config')){return;}
		
		{$idmd}tant = {$idmd}tant+1;
		{$idmd}reste=10-{$idmd}tant;
		if ({$idmd}tant < 10 ) {                           
			{$idmd}timerID = setTimeout(\"{$idmd}demarre()\",3000);
	      } else {
			{$idmd}tant = 0;
			{$idmd}ChargeLogs();
			{$idmd}demarre();                                
	   }
	}


	function {$idmd}ChargeLogs(){
		LoadAjax('smb-status','$page?smb-status=yes');
	}	
	
	function refresh_services(){
		{$idmd}ChargeLogs();
	}
	
	function StartPage(){
		$('#BodyContent').load('$page?popup=yes');
		
	
		//YahooWinS(750,'$page?popup=yes','$title');
		//setTimeout(\"{$idmd}WaitToStart();\",500);	
		
	}
	
	function {$idmd}WaitToStart(){
		{$idmd}timeout={$idmd}timeout+1;
		if({$idmd}timeout>30){alert('time-out');return ;}
		if(!document.getElementById('smb-status')){
			setTimeout(\"{$idmd}WaitToStart();\",800);
			return;
		}
		{$idmd}timeout=0;
		setTimeout(\"{$idmd}ChargeLogs();\",500);	
		setTimeout(\"{$idmd}demarre()\",500);	
	
	}
	
	$js1
	$js2
	StartPage();
	
	";
	


SET_CACHED(__FILE__,__FUNCTION__,null,$html);	
echo $html;
	
	
}

function popup_index(){
$html=main_tabs();
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
}




function main_index(){
$users=new usersMenus();

if($users->SAMBA_INSTALLED){
	$samba="
	<H3>{APP_SAMBA} V$users->SAMBA_VERSION</H3>
	<hr>
	<div id='samba_status'></div>
	<script>
		LoadAjax('samba_status','samba.index.php?status=yes');
	</script>
	";
	
	
	
}

if($users->PUREFTP_INSTALLED){
	$pure="
	<H3>{APP_PUREFTPD}</H3>
	<hr>
	".pureftpd_status();
	
}

$pure=$pure."<br>
<H3>{APP_RSYNC}</H3><hr>
" .rsync_status();




	$html="<H1>{fileshare}</H1>
	<table style='width:100%'>
	<tr>
		<td valign='top'>$samba</td>
		<td valign='top'>$pure</td>
	</tr>
	</table>
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}



function main_tabs(){
	//$html=GET_CACHED(__FILE__,__FUNCTION__,null,TRUE);
	if($html<>null){return $html;}
	$page=CurrentPageName();
	$users=new usersMenus();
	$tpl=new templates();
	
	if($users->SAMBA_INSTALLED){
		$array["net_share"]='{net_share}';
		$array["APP_SAMBA"]='{APP_SAMBA}';
		
	}
	if($users->PUREFTP_INSTALLED){$array["ftp_share"]='{ftp_share}';}
	$array["rsync"]="{APP_RSYNC}";
	if($users->GLUSTER_INSTALLED){$array["gluster"]='{APP_GLUSTER}';}
	
	
	
	while (list ($num, $ligne) = each ($array) ){
		
		if($num=="APP_SAMBA"){
			$tab[]= $tpl->_ENGINE_parse_body("<li><a href=\"samba.index.php?script=smbpop&js-in-line=yes\"><span>$ligne</span></a></li>\n");
			continue;
		}			
		
		if($num=="gluster"){
			$tab[]="<li><a href=\"gluster.samba.php\"><span>$ligne</span></a></li>\n";
			continue;
		}
		
		$tab[]="<li><a href=\"$page?main=$num\"><span>$ligne</span></a></li>\n";
			
		}
	
	
	

	$html="
		<div id='main_samba_config' style='background-color:white'>
		<ul>
		". implode("\n",$tab). "
		</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_samba_config').tabs();
			

			});
		</script>
	
	";
		
	$tpl=new templates();
	$html=$tpl->_ENGINE_parse_body($html);
	SET_CACHED(__FILE__,__FUNCTION__,null,$html);
	return $html;
		
	
}

function main_switch(){
	
	
	//if(GET_CACHED(__FILE__,__FUNCTION__,$_GET["main"])){return null;}
	
	switch ($_GET["main"]) {
		case "index":
				$html=main_index();
				SET_CACHED(__FILE__,__FUNCTION__,$_GET["main"],$html);
				echo $html;
			break;
		case "net_share":
				$html=main_samba();
				SET_CACHED(__FILE__,__FUNCTION__,$_GET["main"],$html);
				echo $html;
				break;
		case "ftp_share":
				$html=main_pureftpd();
				SET_CACHED(__FILE__,__FUNCTION__,$_GET["main"],$html);
				echo $html;
				break;
			
		case "net_share2":
				$html=main_samba_content();
				SET_CACHED(__FILE__,__FUNCTION__,$_GET["main"],$html);
				echo $html;
				break;				
			
		case "rsync":
				$html=main_rsync();
				SET_CACHED(__FILE__,__FUNCTION__,$_GET["main"],$html);
				echo $html;
				break;
				
			
				
		default:
				$html=main_samba();
				$_GET["main"]="NULL";
				SET_CACHED(__FILE__,__FUNCTION__,$_GET["main"],$html);
				echo $html;
				$_GET["main"]=null;
				break;
			
			
	}
	
}


function cookies_main(){
	
	if($_GET["main"]==null){
		if($_COOKIE["fileshare_index_main"]<>null){
			$_GET["main"]=$_COOKIE["fileshare_index_main"];
		}else{
			$_GET["main"]="net_share";
		}
	}else{
		setcookie('fileshare_index_main',$_GET["main"], (time() + 3600));

	}
	
}


function antispam_script(){
	
	$page=CurrentPageName();
	$html=
	"YahooWin2(550,'$page?popup-antispam=yes','Anti-spam',''); 
	
	
var X_ApplyKasSpamas= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	YahooWin2(550,'$page?popup-antispam=yes','Anti-spam','');
	}	
	
	function ApplyKasSpamas(){
		var XHR = new XHRConnection();
	
		if(document.getElementById('enable_spamassassin')){
			XHR.appendData('enable_spamassassin',document.getElementById('enable_spamassassin').value);
			document.getElementById('img_enable_spamassassin').src='img/wait_verybig.gif';
			
		}
		
		if(document.getElementById('enable_kaspersky_as')){
			XHR.appendData('enable_kaspersky_as',document.getElementById('enable_kaspersky_as').value);
			document.getElementById('img_enable_kaspersky_as').src='img/wait_verybig.gif';
		}	
		
		XHR.appendData('enable_as_modules','yes');	
		XHR.sendAndLoad('$page', 'GET',X_ApplyKasSpamas);	
		
	
	}";
	return  $html;
	
}



function services_status(){
	$tpl=new templates();
	
	if(is_file("ressources/logs/status.samba.html")){
		$datas=file_get_contents("ressources/logs/status.samba.html");
		echo $tpl->_ENGINE_parse_body($datas);
		return null;
	}
	
	$ini=new Bs_IniHandler();
	$user=new usersMenus();
	$sock=new sockets();
	
	$ini->loadString($sock->getfile('daemons_status',$_GET["hostname"]));	
	if($user->SAMBA_INSTALLED){
		$samba_status=DAEMON_STATUS_ROUND("SAMBA_SMBD",$ini);
	}
	$pureftpd_status=DAEMON_STATUS_ROUND("PUREFTPD",$ini);
		$infos=samba_infos();	
	echo $tpl->_ENGINE_parse_body("<div style='width:270px'>$samba_status<br>$pureftpd_status<br>$infos</div>");
	}
	
function pureftpd_status(){

	$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}		
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString($sock->getfile('daemons_status',$_GET["hostname"]));	
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body(DAEMON_STATUS_ROUND("PUREFTPD",$ini));
	
	
	
}	



	
function main_kavsamba_status(){
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString($sock->getfile('kstatus',$_GET["hostname"]));
	$status=DAEMON_STATUS_ROUND("KAV4SAMBA",$ini);

	$tpl=new templates();
	return $tpl->_ENGINE_parse_body("$status");	
}


function samba_infos(){
	$users=new usersMenus();
	
	if($users->KAV4SAMBA_INSTALLED){
		
		$infos=main_kavsamba_status();
	}	
	
	
	return  $infos;
	
}
	
function main_samba(){
	cookies_main();
	$page=CurrentPageName();
	$html="
	<div id='main_samba_content'></div>
	
	<script>
		LoadAjax('main_samba_content','$page?main=net_share2');
	</script>
	";
	
	return $html;
	
}	
	

function main_samba_content(){
	
cookies_main();
	$users=new usersMenus();
	$users->LoadModulesEnabled();
	
	$samba=Paragraphe('64-samba-grey.png','{APP_SAMBA}','{feature_not_installed}','',null,210,null,0,false);
	$share=Paragraphe('64-share-grey.png','{SHARE_FOLDER}','{feature_not_installed}','',null,210,null,0,false);
	$audit=Paragraphe('folder-64-spamassassin-grey.png','{samba_audit}','{feature_not_installed}',"",null,210,null,0,false);
	$usb=Paragraphe('usb-share-64.png','{usb_share}','{feature_not_installed}',"",null,210,null,0,false);
	$usb=Paragraphe('usb-share-64.png','{usb_share}','{feature_not_installed}',"",null,210,null,0,false);
	$dropbox=Paragraphe('dropbox-64-grey.png','{APP_DROPBOX}','{APP_DROPBOX_TEXT}',"",null,210,null,0,false);
	if($users->DROPBOX_INSTALLED){
		$dropbox=Paragraphe('dropbox-64.png','{APP_DROPBOX}','{APP_DROPBOX_TEXT}',"javascript:Loadjs('samba.dropbox.php')",null,210,null,0,false);
	}
	
	
	
	if($users->SAMBA_INSTALLED){
		$cups=Paragraphe('64-printer-grey.png',"{APP_CUPS}",'{feature_not_installed}');
		$greyhole=Paragraphe('folder-64-artica-backup-grey.png',"{APP_GREYHOLE}",'{feature_not_installed}');
		$samba=Paragraphe('64-samba.png','{APP_SAMBA}','{APP_SAMBA_TEXT}',"javascript:Loadjs('samba.index.php?script=smbpop')",null,210,null,0,false);
		$share=Paragraphe('64-share.png','{SHARE_FOLDER}','{SHARE_FOLDER_TEXT}',"javascript:Loadjs('SambaBrowse.php');",null,210,null,0,false);
		$audit=Paragraphe('folder-64-spamassassin.png','{samba_audit}','{samba_audit_text}',"new:smb-audit/index.php",null,210,null,0,false);
		$usb=Paragraphe('usb-share-64.png','{usb_share}','{usb_share_text}',"javascript:Loadjs('usb.browse.php')",null,210,null,0,false);
		$NTconfig=Buildicon64("DEF_ICO_NTPOL");
		$sync=Buildicon64('DEF_ICO_SAMBA_SYNCHRO');
		$roaming=Buildicon64('DEF_ICO_ROAMINGP');
		if($users->CUPS_INSTALLED){$cups=Paragraphe('64-printer.png',"{APP_CUPS}",'{APP_CUPS_TEXT}',"javascript:Loadjs('cups.index.php')",null,210,null,0,false);}
		$plugins=Paragraphe("folder-lego.png","{plugins}","{enable_plugins_text}","javascript:Loadjs('samba.plugins.php')");
		$activedirectory=Paragraphe("folder-import-ad-64.png","{ad_samba_member}","{ad_samba_member_text}","javascript:Loadjs('ad.connect.php')");
		if($users->GREYHOLE_INSTALLED){$greyhole=Paragraphe('folder-64-artica-backup.png',"{APP_GREYHOLE}",'{enable_grehole_text}',"javascript:Loadjs('greyhole.php')");}
		$q=new mysql();
		$count=$q->COUNT_ROWS("smbstatus_users", "artica_events");
		if($count>0){
			$members_connected=Paragraphe("user-group-64.png", "$count {members_connected}", "{members_connected_samba_text}","javascript:Loadjs('samba.smbstatus.php')",null,210,null,0,false);
		}
		
	}
		

		
		$tr[]=$members_connected;
		$tr[]=$share;
		$tr[]=$greyhole;
		$tr[]=$usb;
		$tr[]=$dropbox;
		$tr[]=$plugins;
		$tr[]=$samba;
		$tr[]=$roaming;
		$tr[]=$activedirectory;
		$tr[]=$NTconfig;
		$tr[]=$cups;	
		$tr[]=$sync;
		$tr[]=$audit;
	

$tables[]="<table style='width:100%'><tr>";
$t=0;
while (list ($key, $line) = each ($tr) ){
		$line=trim($line);
		if($line==null){continue;}
		$t=$t+1;
		$tables[]="<td valign='top'>$line</td>";
		if($t==3){$t=0;$tables[]="</tr><tr>";}
		}

if($t<3){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top'>&nbsp;</td>";				
	}
}
				
$tables[]="</table>";	
$html=implode("\n",$tables);	
$body="<div style='width:700px'>$html</div>";		
$sock=new sockets();
	$SambaEnabled=$sock->GET_INFO("SambaEnabled");
	if($SambaEnabled==null){$SambaEnabled=1;}	
	$disable_samba=Paragraphe("server-disable-64.png",'{enable_disable_samba}','{enable_disable_samba_text}',
	"javascript:Loadjs('samba.disable.php');",'{enable_disable_samba_text}',210,null,1);		
	if($SambaEnabled==0){
		$body="<center style='margin:10px'>$disable_samba</center>";
	}
	
	$status="<div id='smb-status' style='width:290px;height:350px;overflow:auto'></div>
				<div style='width:100%;text-align:right'>" . imgtootltip("refresh-24.png","{refresh}","refresh_services()")."</div>";
		
$html="$body";	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);	
	
	
}
function main_pureftpd(){
//$status=pureftpd_status();	
cookies_main();
	$users=new usersMenus();
	$tpl=new templates();
	
	$users->LoadModulesEnabled();
	$pure=Paragraphe('64-pureftpd-grey.png','{APP_PUREFTPD}','{feature_not_installed}','');
	$events=Paragraphe('folder-64-spamassassin-grey.png','{events}','{feature_not_installed}','');
	$apply=Paragraphe('system-64-grey.png','{apply}','{feature_not_installed}','');
	$purewho=Paragraphe('folder-rules2-64-info-grey.png','{current_connections}','{feature_not_installed}','');
	
	if($users->PUREFTP_INSTALLED){
		$pure=Paragraphe('64-pureftpd.png','{APP_PUREFTPD}','{APP_PUREFTPD_TEXT}',"javascript:Loadjs('pureftp.index.php?js=yes')");
		$events=Paragraphe('folder-64-spamassassin.png','{events}','{events_text}',"javascript:Loadjs('pureftp.events.php')");
		$apply=Paragraphe('system-64.png','{apply}','{apply_config_pureftpd}',"javascript:ApplyConfig('pure-ftpd');");
		$purewho=Paragraphe('folder-rules2-64-info.png','{current_connections}','{current_connections_text}',"javascript:Loadjs('pureftp.events.php?who=yes')");
		
	}
	
	
$html="$tab
<table style='width:100%'>
<tr>
<td valign='top'>

		<table>
			<tr>
			<td valign='top' >$pure</td>
			<td valign='top' >$events</td>
			<td valign='top'>$purewho</td>
			</tr>	
			<tr>
			<td valign='top' >$apply</td>
			<td valign='top' >$status</td>
			<td valign='top' >&nbsp;</td>
			</tr>					
		</table>
</td>
<td valign='top' style='width:1%'>$pureftp_error
</td>
</tr>
</table>";	
	
	return $tpl->_ENGINE_parse_body($html);	
	
	
}


function rsync_status(){
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString($sock->getfile('rsyncstatus',$_GET["hostname"]));
	$status=DAEMON_STATUS_ROUND("APP_RSYNC",$ini)."<br>".DAEMON_STATUS_ROUND("APP_RSYNC_STUNNEL",$ini);
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($status);	
	}
	
function main_rsync(){
	
	$view_events_server=Paragraphe("routing-domain-relay-events.png","{APP_RSYNC_SERVER_LOG}","{APP_RSYNC_SERVER_LOG_TEXT}","javascript:Loadjs('rsync.server.php?js-logs=yes')");	
	$server_config=Paragraphe('routing-domain-relay.png','{APP_RSYNC}','{APP_RSYNC_SERVER_TEXT}',"javascript:Loadjs('rsync.server.php')");
	$server_folders=Paragraphe('storage-64.png','{APP_RSYNC_FOLDERS}','{APP_RSYNC_FOLDERS_TEXT}',"javascript:Loadjs('rsync.server.folders.php')");
	
	$enable=Paragraphe('disk_share_enable-64.png','{APP_RSYNC_SERVER_ENABLE}','{APP_RSYNC_SERVER_ENABLE_TEXT}',"javascript:Loadjs('rsync.server.enable.php')");
	
	
	$html="<table style='width:100%'>
	<tr>
		<td valign='top'>$server_config</td>
		<td valign='top'>$view_events_server</td>
		<td valign='top'>$server_folders</td>
	</tr>
	<tr>
		<td valign='top'>$enable</td>
		<td valign='top'>&nbsp;</td>
		<td valign='top'>&nbsp;</td>
	</table>
		
	
	
	";
	
	$tpl=new templates();
	echo  $tpl->_ENGINE_parse_body($html,"dar.index.php");
}


?>	