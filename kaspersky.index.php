<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	
$usersmenus=new usersMenus();
if($usersmenus->AsArticaAdministrator==true){}else{die();exit;}		
	
	if(isset($_GET["tab"])){main_switch();exit;}

page();	
function page(){
$usersmenus=new usersMenus();
if($usersmenus->AsArticaAdministrator==true){}else{die();exit;}	
$sock=new sockets();
$datas=base64_decode($sock->getFrameWork('cmd.php?kaspersky-status=yes'));
$ini=new Bs_IniHandler();
$ini->loadString($datas);
//print_r($ini->_params);
if($ini->_params["KAV4PROXY"]["application_installed"]<>1){
	$KAV4PROXY="status_critical.gif";
}else{
	$KAV4PROXY="status_ok.gif";
}
if($ini->_params["KAVMILTER"]["application_installed"]<>1){
	$kav="status_critical.gif";
}else{
	$kav="status_ok.gif";
}
if($ini->_params["KAS_MILTER"]["application_installed"]<>1){
	$kas="status_critical.gif";
}else{
	$kas="status_ok.gif";
}

if($ini->_params["KAV4SAMBA"]["application_installed"]<>1){
	$smb="status_critical.gif";
}else{
	$smb="status_ok.gif";
}

$KAVMILTER_P=trpattern($ini->_params["KAVMILTER"]["pattern_version"]);
$kavmilter_version=$ini->_params["KAVMILTER"]["master_version"];
$kasmilter_ver=$ini->_params["KAS_MILTER"]["master_version"];

$table_version="
<table style=width:100%>


<tr> 
	<td align='center'><strong>{product}</strong></td>
	<td align='center'><strong>{installed}</strong></td>
	<td align='center'><strong>{explain}</strong></td>
</tr>

<tr>
	<td valign='top'><strong>{APP_KAV4PROXY}<br>
	{version}: {$ini->_params["KAV4PROXY"]["master_version"]}<br>
	Pattern: ".trpattern($ini->_params["KAV4PROXY"]["pattern_date"])."
	</strong></td>
	<td valign='top' align='center' width=1%><img src='img/$KAV4PROXY'><br></td>
	<td valign='top'>{APP_KAV4PROXY_TEXT}</td>
</tr>
<tr><td colspan=3><hr></td></tr>
<tr>
	<td valign='top' nowrap><strong>{APP_KAS3_MILTER}<br>
	{version}: {$ini->_params["KAS_MILTER"]["master_version"]}<br>
	Pattern: ".trpattern($ini->_params["KAS_MILTER"]["pattern_date"])."
	</strong>
	</td>
	<td valign='top' align='center' width=1%><img src='img/$kas'></td>
	<td valign='top'>{APP_KAS3_MILTER_TEXT}</td>
</tr>	
<tr><td colspan=3><hr></td></tr>
<tr>
	<td valign='top'><strong>{APP_KAVMILTER}<br>{version}: $kavmilter_version<br>Pattern: $KAVMILTER_P</strong></td>
	<td valign='top' align='center' width=1%><img src='img/$kav'></td>
	<td valign='top'>{APP_KAVMILTER_TEXT}</td>
</tr>
<tr><td colspan=3><hr></td></tr>
<tr>
	<td valign='top'> <strong>{APP_KAV4SAMBA}<br>
	{version}: {$ini->_params["KAV4SAMBA"]["master_version"]}<br>
	Pattern: ".trpattern($ini->_params["KAV4SAMBA"]["pattern_date"])."
	</strong></td>
	<td valign='top' align='center' width=1%><img src='img/$smb'></td>
	<td valign='top'>{APP_KAV4SAMBA_TEXT}</td>
</tr>	
	

</table>
";




$html="
<center style=''>


</div>
<div>
	$table_version
</div>
</center>
<br>



";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
}

function trpattern($str){
$day=substr($str,0,2);
$month=substr($str,2,2);
$year=substr($str,4,4);
$hour=substr($str,9,2);
$min=substr($str,11,2);
return "$year-$month-$day $hour:$min";	
}