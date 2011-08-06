<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.maincf.multi.inc');
	include_once('ressources/class.status.inc');
	if(isset($_GET["org"])){$_GET["ou"]=$_GET["org"];}
	
	if(!PostFixMultiVerifyRights()){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}	
	
	
	if(isset($_GET["enable_fetchmail"])){save();exit;}
	if(isset($_GET["popup"])){popup();exit;}
	js();
	
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_parse_body("{APP_FETCHMAIL}::{$_GET["hostname"]}");
	
	$html="
		function postfix_multi_fetchmail_load(){
			YahooWin3(665,'$page?popup=yes&hostname={$_GET["hostname"]}&ou={$_GET["ou"]}','$title');
		}
		
	var x_postfix_multi_fetchmail_save= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue)}
		postfix_multi_fetchmail_load();
		}		
		
		function postfix_multi_fetchmail_save(){
			var XHR = new XHRConnection();
			XHR.appendData('hostname','{$_GET["hostname"]}');
			XHR.appendData('ou','{$_GET["ou"]}');				
			XHR.appendData('schedule',document.getElementById('schedule').value);
			XHR.appendData('enable_fetchmail',document.getElementById('enable_fetchmail').value);
			document.getElementById('img_enable_fetchmail').src='img/wait_verybig.gif';	
			XHR.sendAndLoad('$page', 'GET',x_postfix_multi_fetchmail_save);
		}
		
		
	
	
	postfix_multi_fetchmail_load();";
	
	echo $html;
	}
	
	
function save(){
	$hostname=$_GET["hostname"];
	$array[$hostname]["enabled"]=$_GET["enable_fetchmail"];
	$array[$hostname]["schedule"]=$_GET["schedule"];
	$array[$hostname]["ou"]=$_GET["ou"];
	$main=new maincf_multi($hostname,$_GET["ou"]);
	$main->SET_BIGDATA("PostfixMultiFetchMail",base64_encode(serialize($array)));
	$sock=new sockets();
	$sock->getFrameWork('cmd.php?restart-fetchmail=yes');
	}
	
function popup(){

	$page=CurrentPageName();
	$ou=$_GET["ou"];
	$hostname=$_GET["hostname"];
	$main=new maincf_multi($hostname,$ou);
	$array=unserialize(base64_decode($main->GET_BIGDATA("PostfixMultiFetchMail")));	
	$schedule=array(2=>2,5=>5,10=>10,15=>15,20=>20,30=>30,40=>40,50=>50,55=>55);
	if($array[$hostname]["schedule"]==null){$array[$hostname]["schedule"]=10;}
	
	
	$enabled=$array[$hostname]["enabled"];
	$fetchmail_enabled=Paragraphe_switch_img('{enable_fetchmail}','{enable_fetchmail_text}','enable_fetchmail',$enabled,null,270);
	
	$forms="<table style='width:100%'>
	<tr>
	<td colspan=2>$fetchmail_enabled</td>
	</tr>
	<tr>
		<td class=legend style='font-size:14px'>{fetch_messages_every}:</td>
		<td style='font-size:14px'>
		". Field_array_Hash($schedule,
		"schedule",$array[$hostname]["schedule"],null,null,0,"font-size:14px;padding:3px")."&nbsp;(minutes)</td>
	</tr>
	</table>
	
	";
	
	
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top' width=1%><img src='img/bg_fetchmail2.png'></td>
		<td valign='top'>$forms</td>
	</tr>
	<tr>
	<td colspan=2 align='right'><hr>". button("{apply}","postfix_multi_fetchmail_save()")."</td>
	</tr>
	
	</table>
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

?>