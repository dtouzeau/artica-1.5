<?php


	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.cups.inc');
	include_once('ressources/class.samba.inc');	

$usersmenus=new usersMenus();
if(($usersmenus->AsArticaAdministrator==false)){echo "alert('No privileges')";exit;}

if(isset($_GET["start"])){start_page();exit;}
if(isset($_GET["external-export-resource"])){save_ressource();exit;}
if(isset($_GET["external-export-progress"])){buildstatus();exit;}
if(isset($_GET["external-export-launch"])){LaunchExport();exit;}
if(isset($_GET["Status"])){echo Status($_GET["Status"]);exit;}
if(isset($_GET["GetStatus"])){ExportStatus();exit;}
if(isset($_GET["logs"])){logs();exit;}

js();


function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{wizard_export_config}');
	$prefix=str_replace(".","_",$page);
	$page=CurrentPageName();
	$html="
var {$prefix}timerID  = null;
var {$prefix}tant=0;
var {$prefix}reste=0;
var stopm='$stop_monitor';
var startm='$start_monitor';

function {$prefix}demarre(){
	{$prefix}tant = {$prefix}tant+1;
	{$prefix}reste=20-{$prefix}tant;
	if(document.getElementById('export-progress-daemon').value>99){
	{$prefix}finish();
	return false;}
	
	if ({$prefix}tant < 10 ) {                           
	{$prefix}timerID =setTimeout(\"{$prefix}demarre()\",1000);
      } else {
		{$prefix}tant = 0;
		{$prefix}LoadStatus();
		{$prefix}demarre(); 
		                              
   }
}

	function {$prefix}finish(){
	document.getElementById('wait').innerHTML='';
	ChangeStatus(100);
	}

function {$prefix}Page(){
   YahooWin3('630','$page?start=yes','$title');                
}

function ExportLogs(){
	YahooWin4('630','$page?logs=yes','$title');
}


var x_{$prefix}StartExportLaunch= function (obj) {
	var results=obj.responseText;
	if(results.length>0){
		if(results.length>0){alert(results);}
	}else{
		ExportChangeStatus(5);
		{$prefix}demarre();
	}
}


var x_{$prefix}StartExportProgress= function (obj) {
	var results=obj.responseText;
	if(results.length>0){
		document.getElementById('export-progress').innerHTML=results;
	}
	
	var XHR = new XHRConnection();
	XHR.appendData('external-export-launch','yes');
	XHR.sendAndLoad('$page', 'GET',x_{$prefix}StartExportLaunch);	
	
}

var x_{$prefix}StartExport= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	var XHR = new XHRConnection();
	XHR.appendData('external-export-progress','yes');
	XHR.sendAndLoad('$page', 'GET',x_{$prefix}StartExportProgress);
}	


function StartExport(){
	var pattern=document.getElementById('external-export-resource').value;
	if(pattern.length>0){
		var XHR = new XHRConnection();
		XHR.appendData('external-export-resource',pattern);
		XHR.sendAndLoad('$page', 'GET',x_{$prefix}StartExport);
	}
}

	function escapeVal(content,replaceWith){
		content = escape(content) 
	
			for(i=0; i<content.length; i++){
				if(content.indexOf(\"%0D%0A\") > -1){
					content=content.replace(\"%0D%0A\",replaceWith)
				}
				else if(content.indexOf(\"%0A\") > -1){
					content=content.replace(\"%0A\",replaceWith)
				}
				else if(content.indexOf(\"%0D\") > -1){
					content=content.replace(\"%0D\",replaceWith)
				}
	
			}	
		return unescape(content);
	}


	var x_{$prefix}ChangeStatus= function (obj) {
		var tempvalue=obj.responseText;
		if(document.getElementById('export-progress-number')){
			document.getElementById('export-progress-daemon').value=document.getElementById('export-progress-number').value;
		}
		document.getElementById('progression_export').innerHTML=tempvalue;
	}		
		
		
	function ExportChangeStatus(number){
		var XHR = new XHRConnection();
		XHR.appendData('Status',number);
		document.getElementById('export-progress-daemon').value=number;
		XHR.sendAndLoad('$page', 'GET',x_{$prefix}ChangeStatus);	
	}
	
	function {$prefix}LoadStatus(){
		var XHR = new XHRConnection();
		XHR.appendData('GetStatus','yes');
		XHR.sendAndLoad('$page', 'GET',x_{$prefix}ChangeStatus);
	}



{$prefix}Page();

";

echo $html;

}





function start_page(){
	
	$sock=new sockets();
	$resource=$sock->GET_INFO('InstantExportExternalResource');
	$usb="Loadjs('browse.usb.php?set-field=external-export-resource');";
	
	$html="<H1>{wizard_export_config}</H1>
	
	<table style='width:100%'>
	<tr>
		<td valign='top'><img src='img/128-export.png'>
		<td valign='top'>
			<div id='export-progress'>
			<p class=caption>{wizard_export_config_step1}</p>
			<p class=caption>{wizard_export_config_step2}</p>
			</div>
			<table style='width:100%'>
			<tr>
				<td class=legend>{selected_resource}:</td>
				<td><input type='text' id='external-export-resource' name='external-export-resource' value='$resource'\"></td>
			</tr>
			</table>			
			<table style='width:100%'>
				<tr>
					<td colspan valign='top'>".Paragraphe("64-network_drive.png","{use_network_drive}","{use_network_drive_text}","javascript:Loadjs('smb.browse.php?set-field=external-export-resource');")."</td>
					<td colspan valign='top'>".Paragraphe("64-external-drive.png","{use_usb_drive}","{use_usb_drive_text}","javascript:$usb")."</td>
				</tr>
				<tr>
					<td valign='top'>" . Paragraphe('64-hd.png','{use_local_storage}','{use_local_storage_text}',
					"javascript:Loadjs('SambaBrowse.php?no-shares=yes&field=external-export-resource&protocol=yes');")."</td>
					</td>
					<td>&nbsp;</td>
				</tr>					
				<tr>
					<td colspan=2 align='right'>
						<hr>
						<input type='button' OnClick=\"javascript:StartExport();\" value='{launch_exportation}&nbsp;&raquo;'>
					</td>
				</tr>
				</table>
					

		</td>
	</tr>
	</table>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);

}


function save_ressource(){
	$sock=new sockets();
	$sock->SET_INFO('InstantExportExternalResource',$_GET["external-export-resource"]);
	
}
function buildstatus(){
	$users=new usersMenus();
	$tpl=new templates();
	if(!$users->AsArticaAdministrator){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "<H3>$error<H3>";
		die();
	}
	$pourc=0;
	$table=Status(0);
	$color="#5DD13D";
	$html="
	<input type='hidden' id='export-progress-daemon' value='0'>
	<table style='width:100%'>
	<tr>
		<td width=1%><span id='wait'><img src='img/wait.gif'></span>
		</td>
		<td width=99%>
			<table style='width:100%'>
			<tr>
			<td>
			<div id='progression_export'>
				<div style='width:100%;background-color:white;padding-left:0px;border:1px solid $color'>
						<div style='width:{$pourc}%;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:$color'>
							<strong style='color:#BCF3D6;font-size:12px;font-weight:bold'>{$pourc}%</strong></center>
						</div>
					</div>
				</div>
			</td>
			</tr>
			</table>		
		</td>
	</tr>
	</table>
	<br>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
}
function Status($pourc,$text=null){
	if($text==null){$text="{scheduled}";}
	if($text<>null){$text="&nbsp;$text";}
$color="#5DD13D";

if($pourc>100){$pourc=100;$color="red";}

$html="
<div style='width:100%;background-color:white;padding-left:0px;border:1px solid $color'>
	<div style='width:{$pourc}%;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:$color'>
		<strong style='color:#BCF3D6;font-size:12px;font-weight:bold'>{$pourc}%</strong></center>
	</div>
</div>
	<div style='font-size:12px;font-weight:bold;text-align:center;'>
	&laquo;&laquo;<a href='#' OnClick=\"javascript:ExportLogs();\" style='font-size:12px;font-weight:bold;text-decoration:underline;'>$text</a>&raquo;&raquo;</div>
	<input type='hidden' id='export-progress-number' value='$pourc'>
";	

$tpl=new templates();
return $tpl->_ENGINE_parse_body($html);
	
}

function LaunchExport(){
	$sock=new sockets();
	$sock->getfile('LaunchExportConfiguration');
	
}

function ExportStatus(){
	if(!is_file("ressources/logs/export.status.conf")){return null;}
	$ini=new Bs_IniHandler("ressources/logs/export.status.conf");
	echo Status($ini->get('STATUS','progress'),$ini->get('STATUS','text'));
}

function logs(){
	$tpl=new templates();
	$html=$tpl->_ENGINE_parse_body("<H1>{wizard_export_config} {events}</H1>");
	
	$datas=@file_get_contents("ressources/logs/export-config.debug");
	$tbl=explode("\n",$datas);
	if(is_array($tbl)){
	while (list ($num, $ligne) = each ($tbl) ){
		if($ligne==null){continue;}
		$tb=$tb."<div style='margin:3px'><code>" .htmlspecialchars($ligne)."</code></div>\n";
	}
	}
	
	$tb=RoundedLightWhite("<div style='width:100%;height:300px;overflow:auto'>$tb</div>");
	echo $html.$tb;
	
}



?>