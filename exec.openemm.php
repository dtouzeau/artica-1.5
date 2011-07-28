<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
include_once(dirname(__FILE__) . '/ressources/class.ldap.inc');
include_once(dirname(__FILE__) . '/framework/class.unix.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');
if(is_array($argv)){if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;	ini_set('html_errors',0);ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);}}


if($argv[1]=='--build'){build();die();}



function build(){
	
	checkdb();
	
}


function checkdb(){
	$unix=new unix();
	$q=new mysql();
	$JAVA_HOME=$unix->JAVA_HOME_GET();
	if(strlen($JAVA_HOME)==0){echo "Starting......: OpenEMM JAVA_HOME failed\n";return;}
	echo "Starting......: OpenEMM JAVA_HOME $JAVA_HOME\n";
	if(!is_file("/home/openemm/bin/openemm.sh")){
		echo "Starting......: OpenEMM /home/openemm/bin/openemm.sh no such file\n";
		return;
	}
	
	if(!is_file("/opt/openemm/tomcat6/bin/startup.sh")){
		echo "Starting......: OpenEMM tomcat 6.x is not installed\n";
		return;
	}
	
	
	
	if(!$q->DATABASE_EXISTS("openemm")){$q->CREATE_DATABASE("openemm");}
	if(!$q->DATABASE_EXISTS("openemm")){echo "Starting......: OpenEMM failed creating database openemm\n";return;}
	echo "Starting......: OpenEMM database openemm OK\n";
	
	if(!$q->DATABASE_EXISTS("openemm_cms")){$q->CREATE_DATABASE("openemm_cms");}
	if(!$q->DATABASE_EXISTS("openemm_cms")){echo "Starting......: OpenEMM failed creating database openemm_cms\n";return;}
	
	if(!test_cms_tables()){
		if(is_file("/home/openemm/USR_SHARE/openemm_cms-2011.sql")){
			$mysql=$unix->find_program("mysql");
			$cmd="$mysql -u $q->mysql_admin -p\"$q->mysql_password\" --batch --database=openemm_cms < /home/openemm/USR_SHARE/openemm_cms-2011.sql";
			shell_exec($cmd);
		}
	}
		
	
	if(!testtables()){
		$mysql=$unix->find_program("mysql");
		if(is_file("/home/openemm/USR_SHARE/openemm-2011.sql")){$cmd="$mysql -u $q->mysql_admin -p\"$q->mysql_password\" --batch --database=openemm < /home/openemm/USR_SHARE/openemm-2011.sql";shell_exec($cmd);}}
		
	if(!testtables()){echo "Starting......: OpenEMM failed creating openemm tables\n";return;}
	if(!test_cms_tables()){echo "Starting......: OpenEMM failed creating openemm_cms tables\n";return;}
	
	
	echo "Starting......: OpenEMM tables in openemm base OK\n";
	echo "Starting......: OpenEMM tables in openemm_cms base OK\n";
	if(!$unix->CreateUnixUser("openemm")){
		echo "Starting......: OpenEMM unix user openemm failed\n";
		return;
	}
	echo "Starting......: OpenEMM unix user openemm OK\n";
	patch_javahome($JAVA_HOME);
	patch_tomcat_dir("/opt/openemm/tomcat6");
	if(!is_dir("/home/openemm/logs")){
		echo "Starting......: OpenEMM creating /home/openemm/logs directory\n";
		@mkdir("/home/openemm/logs",755,true);
		
	}
	
	$aa_complain=$unix->find_program("aa-complain");
	if(is_file($aa_complain)){shell_exec("$aa_complain $JAVA_HOME/bin/java");}
	if(!is_file("/home/openemm/webapps/openemm/WEB-INF/classes/messages_en_US.properties")){
		shell_exec("/bin/cp /home/openemm/webapps/openemm/WEB-INF/classes/messages_en.properties /home/openemm/webapps/openemm/WEB-INF/classes/messages_en_US.properties");
	}
	cms_properties();
	
	if(!is_dir("/home/openemm/work/Catalina/openemm/_")){@mkdir("/home/openemm/work/Catalina/openemm/_",755,true);}
	shell_exec("/bin/chown openemm /home/openemm");
	shell_exec("/bin/chown -R openemm /home/openemm");
	
	if(is_numeric(is_tomcat_running())){
		echo "Starting......: OpenEMM stopping tomcat first...\n";
		shell_exec("/etc/init.d/artica-postfix stop tomcat");
	}
	@unlink("/home/openemm/logs/catalina.out");
	@unlink("/home/openemm/logs/openemm/openemm_axis.log");
	@unlink("/home/openemm/logs/openemm/openemm_axis.log");
	@unlink("/home/openemm/logs/openemm/openemm_core.log");
	@unlink("/home/openemm/logs/openemm/userlogs.log");
	
}


function cms_properties(){
$q=new mysql();	
if($q->mysql_server=="localhost"){$q->mysql_server="127.0.0.1";}
$cms[]="#####################################################";
$cms[]="# Database settings";
$cms[]="#####################################################";
$cms[]="";
$cms[]="cmsdb.driverClassName=com.mysql.jdbc.Driver";
$cms[]="cmsdb.url=jdbc:mysql://$q->mysql_server:$q->mysql_port/openemm_cms?useUnicode=true&amp;characterEncoding=UTF8&amp;jdbcCompliantTruncation=false";
$cms[]="cmsdb.dialect=org.hibernate.dialect.MySQLDialect";
$cms[]="cmsdb.username=$q->mysql_admin";
$cms[]="cmsdb.password=$q->mysql_password";
$cms[]="cmsdb.maxCount=30";
$cms[]="cmsdb.maxWait=10000";
$cms[]="cmsdb.validationQuery=SELECT 1 FROM DUAL";
$cms[]="";
$cms[]="#####################################################";
$cms[]="#Remote webservices settings";
$cms[]="#####################################################";
$cms[]="cms.ccr.url=http://192.168.1.105:8080";	

@file_put_contents("/home/openemm/webapps/openemm/WEB-INF/classes/cms.properties", @implode("\n", $cms));
echo "Starting......: OpenEMM creating cms.properties done.\n";	
	
}

function emm_properties(){
$sock=new sockets();
$OpenEMMServerURL=$sock->GET_INFO("OpenEMMServerURL");
$OpenEMMMailErrorRecipient=$sock->GET_INFO("OpenEMMMailErrorRecipient");
$OpenEMMUserAgent=$sock->GET_INFO("OpenEMMUserAgent");
if($OpenEMMServerURL==null){$OpenEMMServerURL="http://127.0.0.1:8080";}	
if($OpenEMMMailErrorRecipient==null){$OpenEMMMailErrorRecipient="openemm@localhost";}
if($OpenEMMUserAgent==null){$OpenEMMUserAgent="OpenEMM V2011";}

$q=new mysql();
if($q->mysql_server=="localhost"){$q->mysql_server="127.0.0.1";}		
$emm[]="###############################################################################";
$emm[]="# Database Connection Settings (dataAccessContxt.xml, etc.)";
$emm[]="###############################################################################";
$emm[]="jdbc.driverClassName=com.mysql.jdbc.Driver";
$emm[]="jdbc.url=jdbc:mysql://$q->mysql_server:$q->mysql_port/openemm?useUnicode=yes&characterEncoding=UTF-8&useOldAliasMetadataBehavior=true";
$emm[]="jdbc.dialect=org.hibernate.dialect.MySQLDialect";
$emm[]="jdbc.username=$q->mysql_admin";
$emm[]="jdbc.password=$q->mysql_password";
$emm[]="jdbc.maxCount=30";
$emm[]="jdbc.maxWait=10000";
$emm[]="jdbc.validationQuery=SELECT 1 FROM DUAL";
$emm[]="";
$emm[]="###############################################################################";
$emm[]="# System Default Values";
$emm[]="###############################################################################";
$emm[]="system.url=$OpenEMMServerURL";
$emm[]="system.logdir=/home/openemm/var/log";
$emm[]="system.upload=/tmp";
$emm[]="system.updateserver=http://www.openemm.org/";
$emm[]="system.mail.host=127.0.0.1";
$emm[]="";
$emm[]="###############################################################################";
$emm[]="# Feature Default Values";
$emm[]="###############################################################################";
$emm[]="password.expire.days=-1";
$emm[]="fckpath=fckeditor-2.6.4.1";
$emm[]="ecs.server.url=$OpenEMMServerURL";
$emm[]="";
$emm[]="# max number of recipients in database";
$emm[]="recipient.maxRows=200000";
$emm[]="# max size of bulk import";
$emm[]="import.maxRows=60000";
$emm[]="# max size of attachments";
$emm[]="# (check for MySQL parameter max_allowed_packet > attachment.maxSize)";
$emm[]="attachment.maxSize=1048576";
$emm[]="";
$emm[]="# scheduler configuration for mailings and DB cleaning";
$emm[]="delayedMailings.cronExpression=0 0,15,30,45 * * * ?";
$emm[]="dateBasedMailings.cronExpression=0 0 * * * ?";
$emm[]="cleanDB.cronExpression=0 0 3 * * ?";
$emm[]="bounces.maxRemain.days=90";
$emm[]="pending.maxRemain.days=30";
$emm[]="";
$emm[]="# scheduler configuration for removing old login track records";
$emm[]="loginTrackCleaner.retentionTime=7";
$emm[]="loginTrackCleaner.deleteBlockSize=1000";
$emm[]="loginTrackCleaner.cronExpression=0 0 4 * * ?";
$emm[]="";
$emm[]="# available languages for online help";
$emm[]="onlinehelp.languages = de,en,fr";
$emm[]="";
$emm[]="mail.error.recipient=$OpenEMMMailErrorRecipient";
$emm[]="";
$emm[]="###############################################################################";
$emm[]="# Default Values for Caches (Images, Tracking, User Forms, Mailings, Backend)";
$emm[]="###############################################################################";
$emm[]="hostedImage.maxCache=500";
$emm[]="hostedImage.maxCacheTimeMillis=300000";
$emm[]="rdir.keys.maxCache=500";
$emm[]="rdir.keys.maxCacheTimeMillis=300000";
$emm[]="onepixel.keys.maxCache=500";
$emm[]="onepixel.keys.maxCacheTimeMillis=300000";
$emm[]="archive.maxCache=100";
$emm[]="archive.maxCacheTimeMillis=300000";
$emm[]="mailgun.maxCache=100";
$emm[]="mailgun.maxCacheTimeMillis=300000";
$emm[]="company.maxCache=100";
$emm[]="company.maxCacheTimeMillis=300000";
$emm[]="";
$emm[]="###############################################################################";
$emm[]="# Backend Default Values";
$emm[]="###############################################################################";
$emm[]="mailgun.ini.loglevel=WARNING";
$emm[]="mailgun.ini.maildir=/home/openemm/var/spool/ADMIN";
$emm[]="mailgun.ini.default_encoding=quoted-printable";
$emm[]="mailgun.ini.default_charset=ISO-8859-1";
$emm[]="mailgun.ini.db_login=::jdbc.username";
$emm[]="mailgun.ini.db_password=::jdbc.password";
$emm[]="mailgun.ini.sql_connect=::jdbc.url";
$emm[]="mailgun.ini.blocksize=1000";
$emm[]="mailgun.ini.metadir=/home/openemm/var/spool/META";
$emm[]="mailgun.ini.xmlback=/home/openemm/bin/xmlback";
$emm[]="mailgun.ini.account_logfile=/home/openemm/var/spool/log/account.log";
$emm[]="mailgun.ini.xmlvalidate=False";
$emm[]="mailgun.ini.domain=openemm.invalid";
$emm[]="mailgun.ini.mail_log_number=400";
$emm[]="mailgun.ini.eol=LF";
$emm[]="mailgun.ini.mailer=$OpenEMMUserAgent";
$emm[]="";
$emm[]="###############################################################################";
$emm[]="# Import Wizard";
$emm[]="###############################################################################";
$emm[]="import.report.from.address=openemm@localhost";
$emm[]="import.report.from.name=";
$emm[]="import.report.replyTo.address=openemm@localhost";
$emm[]="import.report.replyTo.name=";
$emm[]="import.report.bounce=openemm@localhost";
$emm[]="";	
@file_put_contents("/home/openemm/webapps/openemm/WEB-INF/classes/emm.properties", @implode("\n", $cms));
echo "Starting......: OpenEMM creating emm.properties done.\n";	
	
	
}


function patch_javahome($java){
	$f=@explode("\n", @file_get_contents("/home/openemm/bin/openemm.sh"));
	while (list ($num, $val) = each ($f) ){
		if(preg_match('#JAVA_HOME="(.*?)"#', $val,$re)){
			if($re[1]==$java){echo "Starting......: OpenEMM openemm.sh (java) OK\n";return;}
			$f[$num]="JAVA_HOME=\"$java\"";
			@file_put_contents("/home/openemm/bin/openemm.sh", @implode("\n", $f));
			echo "Starting......: OpenEMM openemm.sh (java) OK\n";return;
		}
	}
	echo "Starting......: OpenEMM openemm.sh (java) FAILED\n";return;
}

function patch_tomcat_dir($dir){
	$f=@explode("\n", @file_get_contents("/home/openemm/bin/openemm.sh"));
	while (list ($num, $val) = each ($f) ){
		if(preg_match('#CATALINA_HOME="(.*?)"#', $val,$re)){
			if($re[1]==$dir){echo "Starting......: OpenEMM openemm.sh (tomcat) OK\n";return;}
			$f[$num]="CATALINA_HOME=\"$dir\"";
			@file_put_contents("/home/openemm/bin/openemm.sh", @implode("\n", $f));
			echo "Starting......: OpenEMM openemm.sh (tomcat) OK\n";return;
		}
	}
	echo "Starting......: OpenEMM openemm.sh (tomcat) FAILED\n";return;	

	
}

function is_tomcat_running(){
	$unix=new unix();
	$ps=$unix->find_program("ps");
	$grep=$unix->find_program("grep");
	$awk=$unix->find_program("awk");
	$cmd="$ps -eo pid,command|$grep org.apache.catalina|$grep -v grep|$awk '{print $1}' 2>&1";
	
	exec($cmd,$results);
	$pid=trim(@implode("", $results));
	if($GLOBALS["VERBOSE"]){echo "Starting......: OpenEMM [$pid] \"$cmd\"\n";}
	
	if(is_numeric($pid)){return $pid;}
	return null;
	
}

function test_cms_tables(){
$q=new mysql();	
$tables[]="cm_category_tbl";
$tables[]="cm_category_tbl_seq";
$tables[]="cm_content_module_tbl";
$tables[]="cm_content_tbl";
$tables[]="cm_content_tbl_seq";
$tables[]="cm_location_tbl";
$tables[]="cm_location_tbl_seq";
$tables[]="cm_mailing_bind_tbl";
$tables[]="cm_mailing_bind_tbl_seq";
$tables[]="cm_media_file_tbl";
$tables[]="cm_media_file_tbl_seq";
$tables[]="cm_tbl_seq";
$tables[]="cm_template_mail_bind_tbl_seq";
$tables[]="cm_template_mailing_bind_tbl";
$tables[]="cm_template_tbl";
$tables[]="cm_template_tbl_seq";
$tables[]="cm_text_version_tbl";
$tables[]="cm_text_version_tbl_seq";
$tables[]="cm_type_tbl";
$tables[]="cm_type_tbl_seq";

while (list ($num, $tbl) = each ($tables) ){
		if(!$q->TABLE_EXISTS($tbl, "openemm_cms")){
			echo "Starting......: OpenEMM CMS: $tbl no such table\n";
			return false;
		}
		
	}
return true;	
}



function testtables(){
	$q=new mysql();
	$tables[]="admin_group_permission_tbl";
	$tables[]="admin_group_tbl";
	$tables[]="admin_permission_tbl";
	$tables[]="admin_tbl";
	$tables[]="bounce_collect_tbl";
	$tables[]="bounce_tbl";
	$tables[]="campaign_tbl";
	$tables[]="click_stat_colors_tbl";
	$tables[]="company_tbl";
	$tables[]="component_tbl";
	$tables[]="config_tbl";
	$tables[]="cust_ban_tbl";
	$tables[]="customer_1_binding_tbl";
	$tables[]="customer_1_tbl";
	$tables[]="customer_1_tbl_seq";
	$tables[]="customer_field_tbl";
	$tables[]="customer_import_errors_tbl";
	$tables[]="customer_import_status_tbl";
	$tables[]="datasource_description_tbl";
	$tables[]="date_tbl";
	$tables[]="dyn_content_tbl";
	$tables[]="dyn_name_tbl";
	$tables[]="dyn_target_tbl";
	$tables[]="emm_layout_tbl";
	$tables[]="export_predef_tbl";
	$tables[]="import_column_mapping_tbl";
	$tables[]="import_gender_mapping_tbl";
	$tables[]="import_log_tbl";
	$tables[]="import_profile_tbl";
	$tables[]="log_tbl";
	$tables[]="login_track_tbl";
	$tables[]="maildrop_status_tbl";
	$tables[]="mailing_account_tbl";
	$tables[]="mailing_backend_log_tbl";
	$tables[]="mailing_mt_tbl";
	$tables[]="mailing_tbl";
	$tables[]="mailinglist_tbl";
	$tables[]="mailloop_tbl";
	$tables[]="mailtrack_tbl";
	$tables[]="onepixel_log_tbl";
	$tables[]="rdir_action_tbl";
	$tables[]="rdir_log_tbl";
	$tables[]="rdir_url_tbl";
	$tables[]="rulebased_sent_tbl";
	$tables[]="softbounce_email_tbl";
	$tables[]="tag_tbl";
	$tables[]="timestamp_tbl";
	$tables[]="title_gender_tbl";
	$tables[]="title_tbl";
	$tables[]="userform_tbl";
	$tables[]="ws_admin_tbl";	
	while (list ($num, $tbl) = each ($tables) ){
		if(!$q->TABLE_EXISTS($tbl, "openemm")){
			echo "Starting......: OpenEMM $tbl no such table\n";
			return false;
		}
		
	}
return true;
	
}

