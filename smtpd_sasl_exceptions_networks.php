<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.users.menus.inc');
	

	
	$user=new usersMenus();
	if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["popup-list"])){popup_list();exit;}
	if(isset($_GET["smtpd_sasl_exceptions_networks_add"])){add();exit;}
	if(isset($_GET["smtpd_sasl_exceptions_networks_del"])){del();exit;}
	
	
	
	if(isset($_GET["popup-toolbox"])){toolbox();exit;}
	if(isset($_GET["smtpd_sasl_exceptions_mynet"])){smtpd_sasl_exceptions_mynet_save();exit;}
	
	js();
	
	
function js(){
if(GET_CACHED(__FILE__,__FUNCTION__,null)){return null;}
$prefix="smtpd_sasl_exceptions_networks_";
$page=CurrentPageName();
$users=new usersMenus();
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{smtpd_sasl_exceptions_networks}');
$give_the_new_network=$tpl->javascript_parse_text("{give the new network}");


$html="

function SaslExceptionsNetworksLoadpage(){
	YahooWin5(550,'$page?popup=yes','$title');
	}
	
var X_smtpd_sasl_exceptions_networks_add= function (obj) {
	LoadAjax('smtpd_sasl_exceptions_networks_list','$page?popup-list=yes');
	}
		


function smtpd_sasl_exceptions_networks_add(){
	var a=prompt('$give_the_new_network');
		if(a){
			var XHR = new XHRConnection();
			XHR.appendData('smtpd_sasl_exceptions_networks_add',a);
			document.getElementById('smtpd_sasl_exceptions_networks_list').innerHTML='<center><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',X_smtpd_sasl_exceptions_networks_add);
			}		
	}
	
function smtpd_sasl_exceptions_delete(id_encrypted){
	var XHR = new XHRConnection();
	XHR.appendData('smtpd_sasl_exceptions_networks_del',id_encrypted);
	document.getElementById('smtpd_sasl_exceptions_networks_list').innerHTML='<center><img src=img/wait_verybig.gif></center>';
	XHR.sendAndLoad('$page', 'GET',X_smtpd_sasl_exceptions_networks_add);
	
	}

	
var X_SmtpdSaslExceptionsMynetSave= function (obj) {
		LoadAjax('smtpd_sasl_exceptions_networks_list','$page?popup-list=yes');
		LoadAjax('smtpd_sasl_exceptions_toolbox','$page?popup-toolbox=yes');
	}	
	
function SmtpdSaslExceptionsMynetSave(){
			var XHR = new XHRConnection();
			XHR.appendData('smtpd_sasl_exceptions_mynet',document.getElementById('smtpd_sasl_exceptions_mynet').value);
			document.getElementById('smtpd_sasl_exceptions_toolbox').innerHTML='<center><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',X_SmtpdSaslExceptionsMynetSave);
}
	
	
	
	SaslExceptionsNetworksLoadpage();
";
	SET_CACHED(__FILE__,__FUNCTION__,null,$html);
	echo $html;	
	
	
}

function popup(){
	
	

	
	
	$page=CurrentPageName();
	$html="<div class=explain>{smtpd_sasl_exceptions_networks_text}<br>{smtpd_sasl_exceptions_networks_explain}</div>
	<table style='width:100%'>
	<tr>
		<td valign='top'>
			<div id='smtpd_sasl_exceptions_networks_list'></div>
		</td>
		<td valign='top'>
			<div id='smtpd_sasl_exceptions_toolbox'></div>
			
		</td>
	</tr>
	</table>
	<script>
		LoadAjax('smtpd_sasl_exceptions_networks_list','$page?popup-list=yes');
		LoadAjax('smtpd_sasl_exceptions_toolbox','$page?popup-toolbox=yes');
	</script>
	";
	
	$tpl=new templates();
 $html=$tpl->_ENGINE_parse_body($html);
 SET_CACHED(__FILE__,__FUNCTION__,null,$html);
 echo $html;
}

function toolbox(){
	$sock=new sockets();
	$smtpd_sasl_exceptions_mynet=$sock->GET_INFO("smtpd_sasl_exceptions_mynet");
	$add=Paragraphe("folder-network2-64-add.png","{add_new_network}","{add_new_network_text}","javascript:smtpd_sasl_exceptions_networks_add()");
	$mynetwork=Paragraphe_switch_img("{enable_mynetwork}","{enable_mynetwork_text}","smtpd_sasl_exceptions_mynet",$smtpd_sasl_exceptions_mynet);
	
	$html="
	$add
	$mynetwork
	<div style='width:100%;text-align:right'><hr>
	".button("{edit}","SmtpdSaslExceptionsMynetSave()")."
	</div>";
	
	$tpl=new templates();
 	echo $tpl->_ENGINE_parse_body($html);	
}

function add(){
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->GET_INFO("smtpd_sasl_exceptions_networks")));
	$array[$_GET["smtpd_sasl_exceptions_networks_add"]]=$_GET["smtpd_sasl_exceptions_networks_add"];
	if(is_array($array)){
		while (list ($num, $net) = each ($array) ){
			$finale[$net]=$net;
		}
	}
	
	$text=base64_encode(serialize($finale));
	$sock->SaveConfigFile($text,"smtpd_sasl_exceptions_networks");
	$sock->getFrameWork("cmd.php?SaveMaincf=yes");
}

function del(){
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->GET_INFO("smtpd_sasl_exceptions_networks")));
	$net=base64_decode($_GET["smtpd_sasl_exceptions_networks_del"]);	
	unset($array[$net]);
	if(is_array($array)){
		while (list ($num, $net) = each ($array) ){
			$finale[$net]=$net;
		}
	}
	
	$text=base64_encode(serialize($finale));
	$sock->SaveConfigFile($text,"smtpd_sasl_exceptions_networks");
	$sock->getFrameWork("cmd.php?SaveMaincf=yes");	
}


function popup_list(){
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->GET_INFO("smtpd_sasl_exceptions_networks")));
	$smtpd_sasl_exceptions_mynet=$sock->GET_INFO("smtpd_sasl_exceptions_mynet");

			$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:99.5%'>
<thead class='thead'>
	<tr>
	<th colspan=3>{smtpd_sasl_exceptions_networks}</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
	if($smtpd_sasl_exceptions_mynet==1){
		$main=new main_cf();
		if(is_array($main->array_mynetworks)){
			while (list ($num, $val) = each ($main->array_mynetworks) ){
				if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
				$html=$html."
				<tr>
					<td width=1%><img src='img/folder-network-32.png'></td>
					<td style='font-size:16px'>". $val."</td>
					<td width=1%>&nbsp;</td>
				</tr>";
			}
			
		}
	}
	
if(is_array($array)){
		while (list ($num, $net) = each ($array) ){
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$net_encrypted=base64_encode($net);
			$html=$html."
			<tr ". CellRollOver().">
				<td width=1%><img src='img/folder-network-32.png'></td>
				<td style='font-size:16px'>". $net."</td>
				<td width=1%>". imgtootltip("delete-32.png","{delete}","smtpd_sasl_exceptions_delete('$net_encrypted')")."</td>
			</tr>";
			
		}
	}	
	
	$html=$html."</table>";
	$tpl=new templates();
 $html=$tpl->_ENGINE_parse_body($html);
 echo $html;	
	
}

function smtpd_sasl_exceptions_mynet_save(){
	$sock=new sockets();
	$sock->SET_INFO("smtpd_sasl_exceptions_mynet",$_GET["smtpd_sasl_exceptions_mynet"]);
	$sock->getFrameWork("cmd.php?SaveMaincf=yes");
	
}






?>