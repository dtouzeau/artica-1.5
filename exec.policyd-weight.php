<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__).'/ressources/class.amavis.inc');

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}

if(preg_match("#--verbose#",implode(" ",$argv))){$_GET["DEBUG"]=true;}


if(systemMaxOverloaded()){
	writelogs("This system is too many overloaded, die()",__FUNCTION__,__FILE__,__LINE__);
	die();
}

$sock=new sockets();
$EnablePolicydWeight=$sock->GET_INFO('EnablePolicydWeight');

if($EnablePolicydWeight<>1){RemovePolicydWeight();}else{EnablePolicyd();}
CheckSpamassassinMilter();
CheckCLamavMilter();
CheckAmavis();


die();

function EnablePolicyd(){
	
RemovePolicydWeight();

	$users=new usersMenus();
	$POLICYD_WEIGHT_PORT=trim($users->POLICYD_WEIGHT_PORT);
	if($POLICYD_WEIGHT_PORT==null){$POLICYD_WEIGHT_PORT=12525;}

events("Enabling policyd-weight on port $POLICYD_WEIGHT_PORT");

$datas=exec('postconf -h smtpd_recipient_restrictions');

if($datas==null){
		events("Warning postconf return null string...");
}else{
	$tbl=explode(",",$datas);
	events("EnablePolicyd():: ". count($tbl)." lines");
	while (list ($num, $ligne) = each ($tbl) ){
		if(trim($ligne)==null){continue;}
		$ARRAY[trim($ligne)]=true;
	}	
}

reset($tbl);

array_unshift($tbl,"permit_mynetworks","permit_sasl_authenticated","reject_unauth_destination");

	while (list ($num, $ligne) = each ($tbl) ){
		if(trim($ligne)==null){continue;}
		$ARRAY2[trim($ligne)]=true;
	}
	
	unset($tbl);
	while (list ($num, $ligne) = each ($ARRAY2) ){
		if(trim($num)==null){continue;}
		$tbl[]=$num;
	}	



$tbl[]="check_client_access hash:/etc/postfix/wbl_connections";
$tbl[]="check_recipient_access hash:/etc/postfix/wbl_connections";
$tbl[]="check_policy_service inet:127.0.0.1:$POLICYD_WEIGHT_PORT";
$finalstring=implode(",",$tbl);

$cmd="postconf -e \"smtpd_recipient_restrictions = $finalstring\"";
events($cmd);
system($cmd);
if(!is_file("/etc/postfix/whitelist_connections")){exec("/bin/touch /etc/postfix/whitelist_connections");}

// REJECT OR OK


	$q=new mysql();
	$sql="SELECT * FROM postfix_whitelist_con";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "$q->mysql_error\n";}
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$finalwhitelist[]=$ligne["ipaddr"]."\tOK";
		$finalwhitelist[]=$ligne["hostname"]."\tOK";
		
	}


if(is_array($finalwhitelist)){
	$conf=implode("\n",$finalwhitelist);
}

events("saving ". strlen($conf)." bytes length in /etc/postfix/wbl_connections");
@file_put_contents("/etc/postfix/wbl_connections",$conf);
system("postmap hash:/etc/postfix/wbl_connections");
events("adding policyd-weight done...");
	
}




$ldap=new clladp();


function RemovePolicydWeight(){
	events("Removing policyd-weight");
	$users=new usersMenus();
	$POLICYD_WEIGHT_PORT=trim($users->POLICYD_WEIGHT_PORT);
	if($POLICYD_WEIGHT_PORT==null){$POLICYD_WEIGHT_PORT=12525;}
	
	$datas=exec('postconf -h smtpd_recipient_restrictions');
	if($datas==null){
		events("RemovePolicydWeight():: Warning postconf return null string...");
		return null;
	}
	
	$tbl=explode(",",$datas);
	events("RemovePolicydWeight():: ". count($tbl)." lines");
	
	if(!is_array($tbl)){
		$tbl[0]="permit_mynetworks";
		$tbl[1]="permit_sasl_authenticated";
		$tbl[2]="reject_unauth_destination";
		
	}else{
		array_unshift($tbl,"permit_mynetworks","permit_sasl_authenticated","reject_unauth_destination");
	}
	
	while (list ($num, $ligne) = each ($tbl) ){
		if(trim($ligne)==null){continue;}
		$ARRAY[trim($ligne)]=trim($ligne);
	}
	
	unset($ARRAY["check_client_access hash:/etc/postfix/wbl_connections"]);
	unset($ARRAY["check_recipient_access hash:/etc/postfix/wbl_connections"]);
	
while (list ($num, $ligne) = each ($ARRAY) ){
		if(preg_match("#127\.0\.0.+?".trim($POLICYD_WEIGHT_PORT)."#",$num)){
			events("delete $num");
			unset($ARRAY[$num]);
		}
	}	
	
	reset($ARRAY);
	
	while (list ($num, $ligne) = each ($ARRAY) ){
		if(trim($ligne)==null){continue;}
		events("Enabled rule $num");
		$finalarray[]=$num;
	}
	if(is_array($finalarray)){
		$finalstring=implode(",",$finalarray);
	}
	system("postconf -e \"smtpd_recipient_restrictions = $finalstring\"");
	
}

function smtpd_milters(){
	$datas=exec('postconf -h smtpd_milters');
	$tbl=explode(" ",$datas);
	events("smtpd_milters():: ". count($tbl)." lines");
	if(!is_array($tbl)){return array();}
	
	while (list ($num, $ligne) = each ($tbl) ){
		if(trim($ligne)==null){continue;}
		$filename=basename($ligne);
		$ARRAY[$filename]=trim($ligne);
	}
	
	if(!is_array($ARRAY)){return array();}
	return $ARRAY;
}
function smtpd_milters_remove($filename){
	$datas=exec('postconf -h smtpd_milters');
	$tbl=explode(" ",$datas);
	events("smtpd_milters_remove():: ". count($tbl)." lines");
	if(!is_array($tbl)){return;}
	
	while (list ($num, $ligne) = each ($tbl) ){
		if(trim($ligne)==null){continue;}
		$filename=basename($ligne);
		$ARRAY[$filename]=trim($ligne);
	}
	
	if(!is_array($ARRAY)){return;}
	unset($ARRAY[$filename]);
	if(!is_array($ARRAY)){$newpattern=null;}else{$newpattern=implode(" ",$ARRAY);}
	events("Adding \"$newpattern\" in smtpd_milters");
	system("postconf -e \"smtpd_milters = $newpattern\"");
	
}
function smtpd_milters_add($filename,$pattern){
	$datas=exec('postconf -h smtpd_milters');
	$tbl=explode(" ",$datas);
	events("smtpd_milters_add():: ". count($tbl)." lines");
	if(!is_array($tbl)){return;}
	
	while (list ($num, $ligne) = each ($tbl) ){
		if(trim($ligne)==null){continue;}
		$filename=basename($ligne);
		$ARRAY[$filename]=trim($ligne);
	}
	
	if(!is_array($ARRAY)){return;}
	$ARRAY[$filename]=$pattern;
	if(!is_array($ARRAY)){$newpattern=null;}else{$newpattern=implode(" ",$ARRAY);}
	events("Adding \"$newpattern\" in smtpd_milters");
	system("postconf -e \"smtpd_milters = $newpattern\"");
	
}


function CheckSpamassassinMilter(){
	$users=new usersMenus();
	$sock=new sockets();
	$SpamAssMilterEnabled=intval(trim($sock->GET_INFO("SpamAssMilterEnabled")));

	$array=smtpd_milters();
	if($array["spamass.sock"]<>null){
		if($SpamAssMilterEnabled==0){
			events("CheckSpamassassinMilter():: spamassassin-milter is disabled but found in main.cf, remove it");
			smtpd_milters_remove("spamass.sock");
			return;
		}
	}
	
	if($array["spamass.sock"]==null){
		if($SpamAssMilterEnabled==1){
			events("CheckSpamassassinMilter():: spamassassin-milter is enabled but not found in main.cf, add it");
			smtpd_milters_add("spamass.sock","unix:/var/spool/postfix/spamass/spamass.sock");
			return;
		}
	}
}

//unix:/var/spool/postfix/var/run/clamav/clamav-milter.ctl

function CheckCLamavMilter(){
	$users=new usersMenus();
	$sock=new sockets();
	$ClamavMilterEnabled=intval(trim($sock->GET_INFO("ClamavMilterEnabled")));

	$array=smtpd_milters();
	if($array["clamav-milter.ctl"]<>null){
		if($ClamavMilterEnabled==0){
			events("CheckCLamavMilter():: clamav-milter is disabled but found in main.cf, remove it");
			smtpd_milters_remove("clamav-milter.ctl");
			return;
		}
	}
	
	if($array["clamav-milter.ctl"]==null){
		if($ClamavMilterEnabled==1){
			events("CheckCLamavMilter():: clamav-milter is enabled but not found in main.cf, add it");
			smtpd_milters_add("clamav-milter.ctl","unix:/var/spool/postfix/var/run/clamav/clamav-milter.ctl");
			return;
		}
	}
}




function CleanMastercf(){
	$datas=@file_get_contents('/etc/postfix/master.cf');
	if($datas==null){return ;}
	$tbl=explode(" ",$datas);
	events("CleanMastercf():: ". count($tbl)." lines");
	if(!is_array($tbl)){return;}

while (list ($num, $ligne) = each ($tbl) ){
		if(trim($ligne)==null){continue;}
		$ARRAY[]=$ligne;
	}

	$newfile=implode("\n",$ARRAY);
	@file_put_contents("/etc/postfix/master.cf",$newfile);
	events("CleanMastercf():: done");
	
}

function CheckAmavis(){
	$sock=new sockets();
	$EnableAmavisDaemon=$sock->GET_INFO("EnableAmavisDaemon");
	if($EnableAmavisDaemon==0){
		events("CheckAmavis():: EnableAmavisDaemon not enabled");
		return ;
	}
	
	CheckAmavisLocalInterface();
	
}

function CheckAmavisLocalInterface(){
	$sock=new sockets();
	$a=$sock->GET_INFO("EnableAmavisInMasterCF");
	$EnableAmavisInMasterCF=intval(trim($a));	
	if($EnableAmavisInMasterCF==0){
		events("CheckAmavisLocalInterface():: EnableAmavisInMasterCF not enabled = \"$a\"");
		return ;
	}

	$datas=@file_get_contents("/usr/local/etc/amavisd.conf");
	$tbl=explode("\n",$datas);
	events("CheckAmavisLocalInterface():: ". count($tbl)." lines");
while (list ($num, $ligne) = each ($tbl) ){
		if(trim($ligne)==null){continue;}
		if(preg_match("#inet_acl.+?qw#",$ligne)){
			events("CheckAmavisLocalInterface():: inet_acl parameters ok");
			return true;		
		}
	}	
	
events("rebuild amavis");
$amavis=new amavis();
$amavis->Save();
$amavis->SaveToServer();	
	
	
}





function events($text){
		$pid=getmypid();
		$date=date("H:i:s");
		$logFile="/var/log/artica-postfix/postfix-config.debug";
		$size=filesize($logFile);
		if($size>1000000){unlink($logFile);}
		$f = @fopen($logFile, 'a');
		$line="[$pid/".basename(__FILE__)."] $date $text\n";
		if($_GET["DEBUG"]){echo $line;}
		@fwrite($f, "[$pid/".basename(__FILE__)."] $date $text\n");
		@fclose($f);	
		}


		
		
?>