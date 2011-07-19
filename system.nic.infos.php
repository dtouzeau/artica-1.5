<?php

	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.system.network.inc');
	
	
//info=proc	
$usersmenus=new usersMenus();
if($usersmenus->AsSystemAdministrator==false){exit;}

if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["proc"])){popup_proc();exit;}

js();

function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$tile=$tpl->_ENGINE_parse_body('{network_hardware_infos}');
	$start="NicinfosStart()";
	
	if($_GET["info"]=='proc'){$start="ProcinfosStart()";}
	$html="
	
		function NicinfosStart(){
			YahooWin2('600','$page?popup=yes','$tile');
		}
		
		function ProcinfosStart(){
			YahooWin2('600','$page?proc=yes','$tile');
		}		
		
		$start;
	
	";
	
	echo $html;
}


function popup(){
	$infos=infos();
	$html="<span style='font-size:16px;font-weight:bold'>{network_hardware_infos_text}</span>
	<br>
	<div style='width:100%;height:400px;overflow:auto'>$infos</div>
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
	
}

function popup_proc(){
	$infos=infos_proc();
	$html="<H1>{proc_hardware_infos_text}</H1>
	<br>
	<div style='width:100%;height:400px;overflow:auto'>$infos</div>
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}

function infos(){
	
	if(!is_file("ressources/logs/LSHW.NET.HTML")){return "<H2 style='color:red'>{could_not_open_infos}</H2>";}
	$datas=file_get_contents("ressources/logs/LSHW.NET.HTML");
	return transform_datas($datas);
	
}



function infos_proc(){
	
	if(!is_file("ressources/logs/LSHW.PROC.HTML")){return "<H2 style='color:red'>{could_not_open_infos}</H2>";}
	$datas=file_get_contents("ressources/logs/LSHW.PROC.HTML");
	return transform_datas($datas);
	
}

function transform_datas($datas){
	if(preg_match("#<body>(.+?)</body>#is",$datas,$re)){$datas=$re[1];}
	$datas=str_replace("class=\"first\"","class=legend valign='top'",$datas);
	$datas=str_replace("class=\"second\"","style='font-size:12px;font-weight:bold'",$datas);
	$datas=str_replace("class=\"node\"","class=table_form style='margin:5px'",$datas);
	$datas=str_replace("class=\"node-disabled\"","class=table_form style='margin:5px'",$datas);
	$datas=str_replace("class=\"sub-first\"","class=legend",$datas);
	$datas=str_replace(">width: <","nowrap>32/64 capabilities:<",$datas);
	
	return $datas;	
}

?>