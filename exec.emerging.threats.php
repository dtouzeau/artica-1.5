<?php
include_once(dirname(__FILE__).'/framework/frame.class.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/ressources/class.ccurl.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__).'/ressources/class.system.network.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');

	if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
	if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["DEBUG"]=true;$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}
	if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;}
	

check();
function check(){
	$unix=new unix();
	$oldpid="/etc/artica-postfix/pids/". basename(__FILE__).".pid";
	if($unix->process_exists(@file_get_contents($oldpid))){
		writelogs("Process ".@file_get_contents($oldpid)." already exists",__FUNCTION__,__FILE__);
		die();
	}
	
	@file_put_contents($oldpid,getmypid());
	
	$sock=new sockets();
	$EnableEmergingThreats=$sock->GET_INFO("EnableEmergingThreats");
	if(!is_numeric($EnableEmergingThreats)){$sock->SET_INFO("EnableEmergingThreats",0);$EnableEmergingThreats=0;}
		
	
	
	if($EnableEmergingThreats<>1){
		echo "Starting......: Emerging Threats: Disabled\n";
		@unlink("/usr/share/artica-postfix/ressources/logs/EnableEmergingThreatsBuild.db");
		die();
	}
	
	
	$GLOBALS["iptables"]=$unix->find_program("iptables");
	$GLOBALS["ipset"]=$unix->find_program("ipset");


	
	
	
	if(!is_file($GLOBALS["iptables"])){
		echo "Starting......: Emerging Threats: iptables no such file\n";
		return;
	}
	
	if(!is_file($GLOBALS["ipset"])){
		echo "Starting......: Emerging Threats: ipset no such file\n";
		$unix->send_email_events("Could not update Emerging Threats (ipset no such file)","You have enabled Emerging Threats, but it seems that\nipset binary is not installed on your system.\ntry to install it by using setup-ubuntu stored in /usr/share/artica-postfix/bin\nArtica will disable Emerging Threats to remove this notification");
		$sock->SET_INFO("EnableEmergingThreats",0);
		return;
	}

	
	
	$q=new mysql();
	$sql="SELECT * FROM postfix_whitelist_con";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "$q->mysql_error\n";}
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$WHITELISTED[$ligne["ipaddr"]]=true;
		$WHITELISTED[$ligne["hostname"]]=true;
		
	}	
	
	
	
	
	$pattern_number=@file_get_contents("/etc/artica-postfix/emerging.threats.pattern");
	if(!is_numeric($pattern_number)){$pattern_number=0;}
	
	$http=new ccurl("http://rules.emergingthreats.net/fwrules/FWrev");
	$tmp=$unix->FILE_TEMP();
	if(!$http->GetFile("$tmp")){
		echo "Starting......: Emerging Threats: http error $http->error\n";
		return;
	}
	
	$pattern_number_internet=trim(@file_get_contents($tmp));
	if($GLOBALS["VERBOSE"]){echo "Starting......: Emerging Threats: $tmp\n";}
	if(!is_numeric($pattern_number_internet)){
		echo "Starting......: Emerging Threats: corrupted pattern\n";
		return;
	}
	if($pattern_number_internet==$pattern_number){
		echo "Starting......: Emerging Threats: No new Pattern current is $pattern_number\n";
		return;
		
	}
	
	echo "Starting......: Emerging Threats: new Pattern $pattern_number_internet\n";
	$tmp=$unix->FILE_TEMP();
	$http=new ccurl("http://rules.emergingthreats.net/fwrules/emerging-Block-IPs.txt");
	if(!$http->GetFile("$tmp")){
		echo "Starting......: Emerging Threats: http error $http->error\n";
		return;
	}
	
	$tbl=explode("\n",@file_get_contents("$tmp"));
	if(count($tbl)==0){
		echo "Starting......: Emerging Threats: corrupted file\n";
		return;	
	}
	
	$iptables_drop_chain='ETLOGDROP'; 
	$iptables_att_chain='ATTACKERS'; 
	$ipset_botcc='botcc';
	$ipset_botccnet='botccnet';
	$iptables=$GLOBALS["iptables"];
	$ipset=$GLOBALS["ipset"];
	
	echo "Starting......: Emerging Threats: flush $iptables_drop_chain\n";
	shell_exec("$iptables -F $iptables_drop_chain 2>/dev/null 1>/dev/null");
	
	echo "Starting......: Emerging Threats: flush $iptables_att_chain\n";
	shell_exec("$iptables -F $iptables_att_chain 2>/dev/null 1>/dev/null");
	
	echo "Starting......: Emerging Threats: delete $iptables_att_chain from FORWARD chain\n";
	shell_exec("$iptables -D FORWARD -j $iptables_att_chain 2>/dev/null 1>/dev/null");
	
	echo "Starting......: Emerging Threats: delete $iptables_att_chain from INPUT chain\n";
	shell_exec("$iptables -D INPUT -j $iptables_att_chain 2>/dev/null 1>/dev/null");

	echo "Starting......: Emerging Threats: delete $ipset_botccnet\n";
	shell_exec("$ipset -X $ipset_botccnet 2>/dev/null 1>/dev/null");
	
	echo "Starting......: Emerging Threats: delete $ipset_botcc\n";                
	shell_exec("$ipset -X $ipset_botcc 2>/dev/null 1>/dev/null");
	
	
	echo "Starting......: Emerging Threats: Create attacker and drop chains\n";
	
	shell_exec("$iptables -N $iptables_att_chain 2>/dev/null 1>/dev/null");
	
	echo "Starting......: Emerging Threats: insert $iptables_att_chain chain into FOWARD chain\n";
	shell_exec("$iptables -I FORWARD 1 -j $iptables_att_chain 2>/dev/null 1>/dev/null");
	
	echo "Starting......: Emerging Threats: insert $iptables_att_chain chain into INPUT chain\n";
	shell_exec("$iptables -I INPUT 1 -j $iptables_att_chain 2>/dev/null 1>/dev/null");
	
	shell_exec("$iptables -N $iptables_drop_chain 2>/dev/null 1>/dev/null");
    shell_exec("$iptables -A $iptables_drop_chain -j LOG --log-level INFO --log-prefix 'ET BLOCK: ' 2>/dev/null 1>/dev/null");
    shell_exec("$iptables -A $iptables_drop_chain -j DROP 2>/dev/null 1>/dev/null");
	shell_exec("$ipset -N $ipset_botccnet nethash 2>/dev/null 1>/dev/null");
    shell_exec("$ipset -N $ipset_botcc iphash 2>/dev/null 1>/dev/null");
	
    echo "Starting......: Emerging Threats: Starting blocklist ". count($tbl). " ip(s) in population\n";
    
    $count=0;
    while (list ($num, $ligne) = each ($tbl) ){
    	if(trim($ligne)==null){continue;}
    	if(substr($ligne,0,1)=="#"){continue;}
    	if(!$WHITELISTED[$ligne]){
    		echo "adding $ligne\n";
    		
    		shell_exec("$ipset -A $ipset_botccnet $ligne 2>/dev/null 1>/dev/null");
    		shell_exec("$ipset -A $ipset_botcc $ligne 2>/dev/null 1>/dev/null");
    		$count++;
    	}
    	
    	
    }
    
 	shell_exec("$iptables -A $iptables_att_chain -p ALL -m set --set $ipset_botcc src,src -j $iptables_drop_chain 2>/dev/null 1>/dev/null");
    shell_exec("$iptables -A $iptables_att_chain -p ALL -m set --set $ipset_botccnet src,src -j $iptables_drop_chain 2>/dev/null 1>/dev/null");
    shell_exec("$iptables -A $iptables_att_chain -p ALL -m set --set $ipset_botcc dst,dst -j $iptables_drop_chain 2>/dev/null 1>/dev/null");
    shell_exec("$iptables -A $iptables_att_chain -p ALL -m set --set $ipset_botccnet dst,dst -j $iptables_drop_chain 2>/dev/null 1>/dev/null");
	
    $unix->send_email_events("Emerging Threats update new pattern $pattern_number_internet $count ip addresses","","system");
    @file_put_contents("/etc/artica-postfix/emerging.threats.pattern",$pattern_number_internet);
    shell_exec("$ipset -L botccnet >/etc/artica-postfix/botccnet.list");
    $tr=explode("\n",@file_get_contents("/etc/artica-postfix/botccnet.list"));
    $conf=array();
    while (list ($num, $ligne) = each ($tr) ){
    	if(trim($ligne)==null){continue;}
    	if(preg_match("#(.+?):#",$ligne)){continue;}
    	$conf["THREADS"][]=$ligne;
    }
    
    shell_exec("$ipset --list botcc >/etc/artica-postfix/ccnet.list");
	$tr=explode("\n",@file_get_contents("/etc/artica-postfix/ccnet.list"));
    $conf=array();
    while (list ($num, $ligne) = each ($tr) ){
    	if(trim($ligne)==null){continue;}
    	if(preg_match("#(.+?):#",$ligne)){continue;}
    	$conf["THREADS"][]=$ligne;
    }    
    
    $conf["COUNT"]=count($conf["THREADS"]);
    writelogs_framework("Writing ressources/logs/EnableEmergingThreatsBuild.db done.");
    @file_put_contents("/usr/share/artica-postfix/ressources/logs/EnableEmergingThreatsBuild.db",serialize($conf));
	@chmod("/usr/share/artica-postfix/ressources/logs/EnableEmergingThreatsBuild.db",0777);
    $conf["COUNT"]=count($conf["THREADS"]);
    @file_put_contents("/usr/share/artica-postfix/ressources/logs/EnableEmergingThreatsBuild.db",serialize($conf));
    
}

function delete_rules(){
	$unix=new unix();
	echo "Starting......: Emerging Threats:  Deleting old rules\n";
	$iptables_save=$unix->find_program("iptables-save");
	$iptables_restore=$unix->find_program("iptables-restore");
	
	
	$cmd="$iptables_save > /etc/artica-postfix/iptables.conf";
	if($GLOBALS["VERBOSE"]){echo "Starting......: $cmd\n";}		
	shell_exec($cmd);

	
	$data=file_get_contents("/etc/artica-postfix/iptables.conf");
	$datas=explode("\n",$data);
	$pattern="#.+?ArticaEmergingThreats#";	
	$count=0;
while (list ($num, $ligne) = each ($datas) ){
		if($ligne==null){continue;}
		if(preg_match($pattern,$ligne)){
			if($GLOBALS["VERBOSE"]){echo "Starting......: Delete $ligne\n";}		
			$count++;continue;}
			$conf=$conf . $ligne."\n";
		}

file_put_contents("/etc/artica-postfix/iptables.new.conf",$conf);
$cmd="$iptables_restore < /etc/artica-postfix/iptables.new.conf";
if($GLOBALS["VERBOSE"]){echo "Starting......: $cmd\n";}
shell_exec("$cmd");
echo "Starting......: Emerging Threats: cleaning iptables $count rules\n";
return $count;	
}


?>