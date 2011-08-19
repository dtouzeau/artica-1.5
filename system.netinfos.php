<?php

	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.tcpip.inc');
	include_once('ressources/class.system.network.inc');
	include_once('ressources/class.main_cf.inc');
	include_once('ressources/class.mysql.inc');
	
	
$usersmenus=new usersMenus();
if($usersmenus->AsPostfixAdministrator==false){die();exit;}
if(isset($_POST["TEXTIPADDRINFOS"])){save();exit;}

page();


function page(){
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql();
	$sql="SELECT netinfos FROM networks_infos WHERE ipaddr='{$_GET["ipaddr"]}'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	
	$html="
	<div style='font-size:16px' id='ssnetinfos'>{$_GET["ipaddr"]}</div>
	<textarea id='TEXTIPADDRINFOS' style='font-size:14px;height:150px;overflow:auto;width:100%' \">{$ligne["netinfos"]}</textarea>
	<div style='text-align:right;margin:10px'>". button("{apply}", "SaveGenNEtInfos()")."</div>
	
	<script>
	var x_SaveGenNEtInfos= function (obj) {
			var results=obj.responseText;
			if(results.length>3){alert(results);}
			if(document.getElementById('main_config_postfix_net')){RefreshTab('main_config_postfix_net');}
			if(document.getElementById('main_config_postfix')){RefreshTab('main_config_postfix');}
			if(document.getElementById('netlist')){LoadAjax('netlist','computer-browse.php?networkslist=yes');}
			RTMMailHide();
			}	
	
	
	
			
		function SaveGenNEtInfos(){
			var XHR = new XHRConnection();
			XHR.appendData('ipaddr','{$_GET["ipaddr"]}');
			XHR.appendData('TEXTIPADDRINFOS',document.getElementById('TEXTIPADDRINFOS').value);
			AnimateDiv('ssnetinfos');
			XHR.sendAndLoad('$page', 'POST',x_SaveGenNEtInfos);			
		}
	</script>	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
	
}


function save(){
	$q=new mysql();
	$sql="SELECT ipaddr,netinfos FROM networks_infos WHERE ipaddr='{$_POST["ipaddr"]}'";
	$_POST["TEXTIPADDRINFOS"]=utf8_encode(addslashes($_POST["TEXTIPADDRINFOS"]));
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	if($ligne["ipaddr"]==null){
		$sql="INSERT INTO networks_infos(ipaddr,netinfos) VALUES ('{$_POST["ipaddr"]}','{$_POST["TEXTIPADDRINFOS"]}')";
	}else{
		$sql="UPDATE networks_infos SET netinfos='{$_POST["TEXTIPADDRINFOS"]}' WHERE ipaddr='{$_POST["ipaddr"]}'";
	}
	
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;}
	
	
	
}
