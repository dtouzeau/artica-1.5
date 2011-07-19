<?php

include_once(dirname(__FILE__).'/framework/frame.class.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include(dirname(__FILE__).'/ressources/class.ldap.inc');

	
	if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
	if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
	
if($GLOBALS["VERBOSE"]){echo "O:{$argv[0]} 1:{$argv[1]} 2:{$argv[2]}\n";}
	
$file=base64_decode($argv[1]);
$GLOBALS["OU"]=base64_decode($argv[2]);
$ou=$GLOBALS["OU"];

if($GLOBALS["VERBOSE"]){echo "file:$file ou:$ou\n";}


if(!is_file($file)){
	events("$file, no such file",100);
	die();
}


$tbl=@explode("\n",@file_get_contents($file));
$ldap=new clladp();
$hashdoms=$ldap->hash_get_all_domains();

$dn="cn=relay_domains,ou=$ou,dc=organizations,$ldap->suffix";
if(!$ldap->ExistsDN($dn)){
		$upd['cn'][0]="relay_domains";
		$upd['objectClass'][0]='PostFixStructuralClass';
		$upd['objectClass'][1]='top';
		if(!$ldap->ldap_add($dn,$upd)){events("$dn: $ldap->ldap_last_error",100);die();}
		unset($upd);		
		}
		
	$dn="cn=relay_recipient_maps,ou=$ou,dc=organizations,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd['cn'][0]="relay_recipient_maps";
		$upd['objectClass'][0]='PostFixStructuralClass';
		$upd['objectClass'][1]='top';
		if(!$ldap->ldap_add($dn,$upd)){events("$dn: $ldap->ldap_last_error",100);die();}
		unset($upd);		
		}	

	$dn="cn=transport_map,ou=$ou,dc=organizations,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd['cn'][0]="transport_map";
		$upd['objectClass'][0]='PostFixStructuralClass';
		$upd['objectClass'][1]='top';
		if(!$ldap->ldap_add($dn,$upd)){events("$dn: $ldap->ldap_last_error",100);die();}
		unset($upd);		
		}		
	

$max=count($tbl);
while (list ($num, $line) = each ($tbl) ){
	$array=explode(";",$line);
	$count=$count+1;
	$domain=$array[0];
	$domain_name=$domain;
	$domain_ip=$array[1];
	$pourcent=($count/$max);
	$pourcent=$pourcent*100;
	$pourcent=round($pourcent);
	if($domain_name==null){
		if($GLOBALS["VERBOSE"]){echo "$line no domain\n";}
		continue;
	}
	if($GLOBALS[__FILE__]["SMTPDOMS"][$domain_name]){continue;}
	
	if($hashdoms[$domain]<>null){
		events("<b>$domain</b>: {error_domain_exists}",$pourcent);
		continue;
	}
	
	unset($upd);
	$dn="cn=$domain_name,cn=relay_domains,ou=$ou,dc=organizations,$ldap->suffix";	
	$upd['cn'][0]="$domain_name";
	$upd['objectClass'][0]='PostFixRelayDomains';
	$upd['objectClass'][1]='top';
	if(!$ldap->ldap_add($dn,$upd)){
		events("<b>$domain</b>: relay_domains:: $ldap->ldap_last_error",$pourcent);
		continue;	
	}
	
	
	
	unset($upd);
	$dn="cn=@$domain_name,cn=relay_recipient_maps,ou=$ou,dc=organizations,$ldap->suffix";
	$upd['cn'][0]="@$domain_name";
	$upd['objectClass'][0]='PostfixRelayRecipientMaps';
	$upd['objectClass'][1]='top';
	if(!$ldap->ldap_add($dn,$upd)){
		events("<b>$domain</b>: relay_recipient_maps:: $ldap->ldap_last_error",$pourcent);
		continue;	
	}
	
	if(preg_match("#(.+?):([0-9]+)#",$domain_ip,$re)){
		$domain_ip=$re[1];
		$relayPort=$re[2];
	}
	if($relayPort==null){$relayPort=25;}
	$relayIP="[$domain_ip]";
	$maps_table="relay:$relayIP:$relayPort";
	
	
	unset($upd);
	$dn="cn=$domain_name,cn=transport_map,ou=$ou,dc=organizations,$ldap->suffix";
	$upd['cn'][0]="$domain_name";
	$upd['objectClass'][0]='transportTable';
	$upd['objectClass'][1]='top';
	$upd["transport"][]=$maps_table;
	if(!$ldap->ldap_add($dn,$upd)){
		events("<b>$domain</b>: transport_map:: $ldap->ldap_last_error",$pourcent);
		continue;	
	}	
	events("<b>$domain</b>: {success}",$pourcent);
	$success=true;
	$GLOBALS[__FILE__]["SMTPDOMS"][$domain_name]=true;

}

events("{import_smtp_domains}:<strong>".count($GLOBALS[__FILE__]["SMTPDOMS"])."</strong> domains",100);
if($success){
	$sock=new sockets();
	$usr=new usersMenus();
	$sock->getFrameWork("cmd.php?postfix-transport-maps=yes");
	
	
	if($usr->cyrus_imapd_installed){
		$sock->getFrameWork("cmd.php?cyrus-check-cyr-accounts=yes");
	}
			
	
	if($usr->AMAVIS_INSTALLED){
		$sock->getFrameWork("cmd.php?amavis-restart=yes");
	}		
}



function events($text,$pourcent){
	$GLOBALS[md5(__FILE__)]["POURC"]=$pourcent;
	$GLOBALS[md5(__FILE__)]["EVENTS"][]=date('H:i:s')." $text";
	$datas=base64_encode(serialize($GLOBALS[md5(__FILE__)]));
	echo $text."\n";
	@file_put_contents("/var/log/artica-postfix/domains.import.{$GLOBALS["OU"]}.log",$datas);
	
	
}


	
?>