<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	
	
	$user=new usersMenus();
	if($user->AsSystemAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["iwlist"])){iwlist();exit;}
	if(isset($_GET["status"])){status();exit;}
	if(isset($_GET["wifi-eth-status"])){eth_status();exit;}
	if(isset($_GET["CONNECT"])){CONNECT_POPUP();exit;}
	if(isset($_POST["CONNECT_TO_ESSID"])){CONNECT_SAVE();exit;}
	if(isset($_GET["WifiAPEnable"])){WifiAPEnable();exit;}
	js();
	
	
function js(){

	$page=CurrentPageName();
	$html="
	
	function LoadWIFIMain(){
		$('#BodyContent').load('$page?popup=yes');
		
		}

	function CheckClientConnection(){
		LoadAjax('wifi-eth-status','$page?wifi-eth-status=yes&check=yes');
	}
	
	LoadWIFIMain();";
	
	echo $html;
}


function popup(){
	
	$page=CurrentPageName();
	$users=new usersMenus();
	$array["status"]='{status}';
	$array["iwlist"]='{wifi_networks}';
	if($users->HOSTAPD_INSTALLED){
		$array["AP"]='{ESSID}';
	}
	$tpl=new templates();
	
	while (list ($num, $ligne) = each ($array) ){
		
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
		
			
		}
	echo "
	<div id=wifi_main_config style='width:100%;height:500px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#wifi_main_config').tabs({
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


function status(){
	$sock=new sockets();
	$ini=new Bs_IniHandler();
	$ini->loadString(base64_decode($sock->getFrameWork('cmd.php?wifi-ini-status=yes')));
	$page=CurrentPageName();

	$APP_WPA_SUPPLIANT=DAEMON_STATUS_ROUND("APP_WPA_SUPPLIANT",$ini);
	//$dansguardian_status=DAEMON_STATUS_ROUND("DANSGUARDIAN",$ini);
	$html="
		
	<table style='width:99%'>
	<tr>
		<td valing='top' width=1% valign='top'><img src='img/wifi-ok-256.png'><div style='padding:5px;font-size:13px;'>{WIFI_ARTICA_EXPLAIN}</div></td>
		<td valign='top'>
			<div id='wifi-eth-status' style='margin-bottom:5px'></div>
			$APP_WPA_SUPPLIANT
		</td>
	</tr>
	</table>
	
	<script>
		LoadAjax('wifi-eth-status','$page?wifi-eth-status=yes');
	</script>
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}


function iwlist(){
	$tpl=new templates();
	$page=CurrentPageName();
	$sock=new sockets();
	$WifiAPEnable=$sock->GET_INFO("WifiAPEnable");
	$sock=new sockets();
	
	if(!is_file("ressources/logs/iwlist.scan")){
		$sock=new sockets();$sock->getFrameWork("cmd.php?iwlist=yes");
	}else{
		$f=file_get_time_min("ressources/logs/iwlist.scan");
		if($f>1){
			$sock->getFrameWork("cmd.php?iwlist=yes");
		}
	}
	
	
	$users=new usersMenus();
	$array=unserialize(@file_get_contents("ressources/logs/iwlist.scan"));
	if(!is_array($array)){
		echo $tpl->_ENGINE_parse_body("<H2>{NO_ESSID}</H2>");
		return;
	}
	
	$html="
		<div style='margin:4px'>
		<table style='width:100%'>
		<tr>
			<td align='right' width=99%'>
			<strong style='font-size:13px'>{enable_wifi_ap}</strong>
			</td>
			<td width=1%>". Field_checkbox("WifiAPEnable",1,$WifiAPEnable,"EditWifiAPEnable()")."</td>
		</tr>
		</table>
	</div>
	<table style='width:100%'>
	<tr>
		<th colspan=2>{ESSID}</th>
		<th>{quality}</th>
		<th>{crypted}</th>
		<th>{bits_rate}</th>
	</tr>
	";
	if(is_array($array)){
	while (list ($num, $ligne) = each ($array) ){
		$APNUM=$num;
		$QUALITY=$ligne["QUALITY"];
		$ESSID=$ligne["ESSID"];
		$KEY=$ligne["KEY"];
		if($KEY){$key="<img src='img/22-key.png'>";}else{$key="&nbsp;";}
		$RATES=explode(";",$ligne["RATES"]);
		$tooltip="{connect}";
		
		while (list ($a, $b) = each ($RATES) ){
			$b=trim($b);
			$b=str_replace(" ","&nbsp;",$b);
			if($b<>null){$RR[]=$b;}
		}
		
		if($ligne["ESSID_SELECTED"]){$wifiok="<img src='img/wifi-ok-22.png'>";}else{$wifiok="&nbsp;";}
		if($users->WPA_SUPPLIANT_INSTALLED){
			if($WifiAPEnable==1){
				$js="WifiConnectAP('$ESSID')";
			}else{
				$tooltip="{WifiConnectAPDisabled}";
			}
		}
		
		
		
		$html=$html."
				<tr ". CellRollOver($js,"{connect}").">
				<td width=1%>$wifiok</td>
				<td valign='middle' nowrap><strong style='font-size:14px'>$ESSID</strong></td>
				<td valign='top'>". pourcentage($QUALITY)."</td>
				<td width=1% align='center'>$key</td>
				<td valign='top' style='font-size:12px'>". implode(" - ",$RR)."</td>
				
				</tr>
				<tr><td colspan=5><hr></td></tr>
				";
			unset($RR);
		
		
	}}
	
	$html=$html."</table>
	<div style='margin:5px;width:100%;text-align:right' >". imgtootltip("32-refresh.png","{refresh}","RefreshTab('wifi_main_config');")."</div>
	
	
	<script>
		function WifiConnectAP(ESSID){
			YahooWin(650,'$page?CONNECT='+ESSID,'{connect}::'+ESSID);
		}
		
		function x_EditWifiAPEnable(){
			RefreshTab('wifi_main_config');
		}
		
		function EditWifiAPEnable(){
			var XHR = new XHRConnection();
			if(document.getElementById('WifiAPEnable').checked){XHR.appendData('WifiAPEnable','1');}else{XHR.appendData('WifiAPEnable','0');}
			XHR.sendAndLoad('$page', 'GET',x_EditWifiAPEnable);
		}
	
	</script>
	";
	echo $tpl->_ENGINE_parse_body("$html");
}

function CONNECT_POPUP(){
	$ESSID=$_GET["CONNECT"];
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->GET_INFO("WifiAccessPoint")));
	$CONFIG=$array[$ESSID];
	$page=CurrentPageName();
	$html="
	<div style='font-size:13px;padding:5px'>{ESSID_CONNECT_PASSWORD}</div>
	<center id='ESSID_PASS' style='padding:5xp'>
	<table style='width:80%'>
	<tr>
		<td class=legend style='font-size:14px'>{password}:</td>
		<td>".Field_password("ESSID_PASSWORD",$CONFIG["ESSID_PASSWORD"],"font-size:14px;padding:3px")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:14px'>{use_dhcp}:</td>
		<td>". Field_checkbox("UseDhcp",1,$CONFIG["UseDhcp"],"WifiEnableDHCP()")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:14px'>{ip_address}:</td>
		<td>". Field_text("ip_address",$CONFIG["ip_address"],"font-size:14px;padding:3px")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:14px'>{mask}:</td>
		<td>". Field_text("mask",$CONFIG["mask"],"font-size:14px;padding:3px")."</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:14px'>{gateway}:</td>
		<td>". Field_text("gateway",$CONFIG["gateway"],"font-size:14px;padding:3px")."</td>
	</tr>	
	<tr>
	<td colspan=2 align='right'>
	<hr>
		". button("{apply}","SaveESSIDPassword()")."
	</td>
	</tr>
	</table>
	</center>
	
	<script>
		
	var x_SaveESSIDPassword=function (obj) {
			RefreshTab('wifi_main_config');
			var results=obj.responseText;
			if(results.length>0){alert(results);}			
			YahooWinHide();
		}	
		
	function WifiEnableDHCP(){
		if(document.getElementById('UseDhcp').checked){
			document.getElementById('ip_address').disabled=true;
			document.getElementById('mask').disabled=true;
			document.getElementById('gateway').disabled=true;
		}else{
			document.getElementById('ip_address').disabled=false;
			document.getElementById('mask').disabled=false;
			document.getElementById('gateway').disabled=false;
		}
	}
		
		function SaveESSIDPassword(){
			var XHR = new XHRConnection();
			XHR.appendData('CONNECT_TO_ESSID','$ESSID');
    		XHR.appendData('ESSID_PASSWORD',document.getElementById('ESSID_PASSWORD').value);
    		XHR.appendData('ip_address',document.getElementById('ip_address').value);
    		XHR.appendData('mask',document.getElementById('mask').value);
    		XHR.appendData('gateway',document.getElementById('gateway').value);
    		if(document.getElementById('UseDhcp').checked){XHR.appendData('UseDhcp','1');}else{XHR.appendData('UseDhcp','0');}
 			document.getElementById('ESSID_PASS').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
    		XHR.sendAndLoad('$page', 'POST',x_SaveESSIDPassword);
			
		}

	setTimeout('WifiEnableDHCP()',900);
		
	</script>
	
	";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);		
}
	
function CONNECT_SAVE(){
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->GET_INFO("WifiAccessPoint")));
	if(is_array($array)){
		while (list ($ssid, $array2) = each ($array) ){
			$array[$ssid]["ENABLED"]=0;	
		}
	}
	
	
	$_POST["ENABLED"]=1;
	$array[$_POST["CONNECT_TO_ESSID"]]=$_POST;
	$values=base64_encode(serialize($array));
	$sock->SaveConfigFile($values,"WifiAccessPoint");
	echo trim(base64_decode($sock->getFrameWork("cmd.php?wifi-connect-point=yes")));
	
}
function WifiAPEnable(){
	$sock=new sockets();
	$sock->SET_INFO("WifiAPEnable",$_GET["WifiAPEnable"]);
	$sock->getFrameWork("cmd.php?wifi-connect-point=yes");
	$sock->getFrameWork("cmd.php?RestartDaemon=yes");
	$sock->getFrameWork("cmd.php?start-wifi=yes");
}
function eth_status(){
	$sock=new sockets();
	
	if(isset($_GET["check"])){
		$info=explode("\n",base64_decode($sock->getFrameWork("cmd.php?wifi-eth-client-check=yes")));
		if(is_array($info)){
			while (list ($index, $line) = each ($info) ){
				$check=$check."<div><code>$line</code></div>";
			}
		}
	}
	
	$ini=new Bs_IniHandler();
	$ini->loadString(base64_decode($sock->getFrameWork("cmd.php?wifi-eth-status=yes")));
	$conf=$ini->_params["IF"];
	$eth=$conf["eth"];
	$state=$conf["wpa_state"];
	$img="64-win-nic.png";
	if($state==null){$state="disabled";}
	
	
	switch ($state) {
		
		case "disabled":
			$img="64-win-nic-off.png";
		break;
		
		case "INACTIVE":
			$img="64-win-nic-off.png";
			$tootip="{check_wifi_card}";
			$js="CheckClientConnection()";
		break;
		case "SCANNING":
			$img="64-win-nic-infos.png";
			
		
		case "COMPLETED":
			$img="64-win-nic.png";
		
		default:
			;
		break;
	}
	
	
	$img=imgtootltip($img,$tootip,$js);

	$html="
	<table style='width:99%'>
	<tr>
		<td valign='top' width=1%>$img</td>
		<td valign='top'>
			<table style='width:100%'>
			<tr>
				<td class=legend>{nic}:</td>
				<td><strong style='font-size:11px'>$eth</td>
			</tr>
			<tr>
				<td class=legend>{state}:</td>
				<td><strong style='font-size:11px'>{{$state}}</td>
			</tr>	
			<tr>
				<td class=legend>{mac_addr}:</td>
				<td><strong style='font-size:11px'>{$conf["bssid"]}</td>
			</tr>	
			<tr>
				<td class=legend>{ESSID}:</td>
				<td><strong style='font-size:11px'>{$conf["ssid"]}</td>
			</tr>
			<tr>
				<td class=legend>{ip_address}:</td>
				<td><strong style='font-size:11px'>{$conf["ip_address"]}</td>
			</tr>												
			</table>
			$check
		</td>
	</tr>
	</table>
	
	";
	$tpl=new templates();
	
	$add="<div style='width:100%;margin:5px;text-align:right'>".imgtootltip("32-refresh.png","{refresh}","RefreshTab('wifi_main_config');")."</div>";
	
	echo "<br>".RoundedLightGreen($tpl->_ENGINE_parse_body($html).$add);
	
}


?>