<?php

include_once(dirname(__FILE__)."/frame.class.inc");
include_once(dirname(__FILE__)."/class.unix.inc");


if(isset($_GET["compile-rules"])){compile_rules();exit;}





while (list ($num, $line) = each ($_GET)){$f[]="$num=$line";}
writelogs_framework("unable to understand query !!!!!!!!!!!..." .@implode(",",$f),"main()",__FILE__,__LINE__);
die();


function compile_rules(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$php5=$unix->LOCATE_PHP5_BIN();
	$cmd=trim("$nohup $php5 /usr/share/artica-postfix/exec.squidguard.php --build >/dev/null 2>&1 &");
	shell_exec($cmd);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);	
	
}