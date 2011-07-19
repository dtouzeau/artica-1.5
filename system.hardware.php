<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.main_cf.inc');
	$usersmenus=new usersMenus();
if($usersmenus->AsArticaAdministrator==false){header('location:users.index.php');exit;}
if(isset($_GET["clocks"])){echo GetSysDate();exit;}
if(isset($_GET["hard"])){echo HARD();exit;}
if(isset($_GET["disk"])){echo disk();exit;}
if(isset($_GET["env"])){echo env();exit;}
if(isset($_GET["changeClock"])){changeClock();exit;}
if(isset($_GET["ConvertSystemToHard"])){ConvertSystemToHard();exit;}
if(isset($_GET["popup-index"])){popup();exit;}


js();


function js(){

$page=CurrentPageName();
$prefix=str_replace(".","_",$page);
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{hardware_info}');
	
	$users=new usersMenus();
	if(!$users->AsAnAdministratorGeneric){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}

$addons=file_get_contents("js/system.js");

$html="$addons

function {$prefix}Loadpage(){
	YahooUser('760','$page?popup-index=yes','$title');
	setTimeout('{$prefix}DisplayDivs()',900);
	}
	

	
function {$prefix}DisplayDivs(){
	LoadAjax('clocks','$page?clocks=yes');
	LoadAjax('hard','$page?hard=yes');
	LoadAjax('disk','$page?disk=yes');
	LoadAjax('env','$page?env=yes')
	}	

	
 {$prefix}Loadpage();
";
	
	echo $html;
}	
	
	
	
function popup(){
	$tpl=new templates();
	$page=CurrentPageName();
	
	$html="
	<input type='hidden' id='changeClock' value='{changeClock}'>
	<div style='width:100%;height:500px;overflow:auto'>
	<div id='clocks'></div>
	<br>
	<div id='hard'></div>
	<br>
	<div id='disk'></div>
	<br>
	<div id='env'></div>
	</div>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function HARD(){
	
$sys=new systeminfos();
	$hash=$sys->lspci();
	$tpl=new templates();
$table="
<table style='width:100%' align=center>
<tr>
	<td align='right' colspan=2>" . imgtootltip('icon_refresh-20.gif','{refresh}',"LoadAjax('hard','$page?hard=yes');")."</td>
</tr>
<tr>
<td valign='top' width=1%>
	<img src='img/hard-64.png'>
	</td>
<td valign='top'>
	<table style='width:100%' align=center>
	<tr>
		<td valign='top'>
		<h5>{hardware_info}</H5><div class=caption style='text-align:right'>{hardware_info_text}</div>
		<table style='width:99%'>
	
	";
	$img="<img src='img/fw_bold.gif'>";
	if(is_array($hash)){
	 while (list ($num, $ligne) = each ($hash) ){
			$table=$table . "
				<tr " . CellRollOver().">
				<td width=1% class=bottom>$img</td>
				<td width=10% nowrap class=bottom>$ligne</td>
				<td width=90% class=bottom>$num</td>
				</tr>
				";
				}
	}	
	$html=$html .RoundedLightWhite("$table</table>");
	echo $tpl->_ENGINE_parse_body($html);
}


function disk(){
$tpl=new templates();
	$sys=new systeminfos();
	$hash=$sys->DiskUsages();
	$page=CurrentPageName();	
	if(!is_array($hash)){return null;}
	$img="<img src='img/fw_bold.gif'>";
	$html="
	<table style='width:100%'>
<tr>
	<td align='right' colspan=2>" . imgtootltip('icon_refresh-20.gif','{refresh}',"LoadAjax('disk','$page?disk=yes');")."</td>
</tr>	
		<tr>
		<td width=1% valign='top'><img src='img/disk-64.png'></td>
		<td valign='top'>
			<H5>{disks_usage}</h5>
			<table style='width:100%' align=center>
				<tr style='background-color:#CCCCCC'>
					<td>&nbsp;</td>
					<td><strong>{Filesystem}</strong></td>
					<td><strong>{size}</strong></td>
					<td><strong>{used}</strong></td>
					<td><strong>{available}</strong></td>
					<td align='center'><strong>{pourcent}</strong></td>
					<td><strong>{mounted_on}</strong></td>
				</tr>
	";
	
	 while (list ($num, $ligne) = each ($hash) ){
	 	$html=$html . "<tr " . CellRollOver().">
	 	<td width=1% class=bottom>$img</td>
	 	<td class=bottom>{$ligne[0]}</td>
	 	<td class=bottom>{$ligne[2]}</td>
	 	<td class=bottom>{$ligne[3]}</td>
	 	<td class=bottom>{$ligne[4]}</td>
	 	<td align='center' class=bottom><strong>{$ligne[5]}</strong></td>
	 	<td class=bottom>{$ligne[6]}</td>
	 	</tr>
	 	";
	 	
	 }
	 
	 $html=$html . "</table>";
	 
	return RoundedLightWhite($tpl->_ENGINE_parse_body($html)."</td></tr></table></td></tr></table>");
	
}
function env(){
	$sys=new systeminfos();
	$tpl=new templates();
	$img="<img src='img/fw_bold.gif'>";
	$html="<H5>{environement}</h5>
	<table style='width:600px' align=center>
	<tr>
	<td><strong>{path}</strong></td>
	<td><code>" . $sys->environements() . "</td>
	</tr>
	</table>";
	return RoundedLightWhite($tpl->_ENGINE_parse_body($html));
	
	
}

function GetSysDate(){
	
	$sock=new sockets();
	$datas=$sock->getfile('CLOCKS');
	$page=CurrentPageName();
	$tb=explode('\|',$datas);
	$tpl=new templates();
	$html="
	<input type='hidden' id='clock_value' value='{$tb[0]}'>
	<input type='hidden' id='convert_clock_tooltip' value='{convert_clock_tooltip}'>
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'>
		<img src='img/chrono-64.png'>
	</td>
	<td valign='top'><H5>{clocks}</h5>
	<div class=explain>{clocks_text}</div>
		<table style='width:100%'>
		<tr>
			<td nowrap align='right'><strong>{system_clock}:</td>
			<td><code style='font-size:12px'><strong id='systemClock'>" . texttooltip($tb[0],'{change_clock_tooltip}',"Loadjs('index.time.php?settings=yes')",null,0,"font-size:12px")."</strong></code></div>
		</tr>
		<tr>
			<td nowrap align='right'><strong>{hardware_clock}</td>
			<td><code style='font-size:12px'><strong>" . texttooltip($tb[1],'{convert_clock_tooltip}','ConvertSystemToHard()') . "</code></strong>
		</tr>
		</table>		
		</td>
	</tr>
	<tr>
	<td align='right' colspan=2>" . imgtootltip('icon_refresh-20.gif','{refresh}',"LoadAjax('clocks','$page?clocks=yes');")."</td>
	</tr>
	</table>";
	return RoundedLightWhite($tpl->_ENGINE_parse_body($html));
	
}
function changeClock(){
	$clock=$_GET["changeClock"];
	$tpl=new templates();
	if(preg_match('#([0-9]+)-([0-9]+)-([0-9]+);([0-9]+):([0-9]+):([0-9]+)#',$clock,$d)){
		$sock=new sockets();
		$newclock=$d[2].$d[3].$d[4].$d[5].$d[1];
		$sock->getfile('SetSystemTime:'.$newclock);
		
	}else{
		echo $tpl->_ENGINE_parse_body('{wrong_date}');
	}
	
}
function ConvertSystemToHard(){
	$sock=new sockets();
	$sock->getfile('ConvertTimeSystemToHard');
}

	