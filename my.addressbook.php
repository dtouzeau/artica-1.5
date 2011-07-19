<?php
include_once(dirname(__FILE__)."/ressources/class.user.inc");
include_once(dirname(__FILE__)."/ressources/class.templates.inc");

if(isset($_GET["contacts"])){contacts();exit;}
if(isset($_GET["members"])){members();exit;}
if(isset($_GET["contacts-list"])){contacts_list();exit;}
if(isset($_GET["members-list"])){members_list();exit;}


tabs();


function tabs(){
	
		$array["members"]="{my_collegues}";
		$array["contacts"]="{my_contacts}";
		$tpl=new templates();
		$page=CurrentPageName();
		
	while (list ($num, $ligne) = each ($array) ){
		
		
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_myaddressbook style='width:750px;height:520px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_myaddressbook').tabs();
		});
		

	LoadAjax('tool-map','miniadm.toolbox.php?script=". urlencode($page)."');
		
		
		</script>";	
	
	
}

function members(){
	$page=CurrentPageName();
	$tpl=new templates();	
		$html="
			<center>
			<table style='width:80%' class=form>
			<tr>
				<td class=legend>{members}:</td>
				<td>". Field_text("members-search",null,"font-size:14px",null,null,null,false,"MembersCheck(event)")."</td>
				<td>". button("{search}","MembersSearch()")."</td>
			</tr>
			</table>
		</center>
		
<div id='members-list' style='height:540px'></div>
	<script>
	function MembersCheck(e){
		if(checkEnter(e)){MembersSearch();}
	}
	
	function MembersSearch(){
			var se=escape(document.getElementById('members-search').value);
			LoadAjax('members-list','$page?members-list=yes&search='+se);
		}

		
	MembersSearch();			
	</script>";
		
echo $tpl->_ENGINE_parse_body($html);
	
	
}

function members_list(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$tofind=$_GET["search"];
	$ldap=new clladp();
	$ct=new user($_SESSION["uid"]);
	$dn="ou={$_SESSION["uid"]},ou=People,dc=$ct->ou,dc=NAB,$ldap->suffix";
	
	if($tofind==null){$tofind='*';}else{$tofind="*$tofind*";}
	$tofind=str_replace('***','*',$tofind);
	$tofind=str_replace('**','*',$tofind);	
	
	$filter="(&(objectClass=userAccount)(|(cn=$tofind)(mail=$tofind)(displayName=$tofind)(uid=$tofind) (givenname=$tofind) ))";
	$attrs=array("displayName","uid","mail","givenname","telephoneNumber","title","sn","mozillaSecondEmail","employeeNumber","sAMAccountName");
	$dn="ou=$ct->ou,dc=organizations,$ldap->suffix";
	
	
	$users=new usersMenus();
	if($users->RestrictNabToGroups){$groups=$ct->Groups_list();}
	
$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<th width=1%></th>
	<th width=99%>{contact}</th>
	<th width=1% nowrap>{phone}</th>
	<th width=1% nowrap>{mobile}</th>
	<th width=1% nowrap>{mail}</th>
	<th>&nbsp;</th>
</thead>
<tbody class='tbody'>";		

	
	$hash=$ldap->Ldap_search($dn,$filter,$attrs,20);
	$number=$hash["count"];
	for($i=0;$i<$number;$i++){
		$affiche=true;
		if(isset($groups)){
			$affiche=false;
			$userCRgroups=array();
			reset($groups);
			$userCR=new user($hash[$i]["uid"][0]);
			$userCRgroups=$userCR->Groups_list();
			while (list ($gidNumber, $groupname) = each ($groups) ){
				if($userCRgroups[$gidNumber]<>null){$affiche=true;break;}
			}
		}
		
		if(!$affiche){continue;}
		$array=$hash[$i];
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$html=$html."
		<tr class=$classtr>
		<td width=1%>". imgtootltip("contact-32.png","{edit}","Loadjs('contact.php?uidUser={$array["uid"][0]}')")."</td>
		<td style='font-size:13px'>{$array["displayname"][0]}&nbsp;</td>
		<td style='font-size:13px'>{$array["telephonenumber"][0]}&nbsp;</td>
		<td style='font-size:13px'>{$array["mobile"][0]}&nbsp;</td>
		<td style='font-size:13px'>{$array["mail"][0]}&nbsp;</td>
		</tr>
		";
		
		
	}
	
	$html=$html."</table>";
	echo $tpl->_ENGINE_parse_body($html);
	
	
}



function contacts(){
	$page=CurrentPageName();
	$tpl=new templates();
	$add=Paragraphe("my-address-book-user-add.png",'{add_new_contact}','{add_new_contact_text}',"javascript:Loadjs('contact.php')");
	
	$left=$add;
	
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top'>
			<center>
			<table style='width:80%' class=form>
			<tr>
				<td class=legend>{contacts}:</td>
				<td>". Field_text("contacts-search",null,"font-size:14px",null,null,null,false,"contactsCheck(event)")."</td>
				<td>". button("{search}","contactsSearch()")."</td>
			</tr>
			</table>
			</center>
			<hr>
			</td>
		<td valign='top'>$left</td>
		
		
	</tr>
	</table>
<div id='contacts-list' style='height:540px'></div>
	<script>
	function contactsCheck(e){
		if(checkEnter(e)){contactsSearch();}
	}
	
	function contactsSearch(){
			var se=escape(document.getElementById('contacts-search').value);
			LoadAjax('contacts-list','$page?contacts-list=yes&search='+se);
		}

		
	contactsSearch();			
	</script>
	
	
	";
	
	
	
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function contacts_list(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$tofind=$_GET["search"];
	$ldap=new clladp();
	$ct=new user($_SESSION["uid"]);
	$dn="ou={$_SESSION["uid"]},ou=People,dc=$ct->ou,dc=NAB,$ldap->suffix";
	
	if($tofind==null){$tofind='*';}else{$tofind="*$tofind*";}
	$tofind=str_replace('***','*',$tofind);
	$tofind=str_replace('**','*',$tofind);
	
$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<th width=1%></th>
	<th width=99%>{contact}</th>
	<th width=1% nowrap>{phone}</th>
	<th width=1% nowrap>{mobile}</th>
	<th width=1% nowrap>{mail}</th>
	<th>&nbsp;</th>
</thead>
<tbody class='tbody'>";		
	
	$filter="(&(objectClass=inetOrgPerson)(|(cn=$tofind)(mail=$tofind)(fileAs=$tofind)(displayName=$tofind)(sn=$tofind) ))";
	$attrs=array("displayName","uid","mail","givenname","mobile","telephoneNumber","title","sn","mozillaSecondEmail","employeeNumber","sAMAccountName");
	$hash=$ldap->Ldap_search($dn,$filter,$attrs,20);
	for($i=0;$i<$hash["count"];$i++){
		$array=$hash[$i];
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$html=$html."
		<tr class=$classtr>
		<td width=1%>". imgtootltip("contact-32.png","{edit}","Loadjs('contact.php?employeeNumber={$array["employeenumber"][0]}')")."</td>
		<td style='font-size:13px'>{$array["displayname"][0]}&nbsp;</td>
		<td style='font-size:13px'>{$array["telephonenumber"][0]}&nbsp;</td>
		<td style='font-size:13px'>{$array["mobile"][0]}&nbsp;</td>
		<td style='font-size:13px'>{$array["mail"][0]}&nbsp;</td>
		</tr>
		";
		
		
	}
	
	$html=$html."</table>";
	echo $tpl->_ENGINE_parse_body($html);
}


?>