<?php
$_GET["filelogs"]="/var/log/artica-postfix/iptables.debug";
$_GET["filetime"]="/etc/artica-postfix/croned.1/".basename(__FILE__).".time";
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;$GLOBALS["debug"]=true;}
if($GLOBALS["VERBOSE"]){ini_set('html_errors',0);ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);}
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__) . '/ressources/class.users.menus.inc');
include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
include_once(dirname(__FILE__) . '/framework/class.unix.inc');
include_once(dirname(__FILE__) . '/framework/frame.class.inc');


if($argv[1]=="--compile-single"){compile_rule($argv[2]);die();}
if($argv[1]=="--export"){export_rules();die();}
if($argv[1]=="--import"){import_rules();die();}





function compile_rule($ID){
	$unix=new unix();
	$php5=$unix->LOCATE_PHP5_BIN();
	$nice=$unix->EXEC_NICE();
	$f[]="MAILTO=\"\"";
	$f[]="PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/usr/X11R6/bin:/usr/share/artica-postfix/bin";
	$f[]="0,15,30,45 * * * * root $nice$php5 ".__FILE__." --export >/dev/null 2>&1";
	$f[]="";
	
	@file_put_contents("/etc/cron.d/iptaccount", @implode("\n", $f));
	shell_exec("/bin/chmod 640 /etc/cron.d/iptaccount >/dev/null 2>&1");
	
	iptables_delete_rule($ID);
	$q=new mysql();
	$sql="SELECT * FROM tcp_account_rules WHERE ID='$ID'";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	if($ligne["ipaddr"]==null){return;}
	if($ligne["mode"]==null){return;}
	$unix=new unix();
	$iptables=$unix->find_program("iptables");
	$cmd="$iptables -A {$ligne["mode"]} -j ACCOUNT --addr {$ligne["ipaddr"]} --tname rule_{$ID} -m comment --comment \"ArticaIptAccountRule_{$ID}\"";
	if($GLOBALS["VERBOSE"]){echo $cmd."\n";}
	shell_exec($cmd);
}

function iptables_delete_rule($ID){
$unix=new unix();
$iptables_save=$unix->find_program("iptables-save");
$iptables_restore=$unix->find_program("iptables-restore");	
system("$iptables_save > /etc/artica-postfix/iptables.conf");
$data=file_get_contents("/etc/artica-postfix/iptables.conf");
$datas=explode("\n",$data);
$pattern="#.+?ArticaIptAccountRule_$ID#";
$c=0;	
while (list ($num, $ligne) = each ($datas) ){
		if($ligne==null){continue;}
		if(preg_match($pattern,$ligne)){$c++;continue;}
		$conf=$conf . $ligne."\n";
		}


file_put_contents("/etc/artica-postfix/iptables.new.conf",$conf);
system("$iptables_restore < /etc/artica-postfix/iptables.new.conf");
}

function export_rules(){
	$unix=new unix();
	if(!is_dir("/var/log/artica-postfix/iptaccount")){@mkdir("/var/log/artica-postfix/iptaccount",0644,true);}
	$iptaccount=$unix->find_program("iptaccount");
	if(!is_file($iptaccount)){
		if($GLOBALS["VERBOSE"]){echo "iptaccount, no such file\n";}
		return;
	}
	exec("$iptaccount -a 2>&1",$results);
	while (list ($num, $ligne) = each ($results) ){
		if(preg_match("#Found table.+?rule_([0-9]+)#",$ligne,$re)){
			$time=time();
			$rule=$re[1];
			if($GLOBALS["VERBOSE"]){echo "Rule [{$re[1]}] ($time)\n";}
			$filename="/var/log/artica-postfix/iptaccount/$time.$rule";
			$cmd="$iptaccount -s -l rule_$rule -f >$filename 2>&1";
			if($GLOBALS["VERBOSE"]){echo "$cmd\n";}
			shell_exec($cmd);
		}
	}
	
	$unix->THREAD_COMMAND_SET($unix->LOCATE_PHP5_BIN()." ".__FILE__." --import");

}

function import_rules(){
	if (!$handle = opendir("/var/log/artica-postfix/iptaccount")) {return ;}
	$q=new mysql();
	$unix=new unix();
	while (false !== ($filename = readdir($handle))) {
		$targetFile="/var/log/artica-postfix/iptaccount/$filename";
		if(!is_file($targetFile)){continue;}
		if(!preg_match("#^([0-9]+)\.([0-9]+)$#",$filename,$re)){echo "$filename is not a requested file...\n";continue;}
		$rule_id=$re[2];
		$time=$re[1];
		$zDate=date('Y-m-d H:i:s',$time);
		$f=explode("\n",@file_get_contents($targetFile));
		$suffix=array();
		$sql_prefix="INSERT INTO tcp_account_events (rule_id,zDate,ipaddr,src_parckets,src_bytes,dst_packets,dst_bytes) VALUES ";
		while (list ($num, $ligne) = each ($f) ){
			if(!preg_match("#^(.+?);([0-9]+);([0-9]+);([0-9]+);([0-9]+)#", $ligne,$re)){continue;}
			$ipaddr=$re[1];
			$src_parckets=$re[2];
			$src_bytes=$re[3];
			$dst_packets=$re[4];
			$dst_bytes=$re[5];
			$suffix[]="('$rule_id','$zDate','$ipaddr','$src_parckets','$src_bytes','$dst_packets','$dst_bytes')";

		}
		
		$sql="$sql_prefix".@implode(",", $suffix);
		$q->QUERY_SQL($sql,"artica_events");
		if(!$q->ok){
			$unix->send_email_events("TCP/IP account failed $filename (MySQL)", "Artica encounter an error while inserting statistics\n$q->mysql_error\n$sql", "system");
			continue;
		}
		
		@unlink($targetFile);
		
	}
	
	
	
	
}


