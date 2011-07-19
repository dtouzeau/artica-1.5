<?php
include_once("ressources/class.ldap.inc");


$ldap=new clladp();


	
		$update_array["objectClass"][]="userAccount";
		$update_array["objectClass"][]="top";
		$update_array["objectClass"][]="ArticaSettings";
		
		$update_array["displayName"][]="Bonjour david touzeau";
		$update_array["homeDirectory"][]='/home/Bonjour david touzeau';
		$update_array["mailDir"][]="cyrus";
		$update_array["givenName"][]="Bonjour david touzeau";
		$update_array["accountGroup"][]=0;
		$update_array["accountActive"][]='TRUE';
		$update_array["MailboxActive"][]='TRUE';
		$update_array["cn"][]="Bonjour david touzeau";
		$update_array["sn"][]="Bonjour david touzeau";
		$update_array["uid"][]="bonjour.david.touzeau";
		//$update_array["mail"][]="tooo@ooo.fr";
		$update_array["userPassword"][]="aaaaaaa";
		//$update_array["domainName"][]="ooo.fr";
		$ldap->ldap_add("cn=Bonjour david touzeau,ou=klf.fr,$ldap->suffix",$update_array);
		echo $ldap->ldap_last_error;
		
			
		

?>

