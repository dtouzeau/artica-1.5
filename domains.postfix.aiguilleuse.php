<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.maincf.multi.inc');
	
	
	
	if(!PostFixMultiVerifyRights()){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["PostFixEnableAiguilleuse"])){save_enable();exit;}
	if(isset($_GET["PostFixEnableAiguilleuse-list"])){routing_list();exit;}	
	if(isset($_GET["PostFixEnableAiguilleuseAdd"])){PostFixEnableAiguilleuseAdd();exit;}
	if(isset($_GET["PostFixAiguilleuseServerDelete"])){del_host();exit;}
	if(isset($_GET["PostFixEnableAiguilleuse-insert"])){add_host();exit;}
	
	
	
	
js();


function js(){
	
$page=CurrentPageName();
$users=new usersMenus();
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{InternalRouter}');

$html="

function InternalRouter_load(){
	YahooWin4('650','$page?popup=yes&ou={$_GET["ou"]}&hostname={$_GET["hostname"]}','$title');
	
	}
	
function PostFixEnableAiguilleuseAdd(){
	YahooWin3('400','$page?PostFixEnableAiguilleuseAdd=yes&ou={$_GET["ou"]}&hostname={$_GET["hostname"]}','$title');
}
	
InternalRouter_load();
";


echo $html;	
	
}

function save_enable(){
	$ou=base64_decode($_GET["ou"]);
	$main=new maincf_multi($_GET["hostname"],$ou);
	$page=CurrentPageName();
	$PostFixEnableAiguilleuse=$main->SET_VALUE("PostFixEnableAiguilleuse",$_GET["PostFixEnableAiguilleuse"]);	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-multi-mastercf={$_GET["hostname"]}");
	$sock->getFrameWork("cmd.php?postfix-multi-aiguilleuse={$_GET["hostname"]}");
}

function popup(){
	
	$ou=base64_decode($_GET["ou"]);
	$main=new maincf_multi($_GET["hostname"],$ou);
	$page=CurrentPageName();
	$tpl=new templates();
	$PostFixEnableAiguilleuse=$main->GET("PostFixEnableAiguilleuse");
	$enable=Paragraphe_switch_img("{PostFixEnableAiguilleuse}","{PostFixEnableAiguilleuse_text}",
	"PostFixEnableAiguilleuse",$PostFixEnableAiguilleuse,null,350);
	
	
$html="
<table style='width:100%'>
<td valign='top' width=1%>
<center>
	<img src='img/128-nodes.png' id='128-nodes'>
</center>
		<table style='width:100%;margin-top:10px;padding:10px;width:220px'>
		<tr ". CellRollOver("PostFixEnableAiguilleuseAdd()").">
			<td valign=top width=1% style='padding:5px'><img src='img/48-network-server-add.png'></td>
			<td valign='top' style='padding:5px'>
			<div>
				<strong style='font-size:13px'>{add_mail_server}</strong>
			</div>
			<div><i style='font-size:11px'>{postfix_multi_add_aiguilleuse_server}</i></div>
			</td>
			</tr>
		</table>
</td>
<td valign='top' width=99%>
$enable
<div style='margin:12px;text-align:right'>	
	". button("{apply}","PostFixEnableAiguilleuseSave()")."<hr>
</div>
<div id='PostFixEnableAiguilleuse-list' style='height:250px;width:100%;overflow:auto'>	



<script>

var x_PostFixEnableAiguilleuseSave= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	document.getElementById('PostFixEnableAiguilleuse').src='img/128-nodes.png';
	}
	
	
	function PostFixEnableAiguilleuseSave(){
		var XHR = new XHRConnection();
		XHR.appendData('PostFixEnableAiguilleuse',document.getElementById('PostFixEnableAiguilleuse').value);
		XHR.appendData('ou','{$_GET["ou"]}');
		XHR.appendData('hostname','{$_GET["hostname"]}');
		document.getElementById('PostFixEnableAiguilleuse').src='img/img/wait_verybig.gif';
		XHR.sendAndLoad('$page', 'GET',x_PostFixEnableAiguilleuseSave);
	
	}

	function RefreshPostfixAiguilleuseList(){
		LoadAjax('PostFixEnableAiguilleuse-list','$page?PostFixEnableAiguilleuse-list=yes&ou={$_GET["ou"]}&hostname={$_GET["hostname"]}');
	}
	
	RefreshPostfixAiguilleuseList();
</script>
";	

echo $tpl->_ENGINE_parse_body($html);
}

function routing_list(){
	$ou=base64_decode($_GET["ou"]);
	$main=new maincf_multi($_GET["hostname"],$ou);
	$page=CurrentPageName();
	$tpl=new templates();
	$PostFixAiguilleuseServers=unserialize(base64_decode($main->GET_BIGDATA("PostFixAiguilleuseServers")));
	$html="
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
		<thead class='thead'>
			<tr>
				<th colspan=3>{routed_servers}</th>
			</tr>
		</thead>
		<tbody class='tbody'>";	

if(is_array($PostFixAiguilleuseServers["HOSTS"])){
			while (list ($hosts, $null) = each ($PostFixAiguilleuseServers["HOSTS"]) ){
				if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
				if(trim($hosts)==null){continue;}
				$hosts_enc=base64_encode($hosts);
				$html=$html . "
				<tr class=$classtr>
					<td width=1%><img src='img/32-network-server.png'></td>
					<td style='font-size:16px'>$hosts</td>
					<td  width=1%>" . imgtootltip('delete-32.png',"{delete} $hosts","PostFixAiguilleuseServerDelete('$hosts_enc')") ."</td>
				</tr>";
			}
		}
	
	$html=$html . "
	</tbody>
	</table>
	
	<script>
	
	var x_PostFixAiguilleuseServerDelete= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		document.getElementById('PostFixEnableAiguilleuse').src='img/128-nodes.png';
		RefreshPostfixAiguilleuseList();
	}
	
	
	function PostFixAiguilleuseServerDelete(host){
		var XHR = new XHRConnection();
		XHR.appendData('PostFixAiguilleuseServerDelete',host);
		XHR.appendData('ou','{$_GET["ou"]}');
		XHR.appendData('hostname','{$_GET["hostname"]}');
		document.getElementById('PostFixEnableAiguilleuse').src='img/img/wait_verybig.gif';
		XHR.sendAndLoad('$page', 'GET',x_PostFixAiguilleuseServerDelete);
	
	}	
	</script>
	
	";
	echo $tpl->_ENGINE_parse_body($html);
}

function PostFixEnableAiguilleuseAdd(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$ou=base64_decode($_GET["ou"]);
	$main=new maincf_multi($_GET["hostname"],$ou);
	$PostFixAiguilleuseServers=unserialize(base64_decode($main->GET_BIGDATA("PostFixAiguilleuseServers")));
	$sql="SELECT `value` FROM `postfix_multi` WHERE ou='$ou' AND `key`='myhostname'";
	
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,'artica_backup');
	$num=mysql_num_rows($results);
	
	$html="<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th colspan=3>($num) {instances}</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	if(!$q->ok){echo "<H5>$q->mysql_error</H5>";}
	$count=0;
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
				if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
				if(trim($ligne["value"])==null){continue;}
				if($PostFixAiguilleuseServers["HOSTS"][trim(strtolower($ligne["value"]))]){continue;}
				if(trim(strtolower($ligne["value"]))==trim(strtolower($_GET["hostname"]))){continue;}
				$count++;
				$html=$html . "
				<tr class=$classtr>
					<td width=1%><img src='img/32-network-server.png'></td>
					<td style='font-size:16px'>{$ligne["value"]}</td>
					<td  width=1%>" . imgtootltip('plus-24.png',"{add} {$ligne["value"]}",
					"PostFixEnableAiguilleuseInsert('{$ligne["value"]}')") ."</td>
				</tr>";
			}
	if($count==0){
		$html=$html . "
				<tr class=$classtr>
				<td colspan=3 style='font-size:16px'>{error_no_server_specified}</td>
				</tr>";
	}
	
		
	
	$html=$html . "
	</tbody>
	</table>
	
	<script>
var x_PostFixEnableAiguilleuseInsert= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	RefreshPostfixAiguilleuseList();
	PostFixEnableAiguilleuseAdd();
	}
	
	
	function PostFixEnableAiguilleuseInsert(hostname){
		var XHR = new XHRConnection();
		XHR.appendData('PostFixEnableAiguilleuse-insert',hostname);
		XHR.appendData('ou','{$_GET["ou"]}');
		XHR.appendData('hostname','{$_GET["hostname"]}');
		XHR.sendAndLoad('$page', 'GET',x_PostFixEnableAiguilleuseInsert);
	
	}
	</script>	
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
}

function add_host(){
	$ou=base64_decode($_GET["ou"]);
	$main=new maincf_multi($_GET["hostname"],$ou);
	$page=CurrentPageName();
	$tpl=new templates();
	
	$_GET["PostFixEnableAiguilleuse-insert"]=strtolower(trim($_GET["PostFixEnableAiguilleuse-insert"]));
	
	$PostFixAiguilleuseServers=unserialize(base64_decode($main->GET_BIGDATA("PostFixAiguilleuseServers")));
	if(!is_array($PostFixAiguilleuseServers)){$PostFixAiguilleuseServers=array();}
	$PostFixAiguilleuseServers["HOSTS"][$_GET["PostFixEnableAiguilleuse-insert"]]=true;
	$PostFixAiguilleuseServers[$_GET["PostFixEnableAiguilleuse-insert"]]["DESTINATIONS"]=array();
	
	$final=base64_encode(serialize($PostFixAiguilleuseServers));
	$main->SET_BIGDATA("PostFixAiguilleuseServers",$final);
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-multi-aiguilleuse={$_GET["hostname"]}");	
	
	
}
function del_host(){
	$ou=base64_decode($_GET["ou"]);
	$main=new maincf_multi($_GET["hostname"],$ou);
	$page=CurrentPageName();
	$tpl=new templates();
	$host=base64_decode($_GET["PostFixAiguilleuseServerDelete"]);
	$PostFixAiguilleuseServers=unserialize(base64_decode($main->GET_BIGDATA("PostFixAiguilleuseServers")));
	unset($PostFixAiguilleuseServers["HOSTS"][$host]);
	unset($PostFixAiguilleuseServers[$host]);
	$final=base64_encode(serialize($PostFixAiguilleuseServers));
	$main->SET_BIGDATA("PostFixAiguilleuseServers",$final);	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-multi-aiguilleuse={$_GET["hostname"]}");	
	
}





?>
