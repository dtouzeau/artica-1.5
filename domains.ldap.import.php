<?php
	if(isset($_GET["verbose"])){$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.cron.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.ldap.ou.inc');

	
	if(!VerifyRights()){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["connexions-list"])){connexions_list();exit;}
	if(isset($_GET["connexion"])){connexion_form();exit;}
	if(isset($_POST["connection_name"])){connection_save();exit;}
	if(isset($_POST["DeleteID"])){connection_delete();exit;}
	if(isset($_GET["connection-status"])){connection_ldap_status();exit;}
	if(isset($_POST["ExecuteConnection"])){ExecuteConnection();exit;}
	
	js();
	
	
	
function js(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{$_GET["ou"]}:{ldap_importation}");
	$ou=urlencode($_GET["ou"]);
	$html="YahooWin3('650','$page?popup=yes&ou=$ou','$title')";
	echo $html;
	
}
	
	
	
	
function VerifyRights(){
	$usersmenus=new usersMenus();
	if(!$usersmenus->AsSystemAdministrator){$_GET["ou"]=$_SESSION["ou"];}
	if($usersmenus->AsOrgPostfixAdministrator){return true;}
	if($usersmenus->AsMessagingOrg){return true;}
	if(!$usersmenus->AllowChangeDomains){return false;}
}	


function popup(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$ou=urlencode($_GET["ou"]);
	$html="<div class=explain>{ldap_importation_text}</div>
	<p>&nbsp;</p>
	<div id='ldap_importation_div' style='width:100%;height:550px;overflow:auto'></div>
	
	
	
	<script>
		function RefreshLdapList(){
			LoadAjax('ldap_importation_div','$page?connexions-list=yes&ou=$ou');
		}
		
		RefreshLdapList();
	</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
}

function connexions_list(){
	$page=CurrentPageName();
	$tpl=new templates();		
	$ou=urlencode($_GET["ou"]);
	
	$add=imgtootltip("plus-24.png","{add}","YahooWin4(550,'$page?connexion=0&ou=$ou','{add_connection}')");
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>$add</th>
		<th>{connection_name}</th>
		<th>{schedule}</th>
		<th width=1%>{enabled}</th>
		<th width=1%>{members}</th>
		<th>&nbsp;</th>
		<th width=1%>". imgtootltip("refresh-32.png","{refresh}","RefreshLdapList()")."</th>
	</tr>
</thead>
<tbody class='tbody'>		
";	
	
$sql="SELECT ID,connexion_name,enabled,ScheduleMin FROM ldap_ou_import WHERE ou='{$_GET["ou"]}'";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}	
	
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	$img="ok24.png";
	if($ligne["enabled"]==0){$img="ok24-grey.png";$img2="ok24-grey.png";}else{
		$js[]="LoadAjax('status-{$ligne["ID"]}','$page?connection-status={$ligne["ID"]}');";
	}
	
	$title=addslashes($ligne["connexion_name"]);
	$link="<a href=\"javascript:blur();\" OnClick=\"javascript:YahooWin4(550,'$page?connexion={$ligne["ID"]}&ou=$ou','$title')\"
	style='font-size:14px;font-weight:bold;text-decoration:underline'>";
	$delete=imgtootltip("delete-32.png","{delete}","DeleteConnexion('{$ligne["ID"]}')");
	$exec=imgtootltip("eclaire-24.png","{execute}","ExecuteConnection('{$ligne["ID"]}')");
	$html=$html."<tr  class=$classtr>
	<td style='font-size:14px;font-weight:bold'><img src=img/fw_bold.gif></td>
	<td style='font-size:14px;font-weight:bold'>$link{$ligne["connexion_name"]}</a></td>
	<td style='font-size:14px;font-weight:bold'>$link{$ligne["ScheduleMin"]}</a></td>
	<td style='font-size:14px;font-weight:bold' width=1%><img src='img/$img'></a></td>
	<td style='font-size:14px;font-weight:bold' width=1% align='center'><span id='status-{$ligne["ID"]}'>$img2</span></a></td>
	<td style='font-size:14px;font-weight:bold'width=1% >$exec</td>
	
	<td style='font-size:14px;font-weight:bold'width=1% >$delete</td>
	
	</tR>";	
	}
	
$html=$html."</table>

<script>
	var X_DeleteConnexion= function (obj) {
		var results=trim(obj.responseText);
		if(results.length>0){alert(results);}
		RefreshLdapList();

	}		
function DeleteConnexion(ID){
		var XHR = new XHRConnection();
		XHR.appendData('DeleteID',ID);
		XHR.appendData('ou','{$_GET["ou"]}');
		AnimateDiv('ldap_importation_div');   
		XHR.sendAndLoad('$page', 'POST',X_DeleteConnexion);
	}
	
function ExecuteConnection(ID){
		var XHR = new XHRConnection();
		XHR.appendData('ExecuteConnection',ID);
		XHR.appendData('ou','{$_GET["ou"]}');
		AnimateDiv('ldap_importation_div');   
		XHR.sendAndLoad('$page', 'POST',X_DeleteConnexion);
	}	

". @implode("\n",$js)."
</script>

";

echo $tpl->_ENGINE_parse_body($html);
	
}

function connection_delete(){
	
	$sql="DELETE FROM ldap_ou_import WHERE ID={$_POST["DeleteID"]} AND ou='{$_POST["ou"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	
	
}




function connexion_form(){
	if(!is_numeric($_GET["connexion"])){echo "Not a numeric";return;}
	$page=CurrentPageName();
	$tpl=new templates();	
	$q=new mysql();
	$button_name="{apply}";
	if($_GET["connexion"]==0){$button_name="{add}";}
	$sql="SELECT * FROM ldap_ou_import WHERE ID={$_GET["connexion"]} AND ou='{$_GET["ou"]}'";
	
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$cron=new cron_macros();

	while (list ($num, $val) = each ($cron->cron_defined_macros) ){
		if($num==null){continue;}
		$cronR[$num]=$num;
	}	
	$cronR[null]="{select}";
	$db=unserialize($ligne["ldapdatas"]);
	if(!is_numeric($db["server_port"])){$db["server_port"]=389;}
	if($db["bind_dn"]==null){$db["bind_dn"]="uid=zimbra,cn=admins,cn=zimbra";};
	
	$default_filter="(ObjectClass=zimbraAccount)";
	if($db["query_filter"]==null){$db["query_filter"]=$default_filter;}
	
	$html="
	<div id='AdPostfixExtLdapDiv'>
	<table style='width:100%;' class=form>
	<tr>
		<td valign='top' class=legend>Zimbra?:</td>
		<td>". help_icon("{zimbraHowto}")."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend>{connection_name}:</td>
		<td>". Field_text("connection_name",$ligne["connexion_name"],"width:250px;padding:3px;font-size:14px")."</td>
	</tr>
	<tr>
		<td valign='top' class=legend>{enabled}:</td>
		<td>". Field_checkbox("enabled",1,$ligne["enabled"],"CheckOULDAPParameter()")."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend nowrap>{schedule}:</td>
		<td valign='top' >". Field_array_Hash($cronR,"schedule",$ligne["ScheduleMin"],null,null,0,"font-size:14px;padding:3px")."</td>
	</tr>	
	
	<tr>
		<td valign='top' class=legend>{server_host}:</td>
		<td>". Field_text("server_host",$db["server_host"],"width:250px;padding:3px;font-size:14px")."</td>
	</tr>
	<tr>
		<td valign='top' class=legend>{server_port}:</td>
		<td>". Field_text("server_port",$db["server_port"],"width:60px;padding:3px;font-size:14px")."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend>{search_base}:</td>
		<td>". Field_text("search_base",$db["search_base"],"width:250px;padding:3px;font-size:14px")."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend>{bind_dn}:</td>
		<td>". Field_text("bind_dn",$db["bind_dn"],"width:250px;padding:3px;font-size:14px")."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend>{password}:</td>
		<td>". Field_password("bind_password",$db["bind_password"],"width:120px;padding:3px;font-size:14px")."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend>{query_filter}:</td>
		<td>". Field_text("query_filter",$db["query_filter"],"width:250px;padding:3px;font-size:14px")."</td>
	</tr>	
	<tr>
	<td colspan=2 align='right'><hr>". button("$button_name","SaveOULDAPParameter()")."</td>
	</tr>	
	</table>
	</div>
	<script>
var X_SSaveOULDAPParameter= function (obj) {
		var results=trim(obj.responseText);
		if(results.length>0){alert(results);}
		YahooWin4Hide();
		RefreshLdapList();

	}		
function SaveOULDAPParameter(){
		var XHR = new XHRConnection();
		if(document.getElementById('enabled').checked){
			XHR.appendData('enabled',1);
		}else{
			XHR.appendData('enabled',0);
		}
		XHR.appendData('connexion','{$_GET["connexion"]}');
		XHR.appendData('ou','{$_GET["ou"]}');
		XHR.appendData('connection_name',document.getElementById('connection_name').value);
		XHR.appendData('schedule',document.getElementById('schedule').value);
		XHR.appendData('server_host',document.getElementById('server_host').value);
		XHR.appendData('server_port',document.getElementById('server_port').value);
		XHR.appendData('bind_dn',document.getElementById('bind_dn').value);
		XHR.appendData('bind_password',document.getElementById('bind_password').value);
		XHR.appendData('query_filter',document.getElementById('query_filter').value);
		XHR.appendData('search_base',document.getElementById('search_base').value);
		AnimateDiv('AdPostfixExtLdapDiv');   
		XHR.sendAndLoad('$page', 'POST',X_SSaveOULDAPParameter);
		
	}
	
	function CheckOULDAPParameter(){
		document.getElementById('schedule').disabled=true;
		document.getElementById('server_host').disabled=true;
		document.getElementById('server_port').disabled=true;
		document.getElementById('bind_dn').disabled=true;
		document.getElementById('bind_password').disabled=true;
		document.getElementById('query_filter').disabled=true;
		document.getElementById('search_base').disabled=true;
		

		if(document.getElementById('enabled').checked){
			document.getElementById('schedule').disabled=false;
			document.getElementById('server_host').disabled=false;
			document.getElementById('server_port').disabled=false;
			document.getElementById('search_base').disabled=false;
			document.getElementById('bind_dn').disabled=false;
			document.getElementById('bind_password').disabled=false;
			document.getElementById('query_filter').disabled=false;
			document.getElementById('search_base').disabled=false;
		}	
	
	}
CheckOULDAPParameter();
</script>	
	
	
	";
	echo $tpl->_ENGINE_parse_body($html);
}
function connection_save(){
	

	$datas=addslashes(serialize($_POST));
	$_POST["connection_name"]=addslashes($_POST["connection_name"]);
	$sql="INSERT INTO ldap_ou_import (`ou`, `ScheduleMin`,`ldapdatas`,`enabled`,`connexion_name`)
	VALUES('{$_POST["ou"]}','{$_POST["schedule"]}','$datas','{$_POST["enabled"]}','{$_POST["connection_name"]}');";
	
	if($_POST["connexion"]>0){
		$sql="UPDATE ldap_ou_import SET
		`ScheduleMin`='{$_POST["schedule"]}',
		`enabled`='{$_POST["enabled"]}',
		`connexion_name`='{$_POST["connection_name"]}',
		`ldapdatas`='$datas'
		WHERE ID='{$_POST["connexion"]}' AND ou='{$_POST["ou"]}'
		
		";
	}
	
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?ou-ldap-import-schedules=yes");
	
	
	
}

function connection_ldap_status(){
	$ldap_ou=new ldapOu($_GET["connection-status"]);
	echo $ldap_ou->GetCountDeUsers();
	
}

function ExecuteConnection(){
	$ID=$_POST["ExecuteConnection"];
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?ou-ldap-import-execute=yes&ID=$ID");
	$tpl=new templates();
	echo $tpl->javascript_parse_text("{importation_background_text}");
}



?>