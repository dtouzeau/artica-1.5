<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.maincf.multi.inc');
	if(isset($_GET["org"])){$_GET["ou"]=$_GET["org"];}
	
	if(!PostFixMultiVerifyRights()){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}	

if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["enable_smtps"])){save();exit;}


js();


function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$ENABLE_SMTPS=$tpl->_ENGINE_parse_body('{ENABLE_SMTPS}');
	$html="
	function LoadMasterMultiCFSSL(){
		YahooWin3(485,'$page?popup=yes&ou={$_GET["ou"]}&hostname={$_GET["hostname"]}','master.cf (SSL)','$ENABLE_SMTPS'); 
	}
	
var x_SaveMasterMultiCFSSL= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			LoadMasterMultiCFSSL();
			}		
	
	
	function SaveMasterMultiCFSSL(){
		var XHR = new XHRConnection();
    	XHR.appendData('enable_smtps',document.getElementById('enable_smtps').value);
    	XHR.appendData('ou','{$_GET["ou"]}');
    	XHR.appendData('hostname','{$_GET["hostname"]}');
    	document.getElementById('smtpsmulti').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
    	XHR.sendAndLoad('$page', 'GET',x_SaveMasterMultiCFSSL);
		}	
	LoadMasterMultiCFSSL();";	
	echo $html;	
}

function popup(){

	$enabled=0;
	$master=new maincf_multi($_GET["hostname"],$_GET["ou"]);
	
	$form=Paragraphe_switch_img('{ENABLE_SMTPS}','{SMTPS_TEXT}','enable_smtps',$master->GET("PostfixMasterEnableSSL"),null,450);
	$page=CurrentPageName();
	$html="
	<div id='smtpsmulti'>
		<table style='width:100%'>
		<tr>
		
		<td align='left' width=99%>$form</td>
	</tr>
	<tr>
		<td align='right'><hr>". button("{save}","SaveMasterMultiCFSSL()")."</td>
	</tr>
	</table>
	</div>";
	
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);		
}

function save(){
	$master=new maincf_multi($_GET["hostname"],$_GET["ou"]);
	$master->SET_VALUE("PostfixMasterEnableSSL",$_GET["enable_smtps"]);
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-multi-mastercf={$_GET["hostname"]}");		
	
}

?>