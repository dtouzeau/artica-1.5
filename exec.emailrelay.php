<?php
include_once(dirname(__FILE__) . '/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.templates.inc');


if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;$GLOBALS["debug"]=true;}

if($argv[1]=="--corrupted"){corrupted_file($argv[2]);exit;}
if($argv[1]=="--notifier-queue"){clean_notifier_queue("/var/spool/artica-notifier");emailing_emailrelay_clean();exit;}
if($argv[1]=="--emailrelays-emailing"){build_email_relays();exit;}
if($argv[1]=="--emailing-ou-status"){emailing_ou_status($argv[2]);exit;}
if($argv[1]=="--emailing-remove"){emailing_remove($argv[2]);exit;}
if($argv[1]=="--start"){build_email_relays_start();exit;}
if($argv[1]=="--stop"){emailing_emailrelay_stop_all();exit;}


@unlink("/etc/artica-postfix/emailrelay.cmd");
$path="/etc/artica-postfix/smtpnotif.conf";
	if(!file_exists($path)){
		echo "Starting......: SMTP notifier unable to stat $path\n";
		return null;
	}
	
	
$ini=new Bs_IniHandler($path);
$smtp_server_name=$ini->_params["SMTP"]["smtp_server_name"];
$smtp_server_port=$ini->_params["SMTP"]["smtp_server_port"];
$smtp_auth_user=$ini->_params["SMTP"]["smtp_auth_user"];
$smtp_auth_passwd=$ini->_params["SMTP"]["smtp_auth_passwd"];
$tls_enabled=$ini->_params["SMTP"]["tls_enabled"];

$smtp_server_name=trim($smtp_server_name);
$smtp_server_port=trim($smtp_server_port);
if(trim($smtp_server_name)==null){$smtp_server_name="127.0.0.1";}
if(!is_numeric($smtp_server_port)){$smtp_server_port="25";}



$params[]="--close-stderr --no-smtp --poll=5 --syslog --pid-file=/var/run/artica-notifier.pid --spool-dir=/var/spool/artica-notifier --log";
$params[]="--connection-timeout=10";
$params[]="--forward-to $smtp_server_name:$smtp_server_port";

echo "Starting......: SMTP notifier using remote smtp $smtp_server_name:$smtp_server_port\n";

if(trim($smtp_auth_user)<>null){
	echo "Starting......: SMTP notifier authentication enabled\n";
	$params[]="--client-auth /etc/artica-postfix/notification.auth";
	$cltauth[]="LOGIN client $smtp_auth_user $smtp_auth_passwd";
	@file_put_contents("/etc/artica-postfix/notification.auth",@implode("\n",$cltauth));
}

if($tls_enabled==1){
	echo "Starting......: SMTP notifier SSL enabled\n";
	$params[]="--client-tls";
}

@file_put_contents("/etc/artica-postfix/emailrelay.cmd",@implode(" ",$params));
@mkdir("/var/spool/artica-notifier",0666,true);


function clean_notifier_queue($queue_path){
	
	writelogs("verify $queue_path",__FUNCTION__,__FILE__,__LINE__);
	$unix=new unix();
	foreach (glob("$queue_path/*.envelope.bad") as $filename) {
		$basename=basename($filename);
    	$time=$unix->file_time_min($filename);
    	
    	echo "$filename ($time mn)\n";
    	writelogs("verify $basename ($time mn)",__FUNCTION__,__FILE__,__LINE__);
    	if($time>86400){
    		$enveloppe=str_replace(".envelope.bad",".content",$filename);
    		$enveloppe_basename=basename($enveloppe);
    		writelogs("delete $basename,$enveloppe_basename",__FUNCTION__,__FILE__,__LINE__);
    		echo "$filename,$enveloppe =delete ";
    		@unlink($filename);
    		@unlink($enveloppe);
    		$deleted[]="$filename {$time}mn exceed 8640mn";
    		continue;
    	}
		
    	$newnevelopper=str_replace(".envelope.bad",".envelope",$filename);
    	writelogs("rename $basename,$newnevelopper",__FUNCTION__,__FILE__,__LINE__);
    	rename($filename,$newnevelopper);
    	
	}
	
	foreach (glob("$queue_path/*.envelope.busy") as $filename) {
    	
		$basename=basename($filename);
		$time=$unix->file_time_min($filename);
    	
    	writelogs("verify $basename ($time mn)",__FUNCTION__,__FILE__,__LINE__);
    	if($time>86400){
    		$enveloppe=str_replace(".envelope.busy",".content",$filename);
    		$enveloppe_basename=basename($enveloppe);
    		writelogs("delete $basename,$enveloppe_basename",__FUNCTION__,__FILE__,__LINE__);
    		@unlink($filename);
    		@unlink($enveloppe);
    		$deleted[]="$filename {$time}mn exceed 8640mn";
    		continue;
    	}
    
	}

	
	if(count($deleted)>0){send_email_events(count($deleted)." emails deleted in emailrelay queue",@implode("\n",$deleted),"system");}
	
}


function emailing_emailrelay_clean(){
	
	$sock=new sockets();
	$EnableInterfaceMailCampaigns=$sock->GET_INFO("EnableInterfaceMailCampaigns");
	if($EnableInterfaceMailCampaigns<>1){return;}	
	
	writelogs("verify /var/spool/artica-emailing/queues",__FUNCTION__,__FILE__,__LINE__);
	foreach (glob("/var/spool/artica-emailing/parameters/*.params") as $filename) {
		$basename=basename($filename);
		if(preg_match("#([0-9]+)\.params#",$basename,$re)){
			clean_notifier_queue("/var/spool/artica-emailing/queues/{$re[1]}");
		}
	}	
}


function emailing_emailrelay_stop_all(){
	$unix=new unix();
	$emailrelay=$unix->find_program("emailrelay");
	$pgrep=$unix->find_program("pgrep");
	$emailrelay=str_replace("/","\/",$emailrelay);
	exec("pgrep -l -f \"$emailrelay.+?\/var\/spool\/artica-emailing\" 2>&1",$results);
	while (list ($num, $val) = each ($results) ){
		if(preg_match("#^([0-9]+)\s+(.+)#",$val,$re)){
			echo "Stopping emailrelay.....: PID  {$re[1]}\n"; 
			shell_exec("/bin/kill {$re[1]}");
		}
	}
}


function build_email_relays_start(){
	
	$sock=new sockets();
	$EnableInterfaceMailCampaigns=$sock->GET_INFO("EnableInterfaceMailCampaigns");
	if($EnableInterfaceMailCampaigns<>1){return;}
foreach (glob("/var/spool/artica-emailing/parameters/*.params") as $filename) {
		$parameters=unserialize(@file_get_contents($filename));
		$basename=basename($filename);
		$smtp_server_name=$parameters["smtp_server_name"];
		$smtp_server_port=$parameters["smtp_server_port"];
		$ou=base64_decode($parameters["ou"]);
		if(preg_match("#([0-9]+)\.params#",$basename,$re)){
			$ID=$re[1];
		}
		
		if($GLOBALS["VERBOSE"]){echo "$basename:pid=/var/spool/artica-emailing/run/$ID.pid\n";};
		emailing_emailrelay_start($ID);
	}	
}




function build_email_relays(){
	
	$sql="SELECT * FROM emailing_mailers ORDER BY ID DESC";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	
	@mkdir("/var/spool/artica-emailing",0666,true);
	@mkdir("/var/spool/artica-emailing/parameters",0666,true);
	@mkdir("/var/spool/artica-emailing/queues",0666,true);
	@mkdir("/var/spool/artica-emailing/run",0666,true);
	if(!$q->ok){
		reload_emailrelays();
		return;
	}
	
	foreach (glob("/var/spool/artica-emailing/parameters/*.params") as $filename) {
		echo "Starting......: remove old configuration $filename\n";
		@unlink($filename);
	}
	
	$sock=new sockets();
	$EnableInterfaceMailCampaigns=$sock->GET_INFO("EnableInterfaceMailCampaigns");
	if($EnableInterfaceMailCampaigns<>1){return;}	
	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$ou=$ligne["ou"];
		$ID=$ligne["ID"];
		$parameters=base64_decode($ligne["parameters"]);
		if($GLOBALS["VERBOSE"]){echo "writing parameters for /var/spool/artica-emailing/parameters/$ID.params\n";}
		@file_put_contents("/var/spool/artica-emailing/parameters/$ID.params",$parameters);
	}
	reload_emailrelays();
	
}

function reload_emailrelays(){
	emailing_emailrelay_stop_all();
	foreach (glob("/var/spool/artica-emailing/parameters/*.params") as $filename) {
		$parameters=unserialize(@file_get_contents($filename));
		$basename=basename($filename);
		$smtp_server_name=trim($parameters["smtp_server_name"]);
		$smtp_server_port=trim($parameters["smtp_server_port"]);
		$ou=base64_decode($parameters["ou"]);
		if(preg_match("#([0-9]+)\.params#",$basename,$re)){
			$ID=$re[1];
		}
		
		if($GLOBALS["VERBOSE"]){echo "$basename:pid=/var/spool/artica-emailing/run/$ID.pid\n";};
		emailing_emailrelay_stop($ID);
		emailing_emailrelay_start($ID);
	}
}


function emailing_emailrelay_stop($ID){
	$pidpath="/var/spool/artica-emailing/run/$ID";
	$unix=new unix();
	if(!$unix->process_exists($unix->get_pid_from_file($pidpath))){
		echo "Stopping emailrelay.....: Instance $ID already stopped\n";
		return; 
		
	}
	
	$pid=$unix->get_pid_from_file($pidpath);
	shell_exec("/bin/kill $pid");
	for($i=0;$i<10;$i++){
		sleep(1);
		if(!$unix->process_exists($unix->get_pid_from_file($pidpath))){
			break;
		}		
	}
	
	if(!$unix->process_exists($unix->get_pid_from_file($pidpath))){
		echo "Stopping emailrelay.....: Instance $ID success\n";
	}else{
		echo "Stopping emailrelay.....: Instance $ID Failed\n";
	}
	
}

function emailing_emailrelay_status($ID){
	$unix=new unix();
	$pidpath="/var/spool/artica-emailing/run/$ID";
	$master_pid=$unix->get_pid_from_file($pidpath);
	
	$parameters=unserialize(@file_get_contents("/var/spool/artica-emailing/parameters/$ID.params"));
	$smtp_server_name=$parameters["smtp_server_name"];
	$smtp_server_port=$parameters["smtp_server_port"];
	if($smtp_server_port==null){$smtp_server_port=25;}
	
	$l[]="[APP_EMAILRELAY_$ID]";
	$l[]="service_name=APP_EMAILRELAY";
	$l[]="master_version=".$unix->GetVersionOf("emailrelay");
	$l[]="service_cmd=artica-notifier";	
	$l[]="service_disabled=1";
	$l[]="pid_path=$pidpath";
	$l[]="queue_num=".emailing_queues_count($ID);
	$l[]="change-name=$smtp_server_name:$smtp_server_port";
	if(!$unix->process_exists($master_pid)){$l[]="running=0\ninstalled=1";$l[]="";echo implode("\n",$l);return;}	
	$l[]="running=1";
	$l[]=$unix->GetMemoriesOf($master_pid);
	$l[]="";
	return implode("\n",$l);return;
	
	
	
}

function emailing_queues_count($ID){
	
	
	$items = glob("/var/spool/artica-emailing/queues/$ID/*.envelope");
	return count($items);
}

function emailing_emailrelay_config_mysql($ID){
		if(!is_numeric($ID)){return null;}
		if($ID<1){return null;}
		$q=new mysql();
		$sql="SELECT * FROM emailing_mailers WHERE ID=$ID";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		if(!$q->ok){
			echo "Starting......: Instance $ID $q->mysql_error\n";
			return;
		}	
		$datas=unserialize(base64_decode($ligne["parameters"]));
		@mkdir("/var/spool/artica-emailing/parameters",666,true);
		@file_put_contents("/var/spool/artica-emailing/parameters/$ID.params",serialize($datas));
		echo "Starting......: Instance $ID build configuration from mysql done\n";
	
	
}


function emailing_emailrelay_start($ID){
	$pidpath="/var/spool/artica-emailing/run/$ID";
	$unix=new unix();
	if($unix->process_exists($unix->get_pid_from_file($pidpath))){
		echo "Starting......: Instance $ID already running\n";
		return; 
		
	}
	emailing_emailrelay_config_mysql($ID);
	$config=emailing_emailrelay_config($ID);
	$unix=new unix();
	$binpath=$unix->find_program("emailrelay");
	echo "Starting......: Instance $ID \"$binpath $config\"\n";
	shell_exec("$binpath $config");
	
	for($i=0;$i<10;$i++){
		sleep(1);
		if($unix->process_exists($unix->get_pid_from_file($pidpath))){
			break;
		}		
	}
	
	if($unix->process_exists($unix->get_pid_from_file($pidpath))){
		write_syslog("[$ID]: success started emairelay",basename(__FILE__));
		echo "Starting......: Instance $ID success PID ". $unix->get_pid_from_file($pidpath)."\n";
	}else{
		echo "Starting......: Instance $ID failed\n";
		echo "$binpath $config\n";
	}
	

	
	
}

function emailing_remove($ID){
	if($ID<1){return;}
	emailing_emailrelay_stop($ID);
	@unlink("/var/spool/artica-emailing/parameters/$ID.params");
	@unlink("/var/spool/artica-emailing/run/$ID");
	shell_exec("/bin/rm -rf /var/spool/artica-emailing/queues/$ID");
	
}


function emailing_emailrelay_config($ID){
	$parameters=unserialize(@file_get_contents("/var/spool/artica-emailing/parameters/$ID.params"));
	
	$smtp_server_name=trim($parameters["smtp_server_name"]);
	$smtp_server_port=trim($parameters["smtp_server_port"]);	
	if(!is_numeric($smtp_server_port)){$smtp_server_port="25";}
	$smtp_auth_user=$parameters["smtp_auth_user"];
	$smtp_auth_passwd=$parameters["smtp_auth_passwd"];
	$tls_enabled=$parameters["tls_enabled"];
	if(trim($smtp_server_name)==null){
		echo "Starting......: emailrelay no smtp server\n";
		return;}
	
	@mkdir("/var/spool/artica-emailing/queues/$ID",0666,true);
	
	$params[]="--close-stderr --no-smtp --poll=5 --syslog --pid-file=/var/spool/artica-emailing/run/$ID ";
	$params[]="--spool-dir=/var/spool/artica-emailing/queues/$ID";
	$params[]="--connection-timeout=10";
	$params[]="--forward-to $smtp_server_name:$smtp_server_port";

	echo "Starting......: emailrelay instance $ID using remote smtp $smtp_server_name:$smtp_server_port\n";

	if(trim($smtp_auth_user)<>null){
		echo "Starting......: emailrelay instance $ID PLAIN authentication $smtp_auth_userenabled@$smtp_server_name\n";
		$params[]="--client-auth /etc/artica-postfix/notification.$ID.auth";
		$cltauth[]="PLAIN client $smtp_auth_user $smtp_auth_passwd\n";
		@file_put_contents("/etc/artica-postfix/notification.$ID.auth",@implode("\n",$cltauth));
	}	
	
	echo "Starting......: emailrelay TLS:$tls_enabled\n";
	if($tls_enabled==1){
	echo "Starting......: instance $ID SSL enabled\n";
	$params[]="--client-tls";
	}
	$params[]="--log";
	$conf=@implode(' ',$params);
	return $conf;
}
function emailing_ou_status($ou){
	$ou=base64_decode($ou);
	if($GLOBALS["VERBOSE"]){echo "search $ou\n";}
	foreach (glob("/var/spool/artica-emailing/parameters/*.params") as $filename) {
		$parameters=unserialize(@file_get_contents($filename));
		$basename=basename($filename);
		
		$ou_params=base64_decode($parameters["ou"]);
		if($GLOBALS["VERBOSE"]){echo "$basename ($ou_params)\n";}
		
		if($ou_params<>$ou){
			if($GLOBALS["VERBOSE"]){echo("$ou_params < > $ou\n");}
			continue;}
		if(!preg_match("#([0-9]+)\.params#",$basename,$re)){continue;}
		
		$ID=$re[1];
		if($GLOBALS["VERBOSE"]){echo "emailing_emailrelay_status($ID)\n";}
		$conf[]=emailing_emailrelay_status($ID);
	}
	
	echo @implode("\n",$conf);
}


function corrupted_file($filesource){
	if(preg_match("#\/var\/spool\/artica-emailing\/queues\/([0-9]+)\/emailrelay\.#",$filesource,$re)){
		$ID=$re[1];
	}
	
	if($GLOBALS["VERBOSE"]){echo "corrupted_file:: ID:$ID\n";}
	if(strpos($filesource,"envelope.busy")>0){$replace_source="envelope.busy";}
	if(strpos($filesource,"envelope.bad")>0){$replace_source="envelope.bad";}
	if($GLOBALS["VERBOSE"]){echo "corrupted_file:: replace:$replace_source\n";}
	$enveloppe=str_replace("$replace_source","envelope",$filesource);
	$content=str_replace("$replace_source","content",$filesource);
	if($GLOBALS["VERBOSE"]){echo "corrupted_file:: enveloppe:$enveloppe\n";}
	if($GLOBALS["VERBOSE"]){echo "corrupted_file:: content..:$content\n";}
	
	$f=explode("\n",@file_get_contents($enveloppe));
	if(is_array($f)){
		while (list ($num, $line) = each ($f) ){
			if(preg_match("#X-MailRelay-From:\s+(.+?)$#",$line,$re)){$mailfrom=trim($re[1]);}
			if(preg_match("#X-MailRelay-To-Remote:\s+(.+?)$#",$line,$re)){$mailto=trim($re[1]);}
		}
		
	}
	
	$basename=basename($enveloppe);
	if($GLOBALS["VERBOSE"]){echo "corrupted_file:: $basename from=<$mailfrom>\n";}
	if($GLOBALS["VERBOSE"]){echo "corrupted_file:: $basename to=<$mailto>\n";}
	
	@unlink($enveloppe);
	@unlink($content);
	
	
	write_syslog("[$ID]: Corrupted file $basename from $mailfrom to $mailto",basename(__FILE__));
	
	if(is_numeric($ID)){if($ID>0){
		send_email_events("[emailing instance $ID]: Corrupted file $basename from $mailfrom to $mailto","files $enveloppe\n$content has been removed from queue","system");
		emailing_emailrelay_start($ID);
	
	}}
	//;
	
}




?>