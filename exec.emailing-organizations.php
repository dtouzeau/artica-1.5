<?php
if(posix_getuid()<>0){
	die("Cannot be used in web server mode\n\n");
}



	include_once(dirname(__FILE__) . '/ressources/class.users.menus.inc');
	include_once(dirname(__FILE__) . '/ressources/class.artica.inc');
	include_once(dirname(__FILE__) . '/ressources/class.ldap.inc');
	include_once(dirname(__FILE__) . '/class.cronldap.inc');

	
if($argv[1]<>null){
	$ou=$argv[1];	
}
	
	$sock=new sockets();
	if(preg_match('#<from>(.+?)</from><subject>(.+?)</subject><body>(.+?)</body>#is',$sock->GET_INFO("eMailingForAllOrg$ou"),$re)){
		$from=$re[1];
		$subject=$re[2];
		$body=$re[3];
		$conf=explode("\n",$body);
		$body=null;
		while (list($num,$val)=each($conf)){
			if(trim($val)==null){continue;}
			$body.=$val."\n";
		}
	}else{
		die();
	}

	
if(trim($from==null)){die();}
if(trim($subject==null)){die();}
if(trim($body==null)){die();}

$ldap=new clladp();
$ld =$ldap->ldap_connection;
$bind =$ldap->ldapbind;
$suffix=$ldap->suffix;	
$arr=array("mail","displayname");

if($ou<>null){$prefix="ou=$ou,";}

$sr = @ldap_search($ld,"{$prefix}dc=organizations,$suffix",'(objectclass=userAccount)',$arr);
if ($sr) {
	$hash=ldap_get_entries($ld,$sr);
	for($i=0;$i<$hash["count"];$i++){
		$mail=$hash[$i]["mail"][0];
		$displayname=$hash[$i]["displayname"][0];
		SendMailNotifWithSendMail($body,$subject,$from,$mail);
		
	}
}
		





	
?>