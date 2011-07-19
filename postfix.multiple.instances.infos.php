<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	
	
	
	if(posix_getuid()==0){die();}
	
	$user=new usersMenus();
	if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["popup-list"])){getlist();exit;}
	if(isset($_GET["delete"])){DELETE();exit;}
	
js();



function js(){
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{POSTFIX_MULTI_INSTANCE_INFOS}");
	$ask_perform_operation_delete_item=$tpl->_ENGINE_parse_body("{ask_perform_operation_delete_item}");
	$page=CurrentPageName();
	$start="POSTFIX_MULTI_INSTANCE_INFOS_START()";
	$html="
	function POSTFIX_MULTI_INSTANCE_INFOS_START(){
		YahooWin('550','$page?popup=yes','$title');
	}
	
	function POSTFIX_MULTI_INSTANCE_INFOS_LIST(){
		LoadAjax('multiples-instances-list','$page?popup-list=yes');
	}
	
	var X_POSTFIX_MULTI_INSTANCE_INFOS_DEL= function (obj) {
	 var results=obj.responseText;
	 if(results.length>1){alert(results);}
	 POSTFIX_MULTI_INSTANCE_INFOS_LIST();
	}	
	
	function POSTFIX_MULTI_INSTANCE_INFOS_DEL(ou,ip){
		if(confirm('$ask_perform_operation_delete_item\\n'+ou+'('+ip+')')){
				var XHR = new XHRConnection();
				XHR.appendData('delete','yes');
				XHR.appendData('ou',ou);
				XHR.appendData('ip',ip);
				document.getElementById('multiples-instances-list').innerHTML='<center><img src=img/wait_verybig.gif></center>';
				XHR.sendAndLoad('$page', 'GET',X_POSTFIX_MULTI_INSTANCE_INFOS_DEL);	
		}
	
	}
	
	$start;
	";
	
	echo $html;
	
}

function DELETE(){
	$sql="DELETE FROM postfix_multi WHERE ou='{$_GET["ou"]}' AND ip_address='{$_GET["ip"]}'";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");	
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-multi-configure-ou={$_GET["ou"]}");
}


function popup(){
	
	
	$html="<div class=explain>{POSTFIX_MULTI_INSTANCE_INFOS_TEXT}</div>
	<div id='multiples-instances-list'></div>
	<script>
		POSTFIX_MULTI_INSTANCE_INFOS_LIST();
	</script>
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function getlist(){
	
	$sql="SELECT ou, ip_address, `key` , `value` FROM postfix_multi WHERE `key` = 'myhostname'";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th>&nbsp;</th>
		<th>{servername}</th>
		<th>{organization}</th>
		<th>{ip_address}</th>
		<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>	
	";
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	$html=$html."
	<tr  class=$classtr>
	<td style='font-size:14px;font-weight:bold'><img src=img/fw_bold.gif></td>
	<td style='font-size:14px;font-weight:bold'>{$ligne["value"]}</td>
	<td style='font-size:14px;font-weight:bold'>{$ligne["ou"]}</td>
	<td style='font-size:14px;font-weight:bold'>{$ligne["ip_address"]}</td>
	<td>". imgtootltip("delete-24.png","{delete}","POSTFIX_MULTI_INSTANCE_INFOS_DEL('{$ligne["ou"]}','{$ligne["ip_address"]}')")."</td>
	</tR>
		
	";
	}
	$html=$html."</table>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
		
		
}
	
	




?>