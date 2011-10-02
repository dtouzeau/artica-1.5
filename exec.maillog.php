<?php
$GLOBALS["DEBUG_MEM"]=true;
$GLOBALS["DEBUG_MEM_FILE"]="/var/log/artica-postfix/postfix-logger.debug";

events("Memory: START AT ".round(((memory_get_usage()/1024)/1000),2) ." line:".__LINE__);
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
events("Memory: ".round(((memory_get_usage()/1024)/1000),2) ." after includes class.ini.inc line:".__LINE__);
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
events("Memory: ".round(((memory_get_usage()/1024)/1000),2) ." after includes class.os.system.inc line:".__LINE__);
include_once(dirname(__FILE__).'/framework/frame.class.inc');
events("Memory: ".round(((memory_get_usage()/1024)/1000),2) ." after includes frame.class.inc line: ".__LINE__);
include_once(dirname(__FILE__).'/framework/class.unix.inc');
events("Memory: ".round(((memory_get_usage()/1024)/1000),2) ." after includes class.unix.inc line: ".__LINE__);
include_once(dirname(__FILE__).'/framework/class.settings.inc');
events("Memory: ".round(((memory_get_usage()/1024)/1000),2) ." after includes class.settings.inc line: ".__LINE__);
include_once(dirname(__FILE__).'/ressources/class.sockets.inc');

events("Memory: FINISH ".round(((memory_get_usage()/1024)/1000),2) ." after includes line: ".__LINE__);

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
$GLOBALS["COMMANDLINE"]=implode(" ",$argv);
if(strpos($GLOBALS["COMMANDLINE"],"--verbose")>0){$GLOBALS["VERBOSE"]=true;$GLOBALS["debug"]=true;$GLOBALS["DEBUG"]=true;ini_set('html_errors',0);ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);}

if($argv[1]=='--amavis-port'){postfix_is_amavis_port($argv[2]);die();}

$unix=new unix();
$pidfile="/etc/artica-postfix/".basename(__FILE__).".pid";
$oldpid=@file_get_contents($pidfile);
if($unix->process_exists($oldpid)){writelogs("Already running pid $oldpid, Aborting");die();}
$pid=getmypid();
events("running $pid ");
file_put_contents($pidfile,$pid);
events("Memory: ".round(((memory_get_usage()/1024)/1000),2) ." after unix() declaration line: ".__LINE__);
$sock=new sockets();
events("Memory: ".round(((memory_get_usage()/1024)/1000),2) ." after sockets() declaration line: ".__LINE__);
$users=new settings_inc();
events("Memory: ".round(((memory_get_usage()/1024)/1000),2) ." after usersMenus() declaration line: ".__LINE__);
$_GET["server"]=$users->hostname;
$_GET["IMAP_HACK"]=array();
$GLOBALS["ZARAFA_INSTALLED"]=$users->ZARAFA_INSTALLED;
$GLOBALS["AMAVIS_INSTALLED"]=$users->AMAVIS_INSTALLED;

$GLOBALS["POP_HACK"]=array();
$GLOBALS["SMTP_HACK"]=array();
$GLOBALS["PHP5_BIN"]=LOCATE_PHP5_BIN2();
$GLOBALS["PostfixNotifyMessagesRestrictions"]=$sock->GET_INFO("PostfixNotifyMessagesRestrictions");
$GLOBALS["PopHackEnabled"]=$sock->GET_INFO("PopHackEnabled");
$GLOBALS["PopHackCount"]=$sock->GET_INFO("PopHackCount");
$GLOBALS["DisableMailBoxesHack"]=$sock->GET_INFO("DisableMailBoxesHack");
$GLOBALS["EnableArticaSMTPStatistics"]=$sock->GET_INFO("EnableArticaSMTPStatistics");
if(!is_numeric($GLOBALS["EnableArticaSMTPStatistics"])){$GLOBALS["EnableArticaSMTPStatistics"]=1;}
if(!is_numeric($GLOBALS["DisableMailBoxesHack"])){$GLOBALS["DisableMailBoxesHack"]=0;}
if($GLOBALS["PopHackEnabled"]==null){$GLOBALS["PopHackEnabled"]=1;}
if($GLOBALS["PopHackCount"]==null){$GLOBALS["PopHackCount"]=10;}
$GLOBALS["MYPATH"]=dirname(__FILE__);
$GLOBALS["SIEVEC_PATH"]=$unix->LOCATE_SIEVEC();
$GLOBALS["SMTP_HACK_CONFIG_RATE"]["NAME_SERVICE_NOT_KNOWN"]=10;
$GLOBALS["SMTP_HACK_CONFIG_RATE"]["SASL_LOGIN"]=15;
$GLOBALS["SMTP_HACK_CONFIG_RATE"]["RBL"]=5;
$GLOBALS["SMTP_HACK_CONFIG_RATE"]["USER_UNKNOWN"]=10;
$GLOBALS["SMTP_HACK_CONFIG_RATE"]["BLOCKED_SPAM"]=5;
$GLOBALS["SMTP_HACK_CONFIG_RATE"]["SMTPHACK_TIMEOUT"]=10;
$GLOBALS["SMTP_HACK_CONFIG_RATE"]["SMTPHACK_RESOLUTION_FAILURE"]=2;
$GLOBALS["SMTP_HACK_CONFIG_RATE"]["SMTPHACK_TOO_MANY_ERRORS"]=10;
smtp_hack_reconfigure();
$GLOBALS["CLASS_UNIX"]=$unix;
$GLOBALS["postfix_bin_path"]=$unix->find_program("postfix");
$GLOBALS["NOHUP_PATH"]=$unix->find_program("nohup");
@mkdir("/var/log/artica-postfix/smtp-connections",0755,true);

@mkdir("/etc/artica-postfix/cron.1",0755,true);
@mkdir("/etc/artica-postfix/cron.2",0755,true);
$users=null;
$sock=null;
$unix=null;
events("Memory: ".round(((memory_get_usage()/1024)/1000),2) ." after all declarations ".__LINE__);




$pipe = fopen("php://stdin", "r");
while(!feof($pipe)){
	$buffer=fgets($pipe, 4096);
	Parseline($buffer);
	$buffer=null;
}

fclose($pipe);
events("Shutdown...");
die();
function Parseline($buffer){
	
$buffer=trim($buffer);
if($buffer==null){return null;}

if(is_file("/var/log/artica-postfix/smtp-hack-reconfigure")){smtp_hack_reconfigure();}

if(strpos($buffer,"Do you need to run 'sa-update'?")>0){amavis_sa_update($buffer);return;}
if(strpos($buffer,"Passed CLEAN {AcceptedOpenRelay}")>0){return;} 
if(strpos($buffer,"Valid PID file (")>0){return;} 
if(strpos($buffer,"]: SA dbg:")>0){return;} 
if(strpos($buffer,") SA dbg:")>0){return;} 
if(strpos($buffer,"enabling PIX workarounds: disable_esmtp delay_dotcrlf")>0){return;} 
if(strpos($buffer,"]: child: exiting: idle for")>0){return;} 
if(strpos($buffer,"]: master: child")>0){return;} 
if(strpos($buffer,") 2822.From: <")>0){return;} 
if(strpos($buffer,") Connecting to LDAP server")>0){return;} 
if(strpos($buffer,") connect_to_ldap: connected")>0){return;} 
if(strpos($buffer,") connect_to_ldap: bind")>0){return;} 
if(strpos($buffer,") Passed CLEAN, AM.PDP-SOCK [")>0){return;} 
if(strpos($buffer,") inspect_dsn: is a DSN")>0){return;}
if(strpos($buffer,": decided action=DUNNO NULL")>0){return;} 
if(strpos($buffer,"Mail::SpamAssassin::Plugin::Check")>0){return;} 
if(strpos($buffer,": decided action=PREPEND X-policyd-weight: using cached result;")>0){return;} 
//if(strpos($buffer,") SPAM-TAG, <")>0){return;} 
if(strpos($buffer,") mail checking ended: version_server=")>0){return;} 
if(strpos($buffer,") check_header:")>0){return;} 
if(strpos($buffer,") dkim: FAILED Author")>0){return;} 
if(strpos($buffer,") dkim: VALID Sender signature")>0){return;} 
if(strpos($buffer,") collect banned table")>0){return;} 
if(strpos($buffer,") p.path")>0){return;}  
if(strpos($buffer,") ask_av Using (ClamAV-clamd): CONTSCAN")>0){return;} 
if(strpos($buffer,") ClamAV-clamd: Connecting to socket")>0){return;} 
if(strpos($buffer,") ClamAV-clamd: Sending CONTSCAN")>0){return;}  
//if(strpos($buffer,") p00")>0){return;}  
//if(strpos($buffer,") TIMING [total")>0){return;} 
//if(strpos($buffer,") TIMING-SA total")>0){return;}   
if(strpos($buffer,") policy protocol:")>0){return;} 
if(strpos($buffer,"]: policy protocol:")>0){return;} 
if(strpos($buffer,") run_av (ClamAV-clamd)")>0){return;}
if(strpos($buffer,"Net::Server: Process Backgrounded")>0){return;}
if(strpos($buffer,"Net::Server:")>0){return;}
if(strpos($buffer,"user=postfix, EUID:")>0){return;}
if(strpos($buffer,"No \$altermime,")>0){return;}
if(strpos($buffer,"starting. /usr/local/sbin/amavisd")>0){return;}
if(strpos($buffer,"initializing Mail::SpamAssassin")>0){return;}
if(strpos($buffer,"Net::Server: Binding to UNIX socket file")>0){return;}
if(strpos($buffer,"SpamControl: init_pre_chroot on SpamAssassin done")>0){return;}
if(strpos($buffer,"Starting worker for LMTP request")>0){return;}
if(strpos($buffer,"LMTP thread exiting")>0){return;}
if(strpos($buffer,") truncating a message passed to SA at")>0){return;}
if(strpos($buffer,"loaded policy bank")>0){return;}
if(strpos($buffer,"process_request: fileno sock")>0){return;}
if(strpos($buffer,"AM.PDP  /var/amavis/")>0){return;}
if(strpos($buffer,") body hash: ")>0){return;}
//if(strpos($buffer,") spam_scan: score=")>0){return;}
if(strpos($buffer,") Cached virus check expired")>0){return;}
if(strpos($buffer,") blocking contents category is")>0){return;}
if(strpos($buffer,") do_notify_and_quar: ccat=")>0){return;}
if(strpos($buffer,") inspect_dsn: not a bounce")>0){return;}
if(strpos($buffer,") local delivery:")>0){return;} 
if(strpos($buffer,") DSN: NOTIFICATION: ")>0){return;}
if(strpos($buffer,") SEND via PIPE:")>0){return;}
if(strpos($buffer,") Checking for banned types and")>0){return;}
if(strpos($buffer,"skipping mailbox user")>0){return;}
if(strpos($buffer,"artica-plugin:")>0){return;} 
if(strpos($buffer,"success delivered trough 192.168.1.228:33559")>0){return;}
if(strpos($buffer,"skiplist: checkpointed /var/lib/cyrus/user")>0){return;}
if(strpos($buffer,"starttls: TLSv1 with cipher AES256-SHA (256/256 bits new)")>0){return;}
if(strpos($buffer,"lost connection after CONNECT from unknown")>0){return null;}
if(strpos($buffer,"lost connection after DATA from unknown")>0){return null;}
if(strpos($buffer,"lost connection after RCPT")>0){return null;}
if(strpos($buffer,"created decompress buffer of")>0){return null;}
if(strpos($buffer,"created compress buffer of")>0){return null;}
if(strpos($buffer,"SQUAT returned")>0){return null;}
if(strpos($buffer,"indexing mailbox user")>0){return null;}
if(strpos($buffer,"mystore: starting txn")>0){return null;}
if(strpos($buffer,"duplicate_mark:")>0){return null;}
if(strpos($buffer,"mystore: committing txn")>0){return null;}
if(strpos($buffer,"cyrus/tls_prune")>0){return null;}
if(strpos($buffer,"milter-greylist: reloading config file")>0){return null;}
if(strpos($buffer,"milter-greylist: reloaded config file")>0){return null;}
if(strpos($buffer,"skiplist: recovered")>0){return null;}
if(strpos($buffer,"milter-reject NOQUEUE < 451 4.7.1 Greylisting in action, please come back in")>0){return null;}
if(strpos($buffer,"extra modules loaded after daemonizing/chrooting")>0){return null;}
if(strpos($buffer,"exec: /usr/bin/php5")>0){return;}
if(strpos($buffer,"Found decoder for ")>0){return;}
if(strpos($buffer,"Internal decoder for ")>0){return;}
if(strpos($buffer,"indexing mailboxes")>0){return;}
if(strpos($buffer,"decided action=DUNNO multirecipient-mail - already accepted by previous query")>0){return;}
if(strpos($buffer,"decided action=PREPEND X-policyd-weight: passed - too many local DNS-errors")>0){return;}
if(strpos($buffer,"DSN: FILTER 554 Spam, spam level")>0){return;}
if(strpos($buffer,"emailrelay: info: no more messages to send")>0){return;}
if(strpos($buffer,"spamd: connection from ip6-localhost")>0){return;}
if(strpos($buffer,"spamd: processing message")>0){return;}
if(strpos($buffer,"spamd: clean message")>0){return;}
if(strpos($buffer,"spamd: result:")>0){return;}
if(strpos($buffer,"prefork: child states: I")>0){return;}
if(strpos($buffer,"autowhitelisted for another")>0){return;}
//if(strpos($buffer,"spamd: identified spam")>0){return;}
if(strpos($buffer,"spamd: handled cleanup of child pid")>0){return;}
if(strpos($buffer,"open_on_specific_fd")>0){return;}
if(strpos($buffer,"rundown_child on")>0){return;}
if(strpos($buffer,"switch_to_my_time")>0){return;}
if(strpos($buffer,"%, total idle")>0){return;}
if(strpos($buffer,"exec.mailarchive.php[")>0){return;}
if(strpos($buffer,"do_notify_and_quarantine: spam level exceeds")>0){return;}
if(strpos($buffer,", DEAR_SOMETHING=")>0){return;}
if(strpos($buffer,", DIGEST_MULTIPLE=")>0){return;}
if(strpos($buffer,", BAD_ENC_HEADER=")>0){return;}
if(strpos($buffer,"dkim: VALID")>0){return;}
if(strpos($buffer,"SA info: pyzor:")>0){return;}
if(strpos($buffer,"DSN: sender is credible")>0){return;}
if(strpos($buffer,"mail_via_pipe")>0){return;}
if(strpos($buffer,") ...continue")>0){return;}
if(strpos($buffer,"Cached spam check expired")>0){return;}
if(strpos($buffer,") cached")>0){return;}
if(strpos($buffer,"extra modules loaded:")>0){return;}
if(strpos($buffer,"Use of uninitialized value")>0){return;}
if(strpos($buffer,"DecodeShortURLs")>0){return;}
if(strpos($buffer,"FWD via SMTP: <")>0){return;}
if(strpos($buffer,"DKIM-Signature header added")>0){return;}
if(strpos($buffer,"Passed CLEAN, MYNETS LOCAL")>0){return;}
if(strpos($buffer,") Passed CLEAN, [")>0){return;}
if(strpos($buffer,") Passed BAD-HEADER, [")>0){return;}
if(strpos($buffer,") Checking: ")>0){return;}
if(strpos($buffer,") WARN: MIME::Parser error: unexpected end of header")>0){return;}
if(strpos($buffer,") Open relay? Nonlocal recips but not originating")>0){return;}
if(strpos($buffer,": not authenticated")>0){return;}
if(strpos($buffer,": dk_eom() returned status")>0){return;}
if(strpos($buffer,"ASN1_D2I_READ_BIO:not enough data")>0){return;}
if(strpos($buffer,"SpamControl: init_pre_fork on SpamAssassin done")>0){return;}
if(strpos($buffer,": Selected group:")>0){return;}
if(strpos($buffer,"Message entity scanning: message CLEAN")>0){return;}
if(strpos($buffer,"New connection on thread")>0){return;}
//if(strpos($buffer,"AM.PDP-SOCK/MYNETS")>0){return;}
if(strpos($buffer,": disconnect from")>0){return;} 
if(strpos($buffer,"sfupdates: KASINFO")>0){return;} 
if(strpos($buffer,": lost connection after CONNECT")>0){return;} 
if(strpos($buffer,"enabling PIX workarounds: disable_esmtp delay_dotcrlf")>0){return;} 
if(strpos($buffer,"Message Aborted!")>0){return;} 
if(strpos($buffer,"WHITELISTED [")>0){return;}
if(strpos($buffer,"COMMAND PIPELINING from")>0){return;}
if(strpos($buffer,"COMMAND COUNT LIMIT from [")>0){return;}
if(strpos($buffer,"]: warning: psc_cache_update:")>0){return;}
if(strpos($buffer,"]: PREGREET")>0){return;}
if(strpos($buffer,": PASS OLD [")>0){return;}
if(strpos($buffer,"]: DNSBL rank")>0){return;}
if(strpos($buffer,"]: HANGUP after")>0){return;}
if(strpos($buffer,": DISCONNECT [")>0){return;}
if(strpos($buffer,"KASNOTICE")>0){return;}
if(strpos($buffer,"KASINFO")>0){return;}
if(strpos($buffer,"]: PASS NEW [")>0){return;}
if(strpos($buffer,"]: COMMAND TIME LIMIT from")>0){return;}
if(strpos($buffer,"Client host triggers FILTER")>0){return;}
if(strpos($buffer,"Starting worker process for IMAP request")>0){return;}
if(strpos($buffer,"IMAP thread exiting")>0){return;}
if(strpos($buffer,"Client disconnected")>0){return;}
if(strpos($buffer,"starting the Postfix mail system")>0){return;}
if(strpos($buffer,"Postfix mail system is already running")>0){return;}
if(strpos($buffer,": Perl version")>0){return;}
if(strpos($buffer,": No decoder for")>0){return;}
if(strpos($buffer,"Using primary internal av scanner")>0){return;}
if(strpos($buffer,"starting.  /usr/local/sbin/amavisd")>0){return;}

if(preg_match("#kavmilter\[.+?\[tid.+?New message from:#",$buffer,$re)){return null;}
if(preg_match("#assp\[.+?LDAP Results#",$buffer,$re)){return null;}
if(preg_match("#smtpd\[.+?\]: disconnect from#",$buffer,$re)){return null;}
if(preg_match("#smtpd\[.+?\]: connect from#",$buffer,$re)){return null;}
if(preg_match("#smtpd\[.+?\]: timeout after END-OF-MESSAGE#",$buffer,$re)){return null;}
if(preg_match("#smtpd\[.+?\]:.+?enabling PIX workarounds#",$buffer,$re)){return null;}
if(preg_match("#milter-greylist:.+?skipping greylist#",$buffer,$re)){return null;}
if(preg_match("#milter-greylist:\s+\(.+?greylisted entry timed out#",$buffer,$re)){return null;}
if(preg_match("#postfix\/qmgr\[.+?\]:\s+.+?: removed#",$buffer,$re)){return null;}
if(preg_match("#postfix\/smtpd\[.+?\]:\s+lost connection after#",$buffer,$re)){return null;}
if(preg_match("#assp.+?\[MessageOK\]#",$buffer,$re)){return null;}
if(preg_match("#assp.+?\[NoProcessing\]#",$buffer,$re)){return null;}
if(preg_match("#passed trough amavis and event is saved#",$buffer,$re)){return null;}
if(preg_match("#assp.+?AdminUpdate#",$buffer,$re)){return null;}
if(preg_match("#last message repeated.+?times#",$buffer,$re)){return null;}
if(preg_match("#cyrus\/master.+?about to exec#",$buffer,$re)){return null;}
if(preg_match("#cyrus\/.+?open: user#",$buffer,$re)){return null;}
if(preg_match("#cyrus\/lmtpunix.+?accepted connection#",$buffer,$re)){return null;}
if(preg_match("#cyrus\/lmtpunix.+?Delivered:#",$buffer,$re)){return null;}
if(preg_match("#cyrus\/master.+?process.+?exited#",$buffer,$re)){return null;}
if(preg_match("#lmtpunix.+?mystore: starting txn#",$buffer,$re)){return null;}
if(preg_match("#lmtpunix.+?duplicate_mark#",$buffer,$re)){return null;}
if(preg_match("#lmtpunix.+?mystore: committing txn#",$buffer,$re)){return null;}
if(preg_match("#ctl_cyrusdb.+?archiving#",$buffer,$re)){return null;}
if(preg_match("#assp.+?LDAP - found.+?in LDAPlist;#",$buffer,$re)){return null;}
if(preg_match("#anvil.+?statistics: max#",$buffer,$re)){return null;}
if(preg_match("#smfi_getsymval failed for#",$buffer)){return null;}
if(preg_match("#cyrus\/imap\[.+?Expunged\s+[0-9]+\s+message.+?from#",$buffer)){return null;}
if(preg_match("#cyrus\/imap\[.+?seen_db:\s+#",$buffer)){return null;}
if(preg_match("#cyrus\/[pop3|imap]\[.+?SSL_accept\(#",$buffer)){return null;}
if(preg_match("#cyrus\/[pop3|imap]\[.+?starttls:#",$buffer)){return null;}
if(preg_match("#cyrus\/[pop3|imap]\[.+?:\s+inflate#",$buffer)){return null;}
if(preg_match("#cyrus\/.+?\[.+?:\s+accepted connection$#",$buffer)){return null;}
if(preg_match("#cyrus\/.+?\[.+?:\s+deflate\(#",$buffer)){return null;}
if(preg_match("#cyrus\/.+?\[.+?:\s+\=>\s+compressed to#",$buffer)){return null;}
if(preg_match("#filter-module\[.+?:\s+KASINFO#",$buffer)){return null;}
if(preg_match("#exec\.mailbackup\.php#",$buffer)){return null;}
if(preg_match("#kavmilter\[.+?\]:\s+Loading#",$buffer)){return null;}
if(preg_match("#DBERROR: init.+?on berkeley#",$buffer)){return null;}
if(preg_match("#FATAL: lmtpd: unable to init duplicate delivery database#",$buffer)){return null;}
if(preg_match("#skiplist: checkpointed.+?annotations\.db#",$buffer)){return null;}
if(preg_match("#duplicate_prune#",$buffer)){return null;}
if(preg_match("#cyrus\/cyr_expire\[[0-9]+#",$buffer)){return null;}
if(preg_match("#cyrus\/imap.+?SSL_accept#",$buffer)){return null;}
if(preg_match("#cyrus\/pop3.+?SSL_accept#",$buffer)){return null;}
if(preg_match("#cyrus\/imap.+?:\s+executed#",$buffer)){return null;}
if(preg_match("#cyrus\/ctl_cyrusdb.+?recovering cyrus databases#",$buffer)){return null;}
if(preg_match("#cyrus.+?executed#",$buffer)){return null;}
if(preg_match("#postfix\/.+?refreshing the Postfix mail system#",$buffer)){return null;}
if(preg_match("#master.+?reload -- version#",$buffer)){return null;}
if(preg_match("#SQUAT failed#",$buffer)){return null;}
if(preg_match("#lmtpunix.+?sieve\s+runtime\s+error\s+for#",$buffer)){return null;}
if(preg_match("#imapd:Loading hard-coded DH parameters#",$buffer)){return null;}
if(preg_match("#ctl_cyrusdb.+?checkpointing cyrus databases#",$buffer)){return null;}
if(preg_match("#idle for too long, closing connection#",$buffer)){return null;}
if(preg_match("#amavis\[.+?Found#",$buffer)){return null;}
if(preg_match("#amavis\[.+?Module\s+#",$buffer)){return null;}
if(preg_match("#amavis\[.+?\s+loaded$#",trim($buffer))){return null;}
if(preg_match("#amavis\[.+?\s+Internal decoder#",trim($buffer))){return null;}
if(preg_match("#amavis\[.+?\s+Creating db#",trim($buffer))){return null;}
if(preg_match("#smtpd\[.+? warning:.+?address not listed for hostname#",$buffer)){return null;}

if(preg_match("#postfix\/policyd-weight\[.+?SPAM#",$buffer)){return null;}
if(preg_match("#postfix\/policyd-weight\[.+?decided action=550#",$buffer)){return null;}
if(preg_match("#qmgr\[.+?: removed#",$buffer)){return null;}
if(preg_match("#cyrus\/lmtp\[.+?Delivered#",$buffer)){return null;}
if(preg_match("#ESMTP::.+?\/var\/amavis\/tmp\/amavis#",$buffer)){return null;}
if(preg_match("#zarafa-dagent.+?Client disconnected#",$buffer)){return null;}



if(regex_amavis($buffer)){return;}








if(preg_match("#zarafa-server.+?SQL Failed: Can't create table '\./zarafa/(.+?)\.frm'#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/zarafa-server.tablefailed".md5($re[1]);
	if($timefile>10){
		email_events("Zarafa server SQL issue unable to create [{$re[1]}] table",
		"zarafa-server claim \n$buffer\nThere is an SQL issue\nplease Check Artica Technology support service.","mailbox");
		@file_put_contents($file,"#");
		}else{events("Zarafa-server SQL issue {$re[1]} {$timefile}Mn/5Mn");}
	return;	
}

if(preg_match("#zarafa-server.+?SQL Failed:(.+)#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/zarafa-server.".md5($re[1]);
	if($timefile>10){
		email_events("Zarafa server SQL issue",
		"zarafa-server claim \n$buffer\nThere is an SQL issue\nplease Check Artica Technology support service.","mailbox");
		@file_put_contents($file,"#");
		}else{events("Zarafa-server SQL issue {$re[1]} {$timefile}Mn/5Mn");}
	return;			
}
	
if(preg_match("#(.+?)\/smtpd\[.+?fatal:\s+config variable inet_interfaces#", $buffer)){
	$file="/etc/artica-postfix/croned.1/postfix.error.inet_interfaces";
	events("inet_interfaces issues' '{$re[1]}'");
	$timefile=file_time_min($file);
	if($timefile>10){
		email_events("{$re[1]}: misconfiguration on inet_interfaces",
		"Postfix claim \n$buffer\n\nIf this event is resended\nplease Check Artica Technology support service.","postfix");
		@file_put_contents($file,"#");
		if($re[1]=="postfix"){
			$cmd=trim("{$GLOBALS["NOHUP_PATH"]} {$GLOBALS["PHP5_BIN"]} /usr/share/artica-postfix/exec.postfix.maincf.php --interfaces >/dev/null 2>&1 &");
			events("$cmd");
			shell_exec($cmd);
		}else{
			$cmd=trim("{$GLOBALS["NOHUP_PATH"]} {$GLOBALS["PHP5_BIN"]} /usr/share/artica-postfix/exec.postfix-multi.php >/dev/null 2>&1 &");
			events("$cmd");
			shell_exec($cmd);
		}
	}
	return;			
}

	if(preg_match("#\]:\s+bayes: cannot open bayes databases\s+(.+?)\/bayes_.+?R\/.+?: tie failed.+?Permission denied#", $buffer,$re)){
		events("cannot open bayes databases , Permission denied' '{$re[1]}/bayes_*'");
		shell_exec("/bin/chown postfix:postfix {$re[1]}/bayes*");
		return;
	}


	if(preg_match("#\]:\s+bayes: cannot open bayes databases\s+(.+?)\/bayes_.+?R\/O: tie failed#", $buffer,$re)){
		events("cannot open bayes databases , unlink '{$re[1]}/bayes_seen' '{$re[1]}/bayes_toks'");
		if(is_file("{$re[1]}/bayes_seen")){@unlink("{$re[1]}/bayes_seen");}
		if(is_file("{$re[1]}/bayes_toks")){@unlink("{$re[1]}/bayes_toks");}
		return;
	}
	
	


	if(preg_match("#zarafa-gateway.+?Unable to negotiate SSL connection#", $buffer,$re)){
		$file="/etc/artica-postfix/croned.1/zarafa-gateway.Unable.to.negotiate.SSL.connection";
		$timefile=file_time_min($file);
		if($timefile>10){
				email_events("Zarafa IMAP/POP3 SSL issue",
				"zarafa-gateway claim \n$buffer\nThere is an SSL issue\nplease Check Artica Technology support service.","mailbox");
				@file_put_contents($file,"#");
			}else{events("Zarafa IMAP/POP3 Unable to negotiate SSL connection {$timefile}Mn/5Mn");}
		return;			
	}


	if(preg_match("#smtpd\[.+?warning:\s+connect to Milter service unix:\/var\/spool\/postfix\/var\/run\/amavisd-milter\/amavisd-milter\.sock: No such file or directory#", $buffer,$re)){
		$file="/etc/artica-postfix/croned.1/postfix.amavisd-milter.sock.No.such.file.or.directory";
		$timefile=file_time_min($file);
		if($timefile>10){
			$amavis=amavisd_milter_bin_path();
			if(strlen($amavis)<5){
				email_events("Postfix: amavisd-milter is not installed !, change the postfix method",
				"postfix claim \n$buffer\nit seems that amavisd-milte is not installed\nYou should re-install amavis or just\nChange amavis hooking to after-queue in order to use amavis main daemon.","postfix");
				@file_put_contents($file,"#");
				return;
			}
		}
	}



	if(preg_match("#\[.+?:\s+connect to 127\.0\.0\.1\[127\.0\.0\.1\]:2003:\s+Connection refused#", $buffer,$re)){
		$file="/etc/artica-postfix/croned.1/postfix.port.2003.Connection.refused";
		$timefile=file_time_min($file);
		if($timefile>5){
				email_events("Postfix: Connect to zarafa LMTP port Connection refused zarafa-lmtp will be restarted",
				"postfix claim \n$buffer\nArtica will try to restart zarafa-lmtp daemon.","postfix");
				shell_exec(trim("{$GLOBALS["NOHUP_PATH"]} /etc/init.d/artica-postfix restart zarafa-lmtp >/dev/null 2>&1 &"));
				@file_put_contents($file,"#");
			}else{events("Postfix: Connect to zarafa LMTP port Connection refused: {$timefile}Mn/5Mn");}
		return;			
		}



if(preg_match("#smtp\[.+?:\s+connect to 127\.0\.0\.1\[127\.0\.0\.1\]:([0-9]+):\s+Connection refused#", $buffer,$re)){
	if(postfix_is_amavis_port($re[1])){
		$file="/etc/artica-postfix/croned.1/postfix.port.{$re[1]}.Connection.refused";
		$timefile=file_time_min($file);
		if($timefile>5){
			email_events("Postfix: Connect to amavis port {$re[1]} Connection refused Amavis will be restarted",
			"postfix claim \n$buffer\nArtica will try to restart amavis daemon.","postfix");
			shell_exec(trim("{$GLOBALS["NOHUP_PATH"]} /etc/init.d/artica-postfix restart amavis --by-exec-maillog >/dev/null 2>&1 &"));
			@file_put_contents($file,"#");
		}else{events("Postfix: Connect to amavis port {$re[1]} Connection refused: {$timefile}Mn/5Mn");}
		return;			
		
	}
}
	



if(preg_match("#cyrus\/.+?\[[0-9]+]#",$buffer)){
	include_once(dirname(__FILE__)."/ressources/class.cyrus.maillog.inc");
	$cyrus=new cyrus_maillog();
	if($cyrus->ParseBuffer($buffer)){return;}
	}
	
if(preg_match("#master\[.+?fatal: bind 127.0.0.1 port 33559: Address already in use#", $buffer,$re)){
	events("Postfix: bind 127.0.0.1 port 33559: Address already in use -> startit");
	shell_exec(trim("{$GLOBALS["NOHUP_PATH"]} {$GLOBALS["postfix_bin_path"]} start >/dev/null 2>&1 &"));
	return;
}	


if(preg_match("#postqueue.+?warning: Mail system is down#", $buffer,$re)){
	events("Postfix: Mail system is down:  -> startit");
	shell_exec(trim("{$GLOBALS["NOHUP_PATH"]} {$GLOBALS["postfix_bin_path"]} start >/dev/null 2>&1 &"));
	return;
}	
	
if(preg_match("#postscreen.+?warning: database\s+(.+?):\s+could not delete entry for#", $buffer,$re)){
	events("Postscreen: Cache database failed");
	if(is_file($re[1])){
		@unlink($re[1]);
		email_events("Postfix: postscreen_cache_map problem",
		"postfix claim \n$buffer\nArtica have deleted {$re[1]} file to fix this issue.","postfix");
	}
}


if(preg_match("#fatal: dict_open: unsupported dictionary type: pcre:  Is the postfix-pcre package installed#i",$buffer,$re)){
	events("Postfix: pcre missing");
	$file="/etc/artica-postfix/croned.1/postfix.pcre.missing";
	$timefile=file_time_min($file);
	if($timefile>20){
		email_events("Postfix: pcre missing",
		"postfix claim \n$buffer\nArtica will try to upgrade postfix.","postfix");
		shell_exec(trim("{$GLOBALS["NOHUP_PATH"]} /usr/share/artica-postfix/bin/artica-make APP_POSTFIX >/dev/null 2>&1 &"));
		@file_put_contents($file,"#");
	}else{events("Postfix: pcre missing: {$timefile}Mn/20Mn");}
	return;			
}

if(preg_match("#zarafa-server.+?The recommended upgrade procedure is to use the zarafa7-upgrade commandline tool#",$buffer,$re)){
	
	$cmd=trim("{$GLOBALS["NOHUP_PATH"]} {$GLOBALS["PHP5_BIN"]} /usr/share/artica-postfix/exec.zarafa-migrate.php --upgrade-7 >/dev/null 2>&1 &");
	events("zarafa-server, need to upgrade... -> $cmd");
	shell_exec($cmd);
}


if(preg_match("#zarafa-gateway.+?POP3, POP3S, IMAP and IMAPS are all four disabled#",$buffer,$re)){
	events("Zarafa-gateway No services enabled...???");
	$file="/etc/artica-postfix/croned.1/zarafa-gateway.no.services";
	$timefile=file_time_min($file);
	if($timefile>10){
		email_events("Zarafa mail server: No mailbox protocol ?",
		"Zarafa claim \n$buffer\nYou have disabled all mailboxes protocols.\nMeans that zarafa-gateway is not necessary ???\nAre you sure ??","mailbox");
		@file_put_contents($file,"#");
	}else{events("Postfix: Zarafa-gateway No services enabled...: {$timefile}Mn/10Mn");}
	return;			
}


if(preg_match("#kavmilter\[.+?Cannot read template file:\s+(.+?)$#",$buffer,$re)){
	events("kavmilter: {$re[1]} missing");
	$md=md5($re[1]);
	$file="/etc/artica-postfix/croned.1/kavmilter.template.$md";
	$timefile=file_time_min($file);
	if($timefile>5){
		email_events("Kaspersky Milter: error template ".basename($re[1]),
		"kavmilter claim \n$buffer\nArtica will try to repair.","postfix");
		shell_exec("/bin/touch {$re[1]}");
		shell_exec(trim("{$GLOBALS["NOHUP_PATH"]} {$GLOBALS["PHP5_BIN"]} /usr/share/artica-postfix/exec.kavmilter.php --templates >/dev/null 2>&1 &"));
		@file_put_contents($file,"#");
	}else{events("kavmilter: {$re[1]} missing: {$timefile}Mn/5Mn");}
	return;		
}



if(preg_match("#kavmilter\[.+?Can't load keys: No active key. Only skip actions allowed#",$buffer,$re)){
	events("kavmilter: key missing");
	$md=md5($re[1]);
	$file="/etc/artica-postfix/croned.1/kavmilter.no-active-key.error";
	$timefile=file_time_min($file);
	if($timefile>10){
		email_events("Kaspersky Milter: no license !!",
		"kavmilter claim \n$buffer\nPlease disable kavmilter plugin or perform a license key activation","postfix");
		@file_put_contents($file,"#");
	}else{events("kavmilter: kavmilter: key missing: {$timefile}Mn/5Mn");}
	return;		
}



if(preg_match("#problem talking to server\s+127\.0\.0\.1:10040: Connection refused#",$buffer,$re)){
	events("Postfix: Postfwd2 issue... -> Connection refused");
	
	$file="/etc/artica-postfix/croned.1/postfix.postfwd2.Connection.refused";
	$timefile=file_time_min($file);
	if($timefile>5){
		email_events("Postfix: postfwd2 plugin is not available",
		"Postfix claim \n$buffer\nArtica will try to start postfwd2.","postfix");
		shell_exec("/etc/init.d/artica-postfix start postfwd2 &");
		@file_put_contents($file,"#");
	}else{events("Postfix: Postfwd2 issue... -> Connection refused: {$timefile}Mn/5Mn");}
	return;	
}


if(preg_match("#warning:.+?then you may have to chmod a\+r\s+(.+?)$#",$buffer,$re)){
	events("chmod a+r {$re[1]}");
	shell_exec("/bin/chmod a+r {$re[1]}");
	return;
}

if(preg_match("#imaps\[.+?Fatal error: tls_start_servertls.+?failed#",$buffer,$re)){
	events("Cyrus-imap : IMAP SSL FAILED");
	$file="/etc/artica-postfix/croned.1/imaps.error.tls_start_servertls";
	$timefile=file_time_min($file);
	if($timefile>5){
		shell_exec(trim("{$GLOBALS["NOHUP_PATH"]} {$GLOBALS["PHP5_BIN"]} /usr/share/artica-postfix/exec.cyrus.php --imaps-failed >/dev/null 2>&1 &"));
		@unlink($file);
		@file_put_contents($file,"#");
	}else{events("Cyrus-imap wait:{$timefile}Mn/5Mn");}
	return;		
}

if(preg_match("#fatal: file.+?main\.cf: parameter setgid_group: unknown group name:\s+(.+)#",$buffer,$re)){
	events("Postfix : group name {$re[1]} problem");
	$file="/etc/artica-postfix/croned.1/postfix.group.{$re[1]}.error";
	$timefile=file_time_min($file);
	if($timefile>5){
		email_events("Postfix: group {$re[1]} is not available",
		"Postfix claim \n$buffer\nArtica will try create this group.","postfix");
		$unix=new unix();
		$groupadd=$unix->find_program("groupadd");
		shell_exec("$groupadd {$re[1]}&");
		@file_put_contents($file,"#");
	}else{events("Postfix: Postfix: group {$re[1]} is not available: {$timefile}Mn/5Mn");}
	return;		
}


if(preg_match("#fatal: parameter inet_interfaces: no local interface found for ([0-9\.]+)#i",$buffer,$re)){
	events("Postfix : NIC {$re[1]} problem");
	$file="/etc/artica-postfix/croned.1/postfix.interface.{$re[1]}.error";
	$timefile=file_time_min($file);
	if($timefile>5){
		email_events("Postfix: Interface {$re[1]} is not available",
		"Postfix claim \n$buffer\nArtica will try to restore TCP/IP interfaces.","postfix");
		shell_exec(trim("{$GLOBALS["NOHUP_PATH"]} {$GLOBALS["PHP5_BIN"]} /usr/share/artica-postfix/exec.virtuals-ip.php >/dev/null 2>&1 &"));
		@unlink($file);
		@file_put_contents($file,"#");
	}else{events("Postfix: Interface {$re[1]} is not available: {$timefile}Mn/5Mn");}
	return;		
}


if(preg_match("#qmgr\[.+?fatal: incorrect version of Berkeley DB: compiled against.+?run-time linked against#i",$buffer,$re)){
	events("Postfix : incorrect version of Berkeley DB");
	$file="/etc/artica-postfix/croned.1/qmgr.error.Berkeley";
	$timefile=file_time_min($file);
	if($timefile>5){
		email_events("Postfix: incorrect version of Berkeley DB",
		"Postfix claim \n$buffer\nArtica will upgrade/re-install your postfix version.","postfix");
		@unlink($file);
		shell_exec(trim("{$GLOBALS["NOHUP_PATH"]} /usr/share/artica-postfix/bin/artica-make APP_POSTFIX 2>&1 &"));
		@file_put_contents($file,"#");
	}else{events("Postfix : incorrect version of Berkeley DB wait:{$timefile}Mn/5Mn");}
	return;		
}
if(preg_match('#smtpd\[.+? warning: unknown smtpd restriction: "(.+?)"#',$buffer,$re)){
	events("Postfix : incorrect parameters on smtpd restriction");
	$file="/etc/artica-postfix/croned.1/smtpd.error.restriction." .md5($re[1]);
	$timefile=file_time_min($file);
	if($timefile>5){
		email_events("Postfix: incorrect parameters on smtpd restriction",
		"Postfix claim \n$buffer\nArtica will try to fix the problem.\nif this error is sended again, please contact Artica Support team.","postfix");
		@unlink($file);
		shell_exec(trim("{$GLOBALS["PHP5_BIN"]} /usr/share/artica-postfix/exec.postfix.maincf.php --smtp-sender-restrictions &"));
		@file_put_contents($file,"#");
	}else{events("Postfix : incorrect parameters on smtpd restriction wait:{$timefile}Mn/5Mn");}
	return;		
}
if(preg_match('#spamc\[.+?connect to spamd on (.+?)\s+failed,.+?Connection refused#',$buffer,$re)){
	events("Spamassassin : {$re[1]} Connection refused");
	$file="/etc/artica-postfix/croned.1/spamc.error.cnx.refused." .md5($re[1]);
	$timefile=file_time_min($file);
	if($timefile>5){
		email_events("Spamassassin: Connection refused on {$re[1]}",
		"Spamassassin claim \n$buffer\nYou should have less issues and better performances using Amavisd-new instead Spamassassin only","postfix");
		@unlink($file);
		@file_put_contents($file,"#");
	}else{events("Spamassassin : {$re[1]} Connection refused wait:{$timefile}Mn/5Mn");}
	return;		
}





if(preg_match("#smtpd\[.+?warning: connect to 127.0.0.1:54423: Connection refused#",$buffer,$re)){
	events("restart Artica-policy");
	shell_exec("/etc/init.d/artica-postfix restart artica-policy &");
	return;
}



if(preg_match("#nss_wins\[.+?connect from (.+?)\[(.+?)\]#",$buffer,$re)){
	Postfix_Addconnection($re[1],$re[2]);
	return;
}

if(preg_match("#nss_wins\[.+?warning: (.+?):\s+address not listed for hostname\s+(.+?)$#",$buffer,$re)){
	Postfix_Addconnection_error($re[2],$re[1],"ADDR_NOT_LISTED1");
	return;
}

if(preg_match("#postscreen\[.+?CONNECT from \[(.+?)\]#",$buffer,$re)){
	Postfix_Addconnection(null,$re[1]);
	return;
}

if(preg_match("#dnsblog\[.+?addr\s+(.+?)\s+listed by domain#",$buffer,$re)){
	Postfix_Addconnection_error(null,$re[1],"RBL");
	return;
}

if(preg_match("#nss_wins\[.+?warning: (.+?):\s+hostname\s+(.+?)\s+verification failed: Name or service not known#",$buffer,$re)){
	//"verification failed: Name or service not known"
	Postfix_Addconnection_error($re[2],$re[1],"VERIFY_FAILED1");
	return;
}

if(preg_match("#nss_wins\[.+?timeout after DATA.+?from\s+(.+?)\[(.+?)\]#",$buffer,$re)){
	//"verification failed: Name or service not known"
	Postfix_Addconnection_error($re[1],$re[2],"TIMEOUT");
	return;
}

if(strpos($buffer,"connect to Milter service inet:127.0.0.1:1052: Connection refused")>0){
	events("KavMilter stopped !");
	$md5=md5("connect to Milter service inet:127.0.0.1:1052: Connection refused");
	$file="/etc/artica-postfix/croned.1/postfix.milter.$md5";
	$timefile=file_time_min($file);
	if($timefile>5){
		email_events("Postfix: Kaspersky Antivirus For Postfix daemon is not available",
		"Postfix claim \n$buffer\nArtica will restart it's daemon.","postfix");
		@unlink($file);
		shell_exec("/etc/init.d/kavmilterd restart &");
		file_put_contents($file,"#");
		
	}else{
		events("connect to Milter service inet:127.0.0.1:1052: Connection refused :{$timefile}Mn/5Mn to wait");
	}
	return;	
}

if(preg_match("#problem talking to server .+?:10040: Connection timed out#",$buffer)){
	events("postfwd2 problem Connection timed out !");
	$md5=md5("problem talking to server .+?:10040: Connection timed out");
	$file="/etc/artica-postfix/croned.1/postfix.postfwd2.$md5";
	$timefile=file_time_min($file);
	if($timefile>5){
		email_events("Postfix: postfwd2 Postfix daemon is not available",
		"Postfix claim \n$buffer\nArtica will restart it's daemon.","postfix");
		@unlink($file);
		shell_exec($GLOBALS["PHP5_BIN"]." /usr/share/artica-postfix/exec.postfwd2.php --restart &");
		file_put_contents($file,"#");
		
	}else{
		events("connect to talking to server .+?:10040 :{$timefile}Mn/5Mn to wait");
	}
	return;		
}

if(preg_match("#postfix.+?fatal: non-null host address bits in.+?([0-9\.\/]+)\", perhaps you should use \"(.+?)\"\s+instead#",$buffer,$re)){
	events("NetWork & Nics, need to change from {$re[1]} to {$re[2]}");
	$md5=md5("{$re[1]}{$re[2]}");
	$file="/etc/artica-postfix/croned.1/postfix.network.$md5";
	$timefile=file_time_min($file);
	if($timefile>5){
		email_events("Postfix: Bad network parameter you have set {$re[1]} you need to set {$re[2]} instead !",
		"Postfix claim \n$buffer\n","postfix");
		@unlink($file);
		file_put_contents($file,"#");
	}else{
		events("Bad network parameter you have set {$re[1]} you need to set {$re[2]} instead :{$timefile}Mn");
	}
	return;	
}

if(preg_match("#postfix\/master\[.+?fatal:\s+open lock file\s+(.+?): unable to set exclusive lock: Resource temporarily unavailable#",$buffer,$re)){
	events("postfix: {$re[1]}, unable to set exclusive lock");
	$re[1]=trim($re[1]);
	$md5=md5("postfix: {$re[1]} unable to set exclusive lock");
	$file="/etc/artica-postfix/croned.1/postfix.error.$md5";
	$timefile=file_time_min($file);
	if($timefile>5){
		exec("{$GLOBALS["PHP5_BIN"]} /usr/share/artica-postfix/exec.postfix.maincf.php --repair-locks",$results);
		email_events("Postfix: {$re[1]} unable to set exclusive lock",
		"Postfix claim \n$buffer\nArtica tried to repair it\n".@implode("\n", $results),"postfix");
		if(is_file($re[1])){@unlink($re[1]);}
		@unlink($file);
		file_put_contents($file,"#");
	}else{
		events("postfix: {$re[1]} unable to set exclusive lock instead wait:{$timefile}Mn");
	}
	return;	
}
// ##########################  emailrelay 


if(preg_match("#emailrelay:\s+error:\s+polling:\s+cannot stat\(\)\s+file:\s+(.+)#",$buffer,$re)){
	events("emailrelay: ".basename($re[1])." corrupted file");
	shell_exec("{$GLOBALS["PHP5_BIN"]} /usr/share/artica-postfix/exec.emailrelay.php --corrupted \"{$re[1]}\" &");
	return;
}

if(preg_match("#emailrelay\[(.+?)\].+?emailrelay: error:\s+(.+)#",$buffer,$re)){
	if(strpos("$buffer","cannot stat")>0){return;}
	events("emailrelay PID {$re[1]} Error:Mass Mailing {$re[2]}");
	email_events("emailrelay PID {$re[1]} Error:Mass Mailing {$re[2]}","emailrelay claim \n$buffer\nCheck your configuration file","emailrelay");
	return;
}
if(preg_match("#emailrelay\[(.+?)\].+?emailrelay: warning:\s+(.+)#",$buffer,$re)){
	if(strpos("$buffer","cannot stat")>0){return;}
	events("emailrelay PID {$re[1]} Error:Mass Mailing {$re[2]}");
	email_events("emailrelay PID {$re[1]} Error:Mass Mailing {$re[2]}","emailrelay claim \n$buffer\nCheck your configuration file","emailrelay");
	return;
} 

// ##########################

if(strpos($buffer,"warning: to change inet_interfaces, stop and start Postfix")>0){
	events("inet_interfaces: restarting postfix");
	shell_exec("{$GLOBALS["postfix_bin_path"]} stop && {$GLOBALS["postfix_bin_path"]} start &");
	return;
}

if(preg_match("#(.+?)\/smtpd.+?fatal: bad string length.+? inet_interfaces =#",$buffer,$re)){
	
	if($re[1]=="postfix"){
		$instance="master";
		$cmd="{$GLOBALS["PHP5_BIN"]} /usr/share/artica-postfix/exec.postfix.maincf.php --interfaces";
	}else{
		if(preg_match("#postfix-(.+)#",$re[1],$ri)){
			$cmd="{$GLOBALS["PHP5_BIN"]} /usr/share/artica-postfix/exec.postfix-multi.php --instance-reconfigure {$ri[1]}";
			$instance=$ri[1];
		}
	}
	events("$instance:inet_interfaces is null ?? in postfix configuration file, try to repair");
	$file="/etc/artica-postfix/croned.1/postfix.$instance.inet_interfaces.null";
	$timefile=file_time_min($file);
	if($timefile>5){
		events("$cmd");
		email_events("$instance: inet_interfaces missing data parameter","Postfix claim \n$buffer\nArtica will change value to \"all\"","postfix");
		shell_exec("$cmd &");	
		@unlink($file);
		file_put_contents($file,"#");
	}else{
		events("$instance: inet_interfaces is null ?? but require 5mn to wait current:{$timefile}Mn");
	}
	return;	
}

if(preg_match("#bounce\[.+?fatal: bad string length 0 < 1: myorigin#",$buffer,$re)){
	events("myorigin is null ?? in postfix configuration file, try to repair");
	$file="/etc/artica-postfix/croned.1/postfix.myorigin.null";
	$timefile=file_time_min($file);
	if($timefile>5){
		email_events("Postfix: myorigin missing data parameter","Postfix claim \n$buffer\nArtica will change value","postfix");
		shell_exec("{$GLOBALS["PHP5_BIN"]} /usr/share/artica-postfix/exec.postfix.maincf.php --networks &");	
		@unlink($file);
		file_put_contents($file,"#");
	}else{
		events("myorigin is null ?? but require 5mn to wait current:{$timefile}Mn");
	}
	return;	
}

if(preg_match("#local\[.+?warning: dict_ldap_connect: Unable to bind to server (.+?)\s+#",$buffer,$re)){
	events("{$re[1]} unavailable");
	$file="/etc/artica-postfix/croned.1/postfix.ldap.failed";
	$timefile=file_time_min($file);
	if($timefile>5){
		email_events("Postfix: LDAP server {$re[1]} unavailable","Postfix claim \n$buffer\nplease check the LDAP server database","postfix");
		@unlink($file);
		file_put_contents($file,"#");
	}else{
		events("$re[1]} unavailable but require 5mn to wait current:{$timefile}Mn");
	}
	return;	
}


if(preg_match("#postqueue\[.+?fatal: bad string length 0.+?:\s+(.+?)\s+#",$buffer,$re)){
	events("{$re[1]} is null ?? in postfix configuration file");
	$file="/etc/artica-postfix/croned.1/postfix.postdrop.permissions";
	if(file_time_min($file)>5){
		email_events("Postfix: {$re[1]} missing data parameter","Postfix claim \n$buffer\nContact your support team in order to fix this issue.","postfix");
		@unlink($file);
		file_put_contents($file,"#");
	}
	return;
}

if(preg_match("#opendkim\[([0-9]+)\]:\s+OpenDKIM\s+Filter\s+v(.+?)\s+starting#",$buffer,$re)){
	events("opendkim start");
	email_events("Postfix: Plugin OpenDKIM version {$re[2]} successfuly started","OpenDKIM inform\n$buffer\n","postfix");
	return;	
}


if(preg_match("#zarafa-server\[.+?Server shutdown complete.#",$buffer,$re)){
	events("Zarafa stopped");
	email_events("Zarafa: Zarafa was successfully stopped","$buffer","mailbox");
	return;		
}

if(preg_match("#zarafa-server\[.+?Startup succeeded on pid#",$buffer,$re)){
	events("Zarafa started");
	email_events("Zarafa: Zarafa was successfully started","$buffer","mailbox");
	return;		
}

if(preg_match("#zarafa-server\[.+?SQL Failed: Can't connect to MySQL server on '(.+?)'#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/zarafa.mysql.error";
	events("Zarafa mysql server {$re[1]} error connect to MySQL");
	if(file_time_min($file)>5){
		email_events("Zarafa: Zarafa Can't connect to MySQL server {$re[1]}","Zarafa claims, $buffer\nArtica will try to fix it\nYou will recieve an other notification","mailbox");
		shell_exec($GLOBALS["PHP5_BIN"]." /usr/share/artica-postfix/exec.status.php --zarafa-watchdog &");
		@unlink($file);
		file_put_contents($file,"#");
	}
	return;	
}	

if(preg_match("#zarafa-server\[.+?Unable to find company id for object\s+(.+?)$#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/zarafa.{$re[1]}.error";
	if(file_time_min($file)>5){
		events("{$re[1]}: user is not stored in artica Database");
		shell_exec("{$GLOBALS["PHP5_BIN"]} /usr/share/artica-postfix/exec.zarafa.build.stores.php --emergency \"{$re[1]}\" &");
		email_events("Zarafa: Zarafa was successfully started","Zarafa claims, $buffer\nArtica will try to fix it","mailbox");
		@unlink($file);
		file_put_contents($file,"#");
	}
	return;		
}





if(preg_match("#postfix\/master\[.+?fatal: bind 0\.0\.0\.0 port 25: Address already in use#",$buffer,$re)){
	email_events("Postfix will be restarted","Postfix claims, $buffer","postfix");
	shell_exec("/etc/init.d/artica-postfix restart postfix-single &");
	return;
}

if(preg_match("#zarafa-(.+?)\[.+?Starting zarafa-.+?, pid\s+([0-9]+)#",$buffer,$re)){
	email_events("Zarafa: {$re[1]} successfully started pid {$re[2]}",$buffer,"system");
	return;
}

if(preg_match("#zarafa-dagent\[.+?Failed to resolve recipient (.+?)$#",$buffer,$re)){
	$re[1]=trim($re[1]);
	$file="/etc/artica-postfix/croned.1/zarafa.{$re[1]}.error";
	if(file_time_min($file)>10){
		$zarafa_admin=$GLOBALS["CLASS_UNIX"]->find_program("zarafa-admin");
		exec("$zarafaadmin -l 2>&1",$results);
		email_events("Zarafa: {$re[1]} no such user","Zarafa failed to find {{$re[1]}}\n$buffer\nHere it is the results of already registered users:\n".@implode("\n",$results),"mailbox");
		@unlink($file);
		file_put_contents($file,"#");
	}
	
	return;
}

if(preg_match("#zarafa-dagent\[.+?Unable to login for user (.+?), error code: ([0-9a-zA-Z]+)#",$buffer,$re)){
	$re[1]=trim($re[1]);
	$file="/etc/artica-postfix/croned.1/zarafa.{$re[1]}.error";
	if(file_time_min($file)>10){
		$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("{$GLOBALS["PHP5_BIN"]} /usr/share/artica-postfix/exec.zarafa.build.stores.php --orphans");
		$textadd="Please check if this user exists in the LDAP database, artica will check orphans users and stores in background mode";
		email_events("Zarafa: {$re[1]} user failed to login","Zarafa failed to login {{$re[1]}}\n$buffer\nHere it is the results of already registered users:\n".@implode("\n",$results),"\n$textadd","mailbox");
		@unlink($file);
		file_put_contents($file,"#");
	}
	
	return;
}




if(preg_match("#zarafa-server\[.+?Unable to start server on port 236: Address already in use#",$buffer,$re)){
	events("Zarafa-server error port 236 failed");
	$file="/etc/artica-postfix/croned.1/zarafa.236.error";
	if(file_time_min($file)>10){
		email_events("Zarafa: unable to start port already open","Zarafa claim \n$buffer\nArtica will try to restart it","mailbox");
		shell_exec("/etc/init.d/artica-postfix restart zarafa-server &");
		@unlink($file);
		file_put_contents($file,"#");
	}
	return;	
	
}
if(preg_match("#zarafa-gateway\[.+?Unable to listen on port 110#",$buffer,$re)){
	events("Zarafa-server error port 110 failed");
	$file="/etc/artica-postfix/croned.1/zarafa.110.error";
	if(file_time_min($file)>10){
		email_events("Zarafa: unable to start port 110 already open","Zarafa claim \n$buffer\nArtica will try to restart it","mailbox");
		shell_exec("/etc/init.d/artica-postfix restart zarafa-server &");
		@unlink($file);
		file_put_contents($file,"#");
	}
	return;	
	
}

if(preg_match("#zarafa-licensed\[.+?License is for(.+?)users#",$buffer,$re)){
	events("Zarafa license={$re[1]}");
	@file_put_contents("/etc/artica-postfix/settings/Daemons/ZarafaLicenseInfos",$re[1]);
}

 


if(preg_match("#postfix\/postdrop\[.+?warning: mail_queue_enter: create file maildrop\/.+?:\s+Permission denied#",$buffer,$re)){
	events("Permission denied on maildrop queue");
	$file="/etc/artica-postfix/croned.1/postfix.postdrop.permissions";
	if(file_time_min($file)>10){
		email_events("Postfix: Permissions problems on postdrop queue","Postfix claim \n$buffer\nArtica will try to fix it","postfix");
		shell_exec("{$GLOBALS["PHP5_BIN"]} /usr/share/artica-postfix/exec.postfix.maincf.php --postdrop-perms &");
		@unlink($file);
		file_put_contents($file,"#");
	}
	return;
}

if(preg_match("#smtp\[.+?host\s+(.+?)\[.+?said:\s+421\s+4\.2\.1\s+MSG=.+?\(DNS:NR\)#",$buffer,$re)){
	events("mail Refused from {$re[1]}");
	$file="/etc/artica-postfix/croned.1/postfix.{$re[1]}.refused";
	if(file_time_min($file)>10){
		email_events("Postfix: your messages has been refused from {$re[1]}","Postfix claim \n$buffer\nCheck your smtp configuration in order to be compliance for {$re[1]}","postfix");
		@unlink($file);
		file_put_contents($file,"#");
	}
	return;	
}


if(preg_match("#smtpd\[.+?NOQUEUE: reject:\s+RCPT from\s+(.+?)\[(.+?)\]:.+?<(.+?)>:\s+Recipient address rejected: Mail appeared to be SPAM or forged.+?from=<(.+?)>#",$buffer,$re)){
		events("mail Refused from {$re[1]} for {$re[4]}");
		$file="/etc/artica-postfix/croned.1/postfix.{$re[1]}.refused";
		event_message_reject_hostname("Forged",$re[2],$re[4],$re[3]);
		if(file_time_min($file)>10){
			email_events("Postfix: your messages has been refused from {$re[1]} ({$re[2]}) it seems your Forged your messages","Postfix claim \n$buffer\nCheck your smtp configuration in order to be compliance for {$re[1]}","postfix");
			@unlink($file);
			file_put_contents($file,"#");
		}
		
		return;
}

if(preg_match("#\[.+?NOQUEUE: reject: RCPT from.+?\[(.+?)\]:.+?Mail appeared to be SPAM or forged.+?from=<(.+?)>\s+to=<(.+?)>#",$buffer,$re)){
	event_message_reject_hostname("Forged",$re[1],$re[3],$re[4]);
	return;
}


if(preg_match("#postscreen\[.+?NOQUEUE: reject: RCPT from\s+\[(.+?)\].+?Service currently unavailable;\s+from=<(.*?)>,\s+to=<(.+?)>#",$buffer,$re)){
	event_message_reject_hostname("PostScreen",$re[2],$re[3],$re[1]);
	return;
}

if(preg_match("#\[.+?:\s+NOQUEUE: reject: RCPT from.+?\[(.+?)\]:.+?Sender address rejected: blacklisted sender;\s+from=<(.*)>\s+to=<(.+?)>#",$buffer,$re)){
	event_message_reject_hostname("blacklisted",$re[2],$re[3],$re[1]);
	return;
}
if(preg_match("#\]: NOQUEUE: reject: RCPT from.+?\[(.+?)\]:.+?Banned destination domain.+?from=<(.*?)>\s+to=<(.+?)>#",$buffer,$re)){
	event_message_reject_hostname("Banned domain",$re[2],$re[3],$re[1]);
	return;
}


if(preg_match("#smtpd\[.+?NOQUEUE: reject: RCPT from.+?\[(.+?)\]:.+?Recipient address rejected: Your MTA is listed in too many DNSBLs.+?from=<(.+?)>\s+to=<(.+?)>#",$buffer,$re)){
	event_message_reject_hostname("DNSBL",$re[1],$re[3],$re[4]);
	return;	
}


if(strpos($buffer,"warning: connect to Milter service unix:/var/run/opendkim/opendkim.sock: No such file or directory")>0){
	events("OpenDKIM Failed");
	$file="/etc/artica-postfix/croned.1/postfix.opendkim.error";
	if(file_time_min($file)>10){
		email_events("Postfix: OpenDKIM socket failed","Postfix claim\n$buffer\nArtica try to restart OpenDKIM.","postfix");
		shell_exec("/etc/init.d/artica-postfix restart dkfilter &");
		@unlink($file);
		file_put_contents($file,"#");		
	}
	return;	
}

if(preg_match("#postfix\/smtp.+?connect to\s+(.+?)\[(.+?)\]:([0-9]+):\s+Connection refused#",$buffer,$re)){
	$md5=md5($re[1]);
	$file="/etc/artica-postfix/croned.1/postfix.connexion-refused.$md5.error";
	events("Postfix connexion refused from {$re[1]}");
	if(file_time_min($file)>10){
		email_events("Postfix: Unable to connect to {$re[1]} on port {$re[3]}","Postfix claim\n$buffer\nPlease check if {$re[2]} is available","postfix");
		@unlink($file);
		file_put_contents($file,"#");		
	}
	return;	
	
}


if(preg_match("#NOQUEUE: reject: RCPT from.+?\[(.+?)\]:.+?Relay access denied;\s+from=<(.+?)>\s+to=<(.+?)>#",$buffer,$re)){
	events("Relay access denied :{$re[1]} from {$re[2]} to {$re[2]}");
	event_message_reject_hostname("Relay access denied",$re[2],$re[3],$re[1]);
	return;
}

if(preg_match("#cleanup\[.+?:\s+(.+?):\s+reject: body.+?\s+from.+?\[(.+?)\];\s+from=<(.*?)>\s+to=<(.+?)>.+?Message Body rejected#",$buffer,$re)){
	event_message_milter_reject($re[1],"Banned words",$re[1],$re[2],$buffer);
	return;
}

if(preg_match("#postscreen.+?NOQUEUE: reject: RCPT from \[(.+?)\].+?Service unavailable;.+?blocked using.+?; from=<(.+?)>, to=<(.+?)>#",$buffer,$re)){
	events("PostScreen RBL :{$re[1]} from {$re[2]} to {$re[2]}");
	event_message_reject_hostname("PostScreen RBL",$re[2],$re[3],$re[1]);
	return;
}


if(strpos($buffer,"warning: cannot get certificate from file /etc/ssl/certs/postfix/ca.crt")>0){
	$file="/etc/artica-postfix/croned.1/postfix.certificate.error";
	events("Postfix certificate problems");
	if(file_time_min($file)>10){
		email_events("Postfix: SSL certificate error","Postfix claim\n$buffer\nArtica try to rebuild the certificate.","postfix");
		shell_exec("/usr/share/artica-postfix/bin/artica-install --change-postfix-certificate &");
		@unlink($file);
		file_put_contents($file,"#");			
	}
	return;
}

if(preg_match("#NOQUEUE: reject: CONNECT from.+?\[(.+?)\].+?: Client host rejected: Server configuration error;#",$buffer,$re)){
	events("postfix fatal error {$re[1]} rejected");
	$file="/etc/artica-postfix/croned.1/postfix.Server.configuration.error";
	if(file_time_min($file)>10){
		email_events("Postfix: Server configuration error mails from {$re[1]} has been rejected","Postfix claim\n$buffer\nPlease check your configuration.","postfix");
		@unlink($file);
		file_put_contents($file,"#");
	}
	return;		
}

if(preg_match("#postfix.+?NOQUEUE: reject: RCPT from.+?\[(.+?)\]: 554.+?: Relay access denied; from=<> to=<(.+?)>#",$buffer,$re)){
	events("Access denied :{$re[1]} from unknown to {$re[2]}");
	event_message_reject_hostname("Access denied","unknown",$re[2],$re[1]);
	return;
}


if(preg_match("#NOQUEUE: reject: RCPT from.+?\[(.+?)\]:.+?Client host rejected: Access denied;\s+from=<(.+?)>\s+to=<(.+?)>#",$buffer,$re)){
	events("Access denied :{$re[1]} from {$re[2]} to {$re[2]}");
	event_message_reject_hostname("Access denied",$re[2],$re[3],$re[1]);
	return;
}

if(preg_match("#postfix.+?:\s+(.+):\s+milter-discard: END-OF-MESSAGE\s+from.+?\[(.+?)\]:\s+milter triggers DISCARD action;\s+from=<(.*?)>\s+to=<(.+?)>\s+#",$buffer,$re)){
	events("Rejected :{$re[1]} from {$re[2]} to {$re[2]}");
	event_DISCARD($re[1],$re[3],$re[4],$buffer,$re[2]);
	return;
}

if(preg_match("#smtpd\[.+?NOQUEUE: reject: MAIL from.+?\[(.+?)\]:.+?Sender address rejected: Domain not found;\s+from=<(.+?)>#",$buffer,$re)){
	events("Domain not found :{$re[1]} from {$re[2]}");
	event_message_reject_hostname("Domain not found",$re[2],null,$re[1]);
	return;
}
if(preg_match("#smtpd\[.+?NOQUEUE: reject: MAIL from.+?\[(.+?)\]:.+?Sender address rejected: Access denied;\s+from=<(.+?)>#",$buffer,$re)){
	events("Access denied :{$re[1]} from {$re[2]}");
	event_message_reject_hostname("Access denied",$re[2],null,$re[1]);
	return;
}

//SMTP HACK ######################################################################################################
if(preg_match("#postfix.+?timeout after.+?from.+?\[(.+?)\]#",$buffer,$re)){
	Postfix_Addconnection_error(null,$re[1],"Timeout");
	if($GLOBALS["SMTP_HACK_CONFIG_RATE"]["SMTPHACK_TIMEOUT"]>0){
		$GLOBALS["SMTP_HACK"][$re[1]]["SMTPHACK_TIMEOUT"]=$GLOBALS["SMTP_HACK"][$re[1]]["SMTPHACK_TIMEOUT"]+1;
		events("Postfix Hack: timeout from {$re[1]} {$GLOBALS["SMTP_HACK"][$re[1]]["SMTPHACK_TIMEOUT"]} attempts/{$GLOBALS["SMTP_HACK_CONFIG_RATE"]["SMTPHACK_TIMEOUT"]}");
		if($GLOBALS["SMTP_HACK"][$re[1]]["SMTPHACK_TIMEOUT"]>=$GLOBALS["SMTP_HACK_CONFIG_RATE"]["SMTPHACK_TIMEOUT"]){
			smtp_hack_perform($re[1],$GLOBALS["SMTP_HACK"][$re[1]],"SMTPHACK_TIMEOUT");
			unset($GLOBALS["SMTP_HACK"][$re[1]]);	
		}	
	}
	return null;
}
if(preg_match("#postfix.+?: too many errors after.+?from.+?\[(.+?)\]#",$buffer,$re)){
	if($GLOBALS["SMTP_HACK_CONFIG_RATE"]["SMTPHACK_TOO_MANY_ERRORS"]>0){
		$GLOBALS["SMTP_HACK"][$re[1]]["SMTPHACK_TOO_MANY_ERRORS"]=$GLOBALS["SMTP_HACK"][$re[1]]["SMTPHACK_TOO_MANY_ERRORS"]+1;
		events("Postfix Hack: too many errors from {$re[1]} {$GLOBALS["SMTP_HACK"][$re[1]]["SMTPHACK_TOO_MANY_ERRORS"]} attempts/{$GLOBALS["SMTP_HACK_CONFIG_RATE"]["SMTPHACK_TOO_MANY_ERRORS"]}");
		if($GLOBALS["SMTP_HACK"][$re[1]]["SMTPHACK_TOO_MANY_ERRORS"]>=$GLOBALS["SMTP_HACK_CONFIG_RATE"]["SMTPHACK_TOO_MANY_ERRORS"]){
			smtp_hack_perform($re[1],$GLOBALS["SMTP_HACK"][$re[1]],"SMTPHACK_TOO_MANY_ERRORS");
			unset($GLOBALS["SMTP_HACK"][$re[1]]);	
		}	
	}
	return null;
}






if(preg_match("#postfix.+?: warning: (.+?): hostname.+?verification failed: Temporary failure in name resolution#",$buffer,$re)){
	Postfix_Addconnection_error(null,$re[1],"verification failed");
	if($GLOBALS["SMTP_HACK_CONFIG_RATE"]["SMTPHACK_RESOLUTION_FAILURE"]>0){
		$GLOBALS["SMTP_HACK"][$re[1]]["SMTPHACK_RESOLUTION_FAILURE"]=$GLOBALS["SMTP_HACK"][$re[1]]["SMTPHACK_RESOLUTION_FAILURE"]+1;
		events("Postfix Hack: Temporary failure in name resolution from {$re[1]} {$GLOBALS["SMTP_HACK"][$re[1]]["SMTPHACK_RESOLUTION_FAILURE"]} attempts/{$GLOBALS["SMTP_HACK_CONFIG_RATE"]["SMTPHACK_RESOLUTION_FAILURE"]}");
		if($GLOBALS["SMTP_HACK"][$re[1]]["SMTPHACK_RESOLUTION_FAILURE"]>=$GLOBALS["SMTP_HACK_CONFIG_RATE"]["SMTPHACK_RESOLUTION_FAILURE"]){
			smtp_hack_perform($re[1],$GLOBALS["SMTP_HACK"][$re[1]],"SMTPHACK_RESOLUTION_FAILURE");
			unset($GLOBALS["SMTP_HACK"][$re[1]]);	
		}	
	}
	return null;
}


if(preg_match("#smtpd\[.+?:\s+reject:\s+CONNECT from\s+(.+?)\[([0-9\.]+)\]:\s+554.+?Service unavailable;.+?blocked#",$buffer,$re)){
	Postfix_Addconnection_error($re[1],$re[2],"RBL");	
	if($GLOBALS["SMTP_HACK_CONFIG_RATE"]["RBL"]>0){
		$GLOBALS["SMTP_HACK"][$re[1]]["RBL"]=$GLOBALS["SMTP_HACK"][$re[2]]["RBL"]+2;
		events("Postfix Hack: {$re[1]} RBL !! {$re[2]}={$GLOBALS["SMTP_HACK"][$re[2]]["RBL"]} attempts");
		if($GLOBALS["SMTP_HACK"][$re[2]]["RBL"]>=$GLOBALS["SMTP_HACK_CONFIG_RATE"]["RBL"]){
			smtp_hack_perform($re[2],$GLOBALS["SMTP_HACK"][$re[2]],"RBL");
			unset($GLOBALS["SMTP_HACK"][$re[2]]);	
		}	
	}
	return null;
}


if(preg_match("#smtpd\[.+?warning:\s+(.+?):\s+hostname\s+(.+?)\s+verification failed: Name or service not known#",$buffer,$re)){
	Postfix_Addconnection_error($re[2],$re[1],"Name or service not known");
	if($GLOBALS["SMTP_HACK_CONFIG_RATE"]["NAME_SERVICE_NOT_KNOWN"]>0){
		$GLOBALS["SMTP_HACK"][$re[1]]["NAME_SERVICE_NOT_KNOWN"]=$GLOBALS["SMTP_HACK"][$re[1]]["NAME_SERVICE_NOT_KNOWN"]+1;
		events("Postfix Hack: {$re[1]} Name or service not known {$re[1]}={$GLOBALS["SMTP_HACK"][$re[1]]["NAME_SERVICE_NOT_KNOWN"]} attempts");
		if($GLOBALS["SMTP_HACK"][$re[1]]["NAME_SERVICE_NOT_KNOWN"]>=$GLOBALS["SMTP_HACK_CONFIG_RATE"]["NAME_SERVICE_NOT_KNOWN"]){
			smtp_hack_perform($re[1],$GLOBALS["SMTP_HACK"][$re[1]],"NAME_SERVICE_NOT_KNOWN");
			unset($GLOBALS["SMTP_HACK"][$re[1]]);
		}
	}
	return;
}

if(preg_match('#warning.+?\[([0-9\.]+)\]:\s+SASL LOGIN authentication failed: authentication failure#',$buffer,$re)){
	Postfix_Addconnection_error($re[2],$re[1],"Login failed");
	if($GLOBALS["SMTP_HACK_CONFIG_RATE"]["SASL_LOGIN"]>0){
		$GLOBALS["SMTP_HACK"][$re[1]]["SASL_LOGIN"]=$GLOBALS["SMTP_HACK"][$re[1]]["SASL_LOGIN"]+1;
		events("Postfix Hack:bad SASL login {$re[1]}:{$GLOBALS["SMTP_HACK"][$re[1]]["SASL_LOGIN"]} retries");
		if($GLOBALS["SMTP_HACK"][$re[1]]["SASL_LOGIN"]>=$GLOBALS["SMTP_HACK_CONFIG_RATE"]["SASL_LOGIN"]){
			smtp_hack_perform($re[1],$GLOBALS["SMTP_HACK"][$re[1]],"SASL_LOGIN");
			unset($GLOBALS["SMTP_HACK"][$re[1]]);	
		}
	}
	return null;
}

if(preg_match("#NOQUEUE: reject:.+?from.+?\[([0-9\.]+)\]:.+?Service unavailable.+?blocked using.+?from=<(.+?)> to=<(.+?)> proto#",$buffer,$re)){
	
	event_message_reject_hostname("RBL",$re[2],$re[3],$re[1]);
	if($GLOBALS["SMTP_HACK_CONFIG_RATE"]["RBL"]>0){
		$GLOBALS["SMTP_HACK"][$re[1]]["RBL"]=$GLOBALS["SMTP_HACK"][$re[1]]["RBL"]+1;
		events("Postfix Hack: {$re[1]} RBL !! from=<{$re[2]}> to=<{$re[3]}> {$re[1]}={$GLOBALS["SMTP_HACK"][$re[1]]["RBL"]} attempts");
		if($GLOBALS["SMTP_HACK"][$re[1]]["RBL"]>=$GLOBALS["SMTP_HACK_CONFIG_RATE"]["RBL"]){
			smtp_hack_perform($re[1],$GLOBALS["SMTP_HACK"][$re[1]],"RBL");
			unset($GLOBALS["SMTP_HACK"][$re[1]]);	
		}
	}	
	return null;
}
if(preg_match("#smtpd.+?reject: RCPT from.+?\[(.+?)\]:\s+550.+?:.+Recipient address rejected:.+?because of previous errors.+?from=<(.+?)>\s+to=<(.+?)>#",$buffer,$re)){
	event_message_reject_hostname("RBL",$re[2],$re[3],$re[1]);
	if($GLOBALS["SMTP_HACK_CONFIG_RATE"]["RBL"]>0){
		$GLOBALS["SMTP_HACK"][$re[1]]["RBL"]=$GLOBALS["SMTP_HACK"][$re[1]]["RBL"]+1;
		events("Postfix Hack: {$re[1]} RBL !! from=<{$re[2]}> to=<{$re[3]}> {$re[1]}={$GLOBALS["SMTP_HACK"][$re[1]]["RBL"]} attempts");
		if($GLOBALS["SMTP_HACK"][$re[1]]["RBL"]>=$GLOBALS["SMTP_HACK_CONFIG_RATE"]["RBL"]){
			smtp_hack_perform($re[1],$GLOBALS["SMTP_HACK"][$re[1]],"RBL");
			unset($GLOBALS["SMTP_HACK"][$re[1]]);	
		}
	}	
	return null;
}


if(preg_match("#smtpd.+?reject: RCPT from.+?\[(.+?)\]:\s+554.+?:.+Sender address rejected:.+?FORGED MAIL.+?from=<(.+?)>\s+to=<(.+?)>#",$buffer,$re)){
	event_message_reject_hostname("FORGED",$re[2],$re[3],$re[1]);
	if($GLOBALS["SMTP_HACK_CONFIG_RATE"]["RBL"]>0){
		$GLOBALS["SMTP_HACK"][$re[1]]["RBL"]=$GLOBALS["SMTP_HACK"][$re[1]]["RBL"]+1;
		events("Postfix Hack: {$re[1]} RBL !! from=<{$re[2]}> to=<{$re[3]}> {$re[1]}={$GLOBALS["SMTP_HACK"][$re[1]]["RBL"]} attempts");
		if($GLOBALS["SMTP_HACK"][$re[1]]["RBL"]>=$GLOBALS["SMTP_HACK_CONFIG_RATE"]["RBL"]){
			smtp_hack_perform($re[1],$GLOBALS["SMTP_HACK"][$re[1]],"RBL");
			unset($GLOBALS["SMTP_HACK"][$re[1]]);	
		}
	}	
	return null;
}

if(preg_match("#:\s+NOQUEUE: reject: RCPT from.+?\[(.+?)\]:\s+550.+?:\s+Recipient address rejected: Mail appears to be SPAM or forged.+?from=<(.+?)>\s+to=<(.+?)>#",$buffer,$re)){
	event_message_reject_hostname("RBL",$re[2],$re[3],$re[1]);
	if($GLOBALS["SMTP_HACK_CONFIG_RATE"]["RBL"]>0){
		$GLOBALS["SMTP_HACK"][$re[1]]["RBL"]=$GLOBALS["SMTP_HACK"][$re[1]]["RBL"]+1;
		events("Postfix Hack: {$re[1]} RBL !! from=<{$re[2]}> to=<{$re[3]}> {$re[1]}={$GLOBALS["SMTP_HACK"][$re[1]]["RBL"]} attempts");
		if($GLOBALS["SMTP_HACK"][$re[1]]["RBL"]>=$GLOBALS["SMTP_HACK_CONFIG_RATE"]["RBL"]){
			smtp_hack_perform($re[1],$GLOBALS["SMTP_HACK"][$re[1]],"RBL");
			unset($GLOBALS["SMTP_HACK"][$re[1]]);	
		}
	}	
	return null;
}





if(preg_match("#smtpd.+?reject: RCPT from unknown\[(.+?)\]:\s+550.+?:.+Recipient address rejected:.+?DNSBLs.+?from=<(.+?)>\s+to=<(.+?)>#",$buffer,$re)){
	event_message_reject_hostname("RBL",$re[2],$re[3],$re[1]);
	if($GLOBALS["SMTP_HACK_CONFIG_RATE"]["RBL"]>0){
		$GLOBALS["SMTP_HACK"][$re[1]]["RBL"]=$GLOBALS["SMTP_HACK"][$re[1]]["RBL"]+1;
		events("Postfix Hack: {$re[1]} RBL !! from=<{$re[2]}> to=<{$re[3]}> {$re[1]}={$GLOBALS["SMTP_HACK"][$re[1]]["RBL"]} attempts");
		if($GLOBALS["SMTP_HACK"][$re[1]]["RBL"]>=$GLOBALS["SMTP_HACK_CONFIG_RATE"]["RBL"]){
			smtp_hack_perform($re[1],$GLOBALS["SMTP_HACK"][$re[1]],"RBL");
			unset($GLOBALS["SMTP_HACK"][$re[1]]);	
		}
	}	
	return null;
}


if(preg_match("#NOQUEUE: reject:.+?from.+?\[([0-9\.]+)\]:.+?<(.+?)>:\s+Recipient address rejected: User unknown in local recipient table;\s+from=<(.+?)>\s+to=<(.+?)>#",$buffer,$re)){
	event_message_reject_hostname("User unknown",$re[2],$re[3],$re[1]);
	if($GLOBALS["SMTP_HACK_CONFIG_RATE"]["USER_UNKNOWN"]>0){
		$GLOBALS["SMTP_HACK"][$re[1]]["USER_UNKNOWN"]=$GLOBALS["SMTP_HACK"][$re[1]]["USER_UNKNOWN"]+1;
		events("Postfix Hack: : {$re[1]} User unknown from=<{$re[2]}> to=<{$re[3]}> {$GLOBALS["SMTP_HACK"][$re[1]]["USER_UNKNOWN"]} attempts/{$GLOBALS["SMTP_HACK_CONFIG_RATE"]["USER_UNKNOWN"]}");
		if($GLOBALS["SMTP_HACK"][$re[1]]["USER_UNKNOWN"]>=$GLOBALS["SMTP_HACK_CONFIG_RATE"]["USER_UNKNOWN"]){
			smtp_hack_perform($re[1],$GLOBALS["SMTP_HACK"][$re[1]],"USER_UNKNOWN");
			unset($GLOBALS["SMTP_HACK"][$re[1]]);	
		}
	}	
	return null;
}

if(preg_match("#smtpd\[.+?warning: Illegal address syntax from.+?\[(.+?)\] in MAIL#",$buffer,$re)){
	Postfix_Addconnection_error(null,$re[1],"Illegal address");
	if($GLOBALS["SMTP_HACK_CONFIG_RATE"]["BLOCKED_SPAM"]>0){
		$GLOBALS["SMTP_HACK"][$re[1]]["BLOCKED_SPAM"]=$GLOBALS["SMTP_HACK"][$re[1]]["BLOCKED_SPAM"]+1;
		events("Postfix Hack: {$re[1]} Illegal address syntax {$GLOBALS["SMTP_HACK"][$re[1]]["BLOCKED_SPAM"]} attempts/{$GLOBALS["SMTP_HACK_CONFIG_RATE"]["BLOCKED_SPAM"]}");
		if($GLOBALS["SMTP_HACK"][$re[1]]["BLOCKED_SPAM"]>=$GLOBALS["SMTP_HACK_CONFIG_RATE"]["BLOCKED_SPAM"]){
			smtp_hack_perform($re[1],$GLOBALS["SMTP_HACK"][$re[1]],"BLOCKED_SPAM");
			unset($GLOBALS["SMTP_HACK"][$re[1]]);	
		}
	}	
	return null;
}


if(preg_match("#postfix\/lmtp\[.+?:\s+(.+?):\s+to=<(.+)>,\s+relay=([0-9\.]+)\[.+?:[0-9]+,.+?status=deferred.+?430 Authentication required#",$buffer,$re)){
	events("postfix LMTP error to {$re[2]}");
	$file="/etc/artica-postfix/croned.1/postfix.lmtp.auth.failed";
	event_messageid_rejected($re[1],"Mailbox Authentication required",$re[3],$re[2]);
	if(file_time_min($file)>5){
		email_events("Postfix: LMTP Error","Postfix\n$buffer\nArtica will reconfigure LMTP settings","postfix");
		$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("{$GLOBALS["PHP5_BIN"]} {$GLOBALS["MYPATH"]}/exec.postfix.maincf.php --mailbox-transport");
		@unlink($file);
		file_put_contents($file,"#");
	}
	
	return;	
	
}

if(preg_match("#postfix\/lmtp\[.+?:\s+connect to ([0-9\.]+)\[.+?:[0-9]+:\s+Connection refused#",$buffer)){
	events("postfix LMTP error");
	$file="/etc/artica-postfix/croned.1/postfix.lmtp.cnx.refused";
	event_messageid_rejected($re[1],"LMTP Error","127.0.0.1",$re[2]);
	if(file_time_min($file)>5){
		
		if($GLOBALS["ZARAFA_INSTALLED"]){
			email_events("Postfix: Zarafa LMTP Error","Postfix\n$buffer\nArtica will trying to start Zarafa","postfix");
			$cmd="{$GLOBALS["NOHUP_PATH"]} /etc/init.d/artica-postfix start zarafa >/dev/null 2>&1 &";
			shell_exec(trim($cmd));
			@unlink($file);
			file_put_contents($file,"#");
			return;	
		}
		
		email_events("Postfix: LMTP Error","Postfix\n$buffer\nArtica will reconfigure LMTP settings","postfix");
		$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("{$GLOBALS["PHP5_BIN"]} {$GLOBALS["MYPATH"]}/exec.postfix.maincf.php --mailbox-transport");
		@unlink($file);
		file_put_contents($file,"#");
	}
	
	return;		
}
if(preg_match("#postfix\/.+?:\s+warning:\s+problem talking to server\s+[0-9\.]+:12525:\s+Connection refused#",$buffer)){
	events("postfix policyd-weight error");
	$file="/etc/artica-postfix/croned.1/postfix.policyd-weight.conect.failed";
	
	if(file_time_min($file)>10){
		email_events("Postfix: Policyd-weight server connection problem","Postfix\n$buffer\nArtica will reconfigure restart policyd-weight service","postfix");
		$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart policydw");
		@unlink($file);
		file_put_contents($file,"#");
	}
	
	return;		
}

if(preg_match("#KASERROR.+?keepup2date\s+failed.+?no valid license info found#",$buffer,$re)){
	events("Kas3, license error, uninstall kas3");
	$file="/etc/artica-postfix/croned.1/kas3.license.error";
	if(file_time_min($file)>5){
		email_events("Kaspersky Antispam: license error","Kaspersky Updater claim\n$buffer\nArtica will uninstall Kaspersky Anti-spam","postfix");
		$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --kas3-remove");
		@unlink($file);
		file_put_contents($file,"#");
	}
	
	return;
}


if(preg_match("#postfix\/postfix-script\[.+?\]: fatal: the Postfix mail system is not running#",$buffer,$re)){
	$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("{$GLOBALS["postfix_bin_path"]} start");
	return;
}


if(preg_match("#zarafa-server\[.+?: SQL Failed: Table.+?zarafa\.(.+?)'\s+doesn.+?exist#",$buffer,$re)){
	events("Zarafa, missing table {$re[1]}");
	zarafa_rebuild_db($re[1],$buffer);
	return;
}

if(preg_match("#zarafa-server\[.+?INNODB engine is not support.+?Please enable the INNODB engine#",$buffer,$re)){
	events("Zarafa, INNODB not enabled, restart mysql {$re[1]}");
	$file="/etc/artica-postfix/croned.1/zarafa.INNODB.error";
	if(file_time_min($file)>5){
		email_events("Zarafa server: innodb is not enabled","Zarafa-server claim\n$buffer\nArtica will restart mysql","mailbox");
		$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart mysql");
		@unlink($file);
		file_put_contents($file,"#");
	}
	
	return;
}




if(preg_match("#zarafa-server\[.+?:\s+Cannot instantiate user plugin: ldap_bind_s: Invalid credentials#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/zarafa.ldap_bind_s.error";
	events("zarafa-server -> ldap_bind_s: Invalid credentials");
	if(file_time_min($file)>5){
		email_events("Zarafa server cannot connect to ldap server","Zarafa-server claim\n$buffer\nArtica will restart and reconfigure zarafa","mailbox");
		$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart zarafa");
		@unlink($file);
		file_put_contents($file,"#");
	}
	
	return;
}

if(preg_match("#smtp\[.+? fatal: specify a password table via the.+?smtp_sasl_password_maps.+?configuration parameter#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/postfix.smtp_sasl_password_maps.error";
	events("postfix -> smtp_sasl_password_maps");
	if(file_time_min($file)>5){
		email_events("Postfix configuration problem","Postfix claim\n$buffer\nArtica will disable SMTP Sasl feature","postfix");
		$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("{$GLOBALS["PHP5_BIN"]} /usr/share/artica-postfix/exec.postfix.maincf.php --disable-smtp-sasl");
		@unlink($file);
		file_put_contents($file,"#");
	}
	return;	
}

if(preg_match("#amavis\[.+?TROUBLE.+?in child_init_hook: BDB can't connect db env.+?No such file or directory#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/amavis.BDB.error";
	events("amavis BDB ERROR");
	if(file_time_min($file)>5){
		email_events("AMAVIS BDB Error","amavis claim\n$buffer\nArtica will restart amavis service","postfix");
		$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart amavis");
		@unlink($file);
		file_put_contents($file,"#");
	}
	return;	
}


if(preg_match("#amavis\[.+?custom checks error:\s+Insecure dependency in connect while running with -T switch at .+?/IO/Socket\.pm line 114#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/amavis.Compress-Raw-Zlib.error";
	events("amavis Compress-Raw-Zlib error -> check Compress-Raw-Zlib version");
	if(file_time_min($file)>5){
		email_events("AMAVIS dependency Error","amavis claim\n$buffer\nArtica will try to check depencies, especially \Compress-Raw-Zlib\"","postfix");
		//THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart amavis");
		@unlink($file);
		file_put_contents($file,"#");
	}
	return;	
}


if(preg_match("#amavis\[.+?connect_to_ldap: bind failed: LDAP_INVALID_CREDENTIALS#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/amavis.LDAP.error";
	events("amavis LDAP ERROR");
	if(file_time_min($file)>5){
		email_events("AMAVIS LDAP connexion Error","amavis claim\n$buffer\nArtica will restart amavis service to reconfigure it","postfix");
		$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart amavis");
		@unlink($file);
		file_put_contents($file,"#");
	}
	return;	
}

if(preg_match("#Decoding of p[0-9]+\s+\(.+?data, at least.+?failed, leaving it unpacked: Compress::Raw::Zlib version\s+(.+?)\s+required.+?this is only version\s+(.+?)\s+#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/amavis.Compress.Raw.Zlib.error";
	events("amavis Compress::Raw::Zlib need to be upgraded");
	if(file_time_min($file)>20){
		email_events("AMAVIS Compress::Raw::Zlib need to be upgraded from {$re[1]} to {$re[2]}","amavis claim\n$buffer\nArtica will install a newest Compress::Raw::Zlib version","postfix");
		$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-make APP_COMPRESS_ROW_ZLIB");
		@unlink($file);
		file_put_contents($file,"#");
	}
	return;	
}

if(preg_match("#smtp\[.+?:\s+fatal: valid hostname or network address required in server description:(.+?)#",$buffer,$re)){
	mail_events("{$re[1]} Bad configuration parameters","Postfix claim\n$buffer\nPlease come back to the interface and check your configuration!","postfix");
	return;
}


if(preg_match("#.+?postfix-.+?\/master\[.+?:\s+fatal:\s+bind\s+[0-9\.]+\s+port\s+25:\s+Address already in use#",$buffer,$re)){
	events("Address already in use -> restart postfix");
	email_events("Postfix will be restarted","Line: ". __LINE__."\nPostfix claims, $buffer","postfix");
	$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart postfix-single");
	return null;	
}

if(preg_match("#postfix\/.+?warning:\s+(.+?)\s+and\s+(.+?)\s+differ#",$buffer,$re)){
	$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("/bin/cp -pf {$re[2]} {$re[1]}");
	return ;
}

if(preg_match("#smtpd\[.+?warning:\s+connect to Milter service unix:(.+?):\s+Permission denied#",$buffer,$re)){
	events("chown postfix:postfix {$re[1]}");
	shell_exec("/bin/chown postfix:postfix {$re[1]} &");
	return;
}


if(preg_match("#amavis.+?:.+?_DIE:\s+Can.+?locate.+?.+?body_[0-9]+\.pm\s+in\s+@INC#",$buffer,$re)){
	SpamAssassin_error_saupdate($buffer);
	return null;	
}

if(preg_match("#spamd\[[0-9]+.+?Can.+?locate\s+Mail\/SpamAssassin\/CompiledRegexps\/body_[0-9]+\.pm#",$buffer,$re)){
	SpamAssassin_error_saupdate($buffer);
	return null;
}

if(preg_match("#zarafa-monitor.+?:\s+Unable to get store entry id for company\s+(.+?), error code#",$buffer,$re)){
	zarafa_store_error($buffer);
	return null;
}



if(preg_match("#postfix\/lmtp.+?:\s+(.+?):\s+to=<(.+?)>.+?lmtp.+?deferred.+?451.+?Mailbox has an invalid format#",$buffer,$re)){
	event_messageid_rejected($re[1],"Mailbox corrupted",null,$re[2]);
	mailbox_corrupted($buffer,$re[2]);
	return null;
	}
	

	
if(preg_match("#postfix\/lmtp.+?(.+?):\s+to=<(.+?)>.+?lmtp.+?status=deferred.+?452.+?Over quota#",$buffer,$re)){
	event_messageid_rejected($re[1],"Over quota",null,$re[2]);
	mailbox_overquota($buffer,$re[2]);
	return null;
	}	

if(preg_match("#postfix\/.+?:(.+?):\s+milter-reject: END-OF-MESSAGE\s+.+?Error in processing.+?ALL VIRUS SCANNERS FAILED;.+?from=<(.+?)>\s+to=<(.+?)>#",$buffer,$re)){
	event_message_milter_reject($re[1],"antivirus failed",$re[1],$re[2],$buffer);
	clamav_error_restart($buffer);
	return null;	
	}

if(preg_match("#postfix\/.+?:(.+?):\s+to=<(.+?)>,.+?\[(.+?)\].+?status=deferred.+?virus_scan FAILED#",$buffer,$re)){
	event_messageid_rejected($re[1],"antivirus failed",$re[3],$re[2]);
	return null;
	}
	
if(preg_match("#smtp\[[0-9]+\]:\s+(.+?):\s+to=<(.+?)>,\s+relay=127\.0\.0.+:[0-9]+,.+?deferred.+?451.+?during fwd-connect\s+\(Negative greeting#",$buffer,$re)){
	event_messageid_rejected($re[1],"Internal timed-out","127.0.0.1",$re[2]);
	$file="/etc/artica-postfix/croned.1/timedout-amavis";
	events("fwd-connect ERROR");
	if(file_time_min($file)>5){
		events("fwd-connect ERROR -> restarting Postfix");
		$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("{$GLOBALS["postfix_bin_path"]} stop");
		$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("{$GLOBALS["postfix_bin_path"]} start");
		@unlink($file);
		file_put_contents($file,"#");
	}
	return;		
}
	
	
if(preg_match("#master\[.+?:\s+fatal:\s+binds\+(.+?)\s+port\s+(.+?).+?Address already in use#",$buffer,$re)){
	postfix_bind_error($re[1],$re[2],$buffer);
	return null;
}


if(preg_match("#kavmilter\[.+?:\s+KAVMilter Error\(13\):\s+Active key expired.+?Exiting#",$buffer,$re)){
	kavmilter_expired($buffer);
	return null;
}


if(preg_match("#postfix.+?\[.+?fatal: open\s+\/etc\/postfix-(.+?)\/main\.cf:\s+No such file or directory#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/instance-{$re[1]}.no-such-file";
	events("{$re[1]} -> bad main.cf ".dirname($re[1]));
	if(file_time_min($file)>5){
	email_events("Postfix missing main.cf for {$re[1]} instance","Postfix claim\n$buffer\nArtica will reconfigure this instance","postfix");
	$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("{$GLOBALS["PHP5_BIN"]} /usr/share/artica-postfix/exec.postfix-multi.php --instance-reconfigure {$re[1]}");
	@unlink($file);
	file_put_contents($file,"#");
	}
	return null;		
}

if(preg_match("#postmulti.+?fatal:.+?Failed to obtain all required /etc/postfix-(.+?)\/main\.cf parameters#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/instance-{$re[1]}.no-maincf-params";
	events("{$re[1]} -> bad main.cf ".dirname($re[1]));
	if(file_time_min($file)>5){
	email_events("Postfix missing main.cf for {$re[1]} instance","Postfix claim\n$buffer\nArtica will reconfigure this instance","postfix");
	$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("{$GLOBALS["PHP5_BIN"]} /usr/share/artica-postfix/exec.postfix-multi.php --instance-reconfigure {$re[1]}");
	@unlink($file);
	file_put_contents($file,"#");
	}
	return null;		
}
if(preg_match("#postfix-(.+?)\/postqueue\[.+?warning: Mail system is down#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/instance-{$re[1]}.down";
	$ftime=file_time_min($file);
	events("{$re[1]} -> system down ({$ftime}mn)");
	if($ftime>=5){
		$cmd="{$GLOBALS["PHP5_BIN"]} /usr/share/artica-postfix/exec.postfix-multi.php --instance-start {$re[1]}";
		email_events("Postfix {$re[1]} instance stopped","Postfix claim\n$buffer\nArtica will start this instance","postfix");
		events("$cmd");
		$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET($cmd);
		@unlink($file);
		file_put_contents($file,"#");
	}
	return null;		
}

if(preg_match("#postfix-(.+?)\/master\[.+?daemon started#",$buffer,$re)){
	events("{$re[1]} -> system start");
	email_events("Postfix {$re[1]} instance started","Postfix notify\n$buffer\n","postfix");
	return null;		
}


if(preg_match("#postfix\[.+?fatal: parameter inet_interfaces: no local interface found for ([0-9\.]+)#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/inet_interfaces-{$re[1]}.down";
	$ftime=file_time_min($file);
	events("{$re[1]} -> interface down ({$ftime}mn)");
	if($ftime>=5){
		email_events("Postfix interface {$re[1]} down","Postfix claim\n$buffer\n
		Check your configuration settings in order to see
		why \"{$re[1]}\" is not loaded","postfix");
		@unlink($file);
		file_put_contents($file,"#");
	}
	return null;
}

if(preg_match("#postmulti-script\[.+?warning: (.+?): please verify contents and remove by hand#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/". md5("{$re[1]}").".delete";
	$ftime=file_time_min($file);
	events("{$re[1]} -> delete");
	if($ftime>=5){
		if(is_dir($re[1])){
			$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("/bin/rm -rf {$re[1]} &");
			@unlink($file);
			file_put_contents($file,"#");
		}
	}
	return null;
}



if(preg_match("#.+?\/(.+?)\[.+?:\s+fatal:\s+open\s+(.+?):\s+No such file or directory#",$buffer,$re)){
	postfix_nosuch_fileor_directory($re[1],$re[2],$buffer);
	return null;
}
if(preg_match("#.+?\/(.+?)\[.+?:\s+fatal:\s+open\s+(.+?)\.db:\s+Bad file descriptor#",$buffer,$re)){
	postfix_baddb($re[1],$re[2],$buffer);
	return null;
}

if(preg_match("#postfix\/qmgr.+?:\s+(.+?):\s+from=<(.*?)>,\s+status=expired, returned to sender#",$buffer,$re)){
	event_finish($re[1],null,"expired","expired",$re[2],$buffer);
	return null;
}


if(preg_match("#postfix postmulti\[[0-9+]\]: fatal: No matching instances#",$buffer,$re)){
	multi_instances_reconfigure($buffer);
	return null;
}

if(preg_match('#NOQUEUE: reject: MAIL from.+?452 4.3.1 Insufficient system storage#',$buffer)){
	$file="/etc/artica-postfix/croned.1/postfix.storage.error";
	if(file_time_min($file)>10){
		email_events("Postfix Insufficient storage disk space!!! ","Postfix claim: $buffer\n Please check your hard disk space !" ,"system");
		@unlink($file);
		file_put_contents($file,"#");
	}
	return null;
}


if(preg_match("#starting amavisd-milter.+?on socket#",$buffer)){
	email_events("Amavisd New has been successfully started",$buffer,"system"); 
	return;
}


if(preg_match("#kavmilter\[.+?\]:\s+Could not open pid file#",$buffer)){
	$file="/etc/artica-postfix/croned.1/postfix.kavmilter.pid.error";
		if(file_time_min($file)>10){
			events("Kaspersky Milter PID error");
			email_events("Kaspersky Milter PID error","kvmilter claim $buffer\nArtica will try to restart it","postfix");
			$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart kavmilter');
			@unlink($file);
		}else{
			events("Kaspersky Milter PID error, but take action after 10mn");
		}	
	file_put_contents($file,"#");	
	return null;
	
}	


// HACK POP3
if(preg_match("#cyrus\/pop3\[.+?badlogin.+?.+?\[(.+?)\]\s+APOP.+?<(.+?)>.+?SASL.+?: user not found: could not find password#",$buffer,$re)){
	hackPOP($re[1],$re[2],$buffer);
	return;
	}
if(preg_match("#cyrus\/pop3\[.+?:\s+badlogin:\s+.+?\[(.+?)\]\s+plaintext\s+(.+?)\s+SASL.+?authentication failure:#",$buffer,$re)){
	hackPOP($re[1],$re[2],$buffer);
	return;
}

if(preg_match("#zarafa-gateway\[.+?: Failed to login from\s+(.+?)\s+with invalid username\s+\"(.+?)\"\s+or wrong password#",$buffer,$re)){
	hackPOP($re[1],$re[2],$buffer);
	return;
}


if(preg_match("#postfix\/.+?warning: TLS library problem.+?system library:fopen:No such file or directory.+?\('(.+?)',#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/postfix.tls.{$re[1]}.error";
		if(file_time_min($file)>5){
			events("TLS {$re[1]} No such file");
			email_events("Postfix error TLS on {$re[1]} (no such file)","Postfix claim $buffer\nArtica will try to repair it by rebuilding certificate","postfix");
			$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET('/usr/share/artica-postfix/bin/artica-install --change-postfix-certificate');
			@unlink($file);
		}else{
			events("TLS {$re[1]} No such file failure, but take action after 5mn");
		}	
	return null;
}


if(preg_match("#smtpd.+?:\s+warning: SASL authentication failure: no secret in database#",$buffer)){
	$file="/etc/artica-postfix/croned.1/postfix.sasl.secret.error";
		if(file_time_min($file)>10){
			events("SASL authentication failure");
			email_events("Postfix error SASL","Postfix claim $buffer\nArtica will try to repair it","postfix");
			$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET('/usr/share/artica-postfix/bin/artica-install --postfix-sasldb2');
			@unlink($file);
		}else{
			events("SASL authentication failure, but take action after 10mn");
		}	
	return null;
	
}

if(preg_match("#smtp.+?connect to 127\.0\.0\.1\[127\.0\.0\.1\]:10024: Connection refused#",$buffer,$re)){
	AmavisConfigErrorInPostfix($buffer);
	return null;
}


if(preg_match("#postfix\/smtp\[.+?:\s+(.+?):\s+to=<(.+?)>.+?status=deferred\s+\(SASL authentication failed.+?\[(.+?)\]#",$buffer,$re)){
	event_messageid_rejected($re[1],"authentication failed",$re[3],$re[2]);
	smtp_sasl_failed($re[3],$re[3],$buffer);
}


if(preg_match("#postfix\/smtp\[.+?:\s+(.+?):\s+to=<(.+?)>.+?status=bounced.+?.+?\[(.+?)\]\s+said:\s+554.+?http:\/\/#",$buffer,$re)){
	ImBlackListed($re[3],$buffer);
	event_messageid_rejected($re[1],"Your are blacklisted",$re[3],$re[2]);
	return null;
}

if(preg_match("#postfix\/(cleanup|bounce|smtp|smtpd|flush|trivial-rewrite)\[.+?warning: database\s+(.+?)\.db\s+is older than source file\s+(.+)#",$buffer,$re)){
	postfix_compile_db($re[3],$buffer);
	return null;
}
if(preg_match("#postfix\/(cleanup|bounce|smtp|smtpd|flush|trivial-rewrite)\[.+?fatal: open database\s+(.+?)\.db:\s+No such file or directory#",$buffer,$re)){
	postfix_compile_missing_db($re[2],$buffer);
	return null;
}

if(preg_match("#postfix\/smtp\[.+?:\s+(.+?):\s+host.+?\[(.+?)\]\s+said:\s+[0-9]+\s+invalid sender domain#",$buffer,$re)){
	Postfix_Addconnection_error($re[1],$re[2],"invalid sender domain");
	event_messageid_rejected($re[1],"invalid sender domain",$re[2],null);
	return null;
}

if(preg_match("#warning: connect to Milter service unix:(.+?)clamav-milter.ctl: Connection refused#",$buffer,$re)){
	MilterClamavError($buffer,"$re[1]/clamav-milter.ctl");
	return null;
}

if(preg_match("#warning: connect to Milter service unix:(.+?)spamass.sock: No such file or directory#",$buffer,$re)){
	MilterSpamAssassinError($buffer,"$re[1]/spamass.sock");
	return null;
}

if(preg_match("#warning: connect to Milter service unix:(.+?)greylist.sock: No such file or directory#",$buffer,$re)){
	miltergreylist_error($buffer,"{$re[1]}/greylist.sock");
	return null;
}

if(preg_match("#postfix\/smtpd\[.+?warning: connect to Milter service unix:(.+?)milter-greylist.sock: No such file or directory#",$buffer,$re)){
	miltergreylist_error($buffer,"{$re[1]}/milter-greylist.sock");
	return null;
}

if(preg_match("#warning: connect to Milter service unix:/var/spool/postfix/var/run/amavisd-milter/amavisd-milter.sock: Connection refused#",$buffer)){
		AmavisConfigErrorInPostfix($buffer);
		return null;
}

if(preg_match("#qmgr.+?transport amavis: Connection refused#",$buffer)){
	AmavisConfigErrorInPostfixRestart($buffer);
	return null;
}



if(preg_match('#milter-greylist: greylist: Unable to bind to port (.+?): Permission denied#',$buffer,$re)){
	miltergreylist_error($buffer,$re[1]);
}

if(preg_match('#]:\s+(.+?): to=<(.+?)>.+?socket/lmtp\].+?status=deferred.+?lost connection with.+?end of data#',$buffer,$re)){
	event_finish($re[1],$re[2],"deferred","mailbox service error",null,$buffer);
	return null;
}




if(preg_match('#badlogin: \[(.+?)\] plaintext\s+(.+?)\s+SASL\(-13\): authentication failure: checkpass failed#',$buffer,$re)){
	if($GLOBALS["DisableMailBoxesHack"]==1){return;}
	
	$date=date('Y-m-d H');
	$_GET["IMAP_HACK"][$re[1]][$date]=$_GET["IMAP_HACK"][$re[1]][$date]+1;
	events("cyrus Hack:bad login {$re[1]}:{$_GET["IMAP_HACK"][$re[1]][$date]} retries");
	if($_GET["IMAP_HACK"][$re[1]][$date]>15){
		email_events("Cyrus HACKING !!!!","Build iptables rule \"iptables -I INPUT -s {$re[1]} -j DROP\" for {$re[1]}!\nlaster error: $buffer","mailbox");
		shell_exec("iptables -I INPUT -s {$re[1]} -j DROP");
		events("IMAP Hack: -> iptables -I INPUT -s {$re[1]} -j DROP");
		unset($_GET["IMAP_HACK"][$re[1]]);
	}
	
	return null;
}



if(preg_match('#badlogin: \[(.+?)\] plaintext\s+(.+?)\s+SASL\(-1\): generic failure: checkpass failed#',$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/cyrus.checkpass.error";
	if(file_time_min($file)>10){
		email_events("Cyrus auth error","Artica will restart messaging service\n\"$buffer\"","mailbox");
		$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart imap');
		@unlink($file);
	}
	return null;
}
if(preg_match('#cyrus\/lmtpunix.+?DBERROR:\s+opening.+?\.db:\s+Cannot allocate memory#',$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/cyrus.dberror.restart.error";
	if(file_time_min($file)>10){
		email_events("Cyrus DBERROR error","Artica will restart messaging service\n\"$buffer\"","mailbox");
		$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart imap');
		@unlink($file);
	}
	return null;
}
if(preg_match('#cyrus\/imap.+?DBERROR.+?Open database handle:\s+(.+?)tls_sessions\.db#',$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/cyrus.dberror.tls_sessions.error";
	if(file_time_min($file)>10){
		email_events("Cyrus DBERROR error","Artica will delete {$re[1]}tls_sessions.db file\n\"$buffer\"","mailbox");
		@unlink("{$re[1]}tls_sessions.db");
		@unlink($file);
	}
	return null;
}


if(preg_match('#cyrus\/notify.+?DBERROR db[0-9]: PANIC: fatal region error detected; run recovery#',$buffer)){
	$file="/etc/artica-postfix/croned.1/cyrus.db.error";
	if(file_time_min($file)>10){
		$buffer="Artica has detected a fatal error on cyrus\n$buffer\nArtica will try to repair it but it should not working\n";
		$buffer=$buffer."Perhaps you need to contact your support to correctly recover cyrus databases\n";
		$buffer=$buffer."Notice,read this topic : http://www.gradstein.info/software/how-to-recover-from-cyrus-when-you-have-some-db-errors/\n";
		THREAD_COMMAND_SET('/usr/share/artica-postfix/bin/artica-install --cyrus-recoverdb');
		email_events("Cyrus database error !!",$buffer,"mailbox");
		events("DBERROR detected, take action");
		@unlink($file);
		file_put_contents($file,"#");
	}else{
		events("(fatal region error detected; run recovery) DBERROR detected, but take action after 10mn");
	}
	return null;	
}


if(preg_match("#cyrus.+?DBERROR\s+db[0-9]+:\s+DB_AUTO_COMMIT may not be specified in non-transactional environment#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/cyrus.db.error";
	if(file_time_min($file)>10){
		$buffer="Artica has detected a fatal error on cyrus\n$buffer\nArtica will try to repair it but it should not working\n";
		$buffer=$buffer."Perhaps you need to contact your support to correctly recover cyrus databases\n";
		$buffer=$buffer."Notice,read this topic : http://www.gradstein.info/software/how-to-recover-from-cyrus-when-you-have-some-db-errors/\n";
		$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET('/usr/share/artica-postfix/bin/artica-install --cyrus-ctl-cyrusdb');
		email_events("Cyrus database error !!",$buffer,"mailbox");
		events("DBERROR detected, take action");
		@unlink($file);
		file_put_contents($file,"#");
	}else{
		events("(DB_AUTO_COMMIT may not be specified in non-transactional) DBERROR detected, but take action after 10mn");
	}
	return null;
}

if(preg_match("#tlsmgr.+?fatal: open database .+?Stale NFS file handle#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/tlsmgr.Stale.NFS.file.handle";
	if(file_time_min($file)>10){
		$buffer="Artica has detected a fatal error on Postfix (tls manager)\n$buffer\nTo fix this issue, you need to reboot the computer\n";
		$buffer=$buffer."In order to release locked file\nIf reboot trough Artica did not working, run this commandline :\nshutdown -rF now";
		email_events("Stale NFS file handle !!",$buffer,"postfix");
		events("Stale NFS file handle");
		@unlink($file);
		file_put_contents($file,"#");
	}else{
		events("tlsmgr:Stale NFS file handle, but take action after 10mn");
	}
	return null;
}






if(preg_match("#cyrus.+?:\s+DBERROR:\s+opening.+?mailboxes.db:\s+cyrusdb error#",$buffer)){
	$file="/etc/artica-postfix/croned.1/cyrus.db.error";
	if(file_time_min($file)>10){
		$buffer="Artica has detected a fatal error on cyrus\n$buffer\nArtica will try to repair it but it should not working\n";
		$buffer=$buffer."Perhaps you need to contact your support to correctly recover cyrus databases\n";
		$buffer=$buffer."Notice,read this topic : http://www.gradstein.info/software/how-to-recover-from-cyrus-when-you-have-some-db-errors/\n";
		$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET('/usr/share/artica-postfix/bin/artica-install --cyrus-recoverdb');
		email_events("Cyrus database error !!",$buffer,"mailbox");
		@unlink($file);
		file_put_contents($file,"#");
	}else{
		events("DBERROR detected, but take action after 10mn");
	}
	return null;	
}


if(preg_match('#cyrus\/(.+?)\[.+?login:(.+?)\[(.+?)\]\s+(.+?)\s+.+?User#',$buffer,$re)){
	$service=trim($re[1]);
	$server=trim($re[2]);
	$server_ip=trim($re[3]);
	$user=trim($re[4]);
	cyrus_imap_conx($service,$server,$server_ip,$user);
	return null;
}

if(preg_match("#zarafa-gateway\[.+?:\s+IMAP Login from\s+(.+)\s+for user\s+(.+?)\s+#",$buffer,$re)){
	$service="IMAP";
	$server=trim($re[1]);
	$server_ip=trim($re[1]);
	$user=trim($re[2]);
	cyrus_imap_conx($service,$server,$server_ip,$user);
	return null;
}




if(preg_match('#cyrus\/ctl_mboxlist.+?DBERROR: reading.+?, assuming the worst#',$buffer)){
	$file="/etc/artica-postfix/croned.1/cyrus.db1.error";
	if(file_time_min($file)>10){
		$buffer="Artica has detected a fatal error on cyrus\n$buffer\n\n";
		email_events("Cyrus database error !!",$buffer,"mailbox");
		@unlink($file);
		file_put_contents($file,"#");
	}
	return null;	
}
if(preg_match('#cyrus\/sync_client.+?Can not connect to server#',$buffer)){
	$file="/etc/artica-postfix/croned.1/cyrus.cluster.error";
	if(file_time_min($file)>10){
		$buffer="Artica has detected that the cyrus cluster replica is not available on cyrus\n$buffer\n\n";
		email_events("Cyrus replica not available",$buffer,"mailbox");
		@unlink($file);
		file_put_contents($file,"#");
	}
	return null;	
}

if(preg_match('#cyrus\/sync_client.+?connect.+?failed: No route to host#',$buffer)){
	$file="/etc/artica-postfix/croned.1/cyrus.cluster.error";
	if(file_time_min($file)>10){
		$buffer="Artica has detected that the cyrus cluster replica is not available on cyrus\n$buffer\n\n";
		email_events("Cyrus replica not available",$buffer,"mailbox");
		@unlink($file);
		file_put_contents($file,"#");
	}
	return null;	
}

if(preg_match('#warning: dict_ldap_connect: Unable to bind to server ldap#',$buffer)){
	$file="/etc/artica-postfix/croned.1/ldap.error";
	if(file_time_min($file)>10){
		email_events("Postfix is unable to connect to ldap server ",$buffer,"system");
		@unlink($file);
		file_put_contents($file,"#");
	}
	return null;
}





if(preg_match('#service pop3 pid.+?in BUSY state and serving connection#',$buffer)){
	$file="/etc/artica-postfix/croned.1/pop3-busy.error";
	if(file_time_min($file)>10){
		email_events("Pop3 service is overloaded","pop3 report:\n$buffer\nPlease,increase pop3 childs connections in artica Interface","mailbox");
		@unlink($file);
		file_put_contents($file,"#");
	}
	return null;
}

if(preg_match('#milter inet:[0-9\.]+:1052.+?Connection timed out#',$buffer)){
	$file="/etc/artica-postfix/croned.1/KAV-TIMEOUT.error";
	if(file_time_min($file)>10){
		email_events("Postfix service Cannot connect to Kaspersky Antivirus milter",
		"it report:\n$buffer\nPlease,disable Kaspersky service or contact your support",
		"postfix");
		@unlink($file);
		file_put_contents($file,"#");
	}
	return null;
}

if(preg_match('#milter unix:/var/run/milter-greylist/milter-greylist.sock.+?Connection timed out#',$buffer)){
	$file="/etc/artica-postfix/croned.1/miltergreylist-TIMEOUT.error";
	if(file_time_min($file)>10){
		email_events("milter-greylist error",
		"it report:\n$buffer\nPlease,investigate what plugin cannot send to milter-greylist events",
		"postfix");
		@unlink($file);
		file_put_contents($file,"#");
	}
	return null;
}


if(preg_match('#SASL authentication failure: cannot connect to saslauthd server#',$buffer)){
	$file="/etc/artica-postfix/croned.1/saslauthd.error";
	if(file_time_min($file)>10){
		email_events("saslauthd failed to run","it report:\n$buffer\nThis error is fatal, nobody can be logged on the system.","mailbox");
		@unlink($file);
		file_put_contents($file,"#");
	}
	return null;
}


if(preg_match("#smtp.+?warning:\s+(.+?)\[(.+?)\]:\s+SASL DIGEST-MD5 authentication failed#",$buffer,$re)){
	$router_name=$re[1];
	$ip=$re[2];
	smtp_sasl_failed($router_name,$ip,$buffer);
	return null;
}



if(preg_match('#warning: connect to Milter service unix:/var/run/kas-milter.socket: Permission denied#',$buffer)){
	$file="/etc/artica-postfix/croned.1/kas-perms.error";
	if(file_time_min($file)>10){
		email_events("Kaspersky Anti-spam socket error","it report:\n$buffer\nArtica will restart kas service...","postfix");
		@unlink($file);
		file_put_contents($file,"#");
		$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart kas3');
		
	}
	return null;
}


if(preg_match('#smtpd.+?warning: problem talking to server (.+?):\s+Connection refused#',$buffer,$re)){
	$pb=md5($re[1]);
	
	$file="/etc/artica-postfix/croned.1/postfix-talking.$pb.error";
	$time=file_time_min($file);
	if($time>10){
		events("Postfix routing error {$re[1]}");
		email_events("Postfix routing error {$re[1]}","it report:\n$buffer\nPlease take a look of your routing table","postfix");
		@unlink($file);
		file_put_contents($file,"#");
	}
	events("Postfix routing error {$re[1]} (SKIP) $time/10mn");
	return null;
	
}



if(preg_match("#sync_client.+?connect\((.+?)\) failed: Connection refused#",$buffer,$re)){
$file="/etc/artica-postfix/croned.1/".md5($buffer);
	if(file_time_min($file)>10){
		email_events("Cyrus replica {$re[1]} cluster failed","it report:\n$buffer\n
		please check your support, mails will not be delivered until replica is down !","mailbox");
		@unlink($file);
		file_put_contents($file,"#");
	}
	return null;	
}


if(preg_match("#could not connect to amavisd socket /var/spool/postfix/var/run/amavisd-new/amavisd-new.sock: No such file or directory#",$buffer)){
	amavis_socket_error($buffer);
	return null;
	}
	
if(preg_match("#could not connect to amavisd socket.+?Connection timed out#",$buffer)){
	amavis_socket_error($buffer);
	return null;	
}

if(preg_match("#NOQUEUE: reject:.+?from.+?\[([0-9\.]+)\]:.+?Sender address rejected: Domain not found; from=<(.+?)> to=<(.+?)> proto#",$buffer,$re)){
	event_message_reject_hostname("Domain not found",$re[2],$re[3],$re[1]);
	events("{$re[1]} Domain not found from=<{$re[2]}> to=<{$re[3]}>");
	return null;
	}
	
if(preg_match("#NOQUEUE: reject:.+?from.+?\[([0-9\.]+)\]:.+?Client host rejected: cannot find your hostname.+?from=<(.+?)> to=<(.+?)> proto#",$buffer,$re)){
	event_message_reject_hostname("hostname not found",$re[2],$re[3],$re[1]);
	return null;
}

if(preg_match("#smtpd.+?NOQUEUE:.+?from.+?\[(.+?)\].+?Client host rejected.+?reverse hostname.+?from=<(.+?)>.+?to=<(.+?)>#",$buffer,$re)){
	event_message_reject_hostname("hostname not found",$re[2],$re[3],$re[1]);
	return null;
}

if(preg_match("#smtpd.+?NOQUEUE: reject.+?from.+?\[(.+?)\].+?Helo command rejected:.+?from=<(.+?)> to=<(.+?)>#",$buffer,$re)){
	event_message_reject_hostname("Helo command rejected",$re[2],$re[3],$re[1]);
	return null;
}
if(preg_match("#smtpd.+?NOQUEUE: reject.+?from.+?\[(.+?)\].+?4.3.5 Server configuration problem.+?from=<(.+?)> to=<(.+?)>#",$buffer,$re)){
	event_message_reject_hostname("Server configuration problem",$re[2],$re[3],$re[1]);
	return null;
}

if(preg_match("#postfix.+?\[.+?reject: header.+?from.+?\[([0-9\.]+)\];\s+from=<(.*?)>\s+to=<(.+?)>.+? too many rec.+?pients#",$buffer,$re)){
	events("too many recipients from {$re[2]} to {$re[3]}");
	if($GLOBALS["PostfixNotifyMessagesRestrictions"]==1){
		events("-> notification...");
		$GLOBALS["CLASS_UNIX"]->send_email_events("Blocked message too many recipients from {$re[2]}","Postfix claims $buffer","postfix");
	}
	event_message_reject_hostname("too many recepients",$re[2],$re[3],$re[1]);
	return null;
}



if(preg_match("#cyrus.+?badlogin:\s+(.+?)\s+\[(.+?)\]\s+.+?\s+(.+?)\s+(.+)#",$buffer,$re)){
	$router=$re[1];
	$ip=$re[2];
	$user=$re[3];
	$error=$re[4];
	cyrus_bad_login($router,$ip,$user,$error);
	return null;
}



if(preg_match("#IOERROR.+?fstating sieve script\s+(.+?):\s+No such file or directory#",$buffer,$re)){
	$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("/bin/touch \"".trim($re[1])."\"");
	return null;
}



if(preg_match("#smtp.+?\].+?([A-Z0-9]+):\s+to=<(.+?)>.+?status=deferred.+?\((.+?)command#",$buffer,$re)){
	event_message_rejected("deferred",$re[1],$re[2],$re[3]);
	return null;
}



if(preg_match("#smtp.+?:\s+(.+?):\s+to=<(.+?)>,\s+relay=none,.+?status=deferred \(connect to .+?\[(.+?)\].+?Connection refused#",$buffer,$re)){
	event_message_rejected("Connection refused",$re[1],$re[2],$re[3]);
	return null;
}



if(preg_match("#smtp.+?\].+?([A-Z0-9]+):.+?SASL authentication failed#",$buffer,$re)){
	event_messageid_rejected($re[1],"Authentication failed");
	return null;
}
if(preg_match("#smtp.+?\].+?([A-Z0-9]+):.+?refused to talk to me.+?554 RBL rejection#",$buffer,$re)){
	ImBlackListed($re[2],$buffer);
	event_messageid_rejected($re[1],"Your are blacklisted");
	return null;
}


if(preg_match("#smtp\[.+?:\s+(.+?):\s+to=<(.+?)>,\s+relay=.+?\[(.+?)\].+?status=deferred.+?refused to talk to me#",$buffer,$re)){
	ImBlackListed($re[3],$buffer);
	event_messageid_rejected($re[1],"Your are blacklisted",$re[3],$re[2]);
	return null;
}

if(preg_match("#postfix\/bounce\[.+?:\s+(.+?):\s+sender non-delivery notification#",$buffer,$re)){
	events("{$re[1]} non-delivery");
	event_messageid_rejected($re[1],"non-delivery",null,null);
	return null;
	}	


if(preg_match("#smtp\[.+?\]:\s+(.+?):\s+to=<(.+?)>, relay=(.+?)\[.+?status=bounced\s+\(.+?loops back to myself#",$buffer,$re)){
	event_messageid_rejected($re[1],"loops back to myself",$re[3],$re[2]);
	return null;
}



if(preg_match("#smtp\[.+?:\s+(.+?):\s+host.+?\[(.+?)\]\s+refused to talk to me:#",$buffer,$re)){
	ImBlackListed($re[2],$buffer);
	event_messageid_rejected($re[1],"Your are blacklisted",$re[2]);
	return null;
}





if(preg_match('#milter-greylist:.+?:.+?addr.+?from <(.+?)> to <(.+?)> delayed for#',$buffer,$re)){
	event_message_milter_reject(md5($re[1].$re[2].date('Y-m d H is')),"Greylisting",$re[1],$re[2],$buffer);
	return null;
}

if(preg_match('#milter-greylist:.+?addr.+?\[(.+?)\] from <> to <(.+?)> delayed#',$buffer,$re)){
	event_message_milter_reject(md5($re[1].$re[2].date('Y-m d H is')),"Greylisting","unknown",$re[2],$buffer);
	return null;
}

if(preg_match('#milter-greylist: \(unknown id\): addr.+?\[(.+?)\] from\s+=(.+?)> to <(.+?)>\s+delayed#',$buffer,$re)){
	event_message_milter_reject(md5($re[1].$re[2].time()),"Greylisting",$re[2],$re[3],$buffer,$re[1]);
	return null;
}

if(preg_match("#assp.+?<(.+?)>\s+to:\s+(.+?)\s+recipient delayed#",$buffer,$re)){
	event_message_milter_reject(md5($re[1].$re[2].date('Y-m d H is')),"Greylisting",$re[1],$re[2],$buffer);
	return null;
}

if(preg_match("#assp.+?MessageScoring.+?<(.+?)>\s+to:\s+(.+?)\s+\[spam found\]#",$buffer,$re)){
	event_message_milter_reject(md5($re[1].$re[2].date('Y-m d H is')),"SPAM",$re[1],$re[2],$buffer);
	return null;
}
if(preg_match("#assp.+?MalformedAddress.+?<(.+?)>\s+to:\s+(.+?)\s+\malformed address:'|(.+?)'#",$buffer,$re)){
	event_message_milter_reject(md5($re[1].$re[2].date('Y-m d H is')),"malformed address",$re[1],$re[2],$buffer);
	return null;
}

if(preg_match("#assp.+?\[Extreme\]\s+(.+?)\s+<(.+?)>\s+to:\s+(.+?)\s+\[spam found\]#",$buffer,$re)){
	event_message_milter_reject(md5($re[1].$re[2].date('Y-m d H is')),"SPAM",$re[2],$re[3],$buffer,$re[1]);
	return null;	
}


if(preg_match("#assp.+?<(.*?)>\s+to:\s+(.+?)\s+bounce delayed#",$buffer,$re)){
	if($re[1]==null){$re[1]="Unknown";}
	event_message_milter_reject(md5($re[1].$re[2].date('Y-m d H is')),"bounce delayed",$re[1],$re[2],$buffer);
}

if(preg_match("#assp.+?\[DNSBL\]\s+(.+?)\s+<(.*?)>\s+to:\s+(.+?)\s+#",$buffer,$re)){
	if($re[2]==null){$re[2]="Unknown";}
	event_message_reject_hostname("DNSBL",$re[2],$re[3],$re[1]);
	return null;
}
if(preg_match("#assp.+?\[URIBL\]\s+(.+?)\s+<(.*?)>\s+to:\s+(.+?)\s+#",$buffer,$re)){
	if($re[2]==null){$re[2]="Unknown";}
	event_message_reject_hostname("URIBL",$re[2],$re[3],$re[1]);
	return null;
}


if(preg_match("#assp.+?\[SpoofedSender\]\s+(.+?)\s+<(.*?)>\s+to:\s+(.+?)\s+.+?No Spoofing Allowed#",$buffer,$re)){
	if($re[2]==null){$re[2]="Unknown";}
	event_message_reject_hostname("SPOOFED",$re[2],$re[3],$re[1]);
	return null;
}
if(preg_match("#assp.+?\[InvalidHELO\]\s+(.+?)\s+<(.*?)>\s+to:\s+(.+?)\s+#",$buffer,$re)){
	if($re[2]==null){$re[2]="Unknown";}
	event_message_reject_hostname("BAD HELO",$re[2],$re[3],$re[1]);
	return null;
}


if(preg_match("#NOQUEUE: reject: RCPT from.+?<(.+?)>: Recipient address rejected: User unknown in relay recipient table;.+?to=<(.+?)> proto=SMTP#",
$buffer,$re)){
	$id=md5($re[1].$re[2].date('Y-m d H is'));
	event_finish($id,$re[2],"reject","User unknown",$re[1]);
	return null;
	
}

if(preg_match("#postfix\/lmtp.+?:\s+(.+?):\s+to=<(.+?)>.+?said:\s+550-Mailbox unknown#",$buffer,$re)){
	$id=$re[1];
	$to=$re[2];
	event_message_milter_reject($id,"Mailbox unknown",null,$re[2],$buffer);
	mailbox_unknown($buffer,$to);
	return null;
}


if(preg_match('#: (.+?): reject: RCPT.+?Relay access denied; from=<(.+?)> to=<(.+?)> proto=SMTP#',$buffer,$re)){
	if($re[1]=="NOQUEUE"){$re[1]=md5($re[3].$re[2].date('Y-m d H is'));}
	event_finish($re[1],$re[3],"reject","Relay access denied",$re[2],$buffer);
	return null;
}

if(preg_match('#postfix.+?cleanup.+?:\s+(.+?):\s+milter-reject: END-OF-MESSAGE.+4.6.0 Content scanner malfunction; from=<(.+?)> to=<(.+?)> proto=SMTP#',
$buffer,$re)){
	events("{$re[1]} Content scanner malfunction from=<{$re[2]}> to=<{$re[3]}>");
	event_Content_scanner_malfunction($re[1],$re[2],$re[3]);
	return null;
}
if(preg_match("#postfix.+?cleanup.+?:\s+(.+?):\s+milter-discard.+?END-OF-MESSAGE.+?DISCARD.+?from=<(.+?)> to=<(.+?)> proto=SMTP#",
$buffer,$re)){
	event_DISCARD($re[1],$re[2],$re[3],$buffer);
	return null;
}

if(preg_match("#cleanup\[.+?:\s+(.+?):\s+milter-discard: END-OF-MESSAGE from.+?\[(.+?)\]:\s+milter triggers DISCARD action;\s+from=<(.+?)>\s+to=<(.+?)>#",
$buffer,$re)){
	event_DISCARD($re[1],$re[3],$re[4],$buffer,$re[2]);
	return null;
}
	
if(preg_match("#^([A-ZA-z]+)\s+([0-9]+)\s+([0-9\:]+).+?:\s+([A-Z0-9]+):\s+client=(.+)#",$buffer,$re)){
	$date=date('Y-m-d H:i:s');
	event_newmail($re[4],$date);
	return null;
}



if(preg_match("#^([A-ZA-z]+)\s+([0-9]+)\s+([0-9\:]+).+?:\s+([A-Z0-9]+):\s+message-id=<(.*?)>#",$buffer,$re)){
	events("NEW message_id {$re[4]} {$re[5]}");
	event_message_id($re[4],$re[5]);
	return null;	
}
if(preg_match("#^([A-ZA-z]+)\s+([0-9]+)\s+([0-9\:]+).+?:\s+([A-Z0-9]+):\s+from=<(.*?)>, size=([0-9]+)#",$buffer,$re)){
	events("NEW MAIL {$re[4]} <{$re[5]}> ({$re[6]} bytes)");
	event_message_from($re[4],$re[5],$re[6]);
	return null;
}

if(preg_match("#NOQUEUE: milter-reject: RCPT from.+?: 451 4.7.1 Greylisting in action, please come back in .+?; from=<(.+?)> to=<(.+?)> proto=SMTP#",$buffer,$re)){
	event_message_milter_reject(md5($re[1].$re[2].date('Y-m d H is')),"Greylisting",$re[1],$re[2],$buffer);
	return null;
}

if(preg_match("#^([A-ZA-z]+)\s+([0-9]+)\s+([0-9\:]+).+?:\s+([A-Z0-9]+):\s+milter-reject:.+?:(.+?)\s+from=<(.+?)>#",$buffer,$re)){
	events("milter-reject {$re[4]} <{$re[5]}> ({$re[6]})");
	event_message_milter_reject($re[4],$re[5],$re[6],null,$buffer);
	return null;
}




if(preg_match("#^([A-ZA-z]+)\s+([0-9]+)\s+([0-9\:]+).+?:\s+([A-Z0-9]+):\s+to=<(.+?)>,\s+orig_to=<.+?>,\s+relay=(.+?),\s+delay=.+?,\s+delays=.+?,\s+dsn=.+?,\s+status=([a-zA-Z]+)#"
,$buffer,$re)){
	if(preg_match('#\s+status=.+?\s+\((.+?)\)#',$buffer,$ri)){
		$bounce_error=$ri[1];
	}
   events("Finish {$re[4]} <{$re[5]}> ({$re[7]})");
   event_finish($re[4],$re[5],$re[7],$bounce_error,null,$buffer);   
   return null;
	
}
if(preg_match("#^([A-ZA-z]+)\s+([0-9]+)\s+([0-9\:]+).+?:\s+([A-Z0-9]+):\s+to=<(.+?)>,\s+relay=(.+?),\s+delay=.+?,\s+delays=.+?,\s+dsn=.+?,\s+status=([a-zA-Z]+)#"
,$buffer,$re)){
	if(preg_match('#\s+status=.+?\s+\((.+?)\)#',$buffer,$ri)){
		$bounce_error=$ri[1];
	}
   event_finish($re[4],$re[5],$re[7],$bounce_error,null,$buffer);   
   return null;	
}

	
//-------------------------------------------------------------- ERRORS

if(preg_match('#amavisd-milter.+?could not read from amavisd socket.+?\.sock:Connection timed out#',$buffer,$re)){
	amavis_socket_error($buffer);
	return null;
}

if(preg_match('#warning: milter unix.+?amavisd-milter.sock:.+SMFIC_MAIL reply packet header: Broken pipe#',$buffer,$re)){
	amavis_error_restart($buffer);
	return null;
}
if(preg_match('#sfupdates.+?KASERROR.+?keepup2date\s+failed.+?code.+?critical error#',$buffer,$re)){
	kas_error_update($buffer);
	return null;
}


if(preg_match('#lmtp.+?:\s+(.+?): to=<(.+?)>,.+?status=deferred.+?connect to .+?\[(.+?)\].+?No such file or directory#',
$buffer,$re)){
	event_message_milter_reject($re[1],"deferred",null,$re[1]);
	cyrus_socket_error($buffer,"$re[3]");
	return null;
}

if(preg_match('#lmtp.+?:(.+?):\s+to=<(.+?)>.+?said: 550-Mailbox unknown#',$buffer,$re)){
	event_message_milter_reject($re[1],"Mailbox unknown",null,$re[2]);
	mailbox_unknown($buffer,$re[2]);
	return null;
}

events_not_filtered("Not Filtered:\"$buffer\"");	
}


function regex_amavis($buffer){
	
if(strpos($buffer,"AM.PDP  /var/amavis")>0){return true;}	
	
if(preg_match("#\[[0-9]+\]:\s+\([0-9]+\)\s+Checking:.+?AM\.PDP-SOCK#",$buffer)){return true;}

if(preg_match("#\)\s+Blocked SPAM, AM\.PDP-SOCK\s+\[(.+?)\].+?<(.*?)>.+?<(.+?)>.+?Queue-ID:\s+(.+?),.+?size:\s+([0-9]+)#",$buffer,$re)){
	amavis_spam($re[4],$re[1],$re[2],$re[3],$re[5],"SPAM");
	return true;
}
if(preg_match("#\)\s+Passed\s+BAD-HEADER,.+?\[(.+?)\].+?<(.*?)>.+?<(.+?)>.+?\s+Queue-ID:\s+(.+?),.+?size:\s+([0-9]+)#",$buffer,$re)){
	amavis_spam($re[4],$re[1],$re[2],$re[3],$re[5],"Sended");
	return true;
}

	
if(preg_match("#\)\s+Blocked SPAMMY,.+?\[(.+?)\].+?<(.*?)>.+?<(.+?)>.+?,\s+Queue-ID:\s+(.+?),.+?size:\s+([0-9]+)#",$buffer,$re)){
	amavis_spam($re[4],$re[1],$re[2],$re[3],$re[5],"SPAMMY");
	return true;
}

if(preg_match("#\)\s+Passed CLEAN,.+?\[(.+?)\].+?<(.*?)>.+?<(.+?)>.+?\s+Queue-ID:\s+(.+?),.+?size:\s+([0-9]+)#",$buffer,$re)){
	amavis_spam($re[4],$re[1],$re[2],$re[3],$re[5],"Sended");
	return true;
}
if(preg_match("#smtp.+?status=deferred.+?connect.+?\[127\.0\.0\.1\]:10024: Connection refused#",$buffer,$re)){
	AmavisConfigErrorInPostfix($buffer);
	return true;
}


}


function events($text){
		$filename=basename(__FILE__);
		$logFile="/var/log/artica-postfix/postfix-logger.debug";
		if(!isset($GLOBALS["CLASS_UNIX"])){
			include_once(dirname(__FILE__)."/framework/class.unix.inc");
			$GLOBALS["CLASS_UNIX"]=new unix();
		}
		$GLOBALS["CLASS_UNIX"]->events("$filename $text",$logFile);		
}
		
function event_Content_scanner_malfunction($postfix_id,$from,$to){
	if($GLOBALS["EnableArticaSMTPStatistics"]==0){return;}
	$file="/var/log/artica-postfix/RTM/$postfix_id.msg";
	$ini=new Bs_IniHandler($file);
	$ini->set("TIME","mailfrom","$from");
	$ini->set("TIME","mailto","$to");
	$ini->set("TIME","bounce_error","Content scanner malfunction");
	$ini->set("TIME","time_end",date("Y-m-d H:i:s"));
	$ini->set("TIME","delivery_success","no");
	$ini->saveFile($file);	
}
function event_DISCARD($postfix_id,$from,$to,$buffer=null,$ipaddr=null){
	if($GLOBALS["EnableArticaSMTPStatistics"]==0){return;}
	$file="/var/log/artica-postfix/RTM/$postfix_id.msg";
	$ini=new Bs_IniHandler($file);
	if($ipaddr==null){
		if(preg_match("#from.+?\[([0-9\.]+)?\]#",$buffer,$re)){$ini->set("TIME","smtp_sender",$re[1]);$ipaddr=$re[1];}
	}else{
		$ini->set("TIME","smtp_sender",$ipaddr);
	}	
	
	if(preg_match("#[0-9]+\.[0-9]+\.[0-9]+\.#",$ipaddr)){
		$hostname=gethostbyaddr($ipaddr);
		Postfix_Addconnection_error($hostname,$ipaddr,"Discard");
	}	
	
	$ini->set("TIME","mailfrom","$from");
	$ini->set("TIME","mailto","$to");
	$ini->set("TIME","bounce_error","Discard");
	$ini->set("TIME","time_end",date("Y-m-d H:i:s"));
	$ini->set("TIME","delivery_success","no");
	$ini->saveFile($file);	
}
function event_newmail($postfix_id,$date){
	if($GLOBALS["EnableArticaSMTPStatistics"]==0){return;}
	$file="/var/log/artica-postfix/RTM/$postfix_id.msg";
	$ini=new Bs_IniHandler($file);
	$ini->set("TIME","time_connect",$date);
	$ini->set("TIME","delivery_success","no");
	$ini->saveFile($file);	
}
function event_message_from($postfix_id,$from,$size){
	if($GLOBALS["EnableArticaSMTPStatistics"]==0){return;}	
	$file="/var/log/artica-postfix/RTM/$postfix_id.msg";
	$ini=new Bs_IniHandler($file);
	$ini->set("TIME","mailfrom",$from);
	$ini->set("TIME","mailsize",$size);
	$ini->set("TIME","time_connect",date("Y-m-d H:i:s"));
	$ini->set("TIME","delivery_success","no");
	$ini->saveFile($file);	
}
function event_message_milter_reject($postfix_id,$reject,$from,$to=null,$buffer=null,$sender=null){
	if($GLOBALS["EnableArticaSMTPStatistics"]==0){return;}
	$file="/var/log/artica-postfix/RTM/$postfix_id.msg";
	$ini=new Bs_IniHandler($file);
	if($sender==null){
		if(preg_match("#from.+?\[([0-9\.]+)?\]#",$buffer,$re)){$ini->set("TIME","smtp_sender",$re[1]);}	
		if(preg_match("#assp\[.+?\]:\s+.+?\s+(.+?)\s+<#",$buffer,$re)){$ini->set("TIME","smtp_sender",$re[1]);}
		if(preg_match("#[0-9]+\.[0-9]+\.[0-9]+\.#",$re[1])){
			$hostname=gethostbyaddr($re[1]);
			Postfix_Addconnection_error($hostname,$re[1],$reject);
		}		
	}
	if($to<>null){$ini->set("TIME","mailto",$to);}
	if($from<>null){$ini->set("TIME","mailfrom",$from);}
	$ini->set("TIME","bounce_error",$reject);
	$ini->set("TIME","time_connect",date("Y-m-d H:i:s"));
	$ini->set("TIME","delivery_success","no");
	$ini->saveFile($file);	
}

function event_message_reject_hostname($reject,$from,$to=null,$server){
	if($GLOBALS["EnableArticaSMTPStatistics"]==0){return;}
	$to=str_replace("|","",$to);
	$to=trim($to);
	if(preg_match("#[0-9]+\.[0-9]+\.[0-9]+\.#",$server)){
		$hostname=gethostbyaddr($server);
		Postfix_Addconnection_error($hostname,$server,$reject);
	}
	
	
	$file="/var/log/artica-postfix/RTM/".md5(date("Y-m-d H:i:s").$server.$from).".msg";
	events("$reject: $server from=<$from>< to=<$to> in line ".__LINE__." event: <".basename($file).">");
	
	$ini=new Bs_IniHandler($file);
	$ini->set("TIME","smtp_sender",$server);	
	if($to<>null){$ini->set("TIME","mailto",$to);}
	if($from<>null){$ini->set("TIME","mailfrom",$from);}
	$ini->set("TIME","bounce_error",$reject);
	$ini->set("TIME","time_connect",date("Y-m-d H:i:s"));
	$ini->set("TIME","delivery_success","no");
	$ini->saveFile($file);
	if(!is_file($file)){
		events("$reject:".basename($file)." error writing in line ".__LINE__);
	}
}


function event_messageid_rejected($msg_id_postfix,$error,$server=null,$to=null){
	if($GLOBALS["EnableArticaSMTPStatistics"]==0){return;}
	$file="/var/log/artica-postfix/RTM/$msg_id_postfix.msg";
	$ini=new Bs_IniHandler($file);
	if($server<>null){$ini->set("TIME","smtp_sender",$server);}
	if($to<>null){$ini->set("TIME","mailto",$to);}
	$ini->set("TIME","delivery_success","no");
	$ini->set("TIME","bounce_error",$error);
	$ini->set("TIME","time_connect",date("Y-m-d H:i:s"));
	$ini->saveFile($file);		
}

function event_message_rejected($reject,$msg_id_postfix,$to=null,$buffer){
	if($GLOBALS["EnableArticaSMTPStatistics"]==0){return;}
	$file="/var/log/artica-postfix/RTM/$msg_id_postfix.msg";
	$ini=new Bs_IniHandler($file);
	
	if(preg_match("#invalid sender domain#",$buffer)){
		$reject="Invalid sender domain";
	}
	
	if(preg_match("#^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+#",$buffer)){
		$ini->set("TIME","server_from","$buffer");
	}
	
	if($to<>null){$ini->set("TIME","mailto",$to);}
	
	$ini->set("TIME","bounce_error",$reject);
	$ini->set("TIME","time_connect",date("Y-m-d H:i:s"));
	$ini->set("TIME","delivery_success","no");
	$ini->saveFile($file);	
}

function event_message_id($postfix_id,$messageid){
	if($GLOBALS["EnableArticaSMTPStatistics"]==0){return;}
	$file="/var/log/artica-postfix/RTM/$postfix_id.msg";
	$ini=new Bs_IniHandler($file);
	$ini->set("TIME","message-id","$messageid");
	$ini->set("TIME","time_connect",date("Y-m-d H:i:s"));
	$ini->saveFile($file);		
}
		
function event_greylisted($server,$from){
	if($GLOBALS["EnableArticaSMTPStatistics"]==0){return;}
	$file="/var/log/artica-postfix/RTM/".md5(date("Y-m-d H:i:s").$server.$from).".msg";
	$ini=new Bs_IniHandler($file);
	
	if(preg_match("#[0-9]+\.[0-9]+\.[0-9]+\.#",$server)){
		$hostname=gethostbyaddr($server);
		Postfix_Addconnection_error($hostname,$server,"greylist");
	}	
	
	$ini->set("TIME","mailfrom","$from");
	$ini->set("TIME","server_from","$server");
	$ini->set("TIME","bounce_error","greylisted");
	$ini->set("TIME","time_end",date("Y-m-d H:i:s"));
	$ini->set("TIME","delivery_success","no");
	$ini->saveFile($file);
}

function amavis_spam($postfix_id,$smtp_sender,$from,$to,$size,$action){
	if($GLOBALS["EnableArticaSMTPStatistics"]==0){return;}
	$file="/var/log/artica-postfix/RTM/$postfix_id.msg";
	$ini=new Bs_IniHandler($file);
	$ini->set("TIME","message-id","$postfix_id");
	$ini->set("TIME","time_end",date("Y-m-d H:i:s"));
	$ini->set("TIME","bounce_error",$action);
	$ini->set("TIME","size",$size);
	if($to<>null){$ini->set("TIME","mailto",$to);}
	if($from<>null){$ini->set("TIME","mailfrom","$from");}
	if($smtp_sender<>null){$ini->set("TIME","server_from","$smtp_sender");}
	if($action<>"Sended"){
		$ini->set("TIME","delivery_success","no");
		Postfix_Addconnection_error(null,$smtp_sender,"SPAM");
	}else{
		$ini->set("TIME","delivery_success","yes");
		
	}
	$ini->saveFile($file);
}

function event_finish($postfix_id,$to,$status,$bounce_error,$from=null,$buffer=null){
	if($GLOBALS["EnableArticaSMTPStatistics"]==0){return;}
    $delivery_success='yes';
    if($status=='bounced'){$delivery_success='no';}
	if($status=='deferred'){$delivery_success='no';}
	if($status=='reject'){$delivery_success='no';}
	if($status=='expired'){$delivery_success='no';}
    
	if(preg_match("#Queued mail for delivery#",$bounce_error)){
		$status="Deliver";
		$delivery_success="yes";
		$bounce_error="Sended";
	}
	
	if(preg_match("#Sender address rejected: need fully-qualified address#",$bounce_error)){
		$status="rejected";
		$delivery_success="no";
		$bounce_error="need fully-qualified address";
	}
	
	if(preg_match("#no mailbox here#",$bounce_error)){
		$status="rejected";
		$delivery_success="no";
		$bounce_error="Mailbox Unknown";
	}	
	
	if(preg_match("#refused to talk to me.+?RBL rejection#",$bounce_error)){
			$status="rejected";
			$delivery_success="no";
			$bounce_error="RBL";
	}

	if(preg_match("#550.+?Service unavailable.+?blocked using.+?RBL#",$bounce_error)){
			$status="rejected";
			$delivery_success="no";
			$bounce_error="RBL";
	}
	
	if(preg_match("#554 : Recipient address rejected: Access denied#",$bounce_error)){
			$status="rejected";
			$delivery_success="no";
			$bounce_error="Access denied";
	}	
	
	
	if(preg_match("#451 4.2.0 Mailbox has an invalid format#",$bounce_error)){
			$status="rejected";
			$delivery_success="no";
			$bounce_error="Mailbox corrupt";
	}		
	
	if(preg_match("#delivered via#",$bounce_error)){
		$status="Deliver";
		$delivery_success="yes";
		$bounce_error="Sended";
	}
	
	
	if(preg_match("#Content scanner malfunction#",$bounce_error)){
		$status="Error";
		$delivery_success="no";
		$bounce_error="Content scanner malfunction";
	}
	
	if(preg_match("#4\.5\.0 Failure#",$bounce_error)){
		$status="Error";
		$delivery_success="no";
		$bounce_error="Error";
	}	
	
	
	if(preg_match("#250 2\.0\.0 Ok#",$bounce_error)){
		$status="Deliver";
		$delivery_success="yes";
		$bounce_error="Sended";
	}
	
	if(preg_match("#Host or domain name not found#",$bounce_error)){
		$status="Error";
		$delivery_success="no";
		$bounce_error="Host or domain name not found";
	}
	
	
	if(preg_match("#4\.5\.0 Error in processing#",$bounce_error)){
		$status="Error";
		$delivery_success="no";
		$bounce_error="Error";
	};
	
if(preg_match("#Sender address rejected.+?Domain not found#",$bounce_error)){
		$status="Error";
		$delivery_success="no";
		$bounce_error="Domain not found";
	};	
	
if(preg_match("#delivered to command: procmail -a#",$bounce_error)){
		$status="Deliver";
		$delivery_success="yes";
		$bounce_error="Sent to procmail";
	};

if(preg_match("#550 must be authenticated#",$bounce_error)){
		$status="Error";
		$delivery_success="no";
		$bounce_error="Authentication error";
	};	

if(preg_match("#250 Message.+?accepted by#",$bounce_error)){
		$status="Deliver";
		$delivery_success="yes";
		$bounce_error="Sended";
	};		
	
	
if(preg_match("#Connection timed out#",$bounce_error)){
		$status="Error";
		$delivery_success="no";
		$bounce_error="timed out";
	};	
	
if(preg_match("#connect\s+to.+?Connection refused#",$bounce_error)){
	if(preg_match("#connect to 127\.0\.0\.1\[127\.0\.0\.1\]:2003:#", $bounce_error)){
			$file="/etc/artica-postfix/croned.1/postfix.lmtp.127.0.0.1:2003.refused";
			if(file_time_min($file)>5){
				if($GLOBALS["ZARAFA_INSTALLED"]){
					email_events("Postfix: Zarafa LMTP Error","Postfix\n$buffer\nArtica will trying to start Zarafa","postfix");
					$cmd="{$GLOBALS["NOHUP_PATH"]} /etc/init.d/artica-postfix start zarafa >/dev/null 2>&1 &";
					shell_exec(trim($cmd));
					@unlink($file);
					file_put_contents($file,"#");
					return;	
				}
			}
		}
		$status="Error";
		$delivery_success="no";
		$bounce_error="Connection refused";		
}

if(preg_match("#temporary failure.+?artica-msmtp:\s+recipient address\s+(.+?)\s+not accepted by the server artica-msmtp#",$bounce_error)){
		$status="Error";
		$delivery_success="no";
		$bounce_error="artica-filter error";		
}

if(preg_match("#host.+?said: 550 No such user#",$bounce_error)){
		$status="Error";
		$delivery_success="no";
		$bounce_error="No such user";		
}
		
	if(preg_match("#250 2\.1\.5 Ok#",$bounce_error)){
		$status="Deliver";
		$delivery_success="yes";
		$bounce_error="Sended";
	}
	
	if($bounce_error=="250 OK: data received"){
		$status="Deliver";
		$delivery_success="yes";
		$bounce_error="Sended";		
	}
	
	if($bounce_error=="250 Ok: queued as"){
			$status="Deliver";
			$delivery_success="yes";
			$bounce_error="Sended";		
		}
	if(preg_match("#250\s+ok#",$bounce_error)){
		$status="Deliver";
		$delivery_success="yes";
		$bounce_error="Sended";
	}		
		
	if(preg_match("#504.+?Recipient address rejected#",$bounce_error)){
		$status="Error";
		$delivery_success="no";
		$bounce_error="recipient address rejected";
	}
	
if(preg_match("#Address rejected#",$bounce_error)){
			$status="Error";
			$delivery_success="no";
			$bounce_error="Address rejected";
			}

if(preg_match("#conversation with .+?timed out#",$bounce_error)){
			$status="Error";
			$delivery_success="no";
			$bounce_error="timed out";
			}	

if(preg_match("#connect to\s+(.+?)\[.+?cyrus.+?lmtp\]: Connection refused#",$bounce_error)){
			$status="Error";
			$delivery_success="no";
			$bounce_error="mailbox service error";
			cyrus_generic_error($bounce_error,"Cyrus socket error");	
			}
	
if(preg_match("#host.+?\[(.+?)\]\s+said:.+?<(.+?)>: Recipient address rejected: User unknown in local recipient table#",$bounce_error,$re)){
			$status="Error";
			$delivery_success="no";
			$bounce_error="User unknown";
			$to=$re[2];
			}
			
	if(preg_match("#host.+?said:\s+554.+?<(.+?)>:\s+Recipient address rejected.+?not existing recipient#",$bounce_error,$re)){
			$status="Error";
			$delivery_success="no";
			$bounce_error="not existing recipient";
			$to=$re[2];
			}			
			
			
		if(preg_match("#said:.+?Authentication required#",$bounce_error)){
					$status="Error";
					$delivery_success="no";
					$bounce_error="Authentication required";	
		}
		
		if(preg_match("#temporary failure.+?[0-9]+\s+[0-9\.]+\s+Bad sender address syntax.+?could not send mail#",$bounce_error)){
					$status="Error";
					$delivery_success="no";
					$bounce_error="Bad sender address syntax";	
		}
		
		if(preg_match("#connect.+?Permission denied#",$bounce_error)){
					$status="Error";
					$delivery_success="no";
					$bounce_error="service permissions error";	
		}
		
		if(preg_match("#Command died with status 255:.+?exec\.artica-filter\.php#",$bounce_error)){
					$status="Error";
					$delivery_success="no";
					$bounce_error="artica-filter error";
		}
		
		
		if(preg_match("#host\s+(.+?)\[(.+?)\]\s+said:\s+[0-9]+.+?Recipient address rejected: Access denied#",$bounce_error,$re)){
			$status="Error";
			$delivery_success="no";
			$bounce_error="Access denied";
			$smtp_sender=$re[2];
		}
		
		if(preg_match("#250.+?Ok#i",$bounce_error)){
			$status="Deliver";
			$delivery_success="yes";
			$bounce_error="Sended";
		}
		
		if(preg_match("#Message accepted#i",$bounce_error)){
			$status="Deliver";
			$delivery_success="yes";
			$bounce_error="Sended";	
		}

		if(preg_match("#host.+?said:.+?Domain of sender address.+?does not exist#",$bounce_error)){
			$status="Error";
			$delivery_success="no";
			$bounce_error="Your domain does not exist";
		}
		
		if(preg_match("#connect to .+?No such file or dire#",$bounce_error)){
			$status="Error";
			$delivery_success="no";
			$bounce_error="socket error";
		}
		
		if(preg_match("#lost connection with.+?\[(.+?)\] while receiving#",$bounce_error,$re)){
			$status="Error";
			$delivery_success="no";
			$bounce_error="lost connection";
			$smtp_sender=$re[1];		
		}
		
		if(preg_match("#host.+?\[(.+?)\] said:.+?Recipient address rejected#",$bounce_error,$re)){
			$status="Error";
			$delivery_success="no";
			$bounce_error="Recipient address rejected";
			$smtp_sender=$re[1];		
		}
		
		if(preg_match("#loops back to myself#",$bounce_error,$re)){
			$status="Error";
			$delivery_success="no";
			$bounce_error="loops back to myself";
		}
		
		if(preg_match("#Sender denied#",$bounce_error,$re)){
			$status="Error";
			$delivery_success="no";
			$bounce_error="Sender denied";
		}
		
		if(preg_match("#User unknown#",$bounce_error,$re)){
			$status="Error";
			$delivery_success="no";
			$bounce_error="User unknown";
		}
		
		if(preg_match("#Bounce attack signature verification failed#",$bounce_error,$re)){
			$status="Error";
			$delivery_success="no";
			$bounce_error="Bounce attack";
		}
		
		if(preg_match("#mailbox unavailable#",$bounce_error,$re)){
			$status="Error";
			$delivery_success="no";
			$bounce_error="No mailbox";
		}
		
		if(preg_match("#Message rejected#",$bounce_error,$re)){
			$status="Error";
			$delivery_success="no";
			$bounce_error="Message rejected";			
		}
		
		if(preg_match("#250 2\.0\.0 from MTA#",$bounce_error,$re)){
			$status="Deliver";
			$delivery_success="yes";
			$bounce_error="Sended";			
		}
		
		
		if(preg_match("#421-ts03#",$bounce_error,$re)){
			$status="Error";
			$delivery_success="no";
			$bounce_error="Your are blacklisted";			
		}
		
		if(preg_match("#User does not exist#",$bounce_error,$re)){
			$status="Error";
			$delivery_success="no";
			$bounce_error="No mailbox";			
		}
		
		if(preg_match("#Recipient address rejected#",$bounce_error,$re)){
			$status="Error";
			$delivery_success="no";
			$bounce_error="authentication required";			
		}
		
		
		if($bounce_error=="250 OK"){$bounce_error="Sended";$delivery_success="yes";$status="Deliver";}
		if(preg_match("#lost connection with.+?\[(.+?)\]\s+#",$bounce_error,$re)){$bounce_error="lost connection";$delivery_success="no";$smtp_sender=$re[1];}		
		if(preg_match("#status=bounced \(message size.+?exceeds size limit.+?of server.+?\[(.+?)\]#",$bounce_error,$re)){$bounce_error="size exceed limit";$delivery_success="no";$smtp_sender=$re[1];}
		if(preg_match("#lost connection with.+?\[(.+?)\]\s+#",$bounce_error,$re)){$bounce_error="lost connection";$delivery_success="no";$smtp_sender=$re[1];}

if($delivery_success=="no"){
			if($bounce_error=="User unknown in relay recipient table"){$bounce_error="User unknown";}
			
	    	events("event_finish() line ".__LINE__. " bounce_error=$bounce_error");
	    	if(preg_match("#connect to.+?\[(.+?)lmtp\].+?No such file or directory#",$bounce_error,$ra)){
	    		events("Cyrus error found -> CyrusSocketErrot");
	    		cyrus_socket_error($bounce_error,$ra[1].'/lmtp');
	    		}
	    	if(preg_match("#550\s+User\s+unknown\s+<(.+?)>.+?in reply to RCPT TO command#",$bounce_error,$ra)){mailbox_unknown($bounce_error,$ra[1]);}
	    }
    
	$file="/var/log/artica-postfix/RTM/$postfix_id.msg";
	$ini=new Bs_IniHandler($file);
	if($smtp_sender==null){
		if(preg_match("#from.+?\[([0-9\.]+)?\]#",$buffer,$re)){
			$ini->set("TIME","smtp_sender",$re[1]);
		}
	}else{
		$ini->set("TIME","smtp_sender","$smtp_sender");
	}
	
	if($from<>null){$ini->set("TIME","mailfrom",$from);}
	$ini->set("TIME","mailto","$to");
	$ini->set("TIME","bounce_error","$bounce_error");
	$ini->set("TIME","time_end",date("Y-m-d H:i:s"));
	$ini->set("TIME","delivery_success","$delivery_success");
	
	events("event_finish() [$postfix_id]: $from => $to err=$bounce_error success=$delivery_success");
	
	$ini->saveFile($file);	    
       
	
}

function cyrus_imap_conx($service,$server,$server_ip,$user){
	$date=date('Y-m-d H:i:s');
	events("imap connection $user from ($server_ip)");
	$sql="INSERT INTO mbx_con (`zDate`,`mbx_service`,`client_name`,`client_ip`,`uid`,`imap_server`)
	VALUES('$date','$service','$server','$server_ip','$user','{$_GET["server"]}')";
	$md5=md5($sql);
	@mkdir("/var/log/artica-postfix/IMAP",0750,true);
	$file="/var/log/artica-postfix/IMAP/$md5.sql";
	@file_put_contents($file,$sql);
}


function CyrusSocketErrot(){
	
	
}

function _MonthToInteger($month){
  $zText=$month;	
  $zText=str_replace('JAN', '01',$zText);
  $zText=str_replace('FEB', '02',$zText);
  $zText=str_replace('MAR', '03',$zText);
  $zText=str_replace('APR', '04',$zText);
  $zText=str_replace('MAY', '05',$zText);
  $zText=str_replace('JUN', '06',$zText);
  $zText=str_replace('JUL', '07',$zText);
  $zText=str_replace('AUG', '08',$zText);
  $zText=str_replace('SEP', '09',$zText);
  $zText=str_replace('OCT', '10',$zText);
  $zText=str_replace('NOV', '11',$zText);
  $zText=str_replace('DEC', '12',$zText);
  return $zText;	
}
function email_events($subject,$text,$context){
	$GLOBALS["CLASS_UNIX"]->send_email_events($subject,$text,$context);
	}
	
function interface_events($product,$line){
	$ini=new Bs_IniHandler();
	if(is_file("/usr/share/artica-postfix/ressources/logs/interface.events")){
		$ini->loadFile("/usr/share/artica-postfix/ressources/logs/interface.events");
	}
	$ini->set($product,'error',$line);
	$ini->saveFile("/usr/share/artica-postfix/ressources/logs/interface.events");
	@chmod("/usr/share/artica-postfix/ressources/logs/interface.events",0755);
	
}



function amavis_socket_error($line){
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__;
	events("AMAVIS SOCKET ERROR ! ($line)");
	$ftime=file_time_min($file);
	if($ftime<15){
		events("Unable to process new operation for amavis...waiting 15mn (current {$ftime}mn)");
		return null;
	}
	$unix=new unix();
	$stat=$unix->find_program("stat");
	exec("$stat /var/spool/postfix/var/run/amavisd-new/amavisd-new.sock 2>&1",$STATr);
	
	$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart amavis-milter");
	email_events("Warning Amavis socket is not available",$line." (Postfix claim that amavis socket is not available, 
	Artica will restart amavis \"milter\" service)
	Here it is the stat results:
	------------------------------------------
	file requested :/var/spool/postfix/var/run/amavisd-new/amavisd-new.sock
	".@implode("\n",$STATr)
	,"postfix");
	@unlink($file);
	@mkdir("/etc/artica-postfix/cron.1");
	@file_put_contents($file,"#");	
}

function mailbox_unknown($line,$to){
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__.'.'.md5($to);
	if(file_time_min($file)<15){return null;}
	email_events("Warning unknown mailbox $to","Postfix claim: $to mailbox is not available you should create an alias or mailbox $line","mailbox");
	@unlink($file);
	@file_put_contents($file,"#");	
	
}



 
function amavis_error_restart($buffer){
	events("amavis_error_restart:: $buffer");
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__;
	if(file_time_min($file)<15){
		events("amavis_error_restart:: wait 15mn");
		return null;
	}	
	email_events('Warning Amavis error',"Amavis claim that $buffer, Artica will restart amavis",'postfix');
	$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart amavis");
	@unlink($file);
	file_put_contents($file,"#");	
	}
	
	function clamav_error_restart($buffer){
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__;
	if(file_time_min($file)<15){return null;}	
	email_events('Warning Clamad error',"Postfix claim that $buffer, Artica will restart clamav",'postfix');
	$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart clamd");
	@unlink($file);
	file_put_contents($file,"#");	
	}	
	
function kas_error_update($buffer){
	events("kas_error_update:: $buffer");
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__;
	if(file_time_min($file)<15){return null;}	
	email_events('Kaspersky Anti-spam report failure when updating it`s database',"for your information: $buffer",'postfix');
	$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart kas3");
	@unlink($file);
	file_put_contents($file,"#");	
	}

function cyrus_generic_error($buffer,$subject){
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__;
	if(file_time_min($file)<15){return null;}	
	events("Cyrus error !! $buffer (cache=$file)");
	email_events("cyrus-imapd error: $subject","$buffer, Artica will restart cyrus",'mailbox');
	$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart imap");
	@unlink($file);
	file_put_contents($file,"#");
	
}

function cyrus_socket_error($buffer,$socket){
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__;
	if(file_time_min($file)<15){return null;}	
	email_events("cyrus-imapd socket error: $socket","Postfix claim \"$buffer\", Artica will restart cyrus",'mailbox');
	$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET('/usr/share/artica-postfix/bin/artica-install --cyrus-checkconfig');
	$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart imap');
	@unlink($file);
	@file_put_contents($file,"#");
}

function MilterSpamAssassinError($buffer,$socket){
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__;
	if(file_time_min($file)<15){return null;}	
	email_events("spamassin-milter socket error: $socket","Postfix claim \"$buffer\", Artica will reload Postfix and compile new Postfix settings",'postfix');
	$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET('/usr/share/artica-postfix/bin/artica-install --postfix-reload');
	@unlink($file);
	@file_put_contents($file,"#");	
}


function AmavisConfigErrorInPostfix($buffer){
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__;
	$timeFile=file_time_min($file);
	if($timeFile<15){
		events("*** $buffer ****");
		events("amavisd-new socket no operations, blocked by timefile $timeFile Mn!!!");
		return null;}	
	events("amavisd-new socket error time:$timeFile Mn!!!");
	email_events("amavisd-new socket error","Postfix claim \"$buffer\", Artica will reload Postfix and compile new Postfix settings",'postfix');
	$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("{$GLOBALS["PHP5_BIN"]} ".dirname(__FILE__)."/exec.postfix.maincf.php --reconfigure");
	$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart amavis');
	$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET('/usr/share/artica-postfix/bin/artica-install --postfix-reload');
	@unlink($file);
	@file_put_contents($file,"#");	
	if(!is_file($file)){
		events("error writing time file:$file");
	}
}

function SpamAssassin_error_saupdate($buffer){
$file="/etc/artica-postfix/cron.1/".__FUNCTION__;
	$timeFile=file_time_min($file);
	if($timeFile<15){
		events("*** $buffer ****");
		events("Spamassassin no operations, blocked by timefile $timeFile Mn!!!");
		return null;}	
	events("Spamassassin error time:$timeFile Mn!!!");
	email_events("SpamAssassin error Regex","SpamAssassin claim \"$buffer\", Artica will run /usr/bin/sa-update to fix it",'postfix');
	$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-update --spamassassin --force");
	@unlink($file);
	@file_put_contents($file,"#");	
	if(!is_file($file)){
		events("error writing time file:$file");
	}	
}

function miltergreylist_error($buffer,$socket){
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__;
	if(file_time_min($file)<15){return null;}	
	email_events("Milter Greylist error: $socket","System claim \"$buffer\", Artica will restart milter-greylist",'postfix');
	$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart mgreylist');
	@unlink($file);
	@file_put_contents($file,"#");
}



function MilterClamavError($buffer,$socket){
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__;
	if(file_time_min($file)<15){return null;}	
	email_events("Milter-clamav socket error: $socket","Postfix claim \"$buffer\", 
	Artica will grant postfix to this socket\but you can use amavis instead that will handle clamav antivirus scanner too",'postfix');
	$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("/bin/chmod -R 775 ". dirname($socket));
	$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("/bin/chown -R postfix:postfix ". dirname($socket));
	$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("postqueue -f");
	@unlink($file);
	@file_put_contents($file,"#");	
	
}
function AmavisConfigErrorInPostfixRestart($buffer){
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__;
	if(file_time_min($file)<15){return null;}	
	email_events("Amavis network error: $socket","Postfix claim \"$buffer\", Artica will restart postfix",'postfix');
	
	$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart postfix-single");
	@unlink($file);
	@file_put_contents($file,"#");		
}
function ImBlackListed($server,$buffer){
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__.".".md5($server);
	if(file_time_min($file)<15){return null;}	
	email_events("Your are blacklisted from $server","Postfix claim \"$buffer\", try to investigate why or contact our technical support",'postfix');
	@unlink($file);
	@file_put_contents($file,"#");		
}


function postfix_compile_db($hash_file,$buffer){
	$unix=new unix();
	events("DB Problem -> $hash_file");
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__.".".md5($hash_file);
	if(file_time_min($file)<5){return null;}
	
	if(!is_file($hash_file)){
		@file_put_contents($hash_file,"#");
	}
	$cmd=$unix->find_program("postmap"). " hash:$hash_file 2>&1";
	exec($cmd,$results);
	email_events("Postfix Database problem","Postfix claim \"$buffer\", Artica has recompiled ".basename($hash_file)."\n".@implode("\n",$results),'postfix');
	events("DB Problem -> $hash_file -> $cmd");
	$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET($unix->find_program("postfix"). " reload");		
	@unlink($file);
	@file_put_contents($file,"#");		
	
}

function postfix_compile_missing_db($hash_file,$buffer){
	$unix=new unix();
	events("DB Problem -> $hash_file");
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__.".".md5($hash_file);
	if(file_time_min($file)<5){return null;}
	
	if(!is_file($hash_file)){
		@file_put_contents($hash_file,"#");
	}
	
	email_events("Postfix Database problem","Postfix claim \"$buffer\", Artica will create blanck file and recompile ".basename($hash_file),'postfix');
	$cmd=$unix->find_program("postmap"). " hash:$hash_file";
	$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET($cmd);
	events("DB Problem -> $hash_file -> $cmd");
	$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET($unix->find_program("postfix"). " reload");		
	@unlink($file);
	@file_put_contents($file,"#");		
	
}

function cyrus_bad_login($router,$ip,$user,$error){
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__.".".md5("$router,$ip,$user,$error");
	if(file_time_min($file)<15){return null;}	
	@unlink($file);
	email_events("User $user cannot login to mailbox","cyrus claim \"$error\" for $user (router:$router, ip:$ip),
	 please,send the right password to $user",'mailbox');
	@file_put_contents($file,"#");		
}

function smtp_sasl_failed($router,$ip,$buffer){
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__.".".md5("$router,$ip");
	events("SMTP authentication failed from $router ($ip)"); 
	if(file_time_min($file)<15){return null;}
	@unlink($file);
	email_events("SMTP authentication failed from $router","Postfix claim \"$buffer\" for ip address $ip",'postfix');
	@file_put_contents($file,"#");		
}

function kavmilter_expired($buffer){
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__.".expired";
	if(file_time_min($file)<15){return null;}
	@unlink($file);
	@file_put_contents("/etc/artica-postfix/settings/Daemons/kavmilterEnable","0");
	$cmd=LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.postfix.maincf.php --reconfigure";
	events("$cmd");
	$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET($cmd);
	$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("/etc/init.d/artica-postfix stop kavmilter");
	email_events("Kaspersky For Mail server, license expired","Postfix claim \"$buffer\" Artica will disable Kaspersky and restart postfix",'postfix');
	@file_put_contents($file,"#");
	}

function hackPOP($ip,$logon,$buffer){
	if($GLOBALS["DisableMailBoxesHack"]==1){return;}
	if($GLOBALS["PopHackEnabled"]==0){return;}
	$file="/etc/artica-postfix/croned.1/postfix.hackPop3.error";
	if($ip=="127.0.0.1"){return;}
	$GLOBALS["POP_HACK"][$ip]=intval($GLOBALS["POP_HACK"][$ip])+1;
	$count=intval($GLOBALS["POP_HACK"][$ip]);
	events("POP HACK {$ip} email={$logon} $count/{$GLOBALS["PopHackCount"]} failed");

	if(file_time_min($file)>10){
			email_events("POPHACK {$ip}/{$logon} $count/{$GLOBALS["PopHackCount"]} failed",
			"Mailbox server claim $buffer\nAfter ( $count/{$GLOBALS["PopHackCount"]}) {$GLOBALS["PopHackCount"]} times failed, 
			a firewall rule will added","mailbox");
			@unlink($file);
		}else{
			events("User not found for mailbox {$ip}/{$logon} $count/{$GLOBALS["PopHackCount"]} failed");
		}	
	
	if($count>=$GLOBALS["PopHackCount"]){
		shell_exec("iptables -I INPUT -s {$ip} -j DROP");
		events("POP HACK RULE CREATED {$ip} $count/{$GLOBALS["PopHackCount"]} failed");
		email_events("HACK pop3 from {$ip}","A firewall rule has been created and this IP:{$ip} is now denied ","mailbox");
		unset($GLOBALS["POP_HACK"][$ip]);
	}
	file_put_contents($file,"#");	
}


function zarafa_store_error($buffer){
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__.".store.error";
	if(file_time_min($file)<3600){return null;}
	@unlink($file);
	$cmd=LOCATE_PHP5_BIN()." ".dirname(__FILE__)."/exec.zarafa.build.stores.php";
	events("$cmd");
	$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET($cmd);
	email_events("Zarafa mailbox server store error","Zarafa claim \"$buffer\" Artica will try to reactivate stores and accounts",'mailbox');
	@file_put_contents($file,"#");	
}

function postfix_nosuch_fileor_directory($service,$targetedfile,$buffer){
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__.md5($targetedfile).".postfix.file";
	if(file_time_min($file)<15){return null;}	
	@unlink($file);
	
	$targetedfile=trim($targetedfile);
	if($targetedfile==null){return;}
	if(preg_match("#(.+?)\.db$#",$targetedfile,$re)){
		$unix=new unix();
		$postmap=$unix->find_program("postmap");
		$cmd="/bin/touch {$re[1]}";
		events(__FUNCTION__. " <$cmd>");
		$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET($cmd);
		$cmd="$postmap hash:{$re[1]}";
		events(__FUNCTION__. " <$cmd>");
		$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET($cmd);
		email_events("missing database ". basename($targetedfile),"Service postfix/$service claim \"$buffer\" Artica will create a blank $targetedfile",'smtp');
		$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("postfix reload");
		@file_put_contents($file,"#");	
		return;		
	 }
	

	
	$cmd="/bin/touch $targetedfile";
	events("$cmd");
	$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET($cmd);
	$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("postfix reload");
	email_events("missing ". basename($targetedfile),"Service postfix/$service claim \"$buffer\" Artica will create a blank $targetedfile",'smtp');
	@file_put_contents($file,"#");		
}
function postfix_baddb($service,$targetedfile,$buffer){
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__.md5($targetedfile).".postfix.file";
	if(file_time_min($file)<15){return null;}	
	@unlink($file);
	$targetedfile=trim($targetedfile);
	if($targetedfile==null){return;}	
	$unix=new unix();
	$postmap=$unix->find_program("postmap");
	$cmd="$postmap hash:$targetedfile";
	events(__FUNCTION__. " <$cmd>");
	$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET($cmd);
	email_events("corrupted database ". basename($file),"Service postfix/$service claim \"$buffer\" Artica will rebuild $targetedfile.db",'smtp');
	$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("postfix reload");
	@file_put_contents($file,"#");	
	return;			
}

function multi_instances_reconfigure($buffer){
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__.".postfix.file";
	if(file_time_min($file)<15){return null;}	
	@unlink($file);
	$cmd="{$GLOBALS["PHP5_BIN"]} /usr/share/artica-postfix/exec.postfix-multi.php";
	events(__FUNCTION__. " <$cmd>");
	$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET($cmd);	
	email_events("multi-instances not correctly set","Service postfix claim \"$buffer\" Artica will rebuild multi-instances settings",'smtp');
	@file_put_contents($file,"#");	
	return;		
}

function postfix_bind_error($ip,$port,$buffer){
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__.md5("$ip:$port");
	if(file_time_min($file)<15){
		events("Postfix bind error, time-out");
		return null;
	}	
	@unlink($file);
	$cmd="{$GLOBALS["PHP5_BIN"]} /usr/share/artica-postfix/exec.postfix-multi.php --restart-all";
	events(__FUNCTION__. " <$cmd>");
	$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET($cmd);	
	email_events("Unable to bind $ip:$port","Service postfix claim \"$buffer\" Artica will restart all daemons to fix it",'smtp');
	@file_put_contents($file,"#");	
	return;	
}



function mailbox_corrupted($buffer,$mail){
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__.md5($mail);
	if(file_time_min($file)<15){
		events("mailbox_corrupted <$mail>, time-out");
		return null;
	}	
	@unlink($file);
	email_events("Corrupted mailbox $mail","Service postfix claim \"$buffer\" try to repair the mailbox or to use the command line
	turned out to be corrupted quota files:
	find ~cyrus -type f | grep quota\nremove the quota files for the affected mailbox(es)\nrun
	reconstruct -r -f user/mailboxoftheuser\n\n
	if you cannot perform this operation, you can open a ticket on artica technology company http://www.artica-technology.com' ",'mailbox');
	@file_put_contents($file,"#");	
	return;		
}

function mailbox_overquota($buffer,$mail){
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__.md5($mail);
	if(file_time_min($file)<15){
		events("mailbox_overquota <$mail>, time-out");
		return null;
	}	
	@unlink($file);
	email_events("mailbox $mail Over Quota","Service postfix claim \"$buffer\" try to increase quota for $mail' ",'mailbox');
	@file_put_contents($file,"#");	
	return;		
}

function zarafa_rebuild_db($table,$buffer){
	$file="/etc/artica-postfix/cron.1/".__FUNCTION__;
	if(file_time_min($file)<15){
		events("Zarafa missing table <$table>, time-out");
		return null;
	}	
	@unlink($file);
	email_events("Zarafa missing Mysql table $table","Service Zarafa claim \"$buffer\" artica will destroy the zarafa database in order to let the Zarafa service create a new one' ",'mailbox');
	$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("{$GLOBALS["PHP5_BIN"]} ".dirname(__FILE__)."/exec.mysql.build.php --rebuild-zarafa");
	@file_put_contents($file,"#");	
	return;		
	
}


function smtp_hack_reconfigure(){
	
	if(is_file("/var/log/artica-postfix/smtp-hack-reconfigure")){
		@unlink("/var/log/artica-postfix/smtp-hack-reconfigure");
	}
	
	$sock=new sockets();
	$GLOBALS["SMTP_HACK_CONFIG_RATE"]=unserialize(base64_decode($sock->GET_INFO("PostfixAutoBlockParameters")));
	if($GLOBALS["SMTP_HACK_CONFIG_RATE"]["NAME_SERVICE_NOT_KNOWN"]==null){$GLOBALS["SMTP_HACK_CONFIG_RATE"]["NAME_SERVICE_NOT_KNOWN"]=10;}
	if($GLOBALS["SMTP_HACK_CONFIG_RATE"]["SASL_LOGIN"]==null){$GLOBALS["SMTP_HACK_CONFIG_RATE"]["SASL_LOGIN"]=15;}
	if($GLOBALS["SMTP_HACK_CONFIG_RATE"]["RBL"]==null){$GLOBALS["SMTP_HACK_CONFIG_RATE"]["RBL"]=5;}	
	if($GLOBALS["SMTP_HACK_CONFIG_RATE"]["USER_UNKNOWN"]==null){$GLOBALS["SMTP_HACK_CONFIG_RATE"]["USER_UNKNOWN"]=10;}	
	if($GLOBALS["SMTP_HACK_CONFIG_RATE"]["BLOCKED_SPAM"]==null){$GLOBALS["SMTP_HACK_CONFIG_RATE"]["BLOCKED_SPAM"]=5;}	
	if($GLOBALS["SMTP_HACK_CONFIG_RATE"]["ADDRESS_NOT_LISTED"]==null){$GLOBALS["SMTP_HACK_CONFIG_RATE"]["ADDRESS_NOT_LISTED"]=2;}	
	if($GLOBALS["SMTP_HACK_CONFIG_RATE"]["SMTPHACK_TIMEOUT"]==null){$GLOBALS["SMTP_HACK_CONFIG_RATE"]["SMTPHACK_TIMEOUT"]=10;}
	if($GLOBALS["SMTP_HACK_CONFIG_RATE"]["SMTPHACK_RESOLUTION_FAILURE"]==null){$GLOBALS["SMTP_HACK_CONFIG_RATE"]["SMTPHACK_RESOLUTION_FAILURE"]=2;}
	if($GLOBALS["SMTP_HACK_CONFIG_RATE"]["SMTPHACK_TOO_MANY_ERRORS"]==null){$GLOBALS["SMTP_HACK_CONFIG_RATE"]["SMTPHACK_TOO_MANY_ERRORS"]=10;}
	
	

while (list ($num, $ligne) = each ($GLOBALS["SMTP_HACK_CONFIG_RATE"]) ){
	$info="Starting......: artica-postfix realtime logs SMTP HACK: $num=$ligne";
	events($info);
	echo $info."\n";
}
	
	
}


function smtp_hack_perform($servername,$array,$matches){
	if($servername=="127.0.0.1"){return;}

	$NAME_SERVICE_NOT_KNOWN=$array["NAME_SERVICE_NOT_KNOWN"];
	$SASL_LOGIN=$array["SASL_LOGIN"];
	$USER_UNKNOWN=$array["USER_UNKNOWN"];
	$RBL=$array["RBL"];
	$BLOCKED_SPAM=$array["BLOCKED_SPAM"];
	$ADDRESS_NOT_LISTED=$array["ADDRESS_NOT_LISTED"];
	
	if($NAME_SERVICE_NOT_KNOWN==null){$NAME_SERVICE_NOT_KNOWN=0;}
	if($SASL_LOGIN==null){$SASL_LOGIN=0;}
	if($USER_UNKNOWN==null){$USER_UNKNOWN=0;}
	if($RBL==null){$RBL=0;}
	if($BLOCKED_SPAM==null){$BLOCKED_SPAM=0;}
	if($ADDRESS_NOT_LISTED==null){$ADDRESS_NOT_LISTED=0;}
	
	//$EnablePostfixAutoBlock=$sock->GET_INFO("EnablePostfixAutoBlock");
	
	$text="
	Rule matched: $matches
	--------------------------------------------------------
	NAME_SERVICE_NOT_KNOWN attempts:\t$NAME_SERVICE_NOT_KNOWN
	SASL_LOGIN attempts:\t$SASL_LOGIN
	RBL attempts:\t$RBL
	USER_UNKNOWN attempts:\t$USER_UNKNOWN
	ADDRESS_NOT_LISTED attempts:\t$ADDRESS_NOT_LISTED
	BLOCKED_SPAM attempts:\t$BLOCKED_SPAM";
	
	$md=array(
		"IP"=>$servername,
		"MATCHES"=>$matches,
		"EVENTS"=>$text,
		"DATE"=>date("Y-m-d H:i:s")
	);
	
	$serialize=serialize($md);
	$md5=md5($serialize);
	@mkdir("/var/log/artica-postfix/smtp-hack",0666,true);
	@file_put_contents("/var/log/artica-postfix/smtp-hack/$md5.hack",$serialize);
	events("SMTP Hack: $servername matches $matches $text");
	if(!$GLOBALS["SMTP_HACKS_NOTIFIED"][$servername]){
		$GLOBALS["SMTP_HACKS_NOTIFIED"][$servername]=true;
		email_events("[SMTP HACK]: $servername match rules",$text,'postfix');
	}
}
function events_not_filtered($text){
		$common="/var/log/artica-postfix/postfix-logger.debug";
		$size=@filesize($common);
		$pid=getmypid();
		$date=date("Y-m-d H:i:s");
		$h = @fopen($common, 'a');
		$sline="[$pid] $text";
		$line="$date [$pid] $text\n";
		@fwrite($h,$line);
		@fclose($h);	
	
}

function Postfix_Addconnection($hostname,$ip){
	$time=time();
	$array=array("HOSTNAME"=>$hostname,"IP"=>$ip,"TIME"=>$time);
	$ser=serialize($array);
	@file_put_contents("/var/log/artica-postfix/smtp-connections/". md5($ser).".cnx",$ser);
	
}
function Postfix_Addconnection_error($hostname,$ip,$error_text){
	if($GLOBALS["EnableArticaSMTPStatistics"]==0){return;}
	$time=time();

	if($hostname==null){if($ip<>null){$hostname=gethostbyaddr($ip);}}
	
	if(!preg_match("#[0-9]+\.[0-9]+\.[0-9]+\.#",$ip)){
		$ip=gethostbyname($ip);
		if(!preg_match("#[0-9]+\.[0-9]+\.[0-9]+\.#",$ip)){if($hostname<>null){$ip=gethostbyname($hostname);}}
		if(!preg_match("#[0-9]+\.[0-9]+\.[0-9]+\.#",$ip)){return null;}
	}
	
	$array=array("HOSTNAME"=>$hostname,"IP"=>$ip,"TIME"=>$time,"error"=>$error_text);
	$ser=serialize($array);
	@file_put_contents("/var/log/artica-postfix/smtp-connections/". md5($ser).".err",$ser);
	
}

function amavisd_milter_bin_path(){
	
	$path=$GLOBALS["CLASS_UNIX"]->find_program('amavisd-milter');
	if(is_file($path)){return $path;}
	$path=$GLOBALS["CLASS_UNIX"]->find_program('amavis-milter');
	if(is_file($path)){return $path;}	
}


function postfix_is_amavis_port($portToCheck){
	if(!isset($GLOBALS["AMAVIS_INSTALLED"])){$users=new settings_inc();$GLOBALS["AMAVIS_INSTALLED"]=$users->AMAVIS_INSTALLED;}
	
	
	if(!$GLOBALS["AMAVIS_INSTALLED"]){if($GLOBALS["VERBOSE"]){echo "AMAVIS_INSTALLED -> FALSE\n";return false;}}
	events("Postfix: bind 127.0.0.1 port $portToCheck: -> check Amavis");
	$f=explode("\n",@file_get_contents("/usr/local/etc/amavisd.conf"));
	while (list ($num, $line) = each ($f) ){
			if(preg_match("#inet_socket_port.+?\[(.+?)\]#", $line,$re)){
				$inet_socket_port=$re[1];
				if(strpos($inet_socket_port, ",")){
					$socketstmp=explode(",",$inet_socket_port);while (list ($a, $b) = each ($socketstmp) ){$socket[$b]=true;}
				}else{
					$socket[$inet_socket_port]=true;
				}
			}
		}

	if(!isset($socket)){
		events("Postfix: unable to detect sockets port");
		return false;
	}
	
	if(!isset($socket[$portToCheck])){events("Postfix: $portToCheck no such array...");
		return false;
	}
	
	if($socket[$portToCheck]){
		events("Postfix: $portToCheck is an amavis port");
		return true;
	}
	
	return false;
}

function amavis_sa_update($buffer){
	$unix=new unix();
	$php=$unix->LOCATE_PHP5_BIN();
	$nohup=$unix->find_program("nohup");
	$cmd="$nohup $php /usr/share/artica-postfix/exec.spamassassin.php --sa-update >/dev/null 2>&1 &";
	
	$file="/etc/artica-postfix/pids/".__FUNCTION__.".error.time";
	if(file_time_min($file)<15){events("-> detected $buffer, need to wait 15mn");return null;}	
	@unlink($file);
	@file_put_contents($file,"#");	
	shell_exec(trim($cmd));
	events("$cmd");
	return;			
	
}

 
?>
