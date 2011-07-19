<?php
include_once('ressources/class.templates.inc');
include_once('ressources/class.main_cf.inc');
include_once('ressources/class.main_cf_filtering.inc');



if(isset($_GET["add_subject_rule"])){add_subject_rule();exit;}
if(isset($_GET["add_extension"])){add_extension();exit;}
if(isset($_GET["add_mail_from_rule"])){add_mail_from_rule();exit;}
if(isset($_GET["del_extension"])){del_extension();exit;}
if(isset($_GET["delete_mail_from"])){delete_mail_from();exit;}
if(isset($_GET["delete_mail_subject_rule"])){delete_mail_subject_rule();exit;}


if(!isset($_GET["feature"])){$_GET["feature"]="mime";}


switch ($_GET["feature"]) {
	case "mime": Page_mime();break;
	case "headers": Page_headers();break;
	case "from": Page_headers_from();break;
	default:
		break;
}



function ToolBarr(){
return $html;
	
}

function Page_headers_from(){
	
$toolbar=ToolBarr();

$html="$toolbar<p>{from_rules_explain}</p>
<table style='width:60%'>
	<tr class='rowB'>
		<td >{mail_from_add}</td>
		<td ><input type='text' id='mail_from_rule' value=''></td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td >{postfix_error}</td>
		<td><input type='text' id='postfix_error' value=''></td>
		<td ><input type='button' value='Submit&nbsp;&raquo;' OnClick=\"javascript:add_mailfrom_rule();\"></td>
	</tr>		
	</table>
<div id='array_from'>" . tableau_FROM() . "</div>";
$tpl=new templates('{bt_filter_headers_from}',$html);
echo $tpl->web_page;
	
	
}


function Page_headers(){
	
$toolbar=ToolBarr();

$html="$toolbar

<p>{subjects_rules_explain}</p>
<table style='width:60%'>
	<tr>
		<td >{subject_add}</td>
		<td ><input type='text' id='subject_rule' value=''></td>
		<td >&nbsp;</td>
	</tr>	
	<tr>
		<td >{postfix_error}</td>
		<td ><input type='text' id='postfix_error' value=''></td>
		<td><input type='button' value='Submit&nbsp;&raquo;' OnClick=\"javascript:add_subject_rule();\"></td>
	</tr>		
	</table>
	
<br>
<div id='array_subject'>" . tableau_subject() . "</div>";
$tpl=new templates('{title_header}',$html);
echo $tpl->web_page;
	
	
}

function tableau_FROM(){
	$head=new main_header_check();
	if(!is_array($head->array_subjects)){return null;}
	
	$html="<table style='width:99%' align=center>
	<tr class='rowT'>
	<td width=1%>&nbsp;</td>
	<td  >Match in from address</td>
	<td >Error generated</td>
	<td>Delete</td>
	
	</tr>";
	if(!is_array($head->array_from)){return null;}
	while (list ($num, $ligne) = each ($head->array_from) ){
		$PATTERN=$ligne["PATTERN"];
		$PATTERN=$head->Trasnformtohuman($PATTERN);
		$EXPLAIN=$ligne["EXPLAIN"];
		if($class=='rowA'){$class='rowB';}else{$class='rowA';}
		
		$html=$html . "<tr class='$class'>
		<td><img src='img/cadenas.jpg'></td>
		<td >$PATTERN</td>
		<td >$EXPLAIN</td>
		<td align='center'>
			<a href=\"javascript:delete_mail_from($num);\"onMouseOver=\"javascript:AffBulle('{js_del_from}');\" OnMouseOut=\"javascript:HideBulle();\"><img src='img/x.gif'></a></td>
		</tr>
		";
	}
	$html=$html . "</table>";
	$tpl=new templates();
	$html=$tpl->_parse_body($html);
	return $html;
	
	
}

function tableau_subject(){
	$head=new main_header_check();
	if(!is_array($head->array_subjects)){
		writelogs("head->array_subjects has no entries!!",__FUNCTION__,__FILE__);
		return null;}
	
	$html="<table>
	<tr class='rowT'>
	<td width=1%>&nbsp;</td>
	<td >Match in subject</td>
	<td >Error generated</td>
	<td width=1%>&nbsp;</td>
	</tr>";
	writelogs("head->array_subjects formating table",__FUNCTION__,__FILE__);
	while (list ($num, $ligne) = each ($head->array_subjects) ){
		$PATTERN=$ligne["PATTERN"];
		$PATTERN=$head->Trasnformtohuman($PATTERN);
		$EXPLAIN=$ligne["EXPLAIN"];
		if($class=='rowA'){$class='rowB';}else{$class='rowA';}
		$html=$html . "
		<tr class=$class>
		<td><img src='img/cadenas.jpg'></td>
		<td>$PATTERN</td>
		<td>$EXPLAIN</td>
		<td><a href=\"javascript:delete_mail_subject_rule($num);\"onMouseOver=\"javascript:AffBulle('{js_del_subject}');\" OnMouseOut=\"javascript:HideBulle();\"><img src='img/x.gif'></a></td>
		</tr>
		";
	}
	$html=$html . "</table>";
	
	return $html;
	
	
}



function Page_mime(){
	
	
$toolbar=ToolBarr();

$html=
"$toolbar<p>{mime_attachments_explain}</p>
<table '>
	<tr class='rowB'>
		<td>{mime_attachments_add}</td>
		<td ><input type='text' id='extension' value=''></td>
		<td ><input type='button' value='Submit&nbsp;&raquo;' OnClick=\"javascript:add_filter_extension();\"></td>
	</tr>	
	
	
</tr>
</table>	
<div id='extension_list'>" . ExtensionsList() . "</div>



";

$tpl=new templates('{title_mime}',$html);
echo $tpl->web_page;
	
	
}

function add_subject_rule(){
	writelogs('adding subject rule: ' . $_GET["add_subject_rule"],__FUNCTION__,__FILE__);
	$main=new main_header_check();
	$main->add_subject_rule(trim($_GET["add_subject_rule"]),$_GET["add_postfix_error"]);
	echo tableau_subject();
}

function add_mail_from_rule(){
	writelogs('adding mail from rule: ' . $_GET["add_mail_from_rule"],__FUNCTION__,__FILE__);
	$main=new main_header_check();
	$main->add_mailfrom_rule(trim($_GET["add_mail_from_rule"]),$_GET["add_postfix_error"]);
	echo tableau_FROM();	
	
}

function add_extension(){
	$ext=$_GET["add_extension"];
	$ext=str_replace('*.','',$ext);
	$main=new main_filter_extensions();
	$main->addExtension($ext);
	echo ExtensionsList();
	}
function del_extension(){
	
	$ext=$_GET["del_extension"];
	writelogs('Delete extension number ' . $ext,__FUNCTION__,__FILE__);
	$main=new main_filter_extensions();
	$main->DeleteExtension($ext);
	echo ExtensionsList();
	}	
	
function ExtensionsList(){
	$main=new main_filter_extensions();
	$hash=$main->array_extension;
	if(!is_array($hash)){return null;}
	
	$html="<p>
		<table><tr class='rowT'>
		<td colspan=3>{extension_list_title}</td>";
	while (list ($num, $ligne) = each ($hash) ){
		$num2=$num+1;
		if($class=='rowA'){$class='rowB';}else{$class='rowA';}
		$html=$html . "<tr class=$class>
			<td style='width:1%'><img src='img/cadenas.jpg'></td>
			<td ><strong>&nbsp; *.$ligne&nbsp;</strong></td>
			<td style='width:1%'><a href=\"javascript:delete_extension($num);\"onMouseOver=\"javascript:AffBulle('{js_del_extension}');\" OnMouseOut=\"javascript:HideBulle();\"><img src='img/x.gif'></a></td>
			</tr>";
	}
	
	$html=$html . "</table></p>";
	$tpl=new Templates();
	$html=$tpl->_parse_body($html);	
	return $html;
	
	
}

function delete_mail_from(){
	$main=new main_header_check();
	$main->delete_from($_GET["delete_mail_from"]);
	echo tableau_FROM();
}

function delete_mail_subject_rule(){
	$main=new main_header_check();
	$main->delete_subject($_GET["delete_mail_subject_rule"]);	
	echo tableau_subject();
}



