<?php

include_once('ressources/class.sockets.inc');
include_once('ressources/logs.inc');
include_once('ressources/class.crypt.php');
include_once('ressources/class.user.inc');
if(isset($_GET["path"])){
	$sock=new sockets();	
	if(strpos($_GET["path"],'..')>0){die('HACK: ..');}
	$path="{$_GET["org"]}/{$_GET["path"]}";
	$sock->download_attach($path,$_GET["file"]);
}

if(isset($_GET["xapian-file"])){
	if($_SESSION["uid"]==null){die();}
	$ct=new user($_SESSION["uid"]);
	$crypt=new SimpleCrypt($ct->password);
	$sock=new sockets();	
	$sock->download_srvfile($crypt->decrypt($_GET["xapian-file"]));
}


?>