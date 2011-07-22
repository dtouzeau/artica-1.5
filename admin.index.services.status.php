<?php
session_start();
include_once('ressources/class.templates.inc');
include_once('ressources/class.html.pages.inc');
include_once('ressources/class.cyrus.inc');
include_once('ressources/class.main_cf.inc');
include_once('ressources/charts.php');
include_once('ressources/class.mysql.inc');
include_once('ressources/class.ini.inc');
include_once('ressources/class.os.system.inc');
include_once(dirname(__FILE__)."/ressources/class.monit.inc");


$users=new usersMenus();
if($users->AsArticaAdministrator==true or $users->AsPostfixAdministrator or $user->AsSquidAdministrator){}else{
	$tpl=new templates();
	echo $tpl->javascript_parse_text("alert('{ERROR_NO_PRIVS}');");
	die();
}

if(isset($_GET["disable-nfs"])){disable_nfs_js();exit;}
if(isset($_GET["DISABLE_NFS"])){disable_nfs_perform();exit;}


if($_GET["filterby"]==null){$_GET["filterby"]="1;1;0;0";}
if(isset($_GET["status"])){SERVICES_STATUS();exit;}
if(isset($_GET["mem"])){echo memory();exit;}
if(isset($_GET["svc"])){SERVICES_SVC();exit;}
if(isset($_GET["myfilter"])){filter();exit;}
if(isset($_GET["SERVICE_OPTIONS_JS"])){SERVICE_OPTIONS_JS();exit;}
if(isset($_GET["SERVICE_OPTIONS_POPUP"])){SERVICE_OPTIONS_POPUP();exit;}
if(isset($_GET["js"])){js();exit;}
if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["MONIT"])){MONIT_JS();exit;}
if(isset($_GET["MONIT-POPUP"])){MONIT_POPUP();exit;}
if(isset($_GET["MONIT-SAVE"])){MONIT_SAVE();exit;}
if(isset($_GET["ARTICA-WATCHDOG-SAVE"])){WATCHDOG_SAVE();exit;}

js();


function popup(){
	
	$html="
	<input type='hidden' id='filterby' value='{$_GET["filterby"]}'>
	<div id='myfilter' style='width:370px;padding:3px;border:0px dotted #CCCCCC;text-align:right;margin-left:350px'>".filter(true)."</div>

	
	
</td>
</tr>
</table>	
	". main_tabs()."
	<br>
	<div id=events></div>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function MONIT_JS(){
	$page=CurrentPageName();
	$tpl=new templates();
	$key=$_GET["MONIT"];
	$title=$tpl->_ENGINE_parse_body("{{$key}}");
	$html="
	function ServiceMONITStart(){
		YahooWin2('550','$page?MONIT-POPUP=$key','$title');
	}
	

	
var X_ServiceMonitEdit= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	YahooWin2Hide();
	}
		


function ServiceMonitEdit(){
		var XHR = new XHRConnection();
		XHR.appendData('MONIT-SAVE','$key');
		XHR.appendData('totalmem',document.getElementById('totalmem').value);
		XHR.appendData('totalmem_cycles',document.getElementById('totalmem_cycles').value);
		XHR.appendData('totalcpu',document.getElementById('totalcpu').value);
		XHR.appendData('totalcpu_cycles',document.getElementById('totalcpu_cycles').value);		
		document.getElementById('monit-form').innerHTML='<center><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',X_ServiceMonitEdit);		
	
}	
	
	ServiceMONITStart();
	";
	
	echo $html;	
	
}

function MONIT_SAVE(){
	$keyP=$_GET["MONIT-SAVE"];
	$monit=new monit();

	while (list ($key, $line) = each ($_GET) ){
		$monit->params["$keyP"][$key]=$line;
	}
	
	$monit->save();
	
}

function WATCHDOG_SAVE(){
	
	$enabled=$_GET["enabled"];
	if(preg_match("#WATCHDOG-(.+)#",$_GET["name"],$re)){
		$app=$re[1];
	}
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->GET_INFO("ArticaWatchDogList")));
	$array[$app]=$enabled;
	$conf=base64_encode(serialize($array));
	$sock->SaveConfigFile($conf,"ArticaWatchDogList");
	
	
}


function MONIT_POPUP(){
	
	$key=$_GET["MONIT-POPUP"];
	$monit=new monit();
	
	for($i=1;$i<60;$i++){
		if($i<10){$mns="0{$i}";}else{$mns=$i;}
		$minutes[$i]="{$mns}&nbsp;mn";
		
	}
	
	for($i=1;$i<101;$i++){
		if($i<10){$mns="0{$i}";}else{$mns=$i;}
		$pourcentage_cpu[$i]="$mns%";
		
	}	
	
	
	
	$totalmem_cycles=$monit->params["$key"]["totalmem_cycles"];
	
	$totalmem=$monit->params["$key"]["totalmem"];
	if($totalmem==null){$totalmem=100;}
	
	$totalcpu_cycles=$monit->params["$key"]["totalcpu_cycles"];
	$totalcpu=$monit->params["$key"]["totalcpu"];
	if($totalmem_cycles==null){$totalmem_cycles=5;}
	if($totalcpu_cycles==null){$totalcpu_cycles=5;}
	if($totalcpu==null){$totalcpu=95;}
	
	$html="
	<div id='monit-form'>
	<H3>{{$key}}/{APP_MONIT}</H3>
	<p style='font-size:13px'>{APP_MONIT_EXPLAIN}</p>
	
	<hr>
	
	<table style='width:100%'>
	<tr>
		<td colspan=4><strong style='font-size:16px'>{resource_testing}</td>
	</tR>
		
	</tr>	
	<tr>
		<td class=legend>{max_daemon_memory}:</td>
		<td nowrap>". Field_text('totalmem',$totalmem,"width:60px")."&nbsp;Mb</td>
		<td class=legend>{max_daemon_cycles}:</td>
		<td>". Field_array_Hash($minutes,'totalmem_cycles',$totalmem_cycles)."</td>
	</tr>	
	<tr>
		<td class=legend>{max_daemon_cpu}:</td>
		<td>". Field_array_Hash($pourcentage_cpu,'totalcpu',$totalcpu)."</td>
		<td class=legend>{max_daemon_cycles}:</td>
		<td>". Field_array_Hash($minutes,'totalcpu_cycles',$totalcpu_cycles)."</td>
	</tr>	
	<tr>
		<td colspan=4 align='right'>
			<hr>
				". button("{edit}","ServiceMonitEdit()")."
		</td>
	</tR>	
	
	
	</table>
	</div>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}



function disable_nfs_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$text=$tpl->javascript_parse_text('{disable_service_text_operation}')." ".$tpl->javascript_parse_text('{APP_NFS}')."\\n".$tpl->javascript_parse_text('{perform_operation_ask}');
	
	$html="
		var DISABLE_NFS= function (obj) {
					var results=obj.responseText;
					if(results.length>0){
						alert(results);
						document.getElementById('wwwInfos').innerHTML='';
						}
					
			}				
	
		if(confirm('$text')){
			var XHR = new XHRConnection();
			XHR.appendData('DISABLE_NFS','yes');
			XHR.sendAndLoad('$page', 'GET',DISABLE_NFS);
	
		}
	
	";
	
	echo $html;
}

function disable_nfs_perform(){
	$tpl=new templates();
	$sock=new sockets();
	$sock->SET_INFO("StopMonitoNFS",1);
	echo $tpl->javascript_parse_text('{success}');
	}


function js(){
	
	
$page=CurrentPageName();
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{services_status}');
$idmd='ArticaServicesIndex_';
$html="
var {$idmd}timerID  = null;
var {$idmd}timerID1  = null;
var {$idmd}tant=0;
var {$idmd}reste=0;
var {$idmd}timeout=0;
var filters='';

function {$idmd}demarre(){
	{$idmd}tant = {$idmd}tant+1;
	{$idmd}reste=10-{$idmd}tant;
	if ({$idmd}tant < 10 ) {                           
	{$idmd}timerID = setTimeout(\"{$idmd}demarre()\",5000);
      } else {
			  {$idmd}tant = 0;
              {$idmd}ChargeLogs();
			  {$idmd}demarre(); 
   }
}

function demar1(){
   tant = tant+1;
	if (tant < 2 ) {
      timerID = setTimeout(\"demar1()\",1000);
                
   } else {
		tant = 0;                           
		ChargeLogs();                 
        demarre(); 
   }
}

function SetFilter(){
 	var filters=document.getElementById('running').value+';'+document.getElementById('stopped').value+';'+document.getElementById('disabled').value+';'+document.getElementById('not_installed').value;
 	document.getElementById('filterby').value=filters;
	{$idmd}ChargeLogs();
}

function {$idmd}ChargeLogs(){
	
	{$idmd}ChargeLogsWait()
	}
	
function {$idmd}ChargeLogsWait(){
	{$idmd}timeout={$idmd}timeout+1;
	
	if({$idmd}timeout>100){
		alert('time-out: filterby id !');
		return;
	}
	
		if(!document.getElementById('filterby')){
			setTimeout(\"{$idmd}ChargeLogsWait()\",500);
			return;
		}
	
		if(!document.getElementById('services_status')){
			setTimeout(\"{$idmd}ChargeLogsWait()\",500);
			return;
		}	
	
		{$idmd}timeout=0;
		setTimeout(\"{$idmd}myfilter()\",1000);
		setTimeout(\"{$idmd}ServicesStatus()\",1500);
		setTimeout(\"{$idmd}mymem()\",2000);
	}
	
	
	function {$idmd}ServicesStatus(){
		var srv='';
		var filters='';
		{$idmd}timeout={$idmd}timeout+1;
		if(!document.getElementById('filterby')){
			setTimeout(\"{$idmd}ChargeLogsWait()\",500);
			return;
		}			
		{$idmd}timeout=0;
		if(document.getElementById('service_switch')){srv=document.getElementById('service_switch').value;}
		if(document.getElementById('filterby')){		   
			var filters=document.getElementById('filterby').value;   
			//LoadAjax('services_status','$page?status=yes&hostname={$_GET["hostname"]}&section='+srv+'&filterby='+filters);
		}	   
	}
	
	function {$idmd}myfilter(){
		if(document.getElementById('filterby')){
			LoadAjax('myfilter','$page?myfilter=yes&filterby='+filters+'&hostname={$_GET["hostname"]}');
		}   
	}
	
	function {$idmd}mymem(){
		{$idmd}timeout={$idmd}timeout+1;
		
		if({$idmd}timeout>100){
				alert('$page: time-out: id=mymem !');
				return;
			}
		
		  var myl=document.getElementById('services_status').innerHTML;
		   if(myl.length<100){
		   	setTimeout('{$idmd}mymem()',500);
		   	return;
		   }
		   
		if(!document.getElementById('mymem')){
			setTimeout(\"{$idmd}mymem()\",500);
			return;
		}	   
		   
		{$idmd}timeout=0;   
		LoadAjax('mymem','$page?mem=yes&hostname={$_GET["hostname"]}'); 
	}	
	
	
function ServicesPageStart(){
	//YahooWinS(750,'$page?popup=yes','$title');
	$('#BodyContent').load('$page?popup=yes');
	//setTimeout(\"{$idmd}ChargeLogs()\",500);
	//setTimeout(\"{$idmd}demarre()\",1000);
	

}

var x_SwitchWatchDog= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	RefreshTab('services_status');
	}

function SwitchWatchDog(app){
	var XHR = new XHRConnection();
	if(document.getElementById(app).checked){
		XHR.appendData('enabled','1');
	}else{
		XHR.appendData('enabled','0');
	}
	
	
	XHR.appendData('ARTICA-WATCHDOG-SAVE','yes');
	
	XHR.appendData('name',app);
	XHR.sendAndLoad('$page', 'GET',x_SwitchWatchDog);	
	
}

ServicesPageStart();
";	
	
	echo $html;
}





function SERVICE_OPTIONS_JS(){
	$page=CurrentPageName();
	$key=$_GET["SERVICE_OPTIONS_JS"];
	if(!is_file("ressources/logs/global.status.ini")){echo "alert('".$tpl->_ENGINE_parse_body("{error_no_global_status}")."');";exit;}
	$bsini=new Bs_IniHandler("ressources/logs/global.status.ini");
	if($bsini->_params["$key"]["service_name"]==null){return null;}
	$tpl=new templates();
	$bsini=new Bs_IniHandler("ressources/logs/global.status.ini");	
	$prod=$bsini->_params[$key]["service_name"];
	$title=$tpl->_ENGINE_parse_body("{{$prod}}");
	$html="
	function ServicePopupStart(){
		YahooWin2('350','$page?SERVICE_OPTIONS_POPUP=$key','$title');
	}
	ServicePopupStart();
	";
	
	echo $html;
	
	
}

function SERVICE_OPTIONS_POPUP(){
	$key=$_GET["SERVICE_OPTIONS_POPUP"];
	if(!is_file("ressources/logs/global.status.ini")){echo $tpl->_ENGINE_parse_body("{error_no_global_status}");exit;}
	$bsini=new Bs_IniHandler("ressources/logs/global.status.ini");
	if($bsini->_params["$key"]["service_name"]==null){return null;}
	$tpl=new templates();
	$prod=$bsini->_params[$key]["service_name"];
	$title=$tpl->_ENGINE_parse_body("{{$prod}}");
	
	$status=DAEMON_STATUS_ROUND($key,$bsini);
	
	
	
	if($bsini->_params["$key"]["running"]==1){
	$js_service="javascript:Loadjs('StartStopServices.php?APP={$bsini->_params["$key"]["service_name"]}&cmd={$bsini->_params["$key"]["service_cmd"]}&action=stop')";
	$button="<input type='button' OnClick=\"$js_service\" value='{stop}&nbsp;&raquo;'>";
	}else{
	$js_service="javascript:Loadjs('StartStopServices.php?APP={$bsini->_params["$key"]["service_name"]}&cmd={$bsini->_params["$key"]["service_cmd"]}&action=start')";	
	$button="<input type='button' OnClick=\"$js_service\" value='{start}&nbsp;&raquo;'>";
	}
	
	$html="
	<div id='$title'>
	<H1>$title</H1>
	$status
	<div style='width:100%;text-align:right'>$button</div>
	</div>
	";
	echo $tpl->_ENGINE_parse_body($html);
}



function memory(){
	include_once("ressources/class.os.system.tools.inc");
	$page=CurrentPageName();
	$os=new os_system();
	return  $os->html_Memory_usage()."<script>
		LoadAjax('system-events','artica.events.php?tri=yes&context=system&process=&without-tri=yes');
		
	function articaShowEvent(ID){
		 YahooWin6('750','artica.events.php?ShowID='+ID,'EV::'+ID);
	}
	</script>
	";
	
	
}

function filter($return=false){
$running=0;
$stopped=0;
$disabled=0;
$not_installed=0;
$tbl=explode(";",$_GET["filterby"]);
$running=$tbl[0];
$stopped=$tbl[1];
$disabled=$tbl[2];
$not_installed=$tbl[3];
		
	
	

	
	$tpl=new templates();
	if($return){return $tpl->_ENGINE_parse_body($html);}
	echo $tpl->_ENGINE_parse_body($html);
	
	
}


function main_tabs(){
	$page=CurrentPageName();
	$users=new usersMenus();
	$tpl=new templates();
	$array["index"]='{index}';
	$array["artica_services"]='{artica_services}';
	
	if($users->POSTFIX_INSTALLED){$array["postfix_services"]='{postfix_services}';}
	if($users->cyrus_imapd_installed){$array["mail_services"]='{mail_services}';}
	if($users->ZARAFA_INSTALLED){$array["mail_services"]='{mail_services}';}	
	if($users->SAMBA_INSTALLED){$array["samba_services"]='{samba_services}';}
	if($users->MLDONKEY_INSTALLED){$array["samba_services"]='{samba_services}';}	
	if($users->SQUID_INSTALLED){$array["squid_services"]='{squid_services}';}
	
while (list ($num, $ligne) = each ($array) ){
		if($_GET["section"]==$num){$class="id=tab_current";}else{$class=null;}
		$ligne=$tpl->_ENGINE_parse_body($ligne);
		if(strlen($ligne)>15){$ligne=texttooltip(substr($ligne,0,15)."...",$ligne,null,null,1);}
		$html[]= "<li><a href=\"$page?status=yes&section=$num&filterby={$_GET["filterby"]}\"><span>$ligne</span></a></li>\n";
	
		
		
		//$html=$html . "<li><a href=\"javascript:LoadAjax('main_config_postfix','$page?main=$num&hostname=$hostname')\" $class>$ligne</a></li>\n";
			
		}
	
	
	return "
	<div id=services_status style='width:100%;height:730px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#services_status').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			

			});
		</script>";		
	
}


function INDEX(){
	$page=CurrentPageName();
	$html="
	<table style='width:100%;background-image:url(img/bg_eye.png);background-repeat:no-repeat;background-color:transparent'>
	<tr>
		<td valign='top'>
		<H1>{services_status}</H1>
		<div style='font-size:16px;background-color:transparent;'>
		{services_status_text}
		<br>
		{services_status_text_explain}
		</div>
	</td>
	<td valign='top'>
	<div id='mymem'></div>
	</td>
	</tr>
	</table>
	<hr>
	<div id='system-events' style='width:100%;height:520px;overflow:auto;margin-top:5px'></div>
	<script>
	LoadAjax('mymem','$page?mem=yes'); 
	</script>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}


function SERVICES_STATUS(){
	
	if($_GET["section"]=="index"){INDEX();exit;}
	
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	
	$GLOBALS["ArticaWatchDogList"]=unserialize(base64_decode($sock->GET_INFO("ArticaWatchDogList")));
	
	$users=new usersMenus();
	$users->LoadModulesEnabled();
	
	if($_GET["section"]==null){$_GET["section"]="artica_services";}
	//echo($sock->getfile('daemons_status'));
	
	$table_header="<tr>
		<th width=1% style='$style1$style2'>&nbsp;</td>
		<th  nowrap>{daemon}</td>
		<th nowrap>&nbsp;</td>
		<th width=1% nowrap>{memory}</td>
		<th width=1% nowrap>{virtual_memory}</td>
		<th width=1%>{version}</td>
		<th nowrap >{uptime}</td>
		</tr>";

	$inifile=dirname(__FILE__)."/ressources/logs/global.status.ini";
	$ini->loadFile($inifile);
	
	
	if($_GET["section"]=="artica_services"){
			$html="
			<div style='heigth:550px;overflow:auto'>
			<table style='width:98%;margin:0px;padding:5px;'>$table_header";
			
			if($users->VMWARE_HOST){
				if($users->VMWARE_TOOLS_INSTALLED){
					$html=$html . BuildRow($users,$ini->_params["APP_VMTOOLS"],"{APP_VMTOOLS}");
				}
			}
			
			if($users->VIRTUALBOX_HOST){
				if($users->APP_VBOXADDINTION_INSTALLED){
					$html=$html . BuildRow($users,$ini->_params["APP_VBOXADDITIONS"],"{APP_VBOXADDITIONS}");
				}
			}
			
			
			$html=$html . BuildRow($users,$ini->_params["ARTICA"],"{APP_ARTICA}");
			$html=$html . BuildRow($users,$ini->_params["APP_ARTICA_NOTIFIER"],"{APP_ARTICA_NOTIFIER}");			
			$html=$html . BuildRow($users,$ini->_params["LIGHTTPD"],"{APP_LIGHTTPD}");	
			$html=$html . BuildRow($users,$ini->_params["ARTICA_WATCHDOG"],"{APP_ARTICA_WATCHDOG}");
			$html=$html . BuildRow($users,$ini->_params["APP_ARTICA_STATUS"],"{APP_ARTICA_STATUS}");
			$html=$html . BuildRow($users,$ini->_params["APP_ARTICA_EXECUTOR"],"{APP_ARTICA_EXECUTOR}");
			$html=$html . BuildRow($users,$ini->_params["APP_ARTICA_BACKGROUND"],"{APP_ARTICA_BACKGROUND}");
			$html=$html . BuildRow($users,$ini->_params["APP_SYSLOGER"],"{APP_SYSLOGER}");
			$html=$html . BuildRow($users,$ini->_params["SASLAUTHD"],"{APP_SASLAUTHD}");
			$html=$html . BuildRow($users,$ini->_params["BOA"],"{APP_BOA}");
			$html=$html . BuildRow($users,$ini->_params["FRAMEWORK"],"{APP_FRAMEWORK}");
			$html=$html . BuildRow($users,$ini->_params["APP_APACHE_SRC"],"{APP_APACHE_SRC}");
			$html=$html . BuildRow($users,$ini->_params["APP_GROUPWARE_APACHE"],"{APP_GROUPWARE_APACHE}");
			$html=$html . BuildRow($users,$ini->_params["LDAP"],"{APP_LDAP}");
			$html=$html . BuildRow($users,$ini->_params["ARTICA_MYSQL"],"{APP_MYSQL_ARTICA}");
			$html=$html . BuildRow($users,$ini->_params["APP_GREENSQL"],"{APP_GREENSQL}");
			$html=$html . BuildRow($users,$ini->_params["APP_PDNS"],"{APP_PDNS}");
			$html=$html . BuildRow($users,$ini->_params["APP_PDNS_INSTANCE"],"{APP_PDNS_INSTANCE}");
			$html=$html . BuildRow($users,$ini->_params["PDNS_RECURSOR"],"{APP_PDNS_RECURSOR}");
			$html=$html . BuildRow($users,$ini->_params["DNSMASQ"],"{APP_DNSMASQ}");
			$html=$html . BuildRow($users,$ini->_params["APP_DDCLIENT"],"{APP_DDCLIENT}");							
			$html=$html . BuildRow($users,$ini->_params["BIND9"],"{APP_BIND9}");
			$html=$html . BuildRow($users,$ini->_params["DHCPD"],"{APP_DHCP}");
			$html=$html . BuildRow($users,$ini->_params["APP_NSCD"],"{APP_NSCD}");				
			
			if(!$users->KASPERSKY_WEB_APPLIANCE){
				$html=$html . BuildRow($users,$ini->_params["CLAMAV"],"{APP_CLAMAV}");	
				$html=$html . BuildRow($users,$ini->_params["FRESHCLAM"],"{APP_FRESHCLAM}");
			}
			$html=$html . BuildRow($users,$ini->_params["APP_SNORT"],"{APP_SNORT}");
				while (list ($key, $array) = each ($ini->_params) ){
					if(preg_match("#APP_SNORT:(.+)#",$key,$re)){
						$html=$html . BuildRow($users,$ini->_params[$key],"{APP_SNORT}",$re[1]);
					}
				}
				reset($ini->_params);			
			
			$html=$html . BuildRow($users,$ini->_params["APP_KAV4FS"],"{APP_KAV4FS}");
			$html=$html . BuildRow($users,$ini->_params["APP_KAV4FS_AVS"],"{APP_KAV4FS_AVS}");
			
			
			$html=$html . BuildRow($users,$ini->_params["APP_OCSI"],"{APP_OCSI}");
			$html=$html . BuildRow($users,$ini->_params["APP_OCSI_DOWNLOAD"],"{APP_OCSI_DOWNLOAD}");
			$html=$html . BuildRow($users,$ini->_params["APP_OCSI_LINUX_CLIENT"],"{APP_OCSI_LINUX_CLIENT}");

			$html=$html . BuildRow($users,$ini->_params["KRETRANSLATOR_HTTPD"],"{APP_KRETRANSLATOR_HTTPD}");	
			$html=$html . BuildRow($users,$ini->_params["SYSLOGNG"],"{APP_SYSLOGNG}");
			$html=$html . BuildRow($users,$ini->_params["APP_OPENSSH"],"{APP_OPENSSH}");
			$html=$html . BuildRow($users,$ini->_params["APP_ARTICA_AUTH_TAIL"],"{APP_ARTICA_AUTH_TAIL}");
			
			
			$html=$html . BuildRow($users,$ini->_params["COLLECTD"],"{APP_COLLECTD}");
			$html=$html . BuildRow($users,$ini->_params["SMARTD"],"{APP_SMARTMONTOOLS}");
			$html=$html . BuildRow($users,$ini->_params["APP_AUDITD"],"{APP_AUDITD}");
			$html=$html . BuildRow($users,$ini->_params["APP_VNSTAT"],"{APP_VNSTAT}");
			
			
			
			$html=$html . BuildRow($users,$ini->_params["APP_ZABBIX_SERVER"],"{APP_ZABBIX_SERVER}");
			$html=$html . BuildRow($users,$ini->_params["APP_ZABBIX_AGENT"],"{APP_ZABBIX_AGENT}");						
			$html=$html . BuildRow($users,$ini->_params["IPTABLES"],"{APP_IPTABLES}");
			$html=$html . BuildRow($users,$ini->_params["APP_AMACHI"],"{APP_AMACHI}");		
			$html=$html . BuildRow($users,$ini->_params["NTPD"],"{APP_NTPD}");
			$html=$html . BuildRow($users,$ini->_params["APP_AUTOFS"],"{APP_AUTOFS}");	
			$html=$html . BuildRow($users,$ini->_params["APP_NFS"],"{APP_NFS}");					
			$html=$html . BuildRow($users,$ini->_params["PRELOAD"],"{APP_PRELOAD}");
			$html=$html . BuildRow($users,$ini->_params["CONSOLEKIT"],"{APP_CONSOLEKIT}");
			$html=$html . BuildRow($users,$ini->_params["GDM"],"{APP_GDM}");
			$html=$html . BuildRow($users,$ini->_params["XFCE"],"{APP_XFCE}");
			$html=$html . BuildRow($users,$ini->_params["INETD"],"{APP_INETD}");
			$html=$html . BuildRow($users,$ini->_params["APP_RSYNC"],"{APP_RSYNC}");
			$html=$html . BuildRow($users,$ini->_params["OBM2"],"{APP_OBM2}");	
			$html=$html . BuildRow($users,$ini->_params["DOTCLEAR"],"{APP_DOTCLEAR}");				
			$html=$html . BuildRow($users,$ini->_params["PUREFTPD"],"{APP_PUREFTPD}");
			$html=$html . BuildRow($users,$ini->_params["APP_MLDONKEY"],"{APP_MLDONKEY}");
			$html=$html . BuildRow($users,$ini->_params["APP_DROPBOX"],"{APP_DROPBOX}");
			$html=$html . BuildRow($users,$ini->_params["APP_SABNZBDPLUS"],"{APP_SABNZBDPLUS}");
			$html=$html . "</table></div>";
			$html_A=$html;
	}
	
	if($_GET["section"]=="postfix_services"){
			if($users->POSTFIX_INSTALLED){
				$EnablePostfixMultiInstance=$sock->GET_INFO("EnablePostfixMultiInstance");
				$html="
				<div style='heigth:550px;overflow:auto'>
				<table style='width:98%;margin:0px;padding:5px;'>$table_header";
				$html=$html . BuildRow($users,$ini->_params["POSTFIX"],"{APP_POSTFIX}");
				
				if($EnablePostfixMultiInstance==1){
					reset($ini->_params);
					while (list ($key, $array) = each ($ini->_params) ){
						if(preg_match("#POSTFIX-MULTI-(.+)#",$key,$re)){$html=$html . BuildRow($users,$ini->_params["$key"],"{APP_POSTFIX}",$re[1]);}
						if(preg_match("#APP_CROSSROADS:(.+)#",$key,$re)){$html=$html . BuildRow($users,$ini->_params["$key"],"{APP_CROSSROADS}",$re[1]);}					
					}
				}

				reset($ini->_params);
				while (list ($key, $array) = each ($ini->_params) ){
					if(preg_match("#APP_POSTFWD2:(.+)#",$key,$re)){
						$html=$html . BuildRow($users,$ini->_params[$key],"{APP_POSTFWD2}",$re[1]);
					}
				}
				reset($ini->_params);	

				
				
				
				$html=$html . BuildRow($users,$ini->_params["APP_CROSSROADS"],"{APP_CROSSROADS}");
				$html=$html . BuildRow($users,$ini->_params["ARTICA_MYSQMAIL"],"{APP_ARTICA_MYSQMAIL}");
				$html=$html . BuildRow($users,$ini->_params["APP_POSTFILTER"],"{APP_POSTFILTER}");
				$html=$html . BuildRow($users,$ini->_params["JCHECKMAIL"],"{APP_JCHECKMAIL}");
				$html=$html . BuildRow($users,$ini->_params["MILTER_GREYLIST"],"{APP_MILTERGREYLIST}");	
				$html=$html . BuildRow($users,$ini->_params["ASSP"],"{APP_ASSP}");
				$html=$html . BuildRow($users,$ini->_params["AMAVISD"],"{APP_AMAVISD_NEW}");	
				$html=$html . BuildRow($users,$ini->_params["AMAVISD_MILTER"],"{APP_AMAVISD_MILTER}");
				$html=$html . BuildRow($users,$ini->_params["APP_CLUEBRINGER"],"{APP_CLUEBRINGER}");
				$html=$html . BuildRow($users,$ini->_params["APP_OPENDKIM"],"{APP_OPENDKIM}");
				$html=$html . BuildRow($users,$ini->_params["APP_MILTER_DKIM"],"{APP_MILTER_DKIM}");					
				$html=$html . BuildRow($users,$ini->_params["MAILARCHIVER"],"{APP_MAILARCHIVER}");	
				$html=$html . BuildRow($users,$ini->_params["DKIM_FILTER"],"{APP_DKIM_FILTER}");
				$html=$html . BuildRow($users,$ini->_params["MIMEDEFANG"],"{APP_MIMEDEFANG}");		
				$html=$html . BuildRow($users,$ini->_params["MIMEDEFANGX"],"{APP_MIMEDEFANGX}");			
				$html=$html . BuildRow($users,$ini->_params["CLAMAV_MILTER"],"{APP_CLAMAV_MILTER}");		
				$html=$html . BuildRow($users,$ini->_params["MAILFROMD"],"{APP_MAILFROMD}");		
				$html=$html . BuildRow($users,$ini->_params["SQLGREY"],"{APP_SQLGREY}");
				$html=$html . BuildRow($users,$ini->_params["SPAMASS_MILTER"],"{APP_SPAMASS_MILTER}");
				$html=$html . BuildRow($users,$ini->_params["SPAMASSASSIN"],"{APP_SPAMASSASSIN}");			
				$html=$html . BuildRow($users,$ini->_params["KAVMILTER"],"{APP_KAVMILTER}");
				$html=$html . BuildRow($users,$ini->_params["KAS3"],"{APP_KAS3}");
				$html=$html . BuildRow($users,$ini->_params["KAS_MILTER"],"{APP_KAS3_MILTER}");
				$html=$html . BuildRow($users,$ini->_params["MAILMAN"],"{APP_MAILMAN}");
				$html=$html . BuildRow($users,$ini->_params["APP_ARTICA_POLICY"],"{APP_ARTICA_POLICY}");		
				$html=$html . BuildRow($users,$ini->_params["STUNNEL"],"{APP_STUNNEL}");
				


				
				
				$html=$html . "</table></div>";
				$html_B=$html;
				}
	}
	
	if($_GET["section"]=="squid_services"){
		if($users->SQUID_INSTALLED){
			$html="<table style='width:98%;margin:0px;padding:5px;'>$table_header";
			$html=$html . BuildRow($users,$ini->_params["SQUID"],"{APP_SQUID}");
			$html=$html . BuildRow($users,$ini->_params["APP_ARTICA_SQUID_TAIL"],"{APP_ARTICA_SQUID_TAIL}");
			$html=$html . BuildRow($users,$ini->_params["APP_SQUID_CLAMAV_TAIL"],"{APP_SQUID_CLAMAV_TAIL}");		
			$html=$html . BuildRow($users,$ini->_params["KAV4PROXY"],"{APP_KAV4PROXY}");
			$html=$html . BuildRow($users,$ini->_params["DANSGUARDIAN"],"{APP_DANSGUARDIAN}");
			$html=$html . BuildRow($users,$ini->_params["ARTICA_DANS_TAIL"],"{APP_ARTICA_DANSGUARDIAN_TAIL}");
			$html=$html . BuildRow($users,$ini->_params["APP_PROXY_PAC"],"{APP_PROXY_PAC}");
			$html=$html . BuildRow($users,$ini->_params["APP_SQUIDGUARD_HTTP"],"{APP_SQUIDGUARD_HTTP}");
			$html=$html . BuildRow($users,$ini->_params["APP_ARTICA_SQUIDGUARDTAIL"],"{APP_ARTICA_SQUIDGUARDTAIL}");
			$html=$html . BuildRow($users,$ini->_params["APP_UFDBGUARD"],"{APP_UFDBGUARD}");
			$html=$html . BuildRow($users,$ini->_params["APP_UFDBGUARD_TAIL"],"{APP_UFDBGUARD_TAIL}");
			
			

			
			
			$html=$html . "</table></div>";
			$html_C=$html;	
			}
	}

	
	
	if($_GET["section"]=="mail_services"){
		$html="
		<div style='heigth:550px;overflow:auto'>
		<table style='width:98%;margin:0px;padding:5px;'>$table_header";
		$html=$html . BuildRow($users,$ini->_params["CYRUSIMAP"],"{APP_CYRUS}");
		$html=$html . BuildRow($users,$ini->_params["ROUNDCUBE"],"{APP_ROUNDCUBE}");

				$arrayZarafa[]="APP_ZARAFA";
				$arrayZarafa[]="APP_ZARAFA_GATEWAY";
				$arrayZarafa[]="APP_ZARAFA_SPOOLER";
				$arrayZarafa[]="APP_ZARAFA_WEB";
				$arrayZarafa[]="APP_ZARAFA_MONITOR";
				$arrayZarafa[]="APP_ZARAFA_DAGENT";
				$arrayZarafa[]="APP_ZARAFA_ICAL";
				$arrayZarafa[]="APP_ZARAFA_INDEXER";
				$arrayZarafa[]="APP_ZARAFA_LICENSED";				
				
				while (list ($num, $ligne) = each ($arrayZarafa) ){
					$html=$html . BuildRow($users,$ini->_params[$ligne],"{{$ligne}}");
		
				}		
		
		$html=$html . BuildRow($users,$ini->_params["FETCHMAIL"],"{APP_FETCHMAIL}");
		$html=$html . BuildRow($users,$ini->_params["FETCHMAIL_LOGGER"],"{APP_FETCHMAIL_LOGGER}");	
		$html=$html . BuildRow($users,$ini->_params["P3SCAN"],"{APP_P3SCAN}");
		
		$html=$html . BuildRow($users,$ini->_params["OBM_APACHE"],"{APP_OBM_APACHE}");
		$html=$html . "</table></div>";
		$html_D=$html;	
	}
	
	if($_GET["section"]=="samba_services"){	
		if($users->SAMBA_INSTALLED){
			
			$html="
			<div style='heigth:550px;overflow:auto'>
			<table style='width:98%;margin:0px;padding:5px;'>$table_header";
			$html=$html . BuildRow($users,$ini->_params["SAMBA_SMBD"],"{APP_SAMBA_SMBD}");	
			$html=$html . BuildRow($users,$ini->_params["SAMBA_NMBD"],"{APP_SAMBA_NMBD}");
			$html=$html . BuildRow($users,$ini->_params["SAMBA_WINBIND"],"{APP_SAMBA_WINBIND}");
			$html=$html . BuildRow($users,$ini->_params["APP_GREYHOLE"],"{APP_GREYHOLE}");
			$html=$html . BuildRow($users,$ini->_params["APP_AUDITD"],"{APP_AUDITD}");
			$html=$html . BuildRow($users,$ini->_params["SAMBA_SCANNEDONLY"],"{APP_SCANNED_ONLY}");
			$html=$html . BuildRow($users,$ini->_params["KAV4SAMBA"],"{APP_KAV4SAMBA}");
			$html=$html . BuildRow($users,$ini->_params["APP_KAV4FS"],"{APP_KAV4FS}");
			$html=$html . BuildRow($users,$ini->_params["APP_KAV4FS_AVS"],"{APP_KAV4FS_AVS}");
			$html=$html . BuildRow($users,$ini->_params["CUPS"],"{APP_CUPS}");
			$html=$html . BuildRow($users,$ini->_params["APP_MLDONKEY"],"{APP_MLDONKEY}");
			$html=$html . BuildRow($users,$ini->_params["APP_BACKUPPC"],"{APP_BACKUPPC}");
			$html=$html . BuildRow($users,$ini->_params["APP_OCSI"],"{APP_OCSI}");
			$html=$html . BuildRow($users,$ini->_params["APP_OCSI_DOWNLOAD"],"{APP_OCSI_DOWNLOAD}");	
			$html=$html . BuildRow($users,$ini->_params["APP_DROPBOX"],"{APP_DROPBOX}");
			$html=$html . BuildRow($users,$ini->_params["APP_OPENSSH"],"{APP_OPENSSH}");	
			$html=$html . BuildRow($users,$ini->_params["APP_SABNZBDPLUS"],"{APP_SABNZBDPLUS}");	
			$html=$html . "</table></div>";
			$html_F=$html;				
		}
	}
	
	
	$artica_services="$html_A<br>";
	$postfix_services="$html_B<br>";
	$mail_services="$html_D<br>";
	$samba_services="$html_F<br>";
	$squid_services="$html_C<br>";
	


	switch ($_GET["section"]) {
		case "artica_services":$services=$artica_services;break;
		case "postfix_services":$services=$postfix_services;break;
		case "mail_services":$services=$mail_services;break;
		case "samba_services":$services=$samba_services;break;
		case "squid_services":$services=$squid_services;break;
	
		default:$services=$artica_services;break;
	}
	

	$html_E="
	<input type='hidden' name='service_switch' id='service_switch' value='{$_GET["section"]}'>
	</center>
		$services
	</center>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html_E);
	
}

function BuildRow($users,$array,$application_name,$titleAdd=null){
	include_once("ressources/class.status.inc");
//print_r($array);
	
	$page=CurrentPageName();
	//$sat=new status(1);
	$pid=null;
	$app=str_replace('{','',$application_name);
	$app=str_replace('}','',$app);
	$running=0;
	$stopped=0;
	$disabled=0;
	$not_installed=0;
	$tbl=explode(";",$_GET["filterby"]);
	$filter_running=$tbl[0];
	$stopped=$tbl[1];
	$filter_disabled=$tbl[2];
	$not_installed=$tbl[3];	
		

	$application_installed=$array["application_installed"];
	$master_memory=$array["master_memory"];
	$master_version=$array["master_version"];
	$master_version=str_replace(' ','&nbsp;',$master_version);
	$pattern_version=$array["pattern_version"];
	$service_cmd=$array["service_cmd"];
	$service_disabled=$array["service_disabled"];
	$monit=$array["monit"];
	$pid=$array["master_pid"];
	$status=$array["status"];
	$cpu_percent_total="&nbsp;";
	$memory_percent_total="&nbsp;";
	$uptime=$array["uptime"];
	$master_cached_memory=$array["master_cached_memory"];
	$processes_number=$array["processes_number"];
	$watchdog_features=$array["watchdog_features"];
	$explain=$array["explain"];
	if($explain<>null){$explain_text="<hr>{{$explain}}<hr>";}
	
	if($pid>0){$application_installed=1;}
	if(strlen($master_version)>0){$application_installed=1;}
	
	//writelogs("$application_name installed:$application_installed ,pid=$pid memory=$master_memory",__FUNCTION__,__FILE__,__LINE__);
	
	if($monit==1){
		if(!isset($GLOBALS["MONIT_ARRAY"])){
			$sock=new sockets();
			$GLOBALS["MONIT_ARRAY"]=unserialize(base64_decode($sock->getFrameWork("cmd.php?monit-status=yes")));
		}
		
		$monit_status=$GLOBALS["MONIT_ARRAY"][$app]["status"];
		$monit_uptime=$GLOBALS["MONIT_ARRAY"][$app]["uptime"];
		$memory_kilobytes_total=FormatBytes($GLOBALS["MONIT_ARRAY"][$app]["memory kilobytes total"]);
		$memory_percent_total=$GLOBALS["MONIT_ARRAY"][$app]["memory percent total"];
		$cpu_percent_total=$GLOBALS["MONIT_ARRAY"][$app]["cpu percent total"];
		$application_installed=1;
		$pid=$GLOBALS["MONIT_ARRAY"][$app]["pid"];
		$uptime=$GLOBALS["MONIT_ARRAY"][$app]["uptime"];
		$service_disabled=0;
		$application_installed=1;
		$monit_settings=imgtootltip("ico2658.png","{MONIT_SETTINGS}","Loadjs('$page?MONIT=$app')");
		
		switch ($monit_status) {
			case "monitored":$array["running"]=1;$status="sleeping";break;
			case "not monitored":$array["running"]=0;$status="stopped";break;
			default:
				;
			break;
		}

	}
	
	$info=imgtootltip("icon_info.gif","{processes}:<strong>$processes_number</strong><br>{pid}:<strong>$pid</strong>$explain_text");
	$watchdog_display=Field_checkbox("none",1,0,null,null,true);
	if($watchdog_features==1){
		
		if($GLOBALS["ArticaWatchDogList"][$app]==null){$GLOBALS["ArticaWatchDogList"][$app]=1;}
		$watchdog_display=Field_checkbox("WATCHDOG-$app",1,$GLOBALS["ArticaWatchDogList"][$app],"SwitchWatchDog('WATCHDOG-$app')","{ARTICA_WATCHDOG_INFOS}");
	}
	
	
	
	
	if($service_disabled==null){$service_disabled=-1;}
	
	
	writelogs("$application_name service_disabled=$service_disabled application_installed=$application_installed",__FUNCTION__,__FILE__,__LINE__);
	if($master_memory>0){$master_memory=FormatBytes($master_memory);}
	if($master_cached_memory>0){$master_cached_memory=FormatBytes($master_cached_memory);}
	$style1='padding:3px;border-bottom:1px dotted #CCCCCC;font-size:11px;';
	
	if($application_installed<>1){
		$img='unknown24.png';
		$status="{not_installed}";
		$style2='color:#CCCCCC';
		$master_version="&nbsp;";
		$master_memory="0";
		if($not_installed==0){return null;}
		
	}else{
		if($array["running"]==1){
			writelogs("$app running",__FUNCTION__,__FILE__,__LINE__);
			$img="ok24.png";
			if(strlen($service_cmd)>0){
				$js_service="StartStopService('$service_cmd','0','$application_name');";
				$tooltip="{stop_this_service} $application_name";
				
			}
			if($filter_running==0){
				writelogs("$app running but remove cause filter_running=$filter_running",__FUNCTION__,__FILE__,__LINE__);
				return null;}
			
			
		}else{
			
			if($service_disabled==0){
				$img='warning24.png';
				$status="{disabled}";
				$tooltip="{service_disabled}";
				if($filter_disabled==0){return null;}
			}else {
				$img="danger24.png";
				$status="{stopped}";
				if(strlen($service_cmd)>0){
					$js_service="StartStopService('$service_cmd','1','$application_name');";
					$tooltip="{start_this_service} $application_name";
					}				
			}
		}
	}
	
	if($service_disabled==0){
		$img="warning24.png";
		$js_service=null;
		$tooltip="{service_disabled}";
		$status="{disabled}";
		if($filter_disabled==0){return null;}
	}

	
	if($pattern_version==null){$pattern_version="&nbsp;";}
	 if($pid==0){$master_memory=0;}
	 
	 
	 if($js_service<>null){
	 	$image=imgtootltip($img,$tooltip,$js_service);
	 }else{
	 	$image="<img src='img/$img'>";
	 }
	 
	 if($memory_kilobytes_total<>null){$master_memory=$memory_kilobytes_total;}
	 if($titleAdd<>null){$titleAdd="<div><i>$titleAdd</i></div>";}
		
		$html="<tr . " .CellRollOver().">
		<td width=1% style='$style1$style2'>
			$image
		</td>
		<td style='$style1$style2' nowrap>$application_name$titleAdd</td>
		<td style='$style1$style2' nowrap valign='middle' align='center'>
		<table>
		<tr>
			<td>$info</td>
			<td>$watchdog_display</td>
		</tr>
		</table>
		</td>
		<td style='$style1$style2' width=1% nowrap>&nbsp;$master_memory</td>
		<td style='$style1$style2' width=1% nowrap>&nbsp;$master_cached_memory</td>
		<td style='$style1$style2' width=1% nowrap>&nbsp;$master_version</td>
		<td style='$style1$style2' nowrap width=1% nowrap>$uptime</td>
		</tr>
		";
		
		
	return $html;
	
}

function SERVICES_SVC(){
	
	switch ($_GET["svc"]) {
		case 0:SERVICES_SVC_0();break;
		case 1:SERVICES_SVC_1();break;
		default:
			break;
	}
	
	
}

function SERVICES_SVC_0(){
	$cmd=$_GET["cmd"];
	$typ=$_GET["typ"];
	$apps=$_GET["apps"];
	$html=
	"<table style='width:100%'>
	<input type='hidden' id='cmd' value='$cmd'>
	<input type='hidden' id='typ' value='$typ'>
	<tr>
		<td valign='top' width=1%><img src='img/rouage-90.png'></td>
		<td valign='top'>
			" . RoundedLightWhite("<div id='pos_0' style='width:100%;height:250px;overflow:auto'></div>")."
			
			
	</table>";
	
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}


function SERVICES_SVC_1(){
	$soc=new sockets();
	$datas=$soc->getfile("svc;{$_GET["typ"]};{$_GET["cmd"]}");
	
	if($_GET["typ"]==0){
		$title="{start}";
		$js_service="javascript:StartStopService('{$_GET["cmd"]}','1','{$_GET["apps"]}');";
	}else{
		$title="{stop}";
		$js_service="javascript:StartStopService('{$_GET["cmd"]}','0','{$_GET["apps"]}');";
	}
	
	$tbl=explode("\n",$datas);
	$html="<table style='width:100%'>
	<tr>
	<td colspan=2 align='right'><input type='button' Onclick=\"$js_service\" value='&laquo;&nbsp;&nbsp;&nbsp;$title&nbsp;&nbsp;&nbsp;&raquo;'></td>
	</tr>
	";
	
	while (list ($num, $ligne) = each ($tbl) ){
			if(trim($ligne<>null)){
			$html=$html . "
			<tr>
				<td width=1%>
					<img src='img/fw_bold.gif'>
				</td>
				<td>" . htmlentities($ligne)."</td>
			</tr>
			";
			}
		
	}
	

	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html. "</table>");
}

?>




