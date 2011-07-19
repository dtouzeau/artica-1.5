<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.dansguardian.inc');
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");	
	$user=new usersMenus();
	if(!$user->AsSquidAdministrator){
		$tpl=new templates();
		echo "alert('".$tpl->javascript_parse_text("{ERROR_NO_PRIVS}").");";
		exit;
		
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["compile-db-perform"])){compile();exit;}
	if(isset($_GET["compile-logs"])){compilelogs();exit;}
	if(isset($_GET["status"])){status();exit;}
	
js();


function js(){
$page=CurrentPageName();	
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{squidguard_status}');
$compile_squidguard_db_ask=$tpl->javascript_parse_text('{compile_squidguard_db_ask}');

$html="
	var CompileSquidguardbLOOP_tant=0;
	function squidguard_status_load(){
		YahooWin3(650,'$page?popup=yes&compile={$_GET["compile"]}','$title',''); 
		
	}	
	
	
	var x_CompileSquidguarddb= function (obj) {
		var res=obj.responseText;
		if (res.length>0){alert(res);}
		CompileSquidguardbLOOP();
	}		
	
	
	function CompileSquidguarddb(){
		if(confirm('$compile_squidguard_db_ask')){
			var XHR = new XHRConnection();
			XHR.appendData('compile-db-perform','yes');
			document.getElementById('compile-db-status').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_CompileSquidguarddb);
		}
	}
	
function CompileSquidguardbLOOP(){
	CompileSquidguardbLOOP_tant =CompileSquidguardbLOOP_tant+1;
	if(!document.getElementById('compile-db-status')){return;}
	if (CompileSquidguardbLOOP_tant < 15 ) {                           
		setTimeout(\"CompileSquidguardbLOOP()\",2000);
      } else {
		CompileSquidguardbLOOP_tant = 0;
		CompileSquidguardbLogs();
		dansGuardianStatus();
		CompileSquidguardbLOOP();
		                              
   }
}

function CompileSquidguardbLogs(){
	LoadAjax('compile-db-status','$page?compile-logs=yes');
}

function dansGuardianStatus(){
	LoadAjax('status','$page?status=yes');
}
	
	
	squidguard_status_load();";
	
	echo $html;
	
	
	
}


function status(){
	$sock=new sockets();
	$datas=base64_decode($sock->getFrameWork("cmd.php?squidguard-status=yes"));
	$ini=new Bs_IniHandler();
	$ini->loadString($datas);
	$tpl=new templates();
	$status=DAEMON_STATUS_ROUND("APP_SQUIDGUARD",$ini);
	echo $tpl->_ENGINE_parse_body($status);	
}

function popup(){
	$tpl=new templates();
	if($_GET["compile"]=="yes"){$button_compile=button("{compile_squidguard_databases}","CompileSquidguarddb()");	}
	$page=CurrentPageName();
	$html="
	<div id='status'>$status</div>
	<div><center style='padding:5px;'>$button_compile</center></div>
	<div id='compile-db-status' style='height:250px;overflow:auto'></div>
	
	<script>
	LoadAjax('status','$page?status=yes');
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function compile(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?compile-squidguard-db=yes");
	$dans=new dansguardian_rules();
	$dans->RestartFilters();
	
}

function compilelogs(){
	$l="ressources/logs/squidguard.compile.db.txt";
	if(!is_file($l)){
		echo "<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>";
		return ;
	}
	
	$f=explode("\n",@file_get_contents($l));
	while (list ($index, $line) = each ($f)){
		echo "<div style='padding:3px'><code>$line</code></div>\n";
	}
	
}

///usr/share/artica-postfix/ressources/logs/squidguard.compile.db.txt





?>