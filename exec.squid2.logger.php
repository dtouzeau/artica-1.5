<?php
$GLOBALS["DEBUG_INCLUDES"]=false;
$GLOBALS["LOCAL_DOMAINS"]=array();
$GLOBALS["INTERNAL_FROM"]=array();
$GLOBALS["CACHE"]=array();
$GLOBALS["VERBOSE"]=false;
$GLOBALS["DEBUG"]=false;
$GLOBALS["VERBOSE_MASTER"]=false;
$GLOBALS["LOGON-PAGE"]=false;
ini_set("bug_compat_42" , "off"); ini_set("session.bug_compat_warn" , "off"); 

include_once(dirname(__FILE__).'/framework/frame.class.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
$__server_listening = true;
$__PROCESS_NUM=0;
//error_reporting(E_ALL);
$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".D.pid";
$unix=new unix();
if($unix->process_exists(@file_get_contents($pidfile))){die();}

set_time_limit(0);
ob_implicit_flush();
declare(ticks = 1);
$pid=start_daemon();
events('Running PID '.$pid,"MAIN",__LINE__);
@file_put_contents($pidfile,$pid);
$MyPort=54424;

pcntl_signal(SIGTERM,'sig_handler');
pcntl_signal(SIGINT, 'sig_handler');
pcntl_signal(SIGCHLD,'sig_handler');
pcntl_signal(SIGHUP, 'sig_handler');
server_loop("127.0.0.1", $MyPort);
events('server_loop() Failed',"MAIN",__LINE__);

function server_loop($address, $port){
    GLOBAL $__server_listening;
    GLOBAL $__PROCESS_NUM;

    if(($sock = socket_create(AF_INET, SOCK_STREAM, 0)) < 0){
        events("failed to create socket: ".socket_strerror($sock),__FUNCTION__,__LINE__);
        exit();
    }
 
   
    if(($ret = socket_bind($sock, $address, $port)) < 0){
        events("failed to bind socket: ".socket_strerror($ret),__FUNCTION__,__LINE__);
        exit();
    }
    
  
   
    if( ( $ret = socket_listen( $sock, 0 ) ) < 0 ){
        events("failed to listen to socket: ".socket_strerror($ret),__FUNCTION__,__LINE__);
        exit();
    }

  @unlink("/etc/artica-postfix/pids/squid-tail-sock");
 // socket_set_nonblock($sock);
  socket_getsockname($socket, $GIP, $GPORT);
  $errorcode = socket_last_error();
  $errormsg = socket_strerror($errorcode);  
  events("waiting for clients to connect $GIP:$GPORT ($MyPort) [ERR.$errorcode] `$errormsg` {$GLOBALS["COUNT_ERROR_98"]}",__FUNCTION__,__LINE__);
  if($errorcode==98){ events("die...", __FUNCTION__,__LINE__); return;}
  	
  
  
   @file_put_contents("/etc/artica-postfix/pids/squid-tail-sock", "OK");
	$GLOBALS["SOCK"]=$sock;  

    while ($__server_listening){
        $connection = @socket_accept($sock);
        if ($connection === false){
            usleep(200000);
        }elseif ($connection > 0){
        	events("handle_client()...",__FUNCTION__,__LINE__);
            handle_client($sock, $connection);
        }else{
            events("error: ".socket_strerror($connection),__FUNCTION__,__LINE__);
            die;
        }
    }
}

function handle_client($ssock, $csock){
    GLOBAL $__server_listening;
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".pid";
    $pid = pcntl_fork();
	events("Fork pid $pid",__FUNCTION__,__LINE__);
	if($pid>0){
		$__PROCESS_NUM=$__PROCESS_NUM+1;
		@file_put_contents($pidfile, @getmypid());}
			
    if ($pid == -1){
        events("fork failure!",__FUNCTION__,__LINE__);
        die;
    }
    
    if($pid == 0){
        /* child process */
        $__server_listening = false;
      	events("child process -> socket close() -> interact(); ",__FUNCTION__,__LINE__);
        socket_close($ssock);
        interact($csock);
        events("Closing connection",__FUNCTION__,__LINE__);
        socket_close($csock);
    }else{
        socket_close($csock);
    }
} 


function interact($socket){
	
	events("interact() -> START",__FUNCTION__,__LINE__);
	
	while($buffer=@socket_read($socket,512,PHP_NORMAL_READ)){
	  Parseline($buffer);
	}	
	
if(socket_last_error($socket) == 104) {
    	events("Buffer:Connection closed",__FUNCTION__,__LINE__);
}

events("interact() -> STOP",__FUNCTION__,__LINE__);
	return;

}

function Parseline($buffer){
$buffer=trim($buffer);
if($buffer==null){return null;}

if(preg_match("#GET cache_object#",$buffer)){return null;}

if(preg_match('#MAC:(.+?)\s+(.+?)\s+.+?\s+(.*?)\s+\[.+?:(.+?)\s+.+?\]\s+"(GET|POST|CONNECT)\s+(.+?)\s+.+?"\s+([0-9]+)\s+([0-9]+)\s+([A-Z_]+)#',$buffer,$re)){
	    $cached=0;
	    $mac=$re[1];
		$ip=$re[2];
		$user=$re[3];
		$time=$re[4];
		$uri=$re[6];
		$code_error=$re[7];
		$size=$re[8];
		$SquidCode=$re[9];
		if($ip=="127.0.0.1"){return;}
		if(CACHEDORNOT($SquidCode)){$cached=1;}
		Builsql($ip,$user,$uri,$code_error,$size,$time,$cached,$mac);
		return null;
			
}


}

function CACHEDORNOT($SquidCode){
	
                switch ($SquidCode) {
                              case "TCP_HIT":
                               case "TCP_REFRESH_UNMODIFIED":
                               case "TCP_REFRESH_HIT":
                               case "TCP_REFRESH_FAIL_HIT":
                               case "TCP_REFRESH_MISS":
                               case "TCP_IMS_HIT":
                               case "TCP_MEM_HIT":
                               case "TCP_DENIED":                           	
                               case "TCP_IMS_MISS":
                               case "TCP_OFFLINE_HIT":
                               case "TCP_STALE_HIT":
                               case "TCP_ASYNC_HIT":
                               case "UDP_HIT":
                               case "UDP_DENIED":
                               case "UDP_INVALID":
                           return TRUE;
                           break;
                           default:
                                return FALSE;
                                 break;

                }

}

function Builsql($CLIENT,$username=null,$uri,$code_error,$size=0,$time,$cached,$mac=null){
	
$squid_error["100"]="Continue";
$squid_error["101"]="Switching Protocols";
$squid_error["102"]="Processing";
$squid_error["200"]="Pass";
$squid_error["201"]="Created";
$squid_error["202"]="Accepted";
$squid_error["203"]="Non-Authoritative Information";
$squid_error["204"]="No Content";
$squid_error["205"]="Reset Content";
$squid_error["206"]="Partial Content";
$squid_error["207"]="Multi Status";
$squid_error["300"]="Multiple Choices";
$squid_error["301"]="Moved Permanently";
$squid_error["302"]="Moved Temporarily";
$squid_error["303"]="See Other";
$squid_error["304"]="Not Modified";
$squid_error["305"]="Use Proxy";
$squid_error["307"]="Temporary Redirect";
$squid_error["400"]="Bad Request";
$squid_error["401"]="Unauthorized";
$squid_error["402"]="Payment Required";
$squid_error["403"]="Forbidden";
$squid_error["404"]="Not Found";
$squid_error["405"]="Method Not Allowed";
$squid_error["406"]="Not Acceptable";
$squid_error["407"]="Proxy Authentication Required";
$squid_error["408"]="Request Timeout";
$squid_error["409"]="Conflict";
$squid_error["410"]="Gone";
$squid_error["411"]="Length Required";
$squid_error["412"]="Precondition Failed";
$squid_error["413"]="Request Entity Too Large";
$squid_error["414"]="Request URI Too Large";
$squid_error["415"]="Unsupported Media Type";
$squid_error["416"]="Request Range Not Satisfiable";
$squid_error["417"]="Expectation Failed";
$squid_error["424"]="Locked";
$squid_error["424"]="Failed Dependency";
$squid_error["433"]="Unprocessable Entity";
$squid_error["500"]="Internal Server Error";
$squid_error["501"]="Not Implemented";
$squid_error["502"]="Bad Gateway";
$squid_error["503"]="Service Unavailable";
$squid_error["504"]="Gateway Timeout";
$squid_error["505"]="HTTP Version Not Supported";
$squid_error["507"]="Insufficient Storage";
$squid_error["600"]="Squid header parsing error";	
	
	
	
	

if(preg_match("#^(?:[^/]+://)?([^/:]+)#",$uri,$re)){
		$sitename=$re[1];
		if(preg_match("#^www\.(.+)#",$sitename,$ri)){$sitename=$ri[1];}
	}else{
		events("dansguardian-stats2:: unable to extract domain name from $uri");
		return false;
	}

	
	$TYPE=$squid_error[$code_error];
	$REASON=$TYPE;
	$CLIENT=trim($CLIENT);
	$date=date('Y-m-d')." ". $time;
	if($username==null){$username=GetComputerName($ip);}
	if($size==null){$size=0;}
	
	
	
	if(trim($GLOBALS["IPs"][$sitename])==null){
		$site_IP=trim(gethostbyname($sitename));
		$GLOBALS["IPs"][$sitename]=$site_IP;
	}else{
		$site_IP=$GLOBALS["IPs"][$sitename];
	}
	
	if(count($_GET["IPs"])>5000){unset($_GET["IPs"]);}
	if(count($_GET["COUNTRIES"])>5000){unset($_GET["COUNTRIES"]);}
	
	
	if(trim($GLOBALS["COUNTRIES"][$site_IP])==null){
		if(function_exists("geoip_record_by_name")){
			if($site_IP==null){$site_IP=$sitename;}
			$record = @geoip_record_by_name($site_IP);
			if ($record) {
				$Country=$record["country_name"];
				$GLOBALS["COUNTRIES"][$site_IP]=$Country;
			}
		}else{
			$geoerror="geoip_record_by_name no such function...";
		}
	}else{
		$Country=$GLOBALS["COUNTRIES"][$site_IP];
	}
	
	
	
	
	$zMD5=md5("$uri$date$CLIENT$username$TYPE$Country$site_IP");

	if(!is_dir("/var/log/artica-postfix/dansguardian-stats2")){@mkdir("/var/log/artica-postfix/dansguardian-stats2",600,true);}
	if(!is_dir("/var/log/artica-postfix/dansguardian-stats3")){@mkdir("/var/log/artica-postfix/dansguardian-stats3",600,true);}
	if(!$GLOBALS["SINGLE_SITE"][$sitename]){
		$filewebsite="/var/log/artica-postfix/dansguardian-stats3/".md5($sitename);
		$filewebsite_array=array("sitename"=>$sitename,"country"=>$Country,"ipaddr"=>$site_IP);
		$filecontent=serialize($filewebsite_array);
		if(!is_file($filewebsite)){
			events("$date dansguardian-stats3:: ".basename($filewebsite)." -> \"sitename\"=>$sitename,\"country\"=>$Country,\"ipaddr\"=>$site_IP  (".__LINE__.")" );
			@file_put_contents($filewebsite,$filecontent);
			if(is_file($filewebsite)){$GLOBALS["SINGLE_SITE"][$sitename]=true;}
			events("$date dansguardian-stats3:: ".count($GLOBALS["SINGLE_SITE"])." analyzed websites");
			writeMem();
		}
	}
	
	if(count($GLOBALS["SINGLE_SITE"])>1500){unset($GLOBALS["SINGLE_SITE"]);}
	events("$date dansguardian-stats2:: $REASON:: [$mac]$CLIENT ($username) -> $sitename ($site_IP) Country=$Country ($geoerror) REASON:\"$REASON\" TYPE::\"$TYPE\" size=$size (".__LINE__.")" ); 
	$uri=addslashes($uri);
	$Country=addslashes($Country);
	$sql="('$sitename','$uri','$TYPE','$REASON','$CLIENT','$date','$zMD5','$site_IP','$Country','$size','$username','$cached','$mac')";
	@file_put_contents("/var/log/artica-postfix/dansguardian-stats2/$zMD5.sql",$sql);	
	if(count($GLOBALS["RTIME"])>500){unset($GLOBALS["RTIME"]);}
	$GLOBALS["RTIME"][]=array($sitename,$uri,$TYPE,$REASON,$CLIENT,$date,$zMD5,$site_IP,$Country,$size,$username,$mac);
	@file_put_contents("/etc/artica-postfix/squid-realtime.cache",base64_encode(serialize($GLOBALS["RTIME"])));
	
	
  
}

function GeoIPavailable(){
	if(!function_exists("geoip_db_filename")){
		events('geoip_db_filename not available...');
		return;
		
	}
	
		$cst = array(
             'GEOIP_COUNTRY_EDITION' => GEOIP_COUNTRY_EDITION,
             'GEOIP_REGION_EDITION_REV0' => GEOIP_REGION_EDITION_REV0,
             'GEOIP_CITY_EDITION_REV0' => GEOIP_CITY_EDITION_REV0,
             'GEOIP_ORG_EDITION' => GEOIP_ORG_EDITION,
             'GEOIP_ISP_EDITION' => GEOIP_ISP_EDITION,
             'GEOIP_CITY_EDITION_REV1' => GEOIP_CITY_EDITION_REV1,
             'GEOIP_REGION_EDITION_REV1' => GEOIP_REGION_EDITION_REV1,
             'GEOIP_PROXY_EDITION' => GEOIP_PROXY_EDITION,
             'GEOIP_ASNUM_EDITION' => GEOIP_ASNUM_EDITION,
             'GEOIP_NETSPEED_EDITION' => GEOIP_NETSPEED_EDITION,
             'GEOIP_DOMAIN_EDITION' => GEOIP_DOMAIN_EDITION,
             );

	foreach ($cst as $k=>$v) {
	    events($k.': '.geoip_db_filename($v).'  '.(geoip_db_avail($v) ? 'Available':''));
	}	
}



function writeMem(){
	events("Memory: ".round(((memory_get_usage()/1024)/1000),2) ." Mb".__FUNCTION__,__LINE__);

}

function GetComputerName($ip){
	if($GLOBALS["resvip"][$ip]<>null){return $GLOBALS["resvip"][$ip];}
	$name=gethostbyaddr($ip);
	$GLOBALS["resvip"]=$name;
	return $name;
	}

/**
  * Signal handler
  */
function sig_handler($sig){
	
	events("sig_handler:$sig",__FUNCTION__,__LINE__);
    switch($sig){
        case SIGTERM: 
        	events("sig_handler:SIGTERM -> die()",__FUNCTION__,__LINE__);
        	die();
        	break;
        	
        case SIGHUP:
        		events("Refresh settings ($__PROCESS_NUM)",__FUNCTION__,__LINE__);
        		break;
        	
        case SIGINT:
        	events("sig_handler:SIGINT",__FUNCTION__,__LINE__);
            exit();
        	break;

        case SIGCHLD:
        	events("sig_handler:SIGCHLD",__FUNCTION__,__LINE__);
            pcntl_waitpid(-1, $status);
        	break;
    }
} 

function start_daemon(){
    $pid = pcntl_fork();
   
    if ($pid == -1){
        
        events("fork failure!",__FUNCTION__,__LINE__);
        exit();
    }elseif ($pid){
        
        exit();
    }
    else{
    	events("fork success",__FUNCTION__,__LINE__);
    	posix_setsid();
    	chdir('/');
    	umask(0);
    	return posix_getpid();
	}
} 



function events($text,$function,$line=0){
		$pid=@getmypid();
		$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".pid";
		@file_put_contents($pidfile, $pid);
		$date=@date("H:i:s");
		$logFile="/var/log/artica-postfix/squid-logger-daemon.log";
		$size=@filesize($logFile);
		if($size>1000000){@unlink($logFile);}
		$f = @fopen($logFile, 'a');
		$text="[$pid] $date $function:: $text (L.$line)\n";
		if($GLOBALS["VERBOSE"]){echo $text;}
		@fwrite($f, $text);
		@fclose($f);	
		}
		
		
?>		