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


	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_GET["master"])){master_index();exit;}
	if(isset($_GET["client"])){client_index();exit;}
	if(isset($_GET["ActAsASyslogServer"])){ActAsASyslogServerSave();exit;}
	if(isset($_GET["ActAsASyslogClient"])){ActAsASyslogClientSave();exit;}
	if(isset($_GET["syslog-servers-list"])){SyslogServerList();exit;}
	if(isset($_GET["syslog-host"])){SyslogServerListAdd();exit;}
	if(isset($_GET["syslog-host-delete"])){SyslogServerListDel();exit;}
js();

function js(){
		$page=CurrentPageName();
		$html="
		
		
		function syslogConfigLoad(){
			$('#BodyContent').load('$page?tabs=yes');
			}
			
		syslogConfigLoad();
		";
		echo $html;
		
	
}


function tabs(){
	
	
	$page=CurrentPageName();
	$tpl=new templates();
	$array["master"]='{syslog_server}';
	$array["client"]='{client}';
	
	
	

	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_syslog style='width:100%;height:750px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_config_syslog\").tabs();});
		</script>";		
		
	
}

function master_index(){
	$sock=new sockets();
	$tpl=new templates();
	$page=CurrentPageName();
	
	$ActAsASyslogServer=$sock->GET_INFO("ActAsASyslogServer");
	$enable=Paragraphe_switch_img("{enable_syslog_server}","{enable_syslog_server_text}","ActAsASyslogServer","$ActAsASyslogServer",null,540);
	
	$html="
	<div id='ActAsASyslogServerDiv'>
	$enable
	<div style='text-align:right'><hr>". button("{apply}","ActAsASyslogServerSave()")."</div>
	</div>
	
	<script>
		var x_ActAsASyslogServerSave= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			RefreshTab('main_config_syslog');
		}			
		
	function ActAsASyslogServerSave(){
		var XHR = new XHRConnection();
		XHR.appendData('ActAsASyslogServer',document.getElementById('ActAsASyslogServer').value);
		document.getElementById('ActAsASyslogServerDiv').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_ActAsASyslogServerSave);		
		}
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function client_index(){
	$sock=new sockets();
	$tpl=new templates();
	$page=CurrentPageName();
	
	$ActAsASyslogClient=$sock->GET_INFO("ActAsASyslogClient");
	$enable=Paragraphe_switch_img("{enable_syslog_client}","{enable_syslog_client_text}","ActAsASyslogClient","$ActAsASyslogClient",null,540);
	
	$html="
	<div id='ActAsASyslogClientDiv'>
	$enable
	<div style='text-align:right'><hr>". button("{apply}","ActAsASyslogClientSave()")."</div>
	</div>
	<p>&nbsp;</p>
	
	<table style='width:100%' class=form>
		<tr>
			<td class=legend>{address}:</td>
			<td>". Field_text("syslog-host",null,"font-size:14px;font-weight:bold;width:210px")."</td>
			<td class=legend>{port}:</td>
			<td>". Field_text("syslog-port",514,"font-size:14px;font-weight:bold;width:60px")."</td>	
			<td width=1%>". button("{add}","AddServerSyslogHost()")."</td>
		</tr>
	</table>
	
	<div id='syslog-servers-list' style='width:100%;height:255px;overflow:auto'></div>
	
	<script>
		var x_ActAsASyslogClientSave= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			RefreshTab('main_config_syslog');
		}	

		var x_AddServerSyslogHost= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			SyslogServerListRefresh();
		}			
		
	function ActAsASyslogClientSave(){
		var XHR = new XHRConnection();
		XHR.appendData('ActAsASyslogClient',document.getElementById('ActAsASyslogClient').value);
		document.getElementById('ActAsASyslogClientDiv').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_ActAsASyslogClientSave);		
		}
		
	function AddServerSyslogHost(){
		var XHR = new XHRConnection();
		XHR.appendData('syslog-host',document.getElementById('syslog-host').value);
		XHR.appendData('syslog-port',document.getElementById('syslog-port').value);
		
		document.getElementById('syslog-servers-list').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_AddServerSyslogHost);		
	}
		
	function SyslogServerListRefresh(){
		LoadAjax('syslog-servers-list','$page?syslog-servers-list=yes');
	
	}
	
	SyslogServerListRefresh();
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function SyslogServerListAdd(){
	$sock=new sockets();
	$serversList=unserialize(base64_decode($sock->GET_INFO("ActAsASyslogClientServersList")));
	$serversList["{$_GET["syslog-host"]}:{$_GET["syslog-port"]}"]="{$_GET["syslog-host"]}:{$_GET["syslog-port"]}";
	$sock->SaveConfigFile(base64_encode(serialize($serversList)),"ActAsASyslogClientServersList");
	$sock->getFrameWork("cmd.php?syslog-client-mode=yes");
	
	
}
function SyslogServerListDel(){
	$sock=new sockets();
	$serversList=unserialize(base64_decode($sock->GET_INFO("ActAsASyslogClientServersList")));
	unset($serversList[$_GET["syslog-host-delete"]]);
	$sock->SaveConfigFile(base64_encode(serialize($serversList)),"ActAsASyslogClientServersList");
	$sock->getFrameWork("cmd.php?syslog-client-mode=yes");	
}

function SyslogServerList(){
	$tpl=new templates();
	$page=CurrentPageName();	
	$sock=new sockets();
	$ActAsASyslogClient=$sock->GET_INFO("ActAsASyslogClient");
	$serversList=unserialize(base64_decode($sock->GET_INFO("ActAsASyslogClientServersList")));
	if(count($serversList)==0){return;}
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th>&nbsp;</th>
		<th>{server}</th>
		<th>{status}</th>
		<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";		

	$icon="dns-cp-22.png";
if(is_array($serversList)){	
	while (list ($num, $server) = each ($serversList) ){
		if($server==null){continue;}
		$color="black";
		$udp="UNKNOWN";
		if($ActAsASyslogClient==1){
		if(preg_match("#(.+?):([0-9]+)#",$server,$re)){
			$udp=$sock->getFrameWork("cmd.php?IsUDPport=yes&host={$re[1]}&port={$re[2]}");}
		}
		if($udp=="UNKNOWN"){$udp_img="warning24.png";}
		if($udp=="OK"){$udp_img="ok24.png";}
		if($udp=="FAILED"){$udp_img="danger24.png";}
		
		
		
		if($ActAsASyslogClient<>1){$color="#CCCCCC";}
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$delete=imgtootltip("delete-32.png","{delete}","SyslogServerDelete('$server')");
		$html=$html . "
		<tr  class=$classtr>
			<td width=1%><img src='img/$icon'></td>
			<td width=99%><strong style='font-size:14px'><code style='color:$color'>$server</code></td>
			<td width=1% align='center'><img src='img/$udp_img'></td>
			<td width=1%>$delete</td>
		</td>
		</tr>";
		
	}
}
	
	$html=$html."</tbody></table>
	<script>

	var x_SyslogServerDelete= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);}	
		SyslogServerListRefresh();
	}		
	
	function SyslogServerDelete(key){
		var XHR = new XHRConnection();
		XHR.appendData('syslog-host-delete',key);	
		document.getElementById('syslog-servers-list').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_SyslogServerDelete);
		}	

	</script>	
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
}


function ActAsASyslogServerSave(){
	$sock=new sockets();
	$sock->SET_INFO("ActAsASyslogServer",$_GET["ActAsASyslogServer"]);
	$sock->getFrameWork("cmd.php?syslog-master-mode=yes");
}

function ActAsASyslogClientSave(){
	$sock=new sockets();
	$sock->SET_INFO("ActAsASyslogClient",$_GET["ActAsASyslogClient"]);
	$sock->getFrameWork("cmd.php?syslog-client-mode=yes");	
	
}


