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
@mkdir("/var/log/artica-postfix/dansguardian-stats",0666,true);
events("running $pid ");
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
if(preg_match("#\/\/localhost#",$buffer)){return null;}

	if(preg_match('#"([0-9\.\s:]+)","(.+?)","(.+?)","(.+?)","(.*?)","(.*?)","(.*?)","(.*?)","(.*?)","(.*?)","(.*?)","(.*?)","(.*?)","(.*?)",(.*?)"#',$buffer,$re)){
		$date=$re[1];
		$ip=$re[2];
		$name=$re[3];
		$uri=$re[4];
		$raison=$re[5];
		$size=$re[7];
		$rule=$re[14];
		
		
		
		Builsql($ip,$name,$uri,$rule,$raison,$size);
		return null;
		}
	events("Not filtered: $buffer");

}


function Builsql($CLIENT,$name,$uri,$rule,$TYPE,$size=0){
	$CLIENT=trim($CLIENT);
	if($CLIENT=='-'){$CLIENT=$name;}
	//events("CLIENT: $CLIENT:: name::$name rule::$rule TYPE::$TYPE");
	
	if(!preg_match("#[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+#",$CLIENT)){
		$user=$CLIENT;
		if(preg_match("#[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+#",$name)){
			$CLIENT=$name;
		}
	}

if(preg_match("#^(?:[^/]+://)?([^/:]+)#",$uri,$re)){
		$sitename=$re[1];
	}else{
		events("unable to extract domain name from $uri");
		return false;
	}
	
	if($TYPE==null){$TYPE="PASS";}
	if(preg_match("#\*(.+?)\*\s+(.+?):#",$TYPE,$re)){
		$TYPE=$re[1];
		$REASON=$re[2];
	}
	if(preg_match("#EXCEPTION.+?Exception site match#",$TYPE)){
		$TYPE="PASS";
		$REASON="Whitelisted";
	}
	
	if(preg_match("#DENIED.+?Banned extension#",$TYPE)){
		$TYPE="DENIED";
		$REASON="Banned extension";
	}
	
	if(preg_match("#SCANNED#",$TYPE)){
		$TYPE="PASS";
		$REASON="Scanned";
	}	
	
	
	
	if($CLIENT<>$name){$CLIENT=$name;}
	$date=date('Y-m-d h:i:s');
	
	
	
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
			$record = geoip_record_by_name($site_IP);
			if ($record) {
				$Country=$record["country_name"];
				$GLOBALS["COUNTRIES"][$site_IP]=$Country;
			}
		}
	}else{
		$Country=$GLOBALS["COUNTRIES"][$site_IP];
	}
	$date=date("Y-m-d H:i:s");
	if($size==null){$size=0;}
	if($user==null){$user=$CLIENT;}
	$zMD5=md5("$uri$date$CLIENT$TYPE$Country$site_IP");
	if(preg_match("#EXCEPTION#",$TYPE)){$TYPE="PASS";$REASON="Whitelisted";}
	if($CLIENT==$user){
		if($GLOBALS[$CLIENT]==null){$user=gethostbyaddr($CLIENT);$GLOBALS[$CLIENT]=$user;}else{$user=$GLOBALS[$CLIENT];}
		}
	if($REASON==null){$REASON="Pass";}
	events("$date $REASON:: $CLIENT ($user) -> $sitename ($site_IP) Country=$Country REASON:\"$REASON\" TYPE::\"$TYPE\" size=$size" );
	$uri=addslashes($uri);
	
	if(!is_dir("/var/log/artica-postfix/dansguardian-stats3")){@mkdir("/var/log/artica-postfix/dansguardian-stats3",600,true);}
	$filewebsite="/var/log/artica-postfix/dansguardian-stats3/".md5($sitename);
	$filewebsite_array=array("sitename"=>$sitename,"country"=>$Country,"ipaddr"=>$site_IP);
	$filecontent=serialize($filewebsite_array);
	if(!is_file($filewebsite)){@file_put_contents($filewebsite,$filecontent);}	
	$table="dansguardian_events_".date('Ymd');
	$sql="INSERT IGNORE INTO $table (`sitename`,`uri`,`TYPE`,`REASON`,`CLIENT`,`zDate`,`zMD5`,`remote_ip`,`country`,`QuerySize`,`uid`) 
	VALUES('$sitename','$uri','$TYPE','$REASON','$CLIENT','$date','$zMD5','$site_IP','$Country','$size','$user');";
	@file_put_contents("/var/log/artica-postfix/dansguardian-stats/$zMD5.sql",$sql);	
  
}



function events($text){
		$pid=@getmypid();
		$date=@date("h:i:s");
		$logFile="/var/log/artica-postfix/dansguardian-logger.debug";
		$size=@filesize($logFile);
		if($size>1000000){@unlink($logFile);}
		$f = @fopen($logFile, 'a');
		@fwrite($f, "$pid ".basename(__FILE__)." $text\n");
		@fclose($f);	
		}
		

?>