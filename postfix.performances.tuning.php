<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
session_start();
include_once("ressources/class.templates.inc");
include_once("ressources/class.ldap.inc");
include_once("ressources/class.main_cf.inc");

if(isset($_GET["smtpd_error_sleep_time"])){SaveForm();exit;}
if(isset($_GET["popup-index"])){popup_index();exit;}
js();

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

	
$html="function {$prefix}Loadpage(){
	YahooWin2('650','$page?popup-index=yes','$title');
	}

	
 {$prefix}Loadpage();
";	
 
 echo $html;
	
}

function popup_index(){
$main=new main_cf();
$page=CurrentPageName();
$html="<table style='width:600px' align=center>
<tr>
<td valign='top'><div style='width:100%;height:400px;overflow:auto'>
<form name='FFMAP'>
" . RoundedLightWhite("<table style='width:500px'>
<tr><td colspan=2><H5 style='margin:0px;font-size:13px'>{title_postfix_tuning_1}</H5></td></tr>
<tr><td colspan=2 >". helpCoolapse('{title_postfix_tuning_1_text}')."</td></tr>
	<tr>
		<td class=legend valign='top' nowrap>{smtpd_error_sleep_time}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('smtpd_error_sleep_time',$main->main_array["smtpd_error_sleep_time"],'width:20%',null,null,'{smtpd_error_sleep_time_text}') ."
	</tr>	
<tr><td align='right' colspan='2' style='border-top:1px solid #CCCCCC'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('FFMAP','$page',true,true);\"></td></tr>
</table>")."<br>


" . RoundedLightWhite("<table style='width:500px'>
<tr><td colspan=2 ><H5 style='margin:0px;font-size:13px'>{title_postfix_tuning_2}</H5></td></tr>	
<tr><td colspan=2 >". helpCoolapse('{title_postfix_tuning_2_text}')."</td></tr>
	<tr>
		<td class=legend valign='top' nowrap>{smtpd_soft_error_limit}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('smtpd_soft_error_limit',$main->main_array["smtpd_soft_error_limit"],'width:20%',null,null,'{smtpd_soft_error_limit_text}') ."
	</tr>
	<tr>
		<td class=legend valign='top' nowrap>{smtpd_hard_error_limit}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('smtpd_hard_error_limit',$main->main_array["smtpd_hard_error_limit"],'width:20%',null,null,'{smtpd_hard_error_limit_text}') ."
	</tr>	
<tr><td align='right' colspan='2' style='border-top:1px solid #CCCCCC'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('FFMAP','$page',true,true);\"></td></tr>
</table>")."<br>
" . RoundedLightWhite("<table style='width:500px'>
<tr><td colspan=2 ><H5 style='margin:0px;font-size:13px'>{title_postfix_tuning_3}</H5></td></tr>	
<tr><td colspan=2 >". helpCoolapse('{title_postfix_tuning_3_text}')."</td></tr>	
	<tr>
		<td class=legend valign='top' nowrap>{smtpd_client_connection_count_limit}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('smtpd_client_connection_count_limit',$main->main_array["smtpd_client_connection_count_limit"],'width:20%',null,null,'{smtpd_client_connection_count_limit_text}') ."
	</tr>	
	<tr>
		<td class=legend valign='top' nowrap>{smtpd_client_connection_rate_limit}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('smtpd_client_connection_rate_limit',$main->main_array["smtpd_client_connection_rate_limit"],'width:20%',null,null,'{smtpd_client_connection_rate_limit_text}') ."
	</tr>	
	<tr>
		<td class=legend valign='top' nowrap>{smtpd_client_message_rate_limit}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('smtpd_client_message_rate_limit',$main->main_array["smtpd_client_message_rate_limit"],'width:20%',null,null,'{smtpd_client_message_rate_limit_text}') ."
	</tr>
	<tr>
		<td class=legend valign='top' nowrap>{smtpd_client_recipient_rate_limit}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('smtpd_client_recipient_rate_limit',$main->main_array["smtpd_client_recipient_rate_limit"],'width:20%',null,null,'{smtpd_client_recipient_rate_limit_text}') ."
	</tr>					
	<tr>
		<td class=legend valign='top' nowrap>{smtpd_client_new_tls_session_rate_limit}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('smtpd_client_new_tls_session_rate_limit',$main->main_array["smtpd_client_new_tls_session_rate_limit"],'width:20%',null,null,'{smtpd_client_new_tls_session_rate_limit_text}') ."
	</tr>
	<tr>
		<td class=legend valign='top' nowrap>{smtpd_client_event_limit_exceptions}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('smtpd_client_event_limit_exceptions',$main->main_array["smtpd_client_event_limit_exceptions"],'width:70%',null,null,'{smtpd_client_event_limit_exceptions_text}') ."
	</tr>
<tr><td align='right' colspan='2' style='border-top:1px solid #CCCCCC'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('FFMAP','$page',true,true);\"></td></tr>
</table>")."<br>
" . RoundedLightWhite("<table style='width:500px'>			
<tr><td colspan=2 ><H5 style='margin:0px;font-size:13px'>{title_postfix_tuning_4}</H5></td></tr>	
<tr><td colspan=2 >". helpCoolapse('{title_postfix_tuning_4_text}')."</td></tr>		
	<tr>
		<td class=legend valign='top' nowrap>{in_flow_delay}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('in_flow_delay',$main->main_array["in_flow_delay"],'width:10%',null,null,'{in_flow_delay_text}') ."
	</tr>	
	<tr>
		<td class=legend valign='top' nowrap>{smtp_connect_timeout}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('smtp_connect_timeout',$main->main_array["smtp_connect_timeout"],'width:50%',null,null,'{smtp_connect_timeout_text}') ."
	</tr>
	<tr>
		<td class=legend valign='top' nowrap>{smtp_helo_timeout}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('smtp_helo_timeout',$main->main_array["smtp_helo_timeout"],'width:50%',null,null,'{smtp_helo_timeout_text}') ."
	</tr>	
<tr><td align='right' colspan='2' style='border-top:1px solid #CCCCCC'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('FFMAP','$page',true,true);\"></td></tr>					

</table>")."<br>
" . RoundedLightWhite("<table style='width:500px'>
<tr><td colspan=2 ><H5 style='margin:0px;font-size:13px'>{title_postfix_tuning_8}</H5></td></tr>	
<tr><td colspan=2 >". helpCoolapse('{title_postfix_tuning_8_text}')."</td></tr>		
	<tr>
		<td class=legend valign='top' nowrap>{initial_destination_concurrency}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('initial_destination_concurrency',$main->main_array["initial_destination_concurrency"],'width:50%',null,null,'{initial_destination_concurrency_text}') ."
	</tr>
	<tr>
		<td class=legend valign='top' nowrap>{default_destination_concurrency_limit}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('default_destination_concurrency_limit',$main->main_array["default_destination_concurrency_limit"],'width:50%',null,null,'{default_destination_concurrency_limit_text}') ."
	</tr>
	<tr>
		<td class=legend valign='top' nowrap>{local_destination_concurrency_limit}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('local_destination_concurrency_limit',$main->main_array["local_destination_concurrency_limit"],'width:50%',null,null,'{local_destination_concurrency_limit_text}') ."
	</tr>
	<tr>
		<td class=legend valign='top' nowrap>{smtp_destination_concurrency_limit}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('smtp_destination_concurrency_limit',$main->main_array["smtp_destination_concurrency_limit"],'width:80%',null,null,'{smtp_destination_concurrency_limit_text}') ."
	</tr			
<tr><td align='right' colspan='2' style='border-top:1px solid #CCCCCC'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('FFMAP','$page',true,true);\"></td></tr>					
</table>")."<br>
" . RoundedLightWhite("<table style='width:500px'>

<tr><td colspan=2 ><H5 style='margin:0px;font-size:13px'>{title_postfix_tuning_5}</H5></td></tr>	
<tr><td colspan=2 >". helpCoolapse('{title_postfix_tuning_5_text}')."</td></tr>		
	<tr>
		<td class=legend valign='top' nowrap>{default_destination_recipient_limit}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('default_destination_recipient_limit',$main->main_array["default_destination_recipient_limit"],'width:50%',null,null,'{default_destination_recipient_limit_text}') ."
	</tr>	
	<tr>
		<td class=legend valign='top' nowrap>{smtpd_recipient_limit}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('smtpd_recipient_limit',$main->main_array["smtpd_recipient_limit"],'width:50%',null,null,'{smtpd_recipient_limit_text}') ."
	</tr>		
<tr><td align='right' colspan='2' style='border-top:1px solid #CCCCCC'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('FFMAP','$page',true,true);\"></td></tr>				
</table>")."<br>
" . RoundedLightWhite("<table style='width:500px'>

<tr><td colspan=2 ><H5 style='margin:0px;font-size:13px'>{title_postfix_tuning_6}</H5></td></tr>	
<tr><td colspan=2 >". helpCoolapse('{title_postfix_tuning_6_text}')."</td></tr>			
	<tr>
		<td class=legend valign='top' nowrap>{queue_run_delay}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('queue_run_delay',$main->main_array["queue_run_delay"],'width:50%',null,null,'{queue_run_delay_text}') ."
	</tr>	
	<tr>
		<td class=legend valign='top' nowrap>{minimal_backoff_time}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('minimal_backoff_time',$main->main_array["minimal_backoff_time"],'width:50%',null,null,'{minimal_backoff_time_text}') ."
	</tr>	
	<tr>
		<td class=legend valign='top' nowrap>{queue_run_delay}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('maximal_backoff_time',$main->main_array["maximal_backoff_time"],'width:50%',null,null,'{maximal_backoff_time_text}') ."
	</tr>	
	<tr>
		<td class=legend valign='top' nowrap>{maximal_queue_lifetime}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('maximal_queue_lifetime',$main->main_array["maximal_queue_lifetime"],'width:50%',null,null,'{maximal_queue_lifetime_text}') ."
	</tr>
	<tr>
		<td class=legend valign='top' nowrap>{bounce_queue_lifetime}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('bounce_queue_lifetime',$main->main_array["bounce_queue_lifetime"],'width:50%',null,null,'{bounce_queue_lifetime_text}') ."
	</tr>					
	<tr>
		<td class=legend valign='top' nowrap>{qmgr_message_recipient_limit}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('qmgr_message_recipient_limit',$main->main_array["qmgr_message_recipient_limit"],'width:50%',null,null,'{qmgr_message_recipient_limit_text}') ."
	</tr>
	<tr>
		<td class=legend valign='top' nowrap>{qmgr_message_recipient_minimum}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('qmgr_message_recipient_minimum',$main->main_array["qmgr_message_recipient_minimum"],'width:50%',null,null,'{qmgr_message_recipient_minimum_text}') ."
	</tr>		
<tr><td align='right' colspan='2' style='border-top:1px solid #CCCCCC'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('FFMAP','$page',true,true);\"></td></tr>							
<tr><td colspan=2 ><H5 style='margin:0px;font-size:13px'>{title_postfix_tuning_7}</H5></td></tr>	
<tr><td colspan=2 >". helpCoolapse('{title_postfix_tuning_7_text}')."</td></tr>
	<tr>
		<td class=legend valign='top' nowrap>{default_process_limit}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('default_process_limit',$main->main_array["default_process_limit"],'width:50%',null,null,'{default_process_limit_text}') ."
	</tr>		
<tr><td align='right' colspan='2' style='border-top:1px solid #CCCCCC'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('FFMAP','$page',true,true);\"></td></tr>				
</form>	
</table>")."	

</div>
</td>
</tr>
</table>";	


$tpl=new Templates();
echo $tpl->_ENGINE_parse_body($html);
}

function SaveForm(){
	$main=new main_cf();
	$main=new maincf_multi("master","master");
	while (list ($num, $val) = each ($_GET) ){
		writelogs("$num=\"$val\"",__FUNCTION__,__FILE__);
		$main->main_array[$num]=$val;
		$main->SET_VALUE($num,$val);
	}
	$main->save_conf();
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');
}




?>