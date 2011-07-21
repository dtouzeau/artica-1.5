<?php
define("alse", "alse");
$GLOBALS["AS_ROOT"]=false;
if(!isset($GLOBALS["DEBUG_MEM"])){$GLOBALS["DEBUG_MEM"]=false;}
if($GLOBALS["DEBUG_MEM"]){events("Memory: class.users.menus.inc START AT -> ".round(((memory_get_usage()/1024)/1000),2) ." line:".__LINE__);}
include_once(dirname(__FILE__) .'/logs.inc');
if($GLOBALS["DEBUG_MEM"]){events("Memory: class.users.menus.inc -> ".round(((memory_get_usage()/1024)/1000),2) ." after include logs.inc line:".__LINE__);}

if(function_exists("posix_getuid")){
	if(posix_getuid()<>0){
		if(function_exists("DefineSessions")){DefineSessions();}
		if(function_exists("CheckSessions")){CheckSessions();}
		if(!$GLOBALS["KAV4PROXY_NOSESSION"]){session_start();}
	}else{
		$GLOBALS["AS_ROOT"]=true;
	}
}

if($GLOBALS["DEBUG_MEM"]){events("Memory: class.users.menus.inc -> ".round(((memory_get_usage()/1024)/1000),2) ." LOADED line:".__LINE__);}

class usersMenus {
	var $TreePage=array("domains.edit.group.php"=>true,"samba.index.php"=>true);
	var $ArchStruct=0;
	var $menus;
	var $main_left_menus=array();
	var $menusright;
	var $toplinks;
	var $priv;
	var $MAIN_CONSOLE_PORT=9000;
	var $EnablePerUserRemoteAddressBook=0;
	var $dhcp_installed=false;
	var $AllowChangeDomains=false;
	var $AsSystemAdministrator=false;
	var $AsOrgStorageAdministrator=false;
	var $AllowXapianDownload=false;
	var $AllowAddGroup=false;
	var $AllowChangeKav=false;
	var $AllowChangeKas=false;
	var $AllowEditAsWbl=false;
	var $AllowEditOuSecurity=false;	
	var $RestrictNabToGroups=false;
	var $AsArticaAdministrator=false;
	var $AsSambaAdministrator=false;
	var $AsMailManAdministrator=false;
	var $AsPostfixAdministrator=false;
	var $AsSquidAdministrator=false;
	var $AsMailBoxAdministrator=false;
	var $AsDansGuardianGroupRule=false;
	var $AsWebMaster=false;
	var $AsDnsAdministrator=false;
	var $AsQuarantineAdministrator=false;
	var $AllowFetchMails=false;
	var $AllowSenderCanonical=false;
	var $AllowChangeUserPassword=false;
	var $AllowDansGuardianBanned=false;
	var $AllowChangeUserKas=false;
	var $AllowEditAliases=false;
	var $AllowEmailing=false;
	var $AllowOpenVPN=false;
	var $AsAnAdministratorGeneric=false;
	var $AllowAddUsers=false;
	var $AllowViewStatistics=false;
	var $AllowChangeAntiSpamSettings=false;
	var $AsOrgAdmin=false;
	var $AsInventoryAdmin=false;
	var $AseMailCampaignsAdmin=false;
	var $uid=null;
	var $groupid=-900;
	var $IptablesEnabled;
	var $KAVMILTER_ENABLED;
	var $lighttpd_installed;
	var $spamassassin_installed=false;
	var $spamassassin_version=0;
	var $fetchmail_configured=false;
	var $LOCAL_SID;
	var $EnableFetchmail=0;
	var $EnableFDMFetch=0;
	var $P3ScanEnabled=0;
	var $RoundCubeHTTPEngineEnabled=0;
	var $roundcube_intversion=0;
	var $roundcube_plugins=null;
	var $template_users_menus_h;
	var $template_user_addr;
	var $PasswdPolicy=array();
	var $disks_size=null;
	var $cpumhz=null;
	var $preload_installed=false;
	var $smbmount_installed=false;
	var $collectd_installed=false;
	var $EnableVirtualDomainsInMailBoxes=false;
	var $SIMPLE_GROUPEWARE_INSTALLED=false;
	var $APP_ATOPENMAIL_INSTALLED=false;
	var $CLAMD_DATABASE_PATH="";
	var $APP_ATOPENMAIL_VERSION="";
	var $COLLECTD_RRD=array();
	var $LoadModulesEnabledParsed=false;
	var $dar_installed=false;
	var $GNARWL_INSTALLED=false;
	var $AllowChangeMailBoxRules=false;
	var $cyrus_ldap_admin_password="";
	var $CLAMD_CONF_PATH="";
	var $PFLOGSUMM_INSTALLED=false;
	var $maillog_path;
	var $spamassassin_ipcountry=false;
	var $CUPS_INSTALLED=false;
	var $OBM2_INSTALLED=false;
	var $SARG_INSTALLED=false;
	var $OPENGOO_INSTALLED=false;
	var $APACHE_GROUPWARE_INSTALLED=false;
	var $POSTFIX_QUEUE=array();
	var $LinuxDistriCode=null;
	var $LinuxDistriFullName=null;
	var $APACHE_INSTALLED=false;
	var $cyrus_sync_installed=false;
	var $AllowManageOwnComputers=false;
	var $POLICYD_WEIGHT_PORT;
	var $CRYPTSETUP_INSTALLED=false;
	var $ZABBIX_INSTALLED=true;
	var $HAMACHI_INSTALLED=true;
	var $phpldapadmin_installed=false;
	var $phpmyadmin_installed=false;
	var $HOSTAPD_INSTALLED=false;
	var $HOSTAPD_BINVER=0;
	var $POSTMULTI=false;
	var $MLDONKEY_INSTALLED=false;
	var $KAV4PROXY_LICENSE_ERROR=false;
	var $KAV4PROXY_LICENSE_ERROR_TEXT="";
	var $VIRTUALBOX_HOST=false;
	var $APP_VBOXADDINTION_INSTALLED=false;
	var $MHONARC_VERSION="";
	var $FETCHMAIL_STATUS="";
	var $kavmilter_installed=false;
	var $APACHE_MOD_AUTHNZ_LDAP=false;
	var $OverWriteRestrictedDomains=false;
	/* This licence must be used Only with Artica-for postfix */
	var $ChartLicence="D1XFWGMEW9L.HSK5T4Q79KLYCK07EK";
	/*you cannot use it without any authorizations of maani.us.
	this licence is Copyright 2004-2007, maani.us
	For more information, see
	http://www.maani.us/xml_charts/index.php?menu=License
	*/
	
	var $ChangeAutoInterface=null;
	var $KASPERSKY_WEB_APPLIANCE=false;
	var $mysql_password='';
	var $mysql_admin='root';
	var $mysql_server='127.0.0.1';
	var $ARTICA_FILTER_QUEUE_PATH;
	var $ARTICA_FILTER_MAXSUBQUEUE;
	var $POSTFIX_MAILDROP_PATH;
	var $GLOBAL_CONFIG;
	var $AutomaticConfig=false;
	var $fetchmail_installed=false;
	var $cyrus_imapd_installed=false;
	var $cyrus_lmtp_path='/var/run/cyrus/socket/lmtp';
	var $kas_installed=false;
	var $aveserver_installed;
	var $rrdtool_installed;
	var $procmail_installed=false;	
	var $dnsmasq_installed=false;
	var $mysql_installed=false;
	var $mailgraph_installed=false;
	var $mailgraph_virus_database;
	var $mailgraph_postfix_database;
	var $queuegraph_installed=false;
	var $yorel_installed=false;
	var $LOCATE_AUTHLOG_PATH="";
	
	var $roundcube_installed=false;
	var $awstats_installed=false;
	var $mysql_enabled=true;
	var $aveserver_pattern_date="00000000;0000";
	var $POSTIFX_STATUS="-1;0.0.0;";
	var $KAS_STATUS="-1;0.0.0;";
	var $CYRUS_STATUS="-1;0.0.0;";
	var $KAV_STATUS="-1;0.0.0;";
	var $FETHMAIL_STATUS="-1;0.0.0;";
	var $ARTICA_ROOT_PATH;
	var $ARTICA_VERSION;
	var $ARTICA_FILTER_STATUS=false;
	var $ARTICA_POLICY_STATUS=false;
	var $EMAILRELAY_STATUS=false;
	var $EMAILRELAY_INSTALLED=false;
	var $BOGOFILTER_INSTALLED=false;
	var $KAV_MILTER_INSTALLED=false;
	var $MAILMAN_INSTALLED=false;
	var $POSTFIX_INSTALLED=false;
	var $POSTFIX_VERSION="0.0.0";
	var $crossroads_installed=true;
	var $fetchmail_daemon_logs="";
	var $KAVMILTER_PID;
	var $hotwayd_installed;
	var $SQUID_INSTALLED=false;
	var $SQUID_BIN_PATH="";
	var $VMWARE_HOST=false;
	var $KAV4PROXY_INSTALLED=false;	
	var $KAV4PROXY_PID;
	var $KAV4PROXY_MEMORY=0;
	var $KAV4PROXY_PATTERN="";
	var $KAV4PROXY_VERSION="0.0.0";
	var $DANSGUARDIAN_INSTALLED=false;
	var $AMAVIS_INSTALLED=false;
	var $AMAVISD_VERSION=null;
	var $DANSGUARDIAN_VERSION=null;
	var $PUREFTP_INSTALLED=false;
	var $PUREFTP_USERS=false;
	var $MILTERGREYLIST_INSTALLED=false;
	var $MAILFROMD_INSTALLED=false;
	var $SQUID_PID;	
	var $fqdn;
	var $hostname;
	var $mysql_port;
	var $SMTP_LEVEL;
	var $ArticaFilterEnabled;
	var $AmavisFilterEnabled;
	var $MilterGreyListEnabled;
	var $EnableMilterSpyDaemon=0;
	var $SQLGreyEnabled;
	var $KasxFilterEnabled;
	var $OBM_INSTALLED;
	var $OBMEnabled;
	var $NTPD_INSTALLED;
	var $NTPDEnabled;
	var $MailFromdEnabled;
	var $IPTABLES_INSTALLED;
	var $ApacheArticaEnabled;
	var $SAMBA_INSTALLED;
	var $WINBINDD_INSTALLED;
	var $SAMBA_VERSION;
	var $GeoIPDat_Path;
	var $BadMysqlPassword=0;
	var $POSTFIX_LDAP_COMPLIANCE=false;
	var $MAILFROMD_SOCKET="";
	var $CLAMAV_INSTALLED=false;
	var $CLAMD_INSTALLED=false;
	var $CLAMSCAN_INSTALLED=false;
	var $CLAMAV_SOCKET="";
	var $ClamavMilterEnabled=0;
	var $CLAMAV_MILTER_SOCKET;
	var $CLAMAV_MILTER_INSTALLED=false;
	var $SpamAssMilterEnabled=0;
	var $SPAMASS_MILTER_INSTALLED=false;
	var $SPAMASS_MILTER_SOCKET="";
	var $roundcube_folder="";
	var $phpcgi;
	var $razor_installed=false;
	var $pyzor_installed=false;
	var $razor_config="";
	var $spfmilterEnabled=0;
	var $spfmilter_installed=false;
	var $MIMEDEFANG_INSTALLED=false;
	var $netbiosname;
	var $MIMEDEFANG_SOCKET="";
	var $MimeDefangEnabled;
	var $GRAPHDEFANG_INSTALLED=false;
	var $MHONARC_INSTALLED=alse;
	var $SQUID_VERSION;
	var $SQUID_LDAP_AUTH;
	var $SQUID_NTLM_AUTH;
	var $SQUID_BIN_VERSION=0;
	var $stunnel4_installed=false;
	var $DkimFilterEnabled=0;
	var $DKIMFILTER_INSTALLED=false;
	var $DKIMFILTER_SOCKET="";
	var $blkid_installed=false;
	var $nmap_installed=false;
	var $usb_list=array();
	var $roundcube_mysql_sources;
	var $roundcube_web_folder;
	var $KAV4SAMBA_INSTALLED=false;
	var $KAV4SAMBA_VFS="";
	var $SAMBA_MYSQL_AUDIT=false;
	var $awstats_www_path="";
	var $SQUID_ICAP_ENABLED=false;
	var $LIGHTTPD_LDAP_AUTH=false;
	var $BIND9_INSTALLED=false;
	var $bindrrd_installed=false;
	var $fdm_installed=false;
	var $fdm_cache=false;
	var $p3scan_installed=false;
	var $syslogng_installed=false;
	var $AsDebianSystem=false;
	var $sTunnel4enabled=0;
	var $MailArchiverEnabled=0;
	var $roundcube_version="0.0.0";
	var $EnableMilterBogom=0;
	var $openldap_installed=false;
	var $EnableMysqlFeatures=1;
	var $MILTER_SPY_INSTALLED=false;
	var $EnableCollectdDaemon=0;
	var $EnableAmavisDaemon=0;
	var $imapsync_installed=false;
	var $mailsync_installed=false;
	var $ISOQLOG_INSTALLED=false;
	var $DOTCLEAR_INSTALLED=false;
	var $JCHECKMAIL_INSTALLED=false;
	var $zip_installed=false;
	var $JCheckMailEnabled=0;
	var $JCHECKMAIL_SOCKET;
	var $EnableAmavisBackup=0;
	var $OPENLDAP_DYNLIST=false;
	var $EnableRemoteAddressBook=0;
	var $SLPAD_LOCKED=false;
	var $ALTERMIME_INSTALLED=false;
	var $POMMO_INSTALLED=false;
	var $C_ICAP_INSTALLED=false;
	var $C_ICAP_DNSBL=false;
	var $MILTERGREYLIST_SOCKET="";
	var $OPENVPN_INSTALLED=false;
	var $spamassassin_conf_path="";
	var $EnableNonEncryptedLdapSession=1;
	var $SMARTMONTOOLS_INSTALLED=false;
	var $XAPIAN_PHP_INSTALLED=false;
	var $INSTANSEARCH_ENABLE=false;
	var $JOOMLA_INSTALLED=false;
	var $DSTAT_INSTALLED=false;
	var $SUGARCRM_INSTALLED=false;
	var $GNUPLOT_PNG=false;
	var $IOSTAT_INSTALLED=false;
	var $IOSTAT_ARRAY=array();
	var $PHP_BIN_PATH=null;	
	var $EXEC_NICE=null;
	var $spamassassin_bin_path=null;
	var $cyr_partition_default=null;
	var $cyr_config_directory=null;
	var $smbclient_installed=false;
	var $cyrus_murder_installed=false;
	var $FETCHMAIL_VERSION=null;
	var $cyrus_sync_client_path=null;
	var $ctl_mboxlist=null;
	var $CURL_PATH=null;
	var $NMAP_PATH=null;
	var $msmtp_installed=false;
	var $nmap_version=null;
	var $autofs_installed=false;
	var $APACHE_PORT=null;
	var $NFS_SERVER_INSTALLED=false;
	var $LVM_INSTALLED=false;
	var $MYSQL_NDB_MGMD_INSTALLED=false;
	var $CPU_NUMBER=0;
	var $LOAD_AVERAGE=0;
	var $winexe_installed=false;
	var $OCSI_INSTALLED=false;
	var $ASSP_INSTALLED=false;
	var $MEM_HIGER_1G=true;
	var $LMB_LUNDIMATIN_INSTALLED=false;
	var $APACHE_MODE_VHOSTS_LDAP=false;
	var $TCP_ARRAY=array();
	var $POWER_DNS_INSTALLED=false;
	var $mysqld_version=0;
	var $GLUSTER_INSTALLED=false;
	var $SLAPD_CONF_PATH;
	var $cyrus_squatter_exists=false;
	var $cyrus_ipurge_exists=false;
	var $SCANNED_ONLY_INSTALLED=false;
	var $GROUPOFFICE_INSTALLED=false;
	var $POSTFIX_MULTI=false;
	var $AsOrgPostfixAdministrator=false;
	var $AsMessagingOrg=false;
	var $ASSP_VERSION=0;
	var $MEM_TOTAL_INSTALLEE=0;
	var $KASPERSKY_SMTP_APPLIANCE=false;
	var $POSTFILTER_INSTALLED=false;
	var $VMWARE_TOOLS_INSTALLED=false;
	var $SQUID_NTLM_ENABLED=false;
	var $ZARAFA_INSTALLED=false;
	var $SQUIDGUARD_INSTALLED=false;
	var $SQUIDGUARD_BINVER=0;
	var $SQUIDGUARD_BIN_PATH="";
	var $RSYNC_INSTALLED=false;
	var $ZARAFA_APPLIANCE=false;
	var $mysqld_datadir;
	var $DRUPAL_INSTALLED=false;
	var $MAIL_DKIM_VERSION=null;
	var $HDPARM_INSTALLED=false;
	var $APACHE_MODE_WEBDAV=false;
	var $BACKUPPC_INSTALLED=false;
	var $KAV4FS_INSTALLED=false;
	var $OCS_LNX_AGENT_INSTALLED=false;
	var $POSTFIX_PCRE_COMPLIANCE=false;
	var $AsJoomlaWebMaster=false;
	var $APP_AUDITD_INSTALLED=false;
	var $DKFILTER_INSTALLED=false;
	var $OPENDKIM_INSTALLED=false;
	var $DB_RECOVER_INSTALLED=false;
	var $APP_UFDBGUARD_INSTALLED=false;
	var $ufdbgclient_path=null;
	var $roundcube_subscriptions_option=false;
	var $ADZAPPER_INSTALLED=false;
	var $KINIT_INSTALLED=false;
	var $DKIMPROXY_INSTALLED=false;
	var $MILTER_DKIM_INSTALLED=false;
	var $DROPBOX_INSTALLED=false;
	var $VIRTUALBOX_INSTALLED=false;
	var $AsVirtualBoxManager=false;
	var $TFTPD_INSTALLED=false;
	var $offlineimap_installed=false;
	var $THINSTATION_INSTALLED=false;
	var $OPENSSH_VER=0;
	var $cyrus_syncserver_installed=false;
	var $APP_SQUIDCLAMAV_INSTALLED=false;
	var $squidclamav_path="";
	var $PEAR_PACKAGES=array();
	var $RACOON_INSTALLED=false;
	var $IPSEC_INSTALLED=false;
	var $PPTPD_INSTALLED=false;
	var $PPTP_INSTALLED=false;
	var $SQUID_ARP_ACL_ENABLED=false;
	var $APT_MIRROR_INSTALLED=false;
	var $CLUEBRINGER_INSTALLED=false;
	var $qos_tools_installed=false;
	var $tc_version=0;
	var $LDAP_SYNCPROV=false;
	var $ARTICA_META_ENABLED=false;
	var $PIWIGO_INSTALLED=false;
	var $APP_SABNZBDPLUS_INSTALLED=false;
	var $POSTSCREEN_INSTALLED=false;
	var $KASPERSKY_UPDATE_UTILITY_INSTALLED=false;
	var $deduplication_installed=false;
	var $APP_VNSTAT_INSTALLED=false;
	var $IPSET_INSTALLED=false;
	var $EnableManageUsersTroughActiveDirectory=false;
	var $ActiveDirectoryCredentials;
	var $ASPELL_INSTALLED=false;
	var $MUNIN_CLIENT_INSTALLED=false;
	var $CURLFTPFS_INSTALLED=false;
	var $CIFS_INSTALLED=false;
	var $GREYHOLE_INSTALLED=false;
	var $SQUID_CACHMGR=false;
	var $APACHE_DIR_SITES_ENABLED='';
	var $APACHE_RUN_USER='';
	var $QUOTA_INSTALLED=false;
	var $APACHE_MODULES_PATH='';
	var $DAVFS_INSTALLED=false;
	var $APACHE_MOD_QOS=false;
	var $ISCSI_INSTALLED=false;
	var $ISCSI_CLIENT_INSTALLED=false;
	var $ldapClass;
	var $LXC_INSTALLED=false;
	var $AS_VPS_CLIENT=false;
	var $AS_DEBIAN_FAMILY=false;
	var $VLAN_INSTALLED=false;
	var $disks_inodes="";
	var $LOSETUP_INSTALLED;
	var $SNORT_INSTALLED=false;
	var $APP_EYEOS_INSTALLED=false;
	var $EYEOS_VERSION;
	var $APACHE_MOD_SECURITY=false;
	var $APACHE_MOD_EVASIVE=false;
	var $OPENVPN_APPLIANCE=false;
	var $SIEVE_PORT=0;
	var $Z_PUSH_INSTALLED=false;
	var $APACHE_PROXY_MODE=false;
	var $APP_GREENSQL_INSTALLED=false;
	var $IPTABLES_ACCOUNTING_EXISTS=false;
	var $POWER_DNS_MYSQL=false;
	var $POWERADMIN_INSTALLED=false;
	var $APACHE_MOD_CACHE=false;
	var $APP_AMANDA_INSTALLED=false;
	var $PDNSSEC_INSTALLED=false;
	
	
	private function CheckIndexDefaults($_GLOBAL){
		return $_GLOBAL;
		foreach($this as $key => $value) {
			if(!isset($_GLOBAL[$key])){$_GLOBAL[$key]=$value;}
		}
		
		return $_GLOBAL;
		
		
	}
	
	
	function usersMenus($priv=null,$nobuild=0,$hostname=null){
			if($GLOBALS["DEBUG_MEM"]){events("Memory: class.users.menus.inc -> usersMenus() START AT ".round(((memory_get_usage()/1024)/1000),2) ."M :".__LINE__);}
			if(!class_exists("clladp")){include_once(dirname(__FILE__)."/class.ldap.inc");}
			if($GLOBALS["DEBUG_MEM"]){events("Memory: class.users.menus.inc -> usersMenus() ".round(((memory_get_usage()/1024)/1000),2) ." After include LDAP :".__LINE__);}
			$this->ldapClass=new clladp();
			if($GLOBALS["DEBUG_MEM"]){events("Memory: class.users.menus.inc -> usersMenus() ".round(((memory_get_usage()/1024)/1000),2) ." After ldap :".__LINE__);}
			$_GLOBAL=array();
			if($GLOBALS["VERBOSE"]){
					$trace=debug_backtrace();
					if(isset($trace[1])){
						$called=" called by ". basename($trace[1]["file"])." {$trace[1]["function"]}() line {$trace[1]["line"]}";			
						echo "Instanciate userMenus() $called\n";
					}
				}		
		
			if(!isset($GLOBALS["posix_getuid"])){$GLOBALS["posix_getuid"]=1000;if(function_exists("posix_getuid")){$GLOBALS["posix_getuid"]=posix_getuid();}}
			
			include_once(dirname(__FILE__)."/class.sockets.inc");
			$settings=dirname(__FILE__).'/settings.inc';
			
				if(file_exists($settings)){
					@include($settings);
					$this->GLOBAL_CONFIG=$_GLOBAL;
					$GLOBALS["SETTINGS_FILES"]=$_GLOBAL;
					if(!is_array($this->GLOBAL_CONFIG)){echo "Settings unable stat";return false;}
					}else{
					die("Unable to stat $settings");
					}

			
			
			$this->cyrus_ldap_admin_password=$_GLOBAL["cyrus_ldap_admin_password"];
			if(isset($_GLOBAL["AutomaticConfig"])){$this->AutomaticConfig=$_GLOBAL["AutomaticConfig"];}
			$this->MAIN_CONSOLE_PORT=$_GLOBAL["MAIN_CONSOLE_PORT"];
			$this->imapsync_installed=$_GLOBAL["imapsync_installed"];
			$this->fetchmail_installed=$_GLOBAL["fetchmail_installed"];
			$this->cyrus_imapd_installed=$_GLOBAL["cyrus_imapd_installed"];
			$this->LOCATE_AUTHLOG_PATH=$_GLOBAL["LOCATE_AUTHLOG_PATH"];
			if(isset($_GLOBAL["cyrus_lmtp_path"])){$this->cyrus_lmtp_path=$_GLOBAL["cyrus_lmtp_path"];}
			if(isset($_GLOBAL["cyr_partition_default"])){$this->cyr_partition_default=$_GLOBAL["cyr_partition_default"];}
			if(isset($_GLOBAL["cyr_config_directory"])){$this->cyr_config_directory=$_GLOBAL["cyr_config_directory"];}
			$this->kas_installed=$_GLOBAL["kas_installed"];
			$this->aveserver_installed=$_GLOBAL["aveserver_installed"];
			$this->rrdtool_installed=$_GLOBAL["rrdtool_installed"];
			$this->procmail_installed=$_GLOBAL["procmail_installed"];
			$this->mysql_installed=$_GLOBAL["mysql_installed"];
			$this->mailgraph_installed=$_GLOBAL["mailgraph_installed"];
			$this->mailgraph_virus_database=$_GLOBAL["mailgraph_virus_database"];
			$this->mailgraph_postfix_database=$_GLOBAL["mailgraph_postfix_database"];
			$this->queuegraph_installed=$_GLOBAL["queuegraph_installed"];
			$this->yorel_installed=$_GLOBAL["yorel_installed"];
			$this->roundcube_installed=$_GLOBAL["roundcube_installed"];
			$this->ChangeAutoInterface=$_GLOBAL["ChangeAutoInterface"];
			$this->aveserver_pattern_date=$_GLOBAL["aveserver_pattern_date"];
			$this->disks_inodes=trim($_GLOBAL["disks_inodes"]);
			if(isset($_GLOBAL["POSTFIX_STATUS"])){$this->POSTIFX_STATUS=$_GLOBAL["POSTFIX_STATUS"];}
			if(isset($_GLOBAL["KAS_STATUS"])){$this->KAS_STATUS=$_GLOBAL["KAS_STATUS"];}
			if(isset($_GLOBAL["CYRUS_STATUS"])){$this->CYRUS_STATUS=$_GLOBAL["CYRUS_STATUS"];}
			if(isset($_GLOBAL["KAV_STATUS"])){$this->KAV_STATUS=$_GLOBAL["KAV_STATUS"];}
			if(isset($_GLOBAL["FETCHMAIL_STATUS"])){$this->FETCHMAIL_STATUS=$_GLOBAL["FETCHMAIL_STATUS"];}
			if(isset($_GLOBAL["awstats_installed"])){$this->awstats_installed=$_GLOBAL["awstats_installed"];;}
			if(isset($_GLOBAL["BOGOFILTER_INSTALLED"])){$this->BOGOFILTER_INSTALLED=$_GLOBAL["BOGOFILTER_INSTALLED"];;}
			if(isset($_GLOBAL["MAILMAN_INSTALLED"])){$this->MAILMAN_INSTALLED=$_GLOBAL["MAILMAN_INSTALLED"];;}
			if(isset($_GLOBAL["kavmilter_installed"])){$this->KAV_MILTER_INSTALLED=$_GLOBAL["kavmilter_installed"];;}
			if(isset($_GLOBAL["fetchmail_daemon_logs"])){$this->fetchmail_daemon_logs=$_GLOBAL["fetchmail_daemon_logs"];;}
			if(isset($_GLOBAL["ARTICA_ROOT_PATH"])){$this->ARTICA_ROOT_PATH=$_GLOBAL["ARTICA_ROOT_PATH"];;}
			if(isset($_GLOBAL["ARTICA_FILTER_STATUS"])){$this->ARTICA_FILTER_STATUS=$_GLOBAL["ARTICA_FILTER_STATUS"];;}
			if(isset($_GLOBAL["ARTICA_POLICY_STATUS"])){$this->ARTICA_POLICY_STATUS=$_GLOBAL["ARTICA_POLICY_STATUS"];;}
			if(isset($_GLOBAL["ARTICA_VERSION"])){$this->ARTICA_VERSION=$_GLOBAL["ARTICA_VERSION"];;}
			if(isset($_GLOBAL["hotwayd_installed"])){$this->hotwayd_installed=$_GLOBAL["hotwayd_installed"];;}
			if(isset($_GLOBAL["POSTFIX_INSTALLED"])){$this->POSTFIX_INSTALLED=$_GLOBAL["POSTFIX_INSTALLED"];;}
			if(isset($_GLOBAL["EMAILRELAY_STATUS"])){$this->EMAILRELAY_STATUS=$_GLOBAL["EMAILRELAY_STATUS"];;}
			if(isset($_GLOBAL["EMAILRELAY_INSTALLED"])){$this->EMAILRELAY_INSTALLED=$_GLOBAL["EMAILRELAY_INSTALLED"];;}
			if(isset($_GLOBAL["KAVMILTER_PID"])){$this->KAVMILTER_PID=$_GLOBAL["KAVMILTER_PID"];;}
			if(isset($_GLOBAL["SQUID_INSTALLED"])){$this->SQUID_INSTALLED=$_GLOBAL["SQUID_INSTALLED"];;}
			if(isset($_GLOBAL["KAV4PROXY_INSTALLED"])){$this->KAV4PROXY_INSTALLED=$_GLOBAL["KAV4PROXY_INSTALLED"];;}
			if(isset($_GLOBAL["SQUID_PID"])){$this->SQUID_PID=$_GLOBAL["SQUID_PID"];;}
			if(isset($_GLOBAL["SQUID_NTLM_AUTH"])){$this->SQUID_NTLM_AUTH=$_GLOBAL["SQUID_NTLM_AUTH"];;}
			if(isset($_GLOBAL["SQUID_BIN_VERSION"])){$this->SQUID_BIN_VERSION=$_GLOBAL["SQUID_BIN_VERSION"];;}
			if(isset($_GLOBAL["KAV4PROXY_MEMORY"])){$this->KAV4PROXY_MEMORY=$_GLOBAL["KAV4PROXY_MEMORY"];;}
			if(isset($_GLOBAL["KAV4PROXY_PATTERN"])){$this->KAV4PROXY_PATTERN=$_GLOBAL["KAV4PROXY_PATTERN"];;}
			if(isset($_GLOBAL["KAV4PROXY_VERSION"])){$this->KAV4PROXY_VERSION=$_GLOBAL["KAV4PROXY_VERSION"];;}
			if(isset($_GLOBAL["SQUID_CACHMGR"])){$this->SQUID_CACHMGR=$_GLOBAL["SQUID_CACHMGR"];;}
			if(isset($_GLOBAL["APACHE_DIR_SITES_ENABLED"])){$this->APACHE_DIR_SITES_ENABLED=$_GLOBAL["APACHE_DIR_SITES_ENABLED"];;}
			if(isset($_GLOBAL["APACHE_MODULES_PATH"])){$this->APACHE_MODULES_PATH=$_GLOBAL["APACHE_MODULES_PATH"];;}
			
			
			if(isset($_GLOBAL["APACHE_RUN_USER"])){$this->APACHE_RUN_USER=$_GLOBAL["APACHE_RUN_USER"];;}
			if(isset($_GLOBAL["QUOTA_INSTALLED"])){$this->QUOTA_INSTALLED=$_GLOBAL["QUOTA_INSTALLED"];;}
			if(isset($_GLOBAL["DAVFS_INSTALLED"])){$this->DAVFS_INSTALLED=$_GLOBAL["DAVFS_INSTALLED"];;}
			if(isset($_GLOBAL["APACHE_MOD_QOS"])){$this->APACHE_MOD_QOS=$_GLOBAL["APACHE_MOD_QOS"];;}
			$this->OPENVPN_APPLIANCE=$_GLOBAL["OPENVPN_APPLIANCE"];
			
			
			
			
			$this->hostname=$_GLOBAL["fixed_hostname"];
			$this->fqdn=$_GLOBAL["fqdn_hostname"];
			if(isset($_GLOBAL["DANSGUARDIAN_INSTALLED"])){$this->DANSGUARDIAN_INSTALLED=$_GLOBAL["DANSGUARDIAN_INSTALLED"];;}
			if(isset($_GLOBAL["KAV4PROXY_PID"])){$this->KAV4PROXY_PID=$_GLOBAL["KAV4PROXY_PID"];;}
			if(isset($_GLOBAL["dnsmasq_installed"])){$this->dnsmasq_installed=$_GLOBAL["dnsmasq_installed"];;}
			if(isset($_GLOBAL["PUREFTP_INSTALLED"])){$this->PUREFTP_INSTALLED=$_GLOBAL["PUREFTP_INSTALLED"];;}
			if(isset($_SESSION["uid"])){$this->uid=$_SESSION["uid"];}
			if(isset($_SESSION["groupid"])){$this->groupid=$_SESSION["groupid"];}
			if(isset($_GLOBAL["crossroads_installed"])){$this->crossroads_installed=$_GLOBAL["crossroads_installed"];}
			if(isset($_GLOBAL["AMAVIS_INSTALLED"])){$this->AMAVIS_INSTALLED=$_GLOBAL["AMAVIS_INSTALLED"];}
			if(isset($_GLOBAL["mysql_port"])){$this->mysql_port=$_GLOBAL["mysql_port"];}
			if(isset($_GLOBAL["MILTERGREYLIST_INSTALLED"])){$this->MILTERGREYLIST_INSTALLED=$_GLOBAL["MILTERGREYLIST_INSTALLED"];}
			$this->mysql_password=$_GLOBAL["mysql_password"];
			$this->mysql_admin=$_GLOBAL["mysql_admin"];	
			$this->mysql_server=$_GLOBAL["mysql_server"];
			$this->mysql_enabled=$_GLOBAL["mysql_enabled"];
			$this->OBM_INSTALLED=$_GLOBAL["OBM_INSTALLED"];
			if(isset($_GLOBAL["NTPD_INSTALLED"])){$this->NTPD_INSTALLED=$_GLOBAL["NTPD_INSTALLED"];}
			if(isset($_GLOBAL["MAILFROMD_INSTALLED"])){$this->MAILFROMD_INSTALLED=$_GLOBAL["MAILFROMD_INSTALLED"];	}	
			if(isset($_GLOBAL["ARTICA_FILTER_QUEUE_PATH"])){$this->ARTICA_FILTER_QUEUE_PATH=$_GLOBAL["ARTICA_FILTER_QUEUE_PATH"];}
			if(isset($_GLOBAL["ARTICA_FILTER_MAXSUBQUEUE"])){$this->ARTICA_FILTER_MAXSUBQUEUE=$_GLOBAL["ARTICA_FILTER_MAXSUBQUEUE"];}
			$this->IPTABLES_INSTALLED=$_GLOBAL["IPTABLES_INSTALLED"];
			if(isset($_GLOBAL["GeoIPDat_Path"])){$this->GeoIPDat_Path=$_GLOBAL["GeoIPDat_Path"];}
			$this->lighttpd_installed=$_GLOBAL["lighttpd_installed"];
			if($this->ChartLicence==null){$this->ChartLicence='D1XFWGMEW9L.HSK5T4Q79KLYCK07EK';}
			$this->SAMBA_INSTALLED=$_GLOBAL["SAMBA_INSTALLED"];
			$this->LOCAL_SID=$_GLOBAL["LOCAL_SID"];
			$this->BadMysqlPassword=$_GLOBAL["BadMysqlPassword"];
			if($this->BadMysqlPassword==null){$this->BadMysqlPassword=0;}
			if(isset($_GLOBAL["POSTFIX_LDAP_COMPLIANCE"])){$this->POSTFIX_LDAP_COMPLIANCE=$_GLOBAL["POSTFIX_LDAP_COMPLIANCE"];}
			if(isset($_GLOBAL["MAILFROMD_SOCKET"])){$this->MAILFROMD_SOCKET=$_GLOBAL["MAILFROMD_SOCKET"];}
			if(isset($_GLOBAL["CLAMAV_INSTALLED"])){$this->CLAMAV_INSTALLED=$_GLOBAL["CLAMAV_INSTALLED"];}
			if(isset($_GLOBAL["CLAMSCAN_INSTALLED"])){$this->CLAMSCAN_INSTALLED=$_GLOBAL["CLAMSCAN_INSTALLED"];}
			if(isset($_GLOBAL["CLAMAV_SOCKET"])){$this->CLAMAV_SOCKET=$_GLOBAL["CLAMAV_SOCKET"];}
			if(isset($_GLOBAL["CLAMAV_MILTER_SOCKET"])){$this->CLAMAV_MILTER_SOCKET=$_GLOBAL["CLAMAV_MILTER_SOCKET"];}
			if(isset($_GLOBAL["CLAMAV_MILTER_INSTALLED"])){$this->CLAMAV_MILTER_INSTALLED=$_GLOBAL["CLAMAV_MILTER_INSTALLED"];}
			if(isset($_GLOBAL["SPAMASS_MILTER_INSTALLED"])){$this->SPAMASS_MILTER_INSTALLED=$_GLOBAL["SPAMASS_MILTER_INSTALLED"];}
			if(isset($_GLOBAL["SPAMASS_MILTER_SOCKET"])){$this->SPAMASS_MILTER_SOCKET=$_GLOBAL["SPAMASS_MILTER_SOCKET"];}
			if(isset($_GLOBAL["roundcube_folder"])){$this->roundcube_folder=$_GLOBAL["roundcube_folder"];}
			$this->phpcgi=$_GLOBAL["phpcgi"];
			if(isset($_GLOBAL["razor_config"])){$this->razor_config=$_GLOBAL["razor_config"];}
			if(isset($_GLOBAL["razor_installed"])){$this->razor_installed=$_GLOBAL["razor_installed"];}
			if(isset($_GLOBAL["pyzor_installed"])){$this->pyzor_installed=$_GLOBAL["pyzor_installed"];}
			if(isset($_GLOBAL["spfmilter_installed"])){$this->spfmilter_installed=$_GLOBAL["spfmilter_installed"];}
			if(isset($_GLOBAL["netbiosname"])){$this->netbiosname=$_GLOBAL["netbiosname"];}
			if(isset($_GLOBAL["MIMEDEFANG_INSTALLED"])){$this->MIMEDEFANG_INSTALLED=$_GLOBAL["MIMEDEFANG_INSTALLED"];}
			if(isset($_GLOBAL["MIMEDEFANG_SOCKET"])){$this->MIMEDEFANG_SOCKET=$_GLOBAL["MIMEDEFANG_SOCKET"];}
			if(isset($_GLOBAL["GRAPHDEFANG_INSTALLED"])){$this->GRAPHDEFANG_INSTALLED=$_GLOBAL["GRAPHDEFANG_INSTALLED"];}
			if(isset($_GLOBAL["MHONARC_INSTALLED"])){$this->MHONARC_INSTALLED=$_GLOBAL["MHONARC_INSTALLED"];}
			if(isset($_GLOBAL["MHONARC_VERSION"])){$this->MHONARC_VERSION=$_GLOBAL["MHONARC_VERSION"];}
			if(isset($_GLOBAL["spamassassin_installed"])){$this->spamassassin_installed=$_GLOBAL["spamassassin_installed"];}
			if(isset($_GLOBAL["spamassassin_version"])){$this->spamassassin_version=$_GLOBAL["spamassassin_version"];}
			if(isset($_GLOBAL["bogofilter_installed"])){$this->BOGOFILTER_INSTALLED=$_GLOBAL["bogofilter_installed"];}
			if(isset($_GLOBAL["SQUID_VERSION"])){$this->SQUID_VERSION=$_GLOBAL["SQUID_VERSION"];}
			if(isset($_GLOBAL["SQUID_LDAP_AUTH"])){$this->SQUID_LDAP_AUTH=$_GLOBAL["SQUID_LDAP_AUTH"];}
			$this->stunnel4_installed=$_GLOBAL["stunnel4_installed"];
			if(isset($_GLOBAL["DKIMFILTER_INSTALLED"])){$this->DKIMFILTER_INSTALLED=$_GLOBAL["DKIMFILTER_INSTALLED"];}
			if(isset($_GLOBAL["DKIMFILTER_SOCKET"])){$this->DKIMFILTER_SOCKET=$_GLOBAL["DKIMFILTER_SOCKET"];}
			$this->POSTFIX_VERSION=$_GLOBAL["POSTFIX_VERSION"];
			$this->blkid_installed=$_GLOBAL["blkid_installed"];
			if(isset($_GLOBAL["usb_list"])){$this->usb_list=$_GLOBAL["usb_list"];}
			$this->nmap_installed=$_GLOBAL["nmap_installed"];
			if(isset($_GLOBAL["roundcube_mysql_sources"])){$this->roundcube_mysql_sources=$_GLOBAL["roundcube_mysql_sources"];}
			if(isset($_GLOBAL["roundcube_web_folder"])){$this->roundcube_web_folder=$_GLOBAL["roundcube_web_folder"];}
			if(isset($_GLOBAL["fetchmail_configured"])){$this->fetchmail_configured=$_GLOBAL["fetchmail_configured"];}
			if(isset($_GLOBAL["KAV4SAMBA_INSTALLED"])){$this->KAV4SAMBA_INSTALLED=$_GLOBAL["KAV4SAMBA_INSTALLED"];}
			if(isset($_GLOBAL["KAV4SAMBA_VFS"])){$this->KAV4SAMBA_VFS=$_GLOBAL["KAV4SAMBA_VFS"];}
			if(isset($_GLOBAL["SAMBA_MYSQL_AUDIT"])){$this->SAMBA_MYSQL_AUDIT=$_GLOBAL["SAMBA_MYSQL_AUDIT"];}
			if(isset($_GLOBAL["awstats_www_path"])){$this->awstats_www_path=$_GLOBAL["awstats_www_path"];}
			if(isset($_GLOBAL["SQUID_ICAP_ENABLED"])){$this->SQUID_ICAP_ENABLED=$_GLOBAL["SQUID_ICAP_ENABLED"];}
			if(isset($_GLOBAL["LIGHTTPD_LDAP_AUTH"])){$this->LIGHTTPD_LDAP_AUTH=$_GLOBAL["LIGHTTPD_LDAP_AUTH"];}
			if(isset($_GLOBAL["SIEVE_PORT"])){$this->SIEVE_PORT=$_GLOBAL["SIEVE_PORT"];}
			if(isset($_GLOBAL["Z_PUSH_INSTALLED"])){$this->Z_PUSH_INSTALLED=$_GLOBAL["Z_PUSH_INSTALLED"];}
			if(isset($_GLOBAL["APACHE_MOD_CACHE"])){$this->APACHE_MOD_CACHE=$_GLOBAL["APACHE_MOD_CACHE"];}
			
			
			
			
			$this->bindrrd_installed=$_GLOBAL["bindrrd_installed"];
			$this->fdm_installed=$_GLOBAL["fdm_installed"];
			if(isset($_GLOBAL["fdm_cache"])){$this->fdm_cache=$_GLOBAL["fdm_cache"];}
			$this->p3scan_installed=$_GLOBAL["p3scan_installed"];
			$this->syslogng_installed=$_GLOBAL["syslogng_installed"];
			$this->AsDebianSystem=$_GLOBAL["AsDebianSystem"];
			$this->disks_size=$_GLOBAL["disks_size"];
			if(trim($this->hostname)==null){$this->hostname=$this->fqdn;}
			$this->cpumhz=$_GLOBAL["cpumhz"];
			$this->preload_installed=$_GLOBAL["preload_installed"];
			$this->POSTFIX_MAILDROP_PATH=$_GLOBAL["POSTFIX_MAILDROP_PATH"];
			$this->smbmount_installed=$_GLOBAL["smbmount_installed"];
			if(isset($_GLOBAL["PUREFTP_USERS"])){$this->PUREFTP_USERS=$_GLOBAL["PUREFTP_USERS"];}
			if(isset($_GLOBAL["roundcube_version"])){$this->roundcube_version=$_GLOBAL["roundcube_version"];}
			$this->openldap_installed=$_GLOBAL["openldap_installed"];
			$this->collectd_installed=$_GLOBAL["collectd_installed"];
			if(isset($_GLOBAL["COLLECTD_RRD"])){$this->COLLECTD_RRD=$_GLOBAL["COLLECTD_RRD"];}
			$this->EnableVirtualDomainsInMailBoxes=$_GLOBAL["EnableVirtualDomainsInMailBoxes"];
			$this->SIMPLE_GROUPEWARE_INSTALLED=$_GLOBAL["SIMPLE_GROUPEWARE_INSTALLED"];
			$this->APP_ATOPENMAIL_INSTALLED=$_GLOBAL["APP_ATOPENMAIL_INSTALLED"];
			$this->APP_ATOPENMAIL_VERSION=$_GLOBAL["APP_ATOPENMAIL_VERSION"];
			$this->MILTER_SPY_INSTALLED=$_GLOBAL["MILTER_SPY_INSTALLED"];
			$this->mailsync_installed=$_GLOBAL["mailsync_installed"];
			$this->ISOQLOG_INSTALLED=$_GLOBAL["ISOQLOG_INSTALLED"];
			$this->DOTCLEAR_INSTALLED=$_GLOBAL["DOTCLEAR_INSTALLED"];
			if(isset($_GLOBAL["JCHECKMAIL_INSTALLED"])){$this->JCHECKMAIL_INSTALLED=$_GLOBAL["JCHECKMAIL_INSTALLED"];}
			if(isset($_GLOBAL["JCHECKMAIL_SOCKET"])){$this->JCHECKMAIL_SOCKET=$_GLOBAL["JCHECKMAIL_SOCKET"];}
			$this->zip_installed=$_GLOBAL["zip_installed"];
			$this->BIND9_INSTALLED=$_GLOBAL["BIND9_INSTALLED"];
			$this->OPENLDAP_DYNLIST=$_GLOBAL["OPENLDAP_DYNLIST"];
			$this->SLPAD_LOCKED=$_GLOBAL["SLPAD_LOCKED"];
			$this->dhcp_installed=$_GLOBAL["dhcp_installed"];
			if(isset($_GLOBAL["ALTERMIME_INSTALLED"])){$this->ALTERMIME_INSTALLED=$_GLOBAL["ALTERMIME_INSTALLED"];}
			if(isset($_GLOBAL["POMMO_INSTALLED"])){$this->POMMO_INSTALLED=$_GLOBAL["POMMO_INSTALLED"];}
			$this->dar_installed=$_GLOBAL["dar_installed"];
			if(isset($_GLOBAL["SAMBA_VERSION"])){$this->SAMBA_VERSION=$_GLOBAL["SAMBA_VERSION"];}
			if(isset($_GLOBAL["C_ICAP_INSTALLED"])){$this->C_ICAP_INSTALLED=$_GLOBAL["C_ICAP_INSTALLED"];}
			if(isset($_GLOBAL["C_ICAP_DNSBL"])){$this->C_ICAP_DNSBL=$_GLOBAL["C_ICAP_DNSBL"];}
			if(isset($_GLOBAL["MILTERGREYLIST_SOCKET"])){$this->MILTERGREYLIST_SOCKET=$_GLOBAL["MILTERGREYLIST_SOCKET"];}
			$this->OPENVPN_INSTALLED=$_GLOBAL["OPENVPN_INSTALLED"];
			if(isset($_GLOBAL["GNARWL_INSTALLED"])){$this->GNARWL_INSTALLED=$_GLOBAL["GNARWL_INSTALLED"];}
			if(isset($_GLOBAL["CLAMD_CONF_PATH"])){$this->CLAMD_CONF_PATH=$_GLOBAL["CLAMD_CONF_PATH"];}
			if(isset($_GLOBAL["CLAMD_DATABASE_PATH"])){$this->CLAMD_DATABASE_PATH=$_GLOBAL["CLAMD_DATABASE_PATH"];}
			if(isset($_GLOBAL["PFLOGSUMM_INSTALLED"])){$this->PFLOGSUMM_INSTALLED=$_GLOBAL["PFLOGSUMM_INSTALLED"];}
			if(isset($_GLOBAL["maillog_path"])){$this->maillog_path=$_GLOBAL["maillog_path"];}
			if(isset($_GLOBAL["spamassassin_ipcountry"])){$this->spamassassin_ipcountry=$_GLOBAL["spamassassin_ipcountry"];}
			if(isset($_GLOBAL["spamassassin_conf_path"])){$this->spamassassin_conf_path=$_GLOBAL["spamassassin_conf_path"];}
			if(isset($_GLOBAL["CUPS_INSTALLED"])){$this->CUPS_INSTALLED=$_GLOBAL["CUPS_INSTALLED"];}
			$this->OBM2_INSTALLED=$_GLOBAL["OBM2_INSTALLED"];
			$this->SMARTMONTOOLS_INSTALLED=$_GLOBAL["SMARTMONTOOLS_INSTALLED"];
			if(isset($_GLOBAL["SARG_INSTALLED"])){$this->SARG_INSTALLED=$_GLOBAL["SARG_INSTALLED"];}
			$this->XAPIAN_PHP_INSTALLED=$_GLOBAL["XAPIAN_PHP_INSTALLED"];
			$this->OPENGOO_INSTALLED=$_GLOBAL["OPENGOO_INSTALLED"];
			$this->JOOMLA_INSTALLED=$_GLOBAL["JOOMLA_INSTALLED"];
			if(isset($_GLOBAL["APACHE_GROUPWARE_INSTALLED"])){$this->APACHE_GROUPWARE_INSTALLED=$_GLOBAL["APACHE_GROUPWARE_INSTALLED"];}
			$this->DSTAT_INSTALLED=$_GLOBAL["DSTAT_INSTALLED"];
			$this->SUGARCRM_INSTALLED=$_GLOBAL["SUGARCRM_INSTALLED"];
			if(isset($_GLOBAL["GNUPLOT_PNG"])){$this->GNUPLOT_PNG=$_GLOBAL["GNUPLOT_PNG"];}
			$this->IOSTAT_INSTALLED=$_GLOBAL["IOSTAT_INSTALLED"];
			if(is_array($_GLOBAL["IOSTAT_ARRAY"])){
				$this->IOSTAT_ARRAY=$_GLOBAL["IOSTAT_ARRAY"];
			}
			if(isset($_GLOBAL["POSTFIX_QUEUE"])){$this->POSTFIX_QUEUE=$_GLOBAL["POSTFIX_QUEUE"];}
			$this->PHP_BIN_PATH=$_GLOBAL["PHP_BIN_PATH"];
			$this->EXEC_NICE=$_GLOBAL["EXEC_NICE"];
			if(isset($_GLOBAL["spamassassin_bin_path"])){$this->spamassassin_bin_path=$_GLOBAL["spamassassin_bin_path"];}
			$this->LinuxDistriCode=$_GLOBAL["LinuxDistriCode"];
			$this->LinuxDistriFullName=$_GLOBAL["LinuxDistriFullName"];
			$this->APACHE_INSTALLED=$_GLOBAL["APACHE_INSTALLED"];
			if(isset($_GLOBAL["roundcube_intversion"])){$this->roundcube_intversion=$_GLOBAL["roundcube_intversion"];}
			if(isset($_GLOBAL["roundcube_plugins"])){$this->roundcube_plugins=$_GLOBAL["roundcube_plugins"];}
			if(isset($_GLOBAL["smbclient_installed"])){$this->smbclient_installed=$_GLOBAL["smbclient_installed"];}
			if(isset($_GLOBAL["AMAVISD_VERSION"])){$this->AMAVISD_VERSION=$_GLOBAL["AMAVISD_VERSION"];}
			if(isset($_GLOBAL["cyrus_murder_installed"])){$this->cyrus_murder_installed=$_GLOBAL["cyrus_murder_installed"];}
			if(isset($_GLOBAL["cyrus_sync_installed"])){$this->cyrus_sync_installed=$_GLOBAL["cyrus_sync_installed"];}
			if(isset($_GLOBAL["FETCHMAIL_VERSION"])){$this->FETCHMAIL_VERSION=$_GLOBAL["FETCHMAIL_VERSION"];}
			if(isset($_GLOBAL["cyrus_sync_client_path"])){$this->cyrus_sync_client_path=$_GLOBAL["cyrus_sync_client_path"];}
			if(isset($_GLOBAL["ctl_mboxlist"])){$this->ctl_mboxlist=$_GLOBAL["ctl_mboxlist"];}
			if(isset($_GLOBAL["CURL_PATH"])){$this->CURL_PATH=$_GLOBAL["CURL_PATH"];}
			if(isset($_GLOBAL["DANSGUARDIAN_VERSION"])){$this->DANSGUARDIAN_VERSION=$_GLOBAL["DANSGUARDIAN_VERSION"];}
			if(isset($_GLOBAL["nmap_path"])){$this->NMAP_PATH=$_GLOBAL["nmap_path"];}
			if(isset($_GLOBAL["msmtp_installed"])){$this->msmtp_installed=$_GLOBAL["msmtp_installed"];}
			if(isset($_GLOBAL["nmap_version"])){$this->nmap_version=$_GLOBAL["nmap_version"];}
			$this->autofs_installed=$_GLOBAL["autofs_installed"];
			$this->APACHE_PORT=$_GLOBAL["APACHE_PORT"];
			$this->NFS_SERVER_INSTALLED=$_GLOBAL["NFS_SERVER_INTSALLED"];
			$this->LVM_INSTALLED=$_GLOBAL["LVM_INSTALLED"];
			$this->MYSQL_NDB_MGMD_INSTALLED=$_GLOBAL["MYSQL_NDB_MGMD_INSTALLED"];
			if(isset($_GLOBAL["POLICYD_WEIGHT_PORT"])){$this->POLICYD_WEIGHT_PORT=$_GLOBAL["POLICYD_WEIGHT_PORT"];}
			$this->winexe_installed=$_GLOBAL["winexe_installed"];
			$this->OCSI_INSTALLED=$_GLOBAL["OCSI_INSTALLED"];
			if(isset($_GLOBAL["ASSP_INSTALLED"])){$this->ASSP_INSTALLED=$_GLOBAL["ASSP_INSTALLED"];}
			$this->MEM_HIGER_1G=$_GLOBAL["MEM_HIGER_1G"];
			$this->LMB_LUNDIMATIN_INSTALLED=$_GLOBAL["LMB_LUNDIMATIN_INSTALLED"];
			$this->APACHE_MODE_VHOSTS_LDAP=$_GLOBAL["APACHE_MODE_VHOSTS_LDAP"];
			$this->TCP_ARRAY=$_GLOBAL["TCP_ARRAY"];
			$this->POWER_DNS_INSTALLED=$_GLOBAL["POWER_DNS_INSTALLED"];
			if(isset($_GLOBAL["CLAMD_INSTALLED"])){$this->CLAMD_INSTALLED=$_GLOBAL["CLAMD_INSTALLED"];}
			$this->mysqld_version=$_GLOBAL["mysqld_version"];
			$this->GLUSTER_INSTALLED=$_GLOBAL["GLUSTER_INSTALLED"];
			$this->SLAPD_CONF_PATH=$_GLOBAL["SLAPD_CONF_PATH"];
			if(isset($_GLOBAL["cyrus_squatter_exists"])){$this->cyrus_squatter_exists=$_GLOBAL["cyrus_squatter_exists"];}
			if(isset($_GLOBAL["SCANNED_ONLY_INSTALLED"])){$this->SCANNED_ONLY_INSTALLED=$_GLOBAL["SCANNED_ONLY_INSTALLED"];}
			$this->CRYPTSETUP_INSTALLED=$_GLOBAL["CRYPTSETUP_INSTALLED"];
			$this->ZABBIX_INSTALLED=$_GLOBAL["ZABBIX_INSTALLED"];
			$this->GROUPOFFICE_INSTALLED=$_GLOBAL["GROUPOFFICE_INSTALLED"];
			$this->cyrus_ipurge_exists=$_GLOBAL["cyrus_ipurge_exists"];
			$this->POSTFIX_MULTI=$this->postfixmutli();
			if(isset($_GLOBAL["ASSP_VERSION"])){$this->ASSP_VERSION=$_GLOBAL["ASSP_VERSION"];}
			if(isset($_GLOBAL["HAMACHI_INSTALLED"])){$this->HAMACHI_INSTALLED=$_GLOBAL["HAMACHI_INSTALLED"];}
			if(isset($_GLOBAL["MEM_TOTAL_INSTALLEE"])){$this->MEM_TOTAL_INSTALLEE=$_GLOBAL["MEM_TOTAL_INSTALLEE"];} // 716800
			if(isset($_GLOBAL["KASPERSKY_SMTP_APPLIANCE"])){$this->KASPERSKY_SMTP_APPLIANCE=$_GLOBAL["KASPERSKY_SMTP_APPLIANCE"];}
			if(isset($_GLOBAL["POSTFILTER_INSTALLED"])){$this->POSTFILTER_INSTALLED=$_GLOBAL["POSTFILTER_INSTALLED"];}
			if(isset($_GLOBAL["VMWARE_HOST"])){$this->VMWARE_HOST=$_GLOBAL["VMWARE_HOST"];}
			if(isset($_GLOBAL["VMWARE_TOOLS_INSTALLED"])){$this->VMWARE_TOOLS_INSTALLED=$_GLOBAL["VMWARE_TOOLS_INSTALLED"];}
			if(isset($_GLOBAL["phpldapadmin_installed"])){$this->phpldapadmin_installed=$_GLOBAL["phpldapadmin_installed"];}
			if(isset($_GLOBAL["phpmyadmin_installed"])){$this->phpmyadmin_installed=$_GLOBAL["phpmyadmin_installed"];}			
			if(isset($_GLOBAL["SQUID_NTLM_ENABLED"])){$this->SQUID_NTLM_ENABLED=$_GLOBAL["SQUID_NTLM_ENABLED"];}
			if(isset($_GLOBAL["WINBINDD_INSTALLED"])){$this->WINBINDD_INSTALLED=$_GLOBAL["WINBINDD_INSTALLED"];}
			if(isset($_GLOBAL["ZARAFA_INSTALLED"])){$this->ZARAFA_INSTALLED=$_GLOBAL["ZARAFA_INSTALLED"];}
			if(isset($_GLOBAL["SQUIDGUARD_INSTALLED"])){$this->SQUIDGUARD_INSTALLED=$_GLOBAL["SQUIDGUARD_INSTALLED"];}
			if(isset($_GLOBAL["SQUIDGUARD_BINVER"])){$this->SQUIDGUARD_BINVER=$_GLOBAL["SQUIDGUARD_BINVER"];}
			if(isset($_GLOBAL["SQUIDGUARD_BIN_PATH"])){$this->SQUIDGUARD_BIN_PATH=$_GLOBAL["SQUIDGUARD_BIN_PATH"];}
			if(isset($_GLOBAL["SQUID_BIN_PATH"])){$this->SQUID_BIN_PATH=$_GLOBAL["SQUID_BIN_PATH"];}
			if(isset($_GLOBAL["HOSTAPD_INSTALLED"])){$this->HOSTAPD_INSTALLED=$_GLOBAL["HOSTAPD_INSTALLED"];}
			$this->WPA_SUPPLIANT_INSTALLED=$_GLOBAL["WPA_SUPPLIANT_INSTALLED"];
			if(isset($_GLOBAL["HOSTAPD_BINVER"])){$this->HOSTAPD_BINVER=$_GLOBAL["HOSTAPD_BINVER"];}
			$this->KASPERSKY_WEB_APPLIANCE=$_GLOBAL["KASPERSKY_WEB_APPLIANCE"];
			$this->ArchStruct=$_GLOBAL["ArchStruct"];
			$this->RSYNC_INSTALLED=$_GLOBAL["RSYNC_INSTALLED"];
			$this->ZARAFA_APPLIANCE=$_GLOBAL["ZARAFA_APPLIANCE"];
			$this->mysqld_datadir=$_GLOBAL["mysqld_datadir"];
			$this->DRUPAL_INSTALLED=$_GLOBAL["DRUPAL_INSTALLED"];
			if(isset($_GLOBAL["MAIL_DKIM_VERSION"])){$this->MAIL_DKIM_VERSION=trim($_GLOBAL["MAIL_DKIM_VERSION"]);}
			$this->HDPARM_INSTALLED=trim($_GLOBAL["HDPARM_INSTALLED"]);
			if(isset($_GLOBAL["POSTMULTI"])){$this->POSTMULTI=$_GLOBAL["POSTMULTI"];}
			$this->MLDONKEY_INSTALLED=$_GLOBAL["MLDONKEY_INSTALLED"];
			$this->APACHE_MODE_WEBDAV=$_GLOBAL["APACHE_MODE_WEBDAV"];
			$this->hostname=str_replace('.(none)','',$this->hostname);
			$this->fqdn=str_replace('.(none)','',$this->fqdn);
			$this->BACKUPPC_INSTALLED=$_GLOBAL["BACKUPPC_INSTALLED"];
			$this->BACKUPPC_APACHE=$_GLOBAL["BACKUPPC_APACHE"];
			$this->KAV4FS_INSTALLED=$_GLOBAL["KAV4FS_INSTALLED"];
			if(isset($_GLOBAL["KAV4PROXY_LICENSE_ERROR"])){$this->KAV4PROXY_LICENSE_ERROR=$_GLOBAL["KAV4PROXY_LICENSE_ERROR"];}
			if(isset($_GLOBAL["KAV4PROXY_LICENSE_ERROR_TEXT"])){$this->KAV4PROXY_LICENSE_ERROR_TEXT=$_GLOBAL["KAV4PROXY_LICENSE_ERROR_TEXT"];}
			$this->OCS_LNX_AGENT_INSTALLED=$_GLOBAL["OCS_LNX_AGENT_INSTALLED"];
			if(isset($_GLOBAL["POSTFIX_PCRE_COMPLIANCE"])){$this->POSTFIX_PCRE_COMPLIANCE=$_GLOBAL["POSTFIX_PCRE_COMPLIANCE"];};
			$this->APP_AUDITD_INSTALLED=$_GLOBAL["APP_AUDITD_INSTALLED"];
			if(isset($_GLOBAL["DKFILTER_INSTALLED"])){$this->DKFILTER_INSTALLED=$_GLOBAL["DKFILTER_INSTALLED"];}
			if(isset($_GLOBAL["OPENDKIM_INSTALLED"])){$this->OPENDKIM_INSTALLED=$_GLOBAL["OPENDKIM_INSTALLED"];}
			$this->DB_RECOVER_INSTALLED=$_GLOBAL["DB_RECOVER_INSTALLED"];
			if(isset($_GLOBAL["APP_UFDBGUARD_INSTALLED"])){$this->APP_UFDBGUARD_INSTALLED=$_GLOBAL["APP_UFDBGUARD_INSTALLED"];}
			if(isset($_GLOBAL["ufdbgclient_path"])){$this->ufdbgclient_path=$_GLOBAL["ufdbgclient_path"];}
			if(isset($_GLOBAL["roundcube_subscriptions_option"])){$this->roundcube_subscriptions_option=$_GLOBAL["roundcube_subscriptions_option"];}
			if(isset($_GLOBAL["ADZAPPER_INSTALLED"])){$this->ADZAPPER_INSTALLED=$_GLOBAL["ADZAPPER_INSTALLED"];}
			if(isset($_GLOBAL["KINIT_INSTALLED"])){$this->KINIT_INSTALLED=$_GLOBAL["KINIT_INSTALLED"];}
			if(isset($_GLOBAL["DKIMPROXY_INSTALLED"])){$this->DKIMPROXY_INSTALLED=$_GLOBAL["DKIMPROXY_INSTALLED"];}
			if(isset($_GLOBAL["MILTER_DKIM_INSTALLED"])){$this->MILTER_DKIM_INSTALLED=$_GLOBAL["MILTER_DKIM_INSTALLED"];}
			if(isset($_GLOBAL["DROPBOX_INSTALLED"])){$this->DROPBOX_INSTALLED=$_GLOBAL["DROPBOX_INSTALLED"];}
			if(isset($_GLOBAL["VIRTUALBOX_INSTALLED"])){$this->VIRTUALBOX_INSTALLED=$_GLOBAL["VIRTUALBOX_INSTALLED"];}
			if(isset($_GLOBAL["TFTPD_INSTALLED"])){$this->TFTPD_INSTALLED=$_GLOBAL["TFTPD_INSTALLED"];}
			if(isset($_GLOBAL["offlineimap_installed"])){$this->offlineimap_installed=$_GLOBAL["offlineimap_installed"];}
			if(isset($_GLOBAL["THINSTATION_INSTALLED"])){$this->THINSTATION_INSTALLED=$_GLOBAL["THINSTATION_INSTALLED"];}
			$this->OPENSSH_VER=$_GLOBAL["OPENSSH_VER"];
			if(isset($_GLOBAL["cyrus_syncserver_installed"])){$this->cyrus_syncserver_installed=$_GLOBAL["cyrus_syncserver_installed"];}
			if(isset($_GLOBAL["APP_SQUIDCLAMAV_INSTALLED"])){$this->APP_SQUIDCLAMAV_INSTALLED=$_GLOBAL["APP_SQUIDCLAMAV_INSTALLED"];}
			if(isset($_GLOBAL["squidclamav_path"])){$this->squidclamav_path=$_GLOBAL["squidclamav_path"];}
			$this->RACOON_INSTALLED=$_GLOBAL["RACOON_INSTALLED"];
			$this->IPSEC_INSTALLED=$_GLOBAL["IPSEC_INSTALLED"];
			$this->PPTPD_INSTALLED=$_GLOBAL["PPTPD_INSTALLED"];
			$this->PPTP_INSTALLED=$_GLOBAL["PPTP_INSTALLED"];
			if(isset($_GLOBAL["SQUID_ARP_ACL_ENABLED"])){$this->SQUID_ARP_ACL_ENABLED=$_GLOBAL["SQUID_ARP_ACL_ENABLED"];}
			if(isset($_GLOBAL["GREYHOLE_INSTALLED"])){$this->GREYHOLE_INSTALLED=$_GLOBAL["GREYHOLE_INSTALLED"];}
			$this->APT_MIRROR_INSTALLED=$_GLOBAL["APT_MIRROR_INSTALLED"];
			if(isset($_GLOBAL["CLUEBRINGER_INSTALLED"])){$this->CLUEBRINGER_INSTALLED=$_GLOBAL["CLUEBRINGER_INSTALLED"];}
			$this->qos_tools_installed=$_GLOBAL["qos_tools_installed"];
			$this->tc_version=$_GLOBAL["tc_version"];
			$this->LDAP_SYNCPROV=$_GLOBAL["LDAP_SYNCPROV"];
			$this->PIWIGO_INSTALLED=$_GLOBAL["PIWIGO_INSTALLED"];
			$this->APP_VBOXADDINTION_INSTALLED=$_GLOBAL["APP_VBOXADDINTION_INSTALLED"];
			$this->VIRTUALBOX_HOST=$_GLOBAL["VIRTUALBOX_HOST"];
			$this->APP_SABNZBDPLUS_INSTALLED=$_GLOBAL["APP_SABNZBDPLUS_INSTALLED"];
			if(isset($_GLOBAL["POSTSCREEN_INSTALLED"])){$this->POSTSCREEN_INSTALLED=$_GLOBAL["POSTSCREEN_INSTALLED"];}
			$this->IPSET_INSTALLED=$_GLOBAL["IPSET_INSTALLED"];
			$this->KASPERSKY_UPDATE_UTILITY_INSTALLED=$_GLOBAL["KASPERSKY_UPDATE_UTILITY_INSTALLED"];
			$this->APP_VNSTAT_INSTALLED=$_GLOBAL["APP_VNSTAT_INSTALLED"];
			$this->ASPELL_INSTALLED=$_GLOBAL["ASPELL_INSTALLED"];
			$this->MUNIN_CLIENT_INSTALLED=$_GLOBAL["MUNIN_CLIENT_INSTALLED"];
			$this->APACHE_MOD_AUTHNZ_LDAP=$_GLOBAL["APACHE_MOD_AUTHNZ_LDAP"];
			$this->CURLFTPFS_INSTALLED=$_GLOBAL["CURLFTPFS_INSTALLED"];
			$this->CIFS_INSTALLED=$_GLOBAL["CIFS_INSTALLED"];
			$this->ISCSI_INSTALLED=$_GLOBAL["ISCSI_INSTALLED"];
			$this->LXC_INSTALLED=$_GLOBAL["LXC_INSTALLED"];
			$this->AS_VPS_CLIENT=$_GLOBAL["AS_VPS_CLIENT"];
			$this->VLAN_INSTALLED=$_GLOBAL["VLAN_INSTALLED"];
			$this->LOSETUP_INSTALLED=$_GLOBAL["LOSETUP_INSTALLED"];
			$this->SNORT_INSTALLED=$_GLOBAL["SNORT_INSTALLED"];
			$this->APP_EYEOS_INSTALLED=$_GLOBAL["APP_EYEOS_INSTALLED"];
			$this->EYEOS_VERSION=$_GLOBAL["EYEOS_VERSION"];
			$this->APACHE_MOD_SECURITY=$_GLOBAL["APACHE_MOD_SECURITY"];
			$this->APACHE_MOD_EVASIVE=$_GLOBAL["APACHE_MOD_EVASIVE"];
			$this->APACHE_PROXY_MODE=$_GLOBAL["APACHE_PROXY_MODE"];
			$this->APP_GREENSQL_INSTALLED=$_GLOBAL["APP_GREENSQL_INSTALLED"];
			$this->IPTABLES_ACCOUNTING_EXISTS=$_GLOBAL["IPTABLES_ACCOUNTING_EXISTS"];
			$this->POWER_DNS_MYSQL=$_GLOBAL["POWER_DNS_MYSQL"];
			$this->POWERADMIN_INSTALLED=$_GLOBAL["POWERADMIN_INSTALLED"];
			$this->APP_AMANDA_INSTALLED=$_GLOBAL["APP_AMANDA_INSTALLED"];
			$this->PDNSSEC_INSTALLED=$_GLOBAL["PDNSSEC_INSTALLED"];
			
			
			if(isset($_GLOBAL["ISCSI_CLIENT_INSTALLED"])){$this->ISCSI_CLIENT_INSTALLED=$_GLOBAL["ISCSI_CLIENT_INSTALLED"];}
			if($this->LinuxDistriCode=="DEBIAN"){$this->AS_DEBIAN_FAMILY=true;}
			if($this->LinuxDistriCode=="UBUNTU"){$this->AS_DEBIAN_FAMILY=true;}
			
			
			
			
			if(isset($_GLOBAL["deduplication_installed"])){$this->deduplication_installed=$_GLOBAL["deduplication_installed"];}
			$this->EnableManageUsersTroughActiveDirectory=$_GLOBAL["EnableManageUsersTroughActiveDirectory"];
			$this->ActiveDirectoryCredentials=$_GLOBAL["ActiveDirectoryCredentials"];
			if(!isset($GLOBALS["CLASS_SOCKETS"])){$sock=new sockets();$GLOBALS["CLASS_SOCKETS"]=$sock;}
			if(!is_object($GLOBALS["CLASS_SOCKETS"])){$sock=new sockets();$GLOBALS["CLASS_SOCKETS"]=$sock;}else{$sock=$GLOBALS["CLASS_SOCKETS"];}
			$ArticaMetaEnabled=trim($sock->GET_INFO("ArticaMetaEnabled"));
			if($ArticaMetaEnabled==null){$sock->SET_INFO("ArticaMetaEnabled","0");}
			if($ArticaMetaEnabled==1){$this->ARTICA_META_ENABLED=true;}
			
			$pear=explode(";",$_GLOBAL["PEAR_PACKAGES"]);
			if(is_array($pear)){
				while (list ($num, $pp) = each ($pear) ){
					if(trim($pp)==null){continue;}
					$this->PEAR_PACKAGES[$pp]=true;
				}
			}
			
			
			if($this->SAMBA_INSTALLED){
				if($this->LOCAL_SID==null){
					$this->LOCAL_SID=$sock->getFrameWork('cmd.php?GetLocalSid=yes');
				}
			}
			
			$this->CPU_NUMBER=$_GLOBAL["CPU_NUMBER"];
			$this->LOAD_AVERAGE=$_GLOBAL["LOAD_AVERAGE"];
			
			if($this->OPENVPN_APPLIANCE){
				$this->SQUID_INSTALLED=false;
				$this->SAMBA_INSTALLED=false;
				$this->POSTFIX_INSTALLED=false;
				$this->cyrus_imapd_installed=false;
				$this->ZARAFA_INSTALLED=false;
				$this->APP_AUDITD_INSTALLED=false;
				$this->BACKUPPC_INSTALLED=false;
				$this->nmap_installed=false;
			}
			
			$page=$this->CurrentPageName();
			$this->SessionExpired();
			if($GLOBALS["posix_getuid"]==0){if($this->roundcube_installed){$this->roundcube_detect_version();}}
			
			//Parse privileges
			if(!$GLOBALS["AS_ROOT"]){
				if($GLOBALS["DEBUG_PRIVS"]){writelogs("->_ParsePrivieleges()",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
				$priv=$this->_ParsePrivieleges();
				$this->_TranslateRights($priv);			
				$this->priv=$priv;

			if($nobuild==0){
				$rights=$priv;

				$this->AsAnAdministratorGeneric=$this->IsGlobalAdmin();
				if(!$this->AsArticaAdministrator){
					$menus["{account}"]="javascript:Loadjs(\"users.account.php?js=yes\")";
					//if($this->AllowEditAsWbl){$menus["{white list} & {black list}"]="users.aswb.php";}
				}
				
				
				if($this->AsSystemAdministrator){
					$menus["{manage_your_server}"]="javascript:ConfigureYourserver()";
					$menus["{global settings}"]="javascript:Loadjs(\"artica.settings.php?js=yes\")";
					$menus["{softwares_install}"]="javascript:Loadjs(\"setup.index.php?js=yes\");";
					$menus["{services}"]="javascript:Loadjs(\"admin.index.services.status.php?js=yes\");";
				}
				
				if($this->POSTFIX_INSTALLED){
					if($this->AsPostfixAdministrator){
						$menus["{compile_postfix}"]="javascript:Loadjs(\"postfix.compile.php\");";
					}
				}
				$menus["{explorer}"]="javascript:Loadjs(\"tree.php\");";
				$menus["{logoff}"]="javascript:logoff()";
				$menus["{cache}"]="javascript:CacheOff();";
				$this->menus=$menus;
			}

		
			}
		
	}
	
	private function postfixmutli(){
	if(!$this->POSTMULTI){return false;}
	$vers=explode(".",$this->POSTFIX_VERSION);
	if($vers[0]>=2){if($vers[1]>=5){return true;}}
	}
	
	function SessionExpired(){}
	

	private function CurrentPageName(){$phpPage=basename($_SERVER["SCRIPT_FILENAME"]);return $phpPage;}  		
	

	function LoadModulesEnabled(){
		$debugThis=false;
		if(!isset($GLOBALS["DEBUG_TEMPLATE"])){$GLOBALS["DEBUG_TEMPLATE"]=false;}
		if($GLOBALS["DEBUG_TEMPLATE"]){$debugThis=true;}
		$CurrentPageName=CurrentPageName();
		if($GLOBALS["posix_getuid"]<>0){
			if($GLOBALS["VERBOSE"]){
				$CurrentPageName="logon.php";
			}
		}
		if($this->LoadModulesEnabledParsed){return ;}
		$ldap=$this->ldapClass;
		if(!isset($GLOBALS["CLASS_SOCKET"])){$GLOBALS["CLASS_SOCKET"]=new sockets();$sock=$GLOBALS["CLASS_SOCKET"];}else{$sock=$GLOBALS["CLASS_SOCKET"];}
		if(!class_exists("sqlgrey")){include(dirname(__FILE__).'/class.sqlgrey.inc');}
		if(!class_exists("milter_greylist")){include(dirname(__FILE__) . '/class.milter.greylist.inc');}
		include_once(dirname(__FILE__).'/class.artica.inc');
		if($GLOBALS["DEBUG_TEMPLATE"]){error_log("DEBUG::{$GLOBALS["CURRENT_PAGE"]}:includes done..; function ".__FUNCTION__." in " . __FILE__. " line ".__LINE__);}
		
		if($debugThis){error_log("$CurrentPageName: includes done ".__FUNCTION__.' in '.__FILE__. " in line ". __LINE__);}
		$artica=new artica_general($this->ldapClass);
		if($debugThis){error_log("$CurrentPageName: artica class ok ".__FUNCTION__.' in '.__FILE__. " in line ". __LINE__);}
		
		if($debugThis){writelogs($CurrentPageName.": sqlgrey($this->hostname,true)".__FUNCTION__.'/'.__LINE__);}		
		$sqlg=new sqlgrey($this->hostname,true);
		if($debugThis){writelogs($CurrentPageName.": milter_greylist(true)".__FUNCTION__.'/'.__LINE__);}
		$milter=new milter_greylist(true);
		if($debugThis){writelogs($CurrentPageName.": ".__FUNCTION__.'/'.__LINE__);}
		
		
		if($sock->GET_INFO("kavmilterEnable")==1){$this->KAVMILTER_ENABLED=1;}else{$this->KAVMILTER_ENABLED=0;}
		if($sock->GET_INFO("InstantSearchEnable")==1){$this->INSTANSEARCH_ENABLE=true;}
		$this->MilterGreyListEnabled=$sock->GET_INFO("MilterGreyListEnabled");
		//$this->SMTP_LEVEL=$hash["ArticaMailAddonsLevel"];
		//$this->ArticaFilterEnabled=$hash["ArticaFilterEnabled"];
		$this->AmavisFilterEnabled=$sock->GET_INFO('EnableAmavisDaemon');
		$this->KasxFilterEnabled=$artica->KasxFilterEnabled;
		//$this->NTPDEnabled=$hash["NTPDEnabled"];
		//$this->OBMEnabled=$hash["OBMEnabled"];
		$this->SQLGreyEnabled=$sqlg->SqlGreyEnabled;
		//$this->MailFromdEnabled=$hash["MailFromdEnabled"];
		//$this->IptablesEnabled=$hash["IptablesEnabled"];
		$this->ApacheArticaEnabled=$artica->ApacheArticaEnabled;
		if($this->ApacheArticaEnabled==null){$this->ApacheArticaEnabled=0;}
		$this->ClamavMilterEnabled=$artica->ClamavMilterEnabled;
		$this->SpamAssMilterEnabled=$artica->SpamAssMilterEnabled;
		$this->spfmilterEnabled=$artica->spfmilterEnabled;
		$this->MimeDefangEnabled=$artica->MimeDefangEnabled;
		$this->DkimFilterEnabled=$artica->DkimFilterEnabled;
		$this->EnableFetchmail=$artica->EnableFetchmail;
		$this->EnableFDMFetch=$artica->EnableFDMFetch;
		$this->P3ScanEnabled=$artica->P3ScanEnabled;
		$this->RoundCubeHTTPEngineEnabled=$artica->RoundCubeHTTPEngineEnabled;
		$this->sTunnel4enabled=$artica->sTunnel4enabled;
		$this->MailArchiverEnabled=$sock->GET_INFO("MailArchiverEnabled");
		$this->EnableMilterBogom=$artica->EnableMilterBogom;
		$this->EnableMysqlFeatures=$artica->EnableMysqlFeatures;
		$this->EnableMilterSpyDaemon=$artica->EnableMilterSpyDaemon;
		$this->EnableCollectdDaemon=$artica->EnableCollectdDaemon;
		$this->EnableAmavisDaemon=$artica->EnableAmavisDaemon;
		$this->JCheckMailEnabled=$artica->JCheckMailEnabled;
		$this->EnableAmavisBackup=$artica->EnableAmavisBackup;
		$this->KAVMILTER_ENABLED=$artica->kavmilterEnable;
		$this->EnableRemoteAddressBook=$artica->EnableRemoteAddressBook;
		$this->EnablePerUserRemoteAddressBook=$artica->EnablePerUserRemoteAddressBook;
		$this->EnableNonEncryptedLdapSession=$artica->EnableNonEncryptedLdapSession;
		//writelogs("KasxFilterEnabled={$hash["KasxFilterEnabled"]},ApacheArticaEnabled=$this->ApacheArticaEnabled ",__CLASS__.'/'.__FUNCTION__,__FILE__);
		if(!$this->AMAVIS_INSTALLED){$this->EnableAmavisDaemon==0;}
		
		if($this->SMTP_LEVEL==null){$this->SMTP_LEVEL=0;}
		if($GLOBALS["DEBUG_TEMPLATE"]){error_log("DEBUG::{$GLOBALS["CURRENT_PAGE"]}: DONE; function ".__FUNCTION__." in " . __FILE__. " line ".__LINE__);}
		$this->LoadModulesEnabledParsed=true;
		}
		
		
function ORGANISTATION_FROM_USER(){
	if(!function_exists("ldap_connect")){return array();}
	if($this->ldapClass->ldapFailed){return null;}
	$hash=$this->ldapClass->Hash_Get_ou_from_users($_SESSION["uid"],1);
	if(is_array($hash)){return $hash[0];}
	}	

private function roundcube_detect_version(){
	$webfolder=$this->roundcube_folder;
	$index_php="$this->roundcube_folder/index.php";
	if(!isset($_GET["__ROUNDCUBE_VER"])){$_GET["__ROUNDCUBE_VER"]=null;}
		if($_GET["__ROUNDCUBE_VER"]==null){
			if(!is_file($index_php)){return null;}
			$pattern="#Version\s+([0-9\.a-zA-Z\-]+)#";
			$datas=explode("\n",@file_get_contents($index_php));
			if(!is_array($datas)){return null;}
			while (list ($num, $line) = each ($datas)){
				if(preg_match($pattern,$line,$re)){
					$this->roundcube_version=$re[1];
					$_GET["__ROUNDCUBE_VER"]=$re[1];
					break;
				}
			}
		}
		
	if(!isset($_GET["__ROUNDCUBE_INTVER"])){
		if(preg_match("#^([0-9]+)\.([0-9]+)\.([0-9]+)$#",$this->roundcube_version,$re)){
			if(strlen($re[3])==1){$re[3]="0{$re[3]}";}
			$this->roundcube_intversion=$re[1].$re[2].$re[3];
			$_GET["__ROUNDCUBE_INTVER"]=$this->roundcube_intversion;
			return null;
		}
		
		if(preg_match("#^([0-9]+)\.([0-9]+)$#",$this->roundcube_version,$re)){
			$this->roundcube_intversion=$re[1].$re[2].'0';
			$_GET["__ROUNDCUBE_INTVER"]=$this->roundcube_intversion;
			return null;
		}	
		
		if(preg_match("#^([0-9]+)\.([0-9]+)\.([0-9]+)-#",$this->roundcube_version,$re)){
			
			$this->roundcube_intversion=$re[2].$re[3]."0";
			$_GET["__ROUNDCUBE_INTVER"]=$this->roundcube_intversion;
			return null;
		}	
	}else{
		$this->roundcube_intversion=$this->roundcube_version=$_GET["__ROUNDCUBE_INTVER"];
	}

}

private function build_user_left_menus(){
	
	$array=array();
	if($this->BuildLeftMenus_can_manage_org()){
			if(!isset($GLOBALS["CLASS_SOCKET"])){$GLOBALS["CLASS_SOCKET"]=new sockets();$sock=$GLOBALS["CLASS_SOCKET"];}else{$sock=$GLOBALS["CLASS_SOCKET"];}
			$EnablePostfixMultiInstance=$sock->GET_INFO("EnablePostfixMultiInstance");
			$AllowArticaMetaAddUsers=$sock->GET_INFO("AllowArticaMetaAddUsers");
			$OU=trim($_GET["ou"]);
			if($OU==null){
				include_once(dirname(__FILE__).'/class.user.inc');
				$us=new user($_SESSION["uid"]);
				$OU=$us->ou;
			}

			if($OU<>null){
				if(strlen($OU)>13){$OU_TEXT=substr($OU,0,10).'...';}else{$OU_TEXT=$OU;}
				
				$OU_encoded=base64_encode($OU);
				$array[$OU_TEXT]["MAIN"]="javascript:Loadjs('domains.manage.org.index.php?js=yes&ou=$OU_encoded&encoded=yes');";
				$array[$OU_TEXT]["SUB"]["{main_parameters}"]="javascript:Loadjs('domains.manage.org.index.php?js=yes&ou=$OU_encoded&encoded=yes&in-front-ajax=yes');";
				
				if($this->AsOrgPostfixAdministrator){
					if($this->POSTFIX_INSTALLED){
						if($EnablePostfixMultiInstance==1){
							$array[$OU_TEXT]["SUB"]["{messaging_servers}"]="javascript:Loadjs('domains.postfix.multi.php?ou=$OU_encoded&encoded=yes&in-front-ajax=yes');";
						}
					}
				}
				
				
				$array[$OU_TEXT]["SUB"]["{domains}"]="javascript:Loadjs('domains.edit.domains.php?js=yes&ou=$OU_encoded&encoded=yes&in-front-ajax=yes');";
				if(!$this->ARTICA_META_ENABLED){
						$array[$OU_TEXT]["SUB"]["{add_user}"]="javascript:Loadjs('domains.add.user.php?ou=$OU_encoded&encoded=yes');";
				}else{
					if($AllowArticaMetaAddUsers==1){
						$array[$OU_TEXT]["SUB"]["{add_user}"]="javascript:Loadjs('domains.add.user.php?ou=$OU_encoded&encoded=yes');";
					}
				}
				
				
				
				
				$array[$OU_TEXT]["SUB"]["{members}"]="javascript:Loadjs('domains.manage.org.index.php?finduser-js=yes&ou=$OU_encoded&encoded=yes&in-front-ajax=yes');";
				$array[$OU_TEXT]["SUB"]["{groups}"]="javascript:Loadjs('domains.edit.group.php?ou=$OU_encoded&js=yes&encoded=yes&in-front-ajax=yes');";
				$array[$OU_TEXT]["SUB"]["{search}"]="javascript:Loadjs('domains.find.user.php?ou=$OU_encoded&encoded=yes');";
			
			}	
		}
			
			
		$menus=$this->BuildArticaMenus($array,$sub_array_pages);
		$this->toplinks=$menus;
		return $menus;			
	
	
	
}


   function BuildLeftMenus_can_manage_org(){
   	if($this->AsOrgAdmin){return true;}
   	if($this->AllowAddUsers){return true;}
   	if($this->AsMessagingOrg){return true;}
   	if($this->AsOrgPostfixAdministrator){return true;}
   	if($this->AseMailCampaignsAdmin){return true;}
   	return false;
   }

	
	
	function BuildLeftMenus(){
		$debugThis=false;
		if($GLOBALS["DEBUG_TEMPLATE"]){$debugThis=true;}
		$array=array();
		$sub_array_pages=array();
		$sock=new sockets();
		$SquidActHasReverse=$sock->GET_INFO("SquidActHasReverse");
		$EnablePostfixMultiInstance=$sock->GET_INFO("EnablePostfixMultiInstance");
		$EnableInterfaceMailCampaigns=$sock->GET_INFO("EnableInterfaceMailCampaigns");
		$InstantIpTablesInLeftMenu=$sock->GET_INFO("InstantIpTablesInLeftMenu");
		$FreeWebLeftMenu=$sock->GET_INFO("FreeWebLeftMenu");
		$EnableCluebringer=$sock->GET_INFO("EnableCluebringer");
		$EnableArticaSMTPStatistics=$sock->GET_INFO("EnableArticaSMTPStatistics");
		$EnablePostfixMultiInstance=$sock->GET_INFO("EnablePostfixMultiInstance");
		$EnablePDNS=$sock->GET_INFO("EnablePDNS");
		$EnableDNSMASQ=$sock->GET_INFO("EnableDNSMASQ");
		$EnableLXCINLeftMenus=$sock->GET_INFO("EnableLXCINLeftMenus");
		if(!is_numeric($EnableArticaSMTPStatistics)){$EnableArticaSMTPStatistics=1;}
		$FreeWebListenPort=$sock->GET_INFO("FreeWebListenPort");
		$FreeWebListenSSLPort=$sock->GET_INFO("FreeWebListenSSLPort");
		if(!is_numeric($FreeWebListenPort)){$FreeWebListenPort=80;}
		if(!is_numeric($FreeWebListenSSLPort)){$FreeWebListenSSLPort=443;}
		if(!is_numeric($EnablePDNS)){$EnablePDNS=1;}
		if(!is_numeric($EnableDNSMASQ)){$EnableDNSMASQ=1;}
		if(!is_numeric($EnablePostfixMultiInstance)){$EnablePostfixMultiInstance=1;}
		if(!is_numeric($EnableLXCINLeftMenus)){$EnableLXCINLeftMenus=1;}
		
		
		
		
		$ASDEBIAN=false;
		if($this->LinuxDistriCode=="DEBIAN"){$ASDEBIAN=true;}
		if($this->LinuxDistriCode=="UBUNTU"){$ASDEBIAN=true;}	
			
	if($GLOBALS["DEBUG_TEMPLATE"]){error_log("DEBUG::{$GLOBALS["CURRENT_PAGE"]}: menus->BuildLeftMenus() function ".__FUNCTION__." in " . __FILE__. " line ".__LINE__);}
		
		if($InstantIpTablesInLeftMenu==null){$InstantIpTablesInLeftMenu=1;}
		if($FreeWebLeftMenu==null){$FreeWebLeftMenu=1;}		
		
		if($debugThis){error_log("{$GLOBALS["CURRENT_PAGE"]} ".__FUNCTION__.' in '.__FILE__. " in line ". __LINE__);}
		if($_SESSION["UsersInterfaceDatas"]<>null){
			if($debugThis){error_log("{$GLOBALS["CURRENT_PAGE"]} UsersInterfaceDatas<>null".__FUNCTION__.' in '.__FILE__. " in line ". __LINE__);}
			return null;
		}
		if(is_array($_SESSION["MainLeftMenus"])){
			$this->main_left_menus=$_SESSION["MainLeftMenus"];
			if($debugThis){error_log("{$GLOBALS["CURRENT_PAGE"]} MainLeftMenus<>null, return".__FUNCTION__.' in '.__FILE__. " in line ". __LINE__);}
			return $_SESSION["MainLeftSubPagesHTML"];
		}
		
		if($debugThis){error_log("{$GLOBALS["CURRENT_PAGE"]} ->LoadModulesEnabled ".__FUNCTION__.' in '.__FILE__. " in line ". __LINE__);}
		$this->LoadModulesEnabled();
		if($debugThis){error_log("{$GLOBALS["CURRENT_PAGE"]} ->LoadModulesEnabled done".__FUNCTION__.' in '.__FILE__. " in line ". __LINE__);}
		if($debugThis){error_log("{$GLOBALS["CURRENT_PAGE"]} ->BuildBackupFeature()".__FUNCTION__.' in '.__FILE__. " in line ". __LINE__);}
		$this->BuildBackupFeature();
		if(isset($GLOBALS["CURRENT_PAGE"])){$page=$GLOBALS["CURRENT_PAGE"];}else{$page=CurrentPageName();}

		if($page=='imap.index.php'){return null;}
		if(!isset($_SESSION["uid"])){return null;}
		if(!isset($_GET["ou"])){$_GET["ou"]=null;}
	
		if($_GET["ou"]==null){
			if($this->AsArticaAdministrator){
				if($_COOKIE["MyFavouriteOrganization"]==null){
					if($debugThis){error_log("{$GLOBALS["CURRENT_PAGE"]} ->this->ldapClass->hash_get_ou(false)".__FUNCTION__.' in '.__FILE__. " in line ". __LINE__);}
					$hash=$this->ldapClass->hash_get_ou(false);
					if($debugThis){error_log("{$GLOBALS["CURRENT_PAGE"]} ->OU={$hash[0]}".__FUNCTION__.' in '.__FILE__. " in line ". __LINE__);}
					$_GET["ou"]=$hash[0];
				}else{
					$_GET["ou"]=$_COOKIE["MyFavouriteOrganization"];
				}
			}else{
				if($debugThis){error_log("{$GLOBALS["CURRENT_PAGE"]} ->this->ORGANISTATION_FROM_USER()".__FUNCTION__.' in '.__FILE__. " in line ". __LINE__);}
				$_GET["ou"]=$this->ORGANISTATION_FROM_USER();
			}
			
		}
		if($debugThis){error_log("{$GLOBALS["CURRENT_PAGE"]} ORG={$_GET["ou"]} done".__FUNCTION__.' in '.__FILE__. " in line ". __LINE__);}
		
		$SMTP_LEVEL=$this->SMTP_LEVEL;
		
		if(!$this->AsAnAdministratorGeneric){
			if($debugThis){error_log("{$GLOBALS["CURRENT_PAGE"]} this->build_user_left_menus()".__FUNCTION__.' in '.__FILE__. " in line ". __LINE__);}
			return $this->build_user_left_menus();
		}		

		if($_SESSION["uid"]==-100){$asSuperAdmin=true;}
		
		if($this->AsArticaAdministrator==true){
				$sub_array_pages["ORG"]["domains.index.php"]='MAIN';
			}
		
		if($this->AsPostfixAdministrator==true){
			if($this->POSTFIX_INSTALLED==true){
				$sub_array_pages["POSTFIX"]["postfix.index.php"]='MAIN';
			}
		}
		
		if($this->AsSambaAdministrator==true){
			if(!$this->KASPERSKY_SMTP_APPLIANCE){
				if($this->SAMBA_INSTALLED){
					$sub_array_pages["{fileshare}"]["fileshares.index.php"]='MAIN';
							
					}
			}
		}		
		
	

			

		
		$array["{index page}"]["MAIN"]="javascript:document.location.href='users.index.php'";
		$array["{index page}"]["SUB"]["{master_panel}"]="javascript:document.location.href='users.index.php';";
		if($this->AsSystemAdministrator){
			$array["{index page}"]["SUB"]["{backup}"]="javascript:Loadjs('backup.tasks.php?in-front-ajax=yes');";

			if($this->APP_AMANDA_INSTALLED){
				$array["{index page}"]["SUB"]["{APP_AMANDA}"]="javascript:Loadjs('amanda.php');";
			}
			
			
			if(!$this->KASPERSKY_WEB_APPLIANCE){
				if($this->BACKUPPC_INSTALLED){
					$array["{index page}"]["SUB"]["{APP_BACKUPPC}"]="javascript:Loadjs('backup-pc.index.php?in-front-ajax=yes');";
				}
			if($this->OCSI_INSTALLED){
				$array["{index page}"]["SUB"]["{APP_OCSI}"]="javascript:Loadjs('ocs.ng.php?in-front-ajax=yes');";
				}
			}
			
			
		}

		
			
		
			$OU=trim($_GET["ou"]);
			if($OU==null){
				include_once(dirname(__FILE__).'/class.user.inc');
				$us=new user($_SESSION["uid"]);
				$OU=$us->ou;
			}
			if($this->AllowAddUsers){writelogs("AllowAddUsers=TRUE && ou=$OU",__FUNCTION__,__FILE__);}else{writelogs("AllowAddUsers=FALSE && ou=$OU",__FUNCTION__,__FILE__);}

			if($this->AllowViewStatistics==true){
				
				$sub_array_pages["STAT"]['statistics.index.php']='MAIN';
				if($this->mailgraph_installed==true){
					$sub_array_pages["STAT"]['statistics.mailgraph.php']='{emails_flow}';
				}
				if($this->queuegraph_installed==true){
					$sub_array_pages["STAT"]['statistics.queuegraph.php']='{queue_flow}';
				}
				if($this->yorel_installed==true){
					$sub_array_pages["STAT"]['statistics.systems.yorel.php']='{system_perfomances}';
					$sub_array_pages["STAT"]['statistics.systems-storage.yorel.php']='{system_perfomances_datas}';
				}
				
				
			}

			
		if($this->AsArticaAdministrator==true){
			$array["{organizations}"]["MAIN"]="javascript:Loadjs('domains.index.php?js=yes')";
			$array["{organizations}"]["SUB"]["{main_parameters}"]="javascript:Loadjs('domains.index.php?js=yes')";
			
			if($GLOBALS["VERBOSE"]){echo "<li>Starting this->ldapClass->hash_get_ou(true)</li>";}
			$ous=$this->ldapClass->hash_get_ou(true);
			if(is_array($ous)){
				while (list ($num, $ligne) = each ($ous) ){
					$OU_encoded=base64_encode($OU);
					$array["{organizations}"]["SUB"][$num]="javascript:Loadjs('domains.manage.org.index.php?js=yes&ou=$num&in-front-ajax=yes');";
				}
			}
			if(!$this->ARTICA_META_ENABLED){
				$array["{organizations}"]["SUB"]["{add}"]="javascript:TreeAddNewOrganisation();";
			}else{
				if($sock->GET_INFO("AllowArticaMetaAddUsers")==1){
					$array["{organizations}"]["SUB"]["{add}"]="javascript:TreeAddNewOrganisation();";
				}
			}
			
		
		
		}
		
		
		if($this->BuildLeftMenus_can_manage_org()){
			if($OU<>null){
				if(strlen($OU)>13){$OU_TEXT=substr($OU,0,10).'...';}else{$OU_TEXT=$OU;}
				$OU_encoded=base64_encode($OU);
				$array[$OU_TEXT]["MAIN"]="javascript:Loadjs('domains.manage.org.index.php?js=yes&ou=$OU_encoded&encoded=yes');";
				$array[$OU_TEXT]["SUB"]["{main_parameters}"]="javascript:Loadjs('domains.manage.org.index.php?js=yes&ou=$OU_encoded&encoded=yes&in-front-ajax=yes');";
				
				if($this->AsOrgPostfixAdministrator){
					if($this->POSTFIX_INSTALLED){
						if($EnablePostfixMultiInstance==1){
							$array[$OU_TEXT]["SUB"]["{smtp_relays}"]="javascript:Loadjs('domains.postfix.multi.php?ou=$OU_encoded&encoded=yes&in-front-ajax=yes');";
						}
					}
				}
								
				$array[$OU_TEXT]["SUB"]["{domains}"]="javascript:Loadjs('domains.edit.domains.php?js=yes&ou=$OU_encoded&encoded=yes&in-front-ajax=yes');";

				if($EnableInterfaceMailCampaigns==1){
					if($this->EMAILRELAY_INSTALLED){
					if($this->AseMailCampaignsAdmin){
					$array[$OU_TEXT]["SUB"]["{email_campaigns}"]="javascript:Loadjs('domains.emailings.php?popup-js=yes&ou=$OU_encoded');";
				}}}				
				
				
				if(!$this->EnableManageUsersTroughActiveDirectory){
					if(!$this->ARTICA_META_ENABLED){
							$array[$OU_TEXT]["SUB"]["{add_user}"]="javascript:Loadjs('domains.add.user.php?ou=$OU_encoded&encoded=yes');";
					}else{
						if($sock->GET_INFO("AllowArticaMetaAddUsers")==1){
							$array[$OU_TEXT]["SUB"]["{add_user}"]="javascript:Loadjs('domains.add.user.php?ou=$OU_encoded&encoded=yes');";
						}
					}
				}
				
				
				$array[$OU_TEXT]["SUB"]["{members}"]="javascript:Loadjs('domains.manage.org.index.php?finduser-js=yes&ou=$OU_encoded&encoded=yes&in-front-ajax=yes');";
				$array[$OU_TEXT]["SUB"]["{groups}"]="javascript:Loadjs('domains.edit.group.php?ou=$OU_encoded&js=yes&encoded=yes&in-front-ajax=yes');";
				$array[$OU_TEXT]["SUB"]["{search}"]="javascript:Loadjs('domains.find.user.php?ou=$OU_encoded&encoded=yes');";
				if(!$this->EnableManageUsersTroughActiveDirectory){
					$array[$OU_TEXT]["SUB"]["{groupwares}"]="javascript:Loadjs('domains.manage.org.index.php?ou=$OU_encoded&encoded=yes&groupwares-in-front-ajax=yes');";
				}
				

			}
		}
		
		if($this->AsArticaAdministrator==true){
			$array["system"]["MAIN"]="javascript:Loadjs('system.index.php?js=yes')";
			$array["system"]["SUB"]["{main_parameters}"]="javascript:Loadjs('system.index.php?js=yes')";
			if(!$this->AS_VPS_CLIENT){
				$array["system"]["SUB"]["{left_menu_internal_hard_drives}"]="javascript:Loadjs('system.internal.disks.php?in-front-ajax=yes')";
			}
			
			if($this->autofs_installed){
				$array["system"]["SUB"]["{automount}"]="javascript:Loadjs('autofs.php')";
			}
			
			$array["system"]["SUB"]["{left_menu_net_settings}"]="javascript:Loadjs('system.nic.config.php?js=yes&in-front-ajax=yes')";
			$array["system"]["SUB"]["{mysql_settings}"]="javascript:Loadjs('system.mysql.php')";
			
			
			
			if($sock->GET_INFO("WifiCardOk")==1){$array["system"]["SUB"]["WIFI"]="javascript:Loadjs('system.wifi.php')";}
			if($this->dhcp_installed){
				if(!$this->KASPERSKY_WEB_APPLIANCE){
					$array["system"]["SUB"]["{APP_DHCP}"]="javascript:Loadjs('index.gateway.php?index_dhcp=yes&in-front-ajax=yes')";
				}
			}
			
			if($sock->GET_INFO("QOSInLeftMenu")==1){
				$array["system"]["SUB"]["{Q.O.S}"]="javascript:Loadjs('qos.php?in-front-ajax=yes')";
				
			}
			

			
			if($this->PPTPD_INSTALLED){
				$array["system"]["SUB"]["VPN PPTP"]="javascript:Loadjs('pptpd.php?infront=yes')";
			}
			
			if($this->PPTP_INSTALLED){
				$array["system"]["SUB"]["VPN PPTP"]="javascript:Loadjs('pptpd.php?infront=yes')";
			}	

			
			if($this->IPSEC_INSTALLED){
				$array["system"]["SUB"]["VPN IPSEC"]="javascript:Loadjs('ipsec.php?infront=yes')";
				
			}
			
			if($this->AsDnsAdministrator){
				if($this->POWER_DNS_INSTALLED){$array["system"]["SUB"]["{APP_PDNS}"]="javascript:Loadjs('pdns.php?in-front-ajax=yes')";}
				if($this->dnsmasq_installed){$array["system"]["SUB"]["DNSMasq"]="javascript:Loadjs('dnsmasq.index.php')";}
				
			}
			
			if($this->APACHE_INSTALLED){
					if($FreeWebLeftMenu==1){
						$array["system"]["SUB"]["FreeWebs"]="javascript:Loadjs('freeweb.php?in-front-ajax=yes')";
					}
				}
			
			
			if($this->crossroads_installed){
					$array["system"]["SUB"]["{load_balancing}"]="javascript:Loadjs('crossroads.index.php')";
				}

			if($this->IPTABLES_ACCOUNTING_EXISTS){
				$array["system"]["SUB"]["{APP_IPTACCOUNT}"]="javascript:Loadjs('iptaccount.php')";
			}

			if($this->APT_MIRROR_INSTALLED){
				if($sock->GET_INFO("EnableAptMirror")==1){
					$array["system"]["SUB"]["{REPOSITORY_DEB_MIRROR}"]="javascript:Loadjs('apt-mirror.php?in-front-ajax=yes')";
				}
			}
			
			
			$array["system"]["SUB"]["{APP_OPENSSH}"]="javascript:Loadjs('sshd.php?in-front-ajax=yes')";
			
			if($this->APP_SABNZBDPLUS_INSTALLED){
				$array["system"]["SUB"]["{APP_SABNZBDPLUS}"]="javascript:Loadjs('sabnzbdplus.php?in-front-ajax=yes')";
				
			}
			
			if($this->OPENVPN_INSTALLED){
				$array["{APP_OPENVPN}"]["MAIN"]="javascript:Loadjs('index.openvpn.php?infront=yes')";
				$array["{APP_OPENVPN}"]["SUB"]["{main_parameters}"]="javascript:Loadjs('index.openvpn.php?infront=yes');";
				$array["{APP_OPENVPN}"]["SUB"]["{server}"]="javascript:Loadjs('index.openvpn.php?server-settings-js=yes')";
				$array["{APP_OPENVPN}"]["SUB"]["{client}"]="javascript:Loadjs('openvpn.servers-connect.php?inline-js=yes')";
				
				$array["{APP_OPENVPN}"]["SUB"]["{sessions}"]="javascript:Loadjs('index.openvpn.php?session-js=yes')";
				$array["{APP_OPENVPN}"]["SUB"]["{REMOTE_SITES_VPN}"]="javascript:Loadjs('openvpn.remotesites.php?js-inline=yes')";
				$array["{APP_OPENVPN}"]["SUB"]["{events}"]="javascript:Loadjs('index.openvpn.php?events-js=yes')";
				
				
			}			
			
			
		}
		
		
		if($this->AsVirtualBoxManager){
			if($this->VIRTUALBOX_INSTALLED){
				$array["{virtual_machines}"]["MAIN"]="javascript:Loadjs('virtualbox.php')";
				$array["{virtual_machines}"]["SUB"]["{main_parameters}"]="javascript:Loadjs('virtualbox.php');";
				$array["{virtual_machines}"]["SUB"]["{virtualbox_manager}"]="javascript:s_PopUp('virtualbox/index.html','1024','768')";
			}
			
			if($this->THINSTATION_INSTALLED){
				$array["{virtual_machines}"]["MAIN"]="javascript:Loadjs('virtualbox.php')";
				$array["{virtual_machines}"]["SUB"]["{thinclients}"]="javascript:Loadjs('virtualbox.php?by-thinclients=yes');";
			}
			
			
			if($this->LXC_INSTALLED){
				if($EnableLXCINLeftMenus==1){
					$array["{virtual_machines}"]["MAIN"]="javascript:Loadjs('virtualbox.php')";
					$array["{virtual_machines}"]["SUB"]["{VPS_SERVERS}"]="javascript:Loadjs('lxc.index.php')";
				}
			}
			
		}
		
		
		if($this->AsSambaAdministrator){
			if($this->APP_AUDITD_INSTALLED){
				$EnableAuditd=$sock->GET_INFO("EnableAuditd");
				if($EnableAuditd==null){$EnableAuditd=1;}
				if($EnableAuditd==1){
					$array["system"]["SUB"]["{APP_AUDITD}"]="javascript:Loadjs('auditd.php?in-front-ajax=yes')";
				}
			}
		}
			

			
	if(!$this->OPENVPN_APPLIANCE){
			if($this->AsSambaAdministrator){
				if($this->SAMBA_INSTALLED){
					$array["{fileshare}"]["MAIN"]="javascript:Loadjs('fileshares.index.php?js=yes')";
					$array["{fileshare}"]["SUB"]["{main_parameters}"]="javascript:Loadjs('fileshares.index.php?js=yes');";
					$array["{fileshare}"]["SUB"]["{wins_server}"]="javascript:Loadjs('samba.wins.php');";
					$array["{fileshare}"]["SUB"]["{shared_folders}"]="javascript:Loadjs('samba.index.php?script=yes&js-shared_folders=yes');";
					$array["{fileshare}"]["SUB"]["{virtual_servers}"]="javascript:Loadjs('samba.virtual-servers.php?js-inline=yes');";
					$array["{fileshare}"]["SUB"]["SSLBridge"]="javascript:s_PopUpFull('sslbridge/index.php',900,900,'SSLBridge For Artica')";
					$array["{fileshare}"]["SUB"]["{explorer}"]="javascript:Loadjs('tree.php');";
					$array["{fileshare}"]["SUB"]["{SHARE_FOLDER}"]="javascript:Loadjs('SambaBrowse.php?in-front-ajax=yes');";
					if($this->CUPS_INSTALLED){
						$array["{fileshare}"]["SUB"]["{printers}"]="javascript:Loadjs('cups.index.php?in-front-ajax=yes');";
					}
					$array["{fileshare}"]["SUB"]["{computers}"]="javascript:Loadjs('computer-browse.php?in-front-ajax=yes');";
					}
			}
			
			
			if($this->AsSystemAdministrator){
				if($this->PUREFTP_INSTALLED){
					$array["{fileshare}"]["MAIN"]="javascript:Loadjs('fileshares.index.php?js=yes')";
					$array["{fileshare}"]["SUB"]["{main_parameters}"]="javascript:Loadjs('fileshares.index.php?js=yes');";
				}
			}
				
			if($this->AsSystemAdministrator){
				if($this->RSYNC_INSTALLED){
					$array["{fileshare}"]["MAIN"]="javascript:Loadjs('fileshares.index.php?js=yes')";
					$array["{fileshare}"]["SUB"]["{main_parameters}"]="javascript:Loadjs('fileshares.index.php?js=yes');";
					$array["{fileshare}"]["SUB"]["{explorer}"]="javascript:Loadjs('tree.php');";
				}	
			}
			
			
			if($this->AsSystemAdministrator){
				if($this->MLDONKEY_INSTALLED){
					$array["{fileshare}"]["MAIN"]="javascript:Loadjs('fileshares.index.php?js=yes')";
					$array["{fileshare}"]["SUB"]["{APP_MLDONKEY}"]="javascript:Loadjs('mldonkey.php?in-front-ajax=yes');";
					$array["{fileshare}"]["SUB"]["{explorer}"]="javascript:Loadjs('tree.php');";
				}	
			}
	
			if($this->AsSystemAdministrator){
				if($this->GLUSTER_INSTALLED){
					$array["{fileshare}"]["MAIN"]="javascript:Loadjs('fileshares.index.php?js=yes')";
					$array["{fileshare}"]["SUB"]["{APP_CLUSTER}"]="javascript:Loadjs('gluster.samba.php?in-front-ajax=yes');";
					$array["{fileshare}"]["SUB"]["{explorer}"]="javascript:Loadjs('tree.php');";
				}	
			}
	}
		
		
		
		
		if($this->SQUID_INSTALLED==true){if($this->AsSquidAdministrator==true){
			$sock=new sockets();
			$SQUIDEnable=trim($sock->GET_INFO("SQUIDEnable"));
			if($SQUIDEnable==null){$SQUIDEnable=1;}
			if($SQUIDEnable==1){
					$array["{web_proxy}"]["MAIN"]="javascript:Loadjs('squid.newbee.php?yes')";
					$array["{web_proxy}"]["SUB"]["{main_parameters}"]="javascript:Loadjs('squid.newbee.php?yes')";
					$array["{web_proxy}"]["SUB"]["{add_websites}"]="javascript:Loadjs('squid.visited.php?add-www=yes')";
					$array["{web_proxy}"]["SUB"]["{visited_websites}"]="javascript:Loadjs('squid.visited.php')";
					
					
					
					if($SquidActHasReverse<>1){
						if($this->DANSGUARDIAN_INSTALLED){
							$array["{web_proxy}"]["SUB"]["{DANSGUARDIAN_RULES}"]="javascript:Loadjs('dansguardian.index.php?js=yes')";
		
						}
					}
					
					if($SquidActHasReverse<>1){
						if($this->SQUIDGUARD_INSTALLED){
							$array["{web_proxy}"]["SUB"]["{DANSGUARDIAN_RULES}"]="javascript:Loadjs('dansguardian.index.php?js=yes')";
						}
					}
					
					
					if($SquidActHasReverse<>1){
						if($this->APP_UFDBGUARD_INSTALLED){
							$array["{web_proxy}"]["SUB"]["{DANSGUARDIAN_RULES}"]="javascript:Loadjs('dansguardian.index.php?js=yes')";
						}
					}					

					if($SquidActHasReverse<>1){
						if($this->C_ICAP_INSTALLED){
							$array["{web_proxy}"]["SUB"]["{DANSGUARDIAN_RULES}"]="javascript:Loadjs('dansguardian.index.php?js=yes')";
						}
					}
		
					
					
					$array["{web_proxy}"]["SUB"]["{bandwith_limitation}"]="javascript:Loadjs('squid.bandwith.php?js=yes')";
					$cachemgr_left_menu=$sock->GET_INFO("cachemgr_left_menu");
					
					if($cachemgr_left_menu==1){
						$sql="SELECT servername,useSSL FROM freeweb WHERE `groupware`='cachemgr' LIMIT 0,1";
						$q=new mysql();
						$prefix="http";
						$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
						if($ligne["useSSL"]==1){$FreeWebListenPort=$FreeWebListenSSLPort;$prefix="https";}
						$array["{web_proxy}"]["SUB"]["{cachemgr}"]="javascript:s_PopUpFull('$prefix://{$ligne["servername"]}:$FreeWebListenPort',800,800,'Squid Cache Manager')";
						
					}
					$array["{web_proxy}"]["SUB"]["{statistics}"]="javascript:s_PopUpFull('squid.logon.php',900,900,'Proxy statistics')";
					if($this->SARG_INSTALLED){
						$array["{web_proxy}"]["SUB"]["SARG stats."]="javascript:s_PopUpFull('squid/index.html',900,900,'SARG')";
					}
					
				}
			}
		}
		
		if($this->POSTFIX_INSTALLED){
			if($this->AsPostfixAdministrator==true){
				$array["{messaging}"]["MAIN"]="javascript:Loadjs('postfix.index.php')";
				$array["{messaging}"]["SUB"]["{main_parameters}"]="javascript:Loadjs('postfix.index.php')";
				
				if($EnablePostfixMultiInstance==1){
					$array["{messaging}"]["SUB"]["{multiple_instances}"]="javascript:Loadjs('postfix.multiples.php')";
				}
				
				$array["{messaging}"]["SUB"]["{queue_management}"]="javascript:Loadjs('postfix.queue.monitoring.php?inline-js=yes')";
				
				
				
				$array["{messaging}"]["SUB"]["{security}"]="javascript:Loadjs('postfix.security.php')";
				$array["{messaging}"]["SUB"]["{APP_POSTFWD2}"]="javascript:Loadjs('postfwd2.php?instance=master')";
				
				
				
				$array["{messaging}"]["SUB"]["{postfix_finder}"]="javascript:Loadjs('postfix.finder.php?js=yes')";
				$array["{messaging}"]["SUB"]["{search}"]="javascript:Loadjs('postfix.search.php')";
				if($InstantIpTablesInLeftMenu==1){
					$array["{messaging}"]["SUB"]["Instant Iptables"]="javascript:Loadjs('postfix.iptables.php?in-front-ajax=yes')";
				}
				
				if($this->CLUEBRINGER_INSTALLED){
					if($EnableCluebringer==1){
						$array["{messaging}"]["SUB"]["Policyd Adm."]="javascript:s_PopUp('cluebringer/index.php','800','800')";
					}
				}
				
				$array=$this->MAILBOXES_MENUS($array);
				
				
				
				
				  
			  if($this->fetchmail_installed){
			  	if($EnablePostfixMultiInstance<>1){
			  		$array["{messaging}"]["SUB"]["fetchmail"]="javascript:Loadjs('fetchmail.index.php?ajax=yes&in-front-ajax=yes')";
			  	}
			  }
				
				if($this->AMAVIS_INSTALLED){
					if($sock->GET_INFO("EnableAmavisDaemon")==1){
						$array["{messaging}"]["SUB"]["{APP_AMAVIS}"]="javascript:Loadjs('amavis.index.php?ajax=yes&in-front-ajax=yes')";
					}
				}
				
				if($this->AsPostfixAdministrator){	
					$array["{messaging}"]["SUB"]["{white list}"]="javascript:Loadjs('whitelists.admin.php?js=yes&js-in-line=yes')";
				}
			
				
				if($this->MILTERGREYLIST_INSTALLED){
					$array["{messaging}"]["SUB"]["{APP_MILTERGREYLIST}"]="javascript:Loadjs('milter.greylist.index.php?js=yes&in-front-ajax=yes')";
				}
				
				
				
				
			}
		
		}
		
							 

//		if($this->AsPostfixAdministrator==true && $this->dnsmasq_installed==true && $this->BIND9_INSTALLED==false){$array["dnsmasq"]="dnsmasq.index.php";}
		if($this->POSTFIX_INSTALLED){if($this->AsPostfixAdministrator==true && $this->mailgraph_installed==true){$array["Mailman"]="mailman.index.php";}}
		if($this->AllowViewStatistics==true){
			$array["{statistics}"]["MAIN"]="javascript:Loadjs('statistics.index.php')";
			$array["{statistics}"]["SUB"]["{master_panel}"]="javascript:Loadjs('statistics.index.php')";
			
			if($this->POSTFIX_INSTALLED){
				if($this->AsPostfixAdministrator){
					if($EnableArticaSMTPStatistics==1){
						$array["{statistics}"]["SUB"]["SMTP (con.)"]="javascript:Loadjs('postfix.stats.cnx.php')";
					}
					
				}
			}
			

			if($this->SQUID_INSTALLED){
				$array["{statistics}"]["SUB"]["{web_proxy}"]="javascript:Loadjs('squid.statistics.php')";
				if($this->SARG_INSTALLED){
						$array["{statistics}"]["SUB"]["{web_proxy}:SARG"]="javascript:s_PopUpFull('squid/index.html',900,900,'SARG')";
					}
			}
			
			if($this->APP_VNSTAT_INSTALLED){
				$array["{statistics}"]["SUB"]["{network_stats}"]="javascript:Loadjs('statistics.vnstat.php')";
			}
		
		}	
		
		
// ------------------------ EVENTS	------------------------	

		if($this->AsAnAdministratorGeneric){
			$array["{events}"]["MAIN"]="";
			
			if($this->AsSystemAdministrator){
				$array["{events}"]["SUB"]["{syslog}"]="javascript:Loadjs('syslog.php')";
				$array["{events}"]["SUB"]["{APP_OPENSSH}"]="javascript:Loadjs('sshd.php?events-js=yes')";
				if($this->SNORT_INSTALLED){$array["{events}"]["SUB"]["I.D.S"]="javascript:Loadjs('system.nic.snort.php?events-js=yes')";}
				if($this->OPENVPN_INSTALLED){$array["{events}"]["SUB"]["{APP_OPENVPN}"]="javascript:Loadjs('index.openvpn.php?events-js=yes')";}
				$array["{events}"]["SUB"]["{artica_events}"]="javascript:Loadjs('artica.events.php?in-front-ajax=yes')";
				
				
			}
			
			if($this->POSTFIX_INSTALLED){
				$array["{events}"]["SUB"]["{messaging}"]="javascript:s_PopUp('postfix.events.php?pop=true',450,400)";
			}
			if($this->fetchmail_installed){
				$array["{events}"]["SUB"]["fetchmail"]="javascript:Loadjs('fetchmail.events.php');";
			}
			
			if($this->SAMBA_INSTALLED){
			if($this->AsSambaAdministrator){
				$array["{events}"]["SUB"]["samba watch"]="javascript:Loadjs('samba.watch.php?ajax=yes&in-line=yes');";
				$array["{events}"]["SUB"]["samba"]="javascript:Loadjs('samba.events.php?ajax=yes&in-line=yes');";
				
				}
			}
			
		}
		
	if($this->AsSambaAdministrator){
			if($this->APP_AUDITD_INSTALLED){
				$EnableAuditd=$sock->GET_INFO("EnableAuditd");
				if($EnableAuditd==null){$EnableAuditd=1;}
				if($EnableAuditd==1){
					$array["{events}"]["SUB"]["{APP_AUDITD}"]="javascript:Loadjs('auditd.php?in-front-ajax=yes&js-folder=yes')";
				}
			}
		}		
		
		if($this->KASPERSKY_SMTP_APPLIANCE){
			unset($array["{fileshare}"]);
			unset($array["system"]["SUB"]["{APP_DHCP}"]);
		
		}
		
		if($this->KASPERSKY_WEB_APPLIANCE){unset($array["{fileshare}"]);}
		
		$menus=$this->BuildArticaMenus($array,$sub_array_pages);
		$this->toplinks=$menus;
		return $menus;

		$this->toplinks="<div id='TpLink'>
		<table style='width:100%'>";
		
		$html=$html."\n\t<li class='top'><a href='#'> </a></li>\n";
		if(is_array($array)){
		while (list ($num, $ligne) = each ($array) ){
			
			if($page==$ligne){$class="id='selected'";}else{if($ligne=="#"){$class="id=subtitle";}else{$class=null;}}
				if(strpos($ligne,'ascript')>0){
					$html=$html . "\t\t<li $class><a href='#'>$num&nbsp;&raquo;</a></li>\n";
				}else{
					$html=$html . "\t\t<li $class><a href='$ligne'>$num&nbsp;&raquo;</a></li>\n";
				}
				
			
				
				$toplinks=$this->BuildLefetMenusSubmenu($sub_array_pages,$ligne,false);
				if($toplinks<>null){
						$this->toplinks=$this->toplinks.$toplinks;
						$count_toplinks=$count_toplinks+1;
				}
				
				$BuildLefetMenusSubmenu=$this->BuildLefetMenusSubmenu($sub_array_pages,$ligne);
				
				$html=$html . $BuildLefetMenusSubmenu;
			}
		}
		$html=$html . "<li class='bottom'><a href='#'> </a></li>";
		$this->toplinks=$this->toplinks. "</table></div>";
		if($count_toplinks==0){$this->toplinks=null;}
		$_SESSION["MainLeftMenus"]=$this->main_left_menus;
		$_SESSION["MainLeftSubPagesHTML"]=$html;
		return $html;
		
	}
	
	
private function MAILBOXES_MENUS($array=array()){
	
  if($this->ZARAFA_APPLIANCE){
			  	$array["{messaging}"]["SUB"]["{APP_ZARAFA}"]="javascript:Loadjs('zarafa.index.php')";
			  	return $array;
			  }
			  
   if($this->ZARAFA_INSTALLED){		  
			$array["{messaging}"]["SUB"]["{mailboxes}"]="javascript:Loadjs('zarafa.index.php')";
			return $array;
   	
   }
	
   if($this->cyrus_imapd_installed){
		$array["{messaging}"]["SUB"]["{mailboxes}"]="javascript:Loadjs('cyrus.index.php?in-front-ajax=yes')";
		if($this->roundcube_installed){
			$array["{messaging}"]["SUB"]["{APP_WEBMAIL}"]="javascript:Loadjs('roundcube.index.php?script=yes&in-front-ajax=yes')";
		}
	}
	return $array;		
}
	
	function BuildArticaMenus($master_array,$subarray){
		$tpl=new templates();
		$html[]="
		<div>
		<ul id=\"navigation\">
		
		
		";
		if(is_array($master_array)){
		while (list ($num, $ligne) = each ($master_array) ){
			if(is_array($ligne)){
				$html[]="<li><a class=\"head\" href=\"{$ligne["MAIN"]}\">". $tpl->_ENGINE_parse_body($num)."</a>";
				if(is_array($ligne["SUB"])){
					$html[]="<ul>";
					while (list ($submenu_title, $js) = each ($ligne["SUB"]) ){
						$html[]="<li><a href=\"$js\">". $tpl->_ENGINE_parse_body($submenu_title)."</a></li>";
					}
					$html[]="</ul>";
				}
				$html[]="</li>";
				continue;
			}
			$html[]="<li><a class=\"head\" href=\"$ligne\">$num</a></li>";
			//$html[]="<ul><li> <a href=\"$ligne\">$num</a></li></ul>";
			
		}}
		
		$html[]="</ul></div>
		<script>
		jQuery().ready(function(){	
			jQuery('#navigation').accordion({
			active: false,
			header: '.head',
			navigation: true,
			event: 'click',
			fillSpace: true,
			clearStyle: true,
			animated: 'bounceslide'});
		});
		</script>";
		return implode("\n",$html);
		
	 //animated: 'bounceslide'
	 // animated: 'easeslide'  
	}
	
	
	function BuildLefetMenusSubmenu($array,$page,$normal=true){
		
		$key=$this->BuildLefetMenusFoundSubmenu($array);
		if($array[$key][$page]==null){return null;}
		$page=CurrentPageName();
		if($key<>null){
			while (list ($num, $ligne) = each ($array[$key] )){
				if(strpos($num,'?')>0){$link=substr($num,0,strpos($num,'?'));}else{$link=$num;}
				
				
				if($page==$link){$class="id='selected'";}else{if($num=="#"){$class="id=subtitle";}else{$class=null;}}
				if($ligne<>"MAIN"){
					if($normal==true){$html=$html . "\t\t<li $class><a href='$num' style='font-size:10px'>$ligne&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a></li>\n";}
					else {$html=$html . "\t\t<tr><td width=1%><img src='img/arrow.gif'></td><td class='ten'><a href='$num' >$ligne</a></td></tr>\n";}
				}
			}
			}
		return $html;}
		
		
		function BuildBackupFeature(){
			if(isset($_SESSION["backup_feature"])){return null;}
			$this->LoadModulesEnabled();
			writelogs("backup_feature:: MailArchiverEnabled=$this->MailArchiverEnabled",__FUNCTION__,__FILE__);
			if($this->MailArchiverEnabled==1){
						$array["users.backup.php"]="{backup}";
						$_SESSION["backup_feature"]=true;
					
				}else{					
				
				writelogs("backup_feature:: MIMEDEFANG_INSTALLED=$this->MIMEDEFANG_INSTALLED",__FUNCTION__,__FILE__);
				if($this->MIMEDEFANG_INSTALLED){
					include_once(dirname(__FILE__).'/class.mimedefang.inc');
					$mime=new mimedefang();
					if($mime->ScriptConf_array["BUILD"]["BACKUP_ENABLED"]==1){
						include_once(dirname(__FILE__).'/class.backup.emails.inc');
						$back=new backup_email($_SESSION["ou"]);
						if($back->BackupEnabled==1){
							$array["users.backup.php"]="{backup}";
							$_SESSION["backup_feature"]=true;
							}
						}
					}	
				}
				writelogs("backup_feature:: AMAVIS_INSTALLED=$this->AMAVIS_INSTALLED",__FUNCTION__,__FILE__);
				writelogs("backup_feature:: EnableAmavisDaemon=$this->EnableAmavisDaemon",__FUNCTION__,__FILE__);
				writelogs("backup_feature:: EnableAmavisBackup=$this->EnableAmavisBackup",__FUNCTION__,__FILE__);
				if($this->AMAVIS_INSTALLED){
					if($this->EnableAmavisDaemon==1){
						if($this->EnableAmavisBackup==1){
							$array["users.backup.php"]="{backup}";
							$_SESSION["backup_feature"]=true;
						}
					}
				}
				
			
			
			
		}
		
		
		function BuildUsersTabs(){
			if($GLOBALS["DEBUG_TEMPLATE"]){error_log("DEBUG::{$GLOBALS["CURRENT_PAGE"]}:BuildUsersTabs() start; function ".__FUNCTION__." in " . __FILE__. " line ".__LINE__);} 
			if(!isset($_SESSION["uid"])){
				if($GLOBALS["DEBUG_TEMPLATE"]){error_log("DEBUG::{$GLOBALS["CURRENT_PAGE"]}:SESSION['uid'] is not set,aborting; function ".__FUNCTION__." in " . __FILE__. " line ".__LINE__);}
				return false;
			}
			if($_SESSION['UsersInterfaceDatas']<>null){
				if($GLOBALS["DEBUG_TEMPLATE"]){error_log("DEBUG::{$GLOBALS["CURRENT_PAGE"]}:SESSION['UsersInterfaceDatas']<>null aborting; function ".__FUNCTION__." in " . __FILE__. " line ".__LINE__);}
				return false;
			}
			
			if($GLOBALS["DEBUG_TEMPLATE"]){error_log("DEBUG::{$GLOBALS["CURRENT_PAGE"]}:this->LoadModulesEnabled(); function ".__FUNCTION__." in " . __FILE__. " line ".__LINE__);}
			$this->LoadModulesEnabled();
			if($this->IfIsAnuser()==false){return false;}
			include_once(dirname(__FILE__).'/class.user.inc');
			
			if($_SESSION["TEMPLATE_USER_ADDR"]==null){
					$user=new user($_SESSION["uid"]);
					$_SESSION["TEMPLATE_USER_ADDR"]="<strong>$user->givenName $user->sn</strong><br />
					$user->postalAddress $user->street $user->postalCode<br />
					$user->town<br />
					Phone: $user->telephoneNumber<br />
					Mobile: $user->mobile<br />
					$user->mail";
			}
			
			$this->template_user_addr=$_SESSION["TEMPLATE_USER_ADDR"];
				
			if($_SESSION["ou"]==null){
				$user=new user($_SESSION["uid"]);
				$_SESSION["ou"]=$user->ou;
			}
			
			
		if($this->cyrus_imapd_installed==true){
				if($this->roundcube_installed==true){
					include_once(dirname(__FILE__).'/class.roundcube.inc');
					$r=new roundcube();
					$array[$r->roundCubeArray["user_link"]]='{webmail}';	
				}
			}	
						

			
			
			if(!isset($_SESSION["quarantine_feature"])){
				if($this->MIMEDEFANG_INSTALLED){
					include_once(dirname(__FILE__).'/class.mimedefang.inc');
					$mime=new mimedefang();
					if($mime->ScriptConf_array["BUILD"]["QUARANTINE_SPAM"]==1){
							$array["users.quarantine.php"]="{quarantine}";
							$_SESSION["quarantine_feature"]=true;
						}
					}
			}
						
//----------------------------------------- OBM -------------------------------------------------------			
			if($this->OBM_INSTALLED){
					if($this->OBMEnabled){
					include_once(dirname(__FILE__).'/class.obm.inc');
							$obm=new obm();
							if($obm->external_url==null){$obm->external_url=$_SERVER['SERVER_NAME'];}
							$array["https://$obm->external_url:$obm->apache_listen"]="{APP_OBM}";
						}else{
							writelogs("OBM is not enabled, skip",__CLASS__.'/'.__FUNCTION__,__FILE__);
						}
					}else{
						writelogs("OBM is not installed, skip",__CLASS__.'/'.__FUNCTION__,__FILE__);
					}
	
//-----------------------------------------//-----------------------------------------//-----------------					
			
			if($_SESSION["backup_feature"]){$array["users.backup.php"]="{backup}";}
			if($_SESSION["quarantine_feature"]){$array["users.quarantine.php"]="{quarantine}";}


			
			/*if($this->kas_installed==true){
			if($this->AllowChangeAntiSpamSettings==true){
				$array["kas.user.rules.php"]="Anti-spam";
			}}*/
			
			if($this->AllowFetchMails){
				if($this->fetchmail_installed){
					$array["javascript:Loadjs('wizard.fetchmail.newbee.php?script=yes&uid={$_SESSION["uid"]}')"]="{fetchmail}";
				}
				
			}
			
			if($this->cyrus_imapd_installed){
				if($this->AllowChangeMailBoxRules){
					$password=$_SESSION["passwd"];
					$password=md5($password);
					$array["/smartsieve/login.php?uid={$_SESSION["uid"]}&_session=$password"]="{smartsieve}";
					}
			}
			
			if(is_array($array)){
			while (list ($num, $ligne) = each ($array)){
				$page=CurrentPageName();
				if($page==$num){
					$li=$li . "<a href='$num'><span style='color:yellow'>$ligne</span></a>\n";
					$this->template_users_menus_h=$this->template_users_menus_h."<a href=\"$num\">$ligne</a>&nbsp;|&nbsp;";
				}else{
					$li=$li ."<a href='$num'><span>$ligne</span></a>\n";
					$this->template_users_menus_h=$this->template_users_menus_h."<a href=\"$num\">$ligne</a>&nbsp;|&nbsp;";
				}
				
			}}
			
			
			return $li;
			
		}
		
	
	
	function BuildLefetMenusFoundSubmenu($array){
		$page=CurrentPageName();
		while (list ($num, $ligne) = each ($array) ){
			while (list ($key, $val) = each ($ligne) ){	
				if(strpos($key,'?')>0){$key=substr($key,0,strpos($key,'?'));}
				if($key==$page){
					return $num;
				}
			}
		}
	}
	
	function BuildLefetMenusFoundSubmenubyPage($array,$page){
		while (list ($num, $ligne) = each ($array) ){
			if(is_array($ligne)){
				while (list ($key, $val) = each ($ligne) ){	
					if(strpos($key,'?')>0){$key=substr($key,0,strpos($key,'?'));}
					if($key==$page){
						return $num;
						}
					}
			}
		}
	}	
	
	function _TranslateRights($priv,$log=false){
		if(!isset($GLOBALS["posix_getuid"])){$GLOBALS["posix_getuid"]=1000;if(function_exists("posix_getuid")){$GLOBALS["posix_getuid"]=posix_getuid();}}
		if(!is_array($priv)){
			if($GLOBALS["posix_getuid"]<>0){writelogs("No privileges set",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
			return;
		}
		if($GLOBALS["posix_getuid"]==0){
			$this->AsMailBoxAdministrator=true;
			$this->AsArticaAdministrator=true;
		}
		$this->AsArticaAdministrator=false;
		$this->AsSystemAdministrator=false;
		
		if($log){writelogs("receive array of ". count($priv)." privileges",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		
		if(!isset($priv["AsVirtualBoxManager"])){$priv["AsVirtualBoxManager"]="no";}
		if(!isset($priv["AsSquidAdministrator"])){$priv["AsSquidAdministrator"]="no";}
		if(!isset($priv["AsJoomlaWebMaster"])){$priv["AsJoomlaWebMaster"]="no";}
		if(!isset($priv["AseMailCampaignsAdmin"])){$priv["AseMailCampaignsAdmin"]="no";}
		if(!isset($priv["AsInventoryAdmin"])){$priv["AsInventoryAdmin"]="no";}
		if(!isset($priv["AsOrgAdmin"])){$priv["AsOrgAdmin"]="no";}
		if(!isset($priv["AsMessagingOrg"])){$priv["AsMessagingOrg"]="no";}
		if(!isset($priv["AsDansGuardianGroupRule"])){$priv["AsDansGuardianGroupRule"]="no";}
		if(!isset($priv["AsOrgPostfixAdministrator"])){$priv["AsOrgPostfixAdministrator"]="no";}
		if(!isset($priv["AllowManageOwnComputers"])){$priv["AllowManageOwnComputers"]="no";}
		if(!isset($priv["AsOrgStorageAdministrator"])){$priv["AsOrgStorageAdministrator"]="no";}
		if(!isset($priv["AsMailManAdministrator"])){$priv["AsMailManAdministrator"]="no";}
		if(!isset($priv["AllowXapianDownload"])){$priv["AllowXapianDownload"]="no";}
		if(!isset($priv["AllowChangeMailBoxRules"])){$priv["AllowChangeMailBoxRules"]="no";}
		if(!isset($priv["AllowDansGuardianBanned"])){$priv["AllowDansGuardianBanned"]="no";}
		if(!isset($priv["AllowOpenVPN"])){$priv["AllowOpenVPN"]="no";}
		if(!isset($priv["AllowEmailing"])){$priv["AllowEmailing"]="no";}
		if(!isset($priv["AsQuarantineAdministrator"])){$priv["AsQuarantineAdministrator"]="no";}
		if(!isset($priv["AsDnsAdministrator"])){$priv["AsDnsAdministrator"]="no";}
		if(!isset($priv["AsSambaAdministrator"])){$priv["AsSambaAdministrator"]="no";}
		if(!isset($priv["AllowEditOuSecurity"])){$priv["AllowEditOuSecurity"]="no";}
		if(!isset($priv["AllowChangeAntiSpamSettings"])){$priv["AllowChangeAntiSpamSettings"]="no";}
		if(!isset($priv["AllowViewStatistics"])){$priv["AllowViewStatistics"]="no";}
		if(!isset($priv["AllowChangeUserKas"])){$priv["AllowChangeUserKas"]="no";}
		if(!isset($priv["AllowSenderCanonical"])){$priv["AllowSenderCanonical"]="no";}
		if(!isset($priv["AsMailBoxAdministrator"])){$priv["AsMailBoxAdministrator"]="no";}
		if(!isset($priv["AllowFetchMails"])){$priv["AllowFetchMails"]="no";}
		if(!isset($priv["AllowChangeDomains"])){$priv["AllowChangeDomains"]="no";}
		if(!isset($priv["AllowEditAliases"])){$priv["AllowEditAliases"]="no";}
		if(!isset($priv["AllowAddGroup"])){$priv["AllowAddGroup"]="no";}
		if(!isset($priv["AsSystemAdministrator"])){$priv["AsSystemAdministrator"]="no";}
		if(!isset($priv["AllowChangeKav"])){$priv["AllowChangeKav"]="no";}
		if(!isset($priv["AllowChangeUserPassword"])){$priv["AllowChangeUserPassword"]="no";}
		if(!isset($priv["AllowChangeKas"])){$priv["AllowChangeKas"]="no";}
		if(!isset($priv["AllowAddUsers"])){$priv["AllowAddUsers"]="no";}
		if(!isset($priv["AllowEditAsWbl"])){$priv["AllowEditAsWbl"]="no";}
		if(!isset($priv["AsArticaAdministrator"])){$priv["AsArticaAdministrator"]="no";}
		if(!isset($priv["AsWebMaster"])){$priv["AsWebMaster"]="no";}
		if(!isset($priv["RestrictNabToGroups"])){$priv["RestrictNabToGroups"]="no";}
		
		
		
		
		if($priv["AllowChangeDomains"]=='yes'){$this->AllowChangeDomains=true;}
		if($priv["AllowAddGroup"]=="yes"){$this->AllowAddGroup=true;}
		if($priv["AsSystemAdministrator"]=='yes'){$this->AsSystemAdministrator=true;}
		if($priv["AsArticaAdministrator"]=='yes'){$this->AsArticaAdministrator=true;}
		if($priv["AllowFetchMails"]=='yes'){$this->AllowFetchMails=true;}
		if($priv["AsMailBoxAdministrator"]=='yes'){$this->AsMailBoxAdministrator=true;}
		if($priv["AllowChangeKav"]=='yes'){$this->AllowChangeKav=true;}
		if($priv["AllowSenderCanonical"]=='yes'){$this->AllowSenderCanonical=true;}
		if($priv["AllowChangeUserPassword"]=='yes'){$this->AllowChangeUserPassword=true;}
		if($priv["AllowChangeKas"]=='yes'){$this->AllowChangeKas=true;}
		if($priv["AllowChangeUserKas"]=='yes'){$this->AllowChangeUserKas=true;}
		if($priv["AllowAddUsers"]=='yes'){$this->AllowAddUsers=true;}
		if($priv["AllowViewStatistics"]=='yes'){$this->AllowViewStatistics=true;}
		if($priv["AllowEditAsWbl"]=='yes'){$this->AllowEditAsWbl=true;}
		if($priv["AllowChangeAntiSpamSettings"]=='yes'){$this->AllowChangeAntiSpamSettings=true;}
		if($priv["AllowEditOuSecurity"]=='yes'){$this->AllowEditOuSecurity=true;}
		if($priv["AsSambaAdministrator"]=='yes'){$this->AsSambaAdministrator=true;}
		if($priv["AsDnsAdministrator"]=='yes'){$this->AsDnsAdministrator=true;}
		if($priv["AsQuarantineAdministrator"]=='yes'){$this->AsQuarantineAdministrator=true;}
		if($priv["AllowEditAliases"]=='yes'){$this->AllowEditAliases=true;}
		if($priv["AllowEmailing"]=='yes'){$this->AllowEmailing=true;}
		if($priv["AllowOpenVPN"]=='yes'){$this->AllowOpenVPN=true;}
		if($priv["AllowDansGuardianBanned"]=='yes'){$this->AllowDansGuardianBanned=true;}
		if($priv["AllowChangeMailBoxRules"]=='yes'){$this->AllowChangeMailBoxRules=true;}
		if($priv["AllowXapianDownload"]=='yes'){$this->AllowXapianDownload=true;}
		if($priv["AsMailManAdministrator"]=='yes'){$this->AsMailManAdministrator=true;}
		if($priv["AsOrgStorageAdministrator"]=='yes'){$this->AsOrgStorageAdministrator=true;}
		if($priv["AllowManageOwnComputers"]=='yes'){$this->AllowManageOwnComputers=true;}
		if($priv["AsOrgPostfixAdministrator"]=='yes'){$this->AsOrgPostfixAdministrator=true;}
		if($priv["AsDansGuardianGroupRule"]=='yes'){$this->AsDansGuardianGroupRule=true;}
		if($priv["AsMessagingOrg"]=='yes'){$this->AsMessagingOrg=true;}
		if($priv["AsOrgAdmin"]=='yes'){$this->AsOrgAdmin=true;}
		if($priv["AsInventoryAdmin"]=='yes'){$this->AsInventoryAdmin=true;}
		if($priv["AseMailCampaignsAdmin"]=='yes'){$this->AseMailCampaignsAdmin=true;}
		if($priv["AsJoomlaWebMaster"]=='yes'){$this->AsJoomlaWebMaster=true;}
		if($priv["AsSquidAdministrator"]=='yes'){$this->AsSquidAdministrator=true;}
		if($priv["AsVirtualBoxManager"]=='yes'){$this->AsVirtualBoxManager=true;}
		if($priv["OverWriteRestrictedDomains"]=='yes'){$this->OverWriteRestrictedDomains=true;}
		if($priv["AsWebMaster"]=='yes'){$this->AsWebMaster=true;}
		if($priv["RestrictNabToGroups"]=='yes'){$this->RestrictNabToGroups=true;}
		
		
		if($priv["PasswdPolicy"]<>null){$this->PasswdPolicy=unserialize(base64_decode($priv["PasswdPolicy"]));}
		
		if($log){
			if(is_array($priv)){
			while (list ($key, $val) = each ($priv) ){
				if($val=="yes"){
					writelogs("$key=$val",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				}
			}}
		}		
		
		
		if($this->AllowEditOuSecurity==true){
			if($log){writelogs("{$_SESSION["uid"]}: AllowEditOuSecurity: TRUE",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
			$this->AllowChangeAntiSpamSettings=true;
			$this->AllowDansGuardianBanned=true;
			$this->AllowManageOwnComputers=true;
			$this->AsDansGuardianGroupRule=true;
			$this->AseMailCampaignsAdmin=true;
			$this->AllowChangeAntiSpamSettings=true;
			$this->AllowChangeUserPassword=true;
			$this->AllowFetchMails=true;
			$this->AllowChangeUserKas=true;
			$this->AllowEditAliases=true;
			$this->AllowChangeMailBoxRules=true;
			$this->AllowSenderCanonical=true;
			$this->AllowOpenVPN=true;
			$this->AllowDansGuardianBanned=true;
			$this->AllowXapianDownload=true;
			$this->AllowManageOwnComputers=true;	
			$this->AllowEditAliases=true;			
		}
		

		
		
		if($this->IsGlobalAdmin($log)==true){
			$this->AllowChangeDomains=true;
			$this->AllowAddGroup=true;
			$this->AllowChangeOrg=true;
			$this->AsPostfixAdministrator=true;
			$this->AllowChangeKav=true;
			$this->AllowChangeKas=true;
			$this->AllowViewStatistics=true;
			$this->AllowEditOuSecurity=true;
			$this->AllowAddUsers=true;
			$this->AsSquidAdministrator=true;
			$this->AsSambaAdministrator=true;
			$this->AsDnsAdministrator=true;
			$this->AsQuarantineAdministrator=true;
			$this->AllowDansGuardianBanned=true;
			$this->AllowChangeAntiSpamSettings=true;
			$this->AllowViewStatistics=true;
			$this->AllowSenderCanonical=true;
			$this->AsOrgStorageAdministrator=true;
			$this->AllowManageOwnComputers=true;
			$this->AsDansGuardianGroupRule=true;
			$this->AsMessagingOrg=true;
			}
		
		if($this->AsArticaAdministrator==true){
			$this->AsSystemAdministrator=true;
			$this->AsMailBoxAdministrator=true;
			$this->AsPostfixAdministrator=true;
			$this->AllowChangeKav=true;
			$this->AllowChangeKas=true;
			$this->AllowAddUsers=true;
			$this->AsOrgAdmin=true;
			$this->AseMailCampaignsAdmin=true;
			$this->AllowViewStatistics=true;
			$this->AllowEditOuSecurity=true;
			$this->AsSquidAdministrator=true;
			$this->AsSambaAdministrator=true;
			$this->AsDnsAdministrator=true;
			$this->AsQuarantineAdministrator=true;
			$this->AllowDansGuardianBanned=true;
			$this->AllowFetchMails=true;
			$this->AllowChangeAntiSpamSettings=true;
			$this->AllowSenderCanonical=true;
			$this->AsMailManAdministrator=true;
			$this->AsOrgStorageAdministrator=true;
			$this->AllowManageOwnComputers=true;
			$this->AsOrgPostfixAdministrator=true;
			$this->AsDansGuardianGroupRule=true;
			$this->AsMessagingOrg=true;
			$this->AsInventoryAdmin=true;
			$this->AsJoomlaWebMaster=true;
			$this->AsVirtualBoxManager=true;
			$this->AllowChangeDomains=true;
			$this->OverWriteRestrictedDomains=true;
			$this->AsWebMaster=true;
			$this->AllowChangeAntiSpamSettings=true;
			$this->AllowChangeUserPassword=true;
			$this->AllowFetchMails=true;
			$this->AllowChangeUserKas=true;
			$this->AllowEditAliases=true;
			$this->AllowChangeMailBoxRules=true;
			$this->AllowSenderCanonical=true;
			$this->AllowOpenVPN=true;
			$this->AllowDansGuardianBanned=true;
			$this->AllowXapianDownload=true;
			$this->AllowManageOwnComputers=true;
			$this->AllowEditAliases=true;				
			}
			
		if($this->AsPostfixAdministrator){
			$this->OverWriteRestrictedDomains=true;
		}
			
		if($this->AsSystemAdministrator){
			$this->AsSambaAdministrator=true;
			$this->AsDnsAdministrator=true;
			$this->AsVirtualBoxManager=true;
			$this->OverWriteRestrictedDomains=true;
			$this->AsWebMaster=true;
			$this->AllowEditAliases=true;
			
		}
		if($this->AsMessagingOrg){
			if($log){writelogs("{$_SESSION["uid"]}: AsMessagingOrg: TRUE",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
			$this->AllowSenderCanonical=true;
			$this->AllowAddUsers=true;
			$this->AllowAddGroup=true;
			$this->AllowChangeDomains=true;
			$this->AllowEditAliases=true;
		}
		
		if($this->AllowAddUsers){
			$this->AllowChangeUserPassword=true;
		}
		
		if($this->AllowChangeDomains==true){
			$this->AllowSenderCanonical=true;
		}
		
		if($this->AllowEditOuSecurity==true){
			if($log){writelogs("{$_SESSION["uid"]}: AllowEditOuSecurity: TRUE",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
				$this->AsOrgAdmin=true;
				$this->AllowAddGroup=true;
				$this->AllowSenderCanonical=true;
				$this->AsJoomlaWebMaster=true;
				$this->AllowChangeUserPassword=true;
				$this->AllowChangeAntiSpamSettings=true;
				$this->AllowChangeUserPassword=true;
				$this->AllowFetchMails=true;
				$this->AllowChangeUserKas=true;
				$this->AllowEditAliases=true;
				$this->AllowChangeMailBoxRules=true;
				$this->AllowSenderCanonical=true;
				$this->AllowOpenVPN=true;
				$this->AllowDansGuardianBanned=true;
				$this->AllowXapianDownload=true;
				$this->AllowManageOwnComputers=true;
				$this->AllowEditAliases=true;				
		}
		if($this->AsDnsAdministrator==true){$this->AsOrgAdmin=true;$this->AllowAddGroup=true;}
		if($this->AsOrgAdmin==true){
			$this->AllowChangeDomains=true;
			$this->AllowSenderCanonical=true;
			$this->AllowAddUsers=true;
			$this->AllowAddGroup=true;
			$this->AllowChangeUserPassword=true;
			$this->AsMessagingOrg=true;
			$this->AsJoomlaWebMaster=true;
			$this->AsWebMaster=true;
			$this->AllowChangeAntiSpamSettings=true;
			$this->AllowChangeUserPassword=true;
			$this->AllowFetchMails=true;
			$this->AllowChangeUserKas=true;
			$this->AllowEditAliases=true;
			$this->AllowChangeMailBoxRules=true;
			$this->AllowSenderCanonical=true;
			$this->AllowOpenVPN=true;
			$this->AllowDansGuardianBanned=true;
			$this->AllowXapianDownload=true;
			$this->AllowManageOwnComputers=true;
			$this->AllowEditAliases=true;
			$this->AllowChangeDomains=true;
			$this->AllowEditOuSecurity=true;
		}
		if($this->AsQuarantineAdministrator==true){$this->AsMessagingOrg=true;}
		
		if($this->AsInventoryAdmin){
			$this->AllowManageOwnComputers=true;
		}
		
	}
	
	function IfIsAnuser($log=false){
			if($this->AsMessagingOrg){return false;}
			if($this->AseMailCampaignsAdmin){return false;}
			if($this->AsOrgPostfixAdministrator){return false;}			
			
			if($this->AsSystemAdministrator){
				if($log){writelogs("[{$_SESSION["uid"]}]: AsSystemAdministrator=TRUE",__CLASS__.'/'.__FUNCTION__,__FILE__);}
				return false;}
				
			if($this->AsVirtualBoxManager){
				if($log){writelogs("[{$_SESSION["uid"]}]: AsVirtualBoxManager=TRUE",__CLASS__.'/'.__FUNCTION__,__FILE__);}
				return false;}				
				
				
			if($this->AsMailBoxAdministrator){
				if($log){writelogs("[{$_SESSION["uid"]}]: AsMailBoxAdministrator=TRUE",__CLASS__.'/'.__FUNCTION__,__FILE__);}
				return false;}
			if($this->AsPostfixAdministrator){
				if($log){writelogs("[{$_SESSION["uid"]}]: AsPostfixAdministrator=TRUE",__CLASS__.'/'.__FUNCTION__,__FILE__);}
				return false;}
				
			if($this->AsSquidAdministrator){
				if($log){writelogs("[{$_SESSION["uid"]}]: AsSquidAdministrator=TRUE",__CLASS__.'/'.__FUNCTION__,__FILE__);}
				return false;}
			
			if($this->AllowChangeKav){
			if($log){writelogs("[{$_SESSION["uid"]}]: AllowChangeKav=TRUE",__CLASS__.'/'.__FUNCTION__,__FILE__);}
				return false;}
			if($this->AllowChangeKas){
				if($log){writelogs("[{$_SESSION["uid"]}]: AllowChangeKas=TRUE",__CLASS__.'/'.__FUNCTION__,__FILE__);}
				return false;}
			if($this->AllowAddUsers){
			if($log){writelogs("[{$_SESSION["uid"]}]: AllowAddUsers=TRUE",__CLASS__.'/'.__FUNCTION__,__FILE__);}
				return false;}
			if($this->AsOrgAdmin){
			if($log){writelogs("[{$_SESSION["uid"]}]: AsOrgAdmin=TRUE",__CLASS__.'/'.__FUNCTION__,__FILE__);}
				return false;}
			if($this->AllowViewStatistics){
			if($log){writelogs("[{$_SESSION["uid"]}]: AllowViewStatistics=TRUE",__CLASS__.'/'.__FUNCTION__,__FILE__);}
				return false;}
			if($this->AllowEditOuSecurity){
			if($log){writelogs("[{$_SESSION["uid"]}]: AllowEditOuSecurity=TRUE",__CLASS__.'/'.__FUNCTION__,__FILE__);}
				return false;}
			if($this->AsSambaAdministrator){
			if($log){writelogs("[{$_SESSION["uid"]}]: AsSambaAdministrator=TRUE",__CLASS__.'/'.__FUNCTION__,__FILE__);}
				return false;}
			if($this->AsDnsAdministrator){
			if($log){writelogs("[{$_SESSION["uid"]}]: AsDnsAdministrator=TRUE",__CLASS__.'/'.__FUNCTION__,__FILE__);}
				return false;}
			if($this->AsQuarantineAdministrator){
			if($log){writelogs("[{$_SESSION["uid"]}]: AsQuarantineAdministrator=TRUE",__CLASS__.'/'.__FUNCTION__,__FILE__);}
				return false;}
			if($this->AsOrgStorageAdministrator){
			if($log){writelogs("[{$_SESSION["uid"]}]: AsOrgStorageAdministrator=TRUE",__CLASS__.'/'.__FUNCTION__,__FILE__);}
				return false;}
				
			if($log){writelogs("[{$_SESSION["uid"]}]: None privileges found",__CLASS__.'/'.__FUNCTION__,__FILE__);}	
			return true;
			}
	
	
	function _ParsePrivieleges($content=null){
		$trace=debug_backtrace();
		if(isset($trace[1])){$called=" called by ". basename($trace[1]["file"])." {$trace[1]["function"]}() line {$trace[1]["line"]}";}
		if(!isset($GLOBALS["posix_getuid"])){$GLOBALS["posix_getuid"]=1000;if(function_exists("posix_getuid")){$GLOBALS["posix_getuid"]=posix_getuid();}}
		$posix_getuid=$GLOBALS["posix_getuid"];
		$priv=array();
		include_once(dirname(__FILE__)."/class.privileges.inc");
		if($GLOBALS["DEBUG_PRIVS"]){writelogs("class.privileges.inc ->privileges({$_SESSION["uid"]}) $called",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		$privs=new privileges($_SESSION["uid"]);
		if($GLOBALS["DEBUG_PRIVS"]){writelogs("return privs",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		return $privs->privs;
	}

	function _BuildPrivileges($hash){
		if(!is_array($hash)){return "DEFAULT";}
		while (list ($num, $ligne) = each ($hash) ){
			$conf=$conf ."[$num]=\"$ligne\"\n";
			
		}
		
		return $conf;
		
	}
	
	
	function isTreeReadable($priv=null){
		if(posix_getuid()==0){$this->uid=-100;}
		if($this->uid==-100){return true;}
		if($priv==null){$priv=$this->priv;}
		if($priv["AsSystemAdministrator"]=="yes"){return true;}
		if($priv["AsPostfixAdministrator"]=="yes"){return true;}
		if($priv["AsArticaAdministrator"]=="yes"){return true;}
		if($priv["AsSystemAdministrator"]=="yes"){return true;}
		if($priv["AsSambaAdministrator"]=="yes"){return true;}
		if($priv["AsVirtualBoxManager"]=="yes"){return true;}
		
		if($priv["AllowChangeKas"]=="yes"){return true;}
		if($priv["AllowChangeKav"]=="yes"){return true;}
		if($priv["AllowAddUsers"]=="yes"){return true;}
		if($priv["AllowAddGroup"]=="yes"){return true;}
		return false;
		}
		
	function IsGlobalAdmin($priv=null,$log=false){
		if(!isset($GLOBALS["posix_getuid"])){$GLOBALS["posix_getuid"]=1000;if(function_exists("posix_getuid")){$GLOBALS["posix_getuid"]=posix_getuid();}}
		if($GLOBALS["posix_getuid"]==0){$this->uid=-100;}
		if($this->uid==-100){
			$this->AsArticaAdministrator=true;
			$this->AsQuarantineAdministrator=true;
			$this->AsVirtualBoxManager=true;
			if($log){writelogs("It is a global administrator",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
			return true;
			}
		if($priv==null){$priv=$this->priv;}
		if($priv["AsSystemAdministrator"]=="yes"){return true;}
		if($priv["AsPostfixAdministrator"]=="yes"){return true;}
		if($priv["AsArticaAdministrator"]=="yes"){return true;}
		if($priv["AsSquidAdministrator"]=="yes"){return true;}
		if($priv["AsSambaAdministrator"]=="yes"){return true;}
		if($priv["AsVirtualBoxManager"]=="yes"){return true;}
		return false;
		}
	
		
function array_monitorix($type="day"){
	include_once(dirname(__FILE__)."/class.page.builder.inc");
	$pges=new pagebuilder();			
	return $pges->monitorix_array($type);
	}

}


function url_decode_special($path){
	include_once(dirname(__FILE__)."/class.html.tools.inc");
	return url_decode_special_tool($path);
}

function GetRights_aliases() {
	$usersprivs = new usersMenus ( );
	if ($usersprivs->AsAnAdministratorGeneric) {return 1;}
	if ($usersprivs->AllowEditOuSecurity) {return 1;}
	if ($usersprivs->AsMessagingOrg) {return 1;}
	if ($usersprivs->AllowEditAliases) {return 1;}
	if($usersprivs->AsInventoryAdmin){return 1;}
	return 0;
}

?>