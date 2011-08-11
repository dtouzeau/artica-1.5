<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.maincf.multi.inc');
	include_once('ressources/class.status.inc');
	if(isset($_GET["org"])){$_GET["ou"]=$_GET["org"];}
	
	if(!PostFixMultiVerifyRights()){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["form-database"])){database_form();exit;}
	if(isset($_GET["howto"])){howto();exit;}
	if(isset($_GET["dbindex"])){database_save();exit;}
	if(isset($_GET["dbindexDelete"])){database_delete();exit;}
	if(isset($_GET["postfix-ldap-databases"])){database_list();exit;}
	
js();


function js(){
$page=CurrentPageName();
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body("{remote_users_databases}");
if(is_base64_encoded($_GET["ou"])){$ou=base64_decode($_GET["ou"]);}else{$ou=$_GET["ou"];}
$hostname=$_GET["hostname"];
$add=$tpl->_ENGINE_parse_body("{add}");
$howto=$tpl->_ENGINE_parse_body("{howto}");
$html="

function remote_users_databases_load(){
	YahooWin4('736','$page?popup=yes&ou={$_GET["ou"]}&hostname=$hostname','$ou/$hostname::$title');
	}
	
function FormDatabase(md){
	var title='';
	if(md==''){title='$add';}
	YahooWin5('650','$page?form-database='+md+'&ou={$_GET["ou"]}&hostname=$hostname','$ou/$hostname::'+title);
}
function ADExtHowto(){
	var title='';
	YahooWin6('650','$page?howto=yes&ou={$_GET["ou"]}&hostname=$hostname','$ou/$hostname::$howto');
}


	remote_users_databases_load();
";
echo $html;
}

function popup(){
$page=CurrentPageName();
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body("{remote_users_databases}");
$ou=base64_decode($_GET["ou"]);
$hostname=$_GET["hostname"];

		$add=Paragraphe('databases-add-64.png','{add_ldap_database_link}','{add_ldap_database_link_text}',
		"javascript:FormDatabase('')");	
	
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top' width=1%>
		<center>
			<img src='img/databases-add-128.png'>
				<p>&nbsp;</p>
				$add
		</center>
		</td>
		<td valign='top' width=99%>
			<div class=explain>{remote_users_databases_howto}</div>
			<div style='width:100%;margin:5px;height:255px;overflow:auto' id='postfix-ldap-databases'></div>
		</td>
	</tr>
	</table>
	
	<script>
		function PostfixLoadDatabases(){
			LoadAjax('postfix-ldap-databases','$page?postfix-ldap-databases=yes&hostname={$_GET["hostname"]}&ou={$_GET["ou"]}');
		
		}
		
		PostfixLoadDatabases();
	</script>
	
	";
echo $tpl->_ENGINE_parse_body($html);	
	
}

function database_list(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$ou=base64_decode($_GET["ou"]);
	$hostname=$_GET["hostname"];
	$main=new maincf_multi($hostname,$ou);	
	$databases_list=unserialize(base64_decode($main->GET_BIGDATA("ActiveDirectoryDBS")));	
	$delete_freeweb_text=$tpl->javascript_parse_text("{delete_freeweb_text}");
	
	$html="
	<p>&nbsp;</p>
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
		<thead class='thead'>
			<tr>
				<th colspan=2>{server_host}</th>
				<th>{database_type}</th>
				<th>{infos}</th>
				<th>&nbsp;</th>
			</tr>
		</thead>
		<tbody class='tbody'>";	
	
	if(is_array($databases_list)){
	while (list ($dbindex, $array) = each ($databases_list) ){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		
				$info="<ul>
				<li><strong>{search_base}:</strong>&nbsp;{$array["search_base"]}</li>
				<li><strong>{query_filter}:</strong>&nbsp;{$array["query_filter"]}</li>
				<li><strong>{result_attribute}:</strong>&nbsp;{$array["result_attribute"]}</li>
				<li><strong>{leaf_result_attribute}:</strong>&nbsp;{$array["leaf_result_attribute"]}</li>
				<li><strong>{special_result_attribute}:</strong>&nbsp;{$array["special_result_attribute"]}</li>
				</ul>
				
				
				";
		
				$html=$html."
				<tr class=$classtr>
					<td width=1%>". imgtootltip("datasource-32.png","{edit}","FormDatabase('$dbindex')")."</td>
					<td style='font-size:14px'><center><a href='#' OnClick=\"javascript:FormDatabase('$dbindex')\" style='text-decoration:underline'>{$array["server_host"]}</a></center></td>
					<td style='font-size:14px'><center><a href='#' OnClick=\"javascript:FormDatabase('$dbindex')\" style='text-decoration:underline'>{{$array["database_type"]}}</a></center></td>
					<td width=1%><center>". imgtootltip("32-infos.png",$info)."</center></td>
					<td width=1%>". imgtootltip("delete-32.png","{delete}","FormDatabaseDelete('$dbindex')")."</td>
				</tr>
			
			";
			}
	}	
		$html=$html."</tbody></table>
		
		<script>
		
		var X_FormDatabaseDelete= function (obj) {
			var results=trim(obj.responseText);
			if(results.length>0){alert(results);}
			PostfixLoadDatabases();
			}
		
			function FormDatabaseDelete(dbindex){
				if(confirm('$delete_freeweb_text')){
					var XHR = new XHRConnection();
					XHR.appendData('dbindexDelete',dbindex);
					XHR.appendData('ou','{$_GET["ou"]}');
					XHR.appendData('hostname','{$_GET["hostname"]}');					
					document.getElementById('postfix-ldap-databases').innerHTML='<center><img src=img/wait_verybig.gif></center>';   
					XHR.sendAndLoad('$page', 'GET',X_FormDatabaseDelete);
				}
			
			}
		</script>
		";
		
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function database_form(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$ou=base64_decode($_GET["ou"]);
	$hostname=$_GET["hostname"];
	$main=new maincf_multi($hostname,$ou);	
	$databases_list=unserialize(base64_decode($main->GET_BIGDATA("ActiveDirectoryDBS")));
	$db=$databases_list[$_GET["form-database"]];
	$button_name="{apply}";
	if($_GET["form-database"]==null){$button_name="{add}";}
	$dbTypes=array(
	"alias_maps"=>"{alias_maps} (alias_maps)",
	"virtual_alias_maps"=>"{virtual_alias_maps} (virtual_alias_maps)",
	"virtual_mailbox_maps"=>"{virtual_mailbox_maps} (virtual_mailbox_maps)",
	"relay_recipient_maps"=>"{relay_recipient_maps} (relay_recipient_maps)",
	);
	
	
	$html="

	<table style='width:100%'>
	<tr>
	<td valign='top' style='border-right:5px solid #CCCCCC;padding-right:5px'>"
		. imgtootltip("help-64.png","{howto}","ADExtHowto()").
		"<br>"
		. imgtootltip("databases-search-net-64.png","{search_remote_database}","Loadjs('ad.query.database.php')")."
	</td>
	<td valign='top' width=100%>
	<input type='hidden' id='dbindex' value='{$_GET["form-database"]}'>
	<div id='AdPostfixExtLdapDiv'>
	<table style='width:99.5%' class=form>
	<tr>
		<td valign='top' class=legend>{enabled}:</td>
		<td>". Field_checkbox("ad_external_ldap_enabled",1,$db["enabled"],"CheckEnabledPostfixLDAP()")."</td>
	</tr>
	<tr>
		<td valign='top' class=legend>{resolv_domains}:</td>
		<td>". Field_checkbox("resolv_domains",1,$db["resolv_domains"])."</td>
	</tr>			
	<tr>
		<td valign='top' class=legend>{database_type}:</td>
		<td>". Field_array_Hash($dbTypes,"database_type",$db["database_type"],"style:width:250px;padding:3px;font-size:13px")."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend>{server_host}:</td>
		<td>". Field_text("server_host",$db["server_host"],"width:250px;padding:3px;font-size:13px")."</td>
	</tr>
	<tr>
		<td valign='top' class=legend>{search_base}:</td>
		<td>". Field_text("search_base",$db["search_base"],"width:250px;padding:3px;font-size:13px")."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend>{bind_dn}:</td>
		<td>". Field_text("bind_dn",$db["bind_dn"],"width:250px;padding:3px;font-size:13px")."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend>{password}:</td>
		<td>". Field_password("bind_password",$db["bind_password"],"width:120px;padding:3px;font-size:13px")."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend>{query_filter}:</td>
		<td>". Field_text("query_filter",$db["query_filter"],"width:250px;padding:3px;font-size:13px")."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend>{scope}:</td>
		<td>". Field_text("scope",$db["scope"],"width:250px;padding:3px;font-size:13px")."</td>
	</tr>	
	<tr>
		<td valign='top' class=legend>{result_attribute}:</td>
		<td>". Field_text("result_attribute",$db["result_attribute"],"width:250px;padding:3px;font-size:13px")."</td>
	</tr>		
	<tr>
		<td valign='top' class=legend>{leaf_result_attribute}:</td>
		<td>". Field_text("leaf_result_attribute",$db["leaf_result_attribute"],"width:250px;padding:3px;font-size:13px")."</td>
	</tr>
	<tr>
		<td valign='top' class=legend>{special_result_attribute}:</td>
		<td>". Field_text("special_result_attribute",$db["special_result_attribute"],"width:250px;padding:3px;font-size:13px")."</td>
	</tr>
	
	<tr>
	<td colspan=2 align='right'><hr>". button("$button_name","SaveExternalLDAPParameter()")."</td>
	</tr>	
	</table>
	</div>
	</td>
	</tr>
	</table>	
	
	<script>
	
	
var X_SaveExternalLDAPParameter= function (obj) {
		var results=trim(obj.responseText);
		if(results.length>0){alert(results);}
		PostfixLoadDatabases();
		YahooWin5Hide();
		YahooWin6Hide();
	}		
function SaveExternalLDAPParameter(){
		var XHR = new XHRConnection();
		if(document.getElementById('ad_external_ldap_enabled').checked){
			XHR.appendData('enabled',1);
		}else{
			XHR.appendData('enabled',0);
		}
		
		if(document.getElementById('resolv_domains').checked){
			XHR.appendData('resolv_domains',1);
		}else{
			XHR.appendData('resolv_domains',0);
		}		
	
		

		XHR.appendData('dbindex','{$_GET["form-database"]}');
		XHR.appendData('ou','{$_GET["ou"]}');
		XHR.appendData('hostname','{$_GET["hostname"]}');
		XHR.appendData('database_type',document.getElementById('database_type').value);
		XHR.appendData('server_host',document.getElementById('server_host').value);
		XHR.appendData('search_base',document.getElementById('search_base').value);
		XHR.appendData('bind_dn',document.getElementById('bind_dn').value);
		XHR.appendData('bind_password',document.getElementById('bind_password').value);
		XHR.appendData('query_filter',document.getElementById('query_filter').value);
		XHR.appendData('scope',document.getElementById('scope').value);
		XHR.appendData('result_attribute',document.getElementById('result_attribute').value);
		XHR.appendData('special_result_attribute',document.getElementById('special_result_attribute').value);
		XHR.appendData('leaf_result_attribute',document.getElementById('leaf_result_attribute').value);
		document.getElementById('AdPostfixExtLdapDiv').innerHTML='<center><img src=img/wait_verybig.gif></center>';   
		XHR.sendAndLoad('$page', 'GET',X_SaveExternalLDAPParameter);
		
	}
	
	function CheckEnabledPostfixLDAP(){
		document.getElementById('database_type').disabled=true;
		document.getElementById('server_host').disabled=true;
		document.getElementById('search_base').disabled=true;
		document.getElementById('bind_dn').disabled=true;
		document.getElementById('bind_password').disabled=true;
		document.getElementById('query_filter').disabled=true;
		document.getElementById('scope').disabled=true;
		document.getElementById('result_attribute').disabled=true;
		document.getElementById('special_result_attribute').disabled=true;
		document.getElementById('resolv_domains').disabled=true;
		document.getElementById('leaf_result_attribute').disabled=true;
		if(document.getElementById('ad_external_ldap_enabled').checked){
			document.getElementById('resolv_domains').disabled=false;
			document.getElementById('database_type').disabled=false;
			document.getElementById('server_host').disabled=false;
			document.getElementById('search_base').disabled=false;
			document.getElementById('bind_dn').disabled=false;
			document.getElementById('bind_password').disabled=false;
			document.getElementById('query_filter').disabled=false;
			document.getElementById('scope').disabled=false;
			document.getElementById('result_attribute').disabled=false;
			document.getElementById('special_result_attribute').disabled=false;
			document.getElementById('leaf_result_attribute').disabled=false;	
		}	
	
	}
CheckEnabledPostfixLDAP();
	</script>
	
	
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}

function howto(){
$tpl=new templates();	
$html="	
	<div class=explain>{remote_users_databases_howto}</div>
	<div style='height:350px;width:100%;overflow:auto'>
	<table style='width:100%'>
		<tr>
			<td valign='top' class=legend>{database_type}:</td>
			<td><code style='font-size:13px'>{virtual_mailbox_maps}</code></td>
		</tr>	
		<tr>
			<td valign='top' class=legend>{server_host}:</td>
			<td><code style='font-size:13px'>192.168.0.100</code></td>
		</tr>
		<tr>
			<td valign='top' class=legend>{search_base}:</td>
			<td><code style='font-size:13px'>CN=Users,DC=example,DC=local</code></td>
		</tr>	
		<tr>
			<td valign='top' class=legend>{bind_dn}:</td>
			<td><code style='font-size:13px'>CN=Administrator,CN=Users,DC=example,DC=local</code></td>
		</tr>	
		<tr>
			<td valign='top' class=legend>{password}:</td>
			<td><code style='font-size:13px'>secret</code></td>
		</tr>	
		<tr>
			<td valign='top' class=legend>{query_filter}:</td>
			<td><code style='font-size:13px'>(&(objectClass=person)(mail=%s))</code></td>
		</tr>	
		<tr>
			<td valign='top' class=legend>{scope}:</td>
			<td><code style='font-size:13px'>sub</code></td>
		</tr>	
		<tr>
			<td valign='top' class=legend>{result_attribute}:</td>
			<td><code style='font-size:13px'>mail</code></td>
		</tr>
	</table>
	<hr>
	<table style='width:100%'>
		<tr>
			<td valign='top' class=legend>{database_type}:</td>
			<td><code style='font-size:13px'>{virtual_alias_maps}</code></td>
		</tr>	
		<tr>
			<td valign='top' class=legend>{server_host}:</td>
			<td><code style='font-size:13px'>192.168.0.100</code></td>
		</tr>
		<tr>
			<td valign='top' class=legend>{search_base}:</td>
			<td><code style='font-size:13px'>CN=Users,DC=example,DC=local</code></td>
		</tr>	
		<tr>
			<td valign='top' class=legend>{bind_dn}:</td>
			<td><code style='font-size:13px'>CN=Administrator,CN=Users,DC=example,DC=local</code></td>
		</tr>	
		<tr>
			<td valign='top' class=legend>{password}:</td>
			<td><code style='font-size:13px'>secret</code></td>
		</tr>	
		<tr>
			<td valign='top' class=legend>{query_filter}:</td>
			<td><code style='font-size:13px'>(&(objectClass=person)(otherMailbox=%s))</code></td>
		</tr>	
		<tr>
			<td valign='top' class=legend>{scope}:</td>
			<td><code style='font-size:13px'>sub</code></td>
		</tr>	
		<tr>
			<td valign='top' class=legend>{result_attribute}:</td>
			<td><code style='font-size:13px'>mail</code></td>
		</tr>
	</table>	
	<hr>
	<table style='width:100%'>
		<tr>
			<td valign='top' class=legend>{database_type}:</td>
			<td><code style='font-size:13px'>{virtual_alias_maps}</code></td>
		</tr>	
		<tr>
			<td valign='top' class=legend>{server_host}:</td>
			<td><code style='font-size:13px'>192.168.0.100</code></td>
		</tr>
		<tr>
			<td valign='top' class=legend>{search_base}:</td>
			<td><code style='font-size:13px'>CN=Builtin,DC=example,DC=local</code></td>
		</tr>	
		<tr>
			<td valign='top' class=legend>{bind_dn}:</td>
			<td><code style='font-size:13px'>CN=Administrator,CN=Users,DC=example,DC=local</code></td>
		</tr>	
		<tr>
			<td valign='top' class=legend>{password}:</td>
			<td><code style='font-size:13px'>secret</code></td>
		</tr>	
		<tr>
			<td valign='top' class=legend>{query_filter}:</td>
			<td><code style='font-size:13px'>(&(objectclass=group)(mail=%s))</code></td>
		</tr>	
		<tr>
			<td valign='top' class=legend>{scope}:</td>
			<td><code style='font-size:13px'>sub</code></td>
		</tr>	
		<tr>
			<td valign='top' class=legend>{leaf_result_attribute}:</td>
			<td><code style='font-size:13px'>mail</code></td>
		</tr>
		<tr>
			<td valign='top' class=legend>{special_result_attribute}:</td>
			<td><code style='font-size:13px'>member</code></td>
		</tr>		
	</table>		
	</div>
	";	
		echo $tpl->_ENGINE_parse_body($html);
}

function database_delete(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$ou=base64_decode($_GET["ou"]);
	$hostname=$_GET["hostname"];
	$main=new maincf_multi($hostname,$ou);	
	$databases_list=unserialize(base64_decode($main->GET_BIGDATA("ActiveDirectoryDBS")));	
	unset($databases_list[$_GET["dbindexDelete"]]);
	$final=base64_encode(serialize($databases_list));
	$main->SET_BIGDATA("ActiveDirectoryDBS",$final);
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-multi-cfdb=$hostname");
}
	
function database_save(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$ou=base64_decode($_GET["ou"]);
	$hostname=$_GET["hostname"];
	$main=new maincf_multi($hostname,$ou);	
	$databases_list=unserialize(base64_decode($main->GET_BIGDATA("ActiveDirectoryDBS")));
	if($_GET["dbindex"]==null){$_GET["dbindex"]=time();}
	if($_GET["query_filter"]==null){$_GET["query_filter"]="(&(objectClass=person)(mail=%s))";}
	if($_GET["scope"]==null){$_GET["scope"]="sub";}
	$res=0;
	if($_GET["result_attribute"]==null){$res++;}
	if($_GET["leaf_result_attribute"]==null){$res++;}
	if($_GET["special_result_attribute"]==null){$res++;}
	if($res==0){$_GET["result_attribute"]="mail";}
	$databases_list[$_GET["dbindex"]]=$_GET;	
	$final=base64_encode(serialize($databases_list));
	$main->SET_BIGDATA("ActiveDirectoryDBS",$final);
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-multi-cfdb=$hostname");	
}	