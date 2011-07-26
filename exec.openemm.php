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
	if(!$q->DATABASE_EXISTS("openemm")){echo "Starting......: OpenEMM failed creating database\n";return;}
	echo "Starting......: OpenEMM database OK\n";
	if(!testtables()){
		$mysql=$unix->find_program("mysql");
		if(is_file("/home/openemm/USR_SHARE/openemm-2011.sql")){$cmd="$mysql -u $q->mysql_admin -p\"$q->mysql_password\" --batch --database=openemm < /home/openemm/USR_SHARE/openemm-2011.sql";shell_exec($cmd);}}
		
	if(!testtables()){echo "Starting......: OpenEMM failed creating tables\n";return;}
	echo "Starting......: OpenEMM tables in openemm base OK\n";
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
	shell_exec("/bin/chown openemm:openemm /home/openemm/logs");
	shell_exec("export CATALINA_PID=\"/opt/openemm/tomcat/temp/tomcat.pid\"");
	if(is_numeric(is_tomcat_running())){
		echo "Starting......: OpenEMM stopping tomcat first...\n";
		shell_exec("/etc/init.d/artica-postfix stop tomcat");
	}
	
	// replace /home/openemm/webapps/openemm/WEB-INF/classes/emm.properties
	
	//  /home/openemm/webapps/openemm/WEB-INF/classes/cms.properties
	
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

