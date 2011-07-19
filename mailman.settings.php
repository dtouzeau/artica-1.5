<?php
include_once('ressources/class.mailman.inc');





if(isset($_GET["action_applysettings"])){action_applysettings();exit;}
if(isset($_POST["id"])){$_GET["id"]=$_POST["id"];}
if(!isset($_GET["id"])){exit;}	

if(isset($_GET["gs"])){echo Defaultspy();exit;}
if(isset($_GET["GlobalAdministrationParameters"])){Defaultspy_save();exit;}

if(isset($_GET["mailman_add_new_distribution_list"])){mailman_add_new_distribution_list();exit;}
if(isset($_GET["mailman_delete_distribution_list"])){mailman_delete_distribution_list();exit;}

if(isset($_GET["mailman_create_robots_from_domain"])){mailman_create_robots_from_domain();exit;}
if(isset($_GET["mailman_addresses"])){mailman_addresses();exit;}
if(isset($_GET["mailman_addresses_list"])){echo mailman_addresses_list();exit;}
if(isset($_GET["mailman_change_email_robot"])){mailman_change_email_robot();exit;}
if(isset($_GET["mailman_delete_robot"])){mailman_delete_robot();exit;}


if(isset($_GET["loadApplySettings"])){loadApplySettings();exit;}
if(isset($_GET["affected_org"])){affectedou();exit;}
if(isset($_GET["GeneralOptions"])){echo GeneralOptions();exit;}
if(isset($_GET["real_name"])){GeneralOptions_save();exit;}
if(isset($_GET["send_reminders"])){GeneralOptions_save();exit;}
if(isset($_GET["max_message_size"])){GeneralOptions_save();exit;}
if(isset($_GET["preferred_language"])){GeneralOptions_save();exit;}
if(isset($_GET["nondigestable"])){GeneralOptions_save();exit;}
if(isset($_GET["mime_is_default_digest"])){GeneralOptions_save();exit;}
if(isset($_GET["advertised"])){GeneralOptions_save();exit;}
if(isset($_GET["default_member_moderation"])){GeneralOptions_save();exit;}
if(isset($_GET["require_explicit_destination"])){GeneralOptions_save();exit;}
if(isset($_GET["bounce_processing"])){GeneralOptions_save();exit;}
IF(isset($_GET["bounce_unrecognized_goes_to_list_owner"])){GeneralOptions_save();EXIT;}
IF(isset($_GET["archive_private"])){GeneralOptions_save();EXIT;}
if(isset($_GET["nntp_host"])){GeneralOptions_save();EXIT;}
if(isset($_GET["autorespond_postings"])){GeneralOptions_save();EXIT;}
if(isset($_GET["filter_content"])){GeneralOptions_save();EXIT;}
if(isset($_GET["topics_enabled"])){GeneralOptions_save();EXIT;}


if(isset($_GET["content_filtering_table"])){echo content_filtering_table_filter();exit;}

if(isset($_GET["topic_name"])){topics_add();exit;}
if(isset($_GET["mailman_delete_topics"])){topics_delete();exit;}
if(isset($_GET["topic_list"])){echo topics_list();exit;}
if(isset($_GET["mailman_move_topics"])){topic_move();exit;}


if(isset($_GET["newsg"])){newsg();exit;}
if(isset($_GET["autoresponder"])){autoresponder();exit;}



if(isset($_GET["member_moderation_notice"])){member_moderation_notice_save();exit;}
if(isset($_GET["nonmember_rejection_notice"])){nonmember_rejection_notice_save();exit;}

if(isset($_GET["digest_header"])){digest_header_save();exit;}
if(isset($_GET["digest_footer"])){digest_footer_save();exit;}



if(isset($_GET["moderators"])){echo moderators();exit;}
if(isset($_GET["moderators2"])){echo moderators2();exit;}
if(isset($_GET["add_moderator"])){moderators_add();exit;}
if(isset($_GET["add_moderator2"])){moderators_add2();exit;}

if(isset($_GET["add_banlist"])){banlist_add();exit;}
if(isset($_GET["delete_banlist"])){banlist_del();exit;}


if(isset($_GET["add_accept_these_nonmembers"])){accept_these_nonmembers_add();exit;}
if(isset($_GET["delete_accept_these_nonmembers"])){accept_these_nonmembers_del();exit;}

if(isset($_GET["add_hold_these_nonmembers"])){hold_these_nonmembers_add();exit;}
if(isset($_GET["delete_hold_these_nonmembers"])){hold_these_nonmembers_del();exit;}


if(isset($_GET["add_reject_these_nonmembers"])){reject_these_nonmembers_add();exit;}
if(isset($_GET["delete_reject_these_nonmembers"])){reject_these_nonmembers_del();exit;}


if(isset($_GET["add_discard_these_nonmembers"])){discard_these_nonmembers_add();exit;}
if(isset($_GET["delete_discard_these_nonmembers"])){discard_these_nonmembers_del();exit;}

if(isset($_GET["add_acceptable_aliases"])){acceptable_aliases_add();exit;}
if(isset($_GET["delete_acceptable_aliases"])){acceptable_aliases_del();exit;}

if(isset($_POST["header_filter_rules_action"])){header_filter_rules_add();exit;}
if(isset($_GET["delete_header_filter_rules"])){header_filter_rules_del();exit;}
if(isset($_POST["header_filter_rules_pattern_move"])){header_filter_rules_move();exit;}


if(isset($_GET["bounce_matching_headers"])){bounce_matching_headers_add();exit;}
if(isset($_GET["delete_bounce_matching_headers"])){bounce_matching_headers_del();exit;}

if(isset($_GET["filter_filename_extensions_add"])){filter_filename_extensions_add();exit;}
if(isset($_GET["filter_mime_types_add"])){filter_mime_types_add();exit;}
if(isset($_GET["delete_filter_mime_types"])){filter_mime_types_del();exit;}
if(isset($_GET["delete_filter_filename_extensions"])){filter_filename_extensions_del();exit;}

if(isset($_GET["bounce"])){bounce();exit;}
if(isset($_GET["archives"])){archive();exit;}


if(isset($_GET["delete_moderator"])){moderators_del();exit;}
if(isset($_GET["delete_moderator2"])){moderators_del2();exit;}
if(isset($_GET["welcome"])){echo welcome();exit;}
if(isset($_GET["goodbye"])){echo goodby();exit;}
if(isset($_GET["send_welcome_msg"])){welcome_save();exit;}
if(isset($_GET["send_goodbye_msg"])){goodbye_save();exit;}
if(isset($_GET["notifications"])){echo notifications();exit;}
if(isset($_GET["NewMemberOption"])){echo NewMemberOption();exit;}
if(isset($_GET["new_member_options"])){NewMemberOption_save();exit;}
if(isset($_GET["limits"])){echo limits();exit;}
if(isset($_GET["headers"])){echo headers();exit;}
if(isset($_GET["lang"])){echo language();exit;}
if(isset($_GET["deliver"])){echo deliver();exit;}
if(isset($_GET["attachments"])){echo attachments();exit;}
if(isset($_GET["digest"])){echo digest();exit;}

if(isset($_GET["msgheader"])){echo msg_header();exit;}
if(isset($_GET["msg_header"])){msg_header_save();exit;}

if(isset($_GET["msgfooter"])){echo msg_footer();exit;}
if(isset($_GET["msg_footer"])){msg_footer_save();exit;}

if(isset($_GET["nondigests"])){nondigests();exit;}
if(isset($_GET["contentfiltering"])){content_filtering();exit;}

if(isset($_GET["topics"])){topics();exit;}

if(isset($_GET["add_language"])){language_add();exit;}
if(isset($_GET["delete_available_languages"])){language_del();exit;}
if(isset($_GET["available_languages"])){echo available_languages();exit;}

if(isset($_GET["privacy"])){echo privacy();exit;}


SecGlobal();


function affectedou(){
	$id=$_GET["id"];
	$mailman=new mailman($id);
	$mailman->mailmanouowner=$_GET["affected_org"];
	$mailman->SaveOu();
	}


function privacy_tabs(){
	if(!isset($_GET["tab"])){$_GET["tab"]=0;};
	$page=CurrentPageName();
	$array[]='{main}';
	$array[]='{banlist}';
	$array[]='{sender_filters}';
	$array[]='{recipient_filters}';
	$array[]='{spam_filters}';
	
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["tab"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('{$_GET["id"]}_OptionsContent','$page?privacy=yes&id={$_GET["id"]}&tab=$num')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";		
}
function privacy_sender_tabs(){
	if(!isset($_GET["tab1"])){$_GET["tab1"]=0;};
	$page=CurrentPageName();
	$array[]='{main}';
	$array[]='{member_moderation_notice}';
	$array[]='{accept_these_nonmembers}';
	$array[]='{hold_these_nonmembers}';
	$array[]='{reject_these_nonmembers}';
	$array[]='{discard_these_nonmembers}';
	$array[]='{nonmember_rejection_notice}';
	
while (list ($num, $ligne) = each ($array) ){
		if($_GET["tab1"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('{$_GET["id"]}_OptionsContent','$page?privacy=yes&id={$_GET["id"]}&tab=2&tab1=$num')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";		
}
function spam_filters_tabs(){
	if(!isset($_GET["tab1"])){$_GET["tab1"]=0;};
	$page=CurrentPageName();
	$array[]='{header_filter_rules}';
	$array[]='{bounce_matching_headers}';

	
while (list ($num, $ligne) = each ($array) ){
		if($_GET["tab1"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('{$_GET["id"]}_OptionsContent','$page?privacy=yes&id={$_GET["id"]}&tab=4&tab1=$num')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";		
}

function privacy_sender_member_moderation_notice(){
$mailman=new mailman($_GET["id"]);
$html="<H3>{member_moderation_notice} &laquo;{$_GET["id"]}&raquo;</h3><br> <p>" . privacy_tabs() . "</p>" . privacy_sender_tabs() . "<br>
<form name=ffm1>
<input type='hidden' name='id' value='{$_GET["id"]}'>
<p class='caption'>{member_moderation_notice_text}</p>
<table style='width:100%'>
</table>
<textarea name='member_moderation_notice' id='member_moderation_notice' style='width:100%' rows=5>{$mailman->main_array["member_moderation_notice"]}</textarea>
</form>
<div style='text-align:right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm1','" . CurrentPageName() . "',true)\"></div>
";
$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html);	
}
function privacy_sender_nonmember_rejection_notice(){
$mailman=new mailman($_GET["id"]);
$html="<H3>{nonmember_rejection_notice} &laquo;{$_GET["id"]}&raquo;</h3><br> <p>" . privacy_tabs() . "</p>" . privacy_sender_tabs() . "<br>
<form name=ffm1>
<input type='hidden' name='id' value='{$_GET["id"]}'>
<p class='caption'>{nonmember_rejection_notice_text}</p>
<table style='width:100%'>
</table>
<textarea name='nonmember_rejection_notice' id='nonmember_rejection_notice' style='width:100%' rows=5>{$mailman->main_array["nonmember_rejection_notice"]}</textarea>
</form>
<div style='text-align:right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm1','" . CurrentPageName() . "',true)\"></div>
";
$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html);	
}


function archive(){
$mailman=new mailman($_GET["id"]);
$archfreq=array(0 =>"{yearly}",1 =>"{monthly}",2 =>"{quarterly}",3 =>"{weekly}",4 =>"{daily}");

$html="<H3>{archiving_options} &laquo;{$_GET["id"]}&raquo;</h3>
<form name=ffm1>
<input type='hidden' name='id' value='{$_GET["id"]}'>
<br>".RoundedLightGrey("
<table style='width:100%'>

<tr>
<td align='right'><strong>{archive}:</strong></td>
<td>". Field_numeric_checkbox_img('archive',$mailman->main_array["archive"],'{archive_text}')."</td>
</tr>

<tr>
<td align='right'><strong>{archive_private}:</strong></td>
<td>". Field_numeric_checkbox_img('archive_private',$mailman->main_array["archive_private"],'{archive_private_text}')."</td>
</tr>

<tr>
<td align='right'><strong>{archive_volume_frequency}:</strong></td>
<td>". Field_array_Hash($archfreq,'archive_private',$mailman->main_array["archive_private"])."</td>
</tr>



</table><div style='text-align:right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm1','" . CurrentPageName() . "',true)\"></div>");

$tpl=new templates();
echo  $tpl->_ENGINE_parse_body($html);	
}

function topics_add(){
	$mailman=new mailman($_GET["id"]);
	$_GET["topic_pattern"]=FileRegex($_GET["topic_pattern"]);
	$_GET["topic_desc"]=FileRegex($_GET["topic_desc"]);
	
	$mailman->topics[]=array("name"=>$_GET["topic_name"],"pattern"=>$_GET["topic_pattern"],"desc"=>$_GET["topic_desc"]);
	$mailman->Save();	
	}
	
function topics_delete(){
	$mailman=new mailman($_GET["id"]);
	unset($mailman->topics[$_GET["mailman_delete_topics"]]);
	$mailman->Save();	
	
}

function topic_move(){
	$num=$_GET["mailman_move_topics"];
	$mailman=new mailman($_GET["id"]);
	$mailman->topics=array_move_element($mailman->topics,$mailman->topics[$num],$_GET["move"]);
	$mailman->Save();		
}
	
function FileRegex($data){
	$data=str_replace("#p",".+",$data);
	$data=str_replace("#CRLF",'\\r\\n',$data);
	$data=str_replace("\'","'",$data);
	return $data;
	
}

	
function topics_list(){
	$id=$_GET["id"];
	$mailman=new mailman($_GET["id"]);

	$table="<table style='width:100%'>";
	if(is_array($mailman->topics)){
	while (list ($num, $val) = each ($mailman->topics) ){
		$table=$table. "<tr>
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td><strong>{$val["name"]}</strong></td>
			<td><strong>{$val["pattern"]}</strong></td>
			<td>" . imgtootltip('x.gif','{delete}',"mailman_delete_topics('$id','$num')")."</td>
			<td>" . imgtootltip('arrow_up.gif','{up}',"mailman_move_topics('$id','$num','up')")."</td>
			<td>" . imgtootltip('arrow_down.gif','{down}',"mailman_move_topics('$id','$num','down')")."</td>
		</tr>";
	}}

	$table=$table . "</table>";
	$table=RoundedLightGrey($table);
	$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html.$table);
}	
	
function topics(){
$mailman=new mailman($_GET["id"]);
$id=$_GET["id"];
$archfreq=array(0 =>"{yearly}",1 =>"{monthly}",2 =>"{quarterly}",3 =>"{weekly}",4 =>"{daily}");

$html="<H3>{topics} &laquo;{$_GET["id"]}&raquo;</h3>
<form name=ffm1>
<input type='hidden' name='id' value='{$_GET["id"]}'>
<table><tr><td><H5>{topics}</H5></td><td>" . help_icon('{topics_text}',true)."</td></tr></table>
<br>".RoundedLightGrey("
<table style='width:100%'>

<tr>
<td align='right'><strong>{topics_enabled}:</strong></td>
<td>". Field_numeric_checkbox_img('topics_enabled',$mailman->main_array["topics_enabled"],'{topics_enabled}')."</td>
</tr>

<tr>
<td align='right'><strong>{topics_bodylines_limit}:</strong></td>
<td>". Field_text('topics_bodylines_limit',$mailman->main_array["topics_bodylines_limit"],'width:30%',null,null,'{topics_bodylines_limit_text}')."</td>
</tr>

</table><div style='text-align:right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm1','" . CurrentPageName() . "',true)\"></div></form>");


$form1="<form name=ffm2>
<input type='hidden' name='id' id='id' value='{$_GET["id"]}'>
<input type='hidden' name='topicid' value='{$_GET["topicid"]}'>
<table style='width:100%'>
<tr>
<td align='right'><strong>{topic_name}:</strong></td>
<td>". Field_text('topic_name')."</td>
</tr>
<tr>
<td align='right' nowrap><strong>{topic_pattern}:</strong></td>
<td>". Field_text('topic_pattern')."</td>
</tr>
<tr>
<td align='right' nowrap><strong>{topic_desc}:</strong></td>
<td><textarea id='topic_desc'></textarea></td>
</tr>


</form>
</table><div style='text-align:right'><input type='button' value='{add}&nbsp;&raquo;' OnClick=\"javascript:mailman_add_topic();\"></div>";

$form1=RoundedLightGreen($form1);

$list="<br><div id={$id}_list>" . topics_list() . "</div>";

$tpl=new templates();
echo  $tpl->_ENGINE_parse_body($html. "<br>" . $form1 . $list);	
}

function autoresponder(){
$mailman=new mailman($_GET["id"]);
$arr=array(0=>'{none}',1=>'{openl}',2=>'{moderated}');

$arr1=array(0 => "No", 1 => "Yes, w/discard",2 => "Yes, w/forward");

$html="<H3>{autoresponder} &laquo;{$_GET["id"]}&raquo;</h3>
<form name=ffm1>
<input type='hidden' name='id' value='{$_GET["id"]}'>
<H5>{autoresponder_text}</H5>
<br>".RoundedLightGrey("
<table style='width:100%'>

<tr>
<td align='right'><strong>{autorespond_postings}:</strong></td>
<td>". Field_numeric_checkbox_img('autorespond_postings',$mailman->main_array["autorespond_postings"],'{autorespond_postings_text}')."</td>
</tr>
<tr>
<td align='right'><strong>{autorespond_admin}:</strong></td>
<td>". Field_numeric_checkbox_img('autorespond_admin',$mailman->main_array["autorespond_admin"],'{autorespond_admin_text}')."</td>
</tr>
<tr>
<td align='right'><strong>{autorespond_requests}:</strong></td>
<td><table><tr><td>". Field_array_Hash($arr1,'autorespond_requests',$mailman->main_array["autorespond_requests"])."</td><td>" . help_icon('{autorespond_requests_text}',true)."</td></tr></table></td>
</tr>
<tr>
<td align='right'><strong>{autoresponse_graceperiod}:</strong></td>
<td>". Field_text('autoresponse_graceperiod',$mailman->main_array["autoresponse_graceperiod"],'width:70%',null,null,'{autoresponse_graceperiod_text}')."</td>
</tr>
<tr>
<td align='left' colspan=2><strong>{autoresponse_postings_text}:</strong></td>
</tr>
<tr>
<td align='right' colspan=2><textarea name='autoresponse_postings_text' style='width:100%' rows=5>{$mailman->main_array["autoresponse_postings_text"]}</textarea></td>
</tr>

<tr>
<td align='left' colspan=2><strong>{autoresponse_admin_text}:</strong></td>
</tr>
<tr>
<td align='right' colspan=2><textarea name='autoresponse_admin_text' style='width:100%' rows=5>{$mailman->main_array["autoresponse_admin_text"]}</textarea></td>
</tr>


<tr>
<td align='left' colspan=2><strong>{autoresponse_request_text}:</strong></td>
</tr>
<tr>
<td align='right' colspan=2><textarea name='autoresponse_request_text' style='width:100%' rows=5>{$mailman->main_array["autoresponse_request_text"]}</textarea></td>
</tr>


</table><div style='text-align:right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm1','" . CurrentPageName() . "',true)\"></div>");

$tpl=new templates();
echo  $tpl->_ENGINE_parse_body($html);	
}



function content_filtering_tabs(){
	if(!isset($_GET["tab"])){$_GET["tab"]=0;};
	$page=CurrentPageName();
	$array[]='{include}';
	$array[]='{exclude}';
	
	
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["tab"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('{$_GET["id"]}_OptionsContent','$page?contentfiltering=yes&id={$_GET["id"]}&tab=$num')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";		
}


function content_filtering(){
$mailman=new mailman($_GET["id"]);
$arr=array(0 => "{discard}",1 => "{reject}",2 =>"{forward_to_list_owner}",3 => "{preserve}");

$id=$_GET["id"];

$html="

<input type='hidden' id ='filter_mime_types_add' value='{filter_mime_types_add}'>
<input type='hidden' id ='filter_filename_extensions_add' value='{filter_filename_extensions_add}'>
<H3>{contentfiltering} &laquo;{$_GET["id"]}&raquo;</h3>
<form name=ffm1>
<input type='hidden' name='id' id='id' value='{$_GET["id"]}'>
<table><tr><td><H5>{contentfiltering}</H5></td><td>" . help_icon('{contentfiltering_text}',true)."</td></tr></table>
<br>".RoundedLightGrey("
<table style='width:100%'>

<tr>
<td align='right'><strong>{filter_content}:</strong></td>
<td>". Field_numeric_checkbox_img('filter_content',$mailman->main_array["filter_content"],'{filter_content_text}')."</td>
</tr>
<tr>
<td align='right'><strong>{collapse_alternatives}:</strong></td>
<td>". Field_numeric_checkbox_img('collapse_alternatives',$mailman->main_array["collapse_alternatives"],'{collapse_alternatives_text}')."</td>
</tr>
<tr>
<td align='right'><strong>{convert_html_to_plaintext}:</strong></td>
<td>". Field_numeric_checkbox_img('convert_html_to_plaintext',$mailman->main_array["convert_html_to_plaintext"],'{convert_html_to_plaintext_text}')."</td>
</tr>
<tr>
<td align='right'><strong>{filter_action}:</strong></td>
<td><table><tr><td>". Field_array_Hash($arr,'filter_action',$mailman->main_array["filter_action"])."</td><td>" . help_icon('{filter_action_text}',true)."</td></tr></table></td>
</tr>
</table><div style='text-align:right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm1','" . CurrentPageName() . "',true)\"></div></form>");

$intro="
<br>" . content_filtering_tabs() . "<br>";


$formsb="<div style='text-align:center'>
<table style='width:100%'>

<tr>
	<td style='width:50%' align='center'><input type='button' value='{filter_mime_types_but}&nbsp;&raquo;' OnClick=\"javascript:filter_mime_types_add({$_GET["tab"]});\"></td>
	<td style='width:50%' align='center'><input type='button' value='{filter_filename_extensions_but}&nbsp;&raquo;' OnClick=\"javascript:filter_filename_extensions_add({$_GET["tab"]});\"></td>
	
</tr>
</table>

</div>
";

$list=RoundedLightGrey("<div id='{$id}_table'>" . content_filtering_table_filter() . "</div>");

$formsb=RoundedLightGreen($formsb);
$tpl=new templates();
echo  $tpl->_ENGINE_parse_body($html."<br>$intro<div id='{$id}_filters'>".$formsb . "<br>" . $list . "</div>");			
}


function content_filtering_table_filter(){
	$mailman=new mailman($_GET["id"]);
	if(!isset($_GET["tab"])){$_GET["tab"]=0;};
	
	$id=$_GET["id"];
	if($_GET["tab"]==1){
		$array_1=$mailman->pass_mime_types;
		$array_2=$mailman->pass_filename_extensions;
		$title='<H7>{exclude_explain}</H7>';
		}
	else{
		$array_1=$mailman->filter_mime_types;
		$array_2=$mailman->filter_filename_extensions;
		$title='<H7>{include_explain}</H7>';
		}
		
		
	
$table="<table style='width:100%'>";
	if(is_array($array_1)){
	while (list ($num, $val) = each ($array_1) ){
		if(trim($val)<>null){
	$table=$table. "<tr>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong>$val</strong></td>
		<td>" . imgtootltip('x.gif','{delete}',"mailman_delete_filter_mime_types('$id','$num','{$_GET["tab"]}')")."</td>
	</tr>";
	}}}

	$table=$table . "</table>";


$num=0;	
$table1="<table style='width:100%'>";
	if(is_array($array_2)){
	while (list ($num, $val) = each ($array_2) ){
		if(trim($val)<>null){
	$table1=$table1. "<tr>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong>$val</strong></td>
		<td>" . imgtootltip('x.gif','{delete}',"mailman_delete_filter_filename_extensions('$id','$num','{$_GET["tab"]}')")."</td>
	</tr>";
	}}}

	$table1=$table1 . "</table>";

$html="<table style='width:100%'>
<tr>
<td style='border-right:1px solid #CCCCCC;padding:3px' valign='top' width=50%>$table</td>
<td style='padding:3px' valign='top' width=50%>$table1</td>
</tr>
</table>
"	;
	
	
	$tpl=new templates();	
	return $tpl->_ENGINE_parse_body($title.$html);
	
}


function newsg(){
$mailman=new mailman($_GET["id"]);
$arr=array(0=>'{none}',1=>'{openl}',2=>'{moderated}');
$html="<H3>{mail_new_gateways} &laquo;{$_GET["id"]}&raquo;</h3>
<form name=ffm1>
<input type='hidden' name='id' value='{$_GET["id"]}'>
<H5>{mail_new_gateways_text}</H5>
<br>".RoundedLightGrey("
<table style='width:100%'>

<tr>
<td align='right'><strong>{nntp_host}:</strong></td>
<td>". Field_text('nntp_host',$mailman->main_array["nntp_host"],'width:70%',null,null,'{nntp_host_text}')."</td>
</tr>


<tr>
<td align='right'><strong>{linked_newsgroup}:</strong></td>
<td>". Field_text('linked_newsgroup',$mailman->main_array["linked_newsgroup"],'width:70%',null,null,'{linked_newsgroup_text}')."</td>
</tr>



<tr>
<td align='right'><strong>{gateway_to_news}:</strong></td>
<td>". Field_numeric_checkbox_img('gateway_to_news',$mailman->main_array["gateway_to_news"],'{gateway_to_news_text}')."</td>
</tr>
<tr>
<td align='right'><strong>{gateway_to_mail}:</strong></td>
<td>". Field_numeric_checkbox_img('gateway_to_mail',$mailman->main_array["gateway_to_mail"],'{gateway_to_mail_text}')."</td>
</tr>

<tr>
<td align='right'><strong>{news_moderation}:</strong></td>
<td><table><tr><td>". Field_array_Hash($arr,'news_moderation',$mailman->main_array["news_moderation"])."</td><td>" . help_icon('{news_moderation_text}',true)."</td></tr></table></td>
</tr>
<tr>
<td align='right'><strong>{news_prefix_subject_too}:</strong></td>
<td>". Field_numeric_checkbox_img('news_prefix_subject_too',$mailman->main_array["news_prefix_subject_too"],'{news_prefix_subject_too_text}')."</td>
</tr>
</table><div style='text-align:right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm1','" . CurrentPageName() . "',true)\"></div>");

$tpl=new templates();
echo  $tpl->_ENGINE_parse_body($html);	
}





function privacy_sender_accept_these_nonmembers(){
	$id=$_GET["id"];
	$mailman=new mailman($_GET["id"]);
	$html="<H3>{accept_these_nonmembers} &laquo;{$_GET["id"]}&raquo;</h3><br> <p>" . privacy_tabs() . "</p>" . privacy_sender_tabs() . "<br>
	<p class=caption>{accept_these_nonmembers_text}</p>
	<input type='hidden' id='add_moderator_input' value='{add_moderator_input}'>
	<div style='padding:4px'><center><input type='button' value='{add_moderator}' OnClick=\"javascript:mailman_add_accept_these_nonmembers('$id');\"></center></div>
	";
	
	$table="<table style='width:100%'>";
	if(is_array($mailman->accept_these_nonmembers)){
	while (list ($num, $val) = each ($mailman->accept_these_nonmembers) ){
	$table=$table. "<tr>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong>$val</strong></td>
		<td>" . imgtootltip('x.gif','{delete}',"mailman_delete_accept_these_nonmembers('$id','$num')")."</td>
	</tr>";
	}}

	$table=$table . "</table>";
	$table=RoundedLightGrey($table);
	$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html.$table);
}
function privacy_sender_hold_these_nonmembers(){
	$id=$_GET["id"];
	$mailman=new mailman($_GET["id"]);
	$html="<H3>{accept_these_nonmembers} &laquo;{$_GET["id"]}&raquo;</h3><br> <p>" . privacy_tabs() . "</p>" . privacy_sender_tabs() . "<br>
	<p class=caption>{hold_these_nonmembers_text}</p>
	<input type='hidden' id='add_moderator_input' value='{add_moderator_input}'>
	<div style='padding:4px'><center><input type='button' value='{add_moderator}' OnClick=\"javascript:mailman_add_hold_these_nonmembers('$id');\"></center></div>
	";
	
	$table="<table style='width:100%'>";
	if(is_array($mailman->hold_these_nonmembers)){
	while (list ($num, $val) = each ($mailman->hold_these_nonmembers) ){
	$table=$table. "<tr>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong>$val</strong></td>
		<td>" . imgtootltip('x.gif','{delete}',"mailman_delete_hold_these_nonmembers('$id','$num')")."</td>
	</tr>";
	}}

	$table=$table . "</table>";
	$table=RoundedLightGrey($table);
	$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html.$table);
}


function acceptable_aliases_list(){
	$id=$_GET["id"];
	$mailman=new mailman($_GET["id"]);	
$table="<table style='width:100%'>";
	if(is_array($mailman->acceptable_aliases)){
	while (list ($num, $val) = each ($mailman->acceptable_aliases) ){
	$table=$table. "<tr>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong>$val</strong></td>
		<td>" . imgtootltip('x.gif','{delete}',"mailman_delete_acceptable_aliases('$id','$num')")."</td>
	</tr>";
	}}

	$table=$table . "</table>";	
	$tpl=new templates();
	return  $tpl->_ENGINE_parse_body($html.$table);	
}

function privacy_sender_reject_these_nonmembers(){
	$id=$_GET["id"];
	$mailman=new mailman($_GET["id"]);
	$html="<H3>{reject_these_nonmembers} &laquo;{$_GET["id"]}&raquo;</h3><br> <p>" . privacy_tabs() . "</p>" . privacy_sender_tabs() . "<br>
	<p class=caption>{reject_these_nonmembers_text}</p>
	<input type='hidden' id='add_moderator_input' value='{add_moderator_input}'>
	<div style='padding:4px'><center><input type='button' value='{add_moderator}' OnClick=\"javascript:mailman_add_reject_these_nonmembers('$id');\"></center></div>
	";
	
	$table="<table style='width:100%'>";
	if(is_array($mailman->reject_these_nonmembers)){
	while (list ($num, $val) = each ($mailman->reject_these_nonmembers) ){
	$table=$table. "<tr>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong>$val</strong></td>
		<td>" . imgtootltip('x.gif','{delete}',"mailman_delete_reject_these_nonmembers('$id','$num')")."</td>
	</tr>";
	}}

	$table=$table . "</table>";
	$table=RoundedLightGrey($table);
	$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html.$table);	
}
function privacy_sender_discard_these_nonmembers(){
	$id=$_GET["id"];
	$mailman=new mailman($_GET["id"]);
	$html="<H3>{discard_these_nonmembers} &laquo;{$_GET["id"]}&raquo;</h3><br> <p>" . privacy_tabs() . "</p>" . privacy_sender_tabs() . "<br>
	<p class=caption>{discard_these_nonmembers_text}</p>
	<input type='hidden' id='add_moderator_input' value='{add_moderator_input}'>
	<div style='padding:4px'><center><input type='button' value='{add_moderator}' OnClick=\"javascript:mailman_add_discard_these_nonmembers('$id');\"></center></div>
	";
	
	$table="<table style='width:100%'>";
	if(is_array($mailman->discard_these_nonmembers)){
	while (list ($num, $val) = each ($mailman->discard_these_nonmembers) ){
	$table=$table. "<tr>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong>$val</strong></td>
		<td>" . imgtootltip('x.gif','{delete}',"mailman_delete_discard_these_nonmembers('$id','$num')")."</td>
	</tr>";
	}}

	$table=$table . "</table>";
	$table=RoundedLightGrey($table);
	$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html.$table);	
}
//



function privacy_banlist(){
	$id=$_GET["id"];
$mailman=new mailman($_GET["id"]);
$html="<H3>{banlist} &laquo;{$_GET["id"]}&raquo;</h3>
<p>" . privacy_tabs() . "</p>
<p class=caption>{banlist_text}</p>	
<input type='hidden' id='add_moderator_input' value='{add_moderator_input}'>
<div style='padding:4px'><center><input type='button' value='{add_moderator}' OnClick=\"javascript:mailman_add_banlist('$id');\"></center></div>
<div id=ban_lists>
";
	
	$table="<table style='width:100%'>";
	if(is_array($mailman->ban_list)){
	while (list ($num, $val) = each ($mailman->ban_list) ){
	$table=$table. "<tr>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong>$val</strong></td>
		<td>" . imgtootltip('x.gif','{delete}',"mailman_delete_banlist('$id','$num')")."</td>
	</tr>";
	}}

	$table=$table . "</table></div>";
	$table=RoundedLightGrey($table);
	$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html.$table);	
	
}

function privacy_sender_filters(){
	$id=$_GET["id"];
	$mailman=new mailman($_GET["id"]);
	$member_moderation_action_arr=array(0 =>"{hold}",1 =>"{reject}",2 =>"{discard}");
	$generic_nonmember_action=array(0 =>"{accept}",1 =>"{hold}",2 =>"{reject}",3 =>"{discard}");
	
	switch ($_GET["tab1"]) {
		case 1:return privacy_sender_member_moderation_notice();break;
		case 2:return privacy_sender_accept_these_nonmembers();break;
		case 3:return privacy_sender_hold_these_nonmembers();break;
		case 4:return privacy_sender_reject_these_nonmembers();break;
		case 5:return privacy_sender_discard_these_nonmembers();break;
		case 6:return privacy_sender_nonmember_rejection_notice();break;
		
		default:
			break;
	}
	
	
$html="<H3>{sender_filters} &laquo;{$_GET["id"]}&raquo;</h3>
<p>" . privacy_tabs() . "</p>" . privacy_sender_tabs() . "
<p class=caption>{sender_filters_text}</p>
<form name=ffm1>
<input type='hidden' name='id' value='{$_GET["id"]}'>
<p class='caption'>{privacy_options_text}</p>	<br>".RoundedLightGrey("
<table style='width:100%'>
<tr>
<td align='right'><strong>{default_member_moderation}:</strong></td>
<td>". Field_numeric_checkbox_img('default_member_moderation',$mailman->main_array["default_member_moderation"],'{default_member_moderation_text}')."</td>
</tr>
<tr>
<td align='right'><strong>{member_moderation_action}:</strong></td>
<td><table><tr><td>". Field_array_Hash($member_moderation_action_arr,'member_moderation_action',$mailman->main_array["member_moderation_action"],null,null,0,'width:150px')."</td><td>" . help_icon('{member_moderation_action_text}',true)."</td></tr></table></td>
</tr>
<tr>
<td align='right'><strong>{forward_auto_discards}:</strong></td>
<td>". Field_numeric_checkbox_img('forward_auto_discards',$mailman->main_array["forward_auto_discards"],'{forward_auto_discards_text}')."</td>
</tr>
<tr>
<td align='right'><strong>{generic_nonmember_action}:</strong></td>
<td><table><tr><td>". Field_array_Hash($generic_nonmember_action,'generic_nonmember_action',$mailman->main_array["generic_nonmember_action"],null,null,0,'width:150px')."</td><td>" . help_icon('{generic_nonmember_action_text}',true)."</td></tr></table></td>
</tr>

</table>
<div style='text-align:right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm1','" . CurrentPageName() . "',true)\"></div>
</form>");
$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html);		
	
}

function privacy_recipients_filters(){
	$id=$_GET["id"];
	$mailman=new mailman($_GET["id"]);
	
	
$html="<H3>{recipient_filters} &laquo;{$_GET["id"]}&raquo;</h3>
<p>" . privacy_tabs() . "</p>
<p class=caption>{recipient_filters_text}</p>
<form name=ffm1>
<input type='hidden' name='id' value='{$_GET["id"]}'>
<br>".RoundedLightGrey("
<table style='width:100%'>
<tr>
<td align='right'><strong>{require_explicit_destination}:</strong></td>
<td>". Field_numeric_checkbox_img('require_explicit_destination',$mailman->main_array["require_explicit_destination"],'{require_explicit_destination_text}')."</td>
</tr>
<tr>
<td align='right'><strong>{max_num_recipients}:</strong></td>
<td>". Field_text('max_num_recipients',$mailman->main_array["max_num_recipients"],'width:30%',null,null,'{max_num_recipients_text}',false)."</td>
</tr>
</table>
<div style='text-align:right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm1','" . CurrentPageName() . "',true)\"></div>
</form>");


$lis="
<p class=caption>{acceptable_aliases_text}</p>
<input type='hidden' id='acceptable_aliases_input' value='{acceptable_aliases_input}' 
<div style='padding:4px'><center><input type='button' value='{add} {acceptable_aliases}' OnClick=\"javascript:mailman_add_acceptable_aliases('$id');\"></center></div>
<div id='acceptable_aliases_list'>".acceptable_aliases_list()."</div>";
$lis=RoundedLightGrey($lis);

$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html. "<br>" . $lis);		
	
}


function privacy_spam_filters_bounce_matching_headers(){
	$id=$_GET["id"];
	$mailman=new mailman($_GET["id"]);
	
	
$html="<H3>{bounce_matching_headers} &laquo;{$_GET["id"]}&raquo;</h3>
<p>" . privacy_tabs() . "</p>
<br>
" . spam_filters_tabs() . "
<table><tr><td><H5>{bounce_matching_headers}</H5></td><td>" . help_icon('{bounce_matching_headers_text}',true)."</td></tr></table>


<form name=ffm1>
<input type='hidden' name='id' id='id' value='{$_GET["id"]}'>
<br>".RoundedLightGrey("
<table style='width:100%'>
<tr>
<td align='right' valign='top'><strong>{pattern}:</strong></td>
<td><textarea name='bounce_matching_headers' id=bounce_matching_headers></textarea></td>
<td><input type='button' value='{add}&nbsp;&raquo;' OnClick=\"javascript:mailman_add_bounce_matching_headers();\"></td>
</tr>
</table>
</form>");

$html=$html . "<br>";
$html=$html . "<div id='filters_rules'>" . bounce_matching_headers_list() . "</div>";


$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html);	
	
}

function privacy_spam_filters(){
	$id=$_GET["id"];
	$mailman=new mailman($_GET["id"]);
	
	switch ($_GET["tab1"]) {
		case 1:return privacy_spam_filters_bounce_matching_headers();break;
		default:break;
	}	
	
	
	
$html="<H3>{spam_filters} &laquo;{$_GET["id"]}&raquo;</h3>
<p>" . privacy_tabs() . "</p>
<br>
" . spam_filters_tabs() . "
<table><tr><td><H5>{header_filter_rules}</H5></td><td>" . help_icon('{header_filter_rules_text}',true)."</td></tr></table>


<form name=ffm1>
<input type='hidden' name='id' id='id' value='{$_GET["id"]}'>
<br>".RoundedLightGrey("
<table style='width:100%'>
<tr>
<td align='right' valign='top'><strong>{pattern}:</strong></td>
<td><textarea name='header_filter_rules_pattern' id=header_filter_rules_pattern></textarea></td>
<td>". Field_array_Hash($mailman->header_filter_rules_action,"header_filter_rules_action",null,null,null,null,'width:75px')."</td>
<td><input type='button' value='{add}&nbsp;&raquo;' OnClick=\"javascript:mailman_add_header_filter_rules();\"></td>
</tr>
</table>
</form>");

$html=$html . "<br>";
$html=$html . "<div id='filters_rules'>" . header_filter_rules_list() . "</div>";


$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html);		
	
}


function bounce_tabs(){
	if(!isset($_GET["tab"])){$_GET["tab"]=0;};
	$page=CurrentPageName();
	$array[]='{bounce_detection_sensitivity}';
	$array[]='{notifications}';

	
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["tab"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('{$_GET["id"]}_OptionsContent','$page?bounce=yes&id={$_GET["id"]}&tab=$num')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";		
}

function bounce(){
$mailman=new mailman($_GET["id"]);

switch ($_GET["tab"]) {
		case 1:return bounce_notifications();break;
		default:break;
	}	
	


$html="<H3>{bounce_processing_sec} &laquo;{$_GET["id"]}&raquo;</h3>
<p>" . bounce_tabs() . "</p>	
<table><tr><td><H5>{bounce_detection_sensitivity}</H5></td><td>" . help_icon('{bounce_processing_sec_text}',true)."</td></tr></table>
<form name=ffm1>
<input type='hidden' name='id' value='{$_GET["id"]}'>
<p class='caption'>{privacy_options_text}</p>	<br>".RoundedLightGrey("
<table style='width:100%'>
<tr>
<td align='right'><strong>{bounce_processing}:</strong></td>
<td>". Field_numeric_checkbox_img('bounce_processing',$mailman->main_array["bounce_processing"],'{bounce_processing_text}')."</td>
</tr>

<tr>
<td align='right'><strong>{bounce_score_threshold}:</strong></td>
<td>" . Field_text('bounce_score_threshold',$mailman->main_array["bounce_score_threshold"],"width:30%",null,null,'{bounce_score_threshold_text}')."</td>
</tr>

<tr>
<td align='right'><strong>{bounce_info_stale_after}:</strong></td>
<td valign='top'>" . Field_text('bounce_info_stale_after',$mailman->main_array["bounce_info_stale_after"],"width:30%",null,null,'{bounce_info_stale_after_text}')."</td>
</tr>


<tr>
<td align='right'><strong class=caption>{bounce_you_are_disabled_warnings}:</strong></td>
<td valign='top'>" . Field_text('bounce_you_are_disabled_warnings',$mailman->main_array["bounce_you_are_disabled_warnings"],"width:30%",null,null,'{bounce_you_are_disabled_warnings_text}')."</td>
</tr>


<tr>
<td align='right'><strong class=caption>{bounce_you_are_disabled_warnings_interval}:</strong></td>
<td valign='top'>" . Field_text('bounce_you_are_disabled_warnings_interval',$mailman->main_array["bounce_you_are_disabled_warnings_interval"],"width:30%",null,null,'{bounce_you_are_disabled_warnings_interval_text}')."</td>
</tr>

</table><div style='text-align:right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm1','" . CurrentPageName() . "',true)\"></div>");

$tpl=new templates();
echo  $tpl->_ENGINE_parse_body($html);	
	
	
	
}

function bounce_notifications(){
$mailman=new mailman($_GET["id"]);


$html="<H3>{bounce_processing_sec} &laquo;{$_GET["id"]}&raquo;</h3>
<p>" . bounce_tabs() . "</p>	
<table><tr><td><H5>{notifications}</H5></td><td></td></tr></table>
<form name=ffm1>
<input type='hidden' name='id' value='{$_GET["id"]}'>
<p class='caption'>{privacy_options_text}</p>	<br>".RoundedLightGrey("
<table style='width:100%'>
<tr>
<td align='right'><strong>{bounce_unrecognized_goes_to_list_owner}:</strong></td>
<td>". Field_numeric_checkbox_img('bounce_unrecognized_goes_to_list_owner',$mailman->main_array["bounce_unrecognized_goes_to_list_owner"],'{bounce_unrecognized_goes_to_list_owner_text}')."</td>
</tr>
<tr>
<td align='right'><strong>{bounce_notify_owner_on_disable}:</strong></td>
<td>". Field_numeric_checkbox_img('bounce_notify_owner_on_disable',$mailman->main_array["bounce_notify_owner_on_disable"],'{bounce_notify_owner_on_disable_text}')."</td>
</tr>
<tr>
<td align='right'><strong>{bounce_notify_owner_on_removal}:</strong></td>
<td>". Field_numeric_checkbox_img('bounce_notify_owner_on_removal',$mailman->main_array["bounce_notify_owner_on_removal"],'{bounce_notify_owner_on_removal_text}')."</td>
</tr>
</table>
<div style='text-align:right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm1','" . CurrentPageName() . "',true)\"></div></form>");

$tpl=new templates();
echo  $tpl->_ENGINE_parse_body($html);	
	
	
}


function privacy(){
	
		switch ($_GET["tab"]) {
		case 1:return privacy_banlist();break;
		case 2:return privacy_sender_filters();break;
		case 3: return privacy_recipients_filters();break;
		case 4: return privacy_spam_filters();break;
		default:break;
	}
	
$subscribe_array=array(1 =>"{confirm}",2 =>"{require_approval}",3 =>"{confirm_and_approve}");	
$private_roster_array=array(0 =>"{anyone}",1 =>"{list_members}",2 =>"{list_admin_only}");
	
$mailman=new mailman($_GET["id"]);
$html="<H3>{privacy_options} &laquo;{$_GET["id"]}&raquo;</h3>
<p>" . privacy_tabs() . "</p>	
<form name=ffm1>
<input type='hidden' name='id' value='{$_GET["id"]}'>
<p class='caption'>{privacy_options_text}</p>	<br>".RoundedLightGrey("
<table style='width:100%'>
<tr>
<td align='right'><strong>{advertised}:</strong></td>
<td>". Field_numeric_checkbox_img('advertised',$mailman->main_array["advertised"],'{advertised_text}')."</td>
</tr>

<tr>
<td align='right'><strong>{subscribe_policy}:</strong></td>
<td><table><tr><td>". Field_array_Hash($subscribe_array,'subscribe_policy',$mailman->main_array["subscribe_policy"],null,null,0,'width:150px')."</td><td>" . help_icon('{subscribe_policy_text}',true)."</td></tr></table></td>
</tr>

<tr>
<td align='right'><strong>{unsubscribe_policy}:</strong></td>
<td>". Field_numeric_checkbox_img('unsubscribe_policy',$mailman->main_array["unsubscribe_policy"],'{unsubscribe_policy_text}')."</td>
</tr>

<tr>
<td align='right'><strong>{private_roster}:</strong></td>
<td><table><tr><td>". Field_array_Hash($private_roster_array,'private_roster',$mailman->main_array["private_roster"],null,null,0,'width:150px')."</td><td>" . help_icon('{private_roster_text}',true)."</td></tr></table></td>
</tr>

<tr>
<td align='right'><strong>{obscure_addresses}:</strong></td>
<td>". Field_numeric_checkbox_img('obscure_addresses',$mailman->main_array["obscure_addresses"],'{obscure_addresses_text}')."</td>
</tr>

</table><div style='text-align:right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm1','" . CurrentPageName() . "',true)\"></div>");

$tpl=new templates();
echo  $tpl->_ENGINE_parse_body($html);
	
}


function SecGlobal(){
	$id=$_GET["id"];	
$html="
<div style='padding:10px'>
<table style='width:100%'>
<tr>
<td width=30%' valign='top'>" . mailman_leftmenus() . "</td>
<td valign='top' style='padding-left:4px'><div id='{$id}_OptionsContent'>" . GeneralOptions() . "</div>
</td>
</tr>
</table>
</div>";	
	
$tpl=new templates();
echo  $tpl->_ENGINE_parse_body($html);

	
	
}

function welcome(){
	
$mailman=new mailman($_GET["id"]);
$html="<H3>{welcome_message} &laquo;{$_GET["id"]}&raquo;</h3>	
<form name=ffm1>
<input type='hidden' name='id' value='{$_GET["id"]}'>
<p class='caption'>{welcome_msg_text}</p>
<table style='width:100%'>
<tr>
<td><strong>{send_welcome_msg}</strong></td>
<td align='left'>" . Field_numeric_checkbox_img('send_welcome_msg',$mailman->main_array["send_welcome_msg"]) . "</td>
</tr>
</table>
<textarea name='welcome_msg' id='welcome_msg' style='width:100%' rows=5>{$mailman->main_array["welcome_msg"]}</textarea>
</form>
<div style='text-align:right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm1','" . CurrentPageName() . "',true)\"></div>
";
	

$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html);	
	
}

function msg_header(){
$mailman=new mailman($_GET["id"]);
$id=$_GET["id"];
$html="
<form name='ffm1'>
<input type='hidden' name='id' value='$id'>
<H3>{deliver} &laquo;{$_GET["id"]}&raquo;</h3>
<br>" . deliver_tabs() ."
<H5>{nondigests} &laquo;{$_GET["id"]}&raquo;</h5><p>{nondigests_text}</p>
" .digest_non_tabs() ."
<H5>{msg_header} &laquo;{$_GET["id"]}&raquo;</h5>	
<form name=ffm1>
<input type='hidden' name='id' value='{$_GET["id"]}'>
<p class='caption'>{msg_header_text}</p>
<table style='width:100%'>
</table>
<textarea name='msg_header' id='msg_header' style='width:100%' rows=5>{$mailman->main_array["msg_header"]}</textarea>
</form>
<div style='text-align:right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm1','" . CurrentPageName() . "',true)\"></div>
";
$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html);	
}
function msg_footer(){
$$mailman=new mailman($_GET["id"]);
$id=$_GET["id"];
$html="
<form name='ffm1'>
<input type='hidden' name='id' value='$id'>
<H3>{deliver} &laquo;{$_GET["id"]}&raquo;</h3>
<br>" . deliver_tabs() ."
<H5>{nondigests} &laquo;{$_GET["id"]}&raquo;</h5><p>{nondigests_text}</p>
" .digest_non_tabs() ."<H5>{msg_footer} &laquo;{$_GET["id"]}&raquo;</h5>	
<form name=ffm1>
<input type='hidden' name='id' value='{$_GET["id"]}'>
<p class='caption'>{msg_footer_text}</p>
<table style='width:100%'>
</table>
<textarea name='msg_header' id='msg_header' style='width:100%' rows=5>{$mailman->main_array["msg_footer"]}</textarea>
</form>
<div style='text-align:right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm1','" . CurrentPageName() . "',true)\"></div>
";
$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html);	
}



function NewMemberOption(){
	$mailman=new mailman($_GET["id"]);
	$new_member_options=$mailman->main_array["new_member_options"];
	switch ($new_member_options) {
		case 256:
			$conceal=Field_checkbox('a','16');
			$acknowledge=Field_checkbox('b','4');
			$copypost=Field_checkbox('c','2');
			$fopypost=Field_checkbox('d','256',256);
			break;
		case 258:
			$conceal=Field_checkbox('a','16');
			$acknowledge=Field_checkbox('b','4');
			$copypost=Field_checkbox('c','2',2);
			$fopypost=Field_checkbox('d','256',256);
			break;			
		case 278:
			$conceal=Field_checkbox('a','16',16);
			$acknowledge=Field_checkbox('b','4',4);
			$copypost=Field_checkbox('c','2',2);
			$fopypost=Field_checkbox('d','256',256);
			break;
			

		case 260:
			$conceal=Field_checkbox('a','16');
			$acknowledge=Field_checkbox('b','4',4);
			$copypost=Field_checkbox('c','2');
			$fopypost=Field_checkbox('d','256',256);
			break;	


		case 272:
			$conceal=Field_checkbox('a','16',16);
			$acknowledge=Field_checkbox('b','4');
			$copypost=Field_checkbox('c','2');
			$fopypost=Field_checkbox('d','256',256);
			break;					
			
		case 2:
			$conceal=Field_checkbox('a','16');
			$acknowledge=Field_checkbox('b','4');
			$copypost=Field_checkbox('c','2',2);
			$fopypost=Field_checkbox('d','256');
			break;		

		case 4:
			$conceal=Field_checkbox('a','16');
			$acknowledge=Field_checkbox('b','4',4);
			$copypost=Field_checkbox('c','2');
			$fopypost=Field_checkbox('d','256');
			break;	
		case 6:
			$conceal=Field_checkbox('a','16');
			$acknowledge=Field_checkbox('b','4',4);
			$copypost=Field_checkbox('c','2',2);
			$fopypost=Field_checkbox('d','256');
			break;				

		case 18:
			$conceal=Field_checkbox('a','16',16);
			$acknowledge=Field_checkbox('b','4');
			$copypost=Field_checkbox('c','2',2);
			$fopypost=Field_checkbox('d','256');
			break;	
		case 20:
			$conceal=Field_checkbox('a','16',16);
			$acknowledge=Field_checkbox('b','4',4);
			$copypost=Field_checkbox('c','2');
			$fopypost=Field_checkbox('d','256');
			break;				
									
			
	
		default:
			$conceal=Field_checkbox('a','16');
			$acknowledge=Field_checkbox('b','4');
			$copypost=Field_checkbox('c','2');
			$fopypost=Field_checkbox('d','256',256);
			break;
	}
	
$html="<H3>{new_member_options} &laquo;{$_GET["id"]}&raquo;</h3>	
<form name=ffm1>
<input type='hidden' name='id' value='{$_GET["id"]}'>
<input type='hidden' name='new_member_options' value='yes'>
<p class='caption'>{new_member_options_text}</p>
" . RoundedLightGrey("
<table style='width:80%'>	
<tr>
<td width=1%>$conceal</td>
<td align='left'>{conceal}</td>
</tr>
<tr>
<td width=1%>$acknowledge</td>
<td align='left'>{acknowledge}</td>
</tr>
<tr>
<td width=1%>$copypost</td>
<td align='left'>{copypost}</td>
</tr>
<tr>
<td width=1%>$fopypost</td>
<td align='left'>{filter_duplicate_messages}</td>
</tr>
</table>")."
</form>
<div style='text-align:right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm1','" . CurrentPageName() . "',true)\"></div>";


$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html);

	
	
}

function goodby(){
	
$mailman=new mailman($_GET["id"]);
$html="<H3>{send_goodbye_msg} &laquo;{$_GET["id"]}&raquo;</h3>	
<form name=ffm1>
<input type='hidden' name='id' value='{$_GET["id"]}'>
<p class='caption'>{goodbye_msg_text}</p>
<table style='width:100%'>
<tr>
<td><strong>{send_goodbye_msg}</strong></td>
<td align='left'>" . Field_numeric_checkbox_img('send_goodbye_msg',$mailman->main_array["send_goodbye_msg"]) . "</td>
</tr>
</table>
<textarea name='goodbye_msg' id='goodbye_msg' style='width:100%' rows=5>{$mailman->main_array["goodbye_msg"]}</textarea>
</form>
<div style='text-align:right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm1','" . CurrentPageName() . "',true)\"></div>
";
	

$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html);	
	
}

function notifications(){
$id=$_GET["id"];
	$_GET["tab"]=2;
	$mailman=new mailman($_GET["id"]);
	$html="
	
<H3>{general_options} &laquo;{$_GET["id"]}&raquo;</h3>
<br>" . GeneralOptions_tabs() . "</br>
<H3>{notifications} &laquo;{$_GET["id"]}&raquo;</h3>
<form name='ffm1'>
<input type='hidden' name='id' value='{$_GET["id"]}'>
<br>".RoundedLightGrey("
<table style='width:100%'>
<tr>
<td align='right'><strong>{send_reminders}:</strong></td>
<td>". Field_numeric_checkbox_img('send_reminders',$mailman->main_array["send_reminders"],'{send_reminders_text}')."</td>
</tr>
<tr>
<td align='right'><strong>{admin_immed_notify}:</strong></td>
<td>". Field_numeric_checkbox_img('admin_immed_notify',$mailman->main_array["admin_immed_notify"],'{admin_immed_notify_text}')."</td>
</tr>
<tr>
<td align='right'><strong>{admin_notify_mchanges}:</strong></td>
<td>". Field_numeric_checkbox_img('admin_notify_mchanges',$mailman->main_array["admin_notify_mchanges"],'{admin_notify_mchanges_text}')."</td>
</tr>
<tr>
<td align='right'><strong>{respond_to_post_requests}:</strong></td>
<td>". Field_numeric_checkbox_img('respond_to_post_requests',$mailman->main_array["respond_to_post_requests"],'{respond_to_post_requests_text}')."</td>
</tr>

<tr>
<td align='right'>&nbsp;</td>
<td align='right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm1','" . CurrentPageName() . "',true)\"></td>
</tr>



</table></form>");



$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html);


	
}


function digest_tabs(){
	if(!isset($_GET["tab"])){$_GET["tab"]=0;};
	$page=CurrentPageName();
	$array[]='{main}';
	$array[]='{msg_header}';
	$array[]='{msg_footer}';
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["tab"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('{$_GET["id"]}_OptionsContent','$page?digest=yes&id={$_GET["id"]}&tab=$num')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";		
}

function digest_header(){
$mailman=new mailman($_GET["id"]);
$html="
<H3>{deliver} &laquo;{$_GET["id"]}&raquo;</h3>
<br>" . deliver_tabs() ."
<H5>{digest_header} &laquo;{$_GET["id"]}&raquo;</h5><br>" . digest_tabs() . "<br>
<form name=ffm1>
<input type='hidden' name='id' value='{$_GET["id"]}'>
<p class='caption'>{digest_header_text}</p>
<table style='width:100%'>
</table>
<textarea name='digest_header' id='digest_header' style='width:100%' rows=5>{$mailman->main_array["digest_header"]}</textarea>
</form>
<div style='text-align:right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm1','" . CurrentPageName() . "',true)\"></div>
";
$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html);	
}
function digest_footer(){
$mailman=new mailman($_GET["id"]);
$html="<H3>{deliver} &laquo;{$_GET["id"]}&raquo;</h3>
<br>" . deliver_tabs() ."<H5>{digest_footer} &laquo;{$_GET["id"]}&raquo;</h5><br>" . digest_tabs() . "<br>
<form name=ffm1>
<input type='hidden' name='id' value='{$_GET["id"]}'>
<p class='caption'>{digest_footer_text}</p>
<table style='width:100%'>
</table>
<textarea name='digest_footer' id='digest_footer' style='width:100%' rows=5>{$mailman->main_array["digest_footer"]}</textarea>
</form>
<div style='text-align:right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm1','" . CurrentPageName() . "',true)\"></div>
";
$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html);	
	
}

function digest_non_tabs(){
	if(!isset($_GET["tab"])){$_GET["tab"]=0;};
	$page=CurrentPageName();
	$array[]='{msg_header}';
	$array[]='{msg_footer}';
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["tab"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('{$_GET["id"]}_OptionsContent','$page?deliver=yes&id={$_GET["id"]}&main_tab=2&tab=$num')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";		
}



//

function digest_non(){
	$_GET["main_tab"]=2;
	switch ($_GET["tab"]) {
		case 0:return msg_header();break;
		case 1:return msg_footer();break;
	
		default:return msg_header();break;
	}
	

$mailman=new mailman($_GET["id"]);
$id=$_GET["id"];
$html="
<form name='ffm1'>
<input type='hidden' name='id' value='$id'>
<H3>{deliver} &laquo;{$_GET["id"]}&raquo;</h3>
<br>" . deliver_tabs() ."
<H5>{nondigests} &laquo;{$_GET["id"]}&raquo;</h5><p>{nondigests_text}</p>
" .digest_non_tabs();
	
$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html);
	
}



function digest(){
	$_GET["main_tab"]=1;
	switch ($_GET["tab"]) {
		case 1:return digest_header();break;
		case 2:return digest_footer();break;
	
		default:
			break;
	}
	

$mailman=new mailman($_GET["id"]);

$mime_is_default_digest_arr=array(0 =>"Plain",1 => "MIME");
$digest_volume_frequency=array(0 =>"Yearly",1 =>"Monthly",2 =>"Quarterly",3 =>"Weekly",4 =>"Daily");

$id=$_GET["id"];
$html="
<form name='ffm1'>
<input type='hidden' name='id' value='$id'>
<H3>{deliver} &laquo;{$_GET["id"]}&raquo;</h3>
<br>" . deliver_tabs() ."
<H5>{digest} &laquo;{$_GET["id"]}&raquo;</h5><p>{digestsoptions_text}</p>
" .digest_tabs()."
<br>".RoundedLightGrey("
<table style='width:100%'>
<td align='right'><strong>{mime_is_default_digest}:</strong></td>
<td><table><tr><td>". Field_array_Hash($mime_is_default_digest_arr,'mime_is_default_digest',$mailman->main_array["mime_is_default_digest"],null,null,0,'width:150px')."</td><td>" . help_icon('{mime_is_default_digest_text}',true)."</td></tr></table></td>
</tr>

<tr>
<td align='right'><strong>{digest_size_threshhold}:</strong></td>
<td>". Field_text('digest_size_threshhold',$mailman->main_array["digest_size_threshhold"],"width:20%",null,null,'{digest_size_threshhold_text}')."</td>
</tr>

<tr>
<td align='right'><strong>{digest_send_periodic}:</strong></td>
<td>". Field_numeric_checkbox_img('digest_send_periodic',$mailman->main_array["digest_send_periodic"],'{digest_send_periodic_text}')."</td>
</tr>

<tr>
<td align='right'><strong>{digest_volume_frequency}:</strong></td>
<td><table><tr><td>". Field_array_Hash($digest_volume_frequency,'digest_volume_frequency',$mailman->main_array["digest_volume_frequency"],null,null,0,'width:150px')."</td><td>" . help_icon('{digest_volume_frequency_text}',true)."</td></tr></table></td>
</tr>

<tr>
<td align='right'><strong>{d_new_volume}:</strong></td>
<td>". Field_numeric_checkbox_img('_new_volume',$mailman->main_array["_new_volume"],'{d_new_volume_text}')."</td>
</tr>

<tr>
<td align='right'><strong>{d_send_digest_now}:</strong></td>
<td>". Field_numeric_checkbox_img('_send_digest_now',$mailman->main_array["_send_digest_now"],'{d_send_digest_now_text}')."</td>
</tr>

<tr>
<td align='right'>&nbsp;</td>
<td align='right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm1','" . CurrentPageName() . "',true)\"></td>
</tr>
</table></form>");

$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html);
}	
	


function limits(){
$mailman=new mailman($_GET["id"]);
$id=$_GET["id"];
$html="
<form name='ffm1'>
<input type='hidden' name='id' value='$id'>
<H3>{limits} &laquo;{$_GET["id"]}&raquo;</h3>
<br>".RoundedLightGrey("
<table style='width:100%'>
<tr>
<td align='right'><strong>{max_message_size}:</strong></td>
<td>". Field_text('max_message_size',$mailman->main_array["max_message_size"],"width:80%",null,null,'{max_message_size_text}')."</td>
</tr>
<tr>
<td align='right'><strong>{max_days_to_hold}:</strong></td>
<td>". Field_text('max_message_size',$mailman->main_array["max_days_to_hold"],"width:80%",null,null,'{max_days_to_hold_text}')."</td>
</tr>

<tr>
<td align='right'>&nbsp;</td>
<td align='right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm1','" . CurrentPageName() . "',true)\"></td>
</tr>
</table></form>");

$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html);
}

function headers(){
$mailman=new mailman($_GET["id"]);
$id=$_GET["id"];
$html="
<form name='ffm1'>
<input type='hidden' name='id' value='$id'>
<H3>{headers} &laquo;{$_GET["id"]}&raquo;</h3>
<br>".RoundedLightGrey("
<table style='width:100%'>
<tr>
<td align='right'><strong>{include_rfc2369_headers}:</strong></td>
<td>". Field_numeric_checkbox_img('include_rfc2369_headers',$mailman->main_array["include_rfc2369_headers"],'{include_rfc2369_headers_text}')."</td>
</tr>
<tr>
<td align='right'><strong>{include_list_post_header}:</strong></td>
<td>". Field_numeric_checkbox_img('include_list_post_header',$mailman->main_array["include_list_post_header"],'{include_list_post_header_text}')."</td>
</tr>

<tr>
<td align='right'>&nbsp;</td>
<td align='right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm1','" . CurrentPageName() . "',true)\"></td>
</tr>
</table></form>");

$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html);
}

function attachments(){
$mailman=new mailman($_GET["id"]);
$id=$_GET["id"];
$html="
<form name='ffm1'>
<input type='hidden' name='id' value='$id'>
<H3>{attachments} &laquo;{$_GET["id"]}&raquo;</h3>
<br>".RoundedLightGrey("
<table style='width:100%'>
<tr>
<td align='right'><strong>{scrub_nondigest}:</strong></td>
<td>". Field_numeric_checkbox_img('scrub_nondigest',$mailman->main_array["scrub_nondigest"],'{scrub_nondigest_text}')."</td>
</tr>
<tr>
<td align='right'><strong>{include_list_post_header}:</strong></td>
<td>". Field_numeric_checkbox_img('include_list_post_header',$mailman->main_array["include_list_post_header"],'{include_list_post_header_text}')."</td>
</tr>

<tr>
<td align='right'>&nbsp;</td>
<td align='right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm1','" . CurrentPageName() . "',true)\"></td>
</tr>
</table></form>");

$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html);	
	
	
}

function nondigests(){
$id=$_GET["id"];	
$mailman=new mailman($id);


$html="
<form name='ffm1'>
<input type='hidden' name='id' value='$id'>
<H3>{deliver} &laquo;{$_GET["id"]}&raquo;</h3>
<br>".RoundedLightGrey("
<table style='width:100%'>
<tr>
<td align='right'><strong>{nondigestable}:</strong></td>
<td>" . Field_numeric_checkbox_img('nondigestable',$mailman->main_array["nondigestable"],'{nondigestable_text}')."</td>
</tr>
</form>
<tr>
<td align='right'>&nbsp;</td>
<td align='right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm1','" . CurrentPageName() . "',true);\"></td>
</tr>
</table>");

$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html);	
	
	
}

function language(){
$id=$_GET["id"];	
$mailman=new mailman($id);
$array_pref=array(0=>'{never}',1=>'{always}',2=>'{as_needed}');	

$html="
<form name='ffm1'>
<input type='hidden' name='id' value='$id'>
<H3>{language_options} &laquo;{$_GET["id"]}&raquo;</h3>
<br>".RoundedLightGrey("
<table style='width:100%'>
<tr>
<td align='right'><strong>{preferred_language}:</strong></td>
<td><table><tr><td>". Field_array_Hash($mailman->array_lang,'preferred_language',$mailman->main_array["preferred_language"])."</td><td>" . help_icon('{preferred_language_text}',true)."</td></tr></table></td>
</tr>

<tr>
<td align='right'><strong>{encode_ascii_prefixes}:</strong></td>
<td><table><tr><td>". Field_array_Hash($array_pref,'encode_ascii_prefixes',$mailman->main_array["encode_ascii_prefixes"])."</td><td>" . help_icon('{encode_ascii_prefixes_text}',true)."</td></tr></table></td>
</tr>



</form>
<tr>
<td align='right'>&nbsp;</td>
<td align='right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm1','" . CurrentPageName() . "',true);\"></td>
</tr>
</table>")."<br>".RoundedLightGrey("
<table style='width:100%'>
<tr>
<td align='right' nowrap valign='top'><strong>{available_languages}:</strong></td>
<td>
	<form name='ffm2'>
	<input type='hidden' name='id' value='$id'>
		<table>
			<tr>
				<td>". Field_array_Hash($mailman->array_lang,'add_language',null)."</td>
				<td><input type='button' value='{add}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm2','" . CurrentPageName() . "',false);LoadAjax('{$id}_OptionsContent_available_languages','". CurrentPageName()."?available_languages=yes&id={$id}');\"></td>
			</tr>
		</table>
	</form>

<p class=caption>{available_languages_text}</p><div id='{$id}_OptionsContent_available_languages'>"  . available_languages() . "</p></td>
</tr>

<tr>
<td align='right'><strong>{host_name}:</strong></td>
<td>". Field_text('host_name',$mailman->main_array["host_name"],'width:80%',null,null,'{host_name_text}',false)."</td>
</tr>
</table>");

$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html);
	
}


function deliver_tabs(){
	if(!isset($_GET["tab"])){$_GET["tab"]=0;};
	$page=CurrentPageName();
	$array[]='{main}';
	$array[]='{digest}';
	$array[]='{nondigests}';
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["main_tab"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('{$_GET["id"]}_OptionsContent','$page?deliver=yes&id={$_GET["id"]}&main_tab=$num')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";		
}



function deliver(){
$id=$_GET["id"];	
$mailman=new mailman($id);

switch ($_GET["main_tab"]) {
	case 1:return digest();break;
	case 2:return digest_non();break;
	default:
		break;
}


$html="
<form name='ffm1'>
<input type='hidden' name='id' value='$id'>
<H3>{deliver} &laquo;{$_GET["id"]}&raquo;</h3>
<br>" . deliver_tabs() ."

<P>{nondigestable_text}</p>
<br>".RoundedLightGrey("
<table style='width:100%'>
<tr>
<td align='right'><strong>{nondigestable}:</strong></td>
<td>" . Field_numeric_checkbox_img('nondigestable',$mailman->main_array["nondigestable"],'{nondigestable_text}')."</td>
</tr>
<tr>
<td align='right'><strong>{digest_is_default}:</strong></td>
<td>". Field_numeric_checkbox_img('digest_is_default',$mailman->main_array["digest_is_default"],'{digest_is_default_text}')."</td>
</tr>
</form>
<tr>
<td align='right'>&nbsp;</td>
<td align='right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm1','" . CurrentPageName() . "',true);\"></td>
</tr>
</table>");

$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html);
	
}

function language_add(){
	$mailman=new mailman($_GET["id"]);
	$mailman->available_languages[]=$_GET["add_language"];
	$mailman->Save();
	
}

function welcome_save(){
	$mailman=new mailman($_GET["id"]);
	$mailman->main_array["welcome_msg"]=$_GET["welcome_msg"];
	$mailman->main_array["send_welcome_msg"]=$_GET["send_welcome_msg"];
	$mailman->main_array["send_welcome_msg"]=str_replace("'","`",$mailman->main_array["send_welcome_msg"]);
	$mailman->Save();	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');
	
}

function NewMemberOption_save(){
	$mailman=new mailman($_GET["id"]);
	$a=0;
	$b=0;
	$c=0;
	$d=0;
	if(isset($_GET["a"])){$a=$_GET["a"];}
	if(isset($_GET["b"])){$b=$_GET["b"];}
	if(isset($_GET["c"])){$c=$_GET["c"];}
	if(isset($_GET["d"])){$d=$_GET["d"];}
	
	$tot=$a+$b+$c+$d;
	$mailman->main_array["new_member_options"]=$tot;
	$mailman->Save();
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("{success}\n"."($tot)");	
	
}

function goodbye_save(){
	$mailman=new mailman($_GET["id"]);
	$mailman->main_array["goodbye_msg"]=$_GET["goodbye_msg"];
	$mailman->main_array["send_goodbye_msg"]=$_GET["send_goodbye_msg"];
	$mailman->main_array["goodbye_msg"]=str_replace("'","`",$mailman->main_array["goodbye_msg"]);
	$mailman->Save();	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');	
}
function msg_header_save(){
	$mailman=new mailman($_GET["id"]);
	$mailman->main_array["msg_header"]=$_GET["msg_header"];
	$mailman->main_array["msg_header"]=$_GET["msg_header"];
	$mailman->main_array["msg_header"]=str_replace("'","`",$mailman->main_array["msg_header"]);
	$mailman->Save();	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');		
	
}

function digest_header_save(){
	$mailman=new mailman($_GET["id"]);
	$mailman->main_array["digest_header"]=$_GET["digest_header"];
	$mailman->main_array["digest_header"]=$_GET["digest_header"];
	$mailman->main_array["digest_header"]=str_replace("'","`",$mailman->main_array["digest_header"]);
	$mailman->Save();	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');			
	}

function member_moderation_notice_save(){
	$mailman=new mailman($_GET["id"]);
	$mailman->main_array["member_moderation_notice"]=$_GET["member_moderation_notice"];
	$mailman->main_array["member_moderation_notice"]=$_GET["member_moderation_notice"];
	$mailman->main_array["member_moderation_notice"]=str_replace("'","`",$mailman->main_array["member_moderation_notice"]);
	$mailman->Save();	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');
	}
function nonmember_rejection_notice_save(){
	$mailman=new mailman($_GET["id"]);
	$mailman->main_array["nonmember_rejection_notice"]=$_GET["nonmember_rejection_notice"];
	$mailman->main_array["nonmember_rejection_notice"]=$_GET["nonmember_rejection_notice"];
	$mailman->main_array["nonmember_rejection_notice"]=str_replace("'","`",$mailman->main_array["nonmember_rejection_notice"]);
	$mailman->Save();	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');
	}	
	
	

function digest_footer_save(){
	$mailman=new mailman($_GET["id"]);
	$mailman->main_array["digest_footer"]=$_GET["digest_footer"];
	$mailman->main_array["digest_footer"]=$_GET["digest_footer"];
	$mailman->main_array["digest_footer"]=str_replace("'","`",$mailman->main_array["digest_footer"]);
	$mailman->Save();	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');		
	
}

function msg_footer_save(){
	$mailman=new mailman($_GET["id"]);
	$mailman->main_array["msg_footer"]=$_GET["msg_footer"];
	$mailman->main_array["msg_footer"]=$_GET["msg_footer"];
	$mailman->main_array["msg_footer"]=str_replace("'","`",$mailman->main_array["msg_footer"]);
	$mailman->Save();	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');		
	
}


function GeneralOptions_save(){
	$mailman=new mailman($_GET["id"]);
	while (list ($num, $val) = each ($_GET) ){
		$mailman->main_array[$num]=$val;
		
	}
	$mailman->Save();
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');		
}


function GeneralOptions_tabs(){
	//generallistpersonality
	
if(!isset($_GET["tab"])){$_GET["tab"]=0;};
	$page=CurrentPageName();
	$array[]='{generallistpersonality}';
	$array[]='{administrators}';
	$array[]='{moderators}';
	$array[]='{notifications}';
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["tab"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('{$_GET["id"]}_OptionsContent','$page?GeneralOptions=yes&id={$_GET["id"]}&tab=$num')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";		
	
}


function GeneralOptions(){

	switch ($_GET["tab"]) {
		case 1:return moderators();break;
		case 2:return moderators2();break;
		case 3:return notifications();break;
		default:break;
	}
	
	$mailman=new mailman($_GET["id"]);	
	$ldap=new clladp();
	$ous=$ldap->hash_get_ou(true);
	$ous[null]="{select}";
	$ous["undefined"]="{select}";
	$affected_ou=Field_array_Hash($ous,'affectedou',$mailman->mailmanouowner,null,null,0,"width:150px");
	
	$form="<H5>{affectedou} - $mailman->mailmanouowner</h5>
			<table style='width:100%'>
				<tr>
					<td align='right'><strong>{organization}:</strong></td>
					<td>$affected_ou</td>
				</tr>	
				<tr>
				<td align='right'>&nbsp;</td>
				<td align='right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:affect_org();\"></td>
				</tr>
				</table>";
	
$form=RoundedLightGreen($form);
	$user=new usersMenus();
	if ($user->AsPostfixAdministrator==false){$form=null;}
	
$hash_reply_goes_to_list=array(0=>"{poster}",1=>"{thislist}",2=>"{explicit_address}");
if($mailman->main_array["subject_prefix"]==null){$mailman->main_array["subject_prefix"]="[$id]";}

$id=$_GET["id"];
$html="
<form name='ffm1'>
<input type='hidden' name='id' id='id' value='$id'>
<H3>{general_options} &laquo;{$_GET["id"]}&raquo;</h3>
<br>" . GeneralOptions_tabs() . "</br>
$form
<H5>{generallistpersonality}</H5>
<br>".RoundedLightGrey("
<table style='width:100%'>
<tr>
<td align='right'><strong>{real_name}:</strong></td>
<td>". Field_text('real_name',$mailman->main_array["real_name"],'width:80%',null,null,'{real_name_text}',false)."</td>
</tr>
<tr>
<td align='right'><strong>{description}:</strong></td>
<td>". Field_text('description',$mailman->main_array["description"],'width:80%',null,null,'{description_text}',false)."</td>
</tr>
<tr>
<td align='right'><strong>{host_name}:</strong></td>
<td>". Field_text('host_name',$mailman->main_array["host_name"],'width:80%',null,null,'{host_name_text}',false)."</td>
</tr>


<tr>
<td align='right'><strong>{info}:</strong></td>
<td>". Field_text('info',$mailman->main_array["info"],'width:80%',null,null,'{info_text}',false)."</td>
</tr>
<tr>
<td align='right'><strong>{subject_prefix}:</strong></td>
<td>". Field_text('subject_prefix',$mailman->main_array["subject_prefix"],'width:80%',null,null,'{subject_prefix_text}',false)."</td>
</tr>
<tr>
<td align='right'><strong>{anonymous_list}:</strong></td>
<td>". Field_numeric_checkbox_img('anonymous_list',$mailman->main_array["anonymous_list"],'{anonymous_list_text}')."</td>
</tr>
<tr>
<td align='right'><strong>{first_strip_reply_to}:</strong></td>
<td>". Field_numeric_checkbox_img('first_strip_reply_to',$mailman->main_array["first_strip_reply_to"],'{first_strip_reply_to_text}')."</td>
</tr>
<tr>
<td align='right'><strong>{reply_goes_to_list}:</strong></td>
<td><table><tr><td>". Field_array_Hash($hash_reply_goes_to_list,$mailman->main_array["reply_goes_to_list"])."</td><td>" . help_icon('{reply_goes_to_list_text}',true)."</td></tr></table></td>
</tr>
<tr>
<td align='right'><strong>{reply_to_address}:</strong></td>
<td>". Field_text('reply_to_address',$mailman->main_array["reply_to_address"],'width:80%',null,null,'{reply_to_address_text}',false)."</td>
</tr>

<tr>
<td align='right'><strong>{emergency}:</strong></td>
<td>". Field_numeric_checkbox_img('emergency',$mailman->main_array["emergency"],'{emergency_text}')."</td>
</tr>
<tr>
<td align='right'><strong>{administrivia}:</strong></td>
<td>". Field_numeric_checkbox_img('administrivia',$mailman->main_array["administrivia"],'{administrivia_text}')."</td>
</tr>


<tr>
<td align='right'>&nbsp;</td>
<td align='right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm1','" . CurrentPageName() . "',true)\"></td>
</tr>



</table></form>");



$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html);

}

function moderators(){
	$id=$_GET["id"];
	$_GET["tab"]=1;
	$mailman=new mailman($_GET["id"]);
	$html="
	
<H3>{general_options} &laquo;{$_GET["id"]}&raquo;</h3>
<br>" . GeneralOptions_tabs() . "</br>
	<H5>{administrators} &laquo;{$_GET["id"]}&raquo;</h5>
	<p class=caption>{administrators_intro}</p>
	<input type='hidden' id='add_moderator_input' value='{add_moderator_input}'>
	<div style='padding:4px'><center><input type='button' value='{add_moderator}' OnClick=\"javascript:mailman_add_moderator('$id');\"></center></div>
	";
	
	$table="<table style='width:100%'>";
	if(is_array($mailman->owner)){
	while (list ($num, $val) = each ($mailman->owner) ){
	$table=$table. "<tr>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong>$val</strong></td>
		<td>" . imgtootltip('x.gif','{delete}',"mailman_delete_moderator('$id','$num')")."</td>
	</tr>";
	}}

	$table=$table . "</table>";
	$table=RoundedLightGrey($table);
	$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html.$table);
}

function bounce_matching_headers_list(){
$id=$_GET["id"];
	$mailman=new mailman($_GET["id"]);
	
	$table="<table style='width:100%'>";
	if(is_array($mailman->bounce_matching_headers)){
	while (list ($num, $val) = each ($mailman->bounce_matching_headers) ){
		if(trim($val)<>null){
	$table=$table. "<tr>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong>$val</strong></td>
		<td>" . imgtootltip('x.gif','{delete}',"mailman_delete_bounce_matching_headers('$id','$num')")."</td>
	</tr>";
	}}}

	$table=$table . "</table>";
	$table=RoundedLightGrey($table);
	$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html.$table);	
	
}

function available_languages(){
	$id=$_GET["id"];
	$mailman=new mailman($_GET["id"]);
	
	$table="<table style='width:100%'>";
	if(is_array($mailman->available_languages)){
	while (list ($num, $val) = each ($mailman->available_languages) ){
	$table=$table. "<tr>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong>{$mailman->array_lang[$val]}</strong></td>
		<td>" . imgtootltip('x.gif','{delete}',"mailman_delete_available_languages('$id','$num')")."</td>
	</tr>";
	}}

	$table=$table . "</table>";
	$table=RoundedLightGreen($table);
	$tpl=new templates();	
	
	return $tpl->_parse_body($table);
}

function moderators2(){
	$id=$_GET["id"];
	$_GET["tab"]=2;
	$mailman=new mailman($_GET["id"]);
	$html="
	
<H3>{general_options} &laquo;{$_GET["id"]}&raquo;</h3>
<br>" . GeneralOptions_tabs() . "</br>
<H3>{moderators} &laquo;{$_GET["id"]}&raquo;</h3>
	<p class=caption>{moderators_intro}</p>
	<input type='hidden' id='add_moderator_input' value='{add_moderator_input}'>
	<div style='padding:4px'><center><input type='button' value='{add_moderator}' OnClick=\"javascript:mailman_add_moderator2('$id');\"></center></div>
	";
	
	$table="<table style='width:100%'>";
	if(is_array($mailman->moderator)){
	while (list ($num, $val) = each ($mailman->moderator) ){
		$table=$table. "<tr>
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td><strong>$val</strong></td>
			<td>" . imgtootltip('x.gif','{delete}',"mailman_delete_moderator2('$id','$num')")."</td>
		</tr>";
	}}

	$table=$table . "</table>";
	$table=RoundedLightGrey($table);
	$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html.$table);
}


function header_filter_rules_list(){
	$id=$_GET["id"];
	$mailman=new mailman($_GET["id"]);

	$table="<table style='width:100%'>";
	if(is_array($mailman->header_filter_rules)){
		writelogs(count($mailman->header_filter_rules) . " rules number",__FUNCTION__,__FILE__);
	while (list ($num, $val) = each ($mailman->header_filter_rules) ){
		$table=$table. "<tr>
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td><strong>{$val["pattern"]}</strong></td>
			<td><strong>{$mailman->header_filter_rules_action[$val["action"]]}</strong></td>
			<td>" . imgtootltip('x.gif','{delete}',"mailman_delete_header_filter_rules('$id','$num')")."</td>
			<td>" . imgtootltip('arrow_up.gif','{up}',"mailman_move_header_filter_rules('$id','$num','up')")."</td>
			<td>" . imgtootltip('arrow_down.gif','{down}',"mailman_move_header_filter_rules('$id','$num','down')")."</td>
		</tr>";
	}}

	$table=$table . "</table>";
	$table=RoundedLightGrey($table);
	$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html.$table);
}
function header_filter_rules_del(){
	$id=$_GET["id"];
	$num=$_GET["delete_header_filter_rules"];
	$mailman=new mailman($_GET["id"]);	
	writelogs("Delete rule number $num",__FUNCTION__,__FILE__);
	unset($mailman->header_filter_rules[$num]);
	$mailman->save();		
}



function mailman_leftmenus(){
	$id=$_GET["id"];
	$page=CurrentPageName();
	$html="
	<table>
	<tr ". CellRollOver("LoadAjax('{$id}_OptionsContent','$page?GeneralOptions=yes&id=$id')") .">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong>{general_options}</strong></td>
	</tr>
	
	<tr ". CellRollOver("LoadAjax('{$id}_OptionsContent','$page?deliver=yes&id=$id')") .">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong>{deliver}</strong></td>
	</tr>	
	
	
	<tr ". CellRollOver("LoadAjax('{$id}_OptionsContent','$page?privacy=yes&id=$id')") .">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong>{privacy_options}</strong></td>
	</tr>	

	<tr ". CellRollOver("LoadAjax('{$id}_OptionsContent','$page?NewMemberOption=yes&id=$id')") .">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong>{new_member_options}</strong></td>
	</tr>		
	
	
	
	<tr ". CellRollOver("LoadAjax('{$id}_OptionsContent','$page?lang=yes&id=$id')") .">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong>{language_options}</strong></td>
	</tr>	

	<tr ". CellRollOver("LoadAjax('{$id}_OptionsContent','$page?bounce=yes&id=$id')") .">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong>{bounce_processing_sec}</strong></td>
	</tr>	
	
	<tr ". CellRollOver("LoadAjax('{$id}_OptionsContent','$page?archives=yes&id=$id')") .">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong>{archiving_options}</strong></td>
	</tr>		
	
	<tr ". CellRollOver("LoadAjax('{$id}_OptionsContent','$page?newsg=yes&id=$id')") .">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong>{mail_new_gateways}</strong></td>
	</tr>		
	<tr ". CellRollOver("LoadAjax('{$id}_OptionsContent','$page?autoresponder=yes&id=$id')") .">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong>{autoresponder}</strong></td>
	</tr>

	<tr ". CellRollOver("LoadAjax('{$id}_OptionsContent','$page?contentfiltering=yes&id=$id')") .">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong>{contentfiltering}</strong></td>
	</tr>	

	<tr ". CellRollOver("LoadAjax('{$id}_OptionsContent','$page?topics=yes&id=$id')") .">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong>{topics}</strong></td>
	</tr>		
		
	
	
	
	
	<tr ". CellRollOver("LoadAjax('{$id}_OptionsContent','$page?headers=yes&id=$id')") .">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong>{headers}</strong></td>
	</tr>	

	<tr ". CellRollOver("LoadAjax('{$id}_OptionsContent','$page?welcome=yes&id=$id')") .">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong>{welcome_message}</strong></td>
	</tr>	

	<tr ". CellRollOver("LoadAjax('{$id}_OptionsContent','$page?goodbye=yes&id=$id')") .">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong>{send_goodbye_msg}</strong></td>
	</tr>	
	
	

	
<tr ". CellRollOver("LoadAjax('{$id}_OptionsContent','$page?limits=yes&id=$id')") .">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong>{limits}</strong></td>
	</tr>		

	
	<tr ". CellRollOver("LoadAjax('{$id}_OptionsContent','$page?attachments=yes&id=$id')") .">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong>{attachments}</strong></td>
	</tr>		
	
	
	
	
	</table>
	<br>
	
	
	
	";
	
	$html=RoundedLightGreen($html);
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html . "<br>". $digest);
	
}	

function moderators_add(){
	$id=$_GET["id"];
	$email=$_GET["add_moderator"];
	$mailman=new mailman($id);
	$mailman->owner[]=$email;
	$mailman->save();
	}
function bounce_matching_headers_add(){
	$id=$_GET["id"];
	$_GET["bounce_matching_headers"]=str_replace('#p','+',$_GET["bounce_matching_headers"]);
	writelogs("ADD {$_GET["bounce_matching_headers"]} ",__FUNCTION__,__FILE__);
	$mailman=new mailman($id);
	$mailman->bounce_matching_headers[]=$_GET["bounce_matching_headers"];
	$mailman->Save();
	
}
function bounce_matching_headers_del(){
$id=$_GET["id"];
	$email=$_GET["delete_bounce_matching_headers"];
	$mailman=new mailman($id);
	writelogs("Delete {$mailman->bounce_matching_headers[$email]} ",__FUNCTION__,__FILE__);
	$mailman->bounce_matching_headers[$email]='';
	$mailman->save();		
}
	
function accept_these_nonmembers_add(){
	$id=$_GET["id"];
	$email=$_GET["add_accept_these_nonmembers"];
	$mailman=new mailman($id);
	$mailman->accept_these_nonmembers[]=$email;
	$mailman->save();	
	}
	
function acceptable_aliases_add(){
	$id=$_GET["id"];
	$email=$_GET["add_acceptable_aliases"];
	$mailman=new mailman($id);
	$mailman->acceptable_aliases[]=$email;
	writelogs("ADD  '$email' new array as " .count($mailman->acceptable_aliases). " rows",__FUNCTION__,__FILE__);	
	$mailman->save();		
	}
function discard_these_nonmembers_add(){
	$id=$_GET["id"];
	$email=$_GET["add_discard_these_nonmembers"];
	$mailman=new mailman($id);
	$mailman->discard_these_nonmembers[]=$email;
	$mailman->save();	
	
}

function filter_filename_extensions_add(){
	$id=$_GET["id"];
	$data=$_GET["filter_filename_extensions_add"];
	$mailman=new mailman($id);
	writelogs("ADD  $data new array as " .count($mailman->filter_filename_extensions). " rows",__FUNCTION__,__FILE__);	
	
	if($_GET["tab"]==1){
		$mailman->pass_filename_extensions[]=$data;
	}else{
	$mailman->filter_filename_extensions[]=$data;
	}
	$mailman->save();	
}

function filter_mime_types_add(){
	$id=$_GET["id"];
	$data=$_GET["filter_mime_types_add"];
	$mailman=new mailman($id);
	
	if($_GET["tab"]==1){
		$mailman->pass_mime_types[]=$data;
	}else{
	$mailman->filter_mime_types[]=$data;
	}
	$mailman->save();	
}

function filter_mime_types_del(){
	$id=$_GET["id"];
	$data=$_GET["delete_filter_mime_types"];
	$mailman=new mailman($id);
	if($_GET["tab"]==1){
		unset($mailman->pass_mime_types[$data]);
	}else{
	unset($mailman->filter_mime_types[$data]);
	}
	$mailman->save();		
	}
function filter_filename_extensions_del(){
	$id=$_GET["id"];
	$data=$_GET["delete_filter_filename_extensions"];
	$mailman=new mailman($id);
	
	if($_GET["tab"]==1){
		unset($mailman->pass_filename_extensions[$data]);
	}else{
	unset($mailman->filter_filename_extensions[$data]);
	}
	$mailman->save();	
}	


function hold_these_nonmembers_add(){
	//hold_these_nonmembers
	$id=$_GET["id"];
	$email=$_GET["add_hold_these_nonmembers"];
	$mailman=new mailman($id);
	$mailman->hold_these_nonmembers[]=$email;
	writelogs("ADD  '$email' new array as " .count($mailman->hold_these_nonmembers). " rows",__FUNCTION__,__FILE__);
	$mailman->save();	
	}
function reject_these_nonmembers_add(){
	//hold_these_nonmembers
	$id=$_GET["id"];
	$email=$_GET["add_reject_these_nonmembers"];
	$mailman=new mailman($id);
	$mailman->reject_these_nonmembers[]=$email;
	writelogs("ADD <reject_these_nonmembers> '$email' new array as " .count($mailman->reject_these_nonmembers). " rows",__FUNCTION__,__FILE__);
	$mailman->save();	
	}	
	
	
function acceptable_aliases_del(){
	$id=$_GET["id"];
	$email=$_GET["delete_acceptable_aliases"];
	$mailman=new mailman($id);
	writelogs("Delete {$mailman->acceptable_aliases[$email]} ",__FUNCTION__,__FILE__);
	$mailman->acceptable_aliases[$email]='';
	$mailman->save();	
}	
function hold_these_nonmembers_del(){
	$id=$_GET["id"];
	$email=$_GET["delete_hold_these_nonmembers"];
	$mailman=new mailman($id);
	writelogs("Delete {$mailman->hold_these_nonmembers[$email]} ",__FUNCTION__,__FILE__);
	$mailman->hold_these_nonmembers[$email]='';
	$mailman->save();	
}
function reject_these_nonmembers_del(){
	$id=$_GET["id"];
	$email=$_GET["delete_reject_these_nonmembers"];
	$mailman=new mailman($id);
	writelogs("Delete {$mailman->reject_these_nonmembers[$email]} ",__FUNCTION__,__FILE__);
	$mailman->reject_these_nonmembers[$email]='';
	$mailman->save();	
}		
	
function banlist_add(){
	$id=$_GET["id"];
	$email=$_GET["add_banlist"];
	$mailman=new mailman($id);
	$mailman->ban_list[]=$email;
	$mailman->save();	
}
function moderators_add2(){
	$id=$_GET["id"];
	$email=$_GET["add_moderator2"];
	$mailman=new mailman($id);
	$mailman->moderator[]=$email;
	$mailman->save();
	}	
function moderators_del(){
	$id=$_GET["id"];
	$email=$_GET["delete_moderator"];
	$mailman=new mailman($id);
	writelogs("Delete {$mailman->owner[$email]} moderator");
	$mailman->owner[$email]='';
	$mailman->save();	
	}
function banlist_del(){
	$id=$_GET["id"];
	$email=$_GET["delete_banlist"];
	$mailman=new mailman($id);
	writelogs("Delete {$mailman->ban_list[$email]} ");
	$mailman->ban_list[$email]='';
	$mailman->save();	
	}	
function accept_these_nonmembers_del(){
	$id=$_GET["id"];
	$email=$_GET["delete_accept_these_nonmembers"];
	$mailman=new mailman($id);
	writelogs("Delete {$mailman->accept_these_nonmembers[$email]} ");
	$mailman->accept_these_nonmembers[$email]='';
	$mailman->save();	
}
function discard_these_nonmembers_del(){
	$id=$_GET["id"];
	$email=$_GET["delete_discard_these_nonmembers"];
	$mailman=new mailman($id);
	writelogs("Delete {$mailman->discard_these_nonmembers[$email]} ");
	$mailman->discard_these_nonmembers[$email]='';
	$mailman->save();	
}


//
function moderators_del2(){
	$id=$_GET["id"];
	$email=$_GET["delete_moderator2"];
	$mailman=new mailman($id);
	writelogs("Delete {$mailman->moderator[$email]} moderator");
	$mailman->moderator[$email]='';
	$mailman->save();	
	}	
function language_del(){
	$id=$_GET["id"];
	$email=$_GET["delete_available_languages"];
	$mailman=new mailman($id);
	writelogs("Delete {$mailman->available_languages[$email]} available_languages");
	$mailman->available_languages[$email]='';
	$mailman->save();	
	}
	
function header_filter_rules_add(){
	$id=$_POST["id"];
	$action=$_POST["header_filter_rules_action"];
	$pattern=$_POST["header_filter_rules_pattern"];
	$pattern=str_replace('#p','+',$pattern);
	writelogs("ADD new action '$action' $pattern",__FUNCTION__,__FILE__);
	
	$mailman=new mailman($id);
	$mailman->header_filter_rules[]=array("action"=>$action,"pattern"=>$pattern);
	$mailman->Save();
	}
	
function header_filter_rules_move(){
	$id=$_POST["id"];	
	$mailman=new mailman($id);
	$mailman->header_filter_rules=array_move_element($mailman->header_filter_rules,$mailman->header_filter_rules[$_POST["header_filter_rules_pattern_move"]],$_POST["move"]);
	$mailman->Save();
	
}

function loadApplySettings(){
	$tpl=new templates();
echo $tpl->_ENGINE_parse_body("
	<div style='padding:15px;background-image:url(img/bg_mailman_transp.jpg);background-repeat:no-repeat;background-position:right bottom;'>
	<H4>{$_GET["id"]}</H4>
	<H6>{apply_settings}</H6>
	<br>
	<div id='results' style='border:1px solid #CCCCCC;padding:5px'>
		<center>
			<img src='img/wait.gif'>
		</center>
		</div>
	</div>");	
	
}


function action_applysettings(){
	
	$list=$_GET["action_applysettings"];
	writelogs("apply config file for $list",__FUNCTION__,__FILE__);
	$mailman=new mailman($list);
	$mailman->Save();
	$sock=new sockets();
	$datas=$sock->getfile('MAILMAN_SINGLE:' .$list );
	$datas=htmlentities($datas);
	$datas=str_replace("\n\n","\n",$datas);
	$datas=nl2br($datas);
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("<br><div style='padding-left:10px'><code>$datas</code></div>");
	
}

function mailman_addresses(){
	
	$id=$_GET["id"];
	$mailman=new mailman($_GET["id"]);
		$button="<input type='button' value='{create}&nbsp;&raquo;' OnClick=\"javascript:mailman_create_addresses();\">";
		$field="<strong>{$mailman->main_array["host_name"]}</strong><input type=hidden name='mailman_domain' id='mailman_domain' value='{$mailman->main_array["host_name"]}'>";
	
	$html="
	<input type='hidden' id='id' value='{$_GET["id"]}'>
	<div style='padding:15px;background-image:url(img/bg_mailman_transp.jpg);background-repeat:no-repeat;background-position:right bottom;'>
	<H4>$id {mailman_robots_address}</H4>
	<table style='width:100%'>
	<tr>
	<td align='right' nowrap><strong>{create_address_from}:</td>
	<td>$field</td>
	<td>$button</td>
	</tr>
	</table>
	<div id='{$id}_robots'>" . mailman_addresses_list() . "</div>
	</div>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function mailman_addresses_list(){
	$ldap=new clladp();
	$id=$_GET["id"];
	$search="(&(objectclass=ArticaMailManRobots)(cn=*))";
	$ldap_prefix="cn=$id,cn=mailman,cn=artica,$ldap->suffix";
	$attrs=array();
	$ldap=new clladp();
	$sr =@ldap_search($ldap->ldap_connection,$ldap_prefix,$search,$attrs);	
	if($sr){
		$html="<table style='width:100%'>
		<tr>
		<td></td>
		</tr>";
		$result = ldap_get_entries($ldap->ldap_connection, $sr);
			for($i=0;$i<$result["count"];$i++){
				$email=$result[$i]["cn"][0];
				$mailmanrobottype=$result[$i]["mailmanrobottype"][0];
				$html=$html . "<tr " . CellRollOver() . ">
				<td width=1%><img src='img/mailbox_storage.gif'></td>
				<td style='padding:5px'>
				<input type='hidden' value='$id' id='{$email}_id'>
				<input type='hidden' value='$mailmanrobottype' id='{$email}_mailmanrobottype'>
				
				<strong style='font-size:13px'>" . texttooltip($email,'{mailman_modify_robot}',"mailman_modify_robot('$email')")."</strong></td>
				
				<td><strong style='font-size:13px'>$mailmanrobottype</strong></td>
				<td><strong style='font-size:13px'>" . imgtootltip('x.gif','{delete}',"mailman_delete_robot('$id','$email')")."</td>
				</tr>";
				
			}
			
		$html=$html . "</table>";
		}
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html . "<br>");
}


function mailman_create_robots_from_domain(){
	$domain=$_GET["mailman_create_robots_from_domain"];
	
	
	
	
	
	
	$id=$_GET["id"];
	$mailman=new mailman($id);
	$mailman->main_array["host_name"]=$domain;
	$mailman->Save();
	$sock=new sockets();
	$datas=$sock->getfile('MAILMAN_SINGLE:' .$id );
	
	
	$ldap=new clladp();
	$ldap_prefix="cn=$id,cn=mailman,cn=artica,$ldap->suffix";
	
//delete old emails	
	$search="(&(objectclass=ArticaMailManRobots)(cn=*))";
	$attrs=array("cn");
	$ldap=new clladp();
	$sr =@ldap_search($ldap->ldap_connection,$ldap_prefix,$search,$attrs);		
	if($sr){
		$result = ldap_get_entries($ldap->ldap_connection, $sr);
			for($i=0;$i<$result["count"];$i++){
				$email=$result[$i]["cn"][0];
				$newdn="cn=$email,cn=$id,cn=mailman,cn=artica,$ldap->suffix";
				$ldap->ldap_delete($newdn,true);
			}
	}
	
	
	
	
	$array_prefix=array("admin","bounces","confirm","join","leave","owner","request","subscribe","unsubscribe");
	
	if(!$ldap->ExistsDN("cn=$id@$domain,$ldap_prefix")){
		$upd["objectclass"][]='top';
		$upd["objectclass"][]='ArticaMailManRobots';
		$upd["cn"][]="$id@$domain";
		$upd["MailManDistriList"][]=$id;
		$upd["MailManRobotType"][]="main";
		$ldap->ldap_add("cn=$id@$domain,$ldap_prefix",$upd);
		}
		unset($upd);
	
	while (list ($num, $robot) = each ($array_prefix) ){
		$dn="cn=$id-$robot@$domain,$ldap_prefix";
		
		if(!$ldap->ExistsDN($dn)){
			$upd["objectclass"][]='top';
			$upd["objectclass"][]='ArticaMailManRobots';
			$upd["cn"][]="$id-$robot@$domain";
			$upd["MailManDistriList"][]=$id;
			$upd["MailManRobotType"][]="$robot";
			$ldap->ldap_add($dn,$upd);
			unset($upd);
			
		}
		
	}
}

function mailman_delete_robot(){
	$id=$_GET["id"];
	$ldap=new clladp();	
	$dn1="cn={$_GET["mailman_delete_robot"]},cn=$id,cn=mailman,cn=artica,$ldap->suffix";
	$ldap->ldap_delete($dn1,true);
}

function mailman_change_email_robot(){
	$id=$_GET["id"];
	$ldap=new clladp();
	$email_new=$_GET["email_new"];
	$robot_type=$_GET["robot_type"];
	$dn1="cn={$_GET["mailman_change_email_robot"]},cn=$id,cn=mailman,cn=artica,$ldap->suffix";
	$ldap->ldap_delete($dn1,true);
		$upd["objectclass"][]='top';
		$upd["objectclass"][]='ArticaMailManRobots';
		$upd["cn"][]="$email_new";
		$upd["MailManDistriList"][]=$id;
		$upd["MailManRobotType"][]=$robot_type;
		$ldap->ldap_add("cn=$email_new,cn=$id,cn=mailman,cn=artica,$ldap->suffix",$upd);	
	
	
}
function mailman_add_new_distribution_list(){
	$id=$_GET["id"];
	$ldap=new clladp();	
	$cn="cn=$id,cn=mailman,cn=artica,$ldap->suffix";
	
	$upd["objectclass"][]='top';
	$upd["objectclass"][]='ArticaMailManClass';
	$upd["cn"][]=$id;
	$upd["MailmanListOperation"][]="ADD";
	$upd["MailManListAdministrator"][]=$_GET["MailManListAdministrator"];
	$upd["MailManListAdminPassword"][]=$_GET["MailManListAdminPassword"];
	
	if($_GET["ou"]<>null){
		$upd["MailManOuOwner"][]=$_GET["ou"];
	}else{
		$upd["MailManOuOwner"][]="undefined";	
	}
	$ldap->ldap_add($cn,$upd);	
	
	
}
function mailman_delete_distribution_list(){
	$id=$_GET["id"];
	$ldap=new clladp();	
	$cn="cn=$id,cn=mailman,cn=artica,$ldap->suffix";
	$upd["MailmanListOperation"][]="DEL";
	$ldap->Ldap_modify($cn,$upd);
	
}


function Defaultspy_save(){
	unset($_GET["GlobalAdministrationParameters"]);
	unset($_GET["id"]);
	$mailman=new mailman(null);
	while (list ($num, $val) = each ($_GET) ){
		$mailman->main_default_array[$num]=$val;
	}
	$mailman->save_PyDefaults();
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');
	
		
	
}

function Defaultspy(){
	$mailman=new mailman(null);
$html="<div style='padding:20px'>
		<form name='ffm1'>
		<input type='hidden' name='GlobalAdministrationParameters' value='GlobalAdministrationParameters'>
		<input type='hidden' name='id' value='gs'>
		<H3>{global_settings}</h3>
		<br>".RoundedLightGrey("
		<table style='width:100%'>
		<tr>
		<td align='right'><strong>{MailManAdminPassword}:</strong></td>
		<td>". Field_text('MailManAdminPassword',$mailman->main_default_array["MailManAdminPassword"],'width:80%',null,null,'{MailManAdminPassword_text}',false)."</td>
		</tr>		
		<tr>
		<td align='right'><strong>{mailmandefaultemailhost}:</strong></td>
		<td>". Field_text('MailManDefaultEmailHost',$mailman->main_default_array["MailManDefaultEmailHost"],'width:80%',null,null,'{mailmandefaulthosts}',false)."</td>
		</tr>
		<tr>
		<td align='right'><strong>{MailManDefaultUrlHost}:</strong></td>
		<td>". Field_text('MailManDefaultUrlHost',$mailman->main_default_array["MailManDefaultUrlHost"],'width:80%',null,null,'{mailmandefaulthosts}',false)."</td>
		</tr>
		<tr>
		<td align='right'><strong>{MailManDefaultUrlPattern}:</strong></td>
		<td>". Field_text('MailManDefaultUrlPattern',$mailman->main_default_array["MailManDefaultUrlPattern"],'width:80%',null,null,'{MailManDefaultUrlPattern}',false)."</td>
		</tr>		
		<tr>
		<td align='right'><strong>{MailManDefaulPublicArchiveUrl}:</strong></td>
		<td>". Field_text('MailManDefaulPublicArchiveUrl',$mailman->main_default_array["MailManDefaulPublicArchiveUrl"],'width:80%',null,null,'{MailManDefaulPublicArchiveUrl_text}',false)."</td>
		</tr>			
		<tr>
		<td align='right'><strong>{VirtualHostOverview}:</strong></td>
		<td>". Field_yesno_checkbox_img('VirtualHostOverview',$mailman->main_default_array["VirtualHostOverview"],'{VirtualHostOverview_text}')."</td>
		</tr>

			
		</form>
		<tr>
		<td align='right'>&nbsp;</td>
		<td align='right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm1','" . CurrentPageName() . "',true);\"></td>
		</tr>
		</table>") . "</div>";

$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html);	
	
	
	
	
}


?>