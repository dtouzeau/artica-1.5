<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.dansguardian.inc');
	$user=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$user->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}
	if($user->SQUID_INSTALLED==false){header('location:users.index.php');exit();}
	if($user->AsSquidAdministrator==false){header('location:users.index.php');exit();}	
	

	if(isset($_GET["squid_ip_client"])){main_network_addsrc();exit;}
	if(isset($_GET["squid_acl_to_delete"])){main_acl_delete_index();exit;}
	if(isset($_GET["auth_allow"])){main_network_ldapenable();exit;}
	if(isset($_GET["add_protocol"])){main_Safe_ports_add();exit;}
	if(isset($_GET["Safe_Ports_delete"])){main_Safe_ports_del();exit;}
	if(isset($_GET["add_deny_ext"])){main_denyext_add();exit;}
	if(isset($_GET["del_deny_ext"])){main_denyext_del();exit;}
	if(isset($_GET["dans_http_port"])){main_network_edit_port_dans();exit;}
	if(isset($_GET["squid_http_port"])){main_network_edit_port_squid();exit;}
	if(isset($_GET["EnableKav4Proxy"])){main_kav4proxy_edit();exit;}
	if(isset($_GET["SquidSimpleKav4ProxyMacro1"])){main_kav4proxy_Macro1();exit;}
	$user=new usersMenus();
	
	
	main_page();
	
function main_page(){
	
	switch ($_GET["main"]) {
			case "network":main_network();exit;break;
			case "Safe_ports":main_Safe_ports();exit;break;
			case "config":main_configfile();exit;break;
			case "Safe_ports_list":echo main_Safe_ports_list();exit;break;
			case "deny_ext":main_denyext();exit;break;
			case "deny_ext_list":echo main_denyext_list();exit;break;
			case "kavav":echo main_kav4proxy();exit;break;
			case "info":echo main_infos();exit;break;			
			default:
				break;
		}	
		
		

	if($_GET["hostname"]==null){$user=new usersMenus();$hostname=$user->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}
$apply=applysettings("squid","SQUID_APPLY('{$_GET["hostname"]}')");
	
	
	$html=
	"
<script language=\"JavaScript\">       
var timerID  = null;
var timerID1  = null;
var tant=0;
var reste=0;

function demarre(){
   tant = tant+1;
   reste=10-tant;
	if (tant < 10 ) {                           
      timerID = setTimeout(\"demarre()\",5000);
      } else {
               tant = 0;
               //document.getElementById('wait').innerHTML='<img src=img/wait.gif>';
               ChargeLogs();
               demarre();                                //la boucle demarre !
   }
}


function ChargeLogs(){
	var status='status';
	
	if(document.getElementById('statusid')){
		status=document.getElementById('statusid').value;
	}
	LoadAjax('services_status','squid.index.php?status='+ status+'&hostname={$_GET["hostname"]}');
	}
</script>		
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'><img src='img/bg_squid.jpg'><p class=caption>{squid_simple_intro}</p></td>
	<td valign='top'>
	<div id='services_status'></div>
	</td>
	</tr>
	<tr>
		<td colspan=2 valign='top'>
			<div id='squid_main_config'></div>
		</td>
	</tr>
	</table>
	<script>LoadAjax('squid_main_config','$page?main=network');</script>
	<script>demarre();</script>
	<script>ChargeLogs();</script>
	";
	
	
	$cfg["JS"][]='js/squid.js';
	$cfg["JS"][]='js/icap.js';
	
	
	
	$tpl=new template_users('Squid',$html,0,0,0,0,$cfg);
	
	echo $tpl->web_page;
	
	
	
}

function main_tabs(){
	
	$users=new usersMenus();

	
	
	
	$page=CurrentPageName();
	$array["network"]='{squid_network}';
	$array["Safe_ports"]='{Safe_ports}';
	
	if($users->DANSGUARDIAN_INSTALLED==false){
		$array["deny_ext"]='{deny_ext}';	
		}
		
    if($users->KAV4PROXY_INSTALLED==true){
    	
    	$array["kavav"]='{APP_KAV4PROXY}';
    }
	
	$array['config']='{config_file}';
		$array["info"]='{info}';
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["tab"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('squid_main_config','$page?main=$num&tab=$num&hostname={$_GET["hostname"]}')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";		
}


function main_network(){
	
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}
	
	
	
	$users=new usersMenus($hostname);
	$squid=new squid($hostname);
	
	$squid_ports=$squid->http_port_array[0];
	
if(strpos($squid->http_port_array[0],':')>0){
			$tb=explode(':',$squid->http_port_array[0]);
			$squid_ip_port=$tb[1];
			$squid_http_port=$tb[0];	
			}else{
				$squid_http_port=$squid->http_port_array[0];
			}
			
	
	
	if($squid->is_rules_exists('http_access','password')==true){$auth="yes";}else{$auth="no";}
	
	$my_auth_config=Field_yesno_checkbox("auth_allow",$auth);
	$sys=new systeminfos();
	$sys->array_tcp_addr['']='{all}';	
	
	
	
		if(is_array($squid->acls_rules_array["my_network"]["datas"])){
			$table="<table style='width:60%'>";
			$st=CellRollOver();
			while (list ($num, $line) = each ($squid->acls_rules_array["my_network"]["datas"])){
			$table=$table . "
			<tr $st>
			<td with=1% ><img src='img/fw_bold.gif'></td>
			<td><strong>$line</td>
			<td>{squid_network_ex}</td>
			<td>" . imgtootltip('x.gif','{delete}',"SquidSimpleDelsrc('$hostname','my_network','$num')"). "</td>
			</tr>
			";
			
			
			}
			
			$table=$table . "</table>";
			$table=RoundedLightGrey($table);
		}

	if($users->DANSGUARDIAN_INSTALLED==true){
		$dans=new dansguardian($hostname);
		$dans_port=$dans->Master_array["filterport"];
		
	$form="<table style='width:100%'>

	<tr>
	<td align='right' nowrap><strong>{dansguardian_listen_port}:</strong></td>
	<td>" . Field_text('dans_listen_port',$dans_port,'width:100px') ."</td>
	<td class=caption>{listen_port_text}</td>
	</tr>	
	<tr>
		<td align='right'><strong>{tcp_address}</strong>:</td>
		<td align='left'>" . Field_array_Hash($sys->array_tcp_addr,'dans_http_port_ip',$dans->Master_array["filterip"],null,null,0,'width:150px')."</td>
		<td class=caption>{tcp_address_text}</td>
	</tr>		
	
	<tr>
	<td align='right'><strong>{squid_listen_port}:</strong></td>
	<td>" . Field_text('squid_listen_port',$squid->http_port_array[0],'width:120px') ."</td>
	<td class=caption>{listen_port_chain}</td>
	</tr>
	<tr>
	<td class=caption align='right' colspan=3><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:SquidSimpleEditDansPort('$hostname');\"></td>
	</tr>	
	

	
	</table>
	";
	
	$form=RoundedLightGrey($form);
		
	}else{
		$ports=$squid->http_port_array[0];
		if(strpos($squid->http_port_array[0],':')>0){
			$tb=explode(':',$squid->http_port_array[0]);
			$http_port=$tb[1];
			$filterip=$tb[0];
		}else{
			$http_port=$squid->http_port_array[0];
			$filterip=null;
		}
		
		
		
		$form="<table style='width:100%'>
	<tr>
	<td align='right'><strong>{listen_port}:</strong></td>
	<td>" . Field_text('listen_port',$squid_http_port,'width:100px') ."</td>
	<td class=caption>{listen_port_text}<br><code>({$squid_http_port})</code></td>
	</tr>
	<tr>
		<td align='right'><strong>{tcp_address}</strong>:</td>
		<td align='left'>" . Field_array_Hash($sys->array_tcp_addr,'http_port_ip',$squid_ip_port,null,null,0,'width:150px')."</td>
		<td class=caption><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:SquidSimpleEditSquidPort('$hostname');\"></td>
	</tr>	
	
	</table>";
	$form=RoundedLightGrey($form);	
	}
	
	
	
	
	
	$html=main_tabs() . "<br>
	<H5>{listen_port}</H5>
	<br>$form<br>
	
	
	<h5>{squid_network}</H5>
	<p class=caption>{squid_network_text} {acl_src_text}</p>
	" . RoundedLightGreen("
	<table style='width:100%'>
	<tr>
	<td align='right' nowrap valign='top'><strong>{squid_ldap_auth}</strong></td>
	<td valign='top'>$my_auth_config</td>
	<td valign='top'><input type='button' value='{add}&nbsp;&raquo;' OnClick=\"javascript:SquidSimpleLdapEnable('$hostname');\"></td>
	<td class='caption' valign='top'>{squid_ldap_auth_text}</td>
	</tr>		
	<tr>
	<tr><td colspan=4>&nbsp;</td></tr>
	<td align='right' valign='top'><strong>{acl_src}</strong></td>
	<td valign='top'>" . Field_text('squid_ip_client',null,'width:120px') . "</td>
	<td valign='top'><input type='button' value='{add}&nbsp;&raquo;' OnClick=\"javascript:SquidSimpleAddsrc('$hostname');\"></td>
	<td class='caption' valign='top'>{squid_ip_client_text}</td>
	</tr>

	</table>") . "<br>$table";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'squid.index.php');
	
}


function main_Safe_ports(){
	
	$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}
	
	$squid=new squid($hostname);
	$squid->LoadProtocolTable();
	$fieldlist=$squid->protocol_field;
	$local_list=$squid->acls_rules_array["Safe_ports"]["datas"];
	
	
	$form="<br>
	" . RoundedLightGreen("
	<table style='width:100%'>
	<tr>
	<td align='right' nowrap valign='top'><strong>{Safe_ports_add}:</strong></td>
	<td valign='top'><select name=\"protocol\" id='protocol'>$fieldlist</select></td>
	<td valign='top'><input type='button' value='{add}&nbsp;&raquo;' OnClick=\"javascript:SquidSimpleAddSafePorts('$hostname');\"></td>
	</tr>		
	</table>
	");

	$html=main_tabs() . "<br>
	<h5>{Safe_ports}</H5>
	<p class=caption>{Safe_ports_text}</p>
	$form<br><div id='Safe_Ports'>" . main_Safe_ports_list() . "</div>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'squid.index.php');
	
}


function main_kav4proxy_edit(){
	$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}
	$squid=new squid($hostname);
	$squid->EnableKav4Proxy=$_GET["EnableKav4Proxy"];
	$squid->BuildAclForKav();
	$squid->SaveToLdap();
	
}

function main_kav4proxy_Macro1(){
	$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}
	$squid=new squid($hostname);
	$squid->BuildAclForKav();
	$squid->SaveToLdap();
		
	
}


function main_kav4proxy(){
	$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}
	$squid=new squid($hostname);
	
	$field=Field_numeric_checkbox_img('EnableKav4Proxy',$squid->EnableKav4Proxy,'{enable_disable}');
	
	
$form="<br>
	" . RoundedLightGreen("
	<table style='width:100%'>
	<tr>
	<td align='right' nowrap valign='top'><strong>{enable_kav4proxy}:</strong></td>
	<td valign='top'>$field</td>
	<td valign='top'>{enable_kav4proxy_text}</td>
	</tr>		
	<tr>
	<td colspan=3 align='right'>
	<input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:SquidSimpleKav4Proxy('$hostname');\">
	</td>
	</table>
	");	


$form2="<br>
	" . RoundedLightGreen("
	<table style='width:100%'>
	<tr>
	<td valign='top'><input type='button' value='{enable_kav_macro}&nbsp;&raquo;' OnClick=\"javascript:SquidSimpleKav4ProxyMacro1('$hostname');\"></td>
	<td valign='top'>{enable_kav_macro_text}</td>
	</tr>		
	</table>
	");	

$icap_access=$squid->restrictions_array["icap_access"]["rules"];
if(is_array($icap_access)){
	$access="<H5>icap access</H5>";
	while (list ($num, $line) = each ($icap_access)){
		$access=$access . "<div><code style='font-size:12px;font-weight:bolder'>$line</code></div>";
	}
	
}
$access=RoundedLightGrey($access);
	
$html=main_tabs() . "<br>
	<h5>{APP_KAV4PROXY}</H5>
	<p class=caption>{APP_KAV4PROXY_TEXT}</p>
	$form<br>$form2<br>$access";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'squid.index.php');	
		
	
	
}



function main_denyext(){
	$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}
	
	$squid=new squid($hostname);
	$local_list=$squid->acls_rules_array["Deny_ext"]["datas"];
	

	$form="<br>
	" . RoundedLightGreen("
	<table style='width:100%'>
	<tr>
	<td align='right' nowrap valign='top'><strong>{deny_ext_add}:</strong></td>
	<td valign='top'>" . Field_text('deny_ext',null,'width:100px')."</td>
	<td valign='top'><input type='button' value='{add}&nbsp;&raquo;' OnClick=\"javascript:SquidSimpleAddDenyExt('$hostname');\"></td>
	</tr>		
	</table>
	");

	$html=main_tabs() . "<br>
	<h5>{deny_ext}</H5>
	<p class=caption>{deny_ext_add_text}</p>
	$form<br><div id='_table_deny_ext'>" . main_denyext_list() . "</div>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'squid.index.php');	
	
	
}

function main_denyext_list(){

	$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}
	$squid=new squid($hostname);	
	
	if(!is_array($squid->acls_rules_array["deny_ext"]["datas"])){return null;}
	
	$table="<table style='width:100%'>";
	$st=CellRollOver();	
	while (list ($num, $line) = each ($squid->acls_rules_array["deny_ext"]["datas"])){
		$line=str_replace('$','',$line);
		$line=str_replace('-i','',$line);
		$line=str_replace('\.','.',$line);
		
		$table=$table . "
		<tr $st>
		<td with=1% ><img src='img/fw_bold.gif'></td>
		<td><strong><span style='color:red'>$line</span></strong></td>
		<td>" . imgtootltip('x.gif','{delete}',"SquidSimpleDelDenyExt('$hostname','$num')"). "</td>
		</tr>
		";		
		
	}
	
	
$table=$table . "</table>";
		$table=RoundedLightGrey($table);
	
$tpl=new templates();
	return $tpl->_ENGINE_parse_body($table,'squid.index.php');	
	
}

function main_Safe_ports_list(){
	$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}
	
	$squid=new squid($hostname);
	$local_list=$squid->acls_rules_array["Safe_ports"]["datas"];	
	
if(is_array($local_list)){
		$table="<table style='width:100%'>";
		$st=CellRollOver();
		while (list ($num, $line) = each ($local_list)){
		$table=$table . "
		<tr $st>
		<td with=1% ><img src='img/fw_bold.gif'></td>
		<td><strong>Port <span style='color:red'>$line</span> {acl_is_allowed}</strong></td>
		<td>" . imgtootltip('x.gif','{delete}',"SquidSimpleDelSafePort('$hostname','$num')"). "</td>
		</tr>
		";
		
		
		}
		
		$table=$table . "</table>";
		$table=RoundedLightGrey($table);
	}
$tpl=new templates();
	return $tpl->_ENGINE_parse_body($table,'squid.index.php');
	 
	
}

function main_Safe_ports_add(){
	$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;}else{$hostname=$_GET["hostname"];}
	$squid=new squid($hostname);
	$squid->AddAclType('Safe_ports','port',$_GET["add_protocol"]);	
	$squid->SimpleModeReorgRules();
	}
function main_Safe_ports_del(){
$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;}else{$hostname=$_GET["hostname"];}
	$squid=new squid($hostname);
	$squid->DelAclType('Safe_ports',$_GET["Safe_Ports_delete"]);
	$squid->SimpleModeReorgRules();	
	
}

function main_denyext_add(){
	$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;}else{$hostname=$_GET["hostname"];}
	$add_deny_ext=strtolower($_GET["add_deny_ext"]);
	if(substr($add_deny_ext,0,1)<>'.'){
		$add_deny_ext='.'.$add_deny_ext;
	}
	
	$add_deny_ext=str_replace('.','\\.',$add_deny_ext);
	$add_deny_ext=$add_deny_ext . '$';
	$squid=new squid($hostname);
	$squid->AddAclType('deny_ext','urlpath_regex',"-i $add_deny_ext");
	$squid->AddAccessRule("http_access","deny","deny_ext");
	$squid->SimpleModeReorgRules();
	$squid->SaveToLdap();
	
	
}

function main_denyext_del(){
	if($_GET["hostname"]==null){$hostname=$users->hostname;}else{$hostname=$_GET["hostname"];}
	$squid=new squid($hostname);
	$squid->DelAclType('deny_ext',$_GET["del_deny_ext"]);
	$squid->AddAccessRule("http_access","deny","deny_ext");
	$squid->SaveToLdap();
	$squid->SimpleModeReorgRules();
	}


function main_network_addsrc(){
	$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;}else{$hostname=$_GET["hostname"];}
	
	$squid=new squid($hostname);
	if(trim($_GET["squid_ip_client"])<>null){
		$squid->AddAclType('my_network','src',$_GET["squid_ip_client"]);
	}
	$squid->SimpleModeReorgRules();
	$squid->SaveToLdap();	
}

function main_network_edit_port_dans(){
	$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;}else{$hostname=$_GET["hostname"];}	
	
	$squid=new squid($_GET["hostname"]);
	$squid->http_port_array[0]=$_GET["squid_http_port"];
	$squid->SaveToLdap();	
	writelogs("->class.dansguardian.inc -> dansguardian listen port={$_GET["dans_http_port"]}",__FUNCTION__,__FILE__);
	$dans=new dansguardian($_GET["hostname"]);
	$dans->Master_array["filterip"]=$_GET["dans_http_port_ip"];
	$dans->Master_array["filterport"]=$_GET["dans_http_port"];
	$dans->SaveSettings();
	}
	
function main_network_edit_port_squid(){
		$squid=new squid($_GET["hostname"]);
		$http_mode=$_GET["http_mode"];
		if($_GET["squid_http_port_ip"]<>null){$net=$_GET["squid_http_port_ip"].":".$_GET["squid_http_port"];}else{$net=$_GET["squid_http_port"];}
		$squid->http_port_array[0]=$net;
		$tpl=new templates();
		$squid->SaveToLdap();	
}

function main_acl_delete_index(){
	$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;}else{$hostname=$_GET["hostname"];}	
	$squid=new squid($hostname);
	$squid->DelAclType($_GET["squid_acl_to_delete"],$_GET["index"]);
	$squid->SimpleModeReorgRules();
	$squid->SaveToLdap();
}

function main_network_ldapenable(){
	$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;}else{$hostname=$_GET["hostname"];}	
	$squid=new squid($hostname);
	
	if($_GET["auth_allow"]=="yes"){
		$squid->AddAclType('group_password','external','ldap_group');
		$squid->AddAccessRule("http_access","allow","group_password");
		$squid->SimpleModeReorgRules();
		$squid->SaveToLdap();
	}else{
		$squid->DeleteAclRule('password');
		$squid->SimpleModeReorgRules();
		$squid->SaveToLdap();
	}
	
	
}


function main_infos(){
	$users=new usersMenus();
	$sock=new sockets();
	$datas=$sock->getfile('squidclient:info',$_GET["hostname"]);
	$table=explode("\n",$datas);
	
	while (list ($num, $val) = each ($table) ){
		if(preg_match('#^[A-Za-z\s\:]+$#',$val)){
			$val="<H5>$val</H5>";
		}else{
		if(preg_match('#\s+[A-Za-z]#',$val)){
			if(preg_match('#(.+?):(.+)#',$val,$re)){
				$val="<strong>{$re[1]}:</strong>&nbsp;<strong style='color:red'>{$re[2]}</strong>";
			}
			$val="<div style='margin-left:40px;padding-left:10px'>$val</div>";
		}
		}
		
		$a=$a . $val;
		
	}
	
	
$html=
	main_tabs() . "
	
	<br>
	<div style='padding:5px;margin:5px'>$a</div>";	
	
	$tpl=new templates();
	echo $tpl->_parse_body($html);	
	
}


function main_configfile(){
$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;}else{$hostname=$_GET["hostname"];}
	$squid=new squid($hostname);
	$squid->BuildConfig();
	$squid->SaveToLdap();
	$conf=$squid->squid_conf;
	$table=explode("\n",$conf);
	
	$html=
	main_tabs() . "
	
	<br>
	<div style='padding:5px;margin:5px'>
	<table style='width:100%'>
	
	"; 
	
	while (list ($num, $val) = each ($table) ){
		$linenum=$num+1;
		$html=$html . "<tr><td width=1% style='background-color:#CCCCCCC'><strong>$linenum</strong></td><td width=99%'><code>$val</code></td></tr>";
		
		
	}
	$html=$html . "</table>
	
	</div>";
	
	$tpl=new templates();
	echo $tpl->_parse_body($html);
	
}

/*
auth_param basic program /opt/artica/libexec/digest_auth/ldap/digest_ldap_auth  -b "dc=my-domain,dc=com" -D "cn=admin,dc=my-domain,dc=com" -w 180872 -F "(&(objectClass=userAccount)(uid=%s))" -A "userPassword" -v 3 127.0.0.1
auth_param basic children 5
auth_param basic realm Squid proxy-caching web server
auth_param basic credentialsttl 2 hours
authenticate_ttl 1 hour
SQUID 3 http://www1.at.squid-cache.org/Versions/v3/3.0/cfgman/auth_param.html
*/
//
