<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.main_cf.inc');
	include_once('ressources/class.fetchmail.inc');
	
	
$usersmenus=new usersMenus();
if($usersmenus->AsPostfixAdministrator==false){header('location:users.index.php');exit;}

if(isset($_GET["FetchmailPoolingTime"])){section_fetchmail_daemon_save();exit;}
if(isset($_GET["ajax"])){popup();exit;}

section_Fetchmail_Daemon();



function popup(){
	
	$page=CurrentPageName();
	$sock=new sockets();
	$FetchMailGLobalDropDelivered=$sock->GET_INFO("FetchMailGLobalDropDelivered");
	if(!is_numeric($FetchMailGLobalDropDelivered)){$FetchMailGLobalDropDelivered=0;}
	$yum=new usersMenus();
	for($i=1;$i<60;$i++){
		$hash[$i*60]=$i;
		
		
	}
	$fetch=new fetchmail();
	$list=Field_array_Hash($hash,'FetchmailPoolingTime',$fetch->FetchmailPoolingTime,null,null,null,'width:90px;font-size:14px');
	
$fetchmail_daemon="
					<div id='fetchdaemondiv'>
					<center>
					<table style='width:80%' class=form>
					<tbody>
					<tr>
						<td align='right' nowrap class=legend nowrap><strong style='font-size:14px' >{daemon} {pool} </strong></td>
						<td align='left' style='font-size:14px'>$list&nbsp;(minutes)</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td align='right' class=legend><strong style='font-size:14px' nowrap>{postmaster}</strong></td>
						<td align='left'>" . Field_text('FetchmailDaemonPostmaster',$fetch->FetchmailDaemonPostmaster,"font-size:14px;padding:3px") . "</td>
						<td>&nbsp;</td>
					</tr>	
					<tr>
						<td class=legend><strong style='font-size:14px' >{dropdelivered}:</strong></td>
						<td>". Field_checkbox("FetchMailGLobalDropDelivered", 1,$FetchMailGLobalDropDelivered)."</td>
						<td>". help_icon("{dropdelivered_explain}")."</td>
					</tr>
					
					<tr>
					<td colspan=2 align='right'>
					<hr>
						". button("{edit}","SaveFetchMailDaemon()")."
	

					</td>
					</tr>	
				</tbody>
				</table>
				</center>
				</div>";
		

		$title="{fetchmail}";
		
		
		
		$html="
						$fetchmail_daemon
		<center><img src='img/bg_fetchmail.png'></center>
		<script>
		var x_SaveFetchMailDaemon= function (obj) {
				var results=obj.responseText;
				if(results.length>0){alert(results);}
				YahooWinHide();
				}	
		
		
		function SaveFetchMailDaemon(){
			var XHR = new XHRConnection();		
			XHR.appendData('FetchmailDaemonPostmaster',document.getElementById('FetchmailDaemonPostmaster').value);
			XHR.appendData('FetchmailPoolingTime',document.getElementById('FetchmailPoolingTime').value);
			if(document.getElementById('FetchMailGLobalDropDelivered').checked){XHR.appendData('FetchMailGLobalDropDelivered',1);}else{XHR.appendData('FetchMailGLobalDropDelivered',0);}
			AnimateDiv('fetchdaemondiv');
			XHR.sendAndLoad('$page', 'GET',x_SaveFetchMailDaemon);			
			
			}
		</script>
				";
		
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
	
	
}


function section_Fetchmail_Daemon(){
	$page=CurrentPageName();
	$yum=new usersMenus();
	for($i=1;$i<60;$i++){
		$hash[$i*60]=$i;
		
		
	}
	$fetch=new fetchmail();
	$list=Field_array_Hash($hash,'FetchmailPoolingTime',$fetch->FetchmailPoolingTime,null,null,null,'width:90px');
	
$fetchmail_daemon=RoundedLightGrey("
		<form name=ffmFetch>
					<center>
					
					<table>
					<tr>
						<td align='right' nowrap><strong>{fetch_messages_every} </strong></td>
						<td align='left'>$list  (minutes)</td>
					</tr>
					<tr>
						<td align='right'><strong>{postmaster}</strong></td>
						<td align='left'>" . Field_text('FetchmailDaemonPostmaster',$fetch->FetchmailDaemonPostmaster) . "</td>
					</tr>	
					<tr>
					<td colspan=2 align='right'><input type='button' value='{submit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffmFetch','$page',true);\"></td>
					</tr>	
				</table>
				</form>
			</center>");
		

		$title="{fetchmail}";
		
		$status=fetchmail_status();
		
		$html="<table style='width:600px'>
		<tr>
		<td valign='top'><img src='img/bg_fetchmail.jpg'>
		<td valign='top'>$status</td>
		</tr>
		<td colspan=2>
				<table style='width:100%'>
				<tr>
				<td valign='top' width=60%>
					<H5>{fetchmail_daemon_settings}</H5>
						$fetchmail_daemon
				</td>
				<td valing='top'>" . applysettings("fetch") . "
				
				</td>
				</tr>
				</table>
			</td>
			</tr>			
					</table>";
				
				
$tpl=new template_users($title,$html,0,0,0,0,$cfg);
echo $tpl->web_page;		
	
	}
	
function fetchmail_status(){
	$tpl=new templates();
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString($sock->getfile('fetchmailstatus'));
	$status=DAEMON_STATUS_ROUND("FETCHMAIL",$ini,null);
	return  $tpl->_ENGINE_parse_body($status);	
}
	
function section_fetchmail_daemon_save(){
	$sock=new sockets();
	$fetch=new fetchmail();
	$fetch->FetchmailDaemonPostmaster=$_GET["FetchmailDaemonPostmaster"];
	$fetch->FetchmailPoolingTime=$_GET["FetchmailPoolingTime"];
	$sock->SET_INFO("FetchMailGLobalDropDelivered", $_GET["FetchMailGLobalDropDelivered"]);
	echo $fetch->Save();
	
}
	