<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
include_once('ressources/class.templates.inc');
include_once('ressources/class.mailboxes.inc');
include_once('ressources/kav4mailservers.inc');
include_once('ressources/class.cyrus.inc');
include_once('ressources/class.html.pages.inc');
include_once('ressources/class.cron.inc');
$tpl=new template_users();
$_GET["PRIVS"]=$tpl->_ParsePrivieleges($_SESSION["privileges"]["ArticaGroupPrivileges"]);

writequeries();
if(isset($_GET["delete_domain_confirm"])){delete_domain_confirm();exit;}
if(isset($_GET["add_domain_entity"])){add_domain_entity();exit;}
if(isset($_GET["ListDomainByEntity"])){echo List_domains($_GET["ListDomainByEntity"]);exit;}
if(isset($_GET["add_entity"])){add_entity();exit;}
if(isset($_GET["Tree_Internet_domain_delete_transport"])){Tree_Internet_domain_delete_transport();exit;}

if(isset($_GET["Tree_group_Add_New"])){Tree_group_Add_New();exit;}
if(isset($_GET["Tree_group_edit1"])){Tree_group_edit1();exit;}
if(isset($_GET["Tree_group_delete"])){Tree_group_delete();exit;}

if(isset($_GET["Tree_ou_Add_user"])){Tree_ou_Add_user();exit;}
if(isset($_GET["taskAdd"])){taskAdd();exit;}
if(isset($_GET["TreeSendTaskAdd"])){TreeSendTaskAdd();exit;}
if(isset($_GET["TreeTaskDelete"])){TreeTaskDelete();exit;}
if(isset($_GET["TreeApplyTasks"])){TreeApplyTasks();exit;}
if(isset($_GET["artica_service_start"])){artica_service_start();exit;}
if(isset($_GET["artica_service_stop"])){artica_service_stop();exit;}
if(isset($_GET["TreeFetchmailShowServer"])){TreeFetchmailShowServer();exit();}
if(isset($_GET["AutomaticConfig"])){TreeArticaSaveConfig();exit;}
if(isset($_GET["TreeFetchMailApplyConfig"])){TreeFetchMailApplyConfig();exit;}
if(isset($_GET["fetchmail_daemon_pool"])){TreeArticaSaveConfig();exit;}
if(isset($_GET["GetKavGroups"])){GetKavGroups();exit;}
if(isset($_GET["TreeAddNewOrganisation"])){TreeAddNewOrganisation();exit;}
if(isset($_GET["TreeDeleteOrganisation"])){TreeDeleteOrganisation();exit;}
if(isset($_GET["TreeSynchronyzeMailBoxes"])){TreeSynchronyzeMailBoxes();exit;}


if(isset($_GET["TreeOuLoadPageFindUser"])){TreeOuLoadPageFindUser();exit;}
if(isset($_GET["save_ldap_settings"])){save_ldap_settings();exit;}
if(isset($_GET["TreeKas3SaveSettings"])){TreeKas3SaveSettings();exit;}
if(isset($_GET["kas3UpdaterConfig"])){Treekas3UpdaterConfig();exit;}


$html="
<input type='hidden' id='text_add_entity' value=\"{text_add_entity}\">
<input type='hidden' id='text_add_domain_entity' value=\"{text_add_domain_entity}\">
<input type='hidden' id='text_delete' value=\"{delete}\">
	
<table width='700px' style='border-top:1px dotted #8e8785;'>
	<tr>
		<td width='295px' valign='top' style='border-right:1px dotted #8e8785;'>
			<div id='LdapTree' style='margin:5px;'></div>
		</td>
		<td width=50% valign='top' style='padding-left:5px'>
		<div id='rightInfos'></div>
		</td>
	</tr>
</table>



<script type=\"text/javascript\">

var keys = [{
\"key\" : 46,
\"func\" : TreeDelete}];



var LdapTreeStruct=[
	{
	'id' : 'Root',
	'txt' : 'Server',
	'img' : 'tree-server.gif',
	'onopenpopulate' : myOpenPopulate,
	'openlink' : 'ldapTree.php',
	'canhavechildren' : true	}
	];

var tree = null;
var ReturnedValue=null;	
" . TreeFunctions() . "
function TafelTreeInit () {
	tree = new TafelTree('LdapTree', LdapTreeStruct, {
	'generate' : true,
	'imgBase' : 'img/',
	'width' : '100%', // valeur par d�faut : 100%
	'height' : '650px', // valeur par d�faut : auto
	'openAtLoad' : false,
	'cookies' : false,
	'onEdit' : TreeEditGroup,
	'onClick' : TreeClick,
	'onDropAjax':[MyTreeDrop, \"tree.functions.php\"]
	});
	TafelTreeManager.setKeys(keys); 	
}
	
function myOpenPopulate (branch, response) {
return true;
}



function TreeDelete (tree, code, modifiers, ev) {
	var dltext=document.getElementById('text_delete').value;
	if(confirm(dltext+'?')){
		var XHR = new XHRConnection();
	 	var b = tree.getSelectedBranches(); 
	 	var branchId = b[0].getId(); 
	 	var previous =  b[0].getParent();
	 	if (previous) {
	 		XHR.appendData('PreviousBranch', previous.getId());
	 		}

		XHR.appendData('DeleteBranch', branchId);
		XHR.sendAndLoad('tree.functions.php', 'GET',x_TreeEditGroup);	
		if(ReturnedValue.length>0){ReloadBranch(ReturnedValue);return true;}
		alert('{failed}');
		return false;
		}
} 

function MyTreeDrop(branchMoved,newParent,response,dropFinished){
	if(response=='ok'){return true;}else{alert(response);return false;}
}



function ReloadBranch(id){
var branch = tree.getBranchById(id);
if(branch.removeChildren){
	branch.removeChildren();
	branch._openPopulate(); 
	}else{alert(id);}
}
	
function ReloadTree(){
	if (TafelTreeInit) {
		document.getElementById('LdapTree').innerHTML='';
		TafelTreeInit();
		}
		var selected = tree.getSelectedBranches();
			if (selected.length > 0) {
				for (var i = 0; i < selected.length; i++) {
					var branch = tree.getBranchById(selected[i].getId());
					branch.refreshChildren();				
					}
			}
		
	}	
</script>

";
$tpl=new templates();
$html=$tpl->_ENGINE_parse_body($html);
$tpl=new template_users('{TITLE_DOMAINS}',$html);
echo $tpl->web_page;

function TreeFunctions(){
	$html="
	var x_TreeEditGroup= function (obj) {
		ReturnedValue=obj.responseText;
	}	
	function TreeEditGroup(branch, newValue, oldValue) {
		var str = branch.getId() + \" : \" + newValue;
		var XHR = new XHRConnection();
		XHR.appendData('EditBranch', branch.getId());
		XHR.appendData('EditBranchValue', newValue);
		XHR.sendAndLoad('tree.functions.php', 'GET',x_TreeEditGroup);	
		return ReturnedValue;
		}
		
	function TreeClick(branch) {
		var str = branch.getId();
		var XHR = new XHRConnection();
		XHR.appendData('SelectBranch', branch.getId());
		XHR.setRefreshArea('rightInfos');
		XHR.sendAndLoad('tree.functions.php', 'GET');	
		return ReturnedValue;
		}
		
	function myBeforeOpen(branch, opened){
	var str = branch.getId();
		var XHR = new XHRConnection();
		XHR.appendData('SelectBranch', branch.getId());
		XHR.setRefreshArea('rightInfos');
		XHR.sendAndLoad('tree.functions.php', 'GET');	
		return true;
	}

			
	
	";
	return $html;
	
}


function List_domains($ou=null){
	if($ou==null){return null;}
	$ldap=new clladp();
	$hash=$ldap->hash_get_domains_ou($ou);
	$kav4mailservers=new kav4mailservers();
	if(!is_array($hash)){return null;}
	
	$html="
	<fieldset>
	<legend>{ARRAY_TITLE}</legend>
	<table style='width:95%' align='center'>
	<tr class=rowT>
		
		<td colspan=2>&nbsp;</TD>
		<td nowrap>{ARRAY_TITLE_TRANSPORT}</TD>
		<td nowrap>{ARRAY_TITLE_USERS}</TD>
		<td nowrap>{ARRAY_TITLE_AV}</TD>
		<td>&nbsp;</TD>
	
	</TR>
	";
	
	while (list ($num, $ligne) = each ($hash) ){
		if($class='rowA'){$class='rowB';}else{$class="rowA";}
		$users_numbers=0;
		$users_numbers=$ldap->HashGetUsersAccounts($ou,$ligne);
		$redirect=$ldap->GetTransportTable($ligne);
		if($redirect==null){$redirect="local/dns";}
		if($kav4mailservers->IsDomainLicenced($ligne)==true){
			$kav="<img src='img/status_ok.jpg'>";
			}else{$kav="<img src='img/status_permission.gif'>";}
		
			$html=$html . "<tr class=$class>
		<td  align='center' valign='top' width=1%>
			<img src='img/upload.gif'>
		</td>
		<td valign='top' style='font-size:13px;letter-spacing:3px'>
			<a href='domains.edit.php?domain=$ligne&ou=$ou' style='font-size:13px;' onMouseOver=\"javascript:AffBulle('{js_expand_domain}');\" OnMouseOut=\"javascript:HideBulle();\" >$ligne</a>
		</td>
		<td width=1% align='center'>$redirect</td>
		<td width=1% align='center'>" . count($users_numbers) ."</td>
		<td width=1% align='center'>$kav</td>
		<td width=1%  valign='top'><a href=\"javascript:DelDomain('{$ligne["domain"]}','');\"><img src='img/deluser.gif' onMouseOver=\"javascript:AffBulle('{js_del_domain}');\" OnMouseOut=\"javascript:HideBulle();\"></a></td>		
		</tr>";
		
		
	}
	$tpl=new Templates();
	$html=$tpl->_parse_body($html);
	return $html . "</table></fieldset>";
}
function delete_domain_confirm(){
	writelogs("Receive " . $_GET["delete_domain_confirm"],__FUNCTION__,__FILE__);
	$domain=$_GET["delete_domain_confirm"];
	$mailb=new MailBoxes();
	$mailb->Delete_entire_domain($domain);
	echo List_domains();
	}

	
	
function List_entities($echo=0){
	$ldap=new clladp();
	$hash=$ldap->hash_get_ou();
	if(!is_array($hash)){return null;}
	$html="<center'>
	<fieldset><legend>{entities}</legend><table>
	<tr class=rowT>
	<td>&nbsp;</td>
	<td>{entities}</td>	
	<td>{nb_domains}</td>	
	<td>&nbsp;</td>
	</tr>
	
	";
	
	
	while (list ($num, $val) = each ($hash) ){
		if($class=='rowB'){$class='rowB';}else{$class='rowB';}
		$count_domains=0;
		$count_domains=count($ldap->hash_get_domains_ou($val));
		$html=$html . 
		"<tr class='$class'>
			<td width=1%><img src='img/ou.png'></td>
			<td ><a href=\"javascript:ListDomainByEntity('$val');\"  style='font-size:13px;letter-spacing:3px'>$val</td>
			<td ><span style='font-size:13px;letter-spacing:3px'>$count_domains</td>			
			<td><input type='button' OnClick=\"javascript:AddDomainByEntity('$val');\" value='{js_add_domain}&nbsp;&raquo;'></td>
		</tr>";
		}
		
	$html= $html . "</table></fieldset>";
	
	if($echo==1){
		$tpl=new templates();
		$html=$tpl->_parse_body($html);
		echo $html;exit;}
	return $html;
	
}

function add_entity(){
	$entity=$_GET["add_entity"];
	$ldap=new clladp();
	$ldap->AddGroup($entity);
	echo List_entities(1);
	}
	
function add_domain_entity(){
	$domain=$_GET["add_domain_entity"];
	$ou=$_GET["entity_name"];
	$ldap=new clladp();
	$ldap->AddDomainEntity($ou,$domain);
	echo List_entities(1);
	
}




function Tree_Internet_domain_delete_transport(){
	$domain=$_GET["Tree_Internet_domain_delete_transport"];
	$ou=$_GET["ou"];
	$ldap=new clladp();
	$pages=new HtmlPages();
	$dn="cn=$domain,ou=$ou,dc=organizations,$ldap->suffix";
	$ldap->ldap_delete($dn,True);
	echo $pages->PageTransport($domain,$ou);
}



function Tree_group_Add_New(){
	$group=$_GET["Tree_group_Add_New"];
	$group=replace_accents($group);
	$ou=$_GET["ou"];
	$ldap=new clladp();
	$dn="cn=$group,ou=$ou,dc=organizations,$ldap->suffix";
	$update_array["cn"][0]="$group";
	$update_array["gidNumber"][0]=$ldap->_GenerateGUID();
	$update_array["description"][0]="New posix group";
	$update_array["objectClass"][]='posixGroup';
	$update_array["objectClass"][]='ArticaSettings';
	$update_array["objectClass"][]='top';
	if($ldap->ldap_add($dn,$update_array)==false){
			echo  nl2br(
			"Error: Adding {$update_array["gidNumber"][0]} gid 
			cn=New Group\n".
			$ldap->ldap_last_error);

		}
	$pages=new HtmlPages();
	echo $pages->PageOu($dn);
}
	




function Tree_group_edit1(){
	$gid=$_GET["Tree_group_edit1"];
	$_GET["group_name"]=replace_accents($_GET["group_name"]);
	$ldap=new clladp();
	$hash=$ldap->GroupDatas($gid);
	$dn=$hash["dn"];
	if($hash["cn"]<>$_GET["group_name"]){
		$ldap->ldap_group_rename($dn,"cn={$_GET["group_name"]}");
		$hash=$ldap->GroupDatas($gid);
		$dn=$hash["dn"];}
	$update_array["description"][0]=$_GET["description"];
	$ldap->Ldap_modify($dn,$update_array);
	$pages=new HtmlPages();
	echo $pages->PageGroup($gid);
	}
	
function Tree_group_delete(){
	$gid=$_GET["Tree_group_delete"];
	$ldap=new clladp();
	$hash=$ldap->GroupDatas($gid);
	$ldap->ldap_delete($hash["dn"]);
	$pages=new HtmlPages();
	echo $pages->PageOu($hash["dn_ou"]);
	
	
}



function Tree_ou_Add_user(){
	$user=$_GET["Tree_ou_Add_user"];
	$user=replace_accents($user);
	$ldap=new clladp();
	$ou=$_GET["ou"];
	$uid=str_replace(' ','.',$user);

	if(stripos($user,'@')>0){
		$mail=$user;
		$tbl=explode('@',$user);
		$domainName=$tbl[1];
		$user=$tbl[0];
		$uid=str_replace(' ','.',$user);
		
		
		if(preg_match('#([a-z0-9]+)([\.\-_])([a-z0-9_\-\.]+)#',$user,$reg)){
			$firstname=$reg[1];
			$lastname=$reg[3];	
			
			}
		elseif (preg_match('#(.+)\s+(.+)#',$user,$reg)){
			$firstname=$reg[1];
			$lastname=$reg[2];
			
			}
		elseif (preg_match('#(.+)#',$user,$reg)){
			$lastname=$reg[1];
			$firstname=$lastname;
			
			}
	}
	
	else{
		if(preg_match('#([a-z0-9_\-]+)\s+([a-z0-9_\-]+)#',$user,$reg)){
			$lastname=$reg[2];
			$firstname=$reg[1];
			$domainName='none';
			}else{
				$lastname=$user;
				$firstname=$user;
				$domainName='none';
			}	
	}
	
	$dn="cn=$user,ou=users,ou=$ou,dc=organizations,$ldap->suffix";

	$update_array["cn"][]=$user;
	$update_array["uid"][]=$uid;
	$update_array["sn"][]=$lastname;
	$update_array["domainName"][]=$domainName;

	$update_array["homeDirectory"][]="/home/$firstname.$lastname";
	$update_array["accountGroup"][]="0";
	$update_array["accountActive"][]='TRUE';
	$update_array["mailDir"]='cyrus';
	$update_array["objectClass"][]="userAccount";
	$update_array["objectClass"][]="top";	

	
	
	$ldap->ldap_add($dn,$update_array);
	if($ldap->ldap_last_error<>null){
		echo nl2br($ldap->ldap_last_error);
		exit();
	}
	$update_array=null;
	
	$update_array["givenName"][]=$firstname;
	$update_array["mail"][]=$mail;
	$update_array["DisplayName"][]="$firstname "  . $lastname;
	$update_array["MailBoxActive"][]="FALSE";
	$update_array["objectclass"][]="ArticaSettings";
	
	$ldap->Ldap_add_mod($dn,$update_array);
	$pages=new HtmlPages();
	echo $pages->PageOu("ou=$ou,dc=organizations,$ldap->suffix");
	}
	
function taskAdd(){
	$taskname=$_GET["taskAdd"];
	$pages=new HtmlPages();
	echo $pages->PageTaskAdd($taskname);
	
}

function TreeSendTaskAdd(){
	$taskname=$_GET["TreeSendTaskAdd"];
	$taskID=$_GET["taskID"];
	$cron=new cron();
	$cron->ldap_AddTask($taskID,$taskname);
	$pages=new HtmlPages();
	echo $pages->PageTasks();
}
function TreeTaskDelete(){
	$id=$_GET["TreeTaskDelete"];
	$cron=new cron();
	$cron->Cron_delete($id);
	$pages=new HtmlPages();
	echo $pages->PageTasks();	
	
}
function TreeApplyTasks(){
	$cron=new cron();
	$html=$cron->SockApplyCrons();
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}
function artica_service_start(){
	$sock=new sockets();
	$sock->getfile('start_service:' . $_GET["artica_service_start"]);
	
}
function artica_service_stop(){
	$sock=new sockets();
	$sock->getfile('stop_service:' . $_GET["artica_service_stop"]);
	}
function TreeFetchmailShowServer(){
	$pages=new HtmlPages();
	echo $pages->PageFetchMailShowServerInfos($_GET["TreeFetchmailShowServer"]);
	
}
function TreeArticaSaveConfig(){
	$usr=new usersMenus();
	$tpl=new templates();
	if($usr->AsArticaAdministrator==false){echo $tpl->_ENGINE_parse_body('{no_privileges}');exit;}		
	include_once('ressources/class.artica.inc');
	$artica=new artica();
	while (list ($num, $val) = each ($_GET) ){
		$artica->array_config["ARTICA"][$num]=$val;
	}
	$artica->save();
	echo "ok";
	
}
function save_ldap_settings(){
	$usr=new usersMenus();
	$tpl=new templates();
	if($usr->AsArticaAdministrator==false){echo $tpl->_ENGINE_parse_body('{no_privileges}');exit;}	
	include_once('ressources/class.artica.inc');
	$artica=new artica();
	while (list ($num, $val) = each ($_GET) ){
		$artica->array_ldap_config["LDAP"][$num]=$val;
	}
	$artica->saveLDapSettings();
	echo "ok";	
	
	
}
function TreeFetchMailApplyConfig(){
	$usr=new usersMenus();
	$tpl=new templates();
	if($usr->AsMailBoxAdministrator==false){echo $tpl->_ENGINE_parse_body('{no_privileges}');exit;}
	include_once('ressources/class.fetchmail.inc');
	$fecth=new fetchmail();
	echo $fecth->Save();
	}
function GetKavGroups(){
	$kas=new kav4mailservers(1);
	
}
function TreeAddNewOrganisation(){
	$usr=new usersMenus();
	$tpl=new templates();
	if($usr->AsArticaAdministrator==false){echo $tpl->_ENGINE_parse_body('{no_privileges}');exit;}
	$ou=$_GET["TreeAddNewOrganisation"];
	if($ou=="_Global"){echo "Reserved!";exit;}
	$ldap=new clladp();
	$ldap->AddOrganization($ou);
	if($ldap->ldap_last_error<>null){
		if($ldap->ldap_last_error_num<>68){
			echo $ldap->ldap_last_error;
			exit;
		}
	}
	
	$ldap->ldap_close();
	REMOVE_CACHED("domains.index.php");
	$sock=new sockets();
	$sock->getFrameWork("status.php?force-front-end=yes");
	
	
}
function TreeDeleteOrganisation(){
	$usr=new usersMenus();
	$tpl=new templates();
	$ou=$_GET["TreeDeleteOrganisation"];
	if($usr->AsArticaAdministrator==false){echo $tpl->_ENGINE_parse_body('{no_privileges}');exit;}
	$ldap=new clladp();
	$ldap->ldap_delete("ou=$ou,dc=organizations,$ldap->suffix",true);
	if($ldap->ldap_last_error<>null){echo $ldap->ldap_last_error;exit;}
	echo $tpl->_ENGINE_parse_body( "$ou {deleted}");
}
function TreeSynchronyzeMailBoxes(){
	$usr=new usersMenus();
	$tpl=new templates();
	if($usr->AsMailBoxAdministrator==false){echo $tpl->_ENGINE_parse_body('{no_privileges}');exit;}
	include_once("ressources/class.cyrus.inc");
	$cyrus=new cyrus();
	echo $tpl->_ENGINE_parse_body($cyrus->SynchronizeMailBoxes());
	}

function TreeOuLoadPageFindUser(){
	$pages=new HtmlPages();
	echo $pages->PageOuFindUser($_GET["TreeOuLoadPageFindUser"],$_GET["TreeOuFindUser"]);
}

function TreeKas3SaveSettings(){
	include_once('ressources/class.kas-filter.inc');
	unset($_GET["TreeKas3SaveSettings"]);
	$kas=new kas_filter();
	if($kas->error==true){$html="{error_no_socks}" ;}
	else{
		
		while (list ($num, $val) = each ($_GET) ){$kas->array_datas[$num]=$val;}
 		if($kas->SaveFile()){$html="{success}";}else{$html="{failed}";}
	}
	unset($_GET);
	$tpl=new templates();
	echo html_entity_decode($tpl->_ENGINE_parse_body($html));	
	
}
function Treekas3UpdaterConfig(){
	include_once('ressources/class.kas-filter.inc');
	unset($_GET["kas3UpdaterConfig"]);
	unset($_GET["PRIVS"]);
	$kas=new kasUpdater();
	if($kas->error==true){$html="{error_no_socks}" ;}
	else{
		while (list ($num, $val) = each ($_GET) ){$kas->array_updater_data["updater.options"][$num]=$val;}
		if($kas->SaveFile()){$html="{success}";}else{$html="{failed}";}	
	}
unset($_GET);
	$tpl=new templates();
	echo html_entity_decode($tpl->_ENGINE_parse_body($html));		
	
}




?>