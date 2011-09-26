<?php

$GLOBALS["FORCE"]=false;
if(is_array($argv)){
	if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
	if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;}
	if($GLOBALS["VERBOSE"]){ini_set('html_errors',0);ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);}
}
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
$GLOBALS["posix_getuid"]=0;
include_once(dirname(__FILE__) . '/ressources/class.users.menus.inc');
include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
include_once(dirname(__FILE__) . '/ressources/class.user.inc');
include_once(dirname(__FILE__) . '/ressources/class.ini.inc');
include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
include_once(dirname(__FILE__) . '/framework/class.unix.inc');
include_once(dirname(__FILE__) . '/framework/frame.class.inc');


if($argv[1]=='--build'){build();die();}
if($argv[1]=='--checks'){TestConfig();die();}
if($argv[1]=='--mysql'){dumpdb();die();}
if($argv[1]=='--start'){start_service();die();}
if($argv[1]=='--networks'){snort_NetWorks();die();}
if($argv[1]=='--purge'){purge();die();}




function start_service(){
	@mkdir("/var/log/snort");
	$sock=new sockets();
	$snortInterfaces=unserialize(base64_decode($sock->GET_INFO("SnortNics")));
	if(count($snortInterfaces)==0){
		echo "Starting......: Snort Daemon version No interfaces to listen set...\n";
		return;
	}	
	echo "Starting......: Snort Daemon building configuration...\n";
	build();
	echo "Starting......: Snort Daemon building configuration done...\n";
	
	while (list ($eth, $ligne) = each ($snortInterfaces) ){
		echo "Starting......: Snort Daemon for Interface \"$eth\"...\n";
		start_interface($eth);
	}
	
	
}

function start_interface($eth){
	$unix=new unix();
	if(!isset($GLOBALS["SNORT_PATH"])){$GLOBALS["SNORT_PATH"]=$unix->find_program("snort");}
	$pidpath="/var/run/snort_$eth.pid";
	$pid=@file_get_contents($pidpath);
	if($unix->process_exists($pid)){
		echo "Starting......: Snort Daemon for Interface \"$eth\" Already running PID $pid\n";
		return;
	}
	
	
	$cmds[]="{$GLOBALS["SNORT_PATH"]}";
	$cmds[]="--create-pidfile";
	$cmds[]="--pid-path /var/run/snort_$eth.pid";
	$cmds[]="-m 027 -D -d -l /var/log/snort -u root -g root";
	$cmds[]="-c /etc/snort/snort.conf -i $eth";
	
	$cmd=@implode(" ",$cmds);
	if($GLOBALS["VERBOSE"]){echo "\n\n".$cmd."\n\n";}
	shell_exec($cmd);
	
	for($i=0;$i<6;$i++){
		$pid=@file_get_contents($pidpath);
		if($unix->process_exists($pid)){
			echo "Starting......: Snort Daemon for Interface \"$eth\" success PID $pid\n";
			return;
		}
		sleep(1);
		
	}
	
	echo "Starting......: Snort Daemon for Interface \"$eth\" failed\n";
	echo "Starting......: Snort $cmd\n";
	
	
}

function snort_NetWorks(){
	$ldap=new clladp();
	$nets=$ldap->load_mynetworks();
	while (list ($index, $line) = each ($nets) ){if(preg_match("#[0-9\.]+\/[0-9]+#",$line)){$newnets[$line]=$line;}}
	$nets2=GetAllNicNets();
	while (list ($index, $line) = each ($nets) ){if(preg_match("#[0-9\.]+\/[0-9]+#",$line)){$newnets[$line]=$line;}}
	while (list ($index, $line) = each ($newnets) ){if(preg_match("#127\.0\.0#",$index)){continue;}$final[]=$index;}
	if(count($final)>1){
		$HOME_NET="[".@implode(",",$final)."]";
	}else{
		$HOME_NET=@implode("",$final);
	}
	
	@file_put_contents("/usr/share/artica-postfix/ressources/logs/web/snort.networks",serialize($final));
	@chmod("/usr/share/artica-postfix/ressources/logs/web/snort.networks",777);
	return $HOME_NET;
}



function build(){
	# Setup the network addresses you are protecting";
	$sock=new sockets();

	$snort_version=snort_version();
	if(preg_match("#([0-9]+)\.([0-9]+)\.#",$snort_version,$re)){
		$inver="{$re[1]}{$re[2]}";
	}
	echo "Starting......: Snort Daemon version $snort_version ($inver)\n";
	
	$HOME_NET=snort_NetWorks();
	echo "Starting......: Snort Daemon HOME_NET $HOME_NET\n";
	
	
	
	
$conf[]="var HOME_NET 188.165.241.0/24";
$conf[]="";
$conf[]="# Set up the external network addresses. Leave as \"any\" in most situations";
$conf[]="var EXTERNAL_NET !\$HOME_NET";
$conf[]="";
$conf[]="# List of DNS servers on your network ";
$conf[]="var DNS_SERVERS \$HOME_NET";
$conf[]="";
$conf[]="# List of SMTP servers on your network";
$conf[]="var SMTP_SERVERS \$HOME_NET";
$conf[]="";
$conf[]="# List of web servers on your network";
$conf[]="var HTTP_SERVERS \$HOME_NET";
$conf[]="";
$conf[]="# List of sql servers on your network ";
$conf[]="var SQL_SERVERS \$HOME_NET";
$conf[]="";
$conf[]="# List of telnet servers on your network";
$conf[]="var TELNET_SERVERS \$HOME_NET";
$conf[]="";
$conf[]="# List of ssh servers on your network";
$conf[]="var SSH_SERVERS \$HOME_NET";
$conf[]="";
$conf[]="# List of ports you run web servers on";
$conf[]="portvar HTTP_PORTS [80,311,591,593,901,1220,1414,1830,2301,2381,2809,3128,3702,5250,7001,7777,7779,8000,8008,8028,8080,8088,8118,8123,8180,8243,8280,8888,9090,9091,9443,9999,11371]";
$conf[]="";
$conf[]="# List of ports you want to look for SHELLCODE on.";
$conf[]="portvar SHELLCODE_PORTS !80";
$conf[]="";
$conf[]="# List of ports you might see oracle attacks on";
$conf[]="portvar ORACLE_PORTS 1024:";
$conf[]="";
$conf[]="# List of ports you want to look for SSH connections on:";
$conf[]="portvar SSH_PORTS 22";
$conf[]="";
$conf[]="# other variables, these should not be modified";
$conf[]="var AIM_SERVERS [64.12.24.0/23,64.12.28.0/23,64.12.161.0/24,64.12.163.0/24,64.12.200.0/24,205.188.3.0/24,205.188.5.0/24,205.188.7.0/24,205.188.9.0/24,205.188.153.0/24,205.188.179.0/24,205.188.248.0/24]";
$conf[]="";
$conf[]="# Path to your rules files (this can be a relative path)";
$conf[]="# Note for Windows users:  You are advised to make this an absolute path,";
$conf[]="# such as:  c:\snort\rules";
$conf[]="var RULE_PATH /etc/snort/rules";
$conf[]="var SO_RULE_PATH /etc/snort/so_rules";
$conf[]="var PREPROC_RULE_PATH /etc/snort/preproc_rules";
$conf[]="";
$conf[]="###################################################";
$conf[]="# Step #2: Configure the decoder.  For more information, see README.decode";
$conf[]="###################################################";
$conf[]="";
$conf[]="# Stop generic decode events:";
$conf[]="config disable_decode_alerts";
$conf[]="";
$conf[]="# Stop Alerts on experimental TCP options";
$conf[]="config disable_tcpopt_experimental_alerts";
$conf[]="";
$conf[]="# Stop Alerts on obsolete TCP options";
$conf[]="config disable_tcpopt_obsolete_alerts";
$conf[]="";
$conf[]="# Stop Alerts on T/TCP alerts";
$conf[]="config disable_tcpopt_ttcp_alerts";
$conf[]="";
$conf[]="# Stop Alerts on all other TCPOption type events:";
$conf[]="config disable_tcpopt_alerts";
$conf[]="";
$conf[]="# Stop Alerts on invalid ip options";
$conf[]="config disable_ipopt_alerts";
$conf[]="";
$conf[]="# Alert if value in length field (IP, TCP, UDP) is greater th elength of the packet";
$conf[]="# config enable_decode_oversized_alerts";
$conf[]="";
$conf[]="# Same as above, but drop packet if in Inline mode (requires enable_decode_oversized_alerts)";
$conf[]="# config enable_decode_oversized_drops";
$conf[]="";
$conf[]="# Configure IP / TCP checksum mode";
$conf[]="config checksum_mode: all";
$conf[]="";
$conf[]="# Configure maximum number of flowbit references.  For more information, see README.flowbits";
$conf[]="# config flowbits_size: 64";
$conf[]="";
$conf[]="# Configure ports to ignore ";
$conf[]="# config ignore_ports: tcp 21 6667:6671 1356";
$conf[]="# config ignore_ports: udp 1:17 53";
$conf[]="";
$conf[]="# Configure active response for non inline operation. For more information, see REAMDE.active";
$conf[]="# config response: eth0 attempts 2";
$conf[]="";
$conf[]="";
$conf[]="###################################################";
$conf[]="# Step #3: Configure the base detection engine.  For more information, see  README.decode";
$conf[]="###################################################";
$conf[]="";
$conf[]="# Configure PCRE match limitations";
$conf[]="config pcre_match_limit: 3500";
$conf[]="config pcre_match_limit_recursion: 1500";
$conf[]="";
$conf[]="# Configure the detection engine  See the Snort Manual, Configuring Snort - Includes - Config";
$conf[]="config detection: search-method lowmem search-optimize";
$conf[]="";
$conf[]="# Configure the event queue.  For more information, see README.event_queue";
$conf[]="config event_queue: max_queue 8 log 3 order_events content_length";
$conf[]="";
$conf[]="###################################################";
$conf[]="# Per packet and rule latency enforcement";
$conf[]="# For more information see README.ppm";
$conf[]="###################################################";
$conf[]="";
$conf[]="# Per Packet latency configuration";
$conf[]="#config ppm: max-pkt-time 250, \ ";
$conf[]="#   fastpath-expensive-packets, \ ";
$conf[]="#   pkt-log";
$conf[]="";
$conf[]="# Per Rule latency configuration";
$conf[]="#config ppm: max-rule-time 200, \ ";
$conf[]="#   threshold 3, \ ";
$conf[]="#   suspend-expensive-rules, \ ";
$conf[]="#   suspend-timeout 20, \ ";
$conf[]="#   rule-log alert";
$conf[]="";
$conf[]="###################################################";
$conf[]="# Configure Perf Profiling for debugging";
$conf[]="# For more information see README.PerfProfiling";
$conf[]="###################################################";
$conf[]="";
$conf[]="#config profile_rules: print all, sort avg_ticks";
$conf[]="#config profile_preprocs: print all, sort avg_ticks";
$conf[]="";
$conf[]="###################################################";
$conf[]="# Step #4: Configure dynamic loaded libraries.  ";
$conf[]="# For more information, see Snort Manual, Configuring Snort - Dynamic Modules";
$conf[]="###################################################";
$conf[]="";
$conf[]="# path to dynamic preprocessor libraries";

$conf[]="dynamicpreprocessor directory ". snort_dynamicpreprocessor_path()."/";
$conf[]="";
$conf[]="# path to base preprocessor engine";
$conf[]="dynamicengine ". snort_dynamicengine_path()."/libsf_engine.so";
$conf[]="";
$snort_dynamicrules_path=snort_dynamicrules_path();
if($snort_dynamicrules_path<>null){
$conf[]="# path to dynamic rules libraries";
$conf[]="dynamicdetection directory $snort_dynamicrules_path";
}
$conf[]="";
$conf[]="###################################################";
$conf[]="# Step #5: Configure preprocessors";
$conf[]="# For more information, see the Snort Manual, Configuring Snort - Preprocessors";
$conf[]="###################################################";
$conf[]="";
$conf[]="# Inline packet normalization. For more information, see README.normalize";
$conf[]="# Does nothing in IDS mode";
//$conf[]="preprocessor normalize_ip4";
//$conf[]="preprocessor normalize_tcp: ips ecn stream";
//$conf[]="preprocessor normalize_icmp4";
//$conf[]="preprocessor normalize_ip6";
//$conf[]="preprocessor normalize_icmp6";
$conf[]="";
$conf[]="# Target-based IP defragmentation.  For more inforation, see README.frag3";
$conf[]="preprocessor frag3_global: max_frags 65536";
$conf[]="preprocessor frag3_engine: policy windows detect_anomalies overlap_limit 10 min_fragment_length 100 timeout 180";
$conf[]="";
$conf[]="# Target-Based stateful inspection/stream reassembly.  For more inforation, see README.stream5";
$conf[]="preprocessor stream5_global: max_tcp 8192, track_tcp yes, track_udp yes, track_icmp no max_active_responses 2 min_response_seconds 5";
$conf[]="preprocessor stream5_tcp: policy windows, detect_anomalies, require_3whs 180, \ ";
$conf[]="   overlap_limit 10, small_segments 3 bytes 150, timeout 180, \ ";
$conf[]="    ports client 21 22 23 25 42 53 79 109 110 111 113 119 135 136 137 139 143 \ ";
$conf[]="        161 445 513 514 587 593 691 1433 1521 2100 3306 6070 6665 6666 6667 6668 6669 \ ";
$conf[]="        7000 32770 32771 32772 32773 32774 32775 32776 32777 32778 32779, \ ";
$conf[]="    ports both 80 311 443 465 563 591 593 636 901 989 992 993 994 995 1220 1414 1830 2301 2381 2809 3128 3702 5250 6907 7001 7702 7777 7779 \ ";
$conf[]="        7801 7900 7901 7902 7903 7904 7905 7906 7908 7909 7910 7911 7912 7913 7914 7915 7916 \ ";
$conf[]="        7917 7918 7919 7920 8000 8008 8028 8080 8088 8118 8123 8180 8243 8280 8888 9090 9091 9443 9999 11371";
$conf[]="preprocessor stream5_udp: timeout 180";
$conf[]="";
$conf[]="# performance statistics.  For more information, see the Snort Manual, Configuring Snort - Preprocessors - Performance Monitor";
$conf[]="# preprocessor perfmonitor: time 300 file /var/snort/snort.stats pktcnt 10000";
$conf[]="";
$conf[]="# HTTP normalization and anomaly detection.  For more information, see README.http_inspect";
$conf[]="preprocessor http_inspect: global iis_unicode_map unicode.map 1252";
$conf[]="preprocessor http_inspect_server: server default \ ";
$conf[]="    chunk_length 500000 \ ";
$conf[]="    server_flow_depth 0 \ ";
$conf[]="    client_flow_depth 0 \ ";
$conf[]="    post_depth 65495 \ ";
$conf[]="	oversize_dir_length 500 \ ";
$conf[]="    max_header_length 750 \ ";
$conf[]="    max_headers 100 \ ";
$conf[]="    ports { 80 311 591 593 901 1220 1414 1830 2301 2381 2809 3128 3702 5250 7001 7777 7779 8000 8008 8028 8080 8088 8118 8123 8180 8243 8280 8888 9090 9091 9443 9999 11371 } \ ";
$conf[]='    non_rfc_char { 0x00 0x01 0x02 0x03 0x04 0x05 0x06 0x07 } \ ';
if($inver>28){
	$conf[]="    enable_cookie \ ";
	$conf[]="    extended_response_inspection \ ";
	//$conf[]="    inspect_gzip \ ";
	$conf[]="    normalize_utf \ ";
	//$conf[]="    unlimited_decompress \ ";
}
$conf[]="    apache_whitespace no \ ";
$conf[]="    ascii no \ ";
$conf[]="    bare_byte no \ ";
$conf[]="    base36 no \ ";
$conf[]="	directory no \ ";
$conf[]="	double_decode no \ ";
$conf[]="	iis_backslash no \ ";
$conf[]="	iis_delimiter no \ ";
$conf[]="	iis_unicode no \ ";
$conf[]="	multi_slash no \ ";
$conf[]="   utf_8 no \ ";
$conf[]="	u_encode yes \ ";
$conf[]="	webroot no ";
$conf[]="";
$conf[]="# ONC-RPC normalization and anomaly detection.  For more information, see the Snort Manual, Configuring Snort - Preprocessors - RPC Decode";
$conf[]="preprocessor rpc_decode: 111 32770 32771 32772 32773 32774 32775 32776 32777 32778 32779 no_alert_multiple_requests no_alert_large_fragments no_alert_incomplete";
$conf[]="";
$conf[]="# Back Orifice detection.";
$conf[]="preprocessor bo";
$conf[]="";
$conf[]="# FTP / Telnet normalization and anomaly detection.  For more information, see README.ftptelnet";
$conf[]="preprocessor ftp_telnet: global inspection_type stateful encrypted_traffic no";
$conf[]="preprocessor ftp_telnet_protocol: telnet \ ";
$conf[]="    ayt_attack_thresh 20 \ ";
$conf[]='    normalize ports { 23 } \ ';
$conf[]="    detect_anomalies";
$conf[]="preprocessor ftp_telnet_protocol: ftp server default \ ";
$conf[]="    def_max_param_len 100 \ ";
$conf[]="    ports { 21 2100 3535 } \ ";
$conf[]="    telnet_cmds yes \ ";
$conf[]="    ignore_telnet_erase_cmds yes \ ";
$conf[]="    ftp_cmds { ABOR ACCT ADAT ALLO APPE AUTH CCC CDUP } \ ";
$conf[]="    ftp_cmds { CEL CLNT CMD CONF CWD DELE ENC EPRT } \ ";
$conf[]="    ftp_cmds { EPSV ESTA ESTP FEAT HELP LANG LIST LPRT } \ ";
$conf[]="    ftp_cmds { LPSV MACB MAIL MDTM MIC MKD MLSD MLST } \ ";
$conf[]="    ftp_cmds { MODE NLST NOOP OPTS PASS PASV PBSZ PORT } \ ";
$conf[]="    ftp_cmds { PROT PWD QUIT REIN REST RETR RMD RNFR } \ ";
$conf[]="    ftp_cmds { RNTO SDUP SITE SIZE SMNT STAT STOR STOU } \ ";
$conf[]="    ftp_cmds { STRU SYST TEST TYPE USER XCUP XCRC XCWD } \ ";
$conf[]="    ftp_cmds { XMAS XMD5 XMKD XPWD XRCP XRMD XRSQ XSEM } \ ";
$conf[]="    ftp_cmds { XSEN XSHA1 XSHA256 } \ ";
$conf[]="    alt_max_param_len 0 { ABOR CCC CDUP ESTA FEAT LPSV NOOP PASV PWD QUIT REIN STOU SYST XCUP XPWD } \ ";
$conf[]="    alt_max_param_len 200 { ALLO APPE CMD HELP NLST RETR RNFR STOR STOU XMKD } \ ";
$conf[]="    alt_max_param_len 256 { CWD RNTO } \ ";
$conf[]="    alt_max_param_len 400 { PORT } \ ";
$conf[]="    alt_max_param_len 512 { SIZE } \ ";
$conf[]="    chk_str_fmt { ACCT ADAT ALLO APPE AUTH CEL CLNT CMD } \ ";
$conf[]="    chk_str_fmt { CONF CWD DELE ENC EPRT EPSV ESTP HELP } \ ";
$conf[]="    chk_str_fmt { LANG LIST LPRT MACB MAIL MDTM MIC MKD } \ ";
$conf[]="    chk_str_fmt { MLSD MLST MODE NLST OPTS PASS PBSZ PORT } \ ";
$conf[]="    chk_str_fmt { PROT REST RETR RMD RNFR RNTO SDUP SITE } \ ";
$conf[]="    chk_str_fmt { SIZE SMNT STAT STOR STRU TEST TYPE USER } \ ";
$conf[]="    chk_str_fmt { XCRC XCWD XMAS XMD5 XMKD XRCP XRMD XRSQ } \ ";
$conf[]="    chk_str_fmt { XSEM XSEN XSHA1 XSHA256 } \ ";
$conf[]="    cmd_validity ALLO < int [ char R int ] > \    ";
$conf[]="    cmd_validity EPSV < [ { char 12 | char A char L char L } ] > \ ";
$conf[]="    cmd_validity MACB < string > \ ";
$conf[]="    cmd_validity MDTM < [ date nnnnnnnnnnnnnn[.n[n[n]]] ] string > \ ";
$conf[]="    cmd_validity MODE < char ASBCZ > \ ";
$conf[]="    cmd_validity PORT < host_port > \ ";
$conf[]="    cmd_validity PROT < char CSEP > \ ";
$conf[]="    cmd_validity STRU < char FRPO [ string ] > \    ";
$conf[]="    cmd_validity TYPE < { char AE [ char NTC ] | char I | char L [ number ] } >";
$conf[]="preprocessor ftp_telnet_protocol: ftp client default \ ";
$conf[]="    max_resp_len 256 \ ";
$conf[]="    bounce yes \ ";
$conf[]="    ignore_telnet_erase_cmds yes \ ";
$conf[]="    telnet_cmds yes";
$conf[]="";
$conf[]="";
$conf[]="# SMTP normalization and anomaly detection.  For more information, see README.SMTP";
$conf[]="preprocessor smtp: ports { 25 465 587 691 } \ ";
$conf[]="    inspection_type stateful \ ";
if($inver>28){
	$conf[]="    enable_mime_decoding \ ";
	$conf[]="    max_mime_depth 20480 \ ";
}
$conf[]="    normalize cmds \ ";
$conf[]="    normalize_cmds { ATRN AUTH BDAT CHUNKING DATA DEBUG EHLO EMAL ESAM ESND ESOM ETRN EVFY } \ ";
$conf[]="    normalize_cmds { EXPN HELO HELP IDENT MAIL NOOP ONEX QUEU QUIT RCPT RSET SAML SEND SOML } \ ";
$conf[]="    normalize_cmds { STARTTLS TICK TIME TURN TURNME VERB VRFY X-ADAT X-DRCP X-ERCP X-EXCH50 } \ ";
$conf[]="    normalize_cmds { X-EXPS X-LINK2STATE XADR XAUTH XCIR XEXCH50 XGEN XLICENSE XQUE XSTA XTRN XUSR } \ ";
$conf[]="    max_command_line_len 512 \ ";
$conf[]="    max_header_line_len 1000 \ ";
$conf[]="    max_response_line_len 512 \ ";
$conf[]="    alt_max_command_line_len 260 { MAIL } \ ";
$conf[]="    alt_max_command_line_len 300 { RCPT } \ ";
$conf[]="    alt_max_command_line_len 500 { HELP HELO ETRN EHLO } \ ";
$conf[]="    alt_max_command_line_len 255 { EXPN VRFY ATRN SIZE BDAT DEBUG EMAL ESAM ESND ESOM EVFY IDENT NOOP RSET } \ ";
$conf[]="    alt_max_command_line_len 246 { SEND SAML SOML AUTH TURN ETRN DATA RSET QUIT ONEX QUEU STARTTLS TICK TIME TURNME VERB X-EXPS X-LINK2STATE XADR XAUTH XCIR XEXCH50 XGEN XLICENSE XQUE XSTA XTRN XUSR } \ ";
$conf[]="    valid_cmds { ATRN AUTH BDAT CHUNKING DATA DEBUG EHLO EMAL ESAM ESND ESOM ETRN EVFY } \ ";
$conf[]="    valid_cmds { EXPN HELO HELP IDENT MAIL NOOP ONEX QUEU QUIT RCPT RSET SAML SEND SOML } \ ";
$conf[]="    valid_cmds { STARTTLS TICK TIME TURN TURNME VERB VRFY X-ADAT X-DRCP X-ERCP X-EXCH50 } \ ";
$conf[]="    valid_cmds { X-EXPS X-LINK2STATE XADR XAUTH XCIR XEXCH50 XGEN XLICENSE XQUE XSTA XTRN XUSR } \ ";
$conf[]="    xlink2state { enabled }";
$conf[]="";
$conf[]="# Portscan detection.  For more information, see README.sfportscan";
$conf[]="# preprocessor sfportscan: proto  { all } memcap { 10000000 } sense_level { low }";
$conf[]="";
$conf[]="# ARP spoof detection.  For more information, see the Snort Manual - Configuring Snort - Preprocessors - ARP Spoof Preprocessor";
$conf[]="# preprocessor arpspoof";
$conf[]="# preprocessor arpspoof_detect_host: 192.168.40.1 f0:0f:00:f0:0f:00";
$conf[]="";
$conf[]="# SSH anomaly detection.  For more information, see README.ssh";
$conf[]="preprocessor ssh: server_ports { 22 } \ ";
$conf[]="                  autodetect \ ";
$conf[]="                  max_client_bytes 19600 \ ";
$conf[]="                  max_encrypted_packets 20 \ ";
$conf[]="                  max_server_version_len 100 \ ";
$conf[]="                  enable_respoverflow enable_ssh1crc32 \ ";
$conf[]="                  enable_srvoverflow enable_protomismatch " ;
$conf[]="";
$conf[]="# SMB / DCE-RPC normalization and anomaly detection.  For more information, see README.dcerpc2";
$conf[]="preprocessor dcerpc2: memcap 102400, events [co ]";
$conf[]="preprocessor dcerpc2_server: default, policy WinXP, \ ";
$conf[]="    detect [smb [139,445], tcp 135, udp 135, rpc-over-http-server 593], \ ";
$conf[]="    autodetect [tcp 1025:, udp 1025:, rpc-over-http-server 1025:], \ ";
$conf[]="    smb_max_chain 3";
$conf[]="";
$conf[]="# DNS anomaly detection.  For more information, see README.dns";
$conf[]="preprocessor dns: ports { 53 } enable_rdata_overflow";
$conf[]="";
$conf[]="# SSL anomaly detection and traffic bypass.  For more information, see README.ssl";
$conf[]="preprocessor ssl: ports { 443 465 563 636 989 992 993 994 995 7801 7702 7900 7901 7902 7903 7904 7905 7906 6907 7908 7909 7910 7911 7912 7913 7914 7915 7916 7917 7918 7919 7920 9000 81 }, trustservers, noinspect_encrypted";
$conf[]="";
if($inver>28){
	$conf[]="# SDF sensitive data preprocessor.  For more information see README.sensitive_data";
	$conf[]="preprocessor sensitive_data: alert_threshold 25";
}
$conf[]="";
$conf[]="###################################################";
$conf[]="# Step #6: Configure output plugins";
$conf[]="# For more information, see Snort Manual, Configuring Snort - Output Modules";
$conf[]="###################################################";
$conf[]="";
$conf[]="# unified2 ";
$conf[]="# Recommended for most installs";
$conf[]="# output unified2: filename merged.log, limit 128, nostamp, mpls_event_types, vlan_event_types";
$conf[]="";
$conf[]="# Additional configuration for specific types of installs";
$conf[]="# output alert_unified2: filename snort.alert, limit 128, nostamp";
$conf[]="# output log_unified2: filename snort.log, limit 128, nostamp ";
$conf[]="";
$conf[]="# syslog";
$conf[]="output alert_syslog: LOG_LOCAL0 LOG_ALERT";
$conf[]="";
$conf[]="# pcap";
$conf[]="# output log_tcpdump: tcpdump.log";
$conf[]="";
$q=new mysql();
$conf[]="# database";
$conf[]="output database: alert, mysql, user=$q->mysql_admin password=$q->mysql_password dbname=snort host=$q->mysql_server";
$conf[]="output database: log, mysql, user=$q->mysql_admin password=$q->mysql_password dbname=snort host=$q->mysql_server";
$conf[]="";
$conf[]="# prelude";
$conf[]="# output alert_prelude";
$conf[]="";
$conf[]="# metadata reference data.  do not modify these lines";
$conf[]="include classification.config";
$conf[]="include reference.config";
$conf[]="";
$conf[]="";
$conf[]="###################################################";
$conf[]="# Step #7: Customize your rule set";
$conf[]="# For more information, see Snort Manual, Writing Snort Rules";
$conf[]="#";
$conf[]="# NOTE: All categories are enabled in this conf file";
$conf[]="###################################################";
$conf[]="";
$conf[]="# site specific rules";

foreach (glob("/etc/snort/rules/*.rules") as $filename) {
	echo "Starting......: Snort Daemon adding rule ".basename($filename)."\n";
	$conf[]="include $filename";
	
}
$conf[]="";
$conf[]="###################################################";
$conf[]="# Step #8: Customize your preprocessor and decoder alerts";
$conf[]="# For more information, see README.decoder_preproc_rules";
$conf[]="###################################################";
$conf[]="";
$conf[]="# decoder and preprocessor event rules";
$conf[]="# include \$PREPROC_RULE_PATH/preprocessor.rules";
$conf[]="# include \$PREPROC_RULE_PATH/decoder.rules";
$conf[]="# include \$PREPROC_RULE_PATH/sensitive-data.rules";
$conf[]="";
$conf[]="###################################################";
$conf[]="# Step #9: Customize your Shared Object Snort Rules";
$conf[]="# For more information, see http://vrt-sourcefire.blogspot.com/2009/01/using-vrt-certified-shared-object-rules.html";
$conf[]="###################################################";
$conf[]="";
$conf[]="# dynamic library rules";
$conf[]="# include \$SO_RULE_PATH/bad-traffic.rules";
$conf[]="# include \$SO_RULE_PATH/chat.rules";
$conf[]="# include \$SO_RULE_PATH/dos.rules";
$conf[]="# include \$SO_RULE_PATH/exploit.rules";
$conf[]="# include \$SO_RULE_PATH/icmp.rules";
$conf[]="# include \$SO_RULE_PATH/imap.rules";
$conf[]="# include \$SO_RULE_PATH/misc.rules";
$conf[]="# include \$SO_RULE_PATH/multimedia.rules";
$conf[]="# include \$SO_RULE_PATH/netbios.rules";
$conf[]="# include \$SO_RULE_PATH/nntp.rules";
$conf[]="# include \$SO_RULE_PATH/p2p.rules";
$conf[]="# include \$SO_RULE_PATH/smtp.rules";
$conf[]="# include \$SO_RULE_PATH/sql.rules";
$conf[]="# include \$SO_RULE_PATH/web-activex.rules";
$conf[]="# include \$SO_RULE_PATH/web-client.rules";
$conf[]="# include \$SO_RULE_PATH/web-iis.rules";
$conf[]="# include \$SO_RULE_PATH/web-misc.rules";
$conf[]="";
$conf[]="# Event thresholding or suppression commands. See threshold.conf ";
$conf[]="include threshold.conf";

@file_put_contents("/etc/snort.conf",@implode("\n",$conf));
@file_put_contents("/etc/snort/snort.conf",@implode("\n",$conf));

if(is_file("/etc/snort/snort.debian.conf")){
	$HOME_NET=str_replace("[","",$HOME_NET);
	$HOME_NET=str_replace("]","",$HOME_NET);
	$debianConf[]="# This file is used for options that are changed by Debian to leave";
	$debianConf[]="# the original lib files untouched.";
	$debianConf[]="# You have to use \"dpkg-reconfigure snort\" to change them.";
	$debianConf[]="";
	$debianConf[]="DEBIAN_SNORT_STARTUP=\"boot\"";
	$debianConf[]="DEBIAN_SNORT_HOME_NET=\"$HOME_NET\"";
	$debianConf[]="DEBIAN_SNORT_OPTIONS=\"\"";
	$debianConf[]="DEBIAN_SNORT_INTERFACE=\"eth0\"";
	$debianConf[]="DEBIAN_SNORT_SEND_STATS=\"true\"";
	$debianConf[]="DEBIAN_SNORT_STATS_RCPT=\"root\"";
	$debianConf[]="DEBIAN_SNORT_STATS_THRESHOLD=\"1\"";
	@file_put_contents("/etc/snort/snort.debian.conf",@implode("\n",$debianConf));
}
echo "Starting......: Snort Daemon Testing configuration....\n";
TestConfig();

}

function snort_version(){
	if(!isset($GLOBALS["SNORT_PATH"])){$unix=new unix();$GLOBALS["SNORT_PATH"]=$unix->find_program("snort");}
	exec("{$GLOBALS["SNORT_PATH"]} -V 2>&1",$results);
	while (list ($index, $line) = each ($results) ){
		if(preg_match("#Version\s+([0-9\.]+)#",$line,$re)){return $re[1];}  
		
	}
	return 0;
}

function snort_dynamicpreprocessor_path(){
	if(is_file("/usr/lib/snort_dynamicpreprocessor/libsf_dns_preproc.so")){return "/usr/lib/snort_dynamicpreprocessor";}
	if(is_file("/usr/lib64/snort_dynamicpreprocessor/libsf_dns_preproc.so")){return "/usr/lib64/snort_dynamicpreprocessor";}
	echo "Starting......: Snort Daemon unable to stat snort_dynamicpreprocessor directory !!\n";
}
function snort_dynamicengine_path(){
	if(is_file("/usr/lib/snort_dynamicengine/libsf_engine.so")){return "/usr/lib/snort_dynamicengine";}
	if(is_file("/usr/lib64/snort_dynamicengine/libsf_engine.so")){return "/usr/lib64/snort_dynamicengine";}
	echo "Starting......: Snort Daemon unable to stat snort_dynamicengine directory !!\n";
}
function snort_dynamicrules_path(){
	if(is_file("/usr/lib/snort_dynamicrules/lib_sfdynamic_example_rule.so")){return "/usr/lib/snort_dynamicrules";}
	if(is_file("/usr/lib64/snort_dynamicrules/lib_sfdynamic_example_rule.so")){return "/usr/lib64/snort_dynamicrules";}
	echo "Starting......: Snort Daemon unable to stat snort_dynamicrules directory !!\n";
}


function TestConfig(){
	
	if(!isset($GLOBALS["SNORT_PATH"])){$unix=new unix();$GLOBALS["SNORT_PATH"]=$unix->find_program("snort");}
	if(!is_file($GLOBALS["SNORT_PATH"])){echo "Starting......: Snort Daemon snort, no such binary\n";return; }
	$results=array();
	exec("{$GLOBALS["SNORT_PATH"]} -T -c /etc/snort/snort.conf 2>&1",$results);
	while (list ($index, $line) = each ($results) ){	
		
		if(preg_match("#ERROR:\s+(.+?)\(([0-9]+)\)\s+Unknown rule option#",$line,$re)){
			echo "Starting......: Snort Daemon \"$line\"\n";
			echo "Starting......: Snort Daemon \"Unknown rule option\" error detected file {$re[1]} line {$re[2]} remove it\n";
			CleanFile($re[1],$re[2]);
		}		
		
		if(preg_match("#ERROR:\s+(.+?)\(([0-9]+)\)\s+=>.+?does not take an argument#",$line,$re)){
			echo "Starting......: Snort Daemon \"$line\"\n";
			echo "Starting......: Snort Daemon \"does not take an argument\" error detected file {$re[1]} line {$re[2]} remove it\n";
			CleanFile($re[1],$re[2]);
		}

		if(preg_match("#Snort successfully loaded all rules#",$line)){
			echo "Starting......: Snort Daemon testing config success\n";
		}
		
		if(preg_match("#successfully validated the configuration#",$line)){
			echo "Starting......: Snort Daemon testing config success\n";
		}
		
	}
	
}

function CleanFile($filename,$rulenumber){
	$rulenumber=$rulenumber-1;
	$f=explode("\n",@file_get_contents($filename));
	echo "Starting......: Snort Daemon removing line {$f[$rulenumber]}\n";
	unset($f[$rulenumber]);
	@file_put_contents($filename,@implode("\n",$f));
	TestConfig();
	
}

function dumpdb(){
	$sql="SELECT * FROM sensor ORDER BY sid LIMIT 0,100";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"snort");
	if(!$q->ok){echo "$q->mysql_error\n";}
	
	echo "hostname\tinterface\tfilter\tdetail\tlast_cid\n";
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		echo "{$ligne["hostname"]}\t{$ligne["interface"]}\t{$ligne["filter"]}\t{$ligne["detail"]}\t{$ligne["last_cid"]}\n";
		
	}

	
}

function GetAllNicNets(){
	$unix=new unix();
	$ifconfig=$unix->find_program("ifconfig");
	exec("$ifconfig -a 2>&1",$results);
		while (list ($index, $ligne) = each ($results) ){
			if(preg_match("#addr:([0-9\.]+).+?Mask:([0-9\.]+)#",$ligne,$re)){
			if($re[1]=="127.0.0.1"){continue;}
			$f[]=getcdir($re[1],$re[2]);
			}
	}
}

function getcdir($ip,$netmask){
	if(!preg_match("#^([0-9]+)\.([0-9]+)\.([0-9]+)#",$ip,$re)){return;}

	exec("/usr/share/artica-postfix/bin/ipcalc {$re[1]}.{$re[2]}.{$re[3]}.0/$netmask 2>&1",$result);
	if(is_array($results)){
		while (list ($index, $ligne) = each ($results) ){
			if(preg_match("#Network:\s+(.+?)\s+#",$ligne,$re)){return trim($re[1]);}
		}
	}
	
	
}

function purge(){
	$sock=new sockets();
	$SnortMaxMysqlEvents=$sock->GET_INFO("SnortMaxMysqlEvents");
	
}



?>