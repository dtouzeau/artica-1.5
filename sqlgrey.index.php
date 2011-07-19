<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.sqlgrey.inc');
	
	$user=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}
	
	if($user->AsPostfixAdministrator==false){header('location:users.index.php');exit();}
	if(isset($_GET["SaveGeneralSettings"])){SaveGeneralSettings();exit;}
	if(isset($_GET["main"])){main_switch();exit;}
	if(isset($_GET["AddFqdnWL"])){main_wl_fqdn_add();exit;}
	if(isset($_GET["DelFqdnWL"])){main_wl_fqdn_del();exit;}
	if(isset($_GET["AddIPWL"])){main_wl_ip_add();exit;}
	if(isset($_GET["DelIPWL"])){main_wl_ip_del();exit;}
	
		main_page();
	
function main_page(){
	

	if($_GET["hostname"]==null){
		$user=new usersMenus();
		$_GET["hostname"]=$user->hostname;}
	
	$html=
	"<table style='width:100%'>
	<tr>
	<td width=1% valign='top'><img src='img/bg_sqlgrey.jpg'>	<p class=caption>{about}</p></td>
	<td>
	" . applysettings("sqlgrey") . "<br>" . main_status() . "</td>
	</tr>
	<tr>
		<td colspan=2 valign='top'><br>
			<div id='main_config'></div>
		</td>
	</tr>
	</table>
	<script>LoadAjax('main_config','$page?main=yes');</script>
	
	";
	
	
	$cfg["JS"][]='js/sqlgrey.js';
	
	
	
	
	$tpl=new template_users('{APP_SQLGREY}',$html,0,0,0,0,$cfg);
	
	echo $tpl->web_page;
	
	
	
}	

function main_tabs(){
	if(!isset($_GET["main"])){$_GET["main"]="yes";};
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}
	$page=CurrentPageName();
	$array["yes"]='{main_settings}';
	$array["logs"]='{events}';
	$array["fqdn"]='{fqdn_white_list}';
	$array["ipwl"]='{ip_white_list}';
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["main"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('main_config','$page?main=$num&hostname=$hostname')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";		
}


function main_switch(){
	
	switch ($_GET["main"]) {
		case "yes":main_config();exit;break;
		case "logs":main_logs();exit;break;
		case "fqdn":main_wl_fqdn();exit;break;
		case "ipwl":main_wl_ip();exit;break;
		case "ipwl_list":echo main_wl_ip_list();exit;break;
		case "fqdn_list";echo main_wl_fqdn_list();exit;
		default:
			break;
	}
	
	
}


function main_wl_fqdn(){
$users=new usersMenus();	
if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}

$form="<table style='width:100%'>
	
<tr>
	<td $style align='right' nowrap valign='top'><strong>{add_white_list}:</strong></td>
	<td $style valign='top'><input type='text' id='whl_server' style='width:100%'></td>
	<td $style valign='top' width=1%><input type='button' value='{add}&nbsp;&raquo;' OnClick=\"javascript:AddFqdnWL('$hostname');\" style='margin:0px'></td>
	</tr>	
</table>";

$form=RoundedLightGreen($form);


$list=main_wl_fqdn_list();

$html=main_tabs()."<br>

	<h5>{fqdn_white_list}</H5>	
	<p class=caption>{fqdn_white_list_text}</p>$form<br><div id='list'>$list</div>";


$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function main_wl_fqdn_list(){
$users=new usersMenus();	
if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}	
$pure=new sqlgrey($hostname);	

if(is_array($pure->SqlGreyWLHost)){
	$html="<table style='width:100%'>";
	while (list ($num, $val) = each ($pure->SqlGreyWLHost) ){
		$html=$html . "<tr>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong><code>$val</code></strong></td>
		<td width=1%>" . imgtootltip('x.gif',"{delete}","DelFqdnWL('$hostname','$num')") . "</td>
		</tr>";}
	$html=$html . "</table>";
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body(RoundedLightGrey($html));
	}
}

function main_wl_ip(){
$users=new usersMenus();	
if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}

$form="<table style='width:100%'>
	
<tr>
	<td $style align='right' nowrap valign='top'><strong>{add_white_list}:</strong></td>
	<td $style valign='top'><input type='text' id='whl_server' style='width:100%'></td>
	<td $style valign='top' width=1%><input type='button' value='{add}&nbsp;&raquo;' OnClick=\"javascript:AddIPWL('$hostname');\" style='margin:0px'></td>
	</tr>	
</table>";

$form=RoundedLightGreen($form);


$list=main_wl_ip_list();

$html=main_tabs()."<br>

	<h5>{ip_white_list}</H5>	
	<p class=caption>{ip_white_list_text}</p>$form<br><div id='list'>$list</div>";


$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function main_wl_ip_list(){
$users=new usersMenus();	
if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}	
$pure=new sqlgrey($hostname);	

if(is_array($pure->SqlGreyWLIP)){
	$html="<table style='width:100%'>";
	while (list ($num, $val) = each ($pure->SqlGreyWLIP) ){
		$html=$html . "<tr>
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td><strong><code>$val</code></strong></td>
		<td width=1%>" . imgtootltip('x.gif',"{delete}","DelIPWL('$hostname','$num')") . "</td>
		</tr>";}
	$html=$html . "</table>";
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body(RoundedLightGrey($html));
	}
}

function main_config(){
	$style="style='padding:3px;border-bottom:1px dotted #CCCCCC'";
	$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}
	$pure=new sqlgrey($hostname);
	$page=CurrentPageName();
	
	$greymethod=Field_array_Hash(array("smart"=>"{smart}","full"=>"{full}","classc"=>"{classc}"),"greymethod",$pure->main_array["greymethod"],null);
	
	$optmethod=Field_array_Hash(array("none"=>"none","optin"=>"optin","optout"=>"optout"),"optmethod",$pure->main_array["optmethod"]);
	
	$reject_first_attempt=Field_array_Hash(array("immed"=>"immed","delay"=>"delay"),"reject_first_attempt",$pure->main_array["reject_first_attempt"]);
	$reject_early_reconnect=Field_array_Hash(array("immed"=>"immed","delay"=>"delay"),"reject_early_reconnect",$pure->main_array["reject_early_reconnect"]);
	
	
	$html=main_tabs()."<br>

	<h5>{main_settings}</H5>

	<form name='FFM_DANS2'>
	<input type='hidden' name='hostname' value='$hostname'>
	<input type='hidden' name='SaveGeneralSettings' value='yes'>
	" . RoundedLightGreen("
	<table style='width:100%'>
	
<tr>
	<td $style align='right' nowrap valign='top'><strong>{enable_sqlgrey}:</strong></td>
	<td $style valign='top'>" . Field_numeric_checkbox_img('enable_sqlgrey',$pure->SqlGreyEnabled)."</td>
	<td $style valign='top'>{enable_sqlgrey_text}</td>
	</tr>		
	
	<tr>
	<td $style align='right' nowrap valign='top'><strong>{reconnect_delay}:</strong></td>
	<td $style valign='top' colspan=2>" . Field_text('reconnect_delay',$pure->main_array["reconnect_delay"],'width:50px',null,null,'{reconnect_delay_text}')."</td>
	</tr>

	<tr>
	<td $style align='right' nowrap valign='top'><strong>{max_connect_age}:</strong></td>
	<td $style valign='top' colspan=2>" . Field_text('max_connect_age',$pure->main_array["max_connect_age"],'width:50px',null,null,'{max_connect_age_text}')."</td>
	</tr>
	
	<tr>
	<td $style align='right' nowrap valign='top'><strong>{connect_src_throttle}:</strong></td>
	<td $style valign='top' colspan=2>" . Field_text('connect_src_throttle',$pure->main_array["connect_src_throttle"],'width:50px',null,null,'{connect_src_throttle_text}')."</td>
	</tr>	

	<tr>
	<td $style align='right' nowrap valign='top'><strong>{awl_age}:</strong></td>
	<td $style valign='top' colspan=2>" . Field_text('awl_age',$pure->main_array["awl_age"],'width:50px',null,null,'{awl_age_text}')."</td>
	</tr>		
	
	<tr>
	<td $style align='right' nowrap valign='top'><strong>{group_domain_level}:</strong></td>
	<td $style valign='top' colspan=2>" . Field_text('group_domain_level',$pure->main_array["group_domain_level"],'width:50px',null,null,'{group_domain_level_text}')."</td>
	</tr>		
	<tr>
	<td $style align='right' nowrap valign='top'><strong>{greymethod}:</strong></td>
	<td $style valign='top'>$greymethod</td>
	<td $style valign='top'>" . help_icon("{greymethod_text}")."</td>
	</tr>
	
	<tr>
	<td $style align='right' nowrap valign='top'><strong>{optmethod}:</strong></td>
	<td $style valign='top'>$optmethod</td>
	<td $style valign='top'>" . help_icon("{optmethod_text}")."</td>
	</tr>		

	<tr>
	<td $style align='right' nowrap valign='top'><strong>{discrimination}:</strong></td>
	<td $style valign='top'>" . Field_onoff_checkbox_img('discrimination',$pure->main_array["discrimination"])."</td>
	<td $style valign='top'>" . help_icon("{discrimination_text}")."</td>
	</tr>	
	
	<tr>
	<td $style align='right' nowrap valign='top'><strong>{discrimination_add_rulenr}:</strong></td>
	<td $style valign='top'>" . Field_onoff_checkbox_img('discrimination_add_rulenr',$pure->main_array["discrimination_add_rulenr"])."</td>
	<td $style valign='top'>" . help_icon("{discrimination_add_rulenr_text}")."</td>
	</tr>	


	<tr>
	<td $style align='right' nowrap valign='top'><strong>{reject_first_attempt}:</strong></td>
	<td $style valign='top'>$reject_first_attempt</td>
	<td $style valign='top'>" . help_icon("{reject_first_attempt_text}")."</td>
	</tr>
	<tr>
	<td $style align='right' nowrap valign='top'><strong>{reject_early_reconnect}:</strong></td>
	<td $style valign='top'>$reject_early_reconnect</td>
	<td $style valign='top'>" . help_icon("{reject_early_reconnect_text}")."</td>
	</tr>	
	
	<tr>
	<td $style align='right' nowrap valign='top'><strong>{whitelists_host}:</strong></td>
	<td $style valign='top' colspan=2>" . Field_text('whitelists_host',$pure->main_array["whitelists_host"],'width:250px',null,null,'{whitelists_host_text}')."</td>
	</tr>
	
	<tr>
	<td $style align='right' nowrap valign='top'><strong>{admin_mail}:</strong></td>
	<td $style valign='top' colspan=2>" . Field_text('admin_mail',$pure->main_array["admin_mail"],'width:250px',null,null,'{admin_mail_text}')."</td>
	</tr>

	
	<tr>
	<tr><td colspan=3 style='border-top:1px solid #005447'>&nbsp;</td></tr>
	<tr>
	<td $style colspan=3 align='right' valign='top'><input type='button' value='{save}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('FFM_DANS2','$page',true);LoadAjax('main_config','$page?main=yes');\"></td>
	</tr>

	</table>") . "</FORM><br>$table";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function SaveGeneralSettings(){
	$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}	
	$pure=new sqlgrey($hostname);
	$pure->SqlGreyEnabled=$_GET["enable_sqlgrey"];
	while (list ($num, $val) = each ($_GET) ){
		$pure->main_array[$num]=$val;
		
	}
	
	if($pure->SaveToLdap()){
		$tpl=new templates();
		echo trim($tpl->_ENGINE_parse_body('{success}'));
	
	}
	
	
}

function main_logs(){
	$page=CurrentPageName();
	$tpl=new templates();
	$html=main_tabs() . "
	<H5>{events}</H5>
	<iframe src='sqlgrey.events.php' style='width:100%;height:500px;border:0px'></iframe>";
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function main_wl_fqdn_add(){
	$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}		
	$sql=new sqlgrey($hostname);
	$sql->Add_whitelist_fqdn($_GET["AddFqdnWL"]);
	}
	
function main_wl_ip_add(){
	$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}		
	$sql=new sqlgrey($hostname);
	$sql->Add_whitelist_ip($_GET["AddIPWL"]);	
}
	
function main_wl_fqdn_del(){
	$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}		
	$sql=new sqlgrey($hostname);
	$sql->Del_whitelist_fqdn($_GET["DelFqdnWL"]);	
	}
	
function main_wl_ip_del(){
	$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}		
	$sql=new sqlgrey($hostname);
	$sql->Del_whitelist_ip($_GET["DelIPWL"]);	
	}	
	
	


function main_status(){

$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}		
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString($sock->getfile('pureftd_status',$_GET["hostname"]));	
	if($ini->_params["SQLGREY"]["running"]==0){
		$img="okdanger32.png";
		$rouage='rouage_on.png';
		$rouage_title='{start_service}';
		$rouage_text='{start_service_text}';
		$error= "";
		$js="SQlgreyActionService(\"{$_GET["hostname"]}\",\"start\")";
		$status="{stopped}";
	}else{
		$img="ok32.png";
		$status="running";
		$rouage='rouage_off.png';
		$rouage_title='{stop_service}';
		$rouage_text='{stop_service_text}';		
		$js="SQlgreyActionService(\"{$_GET["hostname"]}\",\"stop\")";
	}
	
	$status="<table style='width:100%'>
	<tr>
	<td valign='top'>
		<img src='img/$img'>
	</td>
	<td valign='top'>
		<table style='width:100%'>
		<tr>
		<td align='right' nowrap><strong>{APP_SQLGREY}:</strong></td>
		<td><strong>$status</strong></td>
		</tr>		
		<tr>
		<td align='right'><strong>{pid}:</strong></td>
		<td><strong>{$ini->_params["SQLGREY"]["master_pid"]}</strong></td>
		</tr>
		<tr>
		<td align='right'><strong>{memory}:</strong></td>
		<td><strong>{$ini->_params["SQLGREY"]["master_memory"]}&nbsp; mb</strong></td>
		</tr>
		<tr>
		<td align='right'><strong>{version}:</strong></td>
		<td><strong>{$ini->_params["SQLGREY"]["master_version"]}</strong></td>
		</tr>	
		<tr>
		<td align='right' nowrap><strong>white list {last_update}:</strong></td>
		<td><strong>{$ini->_params["SQLGREY"]["fqdn_wl_date"]}</strong></td>
		</tr>			
		<tr><td colspan=2>$error</td></tr>	
		<tr><td colspan=2>&nbsp;</td></tr>	
		
		</table>		
	</td>
	</tr>
	</table>";
	
	$status=RoundedLightGreen($status);
	$status_serv=RoundedLightGrey(Paragraphe($rouage ,$rouage_title. " (squid)",$rouage_text,"javascript:$js"));
	return $status;
	
}
	
	
?>	