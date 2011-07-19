<?php
include_once(dirname(__FILE__) . '/ressources/class.main_cf.inc');
include_once(dirname(__FILE__) . '/ressources/class.ldap.inc');
include_once(dirname(__FILE__) . "/ressources/class.sockets.inc");
include_once(dirname(__FILE__) . "/ressources/class.os.system.inc");
include_once(dirname(__FILE__) . "/ressources/class.samba.inc");


	$user=new usersMenus();
	if(($user->AsSystemAdministrator==false) OR ($user->AsSambaAdministrator==false)) {
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}');");
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["usblist"])){echo usblist();exit;}

	
	
js();



function js(){
	$page=CurrentPageName();
	$prefix=str_replace('.','_',$page);
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{APP_DEVICE_CONTROL}');
	
	
$html="
	var {$prefix}timeout=0;
	var {$prefix}timerID  = null;
	var {$prefix}tant=0;
	var {$prefix}reste=0;	


	function {$prefix}LoadPage(){
		LoadWinORG(650,'$page?popup=yes','$title');
		{$prefix}ChargeTimeout();
	
	}
	
	function {$prefix}demarre(){
		if(!WinORGOpen()){return false;}
		{$prefix}tant = {$prefix}tant+1;
		{$prefix}reste=5-{$prefix}tant;
			if ({$prefix}tant <10 ) {                           
				{$prefix}timerID = setTimeout(\"{$prefix}demarre()\",1000);
		      } else {
						if(!WinORGOpen()){return false;}
						{$prefix}tant = 0;
						LoadAjax('usblistbrowse','$page?usblist=yes');
						{$prefix}demarre();
		   }
	}	
	
	function {$prefix}ChargeTimeout(){
		{$prefix}timeout={$prefix}timeout+1;
		if({$prefix}timeout>20){
			alert('time-out $page');
			return false;
		}
		
		if(!document.getElementById('usblistbrowse')){
			setTimeout(\"{$prefix}ChargeTimeout()\",900);
			return false;
			}
		
		{$prefix}timeout=0;
		LoadAjax('usblistbrowse','$page?usblist=yes');
		{$prefix}demarre();
		
	}
	
	{$prefix}LoadPage();
";	

	echo $html;
	
}


function popup(){
	
	$html="
	<div class=explain>{APP_DEVICE_CONTROL_TEXT}</div>
	<div id='usblistbrowse' style='height:400px;width:100%;overflow:auto'></div>";	
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function usblist(){
	$sock=new sockets();
	$tpl=new templates();
	$sock->getFrameWork("cmd.php?usb-scan-write=yes");
	if(!file_exists('ressources/usb.scan.inc')){
		return $tpl->_ENGINE_parse_body("<H1>{error_no_socks}</H1>");
		}
	include("ressources/usb.scan.inc");
	include_once("ressources/class.os.system.tools.inc");
	$os=new os_system();
	$count=0;

	$table="<table style='width:99%'><tr>";
	
	while (list ($num, $usb_data_array) = each ($_GLOBAL["usb_list"]) ){
		$uiid=$num;
		$datas=$os->usb_parse_array($usb_data_array,false);
		if($datas==null){continue;}
		$count=$count+1;
		if($count>2){$table=$table."<tr></tr>";$count=0;}
		$table=$table."<td>";
		$table=$table.$datas;
		$table=$table."</td>";
	}
	
	$tpl=new templates();
	
	return $tpl->_ENGINE_parse_body($table);
	
	
}



	

?>