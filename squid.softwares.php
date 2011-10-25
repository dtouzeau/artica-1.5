<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.groups.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.system.network.inc');
	
	
	$user=new usersMenus();
	if($user->AsSquidAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	if(isset($_GET["install-status"])){install_status();exit;}
	page();
	
	
	
	
function page(){
$page=CurrentPageName();
$tpl=new templates();	
$sock=new sockets();
$ini=new Bs_IniHandler();
$ini->loadString(@file_get_contents(dirname(__FILE__). '/ressources/index.ini'));
$users=new usersMenus();
$ArchStruct=$users->ArchStruct;
if($ArchStruct=="32"){$ArchStruct="i386";}
if($ArchStruct=="64"){$ArchStruct="amd64";}

$GlobalApplicationsStatus=$sock->APC_GET("GlobalApplicationsStatus",2);
if($GlobalApplicationsStatus==null){$GlobalApplicationsStatus=base64_decode($sock->getFrameWork('cmd.php?Global-Applications-Status=yes'));$sock->APC_SAVE("GlobalApplicationsStatus",$GlobalApplicationsStatus);$GLOBALS["GlobalApplicationsStatus"]=$GlobalApplicationsStatus;}	
$squid_version=	ParseAppli($GlobalApplicationsStatus,"APP_SQUID");
$availableversion=$ini->_params["NEXT"]["squid32-$ArchStruct"];
$actualversion=$sock->getFrameWork("squid.php?full-version=yes");
$availableversion_dansguardian=$ini->_params["NEXT"]["dansguardian2-$ArchStruct"];
$actualversion_dansguardian=$sock->getFrameWork("squid.php?full-dans-version=yes");

$html="
<H2>In dev progress, don't use ! - no 64 bits support...</H2>
<div style='font-size:18px'>{current}:&nbsp;{APP_SQUID}:&nbsp;<strong>$squid_version</strong>&nbsp;&nbsp;|&nbsp;{architecture}:&nbsp;<strong>$ArchStruct</strong></div>
<table style='width:100%;margin-top:15px'>
<tbody>
<tr>
	<td valign='top' width=1%><img src='img/bg_squid.jpg'></td>
	<td valign='top'>
			<table style='width:100%' class=form>
				<tbody>
					<tr>
						<td class=legend style='font-size:14px'>{available_software}:</td>
						<td style='font-size:14px;font-weight:bold'>{APP_SQUID2}</div></td>
					</tr>
					<tr>
						<td class=legend style='font-size:14px'>&nbsp;</td>
						<td style='font-size:14px;font-weight:bold'>$availableversion</td>
					</tr>
					<tr>
						<td class=legend style='font-size:14px'>{current}:</td>
						<td style='font-size:14px;font-weight:bold'>$actualversion</td>
					</tr>					
					
				</tbody>
				
			</table>
			<div style='font-size:12px'>{APP_SQUID_TEXT}</div>
			<p>&nbsp;</p>
			<span id='squid-install-status'></span>
			<div style='text-align:right;width:100%'>". imgtootltip("refresh-24.png","{refresh}","squid_install_status()")."</div>
	</td>
</tr>

<tr>
	<td colspan=2><hr></td>
</tr>

<tr>
	<td valign='top' width=1%><img src='img/bg_dansguardian.jpg'></td>
	<td valign='top'>
			<table style='width:100%' class=form>
				<tbody>
					<tr>
						<td class=legend style='font-size:14px'>{available_software}:</td>
						<td style='font-size:14px;font-weight:bold'>{APP_DANSGUARDIAN}</div></td>
					</tr>
					<tr>
						<td class=legend style='font-size:14px'>&nbsp;</td>
						<td style='font-size:14px;font-weight:bold'>$availableversion_dansguardian</td>
					</tr>
					<tr>
						<td class=legend style='font-size:14px'>{current}:</td>
						<td style='font-size:14px;font-weight:bold'>$actualversion_dansguardian</td>
					</tr>					
					
				</tbody>
				
			</table>
			<div style='font-size:12px'>{danseguardian_simple_intro}</div>
			<p>&nbsp;</p>
			<span id='dansguardian-install-status'></span>
			<div style='text-align:right;width:100%'>". imgtootltip("refresh-24.png","{refresh}","dansguardian_install_status()")."</div>
	</td>
</tr>




</tbody>
</table>
<script>
	function squid_install_status(){
		LoadAjaxTiny('squid-install-status','$page?install-status=yes&APPLI=APP_SQUID2');
	}
	
	function dansguardian_install_status(){
		LoadAjaxTiny('dansguardian-install-status','$page?install-status=yes&APPLI=APP_DANSGUARDIAN2');
	}	
squid_install_status();
dansguardian_install_status();
</script>
";

echo $tpl->_ENGINE_parse_body($html);
	
}	
function ParseAppli($status,$key){

if(!is_array($GLOBALS["GLOBAL_VERSIONS_CONF"])){BuildVersions();}
return $GLOBALS["GLOBAL_VERSIONS_CONF"][$key];	
}

function BuildVersions(){
	if(is_file("ressources/logs/global.versions.conf")){
		$GlobalApplicationsStatus=@file_get_contents("ressources/logs/global.versions.conf");
	}else{
		if(is_file("ressources/logs/web/global.versions.conf")){
			$GlobalApplicationsStatus=@file_get_contents("ressources/logs/web/global.versions.conf");
		}
	}
	$tb=explode("\n",$GlobalApplicationsStatus);
	while (list ($num, $line) = each ($tb) ){
		if(preg_match('#\[(.+?)\]\s+"(.+?)"#',$line,$re)){
			$GLOBALS["GLOBAL_VERSIONS_CONF"][trim($re[1])]=trim($re[2]);
		}
		
	}
}

function install_status(){
	$appname=$_GET["APPLI"];
	$ini=new Bs_IniHandler();
	$tpl=new templates();
	$dbg_exists=false;
	if(file_exists(dirname(__FILE__). "/ressources/install/$appname.ini")){
	    $data=file_get_contents(dirname(__FILE__). "/ressources/install/$appname.ini");
		$ini->loadString($data);
		$status=$ini->_params["INSTALL"]["STATUS"];
		$text_info=$ini->_params["INSTALL"]["INFO"];
		
		if(strlen($text_info)>0){$text_info="<span style='color:black;font-size:10px'>$text_info...</span>";}
		
	}else{
		//writelogs("Loading ressources/install/$appname.ini doesn't exists",__FUNCTION__,__FILE__);
	}
	
	if($status==null){$status=0;}
	if($status==0){
		
		echo $tpl->_ENGINE_parse_body("<center style='margin:10px'>".button("{install_upgrade}", "Loadjs('setup.index.progress.php?product=$appname&start-install=yes')",14)."</center>");
		return;
		
	}
	if($status>100){$color="#D32D2D";$status=100;$text='{failed}';}else{$color="#5DD13D";$text=$status.'%';}
	if($status==0){$color="transparent";}
	
	$pourc=pourcentage($status);
	$html="
	<table style='width:100%'>
	<tbody>
	<tr>
		<td>$pourc</td>
		<td style='font-size:12px;font-weight:bold;background-color:$color'>{$text}&nbsp;$text_info</td>
	</tr>
	</tbody>
	</table>
	";
	echo  $tpl->_ENGINE_parse_body($html);

}