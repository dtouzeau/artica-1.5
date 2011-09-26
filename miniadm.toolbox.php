<?php
include_once(dirname(__FILE__)."/ressources/class.mini.admin.inc");
include_once(dirname(__FILE__)."/ressources/class.templates.inc");
include_once(dirname(__FILE__)."/ressources/class.user.inc");
include_once(dirname(__FILE__)."/ressources/class.langages.inc");


if(isset($_GET["script"])){echo toolbox();}



function toolbox(){
	
	$array[$_SESSION["uid"]]="RefreshCenterPanel()";
	
	if($_GET["script"]=="miniadm.www.services.php"){
		$title="myWebServices";
		$array["{myWebServices}"]="Loadjs('miniadm.www.services.php')";
	}
	if($_GET["script"]=="center-panel"){
		$array["{home}"]="RefreshCenterPanel()";
	}	
	
	if($_GET["script"]=="miniamd.user.rtmm.php"){
		$array["{postfix_realtime_events_text}"]="LoadAjax('BodyContent','miniamd.user.rtmm.php')";
	}	
	
	if($_GET["script"]=="miniadm.openvpn.client.php"){
		$array["{my_vpn_cnx}"]="LoadAjax('BodyContent','miniadm.openvpn.client.php')";
	}

	if($_GET["script"]=="my.addressbook.php"){
		$array["{my_address_book}"]="LoadAjax('BodyContent','my.addressbook.php')";
	}

	if($_GET["script"]=="miniadm.webfiltering.index.php"){
		$array["{APP_PROXY_CATS}"]="Loadjs('miniadm.webfiltering.index.php')";
	}		
		

	
	
	compiletool($array);
	
}

function compiletool($array){
$spacer="&nbsp;&raquo;&nbsp;";	
$stylehref="style='font-size:12px;text-decoration:underline'";
	while (list ($num, $val) = each ($array) ){
		$f[]="<a href=\"javascript:blur();\" OnClick=\"javascript:$val\" $stylehref>$num</a>$spacer";
		
	}
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body(@implode("",$f));
}
