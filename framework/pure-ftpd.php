<?php

include_once(dirname(__FILE__)."/frame.class.inc");
include_once(dirname(__FILE__)."/class.unix.inc");


if(isset($_GET["restart"])){restart();exit;}





while (list ($num, $line) = each ($_GET)){$f[]="$num=$line";}

writelogs_framework("unable to understand query !!!!!!!!!!!..." .@implode(",",$f),"main()",__FILE__,__LINE__);
die();



function restart(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup /etc/init.d/artica-postfix restart ftp >/dev/null 2>&1 &");
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);
	
}