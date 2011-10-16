<?php
	$GLOBALS["ICON_FAMILY"]="organizations";
	if(isset($_GET["verbose"])){$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.main_cf.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.active.directory.inc');
	
	

	if(isset($_GET["ShowOrganizations"])){echo ShowOrganizations();exit;}
	if(isset($_GET["ajaxmenu"])){echo "<div id='orgs'>".ShowOrganizations()."</div>";exit;}
	if(isset($_GET["butadm"])){echo butadm();exit;}
	if(isset($_GET["LoadOrgPopup"])){echo LoadOrgPopup();exit;}
	if(isset($_GET["js"])){js();exit;}
	if(isset($_GET["js-pop"])){popup();exit;}
	if(isset($_GET["countdeusers"])){COUNT_DE_USERS();exit;}
	if(isset($_GET["inside-tab"])){popup_inside_tabs();exit;}
	
function js(){
	if(GET_CACHED(__FILE__,__FUNCTION__,"js")){return;}
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{organizations}");
	$page=CurrentPageName();
	$html="
	var timeout=0;
	
	function LoadOrg(){
		$('#BodyContent').load('$page?js-pop=yes');
	}
	
	function OrgfillpageButton(){
	var content=document.getElementById('orgs').innerHTML;
	if(content.length<90){
		setTimeout('OrgfillpageButton()',900);
		return;
	}
	
	LoadAjax('butadm','$page?butadm=yes');
	
	}
	
	LoadOrg();
	";
	
	SET_CACHED(__FILE__,__FUNCTION__,"js",$html);
	echo $html;
	
}


function popup_inside_tabs(){
	$page=CurrentPageName();
	$html="<div id='BodyContentInsideTabs'></div>
	
	<script>
	function LoadOrg2(){
		$('#BodyContentInsideTabs').load('$page?js-pop=yes');
	}
		
	function OrgfillpageButton(){
	var content=document.getElementById('orgs').innerHTML;
	if(content.length<90){
		setTimeout('OrgfillpageButton()',900);
		return;
	}
	
	LoadAjax('butadm','$page?butadm=yes');
	
	}
	
	LoadOrg2();	
	</script>
	";
	echo $html;
	
}

function popup(){
	
//if(GET_CACHED(__FILE__,__FUNCTION__,__FUNCTION__)){return;}

	$sock=new sockets();
	$EnableManageUsersTroughActiveDirectory=$sock->GET_INFO("EnableManageUsersTroughActiveDirectory");
	if(!is_numeric($EnableManageUsersTroughActiveDirectory)){$EnableManageUsersTroughActiveDirectory=0;}	
	$users=new usersMenus();
	
	if($EnableManageUsersTroughActiveDirectory==1){
		$ldap=new ldapAD();
		$usersnumber=$ldap->COUNT_DE_USERS();
	}else{
		$ldap=new clladp();	
		$usersnumber=$ldap->COUNT_DE_USERS();
		$ldap->ldap_close();
	}
	
	if($users->AsArticaAdministrator){
		$parameters="<div style='float:left'>".Paragraphe("parameters2-64.png","{organizations_parameters}","
		{organizations_parameters_text}","javascript:Loadjs('domains.organizations.parameters.php')",null,220,100)."</div>";
	
	}	
	
	$tpl=new templates();
	$Totalusers=$tpl->_ENGINE_parse_body("<i>{this_server_store}:&nbsp;<strong>$usersnumber</strong>&nbsp;{users}</i>");

$page=CurrentPageName();	
$html="
	<div>
		<div style='font-size:18px'>{my_organizations}</div>
		<input type='hidden' name='add_new_organisation_text' id='add_new_organisation_text' value='{add_new_organisation_text}'>
		<table style='width:100%'>
		<tbody>
		<tr>
		<td valign='top' width=1%>$parameters</td>
		<td valign='top' width=99%>
			<div class=explain>
				{about_organization}
				<div style='font-size:14px;text-align:right;padding-top:5px;padding-right:40px'><span id='countdeusers'>$Totalusers</span></div>
			</div>
				<table style='width:100%' class=form>
				<tbody>
					<tr>
						<td class=legend>{organization}:</td>
						<td>". Field_text("organization-find",$_COOKIE["FINDMYOU"],"font-size:16px",null,null,null,false,"SearchOrgsCheck(event)")."</td>
						<td width=1%>". button("{search}","SearchOrgs()")."</td>
					</tr>
				</tbody>
				</table>
		</td>
		</tr>
		</tbody>
		</table>
		<center>

		</center>
		<div id='orgs' style='width:100%;height:450px;overflow:auto;margin-top:10px'></div>
	</div>
	
	<script>
		function SearchOrgs(){
			var se=escape(document.getElementById('organization-find').value);
			Set_Cookie('FINDMYOU', document.getElementById('organization-find').value, '3600', '/', '', '');
			LoadAjax('orgs','$page?ShowOrganizations=yes&search='+se);
			}
			
		function SearchOrgsCheck(e){
			if(checkEnter(e)){SearchOrgs();}
		}
		SearchOrgs();
	</script>
";	

$tpl=new templates();
$html=$tpl->_ENGINE_parse_body($html);
SET_CACHED(__FILE__,__FUNCTION__,__FUNCTION__,$html);
echo $html;
}

function ShowOrganizations(){
	$usersmenus=new usersMenus();
	if($usersmenus->AsArticaAdministrator==true){$orgs=ORGANISATIONS_LIST();}else{
		if($usersmenus->AllowAddGroup==true && $usersmenus->AsArticaAdministrator==false){$orgs=ORGANISTATION_FROM_USER();}
	}
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($orgs);

	
}

function butadm(){
	$usersmenus=new usersMenus();
	$tpl=new templates();
	$sock=new sockets();
	
	if($usersmenus->EnableManageUsersTroughActiveDirectory){return null;}	
	
	if($usersmenus->ARTICA_META_ENABLED){
		if($sock->GET_INFO("AllowArticaMetaAddUsers")<>1){
			Paragraphe('folder-addorg-64-grey.png','{add_new_organisation}','{ARTICA_META_BLOCKED}',null,null,220,100);
			return $tpl->_ENGINE_parse_body(imgtootltip("plus-24-grey.png","{add_new_organisation}<i>{ARTICA_META_BLOCKED}</i>"));
		}
	}


	
	if($usersmenus->AsArticaAdministrator==true){
		Paragraphe('folder-addorg-64.png','{add_new_organisation}','{add_new_organisation_explain}','javascript:TreeAddNewOrganisation();',null,220,100);
		$html=imgtootltip("plus-24.png","<b>{add_new_organisation}</b><br><i>{add_new_organisation_explain}</i>","TreeAddNewOrganisation()");
		return $tpl->_ENGINE_parse_body($html);
		
	}
	return $tpl->_ENGINE_parse_body(imgtootltip("plus-24-grey.png","{add_new_organisation}<i>{ARTICA_META_BLOCKED}</i>"));
}


function ORGANISTATION_FROM_USER(){
	$ldap=new clladp();
	$hash=$ldap->Hash_Get_ou_from_users($_SESSION["uid"],1);
	$ldap->ldap_close();
	if(!is_array($hash)){return null;}
	return Paragraphe('folder-org-64.jpg',"{manage} &laquo;{$hash[0]}&raquo;","<strong>{$hash[0]}:<br></strong>{manage_organisations_text}",'domains.manage.org.index.php?ou='.$hash[0])."	
	<script>
	OrgfillpageButton();
	</script>";
	
	
}

function ORGANISATIONS_LIST(){
	
	$page=CurrentPageName();
	$search=$_GET["search"];
	if($search==null){$search="*";}
	if(strpos("  $search", "*")==0){$search=$search."*";}
	$users=new usersMenus();
	$sock=new sockets();
	$EnableManageUsersTroughActiveDirectory=$sock->GET_INFO("EnableManageUsersTroughActiveDirectory");
	if(!is_numeric($EnableManageUsersTroughActiveDirectory)){$EnableManageUsersTroughActiveDirectory=0;}	
	$AllowInternetUsersCreateOrg=$sock->GET_INFO("AllowInternetUsersCreateOrg");
	if($EnableManageUsersTroughActiveDirectory==1){
		$ldap = new ldapAD();
		$hash=$ldap->hash_get_ou(true);
		
	}else{
		$ldap=new clladp();
		$hash=$ldap->hash_get_ou(true);
	}
	if(!is_array($hash)){return null;}
	ksort($hash);
	
	if($EnableManageUsersTroughActiveDirectory==0){
		if(!$ldap->BuildOrganizationBranch()){
			$error="<div style='float:left'>".Paragraphe("danger64.png","{GENERIC_LDAP_ERROR}",$ldap->ldap_last_error)."</div>";
		}
	}

	
$page=CurrentPageName();
		


if(isset($_GET["ajaxmenu"])){$header="
	<input type='hidden' name='add_new_organisation_text' id='add_new_organisation_text' value='{add_new_organisation_text}'>
	<input type='hidden' name='ajaxmenu' id='ajaxmenu' value='yes'>";
	}

	$add=butadm();
	$html="
	$header
	$error<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>$add</th>
		<th>{organizations}</th>";

		if($users->ZARAFA_INSTALLED){
			if($users->AsMailBoxAdministrator){
				$html=$html."<th>Zarafa</th>";
			}
		}	
$html=$html."<th colspan=2>{users}</th>
		<th colspan=2>{groups}</th>
		<th>{domains}</th>
		<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";

	if(isset($_GET["ajaxmenu"])){$ajax=true;}
	$pic="32-environement.png";
	$search=str_replace(".", "\.", $search);
	$search=str_replace("*", ".*?", $search);
	$style="style='font-size:16px;'";
	
	while (list ($num, $ligne) = each ($hash) ){
		$ou=$ligne;
		$ou_encoded=base64_encode($ou);
		if(!preg_match("#$search#i", $ligne)){writelogs("'$ligne' NO MATCH $search",__FUNCTION__,__FILE__,__LINE__);continue;}
		$md=md5($ligne);
		$uri="javascript:Loadjs('domains.manage.org.index.php?js=yes&ou=$ligne');";
		if($ajax){$uri="javascript:Loadjs('$page?LoadOrgPopup=$ligne');";}
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		Paragraphe($img,"{manage} $ligne","<strong>$ligne:$usersNB<br></strong>{manage_organisations_text}",$uri,null);

		if($EnableManageUsersTroughActiveDirectory==0){
			$img=$ldap->get_organization_picture($ligne,32);
			$usersNB=$ldap->CountDeUSerOu($ligne);
			$usersNB="$usersNB";			
		}else{
			$img=$pic;
			$usersNB=$ldap->CountDeUSerOu($ligne);
			$usersNB="$usersNB";
		}
		
		$delete=imgtootltip("delete-32-grey.png","<b>{delete_ou} $ligne</b><br><i>{delete_ou_text}</i>");	
		if($users->AsArticaAdministrator){
			$delete=Paragraphe('64-cancel.png',"{delete_ou} $ligne",'{delete_ou_text}',"javascript:Loadjs('domains.delete.org.php?ou=$ligne');",null,210,100,0,true);
			$delete=imgtootltip("delete-32.png","<b>{delete_ou} $ligne</b><br><i>{delete_ou_text}</i>","javascript:Loadjs('domains.delete.org.php?ou=$ligne');");
		
		}
		
	if($users->ZARAFA_INSTALLED){
	if($users->AsMailBoxAdministrator){
		Paragraphe("zarafa-logo-64.png","$ou:{APP_ZARAFA}","{ZARAFA_OU_ICON_TEXT}","javascript:Loadjs('domains.edit.zarafa.php?ou=$ou_encoded')",null,210,100,0,true);
		$info=$ldap->OUDatas($ou);
		$zarafaEnabled="zarafa-logo-32.png";
		if(!$info["objectClass"]["zarafa-company"]){$zarafaEnabled="zarafa-logo-32-grey.png";}	
		$zarafa="<td width=1% $style nowrap align='center'><strong style='font-size:16px'>". imgtootltip($zarafaEnabled,"<b>$ou:{APP_ZARAFA}</b><br>{ZARAFA_OU_ICON_TEXT}","Loadjs('domains.edit.zarafa.php?ou=$ou_encoded')")."</td>";
		
		}
	}		
		
		
		$DomainsNB=$ldap->CountDeDomainsOU($ligne);
		$GroupsNB=$ldap->CountDeGroups($ou);
		Paragraphe('folder-useradd-64.png','{create_user}','{create_user_text}',"javascript:Loadjs('domains.add.user.php?ou=$ou')",null,210,null,0,true);
		Paragraphe('64-folder-group-add.png','{create_user}','{create_user_text}',"javascript:Loadjs('domains.add.user.php?ou=$ou')",null,210,null,0,true);
		Paragraphe("64-folder-group-add.png","$ou:{add_group}","{add_a_new_group_in_this_org}:<b>$ou</b>","javascript:Loadjs('domains.edit.group.php?popup-add-group=yes&ou=$ou')");
		
		
		$select=imgtootltip($img,"{manage_organisations_text}",$uri);
		$adduser=imgtootltip("folder-useradd-32.png","<b>{create_user}</b><br><i>{create_user_text}</i>","Loadjs('domains.add.user.php?ou=$ou_encoded&encoded=yes');");
		$addgroup=imgtootltip("32-folder-group-add.png","<b>{add_group}</b><br><i>{add_a_new_group_in_this_org}</i>","Loadjs('domains.edit.group.php?popup-add-group=yes&ou=$ou');");
		
				
				
		
		$html=$html."
		<tr class=$classtr>
			<td width=1% $style nowrap>$select</td>
			<td width=99% $style nowrap><a href=\"javascript:blur();\" OnClick=\"$uri\" style='font-size:18px;font-weight:bolder;text-transform:capitalize;text-decoration:underline'>$ligne</strong></a></td>
			$zarafa
			<td width=1% $style nowrap align='center'><strong style='font-size:16px'>$usersNB</strong></td>
			<td width=1% $style nowrap align='center'><strong style='font-size:16px'>$adduser</strong></td>
			<td width=1% $style nowrap align='center'><strong style='font-size:16px'>$GroupsNB</strong></td>
			<td width=1% $style nowrap align='center'><strong style='font-size:16px'>$addgroup</strong></td>
			<td width=1% $style nowrap align='center'><strong style='font-size:16px'>$DomainsNB</strong></td>
			<td width=1%>$delete</td>
		</tr>";		


		
	}
	
$pic="32-environement.png";
	
	if($AllowInternetUsersCreateOrg==1){
		$sql="SELECT * FROM register_orgs WHERE sended=0 ORDER BY ou";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
		
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$uri="javascript:Loadjs('domains.organizations.parameters.php?ou-sql-js={$ligne["zmd5"]}')";
			Paragraphe("img/org-warning-64.png","$name","<strong>$time:<br></strong>{waiting}",$uri);
			$name=$ligne["ou"];
			$time=$ligne["register_date"];
			$html=$html."
		<tr class=$classtr>
			<td width=1% $style nowrap>$select</td>
			<td width=99% $style nowrap><strong style='font-size:16px'>$ligne</strong></td>
			<td width=1% $style nowrap><strong style='font-size:16px'>-</strong></td>
			<td width=1%>$delete</td>
		</tr>";	
			
		}
	}
	
	
	$html=$html."</tbody></table>";
	
	if($users->POSTFIX_INSTALLED){Buildicon64('DEF_ICO_SENDTOALL',220,100);}
	
	if($EnableManageUsersTroughActiveDirectory==0){$ldap->ldap_close();}
	return "$parameters".$html."$sendmail</div>
	<script>
	OrgfillpageButton();
	</script>
	
	";
}

function LoadOrgPopup(){
	
	echo "
	Loadjs('js/artica_organizations.js');
	Loadjs('js/artica_domains.js');
	YahooWin(750,'domains.manage.org.index.php?org_section=0&SwitchOrgTabs={$_COOKIE["SwitchOrgTabs"]}&ou={$_GET["LoadOrgPopup"]}&ajaxmenu=yes','ORG::{$_GET["LoadOrgPopup"]}');
	
	";
	
	
	
	
	
}


	