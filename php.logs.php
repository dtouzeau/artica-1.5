<?php
include_once(dirname(__FILE__) . '/ressources/class.ldap.inc');
include_once(dirname(__FILE__) . "/ressources/class.sockets.inc");

	$user=new usersMenus();
	if($user->AsSystemAdministrator==false){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}');");
		die();exit();
	}

	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["ev"])){events();exit;}
	
	js();
	
	
function popup(){

	$div="<div style='width:100%;height:350px;overflow:auto' id='phpevents'></div>";
	$div=RoundedLightWhite($div);
	
	$html="<H1>{PHP_EVENTS}</h1>
		<p class=caption>{PHP_EVENTS_TEXT}</p>
		<br>
		$div
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}


	
function js(){
	$page=CurrentPageName();
	$prefix=str_replace('.','_',$page);
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{PHP_EVENTS}');
	$html="
	var {$prefix}timerID  = null;
	var {$prefix}tant=0;
	var {$prefix}reste=0;	
	var {$prefix}timeout=0;
	
		function {$prefix}LoadPage(){
			RTMMail(750,'$page?popup=yes','$title');
			{$prefix}ChargeTimeout();
		}
		
	function {$prefix}demarre(){
		if(!RTMMailOpen()){return false;}
		{$prefix}tant = {$prefix}tant+1;
		{$prefix}reste=5-{$prefix}tant;
			if ({$prefix}tant <10 ) {                           
				{$prefix}timerID = setTimeout(\"{$prefix}demarre()\",1000);
		      } else {
						if(!RTMMailOpen()){return false;}
						{$prefix}tant = 0;
						LoadAjax('phpevents','$page?ev=yes');
						{$prefix}demarre();
		   }
	}	
	
	function {$prefix}ChargeTimeout(){
		{$prefix}timeout={$prefix}timeout+1;
		if({$prefix}timeout>20){
			alert('time-out $page');
			return false;
		}
		
		if(!document.getElementById('phpevents')){
			setTimeout(\"{$prefix}ChargeTimeout()\",900);
			return false;
			}
		
		{$prefix}timeout=0;
		LoadAjax('phpevents','$page?ev=yes');
		{$prefix}demarre();
		
	}
	
	{$prefix}LoadPage();		
	
	";
	
	echo $html;
}

function events(){
	if(!is_file('ressources/logs/php.log')){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("alert('{error_no_datas}');");
		return false;
	}
	
	
	$datas=explode("\n",file_get_contents("ressources/logs/php.log"));
	if(!is_array($datas)){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("alert('{error_no_datas}');");
		return false;		
	}
	
	rsort($datas);
	$html="<table style='width:99%'>";
	
	while (list ($num, $val) = each ($datas) ){
		if(trim($datas)==null){continue;}
		
		if(preg_match("#\[(.+?)\]\s+([0-9:]+)\s+(.+)#",$val,$re)){
			$d=$re[1];
			$t=$re[2];
			$e=trim($re[3]);
		}else{
			if(preg_match('#([0-9A-Za-z\-])\s+([0-9:]+)\s+(.+)#',$val,$re)){
				$d=$re[1];
				$t=$re[2];
				$e=trim($re[3]);
			}else{
				if(preg_match("#\[(.+?)\s+([0-9:]+)\]\s+(.+)#",$val,$re)){
					$d=$re[1];
					$t=$re[2];
					$e=trim($re[3]);
				}else{
				$d="&nbsp;";
				$t="&nbsp;";
				$e=trim($val);
				}
			}
		}
		if($e==null){continue;}
		$t=str_replace("PHP",'',$t);
		
		$html=$html."<tr " . CellRollOver().">
		<td width=1% nowrap valign='top' style='border-bottom:1px dotted #CCCCCC'><code style='font-size:11px'>$d</code></td>
		<td  width=1% valign='top' nowrap style='border-bottom:1px dotted #CCCCCC'><code style='font-size:11px' >$t</code></td>
		<td style='border-bottom:1px dotted #CCCCCC'><code style='font-size:11px' >".htmlspecialchars($e)."</code></td>
		</tr>
		";
		
	}
	
	echo $html."</table>";
	
}

?>