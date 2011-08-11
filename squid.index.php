<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.squid.inc');
	
	
	$user=new usersMenus();
	if($user->SQUID_INSTALLED==false){header('location:users.index.php');exit();}
	if($user->AsSquidAdministrator==false){header('location:users.index.php');exit();}
	
	if(isset($_GET["page-index-squid-status"])){page_index_status();exit;}
	if(isset($_GET["kav-error"])){kavicap_license_error();exit;}
	if(isset($_GET["ApplyConfig"])){main_apply_conf();exit;}
	if(isset($_GET["status"])){main_little_status();exit;}
	if(isset($_GET["main"])){main_switch();exit;}
	if(isset($_GET["UpdateGeneralConfig"])){main_update_global_conf();exit;}
	if(isset($_GET["SquidActionService"])){service_action();exit;}
	if(isset($_GET["DansGuardianActionService"])){service_action();exit;}
	if(isset($_GET["Kav4ProxyActionService"])){service_action();exit;}
	if(isset($_GET["http_port_port"])){main_networking_addport();exit;}
	if(isset($_GET["squid_http_port_delete"])){main_networking_delport();exit;}
	if(isset($_GET["SquidAclAddrule"])){main_acls_add_rule();exit;}
	if(isset($_GET["SquidSelectAcl"])){echo main_acls_add_rule_type();exit;}
	if(isset($_GET["addacl"])){main_acls_add_rule_save();exit;}
	if(isset($_GET["SquidAclActiveRule"])){main_acl_rue_enable();exit;}
	if(isset($_GET["SquidAclLoadDatas"])){main_acls_load_datas();exit;}
	if(isset($_GET["acl_delete"])){main_acls_delete_datas();exit;}
	if(isset($_GET["rule_acl_delete"])){main_acls_delete_rule();exit;}
	if(isset($_GET["AccessSwitch"])){main_access_rules_switch();exit;}
	if(isset($_GET["SquidBackAccessRules"])){main_access_rules_restore();exit;}
	if(isset($_GET["SquidAccessMove"])){main_access_rules_move();exit;}
	if(isset($_GET["SquidAccessDelete"])){main_access_rules_delete();exit();}
	if(isset($_GET["SquidAccessRuleAdd"])){main_access_rules_miniform_add();exit;}
	if(isset($_GET["AddAccessAclRule"])){main_access_rules_add_acl();exit;}
	if(isset($_GET["SquidAccessInsertRule"])){main_access_rules_miniform_insertacl();exit;}
	if(isset($_GET["InsertIntoAccessAclRule"])){main_access_rules_insert_acl();exit;}
	if(isset($_GET["SquidAccessDeleteAcl"])){main_access_rules_delete_acl();exit;}
	if(isset($_GET["save_icap_service"])){main_icap_service_save();exit;}
	if(isset($_GET["SquidIcapDeleteService"])){main_icap_service_delete();exit;}
	if(isset($_GET["SquidIcapAddServiceFromTable"])){main_icap_class_addservice_ajax();exit;}
	if(isset($_GET["SquidIcapDeleteServiceInClass"])){main_icap_class_delservice_ajax();exit;}
	if(isset($_GET["SquidIcapAddNewClass"])){main_icap_class_add_ajax();exit;}
	if(isset($_GET["SquidIcapAddAccessRule"])){main_icap_access_ruleadd_ajax();exit;}
	if(isset($_GET["SquidIcapAccessSwitchEnable"])){main_icap_access_ruleswitch();exit;}
	if(isset($_GET["SquidIcapInsertAclRuleAjax"])){main_icap_access_acladd_ajax();exit;}
	if(isset($_GET["SquidIcapDeleteAccessAcl"])){main_icap_access_acldel_ajax();exit;}
	if(isset($_GET["SquidIcapDeleteAccessRule"])){main_icap_access_del_ajax();exit;}
	if(isset($_GET["SquidIcapAccessMove"])){main_icap_access_rulemove();exit;}
	if(isset($_GET["SquidIcapDeleteClass"])){main_icap_class_del_ajax();exit;}
	if(isset($_GET["cache_dir"])){main_cache_add();exit();}
	if(isset($_GET["SquidCacheDelete"])){main_cache_delete();exit;}
	if(isset($_GET["graph"])){main_graph();exit;}
	if(isset($_GET["dns_nameservers"])){main_networking_adddns();exit;}
	if(isset($_GET["squid_dnsserver_delete"])){main_networking_deldns();exit;}
	if(isset($_GET["StartError"])){main_status_analyse_viewstarterrors();exit;}
	
	


function main_switch(){
	if(!isset($_GET["tab"])){$_GET["tab"]="status";};
	$squid=new squid();
	if($_GET["hostname"]==null){
		$users=new usersMenus();
		$_GET["hostname"]=$users->hostname;
		}	

	switch ($_GET["tab"]) {
		case "status":main_status();break;
		case "config":main_configfile();break;
		case "networking":main_networking();break;
		case "access":main_access_rules();break;
		case "icap_service":main_icap_service();break;
		case "cache":main_cache();break;
		case "acl":main_acls();break;
		default:
			break;
	}
	
	
}


function main_tabs(){
	$page=CurrentPageName();
	$squid=new squid();
	$users=new usersMenus();
	$array["networking"]='{squid_net_settings}';
	$array["acl"]='{acls}';
	$array["access"]='{access_rules}';
	
	if($users->SQUID_ICAP_ENABLED){
		$array["icap_service"]='{icap_service}';
	}
	$array["cache"]='Cache';
	$array["status"]='{status}';	
	$array['config']='{config_file}';

	while (list ($num, $ligne) = each ($array) ){
		if($_GET["tab"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('squid_main_config','$page?main=yes&tab=$num&hostname={$_GET["hostname"]}')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";		
}	

function main_status_failed(){
	$sock=new sockets();
	$datas=$sock->getfile('squid_daemon_failed',$_GET["hostname"]);
	$tbl=explode("\n",$datas);
	while (list ($num, $ligne) = each ($tbl) ){
		if(preg_match('#FATAL:(.+)#',$ligne,$re)){
			$err[]=$re[1];
		}
		
	}
	if(is_array($err)){	
		
		$errors="<div style='padding:5px;color:red;font-weight:bold'><code>".implode("\n",$err)."</code></div>";
	
	}
	
	return $errors;
}


function main_status_saveconf(){
$apply=applysettings("squid","SQUID_APPLY('{$_GET["hostname"]}')");
$tpl=new templates();

$html=main_status_tab().RoundedLightGrey($apply);
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html."<br>");
}

function main_little_status(){
	main_status();
}

function main_status_tab(){
	
	$page=CurrentPageName();
	$squid=new squid();
	$array["connections"]='{connections}';
	$array["status"]='{status}';
	

	
	
	

	while (list ($num, $ligne) = each ($array) ){
		if($_GET["status"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('services_status_squid','$page?status=$num&hostname={$_GET["hostname"]}')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div><br>
	<input type='hidden' name='statusid' id='statusid' value='{$_GET["status"]}'>
	";		
	
}



function main_status(){
	
	
	$page=CurrentPageName();
	$tabs=main_tabs();
	$squid=new squid($_GET["hostname"]);
	$sock=new sockets();
	$ini=new Bs_IniHandler();
	
	$users=new usersMenus();
	$ini->loadString(base64_decode($sock->getFrameWork('cmd.php?squid-ini-status=yes')));
	$AllowAllNetworksInSquid=$sock->GET_INFO("AllowAllNetworksInSquid");
	if(!is_numeric($AllowAllNetworksInSquid)){$AllowAllNetworksInSquid=0;}
	
	

	$squid_status=DAEMON_STATUS_ROUND("SQUID",$ini,null,1);
	$dansguardian_status=DAEMON_STATUS_ROUND("DANSGUARDIAN",$ini,null,1);
	$kav=DAEMON_STATUS_ROUND("KAV4PROXY",$ini,null,1);
	$cicap=DAEMON_STATUS_ROUND("C-ICAP",$ini,null,1);
	$APP_PROXY_PAC=DAEMON_STATUS_ROUND("APP_PROXY_PAC",$ini,null,1);
	$APP_SQUIDGUARD_HTTP=DAEMON_STATUS_ROUND("APP_SQUIDGUARD_HTTP",$ini,null,1);
	$APP_UFDBGUARD=DAEMON_STATUS_ROUND("APP_UFDBGUARD",$ini,null,1);
	
	$md=md5(date('Ymhis'));
	
	$squid=new squidbee();

	if(count($squid->network_array)==0){
		if($AllowAllNetworksInSquid==0){
			$net=Paragraphe("warning64.png","{no_squid_network}","{no_squid_network_text}","javascript:Loadjs('squid.popups.php?script=network')",null,350);
		}
	}	

	$html="<table style='width:99%'>
	<tr>
		<td valign='top'><span id='kav-error'></span>$net$squid_status$kav$APP_UFDBGUARD$APP_SQUIDGUARD_HTTP</td>
		<td valign='top'>$dansguardian_status$cicap$APP_PROXY_PAC</td>
	</tr>
	
	
	</table>
	<center style='margin:15px'><hr><img src='images.listener.php?uri=squid/rrd/connections.hour.png&hostname={$_GET["hostname"]}&md=$md'></center>
	
	<script>
		LoadAjax('kav-error','$page?kav-error=yes');
	</script>
	
	
	";
	
	$tpl=new templates();
	echo $tpl->_parse_body($html);
	}
	

	
function kavicap_license_error(){
	$users=new usersMenus();
	$sock=new sockets();
	if(!$users->KAV4PROXY_INSTALLED){return null;}
	$kavicapserverEnabled=$sock->GET_INFO("kavicapserverEnabled");
	if($kavicapserverEnabled==null){$kavicapserverEnabled=0;}	
	if($kavicapserverEnabled==0){return null;}
	if(!$users->KAV4PROXY_LICENSE_ERROR){return null;}
	
	$pattern_date=trim(base64_decode($sock->getFrameWork("cmd.php?kav4proxy-pattern-date=yes")));
	if($pattern_date==null){
			$html="<table style='width:1OO%'>
				<tr>
				<td width=1% valign='top'>" . imgtootltip("42-red.png",'{av_pattern_database}',"Loadjs('Kav4Proxy.License.php')")."</td>
				<td valign='top'>". RoundedLightWhite("
						<strong>
							<span style='font-size:14px;color:red'>{av_pattern_database_obsolete_or_missing}</span><hr>
							
							")."
				</td>
				</tr>
				</table>";
							$tpl=new templates();
				echo $tpl->_ENGINE_parse_body(RoundedLightGreen($html)."<br>");	
				return;
	}

	$html="<table style='width:1OO%'>
<tr>
<td width=1% valign='top'>" . imgtootltip("42-red.png",'{license}',"Loadjs('Kav4Proxy.License.php')")."</td>
<td valign='top'>". RoundedLightWhite("
		<strong>
			<span style='font-size:14px;color:red'>{license_error} {APP_KAV4PROXY} !!</span><hr>
			<span style='font-size:11px;color:red'>$users->KAV4PROXY_LICENSE_ERROR_TEXT</span></strong>
			")."
</td>
</tr>
</table>";		

$tpl=new templates();
echo $tpl->_ENGINE_parse_body(RoundedLightGreen($html)."<br>");	
	
}
	
	
function main_icap_service(){
	$usermenus=new usersMenus(null,0,$_GET["hostname"]);
	$hostname=$_GET["hostname"];
	$page=CurrentPageName();
	
	switch ($_GET["subsection"]) {
		case "add-icap-service":echo main_icap_service_add_service_form();exit;break;
		case "list-icap-service":echo main_icap_service_list();exit;break;
		case "list-icap-class":echo main_icap_class_list();exit;break;
		case "form-class-addservice":echo main_icap_class_addservice_form();exit;break;
		case "add-icap-class":main_icap_class_add_form();exit;break;
		case "list-icap-access-list":main_icap_access_table();exit();break;
		case "add-icap-access-list":main_icap_access_ruleadd();exit;break;
		case "add-icap-access-acl":main_icap_access_acladd();exit;break;
		case "rebuild-kav4proxy-services":
				main_icap_rebuildKaspersky();
				echo main_icap_service_list();exit;break;
		}
	
	
	$service=main_icap_service_list();
	$classes=main_icap_class_list();
	$roll=CellRollOver();
	$right_menus="
	<table style='width:100%'>
	<tr><td colspan=2><strong style='font-size:12px'>{icap_service_menus}</strong></td></tr>
	<tr $roll>
	<td width=1%><img src='img/fw_bold.gif'></td>
	<td><strong><a href=\"javascript:LoadAjax('icap_section','$page?main=yes&tab=icap_service&hostname=$hostname&subsection=list-icap-service')\">{icap_service}</a></td>
	</tr>
	<tr $roll>
	<td width=1%><img src='img/fw_bold.gif'></td>
	<td><strong><a href=\"javascript:LoadAjax('icap_section','$page?main=yes&tab=icap_service&hostname=$hostname&subsection=add-icap-service')\">{add_icap_service}</a></td>
	</tr>";
	
	if($usermenus->KAV4PROXY_INSTALLED){
		$right_menus=$right_menus."
			<tr $roll>
				<td width=1%><img src='img/fw_bold.gif'></td>
				<td>
					<strong>
					<a href=\"javascript:LoadAjax('icap_section','$page?main=yes&tab=icap_service&hostname=$hostname&subsection=rebuild-kav4proxy-services');
					LoadAjax('icap_class_section','$page?main=yes&tab=icap_service&hostname=$hostname&subsection=list-icap-class');
					\">{rebuild_kaspersky_service}</a>
					</strong>
				</td>
			</tr>";
		
	}
	
	
	$right_menus=$right_menus."</table>";
	$right_menus=RoundedLightGreen($right_menus);
	
	
$right_menus2="
	<table style='width:100%'>
	<tr><td colspan=2><strong style='font-size:12px'>{group_service_menus}</strong></td></tr>
	<tr $roll>
	<td width=1%><img src='img/fw_bold.gif'></td>
	<td><strong><a href=\"javascript:LoadAjax('icap_class_section','squid.index.php?main=yes&tab=icap_service&hostname=$hostname&subsection=list-icap-class');\">{icap_class_list}</a></td>
	</tr>	
	<tr $roll>
	<td width=1%><img src='img/fw_bold.gif'></td>
	<td><strong><a href=\"javascript:LoadAjax('icap_class_section','$page?main=yes&tab=icap_service&hostname=$hostname&subsection=add-icap-class')\">{add_icap_class}</a></td>
	</tr>	
	
	</table>";

$right_menus3="
	<table style='width:100%'>
	<tr><td colspan=2><strong style='font-size:12px'>{icap_access}</strong></td></tr>
	<tr $roll>
	<td width=1%><img src='img/fw_bold.gif'></td>
	<td><strong><a href=\"javascript:icap_access_section('$hostname');\">{access_list}</a></td>
	</tr>	
	<tr $roll>
	<td width=1%><img src='img/fw_bold.gif'></td>
	<td><strong><a href=\"javascript:icap_access_newrule('$hostname')\">{add_icap_rule}</a></td>
	</tr>	
	
	</table>";
	
$right_menus2=RoundedLightGreen($right_menus2);	
$right_menus3=RoundedLightGreen($right_menus3);	
	
$html=main_tabs()."<br>
	<H5>{icap_service}</H5>
	<table style='width:100%'>
	<tr>
	<td valign='top' width=60%><div id='icap_section'>$service</div><br><div id='icap_class_section'>$classes</div></td>
	<td valign='top' width=40%>$right_menus <br>$right_menus2<br>$right_menus3</td>
	</tr>
	
	</table>
	
	";
	
	$tpl=new templates();
	echo $tpl->_parse_body($html);	
}

function main_icap_class_list(){
	$squid=new squid($_GET["hostname"]);
	$hostname=$_GET["hostname"];
	if(!is_array($squid->icap_class_array)){return null;}
	reset($squid->icap_class_array);
	$roll=CellRollOver();
$html="


<table style='width:100%'>
	<tr style='background-color:#CCCCCC'>
	<td>&nbsp;</td>
	<td nowrap><strong>{class_name}</td>
	<td><strong>{icap_service}</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	</tr>
	
	";	
	while (list ($num, $ligne) = each ($squid->icap_class_array) ){
		$html=$html . "<tr $roll>
		<td width=1% valign='top'><img src='img/service-view.gif'></td>
		<td valign='top'><strong>$num</strong></td>
		";
		
		
		if(is_array($ligne)){
			$classes="<table style='width:100%'>";
			while (list ($a, $b) = each ($ligne) ){
			$classes=$classes . "
					<tr>
						<td width=1% valign='top'><img src='img/fw_bold.gif'></td>
						<td>" . texttooltip($b,'{delete_service_in_class}',null,"SquidIcapDeleteServiceInClass('$hostname','$num','$a')")."</td>
					</tr>";
			}
			$classes=$classes . "</table>";
		}
		$html=$html . "<td valign='top'>$classes<span id='form_class_{$num}'></span></td>";
		$html=$html . "<td valign='top' width=1%>" . imgtootltip('plus-16.png','{add_a_service_in_group}',"SquidIcapAddServiceInClass('$hostname','$num')")."</td>";
		$html=$html . "<td valign='top' width=1%>" . imgtootltip('x.gif','{delete_icap_class}',"SquidIcapDeleteClass('$hostname','$num')")."</td>";
		
	}
	
$html=$html . "</table>";
	$tpl=new templates();
	
	return $tpl->_ENGINE_parse_body("<br>
<H5>{icap_class_title}</H5>
<div class=caption>{icap_class_title_text}</div>".RoundedLightGrey($html));	
	
}

function main_icap_class_add_form(){
	$hostname=$_GET["hostname"];	
	$squid=new squid($_GET["hostname"]);
	$tpl=new templates();
	
	if(!is_array($squid->icap_service_array)){echo $tpl->_ENGINE_parse_body("{must_icap_service_before}");exit;}
	reset($squid->icap_service_array);
	while (list ($num, $ligne) = each ($squid->icap_service_array) ){$arr[$num]=$num;}
	$num[null]='{select}';
	$field=Field_array_Hash($arr,'icap_service',null,null,null,0,'width:200px');
	$html="
	<H5>{add_icap_class}</H5>
	<table style='width:100%'>
	<tr>
		<td nowrap><strong>{class_name}:</strong></td>
		<td>" . Field_text('class_name')."</td>
	</tr>	
	<tr>
		<td nowrap><strong>{icap_service}:</strong></td>
		<td>$field</td>
	</tr>
	<tr>
		<td colspan=2 align='right'><input type='button' value='{add}&nbsp;&raquo;' OnClick=\"javascript:SquidIcapAddNewClass('$hostname');\"></td>
	</tr>
	</table>";
	
	echo "<br>".RoundedLightGreen($tpl->_ENGINE_parse_body($html))."<br>";	
	
}

function main_icap_access_table(){
	$hostname=$_GET["hostname"];
	$squid=new squid($_GET["hostname"]);
	$rool=CellRollOver();
	if(is_array($squid->restrictions_array["icap_access"]["rules"])){
		$rules="<table style='width:100%'>
		<tr style='background-color:#CCCCCC'>
		<td>&nbsp;</td>
		<td><strong>{icap_class}</strong></td>
		<td><strong>{acl}</strong></td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		</tr>
		
		";
		while (list ($num, $ligne) = each ($squid->restrictions_array["icap_access"]["rules"]) ){
			$newarr=main_icap_access_table_explode_line($ligne,$num);
			$rules=$rules . "
			<tr $rool>
			<td width=1% valign='top'>{$newarr["allow"]}</td>
			<td width=1% nowrap valign='top'><strong>{$newarr["icap_class"]}</strong></td>
			<td valign='top'>";
				if(is_array($newarr["acl_list"])){
					$rules=$rules . "<table style='width:100%'>";
					
					while (list ($num1, $ligne) = each ($newarr["acl_list"]) ){
						$rules=$rules . "
							<tr>
							<td width=1%><img src='img/fw_bold.gif'></td>
							<td><strong>" . texttooltip($ligne,'{delete_acl_in_access}',null,"SquidIcapDeleteAccessAcl('$hostname','$num1','$num')")."</strong></td>
							</tr>";
							}
					$rules=$rules . "</table>";}
					
			$rules=$rules . "
			<span id='icap_access_insert_rule_{$num}'></span>
			
			</td>
			<td width=1% valign='top'>" . imgtootltip('arrow_down.gif','{down}',"SquidIcapAccessMove('$hostname','$num','down')")."</TD>
			<td width=1% valign='top'>" . imgtootltip('arrow_up.gif','{up}',"SquidIcapAccessMove('$hostname','$num','up')")."</TD>
			<td valign='top'>
			<table style='width:100%'>
			<tr>
				<td width=1%>" . imgtootltip('plus-16.png','{add_acl}',"SquidIcapInsertAclRule('$hostname',$num)") . "</td>
				<td width=1%>" . imgtootltip('rule-add-16.png','{add_rule_here}',"SquidIcapInsertAccessRule('$hostname',$num)") . "</td>
				<td width=1%>" . imgtootltip('x.gif','{delete}',"SquidIcapDeleteAccessRule('$hostname',$num)") . "</td>
			</tr>
			</table>";
			
		}
		
	$rules=$rules."</table>";
	$rules=RoundedLightGrey($rules);
	}
	
	
	
	$html="
	<H6>{icap_access}</h6>
	<div class=caption>{icap_access_text}</div>
	<br>$rules
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function main_icap_access_table_explode_line($ligne,$index){
	
	if(preg_match('#^(.+?)\s+(allow|deny)\s+(.+)#i',$ligne,$re)){
		
		if($re[2]=='allow'){
			$arr["allow"]=imgtootltip('fleche-20.png','{acl_rule_allow}',"SquidIcapAccessSwitchEnable('{$_GET["hostname"]}',$index)");
		}else{
		    $arr["allow"]=imgtootltip('fleche-20-red.png','{acl_rule_deny}',"SquidIcapAccessSwitchEnable('{$_GET["hostname"]}',$index)");	
		}
		$arr["icap_class"]=$re[1];
		$arr["acls"]=$re[3];
		}
		
		$tbl=explode(' ',$arr["acls"]);
		if(!is_array($tbl)){return null;}
		while (list ($num, $ligne) = each ($tbl) ){
			if(trim($ligne)<>null){
				if(substr($ligne,0,1)=='!'){
					$table[]="{and} {isnot} ".substr($ligne,1,strlen($ligne));
				}else{
					$table[]="{and} {is} $ligne";
				}
			}
			
		}
		
		
	$arr["acl_list"]=$table;
	return $arr;
	
}

function main_icap_access_acladd(){
	$hostname=$_GET["hostname"];
	$tpl=new templates();
	$squid=new squid($_GET["hostname"]);
	if(!is_array($squid->acls_rules_array)){echo $tpl->_ENGINE_parse_body('{must_create_acl_first}');exit;}
	while (list ($num, $ligne) = each ($squid->acls_rules_array) ){
		if($ligne["enabled"]=='yes'){
				$arr_acl[$num]="$num ({acl_{$ligne["acl_type"]}})";
				}
	}
	$acl[null]='{select}';
	$acl=Field_array_Hash($arr_acl,'icap_acl',null,null,null,0,'width:200px');
	
	$html="
	<table style='width:1OO%'>
	<tr><td><strong>{add_acl}</strong></td></tr>
	<tr><td><strong>{isnot}</strong>" . Field_checkbox('isnot',1,0)."</td></tr>
	<tr><td>$acl</td></tr>
	<tr><td align='right'><input type='button' value='{add}&nbsp;&raquo;' OnClick=\"javascript:SquidIcapInsertAclRuleAjax('{$_GET["hostname"]}','{$_GET["SquidIcapInsertAclRule"]}');\"></td></tr>
	</table>
	
	";
	
	
	echo RoundedLightGreen($tpl->_ENGINE_parse_body($html))."<br>";
	
	
}

function main_icap_access_acladd_ajax(){
	$squid=new squid($_GET["hostname"]);
	if(trim($_GET["SquidIcapInsertAclRuleAjax"])==null){return null;}
	$squid->icap_restrictions_addacl($_GET["SquidIcapInsertAclRuleAjax"],$_GET["isnot"],$_GET["index"]);
	}
function main_icap_access_del_ajax(){
	$squid=new squid($_GET["hostname"]);
	$squid->icap_restrictions_delete($_GET["SquidIcapDeleteAccessRule"]);
	}
	
function main_icap_access_rulemove(){
	$squid=new squid($_GET["hostname"]);	
	$squid->icap_restrictions_move($_GET["SquidIcapAccessMove"],$_GET["move"]);
}

function main_icap_access_ruleadd(){
	$hostname=$_GET["hostname"];
	$squid=new squid($_GET["hostname"]);
	if(isset($_GET["SquidIcapAccessInsertRule"])){
	$SquidIcapAccessInsertRule=$_GET["SquidIcapAccessInsertRule"];
	}else{$SquidIcapAccessInsertRule='no';}
	
	$tpl=new templates();
	
	if(!is_array($squid->icap_class_array)){echo $tpl->_ENGINE_parse_body('{must_create_group_first}');exit;}
	
	
	reset($squid->icap_class_array);
	while (list ($num, $ligne) = each ($squid->icap_class_array) ){
		$arr[$num]=$num;
		}
	
	
	
	
	$arr[null]='{select}';
	
	$icap_class=Field_array_Hash($arr,'class_name',null,null,null,0,'width:150px');
	$switchA=array(null=>'{select}','allow'=>'{allow}','deny'=>'{deny}');
	$switch=Field_array_Hash($switchA,'class_switch',null,null,null,0,'width:100px');
	if(!is_array($squid->acls_rules_array)){echo $tpl->_ENGINE_parse_body('{must_create_acl_first}');exit;}
		
	
	reset($squid->acls_rules_array);
	while (list ($num, $ligne) = each ($squid->acls_rules_array) ){
		if($ligne["enabled"]=='yes'){
				$arr_acl[$num]="$num ({acl_{$ligne["acl_type"]}})";
				}
	}
	$acl[null]='{select}';
	$acl=Field_array_Hash($arr_acl,'icap_acl',null,null,null,0,'width:200px');
	
	$html="
	<H6>{add_icap_rule}</h6>
	<input type='hidden' id='SquidIcapAccessInsertRule' value='$SquidIcapAccessInsertRule'>	
	<table style='width:100%'>
	<tr>
		<td align='right' nowrap><strong>{access_rule}:</td>
		<td>$switch</td>
	</tr>	
	<tr>
		<td align='right' nowrap><strong>{icap_class}:</td>
		<td>$icap_class</td>
	</tr>
	<tr>
		<td align='right' nowrap><strong>{isnot}:</td>
		<td>"  . Field_checkbox('isnot',1,0)."</td>
	</tr>	
	<tr>
		<td align='right' nowrap><strong>{acl}:</td>
		<td>$acl</td>
	</tr>
	<tr>
	<td colspan=2 align='right'><input type='button' value='{add}&nbsp;&raquo;' OnClick=\"javascript:SquidIcapAddAccessRule('$hostname');\"></td>
	</tr>			
	</table>";
	
	echo RoundedLightGreen($tpl->_ENGINE_parse_body($html));
	
	
	
}


function main_icap_class_addservice_form(){
	$class_name=$_GET["class_name"];
	$hostname=$_GET["hostname"];
	$squid=new squid($_GET["hostname"]);
	$tpl=new templates();
	if(!is_array($squid->icap_service_array)){echo $tpl->_ENGINE_parse_body("{must_icap_service_before}");exit;}
	
	reset($squid->icap_service_array);
	while (list ($num, $ligne) = each ($squid->icap_service_array) ){$arr[$num]=$num;}
	$num[null]='{select}';
	$field=Field_array_Hash($arr,$class_name.'_icap_service');
	$html="
	<input type='hidden' id='hostname' value='$hostname'>
	<table style='width:100%'>
	<tr>
		<td nowrap><strong>{icap_service}:</strong></td>
		<td>$field</td>
		<td><input type='button' value='{add}&nbsp;&raquo;' OnClick=\"javascript:SquidIcapAddServiceFromTable('$hostname','$class_name');\"></td>
	</tr>
	</table>";
	
	echo "<br>".RoundedLightGreen($tpl->_ENGINE_parse_body($html))."<br>";
}

function main_icap_class_addservice_ajax(){
	$class_name=$_GET["class_name"];
	$hostname=$_GET["hostname"];	
	$service=$_GET["SquidIcapAddServiceFromTable"];
	$squid=new squid($_GET["hostname"]);
	$squid->icap_add_service_in_class($class_name,$service);
	}
function main_icap_class_delservice_ajax(){
	$class_name=$_GET["class_name"];
	$hostname=$_GET["hostname"];
	$index=$_GET["SquidIcapDeleteServiceInClass"];
	$squid=new squid($_GET["hostname"]);
	$squid->icap_del_service_in_class($class_name,$index);
	}
function main_icap_class_add_ajax(){
	$class_name=$_GET["SquidIcapAddNewClass"];
	$hostname=$_GET["hostname"];
	$icap_service=$_GET["icap_service"];
	if(trim($icap_service)==null){return null;}
	$squid=new squid($_GET["hostname"]);	
	$squid->Add_icap_class($class_name,$icap_service);
	$squid->SaveToLdap();
	}
	
function main_icap_class_del_ajax(){
	$class_name=$_GET["SquidIcapDeleteClass"];
	$hostname=$_GET["hostname"];	
	$squid=new squid($_GET["hostname"]);
	$squid->Del_icap_class_all($class_name);
	$squid->SaveToLdap();	
	}
function main_icap_access_ruleadd_ajax(){
	$class_name=$_GET["class_name"];
	$hostname=$_GET["hostname"];
	$class_switch=$_GET["class_switch"];
	$icap_acl=$_GET["icap_acl"];
	$isnot=$_GET["isnot"];
	$SquidIcapAccessInsertRule=$_GET["SquidIcapAccessInsertRule"];
	
	if(trim($class_name)==null){return null;}
	if(trim($class_switch)==null){return null;}
	if(trim($icap_acl)==null){return null;}
	$squid=new squid($_GET["hostname"]);

	if(is_numeric($SquidIcapAccessInsertRule)){
		$squid->icap_insert_restrictions_rule($class_name,$class_switch,$icap_acl,$isnot,$SquidIcapAccessInsertRule);
		
	}else{
		$squid->icap_add_restrictions_rule($class_name,$class_switch,$icap_acl,$isnot);
	}
	
	}
function main_icap_access_ruleswitch(){
	$hostname=$_GET["hostname"];
	$squid=new squid($_GET["hostname"]);	
	$squid->icap_restrictions_switch($_GET["SquidIcapAccessSwitchEnable"]);
	}
function main_icap_access_acldel_ajax(){
	$hostname=$_GET["hostname"];
	$squid=new squid($_GET["hostname"]);		
	$squid->icap_restrictions_delacl($_GET["icap_access"],$_GET["SquidIcapDeleteAccessAcl"]);
}
	
	


function main_icap_service_list(){
	$squid=new squid($_GET["hostname"]);
	$roll=CellRollOver();
	if(!is_array($squid->icap_service_array)){return null;}
	reset($squid->icap_service_array);
	$html="<table style='width:100%'>
	<tr style='background-color:#CCCCCC'>
	<td>&nbsp;</td>
	<td nowrap><strong>{service_name}</td>
	<td><strong>{vpoint}</td>
	<td><strong>{bypass}</td>
	<td><strong>{url}</td>
	<td>&nbsp;</td>
	</tr>
	
	";
	while (list ($num, $ligne) = each ($squid->icap_service_array) ){
		
		if($ligne["bypass"]==0){$ligne["bypass"]='no';}else{$ligne["bypass"]='yes';}
		$uri=CellRollOver("LoadAjax('icap_section','$page?main=yes&tab=icap_service&hostname=$hostname&subsection=add-icap-service&service=$num')");
		$html=$html . "<tr $roll>
		<td width=1%><img src='img/service-view.gif'></td>
		<td nowrap $uri>{$ligne["name"]}</td>
		<td nowrap $uri>{{$ligne["vpoint"]}}</td>
		<td nowrap $uri>{$ligne["bypass"]}</td>
		<td nowrap $uri>{$ligne["url"]}</td>
		<td width=1%>" . imgtootltip('x.gif',"{delete} :{$ligne["name"]}","SquidIcapDeleteService('{$_GET["hostname"]}','{$ligne["name"]}')")."</td>
		</tr>
		
		";
		
	}
	
	$html=$html . "</table>";
	$tpl=new templates();
	$html=$tpl->_ENGINE_parse_body($html);
	return RoundedLightGrey($html);
	
}


function main_icap_service_add_service_form(){
	
	$icap_service_name=Field_text('icap_service_name');
	
	if(isset($_GET["service"])){
		$squid=new squid($_GET["hostname"]);
		$serv=$squid->icap_service_array[$_GET["service"]];
		$hidden="<input type='hidden' id='service_exists' name='service_exists' value='{$_GET["service"]}'>";
		$icap_service_name="<input type='hidden' id='icap_service_name' name='icap_service_name' value='{$_GET["service"]}'><strong>{$_GET["service"]}</strong>";
		}
	
	
	$array_vpoint=array("reqmod_precache"=>"{reqmod_precache}","reqmod_postcache"=>"{reqmod_postcache}","respmod_precache"=>"{respmod_precache}","respmod_postcache"=>"{respmod_postcache}");
	
	$html="
	<form name='icap_service_form'>
	$hidden
	<input type='hidden' name='save_icap_service' value='yes'>
	<input type='hidden' name='hostname' value='{$_GET["hostname"]}'>
	<table style='width:100%'>
	<tr>
		<td nowrap align='right'><strong>{service_name}</strong>:</td>
		<td>$icap_service_name</td>
	</tr>
	<tr>
		<td nowrap align='right'><strong>{vpoint}</strong>:</td>
		<td>" . Field_array_Hash($array_vpoint,'icap_service_vpoint',$serv["vpoint"]) . "</td>
	</tr>	
	<tr>
		<td nowrap align='right'><strong>{bypass}</strong>:</td>
		<td>" . Field_checkbox('icap_service_bypass',1,$serv["bypass"]) . "</td>
	</tr>	
	<tr>
		<td nowrap align='right'><strong>{url}</strong>:</td>
		<td>icap://" . Field_text('icap_service_url',$serv["url"],'width:180px') . "</td>
	</tr>	
	<tr>
	<td colspan=2 align='right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:icap_edit_service('{$_GET["hostname"]}');\"></td>
	</tr>	
	</table>
	</form>
	";
	
	$html=RoundedLightGrey($html);
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
	
	
}
	
	
function main_access_rules(){
	
	switch ($_GET["rulename"]) {
		case "http_access":main_access_rules_http_access();break;
		case "http_reply_access";main_access_rules_http_reply_access();break;
		case "icp_access";main_access_rules_icp_access();break;
		case "miss_access";main_access_rules_miss_access();break;
		case "ident_lookup_access";main_access_rules_ident_lookup_access();break;
		
		default:main_access_rules_http_access();break;
	}
	
}


function main_access_rules_menu(){
	
	$array["http_access"]="http_access";
	$array["http_reply_access"]="http_reply_access";
	$array["icp_access"]="icp_access";
	$array["miss_access"]="miss_access";
	$array["ident_lookup_access"]="ident_lookup_access";
	
	

	
$menus="<strong style='font-size:12px'>{switch_access_rules}</strong><table style='width:100%'>";
if(!isset($_GET["rulename"])){$_GET["rulename"]="http_access";}

while (list ($num, $ligne) = each ($array) ){
	
	if($_GET["rulename"]==$ligne){
		$color="red";
	}else{$color="black";}
	$menus=$menus . "
	<tr>
	<td width=1%><img src='img/fw_bold.gif'></td>
	<td nowrap><strong><a href=\"javascript:void(0);\" OnClick=\"javascript:SwitchSquidActionAccess('$hostname','$ligne');\" style='color:$color'>{{$ligne}}</strong></a></td>
	</tr>

	";	
	
}

$menus=$menus . "	</table>";
return RoundedLightGreen($menus);
}

function main_access_rules_miss_access(){
	$squid=new squid($_GET["hostname"]);
	$hostname=$_GET["hostname"];
	$http_access=main_access_rules_table('miss_access',$squid);
	$back_config=RoundedLightGrey(Paragraphe('back-64.png','{access_rules_back}','{access_rules_back_text}',"javascript:SquidBackAccessRules(\"$hostname\")"));
	
	
	$menus=main_access_rules_menu();

	$html=main_tabs()."
	<div class=caption style='padding:5px'>{miss_access_text}</div>
	<table style='width:100%'>
	<tr>
	<td valign='top' width=60%>$http_access</td>
	<td valign='top' width=40%>$menus <br>$back_config</td>
	</tr>
	</table>
	
	";
	
	$tpl=new templates();
	echo $tpl->_parse_body($html);
	}
	
	
function main_access_rules_ident_lookup_access(){
	$squid=new squid($_GET["hostname"]);
	$hostname=$_GET["hostname"];
	$http_access=main_access_rules_table('ident_lookup_access',$squid);
	$back_config=RoundedLightGrey(Paragraphe('back-64.png','{access_rules_back}','{access_rules_back_text}',"javascript:SquidBackAccessRules(\"$hostname\")"));
	
	
	$menus=main_access_rules_menu();

	$html=main_tabs()."
	<div class=caption style='padding:5px'>{ident_lookup_access_text}</div>
	<table style='width:100%'>
	<tr>
	<td valign='top' width=60%>$http_access</td>
	<td valign='top' width=40%>$menus <br>$back_config</td>
	</tr>
	</table>
	
	";
	
	$tpl=new templates();
	echo $tpl->_parse_body($html);	
	
	
}

function main_access_rules_http_access(){
	$squid=new squid($_GET["hostname"]);
	$hostname=$_GET["hostname"];
	$http_access=main_access_rules_table('http_access',$squid);
	$back_config=RoundedLightGrey(Paragraphe('back-64.png','{access_rules_back}','{access_rules_back_text}',"javascript:SquidBackAccessRules(\"$hostname\")"));
	
	
	$menus=main_access_rules_menu();

	$html=main_tabs()."
	<div class=caption style='padding:5px'>{access_rules_text}</div>
	<table style='width:100%'>
	<tr>
	<td valign='top' width=60%>$http_access</td>
	<td valign='top' width=40%>$menus <br>$back_config</td>
	</tr>
	</table>
	
	";
	
	$tpl=new templates();
	echo $tpl->_parse_body($html);
	}

function main_access_rules_http_reply_access(){
	$squid=new squid($_GET["hostname"]);
	$hostname=$_GET["hostname"];
	
	$http_access=main_access_rules_table('http_reply_access',$squid);
	$menus=main_access_rules_menu();
	
	$back_config=RoundedLightGrey(Paragraphe('back-64.png','{access_rules_back}','{access_rules_back_text}',"javascript:SquidBackAccessRules(\"$hostname\")"));

	$html=main_tabs()."<div class=caption style='padding:5px'>{http_reply_access_text}</div>
	<table style='width:100%'>
	<tr>
	<td valign='top' width=60%>$http_access</td>
	<td valign='top' width=40%>$menus<br>$back_config</td>
	</tr>
	</table>
	
	";
	
	$tpl=new templates();
	echo $tpl->_parse_body($html);
	}
function main_access_rules_icp_access(){
	$squid=new squid($_GET["hostname"]);
	$hostname=$_GET["hostname"];
	
	$http_access=main_access_rules_table('icp_access',$squid);
	$menus=main_access_rules_menu();
	
	$back_config=RoundedLightGrey(Paragraphe('back-64.png','{access_rules_back}','{access_rules_back_text}',"javascript:SquidBackAccessRules(\"$hostname\")"));

	$html=main_tabs()."<div class=caption style='padding:5px'>{icp_access_text}</div>
	<table style='width:100%'>
	<tr>
	<td valign='top' width=60%>$http_access</td>
	<td valign='top' width=40%>$menus<br>$back_config</td>
	</tr>
	</table>
	
	";
	
	$tpl=new templates();
	echo $tpl->_parse_body($html);
	}


function main_access_rules_miniform_add(){
	$hostname=$_GET["hostname"];
	$squid=new squid($hostname);
	$acls=$squid->acls_rules_array;
	$rulename=$_GET["rulename"];
	$index=$_GET["index"];
	
	$key=md5($hostname.$rulename.$index);
	if(is_array($acls)){
		while (list ($num, $val) = each ($acls)){
			
			if($val["enabled"]=='yes'){
				$array[$num]=$num;
			}
		}
	$array[null]='{select}';
	}
	
	$field="
		<input type='hidden' id='{$key}_hostname' value='$hostname'>
		<input type='hidden' id='{$key}_rulename' value='$rulename'>
		<input type='hidden' id='{$key}_index' value='$index'>	
		<table style='width:150px'>	
		<tr>
		<td width=1% nowrap>{and}</td>
		<td width=1% nowrap>{isnot} " . Field_yesno_checkbox("{$key}_isnot",'no')."</td>
		<td>" . Field_array_Hash($array,"{$key}_acl",null,null,null,0,'width:100px')."</td>
		<td align='right'><input type='button' OnClick=\"javascript:AddAccessAclRule('$key');\" value='{add}&nbsp;&raquo;'></td>
		</tr>
		</table>";
	
	$tpl=new templates();
	echo RoundedLightGreen($tpl->_ENGINE_parse_body($field));
	
}



function main_access_rules_miniform_insertacl(){
	$hostname=$_GET["hostname"];
	$rulename=$_GET["SquidAccessInsertRule"];
	$index=$_GET["index"];
	
	$squid=new squid($hostname);
	$acls=$squid->acls_rules_array;	
	
	$key=md5($hostname.$rulename.$index);
	if(is_array($acls)){
		while (list ($num, $val) = each ($acls)){
			
			if($val["enabled"]=='yes'){
				$array[$num]=$num;
			}
		}
	$array[null]='{select}';
	}
	
	$field="
		<input type='hidden' id='{$key}_hostname' value='$hostname'>
		<input type='hidden' id='{$key}_rulename' value='$rulename'>
		<input type='hidden' id='{$key}_index' value='$index'>	
		<table style='width:150px'>	
		<tr>
		<td width=1% nowrap>{isnot} " . Field_yesno_checkbox("{$key}_isnot",'no')."</td>
		<td>" . Field_array_Hash($array,"{$key}_acl",null,null,null,0,'width:100px')."</td>
		<td align='right'><input type='button' OnClick=\"javascript:InsertIntoAccessAclRule('$key');\" value='{add}&nbsp;&raquo;'></td>
		</tr>
		</table>";
	
	$tpl=new templates();
	echo RoundedLightGreen($tpl->_ENGINE_parse_body($field));	
	
	
}


function main_access_rules_table($keyname,$squidClass){
	$hostname=$_GET["hostname"];

	$main_array=$squidClass->restrictions_array;
	$array=$main_array[$keyname];

	if(is_array($array)){
		if(is_array($array["rules"])){
			$http_access_table="<table style='width:100%' style='margin:4px'>";
			while (list ($num, $val) = each ($array["rules"])){
				
				if(preg_match('#^allow#',$val)){
					$val=substr($val,5,strlen($val));
					$img="fleche-20.png";
					$text='{acl_rule_allow}';
				}
				
				if(preg_match('#^deny#',$val)){
					$val=substr($val,4,strlen($val));
					$img="fleche-20-red.png";
					$text='{acl_rule_deny}';
				}
				
				$val=main_access_rules_table_buildline($val,$num,$keyname,$squidClass);
				$http_access_table=$http_access_table . "
				<tr>
					<td width=1% valign='top'>" . imgtootltip($img,$text,"SquidAccessSwitchEnable('$hostname','$num','$keyname')")."</td>
					<td valign='top'><strong>$val</strong><span id='access_acl_rule_edit_{$keyname}_{$num}'></span></td>
					<td width=1% valign='top'>" . imgtootltip('plus-16.png','{add_acl}',
					"LoadAjax('access_acl_rule_edit_{$keyname}_{$num}','squid.index.php?SquidAccessRuleAdd=yes&hostname=$hostname&rulename=$keyname&index=$num');")."</TD>
					<td width=1% valign='top'>" . imgtootltip('arrow_down.gif','{down}',"SquidAccessMove('$hostname','$num','$keyname','down')")."</TD>
					<td width=1% valign='top'>" . imgtootltip('arrow_up.gif','{up}',"SquidAccessMove('$hostname','$num','$keyname','up')")."</TD>
					<td width=1% valign='top'>" . imgtootltip('x.gif','{delete}',"SquidAccessDelete('$hostname','$num','$keyname')")."</TD>
					<td width=1% valign='top'>" . imgtootltip('rule-add-16.png','{add_rule_here}',"SquidAccessInsertRule('$hostname','$num','$keyname')")."</TD>
				</tr>
				";
			}
		$http_access_table=$http_access_table . "</table>";}
	}
	
	$http_access_table="<table style='width:100%'>
	<tr>
	<td nowrap><strong style='font-size:12px;margin-bottom:4px'>{{$keyname}}</strong></td>
	<td><span id='access_acl_rule_edit_{$keyname}_0'></span></td>
	<td width=1% valign='top'>" . imgtootltip('rule-add-16.png','{add_rule_here}',"SquidAccessInsertRule('$hostname','0','$keyname')")."</TD>
	</tr>
	</table>
	<br>$http_access_table";
	return RoundedLightGrey($http_access_table);	
	
}


function main_access_rules_table_buildline($val,$index,$keyname,$squidClass){
		$val=trim($val);
		$hostname=$_GET["hostname"];
		$table=explode(" ",$val);
		$html="<table style='width:1%'><tr>";

		
		
		
		while (list ($num, $line) = each ($table)){
			
			if(trim($line)<>null){
	
			$html=$html . "<td nowrap>";
				if(substr($line,0,1)=='!'){
					$line=str_replace('!','',$line);
					
		 			if($squidClass->acls_rules_array[$line]["enabled"]=="no"){
		 				$addontool="<b>ACL disabled</b><br>";
		 				$line="<span style='color:#CCCCCC'>$line</span>";}
			 		
			 	
			 			
					$line=texttooltip($line,"$addontool<b>double-click</b> to delete this acl",null,"SquidAccessDeleteAcl('$hostname','$keyname','$index','$num')");
					$html=$html . "{and} {isnot} $line</td>";}
				else{
					$line=texttooltip($line,"$addontool<b>double-click</b> to delete this acl",null,"SquidAccessDeleteAcl('$hostname','$keyname','$index','$num')");
					$html=$html . "{and} {is} $line</td>";
					}
					
				}
				
			}
		$html=$html . "</tr></table>";
		return $html;
		}
		
function main_access_rules_delete_acl(){
	$squid=new squid($_GET["hostname"]);
	$squid->Restriction_delete_acl($_GET["SquidAccessDeleteAcl"],$_GET["index"],$_GET["acl_index"]);
	}
	
function main_networking_adddns(){
	writelogs("Add new dns {$_GET["dns_nameservers"]}",__FUNCTION__,__FILE__);
	$squid=new squid($_GET["hostname"]);
	$squid->dns_nameservers_array[]=$_GET["dns_nameservers"];
	$squid->SaveToLdap();
}
function main_networking_deldns(){
$squid=new squid($_GET["hostname"]);
	unset($squid->dns_nameservers_array[$_GET["squid_dnsserver_delete"]]);
	$squid->SaveToLdap();	
	
}


function main_networking_addport(){
		$squid=new squid($_GET["hostname"]);
		$http_mode=$_GET["http_mode"];
		if($_GET["http_port_ip"]<>null){$net=$_GET["http_port_ip"].":".$_GET["http_port_port"];}else{$net=$_GET["http_port_port"];}
		
		if($http_mode=='http'){
			$squid->http_port_array[]=$net;
			}
			
		if($http_mode=='https'){	
			$squid->https_port_array[]=$net;
			}
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');
	$squid->SaveToLdap();
	
}

function main_networking_delport(){
	$squid=new squid($_GET["hostname"]);
	
	if($_GET["port_type"]=='http'){
		unset($squid->http_port_array[$_GET["index"]]);
	}
	if($_GET["port_type"]=='https'){
		unset($squid->https_port_array[$_GET["index"]]);
	}
		
	
	$squid->SaveToLdap();
}

function main_networking(){
	$squid=new squid($_GET["hostname"]);
	$hostname=$_GET["hostname"];
	$sys=new systeminfos();
	$sys->array_tcp_addr[null]='{all}';
	$page=CurrentPageName();
	
	
	$https_ports="<table style='width:100%'>
	<tr><td colspan=3><H5>{ssl_ports}</H5></td></tr>
	";
	
	if(is_array($squid->https_port_array)){
		reset($squid->https_port_array);
		while (list ($num, $val) = each ($squid->https_port_array) ){
		$https_ports=$https_ports."
		<tr>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong>$val</strong></td>
		<td width=1%>" . imgtootltip('x.gif','{delete} '.$val,"squid_http_port_delete('{$_GET["hostname"]}','$num','https')")."</td>
		</tr>";
			
		}
	}else{
		$https_ports=$https_ports."<tr><td><i>{use_http_port}</i></td></tr>";
		
	}
	
	$http_ports="<table style='width:100%'>
	<tr>
		<td colspan=3>
			<H5>{http_ports}</H5>
		</td>
	</tr>	";
	
	if(is_array($squid->http_port_array)){
		reset($squid->http_port_array);
		while (list ($num, $val) = each ($squid->http_port_array) ){
		$http_ports=$http_ports."
		<tr>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong>$val</strong></td>
		<td width=1%>" . imgtootltip('x.gif','{delete} '.$val,"squid_http_port_delete('{$_GET["hostname"]}','$num','http')")."</td>
		</tr>";
			
		}
	}

	$dns_server="<table style='width:100%'>
	<tr>
		<td colspan=3>
			<H5>{dns_nameservers}</H5>
		</td>
	</tr>	";
	
	if(is_array($squid->dns_nameservers_array)){
		reset($squid->dns_nameservers_array);
		while (list ($num, $val) = each ($squid->dns_nameservers_array) ){
		$dns_server=$dns_server."
		<tr>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong>$val</strong></td>
		<td width=1%>" . imgtootltip('x.gif','{delete} '.$val,"squid_dnsserver_delete('{$_GET["hostname"]}','$num')")."</td>
		</tr>";
			
		}
	}else{
		$sock=new sockets();
		$datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?resolv-conf")));
		$tb=explode("\n",$datas);
		while (list ($num, $val) = each ($tb) ){
			if(trim($val)<>null){
			$dns_server=$dns_server."
			<tr>
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td><strong>$val</strong></td>
			<td width=1%>&nbsp;</td>
			</tr>";		
			}
		}
		
	}
	
	
	$form_add_ports="<form name='FFM1'>	
					<input type='hidden' name='hostname' value='{$_GET["hostname"]}'>
				
					<table style='width:100%'>
					<tr>
					<td align='right'><strong>{mode_port}</strong>:</td>
					<td align='left'>" . Field_array_Hash(array("http"=>"http","https"=>"https"),'http_mode',null,null,null,0,'width:150px')."</td>
					</tr>					
					<tr>
					<td align='right'><strong>{tcp_address}</strong>:</td>
					<td align='left'>" . Field_array_Hash($sys->array_tcp_addr,'http_port_ip',null,null,null,0,'width:150px')."</td>
					</tr>
					<td align='right'><strong>{port}</strong>:</td>
					<td align='left'>" . Field_text('http_port_port',null,'width:90px')."</td>
					</tr>
					</tr>
					<td align='right' colspan=2>
						<input type='button' value='{add}&nbsp;&raquo;' OnClick=\"javascript:
							ParseForm('FFM1','$page',true);
							LoadAjax('squid_main_config','squid.index.php?main=yes&tab=networking&hostname={$_GET["hostname"]}');\"
							>
					</td>
					</tr>	
					</table>
					</FORM>";
	
	$form_add_dns="
	<form name='FFMDNS'>	
					<input type='hidden' name='hostname' value='{$_GET["hostname"]}'>
					<table style='width:100%'>
					<tr>
					<td align='right'><strong>{dns_nameservers}</strong>:</td>
					<td align='left'>" . Field_text('dns_nameservers',null,'width:150px',null,null,"{dns_nameservers_text}")."</td>
					</tr>
					<tr>
					<td align='right' colspan=2>
						<input type='button' value='{add}&nbsp;&raquo;' OnClick=\"javascript:
							ParseForm('FFMDNS','$page',true);
							LoadAjax('squid_main_config','squid.index.php?main=yes&tab=networking&hostname={$_GET["hostname"]}');\"
							>
					</td>
					</tr>	
					</table>
					</FORM>";
	
	
	$visible_hostname="
	<table style='width:100%'>
		<tr>
			<td align='right'><strong>{visible_hostname}</strong>:</td>
			<td align='left'>" . Field_text('visible_hostname',$squid->global_conf_array["visible_hostname"],'width:150px')."</td>
			<td width=1%>" . help_icon('{visible_hostname_text}',true)."</td>
		</tr>
		<tr>
		<td align='right' colspan=2>
			<input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:SquidUpdateNetworkConfig('{$_GET["hostname"]}');\">
		</td>
		</tr>
	</table>
	";
	
	$timeouts="
	
	<table style='width:100%'>
		<tr>
			<td align='right'><strong>{dead_peer_timeout}</strong>:</td>
			<td align='left'>" . Field_text('dead_peer_timeout',$squid->global_conf_array["dead_peer_timeout"],'width:100px')."</td>
			<td width=1%>" . help_icon('{dead_peer_timeout_text}',true)."</td>
		</tr>	
		<tr>
			<td align='right'><strong>{dns_timeout}</strong>:</td>
			<td align='left'>" . Field_text('dns_timeout',$squid->global_conf_array["dns_timeout"],'width:100px')."</td>
			<td width=1%>" . help_icon('{dns_timeout_text}',true)."</td>
		</tr>		
		<tr>
			<td align='right'><strong>{connect_timeout}</strong>:</td>
			<td align='left'>" . Field_text('connect_timeout',$squid->global_conf_array["connect_timeout"],'width:100px')."</td>
			<td width=1%>" . help_icon('{connect_timeout_text}',true)."</td>
		</tr>		
		<tr>
			<td align='right'><strong>{peer_connect_timeout}</strong>:</td>
			<td align='left'>" . Field_text('peer_connect_timeout',$squid->global_conf_array["peer_connect_timeout"],'width:100px')."</td>
			<td width=1%>" . help_icon('{peer_connect_timeout_text}',true)."</td>
		</tr>		
			
		<tr>
		<td align='right' colspan=2>
			<input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:SquidUpdateNetworkConfig('{$_GET["hostname"]}');\">
		</td>
		</tr>
	</table>
	";	
	
	$limits="	<table style='width:100%'>
		<tr>
			<td align='right'><strong>{request_body_max_size}</strong>:</td>
			<td align='left'>" . Field_text('request_body_max_size',$squid->global_conf_array["request_body_max_size"],'width:100px')."</td>
			<td width=1%>" . help_icon('{request_body_max_size_text}',true)."</td>
		</tr>	
		<tr>
			<td align='right'><strong>{maximum_object_size}</strong>:</td>
			<td align='left'>" . Field_text('maximum_object_size',$squid->global_conf_array["maximum_object_size"],'width:100px')."</td>
			<td width=1%>" . help_icon('{maximum_object_size_text}',true)."</td>
		</tr>				
		<td align='right' colspan=2>
			<input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:SquidUpdateNetworkConfig('{$_GET["hostname"]}');\">
		</td>
		</tr>
	</table>
	";
	
	
	$visible_hostname=RoundedLightGrey($visible_hostname);
	$timeouts=RoundedLightGrey($timeouts);
	$limits=RoundedLightGrey($limits);
	
	$form_add_ports=RoundedLightGrey($form_add_ports);
	$form_add_dns=RoundedLightGrey($form_add_dns);
	$http_ports=RoundedLightGrey($http_ports . "</table>");
	$https_ports=RoundedLightGrey($https_ports . "</table>");
	$dns_server=RoundedLightGrey($dns_server . "</table>");
	
	
	$add_port="

	<input type='hidden' name='hostname' value='{$_GET["hostname"]}'>
	
			<table style='width:100%'>
			<tr>
			<td width=50% valign='top'>
				<H5>{netport}</H5>
				$form_add_ports	<br>
				$visible_hostname<br>
				<H5>{dns_nameservers}</H5>
				$form_add_dns
				<br>
				<H5>{squid_timeouts}</H5>
				$timeouts
				<br>
				<H5>{limits}</H5>
				$limits
			</td>
			<td width=50% valign='top'>
				$http_ports
				<br>
				$https_ports
				<br>
				$dns_server
			</td>
		</tr>
		</table>";
	$html=
	main_tabs() . "
	<br>
	$add_port
	
	";
	
	$tpl=new templates();
	echo $tpl->_parse_body($html);	
	
}


function main_acls(){
	
$squid=new squid($_GET["hostname"]);
reset($squid->acls_rules_array);
if(is_array($squid->acls_rules_array)){
	$html=$html . "<table style='width:100%'>";
	while (list ($num, $val) = each ($squid->acls_rules_array) ){
		if($val["enabled"]=='yes'){
			$img="icon_mini_read.gif";
			$js="no";
			$tooltip='{disable}';
		}else{
		$img="icon_mini_off.gif";
		$js="yes";
		$tooltip='{enable}';
		}
		
		$jss="SquidAclActiveRule('$num','$js','{$_GET["hostname"]}');";
		$edit="SquidAclAddrule('{$_GET["hostname"]}','$num');";
		
		$html=$html . "<tr>
				<td width=1% valign='top'><img src='img/scripts-16.png'></td>
				<td nowrap " . CellRollOver($edit,'{edit}')."><strong style='font-size:10px'>$num</td>
				<td nowrap " . CellRollOver($edit,'{edit}')."><strong style='font-size:10px'>{$squid->acl_list[$val["acl_type"]]}</td>
				<td nowrap><strong style='font-size:10px'>" . imgtootltip($img,$tooltip,$jss)."</td>
				<td width=1%>" . imgtootltip('x.gif','{delete}',"SquidAclDeleteRule('{$_GET["hostname"]}','$num')") ."</td>
				<tr>";
		
				
	} 
	
	
	$html=$html . "</table>";
}else{ writelogs("squid->acls_rules_array is not an array!",__FUNCTION__,__FILE__);}
	
	
$html=RoundedLightGrey($html);	

$add=Paragraphe('acl-add-64.png','{add_rule}',"","javascript:SquidAclAddrule(\"{$_GET["hostname"]}\")",'add');
$add=RoundedLightGrey($add);

$page=
	main_tabs() . "
	<br>
	<div class=caption>{acls_text}</div>
	
	<table style='width:100%'>
	<tr>
	<td valign='top' width=60%>
	<div id='aclrules'>
		$html
	</div>
	</td>
	<td valign='top' width=40%>
	$add
	<div id='AclDatas'></div>
	</td>
	</tr>
	</table>
	";	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($page);
}


function main_configfile(){
	$squid=new squid($_GET["hostname"]);
	$squid->BuildConfig();
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


function main_acls_add_rule(){
	$hostname=$_GET["hostname"];
	$rulename=$_GET["rulename"];
	$squid=new squid($hostname);
	$squid->acl_list[null]='{select}';
	if(trim($rulename)<>null){
		$field_rule="<input type='hidden' name='acl_name' id='acl_name' value='$rulename'><strong>$rulename</strong>";
		$acl_type=$squid->acls_rules_array[$rulename]["acl_type"];
		$aclform=main_acls_add_rule_type($acl_type);
		$list="<input type='hidden' name='acl_type' id='acl_type' value='$acl_type'><strong>{acl_{$acl_type}}</strong>";
		}
		else{
		$field_rule=Field_text('acl_name',null,'width:200px');
		$list= Field_array_Hash($squid->acl_list,'acl_type',null,"SquidSelectAcl();",null,0,'width:200px') ;
		
		}
	
	$squid->acl_list[null]='{select}';
	$form="
			<input type='hidden' name='hostname' id='hostname' value='$hostname'>
			<table style='width:100%'>
			<tr>
				<tr>
					<td align='right'><strong>{acl_name}:</strong></td>
					<td>$field_rule</td>
				</tr>
					<td align='right'><strong>{select_acl_type}:</strong></td>
					<td>$list</td>
				</tr>
				</table>
				";
	
	$form=RoundedLightGrey($form);
	
	$html=$form . "
		<span id='aclform'>$aclform</span></form>
	";
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function main_acls_add_rule_type($acl_type=null){
	$page=CurrentPageName();
	if(isset($_GET["SquidSelectAcl"])){$acl_type=$_GET["SquidSelectAcl"];}
	
	$html="<br>" .RoundedLightGrey("<table style='width:100%'>
	<tr>
		<td nowrap><strong>{give_value}:</strong></td>
		<td>" . Field_text('acl_value') . "</td>
	</tr>
	<tr>
		<td colspan=2 align='right'><input type='button' OnClick=\"javascript:SquidAddAcl();\" value='&nbsp;{add}&nbsp;&nbsp;&raquo;'></td>
	</tr>
	
	</table>");	
	
	$html=$html ."<br>".RoundedLightGreen("
	
	<strong style='font-size:11px'>{acl_{$acl_type}_title}</strong><br>
	<div>{acl_{$acl_type}_text}</div>");
	
	$tpl=new templates();
	return  $tpl->_ENGINE_parse_body($html);
	
}

function main_acls_load_datas(){
	$hostname=$_GET["hostname"];
	$rulename=$_GET["rulename"];
	$squid=new squid($hostname);
	
	$datas=$squid->acls_rules_array[$rulename]["datas"];
	if(!is_array($datas)){return null;}
	
	$html="<table style='width:100%'>";
	
	while (list ($num, $val) = each ($datas) ){
		$html=$html  . "<tr>
		<td width=1%>
			<img src='img/fw_bold.gif'>
		</td>
		<td><code>$val</code></td>
		<td width=1%>
			" . imgtootltip('x.gif','{delete}',"SquidDeleteAclData('$hostname','$rulename','$num')")."
		</td>
		</tR>";
		
	}
	
	$html=$html . "</table>";
	echo "<br>".RoundedLightGrey($html);
	
}


function main_acl_rue_enable(){
	$acl_rule=$_GET["SquidAclActiveRule"];
	$active=$_GET["active"];
	$hostname=$_GET["hostname"];
	$squid=new squid($hostname);
	$squid->EditAclEnable($acl_rule,$active);
}



function main_acls_add_rule_save(){
	$aclname=$_GET["acl_name"];
	$acl_value=$_GET["acl_value"];
	$hostname=$_GET["hostname"];
	$acl_type=$_GET["acl_type"];
	$squid=new squid($hostname);
	$squid->AddAclType($aclname,$acl_type,$acl_value);
	}
function main_acls_delete_datas(){
	$aclname=$_GET["acl_name"];
	$acl_index=$_GET["acl_delete"];
	$hostname=$_GET["hostname"];
	$squid=new squid($hostname);
	$squid->DelAclType($aclname,$acl_index);
	}
function main_acls_delete_rule(){
	$hostname=$_GET["hostname"];
	$squid=new squid($hostname);
	$squid->DeleteAclRule($_GET["rule_acl_delete"]);
	}
function main_access_rules_switch(){
	$hostname=$_GET["hostname"];
	$squid=new squid($hostname);
	$rule_name=$_GET["AccessSwitch"];
	$index=$_GET["AccessSwitchIndex"];
	$squid->Restrictions_switch($rule_name,$index);
	}
	
function main_update_global_conf(){
	$hostname=$_GET["hostname"];
	$squid=new squid($hostname);	
	while (list ($num, $val) = each ($_GET) ){
		$squid->global_conf_array[$num]=$val;
	}
	$squid->SaveToLdap();
	
}
function main_access_rules_restore(){
	$hostname=$_GET["hostname"];
	$squid=new squid($hostname);
	$dn='cn=access,'.$squid->dn;
	$ldap=new clladp();
	$ldap->ldap_delete($dn,true);
	$squid->BuildAccessRulesDefault();
	}
function main_access_rules_move(){
	$hostname=$_GET["hostname"];
	$squid=new squid($hostname);
	$squid->Restrictions_move($_GET["SquidAccessMove"],$_GET["index"],$_GET["move"]);
	}
function main_access_rules_delete(){
	$hostname=$_GET["hostname"];
	$squid=new squid($hostname);	
	$squid->Restrictions_Delete($_GET["SquidAccessDelete"],$_GET["index"]);
}

function main_access_rules_add_acl(){
	$hostname=$_GET["hostname"];	
	$squid=new squid($hostname);
	if($_GET["isnot"]=='yes'){$_GET["acl"]="!{$_GET["acl"]}";}
	$squid->Restrictions_add_acl($_GET["AddAccessAclRule"],$_GET["index"],$_GET["acl"]);	
	}
	
function main_access_rules_insert_acl(){
	$hostname=$_GET["hostname"];
	if($_GET["isnot"]=='yes'){$_GET["acl"]="!{$_GET["acl"]}";}
	$squid=new squid($hostname);	
	$squid->Restrictions_insert_acl($_GET["InsertIntoAccessAclRule"],$_GET["index"],$_GET["acl"]);		
	}

function main_icap_service_save(){
	$hostname=$_GET["hostname"];
	$squid=new squid($hostname);
	
	if($_GET["icap_service_name"]==null){$_GET["icap_service_name"]=md5("{$_GET["icap_service_vpoint"]} {$_GET["icap_service_bypass"]} icap://{$_GET["icap_service_url"]}");}
	if($_GET["icap_service_vpoint"]==null){$_GET["icap_service_vpoint"]="reqmod_precache";}	
	if($_GET["icap_service_bypass"]==null){$_GET["icap_service_bypass"]="0";}	
	if($_GET["icap_service_url"]==null){$_GET["icap_service_url"]="127.0.0.1/av/reqmod";}	
	
	
	$_GET["icap_service_name"]=str_replace(' ','_',$_GET["icap_service_name"]);
	
	
	
	$array=array(
		"name"=>$_GET["icap_service_name"],
		"vpoint"=>$_GET["icap_service_vpoint"],
		"bypass"=>$_GET["icap_service_bypass"],
		"url"=>$_GET["icap_service_url"]
		);
	$squid->icap_service_array[$_GET["icap_service_name"]]=$array;
	$squid->SaveToLdap();
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');
	}
	
function main_icap_service_delete(){
	$hostname=$_GET["hostname"];
	$squid=new squid($hostname);
	unset($squid->icap_service_array[$_GET["SquidIcapDeleteService"]]);
	$squid->SaveToLdap();
	
}
	
function main_icap_rebuildKaspersky(){
	$hostname=$_GET["hostname"];
	$squid=new squid($hostname);
	unset($squid->icap_service_array["is_kav_resp"]);
	unset($squid->icap_service_array["is_kav_req"]);
	
	unset($squid->icap_class_array["ic_kav_resp"]);
	unset($squid->icap_class_array["ic_kav_req"]);
	
	$squid->icap_class_array["ic_kav_resp"]="is_kav_resp";
	
	
	
	$squid->icap_service_array["is_kav_resp"]=array(
		"name"=>"is_kav_resp",
		"vpoint"=>"respmod_precache",
		"bypass"=>0,
		"url"=>"127.0.0.1:1344/av/respmod"
		);
	$squid->icap_add_restrictions_rule("ic_kav_resp",'allow','all','no');	
	$squid->SaveToLdap();
	
}

function main_cache(){
	$hostname=$_GET["hostname"];
	if(isset($_GET["subsection"])){
		switch ($_GET["subsection"]) {
			case "add-cache":echo main_cache_form_addcache();exit;break;
		}
	}
	include_once('ressources/charts.php');
	$squid=new squid($_GET["hostname"]);
	$usermenus=new usersMenus();
	
	include_once('ressources/charts.php');
   if(is_array($squid->cache_dir_array)){
   	reset($squid->cache_dir_array);
   	$stats="<table style=width:100%>";
	while (list ($num, $val) = each ($squid->cache_dir_array)){
		$uri=$uri . "<li>listener.graphs.php?graphfromdir={$val["dir"]}&max={$val["sizemb"]}&hostname={$_GET["hostname"]}</li>";
			$stats=$stats ."<tr>
			<td style='border:1px solid #CCCCCC;padding:3px;'>
			<center><strong style='font-size:12px'>{$val["dir"]}</strong></center><br>
			" .InsertChart('js/charts.swf',"js/charts_library","listener.graphs.php?graphfromdir={$val["dir"]}&max={$val["sizemb"]}&hostname={$_GET["hostname"]}",
			250,250,"FFFFFF",true,$usermenus->ChartLicence) ."</td>
			</tr>";
			
		}
	$stats=$stats . "</table>";}
	
	
	
	
	
	
	
	$add=Paragraphe('hard-drive-add-64.png','{add_cache_dir}','{add_cache_dir_text}',"javascript:SquidCacheAdd(\"$hostname\")","add");
	
	
	
	$add=RoundedLightGrey($add);
	
	
$cache_settings="<table style='width:100%'>
		<tr>
		<td align='right'><strong>{cache_mem}</strong></td>
		<td>" . Field_text('cache_mem',$squid->global_conf_array["cache_mem"],'width:70px')."</td>
		<td>" . help_icon('{cache_mem_text}')."</td>
		</tr>
<tr>
		<td align='right'><strong>{cache_swap_low}</strong></td>
		<td>" . Field_text('cache_swap_low',$squid->global_conf_array["cache_swap_low"],'width:70px')."%</td>
		<td>" . help_icon('{cache_swap_low_text}')."</td>
		</tr>
<tr>
		<td align='right'><strong>{cache_swap_high}</strong></td>
		<td>" . Field_text('cache_swap_high',$squid->global_conf_array["cache_swap_high"],'width:70px')."%</td>
		<td>" . help_icon('{cache_swap_high_text}')."</td>
		</tr>
<tr>
		<td align='right'><strong>{maximum_object_size}</strong></td>
		<td>" . Field_text('maximum_object_size',$squid->global_conf_array["maximum_object_size"],'width:70px')."</td>
		<td>" . help_icon('{maximum_object_size_text}')."</td>
		</tr>
<tr>
		<td align='right'><strong>{minimum_object_size}</strong></td>
		<td>" . Field_text('minimum_object_size',$squid->global_conf_array["minimum_object_size"],'width:70px')."</td>
		<td>" . help_icon('{minimum_object_size_text}')."</td>
		</tr>
<tr>
		<td align='right'><strong>{maximum_object_size_in_memory}</strong></td>
		<td>" . Field_text('maximum_object_size_in_memory',$squid->global_conf_array["maximum_object_size_in_memory"],'width:70px')."</td>
		<td>" . help_icon('{maximum_object_size_in_memory_text}')."</td>
		</tr>			
<tr>
		<td align='right'><strong>{ipcache_size}</strong></td>
		<td>" . Field_text('ipcache_size',$squid->global_conf_array["ipcache_size"],'width:70px')."</td>
		<td>" . help_icon('{ipcache_size_text}')."</td>
		</tr>	
<tr>
		<td align='right'><strong>{ipcache_low}</strong></td>
		<td>" . Field_text('ipcache_low',$squid->global_conf_array["ipcache_low"],'width:70px')."%</td>
		<td>" . help_icon('{ipcache_low_text}')."</td>
		</tr>
<tr>
		<td align='right'><strong>{ipcache_high}</strong></td>
		<td>" . Field_text('ipcache_high',$squid->global_conf_array["ipcache_high"],'width:70px')."%</td>
		<td>" . help_icon('{ipcache_high_text}')."</td>
		</tr>
<tr>
		<td align='right'><strong>{fqdncache_size}</strong></td>
		<td>" . Field_text('fqdncache_size',$squid->global_conf_array["fqdncache_size"],'width:70px')."</td>
		<td>" . help_icon('{fqdncache_size_text}')."</td>
		</tr>
<tr>
		<td align='right' colspan=3><input type='button' value='{edit}&nbsp;' OnClick=\"javascript:SquidUpdateNetworkConfig('{$_GET["hostname"]}');\"></td>
		</tr>				
									
		
			
		
		</table>"	;
	
	
	$cache_settings=RoundedLightGrey($cache_settings);
	$html=main_tabs()."<br>
	<table style='width:100%'>
	<tr>
	<td valign='top' width=50%'>
		<H5>{cache_dir}</H5>
		<div class=caption>{cache_dir_text}</div>
		<div id='left-cache'>" . main_cache_table() . "</div>
		<br>
		<H5>{cache_settings}</H5>
		$cache_settings
		
	</td>
	<td valign='top' width=50%'>
		<div id='right-cache'>$add<br>$stats</div>
		
	</td>
	</table>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	}
function main_cache_table(){
	$hostname=$_GET["hostname"];
	$squid=new squid($hostname);
	$array=$squid->cache_dir_array;
	if(!is_array($array)){return null;}
	
	$html="<table style='width:100%'>
	<tr style='background-color:#CCCCCC;font-weight:bolder'>
	<td width=1%>&nbsp;</td>
	<td>{path}</td>
	<td width=1%>{type}</td>
	<td width=1%>{size}</td>
	<td width=1%>&nbsp;</td>
	</tr>
	";
	
	$rol=CellRollOver();
	
	while (list ($num, $val) = each ($array) ){
		$html=$html . "<tr $rol>
		<td width=1%><img src='img/database-16.png'></td>
		<td><strong><a href='javascript:void();' OnClick=\"javascript:SquidCacheAdd('$hostname','{$val["dir"]}')\">{$val["dir"]}</strong></td>
		<td nowrap><strong>{squid_{$val["type"]}}</strong></td>
		<td nowrap width=1%><strong>{$val["sizemb"]} mb</strong></td>
		<td width=1%>" . imgtootltip('x.gif','{delete} cache N' . $num,"SquidCacheDelete('$hostname','$num')") . "</td>
		</tr>
		
		
		
		";
		
	}
	$html=$html . "</table>";
	$tpl=new templates();
	return  RoundedLightGrey($tpl->_ENGINE_parse_body($html));
}

function main_cache_form_addcache(){
	$hostname=$_GET["hostname"];
	$squid=new squid($hostname);
	
	if(is_array($squid->cache_dir_array)){
		reset($squid->cache_dir_array);
		while (list ($num, $val) = each ($squid->cache_dir_array)){
			if($_GET["path"]==$val["dir"]){$array=$val;}
		}
	reset($squid->cache_dir_array);}
	
	print_r($squid->cache_dir_array[$num]);
	$tpl=new templates();
	$squid->cache_type_list[null]='{select}';
	$type=$tpl->_ENGINE_parse_body(Field_array_Hash($squid->cache_type_list,'cache_type',$array["type"]));
	
	$html="
	<table style='width:100%'>
		<tr>
			<td align='right' nowrap><strong>{path} $index</strong></td>
			<td align='left'>" . Field_text('cache_dir',$array["dir"]) . "</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td align='right' nowrap><strong>{size} (mb)</strong></td>
			<td align='left'>" . Field_text('cache_size',$array["sizemb"]) . " (mb)</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td align='right' nowrap><strong>{cache_type}</strong></td>
			<td align='left'>$type</td>
			<td>&nbsp;</td>
		</tr>	
		<tr>
			<td align='right' nowrap><strong>{cache_dir_level1}</strong></td>
			<td align='left'>" . Field_text('cache_dir_level1',$array["level1"],'width:50px') . "</td>
			<td>" . help_icon('{cache_dir_level1_text}')."</td>
		</tr>
		<tr>
			<td align='right' nowrap><strong>{cache_dir_level2}</strong></td>
			<td align='left'>" . Field_text('cache_dir_level2',$array["level2"],'width:50px') . "</td>
			<td>" . help_icon('{cache_dir_level2_text}')."</td>
		</tr>									
		<tr>
		<td colspan=2 align='right'><input type='button' OnClick=\"javascript:SquidCacheAddAjax('{$_GET["hostname"]}')\" value='{save}&nbsp;&raquo;'>
		</tr>
	
	</table>
	
	
	";
	
	$html=$tpl->_ENGINE_parse_body($html);
	return RoundedLightGrey($html);
	
}

function main_cache_add(){
	$hostname=$_GET["hostname"];
	$squid=new squid($hostname);	
	if($_GET["cache_size"]==null){$_GET["cache_size"]="500";}
	$squid->AddCache($_GET["cache_dir"],$_GET["cache_size"],$_GET["cache_type"],$_GET["cache_dir_level1"],$_GET["cache_dir_level2"]);
	
}
function main_cache_delete(){
	$hostname=$_GET["hostname"];
	$squid=new squid($hostname);	
	$squid->DeleteCacheDir($_GET["SquidCacheDelete"]);
}

function main_graph(){
	$tabs=main_status_tab();
	$md=md5(date('Ymhis'));
	$html="<img src='images.listener.php?uri=squid/rrd/connections.hour.png&hostname={$_GET["hostname"]}&md=$md'>";
	$html=$tabs.RoundedLightGrey($html);
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}
function main_apply_conf(){
	$hostname=$_GET["hostname"];
	$squid=new squid($hostname);	
	$squid->BuildConfig();
	$squid->SaveToLdap();
	$squid->SaveToServer();
	
}

function main_status_analyse_logs(){
	if(!file_exists('ressources/logs/squid.start')){return ;}
	$datas=explode("\n",file_get_contents('ressources/logs/squid.start'));
	$error=null;
	while (list ($num, $ligne) = each ($datas) ){
		if(preg_match('#unrecognized:(.+)#',$ligne,$re)){
			$error="{config_error}";
			$error_text=$re[1];
			$img="status_bad_config.png";
			break;
		}
	}
	
	if($error<>null){
		return "<tr ". CellRollOver('SquidViewStartError()','{view}').">
		<td align='right' valign='top'><img src='img/$img'></td>
		<td valign='top'><strong style='color:red'>$error:</strong><br><span style='color:red'>$error_text</span></td>
		</tr>";
		
	}
	
}

function main_status_analyse_viewstarterrors(){
	
	if(!file_exists('ressources/logs/squid.start')){return ;}
	$datas=explode("\n",file_get_contents('ressources/logs/squid.start'));
while (list ($num, $ligne) = each ($datas) ){
		if($ligne<>null){
			$html=$html . "<div style='padding:2px;border:1px solid white'><code style='font-size:10px'>$ligne</code></div>";
		}
	}
		
	echo $html;
	
}


function page_index_status(){
	$tabs=main_tabs();
	$squid=new squid($_GET["hostname"]);
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString(base64_decode($sock->getFrameWork('cmd.php?squid-ini-status=yes')));
	$tpl=new templates();
//echo "<H1>ouou</H1>";
	$squid_status=DAEMON_STATUS_ROUND("SQUID",$ini,null,1);
	$dansguardian_status=DAEMON_STATUS_LINE("DANSGUARDIAN",$ini,null,1);
	$kav=DAEMON_STATUS_LINE("KAV4PROXY",$ini,null,1);
	$cicap=DAEMON_STATUS_LINE("C-ICAP",$ini,null,1);
	$proxy_pac=DAEMON_STATUS_LINE("APP_PROXY_PAC",$ini,null,1);
	$md=md5(date('Ymhis'));
	
	$squid=new squidbee();

	if(count($squid->network_array)==0){
		$net=Paragraphe("warning64.png","{no_squid_network}","{no_squid_network_text}","javascript:Loadjs('squid.popups.php?script=network')",null,350);
		echo $tpl->_ENGINE_parse_body($net);
		return;
	}	
	
	
	if($dansguardian_status<>null){$dansguardian_status="<tr><td valign='top'>$dansguardian_status</td></tr>";}
	if($kav<>null){$kav="<tr><td valign='top'>$kav</td></tr>";}
	if($cicap<>null){$cicap="<tr><td valign='top'>$cicap</td></tr>";}
	if($proxy_pac<>null){$proxy_pac="<tr><td valign='top'>$proxy_pac</td></tr>";}
	

	$html="
	<table style='width=99%'>'
	<tr>
	<td valign='top'><img src='img/crion-128.png'></td>
	<td valign='top'>
	<table style='width:99%'>
		<tr><td valign='top'>$squid_status</td></tr>
		$kav
		$cicap
		$dansguardian_status
		$proxy_pac
	</table>
	</td>
	</tr>
	</table>	
	";
	
	
	echo $tpl->_parse_body($html);	
	
}

function squid_events_hours(){
	
	
	
}


?>
	
	
	
	
