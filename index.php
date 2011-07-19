<?php
if(!function_exists("session_start")){echo "<div style='margin:200px;padding:10px;border:2px solid red'><center><H1>
<error>&laquo;session&raquo; module is not properly loaded<BR>please restart artica-postfix web daemon using 
<br> <code>/etc/init.d/artica-postfix restart apache</code><br>or reboot this server</error>
<div style='color:red;font-size:13px'>Unable to stat session_start function</div></H1></div>";exit;}
session_start();
include_once('ressources/class.templates.inc');
include_once('ressources/class.mailboxes.inc');
include_once('ressources/class.main_cf.inc');
include_once('ressources/class.status.inc');

if(!isset($_SESSION["uid"])){header('location:logon.php');exit;}
if(isset($_SESSION["uid"])){header('location:users.index.php');exit;}










?>
