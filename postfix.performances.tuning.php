<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
session_start();
include_once("ressources/class.templates.inc");
include_once("ressources/class.ldap.inc");
include_once("ressources/class.main_cf.inc");


if(isset($_GET["with-tabs"])){echo withtabs();exit;}
if(isset($_POST["smtpd_error_sleep_time"])){SaveForm();exit;}
if(isset($_GET["popup-index"])){popup_index();exit;}
if(isset($_GET["popup-form"])){popup_form();exit;}
js();

function withtabs(){
if($_GET["hostname"]==null){$_GET["hostname"]="master";}	
$id=time();
$page=CurrentPageName();
$html="<div id='$id'></div>
<script>
	LoadAjax('$id','$page?popup-index=yes&hostname={$_GET["hostname"]}');
</script>
";
echo $html;
}

function js(){
$prefix=str_replace(".","_",CurrentPageName());
$page=CurrentPageName();
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{title_postfix_tuning}');
	
	$users=new usersMenus();
	if(!$users->AsPostfixAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}
if(!isset($_GET["hostname"])){$_GET["hostname"]="master";}
	
$html="function {$prefix}Loadpage(){
	YahooWin2('650','$page?popup-index=yes&hostname={$_GET["hostname"]}','{$_GET["hostname"]}::$title');
	}

	
 {$prefix}Loadpage();
";	
 
 echo $html;
	
}

function popup_index(){
	$page=CurrentPageName();
	$html="<div id='postfix-tuning-form' style='width:100%'></div>
	<script>
		function LoadPostfixTuningForm(){
			LoadAjax('postfix-tuning-form','$page?popup-form=yes&hostname={$_GET["hostname"]}');
		}
		LoadPostfixTuningForm();
	</script>
	
	";
	echo $html;
	
}

function popup_form(){

$tpl=new templates();
$main=new maincf_multi($_GET["hostname"]);


$smtpd_error_sleep_time=$main->GET("smtpd_error_sleep_time");
$smtpd_soft_error_limit=$main->GET("smtpd_soft_error_limit");
$smtpd_hard_error_limit=$main->GET("smtpd_hard_error_limit");
$smtpd_client_connection_count_limit=$main->GET("smtpd_client_connection_count_limit");
$smtpd_client_connection_rate_limit=$main->GET("smtpd_client_connection_rate_limit");
$smtpd_client_message_rate_limit=$main->GET("smtpd_client_message_rate_limit");
$smtpd_client_recipient_rate_limit=$main->GET("smtpd_client_recipient_rate_limit");
$smtpd_client_new_tls_session_rate_limit=$main->GET("smtpd_client_new_tls_session_rate_limit");
$smtpd_client_event_limit_exceptions=$main->GET("smtpd_client_event_limit_exceptions");
$in_flow_delay=$main->GET("in_flow_delay");
$smtp_connect_timeout=$main->GET("smtp_connect_timeout");
$smtp_helo_timeout=$main->GET("smtp_helo_timeout");
$initial_destination_concurrency=$main->GET("initial_destination_concurrency");
$default_destination_concurrency_limit=$main->GET("default_destination_concurrency_limit");
$local_destination_concurrency_limit=$main->GET("local_destination_concurrency_limit");
$smtp_destination_concurrency_limit=$main->GET("smtp_destination_concurrency_limit");
$default_destination_recipient_limit=$main->GET("default_destination_recipient_limit");
$smtpd_recipient_limit=$main->GET("smtpd_recipient_limit");
$queue_run_delay=$main->GET("queue_run_delay");  
$minimal_backoff_time =$main->GET("minimal_backoff_time");
$maximal_backoff_time =$main->GET("maximal_backoff_time");
$maximal_queue_lifetime=$main->GET("maximal_queue_lifetime"); 
$bounce_queue_lifetime =$main->GET("bounce_queue_lifetime");
$qmgr_message_recipient_limit =$main->GET("qmgr_message_recipient_limit");
$default_process_limit=$main->GET("default_process_limit");


if($smtpd_error_sleep_time==null){$smtpd_error_sleep_time="1s";}
if(!is_numeric($smtpd_soft_error_limit)){$smtpd_soft_error_limit=10;}
if(!is_numeric($smtpd_hard_error_limit)){$smtpd_hard_error_limit=20;}
if(!is_numeric($smtpd_client_connection_count_limit)){$smtpd_client_connection_count_limit=50;}
if(!is_numeric($smtpd_client_connection_rate_limit)){$smtpd_client_connection_rate_limit=0;}
if(!is_numeric($smtpd_client_message_rate_limit)){$smtpd_client_message_rate_limit=0;}
if(!is_numeric($smtpd_client_recipient_rate_limit)){$smtpd_client_recipient_rate_limit=0;}
if(!is_numeric($smtpd_client_new_tls_session_rate_limit)){$smtpd_client_new_tls_session_rate_limit=0;}
if(!is_numeric($initial_destination_concurrency)){$initial_destination_concurrency=5;}
if(!is_numeric($default_destination_concurrency_limit)){$default_destination_concurrency_limit=20;}
if(!is_numeric($smtp_destination_concurrency_limit)){$smtp_destination_concurrency_limit=20;}
if(!is_numeric($local_destination_concurrency_limit)){$local_destination_concurrency_limit=2;}
if(!is_numeric($default_destination_recipient_limit)){$default_destination_recipient_limit=50;}
if(!is_numeric($smtpd_recipient_limit)){$smtpd_recipient_limit=1000;}
if(!is_numeric($default_process_limit)){$default_process_limit=100;}
if(!is_numeric($qmgr_message_recipient_limit)){$qmgr_message_recipient_limit=20000;}
if($smtpd_client_event_limit_exceptions==null){$smtpd_client_event_limit_exceptions="\$mynetworks";}
if($in_flow_delay==null){$in_flow_delay="1s";}
if($smtp_connect_timeout==null){$smtp_connect_timeout="30s";}
if($smtp_helo_timeout==null){$smtp_helo_timeout="300s";}
if($bounce_queue_lifetime==null){$bounce_queue_lifetime="5d";}
if($maximal_queue_lifetime==null){$maximal_queue_lifetime="5d";}
if($maximal_backoff_time==null){$maximal_backoff_time="4000s";}
if($minimal_backoff_time==null){$minimal_backoff_time="300s";}
if($queue_run_delay==null){$queue_run_delay="300s";}



$page=CurrentPageName();
$html="<table style='width:100%' class=form>
<tr>
	<td colspan=2><div style='font-size:16px'>{title_postfix_tuning_1}</div></td>
	<td width=1%>". help_icon('{title_postfix_tuning_1_text}')."</td>
</tr>
</tr>
	<tr>
		<td class=legend valign='top' nowrap>{smtpd_error_sleep_time}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('smtpd_error_sleep_time',$smtpd_error_sleep_time,'width:60px;font-size:14px',null,null,'') ."</td>
		<td width=1%>". help_icon('{smtpd_error_sleep_time_text}')."</td>
	</tr>	
	<tr><td align='right' colspan='3' ><hr>". button("{apply}","SavePerfTuning()")."</td></tr>
</tbody></table>

<br>


<table style='width:100%' class=form>
<tr>
	<td colspan=2 ><div style='font-size:16px'>{title_postfix_tuning_2}</div></td>
	<td width=1%>". help_icon('{title_postfix_tuning_2_text}')."</td>
</tr>
	<tr>
		<td class=legend valign='top' nowrap>{smtpd_soft_error_limit}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('smtpd_soft_error_limit',$smtpd_soft_error_limit,'width:60px;font-size:14px',null,null,'') ."</td>
		<td width=1%>". help_icon('{smtpd_soft_error_limit_text}')."</td>
	</tr>
	<tr>
		<td class=legend valign='top' nowrap>{smtpd_hard_error_limit}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('smtpd_hard_error_limit',$smtpd_hard_error_limit,'width:60px;font-size:14px',null,null,'') ."</td>
		<td width=1%>". help_icon('{smtpd_soft_error_limit_text}')."</td>
	</tr>	
<tr><td align='right' colspan='3' ><hr>". button("{apply}","SavePerfTuning()")."</td></tr>
</tbody></table><br>


<table style='width:100%' class=form>
<tr>
	<td colspan=2><div style='font-size:16px'>{title_postfix_tuning_3}</div></td>
	<td width=1%>". help_icon('{title_postfix_tuning_3_text}')."</td>
</tr>
	<tr>
		<td class=legend valign='top' nowrap>{smtpd_client_connection_count_limit}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('smtpd_client_connection_count_limit',$smtpd_client_connection_count_limit,'width:60px;font-size:14px',null,null,'') ."</td>
		<td width=1%>". help_icon('{smtpd_client_connection_count_limit_text}')."</td>
	</tr>	
	<tr>
		<td class=legend valign='top' nowrap>{smtpd_client_connection_rate_limit}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('smtpd_client_connection_rate_limit',$smtpd_client_connection_rate_limit,'width:60px;font-size:14px',null,null,'') ."</td>
		<td width=1%>". help_icon('{smtpd_client_connection_rate_limit_text}')."</td>
	</tr>	
	<tr>
		<td class=legend valign='top' nowrap>{smtpd_client_message_rate_limit}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('smtpd_client_message_rate_limit',$smtpd_client_message_rate_limit,'width:60px;font-size:14px',null,null,'') ."</td>
		<td width=1%>". help_icon('{smtpd_client_message_rate_limit_text}')."</td>
	</tr>
	<tr>
		<td class=legend valign='top' nowrap>{smtpd_client_recipient_rate_limit}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('smtpd_client_recipient_rate_limit',$smtpd_client_recipient_rate_limit,'width:60px;font-size:14px',null,null,'') ."</td>
		<td width=1%>". help_icon('{smtpd_client_recipient_rate_limit_text}')."</td>
	</tr>					
	<tr>
		<td class=legend valign='top' nowrap>{smtpd_client_new_tls_session_rate_limit}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('smtpd_client_new_tls_session_rate_limit',$smtpd_client_new_tls_session_rate_limit,'width:60px;font-size:14px',null,null,'') ."</td>
		<td width=1%>". help_icon('{smtpd_client_new_tls_session_rate_limit_text}')."</td>
	</tr>
	<tr>
		<td class=legend valign='top' nowrap>{smtpd_client_event_limit_exceptions}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('smtpd_client_event_limit_exceptions',$smtpd_client_event_limit_exceptions,'width:70%;font-size:14px',null,null,'') ."</td>
		<td width=1%>". help_icon('{smtpd_client_event_limit_exceptions_text}')."</td>
	</tr>
<tr><td align='right' colspan='3' ><hr>". button("{apply}","SavePerfTuning()")."</td></tr>
</tbody></table><br>


<table style='width:100%' class=form>			
<tr>
	<td colspan=2><div style='font-size:16px'>{title_postfix_tuning_4}</div></td>
	<td width=1%>". help_icon('{title_postfix_tuning_4_text}')."</td>
</tr>	
	<tr>
		<td class=legend valign='top' nowrap>{in_flow_delay}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('in_flow_delay',$in_flow_delay,'width:60px;font-size:14px',null,null,'') ."</td>
		<td width=1%>". help_icon('{in_flow_delay_text}')."</td>
	</tr>	
	<tr>
		<td class=legend valign='top' nowrap>{smtp_connect_timeout}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('smtp_connect_timeout',$smtp_connect_timeout,'width:60px;font-size:14px',null,null,'') ."</td>
		<td width=1%>". help_icon('{smtp_connect_timeout_text}')."</td>
	</tr>
	<tr>
		<td class=legend valign='top' nowrap>{smtp_helo_timeout}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('smtp_helo_timeout',$smtp_helo_timeout,'width:60px;font-size:14px',null,null,'') ."</td>
		<td width=1%>". help_icon('{smtp_helo_timeout_text}')."</td>
	</tr>	
<tr><td align='right' colspan='3' ><hr>". button("{apply}","SavePerfTuning()")."</td></tr>					

</tbody></table><br>
<table style='width:100%' class=form>
<tr>
	<td colspan=2><div style='font-size:16px'>{title_postfix_tuning_8}</div></td>
	<td width=1%>". help_icon('{title_postfix_tuning_8_text}')."</td>
</tr>	
	<tr>
		<td class=legend valign='top' nowrap>{initial_destination_concurrency}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('initial_destination_concurrency',$initial_destination_concurrency,'width:60px;font-size:14px',null,null,'') ."</td>
		<td width=1%>". help_icon('{initial_destination_concurrency_text}')."</td>
	</tr>
	<tr>
		<td class=legend valign='top' nowrap>{default_destination_concurrency_limit}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('default_destination_concurrency_limit',$default_destination_concurrency_limit,'width:60px;font-size:14px',null,null,'') ."</td>
		<td width=1%>". help_icon('{default_destination_concurrency_limit_text}')."</td>
	</tr>
	<tr>
		<td class=legend valign='top' nowrap>{local_destination_concurrency_limit}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('local_destination_concurrency_limit',$local_destination_concurrency_limit,'width:60px;font-size:14px',null,null,'') ."</td>
		<td width=1%>". help_icon('{local_destination_concurrency_limit_text}')."</td>
	</tr>
	<tr>
		<td class=legend valign='top' nowrap>{smtp_destination_concurrency_limit}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('smtp_destination_concurrency_limit',$smtp_destination_concurrency_limit,'width:60px;font-size:14px',null,null,'') ."</td>
		<td width=1%>". help_icon('{smtp_destination_concurrency_limit_text}')."</td>
	</tr>			
<tr><td align='right' colspan='3' ><hr>". button("{apply}","SavePerfTuning()")."</td></tr>					
</tbody></table>


<table style='width:100%' class=form>
<tr>
	<td colspan=2><div style='font-size:16px'>{title_postfix_tuning_5}</div></td>
	<td width=1%>". help_icon('{title_postfix_tuning_5_text}')."</td>
</tr>
	
	<tr>
		<td class=legend valign='top' nowrap>{default_destination_recipient_limit}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('default_destination_recipient_limit',$default_destination_recipient_limit,'width:60px;font-size:14px',null,null,'') ."</td>
		<td width=1%>". help_icon('{default_destination_recipient_limit_text}')."</td>
	</tr>	
	<tr>
		<td class=legend valign='top' nowrap>{smtpd_recipient_limit}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('smtpd_recipient_limit',$smtpd_recipient_limit,'width:60px;font-size:14px',null,null,'') ."</td>
		<td width=1%>". help_icon('{smtpd_recipient_limit_text}')."</td>
	</tr>		
<tr><td align='right' colspan='3' ><hr>". button("{apply}","SavePerfTuning()")."</td></tr>				
</tbody></table><br>

<table style='width:100%' class=form>
<tr>
	<td colspan=2><div style='font-size:16px'>{title_postfix_tuning_6}</div></td>
	<td width=1%>". help_icon('{title_postfix_tuning_6_text}')."</td>
</tr>		
	<tr>
		<td class=legend valign='top' nowrap>{queue_run_delay}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('queue_run_delay',$queue_run_delay,'width:60px;font-size:14px',null,null,'') ."</td>
		<td width=1%>". help_icon('{queue_run_delay_text}')."</td>
	</tr>	
	<tr>
		<td class=legend valign='top' nowrap>{minimal_backoff_time}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('minimal_backoff_time',$minimal_backoff_time,'width:60px;font-size:14px',null,null,'') ."</td>
		<td width=1%>". help_icon('{minimal_backoff_time_text}')."</td>
	</tr>	
	<tr>
		<td class=legend valign='top' nowrap>{queue_run_delay}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('maximal_backoff_time',$maximal_backoff_time,'width:60px;font-size:14px',null,null,'') ."</td>
		<td width=1%>". help_icon('{maximal_backoff_time_text}')."</td>
	</tr>	
	<tr>
		<td class=legend valign='top' nowrap>{maximal_queue_lifetime}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('maximal_queue_lifetime',$maximal_queue_lifetime,'width:60px;font-size:14px',null,null,'') ."</td>
		<td width=1%>". help_icon('{maximal_queue_lifetime_text}')."</td>
		
	</tr>
	<tr>
		<td class=legend valign='top' nowrap>{bounce_queue_lifetime}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('bounce_queue_lifetime',$bounce_queue_lifetime,'width:60px;font-size:14px',null,null,'') ."</td>
		<td width=1%>". help_icon('{bounce_queue_lifetime_text}')."</td>
	</tr>					
	<tr>
		<td class=legend valign='top' nowrap>{qmgr_message_recipient_limit}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('qmgr_message_recipient_limit',$qmgr_message_recipient_limit,'width:60px;font-size:14px',null,null,'') ."</td>
		<td width=1%>". help_icon('{qmgr_message_recipient_limit_text}')."</td>
	</tr>
	<tr>
		<td class=legend valign='top' nowrap>{qmgr_message_recipient_minimum}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('qmgr_message_recipient_minimum',$qmgr_message_recipient_minimum,'width:60px;font-size:14px',null,null,'') ."</td>
		<td width=1%>". help_icon('{qmgr_message_recipient_minimum_text}')."</td>
	</tr>		
<tr><td align='right' colspan='3' ><hr>". button("{apply}","SavePerfTuning()")."</td></tr>	
</tbody></table><br>

<table style='width:100%' class=form>
<tr>
<tr>
	<td colspan=2><div style='font-size:16px'>{title_postfix_tuning_7}</div></td>
	<td width=1%>". help_icon('{title_postfix_tuning_7_text}')."</td>
</tr>	
	<tr>
		<td class=legend valign='top' nowrap>{default_process_limit}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('default_process_limit',$default_process_limit,'width:60px;font-size:14px',null,null,'') ."</td>
		<td width=1%>". help_icon('{default_process_limit_text}')."</td>
	</tr>		
<tr><td align='right' colspan='3' ><hr>". button("{apply}","SavePerfTuning()")."</td></tr>				

</tbody></table>	

<script>
	var x_SavePerfTuning=function(obj){
    	var tempvalue=trim(obj.responseText);
	  	if(tempvalue.length>3){alert(tempvalue);}
	  	LoadPostfixTuningForm();
	}	
		
		
		function SavePerfTuning(){
			var XHR = new XHRConnection();	
			XHR.appendData('smtpd_error_sleep_time',document.getElementById('smtpd_error_sleep_time').value);
			XHR.appendData('smtpd_soft_error_limit',document.getElementById('smtpd_soft_error_limit').value);
			XHR.appendData('smtpd_hard_error_limit',document.getElementById('smtpd_hard_error_limit').value);
			XHR.appendData('smtpd_client_connection_count_limit',document.getElementById('smtpd_client_connection_count_limit').value);
			XHR.appendData('smtpd_client_connection_rate_limit',document.getElementById('smtpd_client_connection_rate_limit').value);
			XHR.appendData('smtpd_client_message_rate_limit',document.getElementById('smtpd_client_message_rate_limit').value);
			XHR.appendData('smtpd_client_recipient_rate_limit',document.getElementById('smtpd_client_recipient_rate_limit').value);
			XHR.appendData('smtpd_client_new_tls_session_rate_limit',document.getElementById('smtpd_client_new_tls_session_rate_limit').value);
			XHR.appendData('initial_destination_concurrency',document.getElementById('initial_destination_concurrency').value);
			XHR.appendData('default_destination_concurrency_limit',document.getElementById('default_destination_concurrency_limit').value);
			XHR.appendData('smtp_destination_concurrency_limit',document.getElementById('smtp_destination_concurrency_limit').value);
			XHR.appendData('local_destination_concurrency_limit',document.getElementById('local_destination_concurrency_limit').value);
			XHR.appendData('default_destination_recipient_limit',document.getElementById('default_destination_recipient_limit').value);
			XHR.appendData('smtpd_recipient_limit',document.getElementById('smtpd_recipient_limit').value);
			XHR.appendData('default_process_limit',document.getElementById('default_process_limit').value);
			XHR.appendData('qmgr_message_recipient_limit',document.getElementById('qmgr_message_recipient_limit').value);
			XHR.appendData('smtpd_client_event_limit_exceptions',document.getElementById('smtpd_client_event_limit_exceptions').value);
			XHR.appendData('in_flow_delay',document.getElementById('in_flow_delay').value);
			XHR.appendData('smtp_connect_timeout',document.getElementById('smtp_connect_timeout').value);
			XHR.appendData('smtp_helo_timeout',document.getElementById('smtp_helo_timeout').value);
			XHR.appendData('bounce_queue_lifetime',document.getElementById('bounce_queue_lifetime').value);
			XHR.appendData('maximal_queue_lifetime',document.getElementById('maximal_queue_lifetime').value);
			XHR.appendData('maximal_backoff_time',document.getElementById('maximal_backoff_time').value);
			XHR.appendData('minimal_backoff_time',document.getElementById('minimal_backoff_time').value);
			XHR.appendData('queue_run_delay',document.getElementById('queue_run_delay').value);
			XHR.appendData('hostname','{$_GET["hostname"]}');
			AnimateDiv('postfix-tuning-form');
			XHR.sendAndLoad('$page', 'POST',x_SavePerfTuning);			
		}

</script>
";	


$tpl=new Templates();
echo $tpl->_ENGINE_parse_body($html);
}

function SaveForm(){
	$hostname=$_POST["hostname"];
	unset($_POST["hostname"]);
	writelogs("Save settings for $hostname",__FUNCTION__,__FILE__,__LINE__);
	$main=new maincf_multi($hostname);
	while (list ($num, $val) = each ($_POST) ){
		writelogs("$num=\"$val\"",__FUNCTION__,__FILE__);
		$main->SET_VALUE($num,$val);
	}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-others-values=yes&hostname=$hostname");		
	
}




?>