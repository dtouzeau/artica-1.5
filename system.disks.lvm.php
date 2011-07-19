<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.ldap.inc');
	
	$user=new usersMenus();
	if(!$user->AsSystemAdministrator){echo "alert('no privileges');";die();}
	if(isset($_GET["lvm-disks-list"])){disks_list();exit;}
	if(isset($_GET["lvm-disk-add-form"])){disks_add_form();exit;}
	if(isset($_GET["groups-infos"])){group_info();exit;}
	if(isset($_GET["vg-content"])){group_content();exit;}
	if(isset($_GET["lvm-tools"])){tools();exit;}
	
	if(isset($_GET["vgservice"])){vgservice_popup();exit;}
	if(isset($_POST["vgservice-save"])){vgservice_save();exit;}
	
	//lvcreate-popup
	if(isset($_GET["lvcreate-popup"])){lvcreate_popup();exit;}
	if(isset($_POST["lvcreate-perform"])){ACTION_LVM_CREATE_LV();exit;}
	if(isset($_POST["lvs-remove"])){ACTION_LVS_REMOVE();exit;}
	
	//actions
	if(isset($_GET["LVM_CONVERT_DEV"])){ACTION_LVM_CONVERT_DEV();exit;}
	if(isset($_GET["LVM_CREATE_GROUP"])){ACTION_LVM_CREATE_GROUP();exit;}
	
	
	
	start();
	
	
	
function start(){
	$page=CurrentPageName();
	$html="<table style='width:720px'>
	<tr>
		<td valign='top' width=1%><div id='lvm-tools'></div>
		<td valign='top' width=99%><div id='lvm-disks-list'></div>
	</tr>
	</table>
	
	
	<script>
		function refreshLVMList(){
			LoadAjax('lvm-disks-list','$page?lvm-disks-list=yes');
		}
	refreshLVMList();
	</script>
	
	";
	echo $html;
	
}

function disks_list(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();
	$lvmdiskscan_datas=base64_decode($sock->getFrameWork("cmd.php?lvmdiskscan=yes"));
	$lvmdiskscan=unserialize($lvmdiskscan_datas);
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th>$add</th>
		<th>{disk}</th>
		<th>{group}</th>
		<th>{size}</th>
		<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";	
if(is_array($lvmdiskscan)){	
	while (list ($dev, $size) = each ($lvmdiskscan) ){	
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$devmd=md5($dev);
		$scripts[]="LoadAjaxSilent('$devmd-group','$page?groups-infos=yes&dev=".urlencode($dev)."');";
		$scripts_colapse[]="document.getElementById('$devmd-vs').innerHTML='';";
		$html=$html."
		<tr class=$classtr>
			<td width=1%><img src='img/disk-32.png'></td>
			<td><strong style='font-size:14px'>$dev</td>
			<td width=1% nowrap><span id='$devmd-group'></span></td>
			<td width=1% nowrap><strong style='font-size:14px'>$size</strong></td>
			<td width=1% nowrap>&nbsp;</td>
		</tr>
		<tr class=$classtr style='height:auto'>
			<td colspan=5 style='height:auto'><span id='$devmd-vs'></span></td>
		</tr>
		
		";			
			
	}
	
}
	$html=$html."</table>
	<script>
		function RefreshGroupInfos(){
		".@implode("\n",$scripts)."
		LoadAjax('lvm-tools','$page?lvm-tools=yes');
		}
		
		function hideAlllvs(){
		".@implode("\n",$scripts_colapse)."
		}
		

		
	
	RefreshGroupInfos();
	</script>
	";
	echo $tpl->_ENGINE_parse_body($html);

}
function group_info(){
	$dev=$_GET["dev"];
	$devmd=md5($dev);
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();
	$pvdisplay=unserialize(base64_decode($sock->getFrameWork("lvm.php?pvdisplay-dev=yes&dev=".urlencode($_GET["dev"]))));
	$groupname=$pvdisplay[$dev]["GROUP"];
	$groupnamemd=md5($groupname);
	if($groupname==null){
		$html="<center>". imgtootltip('plus-24.png',"{create_lvm_group}","CreateLVMGroupOf('$dev')")."</center>";
		echo $tpl->_ENGINE_parse_body($html);
		return;
	}
	
	$groupname_uri=urlencode($groupname);
	echo "<strong style='font-size:14px'><a href=\"javascript:blur();\" 
	OnClick=\"javascript:ExpanVG_$groupnamemd();\"
	style='font-size:14px;text-decoration:underline;font-weight:bold'>$groupname</a></strong>
	
	<script>
		function ExpanVG_$groupnamemd(){
			hideAlllvs();
			LoadAjaxSilent('$devmd-vs','$page?vg-content=yes&dev=$dev&vg=$groupname_uri');
		}
	</script>	
	
	";
}

function tools(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{ADD_LVM_DISK}');
	$create_lvm_group=$tpl->javascript_parse_text('{create_lvm_group}');
	$addHD=Paragraphe("usb-disk-64-2-add.png","$title","{ADD_LVM_DISK_TEXT}","javascript:AddlvmDisk()");
	
	
	$html="
	$addHD<br>
	
	
	<script>
		function AddlvmDisk(){
			YahooWin2(510,'$page?lvm-disk-add-form=yes','$title');
		}
		
	var x_CreateLVMGroupOf= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		refreshLVMList();
	}			
		
	function CreateLVMGroupOf(dev){
		var gpname=prompt('$create_lvm_group');
		if(gpname){
			var XHR = new XHRConnection();
			XHR.appendData('LVM_CREATE_GROUP',gpname);
			XHR.appendData('dev',dev);
			document.getElementById('lvm-disks-list').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_CreateLVMGroupOf);			
			}
	}
		
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function disks_add_form(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();
	$LVM_CONVERT_DEV_WARN=$tpl->javascript_parse_text("{LVM_CONVERT_DEV_WARN}");
	$disks1=unserialize(base64_decode($sock->getFrameWork("lvm.php?lvmdisk-free")));	
	while (list ($dev, $size) = each ($disks1) ){	
		$serv[]=Paragraphe32("noacco:$dev","<div style='font-size:14px;font-weight:bold'>$size</div>","LVM_CONVERT_DEV('$dev')","hd-toolbar-add-32.png");
		
	}
	
$tables[]="<table style='width:100%'><tr>";
$t=0;
while (list ($key, $line) = each ($serv) ){
		$line=trim($line);
		if($line==null){continue;}
		$t=$t+1;
		$tables[]="<td valign='top'>$line</td>";
		if($t==2){$t=0;$tables[]="</tr><tr>";}
		}

if($t<2){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top'>&nbsp;</td>";				
	}
}
				
	$tables[]="</table>";	
	$html_tables=@implode("\n",$tables);
	
	$html="
	<div class=explain>{LVM_CONVERT_DEV_EXPLAIN}</div>
	<hr>
	$html_tables
	
	<script>
	var x_LVM_CONVERT_DEV= function (obj) {
				var results=obj.responseText;
				if(results.length>0){alert(results);}
				refreshLVMList();
				YahooWin2Hide();
			}	
	
	
		function LVM_CONVERT_DEV(dev){
			if(confirm('$LVM_CONVERT_DEV_WARN')){
				var XHR = new XHRConnection();
				XHR.appendData('LVM_CONVERT_DEV',dev);
				XHR.appendData('dev',dev);
				document.getElementById('lvm-disks-list').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
				XHR.sendAndLoad('$page', 'GET',x_LVM_CONVERT_DEV);
			}
		}
		
	</script>
	";
		echo $tpl->_ENGINE_parse_body($html);

}

function ACTION_LVM_CONVERT_DEV(){
	$sock=new sockets();
	$datas=base64_decode($sock->getFrameWork("lvm.php?convert-disk=yes&dev=".urlencode($_GET["dev"])));
	echo $datas;
}

function ACTION_LVM_CREATE_GROUP(){
	$groupname=$_GET["LVM_CREATE_GROUP"];
	include_once(dirname(__FILE__)."/ressources/class.html.tools.inc");
	$t=new htmltools_inc();
	$groupname=$t->StripSpecialsChars($groupname);
	$sock=new sockets();
	$datas=base64_decode($sock->getFrameWork("lvm.php?vgcreate=yes&dev=".urlencode($_GET["dev"])."&gpname=".urlencode($groupname)));
	echo $datas;
}

function group_content(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$vg=$_GET["vg"];
	$dev=$_GET["dev"];
	$md=md5($vg);
	$add_new_disk=$tpl->_ENGINE_parse_body("{add_new_disk}");
	$delete_disk_confirm=$tpl->javascript_parse_text("{delete_disk_confirm}");
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->getFrameWork("lvm.php?vgdisplay=yes&gpname=".urlencode($vg))));	
	$vgservices=unserialize(base64_decode($sock->GET_INFO("vgservices")));
	$size=FormatBytes($array["SIZE"]);
	$free=FormatBytes($array["FREE"]);
	$styleadd="background:transparent;border:0px;height:auto";
	
	$tool_lvcreate=imgtootltip("hd-toolbar-add-32.png","{add_new_disk}","lvcreate_$md()");
	if($vgservices[$vg]<>null){
		$tool_lvcreate=imgtootltip("hd-toolbar-add-32-grey.png","{add_new_disk}","");
		$title_service="&nbsp;{affected}:{$vgservices[$vg]}&nbsp;";
	}
	
	$tools="
	
	<center>
	<table style='width:5%;$styleadd'>
	<tr style='$styleadd'>
		<td width=1% nowrap style='$styleadd'><div style='font-size:13px'><i>{$_GET["vg"]}&nbsp;$size ($free {free})$title_service</i></div></td>
		<td width=1% style='$styleadd'>$tool_lvcreate</td>
		<td width=1% style='$styleadd'>". imgtootltip("32-nodes.png","{affect_vg_to_service}","vgservice_$md()")."</td>
		
		
		
	</tr>
	</table>
	</center>
	";
	
	$table="
	<center>
	<table style='width:80%;padding:3px;margin:3px;border:2px solid #CCCCCC'>";
	
	if(is_array($array["VS"])){
		while (list ($name, $arrayVS) = each ($array["VS"])){
			$size=$arrayVS["SIZE"];
			$size=FormatBytes($size);
			$crrentsize=$arrayVS["CURRENT_SIZE"];
			$crrentsize_pourc=$crrentsize["POURC"];
			if(is_numeric($crrentsize_pourc)){$crrentsize_pourc=$crrentsize_pourc."%";}
			$js="Loadjs('system.disks.lvs.php?lvs=$name&vg=$vg');";
			$delete=imgtootltip("delete-32.png","{delete}","lvmLVDelete('$name')");
			$href="<a href=\"javascript:blur()\"
			OnClick=\"javascript:$js\"
			style='font-size:14px;font-weight:bold;text-decoration:underline'>";
			
			
			$table=$table."
			<tr style='$styleadd'>
				<td width=1% style='$styleadd' valign='middle'><img src='img/usb-disk-32.png'></td>
				<td style='font-size:14px;font-weight:bold;$styleadd' valign='middle'>$href$name</a></td>
				<td style='font-size:14px;font-weight:bold;$styleadd' width=1% valign='middle' nowrap>$size <span style='font-size:11px'>($crrentsize_pourc)</span></td>
				<td style='font-size:14px;font-weight:bold;$styleadd' width=1% valign='middle'>$delete</td>
			</tr>
			
			";
		}
		
	}
	$table=$table."</table>
	</center>";
	$html=$tools."
	$table
	
	<script>
	
	function lvcreate_$md(){
		YahooWin2('385','$page?lvcreate-popup=yes&gpname=$vg&dev=$dev&vg=$vg','$vg&raquo;&raquo;$add_new_disk');
	}
	
	function vgservice_$md(){
		YahooWin2('400','$page?vgservice=yes&vg=$vg','$vg&raquo;&raquo;');
	
	}
	
	var x_lvmLVDelete= function (obj) {
				var results=obj.responseText;
				if(results.length>0){alert(results);}
				ExpanVG_$md();
			}	
			
		function lvmLVDelete(lvs){
				var XHR = new XHRConnection();
				if(confirm(lvs+':$delete_disk_confirm')){
					XHR.appendData('lvs-remove','yes');
					XHR.appendData('vg','$vg');
					XHR.appendData('lvs',lvs);
					XHR.sendAndLoad('$page', 'POST',x_lvmLVDelete);
				}
			}	
	
	
	</script>";
	
	echo $tpl->_ENGINE_parse_body($html);
}

function lvcreate_popup(){
	$page=CurrentPageName();
	$tpl=new templates();		
	$sock=new sockets();
	$vg=$_GET["vg"];
	$dev=$_GET["dev"];	
	$groupnamemd=md5($vg);
	
	
	$array=unserialize(base64_decode($sock->getFrameWork("lvm.php?vgdisplay=yes&gpname=".urlencode($vg))));	
	$size=FormatBytes($array["SIZE"]);
	$free=FormatBytes($array["FREE"]);	
	
	
	$html="<div class=explain>{lvcreate_explain}</div>
	<p>&nbsp;</p>
	<div style='font-size:18px'><i>{$_GET["vg"]}&nbsp;$size ($free {free})</i></div>
	<div style='font-size:16px'>
	<table style='width:100%' class=form>
	<tr>
		<td class=legend style='font-size:16px'>{disk_name}:</td>
		<td style='font-size:16px'>". Field_text("VGNAME",null,"font-size:16px;width:120px")."</td>
	</tr>	
	<tr>
		<td class=legend style='font-size:16px'>{size}:</td>
		<td style='font-size:16px'>". Field_text("LVSIZE",null,"font-size:16px;width:90px","script:VgCreatePerformCheck(event)")."&nbsp;MB</td>
	</tr>
	<tr>
		<td colspan=2 align='right' style='font-size:16px'><hr>". button("{create}","VgCreatePerform()")."</td>
	</tr>
	</table>
	
	<script>
	var x_VgCreatePerform= function (obj) {
				var results=obj.responseText;
				if(results.length>0){alert(results);}
				ExpanVG_$groupnamemd();
				YahooWin2Hide();
			}	
			
		function VgCreatePerformCheck(e){
			if(checkEnter(e)){
				VgCreatePerform();
			}
		}
	
	
		function VgCreatePerform(){
				var XHR = new XHRConnection();
				XHR.appendData('lvcreate-perform','$dev');
				XHR.appendData('VG','$vg');
				XHR.appendData('LVNAME',document.getElementById('VGNAME').value);
				XHR.appendData('LVSIZE',document.getElementById('LVSIZE').value);
				XHR.sendAndLoad('$page', 'POST',x_VgCreatePerform);
			}
	</script>	
	";
	echo $tpl->_ENGINE_parse_body($html);	
}

function ACTION_LVM_CREATE_LV(){
	$sock=new sockets();
	$vg=$_POST["VG"];
	$dev=$_POST["lvcreate-perform"];
	$lvname=$_POST["LVNAME"];
	$lvsize=$_POST["LVSIZE"];	
	$t=new htmltools_inc();
	$lvname=$t->StripSpecialsChars($lvname);
	$lvname=urlencode($lvname);
	$sock=new sockets();
	writelogs("create \"$lvname\" in $vg for {$lvsize}M",__FUNCTION__,__FILE__,__LINE__);
	
	$datas=base64_decode($sock->getFrameWork("lvm.php?lvcreate=yes&lvname=$lvname&lvsize=$lvsize&dev=".urlencode($_GET["dev"])."&gpname=".urlencode($vg)));
	echo $datas;		
	
}

function ACTION_LVS_REMOVE(){
	include_once('ressources/class.autofs.inc');
	$sock=new sockets();	
	$lvs=$_POST["lvs"];
	$vg=$_POST["vg"];			
	$array=unserialize(base64_decode($sock->getFrameWork("lvm.php?lvdisplay=".urlencode($vg))));	
	$status=$array["/dev/$vg/$lvs"];
	if($status["INFOS"]["UUID"]<>null){
		$auto=new autofs();
		$auto->uuid=$status["INFOS"]["UUID"];
		$auto->by_uuid_addmedia("$vg-$lvs","auto");
	}
	
	$datas=base64_decode($sock->getFrameWork("lvm.php?lvremove=yes&lvs=$lvs&vg=$vg"));
	echo $datas;
}

function vgservice_popup(){
	$page=CurrentPageName();
	$tpl=new templates();		
	$sock=new sockets();	
	$users=new usersMenus();
	$vg=$_GET["vg"];	
	$services[null]="{select}";
	$services["freewebs"]="FreeWebs";
	if($users->LXC_INSTALLED){$services["lxc"]="{APP_LXC}";}
	$groupnamemd=md5($vg);
	$vgservices=unserialize(base64_decode($sock->GET_INFO("vgservices")));
	$html="<div class=explain>{affect_vg_to_service_explain}</div>
	<table class=form style='width:100%'>
	<tr>
		<td class=legend style='font-size:16px'>{service}:</td>
		<td>". Field_array_Hash($services,"vg_serv",$vgservices[$vg],"style:font-size:16px;padding:3px")."</td>
	</tr>
	<tr>
		<td colspan=2 align='right'><hr>". button("{apply}","VgServSave()")."</td>
	</tr>
	</table>
	<script>
	var x_VgServSave= function (obj) {
				var results=obj.responseText;
				if(results.length>0){alert(results);}
				ExpanVG_$groupnamemd();
				YahooWin2Hide();
			}	
	
		function VgServSave(){
				var XHR = new XHRConnection();
				XHR.appendData('vgservice-save','yes');
				XHR.appendData('vg','$vg');
				XHR.appendData('service',document.getElementById('vg_serv').value);
				XHR.sendAndLoad('$page', 'POST',x_VgServSave);
			}	
	
	</script>";
	echo $tpl->_ENGINE_parse_body($html);
	
}
function vgservice_save(){
	$sock=new sockets();
	$vgservices=unserialize(base64_decode($sock->GET_INFO("vgservices")));
	$vgservices[$_POST["vg"]]=$_POST["service"];
	$vgservices[$_POST["service"]]=$_POST["vg"];
	$datas=base64_encode(serialize($vgservices));
	$sock->SaveConfigFile($datas,"vgservices");
}

?>