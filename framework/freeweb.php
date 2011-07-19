<?php

include_once(dirname(__FILE__)."/frame.class.inc");
include_once(dirname(__FILE__)."/class.unix.inc");


if(isset($_GET["mode-security-log"])){mod_security_logs();exit;}
if(isset($_GET["reconfigure"])){freeweb_reconfigure();exit;}
if(isset($_GET["loaded-modules"])){freeweb_modules();exit;}
if(isset($_GET["force-resolv"])){force_resolv();exit;}




while (list ($num, $line) = each ($_GET)){$f[]="$num=$line";}

writelogs_framework("unable to understand query !!!!!!!!!!!..." .@implode(",",$f),"main()",__FILE__,__LINE__);
die();

function force_resolv(){
	$unix=new unix();
	$php=$unix->LOCATE_PHP5_BIN();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup $php /usr/share/artica-postfix/exec.freeweb.php --resolv --force >/dev/null 2>&1 &");
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);		
	
}

function mod_security_logs(){
	$servername=$_GET["servername"];
	$unix=new unix();
	$tail=$unix->find_program("tail");
	$cmd="$tail -n 500 /var/log/apache2/$servername/modsec_debug_log 2>&1";
	exec("$cmd",$results);
	writelogs_framework("$cmd ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>".base64_encode(serialize($results))."</articadatascgi>";	
}

function freeweb_reconfigure(){
	$unix=new unix();
	$php=$unix->LOCATE_PHP5_BIN();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup $php /usr/share/artica-postfix/exec.freeweb.php --httpd >/dev/null 2>&1 &");
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);	
	
}

function freeweb_modules(){
	$unix=new unix();
	$apache2ctl=$unix->find_program("apache2ctl");
	if(!is_file($apache2ctl)){echo "<articadatascgi>".base64_encode(serialize(array("apache2ctl no such file")))."</articadatascgi>";return;}
	$cmd="$apache2ctl -t -D DUMP_MODULES 2>&1";
	exec("$cmd",$results);
	writelogs_framework("$cmd ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>".base64_encode(serialize($results))."</articadatascgi>";
}

?>