<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__) . '/ressources/class.users.menus.inc');
include_once(dirname(__FILE__) . '/ressources/class.templates.inc');
include_once(dirname(__FILE__) . '/framework/class.unix.inc');
include_once(dirname(__FILE__) . '/framework/frame.class.inc');
include_once(dirname(__FILE__) . '/ressources/class.ldap.inc');
include_once(dirname(__FILE__) . '/ressources/class.maincf.multi.inc');
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["DEBUG"]=true;}
if(preg_match("#--reload#",implode(" ",$argv))){$GLOBALS["RELOAD"]=true;}

if($argv[1]=="--build"){build();die();}

function build(){
	$q=new mysql();
	$sql="SELECT ip_address FROM postfix_multi WHERE `key`='dkimproxyEnabled' AND `value`=1";
	$results=$q->QUERY_SQL($sql,'artica_backup');
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
			$ipstr=$ligne["ip_address"];	
			build_single_instance($ipstr);	
	}
	
	
}

function build_single_instance($ip){
	$sql="SELECT `value`,`ou` FROM postfix_multi WHERE `key`='myhostname' AND `ip_address`='$ip'";
	$q=new mysql();
	$unix=new unix();
	$ldap=new clladp();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	$ou=$ligne["ou"];
	$hostname=$ligne["value"];
	$main=new maincf_multi($hostname,$ou);
	echo "Starting......: dkimproxy $hostname\n";
	$freeport=$main->GET("dkimproxy_listenport");
	$array=unserialize(base64_decode($main->GET_BIGDATA("dkimproxy_datas")));
	$unix=new unix();
	
	$key="/etc/dkimproxy/$hostname/private.key";
	@mkdir("/etc/dkimproxy/$hostname",640,true);
	if(!is_file($key)){
		echo "Starting......: dkimproxy $hostname generating public and private key\n";
		$openssl=$unix->find_program("openssl");
		@mkdir("/etc/dkimproxy/$hostname",640,true);
		shell_exec("$openssl genrsa -out /etc/dkimproxy/$hostname/private.key 1024");
		shell_exec("$openssl rsa -in /etc/dkimproxy/$hostname/private.key -pubout -out /etc/dkimproxy/$hostname/public.key");
	}
	
	if($hostname=="master"){
		$domains=$ldap->AllDomains();
	}else{
		$domains=$ldap->hash_get_domains_ou($ou);
	}
	
	while (list ($dom, $nil) = each ($domains) ){
		$dd[]=$dom;
	}
	
	
$conf[]="# specify what address/port DKIMproxy should listen on";
$conf[]="listen    {$ip}:$freeport";
$conf[]="# specify what address/port DKIMproxy forwards mail to";
$conf[]="relay     {$ip}:33560";
$conf[]="# specify what domains DKIMproxy can sign for (comma-separated, no spaces)";
$conf[]="domain    ".@implode(",",$dd);
$conf[]="# specify what signatures to add";
$conf[]="signature dkim(c=relaxed)";
$conf[]="signature domainkeys(c=nofws)";
$conf[]="# specify location of the private key";
$conf[]="keyfile   /etc/dkimproxy/$hostname/private.key";
$conf[]="# specify the selector (i.e. the name of the key record put in DNS)";
$conf[]="selector  {$array["selector_name"]}";
$conf[]="min_servers 5";
$conf[]="min_spare_servers 2";	
$conf[]="";
echo "Starting......: dkimproxy $hostname generating $hostname.conf ". count($dd)." domain(s)\n";
@file_put_contents("/etc/dkimproxy/$hostname.conf",@implode("\n",$conf));
	
	
	
	
}