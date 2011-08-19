<?php
$GLOBALS["DEBUG_MEM"]=true;
$mem=round(((memory_get_usage()/1024)/1000),2);events("START WITH {$mem}MB ","MAIN",__LINE__);
include_once(dirname(__FILE__).'/framework/frame.class.inc');
include_once(dirname(__FILE__)."/framework/class.unix.inc");
include_once(dirname(__FILE__)."/ressources/class.auth.tail.inc");
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
$mem=round(((memory_get_usage()/1024)/1000),2);events("{$mem}MB before class.users.menus.inc","MAIN",__LINE__);
include_once(dirname(__FILE__)."/framework/class.settings.inc");
$mem=round(((memory_get_usage()/1024)/1000),2);events("{$mem}MB after class.settings.inc","MAIN",__LINE__);



if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
if(!Build_pid_func(__FILE__,"MAIN")){
	events(basename(__FILE__)." Already executed.. aborting the process");
	die();
}


$pid=getmypid();
$pidfile="/etc/artica-postfix/".basename(__FILE__).".pid";
@mkdir("/var/log/artica-postfix/xapian",0755,true);
@mkdir("/var/log/artica-postfix/infected-queue",0755,true);
@mkdir("/var/log/artica-postfix/snort-queue",0755,true);
@mkdir("/var/log/artica-postfix/pdns-hack-queue",0755,true);
events("running $pid ");
file_put_contents($pidfile,$pid);
$sock=new sockets();
$users=new settings_inc();
$unix=new unix();
$GLOBALS["RSYNC_RECEIVE"]=array();
$GLOBALS["LOCATE_PHP5_BIN"]=$unix->LOCATE_PHP5_BIN();
$GLOBALS["SID"]="";
$squidbin=$unix->find_program("squid3");
if(!is_file($squidbin)){$squidbin=$unix->find_program("squid");}
$GLOBALS["SQUIDBIN"]=$squidbin;
$GLOBALS["nohup"]=$unix->find_program("nohup");
					
$GLOBALS["ROUNDCUBE_HACK"]=0;
$GLOBALS["PDNS_HACK"]=$sock->GET_INFO("EnablePDNSHack");
$GLOBALS["PDNS_HACK_MAX"]=$sock->GET_INFO("PDNSHackMaxEvents");
if(!is_numeric($GLOBALS["PDNS_HACK_MAX"])){$GLOBALS["PDNS_HACK_MAX"]=3;}
if(!is_numeric($GLOBALS["PDNS_HACK"])){$GLOBALS["PDNS_HACK"]=1;}
$GLOBALS["PDNS_HACK_DB"]=array();

$unix=new unix();
$GLOBALS["NODRYREBOOT"]=$sock->GET_INFO("NoDryReboot");
$GLOBALS["NOOUTOFMEMORYREBOOT"]=$sock->GET_INFO("NoOutOfMemoryReboot");
$GLOBALS["CLASS_SOCKET"]=$sock;
$GLOBALS["CLASS_UNIX"]=$unix;
$sock=null;
$unix=null;

$mem=round(((memory_get_usage()/1024)/1000),2);events("{$mem}MB before forking","MAIN",__LINE__);

$_GET["server"]=$users->hostname;
$pipe = fopen("php://stdin", "r");
while(!feof($pipe)){
$buffer .= fgets($pipe, 4096);
try{ Parseline($buffer);}catch (Exception $e) {events("fatal error:".  $e->getMessage());}

$buffer=null;
}
fclose($pipe);
events("Shutdown...");
die();
function Parseline($buffer){
$buffer=trim($buffer);	
if(strpos($buffer,"]: SA dbg:")>0){return;} 
if(strpos($buffer,") SA dbg:")>0){return;} 
if(preg_match("#exec.dstat.top.php#",$buffer)){return true;}
if(preg_match("#artica-filter#",$buffer)){return true;}
if(preg_match("#postfix\/#",$buffer)){return true;}
if(preg_match("#CRON\[#",$buffer)){return true;}
if(preg_match("#: CACHEMGR:#",$buffer)){return true;}
if(preg_match("#exec\.postfix-logger\.php:#",$buffer)){return true;}
if(preg_match("#artica-install\[#",$buffer)){return true;}
if(preg_match("#monitor action done#",$buffer)){return true;}
if(preg_match("#monitor service.+?on user request#",$buffer)){return true;}
if(preg_match("#CRON\[.+?\(root\).+CMD#",$buffer)){return true;}
if(preg_match("#winbindd\[.+?winbindd_listen_fde_handler#",$buffer)){return true;}
if(preg_match("#slapd.+?conn=[0-9]+\s+fd=.+?closed#",$buffer)){return;}
if(strpos($buffer,"*system*awstats#")>0){return true;}
if(strpos($buffer,"extra modules loaded after daemonizing/chrooting")>0){return;}
if(strpos($buffer,"/etc/cron.d/awstats")>0){return;}
if(strpos($buffer,"emailrelay:")>0){return;}
if(strpos($buffer,"pptpd-logwtmp.so loaded")>0){return;}
if(strpos($buffer,"Reinitializing monit daemon")>0){return;}
if(strpos($buffer,"Monit reloaded")>0){return;}
if(strpos($buffer,"Tarticaldap.logon")>0){return;}
if(strpos($buffer,"pulseaudio[")>0){return;}
if(strpos($buffer,"exec: /usr/bin/php5")>0){return;}
if(strpos($buffer,"Found decoder for ")>0){return;}
if(strpos($buffer,"Internal decoder for ")>0){return;}
if(strpos($buffer,"Loaded Icons")>0){return;}
if(strpos($buffer,"CP ConfReq")>0){return;}
if(strpos($buffer,"CP ConfAck")>0){return;}
if(strpos($buffer,"CP EchoReq")>0){return;}
if(strpos($buffer,"/usr/sbin/cron")>0){return;}
if(strpos($buffer,"no IPv6 routers present")>0){return;}
if(strpos($buffer,"AM.PDP-SOCK")>0){return;}
if(strpos($buffer,"disconnect from unknown")>0){return;}

//LDAP Dustbin
if(strpos($buffer,"SEARCH RESULT tag=")>0){return;}
if(strpos($buffer,'SRCH base="cn=')>0){return;}
if(strpos($buffer,'ACCEPT from IP=')>0){return;}
if(strpos($buffer,'closed (connection lost)')>0){return;}

//automount dustbin
if(strpos($buffer,"handle_packet: type")>0){return;}
if(strpos($buffer,"dev_ioctl_send_fail: token")>0){return;}
if(strpos($buffer,"lookup_mount: lookup(ldap)")>0){return;}
if(strpos($buffer,"handle_packet_missing_indirect: token")>0){return;}
if(strpos($buffer,"getuser_func: called with context")>0){return;}
if(strpos($buffer,"attempting to mount entry /automounts")>0){return;}
if(strpos($buffer,"lookup_one: lookup(ldap)")>0){return;}
if(strpos($buffer,"do_bind: lookup(ldap):")>0){return;}
if(strpos($buffer,"sun_mount: parse")>0){return;}
if(strpos($buffer,"]: failed to mount /")>0){return;}
if(strpos($buffer,"]: do_mount:")>0){return;}
if(strpos($buffer,"]: parse_mount: parse")>0){return;}
if(strpos($buffer,"mount_mount: mount(generic):")>0){return;}
if(strpos($buffer,">> Error connecting to")>0){return;}
if(strpos($buffer,">> Refer to the mount")>0){return;}
if(strpos($buffer,"getpass_func: context (nil)")>0){return;}

//ROOT Dustbin
if(strpos($buffer,"(root) CMD")>0){return;}
if(strpos($buffer,"RELOAD (/etc/cron")>0){return;}
//Cyrus DUSTBIN
if(strpos($buffer,"cyrus PLAIN User logged in")>0){return;}
if(strpos($buffer,"cyrus/ctl_cyrusdb")>0){return;}
if(strpos($buffer,"exited, status 0")>0){return;}
if(strpos($buffer,"fetching user_deny")>0){return;}
if(strpos($buffer,"seen_db: user")>0){return;}
if(strpos($buffer,"mystore: starting txn")>0){return;}
if(strpos($buffer,"mystore: committing")>0){return;}
if(strpos($buffer,"duplicate_mark:")>0){return;}
if(strpos($buffer,"root-servers.net:")>0){return;}
if(strpos($buffer,"KASINFO")>0){return;}

//pdns dustbin
if(strpos($buffer,"question for '")>0){return;}
if(strpos($buffer,"answer to question '")>0){return;}
if(strpos($buffer,"failed (res=3)")>0){return;}
if(preg_match("#pdns_recursor\[[0-9]+\]: \[[0-9]+\]\s+#", $buffer)){return;}

//roundcube dustbin
if(strpos($buffer,"IMAP Error: Empty password")>0){return;}


//monit dustbin
if(strpos($buffer,"Monit has not changed")>0){return;}
if(strpos($buffer,": synchronized to ")>0){return;}
if(strpos($buffer,"monit HTTP server stopped")>0){return;}
if(strpos($buffer,"Shutting down monit HTTP server")>0){return;}
if(strpos($buffer,"Starting monit HTTP server at")>0){return;}
if(strpos($buffer,"Reinitializing monit - Control")>0){return;}
//squid dustbin:

if(strpos($buffer,"Unlinkd pipe opened on FD")>0){return;}
if(strpos($buffer,"Beginning Validation Procedure")>0){return;}

//EMAILRELAY DUSTBIN
if(strpos($buffer,"emailrelay: info: failing file")>0){return;}
if(strpos($buffer,"emailrelay: info: no more messages to send")>0){return; }
if(strpos($buffer,"emailrelay: warning: cannot do tls")>0){return; }
if(strpos($buffer,"]: monit daemon at")>0){return;}
if(strpos($buffer,"artica-ldap[")>0){return;}
 
//SAMBA DUSTBIN
if(strpos($buffer,"winbindd/winbindd_group.c")>0){return;}
if(strpos($buffer,"smb_register_idmap_alloc")>0){return;}
if(strpos($buffer,"Idmap module passdb already registered")>0){return;}
if(strpos($buffer,"Cleaning up brl and lock database after unclean shutdown")>0){return;}
if(strpos($buffer,"winbindd_sig_term_handler")>0){return;}
if(strpos($buffer,"wins_registration_timeout")>0){return;}
if(strpos($buffer,":   netbios connect:")>0){return;}
if(strpos($buffer,"cleanup_timeout_fn")>0){return;}
if(strpos($buffer,"struct wbint_Gid2Sid")>0){return;}
if(strpos($buffer,":   doing parameter")>0){return;}
if(strpos($buffer,"param/loadparm.c")>0){return;}
if(strpos($buffer,":   wins_registration_timeout:")>0){return;}
if(strpos($buffer,"src: struct server_id")>0){return;}
if(strpos($buffer,"dest: struct server_id")>0){return;}
if(strpos($buffer,"messages: struct messaging_rec")>0){return;}
if(strpos($buffer,"ndr/ndr.c")>0){return;}
if(strpos($buffer,"smbd/reply.c")>0){return;}
if(strpos($buffer,"lib/smbldap.c")>0){return;}
if(strpos($buffer,"srvsvc_NetShare")>0){return;}
if(strpos($buffer,"]:   Global parameter")>0){return;}
if(strpos($buffer,"STYPE_IPC_HIDDEN")>0){return;}
if(strpos($buffer,"STYPE_DISKTREE")>0){return;}
if(strpos($buffer,": NTLMSSP_")>0){return;}
if(strpos($buffer,"MSG_SMB_UNLOCK")>0){return;}
if(strpos($buffer,":           messages: ARRAY(")>0){return;}
if(strpos($buffer,"struct messaging_array")>0){return;}
if(strpos($buffer,":                   msg_version              :")>0){return;}
if(strpos($buffer,":           num_messages             :")>0){return;}
if(strpos($buffer,":                   sid                      :")>0){return;}
if(strpos($buffer,":               sid                      :")>0){return;}
if(strpos($buffer,":                       id                       :")>0){return;}
if(strpos($buffer,":               dom_name                 :")>0){return;}
if(strpos($buffer,":                   msg_version              :")>0){return;}
if(strpos($buffer,":                   buf                      :")>0){return;}
if(strpos($buffer,":               result                   :")>0){return;}
if(strpos($buffer,":               gid                      :")>0){return;}
if(strpos($buffer,"server_unc")>0){return;}
if(strpos($buffer,"union ntlmssp_AvValue")>0){return;}
if(strpos($buffer,"MsvAvNbDomainName")>0){return;}
if(strpos($buffer,"NegotiateFlags")>0){return;}
if(strpos($buffer,"AvDnsComputerName")>0){return;}
if(strpos($buffer,"Version: struct VERSION")>0){return;}
if(strpos($buffer,"array: ARRAY(")>0){return;}
if(strpos($buffer,"info_ctr")>0){return;}
if(strpos($buffer,"init_sam_from_ldap: Entry found")>0){return;}
//Snort dustbin

//pdns_recursor[23651]: stats: 600 questions, 665 cache entries, 29 negative entries, 0% cache hits"
// check_ntlm_password:  Authentication for user [root] -> [root] FAILED with error NT_STATUS_WRONG_PASSWORD
if(strpos($buffer,"]: last message repeated")>0){return;}


//pdns dustbin
if(strpos($buffer,"Looking for CNAME")>0){return;}
if(strpos($buffer,"No CNAME cache hit of")>0){return;}
if(strpos($buffer,"Found cache hit")>0){return;}
if(strpos($buffer,": Resolved '")>0){return;}
if(strpos($buffer,": Trying IP")>0){return;}
if(strpos($buffer,".: Got 1 answers")>0){return;}
if(strpos($buffer,": accept answer")>0){return;}
if(strpos($buffer,": determining status")>0){return;}
if(strpos($buffer,": got negative caching")>0){return;}
if(strpos($buffer,": No cache hit for")>0){return;}
if(strpos($buffer,": Checking if we have NS")>0){return;}
if(strpos($buffer,": no valid/useful NS")>0){return;}
if(strpos($buffer,": NS (with ip, or non-glue)")>0){return;}
if(strpos($buffer,": We have NS in cache")>0){return;}
if(strpos($buffer,".: Nameservers:")>0){return;}
if(strpos($buffer,": Trying to resolve NS")>0){return;}
if(strpos($buffer,".: got NS record")>0){return;}
if(strpos($buffer,".: status=")>0){return;}
if(strpos($buffer,".: Starting additional")>0){return;}
if(strpos($buffer,".: Done with additional")>0){return;}
if(strpos($buffer,".: Found cache CNAME hit")>0){return;}
if(strpos($buffer,".: answer is in")>0){return;}
if(strpos($buffer,"is negatively cached via")>0){return;}
if(strpos($buffer,".: within bailiwick")>0){return;}
if(strpos($buffer,"]: Query: '")>0){return;}
if(strpos($buffer,"bdb_equality_candidates:")>0){return;}
if(strpos($buffer,"Cache consultations done")>0){return;}
if(strpos($buffer,".: Entire record")>0){return;}
if(strpos($buffer,"got upwards/level NS record")>0){return;}
if(strpos($buffer,"), rcode=0, in")>0){return;}
if(strpos($buffer,"]    ns1.")>0){return;}
if(strpos($buffer,"error resolving, possible error: Connection refused")>0){return;}
if(strpos($buffer,"Failed to resolve via any of the")>0){return;}
if(strpos($buffer,"failed (res=-1)")>0){return;}
if(strpos($buffer,"question answered from packet cache from")>0){return;}
if(strpos($buffer,": timeout resolving")>0){return;}
if(strpos($buffer,": query throttled")>0){return;}

if(strpos($buffer,'BIND dn="cn=')>0){return;}
if(strpos($buffer,'RESULT tag=')>0){return;}
if(strpos($buffer,'SRCH base="')>0){return;}
if(strpos($buffer,'SRCH attr=')>0){return;}
if(strpos($buffer,'MOD attr=')>0){return;}
if(strpos($buffer,'MOD dn=')>0){return;}
if(strpos($buffer,' UNBIND')>0){return;}
if(strpos($buffer,": connection_input: conn=")>0){return;}
if(strpos($buffer,"attr=dNSTTL aRecord nSRecord cNAMERecord")>0){return;}
if(strpos($buffer,": monit HTTP server started")>0){return;}
if(strpos($buffer,"Awakened by the")>0){return;}


if(dhcpd($buffer)){return;}
if(preg_match("#squid\[[0-9]+\]:#",$buffer)){squid_parser($buffer);return;}
if(preg_match("#nss_wins\[[0-9]+\]:#",$buffer)){nss_parser($buffer);return;}

	$auth=new auth_tail();
	if($auth->ParseLog($buffer)){return;}
	$auth=null;
	
$artica_status_pointer="/etc/artica-postfix/pids/exec.status.php.pointer";
if(IfFileTime($artica_status_pointer,3)){
	events("--> artica-status --all");
	shell_exec(trim("{$GLOBALS["nohup"]} {$GLOBALS["LOCATE_PHP5_BIN"]} /usr/share/artica-postfix/exec.status --all >/dev/null 2>&1 &"));
	WriteFileCache($artica_status_pointer);
}
	
	
if(preg_match("#dnsmasq.+? failed to read\s+(.+?):\s+Permission denied#",$buffer,$re)){
	if(!isset($GLOBALS["aa-complain"])){$GLOBALS["aa-complain"]=$GLOBALS["CLASS_UNIX"]->find_program("aa-complain");}
	if(!isset($GLOBALS["dnsmasq_bin"])){$GLOBALS["dnsmasq_bin"]=$GLOBALS["CLASS_UNIX"]->find_program("dnsmasq");}
	$targetedfile=$re[1];
	$file="/etc/artica-postfix/croned.1/dnsmasq.". md5($targetedfile).".Permission.denied";
	events("dnsmasq $targetedfile -> Permission denied");
	if(IfFileTime($file,10)){
			events("dnsmasq $targetedfile -> chmod 755");
			if(is_file($GLOBALS["aa-complain"])){
				events("dnsmasq {$GLOBALS["aa-complain"]}  -> {$GLOBALS["dnsmasq_bin"]}");
				shell_exec("{$GLOBALS["aa-complain"]} {$GLOBALS["dnsmasq_bin"]}");
			}
			email_events("dnsmasq: Permission denied on $targetedfile","dnmasq claims:\n$buffer\nArtica will change permission of this file to 0755 in order to fix this issue and put it into aa-complain mode",'system');
			shell_exec("/bin/chmod 755 \"$targetedfile\"");
			shell_exec(trim("{$GLOBALS["nohup"]} /etc/init.d/artica-postfix restart dnsmasq >/dev/null 2>&1 &"));
			@unlink($file);	
			WriteFileCache($file);
		}
		return;
	}	


	
if(preg_match("#pam_ldap: error trying to bind \(Invalid credentials\)#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/pam_ldap.Invalid.credentials";
	if(IfFileTime($file,10)){
			email_events("pam_ldap: system unable to contact the LDAP server","system claims:\n$buffer\nArtica will reconfigure nss-ldap system\nSome systems request rebooting\nto be sure, reboot your server",'system');
			shell_exec(trim("{$GLOBALS["nohup"]} /usr/share/artica-postfix/bin/artica-install --nsswitch >/dev/null 2>&1 &"));
			@unlink($file);	
			WriteFileCache($file);
		}
		return;
	}	



if(preg_match("#net:\s+failed to bind to server.+?Error: Invalid credentials#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/net.Invalid.credentials";
	if(IfFileTime($file,10)){
			email_events("Samba/net: system unable to contact the LDAP server","Samba/net claims:\n$buffer\nArtica will reconfigure samba system\n",'system');
			shell_exec(trim("{$GLOBALS["nohup"]} {$GLOBALS["LOCATE_PHP5_BIN"]} /usr/share/artica-postfix/exec.samba.php --build >/dev/null 2>&1 &"));
			@unlink($file);	
			WriteFileCache($file);
		}
		return;
	}	


	
	if(preg_match("#pdns.+?:\s+\[LdapBackend\] Unable to search LDAP directory: Starting LDAP search: Can't contact LDAP server#",$buffer,$re)){
		events("--> PDNS LDAP FAILED");
		$file="/etc/artica-postfix/croned.1/pdns.Can.t.contact.LDAP.server";
		if(IfFileTime($file,10)){
			email_events("PowerDNS: DNS server is unable to contact the LDAP server","PDNS claims:\n$buffer\nArtica will restart PowerDNS service",'system');
			shell_exec(trim("{$GLOBALS["nohup"]} /etc/init.d/artica-postfix restart pdns >/dev/null 2>&1 &"));
			@unlink($file);	
			WriteFileCache($file);
		}
		return;
	}	
	
	if(preg_match("#pdns(?:\[\d{1,5}\])?: Not authoritative for '.*',.*sending servfail to\s+(.+?)\s+\(recursion was desired\)#",$buffer,$re)){
		events("--> PDNS Hack {$re[2]}");
		if($GLOBALS["PDNS_HACK"]==1){
			$GLOBALS["PDNS_HACK_DB"][$re[2]]=$GLOBALS["PDNS_HACK_DB"][$re[2]]+1;
			if($GLOBALS["PDNS_HACK_DB"][$re[2]]>$GLOBALS["PDNS_HACK_MAX"]){
				events("--> PDNS Hack {$re[2]} will be banned");
				@file_put_contents("/var/log/artica-postfix/pdns-hack-queue/".time(), $re[2]);
				unset($GLOBALS["PDNS_HACK_DB"][$re[2]]);
			}
		}
		return;
	}	
	
	
	if(preg_match("#auditd\[.+?Unable to set audit pid, exiting#", $buffer)){
		$file="/etc/artica-postfix/croned.1/Unable.to.set.audit.pid";
		if(IfFileTime($file,10)){
			email_events("Auditd: cannot start","auditd claims:\n$buffer\nIt seems that Auditd cannot start, if you run this computer on an OpenVZ VPS server, be sure that your Administrator has enabled audtid capability
			Take a look here http://bugzilla.openvz.org/show_bug.cgi?id=1157
			\nthis notification is not a good information.\nthe Auditd feature is now disabled\n",'system');
			@unlink($file);
			@file_put_contents("/etc/artica-postfix/settings/Daemons/EnableAuditd", "0");
			shell_exec(trim("{$GLOBALS["nohup"]} /etc/init.d/artica-postfix stop auditd >/dev/null 2>&1 &"));
			WriteFileCache($file);
		return;	
		}	
	}
	
	
	if(preg_match("#snort\[[0-9]+\]:\s+\[.+?\]\s+(.+?)\s+\[Classification: (.+?)\]\s+\[Priority:\s+([0-9]+)\]:\/s+\{(.+?)\}\s+(.+?):([0-9]+)\s+->\s+(.+?):([0-9]+)#",$buffer,$re)){
		$md5=md5($buffer);
		$filename="/var/log/artica-postfix/snort-queue/".time().".$md5.snort";
		@file_put_contents($filename,serialize($re));
		return;
	}
	
	
	if(preg_match("#snort\[.+?:\s+Can.+?t acquire.+?cooked-mode frame doesn.+?t have room for sll header#",$buffer,$re)){
		$file="/etc/artica-postfix/croned.1/snort.cant.bind";
		if(IfFileTime($file,10)){
			email_events("SNORT: Fatal error: could not acquire the network","snort claims:\n$buffer\nIt seems that snort is unable to hook your Interface Card, perhaps your server running in a Xen environnement or any virtual system\nthis notification is not a good information.\nYou should remove the IDS feature from Artica or remove SNORT package\nYour system cannot support IDS system.\nsee http://seclists.org/snort/2011/q2/52\nhttp://support.citrix.com/article/CTX116204",'system');
			@unlink($file);
			WriteFileCache($file);
		return;	
		}	
	}
	

	if(preg_match("#.+?roundcube-(.+?): FAILED login for (.+?) from ([0-9\.]+)#",$buffer,$re)){
		Roundcubehack($re[1],$re[2],$re[3]);
		return;
	}
	
	if(preg_match("#net:\s+failed to bind to server ldap.+?localhost#",$buffer)){
		events("--> exec.samba.php --fix-etc-hosts");
		shell_exec("{$GLOBALS["nohup"]} {$GLOBALS["LOCATE_PHP5_BIN"]} /usr/share/artica-postfix/exec.samba.php --fix-etc-hosts >/dev/null 2>&1 &");
		$file="/etc/artica-postfix/croned.1/net-ldap-bind";
		if(IfFileTime($file,5)){
			shell_exec("{$GLOBALS["nohup"]} /etc/init.d/artica-postfix start ldap >/dev/null 2>&1 &");
			WriteFileCache($file);
		return;	
		}	
	}
	
	
	if(preg_match("#(winbindd|smbd)\[.+?failed to bind to server.+?Invalid credentials#",$buffer)){
		events("SAMBA: Invalid credentials");
		
		$file="/etc/artica-postfix/croned.1/samba-ldap-credentials";
		if(IfFileTime($file,5)){
			email_events("Samba: could not connect to ldap Invalid credentials","samba claims:\n$buffer\nArtica will try to reconfigure password and restart Samba",'system');
			shell_exec("{$GLOBALS["nohup"]} {$GLOBALS["LOCATE_PHP5_BIN"]} /usr/share/artica-postfix/exec.samba.php --fix-etc-hosts >/dev/null 2>&1 &");
			@unlink($file);
			shell_exec("{$GLOBALS["nohup"]} /etc/init.d/artica-postfix restart samba >/dev/null 2>&1 &");
			WriteFileCache($file);
		return;	
		}	
	}
	
	if(preg_match("#winbindd\[.+?ADS uninitialized: No logon servers#",$buffer)){
		events("WINBINDD: ADS uninitialized: No logon servers");
		$file="/etc/artica-postfix/croned.1/winbindd-No-logon-servers";
		if(IfFileTime($file,5)){
				$wbinfo=$GLOBALS["CLASS_UNIX"]->find_program("wbinfo");
				exec("$wbinfo -u 2>&1",$results);
				$restart=false;
				while (list ($num, $ligne) = each ($results) ){events("WINBINDD: $ligne");if(preg_match("#Error#",$ligne)){$restart=true;}}
				WriteFileCache($file);

				if($restart){
					$imploded=@implode("\n",$results);
					events("WINBINDD: $imploded");
					email_events("Samba: Winbindd failed to uninitialize with Active Directory server",
					"samba claims:\n$buffer\nArtica will restart winbindd daemon\nwbinfo results:\n$imploded",'system');
					shell_exec(trim("{$GLOBALS["nohup"]} /etc/init.d/artica-postfix restart winbindd >/dev/null 2>&1"));
					@unlink($file);
					WriteFileCache($file);
				}
			}else{
				events("WINBINDD: Do nothing");
			}
		return;	
	}		
		
		

	
	if(preg_match("#lessfs\[.+?send_backlog : failed to connect to the slave#",$buffer,$re)){
		$file="/etc/artica-postfix/croned.1/lessfs.1";
		if(IfFileTime($file,5)){
			email_events("lessFS: Replication deduplication to connect to the slave ","lessFS claims:\n$buffer\nPlease check communications with the slave",'system');
			WriteFileCache($file);
		return;	
		}	
	}
	
	if(preg_match("#lessfs\[.+?send_backlog : invalid message size#",$buffer,$re)){
		$file="/etc/artica-postfix/croned.1/lessfs.2";
		if(IfFileTime($file,5)){
			email_events("lessFS: Replication deduplication failed to replicate ","lessFS claims:\n$buffer\nPlease check communications with the slave",'system');
			WriteFileCache($file);
		return;	
		}	
	}
	
	if(preg_match("#lessfs\[.+?replication_worker : replication is disabled, disconnect#",$buffer,$re)){
		$file="/etc/artica-postfix/croned.1/lessfs.2";
		if(IfFileTime($file,5)){
			email_events("lessFS: Replication deduplication failed: Slave is disabled ","lessFS claims:\n$buffer\nPlease check communications with the slave",'system');
			WriteFileCache($file);
		return;	
		}	
	}	
	
	if(preg_match("#lessfs\[.+?Could not recover database : (.+?)#",$buffer,$re)){
		$file="/etc/artica-postfix/croned.1/lessfs.3";
		if(IfFileTime($file,5)){
			email_events("lessFS: database {$re[1]} corrupted !!","lessFS claims:\n$buffer\nArtica will try to repair it...",'system');
			shell_exec("lessfsck -o -f -t -c /etc/lessfs.cfg &");
		}
		
	}
	
	if(preg_match("#automount\[.+?mount.+?unknown filesystem type.+?ext4#",$buffer,$re)){
		$file="/etc/artica-postfix/croned.1/automount.unknown.filesystem.type.ext4";
		if(IfFileTime($file,15)){
			email_events("automount: Failed to mount EXT4 !","automount claims:\n$buffer\nYou should upgrade your system in order to obtain the last kernel that enables ext4",'system');
			WriteFileCache($file);
		}
		return;
	}	
	
	if(preg_match("#automount\[.+?mount.+?failed to mount\s+(.+?)\s+on\s+(.+)$#",$buffer,$re)){
		$mount_dir=$re[1];
		$mount_dest=$re[2];
		$md5=md5("$mount_dir$mount_dest");
		$file="/etc/artica-postfix/croned.1/automount.$md5";
		if(IfFileTime($file,15)){
			email_events("automount: Failed to mount $mount_dir ","automount claims:\n$buffer\nCheck your connexions settings on automount section",'system');
			WriteFileCache($file);
		}
		return;
	}	
	
	

	if(preg_match("#modprobe: WARNING: Error inserting\s+(.+?)\s+\(.+?\):\s+No such device#",$buffer,$re)){
		email_events("kernel: missing {$re[1]} module","modprobe claims:\n$buffer\nTry to find the right package that store {$re[2]} file",'VPN');
		return;
	}
	
	
	if(preg_match("#pptp_callmgr.+?Could not open control connection to\s+([0-9\.]+)#",$buffer,$re)){
		vpn_msql_events("VPN connexion failed to {$re[1]}, unable to create connection tunnel",$buffer,"{$re[1]}");
		email_events("VPN connexion failed to {$re[1]}, unable to create connection tunnel ","$buffer",'VPN');
		return;
	}
	
	
	if(preg_match("#pppd\[.+?Can.+?t open options file.+?ppp\/peers\/(.+?):\s+No such file or directory#",$buffer,$re)){
		email_events("VPN connexion failed for {$re[1]} connection,No such file","pptp clients claims $buffer\artica will try to rebuild connections","VPN");
		vpn_msql_events("VPN (PPTPD) failed for {$re[1]} connection,No such file",$buffer,"{vpn_server}");
		shell_exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.pptpd.php --clients &");
		return;
	}
	
	 if(preg_match("#pppd\[.+?peer refused to authenticate: terminating link#",$buffer,$re)){
		vpn_msql_events("VPN (PPTPD) authentification failed from remote host",$buffer,"{vpn_server}");
		return;
	}
	
	 if(preg_match("#pppd\[.+?peer refused to authenticate#",$buffer,$re)){
		vpn_msql_events("VPN (PPTPD) failed peer refused to authenticate",$buffer,"{vpn_server}");
		return;
	}	
	
	if(preg_match("#pppd\[.+?MS-CHAP authentication failed: E=691 Authentication failure#",$buffer,$re)){
		vpn_msql_events("VPN (CLIENT) failed server refused to authenticate (Authentication failure)",$buffer,"{vpn_server}");
		return;
	}	
	
	if(preg_match("#pppd\[.+?MPPE required but not available#",$buffer,$re)){
		vpn_msql_events("VPN (PPTPD) authentification failed MPPE required",$buffer,"{vpn_server}");
		return;
	}
	
	
	
	if(preg_match("#pptpd\[.+?CTRL: Client\s+(.+?)\s+control connection finished#",$buffer,$re)){
		vpn_msql_events("VPN (PPTPD) connection closed for {$re[1]}",$buffer,"{vpn_server}");
		return;
	}
	
	if(preg_match("#pppd\[.+?pptpd-logwtmp\.so ip-up ppp[0-9]+\s+(.+?)\s+([0-9\.]+)#",$buffer,$re)){
		vpn_msql_events("VPN (PPTPD) connection open for {$re[1]} ({$re[2]})","$buffer",'{vpn_server}');
		return;
	}
	
	if(preg_match("#slapd\[(.+?)\]:.+?OpenLDAP: slapd\s+([0-9\.]+)#",$buffer,$re)){
		email_events("OpenLDAP service version {$re[2]} successfully started PID {$re[1]}","$buffer",'system');
		return;
	}
	


if(preg_match("#monit\[.+?Sendmail error:\s+(.+)#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/monit-sendmail-failed-". md5($re[1]);
	if(IfFileTime($file,10)){
		events("MONIT -> SENDMAIL FAILED");
		email_events("Monit is unable to send notifications","Monit claim \"$buffer\"\ntry to analyze why postfix send this error:\n{$re[1]}",'system');
		WriteFileCache($file);
		return;	
	}
}


if(strpos($buffer,"pam_ldap: ldap_simple_bind Can't contact LDAP server")>0){
	$file="/etc/artica-postfix/croned.1/ldap-failed";
	if(IfFileTime($file,10)){
		events("pam_ldap -> LDAP FAILED");
		email_events("LDAP server is unavailable","System claim \"$buffer\" artica will try to restart LDAP server ",'system');
		WriteFileCache($file);
		shell_exec('/etc/init.d/artica-postfix restart ldap --monit &');
		return;	
	}	
}

if(preg_match("#net:\s+failed to bind to server.+?Error:\s+Can.?t\s+contact LDAP server#",$buffer)){
	$file="/etc/artica-postfix/croned.1/ldap-failed";
	if(IfFileTime($file,10)){
		events("NET -> LDAP FAILED");
		email_events("LDAP server is unavailable","System claim \"$buffer\" artica will try to restart LDAP server ",'system');
		WriteFileCache($file);
		shell_exec('/etc/init.d/artica-postfix restart ldap --monit &');
		return;	
	}	
}

if(preg_match("#winbindd\[.+?failed to bind to server\s+(.+?)\s+with dn.+?Error: Can.+?contact LDAP server#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/ldap-failed";
	if(IfFileTime($file,10)){
		events("winbindd -> LDAP FAILED");
		email_events("LDAP server is unavailable","Samba claim \"$buffer\" artica will try to restart LDAP server ",'system');
		WriteFileCache($file);
		shell_exec('/etc/init.d/artica-postfix restart ldap --monit &');
		return;	
	}
}


if(preg_match("#smbd\[.+?User\s+(.+?)with invalid SID\s+(.+?)\s+in passdb#",$buffer,$re)){
	events("SAMBA Invalid SID for {$re[1]}");
	$md5=md5("{$re[1]}{$re[2]}");
	$file="/etc/artica-postfix/croned.1/samba.invalid.sid.$md5";
	if(IfFileTime($file)){
		$unix=new unix();
		$localsid=$unix->GET_LOCAL_SID();
		$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.samba.synchronize.php";
		$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET($cmd);
		email_events("Samba invalid SID for {$re[1]}","Samba claim \"$buffer\"\nUser:{$re[1]} with sid {$re[2]} has this server has the SID $localsid\nArtica will re-sync accounts",'system');
		WriteFileCache($file);
	}
	return true;
}

if(preg_match("#smbd\[.+?sid\s+(.+?)\s+does not belong to our domain#",$buffer,$re)){
	events("SAMBA Invalid global SID for {$re[1]}");
	$md5=md5("{$re[1]}");
	$file="/etc/artica-postfix/croned.1/samba.invalid.sid.$md5";
	if(IfFileTime($file)){
		$unix=new unix();
		$localsid=$unix->GET_LOCAL_SID();
		email_events("Samba global invalid SID for {$re[1]}","Samba claim \"$buffer\"\n{$re[1]} has this server has the real SID $localsid\nTry to rebuild the configuration trough artica web Interface",'system');
		WriteFileCache($file);
	}
	return true;
}


if(preg_match("#NetBIOS name\s+(.+?)\s+is too long. Truncating to (.+?)#",$buffer,$re)){
	events("SAMBA NetBIOS name {$re[1]} is too long");
	$file="/etc/artica-postfix/croned.1/NetBIOSNameTooLong";
	if(IfFileTime($file)){
		email_events("Samba NetBIOS name {$re[1]} is too long","Samba claim \"$buffer\" \nYou should change your server hostname",'system');
		WriteFileCache($file);
	}
	return true;
}	
	
	



if(preg_match('#net:\s+WARNING:\s+Ignoring invalid value.+?Bad Pasword#',$buffer,$re)){
	events("SAMBA unknown parameter Bad Pasword");
	$file="/etc/artica-postfix/croned.1/SambaBadPasword";
	if(IfFileTime($file)){
		email_events("Samba unknown parameter \"Bad Pasword\"","Samba claim \"$buffer\" Artica will reconfigure samba",'system');
		shell_exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.samba.php --reconfigure &");
		WriteFileCache($file);
	}
	return true;
}

if(preg_match('#smbd\[.+Ignoring unknown parameter\s+"hide_unwriteable_files"#',$buffer,$re)){
	events("SAMBA unknown parameter hide_unwriteable_files");
	$file="/etc/artica-postfix/croned.1/hide_unwriteable_files";
	if(IfFileTime($file)){
		email_events("Samba unknown parameter hide_unwriteable_files","Samba claim \"$buffer\" Artica will correct the configuration file",'system');
		shell_exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.samba.php --fix-HideUnwriteableFiles &");
		WriteFileCache($file);
	}
	return true;
}

if(preg_match('#load_usershare_shares: directory\s+(.+?)\s+is not owned by root or does not have the sticky bit#',$buffer,$re)){
	events("SAMBA load_usershare_shares {$re[1]}");
	$file="/etc/artica-postfix/croned.1/load_usershare_shares";
	if(IfFileTime($file)){
		email_events("Samba load_usershare_shares permissions issues","Samba claim \"$buffer\" Artica will correct the filesystem directory",'system');
		shell_exec("chmod 1775 $re[1]/ &");
		shell_exec("chmod chmod +t $re[1]/ &");
		WriteFileCache($file);
	}
	return true;	
}

if(preg_match("#amavis\[.+?:\s+\(.+?\)TROUBLE\s+in child_init_hook:#",$buffer,$re)){
	events("AMAVIS TROUBLE in child_init_hook");
	$file="/etc/artica-postfix/croned.1/amavis.".md5("AMAVIS:TROUBLE in child_init_hook");
	if(IfFileTime($file)){
		email_events("Amavis child error","Amavis claim \"$buffer\" the amavis daemon will be restarted",'postfix');
		shell_exec('/etc/init.d/artica-postfix restart amavis &');
		WriteFileCache($file);
	}
	return true;
}

if(preg_match("#amavis\[.+?:\s+\(.+?\)_DIE:\s+Suicide in child_init_hook#",$buffer,$re)){
	events("AMAVIS TROUBLE in child_init_hook");
	$file="/etc/artica-postfix/croned.1/amavis.".md5("AMAVIS:TROUBLE in child_init_hook");
	if(IfFileTime($file)){
		email_events("Amavis child error","Amavis claim \"$buffer\" the amavis daemon will be restarted",'postfix');
		shell_exec('/etc/init.d/artica-postfix restart amavis &');
		WriteFileCache($file);
	}
	return true;
}


if(preg_match("#smbd_audit:\s+(.+?)\|(.+?)\|(.+?)\|(.+?)\|(.+?)\|(.+?)\|(.+?)\|(.+?)$#",$buffer,$re)){
	events("{$re[5]}/{$re[8]} in xapian queue");
	WriteXapian("{$re[5]}/{$re[8]}"); 
	return true;
}




if(preg_match("#dansguardian.+?:\s+Error connecting to proxy#",$buffer,$re)){
		$file="/etc/artica-postfix/croned.1/squid.tostart.error";
		if(IfFileTime($file,2)){
			events("Squid not available...! Artica will start squid");
			email_events("Proxy error","DansGuardian claim \"$buffer\", Artica will start squid ",'system');
			$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart squid-cache');
			$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET('/etc/init.d/artica-postfix start dansguardian');
			WriteFileCache($file);
			return;
		}else{
			events("Proxy error, but take action after 10mn");
			return;
		}		
}


if(preg_match("#zarafa-server.+?INNODB engine is disabled#",$buffer)){
	$file="/etc/artica-postfix/croned.1/zarafa.INNODB.engine";
	if(IfFileTime($file,2)){
			events("Zarafa innodb errr");
			$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart mysql');
			$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart zarafa');
			WriteFileCache($file);
			return;
		}else{
			events("Zarafa innodb err, but take action after 10mn");
			return;
		}			
}


if(preg_match("#zarafa-spooler\[.+?Unable to open admin session. Error ([0-9a-zA-Z]+)#",$buffer,$re)){
	email_events("zarafa Spooler service error connecting to zarafa server ({$re[1]})","Zarafa claim \"$buffer\" ",'system');
	return;
}


if(preg_match("#(.+?)\[.+?segfault at.+?error.+?in.+?\[#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/segfault.{$re[1]}";
	if(IfFileTime($file,10)){
		events("{$re[1]}: segfault");
		email_events("{$re[1]}: segfault","Kernel claim \"$buffer\" ",'system');
		WriteFileCache($file);
		return;	
	}
}

if(preg_match("#kernel:.+?Out of memory:\s+kill\s+process\s+#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/kernel.Out.of.memory";
	if(!is_numeric($GLOBALS["NOOUTOFMEMORYREBOOT"])){$GLOBALS["NOOUTOFMEMORYREBOOT"]=0;}
	if(IfFileTime($file,1)){
		if($GLOBALS["NOOUTOFMEMORYREBOOT"]<>1){
			events("Out of memory -> REBOOT !!!");
			email_events("Out of memory: reboot action performed","Kernel claim \"$buffer\" the server will be rebooted",'system');
			WriteFileCache($file);
			shell_exec("/etc/init.d/artica-postfix stop");
			shell_exec("reboot");
			return;	
		}else{
			email_events("Out of memory: your system hang !","Kernel claim \"$buffer\" I suggest rebooting the system",'system');
			WriteFileCache($file);
		}
	}
}

if(preg_match("#kernel:.+?ata.+?status:\s+{\s+DRDY#",$buffer,$re)){
	if($GLOBALS["NODRYREBOOT"]==1){
		events("Hard Disk problem: -> reboot banned");
		return ;
	}
	$file="/etc/artica-postfix/croned.1/kernel.DRDY";
	if(IfFileTime($file,5)){
		events("DRDY -> REBOOT !!!");
		exec("/bin/dmesg 2>&1",$results);
		$array["buffer"]=$buffer;
		$array["dmsg"]=$results;
		@mkdir("/etc/artica-postfix/reboot",644,true);
		@file_put_contents("/etc/artica-postfix/reboot/".time(),serialize($array));
		email_events("Hard Disk problem: reboot action performed","Kernel claim \"$buffer\" the server will be rebooted\n".@implode("\n",$results),'system');
		$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("/sbin/reboot");
		return;
	}
}




if(preg_match("#winbindd\[.+?resolve_name: unknown name switch type lmhost#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/winbindd.lmhost.failed";
	if(IfFileTime($file,10)){
		events("winbindd -> lmhost failed");
		WriteFileCache($file);
		$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("{$GLOBALS["LOCATE_PHP5_BIN"]} /usr/share/artica-postfix/exec.samba.php --fix-lmhost");
		return;	
	}	
}

if(preg_match("#nmbd\[.+?become_logon_server_success: Samba is now a logon server for workgroup (.+?)\s+on subnet\s+([A-Z0-9\._-]+)#",$buffer,$re)){
	email_events("Samba (file sharing) started domain {$re[1]}/{$re[2]}","Samba notice: \"$buffer\"",'system');
	return;	
}




if(preg_match("#zarafa-server.+?Unable to connect to database.+?MySQL server on.+?([0-9\.]+)#",$buffer)){
	$file="/etc/artica-postfix/croned.1/zarafa.MYSQL.CONNECT";
	if(IfFileTime($file,2)){
			events("Zarafa Mysql Error errr");
			email_events("MailBox server unable connect to database","Zarafa server  claim \"$buffer\" ",'mailbox');
			WriteFileCache($file);
			return;
		}else{
			events("MailBox server unable connect to database but take action after 10mn");
			return;
		}			
}

if(preg_match("#winbindd:\s+Exceeding\s+[0-9]+\s+client\s+connections.+?no idle connection found#",$buffer)){
	$file="/etc/artica-postfix/croned.1/Winbindd.connect.error";
	if(IfFileTime($file,2)){
			events("winbindd Error connections");
			email_events("Winbindd exceeding connections","Samba server  claim \"$buffer\" \nArtica will restart samba",'system');
			shell_exec('/etc/init.d/artica-postfix restart samba &');
			WriteFileCache($file);
			return;
		}else{
			events("Winbindd exceeding connections take action after 10mn");
			return;
		}			
}




// -------------------------------------------------------------------- MONIT


if(preg_match("#'(.+?)'\s+total mem amount of\s+([0-9]+).+?matches resource limit#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/mem.{$re[1]}.monit";
	if(IfFileTime($file,15)){
				events("{$re[1]} limit memory exceed");
				email_events("{$re[1]}: memory limit","Monitor claim \"$buffer\"",'system');
				WriteFileCache($file);
				return;
			}else{
				events("{$re[1]} limit memory exceed, but take action after 10mn");
				return;
			}			
	}
if(preg_match("#monit\[.+?'(.+?)'\s+trying to restart#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/restart.{$re[1]}.monit";
	if(IfFileTime($file,5)){
				events("{$re[1]} was restarted");
				email_events("{$re[1]}: stopped, try to restart","Monitor claim \"$buffer\"",'system');
				WriteFileCache($file);
				return;
			}else{
				events("{$re[1]}: stopped, try to restart, but take action after 10mn");
				return;
			}			
	}

if(preg_match("#monit\[.+?'(.+?)'\s+process is not running#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/restart.{$re[1]}.monit";
	if(IfFileTime($file,5)){
				events("{$re[1]} was stopped");
				email_events("{$re[1]}: stopped","Monitor claim \"$buffer\"",'system');
				WriteFileCache($file);
				return;
			}else{
				events("{$re[1]}: stopped, but take action after 10mn");
				return;
			}			
	}
	
	
if(preg_match("#pdns\[.+?:\s+binding UDP socket to.+?Address already in use#",$buffer,$re)){
$file="/etc/artica-postfix/croned.1/restart.pdns.bind.error";
	if(IfFileTime($file,5)){
				events("PowerDNS: Unable to bind UDP socket");
				email_events("PowerDNS: Unable to bind UDP socket","Artica will restart PowerDNS",'system');
				$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart pdns');
				WriteFileCache($file);
				return;
			}else{
				events("PowerDNS: Unable to bind UDP socket: but take action after 10mn");
				return;
			}			
	}	
	
	
//pdns_recursor[5011]: Failed to update . records, RCODE=2
if(preg_match("#pdns_recursor\[.+?:\s+Failed to update \. records, RCODE=2#",$buffer,$re)){
$file="/etc/artica-postfix/croned.1/restart.pdns.RCODE2.error";
	if(IfFileTime($file,5)){
				events("PowerDNS: Unable to query Public DNS");
				//email_events("PowerDNS: Unable to query Public DNS","PowerDNS claim: $buffer,It seems that your Public DNS are not available or network is down",'system');
				WriteFileCache($file);
				return;
			}else{
				events("PowerDNS: Unable to query Public DNS: but take action after 10mn");
				return;
			}			
	}		
	

	
if(preg_match("#cpu system usage of ([0-9\.]+)% matches#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/cpu.system.monit";
	if(IfFileTime($file,15)){
				events("cpu exceed");
				email_events("cpu warning {$re[1]}%","Monitor claim \"$buffer\"",'system');
				WriteFileCache($file);
				return;
			}else{
				events("cpu exceed, but take action after 10mn");
				return;
			}			
	}

if(preg_match("#monit.+?'(.+)'\s+start:#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/monit.start.{$re[1]}";
	if(IfFileTime($file,5)){
				events("{$re[1]} start");
				email_events("{$re[1]} starting","Monitor currently starting service {$re[1]}",'system');
				WriteFileCache($file);
				return;
			}else{
				events("{$re[1]} start, but take action after 10mn");
				return;
			}			
	}		

if(preg_match("#monit\[.+?:\s+'(.+?)'\s+process is running with pid\s+([0-9]+)#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/monit.run.{$re[1]}";
	if(IfFileTime($file,5)){
				events("{$re[1]} running");
				email_events("{$re[1]} now running pid {$re[2]}","Monitor report $buffer",'system');
				WriteFileCache($file);
				return;
			}else{
				events("{$re[1]} running, but take action after 10mn");
				return;
			}			
	}		
	
if(preg_match("#nmbd.+?:\s+Cannot sync browser lists#",$buffer,$re)){
		$file="/etc/artica-postfix/croned.1/samba.CannotSyncBrowserLists.error";
		if(IfFileTime($file)){
			events("Samba cannot sync browser list, remove /var/lib/samba/wins.dat");
			@unlink("/var/lib/samba/wins.dat");
			WriteFileCache($file);
		}else{
			events("Samba error:$buffer, but take action after 10mn");
			return;
		}		
}

if(preg_match("#freshclam.+?:\s+Database updated \(([0-9]+)\s+signatures\) from .+?#",$buffer,$re)){
			email_events("ClamAV Database Updated {$re[1]} signatures","$buffer",'update');
			return;
		}
		
		
		
if(preg_match("#freshclam.+?Can.+?t\s+connect to port\s+([0-9]+)\s+of\s+host\s+(.+?)\s+#",$buffer,$re)){
	$host=$re[2].":".$re[1];
	$file="/etc/artica-postfix/croned.1/freshclam.error.".md5($host);
	if(IfFileTime($file)){
			email_events("Unable to update ClamAV Databases from $host","freshclam claim $buffer\nCheck is this server hav access to Internet\nCheck your proxy configuration",'update');
			WriteFileCache($file);
			return;
		}else{
			events("KAV4PROXY error:$buffer, but take action after 10mn");
			return;
		}		
	}		
		


if(preg_match("#KASERROR.+?NOLOGID.+?Can.+?find user mailflt3#",$buffer)){
	$file="/etc/artica-postfix/croned.1/KASERROR.NOLOGID.mailflt3";
		if(IfFileTime($file)){
			$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET('/usr/share/artica-postfix/bin/artica-install --mailflt3');
			WriteFileCache($file);
			return;
		}else{
			events("KASERROR error:$buffer, but take action after 10mn");
			return;
		}		
}

if(preg_match("#lmtp.+?status=deferred.+?lmtp\]:.+?(No such file or directory|Too many levels of symbolic links)#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/cyrus.lmtp.failed";
		if(IfFileTime($file)){
			email_events("cyrus-imapd socket error","Postfix claim \"$buffer\", Artica will restart cyrus",'system');
			$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET('/usr/share/artica-postfix/bin/artica-install --cyrus-checkconfig');
			$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart imap');
			$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("{$GLOBALS["LOCATE_PHP5_BIN"]} /usr/share/artica-postfix/exec.postfix.main.cf.php --imap-sockets");
			cyrus_socket_error($buffer,$re[1]."lmtp");
			WriteFileCache($file);
			return;
		}else{
			events("CYRUS error:$buffer, but take action after 10mn");
			return;
		}		
}


if(preg_match("#rsyncd\[.+?:\s+recv.+?\[(.+?)\].+?([0-9]+)$#",$buffer,$re)){
	$file=md5($buffer);
	@mkdir('/var/log/artica-postfix/rsync',null,true);
	$f["IP"]=$re[1];
	$f["DATE"]=date('Y-m-d H:00:00');
	$f["SIZE"]=$re[2];
	@file_put_contents("/var/log/artica-postfix/rsync/$file",serialize($f));
}

if(preg_match("#kavmilter.+?Can.+?t load keys: No active key#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/kavmilter.key.failed";
		if(IfFileTime($file)){
			email_events("Kaspersky Antivirus Mail license error","KavMilter claim \"$buffer\"",'system');
			WriteFileCache($file);
			return;
		}else{
			events("Kaspersky Antivirus Mail license error:$buffer, but take action after 10mn");
			return;
		}		
}

if(preg_match("#kavmd.+?Can.+?t load keys:.+?#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/kavmd.key.failed";
		if(IfFileTime($file)){
			email_events("Kaspersky Antivirus Mail license error","Kaspersky Antivirus Mail claim \"$buffer\"",'system');
			WriteFileCache($file);
			return;
		}else{
			events("Kaspersky Antivirus Mail license error:$buffer, but take action after 10mn");
			return;
		}		
}

if(preg_match("#kavmd.+?ERROR Engine problem#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/kavmd.engine.failed";
		if(IfFileTime($file)){
			email_events("Kaspersky Antivirus Mail Engine error","Kaspersky Antivirus Mail claim \"$buffer\"",'system');
			WriteFileCache($file);
			return;
		}else{
			events("Kaspersky Antivirus Mail Engine error:$buffer, but take action after 10mn");
			return;
		}		
}



if(preg_match("#kavmilter.+?WARNING.+?Your AV signatures are older than#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/kavmilter.upd.failed";
		if(IfFileTime($file)){
			email_events("Kaspersky Antivirus Mail AV signatures are older","KavMilter claim \"$buffer\"",'system');
			WriteFileCache($file);
			return;
		}else{
			events("Kaspersky Antivirus update license error:$buffer, but take action after 10mn");
			return;
		}		
}
if(preg_match("#dansguardian.+?Error compiling regexp#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/dansguardian.compiling.regexp";
		if(IfFileTime($file)){
			email_events("Dansguardian failed to start","Dansguardian claim \"$buffer\"",'system');
			WriteFileCache($file);
			return;
		}else{
			events("Dansguardian failed to start:$buffer, but take action after 10mn");
			return;
		}		
}

if(preg_match("#kavmilter.+?Invalid value specified for SendmailPath#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/kavmilter.SendmailPath.Invalid";
		if(IfFileTime($file)){
			events("Check SendmailPath for kavmilter");
			$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("{$GLOBALS["LOCATE_PHP5_BIN"]} /usr/share/artica-postfix/exec.kavmilter.php --SendmailPath");
			WriteFileCache($file);
			return;
		}else{
			events("Check SendmailPath for kavmilter:$buffer, but take action after 10mn");
			return;
		}		
}

if(preg_match("#KAVMilter Error.+?Group.+?Default.+?has error#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/kavmilter.Default.error";
		if(IfFileTime($file)){
			events("Check Group default for kavmilter");
			$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("{$GLOBALS["LOCATE_PHP5_BIN"]} /usr/share/artica-postfix/exec.kavmilter.php --default-group");
			WriteFileCache($file);
			return;
		}else{
			events("Check Group default for kavmilter:$buffer, but take action after 10mn");
			return;
		}		
}

if(preg_match("#kavmilter.+?Message INFECTED from (.+?)\(remote:\[(.+?)\).+?with\s+(.+?)$#",$buffer,$re)){
	events("KAVMILTER INFECTION <{$re[1]}> {$re[2]}");
	infected_queue("kavmilter",trim($re[1]),trim($re[2]),trim($re[3]));
	return;
}


if(preg_match("#pdns\[.+?\[LdapBackend.+?Ldap connection to server failed#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/pdns.ldap.error";
	if(IfFileTime($file)){
			events("PDNS LDAP FAILED");
			email_events("PowerDNS ldap connection failed","PowerDNS claim \"$buffer\"",'system');
			WriteFileCache($file);
			return;
		}else{
			events("PDNS FAILED:$buffer, but take action after 10mn");
			return;
		}		
}





if(preg_match("#master.+?cannot find executable for service.+?sieve#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/cyrus.sieve.error";
		if(IfFileTime($file)){
			events("Check sieve path");
			$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --reconfigure-cyrus");
			WriteFileCache($file);
			return;
		}else{
			events("Check sieve path error :$buffer, but take action after 10mn");
			return;
		}		
}


if(preg_match("#smbd\[.+?write_data: write failure in writing to client 0.0.0.0. Error Connection reset by peer#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/samba.Error.Connection.reset.by.peer.error";
		if(IfFileTime($file)){
			events("Check sieve Error Connection reset by peer");
			$text[]="Your MS Windows computers should not have access to the server cause network generic errors";
			$text[]="- Check these parameters:"; 
			$text[]="- Check if Apparmor or SeLinux are disabled on the server.";
			$text[]="- Check your hard drives by this command-line: hdparm -tT /dev/sda(0-9)";
			$text[]="- Check that 137|138|139|445 ports is open from workstation to this server";
			$text[]="- Check network switch or hub connection between this server and your workstations.";
			$text[]="- Try to add this registry key [HKEY_LOCAL_MACHINE\SYSTEM\CurrentControlSet\Services\Disk]\n\t\"TimeOutValue\"=dword:0000003c";
			email_events("Samba network error","Samba claim \"$buffer\"\n" .implode("\n",$text) ,'system');
			WriteFileCache($file);
			return;
		}else{
			events("Check sieve Error Connection reset by peer :$buffer, but take action after 10mn");
			return;
		}		
}


$mem=round(((memory_get_usage()/1024)/1000),2);	
events_not_filtered("Not Filtered:\"$buffer\" (line ".__LINE__.") memory: {$mem}MB");		
}




function IfFileTime($file,$min=10){
	$time=file_time_min($file);
	events("$file = {$time}Mn Max:$min");
	if($time>$min){return true;}
	return false;
}

function WriteFileCache($file){
	@unlink($file);
	@file_put_contents($file,"#");	
}


function Roundcubehack($instance,$account,$ip){
	
	if($ip=="127.0.0.1"){return;}
	
	$enable=$GLOBALS["CLASS_SOCKET"]->GET_INFO("RoundCubeHackEnabled");
	if($enable==null){$enable=1;}
	if($enable==0){return;}
	
	
	
	$maxCount=$GLOBALS["CLASS_SOCKET"]->GET_INFO("RoundCubeHackMaxAttempts");
	
	$maxCountTimeMin=$GLOBALS["CLASS_SOCKET"]->GET_INFO("RoundCubeHackMaxAttemptsTimeMin");
	$attempts=unserialize(base64_decode($GLOBALS["CLASS_SOCKET"]->GET_INFO("RoundCubeHackAttempts")));
	
	if($maxCount==null){$maxCount=6;}
	if($maxCountTimeMin==null){$maxCountTimeMin=10;}
	
	$attempts_first_time=$attempts[$instance][$ip]["TIME"];
	$attempts_count=$attempts[$instance][$ip]["COUNT"];
	if($attempts_first_time==null){$attempts_first_time=time();}
	$minutes=calc_time_min($attempts_first_time);
	
	if($attempts_count==null){$attempts_count=0;}
	$attempts_count++;
	
	events("ROUNDCUBE HACK:: instance \"$instance\" $ip ($account) $attempts_count attempts/$maxCount in {$minutes}mn [ arraof: attempts[$instance][$ip] ]");
	$attempts[$instance][$ip]["TIME"]=$attempts_first_time;
	$attempts[$instance][$ip]["COUNT"]=$attempts_count;
	
	if($attempts_count>=$maxCount){
		if($minutes<=$maxCountTimeMin){
			events("ROUNDCUBE HACK:: block $ip");
			$unix=new unix();
			
			$GLOBALS["CLASS_UNIX"]->send_email_events("RoundCube Hack: $ip from instance $instance is banned","Acount:<b>$account</b>");
			$RoundCubeHackConfig=unserialize(base64_decode($GLOBALS["CLASS_SOCKET"]->GET_INFO("RoundCubeHackConfig")));
			if(!isset($RoundCubeHackConfig[$instance][$ip])){
				$RoundCubeHackConfig[$instance][$ip]=true;
			}
			unset($attempts[$instance][$ip]);
			$GLOBALS["CLASS_SOCKET"]->SaveConfigFile(base64_encode(serialize($RoundCubeHackConfig)),"RoundCubeHackConfig");
			$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("{$GLOBALS["LOCATE_PHP5_BIN"]} /usr/share/artica-postfix/exec.roundcube.php --hacks");
		}else{
			unset($attempts[$instance][$ip]);
		}
	}
	
	$GLOBALS["CLASS_SOCKET"]->SaveConfigFile(base64_encode(serialize($attempts)),"RoundCubeHackAttempts");
	
}




function events($text){
		$filename=basename(__FILE__);
		$logFile="/var/log/artica-postfix/syslogger.debug";
		if(!isset($GLOBALS["CLASS_UNIX"])){
			include_once(dirname(__FILE__)."/framework/class.unix.inc");
			$GLOBALS["CLASS_UNIX"]=new unix();
		}
		$GLOBALS["CLASS_UNIX"]->events("$filename $text",$logFile);
		}
		
function WriteXapian($path){
	$md=md5($path);
	$f="/var/log/artica-postfix/xapian/$md.queue";
	if(is_file($f)){return null;}
	@file_put_contents($f,$path);
	
}
function email_events($subject,$text,$context){
	events("DETECTED: $subject: $text -> $context");
	$GLOBALS["CLASS_UNIX"]->send_email_events($subject,$text,$context);
	}
	
	function vpn_msql_events($subject,$text,$IPPARAM){
	$subject=addslashes($subject);
	$text=addslashes($text);
	$time=time();
	$sql="INSERT INTO vpn_events (`stime`,`subject`,`text`,`IPPARAM`)
	VALUES('$time','$subject','$text','$IPPARAM')";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){events($q->mysql_error ." $sql",__FUNCTION__,__FILE__,__LINE__);}
}

function dhcpd($buffer){
	
	if(preg_match("#dhcpd: Multiple interfaces match the same shared network:\s+(.+)$#",$buffer,$re)){
		if($GLOBALS["CLASS_SOCKET"]->GET_INFO('EnableDHCPServer')==0){return true;}
		email_events("DHCPD:{$re[1]}: check your configuration interfaces match the same shared network","DHCPD claim\n$buffer\nPlease check your configuration",'dhcpd');
		return true;
	}
	
	if(preg_match("#dhcpd:\s+No subnet declaration for\s+(.+?)\s+\((.+?)\)#",$buffer,$re)){
		if($GLOBALS["CLASS_SOCKET"]->GET_INFO('EnableDHCPServer')==0){return true;}
		email_events("DHCPD: bad configuration:: No subnet declaration for {$re[1]}/{$re[2]}",
		"DHCPD claim\n$buffer\nPlease check your configuration.\nYou must add a subnet that handle {$re[2]}",'dhcpd');
		return true;
	}

	
	if(preg_match("#dhcpd:\s+receive_packet failed on (.+?): Network is down#",$buffer,$re)){
			if($GLOBALS["CLASS_SOCKET"]->GET_INFO('EnableDHCPServer')==0){return true;}
			$file="/etc/artica-postfix/croned.1/dhcpd-{$re[1]}-failed";
			events("DHCPD: {$re[1]} is down");
			if(IfFileTime($file,3)){
				email_events("DHCPD: {$re[1]} is down",
				"DHCPD claim\n$buffer\nArtica will restart DHCP service",'dhcpd');
				$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart dhcp");
			}
		return true;
	}	

	if(preg_match("#dhcpd:\s+Not configured to listen on any interfaces#",$buffer)){
		if($GLOBALS["CLASS_SOCKET"]->GET_INFO('EnableDHCPServer')==0){return true;}
		$file="/etc/artica-postfix/croned.1/dhcpfd-interfaces-failed";
		if(IfFileTime($file,10)){
			events("DHCPD -> FAILED");
			email_events("DHCPD corrupted settings","DHCPD claim \"$buffer\"\nArtica will try to repair the configuration",'system');
			shell_exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.dhcpd.php &");
			WriteFileCache($file);
			return true;	
		}	
	}

if(preg_match("#dhcpd: DHCPREQUEST for (.+?)\s+from\s+(.+?)\s+\((.+?)\)\s+via#",$buffer,$re)){
	if($GLOBALS["CLASS_SOCKET"]->GET_INFO('EnableDHCPServer')==0){return true;}
	$md=md5("{$re[1]}{$re[2]}");
	if(!$GLOBALS["DHCPREQUEST"]["$md"]){
		$GLOBALS["DHCPREQUEST"]["$md"]=true;
		events("DHCPD: IP:{$re[1]} MAC:({$re[2]}) computer name={$re[3]}-> exec.dhcpd-leases.php");
		$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("{$GLOBALS["LOCATE_PHP5_BIN"]} /usr/share/artica-postfix/exec.dhcpd-leases.php --single-computer {$re[1]} {$re[2]} {$re[3]}");
	}
	return true;
}	

return false;
	
}

function squid_parser($buffer){
	if(strpos($buffer,"Initializing IP Cache...")>0){return;}
	if(strpos($buffer,"DNS Socket created")>0){return;}
	if(strpos($buffer,"Target number of buckets")>0){return;}
	
	
if(preg_match("#httpAccept:\s+FD\s+[0-9]+:\s+accept failure:\s+\([0-9]+\)\s+Invalid argument#",$buffer)){
	$file="/etc/artica-postfix/croned.1/squid_accept_failure";	
	if(IfFileTime($file)){
			events("FD failure !!");
			email_events("Squid File System error","SQUID claim \"$buffer\" the squid service will be restarted",'proxy');
			$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart squid-cache');
			WriteFileCache($file);
			return;
		}else{
			return;
		}	
}
	
if(preg_match("#squid.*?Failed to verify one of the swap directories, Check cache.log.+?squid -z#",$buffer)){
		events("Squid Must reconfigure squid caches");
		$file="/etc/artica-postfix/croned.1/squid-caches-failed";
		if(IfFileTime($file,5)){
		email_events("Squid failed to load (error swap directories)","Squid claim \"$buffer\"\nArtica will try to repair caches",'proxy');
		shell_exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.squid.php --caches &");
		WriteFileCache($file);
		}else{events("Squid Must reconfigure squid caches (but timed out)");}
		return;	
	}	
	

	if(preg_match("#squid\[([0-9]+)\]:\s+Starting Squid Cache version\s+([0-9\.]+)\s+#",$buffer)){
			events("Squid start pid {$re[1]}");	
			email_events("Squid started pid {$re[1]} version {$re[2]}","Squid has been started \"$buffer\"\n",'proxy');
			return;
	}

	if(preg_match("#Your cache is running out of filedescriptors#",$buffer)){
			events("Squid Your cache is running out of filedescriptors");	
			email_events("SQUID: Your cache is running out of filedescriptors","Squid claim \"$buffer\"\nArtica will reload squid",'proxy');
			shell_exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.squid.php --reload-squid &");
			return;
		}	
	
	
	
if(preg_match("#squid\[.+?comm_old_accept:\s+FD\s+[0-9]+:.+?Invalid argument#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/comm_old_accept.FD15";
	if(IfFileTime($file)){
			events("comm_old_accept FD15 SQUID");
			email_events("Squid File System error","SQUID claim \"$buffer\" the squid service will be restarted",'proxy');
			$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart squid-cache');
			WriteFileCache($file);
			return;
		}else{
			events("comm_old_accept FD15 SQUID");
			return;
		}	
}
if(preg_match("#httpAccept: FD [0-9]+: accept failure: \([0-9]+\) Invalid argument#",$buffer,$re)){
	$file="/etc/artica-postfix/croned.1/comm_old_accept.FD15";
	if(IfFileTime($file)){
			events("FD 83: accept failure SQUID");
			email_events("Squid File System error","SQUID claim \"$buffer\" the squid service will be restarted",'proxy');
			$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET('/etc/init.d/artica-postfix restart squid-cache');
			WriteFileCache($file);
			return;
		}else{
			events("FD 83: accept failure SQUID");
			return;
		}	
}

if(preg_match("#NetfilterInterception.+?failed on FD.+?No such file or directory#",$buffer)){
			events("Squid NetfilterInterception failed");	
			$file="/etc/artica-postfix/croned.1/NetfilterInterception.FD15";
			if(IfFileTime($file)){
				email_events("SQUID: NetfilterInterception failed","Squid claim \"$buffer\"\nArtica will reload squid",'proxy');
				shell_exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.squid.php --reload-squid &");
				WriteFileCache($file);
			}
			return;
		}	


if(preg_match("#squid.+?:\s+essential ICAP service is down after an options fetch failure:\s+icap:\/\/:1344\/av\/respmod#",$buffer,$re)){
		$file="/etc/artica-postfix/croned.1/squid.icap1.error";
		if(IfFileTime($file)){
			email_events("Kaspersky for Squid Down","$buffer",'proxy');
			$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET('/etc/init.d/artica-postfix start kav4proxy');
			$GLOBALS["CLASS_UNIX"]->THREAD_COMMAND_SET("{$GLOBALS["SQUIDBIN"]} -k reconfigure");
			WriteFileCache($file);
		}else{
			events("KAV4PROXY error:$buffer, but take action after 10mn");
			
		}
	return;			
}

if(preg_match("#squid\[([0-9]+)\]:\s+storeLateRelease:\s+#",$buffer,$re)){
	email_events("Proxy: Squid was successfull loaded PID {$re[1]}","$buffer",'proxy');
	return;
}

if(preg_match("#squid\[.+?Squid Parent: child process ([0-9]+) exited with status ([0-9]+)#",$buffer,$re)){
	email_events("Proxy: Squid child process PID {$re[1]} was been terminated (code {$re[2]})","Squid claim \"$buffer\"",'proxy');
	return;
}

if(preg_match("#squid\[.+?:idnsSendQuery.+?Invalid argument#",$buffer,$re)){
	email_events("Proxy: DNS configuration error","Squid claim \"$buffer\"\nIt seems that you have a DNS misconfiguration under Proxy settings",'proxy');
	return;
}

if(preg_match("#squid\[.+?:\s+(.+?):\s+\(13\)\s+Permission denied#",$buffer,$re)){
	$file_error=trim($re[1]);
	$file="/etc/artica-postfix/croned.1/squid.". md5($file_error).".error";
	events("SQUID:: Permissions error on $file_error");
		if(IfFileTime($file)){
			email_events("Squid File $file_error error","SQUID claim \"$buffer\" permissions of $file_error will be changed to squid:squid ",'proxy');
			$dirfile=dirname($file_error);
			if(is_dir($dirfile)){
				$cmd="/bin/chown squid:squid $dirfile";
				events("$cmd");
				shell_exec("$cmd &");
				
				$cmd="/bin/chown -R squid:squid $dirfile";
				events("$cmd");
				shell_exec("$cmd &");
			}
			WriteFileCache($file);	
		}
		
		return;
}


events_not_filtered("SQUID:: Not Filtered:\"$buffer\"");
		
	
}


function nss_parser($buffer){
	if(preg_match('#nss_wins.+?failed to bind to server\s+(.+?)\s+with\s+dn="(.+?)"\s+Error:\s+Invalid credentials#',$buffer,$re)){	
		$file="/etc/artica-postfix/croned.1/nss_parser.Invalidcredentials.error";
		events("nss_wins:: Invalid credentials");
		if(IfFileTime($file)){
			email_events("System error NSS cannot bind to {$re[1]}: Invalid credentials","NSS Wins claim \"$buffer\"",'system');
			}
			WriteFileCache($file);	
			return;	
		}	
		
	
	events_not_filtered("nss_wins:: Not Filtered:\"$buffer\"");
	
}


function events_not_filtered($text){
		$common="/var/log/artica-postfix/syslogger.debug";
		$size=@filesize($common);
		$pid=getmypid();
		$date=date("Y-m-d H:i:s");
		$h = @fopen($common, 'a');
		$sline="[$pid] $text";
		$line="$date [$pid] $text\n";
		@fwrite($h,$line);
		@fclose($h);	
	
}

?>