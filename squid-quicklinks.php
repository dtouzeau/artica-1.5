<?php
if(isset($_GET["verbose"])){$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}
include_once('ressources/class.templates.inc');
session_start();
include_once('ressources/class.html.pages.inc');
include_once('ressources/class.cyrus.inc');
include_once('ressources/class.main_cf.inc');
include_once('ressources/charts.php');
include_once('ressources/class.syslogs.inc');
include_once('ressources/class.system.network.inc');
include_once('ressources/class.os.system.inc');

//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);
if(isset($_GET["verbose"])){$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}
if(!isset($GLOBALS["CLASS_USERS"])){$GLOBALS["CLASS_USERS"]=new usersMenus();$users=$GLOBALS["CLASS_USERS"];}else{$users=$GLOBALS["CLASS_USERS"];}
if(!$users->AsAnAdministratorGeneric){die("Not autorized");}
if(isset($_GET["off"])){off();exit;}
if(function_exists($_GET["function"])){call_user_func($_GET["function"]);exit;}

$page=CurrentPageName();
$users=new usersMenus();
$tpl=new templates();
$sock=new sockets();
if($_GET["stats"]){
	$tr[]=$tpl->_ENGINE_parse_body(quicklinks_paragraphe("perf-stats-48.png", "traffic_statistics","squid_traffic_statistics_text", "QuickLinkSystems('traffic_statistics')"));
	$tr[]=$tpl->_ENGINE_parse_body(quicklinks_paragraphe("members-48.png", "members","section_security_text", "QuickLinkSystems('members_statistics')"));
	$tr[]=$tpl->_ENGINE_parse_body(quicklinks_paragraphe("48-tasks.png", "tasks","", "QuickLinkSystems('section_tasks')"));
	
	if(($users->AsSquidAdministrator) OR ($users->AsDansGuardianAdministrator)){
		if($users->SQUID_INSTALLED){
			$tr[]=$tpl->_ENGINE_parse_body(quicklinks_paragraphe("48-parameters.png", "proxy_main_settings","main_interface_back_interface_text", "SquidMainQuickLinks()"));
		}
	}
	
	$tr[]=$tpl->_ENGINE_parse_body(quicklinks_paragraphe("web-site-48.png", "main_interface","main_interface_back_interface_text", "QuickLinksHide()"));

}
$count=1;


while (list ($key, $line) = each ($tr) ){
	if($line==null){continue;}
	$f[]="<li id='kwick1'>$line</li>";
	$count++;
	
}



	
	$html="
            <div id='QuickLinksTop'>
                <ul class='kwicks'>
					".@implode("\n", $f)."
                    
                </ul>
            </div>
	
	<div id='quicklinks-samba' style='width:900px'></div>
	<div id='BodyContent' style='width:900px'></div>
	
	
	<script>
		function LoadQuickTaskBar(){
			$(document).ready(function() {
				$('#QuickLinksTop .kwicks').kwicks({max: 205,spacing:  5});
			});
		}
		
		function QuickLinksSamba(){
			Set_Cookie('QuickLinkCache', 'quicklinks.fileshare.php', '3600', '/', '', '');
			LoadAjax('BodyContent','quicklinks.fileshare.php');
		}
		
		function QuickLinksProxy(){
			Set_Cookie('QuickLinkCache', 'quicklinks.proxy.php', '3600', '/', '', '');
			LoadAjax('BodyContent','quicklinks.proxy.php');		
		
		}
		
		function QuickLinksKav4Proxy(){
			Set_Cookie('QuickLinksKav4Proxy', 'kav4proxy.php?inline=yes', '3600', '/', '', '');
			LoadAjax('BodyContent','kav4proxy.php?inline=yes');		
		
		}		
		
		
		
		function QuickLinkSystems(sfunction){
			Set_Cookie('QuickLinkCache', '$page?function='+sfunction, '3600', '/', '', '');
			LoadAjax('BodyContent','$page?function='+sfunction);
		}
		
		function QuickLinkMemory(){
			var memorized=Get_Cookie('QuickLinkCache');
			if(!memorized){
				QuickLinkSystems('section_computers_infos');
				return;
			}
			
			if(memorized.length>0){
				LoadAjax('BodyContent',memorized);
			}else{
				QuickLinkSystems('section_computers_infos');

			}
		
		}
		
		LoadQuickTaskBar();
		QuickLinkMemory();
	</script>
	";
	
	
	


$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);


function quicklinks_paragraphe($img,$title,$text,$link){
	
	$html="
	<table style='width:205px;margin-top:2px' OnClick=\"javascript:$link\">
	<tbody>
	<tr>
		<td width=1% valign='top'>". imgtootltip($img,"{{$text}}",$link)."</td>
		<td style='color:white;padding-left:2px;' valign='top' width=99%>
		<div style='font-size:14px;font-weight:bold;letter-spacing:-1px;padding-bottom:3px;border-bottom:1px solid white;margin-bottom:3px'  
		OnClick=\"javascript:$link\">{{$title}}
		</div>
	</tr>
	</tbody>
	</table>
	";
	return $html;
	
	
	
}

function traffic_statistics(){
	
	$html="<div id='squid_traffic_stats'></div>
	
	<script>
		LoadAjax('squid_traffic_stats','squid.traffic.statistics.php');
	</script>
	
	";
	echo $html;
}

function members_statistics(){
	$html="<div id='squid_members_stats'></div>
	
	<script>
		LoadAjax('squid_members_stats','squid.members.statistics.php');
	</script>
	
	";
	echo $html;	
	
}

function section_tasks(){
	
	echo "<script>LoadAjax('BodyContent','squid.statistics.tasks.php');</script>";
	
}



function section_security(){
	
	$tr[]=kaspersky();
	$tr[]=statkaspersky();
	$tr[]=clamav();
	$tr[]=icon_troubleshoot();
	$tr[]=certificate();
	$tr[]=icon_externalports();
	$tr[]=incremental_backup();
$tables[]="<table style='width:100%' class=form><tr>";
$t=0;
while (list ($key, $line) = each ($tr) ){
		$line=trim($line);
		if($line==null){continue;}
		$t=$t+1;
		$tables[]="<td valign='top'>$line</td>";
		if($t==2){$t=0;$tables[]="</tr><tr>";}
		}

if($t<2){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top'>&nbsp;</td>";				
	}
}	
	

$links=@implode("\n", $tables);
$heads=section_computer_header();
$html="
<table style='width:100%'>
<tr>
	<td valign='top'>$heads</td>
	<td valign='top'>$links</td>
</tr>
</table>
";

	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function section_softwares(){
	
	$tr[]=icon_update_clamav();
	$tr[]=icon_update_spamassassin_blacklist();
	$tr[]=icon_update_artica();
	$tr[]=applis();
	$tr[]=apt();
	
$tables[]="<table style='width:100%' class=form><tr>";
$t=0;
while (list ($key, $line) = each ($tr) ){
		$line=trim($line);
		if($line==null){continue;}
		$t=$t+1;
		$tables[]="<td valign='top'>$line</td>";
		if($t==2){$t=0;$tables[]="</tr><tr>";}
		}

if($t<2){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top'>&nbsp;</td>";				
	}
}	
	

$links=@implode("\n", $tables);
$heads=section_computer_header();
$html="
<table style='width:100%'>
<tr>
	<td valign='top'>$heads</td>
	<td valign='top'>$links</td>
</tr>
</table>
";

	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}


function section_computers_infos(){
	$tr[]=sysinfos();
	$tr[]=icon_system();
	$tr[]=nic_settings();
	$tr[]=icon_memory();
	$tr[]=icon_harddrive();
	$tr[]=icon_adduser();
	$tr[]=scancomputers();
	$tr[]=sharenfs();
	$tr[]=clientnfs();
	
$tables[]="<table style='width:100%' class=form><tr>";
$t=0;
while (list ($key, $line) = each ($tr) ){
		$line=trim($line);
		if($line==null){continue;}
		$t=$t+1;
		$tables[]="<td valign='top'>$line</td>";
		if($t==2){$t=0;$tables[]="</tr><tr>";}
		}

if($t<2){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top'>&nbsp;</td>";				
	}
}	
	

$links=@implode("\n", $tables);
$heads=section_computer_header();
$html="
<table style='width:100%'>
<tr>
	<td valign='top'>$heads</td>
	<td valign='top'>$links</td>
</tr>
</table>
";

	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function section_computer_header(){

$hour=date('h');
$key_cache="CACHEINFOS_STATUSSEVERREDNDER$hour";
//if(isset($_SESSION[$key_cache])){return $_SESSION[$key_cache];}
unset($_SESSION["DISTRI"]);

include_once('ressources/class.os.system.inc');
include_once("ressources/class.os.system.tools.inc");
$sock=new sockets();
$datas=unserialize($sock->getFrameWork("services.php?dmicode=yes"));
$img="img/server-256.png";
$foundChassis=false;
if(is_array($datas)){
	$proc_type=$datas["PROC_TYPE"];
	$MANUFACTURER =$datas["MANUFACTURER"];
	$PRODUCT=$datas["PRODUCT"];
	$CHASSIS=$datas["CHASSIS"];
	$md5Chassis=md5("{$datas["MANUFACTURER"]}{$datas["CHASSIS"]}{$datas["PRODUCT"]}");
	if(is_file("img/vendors/$md5Chassis.jpg")){$img="img/vendors/$md5Chassis.jpg";$foundChassis=true;}
	if(is_file("img/vendors/$md5Chassis.jpeg")){$img="img/vendors/$md5Chassis.jpeg";$foundChassis=true;}
	if(is_file("img/vendors/$md5Chassis.png")){$img="img/vendors/$md5Chassis.png";$foundChassis=true;}
	
}

if(!$foundChassis){
	$chassis_serial="<tr>
					<td valign='top' style='font-size:12px' class=legend>{serial}:</td>
					<td valign='top' style='font-size:12px'><strong>$md5Chassis</td>
				</tr>";
}

if(!isset($_SESSION["DISTRI"])){
	$sys=new systeminfos();
	writelogs('Loading datas system for session',__FUNCTION__,__FILE__);
	$distri=$sys->ditribution_name;
	$kernel=$sys->kernel_version;
	$LIBC=$sys->libc_version;
	$temp=$sys->GetCpuTemp();
	$users=new usersMenus();
	$os=new os_system();
	$arraycpu=$os->cpu_info();
	$cpuspeed=round(($arraycpu["cpuspeed"]/1000*100)/100,2); 
	$host=$users->hostname;
	$publicip=@file_get_contents("ressources/logs/web/myIP.conf");
$distri_logo="img/serv-mail-linux.png";
if(is_file("img/$users->LinuxDistriCode.png")){$distri_logo="img/$users->LinuxDistriCode.png";}
if(is_file("img/$users->LinuxDistriCode.gif")){$distri_logo="img/$users->LinuxDistriCode.gif";}
	$distri="
	<center>
	
	
	<table style='width:100%;color:black;' class=form>
		<tr>
			<td colspan=2 align=center><img src='$img'></td>
		</tr>
				<tr>
					<td valign='top' style='font-size:12px' class=legend>{server}:</td>
					<td valign='top' style='font-size:12px'><strong>$host</strong><br><strong>$MANUFACTURER $PRODUCT $CHASSIS</td>
				</tr>
				<tr>
					<td valign='top' style='font-size:12px' class=legend>{public_ip}:</td>
					<td valign='top' style='font-size:12px'><strong>$publicip</strong></td>
				</tr>				
				<tr>
					<td valign='top' style='font-size:12px' class=legend>{processors}:</td>
					<td valign='top' style='font-size:12px'><strong>{$arraycpu["cpus"]} cpu(s):{$cpuspeed}GHz<br>$proc_type</strong></td>
				</tr>				
				<tr>
					<td valign='top' style='font-size:12px' class=legend>Artica:</td>
					<td valign='top' style='font-size:12px'><strong>$users->ARTICA_VERSION</strong></td>
				</tr>							
					<td valign='top' style='font-size:12px'><img src='$distri_logo'></td>
					<td valign='top' style='font-size:12px'><strong>$distri<br>kernel $kernel
					<br>libc $LIBC<br>Temp $temp&nbsp;C</strong>
					</td>
				</tr>
				$chassis_serial
			</table>
</center>";
	
	$_SESSION["DISTRI"]=$distri;
}else{
	$distri=$_SESSION["DISTRI"];
}




	
	
	
	$html="$distri";
				
	$_SESSION[$key_cache]=$html;
	return $html;
	
	
}


function off(){
	
$html="<div id='content' style='background-color:white;padding:0px;margin:0px'>
		<table style='width:100%'>
			<tr>
				<td valign='top' style='padding:0px;margin:0px;width:150px' class=tdleftmenus id='id-tdleftmenus'>
					<div id='TEMPLATE_LEFT_MENUS'></div>
				</td>
				<td valign='top' style='padding-left:3px'>
					<div id='template_users_menus'></div>
					<div id='BodyContentTabs'></div>
						<div id='BodyContent' style='margin-top:8px'>
							<div style='float:right'><a href='#' OnClick=\"javascript:QuickLinks()\"><img src='img/arrowup-32.png' id='img-quicklinks'></a></div> <h1 id='template_title'>{TEMPLATE_TITLE}</h1>

						</div>

				</td>
				<td valign='top'><div id='TEMPLATE_RIGHT_MENUS'></div>
				</td>
			</tr>	
	</table>	

	<div class='clearleft'></div>
	<div class='clearright'></div>
	</div id='content'>
	<script>LoadAjax('BodyContent','admin.index.php?admin-ajax=yes');</script>
	
	";	

echo $html;
	
}

function LocalParagraphe($title,$text,$js,$img){
	
		$js=str_replace("javascript:","",$js);
		$id=md5($js);
		$img_id="{$id}_img";
		Paragraphe($img, $title, $text,$js);
	$html="
	<table style='width:198px;'>
	<tr>
	<td width=1% valign='top'>" . imgtootltip($img,"{{$text}}","$js",null,$img_id)."</td>
	<td><strong style='font-size:12px'>{{$title}}</strong><div style='font-size:11px'>{{$text}}</div></td>
	</tr>
	</table>";
	

return "<div style=\"width:200px;margin:2px\" 
	OnMouseOver=\"javascript:ParagrapheWhiteToYellow('$id',0);this.style.cursor='pointer';\" 
	OnMouseOut=\"javascript:ParagrapheWhiteToYellow('$id',1);this.style.cursor='auto'\" OnClick=\"javascript:$js\">
  <b id='{$id}_1' class=\"RLightWhite\">
  <b id='{$id}_2' class=\"RLightWhite1\"><b></b></b>
  <b id='{$id}_3' class=\"RLightWhite2\"><b></b></b>
  <b id='{$id}_4' class=\"RLightWhite3\"></b>
  <b id='{$id}_5' class=\"RLightWhite4\"></b>
  <b id='{$id}_6' class=\"RLightWhite5\"></b></b>

  <div id='{$id}_0' class=\"RLightWhitefg\" style='padding:2px;'>
   $html
  </div>

  <b id='{$id}_7' class=\"RLightWhite\">
  <b id='{$id}_8' class=\"RLightWhite5\"></b>
  <b id='{$id}_9' class=\"RLightWhite4\"></b>
  <b id='{$id}_10' class=\"RLightWhite3\"></b>
  <b id='{$id}_11' class=\"RLightWhite2\"><b></b></b>
  <b id='{$id}_12' class=\"RLightWhite1\"><b></b></b></b>
</div>
";		
		
	
}


function main_kaspersky_action(){
	include_once('ressources/class.kas-filter.inc');
	$kas=new kas_single();
	$html="
	<table style='width:100%'>
	<tr>
		<td align='right'><strong>{ACTION_SPAM_MODE}:</strong></td>
		<td>" . Field_array_Hash($kas->ACTION_SPAM_MODE_FIELD,'ACTION_SPAM_MODE',$kas->ACTION_SPAM_MODE)."</td>
	</tr>
	<tr>
		<td align='right'><strong>{ACTION_SPAM_SUBJECT}:</strong></td>
		<td>" . Field_text('ACTION_SPAM_SUBJECT_PREFIX',$kas->ACTION_SPAM_SUBJECT_PREFIX,'width:100%')."</td>
	<td>
	<tr><td colspan=2><hr></td></tr>
	
	
	
	<tr>
		<td align='right'><strong>{ACTION_PROBABLE_MODE}:</td>
		<td>" . Field_array_Hash($kas->ACTION_SPAM_MODE_FIELD,'ACTION_PROBABLE_MODE',$kas->ACTION_PROBABLE_MODE)."</td>
	</tr>	
	<tr>
		<td align='right'><strong>{ACTION_PROBABLE_MODE_SUBJECT}:</td>
		<td>" . Field_text('ACTION_PROBABLE_SUBJECT_PREFIX',$kas->ACTION_PROBABLE_SUBJECT_PREFIX,'width:100%')."</td>
	</tr>		
	<tr><td colspan=2 align='right'><input type='button' OnClick=\"javascript:kavStep4();\" value='{build}&nbsp;&raquo;'></td></tr>
	
	
	</table>";
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'kas.group.rules.php');	

	
}


function main_kaspersky(){
	if(!isset($GLOBALS["CLASS_USERS"])){$GLOBALS["CLASS_USERS"]=new usersMenus();$users=$GLOBALS["CLASS_USERS"];}else{$users=$GLOBALS["CLASS_USERS"];}
	$users->LoadModulesEnabled();
	$page=CurrentPageName();

	$html="
	<p style='font-size:12px;font-weight:bold'>{welcome_kaspersky}</p>
	<table style='width:100%'>
	";
	
	if($users->kas_installed){
		$html=$html . "<tr>
	<td width=1%>
		" . Field_numeric_checkbox_img('enable_kasper',$users->KasxFilterEnabled,'{enable_disable}').
	"</td>
	<td>{enable_kaspersky_antispam}</td>
	</tr>";
	
	
	}
	
	if($users->KAV_MILTER_INSTALLED){
		$html=$html . "<tr>
	<td width=1%>
		" . Field_numeric_checkbox_img('enable_kav',$users->KAVMILTER_ENABLED,'{enable_disable}').
	"</td>
	<td>{enable_kaspersky_antivirus}</td>
	</tr>";
	
	
	}	
	
	$html=$html . "
	<tr><td colspan=2 align='right'><input type='button' OnClick=\"javascript:kavStep2();\" value='{next}&nbsp;&raquo;'></td></tr>
	
	</table>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}


function main_kaspersky_level(){
	
		include_once('ressources/class.kas-filter.inc');
		$kas=new kas_single();
		$OPT_SPAM_RATE_LIMIT_TABLE=array(4=>"{maximum}",3=>"{high}",2=>"{normal}",1=>"{minimum}");
		$OPT_SPAM_RATE_LIMIT=Field_array_Hash($OPT_SPAM_RATE_LIMIT_TABLE,'OPT_SPAM_RATE_LIMIT',$kas->main_array["OPT_SPAM_RATE_LIMIT"]);
		
		$html="
		<table style='width:100%'>
		<tr>
			<td align='right' nowrap valign='top'><strong>{OPT_SPAM_RATE_LIMIT}:</strong></td>
			<td valign='top'>$OPT_SPAM_RATE_LIMIT</td>
			<td valign='top'>{OPT_SPAM_RATE_LIMIT_TEXT}</td>
		<tr><td colspan=3 align='right'><input type='button' OnClick=\"javascript:kavStep3();\" value='{next}&nbsp;&raquo;'></td></tr>
		</tr>
		</table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'kas.group.rules.php');		
	
	
}

function kaspersky(){
	if(!isset($GLOBALS["CLASS_USERS"])){$GLOBALS["CLASS_USERS"]=new usersMenus();$users=$GLOBALS["CLASS_USERS"];}else{$users=$GLOBALS["CLASS_USERS"];}
	$GLOBALS["ICON_FAMILY"]="ANTIVIRUS";
	if(!$users->POSTFIX_INSTALLED){return false;}
	$users->LoadModulesEnabled();
	$page=CurrentPageName();
	if($users->kas_installed OR $users->KAV_MILTER_INSTALLED){
		$img="bigkav-64.png";
		$js="Loadjs('configure.server.php?script=enable_kasper')";
		return Paragraphe($img,"{enable_kaspersky}","{enable_kaspersky_text}","javascript:$js");
		return LocalParagraphe("enable_kaspersky","enable_kaspersky_text","Loadjs('configure.server.php?script=enable_kasper')","bigkav24.png");
	}
	
}
function icon_update_clamav(){
	$GLOBALS["ICON_FAMILY"]="ANTIVIRUS";
	if(!isset($GLOBALS["CLASS_USERS"])){$GLOBALS["CLASS_USERS"]=new usersMenus();$users=$GLOBALS["CLASS_USERS"];}else{$users=$GLOBALS["CLASS_USERS"];}
	if(!$users->CLAMAV_INSTALLED){return null;}
	if(!$users->KASPERSKY_WEB_APPLIANCE){return null;}
	if(!$users->KASPERSKY_SMTP_APPLIANCE){return null;}
	if(!$users->AsAnAdministratorGeneric){return null;}
	$js="Loadjs('clamav.update.php');";
	$img="clamav-update-48.png";
	return Paragraphe($img,"{UPDATE_CLAMAV}","{UPDATE_CLAMAV_EXPLAIN}","javascript:$js");
	return LocalParagraphe("UPDATE_CLAMAV","UPDATE_CLAMAV_EXPLAIN",$js,$img);				
}	
function icon_troubleshoot(){
	$GLOBALS["ICON_FAMILY"]="REPAIR";
	$users=new usersMenus();
	if(!$users->AsArticaAdministrator){return null;}
	$js="Loadjs('index.troubleshoot.php');";
	$img="64-troubleshoot-index.png";
	return Paragraphe($img,"{troubleshoot}","{troubleshoot_explain}","javascript:$js");
	return LocalParagraphe("troubleshoot","troubleshoot_explain",$js,$img);				
		
}
function icon_externalports(){
	$GLOBALS["ICON_FAMILY"]="SECURITY";
	if(!isset($GLOBALS["CLASS_USERS"])){$GLOBALS["CLASS_USERS"]=new usersMenus();$users=$GLOBALS["CLASS_USERS"];}else{$users=$GLOBALS["CLASS_USERS"];}
	if(!$users->AsArticaAdministrator){return null;}
	if($users->KASPERSKY_WEB_APPLIANCE){return null;}
	
	$js="Loadjs('external-ports.php')";
	$img="64-bind.png";
	return Paragraphe($img,"{EXTERNAL_PORTS}","{EXTERNAL_PORTS_TEXT}","javascript:$js");
	return LocalParagraphe("EXTERNAL_PORTS","EXTERNAL_PORTS_TEXT",$js,$img);	
	}	
function postmaster(){
	$GLOBALS["ICON_FAMILY"]="SMTP";
$sock=new sockets();
if($sock->GET_INFO("EnablePostfixMultiInstance")==1){return null;}	
if(!isset($GLOBALS["CLASS_USERS"])){$GLOBALS["CLASS_USERS"]=new usersMenus();$users=$GLOBALS["CLASS_USERS"];}else{$users=$GLOBALS["CLASS_USERS"];}
if(!$users->POSTFIX_INSTALLED){return false;}	
return LocalParagraphe("postmaster","postmaster_text","Loadjs('postfix.postmaster.php')","folder-useraliases2-48.png");	
}

function Firstwizard(){
	return LocalParagraphe("first_settings","first_settings","Loadjs('configure.server.php?script=wizard')","folder-update-48.png");	
}

function wizard_kaspersky_appliance_smtp(){
	$GLOBALS["ICON_FAMILY"]="ANTIVIRUS";
	if(!isset($GLOBALS["CLASS_USERS"])){$GLOBALS["CLASS_USERS"]=new usersMenus();$users=$GLOBALS["CLASS_USERS"];}else{$users=$GLOBALS["CLASS_USERS"];}
	if(!$users->KASPERSKY_SMTP_APPLIANCE){return null;}
	return LocalParagraphe("wizard_kaspersky_smtp_appliance","wizard_kaspersky_smtp_appliance_text_wizard","Loadjs('wizard.kaspersky.appliance.php')","kaspersky-wizard-48.png");
}


function clamav(){
	$GLOBALS["ICON_FAMILY"]="ANTIVIRUS";
	$page=CurrentPageName();
	if(!isset($GLOBALS["CLASS_USERS"])){$GLOBALS["CLASS_USERS"]=new usersMenus();$users=$GLOBALS["CLASS_USERS"];}else{$users=$GLOBALS["CLASS_USERS"];}
	if($users->KASPERSKY_WEB_APPLIANCE){return null;}
	if($users->KASPERSKY_SMTP_APPLIANCE){return null;}
	$img="clamav-64.png";
	$js="Loadjs('clamav.index.php');";
	return Paragraphe($img,"{clamav_av}","{clamav_av_text}","javascript:$js");
	return LocalParagraphe("clamav_av","clamav_av_text","Loadjs('clamav.index.php');","clamav-48.png");
	}



function nic_settings(){
	$GLOBALS["ICON_FAMILY"]="NETWORK";
	$page=CurrentPageName();
	$js="Loadjs('system.nic.config.php?js=yes')";
	$img="64-win-nic.png";
	return Paragraphe($img,"{nic_settings}","{nic_settings_text}","javascript:$js");
	return LocalParagraphe("nic_settings","nic_settings_text",$js,$img);
	}
	
function wizard_backup(){
	$GLOBALS["ICON_FAMILY"]="BACKUP";
$page=CurrentPageName();
	$js="Loadjs('wizard.backup-all.php')";
	$img="48-dar-index.png";
	return LocalParagraphe("manage_backups","manage_backups_text",$js,"48-dar-index.png");
	

	
}

function scancomputers(){
	$GLOBALS["ICON_FAMILY"]="NETWORK";
	$js="Loadjs('computer-browse.php')";
	$img="64-win-nic-browse.png";
	return Paragraphe($img,"{browse_computers}","{browse_computers_text}","javascript:$js");
	return LocalParagraphe("browse_computers","browse_computers_text",$js,$img);
	}
	
	function sharenfs(){
		$GLOBALS["ICON_FAMILY"]="SYSTEM";
	if(!isset($GLOBALS["CLASS_USERS"])){$GLOBALS["CLASS_USERS"]=new usersMenus();$users=$GLOBALS["CLASS_USERS"];}else{$users=$GLOBALS["CLASS_USERS"];}
	if(!$users->NFS_SERVER_INSTALLED){return null;}
	$js="Loadjs('SambaBrowse.php')";
	$img="nfs-64.png";
	
	return Paragraphe($img,"{nfs_share}","{nfs_share_text}","javascript:$js");
	return LocalParagraphe("nfs_share","nfs_share_text",$js,$img);
	}

function clientnfs(){
	$GLOBALS["ICON_FAMILY"]="SYSTEM";
	if(!isset($GLOBALS["CLASS_USERS"])){$GLOBALS["CLASS_USERS"]=new usersMenus();$users=$GLOBALS["CLASS_USERS"];}else{$users=$GLOBALS["CLASS_USERS"];}
	if(!$users->autofs_installed){return null;}
	$js="Loadjs('nfs-client.php')";
	$img="database-network-64.png";
	return Paragraphe($img,"{NFS_CLIENT}","{NFS_CLIENT_TEXT}","javascript:$js");
	return LocalParagraphe("NFS_CLIENT","NFS_CLIENT_TEXT",$js,$img);
}		
	
function postfix_events(){
	$GLOBALS["ICON_FAMILY"]="SMTP";
	$js="Loadjs('postfix-realtime-events.php')";
	$img="folder-logs-643.png";
	return Paragraphe($img,"{postfix_realtime_events}","{postfix_realtime_events_text}","javascript:$js");
	return LocalParagraphe("postfix_realtime_events","postfix_realtime_events_text",$js,$img);
	}

function dmidecode(){
	$GLOBALS["ICON_FAMILY"]="SYSTEM";
	$js="Loadjs('dmidecode.php')";
	$img="system-64.org.png";
	return Paragraphe($img,"{dmidecode}","{dmidecode_text}","javascript:$js");
	return LocalParagraphe("dmidecode","dmidecode_text",$js,$img);
	}		
function icon_update_artica(){
	if(!isset($GLOBALS["CLASS_USERS"])){$GLOBALS["CLASS_USERS"]=new usersMenus();$users=$GLOBALS["CLASS_USERS"];}else{$users=$GLOBALS["CLASS_USERS"];}
	if(!$users->AsArticaAdministrator){return null;}
	$GLOBALS["ICON_FAMILY"]="UPDATE";
	$js="Loadjs('artica.update.php?js=yes')";
	$img="folder-64-artica-update.png";
	$tpl=new templates();
	return Paragraphe($img,"{artica_autoupdate}","{artica_autoupdate_text}","javascript:$js");
	return $tpl->_ENGINE_parse_body(LocalParagraphe("artica_autoupdate","artica_autoupdate_text",$js,$img),'system.index.php');	
	}
	
function icon_update_spamassassin_blacklist(){
	if(!isset($GLOBALS["CLASS_USERS"])){$GLOBALS["CLASS_USERS"]=new usersMenus();$users=$GLOBALS["CLASS_USERS"];}else{$users=$GLOBALS["CLASS_USERS"];}
	if(!$users->spamassassin_installed){return null;}
	if(!$users->AsPostfixAdministrator){return null;}
	$js="Loadjs('sa-blacklist.php')";
	$img="64-spam.png";
	$tpl=new templates();
	return Paragraphe($img,"{APP_SA_BLACKLIST}","{APP_SA_BLACKLIST_AUTOUPDATE}","javascript:$js");
	return $tpl->_ENGINE_parse_body(LocalParagraphe("APP_SA_BLACKLIST","APP_SA_BLACKLIST_AUTOUPDATE",$js,$img),'system.index.php');	
	}	
	
function statkaspersky(){
	$GLOBALS["ICON_FAMILY"]="ANTIVIRUS";
	$js="YahooWin(580,'kaspersky.index.php','Kaspersky');";
	$img="bigkav-64.png";		
	return Paragraphe($img,"{Kaspersky}","{kaspersky_av_text}","javascript:$js");
	return LocalParagraphe("Kaspersky","kaspersky_av_text","YahooWin(580,'kaspersky.index.php','Kaspersky');","bigkav-48.png");
}
function sysinfos(){
	$GLOBALS["ICON_FAMILY"]="SYSTEM";
	return Paragraphe("scan-64.png", "{sysinfos}", "{sysinfos_text}","javascript:s_PopUp('phpsysinfo/index.php',1000,600,1);");
}
function certificate(){
	$GLOBALS["ICON_FAMILY"]="SECURITY";
	$js="Loadjs('postfix.tls.php?js-certificate=yes')";
	$img="certificate-download-64.png";	
	return Paragraphe($img,"{ssl_certificate}","{ssl_certificate_text}","javascript:$js");
	return LocalParagraphe("ssl_certificate","ssl_certificate_text","Loadjs('postfix.tls.php?js-certificate=yes')","folder-lock-48.png");

}
function apt(){
	$GLOBALS["ICON_FAMILY"]="UPDATE";
	$js="Loadjs('artica.repositories.php')";
	$img="DEBIAN_mirror-64.png";	
	if(!isset($GLOBALS["CLASS_USERS"])){$GLOBALS["CLASS_USERS"]=new usersMenus();$users=$GLOBALS["CLASS_USERS"];}else{$users=$GLOBALS["CLASS_USERS"];}
	if(!$users->AsDebianSystem){return null;}
	return Paragraphe($img,"{repository_manager}","{repository_manager_text}","javascript:$js");
	return LocalParagraphe("repository_manager","repository_manager_text","Loadjs('artica.repositories.php')","folder-lock-48.png");
}
function incremental_backup(){
	$GLOBALS["ICON_FAMILY"]="BACKUP";
	$js="Loadjs('wizard.backup-all.php')";
	$img="64-dar-index.png";
	return Paragraphe($img,"{manage_backups}","{manage_backups}","javascript:$js");
	return LocalParagraphe("manage_backups","manage_backups",$js,"48-dar-index.png");
}
function atica_perf(){
	$GLOBALS["ICON_FAMILY"]="SYSTEM";
	$img="perfs-64.png";	
	$js="Loadjs('artica.performances.php')";
	
	return Paragraphe($img,"{artica_performances}","{artica_performances_text}","javascript:$js");
	
}
function applis(){
	$GLOBALS["ICON_FAMILY"]="SOFTWARES";
	$js="Loadjs('setup.index.php?js=yes')";
	$img="bg-applis-64.png";
	return Paragraphe($img,"{install_applis}","{install_applis_text}","javascript:$js");
		
		}
function icon_system(){
	$GLOBALS["ICON_FAMILY"]="SYSTEM";
	if(!isset($GLOBALS["CLASS_USERS"])){$GLOBALS["CLASS_USERS"]=new usersMenus();$users=$GLOBALS["CLASS_USERS"];}else{$users=$GLOBALS["CLASS_USERS"];}
	if(!$users->AsAnAdministratorGeneric){return null;}
	$js="Loadjs('admin.index.services.status.php?js=yes')";
	$img="rouage-64.png";
	return Paragraphe($img,"{manage_services}","{manage_services_text}","javascript:$js");
}


function icon_memory(){
	$GLOBALS["ICON_FAMILY"]="SYSTEM";
	if(!isset($GLOBALS["CLASS_USERS"])){$GLOBALS["CLASS_USERS"]=new usersMenus();$users=$GLOBALS["CLASS_USERS"];}else{$users=$GLOBALS["CLASS_USERS"];}
	if(!$users->AsAnAdministratorGeneric){return null;}
	$js="Loadjs('system.memory.php?js=yes')";
	$img="bg_memory-64.png";
	return Paragraphe($img,"{system_memory}","{system_memory_text}","javascript:$js");
	
	}
function icon_harddrive(){
	$GLOBALS["ICON_FAMILY"]="SYSTEM";
	if(!isset($GLOBALS["CLASS_USERS"])){$GLOBALS["CLASS_USERS"]=new usersMenus();$users=$GLOBALS["CLASS_USERS"];}else{$users=$GLOBALS["CLASS_USERS"];}
	if(!$users->AsAnAdministratorGeneric){return null;}
	$js="Loadjs('system.internal.disks.php')";
	$img="64-hd.png";
	return Paragraphe($img,"{internal_hard_drives}","{internal_hard_drives_text}","javascript:$js");
		
	}	
function icon_adduser(){
	$GLOBALS["ICON_FAMILY"]="USER";
	if(!isset($GLOBALS["CLASS_USERS"])){$GLOBALS["CLASS_USERS"]=new usersMenus();$users=$GLOBALS["CLASS_USERS"];}else{$users=$GLOBALS["CLASS_USERS"];}
	$sock=new sockets();
	if(!$users->AllowAddUsers){return null;}
	if($users->ARTICA_META_ENABLED){
		if($sock->GET_INFO("AllowArticaMetaAddUsers")<>1){return null;}
	}
	$js="Loadjs('create-user.php');";
	$img="identity-add-64.png";
	return Paragraphe($img,"{add_user}","{add user explain}","javascript:$js");
			
}		