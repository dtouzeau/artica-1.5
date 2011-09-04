<?php
	session_start();
	if($_SESSION["uid"]==null){echo "window.location.href ='logoff.php';";die();}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.system.network.inc');
	include_once('ressources/class.freeweb.inc');
	include_once('ressources/class.awstats.inc');
	include_once('ressources/class.pdns.inc');
	
	

	$user=new usersMenus();
	if($user->AsWebMaster==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["mod-status-list"])){mod_status_list();exit;}
	if(isset($_POST["mod-ip-add"])){mod_status_ip_add();exit;}
	if(isset($_POST["mod-ip-del"])){mod_status_ip_del();exit;}
	js();
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{status}");
	echo "YahooWin2('355','$page?popup=yes','FreeWebs $title')";
	
	
}

function popup(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$apache_mod_status_add_explain=$tpl->javascript_parse_text("{apache_mod_status_add_explain}");
	$html="<div class=explain style='margin-bottom:10px'>{apache_mod_status_allow_explain}</div>
	<div id='mod-status-list' style='height:250px;overflow:auto'></div>
	
	<script>
		function mod_status_load(){
			LoadAjax('mod-status-list','$page?mod-status-list=yes');
		}
		
	var x_mod_status_add=function (obj) {
			var results=obj.responseText;
			if(results.length>3){alert(results);}			
			mod_status_load();
		}			
		
		function mod_status_add(){
			var ip=prompt('$apache_mod_status_add_explain');
			if(ip){
				var XHR = new XHRConnection();
				XHR.appendData('mod-ip-add',ip);
				AnimateDiv('mod-status-list');
				XHR.sendAndLoad('$page', 'POST',x_mod_status_add);			
			}
		}
		
		
	mod_status_load();
	</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
}

function mod_status_ip_add(){
	$sock=new sockets();
	$hash=unserialize(base64_decode($sock->GET_INFO("FreeWedMoStatusAllowFrom")));
	$hash[$_POST["mod-ip-add"]]=$_POST["mod-ip-add"];
	$sock->SaveConfigFile(base64_encode(serialize($hash)), "FreeWedMoStatusAllowFrom");
	$sock->getFrameWork("freeweb.php?reconfigure=yes");
}

function mod_status_ip_del(){
	$sock=new sockets();
	$hash=unserialize(base64_decode($sock->GET_INFO("FreeWedMoStatusAllowFrom")));
	unset($hash[$_POST["mod-ip-del"]]);
	$sock->SaveConfigFile(base64_encode(serialize($hash)), "FreeWedMoStatusAllowFrom");
	$sock->getFrameWork("freeweb.php?reconfigure=yes");
}

function mod_status_list(){
	$sock=new sockets();
	$tpl=new templates();
	$page=CurrentPageName();
	$hash=unserialize(base64_decode($sock->GET_INFO("FreeWedMoStatusAllowFrom")));
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th width=1%>". imgtootltip("plus-24.png","{add}","mod_status_add()")."</th>
	<th>{ipaddr}</th>
	<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	if(is_array($hash)){
		while (list ($num, $ligne) = each ($hash) ){
			
			if($ligne==null){continue;}
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}	
			
		$html=$html."
			<tr class=$classtr>
			<td width=1%><img src='img/folder-network-32.png'></td>
			<td nowrap><strong style='font-size:14px'>$ligne</strong></td>
			<td width=1%>". imgtootltip("delete-32.png","{delete}","mod_status_del('$ligne')")."</td>
			</tr>
			";	
		}
	}

		$html=$html."</tbody></table>
		<script>
		function mod_status_del(ip){
			var XHR = new XHRConnection();
			XHR.appendData('mod-ip-del',ip);
			AnimateDiv('mod-status-list');
			XHR.sendAndLoad('$page', 'POST',x_mod_status_add);
		}		
		</script>
		";
	echo $tpl->_ENGINE_parse_body($html);	
	
}
