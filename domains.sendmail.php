<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.main_cf.inc');
	
$users=new usersMenus();
$tpl=new templates();

if($_GET["ou"]==null){
	if(!$users->AsMailBoxAdministrator){
		echo $tpl->javascript_parse_text("alert('{ERROR_NO_PRIVS}');");
		die();
	}
}

if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["subject"])){save();exit;}
if(isset($_GET["sendnow"])){sendnow();exit;}

js();


function js(){
	$tpl=new templates();
	if($_GET["ou"]<>null){
		$users=new usersMenus();
		if(!$users->AsOrgAdmin){
			echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}');");
			die();	
		}
		if(!$users->AsArticaAdministrator){
			if($_SESSION["ou"]<>$_GET["ou"]){
				echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}');");
				die();	
			}
		}
		
		$ou=$_GET["ou"];}
		$ou_encrypted=base64_encode($ou);
	
	
	$title=$tpl->_ENGINE_parse_body('{send_to_all_users}');
	$page=CurrentPageName();
	$html="
	function StartPage(){
		YahooWin(650,'$page?popup=yes&ou=$ou_encrypted');
		
		
	}

var x_SaveEmailText=function (obj) {
	var tempvalue=obj.responseText;
	if (tempvalue.length>0){alert(tempvalue);} 
	StartPage();
	
} 

var x_SendEmailText=function (obj) {
	var tempvalue=obj.responseText;
	if (tempvalue.length>0){alert(tempvalue);} 
}



function SendEmailText(){
	SaveEmailText();
	var XHR = new XHRConnection();
	XHR.appendData('ou','$ou');
    XHR.appendData('sendnow','yes');
    XHR.sendAndLoad('$page', 'GET',x_SendEmailText);
	
}
	
	function SaveEmailText(){
		var XHR = new XHRConnection();
		XHR.appendData('ou','$ou');
        XHR.appendData('subject',document.getElementById('subjectEmailingOrg').value);
        XHR.appendData('from',document.getElementById('FromEmailingOrg').value);
        XHR.appendData('body',document.getElementById('bodyEmailingOrg').value);
        document.getElementById('emailform').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
        XHR.sendAndLoad('$page', 'GET',x_SaveEmailText);
	}

StartPage();
	
	";
	echo $html;
	
}


function popup(){
	$tpl=new templates();
	$page=CurrentPageName();
	$sock=new sockets();
	$_GET["ou"]=base64_decode($_GET["ou"]);
	if($_GET["ou"]<>null){
		$config=$sock->GET_INFO("eMailingForAllOrg{$_GET["ou"]}");
	}else{
		$config=$sock->GET_INFO("eMailingForAllOrg");
	}
	
	
	if(preg_match('#<from>(.+?)</from><subject>(.+?)</subject><body>(.+?)</body>#is',$config,$re)){
		$from=$re[1];
		$subject=$re[2];
		$body=$re[3];
		$conf=explode("\n",$body);
		$body=null;
		while (list($num,$val)=each($conf)){
			if(trim($val)==null){continue;}
			$body.=$val."\n";
		}
	}
	
	if($_SESSION["uid"]<>-100){
		$user=new user($uid);
		$from=$user->mail;
	}	
	
	$html="
	<H1>{send_to_all_users}</H1>
	<p class=caption>{send_to_all_users_text}</p>
	<div id='emailform'>
	<table style='width:100%' class=table_form>
	<tr><td colspan=2 align=right><input type='button' OnClick=\"javascript:SendEmailText();\" value='{send}&nbsp;&raquo;'></td></tr>
	<tr>
		<td class=legend>{from}:</td>
		<td>". Field_text('FromEmailingOrg',$from,'width:220px')."</td>
	</tr>	
	<tr>
		<td class=legend>{subject}:</td>
		<td>". Field_text('subjectEmailingOrg',$subject)."</td>
	</tr>
	<tr><td colspan=2><hr></td></tr>
	<tr><td colspan=2><textarea name='body' id='bodyEmailingOrg' style='width:100%;height:120px;overflow:auto'>$body</textarea></td>
	</tr>
	<tr><td colspan=2><hr></td></tr>
	<tr><td colspan=2 align=right><input type='button' OnClick=\"javascript:SaveEmailText();\" value='{edit}&nbsp;&raquo;'></td></tr>
	</table>
	</div>
	";
	echo $tpl->_ENGINE_parse_body($html);	
}

function save(){
	$tpl=new templates();
	if($_SESSION["uid"]<>-100){
		$user=new user($_SESSION["uid"]);
		$_GET["from"]=$user->mail;
	}
	
	$conf="<from>{$_GET["from"]}</from><subject>{$_GET["subject"]}</subject><body>{$_GET["body"]}</body>";
	$sock=new sockets();
	
	if($_GET["ou"]<>null){
		$users=new usersMenus();
		if(!$users->AsOrgAdmin){
			echo html_entity_decode($tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}');"));
			die();	
		}
		if(!$users->AsArticaAdministrator){
			if($_SESSION["ou"]<>$_GET["ou"]){
				echo html_entity_decode($tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}');"));
				die();	
			}
		}
	}	
	
	
if($_GET["ou"]<>null){
		$sock->SaveConfigFile($conf,"eMailingForAllOrg{$_GET["ou"]}");
	}else{
		$sock->SaveConfigFile($conf,'eMailingForAllOrg');
	}	
	
	
}

function sendnow(){
	$sock=new sockets();
	
if($_GET["ou"]<>null){
		$sock->getfile("SendMailingSingleOrgs:{$_GET["ou"]}");
	}else{
		$sock->getfile('SendMailingToOrgs');;
	}		
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{SendMailingToOrgs_text}');
	}
	
?>