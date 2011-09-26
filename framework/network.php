<?php
include_once(dirname(__FILE__)."/frame.class.inc");
include_once(dirname(__FILE__)."/class.unix.inc");
include_once(dirname(__FILE__)."/class.postfix.inc");



if(isset($_GET["NetworkManager-check-redhat"])){NetworkManager_redhat();exit;}
if(isset($_GET["reconfigure-postfix-instances"])){postfix_reconfigures_multiples_instances();exit;}
if(isset($_GET["ping"])){pinghost();exit;}
if(isset($_GET["crossroads-restart"])){crossroads_restart();exit;}
if(isset($_GET["ipv6"])){ipv6();exit;}
if(isset($_GET["OpenVPNServerLogs"])){OpenVPN_ServerLogs();exit;}
if(isset($_GET["ipdeny"])){ipdeny();exit;}
if(isset($_GET["fw-inbound-rules"])){iptables_inbound();exit;}
if(isset($_GET["fqdn"])){fqdn();exit;}
if(isset($_GET["iptaccount-installed"])){iptaccount_check();exit;}
if(isset($_GET["ifup-ifdown"])){ifup_ifdown();exit;}
if(isset($_GET["reconstruct-interface"])){reconstruct_interface();exit;}
if(isset($_GET["dhcpd-leases"])){dhcpd_leases_force();exit;}
if(isset($_GET["dhcpd-leases-script"])){dhcpd_leases_script();exit;}




while (list ($num, $ligne) = each ($_GET) ){$a[]="$num=$ligne";}
writelogs_framework("unable to unserstand ".@implode("&",$a),__FUNCTION__,__FILE__,__LINE__);


function NetworkManager_redhat(){
	$unix=new unix();
	$chkconfig=$unix->find_program("chkconfig");
	if(!is_file($chkconfig)){return;}
	exec("$chkconfig --list NetworkManager 2>&1",$results);
	echo "<articadatascgi>". @implode("\n",$results)."</articadatascgi>";
	
	
}

function iptaccount_check(){
	$unix=new unix();
	$iptaccount=$unix->find_program("iptaccount");
	if(!is_file($iptaccount)){echo "<articadatascgi>FALSE</articadatascgi>";return;}
	exec("$iptaccount -a 2>&1",$results);
	while (list ($num, $ligne) = each ($results) ){
		if(preg_match("#failed: Can't get table names from kernel#", $ligne)){
			echo "<articadatascgi>FALSE</articadatascgi>";return;
		}
	}
	echo "<articadatascgi>TRUE</articadatascgi>";return;
}

function fqdn(){
	$unix=new unix();
	$hostname=$unix->find_program("hostname");
	$cmd=trim("$hostname -f 2>&1");
	exec($cmd,$results);
	writelogs_framework($cmd ." ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>". base64_encode(trim(@implode(" ", $results)))."</articadatascgi>";
}

function postfix_reconfigures_multiples_instances(){
	$unix=new unix();
	$php=$unix->LOCATE_PHP5_BIN();
	$nohup=$unix->find_program("nohup");
	shell_exec(trim("$nohup $php /usr/share/artica-postfix/exec.virtuals-ip.php --postfix-instances >/dev/null 2>&1 &"));

}

function pinghost(){
	$host=$_GET["ping"];
	$unix=new unix();
	if($unix->PingHost($host)){
		echo "<articadatascgi>TRUE</articadatascgi>";
	}
}


	
function ifup_ifdown(){
	$eth=$_GET["ifup-ifdown"];
	$unix=new unix();
	$php=$unix->LOCATE_PHP5_BIN();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup $php /usr/share/artica-postfix/exec.virtuals-ip.php --ifupifdown $eth >/dev/null 2>&1 &");
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);		
}

function crossroads_restart(){
	$unix=new unix();
	$php=$unix->LOCATE_PHP5_BIN();
	$nohup=$unix->find_program("nohup");
	shell_exec(trim("$nohup $php /usr/share/artica-postfix/exec.crossroads.php --multiples-restart >/dev/null 2>&1 &"));	
	
}
function dhcpd_leases_force(){
	$unix=new unix();
	$php=$unix->LOCATE_PHP5_BIN();
	$nohup=$unix->find_program("nohup");
	shell_exec(trim("$nohup $php /usr/share/artica-postfix/exec.dhcpd-leases.php --force >/dev/null 2>&1 &"));		

}

function dhcpd_leases_script(){
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".pid";
	$unix=new unix();
	$pid=@file_get_contents($pidfile);
	if($unix->process_exists($pid)){
		$time=$unix->PROCCESS_TIME_MIN($pid);
		echo "<articadatascgi>". base64_encode(serialize(array($pid,$time)))."</articadatascgi>";
	}

}

function ipv6(){
	$unix=new unix();
	$php=$unix->LOCATE_PHP5_BIN();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup $php /usr/share/artica-postfix/exec.virtuals-ip.php --ipv6 >/dev/null 2>&1 &");
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);	
	
}

function OpenVPN_ServerLogs(){
	$unix=new unix();
	$php=$unix->LOCATE_PHP5_BIN();
	$tail=$unix->find_program("tail");
	$cmd=trim("$tail -n 300 /var/log/openvpn/openvpn.log 2>&1 ");
	
	exec($cmd,$results);		
	writelogs_framework($cmd ." ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
}

function ipdeny(){
	$unix=new unix();
	$php=$unix->LOCATE_PHP5_BIN();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup $php /usr/share/artica-postfix/exec.postfix.iptables.php --ipdeny >/dev/null 2>&1 &");
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);		
}

function iptables_inbound(){
	$unix=new unix();
	$php=$unix->LOCATE_PHP5_BIN();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup $php /usr/share/artica-postfix/exec.postfix.iptables.php --perso >/dev/null 2>&1 &");
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);	
	
}

function reconstruct_interface(){
	$eth=$_GET["reconstruct-interface"];
	$unix=new unix();
	$php=$unix->LOCATE_PHP5_BIN();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup $php /usr/share/artica-postfix/exec.virtuals-ip.php --reconstruct-interface $eth >/dev/null 2>&1 &");
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);	
	
	
}



