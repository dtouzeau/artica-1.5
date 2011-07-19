<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.clamav.inc');
	
$usersmenus=new usersMenus();
if($usersmenus->AsArticaAdministrator==true){}else{die();exit;}		
	
	if(isset($_GET["tab"])){main_switch();exit;}
	if(isset($_GET["freshclam-js"])){freshclam_js();exit;}
	if(isset($_GET["freshclam-popup"])){freshclam_page();exit;}
	if(isset($_GET["MaxAttempts"])){freshclam_save();exit;}
	if(isset($_GET["freshclam-country"])){freshclam_country();exit;}
	if(isset($_GET["freshclam-mirror-add"])){freshclam_addmirror();exit;}
	if(isset($_GET["freshclam-mirrors"])){echo freshclam_mirrors();exit;}
	if(isset($_GET["freshclam-mirror-del"])){freshclam_delmirror();exit;}
	if(isset($_GET["popup"])){page();exit;}
	if(isset($_GET["popup-false"])){page_false();exit;}
	if(isset($_GET["recompile"])){recompile();exit;}
js();
function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{APP_CLAMAV}');
	$addtext=$tpl->_ENGINE_parse_body('{APP_CLAMAV_INSTALL_INFOS}');
	$RECOMPILE_CLAMAV_TEXT=$tpl->_ENGINE_parse_body('{RECOMPILE_CLAMAV_TEXT}');
	$users=new usersMenus();
	
	$start="LoadClamavConfig()";
	
	if(!$users->CLAMAV_INSTALLED){
		$start="LoadClamavConfigFalse()";
	}
	
	$html="
		function LoadClamavConfig(){
			YahooWin4('600','$page?popup=yes','$title');
		
		}
	
		function LoadClamavConfigFalse(){
			YahooWin4('600','$page?popup-false=yes','$title');
		
		}	
		
		var x_ClamavInstall= function (obj) {
			var results=obj.responseText;
			alert('$addtext\\n'+results);
			Loadjs('setup.index.php?js=yes');
			
		}		

		function ClamavInstall(){
		 var XHR = new XHRConnection();
		 XHR.appendData('install_app','APP_CLAMAV');
		 XHR.sendAndLoad('setup.index.php', 'GET',x_ClamavInstall);
		}
		
		function ShowClamavInstallLogs(){
			YahooWin5('500','setup.index.php?InstallLogs=APP_CLAMAV','$title');
		}
		
		function ClamavRecompile(){
		if(confirm('$RECOMPILE_CLAMAV_TEXT ?')){
		 	var XHR = new XHRConnection();
		 	XHR.appendData('recompile','APP_CLAMAV');
		 	XHR.sendAndLoad('$page', 'GET',x_ClamavInstall);
			}
		}
		

	
function ApplicationSetup(app){
    var XHR = new XHRConnection();
	XHR.appendData('install_app',app);
	XHR.sendAndLoad('$page', 'GET',x_ApplicationSetup);
	}		
		
	
	$start";
	
	echo $html;
	
}


function freshclam_delmirror(){
	$mirror=trim(strtolower($_GET["freshclam-mirror-del"]));
	$clam=new clamav();
	unset($clam->freshclam_mirrors_selected[$mirror]);
	$clam->freshclam_save();
	
}


function page_false(){
	
	if(is_file(dirname(__FILE__).'/ressources/install/APP_CLAMAV.dbg')){
		
		$logs="<br>".RoundedLightYellow("<center>
			<a href=\"javascript:ShowClamavInstallLogs();\" style='font-size:14px;font-weight:bold'>{INSTALL_CLAMAV_PROGRESS}</a></center>");
		
	}
	
	$img="must-install-128.png";
	$install=Paragraphe("64-install-soft.png",'{INSTALL_CLAMAV}','{INSTALL_CLAMAV_TEXT}',"javascript:ClamavInstall();");
	
	$html="<H1>{APP_CLAMAV}</H1>
	<table style='width:100%'>
	<tr>
		<td valign='top' width=1%><img src='img/$img'>
		<td valign='top'>" .RoundedLightWhite('<div style="font-size:12px">{CLAMAV_NOT_INSTALLED_CLICK_TO_INSTALL}</div>')."<br><br>
		<center>$install</center>
		$logs
		</td>
	</tr>
	</table>
	
	";
		
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
	
}

function page(){
$usersmenus=new usersMenus();
if($usersmenus->AsArticaAdministrator==true){}else{die();exit;}	


$sock=new sockets();
$datas=$sock->getfile('clamav_status');
$ini=new Bs_IniHandler();
$ini->loadString($datas);
$page=CurrentPageName();
//print_r($ini->_params);
if($ini->_params["CLAMAV"]["application_installed"]<>1){
	$clamd_img="status_critical.gif";
}else{
	$clamd_img="status_ok.gif";
}
if($ini->_params["CLAMSCAN"]["application_installed"]<>1){
	$clamds_img="status_critical.gif";
}else{
	$clamds_img="status_ok.gif";
}
if($ini->_params["CLAMAV_MILTER"]["application_installed"]<>1){
	$clamdm_img="status_critical.gif";
}else{
	$clamdm_img="status_ok.gif";
}

if($ini->_params["FRESHCLAM"]["application_installed"]<>1){
	$clamdf_img="status_critical.gif";
}else{
	$clamdf_img="status_ok.gif";
	$freshclam_settings=Paragraphe("folder-update.png","{APP_FRESHCLAM}",'{product_update_settings_text}');
	$freshclam_settings="<div style='float:left'>$freshclam_settings</div>";
}


if($ini->_params["CLAMSCAN"]["master_version"]<>null){$master_version=$ini->_params["CLAMSCAN"]["master_version"];}
if($ini->_params["CLAMSCAN"]["master_version"]<>null){$master_version=$ini->_params["CLAMSCAN"]["master_version"];}
if($ini->_params["FRESHCLAM"]["master_version"]<>null){$master_version=$ini->_params["FRESHCLAM"]["master_version"];}

if($ini->_params["CLAMSCAN"]["pattern_version"]<>null){$pattern_version=$ini->_params["CLAMSCAN"]["pattern_version"];}
if($ini->_params["CLAMSCAN"]["pattern_version"]<>null){$pattern_version=$ini->_params["CLAMSCAN"]["pattern_version"];}
if($ini->_params["FRESHCLAM"]["pattern_version"]<>null){$pattern_version=$ini->_params["FRESHCLAM"]["pattern_version"];}


$table_version="
<span style='font-size:14px;font-weight:bold'>Version $master_version / Pattern $pattern_version</span>
<table style=width:95%>
<tr style='background-color:#CCCCCC'> 
	<th>{product}</th>
	<th>{installed}</th>
	<th>{explain}</th>
</tr>

<tr>
	<td valign='top' class=legend nowrap><strong>{APP_CLAMAV}</strong></td>
	<td valign='top' align='center' width=1%><img src='img/$clamd_img'></td>
	<td valign='top' style='font-size:11px'>{APP_CLAMAV_TEXT}</td>
</tr>
<tr><td colspan=3 style='border-bottom:1px solid #CCCCCC'>&nbsp;</td></tr>
<tr>
	<td valign='top' nowrap class=legend nowrap><strong>{APP_FRESHCLAM}</strong></td>
	<td valign='top' align='center' width=1%>
		<img src='img/$clamdf_img'>
	</td>
	<td valign='top' style='font-size:11px'>
		<table style='width:100%'>
		<tr>
		<td valign='top'>{APP_FRESHCLAM_TEXT}</td>	
		<td valign='top' width=1%>" . imgtootltip("folder-update-32.png","{product_update_settings_text}","Loadjs('$page?freshclam-js=yes')")."</td>
			
		</tr>
		</table>
	</td>
</tr>	
<tr><td colspan=3 style='border-bottom:1px solid #CCCCCC'>&nbsp;</td></tr>
<tr>
	<td valign='top' class=legend nowrap><strong>{APP_CLAMSCAN}</strong></td>
	<td valign='top' align='center' width=1%><img src='img/$clamds_img'></td>
	<td valign='top' style='font-size:11px'>{APP_CLAMSCAN_TEXT}</td>
</tr>
<tr><td colspan=3 style='border-bottom:1px solid #CCCCCC'>&nbsp;</td></tr>
<tr>
	<td valign='top' class=legend nowrap> <strong>{APP_CLAMAV_MILTER}</strong></td>
	<td valign='top' align='center' width=1%><img src='img/$clamdm_img'></td>
	<td valign='top' style='font-size:11px'>{APP_CLAMAV_MILTER_TEXT}</td>
</tr>	
	

</table>

";

$recompile=Paragraphe("64-install-soft.png","{INSTALL_UPGRADE_RECOMPILE}","{RECOMPILE_CLAMAV_TEXT}","javascript:ClamavRecompile();");
$freshclam=Buildicon64('DEF_ICO_FRESHCLAM_SETTINGS');


$table_version="<div style='height:350px;overflow:auto'>$table_version".Patterns()."</div>";
$table_version=RoundedLightWhite($table_version);
$html="
<center>
<div style='border: 1px solid rgb(59, 136, 118); width: 598px; margin-top: -10px; background-color: rgb(59, 136, 118); margin-left: -10px;'>
	<img src='img/clamav_bg.png'></div>
</center>
<div style='background-color: rgb(59, 136, 118);width:595px;margin-left:-10px;padding-left:5px;margin-bottom:-10px;text-align:center'>
<center>
<div style='width:583px;margin-top:5px;margin-left:10px;background-color: rgb(59, 136, 118);margin-top:0px;'>
<table style='width:100%' class=table_form>
<tr>
<td>$recompile</td>
<td>$freshclam</td>
</tr>
</table>
<center>

		$table_version
	
</center>
</div>
</center>
<br>
</div>


";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
}


function Patterns(){
	$clam=new clamav();
	$array=$clam->LoadDatabasesStatus();
	if(!is_array($array)){return null;}
	
	$html="<table>
	<tr>
	<th>{date}</th>	
	<th>{pattern}</th>
	</tr>
		
	";
	
	while (list ($file, $date) = each ($array) ){
		$html=$html . "
		<tr>
			<td style='font-size:12px;font-weight:bold' nowrap>{$date[1]}</td>
			<td style='font-size:11px'>{$date[0]}</td>
		</tr>
		";
		
	}
	
	$html=$html . "</table>";
	return $html;
	
	
}


function freshclam_js(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{APP_FRESHCLAM}');
	$html="
		function FreshclamLoad(){
			YahooWin2(400,'$page?freshclam-popup=yes','$title');
		
		}
		
	var x_FreshClamSaveSettings= function (obj) {
		var response=obj.responseText;
		if(response){alert(response);}
    	FreshclamLoad();    
    }	

	var x_AddFreshClamMirror= function (obj) {
		var response=obj.responseText;
		if(response){alert(response);}
    	LoadAjax('mirrors_lists','$page?freshclam-mirrors=yes');  
    }    
		
		function FreshClamSaveSettings(){
			var XHR = new XHRConnection();
			XHR.appendData('MaxAttempts',document.getElementById('MaxAttempts').value);
			XHR.appendData('Checks',document.getElementById('Checks').value);
			XHR.sendAndLoad('$page', 'GET',x_FreshClamSaveSettings);	
		}
		
		function FreshClamCounty(){
			LoadAjax('mirrors-selected','$page?freshclam-country='+document.getElementById('fresh_country').value);
		}
		
		function AddFreshClamMirror(){
			document.getElementById('mirrors_lists').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
			var XHR = new XHRConnection();
			XHR.appendData('freshclam-mirror-add',document.getElementById('fresh_mirror').value);
			XHR.sendAndLoad('$page', 'GET',x_AddFreshClamMirror);			
		}
		
		function FresgClamMirrorDelete(mirror){
			document.getElementById('mirrors_lists').innerHTML='<div style=\"width:100%\"><center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center></div>';
			var XHR = new XHRConnection();
			XHR.appendData('freshclam-mirror-del',mirror);
			XHR.sendAndLoad('$page', 'GET',x_AddFreshClamMirror);	
		}				
		
		
		
		FreshclamLoad();
		
	
	";
	
	echo $html;
	
}

function freshclam_save(){
	$clam=new clamav();
	$clam->freshclam_array["MaxAttempts"]=$_GET["MaxAttempts"];
	$clam->freshclam_array["Checks"]=$_GET["Checks"];
	$clam->freshclam_save();
	
}

function freshclam_country(){
	$country=$_GET["freshclam-country"];
	$clam=new clamav();
	while (list ($index, $mirror) = each ($clam->freshclam_mirrors[$country]) ){
		$array[$mirror]=$mirror;
	}
	$array[null]="{select}";
	$tpl=new templates();
	$list=$tpl->_ENGINE_parse_body(Field_array_Hash($array,'fresh_mirror',null));
	
	echo $tpl->_ENGINE_parse_body("
	<table style='width:100%'>
	<tr>
		<td>$list</td>
		<td><input type='button' value='{add}&nbsp;&raquo;' OnClick=\"javascript:AddFreshClamMirror();\"></td>
	</tr>
	</table>
	");
	
}

function freshclam_addmirror(){
	
	$mirror=$_GET["freshclam-mirror-add"];
	$clam=new clamav();
	$clam->freshclam_mirrors_selected[$mirror]=$mirror;
	$clam->freshclam_save();
	
}

function freshclam_mirrors(){
	
	$clam=new clamav();
	$html="<br><table style='width:100%'>";
	
	if(is_array($clam->freshclam_mirrors_selected)){
		while (list ($num, $line) = each ($clam->freshclam_mirrors_selected) ){
			$html=$html . "<tr>
				<td width=1%><img src='img/fw_bold.gif'></td>
				<td><strong style=font-size:13px'><code>$num</code></td>
				<td>" . imgtootltip('ed_delete.gif','{delete}',"FresgClamMirrorDelete('$num')")."</td>
				</tr>
				";
		
		}
		
	}
	
	$html=$html . "</table>";
	$tpl=new templates();
	$html=$tpl->_ENGINE_parse_body($html);
	return RoundedLightGrey($html);
	
}

function freshclam_page(){
	$clam=new clamav();
	$page=CurrentPageName();
	$tpl=new templates();
	
	while (list ($index, $array) = each ($clam->freshclam_mirrors) ){
		$countries[$index]=$index;

		
	}
	$countries[null]="{select}";
	$contry=Field_array_Hash($countries,'fresh_country',null,"FreshClamCounty()");
	$mirrors=freshclam_mirrors();

	$html="<H1>{APP_FRESHCLAM}</H1>
	<table style='width:100%' class=table_form>
	<tr>
		<td colspan=3 align='left'><H2 style='margin:3px;text-transform:capitalize'>{settings}</H2></td>
	</tr>	
	<tr>
		<td class=legend>{MaxAttempts}:</td>
		<td>" . Field_text('MaxAttempts',$clam->freshclam_array["MaxAttempts"],'width:90px')."</td>
		<td>" . help_icon('{MaxAttempts_text}')."</td>
	</tr>
	<tr>
		<td class=legend>{Checks}:</td>
		<td>" . Field_text('Checks',$clam->freshclam_array["Checks"],'width:90px')."</td>
		<td>" . help_icon('{Checks_text}')."</td>
	</tr>	
	<tr>
		<td colspan=3 align='right'><hr><input type='button' OnClick=\"FreshClamSaveSettings();\" value='{edit}&nbsp;&raquo;'></td>
	</tr>
		
	</table>
	<br>
	<table style='width:100%' class=table_form>
	<tr>
		<td align='left'><H2 style='margin:3px;text-transform:capitalize'>{mirrors}</H2></td>
	</tr>	
	<td>$contry</td>
	<td><span id='mirrors-selected'></span></td>
	</tr>
	<tr>
		<td colspan=2><div style='width:100%;height:150px;padding:5px;overflow:auto' id='mirrors_lists'>$mirrors</div></td>
	</tr>
	</table>
	
	
	
	
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function recompile(){
	
	$sock=new sockets();
	$sock->getfile("ClamavRecompile");
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');
	
}


