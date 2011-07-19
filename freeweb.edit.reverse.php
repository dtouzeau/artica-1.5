<?php
	session_start();
	if($_SESSION["uid"]==null){echo "window.location.href ='logoff.php';";die();}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.pure-ftpd.inc');
	include_once('ressources/class.apache.inc');
	include_once('ressources/class.freeweb.inc');
	include_once('ressources/class.user.inc');
	$user=new usersMenus();
	if($user->AsWebMaster==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["listwebs-search"])){lists();exit;}
	if(isset($_GET["zmd5-js"])){form_edit_js();exit;}
	if(isset($_GET["zmd5"])){form_edit();exit;}
	
	if(isset($_GET["adv-js"])){adv_js();exit;}
	if(isset($_GET["adv"])){adv_form();exit;}
	if(isset($_POST["ProxyVia"])){adv_form_save();exit;}
	
	if(isset($_POST["ProxyPass"])){form_save();exit;}
	if(isset($_POST["ProxyPassDelete"])){form_del();exit;}
	
	
	
form();
function adv_js(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{reverse_proxy}&nbsp;&raquo;&nbsp;{advanced_settings}");
	$html="YahooWin4('490','$page?adv=yes&servername={$_GET["servername"]}','$title');";
	echo $html;
}
function form_edit_js(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{reverse_proxy}");
	$html="YahooWin4('490','$page?zmd5={$_GET["zmd5-js"]}&servername={$_GET["servername"]}','$title');";
	echo $html;
}

function form(){
	$page=CurrentPageName();
	$tpl=new templates();		
	$t=time();
	$html="
	<table style='width:100%'>
	<tr><td width=99% valign='top'><div class=explain>{freeweb_reverse_proxy_explain}</div></td>
	<td valign='top'>". imgtootltip("parameters2-64.png","{advanced_settings}","Loadjs('$page?adv-js=yes&servername={$_GET["servername"]}')")."</td>
	</tr>
	</table>
	<center>
	<table style='width:100%' class=form style='width:50%'>
	<tr>
	<td class=legend>{search}:</td>
	<td>". Field_text("ProxyPassList-search",null,"font-size:14px;padding:3px",null,null,null,false,"ReversProxySearchCheck(event)")."</td>
	</tr>
	</table>
	</center>
	<div id='ProxyPassList'></div>
	<script>
		function ReversProxySearchCheck(e){
			if(checkEnter(e)){ReversProxySearch();}
		}
		function ReversProxySearch(){
			var se=escape(document.getElementById('ProxyPassList-search').value);
			LoadAjax('ProxyPassList','$page?listwebs-search=yes&search='+se+'&servername={$_GET["servername"]}');
		}
			
		ReversProxySearch();	
	</script>
	";

	echo $tpl->_ENGINE_parse_body($html);	
	
}


function lists(){
	
	$search=$_GET["search"];
	$page=CurrentPageName();
	$users=new usersMenus();
	$where=null;
	
	if(strlen($search)>1){
		$search="*$search*";
		$search=str_replace("*","%",$search);
		$search=str_replace("%%","%",$search);
		$whereOU="AND ProxyPass LIKE '$search'";
	}
	
	
	
	
	$tpl=new templates();	
	$sock=new sockets();	
	
	$delete_freeweb_text=$tpl->javascript_parse_text("{delete_freeweb_text}");
	$sql="SELECT * FROM freewebs_proxy WHERE servername='{$_GET["servername"]}' $whereOU ORDER BY ProxyPass";
	$q=new mysql();
	$q->BuildTables();
	
	
	$results=$q->QUERY_SQL($sql,'artica_backup');
	if(!$q->ok){echo "<H2>$q->mysql_error</H2><code>$sql</code>";}
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th>". imgtootltip("plus-24.png","{add}","Loadjs('$page?zmd5-js=&servername={$_GET["servername"]}')")."</th>
	<th>{request_from}</th>
	<th>&nbsp;</th>
	<th>{redirect_to}</th>
	<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";			
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$request_from=null;
		$redirect_to=null;
		if(preg_match("#(.+?)\s+(.+)#", $ligne["ProxyPass"],$re)){$request_from=$re[1];$redirect_to=$re[2];}
		
		
		$html=$html."
			<tr class=$classtr>
			<td width=1%>". imgtootltip("Firewall-Move-Right-32.png","{edit}","Loadjs('$page?zmd5-js={$ligne["zmd5"]}&servername={$_GET["servername"]}')")."</td>
			<td nowrap><strong style='font-size:14px'>$request_from</strong></td>
			<td width=1%><img src='img/fleche-20-right.png'></td>
			<td nowrap><strong style='font-size:14px'>$redirect_to</strong></td>
			<td width=1%>". imgtootltip("delete-24.png","{delete}","ProxyPassDelete('{$ligne["zmd5"]}')")."</td>
			</tr>
			";
		
	}	
	
	$html=$html."
	</tbody>
	</table>
	<script>
	var x_ProxyPassDelete=function (obj) {
			var results=obj.responseText;
			if(results.length>10){alert(results);}	
			ReversProxySearch();
			YahooWin4Hide();
			if(document.getElementById('main_config_freeweb')){	RefreshTab('main_config_freeweb');}
			if(document.getElementById('container-www-tabs')){	RefreshTab('container-www-tabs');}
		}	
		
		function ProxyPassDelete(server){
			if(confirm('$delete_freeweb_text')){
				var XHR = new XHRConnection();
				XHR.appendData('ProxyPassDelete',server);
				AnimateDiv('ProxyPassList');
    			XHR.sendAndLoad('$page', 'POST',x_ProxyPassDelete);
			}
		}	
	</script>	
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);	
	
	
}

function form_edit(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$sql="SELECT * FROM freewebs_proxy WHERE zmd5='{$_GET["zmd5"]}'";
	$q=new mysql();
	$button="{edit}";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
	if($_GET["zmd5"]==null){$button="{add}";}
	
	
	$request_from="/";
	$redirect_to="http://otherserver:8088/";
	if(preg_match("#(.+?)\s+(.+)#", $ligne["ProxyPass"],$re)){$request_from=$re[1];$redirect_to=$re[2];}

	
	
	$html=$html."
	<div id='block3'>
	<table style='width:100%' class=form>
	<tr>
		<td colspan=3><span style='font-size:16px'>{reverse_proxy}<hr style='border-color:005447'></td>
	</tr>
	<tr>
		<td class=legend>{request_from}:</td>
		<td>". Field_text("ProxyPass1",$request_from,"width:280px;font-size:16px;padding:3px;font-weight:bold")."</td>
		<td>". help_icon("{ProxyPass_explain}")."</td>
	</tr>	
	<tr>
		<td class=legend>{redirect_to}:</td>
		<td>". Field_text("ProxyPass2",$redirect_to,"width:280px;font-size:16px;padding:3px;font-weight:bold")."</td>
		<td>". help_icon("{ProxyPass_explain}")."</td>
	</tr>		
	<tr><td colspan=2 align='right'><hr>". button($button,"ProxyPassSave()")."</td></tr>
		
	</table>
	
	
	</div>
<script>
		
		function ProxyPassSave(){
				var XHR = new XHRConnection();
				XHR.appendData('zmd5','{$_GET["zmd5"]}');
				XHR.appendData('servername','{$_GET["servername"]}');
				XHR.appendData('ProxyPass',document.getElementById('ProxyPass1').value+'   '+document.getElementById('ProxyPass2').value);
				AnimateDiv('ProxyPassList');
    			XHR.sendAndLoad('$page', 'POST',x_ProxyPassDelete);
			
		}		
	
	</script>";

	echo $tpl->_ENGINE_parse_body($html);
	
}

function form_save(){
	
	$md5=md5($_POST["servername"].$_POST["ProxyPass"]);
	$sql="INSERT INTO freewebs_proxy (zmd5,servername,ProxyPass) VALUES('$md5','{$_POST["servername"]}','{$_POST["ProxyPass"]}');";
	if($_POST["zmd5"]<>null){
		$sql="UPDATE freewebs_proxy SET ProxyPass='{$_POST["ProxyPass"]}' WHERE zmd5='{$_POST["zmd5"]}'";
	}
	
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?freeweb-website=yes&servername={$_POST["servername"]}");
	
}

function form_del(){
	$sql="DELETE FROM freewebs_proxy WHERE zmd5='{$_POST["ProxyPassDelete"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?freeweb-website=yes&servername={$_POST["servername"]}");		
}

function adv_form(){
	$page=CurrentPageName();
	$tpl=new templates();		
	$f=new freeweb($_GET["servername"]);
	if(!is_numeric($f->Params["Proxy"]["ProxyRequests"])){$f->Params["Proxy"]["ProxyRequests"]=0;}
	if(!is_numeric($f->Params["Proxy"]["ProxyVia"])){$f->Params["Proxy"]["ProxyVia"]=0;}
	if(!is_numeric($f->Params["Proxy"]["KeepAlive"])){$f->Params["Proxy"]["KeepAlive"]=0;}
	if(!is_numeric($f->Params["Proxy"]["ProxyTimeout"])){$f->Params["Proxy"]["ProxyTimeout"]=300;}
	
	
	
	
$html=$html."
	<div id='block3'>
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{ProxyRequests}:</td>
		<td>". Field_checkbox("ProxyRequests",1,$f->Params["Proxy"]["ProxyRequests"])."</td>
		<td>". help_icon("{ProxyRequests_explain}")."</td>
	</tr>	
	<tr>
		<td class=legend>{ProxyVia}:</td>
		<td>". Field_checkbox("ProxyVia",1,$f->Params["Proxy"]["ProxyVia"])."</td>
		<td>". help_icon("{ProxyVia_explain}")."</td>
	</tr>
	<tr>
		<td class=legend>{KeepAlive}:</td>
		<td>". Field_checkbox("KeepAlive",1,$f->Params["Proxy"]["KeepAlive"])."</td>
		<td>". help_icon("{KeepAlive_explain}")."</td>
	</tr>	
	<tr>
		<td class=legend>{AllowCONNECT}:</td>
		<td>". Field_text("AllowCONNECT",$f->Params["Proxy"]["AllowCONNECT"],"width:150px;font-size:16px;padding:3px;font-weight:bold")."</td>
		<td>". help_icon("{AllowCONNECT_explain}")."</td>
	</tr>	
	<tr>
		<td class=legend>{ProxyTimeout}:</td>
		<td>". Field_text("ProxyTimeout",$f->Params["Proxy"]["ProxyTimeout"],"width:90px;font-size:16px;padding:3px;font-weight:bold")."</td>
		<td>". help_icon("{ProxyTimeout_explain}")."</td>
	</tr>	
	
	
	
	
	<tr><td colspan=2 align='right'><hr>". button("{apply}","ProxyAdvSave()")."</td></tr>
		
	
	</table>
	
	
	</div>
<script>
		
		function ProxyAdvSave(){
				var XHR = new XHRConnection();
				XHR.appendData('servername','{$_GET["servername"]}');
				if(document.getElementById('ProxyRequests').checked){XHR.appendData('ProxyRequests',1);}else{XHR.appendData('ProxyRequests',0);}
				if(document.getElementById('ProxyVia').checked){XHR.appendData('ProxyVia',1);}else{XHR.appendData('ProxyVia',0);}
				if(document.getElementById('KeepAlive').checked){XHR.appendData('KeepAlive',1);}else{XHR.appendData('KeepAlive',0);}
				XHR.appendData('AllowCONNECT',document.getElementById('AllowCONNECT').value);
				XHR.appendData('ProxyTimeout',document.getElementById('ProxyTimeout').value);
				
				
				AnimateDiv('ProxyPassList');
    			XHR.sendAndLoad('$page', 'POST',x_ProxyPassDelete);
			
		}		
	
	</script>";

	echo $tpl->_ENGINE_parse_body($html);	
	
}
function adv_form_save(){
	$f=new freeweb($_POST["servername"]);
	while (list ($key, $line) = each ($_POST) ){
		$f->Params["Proxy"][$key]=$line;
	}
	$f->SaveParams();
}