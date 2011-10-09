<?php
include_once(dirname(__FILE__)."/frame.class.inc");
include_once(dirname(__FILE__)."/class.unix.inc");
include_once(dirname(__FILE__)."/class.postfix.inc");



if(isset($_GET["force-front-end"])){force_frontend();exit;}
if(isset($_GET["cpu-check-nx"])){check_nx();exit;}



while (list ($num, $ligne) = each ($_GET) ){$a[]="$num=$ligne";}
writelogs_framework("unable to unserstand ".@implode("&",$a),__FUNCTION__,__FILE__,__LINE__);



function force_frontend(){
	$unix=new unix();
	$php5=$unix->LOCATE_PHP5_BIN();
	$nohup=$unix->find_program("nohup");
	shell_exec(trim("$nohup $php5 /usr/share/artica-postfix/exec.admin.status.postfix.flow.php --force >/dev/null 2>&1 &"));
}

function check_nx(){
	$unix=new unix();
	$check=$unix->find_program("check-bios-nx");
	if(strlen($check)<5){return;}
	exec("/usr/bin/check-bios-nx --verbose 2>&1",$results);
	echo "<articadatascgi>".base64_encode(@implode("\n",$results))."</articadatascgi>";	
}




