<?php
if(isset($_GET["verbose"])){$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}
include_once('ressources/class.templates.inc');
include_once('ressources/class.html.pages.inc');
include_once('ressources/class.system.network.inc');
include_once('ressources/class.os.system.inc');


$users=new usersMenus();
if(!$users->AsSystemAdministrator){die();}

if(isset($_GET["popup"])){popup();exit;}
if(isset($_POST["netbiosname"])){ChangeHostName();exit;}

js();


function js(){
	$tpl=new templates();
	$page=CurrentPageName();
	$users=new usersMenus();
	$sock=new sockets();
	$hostname=trim($sock->GET_INFO("myhostname"));
	if($hostname==null){$hostname=$users->fqdn;}
	$title=$tpl->_ENGINE_parse_body("{change_server_hostname}&nbsp;&raquo;&nbsp;$hostname");
	$html="YahooWinBrowse(470,'$page?popup=yes','$title')";
	echo $html;
	
}

function popup(){
	
	$tpl=new templates();
	$page=CurrentPageName();
	$sock=new sockets();
	$hostname=base64_decode($sock->getFrameWork("network.php?fqdn=yes"));
	$mustchangeHostname=false;
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	$DisableNetworksManagement=$sock->GET_INFO("DisableNetworksManagement");
	if(!is_numeric($DisableNetworksManagement)){$DisableNetworksManagement=0;}	
	if(preg_match("#Name or service not known#", $hostname)){$mustchangeHostname=true;$hostname=null;}
	if(preg_match("#locahost\.localdomain#", $hostname)){$mustchangeHostname=true;}
	if(preg_match("#[A-Za-z]+\s+[A-Za-z]+#", $hostname)){$mustchangeHostname=true;}
	$explain="{change_server_hostname}";
	if($mustchangeHostname){$explain="{your_hostname_is_not_correct}";}
	$netbiosname_field=$tpl->javascript_parse_text("{netbiosname}");
	$domain_field=$tpl->javascript_parse_text("{domain}");
	if($hostname==null){
		$users=new usersMenus();
		$hostname=$users->fqdn;
	}	
	
	
	if(strpos($hostname, '.')>0){
		$Thostname=explode(".", $hostname);
		$netbiosname=$Thostname[0];
		unset($Thostname[0]);
		$domainname=@implode(".", $Thostname);
	}else{
		$netbiosname=$hostname;
	}
	
	if(preg_match("#[A-Za-z]+\s+[A-Za-z]+#", $netbiosname)){$netbiosname=null;}
	
	$html="
	<div id='chhostdiv'>
	<div class=explain>$explain</div>
	<table style='width:100%' class=form>
	<tr>
		<td class=legend style='font-size:16px'>{netbiosname}:</td>
		<td>". Field_text("hostname_netbios",$netbiosname,"font-size:16px;width:220px",null,null,null,false,"ChangeQuickHostnameCheck(event)")."</td>
	</tr>
	</tr>
		<td class=legend style='font-size:16px'>{domain}:</td>
		<td>". Field_text("hostname_domain",$domainname,"font-size:16px;width:220px",null,null,null,false,"ChangeQuickHostnameCheck(event)")."&nbsp;<input type='button' OnClick=\"javascript:Loadjs('browse.domains.php?field=hostname_domain')\" value='{browse}...'></td>
	</tr>
	<tr>
		<td colspan=5 align='right'><hr>". button("{apply}","ChangeQuickHostname()")."</td>
	</tr>
	</table>
	</div>
	<script>
		var X_ChangeQuickHostname= function (obj) {
			var results=obj.responseText;
			if(document.getElementById('admin_perso_tabs')){RefreshTab('admin_perso_tabs');}		
			YahooWinBrowseHide();
			}
			
		function ChangeQuickHostnameCheck(e){
			if(checkEnter(e)){ChangeQuickHostname();}
		}

		
		function ChangeQuickHostname(){
			var XHR = new XHRConnection();
			var DisableNetworksManagement=$DisableNetworksManagement;
			if(DisableNetworksManagement==1){alert('$ERROR_NO_PRIVS');return;}
			var netbios=document.getElementById('hostname_netbios').value;
			var dom=document.getElementById('hostname_domain').value;
			if(netbios.length==0){alert('$netbiosname_field (Null!)');return;}
			if(dom.length==0){alert('$domain_field (Null!)');return;}
			if(dom=='localhost.localdomain'){alert('localhost.localdomain wrong domain...');return;}
			if(document.getElementById('hostnameInFront')){document.getElementById('hostnameInFront').innerHTML=netbios+'.'+dom;}
			
			XHR.appendData('netbiosname',netbios);
			XHR.appendData('domain',dom);
			AnimateDiv('chhostdiv');
			XHR.sendAndLoad('$page', 'POST',X_ChangeQuickHostname);
			
		}	
	
	
	</script>
	
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
}


function ChangeHostName(){
	
	$sock=new sockets();
	$tpl=new templates();
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	$DisableNetworksManagement=$sock->GET_INFO("DisableNetworksManagement");
	if($DisableNetworksManagement==null){$DisableNetworksManagement=0;}	
	if($DisableNetworksManagement==1){echo $ERROR_NO_PRIVS;}
	
	
	$tpl=new templates();
	$newhost="{$_POST["netbiosname"]}.{$_POST["domain"]}";	
	$sock=new sockets();
	$sock->SET_INFO("myhostname",$newhost);
	$sock->getFrameWork("cmd.php?ChangeHostName=$newhost");
	
	$users=new usersMenus();
	if($users->POSTFIX_INSTALLED){$sock->getFrameWork("cmd.php?postfix-others-values=yes");}
	
	
	
}

//du -cshB K /var/*
