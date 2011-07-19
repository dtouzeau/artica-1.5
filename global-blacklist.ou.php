<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
$user=new usersMenus();
if($user->AllowEditOuSecurity==false){header('location:users.index.php');}	
if(isset($_GET["ImportArticaDomainsTable"])){ImportArticaDomainsTable();}
if(isset($_GET["DeleteGlobalBlack"])){DeleteGlobalBlack();exit;}
if(isset($_GET["add_domain"])){add_domain();exit;}
INDEX();


function INDEX(){
	
	$page=CurrentPageName();
	
	if(isset($_GET["find"])){$table=SearchTable($_GET["find"]);}else{
	$table=DomainsTable($_GET["ou"]);	
	}


	
	$html="
	<input type='hidden' id='SearchString' value='{$_GET["find"]}'>
	<center>
	<table style='width:100%'>
	<tr>
		<td>
			<form name='FFMQ'>
			<input type='hidden' name='ImportArticaDomainsTable' value='{$_GET["ou"]}'>
			<input type='button' value='&laquo;&nbsp;{import_black_domain_table}&nbsp;&raquo;' OnClick=\"javascript:MyHref('$page?ou={$_GET["ou"]}&ImportArticaDomainsTable={$_GET["ou"]}');\">
			</form>
		</td>
		<td>
		
		<table>
			<tr>
			<td align='right' nowrap><strong>{add_b_dom}:</strong></td>
			<input type='hidden' value='{$_GET["ou"]}' id='ou' name='ou' >
			<td>" . Field_text('add_domain',null,null,null,null,null,null,"BTAddBlackDomain(event);") . "</td>
			<td><input type='button' value='{add}&nbsp;&raquo;' OnClick=\"javascript:AddBlackDomain();\"></td>
			</tr>
		</table>
		
		</td>
		
		
	</tr>
	<div style='margin:5px'>
	<form name='FFMQ1'>
	<input type='hidden' name='ou' id='ou' value='{$_GET["ou"]}'>
	<table style='width:100%'>
	<tr>
	<td align='right'><strong>{search}</strong></td>
	<td>" . Field_text('find',$_GET["find"]) . "</td>
	<td><input type='submit' value='&laquo;&nbsp;{search}&nbsp;&raquo;'></td>
	</tr>
	</table>
	</form>
	$table
	
	
	";
	
	
$cfg["JS"][]="js/quarantine.ou.js";
$tpl=new template_users('{global_blacklist}',$html,0,0,0,0,$cfg);
echo $tpl->web_page;	
	
	
}

function DomainsTable($ou){
	$ldap=new clladp();
	$hash=$ldap->Hash_get_ou_blacklisted_domains($_GET["ou"]);
	$page_requested=$_GET["page"];
	if($page_requested==null){$page_requested=1;}
	
	
	if(!is_array($hash)){return null;}
		$count=count($hash);
		$pages=intval($count/30);
		if($page_requested==1){
			$start=0;
			$stop=30;
		}else{
			$start=$page_requested*30;
			$stop=$start+30;
			}
		
		
		
	$tabs=BuildTables($pages,$page_requested,$ou);	
	$html="
	<input type='hidden' id='page_requested' value='$page_requested'>
	<br>
	<table style='width:90%'>
	<tr style='background-color:#005447'>
	<td style='color:white' colspan=2><strong>{global_blacklist} &nbsp;&laquo;$count {rows}  $page_requested/$pages {pages}&nbsp;&raquo;</strong></td>
	</tr>
	<tr><td colspan=2>$tabs</td></tr>
	";
	

	
	
	for($i=$start;$i<$stop;$i++){
		if($hash[$i]<>null){
		$html=$html . "<tr><td class=bottom><strong>{$hash[$i]}</strong></td>
		<td width=1% class=bottom>" . imgtootltip('x.gif','{delete}',"DeleteGlobalBlack('{$hash[$i]}')")."</td>
		</tr>";
		}
		$count=$count+1;

	}
	
	$html=$html . "</table>";
	return $html;
	
}

function BuildTables($MaxPages,$currentpage,$ou){
$groupPage=$_GET["grouppage"];
if($groupPage==0){$groupPage=1;}
	
	
$page=CurrentPageName();	
if($groupPage+10<$MaxPages){
	$nextpage=($groupPage*10);
	$nextGroup=$groupPage+1;
	$end="<li><a href='$page?page=$nextpage&ou=$ou&grouppage=$nextGroup' $id>$i&raquo;&raquo;</a></li>";
	$max=$groupPage*10;
	$startGroupPage=$max-10;
	$group_page_moins=$groupPage-1;
	if($group_page_moins>0){
		$return_page=$group_page_moins*10;
		$debut="
		<li><a href='$page?page=$return_page&ou=$ou&grouppage=$group_page_moins' $id>&laquo;&laquo;</a></li>";
	}
	
}
	
else{$max=$MaxPages;}
for($i=$startGroupPage;$i<$max;$i++){
	if($currentpage==$i){$id="id='tab_current'";}else{$id=null;}
	$list=$list."<li><a href='$page?page=$i&ou=$ou&grouppage=$groupPage' $id>$i</a></li>\n";
	
}
$html="<input type='hidden' value='grouppage' id='grouppage' value='$groupPage'><div id=tablist>$debut$list$end</div>";
return $html;
	
}

function ImportArticaDomainsTable(){
	$ou=$_GET["ImportArticaDomainsTable"];
	$tpl=new templates();
	if(!file_exists('ressources/databases/blackdomainsList.db')){echo $tpl->_ENGINE_parse_body('{failed}');return null;}
	$datas=file_get_contents('ressources/databases/blackdomainsList.db');
	$datas=explode("\n",$datas);
	$ldap=new clladp();
	$dn="cn=blackListedDomains,ou=$ou,dc=organizations,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
				$update_array["cn"][]='blackListedDomains';
				$update_array["objectClass"][]='PostFixStructuralClass';
				$update_array["objectClass"][]='top';
				$this->ldap_add($dn,$update_array);
		}	

	
	
	if(!is_array($datas)){echo $tpl->_ENGINE_parse_body('{failed}');return null;}
	
	while (list ($num, $ligne) = each ($datas) ){
		if($ligne<>null){
			if($already[strtolower($ligne)]==null){
				$ARR[strtolower($ligne)]=strtolower($ligne);
			}
			
		}
		
	}
	unset($datas);
	$datas=null;
	$hash=null;
	if(!is_array($ARR)){
		echo $tpl->_ENGINE_parse_body('{success}');
		return null;
	}
	while (list ($num, $ligne) = each ($ARR) ){
			AddAdomain($ou,$ligne);
		}
	
	//print_r($upd);
	

	if(!$ldap->Ldap_add_mod($dn,$upd)){
	echo $tpl->_ENGINE_parse_body("{failed} ". count($ARR) . " {rows}\n$ldap->ldap_last_error");return null;}
	
	echo $tpl->_ENGINE_parse_body('{success}');

}

function AddAdomain($ou,$domain){
	$ldap=new clladp();
	$dn="cn=$domain,cn=blackListedDomains,ou=$ou,dc=organizations,$ldap->suffix";
	if($ldap->ExistsDN($dn)){
		return true;
	}
	
		$update_array["cn"][]=$domain;
		$update_array["objectClass"][]='DomainsBlackListOu';
		$update_array["objectClass"][]='top';
		if(!$ldap->ldap_add($dn,$update_array)){return false;}
	
	
}

function SearchTable($find){
	
	

		$ldap=new clladp();
		$hash=$ldap->Hash_get_ou_blacklisted_domains($_GET["ou"],$find);
		if(!is_array($hash)){
			return null;
		}
		
$page=CurrentPageName();
if($breaked==true){$error="<span style='color:red'>{search_break}</span>";}
$count=count($hash);
$html="
	<br>
	<table style='width:90%'>
	<tr style='background-color:#005447'>
	<td colspan=2 style='color:white'><strong>{global_blacklist} &nbsp;&laquo;{search} $count {rows}  &nbsp;&raquo;</strong></td>
	</tr>
	<tr>
	<td>$error</td>
	<td><input type='button' value='&laquo;&nbsp;{go_back}' OnClick=\"javascript:MyHref('$page?ou={$_GET["ou"]}');\"></td>
	
	</tr>";	
while (list ($num, $ligne) = each ($hash) ){
	$html=$html . "<tr>
		<td class=bottom><strong>$ligne</strong></td>
		<td class=bottom>" . imgtootltip('x.gif','{delete}',"DeleteGlobalBlack('$ligne')")."</td>
		</tr>";
	}
	$html=$html . "</table>";
	return $html;
}
function DeleteGlobalBlack(){
	$num=$_GET["DeleteGlobalBlack"];
	$ou=$_GET["ou"];
	$ldap=new clladp();
	$tpl=new templates();
	$dn="cn=$domain,cn=blackListedDomains,ou=$ou,dc=organizations,$ldap->suffix";
	
	if(!$ldap->ldap_delete($dn,true)){echo $tpl->_ENGINE_parse_body("{failed}\n".$ldap->ldap_last_error);}
	else{echo $tpl->_ENGINE_parse_body('{success}');}
	}
function add_domain(){
	$domain=$_GET["add_domain"];
	$ou=$_GET["ou"];
	$tpl=new templates();
	
	$ldap=new clladp();
	
	$dn="cn=$domain,cn=blackListedDomains,ou=$ou,dc=organizations,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$update_array["cn"][]=$domain;
		$update_array["objectClass"][]='DomainsBlackListOu';
		$update_array["objectClass"][]='top';
		if(!$ldap->ldap_add($dn,$update_array)){
		echo $tpl->_ENGINE_parse_body("$domain -> {failed}\n$ldap->ldap_last_error");
		}else{echo $tpl->_ENGINE_parse_body('{success}');}
	}else{echo $tpl->_ENGINE_parse_body('{success}');}
		
	
}

