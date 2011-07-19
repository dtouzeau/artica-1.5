<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.system.network.inc');
	
	
$usersmenus=new usersMenus();
if(!$usersmenus->AsAnAdministratorGeneric){
	$tpl=new templates();
	$alert=$tpl->_ENGINE_parse_body('{ERROR_NO_PRIVS}');
	echo "alert('$alert');";
	die();	
}

if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["status"])){status();exit;}
if(isset($_GET["status-service"])){status_service();exit;}
if(isset($_GET["conf"])){popup_settings();exit;}
if(isset($_GET["EnableSabnZbdPlus"])){save();exit;}
js();



function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{APP_SABNZBDPLUS}");
	
	if(isset($_GET["in-front-ajax"])){
		echo "$('#BodyContent').load('$page?popup=yes&in-front-ajax=yes');";
		return;
	}
	
	$html="
	YahooWin3('600','$page?popup=yes','$title');";
	
	
	echo $html;
	
	
}


function status(){
	$page=CurrentPageName();
	$tpl=new templates();
	if(isset($_GET["in-front-ajax"])){$title="<H1>{APP_SABNZBDPLUS}</H1>";}
	$html="
	<table style='width=100%'>
	<tr>
		<td valign='top'><div id='APP_SABNZBDPLUS_STATUS'></div></td>
		<td valign='top' width=99%>$title<div class=explain >{APP_SABNZBDPLUS_EXPLAIN}</div></td>
	</tr>
	</table>
	<script>
		LoadAjax('APP_SABNZBDPLUS_STATUS','$page?status-service=yes');
	</script>	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function status_service(){
	
	$sock=new sockets();
	$tpl=new templates();
	$ini=new Bs_IniHandler();
	$page=CurrentPageName();	
	$ini->loadString(base64_decode($sock->getFrameWork("cmd.php?sabnzbdplus-ini-status=yes")));
	$status=DAEMON_STATUS_ROUND("APP_SABNZBDPLUS",$ini,null,0);
	echo $tpl->_ENGINE_parse_body($status);		
	
}

function popup_settings(){
	
	$sock=new sockets();
	$tpl=new templates();
	$page=CurrentPageName();
	$sabnzbdplusDir=$sock->GET_INFO("sabnzbdplusDir");
	$sabnzbdplusPort=$sock->GET_INFO("sabnzbdplusPort");
	$sabnzbdplusIpAddr=$sock->GET_INFO("sabnzbdplusIpAddr");
	
	
	if ($sabnzbdplusDir==null){$sabnzbdplusDir="/home/sabnzbdplus";}
	if(!is_numeric($sabnzbdplusPort)){$sabnzbdplusPort="9666";}
	if($sabnzbdplusIpAddr==null){$sabnzbdplusIpAddr="0.0.0.0";}
	
	$ip=new networking();
	$ips=$ip->ALL_IPS_GET_ARRAY();
	
	while (list ($num, $ligne) = each ($ips) ){
		$wbconsole[]="<li><a href='http://$num:$sabnzbdplusPort' style='font-size:13px;text-decoration:underline'>http://$num:$sabnzbdplusPort</a></li>";
	}
	
	reset($ips);
	$ips["0.0.0.0"]="{all}";
	
	$nets=Field_array_Hash($ips,"sabnzbdplusIpAddr",$sabnzbdplusIpAddr,"style:font-size:13px;padding:3px");
	
	$html="
	<div id='sabnzbdplus-id'>
	<table style='width:99.5%' class=form>
	<tr>
		<td class=legend>{enable_sabnzbdplus}:</td>
		<td>". Field_checkbox("EnableSabnZbdPlus",1,$sock->GET_INFO("EnableSabnZbdPlus"))."</td>
	</tr>
	<tr>
		<td class=legend>{working_directory}:</td>
		<td>". Field_text("sabnzbdplusDir",$sabnzbdplusDir,"font-size:13px;padding:3px")."</td>
	</tr>	
	<tr>
		<td class=legend>{listen_http_port}:</td>
		<td>". Field_text("sabnzbdplusPort",$sabnzbdplusPort,"font-size:13px;padding:3px;width:40px")."</td>
	</tr>	
	<tr>
		<td class=legend>{listen_ip}:</td>
		<td>$nets</td>
	</tr>	
	<tr>
		<td colspan=2 align='right'><hr>". button("{apply}","sabnzbdplusSave()")."</td>
	</tr>	
	</table>
	<hr>
	<H3>{web_console_access}:</H3><br>". @implode("\n",$wbconsole)."
	
	<script>
	
	var x_sabnzbdplusSave= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		RefreshTab('TAB_APP_SABNZBDPLUS');
		
	}	
	
		function sabnzbdplusSave(){
			var XHR=XHRParseElements('sabnzbdplus-id');
			document.getElementById('sabnzbdplus-id').innerHTML='<center><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_sabnzbdplusSave);
		}
	
	</script>";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function save(){
	$sock=new sockets();
	while (list ($num, $ligne) = each ($_GET) ){
		$sock->SET_INFO($num,$ligne);
		
	}
	
	$sock->getFrameWork("cmd.php?sabnzbdplus-restart=yes");
	
}


function popup(){
	
	
	$page=CurrentPageName();
	$array["status"]='{status}';
	$array["conf"]='{settings}';
	
	$tpl=new templates();
	
	
	
	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=TAB_APP_SABNZBDPLUS style='width:100%;height:350px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#TAB_APP_SABNZBDPLUS').tabs({
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





?>