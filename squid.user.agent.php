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
	if(isset($_GET["useragent-masters"])){field_array_agents();exit;}
	if(isset($_GET["useragent-list"])){agents_list();exit;}
	if(isset($_GET["UserAgentToAdd"])){add();exit;}
	if(isset($_GET["UserAgentDelete"])){del();exit;}
	
js();


function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$useragent_database=$tpl->_ENGINE_parse_body("{useragent_database}");
	$html="
		YahooWin3('680','$page?popup=yes','$useragent_database');
	
	";
		
	echo $html;
	
}


function popup(){
	$page=CurrentPageName();
	$html="
	<div class=explain>{useragent_database_explain}</div>
	

	<table style='width:100%'>
		<tr>
			<td valign='top' class=legend style='font-size:15px' width=99% nowrap>{add}&nbsp;&nbsp;{useragent}:</td>
			<td valign='top' class=legend width=1% align='left'>". Field_text("UserAgentToAdd",null,"font-size:13px;padding:3px;width:220px")."</td>
		</tr>
		<tr>
		<td colspan=2><table style='width:100%'>
		<tr>	
			<td valign='top' class=legend style='font-size:15px' width=1% nowrap>{string}:</td>
			<td valign='top' class=legend width=99% align='left'>". Field_text("StringToAdd",null,"font-size:13px;padding:3px;width:500px")."</td>
			<td width=1%>". button("{add}","UserAgentAdd()")."</td></tr>
		</tr>
		</table>
			
		</tr>
				
	</table>	
	<hr>
		<table style='width:100%'>
		<tr>
			<td valign='top' class=legend style='font-size:15px' width=99% nowrap>{useragent}:</td>
			<td valign='top' class=legend width=1% align='left'><span id='useragent-masters'></span></td>
		</tr>
	</table>
	
	<div id='useragentslist' style='height:300px;overflow:auto;margin-top:8px;width:100%'></div>
	
	
	<script>
		function RefreshUseragentMasters(){
			var select='';
			if(document.getElementById('UserAgentSelect')){
				select=escape(document.getElementById('UserAgentSelect').value);
			}
			LoadAjax('useragent-masters','$page?useragent-masters=yes&selected='+select);
		}

		function DisplayUserAgentList(){
			LoadAjax('useragentslist','$page?useragent-list='+escape(document.getElementById('UserAgentSelect').value));
		}
		
	var x_UserAgentAdd=function(obj){
     		var tempvalue=obj.responseText;
      		if(tempvalue.length>3){alert(tempvalue);}
			RefreshUseragentMasters();
		}		
		
		function UserAgentAdd(){
		var XHR = new XHRConnection();
		XHR.appendData('UserAgentToAdd',document.getElementById('UserAgentToAdd').value);
		XHR.appendData('StringToAdd',document.getElementById('StringToAdd').value);
		document.getElementById('useragentslist').innerHTML='<center style=\"width:100%\"><img src=img/wait.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_UserAgentAdd);		
		}
		
		function UserAgentDelete(key){
			var XHR = new XHRConnection();
			XHR.appendData('UserAgentDelete',key);
			document.getElementById('useragentslist').innerHTML='<center style=\"width:100%\"><img src=img/wait.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_UserAgentAdd);			
		}
		

	RefreshUseragentMasters();
		
	</script>
	";
		
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body($html);
	
}

function field_array_agents(){
	$sql="SELECT browser FROM `UserAgents` GROUP BY browser ORDER BY browser";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$array[$ligne["browser"]]=$ligne["browser"];
	}
	$array[null]="{select}";
	ksort($array);
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body(Field_array_Hash($array,"UserAgentSelect",$_GET["selected"],"DisplayUserAgentList()",null,0,"font-size:14px;padding:3px"))."
		<script>
		DisplayUserAgentList();	
	</script>";
	
	
}


function agents_list(){
	if($_GET["useragent-list"]==null){
		$sql="SELECT unique_key,`string` FROM `UserAgents` ORDER BY string LIMIT 0,30";	
	}else{
		$sql="SELECT unique_key,`string` FROM `UserAgents` WHERE browser='{$_GET["useragent-list"]}' ORDER BY string";
	}
	
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	$html="<table class=tableView style='width:99%'>
				<thead class=thead>
				<tr>
					<th width=1% nowrap colspan=3>{$_GET["useragent-list"]}</td>
				</tr>
				</thead>";
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){	
	if($cl=="oddRow"){$cl=null;}else{$cl="oddRow";}
		$html=$html."<tr class=$cl>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><code>{$ligne["string"]}</code></td>
		<td width=1%>". imgtootltip("ed_delete.gif","{delete}","UserAgentDelete('{$ligne["unique_key"]}')")."</td>
		</tr>
		";
		
	}
	
	$html=$html."</table>
	

	";
	echo $html;
	
}

function add(){
	$unique_key=md5($_GET["StringToAdd"]);
	$prodct=$_GET["UserAgentToAdd"];
	$string=$_GET["StringToAdd"];
	$sql="INSERT INTO UserAgents(unique_key,browser,string) VALUES('$unique_key','$prodct','$string')";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
}

function del(){
	$sql="DELETE FROM UserAgents WHERE unique_key='{$_GET["UserAgentDelete"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}	
}
	
	

?>