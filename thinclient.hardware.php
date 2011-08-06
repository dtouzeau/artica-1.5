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
	if(isset($_GET["network"])){network();exit;}
	if(isset($_GET["usb"])){usb_drivers();exit;}
	if(isset($_GET["sound"])){sound_drivers();exit;}
	if(isset($_GET["storage"])){storage_module();exit;}
	if(isset($_GET["module"])){save_module();exit;}
	if(isset($_GET["package"])){save_package();exit;}
	if(isset($_GET["keyboard"])){keyboard_drivers();exit;}
	if(isset($_GET["video"])){video_drivers();exit;}
	if(isset($_GET["filesystem"])){filesystem_drivers();exit;}
	if(isset($_GET["rebuild-default-warn"])){buildDefault();exit;}
	
	
	
js();

function js(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$title=$tpl->_ENGINE_parse_body('{thinclient_hardware}');
	$rebuild_default_warning_modules=$tpl->javascript_parse_text("{rebuild_default_warning_modules}");
	
$html="
	function ThinCLientHardwareLoadpage(){
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
	
	var x_RebuildDefaultModules= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);}
		RefreshTab('main_config_thinclient_hardware');
	}		
	
	

	function RebuildDefaultModules(){
		if(confirm('$rebuild_default_warning_modules')){
			var XHR = new XHRConnection();
			XHR.appendData('rebuild-default-warn','yes');
			XHR.sendAndLoad('$page', 'GET',x_RebuildDefaultModules);		
		
		}
	}
	
	
	
	ThinCLientHardwareLoadpage()";

echo $html;
	
}


function popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$array["network"]='{network_drivers}';
	$array["video"]="{video_drivers}";
	$array["usb"]='{usb_drivers}';
	$array["sound"]='{sound_drivers}';
	$array["keyboard"]="{keyboard}";
	$array["storage"]='{storage_drivers}';
	$array["filesystem"]='{filesystem}';


	
	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_thinclient_hardware style='width:100%;height:650px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_thinclient_hardware').tabs({
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

function filesystem_drivers(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$sql="SELECT `package` FROM thinclient_package_modules";
	$q=new mysql;
	$results=$q->QUERY_SQL($sql,'artica_backup');
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$modules[trim($ligne["package"])]=true;
	}	
	
	$mds=array("autofs4"=>"Automount and autofs support",
"isofs"=>"ISO9960 file system support for CDRoms",
"udf"=>"ISO13346 (UDF) file system",
"vfat"=>"Fat and VFat file system support",
"ntfs"=>"NTFS file system support",
"ext2"=>"Ext2 file system support",
"ext3"=>"Ext3 file system support",
"supermount"=>"Supermount support for auto unmounting of removable media",
"nfs"=>"NFS file system support",
"smbfs"=>"Samba client FS Support, allows you to mount smb filesystems");
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView'>
<thead class='thead'>
	<tr>
	<th colspan=2>&nbsp;</th>
	</tr>
</thead>virtualbox_all_status
<tbody class='tbody'>";


while (list ($num, $ligne) = each ($mds) ){
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	if($modules[$num]){$def=1;}else{$def=0;}
	$html=$html."
	<tr class=$classtr>
	<td width=1%>". Field_checkbox("id_$num",1,$def,"ThinClientPackage('{$num}')")."</td>
	<td width=99% style='font-size:13px'>$ligne</td>
	</tr>
	";}
	
	$html=$html."
	</tbody>
	</table>
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}

function video_drivers(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$sql="SELECT `package` FROM thinclient_package_modules";
	$q=new mysql;
	$results=$q->QUERY_SQL($sql,'artica_backup');
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$modules[trim($ligne["package"])]=true;
	}		
$mds=array(	
"xorg6-apm"=>"Alliance ProMotion video driver ",
"xorg6-ark"=>"Ark Logic video driver",
"xorg6-ati"=>"ATI video driver",
"xorg6-chips"=>"Chips and Technologies video driver ",
"xorg6-cirrus"=>"Cirrus Logic video driver",
"xorg6-cyrix"=>"Cyrix video driver",
"xorg6-glint"=>"GLINT/Permedia video driver ",
"xorg6-i128"=>"Number 9 I128 video driver ",
"xorg6-i740"=>"Intel i740 video driver ",
"xorg6-i810"=>"Intel 8xx integrated graphics chipsets ",
"xorg6-mga"=>"Matrox video driver ",
"xorg6-neomagic"=>"Neomagic video driver ",
"xorg6-nsc"=>"Nsc video driver ",
"xorg6-nv"=>"NVIDIA video driver",
"xorg6-nvidia"=>"Driver for modern nVidia cards (ver. 185.18.14)",
"xorg6-r128"=>"ATI Rage 128 video driver ",
"xorg6-radeon"=>"ATI RADEON video driver ",
"xorg6-rendition"=>"Rendition video driver ",
"xorg6-s3"=>"S3 video driver",
"xorg6-s3virge"=>"S3 ViRGE video driver ",
"xorg6-savage"=>"S3 Savage video driver ",
"xorg6-siliconmotion"=>"Silicon Motion video driver ",
"xorg6-sis"=>"SiS video driver",
"xorg6-tdfx"=>"3Dfx video driver ",
"xorg6-tga"=>"DEC TGA video driver",
"xorg6-trident"=>"Trident video driver",
"xorg6-tseng"=>"Tseng Labs video driver ",
"xorg6-vesa"=>"Generic VESA driver, {use_this_dont_know}",
"xorg6-vga"=>"VGA 320x200 8 bit",
"xorg6-via"=>"Legacy VIA driver",
"xorg6-unichrome"=>"VIA unichrome for CLE266, KM400/KN400, K8M800/K8N800, PM800/PM880/CN400,P4M800PRO, CX700, K8M890, P4M890, CN750, P4M900, VX800.",
"xorg6-vmware"=>"VMware SVGA video driver ",
"z-xorg6-intel"=>"supports i740, i810, i915, i945, i950 and new models of GMA graphics like GMA3000 Do not use manual X settings with this (only auto probe).",
"z-915resolution"=>"adds support for 1440x900 and 1680x1050 modes. See http://915resolution.mango-lang.org/");



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
	$html=$html."
	<tr class=$classtr>
	<td width=1%>". Field_checkbox("id_$num",1,$def,"ThinClientPackage('{$num}')")."</td>
	<td width=99% style='font-size:13px'>$ligne</td>
	</tr>
	";}
	
	$html=$html."
	</tbody>
	</table>
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}


function keyboard_drivers(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$sql="SELECT `package` FROM thinclient_package_modules";
	$q=new mysql;
	$results=$q->QUERY_SQL($sql,'artica_backup');
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$modules[trim($ligne["package"])]=true;
	}		
$mds=array("keymaps-ar"=>"Arabic",
"keymaps-cs"=>"Czech",
"keymaps-da"=>"Danish",
"keymaps-de"=>"German",
"keymaps-de_ch"=>"German-Switzerland",
"keymaps-en_gb"=>"English-Great Britian",
"keymaps-en_in"=>"English-United States International",
"keymaps-en_nz "=>"English-New Zealand",
"keymaps-en_us"=>"English-United States",
"keymaps-es"=>"Spanish",
"keymaps-et"=>"Estonian",
"keymaps-fr_be"=>"French-Belgium",
"keymaps-fr_ca"=>"French-Canada",
"keymaps-fr_ch"=>"French-Switzerland",
"keymaps-fr"=>"French",
"keymaps-hr"=>"Croatian",
"keymaps-hu"=>"Hungarian",
"keymaps-it"=>"Italian",
"keymaps-ja"=>"Japanese",
"keymaps-la"=>"Latin",
"keymaps-lt"=>"Lithuanian",
"keymaps-lv"=>"Latvian",
"keymaps-mk"=>"Macedonian",
"keymaps-nl"=>"Dutch",
"keymaps-nl_be"=>"Dutch-Belgium",
"keymaps-nb"=>"Norwegian",
"keymaps-pl"=>"Polish",
"keymaps-pt_br"=>"Portuguise-Brazil",
"keymaps-pt"=>"Portuguise",
"keymaps-ro"=>"Romanian",
"keymaps-ru"=>"Russian",
"keymaps-sv_fi"=>"Swedish-Finland",
"keymaps-sv"=>"Swedish",
"keymaps-sl"=>"Slovenian",
"keymaps-th"=>"Thai",
"keymaps-tr"=>"Turkish");	

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
	$html=$html."
	<tr class=$classtr>
	<td width=1%>". Field_checkbox("id_$num",1,$def,"ThinClientPackage('{$num}')")."</td>
	<td width=99% style='font-size:13px'>$ligne</td>
	</tr>
	";}
	
	$html=$html."
	</tbody>
	</table>
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}

function sound_drivers(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$sql="SELECT `module` FROM thinclient_hardware_modules";
	$q=new mysql;
	$results=$q->QUERY_SQL($sql,'artica_backup');
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$modules[trim($ligne["module"])]=true;
	}		
$mds=array("snd-ali5451"=>"AC97 intergrated device with ALi M5451 Audio Controller (M1535/M1535D/M1535+/M1535D+ south bridges)",
"snd-atiixp"=>"AC97 intergrated device with ATI chipsets (ATI IXP 150/200/250/300/400)",
"snd-au8810"=>"Aureal Advantage soundcards",
"snd-au8820"=>"Aureal Vortex soundcards",
"snd-au8830"=>"Aureal Vortex 2 soundcards",
"snd-azt3328"=>"Aztech AZF3328 (PCI168) soundcards",
"snd-cs46xx"=>"Cirrus Logic CS4610/CS4612/CS4614/CS4615/CS4622/CS4624/CS4630/CS4280 chips",
"snd-cs4281"=>"Cirrus Logic CS4281 chips",
"snd-cs5535audio"=>"CS5535/CS5536 Audio",
"snd-emu10k1"=>"Sound Blaster PCI 512, Live!, Audigy and E-mu APS (partially supported) soundcards",
"snd-emu10k1x"=>"Emu10k1X (Dell OEM Version)",
"snd-korg1212"=>"Korg 1212IO soundcards",
"snd-mixart"=>"Digigram miXart soundcards",
"snd-nm256"=>"NeoMagic NM256AV/ZX chips",
"snd-rme32"=>"RME Digi32, Digi32 PRO and Digi32/8 (Sek'd Prodif32,Prodif96 and Prodif Gold) audio devices",
"snd-rme96"=>"RME Digi96, Digi96/8 and Digi96/8 PRO/PAD/PST soundcards",
"snd-rme9652"=>"RME Hammerfall (RME Digi9652/Digi9636) soundcards",
"snd-hdsp"=>"RME Hammerfall DSP Audio soundcards",
"snd-hda-intel"=>"Intel HD Audio",
"snd-trident"=>"Trident 4D-Wave DX/NX or SiS 7018 chips",
"snd-ymfpci"=>"Yamaha PCI chips YMF724,YMF724F,YMF740,YMF740C,YMF744,YMF754",
"snd-als4000"=>"Avance Logic ALS4000 chips",
"snd-cmipci"=>"C-Media CMI8338 or CMI8738 chips",
"snd-ens1370"=>"Ensoniq AudioPCI ES1370 chips",
"snd-ens1371"=>"Ensoniq AudioPCI ES1371 chips and Sound Blaster PCI 64 or 128",
"snd-es1938"=>"ESS Solo-1 (ES1938, ES1946, ES1969) chips",
"snd-es1968"=>"ESS Maestro 1/2/2E chips",
"snd-maestro3"=>"ESS Maestro 3 (Allegro) chips",
"snd-fm801"=>"ForteMedia FM801 chip",
"snd-ice1712"=>"ICE1712 (Envy24) chip",
"snd-ice1724"=>"ICE/VT1724/1720 (Envy24HT/PT) chips",
"snd-intel8x0"=>"AC97 intergrated device with Intel/SiS/nVidia/AMD chipsets, or ALi chipsets using the M5455 Audio Controller",
"snd-sonicvibes"=>"S3 SonicVibes chip",
"snd-via82xx"=>"AC97 intergrated device with VIA chipsets",
"snd-vx222"=>"Digigram VX222 soundcards");	

$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView'>
<thead class='thead'>
	<tr>
	<th colspan=2>USB</th>
	</tr>
</thead>
<tbody class='tbody'>";


while (list ($num, $ligne) = each ($mds) ){
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	if($modules[$num]){$def=1;}else{$def=0;}
	$html=$html."
	<tr class=$classtr>
	<td width=1%>". Field_checkbox("id_$num",1,$def,"ThinClientHardWare('{$num}')")."</td>
	<td width=99% style='font-size:13px'>$ligne</td>
	</tr>
	";}
	
	$html=$html."
	</tbody>
	</table>
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}

function storage_module(){
$page=CurrentPageName();
	$tpl=new templates();	
	$sql="SELECT `module` FROM thinclient_hardware_modules";
	$q=new mysql;
	$results=$q->QUERY_SQL($sql,'artica_backup');
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$modules[trim($ligne["module"])]=true;
	}		
$mds=array("ide-floppy"=>"LS-120, Iomega Zip",
"floppy"=>"Floppy disk support",
"ide-cd"=>"CD-Rom Drive Support",
"ahci"=>"ACHI SATA support",
"ata_piix"=>"Intel piix SATA chipset support",
"sata_nv"=>"NVidia SATA support",
"sata_promise"=>"Promise SATA support",
"sata_sil"=>"Silicon Image SATA support",
"sata_sil24"=>"Silicon Image 3124/3132 SATA support",
"sata_sis"=>"SIS 964/180 SATA support",
"sata_via"=>"VIA SATA support");	
	
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
	$html=$html."
	<tr class=$classtr>
	<td width=1%>". Field_checkbox("id_$num",1,$def,"ThinClientHardWare('{$num}')")."</td>
	<td width=99% style='font-size:13px'>$ligne</td>
	</tr>
	";}
	
	$html=$html."
	</tbody>
	</table>
	";
	echo $tpl->_ENGINE_parse_body($html);
}

function usb_drivers(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$sql="SELECT `module` FROM thinclient_hardware_modules";
	$q=new mysql;
	$results=$q->QUERY_SQL($sql,'artica_backup');
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$modules[trim($ligne["module"])]=true;
	}	
	
$mds=array(
"usb-hid"=>"Needed for USB keyboards and mice",  
"usb-storage"=>"Needed for USB-Sticks",
"usb-printer"=>"Needed for USB-Printers",
"usb-cdrom"=>"Needed for USB CD-Rom Drives",
"airprime"=>"USB AirPrime CDMA Wireless Driver",
"anydata"=>"USB AnyData CDMA Wireless Driver",
"belkin_sa"=>"USB Belkin and Peracom Single Port Serial Driver",
"whiteheat"=>"USB ConnectTech WhiteHEAT Serial Driver",
"digi_acceleport"=>"USB Digi International AccelePort USB Serial Driver",
"cp2101"=>"USB CP2101 UART Bridge Controller",
"cypress_m8"=>"USB Cypress M8 USB Serial Driver",
"empeg"=>"USB Empeg empeg-car Mark I/II Driver",
"ftdi_sio"=>"USB FTDI Single Port Serial Driver",
"visor"=>"USB Handspring Visor / Palm m50x / Sony Clie Driver",
"ipaq"=>"USB PocketPC PDA Driver",
"ir-usb"=>"USB IR Dongle Serial Driver",
"io_edgeport"=>"USB Inside Out Edgeport Serial Driver",
"io_ti"=>"USB Inside Out Edgeport Serial Driver, (TI devices)",
"garmin_gps"=>"USB Garmin GPS Driver",
"ipw"=>"USB IPWireless (3G UMTS TDD) Driver",
"keyspan_pda"=>"USB Keyspan PDA Single Port Serial Driver",
"kl5kusb105"=>"USB KL5KUSB105 (Palmconnect) Driver",
"kobil_sct"=>"USB KOBIL chipcard reader",
"mct_u232"=>"USB MCT Single Port Serial Driver",
"pl2303"=>"USB Prolific 2303 Single Port Serial Driver",
"hp4x"=>"USB HP4x Calculators support",
"ti_usb_3410_5052"=>"USB TI 3410/5052 Serial Driver",
"cyberjack"=>"USB REINER SCT cyberJack pinpad/e-com chipcard reader",
"option"=>"USB Option PCMCIA serial Driver",
"omninet"=>"USB ZyXEL omni.net LCD Plus Driver");	

$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView'>
<thead class='thead'>
	<tr>
	<th colspan=2>USB</th>
	</tr>
</thead>
<tbody class='tbody'>";


while (list ($num, $ligne) = each ($mds) ){
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	if($modules[$num]){$def=1;}else{$def=0;}
	$html=$html."
	<tr class=$classtr>
	<td width=1%>". Field_checkbox("id_$num",1,$def,"ThinClientHardWare('{$num}')")."</td>
	<td width=99% style='font-size:13px'>$ligne</td>
	</tr>
	";}
	
	$html=$html."
	</tbody>
	</table>
	";
	echo $tpl->_ENGINE_parse_body($html);	
	
}


function network(){
	$page=CurrentPageName();
	$tpl=new templates();		
	Networkdefaults();
	
	$sql="SELECT `module` FROM thinclient_hardware_modules";
	$q=new mysql;
	$results=$q->QUERY_SQL($sql,'artica_backup');
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$modules[trim($ligne["module"])]=true;
	}
	
$dixcent=array(	
"3c501"=>"3c501 EtherLink support",
"3c503"=>"3c503 EtherLink II support",
"3c505"=>"3c505 EtherLink Plus support",
"3c507"=>"3c507 EtherLink 16 support",
"3c509"=>"3c509/3c529 (MCA)/3c579 EtherLink III support",
"3c515"=>"3c515 ISA Fast EtherLink ",
"3c59x"=>"3c590/3c900 series (592/595/597) Vortex/Boomerang support ",
"8139too"=>"RealTek RTL-8139 PCI Fast Ethernet Adapter support very common in no-name network cards. Covers also 8129.",
"8139cp"=>"RealTek RTL-8139 C+ PCI Fast Ethernet Adapter support",
"ac3200"=>"Ansel Communications EISA 3200 support",
"amd8111e"=>"AMD 8111 (new PCI lance) support",
"at1700"=>"AT1700/1720 support",
"atl2"=>"Attansic L2. Atheros(R) L2 Ethernet Network Driver ver. 1.0.40.3",
"b44"=>"Broadcom 4400 ethernet support",
"cs89x0"=>"CS89x0 support",
"de4x5"=>"Generic DECchip & DIGITAL EtherWORKS PCI/EISA",
"de2104x"=>"Early DECchip Tulip (dc2104x) PCI support",
"depca"=>"DEPCA, DE10x, DE200, DE201, DE202, DE422 support",
"dgrs"=>"Digi Intl. RightSwitch SE-X support",
"dmfe"=>"Davicom DM910x/DM980x support",
"hp100"=>"HP 10/100VG PCLAN (ISA, EISA, PCI) support",
"e100"=>"EtherExpressPro/100 support (e100, Alternate Intel driver)",
"e2100"=>"Cabletron E21xx support",
"eepro"=>"EtherExpressPro support/EtherExpress 10 (i82595) support",
"eepro100"=>"EtherExpressPro/100 support (eepro100, original Becker driver)",
"eexpress"=>"EtherExpress 16 support",
"epic100"=>"SMC EtherPower II",
"eth16i"=>"ICL EtherTeam 16i/32 support",
"ewrk3"=>"EtherWORKS 3 (DE203, DE204, DE205) support",
"fealnx"=>"Myson MTD-8xx PCI Ethernet support",
"forcedeth 	"=>"nForce Ethernet support (nVidia 0.62-Driver Package V1.30)",
"hp-plus"=>"HP PCLAN+ (27247B and 27252A) support",
"hp "=>"HP PCLAN (27245 and other 27xxx series) support",
"lp486e"=>"LP486E on board Ethernet",
"lance"=>"AMD LANCE and PCnet (AT1500 and NE2100) support",
"ne io=0x300"=>"NE2000/NE1000 support",
"ne2k-pci"=>"PCI version of NE-2000",
"natsemi"=>"National Semiconductor DP8381x series PCI Ethernet support",
"ni5010"=>"Racal-Interlan (Micom) NI cards",
"ni52"=>"Racal-Interlan (Micom) NI cards",
"ni65"=>"Racal-Interlan (Micom) NI cards",
"pcnet32"=>"AMD PCnet32 PCI support",
"sis900"=>"SiS 900/7016 PCI Fast Ethernet Adapter support. Common in integrated motherboards",
"smc-ultra"=>"SMC Ultra support",
"smc9194"=>"SMC 9194 support",
"starfire"=>"Adaptec Starfire/DuraLAN support",
"sundance"=>"Sundance Alta support",
"tlan"=>"TI ThunderLAN support, Compaq Neteligent 10/100",
"typhoon"=>"3cr990 series Typhoon support",
"tulip"=>"DECchip Tulip (dc21x4x) PCI support",
"via-rhine"=>"VIA Rhine support (both Rhine I and II). Common in integrated motherboards",
"wd"=>"WD80*3 support",
"winbond-840"=>"Winbond W89c840 Ethernet support",
"xircom_cb"=>"Xircom CardBus support",
"xircom_tulip_cb"=>"Xircom Tulip-like CardBus support");

$mille=array(	
"acenic"=>"Alteon AceNIC/3Com 3C985/NetGear GA620 Gigabit support",
"atl1"=>"Attansic L1. Atheros 1000M Ethernet Network Driver version=1.2.40.3",
"atl1e"=>"Attansic L1e. Atheros(R) AR8121/AR8113/AR8114 PCI-E Ethernet Network Driver ver. 1.0.1.0",
"bnx2"=>"Broadcom NetXtremeII support",
"dl2k"=>"D-Link DL2000-based Gigabit Ethernet support",
"e1000"=>"Intel(R) PRO/1000 Gigabit Ethernet support (PCI)",
"e1000e"=>"Intel(R) 82567LM (PCIe)",
"ns83820"=>"National Semiconductor DP83820 support",
"hamachi"=>"Packet Engines Hamachi GNIC-II support",
"yellowfin"=>"Packet Engines Yellowfin Gigabit-NIC support",
"r8101"=>"Realtek 8101 Gigabit Ethernet support",
"r8168"=>"Realtek 8168 Gigabit Ethernet support",
"r8169"=>"Realtek 8169 Gigabit Ethernet support - version 6.011_2.6.16.5",
"sis190"=>"SiS190/SiS191 gigabit ethernet support",
"skge"=>"New SysKonnect GigaEthernet support",
"sk98lin"=>"Marvell Yukon Chipset / SysKonnect SK-98xx Support",
"sky2"=>"SysKonnect Yukon2 support",
"tg3"=>"Broadcom Tigon3 ver. 3.99k",
"via-velocity"=>"VIA Velocity support",
"cxgb"=>"Chelsio 10Gb Ethernet support",
"ixgb"=>"Intel(R) PRO/10GbE support",
"s2io"=>"S2IO 10Gbe XFrame NIC");

$pcmcia=array(
"3c589_cs"=>"3Com 3c589 PCMCIA support",
"3c574_cs"=>"3Com 3c574 PCMCIA support",
"fmvj18x_cs"=>"Fujitsu FMV-J18x PCMCIA support",
"pcnet_cs"=>"NE2000 compatible PCMCIA support",
"nmclan_cs"=>"New Media PCMCIA support",
"smc91c92_cs"=>"SMC 91Cxx PCMCIA support",
"xirc2ps_cs"=>"Xircom 16-bit PCMCIA support",
"axnet_cs"=>"Asix AX88190 PCMCIA support",
"ibmtr_cs"=>"IBM PCMCIA tokenring adapter support"

);

$wifi=array(
"ray_cs"=>"Aviator/Raytheon 2.4MHz wireless support",
"netwave_cs"=>"Xircom Netwave AirSurfer Pcmcia wireless support",
"wavelan_cs"=>"AT&T/Lucent old WaveLAN Pcmcia wireless support",
"orinoco_pci"=>"Prism 2.5 PCI 802.11b adaptor support (EXPERIMENTAL)",
"orinoco_cs"=>"Hermes PCMCIA card support",
"airo_cs"=>"Cisco/Aironet 34X/35X/4500/4800 PCMCIA cards",
"ath_pci"=>"Madwifi Support",
"aes-i586"=>"WPA Supplicant Support"

);

$html="
<div style='float:right'>". button("{rebuild_defaults}","RebuildDefaultModules()")."</div>
<div class=explain>{thinclient_network_modules_explain}</div>

<table cellspacing='0' cellpadding='0' border='0' class='tableView'>
<thead class='thead'>
	<tr>
	<th colspan=2>{nic} 10/100 Mbs</th>
	</tr>
</thead>
<tbody class='tbody'>";


while (list ($num, $ligne) = each ($dixcent) ){
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	if($modules[$num]){$def=1;}else{$def=0;}
	$html=$html."
	<tr class=$classtr>
	<td width=1%>". Field_checkbox("id_$num",1,$def,"ThinClientHardWare('{$num}')")."</td>
	<td width=99% style='font-size:13px'>$ligne</td>
	</tr>
	";}
	
$html=$html."</tbody>
<thead class='thead'>
	<tr>
	<th colspan=2>{nic} 1000Mbs</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
while (list ($num, $ligne) = each ($mille) ){
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	if($modules[$num]){$def=1;}else{$def=0;}
	$html=$html."
	<tr class=$classtr>
	<td width=1%>". Field_checkbox("id_$num",1,$def,"ThinClientHardWare('{$num}')")."</td>
	<td width=99% style='font-size:13px'>$ligne</td>
	</tr>
	";}	
	
	
$html=$html."</tbody>
<thead class='thead'>
	<tr>
	<th colspan=2>{nic} PCMCIA</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
while (list ($num, $ligne) = each ($pcmcia) ){
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	if($modules[$num]){$def=1;}else{$def=0;}
	$html=$html."
	<tr class=$classtr>
	<td width=1%>". Field_checkbox("id_$num",1,$def,"ThinClientHardWare('{$num}')")."</td>
	<td width=99% style='font-size:13px'>$ligne</td>
	</tr>
	";}		
	
$html=$html."</tbody>
<thead class='thead'>
	<tr>
	<th colspan=2>{nic} WIFI</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
while (list ($num, $ligne) = each ($wifi) ){
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	if($modules[$num]){$def=1;}else{$def=0;}
	$html=$html."
	<tr class=$classtr>
	<td width=1%>". Field_checkbox("id_$num",1,$def,"ThinClientHardWare('{$num}')")."</td>
	<td width=99% style='font-size:13px'>$ligne</td>
	</tr>
	";}			
	$html=$html."
	</tbody>
	</table>
	";
	echo $tpl->_ENGINE_parse_body($html);
}

function Networkdefaults(){
$sql="SELECT COUNT(`module`) as tcount FROM thinclient_hardware_modules";
$q=new mysql();
$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	if($ligne["tcount"]<1){buildDefault();}
}

function save_module(){
	$module=$_GET["module"];
	$exist=false;
	$sql="SELECT `module` FROM thinclient_hardware_modules WHERE `module`='$module'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
	if($ligne["module"]<>null){$exist=true;}
	if($_GET["enabled"]==1){
		$sql="INSERT INTO thinclient_hardware_modules (`module`) VALUES ('$module');";
	}else{
		$sql="DELETE FROM thinclient_hardware_modules WHERE `module`='$module';";
	}
	$q->QUERY_SQL($sql,'artica_backup');
	if(!$q->ok){echo $q->mysql_error;}	
	
}

function save_package(){
	$q=new mysql();
	$module=$_GET["package"];
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


function buildDefault(){
	$q=new mysql();
	$sql="TRUNCATE TABLE `thinclient_hardware_modules`";
	$q->QUERY_SQL($sql,'artica_backup');
	
	$sql="TRUNCATE TABLE `thinclient_package_modules`";
	$q->QUERY_SQL($sql,'artica_backup');
	$f[]="module 3c501";
	$f[]="module 3c503";
	$f[]="module 3c505";
	$f[]="module 3c507";
	$f[]="module 3c509";
	$f[]="module 3c515";
	$f[]="module 3c59x";
	$f[]="module 8139too";
	$f[]="module 8139cp";
	$f[]="module ac3200";
	$f[]="module amd8111e";
	$f[]="module at1700";
	$f[]="module atl2";
	$f[]="module b44";
	$f[]="module cs89x0";
	$f[]="module de4x5";
	$f[]="module de2104x";
	$f[]="module forcedeth";
	$f[]="module pcnet32";
	$f[]="module sis900";
	$f[]="module via-rhine";
	$f[]="module e1000";
	$f[]="module r8101";
	$f[]="module r8168";
	$f[]="module r8169";
	$f[]="module usb-hid";
	$f[]="module usb-storage";
	$f[]="module usb-cdrom";
	$f[]="module ide-cd";
	$f[]="module ata_piix";
	$f[]="module isofs";
	$f[]="module vfat";
	$f[]="module ntfs";
	$f[]="module ext2";
	$f[]="module ext3";
	$f[]="module supermount";
	$f[]="module nfs";
	$f[]="module smbfs";
	$f[]="package hwclock";
	$f[]="package rdate";
	$f[]="package xorg6-i810";
	$f[]="package xorg6-nv";
	$f[]="package xorg6-radeon";
	$f[]="package xorg6-s3";
	$f[]="package xorg6-trident";
	$f[]="package xorg6-vesa";
	$f[]="package xorg6-via";
	$f[]="package xorg6-vmware";
	$f[]="package keymaps-en_us";
	$f[]="package keymaps-fr";
	$f[]="package rdesktop";
	//$f[]="package icewm";
	//$f[]="package icewm-theme-xp";	
	
	while (list ($num, $ligne) = each ($f) ){
		if(preg_match("#^module\s+(.+)#",$ligne,$re)){
				$sql="INSERT INTO thinclient_hardware_modules (`module`) VALUES ('{$re[1]}');";
				$q->QUERY_SQL($sql,'artica_backup');	
				continue;
		}
		
		if(preg_match("#^package\s+(.+)#",$ligne,$re)){
				$sql="INSERT INTO thinclient_package_modules (`package`) VALUES ('{$re[1]}');";
				$q->QUERY_SQL($sql,'artica_backup');	
				continue;
		}	
	}


	
}


?>