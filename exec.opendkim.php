<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;$GLOBALS["debug"]=true;}
if(preg_match("#--simule#",implode(" ",$argv))){$GLOBALS["SIMULE"]=true;$GLOBALS["SIMULE"]=true;}
if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;$GLOBALS["FORCE"]=true;}


if($argv[1]=="--build"){build();die();}
if($argv[1]=="--whitelist"){WhitelistHosts();die();}
if($argv[1]=="--networks"){MyNetworks();die();}
if($argv[1]=="--buildKeyView"){buildKeyView();die();}
if($argv[1]=="--TESTKeyView"){TESTKeyView();die();}
if($argv[1]=="--keyTable"){keyTable();die();}




function build(){
	$sock=new sockets();
	$EnableDKFilter=$sock->GET_INFO("EnableDKFilter");
	$conf=unserialize(base64_decode($sock->GET_INFO("OpenDKIMConfig")));
	if($EnableDKFilter==null){$EnableDKFilter=0;}
	
	if($conf["On-BadSignature"]==null){$conf["On-BadSignature"]="accept";}
	if($conf["On-NoSignature"]==null){$conf["On-NoSignature"]="accept";}
	if($conf["On-DNSError"]==null){$conf["On-DNSError"]="tempfail";}
	if($conf["On-InternalError"]==null){$conf["On-InternalError"]="accept";}

	if($conf["On-Security"]==null){$conf["On-Security"]="tempfail";}
	if($conf["On-Default"]==null){$conf["On-Default"]="accept";}
	if($conf["ADSPDiscard"]==null){$conf["ADSPDiscard"]="1";}
	if($conf["ADSPNoSuchDomain"]==null){$conf["ADSPNoSuchDomain"]="1";}	
	if($conf["DomainKeysCompat"]==null){$conf["DomainKeysCompat"]="0";}
	if($conf["OpenDKIMTrustInternalNetworks"]==null){$conf["OpenDKIMTrustInternalNetworks"]="1";}
	
	
	
	
if($conf["DomainKeysCompat"]==1){$f[]="DomainKeysCompat		  {$conf["DomainKeysCompat"]}";}
$f[]="ADSPNoSuchDomain        {$conf["ADSPNoSuchDomain"]}";
$f[]="ADSPDiscard        	  {$conf["ADSPDiscard"]}";
$f[]="AutoRestart             1";
$f[]="AutoRestartRate         10/1h";
$f[]="Canonicalization        simple/simple";
$f[]="ExemptDomains			  refile:/etc/mail/dkim/trusted-domains";
$f[]="ExternalIgnoreList      refile:/etc/mail/dkim/trusted-hosts";
$f[]="InternalHosts           refile:/etc/mail/dkim/internal-hosts";
$f[]="KeyTable                file:/etc/mail/dkim/keyTable";
$f[]="SigningTable            refile:/etc/mail/dkim/signingTable";
$f[]="LogWhy                  Yes";
$f[]="On-Default              {$conf["On-Default"]}";
$f[]="On-BadSignature         {$conf["On-BadSignature"]}";
$f[]="On-DNSError             {$conf["On-DNSError"]}";
$f[]="On-InternalError        {$conf["On-InternalError"]}";
$f[]="On-NoSignature          {$conf["On-NoSignature"]}";
$f[]="On-Security             {$conf["On-Security"]}";
$f[]="PidFile                 /var/run/opendkim/opendkim.pid";
$f[]="SignatureAlgorithm      rsa-sha256";
$f[]="Socket                  local:/var/run/opendkim/opendkim.sock";
$f[]="Syslog                  Yes";
$f[]="SyslogSuccess           Yes";
$f[]="TemporaryDirectory      /var/tmp";
$f[]="UMask                   022";
$f[]="UserID                  postfix:postfix";
$f[]="X-Header                Yes";	

@file_put_contents("/etc/opendkim.conf",@implode("\n",$f));

keyTable();
WhitelistDomains();
WhitelistHosts();
MyNetworks($conf["OpenDKIMTrustInternalNetworks"]);
echo "Starting......: opendkim Apply permissions...\n";
shell_exec("/bin/chmod 755 /etc/mail/dkim >/dev/null 2>&1");
shell_exec("/bin/chmod 755 /etc/mail/dkim/keys >/dev/null 2>&1");
shell_exec("/bin/chmod 750 /etc/mail/dkim/keys/* >/dev/null 2>&1");
shell_exec("/bin/chmod 640 /etc/mail/dkim/keys/*/* >/dev/null 2>&1");
shell_exec("/bin/chown -R postfix:postfix /etc/mail/dkim >/dev/null 2>&1");
echo "Starting......: opendkim Apply permissions done...\n";
	
}
function keyTable(){
$unix=new unix();
$opendkim_genkey=$unix->find_program("opendkim-genkey");

if(!is_file($opendkim_genkey)){
	$opendkim_genkey=$unix->find_program("opendkim-genkey.sh");
}

if(!is_file($opendkim_genkey)){
	echo "Starting......: opendkim \"opendkim-genkey(.sh\" no such binary found !\n";
	return;
}	
$chown=$unix->find_program("chown");
$file="/etc/mail/dkim/keyTable";
@mkdir(dirname($file),null,true);

$ldap=new clladp();
$domainsH=$ldap->AllDomains();
if(is_array($domainsH)){
	while (list ($num, $DOMAIN) = each ($domainsH) ){
		$dir="/etc/mail/dkim/keys/$DOMAIN";
		if(!is_dir($dir)){
			echo "Starting......: opendkim creating directory /etc/mail/dkim/keys/$DOMAIN\n";
			@mkdir("/etc/mail/dkim/keys/$DOMAIN",null,true);
		}	
		if(!keyTableVerifyFiles($dir)){
			echo "Starting......: opendkim generating TXT and private for $DOMAIN\n";
			$cmd="$opendkim_genkey -D $dir/ -d $DOMAIN -s default";
			system($cmd);
			shell_exec("/bin/cp $dir/default.private $dir/default");
		}else{
			echo "Starting......: opendkim TXT and private for $DOMAIN OK\n";
		}
		
		shell_exec("$chown -R postfix:postfix $dir >/dev/null 2>&1");
		$keyTable[]="default._domainkey.$DOMAIN	$DOMAIN:default:/etc/mail/dkim/keys/$DOMAIN/default";
		$signingTable[]="*@$DOMAIN default._domainkey.$DOMAIN";
		
	}
}else{
	echo "Starting......: opendkim generating No domains set\n";
}
	
	if(@file_put_contents("/etc/mail/dkim/keyTable",@implode("\n",$keyTable))){
			echo "Starting......: opendkim generating keyTable done...\n";
	}else{
		echo "Starting......: opendkim FAILED generating keyTable done...\n";
	}
	
	if(@file_put_contents("/etc/mail/dkim/signingTable",@implode("\n",$signingTable))){
		echo "Starting......: opendkim generating signingTable done...\n";
	}else{
		echo "Starting......: opendkim FAILED generating signingTable done...\n";	
	}
	
}

function WhitelistDomains(){
	
	$sql="SELECT * FROM spamassassin_dkim_wl ORDER BY ID DESC";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");


	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
	$f[]=$ligne["domain"];
	}
	
	@file_put_contents("/etc/mail/dkim/trusted-domains",@implode("\n",$f));
	echo "Starting......: opendkim generating trusted domains ". count($f)." entries done...\n";
}

function keyTableVerifyFiles($dir){
	if(!is_file("$dir/default.private")){return false;}
	if(!is_file("$dir/default.txt")){return false;}
	if(!is_file("$dir/default")){return false;}
	return true;
}

function WhitelistHosts(){  
		
	$q=new mysql();
	$sql="SELECT * FROM postfix_whitelist_con";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "$q->mysql_error\n";}
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$f[]=$ligne["ipaddr"];
		$f[]=$ligne["hostname"];
		
	}	
	@mkdir("/etc/mail/dkim",null,true);
 	@file_put_contents("/etc/mail/dkim/trusted-hosts",@implode("\n",$f));
 	echo "Starting......: opendkim generating trusted hosts ". count($f)." entries done...\n";
}

function MyNetworks($trust=0){
	if($trust==1){
		$ldap=new clladp();
		$nets=$ldap->load_mynetworks();
	}
	$nets[]="127.0.0.0/8";
	while (list ($num, $network) = each ($nets) ){$cleaned[$network]=$network;}
	unset($nets);
	while (list ($network, $network2) = each ($cleaned) ){$nets[]=$network;}	
	echo "Starting......: opendkim generating internal hosts ". count($nets)." entries done...\n";
	$nets[]="";
	@file_put_contents("/etc/mail/dkim/internal-hosts",@implode("\n",$nets));
}

function buildKeyView(){
$ldap=new clladp();
$domainsH=$ldap->AllDomains();
if(is_array($domainsH)){
	while (list ($num, $DOMAIN) = each ($domainsH) ){
		$file="/etc/mail/dkim/keys/$DOMAIN/default.txt";
		if(is_file($file)){
			$array[$DOMAIN]=@file_get_contents($file);	
		}
	
}
}

@file_put_contents("/etc/mail/dkim.domains.key",base64_encode(serialize($array)));


}
function TESTKeyView(){
	$unix=new unix();
	$opendkim=$unix->find_program("opendkim-testkey");
	if(!is_file($opendkim)){return ;}
$ldap=new clladp();
$domainsH=$ldap->AllDomains();
if(is_array($domainsH)){
	while (list ($num, $DOMAIN) = each ($domainsH) ){
		unset($results);
		exec("$opendkim -d $DOMAIN -s default 2>&1",$results);
		$array[$DOMAIN]=@implode("\n",$results);
	}
}

@file_put_contents("/etc/mail/dkim.domains.tests.key",base64_encode(serialize($array)));


}

 

?>