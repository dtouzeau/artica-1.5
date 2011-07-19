<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.graphs.inc');
	include_once('ressources/class.virtualbox.inc');
	
	
	if(posix_getuid()==0){die();}
	
	$user=new usersMenus();
	if($user->AsVirtualBoxManager==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["status"])){status();exit;}
	if(isset($_GET["stats"])){statistics();exit;}
	if(isset($_GET["tools"])){tools();exit;}
	if(isset($_GET["nat"])){nat_settings();exit;}
	if(isset($_GET["nat-save"])){nat_add();exit;}
	if(isset($_GET["nat-list"])){nat_list();exit;}
	if(isset($_GET["nat-del"])){nat_del();exit;}
	if(isset($_GET["nat-rebuild"])){nat_rebuild();exit;}
	
	if(isset($_GET["guestmemoryballoon"])){guestmemoryballoon();exit;}
	if(isset($_GET["nestedpaging"])){nestedpaging();exit;}
	if(isset($_GET["vtxvpid"])){vtxvpid();exit;}
	if(isset($_GET["acpi"])){acpi();exit;}
	if(isset($_GET["ioapic"])){ioapic();exit;}
	if(isset($_GET["pagefusion"])){pagefusion();exit;}
	if(isset($_GET["hpet"])){hpet();exit;}
	
	
	
	
	if(isset($_GET["largepages"])){largepages();exit;}
	if(isset($_GET["guestmemory"])){guestmemory();exit;}
	if(isset($_GET["vram"])){vram();exit;}
	if(isset($_GET["pid-kill"])){vbox_kill();exit;}
	if(isset($_GET["VboxCpus"])){VboxCpus();exit;}
	
	
	
	
	
js();


function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();	
	$infos=$sock->getFrameWork("cmd.php?virtualbox-showvminfo=yes&uuid=".base64_encode($_GET["uuid"]));
	$array=unserialize(base64_decode($infos));
	
	$title=$tpl->_ENGINE_parse_body("{virtual_machine}: {$array["NAME"]}");
$html="
	function VirtualBoxComputerLoad(){
			YahooWin2('650','$page?popup=yes&uuid={$_GET["uuid"]}','$title');
		}
	
	
	VirtualBoxComputerLoad()";

echo $html;
	
}

function tools(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$sock=new sockets();	
	$infos=unserialize(base64_decode($sock->getFrameWork("cmd.php?virtualbox-showvminfo=yes&uuid=".base64_encode($_GET["uuid"]))));	
	

	
	$clonehd=Paragraphe("storage-64.png","{CLONE_HD}","{CLONE_HD_TEXT}","javascript:Loadjs('virtualbox.clonehd.php?uuid={$_GET["uuid"]}')");
	$watchdog=Paragraphe("rouage-64.png","{VIRTUALBOX_WATCHDOG}","{VIRTUALBOX_WATCHDOG_TEXT}","javascript:Loadjs('virtualbox.watchdog.php?uuid={$_GET["uuid"]}')");
	
		$tr[]=$clonehd;
		$tr[]=$watchdog;
		$tr[]=$whitelist;

	
$tables[]="<table style='width:100%'><tr>";
$t=0;
while (list ($key, $line) = each ($tr) ){
		$line=trim($line);
		if($line==null){continue;}
		$t=$t+1;
		$tables[]="<td valign='top'>$line</td>";
		if($t==3){$t=0;$tables[]="</tr><tr>";}
		}

if($t<3){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top'>&nbsp;</td>";				
	}
}	
	
	
$html="<div style='width:700px'>". implode("\n",$tables)."</div>";

	$tpl=new templates();
	$datas=$tpl->_ENGINE_parse_body($html,"postfix.plugins.php");	
	echo $datas;	
	
}

function statistics(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$sock=new sockets();	
	$infos=unserialize(base64_decode($sock->getFrameWork("cmd.php?virtualbox-showvminfo=yes&uuid=".base64_encode($_GET["uuid"]))));
	$stats=unserialize(base64_decode($sock->getFrameWork("cmd.php?virtualbox-showcpustats=yes&virtual-machine=".base64_encode($infos["NAME"]))));	
	
	if(!is_array($stats["CPU_LOAD_KERNEL_TABLE"])){
		echo $tpl->_ENGINE_parse_body("<H2>{error_no_datas}</H2>");
		return;
	}
	
	
	$fileName = dirname(__FILE__)."/ressources/logs/{$_GET["uuid"]}-cpu-kernel.png";
	$g=new artica_graphs($fileName,1);	
	while (list($index,$data)=each($stats["CPU_LOAD_KERNEL_TABLE"])){
		$g->ydata[]=$data;
		$g->xdata[]="";
	}

	$g->title='CPU Kernel';
	$g->x_title="%";
	$g->y_title=$tpl->_ENGINE_parse_body("{seconds}");
	$g->width=600;
	$g->line_green();
	
	$fileName = dirname(__FILE__)."/ressources/logs/{$_GET["uuid"]}-cpu-user.png";
	$g=new artica_graphs($fileName,1);	
	while (list($index,$data)=each($stats["CPU_LOAD_USER_TABLE"])){
		$g->ydata[]=$data;
		$g->xdata[]="";
	}

	$g->title='CPU User';
	$g->x_title="%";
	$g->y_title=$tpl->_ENGINE_parse_body("{seconds}");
	$g->width=600;
	$g->line_green();
	
	
	$fileName = dirname(__FILE__)."/ressources/logs/{$_GET["uuid"]}-memory.png";
	$g=new artica_graphs($fileName,1);	
	while (list($index,$data)=each($stats["CPU_LOAD_MEMORY_TABLE"])){
		$g->ydata[]=$data;
		$g->xdata[]="";
	}

	$g->title='Memory (kB)';
	$g->x_title="Kb";
	$g->y_title=$tpl->_ENGINE_parse_body("{seconds}");
	$g->width=600;
	$g->line_green();	

	
	
	$html="
		<center style='margin-top:10px'><img src='ressources/logs/{$_GET["uuid"]}-cpu-kernel.png'></center>
		<center style='margin-top:10px'><img src='ressources/logs/{$_GET["uuid"]}-cpu-user.png'></center>
		<center style='margin-top:10px'><img src='ressources/logs/{$_GET["uuid"]}-memory.png'></center>
		
		";
	echo $html;
	
	
	
}


function status(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$sock=new sockets();	
	$infos=unserialize(base64_decode($sock->getFrameWork("cmd.php?virtualbox-showvminfo=yes&uuid=".base64_encode($_GET["uuid"]))));
	$paragraphe=Paragraphe(oslistIMG($infos["GUEST OS"]),"{$infos["NAME"]}","<hr>{$infos["GUEST OS"]}");
	$kill_process=$tpl->javascript_parse_text("{kill_process}");
	$pid_array=unserialize(base64_decode($sock->getFrameWork("cmd.php?VboxPid={$_GET["uuid"]}")));
	
	if($pid_array["PID"]>1){
		
		$ini=new Bs_IniHandler();
		$ini->loadString($pid_array["INFOS"]);
		$status="
		<table class=form style='width:218px'>
			<tr>
			<td align='right' nowrap class=legend><strong>{pid}:</strong></td>
			<td><strong style='font-size:13px'>{$ini->_params["APP_VIRTUALBOX"]["master_pid"]}</strong></td>
			<td>". imgtootltip("ed_delete.gif","{kill_process}","VirtBoxKill('{$ini->_params["APP_VIRTUALBOX"]["master_pid"]}');")."</td>
		</tr>			
		<tr>
			<td align='right' class=legend><strong>{memory}:</strong></td>
			<td colspan=2><strong style='font-size:13px'>".FormatBytes($ini->_params["APP_VIRTUALBOX"]["master_memory"])."</strong></td>
		</tr>		
		<tr>
			<td align='right' class=legend><strong>{virtual_memory}:</strong></td>
			<td colspan=2><strong style='font-size:13px'>".FormatBytes($ini->_params["APP_VIRTUALBOX"]["master_cached_memory"])."</strong></td>
		</tr>
		<tr>
			<td colspan=3><div style='text-align:right'><i style='font-size:13px'>{since}: {$ini->_params["APP_VIRTUALBOX"]["uptime"]}</i></div></td>
		</tr>
		</table>
		
		<script>
		
	var X_VirtBoxKill= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		RefreshTab('main_config_{$_GET["uuid"]}');
		RefreshVirtualBoxList();
	}
		
			function VirtBoxKill(pid){
				if(confirm('$kill_process '+pid+'?')){
					var XHR = new XHRConnection();
					XHR.appendData('pid-kill',pid);
					XHR.appendData('uuid','{$_GET["uuid"]}');
					XHR.sendAndLoad('$page', 'GET',X_VirtBoxKill);
				}
			
			}
	</script>
		";
		
		
		
		
		
	}
	
	$APP_NO_VBOXADDITIONS=$tpl->javascript_parse_text("{APP_NO_VBOXADDITIONS}");
	if(preg_match("#([0-9]+)#",$infos["CONFIGURED MEMORY BALLOON SIZE"],$re)){$memoryballon=$re[1];}
	if(preg_match("#([0-9]+)#",$infos["MEMORY SIZE"],$re)){$memory_size=$re[1];}
	if(preg_match("#([0-9]+)#",$infos["VRAM SIZE"],$re)){$vram=$re[1];}
	
	
	$MEMORY_BALLON_ASK_MB=$tpl->_ENGINE_parse_body("{MEMORY_BALLON_ASK_MB}");
	$MEMORY_SIZE_ASK=$tpl->_ENGINE_parse_body("{MEMORY_SIZE_ASK}");
	$virtual_box_cpus=$tpl->_ENGINE_parse_body("{virtual_box_cpus}");

	
	
	if(strlen($infos["ADDITIONS ACTIVE"])>0){
		if($infos["ADDITIONS ACTIVE"]=="no"){
			$ADDITIONSimg="<img src='img/status_warning.gif'>";
		}
		$infos["ADDITIONS ACTIVE"]="{{$infos["ADDITIONS ACTIVE"]}}";
		
	
	}

	if($infos["PAGE FUSION"]=="off"){$pagefusion=0;}
	if($infos["PAGE FUSION"]=="on"){$pagefusion=1;}
	
	if($infos["NESTED PAGING"]=="off"){$nestedpaging=0;}
	if($infos["NESTED PAGING"]=="on"){$nestedpaging=1;}
	
	if($infos["LARGE PAGES"]=="on"){$largepages=1;}
	if($infos["LARGE PAGES"]=="off"){$largepages=0;}
	
	if($infos["VT-X VPID"]=="on"){$vtxvpid=1;}
	if($infos["VT-X VPID"]=="off"){$vtxvpid=0;}	

	if($infos["ACPI"]=="on"){$acpi=1;}
	if($infos["ACPI"]=="off"){$acpi=0;}		
	
	if($infos["IOAPIC"]=="on"){$ioapic=1;}
	if($infos["IOAPIC"]=="off"){$ioapic=0;}			
	
	if($infos["HPET"]=="on"){$hpet=1;}
	if($infos["HPET"]=="off"){$hpet=0;	}
	
	
	$html="



<table style='width:100%'>
<tr>
<td valign='top'>$paragraphe<br>$status</td>
<td valign='top'>
	<table style='width:100%'>
	
	
	<tr>
		<td class=legend style='font-size:13px;' nowrap>{APP_VBOXADDITIONS}:</td>
		<td style='font-size:13px;' nowrap>{$infos["ADDITIONS ACTIVE"]}</td>
		<td align='center'>$ADDITIONSimg</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:13px;' nowrap>
			<a href='javascript:blur();' OnClick=\"javascript:VboxMemory()\" 
			style='text-decoration:underline'>{physical_memory}</a>:</td>
		<td style='font-size:13px;'>{$infos["MEMORY SIZE"]}</td>
		<td>&nbsp;</td>
	</tr>
		
	<tr>
		<td class=legend style='font-size:13px;' nowrap><a href='javascript:blur();' OnClick=\"javascript:VboxVram()\" 
			style='text-decoration:underline'>{vramsize}:</td>
		<td style='font-size:13px;'>{$infos["VRAM SIZE"]}</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:13px;' nowrap><a href='javascript:blur();' OnClick=\"javascript:guestmemoryballoon()\" style='text-decoration:underline'>{guestmemoryballoon}:</a></td>
		<td style='font-size:13px;' nowrap>{$infos["CONFIGURED MEMORY BALLOON SIZE"]}</a></td>
		<td>". help_icon("{guestmemoryballoon_text}")."</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px;' nowrap>{pagefusion}:</td>
		<td style='font-size:13px;'>". Field_checkbox("pagefusion",1,$pagefusion,"pagefusion()")."</td>
		<td></td>
	</tr>			
	<tr>
		<td class=legend style='font-size:13px;' nowrap>
		<a href='javascript:blur();' OnClick=\"javascript:VboxCpus()\" style='text-decoration:underline'>{virtual_box_cpus}:</a></td>
		<td style='font-size:13px;'>{$infos["NUMBER OF CPUS"]}</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px;' nowrap>{vtxvpid}:</td>
		<td style='font-size:13px;'>". Field_checkbox("vtxvpid",1,$vtxvpid,"vtxvpid()")."</td>
		<td>&nbsp;</td>
	</tr>
	
	
	<tr>
		<td class=legend style='font-size:13px;' nowrap>{acpi}:</td>
		<td style='font-size:13px;'>". Field_checkbox("acpi",1,$acpi,"acpi()")."</td>
		<td>&nbsp;</td>
	</tr>
	
	<tr>
		<td class=legend style='font-size:13px;' nowrap>{ioapic}:</td>
		<td style='font-size:13px;'>". Field_checkbox("ioapic",1,$ioapic,"ioapic()")."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:13px;' nowrap>{hpet}:</td>
		<td style='font-size:13px;'>". Field_checkbox("hpet",1,$hpet,"hpet()")."</td>
		<td>". help_icon("{hpet_text}")."</td>
	</tr>

	
	
	<tr>
		<td class=legend style='font-size:13px;' nowrap>{nestedpaging}:</td>
		<td style='font-size:13px;'>". Field_checkbox("nestedpaging",1,$nestedpaging,"nestedpaging()")."</td>
		<td>&nbsp;</td>
	</tr>
	</tr>	
		<td class=legend style='font-size:13px;' nowrap>{largepages}:</td>
		<td style='font-size:13px;'>". Field_checkbox("largepages",1,$largepages,"largepages()")."</td>
		<td>&nbsp;</td>
	</tr>			
	<tr>
	
	<tr>
		<td class=legend style='font-size:13px;' nowrap>{accelerate3d}:</td>
		<td style='font-size:13px;'>{$infos["3D ACCELERATION"]}</td>
		<td>&nbsp;</td>
	</tr>	
	</table>
	</td>
</tr>
</table>
<script>

var X_guestmemoryballoon= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	RefreshTab('main_config_{$_GET["uuid"]}');
	}
	
	
	function guestmemoryballoon(){
		var additions='{$infos["ADDITIONS ACTIVE"]}';
		if(additions.length>0){
			if(additions!=='yes'){
				alert('$APP_NO_VBOXADDITIONS');
				return;
			}
		}
		
		var ballon=prompt('$MEMORY_BALLON_ASK_MB','$memoryballon');
		if(ballon){
			var XHR = new XHRConnection();
			XHR.appendData('guestmemoryballoon',ballon);
			XHR.appendData('uuid','{$_GET["uuid"]}');
			XHR.sendAndLoad('$page', 'GET',X_guestmemoryballoon);	
		}
	}
	
	function VboxMemory(){
		var ballon=prompt('$MEMORY_SIZE_ASK','$memory_size');
		if(ballon){
			var XHR = new XHRConnection();
			XHR.appendData('guestmemory',ballon);
			XHR.appendData('uuid','{$_GET["uuid"]}');
			XHR.sendAndLoad('$page', 'GET',X_guestmemoryballoon);	
		}
	}

	function VboxCpus(){
	var ballon=prompt('$virtual_box_cpus','{$infos["NUMBER OF CPUS"]}');
		if(ballon){
			var XHR = new XHRConnection();
			XHR.appendData('VboxCpus',ballon);
			XHR.appendData('uuid','{$_GET["uuid"]}');
			XHR.sendAndLoad('$page', 'GET',X_guestmemoryballoon);	
		}
	
	}
	
	
	function VboxVram(){
		var ballon=prompt('$MEMORY_SIZE_ASK','$vram');
		if(ballon){
			var XHR = new XHRConnection();
			XHR.appendData('vram',ballon);
			XHR.appendData('uuid','{$_GET["uuid"]}');
			XHR.sendAndLoad('$page', 'GET',X_guestmemoryballoon);	
		}
	}		
	
	
	
	
	function nestedpaging(){
		var XHR = new XHRConnection();
		if(document.getElementById('nestedpaging').checked){XHR.appendData('nestedpaging',1);}else{XHR.appendData('nestedpaging',0);}
		XHR.appendData('uuid','{$_GET["uuid"]}');
		XHR.sendAndLoad('$page', 'GET',X_guestmemoryballoon);
	}
	
	function vtxvpid(){
		var XHR = new XHRConnection();
		if(document.getElementById('vtxvpid').checked){XHR.appendData('vtxvpid',1);}else{XHR.appendData('vtxvpid',0);}
		XHR.appendData('uuid','{$_GET["uuid"]}');
		XHR.sendAndLoad('$page', 'GET',X_guestmemoryballoon);
	}

	function acpi(){
		var XHR = new XHRConnection();
		if(document.getElementById('acpi').checked){XHR.appendData('acpi',1);}else{XHR.appendData('acpi',0);}
		XHR.appendData('uuid','{$_GET["uuid"]}');
		XHR.sendAndLoad('$page', 'GET',X_guestmemoryballoon);
	}	
	function ioapic(){
		var XHR = new XHRConnection();
		if(document.getElementById('ioapic').checked){XHR.appendData('ioapic',1);}else{XHR.appendData('ioapic',0);}
		XHR.appendData('uuid','{$_GET["uuid"]}');
		XHR.sendAndLoad('$page', 'GET',X_guestmemoryballoon);
	}		
	function pagefusion(){
		var XHR = new XHRConnection();
		if(document.getElementById('pagefusion').checked){XHR.appendData('pagefusion',1);}else{XHR.appendData('pagefusion',0);}
		XHR.appendData('uuid','{$_GET["uuid"]}');
		XHR.sendAndLoad('$page', 'GET',X_guestmemoryballoon);
	}
	function hpet(){
		var XHR = new XHRConnection();
		if(document.getElementById('hpet').checked){XHR.appendData('hpet',1);}else{XHR.appendData('hpet',0);}
		XHR.appendData('uuid','{$_GET["uuid"]}');
		XHR.sendAndLoad('$page', 'GET',X_guestmemoryballoon);
	}		
	
	
	
	function largepages(){
		var XHR = new XHRConnection();
		if(document.getElementById('largepages').checked){XHR.appendData('largepages',1);}else{XHR.appendData('largepages',0);}
		XHR.appendData('uuid','{$_GET["uuid"]}');
		XHR.sendAndLoad('$page', 'GET',X_guestmemoryballoon);
	}	
</script>



	";

	$stats=unserialize(base64_decode($sock->getFrameWork("cmd.php?virtualbox-showcpustats=yes&virtual-machine=".base64_encode($infos["NAME"]))));	
	
	if(is_array($stats["CPU_LOAD_KERNEL_TABLE"])){
		$fileName = dirname(__FILE__)."/ressources/logs/{$_GET["uuid"]}-cpu-kernel.png";
		$g=new artica_graphs($fileName,1);	
		while (list($index,$data)=each($stats["CPU_LOAD_KERNEL_TABLE"])){
			$g->ydata[]=$data;
			$g->xdata[]="";
		}

		$g->title='CPU Kernel';
		$g->x_title="%";
		$g->y_title=$tpl->_ENGINE_parse_body("{seconds}");
		$g->width=600;
		$g->line_green();
		$html=$html."<hr><center style='margin-top:10px'><img src='ressources/logs/{$_GET["uuid"]}-cpu-kernel.png'></center>";
	}

echo $tpl->_ENGINE_parse_body($html);
	
	
}

function popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$array["status"]='{status}';
	$array["tools"]='{tools}';
	$array["stats"]='{statistics}';
	$sock=new sockets();
	$infos=unserialize(base64_decode($sock->getFrameWork("cmd.php?virtualbox-showvminfo=yes&uuid=".base64_encode($_GET["uuid"]))));
	
	$first_nic=$infos["NIC 1"];
		if(preg_match("#Attachment: NAT,#",$first_nic)){
			$array["nat"]='{nat_configuration}';
		}	
	
	
	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&uuid={$_GET["uuid"]}\"><span>$ligne</span></a></li>\n");
		
	}
	
	
	echo "
	<div id=main_config_{$_GET["uuid"]} style='width:100%;height:590px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_{$_GET["uuid"]}').tabs({
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

function popup_old(){
	$page=CurrentPageName();
	$tpl=new templates();	
	
	$html="
	
	<div id='VirtualBoxTAP1'>
	<table style='width:100%'>
	<tr>
		<td class=legend style='font-size:13px;' nowrap>{virtual_machine_name}:</td>
		<td style='font-size:13px;'>". Field_text("name",$array["name"],"font-size:13px;padding:3px;width:120px")."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:13px;' nowrap>{virtual_machine_location}:</td>
		<td style='font-size:13px;'>". Field_text("basefolder",$array["basefolder"],"font-size:13px;padding:3px;width:120px")."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:13px;' nowrap>{ComputerOS}:</td>
		<td>". Field_array_Hash(oslist(),"ostype",$array["ostype"],null,null,0,'font-size:13px;padding:3px')."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend style='font-size:13px;' nowrap>{physical_memory}:</td>
		<td style='font-size:13px;'>". Field_text("memorysize",$array["memorysize"],"font-size:13px;padding:3px;width:90px")."&nbsp;MB</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:13px;' nowrap>{vramsize}:</td>
		<td style='font-size:13px;'>". Field_text("vramsize",$array["vramsize"],"font-size:13px;padding:3px;width:90px")."&nbsp;MB</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:13px;' nowrap>{guestmemoryballoon}:</td>
		<td style='font-size:13px;'>". Field_text("guestmemoryballoon",$array["guestmemoryballoon"],"font-size:13px;padding:3px;width:90px")."&nbsp;MB</td>
		<td>". help_icon("{guestmemoryballoon_text}")."</td>
	</tr>		
	<tr>
		<td class=legend style='font-size:13px;' nowrap>{virtual_box_cpus}:</td>
		<td>". Field_array_Hash(array(1=>1,2=>2,3=>3,4=>4),"cpus",$array["cpus"],null,null,0,'font-size:13px;padding:3px')."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:13px;' nowrap>{acpi}:</td>
		<td>". Field_checkbox("acpi",1,$array["acpi"])."</td>
		<td>&nbsp;</td>
	</tr>		
	<tr>
		<td class=legend style='font-size:13px;' nowrap>{ioapic}:</td>
		<td>". Field_checkbox("ioapic",1,$array["ioapic"])."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:13px;' nowrap>{rtcuseutc}:</td>
		<td>". Field_checkbox("rtcuseutc",1,$array["rtcuseutc"])."</td>
		<td>". help_icon("{hpet_text}")."</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:13px;' nowrap>{hwvirtex}:</td>
		<td>". Field_checkbox("hwvirtex",1,$array["hwvirtex"],"hwvirtexSwitch()")."</td>
		<td>&nbsp;</td>
	</tr>	
		<td class=legend style='font-size:13px;' nowrap>{nestedpaging}:</td>
		<td>". Field_checkbox("nestedpaging",1,$array["nestedpaging"],"hwvirtexSwitch()")."</td>
		<td>&nbsp;</td>
	</tr>
	</tr>	
		<td class=legend style='font-size:13px;' nowrap>{largepages}:</td>
		<td>". Field_checkbox("largepages",1,$array["largepages"])."</td>
		<td>&nbsp;</td>
	</tr>			
	<tr>
		<td class=legend style='font-size:13px;' nowrap>{vtxvpid}:</td>
		<td>". Field_checkbox("vtxvpid",1,$array["vtxvpid"])."</td>
		<td>&nbsp;</td>
	</tr>	
	
	
	<tr>
		<td class=legend style='font-size:13px;' nowrap>{hwvirtexexcl}:</td>
		<td>". Field_checkbox("hwvirtexexcl",1,$array["hwvirtexexcl"])."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:13px;' nowrap>{accelerate3d}:</td>
		<td>". Field_checkbox("accelerate3d",1,$array["accelerate3d"])."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td colspan=3 align='right'><hr>". button("{apply}","SaveVirtualBoxStandardParams()")."</td>
	</tr>
	</table>
	</div>
	<script>
function hwvirtexSwitch(){
	document.getElementById('nestedpaging').disabled=true;
	document.getElementById('vtxvpid').disabled=true;
	document.getElementById('largepages').disabled=true;
	if(document.getElementById('hwvirtex').checked){
		document.getElementById('nestedpaging').disabled=false;
		document.getElementById('vtxvpid').disabled=false;
		if(document.getElementById('nestedpaging').checked){document.getElementById('largepages').disabled=false;}
	}
}
	
var X_SaveVirtualBoxStandardParams= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	YahooWin2Hide(); 
	}	
	
	
function SaveVirtualBoxStandardParams(){
		var XHR = new XHRConnection();
		XHR.appendData('name',document.getElementById('name').value);
		XHR.appendData('basefolder',document.getElementById('basefolder').value);
		XHR.appendData('ostype',document.getElementById('ostype').value);
		XHR.appendData('memorysize',document.getElementById('memorysize').value);
		XHR.appendData('vramsize',document.getElementById('vramsize').value);
		XHR.appendData('guestmemoryballoon',document.getElementById('guestmemoryballoon').value);
		XHR.appendData('cpus',document.getElementById('cpus').value);
		if(document.getElementById('acpi').checked){XHR.appendData('acpi',1);}else{XHR.appendData('acpi',0);}
		if(document.getElementById('ioapic').checked){XHR.appendData('ioapic',1);}else{XHR.appendData('ioapic',0);}
		if(document.getElementById('rtcuseutc').checked){XHR.appendData('rtcuseutc',1);}else{XHR.appendData('rtcuseutc',0);}
		if(document.getElementById('hwvirtex').checked){XHR.appendData('hwvirtex',1);}else{XHR.appendData('hwvirtex',0);}
		if(document.getElementById('nestedpaging').checked){XHR.appendData('nestedpaging',1);}else{XHR.appendData('nestedpaging',0);}
		if(document.getElementById('largepages').checked){XHR.appendData('largepages',1);}else{XHR.appendData('largepages',0);}
		if(document.getElementById('vtxvpid').checked){XHR.appendData('vtxvpid',1);}else{XHR.appendData('vtxvpid',0);}
		if(document.getElementById('hwvirtexexcl').checked){XHR.appendData('hwvirtexexcl',1);}else{XHR.appendData('hwvirtexexcl',0);}
		if(document.getElementById('accelerate3d').checked){XHR.appendData('accelerate3d',1);}else{XHR.appendData('accelerate3d',0);}
		document.getElementById('VirtualBoxTAP1').innerHTML='<center><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',X_SaveVirtualBoxStandardParams);		
	}
	
	hwvirtexSwitch();
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function nat_settings(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();
	$infos=$sock->getFrameWork("cmd.php?virtualbox-showvminfo=yes&uuid=".base64_encode($_GET["uuid"]));
	$array=unserialize(base64_decode($infos));	
	
	
	$nics["82540EM"]="e1000";
	$nics["82543GC"]="e1000";
	$nics["82545EM"]="e1000";
	$nics["Am79C970A"]="pcnet";
	$nics["Am79C973"]="pcnet";
	
	$nic1=$array["NIC 1"];
	if(preg_match("#Type: (.+?),#",$nic1,$re)){$nic1=$re[1];}
	$Type=$nics[$nic1];
	
	$html="
	<div id='vbownatid'><strong style='font-size:16px'>{nic} N°1:&nbsp;$nic1&nbsp;|&nbsp;{type}:$Type</strong>
	<div class=explain>{vbox_nat_configuration_explain}</div>
	<table class=form>
	<tr>
		<td class=legend>{host_port}:</td>
		<td>". Field_text("{$_GET["uuid"]}-host_port",null,"font-size:16px;padding:3px;width:90px","script:VirtualBoxAddNatPortchk(event)")."</td>
		<td class=legend>{virtual_machine_port}:</td>
		<td>". Field_text("{$_GET["uuid"]}-vbox_port",null,"font-size:16px;padding:3px;width:90px","script:VirtualBoxAddNatPortchk(event)")."</td>
	</tr>
	<tr>
		<td colspan=4 align='right'><hr>". button("{add}","VirtualBoxAddNatPort()")."</td>
	</tr>	
	</table>
	<div id='vboxnat-list-{$_GET["uuid"]}' style='width:100%;height:250px;overflow:auto'></div>
	
	
	</div>
	
	<script>
var X_VirtualBoxAddNatPort= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	RefreshTab('main_config_{$_GET["uuid"]}');
	}	
	
	function VirtualBoxAddNatPortchk(e){
		if(checkEnter(e)){VirtualBoxAddNatPort();}
	}
	
	
function VirtualBoxAddNatPort(){
		var XHR = new XHRConnection();
		XHR.appendData('nat-save','yes');
		XHR.appendData('host_port',document.getElementById('{$_GET["uuid"]}-host_port').value);
		XHR.appendData('vbox_port',document.getElementById('{$_GET["uuid"]}-vbox_port').value);
		XHR.appendData('uuid','{$_GET["uuid"]}');
		document.getElementById('vbownatid').innerHTML='<center><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',X_VirtualBoxAddNatPort);		
	}	
	
	function RefreshNatPorts(){
		LoadAjax('vboxnat-list-{$_GET["uuid"]}','$page?nat-list=yes&uuid={$_GET["uuid"]}');
	}
	
	function DeleteVboxNat(from,to){
		var XHR = new XHRConnection();
		XHR.appendData('nat-del','yes');
		XHR.appendData('host_port',from);
		XHR.appendData('vbox_port',to);
		XHR.appendData('uuid','{$_GET["uuid"]}');
		document.getElementById('vbownatid').innerHTML='<center><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',X_VirtualBoxAddNatPort);		
	}
	
	function RebuildNat(){
		var XHR = new XHRConnection();
		XHR.appendData('nat-rebuild','yes');
		XHR.appendData('uuid','{$_GET["uuid"]}');
		document.getElementById('vbownatid').innerHTML='<center><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',X_VirtualBoxAddNatPort);	
	}
	
		RefreshNatPorts();
	</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}


function nat_add(){
	$vbx=new virtualbox($_GET["uuid"]);
	echo $vbx->AddNatPort($_GET["host_port"],$_GET["vbox_port"]);
	$tpl=new templates();
	echo $tpl->javascript_parse_text("{vbox_need_reboot_text}");
	
}

function nat_rebuild(){
	$vbx=new virtualbox($_GET["uuid"]);
	echo $vbx->RebuildNats();
	$tpl=new templates();
	echo $tpl->javascript_parse_text("{vbox_need_reboot_text}");	
}

function nat_del(){
	$vbx=new virtualbox($_GET["uuid"]);
	echo $vbx->DelNatPort($_GET["host_port"],$_GET["vbox_port"]);
	$tpl=new templates();
	echo $tpl->javascript_parse_text("{vbox_need_reboot_text}");		
}

function guestmemoryballoon(){
	$vbx=new virtualbox($_GET["uuid"]);
	echo $vbx->guestmemoryballoon($_GET["guestmemoryballoon"]);
}

function VboxCpus(){
	$vbx=new virtualbox($_GET["uuid"]);
	echo $vbx->SaveSettingsInt($_GET["VboxCpus"],"cpus");	
	
}

function nestedpaging(){
	$vbx=new virtualbox($_GET["uuid"]);
	echo $vbx->SaveSettingsOnOff($_GET["nestedpaging"],"nestedpaging");	
}

function vtxvpid(){
	$vbx=new virtualbox($_GET["uuid"]);
	echo $vbx->SaveSettingsOnOff($_GET["vtxvpid"],"vtxvpid");	
}
function acpi(){
	$vbx=new virtualbox($_GET["uuid"]);
	echo $vbx->SaveSettingsOnOff($_GET["acpi"],"acpi");	
}
function ioapic(){
	$vbx=new virtualbox($_GET["uuid"]);
	echo $vbx->SaveSettingsOnOff($_GET["ioapic"],"ioapic");		
}
function pagefusion(){
	$vbx=new virtualbox($_GET["uuid"]);
	echo $vbx->SaveSettingsOnOff($_GET["pagefusion"],"pagefusion");		
}

function hpet(){
	$vbx=new virtualbox($_GET["uuid"]);
	echo $vbx->SaveSettingsOnOff($_GET["hpet"],"hpet");	
}

function largepages(){
	$vbx=new virtualbox($_GET["uuid"]);
	echo $vbx->SaveSettingsOnOff($_GET["largepages"],"largepages");		
}

function guestmemory(){
	$vbx=new virtualbox($_GET["uuid"]);
	echo $vbx->SaveSettingsInt($_GET["guestmemory"],"memory");	
}
function vram(){
	$vbx=new virtualbox($_GET["uuid"]);
	echo $vbx->SaveSettingsInt($_GET["vram"],"vram");	
}
function vbox_kill(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?kill-pid-single={$_GET["pid-kill"]}");
	}



function nat_list(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$sql="SELECT * FROM virtualbox_nat WHERE vboxid='{$_GET["uuid"]}'";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,'artica_backup');
	if(!$q->ok){
		if(preg_match("#virtualbox_nat' doesn't exist#",$q->mysql_error)){
			$q->CheckTableThinClient();
			echo "<H2>Please refresh, tables are installed</H2>";
			return;
		}
		
		echo $q->mysql_error;return;
	}
	$html="
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
	<thead class='thead'>
		<tr>			
			<th width=1% nowrap>{host_port}</th>
			<th>&nbsp;</th>
			<th width=1% nowrap>{vbox_port}</th>
			<th>&nbsp;</th>
		</tr>
	</thead>
	<tbody class='tbody'>";	
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$html=$html."
		<tr class=$classtr>
		<td style='font-size:16px;font-weight:bold' width=50% align='center'>{$ligne["localport"]}</td>
		<td style='font-size:16px;font-weight:bold' width=1%><img src='img/arrow-right-32.png'></td>
		<td style='font-size:16px;font-weight:bold' width=50% align='center'>{$ligne["vboxport"]}</td>
		<td style='font-size:16px;font-weight:bold' width=1%>". imgtootltip("delete-32.png","{delete}","DeleteVboxNat('{$ligne["localport"]}','{$ligne["vboxport"]}')")."</td>
		</tr>
		";
		
	}
	
	$html=$html."</table>
	<div style='text-align:right'><hr>". button("{rebuild_parameters}","RebuildNat()")."</div>";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}


function oslist(){
	
return array("Other"=>"Other/Unknown",
"Windows31"=>"Windows 3.1",
"Windows95"=>"Windows 95",
"Windows98"=>"Windows 98",
"WindowsMe"=>"Windows Me",
"WindowsNT4"=>"Windows NT 4",
"Windows2000"=>"Windows 2000",
"WindowsXP"=>"Windows XP",
"WindowsXP_64"=>"Windows XP (64 bit)",
"Windows2003"=>"Windows 2003",
"Windows2003_64"=>"Windows 2003 (64 bit)",
"WindowsVista"=>"Windows Vista",
"WindowsVista_64"=>"Windows Vista (64 bit)",
"Windows2008"=>"Windows 2008",
"Windows2008_64"=>"Windows 2008 (64 bit)",
"Windows7"=>"Windows 7",
"Windows7_64"=>"Windows 7 (64 bit)",
"WindowsNT"=>"Other Windows",
"Linux22"=>"Linux 2.2",
"Linux24"=>"Linux 2.4",
"Linux24_64"=>"Linux 2.4 (64 bit)",
"Linux26"=>"Linux 2.6",
"Linux26_64"=>"Linux 2.6 (64 bit)",
"ArchLinux"=>"Arch Linux",
"ArchLinux_64"=>"Arch Linux (64 bit)",
"Debian"=>"Debian",
"Debian_64"=>"Debian (64 bit)",
"OpenSUSE"=>"openSUSE",
"OpenSUSE_64"=>"openSUSE (64 bit)",
"Fedora"=>"Fedora",
"Fedora_64"=>"Fedora (64 bit)",
"Gentoo"=>"Gentoo",
"Gentoo_64"=>"Gentoo (64 bit)",
"Mandriva"=>"Mandriva",
"Mandriva_64"=>"Mandriva (64 bit)",
"RedHat"=>"Red Hat",
"RedHat_64"=>"Red Hat (64 bit)",
"Turbolinux"=>"Turbolinux",
"Turbolinux"=>"Turbolinux (64 bit)",
"Ubuntu"=>"Ubuntu",
"Ubuntu_64"=>"Ubuntu (64 bit)",
"Xandros"=>"Xandros",
"Xandros_64"=>"Xandros (64 bit)",
"Oracle"=>"Oracle",
"Oracle_64"=>"Oracle (64 bit)",
"Linux"=>"Other Linux",
"Solaris"=>"Solaris",
"Solaris_64"=>"Solaris (64 bit)",
"OpenSolaris"=>"OpenSolaris",
"OpenSolaris_64"=>"OpenSolaris (64 bit)",
"FreeBSD"=>"FreeBSD",
"FreeBSD_64"=>"FreeBSD (64 bit)",
"OpenBSD"=>"OpenBSD",
"OpenBSD_64"=>"OpenBSD (64 bit)",
"NetBSD"=>"NetBSD",
"NetBSD_64"=>"NetBSD (64 bit)",
"OS2Warp3"=>"OS/2 Warp 3",
"OS2Warp4"=>"OS/2 Warp 4",
"OS2Warp45"=>"OS/2 Warp 4.5",
"OS2eCS"=>"eComStation",
"OS2"=>"Other OS/2",
"MacOS"=>"Mac OS X Server",
"MacOS_64"=>"Mac OS X Server (64 bit)",
"DOS"=>"DOS",
"Netware"=>"Netware",
"L4"=>"L4",
"QNX"=>"QNX");
}
function oslistIMG($os){
	
$arr= array("Other"=>null,
"Windows31"=>"wink3_bg.png",
"Windows95"=>"wink3_bg.png",
"Windows98"=>"wink3_bg.png",
"WindowsMe"=>"wink3_bg.png",
"WindowsNT4"=>"wink3_bg.png",
"Windows2000"=>"wink3_bg.png",
"WindowsXP"=>"wink3_bg.png",
"WindowsXP_64"=>"wink3_bg.png",
"Windows2003"=>"wink3_bg.png",
"Windows2003_64"=>"wink3_bg.png",
"WindowsVista"=>"wink3_bg.png",
"WindowsVista_64"=>"wink3_bg.png",
"Windows2008"=>"wink3_bg.png",
"Windows2008_64"=>"wink3_bg.png",
"Windows7"=>"wink3_bg.png",
"Windows7_64"=>"wink3_bg.png",
"WindowsNT"=>"wink3_bg.png",
"Linux22"=>"linux-inside-64.png",
"Linux24"=>"linux-inside-64.png",
"Linux24_64"=>"linux-inside-64.png",
"Linux26"=>"linux-inside-64.png",
"Linux26_64"=>"linux-inside-64.png",
"ArchLinux"=>"linux-inside-64.png",
"ArchLinux_64"=>"linux-inside-64.png",
"Debian"=>"DEBIAN.png",
"Debian_64"=>"DEBIAN.png",
"OpenSUSE"=>"SUSE.png",
"OpenSUSE_64"=>"SUSE.png",
"Fedora"=>"FEDORA.png",
"Fedora_64"=>"FEDORA.png",
"Gentoo"=>"linux-inside-64.png",
"Gentoo_64"=>"linux-inside-64.png",
"Mandriva"=>"MANDRAKE.png",
"Mandriva_64"=>"MANDRAKE.png",
"RedHat"=>"Red Hat",
"RedHat_64"=>"Red Hat (64 bit)",
"Turbolinux"=>"Turbolinux",
"Turbolinux"=>"Turbolinux (64 bit)",
"Ubuntu"=>"UBUNTU.png",
"Ubuntu_64"=>"UBUNTU.png",
"Xandros"=>"linux-inside-64.png",
"Xandros_64"=>"linux-inside-64.png",
"Oracle"=>"Oracle",
"Oracle_64"=>"Oracle (64 bit)",
"Linux"=>"linux-inside-64.png",
"Solaris"=>"Solaris",
"Solaris_64"=>"Solaris (64 bit)",
"OpenSolaris"=>"OpenSolaris",
"OpenSolaris_64"=>"OpenSolaris (64 bit)",
"FreeBSD"=>"FreeBSD",
"FreeBSD_64"=>"FreeBSD (64 bit)",
"OpenBSD"=>"OpenBSD",
"OpenBSD_64"=>"OpenBSD (64 bit)",
"NetBSD"=>"NetBSD",
"NetBSD_64"=>"NetBSD (64 bit)",
"OS2Warp3"=>"OS/2 Warp 3",
"OS2Warp4"=>"OS/2 Warp 4",
"OS2Warp45"=>"OS/2 Warp 4.5",
"OS2eCS"=>"eComStation",
"OS2"=>"Other OS/2",
"MacOS"=>"Mac OS X Server",
"MacOS_64"=>"Mac OS X Server (64 bit)",
"DOS"=>"DOS",
"Netware"=>"Netware",
"L4"=>"L4",
"QNX"=>"QNX");

if(is_file("img/{$arr["$os"]}")){return "{$arr["$os"]}";}else{return "computer-tour-64.png";}

}
