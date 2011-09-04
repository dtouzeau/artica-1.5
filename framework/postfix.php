<?php

include_once(dirname(__FILE__)."/frame.class.inc");
include_once(dirname(__FILE__)."/class.unix.inc");



if(isset($_GET["mastercf"])){master_cf();exit;}
if(isset($_GET["RunSaUpd"])){RunSaUpd();exit;}








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


die();