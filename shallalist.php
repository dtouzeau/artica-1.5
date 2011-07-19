<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.dansguardian.inc');
	include_once('ressources/class.ccurl.inc');
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");	
	$user=new usersMenus();
	if(!$user->AsSquidAdministrator){
		$tpl=new templates();
		echo "alert('".$tpl->javascript_parse_text("{ERROR_NO_PRIVS}").");";
		exit;
		
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["request-order"])){request_order();exit;}
	if(isset($_GET["savelicense"])){savelicense();exit;}
	if(isset($_GET["delete-lic"])){deletelic();exit;}
js();
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{shallalist}");
	
	$html="
	function shallalist_load(){
		YahooWin2('670','$page?popup=yes','$title');
	}
	
	var x_shallalist_license_send= function (obj) {
		var res=obj.responseText;
		if (res.length>0){alert(res);}
		shallalist_load();
	}	
	
	function shallalist_license_send(){
		var XHR = new XHRConnection();
		document.getElementById('shallaimg').src='img/wait_verybig.gif';
		XHR.appendData('savelicense',document.getElementById('license').value);
		XHR.sendAndLoad('$page', 'GET',x_shallalist_license_send);
	}
	
	function shallalist_devis_send(){
		var XHR = new XHRConnection();
		document.getElementById('shallaimg').src='img/wait_verybig.gif';
		XHR.appendData('request-order',1);
		XHR.appendData('company',document.getElementById('company').value);
		XHR.appendData('email',document.getElementById('email').value);
		XHR.sendAndLoad('$page', 'GET',x_shallalist_license_send);	
	}
	
	function shallalist_delete_license(){
		var XHR = new XHRConnection();
		document.getElementById('shallaimg').src='img/wait_verybig.gif';
		XHR.appendData('delete-lic',1);
		XHR.sendAndLoad('$page', 'GET',x_shallalist_license_send);	
	}
	
	shallalist_load();
	";
	echo $html;
	
	
	
}


function popup(){
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$datas=$sock->GET_INFO("shallalistLicense");
	$ini->loadString($datas);
	$license=$ini->_params["SHALLA"]["LICENSE"];
	
	if($license==null){
		popup_ask_license();return;
	}
	
	
	$html="
	<table style='width:100%'>
		<tr>
			<td valign='top' width=1%><img src='img/database-spider-plus-128.png' id='shallaimg'></td>
			<td valign='top'>
			<div style='font-size:14px'>{shallalist_text}</div>
			<hr>
			<table style='width:100%'>
			<tr>
				<td class=legend style='font-size:13px;'>{company}:</td>
				<td style='font-size:13px;'><strong>{$ini->_params["INFO"]["company"]}</strong></td>
			</tr>
			<tr>
				<td class=legend style='font-size:13px;'>{your_email_address}:</td>
				<td style='font-size:13px;'><strong>{$ini->_params["INFO"]["email"]}</strong></td>
			</tr>
			<tr>
				<td class=legend style='font-size:13px;'>{shallalist_license}:</td>
				<td style='font-size:13px;'><strong>$license</strong></td>
			</tr>						
			<tr>
				<td colspan=2 align='right'>
					<hr>
						". button("{remove_license}","shallalist_delete_license()")."
			</td>
			</tr>
			</table>
			</td>
	</tr>
	</table>";		
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
	
}

function popup_ask_license(){
	$sock=new sockets();
	$datas=$sock->GET_INFO("shallalistLicense");
	$ini=new Bs_IniHandler();
	$ini->loadString($datas);

	
	$html="
	<table style='width:100%'>
		<tr>
			<td valign='top' width=1%><img src='img/database-spider-plus-128.png' id='shallaimg'></td>
			<td valign='top'>
			<div style='font-size:14px'>{shallalist_text}</div>
			<hr>
			<div style='font-size:14px'>{shallalist_buye}</div>
			<table style='width:100%'>
			<tr>
				<td class=legend style='font-size:13px;'>{company}:</td>
				<td>". Field_text("company",$ini->_params["INFO"]["company"],"font-size:13px;padding:3px")."</td>
			</tr>
			<tr>
				<td class=legend style='font-size:13px;'>{your_email_address}:</td>
				<td>". Field_text("email",$ini->_params["INFO"]["email"],"font-size:13px;padding:3px")."</td>
			</tr>		
			<tr>
				<td colspan=2 align='right'>
					<hr>
						". button("{send}","shallalist_devis_send()")."
			</td>
			</tr>
			</table>
	<hr>
		<table style='width:100%'>
			<tr>
				<td class=legend style='font-size:13px;'><H3>{shallalist_license}:</H3></td>
				<td>". Field_text("license",null,"font-size:12px;padding:3px")."</td>
			</tr>	
			<tr>
				<td colspan=2 align='right'>
					<hr>
						". button("{submit_license}","shallalist_license_send()")."
			</td>	
			</tr>
		</table>	
	</td>
	</tr>
	</table>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function savelicense(){

	$sock=new sockets();
	$uuid=base64_decode($sock->getFrameWork("cmd.php?system-unique-id=yes"));
	$sock=new sockets();
	$shallalistLicense=$sock->GET_INFO("shallalistLicense");
	$ini=new Bs_IniHandler();
	$ini->loadString($shallalistLicense);
	$license=$_GET["savelicense"];
	$tpl=new templates();
	
	$array["check"]="yes";
	$array["lic"]="$license";
	$array["uuid"]="$uuid";
	
	$curl=new ccurl("http://www.artica.fr/shalla-orders.php");
	$curl->parms=$array;
	if(!$curl->get()){
			$tpl=new templates();
			echo $tpl->javascript_parse_text($curl->error);
			return;
		}
		
	if(preg_match("#<ANSWER>OK</ANSWER>#is",$curl->data)){
		
		echo $tpl->javascript_parse_text("{shalla_success_license}");
	}else{
		
		echo $tpl->javascript_parse_text("{failed}")."\n".$curl->data;
		exit;		
	}
	
	$ini->set("SHALLA","LICENSE",$license);
	$sock->SaveConfigFile($ini->toString(),"shallalistLicense");
	$sock->getFrameWork("cmd.php?shalla-update-now=yes");
	
}

function request_order(){
	$curl=new ccurl("http://www.artica.fr/shalla-orders.php");
	$sock=new sockets();
	$uuid=base64_decode($sock->getFrameWork("cmd.php?system-unique-id=yes"));
	$sock=new sockets();
	$shallalistLicense=$sock->GET_INFO("shallalistLicense");
	$ini=new Bs_IniHandler();
	$ini->loadString($shallalistLicense);
	$license=$ini->_params["SHALLA"]["LICENSE"];

	$conf[]="[INFO]";
	$conf[]="email={$_GET["email"]}";
	$conf[]="company={$_GET["company"]}";
	$conf[]="[SHALLA]";
	$conf[]="LICENSE=$license";
	$sock->SaveConfigFile(implode("\n",$conf),"shallalistLicense");
	
	
	
	$_GET["UUID"]=$uuid;
	$datas=base64_encode(serialize($_GET));
	$curl->parms["DATAS"]=$datas;
	
	
	
	if(!$curl->get()){
		$tpl=new templates();
		echo $tpl->javascript_parse_text($curl->error);
		return;
	}
	
	if(preg_match("#<ANSWER>OK</ANSWER>#is",$curl->data)){
		$tpl=new templates();
		echo $tpl->javascript_parse_text("{shalla_success_order}");
	}
	
}

function deletelic(){
	$sock=new sockets();
	$shallalistLicense=$sock->GET_INFO("shallalistLicense");
	$ini=new Bs_IniHandler();
	$ini->loadString($shallalistLicense);	
	unset($ini->_params["SHALLA"]["LICENSE"]);
	$sock->SaveConfigFile($ini->toString(),"shallalistLicense");
}



?>