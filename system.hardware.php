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
	
	<script>
	LoadAjax('clocks','$page?clocks=yes');
	</script>	
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function HARD(){
	
$sys=new systeminfos();
	$hash=$sys->lspci();
	$page=CurrentPageName();	
	$tpl=new templates();
$table="
<table style='width:100%' align=center>
<tr>
<td valign='top' width=1%>
	<img src='img/hard-64.png'>
	</td>
<td valign='top'>
	<table style='width:100%' align=center>
	<tr>
		<td valign='top'>
		<h5>{hardware_info}</H5><div class=explain>{hardware_info_text}</div>
		<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
		<tbody>
<thead class='thead'>

<tr>
<th>&nbsp;</th>
<th>&nbsp;</th>
</tr>
</thead>
	
	";
	$img="<img src='img/fw_bold.gif'>";
	if(is_array($hash)){
	 while (list ($num, $ligne) = each ($hash) ){
	 	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$table=$table . "
				<tr class=$classtr>
				<td width=1% nowrap class=bottom style='font-size:14px'><strong>$ligne</strong></td>
				<td width=100% class=bottom style='font-size:13px'>$num</td>
				</tr>
				";
				}
	}	
	$html=$html ."$table</tbody></table><script>LoadAjax('disk','$page?disk=yes');</script>";
	echo $tpl->_ENGINE_parse_body($html);
}


function disk(){
$tpl=new templates();
	$sys=new systeminfos();
	$hash=$sys->DiskUsages();
	$page=CurrentPageName();	
	if(!is_array($hash)){return "<script>LoadAjax('env','$page?env=yes');</script>";}
	$img="<img src='img/fw_bold.gif'>";
	$html="
	<table style='width:100%'>
<tr>
	<td align='right' colspan=2>" . imgtootltip('icon_refresh-20.gif','{refresh}',"LoadAjax('env','$page?disk=yes');")."</td>
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
	 
	 $html=$html . "</table>
	 
	 
	 ";
	 
	return $tpl->_ENGINE_parse_body($html)."</td></tr></table></td></tr></table><script>LoadAjax('disk','$page?env=yes');</script>";
	
}
function env(){
	$sys=new systeminfos();
	$tpl=new templates();
	$page=CurrentPageName();

	
	$img="<img src='img/fw_bold.gif'>";
	$html="<H5>{environement}</h5>
		<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
		<tbody>
<thead class='thead'>

<tr>
<th>{path}</th>

</tr>
</thead>
";
	$array=$sys->environements();
	 while (list ($num, $ligne) = each ($array) ){
	 if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	 	$html=$html."
	 		<tr class=$classtr>
	<td><strong>{path}</strong></td>
	<td><code>$ligne</code></td>
	</tr>";
	 }	

	
	return $tpl->_ENGINE_parse_body($html."</tbody></table>");
	
	
}

function GetSysDate(){
	
	$sock=new sockets();
	$tb=unserialize(base64_decode($sock->getFrameWork('services.php?clock=yes')));
	$page=CurrentPageName();
	
	$tpl=new templates();
	$html="
	<input type='hidden' id='clock_value' value='{$tb[0]}'>
	<input type='hidden' id='convert_clock_tooltip' value='{convert_clock_tooltip}'>
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'>". imgtootltip("64-refresh.png","{refresh}","LoadAjax('clocks','$page?clocks=yes')")."
		
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
	</table>
	<script>
	LoadAjax('hard','$page?hard=yes');
	</script>
	
	";
	return $tpl->_ENGINE_parse_body($html);
	
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

	