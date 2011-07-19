<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.dnsmasq.inc');
	include_once('ressources/class.main_cf.inc');
$usersmenus=new usersMenus();
if($usersmenus->AsPostfixAdministrator==false){header('location:users.index.php');exit;}	
if(isset($_GET["othersoptions"])){othersoptions();exit;}
if(isset($_GET["mxrecdomainfrom"])){SaveConfMx();exit;}
if(isset($_GET["mxHostsReload"])){echo Loadmxhosts();exit;}
if(isset($_GET["DnsmasqDeleteMxHost"])){DnsmasqDeleteMxHost();exit;}
if(isset($_GET["DnsMasqMxMove"])){DnsMasqMxMove();exit;}

	if(posix_getuid()<>0){
	$user=new usersMenus();
	if($user->AsDnsAdministrator==false){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("alert('{ERROR_NO_PRIVS}');");
		die();exit();
	}
}	

page();	
function page(){

	$cf=new dnsmasq();
	$page=CurrentPageName();
	$tpl=new templates();
	
	$time=time();
	
$html="
<div style='font-size:16px'>{others_options}</div>
<form name='fmm1'>
<input type='hidden' name='othersoptions' value='yes'>
<center>
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{localmx}:</td>
		<td width=1%>" . Field_key_checkbox_img('localmx',$cf->main_array["localmx"],'{enable_disable}')."</td>
		<td width=1%>". help_icon("{localmx_text}")."</td>
	</tr>
	<tr>
		<td class=legend>{selfmx}:</td>
		<td width=1% >" . Field_key_checkbox_img('selfmx',$cf->main_array["selfmx"],'{enable_disable}')."</td>
		<td width=1%>".help_icon("{selfmx_text}")."</td>
	</tr>
	<tr>
		<td nowrap class=legend>{mx-target}</td>
		<td><strong>" . Field_text('mx-target',$cf->main_array["mx-target"],"font-size:14px;padding:3px;width:210px") . "</td>
		<td width=1%>".help_icon("{mx-target_text}")."</td>
	</tr>
	<tr><td colspan=3 align='right'><hr>". button("{apply}","ParseForm('fmm1','$page',true);")."</td></tr>
</table>
</form>
</center>	


<div class=explain>{dnsmasq_DNS_records_text}<br>{mxexamples}</div>

<form name='$time'>
<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th>{mxrecdomainfrom}</th>
	<th>{mxrecdomainto}</th>
	<th>{mxheight}</th>
	<th width=1%>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>
<tr class=oddRow>
<td  width='140px'>" . Field_text('mxrecdomainfrom',null,"font-size:13px;width:140px") . "</td>
<td   width='180px'>" . Field_text('mxrecdomainto',null,"font-size:13px;width:180px") . "</td>
<td   width='100px'>" . Field_text('mxheight',null,"font-size:13px;width:100px") . "</td>
<td >". button("{add}","ParseForm('$time','$page',true);mxHostsReload()")."</td>
</tr>
</table>
</form>
</center>
<p>&nbsp;</p>
<div id='mx_hosts'>" . Loadmxhosts() . "</div>
";

echo $tpl->_ENGINE_parse_body($html);
	
}

function othersoptions(){
	unset($_GET["othersoptions"]);
	$conf=new dnsmasq();
	while (list ($key, $line) = each ($_GET) ){
		if($line<>null){
			$conf->main_array[$key]=$line;	
		}else{unset($conf->main_array[$key]);}
		}
	$conf->SaveConf(); 
}

function SaveConfMx(){
	$mxrecdomainfrom=$_GET["mxrecdomainfrom"];
	$mxrecdomainto=$_GET["mxrecdomainto"];
	$mxheight=$_GET["mxheight"];
	if($mxrecdomainfrom==null && $mxrecdomainto==null && $mxheight==null){return null;}
	$cf=new dnsmasq();
	$cf->array_mxhost[]="$mxrecdomainfrom,$mxrecdomainto,$mxheight";
	$cf->SaveConf();
}
function DnsmasqDeleteMxHost(){
	$cf=new dnsmasq();
	unset($cf->array_mxhost[$_GET["DnsmasqDeleteMxHost"]]);
	$cf->SaveConf();
	}
function DnsMasqMxMove(){
	$cf=new dnsmasq();
	$newarrar=array_move_element($cf->array_mxhost,$cf->array_mxhost[$_GET["DnsMasqMxMove"]],$_GET["move"]);
	$cf->array_mxhost=$newarrar;
	$cf->SaveConf();
	
}
function Loadmxhosts(){
	$conf=new dnsmasq();
	if(!is_array($conf->array_mxhost)){return null;}
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th>{mxrecdomainfrom}</th>
	<th>{mxrecdomainto}</th>
	<th>{mxheight}</th>
	<th width=1% colspan=4>&nbsp;</th>
	</tr>
</thead>	
	";
	
	$classtr="oddRow";
	
	while (list ($index, $line) = each ($conf->array_mxhost) ){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$m=explode(",",$line);
		$cell_up="<td width=1%>" . imgtootltip('arrow_up.gif','{up}',"DnsMasqMxMove('$index','up')") ."</td>";
		$cell_down="<td width=1%>" . imgtootltip('arrow_down.gif','{down}',"DnsMasqMxMove('$index','down')") ."</td>";		
		$html=$html . "
		<tr>
			
			<td width='140px' style='font-size:14px;font-weight:bold'>{$m[0]}</td>
			<td width='180px' style='font-size:14px;font-weight:bold'>{$m[1]}</td>
			<td width='100px' style='font-size:14px;font-weight:bold'>{$m[2]}</td>
			$cell_up
			$cell_down
			<td width='100px'>" . imgtootltip('delete-32.png','{delete}',"DnsmasqDeleteMxHost('$index');")."</td>
		</tr>";
	}
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html . "</table></center>");
	
}

	