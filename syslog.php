<?php
session_start ();
include_once ('ressources/class.templates.inc');
include_once ('ressources/class.ldap.inc');
include_once ('ressources/class.users.menus.inc');


	$user=new usersMenus();
	if(!$user->AsSystemAdministrator){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["search"])){search();exit;}

js();


function js(){
	$page=CurrentPageName();
	$html="
	
	
function SyslogLoadpage(){
	$('#BodyContent').load('$page?popup=yes');
	}
	
	
	SyslogLoadpage();";
	
	echo $html;
	
	
}


function popup(){
	$page=CurrentPageName();
	$html="
	<table style='width:100%'>
	<tr>
		<td class=legend valign='middle'>{search}:</td>
		<td>". Field_text("syslog-search",null,"font-size:13px;padding:3px;",null,null,null,false,"SyslogSearchPress(event)")."</td>
		<td align='right' width=1%>". imgtootltip("32-refresh.png","{refresh}","SyslogRefresh()")."</td>
	</tr>
	</table>
	
	<div style='widht:99%;height:600px;overflow:auto;margin:5px' id='syslog-table'></div>
	<script>
		function SyslogSearchPress(e){
			if(checkEnter(e)){SearchSyslog();}
		}
	
	
		function SearchSyslog(){
			var pat=escape(document.getElementById('syslog-search').value);
			LoadAjax('syslog-table','$page?search='+pat);
		
		}
		
		function SyslogRefresh(){
			SearchSyslog();
		}
	
	SearchSyslog();
	</script>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function search(){
	
	$pattern=base64_encode($_GET["search"]);
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->getFrameWork("cmd.php?syslog-query=$pattern")));
	if(!is_array($array)){return null;}
	
	$html="<table class=TableView>";
	
	while (list ($key, $line) = each ($array) ){
		if($line==null){continue;}
		if($tr=="class=oddrow"){$tr=null;}else{$tr="class=oddrow";}
		
			$html=$html."
			<tr $tr>
			<td><code>$line</cod>
			</tr>
		
		";
		
	}
	
	
	$html=$html."</table>";

	echo $html;
}




?>