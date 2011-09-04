<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.backuppc.inc');
	if(posix_getuid()==0){die();}
	
	$user=new usersMenus();
	if($user->AsSystemAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["popup-status"])){popup_status();exit;}
	if(isset($_GET["popup-settings"])){popup_settings();exit;}
	if(isset($_GET["EnableBackupPc"])){Save();exit;}
	js();
	
	
function js() {

	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{APP_BACKUPPC}");
	$page=CurrentPageName();
	
	$start="BACKUPPC_START()";
	if(isset($_GET["in-front-ajax"])){$start="BACKUPPC_START2()";}
	
	$html="
	
	function BACKUPPC_START(){YahooWin2('650','$page?popup=yes','$title');}
	
	function BACKUPPC_START2(){
		$('#BodyContent').load('$page?popup=yes');}		
	

	
	$start;
	";
	
	echo $html;	
	
}

function popup(){
	$page=CurrentPageName();
	$array["popup-status"]='{status}';
	$array["popup-settings"]='{parameters}';
	//$array["popup-bandwith"]='{bandwith}';
	
	
	
	

	
	$tpl=new templates();

	while (list ($num, $ligne) = each ($array) ){
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_backuppc style='width:100%;height:350px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_backuppc').tabs();
			
			
			});
		</script>";	
}

function popup_status(){
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString(base64_decode($sock->getFrameWork('cmd.php?backuppc-ini-status=yes')));
	$status=DAEMON_STATUS_ROUND("APP_BACKUPPC",$ini);
	$page=CurrentPageName();
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top'>
			<img src='img/backuppc-128.png'>
			<hr>
		</td>
		<td valign='top'>
			$status		
		</td>			
	</tr>
	</table>
	
				<div style='font-size:14px'>
			{APP_BACKUPPC_TEXT}
			</div>
	
	<div id='users-backuppc-table'></div>
	
	<script>
		//LoadAjax('users-backuppc-table','$page?popup-users=yes');
	</script>
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function popup_settings(){
	
	for($i=1;$i<121;$i++){
		$t=$i;
		if($i<10){$t="0$i";}
		$days[$i]=$t;
	}	
	
	
	$sock=new sockets();
	$EnableBackupPc=$sock->GET_INFO("EnableBackupPc");
	if(!is_numeric($EnableBackupPc)){$EnableBackupPc=0;}
	
	$lang["en"]="English";
	$lang["fr"]="Francais";
	$lang["pt_br"]="Portugues";
	$lang["nl"]="Dutch";
	$lang["es"]="Espanol";
	$lang["it"]="Italiano";
	$lang["de"]="Deutsch";
	
	$array=unserialize(base64_decode($sock->GET_INFO("BackupPCGeneralConfig")));

if($array["Language"]==null){$array["Language"]="en";}
if($array["MaxBackups"]==null){$array["MaxBackups"]="4";}
if($array["MaxBackupPCNightlyJobs"]==null){$array["MaxBackupPCNightlyJobs"]="2";}
if($array["BackupPCNightlyPeriod"]==null){$array["BackupPCNightlyPeriod"]="1";}
if($array["TopDir"]==null){$array["TopDir"]="/var/lib/backuppc";}
if($array["MaxOldLogFiles"]==null){$array["MaxOldLogFiles"]="14";}	
	
	
	
	$html="
	<div id='backuppcparamsG'>
	<table style='width:100%'>
	<tr>
		<td style='font-size:13px' class=legend>{activate}:</td>
		<td style='font-size:13px'>". Field_checkbox("EnableBackupPc",1,$EnableBackupPc)."</td>
		<td></td>
	</tr>	
	
	
	<tr>
		<td style='font-size:13px' class=legend>{language}:</td>
		<td style='font-size:13px'>". Field_array_Hash($lang,"Language",$array["Language"],null,null,0,"font-size:13px;padding:3px")."&nbsp;</td>
		<td></td>
	</tr>	
	<tr>
		<td style='font-size:13px' class=legend>{MaxBackups}:</td>
		<td style='font-size:13px'>". Field_array_Hash($days,"MaxBackups",$array["MaxBackups"],null,null,0,"font-size:13px;padding:3px")."&nbsp;</td>
		<td>". help_icon("{MaxBackups_text}")."</td>
	</tr>
	<tr>
		<td style='font-size:13px' class=legend>{MaxOldLogFiles}:</td>
		<td style='font-size:13px'>". Field_array_Hash($days,"MaxOldLogFiles",$array["MaxOldLogFiles"],null,null,0,"font-size:13px;padding:3px")."&nbsp;</td>
		<td>". help_icon("{MaxOldLogFiles_text}")."</td>
	</tr>	
	<tr>
		<td style='font-size:13px' class=legend>{MaxBackupPCNightlyJobs}:</td>
		<td style='font-size:13px'>". Field_array_Hash($days,"MaxBackupPCNightlyJobs",$array["MaxBackupPCNightlyJobs"],null,null,0,"font-size:13px;padding:3px")."&nbsp;</td>
		<td>". help_icon("{MaxBackupPCNightlyJobs_text}")."</td>
	</tr>
	<tr>
		<td style='font-size:13px' class=legend>{BackupPCNightlyPeriod}:</td>
		<td style='font-size:13px'>". Field_array_Hash($days,"BackupPCNightlyPeriod",$array["BackupPCNightlyPeriod"],null,null,0,"font-size:13px;padding:3px")."&nbsp;{days}</td>
		<td>". help_icon("{BackupPCNightlyPeriod_text}")."</td>
	</tr>
	<tr>
		<td style='font-size:13px' class=legend>{TopDir}:</td>
		<td>". Field_text("TopDir",$array["TopDir"],"font-size:13px;padding:3px;width:245px")."
		&nbsp;<input type='button' value='{browse}...' OnClick=\"javascript:Loadjs('SambaBrowse.php?no-shares=yes&field=TopDir');\"></td>
		<td>". help_icon("{TopDir_text}")."</td>
	</tr>	
	<tr>
		<td colspan=3 align='right'><hr>".button("{apply}","SaveGenBackupPC()")."</td>
	</tr>
	</table>
	</div>
	
	<script>
	
	var x_SaveGenBackupPC=function(obj){
      var tempvalue=obj.responseText;
      if(tempvalue.length>3){alert(tempvalue);}
     document.getElementById('imgftp').innerHTML='<center style=\"width:100%\">&nbsp;</center>';
      
      }	

	function SaveGenBackupPC(){
		var XHR = new XHRConnection();
		XHR.appendData('Language',document.getElementById('Language').value);
		XHR.appendData('MaxBackups',document.getElementById('MaxBackups').value);
		XHR.appendData('MaxBackupPCNightlyJobs',document.getElementById('MaxBackupPCNightlyJobs').value);
		XHR.appendData('TopDir',document.getElementById('TopDir').value);
		XHR.appendData('MaxOldLogFiles',document.getElementById('MaxOldLogFiles').value);
		
		
		
		if(document.getElementById('EnableBackupPc').checked){XHR.appendData('EnableBackupPc','1');}else{XHR.appendData('EnableBackupPc','0');}
		XHR.appendData('BackupPCNightlyPeriod',document.getElementById('BackupPCNightlyPeriod').value);
		document.getElementById('backuppcparamsG').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_SaveGenBackupPC);		
	
	}	
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function Save(){
	
	$sock=new sockets();
	$sock->GET_INFO("EnableBackupPc",$_GET["EnableBackupPc"]);
	
	$f=base64_encode(serialize($_GET));
	$sock->SaveConfigFile($f,"BackupPCGeneralConfig");
	$sock->getFrameWork("cmd.php?restart-backuppc=yes");
}



?>