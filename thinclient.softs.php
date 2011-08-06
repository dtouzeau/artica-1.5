<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	
	
	
	if(posix_getuid()==0){die();}
	
	$user=new usersMenus();
	if($user->AsVirtualBoxManager==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["services"])){services();exit;}
	if(isset($_GET["windows_manager"])){windows_drivers();exit;}
	if(isset($_GET["utilities"])){utilities_drivers();exit;}
	
	
	if(isset($_GET["sound"])){sound_drivers();exit;}
	if(isset($_GET["storage"])){storage_module();exit;}
	if(isset($_GET["module"])){save_module();exit;}
	if(isset($_GET["package"])){save_package();exit;}

	
	
	
	
js();

function js(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$title=$tpl->_ENGINE_parse_body('{services}');
	
$html="
	function ThinCLientSoftLoadpage(){
			YahooWin2('750','$page?popup=yes','$title');
			
		}
	
		
	var x_ThinClientHardWare= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);}
	}		
	
	
	function ThinClientHardWare(id){
		var XHR = new XHRConnection();
		XHR.appendData('module',id);
		if(document.getElementById('id_'+id).checked){XHR.appendData('enabled',1);}else{XHR.appendData('enabled',0);}
		XHR.sendAndLoad('$page', 'GET',x_ThinClientHardWare);	
	}	

	function ThinClientPackage(id){
		var XHR = new XHRConnection();
		XHR.appendData('package',id);
		if(document.getElementById('id_'+id).checked){XHR.appendData('enabled',1);}else{XHR.appendData('enabled',0);}
		XHR.sendAndLoad('$page', 'GET',x_ThinClientHardWare);	
	}		
	
	
	
	ThinCLientSoftLoadpage()";

echo $html;
	
}


function popup(){
	
	

	$page=CurrentPageName();
	$tpl=new templates();
	$array["services"]='{services}';
	$array["windows_manager"]='{windows_manager}';
	$array["utilities"]="{utilities}";	


	
	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_thinclient_softs style='width:100%;height:650px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_thinclient_softs').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			
			
			});
		</script>";		
	
}

function services(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$sql="SELECT `package` FROM thinclient_package_modules";
	$q=new mysql;
	$results=$q->QUERY_SQL($sql,'artica_backup');
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$modules[trim($ligne["package"])]=true;
	}	
	
$mds=array("rdesktop"=>"X RDP client for Windows Terminal Services (ver 1.6).",
"vncviewer"=>"VNC client (vncviewer)",
"ica"=>"Citrix ICA client ver. 11",
"tarantella"=>"Tarantella client support",
"ica_wfc"=>"Citrix ICA manager ver. 11",
"xnes"=>"XDM in a window client",
"rxvt"=>"Light Xterm Client (vt102)",
"xterm"=>"Xterm Client (vt220)",
"ssh"=>"Secure Shell client",
"tn5250"=>"5250 terminal emulator",
"dillo"=>"Dillo light web browser",
"nx"=>"No Machine NX client",
"2x"=>"2X client",
"java"=>"Java runtime",
"firefox"=>"Firefox web browser ver. 3.0.x",
"flash"=>"Flash plug-in ver. 9.0",
"kiosk"=>"Sets Firefox in kiosk mode (full screen requires a WM)",
"thinlinc"=>"Cendio Thinlinc terminal client ver. 3.0.0-1979",
"vmview"=>"Open-vmware view client 4.0.1");
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView'>
<thead class='thead'>
	<tr>
	<th colspan=2>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";

	$enable_service["rdesktop"]=true;

while (list ($num, $ligne) = each ($mds) ){
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	if($modules[$num]){$def=1;}else{$def=0;}
	if(!$enable_service["$num"]){
		$js[]="document.getElementById('id_$num').disabled=true;";
	}
	
	
	$html=$html."
	<tr class=$classtr>
	<td width=1%>". Field_checkbox("id_$num",1,$def,"ThinClientPackage('{$num}')")."</td>
	<td width=99% style='font-size:13px'>$ligne</td>
	</tr>
	";}
	
	
	$final_js=@implode("\n",$js);
	$html=$html."
	</tbody>
	</table>
	
	<script>
		function DisablePackagesServices(){
			$final_js	
		
		}
		
		DisablePackagesServices();
	</script>";
	echo $tpl->_ENGINE_parse_body($html);
	
}


function utilities_drivers(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$sql="SELECT `package` FROM thinclient_package_modules";
	$q=new mysql;
	$results=$q->QUERY_SQL($sql,'artica_backup');
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$modules[trim($ligne["package"])]=true;
	}	
	
$mds=array("xtdesk"=>"Adds icons to desktop",
"ttf-freefont"=>"GNU freefont true type font. Improves the looks of icewm, firefox etc. (3.5 MB)",
"www"=>"Web access to client.  From a browser: http://<IP number> The standard page is for general user settings, administrative access is on port 6800.",
"lp_server"=>"Remote printing daemon (JetDirect compatible)",
"lpr"=>"LPR Print Server, for use with samba-server package",
"lprng"=>"LPRng Print Server, supports network based printing",
"sshd"=>"Dropbear secure shell server",
"tftpd"=>"Built in tftpd server. Useful for making a tftpserver for thinstation on a WAN over a slow link.",
"samba-server"=>"Samba server FS Support, allows you to share local floppy/cdrom/hd/printer to other Windows PCs. Needs supermount for removeable media.",
"samba-client"=>"Samba smbclient, gives a shell like environment to access an samba server ",
"hdupdate"=>"Package for updating TS images on a hardisk over a network connection",
"scp"=>"Add ability to download files over internet using scp.  This package adds some networking based options for downloading configuration files or in using hdupdate package.",
"openvpn"=>"OpenVPN Client Support",
"gemplus410"=>"Card reader Gemplus 410 (Serial) and Gemplus 430 (USB)",
"ccidreader"=>"Generic USB card reader");
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView'>
<thead class='thead'>
	<tr>
	<th colspan=2>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";


while (list ($num, $ligne) = each ($mds) ){
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	if($modules[$num]){$def=1;}else{$def=0;}
	$js[]="document.getElementById('id_$num').disabled=true;";
	
	
	$html=$html."
	<tr class=$classtr>
	<td width=1%>". Field_checkbox("id_$num",1,$def,"ThinClientPackage('{$num}')")."</td>
	<td width=99% style='font-size:13px'>$ligne</td>
	</tr>
	";}
	
	
	$final_js=@implode("\n",$js);
	$html=$html."
	</tbody>
	</table>
	
	<script>
		function DisablePackagesUtilities(){
			$final_js	
		
		}
		
		DisablePackagesUtilities();
	</script>
	
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}


function windows_drivers(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$sql="SELECT `package` FROM thinclient_package_modules";
	$q=new mysql;
	$results=$q->QUERY_SQL($sql,'artica_backup');
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$modules[trim($ligne["package"])]=true;
	}		
$mds=array("blackbox"=>"Blackbox window manager.  Makes TS a light workstation.",
"icewm"=>"ICEWM window manager.  Makes TS a light workstation.",
"icewm-theme-bernstein"=>"bernstein GTK2 style",
"icewm-theme-bluecrux"=>"GTK2 style",
"icewm-theme-liquid"=>"Mac liquid style",
"icewm-theme-xp"=>"Windows XP style");	

$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView'>
<thead class='thead'>
	<tr>
	<th colspan=2>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";


while (list ($num, $ligne) = each ($mds) ){
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	if($modules[$num]){$def=1;}else{$def=0;}
	$js[]="document.getElementById('id_$num').disabled=true;";
	$html=$html."
	<tr class=$classtr>
	<td width=1%>". Field_checkbox("id_$num",1,$def,"ThinClientPackage('{$num}')")."</td>
	<td width=99% style='font-size:13px'>$ligne</td>
	</tr>
	";}
	
	$final_js=@implode("\n",$js);
	$html=$html."
	</tbody>
	</table>
	
	<script>
		function DisablePackagesWindows(){
			$final_js	
		
		}
		
		DisablePackagesWindows();
	</script>";
	echo $tpl->_ENGINE_parse_body($html);
	
}


function save_package(){
	$q=new mysql();
	$module=$_GET["package"];
		
$mds=array("blackbox"=>"Blackbox window manager.  Makes TS a light workstation.",
"icewm"=>"ICEWM window manager.  Makes TS a light workstation.",
"icewm-theme-bernstein"=>"Select ONE icewm theme package.",
"icewm-theme-bluecrux"=>"GTK2 style",
"icewm-theme-liquid"=>"Mac liquid style",
"icewm-theme-xp"=>"Windows XP style");	


if($mds[$module]<>null){
	while (list ($num, $ligne) = each ($mds) ){
		$sql="DELETE FROM thinclient_package_modules WHERE `package`='$num';";
		$q->QUERY_SQL($sql,'artica_backup');
	}
}
	
	if($_GET["enabled"]==1){
		$sql="INSERT INTO thinclient_package_modules (`package`) VALUES ('$module');";
	}else{
		$sql="DELETE FROM thinclient_package_modules WHERE `package`='$module';";
	}
	$q->QUERY_SQL($sql,'artica_backup');
	if(!$q->ok){echo $q->mysql_error;}	

	if($module=="xorg6-via"){
		$sql="DELETE FROM thinclient_package_modules WHERE `package`='xorg6-openchrome';";
		$q->QUERY_SQL($sql,'artica_backup');
		$sql="DELETE FROM thinclient_package_modules WHERE `package`='xorg6-unichrome';";
		$q->QUERY_SQL($sql,'artica_backup');		
	}
	
	if($module=="xorg6-unichrome"){
		$sql="DELETE FROM thinclient_package_modules WHERE `package`='xorg6-via';";
		$q->QUERY_SQL($sql,'artica_backup');
	}	

	  
	
}


?>