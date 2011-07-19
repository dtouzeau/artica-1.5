<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
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
	if(isset($_GET["EnableSquidGuardHTTPService"])){save();exit;}
	if(isset($_GET["popup"])){popup();exit;}
	
js();	
	
function js(){
	$tpl=new templates();
	$page=CurrentPageName();
	$title=$tpl->_ENGINE_parse_body("{banned_page_webservice}");
	
	$html="
		YahooWin5('500','$page?popup=yes','$title');
	";
	echo $html;
		
		
}


function popup(){
	$page=CurrentPageName();
	$sock=new sockets();
	$EnableSquidGuardHTTPService=$sock->GET_INFO("EnableSquidGuardHTTPService");
	if(strlen(trim($EnableSquidGuardHTTPService))==0){$EnableSquidGuardHTTPService=1;}
	
	
	
	$SquidGuardApachePort=$sock->GET_INFO("SquidGuardApachePort");
	if($SquidGuardApachePort==null){$SquidGuardApachePort=9020;}
	
	$SquidGuardIPWeb=$sock->GET_INFO("SquidGuardIPWeb");
	$fulluri=$sock->GET_INFO("SquidGuardIPWeb");
	if($SquidGuardIPWeb==null){
			$SquidGuardIPWeb="http://".$_SERVER['SERVER_ADDR'].':'.$SquidGuardApachePort."/exec.squidguard.php";
			$fulluri="http://".$_SERVER['SERVER_ADDR'].':'.$SquidGuardApachePort."/exec.squidguard.php";
	}	
	$SquidGuardIPWeb=str_replace("http://",null,$SquidGuardIPWeb);
	$SquidGuardIPWeb=str_replace("https://",null,$SquidGuardIPWeb);
	
	if(preg_match("#\/(.+?):([0-9]+)\/#",$SquidGuardIPWeb,$re)){$SquidGuardIPWeb="{$re[1]}:{$re[2]}";}
	
	if(preg_match("#(.+?):([0-9]+)#",$SquidGuardIPWeb,$re)){
		$SquidGuardServerName=$re[1];
		$SquidGuardApachePort=$re[2];
	}		
		
		

	
	
	$html="
	<div id='EnableSquidGuardHTTPServiceDiv'>
	<div class=explain>{banned_page_webservice_text}</div>
	<hr>
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:13px'>{enable_http_service}:</td>
		<td>". Field_checkbox("EnableSquidGuardHTTPService",1,$EnableSquidGuardHTTPService,"EnableSquidGuardHTTPService()")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{listen_port}:</td>
		<td>". Field_text("listen_port_squidguard",$SquidGuardApachePort,"font-size:13px;padding:3px;width:60px",null,null,null,false,"")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{servername}:</td>
		<td style='font-size:13px'>". Field_text("servername_squidguard",$SquidGuardServerName,"font-size:13px;padding:3px;width:180px",null,null,null,false,"")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{uri}:</td>
		<td style='font-size:13px'>". Field_text("fulluri","$fulluri","font-size:13px;padding:3px;width:290px",null,null,null,false,"")."</td>
	</tr>	
	<tr>
		<td colspan=2 align='right'><hr>". button("{apply}","SaveSquidGuardHTTPService()")."</td>
	</tr>	
	</table>
	</div>
	<script>
		function EnableSquidGuardHTTPService(){
			 document.getElementById('listen_port_squidguard').disabled=true;
			 document.getElementById('servername_squidguard').disabled=true;
			 document.getElementById('fulluri').disabled=true;
			 
			 if(document.getElementById('EnableSquidGuardHTTPService').checked){
			 	document.getElementById('listen_port_squidguard').disabled=false;
			 	document.getElementById('servername_squidguard').disabled=false;
			 }else{
			 	document.getElementById('fulluri').disabled=false;
			 }
		
		}
		
var x_SaveSquidGuardHTTPService=function(obj){
	  YahooWin5Hide();
      Loadjs('$page');
	}

	function SaveSquidGuardHTTPService(){
      var XHR = new XHRConnection();
     if(document.getElementById('EnableSquidGuardHTTPService').checked){XHR.appendData('EnableSquidGuardHTTPService',1);}else{XHR.appendData('EnableSquidGuardHTTPService',0);}
     XHR.appendData('listen_port_squidguard',document.getElementById('listen_port_squidguard').value);
     XHR.appendData('servername_squidguard',document.getElementById('servername_squidguard').value);
     XHR.appendData('fulluri',document.getElementById('fulluri').value);
     document.getElementById('EnableSquidGuardHTTPServiceDiv').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>'; 
     XHR.sendAndLoad('$page', 'GET',x_SaveSquidGuardHTTPService);     	
	
	}
	
	EnableSquidGuardHTTPService();";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function save(){
	
	$sock=new sockets();
	if($_GET["EnableSquidGuardHTTPService"]==0){
		$SquidGuardIPWeb=$_GET["fulluri"];
	}else{
		$SquidGuardIPWeb="http://".$_GET["servername_squidguard"].":".$_GET["listen_port_squidguard"]."/exec.squidguard.php";
	}
	
	
	$sock->SET_INFO("SquidGuardApachePort",$_GET["listen_port_squidguard"]);
	$sock->SET_INFO("EnableSquidGuardHTTPService",$_GET["EnableSquidGuardHTTPService"]);
	$sock->SET_INFO("SquidGuardIPWeb",$SquidGuardIPWeb);
	$sock->getFrameWork("cmd.php?squid-wrapzap=yes");
	$sock->getFrameWork("cmd.php?reload-squidguardWEB=yes");
	$dans=new dansguardian_rules();
	$dans->RestartFilters();	
	
	
}

?>