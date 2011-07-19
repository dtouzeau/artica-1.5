<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.autofs.inc');
	include_once('ressources/class.computers.inc');

	
	$user=new usersMenus();
	if($user->AsMailBoxAdministrator==false){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		die();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["cyrus-ressource"])){add_ressource();exit;}
	if(isset($_GET["res-list"])){echo list_ressource();exit;}
	if(isset($_GET["backup-config"])){echo backup_config();exit;}
	if(isset($_GET["backup-save-config"])){backup_save_config();exit;}
	if(isset($_GET["cyrus-delete"])){backup_delete();exit;}
	if(isset($_GET["backup-perform-now"])){backup_now();exit;}
	

js();
function js(){
$page=CurrentPageName();
	$prefix=str_replace('.','_',$page);
	$prefix=str_replace('-','',$prefix);
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{APP_CYRUS_BACKUP}');
	$settings=$tpl->_ENGINE_parse_body('{settings}');
	$CYR_BACKUP_NOW=$tpl->_ENGINE_parse_body('{CYR_BACKUP_NOW}');
	$load="{$prefix}LoadPage();";
	
	if(isset($_GET["add-automount"])){$load="CyrusBackupAddResourceFormWebPages()";}
	
$html="
	var {$prefix}timeout=0;
	var {$prefix}timerID  = null;
	var {$prefix}tant=0;
	var {$prefix}reste=0;	

	var x_CyrusBackupAddResourceFormWebPages= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			{$prefix}LoadPage();
			}	
	
	function CyrusBackupAddResourceFormWebPages(){
		var XHR = new XHRConnection();
		var default_schedule='0 0,3,12,19 * * *';
    	XHR.appendData('cyrus-ressource','{$_GET["add-automount"]}');
    	XHR.appendData('cyrus-schedule',default_schedule);
    	XHR.sendAndLoad('$page', 'GET',x_CyrusBackupAddResourceFormWebPages);
	}
	
	function CyrusBackupNow(HD){
		alert('$CYR_BACKUP_NOW');
		var XHR = new XHRConnection();
		XHR.appendData('backup-perform-now',HD);
		XHR.sendAndLoad('$page','GET');
	}

	function {$prefix}LoadPage(){
		YahooLogWatcher(700,'$page?popup=yes','$title');
	}
	
	function CyrusBackupOptions(HD){
		RTMMail(550,'$page?backup-config='+HD,'$settings');
	}	
	
	
		var x_SaveBackupCyrusSettings= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			RTMMailHide();
			}		
	
	function SaveBackupCyrusSettings(HD){
		var XHR = new XHRConnection();
		XHR.appendData('backup-save-config',HD);
    	XHR.appendData('BACKUP_MAILBOXES',document.getElementById('BACKUP_MAILBOXES').value);
    	XHR.appendData('BACKUP_DATABASES',document.getElementById('BACKUP_DATABASES').value);
    	XHR.appendData('BACKUP_ARTICA',document.getElementById('BACKUP_ARTICA').value);
    	XHR.appendData('CONTAINER',document.getElementById('CONTAINER').value);
    	XHR.appendData('MAX_CONTAINERS',document.getElementById('MAX_CONTAINERS').value);
 		document.getElementById('CyrBackDiv').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
    	XHR.sendAndLoad('$page', 'GET',x_SaveBackupCyrusSettings);
	}
	
	     

	
	
var x_AddCyrusBackupResource=function (obj) {
	LoadAjax('cyrus-list-res','$page?res-list=yes');
	}	
	
	function AddCyrusBackupResource(){
    	var XHR = new XHRConnection();
    	XHR.appendData('cyrus-ressource',document.getElementById('cyrus-ressource').value);
 		document.getElementById('cyrus-list-res').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
    	XHR.sendAndLoad('$page', 'GET',x_AddCyrusBackupResource);
	}
	
	function CyrusBackupDelete(mount){
	var XHR = new XHRConnection();
    	XHR.appendData('cyrus-delete',mount);
 		document.getElementById('cyrus-list-res').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
    	XHR.sendAndLoad('$page', 'GET',x_AddCyrusBackupResource);
	}
	
	function SetCyrusBackupSchedule(res){
		var XHR = new XHRConnection();
    	XHR.appendData('cyrus-ressource',res);
    	XHR.appendData('cyrus-schedule',document.getElementById(res+'_SCHEDULE').value);
 		document.getElementById('cyrus-list-res').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
    	XHR.sendAndLoad('$page', 'GET',x_AddCyrusBackupResource);
	}
	

	$load";

	echo $html;
	}
	
function backup_save_config(){

	$HD=$_GET["backup-save-config"];
	$cyr=new cyrusbackup();
	
	while (list ($key, $value) = each ($_GET) ){
		$cyr->list[$HD][$key]=$value;	
	}
	$cyr->save();
}
	
	
function popup(){

	$add=Paragraphe('disk-backup-64-add.png','{CYRUS_ADD_RESOURCES}','{CYRUS_ADD_RESOURCES_TEXT}',"javascript:Loadjs('automount.php?field=cyrus-ressource');");
	$resources=list_ressource();
	$html="<H1>{GENERIC_BACKUP}</H1>
	<p class=caption>{GENERIC_BACKUP_TEXT}</p>
		<table style='width:100%'>
		<tr>
			<td valign='top' width=1%>$add</td>
		<td valign='top'>
		<table style='width:100%' class=table_form>
		<tr>
			<td valign='middle' class=legend nowrap>{resource}:</td>
			<td>". Field_text('cyrus-ressource',null,'width:240px')."</td>
			<td width=1%><input type='button' OnClick=\"javascript:AddCyrusBackupResource();\" value='{add}&nbsp&raquo;'>
		</tr>
		</table>
		<br>
		
		". RoundedLightWhite("<div id='cyrus-list-res'>$resources</div>")."
		
	</td>
	</tr>
	</table>";
		
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	}
	
function add_ressource(){
	$cyr=new cyrusbackup();
	if(trim($_GET["cyrus-ressource"])==null){return null;}
	$cyr->list[$_GET["cyrus-ressource"]]["enabled"]='yes';
	$cyr->list[$_GET["cyrus-ressource"]]["schedule"]=$_GET["cyrus-schedule"];
	
	
	$cyr->save();
}

function list_ressource(){
	$tpl=new templates();
	$cyr=new cyrusbackup();
	if(!is_array($cyr->list)){return  $tpl->_ENGINE_parse_body("<strong style='font-size:13px'>{error_no_datas}</strong>");}
	
	$html="<table style='width:99%'>
	<tr>
	<th>&nbsp;</th>
	<th>{mount}</th>
	<th>{schedule}</th>
	<th>&nbsp;</th>
	<th>&nbsp;</th>
	<th>&nbsp;</th>
	<th>&nbsp;</th>
	</tr>";
	reset($cyr->list);
	
	//javascript:Loadjs('cron.php?function=SaveSchedule&field=schedule')
	
	while (list ($mount, $array) = each ($cyr->list) ){
		if(trim($mount)==null){continue;}
		$html=$html . "<tr ". CellRollOver().">
		<td valign='middle' width=1%'><img src='img/fw_bold.gif'></td>
		<td valign='middle'>
			<strong style='font-size:12px'><code>$mount</code></strong>
		</td>
		<td valign='middle'>
			<strong style='font-size:10px'>". Field_text("{$mount}_SCHEDULE",$array["schedule"])."</strong>
		</td>	
			<td valign='top' width=1%'>". imgtootltip('time-30.png',"{schedule}","Loadjs('cron.php?field={$mount}_SCHEDULE')")."</td>
			<td valign='top' width=1%'>". imgtootltip('rouage2.png',"{settings}","CyrusBackupOptions('$mount')")."</td>
			<td valign='middle' width=1%'><input type='button' OnClick=\"javascript:SetCyrusBackupSchedule('$mount')\" value='{edit}'></td>
			<td valign='middle' width=1%'>". imgtootltip('30-cancel.png','{delete}',"CyrusBackupDelete('$mount')")."</td>			
		</tr>
		<tr>
			<td colspan=7><hr></td>
		</tr>";	
		
	}
	
	$html=$html."</table>";
	
	
	return $tpl->_ENGINE_parse_body($html);	
	
	
}

function backup_config(){
	
	$cyrusbackup=new cyrusbackup();
	$datas=$cyrusbackup->list[$_GET["backup-config"]];
	
	if($datas["CONTAINER"]==null){$datas["CONTAINER"]="D";}
	if($datas["MAX_CONTAINERS"]==null){$datas["MAX_CONTAINERS"]="3";}
	if($datas["BACKUP_MAILBOXES"]==null){$datas["BACKUP_MAILBOXES"]="1";}
	if($datas["BACKUP_DATABASES"]==null){$datas["BACKUP_DATABASES"]="1";}
	if($datas["BACKUP_ARTICA"]==null){$datas["BACKUP_ARTICA"]="1";}
	if($datas["STOP_SERVICES"]==null){$datas["STOP_SERVICES"]="1";}
	
	$backup_container=array("D"=>"{day}","W"=>"{week}");
	$backup_container=Field_array_Hash($backup_container,"CONTAINER",$datas["CONTAINER"]);
	
	for($i=1;$i<20;$i++){
		$MaxContainer[$i]=$i;
	}
	
	$MaxContainer=Field_array_Hash($MaxContainer,"MAX_CONTAINERS",$datas["MAX_CONTAINERS"]);
	
	$BACKUP_MAILBOXES=Field_numeric_checkbox_img('BACKUP_MAILBOXES',$datas["BACKUP_MAILBOXES"],'{mailboxes_backup_explain}');
	$BACKUP_DATABASES=Field_numeric_checkbox_img('BACKUP_DATABASES',$datas["BACKUP_DATABASES"],'{databases_backup_explain}');
	$BACKUP_ARTICA=Field_numeric_checkbox_img('BACKUP_ARTICA',$datas["BACKUP_ARTICA"],'{artica_confs_explain}');
	$STOP_SERVICES=Field_numeric_checkbox_img('STOP_SERVICES',$datas["STOP_SERVICES"],'{STOP_SERVICES_BACKUP_EXPLAIN}');
	
	$perform_backup=Paragraphe("64-recycle.png","{backup_now}","{backup_now_text}","javascript:CyrusBackupNow('{$_GET["backup-config"]}');");
	
	
	$form="<table style='width:100%'>
		<tr>
			<td valign='top' class=legend>{backup_container}:</td>
			<td valign='top'>$backup_container</td>
		</tr>
		<tr>
			<td valign='top' class=legend>{max_backup_container}:</td>
			<td valign='top'>$MaxContainer</td>
		</tr>
		<tr>
			<td valign='top' class=legend>{stop_services}:</td>
			<td valign='top'>$STOP_SERVICES</td>
		</tr>		
		<tr>
			<td colspan=2><hr><H3>{what_to_backup}</H3></td></tr>
		<tr>
			<td valign='top' class=legend>{mailboxes}:</td>
			<td valign='top'>$BACKUP_MAILBOXES</td>
		</tr>	
		<tr>
			<td valign='top' class=legend>{databases}:</td>
			<td valign='top'>$BACKUP_DATABASES</td>
		</tr>	
		<tr>
			<td valign='top' class=legend>{artica_confs}:</td>
			<td valign='top'>$BACKUP_ARTICA</td>
		</tr>	
		<tr>
			<td colspan=2 align=right>
				<hr>
			<input type='button' OnClick=\"javascript:SaveBackupCyrusSettings('{$_GET["backup-config"]}');\" value='{edit}&nbsp;&raquo;'>
			</td>
		</tr>							
		</table>
	";
	
	$form=RoundedLightWhite($form);
	$html="<H1>{$_GET["backup-config"]}::{settings}</H1>
	<table style='width:100%'>
	<tr>
		<td valign='top'>$perform_backup</td>
		<td valign='top'>
			<div id='CyrBackDiv'>$form</div>
		</td>
	</tr>
	</table>
	
	
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html,'cyrus.index.php,artica.backup.index.php,dar.index.php');
	}
	
function backup_now(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?cyrus-backup-now={$_GET["backup-perform-now"]}");
	
}

function backup_delete(){
	$cyr=new cyrusbackup();
	unset($cyr->list[$_GET["cyrus-delete"]]);
	$cyr->save();
}




class cyrusbackup{
	var $list;
	
	function cyrusbackup(){
		$this->load();
		
	}
	
	private function load(){
		$sock=new sockets();
		$ini=new Bs_IniHandler();
		$datas=$sock->GET_INFO('CyrusBackupRessource');
		$ini->loadString($datas);
		$this->list=$ini->_params;
	}
	//artica-backup --single-cyrus mount
	public function save(){
		$ini=new Bs_IniHandler();
		$ini->_params=$this->list;
		$sock=new sockets();
		$sock->SaveConfigFile($ini->toString(),'CyrusBackupRessource');
		$sock->getFrameWork('RestartDaemon');
		
	}
	
	
}
	
?>