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
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");


if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
if(count($argv)>0){
	$imploded=implode(" ",$argv);
	if(preg_match("#--verbose#",$imploded)){$GLOBALS["VERBOSE"]=true;$GLOBALS["debug"]=true;ini_set_verbosed(); }
	if(preg_match("#--reload#",$imploded)){$GLOBALS["RELOAD"]=true;}
	if(preg_match("#--shalla#",$imploded)){$GLOBALS["SHALLA"]=true;}
	if(preg_match("#--catto=(.+?)\s+#",$imploded,$re)){$GLOBALS["CATTO"]=$re[1];}
	if($argv[1]=="--inject"){echo inject($argv[2],$argv[3]);exit;}
	if($argv[1]=="--conf"){echo conf();exit;}
	if($argv[1]=="--ufdbguard-compile"){echo UFDBGUARD_COMPILE_SINGLE_DB($argv[2]);exit;}	
	if($argv[1]=="--ufdbguard-dbs"){echo UFDBGUARD_COMPILE_DB();exit;}
	if($argv[1]=="--ufdbguard-miss-dbs"){echo ufdbguard_recompile_missing_dbs();exit;}
	if($argv[1]=="--ufdbguard-recompile-dbs"){echo ufdbguard_recompile_dbs();exit;}
	if($argv[1]=="--ufdbguard-schedule"){ufdbguard_schedule();exit;}
	if($argv[1]=="--list-missdbs"){BuildMissingUfdBguardDBS(false,true);exit;}				
	if($argv[1]=="--cron-compile"){cron_compile();exit;}
	if($argv[1]=="--ufdbguard-status"){print_r(UFDBGUARD_STATUS());exit;}
	if($argv[1]=="--persos"){PersonalCategoriesRepair();exit;}
	
	
	
}
	





include_once(dirname(__FILE__).'/framework/class.unix.inc');
if(!Build_pid_func(__FILE__,"MAIN")){
	writelogs(basename(__FILE__).":Already executed.. aborting the process",basename(__FILE__),__FILE__,__LINE__);
	die();
}

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
	
	$unix=new unix();
	$unix->DANSGUARDIAN_CATEGORIES();
	
}

function conf(){
	$users=new usersMenus();
	$s=new squidguard();
	$datas=$s->BuildConf();
	if($GLOBALS["VERBOSE"]){echo $datas;}
	@file_put_contents("/etc/squid/squidGuard.conf",$datas);
	
	@mkdir("/etc/ufdbguard",null,true);
	@file_put_contents("/etc/ufdbguard/ufdbGuard.conf",$datas);
	if($users->APP_UFDBGUARD_INSTALLED){shell_exec("/etc/init.d/ufdb reconfig");}
	PersonalCategoriesRepair();
	
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
	

	$s=new squidguard();
	$datas=$s->BuildConf();
	@file_put_contents("/etc/squid/squidGuard.conf",$datas);
	
	@mkdir("/etc/ufdbguard",null,true);
	@file_put_contents("/etc/ufdbguard/ufdbGuard.conf",$datas);
	if($users->APP_UFDBGUARD_INSTALLED){
		BuildMissingUfdBguardDBS();
		ufdbguard_schedule();
	}

	
	
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
	if(is_file("/var/log/ufdbguard/ufdbguardd.log")){@chmod("/var/log/ufdbguard/ufdbguardd.log",777);}
	if(is_file("/etc/init.d/ufdb")){shell_exec("/etc/init.d/ufdb reconfig >/dev/null 2>&1");}
	PersonalCategoriesRepair();
	shell_exec("{$GLOBALS["SQUIDBIN"]} -k reconfigure");
	send_email_events("SquidGuard/ufdbGuard rules was rebuilded","This is new configuration file of the squidGuard/ufdbGuard:\n-------------------------------------\n$datas","system");
	shell_exec(LOCATE_PHP5_BIN2()." ".dirname(__FILE__)."/exec.c-icap.php --maint-schedule");
	
	}
	
function PersonalCategoriesRepair(){
	$unix=new unix();
	$user=GetSquidUser();
	$reload=false;
	$dirs=$unix->dirdir("/var/lib/squidguard/personal-categories");
	while (list ($a, $dir) = each ($dirs)){
		if(!is_file("$dir/expressions")){
			events_ufdb_tail("exec.squidguard.php: creating $dir/expressions",__LINE__);
			@file_put_contents("$dir/expressions"," ");
			$reload=true;
		}
		
	}
	
	shell_exec("/bin/chown -R $user:$user /var/lib/squidguard >/dev/null 2>&1 &");
	if($reload){shell_exec("{$GLOBALS["SQUIDBIN"]} -k reconfigure");}
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
	
	shell_exec("/etc/init.d/ufdb reconfig");
	
}
	

function databasesStatus(){
	$datas=explode("\n",@file_get_contents("/etc/squid/squidGuard.conf"));
	$count=0;
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

function inject($category,$file=null){
	if($file==null){
		$dir="/var/lib/squidguard";
		if($GLOBALS["SHALLA"]){$dir="/root/shalla/BL";}
		if(!is_file("$dir/$category/domains")){
			echo "$dir/$category/domains no such file";
			return;
			
		}
		$file="$dir/$category/domains";
	}
		
	if(!is_file("$file")){
			echo "$file no such file";
			return;
	}
		
	$sock=new sockets();
	$uuid=base64_decode($sock->getFrameWork("cmd.php?system-unique-id=yes"));
	if($uuid==null){echo "No uuid\n";return;}
	echo "open $file\n";
	$f=explode("\n",@file_get_contents("$file"));
	krsort($f);
	$q=new mysql_squid_builder();
	if($GLOBALS["CATTO"]<>null){$category=$GLOBALS["CATTO"];}
	
	$prefix="INSERT IGNORE INTO dansguardian_community_categories (zmd5,zDate,category,pattern,uuid) VALUES ";
	
	while (list ($index, $www) = each ($f)){
		if(preg_match("#^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$#", $www)){continue;}
		$www=trim(strtolower($www));
		if($www=="thisisarandomentrythatdoesnotexist.com"){continue;}
		if($www==null){continue;}
		$md5=md5($www.$category);
		$n[]="('$md5',NOW(),'$category','$www','$uuid')";
		echo $www." - $category\n";
		
		if(count($n)>1000){
			$sql=$prefix.@implode(",",$n);
			$q->QUERY_SQL($sql,"artica_backup");
			if(!$q->ok){echo $q->mysql_error."\n";die();}
			$n=array();
			
		}
		
	}
	
		if(count($f)>0){
			$sql=$prefix.@implode(",",$n);
			$q->QUERY_SQL($sql,"artica_backup");
			if(!$q->ok){echo $q->mysql_error."\n$sql";continue;}
			$n=array();
		}	
	
	
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
			$cmd="$ufdbGenTable -n -D -W -t $category$d$u";
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




function ufdbguard_recompile_missing_dbs(){
	$array=BuildMissingUfdBguardDBS();
	while (list ($filename, $size) = each ($array) ){
	 events_ufdb_tail("#STRONG# check $filename #!STRONG#",__LINE__);
	 UFDBGUARD_COMPILE_SINGLE_DB($filename);
	}
	$array=BuildMissingUfdBguardDBS();
	build();
	if(is_file("/etc/init.d/ufdb")){shell_exec("/etc/init.d/ufdb reconfig >/dev/null 2>&1");}
	
}

function ufdbguard_recompile_dbs(){
	@unlink("/var/log/artica-postfix/ufdbguard-compilator.debug");
	build();
	$array=BuildMissingUfdBguardDBS(true);
	while (list ($filename, $size) = each ($array) ){
	 events_ufdb_tail("#STRONG# check $filename #!STRONG#",__LINE__);
	 UFDBGUARD_COMPILE_SINGLE_DB($filename);
	}
	$array=BuildMissingUfdBguardDBS();
	build();
	if(is_file("/etc/init.d/ufdb")){shell_exec("/etc/init.d/ufdb reconfig >/dev/null 2>&1");}	
	
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

function cron_compile(){
	$users=new usersMenus();
	if(!$users->APP_UFDBGUARD_INSTALLED){return;}
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$unix=new unix();
	$restart=false;
	if($unix->process_exists(@file_get_contents($pidfile))){return;}
	if(is_file("/etc/artica-postfix/ufdbguard.compile.alldbs")){
		@unlink("/etc/artica-postfix/ufdbguard.compile.alldbs");
		events_ufdb_exec("CRON:: -> ufdbguard_recompile_dbs()");
		ufdbguard_recompile_dbs();
		return;
	}
	
	if(is_file("/etc/artica-postfix/ufdbguard.compile.missing.alldbs")){
		events_ufdb_exec("CRON:: -> ufdbguard_recompile_missing_dbs()");
		@unlink("/etc/artica-postfix/ufdbguard.compile.missing.alldbs");
		ufdbguard_recompile_missing_dbs();
		return;
	}
	
	if(is_file("/etc/artica-postfix/ufdbguard.reconfigure.task")){
		events_ufdb_exec("CRON:: -> build()");
		@unlink("/etc/artica-postfix/ufdbguard.reconfigure.task");
		build();
		return;
	}
	

	foreach (glob("/etc/artica-postfix/ufdbguard.recompile-queue/*") as $filename) {
		$restart=true;
		$db=@file_get_contents($filename);
		@unlink($filename);
		UFDBGUARD_COMPILE_SINGLE_DB("/var/lib/squidguard/$db/domains");
		
		
	}
	
	if($restart){
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