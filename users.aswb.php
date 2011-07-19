<?php
session_start();
include_once('ressources/class.templates.inc');
include_once('ressources/class.ldap.inc');
include_once('ressources/class.html.pages.inc');
include_once('ressources/class.user.inc');


$priv=new usersMenus();
if(isset($_GET["list"])){email_list();exit;}
if(isset($_GET["Addwbl"])){popup_addwbl();exit;}
if(isset($_GET["blw_email"])){add_wbl();exit;}
if(isset($_GET["delete_email"])){delete_wbl();exit;}



page();

function page(){
$page=CurrentPageName();
$tpl=new templates();
$title1=$tpl->_ENGINE_parse_body('{add_email}');

$html="
<br><br>
<table style='width:100%'>
<tr>
<td width=1% valign='top'><img src='img/bg_chess.jpg'></td>
<td valign='top'>". RoundedLightGreen("
	<H3 style='margin-bottom:0px'>{white list}</H2>
	<p>{white_list_text}</p>
	<H3 style='margin-bottom:0px'>{black list}</H2>
	<p>{black_list_text}</p>")."
</td>
</tr>
<td colspan=2 width=100% valign='top'>
<div id='mailist'></div>
</td>
</tr>
</table>
<script>
LoadAjax('mailist','$page?list=yes');

function wbladd(){YahooWin(400,'$page?Addwbl=yes','$title1');}

var X_ActionWbladdForm= function (obj) {
	var results=trim(obj.responseText);
	if(results.length>0){alert(results);}
	LoadAjax('mailist','$page?list=yes');
	}

function ActionWbladdForm(){
	var blw_email=document.getElementById('blw_email').value;
	var blw_type=document.getElementById('blw_type').value;
	var XHR = new XHRConnection();
	XHR.appendData('blw_email',blw_email);	
	XHR.appendData('blw_type',blw_type);		
	XHR.sendAndLoad('$page', 'GET',X_ActionWbladdForm);	
	}
	
	
function delete_aswbl(email,type){
	var XHR = new XHRConnection();
	XHR.appendData('delete_email',email);	
	XHR.appendData('blw_type',type);		
	XHR.sendAndLoad('$page', 'GET',X_ActionWbladdForm);	
	}

</script>
";

$tpl=new template_users("{white list} & {black list}",$html);
echo $tpl->web_page;	
}

function email_list(){
	
	
$user=new user($_SESSION["uid"]);
$white=$user->amavisWhitelistSender;
$black=$user->amavisBlacklistSender;

while (list ($num, $val) = each ($white) ){
	$arr[$val]=0;
	}
while (list ($num, $val) = each ($black) ){
	$arr[$val]=1;
	}
	
	
if(is_array($arr)){ksort($arr);}
if(!is_array($arr)){$arr=array();}



while (list ($num, $val) = each ($arr) ){
	if($val==1){$color="black";}else{$color="white";}
	$row=$row .
	"<tr>
		<td valign='top' width=1% style='background-color:$color;border:1px dotted #CCCCCC;'>&nbsp;</td>
		<td valign='top' width=99% style='border-bottom:1px solid #CCCCCC'><code><strong style='font-size:13px'>$num</td>
		<td valign='top' width=1% style='border-bottom:1px solid #CCCCCC'>" . imgtootltip('ed_delete.gif',"{delete}:$num","delete_aswbl('$num',$val);")."</td>
	</tr>
		";
	}	
	
	
	
$button=Paragraphe("member-add-64.png",'{add_email}','{add_backlistwhitelist_email}',"javascript:wbladd()","{add_email}",220,70);
	$html="
	
	<table style='width:100%'>
	<tr>
		<td valign='top'>
		<div style='width:455px;height:400px;overflow:auto'>
			<table style='width:445px;padding:5px;border:1px solid #CCCCCC;padding-bottom:10px'>
			<tr>
				<th width=1% nowrap>{email_type}</th>
				<th width=99%>{email}</th>
				<th width=1% nowrap>&nbsp;</th>
			</tr>
			$row
			</table>
			</div>
		
		</td>
		<td valign='top'>$button</td>
	</tr>
	</table>
	
	
	";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
}

function popup_addwbl(){
	$page=CurrentPageName();
	$html="<h1>{add_email}</H1>
	<p class=caption>{add_backlistwhitelist_email}</p>
	<p class=caption>{add_wbl_text}</p>
	<form name='ffm1blw'>
	<table style='width:100%;background-color:#FFFFFF;padding:5px;border:1px solid #CCCCCC'>
	<tr>
		<td class=legend>{email}:</td>
		<td>".Field_text('blw_email','','width:190px')."</td>
	</tr>
	<tr>
	<td class=legend>{email_type}:</td>
	<td>" . Field_numeric_checkbox_img('blw_type',0,'{email_type_help}')."</TD>
	</tr>
	<tr>
	<td colspan=2 style='padding-top:4px;border-top:1px solid #CCCCCC' align='right'><input type='button' OnClick=\"javascript:ActionWbladdForm();\" value='{add}'></td>
	</tr>
	</table>
	</form>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	}
	
function add_wbl(){
	$user=new user($_SESSION["uid"]);
	if($_GET["blw_type"]==1){
		$user->add_whitelist($_GET["blw_email"]);
	}else{
		$user->add_blacklist($_GET["blw_email"]);
	}
}
function delete_wbl(){
	$user=new user($_SESSION["uid"]);
if($_GET["blw_type"]==0){
		$user->del_whitelist($_GET["delete_email"]);
	}else{
		$user->del_blacklist($_GET["delete_email"]);
	}	
	
	
}


?>