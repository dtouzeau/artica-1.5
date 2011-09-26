<?php

include_once(dirname(__FILE__)."/frame.class.inc");
include_once(dirname(__FILE__)."/class.unix.inc");



if(isset($_GET["mastercf"])){master_cf();exit;}
if(isset($_GET["RunSaUpd"])){RunSaUpd();exit;}
if(isset($_GET["postfix-instances-list"])){postfix_instances_list();exit;}
if(isset($_GET["instance-delete"])){postfix_instance_delete();exit;}






while (list ($num, $line) = each ($_GET)){$f[]="$num=$line";}
writelogs_framework("unable to understand query !!!!!!!!!!!..." .@implode(",",$f),"main()",__FILE__,__LINE__);


function master_cf(){
	$servername=$_GET["instance"];
	if($servername=="master"){$path="/etc/postfix/master.cf";}else{$path="/etc/postfix-$servername/master.cf";}
	echo "<articadatascgi>". base64_encode(@file_get_contents($path))."</articadatascgi>";	
	
}

function RunSaUpd(){
	$statusFileContent="ressources/logs/sa-update-status.txt";
	@file_put_contents($statusFileContent, "{scheduled}\n");
	shell_exec("/bin/chmod 777 $statusFileContent");
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$php5=$unix->LOCATE_PHP5_BIN();
	$cmd=trim("$nohup $php5 /usr/share/artica-postfix/exec.spamassassin.php --sa-update >/dev/null 2>&1 &");
	shell_exec($cmd);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
}

function postfix_instance_delete(){
	$unix=new unix();
	$postmulti=$unix->find_program("postmulti");
	$instance="postfix-{$_GET["instance-delete"]}";
	$cmd="$postmulti -i $instance -p stop";
	$results=array();
	exec($cmd,$results);
	writelogs_framework($cmd ." ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	$cmd="$postmulti -i $instance -e disable";
	$results=array();
	exec($cmd,$results);
	writelogs_framework($cmd ." ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);	
	$rm=$unix->find_program("rm");
	$directory="/var/spool/$instance";
	if(is_dir($directory)){
		$cmd="$rm -rf $directory";
		$results=array();
		exec($cmd,$results);
		writelogs_framework($cmd ." ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);			
	}
	
}

function postfix_instances_list(){
	$unix=new unix();
	$search=trim(strtolower($_GET["search"]));
	if(strlen($search)>0){
		$grep=$unix->find_program("grep");
		$search=str_replace(".", "\.", $search);
		$search=str_replace("*", ".*?", $search);
		$searcchmd="|$grep -E '$search.*?\s+'";
	}
	
	$postmulti=$unix->find_program("postmulti");
	$cmd="$postmulti -l$searcchmd 2>&1";
	exec($cmd,$results);
	writelogs_framework($cmd ." ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
}


die();