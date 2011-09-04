<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.tcpip.inc');
	include_once('ressources/class.system.network.inc');
	include_once('ressources/class.main_cf.inc');
	include_once('ressources/class.mysql.inc');
	
	
$usersmenus=new usersMenus();
if(isset($_GET["mynet_ipfrom"])){CalculCDR();exit;}
if($usersmenus->AsPostfixAdministrator==false){header('location:users.index.php');exit;}

if(isset($_GET["main"])){switch_popup();exit;}
if($_GET["section"]=="BindInterfaceForm"){echo BindInterfaceForm();exit;}
if($_GET["section"]=="networkint"){echo NetworkInterfacesForm();exit;}
if(isset($_GET["ReloadInterfaceTable"])){echo BindInterfaceTable();exit;}
if(isset($_GET["ReloadNetworkTable"])){echo mynetworks_table();exit;}


if(isset($_GET["POSTFIX_MULTI_INSTANCE_JS"])){POSTFIX_MULTI_INSTANCE_JS();exit;}
if(isset($_GET["EnablePostfixMultiInstance"])){POSTFIX_MULTI_INSTANCE_SAVE();exit;}
if(isset($_GET["inet_interface_add"])){inet_interface_add();exit;}
if(isset($_GET["PostfixAddMyNetwork"])){PostfixAddMyNetwork();exit;}
if(isset($_GET["PostFixDeleteMyNetwork"])){PostFixDeleteMyNetwork();exit;}
if(isset($_GET["PostfixDeleteInterface"])){PostfixDeleteInterface();exit;}
if(isset($_GET["ignore_mx_lookup_error"])){SaveDNSSettings();exit;}
if(isset($_GET["bind9infos"])){echo bind9infos();exit;}
if(isset($_GET["script"])){switch_script();exit;}
if(isset($_GET["popup"])){switch_popup();exit;}
if(isset($_GET["PostfixEnabledInBind9"])){bind9_save_enable();exit;}
if(isset($_GET["bind9Options"])){bind9_form();exit;}
if(isset($_GET["PostfixBind9Delete"])){bind9_delete();exit;}
if(isset($_GET["PostfixBind9NameServer"])){bind9_add();exit;}
if(isset($_GET["ajax"])){ajax();exit;}
if(isset($_GET["ajax-popup"])){popup();exit;}


if(isset($_GET["smtp_bind_address6"])){ipv6_save();exit;}
page();	

function ajax(){
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{postfix_network}");
	$datas=file_get_contents("js/postfix-network.js");
	$page=CurrentPageName();
	$prefix=str_replace(".","_",$page);
	$html="
	var {$prefix}Timeout=0;
	$datas
	".addinscripts()."
	YahooWin0(700,'postfix.network.php?ajax-popup=yes','$title');
	
	
	
	";
	echo $html;
	
}

function popuptabs(){
	
	$page=CurrentPageName();
	$array["BindInterfaceForm"]='{inet_interfaces_title}';
	$array["networkint"]='{network}';
	$array["dns"]='{DNS_SETTINGS}';
	$array["ipv6"]='ipv6';
	
	$users=new usersMenus();
	if($users->POSTFIX_MULTI){
		$array["POSTFIX_MULTI_INSTANCE"]="{POSTFIX_MULTI_INSTANCE}";
	}
	

while (list ($num, $ligne) = each ($array) ){
		$html[]= "<li><a href=\"$page?main=$num\"><span>$ligne</span></a></li>\n";
	}
	
	
	return "
	<div id=main_config_postfix_net style='width:100%;height:430px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_postfix_net').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			
			
			});
		</script>";		
}



function popup(){
	$tab=popuptabs();
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($tab);
	
}

function addinscripts(){
	
	$page=CurrentPageName();
	$prefix=str_replace(".","_",$page);	
	
	return "	
	var x_PostfixBind9Delete= function (obj) {
			LoadAjax('bind9Options','$page?bind9Options=yes');
		}	
	
		function PostfixBind9Delete(num){
			var XHR = new XHRConnection();
			XHR.appendData('PostfixBind9Delete',num);
			XHR.sendAndLoad('$page', 'GET',x_PostfixBind9Delete);
		}
		
		function PostfixBind9Add(){
			var nameserver=document.getElementById('PostfixBind9NameServer').value;
			var XHR = new XHRConnection();
			XHR.appendData('PostfixBind9NameServer',nameserver);
			XHR.sendAndLoad('$page', 'GET',x_PostfixBind9Delete);			
		}
		
	
	function networkint(){ 
		LoadAjax('networkint','$page?section=networkint');
	}
	function bind9Implent(){ 
		LoadAjax('bind9Implent','$page?bind9infos=yes');
	}
	function bind9Options(){ 
		LoadAjax('bind9Options','$page?bind9Options=yes');
	}			
	
	
	function nothing(){
	
	}
	
	//setTimeout(\"initForms()\",3000)
	
	
	
	
	";
	
}




function bind9_delete(){
	$sock=new sockets();
	$dns=$sock->GET_INFO('PostfixBind9DNSList');
	$array=explode("\n",$dns);	
	unset($array[$_GET["PostfixBind9Delete"]]);
	if(is_array($array)){
		$txt=implode("\n",$array);
	}else{$txt=" ";}
	
	$sock->SaveConfigFile($txt,"PostfixBind9DNSList");
	
}

function bind9_add(){
	$sock=new sockets();
	$dns=$sock->GET_INFO('PostfixBind9DNSList');
	$array=explode("\n",$dns);		
	$array[]=$_GET["PostfixBind9NameServer"];
	$txt=implode("\n",$array);
	$sock->SaveConfigFile($txt,"PostfixBind9DNSList");
	}

function switch_script(){
	
	switch ($_GET["script"]) {
		case "bind9":bind9_script();break;
		default:
			break;
	}
}
function switch_popup(){
	
	switch ($_GET["main"]) {
		case "bind9":bind9_popup();break;
		case "BindInterfaceForm":BindInterfaceForm();break;
		case "networkint":NetworkInterfacesForm();break;
		case "dns":QueryDNSForm();break;
		case "POSTFIX_MULTI_INSTANCE":POSTFIX_MULTI_INSTANCE();exit;
		case "ipv6":ipv6();exit;break;
		default:
			break;
	}
}
function bind9_form(){
	$sock=new sockets();
	$PostfixEnabledInBind9=$sock->GET_INFO('PostfixEnabledInBind9');
	if($PostfixEnabledInBind9<>1){return null;}
	
	$dns=$sock->GET_INFO('PostfixBind9DNSList');
	if(trim($dns)==null){
		$net=new networking();
		if(is_array($net->arrayNameServers)){
			$dns=implode("\n",$net->arrayNameServers);
			$sock->SaveConfigFile($dns,"PostfixBind9DNSList");
		}
		
	}
	
	
	$array=explode("\n",$dns);
	if(!is_array($array)){$array=array();}
	$title="<br><H5>{dns_servers}</H5>";
	$html="
	<table class=table_form style='width:90%'>";
	while (list ($num, $val) = each ($array) ){
		if(trim($val)==null){continue;}
			$html=$html . "
				<tr>
					<td width=1%><img src='img/fw_bold.gif'></td>
					<td><strong style='font-size:13px'>$val</td>
					<td width=1%>" . imgtootltip("ed_delete.gif",'{delete}',"PostfixBind9Delete($num);")."</td>
				</tr>";
	}
	$html=$html . "
	
	<tr>
		<td width=1%>&nbsp;</td>
		<td>" . Field_text('PostfixBind9NameServer',null,'width:190px')."&nbsp;<input type='button' OnClick=\"javascript:PostfixBind9Add();\" value='{add}&nbsp;&raquo;'></td>
		<td width=1%>&nbsp;</td>
	</tr>
	</table>";
	
	
$html=RoundedLightWhite($html);
$tpl=new templates();
echo $tpl->_ENGINE_parse_body("$title$html");	
}

function bind9_script(){
	
$page=CurrentPageName();
$html="
	var tmpnum='';
	
	load();
	
	function load(){
	YahooWin(350,'$page?popup=bind9','','');	
	}
	
var x_PostfixEnabledInBind9= function (obj) {
	load();
	LoadAjax('bind9Implent','$page?bind9infos=yes');
	LoadAjax('bind9Options','$page?bind9Options=yes');
}	


	function PostfixEnabledInBind9(){
		var XHR = new XHRConnection();
		XHR.appendData('PostfixEnabledInBind9',document.getElementById('PostfixEnabledInBind9').value);
		XHR.sendAndLoad('$page', 'GET',x_PostfixEnabledInBind9);		
		
	}
	";
	echo $html;	
}

function bind9_save_enable(){
	$sock=new sockets();
	$sock->SET_INFO('PostfixEnabledInBind9',$_GET["PostfixEnabledInBind9"]);
	
}

function bind9_popup(){
	$sock=new sockets();
	$PostfixEnabledInBind9=$sock->GET_INFO('PostfixEnabledInBind9');
	$enable=Paragraphe_switch_img('{postfix_not_bind_activated}','{postfix_better_with_bind9_explain}','PostfixEnabledInBind9',$PostfixEnabledInBind9,'{enable_disable}',390);
	
$html="
	<h1>{bind9_with_postfix}</H1>
	<div style='width:300px'>$enable</div>
	<div style='text-align:right'>
	<hr>
		<input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:PostfixEnabledInBind9();\">
	</div>
	";	
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);

}


function BindInterfaceForm($noecho=0){
	
	$page=CurrentPageName();
	$ip=new networking(1);
	$array_IP=$ip->ALL_IPS_GET_ARRAY();
	$array_IP["all"]="{all}";
	$array_IP[null]="{select}";
	$fieldIP=Field_array_Hash($array_IP,"inet_interface_select",null,null,null,0,"padding:3px;font-size:14px");
	$BindInterfaceTable=BindInterfaceTable();
	$sock=new sockets();
	$PostfixBindInterfacePort=$sock->GET_INFO("PostfixBindInterfacePort");
	if(!is_numeric($PostfixBindInterfacePort)){	$PostfixBindInterfacePort=25;}
		
$html="<div id='BindInterfaceForm'>
	<table style='width:90%' align='center' class=form>
	<tr>
		<td align='right' valign='top' nowrap class=legend>{give the new interface}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('inet_interface_add',null,'width:80%;padding:3px;font-size:14px',null,null,'{inet_interfaces_text}') ."</td>
	</tr>
	<tr>
		<td align='right' valign='top' nowrap class=legend>{or} {select_ip_address}&nbsp;:</strong></td>
		<td align='left'>$fieldIP</td>
	</tr>
	<tr>
		<td align='right' valign='top' nowrap class=legend>{listen_port}&nbsp;:</strong></td>
		<td align='left'>" . Field_text('PostfixBindInterfacePort',$PostfixBindInterfacePort,'width:60px;padding:3px;font-size:14px',null,null) ."</td>
	</tr>		
	<tr><td colspan=2 align='right'>&nbsp;
	<hr>". button("{add}","PostfixAddInterface()")."
	</td>
	</table>
	</div>
	<div id='interface_table' style='padding:10px'>$BindInterfaceTable</div>
	<script>
		var x_ReloadInterface= function (obj) {
			RefreshTab('main_config_postfix_net');
			}
	
	
		function PostfixAddInterface(){
			var ip_selected=document.getElementById('inet_interface_select').value;
			var inet_interface_add=document.getElementById('inet_interface_add').value;
			if (inet_interface_add.length==0){
				if(ip_selected.length>0){
					inet_interface_add=ip_selected;
				}
			}
			
			document.getElementById('inet_interface_add').value='';
			document.getElementById('inet_interface_select').value='';
			var XHR = new XHRConnection();
			XHR.appendData('inet_interface_add',inet_interface_add);
			if(!document.getElementById('PostfixBindInterfacePort')){alert('PostfixBindInterfacePort no such field');return;}
			XHR.appendData('PostfixBindInterfacePort',document.getElementById('PostfixBindInterfacePort').value);
			AnimateDiv('BindInterfaceForm');
			XHR.sendAndLoad('$page', 'GET',x_ReloadInterface);	
			
		}
		
		function ReloadInterfaceTable(){
			LoadAjax('BindInterfaceForm','postfix.network.php?section=BindInterfaceForm');
			}	
			
		function PostfixDeleteInterface(num){
			var XHR = new XHRConnection();
			XHR.appendData('PostfixDeleteInterface',num);
			document.getElementById('BindInterfaceForm').innerHTML=\"<center style='width:100%'><img src='img/wait_verybig.gif'></center>\";
			XHR.sendAndLoad('$page', 'GET',x_ReloadInterface);
			}			
	
	</script>
	";
	
$tpl=new templates();
if($noecho==1){return  $tpl->_ENGINE_parse_body($html);}
echo $tpl->_ENGINE_parse_body($html);
	
}



function QueryDNSForm(){
	$sock=new sockets();
	$main=new main_cf(0);
	$page=CurrentPageName();
	$ldap=new clladp();
	$domains=$ldap->hash_get_all_domains();
	$myhostname=$sock->GET_INFO("myhostname");
	
	$domains["\$mydomain"]="\$mydomain";
	$myorigin=Field_array_Hash($domains,"myorigin",$main->main_array["myorigin"]);
	if(strlen($myhostname)>2){$main->main_array["myhostname"]=$myhostname;}
	
	$myorigin="<strong>\$mydomain</strong><input type='hidden' name='myorigin' value='\$mydomain' id='myorigin'>";
	
	$styleadd="style='font-size:13px;padding:4px'";

$html="<div id='QueryDNSFormSaveid'>
<span style='font-size:12px;font-weight:bold'>{DNS_SETTINGS} & {hostname}</span>
	<table style='width:90%' align='center'>
	<tr>
	<td align='right' valign='top' nowrap class=legend>{myhostname}&nbsp;:</strong></td>
	<td align='left' width=1% $styleadd>" . Field_text('myhostname',$main->main_array["myhostname"],'width:220px;font-size:14px;padding:3px') ."</td>
	<td valign='top' width=1% $styleadd>".help_icon('{myhostname_text}')."</td>
	<tr>
	<tr>
	<td align='right' valign='top' nowrap class=legend $styleadd>{myorigin}&nbsp;:</strong></td>
	<td align='left' width=1% $styleadd> $myorigin</td>
	<td valign='top' width=1% $styleadd>".help_icon('{myorigin_text}')."</td>
	<tr>	
	<td align='right' valign='top' nowrap class=legend $styleadd>{ignore_mx_lookup_error}&nbsp;:</strong></td>
	<td align='left' width=1% $styleadd>" . Field_checkbox('ignore_mx_lookup_error','yes',$main->main_array["ignore_mx_lookup_error"]) ."</td>
	<td valign='top' width=1% $styleadd>".help_icon('{ignore_mx_lookup_error_text}')."</td>
	</tr>
	<tr>
		<tr>
	<td align='right' valign='top' nowrap class=legend $styleadd>{disable_dns_lookups}&nbsp;:</strong></td>
	<td align='left'  width=1% $styleadd>" . Field_checkbox('disable_dns_lookups','yes',$main->main_array["disable_dns_lookups"]) ."</td>
	<td valign='top' width=1% $styleadd>".help_icon('{disable_dns_lookups_text}')."</td>
	</tr>
	<tr><td colspan=3 align='right'><hr>". button("{edit}","QueryDNSFormSave()")."</td></tr>
	</table>
	</div>
	
	
	<script>
	
	var x_QueryDNSFormSave= function (obj) {
		var results=obj.responseText;
		if(results.length>3){alert('\"'+results+'\"'+results.length);}
		RefreshTab('main_config_postfix_net');
		}
		
	
		function QueryDNSFormSave(){
			var XHR = new XHRConnection();
			XHR.appendData('myhostname',document.getElementById('myhostname').value);
			if(document.getElementById('ignore_mx_lookup_error').checked){XHR.appendData('ignore_mx_lookup_error','yes');}else{
				XHR.appendData('ignore_mx_lookup_error','no');
			}
			
			if(document.getElementById('disable_dns_lookups').checked){XHR.appendData('disable_dns_lookups','yes');}else{
				XHR.appendData('disable_dns_lookups','no');
			}			
			document.getElementById('QueryDNSFormSaveid').innerHTML='<center><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_QueryDNSFormSave);	
			
		}
	</script>
	
	";


$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
	
}

function NetworkInterfacesForm($noecho=0){
	//$mynetworks_table=mynetworks_table();
	$page=CurrentPageName();
$html="
<span style='font-size:16px;font-weight:bold'>{mynetworks_title}</span>
	<table style='width:100%;margin-top:8px' align='center'>
	<tr>
	<td align='right' valign='top' nowrap class=legend>{give the new network}&nbsp;:</strong></td>
	<td align='left'>" . Field_text('mynetworks',null,'width:80%;padding:3px;font-size:13px',null,null,'{mynetworks_text}') ."</td>
	</tr>
	<tr>
	<td align='right' valign='top' nowrap class=legend>{or} {give_ip_from_ip_to}&nbsp;:</strong></td>
	<td align='left'>" . 
	Field_text('ipfrom',null,'width:100px;padding:3px;font-size:13px',null,'PostfixCalculateMyNetwork()') . 
	Field_text('ipto',null,'width:100px;;padding:3px;font-size:13px',null,'PostfixCalculateMyNetwork()') ."</td>
	</tr>
	
	<tr><td colspan=2 align='right'>
		<hr>
		". button("{add}","PostfixAddMyNetwork()")."
	</td>
	</tr>
	</table>	
	<div id='network_table' style='padding:10px'>$mynetworks_table</div>
	
	<script>
	
		var x_ReloadNetworkTable= function (obj) {
			ReloadNetworkTable();
			}	
				
	function PostfixAddMyNetwork(){
		PostfixCalculateMyNetwork();
		var XHR = new XHRConnection();
		XHR.appendData('PostfixAddMyNetwork',document.getElementById('mynetworks').value);
		document.getElementById('network_table').innerHTML=\"<center style='width:100%'><img src='img/wait_verybig.gif'></center>\";
		XHR.sendAndLoad('$page', 'GET',x_ReloadNetworkTable);
	}	
	
		function ReloadNetworkTable(){
			LoadAjax('network_table','$page?ReloadNetworkTable=yes');
			}
			
	var x_PostfixCalculateMyNetwork= function (obj) {
		var results=obj.responseText;
		document.getElementById('mynetworks').value=trim(results);
	}


	function PostfixCalculateMyNetwork(){
		if(!document.getElementById('ipfrom')){return false;}
		var ipfrom=document.getElementById('ipfrom').value;
		var ipto=document.getElementById('ipto').value;
		
		if(ipfrom.length>0){
			var ARRAY=ipfrom.split('\.');
			if(ARRAY.length>3){
				if(ipto.length==0){
					document.getElementById('ipto').value=ARRAY[0] + '.' + ARRAY[1] + '.'+ARRAY[2] + '.255';
					
					}
					}else{return false}
		}else{return false;}
		document.getElementById('ipfrom').value=ARRAY[0] + '.' + ARRAY[1] + '.'+ARRAY[2] + '.0';
		ipfrom=ARRAY[0] + '.' + ARRAY[1] + '.'+ARRAY[2] + '.0';
		var XHR = new XHRConnection();
		XHR.appendData('mynet_ipfrom',ipfrom);
		XHR.appendData('mynet_ipto',document.getElementById('ipto').value);
		XHR.sendAndLoad('$page', 'GET',x_PostfixCalculateMyNetwork);
		}	

	function PostFixDeleteMyNetwork(num){
		var XHR = new XHRConnection();
		XHR.appendData('PostFixDeleteMyNetwork',num);
		document.getElementById('network_table').innerHTML=\"<center style='width:100%'><img src='img/wait_verybig.gif'></center>\";
		XHR.sendAndLoad('$page', 'GET',x_ReloadNetworkTable);
		}		
			
	
	ReloadNetworkTable();
	</script>
	
	";
$tpl=new templates();
if($noecho==1){return $tpl->_ENGINE_parse_body($html);}

echo $tpl->_ENGINE_parse_body($html);

}


function BindInterfaceTable(){
	
	$sock=new sockets();
	$table=explode("\n",$sock->GET_INFO("PostfixBinInterfaces"));
	
	if(!is_array($table)){$table[]="all";}
	
	
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th colspan=3>{inet_interfaces_title}</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	if(is_array($table)){
	while (list ($num, $val) = each ($table) ){
		if($val==null){continue;}
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		
		$html=$html . "
		<tr class=$classtr>
			<td width=1%><img src='img/folder-network-32.png'></td>
			<td style='font-size:16px'>$val</td>
			<td  width=1%>" . imgtootltip('delete-32.png','{delete} {inet_interface}',"PostfixDeleteInterface($num)") ."</td>
		</tr>";
		
	}}
	
	$html=$html . "</table></center>";
	
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
	}
	
function mynetworks_table(){
	
	$main=new main_cf();
	

	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th colspan=4>{networks}</th>
	</tr>
</thead>
<tbody class='tbody'>";			

	$q=new mysql();
	if(is_array($main->array_mynetworks)){
	while (list ($num, $val) = each ($main->array_mynetworks) ){
		if(trim($val)==null){continue;}
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$sql="SELECT netinfos FROM networks_infos WHERE ipaddr='$val'";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		$ligne["netinfos"]=htmlspecialchars($ligne["netinfos"]);
		$ligne["netinfos"]=nl2br($ligne["netinfos"]);
		if($ligne["netinfos"]==null){$ligne["netinfos"]="{no_info}";}
		$html=$html . "
		<tr class=$classtr>
			<td width=1%><img src='img/folder-network-32.png'></td>
			<td style='font-size:16px'>$val</td>
			<td style='font-size:16px'><a href=\"javascript:blur();\" OnClick=\"javascript:GlobalSystemNetInfos('$val')\" style='font-size:12px;text-decoration:underline'><i>{$ligne["netinfos"]}</i></a></td>
			<td  width=1%>" . imgtootltip('delete-32.png','{delete} {network}',"PostFixDeleteMyNetwork($num)") ."</td>
		</tr>";
		}
	}
	
	$html=$html . "
	</tbody>
	</table>
	</center>";
	
	
	
	
		/*	<div id='div_net'>$html</div><br>
			<input type='button' value='Add a network&nbsp;&raquo;' OnClick=\"javascript:TreePostfixAddMyNetwork();\" style='float:right'>
			<p style='font-size:9px'>{mynetworks_text}</p>
		</fieldset>";
		
		*/
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);	
}
function CalculCDR(){
	$ip=new IP();
	$ipfrom=$_GET["mynet_ipfrom"];
	$ipto=$_GET["mynet_ipto"];
	$SIP=$ip->ip2cidr($ipfrom,$ipto);
	echo trim($SIP);
	}
function inet_interface_add(){
	$main=new main_cf();
	$sock=new sockets();
	
	
	if($_GET["inet_interface_add"]<>null){
		$table=explode("\n",$sock->GET_INFO("PostfixBinInterfaces"));	
		if(!is_array($table)){$table[]="all";}
		$table[]=$_GET["inet_interface_add"];
	
		while (list ($num, $val) = each ($table) ){
			if($val==null){continue;}
			$newarray[]=$val;
		}
	
		if(!is_array($newarray)){$newarray[]="all";}
		$sock->SaveConfigFile(implode("\n",$newarray),"PostfixBinInterfaces");
		$sock->getFrameWork("cmd.php?postfix-interfaces=yes");
	}

	if(is_numeric($_GET["PostfixBindInterfacePort"])){
		if($_GET["PostfixBindInterfacePort"]<>25){
			$PostfixBindInterfacePort=$sock->GET_INFO("PostfixBindInterfacePort");
			if($_GET["PostfixBindInterfacePort"]<>$PostfixBindInterfacePort){
				$sock->SET_INFO("PostfixBindInterfacePort", trim($_GET["PostfixBindInterfacePort"]));
				$sock->getFrameWork("cmd.php?postfix-ssl=yes");
			}
		}
	}
	
	

}
function PostfixAddMyNetwork(){
	$main=new main_cf();
	
	if(preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)\/([0-9]+)#",$_GET["PostfixAddMyNetwork"],$re)){
		$_GET["PostfixAddMyNetwork"]="{$re[1]}.{$re[2]}.{$re[3]}.{$re[4]}/{$re[5]}";
	}
	
	$main->add_my_networks($_GET["PostfixAddMyNetwork"]);
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-networks=yes");
	}
function PostFixDeleteMyNetwork(){
	$main=new main_cf();
	$main->delete_my_networks($_GET["PostFixDeleteMyNetwork"]);
		$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-networks=yes");
}
function PostfixDeleteInterface(){
	$sock=new sockets();
	$table=explode("\n",$sock->GET_INFO("PostfixBinInterfaces"));	
	unset($table[$_GET["PostfixDeleteInterface"]]);
	if(!is_array($table)){$table[]="all";}
	
	while (list ($num, $val) = each ($table) ){
		if($val==null){continue;}
		$newarray[]=$val;
	}
	
	if(!is_array($newarray)){$newarray[]="all";}
	$sock->SaveConfigFile(implode("\n",$newarray),"PostfixBinInterfaces");
	$sock->getFrameWork("cmd.php?postfix-interfaces=yes");
	
}

function SaveDNSSettings(){
	$main=new main_cf();	
	$main->main_array["ignore_mx_lookup_error"]=$_GET["ignore_mx_lookup_error"];
	$main->main_array["disable_dns_lookups"]=$_GET["disable_dns_lookups"];
	$main->main_array["myhostname"]=$_GET["myhostname"];
	$main->save_conf();
	
	$sock=new sockets();
	$sock->SET_INFO("myhostname",$_GET["myhostname"]);
	$sock->getFrameWork("cmd.php?postfix-others-values=yes");
	
	
	}
	
function bind9infos(){
	$page=CurrentPageName();
	$sock=new sockets();
	$users=new usersMenus();
	if(!$users->BIND9_INSTALLED){
		if(!$users->POWER_DNS_INSTALLED){
			$title="{postfix_better_with_bind9}";
			$text="{postfix_better_with_bind9_explain}";
			$img="64-red.png";
			return Paragraphe($img,$title,$text);
		}
	}
	$PostfixEnabledInBind9=$sock->GET_INFO('PostfixEnabledInBind9');
	if($PostfixEnabledInBind9<>1){
		$title="{postfix_not_bind_activated}";
		$text="{postfix_better_with_bind9_explain}";
		$img="i64.png";
		$uri="javascript:Loadjs('$page?script=bind9')";
		return Paragraphe($img,$title,$text,$uri);
		
	}
	
$title="{postfix_bind_activated}";
		$text="{postfix_bind_activated_text}";
		$img="ok64.png";
		$uri="javascript:Loadjs('$page?script=bind9')";
		return Paragraphe($img,$title,$text,$uri);	
}

function  POSTFIX_MULTI_INSTANCE_JS(){
		$page=CurrentPageName();
		$tpl=new templates();
		$title=$tpl->_ENGINE_parse_body("{POSTFIX_MULTI_INSTANCE}");
		$html="
			function POSTFIX_MULTI_INSTANCE_JS_START(){
				YahooWin5(600,'$page?main=POSTFIX_MULTI_INSTANCE','$title');
			}
		
		POSTFIX_MULTI_INSTANCE_JS_START();";
	
	echo $html;
}


function POSTFIX_MULTI_INSTANCE(){
	$sock=new sockets();
	$page=CurrentPageName();
	$EnablePostfixMultiInstance=$sock->GET_INFO("EnablePostfixMultiInstance");
	$enable=Paragraphe_switch_img("{ENABLE_POSTFIX_MULTI_INSTANCE}","{POSTFIX_MULTI_INSTANCE_TEXT}",
	"EnablePostfixMultiInstance",$EnablePostfixMultiInstance,null,400);
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top' width=1%><img src='img/postfix-multi-128.png'></td>
		<td valign='top'>
				<table style='width:100%'>
			<tr>
				<td valign='top'>$enable</td>
			</tr>
			</table>
		</td>
	</tr>
	</table>
	<div class=explain>{POSTFIX_MULTI_INSTANCE_HOWTO}</div>
		<div style='text-align:right'><hr>
		". button("{edit}","POSTFIX_MULTI_INSTANCE()")."
		</div>
		
	<script>
	var x_POSTFIX_MULTI_INSTANCE= function (obj) {
			remove_cache();
			if(document.getElementById('main_config_postfix_net')){RefreshTab('main_config_postfix_net');}
			if(document.getElementById('main_config_postfix')){RefreshTab('main_config_postfix');}
			YahooWin5Hide();
			}	
	
	
		function POSTFIX_MULTI_INSTANCE(){
			var XHR = new XHRConnection();
			XHR.appendData('EnablePostfixMultiInstance',document.getElementById('EnablePostfixMultiInstance').value);
			document.getElementById('img_EnablePostfixMultiInstance').src='img/wait_verybig.gif';
			XHR.sendAndLoad('$page', 'GET',x_POSTFIX_MULTI_INSTANCE);			
		}
	</script>
	";
	
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
	
}

function POSTFIX_MULTI_INSTANCE_SAVE(){
	$sock=new sockets();
	$sock->SET_INFO("EnablePostfixMultiInstance",$_GET["EnablePostfixMultiInstance"]);
	
	if($_GET["EnablePostfixMultiInstance"]==0){
		$sock->getFrameWork("cmd.php?postfix-multi-disable=yes");
		return;
	}
	
	$sock->getFrameWork("cmd.php?restart-postfix-single=yes");
}

function ipv6(){
	$sock=new sockets();
	$smtp_bind_address6=$sock->GET_INFO("smtp_bind_address6");
	$PostfixEnableIpv6=$sock->GET_INFO("PostfixEnableIpv6");
	$tpl=new templates();
	$page=CurrentPageName();
	$html="
	<table style='width:100%'>
	<tr>
	<td valign='top' width=1%>
	<img src='img/folder-network-128.png' id='smtp_bind_address6_img'>
	</td>
	<td valign='top'>
	<div class=explain>{smtp_bind_address6}</div>
	
	<table style='width:100%'>
	<tr>
		<td class=legend>{enable_ipv6}:</td>
		<td>". Field_checkbox("PostfixEnableIpv6",1,$PostfixEnableIpv6,"PostfixEnableIpv6Check()")."</td>
	</tr>	
	<tr>
		<td class=legend>{addr}:</td>
		<td>". Field_text("smtp_bind_address6",$smtp_bind_address6,"font-size:13px;padding:3px;width:220px")."</td>
	</tr>
	<tr>
		<td colspan=2 align=right><hr>". button("{apply}","smtp_bind_address6_save()")."</td>
	</tr>
	</table>
	</td>
	</tr>
	</table>
	
	<script>
	var x_smtp_bind_address6_save= function (obj) {
			if(document.getElementById('main_config_postfix_net')){RefreshTab('main_config_postfix_net');}
			if(document.getElementById('main_config_postfix')){RefreshTab('main_config_postfix');}
			}	
	
	
		function smtp_bind_address6_save(){
			var XHR = new XHRConnection();
			XHR.appendData('smtp_bind_address6',document.getElementById('smtp_bind_address6').value);
			document.getElementById('smtp_bind_address6_img').src='img/wait_verybig.gif';
			XHR.sendAndLoad('$page', 'GET',x_smtp_bind_address6_save);			
		}
		
		function PostfixEnableIpv6Check(){
			document.getElementById('smtp_bind_address6').disabled=true;
			if(document.getElementById('PostfixEnableIpv6').checked){
				document.getElementById('smtp_bind_address6').disabled=false;
			}
		}
PostfixEnableIpv6Check();
	</script>
		
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
}

function ipv6_save(){
	$sock=new sockets();
	$sock->SET_INFO("smtp_bind_address6",$_GET["smtp_bind_address6"]);
	$sock->SET_INFO("PostfixEnableIpv6",$_GET["PostfixEnableIpv6"]);
	$sock->getFrameWork("cmd.php?postfix-interfaces=yes");
}



	
?>	

