<?php

$GLOBALS["VERBOSE"]=false;
$GLOBALS["NORELOAD"]=false;
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}

	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.cron.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.ldap.ou.inc');
	include_once('ressources/class.user.inc');
	include_once(dirname(__FILE__).'/framework/class.unix.inc');
	include_once(dirname(__FILE__).'/framework/frame.class.inc');	
	
	
if(is_array($argv)){
	if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
	if(preg_match("#--no-reload#",implode(" ",$argv))){$GLOBALS["NORELOAD"]=true;}
	if($GLOBALS["VERBOSE"]){ini_set('html_errors',0);ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);}
}
	


if($argv[1]=="--import"){import($argv[2]);die();}
if($argv[1]=="--schedules"){schedules();die();}
if($argv[1]=="--clear-all"){clear_all();die();}



function import($ID){
	
	$zimbra=new ldapOu($ID);
	$hash=$zimbra->searchUsers();
	$ou=$zimbra->ou;
	if($ou==null){echo "Destination: no such ou\n";return;}
	if(preg_match("#zimbraAccount#",$zimbra->QueryUsers)){
		$AsZimbra=true;
	}
	
	for($i=0;$i<$hash["count"];$i++){
		importZimbra($hash[$i],$ou);
	}
	
}

function clear_all(){
$unix=new unix();
	$files=$unix->DirFiles("/etc/cron.d");
	$cron=new cron_macros();
	$php5=$unix->LOCATE_PHP5_BIN();
	
	
	while (list ($index, $line) = each ($files) ){
		if($index==null){continue;}
		if(preg_match("#^LdapImport-#",$index)){
			echo "Deleting /etc/cron.d/$index\n";
			@unlink("/etc/cron.d/$index");
		}
	}

	$sql="TRUNCATE TABLE ldap_ou_import";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if($q->ok){
		echo "Empty table done...\n";
	}else{
		echo $q->mysql_error."\n";
	}
	
}

function schedules(){
	$unix=new unix();
	$files=$unix->DirFiles("/etc/cron.d");
	$cron=new cron_macros();
	$php5=$unix->LOCATE_PHP5_BIN();
	
	
	while (list ($index, $line) = each ($files) ){
		if($index==null){continue;}
		if(preg_match("#^LdapImport-#",$index)){
			@unlink("/etc/cron.d/$index");
		}
	}
	

	$sql="SELECT * FROM ldap_ou_import WHERE enabled=1";
	
	
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
 	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
 		if(trim($ligne["ScheduleMin"]==null)){continue;}
 		$schedule=$cron->cron_defined_macros[$ligne["ScheduleMin"]];
 		$f[]="PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/usr/X11R6/bin:/usr/share/artica-postfix/bin";
		$f[]="MAILTO=\"\"";
		$f[]="$schedule  root $php5 ".__FILE__." --import {$ligne["ID"]} >/dev/null 2>&1";
		$f[]="";
		@file_put_contents("/etc/cron.d/LdapImport-{$ligne["ID"]}",implode("\n",$f));
		@chmod("/etc/cron.d/LdapImport-{$ligne["ID"]}",600);
		unset($f);
 	}
	
}



function importZimbra($hash,$ou){
	$uid=$hash["uid"][0];
	$displayname=$hash["displayname"][0];
	$postalcode=$hash["postalcode"][0];
	$street=$hash["street"][0];
	$telephonenumber=$hash["telephonenumber"][0];
	$homephone=$hash["homephone"][0];
	$mobile=$hash["mobile"][0];
	$mail=$hash["mail"][0];
	$givenname=$hash["givenname"][0];
	$sn=$hash["sn"][0];
	$userpassword=$hash["userpassword"][0];
	$town=$hash["l"][0];
	if($hash["zimbramaildeliveryaddress"][0]<>null){
		$aliases[]=$hash["zimbramaildeliveryaddress"][0];
	}
	if($mail==null){return;}
	if(preg_match("#^admin[@\.]#",$mail)){return;}
	if(preg_match("#^wiki[@\.]#",$mail)){return;}
	if(preg_match("#^spam\.#",$mail)){return;}
	if(preg_match("#^ham\.#",$mail)){return;}
	
	if(count($hash["zimbramailalias"]["count"]>0)){
		for($i=0;$i<$hash["zimbramailalias"]["count"],$i++;){
			$aliases[]=$hash["zimbramailalias"][$i];
		}
		
	}
	
	if(preg_match("#(.+?)@(.+)#",$mail,$re)){$domain=$re[2];}
	$user=new user($uid);
	$user->ou=$ou;
	$user->mail=$mail;
	if($userpassword<>null){$user->password=$userpassword;}
	if($givenname<>null){$user->givenName=$givenname;}
	if($sn<>null){$user->sn=$sn;}
	if($street<>null){$user->street=$street;}
	if($displayname){$user->DisplayName=$displayname;}
	if($telephonenumber<>null){$user->telephoneNumber=$telephonenumber;}
	if($homephone<>null){$user->homePhone=$homephone;}
	if($mobile<>nul){$user->mobile=$mobile;}
	if($postalcode<>null){$user->postalCode=$postalcode;}
	if($town<>null){$user->town=$town;}
	$user->domainname=$domain;
	echo "Adding/updating $uid $mail in ou \"$ou\"\n";
	
	$user->add_user();
	
	
	if(is_array($aliases)){
		$user=new user($uid);
		while (list ($ip, $li) = each ($aliases) ){$user->add_alias($li);}
	}
}

?>