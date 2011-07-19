<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.viptrack.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.sockets.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');


if(preg_match("#--verbose#",@implode(" ",$argv))){
	$GLOBALS["VERBOSE"]=true;$GLOBALS["debug"]=true;$GLOBALS["DEBUG"]=true;
	ini_set('html_errors',0);
	ini_set('display_errors', 1);
	ini_set('error_reporting', E_ALL);
	}


$sock=new sockets();
$unix=new unix();
if($sock->GET_INFO("EnableVIPTrack")<>1){
	if($GLOBALS["VERBOSE"]){echo "EnableVIPTrack is not enabled, aborting\n";}
	die();
}

if($argv[1]=="--report"){report($argv[2]);die();}
if($argv[1]=="--reports"){$GLOBALS["FORCE"]=true;BuildReports();die();}


$tmpfile="/etc/artica-postfix/pids/".basename(__FILE__).".pid";
$pid=@file_get_contents($tmpfile);
if($unix->process_exists($pid)){
	if($GLOBALS["VERBOSE"]){echo "EnableVIPTrack already process $pid running, aborting\n";}
	die();
}

@file_put_contents($tmpfile,getmypid());

	
if($argv[1]=="--query"){query();die();}
if($argv[1]=="--query2"){query2();die();}
if($argv[1]=="--queue"){PostQueue();die();}

PostQueue();
query();
query2();
CleanTables();


function CleanTables(){
	$unix=new unix();
	$tmpfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".time";
	$time=$unix->file_time_min($tmpfile);
	if($time>240){
		@unlink($tmpfile);
		@file_put_contents($tmpfile,"#");
	}else{
		return;
	}
	
	$q=new mysql();
	$sql="OPTIMIZE TABLE viptrack_connections";
	$q->QUERY_SQL($sql,"artica_events");
	$sql="OPTIMIZE TABLE viptrack_content";
	$q->QUERY_SQL($sql,"artica_events");	
	
	
}

function BuildReports(){
	$sock=new sockets();
	$unix=new unix();
	if(!$GLOBALS["FORCE"]){
		$VIPTrackReportEach=$sock->GET_INFO("VIPTrackReportEach");	
		if(!is_numeric($VIPTrackReportEach)){$VIPTrackReportEach=24*60;}
		$tmpfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".time";
		$time=$unix->file_time_min($tmpfile);
		if($time<$VIPTrackReportEach){return;}
		@unlink($time);
		@file_put_contents($tmpfile,"#");
	}
	
	
	$sql="SELECT email FROM postfix_viptrack WHERE `enabled`=1 ORDER BY email";
	$q=new mysql();	
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){if($GLOBALS["VERBOSE"]){echo $q->mysql_error;}$unix->send_email_events("VipTrack failed MySQL Error ".__LINE__,$q->mysql_error,"postfix");return;}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($ligne["email"]==null){continue;}
		$vip=new viptrack($ligne["email"]);
	}
				
	
	
}





function report($email){
	$unix=new unix();
	if($email=="all"){
		if($GLOBALS["VERBOSE"]){
			$sql="SELECT email FROM postfix_viptrack WHERE `enabled`=1 ORDER BY email";
			$q=new mysql();	
			$results=$q->QUERY_SQL($sql,"artica_backup");
			if(!$q->ok){if($GLOBALS["VERBOSE"]){echo $q->mysql_error;}$unix->send_email_events("VipTrack failed MySQL Error ".__LINE__,$q->mysql_error,"postfix");return;}
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				if($ligne["email"]==null){continue;}
				echo "\n\n----------------------------------------------------\n";
				$vip=new viptrack($ligne["email"]);
			}
			
			return;
		}
	}
	
	$vip=new viptrack($email);
	
}


function PostQueue(){
	
	$sock=new sockets();
	$VIPTrackQueueTimeOut=$sock->GET_INFO("VIPTrackQueueTimeOut");
	$VIPTrackQueueMinTime=$sock->GET_INFO("VIPTrackQueueMinTime");
	if(!is_numeric($VIPTrackQueueTimeOut)){$VIPTrackQueueTimeOut=15;}
	if(!is_numeric($VIPTrackQueueMinTime)){$VIPTrackQueueMinTime=120;}
	if($VIPTrackQueueMinTime<$VIPTrackQueueTimeOut){$VIPTrackQueueMinTime=$VIPTrackQueueTimeOut;}	
	
	if($GLOBALS["VERBOSE"]){echo "Timeout is set to $VIPTrackQueueTimeOut\n";}
	
	$unix=new unix();
	if(!$GLOBALS["VERBOSE"]){
		$tmpfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".time";
		$time=$unix->file_time_min($tmpfile);
		if($time>$VIPTrackQueueTimeOut){
			@unlink($tmpfile);
			@file_put_contents($tmpfile,"#");
		}else{
			return;
		}	
	}
	

	
	$cti=0;
	$sql="SELECT email FROM postfix_viptrack WHERE `enabled`=1 ORDER BY email";
	$q=new mysql();	
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		if($GLOBALS["VERBOSE"]){echo $q->mysql_error;}
		$unix->send_email_events("VipTrack failed MySQL Error ".__LINE__,$q->mysql_error,"postfix");
		return;
		
	}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($ligne["email"]==null){continue;}
		$array_inbound[]="(`recipients` LIKE '%{$ligne["email"]}%')";
		$array_outbound[]="(`from`='{$ligne["email"]}')";
	}	
	
	$rquested[]="`msgid`";
	$rquested[]="`instance`";
	$rquested[]="`zDate`";
	$rquested[]="`from`";
	$rquested[]="`recipients`";
	$rquested[]="`context`";
	$rquested[]="`event`";
	$rquested[]="`removed`";
	$rquested[]="`from_domain`";
	$rquested[]="`size`";
	
	
	$sql_inbound ="SELECT ".@implode(",",$rquested)." FROM postqueue WHERE 1 AND (". @implode(" OR ",$array_inbound).") ORDER BY zDate";
	$sql_outbound="SELECT ".@implode(",",$rquested)." FROM postqueue WHERE 1 AND (". @implode(" OR ",$array_outbound).") ORDER BY zDate";	
			
	$results=$q->QUERY_SQL($sql_inbound,"artica_events");
	if(!$q->ok){
		if($GLOBALS["VERBOSE"]){echo $q->mysql_error;}
		$unix->send_email_events("VipTrack failed MySQL Error",$q->mysql_error."\n$sql","postfix");
		return;	
	}	
	
	if(mysql_num_rows($results)>0){
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				mb_internal_encoding("UTF-8");
				$ligne["subject"] = mb_decode_mimeheader($ligne["subject"]); 
				$time=strtotime($ligne["zDate"]);
				$ligne["zDate"]=date("l d F H:i:s",$time);		
				$ligne["subject"]=GetSubjectMessage($ligne["msgid"]);			
				$detected[]="{$ligne["msgid"]}: stored since {$ligne["zDate"]} {$ligne["from"]} to {$ligne["recipients"]} {$ligne["subject"]}\n{$ligne["event"]}\n----------------------------------\n\n";
				
			}	
	}
	
	$results=$q->QUERY_SQL($sql_outbound,"artica_events");
	if(!$q->ok){
		if($GLOBALS["VERBOSE"]){echo $q->mysql_error;}
		$unix->send_email_events("VipTrack failed MySQL Error",$q->mysql_error."\n$sql","postfix");
		return;	
	}	
	
	if(mysql_num_rows($results)>0){
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				$ligne["subject"]=GetSubjectMessage($ligne["msgid"]);
				$time=strtotime($ligne["zDate"]);
				$ligne["zDate"]=date("l d F H:i:s",$time);
				$detected[]="{$ligne["msgid"]}: stored since {$ligne["zDate"]} {$ligne["from"]} to {$ligne["recipients"]} {$ligne["subject"]}\n{$ligne["event"]}\n----------------------------------\n\n";
			}	
	}
	

	
	if(count($detected)>0){
		$subject="VIPTrack: ".count($detected)." messages in queue ";
		$text="You will find here the messages list stored on the queue\n".@implode("\n",$detected);
		
		if($GLOBALS["VERBOSE"]){
			echo "\n\n-----------------------------------------------\n$subject\n\n$text\n";
		}else{

			if(!is_dir("/etc/artica-postfix/VipTrack.time")){@mkdir("/etc/artica-postfix/VipTrack.time",666,true);}
			$md5=md5("$subject$text");
			$timefile="/etc/artica-postfix/VipTrack.time/$md5.time";
			$time_file=$unix->file_time_min($timefile);
			if($time_file>$VIPTrackQueueMinTime){
				$unix->send_email_events($subject,$text,"VIPTrack");
				@unlink($timefile);
				@file_put_contents($timefile,"#");
			}
		}
	}
	
	if($GLOBALS["VERBOSE"]){echo "END\n";}
	
}

function GetSubjectMessage($msgid){
	$unix=new unix();
	$postcat=$unix->find_program("postcat");
	if(!is_file($postcat)){return "postcat no such file !";}
	exec("$postcat -qh $msgid 2>&1",$results);
		
	while (list ($index, $line) = each ($results) ){
		if(preg_match("#Subject:(.+)#",$line,$re)){
			mb_internal_encoding("UTF-8");
			$re[1] = mb_decode_mimeheader($re[1]);
			return $re[1]; 		
		}
	}
	

		
	
	
}



function query2(){
	$unix=new unix();
	$cti=0;
	$sql="SELECT email FROM postfix_viptrack WHERE `enabled`=1 ORDER BY email";
	$q=new mysql();	
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		if($GLOBALS["VERBOSE"]){echo $q->mysql_error;}
		$unix->send_email_events("VipTrack failed MySQL Error ".__LINE__,$q->mysql_error,"postfix");
		return;
		
	}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($ligne["email"]==null){continue;}
		$array_inbound[]="(`to`='{$ligne["email"]}')";
		$array_outbound[]="(`from`='{$ligne["email"]}')";
	}

	if(count($array_outbound)==0){if($GLOBALS["VERBOSE"]){echo "No users to check (LINE:".__LINE__.")\n";}return;}	
	
	$rquested[]="`zDate`";
	$rquested[]="`from`";
	$rquested[]="`to`";
	$rquested[]="`subject`";
	$rquested[]="`size`";
	$rquested[]="`bounce_error`";
	$rquested[]="`country`";
	$rquested[]="`from_domain`";
	$rquested[]="`to_domain`";
	$rquested[]="`ipaddr`";	

	$sql="SELECT `zDate` FROM viptrack_content ORDER BY `zDate` DESC LIMIT 0 , 1";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_events"));
	$q_one="1";
	if($ligne["zDate"]<>null){
	if($GLOBALS["VERBOSE"]){echo "No users to check\n";}
	$q_one="`zDate`>'{$ligne["zDate"]}'";}

	$sql_inbound ="SELECT ".@implode(",",$rquested)." FROM amavis_event WHERE $q_one AND (". @implode(" OR ",$array_inbound).") ORDER BY zDate";
	$sql_outbound="SELECT ".@implode(",",$rquested)." FROM amavis_event WHERE $q_one AND (". @implode(" OR ",$array_outbound).") ORDER BY zDate";	
	$prefix="INSERT INTO viptrack_content (`zmd5`,`domain_from`,`domain_to`, `from`,`to`, `zDate`, `bounce_error`,`smtp_sender`,`Country`,`subject`,`size`) VALUES ";

	$results=$q->QUERY_SQL($sql_inbound,"artica_events");
	if(!$q->ok){
		if($GLOBALS["VERBOSE"]){echo $q->mysql_error;}
		$unix->send_email_events("VipTrack failed MySQL Error",$q->mysql_error."\n$sql","postfix");
		return;	
	}	
	
	if(mysql_num_rows($results)>0){
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				$c=array();
				while (list ($a, $b) = each ($ligne)){$c[]=$b;}
				$md5=md5(@implode("",$c));
				$ligne["from_domain"]=strtolower($ligne["from_domain"]);
				$ligne["to_domain"]=strtolower($ligne["to_domain"]);
				$ligne["from"]=strtolower($ligne["from"]);
				$ligne["to"]=strtolower($ligne["to"]);
				$ligne["delivery_user"]=strtolower($ligne["delivery_user"]);
				if($ligne["from"]==null){$ligne["from"]="undisclosed";}
				if($ligne["to"]==null){$ligne["to"]="undisclosed";}
				if($ligne["from_domain"]==null){$ligne["from_domain"]="undisclosed";}
				if($ligne["to_domain"]==null){$ligne["to_domain"]="undisclosed";}
				if($ligne["country"]==null){$tt=GeoIP($ligne["ipaddr"]);$ligne["country"]=$tt[0];}	
				$ligne["subject"]=addslashes($ligne["subject"]);				
				
				
				if(!isset($country[0])){$country[0]="unknown";}
				$sq[]="('$md5','{$ligne["from_domain"]}','{$ligne["to_domain"]}','{$ligne["from"]}','{$ligne["to"]}',
				'{$ligne["zDate"]}','{$ligne["bounce_error"]}','{$ligne["ipaddr"]}','{$ligne["country"]}','{$ligne["subject"]}','{$ligne["size"]}')";
			
			
			if(count($sq)>500){
				$cti=$cti+500;
				if($GLOBALS["VERBOSE"]){echo "AMAVIS INBOUND:$cti\n";}
				$sql=$prefix." ".@implode(",",$sq);
				unset($sq);
				$q->QUERY_SQL($sql,"artica_events");
				if(!$q->ok){
					if($GLOBALS["VERBOSE"]){echo $q->mysql_error;}
					$unix->send_email_events("VipTrack failed MySQL Error  ".__LINE__,$q->mysql_error."\n$sql","postfix");
					return;	
				}
			}
	
		}
		
		if(count($sq)>0){
			$cti=$cti+count($sq);
			if($GLOBALS["VERBOSE"]){echo "AMAVIS INBOUND:$cti\n";}
			$sql=$prefix." ".@implode(",",$sq);
			unset($sq);	
			$q->QUERY_SQL($sql,"artica_events");
			if(!$q->ok){
				if($GLOBALS["VERBOSE"]){echo $q->mysql_error;}
				$unix->send_email_events("VipTrack failed MySQL Error  ".__LINE__,$q->mysql_error."\n$sql","postfix");
				return;	
			}					
		}		
		
	}
	
//----------------------------------------------------------------------------------------------------------------------------------------------	
	
	$results=$q->QUERY_SQL($sql_outbound,"artica_events");
	if(!$q->ok){
		if($GLOBALS["VERBOSE"]){echo $q->mysql_error;}
		$unix->send_email_events("VipTrack failed MySQL Error",$q->mysql_error."\n$sql","postfix");
		return;	
	}	
	
	if(mysql_num_rows($results)>0){
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				$c=array();
				while (list ($a, $b) = each ($ligne)){$c[]=$b;}
				$md5=md5(@implode("",$c));
				$ligne["from_domain"]=strtolower($ligne["from_domain"]);
				$ligne["to_domain"]=strtolower($ligne["to_domain"]);
				$ligne["from"]=strtolower($ligne["from"]);
				$ligne["to"]=strtolower($ligne["to"]);
				$ligne["delivery_user"]=strtolower($ligne["delivery_user"]);
				if($ligne["from"]==null){$ligne["from"]="undisclosed";}
				if($ligne["to"]==null){$ligne["to"]="undisclosed";}
				if($ligne["from_domain"]==null){$ligne["from_domain"]="undisclosed";}
				if($ligne["to_domain"]==null){$ligne["to_domain"]="undisclosed";}
				if($ligne["country"]==null){$tt=GeoIP($ligne["ipaddr"]);$ligne["country"]=$tt[0];}	
				$ligne["subject"]=addslashes($ligne["subject"]);				
				
				
				if(!isset($country[0])){$country[0]="unknown";}
				$sq[]="('$md5','{$ligne["from_domain"]}','{$ligne["to_domain"]}','{$ligne["from"]}','{$ligne["to"]}',
				'{$ligne["zDate"]}','{$ligne["bounce_error"]}','{$ligne["ipaddr"]}','{$ligne["country"]}','{$ligne["subject"]}','{$ligne["size"]}')";
			
			
			if(count($sq)>500){
				$cti=$cti+500;
				if($GLOBALS["VERBOSE"]){echo "AMAVIS OUTBOUND:$cti\n";}
				$sql=$prefix." ".@implode(",",$sq);
				unset($sq);
				$q->QUERY_SQL($sql,"artica_events");
				if(!$q->ok){
					if($GLOBALS["VERBOSE"]){echo $q->mysql_error;}
					$unix->send_email_events("VipTrack failed MySQL Error  ".__LINE__,$q->mysql_error."\n$sql","postfix");
					return;	
				}
			}
	
		}
		
		if(count($sq)>0){
			$cti=$cti+count($sq);
			if($GLOBALS["VERBOSE"]){echo "AMAVIS OUTBOUND:$cti\n";}
			$sql=$prefix." ".@implode(",",$sq);
			unset($sq);	
			$q->QUERY_SQL($sql,"artica_events");
			if(!$q->ok){
				if($GLOBALS["VERBOSE"]){echo $q->mysql_error;}
				$unix->send_email_events("VipTrack failed MySQL Error  ".__LINE__,$q->mysql_error."\n$sql","postfix");
				return;	
			}					
		}		
		
	}	
	
}






function query(){
	$unix=new unix();
	$sql="SELECT email FROM postfix_viptrack WHERE `enabled`=1 ORDER BY email";
	$q=new mysql();	
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		if($GLOBALS["VERBOSE"]){echo $q->mysql_error;}
		$unix->send_email_events("VipTrack failed MySQL Error",$q->mysql_error,"postfix");
		return;
		
	}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($ligne["email"]==null){continue;}
		$array_inbound[]="(delivery_user='{$ligne["email"]}')";
		$array_outbound[]="(sender_user='{$ligne["email"]}')";
	}

	if(count($array_outbound)==0){if($GLOBALS["VERBOSE"]){echo "No users to check (LINE:".__LINE__.")\n";}return;}	
	
	$rquested[]="`time_connect`";
	$rquested[]="`time_stamp`";
	$rquested[]="`sender_domain`";
	$rquested[]="`delivery_domain`";
	$rquested[]="`delivery_user`";
	$rquested[]="`sender_user`";
	$rquested[]="`smtp_sender`";
	$rquested[]="`bounce_error`";
	
	$sql="SELECT `zDate` FROM viptrack_connections ORDER BY `zDate` DESC LIMIT 0 , 1";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_events"));
	$q_one="1";
	if($ligne["zDate"]<>null){
	if($GLOBALS["VERBOSE"]){echo "No users to check\n";}
	$q_one="`time_connect`>'{$ligne["zDate"]}'";}
	
	
	$sql_inbound ="SELECT ".@implode(",",$rquested)." FROM smtp_logs WHERE $q_one AND (". @implode(" OR ",$array_inbound).") ORDER BY time_connect";
	$sql_outbound="SELECT ".@implode(",",$rquested)." FROM smtp_logs WHERE $q_one AND (". @implode(" OR ",$array_outbound).") ORDER BY time_connect";

	$results=$q->QUERY_SQL($sql_inbound,"artica_events");
	if(!$q->ok){
	if($GLOBALS["VERBOSE"]){echo $q->mysql_error;}
		$unix->send_email_events("VipTrack failed MySQL Error",$q->mysql_error."\n$sql","postfix");
		return;	
	}
	
	
			   
	$prefix="INSERT INTO viptrack_connections (`zmd5`,`domain_from`,`domain_to`, `from`,`to`, `zDate`, `bounce_error`,`smtp_sender`,`Country`) VALUES "	;
	$cti=0;
	if(mysql_num_rows($results)>0){
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$c=array();
			while (list ($a, $b) = each ($ligne)){$c[]=$b;}
			$md5=md5(@implode("",$c));
			$ligne["delivery_domain"]=strtolower($ligne["delivery_domain"]);
			$ligne["sender_domain"]=strtolower($ligne["sender_domain"]);
			$ligne["delivery_user"]=strtolower($ligne["delivery_user"]);
			$ligne["sender_user"]=strtolower($ligne["sender_user"]);
			if($ligne["delivery_user"]==null){$ligne["delivery_user"]="undisclosed";}
			if($ligne["sender_user"]==null){$ligne["sender_user"]="undisclosed";}
			if($ligne["delivery_domain"]==null){$ligne["delivery_domain"]="undisclosed";}
			if($ligne["sender_user"]==null){$ligne["sender_user"]="undisclosed";}
			if($ligne["sender_domain"]==null){$ligne["sender_domain"]="undisclosed";}					
			
			$country=GeoIP($ligne["smtp_sender"]);
			if(!isset($country[0])){$country[0]="unknown";}
			$sq[]="('$md5','{$ligne["sender_domain"]}','{$ligne["delivery_domain"]}','{$ligne["sender_user"]}','{$ligne["delivery_user"]}',
			'{$ligne["time_connect"]}','{$ligne["bounce_error"]}','{$ligne["smtp_sender"]}','{$country[0]}')";
			
			if(count($sq)>500){
				$cti=$cti+500;
				if($GLOBALS["VERBOSE"]){echo "INBOUND:$cti\n";}
				$sql=$prefix." ".@implode(",",$sq);
				unset($sq);
				$q->QUERY_SQL($sql,"artica_events");
				if(!$q->ok){
					if($GLOBALS["VERBOSE"]){echo $q->mysql_error;}
					$unix->send_email_events("VipTrack failed MySQL Error  ".__LINE__,$q->mysql_error."\n$sql","postfix");
					return;	
					}
			}
			
		}
		
		if(count($sq)>0){
			$cti=$cti+count($sq);
			if($GLOBALS["VERBOSE"]){echo "INBOUND:$cti\n";}
			$sql=$prefix." ".@implode(",",$sq);
			unset($sq);	
			$q->QUERY_SQL($sql,"artica_events");
			if(!$q->ok){
				if($GLOBALS["VERBOSE"]){echo $q->mysql_error;}
				$unix->send_email_events("VipTrack failed MySQL Error  ".__LINE__,$q->mysql_error."\n$sql","postfix");
				return;	
			}					
		}
		
	}
	
//----------------------------------------------------------------------------------------------------------------------------------------------	
	
	$results=$q->QUERY_SQL($sql_outbound,"artica_events");
	if(!$q->ok){
	if($GLOBALS["VERBOSE"]){echo $q->mysql_error;}
		$unix->send_email_events("VipTrack failed MySQL Error (outbound) ".__LINE__,$q->mysql_error."\n$sql","postfix");
		return;	
	}

	if(mysql_num_rows($results)>0){
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$c=array();
			while (list ($a, $b) = each ($ligne)){$c[]=$b;}
			$md5=md5(@implode("",$c));
			$ligne["delivery_domain"]=strtolower($ligne["delivery_domain"]);
			$ligne["sender_domain"]=strtolower($ligne["sender_domain"]);
			$ligne["delivery_user"]=strtolower($ligne["delivery_user"]);
			$ligne["sender_user"]=strtolower($ligne["sender_user"]);
			$ligne["delivery_user"]=strtolower($ligne["delivery_user"]);
			if($ligne["delivery_user"]==null){$ligne["delivery_user"]="undisclosed";}
			if($ligne["sender_user"]==null){$ligne["sender_user"]="undisclosed";}
			if($ligne["delivery_domain"]==null){$ligne["delivery_domain"]="undisclosed";}
			if($ligne["sender_user"]==null){$ligne["sender_user"]="undisclosed";}
			if($ligne["sender_domain"]==null){$ligne["sender_domain"]="undisclosed";}			
			
			$country=GeoIP($ligne["smtp_sender"]);
			if(!isset($country[0])){$country[0]="unknown";}
			$sq[]="('$md5','{$ligne["sender_domain"]}','{$ligne["delivery_domain"]}','{$ligne["sender_user"]}','{$ligne["delivery_user"]}',
			'{$ligne["time_connect"]}','{$ligne["bounce_error"]}','{$ligne["smtp_sender"]}','{$country[0]}')";
			
			if(count($sq)>500){
				$cti=$cti+500;
				if($GLOBALS["VERBOSE"]){echo "OUTBOUND:$cti\n";}
				$sql=$prefix." ".@implode(",",$sq);
				unset($sq);
				$q->QUERY_SQL($sql,"artica_events");
				if(!$q->ok){
					if($GLOBALS["VERBOSE"]){echo $q->mysql_error;}
					$unix->send_email_events("VipTrack failed MySQL Error (outbound) ".__LINE__,$q->mysql_error."\n$sql","postfix");
					return;	
					}
			}
			
		}
		
		if(count($sq)>0){
			$cti=$cti+count($sq);
			if($GLOBALS["VERBOSE"]){echo "OUTBOUND:$cti\n";}
			$sql=$prefix." ".@implode(",",$sq);
			unset($sq);	
			$q->QUERY_SQL($sql,"artica_events");
			if(!$q->ok){
				if($GLOBALS["VERBOSE"]){echo $q->mysql_error;}
				$unix->send_email_events("VipTrack failed MySQL Error (outbound) ".__LINE__,$q->mysql_error."\n$sql","postfix");
				return;	
			}					
		}
		
	}	
	
	
	
}
function events($text){
		$pid=getmypid();
		$date=date('H:i:s');
		$logFile="/var/log/artica-postfix/postfix-logger.sql.debug";
		$size=filesize($logFile);
		if($size>5000000){unlink($logFile);}
		$f = @fopen($logFile, 'a');
		@fwrite($f, "$date [$pid] VipTrack::$text\n");
		@fclose($f);	
		}


function GeoIP($site_IP){
	if(!function_exists("geoip_record_by_name")){
		if($GLOBALS["VERBOSE"]){echo "geoip_record_by_name no such function\n";}
		return array();
	}
	
	if($site_IP==null){
		events("GeoIP():: $site_IP is Null");
		return array();}
	if(!preg_match("#[0-9]+\.[0-9]+\.[0-9]+#",$site_IP)){
		events("GeoIP():: $site_IP ->gethostbyname()");
		$site_IP=gethostbyname($site_IP);
		events("GeoIP():: $site_IP");
	}
	
if(isset($GLOBALS["COUNTRIES"][$site_IP])){
	if(trim($GLOBALS["COUNTRIES"][$site_IP])<>null){
		return array($GLOBALS["COUNTRIES"][$site_IP],$GLOBALS["CITIES"][$site_IP]);
	}
}
	
	$record = geoip_record_by_name($site_IP);
	if ($record) {
		$Country=$record["country_name"];
		$city=$record["city"];
		$GLOBALS["COUNTRIES"][$site_IP]=$Country;
		$GLOBALS["CITIES"][$site_IP]=$city;
		events("GeoIP():: $site_IP $Country/$city");
		return array($GLOBALS["COUNTRIES"][$site_IP],$GLOBALS["CITIES"][$site_IP]);
	}else{
		$GLOBALS["COUNTRIES"][$site_IP]="unknown";
		$GLOBALS["CITIES"][$site_IP]="unknown";
		events("GeoIP():: $site_IP No record");
		return array("unknown","unknown");
	}
		
	return array("unknown","unknown");
}