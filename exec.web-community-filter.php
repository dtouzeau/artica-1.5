<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.ccurl.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');
include_once(dirname(__FILE__).'/ressources/class.squidguard.inc');

if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;}

if(!ifMustBeExecuted()){die();}

if($argv[1]=="--patterns"){die();}
if($argv[1]=="--sitesinfos"){die();}
if($argv[1]=="--groupby"){die();}
if($argv[1]=="--import"){import();die();}
if($argv[1]=="--export"){export(true);die();}



	$t=time();
	$sock=new sockets();
	$users=new usersMenus();
	
	$system_is_overloaded=system_is_overloaded();
	if($system_is_overloaded){
		$unix=new unix();
		$unix->send_email_events("Overloaded system, Web filtering maintenance databases tasks aborted (general)",
		 "Artica will wait a new better time...", "proxy");
		die();
	}
	

	$WebCommunityUpdatePool=$sock->GET_INFO("WebCommunityUpdatePool");
	if(!is_numeric($WebCommunityUpdatePool)){$WebCommunityUpdatePool=360;$sock->SET_INFO("WebCommunityUpdatePool",360);}
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$cachetime="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".time";
	$unix=new unix();	
	$pid=@file_get_contents($pidfile);
	if($unix->process_exists($pid)){
		WriteMyLogs("Already executed PID:$pid, die()",__FUNCTION__,__FILE__,__LINE__);
		die();
	}
	
	$filetime=file_time_min($cachetime);
	if(!$GLOBALS["FORCE"]){
		if($filetime<$WebCommunityUpdatePool){WriteMyLogs("{$filetime}Mn need {$WebCommunityUpdatePool}Mn, aborting...",__FUNCTION__,__FILE__,__LINE__);die();}
	}
	
	
	@mkdir(dirname($cachetime),0755,true);
	@unlink($cachetime);
	@file_put_contents($cachetime,"#");
	$GLOBALS["MYPID"]=getmypid();
	@file_put_contents($pidfile,$GLOBALS["MYPID"]);
	
	WriteMyLogs("-> Export()","MAIN",null,__LINE__);
	Export();
	WriteMyLogs("-> Import()","MAIN",null,__LINE__);
	import();
	

	$distanceOfTimeInWords=$unix->distanceOfTimeInWords($t,time());
	$unix->send_email_events("Web filtering maintenance databases tasks success",
		 "Exporting websites, importing websites calculate categories took $distanceOfTimeInWords", "proxy");
	
	
function Export($asPid=false){
	$unix=new unix();
	$restartProcess=false;
	$nohup=$unix->find_program("nohup");
	$php5=$unix->LOCATE_PHP5_BIN();
	$restart_cmd=trim("$nohup $php5 ".__FILE__." --export >/dev/null 2>&1 &");
	
	
	if($asPid){
		$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
		$cachetime="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".time";
		$unix=new unix();	
		$pid=@file_get_contents($pidfile);
		if($unix->process_exists($pid)){WriteMyLogs("Already executed PID:$pid, die()",__FUNCTION__,__FILE__,__LINE__);die();}	
		@file_put_contents($pidfile,getmypid());
	}
	
	
	$q=new mysql_squid_builder();
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
				if(count($f)>1000){
					$q->QUERY_SQL($prefix.@implode(",",$f));
					if(!$q->ok){echo $q->mysql_error."\n";return;}
					$f=array();
				}
				
			}
		$q->QUERY_SQL("UPDATE $table SET sended=1 WHERE sended=0");
		}
		
	}	
	
	if(count($f)>0){$q->QUERY_SQL($prefix.@implode(",",$f));$f=array();	}
			
	
	$ALLCOUNT=$q->COUNT_ROWS("categorize");
	if($ALLCOUNT>4000){$restartProcess=true;}
	$sql="SELECT * FROM categorize ORDER BY zDate DESC LIMIT 0,4000";

	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error."\n$sql\n";return;}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($ligne["category"]==null){continue;}
		if($ligne["pattern"]==null){continue;}
		if($ligne["zmd5"]==null){continue;}
		
		$array[$ligne["zmd5"]]=array(
				"category"=>$ligne["category"],
				"pattern"=>$ligne["pattern"],
			    "uuid"=>$ligne["uuid"]
		);
	}

if(!is_array($array)){WriteMyLogs("Nothing to export",__FUNCTION__,__FILE__,__LINE__);return;}
if(count($array)==0){WriteMyLogs("Nothing to export",__FUNCTION__,__FILE__,__LINE__);return;}

	WriteMyLogs("Exporting ". count($array)." websites",__FUNCTION__,__FILE__,__LINE__);
	$f=base64_encode(serialize($array));
	$curl=new ccurl("http://www.artica.fr/shalla-orders.php");
	$curl->parms["COMMUNITY_POST"]=$f;
	
	if(!$curl->get()){
		writelogs("Failed exporting ".count($array)." categorized websites to Artica cloud repository servers",__FUNCTION__,__FILE__,__LINE__);
		$unix->send_email_events("Failed exporting ".count($array)." categorized websites to Artica cloud repository servers",null,"proxy");
		WriteMyLogs("Exporting failed". count($array)." websites",__FUNCTION__,__FILE__,__LINE__);
		return null;
	}
	
	if(preg_match("#<ANSWER>OK</ANSWER>#is",$curl->data)){
		WriteMyLogs("Exporting success ". count($array)." websites",__FUNCTION__,__FILE__,__LINE__);
		$unix->send_email_events("Success exporting ".count($array)." categorized websites to Artica cloud repository servers",null,"proxy");
		
		writelogs("Deleting websites...",__FUNCTION__,__FILE__,__LINE__);
		while (list ($md5, $datas) = each ($array) ){
			$sql="DELETE FROM categorize WHERE zmd5='$md5'";
			$q->QUERY_SQL($sql,"artica_backup");
		}
		
		if($restartProcess){
			writelogs("$restart_cmd",__FUNCTION__,__FILE__,__LINE__);
			shell_exec($restart_cmd);
		}else{
			$q->QUERY_SQL("OPTIMIZE TABLE categorize","artica_backup");
		}
	}else{
		writelogs("Failed exporting ".count($array)." categorized websites to Artica cloud repository servers \"$curl->data\"",__FUNCTION__,__FILE__,__LINE__);
	}
	
	
	
}



function pushit(){
	$curl=new ccurl("http://www.artica.fr/shalla-orders.php");
	$curl->parms["ORDER_EXPORT"]="yes";
	$curl->get();
	if(preg_match("#<ANSWER>OK</ANSWER>#is",$curl->data)){
		WriteMyLogs("success",__FUNCTION__,__FILE__,__LINE__);
	}else{
		WriteMyLogs("failed\n$curl->data" ,__FUNCTION__,__FILE__,__LINE__);	
	}
}

function import(){
	include_once(dirname(__FILE__)."/exec.squid.blacklists.php");
	update();downloads();inject();
	WriteCategoriesStatus(true);
}

function ParseGzSqlFile($filepath){
	
	
	if($GLOBALS["MYSQLCOMMAND"]==null){
		$unix=new unix();
		$mysql=$unix->find_program("mysql");
		$q=new mysql();
		if($q->mysql_password<>null){
			$password=" --password=$q->mysql_password";
		}
		$nice=EXEC_NICE();
		$cmd="$nice$mysql --batch --user=$q->mysql_admin $password --port=$q->mysql_port";
		$cmd=$cmd." --host=$q->mysql_server --database=artica_backup";
		$cmd=$cmd." --max_allowed_packet=500M";
		$GLOBALS["MYSQLCOMMAND"]=$cmd;
	}else{
		$cmd=$GLOBALS["MYSQLCOMMAND"];
	}
	
	//echo $cmd." <$filepath\n";
	echo "Starting......: [ParseGzSqlFile]:: Artica database community running importation (". basename($filepath).")\n";
	exec("$cmd <$filepath 2>&1",$results);
	
	
	
	if(count($results)>0){
		while (list ($num, $ligne) = each ($results) ){
			if(!preg_match("#Duplicate entry#",$ligne)){
				echo "Starting......: Artica database community $ligne\n";
				if(preg_match("#ERROR\s+[0-9]+#",$ligne)){
					echo "Starting......: Artica database community error detected\n";
					$GLOBALS["NEWFILES"][]=$ligne;
					$unix->send_email_events("Web community mysql error", "Unable to import data file $filepath\n$ligne","proxy");
					return false;
				}
			}
		}
	}
	return true;
	@unlink($filepath);
	
}


function uncompress($srcName, $dstName) {
	$string = implode("", gzfile($srcName));
	$fp = fopen($dstName, "w");
	fwrite($fp, $string, strlen($string));
	fclose($fp);
} 
	



function WriteCategory($category){
	$squidguard=new squidguard();
	echo "Starting......: Artica database writing category $category\n";
	echo "Starting......: Artica database /etc/dansguardian/lists/blacklist-artica/$category/domains\n";
	echo "Starting......: Artica database /var/lib/squidguard/blacklist-artica/$category\n";
	@mkdir("/etc/dansguardian/lists/blacklist-artica/$category",0755,true);
	@mkdir("/var/lib/squidguard/blacklist-artica/$category",0755,true);
	
	if(!is_file("/etc/dansguardian/lists/blacklist-artica/$category/urls")){@file_put_contents("/etc/dansguardian/lists/blacklist-artica/$category/urls","#");}
	if(!is_file("/var/lib/squidguard/blacklist-artica/$category/urls")){@file_put_contents("/var/lib/squidguard/blacklist-artica/$category/urls","#");}
		
	$sql="SELECT pattern FROM dansguardian_community_categories WHERE enabled=1 and category='$category'";
	$q=new mysql_squid_builder();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		echo "Starting......: Artica database $q->mysql_error\n";
		return;
	}
	$num=mysql_num_rows($results);
	echo "Starting......: Artica database $num domains\n";
	
	$domain_path_1="/etc/dansguardian/lists/blacklist-artica/$category/domains";
	$domain_path_2="/var/lib/squidguard/blacklist-artica/$category/domains";
	$fh1 = fopen($domain_path_1, 'w+');
	$fh2 = fopen($domain_path_2, 'w+');
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($ligne["pattern"]==null){continue;}
		 if(!$squidguard->VerifyDomainCompiledPattern($ligne["pattern"])){continue;}
		 fwrite($fh1, $ligne["pattern"]."\n");
		 fwrite($fh2, $ligne["pattern"]."\n");
	}
	
	fclose($fh1);
	fclose($fh2);
	
	echo "Starting......: finish\n\n";
		
}



function GetCategory($www){
	if(preg_match("#^www\.(.+)#",$www,$re)){$www=$re[1];}
	$sql="SELECT category FROM dansguardian_community_categories WHERE pattern='$www' and enabled=1";
	$q=new mysql_squid_builder();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$f[]=$ligne["category"];
	}
	
	if(is_array($f)){return @implode(",",$f);}
	
}


function mycnf_get_value($key){
	$unix=new unix();
	$cnf=$unix->MYSQL_MYCNF_PATH();
	$f=explode("\n",@file_get_contents($cnf));
	while (list ($index, $line) = each ($f) ){
		if(preg_match("#$key(.*?)=(.*)#",$line,$re)){
			$re[2]=trim($re[2]);
			return $re[2];
			}
		}
	}


function mycnf_change_value($key,$value_to_modify){
	$unix=new unix();
	$value_to_modify=trim($value_to_modify);
	$cnf=$unix->MYSQL_MYCNF_PATH();
	$f=explode("\n",@file_get_contents($cnf));
	while (list ($index, $line) = each ($f) ){
		if(preg_match("#$key(.*?)=(.*)#",$line,$re)){
			$re[2]=trim($re[2]);
			echo "Starting......: Artica database community line $index $key = {$re[2]} change to $value_to_modify\n";
			$f[$index]="$key = $value_to_modify";
			$found=true;
			}
		}
	@file_put_contents($cnf,@implode("\n",$f));
	
	
	
	}
	

function WriteMyLogs($text,$function,$file,$line){
	$mem=round(((memory_get_usage()/1024)/1000),2);
	writelogs($text,$function,__FILE__,$line);
	$logFile="/var/log/artica-postfix/".basename(__FILE__).".log";
	if(!is_dir(dirname($logFile))){mkdir(dirname($logFile));}
   	if (is_file($logFile)) { 
   		$size=filesize($logFile);
   		if($size>9000000){unlink($logFile);}
   	}
   	$date=date('m-d H:i:s');
	$logFile=str_replace("//","/",$logFile);
	$f = @fopen($logFile, 'a');
	@fwrite($f, "$date [{$GLOBALS["MYPID"]}][{$mem}MB]: [$function::$line] $text\n");
	@fclose($f);
}
function ifMustBeExecuted(){
	$users=new usersMenus();
	$sock=new sockets();
	$update=true;
	if(!$users->SQUID_INSTALLED){$update=false;}
	$CategoriesRepositoryEnable=$sock->GET_INFO("CategoriesRepositoryEnable");
	$EnableWebProxyStatsAppliance=$sock->GET_INFO("EnableWebProxyStatsAppliance");
	if(!is_numeric($CategoriesRepositoryEnable)){$CategoriesRepositoryEnable=0;}
	if(!is_numeric($EnableWebProxyStatsAppliance)){$EnableWebProxyStatsAppliance=0;}
	if($CategoriesRepositoryEnable==1){$update=true;}
	if($EnableWebProxyStatsAppliance==1){$update=true;}
	return $update;
}	
	


?>