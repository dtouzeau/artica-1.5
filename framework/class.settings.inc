<?php

class settings_inc {
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
	var $AsArticaAdministrator=false;
	var $AsSambaAdministrator=false;
	var $AsMailManAdministrator=false;
	var $AsPostfixAdministrator=false;
	var $AsSquidAdministrator=false;
	var $AsMailBoxAdministrator=false;
	var $AsDansGuardianGroupRule=false;
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
	var $OPENVPN_APPLIANCE=false;
	var $SIEVE_PORT=0;
	var $TOMCAT_INSTALLED=false;
	var $TOMCAT_VERSION="";
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
	var $LOSETUP_INSTALLED=false;
	var $SNORT_INSTALLED=false;
	var $APP_EYEOS_INSTALLED=false;
	var $EYEOS_VERSION;
	var $APACHE_MOD_SECURITY=false;
	var $APACHE_MOD_EVASIVE=false;
	var $Z_PUSH_INSTALLED=false;
	var $APACHE_PROXY_MODE=false;
	var $APP_GREENSQL_INSTALLED=false;
	var $IPTABLES_ACCOUNTING_EXISTS=false;
	var $POWER_DNS_MYSQL=false;
	var $POWERADMIN_INSTALLED=false;
	var $APACHE_MOD_CACHE=false;
	var $APP_AMANDA_INSTALLED=false;
	var $NSCD_INSTALLED=false;
	var $DRUPAL7_INSTALLED=false;
	var $TOMCAT_JAVA="";
	var $TOMCAT_DIR="";
	var $APACHE_MOD_TOMCAT=false;
	private function CheckIndexDefaults($_GLOBAL){
		return $_GLOBAL;
		foreach($this as $key => $value) {
			if(!isset($_GLOBAL[$key])){$_GLOBAL[$key]=$value;}
		}
		
		return $_GLOBAL;
		
		
	}
	
	
	function settings_inc(){
			$_GLOBAL=array();
			if($GLOBALS["VERBOSE"]){
					$trace=debug_backtrace();
					if(isset($trace[1])){
						$called=" called by ". basename($trace[1]["file"])." {$trace[1]["function"]}() line {$trace[1]["line"]}";			
						echo "Instanciate userMenus() $called\n";
					}
				}		
		
			
			
			$settings="/usr/share/artica-postfix/ressources/settings.inc";
			
			if(file_exists($settings)){
					@include($settings);
					$this->GLOBAL_CONFIG=$_GLOBAL;
					$GLOBALS["SETTINGS_FILES"]=$_GLOBAL;
					if(!is_array($this->GLOBAL_CONFIG)){echo "Settings unable stat";return false;}
					}else{
					die("Unable to stat $settings");
			}

			
			if(isset($_GLOBAL["NSCD_INSTALLED"])){$this->NSCD_INSTALLED=$_GLOBAL["NSCD_INSTALLED"];}
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
			if(isset($_GLOBAL["APACHE_MOD_CACHE"])){$this->APACHE_MOD_CACHE=$_GLOBAL["APACHE_MOD_CACHE"];}
			if(isset($_GLOBAL["TOMCAT_INSTALLED"])){$this->TOMCAT_INSTALLED=$_GLOBAL["TOMCAT_INSTALLED"];}
			if(isset($_GLOBAL["TOMCAT_VERSION"])){$this->APACHE_MOD_CACHE=$_GLOBAL["TOMCAT_VERSION"];}
			
			
			
			
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
			$this->SIEVE_PORT=$_GLOBAL["SIEVE_PORT"];
			$this->APACHE_PROXY_MODE=$_GLOBAL["APACHE_PROXY_MODE"];
			$this->APP_GREENSQL_INSTALLED=$_GLOBAL["APP_GREENSQL_INSTALLED"];
			$this->IPTABLES_ACCOUNTING_EXISTS=$_GLOBAL["IPTABLES_ACCOUNTING_EXISTS"];
			$this->POWER_DNS_MYSQL=$_GLOBAL["POWER_DNS_MYSQL"];
			$this->POWERADMIN_INSTALLED=$_GLOBAL["POWERADMIN_INSTALLED"];
			
			if(isset($_GLOBAL["ISCSI_CLIENT_INSTALLED"])){$this->ISCSI_CLIENT_INSTALLED=$_GLOBAL["ISCSI_CLIENT_INSTALLED"];}
			if($this->LinuxDistriCode=="DEBIAN"){$this->AS_DEBIAN_FAMILY=true;}
			if($this->LinuxDistriCode=="UBUNTU"){$this->AS_DEBIAN_FAMILY=true;}
			$this->APACHE_MOD_SECURITY=$_GLOBAL["APACHE_MOD_SECURITY"];
			$this->APACHE_MOD_EVASIVE=$_GLOBAL["APACHE_MOD_EVASIVE"];
			$this->OPENVPN_APPLIANCE=$_GLOBAL["OPENVPN_APPLIANCE"];
			if(isset($_GLOBAL["Z_PUSH_INSTALLED"])){$this->Z_PUSH_INSTALLED=$_GLOBAL["Z_PUSH_INSTALLED"];}
			if(isset($_GLOBAL["APP_AMANDA_INSTALLED"])){$this->APP_AMANDA_INSTALLED=$_GLOBAL["APP_AMANDA_INSTALLED"];}
			if(isset($_GLOBAL["DRUPAL7_INSTALLED"])){$this->DRUPAL7_INSTALLED=$_GLOBAL["DRUPAL7_INSTALLED"];}
			$this->TOMCAT_JAVA=$_GLOBAL["TOMCAT_JAVA"];
			$this->TOMCAT_DIR=$_GLOBAL["TOMCAT_DIR"];
			$this->APACHE_MOD_TOMCAT=$_GLOBAL["APACHE_MOD_TOMCAT"];
			
			
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
			
			
			$this->CPU_NUMBER=$_GLOBAL["CPU_NUMBER"];
			$this->LOAD_AVERAGE=$_GLOBAL["LOAD_AVERAGE"];
			
			if($this->OPENVPN_APPLIANCE){
				$this->SQUID_INSTALLED=false;
				$this->SAMBA_INSTALLED=false;
				$this->POSTFIX_INSTALLED=false;
				$this->cyrus_imapd_installed=false;
				$this->ZARAFA_INSTALLED=false;
				$this->BACKUPPC_INSTALLED=false;
				$this->nmap_installed=false;
			}			
			
			
			
		}
		
	private function postfixmutli(){
		if(!$this->POSTMULTI){return false;}
		$vers=explode(".",$this->POSTFIX_VERSION);
		if($vers[0]>=2){if($vers[1]>=5){return true;}}
	}		
	
}

?>