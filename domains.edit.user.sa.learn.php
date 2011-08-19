<?php
session_start();
include_once(dirname(__FILE__)."/ressources/class.templates.inc");
include_once(dirname(__FILE__)."/ressources/class.ldap.inc");
include_once(dirname(__FILE__)."/ressources/class.user.inc");

//permissions	
	if(!CheckRights()){
		$tpl=new templates();
		echo "alert('".$tpl->javascript_parse_text("{ERROR_NO_PRIVS}").'");';
	}

	if(isset($_GET["popup"])){USER_JUNK_LEARNING_POPUP();exit;}
	if(isset($_GET["EnableUserSpamLearning"])){USER_JUNK_LEARNING_SAVE();exit;}

js();
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$uid=$_GET["uid"];
	$title=$tpl->_ENGINE_parse_body("$uid::{EnableUserSpamLearning}");
	
	$html="
	
	function USER_JUNK_LEARNING_JS(){
		YahooWin2(650,'$page?popup=yes&uid=$uid','$title');
	
	}
	
var x_USER_JUNK_LEARNING_SAVE=function (obj) {
	tempvalue=obj.responseText;
	if(tempvalue.length>3){alert(tempvalue);}
	USER_JUNK_LEARNING_JS();
    }	 	
	
	function USER_JUNK_LEARNING_SAVE(){
		var XHR = new XHRConnection();
		XHR.appendData('uid','$uid');
		XHR.appendData('EnableUserSpamLearning',document.getElementById('EnableUserSpamLearning').value);
		document.getElementById('EnableUserSpamLearning_div').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_USER_JUNK_LEARNING_SAVE);
	}
	
	USER_JUNK_LEARNING_JS();
	
	";
	
	echo $html;
	
}

function USER_JUNK_LEARNING_POPUP(){
$page=CurrentPageName();
	$tpl=new templates();	
	$uid=$_GET["uid"];
	$users=new user($uid);
	$field=Paragraphe_switch_img('{EnableUserSpamLearning}',
	'{EnableUserSpamLearning_text}','EnableUserSpamLearning',$users->EnableUserSpamLearning,null,350);
	$html="
	
	<div class=explain style='font-size:13px'>{EnableUserSpamLearning_explain}</div>
	<table style='width:100%'>
	<tr>
	<td valign='top'><img src='img/bg_spam-assassin-250.png'></td>
	<td valign='top'>
	<br>
	<div id='EnableUserSpamLearning_div'>
	$field
	</div>
	<br>
	<div style='text-align:right;width:100%'><hr>
	". button("{edit}","USER_JUNK_LEARNING_SAVE()")."
	
	</td>
	</tr>
	</table>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function USER_JUNK_LEARNING_SAVE(){
$uid=$_GET["uid"];	
$users=new user($uid);
$users->EnableUserSpamLearning=$_GET["EnableUserSpamLearning"];
$users->SaveJunkLearning();
}
function CheckRights(){
	if(!$_GET["uid"]){return false;}	
	$usersprivs=new usersMenus();
	if($usersprivs->AsAnAdministratorGeneric){return true;}
	if($usersprivs->AsOrgAdmin){return true;}
	if($usersprivs->AllowAddUsers){return true;}
	if($_SESSION["uid"]==$_GET["uid"]){return true;}	
}	

?>