<?php
include_once(dirname(__FILE__)."/frame.class.inc");
sys_events($_SERVER["REQUEST_URI"]);
if(isset($_GET["restart"])){restart();exit;}


function restart(){
	$value=exec("/etc/init.d/artica-postfix start daemon");
	sys_events($value);
	sys_THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart apache-groupware");
	
}

?>