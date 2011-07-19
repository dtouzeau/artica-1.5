<?php
	session_start();
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.main_cf.inc');
	include_once('ressources/class.user.inc');	
	
	


	$usersprivs=new usersMenus();
	if(!$usersprivs->AllowEditOuSecurity){
			$tpl=new templates();
			echo "alert('".$tpl->javascript_parse_text('{ERROR_NO_PRIVS}')."');";
			die();
		}
		
		
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["MaxMailsDay"])){save();exit;}		
js();


function js(){
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{quotas_policies}");
	$page=CurrentPageName();
	echo "YahooWin4(450,'$page?popup=yes&uid={$_GET["uid"]}','$title::{$_GET["uid"]}');";
	}
	
function popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sql="SELECT * FROM postfix_sender_quotas WHERE uid='{$_GET["uid"]}'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	$MaxMailsDay=$ligne["MaxMailsDay"];
	$MaxMailDaySize=$ligne["MaxMailDaySize"];
	$OnlyNotify=$ligne["OnlyNotify"];
	if($MaxMailsDay==null){$MaxMailsDay=0;}
	if($MaxMailDaySize==null){$MaxMailDaySize=0;}
	if($OnlyNotify==null){$OnlyNotify=1;}
	
	
	$html="
		<div id='maxquotasent'>
		<table style='width:100%'>
		<tr>
			<td valign='top' class=legend style='font-size:13px'>{MaxMailsDay}:</td>
			<td valign='top'>". Field_text("MaxMailsDay",$MaxMailsDay,"font-size:13px;padding:3px;width:90px")."</td>
			<td valign='top'>&nbsp;</td>
		</tr>
		<tr>
			<td valign='top' class=legend style='font-size:13px'>{MaxMailDaySize}:</td>
			<td valign='top' style='font-size:13px' nowrap>". Field_text("MaxMailDaySize",$MaxMailDaySize,"font-size:13px;padding:3px;width:90px")."&nbsp;MB</td>
			<td valign='top'>&nbsp;</td>
		</tr>
		<tr>
			<td valign='top' class=legend style='font-size:13px'>{OnlyNotify}:</td>
			<td valign='top' width=1% align=left nowrap>". Field_checkbox("OnlyNotify",1,$OnlyNotify)."</td>
			<td width=1%>" . help_icon("{quotas_policies_emailOnlyNotify_explain}")."</td>
		</tr>
		<tr>
			<td colspan=3 align='right'><hr>". button("{apply}","SaveSenderPolicy()")."</td>
		</table>
		</div>
	<script>
	
var x_SaveSenderPolicy= function (obj) {
	var results=obj.responseText;
	if (results.length>0){alert(results);}
	document.getElementById('maxquotasent').innerHTML='';
	YahooWin4Hide();
	}	
	
function SaveSenderPolicy(){
	var XHR = new XHRConnection();
	XHR.appendData('MaxMailsDay',document.getElementById('MaxMailsDay').value);
	XHR.appendData('MaxMailDaySize',document.getElementById('MaxMailDaySize').value);
	if(document.getElementById('OnlyNotify').checked){XHR.appendData('OnlyNotify',1);}else{XHR.appendData('OnlyNotify',0);}
	XHR.appendData('uid','{$_GET["uid"]}');	
	document.getElementById('maxquotasent').innerHTML='<center><img src=img/wait_verybig.gif></center>';
	XHR.sendAndLoad('$page', 'GET',x_SaveSenderPolicy);	
	}
</script>	
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}

function save(){
	$sql="SELECT uid FROM postfix_sender_quotas WHERE uid='{$_GET["uid"]}'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
	if($ligne["uid"]==null){
		$sql="INSERT INTO postfix_sender_quotas (`uid`,`MaxMailsDay`,`MaxMailDaySize`,`OnlyNotify`) VALUES (
		'{$_GET["uid"]}','{$_GET["MaxMailsDay"]}','{$_GET["MaxMailDaySize"]}','{$_GET["OnlyNotify"]}');";
	}else{
		$sql="UPDATE postfix_sender_quotas SET 
			`MaxMailsDay`='{$_GET["MaxMailsDay"]}',
			`MaxMailDaySize`='{$_GET["MaxMailDaySize"]}',
			`OnlyNotify`='{$_GET["OnlyNotify"]}'
			WHERE uid='{$_GET["uid"]}'";
	}
	
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?artica-policy-reload=yes");
	
}
