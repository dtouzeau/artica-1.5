<?php
	if(isset($_GET["verbose"])){$GLOBALS["VERBOSE"]=true;ini_set('html_errors',1);ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.mysql.inc');

	
	if(!VerifyRights()){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}	
	
	if(isset($_GET["popup"])){popup();exit;}
	
	js();
	
	
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$ou=urlencode($_GET["ou"]);
	$add_a_subdomain=$tpl->_ENGINE_parse_body("{add_a_subdomain}");
	$html="YahooWin4(650,'$page?popup=yes&ou=$ou','$add_a_subdomain');";
	echo $html;
}
		
		
function popup(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$users=new usersMenus();
	$q=new mysql();
	$sql="SELECT * FROM officials_domains ORDER BY domain";
	writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$domains[$ligne["domain"]]=$ligne["domain"];
	}
	
	$OverWriteRestrictedDomains=0;
	if($users->OverWriteRestrictedDomains){$OverWriteRestrictedDomains=1;}
	
	
	if($OverWriteRestrictedDomains==1){
		$over="
		<hr>
		<div class=explain>{OverWriteRestrictedDomains_allow_text}</div>
		<table style='width:100%' class=form>
	<tr>
		<td class=legend>{domain}:</td>
		<td width=100%>". Field_text("overwrite-domain",null,"font-size:16px;padding:3px;width:320px")."</td>
	</tr>
	<tr>
		<td colspan=2 align='right'><hr>". button("{add}","AddSubdomainOUOver()")."</td>
	</tr>
	</table>
		";
		
	}
	
	$_GET["ou"]=urlencode($_GET["ou"]);
	$domains[null]="{select}";
	$html="
	<div id='add_subdomain_explain'></div>
	<div class=explain >{add_subdomain_explain}</div>
	
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{subdomain}:</td>
		<td width=50%>". Field_text("subdomain",null,"font-size:16px;padding:3px;width:220px")."</td>
		<td style='font-size:16px;padding:3px' width=1%><strong>.</strong></td>
		<td width=50%>". Field_array_Hash($domains,"maindomain",null,"style:font-size:16px;padding:3px;")."</td>
	</tr>
	<tr>
		<td colspan=4 align='right'><hr>". button("{add}","AddSubdomainOU()")."</td>
	</tr>
	</table>
	$over
	<script>
	var x_AddSubdomainOU= function (obj) {
		var results=obj.responseText;
		document.getElementById('subdomain').innerHTML='';
		if(results.length>3){alert(results);return;}
		YahooWin4Hide();
		LoadAjax('LocalDomainsList','domains.edit.domains.php?LocalDomainList=yes&ou={$_GET["ou"]}');
	}		
		
		function AddSubdomainOU(){
			var maindomain=document.getElementById('maindomain').value;
			var subdomain=document.getElementById('subdomain').value;
			if(maindomain.length<3){return;}
			if(subdomain.length<3){return;}
			var domain=subdomain+'.'+maindomain;
			var XHR = new XHRConnection();
			XHR.appendData('AddNewInternetDomain','{$_GET["ou"]}');
			XHR.appendData('AddNewInternetDomainDomainName',domain);	
			AnimateDiv('add_subdomain_explain');
			XHR.sendAndLoad('domains.edit.domains.php', 'GET',x_AddSubdomainOU);
			
		}
		
		function AddSubdomainOUOver(){
			var maindomain=document.getElementById('overwrite-domain').value;
			if(maindomain.length<3){return;}
			var XHR = new XHRConnection();
			XHR.appendData('AddNewInternetDomain','{$_GET["ou"]}');
			XHR.appendData('AddNewInternetDomainDomainName',maindomain);	
			AnimateDiv('add_subdomain_explain');
			XHR.sendAndLoad('domains.edit.domains.php', 'GET',x_AddSubdomainOU);			
		}

	</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}		
	

function VerifyRights(){
	$usersmenus=new usersMenus();
	if($usersmenus->AsMessagingOrg){return true;}
	if(!$usersmenus->AllowChangeDomains){return false;}
}




?>