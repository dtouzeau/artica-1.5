<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
include_once(dirname(__FILE__) . '/ressources/class.ldap.inc');
include_once(dirname(__FILE__) . '/framework/class.unix.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');
if(is_array($argv)){if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;	ini_set('html_errors',0);ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);}}


if($argv[1]=='--build'){build();die();}
if($argv[1]=='--sendmail'){sendMailCompile();die();}



function build(){
	
	checkdb();
	patch_config_sh();
	patch_bavd();
}


function patch_config_sh(){
	
	$datas=explode("\n",@file_get_contents("/home/openemm/bin/scripts/config.sh"));
	while (list ($num, $val) = each ($datas) ){
		if(preg_match('#sendmail="(.+?)"#', $val,$re)){
			echo "Starting......: OpenEMM patching config.sh line $num\n";
			$datas[$num]=str_replace($re[1], "/home/openemm/sendmail/sbin/sendmail", $datas[$num]);
		}
		
	}

@file_put_contents("/home/openemm/bin/scripts/config.sh", @implode("\n", $datas));
}
function patch_bavd(){
$datas=explode("\n",@file_get_contents("/home/openemm/bin/scripts/bavd.py"));
	while (list ($num, $val) = each ($datas) ){
			$datas[$num]=str_replace("/usr/sbin/sendmail", "/home/openemm/sendmail/sbin/sendmail", $datas[$num]);
		}
	echo "Starting......: OpenEMM patching bavd.py done\n";
	@file_put_contents("/home/openemm/bin/scripts/bavd.py", @implode("\n", $datas));		
		
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
	$OpenEMMNextMTA=$sock->GET_INFO("OpenEMMNextMTA");
	$OpenEMMNextMTAPort=$sock->GET_INFO("OpenEMMNextMTAPort");
	$OpenEMMSendMailPort=$sock->GET_INFO("OpenEMMSendMailPort");

	if($OpenEMMServerURL==null){$OpenEMMServerURL="http://127.0.0.1:8080";}	
	if($OpenEMMMailErrorRecipient==null){$OpenEMMMailErrorRecipient="openemm@localhost";}
	if($OpenEMMUserAgent==null){$OpenEMMUserAgent="OpenEMM V2011";}
	if($OpenEMMNextMTA==null){$OpenEMMNextMTA="nextsmtp.domain.tld";}
	if(!is_numeric($OpenEMMNextMTAPort)){$OpenEMMNextMTAPort="25";}
	if(!is_numeric($OpenEMMSendMailPort)){$OpenEMMSendMailPort="6880";}

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
$emm[]="system.mail.host=127.0.0.1:$OpenEMMSendMailPort";
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

function sendMailCompile(){
$sock=new sockets();
	$OpenEMMNextMTA=$sock->GET_INFO("OpenEMMNextMTA");
	$OpenEMMNextMTAPort=$sock->GET_INFO("OpenEMMNextMTAPort");
	$OpenEMMSendMailPort=$sock->GET_INFO("OpenEMMSendMailPort");
	if($OpenEMMNextMTA==null){$OpenEMMNextMTA="nextsmtp.domain.tld";}
	if(!is_numeric($OpenEMMNextMTAPort)){$OpenEMMNextMTAPort="25";}
	if(!is_numeric($OpenEMMSendMailPort)){$OpenEMMSendMailPort="6880";}


$f[]="ifdef(`_CF_DIR_', `',";
$f[]="	`ifelse(__file__, `__file__',";
$f[]="		`define(`_CF_DIR_', `../')',";
$f[]="		`define(`_CF_DIR_',";
$f[]="			substr(__file__, 0, eval(len(__file__) - 8)))')')";
$f[]="";
$f[]="divert(0)dnl";
$f[]="ifdef(`OSTYPE', `dnl',";
$f[]="`include(_CF_DIR_`'m4/cfhead.m4)dnl";
$f[]="VERSIONID(`\$Id: cf.m4,v 8.32 1999/02/07 07:26:14 gshapiro Exp \$')')\n";
@file_put_contents("/home/openemm/sendmail/etc/cf.m4", @implode("\n", $f));
if(!is_file("/home/openemm/sendmail/etc/auth-info")){@file_put_contents("/home/openemm/sendmail/etc/auth-info", " ");}
if(!is_file("/home/openemm/sendmail/etc/local-host-names")){@file_put_contents("/home/openemm/sendmail/etc/local-host-names", " ");}
if(!is_dir("/home/openemm/sendmail/run")){@mkdir("/home/openemm/sendmail/run",0755,true);}
if(!is_dir("/home/openemm/sendmail/spool/mqueue")){@mkdir("/home/openemm/sendmail/spool/mqueue",0755,true);}
if(!is_dir("/home/openemm/sendmail/spool/clientmqueue")){@mkdir("/home/openemm/sendmail/spool/clientmqueue",0755,true);}



$trusted[]="Ct openemm";
@file_put_contents("/home/openemm/sendmail/etc/trusted-users", @implode("\n", $trusted));

shell_exec("/bin/chown -R openemm:openemm /home/openemm/sendmail/etc");
shell_exec("/bin/chown openemm:openemm /home/openemm/sendmail");
shell_exec("/bin/chown openemm:openemm /home/openemm/sendmail/spool");
shell_exec("/bin/chown openemm:openemm /home/openemm/sendmail/spool/mqueue");
shell_exec("/bin/chown openemm:openemm /home/openemm/sendmail/spool/clientmqueue");
shell_exec("/bin/chown openemm:openemm /home/openemm/sendmail/run");
shell_exec("/bin/chmod 775 /home/openemm/sendmail/run");
shell_exec("/bin/chmod 644 /home/openemm/sendmail/spool/mqueue");
shell_exec("/bin/chmod 644 /home/openemm/sendmail/spool/clientmqueue");



$f[]="divert(-1)dnl";
$f[]="VERSIONID(`setup for openemm')dnl";
$f[]="include(`/home/openemm/sendmail/etc/cf.m4')dnl";
$f[]="OSTYPE(`linux')dnl";
$f[]="define(`confSMTP_LOGIN_MSG', `\$j Sendmail; \$b')dnl";
$f[]="define(`confLOG_LEVEL', `9')dnl";
$f[]="define(`SMART_HOST', `relay:$OpenEMMNextMTA')dnl";
$f[]="dnl define(`RELAY_MAILER',`esmtp')dnl";
$f[]="define(`RELAY_MAILER_ARGS', `TCP \$h $OpenEMMNextMTAPort')dnl";
$f[]="define(`confDEF_USER_ID', ``8:12'')dnl";
$f[]="dnl define(`confAUTO_REBUILD')dnl";
$f[]="define(`confTO_CONNECT', `1m')dnl";
$f[]="define(`confTRY_NULL_MX_LIST', `True')dnl";
$f[]="define(`confDONT_PROBE_INTERFACES', `True')dnl";
$f[]="define(`PROCMAIL_MAILER_PATH', `/usr/bin/procmail')dnl";
$f[]="define(`ALIAS_FILE', `/home/openemm/sendmail/etc/aliases')dnl";
$f[]="define(`STATUS_FILE', `/home/openemm/log/statistics')dnl";
$f[]="define(`QUEUE_DIR', `/home/openemm/sendmail/spool/mqueue*')dnl";
$f[]="define(`confTRUSTED_USER', `openemm')dnl";
$f[]="define(`confRUN_AS_USER', `openemm:openemm')dnl";
$f[]="FEATURE(`use_ct_file')dnl";
$f[]="FEATURE(`use_cw_file')dnl";
$f[]="define(`confCT_FILE', `-o /home/openemm/sendmail/etc/trusted-users')dnl";
$f[]="define(`confCW_FILE', `-o /home/openemm/sendmail/etc/local-host-names')dnl";
$f[]="define(`confPID_FILE', `/home/openemm/sendmail/run/sendmail.pid')dnl";
$f[]="define(`UUCP_MAILER_MAX', `2000000')dnl";
$f[]="define(`confUSERDB_SPEC', `/home/openemm/sendmail/etc/userdb.db')dnl";
$f[]="define(`confPRIVACY_FLAGS', `authwarnings,novrfy,noexpn,restrictqrun')dnl";
$f[]="define(`confAUTH_OPTIONS', `A')dnl";
$f[]="dnl define(`confAUTH_OPTIONS', `A p')dnl";
$f[]="dnl TRUST_AUTH_MECH(`EXTERNAL DIGEST-MD5 CRAM-MD5 LOGIN PLAIN')dnl";
$f[]="dnl define(`confAUTH_MECHANISMS', `EXTERNAL GSSAPI DIGEST-MD5 CRAM-MD5 LOGIN PLAIN')dnl";
$f[]="dnl define(`confCACERT_PATH', `/etc/pki/tls/certs')dnl";
$f[]="dnl define(`confCACERT', `/etc/pki/tls/certs/ca-bundle.crt')dnl";
$f[]="dnl define(`confSERVER_CERT', `/etc/pki/tls/certs/sendmail.pem')dnl";
$f[]="dnl define(`confSERVER_KEY', `/etc/pki/tls/certs/sendmail.pem')dnl";
$f[]="dnl define(`confTO_QUEUEWARN', `4h')dnl";
$f[]="dnl define(`confTO_QUEUERETURN', `5d')dnl";
$f[]="dnl define(`confQUEUE_LA', `12')dnl";
$f[]="dnl define(`confREFUSE_LA', `18')dnl";
$f[]="define(`confTO_IDENT', `0')dnl";
$f[]="dnl FEATURE(delay_checks)dnl";
$f[]="FEATURE(`no_default_msa', `dnl')dnl";
$f[]="dnl FEATURE(`smrsh', `/usr/sbin/smrsh')dnl";
$f[]="dnl FEATURE(`mailertable', `hash -o /etc/mail/mailertable.db')dnl";
$f[]="dnl FEATURE(`virtusertable', `hash -o /etc/mail/virtusertable.db')dnl";
$f[]="FEATURE(redirect)dnl";
$f[]="FEATURE(always_add_domain)dnl";
$f[]="FEATURE(use_cw_file)dnl";
$f[]="FEATURE(use_ct_file)dnl";
$f[]="dnl define(`confMAX_DAEMON_CHILDREN', `20')dnl";
$f[]="dnl define(`confCONNECTION_RATE_THROTTLE', `3')dnl";
$f[]="dnl FEATURE(local_procmail, `', `procmail -t -Y -a \$h -d \$u')dnl";
$f[]="dnl FEATURE(`access_db', `hash -T<TMPF> -o /etc/mail/access.db')dnl";
$f[]="dnl FEATURE(`blacklist_recipients')dnl";
$f[]="dnl EXPOSED_USER(`root')dnl";
$f[]="dnl define(`confLOCAL_MAILER', `cyrusv2')dnl";
$f[]="dnl define(`CYRUSV2_MAILER_ARGS', `FILE /var/lib/imap/socket/lmtp')dnl";
echo "Starting......: Sendmail for OpenEMM server local port : 127.0.0.1:$OpenEMMSendMailPort\n";
$f[]="DAEMON_OPTIONS(`Port=$OpenEMMSendMailPort,Addr=127.0.0.1, Name=MTA')dnl";
$f[]="dnl DAEMON_OPTIONS(`Port=submission, Name=MSA, M=Ea')dnl";
$f[]="dnl DAEMON_OPTIONS(`Port=smtps, Name=TLSMTA, M=s')dnl";
$f[]="dnl DAEMON_OPTIONS(`port=smtp,Addr=::1, Name=MTA-v6, Family=inet6')dnl";
$f[]="dnl DAEMON_OPTIONS(`Name=MTA-v4, Family=inet, Name=MTA-v6, Family=inet6')";
$f[]="dnl FEATURE(`accept_unresolvable_domains')dnl";
$f[]="dnl FEATURE(`relay_based_on_MX')dnl";
$f[]="LOCAL_DOMAIN(`localhost.localdomain')dnl";
$f[]="dnl MASQUERADE_AS(`mydomain.com')dnl";
$f[]="dnl FEATURE(masquerade_envelope)dnl";
$f[]="dnl FEATURE(masquerade_entire_domain)dnl";
$f[]="dnl MASQUERADE_DOMAIN(localhost)dnl";
$f[]="dnl MASQUERADE_DOMAIN(localhost.localdomain)dnl";
$f[]="dnl MASQUERADE_DOMAIN(mydomainalias.com)dnl";
$f[]="dnl MASQUERADE_DOMAIN(mydomain.lan)dnl";
$f[]="MAILER(smtp)dnl";
$f[]="MAILER(procmail)dnl";
$f[]="dnl MAILER(cyrusv2)dnl";
$f[]="TRUST_AUTH_MECH(`GSSAPI DIGEST-MD5 CRAM-MD5 LOGIN')dnl";
$f[]="define(`confAUTH_MECHANISMS', `GSSAPI DIGEST-MD5 CRAM-MD5 LOGIN')dnl";
$f[]="define(`confDEF_AUTH_INFO', `/home/openemm/sendmail/etc/auth-info')dnl";

$f[]="dnl\n";	

@file_put_contents("/home/openemm/sendmail/etc/sendmail.mc", @implode("\n", $f));

unset($f);
$f[]="divert(-1)";
$f[]="divert(0)dnl";
$f[]="VERSIONID(`\$Id: submit.mc,v 8.14 2006/04/05 05:54:41 ca Exp \$')";
$f[]="define(`confCF_VERSION', `Submit')dnl";
$f[]="define(`__OSTYPE__',`')dnl dirty hack to keep proto.m4 from complaining";
$f[]="define(`_USE_DECNET_SYNTAX_', `1')dnl support DECnet";
$f[]="define(`confTIME_ZONE', `USE_TZ')dnl";
$f[]="define(`confDONT_INIT_GROUPS', `True')dnl";
$f[]="dnl";
$f[]="dnl If you use IPv6 only, change [127.0.0.1] to [IPv6:::1]";
$f[]="FEATURE(`msp', `[127.0.0.1]')dnl";
$f[]="define(`confRUN_AS_USER', `openemm:openemm')dnl";
$f[]="define(`confPID_FILE', `/home/openemm/sendmail/run/sm-client.pid')dnl";
$f[]="define(`QUEUE_DIR', `/home/openemm/sendmail/run/sm-client.pid')dnl\n";
@file_put_contents("/home/openemm/sendmail/etc/submit.mc", @implode("\n", $f));

$unix=new unix();
$m4=$unix->find_program("m4");
echo "Starting......: Sendmail for OpenEMM server using m4:$m4\n";

shell_exec("$m4 /home/openemm/sendmail/etc/cf.m4 /home/openemm/sendmail/etc/sendmail.mc > /home/openemm/sendmail/etc/sendmail.cf");
shell_exec("$m4 /home/openemm/sendmail/etc/cf.m4 /home/openemm/sendmail/etc/submit.mc > /home/openemm/sendmail/etc/submit.cf");
echo "Starting......: Sendmail for OpenEMM server submit.cf & sendmail.cf done...\n";
@unlink("/etc/mail/sendmail.cf");
@unlink("/etc/mail/submit.cf");
if(!is_dir("/etc/mail")){@mkdir("/etc/mail");}
shell_exec("/bin/ln -s -f /home/openemm/sendmail/etc/sendmail.cf /etc/mail/sendmail.cf >/dev/null 2>&1");
shell_exec("/bin/ln -s -f /home/openemm/sendmail/etc/submit.cf /etc/mail/submit.cf >/dev/null 2>&1");


}

