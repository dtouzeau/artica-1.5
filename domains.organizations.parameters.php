<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.main_cf.inc');
	include_once('ressources/class.mysql.inc');

	
	$users=new usersMenus();
	if(!$users->AsArticaAdministrator){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_GET["params"])){main_params();exit;}
	if(isset($_GET["domains"])){main_domains();exit;}
	if(isset($_GET["officals-domains-list"])){main_domains_list();exit;}
	if(isset($_POST["officals-domains-add"])){main_domains_add();exit;}
	if(isset($_POST["officals-domains-del"])){main_domains_del();exit;}
	if(isset($_POST["AllowInternetUsersCreateOrg"])){main_params_save();exit;}
	
	if(isset($_GET["AllowInternetUsersCreateOrg-text"])){UsersCreateOrg_popup();exit;}
	if(isset($_POST["UsersCreateOrgWelComeText"])){UsersCreateOrg_save();exit;}
	
	
	if(isset($_GET["ou-sql-js"])){ou_sql_js();exit;}
	if(isset($_GET["ou-sql-popup"])){ou_sql_popup();exit;}
	
	
	
	js();

function ou_sql_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql();
	$sql="SELECT ou FROM register_orgs WHERE `zmd5`='{$_GET["ou-sql-js"]}'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$title=$tpl->_ENGINE_parse_body("{organization}:{$ligne["ou"]}");
	echo "YahooWin3('550','$page?ou-sql-popup={$_GET["ou-sql-js"]}','$title')";
}	

function ou_sql_popup(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$q=new mysql();
	$sql="SELECT * FROM register_orgs WHERE `zmd5`='{$_GET["ou-sql-popup"]}'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
	$html="
	<div class=explain>{waiting_user_confirmation}</div>
	<table style='width:100%' class=form>
		<tr>
			<td class=legend style='font-size:13px;'>{register_date}:</td>
			<td style='font-size:13px;font-weight:bold'>{$ligne["register_date"]}</td>
		</tr>	
		<tr>
			<td class=legend style='font-size:13px;'>{company}:</td>
			<td style='font-size:13px;font-weight:bold'>{$ligne["company"]}</td>
		</tr>
		<tr>
			<td class=legend style='font-size:13px;'>{organization}:</td>
			<td style='font-size:13px;font-weight:bold'>{$ligne["ou"]}</td>
		</tr>
		<tr>
			<td class=legend style='font-size:13px;'>{domain}:</td>
			<td style='font-size:13px;font-weight:bold'>{$ligne["domain"]}</td>
		</tr>		
		
		<tr>
			<td class=legend style='font-size:13px;'>{username}:</td>
			<td style='font-size:13px;font-weight:bold'>{$ligne["username"]}</td>
		</tr>
		<tr>
			<td class=legend style='font-size:13px;'>{password}:</td>
			<td style='font-size:13px;font-weight:bold'>******</td>
		</tr>
		<tr>
			<td class=legend style='font-size:13px;'>{original_mail}:</td>
			<td style='font-size:13px;font-weight:bold'>{$ligne["email"]}</td>
		</tr>
		</table>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{organizations_parameters}");
	echo "YahooWin2('670','$page?tabs=yes','$title')";
}

function tabs(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$array["params"]="{parameters}";
	$array["domains"]="{domains}";
		while (list ($num, $ligne) = each ($array) ){
			$html[]= "<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n";
	}
	
	
	echo $tpl->_ENGINE_parse_body("
	<div id=main_config_allorgs style='width:100%;height:550px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_config_allorgs\").tabs();});
		</script>");	
	
}

function main_params_save(){

	$sock=new sockets();
	$sock->SET_INFO("AllowInternetUsersCreateOrg",$_POST["AllowInternetUsersCreateOrg"]);
	$sock->SET_INFO("RobotInternetUsers",$_POST["RobotInternetUsers"]);
	$sock->SET_INFO("EnablePublicOrganizations",$_POST["EnablePublicOrganizations"]);
	$sock->SET_INFO("InternetDomainsAsOnlySubdomains",$_POST["InternetDomainsAsOnlySubdomains"]);
	$sock->SET_INFO("AddInArticaLogonFrontPage",$_POST["AddInArticaLogonFrontPage"]);
	$sock->SET_INFO("FreewebsStorageDirectory",$_POST["FreewebsStorageDirectory"]);
	
	

}

function main_params(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();
	$user=new usersMenus();
	$AllowInternetUsersCreateOrg=$sock->GET_INFO("AllowInternetUsersCreateOrg");
	$InternetDomainsAsOnlySubdomains=$sock->GET_INFO("InternetDomainsAsOnlySubdomains");
	$EnablePublicOrganizations=$sock->GET_INFO("EnablePublicOrganizations");
	$AddInArticaLogonFrontPage=$sock->GET_INFO("AddInArticaLogonFrontPage");
	$FreewebsStorageDirectory=$sock->GET_INFO("FreewebsStorageDirectory");
	if(!is_numeric($AllowInternetUsersCreateOrg)){$AllowInternetUsersCreateOrg=0;}
	if(!is_numeric($InternetDomainsAsOnlySubdomains)){$InternetDomainsAsOnlySubdomains=0;}
	if(!is_numeric($EnablePublicOrganizations)){$EnablePublicOrganizations=0;}
	if(!is_numeric($AddInArticaLogonFrontPage)){$AddInArticaLogonFrontPage=0;}
	$RobotInternetUsers=$sock->GET_INFO("RobotInternetUsers");
	if($RobotInternetUsers==null){
		$RobotInternetUsers="postmaster@$user->fqdn";
	}
	
	if($FreewebsStorageDirectory==null){$FreewebsStorageDirectory="/var/www";}
	
	$html="
	<div id='mainparamasorgs'>
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{AllowInternetUsersCreateOrg}:</td>
		<td>". Field_checkbox("AllowInternetUsersCreateOrg",1,$AllowInternetUsersCreateOrg,"CheckForm()")."</td>
		<td>". help_icon("{AllowInternetUsersCreateOrg_text}")."</td>
	</tr>
	<tr>
		<td colspan=2 align='right'><a href=\"javascript:blur();\" 
		OnClick=\"javascript:YahooWin5(550,'$page?AllowInternetUsersCreateOrg-text=yes','{welcome_registration_text}');\"
		style='font-size:12px;text-decoration:underline'
		><i>{welcome_registration_text}</i></a>
		<td>". help_icon("{AllowInternetUsersCreateOrg_welcome_explain}")."</td>
	</tr>
	<tr><td colspan=3>&nbsp;</td></tr>
	
	<tr>
	
		<td class=legend>{RobotInternetUsers}:</td>
		<td>". Field_text("RobotInternetUsers",$RobotInternetUsers,"font-size:13px;padding:3px")."</td>
		<td>". help_icon("{RobotInternetUsers_text}")."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend>{EnablePublicOrganizations}:</td>
		<td>". Field_checkbox("EnablePublicOrganizations",1,$EnablePublicOrganizations)."</td>
		<td>". help_icon("{EnablePublicOrganizations_text}")."</td>
		<td>&nbsp;</td>
	</tr>	
		
	<tr>
		<td class=legend>{InternetDomainsOrgAsOnlySubdomains}:</td>
		<td>". Field_checkbox("InternetDomainsAsOnlySubdomains",1,$InternetDomainsAsOnlySubdomains)."</td>
		<td>". help_icon("{InternetDomainsOrgAsOnlySubdomains_text}")."</td>
	</tr>
	<tr>
		<td class=legend>{AddInArticaLogonFrontPage}:</td>
		<td>". Field_checkbox("AddInArticaLogonFrontPage",1,$AddInArticaLogonFrontPage)."</td>
		<td>". help_icon("{AddInArticaLogonFrontPage_text}")."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend>{FreewebsStorageDirectory}:</td>
		<td>". Field_text("FreewebsStorageDirectory",$FreewebsStorageDirectory,"font-size:13px;padding:3px")."</td>
		<td><input type='button' OnClick=\"javascript:Loadjs('SambaBrowse.php?no-shares=yes&field=FreewebsStorageDirectory')\" value='{browse}...'></td>
		<td>". help_icon("{FreewebsStorageDirectory_text}")."</td>
		
	</tr>					
	
	<tr>
		<td colspan=4 align='right'><hr>". button("{apply}","SaveOrgsGenSettings()")."</td>
	</tr>
	</table>
	</div>
	<script>
	var x_SaveOrgsGenSettings= function (obj) {
		var results=obj.responseText;
		if(results.length>3){alert(results);}
		RefreshTab('main_config_allorgs');
	}

function SaveOrgsGenSettings(){
		var XHR = new XHRConnection();
		
		if(document.getElementById('AllowInternetUsersCreateOrg').checked){
			XHR.appendData('AllowInternetUsersCreateOrg',1);
		}else{
			XHR.appendData('AllowInternetUsersCreateOrg',0);
		}
		
		if(document.getElementById('InternetDomainsAsOnlySubdomains').checked){
			XHR.appendData('InternetDomainsAsOnlySubdomains',1);
		}else{
			XHR.appendData('InternetDomainsAsOnlySubdomains',0);
		}
		
		if(document.getElementById('EnablePublicOrganizations').checked){
			XHR.appendData('EnablePublicOrganizations',1);
		}else{
			XHR.appendData('EnablePublicOrganizations',0);
		}

		if(document.getElementById('AddInArticaLogonFrontPage').checked){
			XHR.appendData('AddInArticaLogonFrontPage',1);
		}else{
			XHR.appendData('AddInArticaLogonFrontPage',0);
		}			

		
		
		XHR.appendData('RobotInternetUsers',document.getElementById('RobotInternetUsers').value);
		XHR.appendData('FreewebsStorageDirectory',document.getElementById('FreewebsStorageDirectory').value);		

		
		
		AnimateDiv('mainparamasorgs');
		XHR.sendAndLoad('$page', 'POST',x_SaveOrgsGenSettings);

}

function CheckForm(){
	document.getElementById('RobotInternetUsers').disabled=true;
	if(document.getElementById('AllowInternetUsersCreateOrg').checked){
		document.getElementById('RobotInternetUsers').disabled=false;
	}
}

CheckForm();

</script>";
	echo $tpl->_ENGINE_parse_body($html);
	
}


function main_domains(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$domaintext=$tpl->javascript_parse_text("{domain}");
	$add_official_domains_popup_text=$tpl->javascript_parse_text("{add_official_domains_popup_text}");
	$html="
	<div class=explain>{maindomains_explain}</div>
	<p>&nbsp;</p>
	<center>
	<table style='width:70%' class=form>
	<tr>
		<td class=legend>{domains}:</td>
		<td>". Field_text("officals-domains-search",null,"font-size:14px;padding:3px",null,null,null,false,"OfficialsDomainsSearchCheck(event)")."</td>
		<td>". button("{search}","OfficialsDomainsSearch()")."</td>
	</tr>
	</table>
	</center>
	<div id='officals-domains-list' style='width:100%;height:450px;overflow:auto'>
	
	<script>
		function OfficialsDomainsSearchCheck(e){
			if(checkEnter(e)){OfficialsDomainsSearch();}
		}
		
		function OfficialsDomainsSearch(){
			var se=escape(document.getElementById('officals-domains-search').value);
			LoadAjax('officals-domains-list','$page?officals-domains-list=yes&search='+se);
		}
		
	var x_AddOfficialDomain= function (obj) {
		var results=obj.responseText;
		if(results.length>3){alert(results);}
		OfficialsDomainsSearch();
	}		
		
		function AddOfficialDomain(){
			var domain=prompt('$add_official_domains_popup_text:');
			if(domain){
				var XHR = new XHRConnection();
				XHR.appendData('officals-domains-add','yes');
				XHR.appendData('officals-domains',domain);	
				AnimateDiv('officals-domains-list');
				XHR.sendAndLoad('$page', 'POST',x_AddOfficialDomain);
			}
		}
		
		function DeleteOfficialDomains(domain){
			var XHR = new XHRConnection();
			XHR.appendData('officals-domains-del','yes');
			XHR.appendData('officals-domains',domain);
			AnimateDiv('officals-domains-list');		
			XHR.sendAndLoad('$page', 'POST',x_AddOfficialDomain);			
		}
		OfficialsDomainsSearch();
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function main_domains_add(){
	$q=new mysql();
	$_POST["officals-domains"]=trim(strtolower($_POST["officals-domains"]));
	if(strpos($_POST["officals-domains"],",")>0){
		$tbl=explode(",",$_POST["officals-domains"]);
		while (list ($num, $ligne) = each ($tbl) ){
			$ligne=trim(strtolower($ligne));
			if($ligne==null){continue;}
			$sql="INSERT IGNORE INTO officials_domains(domain) VALUES('$ligne')";
			$q->QUERY_SQL($sql,"artica_backup");
			if(!$q->ok){echo $q->mysql_error;return;}
		}
		
		return;
	}
	
	
	$sql="INSERT IGNORE INTO officials_domains(domain) VALUES('{$_POST["officals-domains"]}')";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;}
	
}

function main_domains_list(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$search=$_GET["search"];
	$search="*$search*";
	$search=str_replace("*","%",$search);
	$search=str_replace("%%","%",$search);
	$add=imgtootltip("plus-24.png","{add}","AddOfficialDomain()");
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>$add</th>
		<th colspan=3>{domains}</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	
	$q=new mysql();
	$sql="SELECT * FROM officials_domains WHERE domain LIKE '$search' ORDER BY domain";
	writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$delete=imgtootltip("delete-32.png","{delete}","DeleteOfficialDomains('{$ligne["domain"]}')");
		$html=$html."
		<tr class=$classtr>
			<td width=1%><img src='img/domain-32.png'></td>
			<td style='font-size:14px;font-weight:bold'>{$ligne["domain"]}</td>
			<td width=1%>$delete</td>
		</tr>
		";
	}
	
	$html=$html."</table>";
	echo $tpl->_ENGINE_parse_body($html);
}

function main_domains_del(){
	$_POST["officals-domains"]=trim(strtolower($_POST["officals-domains"]));
	$sql="DELETE FROM officials_domains WHERE domain='{$_POST["officals-domains"]}'";
		writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;}	
}

function UsersCreateOrg_popup(){
	$sock=new sockets();
	$UsersCreateOrgWelComeText=base64_decode($sock->GET_INFO("UsersCreateOrgWelComeText"));
	
	$tpl=new templates();
	$page=CurrentPageName();
	$html="
	<div class=explain>{AllowInternetUsersCreateOrg_welcome_explain}</div>
	<textarea style='width:100%;height:430px;overlfow:auto;font-size:14px' id='UsersCreateOrgWelComeText'>$UsersCreateOrgWelComeText</textarea>
	<div style='width:100%;text-align:right'>". button("{apply}","UsersCreateOrgWelComeTextSave()")."</div>
	<script>
	var x_UsersCreateOrgWelComeTextSave= function (obj) {
		var results=obj.responseText;
		if(results.length>3){alert(results);}
		YahooWin5Hide();
	}		
		

		function UsersCreateOrgWelComeTextSave(domain){
			var XHR = new XHRConnection();
			XHR.appendData('UsersCreateOrgWelComeText',document.getElementById('UsersCreateOrgWelComeText').value);
			XHR.sendAndLoad('$page', 'POST',x_UsersCreateOrgWelComeTextSave);			
		}	
</script>	";
	
	echo $tpl->_ENGINE_parse_body($html);
}

function UsersCreateOrg_save(){
	$sock=new sockets();
	$sock->SaveConfigFile(base64_encode(stripslashes($_POST["UsersCreateOrgWelComeText"])),"UsersCreateOrgWelComeText");
}


?>