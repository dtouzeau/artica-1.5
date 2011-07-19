<?php
include_once(dirname(__FILE__).'/class.ini.inc');
include_once(dirname(__FILE__).'/class.sockets.inc');
include_once(dirname(__FILE__).'/class.mysql.inc');
include_once(dirname(__FILE__).'/class.users.menus.inc');
include_once(dirname(__FILE__).'/class.ldap.inc');
include_once(dirname(__FILE__).'/class.user.inc');
include_once(dirname(__FILE__).'/class.groups.inc');


class obm{
	var $OBMEnabled;
	var $apache_listen;
	var $httpd_conf;
	var $obmini;
	var $external_url;
	var $external_protocol;
	var $ObmOBMSyncCron;
	var $ObMSyncPool;
	var $dn;
	function obm(){
		$this->BuildTree();
		$this->LoadConf();
		
		
		
		
	}
	
	
	function BuildTree(){
		$ldap=new clladp();
		$dn="ou=sysusers,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
			$upd["objectClass"][]="organizationalUnit";
			$upd["objectClass"][]="top";
			$upd["ou"][]="sysusers";
			$ldap->ldap_add($dn,$upd);
			unset($upd);
		}
		
		
		$dn="uid=ldapadmin,ou=sysusers,$ldap->suffix";		
		if(!$ldap->ExistsDN($dn)){
			$upd["objectClass"][]="simpleSecurityObject";
			$upd["objectClass"][]="account";
			$upd["objectClass"][]="top";
			$upd["uid"][]="ldapadmin";
			$upd["userPassword"][]="mdp3PaAL";
			$ldap->ldap_add($dn,$upd);
			unset($upd);
		}			
			
		}
		
	function LoadConf(){
		$sock=new sockets();
		$this->httpd_conf=$sock->GET_INFO("OBMApacheFile");
		$this->obmini=$sock->GET_INFO("OBMConfIni");
		$this->OBMEnabled=$sock->GET_INFO("OBMEnabled");
		$this->OBMSyncCron=$sock->GET_INFO('OBMSyncCron');
		if($this->OBMSyncCron==null){$this->OBMSyncCron="2h";}
		if($this->httpd_conf==null){$this->httpd_conf=$this->apacheConf();}
		if($this->obmini==null){$this->obmini=$this->obm_ini();}
		
		$tb=explode("\n",$this->httpd_conf);
		if(is_array($tb)){
			while (list ($num, $val) = each ($tb) ){
				if(preg_match('#server.port[\s+=]([0-9]+)#',$val,$r)){
					$this->apache_listen=$r[1];
				}
			}
		}
		
		
		
		
		$ini=new Bs_IniHandler();
		$ini->loadString($this->obmini);
		$this->external_protocol=$ini->_params["global"]["external-protocol"];
		$this->external_url=$ini->_params["global"]["external-url"];
		if($this->apache_listen==null){$this->apache_listen=447;}
	
		}
		
	function apacheConf(){
		$user=new usersMenus();
		if($this->apache_listen==null){$this->apache_listen=447;}
			$conf=$conf ."#artica-postfix saved by artica lighttpd-obm.conf\n";
			$conf=$conf ."\n";
			$conf=$conf ."server.modules = (\n";
			$conf=$conf ."\t\"mod_alias\",\n";
			$conf=$conf ."\t\"mod_access\",\n";
			$conf=$conf ."\t\"mod_accesslog\",\n";
			$conf=$conf ."\t\"mod_compress\",\n";
			$conf=$conf ."\t\"mod_fastcgi\",\n";
			$conf=$conf ."\t\"mod_status\",\n";
			$conf=$conf ."\t\"mod_setenv\" )\n";
			$conf=$conf ."\n";
			$conf=$conf ."server.document-root        = \"/usr/share/obm/php\"\n";
			$conf=$conf ."server.username = \"www-data\"\n";
			$conf=$conf ."server.groupname = \"www-data\"\n";
			$conf=$conf ."server.errorlog             = \"/var/log/lighttpd/obm-error.log\"\n";
			$conf=$conf ."index-file.names            = ( \"index.php\")\n";
			$conf=$conf ."\n";
			$conf=$conf ."mimetype.assign             = (\n";
			$conf=$conf ."\t\".pdf\"          =>      \"application/pdf\",\n";
			$conf=$conf ."\t\".sig\"          =>      \"application/pgp-signature\",\n";
			$conf=$conf ."\t\".spl\"          =>      \"application/futuresplash\",\n";
			$conf=$conf ."\t\".class\"        =>      \"application/octet-stream\",\n";
			$conf=$conf ."\t\".ps\"           =>      \"application/postscript\",\n";
			$conf=$conf ."\t\".torrent\"      =>      \"application/x-bittorrent\",\n";
			$conf=$conf ."\t\".dvi\"          =>      \"application/x-dvi\",\n";
			$conf=$conf ."\t\".gz\"           =>      \"application/x-gzip\",\n";
			$conf=$conf ."\t\".pac\"          =>      \"application/x-ns-proxy-autoconfig\",\n";
			$conf=$conf ."\t\".swf\"          =>      \"application/x-shockwave-flash\",\n";
			$conf=$conf ."\t\".tar.gz\"       =>      \"application/x-tgz\",\n";
			$conf=$conf ."\t\".tgz\"          =>      \"application/x-tgz\",\n";
			$conf=$conf ."\t\".tar\"          =>      \"application/x-tar\",\n";
			$conf=$conf ."\t\".zip\"          =>      \"application/zip\",\n";
			$conf=$conf ."\t\".mp3\"          =>      \"audio/mpeg\",\n";
			$conf=$conf ."\t\".m3u\"          =>      \"audio/x-mpegurl\",\n";
			$conf=$conf ."\t\".wma\"          =>      \"audio/x-ms-wma\",\n";
			$conf=$conf ."\t\".wax\"          =>      \"audio/x-ms-wax\",\n";
			$conf=$conf ."\t\".ogg\"          =>      \"application/ogg\",\n";
			$conf=$conf ."\t\".wav\"          =>      \"audio/x-wav\",\n";
			$conf=$conf ."\t\".gif\"          =>      \"image/gif\",\n";
			$conf=$conf ."\t\".jar\"          =>      \"application/x-java-archive\",\n";
			$conf=$conf ."\t\".jpg\"          =>      \"image/jpeg\",\n";
			$conf=$conf ."\t\".jpeg\"         =>      \"image/jpeg\",\n";
			$conf=$conf ."\t\".png\"          =>      \"image/png\",\n";
			$conf=$conf ."\t\".xbm\"          =>      \"image/x-xbitmap\",\n";
			$conf=$conf ."\t\".xpm\"          =>      \"image/x-xpixmap\",\n";
			$conf=$conf ."\t\".xwd\"          =>      \"image/x-xwindowdump\",\n";
			$conf=$conf ."\t\".css\"          =>      \"text/css\",\n";
			$conf=$conf ."\t\".html\"         =>      \"text/html\",\n";
			$conf=$conf ."\t\".htm\"          =>      \"text/html\",\n";
			$conf=$conf ."\t\".js\"           =>      \"text/javascript\",\n";
			$conf=$conf ."\t\".asc\"          =>      \"text/plain\",\n";
			$conf=$conf ."\t\".c\"            =>      \"text/plain\",\n";
			$conf=$conf ."\t\".cpp\"          =>      \"text/plain\",\n";
			$conf=$conf ."\t\".log\"          =>      \"text/plain\",\n";
			$conf=$conf ."\t\".conf\"         =>      \"text/plain\",\n";
			$conf=$conf ."\t\".text\"         =>      \"text/plain\",\n";
			$conf=$conf ."\t\".txt\"          =>      \"text/plain\",\n";
			$conf=$conf ."\t\".dtd\"          =>      \"text/xml\",\n";
			$conf=$conf ."\t\".xml\"          =>      \"text/xml\",\n";
			$conf=$conf ."\t\".mpeg\"         =>      \"video/mpeg\",\n";
			$conf=$conf ."\t\".mpg\"          =>      \"video/mpeg\",\n";
			$conf=$conf ."\t\".mov\"          =>      \"video/quicktime\",\n";
			$conf=$conf ."\t\".qt\"           =>      \"video/quicktime\",\n";
			$conf=$conf ."\t\".avi\"          =>      \"video/x-msvideo\",\n";
			$conf=$conf ."\t\".asf\"          =>      \"video/x-ms-asf\",\n";
			$conf=$conf ."\t\".asx\"          =>      \"video/x-ms-asf\",\n";
			$conf=$conf ."\t\".wmv\"          =>      \"video/x-ms-wmv\",\n";
			$conf=$conf ."\t\".bz2\"          =>      \"application/x-bzip\",\n";
			$conf=$conf ."\t\".tbz\"          =>      \"application/x-bzip-compressed-tar\",\n";
			$conf=$conf ."\t\".tar.bz2\"      =>      \"application/x-bzip-compressed-tar\",\n";
			$conf=$conf ."\t\"\"              =>      \"application/octet-stream\",\n";
			$conf=$conf ." )\n";
			$conf=$conf ."\n";
			$conf=$conf ."\n";
			$conf=$conf ."accesslog.filename          = \"/var/log/lighttpd/obm-access.log\"\n";
			$conf=$conf ."url.access-deny             = ( \"~\", \".inc\" )\n";
			$conf=$conf ."\n";
			$conf=$conf ."setenv.add-environment = ( \"OBM_INCLUDE_VAR\"=>\"obminclude\")\n";
			$conf=$conf ."#php_value include_path \".:/usr/share/obm\"\n";
			$conf=$conf ."#php_value session.bug_compat_42 1\n";
			$conf=$conf ."#php_value session.bug_compat_warn 0\n";
			$conf=$conf ."\n";
			$conf=$conf ."static-file.exclude-extensions = ( \".php\", \".pl\", \".fcgi\" )\n";
			$conf=$conf ."server.port                 = $this->apache_listen\n";
			$conf=$conf ."#server.bind                = \"127.0.0.1\"\n";
			$conf=$conf ."#server.error-handler-404   = \"/error-handler.html\"\n";
			$conf=$conf ."#server.error-handler-404   = \"/error-handler.php\"\n";
			$conf=$conf ."server.pid-file             = \"/var/run/lighttpd/lighttpd-obm.pid\"\n";
			$conf=$conf ."server.max-fds \t\t    = 2048\n";
			$conf=$conf ."\n";
			$conf=$conf ."fastcgi.server = ( \".php\" =>((\n";
			$conf=$conf ."\t\t\"bin-path\" => \"/usr/bin/php-cgi -c /etc/php5/cgi/php.ini\",\n";
			$conf=$conf ."\t\t\"socket\" => \"/var/run/lighttpd/php.socket\",\n";
			$conf=$conf ."\n";
			$conf=$conf ."\t\t\"min-procs\" => 1,\n";
			$conf=$conf ."\t\t\"max-procs\" => 2,\n";
			$conf=$conf ."\t\t\"max-load-per-proc\" => 2,\n";
			$conf=$conf ."\t\t\"idle-timeout\" => 10,\n";
			$conf=$conf ."\t\t \"bin-environment\" => (\n";
			$conf=$conf ."\t\t\t\"PHP_FCGI_CHILDREN\" => \"40\",\n";
			$conf=$conf ."\t\t\t\"PHP_FCGI_MAX_REQUESTS\" => \"100\"\n";
			$conf=$conf ."\t\t),\n";
			$conf=$conf ."\t\t\"bin-copy-environment\" => (\n";
			$conf=$conf ."\t\t\t\"PATH\", \"SHELL\", \"USER\"\n";
			$conf=$conf ."\t\t),\n";
			$conf=$conf ."\t\t\"broken-scriptfilename\" => \"enable\"\n";
			$conf=$conf ."\t))\n";
			$conf=$conf .")\n";
			$conf=$conf ."ssl.engine                 = \"enable\"\n";
			$conf=$conf ."ssl.pemfile                = \"/opt/artica/ssl/certs/lighttpd.pem\"\n";
			$conf=$conf ."status.status-url          = \"/server-status\"\n";
			$conf=$conf ."status.config-url          = \"/server-config\"\n";
			$conf=$conf ."\n";
			$conf=$conf ."\n";
			$conf=$conf ."alias.url += ( \"/images/\" => \"/usr/share/obm/resources/\" )\n";
			$conf=$conf ."alias.url += ( \"/cgi-bin/\" => \"/usr/lib/cgi-bin/\" )\n";
			return $conf;
		
		
	}
	
	function SaveToLdap(){
		$sock=new sockets();
		$sock->SaveConfigFile($this->apacheConf(),"OBMApacheFile");
		$sock->SaveConfigFile($this->obm_ini(),"OBMConfIni");
		$sock->SaveConfigFile($this->obm_inc(),"OBMConfInc");
		$sock->SET_INFO("OBMSyncCron",$this->OBMSyncCron);
		$sock->SET_INFO("OBMEnabled",$this->OBMEnabled);
		$sock->getfile('obm_settings');
		}
	
	function obm_ini(){
			$ldap=new clladp();
			$users=new  usersMenus();
			if($this->external_protocol==null){$this->external_protocol='https';}
			
			$conf=$conf.";OBM system configuration file\n";
			$conf=$conf."; \n";
			$conf=$conf."[global]\n";
			$conf=$conf."title = OBM\n";
			$conf=$conf."\n";
			$conf=$conf."; example : for https://extranet.aliasource.fr/obm/ \n";
			$conf=$conf."; external-url = extranet.aliasource.fr\n";
			$conf=$conf."; external-protocol = $this->external_protocol\n";
			$conf=$conf."; obm-prefix = /obm/\n";
			$conf=$conf."external-url = $this->external_url\n";
			$conf=$conf."external-protocol = $this->external_protocol\n";
			$conf=$conf."obm-prefix = /\n";
			$conf=$conf."\n";
			$conf=$conf."; Database infos\n";
			$conf=$conf."host = $users->mysql_server:$users->mysql_port\n";
			$conf=$conf."dbtype = MYSQL\n";
			$conf=$conf."db = obm\n";
			$conf=$conf."user = $users->mysql_admin\n";
			$conf=$conf."password = \"$users->mysql_password\"\n";
			$conf=$conf."\n";
			$conf=$conf."; Default language\n";
			$conf=$conf."lang = en\n";
			$conf=$conf."\n";
			$conf=$conf."; Modules OBM fonctionnels\n";
			$conf=$conf."obm-ldap = false\n";
			$conf=$conf."obm-mail = true\n";
			$conf=$conf."obm-samba = false\n";
			$conf=$conf."obm-web = false\n";
			$conf=$conf."\n";
			$conf=$conf."; login are 'login' and not 'login@domain'\n";
			$conf=$conf."singleNameSpace = false\n";
			$conf=$conf."\n";
			$conf=$conf."renameUserMailbox = false\n";
			$conf=$conf."\n";
			$conf=$conf."backupRoot = \"/opt/artica/backup/obm\"\n";
			$conf=$conf."\n";
			$conf=$conf."\n";
			$conf=$conf."[automate]\n";
			$conf=$conf."; Automate specific parameters\n";
			$conf=$conf."\n";
			$conf=$conf."; Log level\n";
			$conf=$conf."logLevel = 2\n";
			$conf=$conf."\n";
			$conf=$conf."; LDAP server address\n";
			$conf=$conf."ldapServer =\"\"\n";
			$conf=$conf."\n";
			$conf=$conf."; LDAP Root\n";
			$conf=$conf."; Exemple : 'aliasource,local' means that the root DN is: 'dc=aliasource,dc=local' \n";
			$conf=$conf."ldapRoot =\n";
			$conf=$conf."\n";
			$conf=$conf."; Enable Cyrus partition support\n";
			$conf=$conf."; if cyrusPartition is enable, a dedicated Cyrus partition is created for each OBM domain\n";
			$conf=$conf."; Going cyrusPartition enabled from cyrusPartition disabled needs system work\n";
			$conf=$conf."cyrusPartition = true\n";
			$conf=$conf."\n";
			$conf=$conf."oldSidMapping = false\n";
			return $conf;
	}
	
	function obm_inc(){
		
		$conf=$conf."<script language=\"php\">\n";
		$conf=$conf."///////////////////////////////////////////////////////////////////////////////\n";
		$conf=$conf."// OBM - File : obm_conf.inc.sample                                          //\n";
		$conf=$conf."//     - Desc : OBM specific site configuration Sample file                  //\n";
		$conf=$conf."// 2005-04-04 Pierre Baudracco                                               //\n";
		$conf=$conf."///////////////////////////////////////////////////////////////////////////////\n";
		$conf=$conf."// "."$"."Id: obm_conf.inc.sample 2766 2008-02-25 15:57:44Z anthony "."$"." //\n";
		$conf=$conf."///////////////////////////////////////////////////////////////////////////////\n";
		$conf=$conf."\n";
		$conf=$conf."\n";
		$conf=$conf."\n";
		$conf=$conf."// Global parameters --------------------------------------------------------//\n";
		$conf=$conf."\n";
		$conf=$conf."// OBM host (for mail links)\n";
		$conf=$conf."// CGP_HOST IS DEPRECATED. See external-url,..\n";
		$conf=$conf."\n";
		$conf=$conf."// Resources alias absolute path. (default value is '/images')\n";
		$conf=$conf."// "."$"."resources_path = \""."$"."cgp_host/images\"; \n";
		$conf=$conf."\n";
		$conf=$conf."// Home page redirection\n";
		$conf=$conf."//"."$"."c_home_redirect = \""."$"."cgp_host\" . 'calendar/calendar_index.php';\n";
		$conf=$conf."\n";
		$conf=$conf."// Use specific langs (was "."$"."cgp_site_include)\n";
		$conf=$conf."//"."$"."conf_lang = true;\n";
		$conf=$conf."\n";
		$conf=$conf."// Use specific check controls (see conf/modules/module.inc)\n";
		$conf=$conf."//"."$"."conf_modules = true;\n";
		$conf=$conf."\n";
		$conf=$conf."// Todo lines to display\n";
		$conf=$conf.""."$"."cgp_todo_nb = 5;\n";
		$conf=$conf."\n";
		$conf=$conf."// Maximum rows allowed to display\n";
		$conf=$conf.""."$"."conf_display_max_rows = 200;\n";
		$conf=$conf."\n";
		$conf=$conf."// Allow * in search field\n";
		$conf=$conf.""."$"."cgp_sql_star = true;\n";
		$conf=$conf."// archive checkbox return only archived results\n";
		$conf=$conf."//"."$"."cgp_archive_only = true;\n";
		$conf=$conf."\n";
		$conf=$conf."// Tuning constants\n";
		$conf=$conf.""."$"."ctu_sql_limit = true;\n";
		$conf=$conf."\n";
		$conf=$conf."// MySQL (only 4.1+) charset used (for database and files !)\n";
		$conf=$conf."//"."$"."cmy_character_set = '_latin1';\n";
		$conf=$conf."\n";
		$conf=$conf."// MySQL (only 4.1+) charset collation used (for database and files !)\n";
		$conf=$conf."//"."$"."cmy_charset_collation = 'latin1_general_ci';\n";
		$conf=$conf."\n";
		$conf=$conf."// is Mail enabled for OBM ? (eg: calendar)\n";
		$conf=$conf.""."$"."cgp_mail_enabled = true;\n";
		$conf=$conf."\n";
		$conf=$conf."// is Demo enabled (login screen array with default account)\n";
		$conf=$conf.""."$"."cgp_demo_enabled = false;\n";
		$conf=$conf."\n";
		$conf=$conf."// Session Cookie lifetime (in seconds, 0=session cookie)\n";
		$conf=$conf.""."$"."cs_lifetime = 0;\n";
		$conf=$conf."\n";
		$conf=$conf."// Use Database sessions\n";
		$conf=$conf.""."$"."cgp_sess_db = false;\n";
		$conf=$conf."//"."$"."cgp_sess_db = true;\n";
		$conf=$conf."\n";
		$conf=$conf."// Cookie name and domain\n";
		$conf=$conf.""."$"."cgp_cookie_name = 'OBM_Session';\n";
		$conf=$conf.""."$"."cgp_cookie_domain = 'aliacom.local';\n";
		$conf=$conf."\n";
		$conf=$conf."// authentification : 'CAS' (SSO AliaSuite) or 'standalone' (default)\n";
		$conf=$conf."//"."$"."auth_kind = 'CAS';\n";
		$conf=$conf."//"."$"."cas_server = 'sso.aliacom.local';\n";
		$conf=$conf."//"."$"."cas_server_port = 8443;\n";
		$conf=$conf."//"."$"."cas_server_uri = '';\n";
		$conf=$conf."\n";
		$conf=$conf."// encyption used to store password : 'PLAIN', 'MD5SUM' or 'CRYPT'\n";
		$conf=$conf.""."$"."password_encryption = 'PLAIN';\n";
		$conf=$conf."\n";
		$conf=$conf."// Modules specific parameters ----------------------------------------------//\n";
		$conf=$conf."\n";
		$conf=$conf."// Company and Contact : Copy address to clipboard\n";
		$conf=$conf.""."$"."cclipboard_address = array ('title', 'address1' , 'address2', 'address3', 'zipcode', 'town', 'expresspostal', 'country', 'phone', 'fax', 'mobile', 'email', 'web');\n";
		$conf=$conf."\n";
		$conf=$conf."// Company : Auto format fields\n";
		$conf=$conf.""."$"."caf_company_name = true;\n";
		$conf=$conf.""."$"."caf_town = true;\n";
		$conf=$conf."\n";
		$conf=$conf."// Company + Contact : Advanced search enabled by default\n";
		$conf=$conf.""."$"."csearch_advanced_default = false;\n";
		$conf=$conf."\n";
		$conf=$conf."// Contact : is mailing enabled by default for a new contact\n";
		$conf=$conf.""."$"."cgp_mailing_default = true;\n";
		$conf=$conf."\n";
		$conf=$conf."// Contact : is a new contact private by default\n";
		$conf=$conf.""."$"."ccontact_private_default = true;\n";
		$conf=$conf."\n";
		$conf=$conf."// Calendar : show public groups\n";
		$conf=$conf.""."$"."ccalendar_public_groups = true;\n";
		$conf=$conf."\n";
		$conf=$conf."// Calendar: generic E-mail resource admin for calendar module\n";
		$conf=$conf."//"."$"."ccalendar_resource_admin = 'resource@mydomain';\n";
		$conf=$conf."\n";
		$conf=$conf."// Calendar first ay of week, first and last hour display\n";
		$conf=$conf."//"."$"."ccalendar_weekstart = 'monday';\n";
		$conf=$conf.""."$"."ccalendar_first_hour = 8;\n";
		$conf=$conf.""."$"."ccalendar_last_hour = 20;\n";
		$conf=$conf."\n";
		$conf=$conf."// Calendar max user displayed in the calendar\n";
		$conf=$conf."//"."$"."ccalendar_max_users = 25;\n";
		$conf=$conf."\n";
		$conf=$conf."// Calendar Resource usage\n";
		$conf=$conf.""."$"."ccalendar_resource = true;\n";
		$conf=$conf."\n";
		$conf=$conf."// Calendar send ics file\n";
		$conf=$conf.""."$"."ccalendar_send_ics = true;\n";
		$conf=$conf."\n";
		$conf=$conf."// Calendar hour fraction (minutes select granularity)\n";
		$conf=$conf.""."$"."ccalendar_hour_fraction = 4;\n";
		$conf=$conf."\n";
		$conf=$conf."// Generic E-mail for Incident module\n";
		$conf=$conf."//"."$"."cmail_incident = 'support@mydomain';\n";
		$conf=$conf."\n";
		$conf=$conf."// Document : Root of the document repository\n";
		$conf=$conf.""."$"."cdocument_root = '/usr/share/obm';\n";
		$conf=$conf.""."$"."default_path = '/Default';                  // Default directory\n";
		$conf=$conf."\n";
		$conf=$conf."// Group : is a new group private by default\n";
		$conf=$conf.""."$"."cgroup_private_default = true;\n";
		$conf=$conf."\n";
		$conf=$conf."// default taxes values\n";
		$conf=$conf.""."$"."cdefault_tax = array ('TVA 19,6' => 1.196, 'TVA 5,5' => 1.055, 'Pas de TVA' => 1);\n";
		$conf=$conf."\n";
		$conf=$conf."//----------------------//\n";
		$conf=$conf."// Profiles definitions //\n";
		$conf=$conf."//----------------------//\n";
		$conf=$conf."\n";
		$conf=$conf.""."$"."profiles['user']['level'] = 4;\n";
		$conf=$conf.""."$"."profiles['user']['module']['mailbox'] = "."$"."perm_editor;\n";
		$conf=$conf.""."$"."profiles['editor'] = array (\n";
		$conf=$conf."  'section' => array (\n";
		$conf=$conf."    'default' => 0,\n";
		$conf=$conf."    'com' => 1,\n";
		$conf=$conf."    'prod' => 1,\n";
		$conf=$conf."    'user' => 1,\n";
		$conf=$conf."    'my' => 1),\n";
		$conf=$conf."  'module' => array (\n";
		$conf=$conf."    'default' => "."$"."perm_editor),\n";
		$conf=$conf."  'level' => 3,\n";
		$conf=$conf."  'access_restriction' => 'ALLOW_ALL',\n";
		$conf=$conf."  'access_exeptions' => array()\n";
		$conf=$conf.");\n";
		$conf=$conf.""."$"."profiles['admin_delegue'] = array (\n";
		$conf=$conf."  'section' => array (\n";
		$conf=$conf."    'default' => 1),\n";
		$conf=$conf."  'module' => array (\n";
		$conf=$conf."    'default' => "."$"."perm_admin,\n";
		$conf=$conf."    'domain' => 0),\n";
		$conf=$conf."  'properties' => array (\n";
		$conf=$conf."    'admin_realm' => array ('user', 'delegation')\n";
		$conf=$conf."    ),\n";
		$conf=$conf."  'level' => 2,\n";
		$conf=$conf."  'level_managepeers' => 1,\n";
		$conf=$conf."  'access_restriction' => 'ALLOW_ALL',\n";
		$conf=$conf."  'access_exeptions' => array()\n";
		$conf=$conf.");\n";
		$conf=$conf."\n";
		$conf=$conf.""."$"."profiles['admin'] = array (\n";
		$conf=$conf."  'section' => array (\n";
		$conf=$conf."    'default' => 1),\n";
		$conf=$conf."  'module' => array (\n";
		$conf=$conf."    'default' => "."$"."perm_admin,\n";
		$conf=$conf."    'domain' => 0),\n";
		$conf=$conf."  'properties' => array (\n";
		$conf=$conf."    'admin_realm' => array ('user', 'delegation', 'domain')\n";
		$conf=$conf."    ),\n";
		$conf=$conf."  'level' => 1,\n";
		$conf=$conf."  'level_managepeers' => 1,\n";
		$conf=$conf."  'access_restriction' => 'ALLOW_ALL',\n";
		$conf=$conf."  'access_exeptions' => array()\n";
		$conf=$conf.");\n";
		$conf=$conf."\n";
		$conf=$conf."//-----------------//\n";
		$conf=$conf."// Displayed Infos //\n";
		$conf=$conf."//-----------------//\n";
		$conf=$conf."// sections -- Default is true\n";
		$conf=$conf."//"."$"."cgp_show['section'] = ''; // Needed if module order to change\n";
		$conf=$conf."//"."$"."cgp_show['section']['com']['url'] = \""."$"."path/calendar/calendar_index.php\";\n";
		$conf=$conf."//"."$"."cgp_show['section']['webmail']['url'] = 'http://webmail';\n";
		$conf=$conf."//"."$"."cgp_show['section']['webmail']['target'] = '_blanck';\n";
		$conf=$conf."\n";
		$conf=$conf."// modules -- false to hide, otherwise section\n";
		$conf=$conf."//"."$"."cgp_show['module'] = ''; // Needed if module order to change\n";
		$conf=$conf."//"."$"."cgp_show['module']['company'] = 'com';\n";
		$conf=$conf."\n";
		$conf=$conf."// References fields\n";
		$conf=$conf."//"."$"."cgp_hide['']['region'] = true;\n";
		$conf=$conf."\n";
		$conf=$conf."// Company module\n";
		$conf=$conf."//"."$"."cgp_hide['company']['company_number'] = true;\n";
		$conf=$conf."//"."$"."cgp_hide['company']['company_vat'] = true;\n";
		$conf=$conf."//"."$"."cgp_hide['company']['company_siret'] = true;\n";
		$conf=$conf."//"."$"."cgp_hide['company']['type'] = true;\n";
		$conf=$conf."//"."$"."cgp_hide['company']['activity'] = true;\n";
		$conf=$conf."//"."$"."cgp_hide['company']['nafcode'] = true;\n";
		$conf=$conf."//"."$"."cgp_hide['company']['company_address3'] = true;\n";
		$conf=$conf."//"."$"."cgp_hide['company']['company_expresspostal'] = true;\n";
		$conf=$conf."\n";
		$conf=$conf."// Contact module\n";
		$conf=$conf."//"."$"."cgp_hide['contact']['function'] = true;\n";
		$conf=$conf."//"."$"."cgp_hide['contact']['contact_title'] = true;\n";
		$conf=$conf."//"."$"."cgp_hide['contact']['responsible'] = true;\n";
		$conf=$conf."//"."$"."cgp_hide['contact']['contact_service'] = true;\n";
		$conf=$conf."//"."$"."cgp_hide['contact']['contact_comment2'] = true;\n";
		$conf=$conf."//"."$"."cgp_hide['contact']['contact_comment3'] = true;\n";
		$conf=$conf."//"."$"."cgp_hide['contact']['contact_date'] = true;\n";
		$conf=$conf."\n";
		$conf=$conf."//--------------------//\n";
		$conf=$conf."// User specific data //\n";
		$conf=$conf."//--------------------//\n";
		$conf=$conf."// Categories available for modules :\n";
		$conf=$conf."// company, contact, incident, document\n";
		$conf=$conf."// Lang var definitions here for example : Put these in conf/lang/\n";
		$conf=$conf."//"."$"."l_companycategory1 = 'Category1';\n";
		$conf=$conf."//"."$"."cgp_user['company']['category']['companycategory1'] = array('mode'=>'multi');\n";
		$conf=$conf."//"."$"."l_contactcategory1 = 'Contact category1';\n";
		$conf=$conf."//"."$"."cgp_user['contact']['category']['contactcategory1'] = array('mode'=>'multi');\n";
		$conf=$conf."//"."$"."l_contactcategory2 = 'Contact category2';\n";
		$conf=$conf."//"."$"."cgp_user['contact']['category']['contactcategory2'] = array('mode'=>'multi');\n";
		$conf=$conf."//"."$"."l_documentcategory1 = 'Doc cat1';\n";
		$conf=$conf."//"."$"."cgp_user['document']['category']['documentcategory1'] = array('mode'=>'mono');\n";
		$conf=$conf."\n";
		$conf=$conf."// AliaSuite : Set if there is a link for Aliamin, Blogs and Aliacms\n";
		$conf=$conf."//"."$"."aliamin_link = 'http://aliamin.aliacom.local/';\n";
		$conf=$conf."//"."$"."aliacms_link = 'http://cms.aliacom.local/';\n";
		$conf=$conf."//"."$"."blogs_link = 'http://blogs.aliacom.local/';\n";
		$conf=$conf."\n";
		$conf=$conf."</script>";		
		return $conf;
		
	}
	
	
}


class obm_export_single{
	var $database="obm";
	var $uid;
	var $mail;
	var $ou;
	var $userclass;
	var $OBM_INSTALLED;
	var $OBMEnabled;
	var $IsOBM=false;
	
	function obm_export_single($uid){
		include_once(dirname(__FILE__).'/class.user.inc');
		$this->uid=$uid;
		$users=new usersMenus();
		$users->LoadModulesEnabled();
		$this->OBM_INSTALLED=$users->OBM_INSTALLED;
		$this->OBMEnabled=$users->OBMEnabled;
		
		
	}
	
	function CheckOBM(){
		if($this->OBM_INSTALLED){
			if($this->OBMEnabled<>1){
				writelogs("OBM is installed but not enabled",__CLASS__.'/'.__FUNCTION__,__FILE__);
				return false;}else{
					writelogs("OBM is installed and enabled",__CLASS__.'/'.__FUNCTION__,__FILE__);
				}{
					return true;
				}
		}else{
			writelogs("OBM is not installed",__CLASS__.'/'.__FUNCTION__,__FILE__);
		}
		return false;
	}
	
	function IsUserExists($uidNumber){
		$obmex=new obm_export(1);
		$res=$obmex->UserObmExists($uidNumber);
		if($res){
			writelogs("$uidNumber This user is an OBM user",__CLASS__.'/'.__FUNCTION__,__FILE__);
			}
	
			return $res;
	}
	
	
	function Export(){
		if(!$this->CheckOBM()){return null;}
		$obmex=new obm_export(1);
		$user=new user($this->uid);
		$this->userclass=$user;
		$this->mail=$user->mail;
		$this->ou=$user->ou;
		if($obmex->UserObmExists($user->uidNumber)){
			writelogs("$user->uidNumber ($user->uid) exists update infos..",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$this->UpdateUser();
		}else{
			writelogs("$user->uidNumber ($user->uid) does not exists add infos..",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$this->AddUser();
		}
		
		
	}
	
	function CreateManager($admin,$adminpassword){
		$obmex=new obm_export(1);
		$date=date('Y-m-d H:i:s');
		if($obmex->UserObmExists(1)){
			$sql="UPDATE `obm`.`UserObm` SET `userobm_login`='$admin',
			`userobm_timeupdate`='$date',
			`userobm_password`='$adminpassword',
			`userobm_domain_id`='0',
			`userobm_gid`='512',
			`userobm_perms`='admin' 
			WHERE `UserObm`.`userobm_id`=1 LIMIT 1";
			$q=new mysql();
			$q->QUERY_SQL($sql,'obm');
		}else{
		
		$sql="INSERT INTO UserObm (userobm_domain_id,
		 userobm_login,
		 userobm_password,
		 userobm_password_type,
		 userobm_perms, 
		 userobm_lastname,
		 userobm_firstname,
		 userobm_uid,
		 userobm_gid) VALUES (0, '$admin','$adminpassword','PLAIN','admin',
		 'Artica Manager', 'SuperAdmin', '1', '512');";
		$q=new mysql();
		$q->QUERY_SQL($sql,'obm');
		}
		
		
	}
	
	
	function GetUserDomain(){
		$obmex=new obm_export(1);
		$domains=$obmex->GetOudomains($this->ou);
		$domain=$domains[0];
		if(trim($domain)==null){
			if(preg_match('#(.+?)@(.+)#',$this->mail,$r)){$domain=$r[1];}
		}
		
		return array($domain,$domains[1]);
		}
	
	
	function CheckDomainID(){
		$domains=$this->GetUserDomain();
		$domain=$domains[0];
		$aliases=$domains[1];		
		$obmex=new obm_export(1);
		$domain_id=$obmex->GetobmDomainId($domain);
		if($domain_id==null){
			$obmex->AddNewObmDomain($this->ou,$domain,$aliases);
			$domain_id=$obmex->GetobmDomainId($domain);
		}
		return $domain_id;
	}
	
	
	function Adduser(){
		$obmex=new obm_export(1);
		$domain_id=$this->CheckDomainID($domain,$aliases);
		if($domain_id==null){
			echo "OBM:domain_id=null\n";
			exit;
		}
		writelogs("$this->uid organization:$this->ou domain_id:$domain_id",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$obmex->AddNewUserObm($this->userclass,$this->ou,$domain_id);
		}
		
	function UpdateUser(){
		$obmex=new obm_export(1);
		$domain_id=$this->CheckDomainID($domain,$aliases);
		writelogs("$this->uid organization:$this->ou domain_id:$domain_id",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$obmex->EditUserObm($this->userclass,$domain_id);
		}
	
	
	
	
	
}


class obm_export{
	var $database="obm";
	
	
	function obm_export($donothing=0,$obm_database="obm"){
			$this->database=$obm_database;
			if($donothing==0){
				$this->writelogs("Starting obm injection....");
				$this->export_organizations();
			}
			
		
		
		
		
	}
	
		function writelogs($text=null,$function=null){
		if($file_source==null){$file_source= __FILE__ ;}
		writelogs($text,$function,__FILE__);
		
		$file_source=basename($file_source);
		    $logFile="/var/log/artica-postfix/obm-synchro.log";
		    if(!is_dir(dirname($logFile))){mkdir(dirname($logFile));}
   		if (is_file($logFile)) { 
   			$size=filesize($logFile);
		    	if($size>500000){unlink($logFile);}
   		}
		 $logFile=str_replace("//","/",$logFile);
		$f = @fopen($logFile, 'a');
		$date=date("Y-m-d H:i:s");
		@fwrite($f, "$date:($file_source) [$function()]:: $text\n");
		@fclose($f);
				
		
	}
	
	
	function export_organizations(){
		$ldap=new clladp();
		$hash=$ldap->hash_get_ou(true);
		
		if(is_array($hash)){
			while (list ($organization, $val) = each ($hash) ){
			$this->writelogs("Checking $organization");
			$array=$this->GetOudomains($organization);
			if(strlen($array[0]==null)){continue;}
			$domain_id=$this->GetobmDomainId($array[0]);
			if(trim($domain_id)==null){
				$this->AddNewObmDomain($organization,$array[0],$array[1]);
				$domain_id=$this->GetobmDomainId($array[0]);
			}else{
				$this->EditObmDomain($domain_id,$organization,$array[0],$array[1]);
			}
			
			if($domain_id==null){
				$this->writelogs("Unable to create domain {$array[0]} for organization $organization");
				continue;
			}
			
			$this->export_users($organization,$domain_id);
			$this->export_groups($organization,$domain_id);
			$this->UpDateObmDomainState($domain_id);
			
		}
	}

	
	$this->writelogs("Success exporting LDAP database to OBM Mysql database....");
	}
	
	
	function GetOudomains($organization){
		$ldap=new clladp();
		$hash=$ldap->Hash_domains_table($organization);
		if(!is_array($hash)){return array();}
		while (list ($domains, $val) = each ($hash) ){
			$array[]=$domains;
			
		}
		$firstDomain=$array[0];
		unset($array[0]);
		return array($firstDomain,implode("\n",$array));
		
	}
	
	function GetobmDomainId($domain_name){
		$sql="SELECT domain_id FROM Domain WHERE domain_name='$domain_name'";
		$q=new mysql();
		if($this->database==null){$this->database='obm2';}
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,$this->database));
		return $ligne["domain_id"];
		}
		
	function UpDateObmDomainState($domain_id){
		$sql="INSERT INTO `obm`.`DomainPropertyValue` (
				`domainpropertyvalue_domain_id` ,
				`domainpropertyvalue_property_key` ,
				`domainpropertyvalue_value`
				)
				VALUES (
				'$domain_id', 'update_state', '1'
				);";
		$q=new mysql();
		$q->QUERY_SQL($sql,$this->database);}
		
	function AddNewObmDomain($organization,$firstdomain,$aliasesDomains){
		$date=date('Y-m-d h:I:s');
		
		
		
		$SQL="INSERT INTO `Domain` (`domain_timeupdate`, `domain_timecreate`, `domain_usercreate`, `domain_userupdate`, `domain_label`, `domain_description`, 
		`domain_name`, `domain_alias`, `domain_mail_server_id`) 
		VALUES ('$date', '$date', 0, NULL, '$organization', '', '$firstdomain', '$aliasesDomains', NULL);";
		
		if($this->database=="obm2"){
			$SQL="INSERT INTO `Domain` ( `domain_timeupdate`, `domain_timecreate`, `domain_usercreate`,
			 `domain_userupdate`, `domain_label`, `domain_description`, `domain_name`, `domain_alias`, `domain_global`) VALUES
			( '$date', '$date', NULL, NULL, '$organization', '$firstdomain for $organization organization', $firstdomain', '', 0);";			
		}
		
		$q=new mysql();
		$q->QUERY_SQL($SQL,$this->database);
		if(!$q->ok){echo $q->mysql_error;}
		
		}
		
	function AddNewObmGroup($GuidNumber,$domain_id){
		$date=date('Y-m-d h:I:s');
		$gp=new groups($GuidNumber);
		$this->writelogs("Adding group $GuidNumber into database....");
		$sql="INSERT INTO UGroup (group_id,group_domain_id,group_timeupdate,group_timecreate,group_local,group_gid,group_name,group_desc)
		VALUES($GuidNumber,$domain_id,'$date','$date',1,$GuidNumber,'$gp->groupName','');
		";
		$q=new mysql();
		$q->QUERY_SQL($sql,$this->database);
		}
		
		function AddOBMUsersToGroup($GuidNumber,$domain_id){
			$group=new groups($GuidNumber);
			while (list ($uid, $val) = each ($group->members_array) ){
				$user=new user($uid);
				if(!$this->UserAffectedGroupExists($user->uidNumber,$GuidNumber)){
					$this->writelogs("Affect $user->uid to group $GuidNumber");
					$sql="INSERT INTO UserObmGroup (userobmgroup_userobm_id,userobmgroup_group_id) VALUES('$user->uidNumber','$GuidNumber');";
					$q=new mysql();
					$q->QUERY_SQL($sql,$this->database);
				}
			
			}
			
			
			
		}
		
		function UserAffectedGroupExists($uidnumber,$gidnumber){
			$sql="SELECT userobmgroup_group_id FROM UserObmGroup WHERE userobmgroup_group_id=$gidnumber AND userobmgroup_userobm_id=$uidnumber";
			$q=new mysql();
			$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,$this->database));
		if(strlen($ligne["userobmgroup_group_id"])>0){
			$this->writelogs("$uidnumber Alredy affected to $gidnumber");
			return true;}
		
		$this->writelogs("$uidnumber not exists in $gidnumber ({$ligne["userobmgroup_group_id"]})",__FUNCTION__);
		return false;
			
			
		}
		
		
	function EditObmGroup($GuidNumber,$domain_id){
		$date=date('Y-m-d h:I:s');
		$gp=new groups($GuidNumber);
		$sql="UPDATE UGroup SET group_domain_id=$domain_id,group_timeupdate='$date',group_name='$gp->groupName' WHERE group_id='$GuidNumber'";
		$q=new mysql();
		$q->QUERY_SQL($SQL,$this->database);
		
	}
		
	function EditObmDomain($domain_id,$organization,$firstdomain,$aliasesDomains){
		$date=date('Y-m-d h:I:s');
		$sql="UPDATE Domain SET domain_timeupdate='$date', domain_label='$organization', domain_name='$firstdomain', domain_alias='$aliasesDomains'
		WHERE domain_id=$domain_id";
		$q=new mysql();
		$q->QUERY_SQL($SQL,$this->database);
	}
	
	function UserObmExists($uidNumber){
		$sql="SELECT userobm_id FROM UserObm WHERE userobm_id='$uidNumber'";
		$q=new mysql();
		
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,$this->database));
		if(strlen($ligne["userobm_id"])>0){
			$this->writelogs("uidNumber==\"{$ligne["userobm_id"]}\"");
			return true;}
		return false;
			
		}
	function GroupObmExists($gidNumber){
		$sql="SELECT group_id FROM UGroup WHERE group_id='$gidNumber'";
		$q=new mysql();
		
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,$this->database));
		if(strlen($ligne["group_id"])>0){
			return true;}
			
		$this->writelogs("$gidNumber group does not exists",__FUNCTION__);	
		return false;
			
		}		
	
	
	function export_users($organization,$domain_id){
		$this->writelogs("Analyze users in $organization");
		$ldap=new clladp();
		$hash=$ldap->hash_users_ou($organization);
		while (list ($uid, $val) = each ($hash) ){
			$user=new user($uid);
			$uidNumber=$user->uidNumber;
			if(!$this->UserObmExists($uidNumber)){
				
				$this->AddNewUserObm($user,$organization,$domain_id);
			}else{
				$this->writelogs("Editing user $uid ($uidNumber)");
				$this->EditUserObm($user,$domain_id);
			}
			
			
		}
		
		
		
		
	}
function addNewUserObm($user,$organization,$domain_id){	
	if($user->uid==null){return null;}
	$date=date('Y-m-d h:I:s');
	
	
	$sql="INSERT INTO `UserObm` 
		(`userobm_id`, 
		`userobm_domain_id`, 
		`userobm_timeupdate`, 
		`userobm_timecreate`, 
		`userobm_userupdate`, 
		`userobm_usercreate`, 
		`userobm_local`, 
		`userobm_ext_id`, 
		`userobm_system`, 
		`userobm_archive`, 
		`userobm_timelastaccess`, 
		`userobm_login`, 
		`userobm_nb_login_failed`, 
		`userobm_password_type`, 
		`userobm_password`, 
		`userobm_password_dateexp`, 
		`userobm_account_dateexp`, 
		`userobm_perms`, 
		`userobm_delegation_target`, 
		`userobm_delegation`, 
		`userobm_calendar_version`, 
		`userobm_uid`, 
		`userobm_gid`, 
		`userobm_datebegin`, 
		`userobm_hidden`, 
		`userobm_kind`, 
		`userobm_lastname`, 
		`userobm_firstname`, 
		`userobm_title`, 
		`userobm_sound`, 
		`userobm_company`, 
		`userobm_direction`, 
		`userobm_service`, 
		`userobm_address1`, 
		`userobm_address2`, 
		`userobm_address3`, 
		`userobm_zipcode`, 
		`userobm_town`, 
		`userobm_expresspostal`, 
		`userobm_country_iso3166`, 
		`userobm_phone`, 
		`userobm_phone2`, 
		`userobm_mobile`, 
		`userobm_fax`, 
		`userobm_fax2`, 
		`userobm_web_perms`, 
		`userobm_web_list`, 
		`userobm_web_all`, 
		`userobm_mail_perms`, 
		`userobm_mail_ext_perms`, 
		`userobm_email`, 
		`userobm_mail_server_id`, 
		`userobm_mail_quota`, 
		`userobm_mail_quota_use`, 
		`userobm_mail_login_date`, 
		`userobm_nomade_perms`, 
		`userobm_nomade_enable`, 
		`userobm_nomade_local_copy`, 
		`userobm_nomade_datebegin`, 
		`userobm_nomade_dateend`, 
		`userobm_email_nomade`, 
		`userobm_vacation_enable`, 
		`userobm_vacation_datebegin`, 
		`userobm_vacation_dateend`, 
		`userobm_vacation_message`, 
		`userobm_samba_perms`, 
		`userobm_samba_home`, 
		`userobm_samba_home_drive`, 
		`userobm_samba_logon_script`, 
		`userobm_host_id`, 
		`userobm_description`, 
		`userobm_location`, 
		`userobm_education`, 
		`userobm_photo_id`) 
		VALUES ('$user->uidNumber', '$domain_id', '$date', '$date', NULL, NULL, '1', NULL, '0', '0',
		 '0000-00-00 00:00:00', '$user->uid', '0', 'PLAIN', '$user->password', 
		 NULL, NULL, 'user', '', '', '0000-00-00 00:00:00', '$user->uidNumber', '$user->group_id', NULL, 
		 '0', '$user->sn', '$user->cn', '$user->givenName', '$user->title', '', '$organization', '', '', '$user->street', '', '', '$user->postalCode', '$user->town', '$user->postalAddress', '0', '$user->telephoneNumber', '', '$user->mobile', 
		 '', '', '0', NULL, '0', '0', '0', NULL, NULL, '0', '0', '0000-00-00 00:00:00', '0', '0', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', '0', '0000-00-00 00:00:00', 
		 '0000-00-00 00:00:00', NULL, '0', '', '', '', '0', NULL, NULL, NULL, NULL);";
		$q=new mysql();
		$q->QUERY_SQL($sql,$this->database);
		if(!$q->ok){$this->writelogs("$user->uid failed !!");return null;}
		$this->writelogs("++ Adding user $user->uid ($user->uidNumber)");
	
		}
		
		
		function EditUserObm($user,$domain_id=null){
			$date=date('Y-m-d h:I:s');
			if($domain_id==null){
				$domain_id=$this->GetobmDomainId($user->domainname);
			}
			
			$sql="UPDATE UserObm SET 
				`userobm_domain_id`='$domain_id',
				`userobm_timeupdate`='$date',
				`userobm_login`='$user->uid',
				`userobm_password_type`='PLAIN',
				`userobm_password`='$user->password',
				`userobm_uid`='$user->uidNumber',
				`userobm_gid`='$user->group_id',
				`userobm_lastname`='$user->cn',
				`userobm_firstname`='$user->givenName', 
				`userobm_title`='$user->title',
				`userobm_phone`='$user->telephoneNumber',
				`userobm_address1`='$user->street',
				`userobm_zipcode`='$user->postalCode',
				`userobm_town`='$user->town', 
				`userobm_expresspostal`='$user->postOfficeBox', 
				`userobm_address2`='$user->postalAddress',
				`userobm_perms`='user'
				 WHERE userobm_id='$user->uidNumber';";
				$q=new mysql();
				$q->QUERY_SQL($sql,$this->database);
				}
		
		function export_groups($organization,$domain_id){
			$ldap=new clladp();
			$hash=$ldap->hash_groups($organization);
			while (list ($groupname, $val) = each ($hash) ){
				$group_id=$val["gid"];
				if(!$this->GroupObmExists($group_id)){
					$this->writelogs("Adding Group $group_id");
					$this->AddNewObmGroup($group_id,$domain_id);
				}else{
					$this->writelogs("Editing Group $group_id");
					$this->EditObmGroup($group_id,$domain_id);
				}
				
				$this->AddOBMUsersToGroup($group_id,$domain_id);
				
				
			}
			
		}
		
		
		
}

//permission=admin_delegue,editor,user,admin

class obm_user{
	var $userobm_id;
	var $userobm_domain_id;
	var $userobm_login;
	var $userobm_password;
	var $userobm_perms="user";
	var $userobm_uid;
	var $userobm_gid;
	var $userobm_kind;
	var $userobm_lastname;
	var $userobm_firstname;
	var $userobm_address1;
	var $userobm_zipcode;
	var $userobm_phone;
	var $userobm_mobile;
	var $userobm_email;
	var $groups_list;
	var $MasterDomain;
	var $ou;
	var $last_error;
	var $database="obm2";
	var $gids=array();
	
	function obm_user($uid){
		
		$user=new user($uid);
		$this->userobm_id=$user->uidNumber;
		$this->userobm_uid=$user->uidNumber;
		
		$this->userobm_login=$uid;
		
		if(preg_match("#(.+?)@(.+)#",$user->mail,$re)){
			$this->MasterDomain=$re[2];
		}else{
			write_syslog("Unable to stat domain in $user->mail",__FILE__);
		}
		$this->groups_list=$user->Groups_list();
		
		
		$userobm_domain_id=$this->GetDomainID($this->MasterDomain);
		$this->userobm_domain_id=$userobm_domain_id;
		$this->userobm_password=$user->password;
		$this->userobm_kind=$user->DisplayName;
		$this->userobm_lastname=$user->sn;
		$this->userobm_firstname=$user->givenName;
		$this->userobm_address1=$user->street;
		$this->userobm_zipcode=$user->postalCode;
		$this->userobm_phone=$user->telephoneNumber;
		$this->userobm_mobile=$user->mobile;
		$this->userobm_email=$user->mail;
		$this->ou=$user->ou;
		$this->DeleteGroups();
		$this->CheckGroups();
		$this->CheckDomain();
		
		$this->userobm_gid=$user->gis[0];
		$this->CheckUser();
		$this->AddEntities();
		
		
	}
	
	function CheckDomain(){
		$date=date('Y-m-d h:I:s');
		if($this->userobm_domain_id==null){
			$sql="INSERT INTO `Domain` (`domain_timeupdate`, `domain_timecreate`, `domain_usercreate`, 
			`domain_userupdate`, `domain_label`, `domain_description`, `domain_name`, `domain_alias`, `domain_global`) VALUES
			('$date', '$date', NULL, NULL, '$this->MasterDomain', '$this->MasterDomain for organization $this->ou', '$this->MasterDomain', '', 0);";
			
			$q=new mysql();
			$q->QUERY_SQL($sql,"obm2");
			$this->userobm_domain_id=$this->GetDomainID($this->MasterDomain);
			
		}
		
	}
	
	function CheckUser(){
		$sql="SELECT userobm_uid FROM UserObm WHERE userobm_uid=$this->userobm_uid LIMIT 0,1";
		$q=new mysql();
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,$this->database));
		if($ligne["userobm_uid"]==null){$this->add();}else{$this->Edit();}
		
	}
	
	function CheckGroups(){
		if(!is_array($this->groups_list)){
			write_syslog("Unable to stat groups in id $this->userobm_id",__FILE__);
			return null;
		}
		
		while (list ($gid, $name) = each ($this->groups_list) ){
			if($gid<1000){continue;}
			$groupname=$this->group_name($gid);
			$this->gids[]=$gid;
			if($groupname==null){$this->group_add($gid,$this->userobm_domain_id);}
			$this->AddToGroup($gid);
			$GroupEntity=null;
			$GroupEntity=$this->GroupEntity($gid);
			if($GroupEntity==null){$this->AddGroupEntity($gid);}
			
		}
		
		reset($this->groups_list);
		
	}
	
	function AddToGroup($gid){
		$sql="INSERT INTO UserObmGroup (userobmgroup_group_id,userobmgroup_userobm_id) VALUES($gid,$this->userobm_id)";
		$q=new mysql();
		$q->QUERY_SQL($sql,"obm2");
		
	}
	
	function DeleteGroups(){
		$sql="DELETE FROM UserObmGroup WHERE userobmgroup_userobm_id=$this->userobm_id";
		$q=new mysql();
		$q->QUERY_SQL($sql,"obm2");
	}
	
	
	function group_name($gid){
		$sql="SELECT group_name FROM UGroup WHERE group_id=$gid  LIMIT 0,1";
		$q=new mysql();
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,$this->database));
		return $ligne["group_name"];
	}
	
	function GetDomainID($domain){
		if($domain==null){
			write_syslog("null given for GetDomainID()",__FILE__);
			return null;
		}
		$sql="SELECT domain_id FROM Domain WHERE domain_name='$domain'";
		$q=new mysql();
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,$this->database));
		if($ligne["domain_id"]>0){return $ligne["domain_id"];}
		
	}
	
	
	function Edit(){
		
		$sql="UPDATE UserObm
		SET 
			userobm_password='$this->userobm_password',
			userobm_kind='$this->userobm_kind',
			userobm_lastname='$this->userobm_lastname',
			userobm_firstname='$this->userobm_firstname',
			userobm_address1='$this->userobm_address1',
			userobm_zipcode='$this->userobm_zipcode',
			userobm_phone='$this->userobm_phone',
			userobm_mobile='$this->userobm_mobile',
			userobm_email='$this->userobm_email'
			WHERE userobm_id='$this->userobm_id'";
			
			$q=new mysql();
		    $q->QUERY_SQL($sql,$this->database);
		
	}
	
	
	function add(){
		
		if($this->userobm_domain_id==null){
			$this->last_error="Unable to find userobm_domain_id for $this->MasterDomain";
			return false;
		}
		
		if($this->userobm_gid==null){$this->userobm_gid=513;}
		
		$date=date('Y-m-d h:I:s');
		$sql="INSERT INTO `UserObm` (
			`userobm_id`, 
			`userobm_domain_id`, 
			`userobm_timeupdate`, 
			`userobm_timecreate`, 
			`userobm_userupdate`, 
			`userobm_usercreate`, 
			`userobm_local`, 
			`userobm_ext_id`, 
			`userobm_system`, 
			`userobm_archive`, 
			`userobm_status`, 
			`userobm_timelastaccess`, 
			`userobm_login`, 
			`userobm_nb_login_failed`, 
			`userobm_password_type`, 
			`userobm_password`, 
			`userobm_password_dateexp`, 
			`userobm_account_dateexp`, 
			`userobm_perms`, 
			`userobm_delegation_target`, 
			`userobm_delegation`, 
			`userobm_calendar_version`, 
			`userobm_uid`, 
			`userobm_gid`, 
			`userobm_datebegin`, 
			`userobm_hidden`, 
			`userobm_kind`, 
			`userobm_lastname`, 
			`userobm_firstname`, 
			`userobm_title`, 
			`userobm_sound`, 
			`userobm_company`, 
			`userobm_direction`, 
			`userobm_service`, 
			`userobm_address1`, 
			`userobm_address2`, 
			`userobm_address3`, 
			`userobm_zipcode`, 
			`userobm_town`, 
			`userobm_expresspostal`, 
			`userobm_country_iso3166`, 
			`userobm_phone`, 
			`userobm_phone2`, 
			`userobm_mobile`, 
			`userobm_fax`, 
			`userobm_fax2`, 
			`userobm_web_perms`, 
			`userobm_web_list`, 
			`userobm_web_all`, 
			`userobm_mail_perms`, 
			`userobm_mail_ext_perms`, 
			`userobm_email`, 
			`userobm_mail_server_id`, 
			`userobm_mail_quota`, 
			`userobm_mail_quota_use`, 
			`userobm_mail_login_date`, 
			`userobm_nomade_perms`, 
			`userobm_nomade_enable`, 
			`userobm_nomade_local_copy`, 
			`userobm_nomade_datebegin`, 
			`userobm_nomade_dateend`, 
			`userobm_email_nomade`, 
			`userobm_vacation_enable`, 
			`userobm_vacation_datebegin`, 
			`userobm_vacation_dateend`, 
			`userobm_vacation_message`, 
			`userobm_samba_perms`, 
			`userobm_samba_home`, 
			`userobm_samba_home_drive`, 
			`userobm_samba_logon_script`, 
			`userobm_host_id`, 
			`userobm_description`, 
			`userobm_location`, 
			`userobm_education`, 
			`userobm_photo_id`) VALUES
			($this->userobm_id, 
			$this->userobm_domain_id, 
			'$date',
			'$date', 
				NULL,
				NULL,
				1, 
				NULL,
				0,
				0,
				'VALID',
				'0000-00-00 00:00:00',
				'$this->userobm_login',
				0, 
				'PLAIN',
				'$this->userobm_password',
				NULL,
				NULL,
				'$this->userobm_perms',
				'Delegation',
				'Delegation',
				'0000-00-00 00:00:00',
				$this->userobm_id, 
				$this->userobm_gid,
				NULL,
				1,
				'$this->userobm_kind',
				'$this->userobm_lastname', 
				'$this->userobm_firstname',
				'Title', 
				 NULL, 
				 'Fax 2',
				 'Direction',
				 'Service',
				 'Service',
				 'Service',
				 '$this->userobm_address1',
				 'Address',
				 'Address 3',
				 '$this->userobm_zipcode',
				  '', 
				  '$this->userobm_phone',
				  'Phone 2',
				  '$this->userobm_mobile',
				  'Phone 2', 
				  'Fax 2',
				   0,
				   NULL,
				   0,
				   0,
				   0,
				   '$this->userobm_email',
				   NULL,
				   0,
				   0,
				   '0000-00-00 00:00:00',
				   0, 
				   0, 
				   0,
				   '0000-00-00 00:00:00', 
				   '0000-00-00 00:00:00', 
				   NULL, 
				   0, 
				   NULL, 
				   NULL, 
				   NULL, 
				   0, 
				   '', 
				   '', 
				   '', 
				   NULL,
				   'Description', 
				   NULL, 
				   NULL, 
				   NULL);";
				   
				   $q=new mysql();
				   $q->QUERY_SQL($sql,$this->database);
		
	}
	
	
function group_add($groupid,$group_domain_id){
	
	$gp=new groups($groupid);
	$date=date('Y-m-d h:I:s');
	$sql="INSERT INTO `UGroup` (
	 `group_id`,
	 `group_domain_id`, 
	 `group_timeupdate`,
	 `group_timecreate`,
	 `group_userupdate`,
	 `group_usercreate`,
	 `group_system`,
	 `group_archive`,
	 `group_privacy`,
	 `group_local`,
	 `group_ext_id`,
	 `group_samba`,
	 `group_gid`,
	 `group_mailing`,
	 `group_delegation`,
	 `group_manager_id`,
	 `group_name`,
	 `group_desc`,
	 `group_email`,
	 `group_contacts`) VALUES
	 ($groupid,
	  $group_domain_id,
	  '$date',
	  '$date',
	   NULL, 
	   NULL,
	   0,
	   0,
	   0,
	   1,
	   NULL,
	   0,
	   $groupid,
	   0,
	   'delegation', 
	   NULL,
	   '$gp->groupName',
	   '$gp->groupName', '', '');";
	   
	   $q=new mysql();
	   if(!$q->QUERY_SQL($sql,$this->database)){
	   		write_syslog("unable to add group $gp->group_id/$gp->ou for $this->userobm_login",__FILE__);
	   		write_syslog("$q->mysql_error",__FILE__);
	   }
		
	
	
	
}


function AddEntities(){
	//CalendarEntity
	$CalendarEntity=$this->GetCalendarEntity();
	$UserEntity=$this->GetUserEntity();
	$MailboxEntity=$this->GetMailboxEntity();
	
	if($CalendarEntity==null){$this->AddCalendarEntity();}
	if($UserEntity==null){$this->AddUserEntity();}
	if($MailboxEntity==null){$this->AddMailboxEntity();}
	$this->EntitiesRight();
	
}

function EntitiesRight(){
	$CalendarEntity=$this->GetCalendarEntity();
	$MailboxEntity=$this->GetMailboxEntity();
	
	$MailboxEntityRightsID=$this->GetEntityRightID($MailboxEntity);
	if($MailboxEntityRightsID==null){
		$sql="INSERT INTO EntityRight (entityright_entity_id,entityright_access,entityright_read)
		VALUES($MailboxEntity,1,0)";
		$q=new mysql();
		$q->QUERY_SQL($sql,$this->database);
	}
	
	$MailboxEntityRightsID=null;
	$MailboxEntityRightsID=$this->GetEntityRightID($CalendarEntity);
	if($MailboxEntityRightsID==null){
		$sql="INSERT INTO EntityRight (entityright_entity_id,entityright_access,entityright_read)
		VALUES($CalendarEntity,1,1)";
		$q=new mysql();
		$q->QUERY_SQL($sql,$this->database);
	}	
	
	
	
}

function newEntity() {
  	$q=new mysql();
    $sql='INSERT INTO Entity (entity_mailing) VALUES (TRUE)';
   	if($q->QUERY_SQL($sql,$this->database)){
    	return $q->last_id;
   	}
   	
  }
  
  
function GetEntityRightID($entity_id){
	  $sql="SELECT entityright_id FROM EntityRight WHERE entityright_entity_id=$entity_id";
		$q=new mysql();
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,$this->database));
		if($ligne["entityright_id"]>0){return $ligne["entityright_id"];}	  
}
  
  
  function GetCalendarEntity(){
  	$sql="SELECT calendarentity_entity_id FROM CalendarEntity WHERE calendarentity_calendar_id=$this->userobm_id";
  		$q=new mysql();
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,$this->database));
		if($ligne["calendarentity_entity_id"]>0){return $ligne["calendarentity_entity_id"];}
  }
  
  function GetUserEntity(){
  		$sql="SELECT userentity_entity_id FROM UserEntity WHERE userentity_user_id=$this->userobm_id";
  		$q=new mysql();
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,$this->database));
		if($ligne["userentity_entity_id"]>0){return $ligne["userentity_entity_id"];}  		
  }
  
  function GetMailboxEntity(){
  	$sql="SELECT mailboxentity_entity_id FROM MailboxEntity WHERE mailboxentity_mailbox_id=$this->userobm_id";
		$q=new mysql();
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,$this->database));
		if($ligne["mailboxentity_entity_id"]>0){return $ligne["mailboxentity_entity_id"];}  	  	
  }
  
  
  function AddCalendarEntity(){
  		$entity=$this->newEntity();
  		write_syslog("Add new CalendarEntity entity id $entity",__FILE__);
  		$sql="INSERT INTO CalendarEntity (calendarentity_entity_id,calendarentity_calendar_id) 
  		VALUES($entity,$this->userobm_id)";
  		$q=new mysql();
  		$q->QUERY_SQL($sql,$this->database);
  		}
  
  function AddUserEntity(){
  		$entity=$this->newEntity();
  		write_syslog("Add new UserEntity entity id $entity",__FILE__);
  		$sql="INSERT INTO UserEntity (userentity_entity_id,userentity_user_id) 
  		VALUES($entity,$this->userobm_id)";
  		$q=new mysql();
  		$q->QUERY_SQL($sql,$this->database);
  		} 

  function AddMailboxEntity(){
  		$entity=$this->newEntity();
  		write_syslog("Add new MailboxEntity entity id $entity",__FILE__);
  		$sql="INSERT INTO MailboxEntity (mailboxentity_entity_id,mailboxentity_mailbox_id) 
  		VALUES($entity,$this->userobm_id)";
  		$q=new mysql();
  		$q->QUERY_SQL($sql,$this->database);
  		} 

  	function AddGroupEntity($gid){
  		$entity=$this->newEntity();
  		write_syslog("Add new Group entity id $entity for $gid",__FILE__);
		$sql="INSERT INTO GroupEntity (groupentity_entity_id,groupentity_group_id) 
  		VALUES($entity,$gid)";
  		$q=new mysql();
  		$q->QUERY_SQL($sql,$this->database);  		
  	}

  	function GroupEntity($gid){
  		$sql="SELECT groupentity_entity_id FROM GroupEntity WHERE groupentity_group_id=$gid";
		$q=new mysql();
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,$this->database));
		if($ligne["groupentity_entity_id"]>0){return $ligne["groupentity_entity_id"];}  
  	}
	
	
	



	
	
}

class OBM2{
	
	
	function obm2(){
		
	}
	
	
function CleanOBMGhostAccount(){
		$sql="DELETE FROM `UserObm` WHERE LENGTH( userobm_login ) =0";
		$q=new mysql();
		$q->QUERY_SQL($sql,"obm2");
		}
	
	
	
}
	
	
	
	
	
	



 
?>