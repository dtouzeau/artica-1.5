<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.main_cf.inc');
	
	
$usersmenus=new usersMenus();
if($usersmenus->AsPostfixAdministrator==false){header('location:users.index.php');exit;}
if(isset($_GET["KavUpdates"])){KavUpdates();exit;}


$page=PageAveServerUpdateConfig();
$JS["JS"][]="js/kas.js";
$tpl=new template_users('Kaspersky Antivirus {product_update_settings}',$page,0,0,0,0,$JS);

echo $tpl->web_page;


function PageAveServerUpdateConfig(){
		$yum=new usersMenus();
		$tpl=new templates();
		if($yum->AsPostfixAdministrator==false){return $tpl->_ENGINE_parse_body("<h3>{not allowed}</H3>");}
		include_once(dirname(__FILE__) . '/ressources/kav4mailservers.inc');
		$kav=new kav4mailservers(1);
		$array_conf=$kav->array_conf["updater.options"];
		$cron=$kav->CronTask();
		$UseUpdateServerUrl=Field_yesno_checkbox_img('UseUpdateServerUrl',$array_conf["UseUpdateServerUrl"]);
		$UseUpdateServerUrlOnly=Field_yesno_checkbox_img('UseUpdateServerUrlOnly',$array_conf["UseUpdateServerUrlOnly"]);
		$PassiveFtp=Field_yesno_checkbox_img('PassiveFtp',$array_conf["PassiveFtp"]);
		$html="
		<table style='width:100%'>
		<tr>
		<td valign='top'><img src='img/bg_download.jpg'></td>
		<td valign='top'>
		" . RoundedLightGreen("<strong>{schedule}:&laquo; $cron &raquo;</strong>")."<br>" . 
		
		RoundedLightGrey("<form name=ffmupdate>
		<input type='hidden' value='{$array_conf["PostUpdateCmd"]}' name='PostUpdateCmd'>
		<H4>{keepup2date settings}</H4>
		<table>
		<tr>
		<td>$UseUpdateServerUrl</td>
		<td align='left' nowrap>{UseUpdateServerUrl}</td>
		
		</tr>
		<tr>
		<td>$UseUpdateServerUrlOnly</td>
		<td align='left'>{UseUpdateServerUrlOnly}</td>
		</tr>
		<tr>
		<td>$PassiveFtp</td>
		<td align='left'>{PassiveFtp}</td>
		</tr>			
		</table>
		<table>
		<tr>
		<td align='right' nowrap>{UpdateServerUrl}:</td>
		<td align='left'>" . Field_text('UpdateServerUrl',$array_conf["UpdateServerUrl"])."</td>
		</tr>	
		<tr>
		<td align='right'>{ProxyAddress}:</td>
		<td align='left'>" . Field_text('ProxyAddress',$array_conf["ProxyAddress"])."</td>
		</tr>	
		<tr>
		<td align='right'>{ConnectTimeout}:</td>
		<td align='left'>" . Field_text('ConnectTimeout',$array_conf["ConnectTimeout"],'')."</td>
		</tr>	
		<tr>
		<td colspan=2 align='right'><input type='button' OnClick=\"javascript:ParseForm('ffmupdate','users.kav.php',true);\" value='{submit}&nbsp;&raquo;'></td>
		</tr>
		</table>
		</form>")."<br>

		
			
		<br>
		</td>
		</tr>
		<tr><td align='center' colspan=2><input type='button' OnClick=\"javascript:KavUpdates()\" value='&laquo;&nbsp;{update_now}&nbsp;&raquo;'></td></tr>
		<tr><td colspan=2>"  . UpdatesError().UpdatesSuccess()."</td>
		</tr>
		</table>
		" ;

		return $tpl->_ENGINE_parse_body($html);
	}
	
function UpdatesError(){
	$kav=new kav4mailservers(1);
	$updates_error=$kav->UpdatesError();
		if(is_array($updates_error)){
			$html=$html."<H5>{updates_error}</H5>
			<center>
			<table style='width:450px'>
			<tr style='background-color:#005447;'>
			<td>&nbsp;</td>
			<td style='color:white;font-weight:bold' width=5%>{date}</td>
			<td style='color:white;font-weight:bold'>{details}</td>
			</tr>";
			
			while (list ($key, $val) = each ($updates_error) ){
				
				if($count>5){break;}
				if(preg_match('#\[([0-9\-:\s\/]+).+?\]#',$val,$reg)){
					$val=str_replace($reg[0],'',$val);
					$count=$count+1;
				
				$html=$html . "<tr>
				<td width=1% class='bottom'><img src='img/status_warning.jpg'></td>
				<td class='bottom' nowrap><div style='color:red;font-size:11px;padding:3px;'>{$reg[1]}</div></td>
				<td class='bottom'><div style='color:red;font-size:11px;padding:3px;'>$val</div></td>
				</tR>";
				}
				
			}
			$html=$html . "</table></center>";
		}
		return $html;	
	
}
function UpdatesSuccess(){
	$kav=new kav4mailservers(1);
	$updates_success=$kav->UpdatesSuccess();
	
if(is_array($updates_success)){
			$html=$html."<H5>{updates_success}</H5>
			<center>
			<table style='width:450px'>
			<tr style='background-color:#005447;'>
			<td>&nbsp;</td>
			<td style='color:white;font-weight:bold'  width=5%>{date}</td>
			<td style='color:white;font-weight:bold'>{details}</td>
			</tr>";
			
			
			while (list ($key, $val) = each ($updates_success) ){
				if($count>5){break;}
				if(preg_match('#\[([0-9\-:\s\/]+).+?\]#',$val,$reg)){
					$val=str_replace($reg[0],'',$val);
					$count=$count+1;
				$html=$html . "<tr>
				<td width=1% class='bottom'><img src='img/status_ok.jpg'></td>
				<td class='bottom' nowrap><div style='color:blue;font-size:11px;padding:3px;'>{$reg[1]}</div></td>
				<td class='bottom'><div style='color:blue;font-size:11px;padding:3px;'>$val</div></td>
				</tR>";
				}
				
			}
			$html=$html . "</table></center>";
		}		
	;	

		return $html;
	}	
function KavUpdates(){
	$sock=new sockets();
	$sock->getfile('aveserver_perform_udpates');
	$tpl=new templates();
	$tpl->_ENGINE_parse_body('{success}');
	
}
