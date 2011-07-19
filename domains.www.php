<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.apache.inc');
	include_once('ressources/class.system.network.inc');
	include_once('ressources/class.pdns.inc');
	
	$usersmenus=new usersMenus();
	if(!$usersmenus->AsOrgAdmin){
		$tpl=new templates();
		echo $tpl->javascript_parse_text('{ERROR_NO_PRIVS}');
		exit;
	}
	
	if(isset($_GET["add"])){js_add();exit;}
	if(isset($_GET["popup-add"])){popup_add();exit;}
	if(isset($_GET["wwwInfos"])){wwwInfos();exit;}
	if(isset($_GET["add-www-service"])){add_web_service();exit;}
	if(isset($_GET["delete-www-service"])){delete_www_service();exit;}
	
js();

function delete_www_service(){
	$sock=new sockets();
	echo $sock->getFrameWork('cmd.php?vhost-delete='.$_GET["delete-www-service"]);
}

function js_add(){
	
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{ADD_WEB_SERVICE}",'domains.manage.org.index.php');
	$page=CurrentPageName();
	$prefix="add_".str_replace(".","_",$page);
	$ou=$_GET["ou"];
	$www_delete_web_service_confirm=$tpl->javascript_parse_text("{www_delete_web_service_confirm}");
	$html="
	var timeout=0;
	
	function {$prefix}_start(){
		YahooWin(650,'$page?popup-add=yes&ou=$ou&host={$_GET["host"]}','{$_GET["host"]}');
		
	}
	
	function AddWWWServiceChange(){
		LoadAjax('wwwInfos','$page?wwwInfos='+document.getElementById('ServerWWWType').value);   
	}
	
var x_AddWebService= function (obj) {
			var results=obj.responseText;
			if(results.length>0){
				alert(results);
				document.getElementById('wwwInfos').innerHTML='';
				return;
			}
			document.getElementById('wwwInfos').innerHTML='';
			YahooWinHide();
			
			if(document.getElementById('groupwares-list-div')){
				LoadAjax('groupwares-list-div','$page?groupware-list=yes&SwitchOrgTabs=$ou&ou=$ou&mem=yes');
			}	
					
			RefreshTab('org_main');
			
			if(document.getElementById('ORG_VHOSTS_LIST')){
				LoadAjax('ORG_VHOSTS_LIST','domains.manage.org.index.php?ORG_VHOSTS_LIST=$ou');
			}
			
			
			}

	var x_DelWebService= function (obj) {
			var results=obj.responseText;
			if(results.length>0){
				alert(results);
				document.getElementById('wwwInfos').innerHTML='';
				}
			document.getElementById('wwwInfos').innerHTML='';
			YahooWinHide();
			RefreshTab('org_main');
			if(document.getElementById('ORG_VHOSTS_LIST')){
				LoadAjax('ORG_VHOSTS_LIST','domains.manage.org.index.php?ORG_VHOSTS_LIST=$ou');
			}
	}			
	
	function AddWebService(){
		var XHR = new XHRConnection();
		XHR.appendData('add-www-service','yes');
		XHR.appendData('ou','$ou');
		if(document.getElementById('ServerWWWType')){XHR.appendData('ServerWWWType',document.getElementById('ServerWWWType').value);}
		if(document.getElementById('servername')){XHR.appendData('servername',document.getElementById('servername').value);}
		if(document.getElementById('domain')){XHR.appendData('domain',document.getElementById('domain').value);}
		if(document.getElementById('IP')){XHR.appendData('IP',document.getElementById('IP').value);}
		XHR.appendData('host','{$_GET["host"]}');
		
		if(document.getElementById('WWWSSLMode').checked){
			XHR.appendData('WWWSSLMode','TRUE');
		}else{
			XHR.appendData('WWWSSLMode','FALSE');
		}
		
		if(document.getElementById('WWWMysqlUser').value=='admin'){alert('admin: not allowed');return;}
		if(document.getElementById('WWWMysqlUser').value=='Admin'){alert('Admin: not allowed');return;}
		if(document.getElementById('WWWMysqlUser').value=='Manager'){alert('Manager: not allowed');return;}
		if(document.getElementById('WWWMysqlUser').value=='root'){alert('root: not allowed');return;}
		
		XHR.appendData('WWWMysqlUser',document.getElementById('WWWMysqlUser').value);
		XHR.appendData('WWWMysqlPassword',document.getElementById('WWWMysqlPassword').value);
		if(document.getElementById('WWWAppliUser')){XHR.appendData('WWWAppliUser',document.getElementById('WWWAppliUser').value);}
		if(document.getElementById('WWWMultiSMTPSender')){XHR.appendData('WWWMultiSMTPSender',document.getElementById('WWWMultiSMTPSender').value);}
		if(document.getElementById('WWWEnableAddressBook')){XHR.appendData('WWWEnableAddressBook',document.getElementById('WWWEnableAddressBook').value);}
		
		
		
		XHR.appendData('WWWAppliPassword',document.getElementById('WWWAppliPassword').value);
		document.getElementById('wwwInfos').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
    	XHR.sendAndLoad('$page', 'GET',x_AddWebService);		
		}
		
		function DelWebService(){
			if(confirm('$www_delete_web_service_confirm')){
				var XHR = new XHRConnection();
				XHR.appendData('delete-www-service','{$_GET["host"]}');
				document.getElementById('wwwInfos').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
				XHR.sendAndLoad('$page', 'GET',x_DelWebService);
			}	
			
		}
	
	
	{$prefix}_start();
	";
	
	echo $html;
	
}


function popup_add(){
	$sock=new sockets();
	$users=new usersMenus();
$ApacheGroupWarePort=$sock->GET_INFO("ApacheGroupWarePort");
$ApacheGroupWarePortSSL=$sock->GET_INFO("ApacheGroupWarePortSSL");
if(!is_numeric($ApacheGroupWarePortSSL)){$ApacheGroupWarePortSSL=443;}	
	$list=listOfAvailableServices();
	$ldap=new clladp();
	$sock=new sockets();
	$domns=$ldap->hash_get_domains_ou($_GET["ou"]);
	
	
	if(count($domns)==0){
		$fqdns=explode(".",$users->fqdn);
		unset($fqdns[0]);
		$fqdn=@implode(".",$fqdns);
		$domns[$fqdn]=$fqdn;
	}
	$domns[null]="{select}";
	$domains=Field_array_Hash($domns,"domain",null);
	$page=CurrentPageName();
	
	$wwwmysqluser_disabled=false;
	$wwwmysqlpassword_disabled=false;
	
	
	$ip=new networking();
	$ips=$ip->ALL_IPS_GET_ARRAY();
	$ips[null]="{select}";
	$eth=Field_array_Hash($ips,'IP');
	$title="{ADD_WEB_SERVICE}";
	$button="{add}";
	$img="www-add-128.png";
	
	$server_row="<tr>
					<td class=legend>{www_server_name}:</td>
					<td>". Field_text("servername",null,"width:90px;padding:3px;font-size:13px")."&nbsp;$domains</td>
				</tr>";
				
	$address_row="<tr>
					<td class=legend>{address}:</td>
					<td>$eth</td>
				</tr>";
				
$h=new vhosts();
	
if($_GET["host"]<>null){
	
	

	
		$title=$_GET["host"];
		$button="{edit}";
		$LoadVhosts=$h->LoadHost($_GET["ou"],$_GET["host"]);
		$serv=$LoadVhosts["wwwservertype"];
		
		if($h->noneeduser["$serv"]){
			$wwwmysqluser_disabled=true;
			$wwwmysqlpassword_disabled=true;
		}
		
		if($h->noneeduser_mysql["$serv"]){		
		}

		
		$img=$h->IMG_ARRAY_128[$LoadVhosts["wwwservertype"]];
		$list="<input type='hidden' id='ServerWWWType' name='ServerWWWType' value='{$LoadVhosts["wwwservertype"]}'><i style='font-size:14px'>{$LoadVhosts["wwwservertype"]}</i>";
		$server_row="<tr>
					<td class=legend>{www_server_name}:</td>
					<td><strong style='font-size:16px'>{$_GET["host"]}</strong></td>
				</tr>";	
	   $address_row=null;
	   $delete=	"<tr>
	   				
					<td colspan=2 align='right'>".button("{delete}","DelWebService();")."
				</tr>";
	}
	
	$users_row="<tr>
					<td class=legend>{WWWAppliUser}:</td>
					<td>". Field_text("WWWAppliUser",$LoadVhosts["wwwappliuser"],'width:120px;padding:3px;font-size:13px')."</td>
				</tr>";

	if($_GET["host"]<>null){
		if($h->noneeduser[$LoadVhosts["wwwservertype"]]){
			$users_row=null;	
		}
	}
	
	
	if($LoadVhosts["wwwsslmode"]=="TRUE"){$LoadVhosts["wwwsslmode"]=1;}else{$LoadVhosts["wwwsslmode"]=0;}
	
	$EnablePostfixMultiInstance=$sock->GET_INFO("EnablePostfixMultiInstance");
	
	if($EnablePostfixMultiInstance==1){
		$q=new mysql();
		$sql="SELECT ipaddr FROM nics_virtuals WHERE org='{$_GET["ou"]}'";
		$results=$q->QUERY_SQL($sql,"artica_backup");
		$ipssmtp[null]="{select}";
		
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$ipssmtp[trim($ligne["ipaddr"])]=trim($ligne["ipaddr"]);
			}		
		$ipssmtp["127.0.0.1"]="127.0.0.1";
		
		$WWWMultiSMTPSender="
				<tr>
					<td class=legend>{WWWMultiSMTPSender}:</td>
					<td>". Field_array_Hash($ipssmtp,"WWWMultiSMTPSender",$LoadVhosts["WWWMultiSMTPSender"],"style:;padding:3px;font-size:13px")."</td>
				</tr>	";	
		
		
		
	}
	
	
	if($h->WWWEnableAddressBook_ENABLED[$LoadVhosts["wwwservertype"]]){
		if($LoadVhosts["WWWEnableAddressBook"]==null){$LoadVhosts["WWWEnableAddressBook"]=0;}
		$WWWEnableAddressBook="
		<tr>
			<td class=legend >{roundcube_ldap_directory}:</td>
			<td style='width:170px'>". Field_checkbox("WWWEnableAddressBook",1,$LoadVhosts["WWWEnableAddressBook"])."</td>
		</tr>";
		
		
	}
	
	
		if($LoadVhosts["wwwservertype"]=="ROUNDCUBE"){
			if($_GET["host"]<>null){
				$hostEncrypt=base64_encode($_GET["host"]);
				$roundcube_globaladdressBook="<br><hr>".Paragraphe("addressbook-64.png","{global_addressbook}","{global_addressbook_explain}","javascript:Loadjs('roundcube.globaladdressbook.php?www=$hostEncrypt')");
			}
			
		}	
	
		if($LoadVhosts["wwwservertype"]=="SUGAR"){
			if($_GET["host"]<>null){
				$port=$ApacheGroupWarePort;
				if($LoadVhosts["wwwsslmode"]==1){$port=$ApacheGroupWarePortSSL;}
				$sugar_warn="<strong style='color:red'>{SUGAR_WARNING_FINISH_INSTALL}:<br>
				<a style='font-size:12px;font-weight:bold;text-decoration:underline' href='http://{$_GET["host"]}:$ApacheGroupWarePort/install.php?goto=SilentInstall&cli=true'>http://{$_GET["host"]}:$ApacheGroupWarePort/install.php?goto=SilentInstall&cli=true<a>

				";
			}
			
		}	
	
	

	
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top'><img src='img/$img'>$roundcube_globaladdressBook
		<td valign='top'>
			<table style='width:100%' class=form>
				<tr>
					<td class=legend>{service_type}:</td>
					<td>$list</td>
				</tr>
				$server_row
				$address_row
				<tr>
					<td class=legend>{https_mode} ({port}:443):</td>
					<td>". Field_checkbox("WWWSSLMode",1,$LoadVhosts["wwwsslmode"])."</td>
				</tr>
				$WWWMultiSMTPSender				
				<tr>
					<td class=legend>{WWWMysqlUser}:</td>
					<td>". Field_text("WWWMysqlUser",$LoadVhosts["wwwmysqluser"],'width:120px;padding:3px;font-size:13px',null,null,null,false,null,$wwwmysqluser_disabled)."</td>
				</tr>
				<tr>
					<td class=legend>{WWWMysqlPassword}:</td>
					<td>". Field_password("WWWMysqlPassword",$LoadVhosts["wwwmysqlpassword"],'width:120px;padding:3px;font-size:13px',null,null,null,false,null,$wwwmysqlpassword_disabled)."</td>
				</tr>				
				$users_row
				<tr>
					<td class=legend>{WWWAppliPassword}:</td>
					<td>". Field_password("WWWAppliPassword",$LoadVhosts["wwwapplipassword"],'width:120px;padding:3px;font-size:13px',null,null,null,false,null,$wwwapplipassword_disabled)."</td>
				</tr>		
				$WWWEnableAddressBook
				<tr>
					<td colspan=2 align='right'><hr>
					". button("$button","AddWebService();")."
				</tr>
				$delete							
				<tr>
					<td colspan=2 valign='top'>
						$sugar_warn
						<div id='wwwInfos'></div>	
					</td>
				</tr>

					
			</table>
			
		</td>
	</tr>
	</table>
	
	
	";
	
	if($h->noneeduser["$serv"]){
		$script_1="
		if(document.getElementById('WWWAppliUser')){document.getElementById('WWWAppliUser').disabled=true;}
		if(document.getElementById('WWWAppliPassword')){document.getElementById('WWWAppliPassword').disabled=true;}
		";
	}else{
		$script_1="
		if(document.getElementById('WWWAppliUser')){document.getElementById('WWWAppliUser').disabled=false;}
		if(document.getElementById('WWWAppliPassword')){document.getElementById('WWWAppliPassword').disabled=false;}
		";
	}
	
	if($h->noneeduser_mysql["$serv"]){
		$script_2="
		if(document.getElementById('WWWMysqlUser')){document.getElementById('WWWMysqlUser').disabled=true;}
		if(document.getElementById('WWWMysqlPassword')){document.getElementById('WWWMysqlPassword').disabled=true;}
		";
	}else{
		$script_2="
		if(document.getElementById('WWWMysqlUser')){document.getElementById('WWWMysqlUser').disabled=false;}
		if(document.getElementById('WWWMysqlPassword')){document.getElementById('WWWMysqlPassword').disabled=false;}
		";
	}	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'domains.manage.org.index.php')."
	<script>
		function wwwEnableDisable(){
			$script_1
			$script_2
		}
		setTimeout('wwwEnableDisable()',1000);
	</script>";
	
	
}


function wwwInfos(){
	$tpl=new templates();
	$h=new vhosts();
	$serv=$_GET["wwwInfos"];
	if(is_numeric($serv)){return null;}
	if($serv==null){return null;}
	
	if($h->noneeduser["$serv"]){
		$script_1="
		if(document.getElementById('WWWAppliUser')){document.getElementById('WWWAppliUser').disabled=true;}
		if(document.getElementById('WWWAppliPassword')){document.getElementById('WWWAppliPassword').disabled=true;}
		";
	}else{
		$script_1="
		if(document.getElementById('WWWAppliUser')){document.getElementById('WWWAppliUser').disabled=false;}
		if(document.getElementById('WWWAppliPassword')){document.getElementById('WWWAppliPassword').disabled=false;}
		";
	}
	
	if($h->noneeduser_mysql["$serv"]){
		$script_2="
		if(document.getElementById('WWWMysqlUser')){document.getElementById('WWWMysqlUser').disabled=true;}
		if(document.getElementById('WWWMysqlPassword')){document.getElementById('WWWMysqlPassword').disabled=true;}
		";
	}else{
		$script_2="
		if(document.getElementById('WWWMysqlUser')){document.getElementById('WWWMysqlUser').disabled=false;}
		if(document.getElementById('WWWMysqlPassword')){document.getElementById('WWWMysqlPassword').disabled=false;}
		";
	}	
	
$html="<table style='width:100%'>
<tr>
<td valign='top'><img src='img/{$h->IMG_ARRAY_128["$serv"]}'></td>
<td valign='top'>
		<H2 style='margin-bottom:0px'>{{$h->TEXT_ARRAY[$serv]["TITLE"]}}</H2><hr>
		<p style='font-size:12px'>{{$h->TEXT_ARRAY[$serv]["TEXT"]}}</p>
		<hr>
</td>
</tr>
</table>
		";
	
	$tpl=new templates();
	
	echo "<div style='padding:3px;border:1px dotted #CCCCCC;margin-top:5px'>
		".$tpl->_ENGINE_parse_body($html,'domains.manage.org.index.php')."
	</div>
	<script>
	$script_1
	$script_2
	</script>";
}

function add_web_service(){
	$ou=$_GET["ou"];
	$ServerWWWType=$_GET['ServerWWWType'];
	$servername=$_GET["servername"];
	$domain=$_GET["domain"];
	$IP=$_GET["IP"];
	$tpl=new templates();
	$noneed_mysql=false;
	$noneed_appliPass=false;
	
	if($_GET["host"]==null){
		if($servername==null){echo $tpl->_ENGINE_parse_body("{server_name}=null");exit;}
		if($IP==null){echo $tpl->_ENGINE_parse_body("{address}=null");exit;}    
		if($domain==null){echo $tpl->_ENGINE_parse_body("{domain}=null");exit;}
		if($ServerWWWType==null){echo $tpl->_ENGINE_parse_body("{service_type}=null");exit;}
	}
	
	
	
	$noneed_mysql=false;
	
	if($ou==null){echo $tpl->_ENGINE_parse_body("{organization}=null");exit;}
	$vhosts=new vhosts($_GET["ou"]);
	$vvhosts=new vhosts();
	$noneeduser=$vvhosts->noneeduser;
	$noneeduser_mysql=$vvhosts->noneeduser_mysql;
	
	
	if($noneeduser[$ServerWWWType]){$noneed_appliPass=true;}
	if($noneeduser_mysql[$ServerWWWType]){$noneed_mysql=true;}

	if(!$noneed_mysql){
		if($_GET["WWWMysqlUser"]==null){
			echo $tpl->_ENGINE_parse_body("\"$ServerWWWType\":\n{WWWMysqlUser}=null\n$noneed_mysql\nL.".__LINE__);
			exit;
		}
		if($_GET["WWWMysqlPassword"]==null){
			echo $tpl->_ENGINE_parse_body("$ServerWWWType:{WWWMysqlPassword}=null");exit;
			}
	}
	
	if(!$noneeduser["$ServerWWWType"]){
		if($_GET["WWWAppliUser"]==null){
			echo $tpl->_ENGINE_parse_body("$ServerWWWType:\n{WWWAppliUser}=null\n{$vhosts->noneeduser["$ServerWWWType"]}");
		exit;}
	}
	
	if(!$noneed_appliPass){
		if($_GET["WWWAppliPassword"]==null){echo $tpl->_ENGINE_parse_body("{WWWAppliPassword}=null");exit;}
	}
	

	if($_GET["host"]==null){
		$hostname=$servername.".".$domain;
		$vhosts->apachedomainname=$domain;
		$vhosts->apacheIPAddress=$IP;		
	}else{
		$hostname=$_GET["host"];
	}
	
	$vhosts->ou=$ou;
	$vhosts->BuildRoot();
	$vhosts->WWWAppliPassword=$_GET["WWWAppliPassword"];
	$vhosts->WWWAppliUser=$_GET["WWWAppliUser"];
	$vhosts->WWWMysqlUser=$_GET["WWWMysqlUser"];
	$vhosts->WWWMysqlPassword=$_GET["WWWMysqlPassword"];
	$vhosts->WWWSSLMode=$_GET["WWWSSLMode"];
	if(isset($_GET["WWWEnableAddressBook"])){
		$vhosts->WWWEnableAddressBook=$_GET["WWWEnableAddressBook"];
	}
	writelogs("WWWMultiSMTPSender={$_GET["WWWMultiSMTPSender"]}",__FUNCTION__,__FILE__,__LINE__);
	
	$vhosts->WWWMultiSMTPSender=$_GET["WWWMultiSMTPSender"];
	$vhosts->Addhost($hostname,$ServerWWWType);
	}

function listOfAvailableServices(){
	$vhost=new vhosts();
	$tpl=new templates();
	$user=new usersMenus();
	$array=$vhost->listOfAvailableServices();
	return $tpl->_ENGINE_parse_body(Field_array_Hash($array,'ServerWWWType',null,"AddWWWServiceChange()"));
}


?>