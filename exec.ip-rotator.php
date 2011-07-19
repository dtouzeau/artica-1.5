<?php

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.acls.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");

if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;}

if($argv[1]=='--build'){build();die();}



function build(){
	iptables_delete_all();
	$sql="SELECT * FROM ip_rotator_smtp ORDER BY ID";
	$mode["nth"]="{counter}";
	$mode["random"]="{random}";
	$unix=new unix();
	$itables=$unix->find_program("iptables");
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "$q->mysql_error\n";}
	$count=mysql_num_rows($results);
	
	echo "Starting......: TCP/IP Rotator $count items\n";
	if($count==0){return;}
	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$ipsrc=$ligne["ipsource"];
		$ipdest=$ligne["ipdest"];
		$mode=$ligne["mode"];
		$comment=" -m comment --comment \"ArticaIpRotator\"";
		if($mode=="nth"){$mode_text=" -m statistic --mode nth --every {$ligne["mode_value"]} ";}
		if($mode=="random"){$mode_text=" -m statistic --mode random --probability {$ligne["mode_value"]} ";}
		$cmdline="$itables -t nat -A PREROUTING -p tcp -d $ipsrc --dport 25 -m state --state NEW $mode_text --packet 0 -j DNAT --to-destination $ipdest $comment";
		if($GLOBALS["VERBOSE"]){echo $cmdline."\n";}
		$results=array();
		exec($cmdline,$results);
		while (list ($a, $b) = each ($results) ){echo "Starting......: TCP/IP Rotator: $b\n";}
		
	}
	
}

function iptables_delete_all(){
	$unix=new unix();
	$itables_save=$unix->find_program("iptables-save");
	$itables_restore=$unix->find_program("iptables-restore");
	echo "Starting......: TCP/IP Exporting datas\n";	
	system("$itables_save > /etc/artica-postfix/iptables.conf");
	$data=file_get_contents("/etc/artica-postfix/iptables.conf");
	$datas=explode("\n",$data);
	$pattern="#.+?ArticaIpRotator#";	
while (list ($num, $ligne) = each ($datas) ){
		if($ligne==null){continue;}
		if(preg_match($pattern,$ligne)){
			echo "Starting......: TCP/IP Rotator Deleting rule $num\n";
			continue;
		}
		$conf=$conf . $ligne."\n";
		}

echo "Starting......: TCP/IP Rotator restoring datas\n";
file_put_contents("/etc/artica-postfix/iptables.new.conf",$conf);
system("$itables_restore < /etc/artica-postfix/iptables.new.conf");


}

?>