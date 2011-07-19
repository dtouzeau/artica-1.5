<?php
session_start();
include_once('ressources/class.templates.inc');

if(isset($_GET["index"])){sa_blacklist();exit;}
if(isset($_GET["EnableSaBlackListUpdate"])){EnableSaBlackListUpdateSave();exit;}
js();




function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$user=new usersMenus();
	if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		$error="alert('{ERROR_NO_PRIVS}');";
		echo $tpl->_ENGINE_parse_body($error);
		die();
	
	}
	$title=$tpl->_ENGINE_parse_body('{APP_SA_BLACKLIST}');
	
	$html="
	
	
	function APP_SA_BLACKLIST(){
		YahooWin3(600,'$page?index=yes&ou={$_GET["ou"]}','$title');
	}
	
	APP_SA_BLACKLIST();
	
var x_EnableSaBlackListUpdateSave= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>0){alert(tempvalue)};
	APP_SA_BLACKLIST();
}	
	
	function EnableSaBlackListUpdateSave(){
		var EnableSaBlackListUpdate=document.getElementById('EnableSaBlackListUpdate').value;
		var XHR = new XHRConnection();
		XHR.appendData('EnableSaBlackListUpdate',EnableSaBlackListUpdate);
		document.getElementById('EnableSaBlackListUpdateDiv').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_EnableSaBlackListUpdateSave);
		}
		
	
	";
	echo $html;
	}

	
function sa_blacklist(){
	
	$sock=new sockets();
	$EnableSaBlackListUpdate=$sock->GET_INFO('EnableSaBlackListUpdate');
	$SpamassassinSablackListCount=$sock->getfile('SpamassassinSablackListCount');
	$form=Paragraphe_switch_img('{ENABLE_SA_BLACKLIST_UPDATE}','{ENABLE_SA_BLACKLIST_UPDATE_TEXT}',"EnableSaBlackListUpdate",$EnableSaBlackListUpdate,'{enable_disable}',300);
	
	$html="<H1>{APP_SA_BLACKLIST}</H1>
	<div id='EnableSaBlackListUpdateDiv'>
	<p class=caption>{APP_SA_BLACKLIST_AUTOUPDATE}</p>

	<table style='width:100%'>
	<tr>
		<td valign='top'>
			<img src='img/bg_spam-assassin-120.png'>
			<p class=caption style='color:#B73C3C;width:200px;background-color:white;padding:3px;margin:3px;border:1px solid #B73C3C'>{APP_SA_BLACKLIST_AUTOUPDATE_WARNING}</p>
		
		</td>
		<td valign='top'>
			<H3 style='text-align:right'>$SpamassassinSablackListCount</H3><hr>
		$form
		<hr>
			<div style='width:100%;text-align:right'><input type='button' OnClick=\"javascript:EnableSaBlackListUpdateSave();\" value='{edit}&nbsp;&raquo;&raquo;'></div>
		</td>
	</tr>
	</table>
	</div>	
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function EnableSaBlackListUpdateSave(){
	
	$user=new usersMenus();
	if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		$error='{ERROR_NO_PRIVS}';
		echo $tpl->_ENGINE_parse_body($error);
		die();
	
	}	
	$sock=new sockets();
	$sock->SET_INFO("EnableSaBlackListUpdate",$_GET["EnableSaBlackListUpdate"]);
	$sock->getFrameWork('SpamassassinReload');
}



