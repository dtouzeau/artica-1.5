<?php
	if(isset($_GET["verbose"])){$GLOBALS["VERBOSE"]=true;ini_set('html_errors',1);ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.auto-aliases.inc');
	include_once('ressources/class.system.network.inc');
	
	
	
	
	if(!VerifyRights()){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_GET["config"])){config();exit;}
	
	if(isset($_GET["config-local"])){config_local();exit;}
	if(isset($_GET["EditLocalDomain"])){config_local_edit();exit;}
	
	if(isset($_GET["trusted_smtp_domain"])){trusted_smtp_domain_save();exit;}
	if(isset($_GET["remote"])){save_routage();exit;}
	
	
	if(isset($_GET["duplicate"])){duplicate();exit;}
	if(isset($_GET["duplicate-server"])){duplicate_save();exit;}
	if(isset($_GET["duplicate-delete"])){duplicate_delete();exit;}
	
	
	
	if(isset($_GET["aliases"])){aliases();exit;}
	if(isset($_GET["aliases-database"])){aliases_database();exit;}
	if(isset($_GET["aliases-import"])){aliases_import_form();exit;}
	if(isset($_POST["aliases-import-perform"])){aliases_import_perform();exit;}
	if(isset($_POST["aliase-delete-perform"])){aliases_delete_perform();exit;}
	if(isset($_POST["aliase-empty-perform"])){aliases_empty_perform();exit;}
	
	
	
	
	
	if(isset($_GET["users"])){users();exit;}
	if(isset($_GET["users-database"])){users_database();exit;}
	if(isset($_GET["users-import"])){users_import_form();exit;}
	if(isset($_POST["users-import-perform"])){users_import_perform();exit;}
	if(isset($_POST["users-delete-perform"])){users_delete_perform();exit;}
	if(isset($_POST["users-empty-perform"])){users_empty_perform();exit;}
	
	
	
	js();
	
	
	
function js(){
	$domain=$_GET["domain"];
	$_GET["ou"]=urlencode($_GET["ou"]);
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{relay_domain_map}::$domain");
	if(isset($_GET["local"])){
		$title=$tpl->_ENGINE_parse_body("{local_domain_map}::$domain");
	}
	
	
	
	$html="YahooWin2(600,'$page?tabs=yes&ou={$_GET["ou"]}&domain=$domain&local={$_GET["local"]}','$title');";
	echo $html;	
}

function tabs(){
	$domain=$_GET["domain"];
	$_GET["ou"]=urlencode($_GET["ou"]);	
	
	$page=CurrentPageName();
	$tpl=new templates();
	
	if($_GET["local"]=="yes"){
		$array["config-local"]="{parameters}";
		$array["catch-all"]="{catch_all}";
	}else{
		$array["config"]="{routing_rule}";
	}
	
	$array["aliases"]="{aliases}";
	$array["duplicate"]="{duplicate_domain}";
	$array["antispam"]="{spam_rules}";
	
	
	
	
	while (list ($num, $ligne) = each ($array) ){
			if($num=="antispam"){
				$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"domains.amavis.php?domain=".urlencode($domain)."&ou={$_GET["ou"]}&bytabs=yes\"><span>$ligne</span></a></li>\n");
				continue;
			}
			if($num=="catch-all"){
				$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"domains.catchall.php?domain=".urlencode($domain)."&ou={$_GET["ou"]}&bytabs=yes\"><span>$ligne</span></a></li>\n");
				continue;
			}
					
			$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&domain=".urlencode($domain)."&ou={$_GET["ou"]}\"><span>$ligne</span></a></li>\n");
			continue;
		
	}
	
	
	echo "
	<div id=main_config_relay_domain style='width:100%;height:650px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_config_relay_domain\").tabs();});
		</script>";		
}

function config_local(){
	$page=CurrentPageName();
	$tpl=new templates();		
	$num=$_GET["domain"];
	$ou=$_GET["ou"];
	
	$autoalias=new AutoAliases($ou);
	if(strlen($autoalias->DomainsArray[$num])>0){$alias="1";}
	$autoalias_p=Paragraphe_switch_img("{autoaliases}","{autoaliases_text}","{$num}_autoaliases",$alias,'{enable_disable}',500);
	$catchall=Paragraphe("64-catch-all.png","{catch_all}","{catch_all_mail_text}","javascript:Loadjs('domains.catchall.php?domain=$num&ou=$ou')");
	
	$html="
	$autoalias_p
	<hr>
	<div style='text-align:right'>".button("{apply}","EditLocalDomainNew('$num')")."</div>	

	<script>
	
		var x_EditLocalDomainNew= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>0){alert(tempvalue)};
			RefreshTab('main_config_relay_domain');
		}		
			
	
	function EditLocalDomainNew(domain){
		var XHR = new XHRConnection();
		XHR.appendData('EditLocalDomain','$num');
		XHR.appendData('ou','$ou');
		XHR.appendData('autoaliases',document.getElementById('{$num}_autoaliases').value);
		XHR.sendAndLoad('$page', 'GET',x_EditLocalDomainNew);
	 }
		
</script>
	

	
	
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}

function config_local_edit(){
	$domain=$_GET["EditLocalDomain"];
	$ou=$_GET["ou"];
	
	//Save Autoaliases.
	$autoaliases=new AutoAliases($ou);
	if($_GET["autoaliases"]=="1"){$autoaliases->DomainsArray[$domain]=$domain;}else{unset($autoaliases->DomainsArray[$domain]);}
	if(!$autoaliases->Save()){
		echo "Failed...";
		return;
	}
	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-transport-maps=yes");	
	
}



function duplicate(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$ldap=new clladp();	
	$ou=$_GET["ou"];
	$domain=$_GET["domain"];
	$q=new mysql();
	$sql="SELECT * FROM postfix_duplicate_maps WHERE `pattern`='$domain'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	
	if($ligne["pattern"]<>null){
		$delete=imgtootltip("delete-64.png","{delete}","DuplicateDelete()");
	}
	
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
	if(!is_numeric($ligne["port"])){$ligne["port"]=25;}
	$html="
	<div style='font-size:16px'>{$_GET["domain"]}</div>
	<table style='width:100%'>
	<tr>
	<td width=99%'>
		<div class=explain id='div-duplicate-{$_GET["domain"]}'>{duplicate_domain_explain}</div>
	</td>
	<td width=1%>$delete</td>
	</tr>
	</table>
	<p>&nbsp;</p>
	<table style='width:100%'>
		<tr>
		<td class=legend style='font-size:16px'>{target_computer_name}:</td>
		<td style='font-size:16px'>". Field_text("duplicate-computer-name",$ligne["relay"],"font-size:16px;width:220px")."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend style='font-size:16px'>{port}:</td>
		<td style='font-size:16px'>". Field_text("duplicate-remote-port",$ligne["port"],"font-size:16px;width:60px;text-align:right")."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend style='font-size:16px'>{destination_domain}:</td>
		<td style='font-size:16px'>". Field_text("duplicate-destination-domain",$ligne["nextdomain"],"font-size:16px;width:220px;text-align:right")."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td colspan=3 align='right'><hr>". button("{apply}","SaveDuplicateDomainNew()")."</td>
	</tr>
	</table>
	<script>
		var x_SaveDuplicateDomainNew= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>0){alert(tempvalue)};
			RefreshTab('main_config_relay_domain');
		}		
		
		


	function SaveDuplicateDomainNew(){
			var XHR = new XHRConnection();
			XHR.appendData('duplicate-server',document.getElementById('duplicate-computer-name').value);
			XHR.appendData('duplicate-port',document.getElementById('duplicate-remote-port').value);
			XHR.appendData('duplicate-destination-domain',document.getElementById('duplicate-destination-domain').value);
			XHR.appendData('ou','$ou');
			XHR.appendData('domain','$domain');
			document.getElementById('div-duplicate-{$_GET["domain"]}').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_SaveDuplicateDomainNew);		
	
	}	
	
	function DuplicateDelete(){
			var XHR = new XHRConnection();
			XHR.appendData('duplicate-delete','yes');
			XHR.appendData('ou','$ou');
			XHR.appendData('domain','$domain');
			document.getElementById('div-duplicate-{$_GET["domain"]}').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_SaveDuplicateDomainNew);	
	}
	
	</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}

function duplicate_save(){
	$ou=$_GET["ou"];
	$domain=$_GET["domain"];
	$q=new mysql();
	$sql="DELETE FROM  postfix_duplicate_maps WHERE `pattern`='$domain'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	if(!$q->ok){echo "$q->mysql_error";return;}
	$_GET["duplicate-destination-domain"]=trim(strtolower($_GET["duplicate-destination-domain"]));
	$sql="INSERT INTO postfix_duplicate_maps (`pattern`,`relay`,`port`,`ou`,`nextdomain`) VALUES
	('$domain','{$_GET["duplicate-server"]}','{$_GET["duplicate-port"]}','$ou','{$_GET["duplicate-destination-domain"]}')";
	
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "$q->mysql_error";return;}
	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-bcc-tables=yes");
}

function duplicate_delete(){
	
	$sql="DELETE FROM postfix_duplicate_maps WHERE pattern='{$_GET["domain"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");	
	
}


function config(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$ldap=new clladp();	
	$ou=$_GET["ou"];
	$domain=$_GET["domain"];
	$HashDomains=$ldap->Hash_relay_domains($_GET["ou"]);
	$routage=$HashDomains[$_GET["domain"]];
	$tools=new DomainsTools();
	$arr=$tools->transport_maps_explode($routage);
	
	$dn="cn=@{$_GET["domain"]},cn=relay_recipient_maps,ou=$ou,dc=organizations,$ldap->suffix";
	if($ldap->ExistsDN($dn)){
		$trusted_smtp_domain=1;
	}
	

	$html="
	<div style='font-size:16px'>{$_GET["domain"]}</div>
	<div class=explain id='div-{$_GET["domain"]}'>{relaydomain_explain}</div>
	<p>&nbsp;</p>
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:16px'>{target_computer_name}:</td>
		<td style='font-size:16px'>". Field_text("target_computer_name","{$arr[1]}","font-size:16px;width:220px")."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend style='font-size:16px'>{port}:</td>
		<td style='font-size:16px'>". Field_text("remote-port","{$arr[2]}","font-size:16px;width:60px;text-align:right")."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:16px'>{trusted_smtp_domain}:</td>
		<td style='font-size:16px'>". Field_checkbox("trusted_smtp_domain",1,$trusted_smtp_domain,"trusted_smtp_domain_save()")."</td>
		<td>". help_icon("{trusted_smtp_domain_text}")."</td>
	</tr>	
	
	
	<tr>
		<td colspan=3 align='right'><hr>". button("{apply}","SaveRelayDomainNew()")."</td>
	</tr>
	</table>
	
	
	<script>
		var x_trusted_smtp_domain_save= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>0){alert(tempvalue)};
			if (document.getElementById('RelayDomainsList')){
				LoadAjax('RelayDomainsList','domains.edit.domains.php?RelayDomainsList=yes&ou=$ou');
			}
			
			RefreshTab('main_config_relay_domain');
		}
		
		
		var x_SaveRelayDomainNew= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>0){alert(tempvalue)};
			if (document.getElementById('RelayDomainsList')){
				LoadAjax('RelayDomainsList','domains.edit.domains.php?RelayDomainsList=yes&ou=$ou');
			}
			RefreshTab('main_config_relay_domain');
		}		
		
		


	function SaveRelayDomainNew(){
			var XHR = new XHRConnection();
			XHR.appendData('remote',document.getElementById('target_computer_name').value);
			XHR.appendData('port',document.getElementById('remote-port').value);
			XHR.appendData('ou','$ou');
			XHR.appendData('domain','$domain');
			document.getElementById('div-{$_GET["domain"]}').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_SaveRelayDomainNew);		
	
	}

	function trusted_smtp_domain_save(num){
			var XHR = new XHRConnection();
			if (document.getElementById('trusted_smtp_domain').checked){
			XHR.appendData('trusted_smtp_domain',1);}else{
			XHR.appendData('trusted_smtp_domain',0);}
			XHR.appendData('ou','$ou');
			XHR.appendData('domain','$domain');
			XHR.sendAndLoad('$page', 'GET',x_trusted_smtp_domain_save);	
			
		}	
	
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function trusted_smtp_domain_save(){
	$ldap=new clladp();
	$domain_name=$_GET["domain"];
	$ou=$_GET["ou"];
	$upd=array();
	$trusted_smtp_domain=$_GET["trusted_smtp_domain"];

	
	$dn="cn=relay_recipient_maps,ou=$ou,dc=organizations,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd['cn'][0]="relay_recipient_maps";
		$upd['objectClass'][0]='PostFixStructuralClass';
		$upd['objectClass'][1]='top';
		if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error;return;}
		unset($upd);		
		}	

	$dn="cn=@$domain_name,cn=relay_recipient_maps,ou=$ou,dc=organizations,$ldap->suffix";
	$ldap->ldap_delete($dn);		
	
	if($trusted_smtp_domain==1){	
		$upd['cn'][0]="@$domain_name";
		$upd['objectClass'][0]='PostfixRelayRecipientMaps';
		$upd['objectClass'][1]='top';
		if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error;return;}
	}

	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-transport-maps=yes");
	
	
}

function save_routage(){
	$relayPort=$_GET["port"];
	$relayIP=$_GET["remote"];
	$domain_name=trim($_GET["domain"]);
	$ou=$_GET["ou"];
	$tpl=new templates();
	$ldap=new clladp();
	
	if($relayIP=="127.0.0.1"){echo $tpl->javascript_parse_text("{NO_RELAY_TO_THIS_SERVER_EXPLAIN}");return;}

	$tc=new networking();
	$IPSAR=$tc->ALL_IPS_GET_ARRAY();	
	
	if(!preg_match("#[0-9]\.[0-9]+\.[0-9]+\.[0-9]+#",$relayIP)){
		$ip=gethostbyname($relayIP);
		while (list ($ip1, $ip2) = each ($IPSAR)){if($relayIP==$ip1){echo $tpl->javascript_parse_text("{NO_RELAY_TO_THIS_SERVER_EXPLAIN}");return;}}
	}else{
		while (list ($ip1, $ip2) = each ($IPSAR)){if($relayIP==$ip1){echo $tpl->javascript_parse_text("{NO_RELAY_TO_THIS_SERVER_EXPLAIN}");return;}}		
	}
	
	if(!$ldap->EditRelayDomain($ou,$domain_name,$relayIP,$relayPort)){return;}
	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-transport-maps=yes");
}

function aliases(){
$tpl=new templates();
	$page=CurrentPageName();
	$ou=$_GET["ou"];
	$domain=$_GET["domain"];
	$md5=md5($domain);
	$html="
	<div class=explain>{aliases_domains_explain}</div>
	
		<center>
		<table style='width:450px' class=form>
		<tr>
			<td class=legend valign='middle'>{search}:</td>
			<td valign='middle'>". Field_text("aliases-db-search",null,"font-size:14px;padding:3px;width:100%",null,null,null,false,
			"RefreshAliasesDatabase(event)")."</td>
			<td width=1% valign='middle'>". button("{search}","RefreshAliasesDatabase()")."</td>
		</tr>
		</table>
	</center>
	
	
	<div id='aliases_$md5' style='width:100%;height:450px;overflow:auto'></div>
	
	
	<script>
		function RefreshAliasesDatabaseCheck(e){
			if(checkEnter(e)){RefreshAliasesDatabase();}
		
		}
	
	
		function RefreshAliasesDatabase(){
			var se=escape(document.getElementById('aliases-db-search').value);
			LoadAjax('aliases_$md5','$page?aliases-database=yes&search='+se+'&domain=".urlencode($domain)."&ou=".urlencode($_GET["ou"])."');
		
		}
		
		function ImportAliaseDomain(){
			YahooWin4('550','$page?aliases-import=yes&domain=".urlencode($domain)."&ou=".urlencode($_GET["ou"])."');
		
		}
		
		var x_aliase_delete= function (obj) {
			var tempvalue=obj.responseText;
			RefreshAliasesDatabase();
		}			
	
		function aliase_delete(alias){
			var XHR = new XHRConnection();
			XHR.appendData('aliase-delete-perform',alias);
			XHR.appendData('ou','$ou');
			XHR.appendData('domain','$domain');
			XHR.sendAndLoad('$page', 'POST',x_aliase_delete);				
		}	

		function EmptyAliaseDomain(){
			var XHR = new XHRConnection();
			XHR.appendData('aliase-empty-perform','yes');
			XHR.appendData('ou','$ou');
			XHR.appendData('domain','$domain');
			XHR.sendAndLoad('$page', 'POST',x_aliase_delete);		
		
		}
		
		RefreshAliasesDatabase();
	</script>
	
	
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}
function users(){
	$tpl=new templates();
	$page=CurrentPageName();
	$ou=$_GET["ou"];
	$domain=$_GET["domain"];
	$md5=md5($domain);
	$html="
		<center>
		<table style='width:450px' class=form>
		<tr>
			<td class=legend valign='middle'>{search}:</td>
			<td valign='middle'>". Field_text("users-db-search",null,"font-size:14px;padding:3px;width:100%",null,null,null,false,
			"RefreshUserDatabaseCheck(event)")."</td>
			<td width=1% valign='middle'>". button("{search}","RefreshUserDatabase()")."</td>
		</tr>
		</table>
	</center>
	
	
	<div id='users_$md5' style='width:100%;height:450px;overflow:auto'></div>
	
	
	<script>
		function RefreshUserDatabaseCheck(e){
			if(checkEnter(e)){RefreshUserDatabase();}
		
		}
	
	
		function RefreshUserDatabase(){
			var se=escape(document.getElementById('users-db-search').value);
			LoadAjax('users_$md5','$page?users-database=yes&search='+se+'&domain=".urlencode($domain)."&ou=".urlencode($_GET["ou"])."');
		
		}
		
		function ImportUsersRelayDomain(){
			YahooWin4('550','$page?users-import=yes&domain=".urlencode($domain)."&ou=".urlencode($_GET["ou"])."');
		
		}
		
		var x_DeleteUsersIntoDB= function (obj) {
			var tempvalue=obj.responseText;
			RefreshUserDatabase();
		}			
	
		function DeleteUsersIntoDB(email){
			var XHR = new XHRConnection();
			XHR.appendData('users-delete-perform',email);
			XHR.appendData('ou','$ou');
			XHR.appendData('domain','$domain');
			XHR.sendAndLoad('$page', 'POST',x_DeleteUsersIntoDB);				
		}	

		function EmptyUsersRelayDomain(){
			var XHR = new XHRConnection();
			XHR.appendData('users-empty-perform','yes');
			XHR.appendData('ou','$ou');
			XHR.appendData('domain','$domain');
			XHR.sendAndLoad('$page', 'POST',x_DeleteUsersIntoDB);		
		
		}
		
		RefreshUserDatabase();
	</script>
	
	
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function aliases_database(){
$tpl=new templates();
	$page=CurrentPageName();
	$ou=$_GET["ou"];
	$domain=$_GET["domain"];	
	$ldap=new clladp();
	$trusted_smtp_domain=0;
	$search=$_GET["search"];
	$search="*".$_GET["search"]."*";
	$search=str_replace("**","*",$search);
	$search=str_replace("*","%",$search);	

	
$sql="SELECT `alias` FROM postfix_aliases_domains WHERE
	`ou`='$ou' AND `domain`='$domain'
	AND `alias` LIKE '$search' ORDER BY `alias` LIMIT 0,90";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}	
	
$html="
<p>&nbsp;</p>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>". imgtootltip("plus-24.png","{import}","ImportAliaseDomain()")."</th>
		<th>{aliases}</th>
		<th>". imgtootltip("delete-32.png","{empty_database}","EmptyAliaseDomain()")."</th>
	</tr>
</thead>
<tbody class='tbody'>";


	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	$html=$html."
	<tr  class=$classtr>
	<td style='font-size:14px;font-weight:bold'><img src=img/fw_bold.gif></td>
	<td style='font-size:14px;font-weight:bold'>{$ligne["alias"]}</a></td>
	<td width=1%>". imgtootltip("delete-24.png","{delete}","aliase_delete('{$ligne["alias"]}')")."</td>
	</tR>";

	}
	
	
	$html=$html."</table>\n";


echo $tpl->_ENGINE_parse_body($html);return;
		
	
}


function users_database(){
	$tpl=new templates();
	$page=CurrentPageName();
	$ou=$_GET["ou"];
	$domain=$_GET["domain"];	
	$ldap=new clladp();
	$trusted_smtp_domain=0;
	$search=$_GET["search"];
	$search="*".$_GET["search"]."*";
	$search=str_replace("**","*",$search);
	$search=str_replace("*","%",$search);	
	$dn="cn=@{$_GET["domain"]},cn=relay_recipient_maps,ou=$ou,dc=organizations,$ldap->suffix";
	if($ldap->ExistsDN($dn)){$trusted_smtp_domain=1;}
	
	if($trusted_smtp_domain==1){
		$html="<div class=explain>{DOMAIN_TRUSTED_NO_USERDB_TEXT}</div>";
		echo $tpl->_ENGINE_parse_body($html);return;
	}
	
$sql="SELECT `email` FROM postfix_relais_domains_users WHERE
	`ou`='$ou' AND `domain`='$domain'
	AND `email` LIKE '$search' ORDER BY email LIMIT 0,90";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}	
	
$html="
<p>&nbsp;</p>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>". imgtootltip("plus-24.png","{import}","ImportUsersRelayDomain()")."</th>
		<th>{email}</th>
		<th>". imgtootltip("delete-32.png","{empty_database}","EmptyUsersRelayDomain()")."</th>
	</tr>
</thead>
<tbody class='tbody'>";


	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	$html=$html."
	<tr  class=$classtr>
	<td style='font-size:14px;font-weight:bold'><img src=img/fw_bold.gif></td>
	<td style='font-size:14px;font-weight:bold'>{$ligne["email"]}</a></td>
	<td width=1%>". imgtootltip("delete-24.png","{delete}","POSTFIX_MULTI_INSTANCE_INFOS_DEL('{$ligne["ou"]}','{$ligne["ip_address"]}')")."</td>
	</tR>";

	}
	
	
	$html=$html."</table>\n";


echo $tpl->_ENGINE_parse_body($html);return;
	
}

function users_import_form(){
	$tpl=new templates();
	$page=CurrentPageName();
	$ou=$_GET["ou"];
	$domain=$_GET["domain"];	
	$importing=$tpl->javascript_parse_text("{importing}");
	$html="
	<div class=explain>{DOMAIN_NO_TRUSTED_IMPORT_TEXT}</div>
	<p>&nbsp;</p>
	<div style='text-align:right;width:100%'>". button("{import}","ImportUsersIntoDB()")."</div><hr>
	<textarea style='width:100%;height:350px;overflow:auto' id='domain-relay-import-field'></textarea>
	<hr>
	<div style='text-align:right;width:100%'>". button("{import}","ImportUsersIntoDB()")."</div>
	
	<script>
		var x_ImportUsersIntoDB= function (obj) {
			var tempvalue=obj.responseText;
			document.getElementById('domain-relay-import-field').value=tempvalue;
			RefreshUserDatabase();
		}			
	
		function ImportUsersIntoDB(){
			var XHR = new XHRConnection();
			XHR.appendData('users-import-perform',document.getElementById('domain-relay-import-field').value);
			XHR.appendData('ou','$ou');
			XHR.appendData('domain','$domain');
			document.getElementById('domain-relay-import-field').value='$importing';
			XHR.sendAndLoad('$page', 'POST',x_ImportUsersIntoDB);				
		}
	
	
	</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
}


function aliases_import_form(){
	
	$tpl=new templates();
	$page=CurrentPageName();
	$ou=$_GET["ou"];
	$domain=$_GET["domain"];	
	$importing=$tpl->javascript_parse_text("{importing}");
	$html="
	<div class=explain>{DOMAIN_ALIASES_IMPORT_TEXT}</div>
	<p>&nbsp;</p>
	<div style='text-align:right;width:100%'>". button("{import}","ImportAliasesIntoDB()")."</div><hr>
	<textarea style='width:100%;height:350px;overflow:auto' id='domain-relay-import-field'></textarea>
	<hr>
	<div style='text-align:right;width:100%'>". button("{import}","ImportAliasesIntoDB()")."</div>
	
	<script>
		var x_ImportAliasesIntoDB= function (obj) {
			var tempvalue=obj.responseText;
			document.getElementById('domain-relay-import-field').value=tempvalue;
			RefreshAliasesDatabase();
		}			
	
		function ImportAliasesIntoDB(){
			var XHR = new XHRConnection();
			XHR.appendData('aliases-import-perform',document.getElementById('domain-relay-import-field').value);
			XHR.appendData('ou','$ou');
			XHR.appendData('domain','$domain');
			document.getElementById('domain-relay-import-field').value='$importing';
			XHR.sendAndLoad('$page', 'POST',x_ImportAliasesIntoDB);				
		}
	
	
	</script>
	";
	echo $tpl->_ENGINE_parse_body($html);	
}

function aliases_import_perform(){
	$tpl=new templates();
	$page=CurrentPageName();
	$ou=$_POST["ou"];
	$domain=$_POST["domain"];
	$tbl=explode("\n",$_POST["aliases-import-perform"]);
	$failed=0;
	$prefix="INSERT IGNORE INTO postfix_aliases_domains (`domain`,`ou`,`alias`) VALUES ";
	
	while (list ($index, $aliases) = each ($tbl)){
		$aliases=trim(strtolower($aliases));
		if($aliases==null){continue;}
		$t[]="('$domain','$ou','$aliases')";
		
	}
	if(count($t)>0){
		$sql=$prefix.@implode(",",$t);
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){
			echo $q->mysql_error;
			return;
		}	
	}
	
	echo $tpl->javascript_parse_text("{failed}: $failed\n{success}:".count($t)."",1);
	$sock=new sockets();
	//$sock->getFrameWork("cmd.php?postfix-hash-aliases=yes");	
	
}

function users_import_perform(){
	$tpl=new templates();
	$page=CurrentPageName();
	$ou=$_POST["ou"];
	$domain=$_POST["domain"];
	$domainp=str_replace(".","\.",$domain);		
	$tbl=explode("\n",$_POST["users-import-perform"]);
	$failed=0;
	$prefix="INSERT IGNORE INTO postfix_relais_domains_users (`domain`,`ou`,`email`) VALUES ";
	
	while (list ($index, $email) = each ($tbl)){
		$email=trim($email);
		if($email==null){continue;}
		if(!preg_match("#^.+?@#",$email)){$email="$email@$domain";}
		if(!preg_match("#(.+?)@$domainp#",$email)){$failed++;continue;}
		$t[]="('$domain','$ou','$email')";
		
	}
	if(count($t)>0){
		$sql=$prefix.@implode(",",$t);
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){
			echo $q->mysql_error;
			return;
		}	
	}
	
	echo $tpl->javascript_parse_text("{failed}: $failed\n{success}:".count($t)."",1);
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-hash-aliases=yes");	
	
}

function users_delete_perform(){
	$tpl=new templates();
	$page=CurrentPageName();
	$ou=$_POST["ou"];
	$domain=$_POST["domain"];	
	$sql="	DELETE FROM postfix_relais_domains_users WHERE `ou`='$ou' AND `domain`='$domain'
	AND `email`='{$_POST["users-delete-perform"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		echo $q->mysql_error;
		return;
	}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-hash-aliases=yes");			
	
}

function aliases_delete_perform(){
	$tpl=new templates();
	$page=CurrentPageName();
	$ou=$_POST["ou"];
	$domain=$_POST["domain"];	
	$sql="	DELETE FROM postfix_aliases_domains WHERE `ou`='$ou' AND `domain`='$domain'
	AND `alias`='{$_POST["aliase-delete-perform"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		echo $q->mysql_error;
		return;
	}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-hash-aliases=yes");		
}

function aliases_empty_perform(){
	$tpl=new templates();
	$page=CurrentPageName();
	$ou=$_POST["ou"];
	$domain=$_POST["domain"];	
	$sql="	DELETE FROM postfix_aliases_domains WHERE `ou`='$ou' AND `domain`='$domain'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		echo $q->mysql_error;
		return;
	}	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-hash-aliases=yes");		
}


function users_empty_perform(){
	$tpl=new templates();
	$page=CurrentPageName();
	$ou=$_POST["ou"];
	$domain=$_POST["domain"];	
	$sql="	DELETE FROM postfix_relais_domains_users WHERE `ou`='$ou' AND `domain`='$domain'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		echo $q->mysql_error;
		return;
	}	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-hash-aliases=yes");		
}

	
	
function VerifyRights(){
	$usersmenus=new usersMenus();
	if($usersmenus->AsMessagingOrg){return true;}
	if(!$usersmenus->AllowChangeDomains){return false;}
}	
