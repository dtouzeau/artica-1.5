<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.maincf.multi.inc');
	include_once('ressources/class.ssl.certificate.inc');
	if(isset($_GET["org"])){$_GET["ou"]=$_GET["org"];}
	
	if(!PostFixMultiVerifyRights()){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["countryName"])){save();exit;}
	
	
	
js();	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{certificate_infos}',"postfix.index.php");
	$title2=$tpl->_ENGINE_parse_body('{PostfixAutoBlockManageFW}',"postfix.index.php");
	$title_compile=$tpl->_ENGINE_parse_body('{PostfixAutoBlockCompileFW}',"postfix.index.php");
	$ou=$_GET["ou"];
	$hostname=$_GET["hostname"];
$html="
		function PostfixMultiCertificateLoad(){
			YahooWin3(575,'$page?popup=yes&hostname=$hostname&ou=$ou','$title');	
		
		}
		
	var X_PostfixMultiCertificateSave= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		YahooWin3Hide();
		}			
		
		function PostfixMultiCertificateSave(){
			var XHR = new XHRConnection();
			XHR.appendData('hostname','$hostname');
			XHR.appendData('ou','$ou');
		  	XHR.appendData('countryName',document.getElementById('countryName').value);
		  	XHR.appendData('localityName',document.getElementById('localityName').value);
		  	XHR.appendData('organizationalUnitName',document.getElementById('organizationalUnitName').value);
		  	XHR.appendData('emailAddress',document.getElementById('emailAddress').value);
		  	XHR.appendData('organizationName',document.getElementById('organizationName').value);
		  	
		  	
		  	
		  	document.getElementById('PostfixMultiCertificateDiv').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		  	XHR.sendAndLoad('$page', 'GET',X_PostfixMultiCertificateSave);	
		}
	
	
	PostfixMultiCertificateLoad()";
	echo $html;

}

function save(){
	$main=new maincf_multi($_GET["hostname"],base64_decode($_GET["ou"]));
	$datas=base64_encode(serialize($_GET));
	$main->SET_BIGDATA("certificate_smtp_parameters",$datas);
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-multi-sasl={$_GET["hostname"]}");
	
}


function popup(){
	
$ssl=new ssl_certificate();
	$array=$ssl->array_ssl;
	$styleF="font-size:13px;padding:3px";
	
	
	$main=new maincf_multi($_GET["hostname"],base64_decode($_GET["ou"]));
	$conf=unserialize(base64_decode($main->GET_BIGDATA("certificate_smtp_parameters")));
	
	if($conf["organizationName"]==null){$conf["organizationName"]=base64_decode($_GET["ou"]);}
	
	$users=new usersMenus();
	$tpl=new templates();
	$country_name=Field_array_Hash($ssl->array_country_codes,'countryName',$conf["countryName"],null,null,0,$styleF);	
	$html="<div id='PostfixMultiCertificateDiv'>
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:13px'>{countryName}:</td>	
		<td>$country_name</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{localityName}:</td>
		<td>". Field_text("localityName",$conf["localityName"],$styleF)."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{organizationalUnitName}:</td>
		<td>". Field_text("organizationalUnitName",$conf["organizationalUnitName"],$styleF)."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{organizationName}:</td>
		<td>". Field_text("organizationName",$conf["organizationName"],$styleF)."</td>
	</tr>	
	
	
		<tr>
		<td class=legend style='font-size:13px'>{emailAddress}:</strong></td>
		<td align='left'>" . Field_text("emailAddress",$conf["emailAddress"],$styleF)  . "</td>
		</tr>
	<tr>
		<td colspan=2 align=right>
			<hr>
				". button("{apply}","PostfixMultiCertificateSave()")."
			</td>
	</tr>	
	</table>
	</div>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
}

?>