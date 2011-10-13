<?php
//http://www.alexa.com/siteowners/widgets/sitestats?
include_once('ressources/class.templates.inc');
include_once('ressources/class.ini.inc');
include_once('ressources/class.sockets.inc');

if(isset($_GET["verbose"])){
	$GLOBALS["DEBUG_TEMPLATE"]=true;
	$GLOBALS["VERBOSE"]=true;
	ini_set('display_errors', 1);
	ini_set('error_reporting', E_ALL);	
}
$GLOBALS["CURRENT_PAGE"]=CurrentPageName();

$sock=new sockets();
$title=$sock->GET_INFO("HTMLTitle");
if(trim($title)==null){$title="%s (%v)";}

$users=new usersMenus();
$title=str_replace("%s", $users->hostname, $title);
$title=str_replace("%v", @file_get_contents("VERSION"), $title);
echo $title;