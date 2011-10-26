<?php
$GLOBALS["ICON_FAMILY"]="COMPUTERS";
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.tcpip.inc');
include_once(dirname(__FILE__).'/ressources/class.system.network.inc');
include_once(dirname(__FILE__).'/ressources/class.computers.inc');

if(posix_getuid()<>0){
	$users=new usersMenus();
	if((!$users->AsSambaAdministrator) OR (!$users->AsSystemAdministrator)){
		$tpl=new templates();
		$error=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}
}
if(isset($_GET["tabs"])){tabs();exit;}
if(isset($_GET["scan-nets-js"])){scan_net_js();exit;}
if($_GET["mode"]=="selection"){selection_js();exit;}
if(isset($_GET["networkslist"])){networkslist(0);exit;}
if(isset($_GET["ComputersAllowDHCPLeases"])){ComputersAllowDHCPLeasesSave();exit;}
if(isset($_GET["ComputersAllowNmap"])){ComputersAllowNmapSave();exit;}
if(isset($_GET["EnableArpDaemon"])){EnableArpDaemonSave();exit;}



if(isset($_GET["browse-computers"])){index();exit;}

if(isset($_GET["browse-networks"])){networks();exit;}
if(isset($_GET["browse-networks-add"])){networks_add();exit;}

if(isset($_GET["network-scanner-execute"])){network_scanner_execute();exit;}
if(isset($_GET["computer-refresh"])){echo computer_list();exit;}
if(isset($_GET["calc-cdir-ip"])){echo network_calc();exit;}
if(isset($_GET["calc-cdir-ip-add"])){echo networks_add_save();exit;}
if(isset($_GET["NetworkDelete"])){networks_del();exit;}
if(isset($_GET["DeleteAllcomputers"])){computers_delete();exit;}
if(isset($_GET["Status"])){Status();exit;}
if(isset($_GET["view-scan-logs"])){events();exit;}
if(isset($_GET["NetWorksDisable"])){networks_disable();exit;}
if(isset($_GET["NetWorksEnable"])){networks_enable();exit;}
if(isset($_GET["MenusRight"])){echo menus_right();exit;}



if(isset($_GET["artica-import-popup"])){artica_import_popup();exit;}
if(isset($_GET["artica-import-delete"])){artica_import_delete();exit;}

if(isset($_GET["artica-importlist-popup"])){artica_importlist_popup();exit;}


if(isset($_GET["artica_ip_addr"])){artica_import_save();exit;}
if(isset($_POST["popup_import_list"])){artica_importlist_perform();exit;}

if(isset($_GET["selection-computers"])){selection_popup();exit;}
if(isset($_GET["selection-list"])){selection_list();exit;}




if(posix_getuid()<>0){js();}



function ComputersAllowDHCPLeasesSave(){
	$sock=new sockets();
	$sock->SET_INFO("ComputersAllowDHCPLeases",$_GET["ComputersAllowDHCPLeases"]);
	
}
function ComputersAllowNmapSave(){
	$sock=new sockets();
	$sock->SET_INFO("ComputersAllowNmap",$_GET["ComputersAllowNmap"]);
	
}

function EnableArpDaemonSave(){
	$sock=new sockets();
	$sock->SET_INFO("EnableArpDaemon",$_GET["EnableArpDaemon"]);
	$sock->getFrameWork("cmd.php?restart-artica-status=yes");	
}

function scan_net_js(){
	$page=CurrentPageName();
	echo "var x_ScanNetwork = function (obj) {var tempvalue=obj.responseText;if(tempvalue.length>3){alert(tempvalue);}}

	function ScanNetwork(){
		var XHR = new XHRConnection();
		XHR.appendData('network-scanner-execute','yes');
        XHR.sendAndLoad('$page', 'GET',x_ScanNetwork);        
	
	}
	
	ScanNetwork();";
	
}

function tabs(){
	$page=CurrentPageName();
	$array["browse-computers"]='{parameters}';
	$array["search-computers"]='{search_computers}';
	
	if($_GET["OnlyOCS"]==1){unset($array["browse-computers"]);}
	if($_GET["OnlyOCS"]=="yes"){unset($array["browse-computers"]);}
	
	$tpl=new templates();

	while (list ($num, $ligne) = each ($array) ){
		if($num=="search-computers"){
			$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"ocs.search.php?start=yes&mode={$_GET["mode"]}&value={$_GET["value"]}&callback={$_GET["callback"]}\"><span>$ligne</span></a></li>\n");
			continue;
		}
		$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&mode={$_GET["mode"]}&value={$_GET["value"]}&callback={$_GET["callback"]}\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_browse_computers style='width:100%;height:650px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_browse_computers').tabs();
				});
		</script>";		

	
}


function networks_del(){
	$md5=$_GET["NetworkDelete"];
	
	$net=new networkscanner();
	print_r($net->networklist);
	while (list ($num, $maks) = each ($net->networklist)){
		$maks_md5=md5($maks);
		if($maks_md5==$md5){
			echo "OK $num-$maks";
			
			unset($net->networklist[$num]);
			$net->save();
			break;
		}
	}

	
}

function network_calc(){
	$ip_start=$_GET["calc-cdir-ip"];
	$mask=$_GET["calc-cdir-netmask"];
	$ip=new networking();
	echo $ip->route_shouldbe($ip_start,$mask);
	
}

function networks_add_save(){
	$net=new networkscanner();
	$netmask=$_GET["calc-cdir-ip-add"];
	if($netmask<>null){
		$net->networklist[]=$netmask;
		$net->save();
	}
	
}


function selection_js(){
	$users=new usersMenus();
	if(!$users->AsSambaAdministrator){die("alert('no privileges')");}
	
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{browse_computers}::{select}');
	$callback=$_GET["callback"];
	$html="
	function BrowseComputerSelection(){
		YahooLogWatcher(550,'$page?selection-computers=*&callback=$callback','$title');
	}
	
	BrowseComputerSelection();";
	
	
	echo $html;
	}
	
	
function selection_popup(){
	$page=CurrentPageName();
	$callback=$_GET["callback"];
	$html=Field_text("selection-search",$_GET["selection-computers"],"font-size:13px;padding:3px",null,null,null,false,"SelectionComputerPress(event)")."
	<div style='padding:3px;border:3px;height:300px;overflow:auto' id='selection-list'></div>
	
	
	
	<script>
	   function SelectionComputerPress(e){
	   		if(checkEnter(e)){SelectionComputer(document.getElementById('selection-search').value);}
		}
	
	
		function SelectionComputer(pattern){
			LoadAjax('selection-list','$page?selection-list='+pattern+'&callback=$callback');
		}
		
		SelectionComputer('*');
	</script>
	
	";
	
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function selection_list(){
	if($_GET["selection-list"]=='*'){$_GET["selection-list"]=null;}
	if($_GET["selection-list"]==null){$tofind="*";}else{$tofind="*{$_GET["selection-list"]}*";}
	$filter_search="(&(objectClass=ArticaComputerInfos)(|(cn=$tofind)(ComputerIP=$tofind)(uid=$tofind))(gecos=computer))";
	$ldap=new clladp();
	$attrs=array("uid","ComputerIP","ComputerOS","ComputerMachineType","ComputerMacAddress");
	$dn="$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter_search,$attrs,20);


$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='margin-top:8px'>
<thead class='thead'>
	<tr>
	<th colspan=4>$tofind&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";

for($i=0;$i<$hash["count"];$i++){
	$realuid=$hash[$i]["uid"][0];
	$hash[$i]["uid"][0]=str_replace('$','',$hash[$i]["uid"][0]);
	$js_show=MEMBER_JS($realuid,1);
	if($_GET["callback"]<>null){$js_selection="{$_GET["callback"]}('$realuid');";}
	

	
	$ip=$hash[$i][strtolower("ComputerIP")][0];
	$os=$hash[$i][strtolower("ComputerOS")][0];
	$type=$hash[$i][strtolower("ComputerMachineType")][0];
	$mac=$hash[$i][strtolower("ComputerMacAddress")][0];
	$name=$hash[$i]["uid"][0];
	if(strlen($name)>25){$name=substr($name,0,23)."...";}
	
	
	if($os=="Unknown"){if($type<>"Unknown"){$os=$type;}}
	
	if(strlen($os)>20){$os=texttooltip(substr($os,0,17).'...',$os,null,null,1);}
	if(strlen($ip)>20){$ip=texttooltip(substr($ip,0,17).'...',$ip,null,null,1);}
	if(!preg_match("#^[0-9]+\.[0-9]+\.[0-9]+#",$ip)){$ip=$ip="0.0.0.0";}
	$img=imgtootltip("computer-32.png","{view}",MEMBER_JS($realuid,1,1));
	
	$roolover=CellRollOver($js_selection,"{select}");
	
	
	if(!IsPhysicalAddress($mac)){
		$img=imgtootltip("computer-warning-32.png","{WARNING_MAC_ADDRESS_CORRUPT}");
		$roolover=CellRollOver($js_show,"{edit}<hr>{WARNING_MAC_ADDRESS_CORRUPT}");
		$js_selection=null;
	}
if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	$html=$html . 
	"<tr class=$classtr>
	<td width=1%>$img</td>
	<td nowrap $roolover><strong style='font-size:13px'>$name</strong></td>
	<td $roolover><strong style='font-size:13px'>$ip</strong></td>
	<td $roolover><strong style='font-size:13px'>$mac</strong></td>
	$js_add
	</tr>
	";
	}
$html=$html . "</tbody></table>";
$tpl=new templates();
echo  $tpl->_ENGINE_parse_body($html);
	
}



function js($nostartReturn=false){
	
	$users=new usersMenus();
	if(!$users->AsSambaAdministrator){die("alert('no privileges')");}
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{browse_computers}');
	$networks=$tpl->_ENGINE_parse_body('{edit_networks}');
	$delete_all_computers_warn=$tpl->javascript_parse_text('{delete_all_computers_warn}');
	$import_artica_computers=$tpl->_ENGINE_parse_body('{import_artica_computers}');
	$prefix=str_replace('.','_',$page);
	$prefix=str_replace('-','',$prefix);
	
	$start="browse_computers_start();";
	if(isset($_GET["in-front-ajax"])){$start="browse_computers_start_infront();";}
	if(isset($_GET["no-start-js"])){$start=null;}
	if($nostartReturn){$start=null;};
	$html="
	var rule_mem='';
	var {$prefix}timeout=0;
	var {$prefix}timerID  = null;
	var {$prefix}tant=0;
	var {$prefix}reste=0;	
	
	function browse_computers_start(){
		YahooLogWatcher(750,'$page?tabs=yes&mode={$_GET["mode"]}&value={$_GET["value"]}&callback={$_GET["callback"]}&OnlyOCS={$_GET["OnlyOCS"]}','$title');
		{$prefix}demarre();
	
	}
	
	function browse_computers_start_infront(){
	   $('#BodyContent').load('$page?tabs=yes&mode={$_GET["mode"]}&value={$_GET["value"]}&callback={$_GET["callback"]}&show-title=yes&OnlyOCS={$_GET["OnlyOCS"]}');
	   {$prefix}demarre();
	
	}	
	
	function {$prefix}demarre(){
		if(!YahooLogWatcherOpen()){return false;}
			{$prefix}tant = {$prefix}tant+1;
	
			if ({$prefix}tant <10 ) {                           
				{$prefix}timerID = setTimeout(\"{$prefix}demarre()\",1000);
		      } else {
						if(!YahooSetupControlOpen()){return false;}
						{$prefix}tant = 0;
						{$prefix}ChargeLogs();
						{$prefix}demarre();
		   }
	}
	
var x_{$prefix}ChargeLogs  = function (obj) {
		document.getElementById('progress-computers').innerHTML=obj.responseText;
	}	

	
	function {$prefix}ChargeLogs(){
		var XHR = new XHRConnection();
		XHR.appendData('Status','yes');
		XHR.sendAndLoad('$page', 'GET',x_{$prefix}ChargeLogs);  
	}
	
	

	
	function ViewNetwork(){
		YahooWin2(550,'$page?browse-networks=yes','$networks');
	}
	

	
	function ViewComputerScanLogs(){
		YahooWin3(550,'$page?view-scan-logs=yes','$networks');
	}
	

	
var x_ClacNetmaskcdir  = function (obj) {
		document.getElementById('netmaskcdir').value=obj.responseText;
	}	
	
	function ClacNetmaskcdir(){
		var XHR = new XHRConnection();
		XHR.appendData('calc-cdir-ip',document.getElementById('ip_addr').value);
		XHR.appendData('calc-cdir-netmask',document.getElementById('netmask').value);
		XHR.sendAndLoad('$page', 'GET',x_ClacNetmaskcdir);        
	}
	
	

	
	

	
	function BrowsComputersRefresh(){
		var mode='';
		var val='';
		if(document.getElementById('mode')){mode=document.getElementById('mode').value;}
		if(document.getElementById('value')){val=document.getElementById('value').value;}
		if(document.getElementById('callback')){callback=document.getElementById('callback').value;}
		LoadAjax('computerlist','$page?computer-refresh=yes&tofind='+document.getElementById('query_computer').value+'&mode={$_GET["mode"]}&{$_GET["value"]}&callback={$_GET["callback"]}');
	
	}
	
	var x_AddComputerToDansGuardian= function (obj) {
		var mid='ip_group_rule_list_'+rule_mem;
		LoadAjax(mid,'dansguardian.index.php?ip-group_list-rule='+rule_mem);
	}

	var x_DeleteAllComputers= function (obj) {
		var results=obj.responseText;
		alert(results);
		BrowsComputersRefresh();
	}
	
	function AddComputerToDansGuardian(uid,rule){
		var XHR = new XHRConnection();
		rule_mem=rule;
		XHR.appendData('AddComputerToDansGuardian',uid);
		XHR.appendData('AddComputerToDansGuardianRule',rule);
		XHR.sendAndLoad('dansguardian.index.php', 'GET',x_AddComputerToDansGuardian);
	}
	
	function BrowseComputerCheckRefresh(e){
		if(checkEnter(e)){BrowsComputersRefresh();}
	}
	function BrowseComputerFind(){BrowsComputersRefresh();}
	
	
	function DeleteAllComputers(){
		if(confirm('$delete_all_computers_warn')){
			var XHR = new XHRConnection();
			XHR.appendData('DeleteAllcomputers','yes');
			AnimateDiv('computerlist');
			XHR.sendAndLoad('$page', 'GET',x_DeleteAllComputers);  
		}
	}
	
var x_NetWorksDisable= function (obj) {
		if(document.getElementById('main_config_snort')){RefreshTab('main_config_snort');}
		ViewNetwork();
	}	
	
	function NetWorksDisable(mask){
			var XHR = new XHRConnection();
			XHR.appendData('NetWorksDisable',mask);
		 	document.getElementById('networks').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
			XHR.sendAndLoad('$page', 'GET',x_NetWorksDisable); 
	} 
	
	function NetWorksEnable(mask){
			var XHR = new XHRConnection();
			XHR.appendData('NetWorksEnable',mask);
		 	document.getElementById('networks').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
			XHR.sendAndLoad('$page', 'GET',x_NetWorksDisable); 
	} 	
	

	
	
  	
		
	
	
	function DeleteImportComputers(ip){
				var XHR = new XHRConnection();
				XHR.appendData('artica-import-delete',ip);
				XHR.sendAndLoad('$page', 'GET',x_SaveImportComputers); 
	}
$start
	";
	
	if($nostartReturn){return $html;}
	echo $html;
}




function index(){
	$tpl=new templates();
	if(isset($_GET["show-title"])){
		$title=$tpl->_ENGINE_parse_body('{browse_computers}')."::";
	}
	
	if($_GET["mode"]=="dansguardian-ip-group"){
		$title_add=" - {APP_DANSGUARDIAN}";
	}
	
	if($_GET["mode"]=="selection"){
		$title_add=" - {select}";
	}
	
	
	
	$menus_right=menus_right();
	$list=computer_list();
	$html="
	<div style='float:right'>" . imgtootltip('32-refresh.png','{refresh}','BrowsComputersRefresh()')."</div>
	<H3 style='border-bottom:1px solid #005447'>$title$title_add</H3>
	<table style='width:100%'>
	<tr>
		<td valign='top' width='90%'>
			<div style='width:100%;height:350px;overflow:auto' id='computerlist'>" . $list."</div>
			<div style='text-align:right;padding:4px;width:100%'><hr>" .button('{delete_all}',"DeleteAllComputers()")."</div>
		</td>
		<td valign='top'>
			$menus_right
		</td>
	</tr>
	</table>
	<div id='progress-computers'></div>";
	
	
	
	
	echo $tpl->_ENGINE_parse_body($html);	
}

function computers_delete(){
	$filter_search="(&(objectClass=ArticaComputerInfos)(|(cn=*)(ComputerIP=*)(uid=*))(gecos=computer))";
	$ldap=new clladp();
	$attrs=array("uid","dn","ComputerOS");
	$dn="$ldap->suffix";
	$hash=$ldap->Ldap_search($dn,$filter_search,$attrs);
	for($i=0;$i<$hash["count"];$i++){
		if($hash["$i"]["uid"][0]==null){continue;}
		$count=$count+1;
		$computer=new computers($hash["$i"]["uid"][0]);
		$computer->DeleteComputer();
	}
	
	$tpl=new templates();
	echo $tpl->javascript_parse_text("{success}: $count {computers}");
	
}

function computer_list(){
	$tofindorg=$_GET["tofind"];
	if($_GET["tofind"]=='*'){$_GET["tofind"]=null;}
	if($_GET["tofind"]==null){$tofind="*";}else{$tofind="*{$_GET["tofind"]}*";}
	$tofind=str_replace("**", "*", $tofind);
	$filter_search="(&(objectClass=ArticaComputerInfos)(|(cn=$tofind)(ComputerIP=$tofind)(uid=$tofind))(gecos=computer))";
	
$ldap=new clladp();
$attrs=array("uid","ComputerIP","ComputerOS","ComputerMachineType","ComputerMacAddress");
$dn="$ldap->suffix";
$hash=$ldap->Ldap_search($dn,$filter_search,$attrs,20);

if(IsPhysicalAddress($tofindorg)){
	$tofind=strtolower($tofindorg);
	$tofind=str_replace('-',":",$tofind);
	$patternMac="(&(objectclass=posixAccount)(ComputerMacAddress=$tofind))";
	$hash2=$ldap->Ldap_search($dn,$patternMac,$attrs,20);
}



$PowerDNS="<td width=1%><h3>&nbsp;|&nbsp;</h3></td><td><h3>". texttooltip('{APP_PDNS}','{APP_PDNS_TEXT}',"javascript:Loadjs('pdns.php')")."</h3></td>";

if($_GET["mode"]=="selection"){$PowerDNS=null;}

$html="

<input type='hidden' id='mode' value='{$_GET["mode"]}' name='mode'>
<input type='hidden' id='value' value='{$_GET["value"]}' name='value'>
<input type='hidden' id='callback' value='{$_GET["callback"]}' name='callback'>
<table style='width:100%'>
	<tr>
		<td width=1% nowrap><H3>{$hash["count"]} {computers}</H3></td>
		$PowerDNS
	</tr>
</table>
<table cellspacing='0' cellpadding='0' border='0' class='tableView'>
<thead class='thead'>
	<tr>
	<th colspan=4>$tofind</th>
	</tr>
</thead>
<tbody class='tbody'>";



for($i=0;$i<$hash["count"];$i++){
	$realuid=$hash[$i]["uid"][0];
	$hash[$i]["uid"][0]=str_replace('$','',$hash[$i]["uid"][0]);
	
	$js=MEMBER_JS($realuid,1);
	$Alreadyrealuid[$realuid]=true;
	if($_GET["mode"]=="dansguardian-ip-group"){$js_add="<td width=1%>" . imgtootltip('add-18.gif',"{add_computer}","AddComputerToDansGuardian('$realuid','{$_GET["value"]}')")."</td>";}
	if($_GET["mode"]=="selection"){$js="{$_GET["callback"]}('$realuid');";}
	$ip=$hash[$i][strtolower("ComputerIP")][0];
	$os=$hash[$i][strtolower("ComputerOS")][0];
	$type=$hash[$i][strtolower("ComputerMachineType")][0];
	$name=$hash[$i]["uid"][0];
	if(strlen($name)>25){$name=substr($name,0,23)."...";}
	if($os=="Unknown"){if($type<>"Unknown"){$os=$type;}}
	if(strlen($os)>20){$os=texttooltip(substr($os,0,17).'...',$os,null,null,1);}
	if(strlen($ip)>20){$ip=texttooltip(substr($ip,0,17).'...',$ip,null,null,1);}
	
	
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	$js=str_replace("javascript:",'',$js);
	$html=$html . 
	"<tr>
	<td width=1% class=$classtr><img src='img/computer-32.png'></td>
	<td $roolover nowrap><a href='#' OnClick=\"javascript:$js\" style='font-size:13px;text-decoration:underline'>$name</a></td>
	<td $roolover style='font-size:13px'>$ip</a></td>
	<td $roolover style='font-size:13px'>$os</a></td>
	$js_add
	</tr>
	";
	}
	
	if(is_array($hash2)){
		for($i=0;$i<$hash2["count"];$i++){
			$realuid=$hash2[$i]["uid"][0];
			if(isset($Alreadyrealuid[$realuid])){continue;}
			$hash2[$i]["uid"][0]=str_replace('$','',$hash2[$i]["uid"][0]);
			$js=MEMBER_JS($realuid,1);
			$Alreadyrealuid[$realuid]=true;
			if($_GET["mode"]=="dansguardian-ip-group"){$js_add="<td width=1%>" . imgtootltip('add-18.gif',"{add_computer}","AddComputerToDansGuardian('$realuid','{$_GET["value"]}')")."</td>";}
			if($_GET["mode"]=="selection"){$js="{$_GET["callback"]}('$realuid');";}
			$ip=$hash2[$i][strtolower("ComputerIP")][0];
			$os=$hash2[$i][strtolower("ComputerOS")][0];
			$type=$hash2[$i][strtolower("ComputerMachineType")][0];
			$name=$hash2[$i]["uid"][0];
			if(strlen($name)>25){$name=substr($name,0,23)."...";}
			if($os=="Unknown"){if($type<>"Unknown"){$os=$type;}}
			if(strlen($os)>20){$os=texttooltip(substr($os,0,17).'...',$os,null,null,1);}
			if(strlen($ip)>20){$ip=texttooltip(substr($ip,0,17).'...',$ip,null,null,1);}
			
			
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$js=str_replace("javascript:",'',$js);
			$html=$html . 
			"<tr>
			<td width=1% class=$classtr><img src='img/computer-32.png'></td>
			<td $roolover nowrap><a href='#' OnClick=\"javascript:$js\" style='font-size:13px;text-decoration:underline'>$name</a></td>
			<td $roolover style='font-size:13px'>$ip</a></td>
			<td $roolover style='font-size:13px'>$os</a></td>
			$js_add
			</tr>
			";	
		}		
		
	}
	
	
	
$html=$html . "</tbody></table>";
$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html);		
	
}


function menus_right(){
	$findcomputer=Paragraphe("64-samba-find.png","{scan_your_network}",'{scan_your_network_text}',"javascript:Loadjs('computer-browse.php?scan-nets-js=yes')","scan_your_network",210);
	$networs=Paragraphe("64-win-nic-loupe.png","{edit_networks}",'{edit_networks_text}',"javascript:ViewNetwork()","edit_networks",210);
	$add_computer_js="javascript:YahooUser(780,'domains.edit.user.php?userid=newcomputer$&ajaxmode=yes','New computer');";
	$add_computer=Paragraphe("64-add-computer.png","{ADD_COMPUTER}","{ADD_COMPUTER_TEXT}",$add_computer_js);
	$sock=new sockets();
	$ComputersAllowNmap=$sock->GET_INFO("ComputersAllowNmap");
	if($ComputersAllowNmap==null){$ComputersAllowNmap=1;}
	if($ComputersAllowNmap==0){
		$findcomputer=Paragraphe("64-samba-find-grey.png","{scan_your_network}",'{scan_your_network_text}',"","scan_your_network",210);
	}
	
	
	if($_GET["mode"]=="selection"){
		$networs=null;
		$findcomputer=null;
	}
	
	$users=new usersMenus();
	if(!$users->nmap_installed){
		$findcomputer=Paragraphe("64-samba-find-grey.png","{scan_your_network}",'{scan_your_network_text}',"","scan_your_network",210);
	}
	
	$html=
	"<table>
	<tr>
	<td>
		<table style='width:100%' class='table_form'>
		<tr>
		<td class=legend>{query}:</td>
		<td>" . Field_text('query_computer',null,'width:120xp',null,"BrowseComputerFind()",false,false,'BrowseComputerCheckRefresh(event)')."</td>
		</tr>
		<tr>
	
		</table>
		$findcomputer$networs$add_computer
	</td>
	</tr>
	</table>
	<script>". js(true)."</script>
	";
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);		
}

function events(){
	$tpl=new templates();
	
	echo $tpl->_ENGINE_parse_body("<H1>{events}</H1>");
	
	if(!is_file("ressources/logs/nmap.log")){
		echo $tpl->_ENGINE_parse_body("{error_no_datas}");
		return;
	}
	
	$datas=@file_get_contents("ressources/logs/nmap.log");
	$tpl=explode("\n",$datas);
	if(!is_array($tpl)){
		echo $tpl->_ENGINE_parse_body("{error_no_datas}");
		return;
	}
	
rsort($tpl);
	
while (list ($num, $line) = each ($tpl)){
		if(trim($line)==null){continue;}
		$html=$html . "<div><code style='font-size:10px'>$line</code></div>";
}		

$html="<div style='width:100%;height:230px;overflow:auto'>$html</div>";
	echo $html;
	
}

function networks(){
	$page=CurrentPageName();
	$tpl=new templates();
	$networsplus=Paragraphe("64-win-nic-plus.png","{add_network}",'{add_network_text}',"javascript:AddNetwork()","add_network",210);
	$importArtica=Paragraphe("64-samba-get.png","{import_artica_computers}",'{import_artica_computers_text}',"javascript:ImportComputers('')","import_artica_computers",210);
	$importList=Paragraphe("64-samba-get.png","{import_artica_computers}",'{import_artica_computers_list_text}',"javascript:ImportListComputers()","import_artica_computers_list_text",210);
	$networks=$tpl->_ENGINE_parse_body("{networks}");
	$import_artica_computers=$tpl->_ENGINE_parse_body("{import_artica_computers}");
	
	$sock=new sockets();
	$ComputersAllowNmap=$sock->GET_INFO("ComputersAllowNmap");
	$ComputersAllowDHCPLeases=$sock->GET_INFO("ComputersAllowDHCPLeases");
	$EnableArpDaemon=$sock->GET_INFO("EnableArpDaemon");
	
	if(!is_numeric($ComputersAllowNmap)){$ComputersAllowNmap=1;}
	if(!is_numeric($ComputersAllowDHCPLeases)){$ComputersAllowDHCPLeases=1;}
	if(!is_numeric($EnableArpDaemon)){$EnableArpDaemon=1;}
	
	$autoscan_form="
	<table style='width:100%'>
	<tr>
		<td valign='top' class=legend><a href=\"javascript:blur();\" OnClick=\"javascript:Loadjs('nmap.index.php');\" class=legend style='text-decoration:underline'>{allow_nmap_scanner}</a></td>
		<td valign='top'>". Field_checkbox("ComputersAllowNmap",1,$ComputersAllowNmap,"ComputersAllowNmapCheck()")."</td>
	</tr>
	<tr>
		<td valign='top' class=legend>{allow_parse_dhcp_leases}</td>
		<td valign='top'>". Field_checkbox("ComputersAllowDHCPLeases",1,$ComputersAllowDHCPLeases,"ComputersAllowDHCPLeasesCheck()")."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend>{EnableArpDaemon}</td>
		<td valign='top'>". Field_checkbox("EnableArpDaemon",1,$EnableArpDaemon,"EnableArpDaemonCheck()")."</td>
	</tr>	
	</table>
	
	";
	
	$users=new usersMenus();
	$articas=artica_import_list();
	$height_artica=200;
	if(strlen($articas)<5){$height_artica=0;}
	$NMAP_INSTALLED=1;
	$ARPD_INSTALLED=1;
	if(!$users->nmap_installed){$NMAP_INSTALLED=0;}
	if(!$users->ARPD_INSTALLED){$ARPD_INSTALLED=0;}
	
	$html="
	<div id='networks'>
	<table style='width:100%'>
	<tr>
		<td valign='top' width=70%>
			<div style='width:100%;height:265px;overflow:auto'>
				<div id='netlist'></div>
				
			</div>
			$autoscan_form
			<br>
			<div style='width:100%;height:{$height_artica}px;overflow:auto'>
				<div id='articas'>$articas</div>
			</div>	
		</td>
		<td valign='top'>
		$networsplus
		$importArtica
		$importList
		</td>
	</tr>
	</table>
	</div>
	
	<script>
		function AddNetwork(){
			YahooWin3(450,'$page?browse-networks-add=yes','$networks');
		}
		
		function ImportComputers(ip){
			YahooWin3('450','$page?artica-import-popup=yes&ip='+ip,'$import_artica_computers');
		
		}		
		
	function ImportListComputers(){
		YahooWin3('450','$page?artica-importlist-popup=yes','$import_artica_computers');
	
	}

		function EnableArpDaemonCheck(){
			var XHR = new XHRConnection();
			if(document.getElementById('EnableArpDaemon').checked){
			XHR.appendData('EnableArpDaemon',1);}else{XHR.appendData('EnableArpDaemon',0);}
			XHR.sendAndLoad('$page', 'GET'); 
		}	
	
	
		function ComputersAllowDHCPLeasesCheck(){
			var XHR = new XHRConnection();
			if(document.getElementById('ComputersAllowDHCPLeases').checked){
			XHR.appendData('ComputersAllowDHCPLeases',1);}else{XHR.appendData('ComputersAllowDHCPLeases',0);}
			XHR.sendAndLoad('$page', 'GET'); 
		}
		
		function ComputersAllowNmapCheck(){
			var XHR = new XHRConnection();
			if(document.getElementById('ComputersAllowNmap').checked){
			XHR.appendData('ComputersAllowNmap',1);}else{XHR.appendData('ComputersAllowNmap',0);}
			XHR.sendAndLoad('$page', 'GET')		
		}
		
		function RefreshNetworklist(){
			LoadAjax('netlist','$page?networkslist=yes');
		}
		
	function BrowsComputersRefresh(){
		var mode='';
		var val='';
		if(document.getElementById('mode')){mode=document.getElementById('mode').value;}
		if(document.getElementById('value')){val=document.getElementById('value').value;}
		if(document.getElementById('callback')){callback=document.getElementById('callback').value;}
		LoadAjax('computerlist','$page?computer-refresh=yes&tofind='+document.getElementById('query_computer').value+'&mode={$_GET["mode"]}&{$_GET["value"]}&callback={$_GET["callback"]}');
	
	}		
	
	function CheckNmap(){
		var NMAP_INSTALLED=$NMAP_INSTALLED;
		var ARPD_INSTALLED=$ARPD_INSTALLED;
		document.getElementById('ComputersAllowNmap').disabled=true;
		document.getElementById('EnableArpDaemon').disabled=true;
		if(NMAP_INSTALLED==1){document.getElementById('ComputersAllowNmap').disabled=false;}
		if(ARPD_INSTALLED==1){document.getElementById('EnableArpDaemon').disabled=false;}
	
	}
	CheckNmap();
	RefreshNetworklist();
	</script>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	}
	

	
	
function networks_disable(){
	$net=new networkscanner();
	$net->disable_net($_GET["NetWorksDisable"]);
}
function networks_enable(){
	$net=new networkscanner();
	$net->enable_net($_GET["NetWorksEnable"]);	
	
}
	
function networks_add(){
	$page=CurrentPageName();
	$tpl=new templates();
	$html="<span style='font-size:16px;margin:10px'>{add_network}</span>
	<div id='networks_add'>
		<table style='width:100%' class=form>
			<tr>
				<td class=legend>{ip_address}:</td>
				<td valign='top'>".field_ipv4("ip_addr",null,'font-size:14px',null,'ClacNetmaskcdir()',null,false,"ClacNetmaskcdir()")."</td>
			</tr>
				<td class=legend>{netmask}:</td>
				<td valign='top'>".field_ipv4("netmask","255.255.255.0",'font-size:14px',null,'ClacNetmaskcdir()',null,false,"ClacNetmaskcdir()")."</td>				
			</tr>
			
			
			<tr>
				<td class=legend>{cdir}:</td>
				<td valign='top' >
				". Field_text('netmaskcdir',null,'width:190px;padding:3px;font-size:16px')."
				</td>
				
				
			</tr>
			<TR>
				<td colspan=2 align='right'><hr>". button("{add}","AddNetworkPerform()")."</td>
			</tr>
		</table>
		</div>
		
<script>
var x_ClacNetmaskcdir  = function (obj) {
		document.getElementById('netmaskcdir').value=obj.responseText;
	}	
	
	function ClacNetmaskcdir(){
		var XHR = new XHRConnection();
		XHR.appendData('calc-cdir-ip',document.getElementById('ip_addr').value);
		XHR.appendData('calc-cdir-netmask',document.getElementById('netmask').value);
		XHR.sendAndLoad('$page', 'GET',x_ClacNetmaskcdir);        
	}
	
	var x_AddNetworkPerform= function (obj) {
		if(document.getElementById('main_config_snort')){RefreshTab('main_config_snort');}
		YahooWin3Hide();RefreshNetworklist();
	}	
	
	function AddNetworkPerform(){
		var XHR = new XHRConnection();
		var cdir=document.getElementById('netmaskcdir').value;
		if(cdir.length>0){
			XHR.appendData('calc-cdir-ip-add',document.getElementById('netmaskcdir').value);
		 	AnimateDiv('networks_add');
			XHR.sendAndLoad('$page', 'GET',x_AddNetworkPerform); 
		}  
	
	}	

</script>";
		echo $tpl->_ENGINE_parse_body($html);	
}

function artica_import_delete(){
	$sock=new sockets();
	$ini=new Bs_IniHandler();
	$ini->loadString($sock->GET_INFO("ComputersImportArtica"));	
	unset($ini->_params[$_GET["artica-import-delete"]]);
	$sock->SaveConfigFile($ini->toString(),"ComputersImportArtica");
}
	


function artica_import_popup(){
	$page=CurrentPageName();
	//cyrus.murder.php
	$sock=new sockets();
	$ini=new Bs_IniHandler();
	$ini->loadString($sock->GET_INFO("ComputersImportArtica"));	
	$array=$ini->_params[$_GET["ip"]];
	$html="
	<div class=explain>{import_artica_computers_explain}</div>
	<div id='import_artica_computers'>
		<table style='width:100%' class=form>
			<tr>
				<td valign='top' class=legend>{REMOTE_ARTICA_SERVER}:</td>
				<td valign='top'>".Field_text("artica_ip_addr",$array["artica_ip_addr"],'width:120px;font-size:14px',null)."</td>
			</tr>
			<tr>
				<td valign='top' class=legend>{REMOTE_ARTICA_SERVER_PORT}:</td>
				<td valign='top'>".Field_text("port",$array["port"],'width:120px;font-size:14px',null)."</td>			
			</tr>
			<tr>
				<td valign='top' class=legend>{username}:</td>
				<td valign='top'>".Field_text("artica_user",$array["artica_user"],'width:120px;font-size:14px',null)."</td>
			</tr>
			<tr>				
				<td valign='top' class=legend>{password}:</td>
				<td valign='top'>".Field_password("password",$array["password"],'width:120px;font-size:14px',null)."</td>			
			</tr>			
			<TR>
				<td colspan=2 align='right'>
					<hr>". button("{apply}","SaveImportComputers()")."
					
				</td>
			</tr>
		</table>
		</div>
<script>
	var x_SaveImportComputers= function (obj) {
			YahooWin3Hide();
			if(document.getElementById('main_config_snort')){RefreshTab('main_config_snort');}
		}	
		
	
	function SaveImportComputers(){
				var XHR = new XHRConnection();
				XHR.appendData('artica_ip_addr',document.getElementById('artica_ip_addr').value);
				XHR.appendData('port',document.getElementById('port').value);
				XHR.appendData('artica_user',document.getElementById('artica_user').value);
				XHR.appendData('password',document.getElementById('password').value);
				AnimateDiv('import_artica_computers');
				XHR.sendAndLoad('$page', 'GET',x_SaveImportComputers); 
		} 		
		
</script>		";
		
		
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
	
}

function artica_import_list(){
	$sock=new sockets();
	$ini=new Bs_IniHandler();
	$ini->loadString($sock->GET_INFO("ComputersImportArtica"));
	if(!is_array($ini->_params)){return null;}
	$html="
	
	<div style='font-size:13px;padding-bottom:5px;font-weight:bold'>{import_artica_computers}</div>
	<table>";
	while (list ($ip, $array) = each ($ini->_params)){
		if(trim($ip)==null){continue;}
		
		$delete=imgtootltip('ed_delete.gif','{delete}',"DeleteImportComputers('$ip')");
		$js="ImportComputers('$ip')";
		
		$html=$html . "
		<tr " . CellRollOver($js).">
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td><strong style='font-size:13px'>$ip:{$array["port"]}</td>
			<td><strong style='font-size:13px'>{$array["artica_user"]}</td>
			<td>$delete</td>
		</tr>
		
		";}
			
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body("$html</table>");
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
}


function artica_import_save(){
	$sock=new sockets();
	$ini=new Bs_IniHandler();
	$ini->loadString($sock->GET_INFO("ComputersImportArtica"));
	
	while (list ($num, $line) = each ($_GET)){
		$ini->_params[$_GET["artica_ip_addr"]][$num]=$line;
	}
	
	$sock->SaveConfigFile($ini->toString(),"ComputersImportArtica");
	$sock->getFrameWork("cmd.php?computers-import-nets=yes");
	
}

function artica_importlist_popup(){
	$page=CurrentPageName();
	$html="<div class=explain>{computer_popup_import_explain}</div>
	<div id='popup_import_div' class=form>
	<textarea id='popup_import_list' style='width:99%;height:450px;overflow:auto'></textarea>
	<div style='text-align:right'>
		<hr>
			". button("{import}","ImportListComputersPerform()")."
	</div>
	</div>
<script>
	var x_ImportListComputersPerform= function (obj) {
		var results=obj.responseText;
		alert(results);
		YahooWin3Hide();
		BrowsComputersRefresh();
		if(document.getElementById('main_config_snort')){RefreshTab('main_config_snort');}
	}		
	
	function ImportListComputersPerform(){
			var XHR = new XHRConnection();
			XHR.appendData('popup_import_list',document.getElementById('popup_import_list').value);
		 	document.getElementById('popup_import_div').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
			XHR.sendAndLoad('$page', 'POST',x_ImportListComputersPerform); 
	}
</script>		
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}


function artica_importlist_perform(){
	$datas=$_POST["popup_import_list"];
	$sock=new sockets();
	$sock->SaveConfigFile($datas,"ComputerListToImport");	
	$sock->getFrameWork("cmd.php?browse-computers-import-list=yes");
	$tpl=new templates();
	echo $tpl->javascript_parse_text("{importation_background_text}");
	

}

	
function networkslist($noecho=1){
	$q=new mysql();
	$net=new networkscanner();
	$page=CurrentPageName();
	if(!is_array($net->networklist)){return null;}
	$html="
<div style='height:250px;overflow:auto'>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th>". imgtootltip("plus-24.png","{add}","AddNetwork()")."</td>
	<th colspan=2>{networks}</th>
	<th width=1%>". imgtootltip("refresh-24.png","{refresh}","RefreshNetworklist()")."</th>
	</tr>
</thead>
<tbody class='tbody'>";
	
while (list ($num, $maks) = each ($net->networklist)){
		if(trim($maks)==null){continue;}
		$hash[$maks]=$maks;
}	
	
	while (list ($num, $maks) = each ($hash)){
		if(trim($maks)==null){continue;}
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$delete=imgtootltip('delete-32.png','{delete}',"NetworkDelete('" . md5($num)."')");
		$sql="SELECT netinfos FROM networks_infos WHERE ipaddr='$maks'";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		$ligne["netinfos"]=htmlspecialchars($ligne["netinfos"]);
		$ligne["netinfos"]=nl2br($ligne["netinfos"]);
		if($ligne["netinfos"]==null){$ligne["netinfos"]="{no_info}";}		
		$infos="<div><a href=\"javascript:blur();\" 
		OnClick=\"javascript:GlobalSystemNetInfos('$maks')\" 
		style='font-size:9px;text-decoration:underline'><i>{$ligne["netinfos"]}</i></a></div>";
		
		if($net->DefaultNetworkList[$maks]){
			if(!$net->Networks_disabled[$maks]){
				$style=null;
				$delete="{default}&nbsp;" .texttooltip("{enabled}","{disable}","NetWorksDisable('$maks');",null,0,'font-size:12px;color:black');
			}else{
				
				$style=";text-decoration:line-through";
				$delete="{default}&nbsp;" .texttooltip("{disabled}","{enable}","NetWorksEnable('$maks');",null,0,'font-size:12px;color:red');
			}
			
		}
		
		$html=$html . "
		<tr class=$classtr>
			<td width=1%><img src='img/32-network-server.png'></td>
			<td><strong style='font-size:14px$style' nowrap>$maks$infos</td>
			<td nowrap colspan=2 align='center'>$delete</td>
		</tr>
		
		";}
		
	$html=$html . "</tbody>
	</table>
	</div>
	
	<script>
	var x_NetworkDelete= function (obj) {
		if(document.getElementById('main_config_snort')){RefreshTab('main_config_snort');}
		RefreshNetworklist();
	}	
	
	function NetworkDelete(md){
		var XHR = new XHRConnection();
		XHR.appendData('NetworkDelete',md);
		AnimateDiv('netlist');
		XHR.sendAndLoad('$page', 'GET',x_NetworkDelete);  	
	
	}	
	</script>
	";		
			
	$tpl=new templates();
	if($noecho==1){return $tpl->_ENGINE_parse_body("$html");}
	echo $tpl->_ENGINE_parse_body($html);			
	
	
}




function network_scanner_execute(){
	$tpl=new templates();
	$net=new networkscanner();
	$net->save();
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?LaunchNetworkScanner=yes");
	$box=$tpl->_ENGINE_parse_body('{network_scanner_execute_background}');
	
	$ini=new Bs_IniHandler('ressources/logs/nmap.progress.ini');
	$ini->set('NMAP','pourc','10');
	$ini->set('NMAP','text','{scheduled}');	
	$ini->saveFile('ressources/logs/nmap.progress.ini');
	
	echo $box;
	
}

function Status(){
	$ini=new Bs_IniHandler('ressources/logs/nmap.progress.ini');
	$pourc=$ini->get('NMAP','pourc');
	$text=$ini->get('NMAP','text');
	if($pourc==null){$pourc=0;}
	if($pourc==0){$text="{sleeping}";}
	if($pourc==100){$text="{success}";}
	$color="#5DD13D";	
	$tpl=new templates();
$html="
<table style='width:100%'>
<tr>
<td valign='top'>
	<p class=caption>$text...</p>
	<div style='width:100%;background-color:white;padding-left:0px;border:1px solid $color'>
		<div id='progression_computers'>
			<div style='width:{$pourc}%;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:$color'>
				<strong style='color:#BCF3D6;font-size:12px;font-weight:bold'>{$pourc}%</strong></center>
			</div>
		</div>
	</div>
</td>
<td valign=middle width=1%><div style='background-color:white;padding:5px'>" . imgtootltip('loupe-32.png','{events}',"ViewComputerScanLogs()")."</div></td>
</tr>
</table>
";	
				
echo $tpl->_ENGINE_parse_body($html);				
				
}







?>