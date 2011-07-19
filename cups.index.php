<?php

	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.cups.inc');
	include_once('ressources/class.samba.inc');	
	
$usersmenus=new usersMenus();
if(($usersmenus->AsAnAdministratorGeneric==false)){echo "alert('No privileges')";exit;}

if(isset($_GET["cups-index"])){cups_index();exit;}
if(isset($_GET["cups-scan"])){cups_scan();exit;}
if(isset($_GET["cups-add"])){cups_add();exit;}
if(isset($_GET["cups-scan-list"])){echo cups_scan_list();exit;}
if(isset($_GET["cups-models-list"])){cups_models_list();exit;}
if(isset($_GET["cups-gen-config"])){cups_gen_config();exit;}
if(isset($_GET["cups-gen-drivers"])){cups_gen_drivers();exit;}
if(isset($_GET["cups-drv-logs"])){cups_gen_drivers_logs();exit;}
if(isset($_GET["cups-gen-drivers-perform"])){cups_gen_drivers_perform();exit;}
if(isset($_GET["cups-installed"])){cups_installed();exit;}
if(isset($_GET["cups-installed-list"])){cups_installed_list();exit;}
if(isset($_GET["cups-shared-list"])){cups_shared_list();exit;}


if(isset($_GET["cups-delete-printer"])){cups_delete_printer();exit;}
if(isset($_GET["cups-delete-shared-printer"])){cups_delete_shared_printer();exit;}
if(isset($_GET["cups-delete-all-shared-printer"])){cups_delete_all_shared_printer();exit;}

if(isset($_GET["cups-add-nic-printer"])){cups_add_nic_printer();exit;}
if(isset($_GET["cups-add-printer-share"])){cups_add_printer_share();exit;}


if(isset($_GET["cups-drivers"])){cups_drivers_query();exit;}
if(isset($_GET["cups-drivers-search"])){echo cups_drivers_search();exit;}
if(isset($_GET["cups-search-drivers"])){echo cups_drivers_query();exit;}
if(isset($_GET["cups-add-printer-disk"])){cups_add_printer_disk();exit;}
if(isset($_GET["cups-installed-info"])){cups_installed_info();exit;}



js();

function js(){
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{APP_CUPS}');
	$want=$tpl->_ENGINE_parse_body('{error_want_operation}');
	$error_no_ipaddr=$tpl->_ENGINE_parse_body("{error_no_ipaddr}");
	$error_no_localization=$tpl->_ENGINE_parse_body("{error_no_localization}");
	$page=CurrentPageName();
	$start="CupsLoad();";
	if(isset($_GET["in-front-ajax"])){$start="CupsLoad2();";}
	$html="
	
var cups_timerID  = null;
var cups_tant=0;
var cups_reste=0;

function StartDrvLogs(){
   	cups_tant = cups_tant+1;
   	cups_reste=10-cups_tant;
	if (cups_tant < 10 ) {                           
      	cups_timerID = setTimeout(\"StartDrvLogs()\",4000);
     }else {
     	cups_tant = 0;
     	if(!YahooWin3Open()){
	     	if(document.getElementById('driverslogs')){
	        	LoadAjax('driverslogs','$page?cups-drv-logs=yes');
	        	StartDrvLogs();
			}
		}
	}                                
}
	
	
	function ToolTipSearchDriver(){
		YahooWin5(550,'$page?cups-search-drivers=yes','$title');
	
	}
	
   function CupsLoad(){
		YahooWin2(750,'$page?cups-index=yes','$title');
	}
	
   function CupsLoad2(){
   		$('#BodyContent').load('$page?cups-index=yes');
		
	}	
		
	function CupsConnected(){
		YahooWin3(450,'$page?cups-scan=yes','$title');
		setTimeout(\"CupsConnectedList()\",1500);
	}
	
	function CupsConnectedList(){
		LoadAjax('cups_scan','$page?cups-scan-list=yes');
	
	}
	
	function CupsAddPrinter(type,path){
		YahooWin4(550,'$page?cups-add=yes&type='+type+'&path='+path,'$title');
	}
	
	function CupsPrinterNetwork(){
		YahooWin4(550,'$page?cups-add-nic-printer=yes','$title');
	
	}
	
	function PrinterInfos(printer_name){
		YahooWin4(550,'$page?cups-installed-info='+printer_name,'$title');
	
	}
	
	function ScanVendorsPrinters(){
		var vendor=document.getElementById('vendor').value;
		LoadAjax('cups_models','$page?cups-models-list='+vendor);
	}
	
	function ShowSelectedConfig(){
		if(!document.getElementById('path')){return null;}
		var vendor=document.getElementById('vendor').value;
		var model=document.getElementById('model').value;
		var path=document.getElementById('path').value;
		var type=document.getElementById('type').value;
		LoadAjax('genconfig','$page?cups-gen-config=yes&vendor='+vendor+'&model='+model+'&path='+path+'&type='+type);
		}
		
	function CupsGenerateDriver(){
		YahooWin3(550,'$page?cups-gen-drivers=yes','$title');
	
	}
	
	function CupsInstalled(){
		YahooWin3(550,'$page?cups-installed=yes','$title');
		setTimeout(\"CupsInstalledList()\",1500);
	}
	
	function CupsInstalledList(){
	    document.getElementById('installed_printers').innerHTML='<img src=\"img/wait_verybig.gif\">';
		document.getElementById('shared_printers').innerHTML='<img src=\"img/wait_verybig.gif\">';
		LoadAjax('installed_printers','$page?cups-installed-list=yes');
		LoadAjax('shared_printers','$page?cups-shared-list=yes');
	}
	
	function CupsGenDrv(){
		if(confirm('$want')){
			var XHR = new XHRConnection();
     		document.getElementById('driverslogs').innerHTML='<img src=\"img/wait.gif\">';
      		XHR.appendData('cups-gen-drivers-perform','yes');
      		XHR.sendAndLoad('$page', 'GET');
      		StartDrvLogs();
		}
	
	}
	
	function x_UnistallPrinter(obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue);}	
		CupsInstalledList();
	}
	
	function UnistallPrinter(printer){
		if(confirm('$want')){
			var XHR = new XHRConnection();
			document.getElementById('installed_printers').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			document.getElementById('shared_printers').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			XHR.appendData('cups-delete-printer',printer);
			XHR.sendAndLoad('$page', 'GET',x_UnistallPrinter);
		}
	}
	
	
	function CupsDriversSearch(){
		YahooWin3(550,'$page?cups-drivers=yes','$title');
		}
		
	function SearchLpr(){
		var search=document.getElementById('search_driv').value;
		var tooltipsearch=document.getElementById('tooltipsearch').value;
		LoadAjax('cups_drivers','$page?cups-drivers-search='+search+'&tooltipsearch='+tooltipsearch);
	}
	
	function x_AddPrinterToDisk(obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue);}	
		YahooWin4Hide();
		CupsInstalled();
	}	
	
	
	function AddPrinterToDisk(path,driver,vender){
		var name=document.getElementById('printer_name').value;
		var localization=document.getElementById('localization').value;
		document.getElementById('add_new_printer_to_disk').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		var XHR = new XHRConnection();
		XHR.appendData('cups-add-printer-disk','yes');
		XHR.appendData('path',path);
		XHR.appendData('driver',driver);
		XHR.appendData('vender',vender);
		XHR.appendData('name',name);
		XHR.appendData('localization',localization);		
		XHR.sendAndLoad('$page', 'GET',x_AddPrinterToDisk);
	}
	
	
	function ToolTipSelect(vendor,printer,driver){
		if(document.getElementById('vendor')){document.getElementById('vendor').value=vendor;}
		
		
		if(document.getElementById('isPrinterlocal')){
			var vendor=document.getElementById('vendor').value;
			var model=driver
			var path=document.getElementById('path').value;
			var type=document.getElementById('type').value;
			document.getElementById('cups_models').innerHTML='<strong>'+printer+'</strong>';
			LoadAjax('genconfig','$page?cups-gen-config=yes&vendor='+vendor+'&model='+model+'&path='+path+'&type='+type);
			YahooWin5Hide();
			return;		
			}
		
		if(document.getElementById('cups_models')){
			LoadAjax('cups_models','$page?cups-models-list='+vendor+'&SelectedPrinter='+printer+'&DriverPath='+driver);
			YahooWin5Hide();
		}
	}
	
	function AddNicPrinterPerform(){
		var name=document.getElementById('nickname').value;
		var ipaddr=document.getElementById('ipaddr').value;
		var localization=document.getElementById('localization').value;
		
		if(ipaddr.length==0){
			alert('$error_no_ipaddr');
			return false;
		 }
		 
		if(localization.length==0){
			alert('$error_no_localization');
			return false;
		 }		 
		 
		var path='socket://'+document.getElementById('ipaddr').value+':'+document.getElementById('port').value;
		var vendor=document.getElementById('vendor').value;
		var driver=document.getElementById('path').value;
		
		var XHR = new XHRConnection();
		XHR.appendData('cups-add-printer-disk','yes');
		XHR.appendData('path',path);
		XHR.appendData('driver',driver);
		XHR.appendData('vender',vender);
		XHR.appendData('name',name);
		XHR.appendData('localization',localization);		
		XHR.sendAndLoad('$page', 'GET',x_AddPrinterToDisk);		
		}
		
	function CupsSambaShare(printer_name,driver_path){
		var XHR = new XHRConnection();
		XHR.appendData('cups-add-printer-share','yes');
		XHR.appendData('driver',driver_path);
		XHR.appendData('printer',printer_name);
		document.getElementById('shared_printers').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_UnistallPrinter);
	}
		
		
	function UnistallSharedPrinter(driver){
		if(confirm('$want')){
			var XHR = new XHRConnection();
			document.getElementById('shared_printers').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			XHR.appendData('cups-delete-shared-printer',driver);
			XHR.sendAndLoad('$page', 'GET',x_UnistallPrinter);
		}
	
	}
	
	function UninstallAllPrinters(){
		if(confirm('$want')){
			var XHR = new XHRConnection();
			document.getElementById('shared_printers').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			XHR.appendData('cups-delete-all-shared-printer','yes');
			XHR.sendAndLoad('$page', 'GET',x_UnistallPrinter);
		}
	
	}
		
	
	$start
	";
	
	echo $html;
}


function cups_add_printer_disk(){
	
	$path=$_GET["path"];
	$driver=$_GET["driver"];
	$vender=$_GET["vender"];
	
	$localization=$_GET["localization"];
	
	$name=$_GET["name"];
	$name=str_replace(" ","_",$name);
	$name=str_replace(";","_",$name);

	$localization=str_replace(" ","_",$localization);
	$localization=str_replace(";","_",$localization);

	$samba=new samba();
	$samba->SaveToLdap();
	
	$array["name"]=$name;
	$array["path"]=$path;
	$array["driver"]=$driver;
	$array["localization"]=$localization;
	
	
	$datas=base64_encode(serialize($array));
	
	$sock=new sockets();
	$datas=$sock->getFrameWork("cmd.php?cups-add-printer=yes&params=$datas");
	$return=unserialize(base64_decode($datas));
	echo implode("\n",base64_decode($return));
	
	
}

function cups_index(){
	
	$server=$_SERVER['SERVER_NAME'];
	if(preg_match('#(.+?):(.+)#',$server,$re)){$server=$re[1];}
	$connected=Paragraphe("64-printer-connected.png","{CONNECTED_PRINTERS}","{CONNECTED_PRINTERS_TEXT}","javascript:CupsConnected()");
	$drivers=Paragraphe("add-remove-64.png","{GENERATE_DRIVERS}","{GENERATE_DRIVERS_TEXT}","javascript:CupsGenerateDriver()");
	$installed=Paragraphe("64-printer-installed.png","{INSTALLED_PRINTERS}","{INSTALLED_PRINTERS_TEXT}","javascript:CupsInstalled()");
	$nic_printer=Paragraphe("64-printers-nic.png","{ADD_NETWORK_PRINTER}","{ADD_NETWORK_PRINTER_TEXT}","javascript:CupsPrinterNetwork()");
	$drivers_search=Paragraphe("64-cd-scan.png","{QUERY_DRIVERS_COLLECTION}","{QUERY_DRIVERS_COLLECTION_TEXT}","javascript:CupsDriversSearch()");
	$web=Paragraphe("64-administrative-tools.png","{ADMIN_CUPS}","{ADMIN_CUPS_TEXT}","javascript:s_PopUpFull('http://$server:631',830,600)");
	
	
	
	$samba=new samba();
	
	$html="
	<H1>{APP_CUPS}</H1>
	<p class=caption>{APP_CUPS_TEXT}</p>
	<table style='width:100%'>
	<tr>
		<td valign='top'>$connected</td>
		<td valign='top'>$installed</td>
		<td valign='top'>$drivers</td>
		
	</tr>
	<tr>
		<td valign='top'>$nic_printer</td>
		<td valign='top'>$drivers_search</td>
		<td valign='top'>$web</td>
	</tr>
	</table>
	
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function cups_scan(){
	
	$html="<H1>{CONNECTED_PRINTERS}</H1>
	<table style='width:100%'>
	<tr>
	<td valign='top'>
		<p class=caption>{CONNECTED_PRINTERS_TEXT}</p>
	</td>
	<td valign='top' width=1%>" . imgtootltip("32-recycle.png","{refresh}","CupsConnectedList()")."</td>
	</tr>
	</table>
	" . RoundedLightWhite("
	<div style='width:100%;height:200px;overflow:auto' id='cups_scan'>
	</div>")."";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
	
}

function cups_scan_list(){
	
	$cups=new cups();
	$array=$cups->ScanPrinters();
	$tpl=new templates();
	if(!is_array($array)){
		return $tpl->_ENGINE_parse_body("{NO_CONNECTED_PRINTERS}");
	}
	
	$html="<table style='width:100%'>";
	while (list ($num, $ligne) = each ($array) ){
		$show_path=urldecode("{$ligne["PATH"]}");
		$html=$html . "<tr>
			<td width=1%><img src='img/32-printer-connected.png'></td>
			<td><code style='font-size:13px;font-weight:bold'>{$ligne["TYPE"]}</code></td>
			<td><code style='font-size:13px;font-weight:bold'>{$ligne["PATH"]}</code></td>
			<td width=1%>" . imgtootltip("32-printer-connected-add.png","{add}","CupsAddPrinter('{$ligne["TYPE"]}','{$ligne["PATH"]}');")."
			
			</tr>
			";
	}
	
	$html=$html . "</table>";
	
	return $tpl->_ENGINE_parse_body($html);
	
	
}

function cups_add(){
$path=$_GET["path"];
$tpl=new templates();

if(!is_file(dirname(__FILE__).'/ressources/scan.printers.drivers.inc')){
	echo $tpl->_ENGINE_parse_body("<p style='font-size:13px;font-weight:bold;color:red'>{no_cups_drivers_scanned}</p>");
	exit;
}
include_once(dirname(__FILE__).'/ressources/scan.printers.drivers.inc');

while (list ($num, $ligne) = each ($GLOBAL_PRINTERS)){$vendors[$num]=$num;}
$vendors[null]="{select}";
array_multisort($vendors);
$vendors_field=Field_array_Hash($vendors,'vendor',null,"ScanVendorsPrinters()");



$html="<H1>$path</H1>
	<input type='hidden' id='isPrinterlocal' value='yes'>
	<input type='hidden' id='path' value='$path'>
	<input type='hidden' id='type' value='{$_GET["type"]}'>
	<table style='width:100%'>
	<tr>
	<td valign='top' class=legend>{manufacturer}:</td>
		<td>
		<table style='width:100%'>
			<tr>
			<td width=1%>$vendors_field</td>
			<td align='left' style='width:99%'>" . imgtootltip('22-banned-regex.png','{search_drivers}',"ToolTipSearchDriver();")."</td>
			</tr>
			</table>
		</td>
	</tr>
	<tr>
	<td valign='top' class=legend>{model}:</td>
	<td><span id='cups_models'></span></td>
	</tr>	
	</table>
	<div id='genconfig'></div>
	";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);		
	
}

function cups_models_list(){
	$vendor=$_GET["cups-models-list"];
	
	if(isset($_GET["SelectedPrinter"])){
		$html="
		<input type='hidden' id='path' value='{$_GET["DriverPath"]}'>
		<input type='hidden' id='model' value='{$_GET["SelectedPrinter"]}'>
		<input type='hidden' id='vender' value='$vendor'>	
		<span style='font-weight:bold'><code>{$_GET["SelectedPrinter"]}</code>
		";
		echo $html;
		exit;
		
		
	}
	
	
	include_once(dirname(__FILE__).'/ressources/scan.printers.drivers.inc');
	
	$array=$GLOBAL_PRINTERS[$vendor];
	while (list ($num, $arrayS) = each ($array)){
		$model=$num;
		while (list ($language, $file) = each ($arrayS)){
			
			$arr["$model ($language)"]="$file";
			
		}
	}
	
	array_multisort($arr);
	
	$arr["{select}"]=null;
	
	while (list ($model, $file) = each ($arr)){
		$res[$file]=$model;
		
	}
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body(Field_array_Hash($res,"model",null,"ShowSelectedConfig()"));
	
}

function cups_gen_config(){
	
	$vender=$_GET["vendor"];
	$model=$_GET["model"];
	$sock=new sockets();
	$name=$sock->getfile("PrinterPPDName:$model");
	$name=str_replace(" ","_",$name);
$html="
<div id='add_new_printer_to_disk'>
<table style='width:100%' class=table_form> 
	<tr>
	<td valign='top' class=legend>{manufacturer}:</td>
	<td>$vender</td>
	</tr>
	<tr>
	<td valign='top' class=legend>{driver}:</td>
	<td>$model</td>
	</tr>
	<td valign='top' class=legend>{path}:</td>
	<td>{$_GET["type"]}://{$_GET["path"]}</td>
	</tr>
	<tr>
		<td valign='top' class=legend>{printer_name}:</td>
		<td>" . Field_text('printer_name',$name,'width:190px')."
	<tr>
	<tr>
		<td valign='top' class=legend>{localization}:</td>
		<td>" . Field_text('localization','office','width:190px')."
	<tr>	
	
		<td colspan=2 align='right'>
			<input type='button' value='{add_this_printer}&nbsp;&raquo;' OnClick=\"javascript:AddPrinterToDisk('{$_GET["type"]}://{$_GET["path"]}','$model','$vender');\">
		</td>
	</tr>
	</table>
</div>
";

	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function cups_gen_drivers(){

	$html="<H1>{GENERATE_DRIVERS}</H1>
	<div style='float:right;margin:4px'><input type='button' OnClick=\"javascript:CupsGenDrv();\" value='{GENERATE_DRIVERS}&nbsp;&raquo;'></div>
	<p class=caption>{GENERATE_DRIVERS_TEXT}<br>{GENERATE_DRIVERS_EXPLAIN}</p>
	
	" . RoundedLightWhite("<div id='driverslogs' style='width:100%;height:300px;overflow:auto'></div>")."
	
	<div style='font-size:12px;text-align:right'><strong><a href='ressources/logs/gutenprint.log'>{download}</a></strong></div>
	";
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}


function cups_installed(){
	$html="<H1>{INSTALLED_PRINTERS}</H1>
	
	<div style='float:right;margin:3px'>" . imgtootltip("icon_sync.gif","{refresh}","CupsInstalledList()")."</div>
	<p class=caption>{GENERATE_DRIVERS_TEXT}<br>{INSTALLED_PRINTERS_TEXT}</p>
	" . RoundedLightWhite("<div id='installed_printers' style='width:100%;height:200px;overflow:auto'></div>")."
	<hr>
	<H3 style=''>{shared_printers}</H3>
	"
	. RoundedLightWhite("<div id='shared_printers' style='width:100%;height:200px;overflow:auto'></div>")."";
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function cups_installed_list(){
	$sock=new sockets();
	$datas=$sock->getfile('lpstat');
	$datas=explode("\n",$datas);
	
	$html="<table style='width:99%'>";
	
while (list ($num, $val) = each ($datas) ){
		if(trim($val)==null){continue;}	
		
		if(preg_match("#No destinations added#",$val,$re)){continue;}
		
		if(preg_match("#(.+?)\s+(.+)#",$val,$re)){
			$drv_infos=explode(';',$sock->getfile("lpstatDriverInfos:{$re[1]}"));
			$share=imgtootltip("folder-shared.gif",'{share_this_printer}',"CupsSambaShare('{$re[1]}','{$drv_infos[0]}')");
			
			
		$html=$html . "
			<tr " . CellRollOver().">
				<td width=1% valign='top'><img src='img/32-printer-connected.png'></td>
				<td nowrap valign='top'><strong style='font-size:12px'>{$re[1]}</strong><br><i>{$drv_infos[1]}</i></td>
				<td valign='top'><strong style='font-size:10px'><code>{$re[2]}</code></strong></td>
				<td valign='top'>" . imgtootltip('info-18.png','{more_infos}',"PrinterInfos('{$re[1]}');")."</td>
				<td valign='top'>$share</td>
				<td valign='top'>" . imgtootltip('ed_delete.gif','{delete}',"UnistallPrinter('{$re[1]}');")."</td>
			</tr>
			<tr>
				<td colspan=6 class=legend>{driver}:{$drv_infos[0]}<hr></td>
			</tr>
			";
		}
	}
	
	$html=$html . "</table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}

function cups_shared_list(){
	
	$samba=new samba();
	$password=$samba->GetAdminPassword("administrator");
	$sock=new sockets();
	$datas=$sock->getfile("lpShared:$password");	
	$datas=explode("\n",$datas);
	$html="<table style='width:99%'>";	
	$coiunt=0;
while (list ($num, $val) = each ($datas) ){
		if(trim($val)==null){continue;}		
		if(preg_match("#description:\[(.+?)\]#",$val,$re)){
					$drv_infos=explode(',',$re[1]);
					$printer_name=$drv_infos[2];
					$driver=$sock->getfile("lpDriverSharedName:$printer_name;$password");
					$count=$count+1;
					
				$html=$html . "
					<tr " . CellRollOver().">
						<td width=1% valign='top'><img src='img/32-printer-connected.png'></td>
						<td valign='top'><strong style='font-size:10px'><code>{$drv_infos[1]}</code></strong></td>
						<td valign='top'><strong style='font-size:10px'><code>{$drv_infos[2]}</code></strong></td>
						<td valign='top'>" . imgtootltip('ed_delete.gif','{delete}',"UnistallSharedPrinter('$driver');")."</td>
					</tr>
					<tr>
					<td class=legend colspan=4>{$drv_infos[0]} ($driver)<hr></td>
					</tr>
					";
				}
}

if($count>0){
	$button_removeall="<tr><td colspan=4 align='right'><input type='button' OnClick=\"javascript:UninstallAllPrinters();\" value='{delete_all_printers}&nbsp;&raquo;'></td></tr>";
}

$html=$html . "
	$button_removeall
</table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}


function cups_installed_info(){
	
	$printer_name=$_GET["cups-installed-info"];
	$sock=new sockets();
	$data=$sock->getfile("lpstatPrinterInfos:$printer_name");
	
	$html="
	<H1>$printer_name</H1>";
	
	$table="
	<div style='width:100%;height:300px;overflow:auto'>
	<table style='width:100%'>";
	
	$tbl=explode("\n",$data);
	while (list ($num, $val) = each ($tbl) ){
		
		if(preg_match('#.+?since#',$val)){
			$table=$table."<tr>
			<td colspan=2 align='right'>$val</td>
			</tr>";
			continue;
		}
		
		if(preg_match("#(.+?):(.*)#",$val,$re)){
			$table=$table . "
				<tr>
					<td class=legend>{$re[1]}:</td>
					<td nowrap><strong>{$re[2]}</td>
				</tr>
				";
		}else{
			$table=$table."<tr>
			<td colspan=2 align='right'>$val</td>
			</tr>";
		}
	}
		
		$table=$table."</table></div>";
		
		$html=$html . RoundedLightWhite($table);
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);		
	
	
}


function cups_gen_drivers_logs(){
	$tpl=new templates();
	$logfile=dirname(__FILE__).'/ressources/logs/gutenprint.log';
	if(!is_file($logfile)){
		return $tpl->_ENGINE_parse_body('<code>{waiting}...</code>');
	}
	
	
	$sock=new sockets();
	$datas=explode("\n",$sock->getfile("CupsDrvLogs"));
	$datas=array_reverse ($datas, TRUE);
	while (list ($num, $val) = each ($datas) ){
		if(trim($val)==null){continue;}	
		$html=$html . "<div><code style='font-size:10px'>$val</code></div>";
	}
	echo $html;
}
function cups_gen_drivers_perform(){
	$sock=new sockets();
	$sock->getfile('lpGendrv');
}

function cups_delete_printer(){
$sock=new sockets();
$response=$sock->getFrameWork("cmd.php?cups-delete-printer={$_GET["cups-delete-printer"]}");	
echo implode("\n",base64_decode($response));	
}

function cups_delete_shared_printer(){
	$sock=new sockets();
	$samba=new samba();
	$password=$samba->GetAdminPassword("administrator");
	$response=$sock->getfile("DeleteSharedPrinter:{$_GET["cups-delete-shared-printer"]};$password");	
	echo $response;		
	}
	
function cups_delete_all_shared_printer(){
	$sock=new sockets();
	$response=$sock->getfile("DeleteAllSharedPrinter");	
	echo $response;			
	}
	
function cups_add_printer_share(){
	$driver=$_GET["driver"];
	$printer=$_GET["printer"];
	$samba=new samba();
	$password=$samba->GetAdminPassword("administrator");
	$sock=new sockets();
	$response=$sock->getfile("InstallPrinterShare:$printer;$driver;$password");	
	
}


function cups_add_nic_printer(){
	
	if(!is_file(dirname(__FILE__).'/ressources/scan.printers.drivers.inc')){
		echo $tpl->_ENGINE_parse_body("<p style='font-size:13px;font-weight:bold;color:red'>{no_cups_drivers_scanned}</p>");
		exit;
	}
	include_once(dirname(__FILE__).'/ressources/scan.printers.drivers.inc');
	
	while (list ($num, $ligne) = each ($GLOBAL_PRINTERS)){$vendors[$num]=$num;}
	$vendors[null]="{select}";
	array_multisort($vendors);
	$vendors_field=Field_array_Hash($vendors,'vendor',null,"ScanVendorsPrinters()");	
		
	$form="<table style='width:100%'>
		<tr>
			<td class=legend>{network_address}:</td>
			<td>" . Field_text("ipaddr",null,"width:90px")."
		</tr>
		<tr>
			<td class=legend>{listen_port}:</td>
			<td>" . Field_text("port",9100,"width:35px")."
		</tr>			
		
		<tr>
			<td class=legend>{nickname}:</td>
			<td>" . Field_text("nickname",null,"width:90px")."
		</tr>
		<tr>
			<td class=legend>{localization}:</td>
			<td>" . Field_text("localization",null,"width:190px")."
		</tr>				
	
		<tr>
		<td valign='top' class=legend>{manufacturer}:</td>
		<td>
			<table style='width:100%'>
			<tr>
			<td width=1%>$vendors_field</td>
			<td align='left' style='width:99%'>" . imgtootltip('22-banned-regex.png','{search_drivers}',"ToolTipSearchDriver();")."</td>
			</tr>
			</table>
		</td>
		</tr>
		<tr>
		<td valign='top' class=legend>{model}:</td>
		<td><span id='cups_models'></span></td>
		</tr>	
		<tr>
			<td colspan=2 align='right'>
				<input type='button' OnClick=\"javascript:AddNicPrinterPerform();\" value=\"{ADD_NETWORK_PRINTER}&nbsp;&raquo;\">
			</td>
		</tr>
		
		
		</table>";	
		
	$html="<H1>{ADD_NETWORK_PRINTER}</H1>
		<p class=caption>{ADD_NETWORK_PRINTER_TEXT}</p>
		
		" . RoundedLightWhite("<div id='nic_printers' style='width:100%;'>$form</div>")."";
		
		
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body($html);
		
	
	
}

function cups_drivers_query(){
	$tooltipsearch="<input type='hidden' id='tooltipsearch' value='0'>";
	$cups=new cups();
	$count_drivers=$cups->DriversNumber();
	if(isset($_GET["cups-search-drivers"])){
		$tooltipsearch="<input type='hidden' id='tooltipsearch' value='1'>";
		$tooltip=true;
	}
	
	$html="<H1>{QUERY_DRIVERS_COLLECTION}</H1>
	<H3 style='width:100%;text-align:right;border-bottom:1px solid #CCCCCC;margin-bottom:5px;float:right'>$count_drivers {drivers_in_collection}</H3>
	<p class=caption>{QUERY_DRIVERS_COLLECTION_TEXT}</p>
	$tooltipsearch
	<center style='width:100%'>
	<table style='width:10%'>
	<tr>
	<td valign='top'>
		" . Field_text('search_driv',null,'width:220px',null,"SearchLpr();",null,false,null)."
	</td>
	<td valign='middle'>
		<input type='button' value='{search}&nbsp;&raquo;' OnClick=\"javascript:SearchLpr();\" style='margin:0px'>
	</td>
	</tr>
	</table>
	</center>
	<br>
	" . RoundedLightWhite("
	<div style='width:100%;height:200px;overflow:auto' id='cups_drivers'></div>")."";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
}

function cups_drivers_search($tooltip=false){
	
	
	$search=$_GET["cups-drivers-search"];
	$cups=new cups();
	$array=$cups->DriversSearch($search);
	
	if($_GET["tooltipsearch"]==1){$tooltip=true;}
	
	if(!is_array($array)){return null;}
	
	$count=0;
	$html="<table style='width:100%'>";
	while (list ($num, $tableau) = each ($array)){
		$VENDOR=$tableau["VENDOR"];
		$PRINTER=$tableau["PRINTER"];
		$DRIVER=$tableau["DRIVER"];
		
		$count=$count+1;
		if($count>100){break;}
		if($tooltip){
			$action=imgtootltip('fleche-20.png',"{select} $PRINTER","ToolTipSelect('$VENDOR','$PRINTER','$DRIVER')");
		}else{
			$action="{found}";
		}
		
		
		$html=$html."<tr>
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td><strong style='width:13px'>$num</strong></td>
			<td><strong style='width:13px'>$VENDOR</strong></td>
			<td><strong style='width:13px'>$action</strong></td>
			</tr>";
		
		
	}
	
	
		$tpl=new templates();
		return $tpl->_ENGINE_parse_body("$html</table>");	
	
}



//http://asoyeur.free.fr/linux/samba/cups.html
//http://localhost:631/help/options.html?TOPIC=Getting+Started&QUERY=
//http://cups.sourceforge.net/cups_install.html
//http://www.enterprisenetworkingplanet.com/netsysm/article.php/3621876
//http://www.cups.org/doc-1.1/sum.html#5_4
/*mkdir /usr/share/cups/model/
cp /usr/share/ppd/gutenprint/5.2/stp-bjc-MULTIPASS-MP170.5.2.ppd /usr/share/cups/model/
http://www.enterprisenetworkingplanet.com/netsysm/article.php/3621876
http://www.linuxtopia.org/online_books/network_administration_guides/samba_reference_guide/29_CUPS-printing_105.html

//sudo lpadmin -p MFC210C -E -v usb://Brother/MFC-210C -P /usr/share/cups/model/brmfc210c_cups.ppd

add driver
chmod 777 /etc/samba/printer_drivers
/usr/bin/rpcclient localhost -N -A /tmp/passd -c "adddriver \"Windows NT x86\" \"MFC-210C:pscript5.dll:MFC-210C.ppd:ps5ui.dll:pscript.hlp:NULL:RAW:pscript5.dll,MFC-210C.ppd,ps5ui.dll,pscript.hlp,pscript.ntf,cups6.ini,cupsps6.dll,cupsui6.dll\""
/usr/bin/rpcclient localhost -N -A /tmp/passd -c "setdriver MFC-210C MFC-210C"
/usr/bin/rpcclient localhost -N -A /tmp/passd -c "enumprinters"


enum driver 
pcclient localhost -N -A /tmp/passd -c "getprinter Canon_PIXMA_MP710 2"

deldriver
/var/cache/samba/printing
 rm -rf /var/lib/samba/ntprinters.tdb

*/




?>