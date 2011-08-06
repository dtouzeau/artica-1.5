<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.groups.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.system.network.inc');
	
	
	
	$user=new usersMenus();
	if($user->SQUID_INSTALLED==false){die('not allowed');}
	if($user->AsSquidAdministrator==false){die('not allowed');}
	
	if($_GET["main"]=="index"){echo index();exit;}
	if($_GET["main"]=="daemons"){echo daemons();exit;}
	if($_GET["main"]=="clamav"){echo clamav();exit;}
	if($_GET["main"]=="logs"){echo logs();exit;}
	if(isset($_GET["MaxKeepAliveRequests"])){save_settings();exit;}
	if(isset($_GET["srv_clamav_SendPercentData"])){save_settings();exit;}
	
	
	js();
	
	
function js(){
	$tpl=new templates();
	$page=CurrentPageName();
	$title=$tpl->_ENGINE_parse_body('{cicap_title}');
	$title1=$tpl->_ENGINE_parse_body('{clamav_settings}');
	$title2=$tpl->_ENGINE_parse_body('{clamav_settings}');
	
	$start="loadcicap();";
	if(isset($_GET["runthis"])){$start=$_GET["runthis"]."();";}
	
	$html="
	
		function loadcicap(){
			YahooWin(700,'$page?main=index','$title');
		
		}
		
		function cicap_daemons(){
			YahooWin2(550,'$page?main=daemons','$title');
		
		}		

		function cicap_clamav(){
			YahooWin2(550,'$page?main=clamav','$title');
		
		}					
		
		function cicap_logs(){
			YahooWin2(550,'$page?main=logs','$title');
		
		}			
		
		$start
	
	";
	echo $html;
}

function index(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$array["daemons"]='{daemon_settings}';
	$array["clamav"]='{clamav_settings}';
	$array["logs"]='{icap_logs}';
	
	while (list ($num, $ligne) = each ($array) ){
		$html[]= "<li><a href=\"$page?main=$num\"><span>$ligne</span></a></li>\n";
	}
	
	
	echo $tpl->_ENGINE_parse_body("
	<div id=main_config_cicap style='width:100%;height:550px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_cicap').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			
			
			});
		</script>");		
	
	return;
	$daemon=Paragraphe('rouage-64.png','{daemon_settings}','{daemon_settings_text}',"javascript:cicap_daemons()");
	$clamav=Paragraphe('clamav-64.png','{clamav_settings}','{clamav_settings_text}',"javascript:cicap_clamav()");
	$logs=Paragraphe('folder-logs-643.png','{icap_logs}','{icap_logs_text}',"javascript:cicap_logs()");
	
	//
	
	$html="<H1>{cicap_title}</H1>
	
	<table style='width:100%'>
	<tr>
		<td valign='top'>$daemon</td>
		<td valign='top'>$clamav</td>
	</tr>
		
	<tr>
		<td valign='top'>$logs</td>
		<td valign='top'>&nbsp;</td>
	</tr>
	</table>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function logs(){
	$page=CurrentPageName();
	
	
	
	$dd=logs_datas();
$html="<H1>{icap_logs}</H1>
	<p class=caption>{icap_logs_text}</p>
	
	". RoundedLightWhite("<div style='width:99%;height:300px;overflow:auto'>$dd</div>");
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
	
}

function logs_datas(){
	
	$sock=new sockets();
	$datas=$sock->getfile('cicapevents');
	$tbl=explode("\n",$datas);
	if(!is_array($tbl)){return null;}
	$tbl=array_reverse($tbl);
	while (list ($num, $line) = each ($tbl) ){
		if(trim($line==null)){continue;}
		$line=htmlspecialchars($line);
		$html=$html."<div><code style='font-size:10px'>$line</code></div>";
		

		
	}
	
	return $html;
	
	
}


function clamav(){
	
	$ci=new cicap();
	$page=CurrentPageName();
	$html="
	<div style='font-size:14px;margin:8px'>{clamav_settings_text}</div>
	
	<div id='ffmcc2'>
	<table style='width:100%'>
	<tr>
		<td class=legend>{ENABLE_CLAMAV}:</td>
		<td style=';font-size:13px'>" . Field_checkbox('EnableClamavInCiCap',1,$ci->EnableClamavInCiCap,'EnableClamavInCiCapCheck()')."</td>
		<td>&nbsp;</td>
	</tr>	
	
	
	
	
	<tr>
		<td class=legend>{srv_clamav.SendPercentData}:</td>
		<td style=';font-size:13px'>" . Field_text('srv_clamav.SendPercentData',$ci->main_array["CONF"]["srv_clamav.SendPercentData"],'width:30px;font-size:13px;padding:3px')."&nbsp;%</td>
		<td>" . help_icon('{srv_clamav.SendPercentData_text}')."</td>
	</tr>

	<tr>
		<td class=legend>{srv_clamav.StartSendPercentDataAfter}:</td>
		<td style=';font-size:13px'>" . Field_text('srv_clamav.StartSendPercentDataAfter',$ci->main_array["CONF"]["srv_clamav.StartSendPercentDataAfter"],'width:30px;font-size:13px;padding:3px')."&nbsp;M</td>
		<td>" . help_icon('{srv_clamav.StartSendPercentDataAfter_text}')."</td>
	</tr>	
	
	<tr>
		<td class=legend>{srv_clamav.MaxObjectSize}:</td>
		<td style=';font-size:13px'>" . Field_text('srv_clamav.MaxObjectSize',$ci->main_array["CONF"]["srv_clamav.MaxObjectSize"],'width:30px;font-size:13px;padding:3px')."&nbsp;M</td>
		<td>" . help_icon('{srv_clamav.MaxObjectSize_text}')."</td>
	</tr>

	<tr>
		<td class=legend>{srv_clamav.ClamAvMaxFilesInArchive}:</td>
		<td style=';font-size:13px'>" . Field_text('srv_clamav.ClamAvMaxFilesInArchive',$ci->main_array["CONF"]["srv_clamav.ClamAvMaxFilesInArchive"],'width:30px;font-size:13px;padding:3px')."&nbsp;{files}</td>
		<td>" . help_icon('{srv_clamav.ClamAvMaxFilesInArchive}')."</td>
	</tr>	
	
	<tr>
		<td class=legend>{srv_clamav.ClamAvMaxFileSizeInArchive}:</td>
		<td style=';font-size:13px'>" . Field_text('srv_clamav.ClamAvMaxFileSizeInArchive',$ci->main_array["CONF"]["srv_clamav.ClamAvMaxFileSizeInArchive"],'width:30px;font-size:13px;padding:3px')."&nbsp;M</td>
		<td>" . help_icon('{srv_clamav.ClamAvMaxFileSizeInArchive}')."</td>
	</tr>

	<tr>
		<td class=legend>{srv_clamav.ClamAvMaxRecLevel}:</td>
		<td style=';font-size:13px'>" . Field_text('srv_clamav.ClamAvMaxRecLevel',$ci->main_array["CONF"]["srv_clamav.ClamAvMaxRecLevel"],'width:30px;font-size:13px;padding:3px')."&nbsp;M</td>
		<td>" . help_icon('{srv_clamav.ClamAvMaxRecLevel}')."</td>
	</tr>		
	
	<tr>
		<td colspan=3 align='right'><hr>
		". button("{apply}","SaveICapCLam()")."
			
		</td>
	</tr>
	</table>
	</div>
	
	<script>
	
	
	var x_SaveICapCLam=function(obj){
     var tempvalue=obj.responseText;
	  if(tempvalue.length>3){alert(tempvalue);}
	  RefreshTab('main_config_cicap');
	
	}	
	
	function SaveICapCLam(){
		var XHR = new XHRConnection();
	    XHR.appendData('srv_clamav.SendPercentData',document.getElementById('srv_clamav.SendPercentData').value);
	    XHR.appendData('srv_clamav.StartSendPercentDataAfter',document.getElementById('srv_clamav.StartSendPercentDataAfter').value);
	    XHR.appendData('srv_clamav.MaxObjectSize',document.getElementById('srv_clamav.MaxObjectSize').value);
	    XHR.appendData('srv_clamav.ClamAvMaxFilesInArchive',document.getElementById('srv_clamav.ClamAvMaxFilesInArchive').value);
	    XHR.appendData('srv_clamav.ClamAvMaxFileSizeInArchive',document.getElementById('srv_clamav.ClamAvMaxFileSizeInArchive').value);
	    XHR.appendData('srv_clamav.ClamAvMaxRecLevel',document.getElementById('srv_clamav.ClamAvMaxRecLevel').value);
	    
	    if(document.getElementById('EnableClamavInCiCap').checked){
	    	XHR.appendData('EnableClamavInCiCap',1);
		}else{
			XHR.appendData('EnableClamavInCiCap',0);
		}
	    
	    document.getElementById('ffmcc2').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>'; 
       	XHR.sendAndLoad('$page', 'GET',x_SaveICapCLam);
	}
	
	function EnableClamavInCiCapCheck(){
	 
		document.getElementById('srv_clamav.SendPercentData').disabled=true;
		document.getElementById('srv_clamav.StartSendPercentDataAfter').disabled=true;
		document.getElementById('srv_clamav.MaxObjectSize').disabled=true;
		document.getElementById('srv_clamav.ClamAvMaxFilesInArchive').disabled=true;
		document.getElementById('srv_clamav.ClamAvMaxFileSizeInArchive').disabled=true;
		document.getElementById('srv_clamav.ClamAvMaxRecLevel').disabled=true;
		if(document.getElementById('EnableClamavInCiCap').checked){
			document.getElementById('srv_clamav.SendPercentData').disabled=false;
			document.getElementById('srv_clamav.StartSendPercentDataAfter').disabled=false;
			document.getElementById('srv_clamav.MaxObjectSize').disabled=false;
			document.getElementById('srv_clamav.ClamAvMaxFilesInArchive').disabled=false;
			document.getElementById('srv_clamav.ClamAvMaxFileSizeInArchive').disabled=false;
			document.getElementById('srv_clamav.ClamAvMaxRecLevel').disabled=false;		
		}else{
			
		}
	
	}
	
	setTimeout('EnableClamavInCiCapCheck()',500);
	
</script>	
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}


function daemons(){
	
	$ci=new cicap();
	$page=CurrentPageName();
	$sock=new sockets();
	$EnableSquidGuardInCiCAP=$sock->GET_INFO("EnableSquidGuardInCiCAP");
	if($EnableSquidGuardInCiCAP==null){$EnableSquidGuardInCiCAP=1;}
	$EnableUfdbGuard=$sock->GET_INFO("EnableUfdbGuard");
	$users=new usersMenus();
	
	if(!$users->SQUIDGUARD_INSTALLED){
		$disableSquiduard=true;
		$EnableSquidGuardInCiCAP=0;	
	}
	
	if($users->APP_UFDBGUARD_INSTALLED){
		if($EnableUfdbGuard==1){
			$disableSquiduard=true;
			$EnableSquidGuardInCiCAP=0;
		}
	}
	
	if($disableSquiduard){$DisableSquidGuardCheckCicap="DisableSquidGuardCheckCicap();";}
	
	
	$notifyVirHTTPServer=false;
	if($ci->main_array["CONF"]["ViralatorMode"]==1){
	if(preg_match('#https://(.*?)/exec#',$ci->main_array["CONF"]["VirHTTPServer"],$re)){
		if(trim($re[1])==null){$notifyVirHTTPServer=true;}
		if(trim($re[1])=="127.0.0.1"){$notifyVirHTTPServer=true;}
		if(trim($re[1])=="localhost"){$notifyVirHTTPServer=true;}
	}}
	
	if($notifyVirHTTPServer==true){
		$color="color:red;font-weight:bolder";
	}
	
	
	for($i=1;$i<13;$i++){
		$f[$i]=$i;
	}
	
	
	$html="
	<div class=explain>{daemon_settings_text}</div>
	<input type='hidden' id='EnableClamavInCiCapCheck' value='$ci->EnableClamavInCiCap'>
	<div id='ffmcc1'>
	<table style='width:100%' class=table_form>
	<tr>
		<td class=legend>{enable_squidguard}:</td>
		<td>" . Field_checkbox("EnableSquidGuardInCiCAP",1,$EnableSquidGuardInCiCAP)."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend>{Timeout}:</td>
		<td>" . Field_text('Timeout',$ci->main_array["CONF"]["Timeout"],'width:30px;font-size:13px;padding:3px')."&nbsp;{seconds}</td>
		<td>" . help_icon('{Timeout_text}')."</td>
	</tr>
	
	<tr>
		<td class=legend nowrap>{MaxKeepAliveRequests}:</td>
		<td>" . Field_text('MaxKeepAliveRequests',$ci->main_array["CONF"]["MaxKeepAliveRequests"],'width:30px;font-size:13px;padding:3px')."&nbsp;</td>
		<td>" . help_icon('{MaxKeepAliveRequests_text}')."</td>
	</tr>	
	
	<tr>
		<td class=legend nowrap>{KeepAliveTimeout}:</td>
		<td>" . Field_text('KeepAliveTimeout',$ci->main_array["CONF"]["KeepAliveTimeout"],'width:30px;font-size:13px;padding:3px')."&nbsp;{seconds}</td>
		<td>" . help_icon('{KeepAliveTimeout_text}')."</td>
	</tr>
	
	<tr>
		<td class=legend nowrap>{MaxServers}:</td>
		<td>" . Field_text('MaxServers',$ci->main_array["CONF"]["MaxServers"],'width:30px;font-size:13px;padding:3px')."&nbsp;</td>
		<td>" . help_icon('{MaxServers_text}')."</td>
	</tr>	
	
	
	<tr>
		<td class=legend nowrap>{MinSpareThreads}:</td>
		<td>" . Field_text('MinSpareThreads',$ci->main_array["CONF"]["MinSpareThreads"],'width:30px;font-size:13px;padding:3px')."&nbsp;</td>
		<td>" . help_icon('{MinSpareThreads_text}')."</td>
	</tr>		
	
	<tr>
		<td class=legend nowrap>{MaxSpareThreads}:</td>
		<td>" . Field_text('MaxSpareThreads',$ci->main_array["CONF"]["MaxSpareThreads"],'width:30px;font-size:13px;padding:3px')."&nbsp;</td>
		<td>" . help_icon('{MaxSpareThreads_text}')."</td>
	</tr>	

	<tr>
		<td class=legend nowrap>{ThreadsPerChild}:</td>
		<td>" . Field_text('ThreadsPerChild',$ci->main_array["CONF"]["ThreadsPerChild"],'width:30px;font-size:13px;padding:3px')."&nbsp;</td>
		<td>" . help_icon('{ThreadsPerChild_text}')."</td>
	</tr>	

	<tr>
		<td class=legend nowrap>{MaxRequestsPerChild}:</td>
		<td>" . Field_text('MaxRequestsPerChild',$ci->main_array["CONF"]["MaxRequestsPerChild"],'width:30px;font-size:13px;padding:3px')."&nbsp;</td>
		<td>" . help_icon('{MaxRequestsPerChild_text}')."</td>
	</tr>	
		<tr>
		<td class=legend>{debug_mode}:</td>
		<td>" . Field_array_Hash($f,"DebugLevel",$ci->main_array["CONF"]["DebugLevel"],null,null,0,'font-size:13px;padding:3px')."&nbsp;</td>
		<td>" . help_icon('{log level_text}')."</td>
	</tr>
	<tr><td colspan=3>&nbsp;</td></tr>
	<tr><td colspan=3 style='border-top:1px solid #CCCCCC'>&nbsp;</td></tr>
	
	<tr>
		<td class=legend nowrap>{ViralatorMode}:</td>
		<td>" . Field_checkbox("ViralatorMode",1,$ci->main_array["CONF"]["ViralatorMode"],"EnableDisableViralatorMode()")."</td>
		<td>" . help_icon('{ViralatorMode_text}')."</td>
	</tr>	
	<tr>
		<td class=legend>{VirSaveDir}:</td>
		<td>" . Field_text('VirSaveDir',$ci->main_array["CONF"]["VirSaveDir"],'width:290px;font-size:13px;padding:3px')."&nbsp;</td>
		<td>" . help_icon('{VirSaveDir_text}')."</td>
	</tr>		
	<tr>
		<td class=legend style='$color'>{VirHTTPServer}:</td>
		<td>" . Field_text('VirHTTPServer',$ci->main_array["CONF"]["VirHTTPServer"],'width:290px;font-size:13px;padding:3px')."&nbsp;</td>
		<td>" . help_icon('{VirHTTPServer_text}')."</td>
	</tr>
	<tr>	
		<td class=legend>{example}:</td>
		<td colspan=2><strong><a href='https://{$_SERVER['SERVER_NAME']}/exec.cicap.php?usename=%f&remove=1&file='>https://{$_SERVER['SERVER_NAME']}/exec.cicap.php?usename=%f&remove=1&file=</a></strong></td>
	</tr>	
			
	

	<tr>
		<td colspan=3 align='right'>
		<hr>
			". button("{apply}","SaveIcapDaemonSet()")."
		</td>
	</tr>
	</table>
	</div>
	
	<script>
var x_SaveIcapDaemonSet=function(obj){
     var tempvalue=obj.responseText;
	  if(tempvalue.length>3){alert(tempvalue);}
	  RefreshTab('main_config_cicap');
	
	}	
	
	function SaveIcapDaemonSet(){
		var XHR = new XHRConnection();
	    XHR.appendData('Timeout',document.getElementById('Timeout').value);
	    XHR.appendData('MaxKeepAliveRequests',document.getElementById('MaxKeepAliveRequests').value);
	    XHR.appendData('KeepAliveTimeout',document.getElementById('KeepAliveTimeout').value);
	    
	    XHR.appendData('MaxServers',document.getElementById('MaxServers').value);
	    XHR.appendData('MinSpareThreads',document.getElementById('MinSpareThreads').value);
	    XHR.appendData('ThreadsPerChild',document.getElementById('ThreadsPerChild').value);
	    XHR.appendData('MaxRequestsPerChild',document.getElementById('MaxRequestsPerChild').value);
	    XHR.appendData('VirSaveDir',document.getElementById('VirSaveDir').value);
	    XHR.appendData('VirHTTPServer',document.getElementById('VirHTTPServer').value);
	    XHR.appendData('DebugLevel',document.getElementById('DebugLevel').value);
	    if(document.getElementById('ViralatorMode').checked){XHR.appendData('ViralatorMode',1);}else{XHR.appendData('ViralatorMode',0);}
		
	    if(document.getElementById('EnableSquidGuardInCiCAP').checked){
	    	XHR.appendData('EnableSquidGuardInCiCAP',1);
		}else{
			XHR.appendData('EnableSquidGuardInCiCAP',0);
		}		
		
		
	    document.getElementById('ffmcc1').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>'; 
       	XHR.sendAndLoad('$page', 'GET',x_SaveIcapDaemonSet);
	}
	
	function EnableDisableViralatorMode(){
		 document.getElementById('VirSaveDir').disabled=true;
	     document.getElementById('VirHTTPServer').disabled=true;
	     if(document.getElementById('EnableClamavInCiCapCheck').value==0){return;}
	     
	     
	     if(document.getElementById('ViralatorMode').checked){
	      document.getElementById('VirSaveDir').disabled=false;
	      document.getElementById('VirHTTPServer').disabled=false;
		 }
	
	}
	
	function DisableSquidGuardCheckCicap(){
	 	if(document.getElementById('EnableSquidGuardInCiCAP')){
	 		document.getElementById('EnableSquidGuardInCiCAP').checked=false;
	 		document.getElementById('EnableSquidGuardInCiCAP').disabled=true;
		}
	}
	
	EnableDisableViralatorMode();
	$DisableSquidGuardCheckCicap
	
</script>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function save_settings(){
	$sock=new sockets();
	if(isset($_GET["EnableClamavInCiCap"])){
		$ci=new cicap();
		if($ci->EnableClamavInCiCap<>$_GET["EnableClamavInCiCap"]){
			$sock->SET_INFO("EnableClamavInCiCap",$_GET["EnableClamavInCiCap"]);
			$sock->getFrameWork("cmd.php?squid-reconfigure=yes");
		}
	}
	if(isset($_GET["EnableSquidGuardInCiCAP"])){
		if($sock->GET_INFO("EnableSquidGuardInCiCAP")<>$_GET["EnableSquidGuardInCiCAP"]){
				$sock->SET_INFO("EnableSquidGuardInCiCAP",$_GET["EnableSquidGuardInCiCAP"]);
				$sock->getFrameWork("cmd.php?squid-reconfigure=yes");
		}
	}
	
	
	$ci=new cicap();
	while (list ($num, $line) = each ($_GET)){	
		if(preg_match('#^srv_clamav_(.+)#',$num,$re)){
			$num="srv_clamav.{$re[1]}";
		}
		
		writelogs("Save $num => $line",__FUNCTION__,__FILE__,__LINE__);
		$ci->main_array["CONF"][$num]=$line;
	}
	
	$tpl=new templates();
	$ci->Save();
	
}

//
	
?>	