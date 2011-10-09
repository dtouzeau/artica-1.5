<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	
	


	$user=new usersMenus();
	if($user->AllowViewStatistics==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){tabs();exit;}
	if(isset($_GET["etch"])){etch();exit;}
	


	js();
	
function js(){

	$page=CurrentPageName();
	$tpl=new templates();
	if(isset($_GET["newinterface"])){$newinterface="&newinterface=yes";}
	$title=$tpl->_ENGINE_parse_body("{network_stats}");
	
	$html="$('#BodyContent').load('$page?popup=yes$newinterface');";
	echo $html;
}

function tabs(){
	$tpl=new templates();
	$page=CurrentPageName();
	$error="<center style='font-size:18px;color:red;margin:5px;font-weight:bolder'>{NO_DATA_COME_BACK_LATER}</center><center><img src='img/report-warning-256.png'></center>";
	$array=unserialize(@file_get_contents("ressources/logs/vnstat-array.db"));
	if(!is_array($array)){echo $tpl->_ENGINE_parse_body("$error");return;}
	if(isset($_GET["newinterface"])){$newinterface="style='font-size:14px'";}
	
	
	$html=array();
	while (list ($num, $eth) = each ($array) ){
		if($eth=="command"){continue;}
		if($eth=="available"){continue;}
		$html[]= "<li><a href=\"$page?etch=$eth\"><span $newinterface>{nic}:&nbsp;$eth</span></a></li>\n";
	}	
	if(count($html)==0){echo $tpl->_ENGINE_parse_body("$error");return;}
	echo $tpl->_ENGINE_parse_body("
	<div id=main_config_vnstat style='width:100%;height:850px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_config_vnstat\").tabs();});
		</script>");		
}

function etch(){
		$interface=$_GET["etch"];
		$cmdr[]="ressources/logs/vnstat-$interface-resume.png";
		$cmdr[]="ressources/logs/vnstat-$interface-hourly.png";
		$cmdr[]="ressources/logs/vnstat-$interface-daily.png";
		$cmdr[]="ressources/logs/vnstat-$interface-monthly.png";
		$cmdr[]="ressources/logs/vnstat-$interface-top.png";
		$imgs=array();
		$tpl=new templates();
		$page=CurrentPageName();
		$error="<center style='font-size:18px;color:red;margin:5px;font-weight:bolder'>{NO_DATA_COME_BACK_LATER}</center><center><img src='img/report-warning-256.png'></center>";		
		
		while (list ($num, $filename) = each ($cmdr) ){
			if(!is_file($filename)){continue;}
			$t=time();
			$imgs[]="<center style='margin:10px'><img src='$filename?$t'></center>";
			
		}
		
		
		if(count($imgs)==0){echo $tpl->_ENGINE_parse_body("$error");return;}
		echo $tpl->_ENGINE_parse_body(@implode("\n",$imgs));
	
}


