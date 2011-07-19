<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.os.system.inc');
	include_once('ressources/class.computers.inc');
	include_once('ressources/class.ocs.inc');
	
	

	
	$user=new usersMenus();
	if(($user->AsSystemAdministrator==false) OR ($user->AsSambaAdministrator==false)) {
		$tpl=new templates();
		$text=$tpl->_ENGINE_parse_body('{ERROR_NO_PRIVS}');
		$text=replace_accents(html_entity_decode($text));
		echo "alert('$text');";
		exit;
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	
	
	if(isset($_GET["ocsweb-status"])){echo status();exit;}
	if(isset($_GET["popup-status"])){popup_status();exit;}
	if(isset($_GET["ocs-resolve"])){popup_status_resolve();exit;}
	
	if(isset($_GET["params-web"])){params_web();exit;}
	if(isset($_GET["ocswebservername"])){params_web_save();exit;}
	
	// certificate 
	if(isset($_GET["OCSCertServerName"])){popup_certificate_save();exit;}
	
	
	if(isset($_GET["deploy-js"])){agent_deploy_js();exit;}
	if(isset($_GET["deploy-popup"])){agent_deploy_popup();exit;}
	if(isset($_GET["agent_deploy_add_task"])){agent_deploy_add_task();exit;}
	if(isset($_GET["connected"])){popup_connected();exit;}
	if(isset($_GET["connected-search"])){popup_connected_search();exit;}
	if(isset($_GET["connected-mac"])){popup_connected_add();exit;}
	if(isset($_GET["certificate"])){popup_certificate();exit;}
	
	if(isset($_GET["ocs-csr-infos"])){popup_certificate_csr();exit;}
	if(isset($_GET["certificate-upload"])){popup_certificate_upload();exit;}
	if(isset($_GET["CertToUploadOCS"])){popup_certificate_upload_save();exit;}
	if(isset($_GET["SelfSigned"])){SaveSelfSigned();exit;}
	if(isset($_GET["getpki"])){getpki();exit;}
	
	//packages
	
	if(isset($_GET["packages"])){popup_packages();exit;}
	if(isset($_GET["packages-list"])){popup_packages_list();exit;}
	if(isset($_GET["DELETE-FILEID"])){popup_packages_delete();exit;}
	
	
	//networks
	if(isset($_GET["networks"])){popup_network();exit;}
	if(isset($_GET["PROLOG_FREQ"])){popup_network_save();exit;}
	if(isset($_GET["agent"])){popup_agent_win();exit;}
	
	if(isset($_GET["OCSImportToLdap"])){saveothers();exit;}
	
	js();
	
	
function agent_deploy_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	
	$prefix=str_replace(".","_",$page);
	$uid=$_GET["deploy-js"];
	$title=$tpl->_ENGINE_parse_body("$uid::{OCS_DEPLOY_WINDOWS}","domains.edit.user.php");
	$html="
	
	function {$prefix}LoadMain(){
		YahooWin4('550','$page?deploy-popup=$uid','$title');
		
	}
	
var x_agent_deploy_add_task=function (obj) {
	var results=obj.responseText;
	if (results.length>0){alert(results);}
	Loadjs('computer.install.php?uid=$uid');
	YahooWin4Hide();
}	
	
	function agent_deploy_add_task(){
		var id_files=document.getElementById('id_files').value;
		if(id_files.length==0){return;}
		var XHR = new XHRConnection();
		XHR.appendData('agent_deploy_add_task',id_files);
		XHR.appendData('uid','$uid');
		document.getElementById('ocs-deploy-div').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_agent_deploy_add_task);
		
	}
	
	

	{$prefix}LoadMain();";
	
	echo $html;
		
	
}
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{APP_OCSI}');
	$prefix=str_replace(".","_",$page);
	$start="{$prefix}LoadMain()";
	if(isset($_GET["in-front-ajax"])){
		$start="{$prefix}LoadMainAjax()";
	}
	
	$html="
	
	function {$prefix}LoadMain(){
		LoadWinORG('730','$page?popup=yes','$title');
		}	
		
function {$prefix}LoadMainAjax(){
	$('#BodyContent').load('$page?popup=yes');
	}		
		
	function ocs_web_params(){
		YahooWin2('500','$page?params-web=yes');
	}
	
	function EditRemoteSoftware(id){
		YahooWin2('500','$page?popup-edit-software='+id);
	}
	
	function RefreshSoftwaresList(){
		LoadAjax('software_list','$page?sflist=yes');
	}
	
	
var x_DelRemoteSoftware=function (obj) {
	var results=obj.responseText;
	if (results.length>0){alert(results);}
	RefreshSoftwaresList();
}

function DelRemoteSoftware(id){
		var XHR = new XHRConnection();
		XHR.appendData('DelRemoteSoftware',id);
		document.getElementById('software_list').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_DelRemoteSoftware);
}

var x_SavePackegInfos=function (obj) {
	var results=obj.responseText;
	if (results.length>0){alert(results);}
	RefreshSoftwaresList();
	YahooWin2Hide();
}

function SavePackegInfos(id){
		var XHR = new XHRConnection();
		XHR.appendData('SavePackegInfos',id);
		XHR.appendData('description',document.getElementById('txtDescription').value);
		XHR.appendData('commandline',document.getElementById('commandline').value);
		XHR.appendData('ExecuteAfter',document.getElementById('ExecuteAfter').value);
		XHR.appendData('MinutesToWait',document.getElementById('MinutesToWait').value);
		document.getElementById('packageinfo').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_SavePackegInfos);
}

var x_SaveWebServerName=function (obj) {
	var results=obj.responseText;
	if (results.length>0){alert(results);}
	RefreshTab('main_config_ocsi');
	
}



function SaveWebServerName(){
		var XHR = new XHRConnection();
		XHR.appendData('ocswebservername',document.getElementById('ocswebservername').value);
		XHR.appendData('OCSWebPort',document.getElementById('OCSWebPort').value);
		XHR.appendData('OCSWebPortSSL',document.getElementById('OCSWebPortSSL').value);
		document.getElementById('ocswebservername_div').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_SaveWebServerName);
}

function SaveOthersParameters(){
		var XHR = new XHRConnection();
		XHR.appendData('OCSImportToLdap',document.getElementById('OCSImportToLdap').value);
		document.getElementById('ocswebservername_div').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_SaveWebServerName);
}
	
$start;
	
";

echo $html;
}	


function popup(){
	$page=CurrentPageName();
	$sock=new sockets();
	
	$UseFusionInventoryAgents=$sock->GET_INFO("UseFusionInventoryAgents");
	if($UseFusionInventoryAgents==null){$UseFusionInventoryAgents=1;}	
	
	$array["popup-status"]='{status}';
	$array["params-web"]='{ocs_web_params}';
	$array["connected"]='{computers}';
	$array["packages"]='{packages}';
	
	if($UseFusionInventoryAgents==0){
		$array["certificate"]='{certificate}';
		$array["networks"]='{networks}';
		$array["agent"]='{OCS_AGENT}';
	}
	//$array["popup-bandwith"]='{bandwith}';

	$tpl=new templates();

	while (list ($num, $ligne) = each ($array) ){
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_ocsi style='width:100%;height:500px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_ocsi').tabs({
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

function popup_status_resolve(){
	
	$ocs=new ocs();
	$server=$ocs->SSL_SERVER_NAME();
	$ip=gethostbyname($server);
	if($server==$ip){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body(Paragraphe("warning64.png","$server","{OCS_COULD_NOT_RESOLVE_SSLHOST}",null,null,340));
	}
	
	
}



function popup_status(){
	$page=CurrentPageName();
	$users=new usersMenus();
	if(!$users->OCSI_INSTALLED){
		$notinstalled=Paragraphe("64-ocs.png","{APP_OCSI}: {feature_not_installed}","{ERROR_NOT_INSTALLED_REDIRECT}",
		"javascript:Loadjs('setup.index.progress.php?product=APP_OCSI&start-install=yes');");
		
		$html="<H1>{APP_OCSI}</H1>
		<center>$notinstalled</center>
		";
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body($html);	
		exit;
		
	}
	
	
		
	$html="
	<img src='img/ocs-banner.gif' style='margin-top: -3px;margin-left:-10px'>
	
	<table style='width:100%'>
		<tr>
			<td valign='top'><p  style='font-size:14px'>{APP_OCSI_TEXT}</p><div id='ocs-resolve'></div></td>
			<td valign='top'><div id='ocsweb-status'></div></td>
		</tr>
	</table>
	
	<script>
		LoadAjax('ocsweb-status','$page?ocsweb-status=yes');
	</script>";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
}

function params_web_save(){
	$sock=new sockets();
	$sock->SET_INFO("ocswebservername",$_GET["ocswebservername"]);
	$sock->SET_INFO("OCSWebPort",$_GET["OCSWebPort"]);
	$sock->SET_INFO("OCSWebPortSSL",$_GET["OCSWebPortSSL"]);
	$ocs=new ocs();
	$ocs->PACKAGE_UPDATE_ALL_RESSOURCES();
	$sock->getFrameWork("cmd.php?ocsweb-restart=yes");
}

function saveothers(){
	$sock=new sockets();
	if($_GET["OCSImportToLdap"]<10){$_GET["OCSImportToLdap"]=10;}
	$sock->SET_INFO("OCSImportToLdap",$_GET["OCSImportToLdap"]);
}

function params_web(){
	
	$sock=new sockets();
	$users=new usersMenus();
	$ocswebservername=$sock->GET_INFO("ocswebservername");
	$OCSImportToLdap=$sock->GET_INFO("OCSImportToLdap");
	$UseFusionInventoryAgents=$sock->GET_INFO("UseFusionInventoryAgents");
	if($UseFusionInventoryAgents==null){$UseFusionInventoryAgents=1;}
	if($OCSImportToLdap==null){$OCSImportToLdap=60;}

	
	$OCSWebPort=$sock->GET_INFO("OCSWebPort");
	$OCSWebPortSSL=$sock->GET_INFO("OCSWebPortSSL");
	if($OCSWebPort==null){$OCSWebPort=9080;}
	if($OCSWebPortSSL==null){$OCSWebPortSSL=$OCSWebPort+50;}
	if($ocswebservername==null){$ocswebservername=$users->hostname;}
	if($UseFusionInventoryAgents==1){
		$http_suffix="https";
		$OCSWebPortLink=$OCSWebPortSSL;
		}else{
			$http_suffix="http";
			$OCSWebPortLink=$OCSWebPort;
		}
	

	$www=texttooltip("{website}:$http_suffix://$ocswebservername:$OCSWebPortLink/ocsreports","{website}",
	"javascript:s_PopUpFull('http://$ocswebservername:$OCSWebPortLink/ocsreports',800,600)",null,0,"font-size:14px;font-weight:bold;color:blue",1);
		
$html="<div id='ocswebservername_div'>

	<table style='width:100%'>
	<tr>
	<td valign='top' width=1%>
		<img src='img/web-64.png'>
	</td>
	<td valign='top'>
			<div style='font-size:14px'>{ocs_web_params_text}</div>
			<table style='width:100%'>
			<tr>
				<td colspan=2><hr><div>$www</div><div style='font-size:11px;text-align:right'>{username}:admin&nbsp;{password}:admin<hr></td>
			</tr>
				<tr>
					<td valign='top' class=legend>{servername}:</td>
					<td valign='top'>". Field_text("ocswebservername",$ocswebservername,"width:180px;font-size:13px;padding:3px")."</td>
					<td width=1%>". help_icon("{OCSI_SERVERNAME_EXPLAIN}")."</td>
				</tr>
				<tr>
					<td valign='top' class=legend>{UseFusionInventoryAgents}:</td>
					<td valign='top'>". Field_checkbox("UseFusionInventoryAgents",1,$UseFusionInventoryAgents,"UseFusionInventoryAgentsCheck()")."</td>
					<td width=1%>". help_icon("{UseFusionInventoryAgents_text}")."</td>
				</tr>				
				<tr>
					<td valign='top' class=legend>{listen_port}:</td>
					<td valign='top'>". Field_text("OCSWebPort",$OCSWebPort,"width:48px;font-size:13px;padding:3px")."</td>
					<td width=1%>". help_icon("{OCSI_NORMAL_PORT_EXPLAIN}")."</td>
				</tr>
				<tr>
					<td valign='top' class=legend>{listen_ssl_port}:</td>
					<td valign='top'>". Field_text("OCSWebPortSSL",$OCSWebPortSSL,"width:48px;font-size:13px;padding:3px")."</td>
					<td width=1%>". help_icon("{OCSI_SSL_PORT_EXPLAIN}")."</td>
				</tr>
											

				<tr><td colspan=2 align='right'><hr>". button("{apply}","SaveWebServerName()")."</tr>
				<tr><td colspan=2 align='right'>&nbsp;</tr>
				<tr>
					<td valign='top' class=legend>{automatic_computers_injection}:</td>
					<td valign='top'>". Field_text("OCSImportToLdap",$OCSImportToLdap,"width:48px;font-size:13px;padding:3px")."&nbsp;{minutes}</td>
					<td width=1%>". help_icon("{automatic_computers_injection_explain}")."</td>
				</tr>	
				<tr><td colspan=2 align='right'><hr>". button("{apply}","SaveOthersParameters()")."</tr>
				
			</table>
	</td>
	</tr>
	</table>
	</div>
	
	<script>
		function UseFusionInventoryAgentsCheck(){
			document.getElementById('OCSWebPort').disabled=true;
			if(!document.getElementById('UseFusionInventoryAgents').checked){
				document.getElementById('OCSWebPort').disabled=false;
			}
			
		}
		UseFusionInventoryAgentsCheck();
	</script>
	
	";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
	
}

function agent_deploy_popup(){
	
	$q=new mysql();
	$sql="SELECT id_files,filename FROM files_storage WHERE OCS_PACKAGE=1";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$array[$ligne["id_files"]]=$ligne["filename"];
	}
	$array[null]="{select}";
	
	$package=Field_array_Hash($array,"id_files");
	
	$html="
	<H1>{OCS_DEPLOY_WINDOWS}</H1>
	<p class=caption>{OCS_DEPLOY_WINDOWS_TEXT}</p>
	<table style='width:100%' class=table_form>
	<tr>
		<td valign='top' class=legend nowrap>{filename}:</td>
		<td>$package</td>
	</tr>
	<tr>
		<td colspan=2 align='right'><hr><input type='button' OnClick=\"javascript:agent_deploy_add_task();\" value='{deploy}&nbsp;&raquo;'></td>
	</tr>
	</table>
	<div id='ocs-deploy-div'></div>;
	";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html,'domains.edit.user.php');	
	
}


function status(){
	$page=CurrentPageName();
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString(base64_decode($sock->getFrameWork('cmd.php?ocsweb-status=yes')));
	$status=DAEMON_STATUS_ROUND("APP_OCSI",$ini);
	$status1=DAEMON_STATUS_ROUND("APP_OCSI_DOWNLOAD",$ini);
	$tpl=new templates();
	
	$html="$status<br>$status1
	
	
	<script>
		LoadAjax('ocs-resolve','$page?ocs-resolve=yes');
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);		
	
}


function agent_deploy_add_task(){
	$file_id=$_GET["agent_deploy_add_task"];
	$uid=$_GET["uid"];
	$commandline=null;
	$debug_mode=1;
	
	$sql_insert="INSERT INTO deploy_tasks (files_id,computer_id,commandline,username,password,debug_mode)
	VALUES('$file_id','$uid','$commandline','$username','$password','$debug_mode');
	";
	$q=new mysql();
	$q->QUERY_SQL($sql_insert,"artica_backup");
	if(!$q->ok){
		echo $q->mysql_error;
	}
	
	$sock=new sockets();
	$sock->getFrameWork('cmd.php?LaunchRemoteInstall=yes');
	
}

function popup_connected(){
	$page=CurrentPageName();
	$html="
	<div style='font-size:14px'>{OCS_CONNECTED_COMPUTERS_TEXT}</div>
	<div id='ocs-computers' style='width:99%;height:350px;overflow:auto;border:1px solid #CCCCCC;margin-top:8px'></div>
	
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:13px'>{search}:</td>
		<td>". Field_text("searchocs",null,"font-size:13px;padding:3px",null,null,null,false,"OCSSearchPCCheck(event)")."</td>
	</tr>
	</table>
	
	<script>
		function OCSSearchPCCheck(e){
			if(checkEnter(e)){OCSSearchPCs();}
		}
	
		function OCSSearchPCs(){
			LoadAjax('ocs-computers','$page?connected-search='+document.getElementById('searchocs').value);
		}
		

	var x_AddComputerFromOCS=function (obj) {
		var results=obj.responseText;
		if (results.length>0){alert(results);}
		OCSSearchPCs();
	
	}



	function AddComputerFromOCS(mac){
			var XHR = new XHRConnection();
			XHR.appendData('connected-mac',mac);
			document.getElementById('ocs-computers').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_AddComputerFromOCS);
		}			
				
		
		OCSSearchPCs();
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function popup_connected_search(){
	$ocs=new ocs();
	$sql=$ocs->COMPUTER_SEARCH_QUERY($_GET["connected-search"]);
	$CONFIG=$ocs->GET_SERVER_SETTINGS();
	$PROLOG_FREQ=$CONFIG["PROLOG_FREQ"]*60;	
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"ocsweb");
	if(!$q->ok){
		echo "<p>&nbsp;</p><p style='font-size:15px'>$q->mysql_error<hr>$sql</p>";
		return;
	}	
	
	
	$html="
	<table style='width:100%;'>
	<tr>
		<th colspan=2>{computer}</th>
		<th>{status}</th>
		<th>{ComputerMacAddress}</th>
		<th>{ip_address}</th>
	</tr>";
	

	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($ligne["IPADDRESS"]=="0.0.0.0"){continue;}
		if($ligne["MACADDR"]=="00:00:00:00:00:00"){continue;}
		if($already[$ligne["MACADDR"]]){continue;}
		if($already[$ligne["NAME"].$ligne["IPSRC"]]){continue;}
		$status=null;
		
		$already[$ligne["MACADDR"]]=true;
		$already[$ligne["NAME"].$ligne["IPADDRESS"]]=true;
		
		$f=new computers();
		$uid=$f->ComputerIDFromMAC($ligne["MACADDR"]);
		if(trim($uid)<>null){
			$already[$ligne["NAME"].$ligne["IPSRC"]]=true;
		}
		
		
		
		$js=MEMBER_JS($uid,1,1);
		
		$last=distanceOfTimeInWords(strtotime($ligne["LASTCOME"]),time());
		$mins=distanceMinStrings($ligne["LASTCOME"]);
		$js_text="{$ligne["NAME"]}<hr>{last_com}:$last<hr>{$ligne["IPADDRESS"]}";
		
		if($mins>$PROLOG_FREQ){
			$status=imgtootltip('status_service_removed.png',$last);
			$js_text="{$ligne["NAME"]}<hr><span color:red>{last_com}:$last</span>";
		}else{
			$status=imgtootltip('status_service_run.png',$last);
		}		
		
		if($uid==null){
			$js=null;
			$status=imgtootltip("status_warning.gif","{ocs_computer_is_not_in_ldap}","AddComputerFromOCS('{$ligne["MACADDR"]}')",null,md5($ligne["MACADDR"]).time());
			$js_text="{ocs_computer_is_not_in_ldap}";
		}else{
			
		}
		
		if(trim($ligne["IPADDRESS"])<>null){
		if(trim($ligne["IPSRC"])<>trim($ligne["IPADDRESS"])){
			$ligne["IPSRC"]=$ligne["IPSRC"]."/".$ligne["IPADDRESS"];
		}}
		
		
	$html=$html."
		<tr ". CellRollOver($js,$js_text).">
			<td width=1%><img src='img/laptop-32.png'></td>
			<td style='font-size:13px'>{$ligne["NAME"]}</td>
			<td width=1% align='center' valign='middle'>$status</td>
			<td style='font-size:13px'>{$ligne["MACADDR"]}</td>
			<td style='font-size:12px'>{$ligne["IPSRC"]}</td>
		</tr>
		
		";
	}	
	
	$html=$html."</table>
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function popup_certificate(){
	$sock=new sockets();
	$page=CurrentPageName();
	$tpl=new templates();
	$upload_server_certificate=$tpl->_ENGINE_parse_body("{upload_server_certificate}");
	$OCSCertInfos=unserialize(base64_decode($sock->GET_INFO("OCSCertInfos")));
	if($OCSCertInfos["OCSCertServerName"]==null){
		$users=new usersMenus();
		$OCSCertInfos["OCSCertServerName"]=$users->hostname;
	}
	
	$CertificateMaxDays=$sock->GET_INFO('CertificateMaxDays');
	if($CertificateMaxDays==null){$CertificateMaxDays=730;}	
	$UseSelfSignedCertificate=$sock->GET_INFO("UseSelfSignedCertificate");
	if($UseSelfSignedCertificate==null){$UseSelfSignedCertificate=1;}
	$EXPLAIN="{ocs_certificate_explain_self}";
	if($UseSelfSignedCertificate==0){
		$start="LoadOcsCSrInfos()";
		$upload=Paragraphe("certificate-upload-64.png","{upload_server_certificate}","{upload_server_certificate_text_ocs}","javascript:OcsWebCertificateUpload()");
		$EXPLAIN="{ocs_certificate_explain}";
	}
	
	$datas=$sock->GET_INFO("OCSServerDotCrt");
	$datas=str_replace("\n\n","\n",$datas);	
	if(strlen($datas)>50){
		$download_cert="<br>".Paragraphe("certificate-download-64.png","{download_certificate}","{ocs_download_certificate_text}",
		"$page?getpki=yes");
	}
	
	if(preg_match("#^(.+?)\.#",$OCSCertInfos["OCSCertServerName"],$re)){$OCSCertInfos["OCSCertServerName"]=$re[1];}
	
	
	
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top' width=1%>
			<center><img src='img/certificate-128.png' id='certificate-img' align='center'></center>
			<br><br>$upload
			$download_cert</td>
		<td valign='top'>
			<div style='font-size:14px'>$EXPLAIN</div>
			<table style='width:100%'>
			<tr>
				<td class=legend style='font-size:13px'>{use_self_signed_certificate}:</td>
				<td>". Field_checkbox("UseSelfSignedCertificate",1,$UseSelfSignedCertificate,"SwitchUseSelfSignedCertificate()")."</td>
			</tr>			
			<tr>
				<td class=legend style='font-size:13px'>{servername}:</td>
				<td>". Field_text("OCSCertServerName",$OCSCertInfos["OCSCertServerName"],"font-size:13px;padding:3px")."</td>
			</tr>
			<tr>
				<td class=legend style='font-size:13px'>{domain}:</td>
				<td>". Field_text("OCSCertDomainName",$OCSCertInfos["OCSCertDomainName"],"font-size:13px;padding:3px")."</td>
			</tr>
			<tr>
				<td class=legend style='font-size:13px'>{email}:</td>
				<td>". Field_text("OCSCertEmail",$OCSCertInfos["OCSCertEmail"],"font-size:13px;padding:3px")."</td>
			</tr>
			<tr>
				<td class=legend style='font-size:13px'>{CertificateMaxDays}:</td>
				<td><strong style='font-size:13px'>$CertificateMaxDays</strong> {days}</td>
			</tr>
			<tr>
				<td colspan=2 align='right'><hr>". button("{generate_certificate}","OCSGenerateCSR()")."</td>
			</tr>
		</td>
	</tr>
	</table>
	
	<div id='ocs-csr-infos' style='width:100%'></div>
	
	<script>
		var x_OCSGenerateCSR=function (obj) {
			var results=obj.responseText;
			if (results.length>0){alert(results);}
			RefreshTab('main_config_ocsi');
			
		}
		
		function OCSGenerateCSR(){
				var XHR = new XHRConnection();
				XHR.appendData('OCSCertServerName',document.getElementById('OCSCertServerName').value);
				XHR.appendData('OCSCertDomainName',document.getElementById('OCSCertDomainName').value);
				XHR.appendData('OCSCertEmail',document.getElementById('OCSCertEmail').value);
				XHR.appendData('UseSelfSignedCertificate',document.getElementById('UseSelfSignedCertificate').value);
				document.getElementById('certificate-img').src='img/wait_verybig.gif';
				XHR.sendAndLoad('$page', 'GET',x_OCSGenerateCSR);
		}
		
		function OcsWebCertificateUpload(){
			YahooWin5('550','$page?certificate-upload=yes','$upload_server_certificate');
		}
		
		function SwitchUseSelfSignedCertificate(){
			var XHR = new XHRConnection();
			if(document.getElementById('UseSelfSignedCertificate').checked){XHR.appendData('SelfSigned',1);}else{XHR.appendData('SelfSigned',0);}
			XHR.sendAndLoad('$page', 'GET',x_OCSGenerateCSR);
		}

		function LoadOcsCSrInfos(){
			LoadAjax('ocs-csr-infos','$page?ocs-csr-infos=yes');
		}
		$start;
	
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);	
}

function SaveSelfSigned(){
	$sock=new sockets();
	$sock->SET_INFO("UseSelfSignedCertificate",$_GET["SelfSigned"]);
}

function popup_certificate_save(){
	$sock=new sockets();
	$sock->SET_INFO("UseSelfSignedCertificate",$_GET["UseSelfSignedCertificate"]);
	$sock->SaveConfigFile(base64_encode(serialize($_GET)),"OCSCertInfos");
	echo trim(base64_decode($sock->getFrameWork("cmd.php?ocs-generate-certificate=yes")));
	
}

function popup_certificate_csr(){
	$sock=new sockets();
	$datas=base64_decode($sock->getFrameWork("cmd.php?ocs-get-csr=yes"));	
	if($datas==null){return null;}
	
	$html="
	<div style='width:100%;font-size:14px;margin:8px'>{OCS_CSR_HOWTO}</div>
	<textarea style='font-size:14x;width:100%;height:184px;overflow:auto'>$datas</textarea>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function popup_certificate_upload(){
	$sock=new sockets();
	$page=CurrentPageName();
	$tpl=new templates();
	$datas=base64_decode($sock->getFrameWork("cmd.php?ocs-get-csr=yes"));	
	if($datas==null){
		echo $tpl->_ENGINE_parse_body("<H2>{OCS_MUST_CREATE_CERTIFICATE_CSR_FIRST}</H2>");
		return;
		
	}
	
	$datas=$sock->GET_INFO("OCSServerDotCrt");
	$datas=str_replace("\n\n","\n",$datas);
	$html="
	<div id='certifUploadForm'>
	<div style='font-size:14px;margin:5px'>{upload_server_certificate_text_ocs}</div>
	<tr>
			<td style='font-size:13px'>{file_path}:</td>
			<td>". Field_text("CertToUploadOCS","",'width:65%;font-size:13px;padding:3px'). " </td>
			<td width=1%><input type='button' value='{browse}&nbsp;&raquo;' OnClick=\"javascript:Loadjs('tree.php?select-file=cert&target-form=CertToUploadOCS');\"></td>
		</tr>
	</table>
	
	
	
	<div style='text-align:right'><hr>". button("{upload_certificate}","UploadOCSCert()")."</div>
	<br>
	</div>
	<div style='font-size:14x;width:100%;height:184px;overflow:auto' id='certificatePasted'><code>". nl2br($datas)."</code></div>
	
	
	<script>
		var x_certifUploadForm=function (obj) {
			var results=obj.responseText;
			if (results.length>0){alert(results);}
			YahooWin5Hide();
			RefreshTab('main_config_ocsi');
			
		}
		
		function UploadOCSCert(){
				var XHR = new XHRConnection();
				XHR.appendData('CertToUploadOCS',document.getElementById('certificatePasted').value);
				document.getElementById('certifUploadForm').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
				XHR.sendAndLoad('$page', 'GET',x_certifUploadForm);
		}
	
	</script>";
	echo $tpl->_ENGINE_parse_body($html);
	
}

function popup_certificate_upload_save(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?ocs-generate-final-certificate=yes&path=".base64_encode($_GET["CertToUploadOCS"]));
	
}


function popup_connected_add(){
	$ocs=new ocs();
	$ocs->INJECT_COMPUTER_TOLDAP($_GET["connected-mac"]);
	
	
}

function getpki(){
	$path="/etc/ocs/cert/cacert.pem";
	$file=basename("$path");
	$sock=new sockets();
	$datas=base64_decode($sock->getFrameWork("cmd.php?file-content=".base64_encode($path)));
	$content_type=base64_decode($sock->getFrameWork("cmd.php?mime-type=".base64_encode($path)));
	header('Content-Type: '.$content_type);
	header("Content-Disposition: attachment; filename=\"$file\"");	
	echo $datas;	
	
	
}

function popup_packages(){
	$page=CurrentPageName();
	$html="<table style='width:100%'>
	<tr>
		<td valign='top' width=1%>". imgtootltip("package-add-128.png","{add_new_package}","Loadjs('ocs.packages.add.php')")."
		
		</td>
		<td valign='top'>
			<div style='font-size:13px;margin:3px'>{OCS_PACKAGE_INFOS}</div>
			<div id='packages-list' style='widh:100%;height:350px;overflow:auto'></div>
		</td>
	</tr>
	</table>	
	<script>
		function RefreshOCSPackageList(){
			LoadAjax('packages-list','$page?packages-list=yes');
		}
		
		RefreshOCSPackageList();
	
	</script>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}






function popup_packages_list(){
	$page=CurrentPageName();
	$ocs=new ocs();
	$document_root="/var/lib/ocsinventory-reports";
	$sql="SELECT * FROM download_available ORDER BY FILEID DESC";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"ocsweb");
	if(!$q->ok){
		echo "<p>&nbsp;</p><p style='font-size:15px'>$q->mysql_error<hr>$sql</p>";
		return;
	}
	
	$html="<table style='width:100%'>
	<tr>
		<th colspan=2>{package}</th>
		<th>{size}</th>
		<th>{action}</th>
		<th>{system_os_mini}</th>
		<th>{affected}</th>
		<th>&nbsp;</th>
		<th>&nbsp;</th>
	</tr>
		
		";
	
	$sock=new sockets();
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$FILEID=$ligne["FILEID"];
		$computersnb=$ocs->PACKAGE_AFFECT_COUNT_COMPUTERS($FILEID);
		$NAME=$ligne["NAME"];
		$SIZE=FormatBytes($ligne["SIZE"]/1024);
		$date=date("l d F H:i (Y) ",$FILEID);
		
		$infos=unserialize(base64_decode($sock->getFrameWork("cmd.php?ocs-package-infos=$FILEID")));
		$ACT=$infos["ACT"];
		$affected="-";
		$HASH=$ocs->PACKAGE_DOWNLOAD_INFOS_HASH($FILEID);
		$text="<li>{created}:&nbsp;$date</li><li>{path}:&nbsp;$document_root/$FILEID</li><li>https://{$HASH["INFO_LOC"]}/</li>";
		$text=$text."<li>http://{$HASH["PACK_LOC"]}/</ki>";
		
		
		if($computersnb>0){
			$affected=texttooltip("$computersnb","{display_affected_computers}","Loadjs('ocs.packages.affect.php?FILEID=$FILEID')",0,0,"font-size:13px;font-weight:bolder");
		}
		
		$affect=imgtootltip("package-affect-32.png","{ocs_affect_packages_computers}","Loadjs('ocs.packages.affect.php?FILEID=$FILEID')");
		
		
		
		$html=$html."
		<tr ". CellRollOver().">
			<td width=1%><img src='img/package-32.png'></td>
			<td><strong style='font-size:13px' " . CellRollOver(null,$text).">$NAME</td></td>
			<td><strong style='font-size:13px' align='center'>$SIZE</td></td>
			<td><strong style='font-size:13px' align='center'>{{$ACT}}</td></td>
			<td><strong style='font-size:13px' align='center'>{$ligne["OSNAME"]}</strong></td>
			<td width=1% align='center'>$affected</td>
			<td width=1% align='center'>$affect</td>
			<td>". imgtootltip("package-delete-32.png","{delete}","OCSDeletePackage('$FILEID')")."</td>
		</tr>
		
		";
		
	}
	
	$html=$html."</table>
	
	<script>
	
		var x_OCSDeletePackage=function (obj) {
			var results=obj.responseText;
			if (results.length>0){alert(results);}
			RefreshOCSPackageList();
			
		}
	
		function OCSDeletePackage(FILEID){
			var XHR = new XHRConnection();
			XHR.appendData('DELETE-FILEID',FILEID);
			document.getElementById('packages-list').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_OCSDeletePackage);			
		}
	</script>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function popup_packages_delete(){

	$q=new mysql();
	$sql="SELECT ID FROM download_enable WHERE FILEID='{$_GET["DELETE-FILEID"]}'";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'ocsweb'));
	$IVALUE=$ligne["ID"];	
	
	
	$sql="DELETE FROM download_enable WHERE FILEID='{$_GET["DELETE-FILEID"]}'";
	$q->QUERY_SQL($sql,"ocsweb");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sql="DELETE FROM download_available WHERE FILEID='{$_GET["DELETE-FILEID"]}'";
	$q->QUERY_SQL($sql,"ocsweb");
	
	if($IVALUE>0){
		$sql="DELETE FROM devices WHERE IVALUE='$IVALUE' AND NAME='DOWNLOAD'";
		$q->QUERY_SQL($sql,"ocsweb");
	}
	
	$sock=new sockets();
	writelogs("OCS-PACKAGES:: DELETE {$_GET["DELETE-FILEID"]} -> cmd.php?ocs-package-delete&FILEID={$_GET["DELETE-FILEID"]}",__FUNCTION__,__FILE__,__LINE__);
	$sock->getFrameWork("cmd.php?ocs-package-delete?FILEID={$_GET["DELETE-FILEID"]}");	
	
}

function popup_network(){
	$page=CurrentPageName();
	for($i=1;$i<73;$i++){
		if($i<10){$t="0$i";}else{$t=$i;}
		$PROLOG_FREQ[$i]=$t;
	}
	
	$ocs=new ocs();
	$conf=$ocs->GET_SERVER_SETTINGS();
	
	$html="
	<div id='SaveOCSMain'>
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:13px'>{PROLOG_FREQ}</td>
		<td style='font-size:13px'>". Field_array_Hash($PROLOG_FREQ,"PROLOG_FREQ",$conf["PROLOG_FREQ"],null,null,0,"font-size:13px;padding:3px")."&nbsp;{hours}</td>
		<td width=1%>". help_icon("{PROLOG_FREQ_HELP}")."</td>
	</tr>
	<tr>
		<td colspan=3 align='right'><hr>
			". button("{apply}","SaveOCSMain()")."</td>
	</tr>
	</table>
	</div>
	<script>
	
		var x_SaveOCSMain=function (obj) {
			var results=obj.responseText;
			if (results.length>0){alert(results);}
			RefreshTab('main_config_ocsi');
			
		}
		
		function SaveOCSMain(){
				var XHR = new XHRConnection();
				XHR.appendData('PROLOG_FREQ',document.getElementById('PROLOG_FREQ').value);
				document.getElementById('SaveOCSMain').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
				XHR.sendAndLoad('$page', 'GET',x_SaveOCSMain);
		}
	</script>	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}

function popup_network_save(){
	$ocs=new ocs();
	$ocs->SET_SERVER_SETTINGS("PROLOG_FREQ",$_GET["PROLOG_FREQ"]);
	
}

function popup_agent_win(){
	
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->getFrameWork("cmd.php?ocs-agent-zip-packages=yes")));
	$servername=$OCSCertInfos["OCSCertServerName"];
	$domainname=$OCSCertInfos["OCSCertDomainName"];
	$OCSWebPort=$sock->GET_INFO("OCSWebPort");
	$OCSWebPortSSL=$sock->GET_INFO("OCSWebPortSSL");
	if($OCSWebPort==null){$OCSWebPort=9080;}
	if($OCSWebPortSSL==null){$OCSWebPortSSL=$OCSWebPort+50;}
	if($servername==null){$users=new usersMenus();$servername=$users->hostname;}
	
	if($domainname<>null){
		$servername=$servername.".$domainname";
	}
	if(is_array($array)){
		while (list ($num, $ligne) = each ($array) ){
			$size=FormatBytes($ligne);
			$agent=$agent."<li style='font-size:14px'><a href='https://$servername:$OCSWebPortSSL/$num' style='font-size:14px' _target=new>
			<u>$num&nbsp;$size</u><br><span style='font-size:10px'>https://$servername:$OCSWebPortSSL/$num</span></li>";
		}
	}
	
	
	$html="<div style='font-size:12px;margin:5px'>{OCS_AGENT_INTRO}</div>
	
	<H3>{OCS_AGENT_INSTALL_TITLE}</H3>
	<div style='font-size:14px;font-weight:bold;border-top:1px solid black;border-bottom:1px solid black;margin:5px;padding:3px'>{OCS_AGENT_INSTALL_CACERT}</div>

<center style='margin-top:8px'>
	<img src='img/ocs/Agent_setup_file.png'><br><br>
	<strong style='font-size:14px;margin:5px'>{OCS_AGENT_INSTALL_1}<hr><ul>$agent</ul></strong>
</center>

<center style='margin-top:8px'>
	<img src='img/ocs/Agent_setup_2.png'><br><br>
	<strong style='font-size:14px;margin:5px'>{OCS_AGENT_INSTALL_2}</strong>
</center>


<center style='margin-top:8px'>
	<img src='img/ocs/Agent_setup_3.png'><br><br>
	<strong style='font-size:14px;margin:5px'>{OCS_AGENT_INSTALL_3}
		<span color:red>{servername}:$servername, {listen_port}:$OCSWebPort</span>
	</strong>
</center>

<center style='margin-top:8px'>
	<img src='img/ocs/Agent_setup_5.png'><br><br>
	<strong style='font-size:14px;margin:5px'>{OCS_AGENT_INSTALL_5}</strong>
</center>

	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
}


// MACADDR


?>