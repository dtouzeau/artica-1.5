<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.main_cf.inc');
	
	
$usersmenus=new usersMenus();
if($usersmenus->AsMailBoxAdministrator==false){header('location:users.index.php');exit;}
if(isset($_GET["PagePostFixQueueTab"])){PagePostFixQueueTab();exit;}
if(isset($_GET["popup-index"])){popup_index();exit;}
js();

function js(){
$prefix=str_replace(".","_",CurrentPageName());
$page=CurrentPageName();
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{storage_rules}');
	
	$users=new usersMenus();
	if(!$users->AsPostfixAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}

	$js=file_get_contents("postfix.js");
	
$html="function {$prefix}Loadpage(){
	YahooWin2('650','$page?popup-index=yes','$title');
	}
$js
	
 {$prefix}Loadpage();
";	
 
 echo $html;
	
}


function popup_index(){
	$html="
		<script type=\"text/javascript\" language=\"javascript\" src=\"postfix.js\"></script>
		<div id='area'>" .PagePostFixQueue_1() . "</div>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
		}
		
	
	function PagePostFixQueue_1(){
		
		$main=new main_cf();
		$save="<input type='button' value='{submit}&raquo;' OnClick=\"javascript:ParseForm('ffmStorageQueue','tree.listener.postfix.php',true);\">";
		$html="
		<div id=tablist>
		<li><a href=\"javascript:PagePostFixQueueTab(1)\" id=tab_current>{bounce_messages_rules}</a></li>
		<li><a href=\"javascript:PagePostFixQueueTab(2)\">{queue_settings}</a></li>
		</div>
		
		<H5 style='margin:0px;margin-bottom:4px;font-size:14px;'>{bounce_messages_rules}</H5>
			<p class=caption >{bounce_messages_rules_text}</p>
		" . RoundedLightWhite("<div style='width:100%;height:400px;overflow:auto'>
		<form name='ffmStorageQueue'>
		<input type='hidden' name='save_bounce_maincf' value='yes'>
		<table>
		<tr><td colspan=2 align='rigth' style='padding-right:10px;text-align:right'>$save</td></tr>
		<tr>
			    <td nowrap align='right' class=legend>{soft_bounce}</strong>:</td>
			    <td>" . Field_yesno_checkbox_img('soft_bounce',$main->main_array["soft_bounce"],'{enable_disable}') . "</td>
		</tr>
		<tr>
			<td></td>
			<td><i>{soft_bounce_text}</td>
		</tr>
		<!-- -- --- --->
		<tr><td colspan=2>&nbsp;</td></tr>
		<tr>
			   <td nowrap align='right' class=legend>{bounce_queue_lifetime}</strong>:</td>
			    <td>" . Field_text('bounce_queue_lifetime',$main->main_array["bounce_queue_lifetime"],'width:20%')."</td>
		</tr>
		<tr>
			<td></td>
			<td><i>{bounce_queue_lifetime_text}</td>
		</tr>		
<!-- -- --- --->
		<tr><td colspan=2>&nbsp;</td></tr>
		<tr>
			   <td nowrap align='right' class=legend>{bounce_service_name}</strong>:</td>
			    <td>". Field_array_Hash($main->services_lists,'bounce_service_name',$main->main_array["bounce_service_name"]) ."</td>
		</tr>
		<tr>
			<td></td>
			<td><i>{bounce_service_name_text}</td>
		</tr>
<!-- -- --- --->
		<tr><td colspan=2>&nbsp;</td></tr>
		<tr>
			   <td nowrap align='right' class=legend>{bounce_size_limit}</strong>:</td>
			       <td>" . Field_text('bounce_size_limit',$main->main_array["bounce_size_limit"],'width:20%')." (bytes)</td>
		</tr>
		<tr>
			<td></td>
			<td><i>{bounce_size_limit_text}</td>
		</tr>							
<!-- -- --- --->
		<tr><td colspan=2>&nbsp;</td></tr>
		<tr>
			   <td nowrap align='right' valign='top'><strong>{bounce_template_file}</strong>:</td>
			       <td><table>
			       	<tr>
			       		<td width='1%'>" . $link=imgtootltip('edit.jpg','{edit}',"PostFixBounceTemplate('failure_template')") . "</td>
			       		<td><a href=\"javascript:PostFixBounceTemplate('failure_template');\"><strong>{failure_template}</strong></a>
			       	</tr>
			       	<tr>
			       		<td width='1%'>" . $link=imgtootltip('edit.jpg','{edit}',"PostFixBounceTemplate('delay_template')") . "</td>
			       		<td><a href=\"javascript:PostFixBounceTemplate('delay_template');\"><strong>{delay_template}</strong></a>
			       	</tr>
			       	<tr>
			       		<td width='1%'>" . $link=imgtootltip('edit.jpg','{edit}',"PostFixBounceTemplate('success_template')") . "</td>
			       		<td><a href=\"javascript:PostFixBounceTemplate('success_template');\"><strong>{success_template}</strong></a>
			       	</tr>
			       	<tr>
			       		<td width='1%'>" . $link=imgtootltip('edit.jpg','{edit}',"PostFixBounceTemplate('verify_template')") . "</td>
			       		<td><a href=\"javascript:PostFixBounceTemplate('verify_template');\"><strong>{verify_template}</strong></a>
			       	</tr>	
				</table>
			       </td>
		</tr>
		<tr>
			<td></td>
			<td><i>{bounce_template_file_text}</td>
		</tr>			
<!-- -- --- --->
		<tr><td colspan=2>&nbsp;</td></tr>
		<tr>
			   <td nowrap align='right' class=legend>{bounce_notice_recipient}</strong>:</td>
			       <td>" . Field_text('bounce_notice_recipient',$main->main_array["bounce_notice_recipient"],'width:40%')."</td>
		</tr>
		<tr>
			<td></td>
			<td><i>{bounce_notice_recipient_text}</td>
		</tr>							
<!-- -- --- --->	
		<tr><td colspan=2>&nbsp;</td></tr>
		<tr>
			   <td nowrap align='right' class=legend>{double_bounce_sender}</strong>:</td>
			       <td>" . Field_text('double_bounce_sender',$main->main_array["double_bounce_sender"],'width:40%')."</td>
		</tr>
		<tr>
			<td></td>
			<td><i>{double_bounce_sender_text}</td>
		</tr>							
<!-- -- --- --->			
		<tr><td colspan=2 align='rigth' style='padding-right:10px;text-align:right'>$save</td></tr>
	</table>
		</form></div>");
		
		$tpl=new templates();
		return $tpl->_ENGINE_parse_body($html);
}
	function PagePostFixQueue_2(){
		$main=new main_cf();
		$save="<input type='button' value='{submit}&raquo;' OnClick=\"javascript:ParseForm('ffmStorageQueue','tree.listener.postfix.php',true);\">";
		$html="
		<div id=tablist>
		<li><a href=\"javascript:PagePostFixQueueTab(1)\">{bounce_messages_rules}</a></li>
		<li><a href=\"javascript:PagePostFixQueueTab(2)\"  id=tab_current>{queue_settings}</a></li>
		</div>
		<H5 style='margin:0px;margin-bottom:4px;font-size:14px;'>{queue_settings}</H5>
		" . RoundedLightWhite("<div style='width:100%;height:400px;overflow:auto'>
		<form name='ffmStorageQueue'>
		<input type='hidden' name='save_bounce_maincf' value='yes'>
		<table>
		<tr><td colspan=2 align='rigth' style='padding-right:10px;text-align:right'>$save</td></tr>
		<tr>
			    <td nowrap align='right' class=legend>{maximal_queue_lifetime}</strong>:</td>
			    <td>" . Field_text('maximal_queue_lifetime',$main->main_array["maximal_queue_lifetime"],'width:40%')."</td>
		</tr>
		<tr>
			<td></td>
			<td><i>{maximal_queue_lifetime_text}</td>
		</tr>
		<!-- -- --- --->		
		<tr><td colspan=2 align='rigth' style='padding-right:10px;text-align:right'>$save</td></tr>
	</table>
		</form></div>");
		
		$tpl=new templates();
		return $tpl->_ENGINE_parse_body($html);
		
	}
	
function PagePostFixQueueTab(){
	$pages=new HtmlPages();
	if($_GET["PagePostFixQueueTab"]==1){echo PagePostFixQueue_1();}
	if($_GET["PagePostFixQueueTab"]==2){echo PagePostFixQueue_2();}
	}			
	
?>