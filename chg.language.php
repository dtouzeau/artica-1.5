<?php
	if(posix_getuid()==0){die();}
	session_start();
	if($_SESSION["uid"]==null){echo "window.location.href ='logoff.php';";die();}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_POST["lang"])){saveLang();exit;}
	
js();


function js(){
	
	$tpl=new templates();
	$page=CurrentPageName();
	$title=$tpl->_ENGINE_parse_body("{language}");
	$html="YahooWin4('290','$page?popup=yes','$title')";
	echo $html;
	
}

function popup(){
		$tpl=new templates();
		$page=CurrentPageName();	
		$html=new htmltools_inc();
		$lang=$html->LanguageArray();
		$field_lang=Field_array_Hash($lang,'lang',$_COOKIE["artica-language"],"style:font-size:14px");
		
		$html="
		<table class=form style='width:100%;margin-top:25px'>
		<tr>
			<td class=legend>{language}:</td>
			<td>$field_lang</td>
			<td width=1%>".button("{apply}", "ChangeAdminLang()")."</td> 
		</tr>
		</table>		
<script>
	var x_ChangeAdminLang= function (obj) {
	 var results=obj.responseText;
	 if(results.length>1){alert(results);}
	 CacheOff();
	 YahooWin4Hide();
	}

function ChangeAdminLang(){
	var lang=document.getElementById('lang').value;
	Set_Cookie('artica-language', lang, '3600', '/', '', '');
	var XHR = new XHRConnection();
	XHR.appendData('lang',lang);
	XHR.sendAndLoad('$page', 'POST',x_ChangeAdminLang);		
	
}
</script>
";
		echo $tpl->_ENGINE_parse_body($html);
	
}

function saveLang(){
	$sock=new sockets();
	$_SESSION["detected_lang"]=$_POST["lang"];
	$FileCookyKey=md5($_SERVER["REMOTE_ADDR"].$_SERVER["HTTP_USER_AGENT"]);
	$sock->SET_INFO($FileCookyKey, $_POST["Changelang"]);
}
