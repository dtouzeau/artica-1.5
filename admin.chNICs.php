<?php
if(isset($_GET["verbose"])){$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}
include_once('ressources/class.templates.inc');
include_once('ressources/class.html.pages.inc');
include_once('ressources/class.system.network.inc');
include_once('ressources/class.os.system.inc');
include_once('ressources/class.system.nics.inc');

$users=new usersMenus();
if(!$users->AsSystemAdministrator){die();}

if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["wizard-nic-list"])){wizard_list_nic();exit;}
if(isset($_POST["ApplyNetSettingsWizard"])){ApplyNetSettingsWizard();exit;}
if(isset($_POST["WizardNetLeaveUnconfigured"])){WizardNetLeaveUnconfiguredSave();exit;}
js();


function js(){
	$tpl=new templates();
	$page=CurrentPageName();
	
	$users=new usersMenus();
	$sock=new sockets();
	$title=$tpl->_ENGINE_parse_body("{network_settings}");
	$html="YahooWinBrowse(650,'$page?popup=yes','$title')";
	echo $html;
	
}

function popup(){
	
	$tpl=new templates();
	$page=CurrentPageName();
	$sock=new sockets();
	$WizardNetLeaveUnconfigured=$sock->GET_INFO("WizardNetLeaveUnconfigured");
	
	$html="
	<div class=explain style='font-size:14px'>{FIRST_WIZARD_NIC1}</div>
	<div style='text-align:right;width:100%'>
		<table class=form width=5%>
			<tbody>
				<tr>
					<td class=legend nowrap>{i_prefer_leave_unconfigured}:</td>
					<td>". Field_checkbox("WizardNetLeaveUnconfigured", 1,$WizardNetLeaveUnconfigured,"WizardNetLeaveUnconfiguredSave()")."</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div id='wizard-nic-list'></div>
	
	<script>
		function WizardRefreshNics(){
			LoadAjax('wizard-nic-list','$page?wizard-nic-list=yes');
			}
			
	var x_WizardNetLeaveUnconfiguredSave= function (obj) {
		var results=obj.responseText;
		if(results.length>3){alert(results);}
		YahooWinBrowseHide();
	}	
	
	function WizardNetLeaveUnconfiguredSave(){
		var XHR = new XHRConnection();
		AnimateDiv('wizard-nic-list');
		if(document.getElementById('WizardNetLeaveUnconfigured').checked){
		XHR.appendData('WizardNetLeaveUnconfigured','1');}else{XHR.appendData('WizardNetLeaveUnconfigured','0');}
		XHR.sendAndLoad('$page', 'POST',x_WizardNetLeaveUnconfiguredSave);	
	
	}			
			
			
	WizardRefreshNics();
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
}

function WizardNetLeaveUnconfiguredSave(){
	$sock=new sockets();
	$sock->SET_INFO("WizardNetLeaveUnconfigured", $_POST["WizardNetLeaveUnconfigured"]);
	
}


function wizard_list_nic(){
	$tpl=new templates();
	$page=CurrentPageName();
	$sock=new sockets();	
	$datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?list-nics=yes")));
	$refresh=imgtootltip("refresh-24.png","{refresh}","WizardRefreshNics()");

	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th colspan=2 align='center' style='text-align:center'>$refresh</th>
		<th>{tcp_address}</td>
		<th>{netmask}</th>
		<th>{gateway}</th>
		<th>{mac_addr}</th>
		<th>DHCP</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	$configured=true;
	while (list ($num, $val) = each ($datas) ){
		if(trim($val)==null){continue;}
		writelogs("Found: $val",__FUNCTION__,__FILE__,__LINE__);
		$val=trim($val);
		
		if(preg_match('#master#',$val)){continue;}
		if(preg_match("#^veth.+?#",$val)){continue;}
		if(preg_match("#^tunl[0-9]+#",$val)){continue;}
		if(preg_match("#^dummy[0-9]+#",$val)){continue;}
		if(preg_match("#^gre[0-9]+#",$val)){continue;}
		if(preg_match("#^ip6tnl[0-9]+#",$val)){continue;}
		if(preg_match("#^sit[0-9]+#",$val)){continue;}
		if(preg_match("#^vlan[0-9]+#",$val)){continue;}
		$nicinfos=$sock->getFrameWork("cmd.php?nicstatus=$val");
		$tbl=explode(";",$nicinfos);
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		if($tbl[0]==null){$tbl[0]="{not_set}";}
		if($tbl[1]==null){$tbl[1]="{not_set}";}
		if($tbl[2]==null){$tbl[2]="{not_set}";}
		if($tbl[4]==null){$tbl[4]="&nbsp;";}
		$js="javascript:Loadjs('system.nic.edit.php?nic=$val&button=confirm&noreboot=noreboot')";
		$href="<a href=\"javascript:blur();\" OnClick=\"$js\" style='font-weight:bold;font-size:14px;text-decoration:underline'>";
		$nic=new system_nic($val);
		
		
		if(!$nic->IsConfigured()){
			$configured=false;
			$img=imgtootltip("warning-panneau-32.png","<b>$val:{$tbl[0]}</b><hr>{this_nic_is_not_configured_text}");
		}else{
			$img=imgtootltip("check-32.png","<b>$val:{$tbl[0]}</b><hr>{this_nic_is_configured_text}");
		}
		
		if($nic->dhcp==1){
			$dhcp="{yes}";
			if(!preg_match("#[0-9\.]+#", $tbl[0])){$tbl[0]="{automatic}";}
			$tbl[2]="{automatic}";
			$tbl[4]="{automatic}";
			
		}else{$dhcp="{no}";}
		
		$html=$html."
		<tr class=$classtr>
			<td style='font-weight:bold;font-size:14px' width=1%>$href$val</a></td>
			<td style='font-weight:bold;font-size:14px' width=1%>$img</a></td>
			<td style='font-weight:bold;font-size:14px'>$href{$tbl[0]}</a></td>
			<td style='font-weight:bold;font-size:14px'>$href{$tbl[2]}</a></td>
			<td style='font-weight:bold;font-size:14px'>$href{$tbl[4]}</a></td>
			<td style='font-weight:bold;font-size:14px'>$href{$tbl[1]}</a></td>
			<td style='font-weight:bold;font-size:14px'>$href{$dhcp}</a></td>
		</tr>			
		";
		
		
	}
	
	
	$html=$html."</tbody>
	</table>
	
	<script>
	
	var x_ApplyNetSettingsWizard= function (obj) {
		var results=obj.responseText;
		if(results.length>3){alert(results);}
		WizardRefreshNics();
	}	
	
	function ApplyNetSettingsWizard(){
		var XHR = new XHRConnection();
		AnimateDiv('wizard-nic-list');
		XHR.appendData('ApplyNetSettingsWizard','1');
		XHR.sendAndLoad('$page', 'POST',x_ApplyNetSettingsWizard);	
	
	}
	</script>
	";
	
	if($configured){
		$html=$html."<div class=explain style='font-size:14px'>{FIRST_WIZARD_NIC2}</div>
		<center>". button("{SaveToDisk}","ApplyNetSettingsWizard()",18)."</center>
		
		";
	}
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}
function ApplyNetSettingsWizard(){
	$sock=new sockets();
	$sock->getFrameWork("network.php?reconstruct-all-interfaces=yes");
	$tpl=new templates();
	echo $tpl->javascript_parse_text("{reboot_computer_to_take_effects}");
}



//du -cshB K /var/*
