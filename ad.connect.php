<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	
	$users=new usersMenus();
	if(!$users->AsSystemAdministrator){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["ADSERVER"])){save();exit;}
	if(isset($_GET["net-ads-infos"])){ads_infos();exit;}
	if(isset($_GET["winbindd"])){winbindd();exit;}
	if(isset($_GET["addldap"])){ad_ldap();exit;}
	if(isset($_GET["netadsleave-icon"])){netadsleave_icon();exit;}
	if(isset($_GET["netads-leave-perform"])){netadsleave_perform();exit;}
	if(isset($_GET["EnableManageUsersTroughActiveDirectory"])){saveAdStrict();exit;}
js();

function js(){
	
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{APP_AD_CONNECT}");
	$page=CurrentPageName();
	$html="
	function AdConnectPopup(){
		YahooWin3('650','$page?popup=yes','$title')
	}
	
	AdConnectPopup();";

	echo $html;
	
}

function saveAdStrict(){
	$sock=new sockets();
	$sock->SaveConfigFile(serialize($_GET),"ActiveDirectoryCredentials");
	$sock->SET_INFO("CyrusToAD",$_GET["CyrusToAD2"]);
	$sock->SET_INFO("EnableManageUsersTroughActiveDirectory",$_GET["EnableManageUsersTroughActiveDirectory"]);
	$sock->SET_INFO("DisableSambaFileSharing",$_GET["DisableSambaFileSharing"]);
	$sock->SET_INFO("CyrusToADSyncTime",$_GET["CyrusToADSyncTime"]);
	$sock->getFrameWork("cmd.php?samba-save-config=yes");
	$sock->getFrameWork("cmd.php?saslauthd-restart=yes");
	$sock->getFrameWork("cmd.php?reconfigure-cyrus=yes");
	$sock->getFrameWork("cmd.php?cyrus-sync-to-ad=yes");
	$sock->getFrameWork("services.php?process1=yes");

}

function ad_ldap(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$sock=new sockets();
	$users=new usersMenus();
		$cyrus_imapd_installed=0;
		$EnableManageUsersTroughActiveDirectory=$sock->GET_INFO("EnableManageUsersTroughActiveDirectory");
		$EnableSambaActiveDirectory=$sock->GET_INFO("EnableSambaActiveDirectory");
		$DisableSambaFileSharing=$sock->GET_INFO("DisableSambaFileSharing");
		$CyrusToAD=$sock->GET_INFO("CyrusToAD");
		$CyrusToADSyncTime=$sock->GET_INFO("CyrusToADSyncTime");
		if(!is_numeric($EnableManageUsersTroughActiveDirectory)){$EnableManageUsersTroughActiveDirectory=0;}
		if(!is_numeric($DisableSambaFileSharing)){$DisableSambaFileSharing=0;}
		if(!is_numeric($CyrusToAD)){$CyrusToAD=0;}
		if(!is_numeric($CyrusToADSyncTime)){$CyrusToADSyncTime=10;}
		$ActiveDirectoryCredentials=array();
		$ActiveDirectoryCredentials=unserialize($sock->GET_INFO("ActiveDirectoryCredentials"));
		
		if($EnableSambaActiveDirectory==1){
				$array=unserialize(base64_decode($sock->getFrameWork("cmd.php?net-ads-info=yes")));
				$ActiveDirectoryCredentials["suffix"]=$array["Bind Path"];
				$ActiveDirectoryCredentials["host"]=$array["LDAP server"];
		}					
		
		
		if($ActiveDirectoryCredentials["bind_dn"]==null){
			$ActiveDirectoryCredentials["bind_dn"]="CN=Administrator,CN=Users";
		}
		
		if($users->cyrus_imapd_installed){$cyrus_imapd_installed=1;}
		
		
		$html="
		<div id='StrictADDiv'>
		<table style='width:100%' class=form>
			<tr>
				<td class=legend>{EnableManageUsersTroughActiveDirectory}</td>
				<td>". Field_checkbox('EnableManageUsersTroughActiveDirectory',1,$EnableManageUsersTroughActiveDirectory,"EnableFormCredAd()")."</td>
			</tr>
			<tr>
				<td class=legend>{EnableCyrusImapToAd}</td>
				<td>". Field_checkbox('CyrusToAD2',1,$CyrusToAD,"EnableFormCredAd()")."</td>
			</tr>			
			<tr>
				<td valign='top' class=legend>{TimeSynchronization}:</td>
				<td style='font-size:13px'>". Field_text("CyrusToADSyncTime",$CyrusToADSyncTime,"width:60px;padding:3px;font-size:13px")."Mn</td>
			</tr>			
			
			<tr>
				<td class=legend>{DisableSambaFileSharing}</td>
				<td>". Field_checkbox('DisableSambaFileSharing',1,$DisableSambaFileSharing)."</td>
			</tr>			
			<tr>
				<td valign='top' class=legend>{server_host}:</td>
				<td>". Field_text("host",$ActiveDirectoryCredentials["host"],"width:250px;padding:3px;font-size:13px")."</td>
			</tr>
			<tr>
				<td valign='top' class=legend>{search_base}:</td>
				<td>". Field_text("suffix",$ActiveDirectoryCredentials["suffix"],"width:250px;padding:3px;font-size:13px")."</td>
			</tr>	
			<tr>
				<td valign='top' class=legend>{bind_dn}:</td>
				<td>". Field_text("bind_dn",$ActiveDirectoryCredentials["bind_dn"],"width:250px;padding:3px;font-size:13px")."</td>
			</tr>	
			<tr>
				<td valign='top' class=legend>{password}:</td>
				<td>". Field_password("password",$ActiveDirectoryCredentials["password"],"width:120px;padding:3px;font-size:13px")."</td>
			</tr>
			<tr>
				<td colspan=2 align='right'>". button("{apply}","SaveStrictAd()")."</td>
			</tr>	
	</table>		
	</div>	
		
		
		
	<script>
	
var X_SaveStrictAd= function (obj) {
		var results=trim(obj.responseText);
		if(results.length>0){alert(results);}
		RefreshTab('main_ad_connect_popup');
		CacheOff();
	}		
	
	function SaveStrictAd(){
		var XHR=XHRParseElements('StrictADDiv');
		document.getElementById('StrictADDiv').innerHTML='<center><img src=img/wait_verybig.gif></center>';   
		XHR.sendAndLoad('$page', 'GET',X_SaveStrictAd);
	
	}
	
	
	function EnableFormCredAd(){
		var cyrus_imapd_installed=$cyrus_imapd_installed;
		DisableFieldsFromId('StrictADDiv');
		document.getElementById('EnableManageUsersTroughActiveDirectory').disabled=false;
		if(document.getElementById('EnableManageUsersTroughActiveDirectory').checked){
			EnableFieldsFromId('StrictADDiv');
		}
		
		var EnableSambaActiveDirectory='$EnableSambaActiveDirectory';
		var suffix='{$ActiveDirectoryCredentials["suffix"]}';
		if(EnableSambaActiveDirectory=='1'){
			if(suffix.length>2){
				document.getElementById('host').disabled=true;
				document.getElementById('suffix').disabled=true;

			}
		}
		if(cyrus_imapd_installed==0){
			if(document.getElementById('CyrusToAD')){
				document.getElementById('CyrusToAD').disabled=true;
			}
		}		
	
	}
	

	
	EnableFormCredAd();	
	</script>
	
	
	";
	echo $tpl->_ENGINE_parse_body($html);	
		
}


function popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$array["winbindd"]="{APP_AD_CONNECT}";
	$array["addldap"]="{users_database}";
	
	
	
	while (list ($num, $ligne) = each ($array) ){
		
		
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
		
		//$html=$html . "<li><a href=\"javascript:LoadAjax('main_system_settings','$page?tab=$num&hostname=$hostname')\" $class>$ligne</a></li>\n";
			
		}
	echo "<div id=main_ad_connect_popup style='width:100%;height:500px;overflow:auto;background-color:white;'>
				<ul>". implode("\n",$html)."</ul>
		</div>
		<script>
				$(document).ready(function(){
					$('#main_ad_connect_popup').tabs();
			

			});
		</script>";			
	
	
	
}

function winbindd(){


	$sock=new sockets();
	$COMPATIBLE=false;
	$page=CurrentPageName();
	$tpl=new templates();
	$users=new usersMenus();
	if(!$users->WINBINDD_INSTALLED){
		$html="<center style='margin:10px;font-size:14px'>{WINBINDD_NOT_INSTALLED_TEXT}</center>";
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body($html);
		exit;
	}
	
	
	$EnableSambaActiveDirectory=$sock->GET_INFO("EnableSambaActiveDirectory");

	
	
	$sock=new sockets();
	$config=unserialize(base64_decode($sock->GET_INFO("SambaAdInfos")));
	
	if(preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+).([0-9]+)#",$config["ADSERVER_IP"],$re)){
		$ipnum1=$re[1];
		$ipnum2=$re[2];
		$ipnum3=$re[3];
		$ipnum4=$re[4];
	}
	
	
	
	$form_ip="
	<table style='width:50px'>
	<tr>
		<td style='margin:0;padding:0' align='center' width=1%>". Field_text("ipnum1",$ipnum1,"font-size:14px;padding:3px;width:35px;text-align:center")."</td>
		<td style='margin:0;padding:0' align='center' width=1%><strong style='font-size:16px;'>.</strong></td>
		<td style='margin:0;padding:0' align='center' width=1%>". Field_text("ipnum2",$ipnum2,"font-size:14px;padding:3px;width:35px;text-align:center")."</td>
		<td style='margin:0;padding:0' align='center' width=1%><strong style='font-size:16px;'>.</strong></td>
		<td style='margin:0;padding:0' align='center' width=1%>". Field_text("ipnum3",$ipnum3,"font-size:14px;padding:3px;width:35px;text-align:center")."</td>
		<td style='margin:0;padding:0' align='center' width=1%><strong style='font-size:16px;'>.</strong></td>
		<td style='margin:0;padding:0' align='center' width=1%>". Field_text("ipnum4",$ipnum4,"font-size:14px;padding:3px;width:35px;text-align:center")."</td>
	</tr>						
		
	</table>
	
	";
	
	$html="
	<div class=explain>{make_samba_ad_text}</div>
	<table style='width:100%;padding:9px' class=form>
	<tr>
		<td valign='top' align='center'>
				<img src='img/wink3_bg.png' id='sambadimg'>
				<br>
				<div id='netadsleave'></div>
		</td>
		<td valign='top'>
	<div id='EnableManageUsersTroughActiveDirectoryID'>
	<table style='width:100%'>

	<tr>
		<td class=legend style='font-size:12px'>{activedirectory_server}:</td>
		<td>". Field_text("ADSERVER",$config["ADSERVER"],"font-size:14px;padding:3px;width:165px")."</td>
		<td>". help_icon("{howto_ad_server}")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:12px'>{activedirectory_ipaddr}:</td>
		<td style='margin:0;padding:0'>$form_ip</td>
		<td>". help_icon("{howto_ADIPADDR}")."</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:12px'>{activedirectory_domain}:</td>
		<td>". Field_text("ADDOMAIN",$config["ADDOMAIN"],"font-size:14px;padding:3px;width:165px")."</td>
		<td>". help_icon("{howto_ADDOMAIN}")."</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:12px'>{activedirectory_admin}:</td>
		<td>". Field_text("ADADMIN",$config["ADADMIN"],"font-size:14px;padding:3px;width:165px")."</td>
		<td>". help_icon("{howto_ADADMIN}")."</td>
	</tr>		
		
	<tr>
		<td class=legend style='font-size:12px'>{password}:</td>
		<td>". Field_password("PASSWORD",$config["PASSWORD"],"width:100px;font-size:14px;padding:3px;width:165px")."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend style='font-size:12px'>{winbind_user_password}:</td>
		<td>". Field_password("WINBINDPASSWORD",$config["WINBINDPASSWORD"],"width:100px;font-size:14px;padding:3px;width:165px")."</td>
		<td>". help_icon("{howto_WINBINDPASSWORD}")."</td>
	</tr>	
	
	
	<tr>
		<td colspan=3 align='right'>". button("{apply}","SaveAdSettings()")."</td>
	</tr>
	</table>
	</div>
	</td>
	</tr>
	</table>
<div id='net-ads-infos'></div>
				<div style='text-align:right'>". imgtootltip("20-refresh.png","{refresh}","RefreshAdsInfos()")."</div>			
	<script>
	
	function RefreshAdsInfos(){
		LoadAjax('net-ads-infos','$page?net-ads-infos=yes');
	}
	
	var X_SaveAdSettings= function (obj) {
		var results=obj.responseText;
		if(results.length>2){
			alert(results);
			document.getElementById('sambadimg').src='img/wink3_bg.png';
			return;

			}
		AdConnectPopup();
		}	
		
	var X_NetAdsLeave= function (obj) {
		var results=obj.responseText;
		if(results.length>2){
			alert(results);
			document.getElementById('sambadimg').src='img/wink3_bg.png';
			return;

			}
		 RefreshTab('main_ad_connect_popup');
		}		

		
		
		function SaveAdSettings(){
			var XHR = new XHRConnection();
			var ipnum1=document.getElementById('ipnum1').value;
			var ipnum2=document.getElementById('ipnum2').value;
			var ipnum3=document.getElementById('ipnum3').value;
			var ipnum4=document.getElementById('ipnum4').value;
			
			XHR.appendData('ADSERVER',document.getElementById('ADSERVER').value);
			XHR.appendData('ADDOMAIN',document.getElementById('ADDOMAIN').value);
			XHR.appendData('ADADMIN',document.getElementById('ADADMIN').value);
			XHR.appendData('PASSWORD',document.getElementById('PASSWORD').value);
			XHR.appendData('ADSERVER_IP',ipnum1+'.'+ipnum2+'.'+ipnum3+'.'+ipnum4);
			XHR.appendData('WINBINDPASSWORD',document.getElementById('WINBINDPASSWORD').value);
			document.getElementById('sambadimg').src='img/wait_verybig.gif';
			XHR.sendAndLoad('$page', 'GET',X_SaveAdSettings);	
		}	
		
		function NetAdsLeave(){
			var XHR = new XHRConnection();
			XHR.appendData('netads-leave-perform','yes');
			document.getElementById('sambadimg').src='img/wait_verybig.gif';
			XHR.sendAndLoad('$page', 'GET',X_NetAdsLeave);			
		
		}
		
		
		
	RefreshAdsInfos();
	
</script>";

	echo $tpl->_ENGINE_parse_body($html);
	}

function save(){
	$tpl=new templates();
	$sock=new sockets();
	if(preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+).([0-9]+)#",$_GET["ADSERVER"])){
		echo $tpl->javascript_parse_text("{SAMBAD_NOT_IP_IN_SRVNAME}");
		return;
	}	
	
	if($_GET["ADDOMAIN"]==null){
		echo $tpl->javascript_parse_text("{DOMAIN_CANNOT_BE_NULL}");
		return;
	}
	
	
	$server_ip=$_GET["ADSERVER_IP"];
	$hostname=$_GET["ADSERVER"];
	if(preg_match("#^(.+?)\.#",$hostname,$re)){
		writelogs("Strip $hostname to {$re[1]}",__FUNCTION__,__FILE__,__LINE__);
		$hostname=$re[1];
		$_GET["ADSERVER"]=$re[1];
	}
	
	
	if(preg_match("#(.+?)\.(.+)#",trim($_GET["ADDOMAIN"]),$re)){$_GET["WORKGROUP"]=$re[1];}else{$_GET["WORKGROUP"]=$_GET["ADDOMAIN"];}
	$servername="{$_GET["ADSERVER"]}.{$_GET["ADDOMAIN"]}";
	writelogs("$server_ip/$servername/{$_GET["WORKGROUP"]}",__FUNCTION__,__FILE__,__LINE__);
	
	
	$ETCHOSTS=false;
	if(preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+).([0-9]+)#",$server_ip)){
		$ipaddr=gethostbyname($hostname);
		writelogs("gethostbyname($hostname)=$ipaddr",__FUNCTION__,__FILE__,__LINE__);
		if(!preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+).([0-9]+)#",$ipaddr)){
			$ETCHOSTS=true;
		}
		writelogs("ETCHOSTS=$ETCHOSTS",__FUNCTION__,__FILE__,__LINE__);
		
		
		if(!$ETCHOSTS){
			$PING=trim($sock->getFrameWork("cmd.php?ping=yes&ip=$ipaddr"));
			writelogs("PING($ipaddr)=$PING",__FUNCTION__,__FILE__,__LINE__);
			if($PING=="FALSE"){$ETCHOSTS=true;}
		}
		
		if($ETCHOSTS){
			$line=base64_encode("$server_ip\t$servername\t$hostname");
			$sock->getFrameWork("cmd.php?etc-hosts-add=$line");
						
		}
	}
	
	
	$ipaddr=gethostbyname($servername);
	writelogs("gethostbyname($servername)=$ipaddr",__FUNCTION__,__FILE__,__LINE__);
	
	
	if(!preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+).([0-9]+)#",$ipaddr)){
		echo $tpl->javascript_parse_text("$ipaddr\n{SAMBAD_SRVNAME_CANOT_BE_RESOLVED}");
		return;
	}
	$PING=trim($sock->getFrameWork("cmd.php?ping=$ipaddr"));
	if($PING=="FALSE"){
		echo $tpl->javascript_parse_text("$servername\n$ipaddr\n{SAMBAD_SRVNAME_CANOT_BE_PING}");
		return;
	}
	
		
		$arrayCyrus["domain"]=$_GET["ADDOMAIN"];
		$arrayCyrus["servername"]=$_GET["ADSERVER"];
		$arrayCyrus["admin"]=$_GET["ADADMIN"];
		$arrayCyrus["password"]=$_GET["PASSWORD"];	
		$arrayCyrus["WINBINDPASSWORD"]=$_GET["WINBINDPASSWORD"];
		
	
	
	$sock->SET_INFO("EnableSambaActiveDirectory",1);
	//$sock->SET_INFO("EnableManageUsersTroughActiveDirectory",$_GET["EnableManageUsersTroughActiveDirectory"]);
	$array=base64_encode(serialize($_GET));
	$sock->SaveConfigFile($array,"SambaAdInfos");
	$sock->SaveConfigFile(serialize($arrayCyrus),"CyrusToADConfig");
	$sock->getFrameWork("cmd.php?samba-save-config=yes");
}

function ads_infos(){
	$sock=new sockets();
	$page=CurrentPageName();
	$tpl=new templates();
	$EnableSambaActiveDirectory=$sock->GET_INFO("EnableSambaActiveDirectory");
	if($EnableSambaActiveDirectory<>1){return null;}
	$array=unserialize(base64_decode($sock->getFrameWork("cmd.php?net-ads-info=yes&reconnect={$_GET["reconnect"]}")));
	
	while (list ($index, $line) = each ($array) ){
		
		$html=$html." <strong>$index:</strong><i>$line</i>, ";
	}
	
	if($array["ads_connect"]=="No logon servers"){$array["KDC server"]="No logon servers";$buttonconnect="<center>". button("{reconnect}","AdsReconnect()")."</center>";}
	
	
	
	if($array["KDC server"]<>null){
		$script="
		<script>
			DisableFieldsFromId('EnableManageUsersTroughActiveDirectoryID');
			LoadAjax('netadsleave','$page?netadsleave-icon=yes');
		</script>
		$buttonconnect
		";
	}else{
		$script="
		<center>". button("{reconnect}","AdsReconnect()")."</center>
		<script>
			function AdsReconnect(){
				LoadAjax('net-ads-infos','$page?net-ads-infos=yes&reconnect=yes');
			
			}
			
		</script>
		
		";
		
	}

	echo $tpl->_ENGINE_parse_body("<div class=explain>$html</div>$script");
	
}

function netadsleave_perform(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?net-ads-leave=yes");
	$sock->SET_INFO("EnableSambaActiveDirectory",0);
	$sock->getFrameWork("cmd.php?samba-save-config=yes");
	
}

function netadsleave_icon(){
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("<hr>".imgtootltip("delete-64.png","{leave_ad}","NetAdsLeave()"));
	
}



