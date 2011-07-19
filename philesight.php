<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');

	if(isset($_GET["EnablePhileSight"])){parameters_save();exit;}
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_POST["dir"])){json_root();exit;}
	if(isset($_GET["imgphile"])){imgphile();exit;}
	if(isset($_GET["showroot"])){showroot();exit;}
	if(isset($_GET["parameters"])){parameters();exit;}
	if(isset($_GET["Refresh"])){parameters_save();exit;}
	if(isset($_GET["perform"])){perform();exit;}
js();
	
function js(){	
	$page=CurrentPageName();
	
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{APP_PHILESIGHT}');
	$error_want_operation=$tpl->_ENGINE_parse_body('{error_want_operation}');
	$apply_upgrade_help=$tpl->_ENGINE_parse_body('{apply_upgrade_help}');
	$start="LoadPhileSight();";
	if(isset($_GET["js-settings"])){$start="PhileSightParameters()";}
	
	
	
$html="
	LoadPhileSightTT=0;

	function LoadPhileSight(){
		RTMMail(800,'$page?showroot=yes','$title');
		YahooWin5(750,'$page?popup=yes','$title');
		LoadPhileSightWaitTolOAD();
	}
	
	function PhileSightParameters(){
		YahooWin6(645,'$page?parameters=yes','$title');
	}
	
	var x_PhileSightParametersSaveForm= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>0){alert(tempvalue);}
			YahooWin6Hide();
			}	
	
	function PhileSightParametersSave(){
			var XHR = new XHRConnection();
        	XHR.appendData('Refresh',document.getElementById('Refresh').value);
        	XHR.appendData('PhileSizeCpuLimit',document.getElementById('PhileSizeCpuLimit').value);
        	XHR.appendData('EnablePhileSight',document.getElementById('EnablePhileSight').value);
        	document.getElementById('PhileSightParametersSave').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_PhileSightParametersSaveForm);		
	}
	
	function LoadPhileSightWaitTolOAD(){
		if(!document.getElementById('folderTree')){
			LoadPhileSightTT=LoadPhileSightTT+1;
			if(LoadPhileSightTT>30){
				alert('timed-out');
				return;
			}
			setTimeout('LoadPhileSightWaitTolOAD()',500);
			return;
		}
		LoadPhileSightTT=0;
		initTree();
	
	}
	
	var x_PhileSightParametersSave= function (obj) {
			var tempvalue=obj.responseText;
			alert('$apply_upgrade_help');
			LoadPhileSight();
			}		
	
	function PhileSightIndex(){
		if(confirm('$error_want_operation')){
			var XHR = new XHRConnection();
			XHR.appendData('perform','yes');
			XHR.sendAndLoad('$page', 'GET',x_PhileSightIndex);	
		}
	}


	function initTree(){
	
			$('#folderTree').fileTree({ 
					root: '/', 
					script: '$page?mounted=/', 
					folderEvent: 'click', 
					expandSpeed: 750, 
					collapseSpeed: 750, 
					expandEasing: 'easeOutBounce', 
					collapseEasing: 'easeOutBounce' ,
					multiFolder: false}, function(file) {PhileSightClick(file);});

	}
	
function PhileSightClick(branch){
     if(document.getElementById('imgphile')){
		if(!RTMMailOpen()){
			RTMMail(800,'$page?showroot=yes&imgphile='+branch,'$title');
			return;
		}
        LoadAjax('imgphile','$page?imgphile='+branch);
     }else{
    	RTMMail(800,'$page?showroot=yes&imgphile='+branch,'$title');
	}
        
     return true;   
}	


$start
";	

echo $html;
}

function popup(){
	
	if(isset($_GET["imgphile"])){
		$sock=new sockets();
		$img=$sock->getFrameWork("cmd.php?philesize-img={$_GET["imgphile"]}");
	}
	
	$p=Paragraphe("philesight-64.png","{parameters}","{APP_PHILESIGHT_PARAMETERS}","javascript:PhileSightParameters()");
	$p1=Paragraphe("64-recycle.png","{index_database}","{index_database_text}","javascript:PhileSightIndex()");
	
	
	
	
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top' width=95%>
			<div id='folderTree' style='width:100%;height:500px;overflow:auto'>$img</div>
		</td>
		<td valign='top'>$p$p1</td>
	</tr>
	</table>";
	
	
	
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function showroot(){
	
	$sock=new sockets();
	$img=$sock->getFrameWork("cmd.php?philesize-img=/");
	echo RoundedLightWhite("<div id='imgphile'>$img</div>");
	
}

function json_root($path=null){
	$tpl=new templates();
	$path=$_POST["dir"];
	$text=$tpl->_ENGINE_parse_body("{APP_PHILESIGHT}");
	
	echo "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
	$page=CurrentPageName();
	$sock=new sockets();
	if($path==null){
		$path="/";
		$datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?B64-dirdir=".base64_encode('/'))));}
	else{
		$datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?B64-dirdir=". base64_encode($path))));
	}

	if(!is_array($datas)){return null;}
	echo "<li class=\"file ext_settings\"><a href=\"#\" rel=\"" . htmlentities($_POST['dir']) . "\">". htmlentities("$text: ".basename($_POST['dir']))."</a></li>";
	while (list($num,$val)=each($datas)){
		if(trim($val)==null){continue;}
			$newpath="$path/$val";
			$newpathsmb=str_replace('//','/',$newpath);
			echo "<li class=\"directory collapsed\"><a href=\"#\" rel=\"" . htmlentities($_POST['dir'] . '/'.$val) . "/\">" . $val . "</a></li>";
			
			
		}
		
}

function imgphile(){
	$sock=new sockets();
	echo $sock->getFrameWork("cmd.php?philesize-img={$_GET["imgphile"]}");
}

function parameters_save(){
	$sock=new sockets();
	$sock->SET_INFO("PhileSizeRefreshEach",$_GET["Refresh"]);
	$sock->SET_INFO("PhileSizeCpuLimit",$_GET["PhileSizeCpuLimit"]);
	$sock->SET_INFO("EnablePhileSight",$_GET["EnablePhileSight"]);
	
	}

function parameters(){
	$array["disable"]="{disable}";
	$array[30]="30mn";
	$array[60]="1h";
	$array[120]="2h";
	$array[240]="4h";
	$array[1440]="1 {day}";
	$sock=new sockets();
	
$cpulimit_array=array(
	0=>"{no_limit}",
	10=>"10%",
	20=>"20%",
	30=>"30%",
	35=>"35%",
	40=>"40%",
	45=>"45%",
	50=>"50%",
	55=>"55%",
	60=>"60%",
	65=>"65%",
	70=>"70%",
	75=>"75%",
	80=>"80%",
	85=>"85%",
	90=>"90%",
	95=>"95%",		
);	
	

	$EnablePhileSight=$sock->GET_INFO("EnablePhileSight");
	if($EnablePhileSight==null){$EnablePhileSight=0;}
	$rr=$sock->GET_INFO("PhileSizeRefreshEach");
	$PhileSizeCpuLimit=$sock->GET_INFO("PhileSizeCpuLimit");
	if($PhileSizeCpuLimit==null){$PhileSizeCpuLimit=20;}
	if($rr==null){$rr=120;}
	$refresh=Field_array_Hash($array,"Refresh",$rr);
	$PhileSizeCpuLimit=Field_array_Hash($cpulimit_array,"PhileSizeCpuLimit",$PhileSizeCpuLimit);
	if($enable==null){$enable=1;}
	
	
	$EnablePhileSightCHeck=Paragraphe_switch_img("{enable_philesight}","{enable_philesight_text}",'EnablePhileSight',$EnablePhileSight);
	
	
	$form="<strong style='font-size:13px'>{APP_PHILESIGHT_PARAMETERS}</strong>
	<div id='PhileSightParametersSave'>
	<table style='width:100%'>
	<tr>
		<td valign='top'>
		$EnablePhileSightCHeck
		</td>
	<td valign='top'>
	<table style='width:100%'>
	
	<tr>
		<td class=legend>{refresh_database_each}:</td>
		<td>$refresh</td>
	</tr>
	<tr>
		<td class=legend>{cpulimit}:</td>
		<td>$PhileSizeCpuLimit</td>
	</tr>	
	</table>
	</td>
	</tr>
	<tr>
		<td colspan=2 align='right'>
			<hr>". button("{apply}","PhileSightParametersSave()"). "
			
		</td>
	</tr>	
	</table>
	</div>";
		
	
		
	
	$html="<H1>{APP_PHILESIGHT} {parameters}</H1>
	$form";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
}
function perform(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?philesight-perform=yes");
	
}


?>