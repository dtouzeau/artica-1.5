<?php

include_once(dirname(__FILE__)."/frame.class.inc");
include_once(dirname(__FILE__)."/class.unix.inc");


if(isset($_GET["syslogger"])){syslogger();exit;}
if(isset($_GET["openvpn"])){openvpn();exit;}
if(isset($_GET["postfix-single"])){postfix_single();exit;}
if(isset($_GET["nsswitch"])){nsswitch();exit;}
if(isset($_GET["changeRootPasswd"])){changeRootPasswd();exit;}
if(isset($_GET["process1"])){process1();exit;}
if(isset($_GET["mysql-status"])){mysql_status();exit;}
if(isset($_GET["greensql-status"])){greensql_status();exit;}
if(isset($_GET["greensql-reload"])){greensql_reload();exit;}
if(isset($_GET["greensql-logs"])){greensql_logs();exit;}
if(isset($_GET["restart-postfix-all"])){restart_postfix_all();exit;}





while (list ($num, $line) = each ($_GET)){$f[]="$num=$line";}

writelogs_framework("unable to understand query !!!!!!!!!!!..." .@implode(",",$f),"main()",__FILE__,__LINE__);
die();
function mysql_status(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --mysql --nowachdog",$results);
	echo "<articadatascgi>". base64_encode(@implode("\n",$results))."</articadatascgi>";		
}
function greensql_status(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --greensql --nowachdog",$results);
	echo "<articadatascgi>". base64_encode(@implode("\n",$results))."</articadatascgi>";		
}
function syslogger(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup /etc/init.d/artica-postfix restart sysloger >/dev/null 2>&1 &");
	shell_exec($cmd);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	
}

function restart_postfix_all(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup /etc/init.d/artica-postfix restart postfix-heavy >/dev/null 2>&1 &");
	shell_exec($cmd);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);	
}


function openvpn(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup /etc/init.d/artica-postfix restart openvpn >/dev/null 2>&1 &");
	shell_exec($cmd);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	
}
function postfix_single(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup /etc/init.d/artica-postfix restart postfix-single >/dev/null 2>&1 &");
	shell_exec($cmd);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	
}

function nsswitch(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup /usr/share/artica-postfix/bin/artica-install --nsswitch >/dev/null 2>&1 &");
	shell_exec($cmd);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);	
}

function process1(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup /usr/share/artica-postfix/bin/process1 --force --verbose ". time()." >/dev/null 2>&1 &");
	shell_exec($cmd);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);		
}

function greensql_reload(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup /usr/share/artica-postfix/bin/artica-install --greensql-reload ". time()." >/dev/null 2>&1 &");
	shell_exec($cmd);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);		
}

function changeRootPasswd(){
	$unix=new unix();
	$echo=$unix->find_program("echo");
	$passwd=base64_decode($_GET["pass"]);
	$chpasswd=$unix->find_program("chpasswd");
	$pass=$unix->shellEscapeChars($pass);
	$cmd="$echo \"root:$passwd\" | $chpasswd 2>&1";
	exec("$cmd",$results);
	writelogs_framework("$cmd " .count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	while (list ($num, $line) = each ($results)){writelogs_framework("$line",__FUNCTION__,__FILE__,__LINE__);}
	
}
function greensql_logs(){
	$unix=new unix();
	$php=$unix->LOCATE_PHP5_BIN();
	$tail=$unix->find_program("tail");
	$cmd=trim("$tail -n 300 /var/log/greensql.log 2>&1 ");
	
	exec($cmd,$results);		
	writelogs_framework($cmd ." ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
}	
