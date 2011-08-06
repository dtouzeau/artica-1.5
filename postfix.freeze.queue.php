<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.maincf.multi.inc');
	
	
$users=new usersMenus();
if(!$users->AsPostfixAdministrator){
	$tpl=new templates();
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	echo "alert('$ERROR_NO_PRIVS');";
	die();
	
}

	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["freeze_delivery_queue"])){save();exit;}
	
js();



function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title="{pause_the_queue}::{$_GET["hostname"]}/{$_GET["ou"]}";
	$title=$tpl->_ENGINE_parse_body($title);
	echo "YahooWin3(350,'$page?popup=yes&hostname={$_GET["hostname"]}&ou={$_GET["ou"]}','$title');";
	}
	
	
function popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$main=new maincf_multi($_GET["hostname"],$_GET["ou"]);
	$freeze_delivery_queue=$main->GET('freeze_delivery_queue');
	
	if($freeze_delivery_queue==1){$value=0;}else{$value=1;}
	
	$html="
	<div id='freeze-id'>
	".Paragraphe_switch_img("{pause_the_queue}","{pause_the_queue_explain}","freeze",$value,null,320).
	"<hr>
	<div style='text-align:right'>". button("{apply}","SaveFreeze()")."</div>
	</div>
	<script>
		var x_SaveFreeze= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			YahooWin3Hide();
			if(document.getElementById('postfix-multi-servers-listed')){
				RefreshPostfixMultiList();
			}
			
		}	
	
	
			function SaveFreeze(site){
			var XHR = new XHRConnection();
			XHR.appendData('ou','{$_GET["ou"]}');
			XHR.appendData('hostname','{$_GET["hostname"]}');
			XHR.appendData('freeze_delivery_queue',document.getElementById('freeze').value);
			document.getElementById('freeze-id').innerHTML=\"<center style='margin:10px'><img src='img/wait_verybig.gif'></center>\";
			XHR.sendAndLoad(\"$page\", 'GET',x_SaveFreeze);		
		}
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
}

function save(){
	$page=CurrentPageName();
	$tpl=new templates();
	$main=new maincf_multi($_GET["hostname"],$_GET["ou"]);
	if($_GET["freeze_delivery_queue"]==1){$main->SET_VALUE("freeze_delivery_queue",0);}else{$main->SET_VALUE("freeze_delivery_queue",1);}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-freeze=yes&hostname={$_GET["hostname"]}");
}
	
