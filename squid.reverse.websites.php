<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.groups.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.system.network.inc');
	
	
	$user=new usersMenus();
	if($user->AsSquidAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}

	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["SquidActHasReverse"])){Save();exit;}
	if(isset($_GET["website"])){add_website();exit;}
	if(isset($_GET["websites-list"])){websites_list();exit;}
	if(isset($_GET["AccelAddReverseSiteDelete"])){del_website();exit;}
js();


function js(){
	
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{squid_reverse_proxy}");
	$page=CurrentPageName();
	$html="
		function squid_reverse_websites_proxy_load(){
			YahooWin3('600','$page?popup=yes','$title');
		
		}
		
		var x_AccelAddReverseSite= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			LoadAjax('www_accel_list','$page?websites-list=yes');
		}		
		
		function AccelAddReverseSite(){
		 	var XHR = new XHRConnection();
			XHR.appendData('website',document.getElementById('website').value);
			XHR.appendData('website_ip',document.getElementById('website_ip').value);
			XHR.appendData('website_port',document.getElementById('website_port').value);
			AnimateDiv('www_accel_list');	
			XHR.sendAndLoad('$page', 'GET',x_AccelAddReverseSite);	
		}
		
		function AccelAddReverseSiteDelete(ID){
			var XHR = new XHRConnection();
			XHR.appendData('AccelAddReverseSiteDelete',ID);
			document.getElementById('www_accel_list').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';	
			XHR.sendAndLoad('$page', 'GET',x_AccelAddReverseSite);				
		}
			
		
	squid_reverse_websites_proxy_load();";
	
	echo $html;
	
}



function popup(){
	
	$page=CurrentPageName();
	$html="
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:13px' nowrap>{website}:</td>
		<td width=99%>". Field_text("website",null,"font-size:13px;padding:3px;width:100%")."</td>
		<td class=legend>{example}:www.mydomain.tld</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px' nowrap>{ip_address}:</td>
		<td width=99%>". Field_text("website_ip",null,"font-size:13px;padding:3px;width:120px")."</td>
		<td class=legend>{example}:192.168.1.24</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px' nowrap>{listen_port}:</td>
		<td width=99%>". Field_text("website_port",80,"font-size:13px;padding:3px;width:90px")."</td>
		<td class=legend>{example}:80</td>
	</tr>	
	<tr>
		<td colspan=3 align='right'><hr>". button("{add}","AccelAddReverseSite()")."</td>
	</tr>	
	</table>
	<div id='www_accel_list' style='height:250px;overflow:auto'></div>
	
	<script>LoadAjax('www_accel_list','$page?websites-list=yes');</script>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function add_website(){
	
	$sql="INSERT INTO squid_accel (website_name,website_ip,website_port) VALUES('{$_GET["website"]}','{$_GET["website_ip"]}','{$_GET["website_port"]}')";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?squidnewbee=yes");	
}

function websites_list(){
	
	
	$html="<table style='width:90%;margin:5px;padding:5px;border:1px dotted #CCCCCC'>
	<tr>
	<th colspan=2>{website}</th>
	<th>{ip_address}</th>
	<th>&nbsp;</th>
	</tr>";
	
	$sql="SELECT * FROM squid_accel ORDER BY ID DESC";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
		$html=$html."
		<tr ". CellRollOver().">
			<td width=1%><img src='img/website-32.png'></td>
			<td style='font-size:14px' width=99%><strong>{$ligne["website_name"]}:{$ligne["website_port"]}</strong></td>
			<td style='font-size:14px' width=1%><strong>{$ligne["website_ip"]}</strong></td>
			<td width=1%>". imgtootltip("delete-32.png","{delete}","AccelAddReverseSiteDelete({$ligne["ID"]})"). "</td>
		</tr>
		";	
	}
	
	$html=$html."</table>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	}
	
function del_website(){
	
	$sql="DELETE FROM squid_accel WHERE ID={$_GET["AccelAddReverseSiteDelete"]}";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?squidnewbee=yes");		
}

?>