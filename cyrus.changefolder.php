<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.mysql.inc');	
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.cyrus.inc');

	$users=new usersMenus();
	if(!$users->AsPostfixAdministrator){
		$tpl=new templates();
		$error=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["default_directory_size"])){default_directory_size();exit;}
	if(isset($_GET["current_directory_size"])){current_directory_size();exit;}
	if(isset($_GET["CyrusMoveToCurrent"])){CyrusMoveToCurrent();exit;}
	if(isset($_GET["CyrusSaveNewDir"])){CyrusSaveNewDir();exit;}
	if(isset($_GET["cyruslogs"])){cyruslogs();exit;}
	js();
	
function js(){
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{cyrus_change_folder}');
	$page=CurrentPageName();

$html="
var cyrus_change_folder_tant=0;

function cyrus_change_folder_schedule(){
	if(!YahooWinOpen()){return;}
	cyrus_change_folder_tant =cyrus_change_folder_tant+1;
	if (cyrus_change_folder_tant < 10 ) {                           
		setTimeout(\"cyrus_change_folder_schedule()\",2000);
      } else {
		cyrus_change_folder_tant= 0;
		ChargeCyrusPartitionDefaultSize();
		cyrus_change_folder_schedule();
		                              
   }
}



function cyrus_change_folder_load(){
	YahooWin('630','$page?popup=yes','$title');
}
	
var x_current_directory_size=function (obj) {
	tempvalue=obj.responseText;
	document.getElementById('current_directory_id').innerHTML=tempvalue;
	cyruslogsCheck();
    }

var x_cyruslogsCheck=function (obj) {
	tempvalue=obj.responseText;
	document.getElementById('cyruslogs').innerHTML=tempvalue;
    }	    
	
var x_default_directory_size=function (obj) {
	tempvalue=obj.responseText;
	document.getElementById('default_directory_id').innerHTML=tempvalue;
	var XHR = new XHRConnection();
	XHR.appendData('current_directory_size','yes');
	XHR.sendAndLoad('$page', 'GET',x_current_directory_size);	
    }	
	
function ChargeCyrusPartitionDefaultSize(){
	document.getElementById('default_directory_id').innerHTML='<img src=\"img/wait.gif\">';
	document.getElementById('current_directory_id').innerHTML='<img src=\"img/wait.gif\">';
	var XHR = new XHRConnection();
	XHR.appendData('default_directory_size','yes');
	XHR.sendAndLoad('$page', 'GET',x_default_directory_size);
}

function cyruslogsCheck(){
	document.getElementById('cyruslogs').innerHTML='<img src=\"img/wait.gif\">';
	var XHR = new XHRConnection();
	XHR.appendData('cyruslogs','yes');
	XHR.sendAndLoad('$page', 'GET',x_cyruslogsCheck);
}

var x_CyrusMoveToCurrent=function (obj) {
	tempvalue=obj.responseText;
	if(tempvalue.length>3){alert(tempvalue);}
    }

function CyrusMoveToCurrent(){
	var XHR = new XHRConnection();
	XHR.appendData('CyrusMoveToCurrent','yes');
	XHR.sendAndLoad('$page', 'GET',x_CyrusMoveToCurrent);	
}

function CyrusSaveNewDir(){
	var newtdir=document.getElementById('newtdir').value;
	var XHR = new XHRConnection();
	XHR.appendData('CyrusSaveNewDir',newtdir);
	XHR.sendAndLoad('$page', 'GET',x_CyrusMoveToCurrent);	
}
	


cyrus_change_folder_load();";
	
	echo $html;
	
	
}

function save(){
	$sock=new sockets();
	$sock->SET_INFO("PopHackEnabled",$_GET["PopHackEnabled"]);
	$sock->SET_INFO("PopHackCount",$_GET["PopHackCount"]);
	$sock->getFrameWork("cmd.php?restart-artica-maillog=yes");
}


function popup(){
	
	$sock=new sockets();
	$directorypath=base64_decode($sock->getFrameWork("cmd.php?cyrus-get-partition-default=yes"));
	
	$html="
	<div id=cyrus_change_folder_id>
	<p style='font-size:13px'><div style='float:right;font-size:13px'>". imgtootltip("48-refresh.png","{refresh}","ChargeCyrusPartitionDefaultSize()")."</div>
	<span style='font-size:13px'>{cyrus_change_folder_explain}</p>
	<table style='width:100%'>
	<tr>
		<td valign='top' class=legend>{default_directory}: <strong><code>/var/spool/cyrus/mail</strong></code>:</td>
		<td valign='top'><div id='default_directory_id' style='font-size:13px;font-weight:bolder'></div></td>
		<td valign='top'>". imgtootltip("fleche-20.png","{move_to}:{current_directory}","CyrusMoveToCurrent()")."</td>
	</tr>
	<tr>
		<td valign='top' class=legend>{current_directory}: <strong><code>$directorypath</code></strong>:</td>
		<td valign='top'><div id='current_directory_id' style='font-size:13px;font-weight:bolder'></div></td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td valign='top' class=legend>{next_directory}:</td>
		<td valign='top'>". Field_text("newtdir",null,"font-size:13px;padding:3px;width:100%")."</td>
		<td>". button("{browse}","Loadjs('SambaBrowse.php?no-shares=yes&field=newtdir&no-hidden=yes')")."</td>
	</tr>		
	<table>
	
	</table>
	<hr>
	<div style='width:100%;text-align:right'>". button("{apply}","CyrusSaveNewDir()")."</div>
	</div>
	<hr style='border-color:#005447'>
	<div id='cyruslogs' style='width:100%;height:220px;overflow:auto'></div>
	
	<script>
		ChargeCyrusPartitionDefaultSize();
	</script>
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function default_directory_size(){
	
	$sock=new sockets();
	$CyrusImapPartitionDefaultSize=$sock->GET_INFO("CyrusImapPartitionDefaultDirSize");
	if(trim($CyrusImapPartitionDefaultSize)==null){
		$tpl=new templates();
		$CyrusImapPartitionDefaultSize=$tpl->_ENGINE_parse_body("{unknown}");
	}
	
	echo $CyrusImapPartitionDefaultSize;
}
function current_directory_size(){
	$sock=new sockets();
	$CyrusImapPartitionDefaultSize=$sock->GET_INFO("CyrusImapPartitionDefaultSize");
	if(trim($CyrusImapPartitionDefaultSize)==null){
		$tpl=new templates();
		$CyrusImapPartitionDefaultSize=$tpl->_ENGINE_parse_body("{unknown}");
	}
	
	echo $CyrusImapPartitionDefaultSize;
}
function CyrusMoveToCurrent(){
	$logs="/usr/share/artica-postfix/ressources/logs/cyrus_dir_logs";
	@unlink($logs);
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?cyrus-MoveDefaultToCurrentDir=yes");	
	$tpl=new templates();
	echo $tpl->javascript_parse_text("{apply_upgrade_help}");	
}

function CyrusSaveNewDir(){
	$logs="/usr/share/artica-postfix/ressources/logs/cyrus_dir_logs";
	@unlink($logs);
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?cyrus-SaveNewDir=".base64_encode($_GET["CyrusSaveNewDir"]));	
	$tpl=new templates();
	echo $tpl->javascript_parse_text("{apply_upgrade_help}");
}
function cyruslogs(){
	$datas=explode("\n",@file_get_contents("/usr/share/artica-postfix/ressources/logs/cyrus_dir_logs"));
	if(!is_array($datas)){return null;}
	
	while (list ($num, $ligne) = each ($datas) ){
		if(trim($ligne)==null){continue;}
		$html[]= "<div style=\"padding:3px\"><code>$ligne<code></div>\n";
	}
	
	
	if(is_array($html)){
		krsort($html);
		echo implode("\n",$html);}
		
}



?>