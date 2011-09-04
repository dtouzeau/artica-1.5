<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.maincf.multi.inc');
	include_once('ressources/class.status.inc');

	include_once('ressources/class.ini.inc');
	include_once('ressources/class.milter.greylist.inc');	
	
	
	if(isset($_GET["org"])){$_GET["ou"]=$_GET["org"];}
	
	if(!PostFixMultiVerifyRights()){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	if(isset($_POST["activate_sync_port"])){activate_sync_port_save();exit;}
	if(isset($_GET["syncmgreylist-list"])){syncmgreylist_list();exit;}
	if(isset($_POST["DeletePeerMX"])){DeletePeerMX();exit;}
	if(isset($_POST["AddPeerMX"])){AddPeerMX();exit;}
	
	
	
page();

function page(){
	$page=CurrentPageName();
	$tpl=new templates();
	$mgrey=new milter_greylist(false,$_GET["hostname"],base64_decode($_GET["ou"]));
	$ou=base64_decode($_GET["ou"]);
	
	$html="
	<div class=explain>{milter_multimx_explain}</div>
	
	<table style='width:100%;margin-bottom:10px' class=form>
	<tbody>
	<tr>
		<td class=legend>{activate_sync_port}</td>
		<td>". Field_checkbox("activate_sync_port", 1,$mgrey->main_array["activate_sync_port"])."</td>
		<td width=1%>".help_icon("{activate_sync_port_mgreylist_text}")."</td> 
	</tr>
	<tr><td colspan=3 align='right'><hr>". button("{apply}","SaveSyncPortMgrey()")."</td></tr>
	</tbody>
	</table>
	
	<div style='width:100%;height:250px;overlflow:auto' id='syncmgreylist-list'></div>
	
	
	<script>
	
		function RefreshSyncmgreylist(){
			LoadAjax('syncmgreylist-list','$page?syncmgreylist-list=yes&hostname={$_GET["hostname"]}&ou={$_GET["ou"]}');
		}
	
		
		var X_SaveSyncPortMgrey= function (obj) {
			var results=obj.responseText;
			if(results.length>3){alert(results);}
			RefreshSyncmgreylist();
		}		

		function SaveSyncPortMgrey(){
				var XHR = new XHRConnection();
				if(document.getElementById('activate_sync_port').checked){XHR.appendData('activate_sync_port',1);}else{XHR.appendData('activate_sync_port',0);}
				XHR.appendData('hostname','{$_GET["hostname"]}');
				XHR.appendData('ou','$ou');
				XHR.sendAndLoad('$page', 'POST',X_SaveSyncPortMgrey);
			}
			
	
	RefreshSyncmgreylist();
	</script>";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}
function activate_sync_port_save(){
	$mgrey=new milter_greylist(false,$_POST["hostname"],$_POST["ou"]);
	$mgrey->main_array["activate_sync_port"]=$_POST["activate_sync_port"];
	$mgrey->SaveToLdap();
	
}

function DeletePeerMX(){
	$mgrey=new milter_greylist(false,$_POST["hostname"],$_POST["ou"]);
	unset($mgrey->main_array["peer"][$_POST["DeletePeerMX"]]);
	$mgrey->SaveToLdap();	
}

function AddPeerMX(){
	$mgrey=new milter_greylist(false,$_POST["hostname"],$_POST["ou"]);
	$mgrey->main_array["peer"][$_POST["AddPeerMX"]]=$_POST["AddPeerMX"];
	$mgrey->SaveToLdap();
}

function syncmgreylist_list(){
	$page=CurrentPageName();
	$tpl=new templates();
	$mgrey=new milter_greylist(false,$_GET["hostname"],base64_decode($_GET["ou"]));
	$ou=base64_decode($_GET["ou"]);
	$add=imgtootltip("plus-24.png","{add}","AddPeerMX()");
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>$add</th>
		<th>MX</th>
		<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
	
	while (list ($num, $ligne) = each ($mgrey->main_array["peer"]) ){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$html=$html."
		<tr class=$classtr>
			<td colspan=2 style='font-size:16px' width=99%>$num</td>
			<td width=1%>". imgtootltip("delete-32.png","DeletePeerMX('$num')")."</td>
		</tr>
		";
		
		
	}
	
	
	$html=$html."</tbody></table>
	<script>
		function DeletePeerMX(num){
				var XHR = new XHRConnection();
				XHR.appendData('DeletePeerMX',num);
				XHR.appendData('hostname','{$_GET["hostname"]}');
				XHR.appendData('ou','$ou');
				XHR.sendAndLoad('$page', 'POST',X_SaveSyncPortMgrey);		
		
		}
		
		function AddPeerMX(){
				var num=prompt('$mgreylist_add_peer_mx');
				if(num){
					var XHR = new XHRConnection();
					XHR.appendData('AddPeerMX',num);
					XHR.appendData('hostname','{$_GET["hostname"]}');
					XHR.appendData('ou','$ou');
					XHR.sendAndLoad('$page', 'POST',X_SaveSyncPortMgrey);		
				}
		}		
	
	</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
	
	
}
