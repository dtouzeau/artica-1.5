<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}

include_once(dirname(__FILE__) . '/ressources/class.users.menus.inc');
include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
include_once(dirname(__FILE__) . '/ressources/class.user.inc');
include_once(dirname(__FILE__) . '/ressources/class.ini.inc');
include_once(dirname(__FILE__) . '/ressources/class.templates.inc');
include_once(dirname(__FILE__) . '/ressources/class.rtmm.tools.inc');
include_once(dirname(__FILE__) . '/ressources/class.system.network.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__)."/framework/class.unix.inc");
include_once(dirname(__FILE__)."/framework/frame.class.inc");


if(preg_match("#--force#",@implode(" ",$argv))){$GLOBALS["FORCE"]=true;}
if(preg_match("#--verbose#",@implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}


RTMevents("start realtime monitor");
include_once(dirname(__FILE__).'/framework/class.unix.inc');
$sock=new sockets();
$EnableArticaSMTPStatistics=$sock->GET_INFO("EnableArticaSMTPStatistics");
if(!is_numeric($EnableArticaSMTPStatistics)){$EnableArticaSMTPStatistics=1;}
if($EnableArticaSMTPStatistics==0){die();}


if(!$GLOBALS["FORCE"]){
		if(!Build_pid_func(__FILE__,"MAIN")){
			writelogs(basename(__FILE__).":Already executed.. aborting the process",basename(__FILE__),__FILE__,__LINE__);
			die();
		}
		if(system_is_overloaded()){RTMevents("die, overloaded");die();}
			@mkdir("/etc/artica-postfix/croned.2",null,true);
			$RTMMailSchedule=intval($sock->GET_INFO('RTMMailSchedule'));
			if($RTMMailSchedule<1){$RTMMailSchedule=35;}
			$timef=file_get_time_min("/etc/artica-postfix/croned.2/".md5(__FILE__));
			if($timef<$RTMMailSchedule){
				RTMevents("die, {$RTMMailSchedule}mn minimal current {$timef}mn");die();
		}
	}
RTMevents("current time {$timef}mn");



$array["Content scanner malfunction"]="Content scanner malfunction";
$array["Discard"]="Discard";
$array["DNS Error"]="DNS Error";
$array["Domain not found"]="Domain not found";
$array["Error"]="Error";
$array["Greylisting"]="Greylisting";
$array["hostname not found"]="hostname not found";
$array["RBL"]="RBL";
$array["Relay access denied"]="Relay access denied";
$array["Sended"]="Sended";
$array["SPAM"]="SPAM";
$array["SPAMMY"]="SPAMMY";
$array["Mailbox unknown"]="Mailbox unknown";
$array["User unknown in relay recipient table"]="User unknown in relay recipient table";
$array[null]="{all}";


$ini=new Bs_IniHandler();
if(is_file("/etc/artica-postfix/settings/Daemons/RTMMailConfig")){$ini->loadFile("/etc/artica-postfix/settings/Daemons/RTMMailConfig");}
if($ini->_params["ENGINE"]["LIMIT"]==null){$ini->_params["ENGINE"]["LIMIT"]="100";}
$maxmail=$ini->_params["ENGINE"]["LIMIT"];
$FILTERBY=$ini->_params["ENGINE"]["FILTERBY"];
$_GET["INI"]=$ini->_params;

	RTMevents("Building $file filter by $FILTERBY");

	switch ($FILTERBY) {
		case "SPAM":
			$sql="SELECT * FROM smtp_logs WHERE LENGTH(delivery_user)>0 AND SPAM='1' ORDER BY time_connect DESC LIMIT 0,{$ini->_params["ENGINE"]["LIMIT"]}";
			$file="last-100-mails-".md5($FILTERBY).".html";
			break;
		
			
		case "SPAMMY":
			$sql="SELECT * FROM smtp_logs WHERE LENGTH(delivery_user)>0 AND spammy='1' ORDER BY time_connect DESC LIMIT 0,{$ini->_params["ENGINE"]["LIMIT"]}";
			$file="last-100-mails-".md5($FILTERBY).".html";
			break;	
	
		case "Sended":
			$sql="SELECT * FROM smtp_logs WHERE LENGTH(delivery_user)>0 AND bounce_error='$num' AND SPAM=0 AND spammy=0 ORDER BY time_connect DESC LIMIT 0,{$ini->_params["ENGINE"]["LIMIT"]}";
			$file="last-100-mails-".md5($FILTERBY).".html";
			break;	

		case null:
			$sql="SELECT * FROM smtp_logs WHERE LENGTH(delivery_user)>0 ORDER BY time_connect DESC LIMIT 0,{$ini->_params["ENGINE"]["LIMIT"]}";
			$file="last-100-mails.html";
			break;			
			
		default:
			$sql="SELECT * FROM smtp_logs WHERE LENGTH(delivery_user)>0 AND bounce_error='$num' ORDER BY time_connect DESC LIMIT 0,{$ini->_params["ENGINE"]["LIMIT"]}";
			$file="last-100-mails-".md5($FILTERBY).".html";
			break;
		}
		
	BuildHeader($sql,$file,$maxmail,$FILTERBY);		
RTMevents("RTMMailConfig OK");
UpdateDiscards();
RTMevents("UpdateDiscards() OK");
UpdateGeoip();
RTMevents("UpdateGeoip() OK");

@unlink("/etc/artica-postfix/croned.2/".md5(__FILE__));
@file_put_contents("/etc/artica-postfix/croned.2/".md5(__FILE__),date('Y-m-d H:i:s'));


die();


function BuildHeader($sql_query,$html_file="last-100-mails.html",$maxmail,$subtitle=null){
$date_start=time();
	
$q=new mysql();
$results=$q->QUERY_SQL($sql_query,"artica_events");

if(!$q->ok){
		RTMevents("Wrong sql query $q->mysql_error");
		return null;
	}
	
	$count=0;
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$count=$count+1;
		$tr=$tr.format_line($ligne);
		}
	
	if($_GET["COUNT_MAILS"]==null){	
		$sql="SELECT count(*) as tcount FROM smtp_logs";
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_events"));	
		$count_table=$ligne["tcount"];
		$count_table=number_format($count_table, 0, ',', ' ');
		$_GET["COUNT_MAILS"]=$count_table;
	}else{
		$count_table=$_GET["COUNT_MAILS"];
	}
if($subtitle==null){$subtitle="{all}";}
$date_end=time();
$calculate=distanceOfTimeInWords($date_start,$date_end);
RTMevents("$calculate");

$html="
<table style='width:100%'>
<tr>
	<td><H3 style='font-weight:bold;border-bottom:1px solid #CCCCCC;margin-bottom:4px;'>{last_mails} ($maxmail)</H3>
		<p class=caption>{filter}:<strong>$subtitle ($calculate)</strong> - $count_table eMails {total}</p>
	</td>
	<td>
		<table style='width:100%'>
		<td>
		" . postfixlogger_progress()."</td>
		</tr>
		</table>
	</td>
</tr>
</table>
	<div style='width:100%;height:300px;overflow:auto'>
	<table style='width:100%'>
	$tr
	</table>
</div>
";

RTMevents("BuildHeader():: Saving usr/share/artica-postfix/ressources/logs/$html_file ($count)");
file_put_contents("/usr/share/artica-postfix/ressources/logs/$html_file",$tr);
file_put_contents("/usr/share/artica-postfix/ressources/logs/" . str_replace("-",".",$html_file),$html);
@chmod("/usr/share/artica-postfix/ressources/logs/$html_file",0755);
@chmod("/usr/share/artica-postfix/ressources/logs/" . str_replace("-",".",$html_file),0755);

}
	
	




	
function postfixlogger_progress(){
	
	
	$user=new usersMenus();
	if(is_file("/usr/share/artica-postfix/ressources/logs/postfix-logger.ini")){
		$ini=new Bs_IniHandler("/usr/share/artica-postfix/ressources/logs/postfix-logger.ini");
	}else{
		$ini=new Bs_IniHandler();
	}
	$max=$ini->get("PROGRESS","max");
	$current=$ini->get("PROGRESS","current");
	$type=$ini->get("PROGRESS","type");
	$endtime=$ini->get("PROGRESS","time");
	if($current==null){$current=0;}
	if($type==null){$type="{scheduled}";}
	if($endtime==null){$endtime=date("h:i:s");}
	
	
	if($max<>null){
		$pourc=round($current/$max,2)*100;
	}else{
		$pourc="0";
	}
		$queue=$user->POSTFIX_QUEUE["RTM"];
 		$endtime=str_replace("Y-m-d","",$endtime);

$color="#5DD13D";
	
$html="
<table style='width:100%'>
<tr>
<td valign='top'>
<table style='width:100%'>
	<tr>
		<td align='center' colspan=2><strong style='font-size:11px'>&laquo;$type&raquo;&nbsp;$current/$max/$queue ({end}:$endtime) </td>
	</tr>
		
	<tr>
		<td width=1%><span id='wait'>$img</span>
		</td>
		<td width=99%>
			<table style='width:100%'>
			<tr>
			<td>
				<div style='width:100%;background-color:white;padding-left:0px;border:1px solid $color'>
					<div id='progression_postfix'>
						<div style='width:{$pourc}%;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:$color'>
							<strong style='color:#BCF3D6;font-size:12px;font-weight:bold'>{$pourc}%</strong></center>
						</div>
					</div>
				</div>
			</td>
			</tr>
			</table>		
		</td>
	</tr>
	</table>
</td>
<td valign='top' width=1%>
	<div style='padding:2px;border:1px solid #CCCCCC'>" . imgtootltip("rouage-32.png","{parameters}","Loadjs('RTMMailConfig.php')")."</div>
</td>
<td valign='top' width=1%>
	<div style='padding:2px;border:1px solid #CCCCCC'>" . imgtootltip("loupe-32.png","{detach}","Loadjs('RTMMail.php')")."</div>
</td>



</tr>
</table>";	
return $html;
	
	
	
	
}

function UpdateDiscards(){
	$q=new mysql();
	$sql="UPDATE smtp_logs SET bounce_error='Error' WHERE bounce_error LIKE '%4.7.1 Service unavailable%'";
	$q->QUERY_SQL($sql,"artica_events");
	$sql="UPDATE smtp_logs SET bounce_error='Error' WHERE bounce_error LIKE '%4.5.0 Failure%'";
	$q->QUERY_SQL($sql,"artica_events");
	$sql="UPDATE smtp_logs SET bounce_error='Content scanner malfunction' WHERE bounce_error LIKE '%4.6.0 Content scanner malfunction%'";
	$q->QUERY_SQL($sql,"artica_events");
	$sql="UPDATE smtp_logs SET bounce_error='Sended' WHERE LENGTH(bounce_error)=0";
	$q->QUERY_SQL($sql,"artica_events");
	$sql="UPDATE smtp_logs SET bounce_error='Mailbox unknown' WHERE bounce_error LIKE '%mailbox unavailable%'";
	$q->QUERY_SQL($sql,"artica_events");
	$sql="UPDATE smtp_logs SET bounce_error='DNS Error' WHERE bounce_error LIKE '%delivery temporarily suspended: Host or domain name not found%'";
	$q->QUERY_SQL($sql,"artica_events");	
	
	
	
	
}


function RTMevents($text){
		$f=new debuglogs();
		$f->events(basename(__FILE__)." $text","/var/log/artica-postfix/artica-status.debug");
		}
		

function UpdateGeoip(){
	$database="/usr/share/GeoIP/GeoIP.dat";

if(!is_file($database)){
		RTMevents("Unable to stat /usr/local/share/GeoIP/GeoIP.dat");
		installgeoip();
		return null;
		}
if(!is_file("/usr/local/share/GeoIP/GeoIPCity.dat")){
	if(is_file("/usr/local/share/GeoIP/GeoLiteCity.dat")){
		RTMevents("Linking /usr/local/share/GeoIP/GeoLiteCity.dat");
		system('/bin/ln -s /usr/local/share/GeoIP/GeoLiteCity.dat /usr/local/share/GeoIP/GeoIPCity.dat >/dev/null 2>&1');
	}
}

if(!is_file("/usr/share/GeoIP/GeoIPCity.dat")){
	if(is_file("/usr/share/GeoIP/GeoLiteCity.dat")){
		RTMevents("Linking /usr/share/GeoIP/GeoLiteCity.dat");
		system('/bin/ln -s /usr/share/GeoIP/GeoLiteCity.dat /usr/share/GeoIP/GeoIPCity.dat >/dev/null 2>&1');
	}
}

if(!function_exists("geoip_record_by_name")){
	RTMevents("Unable to sat geoip_record_by_name() function");
	installgeoip();
	return null;
}

$db_info=geoip_database_info(GEOIP_COUNTRY_EDITION);
RTMevents("Using $db_info");
$q=new mysql();
$sql="SELECT smtp_sender  FROM `smtp_logs` WHERE LENGTH(smtp_sender)>0 AND smtp_sender!='127.0.0.1' AND (Country IS NULL or Country='undefined')";

$results=$q->QUERY_SQL($sql,"artica_events");

if(!$q->ok){
		RTMevents("Wrong sql query $q->mysql_error");
		return null;
	}
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$smtp_sender=$ligne["smtp_sender"];
		if($_GET["smtp_cache"][$smtp_sender]){continue;}
		$record = geoip_record_by_name($smtp_sender);
		if (!$record) {
			RTMevents("unable to locate this IP $smtp_sender");
			$_GET["smtp_cache"][$smtp_sender]=true;
			continue;
		}
		
		$Country=$record["country_name"];
		$_GET["smtp_cache"][$smtp_sender]=true;
		RTMevents("$smtp_sender =  $Country");	
		updategeo($smtp_sender,$Country);		
		
		
	}
	
}

function updategeo($ip,$country){
	$sql="UPDATE smtp_logs SET Country='$country' WHERE smtp_sender='$ip'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_events");
	
}


function installgeoip(){
	
		if(is_file('/usr/bin/pecl')){
			if(!is_file("/etc/artica-postfix/php-geoip-checked")){
			system('/usr/share/artica-postfix/bin/setup-ubuntu --check-base-system');
			system('/usr/bin/pecl install geoip');
			system('/etc/init.d/artica-postfix restart apache');
			system('/bin/touch /etc/artica-postfix/php-geoip-checked');
			}
		}	
	
}



?>