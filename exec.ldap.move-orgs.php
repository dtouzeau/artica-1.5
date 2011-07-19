<?php
	include_once(dirname(__FILE__).'/ressources/class.templates.inc');
	include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
	include_once(dirname(__FILE__).'/ressources/class.users.menus.inc');
	include_once(dirname(__FILE__).'/ressources/class.ccurl.inc');
	include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
	include_once(dirname(__FILE__).'/framework/class.unix.inc');
	include_once(dirname(__FILE__)."/framework/frame.class.inc");	
	
if(posix_getuid()<>0){
	die("Cannot be used in web server mode\n\n");
}
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["DEBUG"]=true;}
if($argv[1]=='--upload'){
	$_GET["OU"]=$argv[2];
	$_GET["SESSION"]=$argv[3];
	upload();exit;
}

if($argv[1]=="--upload-users"){
	$_GET["OU"]=$argv[2];
	$_GET["SESSION"]=$argv[3];	
	uploadUsers();exit;
}



$ou=$argv[2];
$newou=$argv[1];
$ldap=new clladp();
$unix=new unix();
$slapcat=$unix->find_program("slapcat");
$ldapadd=$unix->find_program("ldapadd");

if(!is_file($slapcat)){echo "No slapcat !!\n";return;}
if(!is_file($ldapadd)){echo "No ldapadd !!\n";return;}
if($newou==null){echo "No destination\n";return;}
if($ou==null){echo "No org source\n";return;}

@mkdir("/usr/share/artica-postfix/ressources/ldap-back/$ou",null,true);
$tmpfile="/usr/share/artica-postfix/ressources/ldap-back/$ou/$ou.ldif";

$dn="ou=$ou,dc=organizations,$ldap->suffix";
ExportDN($ou,$newou,$dn);
$ldap->ldap_delete($dn,true);

$dn="cn=$ou,cn=catch-all,cn=artica,$ldap->suffix";
if($ldap->ExistsDN($dn)){
	ExportDN($ou,$newou,$dn);
	$ldap->ldap_delete($dn,true);
}

$dn="cn=$ou,cn=PostfixRobots,cn=artica,$ldap->suffix";
if($ldap->ExistsDN($dn)){
	ExportDN($ou,$newou,$dn);
	$ldap->ldap_delete($dn,true);
}
$dn="dc=$ou,dc=NAB,$ldap->suffix";
if($ldap->ExistsDN($dn)){
	ExportDN($ou,$newou,$dn);
	$ldap->ldap_delete($dn,true);
}

$dn="ou=$newou,dc=organizations,$ldap->suffix";
if(!$ldap->ExistsDN($dn)){
	echo "Move $ou to $newou \"ou=$newou,dc=organizations,$ldap->suffix\" no such branch: FAILED\n";
	return;	
}

echo "Moved old $ou data has been saved into the server\n";
$q=new mysql();
$sql="UPDATE postfix_multi SET ou='$newou' WHERE ou='$ou'";
echo "$sql\n";
$q->QUERY_SQL($sql,"artica_backup");
if(!$q->ok){echo $q->mysql_error."\n";}


$sql="UPDATE emailing_campain_linker SET ou='$newou' WHERE ou='$ou'";
echo "$sql\n";
$q->QUERY_SQL($sql,"artica_backup");
if(!$q->ok){echo $q->mysql_error."\n";}

$sql="UPDATE emailing_campain_queues SET ou='$newou' WHERE ou='$ou'";
echo "$sql\n";
$q->QUERY_SQL($sql,"artica_backup");
if(!$q->ok){echo $q->mysql_error."\n";}

$sql="UPDATE emailing_db_paths SET ou='$newou' WHERE ou='$ou'";
echo "$sql\n";
$q->QUERY_SQL($sql,"artica_backup");
if(!$q->ok){echo $q->mysql_error."\n";}

$sql="UPDATE emailing_mailers SET ou='$newou' WHERE ou='$ou'";
echo "$sql\n";
$q->QUERY_SQL($sql,"artica_backup");
if(!$q->ok){echo $q->mysql_error."\n";}

$sql="UPDATE emailing_templates SET ou='$newou' WHERE ou='$ou'";
echo "$sql\n";
$q->QUERY_SQL($sql,"artica_backup");
if(!$q->ok){echo $q->mysql_error."\n";}

$sql="UPDATE emailing_campain_queues SET ou='$newou' WHERE ou='$ou'";
echo "$sql\n";
$q->QUERY_SQL($sql,"artica_backup");
if(!$q->ok){echo $q->mysql_error."\n";}

$sql="UPDATE emailing_campain_imap SET ou='$newou' WHERE ou='$ou'";
echo "$sql\n";
$q->QUERY_SQL($sql,"artica_backup");
if(!$q->ok){echo $q->mysql_error."\n";}

$sql="UPDATE smtp_generic_maps SET ou='$newou' WHERE ou='$ou'";
echo "$sql\n";
$q->QUERY_SQL($sql,"artica_backup");
if(!$q->ok){echo $q->mysql_error."\n";}

$sql="UPDATE smtp_attachments_blocking SET ou='$newou' WHERE ou='$ou'";
echo "$sql\n";
$q->QUERY_SQL($sql,"artica_backup");
if(!$q->ok){echo $q->mysql_error."\n";}

$sql="UPDATE reports SET ou='$newou' WHERE ou='$ou'";
echo "$sql\n";
$q->QUERY_SQL($sql,"artica_backup");
if(!$q->ok){echo $q->mysql_error."\n";}

$sql="UPDATE mbx_migr_users SET ou='$newou' WHERE ou='$ou'";
echo "$sql\n";
$q->QUERY_SQL($sql,"artica_backup");
if(!$q->ok){echo $q->mysql_error."\n";}

$sql="UPDATE mbx_migr SET ou='$newou' WHERE ou='$ou'";
echo "$sql\n";
$q->QUERY_SQL($sql,"artica_backup");
if(!$q->ok){echo $q->mysql_error."\n";}

$sql="UPDATE quarantine SET organization='$newou' WHERE organization='$ou'";
echo "$sql\n";
$q->QUERY_SQL($sql,"artica_backup");
if(!$q->ok){echo $q->mysql_error."\n";}

$sql="UPDATE kavmilter SET ou='$newou' WHERE ou='$ou'";
echo "$sql\n";
$q->QUERY_SQL($sql,"artica_backup");
if(!$q->ok){echo $q->mysql_error."\n";}

$sql="UPDATE kas3 SET ou='$newou' WHERE ou='$ou'";
echo "$sql\n";
$q->QUERY_SQL($sql,"artica_backup");
if(!$q->ok){echo $q->mysql_error."\n";}

$sql="UPDATE nics_virtuals SET org='$newou' WHERE org='$ou'";
echo "$sql\n";
$q->QUERY_SQL($sql,"artica_backup");
if(!$q->ok){echo $q->mysql_error."\n";}



function uploadUsers(){
	
	$ldap=new clladp();
	
	$pattern="(&(objectclass=userAccount)(cn=*))";
	$attr=array();
	
	$sr =ldap_search($this->ldap_connection,$this->suffix,$pattern,$attr);
	if($sr){
		$hash=ldap_get_entries($this->ldap_connection,$sr);
		writelogs("Found: {$hash["count"]} entries",__CLASS__.'/'.__FUNCTION__,__FILE__);
		if($hash["count"]>0){		
			for($i=0;$i<$hash["count"];$i++){
				$res[$hash[$i]["uid"][0]]=$hash[$i]["mail"][0];
			}
		}else{
			writelogs("Failed search $pattern",__CLASS__.'/'.__FUNCTION__,__FILE__);
		}
	return $res;}	
	
	
	
	
}



function CleanDatas($ou,$newou,$datas){
$tb=explode("\n",$datas);
	while (list ($num, $ligne) = each ($tb) ){
		if(preg_match('#^structuralObjectClass#',$ligne)){continue;}
		if(preg_match('#^entryUUID#',$ligne)){continue;}
		if(preg_match('#^creatorsName#',$ligne)){continue;}
		if(preg_match('#^createTimestamp#',$ligne)){continue;}
		if(preg_match('#^entryCSN#',$ligne)){continue;}
		if(preg_match('#^modifiersName#',$ligne)){continue;}
		if(preg_match('#^modifyTimestamp#',$ligne)){continue;}	
		$ligne=str_replace($ou,$newou,$ligne);
		$conf=$conf.$ligne."\n";	
	}	
echo "CleanDatas():: from ou=$ou to newou=$newou ". strlen($conf). " bytes size\n";
return $conf;
}


function ExportOuDN($ou){
	$unix=new unix();
	$ldap=new clladp();
	$slapcat=$unix->find_program("slapcat");
	$ldapadd=$unix->find_program("ldapadd");	
	$devnull=" >/dev/null 2>&1";
	@mkdir("/usr/share/artica-postfix/ressources/ldap-back/$ou",null,true);
	$dn="ou=$ou,dc=organizations,$ldap->suffix";
	$tmpfile="/usr/share/artica-postfix/ressources/ldap-back/$ou/backup.ldif";
	$cmd="$slapcat -a \"(&(entryDN:dnSubtreeMatch:=$dn))\" -l $tmpfile $devnull";
	echo "ExportOuDN():: Running $cmd\n";
	system($cmd);
	$dntemp=file_get_contents($tmpfile);


$dn="cn=$ou,cn=PostfixRobots,cn=artica,$ldap->suffix";
if($ldap->ExistsDN($dn)){
	$cmd="$slapcat -a \"(&(entryDN:dnSubtreeMatch:=$dn))\" -l $tmpfile $devnull";
	echo "ExportOuDN():: Running $cmd\n";
	system($cmd);
	$dntemp=$dntemp."\n". file_get_contents($tmpfile);
	}

$dn="cn=$ou,cn=catch-all,cn=artica,$ldap->suffix";
if($ldap->ExistsDN($dn)){
	$cmd="$slapcat -a \"(&(entryDN:dnSubtreeMatch:=$dn))\" -l $tmpfile $devnull";
	echo "ExportOuDN():: Running $cmd\n";
	system($cmd);
	$dntemp=$dntemp."\n". file_get_contents($tmpfile);
	}
$dn="dc=$ou,dc=NAB,$ldap->suffix";
if($ldap->ExistsDN($dn)){
	$cmd="$slapcat -a \"(&(entryDN:dnSubtreeMatch:=$dn))\" -l $tmpfile $devnull";
	echo "ExportOuDN():: Running $cmd\n";
	system($cmd);
	$dntemp=$dntemp."\n". file_get_contents($tmpfile);
	}
	
echo "ExportOuDN():: Saving $tmpfile\n";	
file_put_contents($tmpfile,$dntemp);	
	

}


function ExportDN($ou,$newou,$dn){
	$unix=new unix();
	$ldap=new clladp();
	$slapcat=$unix->find_program("slapcat");
	$ldapadd=$unix->find_program("ldapadd");	
	$dn=trim($dn);
	$tmpfile="/usr/share/artica-postfix/ressources/ldap-back/$ou/".time().".ldif";
	$newtmp="/tmp/".md5($tmpfile).".ldif";	
	$cmd="$slapcat -a \"(&(entryDN:dnSubtreeMatch:=$dn))\" -l $tmpfile";
	echo "ExportDN():: Running $cmd\n";
	system($cmd);
	$datas=file_get_contents($tmpfile);
	$conf=CleanDatas($ou,$newou,$datas);
	file_put_contents($newtmp,$conf);
	$cmd="$ldapadd -D cn=$ldap->ldap_admin,$ldap->suffix -h $ldap->ldap_host -p $ldap->ldap_port -w $ldap->ldap_password -x -f $newtmp";
	system($cmd);
}



function upload(){
echo "Starting uploading organization\n";
$users=new usersMenus();
if($users->CURL_PATH==null){echo "Unable to stat Curl program\n";exit;}	

$ldap=new clladp();
$session=$_GET["SESSION"];
$ou=$_GET["OU"];

echo "Using session $session for ou $ou\n";

	$sock=new sockets();
	$ini=new Bs_IniHandler();
	echo "Reading session $session\n";
	$ini->loadString($sock->GET_INFO($session));
	
$uri="https://{$ini->_params["CONF"]["servername"]}:{$ini->_params["CONF"]["port"]}/cyrus.murder.listener.php";
$command="?export-ou=yes&admin={$ini->_params["CONF"]["username"]}&pass={$ini->_params["CONF"]["password"]}&original-suffix=$ldap->suffix";
echo "Exporting DN....\n";
ExportOuDN($ou);
echo "Exporting DN done\n";
$tmpfile="/usr/share/artica-postfix/ressources/ldap-back/$ou/backup.ldif";
echo "Sending ldif $tmpfile to the target server:  \"$uri\"\n";

$filedatas=base64_encode(@file_get_contents("$tmpfile"));

$curl=new ccurl("$uri$command");
echo "post ldif $tmpfile to the target server:  \"$uri\"\n";
$curl->parms["exported-datas"]=$filedatas;
$curl->parms["exported-org"]=$ou;
if(!$curl->get()){
		echo "Failed\n";
	}

echo $curl->data."\n";
//system($cmd);

	
}


?>