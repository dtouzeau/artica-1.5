<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.main_cf.inc');
	include_once('ressources/class.tcpip.inc');
	include_once('ressources/class.system.network.inc');	
	include_once('ressources/class.postfix-multi.inc');
	
$users=new usersMenus();
$tpl=new templates();
if(!$users->AsOrgPostfixAdministrator){
		echo $tpl->javascript_parse_text("alert('{ERROR_NO_PRIVS}');");
		die();
	}

if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["settings-perfs"])){settings_perfs();exit;}
if(isset($_GET["settings-perfs-save"])){settings_perfs_save();exit;}
js();



function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{title_postfix_tuning}");
	$html="
		function ORG_PERF_POSTFIX_START(){
			YahooWin2('650','$page?popup=yes&ou={$_GET["ou"]}&hostname={$_GET["hostname"]}','$title');
		}
	
	ORG_PERF_POSTFIX_START()";
	
	echo $html;
	
	
	
}

function popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$html="
	<div id='postfix-performances-multi'></div>
	
	<script>
		LoadAjax('postfix-performances-multi','$page?settings-perfs=yes&ou={$_GET["ou"]}&hostname={$_GET["hostname"]}');
	</script>
	";
	
	echo $html;
	
	
}

function settings_perfs(){
	$page=CurrentPageName();
	$tpl=new templates();
	$master=new maincf_multi($_GET["hostname"],$_GET["ou"]);
	$in_flow_delay=$master->GET("in_flow_delay");
	$minimal_backoff_time=$master->GET("minimal_backoff_time");
	$maximal_backoff_time=$master->GET("maximal_backoff_time");
	$bounce_queue_lifetime=$master->GET("bounce_queue_lifetime");
	$default_process_limit=$master->GET("default_process_limit");
	$maximal_queue_lifetime=$master->GET("maximal_queue_lifetime");
	
	$queue_run_delay=$master->GET("queue_run_delay");
	
	if($in_flow_delay==null){$in_flow_delay="1s";}
	if($minimal_backoff_time==null){$minimal_backoff_time="300s";}
	if($maximal_backoff_time==null){$maximal_backoff_time="4000s";}
	if($default_process_limit==null){$default_process_limit="100";}
	if($bounce_queue_lifetime==null){$bounce_queue_lifetime="5d";}
	if($maximal_queue_lifetime==null){$maximal_queue_lifetime="5d";}
	if($queue_run_delay==null){$queue_run_delay="5m";}
	
	$qmgr_message_active_limit=$master->GET("qmgr_message_active_limit");
	$qmgr_message_recipient_limit=$master->GET("qmgr_message_recipient_limit");
	$qmgr_message_recipient_minimum=$master->GET("qmgr_message_recipient_minimum");
	
	
	if(!is_numeric($qmgr_message_active_limit)){$qmgr_message_active_limit=20000;}
	if(!is_numeric($qmgr_message_recipient_limit)){$qmgr_message_recipient_limit=20000;}
	if(!is_numeric($qmgr_message_recipient_minimum)){$qmgr_message_recipient_minimum=10;}
	
	
	
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top'><img src='img/global-settings-128.png' id='global-settings-128'></td>
		<td valign='top'>
			<table style='width:100%'>
			<tr>
				<td class=legend>{in_flow_delay}:</td>
				<td>". Field_text("in_flow_delay",$in_flow_delay,"width:90px;padding:3px;font-size:13px")."</td>
				<td width=1%>". help_icon("{in_flow_delay_text}")."</td>
			</tr>
			<tr>
				<td class=legend>{queue_run_delay}:</td>
				<td>". Field_text("queue_run_delay",$queue_run_delay,"width:90px;padding:3px;font-size:13px")."</td>
				<td width=1%>". help_icon("{queue_run_delay_text}")."</td>
			</tr>			
			
			
			<tr>
				<td class=legend>{minimal_backoff_time}:</td>
				<td>". Field_text("minimal_backoff_time",$minimal_backoff_time,"width:90px;padding:3px;font-size:13px")."</td>
				<td width=1%>". help_icon("{minimal_backoff_time_text}")."</td>
			</tr>			
			<tr>
				<td class=legend>{maximal_backoff_time}:</td>
				<td>". Field_text("maximal_backoff_time",$maximal_backoff_time,"width:90px;padding:3px;font-size:13px")."</td>
				<td width=1%>". help_icon("{maximal_backoff_time_text}")."</td>
			</tr>

		
			<tr>
				<td class=legend>{default_process_limit}:</td>
				<td>". Field_text("default_process_limit",$default_process_limit,"width:90px;padding:3px;font-size:13px")."</td>
				<td width=1%>". help_icon("{default_process_limit_text}")."</td>
			</tr>	
			<tr>
				<td colspan=3 style='font-size:16px;font-weight:bold'>{parameters}:&nbsp;&laquo;&nbsp;{queue}&nbsp;&raquo;<hr></td>
			</tr>
			<tr>
				<td class=legend>{bounce_queue_lifetime}:</td>
				<td>". Field_text("bounce_queue_lifetime",$bounce_queue_lifetime,"width:90px;padding:3px;font-size:13px")."</td>
				<td width=1%>". help_icon("{bounce_queue_lifetime_text}")."</td>
			</tr>			
			<tr>
				<td class=legend valign='top' nowrap>{maximal_queue_lifetime}&nbsp;:</strong></td>
				<td>". Field_text("maximal_queue_lifetime",$maximal_queue_lifetime,"width:90px;padding:3px;font-size:13px")."</td>
				<td width=1%>". help_icon("{maximal_queue_lifetime_text}")."</td>
			</tr>
			<tr>
				<td class=legend valign='top' nowrap>{qmgr_message_active_limit}&nbsp;:</strong></td>
				<td>". Field_text("qmgr_message_active_limit",$qmgr_message_active_limit,"width:90px;padding:3px;font-size:13px")."</td>
				<td width=1%></td>
			</tr>
			</tr>								
				<td class=legend valign='top' nowrap>{qmgr_message_recipient_limit}&nbsp;:</strong></td>
				<td>". Field_text("qmgr_message_recipient_limit",$qmgr_message_recipient_limit,"width:90px;padding:3px;font-size:13px")."</td>
				<td width=1%>". help_icon("{qmgr_message_recipient_limit_text}")."</td>
			</tr>			
			<tr>								
				<td class=legend valign='top' nowrap>{qmgr_message_recipient_minimum}&nbsp;:</strong></td>
				<td>". Field_text("qmgr_message_recipient_minimum",$qmgr_message_recipient_minimum,"width:90px;padding:3px;font-size:13px")."</td>
				<td width=1%>". help_icon("{qmgr_message_recipient_minimum_text}")."</td>
			</tr>	
			
			
			
			<tr>
				<td colspan=3 align='right'>
				<hr>
				". button("{apply}","SaveHostTunePostfix()")."</td>
			</tr>
			</table>
		</td>
		</tr>
		</table>
		
	<script>
	var x_SaveHostTunePostfix= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		document.getElementById('global-settings-128').src='img/global-settings-128.png';
	}	
	
	
		function SaveHostTunePostfix(){	
			var XHR = new XHRConnection();
			XHR.appendData('settings-perfs-save','yes');
			XHR.appendData('hostname','{$_GET["hostname"]}');
			XHR.appendData('ou','{$_GET["ou"]}');
			XHR.appendData('in_flow_delay',document.getElementById('in_flow_delay').value);
			XHR.appendData('minimal_backoff_time',document.getElementById('minimal_backoff_time').value);
			XHR.appendData('maximal_backoff_time',document.getElementById('maximal_backoff_time').value);
			XHR.appendData('bounce_queue_lifetime',document.getElementById('bounce_queue_lifetime').value);
			XHR.appendData('default_process_limit',document.getElementById('default_process_limit').value);
			XHR.appendData('maximal_queue_lifetime',document.getElementById('maximal_queue_lifetime').value);
			
			
			XHR.appendData('queue_run_delay',document.getElementById('queue_run_delay').value);
			XHR.appendData('qmgr_message_active_limit',document.getElementById('qmgr_message_active_limit').value);
			XHR.appendData('qmgr_message_recipient_limit',document.getElementById('qmgr_message_recipient_limit').value);
			XHR.appendData('qmgr_message_recipient_minimum',document.getElementById('qmgr_message_recipient_minimum').value);
			
			document.getElementById('global-settings-128').src='img/wait_verybig.gif';
			XHR.sendAndLoad('$page', 'GET',x_SaveHostTunePostfix);	
			}	
	
	
	</script>
		
	";
echo $tpl->_ENGINE_parse_body($html);

}

function settings_perfs_save(){
	$master=new maincf_multi($_GET["hostname"],$_GET["ou"]);
	$master->SET_VALUE("in_flow_delay",$_GET["in_flow_delay"]);
	$master->SET_VALUE("minimal_backoff_time",$_GET["minimal_backoff_time"]);
	$master->SET_VALUE("maximal_backoff_time",$_GET["maximal_backoff_time"]);
	$master->SET_VALUE("bounce_queue_lifetime",$_GET["bounce_queue_lifetime"]);
	$master->SET_VALUE("default_process_limit",$_GET["default_process_limit"]);
	$master->SET_VALUE("maximal_queue_lifetime",$_GET["maximal_queue_lifetime"]);
	$master->SET_VALUE("queue_run_delay",$_GET["queue_run_delay"]);
	
	$master->SET_VALUE("qmgr_message_active_limit",$_GET["qmgr_message_active_limit"]);
	$master->SET_VALUE("qmgr_message_recipient_limit",$_GET["qmgr_message_recipient_limit"]);
	$master->SET_VALUE("qmgr_message_recipient_minimum",$_GET["qmgr_message_recipient_minimum"]);
	
	
	
	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-multi-settings={$_GET["hostname"]}");
}

?>