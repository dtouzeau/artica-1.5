<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
$sock=new sockets();
echo "<port>".$sock->RandomPort()."</port>\n";
die();
?>