<?php
include(dirname(__FILE__).'/ressources/class.qos.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');

	
	if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
	if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}

	
	
if($argv[1]=="--build"){build();die();}
if($argv[1]=="--delete"){Deleteqdisc();die();}



function build(){
	Deleteqdisc();
	$unix=new unix();
	if($GLOBALS["TC"]==null){$unix=new unix();$GLOBALS["TC"]=$unix->find_program("tc");	}
	$iptables=$unix->find_program("iptables");
	$sysctl=$unix->find_program("sysctl");				
	
	$sql="SELECT * FROM qos_eth WHERE enabled=1";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,'artica_backup');
	if(!$q->ok){
		echo "Starting......: Q.O.S Error:\"$q->mysql_error\" checking old commands\n";
		PerformOldScript();
		return;
	}
	
	$num_rows = mysql_num_rows($results);
	if($num_rows<1){
		echo "Starting......: Q.O.S no rule defined\n";
		return;
	}
	$GLOBALS["COMMANDS"][]="$sysctl -w net.ipv4.ip_forward=1 >/dev/null 2>&1";
	
	$handle=0;
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$service_id=$ligne["ID"];
		$eth=$ligne["NIC"];
		$default_class_id=$ligne["default_class_id"];
		$bandwith=$ligne["bandwith"];
		$name=$ligne["name"];
		if($default_class_id<1){$default_class_id=1000;}
		$handle++;
		echo "Starting......: Q.O.S service \"$name\"\n";
		//$cmd="$tc qdisc add dev $eth root handle $handle: cbq bandwidth {$bandwith}kbps handle $handle: htb default 0";
		$cmd="{$GLOBALS["TC"]} qdisc add dev $eth root handle $handle: htb";
		$GLOBALS["COMMANDS"][]=$cmd;
		
		$cmd="$iptables -t mangle -N $eth -m comment --comment \"ArticaQOSRules\"";
		$GLOBALS["COMMANDS"][]=$cmd;
		add_classes($service_id,$eth,$handle);
		$cmd="$iptables -t nat -A POSTROUTING -o $eth -j MASQUERADE -m comment --comment \"ArticaQOSRules\"";
		//$cmd="$iptables -t mangle -A POSTROUTING -o $eth -j MASQUERADE -m comment --comment \"ArticaQOSRules\"";
		if($GLOBALS["VERBOSE"]){echo "$cmd\n";}
		$GLOBALS["COMMANDS"][]=$cmd;
	}
	

	if(is_array($GLOBALS["COMMANDS"])){
		@file_put_contents("/etc/artica-postfix/qos.cmds",@implode("\n",$GLOBALS["COMMANDS"]));
		while (list ($num, $cmdline) = each ($GLOBALS["COMMANDS"]) ){
			if(trim($cmdline)==null){continue;}
			if($GLOBALS["VERBOSE"]){echo "$cmdline\n";}
			system($cmdline);
		}
		
		echo "Starting......: Q.O.S done\n";
	}
}

function PerformOldScript(){
	$f=explode("\n",@file_get_contents("/etc/artica-postfix/qos.cmds"));
	if(!is_array($f)){return;}
	if(count($f)==0){return;}
	while (list ($num, $cmdline) = each ($f)){
		if(trim($cmdline)==null){continue;}
		if($GLOBALS["VERBOSE"]){echo "$cmdline\n";}
		system($cmdline);
	}
	
	echo "Starting......: Q.O.S done\n";
}

function add_classes($service_id,$dev,$handle){
	if($GLOBALS["TC"]==null){$unix=new unix();$GLOBALS["TC"]=$unix->find_program("tc");	}
	$sql="SELECT * FROM qos_class WHERE service_id=$service_id AND enabled=1 ORDER BY prio";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,'artica_backup');
	$pri=0;
	// kbit //kbps
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$pri++;	
		//$ligne["rate"]=$ligne["rate"]/8;
		//$ligne["ceil"]=$ligne["ceil"]/8;
		
		$cmd="{$GLOBALS["TC"]} class add dev $dev parent $handle: classid $handle:{$ligne["ID"]}0 htb rate {$ligne["rate"]}kbps ceil {$ligne["ceil"]}kbps prio $pri";
		echo "Starting......: Q.O.S class \"{$ligne["name"]}\"\n";
		$GLOBALS["COMMANDS"][]=$cmd;
		
		
		$cmd="{$GLOBALS["TC"]} qdisc add dev $dev parent $handle:{$ligne["ID"]}0 handle {$ligne["ID"]}0: sfq perturb 10";
		$GLOBALS["COMMANDS"][]=$cmd;
			
		$cmd="{$GLOBALS["TC"]} filter add dev $dev parent $handle:0 protocol ip handle {$pri}0 fw flowid $handle:{$ligne["ID"]}0";
		$GLOBALS["COMMANDS"][]=$cmd;
		iptables_rules($ligne["ID"],"$handle:{$ligne["ID"]}0",$dev,"{$pri}0");
		
		
	}
	
	
}


function iptables_rules($class_id,$handle_id,$dev,$mark){
	$unix=new unix();
	$iptables=$unix->find_program("iptables");
	
	$sql="SELECT * FROM qos_rules WHERE class_id=$class_id AND enabled=1";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,'artica_backup');	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$ligne["sip"]=trim($ligne["sip"]);
		if($ligne["sip"]==null){$ligne["sip"]="*";}
		$ligne["sip"]=str_replace("*","0/0",$ligne["sip"]);
		$cmd=null;
		$ligne["dip"]=trim($ligne["dip"]);
		if($ligne["dip"]==null){$ligne["dip"]="*";}
		$ligne["dip"]=str_replace("*","0/0",$ligne["dip"]);
		
		if(strpos($ligne["dport"],",")>0){
			$dport=" -m multiport --dports {$ligne["dport"]}";
		}else{
			if(trim($ligne["dport"])<>null){$dport=" --dport {$ligne["dport"]}";}
		}
		
		if(strpos($ligne["sport"],",")>0){
			$sport=" -m multiport --sports {$ligne["sport"]}";
		}else{
			if(trim($ligne["sport"])<>null){$sport=" --sport {$ligne["sport"]}";}
		}		

		/*$cmd="$iptables -t mangle -A FORWARD -p {$ligne["proto"]} -s {$ligne["sip"]} -d {$ligne["dip"]}";
		$cmd=$cmd."$sport$dport -j LOG --log-prefix \" MARK FORWARD \"  -m comment --comment \"ArticaQOSRules\"";
		if($GLOBALS["VERBOSE"]){echo "$cmd\n";}
		exec($cmd,$resultscmds);
		if($GLOBALS["VERBOSE"]){echo @implode("\n",$resultscmds);}	
		$cmd=null;
		*/
		$cmd="$iptables -t mangle -A FORWARD -p {$ligne["proto"]} -s {$ligne["sip"]} -d {$ligne["dip"]}";
		$cmd=$cmd."$sport$dport -j MARK --set-mark $mark";
		$cmd=$cmd." -m comment --comment \"ArticaQOSRules\"";
		$GLOBALS["COMMANDS"][]=$cmd;
	}
	
	
}





function Deleteqdisc(){
	if($GLOBALS["TC"]==null){$unix=new unix();$GLOBALS["TC"]=$unix->find_program("tc");	}
	echo "Starting......: Q.O.S delete hold settings with \"{$GLOBALS["TC"]}\"\n";
	exec("{$GLOBALS["TC"]} qdisc show 2>&1",$resultscmds);
	while (list ($num, $line) = each ($resultscmds) ){
		if(preg_match("#qdisc htb.+?dev\s+(.+?)\s+#",$line,$re)){
			echo "Starting......: Q.O.S delete {$re[1]}\n";
			shell_exec("{$GLOBALS["TC"]} qdisc del dev {$re[1]} root");
		}else{
			if($GLOBALS["VERBOSE"]){echo "NO MATCH $line #qdisc htb.+?dev\s+(.+?)\s+# \n";}
		}
		
	}
	
	$unix=new unix();
	$unix->IPTABLES_DELETE_REGEX_ENTRIES("ArticaQOSRules");
	
	
	
	
}


/*$IPTABLES -A FORWARD -i ppp0 -o eth0 -m state --state ESTABLISHED,RELATED -j ACCEPT
$IPTABLES -A FORWARD -i eth0 -o ppp0 -j ACCEPT
$IPTABLES -t nat -A POSTROUTING -o ppp0 -j MASQUERADE
*/
?>