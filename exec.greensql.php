<?php
$GLOBALS["FORCE"]=false;
if(is_array($argv)){if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;}if($GLOBALS["VERBOSE"]){ini_set('html_errors',0);ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);}}
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
$GLOBALS["posix_getuid"]=0;
include_once(dirname(__FILE__) . '/ressources/class.users.menus.inc');
include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
include_once(dirname(__FILE__) . '/ressources/class.user.inc');
include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
include_once(dirname(__FILE__) . '/framework/class.unix.inc');
include_once(dirname(__FILE__) . '/framework/frame.class.inc');


if($argv[1]=="--build"){build();die();}
if($argv[1]=="-V"){$GLOBALS["VERBOSE"]=true;echo Greensqlversion()."\n";die();}
if($argv[1]=="--sets"){GreensqlDaemonsSettings();die();}



function build(){
	echo "Starting......: GreenSQL checking Database....\n";
	$q=new mysql();
	if(!$q->DATABASE_EXISTS("greensql")){echo "Starting......: GreenSQL creating database greensql\n";$q->CREATE_DATABASE("greensql");}
	checkGreenTables();
	buildconfig();
	
}

function GreensqlDaemonsSettings(){
	
	$sql="SELECT frontend_ip,frontend_port FROM proxy WHERE proxyid=1";
	$q=new mysql();	
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
	@file_put_contents("/etc/artica-postfix/settings/Mysql/GreenIP", $ligne["frontend_ip"]);
	@file_put_contents("/etc/artica-postfix/settings/Mysql/GreenPort", $ligne["frontend_port"]);	
}


function checkGreenTables(){
	
	$q=new mysql();
	if(!$q->TABLE_EXISTS("query", "greensql")){
		echo "Starting......: GreenSQL creating query table\n";
		$sql="CREATE table query(
		queryid int unsigned NOT NULL auto_increment primary key,
		proxyid        int unsigned NOT NULL default '0',
		perm           smallint unsigned NOT NULL default 1,
		db_name        char(50) NOT NULL,
		query          text NOT NULL,
		INDEX(proxyid,db_name)
		) DEFAULT CHARSET=utf8;
		";
		$q->QUERY_SQL($sql,"greensql");
		if(!$q->ok){echo "Starting......: GreenSQL failed $q->mysql_error\n";}
	}


	if(!$q->TABLE_EXISTS("proxy", "greensql")){
		echo "Starting......: GreenSQL creating proxy table\n";
		$sql="
			CREATE table proxy
			(
			proxyid        int unsigned NOT NULL auto_increment primary key,
			proxyname      char(50) NOT NULL default '',
			frontend_ip    char(20) NOT NULL default '',
			frontend_port  smallint unsigned NOT NULL default 0,
			backend_server char(50) NOT NULL default '',
			backend_ip     char(20) NOT NULL default '',
			backend_port   smallint unsigned NOT NULL default 0,
			dbtype         char(20) NOT NULL default 'mysql',
			status         smallint unsigned NOT NULL default '1'
			) DEFAULT CHARSET=utf8;";
		$q->QUERY_SQL($sql,"greensql");
		if(!$q->ok){echo "Starting......: GreenSQL failed $q->mysql_error\n";}
		$q->QUERY_SQL("insert into proxy values (1,'Default MySQL Proxy','127.0.0.1',3305,'localhost','127.0.0.1',3306,'mysql',1);","greensql");
		$q->QUERY_SQL("insert into proxy values (2,'Default PgSQL Proxy','127.0.0.1',5431,'localhost','127.0.0.1',5432,'pgsql',1);","greensql");
	}


	if(!$q->TABLE_EXISTS("db_perm", "greensql")){
		echo "Starting......: GreenSQL creating db_perm table\n";
		$sql="CREATE table db_perm
			(
			dbpid          int unsigned NOT NULL auto_increment primary key,
			proxyid        int unsigned NOT NULL default '0',
			db_name        char(50) NOT NULL,
			perms          bigint unsigned NOT NULL default '0',
			perms2         bigint unsigned NOT NULL default '0',
			status         smallint unsigned NOT NULL default '0',
			sysdbtype      char(20) NOT NULL default 'user_db',
			status_changed datetime NOT NULL default '00-00-0000 00:00:00',
			INDEX (proxyid, db_name)
			) DEFAULT CHARSET=utf8;";
		
		$q->QUERY_SQL($sql,"greensql");
		if(!$q->ok){echo "Starting......: GreenSQL failed $q->mysql_error\n";}
		$q->QUERY_SQL("insert into db_perm (dbpid, proxyid, db_name, sysdbtype) values (1,0,'default mysql db', 'default_mysql');","greensql");
		$q->QUERY_SQL("insert into db_perm (dbpid, proxyid, db_name, sysdbtype) values (2,0,'no-name mysql db', 'empty_mysql');","greensql");
		$q->QUERY_SQL("insert into db_perm (dbpid, proxyid, db_name, sysdbtype) values (3,0,'default pgsql db', 'default_pgsql');","greensql");
	}
	
	
	if(!$q->TABLE_EXISTS("admin", "greensql")){
		echo "Starting......: GreenSQL creating admin table\n";
		$sql= "CREATE table admin(
			adminid         int unsigned NOT NULL auto_increment primary key,
			name           char(50) NOT NULL default '',
			pwd            char(50) NOT NULL default '',
			email          char(50) NOT NULL default ''
			) DEFAULT CHARSET=utf8;";
		
		$q->QUERY_SQL($sql,"greensql");
		if(!$q->ok){echo "Starting......: GreenSQL failed $q->mysql_error\n";}
		$q->QUERY_SQL("insert into admin values(1,'admin',sha1('pwd'),'');","greensql");

	}

	if(!$q->TABLE_EXISTS("alert", "greensql")){
		echo "Starting......: GreenSQL creating alert table\n";
		$sql= "CREATE table alert
			(
			alertid             int unsigned NOT NULL auto_increment primary key,
			agroupid            int unsigned NOT NULL default '0',
			event_time          datetime NOT NULL default '00-00-0000 00:00:00',
			risk                smallint unsigned NOT NULL default '0',
			block               smallint unsigned NOT NULL default '0',
			dbuser              varchar(50) NOT NULL default '',
			userip              varchar(50) NOT NULL default '',
			query               text NOT NULL,
			reason              text NOT NULL,
			INDEX (agroupid)
			) DEFAULT CHARSET=utf8;";
		$q->QUERY_SQL($sql,"greensql");
		if(!$q->ok){echo "Starting......: GreenSQL failed $q->mysql_error\n";}
	}



	if(!$q->TABLE_EXISTS("alert_group", "greensql")){
		echo "Starting......: GreenSQL creating alert_group table\n";
		$sql= "CREATE table alert_group(
			agroupid            int unsigned NOT NULL auto_increment primary key,
			proxyid             int unsigned NOT NULL default '1',
			db_name             char(50) NOT NULL default '',
			update_time         datetime NOT NULL default '00-00-0000 00:00:00',
			status              smallint NOT NULL default 0,
			pattern             text NOT NULL,
			INDEX(update_time)
			)";	
			$q->QUERY_SQL($sql,"greensql");
		if(!$q->ok){echo "Starting......: GreenSQL failed $q->mysql_error\n";}
	}	
	echo "Starting......: GreenSQL check tables done...\n";
}

function Greensqlversion(){
	$f=explode("\n", @file_get_contents("/usr/share/greensql-console/config.php"));
	while (list ($num, $ligne) = each ($f) ){
		if(preg_match("#version.+?([0-9\.]+)#", $ligne,$re)){
			
			return $re[1];
		}else{
			if($GLOBALS["VERBOSE"]){echo "\"$ligne\" ->NO MATCH\n";}
		}
	}
	
}

function buildconfig(){
$version=Greensqlversion();
$f[]="#database settings";
$q=new mysql();
$f[]="[database]";
$f[]="dbhost=$q->mysql_server";
$f[]="dbname=greensql";
$f[]="dbuser=$q->mysql_admin";
$f[]="dbpass=$q->mysql_password";
$f[]="dbtype=mysql";
$f[]="";
$f[]="[logging]";
$f[]="# logfile - this parameter specifies location of the log file.";
$f[]="# By default this will point to /var/log/greensql.log file in linux.";
$f[]="logfile = /var/log/greensql.log";
$f[]="# loglevel - this parameter specifies level of logs to produce.";
$f[]="# Bigger value yelds more debugging information.";
$f[]="loglevel = 10";
$f[]="";
$f[]="[risk engine]";
$f[]="# If query risk is bigger then specified value, query will be blocked";
$f[]="block_level = 30";
$f[]="# Level of risk used to generate warnings. It is recomended to run application";
$f[]="# in low warning level and then to acknowledge all valid queries and";
$f[]="# then to lower the block_level";
$f[]="warn_level=20";
$f[]="# Risk factor associated with SQL comments";
$f[]="risk_sql_comments=30";
$f[]="# Risk factor associated with access to sensitive tables";
$f[]="risk_senstivite_tables=10";
$f[]="# Risk factor associated with 'OR' SQL token";
$f[]="risk_or_token=5";
$f[]="# Risk factor associated with 'UNION' SQL statement";
$f[]="risk_union_token=10";
$f[]="# Risk factor associated with variable comparison. For example: 1 = 1";
$f[]="risk_var_cmp_var=30";
$f[]="# Risk factor associated with variable ony operation which is always true.";
$f[]="# For example: SELECT XXX from X1 WHERE 1";
$f[]="risk_always_true=30";
$f[]="# Risk factor associated with an empty password SQL operation.";
$f[]="# For example : SELECT * from users where password = \"\"";
$f[]="# It works with the following fields: pass/pwd/passwd/password";
$f[]="risk_empty_password=30";
$f[]="# Risk factor associated with miltiple queires which are separated by ";"";
$f[]="risk_multiple_queries=30";
$f[]="# Risk of SQL commands that can used to bruteforce database content.";
$f[]="risk_bruteforce=15";
@mkdir("/etc/greensql",644,true);
@file_put_contents("/etc/greensql/greensql.conf", @implode("\n", $f));
echo "Starting......: GreenSQL check greensql.conf done...\n";
unset($f);
echo "Starting......: GreenSQL v$version\n";
$f[]="<?php";
$f[]="\$version = \"$version\";";
$f[]="\$db_type = \"mysql\";";
$f[]="\$db_host = \"$q->mysql_server\";";
$f[]="\$db_port = $q->mysql_port;";
$f[]="\$db_name = \"greensql\";";
$f[]="\$db_user = \"$q->mysql_admin\";";
$f[]="\$db_pass = \"$q->mysql_password\";";
$f[]="\$log_file = \"/var/log/greensql.log\";";
$f[]="\$num_log_lines = 200;";
$f[]="\$limit_per_page = 10;";
$f[]="\$cache_dir = \"templates_c\";";
$f[]="\$smarty_dir = \"/usr/share/php/smarty\";";
$f[]="";
$f[]="?>";
@file_put_contents("/usr/share/greensql-console/config.php", @implode("\n", $f));
shell_exec("/bin/chmod 0777 /usr/share/greensql-console/templates_c");
echo "Starting......: GreenSQL check config.php done...\n";


}



