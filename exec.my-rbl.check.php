<?php
$GLOBALS["VERBOSE"]=false;
$GLOBALS["DEBUG"]=false;;
$GLOBALS["FORCE"]=false;
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__) . '/ressources/class.users.menus.inc');
include_once(dirname(__FILE__) . '/framework/class.unix.inc');
include_once(dirname(__FILE__) . '/framework/frame.class.inc');
include_once(dirname(__FILE__) . '/ressources/class.ldap.inc');
include_once(dirname(__FILE__) . '/ressources/class.maincf.multi.inc');

if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["DEBUG"]=true;$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}
if(preg_match("#--reload#",implode(" ",$argv))){$GLOBALS["RELOAD"]=true;}
if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;}

if($argv[1]=='--myip'){GetMyIp();die();}
if($argv[1]=='--checks'){CheckCMDLine();die();}
if($argv[1]=='--verif'){Checks();die();}
if($argv[1]=='--query'){ChecksDNSBL($argv[2],true);die();}


function CheckCMDLine(){
	$sock=new sockets();
	$ips=unserialize(base64_decode($sock->GET_INFO("RBLCheckIPList")));
	
	if(count($ips)>0){
		if($GLOBALS["VERBOSE"]){echo count($ips). " elements to check\n";}
		if(is_array($ips)){
			while (list ($num, $ip) = each ($ips) ){
				if($GLOBALS["VERBOSE"]){echo "$ip element...\n";}
				ChecksDNSBL($ip,false,true);
			}
			ChecksDNSBL();
			return;
		}
		
	}
	ChecksDNSBL();
}
	


function Checks(){
	include_once("HTTP/Request.php");
	include_once('Net/DNSBL.php');
	
	if(!class_exists("Net_DNSBL")){
		if($GLOBALS["VERBOSE"]){echo "Net_DNSBL does not exists ".__LINE__."\n";}
		$unix=new unix();
		$pear_bin=$unix->find_program("pear");
		if($pear_bin==null){
			writelogs("Fatal 'pear' no such file",__FUNCTION__,__FILE__,__LINE__);
			$p=Paragraphe('danger64.png',"{PEAR_NOT_INSTALLED}","{PEAR_NOT_INSTALLED_TEXT}",300,80);
			@file_put_contents("/usr/share/artica-postfix/ressources/logs/web/blacklisted.html",$p);
   			@chmod("/usr/share/artica-postfix/ressources/logs/web/blacklisted.html",775);
			return;
		}
		
		shell_exec("$pear_bin install Net_DNSBL");
	}else{
		if($GLOBALS["VERBOSE"]){echo "Net_DNSBL OK\n";}
	}
	
	if(!class_exists("HTTP_Request")){
		if($GLOBALS["VERBOSE"]){echo "HTTP_Request does not exists ".__LINE__."\n";}
		$unix=new unix();
		$pear_bin=$unix->find_program("pear");
		if($GLOBALS["VERBOSE"]){echo "Pear:$pear_bin install HTTP_Request\n";}
		if($pear_bin==null){
			writelogs("Fatal 'pear' no such file",__FUNCTION__,__FILE__,__LINE__);
			$p=Paragraphe('danger64.png',"{PEAR_NOT_INSTALLED}","{PEAR_NOT_INSTALLED_TEXT}",300,80);
			 @file_put_contents("/usr/share/artica-postfix/ressources/logs/web/blacklisted.html",$p);
   			 @chmod("/usr/share/artica-postfix/ressources/logs/web/blacklisted.html",775);
			 return;
		
		}
		if($GLOBALS["VERBOSE"]){echo "running $pear_bin install HTTP_Request\n";}
		shell_exec("$pear_bin install HTTP_Request");
	}else{
		if($GLOBALS["VERBOSE"]){echo "HTTP_Request OK\n";}
	}	
}


function GetMyIp(){
	Checks();
	$sock=new sockets();
	if($sock->GET_INFO("DoNotResolvInternetIP")==1){
		$ip=$sock->GET_INFO("PublicIPAddress");
		if($ip<>null){return $ip;}
	}
	
	$time=file_time_min("/usr/share/artica-postfix/ressources/logs/web/myIP.conf");
	if($time<60){
		return trim(@file_get_contents("/usr/share/artica-postfix/ressources/logs/web/myIP.conf"));
	}
	@unlink("/usr/share/artica-postfix/ressources/logs/web/myIP.conf");
	include_once("HTTP/Request.php");
	include_once('Net/DNSBL.php');
	
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$datas=$sock->GET_INFO("ArticaProxySettings");
	$ini->loadString($datas);
	$ArticaProxyServerEnabled=$ini->_params["PROXY"]["ArticaProxyServerEnabled"];
	$ArticaProxyServerName=$ini->_params["PROXY"]["ArticaProxyServerName"];
	$ArticaProxyServerPort=$ini->_params["PROXY"]["ArticaProxyServerPort"];
	$ArticaProxyServerUsername=$ini->_params["PROXY"]["ArticaProxyServerUsername"];
	$ArticaProxyServerUserPassword=$ini->_params["PROXY"]["ArticaProxyServerUserPassword"];
	$ArticaCompiledProxyUri=$ini->_params["PROXY"]["ArticaCompiledProxyUri"];
	
	$req =new HTTP_Request("http://www.artica.fr/my-ip.php");  
	$req->setURL("http://www.artica.fr/my-ip.php");	
	$req->setMethod(HTTP_REQUEST_METHOD_GET);
	if($ArticaProxyServerEnabled=="yes"){
			$req->setProxy($ArticaProxyServerName, $ArticaProxyServerPort, $ArticaProxyServerUsername, $ArticaProxyServerUserPassword); 
	}
	
	$req->sendRequest();
	$code = $req->getResponseCode();
	$datas= trim($req->getResponseBody());
	
	if(preg_match("#([0-9\.]+)#",$datas,$re)){
		$myip=$re[1];
		writelogs("http://www.artica.fr/my-ip.php -> $code ($datas)");
	}
	if($myip<>null){
		@file_put_contents("/usr/share/artica-postfix/ressources/logs/web/myIP.conf",$myip);
		@chmod("/usr/share/artica-postfix/ressources/logs/web/myIP.conf",775);
		$sock->SET_INFO("PublicIPAddress",$myip);
	}
	
}


function ChecksDNSBL($iptocheck=null,$output=false,$increment=false){
	if(trim($iptocheck=="--force")){$iptocheck=null;$output=false;}
	$textip=null;
	if($iptocheck==null){$myip=GetMyIp();}else{$myip=$iptocheck;}
	if(!preg_match("#[0-9+]\.[0-9+]\.[0-9+]\.[0-9+]#",$myip)){
		$textip=" ($myip) ";
		$myip=gethostbyname($myip);
		if($GLOBALS["VERBOSE"]){
			echo "Checking $myip...........: was$textip\n";
		}
	}
	$sock=new sockets();
	$unix=new unix();
	$RBLCheckFrequency=$sock->GET_INFO("RBLCheckFrequency");
	$RBLCheckNotification=$sock->GET_INFO("RBLCheckNotification");
	if(!is_numeric($RBLCheckFrequency)){$RBLCheckFrequency=60;}	
	if(!is_numeric($RBLCheckNotification)){$RBLCheckNotification=0;}
	
	if($GLOBALS["VERBOSE"]){
		echo "Checking $myip$textip...........: RBLCheckFrequency...: $RBLCheckFrequency\n";
		echo "Checking $myip$textip...........: RBLCheckNotification: $RBLCheckNotification\n";
	}
	
	if(!$GLOBALS["FORCE"]){
		$md=md5($myip);
		$timefile="/etc/artica-postfix/cron.1/ChecksDNSBL.$md.time";
		if(!$GLOBALS["VERBOSE"]){
			$time=file_time_min($timefile);
			if($time<$RBLCheckFrequency){
				echo @file_get_contents($timefile);
				return;
			}	
		}
		@unlink($timefile);
		@file_put_contents($timefile,"#");
	}
	include_once('Net/DNSBL.php');
	$dnsbl = new Net_DNSBL();
	
	if(!isset($GLOBALS["DDNS"])){
		$sql="SELECT * FROM rbl_servers WHERE enabled=1 ORDER BY `rbl`";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		if($q->ok){
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$GLOBALS["DDNS"][]=$ligne["rbl"];
			}
		}
	}
	
	if(count($GLOBALS["DDNS"])==0){
		$GLOBALS["DDNS"][]="b.barracudacentral.org";
		$GLOBALS["DDNS"][]="bl.deadbeef.com";
		$GLOBALS["DDNS"][]="bl.emailbasura.org";
		$GLOBALS["DDNS"][]="bl.spamcannibal.org";
		$GLOBALS["DDNS"][]="bl.spamcop.net";
		//$dnss[]="blackholes.five-ten-sg.com";
		$GLOBALS["DDNS"][]="blacklist.woody.ch";
		$GLOBALS["DDNS"][]="bogons.cymru.com";
		$GLOBALS["DDNS"][]="cbl.abuseat.org";
		$GLOBALS["DDNS"][]="cdl.anti-spam.org.cn";
		$GLOBALS["DDNS"][]="combined.abuse.ch";
		$GLOBALS["DDNS"][]="combined.rbl.msrbl.net";
		$GLOBALS["DDNS"][]="db.wpbl.info";
		$GLOBALS["DDNS"][]="dnsbl-1.uceprotect.net";
		$GLOBALS["DDNS"][]="dnsbl-2.uceprotect.net";
		$GLOBALS["DDNS"][]="dnsbl-3.uceprotect.net";
		$GLOBALS["DDNS"][]="dnsbl.ahbl.org";
		$GLOBALS["DDNS"][]="dnsbl.cyberlogic.net";
		$GLOBALS["DDNS"][]="dnsbl.inps.de";
		$GLOBALS["DDNS"][]="dnsbl.njabl.org";
		$GLOBALS["DDNS"][]="dnsbl.sorbs.net";
		$GLOBALS["DDNS"][]="drone.abuse.ch";
		$GLOBALS["DDNS"][]="drone.abuse.ch";
		$GLOBALS["DDNS"][]="duinv.aupads.org";
		$GLOBALS["DDNS"][]="dul.dnsbl.sorbs.net";
		$GLOBALS["DDNS"][]="dul.ru";
		$GLOBALS["DDNS"][]="dyna.spamrats.com";
		$GLOBALS["DDNS"][]="dynip.rothen.com";
		$GLOBALS["DDNS"][]="fl.chickenboner.biz";
		$GLOBALS["DDNS"][]="http.dnsbl.sorbs.net";
		$GLOBALS["DDNS"][]="images.rbl.msrbl.net";
		$GLOBALS["DDNS"][]="ips.backscatterer.org";
		$GLOBALS["DDNS"][]="ix.dnsbl.manitu.net";
		$GLOBALS["DDNS"][]="korea.services.net";
		$GLOBALS["DDNS"][]="misc.dnsbl.sorbs.net";
		$GLOBALS["DDNS"][]="noptr.spamrats.com";
		$GLOBALS["DDNS"][]="ohps.dnsbl.net.au";
		$GLOBALS["DDNS"][]="omrs.dnsbl.net.au";
		$GLOBALS["DDNS"][]="orvedb.aupads.org";
		$GLOBALS["DDNS"][]="osps.dnsbl.net.au";
		$GLOBALS["DDNS"][]="osrs.dnsbl.net.au";
		$GLOBALS["DDNS"][]="owfs.dnsbl.net.au";
		$GLOBALS["DDNS"][]="owps.dnsbl.net.au";
		$GLOBALS["DDNS"][]="pbl.spamhaus.org";
		$GLOBALS["DDNS"][]="phishing.rbl.msrbl.net";
		$GLOBALS["DDNS"][]="probes.dnsbl.net.au";
		$GLOBALS["DDNS"][]="proxy.bl.gweep.ca";
		$GLOBALS["DDNS"][]="proxy.block.transip.nl";
		$GLOBALS["DDNS"][]="psbl.surriel.com";
		$GLOBALS["DDNS"][]="rbl.interserver.net";
		$GLOBALS["DDNS"][]="rdts.dnsbl.net.au";
		$GLOBALS["DDNS"][]="relays.bl.gweep.ca";
		$GLOBALS["DDNS"][]="relays.bl.kundenserver.de";
		$GLOBALS["DDNS"][]="relays.nether.net";
		$GLOBALS["DDNS"][]="residential.block.transip.nl";
		$GLOBALS["DDNS"][]="ricn.dnsbl.net.au";
		$GLOBALS["DDNS"][]="rmst.dnsbl.net.au";
		$GLOBALS["DDNS"][]="sbl.spamhaus.org";
		$GLOBALS["DDNS"][]="short.rbl.jp";
		$GLOBALS["DDNS"][]="smtp.dnsbl.sorbs.net";
		$GLOBALS["DDNS"][]="socks.dnsbl.sorbs.net";
		$GLOBALS["DDNS"][]="spam.abuse.ch";
		$GLOBALS["DDNS"][]="spam.dnsbl.sorbs.net";
		$GLOBALS["DDNS"][]="spam.rbl.msrbl.net";
		$GLOBALS["DDNS"][]="spam.spamrats.com";
		$GLOBALS["DDNS"][]="spamlist.or.kr";
		$GLOBALS["DDNS"][]="spamrbl.imp.ch";
		$GLOBALS["DDNS"][]="t3direct.dnsbl.net.au";
		$GLOBALS["DDNS"][]="tor.ahbl.org";
		$GLOBALS["DDNS"][]="tor.dnsbl.sectoor.de";
		$GLOBALS["DDNS"][]="torserver.tor.dnsbl.sectoor.de";
		$GLOBALS["DDNS"][]="ubl.lashback.com";
		$GLOBALS["DDNS"][]="ubl.unsubscore.com";
		$GLOBALS["DDNS"][]="virbl.bit.nl";
		$GLOBALS["DDNS"][]="virus.rbl.jp";
		$GLOBALS["DDNS"][]="virus.rbl.msrbl.net";
		$GLOBALS["DDNS"][]="web.dnsbl.sorbs.net";
		$GLOBALS["DDNS"][]="wormrbl.imp.ch";
		$GLOBALS["DDNS"][]="xbl.spamhaus.org";
		$GLOBALS["DDNS"][]="zen.spamhaus.org";
		$GLOBALS["DDNS"][]="zombie.dnsbl.sorbs.net";	
	}
	
	
	if($GLOBALS["VERBOSE"]){
		echo "Checking $myip$textip...........: checking............: ". count($GLOBALS["DDNS"]) ." rbls servers\n";
		echo "Checking $myip$textip...........: Output..............: $output\n";
	}	
	
	if($GLOBALS["VERBOSE"]){echo "checking ". count($GLOBALS["DDNS"]) ." rbls servers\n";}
	if($GLOBALS["VERBOSE"]){echo "Checking $myip...........: ->setBlacklists();\n";}
	reset($GLOBALS["DDNS"]);
	$dnsbl->setBlacklists($GLOBALS["DDNS"]);
	if(!$output){
		if(!$increment){
			if($GLOBALS["VERBOSE"]){echo "Delete /usr/share/artica-postfix/ressources/logs/web/blacklisted.html\n";}
			@unlink("/usr/share/artica-postfix/ressources/logs/web/blacklisted.html");
		}
	}

	if($output){
		if ($dnsbl->isListed($myip)) {
		$blacklist=$dnsbl->getListingBl($myip);
		$detail = $dnsbl->getDetails($myip); 
		$final="$blacklist;{$detail["txt"][0]}";
		@file_put_contents($timefile,$final);
		echo $final;
		}
	
	return;
	}

	$date=date('l F H:i');
	if($GLOBALS["VERBOSE"]){echo "Checking $myip$textip...........: Output..............: $date\n";}
	if(!$increment){
		@unlink("/usr/share/artica-postfix/ressources/logs/web/blacklisted.html");
		@unlink("/usr/share/artica-postfix/ressources/logs/web/Notblacklisted.html");
	}
	
if ($dnsbl->isListed($myip)) {
   $blacklist=$dnsbl->getListingBl($myip);
   if($RBLCheckNotification==1){
   	$unix->send_email_events("Your server ($myip$textip) is blacklisted from $blacklist","This is the result of checking your server from " .count($GLOBALS["DDNS"])." black list servers.
   It seems your server (ip:$myip$textip) is blacklisted from $blacklist
   If you trying to send mails from this server, it should be rejected from many SMTP servers that use \"$blacklist\" for check senders IP addresses.
   ","postfix");
   }
   echo "$myip: blacklisted from $blacklist write \"/usr/share/artica-postfix/ressources/logs/web/blacklisted.html\"\n";
   $p=Paragraphe('danger64.png',"{WARN_BLACKLISTED}","$myip$textip {IS_BLACKLISTED_FROM} $blacklist ($date)","javascript:Loadjs('system.rbl.check.php')","$myip {IS_BLACKLISTED_FROM} $blacklist",300,80);
   if($increment){$p=@file_get_contents("/usr/share/artica-postfix/ressources/logs/web/blacklisted.html").$p;}
   @file_put_contents("/usr/share/artica-postfix/ressources/logs/web/blacklisted.html",$p);
   shell_exec("/bin/chmod 777 /usr/share/artica-postfix/ressources/logs/web/blacklisted.html >/dev/null 2>&1");
   return;
}else{
	if($GLOBALS["VERBOSE"]){echo "checking ". count($GLOBALS["DDNS"]) ." rbls servers success\n";}
}

$dnsbl = new Net_DNSBL();
reset($GLOBALS["DDNS"]);
$dnsbl->setBlacklists($GLOBALS["DDNS"]);
if ($dnsbl->isListed($myip)) {
   $blacklist=$dnsbl->getListingBl($myip);
   if($RBLCheckNotification==1){
	   send_email_events("Your server ($myip$textip) is blacklisted from $blacklist","This is the result of checking your server from " .count($GLOBALS["DDNS"])." black list servers.
	   It seems your server (ip:$myip$textip) is blacklisted from $blacklist
	   If you trying to send mails from this server, it should be rejected from many SMTP servers that use \"$blacklist\" for check senders IP addresses.
	   ","postfix");
   }

   echo "$myip$textip: blacklisted from $blacklist write \"/usr/share/artica-postfix/ressources/logs/web/blacklisted.html\"\n";
   $p=Paragraphe('danger64.png',"{WARN_BLACKLISTED}","$myip$textip {IS_BLACKLISTED_FROM} $blacklist ($date)","javascript:Loadjs('system.rbl.check.php')","$myip$textip {IS_BLACKLISTED_FROM} $blacklist",300,80);
   if($increment){$p=@file_get_contents("/usr/share/artica-postfix/ressources/logs/web/blacklisted.html").$p;}
   @file_put_contents("/usr/share/artica-postfix/ressources/logs/web/blacklisted.html",$p);
   shell_exec("/bin/chmod 777 /usr/share/artica-postfix/ressources/logs/web/blacklisted.html >/dev/null 2>&1");
   return;
}else{
	if($GLOBALS["VERBOSE"]){echo "checking ". count($GLOBALS["DDNS"]) ." rbls servers success\n";}
}
$p=Paragraphe('ok64.png',"{NOT_BLACKLISTED}","$myip$textip {IS_NOT_BLACKLISTED } ($date)","javascript:Loadjs('system.rbl.check.php')",null,300,80);
if($increment){$p=@file_get_contents("/usr/share/artica-postfix/ressources/logs/web/Notblacklisted.html").$p;}
@file_put_contents("/usr/share/artica-postfix/ressources/logs/web/Notblacklisted.html",$p);
shell_exec("/bin/chmod 777 /usr/share/artica-postfix/ressources/logs/web/Notblacklisted.html >/dev/null 2>&1");



}
?>

