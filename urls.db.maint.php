<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.cron.inc');
	
	
	
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
	if(isset($_GET["DBH"])){save();exit;}
	if(isset($_GET["RunDBM"])){RunDBM();exit;}
	
	js();
	
	
function js(){
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{databases_maintenance}");
	$page=CurrentPageName();
	$html="YahooWin2('600','$page?popup=yes','$title')";
	echo $html;
	}
	
function popup(){
	$page=CurrentPageName();
	$sock=new sockets();
	$time=unserialize(base64_decode($sock->GET_INFO("SquidGuardMaintenanceTime")));
	$cron=new cron_macros();
	
	if($time["DBH"]==null){$time["DBH"]=23;}
	if($time["DBM"]==null){$time["DBM"]=45;}
	
	$html="
	<div id='DBMDIV'>
	
	<table style='width:100%'>
	<tr>
		<td width=1% valign='top'><img src='img/database-check-128.png'></td>
		
		<td valign='top'><div class=explain>{databases_maintenance_explain}</div>
			<table style='width:100%'>
			<tr>
				<td valign='middle' class=legend style='font-size:16px' width=1% nowrap>{time}:</td>
				<td valign='middle' class=legend style='font-size:16px' width=1% nowrap>".Field_array_Hash($cron->cron_hours,"DBH",$time["DBH"],null,null,0,"font-size:16px;padding:3px")."</td>
				<td valign='middle' class=legend style='font-size:16px' width=1% nowrap>:</td>
				<td valign='middle' class=legend style='font-size:16px' width=1% nowrap>".Field_array_Hash($cron->cron_mins,"DBM",$time["DBM"],null,null,0,"font-size:16px;padding:3px")."</td>
				<td valign='middle' class=legend width=99% nowrap>". button("{apply}","SaveDBM()")."</td>
			</tr>
			</table>
			<div style='margin-top:10px'>&nbsp;</div>
			<center style='margin:10px;'><hr>". button("{run_maintenance_now}","RunDBM()")."<hr></center>
			
		</td>
	</tr>
	</table>
	</div>
	
	<script>
	var x_SaveDBM= function (obj) {
		var res=obj.responseText;
		if (res.length>0){alert(res);}
		YahooWin2Hide();
	}		
	
function SaveDBM(){
		var XHR = new XHRConnection();
		XHR.appendData('DBH',document.getElementById('DBH').value);
		XHR.appendData('DBM',document.getElementById('DBM').value);
		document.getElementById('DBMDIV').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_SaveDBM);
		 		
	}

	var x_SaveDBM= function (obj) {
		var res=obj.responseText;
		if (res.length>0){alert(res);}
		YahooWin2Hide();
	}		
	
function RunDBM(){
		var XHR = new XHRConnection();
		XHR.appendData('RunDBM',1);
		document.getElementById('DBMDIV').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_SaveDBM);
		 		
	}		
	
	</script>
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function save(){
	$sock=new sockets();
	$sock->SaveConfigFile(base64_encode(serialize($_GET)),"SquidGuardMaintenanceTime");
	$sock->getFrameWork("cmd.php?squidguard-db-maint=yes");
	
}

function RunDBM(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?squidguard-db-maint-now=yes");
	$tpl=new templates();
	echo $tpl->javascript_parse_text("{apply_upgrade_help}");
}
	
	

?>