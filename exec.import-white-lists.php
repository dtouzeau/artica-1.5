<?php
include_once(dirname(__FILE__)."/framework/frame.class.inc");
if(posix_getuid()<>0){
	die("Cannot be used in web server mode\n\n");
}

echo "Starting importing white list....\n";
include_once(dirname(__FILE__) . '/class.cronldap.inc');

if(!is_file("/etc/artica-postfix/settings/Daemons/AutoLearnSentMailboxes")){die("unable to stat /etc/artica-postfix/settings/Daemons/AutoLearnSentMailboxes");}

echo "Init ldap connection...\n";
$ldap=new cronldap();
if(!$ldap->ldap_connected){die("Unable to connect to ldap server");}
$AutoLearnSentMailboxes=file_get_contents("/etc/artica-postfix/settings/Daemons/AutoLearnSentMailboxes");

$tbl=explode("\n",$AutoLearnSentMailboxes);
echo "Parsing ".count($tbl). " lines\n";

while (list ($num, $val) = each ($tbl) ){
	if(trim($val)==null){
		continue;
	}
	if(preg_match('#(.+?):(.+?)@(.+)#',$val,$re)){
		$whitelist=$re[1];
		$domain=$re[3];
		echo "Adding $whitelist to $domain\n";
		WhiteListsAddDomain($domain,$whitelist,$ldap);
		
	}
	
}


function WhiteListsAddDomain($domain,$whitelist,$ldap){
	if(!$ldap->ExistsDN("cn=wlbl,cn=artica,$ldap->suffix")){
		$upd["objectClass"][]='top';
		$upd["objectClass"][]="PostFixStructuralClass";
		$upd["cn"]="wlbl";
		$ldap->ldap_add("cn=wlbl,cn=artica,$ldap->suffix",$upd);
		unset($upd);
		}
		
	if(!$ldap->ExistsDN("cn=$domain,cn=wlbl,cn=artica,$ldap->suffix")){
		$upd["objectClass"][]='top';
		$upd["objectClass"][]='ArticaSettings';
		$upd["objectClass"][]="PostFixStructuralClass";
		$upd["cn"]="$domain";
		$ldap->ldap_add("cn=$domain,cn=wlbl,cn=artica,$ldap->suffix",$upd);
		unset($upd);		
	}
	
	
		$up["KasperkyASDatasAllow"]=$whitelist;
		$ldap->ldap_add_mod("cn=$domain,cn=wlbl,cn=artica,$ldap->suffix",$up);
	
}





?>