<?php
$GLOBALS["KAV4PROXY_NOSESSION"]=true;
$GLOBALS["RELOAD"]=false;
$_GET["LOGFILE"]="/var/log/artica-postfix/dansguardian.compile.log";
if(posix_getuid()<>0){parseTemplate();die();}

include_once(dirname(__FILE__)."/ressources/class.user.inc");
include_once(dirname(__FILE__)."/ressources/class.groups.inc");
include_once(dirname(__FILE__)."/ressources/class.ldap.inc");
include_once(dirname(__FILE__)."/ressources/class.system.network.inc");
include_once(dirname(__FILE__)."/ressources/class.dansguardian.inc");
include_once(dirname(__FILE__)."/ressources/class.squid.inc");
include_once(dirname(__FILE__)."/ressources/class.squidguard.inc");
include_once(dirname(__FILE__)."/ressources/class.mysql.inc");
include_once(dirname(__FILE__)."/ressources/class.compile.ufdbguard.inc");
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");




if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
if(count($argv)>0){
	$imploded=implode(" ",$argv);
	if(preg_match("#--verbose#",$imploded)){$GLOBALS["VERBOSE"]=true;$GLOBALS["debug"]=true;ini_set_verbosed(); }
	if(preg_match("#--reload#",$imploded)){$GLOBALS["RELOAD"]=true;}
	if(preg_match("#--shalla#",$imploded)){$GLOBALS["SHALLA"]=true;}
	if(preg_match("#--catto=(.+?)\s+#",$imploded,$re)){$GLOBALS["CATTO"]=$re[1];}
	
	$argvs=$argv;
	unset($argvs[0]);
	if($argv[1]=="--databases-status"){databases_status();exit;}
	if($argv[1]=="--ufdbguard-status"){print_r(UFDBGUARD_STATUS());exit;}
	if($argv[1]=="--cron-compile"){cron_compile();exit;}
	if($argv[1]=="--compile-category"){UFDBGUARD_COMPILE_CATEGORY($argv[2]);exit;}
	if($argv[1]=="--compile-all-categories"){UFDBGUARD_COMPILE_ALL_CATEGORIES();exit;}
	if($argv[1]=="--ufdbguard-recompile-dbs"){echo UFDBGUARD_COMPILE_ALL_CATEGORIES();exit;}
	
	
	ufdbguard_admin_events("receive ".@implode(" ", $argvs),"MAIN",__FILE__,__LINE__,"config");
	
	if($GLOBALS["VERBOSE"]){echo "Execute ".@implode(" ", $argv)."\n";}
	
	if($argv[1]=="--inject"){echo inject($argv[2],$argv[3]);exit;}
	if($argv[1]=="--conf"){echo conf();exit;}
	if($argv[1]=="--ufdbguard-compile"){echo UFDBGUARD_COMPILE_SINGLE_DB($argv[2]);exit;}	
	if($argv[1]=="--ufdbguard-dbs"){echo UFDBGUARD_COMPILE_DB();exit;}
	if($argv[1]=="--ufdbguard-miss-dbs"){echo ufdbguard_recompile_missing_dbs();exit;}
	
	if($argv[1]=="--ufdbguard-schedule"){ufdbguard_schedule();exit;}
	if($argv[1]=="--list-missdbs"){BuildMissingUfdBguardDBS(false,true);exit;}				
	
	
	
	if($argv[1]=="--parsedir"){ParseDirectory($argv[2]);exit;}
	
	
	
	
}
	


$unix=new unix();
$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".MAIN.pid";
$pid=@file_get_contents($pidfile);
if($unix->process_exists($pid,basename(__FILE__))){
	writelogs(basename(__FILE__).":Already executed.. aborting the process",basename(__FILE__),__FILE__,__LINE__);
	die();
}
@file_put_contents($pidfile, getmypid());


if($argv[1]=="--categories"){build_categories();exit;}
if($argv[2]=="--reload"){$GLOBALS["RELOAD"]=true;}
if($argv[1]=="--build"){build();exit;}
if($argv[1]=="--status"){echo status();exit;}
if($argv[1]=="--compile"){echo compile_databases();exit;}
if($argv[1]=="--db-status"){print_r(databasesStatus());exit;}
if($argv[1]=="--db-status-www"){echo serialize(databasesStatus());exit;}

if($argv[1]=="--compile-single"){echo CompileSingleDB($argv[2]);exit;}
if($argv[1]=="--conf"){echo conf();exit;}



//http://cri.univ-tlse1.fr/documentations/cache/squidguard.html


function build_categories(){
$q=new mysql_squid_builder();

$sql="SELECT LOWER(pattern) FROM category_porn WHERE enabled=1 AND pattern REGEXP '[a-zA-Z0-9\_\-]+\.[a-zA-Z0-9\_\-]+' INTO OUTFILE 'porn.txt' FIELDS OPTIONALLY ENCLOSED BY 'n'";
$q->QUERY_SQL($sql);	
if(!$q->ok){echo $q->mysql_error."\n";}
	
	
}

function conf(){
	$users=new usersMenus();
	$ufdb=new compile_ufdbguard();
	$datas=$ufdb->buildConfig();
	if($GLOBALS["VERBOSE"]){echo $datas;}
	@file_put_contents("/etc/squid/squidGuard.conf",$datas);
	if(!is_file("/var/log/ufdbguard/ufdbguardd.log")){
		@mkdir("/var/log/ufdbguard",755,true);
		@file_put_contents("/var/log/ufdbguard/ufdbguardd.log", "see /var/log/squid/ufdbguardd.log\n");
		shell_exec("chmod 777 /var/log/ufdbguard/ufdbguardd.log");
	}
	
	
	if(is_file("/usr/sbin/ufdbguardd")){
		if(!is_file("/usr/bin/ufdbguardd")){
			$unix=new unix();
			$ln=$unix->find_program("ln");
			shell_exec("$ln -s /usr/sbin/ufdbguardd /usr/bin/ufdbguardd");
		}
	}
	@mkdir("/etc/ufdbguard",755,true);
	@file_put_contents("/etc/ufdbguard/ufdbGuard.conf",$datas);
	if($users->APP_UFDBGUARD_INSTALLED){
		shell_exec("/etc/init.d/ufdb reconfig");
		ufdbguard_admin_events("Service was sucessfully rebuiled and restarted",__FUNCTION__,__FILE__,__LINE__,"config");
	}
	
	
}

function build(){
	
	$users=new usersMenus();
	$sock=new sockets();
	$unix=new unix();
	$chown=$unix->find_program("chown");
	$chmod=$unix->find_program("chmod");
	$squidbin=$unix->find_program("squid3");
	if(!is_file($squidbin)){$squidbin=$unix->find_program("squid");}
	$GLOBALS["SQUIDBIN"]=$squidbin;	
	
	$installed=false;
	if($users->SQUIDGUARD_INSTALLED){$installed=true;}
	if($users->APP_UFDBGUARD_INSTALLED){$installed=true;}
	if(!$installed){return false;}
	

	$ufdb=new compile_ufdbguard();
	$datas=$ufdb->buildConfig();
	@mkdir("/etc/ufdbguard",null,true);
	@file_put_contents("/etc/ufdbguard/ufdbGuard.conf",$datas);
	if(is_file("/usr/sbin/ufdbguardd")){if(!is_file("/usr/bin/ufdbguardd")){$unix=new unix();$ln=$unix->find_program("ln");shell_exec("$ln -s /usr/sbin/ufdbguardd /usr/bin/ufdbguardd");}}
	if($users->APP_UFDBGUARD_INSTALLED){ufdbguard_schedule();}

	
	
	$user=GetSquidUser();
	if(!is_file("/squid/log/squid/squidGuard.log")){
		@mkdir("/squid/log/squid",755,true);
		@file_put_contents("/squid/log/squid/squidGuard.log","#");
		shell_exec("$chown $user /squid/log/squid/squidGuard.log");
	}
	shell_exec("$chown -R $user /var/lib/squidguard/*");
	shell_exec("$chown -R $user /var/log/squid/*");
	shell_exec("$chmod -R 755 /var/lib/squidguard/*");
	shell_exec("$chmod -R ug+x /var/lib/squidguard/*");
	
	if(!is_file("/var/log/ufdbguard/ufdbguardd.log")){@mkdir("/var/log/ufdbguard",755,true);@file_put_contents("/var/log/ufdbguard/ufdbguardd.log", "see /var/log/squid/ufdbguardd.log\n");}
	shell_exec("chmod 777 /var/log/ufdbguard/ufdbguardd.log");	
	ufdbguard_admin_events("Service will be rebuiled and restarted",__FUNCTION__,__FILE__,__LINE__,"config");
	if(is_file("/etc/init.d/ufdb")){shell_exec("/etc/init.d/ufdb reconfig >/dev/null 2>&1");}
	shell_exec("{$GLOBALS["SQUIDBIN"]} -k reconfigure");
	send_email_events("SquidGuard/ufdbGuard rules was rebuilded","This is new configuration file of the squidGuard/ufdbGuard:\n-------------------------------------\n$datas","system");
	shell_exec(LOCATE_PHP5_BIN2()." ".dirname(__FILE__)."/exec.c-icap.php --maint-schedule");
	
	}
	

	
function FileMD5($path){
if(strlen(trim($GLOBALS["md5sum"]))==0){
		$unix=new unix();
		$md5sum=$unix->find_program("md5sum");
		$GLOBALS["md5sum"]=$md5sum;
}

if(strlen(trim($GLOBALS["md5sum"]))==0){return md5(@file_get_contents($path));}


exec("{$GLOBALS["md5sum"]} $path 2>&1",$res);
$data=trim(@implode(" ",$res));
if(preg_match("#^(.+?)\s+.+?#",$data,$re)){return trim($re[1]);}
	
}

function UFDBGUARD_COMPILE_SINGLE_DB($path){
	
	$unix=new unix();
	$path=str_replace(".ufdb","",$path);
	$pidpath="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.md5($path).".pid";
	$oldpid=@file_get_contents($pidpath);
	if($unix->process_exists($oldpid)){
		events_ufdb_tail("Check \"$path\"... Already process PID \"$oldpid\" running task has been aborted");
		return;
	}
	
	
	
	$category=null;
	$ufdbGenTable=$unix->find_program("ufdbGenTable");
	events_ufdb_tail("Check \"$path\"...",__LINE__);
	if(preg_match("#\/var\/lib\/squidguard\/(.+?)\/(.+?)/(.+?)$#",$path,$re)){
		$category=$re[2];
		$domain_path="/var/lib/squidguard/{$re[1]}/{$re[2]}/domains";		
	}
	if($category==null){
		if(preg_match("#\/var\/lib\/squidguard\/(.+?)\/domains#",$path,$re)){
			$category=$re[1];
			$domain_path="/var/lib/squidguard/{$re[1]}/domains";		
		}	
	}
	
	if(preg_match("#web-filter-plus\/BL\/(.+?)\/domains#",$path,$re)){
		$category=$re[1];
		$domain_path="/var/lib/squidguard/web-filter-plus/BL/$category/domains";	
	}
	
	if(preg_match("#blacklist-artica\/(.+?)\/(.+?)\/domains#",$path,$re)){
		events_ufdb_tail("find double category \"{$re[1]}-{$re[2]}\"...",__LINE__);
		$category="{$re[1]}-{$re[2]}";
		$domain_path="/var/lib/squidguard/blacklist-artica/{$re[1]}/{$re[2]}/domains";	
	}	

	if(preg_match("#blacklist-artica\/sex\/(.+?)\/domains#",$path,$re)){
		$category=$re[1];
		$domain_path="/var/lib/squidguard/blacklist-artica/sex/$category/domains";	
	}
	
	if($category==null){
		events_ufdb_tail("exec.squidguard.php:: \"$path\" cannot understand...");
	}
	
	events_ufdb_tail("exec.squidguard.php:: Found category \"$category\"",__LINE__);

	if(!is_file($path)){
		events_ufdb_tail("exec.squidguard.php:$category: \"$path\" no such file, build it",__LINE__);
		@file_put_contents($domain_path," ");
	}
	
	$category_compile=substr($category,0,15);
	if(strlen($category_compile)>15){
			$category_compile=str_replace("recreation_","recre_",$category_compile);
			$category_compile=str_replace("automobile_","auto_",$category_compile);
			$category_compile=str_replace("finance_","fin_",$category_compile);
			if(strlen($category_compile)>15){
				$category_compile=str_replace("_", "", $category_compile);
				if(strlen($category_compile)>15){
					$category_compile=substr($category_compile, strlen($category_compile)-15,15);
				}
			}
		}	
	
	events_ufdb_tail("exec.squidguard.php:: category \"$category\" retranslated to \"$category_compile\"",__LINE__);
	
	
	if(is_file("$domain_path.ufdb")){
		events_ufdb_tail("exec.squidguard.php:: removing \"$domain_path.ufdb\" ...");
		@unlink("$domain_path.ufdb");
	
	}
	if(!is_file($domain_path)){
		events_ufdb_tail("exec.squidguard.php:: $domain_path no such file, create an empty one",__LINE__);
		@mkdir(dirname($domain_path),755,true);
		@file_put_contents($domain_path,"#");
	}
	
	
	$d=" -d $domain_path";
	$cmd="$ufdbGenTable -n -D -W -t $category_compile$d 2>&1";
	events_ufdb_tail("exec.squidguard.php:$category:$cmd");
	$time=time();
	exec($cmd,$results);
	exec($cmd,$results);
	while (list ($a, $b) = each ($results)){
		if(strpos($b,"is not added because it was already matched")){continue;}
		if(strpos($b,"has optimised subdomains")){continue;}
		events_ufdb_tail("exec.squidguard.php:$category:$b");
	}
	events_ufdb_tail("exec.squidguard.php:$category_compile: execution :". $unix->distanceOfTimeInWords($time,time()),__LINE__);
	
	events_ufdb_tail("exec.squidguard.php:$category:done..");
	
	$user=GetSquidUser();
	$chown=$unix->find_program("chown");
	if(is_file($chown)){
		events_ufdb_tail("exec.squidguard.php:$category:$chown -R $user /var/lib/squidguard");
		shell_exec("$chown -R $user /var/lib/squidguard/*");
		shell_exec("$chown -R $user /var/log/squid/*");
	}		
	PersonalCategoriesRepair();
	events_ufdb_tail("/etc/init.d/ufdb reconfig");
	ufdbguard_admin_events("Service was sucessfully rebuiled and restarted",__FUNCTION__,__FILE__,__LINE__,"config");
	shell_exec("/etc/init.d/ufdb reconfig");
	
}
	

function databasesStatus(){
	$datas=explode("\n",@file_get_contents("/etc/squid/squidGuard.conf"));
	$count=0;
	$f=array();
	while (list ($a, $b) = each ($datas)){
		
		if(preg_match("#domainlist.+?(.+)#",$b,$re)){
			$f[]["domainlist"]["path"]="/var/lib/squidguard/{$re[1]}";
			
			continue;
			
		}
		
		if(preg_match("#expressionlist.+?(.+)#",$b,$re)){
			$f[]["expressionlist"]["path"]="/var/lib/squidguard/{$re[1]}";
			
			continue;
		}
		
		if(preg_match("#urllist.+?(.+)#",$b,$re)){
			$f[]["urllist"]["path"]="/var/lib/squidguard/{$re[1]}";
			
			continue;
		}
		
		
	}
	

	
	while (list ($a, $b) = each ($f)){

		$domainlist=$b["domainlist"]["path"];
		$expressionlist=$b["expressionlist"]["path"];
		$urllist=$b["urllist"]["path"];
		
		if(is_file($domainlist)){
			$key="domainlist";
			$path=$domainlist;
		}
		
		if(is_file($expressionlist)){
			$key="expressionlist";
			$path=$expressionlist;
		}

		if(is_file($urllist)){
			$key="urllist";
			$path=$urllist;
		}			
		
		$d=explode("\n",@file_get_contents($path));
		$i[$path]["type"]=$key;
		$i[$path]["size"]=@filesize("$domainlist.db");
		$i[$path]["linesn"]=count($d);
		$i[$path]["date"]=filemtime($path);
		
		
		
		
	}
	
	return $i;
	
}

function status(){
	
	
	$squid=new squidbee();
	$array=$squid->SquidGuardDatabasesStatus();
	$conf[]="[APP_SQUIDGUARD]";
	$conf[]="service_name=APP_SQUIDGUARD";
	
	
	if(is_array($array)){
		$conf[]="running=0";
		$conf[]="why={waiting_database_compilation}<br>{databases}:&nbsp;".count($array);
		return implode("\n",$conf);
		
	}
	
	
	$unix=new unix();
	$users=new usersMenus();
	$pidof=$unix->find_program("pidof");
	exec("$pidof $users->SQUIDGUARD_BIN_PATH",$res);
	$array=explode(" ",implode(" ",$res));
	while (list ($index, $line) = each ($array)){
		if(preg_match("#([0-9]+)#",$line,$ri)){
			$pid=$ri[1];
			$inistance=$inistance+1;
			$mem=$mem+$unix->MEMORY_OF($pid);
			$ppid=$unix->PPID_OF($pid);
		}
	}
	$conf[]="running=1";
	$conf[]="master_memory=$mem";
	$conf[]="master_pid=$ppid";
	$conf[]="other={processes}:$inistance"; 
	return implode("\n",$conf);
	
}

function CompileSingleDB($db_path){
	$user=GetSquidUser();
	$users=new usersMenus();
	$unix=new unix();
	if(strpos($db_path,".db")>0){$db_path=str_replace(".db","",$db_path);}
	$verb=" -d";
	$chown=$unix->find_program("chown");
	$chmod=$unix->find_program("chmod");
	exec($users->SQUIDGUARD_BIN_PATH." $verb -C $db_path",$repair);	
	shell_exec("$chown -R $user /var/lib/squidguard/*");
	shell_exec("$chmod -R 755 /var/lib/squidguard/*");	
	shell_exec("$chmod -R ug+x /var/lib/squidguard/*");	
	
	$db_recover=$unix->LOCATE_DB_RECOVER();
	shell_exec("$db_recover -h ".dirname($db_path));
	build();
	KillSquidGuardInstances();	
	send_email_events("squidGuard: $db_path repair","the database $db_path was repair by artica\n",@implode("\n",$repair),"squid");
	
}

function KillSquidGuardInstances(){
	$unix=new unix();
	$users=new usersMenus();
	$pidof=$unix->find_program("pidof");
	if(strlen($pidof)>3){
		exec("$pidof $users->SQUIDGUARD_BIN_PATH 2>&1",$results);
		$pids=trim(@implode(" ",$results));
		if(strlen($pids)>3){
			echo "Starting......: squidGuard kill $pids PIDs\n";
			shell_exec("/bin/kill $pids");
		}
		
	}	
	
}


function compile_databases(){
	$users=new usersMenus();
	$squid=new squidbee();
	$array=$squid->SquidGuardDatabasesStatus();
	$verb=" -d";
	
	
		$array=$squid->SquidGuardDatabasesStatus(0);

	
	if( count($array)>0){
		while (list ($index, $file) = each ($array)){
			echo "Starting......: squidGuard compiling ". count($array)." databases\n";
			$file=str_replace(".db",'',$file);
			$textfile=str_replace("/var/lib/squidguard/","",$file);
			echo "Starting......: squidGuard compiling $textfile database ".($index+1) ."/". count($array)."\n";
			if($GLOBALS["VERBOSE"]){$verb=" -d";echo $users->SQUIDGUARD_BIN_PATH." $verb -C $file\n";}
			system($users->SQUIDGUARD_BIN_PATH." -P$verb -C $file");
		}
	}else{
		echo "Starting......: squidGuard compiling all databases\n";
		if($GLOBALS["VERBOSE"]){$verb=" -d";echo $users->SQUIDGUARD_BIN_PATH." $verb -C all\n";}
		system($users->SQUIDGUARD_BIN_PATH." -P$verb -C all");
	}

	
		
	$user=GetSquidUser();
	$unix=new unix();
	$chown=$unix->find_program("chown");
	$chmod=$unix->find_program("chmod");
	shell_exec("$chown -R $user /var/lib/squidguard/*");
	shell_exec("$chmod -R 755 /var/lib/squidguard/*");		
 	system(LOCATE_PHP5_BIN2()." ".dirname(__FILE__)."/exec.squid.php --build");
	build();
	KillSquidGuardInstances();
	
	
	
 
 
}

function parseTemplate(){
	include_once(dirname(__FILE__)."/ressources/class.sockets.inc");
	$sock=new sockets();
	$template=$sock->GET_INFO("DansGuardianHTMLTemplate");
	$EnableSquidFilterWhiteListing=$sock->GET_INFO("EnableSquidFilterWhiteListing");
	if(strlen($template)<50){$template=$sock->getFrameWork("cmd.php?dansguardian-get-template=yes");}
	if(preg_match("#<body>(.+?)</body>#is",$template,$re)){$template=$re[1];}
	//url=http://www.eicar.org/download/eicarcom2.zip&source=192.168.1.212/-&user=-&virus=stream:+Eicar-Test-Signature+FOUND
	
	if(isset($_GET["source"])){$_GET["clientaddr"]=$_GET["source"];}
	if(isset($_GET["user"])){$_GET["clientname"]=$_GET["user"];}
	if(isset($_GET["virus"])){$_GET["targetgroup"]=$_GET["virus"];}
	
	$template=str_replace("-USER-",$_GET["clientname"],$template);
	$template=str_replace("-URL-",$_GET["url"],$template);
	$template=str_replace("-IP-",$_GET["clientaddr"],$template);
	$template=str_replace("-REASONGIVEN-",$_GET["targetgroup"],$template);
	$template=str_replace("-REASONLOGGED-",$_GET["clientgroup"],$template);
	if($EnableSquidFilterWhiteListing==1){
		$DansGuardianWhiteListIntro=$sock->GET_INFO("DansGuardianWhiteListIntro");	
		if(strlen($DansGuardianWhiteListIntro)<2){$DansGuardianWhiteListIntro="<strong style=\"font-size:14px\">Unlock this Website</strong><hr><br><i style=\"font-size:14px\">Access to this site is restricted because it is not classified in any category selected by our company policy.<br>If you think that this website is safe and help your work for company objectives, you are free to save this website into categories listed bellow.</i><hr>";}
	}
	
	

	echo "
	<html>
	<head>
	<title>{$_GET["clientname"]}::{$_GET["clientaddr"]}::{$_GET["targetgroup"]}</title>
	</head>
	<body>
	<center>
	$template
	<p>&nbsp;</p>
	$DansGuardianWhiteListIntro
	</center>
	</body>
	</html>
	";
	
	
	
}

function GetSquidUser(){
	$unix=new unix();
	$squidconf=$unix->SQUID_CONFIG_PATH();
	if(!is_file($squidconf)){
		echo "Starting......: squidGuard unable to get squid configuration file\n";
		return "squid:squid";
	}
	
	$array=explode("\n",@file_get_contents($squidconf));
	while (list ($index, $line) = each ($array)){
		if(preg_match("#cache_effective_user\s+(.+)#",$line,$re)){
			$user=trim($re[1]);
			$user=trim($re[1]);
		}
		if(preg_match("#cache_effective_group\s+(.+)#",$line,$re)){
			$group=trim($re[1]);
		}
	}
	
return "$user:$group";
	
	
	
}

function ParseDirectory($path){
	if(!is_dir($path)){echo "$path No such directory\n";return;}
	$sock=new sockets();
	$uuid=base64_decode($sock->getFrameWork("cmd.php?system-unique-id=yes"));
	if($uuid==null){echo "No uuid\n";return;}	
	$handle=opendir($path);
	$q=new mysql_squid_builder();
	$f=false;
	while (false !== ($dir = readdir($handle))) {
		if($dir=="."){continue;}
		if($dir==".."){continue;}	
		if(!is_file("$path/$dir/domains")){echo "$path/$dir/domains no such file\n";continue;}
		$category=sourceCategoryToArticaCategory($dir);
		if($category==null){echo "$path/$dir/domains no such category\n";continue;}
		$table="category_".$q->category_transform_name($category);
		if(!$q->TABLE_EXISTS($table)){echo "$category -> no such table $table\n";continue;}
		inject($category,$table,"$path/$dir/domains");
		
		
	}
	
	
	$tables=$q->LIST_TABLES_CATEGORIES();
	while (list ($table, $www) = each ($tables)){
		$sql="SELECT COUNT(zmd5) as tcount FROM $table WHERE sended=0 and enabled=1";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql));
		$prefix="INSERT IGNORE INTO categorize (zmd5 ,pattern,zDate,uuid,category) VALUES";
		if($ligne["tcount"]>0){
			echo "$table {$ligne["tcount"]} items to export\n";
			$results=$q->QUERY_SQL("SELECT * FROM $table WHERE sended=0 and enabled=1");
			while($ligne2=mysql_fetch_array($results,MYSQL_ASSOC)){
				$f[]="('{$ligne2["zmd5"]}','{$ligne2["pattern"]}','{$ligne2["zDate"]}','$uuid','{$ligne2["category"]}')";
				$c++;
				if(count($f)>3000){
					$q->QUERY_SQL($prefix.@implode(",",$f));
					if(!$q->ok){echo $q->mysql_error."\n";return;}
					$f=array();
				}
				
			}
		$q->QUERY_SQL("UPDATE $table SET sended=1 WHERE sended=0");
		}
		
	}
	
if(count($f)>0){
	$q->QUERY_SQL($prefix.@implode(",",$f));
	$f=array();	
}	
	
	
	
}

function sourceCategoryToArticaCategory($category){
	$array["gambling"]="gamble";
	$array["gamble"]="gamble";
	$array["hacking"]="hacking";
	$array["malware"]="malware";
	$array["phishing"]="phishing";
	$array["porn"]="porn";
	$array["sect"]="sect";
	$array["socialnetwork"]="socialnet";
	$array["violence"]="violence";
	$array["adult"]="porn";
	$array["ads"]="publicite";
	$array["warez"]="warez";
	$array["drugs"]="drogue";
	$array["forums"]="forums";
	$array["filehosting"]="filehosting";
	$array["games"]="games";
	$array["astrology"]="astrology";
	$array["publicite"]="publicite";
	$array["radio"]="webradio";
	$array["sports"]="recreation/sports";
	$array["press"]="press";
	$array["audio-video"]="audio-video";
	$array["webmail"]="webmail";
	$array["chat"]="chat";
	$array["social_networks"]="socialnet";
	$array["ads"]="publicite";
	$array["adult"]="porn";
	$array["aggressive"]="aggressive";
	$array["astrology"]="astrology";
	$array["audio-video"]="audio-video";
	$array["bank"]="finance/banking";
	$array["blog"]="blog";
	$array["celebrity"]="celebrity";
	$array["chat"]="chat";
	$array["cleaning"]="cleaning";
	$array["dangerous_material"]="dangerous_material";
	$array["dating"]="dating";
	$array["drugs"]="porn";
	$array["filehosting"]="filehosting";
	$array["financial"]="financial";
	$array["forums"]="forums";
	$array["gambling"]="gamble";
	$array["games"]="games";
	$array["hacking"]="hacking";
	$array["jobsearch"]="jobsearch";
	$array["liste_bu"]="liste_bu";
	$array["malware"]="malware";
	$array["marketingware"]="marketingware";
	$array["mixed_adult"]="mixed_adult";
	$array["mobile-phone"]="mobile-phone";
	$array["phishing"]="phishing";
	$array["press"]="press";
	$array["radio"]="webradio";
	$array["reaffected"]="reaffected";
	$array["redirector"]="redirector";
	$array["remote-control"]="remote-control";
	$array["sect"]="sect";
	$array["sexual_education"]="sexual_education";
	$array["shopping"]="shopping";
	$array["social_networks"]="socialnet";
	$array["sports"]="recreation/sports";
	$array["strict_redirector"]="strict_redirector";
	$array["strong_redirector"]="strong_redirector";
	$array["tricheur"]="tricheur";
	$array["violence"]="violence";
	$array["warez"]="warez";
	$array["webmail"]="webmail";
	$array["ads"]="publicite";
	$array["adult"]="porn";
	$array["aggressive"]="aggressive";
	$array["astrology"]="astrology";
	$array["audio-video"]="audio-video";
	$array["bank"]="finance/banking";
	$array["blog"]="blog";
	$array["celebrity"]="celebrity";
	$array["chat"]="chat";
	$array["cleaning"]="cleaning";
	$array["dangerous_material"]="dangerous_material";
	$array["dating"]="dating";
	$array["drugs"]="porn";
	$array["filehosting"]="filehosting";
	$array["financial"]="financial";
	$array["forums"]="forums";
	$array["gambling"]="gamble";
	$array["games"]="games";
	$array["hacking"]="hacking";
	$array["jobsearch"]="jobsearch";
	$array["liste_bu"]="liste_bu";
	$array["malware"]="malware";
	$array["marketingware"]="marketingware";
	$array["mixed_adult"]="mixed_adult";
	$array["mobile-phone"]="mobile-phone";
	$array["phishing"]="phishing";
	$array["press"]="press";
	$array["radio"]="webradio";
	$array["reaffected"]="reaffected";
	$array["redirector"]="redirector";
	$array["remote-control"]="remote-control";
	$array["sect"]="sect";
	$array["sexual_education"]="sexual_education";
	$array["shopping"]="shopping";
	$array["social_networks"]="socialnet";
	$array["sports"]="recreation/sports";
	$array["strict_redirector"]="strict_redirector";
	$array["strong_redirector"]="strong_redirector";
	$array["tricheur"]="tricheur";
	$array["violence"]="violence";
	$array["warez"]="warez";
	$array["webmail"]="webmail";	
	$array["adv"]="publicite";
	$array["aggressive"]="aggressive";
	$array["automobile"]="automobile/cars";
	$array["chat"]="chat";
	$array["dating"]="dating";
	$array["downloads"]="downloads";
	$array["drugs"]="drugs";
	$array["education"]="recreation/schools";
	$array["finance"]="financial";
	$array["forum"]="forums";
	$array["gamble"]="gamble";
	$array["government"]="governments";
	$array["hacking"]="hacking";
	$array["hospitals"]="hospitals";
	$array["imagehosting"]="imagehosting";
	$array["isp"]="isp";
	$array["jobsearch"]="jobsearch";
	$array["library"]="books";
	$array["models"]="models";
	$array["movies"]="movies";
	$array["music"]="music";
	$array["news"]="news";
	$array["porn"]="porn";
	$array["redirector"]="redirector";
	$array["religion"]="religion";
	$array["remotecontrol"]="remote-control";
	$array["ringtones"]="ringtones";
	$array["searchengines"]="searchengines";
	$array["shopping"]="shopping";
	$array["socialnet"]="socialnet";
	$array["spyware"]="spyware";
	$array["tracker"]="tracker";
	$array["updatesites"]="updatesites";
	$array["violence"]="violence";
	$array["warez"]="warez";
	$array["weapons"]="weapons";
	$array["webmail"]="webmail";
	$array["webphone"]="webphone";
	$array["webradio"]="webradio";
	$array["webtv"]="webtv";		
	if(!isset($array[$category])){return null;}
	return $array[$category];
	
	
}

function inject($category,$table=null,$file=null){
	if($table==null){echo "Table is null\n";}
	$q=new mysql_squid_builder();
	if(!$q->TABLE_EXISTS($table)){echo "$category -> no such table $table\n";return;}
		
		
	if($file==null){
		$dir="/var/lib/squidguard";
		if($GLOBALS["SHALLA"]){$dir="/root/shalla/BL";}
		if(!is_file("$dir/$category/domains")){
			echo "$dir/$category/domains no such file";
			return;
			
		}
		$file="$dir/$category/domains";
	}
		
	if(!is_file("$file")){echo "$file no such file";return;}
		
	$sock=new sockets();
	$uuid=base64_decode($sock->getFrameWork("cmd.php?system-unique-id=yes"));
	if($uuid==null){echo "No uuid\n";return;}
	echo "open $file\n";
	$f=explode("\n",@file_get_contents("$file"));
	krsort($f);
	$q=new mysql_squid_builder();
	if($GLOBALS["CATTO"]<>null){$category=$GLOBALS["CATTO"];}
	
	$prefix="INSERT IGNORE INTO $table (zmd5,zDate,category,pattern,uuid) VALUES ";
	$c=0;
	while (list ($index, $www) = each ($f)){
		if($www==null){continue;}
		if(preg_match("#^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$#", $www)){continue;}
		$www=trim(strtolower($www));
		if($www=="thisisarandomentrythatdoesnotexist.com"){continue;}
		if($www==null){continue;}
		$md5=md5($www.$category);
		$n[]="('$md5',NOW(),'$category','$www','$uuid')";
		$c++;
		
		if(count($n)>3000){
			$sql=$prefix.@implode(",",$n);
			$q->QUERY_SQL($sql,"artica_backup");
			if(!$q->ok){echo $q->mysql_error."\n";die();}
			echo "$c items\n";
			$n=array();
			
		}
		
	}
	
		if(count($f)>0){
			if($c>0){
				echo "$c items line:". __LINE__."\n";
				$sql=$prefix.@implode(",",$n);
				$q->QUERY_SQL($sql,"artica_backup");
				if(!$q->ok){echo $q->mysql_error."\n$sql";continue;}
				$n=array();
			}
		}	
		
	@unlink($file);
	
	
}

function UFDBGUARD_COMPILE_DB(){
	
	
	$unix=new unix();
	$pidfile="/etc/artica-postfix/pids/UFDBGUARD_COMPILE_DB.pid";
	if($unix->process_exists(@file_get_contents($pidfile))){
		echo "Process already exists PID: ".@file_get_contents($pidfile)."\n";
		return;
	}
	
	
	@file_put_contents($pidfile,getmypid());
	$ufdbGenTable=$unix->find_program("ufdbGenTable");
	$datas=explode("\n",@file_get_contents("/etc/squid/squidGuard.conf"));
	if(strlen($ufdbGenTable)<5){echo "ufdbGenTable no such file\n";return ;}
	
	$md5db=unserialize(@file_get_contents("/etc/artica-postfix/ufdbGenTableMD5"));
	
	
	$count=0;
	while (list ($a, $b) = each ($datas)){
		if(preg_match("#domainlist.+?(.+)\/domains#",$b,$re)){
			$f["/var/lib/squidguard/{$re[1]}"]="/var/lib/squidguard/{$re[1]}";
		}
	}
	
	
	
	if(!is_array($datas)){echo "No databases set\n";return ;}
	while (list ($directory, $b) = each ($f)){
		$mustrun=false;
		if(preg_match("#.+?\/([a-zA-Z0-9\-\_]+)$#",$directory,$re)){
			$category=$re[1];
			$category=substr($category,0,15);
			if($GLOBALS["VERBOSE"]){echo "Checking $category\n";}
		}
		
		// ufdbGenTable -n -D -W -t adult -d /var/lib/squidguard/adult/domains -u /var/lib/squidguard/adult/urls     
		if(is_file("$directory/domains")){
			$md5=FileMD5("$directory/domains");
			if($md5<>$md5db["$directory/domains"]){
				$mustrun=true;
				$md5db["$directory/domains"]=$md5;
				$dbb[]="$directory/domains";
			}else{
				if($GLOBALS["VERBOSE"]){echo "$md5 is the same, skip $directory/domains\n";}
			}
			
			
			$d=" -d $directory/domains";
		}else{
			if($GLOBALS["VERBOSE"]){echo "$directory/domains no such file\n";}
		}
		if(is_file("$directory/urls")){
			$md5=FileMD5("$directory/urls");
			if($md5<>$md5db["$directory/urls"]){$mustrun=true;$md5db["$directory/urls"]=$md5;$dbb[]="$directory/urls";}
			$u=" -u $directory/urls";
		}
		
		if(!is_file("$directory/domains.ufdb")){$mustrun=true;$dbb[]="$directory/*";}
		
		if($mustrun){
			$dbcount=$dbcount+1;
			$category_compile=$category;
			if(strlen($category_compile)>15){
			$category_compile=str_replace("recreation_","recre_",$category_compile);
			$category_compile=str_replace("automobile_","auto_",$category_compile);
			$category_compile=str_replace("finance_","fin_",$category_compile);
			if(strlen($category_compile)>15){
				$category_compile=str_replace("_", "", $category_compile);
				if(strlen($category_compile)>15){
					$category_compile=substr($category_compile, strlen($category_compile)-15,15);
				}
			}
		}			
			
			
			$cmd="$ufdbGenTable -n -D -W -t $category_compile$d$u";
			echo $cmd."\n";
			shell_exec($cmd);
		}
		$u=null;$d=null;$md5=null;
	}
	
	@file_put_contents("/etc/artica-postfix/ufdbGenTableMD5",serialize($md5db));
	$user=GetSquidUser();
	$chown=$unix->find_program($chown);
	if(is_file($chown)){
		shell_exec("$chown -R $user /var/lib/squidguard/*");
		shell_exec("$chown -R $user /var/log/squid/*");
	}	
	if($dbcount>0){
		send_email_events("Maintenance on Web Proxy urls Databases: $dbcount database(s)",@implode("\n",$dbb)."\n","system");
	}
	
}

function BuildMissingUfdBguardDBS($all=false,$output=false){
	$Narray=array();
	$array=explode("\n",@file_get_contents("/etc/ufdbguard/ufdbGuard.conf"));
	while (list ($index, $line) = each ($array) ){
		if(preg_match("#domainlist.+?(.+)\/domains#",$line,$re)){
			$datas_path="/var/lib/squidguard/{$re[1]}/domains";
			$path="/var/lib/squidguard/{$re[1]}/domains.ufdb";
			
			if(!$all){
				if(!is_file($path)){
					if($output){echo "Missing $path\n";} 
					$Narray[$path]=@filesize($datas_path);
				}
			}
			if($all){$Narray[$path]=@filesize($datas_path);}
			
		}
		
	}
	
	echo "Starting......: ufdbGuard ". count($Narray)." database(s) must be compiled\n";
	if(!$all){
		@file_put_contents("/usr/share/artica-postfix/ressources/logs/ufdbguard.db.status.txt",serialize($Narray));
		chmod("/usr/share/artica-postfix/ressources/logs/ufdbguard.db.status.txt",777);
	}
	return $Narray;
}

function UFDBGUARD_STATUS(){
	$Narray=array();
	$unix=new unix();
	$array=explode("\n",@file_get_contents("/etc/ufdbguard/ufdbGuard.conf"));
	while (list ($index, $line) = each ($array) ){
		if(preg_match("#domainlist.+?(.+)\/domains#",$line,$re)){
			$datas_path="/var/lib/squidguard/{$re[1]}/domains";
			$path="/var/lib/squidguard/{$re[1]}/domains.ufdb";
			$size=$unix->file_size($path);
			$Narray[$path]=$size;
			
		}
		
	}
	
	@file_put_contents("/usr/share/artica-postfix/ressources/logs/ufdbguard.db.size.txt",serialize($Narray));
	chmod("/usr/share/artica-postfix/ressources/logs/ufdbguard.db.size.txt",777);
	
	return $Narray;
}


function databases_status(){
	if($GLOBALS["VERBOSE"]){echo "databases_status() line:".__LINE__."\n";}
	$unix=new unix();
	$chmod=$unix->find_program("chmod");
	@mkdir("/var/lib/squidguard",755,true);
	$q=new mysql_squid_builder();
	$sql="SELECT table_name as c FROM information_schema.tables WHERE table_schema = 'squidlogs' AND table_name LIKE 'category_%'";
	$results=$q->QUERY_SQL($sql);
	if($GLOBALS["VERBOSE"]){echo $sql."\n";}	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$table=$ligne["c"];
		if(!preg_match("#^category_(.+)#", $table,$re)){continue;}
		$categoryname=$re[1];
		if($GLOBALS["VERBOSE"]){echo "Checks $categoryname\n";}
		if(is_file("/var/lib/squidguard/$categoryname/domains.ufdb")){
			if($GLOBALS["VERBOSE"]){echo "Checks $categoryname/domains.ufdb\n";}
			$size=@filesize("/var/lib/squidguard/$categoryname/domains.ufdb");
			if($GLOBALS["VERBOSE"]){echo "Checks $categoryname/domains\n";}
			$textsize=@filesize("/var/lib/squidguard/$categoryname/domains");
			
		}
		if(!is_numeric($textsize)){$textsize=0;}
		if(!is_numeric($size)){$size=0;}
		$array[$table]=array("DBSIZE"=>$size,"TXTSIZE"=>$textsize);
	}

	if($GLOBALS["VERBOSE"]){print_r($array);}
	@file_put_contents("/usr/share/artica-postfix/ressources/logs/web/ufdbguard_db_status", serialize($array));
	shell_exec("$chmod 777 /usr/share/artica-postfix/ressources/logs/web/ufdbguard_db_status");
	
}

function ufdbguard_recompile_missing_dbs(){
	$unix=new unix();
	$touch=$unix->find_program("touch");
	@mkdir("/var/lib/squidguard",755,true);
	$q=new mysql_squid_builder();
	$sql="SELECT table_name as c FROM information_schema.tables WHERE table_schema = 'squidlogs' AND table_name LIKE 'category_%'";
	$results=$q->QUERY_SQL($sql);
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$table=$ligne["c"];
		if(!preg_match("#^category_(.+)#", $table,$re)){continue;}
		$categoryname=$re[1];
		echo "Starting......: ufdbGuard $table -> $categoryname\n";
		if(!is_file("/var/lib/squidguard/$categoryname/domains")){
			@mkdir("/var/lib/squidguard/$categoryname",755,true);
			$sql="SELECT LOWER(pattern) FROM {$ligne["c"]} WHERE enabled=1 AND pattern REGEXP '[a-zA-Z0-9\_\-]+\.[a-zA-Z0-9\_\-]+' INTO OUTFILE '$table.temp' FIELDS OPTIONALLY ENCLOSED BY 'n'";
			$q->QUERY_SQL($sql);
			if(!is_file("/var/lib/mysql/squidlogs/$table.temp")){
				echo "Starting......: ufdbGuard /var/lib/mysql/squidlogs/$table.temp no such file\n";
				continue;
			}
			echo "Starting......: ufdbGuard /var/lib/mysql/squidlogs/$table.temp done...\n";
			@copy("/var/lib/mysql/squidlogs/$table.temp", "/var/lib/squidguard/$categoryname/domains");	
			@unlink("/var/lib/mysql/squidlogs/$table.temp");
			echo "Starting......: ufdbGuard UFDBGUARD_COMPILE_SINGLE_DB(/var/lib/squidguard/$categoryname/domains)\n";
			UFDBGUARD_COMPILE_SINGLE_DB("/var/lib/squidguard/$categoryname/domains");					
		}else{
			echo "Starting......: ufdbGuard /var/lib/squidguard/$categoryname/domains OK\n";
			
		}
		
		if(!is_file("/var/lib/squidguard/$categoryname/expressions")){shell_exec("$touch /var/lib/squidguard/$categoryname/expressions");}
		
	}
	build();
	if(is_file("/etc/init.d/ufdb")){
		ufdbguard_admin_events("Service will be reloaded",__FUNCTION__,__FILE__,__LINE__,"config");
		shell_exec("/etc/init.d/ufdb reconfig >/dev/null 2>&1");}
	
}

function ufdbguard_recompile_dbs(){
	@unlink("/var/log/artica-postfix/ufdbguard-compilator.debug");
	build();
	$unix=new unix();
	$rm=$unix->find_program("rm");
	shell_exec("$rm -rf /var/lib/squidguard/*");
	ufdbguard_recompile_missing_dbs();	
	
}
function ufdbguard_schedule(){
	$sock=new sockets();
	$unix=new unix();
	$UfdbGuardSchedule=unserialize(base64_decode($sock->GET_INFO("UfdbGuardSchedule")));
	$cronfile="/etc/cron.d/artica-ufdb-dbs";	
	if(!is_numeric($UfdbGuardSchedule["EnableSchedule"])){$UfdbGuardSchedule["EnableSchedule"]=0;}
	if($UfdbGuardSchedule["EnableSchedule"]==0){
		@unlink($cronfile);
		echo "Starting......: ufdbGuard recompile all databases is not scheduled\n";
		return;
	}
	$f[]="MAILTO=\"\"";
	$f[]="{$UfdbGuardSchedule["H"]} {$UfdbGuardSchedule["M"]} * * * root ".$unix->LOCATE_PHP5_BIN()." ".__FILE__." --ufdbguard-recompile-dbs >/dev/null 2>&1"; 
	@file_put_contents($cronfile,@implode("\n",$f) );	
	echo "Starting......: ufdbGuard recompile all databases each day at {$UfdbGuardSchedule["H"]}:{$UfdbGuardSchedule["M"]}\n";
	events_ufdb_tail("ufdbGuard recompile all databases each day at {$UfdbGuardSchedule["H"]}:{$UfdbGuardSchedule["M"]}",__LINE__);
}

function UFDBGUARD_COMPILE_CATEGORY($category){
	$unix=new unix();
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$oldpid=@file_get_contents($pidfile);
	if($unix->process_exists($pid,basename(__FILE__))){return;}
	@file_put_contents($pidfile, getmypid());
	
	ufdbguard_admin_events("start $category category compilation",__FUNCTION__,__FILE__,__LINE__,"compile");
	$ufdb=new compile_ufdbguard();
	$ufdb->compile_category($category);
	ufdbguard_admin_events("Service will be reloaded",__FUNCTION__,__FILE__,__LINE__,"config");
	shell_exec("/etc/init.d/ufdb reload");	
}

function UFDBGUARD_COMPILE_ALL_CATEGORIES(){
	$unix=new unix();
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$oldpid=@file_get_contents($pidfile);
	if($unix->process_exists($pid,basename(__FILE__))){return;}
	@file_put_contents($pidfile, getmypid());
	ufdbguard_admin_events("start all categories compilation",__FUNCTION__,__FILE__,__LINE__,"compile");
	$q=new mysql_squid_builder();
	$t=time();
	$cats=$q->LIST_TABLES_CATEGORIES();
	$ufdb=new compile_ufdbguard();
	while (list ($table, $line) = each ($cats) ){
		if(preg_match("#category_(.+)#", $table,$re)){
			$ufdb->compile_category($re[1]);
		}
		
	}
	
	$ttook=$unix->distanceOfTimeInWords($t,time(),true);
	ufdbguard_admin_events("Compilation all categories done ($ttook)",__FUNCTION__,__FILE__,__LINE__,"global-compile");
	ufdbguard_admin_events("Service will be reloaded",__FUNCTION__,__FILE__,__LINE__,"config");
	shell_exec("/etc/init.d/ufdb reload");		
}

function cron_compile(){
	$users=new usersMenus();
	if(!$users->APP_UFDBGUARD_INSTALLED){return;}
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$unix=new unix();
	$restart=false;
	if($unix->process_exists(@file_get_contents($pidfile))){return;}
	@file_put_contents($pidfile, getmypid());
	
	
	if(is_file("/etc/artica-postfix/ufdbguard.compile.alldbs")){
		@unlink("/etc/artica-postfix/ufdbguard.compile.alldbs");
		events_ufdb_exec("CRON:: -> ufdbguard_recompile_dbs()");
		ufdbguard_admin_events("-> ufdbguard_recompile_dbs()",__FUNCTION__,__FILE__,__LINE__,"config");
		UFDBGUARD_COMPILE_ALL_CATEGORIES();
		return;
	}
	
	if(is_file("/etc/artica-postfix/ufdbguard.compile.missing.alldbs")){
		events_ufdb_exec("CRON:: -> ufdbguard_recompile_missing_dbs()");
		@unlink("/etc/artica-postfix/ufdbguard.compile.missing.alldbs");
		ufdbguard_admin_events("-> ufdbguard_recompile_missing_dbs()",__FUNCTION__,__FILE__,__LINE__,"config");
		ufdbguard_recompile_missing_dbs();
		return;
	}
	
	if(is_file("/etc/artica-postfix/ufdbguard.reconfigure.task")){
		events_ufdb_exec("CRON:: -> build()");
		@unlink("/etc/artica-postfix/ufdbguard.reconfigure.task");
		ufdbguard_admin_events("-> build()",__FUNCTION__,__FILE__,__LINE__,"config");
		build();
		return;
	}
	

	foreach (glob("/etc/artica-postfix/ufdbguard.recompile-queue/*") as $filename) {
		$restart=true;
		$db=@file_get_contents($filename);
		@unlink($filename);
		ufdbguard_admin_events("-> UFDBGUARD_COMPILE_SINGLE_DB(/var/lib/squidguard/$db/domains)",__FUNCTION__,__FILE__,__LINE__,"config");
		UFDBGUARD_COMPILE_SINGLE_DB("/var/lib/squidguard/$db/domains");
		
		
	}
	
	if($restart){
		ufdbguard_admin_events("Service will be reloaded",__FUNCTION__,__FILE__,__LINE__,"config");
		shell_exec("/etc/init.d/ufdb reload");
	}
	
	
}



function events_ufdb_exec($text){
		$pid=@getmypid();
		$date=@date("h:i:s");
		$logFile="/var/log/artica-postfix/ufdbguard-compilator.debug";
		$size=@filesize($logFile);
		if($size>1000000){@unlink($logFile);}
		$f = @fopen($logFile, 'a');
		$textnew="$date [$pid]:: ".basename(__FILE__)." $text\n";
		
		@fwrite($f,$text );
		@fclose($f);	
		}


function events_ufdb_tail($text,$line=0){
		$pid=@getmypid();
		$date=@date("h:i:s");
		$logFile="/var/log/artica-postfix/ufdbguard-tail.debug";
		$size=@filesize($logFile);
		if($size>1000000){@unlink($logFile);}
		$f = @fopen($logFile, 'a');
		if($line>0){$line=" line:$line";}else{$line=null;}
		$textnew="$date [$pid]:: ".basename(__FILE__)." $text$line\n";
		if($GLOBALS["VERBOSE"]){echo $textnew;}
		@fwrite($f,$textnew );
		@fclose($f);	
		events_ufdb_exec($textnew);
		}
		
	


?>