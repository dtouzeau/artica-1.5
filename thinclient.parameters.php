<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	
	
	
	if(posix_getuid()==0){die();}
	
	$user=new usersMenus();
	if($user->AsVirtualBoxManager==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["index"])){index();exit;}
	if(isset($_GET["AUDIO_LEVEL"])){SAVE();exit;}
	if(isset($_GET["SCREEN_RESOLUTION"])){SAVE();exit;}
	
	if(isset($_GET["sessions"])){sessions();exit;}
	if(isset($_GET["session-type"])){session_type();exit;}
	if(isset($_GET["screen"])){screen();exit;}
	if(isset($_GET["RDESKTOP_SERVER"])){SAVE_RDESKTOP_SERVER();exit;}
	js();
function js(){
	
	$page=CurrentPageName();
	$tpl=new templates();	
	$title=$tpl->_ENGINE_parse_body("{computer}:{$_GET["uid"]}");	
	
	$html="
		function ThinCLientParmsLoadpage(){
			YahooWin2('600','$page?popup=yes&uid={$_GET["uid"]}','$title');
			
		}
		ThinCLientParmsLoadpage();
		";
		
	echo $html;
	
}	

function popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$md=md5($_GET["uid"]);
	$array["index"]='{general_settings}';
	$array["screen"]='{screen}';
	

	
	
	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&uid={$_GET["uid"]}\"><span>$ligne</span></a></li>\n");
	}
	
	
	for($i=0;$i<4;$i++){
		$num=$i+1;
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?sessions=yes&uid={$_GET["uid"]}&NUM=$i\"><span>{session} $num</span></a></li>\n");
	}
	
	
	
	echo "
	<div id=main_config_thinclient_$md style='width:100%;height:400px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_thinclient_$md').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			
			
			});
		</script>";	}
	
	
function screen(){
	$page=CurrentPageName();
	$sock=new sockets();
	$tpl=new templates();
	$md=md5($_GET["uid"]);
	$q=new mysql();
	$sql="SELECT parameters FROM thinclient_computers WHERE `uid`='{$_GET["uid"]}'";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	$parameters=unserialize(base64_decode($ligne["parameters"]));	
	
	if($parameters["SCREEN_RESOLUTION"]==null){$parameters["SCREEN_RESOLUTION"]="1024x768";}
	if($parameters["SCREEN_RESOLUTION_SEQUENCE_ENABLED"]==null){$parameters["SCREEN_RESOLUTION_SEQUENCE_ENABLED"]="1";}
	if($parameters["SCREEN_RESOLUTION_SEQUENCE"]==null){$parameters["SCREEN_RESOLUTION_SEQUENCE"]="1024x768|800x600|640x480|*";}
	if($parameters["SCREEN_BLANK_TIME"]==null){$parameters["SCREEN_BLANK_TIME"]="10";}
	if($parameters["SCREEN_STANDBY_TIME"]==null){$parameters["SCREEN_STANDBY_TIME"]="20";}
	if($parameters["SCREEN_SUSPEND_TIME"]==null){$parameters["SCREEN_SUSPEND_TIME"]="30";}
	if($parameters["SCREEN_OFF_TIME"]==null){$parameters["SCREEN_OFF_TIME"]="60";}
	if($parameters["DONT_VT_SWITCH_STATE"]==null){$parameters["DONT_VT_SWITCH_STATE"]="0";}
	if($parameters["DONT_ZAP_STATE"]==null){$parameters["DONT_ZAP_STATE"]="0";}
	
	
	
	
	
	$screens["1920x1200"]="1920x1200";
	$screens["1024x600"]="1024x600";
	$screens["1024x768"]="1024x768";
	$screens["800x600"]="800x600";
	
$html="<table style='width:100%'>

	<tr>
		<td valign='top' style='font-size:13px' class=legend>{SCREEN_RESOLUTION}:<td>
		<td>". Field_array_Hash($screens,"SCREEN_RESOLUTION",$parameters["SCREEN_RESOLUTION"],"",null,0,"font-size:13px;padding:3px")."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td valign='top' style='font-size:13px' class=legend>{SCREEN_RESOLUTION_SEQUENCE}:<td>
		<td>". Field_checkbox("SCREEN_RESOLUTION_SEQUENCE_ENABLED",1,$parameters["SCREEN_RESOLUTION_SEQUENCE_ENABLED"],"SCREEN_RESOLUTION_SEQUENCE_SWITCH()")."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td valign='top' style='font-size:13px' class=legend>{SEQUENCE}:<td>
		<td>". Field_text("SCREEN_RESOLUTION_SEQUENCE",$parameters["SCREEN_RESOLUTION_SEQUENCE"],"font-size:13px;padding:3px")."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td valign='top' style='font-size:13px' class=legend>{SCREEN_BLANK_TIME}:<td>
		<td style='font-size:13px'>". Field_text("SCREEN_BLANK_TIME",$parameters["SCREEN_BLANK_TIME"],"font-size:13px;padding:3px;width:60px")."&nbsp;{minutes}</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td valign='top' style='font-size:13px' class=legend>{SCREEN_STANDBY_TIME}:<td>
		<td style='font-size:13px'>". Field_text("SCREEN_STANDBY_TIME",$parameters["SCREEN_STANDBY_TIME"],"font-size:13px;padding:3px;width:60px")."&nbsp;{minutes}</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td valign='top' style='font-size:13px' class=legend>{SCREEN_STANDBY_TIME}:<td>
		<td style='font-size:13px'>". Field_text("SCREEN_STANDBY_TIME",$parameters["SCREEN_STANDBY_TIME"],"font-size:13px;padding:3px;width:60px")."&nbsp;{minutes}</td>
		<td>&nbsp;</td>
	</tr>
		<td valign='top' style='font-size:13px' class=legend>{SCREEN_SUSPEND_TIME}:<td>
		<td style='font-size:13px'>". Field_text("SCREEN_SUSPEND_TIME",$parameters["SCREEN_SUSPEND_TIME"],"font-size:13px;padding:3px;width:60px")."&nbsp;{minutes}</td>
		<td>&nbsp;</td>
	</tr>
	</tr>
		<td valign='top' style='font-size:13px' class=legend>{SCREEN_OFF_TIME}:<td>
		<td style='font-size:13px'>". Field_text("SCREEN_OFF_TIME",$parameters["SCREEN_OFF_TIME"],"font-size:13px;padding:3px;width:60px")."&nbsp;{minutes}</td>
		<td>&nbsp;</td>
	</tr>	
	
	<tr>
		<td valign='top' style='font-size:13px' class=legend>{DONT_VT_SWITCH_STATE}:<td>
		<td>". Field_checkbox("DONT_VT_SWITCH_STATE",1,$parameters["DONT_VT_SWITCH_STATE"])."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td valign='top' style='font-size:13px' class=legend>{DONT_ZAP_STATE}:<td>
		<td>". Field_checkbox("DONT_ZAP_STATE",1,$parameters["DONT_ZAP_STATE"])."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td colspan=3 align='right'><hr>". button("{apply}","SaveScreeResolution()")."</td>
	</tr>
	</table>
	
	<script>
	
	var x_SaveThinStationParams1= function (obj) {
		var response=obj.responseText;
		if(response.length>0){alert(response);}
		RefreshTab('main_config_thinclient_$md');
	}	

function SaveScreeResolution(){
		var XHR = new XHRConnection();
		XHR.appendData('uid','{$_GET["uid"]}');
		XHR.appendData('SCREEN_RESOLUTION',document.getElementById('SCREEN_RESOLUTION').value);
		XHR.appendData('SCREEN_RESOLUTION_SEQUENCE',document.getElementById('SCREEN_RESOLUTION_SEQUENCE').value);
		XHR.appendData('SCREEN_BLANK_TIME',document.getElementById('SCREEN_BLANK_TIME').value);
		XHR.appendData('SCREEN_STANDBY_TIME',document.getElementById('SCREEN_STANDBY_TIME').value);
		XHR.appendData('SCREEN_SUSPEND_TIME',document.getElementById('SCREEN_SUSPEND_TIME').value);
		XHR.appendData('SCREEN_OFF_TIME',document.getElementById('SCREEN_OFF_TIME').value);
		
		
		if(document.getElementById('SCREEN_RESOLUTION_SEQUENCE_ENABLED').checked){XHR.appendData('SCREEN_RESOLUTION_SEQUENCE_ENABLED',1);}else {XHR.appendData('SCREEN_RESOLUTION_SEQUENCE_ENABLED',0);}
		if(document.getElementById('DONT_VT_SWITCH_STATE').checked){XHR.appendData('DONT_VT_SWITCH_STATE',1);}else {XHR.appendData('DONT_VT_SWITCH_STATE',0);}
		if(document.getElementById('DONT_ZAP_STATE').checked){XHR.appendData('DONT_ZAP_STATE',1);}else {XHR.appendData('DONT_ZAP_STATE',0);}
		XHR.sendAndLoad('$page', 'GET',x_SaveThinStationParams1);
	}	
	
		function SCREEN_RESOLUTION_SEQUENCE_SWITCH(){
			document.getElementById('SCREEN_RESOLUTION').disabled=true;
			document.getElementById('SCREEN_RESOLUTION_SEQUENCE').disabled=true;
			
			if(document.getElementById('SCREEN_RESOLUTION_SEQUENCE_ENABLED').checked){
				document.getElementById('SCREEN_RESOLUTION_SEQUENCE').disabled=false;
			}else{
				document.getElementById('SCREEN_RESOLUTION').disabled=false;
			}
		
		}
		SCREEN_RESOLUTION_SEQUENCE_SWITCH();
	</script>
	
	";	
	echo $tpl->_ENGINE_parse_body($html);	
	//Screen resolutions available in the workstations
}	
	
	
function sessions(){
	$page=CurrentPageName();
	$sock=new sockets();
	$tpl=new templates();
	$md=md5($_GET["uid"]);
	$q=new mysql();
	$sql="SELECT parameters FROM thinclient_computers WHERE `uid`='{$_GET["uid"]}'";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	$parameters=unserialize(base64_decode($ligne["parameters"]));
	
	$sql="SELECT `package` FROM thinclient_package_modules";
	$results=$q->QUERY_SQL($sql,'artica_backup');
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$modules[trim($ligne["package"])]=true;
	}		
	
	//if($modules["vncviewer"]==true){$session_type["vncviewer"]="vncviewer";}
	if($modules["rdesktop"]==true){$session_type["rdesktop"]="Remote desktop";}
	//if($modules["blackbox"]==true){$session_type["blackbox"]="blackbox";}
	//if($modules["icewm"]==true){$session_type["icewm"]="icewm";}
	//if($modules["dillo"]==true){$session_type["dillo"]="Web Browser";}				

	
	if($parameters["SESSIONS"][$_GET["NUM"]]["TITLE"]==null){$parameters["SESSIONS"][$_GET["NUM"]]["TITLE"]="Remote Desktop";}
	if($parameters["SESSIONS"][$_GET["NUM"]]["TYPE"]==null){$parameters["SESSIONS"][$_GET["NUM"]]["TYPE"]="rdesktop";}
	
	$SESSIONS=$parameters["SESSIONS"];
	
	
$html="<table style='width:100%'>
	<tr>
		<td valign='top' style='font-size:13px' class=legend>{session_name}:<td>
		<td>". Field_text("TITLE",$SESSIONS[$_GET["NUM"]]["TITLE"],"font-size:13px;padding:3px")."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td valign='top' style='font-size:13px' class=legend>{type}:<td>
		<td>". Field_array_Hash($session_type,"TYPE",$SESSIONS[$_GET["NUM"]]["TYPE"],"ShowSessionType()",null,0,"font-size:13px;padding:3px")."</td>
		<td>&nbsp;</td>
	</tr>
		<tr>
		<td colspan=3 align='right'><hr>". button("{apply}","SaveSessionSettings{$_GET["NUM"]}()")."</td>
		</tr>			
		</table>
		
		<div id='session-params-{$_GET["NUM"]}' ></div>
		
	<script>
		var x_SaveSessionSettings= function (obj) {
			var response=obj.responseText;
			if(response.length>0){alert(response);}
		}	
	
	function SaveSessionSettings{$_GET["NUM"]}(){
			var XHR = new XHRConnection();
			XHR.appendData('uid','{$_GET["uid"]}');
			XHR.appendData('TITLE',document.getElementById('TITLE').value);
			XHR.appendData('TYPE',document.getElementById('TYPE').value);
			XHR.appendData('NUM','{$_GET["NUM"]}');
			XHR.sendAndLoad('$page', 'GET',x_SaveSessionSettings);
		}	
	
	
	
		function ShowSessionType(num){
			var typesess=document.getElementById('TYPE').value;
			LoadAjax('session-params-{$_GET["NUM"]}','$page?session-type='+typesess+'&NUM='+num+'&uid={$_GET["uid"]}');
		}
	ShowSessionType({$_GET["NUM"]});
	</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
}

function session_type(){
	if($_GET["session-type"]==null){return;}
	$page=CurrentPageName();
	$sock=new sockets();
	$tpl=new templates();
	$md=md5($_GET["uid"]);
	$q=new mysql();	
	$sql="SELECT parameters FROM thinclient_computers WHERE `uid`='{$_GET["uid"]}'";
	
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	$parameters=unserialize(base64_decode($ligne["parameters"]));
	if($parameters["SESSIONS"][$_GET["NUM"]]["TITLE"]==null){$parameters["SESSIONS"][$_GET["NUM"]]["TITLE"]="Remote Desktop";}
	if($parameters["SESSIONS"][$_GET["NUM"]]["TYPE"]==null){$parameters["SESSIONS"][$_GET["NUM"]]["TYPE"]="rdesktop";}
	if($parameters["SESSIONS"][$_GET["NUM"]]["COLOUR"]==null){$parameters["SESSIONS"][$_GET["NUM"]]["COLOUR"]="24";}
	
	
	
	$SESSIONS=$parameters["SESSIONS"];	
	
	$color=array(8=>8,16=>16,24=>24);
	
	if($_GET["session-type"]=="rdesktop"){
		$html="
		<table style='width:100%;margin-top:10px'>
		<tr>
			<td valign='top' style='font-size:13px' class=legend>{RDESKTOP_SERVER}:<td>
			<td>". Field_text("RDESKTOP_SERVER",$SESSIONS[$_GET["NUM"]]["RDESKTOP_SERVER"],"font-size:13px;padding:3px")."</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td valign='top' style='font-size:13px' class=legend>{username}:<td>
			<td>". Field_text("username",$SESSIONS[$_GET["NUM"]]["username"],"font-size:13px;padding:3px")."</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td valign='top' style='font-size:13px' class=legend>{domain}:<td>
			<td>". Field_text("domain",$SESSIONS[$_GET["NUM"]]["domain"],"font-size:13px;padding:3px")."</td>
			<td>&nbsp;</td>
		</tr>			
		<tr>
			<td valign='top' style='font-size:13px' class=legend>{password}:<td>
			<td>". Field_password("password",$SESSIONS[$_GET["NUM"]]["password"],"font-size:13px;padding:3px")."</td>
			<td>&nbsp;</td>
		</tr>	
		<tr>
			<td valign='top' style='font-size:13px' class=legend>{colour_depth}:<td>
			<td>". Field_array_Hash($color,"COLOUR",$SESSIONS[$_GET["NUM"]]["COLOUR"],"",null,0,"font-size:13px;padding:3px")."</td>
			<td>&nbsp;</td>
		</tr>	
		<tr>
		<td colspan=3 align='right'><hr>". button("{apply}","SaveRDPSettings{$_GET["NUM"]}()")."</td>
		</tr>			
		</table>
	<script>
		var x_SaveRDPSettings= function (obj) {
			var response=obj.responseText;
			if(response.length>0){alert(response);}
		}	
	
	function SaveRDPSettings{$_GET["NUM"]}(){
			var XHR = new XHRConnection();
			XHR.appendData('uid','{$_GET["uid"]}');
			XHR.appendData('RDESKTOP_SERVER',document.getElementById('RDESKTOP_SERVER').value);
			XHR.appendData('username',document.getElementById('username').value);
			XHR.appendData('domain',document.getElementById('domain').value);
			XHR.appendData('password',document.getElementById('password').value);
			XHR.appendData('COLOUR',document.getElementById('COLOUR').value);
			XHR.appendData('NUM','{$_GET["NUM"]}');
			
			XHR.sendAndLoad('$page', 'GET',x_SaveRDPSettings);
		}	
	
	</script>
			
		
		";
		echo $tpl->_ENGINE_parse_body($html);
		return;
		
	}
	
	
	
}
	
function index(){
	$page=CurrentPageName();
	$sock=new sockets();
	$tpl=new templates();
	$md=md5($_GET["uid"]);
	$q=new mysql();
	$sql="SELECT parameters FROM thinclient_computers WHERE `uid`='{$_GET["uid"]}'";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	$parameters=unserialize(base64_decode($ligne["parameters"]));
	$keymap_list=unserialize(base64_decode($sock->getFrameWork("cmd.php?keymap-list=yes")));
	$keymap_list[null]="{select}";
	if($parameters["AUDIO_LEVEL"]==null){$parameters["AUDIO_LEVEL"]=10;}
	if($parameters["USB_ENABLED"]==null){$parameters["USB_ENABLED"]=0;}
	if($parameters["DAILY_REBOOT"]==null){$parameters["DAILY_REBOOT"]=0;}
	if($parameters["CUSTOM_CONFIG"]==null){$parameters["CUSTOM_CONFIG"]=0;}
	if($parameters["RECONNECT_PROMPT"]==null){$parameters["RECONNECT_PROMPT"]="ON";}
	
	
	
	for($i=0;$i<101;$i++){
		$AUDIO_LEVEL_MAPS[$i]=$i;
	}
	
	$reconnect_array=array(
		"OFF"=>"{none}",
		"ON"=>"{RECONNECT_PROMPT}",
		"AUTO"=>"{RECONNECT_AUTO}",
		"MENU"=>"{MENU_SHOW}"
		);

	
	
	$html="<table style='width:100%'>
	<tr>
		<td valign='top' style='font-size:13px' class=legend>{AUDIO_LEVEL}:<td>
		<td>". Field_array_Hash($AUDIO_LEVEL_MAPS,"AUDIO_LEVEL",$parameters["AUDIO_LEVEL"],null,null,0,"font-size:13px;padding:3px")."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td valign='top' style='font-size:13px' class=legend>{KEYBOARD_MAP}:<td>
		<td>". Field_array_Hash($keymap_list,"KEYBOARD_MAP",$parameters["KEYBOARD_MAP"],null,null,0,"font-size:13px;padding:3px")."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td valign='top' style='font-size:13px' class=legend>{RECONNECT_PROMPT}:<td>
		<td>". Field_array_Hash($reconnect_array,"RECONNECT_PROMPT",$parameters["RECONNECT_PROMPT"],null,null,0,"font-size:13px;padding:3px")."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td valign='top' style='font-size:13px' class=legend>{USB_ENABLED}:<td>
		<td>". Field_checkbox("USB_ENABLED",1,$parameters["USB_ENABLED"])."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td valign='top' style='font-size:13px' class=legend>{DAILY_REBOOT}:<td>
		<td>". Field_checkbox("DAILY_REBOOT",1,$parameters["DAILY_REBOOT"])."</td>
		<td>".help_icon("{DAILY_REBOOT_EXPLAIN}")."</td>
	</tr>	
	<tr>
		<td valign='top' style='font-size:13px' class=legend>{CUSTOM_CONFIG}:<td>
		<td>". Field_checkbox("CUSTOM_CONFIG",1,$parameters["CUSTOM_CONFIG"])."</td>
		<td>".help_icon("{CUSTOM_CONFIG_EXPLAIN}")."</td>
	</tr>	
	<tr>
		<td colspan=3 align='right'>
			". button("{apply}","SaveThinStationParams1()")."</td>
	</tr>
	
	</table>
	
	<script>
var x_SaveThinStationParams1= function (obj) {
		var response=obj.responseText;
		if(response.length>0){alert(response);}
		RefreshTab('main_config_thinclient_$md');
	}	

function SaveThinStationParams1(){
		var XHR = new XHRConnection();
		XHR.appendData('uid','{$_GET["uid"]}');
		XHR.appendData('AUDIO_LEVEL',document.getElementById('AUDIO_LEVEL').value);
		XHR.appendData('KEYBOARD_MAP',document.getElementById('KEYBOARD_MAP').value);
		XHR.appendData('RECONNECT_PROMPT',document.getElementById('RECONNECT_PROMPT').value);
		
		
		if(document.getElementById('USB_ENABLED').checked){XHR.appendData('USB_ENABLED',1);}else {XHR.appendData('USB_ENABLED',0);}
		if(document.getElementById('DAILY_REBOOT').checked){XHR.appendData('DAILY_REBOOT',1);}else {XHR.appendData('DAILY_REBOOT',0);}		
		if(document.getElementById('CUSTOM_CONFIG').checked){XHR.appendData('CUSTOM_CONFIG',1);}else {XHR.appendData('CUSTOM_CONFIG',0);}
		XHR.sendAndLoad('$page', 'GET',x_SaveThinStationParams1);
	}	
	
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}
function SAVE_RDESKTOP_SERVER(){
	$q=new mysql();
	$sql="SELECT parameters FROM thinclient_computers WHERE `uid`='{$_GET["uid"]}'";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	$parameters=unserialize(base64_decode($ligne["parameters"]));	
	
	while (list ($num, $ligne) = each ($_GET) ){
		$parameters["SESSIONS"][$_GET["NUM"]][$num]=$ligne;
		
	}	
	
	$new_params=base64_encode(serialize($parameters));
	$sql="UPDATE thinclient_computers SET parameters='$new_params'  WHERE `uid`='{$_GET["uid"]}'";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}	
	
}


function SAVE(){
	$q=new mysql();
	$sql="SELECT parameters FROM thinclient_computers WHERE `uid`='{$_GET["uid"]}'";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	$parameters=unserialize(base64_decode($ligne["parameters"]));	
	
	while (list ($num, $ligne) = each ($_GET) ){
		$parameters[$num]=$ligne;
		
	}
	$new_params=base64_encode(serialize($parameters));
	$sql="UPDATE thinclient_computers SET parameters='$new_params'  WHERE `uid`='{$_GET["uid"]}'";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?thinclients-rebuild=yes");
	
	
}	
