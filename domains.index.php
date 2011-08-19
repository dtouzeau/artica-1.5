<?php
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
	
function js(){
	if(GET_CACHED(__FILE__,__FUNCTION__,"js")){return;}
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{organizations}");
	$page=CurrentPageName();
	$html="
	var timeout=0;
	
	function LoadOrg(){
			
		$('#BodyContent').load('$page?js-pop=yes', function() {Orgfillpage();});
		//LoadWinORG('760','$page?js-pop=yes','$title');
		//setTimeout('Orgfillpage()',900);
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

function popup(){
	
//if(GET_CACHED(__FILE__,__FUNCTION__,__FUNCTION__)){return;}

	$sock=new sockets();
	$EnableManageUsersTroughActiveDirectory=$sock->GET_INFO("EnableManageUsersTroughActiveDirectory");
	if(!is_numeric($EnableManageUsersTroughActiveDirectory)){$EnableManageUsersTroughActiveDirectory=0;}	

	
	if($EnableManageUsersTroughActiveDirectory==1){
		$ldap=new ldapAD();
		$usersnumber=$ldap->COUNT_DE_USERS();
	}else{
		$ldap=new clladp();	
		$usersnumber=$ldap->COUNT_DE_USERS();
		$ldap->ldap_close();
	}
	
	
	
	$tpl=new templates();
	$users=$tpl->_ENGINE_parse_body("<i>{this_server_store}:&nbsp;<strong>$usersnumber</strong>&nbsp;{users}</i>");

$page=CurrentPageName();	
$html="
	<div style='background-image:url(/img/home-bg-256.png);background-repeat:no-repeat;background-position:top right'><div style='font-size:18px'>{my_organizations}</div>
		<input type='hidden' name='add_new_organisation_text' id='add_new_organisation_text' value='{add_new_organisation_text}'>
		<div class=explain>
				{about_organization}
				<div style='font-size:14px;text-align:right;padding-top:5px;padding-right:40px'><span id='countdeusers'>$users</span></div>
		</div>
		<center>
		<table style='width:80%' class=form>
		<tbody>
		<tr>
			<td class=legend>{organization}:</td>
			<td>". Field_text("organization-find",$_COOKIE["FINDMYOU"],"font-size:16px",null,null,null,false,"SearchOrgsCheck(event)")."</td>
			<td width=1%>". button("{search}","SearchOrgs()")."</td>
		</tr>
		</tbody>
		</table>
		</center>
		<div id='orgs' style='width:750px;height:450px;overflow:auto;margin-top:10px'></div>
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
			return $tpl->_ENGINE_parse_body(
			Paragraphe('folder-addorg-64-grey.png','{add_new_organisation}',
			'{ARTICA_META_BLOCKED}',null,null,220,100));
		}
	}


	
	if($usersmenus->AsArticaAdministrator==true){

	$html=Paragraphe('folder-addorg-64.png','{add_new_organisation}',
	'{add_new_organisation_explain}','javascript:TreeAddNewOrganisation();',null,220,100);}
	
	return $tpl->_ENGINE_parse_body($html);
	echo $tpl->_ENGINE_parse_body($html);		
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

	
	$html="
	$header
	<div style='width:700px;height:550px'>$error";

	if(isset($_GET["ajaxmenu"])){$ajax=true;}
	
	$html=$html."<div style='float:left'>".butadm()."</div>";
	
	$search=str_replace(".", "\.", $search);
	$search=str_replace("*", ".*?", $search);
	
	
	while (list ($num, $ligne) = each ($hash) ){
		if(!preg_match("#$search#i", $ligne)){
			writelogs("'$ligne' NO MATCH $search",__FUNCTION__,__FILE__,__LINE__);
			continue;}
		$md=md5($ligne);
		$uri="javascript:Loadjs('domains.manage.org.index.php?js=yes&ou=$ligne');";
		if($ajax){
			$uri="javascript:Loadjs('$page?LoadOrgPopup=$ligne');";
		}
		
		if($EnableManageUsersTroughActiveDirectory==0){
			$img=$ldap->get_organization_picture($ligne,64);
		}else{
			$img="folder-org-64.png";
			$usersNB=$ldap->CountDeUSerOu($ligne);
			$usersNB="$usersNB {users}";
		}
		

		$html=$html . "<div style='float:left'>" . Paragraphe($img,"{manage} $ligne","
		<strong>$ligne:$usersNB<br></strong>{manage_organisations_text}",$uri,null,220,100) . "</div>
		";
		
	}
	
	if($AllowInternetUsersCreateOrg==1){
		$sql="SELECT * FROM register_orgs WHERE sended=0 ORDER BY ou";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
		
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
			$uri="javascript:Loadjs('domains.organizations.parameters.php?ou-sql-js={$ligne["zmd5"]}')";
			$name=$ligne["ou"];
			$time=$ligne["register_date"];
			$html=$html . "<div style='float:left'>" . Paragraphe("img/org-warning-64.png","$name","
			<strong>$time:<br></strong>{waiting}",$uri,null,220,100) . "</div>
			";
			
		}
	}
	
	
	
	
	if($users->POSTFIX_INSTALLED){
		$sendmail="<div style='float:left'>".Buildicon64('DEF_ICO_SENDTOALL',220,100)."</div>";
		
	}
	
	if(isset($_GET["ajaxmenu"])){
		$html=$html."
		<div style='float:left'>" .butadm()."</div>";
		
		
	}
	
	if($users->AsArticaAdministrator){
		$parameters="<div style='float:left'>".Paragraphe("parameters2-64.png","{organizations_parameters}","
		{organizations_parameters_text}","javascript:Loadjs('domains.organizations.parameters.php')",null,220,100)."</div>";
	
	}
	
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


	