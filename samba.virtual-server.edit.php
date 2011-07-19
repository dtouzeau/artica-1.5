<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.samba.aliases.inc');

	if(isset($_GET["debug-page"])){ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);$GLOBALS["VERBOSE"]=true;}
	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_GET["settings"])){main_settings();exit;}
	if(isset($_POST["RootDir"])){main_settings_save();exit;}
	if(isset($_GET["ipaddr-field"])){field_ipaddr();exit;}

	js();
function js(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$title="{virtual_server}&nbsp;&raquo;&nbsp;{$_GET["hostname"]}";
	$hostname=$_GET["hostname"];
	if($hostname==null){$title="{virtual_server}&nbsp;&raquo;&nbsp;{add}";}
	$title=$tpl->_ENGINE_parse_body($title);
	$html="YahooWin3(650,'$page?tabs=yes&hostname=$hostname','$title')";
	echo $html;	
	}
	
function tabs(){
		$page=CurrentPageName();
		$tpl=new templates();
		$array["settings"]='{main_settings}';
		if($_GET["hostname"]<>null){
			$array["shares"]='{shared_folders}';
		}
		while (list ($num, $ligne) = each ($array) ){
			if($num=="shares"){
				$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"samba.virtual-server.folders.php?hostname={$_GET["hostname"]}\"><span>$ligne</span></a></li>\n");
				continue;
			}
			
			
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&hostname={$_GET["hostname"]}\"><span>$ligne</span></a></li>\n");
			
			
		}
	
	
	echo "
	<div id=main_config_virtsamba style='width:100%;height:550px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_virtsamba').tabs();
			});
		</script>";	
	
}

function main_settings(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$hostname=$_GET["hostname"];
	$smb=new samba_aliases($hostname);
	$must_choose_ipaddr=$tpl->javascript_parse_text("{must_choose_ipaddr}");
	$q=new mysql();

	$button="{apply}";
	$lock=1;
	if($hostname==null){
		$button="{add}";
		$lock=0;
	}
	$ldap=new clladp();
	
	$ous=$ldap->hash_get_ou(true);
	$ous[null]="{none}";
	$oufield=Field_array_Hash($ous, "smbou",$smb->ou,"style:font-size:13px;padding:3px");
	$addIpaddr=imgtootltip("plus-24.png","{add}","Loadjs('system.nic.config.php?js-virtual-add=yes&function-after=IpAddrSMbField');");

	
	
	$html="
	<div id='smbvritid'>
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{netbiosname}:</td>
		<td valign='top'>" . Field_text("hostname",$hostname,'width:190px;font-size:16px;padding:3px;font-weight:bold;font-family:monospace',null,null,null,false,null,$workgroup_disabled)."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend>{listen_ip}:</td>
		<td valign='top'><span id='ipaddr-field'>$ipaddrfield</span>&nbsp;<siv style='float:right'>$addIpaddr</div></td>
		<td>" . help_icon("{samba_aliases_virtual_ip}")."</td>
	</tr>	
	<tr>
		<td align='right' nowrap valign='top' class=legend class=legend>{workgroup}:</td>
		<td valign='top'>" . Field_text("workgroup",$smb->workgroup,'width:190px;font-size:13px;padding:3px',null,null,null,false,null,$workgroup_disabled)."</td>
		<td valign='top'>" . help_icon("{workgroup_text}")."</td>
	</tr>
	<tr>
		<td align='right' nowrap valign='top' class=legend class=legend>{organization}:</td>
		<td valign='top'>$oufield</td>
		<td valign='top'>" . help_icon("{virtual_smb_ou_explain}")."</td>
	</tr>	
	<tr>
		<td align='right' nowrap valign='top' class=legend class=legend>{root_directory}:</td>
		<td valign='top'>" . Field_text("RootDir",$smb->RootDir,
		'width:190px;font-size:13px;padding:3px',null,null,null,false,null,$workgroup_disabled).
		"&nbsp;<input type='button' value='{browse}...' OnClick=\"javascript:Loadjs('SambaBrowse.php?field=RootDir&no-shares=yes');\"></td>
		<td valign='top'>" . help_icon("{smb_RootDir_text}")."</td>
	</tr>	
	<tr>
		<td colspan=3 align='right'><hr>". button($button,"SaveSMBVirt()")."</td>
	</tr>
	</table>
	</div>
	
	<script>
		var x_SaveSMBVirt=function (obj) {
		    var hostname='$hostname';
			var results=obj.responseText;
			if(results.length>2){alert(results);}			
			if(document.getElementById('browse-samba-list')){BrowseSambaSearch();}
			if(hostname.length==0){YahooWin3Hide();return;}
			RefreshTab('main_config_virtsamba');
		}
	
	
		function SaveSMBVirt(){
			var XHR = new XHRConnection();
			var ipaddr=document.getElementById('smbipaddr').value;
			XHR.appendData('ipaddr',ipaddr);
			XHR.appendData('hostname',document.getElementById('hostname').value);
			XHR.appendData('workgroup',document.getElementById('workgroup').value);
			XHR.appendData('ou',document.getElementById('smbou').value);
			XHR.appendData('RootDir',document.getElementById('RootDir').value);
    		AnimateDiv('smbvritid');
    		XHR.sendAndLoad('$page', 'POST',x_SaveSMBVirt);
				
		
		}
		
		function lockSmbAlias(){
			var lock=$lock;
			if(lock==1){document.getElementById('hostname').disabled=true;}
			
		}
		
		function IpAddrSMbField(){
			LoadAjax('ipaddr-field','$page?hostname=$hostname&ipaddr-field=yes');
		}
		
		lockSmbAlias();
		IpAddrSMbField();
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);

}

function field_ipaddr(){
	$q=new mysql();
	$smb=new samba_aliases($_GET["hostname"]);
	$tpl=new templates();
	$sql="SELECT ipaddr FROM samba_hosts";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){$ipall[$ligne["ipaddr"]]=true;}
	
	$sql="SELECT ipaddr FROM nics_virtuals";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($ipall[$ligne["ipaddr"]]){continue;}
		$ipaddrs[$ligne["ipaddr"]]=$ligne["ipaddr"];
	}
	if($smb->ipaddr<>null){
		$ipaddrs[$smb->ipaddr]=$smb->ipaddr;
	}
	$ipaddrs[null]="{none}";
	echo $tpl->_ENGINE_parse_body(Field_array_Hash($ipaddrs, "smbipaddr",$smb->ipaddr,"style:font-size:13px;padding:3px"));	
	
}

function main_settings_save(){
	$smb=new samba_aliases($_POST["hostname"]);
	$smb->ipaddr=$_POST["ipaddr"];
	$smb->RootDir=$_POST["RootDir"];
	$smb->workgroup=$_POST["workgroup"];
	$smb->ou=$_POST["ou"];
	$smb->Edit();
	
}
