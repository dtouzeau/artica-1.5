<?php
include_once(dirname(__FILE__)."/frame.class.inc");
include_once(dirname(__FILE__)."/class.unix.inc");
include_once(dirname(__FILE__)."/class.postfix.inc");


if(isset($_GET["CleanCache"])){CleanCache();exit;}
if(isset($_GET["GetLangagueFile"])){LOAD_LANGUAGE_FILE();exit;}
if(isset($_GET["SaveConfigFile"])){SaveConfigFile();exit;}
if(isset($_GET["SaveClusterConfigFile"])){SaveClusterConfigFile();exit;}
if(isset($_GET["SmtpNotificationConfig"])){SmtpNotificationConfig();exit;}
if(isset($_GET["refresh-frontend"])){Refresh_frontend();exit;}
if(isset($_GET["find-program"])){find_sock_program();exit;}
if(isset($_GET["syslog-query"])){SYSLOG_QUERY();exit;}
if(isset($_GET["aptcheck"])){aptcheck();exit;}
if(isset($_GET["SetServerTime"])){SetServerTime();exit;}
if(isset($_GET["CompileSSHDRules"])){CompileSSHDRules();exit;}
if(isset($_GET["ou-ldap-import-execute"])){LDAP_IMPORT_EXEC();exit;}
if(isset($_GET["sys-sync-paquages"])){SysSyncPaquages();exit;}


if(isset($_GET["LaunchRemoteInstall"])){LaunchRemoteInstall();exit;}
if(isset($_GET["restart-web-server"])){RestartWebServer();exit;}
if(isset($_GET["restart-artica-status"])){RestartArticaStatus();exit;}
if(isset($_GET["wake-on-lan"])){WakeOnLan();exit;}

if(isset($_GET["net-ads-leave"])){net_ads_leave();exit;}
if(isset($_GET["process1-force"])){process1_force();exit;}

if(isset($_GET["right-status"])){right_status();exit;}

if(isset($_GET["RestartApacheGroupwareForce"])){RestartApacheGroupwareForce();exit;}
if(isset($_GET["RestartApacheGroupwareNoForce"])){RestartApacheGroupwareNoForce();exit;}
if(isset($_GET["ApacheDirDelete"])){ApacheDirDelete();exit;}

//snort

if(isset($_GET["snort-networks"])){snort_networks();exit;}
if(isset($_GET["restart-snort"])){restart_snort();exit;}


if(isset($_GET["restart-apache-src"])){RestartApacheSrc();exit;}
if(isset($_GET["apachesrc-ini-status"])){apache_src_status();exit;}
if(isset($_GET["snort-status"])){snort_status();exit;}
if(isset($_GET["freeweb-restart"])){RestartApacheSrc();pureftpd_restart();exit;}
if(isset($_GET["freeweb-website"])){freeweb_website();exit;}
if(isset($_GET["freeweb-permissions"])){freeweb_permissions();exit;}
if(isset($_GET["freeweb-groupware"])){freeweb_groupware();exit;}



if(isset($_GET["awstats-perform"])){awstats_perform();exit;}
if(isset($_GET["VIPTrackRun"])){VIPTrackRun();exit;}

//postfwd2_status
if(isset($_GET["postfwd2-status"])){postfwd2_status();exit;}
if(isset($_GET["postfwd2-reload"])){postfwd2_reload();exit;}
if(isset($_GET["postfwd2-restart"])){postfwd2_restart();exit;}



//sabnzbdplus-restart

if(isset($_GET["sabnzbdplus-ini-status"])){sabnzbdplus_src_status();exit;}
if(isset($_GET["sabnzbdplus-restart"])){sabnzbdplus_restart();exit;}


if(isset($_GET["start-install-app"])){SETUP_CENTER_LAUNCH();exit;}

if(isset($_GET["ChangeMysqlLocalRoot"])){ChangeMysqlLocalRoot();exit;}
if(isset($_GET["ChangeMysqlLocalRoot2"])){ChangeMysqlLocalRoot2();exit;}
if(isset($_GET["ChangeMysqlDir"])){ChangeMysqlDir();exit;}



if(isset($_GET["change-mysql-params"])){ChangeMysqlParams();exit;}
if(isset($_GET["mysql-myd-file"])){mysql_myd_file();exit;}
if(isset($_GET["mysql-check"])){mysql_check();exit;}

if(isset($_GET["viewlogs"])){viewlogs();exit;}
if(isset($_GET["LdapdbStat"])){LdapdbStat();exit;}
if(isset($_GET["LdapdbSize"])){LdapdbSize();exit;}
if(isset($_GET["ldap-restart"])){ldap_restart();exit;}
if(isset($_GET["buildFrontEnd"])){buildFrontEnd();exit;}
if(isset($_GET["cpualarm"])){cpualarm();exit;}
if(isset($_GET["CurrentLoad"])){CurrentLoad();exit;}
if(isset($_GET["TaskLastManager"])){TaskLastManager();exit;}
if(isset($_GET["start-all-services"])){StartAllServices();exit;}
if(isset($_GET["kill-pid-number"])){process_kill();exit;}
if(isset($_GET["kill-pid-single"])){process_kill_single();exit;}
if(isset($_GET["postmaster-cron"])){postmaster_cron();exit;}
if(isset($_GET["start-service-name"])){StartServiceCMD();exit;}
if(isset($_GET["stop-service-name"])){StopServiceCMD();exit;}
if(isset($_GET["START-STOP-SERVICES"])){START_STOP_SERVICES();exit;}
if(isset($_GET["monit-status"])){MONIT_STATUS();exit;}
if(isset($_GET["monit-restart"])){MONIT_RESTART();exit;}
if(isset($_GET["restart-http-engine"])){LIGHTTPD_RESTART();exit;}
if(isset($_GET["fcron-restart"])){FCRON_RESTART();exit;}
if(isset($_GET["restart-mldonkey"])){MLDONKEY_RESTART();exit;}
if(isset($_GET["restart-artica-maillog"])){ARTICA_MAILLOG_RESTART();exit;}
if(isset($_GET["notifier-restart"])){EMAILRELAY_RESTART();exit;}

if(isset($_GET["cdir-calc"])){IP_CALC_CDIR();exit;}
if(isset($_GET["ip-get-default-getway"])){getDefaultGateway();exit;}
if(isset($_GET["ip-get-default-dns"])){GetMyDNSServers();exit;}
if(isset($_GET["ip-del-route"])){IP_DEL_ROUTE();exit;}
if(isset($_GET["ip-build-routes"])){IP_ROUTES();exit;}

if(isset($_GET["DeleteAllIpTablesRules"])){IpTables_delete_all_rules();exit;}
if(isset($_GET["WhiteListResolvMX"])){IpTables_WhiteListResolvMX();exit;}


if(isset($_GET["unix-groups"])){unix_groups();exit;}
if(isset($_GET["ping"])){ping();exit;}


//autofs
if(isset($_GET["autofs-ini-status"])){AUTOFS_STATUS();exit;}
if(isset($_GET["autofs-restart"])){AUTOFS_RESTART();exit;}
if(isset($_GET["autofs-reload"])){AUTOFS_RELOAD();exit;}


//resolv
if(isset($_GET["copyresolv"])){copyresolv();exit;}


if(isset($_GET["greyhole-ini-status"])){GREYHOLE_STATUS();exit;}
if(isset($_GET["greyhole-restart"])){GREYHOLE_RESTART();exit;}
if(isset($_GET["greyhole-daily-fck"])){GREYHOLE_DAILY_FCK();exit;}


if(isset($_GET["ProcessExists"])){ProcessExists();exit;}

if(isset($_GET["compile-proxy"])){PROXY_SAVE();exit;}
if(isset($_GET["sarg-config"])){SARG_SAVE();exit;}
if(isset($_GET["sarg-run"])){SARG_EXEC();exit;}
if(isset($_GET["sarg-passwords"])){SARG_PASSWORDS();exit;}


//syslog
if(isset($_GET["syslog-master-mode"])){syslog_master_mode();exit;}
if(isset($_GET["syslog-client-mode"])){syslog_client_mode();exit;}
if(isset($_GET["IsUDPport"])){IsUDPport();exit;}

//PDNS
if(isset($_GET["pdns-restart"])){POWERDNS_RESTART();exit;}


//DNSMASQ
if(isset($_GET["LoaddnsmasqConf"])){DNSMASQ_LOAD_CONF();exit;}
if(isset($_GET["restart-dnsmasq"])){DNSMASQ_RESTART();exit;}


//iscsi
if(isset($_GET["iscsi-search"])){iscsi_search();exit;}
if(isset($_GET["restart-iscsi"])){iscsi_restart();exit;}
if(isset($_GET["iscsi-status"])){iscsi_status();exit;}
if(isset($_GET["reload-iscsi"])){iscsi_reload();exit;}
if(isset($_GET["iscsi-client"])){iscsi_client();exit;}
if(isset($_GET["iscsi-sessions"])){iscsi_client_sessions();exit;}




//UpdateUtility

if(isset($_GET["UpdateUtilitySource"])){UpdateUtilitySource();exit;}

//stunnel
if(isset($_GET["stunnel-ini-status"])){STUNNEL_INI_STATUS();exit;}
if(isset($_GET["stunnel-restart"])){STUNNEL_RESTART();exit;}



if(isset($_GET["hamachi-net"])){hamachi_net();exit;}
if(isset($_GET["hamachi-status"])){hamachi_status();exit;}
if(isset($_GET["hamachi-sessions"])){hamachi_sessions();exit;}
if(isset($_GET["hamachi-ip"])){hamachi_currentIP();exit;}
if(isset($_GET["hamachi-restart"])){hamachi_restart();exit;}
if(isset($_GET["hamachi-delete-net"])){hamachi_delete_network();exit;}
if(isset($_GET["UpdateKav4Proxy"])){Kav4ProxyUpdate();exit;}

if(isset($_GET["kavmilter-configure"])){kavmilter_configure();exit;}
if(isset($_GET["kavmilter-mem"])){kavmilter_mem();exit;}
if(isset($_GET["kavmilter-pattern"])){kavmilter_pattern();exit;}
if(isset($_GET["kavmilter_license"])){kavmilter_license();exit;}
if(isset($_GET["kavmilter-bases-infos"])){kav4lms_bases_infos();exit;}
if(isset($_GET["KavMilterDbVer"])){KavMilterDbVer();exit;}



if(isset($_GET["kav4fs-infos"])){kav4fs_infos();exit;}
if(isset($_GET["kav4fs-ini-status"])){kav4fs_status();exit;}

if(isset($_GET["kaf4fs-install-key"])){kav4fs_install_key();exit;}
if(isset($_GET["kaf4fs-pattern"])){kav4fsPatternDate();exit;}
if(isset($_GET["kav4fs-tasks"])){kav4fs_tasks();exit;}

if(isset($_GET["pptpd-ini-status"])){pptpd_status();exit;}
if(isset($_GET["pptpd-clients-ini-status"])){pptpd_clients_status();exit;}
if(isset($_GET["pptpd-chap"])){pptpd_chap();exit;}
if(isset($_GET["pptpd-restart"])){pptpd_restart();exit;}
if(isset($_GET["pptpd-ifconfig"])){pptpd_ifconfig();exit;}

if(isset($_GET["mbx-migr-add-file"])){mailbox_migration_import_file();exit;}
if(isset($_GET["mbx-migr-reload-members"])){mailbox_migration_start_members();exit;}



if(isset($_GET["ocsweb-restart"])){OCSWEB_RESTART();exit;}
if(isset($_GET["ocsweb-status"])){OCSWEB_STATUS();exit;}
if(isset($_GET["ocs-generate-certificate"])){OCSWEB_CERTIFICATE();exit();}
if(isset($_GET["ocs-get-csr"])){OCSWEB_CERTIFICATE_CSR();exit;}
if(isset($_GET["ocs-generate-final-certificate"])){OCSWEB_FINAL_CERTIFICATE();exit;}
if(isset($_GET["ocs-package-infos"])){OCSWEB_PACKAGE_INFOS();exit;}
if(isset($_GET["ocs-package-cp"])){OCSWEB_PACKAGE_COPY();exit;}
if(isset($_GET["ocs-package-cpinfo"])){OCSWEB_PACKAGE_CREATE_INFO();exit;}
if(isset($_GET["ocs-package-delete"])){OCSWEB_PACKAGE_DELETE();exit;}
if(isset($_GET["ocs-package-frag"])){OCSWEB_PACKAGE_FRAGS();exit;}
if(isset($_GET["ocs-agent-zip-packages"])){OCSWEB_GET_AGENT_PACKAGE_FILENAME();exit;}
if(isset($_GET["ocsagntlnx-status"])){OCSAGENT_STATUS();exit;}
if(isset($_GET["ocsagntlnx-restart"])){OCSAGENT_RESTART();exit;}
if(isset($_GET["ocsInventoryagntWinVer"])){InventoryAgentsWindowsVersions();exit;}
if(isset($_GET["UpdateFusionInventory"])){OCSAGENT_UPDATE_FUSION_INVENTORY();exit;}
if(isset($_GET["winexe-ver"])){WINEXE_VERSION();exit;}
if(isset($_GET["moveOcsAgentPackage"])){OCSWEB_MOVE_INVENTORY_WIN_PACKAGE();exit;}
if(isset($_GET["ocs-web-events"])){OCSWEB_WEB_EVENTS();exit;}
if(isset($_GET["ocs-web-errors"])){OCSWEB_WEB_ERRORS();exit;}
if(isset($_GET["ocs-service-events"])){OCSWEB_SERV_EVENTS();exit;}
if(isset($_GET["sysctl-value"])){KERNEL_SYSCTL_VALUE();exit;}
if(isset($_GET["sysctl-setvalue"])){KERNEL_SYSCTL_SET_VALUE();exit;}

if(isset($_GET["keymap-list"])){KEYBOARD_KEY_MAP();exit;}

//artica-meta
if(isset($_GET["artica-meta-register"])){artica_meta_register();exit;}
if(isset($_GET["artica-meta-join"])){artica_meta_join();exit;}
if(isset($_GET["artica-meta-unjoin"])){artica_meta_unjoin();exit;}
if(isset($_GET["artica-meta-push"])){artica_meta_push();exit;}
if(isset($_GET["artica-meta-user"])){artica_meta_user();exit;}
if(isset($_GET["artica-meta-export-dns"])){artica_meta_user_export_dns();exit;}
if(isset($_GET["artica-meta-awstats"])){artica_meta_export_awstats();exit;}
if(isset($_GET["artica-meta-computer"])){artica_meta_computer();exit;}
if(isset($_GET["artica-meta-fetchmail-rules"])){artica_meta_fetchmail_rules();exit;}
if(isset($_GET["artica-meta-ovpn"])){artica_meta_ovpn();exit;}
if(isset($_GET["artica-meta-openvpn-sites"])){artica_meta_export_openvpn_sites();exit;}


//freewebs

if(isset($_GET["freewebs-rebuild"])){freeweb_rebuild();exit;}

//organizations

if(isset($_GET["move-ldap-ou"])){ORGANISATION_RENAME();exit;}

//iptables
if(isset($_GET["iptables-bridge-rules"])){IPTABLES_CHAINES_BRIDGE_RULES();exit;}
if(isset($_GET["virtual-ip-build-bridges"])){TCP_VIRTUALS_BUILD_BRIDGES();exit;}
if(isset($_GET["iptables-rotator"])){IPTABLES_CHAINES_ROTATOR();exit;}
if(isset($_GET["iptables-rotator-show"])){IPTABLES_CHAINES_ROTATOR_SHOW();exit;}



//apt-mirror
if(isset($_GET["apt-mirror-ini-status"])){APT_MIRROR_STATUS();exit;}
if(isset($_GET["apt-mirror-schedule"])){APT_MIRROR_SCHEDULE();exit;}

//qos
if(isset($_GET["qos-iptables"])){qos_iptables();exit;}
if(isset($_GET["qos-compile"])){qos_compile();exit;}



//ddclient
if(isset($_GET["ddclient"])){DDCLIENT_RESTART();exit;}


//audtitd
if(isset($_GET["auditd-rebuild"])){AUDITD_REBUILD();exit;}
if(isset($_GET["auditd-ini-status"])){AUDITD_STATUS();exit;}
if(isset($_GET["auditd-config"])){AUDITD_CONFIG();exit;}
if(isset($_GET["auditd-apply"])){AUDITD_SAVE_CONFIG();exit;}
if(isset($_GET["auditd-force"])){AUDITD_FORCE();exit;}


//crossroads
if(isset($_GET["crossroads-ini-status"])){CROSSROADS_STATUS();exit;}
if(isset($_GET["crossroads-restart"])){CROSSROADS_RESTART();exit;}
if(isset($_GET["crossroads-events"])){CROSSROADS_EVENTS();exit;}

//saslauthd
if(isset($_GET["saslauthd-restart"])){saslauthd_restart();exit;}


//openDKIM
if(isset($_GET["opendkim-restart"])){OPENDKIM_RESTART();exit;}
if(isset($_GET["opendkim-whitelistdomains"])){OPENDKIM_WHITELIST_DOMAINS();exit;}
if(isset($_GET["opendkim-show-keys"])){OPENDKIM_SHOW_KEYS();exit;}
if(isset($_GET["opendkim-show-tests-keys"])){OPENDKIM_SHOW_TESTS_KEYS();exit;}


//milter-dkim
if(isset($_GET["milter-dkim-restart"])){MILTERDKIM_RESTART();exit;}
if(isset($_GET["milterdkim-show-tests-keys"])){MILTERDKIM_SHOW_TESTS_KEYS();exit;}
if(isset($_GET["milterdkim-show-keys"])){MILTERDKIM_SHOW_KEYS();exit;}
if(isset($_GET["milterdkim-whitelistdomains"])){MILTERDKIM_WHITELIST_DOMAINS();exit;}

//thincient

if(isset($_GET["thinclients-rebuild"])){THINCLIENT_REBUILD();exit;}
if(isset($_GET["thinclients-rebuild-cd"])){THINCLIENT_REBUILD_CD();exit;}


if(isset($_GET["milter-greylist-ini-status"])){MILTER_GREYLIST_INI_STATUS();exit;}
if(isset($_GET["milter-greylist-reconfigure"])){milter_greylist_reconfigure();exit;}
if(isset($_GET["milter-greylist-multi-status"])){milter_greylist_multi_status();exit;}
if(isset($_GET["move_uploaded_file"])){move_uploaded_file_framework();exit;}

if(isset($_GET["sslfingerprint"])){sslfingerprint();exit;}

if(isset($_GET["kasversion"])){kasversion();exit;}
if(isset($_GET["kas-reconfigure"])){kas_reconfigure();exit;}
if(isset($_GET["Kas3DbVer"])){Kas3DbVer();exit;}




if(isset($_GET["kaspersky-status"])){kaspersky_status();exit;}
if(isset($_GET["kav4proxy-reconfigure"])){kav4proxy_reload();exit;}
if(isset($_GET["kav4proxy-pattern-date"])){kav4ProxyPatternDate();exit;}

// 
if(isset($_GET["RestartRetranslator"])){retranslator_restart();exit;}
if(isset($_GET["RetranslatorSitesList"])){retranslator_sites_lists();exit;}
if(isset($_GET["RetranslatorEvents"])){retranslator_events();exit;}
if(isset($_GET["retranslator-status"])){retranslator_status();exit;}
if(isset($_GET["retranslator-execute"])){retranslator_execute();exit;}
if(isset($_GET["retranslator-dbsize"])){retranslator_dbsize();exit;}
if(isset($_GET["retranslator-tmp-dbsize"])){retranslator_tmp_dbsize();exit;}

if(isset($_GET["Global-Applications-Status"])){Global_Applications_Status();exit;}
if(isset($_GET["status-forced"])){Global_Applications_Status();exit;}
if(isset($_GET["system-reboot"])){shell_exec("reboot");exit;}
if(isset($_GET["system-shutdown"])){shell_exec("init 0");exit;}
if(isset($_GET["system-unique-id"])){GetUniqueID();exit;}
if(isset($_GET["system-debian-kernel"])){system_debian_kernel();exit;}
if(isset($_GET["system-debian-upgrade-kernel"])){system_debian_kernel_upgrade();exit;}

//clamav
if(isset($_GET["update-clamav"])){ClamavUpdate();exit;}
if(isset($_GET["clamd-restart"])){clamd_restart();exit;}
if(isset($_GET["clamav-av-pattern-status"])){clamd_pattern_status();exit;}
if(isset($_GET["clamd-reload"])){clamd_reload();exit;}



//reports
if(isset($_GET["pdf-quarantine-cron"])){reports_build_quarantine_cron();exit;}
if(isset($_GET["pdf-quarantine-send"])){reports_build_quarantine_send();exit;}

//pure-ftpd

if(isset($_GET["pure-ftpd-status"])){pureftpd_status();exit;}
if(isset($_GET["pure-ftpd-restart"])){pureftpd_restart();exit;}
if(isset($_GET["pure-ftpd-users"])){pureftpd_users();exit;}



//NFS
if(isset($_GET["reload-nfs"])){NFS_RELOAD();exit;}


//amavis restart
if(isset($_GET["amavis-restart"])){RestartAmavis();exit;}
if(isset($_GET["amavis-get-events"])){amavis_get_events();exit;}
if(isset($_GET["amavis-configuration-file"])){amavis_get_config();exit;}
if(isset($_GET["amavis-get-status"])){amavis_get_status();exit;}
if(isset($_GET["amavis-template-load"])){amavis_get_template();exit;}
if(isset($_GET["amavis-template-save"])){amavis_save_template();exit;}
if(isset($_GET["amavis-template-help"])){amavis_template_help();exit;}
if(isset($_GET["amavis-watchdog"])){amavis_watchdog();exit;}




//rsync
if(isset($_GET["RestartRsyncServer"])){RestartRsyncServer();exit;}
if(isset($_GET["rsyncd-conf"])){rsync_load_config();exit;}
if(isset($_GET["rsync-save-conf"])){rsync_save_conf();exit;}

//zarafa
if(isset($_GET["zarafa-admin"])){zarafa_admin_chock();exit;}
if(isset($_GET["zarafa-migrate"])){zarafa_migrate();exit;}
if(isset($_GET["zarafa-restart-web"])){zarafa_restart_web();exit;}
if(isset($_GET["zarafa-restart-server"])){zarafa_restart();exit;}



if(isset($_GET["zarafa-user-details"])){zarafa_user_details();exit;}

if(isset($_GET["zarafa-status"])){zarafa_status();exit;}
if(isset($_GET["zarafa-hash"])){zarafa_hash();exit;}
if(isset($_GET["zarafa-read-license"])){zarafa_read_license();exit;}
if(isset($_GET["zarafa-write-license"])){zarafa_write_license();exit;}



//Install/Uninstall
if(isset($_GET["organization-delete"])){organization_delete();exit;}
if(isset($_GET["uninstall-app"])){application_uninstall();exit;}
if(isset($_GET["AppliCenterGetDebugInfos"])){application_debug_infos();exit;}
if(isset($_GET["services-install"])){application_service_install();exit;}


if(isset($_GET["Kav4ProxyLicense"])){kav4proxy_license();exit;}
if(isset($_GET["Kav4ProxyUploadLicense"])){kav4proxy_upload_license();exit;}
if(isset($_GET["Kav4ProxyLicenseDelete"])){kav4proxy_delete_license();exit;}

//fetchmail
if(isset($_GET["restart-fetchmail"])){RestartFetchmail();exit;}
if(isset($_GET["fetchmail-status"])){fetchmail_status();exit;}
if(isset($_GET["fetchmail-logs"])){fetchmail_logs();exit;}


//Ad importation
if(isset($_GET["ad-import-schedule"])){AD_IMPORT_SCHEDULE();exit;}
if(isset($_GET["ad-import-remove-schedule"])){AD_REMOVE_SCHEDULE();exit;}
if(isset($_GET["ad-import-perform"])){AD_PERFORM();exit;}

if(isset($_GET["ou-ldap-import-schedules"])){LDAP_IMPORT_SCHEDULE();exit;}
if(isset($_GET["ou-ldap-import-schedules"])){LDAP_IMPORT_EXEC();exit;}


//exec.hamachi.php
if(isset($_GET["list-nics"])){TCP_LIST_NICS();exit;}
if(isset($_GET["virtuals-ip-reconfigure"])){TCP_VIRTUALS();exit;}
if(isset($_GET["vlan-ip-reconfigure"])){TCP_VLANS();exit;}
if(isset($_GET["nicstatus"])){TCP_NIC_STATUS();exit;}


if(isset($_GET["QueryArticaLogs"])){artica_update_query_fileslogs();exit;}
if(isset($_GET["ReadArticaLogs"])){artica_update_query_logs();exit;}

if(isset($_GET["repair-artica-ldap-branch"])){RepairArticaLdapBranch();exit;}

//certitifcate
if(isset($_GET["ChangeSSLCertificate"])){ChangeSSLCertificate();exit;}
if(isset($_GET["postfix-certificate"])){postfix_certificate();exit;}
if(isset($_GET["certificate-viewinfos"])){certificate_infos();exit;}
if(isset($_GET["postfix-perso-settings"])){postfix_perso_settings();exit;}
if(isset($_GET["postfix-smtpd-restrictions"])){postfix_smtpd_restrictions();exit;}
if(isset($_GET["postfix-mem-disk-status"])){postfix_mem_disk_status();exit;}
if(isset($_GET["postscreen"])){postscreen();exit;}
if(isset($_GET["postfix-throttle"])){postfix_throttle();exit;}
if(isset($_GET["postfix-freeze"])){postfix_freeze();exit;}
if(isset($_GET["postfix-ssl"])){postfix_single_ssl();exit;}
if(isset($_GET["postfix-sasl-mech"])){postfix_single_sasl_mech();exit;}
if(isset($_GET["postfix-postfinger"])){postfix_postfinger();exit;}
if(isset($_GET["postfix-iptables-compile"])){postfix_iptables_compile();exit;}
if(isset($_GET["postfix-body-checks"])){postfix_body_checks();exit;}
if(isset($_GET["postfix-smtp-sender-restrictions"])){postfix_smtp_senders_restrictions();exit;}
if(isset($_GET["maillog-query"])){maillog_query();exit;}
if(isset($_GET["postfix-whitelisted-global"])){postfix_whitelisted_global();exit;}
if(isset($_GET["postfinder"])){postfinder();exit;}

//cluebringer


if(isset($_GET["cluebringer-restart"])){cluebringer_restart();exit;}
if(isset($_GET["cluebringer-ini-status"])){cluebringer_status();exit;}
if(isset($_GET["cluebringer-passwords"])){cluebringer_passwords();exit;}



//postmulti

if(isset($_GET["postfix-multi-status"])){postfix_multi_status();exit;}
if(isset($_GET["postfix-multi-reconfigure"])){postfix_multi_reconfigure();exit;}
if(isset($_GET["postfix-multi-relayhost"])){postfix_multi_relayhost();exit;}
if(isset($_GET["postfix-multi-sasl"])){postfix_multi_ssl();exit;}
if(isset($_GET["postfix-multi-settings"])){postfix_multi_settings();exit;}
if(isset($_GET["postfix-multi-mastercf"])){postfix_multi_mastercf();exit;}
if(isset($_GET["postfix-multi-aiguilleuse"])){postfix_multi_aiguilleuse();exit;}



if(isset($_GET["postfix-multi-perform-reload"])){postfix_multi_perform_reload();exit;}
if(isset($_GET["postfix-multi-perform-restart"])){postfix_multi_perform_restart();exit;}
if(isset($_GET["postfix-multi-perform-flush"])){postfix_multi_perform_flush();exit;}
if(isset($_GET["postfix-multi-reconfigure-all"])){postfix_multi_reconfigure_all();exit;}
if(isset($_GET["postfix-multi-perform-reconfigure"])){postfix_multi_perform_reconfigure();exit;}
if(isset($_GET["restart-postfix-single"])){postfix_restart_single();exit;}
if(isset($_GET["restart-postfix-single-now"])){postfix_restart_single_now();exit;}
if(isset($_GET["postfix-single-status"])){postfix_single_status();exit;}



//virtualbox
if(isset($_GET["virtualbox-list-vms"])){virtualbox_list_vms();exit;}
if(isset($_GET["virtualbox-ini-status"])){virtualbox_status();exit;}
if(isset($_GET["virtualbox-ini-all-status"])){virtualbox_all_status();exit;}
if(isset($_GET["virtualbox-showvminfo"])){virtualbox_showvminfo();exit;}
if(isset($_GET["virtualbox-showcpustats"])){virtualbox_showcpustats();exit;} //$_GET["virtual-machine"]
if(isset($_GET["virtualbox-clonehd"])){virtualbox_clonehd();exit;}
if(isset($_GET["virtualbox-stop"])){virtualbox_stop();exit;}
if(isset($_GET["virtualbox-start"])){virtualbox_start();exit;}
if(isset($_GET["virtualbox-snapshot"])){virtualbox_snapshot();exit;}
if(isset($_GET["install-vdi"])){virtualbox_install();exit;}
if(isset($_GET["virtualbox-nats"])){virtualbox_nats();exit;}
if(isset($_GET["virtualbox-nat-del"])){virtualbox_nat_del();exit;}
if(isset($_GET["virtualbox-nats-rebuild"])){virtualbox_nat_rebuild();exit;}
if(isset($_GET["virtualbox-guestmemoryballoon"])){virtualbox_guestmemoryballoon();exit;}
if(isset($_GET["virtualbox-set-params"])){virtualbox_set_params();exit;}
if(isset($_GET["VboxPid"])){VboxPid();exit;}




if(isset($_GET["dkim-check-presence-key"])){dkim_check_presence_key();exit;}
if(isset($_GET["dkim-amavis-build-key"])){dkim_amavis_build_key();exit;}
if(isset($_GET["dkim-amavis-show-keys"])){dkim_amavis_show_keys();}
if(isset($_GET["dkim-amavis-tests-keys"])){dkim_amavis_tests_keys();}

//opengoo
if(isset($_GET["opengoouid"])){opengoo_user();exit;}
if(isset($_GET["GroupOfficeUid"])){groupoffice_user();exit;}


//safeBox
if(isset($_GET["SafeBoxUser"])){safe_box_set_user();exit;}
if(isset($_GET["mount-safebox"])){safebox_mount();exit;}
if(isset($_GET["umount-safebox"])){safebox_umount();exit;}
if(isset($_GET["safebox-logs"])){safebox_logs();exit;}
if(isset($_GET["check-safebox"])){safebox_check();exit;}

//ntpd
if(isset($_GET["ntpd-restart"])){ntpd_restart();exit;}
if(isset($_GET["ntpd-events"])){ntpd_events();exit;}

//zabix
if(isset($_GET["zabbix-restart"])){zabbix_restart();exit;}


//cyrus
if(isset($_GET["mailboxlist-domain"])){cyrus_mailboxlist_domain();exit;}
if(isset($_GET["mailboxlist"])){cyrus_mailboxlist();exit;}
if(isset($_GET["mailbox-delete"])){cyrus_mailboxdelete();exit;}
if(isset($_GET["DelMbx"])){delete_mailbox();exit;}
if(isset($_GET["cyrus-check-cyr-accounts"])){cyrus_check_cyraccounts();exit;}
if(isset($_GET["cyrus-reconfigure"])){cyrus_reconfigure();exit;}
if(isset($_GET["cyrus-get-partition-default"])){cyrus_paritition_default_path();exit;}
if(isset($_GET["cyrus-MoveDefaultToCurrentDir"])){cyrus_move_default_dir_to_currentdir();exit;}
if(isset($_GET["cyrus-SaveNewDir"])){cyrus_move_newdir();exit;}
if(isset($_GET["cyrus-rebuild-all-mailboxes"])){cyrus_rebuild_all_mailboxes();exit;}
if(isset($_GET["cyrus-imap-status"])){cyrus_imap_status();exit;}
if(isset($_GET["cyrus-change-password"])){cyrus_imap_change_password();}
if(isset($_GET["cyrus-empty-mailbox"])){cyrus_empty_mailbox();exit;}
if(isset($_GET["cyrus-to-ad"])){cyrus_activedirectory();exit;}
if(isset($_GET["cyrus-to-ad-events"])){cyrus_activedirectory_events();exit;}
if(isset($_GET["cyrus-sync-to-ad"])){cyrus_sync_to_ad();exit;}

if(isset($_GET["cyrus-mailbox-exists"])){cyrus_mailbox_exists();exit;}
if(isset($_GET["cyrus-db-config"])){cyrus_db_config();exit;}



if(isset($_GET["emailing-import-contacts"])){emailing_import_contacts();exit;}
if(isset($_GET["emailing-database-migrate-perform"])){emailing_database_migrate_export();exit;}
if(isset($_GET["emailing-builder-linker"])){emailing_builder_linker();exit;}
if(isset($_GET["emailing-builder-linker-simple"])){emailing_builder_linker_simple();exit;}
if(isset($_GET["emailing-build-emailrelays"])){emailing_build_emailrelays();exit;}
if(isset($_GET["emailrelay-ou-status"])){emailing_emailrelays_status_ou();exit;}
if(isset($_GET["emailing-make-unique-table"])){emailing_database_make_unique();exit;}



if(isset($_GET["emailing-remove-emailrelays"])){emailing_emailrelays_remove();exit;}

//restore

if(isset($_GET["cyr-restore"])){cyrus_restore_mount_dir();exit;}
if(isset($_GET["cyr-restore-container"])){cyr_restore_container();;exit;}
if(isset($_GET["cyr-restore-mailbox"])){cyr_restore_mailbox();;exit;}


//WIFI

if(isset($_GET["wifi-ini-status"])){WIFI_INI_STATUS();exit;}
if(isset($_GET["wifi-connect-point"])){WIFI_CONNECT_AP();exit;}
if(isset($_GET["wifi-eth-status"])){WIFI_ETH_STATUS();exit;}
if(isset($_GET["wifi-eth-client-check"])){WIFI_ETH_CHECK();exit;}



//openssh
if(isset($_GET["openssh-ini-status"])){SSHD_INI_STATUS();exit;}
if(isset($_GET["openssh-config"])){SSHD_GET_CONF();exit;}
if(isset($_GET["sshd-restart"])){SSHD_RESTART();exit;}
if(isset($_GET["ssh-keygen"])){SSHD_KEY_GEN();exit;}
if(isset($_GET["ssh-keygen-fingerprint"])){SSHD_KEY_FINGERPRINT();exit;}
if(isset($_GET["ssh-keygen-download"])){SSHD_KEY_DOWNLOAD_PUB();exit;}
if(isset($_GET["sshd-authorized-keys"])){SSHD_KEY_UPLOAD_PUB();exit;}

//SQUID

if(isset($_GET["squid-status"])){SQUID_STATUS();exit;}
if(isset($_GET["squid-reload"])){SQUID_RELOAD();exit;}
if(isset($_GET["squid-ini-status"])){SQUID_INI_STATUS();exit;}
if(isset($_GET["squid-restart-now"])){SQUID_RESTART_NOW();exit;}
if(isset($_GET["force-restart-squid"])){SQUID_RESTART_ALL();exit;}
if(isset($_GET["squid-build-caches"])){SQUID_CACHES();exit;}
if(isset($_GET["squid-task-caches"])){SQUID_TASK_CACHE();exit;}
if(isset($_GET["squid-wrapzap"])){SQUID_WRAPZAP();exit;}
if(isset($_GET["adzapper-compile"])){SQUID_WRAPZAP_COMPILE();exit;}
if(isset($_GET["squid-templates"])){SQUID_TEMPLATES();exit;}
if(isset($_GET["squid-rebuild"])){squid_rebuild();exit;}
if(isset($_GET["squid-reconstruct-caches"])){SQUID_CACHES_RECONSTRUCT();exit;}




if(isset($_GET["Sarg-Scan"])){SQUID_SARG_SCAN();exit;}
if(isset($_GET["squid-GetOrginalSquidConf"])){squid_originalconf();exit;}
if(isset($_GET["MalwarePatrol"])){MalwarePatrol();exit;}
if(isset($_GET["MalwarePatrol-list"])){MalwarePatrol_list();exit;}


if(isset($_GET["force-upgrade-squid"])){SQUID_FORCE_UPGRADE();exit;}
if(isset($_GET["squid-cache-infos"])){SQUID_CACHE_INFOS();exit;}

if(isset($_GET["proxy-pac-build"])){SQUID_PROXY_PAC_REBUILD();exit;}
if(isset($_GET["proxy-pac-show"])){SQUID_PROXY_PAC_SHOW();exit;}
if(isset($_GET["squid-conf-view"])){SQUID_CONF_EXPORT();exit;}




if(isset($_GET["reload-squidguard"])){SQUIDGUARD_RELOAD();exit;}
if(isset($_GET["squidguard-db-status"])){squidGuardDatabaseStatus();exit;}
if(isset($_GET["squidguard-db-maint"])){squidGuardDatabaseMaintenance();exit;}
if(isset($_GET["squidguard-db-maint-now"])){squidGuardDatabaseMaintenanceNow();exit;}



if(isset($_GET["squidguard-status"])){squidGuardStatus();exit;}
if(isset($_GET["compile-squidguard-db"])){squidGuardCompile();exit;}
if(isset($_GET["squidguard-tests"])){squidguardTests();exit;}
if(isset($_GET["reload-squidguardWEB"])){SQUIDGUARD_WEB_RELOAD();exit;}



//mldonkey

if(isset($_GET["mldonkey-ini-status"])){MLDONKEY_INI_STATUS();exit;}





if(isset($_GET["philesize-img"])){philesizeIMG();exit;}
if(isset($_GET["philesize-img-path"])){philesizeIMGPath();exit;}

//samba
if(isset($_GET["smblient"])){samba_smbclient();exit;}
if(isset($_GET["smb-logon-scripts"])){samba_logon_scripts();exit;}
if(isset($_GET["SAMBA-HAVE-POSIX-ACLS"])){SAMBA_HAVE_POSIX_ACLS();exit;}
if(isset($_GET["samba-events-list"])){samba_events_lists();exit;}
if(isset($_GET["samba-move-logs"])){samba_move_logs();exit;}
if(isset($_GET["samba-delete-logs"])){samba_delete_logs();exit;}
if(isset($_GET["winbindd-stop"])){winbindd_stop();exit;}
if(isset($_GET["samba-server-role"])){samba_server_role();exit;}
if(isset($_GET["samba-reconfigure"])){samba_reconfigure();exit;}





if(isset($_GET["add-acl-group"])){samba_add_acl_group();exit;}
if(isset($_GET["add-acl-user"])){samba_add_acl_user();exit;}
if(isset($_GET["change-acl-user"])){samba_change_acl_user();exit;}
if(isset($_GET["change-acl-group"])){samba_change_acl_group();exit;}
if(isset($_GET["delete-acl-group"])){samba_delete_acl_group();exit;}
if(isset($_GET["delete-acl-user"])){samba_delete_acl_user();exit;}
if(isset($_GET["change-acl-items"])){samba_change_acl_items();exit;}
if(isset($_GET["wbinfo-domain"])){samba_wbinfo_domain();exit;}
if(isset($_GET["net-ads-info"])){net_ads_info();exit;}




//postfix
if(isset($_GET["postfixQueues"])){postfixQueues();exit;}
if(isset($_GET["getMainCF"])){postfix_read_main();exit;}
if(isset($_GET["postfix-tail"])){postfix_tail();exit;}
if(isset($_GET["postfix-hash-tables"])){postfix_hash_tables();exit;}
if(isset($_GET["postfix-transport-maps"])){postfix_hash_transport_maps();exit;}
if(isset($_GET["postfix-hash-senderdependent"])){postfix_hash_senderdependent();exit;}
if(isset($_GET["postfix-hash-aliases"])){postfix_hash_aliases();exit;}
if(isset($_GET["postfix-hash-r-canonical"])){postfix_hash_recipient_canonical();exit;}
if(isset($_GET["postfix-bcc-tables"])){postfix_hash_bcc();exit;}
if(isset($_GET["postfix-relayhost"])){postfix_relayhost();exit;}
if(isset($_GET["postfix-smtp-sasl"])){postfix_sasl();exit;}
if(isset($_GET["postfix-multi-transport-maps"])){postfix_multi_transport_maps();exit;}

if(isset($_GET["rbl-check"])){rbl_check();exit;}
if(isset($_GET["my-rbl-check"])){my_rbl_check();exit;}


if(isset($_GET["postfix-hash-smtp-generic"])){postfix_hash_smtp_generic_maps();exit;}
if(isset($_GET["postfix-others-values"])){postfix_others_values();exit;}
if(isset($_GET["postfix-mime-header-checks"])){postfix_mime_header_checks();exit;}
if(isset($_GET["postfix-interfaces"])){postfix_interfaces();exit;}
if(isset($_GET["postfix-networks"])){postfix_single_mynetworks();exit;}
if(isset($_GET["postfix-luser-relay"])){postfix_luser_relay();exit;}
if(isset($_GET["postqueue-master-list"])){postfix_postqueue_master();exit;}
if(isset($_GET["postsuper-d-master"])){postfix_postqueue_delete_msgid();exit;}
if(isset($_GET["postsuper-r-master"])){postfix_postqueue_reprocess_msgid();exit;}
if(isset($_GET["postqueue-f"])){postfix_postqueue_f();exit;}



if(isset($_GET["smtp-domains-import"])){postfix_import_domains_ou();exit;}
if(isset($_GET["smtp-import-events"])){postfix_import_domains_ou_events();exit;}



if(isset($_GET["get-main-cf"])){postfix_get_main_cf();exit;}





if(isset($_GET["ChangeLDPSSET"])){ChangeLDPSSET();exit;}
if(isset($_GET["ASSPOriginalConf"])){ASSPOriginalConf();exit;}
if(isset($_GET["SetupCenter"])){SetupCenter();exit;}
if(isset($_GET["restart-assp"])){RestartASSPService();exit;}
if(isset($_GET["reload-assp"])){ReloadASSPService();exit;}
if(isset($_GET["restart-mailgraph"])){RestartMailGraphService();exit;}
if(isset($_GET["restart-mysql"])){RestartMysqlDaemon();exit;}


if(isset($_GET["restart-openvpn-server"])){RestartOpenVPNServer();exit;}
if(isset($_GET["openvpn-rebuild-certificate"])){openvpn_rebuild_certificates();exit;}
if(isset($_GET["OpenVPNServerSessions"])){openvpn_sesssions();exit;}
if(isset($_GET["openvpn-client-sesssions"])){openvpn_client_sesssions();exit;}
if(isset($_GET["openvpn-server-schedule"])){openvpn_server_exec_schedule();exit;}
if(isset($_GET["openvpn-status"])){openvpn_server_status();exit;}
if(isset($_GET["openvpn-clients-status"])){openvpn_clients_status();exit;}





if(isset($_GET["read-log"])){read_log();exit;}

//roundcube
if(isset($_GET["roundcube-restart"])){RoundCube_restart();exit;}
if(isset($_GET["roundcube-install-sieverules"])){RoundCube_sieverules();exit;}
if(isset($_GET["roundcube-install-contextmenu"])){RoundCube_contextmenu();exit;}
if(isset($_GET["roundcube-install-calendar"])){RoundCube_calendar();exit;}
if(isset($_GET["roundcube-install-globaladdressbook"])){RoundCube_globaladdressbook();exit;}
if(isset($_GET["roundcube-hack"])){RoundCube_hack();exit;}



if(isset($_GET["roundcube-sync"])){RoundCube_sync();exit;}



//assp
if(isset($_GET["assp-multi-load-config"])){ASSP_MULTI_CONFIG();exit;}

//rsync
if(isset($_GET["rsync-reconfigure"])){rsync_reconfigure();exit;}


//mailman
if(isset($_GET["syncro-mailman"])){MailManSync();exit;}
if(isset($_GET["restart-mailman"])){RestartMailManService();exit;}
if(isset($_GET["MailMan-List"])){MailManList();exit;}
if(isset($_GET["mailman-delete"])){MailManDelete();exit;}
if(isset($_GET["MailManSaveGlobalSettings"])){MailManSync();exit;}

//DHCPD
if(isset($_GET["restart-dhcpd"])){RestartDHCPDService();exit;}
if(isset($_GET["apply-dhcpd"])){ApplyDHCPDService();exit;}
if(isset($_GET["apply-bind"])){ApplyBINDService();exit;}
if(isset($_GET["dhcpd-status"])){dhcp_status();exit;}



if(isset($_GET["MySqlPerf"])){MySqlPerf();exit;}
if(isset($_GET["mysql-audit"])){MysqlAudit();exit;}
if(isset($_GET["RestartDaemon"])){RestartDaemon();exit;}
if(isset($_GET["restart-apache-no-timeout"])){RestartApacheNow();exit;}

//network
if(isset($_GET["SaveNic"])){Reconfigure_nic();exit;}
if(isset($_GET["nic-add-route"])){Reconfigure_routes();exit;}


if(isset($_GET["dnslist"])){DNS_LIST();exit;}
if(isset($_GET["ChangeHostName"])){ChangeHostName();exit;}
if(isset($_GET["hostToIp"])){hostToIp();exit;}



//WIFI
if(isset($_GET["iwlist"])){iwlist();exit;}
if(isset($_GET["start-wifi"])){start_wifi();exit;}

//imapSYnc

if(isset($_GET["imapsync-events"])){imapsync_events();exit;}
if(isset($_GET["imapsync-cron"])){imapsync_cron();exit;}
if(isset($_GET["imapsync-run"])){imapsync_run();exit;}
if(isset($_GET["imapsync-stop"])){imapsync_stop();exit;}
if(isset($_GET["imapsync-show"])){imapsync_show();exit;}

//gluster
if(isset($_GET["gluster-remounts"])){GLUSTER_REMOUNT();exit;}
if(isset($_GET["gluster-mounts"])){GLUSTER_MOUNT();exit;}
if(isset($_GET["gluster-update-clients"])){GLUSTER_UPDATE_CLIENTS();exit;}
if(isset($_GET["gluster-restart"])){GLUSTER_RESTART();exit;}
if(isset($_GET["gluster-delete-clients"])){GLUSTER_DELETE_CLIENTS();exit;}
if(isset($_GET["gluster-notify-clients"])){GLUSTER_NOTIFY_CLIENTS();exit;}
if(isset($_GET["glfs-is-mounted"])){GLUSTER_IS_MOUNTED();exit;}

if(isset($_GET["lessfs"])){LESSFS_RESTART();exit;}
if(isset($_GET["lessfs-mounts"])){LESSFS_MOUNTS();exit;}
if(isset($_GET["lessfs-restart"])){LESSFS_RESTART_SERVICE();exit;}


	
//cyrus
if(isset($_GET["cyrus-backup-now"])){CyrusBackupNow();exit;}
if(isset($_GET["restart-cyrus"])){RestartCyrusImapDaemon();exit;}
if(isset($_GET["reload-cyrus"])){ReloadCyrus();exit;}
if(isset($_GET["reconfigure-cyrus"])){ReconfigureCyrusImapDaemon();exit;} // --reconfigure-cyrus
if(isset($_GET["reconfigure-cyrus-debug"])){ReconfigureCyrusImapDaemonDebug();exit;} // --reconfigure-cyrus
if(isset($_GET["restart-cyrus-debug"])){rRestartCyrusImapDaemonDebug();exit;} // --reconfigure-cyrus
if(isset($_GET["repair-mailbox"])){CyrusRepairMailbox();exit;}
if(isset($_GET["cyr-restore-computer"])){cyr_restore_computer();exit;}

//backup
if(isset($_GET["backup-sql-test"])){backup_sql_tests();exit;}
if(isset($_GET["backup-build-cron"])){backup_build_cron();exit;}
if(isset($_GET["backup-task-run"])){backup_task_run();exit;}


if(isset($_GET["backuppc-ini-status"])){BACKUPPPC_INI_STATUS();exit;}
if(isset($_GET["backuppc-affect"])){backuppc_affect();exit;}
if(isset($_GET["backuppc-comp"])){backuppc_load_computer_config();exit;}
if(isset($_GET["backuppc-save-computer"])){backuppc_save_computer_config();exit;}
if(isset($_GET["restart-backuppc"])){backuppc_restart();exit;}
if(isset($_GET["backuppc-computer-infos"])){backuppc_computer_infos();exit;}


//apache
if(isset($_GET["restart-groupware-server"])){RestartGroupwareWebServer();exit;}
if(isset($_GET["philesight-perform"])){philesight_perform();exit;}

//postfix
if(isset($_GET["headers-check-postfix"])){PostfixHeaderCheck();exit;}
if(isset($_GET["SaveMaincf"])){SaveMaincf();exit;}
if(isset($_GET["sasl-finger"])){SASL_FINGER();exit;}
if(isset($_GET["pluginviewer"])){SASL_pluginviewer();exit;}


if(isset($_GET["reconfigure-postfix"])){postfix_reconfigure();exit;}
if(isset($_GET["postfix-stat"])){postfix_stat();exit;}
if(isset($_GET["postfix-multi-queues"])){postfix_multi_queues();exit;}
if(isset($_GET["postfix-mutli-stat"])){postfix_multi_stat();exit;}
if(isset($_GET["postfix-multi-configure-ou"])){postfix_multi_configure();exit;}
if(isset($_GET["postfix-multi-disable"])){postfix_multi_disable();exit;}
if(isset($_GET["postfix-restricted-users"])){postfix_restricted_users();exit;}
if(isset($_GET["postfix-multi-postqueue"])){postfix_multi_postqueue();exit;}
if(isset($_GET["postfix-multi-cfdb"])){postfix_multi_cfdb();exit;}




if(isset($_GET["smtp-hack-reconfigure"])){smtp_hack_reconfigure();exit;}

//organizations
if(isset($_GET["upload-organization"])){ldap_upload_organization();exit;}


//cups
if(isset($_GET["cups-delete-printer"])){cups_delete_printer();exit;}
if(isset($_GET["cups-add-printer"])){cups_add_printer();exit;}

//samba
if(isset($_GET["samba-save-config"])){samba_save_config();exit;}
if(isset($_GET["samba-build-homes"])){samba_build_homes();exit;}
if(isset($_GET["restart-samba"])){samba_restart();exit;}
if(isset($_GET["restart-samba-now"])){samba_restart_now();exit;}
if(isset($_GET["Debugpdbedit"])){samba_pdbedit_debug();exit;}
if(isset($_GET["pdbedit"])){samba_pdbedit();exit;}
if(isset($_GET["pdbedit-group"])){samba_pdbedit_group();exit;}
if(isset($_GET["samba-status"])){samba_status();exit;}
if(isset($_GET["samba-shares-list"])){samba_shares_list();exit;}
if(isset($_GET["samba-synchronize"])){samba_synchronize();exit;}
if(isset($_GET["samba-change-sid"])){samba_change_sid();exit;}
if(isset($_GET["samba-original-conf"])){samba_original_config();exit;}
if(isset($_GET["GetLocalSid"])){GET_LOCAL_SID();exit;}

if(isset($_GET["smbpass"])){samba_password();exit;}
if(isset($_GET["home-single-user"])){samba_build_home_single();exit;}

//dropbox
if(isset($_GET["dropbox-status"])){dropbox_status();exit;}
if(isset($_GET["dropbox-service-status"])){dropbox_service_status();exit;}
if(isset($_GET["dropbox-service-uri"])){dropbox_service_uri();exit;}
if(isset($_GET["dropbox-service-dump"])){dropbox_files_status();exit;}


//

//squid;
if(isset($_GET["squidnewbee"])){squid_config();exit;}
if(isset($_GET["cicap-reconfigure"])){cicap_reconfigure();exit;}
if(isset($_GET["cicap-reload"])){cicap_reload();exit;}
if(isset($_GET["cicap-restart"])){cicap_restart();exit;}
if(isset($_GET["MalwarePatrolDatabasesCount"])){MalwarePatrolDatabasesCount();exit;}

if(isset($_GET["artica-filter-reload"])){ReloadArticaFilter();exit;}
if(isset($_GET["artica-policy-restart"])){RestartArticaPolicy();exit;}
if(isset($_GET["artica-policy-reload"])){ReloadArticaPolicy();exit;}



if(isset($_GET["dirdir"])){dirdir();exit;}
if(isset($_GET["du-dir-size"])){du_dir_size();exit;}



if(isset($_GET["view-file-logs"])){ViewArticaLogs();exit;}
if(isset($_GET["ExecuteImportationFrom"])){ExecuteImportationFrom();exit;}
if(isset($_GET["squid-reconfigure"])){RestartSquid();exit;}
if(isset($_GET["mempy"])){mempy();exit;}
if(isset($_GET["EnableEmergingThreats"])){EnableEmergingThreats();exit;}
if(isset($_GET["EnableEmergingThreatsBuild"])){EnableEmergingThreatsBuild();exit;}

//apache-groupware
if(isset($_GET["reload-apache-groupware"])){ReloadApacheGroupWare();exit;}
if(isset($_GET["build-vhosts"])){BuildVhosts();exit;}
if(isset($_GET["vhost-delete"])){DeleteVHosts();exit;}
if(isset($_GET["install-joomla"])){JOOMLA_INSTALL();exit;}

if(isset($_GET["replicate-performances-config"])){ReplicatePerformancesConfig();exit;}
if(isset($_GET["reload-dansguardian"])){reload_dansguardian();exit;}
if(isset($_GET["reload-ufdbguard"])){reload_ufdbguard();exit;}
if(isset($_GET["ufdbguard-recompile-missing-dbs"])){ufdbguard_compile_missing_dbs();exit;}
if(isset($_GET["ufdbguard-recompile-dbs"])){ufdbguard_compile_all_dbs();exit;}
if(isset($_GET["ufdbguard-compile-schedule"])){ufdbguard_compile_schedule();exit;}




if(isset($_GET["ufdbguard-compilator-events"])){ufdbguard_compilator_events();exit;}



if(isset($_GET["dansguardian-template"])){dansguardian_template();exit;}
if(isset($_GET["dansguardian-get-template"])){dansguardian_get_template();exit;}


if(isset($_GET["searchww-cat"])){dansguardian_search_categories();exit;}
if(isset($_GET["export-community-categories"])){dansguardian_community_categories();exit;}
if(isset($_GET["create-user-folder"])){directory_create_user();exit;}
if(isset($_GET["delete-user-folder"])){directory_delete_user();exit;}



//disks
if(isset($_GET["disks-list"])){disks_list();exit;}
if(isset($_GET["usb-scan-write"])){USB_SCAN_WRITE();exit;}
if(isset($_GET["lvm-lvs"])){lvs_scan();exit;}
if(isset($_GET["sfdisk-dump"])){sfdisk_dump();exit;}
if(isset($_GET["mkfs"])){mkfs();exit;}
if(isset($_GET["parted-print"])){parted_print();exit;}
if(isset($_GET["format-disk-unix"])){format_disk_unix();exit;}
if(isset($_GET["lvs-mapper"])){LVM_LVS_DEV_MAPPER();exit;}
if(isset($_GET["check-dev"])){DEV_CHECK();}
if(isset($_GET["fstab-add"])){fstab_add();exit;}
if(isset($_GET["fstablist"])){fstab_list();exit;}
if(isset($_GET["path-acls"])){acls_infos();exit;}
if(isset($_GET["chmod-access"])){chmod_access();exit;}
if(isset($_GET["acls-status"])){acls_status();exit;}
if(isset($_GET["acls-apply"])){acls_apply();exit;}
if(isset($_GET["acls-delete"])){acls_delete();exit;}
if(isset($_GET["acls-rebuild"])){acls_rebuild();exit;}



if(isset($_GET["IsDir"])){IsDir();exit;}
if(isset($_GET["hdparm-infos"])){hdparm_infos();exit;}
if(isset($_GET["disk-change-label"])){disks_change_label();exit;}
if(isset($_GET["disk-get-label"])){disks_get_label();exit;}
if(isset($_GET["udevinfos"])){udevinfos();exit;}



// cmd.php?fstab-acl=yes&acl=$acl&dev=$dev
if(isset($_GET["fstab-acl"])){fstab_acl();exit;}
if(isset($_GET["fstab-quota"])){fstab_quota();exit;}
if(isset($_GET["fstab-remove"])){fstab_del();exit;}
if(isset($_GET["DiskInfos"])){DiskInfos();exit;}
if(isset($_GET["fstab-get-mount-point"])){fstab_get_mount_point();exit;}
if(isset($_GET["get-mounted-path"])){disk_get_mounted_point();exit;}
if(isset($_GET["fdisk-build-big-partitions"])){disk_format_big_partition();}
if(isset($_GET["chown"])){directory_chown();exit;}
if(isset($_GET["quotastats"])){quotastats();exit;}
if(isset($_GET["repquota"])){repquota();exit;}
if(isset($_GET["setquota"])){setquota();exit;}
if(isset($_GET["quotas-recheck"])){quotasrecheck();exit;}





if(isset($_GET["umount-disk"])){umount_disk();exit;}
if(isset($_GET["lvremove"])){LVM_REMOVE();exit;}
if(isset($_GET["fdiskl"])){fdisk_list();exit;}
if(isset($_GET["lvmdiskscan"])){lvmdiskscan();exit;}
if(isset($_GET["pvscan"])){pvscan();exit;}
if(isset($_GET["vgs-info"])){LVM_VGS_INFO();exit;}
if(isset($_GET["vg-disks"])){LVM_VG_DISKS();exit;}
if(isset($_GET["lvdisplay"])){LVM_LV_DISPLAY();exit;}



if(isset($_GET["lvm-unlink-disk"])){LVM_UNLINK_DISK();exit;}
if(isset($_GET["lvm-link-disk"])){LVM_LINK_DISK();exit;}
if(isset($_GET["vgcreate-dev"])){LVM_CREATE_GROUP();exit;}
if(isset($_GET["DirectorySize"])){disk_directory_size();exit;}
if(isset($_GET["filemd5"])){FILE_MD5();exit;}
if(isset($_GET["read-file"])){ReadFromfile();exit;}



if(isset($_GET["lvs-all"])){LVM_lVS_INFO_ALL();exit;}
if(isset($_GET["lv-resize-add"])){LVM_LV_ADDSIZE();exit;}
if(isset($_GET["lv-resize-red"])){LVM_LV_DELSIZE();exit;}
if(isset($_GET["disk-ismounted"])){disk_ismounted();exit;}
if(isset($_GET["disks-quotas-list"])){disks_quotas_list();exit;}
if(isset($_GET["dfmoinshdev"])){disks_dfmoinshdev();exit;}



if(isset($_GET["filesize"])){file_size();exit;}
if(isset($_GET["filetype"])){file_type();exit;}
if(isset($_GET["mime-type"])){mime_type();exit;}

if(isset($_GET["sync-remote-smtp-artica"])){postfix_sync_artica();exit;}

//etc/hosts
if(isset($_GET["etc-hosts-open"])){etc_hosts_open();exit;}
if(isset($_GET["etc-hosts-add"])){etc_hosts_add();exit;}
if(isset($_GET["etc-hosts-del"])){etc_hosts_del();exit;}
if(isset($_GET["etc-hosts-del-by-values"])){etc_hosts_del_by_values();exit;}



if(isset($_GET["full-hostname"])){hostname_full();exit;}

//computers
if(isset($_GET["nmap-scan"])){nmap_scan();exit;}


//users UNix
if(isset($_GET["unixLocalUsers"])){PASSWD_USERS();exit;}


//tcp
if(isset($_GET["ifconfig-interfaces"])){ifconfig_interfaces();exit;}
if(isset($_GET["ifconfig-all"])){ifconfig_all();exit;}
if(isset($_GET["ifconfig-all-ips"])){ifconfig_all_ips();exit;}
if(isset($_GET["resolv-conf"])){resolv_conf();exit;}
if(isset($_GET["myos"])){MyOs();exit;}
if(isset($_GET["lspci"])){lspci();exit;}
if(isset($_GET["freemem"])){freemem();exit;}
if(isset($_GET["dfmoinsh"])){dfmoinsh();exit;}
if(isset($_GET["printenv"])){printenv();exit;}
if(isset($_GET["GenerateCert"])){GenerateCert();exit;}
if(isset($_GET["all-status"])){GLOBAL_STATUS();exit;}
if(isset($_GET["procstat"])){procstat();exit;}
if(isset($_GET["nic-infos"])){TCP_NIC_INFOS();exit;}



if(isset($_GET["ip-to-mac"])){ip_to_mac();exit;}
if(isset($_GET["arp-ip"])){arp_and_ip();exit;}
if(isset($_GET["hostToMac"])){hostToMac();exit;}
if(isset($_GET["browse-computers-import-list"])){import_computer_from_list();exit;}




if(isset($_GET["refresh-status"])){RefreshStatus();exit;}

if(isset($_GET["SpamassassinReload"])){reloadSpamAssassin();exit;}
if(isset($_GET["SpamAssassin-Reload"])){reloadSpamAssassin();exit;}
if(isset($_GET["spamass-check"])){spamassassin_check();exit;}
if(isset($_GET["spamass-trust-nets"])){spamassassin_trust_networks();exit;}
if(isset($_GET["SpamAssDBVer"])){SpamAssDBVer();exit;}
if(isset($_GET["spamass-build"])){spamassassin_rebuild();exit;}
if(isset($_GET["spamass-test"])){spamassassin_tests();exit;}




if(isset($_GET["SetupIndexFile"])){SetupIndexFile();exit;}
if(isset($_GET["install-web-services"])){InstallWebServices();exit;}
if(isset($_GET["install-web-service-unique"])){InstallWebServiceUnique();exit;}
if(isset($_GET["ForceRefreshLeft"])){ForceRefreshLeft();exit;}
if(isset($_GET["ForceRefreshRight"])){ForceRefreshRight();exit;}


if(isset($_GET["aptupgrade"])){AptGetUpgrade();exit;}
if(isset($_GET["perform-autoupdate"])){artica_update();exit;}


if(isset($_GET["SmtpNotificationConfigRead"])){SmtpNotificationConfigRead();exit;}
if(isset($_GET["testnotif"])){testnotif();exit;}
if(isset($_GET["ComputerRemoteRessources"])){ComputerRemoteRessources();exit;}
if(isset($_GET["free-cache"])){FreeCache();exit;}
if(isset($_GET["DumpPostfixQueue"])){DumpPostfixQueue();exit;}
if(isset($_GET["smtp-whitelist"])){SMTP_WHITELIST();exit;}
if(isset($_GET["LaunchNetworkScanner"])){LaunchNetworkScanner();exit;}
if(isset($_GET["idofUser"])){idofUser();exit;}
if(isset($_GET["php-rewrite"])){rewrite_php();exit;}

if(isset($_GET["B64-dirdir"])){dirdirBase64();exit;}
if(isset($_GET["Dir-Files"])){Dir_Files();exit;}
if(isset($_GET["filestat"])){filestat();exit;}
if(isset($_GET["create-folder"])){folder_create();exit;}
if(isset($_GET["folder-remove"])){folder_delete();exit;}
if(isset($_GET["file-content"])){file_content();exit;}
if(isset($_GET["file-remove"])){file_remove();exit;}

//CLUSTERS
if(isset($_GET["notify-clusters"])){CLUSTER_NOTIFY();exit;}
if(isset($_GET["cluster-restart-notify"])){CLUSTER_CLIENT_RESTART_NOTIFY();exit;}
if(isset($_GET["cluster-client-list"])){CLUSTER_CLIENT_LIST();exit;}
if(isset($_GET["cluster-delete"])){CLUSTER_DELETE();exit;}
if(isset($_GET["cluster-add"])){CLUSTER_ADD();exit;}

//computers
if(isset($_GET["computers-import-nets"])){COMPUTERS_IMPORT_ARTICA();exit;}
if(isset($_GET["smbclientL"])){smbclientL();exit;}

//paths 
if(isset($_GET["SendmailPath"])){SendmailPath();exit;}
if(isset($_GET["release-quarantine"])){release_quarantine();exit;}

//policyd-weight
if(isset($_GET["PolicydWeightReplicConF"])){Restart_Policyd_Weight();exit;}

//dansguardian
if(isset($_GET["dansguardian-update"])){dansguardian_update();exit;}
if(isset($_GET["shalla-update-now"])){shalla_update();exit;}

$uri=$_GET["uri"];

switch ($uri) {
	case "GlobalApplicationsStatus":GlobalApplicationsStatus();exit;break;
	case "artica_version":artica_version();exit;break;
	case "daemons_status":daemons_status();exit;break;
	case "pid":echo "<articadatascgi>".getmypid()."</articadatascgi>";exit;break;
	case "myhostname";myhostname();exit;break;
	
	default:
		;
	break;
}

while (list ($num, $line) = each ($_GET)){
	$f[]="$num=$line";
}

writelogs_framework("unable to understand query !!!!!!!!!!!..." .@implode(",",$f),"main()",__FILE__,__LINE__);
die();

function SMTP_WHITELIST(){
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/postfix.whitelist.php");
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.opendkim.php --whitelist");
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.spamassassin.php --whitelist");
}

function artica_update_query_fileslogs(){
	$unix=new unix();
	$array=$unix->DirFiles("/var/log/artica-postfix");
	echo "<articadatascgi>".base64_encode(serialize($array))."</articadatascgi>";	
}
function artica_update_query_logs(){
	$_GET["file"]=str_replace("../","",$_GET["file"]);
	$array=explode("\n",@file_get_contents("/var/log/artica-postfix/{$_GET["file"]}"));
	echo "<articadatascgi>".base64_encode(serialize($array))."</articadatascgi>";	
}


function nmap_scan(){
	 $unix=new unix();
	 $computer=$unix->shellEscapeChars($_GET["nmap-scan"]);
	 writelogs_framework("Scan the computer:{$_GET["nmap-scan"]}=$computer",__FUNCTION__,__FILE__,__LINE__);
	 $cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.nmapscan.php $computer";
   	 writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	 exec($cmd,$results);
	 echo "<articadatascgi>". @implode("\n",$results)."</articadatascgi>";		
}


function acls_infos(){
	$unix=new unix();
	$path=base64_decode($_GET["path-acls"]);
	$getfacl=$unix->find_program("getfacl");
	if($getfacl==null){return false;}
	exec("$getfacl --tabular \"$path\"",$results);
	
	while (list ($num, $line) = each ($results)){
		if(preg_match("#USER\s+(.+?)\s+(.*)#",$line,$re)){
			$array["OWNER"]=array("NAME"=>$re[1],"RIGHTS"=>$re[2],"DEFAULT"=>true);
			continue;
		}
		
		if(preg_match("#GROUP\s+(.+?)\s+(.*)#",$line,$re)){
			$array["GROUP"]=array("NAME"=>$re[1],"RIGHTS"=>$re[2],"DEFAULT"=>true);
			continue;
		}	

		if(preg_match("#other\s+(.+?)\s+(.*)#",$line,$re)){
			$array["other"]=array("NAME"=>$re[1],"RIGHTS"=>$re[2]);
			continue;
		}			

		if(preg_match("#user\s+(.+?)\s+\s+(.*)#",$line,$re)){
			$array["users"][]=array("NAME"=>$re[1],"RIGHTS"=>$re[2]);
			continue;
		}
		
		if(preg_match("#group\s+(.+?)\s+\s+(.*)#",$line,$re)){
			$array["groups"][]=array("NAME"=>$re[1],"RIGHTS"=>$re[2]);
			continue;
		}		

		
	}
	
	echo "<articadatascgi>".base64_encode(serialize($array))."</articadatascgi>";
	
}

function IsDir(){
	$_GET["IsDir"]=base64_decode($_GET["IsDir"]);
	if(is_dir($_GET["IsDir"])){
		echo "<articadatascgi>".base64_encode("TRUE")."</articadatascgi>";
	}
}


function file_size(){
	$unix=new unix();
	$_GET["filesize"]=$unix->shellEscapeChars($_GET["filesize"]);
	exec($unix->find_program("stat")." {$_GET["filesize"]} ",$results);
	while (list ($num, $line) = each ($results)){
		if(preg_match("#Size:\s+([0-9]+)\s+Blocks#",$line,$re)){
			$res=$re[1];break;
		}
	}
	echo "<articadatascgi>$res</articadatascgi>";	
}

function file_type(){
$unix=new unix();
$filetype=base64_decode($_GET["filetype"]);
	exec($unix->find_program("file")." \"$filetype\" ",$results);	
while (list ($num, $line) = each ($results)){
		if(preg_match("#.+?:\s+(.+?)$#",$line,$re)){
			$res=$re[1];break;
		}
	}
	echo "<articadatascgi>".base64_encode($res)."</articadatascgi>";	
}

function mime_type(){
$unix=new unix();
$filetype=base64_decode($_GET["mime-type"]);
	exec($unix->find_program("file")." -i -b \"$filetype\" ",$results);	
while (list ($num, $line) = each ($results)){
		if(preg_match("#.+?;.+?$#",$line,$re)){
			$res=$line;break;
		}
	}
	echo "<articadatascgi>".base64_encode($res)."</articadatascgi>";	
}	


function COMPUTERS_IMPORT_ARTICA(){
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.import-networks.php");
	
}
function ReloadCyrus(){
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --reload-cyrus");
}

function import_computer_from_list(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.computer.scan.php --import-list");
}

function format_disk_unix(){
	$logs=md5($_GET["format-disk-unix"]);
	@unlink("/usr/share/artica-postfix/ressources/logs/$logs.format");
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --format-disk-unix {$_GET["format-disk-unix"]} --verbose >/usr/share/artica-postfix/ressources/logs/$logs.format 2>&1");
}
function read_log(){
	if(!is_file("/usr/share/artica-postfix/ressources/logs/{$_GET["read-log"]}")){
		writelogs_framework("unable to stat /usr/share/artica-postfix/ressources/logs/{$_GET["read-log"]}");
		return;
	}
echo "<articadatascgi>". @file_get_contents("/usr/share/artica-postfix/ressources/logs/{$_GET["read-log"]}")."</articadatascgi>";	
}


function StartServiceCMD(){
	$cmd=$_GET["start-service-name"];
	exec("/etc/init.d/artica-postfix start $cmd",$results);
	$datas=base64_encode(serialize($results));
	echo "<articadatascgi>$datas</articadatascgi>";	
}
function StopServiceCMD(){
	$cmd=$_GET["stop-service-name"];
	exec("/etc/init.d/artica-postfix stop $cmd",$results);
	$datas=base64_encode(serialize($results));
	echo "<articadatascgi>$datas</articadatascgi>";	
}

function StartAllServices(){
	$unix=new unix();
	$d=$unix->ServicesCMDArray();
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix start");
	while (list ($num, $cmd) = each ($d)){
		sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix start $cmd");	
	}
	
	
}
function DEV_CHECK(){
	$dev=$_GET["check-dev"];
	if(is_link($dev)){
		$link=readlink($dev);
		$dev=str_replace("../mapper","/dev/mapper",$link);
		echo "<articadatascgi>$dev</articadatascgi>";
	}else{
		echo "<articadatascgi>$dev</articadatascgi>";
	}
}

function SQUID_STATUS(){
	exec("/usr/share/artica-postfix/bin/artica-install --squid-status",$results);
	echo "<articadatascgi>". implode("\n",$results)."</articadatascgi>";
}
function SQUID_INI_STATUS(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --all-squid --nowachdog",$results);
	echo "<articadatascgi>". base64_encode(implode("\n",$results))."</articadatascgi>";
}
function SQUID_RELOAD(){
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --squid-reload");
}

function SSHD_INI_STATUS(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --openssh --nowachdog",$results);
	echo "<articadatascgi>". base64_encode(implode("\n",$results))."</articadatascgi>";
}

function STUNNEL_INI_STATUS(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --stunnel --nowachdog",$results);
	echo "<articadatascgi>". base64_encode(implode("\n",$results))."</articadatascgi>";
}

function STUNNEL_RESTART(){
	sys_THREAD_COMMAND_SET( "/etc/init.d/artica-postfix restart stunnel");
}


function MLDONKEY_INI_STATUS(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --mldonkey --nowachdog",$results);
	echo "<articadatascgi>". base64_encode(implode("\n",$results))."</articadatascgi>";
}
function BACKUPPPC_INI_STATUS(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --backuppc --nowachdog",$results);
	echo "<articadatascgi>". base64_encode(implode("\n",$results))."</articadatascgi>";
}


function MILTER_GREYLIST_INI_STATUS(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --milter-greylist --nowachdog",$results);
	echo "<articadatascgi>". base64_encode(implode("\n",$results))."</articadatascgi>";	
	
}

function WIFI_INI_STATUS(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --wifi --nowachdog",$results);
	echo "<articadatascgi>". base64_encode(implode("\n",$results))."</articadatascgi>";	
}


function SQUID_FORCE_UPGRADE(){
	sys_THREAD_COMMAND_SET( "/usr/share/artica-postfix/bin/artica-make APP_SQUID --reconfigure");
}

function artica_update(){
	sys_THREAD_COMMAND_SET( "/usr/share/artica-postfix/bin/artica-update --update --force");
}

function SQUID_SARG_SCAN(){
	$unix=new unix();
	$sarg=$unix->find_program("sarg");
	if(!is_file($sarg)){return null;}
	exec("/usr/share/artica-postfix/bin/artica-install --sarg-scan",$results);
	$datas=base64_encode(serialize($results));
	echo "<articadatascgi>$datas</articadatascgi>";
	
}

function disk_ismounted(){
	$unix= new unix();
	$dev=$_GET["dev"];
	if(is_link($dev)){
		$link=@readlink($dev);
		$dev2=str_replace("../mapper","/dev/mapper",$link);
	}
	writelogs_framework("$dev OR $dev2 ",__FUNCTION__,__FILE__,__LINE__);
	if(!$unix->DISK_MOUNTED($dev)){
		if($dev2<>null){
			if($unix->DISK_MOUNTED($dev2)){
				echo "<articadatascgi>TRUE</articadatascgi>";
				return ;
			}
		}
	}else{
		echo "<articadatascgi>TRUE</articadatascgi>";
		return;
	}
	
	echo "<articadatascgi>FALSE</articadatascgi>";
	
}


function rsync_reconfigure(){
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.rsync-lvm.php");
}

function DeleteVHosts(){
	$unix=new unix();
	$tmp=$unix->FILE_TEMP();
	shell_exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.www.install.php remove {$_GET["vhost-delete"]} --verbose >$tmp 2>&1");
	echo "<articadatascgi>". @file_get_contents($tmp)."</articadatascgi>";
	@unlink($tmp);
	
}

function fstab_add(){
	$dev=$_GET["dev"];
	$mount=$_GET["mount"];
	$unix=new unix();
	writelogs_framework("Add Fstab $dev -> $mount ",__FUNCTION__,__FILE__,__LINE__);
	$unix->AddFSTab($dev,$mount);
	
}

function fstab_del(){
	$dev=$_GET["dev"];
	$unix=new unix();
	$unix->DelFSTab($dev);
}

function fstab_get_mount_point(){
	$dev=$_GET["dev"];
	$unix=new unix();
	$datas=$unix->GetFSTabMountPoint($dev);
	writelogs_framework(count($datas)." mounts points",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>".base64_encode(serialize($datas));
	echo "</articadatascgi>";	
}

function DiskInfos(){
	
	$dev=$_GET["DiskInfos"];
	$unix=new unix();
	exec($unix->find_program("df")." -h $dev",$results);
while (list ($num, $line) = each ($results)){
		if(preg_match("#(.+?)\s+([0-9-A-Z,\.]+)\s+([0-9-A-Z,\.]+)\s+([0-9-A-Z,\.]+)\s+([0-9,\.]+)%\s+(.+)$#i",$line,$re)){
			if($re[6]=="/dev"){continue;}
			$array["SIZE"]=$re[2];
			$array["USED"]=$re[3];
			$array["FREE"]=$re[4];
			$array["POURC"]=$re[5];
			$array["MOUNTED"]=$re[6];
			echo "<articadatascgi>".base64_encode(serialize($array))."</articadatascgi>";
			break;
		}
	}	
	
}

function fstab_list(){
	$datas=explode("\n",@file_get_contents("/etc/fstab"));
	echo "<articadatascgi>".base64_encode(serialize($datas))."</articadatascgi>";
}

function ViewArticaLogs(){
	$datas=@file_get_contents("/var/log/artica-postfix/{$_GET["view-file-logs"]}");
	echo "<articadatascgi>$datas</articadatascgi>";
	}
	
function ExecuteImportationFrom(){
	$path=$_GET["ExecuteImportationFrom"];
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.cyrus-restore.php \"$path\"");
}
function LaunchNetworkScanner(){
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.scan-networks.php");
}

function CLUSTER_NOTIFY(){
	$server=$_GET["notify-clusters"];
	shell_exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.gluster.php --notify-client $server");
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.gluster.php");
}
function CLUSTER_CLIENT_RESTART_NOTIFY(){
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.gluster.php --cluster-restart-notify");
}

function RoundCube_restart(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart roundcube");
}
function RoundCube_sieverules(){
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.roundcube.php --sieverules");
}
function RoundCube_contextmenu(){
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.roundcube.php --contextmenu");
}


function RoundCube_sync(){
	$ou=base64_decode($_GET["ou"]);
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.roundcube.php");
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.groupwares.db.php \"$ou\"");
}



function RoundCube_calendar(){
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.roundcube.php --calendar");
}
function RoundCube_globaladdressbook(){
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.roundcube.php --addressbook");
}

function RoundCube_hack(){
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.roundcube.php --hacks");
}

function opengoo_user(){
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.opengoo.php --user={$_GET["opengoouid"]}");
}
function groupoffice_user(){
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.GroupOffice.php --user={$_GET["GroupOfficeUid"]}");
}
function ntpd_restart(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart ntpd");
}

	
function ntpd_events(){
	$unix=new unix();
	$syslog=$unix->LOCATE_SYSLOG_PATH();
	$tmpf=$unix->FILE_TEMP();
	$cmd=$unix->find_program("tail")." -n 5000 $syslog|". $unix->find_program("grep")." ntpd >$tmpf 2>&1";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);
	$results=explode("\n",@file_get_contents($tmpf));
	@unlink($tmpf);
	writelogs_framework(count($results),__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	
}
function ReloadArticaFilter(){
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.maincf.php --artica-filter --reload");
}

function postfix_perso_settings(){
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.maincf.php --perso-settings --reload");
}

function postfix_smtpd_restrictions(){
	writelogs_framework("/usr/share/artica-postfix/exec.postfix.maincf.php --smtpd-restrictions --reload",__FUNCTION__,__FILE__,__LINE__);
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.maincf.php --smtpd-restrictions --reload");
}

function Reconfigure_nic(){
	
	
	$dns1=$_GET["dns1"];
	$dns2=$_GET["dns2"];
	$routes=unserialize(base64_decode($_GET["routes"]));
	@file_put_contents("/etc/artica-postfix/resolv.{$_GET["SaveNic"]}.tmp","$dns1\n$dns2\n");
	$unix=new unix();

	if(is_file("/etc/network/interfaces")){		
		$array["IPADDR"]=$_GET["ip"];
		$array["NETMASK"]=$_GET["net"];
		$array["GATEWAY"]=$_GET["gw"];
		if($_GET["dhcp"]=="yes"){$array["BOOTPROTO"]="dhcp";}else{$array["BOOTPROTO"]="static";}
		$array["GATEWAY"]=$_GET["GATEWAY"];
		$array["BROADCAST"]=$_GET["broadcast"];
		$array["DNS1"]=$_GET["dns1"];
		$array["DN2"]=$_GET["dns2"];
		$array["ROUTES"]=unserialize(base64_decode($_GET["routes"]));
		$unix->NETWORK_DEBIAN_SAVE($_GET["SaveNic"],$array);
		return;
	}
	
	$unix->NETWORK_ADD_ROUTE($_GET["SaveNic"],$routes);
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --reconfigure-nic {$_GET["SaveNic"]} {$_GET["ip"]} {$_GET["net"]} {$_GET["gw"]} {$_GET["dhcp"]} {$_GET["broadcast"]}");
}

function Reconfigure_routes(){
	$unix=new unix();
	$routes=unserialize(base64_decode($_GET["routes"]));
	writelogs_framework("{$_GET["nic"]} Adding ".count($routes)." routes..",__FUNCTION__,__FILE__,__LINE__);
	
	if(is_file("/etc/network/interfaces")){	
		$array["ROUTES"]=$routes;
		$unix->NETWORK_DEBIAN_SAVE($_GET["nic"],$array);
		return;
	}
	$unix->NETWORK_ADD_ROUTE($_GET["nic"],$routes);	
	if(is_file("/etc/init.d/networking")){sys_THREAD_COMMAND_SET("/etc/init.d/networking force-reload");}
	if(is_file("/etc/init.d/network")){sys_THREAD_COMMAND_SET("/etc/init.d/network restart");}	
}

function postfix_sync_artica(){
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.smtp.export.users.php --sync");
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.maincf.php --reconfigure");
}




function CLUSTER_CLIENT_LIST(){
	$unix=new unix();
	
	$files=$unix->DirFiles("/etc/artica-cluster");
while (list ($num, $path) = each ($files)){
		if(preg_match("#clusters-(.+)#",$path,$re)){
			$ff[]="{$re[1]}";
		}
	}
	if(is_array($ff)){
		echo "<articadatascgi>" . implode("\n",$ff)."</articadatascgi>";
	}
}

function CLUSTER_DELETE(){
	$server=$_GET["cluster-delete"];
	@unlink("/etc/artica-cluster/clusters-$server");
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.gluster.php --notify-all-clients");
	
}
function CLUSTER_ADD(){
	$server=$_GET["cluster-add"];
	@file_put_contents("/etc/artica-cluster/clusters-$server","#");
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.gluster.php --notify-all-clients");
	
}

function mempy(){
	shell_exec("/usr/share/artica-postfix/bin/ps_mem.py >/tmp/mempy.txt 2>&1");
	echo "<articadatascgi>". @file_get_contents("/tmp/mempy.txt")."</articadatascgi>";
}

function SmtpNotificationConfigRead(){
	$unix=new unix();
	$size=$unix->file_size("/etc/artica-postfix/smtpnotif.conf");
	$datas=trim(@file_get_contents("/etc/artica-postfix/smtpnotif.conf"));
	echo "<articadatascgi>$datas</articadatascgi>";
}

function safebox_mount(){
	if(is_file("/var/log/artica-postfix/safebox.{$_GET["uid"]}.debug")){@unlink("/var/log/artica-postfix/safebox.{$_GET["uid"]}.debug");}
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.safebox.php --init {$_GET["uid"]}");
}
function safebox_umount(){
	if(is_file("/var/log/artica-postfix/safebox.{$_GET["uid"]}.debug")){@unlink("/var/log/artica-postfix/safebox.{$_GET["uid"]}.debug");}
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.safebox.php --umount {$_GET["uid"]}");
}
function safebox_check(){
	if(is_file("/var/log/artica-postfix/safebox.{$_GET["uid"]}.debug")){@unlink("/var/log/artica-postfix/safebox.{$_GET["uid"]}.debug");}
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.safebox.php --fsck {$_GET["uid"]}");	
}

function safe_box_set_user(){
	if($_GET["uid"]==null){writelogs_framework("no user set",__FUNCTION__,__FILE__,__LINE__);}
	if(is_file("/var/log/artica-postfix/safebox.{$_GET["uid"]}.debug")){@unlink("/var/log/artica-postfix/safebox.{$_GET["uid"]}.debug");}
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.safebox.php --init {$_GET["uid"]}");
}
function safebox_logs(){
	$uid=$_GET["uid"];
	if(!is_file("/var/log/artica-postfix/safebox.$uid.debug")){
	writelogs_framework("unable to stat /var/log/artica-postfix/safebox.$uid.debug",__FUNCTION__,__FILE__,__LINE__);
	}
	$f=@file_get_contents("/var/log/artica-postfix/safebox.$uid.debug");
	$datas=explode("\n",$f);
	writelogs_framework(count($datas)." rows",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>". base64_encode(serialize($datas))."</articadatascgi>";
}


function CyrusBackupNow(){
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-backup --single-cyrus \"{$_GET["cyrus-backup-now"]}\"");
}

function Refresh_frontend(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.admin.smtp.flow.status.php --force");
}

function cyrus_mailboxlist_domain(){
	exec("/usr/share/artica-postfix/bin/artica-install --mailboxes-domain {$_GET["mailboxlist-domain"]}",$results);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
}
function RepairArticaLdapBranch(){
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-backup --repair-artica-branch");
}

function cyrus_mailboxlist(){
	exec("/usr/share/artica-postfix/bin/artica-install --mailboxes-list",$results);
	writelogs_framework(count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	
}

function cyrus_mailboxdelete(){
	exec("/usr/share/artica-postfix/bin/artica-install --mailbox-delete {$_GET["mailbox-delete"]}",$results);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";		
}

function cyrus_check_cyraccounts(){
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --reload-cyrus");
}
function cyrus_reconfigure(){
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --cyrus-checkconfig --force");
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart imap");
	}
function cyrus_paritition_default_path(){
	$unix=new unix();
	
	echo "<articadatascgi>". base64_encode($unix->IMAPD_GET("partition-default"))."</articadatascgi>";
	
}

function CyrusRepairMailBox(){
	$uid=$_GET["repair-mailbox"];
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.cyrus-repair-mailbox.php $uid");
//cmd:=GLOBAL_INI.get_ARTICA_PHP_PATH() + '/bin/artica-ldap --repair-mailbox '+RegExpr.Match[1]+' '+GLOBAL_INI.get_ARTICA_PHP_PATH()+'/ressources/logs/cyr.repair.'+RegExpr.Match[1];
	}


function InstallWebServices(){
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.www.install.php");
}

function InstallWebServiceUnique(){
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.www.install.php --single-install \"{$_GET["install-web-service-unique"]}\"");
}

function AptGetUpgrade(){
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.apt-get.php --upgrade");
}

function MailManSync(){
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.mailman.php");
}

function RefreshStatus(){
	@file_put_contents("/usr/share/artica-postfix/ressources/logs/launch.status.task","#");
}

function ForceRefreshLeft(){
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.admin.status.postfix.flow.php --services");	
}
function ForceRefreshRight(){
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.admin.smtp.flow.status.php --services");	
}

function Global_Applications_Status(){
		$unix=new unix();
		if(isset($_GET["status-forced"])){
			$unix->THREAD_COMMAND_SET('/usr/share/artica-postfix/bin/artica-install --artica-status-reload');
			return;
		}
	
         if(!is_file('/usr/share/artica-postfix/ressources/logs/global.versions.conf')){ 
             shell_exec('/usr/share/artica-postfix/bin/artica-install -versions > /usr/share/artica-postfix/ressources/logs/global.versions.conf 2>&1');
         }
      
        if(!is_file('/usr/share/artica-postfix/ressources/logs/global.status.ini')){ 
        	@file_put_contents("/usr/share/artica-postfix/ressources/logs/launch.status.task","#");
        	$datas2=@file_get_contents('/etc/artica-postfix/cache.global.status');
         }else{
         	$datas=@file_get_contents('/usr/share/artica-postfix/ressources/logs/global.status.ini');
         	
          if (file_time_min('/usr/share/artica-postfix/ressources/logs/global.status.ini')>10){
            @unlink('/usr/share/artica-postfix/ressources/logs/global.status.ini');
            @unlink('/usr/share/artica-postfix/ressources/logs/global.versions.conf');
            $unix->THREAD_COMMAND_SET('/usr/share/artica-postfix/bin/artica-install --artica-status-reload');
            $unix->THREAD_COMMAND_SET('/usr/share/artica-postfix/bin/artica-install --write-versions');
            $unix->THREAD_COMMAND_SET('/usr/share/artica-postfix/bin/artica-install --generate-status');
         }         	
         	
         }

         
        $datas2=@file_get_contents('/usr/share/artica-postfix/ressources/logs/global.versions.conf');
		$tmp=$unix->FILE_TEMP();
        $unix->THREAD_COMMAND_SET("/bin/chmod 755 /usr/share/artica-postfix/ressources/logs/global.*");
		@unlink($tmp);
		writelogs_framework("datas=".strlen($datas)." bytes datas2=".strlen($datas)." bytes",__FUNCTION__,__FILE__,__LINE__);;
        echo "<articadatascgi>". base64_encode("$datas\n$datas2")."</articadatascgi>";
	}
	
function artica_version(){
	$datas=@file_get_contents("/usr/share/artica-postfix/VERSION");
	if(trim($datas)==null){$datas="0.00";}
	echo "<articadatascgi>$datas</articadatascgi>";
	  
}
function daemons_status(){

      if(!is_file('/usr/share/artica-postfix/ressources/logs/global.status.ini')){ 
            sys_exec('/usr/share/artica-postfix/bin/artica-install --artica-status-reload');
         }

      if(is_file('/usr/share/artica-postfix/ressources/logs/global.status.ini')){ 
            $datas=@file_get_contents("/usr/share/artica-postfix/ressources/logs/global.status.ini");
            echo "<articadatascgi>$datas</articadatascgi>";
            return ;
        }       
}

function myhostname(){
if($_SESSION["FRAMEWORK"]["myhostname"]<>null){echo $_SESSION["FRAMEWORK"]["myhostname"];exit;}
	$datas=sys_hostname_g();
	$_SESSION["FRAMEWORK"]["myhostname"]="<articadatascgi>$datas</articadatascgi>";
	sys_events(basename(__FILE__)."::{$_SERVER['REMOTE_ADDR']}:: myhostname ($datas)");
	echo $_SESSION["FRAMEWORK"]["myhostname"];
}

function SmtpNotificationConfig(){
	@copy("/etc/artica-postfix/settings/Daemons/SmtpNotificationConfig","/etc/artica-postfix/smtpnotif.conf");
}
function SaveMaincf(){
	$php=LOCATE_PHP5_BIN2();
	shell_exec("/etc/init.d/artica-postfix start daemon &");
	sys_THREAD_COMMAND_SET("$php /usr/share/artica-postfix/exec.postfix.maincf.php --reconfigure");	
}

function SaveConfigFile(){
	$file="/usr/share/artica-postfix/ressources/conf/{$_GET["SaveConfigFile"]}";
	if(!is_file($file)){
		//writelogs_framework("read user-backup/ressources/conf/{$_GET["SaveConfigFile"]} ?",__FUNCTION__,__FILE__,__LINE__);
		if(is_file("/usr/share/artica-postfix/user-backup/ressources/conf/{$_GET["SaveConfigFile"]}")){
			$file="/usr/share/artica-postfix/user-backup/ressources/conf/{$_GET["SaveConfigFile"]}";
		}
	}
	
	if(!is_file($file)){
		writelogs_framework("Unable to stat {$_GET["SaveConfigFile"]} ",__FUNCTION__,__FILE__,__LINE__);
		return;
	}

	$key=$_GET["key"];
	$datas=file_get_contents($file);
	//writelogs_framework("read $file ". strlen($datas)." lenght",__FUNCTION__,__FILE__,__LINE__);
	@file_put_contents("/etc/artica-postfix/settings/Daemons/$key",$datas);
	//writelogs_framework("Saving /etc/artica-postfix/settings/Daemons/$key (". strlen($datas)." bytes)",__FUNCTION__,__FILE__,__LINE__);
	unlink($file);
	unset($_SESSION["FRAMEWORK"]);
	}
	
function SaveClusterConfigFile(){
	$file="/usr/share/artica-postfix/ressources/conf/{$_GET["SaveClusterConfigFile"]}";
	$key=$_GET["key"];
	$datas=@file_get_contents($file);
	sys_events("read $file");
	@mkdir("/etc/artica-cluster");
	@file_put_contents("/etc/artica-cluster/$key",$datas);
	sys_events("Saving /etc/artica-cluster/$key (". strlen($datas)." bytes)");
	@unlink($file);
	unset($_SESSION["FRAMEWORK"]);		
}

function LaunchRemoteInstall(){
	$php=LOCATE_PHP5_BIN2();
	sys_THREAD_COMMAND_SET("$php /usr/share/artica-postfix/exec.remote-install.php");
}
function RestartWebServer(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup /etc/init.d/artica-postfix restart apache >/dev/null 2>&1 &");	
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);	
	$cmd=trim("$nohup /etc/init.d/artica-postfix restart apache-groupware >/dev/null 2>&1 &");	
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);	
	
}

function RestartArticaStatus(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart artica-status");
}


function RestartApacheGroupwareForce(){
	shell_exec('/etc/init.d/artica-postfix restart apache-groupware');
}

function RestartApacheGroupwareNoForce(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd="$nohup /etc/init.d/artica-postfix restart apache-groupware >/dev/null 2>&1 &";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);	
	
}

function RestartMailManService(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart mailman");	
}
function samba_restart(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart samba");	
}

function samba_restart_now(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup /etc/init.d/artica-postfix restart samba >/dev/null 2>&1 &");
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);		
}

function samba_synchronize(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.samba.synchronize.php");
}

function samba_save_config(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	@file_put_contents("/var/log/samba/log.smbd", "\n");
	$cmd=trim("$nohup ".LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.samba.php --build >/dev/null 2>&1 &");
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);
}

function CompileSSHDRules(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup ".LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.syslog-engine.php --authfw-compile >/dev/null 2>&1 &");
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);	
}

function samba_original_config(){
	$datas=@file_get_contents("/etc/samba/smb.conf");
	echo "<articadatascgi>". base64_encode($datas)."</articadatascgi>";	
}




function samba_build_homes(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.samba.php --homes");
}
function samba_build_home_single(){
	$uid=base64_decode($_GET["home-single-user"]);
	if($uid==null){return;}
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.samba.php --home \"$uid\"");
}



function samba_change_sid(){
	$unix=new unix();
	$sid=$_GET["samba-change-sid"];
	shell_exec($unix->LOCATE_NET_BIN_PATH()." setlocalsid $sid");
	shell_exec("/usr/share/artica-postfix/bin/process1 --force");
}
function samba_password(){
	$password=base64_decode($_GET["smbpass"]);
	$file="/usr/share/artica-postfix/bin/install/smbldaptools/smbencrypt";
	$unix=new unix();
	$tmp=$unix->FILE_TEMP();
	$cmd="$file \"$password\" >$tmp 2>&1";
	shell_exec($cmd);
	$results=explode("\n",@file_get_contents($tmp));
	@unlink($tmp);
	writelogs_framework("SambaLoadpasswd ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	
	
	echo "<articadatascgi>". base64_encode(implode(" ",$results))."</articadatascgi>";
	
}

function samba_status(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --samba",$results);
	$datas=implode("\n",$results);
	echo "<articadatascgi>$datas</articadatascgi>";	
	
}
function dropbox_status(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --dropbox",$results);
	$datas=implode("\n",$results);
	echo "<articadatascgi>$datas</articadatascgi>";	
	}
	
function dropbox_service_status(){
	exec("/usr/share/artica-postfix/bin/install/dropbox/dropbox.py status",$results);
	$datas=trim(implode(" ",$results));
	echo "<articadatascgi>$datas</articadatascgi>";	
	}	
function dropbox_service_uri(){
	exec("/usr/share/artica-postfix/bin/install/dropbox/dbreadconfig.py",$results);
	$datas=trim(implode(" ",$results));
	echo "<articadatascgi>$datas</articadatascgi>";	
	}		
	
function dropbox_files_status(){
	exec("/usr/share/artica-postfix/bin/install/dropbox/DropBoxValues.py",$results);
	while (list($num,$line)=each($results)){
		if(preg_match("#(.+?)\s+=\s+(.+)#",$line,$re)){
			$array[$re[1]]=$re[2];
		}
	}
	
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";	
}	


function samba_shares_list(){
	$ini=new iniFrameWork("/etc/samba/smb.conf");
	while (list($num,$array)=each($ini->_params)){
		if(trim($array["path"])==null){continue;}
		if(!is_dir(trim($array["path"]))){continue;}
			$results[]=$array["path"];
	}
	
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	
}



function samba_pdbedit(){
	$user=$_GET["pdbedit"];
	$unix=new unix();
	$cmd=$unix->find_program("pdbedit")." -Lv $user -s /etc/samba/smb.conf";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$results);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
}
function samba_pdbedit_group(){
	$administrator_pwd=base64_decode($_GET["password"]);
	$group=$_GET["pdbedit-group"];
	
	
	$unix=new unix();
	$net=$unix->find_program("net");
	$cmd="net rpc group MEMBERS \"$group\" -U administrator%$administrator_pwd 2>&1";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	$results=array();
	exec($cmd,$results);
	$AR["MEMBERS"]=$results;
	$cmd="net groupmap list -U administrator%$administrator_pwd 2>&1";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	$results=array();
	exec($cmd,$results);	
	while (list($num,$line)=each($results)){
		if(strpos(" $line","$group")>0){$AR["MAP"][]=$line;}
	}
	echo "<articadatascgi>". base64_encode(serialize($AR))."</articadatascgi>";
}

function samba_pdbedit_debug(){
	$user=$_GET["Debugpdbedit"];
	$unix=new unix();
	$cmd=$unix->find_program("pdbedit")." -Lv -d 10 $user -s /etc/samba/smb.conf";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$results);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	
	
}



function arp_and_ip(){
	$computer_name=$_GET["arp-ip"];
	if($computer_name==null){return;}
	$unix=new unix();
	$ip=$unix->HostToIp($computer_name);
	writelogs_framework("gethostbyname -> $computer_name = $ip",__FUNCTION__,__FILE__,__LINE__);
	if($ip==$computer_name){return null;}
	if($ip==null){return null;}
	$arp=$unix->IpToMac($ip);
	echo "<articadatascgi>".  base64_encode(serialize(array($ip,$arp)))."</articadatascgi>";	
	}
	
function ip_to_mac(){
	$ip=$_GET["ip-to-mac"];
	$unix=new unix();
	$arp=$unix->IpToMac($ip);
	echo "<articadatascgi>$arp</articadatascgi>";	
	
}


function RestartGroupwareWebServer(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart apache");
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart apache-groupware");
}

function ReloadApacheGroupWare(){
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --reload-apache-groupware");
}


function RestartASSPService(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart assp");	
}
function ReloadASSPService(){
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --reload-assp");	
}
function rewrite_php(){
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --php-include");	
}

function RestartDHCPDService(){
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --reload-dhcpd");		
}


function RestartMailGraphService(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart mailgraph");		
}
function RestartDaemon(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart daemon");
}
function RestartFetchmail(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart fetchmail");
}

function SQUIDGUARD_RELOAD(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.squidguard.php --build --reload");
}
function SQUIDGUARD_WEB_RELOAD(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart squidguard-http");
}



function RestartSquid(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart squid");
}
function RestartArticaPolicy(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart artica-policy");
}
function ReloadArticaPolicy(){
	sys_THREAD_COMMAND_SET("/usr/share/bin/artica-install --reload-artica-policy");
}


function RestartMysqlDaemon(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart mysql");
}

function RestartOpenVPNServer(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart openvpn");
}


function RestartCyrusImapDaemon(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart imap");
}

function rRestartCyrusImapDaemonDebug(){
	exec("/etc/init.d/artica-postfix restart imap --verbose",$results);
	$a=serialize($results);
	echo "<articadatascgi>". base64_encode($a)."</articadatascgi>";		
}

function ReconfigureCyrusImapDaemon(){
	if(isset($_GET["force"])){$force=" --force";}
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --reconfigure-cyrus$force");
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart postfix-single");
}
function ReconfigureCyrusImapDaemonDebug(){
	exec("/usr/share/artica-postfix/bin/artica-install --reconfigure-cyrus --force --verbose",$results);
	$a=serialize($results);
	echo "<articadatascgi>". base64_encode($a)."</articadatascgi>";		
}

function reload_dansguardian(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.squidguard.php --build");
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --reload-dansguardian");
	}
	
function reload_ufdbguard(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.squidguard.php --build");
}	
	
function delete_mailbox(){
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --delete-mailbox {$_GET["DelMbx"]}");
}

function umount_disk(){
	$mount=$_GET["umount-disk"];
	$unix=new unix();
	writelogs_framework("umount $mount",__FUNCTION__,__FILE__,__LINE__);
	shell_exec($unix->find_program("umount")." -l \"$mount\"");
}

function fdisk_list(){
	$unix=new unix();
	exec($unix->find_program("fdisk")." -l",$results);
	if(!is_array($results)){return null;}
	
	while (list ($num, $path) = each ($results)){
		if(preg_match("#Disk\s+(.+?):\s+([0-9\.]+)\s+([A-Z]+),#",$path,$re)){
			$array[trim($re[1])]=trim($re[2]." ".$re[3]);
		}else{
			
		}
	}
	writelogs_framework(count($array)." disks found",__FUNCTION__,__FILE__,__LINE__);
	$a=serialize($array);
	echo "<articadatascgi>". base64_encode($a)."</articadatascgi>";	
}

function lvmdiskscan(){
	$unix=new unix();
	exec($unix->find_program("lvmdiskscan")." -l",$results);
	if(!is_array($results)){return null;}	
	///dev/sda2                [      148.95 GB] LVM physical volume
	while (list ($num, $path) = each ($results)){
		if(preg_match("#(.+?)\s+\[(.+?)\]\s+#",$path,$re)){
			$array[trim($re[1])]=trim($re[2]);
		}else{
			
		}
	}
writelogs_framework(count($array)." disks found",__FUNCTION__,__FILE__,__LINE__);
	$a=serialize($array);
	echo "<articadatascgi>". base64_encode($a)."</articadatascgi>";			
}

function pvscan(){
$unix=new unix();
	exec($unix->find_program("pvscan")." -u",$results);	
if(!is_array($results)){return null;}	
	while (list ($num, $path) = each ($results)){
		if(preg_match("#PV\s+(.+?)\s+with\s+UUID\s+(.+?)\s+VG\s+(.+?)\s+lvm[0-9]\s+\[([0-9,\.]+)\s+([A-Z]+).+?([0-9,\.]+)\s+([A-Z]+)#",$path,$re)){
			$array[trim($re[1])]=array("VG"=>trim($re[3]),"SIZE"=>trim($re[4])." ".trim($re[5]),"UUID"=>trim($re[2]),"FREE"=>trim($re[6])." ".trim($re[7]));
		}
	}
writelogs_framework(count($array)." disks found",__FUNCTION__,__FILE__,__LINE__);
	$a=serialize($array);
	echo "<articadatascgi>". base64_encode($a)."</articadatascgi>";			
	
	
}

function LVM_VG_DISKS(){
	$unix=new unix();
	exec($unix->find_program("pvdisplay")." -c",$results);	
	if(!is_array($results)){return null;}
	while (list ($num, $line) = each ($results)){
		$tb=explode(":",$line);
		
		$size=round(($tb[2]/2048)/1000);
		$array[$tb[1]][]=array($tb[0],$size);
		
	}
	
	$a=serialize($array);
	echo "<articadatascgi>". base64_encode($a)."</articadatascgi>";		
}


function LVM_LV_DISPLAY(){
	$dev=trim($_GET["lvdisplay"]);
	$unix=new unix();
	$vgdisplay=$unix->find_program("lvdisplay");
	$cmd="$vgdisplay -v -m $dev 2>&1";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	exec("$cmd",$results);
	echo "<articadatascgi>". @implode("\n",$results)."</articadatascgi>";
}

function LVM_UNLINK_DISK(){
	$groupname=$_GET["groupname"];
	$dev=$_GET["dev"];
	$unix=new unix();
	$cmd=$unix->find_program("vgreduce")." $groupname $dev";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$results);	
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	
	  
	
}
function LVM_LINK_DISK(){
	$groupname=$_GET["groupname"];
	$dev=$_GET["dev"];
	$unix=new unix();
	$tmpstr=$unix->FILE_TEMP();
	$cmd=$unix->find_program("vgextend")." $groupname $dev";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	shell_exec("$cmd >$tmpstr 2>&1");
	$results=explode("\n",@file_get_content($tmpstr));
	$results[]="$cmd";
	$results[]="$dev -> $groupname";	
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	
	  
	
}

function LVM_CREATE_GROUP(){
	$groupname=$_GET["groupname"];
	$dev=$_GET["dev"];
	exec("/usr/share/artica-postfix/bin/artica-install --vgcreate-dev $dev $groupname",$results);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
}



function ChangeMysqlLocalRoot(){
	@file_put_contents("/usr/share/artica-postfix/ressources/logs/ChangeMysqlLocalRoot","{scheduled}");
	@chmod("/usr/share/artica-postfix/ressources/logs/ChangeMysqlLocalRoot",0755);
	$q=new unix();
	$_GET["password"]=$q->shellEscapeChars($_GET["password"]);
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --change-mysqlroot --inline \"{$_GET["ChangeMysqlLocalRoot"]}\" \"{$_GET["password"]}\" --verbose >>/usr/share/artica-postfix/ressources/logs/ChangeMysqlLocalRoot 2>&1");
	
}

function ChangeMysqlLocalRoot2(){
	$q=new unix();
	$nohup=$q->find_program("nohup");
	if($_GET["password"]==null){echo $results[]="No Password set";}
	if($_GET["username"]==null){echo $results[]="No Username set";}
	$_GET["password"]=$q->shellEscapeChars(base64_decode($_GET["password"]));
	$_GET["username"]=$q->shellEscapeChars(base64_decode($_GET["username"]));
	$tplfile="/usr/share/artica-postfix/ressources/logs/web/ChangeMysqlLocalRoot2.log";
	@file_put_contents($tplfile, "{waiting}....");
	@chmod($tplfile,777);
	$cmd="$nohup /usr/share/artica-postfix/bin/artica-install --change-mysqlroot --inline \"{$_GET["username"]}\" \"{$_GET["password"]}\" >$tplfile 2>&1 &";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec(trim($cmd));
	
	
}

function ChangeMysqlDir(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	if(is_file($nohup)){$nohup="$nohup ";}
	shell_exec("$nohup/usr/share/artica-postfix/bin/artica-install --change-mysqldir >/dev/null 2>&1 &");
}

function ChangeSSLCertificate(){
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --change-certificate");
	}


function viewlogs(){
	$file=$_GET["viewlogs"];
	$datas=@shell_exec("tail -n 100 /var/log/artica-postfix/$file");
	echo "<articadatascgi>$datas</articadatascgi>";
}
function LdapdbStat(){
	$unix=new unix();
	$dbstat=$unix->LOCATE_DB_STAT();
	$ldap_datas=$unix->PATH_LDAP_DIRECTORY_DATA();
	error_log($ldap_datas);
	$head=$unix->LOCATE_HEAD();
	if($dbstat==null){return null;}
	$cmd="$dbstat -h $ldap_datas -m | $head -n 11";
	
	error_log($cmd);
	$results=shell_exec($cmd);
	echo "<articadatascgi>$results</articadatascgi>";
} 
function LdapdbSize(){
	$unix=new unix();
	$du=$unix->LOCATE_DU();
	$ldap_datas=$unix->PATH_LDAP_DIRECTORY_DATA();
	if($du==null){return null;}
	$results=trim(shell_exec("$du -h $ldap_datas"));
	echo "<articadatascgi>$results</articadatascgi>";
}

function du_dir_size(){
	$path=$_GET["path"];
	$unix= new unix();
	$du=$unix->find_program("du");
	if(!is_dir($path)){echo "<articadatascgi>0</articadatascgi>";return;}
	exec("$du -m -s $path",$results);
	if(preg_match("#^([0-9]+)#",@implode("",$results),$re)){echo "<articadatascgi>{$re[1]}</articadatascgi>";return;}
	
}

function ldap_restart(){
sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart ldap");	
}

function buildFrontEnd(){
	
	if(isset($_GET["right"])){
		$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.admin.smtp.flow.status.php --force";
		
		error_log($cmd." ". __FILE__);
		BuildingExecRightStatus("Scheduled",10);	
		sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.admin.smtp.flow.status.php --force");		
		return null;
	}
	
	
	BuildingExecStatus("Scheduled",10);	
	error_log("schedule commande ". LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.admin.status.postfix.flow.php --force");
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.admin.status.postfix.flow.php --force");		
}
function cpualarm(){
$cpu=shell_exec("/usr/share/artica-postfix/bin/cpu-alarm.pl");	
echo "<articadatascgi>$cpu</articadatascgi>";
}
function CurrentLoad(){
	if(preg_match('#load average:\s+([0-9\.]+)#',shell_exec("uptime"),$re)){
		echo "<articadatascgi>{$re[1]}</articadatascgi>";	
	}
}

function TaskLastManager(){
	$datas=shell_exec("/bin/ps -w axo ppid,pcpu,pmem,time,args --sort -pcpu,-pmem|/usr/bin/head --lines=30");	
	echo "<articadatascgi>$datas</articadatascgi>";
}

function postfixQueues(){
	$p=new postfix_system();
	$datas=serialize($p->getQueuesNumber());
	echo "<articadatascgi>".serialize($p->getQueuesNumber())."</articadatascgi>";
}

function postfix_read_main(){
	echo "<articadatascgi>".@file_get_contents("/etc/postfix/main.cf")."</articadatascgi>";
}
function postfix_reconfigure(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.maincf.php --reconfigure");
}
function postfix_restart_single(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart postfix-single");
}
function postfix_restart_single_now(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	if(is_file($nohup)){$nohup="$nohup ";}
	shell_exec("$nohup /etc/init.d/artica-postfix restart postfix-single >/dev/null 2>&1 &");
}

function postfix_restricted_users(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.maincf.php --restricted");
}

function postfix_tail(){
	if(isset($_GET["filter"])){$filter=" \"{$_GET["filter"]}\"";}
	exec("/usr/share/artica-postfix/bin/artica-install --mail-tail$filter",$results);
	//writelogs_framework(count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>".base64_encode(serialize($results))."</articadatascgi>";
}

function postfix_multi_configure(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix-multi.php --org {$_GET["postfix-multi-configure-ou"]}");
}
function postfix_multi_disable(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix-multi.php --removes");
}
function postfix_multi_configure_hostname(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix-multi.php --instance-reconfigure {$_GET["postfix-multi-configure-hostname"]}");
}





function zabbix_restart(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart zabbix");
}

function postfix_multi_stat(){
	$instance=$_GET["postfix-mutli-stat"];
	$unix=new unix();
	$pid=$unix->POSTFIX_MULTI_PID($instance);
	$path="/proc/$pid/exe";
	writelogs_framework("POSTFIX_MULTI_PID->$pid",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
	
	
	$version=$unix->POSTFIX_VERSION();
	if($version==null){
		$pid=$pid;
		$array[0]=-2;
		$array[1]=$version;
		$array[2]=$pid;
		$array[3]=$path;
		echo "<articadatascgi>".base64_encode(serialize($array))."</articadatascgi>";
		return ;
	}
	
	
	if(is_file($path)){
		$pid=$pid;
		$array[0]=1;
		$array[1]=$version;
		$array[2]=$pid;
		$array[3]=$path;
	}else{
		$pid=null;
		$array[0]=0;
		$array[1]=$version;
		$array[2]=null;
		$array[3]=$path;
	}
echo "<articadatascgi>".base64_encode(serialize($array))."</articadatascgi>";	
}



function postfix_stat(){
	$unix=new unix();
	$pid=$unix->POSTFIX_PID();
	$path="/proc/$pid/exe";
	writelogs_framework("POSTFIX_PID->$pid",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
	
	
	$version=$unix->POSTFIX_VERSION();
	if($version==null){
		$pid=$pid;
		$array[0]=-2;
		$array[1]=$version;
		$array[2]=$pid;
		$array[3]=$path;
		echo "<articadatascgi>".base64_encode(serialize($array))."</articadatascgi>";
		return ;
	}
	
	
	if(is_file($path)){
		$pid=$pid;
		$array[0]=1;
		$array[1]=$version;
		$array[2]=$pid;
		$array[3]=$path;
	}else{
		$pid=null;
		$array[0]=0;
		$array[1]=$version;
		$array[2]=null;
		$array[3]=$path;
	}
echo "<articadatascgi>".base64_encode(serialize($array))."</articadatascgi>";

	
}

function ChangeLDPSSET(){
	$unix=new unix();
	$password=base64_decode($_GET["password"]);
	$password=$unix->shellEscapeChars($password);
	
	$vals=shell_exec("/usr/share/artica-postfix/bin/artica-install --change-ldap-settings {$_GET["ldap_server"]} {$_GET["ldap_port"]} {$_GET["suffix"]} {$_GET["username"]} $password {$_GET["change_ldap_server_settings"]}");
	echo "<articadatascgi>$vals</articadatascgi>";
}
function ASSPOriginalConf(){
	echo "<articadatascgi>".@file_get_contents("/usr/share/assp/assp.cfg")."</articadatascgi>";
}
function SetupCenter(){
sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.admin.status.postfix.flow.php --setup");
sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --write-versions");	
}
function BuildVhosts(){
sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.www.install.php");
sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.www.webdav.php --users");		
}


function MySqlPerf(){
	$cmd="mysql -p{$_GET["pass"]} -u {$_GET["username"]} -T -P {$_GET["port"]} -h {$_GET["host"]} -e \"SELECT benchmark(100000000,1+2);\" -vvv >/tmp/mysqlperfs.txt 2>&1";
	shell_exec($cmd);
	
	$tbl=explode("\n",@file_get_contents("/tmp/mysqlperfs.txt"));
	while (list ($num, $ligne) = each ($tbl) ){
		if(preg_match('#row in set\s+\(([0-9\.]+)#',$ligne,$re)){
			$time=trim($re[1]);
		}
	}
	
	echo "<articadatascgi>$time</articadatascgi>";
	@unlink("/tmp/mysqlperfs.txt");
	
	
}

function MysqlAudit(){
	$cmd="/usr/share/artica-postfix/bin/mysqltuner.pl --skipsize --noinfo --nogood --nocolor --pass {$_GET["pass"]} --user {$_GET["username"]} --port {$_GET["port"]} --host {$_GET["host"]} --forcemem {$_GET["server_memory"]} --forceswap {$_GET["server_swap"]} 2>&1";
	exec($cmd,$results);
	while (list ($num, $ligne) = each ($results) ){
		if(preg_match("#Performance Metrics#",$ligne)){$start=true;}
		if(!$start){continue;}
		$f[]=$ligne;
		
	}
	
	
	echo "<articadatascgi>". implode("\n",$f)."</articadatascgi>";
}
function RestartApacheNow(){
	error_log("restarting apache");
	$datas=sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart apache &");
	error_log("restarting apache done $datas");
}
function reloadSpamAssassin(){
sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --reload-spamassassin");
}


function dirdir(){
	$path=$_GET["dirdir"];
	$unix=new unix();
	$array=$unix->dirdir($path);
	writelogs_framework("$path=".count($array)." directories",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>". serialize($array)."</articadatascgi>";
}


function directory_delete_user(){
	$path=base64_decode($_GET["delete-user-folder"]);
	$uid=base64_decode($_GET["uid"]);
	if($uid==null){return;}
	if($path==null){return;}
	if($path=="/"){return;}	
	$dir_uid=posix_getpwuid(fileowner($path));
	$dir_uid_name=$dir_uid["name"];
	writelogs_framework("Delete folder '$path' for $uid against $dir_uid_name",__FUNCTION__,__FILE__,__LINE__);
	if($dir_uid_name<>$uid){
		echo "<articadatascgi>{ERROR_NO_PRIVS}</articadatascgi>;";
		return;
		
	}
	if(is_dir($path)){
		$path=shellEscapeChars($path);
		writelogs_framework("Delete folder '$path' finally",__FUNCTION__,__FILE__,__LINE__);
		shell_exec("/bin/rm -rf $path");
	}
	
	//@mkdir($path,0666,true);
	//shell_exec("/bin/chown $uid $path");
}

function shellEscapeChars($path){
		$unix=new unix();
		return $unix->shellEscapeChars($path);	
}


function directory_create_user(){
	$path=base64_decode($_GET["create-user-folder"]);
	$uid=base64_decode($_GET["uid"]);
	if($uid==null){return;}
	if($path==null){return;}
	if($path=="/"){return;}
	writelogs_framework("Create new folder '$path' for $uid",__FUNCTION__,__FILE__,__LINE__);
	@mkdir($path,0777,true);
	shell_exec("/bin/chown $uid $path");
	@chmod($path,0777);
}

function dirdirEncoded(){
	$path=$_GET["dirdir-encoded"];
	$unix=new unix();
	$array=$unix->dirdir($path);
	writelogs_framework("$path=".count($array)." directories",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>". serialize($array)."</articadatascgi>";
}

function Dir_Files(){
	$path=base64_decode($_GET["Dir-Files"]);
	writelogs_framework("$path",__FUNCTION__,__FILE__,__LINE__);
	$unix=new unix();
	$array=$unix->DirFiles($path);
	writelogs_framework("$path=".count($array)." files",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
}
function filestat(){
	$path=base64_decode($_GET["filestat"]);
	
	$unix=new unix();
	$array=$unix->alt_stat($path);
	if(!is_array($array)){writelogs_framework("ERROR stat -> $path",__FUNCTION__,__FILE__,__LINE__);}
	
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";	
}

function folder_create(){
$path=utf8_decode(base64_decode($_GET["create-folder"]));
$perms=base64_decode($_GET["perms"]);
$unix=new unix();
writelogs_framework("path=$path (".base64_decode($_GET["perms"]).")",__FUNCTION__,__FILE__,__LINE__);
	if(!mkdir(utf8_encode($path),0666,true)){
		writelogs_framework("FATAL ERROR while creating folder $path (".base64_decode($_GET["perms"]).")",__FUNCTION__,__FILE__,__LINE__);
		echo "<articadatascgi>". base64_encode($path." -> {failed}")."</articadatascgi>";
		exit;
	}
	
	if($perms<>null){
		$cmd=$unix->find_program("chown")." ".base64_decode($_GET["perms"])." \"$path\"";
		writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
		shell_exec($cmd);
		}
	
}

function folder_delete(){
$path=base64_decode($_GET["folder-remove"]);
$path=utf8_encode($path);
$unix=new unix();
if($unix->IsProtectedDirectory($path)){
	echo "<articadatascgi>". base64_encode($path." -> {failed} {protected}")."</articadatascgi>";
	exit;
}

writelogs_framework("path=$path",__FUNCTION__,__FILE__,__LINE__);
if(!is_dir($path)){
	writelogs_framework("$path no such directory",__FUNCTION__,__FILE__,__LINE__);
	return;
}
$cmd=$unix->find_program("rm")." -rf \"$path\"";
writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
shell_exec($cmd);
}



function dirdirBase64(){
	$path=base64_decode($_GET["B64-dirdir"]);
	$unix=new unix();
	
	$array=$unix->dirdir($path);
	writelogs_framework("path=$path (".count($array)." elements)",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
}

function ReplicatePerformancesConfig(){
	copy("/etc/artica-postfix/settings/Daemons/ArticaPerformancesSettings","/etc/artica-postfix/performances.conf");
}

function SetupIndexFile(){
 
 	$unix=new unix();
 	$tmpf=$unix->FILE_TEMP();
 	if(is_file("/usr/share/artica-postfix/ressources/index.ini")){@unlink("/usr/share/artica-postfix/ressources/index.ini");}
 	shell_exec("/usr/share/artica-postfix/bin/artica-update --index --verbose >$tmpf 2>&1");
    $datas=@file_get_contents($tmpf);
    @unlink($tmpf);  
    
    $cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.admin.status.postfix.flow.php --setup-center";
    error_log("framework:: $cmd");
	shell_exec(LOCATE_PHP5_BIN2().' /usr/share/artica-postfix/exec.admin.status.postfix.flow.php --setup-center');	
	echo "<articadatascgi>$datas</articadatascgi>";
	
	
}

function testnotif(){
	$unix=new unix();
	$tmpstr=$unix->FILE_TEMP();
	shell_exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/cron.notifs.php --sendmail >$tmpstr 2>&1");
	echo "<articadatascgi>".@file_get_contents($tmpstr)."</articadatascgi>";
	@unlink($tmpstr);
}	
function ComputerRemoteRessources(){
	$unix=new unix();
	$tmpstr=$unix->FILE_TEMP();
	$cmd="/usr/share/artica-postfix/bin/artica-install --remote-ressources \"{$_GET["ComputerRemoteRessources"]}\" \"{$_GET["username"]}\" \"{$_GET["password"]}\" >$tmpstr 2>&1";
	error_log("framework:: $cmd");
	shell_exec($cmd);
	echo "<articadatascgi>".@file_get_contents($tmpstr)."</articadatascgi>";
	@unlink($tmpstr);	
}
function FreeCache(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.swap-monitor.php --free");	
}

function DumpPostfixQueue(){
	$queue=$_GET["DumpPostfixQueue"];
	error_log("framework:: DumpPostfixQueue() -> $queue");
	$postfix=new postfix_system();
	echo "<articadatascgi>".$postfix->READ_QUEUE($queue)."</articadatascgi>";
	
	
}
function idofUser(){
	$unix=new unix();
	exec($unix->find_program('id')." {$_GET["idofUser"]}",$return);
	if(preg_match("#uid=([0-9]+)\({$_GET["idofUser"]}\)#",$return[0],$re)){
		echo "<articadatascgi>{$re[1]}</articadatascgi>";
	}
}

function MailManList(){
	$cmd="/usr/lib/mailman/bin/list_lists -a";
	exec($cmd,$array);
	while (list ($num, $ligne) = each ($array) ){
		
		if(preg_match("#([a-zA-Z0-9-_\.]+)\s+-\s+\[#",$ligne,$re)){
			$rr[]=strtolower($re[1]);
		}
		
	}	
	
 echo "<articadatascgi>". serialize($rr)."</articadatascgi>";	
	
}
function MailManDelete(){
	$list=$_GET["mailman-delete"];
	shell_exec("/bin/touch /var/lib/mailman/data/aliases");
	exec("/usr/lib/mailman/bin/rmlist -a $list",$re);
	if(is_array($re)){
		echo "<articadatascgi>". serialize($re)."</articadatascgi>";
	}
}

function philesizeIMG(){
	
	$unix=new unix();
	$tmpf=$unix->FILE_TEMP();
	$path=$_GET["philesize-img"];
	$img=md5($path);
	$path=str_replace("//","/",$path);
	if(substr($path,strlen($path)-1,1)=='/'){$path=substr($path,0,strlen($path)-1);}
	if($path==null){$path="/";}
	chdir("/usr/share/artica-postfix/bin");
	$cmd="/usr/share/artica-postfix/bin/philesight --db /opt/artica/philesight/database.db --path $path --draw /usr/share/artica-postfix/ressources/logs/$img.png";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	shell_exec("/usr/share/artica-postfix/bin/philesight --db /opt/artica/philesight/database.db --path $path --draw /usr/share/artica-postfix/ressources/logs/$img.png >$tmpf 2>&1");
	echo "<articadatascgi><img src='ressources/logs/$img.png'></articadatascgi>";
	$res=@file_get_contents($tmpf);
	@unlink($tmpf);
	writelogs_framework("ressources/logs/$img.png=>\"$res\" (". @filesize("/usr/share/artica-postfix/ressources/logs/$img.png")." bytes)",__FUNCTION__,__FILE__,__LINE__);
	
	
}
function philesizeIMGPath(){
	
	$unix=new unix();
	$tmpf=$unix->FILE_TEMP();
	$path=$_GET["philesize-img-path"];
	$img=md5($path);
	$path=str_replace("//","/",$path);
	if(substr($path,strlen($path)-1,1)=='/'){$path=substr($path,0,strlen($path)-1);}
	if($path==null){$path="/";}
	chdir("/usr/share/artica-postfix/bin");
	$cmd="/usr/share/artica-postfix/bin/philesight --db /opt/artica/philesight/database.db --path $path --draw /usr/share/artica-postfix/ressources/logs/$img.png";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	@mkdir("/usr/share/artica-postfix/user-backup/ressources/logs");
	@copy("/usr/share/artica-postfix/ressources/logs/$img.png","/usr/share/artica-postfix/user-backup/ressources/logs/$img.png");
	@chmod("/usr/share/artica-postfix/user-backup/ressources/logs/$img.png",0755);
	shell_exec("/usr/share/artica-postfix/bin/philesight --db /opt/artica/philesight/database.db --path $path --draw /usr/share/artica-postfix/ressources/logs/$img.png >$tmpf 2>&1");
	echo "<articadatascgi>ressources/logs/$img.png</articadatascgi>";
	$res=@file_get_contents($tmpf);
	@unlink($tmpf);
	writelogs_framework("ressources/logs/$img.png=>\"$res\" (". @filesize("/usr/share/artica-postfix/ressources/logs/$img.png")." bytes)",__FUNCTION__,__FILE__,__LINE__);
	
	
}

function kaspersky_status(){
	exec("/usr/share/artica-postfix/bin/artica-install --kaspersky-status",$results);
	$text=trim(implode("\n",$results));
	echo "<articadatascgi>". base64_encode($text)."</articadatascgi>";
	
}

function kavmilter_configure(){
	if(is_file("/opt/kav/5.6/kavmilter/bin/kavmilter")){
		sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.kavmilter.php");
	}
		
if(is_file("/opt/kaspersky/kav4lms/bin/kav4lms-cmd")){
		sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.kav4mls.php");
	}	
	
}
function kavmilter_mem(){
	exec("/usr/share/artica-postfix/bin/artica-install --kavmilter-mem",$results);
	$text=trim(implode(" ",$results));
	echo "<articadatascgi>". base64_encode($text)."</articadatascgi>";
}
function kavmilter_pattern(){
	exec("/usr/share/artica-postfix/bin/artica-install --kavmilter-pattern",$results);
	$text=trim(implode(" ",$results));
	echo "<articadatascgi>". base64_encode($text)."</articadatascgi>";
}


function squid_originalconf(){
	$unix=new unix();
	echo "<articadatascgi>". base64_encode(@file_get_contents($unix->LOCATE_SQUID_CONF()))."</articadatascgi>";
}



function  philesight_perform(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.philesight.php --rebuild");
}

function PostfixHeaderCheck(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.maincf.php --headers-check");
}

function disks_list(){
	$unix=new unix();
	$array=$unix->DISK_LIST();
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
}

function disks_change_label(){
	$dev=$_GET["disk-change-label"];
	$name=$_GET["name"];
	exec("/usr/share/artica-postfix/bin/artica-install --disk-change-label $dev $name --verbose",$array);
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
}

function disks_get_label(){
	$dev=$_GET["disk-get-label"];
	$unix=new unix();
	$e2label=$unix->find_program("e2label");
	if($e2label==null){return;}
	exec("$e2label $dev",$array);
	$data=trim(@implode(" ",$array));
	echo "<articadatascgi>". base64_encode($data)."</articadatascgi>";
	
}

function disk_get_mounted_point(){
	$dev=base64_decode($_GET["get-mounted-path"]);
	$unix=new unix();
	echo "<articadatascgi>". base64_encode($unix->MOUNTED_PATH($dev))."</articadatascgi>";
}




function lvs_scan(){
	$results=array();
	$VolumeGroupName=$_GET["lvm-lvs"];
	$unix=new unix();
	exec($unix->find_program("lvs")." --noheadings --aligned --separator \";\" --units g $VolumeGroupName",$returns);
	while (list ($num, $ligne) = each ($returns) ){
		if(!preg_match("#(.+?);(.+?);(.+?);(.+?)G#",$ligne,$re)){continue;}
		$array[trim($re[1])]=str_replace(",",".",trim($re[4]));
	}
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
	}

function sfdisk_dump(){
	$dev=$_GET["sfdisk-dump"];
	$unix=new unix();
	exec($unix->find_program("sfdisk")." -d $dev",$returns);	
	echo "<articadatascgi>". base64_encode(serialize($returns))."</articadatascgi>";
	}
function mkfs(){
	$dev=$_GET["mkfs"];
	if($dev==null){return null;}
	$unix=new unix();
	$ext=$unix->BetterFS();
	exec($unix->find_program("mkfs")." -T $ext $dev",$returns);	
	echo "<articadatascgi>". base64_encode(serialize($returns))."</articadatascgi>";	
}

function parted_print(){
	$dev=$_GET["parted-print"];
	$unix=new unix();
	if($dev==null){return;}
	exec($unix->find_program("parted")." $dev -s unit GB print",$returns);
	echo "<articadatascgi>". base64_encode(serialize($returns))."</articadatascgi>";		
}

function LVM_LVS_DEV_MAPPER(){
	$dev=$_GET["lvs-mapper"];
	$mapper=@readlink($dev);
	$mapper=str_replace("../mapper","/dev/mapper",$mapper);
	echo "<articadatascgi>$mapper</articadatascgi>";
}
function LVM_VGS_INFO(){
	$vg=$_GET["vgs-info"];
	$unix=new unix();
	exec($unix->find_program("vgs")." $vg",$returns);
	$pattern="$vg\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)(.+?)\s+([0-9,\.A-Z]+)\s+([0-9,\.A-Z]+)";
	writelogs_framework("$vg:: PATTERN=\"$pattern\"",__FUNCTION__,__FILE__,__LINE__);
	while (list ($num, $ligne) = each ($returns) ){
		if(preg_match("#$pattern#",$ligne,$re)){
			$array[$vg]=array("SIZE"=>$re[5],"FREE"=>$re[6]);
			echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
			break;
		}else{
			writelogs_framework("$vg:: FAILED=\"$ligne\"",__FUNCTION__,__FILE__,__LINE__);
		}
	}	
	
}


function LVM_lVS_INFO_ALL(){
$unix=new unix();
	exec($unix->find_program("lvs"),$returns);
	$pattern="(.+?)\s+(.+?)\s+(.+?)\s+([0-9,\.A-Z]+)";
	writelogs_framework("PATTERN=\"$pattern\"",__FUNCTION__,__FILE__,__LINE__);
	
	
	while (list ($num, $ligne) = each ($returns) ){
		if(preg_match("#$pattern#",trim($ligne),$re)){
			$array[trim($re[1])]=array("SIZE"=>$re[4],"GROUPE"=>$re[2]);
		}else{
			writelogs_framework("FAILED=\"$ligne\"",__FUNCTION__,__FILE__,__LINE__);
		}
	}

	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
}

function LVM_LV_ADDSIZE(){
	$mapper=$_GET["lv-resize-add"];
	$size=$_GET["size"];
	$unit=$_GET["unit"];
	$results=array();
	$unix=new unix();
	
	$cmd0=$unix->find_program("lvextend")." -L $size$unit $mapper";
	$cmd1=$unix->find_program("umount")." -f $mapper";
	$cmd2=$unix->find_program("resize2fs")." -f $mapper";
	$cmd3=$unix->find_program("mount")." $mapper";
	
	writelogs_framework("$cmd0",__FUNCTION__,__FILE__,__LINE__);
	exec($cmd0,$results0);
	
	writelogs_framework("$cmd1",__FUNCTION__,__FILE__,__LINE__);
	exec($cmd1,$results1);
	
	writelogs_framework("$cmd2",__FUNCTION__,__FILE__,__LINE__);
	exec($cmd2,$results2);

	writelogs_framework("$cmd3",__FUNCTION__,__FILE__,__LINE__);
	exec($cmd3,$results3);	
	
	
	if(is_array($results0)){$results=$results+$results0;}
	if(is_array($results1)){$results=$results+$results1;}
	if(is_array($results2)){$results=$results+$results2;}
	if(is_array($results3)){$results=$results+$results3;}
	
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	

}
function LVM_LV_DELSIZE(){
	$mapper=$_GET["lv-resize-red"];
	$size=$_GET["size"];
	$unit=$_GET["unit"];
	$results=array();
	$unix=new unix();
	
	
	$cmd0=$unix->find_program("lvreduce")." -y -f -L$size$unit $mapper";
	$cmd1=$unix->find_program("umount")." -f $mapper";
	$cmd2=$unix->find_program("resize2fs")." -f -p $mapper $size$unit";
	$cmd3=$unix->find_program("mount")." $mapper";
	
	writelogs_framework("$cmd0",__FUNCTION__,__FILE__,__LINE__);
	exec($cmd0,$results0);
	
	writelogs_framework("$cmd2",__FUNCTION__,__FILE__,__LINE__);
	exec($cmd2,$results2);	
	
	writelogs_framework("$cmd1",__FUNCTION__,__FILE__,__LINE__);
	exec($cmd1,$results1);
	

	writelogs_framework("$cmd3",__FUNCTION__,__FILE__,__LINE__);
	exec($cmd3,$results3);	
	
	
	if(is_array($results0)){$results=$results+$results0;}
	if(is_array($results1)){$results=$results+$results1;}
	if(is_array($results2)){$results=$results+$results2;}
	if(is_array($results3)){$results=$results+$results3;}
	
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	

}



function LVM_REMOVE(){
	
	// dmsetup info -c
	$dev=$_GET["lvremove"];
	$unix=new unix();
	$cmd=$unix->find_program("lvremove")." -f $dev 2>&1";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$results);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
}

function SASL_FINGER(){
	$unix=new unix();
	$saslfinger=$unix->find_program("saslfinger");
	if(!is_file($saslfinger)){
		echo "<articadatascgi>". base64_encode(serialize(array("unable to stat saslfinger")))."</articadatascgi>";
		return;
	}	
	
	exec("$saslfinger -s",$returns);
	echo "<articadatascgi>". base64_encode(serialize($returns))."</articadatascgi>";
}

function SASL_pluginviewer(){
	$unix=new unix();
	$saslfinger=$unix->find_program("pluginviewer");
	if(!is_file($saslfinger)){
		echo "<articadatascgi>". base64_encode(serialize(array("unable to stat pluginviewer")))."</articadatascgi>";
		return;
	}	
	
	exec("$saslfinger -c 2>&1",$returns);
	echo "<articadatascgi>". base64_encode(serialize($returns))."</articadatascgi>";	
	
}

function cups_delete_printer(){
	$unix=new unix();
	$printer=$_GET["cups-delete-printer"];
	$printer=urlencode($printer);
	$lpadmin=$unix->find_program("lpadmin");
	if(!is_file($lpadmin)){
			echo "<articadatascgi>". base64_encode(serialize(array("unable to stat lpadmin")))."</articadatascgi>";
			return;
		}
	
	exec("$lpadmin -x $printer",$returns);
	echo "<articadatascgi>". base64_encode(serialize($returns))."</articadatascgi>";	
}
function cups_add_printer(){
	$unix=new unix();
	$lpadmin=$unix->find_program("lpadmin");
	if(!is_file($lpadmin)){
			echo "<articadatascgi>". base64_encode(serialize(array("unable to stat lpadmin")))."</articadatascgi>";
			return;
		}
	//&name=$name&path=$path&driver=$driver&localization=$localization
	
	$array=unserialize(base64_decode($_GET["params"]));	
	$name=$array["name"];
	$name=urlencode($name);	
	$path=$array["path"];
	
	if(preg_match("#usb:\/\/(.+?)\/(.+)#",$path,$re)){
		writelogs_framework("Found printer name {$re[1]} slash {$re[2]}",__FUNCTION__,__FILE__,__LINE__);
		$re[1]=str_replace(" ","%20",$re[1]);
		$path="usb://".$re[1]."/".$re[2];
	}
	
	shell_exec("/bin/cp {$array["driver"]} /usr/share/cups/model/");
	shell_exec("/bin/chmod -R 777 /usr/share/cups/model");
	$driver=$array["driver"];
	$driver_name=basename($driver_name);
	$cmd="$lpadmin -p $name -L {$array["localization"]} -v \"$path\" -m $driver_name -o printer-is-shared=true";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$results);
	if(is_file("/etc/init.d/cups")){shell_exec("/etc/init.d/cups restart");}
	$cupsenable=$unix->find_program("cupsenable");
	if(is_file($cupsenable)){shell_exec("$cupsenable $name");}
	if(is_file($unix->LOCATE_CUPS_ACCEPT())){shell_exec($unix->LOCATE_CUPS_ACCEPT()." $name");}
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	

}

function samba_reconfigure(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");	
	$cmd=$nohup." /usr/share/artica-postfix/bin/artica-install --samba-reconfigure >/dev/null 2>&1 &";
	sys_THREAD_COMMAND_SET(trim($cmd));
	
}

function squid_config(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.squid.php --reconfigure");
}
function squid_rebuild(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=$nohup." ".LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.squid.php --build >/dev/null 2>&1 &";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);
}

function etc_hosts_open(){
	$datas=explode("\n",@file_get_contents("/etc/hosts"));
	while (list ($num, $ligne) = each ($datas) ){
		if(trim($ligne)==null){continue;}
		$newf[]=$ligne;
	}	
	$newf[]="\n";
	$datz=serialize($newf);
	echo "<articadatascgi>". base64_encode($datz)."</articadatascgi>";	
}
function etc_hosts_add(){
	$DisableEtcHosts=trim(@file_get_contents("/etc/artica-postfix/settings/Daemons/DisableEtcHosts"));
	if($DisableEtcHosts==1){
		writelogs_framework("DisableEtcHosts is enabled, skipping",__FUNCTION__,__FILE__,__LINE__);
		return;
	}
	$datas=explode("\n",@file_get_contents("/etc/hosts"));
	writelogs_framework(count($datas)." rows",__FUNCTION__,__FILE__,__LINE__);
	$datas[]=base64_decode($_GET["etc-hosts-add"]);
	while (list ($num, $ligne) = each ($datas) ){
	 	if(trim($ligne)==null){continue;}
		writelogs_framework("Adding $ligne in /etc/hosts",__FUNCTION__,__FILE__,__LINE__);
		$newf[]=$ligne;
	}
	@file_put_contents("/etc/hosts",implode("\n",$newf)."\n");
	
}
 
function etc_hosts_del(){
	$DisableEtcHosts=trim(@file_get_contents("/etc/artica-postfix/settings/Daemons/DisableEtcHosts"));
	if($DisableEtcHosts==1){
		writelogs_framework("DisableEtcHosts is enabled, skipping",__FUNCTION__,__FILE__,__LINE__);
		return;
	}
	$datas=explode("\n",@file_get_contents("/etc/hosts"));
	writelogs_framework("delete entry {$_GET["etc-hosts-del"]}",__FUNCTION__,__FILE__,__LINE__);
	while (list ($num, $ligne) = each ($datas) ){
		if(trim($ligne)==null){continue;}
		if(md5($ligne)==md5(trim($_GET["etc-hosts-del"]))){
			writelogs_framework("delete line $ligne",__FUNCTION__,__FILE__,__LINE__);
			continue;
		}
		$newf[]=$ligne;
		
	}
	
	@file_put_contents("/etc/hosts",implode("\n",$newf));
	
	
}

function etc_hosts_del_by_values(){
	$DisableEtcHosts=trim(@file_get_contents("/etc/artica-postfix/settings/Daemons/DisableEtcHosts"));
	if($DisableEtcHosts==1){
		writelogs_framework("DisableEtcHosts is enabled, skipping",__FUNCTION__,__FILE__,__LINE__);
		return;
	}	
	$ip=$_GET["ip"];
	$name=$_GET["name"];
	$datas=explode("\n",@file_get_contents("/etc/hosts"));
	writelogs_framework("delete entry $ip -> $name",__FUNCTION__,__FILE__,__LINE__);
	while (list ($num, $ligne) = each ($datas) ){
		if(preg_match("#([0-9\.]+)\s+(.+?)\s+#",$ligne,$re)){
			$ipf=$re[1];
			$servername=$re[2];
			if($ipf==$ip){
				if($servername==$name){
					writelogs_framework("skip $ligne",__FUNCTION__,__FILE__,__LINE__);
					continue;
				}
			}
		}
		$newf[]=$ligne;
	}

	@file_put_contents("/etc/hosts",implode("\n",$newf)."\n");
}

function file_content(){
	$datas=@file_get_contents(base64_decode($_GET["file-content"]));
	echo "<articadatascgi>". base64_encode($datas)."</articadatascgi>";	
}
function file_remove(){
	$f=base64_decode($_GET["file-remove"]);
	if(!is_file($f)){return;}
	@unlink($f);
	
}

function samba_smbclient(){
	$ini=new iniFrameWork("/etc/samba/smb.conf");
	$unix=new unix();
	$creds=unserialize(base64_decode($_GET["creds"]));
	$comp=$_GET["computer"];
	$cmd=$unix->find_program("smbclient")." -N -U {$creds[0]}%{$creds[1]} -L //$comp -g";
	exec($cmd,$results);
	if(is_array($results)){
		while (list ($num, $ligne) = each ($results) ){
			if(preg_match("#Disk\|(.+?)\|#",$ligne,$re)){
				$folder=$re[1];
				$array[$folder]=$ini->_params[$folder]["path"];
			}
		}
	}
	unset($array[$creds[0]]);
	if(!is_array($array)){$array=array();}
	writelogs_framework($cmd." =".count($array)." rows",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
	
	
}

function postfix_certificate(){
	$cmd='/usr/share/artica-postfix/bin/artica-install --change-postfix-certificate --verbose';
	exec($cmd,$results);
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
}

function certificate_infos(){
	$unix=new unix();
	$openssl=$unix->find_program("openssl");
	$l=$unix->FILE_TEMP();
	$f[]="/etc/ssl/certs/cyrus.pem";
	$f[]="/etc/ssl/certs/openldap/cert.pem";
	$f[]="/opt/artica/ssl/certs/lighttpd.pem";
	
	while (list ($num, $path) = each ($f) ){
		if(is_file($path)){
			$cmd="$openssl x509 -in $path -text -noout >$l 2>&1";
			break;
		}
	}
	
	if($cmd<>null){
		shell_exec($cmd);
		$datas=explode("\n",@file_get_contents($l));
		writelogs_framework($cmd." =".count($datas)." rows",__FUNCTION__,__FILE__,__LINE__);
		@unlink($l);
	}
	echo "<articadatascgi>". base64_encode(serialize($datas))."</articadatascgi>";
}

function process_kill_single(){
	if(!is_numeric($_GET["kill-pid-single"])){return;}
	if($_GET["kill-pid-single"]==null){return;}
	if($_GET["kill-pid-single"]<2){return;}	
	$unix=new unix();
	$cmd=$unix->find_program("kill")." -9 {$_GET["kill-pid-single"]}";
	writelogs_framework("kill PID process {$_GET["kill-pid-single"]}",__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);
	sleep(1);	
}

function process_kill(){
	if(!is_numeric($_GET["kill-pid-number"])){return;}
	if($_GET["kill-pid-number"]==null){return;}
	if($_GET["kill-pid-number"]<2){return;}
	process_kill_perform($_GET["kill-pid-number"]);
}

function process_kill_perform($pid){
		if($pid==null){return;}
		if($pid<2){return;}
		$unix=new unix();
		$array=$unix->PROCESS_STATUS($pid);
		if(!$array){return null;}
		if($array[0]="Z"){
			writelogs_framework("Zombie detected PPID:{$array[1]}",__FUNCTION__,__FILE__,__LINE__);
			process_kill_perform($array[1]);			
		}
		$cmd=$unix->find_program("kill")." -9 {$pid}";
		writelogs_framework("kill PID process $pid",__FUNCTION__,__FILE__,__LINE__);
		shell_exec($cmd);
}

function TCP_LIST_NICS(){
	$datas=explode("\n",@file_get_contents("/proc/net/dev"));
	while (list ($num, $line) = each ($datas) ){
		if(preg_match("#^(.+?):#",$line,$re)){
			if(trim($re[1])=="lo"){continue;}
			if(preg_match("#pan[0-9]+#",$re[1])){continue;}
			if(preg_match("#tun[0-9]+#",$re[1])){continue;}
			if(preg_match("#vboxnet[0-9]+#",$re[1])){continue;}
			if(preg_match("#wmaster[0-9]+#",$re[1])){continue;}
			$re[1]=trim($re[1]);
			writelogs_framework("found '{$re[1]}'",__FUNCTION__,__FILE__,__LINE__);
			$array[]=trim($re[1]);
		}
	}
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
	
}

function TCP_NIC_STATUS(){
	exec("/usr/share/artica-postfix/bin/artica-install --nicstatus {$_GET["nicstatus"]}",$results);
	$datas=trim(@implode(" ",$results));
	writelogs_framework("artica-install --nicstatus {$_GET["nicstatus"]} ->$datas",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>$datas</articadatascgi>";
}

function TCP_NIC_INFOS(){
	exec("/usr/share/artica-postfix/bin/artica-install --nicinfos {$_GET["nic-infos"]}",$results);
	$datas=trim(@implode("\n",$results));
	writelogs_framework($datas,__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>$datas</articadatascgi>";	
}



function samba_logon_scripts(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.samba.php --logon-scripts");
}

function TCP_VIRTUALS(){
	if(isset($_GET["stay"])){
		shell_exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.virtuals-ip.php");
		return;
	}
	
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$php5=$unix->LOCATE_PHP5_BIN();
	$cmd="$nohup $php5 /usr/share/artica-postfix/exec.virtuals-ip.php >/dev/null 2>&1 &";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec(trim($cmd));
}

function TCP_VLANS(){
	if(isset($_GET["stay"])){

		shell_exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.virtuals-ip.php --vlans");
		return;
	}
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.virtuals-ip.php --vlans");
	
}

function TCP_VIRTUALS_BUILD_BRIDGES(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.virtuals-ip.php --bridges");
}



function MalwarePatrol(){
	if(!is_file("/etc/squid3/malwares.acl")){@file_put_contents("/etc/squid3/malwares.acl","#");}
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-update --MalwarePatrol");
	}
	
function MalwarePatrol_list(){
	$unix=new unix();
	$tail=$unix->find_program("tail");
	$grep=$unix->find_program("grep");
	$pattern=trim(base64_decode($_GET["pattern"]));
	
	if($pattern==null){
		$cmd="$tail -n 200 /etc/squid3/malwares.acl 2<&1";
	}else{
		$pattern=str_replace("*",".*?",$pattern);
		$cmd="$grep -E '$pattern' /etc/squid3/malwares.acl 2>&1";
	}
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$results);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
	
}
	
function MalwarePatrolDatabasesCount(){
	$datas=explode("\n",@file_get_contents("/etc/squid3/malwares.acl"));
	$count=0;
	while (list ($num, $line) = each ($datas) ){
		if(trim($line)==null){continue;}
		if(substr($line,0,1)=="#"){continue;}
		$count=$count+1;
	}
	echo "<articadatascgi>$count</articadatascgi>";
	
}

function postfix_multi_queues(){
	$instance=$_GET["postfix-multi-queues"];
	$unix=new unix();
	$queue_directory=trim($unix->POSTCONF_MULTI_GET($instance,"queue_directory"));
	$queues=array("active","bounce","corrupt","defer","deferred","flush","hold","incoming");
	
	while (list ($num, $queuename) = each ($queues) ){
		$array["$queuename"]=$unix->dir_count_files_recursive("$queue_directory/$queuename");
	}
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
}


function postfix_multi_postqueue(){
	$instance=$_GET["postfix-multi-postqueue"];
	if($instance==null){$instance="MASTER";}
	$array=unserialize(@file_get_contents("/var/log/artica-postfix/postqueue.$instance"));
	echo "<articadatascgi>". base64_encode($array["COUNT"])."</articadatascgi>";
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.watchdog.postfix.queue.php");
	
}
function postfix_multi_cfdb(){
	$hostname=trim($_GET["postfix-multi-cfdb"]);
	if($hostname=="master"){
		sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.hashtables.php --aliases");
		sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.hashtables.php --transport");
		return;
	}
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix-multi.php --instance-reconfigure \"$hostname\"");
}
function postfix_body_checks(){
	$hostname=trim($_GET["postfix-body-checks"]);
	if($hostname=="master"){
		sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.maincf.php --body-checks");
		return;
	}
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix-multi.php --instance-reconfigure \"$hostname\"");
}



function postfix_smtp_senders_restrictions(){
	$hostname=trim($_GET["postfix-smtp-sender-restrictions"]);
	if($hostname=="master"){
		sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.maincf.php --smtp-sender-restrictions");
		return;
	}
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix-multi.php --instance-reconfigure \"$hostname\"");	
}



function postfix_postqueue_delete_msgid(){
	$unix=new unix();
	$postsuper=$unix->find_program("postsuper");
	$cmd="$postsuper -d {$_GET["postsuper-d-master"]}";
	
	exec("$cmd",$results);
	writelogs_framework("EXEC:: $cmd ".@implode("\n",$results),__FUNCTION__,__LINE__);
	echo "<articadatascgi>". base64_encode(trim(@implode(" ",$results)))."</articadatascgi>";
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.watchdog.postfix.queue.php");
}
function postfix_postqueue_reprocess_msgid(){
	$unix=new unix();
	$postsuper=$unix->find_program("postsuper");
	exec("$postsuper -r {$_GET["postsuper-r-master"]}",$results);
	echo "<articadatascgi>". base64_encode(trim(@implode(" ",$results)))."</articadatascgi>";
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.watchdog.postfix.queue.php");	
}

function postfix_postqueue_f(){
	$unix=new unix();
	$postqueue=$unix->find_program("postqueue");
	$hostname=$_GET["hostname"];
	if($hostname==null){$hostname="master";}
	if($hostname<>"master"){$c=" -c /etc/postfix-$hostanme";}
	exec("$postqueue -f$c 2>&1",$results);
	$results[]="OK: $postqueue -f$c";	
	echo "<articadatascgi>". @implode("\n",$results)."</articadatascgi>";
	$unix->THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix-logger.php --postqueue");
}


function postfix_postqueue_master(){
	$instance=$_GET["postfix-multi-postqueue"];
	if($instance==null){$instance="MASTER";}
	writelogs_framework("OPEN:: /var/log/artica-postfix/postqueue.$instance",__FUNCTION__,__LINE__);
	echo "<articadatascgi>". base64_encode(@file_get_contents("/var/log/artica-postfix/postqueue.$instance"))."</articadatascgi>";
	
}





function ASSP_MULTI_CONFIG(){
	$ou=base64_decode($_GET["assp-multi-load-config"]);
	$instance=str_replace(" ","-",$ou);
	$path="/usr/share/assp-$instance/assp.cfg";
	if($ou=="DEFAULT"){
		$path="/usr/share/assp/assp.cfg";
	}
	$data=explode("\n",@file_get_contents($path));
	while (list ($num, $line) = each ($data) ){
		if(preg_match("#(.+?):=(.*)#",$line,$re)){
			$array[trim($re[1])]=trim($re[2]);
		}
	}
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
	
}

function START_STOP_SERVICES(){
	$md5=$_GET["APP"].$_GET["action"].$_GET["cmd"];
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	@file_put_contents("/usr/share/artica-postfix/ressources/logs/web/$md5.log","...");
	@chmod("/usr/share/artica-postfix/ressources/logs/web/$md5.log",0777);
	$cmd=trim("$nohup /etc/init.d/artica-postfix {$_GET["action"]} {$_GET["cmd"]} >>/usr/share/artica-postfix/ressources/logs/web/$md5.log 2>&1 &");
	writelogs_framework("$cmd",__FUNCTION__,__LINE__);
	shell_exec($cmd);
	
	
}

function kas_reconfigure(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.kasfilter.php");
}

function retranslator_execute(){
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-update --retranslator");
}
function retranslator_dbsize(){
	$unix=new unix();
	$cmd=$unix->find_program("du")." -h -s /var/db/kav/databases 2>&1";
	exec($cmd,$results);
	$text=trim(implode(" ",$results));
	if(preg_match("#^([0-9\.\,A-Z]+)#",$text,$re)){
		$dbsize=$re[1];
	}
	
	
	echo "<articadatascgi>". base64_encode($dbsize)."</articadatascgi>";
}
function retranslator_tmp_dbsize(){
	$unix=new unix();
	$array=$unix->getDirectories("/tmp");
	while (list ($num, $ligne) = each ($array) ){
		if(preg_match("#(.+?)\/temporaryFolder\/bases\/av#",$ligne,$re)){
			$folder=$re[1];
		}
	}
	if(is_dir($folder)){
		$cmd=$unix->find_program("du")." -h -s $folder 2>&1";
		exec($cmd,$results);
		$text=trim(implode(" ",$results));
		if(preg_match("#^([0-9\.\,A-Z]+)#",$text,$re)){
			$dbsize=$re[1];
		}
	}else{
		$dbsize="0M";
	}
	
echo "<articadatascgi>". base64_encode($dbsize)."</articadatascgi>";
}






function retranslator_restart(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart retranslator");
}
function retranslator_sites_lists(){
	$cmd="/usr/share/artica-postfix/bin/retranslator.bin -s -c /etc/kretranslator/retranslator.conf 2>&1";
	exec($cmd,$results);
	writelogs_framework(count($results)." lines [$cmd]",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
}
function retranslator_events(){
	$unix=new unix();
	$cmd=$unix->find_program("tail").' -n 100 /var/log/kretranslator/retranslator.log 2>&1';
	exec($cmd,$results);
	writelogs_framework(count($results)." lines [$cmd]",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
}
function retranslator_status(){
	$cmd="/usr/share/artica-postfix/bin/artica-install --retranslator-status 2>&1";
	exec($cmd,$results);
	writelogs_framework(count($results)." lines [$cmd]",__FUNCTION__,__FILE__,__LINE__);
	$datas=implode("\n",$results);
	
	echo "<articadatascgi>". base64_encode($datas)."</articadatascgi>";	
}

function hamachi_net(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.hamachi.php");
//if(isset($_GET["hamachi-net"])){hamachi_net();exit;} 
}

function hamachi_status(){
	exec("/usr/share/artica-postfix/bin/artica-install --hamachi-status",$rr);
	$ini=new iniFrameWork();
	$ini->loadString(implode("\n",$rr));
	echo "<articadatascgi>". base64_encode(serialize($ini->_params))."</articadatascgi>";
}
function hamachi_sessions(){
	$unix=new unix();
	$session=array();
	exec($unix->find_program("hamachi")." -c /etc/hamachi list",$l);
	while (list ($num, $ligne) = each ($l) ){
		if(preg_match("#\[(.+?)\]#",$ligne,$re)){$net=$re[1];continue;}
		if(preg_match("#([0-9\.]+)#",$ligne,$re)){
			$session[$net][]=$re[1];
		}
	}
	echo "<articadatascgi>". base64_encode(serialize($session))."</articadatascgi>";
}

function hamachi_currentIP(){
	
	$datas=explode("\n",@file_get_contents("/etc/hamachi/state"));
	while (list ($num, $ligne) = each ($datas) ){
		if(preg_match("#Identity\s+([0-9\.]+)#",$ligne,$re)){
			echo "<articadatascgi>". $re[1]."</articadatascgi>";
			break;
		}
	}
	
}

function hamachi_restart(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart hamachi");
	
}

function POWERDNS_RESTART(){
	$unix=new unix();
	$unix->THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart pdns");
}

function hamachi_delete_network(){
	$unix=new unix();
	$_GET["hamachi-delete-net"]=base64_decode($_GET["hamachi-delete-net"]);
	exec($unix->find_program("hamachi")." -c /etc/hamachi leave {$_GET["hamachi-delete-net"]}",$l);
	exec($unix->find_program("hamachi")." -c /etc/hamachi delete {$_GET["hamachi-delete-net"]}",$l);
}

function Kav4ProxyUpdate(){
	$unix=new unix();
	$cmd="/opt/kaspersky/kav4proxy/bin/kav4proxy-keepup2date";
	$type=$_GET["type"];
	
	if($type=="milter"){
		if(is_file("/opt/kav/5.6/kavmilter/bin/keepup2date")){
			$cmd="/opt/kav/5.6/kavmilter/bin/keepup2date";
		}
		
		if(is_file("/opt/kaspersky/kav4lms/bin/kav4lms-keepup2date")){
			shell_exec("/usr/share/artica-postfix/bin/artica-install --kavm4mls-info");
			$cmd="/opt/kaspersky/kav4lms/bin/kav4lms-keepup2date";
		}
		
	}
	
	if($type=="kas"){
		$cmd="/usr/local/ap-mailfilter3/bin/keepup2date -c /usr/local/ap-mailfilter3/etc/keepup2date.conf";
	}
	

	$pid=$unix->PIDOF(basename($cmd));
	if(strlen($pid)>0){return;}
	@file_put_contents("/usr/share/artica-postfix/ressources/logs/Kav4ProxyUpdate","{waiting}...\nSelected $type: ".basename($cmd)."\n\n");
	@chmod("/usr/share/artica-postfix/ressources/logs/Kav4ProxyUpdate",0775);
	sys_THREAD_COMMAND_SET("$cmd >>/usr/share/artica-postfix/ressources/logs/Kav4ProxyUpdate");
}


function SendmailPath(){
	$unix=new unix();
	echo "<articadatascgi>". base64_encode($unix->LOCATE_SENDMAIL_PATH())."</articadatascgi>";
}


function kasmilter_license(){
	$unix=new unix();
	$tmpstr=$unix->FILE_TEMP();	
	$cmd="/usr/local/ap-mailfilter3/bin/licensemanager -c /usr/local/ap-mailfilter3/etc/keepup2date.conf";
	exec("$cmd -s >$tmpstr 2>&1");
	$results=explode("\n",@file_get_contents($tmpstr));
	@unlink($tmpstr);
	$results[]="--------------------------------------------------------------";
	$results[]=basename($cmd);
	$results[]="--------------------------------------------------------------";
	echo "<articadatascgi>". base64_encode(implode("\n",$results))."</articadatascgi>";	
}

function kavmilter_license(){
	$unix=new unix();
	$tmpstr=$unix->FILE_TEMP();	
	if(is_file("/opt/kav/5.6/kavmilter/bin/licensemanager")){
		$cmd="/opt/kav/5.6/kavmilter/bin/licensemanager";
		
	}
	
	if(is_file("/opt/kaspersky/kav4lms/bin/kav4lms-licensemanager")){
		$cmd="/opt/kaspersky/kav4lms/bin/kav4lms-licensemanager";
	}
	exec("$cmd -s >$tmpstr 2>&1");
	$results=explode("\n",@file_get_contents($tmpstr));
	@unlink($tmpstr);
	$results[]="--------------------------------------------------------------";
	$results[]=basename($cmd);
	$results[]="--------------------------------------------------------------";
	echo "<articadatascgi>". base64_encode(implode("\n",$results))."</articadatascgi>";
	
}

function kav4proxy_license(){
	$unix=new unix();
	$tmpstr=$unix->FILE_TEMP();
	$kav4proxyCache="/etc/artica-postfix/kav4proxy-licensemanager";
	$cmd="/opt/kaspersky/kav4proxy/bin/kav4proxy-licensemanager";
	if($_GET["type"]=="milter"){kavmilter_license();return;}
	if($_GET["type"]=="kas"){kasmilter_license();return;}
	
	if(is_file($kav4proxyCache)){
		if($unix->file_time_min($kav4proxyCache)>2880){
			exec("$cmd -s >$kav4proxyCache 2>&1");
		}
		
	}else{
		exec("$cmd -s >$kav4proxyCache 2>&1");
	}
	
	
	$results=explode("\n",@file_get_contents($kav4proxyCache));
	@unlink($tmpstr);
	$results[]="--------------------------------------------------------------";
	$results[]=basename($cmd);
	$results[]="--------------------------------------------------------------";
	echo "<articadatascgi>". base64_encode(implode("\n",$results))."</articadatascgi>";	
	
	
}

function kav4proxy_upload_license(){
	$f=$_GET["Kav4ProxyUploadLicense"];
	$type=$_GET["type"];
	@unlink("/etc/artica-postfix/kav4proxy-licensemanager");
	$cmd="/opt/kaspersky/kav4proxy/bin/kav4proxy-licensemanager";
	if($type=="milter"){
		if(is_file("/opt/kav/5.6/kavmilter/bin/licensemanager")){
			$cmd="/opt/kav/5.6/kavmilter/bin/licensemanager";
			
		}
		
		if(is_file("/opt/kaspersky/kav4lms/bin/kav4lms-licensemanager")){
			$cmd="/opt/kaspersky/kav4lms/bin/kav4lms-licensemanager";
		}
		
	}
	
	
	if($type="kas"){$cmd="/usr/local/ap-mailfilter3/bin/licensemanager -c /usr/local/ap-mailfilter3/etc/keepup2date.conf";}
	
	$unix=new unix();
	$tmpf=$unix->FILE_TEMP();
	shell_exec("$cmd -a $f >$tmpf 2>&1");
	$results=explode("\n",@file_get_contents($results));
	@unlink($tmpf);
	$results[]="--------------------------------------------------------------";
	$results[]=basename($cmd) .":$cmd -a $f";
	$results[]="--------------------------------------------------------------";
	echo "<articadatascgi>". base64_encode(implode("\n",$results))."</articadatascgi>";		
	
}
function kav4proxy_reload(){
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --reload-kav4proxy");
}


function kav4proxy_delete_license(){
	@unlink("/etc/artica-postfix/kav4proxy-licensemanager");
	$type=$_GET["type"];
	$cmd="/opt/kaspersky/kav4proxy/bin/kav4proxy-licensemanager";
	if($type=="milter"){
		if(is_file("/opt/kav/5.6/kavmilter/bin/licensemanager")){
			$cmd="/opt/kav/5.6/kavmilter/bin/licensemanager";
			
		}
		
		if(is_file("/opt/kaspersky/kav4lms/bin/kav4lms-licensemanager")){
			$cmd="/opt/kaspersky/kav4lms/bin/kav4lms-licensemanager";
		}
		
	}
	
	if($type="kas"){$cmd="/usr/local/ap-mailfilter3/bin/licensemanager -c /usr/local/ap-mailfilter3/etc/keepup2date.conf";}
	
	exec("$cmd -a $f",$results);
	$results[]="--------------------------------------------------------------";
	$results[]=basename($cmd) .":$cmd -da";
	$results[]="--------------------------------------------------------------";
	echo "<articadatascgi>". base64_encode(implode("\n",$results))."</articadatascgi>";		
	
}


function kav4lms_bases_infos(){
	$unix=new unix();
	if(is_file("/opt/kav/5.6/kavmilter/bin/licensemanager")){
		$cmd="/usr/share/artica-postfix/bin/artica-install --kavmilter-pattern";
		$cmd2=$unix->find_program("du"). " -h -s /var/db/kav/databases";
	}
	
	if(is_file("/opt/kaspersky/kav4lms/bin/kav4lms-licensemanager")){
		$cmd="/usr/share/artica-postfix/bin/artica-install --kavm4mls-pattern";
		$cmd2=$unix->find_program("du"). " -h -s /var/opt/kaspersky/kav4lms/bases/";
	}	
	
	exec($cmd,$results);
	$f=trim(implode("",$results));
	$d=substr($f,0,2);
	$m=substr($f,2,2);
	$y=substr($f,4,4);
	$h=substr($f,9,2);
	$M=substr($f,11,2);
	$date="$y-$m-$d $h:$M:00";
	unset($results);

	exec($cmd2,$results);
	$f=trim(implode(" ",$results));
	
	
	$f=str_replace(",",".",$f);
	preg_match("#([0-9\.A-Z]+)\s+#",$f,$re);
	$size=$re[1];
	
	echo "<articadatascgi>". base64_encode(serialize(array($date,$size)))."</articadatascgi>";
	
}

function kasversion(){
	exec("/usr/share/artica-postfix/bin/artica-install --kas3-version",$results);
	preg_match("#([0-9\.]+);([0-9]+);([0-9]+)#",implode("",$results),$re);
	$array["version"]=$re[1];
	$f=$re[2];
	$d=substr($f,0,2);
	$m=substr($f,2,2);
	$y=substr($f,4,4);
	
	$f=$re[3];
	$H=substr($f,0,2);
	$M=substr($f,2,2);
	$array["pattern"]="$y-$m-$d $H:$M:00";
	$unix=new unix();
	unset($results);
	$cmd2=$unix->find_program("du"). " -h -s /usr/local/ap-mailfilter3/cfdata/bases/";
	exec($cmd2,$results);
	$f=trim(implode(" ",$results));
	$f=str_replace(",",".",$f);
	preg_match("#([0-9\.A-Z]+)\s+#",$f,$re);
	$size=$re[1];
	$array["size"]=$size;
	
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
	
}

function release_quarantine(){
	$array=unserialize(base64_decode($_GET["release-quarantine"]));
	$unix=new unix();
	$tmpfileConf=$unix->FILE_TEMP();
	
	$msmtp[]= "syslog on";
	$msmtp[]="from {$array["from"]}";
	$msmtp[]="protocol smtp";
	$msmtp[]="host 127.0.0.1";
	$msmtp[]="port 33559";
	@file_put_contents($tmpfileConf,implode("\n",$msmtp));
	if(is_file("/usr/share/artica-postfix/bin/artica-msmtp")){$msmtp_cmd="/usr/share/artica-postfix/bin/artica-msmtp";}
	if(is_file($unix->find_program("msmtp"))){$msmtp_cmd=$unix->find_program("msmtp");}
	$logfile=$unix->FILE_TEMP().".log";
	chmod($tmpfileConf,0600);
	$cmd="$msmtp_cmd --tls-certcheck=off --timeout=10 --file=$tmpfileConf --syslog=on  --logfile=$logfile -- {$array["to"]} <{$array["file"]}";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);
	$data=explode("\n",@file_get_contents($logfile));
	writelogs_framework(implode("\n",$data),__FUNCTION__,__FILE__,__LINE__);
	@unlink($logfile);
	@unlink($tmpfileConf);
	echo "<articadatascgi>". base64_encode(serialize($data))."</articadatascgi>";
}

if(isset($_GET["uninstall-app"])){application_uninstall();exit;}

function application_uninstall(){
	$cmdline=base64_decode($_GET["uninstall-app"]);
	$app=$_GET["app"];
	$unix=new unix();
	@unlink("/usr/share/artica-postfix/ressources/install/$app.ini");
	@unlink("/usr/share/artica-postfix/ressources/install/$app.dbg");
	$tmpstr="/usr/share/artica-postfix/ressources/logs/UNINSTALL_$app";
	
	@file_put_contents($tmpstr,"Scheduled.....");
	@chmod($tmpstr,0755);
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install $cmdline >>$tmpstr 2>&1");
	$results=explode("\n",@file_get_contents($tmpstr));
	@unlink($tmpstr);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
}

function application_debug_infos(){
	$appli=$_GET["AppliCenterGetDebugInfos"];
	$results=explode("\n",@file_get_contents("/usr/share/artica-postfix/ressources/install/$appli.dbg"));
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
	
}
function application_service_install(){
	$cmdline=base64_decode($_GET["services-install"]);
	writelogs_framework("launch $cmdline !!!",__FUNCTION__,__FILE__,__LINE__);
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/setup-ubuntu $cmdline");
}
function Restart_Policyd_Weight(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart policydw");
}

function dansguardian_update(){
	$cmd="/usr/share/artica-postfix/bin/artica-update --dansguardian --verbose";
	file_put_contents("/usr/share/artica-postfix/ressources/logs/DANSUPDATE","{waiting}...\n\n\n");
	@chmod("/usr/share/artica-postfix/ressources/logs/DANSUPDATE",0775);
	sys_THREAD_COMMAND_SET("$cmd >>/usr/share/artica-postfix/ressources/logs/DANSUPDATE");	
	}


function ldap_upload_organization(){
	$ou=base64_decode($_GET["upload-organization"]);
	
	writelogs_framework("Exporting $ou",__FUNCTION__,__FILE__,__LINE__);
	
	$config=$_GET["config-file"];
	$unix=new unix();
	$tmpstr=$unix->FILE_TEMP();
	writelogs_framework("executing  ".LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.ldap.move-orgs.php --upload \"$ou\" \"$config\" >$tmpstr 2>&1",__FUNCTION__,__FILE__,__LINE__);
	shell_exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.ldap.move-orgs.php --upload \"$ou\" \"$config\" >$tmpstr 2>&1");
	$results=explode("\n",@file_get_contents($tmpstr));
	@unlink($tmpstr);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
}
function ifconfig_interfaces(){
	$unix=new unix();
	$cmd=$unix->find_program("ifconfig")." -s";
	exec($cmd,$results);
	while (list ($index, $line) = each ($results) ){
		if(preg_match("#^(.+?)\s+[0-9]+#",$line,$re)){
			$array[trim($re[1])]=trim($re[1]);
		}
	}
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
	
}
function ifconfig_all(){
	$unix=new unix();
	$cmd=$unix->find_program("ifconfig")." -a 2>&1";
	exec($cmd,$results);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
	
}
function ifconfig_all_ips(){
	$unix=new unix();
	$cmd=$unix->find_program("ifconfig")." -a 2>&1";
	exec($cmd,$results);
	while (list ($index, $line) = each ($results) ){
		if(preg_match("#inet (adr|addr):([0-9\.]+)#",$line,$re)){
			
			$ri[$re[2]]=$re[2];
		}else{
			//writelogs_framework("no match $line",__FUNCTION__,__FILE__);
			
		}
		
	}
	echo "<articadatascgi>". base64_encode(serialize($ri))."</articadatascgi>";
	
}


function organization_delete(){
	
	$ou=base64_decode($_GET["organization-delete"]);
	$deletmailboxes=$_GET["delete-mailboxes"];
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.delete-ou.php $ou $deletmailboxes");
}

function fetchmail_status(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --fetchmail --nowachdog",$results);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
}
function fetchmail_logs(){
	$unix=new unix();
	$tail=$unix->find_program("tail");
	exec("$tail -n 200 /var/log/fetchmail.log",$results);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
	
}

function AD_IMPORT_SCHEDULE(){
	$ou=base64_decode($_GET["ou"]);
	$schedule=base64_decode($_GET["schedule"]);
	@mkdir("/etc/artica-postfix/ad-import");
	$f="$schedule ".LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.ad-import-ou.php $ou"; 
	$file="/etc/artica-postfix/ad-import/import-ad-".md5($ou);
	@file_put_contents($file,$f);
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart daemon");

}

function LDAP_IMPORT_SCHEDULE(){
	$unix=new unix();
	$php5=$unix->LOCATE_PHP5_BIN();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup $php5 /usr/share/artica-postfix/exec.domains.ldap.import.php --schedules >/dev/null 2>&1");
	shell_exec($cmd);
}

function LDAP_IMPORT_EXEC(){
	$unix=new unix();
	$php5=$unix->LOCATE_PHP5_BIN();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup $php5 /usr/share/artica-postfix/exec.domains.ldap.import.php --import {$_GET["ID"]} >/dev/null 2>&1");
	shell_exec($cmd);	
}


function AD_REMOVE_SCHEDULE(){
	$ou=base64_decode($_GET["ou"]);
	$file="/etc/artica-postfix/ad-import/import-ad-".md5($ou);
	writelogs_framework("Remove $file");
	@unlink($file);
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart daemon");
}

function AD_PERFORM(){
$ou=base64_decode($_GET["ou"]);

$file="/usr/share/artica-postfix/ressources/logs/web/ad-$ou.log";
@file_put_contents($file,"{scheduled}...");
@chmod($file,777);
sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.ad-import-ou.php $ou");
}

function backup_sql_tests(){
	writelogs_framework("Testing backup id {$_GET["backup-sql-test"]}",__FUNCTION__,__FILE__,__LINE__);
	exec(LOCATE_PHP5_BIN2() ." /usr/share/artica-postfix/exec.backup.php {$_GET["backup-sql-test"]} --only-test --verbose",$results);
	
	writelogs_framework(count($results)." line",__FUNCTION__,__FILE__,__LINE__);
	
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
}

function backup_task_run(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.backup.php {$_GET["backup-task-run"]}");
}


function backup_build_cron(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.backup.php --cron");
}
function GlobalApplicationsStatus(){
	$unix=new unix();
	$mainfile="/usr/share/artica-postfix/ressources/logs/global.versions.conf";
	$mainstatus="/usr/share/artica-postfix/ressources/logs/global.status.ini";
	if(!is_file($mainfile)){
		shell_exec("/usr/share/artica-postfix/bin/artica-install -versions > /usr/share/artica-postfix/ressources/logs/global.versions.conf 2>&1");
	}
	if(!is_file($mainstatus)){
            shell_exec('/usr/share/artica-postfix/bin/artica-install --status > /usr/share/artica-postfix/ressources/logs/global.status.ini 2>&1');
	}
	
	$datas=@file_get_contents($mainstatus)."\n".@file_get_contents($mainfile);
	
	if($unix->file_time_min($mainstatus)>0){
		@unlink($mainfile);
		@unlink($mainstatus);
		sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install -versions >/usr/share/artica-postfix/ressources/logs/global.versions.conf");
		sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --status >/usr/share/artica-postfix/ressources/logs/global.status.ini");
	}
	sys_THREAD_COMMAND_SET("/bin/chmod 755 /usr/share/artica-postfix/ressources/logs/global.*");
	echo "<articadatascgi>". base64_encode($datas)."</articadatascgi>";
}
function resolv_conf(){
	$datas=explode("\n",@file_get_contents("/etc/resolv.conf"));
	echo "<articadatascgi>". base64_encode(serialize($datas))."</articadatascgi>";
	}
	
function GetMyDNSServers(){
	$datas=explode("\n",@file_get_contents("/etc/resolv.conf"));
	writelogs_framework("resolv.conf - > ". count($datas)." rows",__FUNCTION__,__FILE__,__LINE__);
	while (list ($index, $line) = each ($datas) ){
	if(preg_match("#nameserver\s+(.+)#",$line,$re)){
		writelogs_framework("found {$re[1]}",__FUNCTION__,__FILE__,__LINE__);
		$ip=trim($re[1]);
		if($ip==null){continue;}
		if($ip=="127.0.0.1"){continue;}
		$array[]=$ip;
		} 
	}
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
}	
	
function MyOs(){
	exec("/usr/share/artica-postfix/bin/artica-install --myos 2>&1",$results);
	writelogs_framework(trim(implode("",$results)),__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>". trim(implode("",$results))."</articadatascgi>";
}
function lspci(){
	$unix=new unix();
	$lspci=$unix->find_program("lspci");
	exec("$lspci 2>&1",$results);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
}
function freemem(){
	$unix=new unix();
	$prog=$unix->find_program("free");
	exec("$prog -m -o 2>&1",$results);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	
}
function dfmoinsh(){
	$unix=new unix();
	$prog=$unix->find_program("df");
	exec("$prog -h 2>&1",$results);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	
}
function printenv(){
	$unix=new unix();
	$prog=$unix->find_program("printenv");
	exec("$prog 2>&1",$results);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	
}
function GenerateCert(){
	$path=$_GET["path"];
	exec("/usr/share/artica-postfix/bin/artica-install --gen-cert $path",$results);
	echo "<articadatascgi>". trim(implode(" ",$results))."</articadatascgi>";
}
function GLOBAL_STATUS(){
exec("/usr/share/artica-postfix/bin/artica-install --all-status",$results);	
echo "<articadatascgi>". base64_encode((implode("\n",$results)))."</articadatascgi>";
}

function MONIT_STATUS(){
	$unix=new unix();
	$array=$unix->monit_array();
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
}



function MONIT_RESTART(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart monit");
}

function LIGHTTPD_RESTART(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart apache");
}

function FCRON_RESTART(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart cron");
}
function NFS_RELOAD(){
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --nfs-reload");
}

function MLDONKEY_RESTART() {
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart mldonkey");
}

function sabnzbdplus_restart(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart sabnzbdplus");
}

function EMAILRELAY_RESTART(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart artica-notifier");
}

function DNS_LIST(){
	exec("/usr/share/artica-postfix/bin/artica-install --local-dns",$results);
	echo "<articadatascgi>". implode("",$results)."</articadatascgi>";
}

FUNCTION procstat(){
	exec("/usr/share/artica-postfix/bin/procstat {$_GET["procstat"]}",$results);
	while (list ($index, $line) = each ($results) ){
		if(preg_match("#(.+?):(.+)#",$line,$re)){
			$array[trim($re[1])]=trim($re[2]);
		}
	}
	
	if($array["start_time"]<>null){
		if(preg_match("#\(([0-9]+)#",$array["start_time"],$re)){
			$mins=$re[1]/60;
			$text="{$mins}mn";
			if($mins>60){
				$h=round($mins/60,2);
				if(preg_match("#(.+?)\.(.+)#",$h,$re)){
					if(strlen($re[2])==1){$re[2]="{$re[2]}0";}
					$text="{$re[1]}h {$re[2]}mn";
				}else{
					$text="{$h}h";
				}
			}
		}
	}
	
	$array["since"]=$text;
	
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";	
	
}

function imapsync_events(){
	$f="/usr/share/artica-postfix/ressources/logs/imapsync.{$_GET["imapsync-events"]}.logs";
	if(is_file($f)){
		exec("tail -n 300 $f",$datas);
	}else{
		writelogs_framework("unable to stat imapsync.{$_GET["imapsync-events"]}.logs",__FUNCTION__,__FILE__);
		exit;
	}
	writelogs_framework(basename($f).": ".count($datas)." rows",__FUNCTION__,__FILE__);
	echo "<articadatascgi>". base64_encode(serialize($datas))."</articadatascgi>";	
}

function imapsync_cron(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.mailsync.php --cron");
}
function imapsync_run(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.mailsync.php --sync {$_GET["imapsync-run"]}");
}
function imapsync_stop(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.mailsync.php --stop {$_GET["imapsync-stop"]}");
}

function imapsync_show(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.mailsync.php --sync {$_GET["imapsync-show"]} --verbose",$results);
	$datas=@implode("\n",$results);
	echo "<articadatascgi>". base64_encode($datas)."</articadatascgi>";
}


function cyrus_restore_mount_dir(){
	$taskid=$_GET["cyr-restore"];
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.backup.php --mount $taskid",$results);
	writelogs_framework(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.backup.php --mount $taskid",__FUNCTION__,__FILE__);
	$datas=trim(implode("",$results));
	writelogs_framework(strlen($datas)." bytes",__FUNCTION__,__FILE__);
	echo "<articadatascgi>". base64_encode($datas)."</articadatascgi>";	
	
}

function cyr_restore_computer(){
	$taskid=$_GET["cyr-restore-computer"];
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.backup.php --mount --id=$taskid --dir={$_GET["dir"]}",$results);
	$datas=trim(implode("",$results));
	writelogs_framework($datas,__FUNCTION__,__FILE__);
	echo "<articadatascgi>". base64_encode($datas)."</articadatascgi>";		
	// cyr-restore-computer
	
}
//cyr-restore-container
function cyr_restore_container(){
	$taskid=$_GET["cyr-restore-container"];
	$_GET["dir"]=base64_decode($_GET["dir"]);
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.backup.php --mount --id=$taskid --dir={$_GET["dir"]} --list",$results);
	$datas=trim(implode("",$results));
	writelogs_framework($datas,__FUNCTION__,__FILE__);
	echo "<articadatascgi>". base64_encode($datas)."</articadatascgi>";			
}
function cyr_restore_mailbox(){
	$datas=$_GET["cyr-restore-mailbox"];
	writelogs_framework($datas,__FUNCTION__,__FILE__);
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.backup.php --restore-mbx $datas");
}
function disk_format_big_partition(){
	exec("/usr/share/artica-postfix/bin/artica-install --format-b-part {$_GET["dev"]} {$_GET["label"]}",$datas);
	$r=implode("\n",$datas);
	echo "<articadatascgi>". base64_encode($r)."</articadatascgi>";			
	
}
function RestartRsyncServer(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart rsync");
}

function rsync_load_config(){
	$datas=@file_get_contents("/etc/rsync/rsyncd.conf");
	echo "<articadatascgi>". base64_encode($datas)."</articadatascgi>";		
}
function rsync_save_conf(){
	$datas=base64_decode($_GET["rsync-save-conf"]);
	@file_put_contents("/etc/rsync/rsyncd.conf",$datas);
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --reload-rsync");
}
function ARTICA_MAILLOG_RESTART(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart postfix-logger");
}
function disk_directory_size(){
	$dir=base64_decode($_GET["DirectorySize"]);
	$unix=new unix();
	exec($unix->find_program("du")." -h -s $dir 2>&1",$results);
	$r=implode("",$results);
	if(preg_match("#^(.+?)\s+#",$r,$re)){
		echo "<articadatascgi>". $re[1]."</articadatascgi>";		
	}
}

function cyrus_move_default_dir_to_currentdir(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.cyrus-restore.php --move-default-current");
}
function  cyrus_move_newdir(){
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.cyrus-restore.php --move-new-dir {$_GET["cyrus-SaveNewDir"]}";
	sys_THREAD_COMMAND_SET("$cmd");
	shell_exec($cmd);
}
function cyrus_rebuild_all_mailboxes(){
	$f="/usr/share/artica-postfix/ressources/logs/web/". md5($_GET["cyrus-rebuild-all-mailboxes"])."-mailboxes-rebuilded.log";
	@unlink("$f");
	@file_put_contents($f,"");
	@chmod($f,755);
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.cyrus-restore.php --rebuildmailboxes {$_GET["cyrus-rebuild-all-mailboxes"]}");
	
}

function cyrus_imap_status(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --cyrus-imap --nowachdog",$results);
	echo "<articadatascgi>". base64_encode(@implode("\n",$results))."</articadatascgi>";	
}

function cyrus_activedirectory(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.cyrus.php --kinit --reload");
}
function cyrus_activedirectory_events(){
	echo "<articadatascgi>". base64_encode(@file_get_contents("/var/log/artica-postfix/kinit.log"))."</articadatascgi>";	
	
}

function cyrus_imap_change_password(){
	$password=base64_decode($_GET["cyrus-change-password"]);
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/process1 --force verbose password-cyrus");
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --cyrus-checkconfig");
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart imap");
}


function postfix_hash_tables(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.hashtables.php");
}
function postfix_hash_transport_maps(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.hashtables.php --transport --reload");
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.maincf.php --smtp-sender-restrictions");
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.opendkim.php --keyTable");
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.cluebringer.php --internal-domains");
	
}



function postfix_multi_transport_maps(){
	$hostname=$_GET["hostname"];
	
	if($hostname=="master"){
		sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.hashtables.php --transport --reload");
		return;
	}
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix-multi.php --instance-reconfigure \"$hostname\"");
}


function postfix_hash_senderdependent(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.hashtables.php --smtp-passwords");
}
function postfix_hash_recipient_canonical(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.hashtables.php --recipient-canonical");
}
function postfix_hash_smtp_generic_maps(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.hashtables.php --smtp-generic-maps");
}


function postfix_hash_aliases(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.hashtables.php --aliases");
}
function postfix_hash_bcc(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.hashtables.php --bcc");
}

function postfix_relayhost(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd="$nohup ".LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.hashtables.php --relayhost >/dev/null 2>&1 &";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);
}

function postfix_sasl(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd="$nohup ".LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.maincf.php --smtp-sasl >/dev/null 2>&1 &";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);	
}

function postfix_others_values(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.maincf.php --others-values");
}
function postfix_mime_header_checks(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.maincf.php --mime-header-checks");
}

function postfix_interfaces(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.maincf.php --interfaces");
}

function CleanCache(){
	shell_exec("/bin/rm -f /usr/share/artica-postfix/ressources/logs/web/cache/* &");
	shell_exec("/bin/rm -f /usr/share/artica-postfix/ressources/logs/web/*.cache");
	shell_exec("/bin/rm -f /usr/share/artica-postfix/ressources/logs/web/icons-*");
	shell_exec("/bin/rm -f /usr/share/artica-postfix/ressources/logs/web/tooltips-*");
	shell_exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.shm.php --remove &");
}
function zarafa_admin_chock(){
	$unix=new unix();
	$zarafa_admin=$unix->find_program("zarafa-admin");	
	sys_THREAD_COMMAND_SET("$zarafa_admin -l");
}



function zarafa_migrate(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.zarafa-migrate.php {$_GET["zarafa-migrate"]}");
}
function zarafa_restart_web(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart zarafa-web");
}
function zarafa_restart(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart zarafa");
}


function RestartAmavis(){
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --amavis-reload");
}

function zarafa_user_details(){
	$unix=new unix();
	$zarafa_admin=$unix->find_program("zarafa-admin");
	$cmd="$zarafa_admin --details {$_GET["zarafa-user-details"]}";
	exec($cmd,$results);
	while (list ($index, $line) = each ($results) ){
		$line=trim($line);
		if(preg_match("#(.+?):(.+)#",$line,$re)){
			$key=trim($re[1]);
			$value=trim($re[2]);
			if($value=="unlimited"){$value=0;}
			if($value=="yes"){$value=1;}
			if($value=="no"){$value=0;}
			$array[$key]=$value;
			
		}
	}
	
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";	
	
	
}
function fstab_acl(){
	$acl_enabled=$_GET["acl"];
	$dev=base64_decode($_GET["dev"]);
	writelogs_framework("$dev= enable acl=$acl_enabled",__FUNCTION__,__FILE__);
	$unix=new unix();
	$unix->FSTAB_ACL($dev,$acl_enabled);
	}
function fstab_quota(){
	$quota_enabled=$_GET["quota"];
	$dev=base64_decode($_GET["dev"]);
	writelogs_framework("$dev= enable quota=$quota_enabled",__FUNCTION__,__FILE__);
	$unix=new unix();
	$unix->FSTAB_QUOTA($dev,$quota_enabled);
	}	
	
function samba_add_acl_group(){
	$group=base64_decode($_GET["group"]);
	$path=base64_decode($_GET["path"]);
	$unix=new unix();
	$setfacl=$unix->find_program("setfacl");
	if($setfacl==null){
		$results[]="Unable to stat setfacl";
		echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
		return ;	
	}
	$cmd="$setfacl -m group:\"$group\":r \"$path\" 2>&1";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__);
	exec($cmd,$results);
	samba_change_acl_items(1);
	if(is_array($results)){
		echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	
	}
	
}
function samba_add_acl_user(){
	$user=base64_decode($_GET["username"]);
	$path=base64_decode($_GET["path"]);
	$unix=new unix();
	$setfacl=$unix->find_program("setfacl");
	if($setfacl==null){
		$results[]="Unable to stat setfacl";
		echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
		return ;	
	}
	$cmd="$setfacl -m u:\"$user\":r \"$path\" 2>&1";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__);
	exec($cmd,$results);
	samba_change_acl_items(1);
	if(is_array($results)){
		echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	
	}	
}

function samba_delete_acl_group(){
	$group=base64_decode($_GET["group"]);
	$path=base64_decode($_GET["path"]);
	$unix=new unix();
	$setfacl=$unix->find_program("setfacl");
	if($setfacl==null){
		$results[]="Unable to stat setfacl";
		echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
		return ;	
	}
	$cmd="$setfacl -x group:\"$group\" \"$path\" 2>&1";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__);
	exec($cmd,$results);
	samba_change_acl_items(1);
	if(is_array($results)){
		echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	
	}	
	
}
function samba_delete_acl_user(){
	$user=base64_decode($_GET["username"]);
	$path=base64_decode($_GET["path"]);
	$unix=new unix();
	$setfacl=$unix->find_program("setfacl");
	if($setfacl==null){
		$results[]="Unable to stat setfacl";
		echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
		return ;	
	}
	$cmd="$setfacl -x u:\"$user\" \"$path\" 2>&1";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__);
	exec($cmd,$results);
	samba_change_acl_items(1);
	if(is_array($results)){
		echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	
	}	
}

function samba_change_acl_group(){
	$group=base64_decode($_GET["group"]);
	$path=base64_decode($_GET["path"]);
	$unix=new unix();
	$setfacl=$unix->find_program("setfacl");
	if($setfacl==null){
		$results[]="Unable to stat setfacl";
		echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
		return ;	
	}
	if($_GET["chmod"]==null){$_GET["chmod"]='---';}
	$cmd="$setfacl -m group:\"$group\":{$_GET["chmod"]} \"$path\" 2>&1";
	
	if($group=="GROUP"){
		$cmd="$setfacl -m g::{$_GET["chmod"]} \"$path\" 2>&1";
	}
	if($group=="OTHER"){
		$cmd="$setfacl -m o::{$_GET["chmod"]} \"$path\" 2>&1";
	}	
	
	writelogs_framework("$cmd",__FUNCTION__,__FILE__);
	exec($cmd,$results);
	samba_change_acl_items(1);
	if(is_array($results)){
		echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	
	}	
	
}

function samba_change_acl_items($noecho=0){
$path=base64_decode($_GET["path"]);
$unix=new unix();
	$setfacl=$unix->find_program("setfacl");
	$getfacl=$unix->find_program("getfacl");
	
	if($setfacl==null){
		$results[]="Unable to stat setfacl";
		echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
		return ;	
	}
	
	if($_GET["default"]==1){
		$cmd="$getfacl --access \"$path\" | $setfacl -d -M- \"$path\"";
		writelogs_framework("$cmd",__FUNCTION__,__FILE__);
		sys_THREAD_COMMAND_SET($cmd);
		
	}	
	
	if($_GET["recursive"]==1){
		$cmd="$getfacl --access \"$path\" | $setfacl -R -M- \"$path\"";
		writelogs_framework("$cmd",__FUNCTION__,__FILE__);
		sys_THREAD_COMMAND_SET($cmd);
	}

	if($noecho==1){return;}
	if(is_array($results)){
		echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	
	}	

}

function samba_change_acl_user(){
	$username=base64_decode($_GET["username"]);
	$path=base64_decode($_GET["path"]);
	$unix=new unix();
	$setfacl=$unix->find_program("setfacl");
	if($setfacl==null){
		$results[]="Unable to stat setfacl";
		echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
		return ;	
	}
	if($_GET["chmod"]==null){$_GET["chmod"]='---';}
	$cmd="$setfacl -m u:\"$username\":{$_GET["chmod"]} \"$path\" 2>&1";
	
	if($username=="OWNER"){
		$cmd="$setfacl -m u::{$_GET["chmod"]} \"$path\" 2>&1";
	}
	
	writelogs_framework("$cmd",__FUNCTION__,__FILE__);
	exec($cmd,$results);
	samba_change_acl_items(1);
	if(is_array($results)){
		echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	
	}		
}
	
function SAMBA_HAVE_POSIX_ACLS(){
	$unix=new unix();
	$HAVE_POSIX_ACLS="FALSE";
	$smbd=$unix->find_program("smbd");
	$grep=$unix->find_program("grep");
	exec("$smbd -b|$grep -i acl 2>&1",$results);
	while (list ($index, $line) = each ($results) ){
		if(preg_match("#HAVE_POSIX_ACLS#",$line)){
			$HAVE_POSIX_ACLS="TRUE";
			break;
		}
	}
	
	echo "<articadatascgi>". base64_encode($HAVE_POSIX_ACLS)."</articadatascgi>";	
	}

function dansguardian_template(){
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --dansguardian-template");
}
function dansguardian_get_template(){
	echo "<articadatascgi>".@file_get_contents("/usr/share/artica-postfix/bin/install/dansguardian/template.html")."</articadatascgi>";
}

function dansguardian_categories(){
$unix=new unix();
	
	
}	

function find_sock_program(){
	$unix=new unix();
	echo "<articadatascgi>".  base64_encode($unix->find_program($_GET["find-program"]))."</articadatascgi>";	
}

function squidGuardDatabaseStatus(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.squidguard.php --db-status-www",$ri);
	echo "<articadatascgi>".  base64_encode(implode("",$ri))."</articadatascgi>";
}
function squidGuardStatus(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.squidguard.php --status",$ri);
	echo "<articadatascgi>".  base64_encode(implode("\n",$ri))."</articadatascgi>";	
}

function squidGuardCompile(){
	$l="/usr/share/artica-postfix/ressources/logs/squidguard.compile.db.txt";
	@file_put_contents($l,"{waiting}...");
	@chmod($l,0777);
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.squidguard.php --compile >>$l 2>&1");
	
}

function squidguardTests(){
	$uri=base64_decode($_GET["uri"]);
	$client=base64_decode($_GET["client"]);	
	$unix=new unix();
	$squidGuard=$unix->find_program("squidGuard");
	$echo=$unix->find_program("echo");
	$cmd="$echo \"$uri $client/- - GET\" | $squidGuard -c /etc/squid/squidGuard.conf -d 2>&1";
	exec($cmd,$results);
	$results[]=$cmd;
	echo "<articadatascgi>".  base64_encode(serialize($results))."</articadatascgi>";	
	
	
}


function SQUID_CACHE_INFOS(){
	$cache_file="/usr/share/artica-postfix/ressources/logs/web/squid.caches.infos";
	if(is_file($cache_file)){
		$time=file_time_min($cache_file);
		$datas=@file_get_contents($cache_file);
		writelogs_framework("$cache_file time:$time bytes:". strlen($datas),__FUNCTION__,__FILE__,__LINE__);
		if(strlen($datas)>20){
			if($time<10){echo "<articadatascgi>".  base64_encode($datas)."</articadatascgi>";return;}
		}
	}
	
	$unix=new unix();
	$array=$unix->squid_get_cache_infos();
	$serialized=serialize($array);
	@file_put_contents($cache_file,$serialized);
	chmod($cache_file,0777);
	echo "<articadatascgi>".  base64_encode($serialized)."</articadatascgi>";	
}


function cicap_reconfigure(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.c-icap.php --build");
}

function cicap_reload(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart cicap");
}

function cicap_restart(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart cicap");
}


function SQUID_RESTART_NOW(){
	@unlink("/usr/share/artica-postfix/ressources/logs/web/squid.caches.infos");
	shell_exec("/etc/init.d/artica-postfix restart squid-cache");
}
function SQUID_CACHES(){
	@unlink("/usr/share/artica-postfix/ressources/logs/web/squid.caches.infos");
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.squid.php --caches");
}

function SQUID_CACHES_RECONSTRUCT(){
	@unlink("/usr/share/artica-postfix/ressources/logs/web/squid.caches.infos");
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.squid.php --caches-reconstruct");
}


function iwlist(){
	shell_exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.wifi.detect.cards.php --iwlist");
}
function WIFI_CONNECT_AP(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.wifi.detect.cards.php --ap",$r);	
	echo "<articadatascgi>".  base64_encode(implode("\n",$r))."</articadatascgi>";	
}
function start_wifi(){
	shell_exec("/etc/init.d/artica-postfix start wifi");
}

function WIFI_ETH_STATUS(){
	$unix=new unix();
	$eth=$unix->GET_WIRELESS_CARD();
	if($eth==null){
		writelogs_framework("NO eth card found",__FUNCTION__,__FILE__,__LINE__);
		return null;
	}
	$wpa_cli=$unix->find_program("wpa_cli");
	if($wpa_cli==null){
		writelogs_framework("NO wpa_cli found",__FUNCTION__,__FILE__,__LINE__);
		return null;
	}
	exec("$wpa_cli -p/var/run/wpa_supplicant status -i{$eth}",$results);
	writelogs_framework(count($results)." lines",__FUNCTION__,__FILE__,__LINE__);
	$conf="[IF]\neth=$eth\n".implode("\n",$results);
	echo "<articadatascgi>".  base64_encode($conf)."</articadatascgi>";	
}
function WIFI_ETH_CHECK(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.wifi.detect.cards.php --checkap",$r);
	echo "<articadatascgi>".  base64_encode(implode("\n",$r))."</articadatascgi>";	
}
function ChangeHostName(){
	$servername=$_GET["ChangeHostName"];
	shell_exec("/usr/share/artica-postfix/bin/artica-install --change-hostname $servername");
	sleep(2);
	
}

function ClamavUpdate(){
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-update --clamav");
}

function hostname_full(){
	$unix=new unix();
	$ypdomainname=$unix->find_program("ypdomainname");
	$hostname=$unix->find_program("hostname");
	$sysctl=$unix->find_program("sysctl");
	if($ypdomainname<>null){
		exec("$ypdomainname",$results);
		$domain=trim(@implode(" ",$results));
		
		
	}else{
		exec("$sysctl -n kernel.domainname",$results);
		$domain=trim(@implode(" ",$results));
	}
	unset($results);
	exec("$hostname -s",$results);
	$host=trim(@implode(" ",$results));
	unset($results);
	
	if(preg_match("#not set#",$domain)){$domain=null;}
	if(preg_match("#\(none#",$domain)){$domain=null;}
	if($domain==null){
		exec("$hostname -d",$results);
		$domain=trim(@implode(" ",$results));
	}
	
	if(strlen($domain)>0){$host="$host.$domain";}
	$host=str_replace('.(none)',"",$host);
	echo "<articadatascgi>$host</articadatascgi>";	
	
}
function GetUniqueID(){
	$uuid=trim(@file_get_contents("/etc/artica-postfix/settings/Daemons/SYSTEMID"));
	if($uuid==null){
		$unix=new unix();
		$blkid=$unix->find_program("blkid");
		exec($blkid,$results);
		while (list ($index, $line) = each ($results) ){
			if(preg_match("#UUID=\"(.+?)\"#",$line,$re)){
				$uuid=$re[1];
			}
		}
		
		@file_put_contents("/etc/artica-postfix/settings/Daemons/SYSTEMID",$uuid);
	}
	
	echo "<articadatascgi>". base64_encode($uuid)."</articadatascgi>";	
	
}

function shalla_update(){
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-update --filter-plus --force");
}

function dansguardian_search_categories(){
	$www=base64_decode($_GET["searchww-cat"]);
	
	if(preg_match("#www\.(.+?)$#i",$www,$re)){$www=$re[1];}
	writelogs_framework("Search \"$www\" :=>{$_GET["searchww-cat"]}",__FUNCTION__,__FILE__,__LINE__);
	
	$dansguardian_enabled=trim(@file_get_contents("/etc/artica-postfix/settings/Daemons/DansGuardianEnabled"));
	if($dansguardian_enabled==null){$dansguardian_enabled=0;}
	$squidGuardEnabled=trim(@file_get_contents("/etc/artica-postfix/settings/Daemons/squidGuardEnabled"));
	if($squidGuardEnabled==null){$squidGuardEnabled=0;}
	
	if($squidGuardEnabled==1){$path="/var/lib/squidguard";}
	if($dansguardian_enabled==1){$path="/etc/dansguardian/lists";}
	
	if($path==null){return;}
	
	$unix=new unix();
	$www=str_replace(".","\.",$www);
	$www="^$www$";
	$grep=$unix->find_program("grep");
	$cmd="$grep -R -E \"$www\" --mmap -s -l $path";
	exec($cmd,$results);
	writelogs_framework("$cmd -> ".count($results)." lines",__FUNCTION__,__FILE__,__LINE__);
	while (list ($index, $line) = each ($results) ){
		$line=trim(str_replace("$path/","",$line));
		unset($re);
		writelogs_framework("Search \"$www\" :=>\"$line\"",__FUNCTION__,__FILE__,__LINE__);
		if(preg_match("#web-filter-plus\/BL\/(.+?)\/domains$#",$line,$re)){
			
			$array[$re[1]]=true;
			continue;
		}
		
		if(preg_match("#blacklist-artica\/(.+?)\/domains$#",$line,$re)){
			$array[$re[1]]=true;
			continue;
		}
		
		if(preg_match("lists\/blacklists.+*\/(.+?)\/domains$#",$line,$re)){
			$array[$re[1]]=true;
			continue;
		}
		
		if(preg_match("#^blacklists\/#",$line,$re)){continue;}
		
		if(preg_match("#^(.+?)\/domains$#",$line,$re)){
			
			$array[$re[1]]=true;
			continue;
		}

		if(preg_match("#personal-categories#",$line)){continue;}
		
	}
	
	writelogs_framework(serialize($array),__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";	
}
function dansguardian_community_categories(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.web-community-filter.php");	
}
function samba_wbinfo_domain(){
	$WORKGROUP=base64_decode($_GET["wbinfo-domain"]);
	$unix=new unix();
	$wbinfo=$unix->find_program("wbinfo");
	exec("$wbinfo -D $WORKGROUP 2>&1",$results);
	while (list ($index, $line) = each ($results) ){
		if(preg_match("#(.+?):(.+)#",$line,$re)){
			$array[trim($re[1])]=trim($re[2]);
		}
		
	}
	
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
	
}

function openvpn_rebuild_certificates(){
	shell_exec("/bin/rm /etc/artica-postfix/openvpn/keys/*");
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --openvpn-build-certificate && /etc/init.d/artica-postfix restart openvpn");
	
}

function openvpn_server_exec_schedule(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.openvpn.php --schedule");
}
function openvpn_sesssions(){
	$array=explode("\n",@file_get_contents("/var/log/openvpn/openvpn-status.log"));
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
}
function openvpn_client_sesssions(){
	$array=explode("\n",@file_get_contents("/etc/artica-postfix/openvpn/clients/{$_GET["openvpn-client-sesssions"]}/openvpn-status.log"));
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
}
function postfix_get_main_cf(){
	$array=explode("\n",@file_get_contents("/etc/postfix/main.cf"));
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";	
}

function postfix_multi_status(){
	$hostname=$_GET["postfix-multi-status"];
	writelogs_framework("Statusof \"$hostname\"",__FUNCTION__,__FILE__,__LINE__);
	$pidfile="/var/spool/postfix-$hostname/pid/master.pid";
	$unix=new unix();
	$pid=$unix->get_pid_from_file($pidfile);
	
	writelogs_framework("Statusof \"$hostname\" $pidfile=$pid",__FUNCTION__,__FILE__,__LINE__);
	
	if($unix->process_exists($pid)){
		$array["PID"]=$pid;
	}
	
	if(!is_array($array)){return null;}
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";	
	
	
}
function postfix_single_status(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --postfix --nowachdog",$results);
	echo "<articadatascgi>". base64_encode(@implode("\n",$results))."</articadatascgi>";	
	}
	
function ProcessExists(){
	$pid=$_GET["PID"];
	$unix=new unix();
	if($unix->process_exists($pid)){
		echo "<articadatascgi>TRUE</articadatascgi>";
	}
}


function postfix_multi_reconfigure(){
	$hostname=$_GET["postfix-multi-reconfigure"];
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix-multi.php --instance-reconfigure \"$hostname\"");	
}
function postfix_multi_relayhost(){
	$hostname=$_GET["postfix-multi-relayhost"];
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix-multi.php --instance-relayhost \"$hostname\"");	
}
function postfix_multi_ssl(){
	$hostname=$_GET["postfix-multi-sasl"];
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix-multi.php --instance-ssl \"$hostname\"");	
}
function postfix_multi_settings(){
	$hostname=$_GET["postfix-multi-settings"];
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix-multi.php --instance-settings \"$hostname\"");	
}
function postfix_multi_mastercf(){
	$hostname=$_GET["postfix-multi-mastercf"];
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix-multi.php --instance-mastercf \"$hostname\"");	
}
function postfix_multi_aiguilleuse(){
	$hostname=$_GET["postfix-multi-aiguilleuse"];
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix-multi.php --instance-aiguilleuse \"$hostname\"");	
}


function postfix_multi_mime_header_checks(){
	$hostname=$_GET["postfix-multi-mime-header-checks"];
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix-multi.php --mime-header-checks \"$hostname\"");	
}





function postfix_multi_reconfigure_all(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix-multi.php");
}

function SQUID_PROXY_PAC_REBUILD(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart proxy-pac");
}
function SQUID_PROXY_PAC_SHOW(){
	shell_exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.proxy.pac.php --write");
	$datas=@file_get_contents("/usr/share/proxy.pac/proxy.pac");
	writelogs_framework("proxy.pac: ". strlen($datas)." bytes",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>". base64_encode($datas)."</articadatascgi>";	
}

function SQUID_CONF_EXPORT(){
	$datas=@file_get_contents("/etc/squid3/squid.conf");
	echo "<articadatascgi>". base64_encode($datas)."</articadatascgi>";
}




function postfix_multi_perform_reload(){
	$unix=new unix();
	$hostname=$_GET["postfix-multi-perform-reload"];
	$postmulti=$unix->find_program("postmulti");
	shell_exec("$postmulti -i postfix-$hostname -p reload");
	}
function postfix_multi_perform_restart(){
	$unix=new unix();
	$hostname=$_GET["postfix-multi-perform-restart"];
	$postmulti=$unix->find_program("postmulti");
	shell_exec("$postmulti -i postfix-$hostname -p stop");
	shell_exec("$postmulti -i postfix-$hostname -p start");
	}
function postfix_multi_perform_flush(){
	$unix=new unix();
	$hostname=$_GET["postfix-multi-perform-flush"];
	$postmulti=$unix->find_program("postmulti");
	shell_exec("$postmulti -i postfix-$hostname -p flush");
	}
function postfix_multi_perform_reconfigure(){
	$unix=new unix();
	$hostname=$_GET["postfix-multi-perform-reconfigure"];
	shell_exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix-multi.php --instance-reconfigure $hostname");
	}	
	


	

function samba_events_lists(){
	foreach (glob("/var/log/samba/log.*") as $filename) {
		$file=basename($filename);
		$size=@filesize($filename)/1024;
		$array[$file]=$size;
		
	}
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";	
}

function postfix_single_mynetworks(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.maincf.php --networks");
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.maincf.php --headers-check");		
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.opendkim.php --networks");
}

function OPENDKIM_WHITELIST_DOMAINS(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.opendkim.php --whitelist-domains");
}
function MILTERDKIM_WHITELIST_DOMAINS(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.dkim-milter.php --whitelist-domains");
}


function postfix_luser_relay(){
sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.maincf.php --luser-relay --reload");
}

function samba_move_logs(){
	$filename=base64_decode($_GET["samba-move-logs"]);
	if(!is_file("/var/log/samba/$filename")){return null;}
	chdir("/var/log/samba");
	$unix=new unix();
	$zip=$unix->find_program("zip");
	$tar=$unix->find_program("tar");
	
	$target_filename="/usr/share/artica-postfix/ressources/logs/$filename.tar";
	
	$cmd="tar -cf $target_filename $filename";
	
	if($zip<>null){
		$target_filename="/usr/share/artica-postfix/ressources/logs/$filename.zip";
		$cmd="$zip $target_filename $filename";
	}
	if(is_file($target_filename)){@unlink($target_filename);}
	exec($cmd,$results);
	writelogs_framework("$cmd\n".@implode("\n",$results),__FUNCTION__,__FILE__,__LINE__);
	
	if(!file_exists($target_filename)){return null;}
	echo "<articadatascgi>". base64_encode(basename($target_filename))."</articadatascgi>";	
	}
	
function samba_delete_logs(){
	$filename=base64_decode($_GET["samba-delete-logs"]);
	writelogs_framework("try to delete /var/log/samba/$filename",__FUNCTION__,__FILE__,__LINE__);
	
	if(!is_file("/var/log/samba/$filename")){return null;}	
	@unlink("/var/log/samba/$filename");
}

function milter_greylist_reconfigure(){
	if(isset($_GET["hostname"])){$cmdp=" --hostname={$_GET["hostname"]}&ou='{$_GET["ou"]}";}
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.milter-greylist.php$cmdp");
}
function milter_greylist_multi_status(){
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.milter-greylist.php --status --hostname={$_GET["hostname"]} --ou={$_GET["ou"]}";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$results);
	echo "<articadatascgi>". base64_encode(@implode("\n",$results))."</articadatascgi>";	
}

function amavis_get_events(){
	$maillog=$_GET["maillog"];
	$unix=new unix();
	$gep=$unix->find_program("grep");
	$tail=$unix->find_program("tail");
	$cmd="$tail -n 3000 $maillog|$gep amavis 2>&1";
	exec($cmd,$results);
	writelogs_framework("$cmd (". count($results).")" ,__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	
}

function amavis_get_config(){
	echo "<articadatascgi>". base64_encode(@file_get_contents("/usr/local/etc/amavisd.conf"))."</articadatascgi>";
}

function amavis_get_status(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --amavis-full --nowachdog",$results);
	echo "<articadatascgi>". base64_encode(@implode("\n",$results))."</articadatascgi>";	
}

function amavis_watchdog(){
	shell_exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --amavis-watchdog");
}

function amavis_get_template(){
	$tplname=$_GET["amavis-template-load"];
	writelogs_framework("loading \"$tplname\"",__FUNCTION__,__FILE__,__LINE__);
	if(is_file("/usr/local/etc/amavis/$tplname.txt")){
		writelogs_framework("loading /usr/local/etc/amavis/$tplname.txt",__FUNCTION__,__FILE__,__LINE__);
		$datas=@file_get_contents("/usr/local/etc/amavis/$tplname.txt");
	}
		
	if(trim($datas)==null){	
		writelogs_framework("loading /usr/share/artica-postfix/bin/install/amavis/$tplname.txt",__FUNCTION__,__FILE__,__LINE__);
		$datas=@file_get_contents("/usr/share/artica-postfix/bin/install/amavis/$tplname.txt");
	}
	
	echo "<articadatascgi>". base64_encode($datas)."</articadatascgi>";
}

function amavis_template_help(){
	writelogs_framework("loading /usr/share/artica-postfix/bin/install/amavis/README.customize",__FUNCTION__,__FILE__,__LINE__);
	$datas=@file_get_contents("/usr/share/artica-postfix/bin/install/amavis/README.customize");
	echo "<articadatascgi>". base64_encode($datas)."</articadatascgi>";
}

function amavis_save_template(){
	$tplname=$_GET["amavis-template-save"];
	copy("/etc/artica-postfix/settings/Daemons/amavis-template-$tplname","/usr/local/etc/amavis/$tplname.txt");
}


function move_uploaded_file_framework(){
	$src=base64_decode($_GET["src"]);
	$dest_path=base64_decode($_GET["move_uploaded_file"]);
	if(!is_file($src)){echo "<articadatascgi>$src source file, no such file or directory</articadatascgi>";exit;}
	if(!is_dir($dest_path)){echo "<articadatascgi>$dest_path destination path,no such file or directory</articadatascgi>";exit;}
	$filename=basename($src);
	writelogs_framework("/bin/mv $src $dest_path/$filename" ,__FUNCTION__,__FILE__,__LINE__);
	
	shell_exec("/bin/mv $src $dest_path/$filename");
}
function sslfingerprint(){
	$ip=$_GET["ip"];
	$port=$_GET["port"];
	$unix=new unix();
	$openssl=$unix->find_program("openssl");
	$cmd="$openssl s_client -connect  $ip:$port -showcerts | $openssl x509 -fingerprint -noout -md5 2>&1";
	writelogs_framework("$cmd" ,__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$results);
	while (list ($index, $line) = each ($results) ){
		writelogs_framework("$line" ,__FUNCTION__,__FILE__,__LINE__);
		if(preg_match("#MD5 Fingerprint=(.+)#",$line,$re)){
			echo "<articadatascgi>". base64_encode(trim($re[1]))."</articadatascgi>";
			return ;
		}
	}
}
function emailing_import_contacts(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.emailing-import.php");
}
function emailing_database_migrate_export(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.emailing-import.php --import-id {$_GET["emailing-database-migrate-perform"]}");
}

function emailing_database_make_unique(){
	$id=$_GET["ID"];
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.emailing-import.php --make-unique $id";
	sys_THREAD_COMMAND_SET($cmd);
	writelogs_framework($cmd ,__FUNCTION__,__FILE__,__LINE__);
	}

function dkim_check_presence_key(){
	$file="/etc/amavis/dkim/{$_GET["dkim-check-presence-key"]}.key";
	if(is_file($file)){echo "<articadatascgi>". base64_encode("TRUE")."</articadatascgi>";exit;}
	writelogs_framework("UNABLE TO STAT $file" ,__FUNCTION__,__FILE__,__LINE__);
}
function dkim_amavis_build_key(){
	@mkdir("/etc/amavis/dkim",0666,true);
	$key="/etc/amavis/dkim/{$_GET["dkim-amavis-build-key"]}.key";
	if(is_file($key)){@unlink($key);}
	@chown("chown root /usr/share/artica-postfix/bin/install/amavis/check-external-users.conf","root");
	$cmd="/usr/local/sbin/amavisd -c /usr/local/etc/amavisd.conf genrsa $key";
	writelogs_framework("$cmd" ,__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$results);
	echo "<articadatascgi>". base64_encode(@implode("\n",$results))."</articadatascgi>";
	}
	
function dkim_amavis_show_keys(){
	
	$cmd="/usr/local/sbin/amavisd -c /usr/local/etc/amavisd.conf showkeys";
	writelogs_framework("$cmd" ,__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$results);
	while (list ($index, $line) = each ($results) ){
		if(preg_match("#;\s+key.+?,\s+domain\s+(.+?),\s+\/etc\/amavis#",$line,$re)){$domain=$re[1];continue;}
		$ri[$domain][]=$line;
		}
		
	echo "<articadatascgi>". base64_encode(serialize($ri))."</articadatascgi>";

}


function dkim_amavis_tests_keys(){
	$cmd="/usr/local/sbin/amavisd -c /usr/local/etc/amavisd.conf testkeys 2>&1";
	writelogs_framework("$cmd" ,__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$results);

	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	
}

function hdparm_infos(){
$unix=new unix();
$hdparm=$unix->find_program("hdparm");
exec("$hdparm -I {$_GET["hdparm-infos"]} 2>&1",$results);
echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
}

function spamassassin_check(){
	$unix=new unix();
	$bin=$unix->find_program("spamassassin");
	exec("$bin --lint -D 2>&1",$results);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	
}

function spamassassin_trust_networks(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.spamassassin.php --trusted");	
}

function spamassassin_rebuild(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.spamassassin.php --build");	
}



function emailing_builder_linker(){
	$ou=$_GET["emailing-builder-linker"];
	writelogs_framework("exec.emailing.php --build-queues $ou " ,__FUNCTION__,__FILE__,__LINE__);
	shell_exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.emailing.php --build-queues $ou &");
}

function emailing_builder_linker_simple(){
	$ou=base64_decode($_GET["ou"]);
	writelogs_framework("exec.emailing.php --build-single-queue {$_GET["ID"]} $ou" ,__FUNCTION__,__FILE__,__LINE__);
	shell_exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.emailing.php --build-single-queue {$_GET["ID"]} $ou &");
	
}


function emailing_build_emailrelays(){
	writelogs_framework("exec.emailrelay.php --emailrelays-emailing" ,__FUNCTION__,__FILE__,__LINE__);
	shell_exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.emailrelay.php --emailrelays-emailing &");
}

function system_debian_kernel(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.apt-cache.kernel.php --detect");
}

function system_debian_kernel_upgrade(){
	$pkg=$_GET["system-debian-upgrade-kernel"];
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.apt-cache.kernel.php --install $pkg");
}

function reports_build_quarantine_cron(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.quarantine.reports.php --build-cron-users");
	
}
function reports_build_quarantine_send(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.quarantine.reports.php ----user {$_GET["pdf-quarantine-send"]}");
	
}



function emailing_emailrelays_status_ou(){
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.emailrelay.php --emailing-ou-status {$_GET["ou"]}";
	writelogs_framework("$cmd" ,__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$results);
	$datas=@implode("\n",$results);
	echo "<articadatascgi>". base64_encode($datas)."</articadatascgi>";	
	}
	
function emailing_emailrelays_remove(){
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.emailrelay.php --emailing-remove {$_GET["emailing-remove-emailrelays"]}";
	writelogs_framework("$cmd" ,__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$results);
	}
function cyrus_empty_mailbox(){
	$unix=new unix();
	$ipurge=$unix->LOCATE_CYRUS_IPURGE();
	if($ipurge==null){echo "<articadatascgi>". base64_encode("Could not locate ipurge")."</articadatascgi>";return;}
	$user=$_GET["uid"];
	if($user==null){echo "<articadatascgi>". base64_encode("No user set")."</articadatascgi>";return;}
	
	if(trim($_GET["size_of_message"])<>null){$params[]="-m{$_GET["size_of_message"]}";}
	if(trim($_GET["age_of_message"])<>null){$params[]="-d{$_GET["age_of_message"]}";}	
	if($_GET["submailbox"]<>null){$submailbox="/{$_GET["submailbox"]}";}
	$params[]="user/$user$submailbox";
	$cmd="su cyrus -c \"$ipurge -f ".@implode(" ",$params)." 2>&1\"";
	writelogs_framework("$cmd" ,__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$results);
	
	if($_GET["by"]==-100){$_GET["by"]="Super Administrator";}
	
	$finale=trim(implode("",$results));
	if($finale==null){$results[]="Executed...";}
	$unix->send_email_events("Messages task deletion on mailbox $user$submailbox by {{$_GET["by"]} executed",@implode("\n",$results),"mailbox");
	echo "<articadatascgi>". base64_encode(@implode("\n",$results))."</articadatascgi>";
	
}

function smtp_hack_reconfigure(){
	shell_exec("/bin/touch /var/log/artica-postfix/smtp-hack-reconfigure");
}



function pureftpd_status(){
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --pure-ftpd --nowachdog";
	writelogs_framework("$cmd" ,__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$results);
	$datas=@implode("\n",$results);
	echo "<articadatascgi>". base64_encode($datas)."</articadatascgi>";	
}
function directory_chown(){
	$path=shellEscapeChars(utf8_encode(base64_decode($_GET["chown"])));
	$uid=base64_decode($_GET["uid"]);
	$unix=new unix();
	$cmd=$unix->find_program("chown")." $uid $path 2>&1";
		
	exec($cmd,$results);
	writelogs_framework("\n$cmd\n". @implode("\n",$results) ,__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>". base64_encode(@implode("\n",$results))."</articadatascgi>";
}

function smbclientL(){
	$ip=$_GET["smbclientL"];
	$user=base64_decode($_GET["user"]);
	$password=base64_decode($_GET["password"]);
	$unix=new unix();
	$smbclient=$unix->find_program("smbclient");
	if($smbclient==null){
		writelogs_framework("UNable to find smbclient" ,__FUNCTION__,__FILE__,__LINE__);
		return;
	}	
	$f[]=$smbclient;$f[]="-L";$f[]=$ip;	
	if($password==null){$f[]="-N";}else{$f[]="-U $user%$password";}
	$cmd=@implode(" ",$f);
	exec($cmd,$results);
	writelogs_framework("\n$cmd\n". @implode("\n",$results) ,__FUNCTION__,__FILE__,__LINE__);
	while (list ($index, $line) = each ($results) ){
		if(preg_match("#session setup failed: (.+)#",$line,$re)){
			echo "<articadatascgi>". base64_encode($re[1])."</articadatascgi>";
			return;
		}
		if(preg_match("#(.+?)\s+(Printer|IPC|Disk)(.*)#",$line,$re)){
			$array[trim($re[1])]=array("TYPE"=>trim($re[2]),"INFOS"=>trim($re[3]));
		}else{
			writelogs_framework("$line NO MATCH",__FUNCTION__,__FILE__,__LINE__);	
		}
	}
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
	
}


function backuppc_load_computer_config(){
	
	$unix=new unix();
	$path=$unix->BACKUPPC_locate_config_path();
	if(!is_dir($path)){return;}
	$file="$path/{$_GET["backuppc-comp"]}.pl";
	if(!is_file($file)){return null;}
	writelogs_framework("Open $file",__FUNCTION__,__FILE__,__LINE__);	
	echo "<articadatascgi>". base64_encode(@file_get_contents($file))."</articadatascgi>";
}

function backuppc_save_computer_config(){
	$unix=new unix();
	$path=$unix->BACKUPPC_locate_config_path();
	if(!is_dir($path)){return;}	
	$file="$path/{$_GET["backuppc-save-computer"]}.pl";
	$file2="/usr/share/artica-postfix/ressources/logs/{$_GET["backuppc-save-computer"]}.pl";
	@copy($file2,$file);
	@chown($file,"backuppc");
	shell_exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.backup-pc.php --affect");
	shell_exec("/etc/init.d/backuppc reload");
	}
	
function backuppc_affect(){
	$unix=new unix();
	$path=$unix->BACKUPPC_locate_config_path();
	if(!is_dir($path)){return;}	
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.backup-pc.php --affect");
}

function backuppc_restart(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart backuppc");
}

function backuppc_computer_infos(){
	$unix=new unix();
	$uid=$_GET["backuppc-computer-infos"];
	$TopDir=$unix->BACKUPPC_GET_CONFIG_INFOS("TopDir");
	
	$datas=@file_get_contents("$TopDir/log/status.pl");
	writelogs_framework("$uid: Open TopDir $TopDir/log/status.pl ". strlen($datas)." bytes lenght",__FUNCTION__,__FILE__,__LINE__);
	$pattern='#"'.$uid.'".*?=>.*?\{(.+?)\}#is';
	$array=array();
	if(preg_match($pattern,$datas,$re)){
		writelogs_framework("$uid: found $pattern",__FUNCTION__,__FILE__,__LINE__);
		$f=@explode("\n",$re[1]);
		while (list ($num, $ligne) = each ($f) ){
			if(preg_match('#"(.+?)".*=>(.*)#',$ligne,$re)){
				$re[2]=str_replace(",","",trim($re[2]));
				$re[2]=str_replace("'","",$re[2]);
				$re[2]=str_replace('"',"",$re[2]);
				$array[$re[1]]=$re[2];
			}else{
				writelogs_framework("$uid: Not found $ligne",__FUNCTION__,__FILE__,__LINE__);
			}
		}
		
		
		
	}else{
		writelogs_framework("$uid: Not found $pattern",__FUNCTION__,__FILE__,__LINE__);
	}
	
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
	
}

function kav4fs_infos(){
	
	exec("/opt/kaspersky/kav4fs/bin/kav4fs-control --app-info 2>&1",$results);
	while (list ($num, $ligne) = each ($results) ){
		if(preg_match("#(.+?)[:=]+(.+)#",$ligne,$re)){
			$array[trim($re[1])]=trim($re[2]);
		}else{
			writelogs_framework("$ligne No match",__FUNCTION__,__FILE__,__LINE__);
		}
		
	}
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
}

function kav4fs_status(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --kav4fs --nowachdog",$results);
	echo "<articadatascgi>". base64_encode(@implode("\n",$results))."</articadatascgi>";
}

function pptpd_status(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --pptpd --nowachdog",$results);
	echo "<articadatascgi>". base64_encode(@implode("\n",$results))."</articadatascgi>";	
}
function apache_src_status(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --freewebs --nowachdog",$results);
	echo "<articadatascgi>". base64_encode(@implode("\n",$results))."</articadatascgi>";		
}
function snort_status(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --snort --nowachdog",$results);
	echo "<articadatascgi>". base64_encode(@implode("\n",$results))."</articadatascgi>";		
}
function postfwd2_status(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfwd2.php --single-status {$_GET["postfwd2-status"]} --nowachdog",$results);
	echo "<articadatascgi>". base64_encode(@implode("\n",$results))."</articadatascgi>";		
}

function postfwd2_reload(){
	$unix=new unix();
	$unix->THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfwd2.php --reload-instance {$_GET["postfwd2-reload"]}");
}
function postfwd2_restart(){
	$unix=new unix();
	if($_GET["postfwd2-restart"]=="master"){
		$unix->THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.maincf.php --smtp-sender-restrictions");
	}else{
		$unix->THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix-multi.php --instance-reconfigure {$_GET["postfwd2-restart"]}");
	}
	$unix->THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfwd2.php --reload-instance {$_GET["postfwd2-restart"]}");
	
}



function iscsi_status(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --iscsi --nowachdog",$results);
	echo "<articadatascgi>". base64_encode(@implode("\n",$results))."</articadatascgi>";		
}

function AUTOFS_STATUS(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --autofs --nowachdog",$results);
	echo "<articadatascgi>". base64_encode(@implode("\n",$results))."</articadatascgi>";		
}

function GREYHOLE_STATUS(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --greyhole --nowachdog",$results);
	echo "<articadatascgi>". base64_encode(@implode("\n",$results))."</articadatascgi>";	
}

function sabnzbdplus_src_status(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --sabnzbdplus --nowachdog",$results);
	echo "<articadatascgi>". base64_encode(@implode("\n",$results))."</articadatascgi>";	
}

function openvpn_server_status(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --openvpn --nowachdog",$results);
	echo "<articadatascgi>". base64_encode(@implode("\n",$results))."</articadatascgi>";		
}
function openvpn_clients_status(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --openvpn-clients --nowachdog",$results);
	echo "<articadatascgi>". base64_encode(@implode("\n",$results))."</articadatascgi>";	
}

function dhcp_status(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --dhcpd --nowachdog",$results);
	echo "<articadatascgi>". base64_encode(@implode("\n",$results))."</articadatascgi>";		
}


function pptpd_clients_status(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --pptpd-clients --nowachdog",$results);
	echo "<articadatascgi>". base64_encode(@implode("\n",$results))."</articadatascgi>";		
}
function pptpd_chap(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.pptpd.php --chap");
		
}

function pptpd_restart(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart pptpd");
}

function AUDITD_STATUS(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --auditd --nowachdog",$results);
	echo "<articadatascgi>". base64_encode(@implode("\n",$results))."</articadatascgi>";	
}

function CROSSROADS_STATUS(){	
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --crossroads --nowachdog",$results);
	echo "<articadatascgi>". base64_encode(@implode("\n",$results))."</articadatascgi>";	
}

function APT_MIRROR_STATUS(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --apt-mirror --nowachdog",$results);
	echo "<articadatascgi>". base64_encode(@implode("\n",$results))."</articadatascgi>";	
}

function APT_MIRROR_SCHEDULE(){
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.apt-mirror.php --schedules";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);
}

function CROSSROADS_EVENTS(){
	$unix=new unix();
	$master_pid=trim($unix->PIDOF($unix->find_program("xr")));
	shell_exec($unix->find_program("kill -1 ")." $master_pid");
	exec($unix->find_program("tail")." -n 500 /var/log/crossroads.log 2>&1",$results);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
}

function kav4fs_install_key(){
	$license_file=base64_decode($_GET["kaf4fs-install-key"]);
	exec("/opt/kaspersky/kav4fs/bin/kav4fs-control --validate-key $license_file 2>&1",$results);
	while (list ($num, $ligne) = each ($results) ){
		if(preg_match("#error#i",$ligne,$re)){
			echo "<articadatascgi>". base64_encode(@implode("\n",$results))."</articadatascgi>";
			return;
		}
	}
	
}

function kav4fsPatternDate(){
	$u=new kav4fsUnix();
	$pattern_path=$u->MAIN["UpdateFolder"]."/".$u->MAIN["AVBasesFolderName"];
	writelogs_framework("UpdateFolder=$pattern_path",__FUNCTION__,__FILE__,__LINE__);
	if(!is_file($pattern_path."/index.html")){
		return null;
	}
}

function kav4fs_tasks(){
	$u=new kav4fsUnix();
	$u->GetTaskList();
	echo "<articadatascgi>". base64_encode(serialize($u->TASKS))."</articadatascgi>";
}

function IP_CALC_CDIR(){
	$pattern=base64_decode($_GET["cdir-calc"]);
	exec("/usr/share/artica-postfix/bin/ipcalc \"$pattern\" 2>&1",$results);	
	while (list ($num, $ligne) = each ($results) ){
		if(preg_match("#Network:\s+\s+([0-9\.\/]+)\s+[0-9]+#",$ligne,$re)){
			echo "<articadatascgi>". base64_encode(trim($re[1]))."</articadatascgi>";
			return;
		}
	}
	
}

function kav4ProxyPatternDate(){
	$unix=new unix();
	$base=$unix->KAV4PROXY_GET_CONF_PATH("path","BasesPath");
	if(!is_file("$base/master.xml")){return;}
	$f=explode("\n",@file_get_contents("$base/master.xml"));
	$reg='#UpdateDate="([0-9]+)\s+([0-9]+)"#';
	while (list ($num, $ligne) = each ($results) ){
		if(preg_match($reg,$ligne,$re)){
			echo "<articadatascgi>". base64_encode(trim($re[1]).";".trim($re[2]))."</articadatascgi>";
			return;
		}
	}	
	
}

function OCSWEB_RESTART(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart ocsweb");
}
function OCSWEB_STATUS(){
exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --ocsweb --nowachdog",$results);
	echo "<articadatascgi>". base64_encode(@implode("\n",$results))."</articadatascgi>";	
}
function OCSAGENT_STATUS(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --ocsagent --nowachdog",$results);
	echo "<articadatascgi>". base64_encode(@implode("\n",$results))."</articadatascgi>";	
}
function OCSAGENT_RESTART(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart ocsagent");
}


function OCSWEB_CERTIFICATE(){
	$send=false;
	$UseSelfSignedCertificate=@file_get_contents("/etc/artica-postfix/settings/Daemons/UseSelfSignedCertificate");
	if($UseSelfSignedCertificate==null){$UseSelfSignedCertificate=1;}
	
	if($UseSelfSignedCertificate==0){
		shell_exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.ocsweb.php --certificate");
	}
	if($UseSelfSignedCertificate==1){
		exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.ocsweb.php --certificate-self 2>&1",$results);
		while (list ($num, $ligne) = each ($results) ){
			if(preg_match("#error#",$ligne)){$send=true;$err[]=$ligne;}
			writelogs_framework("OCS-PACKAGES:: --certificate-self: $ligne",__FUNCTION__,__FILE__,__LINE__);
		}
		
		if($send){
			echo "<articadatascgi>". base64_encode(@implode("\n",$err))."</articadatascgi>";	
		}
		
		sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart ocsweb");
	}
}

function OCSWEB_CERTIFICATE_CSR(){
	if(!is_file("/etc/ocs/cert/server.csr")){return null;}
	echo "<articadatascgi>". base64_encode(file_get_contents("/etc/ocs/cert/server.csr"))."</articadatascgi>";
}
function OCSWEB_FINAL_CERTIFICATE(){
	$path=base64_decode($_GET["path"]);
	if(!is_file($path)){return null;}
	shell_exec("/bin/cp $path /etc/artica-postfix/settings/Daemons/OCSServerDotCrt");
	shell_exec("/bin/cp $path /etc/ocs/cert/server.crt");
	shell_exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.ocsweb.php --final-cert");
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart ocsweb");
}

function OCSWEB_PACKAGE_INFOS(){
	$FILEID=$_GET["ocs-package-infos"];
	$filepath="/var/lib/ocsinventory-reports/download/$FILEID/info";
	$content=@file_get_contents($filepath);
	if(preg_match('#<DOWNLOAD\s+ID="(.*?)"\s+PRI="(.*?)"\s+ACT="(.*?)"\s+DIGEST="(.*?)"\s+PROTO="(.*?)"\s+FRAGS="(.*?)"\s+DIGEST_ALGO="(.*?)"\s+DIGEST_ENCODE="(.*?)"\s+PATH="(.*?)"\s+NAME="(.*?)"\s+COMMAND="(.*?)"\s+NOTIFY_USER="(.*?)"\s+NOTIFY_TEXT="(.*?)"\s+NOTIFY_COUNTDOWN="(.*?)"\s+NOTIFY_CAN_ABORT="(.*?)"\s+NOTIFY_CAN_DELAY="(.*?)"\s+NEED_DONE_ACTION="(.*?)"\s+NEED_DONE_ACTION_TEXT="(.*?)"#'
	,$content,$re)){
		$array["PRI"]=$re[2];
		$array["ACT"]=$re[3];
		$array["DIGEST"]=$re[4];
		$array["PROTO"]=$re[5];
		$array["FRAGS"]=$re[6];
		$array["DIGEST_ALGO"]=$re[7];
		$array["DIGEST_ENCODE"]=$re[8];
		$array["PATH"]=$re[9];
		$array["NAME"]=$re[10];
		$array["COMMAND"]=$re[11];
		$array["NOTIFY_USER"]=$re[12];
		$array["NOTIFY_TEXT"]=$re[13];
		$array["NOTIFY_COUNTDOWN"]=$re[14];
		$array["NOTIFY_CAN_ABORT"]=$re[15];
		$array["NOTIFY_CAN_DELAY"]=$re[16];
		$array["NEED_DONE_ACTION"]=$re[17];
		$array["NEED_DONE_ACTION_TEXT"]=$re[18];
		echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
	}
}

function FILE_MD5(){
	echo "<articadatascgi>".md5_file(base64_decode($_GET["filemd5"]))."</articadatascgi>";
	
}

function OCSWEB_PACKAGE_COPY(){
	$sourcefile=base64_decode($_GET["filesource"]);
	$FILEID=$_GET["FILEID"];
	$document_root="/var/lib/ocsinventory-reports";
	@mkdir("$document_root/download/$FILEID",0666,true);
	shell_exec("/bin/cp $sourcefile $document_root/download/$FILEID/$FILEID");
}

function OCSWEB_PACKAGE_CREATE_INFO(){
	$unix=new unix();
	$userwww=$unix->APACHE_GROUPWARE_ACCOUNT();
	$FILEID=$_GET["FILEID"];
	$sourcefile="/usr/share/artica-postfix/ressources/logs/$FILEID.info";
	writelogs_framework("OCS-PACKAGES:: SOURCE=\"$sourcefile\"",__FUNCTION__,__FILE__,__LINE__);
	$document_root="/var/lib/ocsinventory-reports";
	@mkdir("$document_root/download/$FILEID",0755,true);
	@chown("$document_root/download/$FILEID",$userwww);
	$finale_file="$document_root/download/$FILEID/info";
	writelogs_framework("OCS-PACKAGES::$sourcefile file:$finale_file",__FUNCTION__,__FILE__,__LINE__);
	shell_exec("/bin/cp $sourcefile $finale_file");
	@chown("$finale_file",$userwww);
	@unlink($sourcefile);
	writelogs_framework("OCS-PACKAGES:: chown:$userwww",__FUNCTION__,__FILE__,__LINE__);
	exec("/bin/chown -R $userwww $document_root/download/$FILEID",$results);
	while (list ($num, $ligne) = each ($results) ){
		writelogs_framework("OCS-PACKAGES:: chown: $ligne",__FUNCTION__,__FILE__,__LINE__);
	}
	
}


FUNCTION OCSWEB_PACKAGE_FRAGS(){
		$sourcefile=base64_decode($_GET["filesource"]);
		$unix=new unix();
		$userwww=$unix->APACHE_GROUPWARE_ACCOUNT();
		if(trim($sourcefile)==null){
			writelogs_framework("OCS-PACKAGES:: base64_decode({$_GET["filesource"]})=\"NULL\" aborting",__FUNCTION__,__FILE__,__LINE__);
			return;
		}		
		
		$FILEID=$_GET["FILEID"];
		if(trim($FILEID)==null){
			writelogs_framework("OCS-PACKAGES:: FILEID=\"NULL\" aborting",__FUNCTION__,__FILE__,__LINE__);
			return;
		}
		$nbfrags=$_GET["nbfrags"];
		
		if(trim($nbfrags)==null){
			writelogs_framework("OCS-PACKAGES:: nbfrags=\"NULL\" aborting",__FUNCTION__,__FILE__,__LINE__);
			return;
		}		

		$TMP=$unix->FILE_TEMP();
		shell_exec("/bin/cp $sourcefile $TMP");
		$document_root="/var/lib/ocsinventory-reports";
		writelogs_framework("OCS-PACKAGES:: nbfrags=\"$nbfrags\"",__FUNCTION__,__FILE__,__LINE__);
		writelogs_framework("OCS-PACKAGES:: SOURCE=\"$sourcefile\"",__FUNCTION__,__FILE__,__LINE__);
		writelogs_framework("OCS-PACKAGES:: DEST=\"$TMP\"",__FUNCTION__,__FILE__,__LINE__);
		@mkdir("$document_root/download/$FILEID",0755,true);
		@chmod("$document_root/download/$FILEID",0755);
		@chown("$document_root/download/$FILEID",$userwww);
		
		$fname = $TMP;
		if( $size = @filesize( $fname )) {
			writelogs_framework("OCS-PACKAGES:: SIZE=\"$size\"",__FUNCTION__,__FILE__,__LINE__);
			$handle = fopen ( $fname, "rb");
			
			$read = 0;
			for( $i=1; $i<$nbfrags; $i++ ) {
				$contents = fread ($handle, $size / $nbfrags );
				$read += strlen( $contents );
				writelogs_framework("OCS-PACKAGES:: OPEN=\"$document_root/download/$FILEID/$FILEID-$i\"",__FUNCTION__,__FILE__,__LINE__);
				$handfrag = fopen( "$document_root/download/$FILEID/$FILEID-$i", "w+b" );
				fwrite( $handfrag, $contents );
				fclose( $handfrag );
				@chown("$document_root/download/$FILEID/$FILEID-$i",$userwww);
			}	
			
			$contents = fread ($handle, $size - $read);
			$read += strlen( $contents );
			$handfrag = fopen( "$document_root/download/$FILEID/$FILEID-$i", "w+b" );
			fwrite( $handfrag, $contents );
			fclose( $handfrag );
			fclose ($handle);
			@chown("$document_root/download/$FILEID/$FILEID-$i",$userwww);
			unlink($TMP);
		}
		
	exec("/bin/chown -R $userwww $document_root/download/$FILEID",$results);
	while (list ($num, $ligne) = each ($results) ){
		writelogs_framework("OCS-PACKAGES:: chown: $ligne",__FUNCTION__,__FILE__,__LINE__);
	}		
		
}

function ApacheDirDelete(){
	$hostname=$_GET["ApacheDirDelete"];
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.freeweb.php --remove-host \"$hostname\"";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);
	}


function OCSWEB_PACKAGE_DELETE(){
	writelogs_framework("OCS-PACKAGES:: ID={$_GET["FILEID"]}",__FUNCTION__,__FILE__,__LINE__);
	$FILEID=$_GET["FILEID"];
	if(trim($FILEID)==null){return;}
	if(strpos($FILEID,"/")>0){return;}
	$FILEID=str_replace("..","",$FILEID);
	$document_root="/var/lib/ocsinventory-reports";
	writelogs_framework("OCS-PACKAGES:: /bin/rm -rf $document_root/$FILEID",__FUNCTION__,__FILE__,__LINE__);
	shell_exec("/bin/rm -rf $document_root/$FILEID");
}
function OCSWEB_GET_AGENT_PACKAGE_FILENAME(){
	$document_root="/var/lib/ocsinventory-reports";
	foreach (glob("$document_root/OCSNG_WINDOWS_AGENT-*") as $filename) {
		$file=basename($filename);
		$size=@filesize($filename)/1024;
		$array[$file]=$size;
		
	}
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
}
function SQUID_RESTART_ALL(){
	@unlink("/usr/share/artica-postfix/ressources/logs/web/restart.squid");
	shell_exec("/bin/touch /usr/share/artica-postfix/ressources/logs/web/restart.squid");
	shell_exec("/bin/chmod 777 /usr/share/artica-postfix/ressources/logs/web/restart.squid");
	shell_exec("/etc/init.d/artica-postfix restart squid >> /usr/share/artica-postfix/ressources/logs/web/restart.squid 2>&1 &");
	
	
}

function LOAD_LANGUAGE_FILE(){
	writelogs_framework("Loading language pack {$_GET["GetLangagueFile"]}",__FUNCTION__,__FILE__,__LINE__);
	$path="/usr/share/artica-postfix/ressources/language/{$_GET["GetLangagueFile"]}.db";
	if(!is_file($path)){
		writelogs_framework("$path no such file",__FUNCTION__,__FILE__,__LINE__);
		return;
	}
	$file=base64_encode(@file_get_contents("/usr/share/artica-postfix/ressources/language/{$_GET["GetLangagueFile"]}.db"));
	echo "<articadatascgi>$file</articadatascgi>";
}

FUNCTION InventoryAgentsWindowsVersions(){
	foreach (glob("/opt/artica/install/sources/fusioninventory/fusioninventory-agent_windows*.exe") as $filename) {
		$file=basename($filename);
		if(preg_match('#fusioninventory-agent_windows-i386-([0-9+\-\.]+)\.exe#i',$file,$r)){
          			if(strpos($r[1],'.')>0){
          				$key=$r[1];
          				$key=str_replace('.','',$key);
          				$key=str_replace('-','',$key);
          				$arr[$key]=$r[1];}
					}
          		
          		if(is_array($arr)){
          			ksort($arr);
          			while (list ($num, $val) = each ($arr) ){$v[]=$val;}
          		}
          		
          		echo "<articadatascgi>{$v[count($v)-1]}</articadatascgi>";
	}
}

function OCSAGENT_UPDATE_FUSION_INVENTORY(){
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-make APP_OCSI_FUSIONCLIENT");
}

function WINEXE_VERSION(){
	$unix=new unix();
	$winexe=$unix->find_program("winexe");
	if($winexe==null){return;}
	exec("$winexe -V",$results);
	while (list ($num, $ligne) = each ($results) ){
		if(preg_match("#Version\s+(.+)#",$ligne)){
			echo "<articadatascgi>$ligne</articadatascgi>";
			return;
		}
	}
}

function hostToMac(){
	$unix=new unix();
	$newip=$unix->HostToIp($_GET["hostToMac"]);
	if($newip==null){$newip=$_GET["ip"];}
	if($newip==null){return;}
	echo "<articadatascgi>" .$unix->IpToMac($newip)."</articadatascgi>";
}
function hostToIp(){
	$unix=new unix();
	echo "<articadatascgi>" .$unix->HostToIp($_GET["hostToIp"])."</articadatascgi>";
}
function OCSWEB_MOVE_INVENTORY_WIN_PACKAGE(){
	$ver=$_GET["moveOcsAgentPackage"];
	@mkdir("/usr/share/artica-postfix/computers/ressources/logs/web",0777,true);
	shell_exec("/bin/cp /opt/artica/install/sources/fusioninventory/fusioninventory-agent_windows-i386-$ver.exe /usr/share/artica-postfix/computers/ressources/logs/web/fusioninventory-agent_windows-i386-$ver.exe");
	@chmod("/usr/share/artica-postfix/computers/ressources/logs/web/fusioninventory-agent_windows-i386-$ver.exe",0664);
}

function SYSLOG_QUERY(){
	$pattern=trim(base64_decode($_GET["syslog-query"]));
	$pattern=str_replace("  "," ",$pattern);
	$pattern=str_replace(" ","\s+",$pattern);
	$pattern=str_replace(".","\.",$pattern);
	$pattern=str_replace("*",".+?",$pattern);
	$pattern=str_replace("/","\/",$pattern);
	$syslogpath=$_GET["syslog-path"];
	
	if($syslogpath==null){
		exec("/usr/share/artica-postfix/bin/artica-install --whereis-syslog",$results);
		while (list ($num, $ligne) = each ($results) ){
			if(preg_match('#SYSLOG:"(.+?)"#',$ligne,$re)){
				$syslogpath=$re[1];
				break;
				writelogs_framework("artica-install --whereis-syslog $syslogpath" ,__FUNCTION__,__FILE__,__LINE__);
			}else{
				writelogs_framework("$ligne no match" ,__FUNCTION__,__FILE__,__LINE__);
			}
		}
		
		
	}
		$unix=new unix();
	
	$tail = $unix->find_program("tail");
	if($tail==null){return;}
	if(isset($_GET["prefix"])){
		$_GET["prefix"]=str_replace("*",".*?",$_GET["prefix"]);
		$pattern="{$_GET["prefix"]}\[[0-9]+\].*?$pattern";
	}
	
	writelogs_framework("Pattern \"$pattern\"" ,__FUNCTION__,__FILE__,__LINE__);
	$maxrows=500;
	if(strlen($pattern)>1){
		$maxrows=2000;
		$grep="|".$unix->find_program("grep")." -E '$pattern'";
	}
	
	unset($results);
	$l=$unix->FILE_TEMP();
	$cmd="$tail -n $maxrows $syslogpath$grep 2>&1";
	exec($cmd,$results);
	krsort($results);
	writelogs_framework($cmd." ". count($results)." rows " ,__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>" .base64_encode(serialize($results))."</articadatascgi>";
}

function SSHD_GET_CONF(){
	$unix=new unix();
	$config=$unix->LOCATE_SSHD_CONFIG_PATH();
	writelogs_framework("config=$config" ,__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>" .base64_encode(@file_get_contents($config))."</articadatascgi>";
}
function SSHD_RESTART(){
	$newfile="/etc/artica-postfix/settings/Daemons/OpenSSHDConfig";
	if(is_file($newfile)){
		$unix=new unix();
		$orginial=$unix->LOCATE_SSHD_CONFIG_PATH();
		if(is_file($orginial)){
			shell_exec("/bin/cp $newfile $orginial");
		}
	}
sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart openssh");
}

function GLUSTER_REMOUNT(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.gluster.php --remount");
}
function GLUSTER_UPDATE_CLIENTS(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.gluster.php --update-all-clients");
}
function GLUSTER_RESTART(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart gluster");
}
function GLUSTER_DELETE_CLIENTS(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.gluster.php --delete-clients");
}
function GLUSTER_NOTIFY_CLIENTS(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.gluster.php --notify-all-clients");
}
function GLUSTER_MOUNT(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.gluster.php --mount");
}
function ApplyDHCPDService(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.dhcpd.compile.php");
}
function ApplyBINDService(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.dhcpd.compile.php --bind");
}

function GLUSTER_IS_MOUNTED(){
	$path=base64_decode($_GET["glfs-is-mounted"]);
	$unix=new unix();
	if($unix->GLFS_ismounted($path)){echo "<articadatascgi>1</articadatascgi>";return;}
}
function PASSWD_USERS(){
	$passwd=@file_get_contents("/etc/passwd");
	writelogs_framework("/etc/passwd ". strlen($passwd)." bytes" ,__FUNCTION__,__FILE__,__LINE__);
	$f=explode("\n",$passwd);
	writelogs_framework("/etc/passwd ". count($f)." lines" ,__FUNCTION__,__FILE__,__LINE__);
	while (list ($num, $ligne) = each ($f) ){
		if(preg_match("#^(.+?):#",$ligne,$re)){
			$array[$re[1]]=$re[1];
		}
	}
	writelogs_framework("/etc/passwd ". count($array)." rows" ,__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>" .base64_encode(serialize($array))."</articadatascgi>";
	
}

function SSHD_KEY_GEN(){
	$uid=$_GET["uid"];
	$unix=new unix();
	$path=base64_decode($_GET["ssh-keygen"]);
	$echo=$unix->find_program("echo");
	$sshkeygen=$unix->find_program("ssh-keygen");
	$su=$unix->find_program("su");
	@mkdir("$path",null,true);
	@chown($path,$uid);
	$maincmd="$echo y|$sshkeygen -t rsa -N '' -q -f $path/id_rsa";
	
	if($uid<>"root"){
		$maincmd="$su $uid -c \"$maincmd\"";
	}
	writelogs_framework("$maincmd" ,__FUNCTION__,__FILE__,__LINE__);
	exec($maincmd,$results);
	echo "<articadatascgi>" .base64_encode(@implode("\n",$results))."</articadatascgi>";
}



function SSHD_KEY_FINGERPRINT(){
	$uid=$_GET["uid"];
	$unix=new unix();
	$path=base64_decode($_GET["ssh-keygen-fingerprint"]);
	echo "<articadatascgi>" .base64_encode($unix->SSHD_GET_FINGERPRINT("$path/id_rsa.pub"))."</articadatascgi>";
}

function SSHD_KEY_DOWNLOAD_PUB(){
	$path=base64_decode($_GET["ssh-keygen-download"]);
	shell_exec("/bin/cp $path/id_rsa.pub /usr/share/artica-postfix/ressources/logs/web/id_rsa.pub");
	@chmod("/usr/share/artica-postfix/ressources/logs/web/id_rsa.pub",0777);
	
}
function SSHD_KEY_UPLOAD_PUB(){
	$uploaded_file=base64_decode($_GET["rsa"]);
	$homedirectory=base64_decode($_GET["home"]);
	$uid=$_GET["uid"];
	if(!is_file($uploaded_file)){
		echo "<articadatascgi>" .base64_encode("$uploaded_file no such file")."</articadatascgi>";
		exit;
	}
	$unix=new unix();
	$fingerprint=$unix->SSHD_GET_FINGERPRINT($uploaded_file);
	if($fingerprint==null){
		echo "<articadatascgi>" .base64_encode("{fingerprint} {corrupted}")."</articadatascgi>";
		exit;
	}
	$cat=$unix->find_program("cat");
	$uploaded_file=$unix->shellEscapeChars($uploaded_file);
	@mkdir($homedirectory,null,true);
	$cmd="$cat $uploaded_file >>$homedirectory/authorized_keys";
	writelogs_framework("$cmd" ,__FUNCTION__,__FILE__,__LINE__);
	exec("$cat $uploaded_file >>$homedirectory/authorized_keys",$results);
	if(is_file("$homedirectory/authorized_keys")){
		echo "<articadatascgi>" .base64_encode("{success}")."</articadatascgi>";
		exit;
	}
	$logs=@implode("<br>",$results);
	
	shell_exec("/bin/chmod -R 700 $homedirectory");
	shell_exec("/bin/chmod 600 $homedirectory/*");
	shell_exec("/bin/chown -R $uid:$uid $homedirectory");
	
	
	echo "<articadatascgi>" .base64_encode("{failed}<br>$logs")."</articadatascgi>";
}
function JOOMLA_INSTALL(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.joomla.install.php");
}

function OCSWEB_WEB_EVENTS(){
	$unix=new unix();
	$tail=$unix->find_program("tail");
	exec("$tail -n 350 /var/log/ocsinventory-server/apache-access.log 2>&1",$results);
	echo "<articadatascgi>" .base64_encode(serialize($results))."</articadatascgi>";
}
function OCSWEB_WEB_ERRORS(){
	$unix=new unix();
	$tail=$unix->find_program("tail");
	exec("$tail -n 350 /var/log/ocsinventory-server/apache-error.log 2>&1",$results);
	echo "<articadatascgi>" .base64_encode(serialize($results))."</articadatascgi>";
}
function OCSWEB_SERV_EVENTS(){
	$unix=new unix();
	$tail=$unix->find_program("tail");
	exec("$tail -n 350 /var/log/ocsinventory-server/activity.log 2>&1",$results);
	echo "<articadatascgi>" .base64_encode(serialize($results))."</articadatascgi>";
}
function GET_LOCAL_SID(){
	$unix=new unix();
	echo "<articadatascgi>" .$unix->GET_LOCAL_SID()."</articadatascgi>";
}
function AUDITD_REBUILD(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.auditd.php --build");
}

function AUDITD_SAVE_CONFIG(){
	shell_exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.auditd.php --conf");
}

function AUDITD_CONFIG(){
	$datas=base64_encode(@file_get_contents("/etc/audit/auditd.conf"));
	writelogs_framework("/etc/audit/auditd.conf= ". strlen($datas)." bytes",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>$datas</articadatascgi>";
}


function AUDITD_FORCE(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.auditd.php --import --force");
}
function ReadFromfile(){
	$datas=@file_get_contents(base64_decode($_GET["read-file"]));
	writelogs_framework("". strlen($datas)." bytes",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>" .base64_encode($datas)."</articadatascgi>";
}
function postfix_import_domains_ou(){
	$ou_decoded=base64_decode($_GET["ou"]);
	$file="/var/log/artica-postfix/domains.import.$ou_decoded.log";
	if(is_file($file)){@unlink($file);}
	writelogs_framework("Scheduling \"{$_GET["file"]}\ \"{$_GET["ou"]}\"",__FUNCTION__,__FILE__,__LINE__);
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.import.smtp.domains.php \"{$_GET["file"]}\" \"{$_GET["ou"]}\"");
	
}

function postfix_import_domains_ou_events(){
	$ou=base64_decode($_GET["ou"]);
	$file="/var/log/artica-postfix/domains.import.$ou.log";
	if(!is_file($file)){
		writelogs_framework("$file no such file",__FUNCTION__,__FILE__,__LINE__);
	}
	echo "<articadatascgi>" .@file_get_contents($file)."</articadatascgi>";
}
function IPTABLES_CHAINES_BRIDGE_RULES(){
	if(!is_numeric($_GET["iptables-bridge-rules"])){return;}
	$unix=new unix();
	$iptables_save=$unix->find_program("iptables-save");

	
	$_GET["iptables-bridge-rules"]=trim($_GET["iptables-bridge-rules"]);
	$cmd="$iptables_save 2>&1";
	exec($cmd,$results);
	writelogs_framework(count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	$pattern="#.+?ArticaBridgesVirtual:{$_GET["iptables-bridge-rules"]}#";	
	$count=0;
	while (list ($num, $ligne) = each ($results) ){
		if($ligne==null){continue;}
		if(preg_match($pattern,$ligne)){$r[]=$ligne;}	
	}
	echo "<articadatascgi>" .base64_encode(serialize($r))."</articadatascgi>";
	
	
}

function IPTABLES_CHAINES_ROTATOR(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.ip-rotator.php --build");
}

function IPTABLES_CHAINES_ROTATOR_SHOW(){
	$unix=new unix();
	$iptables_save=$unix->find_program("iptables-save");
	$grep=	$unix->find_program("grep");
	$cmd="$iptables_save|$grep ArticaIpRotator 2>&1";
	exec($cmd,$results);
	writelogs_framework($cmd." -> ".count($results)." rows",__FUNCTION__,__FILE__,__LINE__);	
	echo "<articadatascgi>" .base64_encode(serialize($results))."</articadatascgi>";
}

function OPENDKIM_RESTART(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart dkfilter");
}
function MILTERDKIM_RESTART(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart dkim-milter");
}


function OPENDKIM_SHOW_KEYS(){
	shell_exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.opendkim.php --buildKeyView");
	echo "<articadatascgi>" .@file_get_contents("/etc/mail/dkim.domains.key")."</articadatascgi>";
	}
function MILTERDKIM_SHOW_KEYS(){
	shell_exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.dkim-milter.php --buildKeyView");
	echo "<articadatascgi>" .@file_get_contents("/etc/mail/dkim.domains.key")."</articadatascgi>";
	}	
function OPENDKIM_SHOW_TESTS_KEYS(){
	shell_exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.opendkim.php --TESTKeyView");
	echo "<articadatascgi>" .@file_get_contents("/etc/mail/dkim.domains.tests.key")."</articadatascgi>";
	}
function MILTERDKIM_SHOW_TESTS_KEYS(){
	shell_exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.dkim-milter.php --TESTKeyView");
	echo "<articadatascgi>" .@file_get_contents("/etc/mail/dkim.domains.tests.key")."</articadatascgi>";
	}	
	

	
	
function squidGuardDatabaseMaintenance(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.c-icap.php --maint-schedule");
}
function squidGuardDatabaseMaintenanceNow(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.c-icap.php --db-maintenance");
}
function SETUP_CENTER_LAUNCH(){
	$app=$_GET["start-install-app"];
	writelogs_framework("$app to install",__FUNCTION__,__FILE__,__LINE__);
	if(trim($app)==null){return;}
	$unix=new unix();
	$cmd="/usr/share/artica-postfix/bin/artica-install --install-status $app";
	exec($cmd,$results);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	while (list ($num, $ligne) = each ($results) ){
		writelogs_framework("$ligne",__FUNCTION__,__FILE__,__LINE__);
	}
	$tmpfile="/usr/share/artica-postfix/ressources/install/$app.dbg";
	
	if(is_file($tmpfile)){if($unix->file_time_min($tmpfile)<1){return;}}

	
	@file_put_contents("Scheduled","/usr/share/artica-postfix/ressources/install/$app.dbg");
	shell_exec("/bin/chmod 777 /usr/share/artica-postfix/ressources/install/$app.dbg");
	
	writelogs_framework("Schedule /usr/share/artica-postfix/bin/artica-make $app >$tmpfile 2>&1",__FUNCTION__,__FILE__,__LINE__);
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-make $app >$tmpfile 2>&1");
	}

function KERNEL_SYSCTL_VALUE(){
	$key=base64_decode($_GET["key"]);
	$unix=new unix();
	$sysctl=$unix->find_program("sysctl");
	exec("$sysctl -n $key",$results);
	echo "<articadatascgi>" .trim(@implode(" ",$results))."</articadatascgi>";
	
	
}

function KERNEL_SYSCTL_SET_VALUE(){
	$key=base64_decode($_GET["key"]);
	$unix=new unix();
	$sysctl=$unix->find_program("sysctl");
	$value=$_GET["sysctl-setvalue"];
	$cmd="sysctl -w $key=$value";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);
	
}

function SQUID_TASK_CACHE(){
	echo "<articadatascgi>" .trim(@file_get_contents("/etc/artica-postfix/settings/Daemons/SquidCacheTask"))."</articadatascgi>";
}
function SQUID_WRAPZAP(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.squid.php --wrapzap");
}
function SQUID_WRAPZAP_COMPILE(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.squid.php --wrapzap-compile --reload");
}
function SQUID_TEMPLATES(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.squid.php --templates");
}

function virtualbox_status(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --vboxwebsrv --nowachdog",$results);
	writelogs_framework(count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>". base64_encode(implode("\n",$results))."</articadatascgi>";	
}
function virtualbox_all_status(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --vdi --nowachdog",$results);
	writelogs_framework(count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>". base64_encode(implode("\n",$results))."</articadatascgi>";	
}

function virtualbox_list_vms(){
	$unix=new unix();
	$array=array();
	$manage=$unix->find_program("VBoxManage");
	if($manage==null){
		writelogs_framework("VBoxManage no such tool",__FUNCTION__,__FILE__,__LINE__);
		return;
	}
	
	exec("$manage list -l vms",$results);
	while (list($index,$line)=each($results)){
		if(preg_match("#(.+?):(.+)#",$line,$re)){
			$key=trim($re[1]);
			$data=trim($re[2]);
			if($key=="Name"){
				if(!preg_match("#\(UUID:\s+#",$data)){$VirtualBoxName=$data;}
			}
		
		
		if($VirtualBoxName<>null){
			if(strtoupper($key)=="NAME"){
				if($array[$VirtualBoxName]["NAME"]<>null){
					
					if(!$GLOBALS["VBXSNAPS"][$data]){
						$array[$VirtualBoxName]["SNAPS"][]=$data;
						$GLOBALS["VBXSNAPS"][$data]=true;
						continue;
					}
					continue;
				}
			}
			$array[$VirtualBoxName][strtoupper($key)]=$data;
		}
	}
}

	echo "<articadatascgi>" .base64_encode(serialize($array))."</articadatascgi>";
	
}
function virtualbox_showvminfo(){
	$unix=new unix();
	$array=array();
	$manage=$unix->find_program("VBoxManage");
	if($manage==null){
		writelogs_framework("VBoxManage no such tool",__FUNCTION__,__FILE__,__LINE__);
		return;
	}
	
	$uuid=base64_decode($_GET["uuid"]);
	
	exec("$manage showvminfo $uuid",$results);
	while (list($index,$line)=each($results)){
		if(preg_match("#(.+?):(.+)#",$line,$re)){
			$key=trim($re[1]);
			$data=trim($re[2]);
			
		}
		
	if(strtoupper($key)=="NAME"){	
		if($array["NAME"]<>null){
			if(!$GLOBALS["VBXSNAPS"][$data]){
				$array["SNAPS"][]=$data;
				$GLOBALS["VBXSNAPS"][$data]=true;
				continue;
				}			
			continue;}
	}
	
	$array[strtoupper($key)]=$data;
	}
	exec("$manage list hdds",$results2);
	while (list($index,$line)=each($results2)){
		if(preg_match("#(.+?):(.+)#",$line,$re)){
			$key=trim($re[1]);
			$data=trim($re[2]);
			
		}
		
		if($key=="UUID"){$UUID=$data;continue;}
		if($key=="Location"){$filename=$data;continue;}
		if($key=="Usage"){
			if(preg_match("#UUID:\s+$uuid#",$data)){
				$array["HDS"][$UUID]=$filename;
			}
		}
	}
	
	

	echo "<articadatascgi>" .base64_encode(serialize($array))."</articadatascgi>";
	
}


function virtualbox_clonehd(){
	$unix=new unix();
	$array=unserialize(base64_decode($_GET["virtualbox-clonehd"]));
	
	$manage=$unix->find_program("VBoxManage");
	if($manage==null){
		writelogs_framework("VBoxManage no such tool",__FUNCTION__,__FILE__,__LINE__);
		return;
	}
	$NAME=$array["NAME"];
	$NAME=str_replace(" ","-",$NAME);
	$uuid=$array["uuid"];
	$filename=$array["filename"];
	$type=$array["type"];
	$format=$array["format"];
	writelogs_framework("\"uuid\"=>$uuid,\"filename\"=>$filename,\"type\"=>$type,\"format\"=>$format",__FUNCTION__,__FILE__,__LINE__);
	if($uuid==null){return;}
	if(!is_file($filename)){return;}
	$basename=basename($filename);
	$dirname=dirname($filename);
	
	if(strpos($basename,"}")==0){
		if(preg_match("#(.+?)\.(.+?)$#",$basename,$re)){
		$newfile=$re[1]."-".time().".".$re[2];
		}else{
			$newfile=$NAME."-".time().".vdi";
		}
	}else{
		$newfile=$NAME."-".time().".vdi";
	}
	
	if($format<>null){$add[]="--format $format";}
	if($type<>null){$add[]="--type $type";}
	
	$add[]="--remember";
	$cmd="$manage clonehd $uuid $dirname/$newfile ".@implode(" ",$add);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$results);
	echo "<articadatascgi>". @implode("\n",$results)."</articadatascgi>";	
	
	
}

function virtualbox_showcpustats(){
	
	$unix=new unix();
	$computer_name=base64_decode($_GET["virtual-machine"]);
	$array=array();
	$manage=$unix->find_program("VBoxManage");
	if($manage==null){
		writelogs_framework("VBoxManage no such tool",__FUNCTION__,__FILE__,__LINE__);
		return;
	}
	$cmd="$manage metrics query $computer_name";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	exec("$manage metrics query $computer_name 2>&1",$results);
	while (list($index,$line)=each($results)){
		if(preg_match("#\s+CPU\/Load\/User\s+(.+)#",$line,$re)){
			$cpu_load_user=str_replace("%","",$re[1]);
			$cpu_load_user=str_replace(" ","",$cpu_load_user);
			$array["CPU_LOAD_USER_TABLE"]=explode(",",$cpu_load_user);	
		}
		if(preg_match("#\s+CPU\/Load\/Kernel\s+(.+)#",$line,$re)){
			$cpu_load_user=str_replace("%","",$re[1]);
			$cpu_load_user=str_replace(" ","",$cpu_load_user);
			$array["CPU_LOAD_KERNEL_TABLE"]=explode(",",$cpu_load_user);	
		}		
		
		if(preg_match("#\s+CPU\/Load\/Kernel:avg\s+([0-9\.]+)#",$line,$re)){
			$array["CPU_LOAD_KERNEL"]=$re[1];
		}
		if(preg_match("#\s+CPU\/Load\/User:avg\s+([0-9\.]+)#",$line,$re)){
			$array["CPU_LOAD_USER"]=$re[1];
		}
		if(preg_match("#\s+RAM\/Usage\/Used:avg\s+([0-9\.]+)#",$line,$re)){
			$array["RAM_USAGE"]=$re[1];
		}
		if(preg_match("#RAM/Usage/Used\s+(.+)#",$line,$re)){
			$cpu_load_user=str_replace(" kB","",$re[1]);
			$cpu_load_user=str_replace(" ","",$cpu_load_user);
			$array["CPU_LOAD_MEMORY_TABLE"]=explode(",",$cpu_load_user);	
		}		
		
	}
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";	
}


	
function KEYBOARD_KEY_MAP(){
	$unix=new unix();
	exec($unix->find_program("find") ." /usr/share/keymaps/i386",$results);
	while (list($index,$line)=each($results)){
		$line=str_replace("/usr/share/keymaps/i386/","",$line);
		if(preg_match("#.+?\/([A-Za-z0-9\-\_]+)\.kmap\.gz$#",$line,$re)){
			$array[$re[1]]=$re[1];
		}
		
	}
	
	ksort($array);
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";	
}

function THINCLIENT_REBUILD(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.thinclient.php --workstations");
}

function THINCLIENT_REBUILD_CD(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.thinclient.php");
}


function virtualbox_stop(){
	$uuid=$_GET["virtualbox-stop"];
	$unix=new unix();
	$array=array();
	$manage=$unix->find_program("VBoxManage");
	if($manage==null){
		writelogs_framework("VBoxManage no such tool",__FUNCTION__,__FILE__,__LINE__);
		return;
	}

	$cmd="$manage controlvm $uuid poweroff >/tmp/$uuid-stop 2>&1 &";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);
	sleep(5);
	echo "<articadatascgi>". @file_get_contents("/tmp/$uuid-stop")."</articadatascgi>";
	
}



function virtualbox_start(){
	$uuid=$_GET["virtualbox-start"];
	$unix=new unix();
	$array=array();
	$manage=$unix->find_program("VBoxManage");
	if($manage==null){
		writelogs_framework("VBoxManage no such tool",__FUNCTION__,__FILE__,__LINE__);
		return;
	}	
	
	$VBoxHeadless=$unix->LOCATE_VBoxHeadless();
	if(is_file($VBoxHeadless)){
		$cmd="$VBoxHeadless --startvm $uuid --vrdp on >/tmp/$uuid-start 2>&1 &";
	}else{
		$cmd="$manage startvm $uuid --type headless >/tmp/$uuid-start 2>&1 &";
	}
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);
	sleep(5);
	echo "<articadatascgi>". @file_get_contents("/tmp/$uuid-start")."</articadatascgi>";
}
function virtualbox_snapshot(){
	$uuid=$_GET["virtualbox-snapshot"];
	$unix=new unix();
	$array=array();
	$manage=$unix->find_program("VBoxManage");
	if($manage==null){
		writelogs_framework("VBoxManage no such tool",__FUNCTION__,__FILE__,__LINE__);
		return;
	}	
	
	$time=time();
	$date=date("Y-m-d H:i:s");
	$cmd="$manage snapshot $uuid take $time --description \"saved on $date\" >/tmp/$uuid-stop 2>&1 &";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);
	sleep(5);
	echo "<articadatascgi>". @file_get_contents("/tmp/$uuid-start")."</articadatascgi>";
}
function virtualbox_guestmemoryballoon(){
	$uuid=$_GET["virtualbox-guestmemoryballoon"];
	$unix=new unix();
	$array=array();
	$VBoxManage=$unix->find_program("VBoxManage");
	if($VBoxManage==null){
		writelogs_framework("VBoxManage no such tool",__FUNCTION__,__FILE__,__LINE__);
		return;
	}	
	$mem=$_GET["mem"];
	$results[]="Change settings on Opened Virtual machine:";
	$cmd="$VBoxManage controlvm $uuid guestmemoryballoon $mem 2>&1";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$results);
	
	$results[]="";
	$results[]="Save settings on Virtual machine:";
	$cmd="$VBoxManage modifyvm $uuid --guestmemoryballoon $mem 2>&1";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$results);	
	$results=VirtualBoxCleanArrayPub($results);
	echo "<articadatascgi>". base64_encode(@implode("\n",$results))."</articadatascgi>";
}


function virtualbox_set_params(){
$unix=new unix();
	$array=array();
	$VBoxManage=$unix->find_program("VBoxManage");
	if($VBoxManage==null){
		writelogs_framework("VBoxManage no such tool",__FUNCTION__,__FILE__,__LINE__);
		return;
	}		
	$uuid=$_GET["virtualbox-set-params"];
	$cmd="$VBoxManage modifyvm $uuid --{$_GET["key"]} {$_GET["value"]} 2>&1";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$results);	
	$results=VirtualBoxCleanArrayPub($results);
	echo "<articadatascgi>". base64_encode(@implode("\n",$results))."</articadatascgi>";	
}


function virtualbox_nats(){
	$unix=new unix();
	$filetmp=$unix->FILE_TEMP();
	shell_exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.virtualbox.php --nat-ports >$filetmp 2>&1");
	echo "<articadatascgi>". base64_encode(@file_get_contents($filetmp))."</articadatascgi>";
	@unlink($filetmp);
}
function virtualbox_nat_rebuild(){
	$unix=new unix();
	$filetmp=$unix->FILE_TEMP();
	$uuid=$_GET["virtualbox-nats-rebuild"];
	shell_exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.virtualbox.php --nat-rebuild $uuid >$filetmp 2>&1");
	echo "<articadatascgi>". base64_encode(@file_get_contents($filetmp))."</articadatascgi>";
	@unlink($filetmp);
}



function virtualbox_nat_del(){
	$unix=new unix();
	$VBoxManage=$unix->find_program("VBoxManage");	
	if(strlen($VBoxManage)<4){return;}
	
	$vboxid=$_GET["uuid"];
	$localport=$_GET["localport"];
	$vboxport=$_GET["vboxport"];
	$cmd="$VBoxManage setextradata $vboxid \"VBoxInternal/Devices/pcnet/0/LUN#0/Config/ArticaNat{$localport}To{$vboxport}/HostPort\"";
	exec($cmd,$results);
	$cmd="$VBoxManage setextradata $vboxid \"VBoxInternal/Devices/pcnet/0/LUN#0/Config/ArticaNat{$localport}To{$vboxport}/GuestPort\" 2>&1";
	exec($cmd,$results);
	$cmd="$VBoxManage setextradata $vboxid \"VBoxInternal/Devices/pcnet/0/LUN#0/Config/ArticaNat{$localport}To{$vboxport}/Protocol\" 2>&1";
	exec($cmd,$results);
	echo "<articadatascgi>". base64_encode(@implode("\n",VirtualBoxCleanArrayPub($results)))."</articadatascgi>";
}

function VirtualBoxCleanArrayPub($array){
	if(!is_array($array)){return;}
	while (list($index,$line)=each($array)){
		if(strpos($line,"VirtualBox Command Line Management")>0){continue;}
		if(strpos($line,"Oracle Corporation")>0){continue;}
		if(strpos($line,"rights reserved.")>0){continue;}
		if(trim($line)==null){continue;}
		if(preg_match("#Context:\s+#",$line)){continue;}
		if(preg_match("#Details:\s+#",$line)){continue;}
		$returned[]=$line;
	}
	
	return $returned;
}

function virtualbox_install(){
	@unlink("/usr/share/artica-postfix/ressources/logs/vdi-install.dbg");
	@file_put_contents("/usr/share/artica-postfix/ressources/logs/vdi-install.dbg","{scheduled}");
	@chmod("/usr/share/artica-postfix/ressources/logs/vdi-install.dbg",777);
	sys_THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/setup-ubuntu --check-virtualbox >>/usr/share/artica-postfix/ressources/logs/vdi-install.dbg 2>&1");
	
}

function getDefaultGateway(){
	$unix=new unix();
	$ipbin=$unix->find_program("ip");
	if($ipbin==null){return;}
	exec("$ipbin route 2>&1",$results);
	while (list($index,$line)=each($results)){
		if(preg_match("#default via\s+(.+?)\s+dev#",$line,$re)){
			echo "<articadatascgi>{$re[1]}</articadatascgi>";
			return;
		}
	}
	
}

function CROSSROADS_RESTART(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart crossroads");
}


function pptpd_ifconfig(){
	$unix=new unix();
	$ifconfig=$unix->find_program("ifconfig");
	writelogs_framework("$ifconfig -a 2>&1",__FUNCTION__,__FILE__,__LINE__);
	exec("$ifconfig -a 2>&1",$results);
	while (list($index,$line)=each($results)){
		if(preg_match("#ppp([0-9]+).+?Point-to-Point#",$line,$re)){$ppp="ppp{$re[1]}";continue;}
		if(preg_match("#inet addr:([0-9\.]+)\s+P-t-P:([0-9\.]+)\s+Mask:([0-9\.]+)#",$line,$re)){
			writelogs_framework("$ppp {$re[1]} ,{$re[2]},{$re[3]} ",__FUNCTION__,__FILE__,__LINE__);
			$array[$ppp]=array(
				"INET"=>$re[1],
				"REMOTE"=>$re[2],"MASK"=>$re[3]
			);
			continue;
			
		}
		
		
	}
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
}

function mailbox_migration_import_file(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.mailbox.migration.php --files");
	
}
function mailbox_migration_start_members(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.mailbox.migration.php --accounts");
}

function PROXY_SAVE(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.system.php --proxy");
	
}

function SARG_SAVE(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.sarg.php --conf");
}
function SARG_EXEC(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.sarg.php --exec");
}
function SARG_PASSWORDS(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.sarg.php --passwords");
}

function DDCLIENT_RESTART(){
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart ddclient");
}

function cluebringer_restart(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd="$nohup /etc/init.d/artica-postfix restart cluebringer >/dev/null 2>&1 &";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);	
	
}

function cluebringer_status(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --cluebringer --nowachdog",$results);
	echo "<articadatascgi>". base64_encode(@implode("\n",$results))."</articadatascgi>";
}
function cluebringer_passwords(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.cluebringer.php --passwords");
}

function qos_iptables(){
	//qos-iptables
	$datas=@file_get_contents("/etc/artica-postfix/qos.cmds");
	echo "<articadatascgi>". base64_encode($datas)."</articadatascgi>";	
}
function qos_compile(){
	//qos-compile
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd="$nohup ". LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.qos.php --build >/dev/null 2>&1 &";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);	
	
}
function clamd_restart(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd="$nohup /etc/init.d/artica-postfix restart clamd >/dev/null 2>&1 &";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec(trim($cmd));	
	}
	
function clamd_reload(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd="$nohup /usr/share/artica-postfix/bin/artica-install --clamd-reload >/dev/null 2>&1 &";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec(trim($cmd));	
}
	
	
function pureftpd_restart(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd="$nohup /etc/init.d/artica-postfix restart ftp >/dev/null 2>&1 &";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec(trim($cmd));		
	}	
function ORGANISATION_RENAME(){
	$newname=base64_decode($_GET["to"]);
	$oldname=base64_decode($_GET["from"]);
	$cmd= LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.ldap.move-orgs.php \"$newname\" \"$oldname\" 2>&1";
	exec($cmd,$results);
	echo "<articadatascgi>". base64_encode(@implode("\n",$results))."</articadatascgi>";
	}


function artica_meta_register(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");	
	$cmd="$nohup ". LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.artica.meta.php --register >/dev/null 2>&1 &";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);		
}

function artica_meta_join(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");	
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.artica.meta.php --join >/dev/null";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);		
}
function artica_meta_unjoin(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");	
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.artica.meta.php --unjoin >/dev/null 2>&1";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);		
}
function artica_meta_push(){
	$key=$_GET["artica-meta-push"];
	@mkdir("/etc/artica-postfix/artica-meta-queue-socks",666,true);
	$file="/etc/artica-postfix/artica-meta-queue-socks/".md5($key).".sock";
	@file_put_contents($file,$key);
	}

function artica_meta_user(){
	$uid=$_GET["artica-meta-user"];
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.artica.meta.users.php --user \"$uid\" >/dev/null 2>&1 &";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);		
}

function artica_meta_computer(){
	$uid=$_GET["artica-meta-computer"];
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.artica.meta.users.php --computer \"$uid\" >/dev/null 2>&1 &";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);		
}
function artica_meta_fetchmail_rules(){
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.artica.meta.users.php --export-fetchmail-rules >/dev/null 2>&1 &";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);	
}
function artica_meta_ovpn(){
	$uid=$_GET["uid"];
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.artica.meta.users.php --ovpn \"$uid\"";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	sys_THREAD_COMMAND_SET($cmd);	
}

function VboxPid(){
	$unix=new unix();
	$pid=$unix->PIDOF_PATTERN($_GET["VboxPid"]);
	if($pid>0){
		$array["PID"]=$pid;
		$array["INFOS"]="[APP_VIRTUALBOX]\n".$unix->GetSingleMemoryOf($pid);
		
		
		
	}
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
}


function artica_meta_user_export_dns(){
	$ArticaMetaEnabled=trim(@file_get_contents("/etc/artica-postfix/settings/Daemons/ArticaMetaEnabled"));
	if($ArticaMetaEnabled<>1){return;}
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.artica.meta.users.php --export-all-dns >/dev/null 2>&1 &";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);		
}

function artica_meta_export_awstats(){
	$ArticaMetaEnabled=trim(@file_get_contents("/etc/artica-postfix/settings/Daemons/ArticaMetaEnabled"));
	if($ArticaMetaEnabled<>1){return;}
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.artica.meta.users.php --export-awstats >/dev/null 2>&1 &";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);		
	
}
function artica_meta_export_openvpn_sites(){
	$ArticaMetaEnabled=trim(@file_get_contents("/etc/artica-postfix/settings/Daemons/ArticaMetaEnabled"));
	if($ArticaMetaEnabled<>1){return;}
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.artica.meta.users.php --export-openvpn-sites >/dev/null 2>&1 &";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);		
	
}



//artica-meta-export-dns




function RestartApacheSrc(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");	
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.freeweb.php --build >/dev/null 2>&1 &";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);	
	$cmd="$nohup /etc/init.d/artica-postfix restart apachesrc >/dev/null 2>&1 &";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);		
}
function pureftpd_users(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");	
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.pureftpd.php >/dev/null 2>&1 &";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);		
}
function postfix_mem_disk_status(){
	$hostname=$_GET["postfix-mem-disk-status"];
	if($hostname=="master"){
		$directory="/var/spool/postfix";
	}else{
		$directory="/var/spool/postfix-$hostname";
	}
	
	$unix=new unix();
	$mem=$unix->MOUNTED_TMPFS_MEM($directory);
	$TOTAL_MEMORY_MB=$unix->TOTAL_MEMORY_MB();
	$TOTAL_MEMORY_MB_FREE=$unix->TOTAL_MEMORY_MB_USED();
	$array=array("MOUTED"=>$mem,"TOTAL_MEMORY_MB"=>$TOTAL_MEMORY_MB,"TOTAL_MEMORY_MB_FREE"=>$TOTAL_MEMORY_MB_FREE);
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
	
}

function awstats_perform(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");	
	$cmd=$nohup.LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.awstats.php --single \"{$_GET["awstats-perform"]}\" >/dev/null 2>&1 &";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);		
}

function WakeOnLan(){
	$wol=new WakeOnLanClass();
	$wol->wake(base64_decode($_GET["wake-on-lan"]));
	echo "<articadatascgi>". base64_encode(@implode("\n",$wol->error))."</articadatascgi>";
	
}

function UpdateUtilitySource(){
	echo "<articadatascgi>". @file_get_contents("/opt/kaspersky/UpdateUtility/updater.ini")."</articadatascgi>";
}

function postscreen(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");	
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.maincf.php --postscreen \"{$_GET["hostname"]}\">/dev/null 2>&1 &";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);		
	
}

function postfix_single_ssl(){
		$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.maincf.php --ssl";
		writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
		sys_THREAD_COMMAND_SET($cmd);
}

function postfix_single_sasl_mech(){
		$cmd="/usr/share/artica-postfix/bin/artica-install --postfix-sasldb2";
		writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
		sys_THREAD_COMMAND_SET($cmd);	
}

function postfix_postfinger(){
	exec("/usr/share/artica-postfix/bin/postfinger --nowarn 2>&1",$resuts);
	echo "<articadatascgi>". base64_encode(serialize($resuts))."</articadatascgi>";
}

function postfix_throttle(){
	$instance=$_GET["instance"];
	if($instance=="master"){
		$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.maincf.php --ssl";
		writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
		sys_THREAD_COMMAND_SET($cmd);
		
		$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.hashtables.php --transport";
		writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
		sys_THREAD_COMMAND_SET($cmd);		
		return;
	}
	
	
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix-multi.php --instance-reconfigure \"$instance\"";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	sys_THREAD_COMMAND_SET($cmd);			
}

if(isset($_GET["postfix-notifs"])){postfix_notifs();exit;}

function postfix_notifs(){
	$instance=$_GET["hostname"];
	if($instance=="master"){
		$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.maincf.php --notifs-templates";
		writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
		sys_THREAD_COMMAND_SET($cmd);
		return;
	}
	
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix-multi.php --instance-reconfigure \"$instance\"";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	sys_THREAD_COMMAND_SET($cmd);	
}

function postfix_freeze(){
	$instance=$_GET["hostname"];
	if($instance=="master"){
		$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.maincf.php --freeze";
		writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
		sys_THREAD_COMMAND_SET($cmd);
		return;
	}
	
	
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix-multi.php --instance-reconfigure \"$instance\"";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	sys_THREAD_COMMAND_SET($cmd);			
}

function LESSFS_RESTART(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup")."$nohup ";	
	$cmd=$nohup.LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.lessfs.php >/dev/null 2>&1 &";
	if(isset($_GET["mount"])){
		$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.lessfs.php --restart";
	}
	
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);			
	
}

function LESSFS_MOUNTS(){
	$unix=new unix();
	$array=$unix->LESSFS_ARRAY();
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
	}
	
	
function LESSFS_RESTART_SERVICE(){
	unlink("/usr/share/artica-postfix/ressources/logs/web/LESS_FS_RESTART");
	@file_put_contents("/usr/share/artica-postfix/ressources/logs/web/LESS_FS_RESTART","scheduled\nPlease Wait....");
	@chmod("/usr/share/artica-postfix/ressources/logs/web/LESS_FS_RESTART",0777);
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.lessfs.php >>/usr/share/artica-postfix/ressources/logs/web/LESS_FS_RESTART 2>&1";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	sys_THREAD_COMMAND_SET($cmd);		
}

function zarafa_status(){
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --zarafa --nowachdog";
	exec($cmd,$results);
	echo "<articadatascgi>". base64_encode(@implode("\n",$results))."</articadatascgi>";
	
}
function zarafa_hash(){
	if(!is_file("/etc/artica-postfix/zarafa-export.db")){
		$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.zarafa.build.stores.php --export-hash";
		shell_exec($cmd);
	}
	echo "<articadatascgi>". @file_get_contents("/etc/artica-postfix/zarafa-export.db")."</articadatascgi>";
}

function zarafa_read_license(){
	echo "<articadatascgi>". base64_encode(@file_get_contents("/etc/zarafa/license/base"))."</articadatascgi>";
}
function zarafa_write_license(){
	@mkdir("/etc/zarafa/license");
	@file_put_contents("/etc/zarafa/license/base",base64_decode($_GET["license"]));
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart zarafa");		
}

function postmaster_cron(){
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.fcron.php --postmaster-cron";
	sys_THREAD_COMMAND_SET($cmd);	
}
function EnableEmergingThreats(){
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.emerging.threats.php";
	sys_THREAD_COMMAND_SET($cmd);	
	
}
function EnableEmergingThreatsBuild(){
	if(is_file("/usr/share/artica-postfix/ressources/logs/EnableEmergingThreatsBuild.db")){
		writelogs_framework("ressources/logs/EnableEmergingThreatsBuild.db Alreay exists.",__FUNCTION__,__FILE__);
		@chmod("/usr/share/artica-postfix/ressources/logs/EnableEmergingThreatsBuild.db",0777);
		echo "<articadatascgi>Done</articadatascgi>";
		return;
	}
	$unix=new unix();;
	$ipset=$unix->find_program("ipset");
	if(!is_file("$ipset")){
		echo "<articadatascgi>Fatal ipset, no such file</articadatascgi>";
		return;
	}
	writelogs_framework("$ipset -L botccnet >/etc/artica-postfix/botccnet.list",__FUNCTION__,__FILE__,__LINE__);
    shell_exec("$ipset -L botccnet >/etc/artica-postfix/botccnet.list");
    $tr=explode("\n",@file_get_contents("/etc/artica-postfix/botccnet.list"));
    $conf=array();
    while (list ($num, $ligne) = each ($tr) ){
    	if(trim($ligne)==null){continue;}
    	if(preg_match("#(.+?):#",$ligne)){continue;}
    	$conf["THREADS"][]=$ligne;
    }
    
    shell_exec("$ipset --list botcc >/etc/artica-postfix/ccnet.list");
	$tr=explode("\n",@file_get_contents("/etc/artica-postfix/ccnet.list"));
    $conf=array();
    while (list ($num, $ligne) = each ($tr) ){
    	if(trim($ligne)==null){continue;}
    	if(preg_match("#(.+?):#",$ligne)){continue;}
    	$conf["THREADS"][]=$ligne;
    }    
    
    $conf["COUNT"]=count($conf["THREADS"]);
    writelogs_framework("Writing ressources/logs/EnableEmergingThreatsBuild.db done.",__FUNCTION__,__FILE__,__LINE__);
    @file_put_contents("/usr/share/artica-postfix/ressources/logs/EnableEmergingThreatsBuild.db",serialize($conf));
	@chmod("/usr/share/artica-postfix/ressources/logs/EnableEmergingThreatsBuild.db",0777);
	echo "<articadatascgi>". count($conf["THREADS"]). "</articadatascgi>";
}

function aptcheck(){
	echo "<articadatascgi>". @file_get_contents("/etc/artica-postfix/apt.upgrade.cache") ."</articadatascgi>";
}

function ping(){
	$ip=$_GET["ip"];
		if(trim($ip)==null){return false;}
		$ftmp="/tmp/". md5(__FILE__);
		exec("/bin/ping -q -c 1 -s 16 -W1 -Q 0x02 $ip >$ftmp 2>&1");
		$results=explode("\n",@file_get_contents($ftmp) );
		@unlink($ftmp);
		if(!is_array($results)){return false;}
		while (list ($index, $line) = each ($results) ){
			if(preg_match("#[0-9]+\s+[a-zA-Z]+\s+[a-zA-Z]+,\s+([0-9]+)\s+received#",$line,$re)){
				if($re[1]>0){
					$ping_check=true;
				}else{
					$ping_check=false;
				}
			}
		}
	if ($ping_check){echo "<articadatascgi>TRUE</articadatascgi>";return;}
	echo "<articadatascgi>FALSE</articadatascgi>";
}

function net_ads_info(){
	
	if($_GET["reconnect"]=="yes"){shell_exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.samba.php --ads");}
	
	
	$cachefile="/etc/artica-postfix/NetADSInfo.cache";
	$cachefilesize=filesize($cachefile);
	writelogs_framework("$cachefile $cachefilesize",__FUNCTION__,__FILE__,__LINE__);
	
	
	if(is_file("/etc/artica-postfix/NetADSInfo.cache")){
		$filetime=file_time_min($cachefile);
		if($filetime<30){
			writelogs_framework("$cachefile {$filetime}Mn",__FUNCTION__,__FILE__,__LINE__);
			$results=explode("\n",@file_get_contents($cachefile));
			}
	}
	
	writelogs_framework("results= ".count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	if(!is_array($results)){
		$unix=new unix();
		$net=$unix->LOCATE_NET_BIN_PATH();
		if(!is_file($net)){$unix->send_email_events("Unable to locate net binary !!","","system");return;}
	 	writelogs_framework("$net ads info 2>&1",__FUNCTION__,__FILE__,__LINE__);
		exec("$net ads info 2>&1",$results);
		@file_put_contents($cachefile,@implode("\n",$results));
	}
		
	while (list ($index, $line) = each ($results) ){
		if(preg_match("#^(.+?):(.+)#",trim($line),$re)){
			writelogs_framework(trim($re[1])."=".trim($re[2]),__FUNCTION__,__FILE__,__LINE__);
			$array[trim($re[1])]=trim($re[2]);
		}
	}
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
	
}

function net_ads_leave(){
	$cachefile="/etc/artica-postfix/NetADSInfo.cache";
	@unlink($cachefile);
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.samba.php --ads-destroy 2>&1";
	exec($cmd,$results);
	echo "<articadatascgi>". @implode("\n",$results)."</articadatascgi>";	
}

function process1_force(){
	$time=time();
	shell_exec("/usr/share/artica-postfix/bin/process1 --force --verbose $time");
}
function saslauthd_restart(){
	$unix=new unix();
	$unix->THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart saslauthd");
}
function cyrus_sync_to_ad(){
	$unix=new unix();
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.cyrus-restore.php --ad-sync --force";
	$unix->THREAD_COMMAND_SET($cmd);
}

function right_status(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim($nohup." ".LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.admin.status.postfix.flow.php --status-right >/dev/null 2>&1 &");
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);	
	shell_exec($cmd);
}

function freeweb_rebuild(){
	$unix=new unix();
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.freeweb.php --build";	
	$unix->THREAD_COMMAND_SET($cmd);
}

function postfix_iptables_compile(){
	$unix=new unix();
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.iptables.php --compile";	
	$unix->THREAD_COMMAND_SET($cmd);	
	
}

function clamd_pattern_status(){
	$cmd="/usr/share/artica-postfix/bin/artica-install --avpattern-status 2>&1";
	exec($cmd,$results);
	echo "<articadatascgi>". @implode("\n",$results)."</articadatascgi>";		
}

function KavMilterDbVer(){
	$unix=new unix();
	$filexml=$unix->KAV_MILTER_PATTERN_FILE();
	if(!is_file($filexml)){echo "<articadatascgi>00-00-0000 00:00</articadatascgi>";return "00-00-0000 00:00";}
	$f=explode("\n",@file_get_contents($filexml));
	while (list ($index, $line) = each ($f) ){if(preg_match("#UpdateDate=\"([0-9\s+]+)\"#",$line,$re)){$ptemp=$re[1];break;}}
	if(preg_match("#([0-9]{1,2})([0-9]{1,2})([0-9]{1,4})\s+([0-9]{1,2})([0-9]{1,2})#",$ptemp,$re)){echo "<articadatascgi>{$re[3]}-{$re[2]}-{$re[1]} {$re[4]}:{$re[5]}:00</articadatascgi>";return;}
	echo "<articadatascgi>00-00-0000 00:00</articadatascgi>";
	return "00-00-0000 00:00";	
	}
function Kas3DbVer(){
	$unix=new unix();
	$filexml="/usr/local/ap-mailfilter3/cfdata/bases/u0607g.xml";
	if(!is_file($filexml)){echo "<articadatascgi>00-00-0000 00:00</articadatascgi>";return "00-00-0000 00:00";}
	$f=explode("\n",@file_get_contents($filexml));
	while (list ($index, $line) = each ($f) ){if(preg_match("#UpdateDate=\"([0-9\s+]+)\"#",$line,$re)){$ptemp=$re[1];break;}}
	if(preg_match("#([0-9]{1,2})([0-9]{1,2})([0-9]{1,4})\s+([0-9]{1,2})([0-9]{1,2})#",$ptemp,$re)){echo "<articadatascgi>{$re[3]}-{$re[2]}-{$re[1]} {$re[4]}:{$re[5]}:00</articadatascgi>";return;}
	echo "<articadatascgi>00-00-0000 00:00</articadatascgi>";
	return "00-00-0000 00:00";	
	}
	
function SpamAssDBVer(){
	$path="/usr/share/artica-postfix/ressources/logs/sa.update.dbg";
	if(!is_file($path)){echo "<articadatascgi>00000</articadatascgi>";return "00000";}
	$f=explode("\n",@file_get_contents($path));
	while (list ($index, $line) = each ($f) ){if(preg_match("#metadata version.+?([0-9]+)#",$line,$re)){$ptemp=$re[1];break;}}
	echo "<articadatascgi>$ptemp</articadatascgi>";
}

function samba_server_role(){
	$unix=new unix();
	$testparm=$unix->find_program("testparm");
	if(!is_file($testparm)){
		writelogs_framework("testparm no such file ",__FUNCTION__,__FILE__,__LINE__);
		return null;
	}
	exec("$testparm -l -s /etc/samba/smb.conf 2>&1",$results);
	while (list ($index, $line) = each ($results) ){
		if(preg_match("#Server role:\s+([A-Z\_]+)#",$line,$re)){
			echo "<articadatascgi>{$re[1]}</articadatascgi>";
			return;
		}
	}
	
}



function maillog_query(){
	$unix=new unix();
	$head=$unix->find_program("head");
	$grep=$unix->find_program("grep");
	$cat=$unix->find_program("cat");
	$tail=$unix->find_program("tail");
	$pattern=$_GET["maillog-query"];
	$path=$_GET["maillog-path"];
	if(strpos($pattern,"*")){
		$pattern=str_replace("*",'.*?',$pattern);
		$e=" -E ";
	}
	
	if($pattern<>null){
		$cmd="$cat $path|$grep$e \"$pattern\"|$head -n 300";
	}else{
		$cmd="$tail -n 300 $path";
	}
	
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	exec("$cmd 2>&1",$results);
	echo "<articadatascgi>". @implode("\n",$results)."</articadatascgi>";
}

function rbl_check(){
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.my-rbl.check.php --query {$_GET["rbl-check"]} 2>&1";
	exec($cmd,$results);
	echo "<articadatascgi>". trim(@implode(" ",$results))."</articadatascgi>";		
}

function my_rbl_check(){
	$et=" &";
	$verbose=" --verbose";
	if(isset($_GET["force"])){
		$force=" --force";
		$et=null;
		$verbose=null;
	}
	
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.my-rbl.check.php --query {$_GET["rbl-check"]}$force";
	$cmd=$cmd." --checks$verbose 2>&1$et";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	shell_exec("$cmd");
}

function ChangeMysqlParams(){
	$basePath="/etc/artica-postfix/settings/Mysql";
	$arrayMysqlinfos=unserialize(base64_decode($_GET["change-mysql-params"]));
	$user=$arrayMysqlinfos["USER"];
	$password=$arrayMysqlinfos["PASSWORD"];
	$server=$arrayMysqlinfos["SERVER"];
	writelogs_framework("Change mysql parameters to $user:$password@$server",__FUNCTION__,__FILE__,__LINE__);
	@file_put_contents("$basePath/database_admin",$user);
	@file_put_contents("$basePath/database_password",$password);
	@file_put_contents("$basePath/mysql_server",$server);
	shell_exec("/usr/share/artica-postfix/bin/process1 --force ".time());
	$unix=new unix();
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.www.install.php";	
	$unix->THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart roundcube");	
	$unix->THREAD_COMMAND_SET($cmd);
}

function VIPTrackRun(){
	$unix=new unix();
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.vip.php --reports";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	$unix->THREAD_COMMAND_SET($cmd);	
}


function postfix_whitelisted_global(){
	$unix=new unix();
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.spamassassin.php --whitelist";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	$unix->THREAD_COMMAND_SET($cmd);
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.kasfilter.php";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	$unix->THREAD_COMMAND_SET($cmd);	
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.milter-greylist.php";		
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	$unix->THREAD_COMMAND_SET($cmd);		
}
function cyrus_db_config(){
	$unix=new unix();
	$cmd="/usr/share/artica-postfix/bin/artica-install --cyrus-db_config";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	$unix->THREAD_COMMAND_SET($cmd);
}

function winbindd_stop(){
	$unix=new unix();
	$cmd="/etc/init.d/artica-postfix stop winbindd";
	$unix->THREAD_COMMAND_SET($cmd);	
}


function mysql_myd_file(){
	$db=$_GET["database"];
	$table=$_GET["table"];
	$unix=new unix();
	$MYSQL_DATADIR=$unix->MYSQL_DATADIR();
	if(!is_file("$MYSQL_DATADIR/$db/$table.MDY")){
		echo "<articadatascgi>NO</articadatascgi>";
		return;
	}else{
		echo "<articadatascgi>YES</articadatascgi>";
	}
	
}

function mysql_check(){
	$db=$_GET["database"];
	$table=$_GET["table"];	
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/mysql.browse.php --mysqlcheck $db $table";	
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	$unix=new unix();
	$unix->THREAD_COMMAND_SET($cmd);		
}

function SetServerTime(){
	$time=$_GET["SetServerTime"];
	$unix=new unix();
	$bin_date=$unix->find_program("date");
	$cmd="$bin_date $time 2>&1";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$results);
	echo "<articadatascgi>". @implode("\n",$results)."</articadatascgi>";
}

function syslog_master_mode(){
	$unix=new unix();
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.syslog-engine.php --build-server";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	$unix->THREAD_COMMAND_SET($cmd);	
	
}
function syslog_client_mode(){
	$unix=new unix();
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.syslog-engine.php --build-client";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	$unix->THREAD_COMMAND_SET($cmd);	
	
}

function AUTOFS_RESTART(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup /etc/init.d/artica-postfix restart autofs >/dev/null &");
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);
}

function GREYHOLE_RESTART(){
	$unix=new unix();
	$cmd="/etc/init.d/artica-postfix restart greyhole";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	$unix->THREAD_COMMAND_SET($cmd);		
}

function GREYHOLE_DAILY_FCK(){
	$unix=new unix();
	$cmd="/usr/bin/greyhole --fsck --if-conf-changed --dont-walk-graveyard > /dev/null";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	$unix->THREAD_COMMAND_SET($cmd);		
}

function AUTOFS_RELOAD(){
	$unix=new unix();
	$davfs=$unix->find_program("mount.davfs");
	$nohup=$unix->find_program("nohup");
	if(is_file($davfs)){
		$cmd=trim($nohup." ". LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.AutoFS.php --davfs >/dev/null &");
		shell_exec($cmd);
		return;		
	}
	$cmd=trim($nohup." /etc/init.d/autofs reload >/dev/null &");
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);
	$cmd="/etc/init.d/artica-postfix restart artica-status";
	$unix->THREAD_COMMAND_SET($cmd);	
}

function IsUDPport(){
	$unix=new unix();
	$host=$_GET["host"];
	$port=$_GET["port"];
	$nc=$unix->find_program("nc");
	if(!is_file($nc)){
		echo "<articadatascgi>UNKNOWN</articadatascgi>";
		return;
	}
	$cmd="$nc -zuv $host $port 2>&1";
	exec($cmd,$results);
	while (list ($index, $line) = each ($results)){
		if(preg_match("#refused#",$line)){
			echo "<articadatascgi>FAILED</articadatascgi>";
			return;
		}
		
		if(preg_match("#open#",$line)){
			echo "<articadatascgi>OK</articadatascgi>";
			return;
		}		
		
	}
	
	echo "<articadatascgi>UNKNOWN</articadatascgi>";
	
	
}
function copyresolv(){
	$unix=new unix();
	$cp=$unix->find_program("cp");
	$chmod=$unix->find_program("chmod");
	$chown=$unix->find_program("chown");
	$copyresolv=$_GET["copyresolv"];
	if(is_file($copyresolv)){
		writelogs_framework("$copyresolv  -> /etc/resolv.conf",__FUNCTION__,__FILE__,__LINE__);
		shell_exec("$cp -f $copyresolv /etc/resolv.conf");
		shell_exec("$cp -f $copyresolv /etc/dnsmasq.resolv.conf");
		shell_exec("$chmod 0644 /etc/resolv.conf");
		shell_exec("$chown root:root /etc/resolv.conf");
	}else{
		writelogs_framework("$copyresolv no such file",__FUNCTION__,__FILE__,__LINE__);
	}
}

function freeweb_website(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=$nohup." ".LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.freeweb.php --sitename \"{$_GET["servername"]}\" >/dev/null 2>&1 &";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);		
	
}

function freeweb_groupware(){
	$unix=new unix();
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.freeweb.php --install-groupware {$_GET["servername"]}";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	$unix->THREAD_COMMAND_SET($cmd);		
}

function freeweb_permissions(){
	$unix=new unix();
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.freeweb.php --perms {$_GET["freeweb-permissions"]} --force";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	$unix->THREAD_COMMAND_SET($cmd);	
}

function postfinder(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim($nohup." ".LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.finder.php >/dev/null 2>&1 &");
	shell_exec($cmd);
}

function IP_DEL_ROUTE(){
	$cmd=trim(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.virtuals-ip.php --routes-del {$_GET["ip-del-route"]}");
	shell_exec($cmd);
}
function IP_ROUTES(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim($nohup." ".LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.virtuals-ip.php --routes >/dev/null 2>&1 &");
	shell_exec($cmd);
}

function disks_quotas_list(){
	$unix=new unix();
	echo "<articadatascgi>". base64_encode(serialize($unix->GET_QUOTA_MOUNTED()))."</articadatascgi>";
}

function quotastats(){
	$unix=new unix();
	$quotastats=$unix->find_program("quotastats");
	if(!is_file($quotastats)){return;}
	exec("$quotastats 2>&1",$results);
	while (list ($index, $line) = each ($results)){
		if(preg_match("#(.+?):(.+)#",$line,$re)){
			$array[trim($re[1])]=trim($re[2]);
		}
	}
	
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
	
}

function repquota(){
	$mount=$_GET["mount"];
	$unix=new unix();
	$repquota=$unix->find_program("repquota");	
	if(!is_file($repquota)){writelogs_framework("repquota no such file",__FUNCTION__,__FILE__,__LINE__);return;}
	$array=array();
	exec("$repquota \"$mount\" 2>&1",$results);
	writelogs_framework("$repquota $mount 2>&1 = ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	while (list ($index, $line) = each ($results)){
		if(preg_match("#Block grace time.+?\s+([0-9]+)days;\s+Inode grace time.+?\s+([0-9]+)days#",$line,$re)){
			$array["GRACES"]["Block"]=$re[1];$array["GRACES"]["Inode"]=$re[2];continue;}
		if(preg_match("#^(.+?)\s+(.+?)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+(.*?)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)#",$line,$re)){
			$uid=$re[1];
			$array["USERS"]["user:$uid"]=array(
				"STATUS"=>$re[2],
				"BLOCK_USED"=>$re[3],
				"BLOCK_SOFT"=>$re[4],
				"BLOCK_HARD"=>$re[5],
				"BLOCK_GRACE"=>$re[6],
				
				"FILE_USED"=>$re[7],
				"FILE_SOFT"=>$re[8],
				"FILE_HARD"=>$re[9],
				"FILE_GRACE"=>$re[10],			
				
			
			);
			
			continue;
		}
		
		if(preg_match("#^(.+?)\s+(.+?)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+(.*?)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+(.*)#",$line,$re)){
			$uid=$re[1];
			$array["USERS"][$uid]=array(
				"STATUS"=>$re[2],
				"BLOCK_USED"=>$re[3],
				"BLOCK_SOFT"=>$re[4],
				"BLOCK_HARD"=>$re[5],
				"BLOCK_GRACE"=>$re[6],
				
				"FILE_USED"=>$re[7],
				"FILE_SOFT"=>$re[8],
				"FILE_HARD"=>$re[9],
				"FILE_GRACE"=>$re[10],			
				
			
			);
			
			continue;
		}		
		
	}
	
	exec("$repquota -g \"$mount\" 2>&1",$results);
	writelogs_framework("$repquota $mount 2>&1 = ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	while (list ($index, $line) = each ($results)){
		if(preg_match("#Block grace time.+?\s+([0-9]+)days;\s+Inode grace time.+?\s+([0-9]+)days#",$line,$re)){
			$array["GRACES"]["Block"]=$re[1];$array["GRACES"]["Inode"]=$re[2];continue;}
		if(preg_match("#^(.+?)\s+([\-\+]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+(.*?)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)#",$line,$re)){
			$uid=$re[1];
			$array["USERS"]["group:$uid"]=array(
				"STATUS"=>$re[2],
				"BLOCK_USED"=>$re[3],
				"BLOCK_SOFT"=>$re[4],
				"BLOCK_HARD"=>$re[5],
				"BLOCK_GRACE"=>$re[6],
				
				"FILE_USED"=>$re[7],
				"FILE_SOFT"=>$re[8],
				"FILE_HARD"=>$re[9],
				"FILE_GRACE"=>$re[10],			
				
			
			);
			
			continue;
		}
		
		if(preg_match("#^(.+?)\s+(.+?)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+(.*?)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+(.*)#",$line,$re)){
			$uid=$re[1];
			$array["USERS"][$uid]=array(
				"STATUS"=>$re[2],
				"BLOCK_USED"=>$re[3],
				"BLOCK_SOFT"=>$re[4],
				"BLOCK_HARD"=>$re[5],
				"BLOCK_GRACE"=>$re[6],
				
				"FILE_USED"=>$re[7],
				"FILE_SOFT"=>$re[8],
				"FILE_HARD"=>$re[9],
				"FILE_GRACE"=>$re[10],			
				
			
			);
			
			continue;
		}		
		
	}	
	
	
	
	
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
	
}

function setquota(){
	
	writelogs_framework("Set quota for \"{$_GET["u"]}\" {$_GET["b"]} {$_GET["bh"]} {$_GET["f"]} {$_GET["fh"]}",__FUNCTION__,__FILE__,__LINE__);	
	
	if(!preg_match("#(.+?):(.+)#",$_GET["u"],$re)){
		writelogs_framework("Unable to preg_match \"{$_GET["u"]}\"",__FUNCTION__,__FILE__,__LINE__);	
		return;}
	$mount=$_GET["mount"];
	$unix=new unix();
	$results2=array();
	$setquota=$unix->find_program("setquota");		
	if(!is_file($setquota)){return;}
	if($re[1]=="user"){$prefix=" -u {$re[2]}";}
	if($re[1]=="group"){$prefix=" -g {$re[2]}";}
	

	$cmd="$setquota $prefix {$_GET["b"]} {$_GET["bh"]} {$_GET["f"]} {$_GET["fh"]} $mount 2>&1";
	
	exec($cmd,$results);
	writelogs_framework("$cmd = ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	
	$cmd="$setquota $prefix -T {$_GET["bg"]} {$_GET["fg"]} \"$mount\" 2>&1";
	
	
	exec($cmd,$results2);
	writelogs_framework("$cmd = ". count($results2)." rows",__FUNCTION__,__FILE__,__LINE__);

	$quotaon=$unix->find_program("quotaon");
	$quotaoff=$unix->find_program("quotaoff");
	$nohup=$unix->find_program("nohup");

	shell_exec(trim("$nohup $quotaoff \"$mount\" && $quotaon \"$mount\" >/dev/null 2>&1 &"));
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
	
		
}

function quotasrecheck(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim($nohup." ".LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.samba.php --quotas-recheck >/dev/null 2>&1 &");
	shell_exec($cmd);	
}
function IpTables_delete_all_rules(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim($nohup." ".LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.iptables.php --delete-all-iptables >/dev/null 2>&1 &");
	shell_exec($cmd);		
}

function IpTables_WhiteListResolvMX(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim($nohup." ".LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.postfix.iptables.php --transfert-white >/dev/null 2>&1 &");
	shell_exec($cmd);	
}

function spamassassin_tests(){
	if(!is_numeric($_GET["spamass-test"])){$_GET["spamass-test"]=null;}
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim($nohup." ".LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.spamassassin.php --spam-tests {$_GET["spamass-test"]} >/dev/null 2>&1 &");
	shell_exec($cmd);		
}
function DNSMASQ_LOAD_CONF(){
	$datas=@file_get_contents("/etc/dnsmasq.conf");
	echo "<articadatascgi>". base64_encode($datas)."</articadatascgi>";
}

function DNSMASQ_RESTART(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim($nohup." /etc/init.d/artica-postfix restart dnsmasq >/dev/null 2>&1 &");
	shell_exec($cmd);	
}
function USB_SCAN_WRITE(){
	$unix=new unix();
	if(isset($_GET["force"])){shell_exec("/usr/share/artica-postfix/bin/artica-install --usb-scan-write");return;}
	$unix->THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --usb-scan-write");
}

function iscsi_search(){
	$ip=$_GET["iscsi-search"];
	$unix=new unix();
	$iscsiadm=$unix->find_program("iscsiadm");
	$cmd="$iscsiadm --mode discovery --type sendtargets --portal $ip 2>&1";
	exec($cmd,$results);
	writelogs_framework("$cmd = ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	$array=array();
	while (list ($index, $line) = each ($results)){
		if(!preg_match("#([0-9\.]+):([0-9]+),([0-9]+)\s+(.+?):(.+)#",$line,$re)){continue;}
		$array[$re[1]][]=array("PORT"=>$re[2],"ID"=>$re[3],"ISCSI"=>$re[4],"FOLDER"=>$re[5],"IP"=>$ip);
	}
	
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
	
}

function iscsi_restart(){
	$unix=new unix();
	$unix->THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart iscsi");	
}
function iscsi_reload(){iscsi_restart();}
function iscsi_client(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim($nohup." ".LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.iscsi.php --clients >/dev/null 2>&1 &");
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);	
	shell_exec($cmd);		
}


function udevinfos(){
	$dev=$_GET["dev"];
	$unix=new unix();
	$udevinfo=$unix->find_program("udevinfo");
	$udevadm=$unix->find_program("udevadm");
	if(is_file($udevinfo)){$cmd="$udevinfo -q all -n $dev 2>&1";}
	if(is_file($udevadm)){$cmd="udevadm info --query=all --path=`/sbin/udevadm info --query=path --name=$dev` 2>&1";}
	exec($cmd,$results);
	writelogs_framework("$cmd = ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	$array=array();
	while (list ($index, $line) = each ($results)){
		if(preg_match("#E:\s+(.+?)=(.+)#",$line,$re)){
			$array[trim($re[1])]=trim($re[2]);
		}
	}	
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
	
}
function disks_dfmoinshdev(){
	$dev=$_GET["dfmoinshdev"];
	$unix=new unix();
	$df=$unix->find_program("df");	
	$cmd="$df -h $dev 2>&1";
	exec($cmd,$results);
	writelogs_framework("$cmd = ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);	
	echo "<articadatascgi>". @implode("\n",$results)."</articadatascgi>";
}

function iscsi_client_sessions(){
$unix=new unix();
	$iscsiadm=$unix->find_program("iscsiadm");
	$cmd="$iscsiadm -m session 2>&1";
	exec($cmd,$results);
	writelogs_framework("$cmd = ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	$array=array();
	while (list ($index, $line) = each ($results)){
		if(!preg_match("#([0-9\.]+):([0-9]+),([0-9]+)\s+(.+?):(.+)#",$line,$re)){continue;}
		$array[$re[1]][]=array("PORT"=>$re[2],"ID"=>$re[3],"ISCSI"=>$re[4],"FOLDER"=>$re[5],"IP"=>$re[1]);
	}
	
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";	
	
}
function SysSyncPaquages(){
	$unix=new unix();
	$unix->THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.system.php --sys-paquages");	
	
}
function unix_groups(){
	$results=explode("\n",@file_get_contents("/etc/group"));
	$gp=array();
	while (list ($index, $line) = each ($results)){
		if(preg_match("#(.+?):.+?:([0-9]+):(.*?)#",$line,$re)){
			$gp[$re[2]]["NAME"]=$re[1];
			if($re[3]<>null){
				$gp[$re[2]]["MEMBERS"][]=$re[3];
			}
		}else{
			writelogs_framework("$line no match #(.+?):x:([0-9]+):(.*?)#",__FUNCTION__,__FILE__,__LINE__);
		}
		
	}
	
	echo "<articadatascgi>". base64_encode(serialize($gp))."</articadatascgi>";
}

function chmod_access(){
	$unix=new unix();
	$stat=$unix->find_program("stat");
	$_GET["chmod-access"]=$unix->shellEscapeChars($_GET["chmod-access"]);
	$cmd="$stat {$_GET["chmod-access"]} 2>&1";
	exec($cmd,$results);
	writelogs_framework("$cmd = ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	while (list ($index, $line) = each ($results)){
		if(preg_match("#Access:.+?\(([0-9]+)\/(.+?)\)\s+Uid:\s+\(.+?\/(.+?)\)\s+Gid:\s+\(.+?\/(.+?)\)#i",$line,$re)){
			writelogs_framework("$line MATCH",__FUNCTION__,__FILE__,__LINE__);
			echo "<articadatascgi>". base64_encode(serialize($re))."</articadatascgi>";
		}
	}
	
}

function acls_status(){
	$unix=new unix();
	$stat=$unix->find_program("stat");	
	$getfacl=$unix->find_program("getfacl");	
	$dir=$unix->shellEscapeChars($_GET["acls-status"]);
	$cmd="$stat $dir 2>&1";
	exec($cmd,$results);
	writelogs_framework("$cmd = ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	if(is_file($getfacl)){
	$cmd="$getfacl $dir 2>&1";
	$results[]="";
	$results[]="#HR#";
	exec($cmd,$results);	
	writelogs_framework("$cmd = ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	}
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
	
}

function acls_apply(){
	$unix=new unix();
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.acls.php --acls-single {$_GET["acls-apply"]}";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	$unix->THREAD_COMMAND_SET($cmd);	
}
function acls_delete(){
	$unix=new unix();
	$setfacl=$unix->find_program("setfacl");	
	$dir=$unix->shellEscapeChars($_GET["acls-delete"]);	
	$cmd="$setfacl -b $dir 2>&1";
	exec("$cmd",$events);	
}
function acls_rebuild(){
	$unix=new unix();
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.acls.php --acls";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	$unix->THREAD_COMMAND_SET($cmd);	
}
function ufdbguard_compile_missing_dbs(){
	@file_put_contents("/etc/artica-postfix/ufdbguard.compile.missing.alldbs","#");
		
	
}
function ufdbguard_compile_all_dbs(){
	
	@file_put_contents("/etc/artica-postfix/ufdbguard.compile.alldbs","#");
	
}
function ufdbguard_compile_schedule(){
	$unix=new unix();
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.squidguard.php --ufdbguard-schedule";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	$unix->THREAD_COMMAND_SET($cmd);	
	
}
function ufdbguard_compilator_events(){
	$unix=new unix();
	$tail=$unix->find_program("tail");
	$cmd="$tail -n 300 /var/log/artica-postfix/ufdbguard-compilator.debug";
	exec($cmd,$results);
	writelogs_framework("$cmd=".count($results),__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	
}

function snort_networks(){
	$cmd=LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.snort.php --networks";
	shell_exec($cmd);
}

function restart_snort(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim($nohup." /etc/init.d/artica-postfix restart snort >/dev/null 2>&1 &");
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);	
	shell_exec($cmd);		
}






?>
