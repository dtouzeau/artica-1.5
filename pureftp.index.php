<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.pure-ftpd.inc');
	include_once('ressources/class.system.network.inc');
	
	
	if(isset($_GET["js"])){js();exit;}
	
	$user=new usersMenus();
	if($user->PUREFTP_INSTALLED==false){header('location:users.index.php');exit();}
	if(($user->AsSystemAdministrator==false) OR ($user->AsSambaAdministrator)==false){header('location:users.index.php');exit();}
	if(isset($_GET["SaveGeneralSettings"])){SaveGeneralSettings();exit;}
	if(isset($_GET["main"])){main_switch();exit;}
	if(isset($_GET["pure-ftpd-page"])){main_js();exit;}
	if(isset($_GET["pure-js"])){main_config_pureftpd_js();exit;}
	
	
		main_page();
		
function js(){
	$user=new usersMenus();
	if($user->PUREFTP_INSTALLED==false){die("alert('product not installed')");}
	if(($user->AsSystemAdministrator==false) OR ($user->AsSambaAdministrator)==false){die("alert('No privileges')");}
	if(is_file("js/pureftpd.js")){$jsadd=file_get_contents("js/pureftpd.js");};
	$page=CurrentPageName();
	$html="
		function PureFtpdLoad(){
			YahooWin('700','$page?pure-ftpd-page=yes');		
			}
		$jsadd
	
	PureFtpdLoad();";
	
	echo $html;
}


function main_js(){
$datas=main_config_pureftpd(1);
$html="<div id='main_config_pureftpd'>
$datas
</div>
";

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
	
}
	
function main_page(){
	

	if($_GET["hostname"]==null){
		$user=new usersMenus();
		$_GET["hostname"]=$user->hostname;}
	
	$html=
	"<table style='width:100%'>
	<tr>
	<td width=1% >
		<img src='img/pure-ftpd.png' style='margin-right:50px;margin-left:50px'><br><br>
		" . applysettings("pure-ftpd")."
	</td>
	<td >
	 " . main_status() . "</td>
	</tr>
	<tr>
		<td colspan=2 >
			<div id='main_config_pureftpd'></div>
		</td>
	</tr>
	</table>
	<script>LoadAjax('main_config_pureftpd','$page?main=yes');</script>
	
	";
	
	
	$cfg["JS"][]='js/pureftpd.js';
	
	
	
	
	$tpl=new template_users('{APP_PUREFTPD}',$html,0,0,0,0,$cfg);
	
	echo $tpl->web_page;
	
	
	
}	

function main_switch(){
	
	switch ($_GET["main"]) {
		case "yes":main_config_pureftpd();exit;break;
	
		default:
			break;
	}
	
	
}


function main_config_pureftpd_js(){
	$page=CurrentPageName();
	
	$html="
	var x_SERVER_FTP_JS_START=function(obj){
      var tempvalue=obj.responseText;
      if(tempvalue.length>3){alert(tempvalue);}
      if(document.getElementById('pureftpd-animation')){document.getElementById('pureftpd-animation').innerHTML='';}
      if(document.getElementById('main_config_freeweb')){RefreshTab('main_config_freeweb');}
      }	

	function SERVER_FTP_JS_START(){
		var XHR = new XHRConnection();
		AnimateDiv('pureftpd-animation');
		XHR.appendData('SaveGeneralSettings',document.getElementById('SaveGeneralSettings').value);
		XHR.appendData('MaxIdleTime',document.getElementById('MaxIdleTime').value);
		XHR.appendData('MaxClientsNumber',document.getElementById('MaxClientsNumber').value);
		XHR.appendData('MaxClientsPerIP',document.getElementById('MaxClientsPerIP').value);
		XHR.appendData('LimitRecursion',document.getElementById('LimitRecursion').value);
		XHR.appendData('MaxLoad',document.getElementById('MaxLoad').value);
		XHR.appendData('AnonymousBandwidth',document.getElementById('AnonymousBandwidth').value);
		XHR.appendData('MaxDiskUsage',document.getElementById('MaxDiskUsage').value);
		XHR.appendData('listen_ip',document.getElementById('listen_ip').value);
		XHR.appendData('listen_port',document.getElementById('listen_port').value);
		XHR.appendData('PassivePortRange',document.getElementById('PassivePortRange1').value+'  '+document.getElementById('PassivePortRange2').value);
		if(document.getElementById('enable_pureftp').checked){XHR.appendData('enable_pureftp','1');}else{XHR.appendData('enable_pureftp','0');}
		if(document.getElementById('BrokenClientsCompatibility').checked){XHR.appendData('BrokenClientsCompatibility','yes');}else{XHR.appendData('BrokenClientsCompatibility','no');}
		if(document.getElementById('NoAnonymous').checked){XHR.appendData('NoAnonymous','yes');}else{XHR.appendData('NoAnonymous','no');}
		if(document.getElementById('AnonymousCanCreateDirs').checked){XHR.appendData('AnonymousCanCreateDirs','yes');}else{XHR.appendData('AnonymousCanCreateDirs','no');}
		if(document.getElementById('AnonymousCantUpload').checked){XHR.appendData('AnonymousCantUpload','yes');}else{XHR.appendData('AnonymousCantUpload','no');}
		if(document.getElementById('AntiWarez').checked){XHR.appendData('AntiWarez','yes');}else{XHR.appendData('AntiWarez','no');}
		if(document.getElementById('AutoRename').checked){XHR.appendData('AutoRename','yes');}else{XHR.appendData('AutoRename','no');}
		if(document.getElementById('DontResolve').checked){XHR.appendData('AutoRename','yes');}else{XHR.appendData('DontResolve','no');}
		if(document.getElementById('DisplayDotFiles').checked){XHR.appendData('DisplayDotFiles','yes');}else{XHR.appendData('DisplayDotFiles','no');}
		if(document.getElementById('ProhibitDotFilesWrite').checked){XHR.appendData('ProhibitDotFilesWrite','yes');}else{XHR.appendData('ProhibitDotFilesWrite','no');}
		if(document.getElementById('ProhibitDotFilesRead').checked){XHR.appendData('ProhibitDotFilesRead','yes');}else{XHR.appendData('ProhibitDotFilesRead','no');}
		XHR.sendAndLoad('$page', 'GET',x_SERVER_FTP_JS_START);		
	
	}
	SERVER_FTP_JS_START();
	
	";
	echo $html;
	}	
	


function main_config_pureftpd($returned=0){
	$style="style='padding:3px;font-size:13px'";
	$users=new usersMenus();
	$page=CurrentPageName();
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}
	$pure=new pureftpd();

	if(preg_match("#([0-9]+)\s+([0-9]+)#",$pure->main_array["PassivePortRange"],$re)){
		$PassivePortRange1=$re[1];
		$PassivePortRange2=$re[2];
	}else{
		$PassivePortRange1=30000;
		$PassivePortRange2=50000;		
	}

	$ip=new networking();
	$ips=$ip->ALL_IPS_GET_ARRAY();
	$ips[null]="{all}";	
	
	$html="
	<div id='pureftpd-animation'></div>
	<input type='hidden' name='hostname' id='hostname' value='$hostname'>
	<input type='hidden' name='SaveGeneralSettings' id='SaveGeneralSettings' value='yes'>
	<table style='width:100%' class=form>
	<tbody>
	
	<tr>
		<td $style class=legend nowrap >{enable_pureftpd}:</strong></td>
		<td $style >" . Field_checkbox('enable_pureftp',1,$pure->PureFtpdEnabled)."</td>
		<td $style >" . help_icon("{enable_pureftpd_text}")."</td>
	</tr>		
	
	<tr>
		<td $style class=legend nowrap >{listen_ip}:</strong></td>
		<td $style >" . Field_array_Hash($ips,'listen_ip',$pure->main_array["listen_ip"],"style:font-size:13px;padding:3px")."</td>
		<td $style >&nbsp;</td>
	</tr>	
	<tr>
		<td $style class=legend nowrap >{listen_port}:</strong></td>
		<td $style >" . Field_text('listen_port',$pure->main_array["listen_port"],"font-size:13px;width:65px")."</td>
		<td $style >&nbsp;</td>
	</tr>		
	<tr>
	<td $style class=legend nowrap >{BrokenClientsCompatibility}:</strong></td>
	<td $style >" . Field_checkbox('BrokenClientsCompatibility','yes',$pure->main_array["BrokenClientsCompatibility"])."</td>
	<td $style >" . help_icon("{BrokenClientsCompatibility_text}")."</td>
	</tr>	


	<tr>
	<td $style class=legend nowrap >{NoAnonymous}:</strong></td>
	<td $style >" . Field_checkbox('NoAnonymous','yes',$pure->main_array["NoAnonymous"])."</td>
	<td $style >" . help_icon("{NoAnonymous_text}")."</td>
	</tr>
	
	<tr>
	<td $style class=legend nowrap >{AnonymousCanCreateDirs}:</strong></td>
	<td $style >" . Field_checkbox('AnonymousCanCreateDirs','yes',$pure->main_array["AnonymousCanCreateDirs"])."</td>
	<td $style >" . help_icon("{AnonymousCanCreateDirs_text}")."</td>
	</tr>	
	
	<tr>
	<td $style class=legend nowrap >{AnonymousCantUpload}:</strong></td>
	<td $style >" . Field_checkbox('AnonymousCantUpload','yes',$pure->main_array["AnonymousCantUpload"])."</td>
	<td $style >" . help_icon("{AnonymousCantUpload_text}")."</td>
	</tr>		
	
	<tr>
	<td $style class=legend nowrap >{AntiWarez}:</strong></td>
	<td $style >" . Field_checkbox('AntiWarez','yes',$pure->main_array["AntiWarez"])."</td>
	<td $style >" . help_icon("{AntiWarez_text}")."</td>
	</tr>	

	<tr>
	<td $style class=legend nowrap >{AutoRename}:</strong></td>
	<td $style >" . Field_checkbox('AutoRename','yes',$pure->main_array["AutoRename"])."</td>
	<td $style >" . help_icon("{AutoRename_text}")."</td>
	</tr>		
	
	

	<tr>
	<td $style class=legend nowrap >{DontResolve}:</strong></td>
	<td $style >" . Field_checkbox('DontResolve','yes',$pure->main_array["DontResolve"])."</td>
	<td $style >" . help_icon("{DontResolve_text}")."</td>
	</tr>
	
	
	<tr>
	<td $style class=legend nowrap >{DisplayDotFiles}:</strong></td>
	<td $style >" . Field_checkbox('DisplayDotFiles','yes',$pure->main_array["DisplayDotFiles"])."</td>
	<td $style >" . help_icon("{DisplayDotFiles_text}")."</td>
	</tr>		
	
	<tr>
	<td $style class=legend nowrap >{ProhibitDotFilesWrite}:</strong></td>
	<td $style >" . Field_checkbox('ProhibitDotFilesWrite','yes',$pure->main_array["ProhibitDotFilesWrite"])."</td>
	<td $style >" . help_icon("{ProhibitDotFilesWrite_text}")."</td>
	</tr>

	<tr>
	<td $style class=legend nowrap >{ProhibitDotFilesRead}:</strong></td>
	<td $style >" . Field_checkbox('ProhibitDotFilesRead','yes',$pure->main_array["ProhibitDotFilesRead"])."</td>
	<td $style >" . help_icon("{ProhibitDotFilesRead_text}")."</td>
	</tr>	

	<tr>
	<td $style class=legend nowrap >{MaxIdleTime}:</strong></td>
	<td $style  colspan=2>" . Field_text('MaxIdleTime',$pure->main_array["MaxIdleTime"],'width:70px;font-size:13px;padding:3px',null,null,'{MaxIdleTime_text}')."</td>
	</tr>	
	
	<tr>
	<td $style class=legend nowrap >{MaxClientsNumber}:</strong></td>
	<td $style  colspan=2>" . Field_text('MaxClientsNumber',$pure->main_array["MaxClientsNumber"],'width:70px;font-size:13px;padding:3px',null,null,'{MaxClientsNumber_text}')."</td>
	</tr>	
	
	
	<tr>
	<td $style class=legend nowrap >{MaxClientsPerIP}:</strong></td>
	<td $style  colspan=2>" . Field_text('MaxClientsPerIP',$pure->main_array["MaxClientsPerIP"],'width:70px;font-size:13px;padding:3px',null,null,'{MaxClientsPerIP_text}')."</td>
	</tr>	

	<tr>
	<td $style class=legend nowrap >{LimitRecursion}:</strong></td>
	<td $style  colspan=2>" . Field_text('LimitRecursion',$pure->main_array["LimitRecursion"],'width:70px;font-size:13px;padding:3px',null,null,'{LimitRecursion_text}')."</td>
	</tr>	
	
	<tr>
	<td $style class=legend nowrap >{Max_Load}:</strong></td>
	<td $style  colspan=2>" . Field_text('MaxLoad',$pure->main_array["MaxLoad"],'width:70px;font-size:13px;padding:3px',null,null,'{MaxLoad_text}')."</td>
	</tr>		

	<tr>
	<td $style class=legend nowrap >{AnonymousBandwidth}:</strong></td>
	<td $style  colspan=2>" . Field_text('AnonymousBandwidth',$pure->main_array["AnonymousBandwidth"],'width:70px;font-size:13px;padding:3px',null,null,'{AnonymousBandwidth_text}')."</td>
	</tr>

	<tr>
	<td $style class=legend nowrap >{MaxDiskUsage}:</strong></td>
	<td $style  colspan=2>" . Field_text('MaxDiskUsage',$pure->main_array["MaxDiskUsage"],'width:70px;font-size:13px;padding:3px',null,null,'{MaxDiskUsage_text}')."%</td>
	</tr>

	<tr>
	<td $style class=legend nowrap >{PassivePortRange1}:</strong></td>
	<td $style  colspan=2>" . Field_text('PassivePortRange1',$PassivePortRange1,'width:70px;font-size:13px;padding:3px',null,null,'{PassivePortRange_text}')." Port</strong></td>
	</tr>		
	<tr>
	<td $style class=legend nowrap >{PassivePortRange2}:</strong></td>
	<td $style  colspan=2>" . Field_text('PassivePortRange2',$PassivePortRange2,'width:70px;font-size:13px;padding:3px',null,null,'{PassivePortRange_text}')." Port</strong></td>
	</tr>	
	
	<tr>
		<td $style class=legend  colspan=3 aling='right'>
		<hr>
		". button("{apply}","javascript:Loadjs('$page?pure-js=yes')")."	
	</td>
	</tr>
	</tbody>
	</table>
	";
	
	$tpl=new templates();
	if($returned==1){return $tpl->_ENGINE_parse_body($html);}
	echo $tpl->_ENGINE_parse_body($html);
	
}

function SaveGeneralSettings(){
	$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}	
	$pure=new pureftpd($hostname);
	$pure->PureFtpdEnabled=$_GET["enable_pureftp"];
	while (list ($num, $val) = each ($_GET) ){
		$pure->main_array[$num]=$val;
		
	}
	
	if($pure->SaveToLdap()){}
	
	
}

function main_status(){

$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}		
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString(base64_decode($sock->getFrameWork('cmd.php?pure-ftpd-status=yes')));	
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body(DAEMON_STATUS_ROUND("PUREFTPD",$ini));
	
	
	
}
	
	
?>	