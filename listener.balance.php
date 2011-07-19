<?php
include_once(dirname(__FILE__) . "/ressources/class.sockets.inc");




if($_SERVER['HTTP_USER_AGENT']<>"artica"){exit;}

while (list ($num, $ligne) = each ($_POST) ){
	echo "POST[$num]=$ligne\n";
	
}

while (list ($num, $ligne) = each ($_GET) ){
	echo "GET[$num]=$ligne\n";
	
}


if($_FILES["crossroads"]["name"]=="crossroads.indentities.conf"){
	if(move_uploaded_file($_FILES["crossroads"]["tmp_name"], dirname(__FILE__)."/ressources/conf/{$_FILES["crossroads"]["name"]}")){
		$socks=new sockets();
		$socks->getfile("CrossRoadsApply:".dirname(__FILE__)."/ressources/conf/{$_FILES["crossroads"]["name"]}");
	}
	
}

if(isset($_GET["Check"])){
	$remote_ip=$_GET["Check"];
	move_uploaded_file($ligne[$num]["tmp_name"], dirname(__FILE__)."/ressources/conf/$remote_ip.{$ligne[$num]["name"]}");
	
	
	while (list ($num, $ligne) = each ($_FILES) ){
		move_uploaded_file($_FILES[$num]["tmp_name"], dirname(__FILE__)."/ressources/conf/$remote_ip.{$_FILES[$num]["name"]}");
	}

	
		
	
	
	
	
}


//print_r($_FILES);

?>