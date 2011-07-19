<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.mysql.inc');	
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.backup.inc');
	include_once('ressources/class.os.system.inc');
	
include_once('ressources/class.ntpd.inc');	
	$user=new usersMenus();
	if($user->AsArticaAdministrator==false){header('location:users.index.php');exit();}
	if(isset($_GET["main"])){main_switch();exit;}
	if(isset($_GET["status"])){echo main_status();exit;}
	if(isset($_GET["op"])){main_switch_op();exit;}
	if(isset($_GET["ArticaBackupEnabled"])){save();exit;}
	if(isset($_GET["disk_usb_add"])){add_usb_disk();exit;}
	if(isset($_GET["disk_usb_del"])){del_usb_disk();exit;}
	if(isset($_GET["AddPersonalizeFolder"])){add_Personalize_Folder();exit;}
	if(isset($_GET["DeletePersonalizeFolder"])){del_Personalize_Folder();exit;}
	if(isset($_GET["RestoreSingleFile"])){RestoreSingleFile();exit;}
	if(isset($_GET["RestoreDatabase"])){RestoreDatabase();exit;}
	if(isset($_GET["PerformRestoreDatabase"])){RestoreDatabase2();exit;}
	if(isset($_GET["jsscript"])){main_switch_scripts();exit;}
	if($_GET["popup"]=="addshare"){main_share_retranslation_add_popup();exit;}
	if(isset($_GET["addnewsharebackup"])){main_share_retranslation_save();exit;}
	
	main_page();
	
function main_page(){
	

	$page=CurrentPageName();
	$html=
	"
<script language=\"JavaScript\"> 

var timerID  = null;
var timerID1  = null;
var tant=0;
var reste=0;

function demarre(){
   tant = tant+1;
   reste=10-tant;
	if (tant < 10 ) {                           
      timerID = setTimeout(\"demarre()\",5000);
      } else {
               tant = 0;
               //document.getElementById('wait').innerHTML='<img src=img/wait.gif>';
               ChargeLogs();
               demarre();                                //la boucle demarre !
   }
}


function ChargeLogs(){
	LoadAjax('services_status','$page?status=yes&hostname={$_GET["hostname"]}');
	if(document.getElementById('target_disks_list')){
		LoadAjax('target_disks_list','$page?main=refresh_disk_list&hostname={$_GET["hostname"]}');
	}
	if(document.getElementById('target_selected_list')){
		LoadAjax('target_selected_list','$page?main=selected_disk_list&hostname={$_GET["hostname"]}');
	}
	
	if(document.getElementById('dar_status')){
		LoadAjax('dar_status','$page?main=dar_status&hostname={$_GET["hostname"]}');
	}
	if(document.getElementById('networks_shares')){
		LoadAjax('networks_shares','$page?main=networks_shares');
	}
	
}

function SelectDisk(dd){
	YahooWin2(450,'$page?main=select-disk&disk='+dd);
	}
	
function DiskDelete(uid){
	    var XHR = new XHRConnection();
		XHR.appendData('disk_usb_del',uid);
		XHR.sendAndLoad('$page', 'GET',x_disk_add);		
}
	
var x_disk_add= function (obj) {
	tempvalue=obj.responseText;
	if(tempvalue.length>1){
	alert(tempvalue);
	}
	YAHOO.example.container.dialog2.hide();
	LoadAjax('target_selected_list','$page?main=selected_disk_list&hostname={$_GET["hostname"]}');
	LoadAjax('persopath','$page?main=persopath&hostname={$_GET["hostname"]}');
	
	}	
	
function disk_add(uid){
	var disk_add_caption=document.getElementById('disk_add_caption').value;
	if(confirm(disk_add_caption)){
		var XHR = new XHRConnection();
		XHR.appendData('disk_usb_add',uid);
		XHR.sendAndLoad('$page', 'GET',x_disk_add);	
	
	}
}

function BrowseDareContener(uid){YahooWin(450,'$page?main=BrowseDareContener&disk='+uid);}

function dar_search(e){
	if(e){
		if(!checkEnter(e)){
			return false;
		}
	}
	var uuid=document.getElementById('uuid-disk').value;
	var search=document.getElementById('dar_query').value;
	LoadAjax('dar_results','$page?main=dar_query&hostname={$_GET["hostname"]}&pattern='+search+'&disk='+uuid);
	}
	
function dar_file_info(file){
	var uuid=document.getElementById('uuid').value;
	YahooWin2(350,'$page?main=dar_file_info&file='+file+'&uuid='+uuid);
	}
	
function AddPersonalizeFolder(){
  var folder=document.getElementById('TreeSelectedFolder').value;
  var XHR = new XHRConnection();
  XHR.appendData('AddPersonalizeFolder',folder);
  XHR.sendAndLoad('$page', 'GET',x_disk_add);	
  
}

function DeletePersoPath(num){
  var XHR = new XHRConnection();
  XHR.appendData('DeletePersonalizeFolder',num);
  XHR.sendAndLoad('$page', 'GET',x_disk_add);

}

var x_RestoreSingleFile= function (obj) {
	tempvalue=obj.responseText;
	alert(tempvalue);
	}

function RestoreSingleFile(database,file,uuid){
  YahooWin3(500,'$page?RestoreSingleFile='+file+'&database='+database+'&uuid='+uuid,'restore');
}
	
function RestoreDatabase(database,uuid){
		YahooWin3(500,'$page?RestoreDatabase='+database+'&uuid='+uuid,'restoring database');
		}
		
function PerformRestore(){		
	var target_folder=document.getElementById('restore_path').value;	
	var uuid=document.getElementById('uuid').value;	
	var database=document.getElementById('database').value;		
	YahooWin4(500,'$page?PerformRestoreDatabase=yes&database='+database+'&uuid='+uuid+'&target_folder='+target_folder,'restoring database');
	}
	
function DeleteRemoteShare(num){
	LoadAjax('share_list','$page?main=share_list&delete='+num);

}

function ConnectShare(num){
	LoadAjax('networks_shares','$page?main=networks_shares&connect='+num);
}

function NetUmount(uuid){
	LoadAjax('networks_shares','$page?main=networks_shares&disconnect='+uuid);
}
	
</script>	
	
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'><img src='img/bg_backup.jpg' style='margin-right:80px'></td>
	<td valign='top'><div id='services_status'>". main_status() . "</div><br>
		<p class=caption>{about}</p>
	
	
	</td>
	</tr>
	<tr>
		<td colspan=2 valign='top'><br>
			<div id='main_config'></div>
		</td>
	</tr>
	</table>
	<script>demarre();LoadAjax('main_config','$page?main=yes');</script>
	
	";
	
	$cfg["JS"][]='js/iptables.js';
	$tpl=new template_users('{APP_BACKUP}',$html,0,0,0,0,$cfg);
	echo $tpl->web_page;
	}	

function main_tabs(){
	if(!isset($_GET["main"])){$_GET["main"]="yes";};
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}
	$page=CurrentPageName();
	$array["yes"]='{main_settings}';
	$array["disk_retranslation"]='{disk_retranslation}';
	$array["share_retranslation"]='{share_retranslation}';
	$array["logs"]='{events}';	
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["main"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('main_config','$page?main=$num&hostname=$hostname')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";		
}



function main_switch(){
	
	switch ($_GET["main"]) {
		case "yes":main_config();exit;break;
		case "logs":main_logs();exit;break;
		case "disk_retranslation":main_disk_retranslation();exit;break;
		case "refresh_disk_list":echo main_disks_discover();exit;break;
		case "selected_disk_list":echo selected_disk_list();exit;break;
		case "nic":echo main_rules_list();exit;break;
		case "select-disk":echo main_select_disk();exit;break;
		case "dar_status":echo dar_status();exit;break;
		case "dar_query":echo dar_search_files();exit;break;
		case "BrowseDareContener":echo dar_conteners();exit;break;
		case "dar_file_info":echo dar_file_info();exit;break;
		case "persopath":echo main_disk_persopath_list();exit;break;
		case "share_list":echo main_share_retranslation_list();exit;break;
		case "share_retranslation":echo main_share_retranslation();exit;break;
		case "networks_shares":echo main_share_retranslation_list_shares();break;
		
		
		default:
			break;
	}
	
	
}	

function main_status(){

$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}		
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString($sock->getfile('BACKUP_ARTICA_STATUS',$_GET["hostname"]));	
	if($ini->_params["ARTICA_BACKUP"]["application_enabled"]==0){
		$img="ok32-grey.png";
		$status="{disabled}";
	}else{
		$img="ok32.png";
		$status="running";
		
	}
	
	
	$size=$ini->_params["ARTICA_BACKUP"]["master_memory"];
	$b="Ko";

	
	if($size>999){
		$size=round($size/1000,2);
		$b="Mo";
	}
	
	if($size>999){
		$size=round($size/1000,2);
		$b="Go";
	}	
	
	$status="<table style='width:100%'>
	<tr>
	<td valign='top'>
		<img src='img/$img'>
	</td>
	<td valign='top'>
		<table style='width:100%'>
		<tr>
		<td align='right' nowrap><strong>{APP_BACKUP}:</strong></td>
		<td><strong>$status</strong></td>
		</tr>		
		<tr>
		<td align='right'><strong>{evry_day_at}:</strong></td>
		<td nowrap><strong>{$ini->_params["ARTICA_BACKUP"]["master_version"]}</strong></td>
		</tr>
		<tr>
		<td align='right'><strong>{folder_size}:</strong></td>
		<td><strong>$size $b</strong></td>
		</tr>					
		<tr><td colspan=2>$error</td></tr>	
		<tr><td colspan=2>&nbsp;</td></tr>	
		
		</table>		
	</td>
	</tr>
	</table>";
	
	$status=RoundedLightGreen($status);
	$status_serv=RoundedLightGrey(Paragraphe($rouage ,$rouage_title. " (squid)",$rouage_text,"javascript:$js"));
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($status);
	
}


function main_config(){
	$back=new backup();
	 $page=CurrentPageName();
	 $entete=main_tabs()."<br>
	 <H5>{main_settings}</H5>
	 <br>
	 ";
	 
	
	 $page=CurrentPageName();
	 $form="
	 <form name='ffm1'>
	 <table style='width:100%'>
	 <tr>
	 	<td valign='top' align='right' class=legend nowrap>{enable_artica_backup}:</strong></td>
	 	<td>" .  Field_numeric_checkbox_img('ArticaBackupEnabled',$back->ArticaBackupEnabled,'{enable_disable}')."</td>
	 </tr>
	 <tr>
	 	<td valign='top' align='right' class=legend>{backup_path}:</strong></td>
	 	<td>" .  Field_text('backup_path',$back->params["backup_path"],'width:250px')."</td>
	 </tr>	 
	 <tr>
	 	<td valign='top' align='right' class=legend>{backup_time}:</strong></td>
	 	<td>" .  Field_text('backup_time',$back->params["backup_time"],'width:90px')."</td>
	 </tr>
	 <tr>
	 	<td valign='top' align='right' class=legend>{ArticaBackupMaxTimeToLiveInDay}:</strong></td>
	 	<td>" .  Field_text('ArticaBackupMaxTimeToLiveInDay',$back->params["ArticaBackupMaxTimeToLiveInDay"],'width:15px')."&nbsp;{days}</td>
	 </tr>	 
	 <tr>
	 <td colspan=2 align='right'><input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffm1','$page',true);\"></td>
	 </tr>
	 </table>
	 </form>";

$form=RoundedLightGrey($form);
$html="	$entete  
	 <table style='width:100%'>
	 <tr>
	 	<td valign='top'>
	 		$form
		<div id='backuplist'>
		". Backuplist()."
		</div>
		</td>
		<td valign='top'>
	 	" . Paragraphe('system-64.png','{backup_now}','{backup_now_text}',"javascript:StartAction(\"$page\",3);",'apply')."<br>
	 	".RoundedLightBlue('{howto_restore}')."<br>
	 	
	 	</td>
	 </tr>
	 </table>";
	 
	 
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("$html");
	
}



function Backuplist(){
	
	$sock=new sockets();
	$datas=$sock->getfile('BackupFileList');
	$tbl=explode("\n",$datas);
	if(!is_array($tbl)){return null;}
	$html="<br>
	<H3>{storage_list}</H3>
	<table style='width:100%' class=table_form>";
	$currdate=date('Y/m/d');
	while (list ($num, $val) = each ($tbl) ){
		if(!preg_match('#artica-backup-([0-9]+)-([0-9]+)-([0-9]+)-([0-9]+)-([0-9]+)-([0-9]+)\.tar.gz;([0-9]+)#',$val,$re)){continue;}
		preg_match('#file:(.+?);#',$val,$ri);
		
		if($currdate=="{$re[1]}/{$re[2]}/{$re[3]}"){
			$style="style='color:red;font-weight:bold'";
		}else{$style=null;}
		
		$html=$html . "
		<tr>
			<td $style>{$re[1]}/{$re[2]}/{$re[3]}</td>
			<td $style>{$re[4]}:{$re[5]}:{$re[6]}</td>
			<td $style>{$ri[1]}</td>
			<td $style>" . FormatBytes($re[7])."</td>
		</tr>";
		
		
	}
	$html=$html."</table>";
	
	return $html;
}





function main_switch_op_save(){
	$ntp=new backup();
	$ntp->SaveToLdap();
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body( "<strong>{compile_rules_ok}</strong>");
}

function main_switch_op_server(){
	$sock=new sockets();
	$sock->getfile('PerformBackupArtica');
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body( "<strong>{success_sendcommand}</strong>");	
	
}

function main_switch_op_end(){
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body( "<p class=caption>{close_windows}</p>");		
	
}

function main_switch_op(){
	
	switch ($_GET["op"]) {
		case 0:main_switch_op_save();exit;break;
		case 1:main_switch_op_server();exit;break;
		case 2:main_switch_op_end();exit;break;
		default:
			break;
	}
	
	
	$html="
	<H5>{backup_now_text}</H5>
	<table style='width:100%'>
	<tr>
	<td width=1% valign='top'><img src='img/system-64.png'></td>
	<td valign='top'>
		<div id='message_0' style='margin:3px'></div>
		<div id='message_1' style='margin:3px'></div>
		<div id='message_2' style='margin:3px'></div>
		<div id='message_3' style='margin:3px'></div>
	
	</td>
	</tr>
	</table>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}



function main_logs(){
	$page=CurrentPageName();
	$sock=new sockets();
	$datas=$sock->getfile('ArticaBackupLogs');
	
$tbl=explode("\n",$datas);
		$tbl=@array_reverse ($tbl, TRUE);		
		while (list ($num, $val) = each ($tbl) ){
			if(trim($val)<>null){
				$img=statusLogs($val);
			$html=$html . "
			<div style='black;margin-bottom:1px;padding:2px;border-bottom:1px dotted #CCCCCC;border-left:5px solid #CCCCCC;width:99%;margin-left:0px'>
					<table style='width:100%'>
					<tr>
					<td><code style='font-size:9px'>$val</code></td>
					</tr>
					</table>
			</div>";
			}
		}		
	
	$tpl=new templates();
	$html=main_tabs() . "
	<H5>{events}</H5>
	<div style='height:300px;overflow:auto;width:100%'>$html</div>";
	echo $tpl->_ENGINE_parse_body($html);
	}
function save(){
	$back=new backup();
	$back->ArticaBackupEnabled=$_GET["ArticaBackupEnabled"];
	while (list ($num, $ligne) = each ($_GET) ){
		$back->params[$num]=$ligne;
	}
	$tpl=new templates();
	
	if($back->SaveToLdap()){echo $tpl->_ENGINE_parse_body('{success}');}else{echo $back->ldap_error;}
}

function main_disk_retranslation(){

$html=main_tabs()."<H5>{disk_retranslation}</h5>
<table style='width:100%'>
<tr>
<td valign='top' width=70%>
<p class=caption>{disk_retranslation_text}</p>
</td>
<td valign='top' width=30%>
<div id='dar_status'>". dar_status()."</div>
</td>
</table>

<table style='width:100%'>
<tr>
<td valign='top' width=50%' style='padding:9px'>
	<H5>{available_disks}</h5>
	<div id='target_disks_list'>" . main_disks_discover()."</div>
	<br>
	<H5>{personal_folders}</h5>
	<table style='width:100%'>
	<tr>
		<td style='width:99%' width=99%><input type='text' id='TreeSelectedFolder' value='' style='font-size:9px;'></td>
		<td style='width:1%' width=1%>
			<div style='width:100%;text-align:right'><input type='button' OnClick=\"javascript:Loadjs('TreeBrowse.php');\" value='{browse}...&nbsp;&raquo;'></div>
		</td>
	</tr>
	<tr>
		<td colspan=2 align='right' style='border-top:1px solid #CCCCCC'>
			<input type='button' OnClick=\"javascript:AddPersonalizeFolder();\" value='{add}&nbsp;&raquo;'>
		</td>
	</tr>
	</table>
	<div id='persopath'>" . main_disk_persopath_list() . "</div>
	
	
</td>
<td valign='top' width=50%' style='padding:9px'>
	<H5>{selected_disks}</h5>
	<div id='target_selected_list'>" . selected_disk_list()."</div>
</td>
</tr>
</table>
";

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);

}

function main_disk_persopath_list(){
	
	$artica=new backup();
	if(!is_array($artica->perso_path)){
		return null;
	}
	
	$html="<table style='widht:100%'>";
	while (list ($num, $val) = each ($artica->perso_path) ){
	$html=$html ."<tr " . CellRollOver_jaune().">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td width=99%><code>$val</code>
		<td width=1%>" . imgtootltip('ed_delete.gif','{delete}',"DeletePersoPath('$num');")."</td>
		</tr>
	
	";
	}
	
	$html=$html . "</table>";
	$tpl=new templates();
	return RoundedLightGrey($tpl->_ENGINE_parse_body($html));
	
}

function selected_disk_list(){
	include_once("ressources/class.os.system.tools.inc");
	$backup=new backup();
	$os=new os_system();
	$sock=new sockets();
	$array=$backup->HdBackup;
	if(!is_array($array)){return null;}
	for($i=0;$i<count($array);$i++){
		if($array[$i]<>null){
			
			$array_usb=usbbyuuid($array[$i]);
			
			$table=$os->usb_parse_array($array_usb);
			$count_archives=trim($sock->getfile("DarCountFiles:{$array[$i]}"));
			if($count_archives>2){
				$view=imgtootltip('spotlight-18.png','{browse_dar}',"BrowseDareContener('{$array[$i]}')");
			}
			
		$dd=$dd."
					<tr " . CellRollOver().">
						<td width=1% valign='top'>
							<img src='img/usb-32.png' style='margin:3px'>
						</td>
						<td nowrap align=right width=90px valign='top'>
							<table style='width:100%'>
								<tr>
									<td>
										<table style='width:100%'>
											<tr>
												<td width=99%><strong style='font-size:12px'>{$array[$i]}</strong></td>
												<td width=1% align='right'>" . imgtootltip("ed_delete.gif","{delete}","DiskDelete('{$array[$i]}')")."</td>
											</tr>
											<tr>
												<td width=99%><strong>$count_archives {archives_files}</strong></td>
												<td width=1%>$view</td>
												
											</tr>
										</table>
									</td>
							</tr>
							<tr><td>$table</td>
							</tr>
							</table>
					</tr>";
		
		
	}}
	
	$tpl=new templates();
$html=RoundedLightGrey("<table style='width:100%'>$dd</table>");	
	return $tpl->_ENGINE_parse_body($html);
	
	
}

function main_disks_discover(){
	$users=new usersMenus();
	$Disks=$users->disks_size;
	if($Disks<>null){
		$tbl=explode(";",$Disks);
		while (list ($num, $val) = each ($tbl) ){
			if(trim($val)<>null){
				$values=explode(",",$val);
				if(is_array($values)){
					$dc=$dc+1;
					$disk=$values[0];
					$size=$values[1];
					$occ=$values[2];
					$disp=$values[3];
					$pourc=$values[4];
					if($pourc>90){$color="#D32D2D";}else{$color="#5DD13D";}
					$dd=$dd."
					<tr " . CellRollOver("SelectDisk('$disk');","{select_this_disk}").">
						<td nowrap align=right width=90px><strong>$disk:</strong></td>
						
						<td>
							<div style='width:100px;background-color:white;padding-left:0px;border:1px solid $color'>
								<div style='width:{$pourc}px;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:$color'>
									<strong>{$pourc}%</strong>
								</div>
							</div>
						</td>
						<td width=1% nowrap><strong>{$occ}G/{$size}G</strong></td>
						
					</tr>";
				}
			}
			
		}
	}
	


	$html=RoundedLightGrey("<table style='width:100%'>
	$dd
	</table>
	
	");
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
	
	
}


function usbbyuuid($uid){
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?usb-scan-write=yes");
if(!file_exists('ressources/usb.scan.inc')){
		return array("LABEL"=>$tpl->_ENGINE_parse_body("<H1>{error_no_socks}</H1>"));
		
	}	
	include("ressources/usb.scan.inc");	
	return $_GLOBAL["usb_list"][$uid];
}


function main_select_disk(){
	
	$disk="/dev/{$_GET["disk"]}";
	$regex_pattern="#\/dev\/{$_GET["disk"]}#";
	$sock=new sockets();
	$tpl=new templates();
	$sock->getFrameWork("cmd.php?usb-scan-write=yes");
	if(!file_exists('ressources/usb.scan.inc')){
		return $tpl->_ENGINE_parse_body("<H1>{error_no_socks}</H1>");
		
	}
	
	include_once("ressources/usb.scan.inc");
	include_once("ressources/class.os.system.tools.inc");
	//64-usb-disk-add.png
	
	//found the right usb device
	
	while (list ($num, $val) = each ($_GLOBAL["usb_list"]) ){
		if(preg_match($regex_pattern,$val["PATH"])){
			$uiid=$num;
			$main_array=$val;
			break;
		}
	}
	
	
	$os=new os_system();
	$infos=$os->usb_parse_array($main_array);
	$img=RoundedLightWhite("<center>".imgtootltip("64-usb-disk-add.png",'{add}',"disk_add('$uiid')")."</center>");
	$text="{add_has_backup}";
	
	
	$html="
	<input type='hidden' id='disk_add_caption' value='{disk_add_caption}'>
	<table style='width:100%'>
	<tr>
	<td valign='top' width=1%>$img<br><center><strong>$text</strong></center></td>
	<td valign='top' width=99%>$infos</td>
	</tr>
	</table>
	
	";
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
	
}


function add_usb_disk(){
	$backup=new backup();
	$backup->add_usb_backup($_GET["disk_usb_add"]);
	if(!$backup->SaveToLdap()){
		echo $backup->ldap_error;
	}else{
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body('{success}');
	}
	
}
function del_usb_disk(){
	$uid=$_GET["disk_usb_del"];
	$backup=new backup();
	$backup->del_usb_backup($uid);
$tpl=new templates();
		echo $tpl->_ENGINE_parse_body('{success}');	
}

function dar_status(){

$users=new usersMenus();
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}		
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString($sock->getfile('darstatus',$_GET["hostname"]));

	
	if($ini->_params["DAR"]["application_installed"]<>1){
		return RoundedLightYellow(Paragraphe('danger32.png','{dar_is_not_installed}','{dar_is_not_installed_text}',""));	
	}
	
	if($ini->_params["DAR"]["running"]==0){
		$img="ok32-grey.png";
		$status="{stopped}";
	}else{
		$img="ok32.png";
		$status="running";
		
	}
	
	
	$size=$ini->_params["DAR"]["master_memory"];
	$b="Ko";

	
	if($size>999){
		$size=round($size/1000,2);
		$b="Mo";
	}
	
	if($size>999){
		$size=round($size/1000,2);
		$b="Go";
	}	
	
	$status="<table style='width:100%'>
	<tr>
	<td valign='top'>
		<img src='img/$img'>
	</td>
	<td valign='top'>
		<table style='width:100%'>
		<tr>
		<td align='right' nowrap><strong>{APP_DAR}:</strong></td>
		<td><strong>$status</strong></td>
		</tr>		
</tr>		
		<tr>
		<td align='right'><strong>{pid}:</strong></td>
		<td><strong>{$ini->_params["DAR"]["master_pid"]}</strong></td>
		</tr>
		<tr>
		<td align='right'><strong>{memory}:</strong></td>
		<td nowrap><strong>$size</strong></td>
		</tr>
		<tr>
		<td align='right'><strong>{version}:</strong></td>
		<td><strong>{$ini->_params["DAR"]["master_version"]}</strong></td>
		</tr>	
		</table>		
	</td>
	</tr>
	</table>";
	
	$status=RoundedLightGreen($status);
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($status);
	
	
}

function dar_conteners(){
	$uid=$_GET["disk"];
	
	$find_form="
	<input type='hidden' id='uuid-disk' value='$uid'>
	<table style='width:100%'>
	<tr>
		<td nowrap align='right'>{search}:</td>
		<td align='left' width=99%'><input type='text' id='dar_query' value='' style='width:100%;font-size:11px;font-weight:bolder' onkeypress=\"javascript:dar_search(event);\"></td>
		<td width=1%><input type='button' value='go&nbsp;&raquo;' OnClick=\"javascript:dar_search();\"></td>
	</tr>
	</table>
	";
	$find_form=RoundedLightGreen($find_form);
	$html="<H5>{browse_dar}</H5>
	<p class=caption>{browse_dar_text}</p>
	$find_form
	<div id='dar_results'></div>
	
	
	";
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
}

function dar_search_files(){
	$pattern=$_GET["pattern"];
	$uuid=$_GET["disk"];
	$sock=new sockets();
	$sock->getfile("DarSearchFiles:$pattern;$uuid");
	if(file_exists("ressources/logs/dar.query.inc")){
		include_once("ressources/logs/dar.query.inc");
		$html="
		<input type='hidden' id='uuid' value='$uuid'>
		<table style='width:100%'>";
		while (list ($num, $val) = each ($_DAR_RES) ){
				$html=$html . "
				
				<tr>
					<td width=1%><img src='img/fw_bold.gif'>
					<td width=99% style='font-size:10px'><code>" . texttooltip($num,'{info}',"dar_file_info('$num')")."</code></td>
					
				</tr>";
			
		}
		
		$html=$html . "</table>";
		$html=RoundedLightGrey("<div style='width:100%;overflow-y:auto;height:200px'>$html</div>");
		
		
	}
	
	return $html;
}

function dar_file_info(){
	$file=$_GET["file"];
	$uuid=$_GET["uuid"];
	$sock=new sockets();
	$datas=explode("\n",$sock->getfile("DarFileInfo:$file;$uuid"));

	$html="<H5>$file</H5>
	<input type='hidden' id='restore_all_database_text' value='{restore_all_database_text}'>
	<table style='width:100%' style='badding:4px;border:1px dotted #CCCCCC'>
	<tr>
	<th>&nbsp;</th>
	<th nowrap>{database_number}</th>
	<th>{saved_date}</th>
	<th>&nbsp;</th>
	</tr>";
	
	if(is_array($datas)){
		while (list ($num, $val) = each ($datas) ){
			if(trim($val<>null)){
			$tb=explode(";",$val);
				$html=$html . "
				<tr>
					<td width=1%><img src='img/fw_bold.gif'></td>
					<td style='font-weight:bold'nowrap width=1% align='center'>
						" . imgtootltip('alias-18.gif','{restore_all_database}',"RestoreDatabase({$tb[0]},'$uuid')")."
					</td>
					<td nowrap width=99%>{$tb[1]}</td>
					<td width=1%>" . imgtootltip('alias-18.gif','{restore_single_file}',"RestoreSingleFile({$tb[0]},'$file','$uuid')")."</td>
				</tr>";
			}
		
		}
		
	}
	
	$html=$html . "</table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function add_Personalize_Folder(){
	$back=new backup();
	$back->add_personal_backup($_GET["AddPersonalizeFolder"]);
	
}

function del_Personalize_Folder(){
$back=new backup();
unset($back->perso_path[$_GET["DeletePersonalizeFolder"]]);
$back->SaveToLdap();	
}

function RestoreSingleFile(){
	$sock=new sockets();
	writelogs("DarRestoreFile:{$_GET["RestoreSingleFile"]}:{$_GET["database"]}:{$_GET["uuid"]}",__FUNCTION__,__FILE__);
	$datas=$sock->getfile("DarRestoreFile:{$_GET["RestoreSingleFile"]}:{$_GET["database"]}:{$_GET["uuid"]}");
	$d=explode("\n",$datas);
	while (list ($num, $val) = each ($d) ){
		if(trim($d)==null){continue;}
		$html=$html . "<div>".htmlentities($val)."</div>";
		
	}
	
	echo "<div style='width:100%;overflow-y:auto;height:300px'>$html</div>";
	
	}
	
function RestoreDatabase(){
	
	$sock=new sockets();
	$num=trim($sock->getfile("DarDatabaseCountFiles:{$_GET["RestoreDatabase"]};{$_GET["uuid"]}"));
	writelogs("DarRestoreFull:{$_GET["RestoreDatabase"]};{$_GET["uuid"]}",__FUNCTION__,__FILE__);
	$html="
	<input type='hidden' id='uuid' value='{$_GET["uuid"]}'>
	<input type='hidden' id='database' value='{$_GET["RestoreDatabase"]}'>
	<H5>{restore_full_database}</H5>
	<p class=caption>{restore_all_database_text}</p>".RoundedLightGrey("
	<table style='width:100%'>
	<tr>
		<td width=1% nowrap align='right'><strong>{usb_device_id}:</strong></td>
		<td width=99% align='left'><strong>{$_GET["uuid"]}</strong>
	</tr>
	<tr>
		<td width=1% nowrap align='right'><strong>{database_number}:</strong></td>
		<td width=99% align='left'><strong>{$_GET["RestoreDatabase"]}</strong>
	</tr>
	<tr>
		<td width=1% nowrap align='right'><strong>{database_files_number}:</strong></td>
		<td width=99% align='left'><strong>$num</strong>
	</tr>	
	</table>");
	
	
	$html=$html . "<br><H5>{target_restore_folder}</H5>".RoundedLightGreen("
	<table style='width:100%'>
	<td width=99%><input type='text' id='restore_path' value=''></td>
	<td width=1%>" . browserTree('restore_path')."</td>
	</tr>
	</table>");
	
	$html=$html . "<br><H5>{perform_restore}</H5>
	<div style='width:100%;text-align:right'><input type='button' value='{perform_restore}&nbsp;&raquo;' OnClick=\"javascript:PerformRestore();\"></div>
	";
		
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
	
}

function RestoreDatabase2(){
$sock=new sockets();
$datas=$sock->getfile("DarRestoreFull:{$_GET["database"]}:{$_GET["uuid"]}:{$_GET["target_folder"]}");
	$d=explode("\n",$datas);
	while (list ($num, $val) = each ($d) ){
		if(trim($d)==null){continue;}
		$html=$html . "<div>".htmlentities($val)."</div>";
		
	}
	echo "<div style='width:100%;overflow-y:auto;height:300px'>$html</div>";	
	
	
}


function main_switch_scripts(){
	switch ($_GET["jsscript"]) {
		case "addshare":main_share_retranslation_js_addshare();
			
			break;
	
		default:
			break;
	}
	
}


function main_share_retranslation_js_addshare(){

	$page=CurrentPageName();
	if(isset($_GET["index"])){
		$uri="&index={$_GET["index"]}";
	}
	
	
echo "	
YahooWin2(450,'$page?popup=addshare$uri','Remote folder...','');

var x_addshare= function (obj) {
			var results=obj.responseText;
			alert(results);
			YAHOO.example.container.dialog2.hide();
			LoadAjax('share_list','$page?main=share_list&hostname={$_GET["hostname"]}');
		}
		
		function addshare(){
			var XHR = new XHRConnection();
			XHR.appendData('addnewsharebackup','yes');
			if(document.getElementById('index')){
				XHR.appendData('index',document.getElementById('index').value);
			}
			XHR.appendData('servername',document.getElementById('servername').value);
			XHR.appendData('share_folder',document.getElementById('share_folder').value);
			XHR.appendData('username',document.getElementById('username').value);
			XHR.appendData('password',document.getElementById('password').value);
			XHR.appendData('domain',document.getElementById('domain').value);
			XHR.sendAndLoad('$page', 'GET',x_addshare);	
		}		
";

}

function main_share_retranslation_add_popup(){
	
	$bck=new backup();
	$line=explode(";",$bck->MountBackup[$_GET["index"]]);
	
	if(isset($_GET["index"])){
		$title="//{$line[0]}/{$line[1]}";
		$fieldindex="<input type='hidden' id='index' value='{$_GET["index"]}'>";
	}else{
		$title="{add_share_folder}";
	}
	$form="	
		<table style='width:100%'>
			<tr>
			<td class=legend nowrap>{server_name}:</td>
			<td>" . Field_text('servername',$line[0],'width:195px')."</td>
			</tr>
			<tr>
			<td class=legend nowrap>{share_folder}:</td>
			<td>" . Field_text('share_folder',$line[1],'width:195px')."</td>
			</tr>
			<tr>
			<td class=legend nowrap>{username}:</td>
			<td>" . Field_text('username',$line[2],'width:195px')."</td>
			</tr>
			<tr>
			<tr>
			<td class=legend nowrap>{domain}:</td>
			<td>" . Field_text('domain',$line[4],'width:195px')."</td>
			</tr>			
			<td class=legend nowrap>{password}:</td>
			<td>" . Field_password('password',$line[3],'width:195px')."</td>
			</tr>											

			<tr>
			<td colspan=2 align='right'><input type='button' OnClick=\"javascript:addshare();\" value='{add}&nbsp;&raquo;'></td>
			</tr>
		</table>			
		";
		
		$form=RoundedLightWhite($form);
		
$html="<H1>$title</H1>$fieldindex
			<p class=caption>{add_share_folder_text}</p>
				$form
			<br>
		";
		
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body($html);	
	
}

function main_share_retranslation_list(){
	$bck=new backup();
	
	
	
	if(isset($_GET["delete"])){
		unset($bck->MountBackup[$_GET["delete"]]);
		$bck->SaveToLdap();
		$bck=new backup();
		
	}
	if(!is_array($bck->MountBackup)){return null;}
	$html="<table style='width:100%'>";
	
	while (list ($num, $datas) = each ($bck->MountBackup)){
		
		if(trim($datas)==null){continue;}
		$ar=explode(";",$datas);
		$md=md5($datas);
		$span="<span id='$md'></span>";
		
		$html=$html . 
		
		"<tr ". CellRollOver().">
			<td width=1% valign='top'><img src='img/24-recipes-folder.png'></td>
			<td valign='top'>$span<strong style='font-size:13px'>" . texttooltip("\\\\{$ar[0]}\\{$ar[1]} ({$ar[2]})","{edit}","Loadjs('$page?jsscript=addshare&index=$num')")."</strong></td>
			<td width=1% valign='top'>" . imgtootltip('ed_delete.gif','{delete}',"DeleteRemoteShare($num)")."</td>
		</tr>";
		
	}
	
	$html=$html . "</table>";
	$tpl=new templates();
	return RoundedLightWhite($tpl->_ENGINE_parse_body($html));
	
}

function main_share_retranslation_save(){
	$bck=new backup();
	if(isset($_GET["index"])){
		$bck->MountBackup[$_GET["index"]]="{$_GET["servername"]};{$_GET["share_folder"]};{$_GET["username"]};{$_GET["password"]};{$_GET["domain"]}";
	}else{
		$bck->MountBackup[]="{$_GET["servername"]};{$_GET["share_folder"]};{$_GET["username"]};{$_GET["password"]};{$_GET["domain"]}";
	}
	if($bck->SaveToLdap()){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body('Share:{success}');
	}else{
		echo $bck->ldap_error;
	}
}


function main_share_retranslation(){
$page=CurrentPageName();	
$html=main_tabs()."

<H5>{share_retranslation}</h5>
<table style='width:100%'>
<tr>
<td valign='top' width=70%>
<p class=caption>{share_retranslation_text}</p>
<div style='text-align:right'><input type='button' OnCLick=\"javascript:Loadjs('$page?jsscript=addshare')\" value='{add_share_folder}&nbsp;&raquo;'></div>
<br>
" . RoundedLightGrey("<H3>{shares_list}</H3><hr><div id='share_list'>" . main_share_retranslation_list()."</div>")."
</td>
<td valign='top' width=30%>
<div id='dar_status'>". dar_status()."</div>" . main_share_retranslation_issmbmount()."<br>
<div id='networks_shares'>" . main_share_retranslation_list_shares()."</div>

</td>
</table>
<table style='width:100%'>
</table>";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
}

function main_share_retranslation_issmbmount(){
	$users=new usersMenus();
	if(!$users->smbmount_installed){
		$html="<H3>{error_smbmount_not_installed}</H3>
		<p class=caption>{error_need_smbmount_feature}</p>
		";
		$tpl=new templates();
		return "<br>".RoundedLightYellow($tpl->_ENGINE_parse_body($html));
		
	}
	
}


function main_share_retranslation_list_shares(){
	$bck=new backup();
	$list=$bck->MountBackup;
	if(!is_array($list)){return null;}
	$sock=new sockets();
	
	
	if($_GET["connect"]<>null){
		$sock->getfile("BackupShareConnect:{$_GET["connect"]}");
	}
	
	if($_GET["disconnect"]<>null){
		$sock->getfile("BackupShareDisconnect:{$_GET["disconnect"]}");
	}
	
$html="<table style='width:100%'>";
	
	while (list ($num, $datas) = each ($list)){
		if(trim($datas)==null){continue;}
		$ar=explode(";",$datas);
		$server=$ar[0];
		$share=$ar[1];
		$account=$ar[2];
		$password=$ar[3];
        $domain=$ar[4];
		$uuid=md5($server.$share);
		$md=md5($datas);
		$color="#5DD13D";
		$img="32-samba-pdc.png";
		$count_archives=trim($sock->getfile("DarCountFiles:$uuid"));
			if($count_archives>2){
				$view=imgtootltip('spotlight-18.png','{browse_dar}',"BrowseDareContener('$uuid')");
			}
			
		$diconnect=imgtootltip('ed_delete.gif','{disconnect}',"NetUmount('$uuid')");	
		$mount_path="/opt/artica/$uuid";
		$dev_source='//'.$server . '/' . $share;
		
		$mounted=trim($sock->getfile("ismounted:$dev_source;$mount_path"));
		$mount=imgtootltip('24-connect.png','{connect_share}',"ConnectShare('$num')");
		
		if($mounted=='FALSE'){$diconnect=$mount;$view=null;$img="32-samba-pdc-red.png";}
		
		$html=$html."
					<tr " . CellRollOver().">
						<td width=1% valign='top'>
							<img src='img/$img' style='margin:3px'>
						</td>
						<td nowrap align=right width=90px valign='top'>
							<table style='width:100%'>
								<tr>
									<td>
										<table style='width:100%'>
											<tr>
												<td width=99%><strong style='font-size:12px'>$server</strong></td>
												<td width=1% align='right'>$diconnect</td>
											</tr>
											<tr>
												<td width=99%><strong style='font-size:12px'>$share</strong></td>
												<td width=1% align='right'></td>
											</tr>											
											<tr>
												<td width=99%><strong>$count_archives {archives_files}</strong></td>
												<td width=1%>$view</td>
												
											</tr>
										</table>
									</td>
								</tr>
							</table>
					</tr>
					<tr><td colspan=2><hr></td></tr>
					";
		
		
		
	
		
	}
	
	$html="$html</table>";
	$tpl=new templates();
	return RoundedLightGrey($tpl->_ENGINE_parse_body($html));	
	
}



?>
