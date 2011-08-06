<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.amavis.inc');
	$user=new usersMenus();
	if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_POST["template"])){savetpl();exit;}
	if(isset($_GET["readme-curtomize"])){readme_curtomize();exit;}
js();

function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{{$_GET["type"]}}");
	
	$html="YahooWin4('700','$page?popup={$_GET["type"]}','$title')";
	echo $html;
	
	
}

function savetpl(){
	$datas=base64_encode($_POST["template-data"]);
	$sock=new sockets();
	$datas=$_POST["template-data"];
	$datas=stripslashes($datas);
	writelogs("Saving {$_POST["template-data"]}",__FUNCTION__,__FILE__,__LINE__);
	$sock->SaveConfigFile($datas,"amavis-template-{$_POST["template"]}");
	
	writelogs("Saving {$_POST["template-data"]} ->amavis-template-save={$_POST["template"]}",__FUNCTION__,__FILE__,__LINE__);
	$sock->getFrameWork("cmd.php?amavis-template-save={$_POST["template"]}");
	writelogs("Saving {$_POST["template-data"]} done",__FUNCTION__,__FILE__,__LINE__);
}

function popup(){
	$page=CurrentPageName();
	$sock=new sockets();
	$datas=base64_decode($sock->getFrameWork("cmd.php?amavis-template-load={$_GET["popup"]}"));
	
	$html="
	<div id='templatedsn'>
	<div style='text-align:right;margin:5px'>". button("{help}","ReadmeCustomize()")."</div>
	<textarea style='width:680px;height:550px;overflow:auto;font-size:12px' id='template-data'>$datas</textarea>
	
	<div style='text-align:right'><hr>". button("{apply}","SaveAmavisTemplate()")."</div>
	</div>
<script>
	var x_SaveAmavisTemplate= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue)};
		Loadjs('$page?type={$_GET["popup"]}');
	}			
		
	function SaveAmavisTemplate(){
		var XHR = new XHRConnection();
		XHR.appendData('template-data',document.getElementById('template-data').value);
		XHR.appendData('template','{$_GET["popup"]}');
		document.getElementById('templatedsn').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'POST',x_SaveAmavisTemplate);	
	}	
	
	function ReadmeCustomize(){
		YahooWin5('650','$page?readme-curtomize=yes','help...');
	}
	
	
	
</script>	
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("$html");
	
	
	
}

function readme_curtomize(){
$page=CurrentPageName();
	$sock=new sockets();
	$datas=base64_decode($sock->getFrameWork("cmd.php?amavis-template-help=yes"));	
	
	$datas=htmlspecialchars($datas);
	$datas=nl2br($datas);
	$datas=str_replace("  ","&nbsp;&nbsp;&nbsp;",$datas);
	echo "<div style='height:450px;overflow:auto'><code>$datas</code></div>";
	
	
}
