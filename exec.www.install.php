<?php
	include_once(dirname(__FILE__).'/ressources/class.templates.inc');
	include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
	include_once(dirname(__FILE__).'/ressources/class.users.menus.inc');
	include_once(dirname(__FILE__).'/ressources/class.apache.inc');
	include_once(dirname(__FILE__).'/ressources/class.system.network.inc');
	include_once(dirname(__FILE__).'/ressources/class.pdns.inc');
	include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
	include_once(dirname(__FILE__).'/framework/class.unix.inc');
	include_once(dirname(__FILE__).'/framework/frame.class.inc');
	include_once(dirname(__FILE__).'/ressources/class.joomla.php');
	include_once(dirname(__FILE__).'/ressources/class.opengoo.inc');
	include_once(dirname(__FILE__).'/ressources/class.roundcube.inc');
	$GLOBALS["SSLKEY_PATH"]="/etc/ssl/certs/apache";	
	if(preg_match("#--verbose#",implode(" ",$argv))){
		$GLOBALS["VERBOSE"]=true;$GLOBALS["debug"]=true;$GLOBALS["DEBUG"]=true;
		ini_set('html_errors',0);
		ini_set('display_errors', 1);
		ini_set('error_reporting', E_ALL);
	}	




if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}

if(is_array($argv)){
	while (list ($i, $cmds) = each ($argv) ){
		if($GLOBALS["VERBOSE"]){echo "token:$cmds\n";}
		if(preg_match("#--verbose#",$cmds)){$_GET["debug"]=true;}
		if(preg_match("#--only-([A-Z0-9]+)#",$cmds,$re)){
			echo "Starting......: {$re[1]} sub-domains\n";
			$GLOBALS["ONLY"]=$re[1];
		}
	}
}

if($argv[1]=="getou"){$f=new opengoo();echo $f->get_Organization($argv[2])."\n";die();}
if($argv[1]=="--single-install"){install_single($argv[2]);die();}




if(preg_match("#--vhosts#",implode(" ",$argv))){vhosts();die();}
if(preg_match("#--mailman#",implode(" ",$argv))){$GLOBALS["OUTPUT"]=true;mailmanhosts();die();}
if(preg_match("#--Wvhosts#",implode(" ",$argv))){@mkdir("/usr/local/apache-groupware/conf");@file_put_contents("/usr/local/apache-groupware/conf/vhosts",vhosts(true));die();}


if(!Build_pid_func(__FILE__,"MAIN")){
	writelogs(basename(__FILE__).":Already executed.. aborting the process",basename(__FILE__),__FILE__,__LINE__);
	die();
}


if($argv[1]=="remove"){remove($argv[2]);die;}


$ldap=new clladp();
$pattern="(&(objectclass=apacheConfig)(apacheServerName=*))";
$attr=array();
$sr =@ldap_search($ldap->ldap_connection,$ldap->suffix,$pattern,$attr);
$hash=ldap_get_entries($ldap->ldap_connection,$sr);	
//print_r($hash);

for($i=0;$i<$hash["count"];$i++){
	
	$root=$hash[$i]["apachedocumentroot"][0];
	$wwwservertype=trim($hash[$i]["wwwservertype"][0]);
	$apacheservername=trim($hash[$i]["apacheservername"][0]);
	if($GLOBALS["ONLY"]<>null){if($wwwservertype<>$GLOBALS["ONLY"]){continue;}}
	install_groupwares($apacheservername,$wwwservertype,$root,$hash[$i]);
}

if($hash["count"]>0){
	echo "restart apache\n";
	writelogs("restart apache...",basename(__FILE__),__FILE__,__LINE__);
	shell_exec('/etc/init.d/artica-postfix restart apache-groupware &');
}


function install_single($apacheservername){
	$ldap=new clladp();
	$pattern="(&(objectclass=apacheConfig)(apacheServerName=$apacheservername))";
	$attr=array();
	$sr =@ldap_search($ldap->ldap_connection,$ldap->suffix,$pattern,$attr);
	$hash=ldap_get_entries($ldap->ldap_connection,$sr);	
	for($i=0;$i<$hash["count"];$i++){
		$root=$hash[$i]["apachedocumentroot"][0];
		$wwwservertype=trim($hash[$i]["wwwservertype"][0]);
		$apacheservername=trim($hash[$i]["apacheservername"][0]);
		if($GLOBALS["ONLY"]<>null){if($wwwservertype<>$GLOBALS["ONLY"]){continue;}}
		install_groupwares($apacheservername,$wwwservertype,$root,$hash[$i]);	
		
	}	
	shell_exec('/etc/init.d/artica-postfix restart apache-groupware &');
}


function install_groupwares($apacheservername,$wwwservertype,$root,$hash){
	
		$dn=$hash["dn"];
		if(preg_match("#ou=www,ou=(.+?),dc=organizations#",$dn,$re) ){$hash["OU"][0]=trim($re[1]);$ouexec=trim($re[1]);}
		echo "Starting......: Apache groupware checking $apacheservername host ($wwwservertype)\n";
	switch ($wwwservertype) {
		case "LMB":LMB_INSTALL($apacheservername,$root,$hash);break;	
		case "JOOMLA":JOOMLA_INSTALL($apacheservername,$root,$hash);break;
		case "ROUNDCUBE":ROUNDCUBE_INSTALL($apacheservername,$root,$hash);break;
		case "SUGAR":SUGAR_INSTALL($apacheservername,$root,$hash);break;						
		case "ARTICA_USR":ARTICA_INSTALL($apacheservername,$root,$hash);break;
		case "OBM2":OBM2_INSTALL($apacheservername,$root,$hash);break;
		case "OPENGOO":OPENGOO_INSTALL($apacheservername,$root,$hash);break;		
		case "GROUPOFFICE":GROUPOFFICE_INSTALL($apacheservername,$root,$hash);break;
		case "ZARAFA":ZARAFA_INSTALL($apacheservername,$root,$hash);break;		
		case "ZARAFA_MOBILE":ZARAFA_MOBILE_INSTALL($apacheservername,$root,$hash);break;
		case "DRUPAL":DRUPAL_INSTALL($apacheservername,$root,$hash);break;		
		case "WEBDAV":WEBDAV_USERS($apacheservername,$root,$hash);break;
		case "PIWIGO":PIWIGO_INSTALL($apacheservername,$root,$hash);break;
		case "SQUID_STATS":SQUID_STATS_INSTALL($apacheservername,$root,$hash);break;					
		}
}



function remove($servername){
	$apache=new vhosts();
	$GLOBALS["ADDLOG"]="/var/log/artica-postfix/$servername.log";
	$confs=$apache->SearchHosts($servername);
	events(__FUNCTION__.":: Check $servername");
	events(__FUNCTION__.":: remove files and directories");
	if(is_dir("/usr/share/artica-groupware/domains/$servername")){
		shell_exec("/bin/rm -rf /usr/share/artica-groupware/domains/$servername");
	}	
	$server_database=str_replace(" ","_",$servername);
	$server_database=str_replace(".","_",$servername);
	$server_database=str_replace("-","_",$server_database);
	
	$q=new mysql();
	if($q->DATABASE_EXISTS($server_database)){
		$q->DELETE_DATABASE($server_database);

	}
	
	$flaseuser["root"]=true;
	$flaseuser["admin"]=true;
	$flaseuser["manager"]=true;
	
	
	$sql="DELETE FROM `mysql`.`db` WHERE `db`.`Db` = '$server_database'";
	$q->QUERY_SQL($sql,"mysql");
	
		
	events(__FUNCTION__.":: removing ldap branch {$confs["dn"]}");
	
	$ldap=new clladp();
	if($ldap->ExistsDN($confs["dn"])){
		$ldap->ldap_delete($confs["dn"]);
	}
	events(__FUNCTION__.":: restarting HTTP service...");
	shell_exec("/etc/init.d/artica-postfix restart apache-groupware &");
	
}

function SQUID_STATS_INSTALL($servername,$root,$hash=array()){
	@mkdir($root,0755,true);
	
	$dirs[]="/usr/share/artica-postfix";
	
	foreach (glob("/usr/share/artica-postfix/*.php") as $filename) {
		$file=basename($filename);
		shell_exec("/bin/ln -s --force $filename $root/$file");
		
		
	}	
	
	foreach (glob("/usr/share/artica-postfix/*.js") as $filename) {
		$file=basename($filename);
		shell_exec("/bin/ln -s --force $filename $root/$file");
	}	
	
	shell_exec("/bin/ln -s --force /usr/share/artica-postfix/ressources $root/ressources");
	shell_exec("/bin/ln -s --force /usr/share/artica-postfix/css $root/css");
	shell_exec("/bin/ln -s --force /usr/share/artica-postfix/js $root/js");
	shell_exec("/bin/ln -s --force /usr/share/artica-postfix/img $root/img");
	shell_exec("/bin/ln -s --force /usr/share/artica-postfix/framework $root/framework");
	
	
	
}

function PIWIGO_INSTALL($servername,$root,$hash=array()){
	$GLOBALS["ADDLOG"]="/var/log/artica-postfix/$servername.log";
	$productname="piwigo";	
	$sourcedir="/usr/local/share/artica/piwigo_src";
	$sql_file="/usr/local/share/artica/piwigo_src/install/piwigo_structure-mysql.sql";
	
	if($root==null){events("Starting install $productname Unable to stat root dir");return false;}
	if(!is_dir($sourcedir)){
		events("Starting install $productname Unable to stat $productname SRC");
		return false;
	}
	
	echo "sql_file:$sql_file\n";
	$user=$hash["wwwmysqluser"][0];
	echo "user:$user\n";
	$mysql_password=$hash[strtolower("WWWMysqlPassword")][0];
	$appli_user=$hash["wwwappliuser"][0];
	$appli_password=$hash["wwwapplipassword"][0];
	
	$ou=$hash["OU"][0];	
	if($mysql_password==null){events("Starting install $productname Unable to stat Mysql password");return false;}
	echo "Create dir $root\n";
	@mkdir($root,0755,true);	
	$server_database=str_replace(".","_",$servername);
	$server_database=str_replace("-","_",$server_database);	
	echo "server_database: $server_database\n";
	events("Starting install $productname sub-system mysql database $server_database...");
	$q=new mysql();
	echo "CREATE_DATABASE: $server_database\n";
	$q->CREATE_DATABASE($server_database);
	
	if(!$q->DATABASE_EXISTS($server_database)){
		echo "CREATE_DATABASE: FAILED\n";
		events("Starting install $productname unable to create MYSQL Database");
		return false;
	}

	events("Starting install $productname installing source code");
	echo "/bin/cp -rf $sourcedir/* $root/\n";
	shell_exec("/bin/cp -rf $sourcedir/* $root/");
	echo "Copy done...\n";

	if($q->mysql_password<>null){
		$password=" --password=$q->mysql_password ";
	}else{
		events("Starting install $productname installing tables datas with null password");
	}
	
	echo "Installing database $server_database\n";
	$cmd="mysql --port=$q->mysql_port --skip-column-names --database=$server_database --silent --xml ";
	$cmd=$cmd." --user=$q->mysql_admin$password <$sql_file";
	shell_exec($cmd);
	echo "Installing database $server_database done.\n";
		
	echo "Setting privileges\n";	
	$q->PRIVILEGES($user,$mysql_password,$server_database);
	$f[]="<?php";	
	$f[]="\$conf['dblayer'] = 'mysql';";
	$f[]="\$conf['db_base'] = '$server_database';";
	$f[]="\$conf['db_user'] = '$user';";
	$f[]="\$conf['db_password'] = '$mysql_password';";
	$f[]="\$conf['db_host'] = '$q->mysql_server';";
	$f[]="";
	$f[]="\$prefixeTable = 'piwigo_';";
	$f[]="";
	$f[]="define('PHPWG_INSTALLED', true);";
	$f[]="define('PWG_CHARSET', 'utf-8');";
	$f[]="define('DB_CHARSET', 'utf8');";
	$f[]="define('DB_COLLATE', '');";
	$f[]="?>";
	
	
	$sql="INSERT INTO `piwigo_config` (`param`, `value`, `comment`) VALUES('local_data_dir_checked', 'true', NULL),('nb_comment_page', '10', 'number of comments to display on each page'),('log', 'false', 'keep an history of visits on your website'),('comments_validation', 'false', 'administrators validate users comments before becoming visible'),('comments_forall', 'false', 'even guest not registered can post comments'),('user_can_delete_comment', 'false', 'administrators can allow user delete their own comments'),('user_can_edit_comment', 'false', 'administrators can allow user edit their own comments'),('email_admin_on_comment_edition', 'false', 'Send an email to the administrators when a comment is modified'),('email_admin_on_comment_deletion', 'false', 'Send an email to the administrators when a comment is deleted'),('gallery_locked', 'false', 'Lock your gallery temporary for non admin users'),('gallery_title', 'Piwigo demonstration site', 'Title at top of each page and for RSS feed'),('gallery_url', '', 'Optional alternate homepage for the gallery'),('rate', 'true', 'Rating pictures feature is enabled'),('rate_anonymous', 'true', 'Rating pictures feature is also enabled for visitors'),('page_banner', '<h1>Piwigo demonstration site</h1><p>My photos web site</p>', 'html displayed on the top each page of your gallery'),('history_admin', 'false', 'keep a history of administrator visits on your website'),('history_guest', 'true', 'keep a history of guest visits on your website'),('allow_user_registration', 'true', 'allow visitors to register?'),('allow_user_customization', 'true', 'allow users to customize their gallery?'),('nbm_send_html_mail', 'true', 'Send mail on HTML format for notification by mail'),('nbm_send_mail_as', '', 'Send mail as param value for notification by mail'),('nbm_send_detailed_content', 'true', 'Send detailed content for notification by mail'),('nbm_complementary_mail_content', '', 'Complementary mail content for notification by mail'),('nbm_send_recent_post_dates', 'true', 'Send recent post by dates for notification by mail'),('email_admin_on_new_user', 'false', 'Send an email to theadministrators when a user registers'),('email_admin_on_comment', 'false', 'Send an email to the administrators when a valid comment is entered'),('email_admin_on_comment_validation', 'false', 'Send an email to the administrators when a comment requires validation'),('email_admin_on_picture_uploaded', 'false', 'Send an email to the administrators when a picture is uploaded'),('obligatory_user_mail_address', 'false', 'Mail address is obligatory for users'),('c13y_ignore', NULL, 'List of ignored anomalies'),('upload_link_everytime', 'false', 'Show upload link every time'),('upload_user_access', '2', 'User access level to upload'),('extents_for_templates', 'a:0:{}', 'Actived template-extension(s)'),('blk_menubar', '', 'Menubar options'),('menubar_filter_icon', 'true', 'Display filter icon'),('index_sort_order_input', 'true', 'Display image order selection list'),('index_flat_icon', 'true', 'Display flat icon'),('index_posted_date_icon', 'true', 'Display calendar by posted date'),('index_created_date_icon', 'true', 'Display calendar by creation date icon'),('index_slideshow_icon', 'true', 'Display slideshow icon'),('picture_metadata_icon', 'true', 'Display metadata icon on picture page'),('picture_slideshow_icon', 'true', 'Display slideshow icon on picture page'),('picture_favorite_icon', 'true', 'Display favorite icon on picture page'),('picture_download_icon', 'true', 'Display download icon on picture page'),('picture_navigation_icons', 'true', 'Display navigation icons on picture page'),('picture_navigation_thumb', 'true', 'Display navigation thumbnails on picture page'),('picture_informations', 'a:11:{s:6:\"author\";b:1;s:10:\"created_on\";b:1;s:9:\"posted_on\";b:1;s:10:\"dimensions\";b:1;s:4:\"file\";b:1;s:8:\"filesize\";b:1;s:4:\"tags\";b:1;s:10:\"categories\";b:1;s:6:\"visits\";b:1;s:12:\"average_rate\";b:1;s:13:\"privacy_level\";b:1;}', 'Information displayed on picture page'),('secret_key', '1fed8190c215435d360c82cb045e37ba', 'a secret key specific to the gallery for internal use');";
	$q->QUERY_SQL($sql,$server_database);	
	
	$sql="INSERT INTO `piwigo_languages` (`id`, `version`, `name`) VALUES('pt_BR', '0', 'Brasil [BR]'),('ca_ES', '0', 'Catalan [CA]'),('da_DK', '0', 'Dansk [DK]'),('de_DE', '0', 'Deutsch [DE]'),('en_UK', '0', 'English [UK]'),('es_AR', '0', 'Español [AR]'),('es_ES', '0', 'Español [ES]'),('fr_FR', '0', 'Français [FR]'),('fr_CA', '0', 'Français [QC]'),('hr_HR', '0', 'Hrvatski [HR]'),('it_IT', '0', 'Italiano [IT]'),('lv_LV', '0', 'Latviešu [LV]'),('hu_HU', '0', 'Magyar [HU]'),('nl_NL', '0', 'Nederlands [NL]'),('pl_PL', '0', 'Polski [PL]'),('pt_PT', '0', 'Português [PT]'),('ro_RO', '0', 'Român? [RO]'),('sk_SK', '0', 'Slovensky [SK]'),('sl_SL', '0', 'Slovenšcina [SL]'),('sh_RS', '0', 'Srpski [SR]'),('sv_SE', '0', 'Svenska [SE]\n'),('vi_VN', '0', 'Ti?ng Vi?t [VN]'),('tr_TR', '0', 'Türkçe [TR]'),('cs_CZ', '0', '?esky [CZ]'),('mk_MK', '0', '?????????? [MK]'),('ru_RU', '0', '??????? [RU]'),('sr_RS', '0', '?????? [SR]'),('he_IL', '0', '????? [IL]'),('ar_SA', '0', '??????? [AR]'),('fa_IR', '0', '????? [IR]'),('ka_GE', '0', '??????? [GE]'),('ja_JP', '0', '??? [JP]'),('zh_CN', '0', '???? [CN]');";	
	$q->QUERY_SQL($sql,$server_database);
	
	$sql="INSERT INTO `piwigo_user_infos` (`user_id`, `nb_image_line`, `nb_line_page`, `status`, `adviser`, `language`, `maxwidth`, `maxheight`, `expand`, `show_nb_comments`, `show_nb_hits`, `recent_period`, `theme`, `registration_date`, `enabled_high`, `level`) VALUES(1, 5, 3, 'webmaster', 'false', 'en_UK', NULL, NULL, 'false', 'false', 'false', 7, 'Sylvia', NOW(), 'true', 8),(2, 5, 3, 'guest', 'false', 'en_UK', NULL, NULL, 'false', 'false', 'false', 7, 'Sylvia', '2011-01-11 12:51:10', 'true', 0);";
	$q->QUERY_SQL($sql,$server_database);

	$md_password=md5($appli_password);
	$sql="INSERT INTO `piwigo_users` (`id`, `username`, `password`, `mail_address`) VALUES (1, '$appli_user', '$md_password', '$appli_user@$servername'),(2, 'guest', NULL, NULL);";
	
	
	
	echo "Writing configuration $root/local/config/database.inc.php\n";
	@file_put_contents("$root/local/config/database.inc.php",@implode("\n",$f));
	$q->QUERY_SQL($sql,$server_database);
	$sql="UPDATE `piwigo_users` SET `username`='$appli_user', `password`='$md_password' WHERE `username`='$appli_user'";
	$q->QUERY_SQL($sql,$server_database);

	
}

function JOOMLA_INSTALL($servername,$root,$hash=array()){
	$GLOBALS["ADDLOG"]="/var/log/artica-postfix/$servername.log";	
	if($root==null){events("Starting install joomla Unable to stat root dir");return false;}
	if(!is_dir("/usr/local/share/artica/joomla_src")){
		events("Starting install joomla Unable to stat JOOMLA SRC");
		return false;
	}
	$sql_file="/usr/share/artica-postfix/bin/install/joomla/joomla.sql";
	
	echo "sql_file:$sql_file\n";

	$user=$hash["wwwmysqluser"][0];
	echo "user:$user\n";
	
	$mysql_password=$hash[strtolower("WWWMysqlPassword")][0];
	
	$appli_user=$hash["wwwappliuser"][0];
	$appli_password=$hash["wwwapplipassword"][0];
	$ou=$hash["OU"][0];
	
	if($user==null){events("Starting install Joomla Unable to stat Mysql username");return false;}
	if($mysql_password==null){events("Starting install Joomla Unable to stat Mysql password");return false;}

	echo "Create dir $root\n";
	@mkdir($root,0755,true);
	
	$server_database=str_replace(".","_",$servername);
	$server_database=str_replace("-","_",$server_database);	
	
	echo "server_database: $server_database\n";
	events("Starting install Joomla sub-system mysql database $server_database...");
	$q=new mysql();
	echo "CREATE_DATABASE: $server_database\n";
	$q->CREATE_DATABASE($server_database);
	
	if(!$q->DATABASE_EXISTS($server_database)){
		echo "CREATE_DATABASE: FAILED\n";
		events("Starting install Joomla unable to create MYSQL Database");
		return false;
	}
	
		
	events("Starting install Joomla installing source code");
	echo "/bin/cp -rf /usr/local/share/artica/joomla_src/* $root/\n";
	shell_exec("/bin/cp -rf /usr/local/share/artica/joomla_src/* $root/");
	echo "Copy done...\n";
	
	if($q->mysql_password<>null){
		$password=" --password=$q->mysql_password ";
	}else{
		events("Starting install Joomla installing tables datas with null password");
	}
	
		echo "Installing database $server_database\n";
		$cmd="mysql --port=$q->mysql_port --skip-column-names --database=$server_database --silent --xml ";
		$cmd=$cmd." --user=$q->mysql_admin$password <$sql_file";
		
		shell_exec($cmd);
		echo "Installing database $server_database done.\n";
		
	echo "Setting privileges\n";	
	$q->PRIVILEGES($user,$mysql_password,$server_database);
	$joomla=new joomla();
	
	echo "Setting Joomla password\n";
	$joomla->SaveAdminPasswordDatabase($server_database,$appli_password);
	
	if(is_dir("$root/installation")){
		echo "removing installation $root/installation subfolder\n";
		shell_exec("/bin/rm -rf $root/installation");
	}
	
	echo "settings configuration in $root installation folder\n";
	JOOMLA_CONFIG($root,$ou,$user,$mysql_password,$server_database);
	
	
}

function JOOMLA_CONFIG($root,$ou,$appli_user,$appli_password,$server_database){
$q=new mysql();
$conf[]="<?php";
$conf[]="class JConfig {";
$conf[]="	/**";
$conf[]="	* -------------------------------------------------------------------------";
$conf[]="	* Site configuration section";
$conf[]="	* -------------------------------------------------------------------------";
$conf[]="	*/";
$conf[]="	/* Site Settings */";
$conf[]="	var \$offline = '0';";
$conf[]="	var \$offline_message = 'This site is down for maintenance.<br /> Please check back again soon.';";
$conf[]="	var \$sitename = '$ou Site ';			// Name of Joomla site";
$conf[]="	var \$editor = 'tinymce';";
$conf[]="	var \$list_limit = '20';";
$conf[]="	var \$legacy = '0';";
$conf[]="";
$conf[]="	/**";
$conf[]="	* -------------------------------------------------------------------------";
$conf[]="	* Database configuration section";
$conf[]="	* -------------------------------------------------------------------------";
$conf[]="	*/";
$conf[]="	/* Database Settings */";
$conf[]="	var \$dbtype = 'mysql';					// Normally mysql";
$conf[]="	var \$host = '$q->mysql_server';				// This is normally set to localhost";
$conf[]="	var \$user = '$appli_user';							// MySQL username";
$conf[]="	var \$password = '$appli_password';						// MySQL password";
$conf[]="	var \$db = '$server_database';							// MySQL database name";
$conf[]="	var \$dbprefix = 'jos_';					// Do not change unless you need to!";
$conf[]="";
$conf[]="	/* Server Settings */";
$conf[]="	var \$secret = 'FBVtggIk5lAzEU9H'; 		//Change this to something more secure";
$conf[]="	var \$gzip = '0';";
$conf[]="	var \$error_reporting = '-1';";
$conf[]="	var \$helpurl = 'http://help.joomla.org';";
$conf[]="	var \$xmlrpc_server = '1';";
$conf[]="	var \$ftp_host = '';";
$conf[]="	var \$ftp_port = '';";
$conf[]="	var \$ftp_user = '';";
$conf[]="	var \$ftp_pass = '';";
$conf[]="	var \$ftp_root = '';";
$conf[]="	var \$ftp_enable = '';";
$conf[]="	var \$tmp_path	= '$root/tmp';";
$conf[]="	var \$log_path	= '$root/logs';";
$conf[]="	var \$offset = '0';";
$conf[]="	var \$live_site = ''; 					// Optional, Full url to Joomla install.";
$conf[]="	var \$force_ssl = 0;		//Force areas of the site to be SSL ONLY.  0 = None, 1 = Administrator, 2 = Both Site and Administrator";
$conf[]="";
$conf[]="	/* Session settings */";
$conf[]="	var \$lifetime = '15';					// Session time";
$conf[]="	var \$session_handler = 'database';";
$conf[]="";
$conf[]="	/* Mail Settings */";
$conf[]="	var \$mailer = 'mail';";
$conf[]="	var \$mailfrom = '';";
$conf[]="	var \$fromname = '';";
$conf[]="	var \$sendmail = '/usr/sbin/sendmail';";
$conf[]="	var \$smtpauth = '0';";
$conf[]="	var \$smtpuser = '';";
$conf[]="	var \$smtppass = '';";
$conf[]="	var \$smtphost = 'localhost';";
$conf[]="";
$conf[]="	/* Cache Settings */";
$conf[]="	var \$caching = '0';";
$conf[]="	var \$cachetime = '15';";
$conf[]="	var \$cache_handler = 'file';";
$conf[]="";
$conf[]="	/* Debug Settings */";
$conf[]="	var \$debug      = '0';";
$conf[]="	var \$debug_db 	= '0';";
$conf[]="	var \$debug_lang = '0';";
$conf[]="";
$conf[]="	/* Meta Settings */";
$conf[]="	var \$MetaDesc = 'Joomla! - the dynamic portal engine and content management system';";
$conf[]="	var \$MetaKeys = 'joomla, Joomla';";
$conf[]="	var \$MetaTitle = '1';";
$conf[]="	var \$MetaAuthor = '1';";
$conf[]="";
$conf[]="	/* SEO Settings */";
$conf[]="	var \$sef = '0';";
$conf[]="	var \$sef_rewrite = '0';";
$conf[]="	var \$sef_suffix = '';";
$conf[]="";
$conf[]="	/* Feed Settings */";
$conf[]="	var \$feed_limit   = 10;";
$conf[]="	var \$feed_email   = 'author';";
$conf[]="}";
$conf[]="?>";

@mkdir($root,755,true);
@mkdir("$root/tmp",755,true);
@mkdir("$root/logs",755,true);
@file_put_contents("$root/configuration.php",@implode("\n",$conf));
}


function SUGAR_INSTALL($servername,$root,$hash=array()){
	$GLOBALS["ADDLOG"]="/var/log/artica-postfix/$servername.log";	
	if($root==null){events("Starting install Sugar Unable to stat root dir");return false;}
	$user=$hash["wwwmysqluser"][0];
	$mysql_password=$hash[strtolower("WWWMysqlPassword")][0];
	
	$appli_user=$hash["wwwappliuser"][0];
	$appli_password=$hash["wwwapplipassword"][0];
	$wwwsslmode=$hash["wwwsslmode"][0];
	if($wwwsslmode=="TRUE"){$SSL=true;}else{$SSL=false;}
	
	
	if($user==null){events("Starting install SugarCRM Unable to stat Mysql username");return false;}
	if($mysql_password==null){events("Starting install SugarCRM Unable to stat Mysql password");return false;}

	@mkdir($root,0755,true);
	
	$server_database=str_replace(".","_",$servername);
	$server_database=str_replace("-","_",$server_database);	
	
	$q=new mysql();
	
	if(!$q->DATABASE_EXISTS($server_database)){
		events("Install SugarCRM sub-system mysql database $server_database...");
		$q->CREATE_DATABASE($server_database);
	}
	
	
	
	if(!$q->DATABASE_EXISTS($server_database)){
		events("Install SugarCRM unable to create MYSQL Database");
		return false;
	}

	
	if(!is_file("$root/index.php")){
		events("Install SugarCRM installing source code");
		shell_exec("/bin/cp -rf /usr/local/share/artica/sugarcrm_src/* $root/");
	}
	
	if(!is_file("$root/install/siteConfig_a.php")){
		events("Install SugarCRM installing install directory");
		shell_exec("/bin/cp -rf /usr/local/share/artica/sugarcrm_src/install $root/");
	}
	$sugar=new SugarCRM();
	$sugar->sql_host=$q->mysql_server;
	$sugar->sql_db=$server_database;
	$sugar->sql_admin=$user;
	$sugar->sql_password=$mysql_password;
	if($appli_user==null){$appli_user="admin";}
	$sugar->manager_name=$appli_user;
	$sugar->manager_password=$appli_password;
	$sugar->sugar_supposed_version=SUGAR_CRMVERSION($root);
	events("$servername v.$sugar->sugar_supposed_version");
	$sugar->servername=$servername;
	$q->PRIVILEGES($user,$mysql_password,$server_database);



	$conf=$sugar->BuildSugarConf($SSL);
	events("Creating configuration file config.php");
	@file_put_contents("$root/config.php",$conf);
	$conf_silent=$sugar->BuildSilentInstallConf($SSL);
	events("Creating silent configuration file config_si.php");
	@file_put_contents("$root/config_si.php",$conf_silent);
	shell_exec("chmod -R 755 $root/include/javascript");
	
	
	
}

function SUGAR_CRMVERSION($rootpath){
     $path="$rootpath/sugar_version.php";
     $datas=@file_get_contents($path);
     $tbl=explode("\n",$datas);
     	while (list ($num, $line) = each ($tbl) ){
     		if(preg_match("#\$sugar_version.+?([0-9\.a-z]+)#",$line,$re)){
     			return $re[1];
     		}
     	}   
}
//##############################################################################

function ARTICA_INSTALL($servername,$root,$hash=array()){
	@mkdir($root,0755,true);
	writelogs("Copy the content of /usr/share/artica-postfix/user-backup",__FUNCTION__,__FILE__,__LINE__);
	shell_exec("cp -rf /usr/share/artica-postfix/user-backup/* $root/");
	
	writelogs("Linking /usr/share/artica-postfix/ressources/settings.inc to $root/ressources/settings.inc",__FUNCTION__,__FILE__,__LINE__);
	shell_exec("ln -s --force /usr/share/artica-postfix/ressources/settings.inc $root/ressources/settings.inc");
	
	writelogs("Linking /usr/share/artica-postfix/ressources/language to $root/ressources/language",__FUNCTION__,__FILE__,__LINE__);
	shell_exec("ln -s --force /usr/share/artica-postfix/ressources/language $root/ressources/language");
	
	writelogs("copy other files;...",__FUNCTION__,__FILE__,__LINE__);
	shell_exec("cp -f /usr/share/artica-postfix/ressources/class.cyrus-admin.inc $root/ressources/class.cyrus-admin.inc");
	shell_exec("cp -f /usr/share/artica-postfix/ressources/class.apache.inc $root/ressources/class.apache.inc");
	shell_exec("cp -f /usr/share/artica-postfix/ressources/class.mysql.inc $root/ressources/class.mysql.inc");

}
function ZARAFA_INSTALL($servername,$root,$hash=array()){
	events("Starting install ZARAFA_INSTALL sub-system ");
	events("ln -s --force /usr/share/zarafa-webaccess $root");
	shell_exec("ln -s --force /usr/share/zarafa-webaccess $root");
}
function ZARAFA_MOBILE_INSTALL($servername,$root,$hash=array()){
	events("Starting install ZARAFA_MOBILE sub-system ");
	events("ln -s --force /usr/share/zarafa-webaccess-mobile $root");
	shell_exec("ln -s --force /usr/share/zarafa-webaccess-mobile $root");
}






function LMB_INSTALL($servername,$root,$hash=array()){
	$GLOBALS["ADDLOG"]="/var/log/artica-postfix/$servername.log";
	if($root==null){events("Starting install LMB Unable to stat root dir");return false;}
	if(!is_dir("/usr/local/share/artica/lmb_src")){
		events("Starting install LMB Unable to stat LMB SRC");
		return false;
	}
	
	$user=$hash["wwwmysqluser"][0];
	$mysql_password=$hash[strtolower("WWWMysqlPassword")][0];
	
	$appli_user=$hash["wwwappliuser"][0];
	$appli_password=$hash["wwwapplipassword"][0];
	
	
	if($user==null){events("Starting install LMB Unable to stat Mysql username");return false;}
	if($mysql_password==null){events("Starting install LMB Unable to stat Mysql password");return false;}
	@mkdir($root,0755,true);
	
	$server_database=str_replace(".","_",$servername);
	$server_database=str_replace("-","_",$server_database);	
	events("Starting install LMB sub-system mysql database $server_database...");
	$q=new mysql();
	$q->CREATE_DATABASE($server_database);
	
	if(!$q->DATABASE_EXISTS($server_database)){
		events("Starting install LMB unable to create MYSQL Database");
		return false;
	}
	
	events("Starting setting permissions on Database with user $user");
	$q->PRIVILEGES($user,$mysql_password,$server_database);
	
	
	events("Starting install LMB installing source code");
	shell_exec("/bin/cp -rf /usr/local/share/artica/lmb_src/* $root/");
	if($q->mysql_password<>null){
		$password=" --password=$q->mysql_password ";
	}else{
		events("Starting install LMB installing tables datas with null password");
	}
	
	$files[]="/usr/local/share/artica/lmb_src/__install_lmb_files/bdd/lmb_install.sql";
	$files[]="/usr/local/share/artica/lmb_src/__install_lmb_files/bdd/lundimatin_villes_01.sql";
	$files[]="/usr/local/share/artica/lmb_src/__install_lmb_files/bdd/lundimatin_villes_02.sql";
	$files[]="/usr/local/share/artica/lmb_src/__install_lmb_files/bdd/lundimatin_villes_01b.sql";
	$files[]="/usr/local/share/artica/lmb_src/__install_lmb_files/bdd/lundimatin_villes_02b.sql";
	
	while (list ($num, $line) = each ($files) ){
		events("Starting install LMB installing tables datas $server_database/$num");
		$cmd="mysql --port=$q->mysql_port --skip-column-names --database=$server_database --silent --xml ";
		$cmd=$cmd." --user=$q->mysql_admin$password <$line";
		shell_exec($cmd);

	}
	
	events("Delete user if not exists...");
	$sql="DELETE FROM annu_admin WHERE ref_contact='C-000000-00001'";
	$q->QUERY_SQL($sql,$server_database);
	$sql="DELETE FROM annu_collab WHERE ref_contact='C-000000-00001'";
	$q->QUERY_SQL($sql,$server_database);
	$sql="DELETE FROM annu_collab_fonctions WHERE ref_contact='C-000000-00001'";
	$q->QUERY_SQL($sql,$server_database);
	$sql="DELETE FROM users WHERE ref_contact='C-000000-00001'";
	$q->QUERY_SQL($sql,$server_database);
	$sql="DELETE FROM users_permissions WHERE ref_user='U-000000-00001'";
	$q->QUERY_SQL($sql,$server_database);
	
$sql="INSERT INTO `annu_admin` (`ref_contact`, `type_admin`) VALUES ('C-000000-00001', 'Interne');";
$q->QUERY_SQL($sql,$server_database);
$sql="INSERT INTO `annu_collab` (`ref_contact`, `numero_secu`, `date_naissance`, `lieu_naissance`, `id_pays_nationalite`, `situation_famille`, `nbre_enfants`) VALUES ('C-000000-00001', '', '0000-00-00', '', NULL, '', NULL);";
$q->QUERY_SQL($sql,$server_database);	
$sql="INSERT INTO `annu_collab_fonctions` (`ref_contact`, `id_fonction`) VALUES ('C-000000-00001', 1);";
$q->QUERY_SQL($sql,$server_database);

$passw2=md5($appli_password);
$sql="INSERT INTO `users` (`ref_user`, `ref_contact`, `ref_coord_user`, `master`, `pseudo`, `code`, `actif`, `ordre`, `id_langage`, `last_id_interface`) VALUES
('U-000000-00001', 'C-000000-00001', 'COO-000000-00002', 1, '$appli_user', '$passw2', 1, 1, 1, 2);";
$q->QUERY_SQL($sql,$server_database);

$sq="INSERT INTO `users_permissions` (`ref_user`, `id_permission`, `value`) VALUES
('U-000000-00001', 1, 1),
('U-000000-00001', 3, 1),
('U-000000-00001', 5, 1),
('U-000000-00001', 6, 1),
('U-000000-00001', 7, 1),
('U-000000-00001', 8, 1),
('U-000000-00001', 9, 1),
('U-000000-00001', 10, 1),
('U-000000-00001', 11, 1),
('U-000000-00001', 12, 1),
('U-000000-00001', 13, 1),
('U-000000-00001', 14, 1),
('U-000000-00001', 15, 1),
('U-000000-00001', 16, 1),
('U-000000-00001', 17, 1),
('U-000000-00001', 18, 1),
('U-000000-00001', 19, 1);";
$q->QUERY_SQL($sql,$server_database);



events("Writing configurations");	
$conf="<?php\n";
$conf=$conf."\$bdd_hote = '$q->mysql_server;port=$q->mysql_port';\n"; 
$conf=$conf."\$bdd_user = '$user';\n";  
$conf=$conf."\$bdd_pass = '$mysql_password';\n";
$conf=$conf."\$bdd_base = '$server_database';\n";
$conf=$conf."?>";

@file_put_contents("$root/config/config_bdd.inc.php",$conf);
@chmod("$root/config/config_bdd.inc.php",0755);

$conf="<?php\n";
$conf=$conf."\$DIR = './';\n"; 
$conf=$conf."\$THIS_DIR = \$DIR;\n";  
$conf=$conf."?>";

@file_put_contents("$root/_dir.inc.php",$conf);
@chmod("$root/_dir.inc.php",0755);
	
	
}




function vhosts($noecho=false){
$ldap=new clladp();
$sock=new sockets();
$unix=new unix();

$ApacheGroupware=$sock->GET_INFO("ApacheGroupware");
if($ApacheGroupware==null){$ApacheGroupware=1;}
echo "Starting......: Apache Groupware enabled ? -> $ApacheGroupware\n";

$ApacheGroupwareListenIP=$sock->GET_INFO("ApacheGroupwareListenIP");
$ApacheGroupWarePort=$sock->GET_INFO("ApacheGroupWarePort");
$ApacheGroupWarePortSSL=$sock->GET_INFO("ApacheGroupWarePortSSL");
$SSLStrictSNIVHostCheck=$sock->GET_INFO("SSLStrictSNIVHostCheck");
$FreeWebsDisableSSLv2=$sock->GET_INFO("FreeWebsDisableSSLv2");

$d_path=$unix->APACHE_DIR_SITES_ENABLED();

if($ApacheGroupware==0){
	$ApacheGroupwareListenIP=$sock->GET_INFO("FreeWebListen");
	$ApacheGroupWarePort=$sock->GET_INFO("FreeWebListenPort");
	$ApacheGroupWarePortSSL=$sock->GET_INFO("FreeWebListenSSLPort");
	echo "Starting......: Apache Groupware switch to Apache source\n";

	foreach (glob("$d_path/groupware-artica-*") as $filename) {
		echo "Starting......: Apache Groupware removing ".basename($filename)."\n";
	}
}

if(!is_numeric($ApacheGroupWarePortSSL)){$ApacheGroupWarePortSSL=443;}
if(!is_numeric($ApacheGroupWarePort)){$ApacheGroupWarePort=80;}
if(!is_numeric($FreeWebsDisableSSLv2)){$FreeWebsDisableSSLv2=0;}
if($ApacheGroupwareListenIP==null){$ApacheGroupwareListenIP="*";}

	echo "Starting......: Apache Port....: $ApacheGroupwareListenIP:$ApacheGroupWarePort\n";
	echo "Starting......: Apache SSL Port: $ApacheGroupwareListenIP:$ApacheGroupWarePortSSL\n";

$pattern="(&(objectclass=apacheConfig)(apacheServerName=*))";
$attr=array();
$sr =@ldap_search($ldap->ldap_connection,$ldap->suffix,$pattern,$attr);
$hash=ldap_get_entries($ldap->ldap_connection,$sr);	

//print_r($hash);

for($i=0;$i<$hash["count"];$i++){
	$ApacheGroupWarePort_WRITE=$ApacheGroupWarePort;
	$root=$hash[$i]["apachedocumentroot"][0];
	$apacheservername=trim($hash[$i]["apacheservername"][0]);
	$wwwservertype=trim($hash[$i]["wwwservertype"][0]);
	if($wwwservertype=="WEBDAV"){continue;}
	if($wwwservertype=="BACKUPPC"){continue;}
	$wwwsslmode=$hash[$i]["wwwsslmode"][0];
	$DirectoryIndex="index.php";
	$magic_quotes_gpc="off";
	$adds=null;
	$ssl=null;
	
	
	
	if($wwwsslmode=="TRUE"){
		$ssl="\tSSLEngine on\n";
		$ssl=$ssl."\tSSLCertificateFile {$GLOBALS["SSLKEY_PATH"]}/$apacheservername.crt\n";
		$ssl=$ssl."\tSSLCertificateKeyFile {$GLOBALS["SSLKEY_PATH"]}/$apacheservername.key\n";
		if($FreeWebsDisableSSLv2==1){
			$ssl=$ssl."\tSSLProtocol -ALL +SSLv3 +TLSv1\n";
			$ssl=$ssl."\tSSLCipherSuite ALL:!aNULL:!ADH:!eNULL:!LOW:!EXP:RC4+RSA:+HIGH:+MEDIUM\n";
		}
		vhosts_BuildCertificate($apacheservername);
		$ApacheGroupWarePort_WRITE=$ApacheGroupWarePortSSL;
		$SSLMODE=true;
		$conf=$conf."\n<VirtualHost *:$ApacheGroupWarePort>\n";
		$conf=$conf."\tServerName $apacheservername\n";
		$conf=$conf."\tRedirect / https://$apacheservername\n";
		$conf=$conf."</VirtualHost>\n\n";
	}
	
	$open_basedir=$root;
	
	if($wwwservertype=="OBM2"){
		$adds=$adds."\tSetEnv OBM_INCLUDE_VAR obminclude\n";
		$adds=$adds."\tAddDefaultCharset ISO-8859-15\n";
		$adds=$adds."\tphp_value  include_path \".:/usr/share/php:/usr/share/php5:$root\"\n";
		$magic_quotes_gpc="On";
		$DirectoryIndex="obm.php";
		$alias="\tAlias /images $root/resources\n";
		$root="$root/php";
	}
	
	if($wwwservertype=="DRUPAL"){
		$DirectoryIndex="index.php";
		$adds=null;
		$adds=$adds."\tAddDefaultCharset ISO-8859-15\n";
		$adds=$adds."\tAccessFileName .htaccess\n";
		$rewrite[]="\t\t\t<IfModule mod_rewrite.c>";
		$rewrite[]="\t\t\t\tRewriteEngine on";
  		$rewrite[]="\t\t\t\tRewriteBase /";
   		$rewrite[]="\t\t\t\tRewriteCond %{REQUEST_FILENAME} !-f";
   		$rewrite[]="\t\t\t\tRewriteCond %{REQUEST_FILENAME} !-d";
   		$rewrite[]="\t\t\t\tRewriteRule ^(.*)$ index.php?q=$1 [L,QSA]";
   		$rewrite[]="\t\t\t</IfModule>";
        $rewrite[]="\t\t\t<FilesMatch \"\.(engine|inc|info|install|module|profile|po|sh|.*sql|theme|tpl(\.php)?|xtmpl)$|^(code-style\.pl|Entries.*|Repository|Root|Tag|Template)$\">";
        $rewrite[]="\t\t\t\tOrder allow,deny";
        $rewrite[]="\t\t\t\tdeny from all";
        $rewrite[]="\t\t\t</FilesMatch>";  	

        $dirplus[]="\t\t\t<Location /cron.php>";
        $dirplus[]="\t\t\t\tOrder deny,allow";
        $dirplus[]="\t\t\t\tdeny from all";
        $dirplus[]="\t\t\t\tallow from 127.0.0.1";
        $dirplus[]="\t\t\t\tallow from IP";
    	$dirplus[]="\t\t\t</Location>";
        
		$root="/usr/share/drupal";
		@mkdir("/usr/share/drupal/sites/$apacheservername/files",0755,true);
		@chmod("/usr/share/drupal/sites/$apacheservername/files",0777);
	}	
	
	
	if($wwwservertype=="SQUID_STATS"){
		$DirectoryIndex="squid.logon.php";
		$open_basedir="/usr/share/artica-postfix/ressources:/usr/share/artica-postfix:/usr/share/artica-postfix/framework:$root:$root/resources:$root/ressources/logs";
	}
	
	
	if($wwwservertype=="GROUPOFFICE"){$open_basedir=null;}
	
	
	if($wwwservertype=="ARTICA_USR"){$open_basedir="/usr/share/artica-postfix/ressources:/usr/share/artica-postfix:/usr/share/artica-postfix/framework:$root:$root/resources:$root/ressources/logs";}
	
	if($GLOBALS["VERBOSE"]){echo " *** OPENBASE DIR: $wwwservertype *** \n";}
	if($GLOBALS["VERBOSE"]){echo " *** OPENBASE DIR: $open_basedir *** \n";}
	
	
	
	@mkdir("$root/php_logs/$apacheservername",0755,true);
	$conf=$conf."\n\n<VirtualHost $ApacheGroupwareListenIP:$ApacheGroupWarePort_WRITE>\n";
	$conf=$conf."\tServerName $apacheservername\n";
	$conf=$conf."\tServerAdmin webmaster@$apacheservername\n";
	$conf=$conf."\tDocumentRoot $root\n";
	$conf=$conf.$ssl;
	$conf=$conf.$alias;
	$conf=$conf.$adds;
	
	
	$conf=$conf."\tphp_value  error_log  \"$root/php_logs/$apacheservername/php.log\"\n";  
	if($open_basedir==null){
		$conf=$conf."\tphp_value open_basedir \"$root\"\n";
	} 
	$conf=$conf."\tphp_value magic_quotes_gpc $magic_quotes_gpc\n";	
	
	$conf=$conf."\t<Directory \"$root\">\n";
	if(is_array($rewrite)){$conf=$conf.@implode("\n",$rewrite)."\n";}	
	$conf=$conf."\t\t\tDirectoryIndex $DirectoryIndex\n";
	$conf=$conf."\t\t\tOptions Indexes FollowSymLinks MultiViews\n";
	$conf=$conf."\t\t\tAllowOverride all\n";
	$conf=$conf."\t\t\tOrder allow,deny\n";
	$conf=$conf."\t\t\tAllow from all\n";

	$conf=$conf."\t</Directory>\n";
	if(is_array($dirplus)){$conf=$conf.@implode("\n",$dirplus)."\n";}	
	$conf=$conf."\tCustomLog /usr/local/apache-groupware/logs/{$apacheservername}_access.log \"%h %l %u %t \\\"%r\\\" %>s %b \\\"%{Referer}i\\\" \\\"%{User-Agent}i\\\" %V\"\n";
	$conf=$conf."\tErrorLog /usr/local/apache-groupware/logs/{$apacheservername}_err.log\n";
	$conf=$conf."</VirtualHost>\n";
	
	if($ApacheGroupware==0){
		$a2ensite=$unix->find_program("a2ensite");
		@mkdir($d_path,0755,true);
		echo "Starting......: Apache Groupware adding $d_path/groupware-artica-$apacheservername.conf\n";
		@file_put_contents("$d_path/groupware-artica-$apacheservername.conf",$conf);
		if(is_file($a2ensite)){shell_exec("$a2ensite $d_path/groupware-artica-$apacheservername.conf");}
		$conf=null;
	}
	
	
	
}
if($SSLMODE){
	if($SSLStrictSNIVHostCheck==1){$SSLStrictSNIVHostCheck="\nSSLStrictSNIVHostCheck off";}
	$conf="Listen $ApacheGroupWarePortSSL$SSLStrictSNIVHostCheck\nNameVirtualHost *:$ApacheGroupWarePortSSL\n".$conf;
}

$mailmanhosts=mailmanhosts();
if($ApacheGroupware==0){
		echo "Starting......: Apache Groupware adding $d_path/groupware-artica-mailmanhosts.conf\n";
		@file_put_contents("$d_path/groupware-artica-mailmanhosts.conf",$mailmanhosts);
		$apache2ctl=$unix->LOCATE_APACHE_CTL();
		if(is_file($apache2ctl)){shell_exec("$apache2ctl -k restart");}
	}
$conf=$conf.$mailmanhosts;

if($noecho){return $conf;}
echo $conf;
	
}

function ROUNDCUBE_SRC_FOLDER(){
	if(is_file('/usr/share/roundcube/index.php')){return '/usr/share/roundcube';}
	if(is_file('/usr/share/roundcubemail/index.php')){return '/usr/share/roundcubemail';}	
	
}

function OPENGOO_TEST_FILES($root){
	$file="/usr/share/artica-postfix/bin/install/opengoo/files.txt";
	if(!is_file($file)){return false;}
	$tbl=explode("\n",@file_get_contents($file));
	while (list ($num, $file) = each ($tbl) ){
			if($file==null){continue;}
			if(!is_file("$root/$file")){
				events("Starting install OpenGoo $root/$file does not exists");
				return false;	
			}
	}

	return true;
	
}

function GROUPOFFICE_TEST_FILES($root){
	$file="/usr/share/artica-postfix/bin/install/opengoo/group-office.txt";
	if(!is_file($file)){return false;}
	$tbl=explode("\n",@file_get_contents($file));
	while (list ($num, $file) = each ($tbl) ){
			if($file==null){continue;}
			if(!is_file("$root/$file")){
				events("Starting install GroupOffice $root/$file does not exists");
				return false;	
			}
	}

	return true;	
}

function OPENGOO_INSTALL($servername,$root,$hash=array()){
	$srcfolder="/usr/local/share/artica/opengoo";
	$GLOBALS["ADDLOG"]="/var/log/artica-postfix/$servername.log";	
	$sql_file="/usr/share/artica-postfix/bin/install/opengoo/opengoo.sql";



	if($root==null){events("Starting install opengoo Unable to stat root dir");return false;}
	if(!is_dir($srcfolder)){
		events("Starting install opengoo Unable to stat SRC");
		return false;
	}
	
	
	$user=$hash["wwwmysqluser"][0];
	$mysql_password=$hash[strtolower("WWWMysqlPassword")][0];
	
	$appli_user=$hash["wwwappliuser"][0];
	$appli_password=$hash["wwwapplipassword"][0];	
	$wwwsslmode=$hash["wwwsslmode"][0];
		
	$server_database=str_replace(".","_",$servername);
	$server_database=str_replace(" ","_",$server_database);
	$server_database=str_replace("-","_",$server_database);	
	events("Starting install opengoo sub-system mysql database $server_database...");	
	
	if($user==null){events("Starting install opengoo Unable to stat Mysql username");return false;}
	if($mysql_password==null){events("Starting install opengoo Unable to stat Mysql password");return false;}
	@mkdir($root,0755,true);
	
	events("Starting install opengoo sub-system mysql database $server_database...");
	$q=new mysql();
	if(!$q->DATABASE_EXISTS($server_database)){
		$q->CREATE_DATABASE($server_database);
	}
	
	if(!$q->DATABASE_EXISTS($server_database)){
		events("Starting install opengoo unable to create MYSQL Database");
		return false;
	}
	
	events("Starting setting permissions on Database with user $user");
	$q->PRIVILEGES($user,$mysql_password,$server_database);
	
	
	if(!OPENGOO_TEST_FILES($root)){
		events("Starting install opengoo installing source code");
		shell_exec("/bin/cp -rf $srcfolder/* $root/");
	}
	
	$opengoo=new opengoo(null,$server_database);
	if(!OPENGOO_CHECK_TABLES($server_database)){
		if($q->mysql_password<>null){
			$password=" --password=$q->mysql_password ";
		}else{
			events("Starting install opengoo installing tables datas with null password");
		}
		
		$cmd="mysql --port=$q->mysql_port --skip-column-names --database=$server_database --silent --xml ";
		$cmd=$cmd." --user=$q->mysql_admin$password <$sql_file";
		shell_exec($cmd);	
	}else{
		events("Starting install opengo Mysql tables are already installed");
		
	}
	
	$sock=new sockets();
	$ApacheGroupWarePort=$sock->GET_INFO("ApacheGroupWarePort");
	events("Starting install opengo SSL=$wwwsslmode");
	if($wwwsslmode=="TRUE"){
		$ROOT_URL="https://$servername";
	}else{
		$ROOT_URL="http://$servername:$ApacheGroupWarePort";
	}
	$conf="<?php\n";
	$conf=$conf."define('DB_ADAPTER', 'mysql');\n"; 
	$conf=$conf."define('DB_HOST', '127.0.0.1');\n";
	$conf=$conf."define('DB_USER', '$q->mysql_admin');\n"; 
	$conf=$conf."define('DB_PASS', '$q->mysql_password');\n"; 
	$conf=$conf."define('DB_NAME', '$server_database');\n"; 
	$conf=$conf."define('DB_PERSIST', true);\n"; 
	$conf=$conf."define('TABLE_PREFIX', 'og_');\n"; 
	$conf=$conf."define('DB_ENGINE', 'InnoDB');\n"; 
	$conf=$conf."define('ROOT_URL', '$ROOT_URL');\n"; 
	$conf=$conf."define('DEFAULT_LOCALIZATION', 'en_us');\n"; 
	$conf=$conf."define('COOKIE_PATH', '/');\n"; 
	$conf=$conf."define('DEBUG', false);\n"; 
	$conf=$conf."define('SEED', '6eb2551152da5a57576754716397703c');\n"; 
	$conf=$conf."define('DB_CHARSET', 'utf8');\n"; 
	$conf=$conf."return true;\n";
	$conf=$conf."?>";
	
	@file_put_contents("$root/config/config.php",$conf);
	
	$opengoo->DefaultsValues();
	events("updating administrator credentials");
	$opengoo->www_servername=$servername;
	$opengoo->UpdateAdmin($appli_user,$appli_password);
	events("updating company name");
	$ou=$opengoo->get_Organization($servername);
	$opengoo->UpdateCompany($ou);
	$unix=new unix();
	$sock=new sockets();
	sys_THREAD_COMMAND_SET( LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.opengoo.php");
	
	

}




function GROUPOFFICE_INSTALL($servername,$root,$hash=array()){
	$srcfolder="/usr/local/share/artica/group-office";
	$GLOBALS["ADDLOG"]="/var/log/artica-postfix/$servername.log";	
	$sql_file="/usr/share/artica-postfix/bin/install/opengoo/group-office.sql";
	$sql_datas="/usr/share/artica-postfix/bin/install/opengoo/group-office-datas.sql";


	if($root==null){events("Starting install GroupOffice Unable to stat root dir");return false;}
	if(!is_dir($srcfolder)){
		events("Starting install GroupOffice Unable to stat SRC");
		return false;
	}
	
	
	$user=$hash["wwwmysqluser"][0];
	$mysql_password=$hash[strtolower("WWWMysqlPassword")][0];
	
	$appli_user=$hash["wwwappliuser"][0];
	$appli_password=$hash["wwwapplipassword"][0];	
	$wwwsslmode=$hash["wwwsslmode"][0];
	$ou=$hash["OU"][0];
	$server_database=str_replace(".","_",$servername);
	$server_database=str_replace(" ","_",$server_database);
	$server_database=str_replace("-","_",$server_database);	
	events("Starting install GroupOffice sub-system mysql database $server_database...");	
	
	if($user==null){events("Starting install GroupOffice Unable to stat Mysql username");return false;}
	if($mysql_password==null){events("Starting install GroupOffice Unable to stat Mysql password");return false;}
	@mkdir($root,0755,true);
	
	events("Starting install GroupOffice sub-system mysql database $server_database...");
	$q=new mysql();
	if(!$q->DATABASE_EXISTS($server_database)){
		$q->CREATE_DATABASE($server_database);
	}
	
	if(!$q->DATABASE_EXISTS($server_database)){
		events("Starting install GroupOffice unable to create MYSQL Database");
		return false;
	}
	
	events("Starting setting permissions on Database with user $user");
	$q->PRIVILEGES($user,$mysql_password,$server_database);

	
	if(!GROUPOFFICE_TEST_FILES($root)){
		events("Starting install GroupOffice installing source code");
		shell_exec("/bin/cp -rf $srcfolder/* $root/");
		@mkdir("/home/groupoffice/$servername",0777,true);
	 }
	 @mkdir("/home/groupoffice/$servername",0777,true);
	 $unix=new unix();
	 $apacheuser=$unix->APACHE_GROUPWARE_ACCOUNT();
	 events("chown /home/groupoffice has $apacheuser");
	 shell_exec("/bin/chown -R $apacheuser /home/groupoffice");
	
	
	if($q->mysql_password<>null){
		$password=" --password=$q->mysql_password ";
	}else{
		events("Starting install GroupOffice installing tables datas with null password");
	}
		
	$cmd="mysql --port=$q->mysql_port --skip-column-names --database=$server_database --silent --xml ";
	$cmd=$cmd." --user=$q->mysql_admin$password <$sql_file";
	shell_exec($cmd);	
	
	$sock=new sockets();
	$ApacheGroupWarePort=$sock->GET_INFO("ApacheGroupWarePort");
	events("Starting install opengo SSL=$wwwsslmode");
	if($wwwsslmode=="TRUE"){
		$ROOT_URL="https://$servername";
	}else{
		$ROOT_URL="http://$servername:$ApacheGroupWarePort";
	}
	
	$q=new mysql();
		
		$conf[]="<?php";
		$conf[]="\$config['enabled']=true;";
		$conf[]="\$config['id']=\"groupoffice\";";
		$conf[]="\$config['debug']=false;";
		$conf[]="\$config['log']=false;";
		$conf[]="\$config['language']=\"en\";";
		$conf[]="\$config['default_country']=\"FR\";";
		$conf[]="\$config['default_timezone']=\"Europe/Amsterdam\";";
		$conf[]="\$config['default_currency']=\"€\";";
		$conf[]="\$config['default_date_format']=\"dmY\";";
		$conf[]="\$config['default_date_separator']=\"-\";";
		$conf[]="\$config['default_time_format']=\"G:i\";";
		$conf[]="\$config['default_first_weekday']=\"1\";";
		$conf[]="\$config['default_decimal_separator']=\",\";";
		$conf[]="\$config['default_thousands_separator']=\".\";";
		$conf[]="\$config['theme']=\"Default\";";
		$conf[]="\$config['allow_themes']=true;";
		$conf[]="\$config['allow_password_change']=false;";
		$conf[]="\$config['allow_profile_edit']=true;";
		$conf[]="\$config['allow_registration']=false;";
		$conf[]="\$config['registration_fields']=\"title_initials,sex,birthday,address,home_phone,fax,cellular,company,department,function,work_address,work_phone,work_fax,homepage\";";
		$conf[]="\$config['required_registration_fields']=\"company,address\";";
		$conf[]="\$config['allow_duplicate_email']=false;";
		$conf[]="\$config['auto_activate_accounts']=false;";
		$conf[]="\$config['notify_admin_of_registration']=true;";
		$conf[]="\$config['register_modules_read']=\"summary,email,calendar,tasks,addressbook,files,notes,links,tools,comments\";";
		$conf[]="\$config['register_modules_write']=\"\";";
		$conf[]="\$config['allowed_modules']=\"\";";
		$conf[]="\$config['register_user_groups']=\"\";";
		$conf[]="\$config['register_visible_user_groups']=\",\";";
		$conf[]="\$config['host']=\"/\";";
		$conf[]="\$config['force_login_url']=false;";
		$conf[]="\$config['full_url']=\"$ROOT_URL/\";";
		$conf[]="\$config['title']=\"Group-Office\";";
		$conf[]="\$config['webmaster_email']=\"webmaster@example.com\";";
		$conf[]="\$config['root_path']=\"$root/\";";
		$conf[]="\$config['tmpdir']=\"/tmp/\";";
		$conf[]="\$config['max_users']=\"0\";";
		$conf[]="\$config['quota']=\"0\";";
		$conf[]="\$config['db_type']=\"mysql\";";
		$conf[]="\$config['db_host']=\"$q->mysql_server\";";
		$conf[]="\$config['db_name']=\"$server_database\";";
		$conf[]="\$config['db_user']=\"$user\";";
		$conf[]="\$config['db_pass']=\"$mysql_password\";";
		$conf[]="\$config['db_port']=\"$q->mysql_port\";";
		$conf[]="\$config['db_socket']=\"\";";
		$conf[]="\$config['file_storage_path']=\"/home/groupoffice/$servername/\";";
		$conf[]="\$config['max_file_size']=\"10000000\";";
		$conf[]="\$config['smtp_server']=\"127.0.0.1\";";
		$conf[]="\$config['smtp_port']=\"25\";";
		$conf[]="\$config['smtp_username']=\"\";";
		$conf[]="\$config['smtp_password']=\"\";";
		$conf[]="\$config['smtp_encryption']=\"\";";
		$conf[]="\$config['smtp_local_domain']=\"\";";
		$conf[]="\$config['restrict_smtp_hosts']=\"\";";
		$conf[]="\$config['max_attachment_size']=\"10000000\";";
		$unix=new unix();
		$ldap=new clladp();
		$zip=$unix->find_program("zip");
		$unzip=$unix->find_program("unzip");
		$xml2wbxml=$unix->find_program("xml2wbxml");
		$conf[]="\$config['cmd_zip']=\"$zip\";";
		$conf[]="\$config['cmd_unzip']=\"$unzip\";";
		$conf[]="\$config['cmd_tar']=\"/bin/tar\";";
		$conf[]="\$config['cmd_chpasswd']=\"/usr/sbin/chpasswd\";";
		$conf[]="\$config['cmd_sudo']=\"/usr/bin/sudo\";";
		$conf[]="\$config['cmd_xml2wbxml']=\"$xml2wbxml\";";
		$conf[]="\$config['cmd_wbxml2xml']=\"/usr/bin/wbxml2xml\";";
		$conf[]="\$config['cmd_tnef']=\"/usr/bin/tnef\";";
		$conf[]="\$config['cmd_php']=\"php\";";
		$conf[]="\$config['phpMyAdminUrl']=\"\";";
		$conf[]="\$config['allow_unsafe_scripts']=\"\";";
		$conf[]="\$config['default_password_length']=\"6\";";
		$conf[]="\$config['session_inactivity_timeout']=\"0\"";		
		//$conf[]="\$config['ldap_host']='$ldap->ldap_host';";	
		//$conf[]="\$config['ldap_user']='$ldap->ldap_admin';";	
		//$conf[]="\$config['ldap_pass']='$ldap->ldap_password';";	
		//$conf[]="\$config['ldap_basedn']='ou=$ou,dc=organizations,$ldap->suffix';";	
		//$conf[]="\$config['ldap_peopledn']='ou=users,ou=$ou,dc=organizations,$ldap->suffix';";	
		//$conf[]="\$config['ldap_groupsdn']='ou=groups,ou=$ou,dc=organizations,$ldap->suffix';";
		$conf[]="?>";		
		@file_put_contents("$root/config.php",implode("\n",$conf));
		
		$sql = "UPDATE go_users SET password='".md5($appli_password)."',username='$appli_user' WHERE id='1'";
		$q=new mysql();
		$q->QUERY_SQL($sql,$server_database);
		
		events("Starting install GroupOffice $root/config.php done...");
}




function ROUNDCUBE_INSTALL($servername,$root,$hash=array()){
	$srcfolder=ROUNDCUBE_SRC_FOLDER();
	$sock=new sockets();
	$ldap=new clladp();
	$EnablePostfixMultiInstance=$sock->GET_INFO("EnablePostfixMultiInstance");
	
	echo "Starting......: Roundcube $servername\n"; 
	
	$GLOBALS["ADDLOG"]="/var/log/artica-postfix/$servername.log";	
	if($root==null){events("Starting install roundcube Unable to stat root dir");return false;}
	if(!is_dir($srcfolder)){
		events("Starting install roundcube Unable to stat SRC");
		return false;
	}
	$sql_file="$srcfolder/SQL/mysql.initial.sql";
	
	if(!is_file($sql_file)){
		events("Starting install roundcube Unable to stat $srcfolder");
		return false;
	}
	

	$user=$hash["wwwmysqluser"][0];
	$mysql_password=$hash[strtolower("WWWMysqlPassword")][0];
			
	$server_database=str_replace(".","_",$servername);
	$server_database=str_replace("-","_",$server_database);	
	events("Starting install roundcube sub-system mysql database $server_database...");	
	
	if($user==null){events("Starting install roundcube Unable to stat Mysql username");return false;}
	if($mysql_password==null){events("Starting install roundcube Unable to stat Mysql password");return false;}
	@mkdir($root,0755,true);
	
	events("Starting install roundcube sub-system mysql database $server_database...");
	$q=new mysql();
	$q->CREATE_DATABASE($server_database);
	
	if(!$q->DATABASE_EXISTS($server_database)){
		events("Starting install roundcube unable to create MYSQL Database");
		return false;
	}
	
	events("Starting setting permissions on Database with user $user");
	echo "Starting......: Roundcube $servername set permissions on Database with user $user\n"; 
	$q->PRIVILEGES($user,$mysql_password,$server_database);
	
	
	events("Starting install roundcube installing source code");
	echo "Starting......: Roundcube $servername installing source code\n"; 
	
	shell_exec("/bin/rm -rf $root/*");
	shell_exec("/bin/cp -rf $srcfolder/* $root/");
	if($q->mysql_password<>null){
		$password=" --password=$q->mysql_password ";
	}else{
		events("Starting install roundcube installing tables datas with null password");
	}

	$files[]=$sql_file;
	$files[]="$srcfolder/SQL/mysql.update.sql";
	
	while (list ($num, $line) = each ($files) ){
		events("Starting install roundcube installing tables $server_database/$num");
		$cmd="mysql --port=$q->mysql_port --skip-column-names --database=$server_database --silent --xml ";
		$cmd=$cmd." --user=$q->mysql_admin$password <$line";
		shell_exec($cmd);
		
		events("Starting install roundcube installing datas $server_database/$num");
		$cmd="mysql --port=$q->mysql_port --skip-column-names --database=$server_database --silent --xml ";
		$cmd=$cmd." --user=$q->mysql_admin$password <$sql_datas";
		shell_exec($cmd);		
		

	}

	if(is_file("$root/plugins/subscriptions_option/subscriptions_option.php")){$subscriptions_option=1;}
	

	$q->checkRoundCubeTables($server_database);
	$conf[]="<?php";
	$conf[]="\$rcmail_config = array();";
	$conf[]="\$rcmail_config[\"db_dsnw\"] = \"mysql://$q->mysql_admin:$q->mysql_password@$q->mysql_server/$server_database\";";
	$conf[]="\$rcmail_config[\"db_dsnr\"] = \"\";";
	$conf[]="\$rcmail_config[\"db_max_length\"] = 512000;  // 500K";
	$conf[]="\$rcmail_config[\"db_persistent\"] = FALSE;";
	$conf[]="\$rcmail_config[\"db_table_users\"] = \"users\";";
	$conf[]="\$rcmail_config[\"db_table_identities\"] = \"identities\";";
	$conf[]="\$rcmail_config[\"db_table_contacts\"] = \"contacts\";";
	$conf[]="\$rcmail_config[\"db_table_session\"] = \"session\";";
	$conf[]="\$rcmail_config[\"db_table_cache\"] = \"cache\";";
	$conf[]="\$rcmail_config[\"db_table_messages\"] = \"messages\";";
	$conf[]="\$rcmail_config[\"db_sequence_users\"] = \"user_ids\";";
	$conf[]="\$rcmail_config[\"db_sequence_identities\"] = \"identity_ids\";";
	$conf[]="\$rcmail_config[\"db_sequence_contacts\"] = \"contact_ids\";";
	$conf[]="\$rcmail_config[\"db_sequence_cache\"] = \"cache_ids\";";
	$conf[]="\$rcmail_config[\"db_sequence_messages\"] = \"message_ids\";";
	$conf[]="?>";
	events("Starting install roundcube saving $root/config/db.inc.php");
	echo "Starting......: Roundcube $servername db.inc.php OK\n";
	@file_put_contents("$root/config/db.inc.php",@implode("\n",$conf));	
	
	unset($conf);
	
	$wwwmultismtpsender=$hash["wwwmultismtpsender"][0];
	$WWWEnableAddressBook=$hash["wwwenableaddressbook"][0];
	events("OU={$hash["OU"][0]} EnablePostfixMultiInstance=$EnablePostfixMultiInstance, SMTP=$wwwmultismtpsender");
	
	
	
	$conf[]="<?php";
	$conf[]="\$rcmail_config = array();";
	$conf[]="\$rcmail_config['debug_level'] =1;";
	$conf[]="\$rcmail_config['enable_caching'] = TRUE;";
	$conf[]="\$rcmail_config['message_cache_lifetime'] = '10d';";
	$conf[]="\$rcmail_config['auto_create_user'] = TRUE;";
	$conf[]="\$rcmail_config['default_host'] = '127.0.0.1';";
	$conf[]="\$rcmail_config['default_port'] = 143;";
	
	if($EnablePostfixMultiInstance==1){
		if(trim($wwwmultismtpsender)<>null){
		$conf[]="// SMTP server used for sending mails.";
		$conf[]="\$rcmail_config['smtp_server'] = '$wwwmultismtpsender';";
		$conf[]="\$rcmail_config['smtp_port'] = 25;"; 
		
	} }else{
		$conf[]="\$rcmail_config['smtp_server'] = '127.0.0.1';";
		$conf[]="\$rcmail_config['smtp_port'] = 25;"; 	
	}
	
	
	$conf[]="\$rcmail_config['smtp_user'] = '';";
	$conf[]="\$rcmail_config['smtp_pass'] = '';";	
	$conf[]="\$rcmail_config['smtp_auth_type'] = '';";
	$conf[]="\$rcmail_config['smtp_helo_host'] = '';";
	$conf[]="\$rcmail_config['smtp_log'] = TRUE;";
	$conf[]="\$rcmail_config['username_domain'] = '';";
	$conf[]="\$rcmail_config['mail_domain'] = '';";
	$conf[]="\$rcmail_config['virtuser_file'] = '';";
	$conf[]="\$rcmail_config['virtuser_query'] = '';";
	$conf[]="\$rcmail_config['list_cols'] = array('subject', 'from', 'date', 'size');";
	$conf[]="\$rcmail_config['skin_path'] = 'skins/default/';";
	$conf[]="\$rcmail_config['skin_include_php'] = FALSE;";
	$conf[]="#LOGS";
	$conf[]="\$rcmail_config['log_driver'] ='syslog';";
	$conf[]="\$rcmail_config['syslog_id'] = 'roundcube-$servername';";	
	$conf[]="\$rcmail_config['temp_dir'] = 'temp/';";
	$conf[]="\$rcmail_config['log_dir'] = 'logs/';";
	$conf[]="\$rcmail_config['session_lifetime'] = 10;";
	$conf[]="\$rcmail_config['ip_check'] = false;";
	$conf[]="\$rcmail_config['double_auth'] = false;";
	$conf[]="\$rcmail_config['des_key'] = 'NIbXC7RaFsZvQTV5NWBbQd9H';";
	$conf[]="\$rcmail_config['locale_string'] = 'us';";
	$conf[]="\$rcmail_config['date_short'] = 'D H:i';";
	$conf[]="\$rcmail_config['date_long'] = 'd.m.Y H:i';";
	$conf[]="\$rcmail_config['date_today'] = 'H:i';";
	$conf[]="\$rcmail_config['useragent'] = 'RoundCube Webmail/0.1-rc2';";
	$conf[]="\$rcmail_config['product_name'] = 'RoundCube Webmail for {$hash["OU"][0]}';";
	$conf[]="\$rcmail_config['imap_root'] = null;";
	$conf[]="\$rcmail_config['drafts_mbox'] = 'Drafts';";
	$conf[]="\$rcmail_config['junk_mbox'] = 'Junk';";
	$conf[]="\$rcmail_config['sent_mbox'] = 'Sent';";
	$conf[]="\$rcmail_config['trash_mbox'] = 'Trash';";
	$conf[]="\$rcmail_config['default_imap_folders'] = array('INBOX', 'Drafts', 'Sent', 'Junk', 'Trash');";
	$conf[]="\$rcmail_config['protect_default_folders'] = TRUE;";
	$conf[]="\$rcmail_config['skip_deleted'] = TRUE;";
	$conf[]="\$rcmail_config['read_when_deleted'] = TRUE;";
	$conf[]="\$rcmail_config['flag_for_deletion'] = TRUE;";
	$conf[]="\$rcmail_config['enable_spellcheck'] = TRUE;";
	$conf[]="\$rcmail_config['spellcheck_uri'] = '';";
	$conf[]="\$rcmail_config['spellcheck_languages'] = NULL;";
	$conf[]="\$rcmail_config['generic_message_footer'] = '';";
	$conf[]="\$rcmail_config['mail_header_delimiter'] = NULL;";
	$conf[]="";
	
	if($WWWEnableAddressBook==1){
		$conf[]="\$rcmail_config['ldap_public']['{$hash["OU"][0]}'] = array(";
		$conf[]="	'name'          => '{$hash["OU"][0]}',";
		$conf[]="	'hosts'         => array('$ldap->ldap_host'),";
		$conf[]="	'port'          => $ldap->ldap_port,";
		$conf[]="	'base_dn'       => 'ou={$hash["OU"][0]},dc=organizations,$ldap->suffix',";
		$conf[]="	'bind_dn'       => 'cn=$ldap->ldap_admin,$ldap->suffix',";
		$conf[]="	'bind_pass'     => '$ldap->ldap_password',";
		$conf[]="	'ldap_version'  => 3,       // using LDAPv3";
		$conf[]="	'search_fields' => array('mail', 'cn','uid','givenName','DisplayName'),  // fields to search in";
		$conf[]="	'name_field'    => 'cn',    // this field represents the contact's name";
		$conf[]="	'email_field'   => 'mail',  // this field represents the contact's e-mail";
		$conf[]="	'surname_field' => 'sn',    // this field represents the contact's last name";
		$conf[]="	'firstname_field' => 'gn',  // this field represents the contact's first name";
		$conf[]="	'scope'         => 'sub',   // search mode: sub|base|list";
		$conf[]="	'LDAP_Object_Classes' => array( 'person', 'inetOrgPerson', 'userAccount'),";
		$conf[]="	'filter'        => 'givenName=*',      // used for basic listing (if not empty) and will be &'d with search queries. ex: (status=act)";
		$conf[]="	'fuzzy_search'  => true);   // server allows wildcard search";
	}
	$conf[]="// enable composing html formatted messages (experimental)";
	$conf[]="\$rcmail_config['enable_htmleditor'] = TRUE;";
	$conf[]="\$rcmail_config['dont_override'] =array('index_sort','trash_mbox','sent_mbox','junk_mbox','drafts_mbox','subscriptions_option');";
	$conf[]="\$rcmail_config['javascript_config'] = array('read_when_deleted', 'flag_for_deletion');";
	$conf[]="\$rcmail_config['include_host_config'] = FALSE;";
	$conf[]="";
	$conf[]="";
	$conf[]="/***** these settings can be overwritten by user's preferences *****/";
	$conf[]="";
	$conf[]="// show up to X items in list view";
	$conf[]="\$rcmail_config['pagesize'] = 40;";
	$conf[]="";
	$conf[]="// use this timezone to display date/time";
	$conf[]="\$rcmail_config['timezone'] = intval(date('O'))/100 - date('I');";
	$conf[]="";
	$conf[]="// is daylight saving On?";
	$conf[]="\$rcmail_config['dst_active'] = (bool)date('I');";
	$conf[]="";
	$conf[]="// prefer displaying HTML messages";
	$conf[]="\$rcmail_config['prefer_html'] = TRUE;";
	$conf[]="";
	$conf[]="// show pretty dates as standard";
	$conf[]="\$rcmail_config['prettydate'] = TRUE;";
	$conf[]="";
	$conf[]="// default sort col";
	$conf[]="\$rcmail_config['message_sort_col'] = 'date';";
	$conf[]="";
	$conf[]="// default sort order";
	$conf[]="\$rcmail_config['message_sort_order'] = 'DESC';";
	$conf[]="";
	$conf[]="// save compose message every 300 seconds (5min)";
	$conf[]="\$rcmail_config['draft_autosave'] = 300;";
	$conf[]="";
	$conf[]="/***** PLUGINS for Roundcube V3 *****/";
	$conf[]="\$rcmail_config['plugins'] = array();";
	
	
	ROUNDCUBE_CONTEXTMENU($root);
	if(is_file("$root/plugins/contextmenu/contextmenu.php")){
		$conf[]="\$rcmail_config['plugins'][] = 'contextmenu';";
	}
	if($subscriptions_option==1){
		$conf[]="\$rcmail_config['plugins'][] = 'subscriptions_option';";
	}
	
	$NAB=new roundcube_globaladdressbook($servername);
	if($NAB->enabled==1){
		ROUNDCUBE_GLOBALADDRESSBOOK();
		if(is_file("$root/plugins/globaladdressbook/globaladdressbook.php")){
			echo "Starting......: Roundcube $servername Enable Global AddressBook \n";
			$conf[]="\$rcmail_config['plugins'][] = 'globaladdressbook';";
			$nab_conf=$NAB->BuildConfig();
			@file_put_contents("$root/plugins/globaladdressbook/config.inc.php",$nab_conf);
			shell_exec("/bin/chmod -R 770 $root/plugins/globaladdressbook");
			shell_exec("/bin/chmod 660 $root/plugins/globaladdressbook/*.php");
			chmod("$root/plugins/globaladdressbook/config.inc.php",755);
			
		}
	}
	$roundcube_class=new roundcube();
	if(!is_file("$root/plugins/remember_me/remember_me.php")){$roundcube_class->plugin_install($root,"remember_me");}
	if(!is_file("$root/plugins/msglistcols/msglistcols.php")){$roundcube_class->plugin_install($root,"msglistcols");}
	if(!is_file("$root/plugins/sticky_notes/sticky_notes.php")){$roundcube_class->plugin_install($root,"sticky_notes");}
	if(!is_file("$root/plugins/jqueryui/jqueryui.php")){$roundcube_class->plugin_install($root,"jqueryui");}
	if(!is_file("$root/plugins/dkimstatus/dkimstatus.php")){$roundcube_class->plugin_install($root,"dkimstatus");}
	if(!is_file("$root/plugins/fail2ban/fail2ban.php")){$roundcube_class->plugin_install($root,"fail2ban");}
	
	if(is_file("$root/plugins/remember_me/remember_me.php")){$conf[]="\$rcmail_config['plugins'][] = 'remember_me';";}
	if(is_file("$root/plugins/msglistcols/msglistcols.php")){$conf[]="\$rcmail_config['plugins'][] = 'msglistcols';";}
	if(is_file("$root/plugins/dkimstatus/dkimstatus.php")){$conf[]="\$rcmail_config['plugins'][] = 'dkimstatus';";}
	if(is_file("$root/plugins/fail2ban/fail2ban.php")){$conf[]="\$rcmail_config['plugins'][] = 'fail2ban';";}
	if($roundcube_class->plugin_password($root,$hash["OU"][0])){
		if(is_file("$root/plugins/dkimstatus/dkimstatus.php")){$conf[]="\$rcmail_config['plugins'][] = 'password';";}
	}
	
	if(is_file("$root/plugins/jqueryui/jqueryui.php")){
		$conf[]="\$rcmail_config['plugins'][] = 'jqueryui';";
		$roundcube_class->plugin_jqueryui($root);
		if(is_file("$root/plugins/sticky_notes/sticky_notes.php")){$conf[]="\$rcmail_config['plugins'][] = 'sticky_notes';";}	
	
	}
	
	
	if(is_file("$root/plugins/sieverules/sieverules.php")){
		$users=new usersMenus();
		$sieverules_port=4190;
		if(is_numeric($users->SIEVE_PORT)){if($users->SIEVE_PORT>0){$sieverules_port=$users->SIEVE_PORT;}}
		echo "Starting......: Roundcube (php) sieverules_port ($sieverules_port)\n";		
		$conf[]="\$rcmail_config['plugins'][] = 'sieverules';";
		$sieve[]="<?php";
		$sieve[]="\$rcmail_config[\"sieverules_host\"] = \"127.0.0.1\";";
		$sieve[]="\$rcmail_config[\"sieverules_port\"] = $sieverules_port;";
		$sieve[]="\$rcmail_config[\"sieverules_usetls\"] = FALSE;";
		$sieve[]="\$rcmail_config[\"sieverules_folder_delimiter\"] = null;";
		$sieve[]="\$rcmail_config[\"sieverules_folder_encoding\"] = null;";
		$sieve[]="\$rcmail_config[\"sieverules_include_imap_root\"] = null;";
		$sieve[]="\$rcmail_config[\"sieverules_ruleset_name\"] = \"roundcube\";";
		$sieve[]="\$rcmail_config[\"sieverules_multiple_actions\"] = TRUE;";
		$sieve[]="\$rcmail_config[\"sieverules_allowed_actions\"] = array(\"fileinto\" => TRUE,\"vacation\" => TRUE,\"reject\" => TRUE,\"redirect\" => TRUE,\"keep\" => TRUE,\"discard\" => TRUE,\"imapflags\" => TRUE,\"notify\" => TRUE,\"stop\" => TRUE);";
		$sieve[]="\$rcmail_config[\"sieverules_other_headers\"] = array(\"Reply-To\", \"List-Id\", \"MailingList\", \"Mailing-List\",\"X-ML-Name\", \"X-List\", \"X-List-Name\", \"X-Mailing-List\",\"Resent-From\",";
		$sieve[]="	\"Resent-To\", \"X-Mailer\", \"X-MailingList\",\"X-Spam-Status\", \"X-Priority\", \"Importance\", \"X-MSMail-Priority\",\"Precedence\", \"Return-Path\", \"Received\", \"Auto-Submitted\",\"X-Spam-Flag\", \"X-Spam-Tests\");";
		$sieve[]="\$rcmail_config[\"sieverules_predefined_rules\"] = array();";
		$sieve[]="\$rcmail_config[\"sieverules_adveditor\"] = 0;";
		$sieve[]="\$rcmail_config[\"sieverules_multiplerules\"] = FALSE;";
		$sieve[]="\$rcmail_config[\"sieverules_default_file\"] = \"/etc/dovecot/sieve/default\";";
		$sieve[]="\$rcmail_config[\"sieverules_auto_load_default\"] = FALSE;";
		$sieve[]="\$rcmail_config[\"sieverules_example_file\"] = \"/etc/dovecot/sieve/example\";";
		$sieve[]="\$rcmail_config[\"sieverules_force_vacto\"] = TRUE;";
		$sieve[]="\$rcmail_config[\"sieverules_use_elsif\"] = TRUE;";
		$sieve[]="?>";		
		@file_put_contents("$root/plugins/sieverules/config.inc.php",@implode("\n",$sieve));
		
	}
	
	$conf[]="";
	$conf[]="";
	$conf[]="// don't let users set pagesize to more than this value if set";
	$conf[]="\$rcmail_config['max_pagesize'] = 200;";
	$conf[]="\$rcmail_config['create_default_folders'] = TRUE;";
	$conf[]="";
	$conf[]="";
	$conf[]="// end of config file";
	$conf[]="?>";	
		
	@file_put_contents("$root/config/main.inc.php",@implode("\n",$conf));
	
	
	$sock=new sockets();
	$EnablePostfixMultiInstance=$sock->GET_INFO("EnablePostfixMultiInstance");
	
	if($EnablePostfixMultiInstance==1){
		echo "Starting......: Roundcube $servername Postfix Multi Instance Enabled \n";
		$smtp=$hash[strtolower("WWWMultiSMTPSender")][0];
		$tbl=@explode("\n",@file_get_contents("$root/config/main.inc.php"));
		while (list ($i, $line) = each ($tbl) ){
			if(preg_match("#rcmail_config.+?smtp_server#",$line)){
				echo "Starting......: Roundcube $servername Postfix change line $i to $smtp\n";
				$tbl[$i]="\$rcmail_config['smtp_server'] = '$smtp';";
			}
		}
		@file_put_contents("$root/config/main.inc.php",@implode("\n",$tbl));
	}
}

function ROUNDCUBE_CONTEXTMENU($dir){
	if(is_file("$dir/plugins/contextmenu/contextmenu.php")){return;}
	writelogs("Installing in $dir/plugins/contextmenu",__FUNCTION__,__FILE__,__LINE__);
	@mkdir("$dir/plugins/contextmenu",0755,true);
	shell_exec("/bin/cp -rf /usr/share/artica-postfix/bin/install/roundcube/contextmenu/* $dir/plugins/contextmenu/");
	shell_exec("/bin/chmod -R 755 $dir/plugins/contextmenu");
	writelogs("Installing in $dir/plugins/contextmenu done...",__FUNCTION__,__FILE__,__LINE__);
}
function ROUNDCUBE_GLOBALADDRESSBOOK($dir){
	if(is_file("$dir/plugins/globaladdressbook/globaladdressbook.php")){return;}
	writelogs("Installing in $dir/plugins/globaladdressbook",__FUNCTION__,__FILE__,__LINE__);
	@mkdir("$dir/plugins/globaladdressbook",0755,true);
	shell_exec("/bin/cp -rf /usr/share/artica-postfix/bin/install/roundcube/globaladdressbook/* $dir/plugins/globaladdressbook/");
	shell_exec("/bin/chmod -R 755 $dir/plugins/globaladdressbook");
	writelogs("Installing in $dir/plugins/globaladdressbook done...",__FUNCTION__,__FILE__,__LINE__);
}

function events($text){
		if($GLOBALS["VERBOSE"]){$_GET["debug"]=true;}
		if($_GET["debug"]){echo "Starting......: Apache groupware $text\n";}
		
		writelogs($text,"main",__FILE__,__LINE__);
		}
		
		
		
		
function mailmanhosts(){
	$ldap=new clladp();
	$sock=new sockets();
	$ApacheGroupware=$sock->GET_INFO("ApacheGroupware");
	if($ApacheGroupware==null){$ApacheGroupware=1;}
	$ApacheGroupwareListenIP=$sock->GET_INFO("ApacheGroupwareListenIP");
	$ApacheGroupWarePort=$sock->GET_INFO("ApacheGroupWarePort");
	$ApacheGroupWarePortSSL=$sock->GET_INFO("ApacheGroupWarePortSSL");
	$SSLStrictSNIVHostCheck=$sock->GET_INFO("SSLStrictSNIVHostCheck");
	
	
	if($ApacheGroupware==0){
		$ApacheGroupwareListenIP=$sock->GET_INFO("FreeWebListen");
		$ApacheGroupWarePort=$sock->GET_INFO("FreeWebListenPort");
		$ApacheGroupWarePortSSL=$sock->GET_INFO("FreeWebListenSSLPort");
	
	}
	
	if(!is_numeric($ApacheGroupWarePortSSL)){$ApacheGroupWarePortSSL=443;}
	if(!is_numeric($ApacheGroupWarePort)){$ApacheGroupWarePort=80;}
	if($ApacheGroupwareListenIP==null){$ApacheGroupwareListenIP="*";}
	
	
	$filter="(&(Objectclass=ArticaMailManRobots)(cn=*))";
	$sr = @ldap_search($ldap->ldap_connection,"dc=organizations,$ldap->suffix",$filter,array());
	if(!$sr){
		writelogs("No mailman list found for pattern $filter",__FUNCTION__,__FILE__,__LINE__);	
		return null;
	
	}
	$hash=ldap_get_entries($ldap->ldap_connection,$sr);
	$cgi_path=mailman_cgibin_path();
	writelogs("cgi_path=$cgi_path, \"dc=organizations,$ldap->suffix\" count=\"".$hash["count"]."\"",__FUNCTION__,__FILE__,__LINE__);
	
	for($i=0;$i<$hash["count"];$i++){
		$webservername=null;
		$webservername=$hash[$i][strtolower("MailManWebServerName")][0];
		$admin_email=$hash[$i]["mailmanowner"][0];
		$cn=$hash[$i]["cn"][0];
		if(preg_match("#(.+?)@#",$cn,$re)){$listname=$re[1];}
		if($admin_email==null){
			writelogs("$webservername= no admin mail, abort DN: {$hash[$i]["dn"]}",__FUNCTION__,__FILE__,__LINE__);
			continue;
		}
		if($webservername==null){
			writelogs("no webserver name, abort",__FUNCTION__,__FILE__,__LINE__);
			continue;
		
			}
			@mkdir("/usr/share/artica-groupware/$webservername/css",0755,true);
			@copy("/usr/share/artica-postfix/bin/install/mailman/style.css","/usr/share/artica-groupware/$webservername/css/style.css");
			$conf=$conf."\n\n<VirtualHost $ApacheGroupwareListenIP:$ApacheGroupWarePort>\n";
			$conf=$conf."ServerAdmin $admin_email\n";
			$conf=$conf."ServerName $webservername\n";
			$conf=$conf."DocumentRoot /usr/share/artica-groupware/$webservername\n";
			$conf=$conf."ScriptAlias /mailman/ $cgi_path/\n";
			$conf=$conf."ScriptAlias /cgi-bin/mailman/ $cgi_path/\n";
			$conf=$conf."<Directory \"$cgi_path\">\n"; 
			$conf=$conf."   Options -MultiViews +SymLinksIfOwnerMatch\n";
			$conf=$conf."   AllowOverride all\n";
			$conf=$conf."   Order allow,deny\n";
			$conf=$conf."  Allow from all\n";			
			$conf=$conf."</Directory>\n";
			$conf=$conf."Alias /images/mailman/ /usr/share/images/mailman/\n";
			$conf=$conf."<Directory \"/usr/share/images/mailman/\">\n";
			$conf=$conf."    AllowOverride None\n";
			$conf=$conf."    Order allow,deny\n";
			$conf=$conf."    Allow from all\n";
			$conf=$conf."</Directory>\n";
			$conf=$conf."Alias /css/ /usr/share/artica-groupware/$webservername/css/\n";
			$conf=$conf."<Directory \"/usr/share/artica-groupware/$webservername/css\">\n";
			$conf=$conf."    AllowOverride None\n";
			$conf=$conf."    Order allow,deny\n";
			$conf=$conf."    Allow from all\n";
			$conf=$conf."</Directory>\n";			
			$conf=$conf."\n";
			$conf=$conf."Alias /pipermail/ /var/lib/mailman/archives/public/\n";
			$conf=$conf."<Directory \"/var/lib/mailman/archives/public\">\n";
			$conf=$conf."    Options Indexes MultiViews FollowSymLinks\n";
			$conf=$conf."    AllowOverride None\n";
			$conf=$conf."    Order allow,deny\n";
			$conf=$conf."    Allow from all\n";
			$conf=$conf."</Directory>\n";
			$conf=$conf."\n";
			$conf=$conf."<IfModule mod_rewrite.c>\n";
			$conf=$conf."	RewriteEngine on\n";
			$conf=$conf."	# Redirect root access to mailman list\n";
			$conf=$conf."	RewriteRule ^$ /mailman/listinfo/$listname [R=permanent,L]\n";
			$conf=$conf."	RewriteRule ^/$ /mailman/listinfo/$listname [R=permanent,L]\n";
			$conf=$conf."	RewriteRule ^mailman.+?/style.css$ /css/style.css [R=permanent,L]\n";	
			$conf=$conf."	RedirectMatch ^/$ /listinfo\n";
			$conf=$conf."</IfModule>\n";	
			$conf=$conf."\n";
			$conf=$conf."CustomLog \"|/usr/sbin/rotatelogs /usr/local/apache-groupware/logs/$webservername 86400\" \"%h %l %u %t \\\"%r\\\" %>s %b \\\"%{Referer}i\\\" \\\"%{User-Agent}i\\\" %V\"\n";
			$conf=$conf."ErrorLog /usr/local/apache-groupware/logs/{$webservername}_err.log\n";
			$conf=$conf."</VirtualHost>\n";		
			}
			
		
			if($GLOBALS["OUTPUT"]){echo $conf;}
			return $conf;
}


	
	
function mailman_cgibin_path(){
	
			$conf=$conf."# Redirect to SSL if available\n";
			$conf=$conf."  <IfModule mod_ssl.c>\n";
			$conf=$conf."      RewriteCond %{HTTPS} !^on$ [NC]\n";
			$conf=$conf."      RewriteRule . https://%{HTTP_HOST}%{REQUEST_URI}  [L]\n";
			$conf=$conf."  </IfModule>\n";		
	
	if(is_file("/var/lib/mailman/cgi-bin/subscribe")){return "/var/lib/mailman/cgi-bin";}
	if(is_file("/usr/local/mailman/cgi-bin/subscribe")){return "/usr/local/mailman/cgi-bin";}
}
	
function OBM2_INSTALL($servername,$root,$hash=array()){
	$GLOBALS["ADDLOG"]="/var/log/artica-postfix/$servername.log";	
	if($root==null){events("Starting install OBM2 Unable to stat root dir");return false;}
	if(!is_dir("/opt/artica/install/sources/obm")){
		events("Starting install OBM2 Unable to stat /opt/artica/install/sources/obm");
		return false;
	}
	
	$sqlfiles=array(
			"create_obmdb_2.3.mysql.sql",
			"obmdb_prefs_values_2.3.sql",
			"obmdb_default_values_2.3.sql",
			"obmdb_test_values_2.3.sql",
			"data-fr/obmdb_nafcode_2.3.sql",
			"data-fr/obmdb_ref_2.3.sql",
			"data-en/obmdb_nafcode_2.3.sql",
			"data-en/obmdb_ref_2.3.sql");
	
	$user=$hash["wwwmysqluser"][0];
	$mysql_password=$hash[strtolower("WWWMysqlPassword")][0];
	
	$appli_user=$hash["wwwappliuser"];
	$appli_password=$hash["wwwapplipassword"];
	
	
	if($user==null){events("Starting install OBM2 Unable to stat Mysql username");return false;}
	if($mysql_password==null){events("Starting install OBM2 Unable to stat Mysql password");return false;}

	@mkdir($root,0755,true);
	
	$server_database=str_replace(".","_",$servername);
	$server_database=str_replace("-","_",$server_database);	
	$q=new mysql();
	if(!$q->DATABASE_EXISTS($server_database)){
		events("Starting install OBM2 sub-system mysql database $server_database...");
		$q->CREATE_DATABASE($server_database);
	
		if(!$q->DATABASE_EXISTS($server_database)){
			events("Starting install OBM2 unable to create MYSQL Database");
			return false;
		}
	}
	
		
	events("Starting install OBM2 installing source code in $root");
	shell_exec("/bin/cp -rf /opt/artica/install/sources/obm/* $root/");
	if($q->mysql_password<>null){
		$password=" --password=$q->mysql_password ";
	}else{
		events("Starting install OBM2 installing tables datas with null password");
	}
	$unix=new unix();
		//<$sql_file
		$cmd=$unix->find_program("mysql")." --port=$q->mysql_port --skip-column-names --database=$server_database --silent --xml ";
		$cmd=$cmd." --user=$q->mysql_admin$password";
		
		if(!OBM2_CheckObmTables($server_database)){
			while (list ($num, $filesql) = each ($sqlfiles) ){
				if(is_file("/opt/artica/install/sources/obm/scripts/2.3/$filesql")){
					events("installing $filesql SQL commands");
					shell_exec($cmd ." </opt/artica/install/sources/obm/scripts/2.3/$filesql");
				}
		}}

	$version=OBM2_VERSION($root);
	if($version==null){
		events("Starting install unable to stat version");
		return false;
	}
	events("Starting install OBM2 version $version");
	if(is_file("$root/scripts/2.3/updates/update-2.3.1-$version.mysql.sql")){
		events("Starting updating OBM2 version 2.3.1-$version");
		shell_exec($cmd ." <$root/scripts/2.3/updates/update-2.3.1-$version.mysql.sql");
	}else{
		events("Starting updating unable to stat $root/scripts/2.3/updates/update-2.3.1-$version.mysql.sql");
	}
		//scripts/2.3/updates/update-2.3.1-2.3.2.mysql.sql
		
	$q->PRIVILEGES($user,$mysql_password,$server_database);
	OBM2_INSTALL_SCRIPTS($root,$servername,$server_database,$user,$mysql_password);
}

function OBM2_INSTALL_SCRIPTS($root,$servername,$server_database,$mysql_user,$mysql_password){
$sock=new sockets();
$ApacheGroupWarePort=$sock->GET_INFO("ApacheGroupWarePort");	
$ldap=new clladp();
$conf=$conf."<script language=\"php\">\n";
$conf=$conf."\$cgp_todo_nb = 5;\n";
$conf=$conf."\$conf_display_max_rows = 200;\n";
$conf=$conf."\$cgp_sql_star = true;\n";
$conf=$conf."\$ctu_sql_limit = true;\n";
$conf=$conf."\$cgp_mail_enabled = true;\n";
$conf=$conf."\$cgp_demo_enabled = false;\n";
$conf=$conf."\$cs_lifetime = 0;\n";
$conf=$conf."\$cgp_sess_db = false;\n";
$conf=$conf."\$password_encryption = 'PLAIN';\n";
$conf=$conf."\$caf_company_name = true;\n";
$conf=$conf."\$caf_town = true;\n";
$conf=$conf."\$csearch_advanced_default = false;\n";
$conf=$conf."\$cgp_mailing_default = true;\n";
$conf=$conf."\$ccalendar_public_groups = true;\n";
$conf=$conf."\$ccalendar_first_hour = 8;\n";
$conf=$conf."\$ccalendar_last_hour = 20;\n";
$conf=$conf."\$ccalendar_resource = true;\n";
$conf=$conf."\$ccalendar_send_ics = true;\n";
$conf=$conf."\$ccalendar_hour_fraction = 4;\n";
$conf=$conf."\$ccalendar_invocation_method = 'onDblClick';\n";
$conf=$conf."\$c_working_days = array(0,1,1,1,1,1,0);\n";
$conf=$conf."\$cimage_logo = 'linagora.jpg';\n";
$conf=$conf."\$cgroup_private_default = true;\n";
$conf=$conf."\$cdefault_tax = array ('TVA 19,6' => 1.196, 'TVA 5,5' => 1.055, 'Pas de TVA' => 1);\n";
$conf=$conf."\$cgp_default_right = array (\n";
$conf=$conf."  'resource' => array (\n";
$conf=$conf."      'public' => array(\n";
$conf=$conf."        'access' => 1,\n";
$conf=$conf."        'read' => 0,\n";
$conf=$conf."        'write' => 0,\n";
$conf=$conf."        'admin' => 0\n";
$conf=$conf."      )\n";
$conf=$conf."    ),\n";
$conf=$conf."  'contact' => array (\n";
$conf=$conf."      'public' => array(\n";
$conf=$conf."        'access' => 1,\n";
$conf=$conf."        'read' => 1,\n";
$conf=$conf."        'write' => 0,\n";
$conf=$conf."        'admin' => 0\n";
$conf=$conf."      )\n";
$conf=$conf."    ),\n";
$conf=$conf."  'mailshare' => array (\n";
$conf=$conf."      'public' => array(\n";
$conf=$conf."        'access' => 1,\n";
$conf=$conf."        'read' => 0,\n";
$conf=$conf."        'write' => 0,\n";
$conf=$conf."        'admin' => 0\n";
$conf=$conf."      )\n";
$conf=$conf."    ),\n";
$conf=$conf."  'mailbox' => array (\n";
$conf=$conf."      'public' => array(\n";
$conf=$conf."        'access' => 1,\n";
$conf=$conf."        'read' => 0,\n";
$conf=$conf."        'write' => 0,\n";
$conf=$conf."        'admin' => 0\n";
$conf=$conf."      )\n";
$conf=$conf."    ),\n";
$conf=$conf."  'calendar' => array (\n";
$conf=$conf."      'public' => array(\n";
$conf=$conf."        'access' => 1,\n";
$conf=$conf."        'read' => 1,\n";
$conf=$conf."        'write' => 0,\n";
$conf=$conf."        'admin' => 0\n";
$conf=$conf."      )\n";
$conf=$conf."    )\n";
$conf=$conf."  );\n";
$conf=$conf."\n";
$conf=$conf."\$profiles['admin'] = array (\n";
$conf=$conf."  'section' => array (\n";
$conf=$conf."    'default' => 1\n";
$conf=$conf."  ),\n";
$conf=$conf."  'module' => array (\n";
$conf=$conf."    'default' => \$perm_admin,\n";;
$conf=$conf."    'domain' => 0),\n";
$conf=$conf."  'properties' => array (\n";
$conf=$conf."    'admin_realm' => array ('user', 'delegation', 'domain')\n";
$conf=$conf."    ),\n";
$conf=$conf."  'level' => 1,\n";
$conf=$conf."  'level_managepeers' => 1,\n";
$conf=$conf."  'access_restriction' => 'ALLOW_ALL'\n";
$conf=$conf."\n";
$conf=$conf.");\n";
$conf=$conf."\$cgp_show['section']['com'] = false;\n";
$conf=$conf."\$cgp_show['section']['prod'] = false;\n";
$conf=$conf."\$cgp_show['section']['compta'] = false;\n";
$conf=$conf."\$cgp_show['module']['company'] = false;\n";
$conf=$conf."\$cgp_show['module']['lead'] = false;\n";
$conf=$conf."\$cgp_show['module']['deal'] = false;\n";
$conf=$conf."\$cgp_show['module']['cv'] = false;\n";
$conf=$conf."\$cgp_show['module']['publication'] = false;\n";
$conf=$conf."\$cgp_show['module']['statistic'] = false;\n";
$conf=$conf."\$cgp_show['module']['time'] = false;\n";
$conf=$conf."\$cgp_show['module']['project'] = false;\n";
$conf=$conf."\$cgp_show['module']['contract'] = false;\n";
$conf=$conf."\$cgp_show['module']['incident'] = false;\n";
$conf=$conf."\$cgp_show['module']['invoice'] = false;\n";
$conf=$conf."\$cgp_show['module']['payment'] = false;\n";
$conf=$conf."\$cgp_show['module']['account'] = false;\n";
$conf=$conf."\n";
$conf=$conf."</script>\n";
@file_put_contents("$root/conf/obm_conf.inc",$conf);
$conf=null;
$conf=$conf."; OBM system configuration file\n";
$conf=$conf."; Copy it to obm_conf.ini (without \".sample\")\n";
$conf=$conf."; Set here Common global parameteres\n";
$conf=$conf."; \n";
$conf=$conf."; Parameters are set like : key = value\n";
$conf=$conf."; Comments are lines beginning with \";\"\n";
$conf=$conf."; OBM Automate need the [global] for the perl section (beware : php is permissive)\n";
$conf=$conf.";\n";
$conf=$conf."[global]\n";
$conf=$conf."; General information\n";
$conf=$conf."title = $servername\n";
$conf=$conf.";\n";
$conf=$conf."; example : for https://extranet.aliasource.fr/obm/ \n";
$conf=$conf."; external-url = extranet.aliasource.fr\n";
$conf=$conf."; external-protocol = https\n";
$conf=$conf."; obm-prefix = /obm/\n";
$conf=$conf."external-url = http://$servername:$ApacheGroupWarePort\n";
$conf=$conf."external-protocol = http\n";
$conf=$conf."obm-prefix = /\n";
$conf=$conf."\n";
$conf=$conf."; Database infos\n";
$conf=$conf."host = 127.0.0.1\n";
$conf=$conf."dbtype = MYSQL\n";
$conf=$conf."db = $server_database\n";
$conf=$conf."user = $mysql_user\n";
$conf=$conf."; Password must be enclosed with \"\n";
$conf=$conf."password = \"$mysql_password\"\n";
$conf=$conf."\n";
$conf=$conf."; Default language\n";
$conf=$conf."lang = fr\n";
$conf=$conf."\n";
$conf=$conf."; Enabled OBM module\n";
$conf=$conf."obm-ldap = false\n";
$conf=$conf."obm-mail = true\n";
$conf=$conf."obm-samba = false\n";
$conf=$conf."obm-web = false\n";
$conf=$conf."obm-contact = true\n";
$conf=$conf."\n";
$conf=$conf."; singleNameSpace mode allow only one domain\n";
$conf=$conf."; login are 'login' and not 'login@domain'\n";
$conf=$conf."; Going multi-domain from mono domain needs system work (ldap, cyrus,...)\n";
$conf=$conf."; Multi-domain disabled by default\n";
$conf=$conf."singleNameSpace = false\n";
$conf=$conf."\n";
$conf=$conf."; backupRoot is the directory used to store backup data\n";
$conf=$conf."backupRoot = \"/var/lib/obm/backup\"\n";
$conf=$conf."\n";
$conf=$conf."; documentRoot is root of document repository\n";
$conf=$conf."documentRoot=\"/var/lib/obm/documents\"\n";
$conf=$conf."documentDefaultPath=\"/\"\n";
$conf=$conf."\n";
$conf=$conf."; LDAP Authentification for obm-sync & ui\n";
$conf=$conf."; ldap authentication server (specify :port if different than default)\n";
$conf=$conf."auth-ldap-server = ldap://localhost\n";
$conf=$conf."; base dn for search (search are performed with scope sub, of not specified, use the server default)\n";
$conf=$conf.";auth-ldap-basedn = \"$ldap->suffix\"\n";
$conf=$conf."; filter used for the search part of the authentication\n";
$conf=$conf."; See http://www.faqs.org/rfcs/rfc2254.html for filter syntax\n";
$conf=$conf.";  - %u will be replace with user login\n";
$conf=$conf.";  - %d will be replace with user OBM domain name\n";
$conf=$conf."; ie: toto@domain.foo : %u=toto, %d=domain.foo\n";
$conf=$conf."; auth-ldap-filter = \"(&(uid=%u)(obmDomain=%d))\"\n";
$conf=$conf."\n";
$conf=$conf."[automate]\n";
$conf=$conf."; Automate specific parameters\n";
$conf=$conf.";\n";
$conf=$conf."; Log level\n";
$conf=$conf."logLevel = 2\n";
$conf=$conf.";\n";
$conf=$conf."; LDAP server address\n";
$conf=$conf.";ldapServer = ldap://localhost\n";
$conf=$conf.";\n";
$conf=$conf."; LDAP use TLS [none|may|encrypt]\n";
$conf=$conf."ldapTls = may\n";
$conf=$conf.";\n";
$conf=$conf."; LDAP Root\n";
$conf=$conf."; Exemple : 'aliasource,local' means that the root DN is: 'dc=aliasource,dc=local' \n";
$conf=$conf."ldapRoot = local\n";
$conf=$conf."\n";
$conf=$conf."; Enable Cyrus partition support\n";
$conf=$conf."; if cyrusPartition is enable, a dedicated Cyrus partition is created for each OBM domain\n";
$conf=$conf."; Going cyrusPartition enabled from cyrusPartition disabled needs system work\n";
$conf=$conf."cyrusPartition = false\n";
$conf=$conf.";\n";
$conf=$conf."; ldapAllMainMailAddress :\n";
$conf=$conf.";    false : publish user mail address only if mail right is enable - default\n";
$conf=$conf.";    true : publish main user mail address, even if mail right is disable\n";
$conf=$conf."ldapAllMainMailAddress = true\n";
$conf=$conf.";\n";
$conf=$conf."; userMailboxDefaultFolders are IMAP folders who are automaticaly created\n";
$conf=$conf."; at user creation ( must be enclosed with \" and in IMAP UTF-7 modified encoding)\n";
$conf=$conf."; Small convertion table\n";
$conf=$conf."; é -> &AOk-\n";
$conf=$conf."; è -> &AOg-\n";
$conf=$conf."; à -> &AOA-\n";
$conf=$conf."; & -> &\n";
$conf=$conf."; Example : userMailboxDefaultFolders = \"Envoy&AOk-s,Corbeille,Brouillons,El&AOk-ments ind&AOk-sirables\"\n";
$conf=$conf."userMailboxDefaultFolders = \"\"\n";
$conf=$conf.";\n";
$conf=$conf."; shareMailboxDefaultFolders are IMAP folders who are automaticaly created\n";
$conf=$conf."; at share creation ( must be enclosed with \" and in IMAP UTF-7 modified\n";
$conf=$conf."; encoding)\n";
$conf=$conf."shareMailboxDefaultFolders = \"\"\n";
$conf=$conf.";\n";
$conf=$conf."; oldSidMapping mode is for compatibility with Aliamin and old install\n";
$conf=$conf."; Modifying this on a running system need Samba domain work (re-register host,\n";
$conf=$conf."; ACL...) \n";
$conf=$conf."; For new one, leave this to 'false'\n";
$conf=$conf."oldSidMapping = false\n";
$conf=$conf.";\n";
$conf=$conf.";\n";
$conf=$conf."; Settings use by OBM Thunderbird autoconf\n";
$conf=$conf."[autoconf]\n";
$conf=$conf.";\n";
$conf=$conf."ldapHostname = ldap.aliacom.local\n";
$conf=$conf."ldapHost = 127.0.0.1\n";
$conf=$conf."ldapPort = 389\n";
$conf=$conf."ldapSearchBase = \"dc=local\"\n";
$conf=$conf."ldapAtts = cn,mail,mailAlias,mailBox,obmDomain,uid\n";
$conf=$conf."ldapFilter = \"mail\"\n";
$conf=$conf."configXml = /usr/lib/obm-autoconf/config.xml\n";
$conf=$conf.";\n";
$conf=$conf."; EOF";
@file_put_contents("$root/conf/obm_conf.ini",$conf);	
}

function OBM2_CheckObmTables($database){
	
$tables[]="Account";
$tables[]="AccountEntity";
$tables[]="ActiveUserObm";
$tables[]="Address";
$tables[]="AddressBook";
$tables[]="AddressbookEntity";
$tables[]="CalendarEntity";
$tables[]="Campaign";
$tables[]="CampaignDisabledEntity";
$tables[]="CampaignEntity";
$tables[]="CampaignMailContent";
$tables[]="CampaignMailTarget";
$tables[]="CampaignPushTarget";
$tables[]="CampaignTarget";
$tables[]="Category";
$tables[]="CategoryLink";
$tables[]="Company";
$tables[]="CompanyActivity";
$tables[]="CompanyEntity";
$tables[]="CompanyNafCode";
$tables[]="CompanyType";
$tables[]="Contact";
$tables[]="ContactEntity";
$tables[]="ContactFunction";
$tables[]="ContactList";
$tables[]="Contract";
$tables[]="ContractEntity";
$tables[]="ContractPriority";
$tables[]="ContractStatus";
$tables[]="ContractType";
$tables[]="Country";
$tables[]="CV";
$tables[]="CvEntity";
$tables[]="DataSource";
$tables[]="Deal";
$tables[]="DealCompany";
$tables[]="DealCompanyRole";
$tables[]="DealEntity";
$tables[]="DealStatus";
$tables[]="DealType";
$tables[]="DefaultOdtTemplate";
$tables[]="Deleted";
$tables[]="DeletedAddressbook";
$tables[]="DeletedContact";
$tables[]="DeletedEvent";
$tables[]="DeletedUser";
$tables[]="DisplayPref";
$tables[]="Document";
$tables[]="DocumentEntity";
$tables[]="DocumentLink";
$tables[]="DocumentMimeType";
$tables[]="Domain";
$tables[]="DomainEntity";
$tables[]="DomainProperty";
$tables[]="DomainPropertyValue";
$tables[]="Email";
$tables[]="Entity";
$tables[]="EntityRight";
$tables[]="Event";
$tables[]="EventAlert";
$tables[]="EventCategory1";
$tables[]="EventEntity";
$tables[]="EventException";
$tables[]="EventLink";
$tables[]="EventTag";
$tables[]="EventTemplate";
$tables[]="GroupEntity";
$tables[]="GroupGroup";
$tables[]="Host";
$tables[]="HostEntity";
$tables[]="IM";
$tables[]="Import";
$tables[]="ImportEntity";
$tables[]="Incident";
$tables[]="IncidentEntity";
$tables[]="IncidentPriority";
$tables[]="IncidentResolutionType";
$tables[]="IncidentStatus";
$tables[]="Invoice";
$tables[]="InvoiceEntity";
$tables[]="Kind";
$tables[]="Lead";
$tables[]="LeadEntity";
$tables[]="LeadSource";
$tables[]="LeadStatus";
$tables[]="List";
$tables[]="ListEntity";
$tables[]="MailboxEntity";
$tables[]="MailShare";
$tables[]="MailshareEntity";
$tables[]="ObmBookmark";
$tables[]="ObmbookmarkEntity";
$tables[]="ObmBookmarkProperty";
$tables[]="ObmInfo";
$tables[]="ObmSession";
$tables[]="of_usergroup";
$tables[]="OGroup";
$tables[]="OgroupEntity";
$tables[]="OGroupLink";
$tables[]="opush_device";
$tables[]="opush_folder_mapping";
$tables[]="opush_sec_policy";
$tables[]="opush_sync_mail";
$tables[]="opush_sync_perms";
$tables[]="opush_sync_state";
$tables[]="OrganizationalChart";
$tables[]="OrganizationalchartEntity";
$tables[]="ParentDeal";
$tables[]="ParentdealEntity";
$tables[]="Payment";
$tables[]="PaymentEntity";
$tables[]="PaymentInvoice";
$tables[]="PaymentKind";
$tables[]="Phone";
$tables[]="PlannedTask";
$tables[]="Profile";
$tables[]="ProfileEntity";
$tables[]="ProfileModule";
$tables[]="ProfileProperty";
$tables[]="ProfileSection";
$tables[]="Project";
$tables[]="ProjectClosing";
$tables[]="ProjectCV";
$tables[]="ProjectEntity";
$tables[]="ProjectRefTask";
$tables[]="ProjectTask";
$tables[]="ProjectUser";
$tables[]="Publication";
$tables[]="PublicationEntity";
$tables[]="PublicationType";
$tables[]="P_Domain";
$tables[]="P_DomainEntity";
$tables[]="P_EntityRight";
$tables[]="P_GroupEntity";
$tables[]="P_Host";
$tables[]="P_HostEntity";
$tables[]="P_MailboxEntity";
$tables[]="P_MailShare";
$tables[]="P_MailshareEntity";
$tables[]="P_of_usergroup";
$tables[]="P_Service";
$tables[]="P_ServiceProperty";
$tables[]="P_UGroup";
$tables[]="P_UserEntity";
$tables[]="P_UserObm";
$tables[]="Region";
$tables[]="Resource";
$tables[]="ResourceEntity";
$tables[]="ResourceGroup";
$tables[]="ResourcegroupEntity";
$tables[]="ResourceItem";
$tables[]="ResourceType";
$tables[]="RGroup";
$tables[]="Service";
$tables[]="ServiceProperty";
$tables[]="SSOTicket";
$tables[]="Stats";
$tables[]="Subscription";
$tables[]="SubscriptionEntity";
$tables[]="SubscriptionReception";
$tables[]="SyncedAddressbook";
$tables[]="TaskEvent";
$tables[]="TaskType";
$tables[]="TaskTypeGroup";
$tables[]="TimeTask";
$tables[]="UGroup";
$tables[]="Updated";
$tables[]="Updatedlinks";
$tables[]="UserEntity";
$tables[]="UserObm";
$tables[]="UserObmGroup";
$tables[]="UserObmPref";
$tables[]="UserObm_SessionLog";
$tables[]="UserSystem";
$tables[]="Website";
$q=new mysql();
while (list ($num, $table) = each ($tables) ){
	if(!$q->TABLE_EXISTS($table,$database)){
		events("Starting install OBM2 $table table does not exists");
		return false;	
	}
}
return true;
}

function OBM2_VERSION($root){
	if(!is_file("$root/obminclude/global.inc")){
		events("Starting install OBM2 $root/obminclude/global.inc file does not exists");
	}
	
	$tbl=explode("\n",@file_get_contents("$root/obminclude/global.inc"));
	while (list ($num, $line) = each ($tbl) ){
		if(preg_match("#obm_version.+?([0-9\.]+)#",$line,$re)){
			return trim($re[1]);
		}
	}
	
	events("Starting install OBM2 unable to find verison in $root/obminclude/global.inc");
}

function OPENGOO_CHECK_TABLES($database){
$tables[]="og_administration_tools";
$tables[]="og_application_logs";
$tables[]="og_billing_categories";
$tables[]="og_comments";
$tables[]="og_companies";
$tables[]="og_config_categories";
$tables[]="og_config_options";
$tables[]="og_contacts";
$tables[]="og_contact_im_values";
$tables[]="og_cron_events";
$tables[]="og_custom_properties";
$tables[]="og_custom_properties_by_co_type";
$tables[]="og_custom_property_values";
$tables[]="og_event_invitations";
$tables[]="og_file_repo";
$tables[]="og_file_repo_attributes";
$tables[]="og_file_types";
$tables[]="og_groups";
$tables[]="og_group_users";
$tables[]="og_gs_books";
$tables[]="og_gs_borderstyles";
$tables[]="og_gs_cells";
$tables[]="og_gs_columns";
$tables[]="og_gs_fonts";
$tables[]="og_gs_fontstyles";
$tables[]="og_gs_layoutstyles";
$tables[]="og_gs_mergedcells";
$tables[]="og_gs_rows";
$tables[]="og_gs_sheets";
$tables[]="og_gs_userbooks";
$tables[]="og_gs_users";
$tables[]="og_guistate";
$tables[]="og_im_types";
$tables[]="og_linked_objects";
$tables[]="og_mail_accounts";
$tables[]="og_mail_account_imap_folder";
$tables[]="og_mail_account_users";
$tables[]="og_mail_contents";
$tables[]="og_mail_conversations";
$tables[]="og_object_handins";
$tables[]="og_object_properties";
$tables[]="og_object_reminders";
$tables[]="og_object_reminder_types";
$tables[]="og_object_subscriptions";
$tables[]="og_object_user_permissions";
$tables[]="og_projects";
$tables[]="og_project_charts";
$tables[]="og_project_chart_params";
$tables[]="og_project_companies";
$tables[]="og_project_contacts";
$tables[]="og_project_co_types";
$tables[]="og_project_events";
$tables[]="og_project_files";
$tables[]="og_project_file_revisions";
$tables[]="og_project_forms";
$tables[]="og_project_messages";
$tables[]="og_project_milestones";
$tables[]="og_project_tasks";
$tables[]="og_project_users";
$tables[]="og_project_webpages";
$tables[]="og_queued_emails";
$tables[]="og_read_objects";
$tables[]="og_reports";
$tables[]="og_report_columns";
$tables[]="og_report_conditions";
$tables[]="og_searchable_objects";
$tables[]="og_shared_objects";
$tables[]="og_tags";
$tables[]="og_templates";
$tables[]="og_template_objects";
$tables[]="og_template_object_properties";
$tables[]="og_template_parameters";
$tables[]="og_timeslots";
$tables[]="og_users";
$tables[]="og_user_passwords";
$tables[]="og_user_ws_config_categories";
$tables[]="og_user_ws_config_options";
$tables[]="og_user_ws_config_option_values";
$tables[]="og_workspace_billings";
$tables[]="og_workspace_objects";
$tables[]="og_workspace_templates";	
$q=new mysql();
while (list ($num, $table) = each ($tables) ){
	if(!$q->TABLE_EXISTS($table,$database)){
		events("Starting install OpenGoo $table table does not exists");
		return false;	
	}
}
return true;
}

function vhosts_BuildCertificate($hostname){
	$unix=new unix();
	$unix->vhosts_BuildCertificate($hostname);
	
}

function DRUPAL_INSTALL($apacheservername,$root,$hash=array()){
	$ldap=new clladp();
	$GLOBALS["ADDLOG"]="/var/log/artica-postfix/$servername.log";	
	$server_database=str_replace(".","_",$apacheservername);
	$server_database=str_replace("-","_",$server_database);	
	events("Starting install drupal table prefix={$server_database}_...");
	$sock=new sockets();
	$ApacheGroupWarePort=$sock->GET_INFO("ApacheGroupWarePort");
	$prefix_web="http";
	if($hash["wwwsslmode"][0]=='TRUE'){$prefix_web="https";}
	$dn=$hash["dn"];
	
	if(preg_match("#ou=www,ou=(.+?),#",$dn,$re)){$ou=$re[1];}
	$q=new mysql();	
	$conf[]="<?php";
	$conf[]="\$db_url = 'mysql://$q->mysql_admin:$q->mysql_password@$q->mysql_server:$q->mysql_port/drupal';";
	$conf[]="\$db_prefix = '{$server_database}_';";
	$conf[]="\$update_free_access = FALSE;";
	$conf[]="\$base_url = '$prefix_web://$apacheservername:$ApacheGroupWarePort';  // NO trailing slash!";
	$conf[]="ini_set('arg_separator.output',     '&amp;');";
	$conf[]="ini_set('magic_quotes_runtime',     0);";
	$conf[]="ini_set('magic_quotes_sybase',      0);";
	$conf[]="ini_set('session.cache_expire',     200000);";
	$conf[]="ini_set('session.cache_limiter',    'none');";
	$conf[]="ini_set('session.cookie_lifetime',  2000000);";
	$conf[]="ini_set('session.gc_maxlifetime',   200000);";
	$conf[]="ini_set('session.save_handler',     'user');";
	$conf[]="ini_set('session.use_cookies',      1);";
	$conf[]="ini_set('session.use_only_cookies', 1);";
	$conf[]="ini_set('session.use_trans_sid',    0);";
	$conf[]="ini_set('url_rewriter.tags',        '');";
	$conf[]="?>";	
	
	@mkdir("/usr/share/drupal/sites/$apacheservername/files",0755,true);
	
	@file_put_contents("/usr/share/drupal/sites/$apacheservername/settings.php",@implode("\n",$conf));
	
	
}

function WEBDAV_USERS(){}



?>