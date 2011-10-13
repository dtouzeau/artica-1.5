<?php
include_once(dirname(__FILE__)."/frame.class.inc");
include_once(dirname(__FILE__)."/class.unix.inc");

if(isset($_GET["status-instance"])){status_instance();exit;}
if(isset($_GET["statrt"])){status_instance_stats();exit;}
if(isset($_GET["start-instance"])){start_instance();exit;}
if(isset($_GET["stop-instance"])){stop_instance();exit;}
if(isset($_GET["restart-instance"])){restart_instance();exit;}
if(isset($_GET["restart-instance-silent"])){restart_instance_silent();exit;}
if(isset($_GET["build-instance"])){build_instance();exit;}
if(isset($_GET["reload-all-instances"])){reload_all_instances();exit;}
if(isset($_GET["reconfigure-all-instances"])){reconfigure_all_instances();exit;}
if(isset($_GET["xr-version"])){xr_version();exit;}


while (list ($num, $line) = each ($_GET)){$f[]="$num=$line";}
writelogs_framework("unable to understand query !!!!!!!!!!!..." .@implode(",",$f),"main()",__FILE__,__LINE__);


function status_instance(){
	$id=$_GET["ID"];
	$unix=new unix();
	$php=$unix->LOCATE_PHP5_BIN();
	
	$cmd="$php /usr/share/artica-postfix/exec.loadbalance.php --status-instance $id 2>&1";
	exec($cmd,$results);
	writelogs_framework($cmd ." ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>". base64_encode(@implode("\n", $results))."</articadatascgi>";	
}
function xr_version(){
	$unix=new unix();
	
	$xr=$unix->find_program("xr");
	if(!is_file($xr)){return;}
	exec("$xr -V 2>&1",$results);
	while (list ($index, $line) = each ($results)){
		if(preg_match("#XR version\s+:.*?([0-9\.]+)#", $line,$re)){$version=$re[1];break;}
	}
	echo "<articadatascgi>$version</articadatascgi>";
}	

function restart_instance_silent(){
	$id=$_GET["ID"];
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$php=$unix->LOCATE_PHP5_BIN();	
	$cmd=trim("$nohup $php /usr/share/artica-postfix/exec.loadbalance.php --restart-instance $id >/dev/null 2>&1 &");
	shell_exec($cmd);		
}

function restart_instance(){
	$id=$_GET["ID"];
	$unix=new unix();
	$php=$unix->LOCATE_PHP5_BIN();
	$cmd="$php /usr/share/artica-postfix/exec.loadbalance.php --stop-instance $id 2>&1";
	exec($cmd,$results);
	
	$cmd="$php /usr/share/artica-postfix/exec.loadbalance.php --build-instance $id 2>&1";
	exec($cmd,$results);	
	
	$cmd="$php /usr/share/artica-postfix/exec.loadbalance.php --start-instance $id 2>&1";
	exec($cmd,$results);
	writelogs_framework($cmd ." ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>". @implode("\n", $results)."</articadatascgi>";	
}
function build_instance(){
	$id=$_GET["ID"];
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$php=$unix->LOCATE_PHP5_BIN();
	$cmd=trim("$nohup $php /usr/share/artica-postfix/exec.loadbalance.php --build-instance $id >/dev/null 2>&1 &");
	shell_exec($cmd);

}
function start_instance(){
	$id=$_GET["ID"];
	$unix=new unix();
	$php=$unix->LOCATE_PHP5_BIN();	
	$cmd="$php /usr/share/artica-postfix/exec.loadbalance.php --start-instance $id 2>&1";
	exec($cmd,$results);
	writelogs_framework($cmd ." ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>". @implode("\n", $results)."</articadatascgi>";	
}
function stop_instance(){
	$id=$_GET["ID"];
	$unix=new unix();
	$php=$unix->LOCATE_PHP5_BIN();	
	$cmd="$php /usr/share/artica-postfix/exec.loadbalance.php --stop-instance $id 2>&1";
	exec($cmd,$results);
	writelogs_framework($cmd ." ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>". @implode("\n", $results)."</articadatascgi>";	
}

function reconfigure_all_instances(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$php=$unix->LOCATE_PHP5_BIN();	
	$cmd=trim("$php /usr/share/artica-postfix/exec.loadbalance.php --build >/dev/null 2>&1");
	shell_exec($cmd);		
}

function reload_all_instances(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$php=$unix->LOCATE_PHP5_BIN();
	$cmd=trim("$nohup $php /usr/share/artica-postfix/exec.loadbalance.php --reload >/dev/null 2>&1 &");
	
}

function status_instance_stats(){
	$ID=$_GET["ID"];
	$unix=new unix();
	$pidfile="/var/run/crossroads/cross_$ID.pid";
	@file_put_contents("/var/log/crossroads/cross_$ID.log", "\n");
	$pid=trim(@file_get_contents($pidfile));
	$kill=$unix->find_program("kill");
	shell_exec("$kill -1 $pid");
	$datas=explode("\n",@file_get_contents("/var/log/crossroads/cross_$ID.log"));
	while (list ($index, $line) = each ($datas)){
		if(preg_match("#REPORT Back end\s+([0-9]+):\s+(.+?),\s+weight\s+([0-9]+)#", $line,$re)){
			$back=$re[1];
			$array[$back]["NAME"]=trim($re[2]);
			$array[$back]["WEIGHT"]=trim($re[3]);
			continue;	
		}
		
		if(preg_match("#Status:\s+(.+)#",$line,$re)){
			$array[$back]["STATUS"]=$re[1];
			continue;	
		}
		
		if(preg_match("#Connections:\s+([0-9]+)#",$line,$re)){
			$array[$back]["CNX"]=$re[1];
			continue;	
		}
		
		if(preg_match("#Served:(.+?),\s+([0-9]+)\s+clients#",$line,$re)){
			$array[$back]["FLOW"]=$re[1];
			$array[$back]["CLIENTS"]=$re[2];
			continue;	
		}
		
	}
	
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";	
	
	
}