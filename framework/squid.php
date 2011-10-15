<?php

include_once(dirname(__FILE__)."/frame.class.inc");
include_once(dirname(__FILE__)."/class.unix.inc");


if(isset($_GET["access-logs"])){access_logs();exit;}

if(isset($_GET["reprocess-database"])){community_reprocess_category();exit();}
if(isset($_GET["kav4proxy-update-now"])){kav4proxy_update();exit();}

if(isset($_GET["update-database-blacklist"])){blacklist_update();exit();}
if(isset($_GET["compil-params"])){compile_params();exit();}
if(isset($_GET["migration-stats"])){migration_stats();exit();}
if(isset($_GET["re-categorize"])){re_categorize();exit();}
if(isset($_GET["kav4proxy-license-error"])){kav4proxy_license_error();exit();}
if(isset($_GET["kav4proxy-pattern-date"])){kav4proxy_pattern_date();exit();}
if(isset($_GET["kav4proxy-configure"])){kav4proxy_configure();exit();}
if(isset($_GET["squid-realtime-cache"])){squid_realtime_cache();exit();}
if(isset($_GET["visited-sites"])){visited_sites();exit();}
if(isset($_GET["rebuild-filters"])){rebuild_filters();exit();}
if(isset($_GET["ufdbguardconf"])){ufdbguardconf();exit();}
if(isset($_GET["export-web-categories"])){export_web_categories();exit();}
if(isset($_GET["ufdbguard-compile-database"])){ufdbguard_compile_database();exit();}
if(isset($_GET["ufdbguard-compile-alldatabases"])){ufdbguard_compile_all_databases();exit();}




while (list ($num, $line) = each ($_GET)){$f[]="$num=$line";}

writelogs_framework("unable to understand query !!!!!!!!!!!..." .@implode(",",$f),"main()",__FILE__,__LINE__);
die();


function access_logs(){
	$unix=new unix();
	$tail=$unix->find_program("tail");
	$grep=$unix->find_program("grep");
	$search=$_GET["search"];
	if(strlen($search)>1){
		$search=str_replace("*", ".*", $search);
		$cmd="$tail -n 1000 /var/log/squid/access.log|$grep -E \"$search\" 2>&1";
	}else{
		$cmd="$tail -n 500 /var/log/squid/access.log 2>&1";
	}
	
	exec($cmd,$results);
	writelogs_framework($cmd ." ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";	
	
	
	
	
}

function community_reprocess_category(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$php5=$unix->LOCATE_PHP5_BIN();
	$cmd=trim("$nohup $php5 /usr/share/artica-postfix/exec.squid.blacklists.php --reprocess-database  {$_GET["reprocess-database"]} >/dev/null 2>&1 &");
	shell_exec($cmd);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
}
function export_web_categories(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$php5=$unix->LOCATE_PHP5_BIN();
	$cmd=trim("$nohup $php5 /usr/share/artica-postfix/exec.web-community-filter.php --export-perso-cats >/dev/null 2>&1 &");
	shell_exec($cmd);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
}


function migration_stats(){
	$unix=new unix();
	if(is_file("/etc/artica-postfix/exec.squid.logs.migrate.php.pid")){
		$pid=$unix->get_pid_from_file("/etc/artica-postfix/exec.squid.logs.migrate.php.pid");
		if(is_numeric($pid)){
			if($unix->process_exists($pid)){
				$time=$unix->PROCCESS_TIME_MIN($pid);
				writelogs_framework("/etc/artica-postfix/exec.squid.logs.migrate.php.pid ->$pid $time mn",__FUNCTION__,__FILE__,__LINE__);
				echo "<articadatascgi>". base64_encode(serialize( array($pid,$time)))."</articadatascgi>";
				return;
			}
		}
	}
	if(is_file("/etc/artica-postfix/pids/exec.squid.logs.migrate.php.pid")){
		$pid=$unix->get_pid_from_file("/etc/artica-postfix/pids/exec.squid.logs.migrate.php.pid");
		if(is_numeric($pid)){
			if($unix->process_exists($pid)){
				$time=$unix->PROCCESS_TIME_MIN($pid);
				echo "<articadatascgi>". base64_encode(serialize(array($pid,$time)))."</articadatascgi>";
			}
		}
	}		
}

function compile_params(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$php5=$unix->LOCATE_PHP5_BIN();
	$cmd=trim("$php5 /usr/share/artica-postfix/exec.squid.php --compilation-params");
	shell_exec($cmd);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);	
}

function kav4proxy_configure(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$php5=$unix->LOCATE_PHP5_BIN();
	$cmd=trim("$nohup $php5 /usr/share/artica-postfix/exec.kav4proxy.php >/dev/null 2>&1 &");
	shell_exec($cmd);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);	
}
function blacklist_update(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$php5=$unix->LOCATE_PHP5_BIN();
	$cmd=trim("$nohup $php5 /usr/share/artica-postfix/exec.squid.blacklists.php --update >/dev/null 2>&1 &");
	shell_exec($cmd);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);	
}
function kav4proxy_update(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$php5=$unix->LOCATE_PHP5_BIN();
	$cmd=trim("$nohup /usr/share/artica-postfix/bin/artica-update --kav4proxy >/dev/null 2>&1 &");
	shell_exec($cmd);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);	
	
}
function kav4ProxyPatternDatePath(){
$unix=new unix();
	$base=$unix->KAV4PROXY_GET_VALUE("path","BasesPath");
	if(is_file("$base/u0607g.xml")){return "$base/u0607g.xml";}	
	if(is_file("$base/master.xml")){return "$base/master.xml";}
	if(is_file("$base/av-i386-0607g.xml")){return "$base/av-i386-0607g.xml";}
	return "$base/master.xml";
}


function kav4proxy_pattern_date(){
	$unix=new unix();
	$base=kav4ProxyPatternDatePath();
	writelogs_framework("Found $base",__FUNCTION__,__FILE__,__LINE__);
	if(!is_file($base)){
		writelogs_framework("$base no such file",__FUNCTION__,__FILE__,__LINE__);
		return;}
	$f=explode("\n",@file_get_contents($base));
	$reg='#UpdateDate="([0-9]+)\s+([0-9]+)"#';
	
	while (list ($num, $ligne) = each ($f) ){
		if(preg_match($reg,$ligne,$re)){
			writelogs_framework("Found {$re[1]} {$re[2]}",__FUNCTION__,__FILE__,__LINE__);
			if(preg_match('#([0-9]{1,2})([0-9]{1,2})([0-9]{1,4});([0-9]{1,2})([0-9]{1,2})#',trim($re[1]).";".trim($re[2]),$regs)){
				echo "<articadatascgi>". base64_encode($regs[3]. "/" .$regs[2]. "/" .$regs[1] . " " . $regs[4] . ":" . $regs[5])."</articadatascgi>";
				return;
			}
		}
	}	
	writelogs_framework("Not found in $base",__FUNCTION__,__FILE__,__LINE__);
}



function kav4proxy_license_error(){
	$unix=new unix();
	
	$cmd=trim("/opt/kaspersky/kav4proxy/bin/kav4proxy-licensemanager -i 2>&1");
	writelogs_framework("$cmd = ".count($results)." lines",__FUNCTION__,__FILE__,__LINE__);	
	exec($cmd,$results);
	while (list ($num, $ligne) = each ($results) ){
		if(preg_match("#Error loading license :(.+)#", $ligne,$re)){
			writelogs_framework("{$re[1]}",__FUNCTION__,__FILE__,__LINE__);
			echo "<articadatascgi>". base64_encode(trim($re[1]))."</articadatascgi>";
			return;
		}		
	}
}

function squid_realtime_cache(){
	echo "<articadatascgi>".@file_get_contents("/etc/artica-postfix/squid-realtime.cache")."</articadatascgi>";
}

function visited_sites(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$php5=$unix->LOCATE_PHP5_BIN();
	$cmd=trim("$nohup $php5 /usr/share/artica-postfix/exec.squid.stats.php --visited-sites >/dev/null 2>&1 &");
	shell_exec($cmd);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);	
	
}


function re_categorize(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$php5=$unix->LOCATE_PHP5_BIN();
	$cmd=trim("$nohup $php5 /usr/share/artica-postfix/exec.re-categorize.php >/dev/null 2>&1 &");
	shell_exec($cmd);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);		
	
}

function rebuild_filters(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$php5=$unix->LOCATE_PHP5_BIN();
	$cmd=trim("$nohup $php5 /usr/share/artica-postfix/exec.squidguard.php --conf >/dev/null 2>&1 &");
	shell_exec($cmd);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);			
}
function ufdbguard_compile_database(){
	$unix=new unix();
	$database=$_GET["ufdbguard-compile-database"];
	$nohup=$unix->find_program("nohup");
	$php5=$unix->LOCATE_PHP5_BIN();
	$cmd=trim("$nohup $php5 /usr/share/artica-postfix/exec.squidguard.php --compile-category $database >/dev/null 2>&1 &");
	shell_exec($cmd);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);		
}
function ufdbguard_compile_all_databases(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$php5=$unix->LOCATE_PHP5_BIN();
	$cmd=trim("$nohup $php5 /usr/share/artica-postfix/exec.squidguard.php --compile-all-categories >/dev/null 2>&1 &");
	shell_exec($cmd);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);	
}


function ufdbguardconf(){
	$tpl=explode("\n",@file_get_contents("/etc/ufdbguard/ufdbGuard.conf"));
	echo "<articadatascgi>". base64_encode(serialize($tpl))."</articadatascgi>";
}