<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.main_cf.inc');
	
$usersmenus=new usersMenus();
if($usersmenus->AsMailBoxAdministrator==false){header('location:users.index.php');exit;}

INDEX();


function INDEX(){
	
	$main=new main_cf();
	
	$html="<p style='font-wight:bold;font-size:12px'>{audit_text_1}</p>
	<table style='width:100%'>
	<tr>
	<td valign='top'><H3>mydestination=</H3></td>
	<td><code>localhost, localhost.\$mydomain, \$myhostname," . LDAP_SEARCH_QUERY(null,'(&(objectclass=organizationalUnit)(associatedDomain=*))','associatedDomain') ."</code></td>
	<tr>
	<td valign='top'><H3>relay_domains=</H3></td>
	<td><code>" . LDAP_SEARCH_QUERY('cn=relay_domains,cn=artica','(&(objectclass=PostFixRelayDomains)(cn=*))','cn') ."</code></td>
	</tr>
	<tr>
	<td valign='top'><H3>relay_recipient_maps=</H3></td>
	<td><code>" . LDAP_SEARCH_QUERY('cn=relay_recipient_maps,cn=artica','(&(objectclass=PostfixRelayRecipientMaps)(cn=*))','cn') ."</code></td>
	</tr>	
	<tr>
	<td valign='top'><H3>transport_maps=</H3></td>
	<td><code>" . LDAP_SEARCH_2QUERY('','(&(objectClass=transportTable)(cn=*))','cn','transport') ."</code></td>
	</tr>		
	
	
	</table>
	
	
	";
	
$tpl=new template_users('{audit_main_domains}',$html);
echo $tpl->web_page;

	
}


function LDAP_SEARCH_QUERY($dn=null,$query,$tofind){
	$ldap=new clladp();
	if($dn<>null){$dn=$dn.','.$ldap->suffix;}else{$dn=$ldap->suffix;}
	
	$hash=$ldap->Ldap_search($dn,$query,array($tofind));
	
	for($z=0;$z<$hash["count"];$z++){
	
	
	for($i=0;$i<$hash[$z][strtolower($tofind)]["count"];$i++){
		$res=$res.$hash[$z][strtolower($tofind)][$i].", ";
		
	}
	}
return $res;
	
	
	
}
function LDAP_SEARCH_2QUERY($dn=null,$query,$key,$tofind){
	$ldap=new clladp();
	if($dn<>null){$dn=$dn.','.$ldap->suffix;}else{$dn=$ldap->suffix;}
	
	$hash=$ldap->Ldap_search($dn,$query,array($tofind,$key));
	
	for($z=0;$z<$hash["count"];$z++){
		
	$value=$hash[$z][$key][0];
	
	for($i=0;$i<$hash[$z][strtolower($tofind)]["count"];$i++){
		$res=$res."$value=".$hash[$z][strtolower($tofind)][$i]."<br> ";
		
	}
	}
return $res;
	
	
	
}