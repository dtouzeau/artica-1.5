<?php

include_once(dirname(__FILE__)."/frame.class.inc");
include_once(dirname(__FILE__)."/class.unix.inc");


if(isset($_GET["db-size"])){db_size();exit;}
if(isset($_GET["recompile"])){recompile();exit;}
if(isset($_GET["recompile-all"])){recompile_all();exit;}





while (list ($num, $line) = each ($_GET)){$f[]="$num=$line";}

writelogs_framework("unable to understand query !!!!!!!!!!!..." .@implode(",",$f),"main()",__FILE__,__LINE__);
die();


function db_size(){
	$unix=new unix();
	$php=$unix->LOCATE_PHP5_BIN();
	shell_exec("$php /usr/share/artica-postfix/exec.squidguard.php --ufdbguard-status");
}

function recompile(){
	@mkdir("/etc/artica-postfix/ufdbguard.recompile-queue",644,true);
	$db=$_GET["recompile"];
	@file_put_contents("/etc/artica-postfix/ufdbguard.recompile-queue/".md5($db)."db",$db);
	
}

function recompile_all(){
	@mkdir("/etc/artica-postfix/ufdbguard.recompile-queue",644,true);
	@file_put_contents("/etc/artica-postfix/ufdbguard.reconfigure.task","#");	
}

