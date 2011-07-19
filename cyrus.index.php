<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.mysql.inc');	
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.cyrus.inc');
	include_once('ressources/class.cron.inc');
	
	$users=new usersMenus();
	if(!$users->AsPostfixAdministrator){
		$tpl=new templates();
		$error=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}	
	
	if(isset($_GET["MailBoxesDomainList"])){MailBoxesDomainList_js();exit;}
	if(isset($_GET["main"])){main_switch();exit;}
	if(isset($_GET["status"])){echo main_status();exit;}
	if(isset($_GET["service_imapssl_enabed"])){ Save_services();exit;}
	if(isset($_GET["allowallsubscribe"])){Save_impad();exit;}
	if(isset($_GET["DeleteRealMailBox"])){DeleteRealMailBox();exit;}
	if(isset($_GET["rebuild_mailboxes"])){rebuild_mailboxes();exit;}
	if(isset($_GET["cyrrepair"])){cyrrepair_js();exit;}
	if(isset($_GET["cyrrepair_popup"])){cyrrepair_popup();exit;}
	if(isset($_GET["cyrrepair_launch"])){cyrrepair_launch();exit;}
	if(isset($_GET["cyrrepair_status"])){cyrrepair_status();exit;}
	if(isset($_GET["popup-index"])){popup_index();exit;}
	
	
	if(isset($_GET["squatter-js"])){squatter_js();exit;}
	if(isset($_GET["squatter-popup"])){squatter_popup();exit;}
	if(isset($_GET["CyrusEnableSquatter"])){squatter_save();exit;}
	
	if(isset($_GET["ipurge-js"])){ipurge_js();exit;}
	if(isset($_GET["ipurge-popup"])){ipurge_popup();exit;}
	if(isset($_GET["CyrusiPurgeTime"])){ipurge_save();exit;}
	js();

	

	
	
function squatter_js(){
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{SQUATTER_SERVICE}');
	$page=CurrentPageName();
	$prefix="cyrus_index_php";
$html="

function SQUATTER_SERVICE(){
	YahooWin('600','$page?squatter-popup=yes','$title');
}



SQUATTER_SERVICE();";
	
	echo $html;
}

function ipurge_js(){
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{IPURGE_SERVICE}');
	$page=CurrentPageName();
	$prefix="cyrus_index_php";
$html="

function IPURGE_SERVICE(){
	YahooWin('553','$page?ipurge-popup=yes','$title');
}

var x_IPURGE_SERVICE_SAVE= function (obj) {
		IPURGE_SERVICE();
	}	

function IPURGE_SERVICE_SAVE(){
		var XHR = new XHRConnection();
		var CyrusiPurgeTime=document.getElementById('H').value+document.getElementById('M').value;
		XHR.appendData('CyrusiPurgeTime',CyrusiPurgeTime);
		XHR.appendData('CyrusiPurgeDays',document.getElementById('CyrusiPurgeDays').value);
		XHR.appendData('CyrusEnableiPurge',document.getElementById('CyrusEnableiPurge').value);
		
		if(document.getElementById('CyrusiPurgeSent').checked){
			XHR.appendData('CyrusiPurgeSent',1);
		}else {
			XHR.appendData('CyrusiPurgeSent',0);
		}
		
		if(document.getElementById('CyrusiPurgeJunk').checked){
			XHR.appendData('CyrusiPurgeJunk',1);
		}else {
			XHR.appendData('CyrusiPurgeJunk',0);
		}		
		
		if(document.getElementById('CyrusiPurgeTrash').checked){
			XHR.appendData('CyrusiPurgeTrash',1);
		}else {
			XHR.appendData('CyrusiPurgeTrash',0);
		}
				
		document.getElementById('img_CyrusEnableiPurge').src='img/wait_verybig.gif';
		
		
		
		
		
		XHR.sendAndLoad('$page', 'GET',x_IPURGE_SERVICE_SAVE);
	}
	

IPURGE_SERVICE();";
	
	echo $html;	
}

function ipurge_save(){
	$sock=new sockets();
	$sock->SET_INFO('CyrusEnableiPurge',$_GET["CyrusEnableiPurge"]);
	$sock->SET_INFO('CyrusiPurgeDays',$_GET["CyrusiPurgeDays"]);
	$sock->SET_INFO('CyrusiPurgeTime',$_GET["CyrusiPurgeTime"]);
	
	$sock->SET_INFO('CyrusiPurgeSent',$_GET["CyrusiPurgeSent"]);
	$sock->SET_INFO('CyrusiPurgeJunk',$_GET["CyrusiPurgeJunk"]);
	$sock->SET_INFO('CyrusiPurgeTrash',$_GET["CyrusiPurgeTrash"]);
	
	$sock->getFrameWork("cmd.php?reconfigure-cyrus=yes");	
}



function ipurge_popup(){
	for($i=0;$i<60;$i++){
		$t=$i;
		if($t<10){$t="0$t";}
		$mins[$t]=$t;
		
	}
	
	for($i=0;$i<24;$i++){
		$t=$i;
		if($t<10){$t="0$t";}
		$hours[$t]=$t;
		
	}
	$hhm[15]=15;
	$hhm[30]=30;
	$hhm[40]=40;
	$hhm[60]=60;
	$hhm[90]=90;
	$hhm[120]=120;
	$hhm[180]=180;
	$hhm[240]=240;
	$hhm[512]=512;
	
	
	
	$sock=new sockets();
	$CyrusEnableiPurge=$sock->GET_INFO('CyrusEnableiPurge');
	$CyrusiPurgeTime=$sock->GET_INFO('CyrusiPurgeTime');
	$CyrusiPurgeDays=$sock->GET_INFO("CyrusiPurgeDays");
	
	$CyrusiPurgeSent=$sock->GET_INFO("CyrusiPurgeSent");
	$CyrusiPurgeJunk=$sock->GET_INFO("CyrusiPurgeJunk");
	$CyrusiPurgeTrash=$sock->GET_INFO("CyrusiPurgeTrash");
	
	
if($CyrusiPurgeSent==null){$CyrusiPurgeSent="1";}
if($CyrusiPurgeJunk==null){$CyrusiPurgeJunk="1";}
if($CyrusiPurgeTrash==null){$CyrusiPurgeTrash="1";}

	if($CyrusiPurgeTime==null){$CyrusiPurgeTime="0320";}
	if($CyrusiPurgeDays==null){$CyrusiPurgeDays=60;}
	$CyrusEnableiPurge=Paragraphe_switch_img('{enable_this_option}','{IPURGE_SERVICE_TEXT}','CyrusEnableiPurge',$CyrusEnableiPurge);	
	$days=Field_array_Hash($hhm,"CyrusiPurgeDays",$CyrusiPurgeDays);
	$hh=substr($CyrusiPurgeTime,0,2);
	$mm=substr($CyrusiPurgeTime,2,2);
	$H=Field_array_Hash($hours,"H",$hh);
	$M=Field_array_Hash($mins,"M",$mm);
	
	
	
	
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top'>
			$CyrusEnableiPurge
		</td>
		<td valign='top'>
			<table style='width:100%'>
			<tr>
				<td valign='top' class=legend>{older_days}:</td>
				<td>$days</td>
			</tr>
			<tr>
				<td valign='top' class=legend nowrap>{runat}:</td>
				<td>$H&nbsp;h&nbsp;$M&nbsp;mn ({$hh}h$mm)</td>
			</tr>
			<td valign='top' class=legend>{items}:</td>
			<td valign='top'><hr>
				<table style='width:100%'>
					<tr>
						<td class=legend nowrap>{inbox_trash}</td>
						<td >" .Field_checkbox('CyrusiPurgeTrash',1,$CyrusiPurgeTrash)."</td>
					</tr>
					<tr>
						<td class=legend nowrap>{inbox_sent}</td>
						<td >" .Field_checkbox('CyrusiPurgeSent',1,$CyrusiPurgeSent)."</td>
					</tr>	
					<tr>
						<td class=legend nowrap>{inbox_junk}</td>
						<td >" .Field_checkbox('CyrusiPurgeJunk',1,$CyrusiPurgeJunk)."</td>
					</tr>	
				</table>								
		</table>
	</td>
	</tr>
	</table>		
	<div style='text-align:right'><hr>". button("{edit}","IPURGE_SERVICE_SAVE()")."</div>			
	
	";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
	
}




function squatter_popup(){
	
	$hhm[15]=15;
	$hhm[30]=30;
	$hhm[40]=40;
	$hhm[60]=60;
	$hhm[120]=120;
	$hhm[180]=180;
	$hhm[240]=240;
	$hhm[512]=512;
	
	$page=CurrentPageName();
	$sock=new sockets();
	$CyrusEnableSquatter=$sock->GET_INFO('CyrusEnableSquatter');
	$CyrusSquatterRindex=$sock->GET_INFO('CyrusSquatterRindex');
	if($CyrusSquatterRindex==null){$CyrusSquatterRindex=120;}
	$CyrusEnableSquatter=Paragraphe_switch_img('{enable_squatter_service}','{SQUATTER_SERVICE_EXPL}','CyrusEnableSquatter',$CyrusEnableSquatter,null,450);
	$CyrusSquatterRindexEveryDay=$sock->GET_INFO('CyrusSquatterRindexEveryDay');
	if($CyrusSquatterRindexEveryDay==null){$CyrusSquatterRindexEveryDay=0;}
	$CyrusSquatterRindexUseScheduleTime=$sock->GET_INFO('CyrusSquatterRindexUseScheduleTime');
	
	if(preg_match("#([0-9]+):([0-9]+)#",$CyrusSquatterRindexUseScheduleTime,$re)){
		$sqat_h=intval($re[1]);
		$sqat_m=intval($re[2]);
	}
	
	$cron=new cron_macros();
	
	$table="
	<table style='width:100%'>
	<tr>
		<td class=legend>{squatter_reindex}:</td>
		<td>". Field_array_Hash($hhm,"CyrusSquatterRindex",$CyrusSquatterRindex,null,null,0,"font-size:13px;padding:3px")."</td>
		<td width=1% nowrap style='font-size:13px'>&nbsp;mn</td>
	</tr>

	</table>
<table style='width:100%'>
	
	<tr>
		<td class=legend>{use_this_schedule}:</td>
		<td>". Field_checkbox("CyrusSquatterRindexEveryDay",1,$CyrusSquatterRindexEveryDay,"CyrusSquatterRindexEveryDayCheck()")."</td>
	</tr>
	<tr>	
		<td class=legend>{every_day_at}:</td>
		<td>
		<table>
		<tr>
			<td>". Field_array_Hash($cron->cron_hours,"squat_h",$sqat_h,null,null,0,"font-size:13px;padding:3px")."</td>
			<td>". Field_array_Hash($cron->cron_mins,"squat_m",$sqat_m,null,null,0,"font-size:13px;padding:3px")."</td>
		</tr>
		</table>
		</td>
	</tr>		
</table>	
	";
	
	$html="
	<div id='squetterid'>
	<table style='width:100%'>
	<tr>
		<td valign='top'>$CyrusEnableSquatter</td>
	</tr>
		<td valign='top'>$table</td>
	</tr>
	<tr>

		<td colspan=2 align='right'><hr>". button("{apply}","SQUATTER_SAVE();")."</td>
	</tr>
	</table>
	</div>
	
	<script>
		function CyrusSquatterRindexEveryDayCheck(){
			document.getElementById('CyrusSquatterRindex').disabled=true;
			document.getElementById('squat_h').disabled=true;
			document.getElementById('squat_m').disabled=true;
			if(document.getElementById('CyrusSquatterRindexEveryDay').checked){
				document.getElementById('squat_h').disabled=false;
				document.getElementById('squat_m').disabled=false;			
			}else{
			document.getElementById('CyrusSquatterRindex').disabled=false;
			}
		}
		
var x_SQUATTER_SAVE= function (obj) {
		SQUATTER_SERVICE();
	}	

function SQUATTER_SAVE(){
		var XHR = new XHRConnection();
		XHR.appendData('CyrusEnableSquatter',document.getElementById('CyrusEnableSquatter').value);
		XHR.appendData('CyrusSquatterRindex',document.getElementById('CyrusSquatterRindex').value);
		XHR.appendData('squat_h',document.getElementById('squat_h').value);
		XHR.appendData('squat_m',document.getElementById('squat_m').value);
		if(document.getElementById('CyrusSquatterRindexEveryDay').checked){
		XHR.appendData('CyrusSquatterRindexEveryDay',1);}else{XHR.appendData('CyrusSquatterRindexEveryDay',0);}
		
		
		document.getElementById('squetterid').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
		XHR.sendAndLoad('$page', 'GET',x_SQUATTER_SAVE);
	}		
	
	CyrusSquatterRindexEveryDayCheck()
</script>";
	
	
	
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
}

function squatter_save(){
	$sock=new sockets();
	$sock->SET_INFO('CyrusEnableSquatter',$_GET["CyrusEnableSquatter"]);
	$sock->SET_INFO('CyrusSquatterRindex',$_GET["CyrusSquatterRindex"]);
	if(strlen($_GET["squat_h"]==1)){$_GET["squat_h"]='0'.$_GET["squat_h"];}
	if(strlen($_GET["squat_m"]==1)){$_GET["squat_m"]='0'.$_GET["squat_m"];}
	
	$sock->SET_INFO('CyrusSquatterRindex',$_GET["CyrusSquatterRindex"]);
	$sock->SET_INFO('CyrusSquatterRindexEveryDay',$_GET["CyrusSquatterRindexEveryDay"]);
	$sock->SET_INFO("CyrusSquatterRindexUseScheduleTime","{$_GET["squat_h"]}:{$_GET["squat_m"]}");
	$sock->getFrameWork("cmd.php?reconfigure-cyrus=yes");
}
	
	
function js(){
$prefix="cyrus_index_php";
$page=CurrentPageName();
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{APP_CYRUS}');
$addons=file_get_contents("js/edit.user.js");	

$startjs="{$prefix}Loadpage();";

if(isset($_GET["in-front-ajax"])){
	$startjs="{$prefix}LoadpageInFront()";
}

$html="
var {$prefix}timerID  = null;
var {$prefix}tant=0;
var {$prefix}reste=0;
var {$prefix}timeout=0;


function {$prefix}ChargeLogs(){
	LoadAjax('services_status_mbx_cyrus','$page?status=yes&hostname={$_GET["hostname"]}');
	}

function {$prefix}Loadpage(){
	YahooWinS('812','$page?popup-index=yes','$title');
	}
	
function {$prefix}LoadpageInFront(){	
	$('#BodyContent').load('$page?popup-index=yes');
}
	


function SaveFormCyrus(){
LoadAjax('main_config','$page?main=cyrusconf&reload=yes');
}
	
	
$startjs
 
$addons 
 
";
	
	echo $html;
}

function popup_index(){
	echo main_tabs();
}
	
function popup_status(){
	$page=CurrentPageName();
	$html="
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top' align='middle' width=40%>
	
	
	
	<center><img src='img/mailbox-256.png'></center></td>
	<td valign='top'><div id='services_status_mbx_cyrus' style='height:150px'>". main_status() . "</div><br>
	<img src='img/bg-cyrus.jpg' align=left style='margin:5px'>
	<p style='font-size:14px;color:black'>{about_cyrus}</p></td>
	</tr>
	</table>
	
	
	";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
	
	}	

function main_tabs(){
	
	$page=CurrentPageName();
	$array["popup-status"]="{status}";
	
	$array["cyrusconf"]='{services_settings}';
	$array["options"]="{options}";
	$array["tools"]='{tools}';
	$array["logs"]='{events}';	
	//$array["conf"]='{config}';
	$array["cyrquota"]='{cyrquota}';
	$array["mailboxes"]='{mailboxes}';
	
	
	while (list ($num, $ligne) = each ($array) ){
		
		$html[]="<li><a href=\"$page?main=$num&hostname=$hostname\"><span>$ligne</span></a></li>\n";
			
		}	
	
	$tab="<div id=main_config_cyrus style='width:100%;height:680px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_cyrus').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			
			
			});
		</script>";		
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($tab);
	
}


function main_switch(){
	
	switch ($_GET["main"]) {
		case "popup-status":popup_status();exit;break;
		case "options":popup_options();exit;break;
		case "options2":popup_options2();exit;break;
		case "yes":main_config();exit;break;
		case "tools":echo main_tools();exit;break;
		case "logs":main_logs();exit;break;
		case "conf":echo main_conf();exit;break;
		case "cyrquota":echo main_cyrquota();exit;break;
		case "cyrusconf":echo main_cyrusconf();exit;break;
		case "mailboxes":echo main_mailboxes();exit;break;
		case "js_rebuild":echo js_rebuild();exit;break;
		case "cyrreconstruct":echo popup_cyrreconstruct();exit;break;
		case "cyrreconstruct_status":echo cyrreconstruct_status();exit;break;
		
		default:main_cyrusconf();break;
	}
	
	
}

function popup_options(){
$page=CurrentPageName();

$html="
<div id='cyrus_index_popup_options'></div>

<script>
	LoadAjax('cyrus_index_popup_options','$page?main=options2');
</script>
";

echo $html;

}


function popup_options2(){	
	
	
	$page=CurrentPageName();
	$squatter=Paragraphe("squatter-64.png","{SQUATTER_SERVICE}","{SQUATTER_SERVICE_TEXT}","javascript:Loadjs('$page?squatter-js=yes')");
	$ipurge=Paragraphe("ipurge-64.png","{IPURGE_SERVICE}","{IPURGE_SERVICE_TEXT}","javascript:Loadjs('$page?ipurge-js=yes')");
	$cyrus_cluster=Paragraphe('64-cluster-grey.png','{CYRUS_CLUSTER}','{CYRUS_CLUSTER_TEXT}');
	
	$users=new usersMenus();
	if(!$users->cyrus_squatter_exists){
		$squatter=Paragraphe("squatter-disabled-64.png","{SQUATTER_SERVICE}","{SQUATTER_SERVICE_TEXT}");
	}	
	
	if(!$users->cyrus_ipurge_exists){
		$ipurge=Paragraphe("ipurge-64-grey.png","{IPURGE_SERVICE}","{IPURGE_SERVICE_TEXT}");
	}		
	
	$password=Buildicon64('DEF_ICO_CYRUS_PASS');
	
	$Hacks=Paragraphe("Firewall-Secure-64.png","Anti-Hacks","{AntiHacks_text}","javascript:Loadjs('cyrus.hacks.php')");
	
	$changefolder=Paragraphe("folder-move-64.png","{cyrus_change_folder}","{cyrus_change_folder_text}","javascript:Loadjs('cyrus.changefolder.php')");
	
	
	$sieve=Paragraphe("filter-sieve-64.png","{sieve_service}","{sieve_service_options_text}","javascript:Loadjs('cyrus.sieve.php')");
	
	$lmtp=Paragraphe("database-connect-settings-64.png","{cyrus_net_behavior}","{cyrus_net_behavior_text}","javascript:Loadjs('cyrus.lmtp.php')");
	
	$kinit=Paragraphe("windows-server-64-grey.png","{ad_samba_member} ({disabled})","{cyrus_ad_member_text}","");
	if($users->KINIT_INSTALLED){
		$kinit=Paragraphe("windows-server-64.png","{ad_samba_member}","{cyrus_ad_member_text}","javascript:Loadjs('cyrus.ad.php')");
	}
	
	$DB_CONFIG=Paragraphe("database-setup-64.png","{database_configuration}","{CYRUS_DB_CONFIG_TEXT}",
	"javascript:Loadjs('cyrus.db_config.php')"
	);
	
	
	
	
	
	
	if($users->cyrus_syncserver_installed){
		$cyrus_cluster=Paragraphe('64-cluster.png','{CYRUS_CLUSTER}',"CYRUS_CLUSTER_TEXT","javascript:Loadjs('cyrus.clusters.php')");
	}	
	
	
	$tr[]=$lmtp;
	$tr[]=$DB_CONFIG;	
	$tr[]=$Hacks;
	$tr[]=$password;
	$tr[]=$cyrus_cluster;
	$tr[]=$changefolder;
	$tr[]=$squatter;
	$tr[]=$ipurge;
	$tr[]=$sieve;
	$tr[]=$kinit;
	


	
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
	

$tpl=new templates();
	echo $tpl->_ENGINE_parse_body(implode("\n",$tables));	
	
}


function main_tools(){
$users=new usersMenus();
$page=CurrentPageName();
if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}	
$html=

"
<table style='width:100%'>
<tr>
<td>" . Paragraphe("64-folder-tools.png",'{rebuild_mailboxes}',"{rebuild_mailboxes_text}","javascript:Loadjs('$page?main=js_rebuild');")."</td>
<td>" . Paragraphe("64-folder-tools.png",'{repair_database}',"{repair_database_text}","javascript:Loadjs('$page?cyrrepair=yes')")."</td>
</tr>
</table>";

	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);	
	
}

function cyrrepair_js(){
	$tpl=new templates();
	$page=CurrentPageName();
	$ask=$tpl->_ENGINE_parse_body('{repair_database_ask}');
	$title=$tpl->_ENGINE_parse_body('{repair_database}');
	$ask=str_replace("\n",'\n',$ask);
	$html="
	
var cyr_tant=0;
var cyr_reste=0;

function time_repair(){
   cyr_tant = cyr_tant+1;
   cyr_reste=10-cyr_tant;
	if (cyr_tant < 10 ) {                           
       	setTimeout(\"time_repair_status()\",1000);
      } else {
               cyr_tant = 0;
               if(document.getElementById('repair_database_status')){
               	 time_repair_status();
               	 time_repair(); 
               }
               
   }
}

var x_time_repair_status= function (obj) {
	var results=obj.responseText;
	document.getElementById('repair_database_status').innerHTML=results;
	}

function time_repair_status(){
		var XHR = new XHRConnection();
		XHR.appendData('cyrrepair_status','yes');
		XHR.sendAndLoad('$page', 'GET',x_time_repair_status);

}
	
	
function ask(){
		if(confirm('$ask')){
			YahooWin(450,'$page?cyrrepair_popup=yes','$title',''); 
		}
	
	}
	
var x_repair_database= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	time_repair_status();
	time_repair();	
	}
	
	
	function repair_database(){
		var XHR = new XHRConnection();
		XHR.appendData('cyrrepair_launch','yes');
		XHR.sendAndLoad('$page', 'GET',x_repair_database);
	
	}	
	
	ask();
	
	
	";
	echo $html;
}

function cyrrepair_popup(){
$html="
	
	<H1>{repair_database}</H1>
	<p class=caption>{repair_database_text}<p>
	
	<center>
	<input type='button' OnClick=\"javascript:repair_database();\" value='{repair_database}'>&nbsp;<input type='button' OnClick=\"javascript:time_repair_status();\" value='{refresh}'>
	</center>
	<div id='repair_database_status'></div>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function cyrrepair_status(){
	
if(is_file("ressources/logs/ctlcyrusdb")){
		$html="<textarea style='width:100%;height:250px'>".file_get_contents("ressources/logs/ctlcyrusdb")."</textarea>";
		echo $html;
	}else{
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("<div style='padding:3px' class=legend><strong>{cyrreconstruct_waiting}</strong></div>");
	}	
	
	
}

function cyrrepair_launch(){
	$sock=new sockets();
	$sock->getfile('ctlcyrusdb');
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');
}


function js_rebuild(){
	$page=CurrentPageName();
	
	$html="
	
var timerID2  = null;
var tant1=0;
var reste1=0;

function cyrdemarre(){
   tant1 = tant1+1;
   reste1=10-tant1;
	if (tant1 < 10 ) {                           
      timerID2 = setTimeout(\"cyrdemarre()\",1000);
      } else {
               tant1 = 0;
               if(document.getElementById('cyrreconstruct_status')){
               	 parse();
               	 cyrdemarre(); 
               }
               
   }
}


var x_parse= function (obj) {
	var results=obj.responseText;
	document.getElementById('cyrreconstruct_status').innerHTML=results;
}

function parse(){
	var XHR = new XHRConnection();
	XHR.appendData('main','cyrreconstruct_status');
	XHR.sendAndLoad('$page', 'GET',x_parse);	
	}
	
	
	
	YahooWin(450,'$page?main=cyrreconstruct','tools',''); 
	cyrdemarre();
	
var x_rebuild_mailboxes= function (obj) {
	var results=obj.responseText;
	alert(results);
}
		
	
function rebuild_mailboxes(){
	var XHR = new XHRConnection();
	XHR.appendData('rebuild_mailboxes','yes');
	XHR.sendAndLoad('$page', 'GET',x_rebuild_mailboxes);	
}
	";
	
	
	return $html;
}

function popup_cyrreconstruct(){
	$html="
	
	<H1>{rebuild_mailboxes}</H1>
	<p class=caption>{rebuild_mailboxestext}<p>
	
	<center>
	<input type='button' OnClick=\"javascript:rebuild_mailboxes();\" value='{rebuild_mailboxes}'>
	
	</center>
	<div id='cyrreconstruct_status'></div>
	";
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
}


function main_status(){

$users=new usersMenus();
			
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString(base64_decode($sock->getFrameWork('cmd.php?cyrus-imap-status=yes')));	
	$status=DAEMON_STATUS_ROUND("CYRUSIMAP",$ini,null);
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($status);
	
}
function main_conf(){
	$cyr=new cyrus_conf();
	
	$datas=$cyr->impadconf;
	$datas=htmlspecialchars($datas);
	$tbl=explode("\n",$datas);
	
	while (list ($num, $ligne) = each ($tbl) ){
		if(trim($ligne)==null){continue;}
		$t=$t."<div><code>$ligne</code></div>";
		
	}
	
	$html="
	<div style='padding:10px;margin:10px;border: 1px dotted #CCCCCC;height:300px;overflow:auto'>
	$t
	</div>
	";
	$tpl=new templates();
	echo RoundedLightWhite($tpl->_ENGINE_parse_body($html));	
}

function main_config(){
	
$sql="SELECT * FROM events WHERE event_id=4 ORDER BY zDate DESC LIMIT 0,100";
$my=new mysql();

$html="<div style='width:100%;height:300px;overflow:auto'><table style='width:99%'>";


$resultat=$my->QUERY_SQL($sql,'artica_events');
	while($ligne=@mysql_fetch_array($resultat,MYSQL_ASSOC)){
	
	switch ($ligne["event_type"]) {
		case 0:$img="icon_mini_warning.gif";break;
		case 1:$img="icon-mini-ok.gif";break;
		case 2:$img="icon-mini-info.gif";break;
		default:$img="icon-mini-info.gif";break;
	}
		
	  $html=$html . "<tr ". CellRollOver().">
	  <td width=1%><img src='img/$img'></td>
	  <td width=1% nowrap>{$ligne["zDate"]}</td>
	  <td>{$ligne["text"]}</td>
	  </tr>";
	  }

	 $html=$html ."</table>";
	 $html=RoundedLightWhite($html);
	 
	 $page="$html</div>
	 ";
	 
	 
	$tpl=new templates();
	echo RoundedLightWhite($tpl->_ENGINE_parse_body($page));
	
}



function main_cyrusconf(){
	$cyrus=new cyrus_conf();
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();
	$EnableMechCramMD5=$sock->GET_INFO("EnableMechCramMD5");
	$EnableMechDigestMD5=$sock->GET_INFO("EnableMechDigestMD5");
	$EnableMechLogin=$sock->GET_INFO("EnableMechLogin");
	$EnableMechPlain=$sock->GET_INFO("EnableMechPlain");
	if(!is_numeric($EnableMechCramMD5)){$EnableMechCramMD5=0;}
	if(!is_numeric($EnableMechDigestMD5)){$EnableMechDigestMD5=0;}
	if(!is_numeric($EnableMechLogin)){$EnableMechLogin=1;}
	if(!is_numeric($EnableMechPlain)){$EnableMechPlain=1;}
	
	
	
	$impad_form="
	<table style='width:99%' class=form>
	
	<tr>
	<td align='right' class=legend style='font-size:13px'>{allowallsubscribe}:</stong></td>
	<td>" . Field_checkbox('allowallsubscribe',1,$cyrus->impad_array["allowallsubscribe"])."</td>
	<td width=1%>". help_icon("{allowallsubscribe_text}")."</td>
	</tr>

	<tr>
	<td align='right' class=legend style='font-size:13px'>{allowanonymouslogin}:</stong></td>
	<td>" . Field_checkbox('allowanonymouslogin',1,$cyrus->impad_array["allowanonymouslogin"])."</td>
	<td width=1%>". help_icon("{allowanonymouslogin_text}")."</td>
	</tr>		
	

	
	<tr>
	<td align='right' class=legend style='font-size:13px'>{createonpost}:</stong></td>
	<td>" . Field_checkbox('createonpost',1,$cyrus->impad_array["createonpost"])."</td>
	<td width=1%>". help_icon("{createonpost_text}")."</td>
	</tr>		
	
	<tr>
	<td align='right' class=legend style='font-size:13px'>{duplicatesuppression}:</stong></td>
	<td>" . Field_checkbox('duplicatesuppression',1,$cyrus->impad_array["duplicatesuppression"])."</td>
	<td width=1%>". help_icon("{duplicatesuppression_text}")."</td>
	</tr>	
	
	
	
	<tr>
	<td align='right' class=legend style='font-size:13px'>{autocreateinboxfolders}:</stong></td>
	<td>" . Field_text("autocreateinboxfolders",$cyrus->impad_array["autocreateinboxfolders"],'width:190px;font-size:13px;padding:3px',null,null,null,FALSE,null,false)."</td>
	<td width=1%>". help_icon("{autocreateinboxfolders_text}")."</td>
	</tr>		
	
	<tr>
	<td align='right' class=legend style='font-size:13px'>{autocreatequota}:</stong></td>
	<td>" . Field_text("autocreatequota",$cyrus->impad_array["autocreatequota"],'width:90px;font-size:13px;padding:3px',null,null)."</td>
	<td width=1%>". help_icon("{autocreatequota_text}")."</td>
	</tr>	
		
	
	<tr>
	<td align='right' class=legend style='font-size:13px'>{maxmessagesize}:</stong></td>
	<td>" . Field_text("maxmessagesize",$cyrus->impad_array["maxmessagesize"],'width:90px;font-size:13px;padding:3px',null,null)."</td>
	<td width=1%>". help_icon("{maxmessagesize_text}")."</td>
	</tr>	
	
	<tr>
	<td align='right' class=legend style='font-size:13px'>{popminpoll}:</stong></td>
	<td>" . Field_text("popminpoll",$cyrus->impad_array["popminpoll"],'width:25px;font-size:13px;padding:3px',null,null)." mn</td>
	<td width=1%>". help_icon("{popminpoll_text}")."</td>
	</tr>	
	
	<tr>
	<td align='right' class=legend style='font-size:13px'>{quotawarn}:</stong></td>
	<td>" . Field_text("quotawarn",$cyrus->impad_array["quotawarn"],'width:25px;font-size:13px;padding:3px',null,null)." %</td>
	<td width=1%>". help_icon("{quotawarn_text}")."</td>
	</tr>
	<tr>
		<td colspan=3 align='left'><span style='font-size:16px'>{authentication}</span><hr></td>
	</tr>	

	<tr>
	<td align='right' class=legend style='font-size:13px'>plain:</stong></td>
	<td>" . Field_checkbox('plain',1,$EnableMechPlain)."</td>
	<td width=1%></td>
	</tr>

	<tr>
	<td align='right' class=legend style='font-size:13px'>login:</stong></td>
	<td>" . Field_checkbox('login',1,$EnableMechLogin)."</td>
	<td width=1%></td>
	</tr>	

	<tr>
	<td align='right' class=legend style='font-size:13px'>cram-md5:</stong></td>
	<td>" . Field_checkbox('cram-md5',1,$EnableMechCramMD5)."</td>
	<td width=1%></td>
	</tr>	
	
	<tr>
	<td align='right' class=legend style='font-size:13px'>digest-md5:</stong></td>
	<td>" . Field_checkbox('digest-md5',1,$EnableMechDigestMD5)."</td>
	<td width=1%></td>
	</tr>	

	<tr>
		<td colspan=3 align='right'><hr>". button("{apply}","CyrusMasterSaveConfig()")."</td>
	</tr>

	
	</table>
	</form>
	";
	
	$ldap=new clladp();
	$hash=$ldap->AllDomains();
	$hash[null]="None";
	$defaultsDomain=Field_array_Hash($hash,'defaultdomain',$cyrus->impad_array["defaultdomain"],null,null,0,'font-size:13px;padding:3px');
	$html="
	<div class=explain>{services_settings_text}</div>
	<div id='cyrusconf'>
		<div id='NavigationForms3'>	
			<h3><a href=\"javascript:blur();\"  OnClick=\"javascript:$('#NavigationForms3').accordion( 'activate' , 0 );\">{ports}</a></h3>
				<div>
					<table cellspacing='0' cellpadding='0' border='0' class='tableView'>
						<thead class='thead'>
								<tr>
									<th>{service}</th>
									<th>{enable}</th>
									<th>{port}</th>
									<th>{maxchild}</th>
									<th>&nbsp;</th>
								</tr>
							</thead>
						<tbody>
						
						<tr class=oddRow>
							<td align='right' class=legend style='font-size:13px'>IMAP:</stong></td>
							<td width=1%><img src='img/status_ok-grey.gif'></td>
							<td><input type='hidden' name='service_imap_listen' value='{$cyrus->main_array["CYRUS"]["service_imap_listen"]}'><strong style='font-size:13px'>imap:143</strong></td>
							<td>" . Field_text("service_imap_maxchild",$cyrus->main_array["CYRUS"]["service_imap_maxchild"],'width:80px;font-size:13px;padding:3px')."</td>
							<td width=1%>"  . help_icon('{maxchild_text}')."</td>
						</tr>	
					
					
						<tr>
							<td align='right' class=legend style='font-size:13px'>IMAP SSL:</stong></td>
							<td width=1%>" . Field_checkbox("service_imapssl_enabed",1,$cyrus->main_array["CYRUS"]["service_imapssl_enabed"])."</td>
							<td>" . Field_text("service_imapssl_listen",$cyrus->main_array["CYRUS"]["service_imapssl_listen"],'width:150px;font-size:13px;padding:3px')."</td>
							<td>" . Field_text("service_imapssl_maxchild",$cyrus->main_array["CYRUS"]["service_imapssl_maxchild"],'width:80px;font-size:13px;padding:3px')."</td>
							<td width=1%>"  . help_icon('{howto_cyrus_service}')."</td>
						</tr>
					
						<tr class=oddRow>
							<td align='right' class=legend style='font-size:13px'>POP3:</stong></td>
							<td width=1%>" . Field_checkbox("service_pop3_enabed",1,$cyrus->main_array["CYRUS"]["service_pop3_enabed"])."</td>
							<td>" . Field_text("service_pop3_listen",$cyrus->main_array["CYRUS"]["service_pop3_listen"],'width:150px;font-size:13px;padding:3px')."</td>
							<td>" . Field_text("service_pop3_maxchild",$cyrus->main_array["CYRUS"]["service_pop3_maxchild"],'width:80px;font-size:13px;padding:3px')."</td>
							<td width=1%>"  . help_icon('{howto_cyrus_service}')."</td>
						</tr>
					
						
						<tr>
							<td align='right' class=legend style='font-size:13px'>POP3 SSL:</stong></td>
							<td width=1%>" . Field_checkbox("service_pop3ssl_enabed",1,$cyrus->main_array["CYRUS"]["service_pop3ssl_enabed"])."</td>
							<td>" . Field_text("service_pop3ssl_listen",$cyrus->main_array["CYRUS"]["service_pop3ssl_listen"],'width:150px;font-size:13px;padding:3px')."</td>
							<td>" . Field_text("service_pop3ssl_maxchild",$cyrus->main_array["CYRUS"]["service_pop3ssl_maxchild"],'width:80px;font-size:13px;padding:3px')."</td>
							<td width=1%>"  . help_icon('{howto_cyrus_service}')."</td>
						</tr>	
					
						<tr class=oddRow>
							<td align='right' class=legend style='font-size:13px'>NNTP:</stong></td>
							<td width=1%>" . Field_checkbox("service_nntpd_enabed",1,$cyrus->main_array["CYRUS"]["service_nntpd_enabed"])."</td>
							<td>" . Field_text("service_nntpd_listen",$cyrus->main_array["CYRUS"]["service_nntpd_listen"],'width:150px;font-size:13px;padding:3px')."</td>
							<td>&nbsp;</td>
							<td width=1%>"  . help_icon('{howto_cyrus_service}')."</td>
						</tr>	
						
						<tr>
							<td align='right' class=legend style='font-size:13px'>NNTP SSL:</stong></td>
							<td width=1%>" . Field_checkbox("service_nntpds_enabed",1,$cyrus->main_array["CYRUS"]["service_nntpds_enabed"])."</td>
							<td>" . Field_text("service_nntpds_listen",$cyrus->main_array["CYRUS"]["service_nntpds_listen"],'width:150px;font-size:13px;padding:3px')."</td>
							<td>&nbsp;</td>
							<td width=1%>"  . help_icon('{howto_cyrus_service}')."</td>
						</tr>	
							<tr class=oddRow>
							<td align='right' class=legend style='font-size:13px'>{defaultdomain}:</stong></td>
							<td width=1%>&nbsp;</td>
							<td>$defaultsDomain</td>
							<td>&nbsp;</td>
							<td width=1%>"  . help_icon('{defaultdomain_text}')."</td>
						</tr>	
					
						<tr>
							<td colspan=5 align='right'>". button("{apply}","CyrusMasterSaveConfig()")."</td>
						</tr>
						</tbody>
					</table>
				</div>
			
			
			<h3><a href=\"javascript:blur();\" OnClick=\"$('#NavigationForms3').accordion( 'activate' , 1);\">{options}</a></h3>
				<div>			
					$impad_form
				</div>
			</div>
		</div>
	</div>
	
	<script>
	
		
	
var x_CyrusMasterSaveConfigSave = function (obj) {
	var results=trim(obj.responseText);
	if(results.length>0){alert(results);}
	RefreshTab('main_config_cyrus');
}


function CyrusMasterSaveConfig(){
	var XHR = new XHRConnection();
	if(document.getElementById('allowallsubscribe').checked){XHR.appendData('allowallsubscribe',1);}else{XHR.appendData('allowallsubscribe',0);}
	if(document.getElementById('allowanonymouslogin').checked){XHR.appendData('allowanonymouslogin',1);}else{XHR.appendData('allowanonymouslogin',0);}
	if(document.getElementById('createonpost').checked){XHR.appendData('createonpost',1);}else{XHR.appendData('createonpost',0);}
	if(document.getElementById('duplicatesuppression').checked){XHR.appendData('duplicatesuppression',1);}else{XHR.appendData('duplicatesuppression',0);}

	
	if(document.getElementById('service_imapssl_enabed').checked){XHR.appendData('service_imapssl_enabed',1);}else{XHR.appendData('service_imapssl_enabed',0);}
	if(document.getElementById('service_pop3_enabed').checked){XHR.appendData('service_pop3_enabed',1);}else{XHR.appendData('service_pop3_enabed',0);}
	if(document.getElementById('service_pop3ssl_enabed').checked){XHR.appendData('service_pop3ssl_enabed',1);}else{XHR.appendData('service_pop3ssl_enabed',0);}
	if(document.getElementById('service_nntpd_enabed').checked){XHR.appendData('service_nntpd_enabed',1);}else{XHR.appendData('service_nntpd_enabed',0);}
	if(document.getElementById('service_nntpds_enabed').checked){XHR.appendData('service_nntpds_enabed',1);}else{XHR.appendData('service_nntpds_enabed',0);}
	
	if(document.getElementById('cram-md5').checked){XHR.appendData('cram-md5',1);}else{XHR.appendData('cram-md5',0);}
	if(document.getElementById('digest-md5').checked){XHR.appendData('digest-md5',1);}else{XHR.appendData('digest-md5',0);}
	if(document.getElementById('plain').checked){XHR.appendData('plain',1);}else{XHR.appendData('plain',0);}
	if(document.getElementById('login').checked){XHR.appendData('login',1);}else{XHR.appendData('login',0);}
	
	
	XHR.appendData('autocreateinboxfolders',document.getElementById('autocreateinboxfolders').value);
	XHR.appendData('autocreatequota',document.getElementById('autocreatequota').value);
	XHR.appendData('maxmessagesize',document.getElementById('maxmessagesize').value);
	XHR.appendData('popminpoll',document.getElementById('popminpoll').value);
	XHR.appendData('quotawarn',document.getElementById('quotawarn').value);
	XHR.appendData('service_imap_maxchild',document.getElementById('service_imap_maxchild').value);
	XHR.appendData('service_imapssl_listen',document.getElementById('service_imapssl_listen').value);
	XHR.appendData('service_imapssl_maxchild',document.getElementById('service_imapssl_maxchild').value);
	XHR.appendData('service_pop3_listen',document.getElementById('service_pop3_listen').value);
	XHR.appendData('service_pop3_maxchild',document.getElementById('service_pop3_maxchild').value);
	XHR.appendData('service_pop3ssl_listen',document.getElementById('service_pop3ssl_listen').value);
	XHR.appendData('service_pop3ssl_maxchild',document.getElementById('service_pop3ssl_maxchild').value);
	XHR.appendData('service_nntpd_listen',document.getElementById('service_nntpd_listen').value);
	XHR.appendData('service_nntpds_listen',document.getElementById('service_nntpds_listen').value);
	document.getElementById('cyrusconf').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
	
	
	XHR.sendAndLoad('$page', 'GET',x_CyrusMasterSaveConfigSave);
	
	}	

	
	$(function() {
			$( \"#NavigationForms3\" ).accordion({autoHeight: false,navigation: true});});	
	
	</script>
	";
		return  $tpl->_ENGINE_parse_body($html);
}


function main_cyrquota(){
	
	
	
$sock=new sockets();
	$page=CurrentPageName();
	$tpl=new templates();

	
	$html="$form";
	
	
	$sock=new sockets();
	$datas=$sock->getfile('cyrquota');
	
	$styleRoll="
	style='border:1px solid white;'
	OnMouseOver=\"this.style.cursor='pointer'\"
	OnMouseOut=\"this.style.cursor='auto'\"";
	
	$tbl=explode("\n",$datas);
	$html=$html ."
	<div align='center' style='height:250px;overflow:auto;width:99%'>
	<table style='width:70%;border:1px solid #CCCCCC'>
	<tr style='background-color:#CCCCCC;font-size:14px;font-weight:bold'>
	<td>&nbsp;</td>
	<td>{user}</td>
	<td nowrap>{quota}</td>
	<td>{used}%</td>
	<td>{used}</td>
	</tr>
	
	";
	while (list ($num, $ligne) = each ($tbl) ){
	if(preg_match('#(.*)\s+(.*)\s+(.*)\s+user\/(.+)#',$ligne,$re)){
		
		if(trim($re[1])==null){$re[1]="{illimited}";}
		if(trim($re[2])<>null){$re[2]=$re[2]."%";}
		
		$html=$html . "
		<tr " . CellRollOver().MEMBER_JS($re[4]).">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td $styleRoll>{$re[4]}</td>
		<td width=1% align='right' $styleRoll>{$re[1]}</td>
		<td width=1% $styleRoll align='center'>{$re[2]}</td>
		<td width=1% $styleRoll>{$re[3]}</td>
		</tr>
		";
	}else{
	
	if(preg_match('#([0-9]+)\s+([0-9]+)\s+user\/(.+)#',$ligne,$re)){
		$html=$html . "
		<tr " . CellRollOver().MEMBER_JS($re[3]).">
		<td width=1% $styleRoll><img src='img/fw_bold.gif'></td>
		<td $styleRoll>{$re[3]}</td>
		<td width=1% align='right' v>{$re[1]}</td>
		<td width=1% $styleRoll align='center'>-</td>
		<td width=1% $styleRoll>{$re[2]}</td>
		</tr>
		";
	}}	
	}
	
	$html=$html ."</table></div>";
	echo RoundedLightWhite($tpl->_ENGINE_parse_body($html));
	
	
}

function Save_services(){
	$cyrus=new cyrus_conf();
	while (list ($num, $ligne) = each ($_GET) ){
		$cyrus->impad_array[$num]=$ligne;
		$cyrus->main_array["CYRUS"][$num]=$ligne;
	}
	
	writelogs("default domain={$_GET["defaultdomain"]}",__FUNCTION__,__FILE__);
	
	$cyrus->impad_array["defaultdomain"]=$_GET["defaultdomain"];
	$sock=new sockets();
	$sock->SET_INFO("EnableMechCramMD5",$_GET["cram-md5"]);
	$sock->SET_INFO("EnableMechDigestMD5",$_GET["digest-md5"]);
	$sock->SET_INFO("EnableMechLogin",$_GET["login"]);
	$sock->SET_INFO("EnableMechPlain",$_GET["plain"]);
	
	$cyrus->SaveToLdap();
}

function Save_impad(){
	$cyrus=new cyrus_conf();
	while (list ($num, $ligne) = each ($_GET) ){
		
		$cyrus->impad_array[$num]=$ligne;
	}
	$cyrus->SaveToLdap();	
	
}

function main_logs(){
	$page=CurrentPageName();
	$tpl=new templates();
	$html="
	
	<iframe src='cyrus.events.php' style='width:100%;height:500px;border:0px'></iframe>";
	echo $tpl->_ENGINE_parse_body($html);
	}
	
	
function MailBoxesDomainList_js(){
$page=CurrentPageName();
	$html="
	
	
var x_LoadMailBoxDomainQuery= function (obj) {
	var response=obj.responseText;
	if(response){
	document.getElementById('maindomainlistcyrus').innerHTML=response;
	}
	
}	
	
	function LoadMailBoxDomainQuery(){
		var mailbox_domain_query=document.getElementById('mailbox_domain_query').value;
		var XHR = new XHRConnection();
		XHR.appendData('main','mailboxes');
		XHR.appendData('mailbox_domain_query',mailbox_domain_query);			
		document.getElementById('maindomainlistcyrus').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
		XHR.sendAndLoad('cyrus.index.php', 'GET',x_LoadMailBoxDomainQuery);	
		
	}
	
	LoadMailBoxDomainQuery();
	
	";
	
	echo $html;
	
}
	
function main_mailboxes(){
	include_once("ressources/class.cyrus-admin.inc");

	
	$sock=new sockets();
	$page=CurrentPageName();
	$tpl=new templates();
	$mailbox_domain_query=null;	
	
	if(isset($_GET["mailbox_domain_query"])){
		$mailbox_domain_query=$_GET["mailbox_domain_query"];
	}
	
	if($sock->GET_INFO("EnableVirtualDomainsInMailBoxes")==1){
		$ldap=new clladp();
		$hash=$ldap->hash_get_local_domains();
		
		$hash[null]="{select}";		
		$domainsf=Field_array_Hash($hash,"mailbox_domain_query",$mailbox_domain_query,"Loadjs('$page?MailBoxesDomainList=yes');",null,0,"padding:5px;font-size:13px");

		$form="
		<table style='width:100%'>
		<tr>
			<td class=legend>{domain}:</td>
			<td>$domainsf</td>
		</tr>
		</table>
		<div id='maindomainlistcyrus'></div>
		";
	}
	
	if($mailbox_domain_query<>null){
		 $datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?mailboxlist-domain=$mailbox_domain_query")));
		 $form=null;		
	}else{
	 $datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?mailboxlist=yes")));
	}
	
	if(isset($_GET["mailbox_domain_query"])){$form=null;}	

	$html="
	$form
	<input type='hidden' id='deletemailbox_infos' value='{deletemailbox_infos}'>
	<div style='width:100%;height:300px;overflow:auto'>
	";
		
	while (list ($num, $ligne) = each ($datas) ){
		if($ligne<>null){
			if(preg_match("#user/(.+)#",$ligne,$re)){
				
				$mailbox_name=$re[1];
				if($mailbox_domain_query<>null){
					$mailbox_name="$mailbox_name@$mailbox_domain_query";
				}
				
				$html=$html . "<div style='float:left;width:150px;border:1px dotted #CCCCCC;margin:2px;padding:3px' " . CellRollOver()." id='".md5($ligne)."'>
				<table style='width:100%'>
				<tr>
				<td width=1% valign='top'><img src='img/identity-24.png'></td>
				<td><strong>$re[1]</strong></td>
				<td width=1%>" . imgtootltip('ed_delete.gif','{delete}',"DeleteRealMailBox('$mailbox_name','".md5($ligne)."');")."</td>
				</tr>
				</table>
				</div>
				
				
				";
			}
			
			
		}
		
	}
	
	$html=$html . "</div>";
	
echo RoundedLightWhite($tpl->_ENGINE_parse_body($html));
}

function DeleteRealMailBox(){
	$mbx=$_GET["DeleteRealMailBox"];
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?DelMbx=$mbx");	
	
}

function rebuild_mailboxes(){
	
	$sock=new sockets();
	$sock->getfile("cyrreconstruct");
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{cyrreconstruct_wait}');
}

function cyrreconstruct_status(){
	if(is_file("ressources/logs/cyrreconstruct")){
		$html="<textarea style='width:100%;height:250px'>".file_get_contents("ressources/logs/cyrreconstruct")."</textarea>";
		return $html;
	}else{
		$tpl=new templates();
		return $tpl->_ENGINE_parse_body("<div style='padding:3px' class=legend><strong>{cyrreconstruct_waiting}</strong></div>");
	}
	
	
}


?>
