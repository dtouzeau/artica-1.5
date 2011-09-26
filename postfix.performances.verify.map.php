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
if($user->AsPostfixAdministrator==false){header('location:logon.php');}
if(isset($_GET["with-tabs"])){echo withtabs();exit;}
if(isset($_POST["address_verify_map"])){PostFixVerifyDatabaseSave();exit;}
if(isset($_GET["popup-index"])){popup_index();exit;}
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
$title=$tpl->_ENGINE_parse_body('{address_verify_map}');
	
	$users=new usersMenus();
	if(!$users->AsPostfixAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}

	
if(!isset($_GET["hostname"])){$_GET["hostname"]="master";}

$html="
$js

function {$prefix}Loadpage(){
	YahooWin2('650','$page?popup-index=yes&hostname={$_GET["hostname"]}','$title');
	}

	
 {$prefix}Loadpage();
";	
 
 echo $html;
	
}

function popup_index(){
$tpl=new templates();
$page=CurrentPageName();	
$main=new maincf_multi($_GET["hostname"]);


$address_verify_map=$main->GET("address_verify_map");
$address_verify_negative_cache=$main->GET("address_verify_negative_cache");
$address_verify_poll_count=$main->GET("address_verify_poll_count");
$address_verify_poll_delay=$main->GET("address_verify_poll_delay");
$address_verify_sender=$main->GET("address_verify_sender");
$address_verify_negative_expire_time=$main->GET("address_verify_negative_expire_time");
$address_verify_negative_refresh_time=$main->GET("address_verify_negative_refresh_time");
$address_verify_positive_expire_time=$main->GET("address_verify_positive_expire_time");
$address_verify_positive_refresh_time=$main->GET("address_verify_positive_refresh_time");

if($address_verify_map==null){
	if($_GET["hostname"]=="master"){
		$address_verify_map="btree:/var/lib/postfix/verify";
	}else{
		$address_verify_map="btree:/var/lib/postfix-{$_GET["hostname"]}/verify";
	}
}

if(!is_numeric($address_verify_negative_cache)){$address_verify_negative_cache=1;}
if(!is_numeric($address_verify_poll_count)){$address_verify_poll_count=3;}
if($address_verify_poll_delay==null){$address_verify_poll_delay="3s";}
if($address_verify_sender==null){$address_verify_sender="double-bounce";}
if($address_verify_negative_expire_time==null){$address_verify_negative_expire_time="3d";}
if($address_verify_negative_refresh_time==null){$address_verify_negative_refresh_time="3h";}
if($address_verify_positive_expire_time==null){$address_verify_positive_expire_time="31d";}
if($address_verify_positive_refresh_time==null){$address_verify_positive_refresh_time="7d";}



$html="
<span id='PostFixVerifyDatabaseSaveDiv'></span>
<div style='font-size:16px'>{address_verify_map_minitext}</div><div class=explain>{address_verify_map_text}</div><br>

		<table style='width:100%;margin:10px;' class='form'>
		<tbody>
			<tr>
				<td nowrap class=legend>{address_verify_map_field}:</strong></td>
				<td>" . Field_text('address_verify_map',$address_verify_map,"font-size:14px;width:220px") . "</td>
			</tr>
			<tr>
				<td nowrap class=legend>{address_verify_negative_cache}:</strong></td>
				<td>" . Field_checkbox('address_verify_negative_cache',1,$address_verify_negative_cache,'{address_verify_negative_cache_text}') . "</td>
			</tr>	
			<tr>
				<td nowrap class=legend>{address_verify_poll_count}:</strong></td>
				<td>" . Field_text('address_verify_poll_count',$address_verify_poll_count,'width:60px;font-size:14px',null,null,'{address_verify_poll_count_text}') . "</td>
			</tr>
			<tr>
				<td nowrap class=legend>{address_verify_poll_delay}:</strong></td>
				<td>" . Field_text('address_verify_poll_delay',$address_verify_poll_delay,'width:60px;font-size:14px',null,null,'{address_verify_poll_delay_text}') . "</td>
			</tr>									
			<tr>
				<td nowrap class=legend>{address_verify_sender}:</strong></td>
				<td>" . Field_text('address_verify_sender',$address_verify_sender,'width:160px;font-size:14px',null,null,'{address_verify_sender_text}') . "</td>
			</tr>			
			<tr>
				<td align='right' nowrap class=legend>{address_verify_negative_expire_time}:</strong></td>
				<td>" . Field_text('address_verify_negative_expire_time',$address_verify_negative_expire_time,'width:60px;font-size:14px',null,null,'{address_verify_negative_expire_time_text}') . "</td>
			</tr>
			<tr>
				<td align='right' nowrap class=legend>{address_verify_negative_refresh_time}:</strong></td>
				<td>" . Field_text('address_verify_negative_refresh_time',$address_verify_negative_refresh_time,'width:60px;font-size:14px',null,null,'{address_verify_negative_refresh_time_text}') . "</td>
			</tr>
			<tr>
				<td align='right' nowrap class=legend>{address_verify_positive_expire_time}:</strong></td>
				<td>" . Field_text('address_verify_positive_expire_time',$address_verify_positive_expire_time,'width:60px;font-size:14px',null,null,'{address_verify_positive_expire_time_text}') . "</td>
			</tr>
			<tr>
				<td align='right' nowrap class=legend>{address_verify_positive_refresh_time}:</strong></td>
				<td>" . Field_text('address_verify_positive_refresh_time',$address_verify_positive_refresh_time,'width:60px;font-size:14px',null,null,'{address_verify_positive_refresh_time_text}') . "</td>
			</tr>									
			<tr>
				<td align='right' colspan=2>
				<hr>
				". button("{apply}","PostFixVerifyDatabaseSave()")."
				</td>
				
			</tr>
			</tbody>				
		</table>
	<script>
	
	var x_PostFixVerifyDatabaseSave=function(obj){
    	var tempvalue=trim(obj.responseText);
	  	if(tempvalue.length>3){alert(tempvalue);}
		document.getElementById('PostFixVerifyDatabaseSaveDiv').innerHTML='';
		}	
	
		function PostFixVerifyDatabaseSave(){
			var XHR = new XHRConnection();	
			if(document.getElementById('address_verify_negative_cache').checked){XHR.appendData('address_verify_negative_cache','1');}else{XHR.appendData('address_verify_negative_cache','0');}
			XHR.appendData('address_verify_map',document.getElementById('address_verify_map').value);
			XHR.appendData('address_verify_poll_count',document.getElementById('address_verify_poll_count').value);
			XHR.appendData('address_verify_poll_delay',document.getElementById('address_verify_poll_delay').value);
			XHR.appendData('address_verify_sender',document.getElementById('address_verify_sender').value);
			XHR.appendData('address_verify_negative_expire_time',document.getElementById('address_verify_negative_expire_time').value);
			XHR.appendData('address_verify_negative_refresh_time',document.getElementById('address_verify_negative_refresh_time').value);
			XHR.appendData('address_verify_positive_expire_time',document.getElementById('address_verify_positive_expire_time').value);
			XHR.appendData('address_verify_positive_refresh_time',document.getElementById('address_verify_positive_refresh_time').value);
			XHR.appendData('hostname','{$_GET["hostname"]}');
			AnimateDiv('PostFixVerifyDatabaseSaveDiv');
			XHR.sendAndLoad('$page', 'POST',x_PostFixVerifyDatabaseSave);				
		
		}
	</script>		
		";


$tpl=new Templates();
echo $tpl->_ENGINE_parse_body($html);
}

function PostFixVerifyDatabaseSave(){
	$hostname=$_POST["hostname"];
	unset($_POST["hostname"]);
	$main=new maincf_multi($hostname);
	while (list ($num, $ligne) = each ($_POST) ){
		$main->SET_VALUE($num, $ligne);
		
	}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-others-values=yes&hostname={$_POST["hostname"]}");		
	
	
}



?>