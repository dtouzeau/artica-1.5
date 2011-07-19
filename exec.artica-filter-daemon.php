<?php
$GLOBALS["DEBUG_INCLUDES"]=false;
$GLOBALS["LOCAL_DOMAINS"]=array();
$GLOBALS["INTERNAL_FROM"]=array();
$GLOBALS["CACHE"]=array();
$GLOBALS["VERBOSE"]=false;
$GLOBALS["DEBUG"]=false;
$GLOBALS["VERBOSE_MASTER"]=false;
$GLOBALS["LOGON-PAGE"]=false;


define(MSG_QUOTA, "Quota exceed: The user to whom this message was addressed has exceeded " .
    "the allowed account quota. Please resend the message at a later time.");


include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
$__server_listening = true;

//error_reporting(E_ALL);
$pidfile="/etc/artica-postfix/".basename(__FILE__).".pid";
$unix=new unix();
if($unix->process_exists(@file_get_contents($pidfile))){die();}


@file_put_contents("/tmp/".basename(__FILE__).".internal.from",serialize(array()));
set_time_limit(0);
ob_implicit_flush();
declare(ticks = 1);
$sock=new sockets();
$GLOBALS["DebugArticaFilter"]=$sock->GET_INFO("DebugArticaFilter");
PopulateMyDomains();

$pid=start_daemon();
events('Running PID 1) -> '.$pid,"MAIN");
@file_put_contents($pidfile,$pid);


pcntl_signal(SIGTERM,'sig_handler');
pcntl_signal(SIGINT, 'sig_handler');
pcntl_signal(SIGCHLD,'sig_handler');
pcntl_signal(SIGHUP, 'sig_handler');
server_loop("127.0.0.1", 54423);


function server_loop($address, $port){
    GLOBAL $__server_listening;
// AF_UNIX AF_INET
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

    socket_set_nonblock($sock);
    events(count($GLOBALS["LOCAL_DOMAINS"]). " internals domains...",__FUNCTION__,__LINE__);
    events("waiting for clients to connect",__FUNCTION__,__LINE__);

    while ($__server_listening){
        $connection = @socket_accept($sock);
        if ($connection === false){
        	if($GLOBALS["DebugArticaFilter"]==1){events("sleep",__FUNCTION__,__LINE__);}
            usleep(2000000);
        }elseif ($connection > 0){
            handle_client($sock, $connection);
        }else{
            events("error: ".socket_strerror($connection),__FUNCTION__,__LINE__);
            die;
        }
    }
}

function handle_client($ssock, $csock){
    GLOBAL $__server_listening;

    $pid = pcntl_fork();
	events("Fork pid $pid",__FUNCTION__,__LINE__);
	if(!isset($GLOBALS["DebugArticaFilter"])){
		$sock=new sockets();
		$GLOBALS["DebugArticaFilter"]=$sock->GET_INFO("DebugArticaFilter");
		if($GLOBALS["DebugArticaFilter"]==null){$GLOBALS["DebugArticaFilter"]=0;}
	}
			
    if ($pid == -1){
        /* fork failed */
        events("fork failure!",__FUNCTION__,__LINE__);
        die;
    }elseif ($pid == 0){
        /* child process */
        $__server_listening = false;
        socket_close($ssock);
        interact($csock);
        events("Closing connection",__FUNCTION__,__LINE__);
        socket_close($csock);
    }else{
        socket_close($csock);
    }
} 
/*protocol_state=RCPT
protocol_name=SMTP
client_address=192.168.1.240
client_name=unknown
reverse_client_name=unknown
helo_name=toto
sender=ddd@ttt.com
recipient=mense@touzeau.bu
recipient_count=0
queue_id=
instance=241.4cbe0108.a7283.0
size=0
etrn_domain=
stress=
sasl_method=
sasl_username=
sasl_sender=
ccert_subject=
ccert_issuer=
ccert_fingerprint=
encryption_protocol=
encryption_cipher=
encryption_keysize=0
*/

function interact($socket){
	$sock=new sockets();
	//protocol_state=END-OF-MESSAGE,RCPT
	
	// PHP_NORMAL_READ PHP_BINARY_READ
	$buffer=@socket_read($socket,2000,PHP_BINARY_READ);
	if(!isset($GLOBALS["DebugArticaFilter"])){
		
		$GLOBALS["DebugArticaFilter"]=$sock->GET_INFO("DebugArticaFilter");
		if($GLOBALS["DebugArticaFilter"]==null){$GLOBALS["DebugArticaFilter"]=0;}
	}
	$GLOBALS["PostfixNotifyMessagesRestrictions"]=$sock->GET_INFO("PostfixNotifyMessagesRestrictions");
	$GLOBALS["ArticaPolicyFilterMaxRCPTInternalDomainsOnly"]=$sock->GET_INFO("ArticaPolicyFilterMaxRCPTInternalDomainsOnly");
	
	
	if(!is_numeric($GLOBALS["ArticaPolicyFilterMaxRCPTInternalDomainsOnly"])){$GLOBALS["ArticaPolicyFilterMaxRCPTInternalDomainsOnly"]=0;}
	if(!is_numeric($GLOBALS["PostfixNotifyMessagesRestrictions"])){$GLOBALS["PostfixNotifyMessagesRestrictions"]=0;}
    
    
	$tbl=explode("\n",$buffer);
	if($GLOBALS["DebugArticaFilter"]==1){events(count($tbl)." lines",__FUNCTION__,__LINE__);}
	
	
	while (list ($num, $ligne) = each ($tbl) ){
		if(preg_match("#(.+?)=(.+)#",$ligne,$re)){
			if($re[1]=="recipient"){
				$GLOBALS["RECIPIENTS"][]=$re[2];
				if($GLOBALS["DebugArticaFilter"]==1){events("To: {$re[2]}",__FUNCTION__,__LINE__);}
			}
			if($GLOBALS["DebugArticaFilter"]==1){events("{$re[1]}:{$re[2]}",__FUNCTION__,__LINE__);}
			$base[trim($re[1])]=trim($re[2]);
		}
		
	}
	$GLOBALS["BASE"]=$base;
	$GLOBALS["protocol_state"]=$base["protocol_state"];
	if($GLOBALS["DebugArticaFilter"]==1){events("Loading caches",__FUNCTION__,__LINE__);}
	$GLOBALS["LOCAL_DOMAINS"]=unserialize(@file_get_contents("/tmp/".basename(__FILE__).".domains"));
	$GLOBALS["CACHE"]=unserialize(@file_get_contents("/tmp/".basename(__FILE__).".cache"));
	$GLOBALS["INTERNAL_FROM"]=unserialize(@file_get_contents("/tmp/".basename(__FILE__).".internal.from"));
	$GLOBALS["QUEUES"]=unserialize(@file_get_contents("/tmp/".basename(__FILE__).".cache.queues"));
	if(count($GLOBALS["QUEUES"])>500){unset($GLOBALS["QUEUES"]);}
	
	/*if(CheckMAXRcptto()){
		socket_write($socket, "action=451 Sorry, your message has too many recipients\n");
		socket_write($socket, "\n");
		unset($GLOBALS["QUEUES"][$GLOBALS["BASE"]["instance"]]);
		writeMem();	
		return;
		
	}*/
	
	if($GLOBALS["protocol_state"]<>"END-OF-MESSAGE"){
		writeMem();
		socket_write($socket, "action=DUNNO\n");
		socket_write($socket, "\n");	
		return;
		}
	
	if($GLOBALS["DebugArticaFilter"]==1){events("running -> BlockQuotaFrom()",__FUNCTION__,__LINE__);}
	
	if(BlockQuotaFrom($base["sender"],$base["recipient"],$base["size"])){
		if($GLOBALS["DebugArticaFilter"]==1){events("Return 552",__FUNCTION__,__LINE__);}
		socket_write($socket, "action=552 " . MSG_QUOTA  . "\n");
		socket_write($socket, "\n");
		if($GLOBALS["DebugArticaFilter"]==1){events("running -> writeMem()",__FUNCTION__,__LINE__);}
		writeMem();
		if($GLOBALS["DebugArticaFilter"]==1){events("running -> point_events()",__FUNCTION__,__LINE__);}
		point_events($base["queue_id"],$base["client_address"],$base["sender"],$base["recipient"],"Over Quota",$base["size"]);
		if($GLOBALS["DebugArticaFilter"]==1){events("finish",__FUNCTION__,__LINE__);}
		return;
	}
	
	if($GLOBALS["DebugArticaFilter"]==1){events("running -> writeMem()",__FUNCTION__,__LINE__);}
	writeMem();
	socket_write($socket, "action=DUNNO\n");
	socket_write($socket, "\n");
	if($GLOBALS["DebugArticaFilter"]==1){events("running -> point_events()",__FUNCTION__,__LINE__);}
	point_events($base["queue_id"],$base["client_address"],$base["sender"],$base["recipient"],null,$base["size"]);
	if($GLOBALS["DebugArticaFilter"]==1){events("finish",__FUNCTION__,__LINE__);}

}

function CheckMAXRcptto(){
	if($GLOBALS["protocol_state"]<>"RCPT"){
		if($GLOBALS["DebugArticaFilter"]==1){
			events("protocol_state :{$GLOBALS["protocol_state"]}, return",__FUNCTION__,__LINE__);
		}
		return;
	}
	if($GLOBALS["PostfixNotifyMessagesRestrictions"]==0){return;}
	$queue_id=$GLOBALS["BASE"]["instance"];
	$recipient=$GLOBALS["BASE"]["recipient"];
	$GLOBALS["QUEUES"][$GLOBALS["BASE"]["instance"]]["recipients"][]=$recipient;
	@file_put_contents("/tmp/".basename(__FILE__).".cache.queues",serialize($GLOBALS["QUEUES"]));
	if($GLOBALS["DebugArticaFilter"]==1){events("RCPT: $queue_id: ".implode(",",$GLOBALS["QUEUES"][$queue_id]["recipients"]),__FUNCTION__,__LINE__);}
	
	$recipients=$GLOBALS["QUEUES"][$GLOBALS["BASE"]["instance"]]["recipients"];
	
	if($GLOBALS["ArticaPolicyFilterMaxRCPTInternalDomainsOnly"]==0){
		if(count($recipients)>=$GLOBALS["PostfixNotifyMessagesRestrictions"]){
			return true;
		}
		
	}
	
	$c=0;
	while (list ($index, $recipient) = each ($recipients) ){
		if(preg_match("#(.+?)@(.+)#",$recipient,$re)){
			$domain=trim(strtolower($re[2]));
			if($GLOBALS["DebugArticaFilter"]==1){events("$recipient Found $domain local={$GLOBALS["LOCAL_DOMAINS"][$domain]}",__FUNCTION__,__LINE__);}
			if($GLOBALS["LOCAL_DOMAINS"][$domain]){
				if($GLOBALS["DebugArticaFilter"]==1){events("COUNT:: $c) $domain is an internal domain",__FUNCTION__,__LINE__);}
				$c++;
			}
		}
		
		
	}
	
	if($GLOBALS["DebugArticaFilter"]==1){events("$c against {$GLOBALS["PostfixNotifyMessagesRestrictions"]}",__FUNCTION__,__LINE__);}
	
	if($c>=$GLOBALS["PostfixNotifyMessagesRestrictions"]){return true;}
	
	
	
	
	
}



function writeMem(){
	$date=date("Y-m-d");
	while (list ($zdate, $array) = each ($GLOBALS["CACHE"]) ){
		if($zdate<>$date){
			events("Clean cache for day  $zdate",__FUNCTION__,__LINE__);
			unset($GLOBALS["CACHE"][$zdate]);
		}
		
	}
	events("Memory: ".round(((memory_get_usage()/1024)/1000),2) ." Mb".__FUNCTION__,__LINE__);
	@file_put_contents("/tmp/".basename(__FILE__).".cache.queues",serialize($GLOBALS["QUEUES"]));
	@file_put_contents("/tmp/".basename(__FILE__).".cache",serialize($GLOBALS["CACHE"]));
	@file_put_contents("/tmp/".basename(__FILE__).".internal.from",serialize($GLOBALS["INTERNAL_FROM"]));
}


function BlockQuotaFrom($from,$mailto,$size){
	$from=trim(strtolower($from));
	$mailto=trim(strtolower($mailto));
	$date=date("Y-m-d");
	$action=false;
	$log=false;
	
	if($from==$mailto){return false;}
	if(preg_match("#(.+?)@(.+)#",$from,$re)){$domain_from=strtolower(trim($re[2]));}
	
	if(!$GLOBALS["LOCAL_DOMAINS"][$domain_from]){
		events("from=<$from> $domain_from not internal (on ". count($GLOBALS["LOCAL_DOMAINS"]). "domains",__FUNCTION__,__LINE__);
		return false;
	
	}	
	if(!is_array($GLOBALS["INTERNAL_FROM"][$from])){PopulateUser($from);}
	
	if($GLOBALS["INTERNAL_FROM"][$from]["uid"]==null){return false;}
	
	$uid=$GLOBALS["INTERNAL_FROM"][$from]["uid"];
	if($GLOBALS["CACHE"][$date][$uid]["COUNT_MESSAGES"]==null){$GLOBALS["CACHE"][$date][$uid]["COUNT_MESSAGES"]=0;}
	
	
	
	
	if($GLOBALS["INTERNAL_FROM"][$uid]["MaxMailsDay"]>0){
		$log=true;
		$mem_quota=$GLOBALS["CACHE"][$date][$uid]["COUNT_MESSAGES"];
		$mem_quota=$mem_quota+1;
		
		$GLOBALS["CACHE"][$date][$uid]["MESSAGES"][]=date("Y-m-d H:i:s")." => $mailto (" .str_replace("&nbsp;",FormatBytes($size/1024)," ").")";
		if($mem_quota>=$GLOBALS["INTERNAL_FROM"][$uid]["MaxMailsDay"]){$action=true;}
		$GLOBALS["CACHE"][$date][$uid]["COUNT_MESSAGES"]=$mem_quota;
	}
	if($GLOBALS["INTERNAL_FROM"][$uid]["MaxMailDaySize"]>0){
		$log=true;
		$mem_quota=$GLOBALS["CACHE"][$date][$uid]["COUNT_MESSAGES_SIZE"];
		$mem_quota=$mem_quota+$size;
		if($mem_quota>=$GLOBALS["INTERNAL_FROM"][$uid]["MaxMailDaySize"]){$action=true;}
		$GLOBALS["CACHE"][$date][$uid]["COUNT_MESSAGES_SIZE"]=$mem_quota;
	}else{
		$GLOBALS["CACHE"][$date][$uid]["COUNT_MESSAGES_SIZE"]=$size;
	}
	
	if($log){
		events("from=<$from> ($uid) to=<$mailto> notify={$GLOBALS["INTERNAL_FROM"][$uid]["OnlyNotify"]} , quota message(s): {$GLOBALS["CACHE"][$date][$uid]["COUNT_MESSAGES"]}, size:{$GLOBALS["CACHE"][$date][$uid]["COUNT_MESSAGES_SIZE"]}",__FUNCTION__,__LINE__);
	}
	
	if($action){
		if($GLOBALS["INTERNAL_FROM"][$uid]["OnlyNotify"]==1){
			events("Send notification and clean cache",__FUNCTION__,__FILE__);
			$text[]="Messages count : {$GLOBALS["CACHE"][$date][$uid]["COUNT_MESSAGES"]}";
			$text[]="Messages Size : {$GLOBALS["CACHE"][$date][$uid]["COUNT_MESSAGES_SIZE"]}";
			$text[]="-------------------------------";
			$text[]=@implode("\n",$GLOBALS["CACHE"][$date][$uid]["MESSAGES"]);
			send_email_events("$uid exceed SMTP quotas",@implode("\n",$text),"postfix");
			unset($GLOBALS["CACHE"][$date][$uid]);
			return false;
		}
		return true;	
	}else{
		return false;
	}
	
	
}



function PopulateUser($from){
	$ldap=new clladp();
	$uid=$ldap->uid_from_email($from);
	if($uid==null){
		events("<$from> uid:$uid",__FUNCTION__,__LINE__);
		$GLOBALS["INTERNAL_FROM"][$from]["uid"]=null;
		$GLOBALS["INTERNAL_FROM"][$from]["MaxMailsDay"]=0;
		$GLOBALS["INTERNAL_FROM"][$from]["MaxMailDaySize"]=0;
		$GLOBALS["INTERNAL_FROM"][$from]["OnlyNotify"]=0;
		return;
	}
	events("<$from> uid:$uid",__FUNCTION__,__LINE__);
	$sql="SELECT * FROM postfix_sender_quotas WHERE uid='$uid'";
	if($GLOBALS["DebugArticaFilter"]==1){events("$sql",__FUNCTION__,__LINE__);}
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	if(!$q->ok){events("$q->mysql_error",__FUNCTION__,__LINE__);}
	$MaxMailsDay=$ligne["MaxMailsDay"];
	$MaxMailDaySize=$ligne["MaxMailDaySize"];
	$OnlyNotify=$ligne["OnlyNotify"];
	if($MaxMailsDay==null){$MaxMailsDay=0;}
	if($MaxMailDaySize==null){$MaxMailDaySize=0;}
	if($OnlyNotify==null){$OnlyNotify=1;}
	if($MaxMailDaySize>0){$MaxMailDaySize=$MaxMailDaySize*1024;$MaxMailDaySize=$MaxMailDaySize*1000;}
	$GLOBALS["INTERNAL_FROM"][$from]["uid"]=$uid;
	$GLOBALS["INTERNAL_FROM"][$uid]["MaxMailsDay"]=$MaxMailsDay;
	$GLOBALS["INTERNAL_FROM"][$uid]["MaxMailDaySize"]=$MaxMailDaySize;
	$GLOBALS["INTERNAL_FROM"][$uid]["OnlyNotify"]=$OnlyNotify;		
	}


/**
  * Signal handler
  */
function sig_handler($sig){
	
	events("sig_handler:$sig",__FUNCTION__,__LINE__);
    switch($sig){
        case SIGTERM: 
        	exit();
        	break;
        	
        case SIGHUP:
        		events("Refresh settings",__FUNCTION__,__LINE__);
        		@file_put_contents("/tmp/".basename(__FILE__).".internal.from",serialize(array()));;
        		PopulateMyDomains();
        		break;
        	
        case SIGINT:
            exit();
        	break;

        case SIGCHLD:
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

function PopulateMyDomains(){
	unset($GLOBALS["LOCAL_DOMAINS"]);
	$ldap=new clladp();
	$doms=$ldap->hash_get_all_domains();
	while (list ($num, $ligne) = each ($doms) ){
		events("Internal domain: $num",__FUNCTION__,__LINE__);
		$GLOBALS["LOCAL_DOMAINS"][trim(strtolower($num))]=true;
	}
	
	@file_put_contents("/tmp/".basename(__FILE__).".domains",serialize($GLOBALS["LOCAL_DOMAINS"]));
	
}


function point_events($postfix_id=null,$smtp_sender=null,$from=null,$to=null,$error=null,$mailsize=0){
	if($postfix_id==null){
		if($GLOBALS["DebugArticaFilter"]==1){events("postfix_id is null",__FUNCTION__,__LINE__);}
		return;
	}
	@mkdir("/var/log/artica-postfix/RTM",null,true);
	$file="/var/log/artica-postfix/RTM/$postfix_id.msg";
	if($GLOBALS["DebugArticaFilter"]==1){events("Loading monitor file $file",__FUNCTION__,__LINE__);}
	$ini=new Bs_IniHandler($file);
	
	if($GLOBALS["DebugArticaFilter"]==1){events("populate monitor file $file",__FUNCTION__,__LINE__);}
	if($smtp_sender<>null){$ini->set("TIME","smtp_sender",$smtp_sender);}
	
	if($from<>null){$ini->set("TIME","mailfrom",$from);}
	$ini->set("TIME","mailto","$to");
	
	$ini->set("TIME","time_end",date("Y-m-d H:i:s"));
	$ini->set("TIME","message-id",$postfix_id);
	$ini->set("TIME","mailsize",$mailsize);
	
	
	if($error==null){
		$ini->set("TIME","bounce_error","Success");
		$ini->set("TIME","delivery_success","yes");
	}else{
		$ini->set("TIME","bounce_error","$error");
		$ini->set("TIME","delivery_success","no");
	}
	if($GLOBALS["DebugArticaFilter"]==1){events("save monitor file $file",__FUNCTION__,__LINE__);}
	$ini->saveFile($file);	
	
	
}


function events($text,$function,$line=0){
		$pid=@getmypid();
		$date=@date("H:i:s");
		$logFile="/var/log/artica-filter/daemon.log";
		$size=@filesize($logFile);
		if($size>1000000){@unlink($logFile);}
		$f = @fopen($logFile, 'a');
		$text="[$pid] $date $function:: $text (L.$line)\n";
		if($GLOBALS["VERBOSE"]){echo $text;}
		@fwrite($f, $text);
		@fclose($f);	
		}
		
		
?>		