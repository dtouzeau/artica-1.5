<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
session_start();
include_once("ressources/class.templates.inc");
include_once("ressources/class.ldap.inc");
include_once("ressources/class.main_cf.inc");
$user=new usersMenus();
if($user->AsPostfixAdministrator==false){
	$tpl=new templates();
	echo "alert('".$tpl->_ENGINE_parse_body('{ERROR_NO_PRIVS}')."')";
	die();
	}
if(isset($_GET["otherpage"])){otherpage();exit;}	
if(isset($_GET["undisclosed_recipients_header"])){SaveForm();exit();}


js();

function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{other_settings}');
	$html="
	function PostFixOtherLoad(){
		YahooWin5(500,'$page?otherpage=yes','$title');
		
	}
	
	
	var x_SavePostfixOtherSection= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue);}
		PostFixOtherLoad();
	}	
	
	function SavePostfixOtherSection(){
		var undisclosed_recipients_header=document.getElementById('undisclosed_recipients_header').value;
		var enable_original_recipient=document.getElementById('enable_original_recipient').value;	
		document.getElementById('otherpagedvi').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		var XHR = new XHRConnection();
		XHR.appendData('enable_original_recipient',enable_original_recipient);
		XHR.appendData('undisclosed_recipients_header',undisclosed_recipients_header);
		XHR.sendAndLoad('domains.edit.user.php', 'GET',x_SavePostfixOtherSection);		  
	}
	
	PostFixOtherLoad();";
	echo $html;
}



function otherpage(){
	$page=CurrentPageName();
$main=new main_cf();



$html="
<H1>Postfix:{other_settings}</H1>
<div id='otherpagedvi'>
<table style='width:100%' class=table_form>
<tr>
	<td class=legend nowrap>{undisclosed_recipients_header}:</td>
	<td>" . Field_text('undisclosed_recipients_header',$main->main_array["undisclosed_recipients_header"],'width:100%')."</td>
	<td>" . help_icon("{undisclosed_recipients_header_text}")."</td>
</tr>
<tr>
	<td class=legend nowrap>{enable_original_recipient}:</td>
	<td>" .Field_yesno_checkbox('enable_original_recipient',$main->main_array["enable_original_recipient"])."</td>
	<td>" . help_icon("{enable_original_recipient_text}")."</td>
</tr>


<tr><td colspan=2><hr></td></tr>
<tr><td colspan=2 align='right'><input type='button' OnClick=\"javascript:SavePostfixOtherSection();\" value='&nbsp;&nbsp;{edit}&nbsp;&raquo;&raquo;'></td></tr>
</table>
</div>

";


	
	
	

$tpl=new Templates();
echo $tpl->_ENGINE_parse_body($html);
}

function SaveForm(){
$main=new main_cf();
while (list ($num, $ligne) = each ($_GET) ){
		$main->main_array[$num]=$ligne;
		
	}
	$main->save_conf();
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');
	
}