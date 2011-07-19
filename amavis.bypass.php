<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.amavis.inc');
	$user=new usersMenus();
	if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["add-ip"])){add_ip();exit;}
	if(isset($_GET["ip-list"])){ip_list();exit;}
	if(isset($_GET["del-ip"])){ip_del();exit;}
page();


//EnableAmavisInMasterCF
function page(){
	$page=CurrentPageName();
	$tpl=new templates();
	$check_client_access_ip_explain=$tpl->javascript_parse_text("{check_client_access_ip_explain}");
	
	$html="<div class=explain>{amavis_bypass_servers_explain}</div>
	
	<center>". button("{add}","AddAmavisBypass()")."</center>
	<hr>
	<div id='amavisbypassList' style='width:100%;height:350px;overflow:auto'></div>
	
	
	
	<script>
	
	function RefreshAmavisdByPass(){
		LoadAjax('amavisbypassList','$page?ip-list=yes');
	
	}
	
	
	var x_AddAmavisBypass= function (obj) {
		var response=obj.responseText;
		if(response){alert(response);}
	   	  RefreshAmavisdByPass();  
		}		
	
		function AddAmavisBypass(){
			var ip=prompt('$check_client_access_ip_explain');
			if(ip){
				var XHR = new XHRConnection();
				XHR.appendData('add-ip',ip);
				document.getElementById('amavisbypassList').innerHTML='<img src=\"img/wait_verybig.gif\">';
				XHR.sendAndLoad('$page', 'GET',x_AddAmavisBypass);	
			}
		}
		
		function DeleteAmavisBypass(ip){
			var XHR = new XHRConnection();
			XHR.appendData('del-ip',ip);
			document.getElementById('amavisbypassList').innerHTML='<img src=\"img/wait_verybig.gif\">';
			XHR.sendAndLoad('$page', 'GET',x_AddAmavisBypass);	
		}

		RefreshAmavisdByPass();	
	
	</script>";
	
	
	echo $tpl->_ENGINE_parse_body($html);
	
}


function add_ip(){
	$sql="INSERT INTO amavisd_bypass (`ip_addr`) VALUES('{$_GET["add-ip"]}')";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-smtp-sender-restrictions=master");
	$sock->getFrameWork("cmd.php?amavis-restart=yes");
}
function ip_del(){
	$sql="DELETE FROM amavisd_bypass WHERE `ip_addr`='{$_GET["del-ip"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;}	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-smtp-sender-restrictions=master");
	$sock->getFrameWork("cmd.php?amavis-restart=yes");
}
function ip_list(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$sock=new sockets();
	$EnableAmavisInMasterCF=$sock->GET_INFO("EnableAmavisInMasterCF");

	
	$html="<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th>{ip_addr}</th>
		<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	
	$sql="SELECT * FROM amavisd_bypass ORDER BY ip_addr";
	

	
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		if(preg_match("#doesn't exist#",$q->mysql_error)){$q->BuildTables();$results=$q->QUERY_SQL($sql,"artica_backup");}
		if(!$q->ok){
			echo $q->mysql_error;return;
		}
	}	
	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}	
			$delete=imgtootltip("delete-32.png","{delete}","DeleteAmavisBypass('{$ligne["ip_addr"]}')");
			$color="black";
			if($EnableAmavisInMasterCF<>1){$color="#CCCCCC";}
			
			
		$html=$html . "
		<tr  class=$classtr>
		<td width=99% style='font-size:16px;color:$color' nowrap>{$ligne["ip_addr"]}</td>
		<td width=1%>$delete</td>
		</tr>";

	}
	
	$html=$html."</table>";
	echo $tpl->_ENGINE_parse_body($html);
		
	
	
}
