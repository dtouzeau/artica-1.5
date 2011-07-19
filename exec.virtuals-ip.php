<?php
include_once(dirname(__FILE__) . '/ressources/class.ldap.inc');
include_once(dirname(__FILE__) . '/ressources/class.templates.inc');
include_once(dirname(__FILE__) . '/framework/class.unix.inc');
include_once(dirname(__FILE__) . '/framework/frame.class.inc');
include_once(dirname(__FILE__) . '/ressources/class.system.network.inc');
include_once(dirname(__FILE__) . '/ressources/class.system.nics.inc');

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;ini_set_verbosed();}
if($argv[1]=="--interfaces"){interfaces_show();die();}
if($argv[1]=="--just-add"){routes();die();}
if($argv[1]=="--ifconfig"){ifconfig_tests();exit;}
if($argv[1]=="--bridges"){bridges_build();exit;}
if($argv[1]=="--parse-tests"){ifconfig_parse($argv[2]);exit;}
if($argv[1]=="--routes"){routes();exit;}
if($argv[1]=="--routes-del"){routes_del($argv[2]);exit;}
if($argv[1]=="--vlans"){build();exit;}
if($argv[1]=="--postfix-instances"){postfix_multiples_instances();exit;}
if($argv[1]=="--ping"){ping($argv[2]);exit;}
if($argv[1]=="--ipv6"){Checkipv6();exit;}




build();

//
//vconfig set_flag eth1.3 1 1
//vconfig set_flag eth1.4 1 1

//http://www.cyberciti.biz/tips/howto-configure-linux-virtual-local-area-network-vlan.html
//http://www.stg.net/vlanbridge


function ping($host){
	ini_set_verbosed();
	$unix=new unix();
	if($unix->PingHost($host)){
		echo "$host:TRUE\n";
	}else{
		echo "$host:FALSE\n";
	}
	
}

function interfaces_show(){
	$nic=new system_nic();
	$datas=$nic->root_build_debian_config();
	echo $datas;
}



function build(){
$users=new usersMenus();	
$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".pid";
$oldpid=@file_get_contents($pidfile);
$unix=new unix();
if($unix->process_exists($oldpid)){
	echo "Starting......: Building networks already executed PID: $oldpid\n";
	die();
}
@file_put_contents($pidfile,getmypid());

	if($users->AS_DEBIAN_FAMILY){
		BuildNetWorksDebian();
		return;
	}
	
	BuildNetWorksRedhat();
	bridges_build();
	Checkipv6();
	
	
}

function BuildNetWorksDebian(){
	if(!is_file("/etc/network/interfaces")){return;}
	echo "Starting......: Building networks mode Debian\n";
	$nic=new system_nic();
	$datas=$nic->root_build_debian_config();
	if($datas==null){
		echo "Starting......: not yet configured\n";
		return;
	}
	
	echo "Starting......: ". strlen($datas)." bytes length\n";
	@file_put_contents("/etc/network/interfaces",$datas);
	bridges_build();
	$unix=new unix();
	$unix->THREAD_COMMAND_SET($unix->LOCATE_PHP5_BIN()." /usr/share/artica-postfix/exec.ip-rotator.php --build");
	$unix->NETWORK_DEBIAN_RESTART();
	}

function BuildNetWorksRedhat(){
	
	echo "Starting......: Building networks mode RedHat\n";
	$nic=new system_nic();
	$datas=$nic->root_build_redhat_config();
	bridges_build();
	$unix=new unix();
	$unix->THREAD_COMMAND_SET($unix->LOCATE_PHP5_BIN()." /usr/share/artica-postfix/exec.ip-rotator.php --build");
	$unix->NETWORK_REDHAT_RESTART();
	}


function ifconfig_tests(){
	$unix=new unix();
	$cmd=$unix->find_program("ifconfig")." -s";
	exec($cmd,$results);
	while (list ($index, $line) = each ($results) ){
		if(preg_match("#^(.+?)\s+[0-9]+#",$line,$re)){
			$array[trim($re[1])]=trim($re[1]);
		}
	}
	print_r($array);
	
}


function bridges_build(){
	$unix=new unix();
	$iptables=$unix->find_program("iptables");
	$sysctl=$unix->find_program("sysctl");
	$sql="SELECT * FROM iptables_bridge ORDER BY ID DESC";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");	
	if(!$q->ok){return null;}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
		$array_virtual_infos=VirtualNicInfosIPaddr($ligne["nics_virtuals_id"]);
		$nicvirtual=$array_virtual_infos["IPADDR"];
		if($nicvirtual==null){continue;}
		$nic_linked=trim($ligne["nic_linked"]);
		if(trim($nic_linked)==null){continue;}
		
		if(preg_match("#(.+?):([0-9]+)#",$nic_linked,$re)){
			$array_virtual_infos=VirtualNicInfosIPaddr($re[2]);
			$nic_linked=$array_virtual_infos["IPADDR"];
		}
		
		$id=$ligne["ID"];
		echo "Starting......: Virtuals bridge $nicvirtual to $nic_linked\n";
		$iptables_rules[]="$iptables -A FORWARD -i $nicvirtual -o $nic_linked -m state --state ESTABLISHED,RELATED -j ACCEPT -m comment --comment \"ArticaBridgesVirtual:$id\" 2>&1";
		$iptables_rules[]="$iptables -A FORWARD -i $nicvirtual -o $nic_linked -j ACCEPT -m comment --comment \"ArticaBridgesVirtual:$id\" 2>&1";
		$iptables_rules[]="$iptables -t nat -A POSTROUTING -o $nic_linked -j MASQUERADE	-m comment --comment \"ArticaBridgesVirtual:$id\" 2>&1";	
		
	}
	
	bridges_delete();
	$rules=0;
	if(count($iptables_rules)>0){
		while (list ($index, $chain) = each ($iptables_rules) ){	
			unset($results);
			exec($chain,$results);
			if(count($results)>0){
				echo "Starting......: Virtuals bridge ERROR $chain\n";
				while (list ($num, $line) = each ($results) ){echo "Starting......: Virtuals bridge ERROR $line\n";}
			}else{
				$rules=$rules+1;
			}
			
		}
	}
	if($rules>0){
		shell_exec("$sysctl -w net.ipv4.ip_forward=1");
	}
	
	echo "Starting......: Virtuals bridge adding iptables $rules rule(s)\n";
}

function bridges_delete(){
	$unix=new unix();
	echo "Starting......: Virtuals bridge Deleting old rules\n";
	$iptables_save=$unix->find_program("iptables-save");
	$iptables_restore=$unix->find_program("iptables-restore");
	
	if($GLOBALS["VERBOSE"]){echo "Starting......: $cmd\n";}		
	$cmd="$iptables_save > /etc/artica-postfix/iptables.conf";
	shell_exec($cmd);

	
	$data=file_get_contents("/etc/artica-postfix/iptables.conf");
	$datas=explode("\n",$data);
	$pattern="#.+?ArticaBridgesVirtual#";	
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
echo "Starting......: Virtuals bridge cleaning iptables $count rules\n";	
}


function ifconfig_parse($path=null){
	$unix=new unix();
	print_r($unix->NETWORK_DEBIAN_PARSE_ARRAY($path));
	
}


function routes(){
	$unix=new unix();
	$route=$unix->find_program("route");
			$types[1]="{network_nic}";
		$types[2]="{host}";	
		
		
		$sql="SELECT * FROM nic_routes ORDER BY `nic`";
	writelogs($sql,__FUNCTION__,__FILE__,__LINE__);
	$q=new mysql();	
	$results=$q->QUERY_SQL($sql,"artica_backup");	
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$type=$ligne["type"];
		$ttype="-net";
		if($type==1){$ttype="-net";}
		if($type==2){$ttype="-host";}
		if($ligne["nic"]<>null){$dev=" dev {$ligne["nic"]}";}
		$cmd="$route add $ttype {$ligne["pattern"]} gw {$ligne["gateway"]}$dev";
		if($GLOBALS["VERBOSE"]){echo $cmd."\n";}
		shell_exec("$cmd >/dev/null 2>&1");
	}
	
	
}

function routes_del($md5){
	$unix=new unix();
	$route=$unix->find_program("route");	
	$q=new mysql();
	$sql="SELECT * FROM nic_routes WHERE `zmd5`='$md5'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$type=$ligne["type"];
	$ttype="-net";
	if($type==1){$ttype="-net";}
	if($type==2){$ttype="-host";}
	if($ligne["nic"]<>null){$dev=" dev {$ligne["nic"]}";}
	$cmd="$route del $ttype {$ligne["pattern"]} gw {$ligne["gateway"]}$dev";
	if($GLOBALS["VERBOSE"]){echo $cmd."\n";}	
	shell_exec("$cmd >/dev/null 2>&1");
	$sql="DELETE FROM nic_routes WHERE `zmd5`='$md5'";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}	
	
	
}


function postfix_multiples_instances(){
	build();
	$unix=new unix();
	$php=$unix->LOCATE_PHP5_BIN();
	
	$sql="SELECT ou, ip_address, `key` , `value` FROM postfix_multi WHERE `key` = 'myhostname'";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
		$hostname=$ligne["value"];
		echo "Starting......: reconfigure postfix instance $hostname\n";
		shell_exec("$php /usr/share/artica-postfix/exec.postfix-multi.php --instance-reconfigure \"$hostname\"");
	}
}


function Checkipv6(){
	$unix=new unix();
	$sock=new sockets();
	$EnableipV6=$sock->GET_INFO("EnableipV6");
	if(!is_numeric($EnableipV6)){$EnableipV6=0;}
	
	if($EnableipV6==0){
		echo "Starting......: Building networks IPv6 is disabled\n";
	}else{
		echo "Starting......: Building networks IPv6 is enabled\n";
	}
	
	$unix->sysctl("net.ipv6.conf.all.disable_ipv6",$EnableipV6);
	$unix->sysctl("net.ipv6.conf.default.disable_ipv6",$EnableipV6);
	$unix->sysctl("net.ipv6.conf.lo.disable_ipv6",$EnableipV6);
	
	@file_put_contents("/proc/sys/net/ipv6/conf/lo/disable_ipv6",$EnableipV6);
	@file_put_contents("/proc/sys/net/ipv6/conf/lo/disable_ipv6",$EnableipV6);
	@file_put_contents("/proc/sys/net/ipv6/conf/all/disable_ipv6",$EnableipV6);
	@file_put_contents("/proc/sys/net/ipv6/conf/default/disable_ipv6",$EnableipV6);
}

?>
