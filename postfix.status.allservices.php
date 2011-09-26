<?php
	if(posix_getuid()==0){die();}
	session_start();
	if($_SESSION["uid"]==null){echo "window.location.href ='logoff.php';";die();}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.main_cf.inc');

	$user=new usersMenus();
	if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	if(isset($_GET["popup"])){popup();exit;}
	js();
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title="{POSTFIX_ALL_SERVICES}";
	$html="YahooWin6(550,'$page?popup=yes','$title')";
	echo $html;
	
}

function popup(){
		
	$tpl=new templates();
	if(is_file("ressources/logs/global.status.ini")){
		
		$ini=new Bs_IniHandler("ressources/logs/global.status.ini");
	}else{
		writelogs("ressources/logs/global.status.ini no such file");
		$sock=new sockets();
		$datas=base64_decode($sock->getFrameWork('cmd.php?Global-Applications-Status=yes'));
		$ini=new Bs_IniHandler($datas);
	}
	
	$sock=new sockets();
	$datas=$sock->getFrameWork('cmd.php?refresh-status=yes');
	

	$array=array_postfix_status();

	
	
	while (list ($num, $ligne) = each ($array) ){
		$st=DAEMON_STATUS_LINE($ligne,$ini,null,1);
		if($st==null){continue;}
		$status=$status .$st."\n";
	}
	
	
	echo $tpl->_ENGINE_parse_body($status);
	
}