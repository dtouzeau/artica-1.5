<?php

include_once(dirname(__FILE__)."/frame.class.inc");
include_once(dirname(__FILE__)."/class.unix.inc");



if(isset($_GET["RefreshDrupalInfos"])){RefreshDrupalInfos();exit;}
if(isset($_GET["add-user"])){add_user();exit;}
if(isset($_GET["del-user"])){del_user();exit;}
if(isset($_GET["enable-user"])){enable_user();exit;}
if(isset($_GET["priv-user"])){priv_user();exit;}
if(isset($_GET["modules-refresh"])){modules_refresh();exit;}
if(isset($_GET["perform-orders"])){perform_orders();exit;}







while (list ($num, $line) = each ($_GET)){$f[]="$num=$line";}
writelogs_framework("unable to understand query !!!!!!!!!!!..." .@implode(",",$f),"main()",__FILE__,__LINE__);


function RefreshDrupalInfos(){
	$servername=$_GET["servername"];
	$unix=new unix();
	$cmd=$unix->LOCATE_PHP5_BIN()." /usr/share/artica-postfix/exec.freeweb.php --drupal-infos \"$servername\"";
	exec($cmd,$results);
	writelogs_framework("$cmd = " . count($results)." rows",__FUNCTION__,__FILE__,__LINE__);	
	
}
function add_user(){
	$servername=$_GET["servername"];
	$unix=new unix();
	$cmd=$unix->LOCATE_PHP5_BIN()." /usr/share/artica-postfix/exec.freeweb.php --drupal-uadd \"{$_GET["add-user"]}\" \"$servername\"";
	exec($cmd,$results);
	writelogs_framework("$cmd = " . count($results)." rows",__FUNCTION__,__FILE__,__LINE__);		
}
function del_user(){
	$servername=$_GET["servername"];
	$unix=new unix();
	$cmd=$unix->LOCATE_PHP5_BIN()." /usr/share/artica-postfix/exec.freeweb.php --drupal-udel \"{$_GET["del-user"]}\" \"$servername\"";
	exec($cmd,$results);
	writelogs_framework("$cmd = " . count($results)." rows",__FUNCTION__,__FILE__,__LINE__);		
}
function priv_user(){
	$servername=$_GET["servername"];
	$unix=new unix();
	$cmd=$unix->LOCATE_PHP5_BIN()." /usr/share/artica-postfix/exec.freeweb.php --drupal-upriv \"{$_GET["priv-user"]}\" \"{$_GET["priv"]}\" \"$servername\"";
	exec($cmd,$results);
	writelogs_framework("$cmd = " . count($results)." rows",__FUNCTION__,__FILE__,__LINE__);		
}
function enable_user(){
	$servername=$_GET["servername"];
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=$nohup." ". $unix->LOCATE_PHP5_BIN()." /usr/share/artica-postfix/exec.freeweb.php --drupal-uact \"{$_GET["enable-user"]}\" \"{$_GET["enabled"]}\" \"$servername\" >/dev/null 2>&1 &";
	shell_exec(trim($cmd));
	writelogs_framework("$cmd = " . count($results)." rows",__FUNCTION__,__FILE__,__LINE__);	
}

function perform_orders(){
	$servername=$_GET["servername"];
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=$nohup." ". $unix->LOCATE_PHP5_BIN()." /usr/share/artica-postfix/exec.freeweb.php --drupal-schedules >/dev/null 2>&1 &";
	shell_exec(trim($cmd));
	writelogs_framework("$cmd = " . count($results)." rows",__FUNCTION__,__FILE__,__LINE__);	
}

function modules_refresh(){
	$servername=$_GET["servername"];
	$unix=new unix();
	$cmd=$unix->LOCATE_PHP5_BIN()." /usr/share/artica-postfix/exec.freeweb.php --drupal-modules \"$servername\"";
	exec($cmd,$results);
	writelogs_framework("$cmd = " . count($results)." rows",__FUNCTION__,__FILE__,__LINE__);	
}

die();