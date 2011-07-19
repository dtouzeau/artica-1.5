<?php
session_start();
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.user.inc');
	include_once('ressources/class.computers.inc');
	include_once('ressources/class.backuppc.inc');
	include_once('ressources/class.cron.inc');
	
	
	if(!Isright()){$tpl=new templates();echo "alert('".$tpl->javascript_parse_text('{ERROR_NO_PRIVS}')."');";die();}
	
	if(isset($_GET["popup"])){popup_tabs();exit;}
	if(isset($_GET["backuppc-folders"])){backuppc_folders();exit;}
	if(isset($_GET["backuppc-BlackoutPeriods"])){backuppc_BlackoutPeriods();exit;}
	if(isset($_GET["BlackoutPeriodsDays"])){BlackoutPeriodsDays();exit;}
	if(isset($_GET["BlackoutPeriodsDaysSave"])){BlackoutPeriodsDaysSave();exit;}
	if(isset($_GET["BlackoutPeriodsSave"])){BlackoutPeriodsSave();exit;}
	if(isset($_GET["backuppc-mainconf"])){mainconf();exit;}
	if(isset($_GET["FullPeriod"])){mainconfSave();exit;}
	if(isset($_GET["popup-index"])){index();exit;}
	if(isset($_GET["backuppc-status"])){backuppc_status();exit;}
	if(isset($_GET["add-shared-to-backup"])){backuppc_folders_add();exit;}
	if(isset($_GET["add-shared-to-backup-no-encoded"])){backuppc_folders_add2();exit;}
	if(isset($_GET["del-shared-to-backup"])){backuppc_folders_del();exit;}
	
	
	
js();	
function js(){
	
	$page=CurrentPageName();
	$uid=base64_decode($_GET["uid"]);
	if(strpos($uid,"$")==0){$uid=$uid.'$';}
	$title=str_replace('$','',$uid);

	$start="backuppcload()";
	
	if(isset($_GET["add-shared"])){
		$start="backuppcload_addsh()";
	}
	
	
	$html="
	function backuppcload(){
		YahooWin5(700,'$page?popup=yes&uid={$_GET["uid"]}','BackupPC::$title');
	
	}
	
	var x_backuppcload_addsh= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);return;}
		backuppcload();
		
	}

	var x_global_refresh= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);return;}
		RefreshTab('backuppc-computerinfos-tabs');
		
	}		
	
	function backuppcload_addsh(){
		var XHR = new XHRConnection();
		XHR.appendData('add-shared-to-backup','{$_GET["add-shared"]}');
		XHR.appendData('uid','{$_GET["uid"]}');
		XHR.sendAndLoad('$page', 'GET',x_backuppcload_addsh); 
	}
	
	function BackupPcDeleteSMBFolder(path_encoded){
		var XHR = new XHRConnection();
		XHR.appendData('del-shared-to-backup',path_encoded);
		XHR.appendData('uid','{$_GET["uid"]}');
		document.getElementById('backuppc-folders').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
		XHR.sendAndLoad('$page', 'GET',x_global_refresh); 	
	}

	$start
	
	";
	
	echo $html;
	
}

function backuppc_folders_add(){
	$uid=base64_decode($_GET["uid"]);
	if(strpos($uid,"$")==0){$uid=$uid.'$';}	
	$back=new backuppc($uid);
	$folder=base64_decode($_GET["add-shared-to-backup"]);
	if($folder=="IPC$"){$folder="C$";}
	$back->smb_folders[$folder]=$folder;
	$back->Save();
	
}
function backuppc_folders_add2(){
	$uid=base64_decode($_GET["uid"]);
	if(strpos($uid,"$")==0){$uid=$uid.'$';}	
	$back=new backuppc($uid);
	$folder=$_GET["add-shared-to-backup-no-encoded"];
	if($folder=="IPC$"){$folder="C$";}
	$back->smb_folders[$folder]=$folder;
	$back->Save();
	
}
function backuppc_folders_del(){
	$uid=base64_decode($_GET["uid"]);
	if(strpos($uid,"$")==0){$uid=$uid.'$';}	
	$back=new backuppc($uid);
	$folder=base64_decode($_GET["del-shared-to-backup"]);
	if($folder=="IPC$"){$folder="C$";}
	unset($back->smb_folders[$folder]);
	$back->Save();	
}


function popup_tabs(){
	$page=CurrentPageName();
	$users=new usersMenus();
	
	$a[]="<li><a href=\"$page?popup-index=yes&uid={$_GET["uid"]}\"><span>{index}</span></a></li>";
	$a[]="<li><a href=\"$page?backuppc-folders=yes&uid={$_GET["uid"]}\"><span>{folders}</span></a></li>";
	$a[]="<li><a href=\"$page?backuppc-BlackoutPeriods=yes&uid={$_GET["uid"]}\"><span>{BlackoutPeriods}</span></a></li>";
	$a[]="<li><a href=\"$page?backuppc-mainconf=yes&uid={$_GET["uid"]}\"><span>{parameters}</span></a></li>";

	
	
	
	$html="
	<div id='backuppc-computerinfos-tabs' style='background-color:white'>
	<ul>
		". implode("\n",$a)."
	</ul>
		</div>
		<script>
				$(document).ready(function(){
					$('#backuppc-computerinfos-tabs').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			

			});
		</script>
	
	";
		
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}

function backuppc_status(){
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString(base64_decode($sock->getFrameWork('cmd.php?backuppc-ini-status=yes')));
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body(DAEMON_STATUS_ROUND("APP_BACKUPPC",$ini));	
	
}


function index(){

	$uid=base64_decode($_GET["uid"]);
	$sock=new sockets();
	$comp=new computers($uid);
	$page=CurrentPageName();
	$infos=unserialize(base64_decode($sock->getFrameWork("cmd.php?backuppc-computer-infos=$comp->ComputerRealName")));
	
	if($infos["errorTime"]>0){$date_error=date('Y-m-d H:i:s',$infos["errorTime"]);}else{$date_error="&nbsp;-&nbsp;";}
	
	if($infos["startTime"]>0){$startTime=date('Y-m-d H:i:s',$infos["startTime"]);}else{$startTime="&nbsp;-&nbsp;";}	
	
	if($infos["reason"]==null){$infos["reason"]="idle";}
	if($infos["state"]==null){$infos["state"]="idle";}
	
	$html="
	<table style='width:100%'>
	<td valign='top'>
	<img src='img/backuppc_logo-240.gif'><hr><div id='backuppc-status' style='width:240px'></div><hr>
	</td>
	<td valign='top'>
		<H3>{APP_BACKUPPC}::$comp->ComputerRealName::{status}</H3>
		<table style='width:100%'>
			<tr>
				<td class=legend nowrap>{reason}:</td>
				<td style='font-size:13px'><strong>{{$infos["reason"]}}</strong>
			</tr>
			<tr>
				<td class=legend nowrap>{status}:</td>
				<td style='font-size:13px'><strong>{{$infos["state"]}}</strong>
			</tr>
			<tr>
				<td class=legend nowrap>{last_error}:</td>
				<td style='font-size:13px'><strong>$date_error:&nbsp;{$infos["error"]}</strong>
			</tr>	
			<tr>
				<td class=legend nowrap>{startTime}:</td>
				<td style='font-size:13px'><strong>$startTime</strong>
			</tr>				
		</table>
	</td>
	</tr>
	</table>

	<script>
		LoadAjax('backuppc-status','$page?backuppc-status=yes&uid=$uid');
	</script>
			
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}



function popup(){
	$comp=new computers(base64_decode($_GET["uid"]));
	$page=CurrentPageName();
	
	
	$html="
	<H3>{scheduled_backups}:$comp->ComputerRealName</H3><br>
	<table style='width:100%'>
	<tr>
		<td valign='top'><div id='backuppc-folders'></div></td>
		<td valign='top'><div id='backuppc-config'></div></td>
	</tr>
	</table>
	
	<script>
		LoadAjax('backuppc-folders','$page?uid={$_GET["uid"]}&backuppc-folders=yes');
		LoadAjax('backuppc-config','$page?uid={$_GET["uid"]}&backuppc-config=yes');
	</script>
		
	
	
	";
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
	
}

function backuppc_folders(){
	$uid=base64_decode($_GET["uid"]);
	$comp=new computers(base64_decode($_GET["uid"]));
	$backup=new backuppc($uid);
	$page=CurrentPageName();
	$html="
	<div style='font-size:14px;margin:5px'>{BACKUPPC_FOLDER_LIST_TEXT}</div>
	<div id='backuppc-folders'>
	<table style='width:100%'>";
	
	while (list ($index, $directory) = each ($backup->smb_folders) ){
		$directoryB64=base64_encode($directory);
		$html=$html."<tr ". CellRollOver().">
			<td width=1%><img src='img/shared.png'></td>
			<td><strong><code style='font-size:13px'>$directory</code></strong></td>
			<td width=1%>". imgtootltip("folder-delete-48.png","{delete}","BackupPcDeleteSMBFolder('$directoryB64')")."</td>
		</tr>";
		
		
	}
	
	$html=$html."
	<tr ". CellRollOver().">
			<td width=1%><img src='img/plus-24.png'></td>
			<td>". Field_text("FolderToAddInBackup",null,"font-size:13px;padding:3px",null,null,null,false,"FolderToAddInBackupPress(event)")."</td>
			<td width=1%>&nbsp;</td>
	
	</table>
	</div>
	
	<script>
		function FolderToAddInBackupPress(e){
			if(checkEnter(e)){
				FolderToAddInBackup();
			}
		}
		
		function FolderToAddInBackup(){
			var XHR = new XHRConnection();
			XHR.appendData('add-shared-to-backup-no-encoded',document.getElementById('FolderToAddInBackup').value);
			XHR.appendData('uid','{$_GET["uid"]}');
			document.getElementById('backuppc-folders').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
			XHR.sendAndLoad('$page', 'GET',x_global_refresh); 
		}
		
		
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}
function backuppc_BlackoutPeriods(){
	$uid=base64_decode($_GET["uid"]);
	$comp=new computers(base64_decode($_GET["uid"]));
	$backup=new backuppc($uid);
	
	
	while (list ($index, $config) = each ($backup->BlackoutPeriods_config) ){
		$list=$list.backupBlackoutExplode($_GET["uid"],$index,$config);
	}

	$list=$list.backupBlackoutExplode($_GET["uid"],$index+1,$array,true);
	
	$html="<i style='font-size:14px'>{BlackoutPeriods_text}</i>
	<hr>
		$list

	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}


function backupBlackoutExplode($uid,$index,$config=array(),$nodelete=FALSE){
	$cron=new cron_macros();
	$tpl=new templates();

	$weekdays=$tpl->_ENGINE_parse_body('{weekdays}');
	$page=CurrentPageName();
	$hourBegin=$config["hourBegin"];
	$hourEnd=$config["hourEnd"];
	$days=array();
	$days[]=texttooltip("{days}:","{edit}","javascript:BlackoutPeriodsDays{$index}($index);",null,0,"font-size:13px;float:left;margin-left:5px");
	if(is_array($config["weekDays"])){
		while (list ($day, $b) = each ($config["weekDays"]) ){
			$days[]=texttooltip($cron->cron_days[$day],"{edit}","javascript:BlackoutPeriodsDays{$index}($index);",null,0,"font-size:13px;float:left;margin-left:5px");
		}
	}	
	
	
	$delet=imgtootltip("delete-48.png","{delete}","BlackoutPeriodsDelete$index()");
	if($nodelete){$delet=null;}
	$html="
	<div id='BlackoutPeriods_$index'>
	<table style='width:100%;margin:5px;padding:5px;border:1px solid #CCCCCC'>
	<tr ". CellRollOver().">
	<td valign='top'>
		<table style='width:470px'>
		<tr>
			<td valign='top' class=legend style='font-size:13px' nowrap>{hourBegin}:</td>
			<td valign='top' style='font-size:13px' nowrap>". Field_array_Hash($cron->cron_mins,"hourBeginM$index",$hourBegin,null,null,0,"font-size:13px")."&nbsp;H&nbsp;:&nbsp;00</td>
			<td valign='top' class=legend style='font-size:13px' nowrap>{hourEnd}:</td>
			<td valign='top' style='font-size:13px' nowrap>". Field_array_Hash($cron->cron_hours,"hourEndH$index",$hourEnd,null,null,0,"font-size:13px")."&nbsp;H&nbsp;:&nbsp;00</td>
		</tr>
		
		<tr ". CellRollOver("BlackoutPeriodsDays{$index}($index)").">
			<td valign='top' class=legend style='font-size:13px' nowrap>$weekdays:</td>
			<td colspan=3>". implode(" ",$days)."</td>
		</tr>
		<tr>
			<td colspan=4 align='right'><hr>". button("{apply}","BlackoutPeriodsSave$index()")."</td>
		</tr>
		
		
		</table>
		</td>
		<td valign='middle' align='center' >$delet</td>
	
	</tr>
	</table>
	</div>
	
	<script>
	
		var x_BlackoutPeriodsSave$index= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			RefreshTab('backuppc-computerinfos-tabs');
			}	
			
		function BlackoutPeriodsSave$index(){
			var XHR = new XHRConnection();
			XHR.appendData('uid','{$_GET["uid"]}');
			XHR.appendData('BlackoutPeriodsSave','$index');
			XHR.appendData('hourBegin',document.getElementById('hourBeginM$index').value);
			XHR.appendData('hourEnd',document.getElementById('hourEndH$index').value);
			document.getElementById('BlackoutPeriods_$index').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';	
			XHR.sendAndLoad('$page', 'GET',x_BlackoutPeriodsSave$index);
		
		}
			
		function BlackoutPeriodsDays{$index}(index){
			YahooWin6('190','$page?BlackoutPeriodsDays=$index&uid=$uid','$weekdays');
		}
	</script>
	
	";
	
	
	return $tpl->_ENGINE_parse_body($html);
}

function BlackoutPeriodsSave(){
	$uid=base64_decode($_GET["uid"]);
	$backup=new backuppc($uid);
	$index=$_GET["BlackoutPeriodsSave"];
	$backup->BlackoutPeriods_config[$index]["hourBegin"]=$_GET["hourBegin"];
	$backup->BlackoutPeriods_config[$index]["hourEnd"]=$_GET["hourEnd"];
	$backup->Save();
	
}

function BlackoutPeriodsDays(){
	$cron=new cron_macros();
	$page=CurrentPageName();
	$comp=new computers(base64_decode($_GET["uid"]));
	$backup=new backuppc(base64_decode($_GET["uid"]));
	$config=$backup->BlackoutPeriods_config[$_GET["BlackoutPeriodsDays"]];
	$html="
	<div id='BlackoutPeriodsDays'>
	<table style='width:100%'>";
	
	while (list ($index, $dayText) = each ($cron->cron_days) ){
		
		$script=$script."if(document.getElementById('day_{$index}').checked){XHR.appendData('$index','1');}\n";
		
		$html=$html."<tr>
		<td width=1%>". Field_checkbox("day_{$index}",1,$config["weekDays"][$index])."</td>
		<td style='font-size:13px'>$dayText</td>
		</tr>
		";
		
	}
	
	$html=$html."
	<tr>
		<td colspan=2 align='right'>". button("{apply}","SaveBlackoutWeeksDays()")."</td>
	</tr>
	</table>
	</div>
	
	<script>
	
		
	var x_SaveBlackoutWeeksDays= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		YahooWin6Hide();	
		RefreshTab('backuppc-computerinfos-tabs');
		}	
	
	
		function SaveBlackoutWeeksDays(){
			var XHR = new XHRConnection();
			$script
			XHR.appendData('uid','{$_GET["uid"]}');
			XHR.appendData('BlackoutPeriodsDaysSave','{$_GET["BlackoutPeriodsDays"]}');
			document.getElementById('BlackoutPeriodsDays').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';	
			XHR.sendAndLoad('$page', 'GET',x_SaveBlackoutWeeksDays);
		}
	</script>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	}
	
function BlackoutPeriodsDaysSave(){
	$uid=base64_decode($_GET["uid"]);
	$backup=new backuppc($uid);
	
	if(isset($_GET["*"])){
		unset($_GET["*"]);
		for($i=0;$i<7;$i++){
			$_GET[$i]=1;
		}
	}
	
	unset($backup->BlackoutPeriods_config[$_GET["BlackoutPeriodsDaysSave"]]["weekDays"]);
	while (list ($day, $value) = each ($_GET) ){
		if(is_numeric($day)){
			if($value==1){
				$backup->BlackoutPeriods_config[$_GET["BlackoutPeriodsDaysSave"]]["weekDays"][$day]=1;
			}
		}
	}
	
	$backup->Save();
	
}

function mainconf(){
	$uid=base64_decode($_GET["uid"]);
	$backup=new backuppc($uid);
	for($i=1;$i<121;$i++){
		$t=$i;
		if($i<10){$t="0$i";}
		$days[$i]=$t;
	}
	
	$backup->flat_configs["IncrPeriod"]=$backup->flat_configs["IncrPeriod"]+0.03;
	$backup->flat_configs["FullPeriod"]=$backup->flat_configs["FullPeriod"]+0.03;
	$page=CurrentPageName();
	
	
	$html="
		
	
	<div id='backuppcmainconf'>
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:13px'>{FullPeriod}:</td>
		<td style='font-size:13px'>". Field_array_Hash($days,"FullPeriod",$backup->flat_configs["FullPeriod"],null,null,0,"font-size:13px;padding:3px")."&nbsp;{days}</td>
		<td>". help_icon("{FullPeriod_text}")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{FullAgeMax}:</td>
		<td style='font-size:13px'>". Field_array_Hash($days,"FullAgeMax",$backup->flat_configs["FullAgeMax"],null,null,0,"font-size:13px;padding:3px")."&nbsp;{days}</td>
		<td>". help_icon("{FullAgeMax_text}")."</td>
	</tr>		
	<tr>
		<td class=legend style='font-size:13px'>{PartialAgeMax}:</td>
		<td style='font-size:13px'>". Field_array_Hash($days,"PartialAgeMax",$backup->flat_configs["PartialAgeMax"],null,null,0,"font-size:13px;padding:3px")."&nbsp;{days}</td>
		<td>". help_icon("{PartialAgeMax_text}")."</td>
	</tr>		

	<tr>
		<td class=legend style='font-size:13px'>{IncrPeriod}:</td>
		<td style='font-size:13px'>". Field_array_Hash($days,"IncrPeriod",$backup->flat_configs["IncrPeriod"],null,null,0,"font-size:13px;padding:3px")."&nbsp;{days}</td>
		<td>". help_icon("{IncrPeriod_text}")."</td>
	</tr>

	<tr>
		<td class=legend style='font-size:13px'>{FullKeepCntMin}:</td>
		<td style='font-size:13px'>". Field_array_Hash($days,"FullKeepCntMin",$backup->flat_configs["FullKeepCntMin"],null,null,0,"font-size:13px;padding:3px")."&nbsp;</td>
		<td>". help_icon("{FullKeepCntMin_text}")."</td>
	</tr>	
	
	<tr>
		<td class=legend style='font-size:13px'>{IncrAgeMax}:</td>
		<td style='font-size:13px'>". Field_array_Hash($days,"IncrAgeMax",$backup->flat_configs["IncrAgeMax"],null,null,0,"font-size:13px;padding:3px")."&nbsp;{days}</td>
		<td>". help_icon("{IncrAgeMax_text}")."</td>
	</tr>

	
	
	<tr>
		<td colspan=3 align='right'><hr>". button("{apply}","BackupPcScheduleSave()")."</td></tr>
	</table>
</div>
<script>
var x_BackupPcScheduleSave= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	YahooWin6Hide();	
	RefreshTab('backuppc-computerinfos-tabs');
}	
	
	
function BackupPcScheduleSave(){
	var XHR = new XHRConnection();
	XHR.appendData('uid','{$_GET["uid"]}');
	XHR.appendData('FullPeriod',document.getElementById('FullPeriod').value);
	XHR.appendData('FullAgeMax',document.getElementById('FullAgeMax').value);
	XHR.appendData('PartialAgeMax',document.getElementById('PartialAgeMax').value);
	XHR.appendData('IncrPeriod',document.getElementById('IncrPeriod').value);
	XHR.appendData('FullKeepCntMin',document.getElementById('FullKeepCntMin').value);
	XHR.appendData('IncrAgeMax',document.getElementById('IncrAgeMax').value);
	document.getElementById('backuppcmainconf').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';	
	XHR.sendAndLoad('$page', 'GET',x_BackupPcScheduleSave);
}
</script>
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function mainconfSave(){
		$uid=base64_decode($_GET["uid"]);
		$backup=new backuppc($uid);
		
		$_GET["IncrPeriod"]=$_GET["IncrPeriod"]-0.03;
		$_GET["FullPeriod"]=$_GET["FullPeriod"]-0.03;
		
		while (list ($key, $value) = each ($_GET) ){
			$backup->flat_configs[$key]=$value;
			
		}
		
		$backup->Save();
	
}
	

function IsRight(){
	if(!isset($_GET["uid"])){return false;}
	$users=new usersMenus();
	if($users->AsArticaAdministrator){return true;}
	if($users->AsSambaAdministrator){return true;}
	if($users->AllowAddUsers){return true;}
	if($users->AllowManageOwnComputers){return true;}
	return false;
	}
	
	
	
	
	
	
?>