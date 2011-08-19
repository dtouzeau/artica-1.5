<?php
if(isset($_GET["verbose"])){$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}
include_once('ressources/class.templates.inc');
session_start();
include_once('ressources/class.html.pages.inc');
include_once('ressources/class.cyrus.inc');
include_once('ressources/class.main_cf.inc');
include_once('ressources/charts.php');
include_once('ressources/class.syslogs.inc');
include_once('ressources/class.system.network.inc');
include_once('ressources/class.os.system.inc');

if(isset($_POST["DisablePurchaseInfo"])){Save();exit;}
if(isset($_GET["popup"])){popup();exit;}
js();


function Save(){
	$sock=new sockets();
	$sock->SET_INFO("DisablePurchaseInfo", $_POST["DisablePurchaseInfo"]);
	
}

function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{ARTICA_P_SUPPORT}");
	$html="RTMMail('550','$page?popup=yes','$title');";
	echo $html;
	
	
	
}

function popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();
	$supportlink="http://www.artica-technology.fr/english/index.php/sylvain/72-solutions-pricing/120-artica-small-business-pricing";
	$html="<table style='width:100%'>
	<tbody>
	<tr>
		<td valign='top' width=1%><img src='img/technical-support-128.png'></td>
		<td valign='top' width=100%><div style='font-size:14px' class=explain>{ARTICA_P_SUPPORT_TEXT}</div>
		<center style='margin-top:15px;margin-bottom:15px'>
			<a href=\"javascript:blur();\" 
			OnClick=\"javascript:s_PopUp('$supportlink',990,900,'');\"
			style='font-size:18px;text-decoration:underline'>{ARTICA_P_SUPPORT} {link}</a>
		</center>
		<div style='text-align:right'>
			<table class=form style='float:right'>
				<tbody>
					<tr>
						<td class=legend>{hide_this_information}:</td>
						<td>". Field_checkbox("DisablePurchaseInfo", 1,$sock->GET_INFO("DisablePurchaseInfo"),"DisablePurchaseInfoCheck()")."</td>
					</tr>
				</tbody>
			</table>
		</div>
		</td>
	</tr>
	</tbody>
	</table>
<script>
		var X_DisablePurchaseInfoCheck= function (obj) {
			var results=obj.responseText;
			if(document.getElementById('admin_perso_tabs')){RefreshTab('admin_perso_tabs');}		
			}
		
		function DisablePurchaseInfoCheck(){
			var XHR = new XHRConnection();
			if(document.getElementById('DisablePurchaseInfo').checked){
				XHR.appendData('DisablePurchaseInfo',1);
			}else{
				XHR.appendData('DisablePurchaseInfo',0);
			}
			
			
			XHR.sendAndLoad('$page', 'POST',X_DisablePurchaseInfoCheck);
			
		}
	</script>
	
	
	";
	
echo $tpl->_ENGINE_parse_body($html);
	
	
	
}


