<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
if(preg_match("#--includes#",implode(" ",$argv))){$GLOBALS["DEBUG_INCLUDES"]=true;}
if($GLOBALS["DEBUG_INCLUDES"]){echo basename(__FILE__)."::class.templates.inc\n";}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
if($GLOBALS["DEBUG_INCLUDES"]){echo basename(__FILE__)."::class.ini.inc\n";}
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
if($GLOBALS["DEBUG_INCLUDES"]){echo basename(__FILE__)."::framework/class.unix.inc\n";}
include_once(dirname(__FILE__).'/framework/class.unix.inc');
if($GLOBALS["DEBUG_INCLUDES"]){echo basename(__FILE__)."::frame.class.inc\n";}
include_once(dirname(__FILE__).'/framework/frame.class.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');



if(!is_file("/usr/share/artica-postfix/ressources/settings.inc")){shell_exec("/usr/share/artica-postfix/bin/process1 --force --verbose");}
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
if(preg_match("#--reload#",implode(" ",$argv))){$GLOBALS["RELOAD"]=true;}
if($GLOBALS["VERBOSE"]){ini_set('display_errors', 1);	ini_set('html_errors',0);ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);}
if($GLOBALS["VERBOSE"]){echo " commands= ".implode(" ",$argv)."\n";}

if($argv[1]=="--categories"){BuildEmptyCategories();die();}
if($argv[1]=="--days"){kav4proxyDays();die();}
if($argv[1]=="--virus"){VirusTable();die();}
if($argv[1]=="--websitesnum"){WebSitesNums();die();}
if($argv[1]=="--all"){all();die();}


function all(){kav4proxyDays();}

function WebSitesNums(){
	$sql="SELECT websites FROM kav4proxyDays GROUP BY websites";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){
		if($GLOBALS["VERBOSE"]){echo "$q->mysql_error\n";return;}
		$unix->send_email_events("Kav4Proxy:[statistics] MySQL Websites statistics error", "$q->mysql_error\n$sql\n", "proxy");return;
	}
	
	$count=mysql_num_rows($results);
	if($GLOBALS["VERBOSE"]){echo "$sql = $count rows\n";}
	$c=0;
	$prefix="INSERT IGNORE INTO dansguardian_sites(website_md5,website) VALUES";
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$md5=md5($ligne["websites"]);
		$f[]="('$md5','{$ligne["websites"]}')";
		$c++;
		if(count($f)>500){
			$compiled=$prefix.@implode(",", $f);
			$f=array();
			$q->QUERY_SQL($compiled,'artica_events');
			if(!$q->ok){
				$unix->send_email_events("Kav4Proxy:[statistics] MySQL Websites statistics error ","Errors: $sql\n$q->mysql_error\nTable $table, database artica_events\nfunction:".__FUNCTION__,"\nFile:".__FILE__,"proxy");
				return;
			}
			
			if(system_is_overloaded(basename(__FILE__))){
				$unix->send_email_events("Kav4Proxy:[statistics] overloaded system, aborting","System is overloaded after $c analyzed websites, statistics will be restarted in next cycle..".__FUNCTION__,"\nFile:".__FILE__,"proxy");
				return;
			}
			
		}
	}

	if(count($f)>0){
		$compiled=$prefix.@implode(",", $f);
		$f=array();
		$q->QUERY_SQL($compiled,'artica_events');
		if(!$q->ok){
			$unix->send_email_events("Kav4Proxy:[statistics] MySQL Websites statistics error ","Errors: $sql\n$q->mysql_error\nTable $table, database artica_events\nfunction:".__FUNCTION__,"\nFile:".__FILE__,"proxy");
			return;
		}
	}	
}


function VirusTable(){
$unix=new unix();
$table="Kav4Proxy_".date("Ym");
$sql="SELECT DATE_FORMAT( zDate, '%Y-%m-%d' ) AS tday, count(zmd5) as tcount, SUM(size) as tsum,sitename,country,category,client
	FROM `$table` WHERE zDate>DATE_SUB(NOW(),INTERVAL 5 DAY)  AND
	zDate<DATE_SUB(NOW(),INTERVAL 1 DAY) AND status='INFECTED'
	GROUP BY sitename,country,category,client ORDER BY tday DESC";
	
	
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){
		if($GLOBALS["VERBOSE"]){echo "$q->mysql_error\n";return;}
		$unix->send_email_events("Kav4Proxy:[statistics] MySQL Virus statistics error", "$q->mysql_error\n$sql\n", "proxy");return;
	}
	
	$count=mysql_num_rows($results);
	if($GLOBALS["VERBOSE"]){echo "$sql = $count rows\n";}
	$c=0;
	$prefix="INSERT IGNORE INTO kav4proxyVirus(zmd5,days,size,sitename,client,hits,category,country) VALUES";
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$md5=md5("{$ligne["tday"]}{$ligne["sitename"]}{$ligne["client"]}");
		
		$f[]="('$md5','{$ligne["tday"]}','{$ligne["tsum"]}','{$ligne["sitename"]}','{$ligne["client"]}','{$ligne["tcount"]}','{$ligne["category"]}','{$ligne["country"]}')";
		if(count($f)>500){
			$compiled=$prefix.@implode(",", $f);
			$f=array();
			$q->QUERY_SQL($compiled,'artica_events');
			if(!$q->ok){
				$unix->send_email_events("Kav4Proxy:[statistics] MySQL Virus statistics error ","Errors: $sql\n$q->mysql_error\nTable $table, database artica_events\nfunction:".__FUNCTION__,"\nFile:".__FILE__,"proxy");
				return;
			}
			
			if(system_is_overloaded(basename(__FILE__))){
				$unix->send_email_events("Kav4Proxy:[statistics] overloaded system, aborting","System is overloaded after $c analyzed websites, statistics will be restarted in next cycle..".__FUNCTION__,"\nFile:".__FILE__,"proxy");
				return;
			}
			
		}
	}

	if(count($f)>0){
		$compiled=$prefix.@implode(",", $f);
		$f=array();
		$q->QUERY_SQL($compiled,'artica_events');
		if(!$q->ok){
			$unix->send_email_events("Kav4Proxy:[statistics] MySQL Virus statistics error ","Errors: $sql\n$q->mysql_error\nTable $table, database artica_events\nfunction:".__FUNCTION__,"\nFile:".__FILE__,"proxy");
			return;
		}
	}	
		
	
}




function BuildEmptyCategories(){
	
	$unix=new unix();
	$table="Kav4Proxy_".date("Ym");
	$sql="SELECT sitename FROM $table WHERE LENGTH( category ) = 0 GROUP BY sitename";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){
		if($GLOBALS["VERBOSE"]){echo "$q->mysql_error\n";return;}
		$unix->send_email_events("Kav4Proxy:[statistics] MySQL statistics error", "$q->mysql_error\n$sql\n", "proxy");return;
	}
	$t1=time();
	$count=mysql_num_rows($results);
	$c=0;
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$sitename=$ligne["sitename"];
		$cat=GetCategory($sitename);
		if($cat<>null){
			$cat=str_replace("forum,forums", "forums",$cat);
			$sql="UPDATE `$table` SET `category`='$cat' WHERE `sitename`='$sitename'";
			
			$q->QUERY_SQL($sql,"artica_events");
			if(!$q->ok){
				$unix->send_email_events("Kav4Proxy:[statistics] MySQL statistics error ","Errors: $sql\n$q->mysql_error\nTable $table, database artica_events","proxy");
				return;
			}
			
			$q->QUERY_SQL("UPDATE `kav4proxyDays` SET `category`='$cat' WHERE `websites`='$sitename'","artica_events");
			$q->QUERY_SQL("UPDATE `kav4proxyVirus` SET `category`='$cat' WHERE `sitename`='$sitename'","artica_events");

		$c++;}
		
	}
	
	
	
	if($c>0){
		$distanceOfTimeInWords=$unix->distanceOfTimeInWords($t1,time());
		$unix->send_email_events("Kav4Proxy:[statistics] MySQL statistics $c/$count categorized for $table table","took $distanceOfTimeInWords","proxy");
		
	}
	
	
}

function kav4proxyDays(){
	
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$pidtime="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".time";
	$unix=new unix();
	$pid=@file_get_contents($pidfile);
	if($unix->process_exists($pid,__FILE__)){
		writelogs("Warning: Already running pid $pid",__FUNCTION__,__FILE__,__LINE__);
		return;
	}

	@file_put_contents($pidfile, getmypid());
	
	$time=$unix->file_time_min($pidtime);
	if($time<300){return;}
	@unlink($pidtime);
	@file_put_contents($pidtime, time());
	BuildEmptyCategories();
	VirusTable();
	
	$unix=new unix();
	$table="Kav4Proxy_".date("Ym");
	$sql="SELECT DATE_FORMAT( zDate, '%Y-%m-%d' ) AS tday, count(zmd5) as tcount, SUM(size) as tsum,sitename,country,category 
	FROM `$table` WHERE zDate>DATE_SUB(NOW(),INTERVAL 5 DAY) 
	AND
	zDate<DATE_SUB(NOW(),INTERVAL 1 DAY)
	GROUP BY sitename,country,category ORDER BY tday DESC";
	
	
	
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){
		if($GLOBALS["VERBOSE"]){echo "$q->mysql_error\n";return;}
		$unix->send_email_events("Kav4Proxy:[statistics] MySQL statistics error", "$q->mysql_error\n$sql\n", "proxy");return;
	}
	
	$count=mysql_num_rows($results);
	if($GLOBALS["VERBOSE"]){echo "$sql = $count rows\n";}
	$c=0;
	$prefix="INSERT IGNORE INTO kav4proxyDays(zmd5,days,size,websites,hits,category,Country) VALUES";
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$md5=md5("{$ligne["tday"]}{$ligne["sitename"]}");
		
		$f[]="('$md5','{$ligne["tday"]}','{$ligne["tsum"]}','{$ligne["sitename"]}','{$ligne["tcount"]}','{$ligne["category"]}','{$ligne["country"]}')";
		if(count($f)>500){
			$compiled=$prefix.@implode(",", $f);
			$f=array();
			$q->QUERY_SQL($compiled,'artica_events');
			if(!$q->ok){
				$unix->send_email_events("Kav4Proxy:[statistics] MySQL statistics error ","Errors: $sql\n$q->mysql_error\nTable $table, database artica_events\nfunction:".__FUNCTION__,"\nFile:".__FILE__,"proxy");
				return;
			}
		}
	}

	if(count($f)>0){
		$compiled=$prefix.@implode(",", $f);
		$f=array();
		$q->QUERY_SQL($compiled,'artica_events');
		if(!$q->ok){
			$unix->send_email_events("Kav4Proxy:[statistics] MySQL statistics error ","Errors: $sql\n$q->mysql_error\nTable $table, database artica_events\nfunction:".__FUNCTION__,"\nFile:".__FILE__,"proxy");
			return;
		}
	}

WebSitesNums();	
	
}


function GetCategory($www){
	if(preg_match("#^www\.(.+)#",$www,$re)){$www=$re[1];}
	$sql="SELECT category FROM dansguardian_community_categories WHERE pattern='$www' and enabled=1";
	$q=new mysql_squid_builder();
	$f=array();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$f[]=$ligne["category"];
	}
	
	if(count($f)>0){return @implode(",",$f);}
}