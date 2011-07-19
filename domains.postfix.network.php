<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.main_cf.inc');
	include_once('ressources/class.tcpip.inc');
	include_once('ressources/class.system.network.inc');	
	include_once('ressources/class.postfix-multi.inc');
	
$users=new usersMenus();
$tpl=new templates();
if(!$users->AsOrgPostfixAdministrator){
		echo $tpl->javascript_parse_text("alert('{ERROR_NO_PRIVS}');");
		die();
	}

if(isset($_GET["popup"])){popup();exit;}	
if(isset($_GET["ReloadNetworkTable"])){echo mynetworks_table();exit;}
if(isset($_GET["mynet_ipfrom"])){CalculCDR();exit;}
if(isset($_GET["PostfixAddMyNetwork"])){PostfixAddMyNetwork();exit;}
if(isset($_GET["PostFixDeleteMyNetwork"])){PostFixDeleteMyNetwork();exit;}


js();



function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{mynetworks_title}");
	$html="
		function ORG_NET_POSTFIX_START(){
			YahooWin3('650','$page?popup=yes&ou={$_GET["ou"]}','$title');
		}
	
	ORG_NET_POSTFIX_START()";
	
	echo $html;
	
	
	
}

function popup(){
		$_GET["ou"]=base64_decode($_GET["ou"]);
		$page=CurrentPageName();
		$main=new main_multi($_GET["ou"],1);
		
		
$html="
<table style='width:90%' align='center'>
	<tr>
	<td align='right' valign='top' nowrap class=legend style='font-size:13px'><strong style='font-size:13px'>{hostname}&nbsp;:</strong></td>
	<td align='left'>" . Field_text('hostname',$main->MyHostname(),'width:80%;font-size:13px;padding:4px',null,null,'{hostname}') ."</td>
	</tr>
</table><hr>

<span style='font-size:12px;font-weight:bold'>{$_GET["ou"]}:{mynetworks_title}</span>
	<table style='width:90%' align='center'>
	<tr>
	<td align='right' valign='top' nowrap class=legend>{give the new network}&nbsp;:</strong></td>
	<td align='left'>" . Field_text('mynetworks',null,'width:80%',null,null,'{mynetworks_text}') ."</td>
	</tr>
	<tr>
	<td align='right' valign='top' nowrap class=legend>{or} {give_ip_from_ip_to}&nbsp;:</strong></td>
	<td align='left'>" . Field_text('ipfrom',null,'width:100px',null,'PostfixCalculateMyNetwork()') . Field_text('ipto',null,'width:100px',null,'PostfixCalculateMyNetwork()') ."</td>
	</tr>
	
	<tr><td colspan=2 align='right'>
		<hr>
		". button("{add}","PostfixAddMyNetwork()")."
	</td>
	</tr>
	</table>	
	<div id='network_table' style='padding:10px'>$mynetworks_table</div>
	
	<script>
	
		var x_ReloadNetworkTable= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			ReloadNetworkTable();
			}	
				
	function PostfixAddMyNetwork(){
		PostfixCalculateMyNetwork();
		var XHR = new XHRConnection();
		XHR.appendData('PostfixAddMyNetwork',document.getElementById('mynetworks').value);
		XHR.appendData('ou','{$_GET["ou"]}');
		XHR.appendData('hostname',document.getElementById('hostname').value);
		
		
		document.getElementById('network_table').innerHTML=\"<center style='width:100%'><img src='img/wait_verybig.gif'></center>\";
		XHR.sendAndLoad('$page', 'GET',x_ReloadNetworkTable);
	}	
	
		function ReloadNetworkTable(){
			LoadAjax('network_table','$page?ReloadNetworkTable=yes&ou={$_GET["ou"]}');
			}
			
	var x_PostfixCalculateMyNetwork= function (obj) {
		var results=obj.responseText;
		document.getElementById('mynetworks').value=trim(results);
	}


	function PostfixCalculateMyNetwork(){
		if(!document.getElementById('ipfrom')){return false;}
		var ipfrom=document.getElementById('ipfrom').value;
		var ipto=document.getElementById('ipto').value;
		
		if(ipfrom.length>0){
			var ARRAY=ipfrom.split('\.');
			if(ARRAY.length>3){
				if(ipto.length==0){
					document.getElementById('ipto').value=ARRAY[0] + '.' + ARRAY[1] + '.'+ARRAY[2] + '.255';
					
					}
					}else{return false}
		}else{return false;}
		document.getElementById('ipfrom').value=ARRAY[0] + '.' + ARRAY[1] + '.'+ARRAY[2] + '.0';
		ipfrom=ARRAY[0] + '.' + ARRAY[1] + '.'+ARRAY[2] + '.0';
		var XHR = new XHRConnection();
		XHR.appendData('mynet_ipfrom',ipfrom);
		XHR.appendData('mynet_ipto',document.getElementById('ipto').value);
		XHR.sendAndLoad('$page', 'GET',x_PostfixCalculateMyNetwork);
		}	

	function PostFixDeleteMyNetwork(num){
		var XHR = new XHRConnection();
		XHR.appendData('PostFixDeleteMyNetwork',num);
		document.getElementById('network_table').innerHTML=\"<center style='width:100%'><img src='img/wait_verybig.gif'></center>\";
		XHR.sendAndLoad('$page', 'GET',x_ReloadNetworkTable);
		}		
			
	
	ReloadNetworkTable();
	</script>
	
	";
$tpl=new templates();
if($noecho==1){return $tpl->_ENGINE_parse_body($html);}

echo $tpl->_ENGINE_parse_body($html);
}

function mynetworks_table(){
	
	$sql="SELECT * FROM postfix_multi WHERE `key`='mynetworks' AND `ou`='{$_GET["ou"]}' ORDER BY ID DESC;";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	
	$style=CellRollOver();
	$html=$html . "
		<center>
		<table class=table_form align='center' style='width:99%'>";
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
		if(trim($ligne["value"])==null){continue;}
		$html=$html . "
		<tr ". CellRollOver().">
			<td width=1%><img src='img/network-1.gif'></td>
			<td style='font-size:13px'>{$ligne["value"]}</td>
			<td  width=1%>" . imgtootltip('x.gif','{delete} {network}',"PostFixDeleteMyNetwork({$ligne["ID"]})") ."</td>
		</tr>";
		}
	
	
	$html=$html . "</table>
	</center>";

	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);	
}
function CalculCDR(){
	$ip=new IP();
	$ipfrom=$_GET["mynet_ipfrom"];
	$ipto=$_GET["mynet_ipto"];
	$SIP=$ip->ip2cidr($ipfrom,$ipto);
	echo trim($SIP);
	}

function PostfixAddMyNetwork(){
	$q=new mysql();
	if($_GET["PostfixAddMyNetwork"]<>null){
		$sql="INSERT INTO postfix_multi (`ou`,`key`,`value`) VALUES('{$_GET["ou"]}','mynetworks','{$_GET["PostfixAddMyNetwork"]}')";
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){
			echo $q->mysql_error."\n".$sql;
			return ;
		}
	}
	
	$sql="SELECT `value` FROM postfix_multi WHERE `key`='myhostname' AND `ou`='{$_GET["ou"]}' LIMIT 0,1;";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
	if($ligne["value"]==null){
		$sql="INSERT INTO postfix_multi  (`ou`,`key`,`value`) VALUES('{$_GET["ou"]}','myhostname','{$_GET["hostname"]}')";
	}else{
		$sql="UPDATE postfix_multi SET `value`='{$_GET["hostname"]}' WHERE `key`='myhostname' AND `ou`='{$_GET["ou"]}'";
	}
	
$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){
			echo $q->mysql_error."\n".$sql;
			return ;
		}	
		
		
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-multi-configure-hostname={$_GET["hostname"]}");	
	
	}
function PostFixDeleteMyNetwork(){
	$sql="DELETE FROM postfix_multi WHERE ID={$_GET["PostFixDeleteMyNetwork"]}";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");	
	if(!$q->ok){
		echo $q->mysql_error."\n".$sql;
		return ;
	}	
	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-multi-configure-hostname={$_GET["hostname"]}");
	
}	


?>