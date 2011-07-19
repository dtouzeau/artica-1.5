<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
if(!Build_pid_func(__FILE__,"MAIN")){
	events("Already executed.. aborting the process");
	die();
}

if($argv[1]=='--date'){echo date("Y-m-d H:i:s")."\n";}

$pid=getmypid();
$pidfile="/etc/artica-postfix/".basename(__FILE__).".pid";
@mkdir("/var/log/artica-postfix/squid-stats",0666,true);
events("running $pid ");
GeoIPavailable();
file_put_contents($pidfile,$pid);
$pipe = fopen("php://stdin", "r");
while(!feof($pipe)){
	$buffer .= fgets($pipe, 4096);
	Parseline($buffer);
	$buffer=null;
}

fclose($pipe);
events("Shutdown...");
die();



function Parseline($buffer){
$buffer=trim($buffer);
if($buffer==null){return null;}

if(preg_match("#GET cache_object#",$buffer)){return null;}

if(preg_match('#(.+?)\s+.+?\s+(.*?)\s+\[.+?:(.+?)\s+.+?\]\s+"(GET|POST|CONNECT)\s+(.+?)\s+.+?"\s+([0-9]+)\s+([0-9]+)#',$buffer,$re)){
	    $ip=$re[1];
		$user=$re[2];
		$time=$re[3];
		$uri=$re[5];
		$code_error=$re[6];
		$size=$re[7];
		
		Builsql($ip,$user,$uri,$code_error,$size,$time);
		return null;
			
}	


events("Not filtered: $buffer");

}


function Builsql($CLIENT,$username=null,$uri,$code_error,$size=0,$time){
	
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
		}
	}
	
	if(count($GLOBALS["SINGLE_SITE"])>1500){unset($GLOBALS["SINGLE_SITE"]);}
	events("$date dansguardian-stats2:: $REASON:: $CLIENT ($username) -> $sitename ($site_IP) Country=$Country ($geoerror) REASON:\"$REASON\" TYPE::\"$TYPE\" size=$size (".__LINE__.")" ); 
	$uri=addslashes($uri);
	$Country=addslashes($Country);
	$sql="('$sitename','$uri','$TYPE','$REASON','$CLIENT','$date','$zMD5','$site_IP','$Country','$size','$username')";
	@file_put_contents("/var/log/artica-postfix/dansguardian-stats2/$zMD5.sql",$sql);	
	if(count($GLOBALS["RTIME"])>500){unset($GLOBALS["RTIME"]);}
	$GLOBALS["RTIME"][]=array($sitename,$uri,$TYPE,$REASON,$CLIENT,$date,$zMD5,$site_IP,$Country,$size,$username);
	@file_put_contents("/etc/artica-postfix/squid-realtime.cache",base64_encode(serialize($GLOBALS["RTIME"])));
	
	
  
}



function events($text){
		$pid=@getmypid();
		$date=@date("h:i:s");
		$logFile="/var/log/artica-postfix/squid-tail.debug";
		$size=@filesize($logFile);
		if($size>1000000){@unlink($logFile);}
		$f = @fopen($logFile, 'a');
		@fwrite($f, "$pid ".basename(__FILE__)." $text\n");
		@fclose($f);	
		}
		
function GetComputerName($ip){
	if($GLOBALS["resvip"][$ip]<>null){return $GLOBALS["resvip"][$ip];}
	$name=gethostbyaddr($ip);
	$GLOBALS["resvip"]=$name;
	return $name;
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
		

?>