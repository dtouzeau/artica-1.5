<?php
	if(posix_getuid()==0){die();}
	session_start();
	if($_SESSION["uid"]==null){echo "window.location.href ='logoff.php';";die();}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.tcpip.inc');
	include_once('ressources/class.system.nics.inc');
	include_once('ressources/class.maincf.multi.inc');
	
	
	$user=new usersMenus();
	if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["instances-tabs"])){tabs();exit;}
	
	if(isset($_GET["instances-list"])){instances_list();exit;}
	if(isset($_GET["instances-search"])){instances_search();exit;}
	
	if(isset($_GET["crossroads-list"])){crossroads_list();exit;}
	if(isset($_GET["crossroads-search"])){crossroads_search();exit;}
	if(isset($_GET["crossroads-delete"])){crossroads_delete();exit;}
	
	
	if(isset($_POST["delete-system-instance"])){instance_system_delete();exit;}
	if(isset($_GET["instance-duplicate-form"])){instances_duplicate_form();exit;}
	if(isset($_GET["add-new-instance"])){instances_duplicate_perform();exit;}
	if(isset($_GET["status-instance"])){instance_status();exit;}
	if(isset($_GET["delete"])){instance_delete();exit;}
	if(isset($_GET["rebuild-instances"])){rebuild_instances();exit;}
	js();
	
	
function js(){
	$page=CurrentPageName();
		echo"document.getElementById('BodyContent').innerHTML='<center><img src=img/wait_verybig.gif></center>';
		$('#BodyContent').load('$page?instances-tabs=yes');";	
}
	
	
	
function tabs(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$array["instances-list"]="{instances}";
	$array["crossroads-list"]="{APP_CROSSROADS}";
	
	while (list ($num, $ligne) = each ($array) ){
		
			$html[]=$tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
			continue;
		
	}
	
	
	echo "
	<div id=main_config_postfixmultipe style='width:100%;height:650px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_config_postfixmultipe\").tabs();});
		</script>";	
	
}	

function instances_list(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$html="
	<center>
		<table style='width:450px' class=form>
		<tr>
			<td class=legend valign='middle'>{search}:</td>
			<td valign='middle'>". Field_text("instances-search",null,"font-size:14px;padding:3px;width:100%",null,null,null,false,"PostfixInstancesSearchCheck(event)")."</td>
			<td width=1% valign='middle'>". button("{search}","PostfixInstancesSearch()")."</td>
		</tr>
		<tr>
			<td class=legend valign='middle'>{on_system}:</td>
			<td colspan=2 align='left'>". Field_checkbox("System", 1,0,'PostfixInstancesSearch()')."</td>
		</tr>
		</table>
	</center>
		<div id='instances-list-table' style='width:100%;height:400px;overflow:auto'></div>
		
	<script>
		function PostfixInstancesSearchCheck(e){
			if(checkEnter(e)){PostfixInstancesSearch();}
		}
		
		
		function PostfixInstancesSearch(){
			System=0;
			var se=escape(document.getElementById('instances-search').value);
			if(document.getElementById('System').checked){System=1;}else{System=0;}
			LoadAjax('instances-list-table','$page?instances-search=yes&search='+se+'&System='+System);
		
		}
		
		PostfixInstancesSearch();
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function crossroads_list(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$html="
	<center>
		<table style='width:450px' class=form>
		<tr>
			<td class=legend valign='middle'>{search}:</td>
			<td valign='middle'>". Field_text("crossroads-search",null,"font-size:14px;padding:3px;width:100%",null,null,null,false,
			"PostfixcrossroadsSearchCheck(event)")."</td>
			<td width=1% valign='middle'>". button("{search}","PostfixcrossroadsSearch()")."</td>
		</tr>
		</table>
	</center>
		<div id='crossroads-list-table' style='width:100%;height:600px;overflow:auto'></div>
		
	<script>
		function PostfixcrossroadsSearchCheck(e){
			if(checkEnter(e)){PostfixcrossroadsSearch();}
		}
		
		
		function PostfixcrossroadsSearch(){
			var se=escape(document.getElementById('crossroads-search').value);
			LoadAjax('crossroads-list-table','$page?crossroads-search=yes&search='+se);
		
		}
		
		PostfixcrossroadsSearch();
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function crossroads_search(){
	$tpl=new templates();
	$page=CurrentPageName();
	$ask_perform_operation_delete_item=$tpl->_ENGINE_parse_body("{delete}: {APP_CROSSROADS}");
	$duplicate_instance=$tpl->_ENGINE_parse_body("{duplicate_instance}");
	$sock=new sockets();
	$search=$_GET["search"];
	$search="*".$_GET["search"]."*";
	$search=str_replace("**","*",$search);
	$search=str_replace("*","%",$search);
	$sock=new sockets();
	$DisableNetworksManagement=$sock->GET_INFO("DisableNetworksManagement");
	if(!is_numeric($DisableNetworksManagement)){$DisableNetworksManagement=0;}
	$apply_network_configuration_warn=$tpl->javascript_parse_text("{apply_network_configuration_warn}");
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");
	
	$sql="SELECT * FROM crossroads_smtp WHERE ipaddr LIKE '$search'";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>". imgtootltip("refresh-32.png","{refresh}","PostfixcrossroadsSearch()")."</th>
		<th>{ipaddr}</th>
		<th>{instances}</th>
		<th>&nbsp;</th>

	</tr>
</thead>
<tbody class='tbody'>	
	";
	$c=0;
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	if($ligne["ou"]==null){$textou="&nbsp;";}else{$textou=$ligne["ou"];}
	$arrayConf=unserialize($ligne["parameters"]);
	$instancesParams=$arrayConf["INSTANCES_PARAMS"];
	$cd=array();	
	$cd[]="<table style='width:100%;background-color:transparent'>";
	while (list ($ip, $none) = each ($arrayConf["INSTANCES"]) ){
		
		if(!is_numeric($instancesParams["MAXCONS"][$ip])){$instancesParams["MAXCONS"][$ip]=0;}
		if(!is_numeric($instancesParams["WEIGTH"][$ip])){$instancesParams["WEIGTH"][$ip]=1;}						
		$cd[]="<tr style='background-color:transparent'>
		<td width=1% style='background-color:transparent;;height:auto;border:0px' width=1%><img src=img/fw_bold.gif></td>
		<td width=99% style='background-color:transparent;font-size:12px;font-weight:bold;height:auto;border:0px'>$ip:25 ({$instancesParams["MAXCONS"][$ip]}/{$instancesParams["WEIGTH"][$ip]})</td>
		</tr>";
		
	}
	$cd[]="</table>";
	
	$href="<a href=\"javascript:blur();\" 
	OnClick=\"javascript:Loadjs('postfix.multiple.crossroads.php?ipaddr={$ligne["ipaddr"]}');\" style='font-size:14px;text-decoration:underline'>";
	
	$html=$html."
	
	
	<tr  class=$classtr>
	<td style='font-size:14px;font-weight:bold'><img src=img/folder-dispatch-22.png></td>
	<td style='font-size:14px;font-weight:bold' valing='top'>$href{$ligne["ipaddr"]}</a></td>
	<td style='font-size:14px;font-weight:bold'>". @implode("\n",$cd)."</td>
	<td width=1%>". imgtootltip("delete-24.png","{delete}","POSTFIX_CROSSROADS_DELETE('{$ligne["ipaddr"]}')")."</td>
	</tR>";
	$c++;
	}
	
	
	$html=$html."
	
	
	</table>
	
	<script>
	var X_POSTFIX_CROSSROADS_DELETE= function (obj) {
	 var results=obj.responseText;
	 if(results.length>1){alert(results);}
	 PostfixcrossroadsSearch();
	}	
	
	function POSTFIX_CROSSROADS_DELETE(ip){
		if(confirm('$ask_perform_operation_delete_item\\n('+ip+')')){
				var XHR = new XHRConnection();
				XHR.appendData('crossroads-delete','yes');
				XHR.appendData('ipaddr',ip);
				document.getElementById('crossroads-list-table').innerHTML='<center><img src=img/wait_verybig.gif></center>';
				XHR.sendAndLoad('$page', 'GET',X_POSTFIX_CROSSROADS_DELETE);	
		}
	
	}
	
	
	</script>	
	
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
	
}

function crossroads_delete(){
	$sql="DELETE FROM crossroads_smtp WHERE ipaddr='{$_GET["ipaddr"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("network.php?crossroads-restart=yes");
	
}

function instances_search(){
	$tpl=new templates();
	$page=CurrentPageName();
	$ask_perform_operation_delete_item=$tpl->_ENGINE_parse_body("{ask_perform_operation_delete_item}");
	$duplicate_instance=$tpl->_ENGINE_parse_body("{duplicate_instance}");
	
	$sock=new sockets();
	$DisableNetworksManagement=$sock->GET_INFO("DisableNetworksManagement");
	if(!is_numeric($DisableNetworksManagement)){$DisableNetworksManagement=0;}
	$apply_network_configuration_warn=$tpl->javascript_parse_text("{apply_network_configuration_warn}");
	$ERROR_NO_PRIVS=$tpl->javascript_parse_text("{ERROR_NO_PRIVS}");

	$html="
	
	<div style='margin:8px;text-align:right'>". button("{apply_network_configuration}","POSTFIX_MULTI_INSTANCE_APPLY()")."</div>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>". imgtootltip("refresh-32.png","{refresh}","PostfixInstancesSearch()")."</th>
		<th>{servername}</th>
		<th>{organization}</th>
		<th>{ip_address}</th>
		<th colspan=4>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
	$c=0;
	if($_GET["System"]==0){
			$search=$_GET["search"];
			$search="*".$_GET["search"]."*";
			$search=str_replace("**","*",$search);
			$search=str_replace("*","%",$search);
		
			$sql="SELECT ou, ip_address, `key` , `value` FROM postfix_multi 
			WHERE (`key` = 'myhostname' AND value LIKE '$search') OR (`key` = 'myhostname' AND ip_address LIKE '$search') ORDER BY value LIMIT 0,50";
			$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
			if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
		
		
		
		
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		if($ligne["ou"]==null){$textou="&nbsp;";}else{$textou=$ligne["ou"];}
		$instances[$c]=$ligne["value"];
		$strlen=strlen($ligne["value"]);
		if($strlen>33){$hostname_text=substr($ligne["value"],0,33)."...";}else{$hostname_text=$ligne["value"];}
		
		$href="<a href=\"javascript:blur();\" OnClick=\"javascript:YahooWin('650','domains.postfix.multi.config.php?ou={$ligne["ou"]}&hostname={$ligne["value"]}','{$ligne["value"]}');\" style='font-size:14px;text-decoration:underline'>";
		
		$html=$html."
		
		
		<tr  class=$classtr>
		<td style='font-size:14px;font-weight:bold'><img src=img/fw_bold.gif></td>
		<td style='font-size:14px;font-weight:bold'>$href$hostname_text</a></td>
		<td style='font-size:14px;font-weight:bold'>$href$textou</a></td>
		<td style='font-size:14px;font-weight:bold'>$href{$ligne["ip_address"]}</a></td>
		<td style='font-size:14px;font-weight:bold'><span id='status-instance-$c'><img src='img/ok32-grey.png'><span></td>
		<td width=1%>". imgtootltip("32-plus.png","{create_new_instance_based_on_this_instance}","DuplicateInstance('{$ligne["value"]}')")."</td>
		<td width=1%>". imgtootltip("delete-24.png","{delete}","POSTFIX_MULTI_INSTANCE_INFOS_DEL('{$ligne["ou"]}','{$ligne["ip_address"]}')")."</td>
		</tR>";
		$c++;
		}
		
		
	}else{
		$sock=new sockets();
		$search=$_GET["search"];
		$Datas=unserialize(base64_decode($sock->getFrameWork("postfix.php?postfix-instances-list=yes&search=".urlencode($search))));
		while (list ($num, $ligne2) = each ($Datas) ){
			if(!preg_match("#^postfix-(.+?)\s+-#", trim($ligne2),$re)){continue;}
			$ligne["value"]=trim($re[1]);
			if($ligne["value"]=="-"){continue;}
			$instances[$c]=$ligne["value"];
			$strlen=strlen($ligne["value"]);
			if($strlen>33){$hostname_text=substr($ligne["value"],0,33)."...";}else{$hostname_text=$ligne["value"];}
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$html=$html."
			<tr  class=$classtr>
				<td style='font-size:14px;font-weight:bold'><img src=img/fw_bold.gif></td>
				<td style='font-size:14px;font-weight:bold'>$href$hostname_text</a></td>
				<td style='font-size:14px;font-weight:bold'>-</a></td>
				<td style='font-size:14px;font-weight:bold'>-</a></td>
				<td style='font-size:14px;font-weight:bold'><span id='status-instance-$c'><img src='img/ok32-grey.png'><span></td>
				<td width=1%>-</td>
				<td width=1%>". imgtootltip("delete-24.png","{delete}","POSTFIX_MULTI_INSTANCE_SYSTEM_DEL('{$ligne["value"]}')")."</td>
			</tR>";
		$c++;			
			
			
			
		}
		
		
	}
	
	$instances=urlencode(base64_encode(serialize($instances)));
	$html=$html."</table>
	<script>
	var X_POSTFIX_MULTI_INSTANCE_INFOS_DEL= function (obj) {
	 var results=obj.responseText;
	 if(results.length>1){alert(results);}
	 PostfixInstancesSearch();
	}	
	
	function POSTFIX_MULTI_INSTANCE_INFOS_DEL(ou,ip){
		if(confirm('$ask_perform_operation_delete_item\\n'+ou+'('+ip+')')){
				var XHR = new XHRConnection();
				XHR.appendData('delete','yes');
				XHR.appendData('ou',ou);
				XHR.appendData('ip',ip);
				document.getElementById('instances-list-table').innerHTML='<center><img src=img/wait_verybig.gif></center>';
				XHR.sendAndLoad('$page', 'GET',X_POSTFIX_MULTI_INSTANCE_INFOS_DEL);	
		}
	
	}
	
	function POSTFIX_MULTI_INSTANCE_SYSTEM_DEL(hostname){
		if(confirm('$ask_perform_operation_delete_item\\n'+hostname)){
				var XHR = new XHRConnection();
				XHR.appendData('delete-system-instance',hostname);
				document.getElementById('instances-list-table').innerHTML='<center><img src=img/wait_verybig.gif></center>';
				XHR.sendAndLoad('$page', 'POST',X_POSTFIX_MULTI_INSTANCE_INFOS_DEL);	
		}	
	
	}
	
	function DuplicateInstance(src){
		YahooWin('450','$page?instance-duplicate-form='+src,'$duplicate_instance::'+src);
	
	}
	
	function StatusInstance0(){
		LoadAjaxHidden('status-instance-0','$page?status-instance=0&hostnames=$instances');
	
	}
	
	function POSTFIX_MULTI_INSTANCE_APPLY(){
			var DisableNetworksManagement=$DisableNetworksManagement;
			if(DisableNetworksManagement==1){alert('$ERROR_NO_PRIVS');return;}	
			if(confirm('$apply_network_configuration_warn')){	
				var XHR = new XHRConnection();
				XHR.appendData('rebuild-instances','yes');
				XHR.appendData('ou','x');
				XHR.sendAndLoad('$page', 'GET',X_POSTFIX_MULTI_INSTANCE_INFOS_DEL);	
			}
		}
	
	
	
	
	StatusInstance0();
	
	</script>	
	
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
}

function instance_system_delete(){
	$instance=$_POST["delete-system-instance"];
	$main=new maincf_multi($instance);
	$ip=$main->ip_addr;
	if($ip<>null){
		$sql="DELETE FROM postfix_multi WHERE ip_address='$ip'";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");		
	}
	$sock=new sockets();
	$sock->getFrameWork("postfix.php?instance-delete=$instance");
	
}


	
function instance_delete(){
	$sql="DELETE FROM postfix_multi WHERE ou='{$_GET["ou"]}' AND ip_address='{$_GET["ip"]}'";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");	
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-multi-configure-ou={$_GET["ou"]}");
}

function instance_status(){
$int=$_GET["status-instance"];
$hostnames=unserialize(base64_decode($_GET["hostnames"]));

$page=CurrentPageName();
if($hostnames[$int]==null){die();}
$sock=new sockets();
$stat=unserialize(base64_decode($sock->getFrameWork("cmd.php?postfix-mutli-stat={$hostnames[$int]}")));
$log="<!-- $int: {$hostnames[$int]} stat:{$stat[0]} -->";
$int=$int+1;
if($stat[0]==1){$img="ok32.png";}else{$img="okdanger32.png";}
$nextjs="
$log

<script>
	function StatusInstance$int(){
		LoadAjaxHidden('status-instance-$int','$page?status-instance=$int&hostnames=". urlencode($_GET["hostnames"])."');
	
	}
	StatusInstance$int();
</script>
";

echo "<img src='img/$img'>$nextjs";


}

function instances_duplicate_form(){
	$tpl=new templates();
	$q=new mysql();
	$page=CurrentPageName();	
	$ldap=new clladp();
	$ous=$ldap->hash_get_ou(true);
	$ous[null]="{select}";
	
	
	//try to find default values.
	writelogs("Build from -> {$_GET["instance-duplicate-form"]}",__FUNCTION__,__FILE__,__LINE__);
	
	$main=new maincf_multi($_GET["instance-duplicate-form"]);
	$tr=explode(".",$_GET["instance-duplicate-form"]);
	writelogs("Build from -> hostname {$tr[0]}",__FUNCTION__,__FILE__,__LINE__);
	
	$tr[0]=$tr[0]."-new";
	$defhostname=@implode(".",$tr);
	writelogs("Build from -> Default hostname $defhostname",__FUNCTION__,__FILE__,__LINE__);
	$ipaddr=$main->ip_addr;
	
	writelogs("Build from -> source iP addr: $main->ip_addr",__FUNCTION__,__FILE__,__LINE__);
	$instance_ip_tbl=explode(".",$main->ip_addr);
	unset($instance_ip_tbl[count($instance_ip_tbl)-1]);
	$net=@implode(".",$instance_ip_tbl);	
	
	writelogs("Build from -> Net: $net",__FUNCTION__,__FILE__,__LINE__);
	$sql="SELECT ipaddr FROM nics_virtuals WHERE ipaddr LIKE '$net.%' ORDER BY ID DESC LIMIT 0,1";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	
	$last_ip=$ligne["ipaddr"];
	$instance_ip_tbl=explode(".",$last_ip);
	$last_number=$instance_ip_tbl[count($instance_ip_tbl)-1];
	writelogs("Build from -> Ladt ip: {$ligne["ipaddr"]}, last number: $last_number",__FUNCTION__,__FILE__,__LINE__);
	unset($instance_ip_tbl[count($instance_ip_tbl)-1]);
	if($last_number<255){$last_number=$last_number+1;}else{$last_number="?";}
	$default_ip_addr=@implode(".",$instance_ip_tbl).".$last_number";
	
	$html="
	<div class=explain>{create_new_instance_based_on_this_instance_explain}</div>
	
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{ipaddr}:</td>
		<td>". Field_text("new-ip-addr",$default_ip_addr,"font-size:14px;padding:3px;width:120px")."</td>
	</tr>
	<tr>
		<td class=legend>{instance_server_name}:</td>
		<td>". Field_text("new-hostname",$defhostname,"font-size:14px;padding:3px;width:170px")."</td>
	</tr>	
	<tr>
		<td class=legend>{organization}:</td>
		<td>". Field_array_Hash($ous,"ou-to-add",$main->ou,"style:font-size:14px;padding:3px")."</td>
	</tr>	
	<tr>
		<td colspan=2 align=right><hr>". button("{add}","AddNewInstance()")."</td>
	</tr>
	</table>
	
	<script>
	
		var X_AddNewInstance= function (obj) {
		 var results=obj.responseText;
		 if(results.length>1){alert(results);PostfixInstancesSearch();return;}
		 YahooWinHide();
		 PostfixInstancesSearch();
		}	
	
		function AddNewInstance(){
			var XHR = new XHRConnection();
			XHR.appendData('add-new-instance','yes');
			XHR.appendData('ou',document.getElementById('ou-to-add').value);
			XHR.appendData('ip',document.getElementById('new-ip-addr').value);
			XHR.appendData('hostname',document.getElementById('new-hostname').value);
			XHR.appendData('src','{$_GET["instance-duplicate-form"]}');
			document.getElementById('instances-list-table').innerHTML='<center><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',X_AddNewInstance);			
		}
	
	
	</script>";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function instances_duplicate_perform(){
	$tpl=new templates();
	$tcp=new IP();
	$q=new mysql();
	$sock=new sockets();
	$instance_hostname=$_GET["hostname"];
	$instance_src_hostname=$_GET["src"];
	$main=new maincf_multi($instance_src_hostname);
	$novirtual=false;
	
	$instance_ip=$_GET["ip"];
	$instance_ip_src=$main->ip_addr;
	if($instance_ip_src==null){echo $tpl->javascript_parse_text("\"$instance_src_hostname\" No such instance");return;}
	
	$organization=$_GET["ou"];
	if($organization==null){
	echo $tpl->javascript_parse_text("{error_choose_organization}");return;}
	
	
	if(trim($instance_hostname)==null){echo $tpl->javascript_parse_text("{instance_server_name}: NULL");return;}
	if(!$tcp->isValid($instance_ip)){echo $tpl->javascript_parse_text("{ipaddr}: \"$instance_ip\" Invalid");return;}
	if(!$tcp->isValid($instance_ip_src)){echo $tpl->javascript_parse_text("{ipaddr}: \"$instance_ip_src\" Invalid");return;}
	$main=new maincf_multi(null,null,$instance_ip);
	if($main->myhostname<>null){echo $tpl->javascript_parse_text("{ipaddr}: \"$instance_ip\" {already_used} -> $main->myhostname");return;}
	$main=new maincf_multi($instance_hostname,null,null);
	if($main->ip_addr<>null){echo $tpl->javascript_parse_text("{hostname}: \"$instance_hostname\" {already_used} -> $main->ip_addr");return;}
	
	$sql="SELECT ipaddr FROM nics_virtuals WHERE ipaddr='$instance_ip'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	if($ligne["ipaddr"]<>null){echo $tpl->javascript_parse_text("{ipaddr}: \"$instance_ip\" {already_used} -> {virtual_interfaces}");return;}
	
	$sql="SELECT ipaddr FROM nics_vlan WHERE ipaddr='$instance_ip'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	if($ligne["ipaddr"]<>null){
		writelogs("Associated to a vlan -> unset Contruct virtuals addresses",__FUNCTION__,__FILE__,__LINE__);
		$novirtual=true;
	}
	
	
	$PING=trim($sock->getFrameWork("cmd.php?ping=".urlencode($instance_ip)));
	if($PING=="TRUE"){
		echo $tpl->javascript_parse_text("$instance_ip:\n{ip_already_exists_in_the_network}");
		return;
	}	
	
	
	if(!$novirtual){
		writelogs("No virtual: FALSE",__FUNCTION__,__FILE__,__LINE__);
		$instance_ip_tbl=explode(".",$instance_ip);
		unset($instance_ip_tbl[count($instance_ip_tbl)-1]);
		$net=@implode(".",$instance_ip_tbl);
		writelogs("virtual: net -> $net",__FUNCTION__,__FILE__,__LINE__);
	
		$sql="SELECT * FROM nics_virtuals WHERE ipaddr LIKE '$net.%' ORDER BY ID DESC LIMIT 0,1";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		if($ligne["ipaddr"]==null){echo $tpl->javascript_parse_text("{ipaddr}: \"$net*\" {no_such_interfaces} -> {virtual_interfaces}\n{you_need_to_create_virtual_ip_first}");return;}
		writelogs("$net.* {$ligne["nic"]} -> $instance_ip/{$ligne["netmask"]}",__FUNCTION__,__FILE__,__LINE__);
		
		$sql="INSERT INTO nics_virtuals (nic,org,ipaddr,netmask,cdir,gateway) 
		VALUES('{$ligne["nic"]}','$organization','$instance_ip','{$ligne["netmask"]}','{$ligne["cdir"]}','{$ligne["gateway"]}');";	
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		
		if(!$q->ok){
			writelogs("virtual:ERROR $q->mysql_error",__FUNCTION__,__FILE__,__LINE__);
			echo $q->mysql_error;
			return;
		}
		
		
	}
		writelogs("All are OK -> starting importation",__FUNCTION__,__FILE__,__LINE__);

	$sql="SELECT `key`,`value`,`ValueTEXT`,`uuid` FROM `postfix_multi` WHERE `ip_address`='$instance_ip_src'";
	$results=$q->QUERY_SQL($sql,"artica_backup");	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
		$key=$ligne["key"];
		$value=$ligne["value"];
		$ValueTEXT=$ligne["ValueTEXT"];
		$uuid=$ligne["uuid"];
		if($key=="inet_interfaces"){continue;}
		if($key=="myhostname"){continue;}
		$value=addslashes($value);
		$ValueTEXT=addslashes($ValueTEXT);
		$sql="INSERT INTO `postfix_multi`
		(`key`,`value`,`ValueTEXT`,`uuid`,`ou`,`ip_address`)
		VALUES('$key','$value','$ValueTEXT','$uuid','$organization','$instance_ip')";
		writelogs($sql,__FUNCTION__,__FILE__,__LINE__);
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){
			echo $q->mysql_error;
			FailedIP($instance_ip);
			return;
		}
	}
	
$sql="INSERT INTO `postfix_multi`
		(`key`,`value`,`uuid`,`ou`,`ip_address`)
		VALUES('inet_interfaces','$instance_ip','$uuid','$organization','$instance_ip')";
	writelogs($sql,__FUNCTION__,__FILE__,__LINE__);
	$q->QUERY_SQL($sql,"artica_backup");
	
	if(!$q->ok){echo $q->mysql_error;FailedIP($instance_ip);return;}
	
$sql="INSERT INTO `postfix_multi`
		(`key`,`value`,`uuid`,`ou`,`ip_address`)
		VALUES('myhostname','$instance_hostname','$uuid','$organization','$instance_ip')";		
	writelogs($sql,__FUNCTION__,__FILE__,__LINE__);
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;FailedIP($instance_ip);return;}

	$main=new maincf_multi(null,null,$instance_ip);	
	$main->SET_VALUE("VirtualHostNameToChange",$instance_hostname);		
	
	
}


function rebuild_instances(){
		$sock=new sockets();
		$sock->getFrameWork("network.php?reconfigure-postfix-instances=yes");
}


function FailedIP($ip){
	$sql="DELETE FROM postfix_multi WHERE `ip_address`='$ip'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	
	$sql="DELETE FROM nics_virtuals WHERE ipaddr='$ip'";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	
}

