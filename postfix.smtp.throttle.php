<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.maincf.multi.inc');
	
	
$users=new usersMenus();
if(!PostFixVerifyRights()){
	$tpl=new templates();
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	echo "alert('$ERROR_NO_PRIVS');";
	die();
	
}


	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["smtp"])){smtp();exit;}
	if(isset($_GET["smtp-instance-list"])){smtp_instance_list();exit;}
	if(isset($_GET["smtp-instance-add"])){smtp_instance_add();exit;}
	if(isset($_GET["smtp-instance-delete"])){smtp_instance_delete();exit;}
	if(isset($_GET["smtp-instance-edit"])){smtp_instance_edit();exit;}
	if(isset($_GET["smtp-instance-save"])){smtp_instance_save();exit;}
	
	if(isset($_GET["domains"])){domains_popup();exit;}
	if(isset($_GET["domains-add"])){domains_add();exit;}
	if(isset($_GET["domains-list"])){domains_list();exit;}
	if(isset($_GET["domain-delete"])){domains_delete();exit;}

	js();



function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title="{domain_throttle}::{$_GET["hostname"]}/{$_GET["ou"]}";
	$title=$tpl->_ENGINE_parse_body($title);
	echo "YahooWin3(660,'$page?popup=yes&hostname={$_GET["hostname"]}&ou={$_GET["ou"]}','$title');";
	}
	
	
function domains_add(){
	$page=CurrentPageName();
	$tpl=new templates();
	$main=new maincf_multi($_GET["hostname"],$_GET["ou"]);	
	$array=unserialize(base64_decode($main->GET_BIGDATA("domain_throttle_daemons_list")));	
	$uuid=$_GET["smtp-daemon-uuid"];
	$array[$uuid]["DOMAINS"][$_GET["domains-add"]]=true;
	if(!$main->SET_BIGDATA("domain_throttle_daemons_list",base64_encode(serialize($array)))){writelogs("{$_GET["hostname"]}/{$_GET["ou"]}: error...");echo "ERROR";return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-throttle=yes&instance={$_GET["hostname"]}");	
}

function domains_delete(){
	$page=CurrentPageName();
	$tpl=new templates();
	$main=new maincf_multi($_GET["hostname"],$_GET["ou"]);	
	$array=unserialize(base64_decode($main->GET_BIGDATA("domain_throttle_daemons_list")));	
	$uuid=$_GET["smtp-daemon-uuid"];
	unset($array[$uuid]["DOMAINS"][$_GET["domain-delete"]]);
	if(!$main->SET_BIGDATA("domain_throttle_daemons_list",base64_encode(serialize($array)))){writelogs("{$_GET["hostname"]}/{$_GET["ou"]}: error...");echo "ERROR";return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-throttle=yes&instance={$_GET["hostname"]}");		
}
	
	
function domains_popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$main=new maincf_multi($_GET["hostname"],$_GET["ou"]);	
	$array=unserialize(base64_decode($main->GET_BIGDATA("domain_throttle_daemons_list")));
	if(is_array($array)){
		while (list ($uuid, $array_conf) = each ($array) ){
			$instances_list[$uuid]=$array_conf["INSTANCE_NAME"];
			
		}
		
	}
	
	$field_instances=Field_array_Hash($instances_list,"smtp-daemon-uuid",null,"style:font-size:13px;padding:3px");
	
	
	$html="
	<div class=explain>{domain_throttle_domain_explain}</div>
	<center><table class=form>
	<tr>
		<td class=legend>{smtp_daemon_name}:</td>
		<td>$field_instances</td>
		<td class=legend>{domain}:</td>
		<td>". Field_text("smtp_domainadd",null,"font-size:14px;padding:3px;width:120px","script:smtp_domainaddfunc_check(event)")."</td>
		<td>". button("{add}","smtp_domainaddfunc()")."</td>
	</tr>
	</table>
	</center>
	<div id='domain_throttle_domains_list' style='width:100%;height:220px;overflow:auto'></div>
	
	<script>
		var x_smtp_domainaddfunc= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			RefreshSMTPDomainList();
			
		}	
		
		function smtp_domainaddfunc_check(e){
			if(checkEnter(e)){smtp_domainaddfunc();}
		}
		
		function smtp_domainaddfunc(){
			var XHR = new XHRConnection();
			XHR.appendData('ou','{$_GET["ou"]}');
			XHR.appendData('hostname','{$_GET["hostname"]}');
			XHR.appendData('domains-add',document.getElementById('smtp_domainadd').value);
			XHR.appendData('smtp-daemon-uuid',document.getElementById('smtp-daemon-uuid').value);
			document.getElementById('domain_throttle_daemon_list').innerHTML=\"<center style='margin:10px'><img src='img/wait_verybig.gif'></center>\";
			XHR.sendAndLoad(\"$page\", 'GET',x_smtp_domainaddfunc);
		}
		
		function RefreshSMTPDomainList(){
			LoadAjax('domain_throttle_domains_list','$page?domains-list=yes&hostname={$_GET["hostname"]}&ou={$_GET["ou"]}');
		
		}
		
		RefreshSMTPDomainList();
	</script>	
	
	";
	echo $tpl->_ENGINE_parse_body($html);			
	
}
	
	
function smtp(){
	$page=CurrentPageName();
	$tpl=new templates();	
	
	$html="
	<div class=explain>{domain_throttle_explain}</div>
	<center><table class=form>
	<tr>
		<td class=legend>{smtp_daemon_name}:</td>
		<td>". Field_text("smtp_daemon_name",null,"font-size:14px;padding:3px","script:smtp_daemon_add_check(event)")."</td>
		<td>". button("{add}","smtp_daemon_add()")."</td>
	</tr>
	</table>
	</center>
	<div id='domain_throttle_daemon_list' style='width:100%;height:220px;overflow:auto'></div>
	
	
	
	<script>
		var x_smtp_daemon_add= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			RefreshDaemonList();
			
		}	
		
		function smtp_daemon_add_check(e){
			if(checkEnter(e)){smtp_daemon_add();}
		}
		
		function smtp_daemon_add(){
			var XHR = new XHRConnection();
			XHR.appendData('ou','{$_GET["ou"]}');
			XHR.appendData('hostname','{$_GET["hostname"]}');
			XHR.appendData('smtp-instance-add',document.getElementById('smtp_daemon_name').value);
			document.getElementById('domain_throttle_daemon_list').innerHTML=\"<center style='margin:10px'><img src='img/wait_verybig.gif'></center>\";
			XHR.sendAndLoad(\"$page\", 'GET',x_smtp_daemon_add);
		}
		
		function RefreshDaemonList(){
			LoadAjax('domain_throttle_daemon_list','$page?smtp-instance-list=yes&hostname={$_GET["hostname"]}&ou={$_GET["ou"]}');
		
		}
		
		RefreshDaemonList();
	</script>	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function smtp_instance_list(){
	$page=CurrentPageName();
	$tpl=new templates();		
	$main=new maincf_multi($_GET["hostname"],$_GET["ou"]);	
	$array=unserialize(base64_decode($main->GET_BIGDATA("domain_throttle_daemons_list")));
	
		$html="
		<hr>
		
		<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th>&nbsp;</th>
	<th>{daemon}</th>
	<th nowrap>{destination_limit}</th>
	<th nowrap>{rate_delay}</th>
	<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";		
		
	if(is_array($array)){
		while (list ($uuid, $array_conf) = each ($array) ){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$color="#909090";
		if($array_conf["ENABLED"]==1){$color="black";}
		$js="<a href=\"javascript:blur();\" style='font-size:14px;text-decoration:underline;color:$color' OnClick=\"javascript:YahooWin4(650,'$page?smtp-instance-edit=$uuid&hostname={$_GET["hostname"]}&ou={$_GET["ou"]}','{$array_conf["INSTANCE_NAME"]}')\">";
		
		
		
		
			$html=$html."<tr class=$classtr>
						<td width=1%><img src='img/plane-32.png'></td>
						<td><strong style='font-size:16px'>$js{$array_conf["INSTANCE_NAME"]}</a></strong></td>
						<td width=1%  align='center'><strong style='font-size:16px;color:$color'>{$array_conf["transport_destination_concurrency_limit"]}</strong></td>
						<td width=1%  align='center'><strong style='font-size:16px;color:$color'>{$array_conf["transport_destination_rate_delay"]}</strong></td>
						<td width=1% align='center'>".imgtootltip("delete-24.png",'{delete}',"DeleteSMTPSenderInstance('$uuid')")."</td>
					</tr>";					
		
		
			
		}
	}	

	$html=$html."</table>
	
	<script>
		var x_DeleteSMTPSenderInstance= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			RefreshDaemonList();
			
		}	
		
		function DeleteSMTPSenderInstance(uuid){
			var XHR = new XHRConnection();
			XHR.appendData('ou','{$_GET["ou"]}');
			XHR.appendData('hostname','{$_GET["hostname"]}');
			XHR.appendData('smtp-instance-delete',uuid);
			document.getElementById('domain_throttle_daemon_list').innerHTML=\"<center style='margin:10px'><img src='img/wait_verybig.gif'></center>\";
			XHR.sendAndLoad(\"$page\", 'GET',x_DeleteSMTPSenderInstance);
		}	
	
	</script>
	
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);		
	
}

function domains_list(){
	$page=CurrentPageName();
	$tpl=new templates();		
	$main=new maincf_multi($_GET["hostname"],$_GET["ou"]);	
	$array=unserialize(base64_decode($main->GET_BIGDATA("domain_throttle_daemons_list")));
	
		$html="
		<hr>
		
		<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th>&nbsp;</th>
	<th>{domain}</th>
	<th>&nbsp;</th>
	<th>{daemon}</th>
	<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";		
		
	if(is_array($array)){
		while (list ($uuid, $array_conf) = each ($array) ){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$color="#909090";
		if($array_conf["ENABLED"]==1){$color="black";}		
		$js="<a href=\"javascript:blur();\" style='font-size:14px;text-decoration:underline;color:$color' OnClick=\"javascript:YahooWin4(650,'$page?smtp-instance-edit=$uuid&hostname={$_GET["hostname"]}&ou={$_GET["ou"]}','{$array_conf["INSTANCE_NAME"]}')\">";
		
		while (list ($domain, $none) = each ($array_conf["DOMAINS"]) ){    
			$html=$html."<tr class=$classtr>
						<td width=1%><img src='img/32-relayhost.png'></td>
						<td><strong style='font-size:16px;color:$color'>$domain</strong></td>
						<td width=1%  align='center' nowrap><strong style='font-size:16px;color:$color'><img src='img/arrow-right-32.png'></strong></td>
						<td width=1%  align='center' nowrap><strong style='font-size:16px;color:$color'>$js{$array_conf["INSTANCE_NAME"]}</a></strong></td>
						<td width=1% align='center' nowrap>".imgtootltip("delete-32.png",'{delete}',"DeleteSMTPDomainInstance('$uuid','$domain')")."</td>
					</tr>";					
			}
		
			
		}
	}	

	$html=$html."</table>
	
	<script>
		var x_DeleteSMTPDomainInstance= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			RefreshSMTPDomainList();
			
		}	
		
		function DeleteSMTPDomainInstance(uuid,domain){
			var XHR = new XHRConnection();
			XHR.appendData('ou','{$_GET["ou"]}');
			XHR.appendData('hostname','{$_GET["hostname"]}');
			XHR.appendData('smtp-daemon-uuid',uuid);
			XHR.appendData('domain-delete',domain);
			document.getElementById('domain_throttle_domains_list').innerHTML=\"<center style='margin:10px'><img src='img/wait_verybig.gif'></center>\";
			XHR.sendAndLoad(\"$page\", 'GET',x_DeleteSMTPDomainInstance);
		}	
	
	</script>
	
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);		
	
}

function smtp_instance_save(){
	$instance=$_GET["smtp-instance-save"];
	$main=new maincf_multi($_GET["hostname"],$_GET["ou"]);	
	$array=unserialize(base64_decode($main->GET_BIGDATA("domain_throttle_daemons_list")));	
	while (list ($key, $val) = each ($_GET) ){
		$array[$instance][$key]=$val;
	}
	if(!$main->SET_BIGDATA("domain_throttle_daemons_list",base64_encode(serialize($array)))){writelogs("{$_GET["hostname"]}/{$_GET["ou"]}: error...");echo "ERROR";return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-throttle=yes&instance={$_GET["hostname"]}");	
}

function smtp_instance_add(){
	
	$instance=$_GET["smtp-instance-add"];
	if(trim($instance)==null){$instance=time();}
	$main=new maincf_multi($_GET["hostname"],$_GET["ou"]);	
	$array=unserialize(base64_decode($main->GET_BIGDATA("domain_throttle_daemons_list")));	
	if(!is_array($array)){$array=array();}
	$uuid=time();
	$array[$uuid]["INSTANCE_NAME"]=$instance;
	$array[$uuid]["transport_destination_concurrency_limit"]="20";
	$array[$uuid]["transport_destination_rate_delay"]="0s";
	$array[$uuid]["ENABLED"]="1";
	if(!$main->SET_BIGDATA("domain_throttle_daemons_list",base64_encode(serialize($array)))){writelogs("{$_GET["hostname"]}/{$_GET["ou"]}: error...");echo "ERROR";return;}	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-throttle=yes&instance={$_GET["hostname"]}");	
	
}

function smtp_instance_delete(){
	$instance=$_GET["smtp-instance-delete"];
	$main=new maincf_multi($_GET["hostname"],$_GET["ou"]);	
	$array=unserialize(base64_decode($main->GET_BIGDATA("domain_throttle_daemons_list")));	
	unset($array[$instance]);
	if(!$main->SET_BIGDATA("domain_throttle_daemons_list",base64_encode(serialize($array)))){writelogs("{$_GET["hostname"]}/{$_GET["ou"]}: error...");echo "ERROR";return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-throttle=yes&instance={$_GET["hostname"]}");
	
	
}

function smtp_instance_edit(){
		
	$page=CurrentPageName();
	$tpl=new templates();		
	$uuid=$_GET["smtp-instance-edit"];
	$main=new maincf_multi($_GET["hostname"],$_GET["ou"]);	
	$array=unserialize(base64_decode($main->GET_BIGDATA("domain_throttle_daemons_list")));
	$conf=$array[$uuid];
	
	if($conf["transport_destination_concurrency_failed_cohort_limit"]==null){$conf["transport_destination_concurrency_failed_cohort_limit"]=1;}
	if($conf["transport_delivery_slot_loan"]==null){$conf["transport_delivery_slot_loan"]=3;}
	if($conf["transport_delivery_slot_discount"]==null){$conf["transport_delivery_slot_discount"]=50;}
	if($conf["transport_delivery_slot_cost"]==null){$conf["transport_delivery_slot_cost"]=5;}
	if($conf["transport_extra_recipient_limit"]==null){$conf["transport_extra_recipient_limit"]=1000;}
	if($conf["transport_initial_destination_concurrency"]==null){$conf["transport_initial_destination_concurrency"]=5;}
	if($conf["transport_destination_recipient_limit"]==null){$conf["transport_destination_recipient_limit"]=50;}
	if($conf["transport_destination_rate_delay"]==null){$conf["transport_destination_rate_delay"]="0s";}
	if($conf["transport_destination_concurrency_positive_feedback"]==null){$conf["transport_destination_concurrency_positive_feedback"]="1/5";}
	if($conf["transport_destination_concurrency_negative_feedback"]==null){$conf["transport_destination_concurrency_negative_feedback"]="1/5";}
	if(!is_numeric($conf["default_process_limit"])){$conf["default_process_limit"]=100;}
	

	$html="
	<div class=explain>{domain_throttle_explain_edit}</div>
	<div id='id-$uuid'>
	<table class=form>
	<tr>
		<td class=legend>{smtp_daemon_name}:<td>
		<td>". Field_text("INSTANCE_NAME",$conf["INSTANCE_NAME"],"width:160px;font-size:13px")."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend>{default_process_limit}:<td>
		<td>". Field_text("default_process_limit",$conf["default_process_limit"],"width:60px;font-size:13px")."</td>
		<td>". help_icon("{default_process_limit_text}")."</td>
	</tr>	

	<tr>
		<td class=legend>{enabled}:<td>
		<td>". Field_checkbox("ENABLED",1,$conf["ENABLED"],"CheckEnabledInstance()")."</td>
		<td>&nbsp;</td>
	<tr>
	
	<tr>
		<td class=legend>{default_destination_concurrency_limit}:<td>
		<td>". Field_text("transport_destination_concurrency_limit",$conf["transport_destination_concurrency_limit"],"width:60px;font-size:13px")."</td>
		<td width=1%>". help_icon("{default_destination_concurrency_limit_text}")."</td>
	</tr>
	<tr>
		<td class=legend>{default_destination_rate_delay}:<td>
		<td>". Field_text("transport_destination_rate_delay",$conf["transport_destination_rate_delay"],"width:60px;font-size:13px")."</td>
		<td width=1%>". help_icon("{default_destination_rate_delay_text}")."</td>
	</tr>
	
	<tr>
		<td class=legend>{initial_destination_concurrency}:<td>
		<td>". Field_text("transport_initial_destination_concurrency",$conf["transport_initial_destination_concurrency"],"width:60px;font-size:13px")."</td>
		<td width=1%>". help_icon("{initial_destination_concurrency_text}")."</td>		
	</tr>	
	
	<tr>
		<td class=legend>{default_destination_concurrency_failed_cohort_limit}:<td>
		<td>". Field_text("transport_destination_concurrency_failed_cohort_limit",$conf["transport_destination_concurrency_failed_cohort_limit"],"width:60px;font-size:13px")."</td>
		<td width=1%>". help_icon("{default_destination_concurrency_failed_cohort_limit_text}")."</td>		
	</tr>
	<tr>
		<td class=legend>{default_destination_concurrency_positive_feedback}:<td>
		<td>". Field_text("transport_destination_concurrency_positive_feedback",$conf["transport_destination_concurrency_positive_feedback"],"width:60px;font-size:13px")."</td>
		<td width=1%>". help_icon("{default_destination_concurrency_positive_feedback_text}")."</td>		
	</tr>	
	<tr>
		<td class=legend>{default_destination_concurrency_negative_feedback}:<td>
		<td>". Field_text("transport_destination_concurrency_negative_feedback",$conf["transport_destination_concurrency_negative_feedback"],"width:60px;font-size:13px")."</td>
		<td width=1%>". help_icon("{default_destination_concurrency_negative_feedback_text}")."</td>		
	</tr>		
	<tr>
		<td class=legend>{default_destination_recipient_limit}:<td>
		<td>". Field_text("transport_destination_recipient_limit",$conf["transport_destination_recipient_limit"],"width:60px;font-size:13px")."</td>
		<td width=1%>". help_icon("{default_destination_recipient_limit_text}")."</td>		
	</tr>		
	
	<tr>
		<td class=legend>{default_extra_recipient_limit}:<td>
		<td>". Field_text("transport_extra_recipient_limit",$conf["transport_extra_recipient_limit"],"width:60px;font-size:13px")."</td>
		<td width=1%>". help_icon("{default_extra_recipient_limit_text}")."</td>		
	</tr>	

	<tr>
		<td class=legend>{default_delivery_slot_loan}:<td>
		<td>". Field_text("transport_delivery_slot_loan",$conf["transport_delivery_slot_loan"],"width:60px;font-size:13px")."</td>
		<td width=1%>". help_icon("{default_delivery_slot_loan_text}")."</td>
	</tr>	
		
	<tr>
		<td class=legend>{default_delivery_slot_cost}:<td>
		<td>". Field_text("transport_delivery_slot_cost",$conf["transport_delivery_slot_cost"],"width:60px;font-size:13px")."</td>
		<td width=1%>". help_icon("{default_delivery_slot_cost_text}")."</td>
	</tr>		
	
	<tr>
		<td class=legend>{default_delivery_slot_discount}:<td>
		<td>". Field_text("transport_delivery_slot_discount",$conf["transport_delivery_slot_discount"],"width:60px;font-size:13px")."</td>
		<td width=1%>". help_icon("{default_delivery_slot_discount_text}")."</td>
	</tr>	
	
	<tr>
		<td colspan=3 align=right><hr>". button("{apply}","SaveSMTPInstanceParams()")."</td>
	</tr>
	
	</table>
	</div>
	<script>
		function CheckEnabledInstance(){
			DisableFieldsFromId('id-$uuid');
			document.getElementById('ENABLED').disabled=false;
			document.getElementById('INSTANCE_NAME').disabled=false;
			if(!document.getElementById('ENABLED').checked){return;}
			EnableFieldsFromId('id-$uuid');
		}
	
	
		var x_SaveSMTPInstanceParams= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			RefreshTab('main_ecluse_config');
			YahooWin4Hide();
			
		}	
		
		function SaveSMTPInstanceParams(){
			var XHR = XHRParseElements('id-$uuid');
			XHR.appendData('ou','{$_GET["ou"]}');
			XHR.appendData('hostname','{$_GET["hostname"]}');
			XHR.appendData('smtp-instance-save','$uuid');
			document.getElementById('id-$uuid').innerHTML=\"<center style='margin:10px'><img src='img/wait_verybig.gif'></center>\";
			XHR.sendAndLoad(\"$page\", 'GET',x_SaveSMTPInstanceParams);
		}	
	CheckEnabledInstance();
	</script>	
	
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}
	

function popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$array["smtp"]='{smtp_senders}';
	$array["domains"]="{routing_domains}";

	
	while (list ($num, $ligne) = each ($array) ){
		$tab[]="<li><a href=\"$page?$num=yes&hostname={$_GET["hostname"]}&ou={$_GET["ou"]}\"><span>$ligne</span></a></li>\n";
			
	}

	$html="
		<div id='main_ecluse_config' style='background-color:white'>
		<ul>
		". implode("\n",$tab). "
		</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_ecluse_config').tabs();
				});
		</script>
	
	";
		
	
	echo $tpl->_ENGINE_parse_body($html);		
}
function PostFixVerifyRights(){
	$usersmenus=new usersMenus();
	if($usersmenus->AsPostfixAdministrator){return true;}
	if($usersmenus->AsMessagingOrg){return true;}
	}	
?>