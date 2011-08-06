<?php
	if(isset($_POST["template_content_post"])){$_GET["tinymce"]=$_POST["template_content_post"];$_GET["ou"]=$_POST["ou"];}
	include_once('ressources/class.emailings.inc');
	if(isset($_GET["edit-template-js"])){template_edit_js();exit;}
	if(isset($_GET["edit-template-perf"])){template_edit_save();exit;}
	
	if(isset($_GET["add-template-js"])){template_add_js();exit;}
	if(isset($_GET["template-add-popup"])){template_add_popup();exit;}
	if(isset($_GET["add-template-perf"])){template_add_perform();exit;}
	if(isset($_GET["delete-template-perf"])){template_del_perform();exit;}
	
	
	if(isset($_GET["template-edit-popup"])){template_edit_popup();exit;}
	if(isset($_GET["tinymce"])){template_tinymce();exit;}
	
	
	if(isset($_GET["template-css"])){template_tinymce_css();exit;}
	if(isset($_GET["template-css-popup"])){template_css_edit();exit;}
	if(isset($_GET["template-css-save"])){template_css_save();exit;}
	
	if(isset($_GET["template-keyword-popup"])){template_keyword_popup();exit;}
	
	if(isset($_GET["template-advopts-popup"])){template_advopts_popup();exit;}
	if(isset($_GET["template-advopts-display"])){template_advopts_display();exit;}
	if(isset($_GET["X-headers-save"])){template_advopts_save();exit;}
	
	if(isset($_GET["template-attached-files-popup"])){template_attach_edit();exit;}
	if(isset($_GET["template-attached-files-iframe"])){template_attach_iframe();exit;}
	if(isset($_GET["template-attached-files-delete"])){template_attach_delete();exit;}
	if(isset($_GET["template-attached-files-show"])){echo template_attach_list($_GET["template-attached-files-show"]);exit;}
	if( isset($_POST['upload']) ){template_attach_uploaded();}
	
	
function template_edit_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$template_title=$tpl->_ENGINE_parse_body("{template}::{$_GET["edit-template-js"]}");
	$css=$tpl->_ENGINE_parse_body("{css}::{$_GET["edit-template-js"]}");
	$keyword=$tpl->_ENGINE_parse_body("{keywords}::{$_GET["edit-template-js"]}");
	$advanced_options=$tpl->_ENGINE_parse_body("{advanced_options}::{$_GET["edit-template-js"]}");
	$attached_files=$tpl->_ENGINE_parse_body("{attached_files}::{$_GET["edit-template-js"]}");
	$are_you_sure_to_delete=$tpl->javascript_parse_text("{are_you_sure_to_delete}");
	$html="
		function emailing_template_edit(){
			YahooWin4(656,'$page?template-edit-popup={$_GET["edit-template-js"]}&ou={$_GET["ou"]}','$template_title');
		}
		
	var x_template_edit_gene= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			YahooWin4Hide();
			RefreshTab('emailing_campaigns');
			emailing_template_edit();	
		}			
		
function template_edit_gene(){
	var XHR = new XHRConnection();
	XHR.appendData('edit-template-perf','{$_GET["edit-template-js"]}');
	XHR.appendData('template_name',document.getElementById('template_name').value);
	XHR.appendData('subject',document.getElementById('subject').value);
	XHR.appendData('ou','{$_GET["ou"]}');
	XHR.sendAndLoad('$page', 'GET',x_template_edit_gene);	
	}
	
var x_TemplateeMailingDelete= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>3){alert(tempvalue)};
	YahooWin4Hide();
	RefreshTab('emailing_campaigns');
}		
	
function TemplateeMailingDelete(){
	if(confirm('$are_you_sure_to_delete')){
		var XHR = new XHRConnection();
		XHR.appendData('delete-template-perf','{$_GET["edit-template-js"]}');
		XHR.appendData('ou','{$_GET["ou"]}');
		XHR.sendAndLoad('$page', 'GET',x_TemplateeMailingDelete);	
		}

}
	
var x_EditCSSTemplateSave= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>3){alert(tempvalue)};
	YahooWin5Hide();
}	
	
function EditCSSTemplateSave(){
	var XHR = new XHRConnection();
	XHR.appendData('template-css-save','{$_GET["edit-template-js"]}');
	XHR.appendData('css',document.getElementById('template-css-content').value);
	XHR.appendData('ou','{$_GET["ou"]}');
	document.getElementById('template_css_edit_div').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
	document.getElementById('template_css_edit_div2').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
	XHR.sendAndLoad('$page', 'GET',x_EditCSSTemplateSave);	
	}	
	
function EditCSSTemplate(){
		YahooWin5(700,'$page?template-css-popup={$_GET["edit-template-js"]}&ou={$_GET["ou"]}','$css');
	}
	
function KeywordsTemplate(){
	YahooWin5(500,'$page?template-keyword-popup={$_GET["edit-template-js"]}&ou={$_GET["ou"]}','$keyword');
}

function template_edit_advopts(){
	YahooWin5(450,'$page?template-advopts-popup={$_GET["edit-template-js"]}&ou={$_GET["ou"]}','$advanced_options');
}

function emailing_template_attachedFiles(){
	YahooWin5(630,'$page?template-attached-files-popup={$_GET["edit-template-js"]}&ou={$_GET["ou"]}','$attached_files');
}
		
	emailing_template_edit();";
	
	echo $html;
	
	
}

function template_del_perform(){
	$template_id=$_GET["delete-template-perf"];
	
	$sql="DELETE FROM emailing_tbl_files WHERE template_id='$template_id'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup")	;

	
	$sql="DELETE FROM emailing_templates WHERE ID='$template_id'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup")	;

}




function template_advopts_popup(){
	$ID=$_GET["template-advopts-popup"];
	$sql="SELECT advopts FROM emailing_templates WHERE ID='$ID'";
	$q=new mysql();
	$page=CurrentPageName();
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$array=unserialize(base64_decode($ligne["advopts"]));
	$xmailers["Zarafa 6.40.0-20653"]="Zarafa 6.40";
	$xmailers["Sylpheed-Claws 0.9.12a (GTK+ 1.2.10; i586-mandrake-linux-gnu)"]="Sylpheed-Claws";
	$xmailers["SquirrelMail/1.4.0"]="SquirrelMail";
	$xmailers["Mozilla Thunderbird 1.0 (Windows/20041206)"]="Mozilla Thunderbird";
	$xmailers["Microsoft Outlook Express 6.00.3790.1106"]="Outlook Express 3790.1106";
	$xmailers["Microsoft Outlook Express 6.00.2900.2963"]="Outlook Express 2900.2963";
	$xmailers["Microsoft Office Outlook, Build 11.0.5510"]="Office Outlook 11.0.5510";
	
	
	$html="
	<div id='xheaders'>
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:13px'>{Disposition-Notification-To}:</td>
		<td>". Field_text("Disposition-Notification-To",$array["Disposition-Notification-To"],'font-size:13px;padding:3px')."</td>
		<td width=1%>". help_icon("{Disposition-Notification-To_text}")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{from}:</td>
		<td>". Field_text("From",$array["From"],'font-size:13px;padding:3px')."</td>
		<td width=1%>". help_icon("{template_from_help}")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{from_name}:</td>
		<td>". Field_text("From_name",$array["From_name"],'font-size:13px;padding:3px')."</td>
		<td width=1%>". help_icon("{from_name_help}")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{Return-Path}:</td>
		<td>". Field_text("Return-Path",$array["Return-Path"],'font-size:13px;padding:3px')."</td>
		<td width=1%>". help_icon("{Return-Path-explain}")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{Reply-to}:</td>
		<td>". Field_text("Reply-to",$array["Reply-to"],'font-size:13px;padding:3px')."</td>
		<td width=1%>". help_icon("{Reply-to-explain}")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{X-Mailer}:</td>
		<td>". Field_array_Hash($xmailers,"X-Mailer",$array["X-Mailer"],null,null,0,"font-size:13px")."</td>
		<td width=1%>". help_icon("{X-Mailer-explain}")."</td>
	</tr>	
	<tr>
		<td colspan=3 align='right'><hr>
			". button("{apply}","SaveXheadersTemplate()")."
		</td>
	</tr>
					
	</table>
	</div>
	
<script>
var x_SaveXheadersTemplate= function (obj) {
	var tempvalue=obj.responseText;
	if(tempvalue.length>3){alert(tempvalue)};
	LoadAjax('advoptsdisplay','$page?template-advopts-display=$ID&ou={$_GET["ou"]}');
	YahooWin5Hide();
}	
	
function SaveXheadersTemplate(){
	var XHR = new XHRConnection();
	XHR.appendData('X-headers-save','$ID');
	XHR.appendData('ou','{$_GET["ou"]}');
	XHR.appendData('Disposition-Notification-To',document.getElementById('Disposition-Notification-To').value);
	XHR.appendData('From',document.getElementById('From').value);
	XHR.appendData('From_name',document.getElementById('From_name').value);
	XHR.appendData('Reply-to',document.getElementById('Reply-to').value);
	XHR.appendData('Return-Path',document.getElementById('Return-Path').value);
	XHR.appendData('X-Mailer',document.getElementById('X-Mailer').value);
	document.getElementById('xheaders').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
	XHR.sendAndLoad('$page', 'GET',x_SaveXheadersTemplate);	
	}	
	
</script>
	
	";
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("$html");	
	
}

function template_advopts_save(){
	$ID=$_GET["X-headers-save"];
	$advopts=base64_encode(serialize($_GET));
$sql="UPDATE emailing_templates SET advopts='$advopts'  WHERE ID='$ID'";	
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		echo $q->mysql_error;
	}
	
}



function template_css_save(){
	$ID=$_GET["template-css-save"];
	$sql="UPDATE emailing_templates SET template_css='{$_GET["css"]}'  WHERE ID='$ID'";	
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		echo $q->mysql_error;
	}
}

function template_keyword_popup(){
	
	$html="
	<div style='font-size:14px'>{template_keywords_text}</div>
	";
	

		
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("$html");
	
}


function template_css_edit(){
	$ID=$_GET["template-css-popup"];
	$sql="SELECT template_css FROM emailing_templates WHERE ID='$ID'";
	$q=new mysql();
	$page=CurrentPageName();
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
	$css=$ligne["template_css"];	
	
	$tpl=new templates();
	$apply=$tpl->_ENGINE_parse_body("{apply}");		
	
	$html="
	<center style='margin:10px' id='template_css_edit_div2'>". button("$apply","EditCSSTemplateSave()")."</center>
	<textarea id='template-css-content' style='width:685px;height:500px;overflow:auto;font-size:12px;font-family:Courrier new;'>$css</textarea>
	<center style='margin:10px' id='template_css_edit_div'>". button("$apply","EditCSSTemplateSave()")."</center>
	
	";
	
echo $html;
	
}


function template_edit_save(){
	$_GET["template_name"]=replace_accents($_GET["template_name"]);
	$sql="UPDATE emailing_templates SET 
		template_name='{$_GET["template_name"]}',
		subject='{$_GET["subject"]}'
		WHERE ID={$_GET["edit-template-perf"]}";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."\n$sql\n";}
}




function template_edit_popup(){
	$q=new mysql();
	$page=CurrentPageName();
	$ID=$_GET["template-edit-popup"];
	$sql="SELECT * FROM emailing_templates WHERE ID='{$_GET["template-edit-popup"]}'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$html="
	<table style='width:100%'>
	<tr>
	<td valign='middle'>
		<h3>{$ligne["template_name"]}</H3>
	</td>
	<td valign='middle' width=1%>".imgtootltip("delete-24.png","{delete}","TemplateeMailingDelete()")."</td>
	</tr>
	</table>
	<div style='font-size:11px;text-align:right;padding-top:5px;border-top:1px solid #005447'>{created_on}:{$ligne["zdate"]}</div>
	
	<table>
	<tr>
	<td valign='top' style='width:250px'>
	". Paragraphe("edit-html-64.png","{template_content}","{template_content_text}","javascript:s_PopUpFull('$page?tinymce=$ID&ou={$_GET["ou"]}',800,700)")."
	<br>
	". Paragraphe("edit-css-64.png","{css}","{template_css_text}","javascript:EditCSSTemplate()")."
	<br>
	". Paragraphe("attach-document-64.png","{attached_files}","{add_attached_files_text}","javascript:emailing_template_attachedFiles()")."	
	<br>
	". Paragraphe("keywords-64.png","{keywords}","{template_keywords_text}","javascript:KeywordsTemplate()")."
	
	
	
	</td>
	</td>
	<td valign='top' width=100%><br>
		<table style='width:100%'>
		<tr>
			<td class=legend style='font-size:13px' nowrap>{template_name}:</td>
			<td>". Field_text("template_name",$ligne["template_name"],"font-size:13px;padding:3px")."</td>
		</tr>
		<tr>
			<td class=legend style='font-size:13px'>{subject}:</td>
			<td width=99%>". Field_text("subject",$ligne["subject"],"font-size:13px;padding:3px")."</td>
		</tr>		
		<tr>
			<td colspan=2 align='right'><HR>". button("{apply}","template_edit_gene()")."</td>
		</TR>
	<tr>
		<td colspan=2><div id='advoptsdisplay'></div></td>
	</tr>			
	<tr>
		<td colspan=2 align='right'><hr>". button("{advanced_options}","template_edit_advopts()")."</td>
	</tr>		
		</table>
	</td>
	</tr>
	</table>
	<script>
		LoadAjax('advoptsdisplay','$page?template-advopts-display={$_GET["template-edit-popup"]}&ou={$_GET["ou"]}');
	</script>
	";
		
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("$html");
	
}

function template_advopts_display(){

	$ID=$_GET["template-advopts-display"];
	$sql="SELECT advopts FROM emailing_templates WHERE ID='$ID'";
	$q=new mysql();
	$page=CurrentPageName();
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$array=unserialize(base64_decode($ligne["advopts"]));
	
	
	$sql="SELECT COUNT(ID) as tcount ,SUM(filesize) as tsume FROM  emailing_tbl_files WHERE template_id=$ID";
	$files_att=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$attached_files=$files_att["tcount"]. "&nbsp;{files}:&nbsp;". FormatBytes($files_att["tsume"]/1024);
	
	
$html="
	<table style='width:100%;margin:3px;padding:3px'>
	<tr>
		<td class=legend style='font-size:13px'>{Disposition-Notification-To}:</td>
		<td><strong style='font-size:13px'>&nbsp;{$array["Disposition-Notification-To"]}</strong></td>
		
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{from}:</td>
		<td><strong style='font-size:13px'>&nbsp;{$array["From"]}</strong></td>
		
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{from_name}:</td>
		<td><strong style='font-size:13px'>&nbsp;{$array["From_name"]}</strong></td>
		
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{Return-Path}:</td>
		<td><strong style='font-size:13px'>&nbsp;{$array["Return-Path"]}</strong></td>
		
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{Reply-to}:</td>
		<td><strong style='font-size:13px'>&nbsp;{$array["Reply-to"]}</strong></td>
		
	</tr>
	<tr>
		<td class=legend style='font-size:13px'>{X-Mailer}:</td>
		<td><strong style='font-size:13px'>&nbsp;{$array["X-Mailer"]}</strong></td>
		
	</tr>	
	<tr>
		<td class=legend style='font-size:13px'>{attached_files}:</td>
		<td><strong style='font-size:13px'>&nbsp;$attached_files</strong></td>
		
	</tr>
					
	</table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("$html");
	
}
	
	
function template_add_js(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$template_add=$tpl->_ENGINE_parse_body('{template_add}');
	
	$html="
		function emailing_template_add(){
			YahooWin4(500,'$page?template-add-popup=yes&ou={$_GET["ou"]}','$template_add');
		}

	
	emailing_template_add();";
	
	echo $html;
	
	
}

function template_add_popup(){
	$tpl=new templates();
	$page=CurrentPageName();
	$html="
	
	<div id='newtemplatediv'>
	<table style='width:100%'>
	<tr>
		<td valign='top' width=1%><img src='img/template-add-90.png'></td>
		<td valign='top'><div style='font-size:16px;margin:5px'>{template_add_emailing_text}</div>
			<table style='width:100%'>
			<tr>
				<td valign='top' style='font-size:13px' class=legend>{template_name}:</td>
				<td>". Field_text("template_name",null,"font-size:13px;padding:3px",null,null,null,false,"SaveNeweMailingTemplatePress(event)")."</td>
			</tr>
			<tr>
				<td colspan=2 align='right'><HR>". button("{add}","SaveNeweMailingTemplate()")."</td>
			</TR>
			</table>
		</td>
	</tr>
	</table>
	</div>
	<script>
	
	var x_SaveNeweMailingTemplate= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			YahooWin4Hide();
			RefreshTab('emailing_campaigns');	
		}	
		
		
		function SaveNeweMailingTemplatePress(e){
			if(checkEnter(e)){SaveNeweMailingTemplate();exit;}
		}
	
	
		function SaveNeweMailingTemplate(){
			var XHR = new XHRConnection();
	      	XHR.appendData('add-template-perf',document.getElementById('template_name').value);
	      	XHR.appendData('ou','{$_GET["ou"]}');
	      	if(document.getElementById('newtemplatediv')){
	      		document.getElementById('newtemplatediv').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
			}
		  	XHR.sendAndLoad('$page', 'GET',x_SaveNeweMailingTemplate);	
		}
	</script>
	";
	echo $tpl->_ENGINE_parse_body("$html");
	
}

function template_add_perform(){
	$template_name=replace_accents($_GET["add-template-perf"]);
	$tpl=new templates();
	if($template_name==null){echo $tpl->javascript_parse_text("{template_name} NULL");exit;}
	$ou=base64_decode($_GET["ou"]);
	$sql="INSERT INTO emailing_templates (ou,template_name,zdate)
	VALUES('$ou','$template_name',NOW());";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;}
	
}

function template_tinymce(){
	$ID=$_GET["tinymce"];
	
	
	if(isset($_POST["template_content_post"])){
		 
		$template_content=$_POST["template_content_{$_POST["template_content_post"]}"];
		$sql="UPDATE emailing_templates SET template_datas='$template_content' WHERE ID=$ID";
		$q=new mysql();		
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){$error="<center><H1 style='color:red'>$q->mysql_error</H1></center>";}
		
	}
	
	
	$sql="SELECT * FROM emailing_templates WHERE ID='$ID'";
	
	$q=new mysql();
	$page=CurrentPageName();
	$tpl=new templates();
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	if($ligne["ou"]<>base64_decode($_GET["ou"])){die();}
	
	
	//TinyMce();
	$html="
	<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
	<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en-gb\" lang=\"en-gb\">
	<head>
    <meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\" />
    <title>{$ligne["template_name"]}</title>
  	<script type=\"text/javascript\" language=\"javascript\" src=\"js/jquery-1.4.2.min.js\"></script>
    <script language=\"javascript\" type=\"text/javascript\" src=\"js/tiny_mce/tiny_mce.js\"></script> 	
  	
	<script>
		//removed template
		tinyMCE.init({
		mode : \"textareas\",
		theme : \"advanced\",
		skin : \"cirkuit\",
		languages : '". $tpl->_detect_lang()."',
		
		plugins : \"pdw,autoresize,safari,pagebreak,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,inlinepopups\",
		theme_advanced_buttons1 : \"pdw_toggle,save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect\",
		theme_advanced_buttons2 : \"cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor\",
		theme_advanced_buttons3 : \"tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen\",
		theme_advanced_buttons4 : \"insertlayer,moveforward,movebackward,absolute,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,pagebreak\",
		theme_advanced_toolbar_location : \"top\",
		theme_advanced_toolbar_align : \"left\",
		theme_advanced_statusbar_location : \"bottom\",
		theme_advanced_resizing : true,
		pdw_toggle_on : 1,
        pdw_toggle_toolbars : \"2,3,4\",
		content_css : \"$page?template-css=$ID&bogus=\"+ new Date().getTime()+\"&ou={$_GET["ou"]}\",
		theme_advanced_font_sizes: \"8px,9px,10px,12px,13px,14px,16px,18px,20px\",
		font_size_style_values : \"8px,9px,10px,12px,13px,14px,16px,18px,20px\",
		
	style_formats : [
		{title : 'Bold text', inline : 'b'},
		{title : 'Red text', inline : 'span', styles : {color : '#ff0000'}},
		{title : 'Red h1', block : 'h1', styles : {color : '#ff0000'}},
		{title : 'Table styles'},
		{title : 'Table row 1', selector : 'tr', classes : 'tablerow1'}
	],		
		
	formats : {
		alignleft : {selector : 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes : 'left'},
		aligncenter : {selector : 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes : 'center'},
		alignright : {selector : 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes : 'right'},
		alignfull : {selector : 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes : 'full'},
		bold : {inline : 'span', 'classes' : 'bold'},
		italic : {inline : 'span', 'classes' : 'italic'},
		underline : {inline : 'span', 'classes' : 'underline', exact : true},
		strikethrough : {inline : 'del'}
		
	}		
		
	});
	</script>
	</head>
	<body style='margin:5px;padding:0px'>
	$error
  	<form name='FORM_$ID' method=POST action='$page'>
  	
  	<input type='hidden' name=ou id=ou value='{$_GET["ou"]}'>
  	<input type='hidden' name=template_content_post id=template_content_post value='$ID'>
	<textarea id=\"template_content_$ID\" name=\"template_content_$ID\" rows=\"40\" cols=\"80\" style=\"width:100%;overflow: scroll;\">{$ligne["template_datas"]}</textarea> 
  	</form>
	</body>
  	</html>";
  	
echo $html;
	
	
}

function template_tinymce_css(){
	$ID=$_GET["template-css"];
	$sql="SELECT template_css FROM emailing_templates WHERE ID='$ID'";
	$q=new mysql();
	$page=CurrentPageName();
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
	$css=$ligne["template_css"];
	if(trim($css)==null){
		$css="
html {
	width: 100%;
	height: 100%;
}

body, td, pre {
	margin: 0px;
	width: 100%;
	height: 100%;
	overflow: hidden;
	font-family:Verdana, Arial, Helvetica, sans-serif;
	font-size: 12px;
	color: #000000;
	}
	
h1{
	font: 20px Tahoma;
}

h2{
	font: 16px Tahoma;
}

h3{
	font: 14px Tahoma;
}
td {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
}

pre {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
}

.example1 {
	font-weight: bold;
	font-size: 14px
}

.example2 {
	font-weight: bold;
	font-size: 12px;
	color: #FF0000
}

.tablerow1 {
	background-color: #BBBBBB;
}

thead {
	background-color: #FFBBBB;
}

tfoot {
	background-color: #BBBBFF;
}

th {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 13px;
}


.bold {
	font-weight: bold;
}

.italic {
	font-style: italic;
}

.underline {
	text-decoration: underline;
}

.left {
	text-align: inherit;
}

.center {
	text-align: center;
}

.right {
	text-align: right;
}

.full {
	text-align: justify
}

img.left, table.left {
	float: left;
	text-align: inherit;
}

img.center, table.center {
	margin-left: auto;
	margin-right: auto;
	text-align: inherit;
}

img.center {
	display: block;
}

img.right, table.right {
	float: right;
	text-align: inherit;
}

	
";
		
	$cssi=addslashes($css);	
	$sql="UPDATE emailing_templates SET template_css='$cssi' WHERE ID=$ID";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");	
	}
	
	
	echo $css;
	
}


function template_attach_edit(){
	$page=CurrentPageName();
	
	$html="<table style='width:100%'>
	<tr>
		<td valign='top' width=1%><img src='img/attach-document-128.png'></td>
		<td valign='top'>
			<div style='font-size:13px'>{add_attached_files_text}</div>
			<iframe style='width:100%;height:440px;overflow:auto;border:0px' src='$page?template-attached-files-iframe={$_GET["template-attached-files-popup"]}&ou={$_GET["ou"]}'></iframe>
		</td>
	</tr>
	</table>
		
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("$html");
	
	
}

function template_attach_list($template_id){

	$sql="SELECT ID,filename,filesize FROM emailing_tbl_files WHERE template_id='$template_id' ORDER BY ID DESC";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	$html="<table style='width:450px;padding:3px;margin:3px;border:1px solid #CCCCCC'>";
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		
	$ext=strtolower(Get_extension($ligne["filename"]));
	$img="img/ext/def_small.gif";
	if($ext<>null){
		if(isset($GLOBALS[$ext])){$img="img/ext/{$ext}_small.gif";}else{
		if(is_file("img/ext/{$ext}_small.gif")){
			$img="img/ext/{$ext}_small.gif";
			$GLOBALS[$ext]=true;
			}
		}
	}
		$size=FormatBytes($ligne["filesize"]/1024);
		
		$delete=imgtootltip("ed_delete.gif","{delete} {$ligne["filename"]}","TemplateDeleteAttachedFile('{$ligne["ID"]}')");
		
		$html=$html."
		<tr>
			<td width=1%><img src='$img'></td>
			<td width=95% nowrap><strong style='font-size:13px'>{$ligne["filename"]}</strong></td>
			<td width=1%><strong style='font-size:13px'>$size</strong></td>
			<td width=1%>$delete</td>
		</tR>
		";
		
	}
	
	$html=$html."</table>";
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body("$html");
}


function template_attach_iframe($error=null){
	
	if($_POST["ou"]<>null){$ou=$_GET["ou"];}
	if($_GET["template-attached-files-iframe"]<>null){$template_id=$_GET["template-attached-files-iframe"];}
	if($_POST["template_id"]<>null){$template_id=$_POST["template_id"];}
	$page=CurrentPageName();
	
	$html="<p>&nbsp;</p>
<div id='content' style='width:400px'>
<table style='width:100%'>
	<tr>
		<td valign='top'>
			<h3>{upload_your_file_here}</h3>
			<div style='font-size:11px'><code style='font-size:13px;font-weight:bold;color:red;margin:4px'>$error</code></div>
			<form method=\"post\" enctype=\"multipart/form-data\" action=\"$page\">
			<input type='hidden' name='upload' value='yes'>
			<input type='hidden' name='ou' value='$ou'>
			<INPUT TYPE='hidden' NAME='MAX_FILE_SIZE' VALUE='1000000'>
			<input type='hidden' name='template_id' value='$template_id'>
			<input type=\"file\" name=\"template_file\" size=\"30\">
			<div style='width:100%;text-align:right'><hr>
				<input type='submit' name='upload' value='{upload_a_file}&nbsp;&raquo;' style='width:220px'>
			</div>
			</form>
		</td>
	</tr>
	</table>
</div>

<div id='attachedfiles'>".template_attach_list($template_id)."</div>
<script>

	var x_TemplateDeleteAttachedFile= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			LoadAjax('attachedfiles','$page?template-attached-files-show=$template_id&ou=$ou');
		}	
	
	
		function TemplateDeleteAttachedFile(ID){
			var XHR = new XHRConnection();
	      	XHR.appendData('template-attached-files-delete',ID);
	      	XHR.appendData('ou','$ou');
	      	if(document.getElementById('attachedfiles')){
	      		document.getElementById('attachedfiles').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
			}
		  	XHR.sendAndLoad('$page', 'GET',x_TemplateDeleteAttachedFile);	
		}

</script>";
$html=iframe($html,0,350);
$tpl=new templates();
echo $tpl->_ENGINE_parse_body("$html");
}

function template_attach_delete(){
	
	$sql="DELETE FROM emailing_tbl_files WHERE ID={$_GET["template-attached-files-delete"]}";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;}
}


function template_attach_uploaded(){
	
	$tmp_path  = $_FILES['template_file']['tmp_name'];
	
	if(!@is_uploaded_file($tmp_path) ){
		template_attach_iframe('{error_unable_to_upload_file}');
		exit;
	}
	$name= $_FILES['template_file']['name'];	
	$type=$_FILES['template_file']["type"];
	$size=$_FILES['template_file']["size"];
	$data = addslashes(fread(fopen($tmp_path, "r"), filesize($tmp_path)));
	$template_id=$_POST["template_id"];
	
	$sql="INSERT INTO emailing_tbl_files (template_id,bin_data,filename,filesize,filetype)
	VALUES('$template_id','$data','$name','$size','$type');
	";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");	
	if(!$q->ok){
		template_attach_iframe($q->mysql_error);
	}
	
	template_attach_iframe();
	
}

	
?>