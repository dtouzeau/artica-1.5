<?php
	include_once('ressources/class.artica.graphs.inc');
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	
	
	$user=new usersMenus();
	if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_GET["status"])){status();exit;}
	if(isset($_GET["members"])){members();exit;}
	if(isset($_GET["members-add-popup"])){members_add_popup();exit;}
	if(isset($_POST["members-save"])){members_add_save();exit;}
	if(isset($_GET["members-list"])){members_list();exit;}
	if(isset($_GET["members-delete"])){members_delete();exit;}
	if(isset($_GET["members-enable"])){members_enable();exit;}
	
	
	if(isset($_GET["EnableVIPTrack"])){EnableVIPTrackSave();exit;}	
	if(isset($_GET["VIPTrackLastHour"])){ScheduleVIPTrackSave();exit;}
	
	if(isset($_GET["GenerateReportsNow"])){GenerateReportsNow();exit;}
	
	if(isset($_GET["db-status"])){dbstatus();exit;}
	
	
	
js();


function GenerateReportsNow(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?VIPTrackRun=yes");
	$tpl=new templates();
	echo $tpl->javascript_parse_text("{reports_generated_check_postmaster_mail}");
}

function ScheduleVIPTrackSave(){
	$sock=new sockets();
	$sock->SET_INFO("VIPTrackLastHour",$_GET["VIPTrackLastHour"]);
	$sock->SET_INFO("VIPTrackReportEach",$_GET["VIPTrackReportEach"]);
	$sock->SET_INFO("VIPTrackQueueTimeOut",$_GET["VIPTrackQueueTimeOut"]);
	$sock->SET_INFO("VIPTrackQueueMinTime",$_GET["VIPTrackQueueMinTime"]);
	
	
}


function js(){
	$tpl=new templates();
	$page=CurrentPageName();
	echo "YahooWin('750','$page?tabs=yes','VIPTrack')";
	}

function tabs(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$array["status"]='{status}';
	$array["members"]='{members}';

	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_postfix_viptrack style='width:100%;height:650px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_postfix_viptrack\").tabs();});
		</script>"
	;	
}


function dbstatus(){
	$tpl=new templates();
	$page=CurrentPageName();
	
	$q=new mysql();
	
	$connexions=$q->COUNT_ROWS("viptrack_connections","artica_events");
	$amavis=$q->COUNT_ROWS("viptrack_content","artica_events");
	$members=$q->COUNT_ROWS("postfix_viptrack","artica_backup");
	
	$report=Paragraphe("vip-report-64.png","{generate_reports}","{generate_reports_now_text}","javascript:VIPTrackGenerate()");
	
	
	$html="
	
	<strong style='font-size:16px'>{databases_status}</strong>
	<hr>
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{members}:</td>
		<td style='font-size:14px'><strong>$members</strong>&nbsp;{members}</td>
	</tr>	
	<tr>
		<td class=legend>{connections}:</td>
		<td style='font-size:14px'><strong>$connexions</strong>&nbsp;{emails}</td>
	</tr>
	<tr>
		<td class=legend>{content_filters}:</td>
		<td style='font-size:14px'><strong>$amavis</strong>&nbsp;{emails}</td>
	</tr>	
	</table>
	<p>&nbsp;</p>
	<center>$report</center>
	
	<script>
	var x_VIPTrackGenerate= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);return;};
		
	}	
	
	function VIPTrackGenerate(){
			var XHR = new XHRConnection();
			XHR.appendData('GenerateReportsNow','yes');
			XHR.sendAndLoad('$page', 'GET',x_VIPTrackGenerate);	
	}		
	</script>
	
	";
	echo $tpl->_ENGINE_parse_body($html);
}


function status(){
	$tpl=new templates();
	$page=CurrentPageName();
	
	$sock=new sockets();
	$EnableVIPTrack=$sock->GET_INFO("EnableVIPTrack");
	$VIPTrackLastHour=$sock->GET_INFO("VIPTrackLastHour");
	$VIPTrackReportEach=$sock->GET_INFO("VIPTrackReportEach");
	$VIPTrackQueueTimeOut=$sock->GET_INFO("VIPTrackQueueTimeOut");
	$VIPTrackQueueMinTime=$sock->GET_INFO("VIPTrackQueueMinTime");
	
	for($i=1;$i<24;$i++){$hours[$i]="$i {hours}";}
	$hours[24]="1 {day}";
	$hours[48]="2 {days}";
	$hours[72]="3 {days}";
	$hours[96]="4 {days}";
	
	$hoursEX[10]="10 {minutes}";
	$hoursEX[15]="15 {minutes}";
	$hoursEX[30]="30 {minutes}";
	$hoursEX[60]="1 {hour}";
	$hoursEX[120]="2 {hours}";
	$hoursEX[180]="3 {hours}";
	$hoursEX[420]="4 {hours}";
	$hoursEX[480]="8 {hours}";
	$hoursEX[1440]="1 {day}";
	$hoursEX[2880]="2 {days}";
	$hoursEX[4320]="3 {days}";
	$hoursEX[5760]="4 {days}";

	
	$VIPTrackQueueTimeOutR[2]="2 {minutes}";
	$VIPTrackQueueTimeOutR[5]="5 {minutes}";
	$VIPTrackQueueTimeOutR[10]="10 {minutes}";
	$VIPTrackQueueTimeOutR[15]="15 {minutes}";
	$VIPTrackQueueTimeOutR[30]="30 {minutes}";
	$VIPTrackQueueTimeOutR[60]="1 {hour}";
	$VIPTrackQueueTimeOutR[120]="2 {hours}";
	$VIPTrackQueueTimeOutR[180]="3 {hours}";
	$VIPTrackQueueTimeOutR[420]="4 {hours}";
	$VIPTrackQueueTimeOutR[480]="8 {hours}";
	$VIPTrackQueueTimeOutR[1440]="1 {day}";	
	
	if(!is_numeric($VIPTrackLastHour)){$VIPTrackLastHour="24";}
	if(!is_numeric($VIPTrackReportEach)){$VIPTrackReportEach=24*60;}
	if(!is_numeric($VIPTrackQueueTimeOut)){$VIPTrackQueueTimeOut=15;}
	if(!is_numeric($VIPTrackQueueMinTime)){$VIPTrackQueueMinTime=120;}
	if($VIPTrackQueueMinTime<$VIPTrackQueueTimeOut){$VIPTrackQueueMinTime=$VIPTrackQueueTimeOut;}
	
	
	$enable=Paragraphe_switch_img("{enable_viptrack}","{viptrack_explain}","EnableVIPTrack",$EnableVIPTrack,null,450);
	
	$VIPTrackLastHourField=Field_array_Hash($hours,"VIPTrackLastHour",$VIPTrackLastHour,"style:font-size:14px;padding:3px");
	$VIPTrackReportEachField=Field_array_Hash($hoursEX,"VIPTrackReportEach",$VIPTrackReportEach,"style:font-size:14px;padding:3px");
	$VIPTrackQueueTimeOutField=Field_array_Hash($VIPTrackQueueTimeOutR,"VIPTrackQueueTimeOut",$VIPTrackQueueTimeOut,"style:font-size:14px;padding:3px");
	$VIPTrackQueueMinTime_field=Field_array_Hash($hoursEX,"VIPTrackQueueMinTime",$VIPTrackQueueMinTime,"style:font-size:14px;padding:3px");
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top'>
			<div id='viptrack-db-status'></div>
		</td>
		<td valign='top'>
			<div id='VIPTrackForm'>
			$enable
			<hr>
			<div style='text-align:right'>". button("{apply}","EnableVIPTrackSave()")."</div>
			
			<p>&nbsp;</p>
			<strong style='font-size:16px'>{schedule}</strong>
			<table style='width:100%'>
			<tr>
			<td class=legend>{execute_reports_each}:</td>
			<td>$VIPTrackReportEachField</td>
			</tr>
			<td class=legend>{calculate_since}:</td>
			<td>$VIPTrackLastHourField</td>
			</tr>
			</tr>
			<td class=legend>{check_queue_each}:</td>
			<td>$VIPTrackQueueTimeOutField</td>
			</tr>			
			</tr>
			<td class=legend>{do_not_send_same_infos_before}:</td>
			<td>$VIPTrackQueueMinTime_field</td>
			</tr>				
			
			</table>
			<hr>
			<div style='text-align:right'>". button("{apply}","ScheduleVIPTrackSave()")."</div>
			
			</div>
		</td>
	</tr>
	</table>
	
	
	<script>
	var x_EnableVIPTrackSave= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);return;};
		RefreshTab('main_postfix_viptrack');
	}	
	
	function EnableVIPTrackSave(){
			var XHR = new XHRConnection();
			XHR.appendData('EnableVIPTrack',document.getElementById('EnableVIPTrack').value);
			document.getElementById('VIPTrackForm').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_EnableVIPTrackSave);	
	}
	
	function ScheduleVIPTrackSave(){
			var XHR = new XHRConnection();
			XHR.appendData('VIPTrackLastHour',document.getElementById('VIPTrackLastHour').value);
			XHR.appendData('VIPTrackReportEach',document.getElementById('VIPTrackReportEach').value);
			XHR.appendData('VIPTrackQueueTimeOut',document.getElementById('VIPTrackQueueTimeOut').value);
			XHR.appendData('VIPTrackQueueMinTime',document.getElementById('VIPTrackQueueMinTime').value);
			
			
			document.getElementById('VIPTrackForm').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_EnableVIPTrackSave);	
	}	
	
	
	
	function DbstatusVIPTrack(){
		LoadAjax('viptrack-db-status','$page?db-status=yes');
	}
	
	
	DbstatusVIPTrack();
	</script>		
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}

function EnableVIPTrackSave(){
	$sock=new sockets();
	$sock->SET_INFO("EnableVIPTrack",$_GET["EnableVIPTrack"]);
	
}

function members(){
	$page=CurrentPageName();
	$tpl=new templates();
	
	$add=Paragraphe32("add_members","add_vip_members_text","YahooWin2('550','$page?members-add-popup=yes','{add_members}')","vipadd-32.png");
	
	$html="
	$add
	<div id='vip-list' style='width:100%;height:450px;overflow:auto'></div>
	
	<script>
		function VipTrackMembersRefresh(){
			LoadAjax('vip-list','$page?members-list=yes');
		}
		
		VipTrackMembersRefresh();
	</script>
	
	";
	echo $tpl->_ENGINE_parse_body($html);
}

function members_add_save(){
	
	$tbl=explode("\n",$_POST["members-save"]);
	while (list ($num, $ligne) = each ($tbl) ){
		$ligne=strtolower(trim($ligne));
		if(trim($ligne)==null){continue;}
		if(!preg_match("#.+?@.+#",$ligne)){
			$error[]=$ligne;
			continue;}
		$sq[]="('$ligne')";
		
	}
	if(count($sq)==0){return;}
	$sql="INSERT INTO postfix_viptrack (email) VALUES ".@implode(",",$sq);
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	if(count($error)>0){echo @implode("\n",$error)."\nFailed...";}
	}
function members_delete(){
	$sql="DELETE FROM postfix_viptrack WHERE `email`='{$_GET["members-delete"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}	
}
function members_enable(){
	$sql="UPDATE postfix_viptrack SET `enabled`='{$_GET["value"]}' WHERE `email`='{$_GET["members-enable"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}	
}

function members_list(){
	
	$tpl=new templates();
	$page=CurrentPageName();
	$sql="SELECT * FROM postfix_viptrack ORDER BY email";
	$q=new mysql();	

	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."<br><code>$sql</code>";return;}

	$html="
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
	<thead class='thead'>
		<tr>
			<th>&nbsp;</th>
			<th>{members}</th>
			<th>{enabled}</th>
			<th>&nbsp;</th>
		</tr>
	</thead>
	<tbody class='tbody'>";

	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$delete=imgtootltip("delete-24.png","{delete}","DeleteVipTrack('{$ligne["email"]}')");
		$html=$html."
		<tr class=$classtr>	
			<td width=1% nowrap style='font-size:14px'><img src='img/vip-24.png'></td>
			<td width=99% nowrap style='font-size:14px'>{$ligne["email"]}</td>
			<td width=1% nowrap style='font-size:14px'>". Field_checkbox("{$ligne["email"]}_enabled",1,$ligne["enabled"],"VipTrackEnable('{$ligne["email"]}')")."</td>
			<td width=1% nowrap style='font-size:14px'>$delete</td>
		</tr>
		";
	}
	
	$delete=$tpl->javascript_parse_text("{delete}");
	
	$html=$html."</table>
	<script>
	var x_DeleteVipTrack= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);return;};
		VipTrackMembersRefresh();
	}
	
	var x_VipTrackEnable= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);return;};
		
	}	
	
	function DeleteVipTrack(email){
		if(confirm('$delete: '+email+' ?')){
			var XHR = new XHRConnection();
			XHR.appendData('members-delete',email);
			document.getElementById('vip-list').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_DeleteVipTrack);	
		}
	}
	
	function VipTrackEnable(email){
		var XHR = new XHRConnection();
		if(document.getElementById(email+'_enabled').checked){XHR.appendData('value','1');}else{XHR.appendData('value','0');}
		XHR.appendData('members-enable',email);
		XHR.sendAndLoad('$page', 'GET',x_VipTrackEnable);	
		}	
	</script>
	
	";
	echo $tpl->_ENGINE_parse_body($html);			
		
	}


function members_add_popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$html="
	<div id='viptrackadd-smtp-div'>
	<div class=explain>{add_multiple_members_explain}</div>

	<textarea id='members-servers-container' style='width:100%;height:450px;overflow:auto;font-size:14px'></textarea>
	<div style='text-align:right'>". button("{add}","MembersVIPTrackSave()")."</div>
	</div>
	<script>
	
	var x_MembersVIPTrackSave= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue)};
		YahooWin2Hide();
		RefreshTab('main_postfix_viptrack');
	}			
		
	function MembersVIPTrackSave(){
		var XHR = new XHRConnection();
		XHR.appendData('members-save',document.getElementById('members-servers-container').value);
		document.getElementById('viptrackadd-smtp-div').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'POST',x_MembersVIPTrackSave);		
		}
	
	</script>";
	echo $tpl->_ENGINE_parse_body($html);
}

