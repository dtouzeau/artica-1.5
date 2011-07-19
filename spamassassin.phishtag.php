<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.amavis.inc');
	include_once('ressources/class.postfix-multi.inc');
	$user=new usersMenus();
	if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["PhishTagRatio"])){Save();exit;}


	
	
js();


function js(){
	
$page=CurrentPageName();
$users=new usersMenus();
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('PhishTag');

$html="

function PhishTag_load(){
	YahooWin3('550','$page?popup=yes','$title');
	
	}
	
PhishTag_load();
";


echo $html;	
	
}


function popup(){
	
	$sock=new sockets();
	$page=CurrentPageName();
	$tpl=new templates();
	$PhishTagRatio=$sock->GET_INFO("PhishTagRatio");
	if($PhishTagRatio==null){$PhishTagRatio="0.1";}
	if($PhishTagURL==null){$PhishTagURL="http://www.antiphishing.org/consumer_recs.html";}
	
	$html="
	<div class=explain id='PhishTagRatioDiv'>{EnablePhishTag_explain}<br>{EnablePhishTag_explain_form}</div>
	
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{ratio}:</td>
		<td>". Field_text("PhishTagRatio","$PhishTagRatio","font-size:14px;padding:3px;width:60px")."</td>
	</tr>
	<tr>
		<td class=legend>{url}:</td>
		<td>". Field_text("PhishTagURL","$PhishTagURL","font-size:14px;padding:3px;width:360px")."</td>
	</tr>	
	<tr>
		<td colspan=2 align='right'><hr>". button("{apply}","PhishTagSave()")."</td>
	</tr>
	</table>
	
	<script>
	
var x_PhishTagSave=function(obj){
      var tempvalue=obj.responseText;
      YahooWin3Hide();
      }	
		
	function PhishTagSave(){
		var XHR = new XHRConnection();
		XHR.appendData('PhishTagRatio',document.getElementById('PhishTagRatio').value);
		XHR.appendData('PhishTagURL',document.getElementById('PhishTagURL').value);
		document.getElementById('PhishTagRatioDiv').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_PhishTagSave);
	}	
	
	
</script>	
	";
	echo $tpl->_ENGINE_parse_body($html);
	
	
}


function Save(){
	$sock=new sockets();
	$sock->SET_INFO("PhishTagURL",$_GET["PhishTagURL"]);
	$sock->SET_INFO("PhishTagRatio",$_GET["PhishTagRatio"]);
	$sock->getFrameWork("cmd.php?spamass-build=yes");
}
