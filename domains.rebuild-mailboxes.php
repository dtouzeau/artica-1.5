<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.mimedefang.inc');	
	include_once('ressources/class.apache.inc');
	include_once('ressources/class.lvm.org.inc');
	
	
	if(!security()){
		$tpl=new templates();
		$error=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
		
	}

	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["rebuild_mailboxes_perform"])){rebuild_mailboxes_perform();exit;}
	if(isset($_GET["rebuild_mailboxes_events"])){rebuild_mailboxes_events();exit;}

js();	
	
	
function js(){
	
$page=CurrentPageName();
	//&ou=$ou&domain=$num
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{rebuild_mailboxes_org}');
	$page=CurrentPageName();
	$ou=base64_decode($_GET["ou"]);
	
	$html="
	
var rebuild_mailboxes_counter=0;

function rebuild_mailboxes_schedule(){
	if(!YahooWin3Open()){return;}
	rebuild_mailboxes_counter =rebuild_mailboxes_counter+1;
	if (rebuild_mailboxes_counter < 10 ) {                           
		setTimeout(\"rebuild_mailboxes_schedule()\",2000);
      } else {
		rebuild_mailboxes_counter= 0;
		rebuild_mailboxes_events();
		rebuild_mailboxes_schedule();                              
   }
}	
	
	
	function rebuild_mailboxes_org_start(){
			YahooWin3(600,'$page?popup=yes&ou=". base64_decode($_GET["ou"])."','$title::$ou');
			setTimeout(\"rebuild_mailboxes_schedule()\",2000);
		
		}
		
	var x_rebuild_mailboxes_perform= function (obj) {
		var response=obj.responseText;
		if(response.length>0){alert(response);}
	    rebuild_mailboxes_events();
	}			
		
	function rebuild_mailboxes_perform(){
		document.getElementById('rebuild_mailboxes_logs').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		var XHR = new XHRConnection();
		XHR.appendData('rebuild_mailboxes_perform','yes');
		XHR.appendData('ou','{$_GET["ou"]}');
		XHR.sendAndLoad('$page', 'GET',x_rebuild_mailboxes_perform);
	}
	
	var x_rebuild_mailboxes_events= function (obj) {
		var response=obj.responseText;
		if(response.length>0){document.getElementById('rebuild_mailboxes_logs').innerHTML=response;}
	}	
	
	function rebuild_mailboxes_events(){
		var XHR = new XHRConnection();
		XHR.appendData('rebuild_mailboxes_events','yes');
		XHR.appendData('ou','{$_GET["ou"]}');
		XHR.sendAndLoad('$page', 'GET',x_rebuild_mailboxes_events);
	}
	
	
	
rebuild_mailboxes_org_start()";

		
		echo $html;
	
}


function popup(){
	
	$html="
	
		<p style='font-size:13px'><div style='float:right;font-size:13px'>". imgtootltip("48-refresh.png","{refresh}","rebuild_mailboxes_events()")."</div>
	<span style='font-size:13px'>{rebuild_mailboxes_org_explain}</p>
	
	<center style='padding:10px;margin:10px'>". button("{rebuild_mailboxes_org}","rebuild_mailboxes_perform()")."</center>
	
	<div id='rebuild_mailboxes_logs' style='width:100%;height:300px;overflow:auto'></div>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("$html");
	
	
}

function rebuild_mailboxes_perform(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?cyrus-rebuild-all-mailboxes={$_GET["ou"]}");
	
}

function rebuild_mailboxes_events(){
	$f="ressources/logs/web/". md5($_GET["ou"])."-mailboxes-rebuilded.log";
	$datas=explode("\n",@file_get_contents($f));
	if(!is_array($datas)){return null;}
	
	while (list ($num, $ligne) = each ($datas) ){
		if(trim($ligne)==null){continue;}
		$html[]= "<div style=\"padding:3px\"><code>$ligne<code></div>\n";
	}
	
	
	if(is_array($html)){
		krsort($html);
		$tpl=new templates();
		$html=implode("\n",$html);
		echo $tpl->_ENGINE_parse_body($html);	
	}
	
}


	
	
function security(){
	if(!isset($_GET["ou"])){return false;}
	
	$user=new usersMenus();
	if($user->AsOrgAdmin){return true;}
	return false;
	
}
?>