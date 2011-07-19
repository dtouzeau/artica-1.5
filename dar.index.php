<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.os.system.inc');
	include_once('ressources/class.user.inc');
	include_once('ressources/class.backup.inc');
	
	
	

	$user=new usersMenus();
	if($user->AsAnAdministratorGeneric==false){die('alert("no privileges")');}

if($_GET["dar-index-tab"]=="dar"){echo index_dar();exit;}	
if($_GET["dar-index-tab"]=="rsync"){echo index_rsync();exit;}
if(isset($_GET["perform-run-backup"])){run_backup_perform();exit;}

	
if(isset($_GET["js"])){echo js();exit;}
if(isset($_GET["js-logs"])){echo js();exit;}
if(isset($_GET["index-page"])){echo index();exit;}
if(isset($_GET["dar-settings"])){echo global_settings();exit;}
if(isset($_GET["dar-target"])){echo WhatToBackup();exit;}
if(isset($_GET["dar-params"])){echo dar_settings();exit;}

if(isset($_GET["exclude-types"])){echo dar_exclude_types();exit;}
if(isset($_GET["hsched"])){echo SaveBackupSettings();exit;}
if(isset($_GET["dar_file"])){echo SaveGeneralSettings();exit;}


if(isset($_GET["shares_folders"])){echo SaveBackupSettings();exit;}
if(isset($_GET["exclude-shared-folders"])){echo samba_excludes_shared();exit;}
if(isset($_GET["exclude-homes-folders"])){echo homes_excludes();exit;}

if(isset($_GET["ExcludeSMBFolder"])){samba_excludes_shared_save();exit;}
if(isset($_GET["ExcludeHomesFolder"])){homes_excludes_save();exit;}


if(isset($_GET["minimal_compress"])){echo SaveBackupSettings();exit;}
if(isset($_GET["exclude-type-list"])){echo dar_exclude_types_list();exit;}
if(isset($_GET["AddExcludeDarFileType"])){dar_exclude_types_add();exit;}

if(isset($_GET["user-defined-folders"])){echo user_defined();exit;}
if(isset($_GET["user-defined-schedule"])){echo user_defined_schedule();exit;}
if(isset($_GET["user-defined-schedule-save"])){user_defined_schedule_save();exit;}

if(isset($_GET["AddDarPersoFolder"])){user_defined_save();exit;}
if(isset($_GET["DelDarPersoFolder"])){user_defined_del();exit;}

if(isset($_GET["external-storage"])){external_storage();exit;}
if(isset($_GET["net_storage_server"])){external_storage_net_save();exit;}

if(isset($_GET["external-storage-usb"])){external_storage_usb();exit;}
if(isset($_GET["external-storage-usb-list"])){echo external_storage_usb_list();exit;}
if(isset($_GET["ExternalUsbSelect"])){external_storage_usb_save();exit;}
if(isset($_GET["external-storage-list"])){external_storage_list();exit;}
if(isset($_GET["ExternalStorageDelete"])){external_storage_delete();exit;}

if(isset($_GET["external-storage-network"])){external_storage_net();exit;}
if(isset($_GET["refresh-dar-status"])){echo dar_status();exit;}
if(isset($_GET["RebuildCollection"])){dar_rebuild();exit;}

if(isset($_GET["events"])){events();exit;}


if(isset($_GET["dar-view"])){dar_view_index();exit;}
if(isset($_GET["mount-dar"])){js_dar_mount();exit;}
if(isset($_GET["mount-dar-list"])){dar_mount_select();exit;}
if(isset($_GET["mount-dar-2"])){dar_mount_2();exit;}
if(isset($_GET["mount-dar-collection"])){dar_mount_collection();exit;}

if(isset($_GET["dar-query"])){dar_query();exit;}
if(isset($_GET["DarQuery"])){dar_query_results();exit;}

if(isset($_GET["dar-list"])){dar_list();exit;}
if(isset($_GET["dar-list-index"])){dar_list_type();exit;}
if(isset($_GET["dar-list-query"])){dar_list_query();exit;}


if(isset($_GET["dar-browse"])){dar_browse();exit;}

if(isset($_GET["schedule"])){schedule();exit;}
if(isset($_GET["RefreshCache"])){RefreshCache();exit;}
if(isset($_GET["perform-backup"])){run_backup_js();exit;}

if(isset($_GET["restore-full"])){retore_full_mode();exit;}
if(isset($_GET["restore-single-file"])){restore_single_file();exit;}
if(isset($_GET["original_path"])){restore_full_mode_exec();exit;}
if(isset($_GET["original_file_path"])){restore_single_mode_exec();exit;}
if(isset($_GET["populate"])){populate();exit;}


function dar_mount_1(){
	$html="<div id='mounted_logs_1'></div>";
	echo $html;
	}
	
	
function run_backup_js(){
	
	$tpl=new templates();
	$page=CurrentPageName();
	$confirm=$tpl->_ENGINE_parse_body('{error_want_operation}');
	
	$html="
	
		var x_PerFormRunBackup=function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>0){alert(tempvalue);}
		}			

	function PerFormRunBackup(){
		var XHR = new XHRConnection();
		XHR.appendData('perform-run-backup','yes');
		XHR.sendAndLoad('$page', 'GET',x_PerFormRunBackup);	
	}	
	
	
	function dar_run_backup(){
			if(confirm('$confirm')){
			PerFormRunBackup();
			}
	}
		
		
	
	dar_run_backup();";
	
	echo $html;
}

function run_backup_perform(){
	$sock=new sockets();
	$sock->getfile('dar-run-backup');
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{run_backup_performed}');
	
}
	
	
function dar_collection_root(){
	if($_SESSION["dar_collection_root"]<>null){return $_SESSION["dar_collection_root"];}
	if($_COOKIE["dar_collection_root"]<>null){
		$_SESSION["dar_collection_root"]=$_COOKIE["dar_collection_root"];
		return $_COOKIE["dar_collection_root"];}
	
}

function dar_mount_collection(){
	$md=$_GET["mount-dar-collection"];
	$_SESSION["dar_collection_root"]=$md;
	$_COOKIE["dar_collection_root"]=$md;
	
}
	
	
function dar_mount_select(){
	$dar=new dar_back();

	$dar_collection_root=dar_collection_root();
	$html="<H1>{select_your_storage}</H1>
	<p class=caption>{select_your_storage_text}</p>
	<div style='width:100%;height:300px;overflow:auto;' id='dar_mount_select'>
	<center>
	<table style='width:50%' class=table_form>
	";
	if(is_array($dar->external_storage)){
	while (list ($num, $line) = each ($dar->external_storage) ){
		if(preg_match('#smb:.+?@(.+?):(.+)#',$line,$re)){
			$img="48-samba.png";
			$index=$num;
			if($index==$dar_collection_root){$img="48-samba-red.png";}
			$server=$re[1];
			$folder=$re[2];
			
		}
		
	if(preg_match('#usb:(.+)#',$line,$re)){
			$img="usb-48-green.png";
			$index=$num;
			$server=$re[1];
			if($index==$dar_collection_root){$img="usb-48-red2.png";}
			$folder="USB";
			
		}
		
		if(preg_match('#file:(.+)#',$line,$re)){
			$img="hd-48.png";
			$index=$num;
			$server=$re[1];
			if($index==$dar_collection_root){$img="hd-48-red.png";}
			$folder="Folder";
			
		}		

		

	$js="MountCollectionRoot('$num')";

	$html=$html . "
		<tr " . CellRollOver($js,'{mount_this_collection}').">
			<td width=1%><img src='img/$img'></td>
			<td style='font-size:12px'>$server</td>
			<td style='font-size:12px'>$folder</td>
			
		</tr>
	
	";		
	}
}
	
$html=$html . "</table></div>";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
	
	
}
	

	
function dar_mount_2(){
	$dar=new dar_back();
	$sock=new sockets();
	$datas=trim($sock->getfile('darmount'));
	$tpl=new templates();
	if(trim($datas)==null){echo $tpl->_ENGINE_parse_body('{failed}');exit;}
	echo $tpl->_ENGINE_parse_body("<div style='background-color:#FFF755;padding:5px;margin:5px;border:1px solid white;'><strong>$datas</strong></div>");
	}
	
	
function restore_single_file(){
$dar_collection_root=dar_collection_root();
$database=$_GET["database"];
$filepath=$_GET["filepath"];
$filename=$_GET["restore-single-file"];
$ini=new Bs_IniHandler("ressources/dar_collection/$dar_collection_root/user_defined.conf");
$fullpath=stripslashes($ini->_params[$database]["TargetFolder"]);
$fullpath_label="$fullpath/$filepath";
if(strlen($filename)>36){
	$filename_label=substr($filename,0,33).'...';
	}
	else{$filename_label=$filename;
	}
$fullpath_label=str_replace("'","`",$fullpath_label);
if(strlen($fullpath_label)>47){$fullpath_label=texttooltip(substr($fullpath_label,0,44).'...',$fullpath_label);}


$page=CurrentPageName();
	$html="
	<H1>{restore}:$filename_label</H1>
	<form name='FFMRF'>
	<table style='width:100%' class=table_form>
	<tr>
		<td class=legend>{restore_from_original_path}:</td>
		<td>" . Field_checkbox('restore_from_original_path',1,1)."&nbsp;</td>
	</tr>
	<tr>
		<td colspan=2 class=legend nowrap>{original_path}:&nbsp;<strong>$fullpath_label</strong>
			<input type='hidden' name='original_file_path' id='original_file_path' value=\"$filepath\">
			<input type='hidden' name='original_filename' id='original_filename' value=\"$filename\">
			<input type='hidden' name='original_root_path' id='original_root_path' value=\"$fullpath\">
			<input type='hidden' name='database' id='database' value='$database'>
		</td>
	</tr>
<tr>
		<td colspan=2 class=legend><hr>	</td>
	</tr>	
	<tr>
		<td class=legend>{restore_from_defined_path}:</td>
		<td>". Field_text('defined_path',null,'width:190px')."</td>
	</tr>	
	<tr>
		<td colspan=2 class=legend><hr>	</td>
	</tr>
	<tr>
		<td colspan=2 align='right'><input type='button' OnClick=\"javascript:ParseForm('FFMRF','$page',true);\" value='{restore}'></td>
	</tr>
	</table>
	</form>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function restore_single_mode_exec(){
	$filename=$_GET["original_filename"];
	$original_file_path=$_GET["original_file_path"];
	$database=$_GET["database"];
	$fullpath=$_GET["original_root_path"];
	$restore_from_original_path=$_GET["restore_from_original_path"];
	$defined_path=$_GET["defined_path"];
	
	if($restore_from_original_path<>1){
		$fullpath=$defined_path;
		if($fullpath==null){die("Error ! defined path is null");}
	}
	$fullpath=stripcslashes($fullpath);
	$pathToRestore="$original_file_path/$filename";
	$dar_collection_root=dar_collection_root();
	
	$sock=new sockets();
	$sock->getfile("DarRestoreFile:$pathToRestore;$database;$dar_collection_root;$fullpath");
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("{restoring_explain_operation}\n$pathToRestore\n$fullpath");
}
	
function retore_full_mode(){
	$dar_collection_root=dar_collection_root();
	$original_path=$_GET["restore-full"];
	$database=$_GET["database"];
	$page=CurrentPageName();
	$html="
	<H1>{restore}:{$_GET["date"]}</H1>
	<form name='FFMR'>
	<table style='width:100%' class=table_form>
	<tr>
		<td class=legend>{restore_from_original_path}:</td>
		<td>" . Field_checkbox('restore_from_original_path',1,1)."&nbsp;</td>
	</tr>
	<tr>
		<td colspan=2 class=legend>{original_path}:&nbsp;<strong>$original_path</strong>
			<input type='hidden' name='original_path' id='original_path' value=\"$original_path\">
			<input type='hidden' name='database' id='database' value='$database'>
		</td>
	</tr>
<tr>
		<td colspan=2 class=legend><hr>	</td>
	</tr>	
	<tr>
		<td class=legend>{restore_from_defined_path}:</td>
		<td>". Field_text('defined_path',null,'width:190px')."</td>
	</tr>	
	<tr>
		<td colspan=2 class=legend><hr>	</td>
	</tr>
	<tr>
		<td colspan=2 align='right'><input type='button' OnClick=\"javascript:ParseForm('FFMR','$page',true);\" value='{restore}'></td>
	</tr>
	</table>
	</form>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function restore_full_mode_exec(){
	$_GET["original_path"]=stripcslashes($_GET["original_path"]);
	$_GET["defined_path"]=stripcslashes($_GET["defined_path"]);
	$dar_collection_root=dar_collection_root();
	$tpl=new templates();
	if($_GET["restore_from_original_path"]==1){
		$pathToRestore=$_GET["original_path"];
	}else{
		$pathToRestore=$_GET["defined_path"];
	}
	
	if($pathToRestore==null){
		echo $tpl->_ENGINE_parse_body("{error_path_cannot_be_null}");
	}
	writelogs("pathToRestore=\"$pathToRestore\"",__FUNCTION__,__FILE__);
	$database=$_GET["database"];
	
	$sock=new sockets();
	$sock->getfile("DarRestorePath:$pathToRestore;$database;$dar_collection_root");
	echo $tpl->_ENGINE_parse_body("{restoring_explain_operation}");
	
}


function js_dar_mount(){
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{connecting}...');
	$page=CurrentPageName();	
	$html="
	function mount_dar_step_1(){
		YahooWin3(400,'$page?mount-dar-list=yes','$title');
		
	}
	
	var x_MountCollectionRoot=function (obj) {
		var tempvalue=obj.responseText;
		mount_dar_step_1();
		}			

	function MountCollectionRoot(md){
		var XHR = new XHRConnection();
		XHR.appendData('mount-dar-collection',md);
		document.getElementById('dar_mount_select').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_MountCollectionRoot);	
	}	

	function mount_dar_step_2(){
		LoadAjax('mounted_logs_1','$page?mount-dar-2=yes');
	
	}
	mount_dar_step_1();
	";
	
	echo $html;
}

function RefreshCache(){
	$tpl=new templates();
	
	while (list ($num, $line) = each ($_SESSION)){
		if(preg_match('#^DAR_ARRAY(.+)#',$num)){
			writelogs("Delete cache $num",__FUNCTION__,__FILE__);
			unset($_SESSION[$num]);
		}
		
	}
	
	
	echo $tpl->_ENGINE_parse_body('{refresh_cache}: {success}');
	
	}

//grep -h -m 50 -i -E --regexp="name=\"logs\.pas.+" /usr/share/artica-postfix/ressources/dar_collection/7a6a9344a383c38cbce4686e38f07afd/*
function dar_query(){
$page=CurrentPageName();	
$tpl=new templates();	
$dar_collection_root=dar_collection_root();

if($dar_collection_root==null){
	$html="<H1>{query_collection}</H1>
	<center>
	<p style='font-size:12px;color:red;font-weight:bold'>{you_need_to_mount_collection_first}</p>
	</center>";
	echo $tpl->_ENGINE_parse_body($html);
	return null;
}
$html="<H1>{query_collection}</H1>
<p class=caption>{query_collection_text}</p>
	<table style='width:100%' class=table_form>
	<tr>
		<td class=legend>{query}:</td>
		<td >" . Field_text('qstring',null,'width:220px')."</td>
		<td ><input type='button' OnClick=\"javascript:DarQuery();\" value='{search}&nbsp;&raquo'></td>
	</tr>
	</table>
	<div id='query_results' style='width:100%;height:250px;overflow:auto'>
	</div>
	
	";
		
	echo $tpl->_ENGINE_parse_body($html);
}

function dar_query_results(){
	$collection=dar_collection_root();
	$sock=new sockets();
	$_GET["DarQuery"]=str_replace('.','\.',$_GET["DarQuery"]);
	$_GET["DarQuery"]=str_replace('*','.+?',$_GET["DarQuery"]);
	$datas=$sock->getfile('DarSearchFile:'.$_GET["DarQuery"].";$collection");
	$tp=explode("\n",$datas);
	$html="<table style='width:99%' class=table_form>
	<tr>
			<th></th>
			<th nowrap>{file}</th>
			<th nowrap>{source_path}</th>
			<th nowrap>{size}</th>
			<th nowrap>{date}</th>
			
	</tr>
	";
	
	$pathquery=dirname(__FILE__)."/ressources/dar_collection/$collection/";
	$ini=new Bs_IniHandler($pathquery."/user_defined.conf");
	$css="style='font-size:11px;font-weight:bold'";
	while (list ($num, $line) = each ($tp)){	
			if(trim($line)==null){continue;}
			$line=str_replace($pathquery,'',$line);
			if($db["$line"]){continue;}
			$dbl[$line]=true;
				if(preg_match('#(.+?)\.xml:.+?name="(.+?)"\s+size="(.*?)"#',$line,$re)){
					$db=$re[1];
					$db_file=$re[1];
					$name=$re[2];
					$size=$re[3];
					if(preg_match('#(.+?)-([0-9]+)-([0-9]+)-([0-9]+)-([0-9]+)-([0-9]+)-diff#',$db,$ri)){
						$db_file=$ri[1];
						$date="{$ri[2]}-{$ri[3]}-{$ri[4]} {$ri[5]}:{$ri[6]}";
					}
					if(trim($size)<>null){$size=FormatBytes($size/1024);}
					$db_path=$ini->_params[$db_file]["TargetFolder"];
	
		$table=$table."
		<tr ". CellRollOver().">
			<td width=1% align='center' $css nowrap>". imgtootltip('spotlight-18.png','{browse}',"BrowseCollection('$db')")."</td>
			<td width=1% align='center' $css nowrap>". texttooltip("$name",'{browse}',"BrowseCollection('$db');")."</strong></td>
			<td width=82% align='left' $css nowrap>". texttooltip($db_path,'{browse}',"BrowseCollection('$db');")."</strong></td>
			<td width=1% align='center' $css nowrap>". texttooltip($size,'{browse}',"javascript:BrowseCollection('$db');")."</strong></td>
			<td width=10% align='center' $css nowrap>". texttooltip($date,'{browse}',"javascript:BrowseCollection('$db');")."</strong></td>
		</tr>
		
		";
	}
	
	}
$html=$html."$table</table>	";
$tpl=new templates();		
echo $tpl->_ENGINE_parse_body($html);
	
}

function GetDatabaseNameFormInt($int,$list){
	$tp=explode("\n",$list);
	while (list ($num, $line) = each ($tp)){
		if(preg_match('#\s+'.$int.'\s+.+?\s+(.+)#',$line,$re)){
			return trim($re[1]);
		}
	}
	
}


function dar_list_type(){
	
	$index=$_GET["dar-list-index"];
	$array=dar_list_build_array();
	$main=$array["MAIN"][$index];

	
	while (list ($num, $line) = each ($main)){
		$txt=$num;
		if(strlen($txt)>20){$txt=substr($txt,0,20).'...';}
		$hash[$num]=$txt;
	}
	$hash[null]='{select}';
	$tpl=new templates();
	
	echo $tpl->_ENGINE_parse_body(Field_array_Hash($hash,'dar_list_selected_type','',"DarlistSelectedType()"));
	
}

function dar_list(){
	$page=CurrentPageName();
	$tpl=new templates();
	$dar_collection_root=dar_collection_root();
	
if($dar_collection_root==null){
	$html="<H1>{query_collection}</H1>
	<center>
	<p style='font-size:12px;color:red;font-weight:bold'>{you_need_to_mount_collection_first}</p>
	</center>";
	echo $tpl->_ENGINE_parse_body($html);
	return null;
}	
	
	writelogs("Build collection...($dar_collection_root)",__FUNCTION__,__FILE__);
	
	$array=dar_list_build_array();
	$indexes=$array["INDEXES"];
	$indexes[null]='{select}';
	$index_fied=Field_array_Hash($indexes,"INDEX",null,"DarListChangeIndexes()");	
	
$html="<H1>{list_dar_collection}</H1>

<table style='width:100%'>
<tr>
	<td valign='top'>
		<p class=caption>{list_dar_collection_text}</p>	
	</td>
	<td valign='top'>
		<table style='width:100%'>
		<tr>
			<td width=1%>" . imgtootltip('refresh-24.png','{refresh_cache}','RefreshCache()')."</td>
			<td valign='middle'>" . texttooltip('{refresh_cache}','{{refresh_cache}','RefreshCache()')."</td>
		</tr>
		</table>
	</td>
</tr>
</table>
<table style='width:99%' class=table_form>
<tr>
	<td class=legend>{index}:</td>
	<td>$index_fied</td>
	<td class=legend>{type}:</td>
	<td><span id='dar-index-2'></span></td>
</tr>
</table>

<div style='width:100%;height:300px;overflow:auto' id='darlisting'></div>

";



echo $tpl->_ENGINE_parse_body($html);
}

function dar_list_build_array(){
$dar_collection_root="ressources/dar_collection/".dar_collection_root();
$session_index="DAR_ARRAY_".dar_collection_root();


if(is_array($_SESSION[$session_index])){
	writelogs("Session returned " . count($session_index) . " arrays rows",__FUNCTION__,__FILE__);
	return $_SESSION[$session_index];
	}

if(!is_file("$dar_collection_root/collections.dmd")){
	writelogs("Unable to stat $dar_collection_root/collections.dmd",__FUNCTION__,__FILE__);
	return array();
	}
$datas=file_get_contents("$dar_collection_root/collections.dmd");
$ini2=new Bs_IniHandler("$dar_collection_root/user_defined.conf");
$iniSize=new Bs_IniHandler("$dar_collection_root/collections.size");


$tp=explode("\n",$datas);
while (list ($num, $line) = each ($tp)){
	if(trim($line)==null){continue;}
	if(preg_match('#\s+([0-9]+)\s+.+?\s+(.+)#',$line,$re)){
		$title=$re[2];
		if(preg_match('#^cyrus_imap_mail#',$re[2])){continue;}
		if($ini2->_params[$re[2]]["TargetFolder"]<>null){$title=$ini2->_params[$re[2]]["TargetFolder"];}
		if(preg_match('#(.+?)-([0-9]+)-([0-9]+)-([0-9]+)-([0-9]+)-([0-9]+)-diff#',$title,$ri)){
			$new_title=$ri[1];
			if($ini2->_params[$new_title]["TargetFolder"]<>null){$title=$ini2->_params[$new_title]["TargetFolder"];}
			$date="{$ri[2]}-{$ri[3]}-{$ri[4]} {$ri[5]}:{$ri[6]}";
			
			
		}
		if(preg_match('#(.+?)_#',$re[2],$ti)){
			$index=$ti[1];
		}else{$index=$re[2];}
		
		
		if(preg_match('#cyrus_imap_datas(.+)#',$re[2],$ti)){$index="cyrus_imap_datas";$title="cyrus_imap_datas";}
			
		
		$js="BrowseCollection('{$re[2]}');";
		if($date==null){$date="ORIGIN";}
		$size=FormatBytes($iniSize->_params["SIZE"][$re[2]]);
		
		
		$index_titles[$index]=$index;
		
		$ARRAYT[$index][$title][]=array("DATE"=>$date,"DB"=>$re[2],"JS"=>$js,"source_folder"=>$title,"size"=>$size);
		}
		
	

}

	$_SESSION[$session_index]=array("INDEXES"=>$index_titles,"MAIN"=>$ARRAYT);
	return $_SESSION[$session_index];
	
}
function dar_list_query(){	
	$dar_collection_root=dar_collection_root();
	$array=dar_list_build_array();
$_GET["type"]=stripslashes($_GET["type"]);

	$main=$array["MAIN"][$_GET["dar-list-query"]][$_GET["type"]];
	if(!is_array($main)){
		writelogs("No array given...",__FUNCTION__,__FILE__);
		return null;
	
		}
	
	$table="<table style='width:99%' class=table_form>";
	
	
while (list ($num, $a) = each ($main)){
	$date=$a["DATE"];
	$js=$a["JS"];
	$DB=$a["DB"];
	$original_path=$a["source_folder"];
	if($date=='ORIGIN'){
		$type='SOURCE';
	}else{$type="INCREMENT";}
	
	if(strlen($original_path)>30){$text=texttooltip(substr($original_path,0,27),$original_path,$js);}else{$text=$text=texttooltip($original_path,"{browse}",$js);}
	$table=$table."
		<tr " . CellRollOver().">
			<td width=1% align='center'>". imgtootltip('spotlight-18.png','{browse}',"$js")."</td>
			<td nowrap><div style='width:85px;' style='font-size:11px'>". texttooltip($date,'{browse}',$js)."</div></td>
			<td nowrap><strong style='font-size:11px'>$type</td>
			<td nowrap><strong style='font-size:11px'>$text</td>
			<td nowrap><strong style='font-size:11px'>{$a["size"]}</td>
			<td><input type='button' OnClick=\"javascript:RestoreFull('$original_path','{$DB}','{$date}','$dar_collection_root');\" value='{restore}'></td>
			</tr>
		";
			
		}
		
		$table=$table."</table>";

$tpl=new templates();		
echo $tpl->_ENGINE_parse_body($table);
	
}


function schedule(){
$dar=new dar_back();
$page=CurrentPageName();
while (list ($num, $line) = each ($dar->array_days)){
	$day=$line;
	$enabled=$dar->main_array["BACKUP"][$day];
	$days=$days."
	<tr>
		<td class=legend>{$day}</td>
		<td>".Field_checkbox($day,1,$enabled)."</td>
	</tr>";
	
}


for($i=0;$i<60;$i++){
	if($i<10){$mins[$i]="0$i";}else{$mins[$i]=$i;}
	}
for($i=0;$i<24;$i++){
	if($i<10){$hours[$i]="0$i";}else{$hours[$i]=$i;}
	}	
	
$minutes=Field_array_Hash($mins,'msched',$dar->main_array["BACKUP"]["msched"]);
$hour=Field_array_Hash($hours,'hsched',$dar->main_array["BACKUP"]["hsched"]);


$html="<H1>{schedule}</H1>
	
<table style='width:99%'>
<tr>
	<td valign='top' width=1%><img src='img/chrono-64.png'></td>
	<td valign='top'>
		<p class=caption>{schedule_text}</p>
		<form name='ffmsched'>
		<table style='width:100%' class=table_form>
		$days
		<tr>
			<td class=legend>{time}</td>
			<td>$hour&nbsp;:&nbsp;$minutes</td>
		</tr>
		<tr>
			<td colspan=2 align='right'>
				<input type='button' value='{edit}&nbsp;&raquo;' OnClick=\"javascript:ParseForm('ffmsched','$page',true);\">
			</td>
		</tr>
		</table>
		</form>
	</td>
	</tr>
</table>
	
";	
	
$tpl=new templates();		
echo $tpl->_ENGINE_parse_body($html);	
}


function js(){
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{incremental_backup}');
	$page=CurrentPageName();
	$AddExcludeDarFileType=$tpl->_ENGINE_parse_body('{AddExcludeDarFileType}');
	$RebuildCollection=$tpl->_ENGINE_parse_body('{RebuildCollectionAsk}');
	$success=$tpl->_ENGINE_parse_body('{success}');
	$browse=$tpl->_ENGINE_parse_body('{browse}');
	$restore=$tpl->_ENGINE_parse_body('{restore}');
	$schedule=$tpl->_ENGINE_parse_body('{schedule}');
	$viewlogs=$tpl->_ENGINE_parse_body('{view_logs}');
	$browse_external_storage=$tpl->_ENGINE_parse_body('{browse_external_storage}');
	$popuplate=$tpl->_ENGINE_parse_body('{populate_text}');
	$loadJS="LoadInc();";
	
	if(isset($_GET["js-logs"])){$loadJS="ViewBackupLogs();";}
	$html="
	
var x_AddExcludeDarFileType= function (obj) {
	LoadAjax('dar_exclude_types_list','$page?exclude-type-list=yes');
	
}		
	
	function AddExcludeDarFileType(){
		var ext=prompt('$AddExcludeDarFileType');
		if(ext){
			var XHR = new XHRConnection();
			XHR.appendData('AddExcludeDarFileType',ext);
			document.getElementById('dar_exclude_types_list').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
			XHR.sendAndLoad('$page', 'GET',x_AddExcludeDarFileType);			
		
		}
	
	}
	
	function LoadInc(){
		YahooWin(700,'$page?index-page=yes','$title');
	
	}
	
var x_AddDarPersoFolder= function (obj) {
	LoadAjax('WhatToBackup','$page?dar-target=yes&tab=user_defined');
	
}		
	
	
	function AddDarPersoFolder(){
		var dar_perso_folder=document.getElementById('dar_perso_folder').value;
		if(dar_perso_folder.length==0){return false;}
		var XHR = new XHRConnection();
		XHR.appendData('AddDarPersoFolder',dar_perso_folder);
		document.getElementById('WhatToBackup').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_AddDarPersoFolder);		
	}
	
	function DelDarPersoFolder(md){
		var XHR = new XHRConnection();
		XHR.appendData('DelDarPersoFolder',md);
		document.getElementById('WhatToBackup').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_AddDarPersoFolder);		
		}
		
	
	var x_ExternalUsbSelect=function (obj) {
		var tempvalue=obj.responseText;
		if (tempvalue.length>0){alert(tempvalue);} 
		if(document.getElementById('usblist')){
			LoadAjax('usblist','$page?external-storage-usb-list=yes');
		}
		
		}	
		


	
	function ExternalUsbSelect(pattern){
		var XHR = new XHRConnection();
		XHR.appendData('ExternalUsbSelect',pattern);
		if(document.getElementById('usblist')){
			document.getElementById('usblist').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		}
		XHR.sendAndLoad('$page', 'GET',x_ExternalUsbSelect);	
	}
	
var x_ExternalStorageDelete=function (obj) {
	var tempvalue=obj.responseText;
	if (tempvalue.length>0){alert(tempvalue);} 
	javascript:YahooWin4('700','dar.index.php?external-storage-list=yes','$browse_external_storage')
	
}		
	
	function ExternalStorageDelete(md){
		var XHR = new XHRConnection();
		XHR.appendData('ExternalStorageDelete',md);
		document.getElementById('external_storage_list').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_ExternalStorageDelete);	
	

	}

		
	var x_DarQuery=function (obj) {
		var tempvalue=obj.responseText;
		document.getElementById('query_results').innerHTML=tempvalue;
	}		
	
	function DarQuery(){
		var qstring=document.getElementById('qstring').value;
		var XHR = new XHRConnection();
		XHR.appendData('DarQuery',qstring);
		document.getElementById('query_results').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_DarQuery);	
	}
	
	
	function RebuildCollection(){
	 if(confirm('$RebuildCollection')){
			var XHR = new XHRConnection();
			XHR.appendData('RebuildCollection','yes');
			XHR.sendAndLoad('$page', 'GET');
			alert('$success');
		}
	}
	
	
	function BrowseCollection(collection){
		YahooWin5('700','$page?dar-browse='+collection,'$browse');
		}
		
	function ParcoursCollection(collection,level){
		YahooWin5('700','$page?dar-browse='+collection+'&level='+level,'$browse');
	}
	
	function DarRestoreSingleFile(filename,collection,filepath){
		YahooWin6(450,'$page?restore-single-file='+filename+'&database='+collection+'&filepath='+filepath,'$restore');
		}
	
	
	
var x_RefreshCache=function (obj) {
	var tempvalue=obj.responseText;
	if (tempvalue.length>0){alert(tempvalue);} 
	YahooWin3(750,'$page?dar-list=yes','List collections')
	
}
	
	function RefreshCache(){
		var XHR = new XHRConnection();
		XHR.appendData('RefreshCache','yes');
		document.getElementById('darlisting').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
		XHR.sendAndLoad('$page', 'GET',x_RefreshCache);	
		
	}
	
	function AutoHide(div){
		if(document.getElementById(div).style.display=='none'){
			document.getElementById(div).style.display='block';
		}else{
			document.getElementById(div).style.display='none';
		}
	}
	
	
	function RestoreFull(original_path,database,zdate,collectionRoot){
		YahooWin6(450,'$page?restore-full='+original_path+'&database='+database+'&date='+zdate+'&collectionRoot='+collectionRoot,'$restore');
		}
		
		
    function SchedulePersoFolder(path){
    	YahooWin4(500,'$page?user-defined-schedule='+path,'$schedule');
    }
    
    function ViewBackupLogs(){
    	YahooWin2(700,'$page?events=yes','$viewlogs');
    }
    
    function ChangeViewLogsDateDar(){
    	var selected_date=document.getElementById('selected_date').value;
    	var lines=document.getElementById('lines').value;
    	YahooWin2(700,'$page?events=yes&selected_date='+selected_date+'&lines='+lines,'$viewlogs');
    	
    }
    
    function DarListChangeIndexes(){
    	var selected_index=document.getElementById('INDEX').value;
    	LoadAjax('dar-index-2','$page?dar-list-index='+selected_index);
    }
    
	function DarlistSelectedType(){
    	var selected_index=document.getElementById('INDEX').value;
    	var dar_list_selected_type=document.getElementById('dar_list_selected_type').value;
    	LoadAjax('darlisting','$page?dar-list-query='+selected_index+'&type='+dar_list_selected_type);
    }  

    function DarExternalRessourcePopuplate(num){
    	var XHR = new XHRConnection();
		XHR.appendData('populate',num);		
		XHR.sendAndLoad('$page', 'GET');	
    	alert('$popuplate');
	}
	
	$loadJS
	";
	
	return $html;
}


function dar_browse(){
	if(!isset($_GET["level"])){$level=0;}else{$level=$_GET["level"];}
	$array=dar_browse_build_xml($_GET["dar-browse"]);
	$table=dar_browse_parse($array,$_GET["dar-browse"],$level);
	$html="
	<h1>{$_GET["dar-browse"]}</H1>
		
	<div style='width:99%;height:300px;overflow:auto' id='treeCollection'>
	$table
	</div>
	";
	
	
	
	
	echo $html;

}	


function dar_browse_parse($array,$collection,$level=0){
	
	$style_top="font-size:11px;font-weight:bold";
	
	$newarray=$array;
	if(strpos($level,'-')>0){
		
		$pp=explode('-',$level);
		$newlevel=0;
		
		$top="<a href='#' OnClick=\"javascript:ParcoursCollection('$collection','0')\" style='$style_top'>&nbsp;&raquo;&nbsp;Root</a>";
		while (list ($num, $lev) = each ($pp) ){
			if($lev<>null){
				if($newarray[$lev]["attrs"]["NAME"]<>null){
					$newlevel=$newlevel."-$lev";
					$recover_path=$recover_path."/".$newarray[$lev]["attrs"]["NAME"];
					$top=$top."<a href='#' OnClick=\"javascript:ParcoursCollection('$collection','$newlevel')\" style='$style_top'>&nbsp;&raquo;&nbsp;{$newarray[$lev]["attrs"]["NAME"]}</a>";
				}
				$newarray=$newarray[$lev]["children"];			
			}
		}
		//print_r($newarray);
		$array=$newarray;
	}else{
		$array=$array[0]["children"];
	}
	
	
	$recover_path=substr($recover_path,1,strlen($recover_path));
	if(!is_array($array)){return null;}
	$html="
	$top
	<table style='width:98%' class=table_form>";
	while (list ($num, $arraysb) = each ($array) ){
		if($arraysb["name"]=="FILE"){
			$filename=$arraysb["attrs"]["NAME"];
			$size=$arraysb["attrs"]["SIZE"];
			$size=FormatBytes(($size/1024));
			$t=$arraysb["children"][0]["attrs"]["ATIME"];
			$ATIME=date('Y-m-d H:i:s',$t);
			$js="DarRestoreSingleFile('$filename','$collection','$recover_path')";
			$filename=texttooltip($filename,'{restore}',$js);
			$files=$files . "<tr>
								<td width=5% nowrap valign='middle'><img src='img/txt_small.gif'>&nbsp;$filename</td>
								<td width=2%>$size</td>
								<td width=2% nowrap>$ATIME</td>
								
							</tr>
							";
			
		}
		
if($arraysb["name"]=="SYMLINK"){
			$filename=$arraysb["attrs"]["NAME"];
			$size=$arraysb["attrs"]["SIZE"];
			
			$t=$arraysb["children"][0]["attrs"]["ATIME"];
			$ATIME=date('Y-m-d H:i:s',$t);
			$files=$files . "<tr>
								<td width=1% nowrap valign='middle'><img src='img/fw.gif'>&nbsp;$filename</td>
								<td>$size</td>
								<td>$ATIME</td>
								
							</tr>
							";
			
		}		
		
		
		
		if($arraysb["name"]=="DIRECTORY"){
			$filename=$arraysb["attrs"]["NAME"];
			$t=$arraysb["children"][0]["attrs"]["ATIME"];
			$ATIME=date('Y-m-d H:i:s',$t);
			$l=$level."-".$num;
			$dir=$dir . "<tr>
								<td width=1% nowrap valign='middle' nowrap><img src='img/folder.gif'>&nbsp;<a href='#' OnClick=\"javascript:ParcoursCollection('$collection','$l')\">$filename</a></td>
								<td>&nbsp;</td>
								<td>$ATIME</td>
							</tr>
							";
			
		}
		
		
	}
	
	$html=$html.$dir.$files . "</table>";
	return $html;
	//DIRECTORY
	//print_r($array[0]["children"]);
	
	
}

		
function dar_browse_build_xml($database){
	$dar_collection_root=dar_collection_root();
	$path=dirname(__FILE__). "/ressources/dar_collection/$dar_collection_root";
	$cache_index="DAR_ARRAY_{$dar_collection_root}_{$database}";
	
if(is_array($_SESSION[$cache_index][0])){
	if(count($_SESSION[$cache_index][0])>0){
		writelogs("return array from xml cache $cache_index",__FUNCTION__,__FILE__);
		return $_SESSION[$cache_index];
		}else{
			writelogs("bad cache $cache_index ".count($_SESSION[$cache_index][0]) ." rows",__FUNCTION__,__FILE__);
		}
	
	}else{
		writelogs("bad cache $cache_index \"not an array\"",__FUNCTION__,__FILE__);	
	}
	
		$sock=new sockets();
		if(is_file("$path/{$database}.xml")){
			writelogs("Reading form file cache \"$path/{$database}.xml\"",__FUNCTION__,__FILE__);
			$datas=file_get_contents("$path/{$database}.xml");
		}else{
			writelogs("$path/{$database}.xml didn't exists invoke artica to get cache...",__FUNCTION__,__FILE__);
			$sock->getfile('DarBrowser:'.$database);
			if(is_file("$path/{$database}.xml")){
				$datas=file_get_contents("$path/{$database}.xml");
			}else{
				writelogs("unable to stat cache $path/{$database}.xml",__FUNCTION__,__FILE__);
				return array();
			}
		}
			
			
			
	writelogs(strlen($datas)." bytes for $database.xml",__FUNCTION__,__FILE__);
	$datas=str_replace("\n\n","\n",$datas);
	if(preg_match('#(.+?)<Catalog(.+?)>#is',$datas,$re)){
		$datas=str_replace($re[0],'',$datas);
		$datas=trim($datas);
	}else{
		writelogs("unable to preg #(.+?)<Catalog(.+?)>#is fot these datas",__FUNCTION__,__FILE__);
		return array();
		}

	$xml=new xml2Array();
	$xml->parse("<Catalog format=\"1.0\">$datas");
	$_SESSION[$cache_index]=$xml->arrOutput;
	writelogs("Writing cache index $cache_index ".count($_SESSION[$cache_index][0]) . " rows",__FUNCTION__,__FILE__);
	return 	$_SESSION[$cache_index];
	
}


function dar_view_index(){
$dar=new dar_back();
	$tpl=new templates();
	$page=CurrentPageName();
	$users=new usersMenus();
	if(!$users->dar_installed){
		$html=Paragraphe_switch_disable('{no_dar_installed}','{no_dar_installed_text}',null,300);
		echo $tpl->_ENGINE_parse_body($html);
		exit;
	}
	
	
	$html="<H1>{display_dar_collection}</H1>
	<table style='width:100%'>
	<tr>
		<td valign='top'>
	<table style='width:100%'>
	<tr>
		<td valign='top'>" . Paragraphe('64-hd-usb.png','{mount_dar}','{mount_dar_text}',"javascript:Loadjs('$page?mount-dar');")."</td>
		<td valign='top'>" . Paragraphe('64-banned-regex.png','{query_collection}','{query_collection_text}',"javascript:YahooWin4(700,'$page?dar-query=yes','{query_collection}')")."</td>
	</tr>
	<tr>
	<td valign='top'>" . Paragraphe('64-banned-phrases-rebuild.png','{rebuild_collection}','{rebuild_collection_text}',"javascript:RebuildCollection();")."</td>
	<td valign='top'>" . Paragraphe('64-categories-loupe.png','{list_dar_collection}','{list_dar_collection_text}',"javascript:YahooWin3(750,'$page?dar-list=yes','{list_dar_collection}')")."</td>
	</tr>
	</table>
	</td>
	</tr>
	</table>
";
			echo $tpl->_ENGINE_parse_body($html);
	
}	

function external_storage_usb_save(){
$dar=new dar_back();
$pattern="{$_GET["ExternalUsbSelect"]}";
$dar->external_storage[md5($pattern)]=$pattern;
$dar->Save();
$tpl=new templates();
echo $tpl->_ENGINE_parse_body("{use_usb_storage}:\n----------------------------------\n{device}: \"{$_GET["ExternalUsbSelect"]}\"\n{added}");
}

function external_storage_net_save(){
$dar=new dar_back();
$pattern="smb:{$_GET["net_storage_user"]}:{$_GET["net_storage_password"]}@{$_GET["net_storage_server"]}:/{$_GET["net_storage_share"]}";
$dar->external_storage[md5($pattern)]=$pattern;	
$dar->Save();
$tpl=new templates();
echo $tpl->_ENGINE_parse_body("{use_network_storage}:\n----------------------------------\n\"\\\\{$_GET["net_storage_server"]}\\{$_GET["net_storage_share"]}\"\n{added}");	
}


function external_storage_usb(){
	$list=external_storage_usb_list();
	$page=CurrentPageName();
	$tpl=new templates();
	
	$html="
	<h1>{use_usb_storage}</H1>
	<table style='width:99%'>
	<tr>
	<td valign='top' with=70%>
	<p class=caption>{use_usb_storage_text}</p>
	<strong style='font-size:12px'>{use_usb_storage_explain}</strong>
	</td>
	<td valign='top' align='center' width=30% style='background-color:#FFFFFF;padding:5px;margin:5px;border:1px solid #CCCCCC'>
	
	<input type='button' OnClick=\"javascript:LoadAjax('usblist','$page?external-storage-usb-list=yes');\" value='{refresh}&nbsp;&raquo;'>
	
	</td>
	</tr>
	</table>
	<hr>
	<div style='width:100%;height:300px;overflow:auto;padding:3px;' id='usblist'>
		$list
	</div>
	";

echo  $tpl->_ENGINE_parse_body($html);	
	
}


function  external_storage_net(){
	$list=external_storage_form();
	$tpl=new templates();
	//<input type='button' OnClick=\"javascript:LoadAjax('storagelist','$page?external-storage-net-test=yes');\" value='{test}&nbsp;&raquo;'>
	$html="
	<h1>{use_network_storage}</H1>
	<table style='width:99%'>
	<tr>
	<td valign='top' with=70%>
	<p class=caption>{use_network_storage_text}</p>
	</td>
	<td valign='top' align='center' width=30% style='background-color:#FFFFFF;padding:5px;margin:5px;border:1px solid #CCCCCC'>
	
	
	
	</td>
	</tr>
	</table>
	<hr>
	<div style='width:100%;height:190px;overflow:auto;padding:3px;' id='storagelist'>
		$list
	</div>";	
	
	echo  $tpl->_ENGINE_parse_body($html);	
}

function  external_storage_form(){
	$page=CurrentPageName();
	$dar=new dar_back();
	$ress=$dar->main_array["GLOBAL"]["external_ressource"];
	
	if(preg_match('#smb:(.+?):(.+?)@(.+?):/(.+)#',$ress,$re)){
		
	}
	
	$html="
	<form name='FFMNET'>
	<table style='width:99%' class=table_form>
	<tr>
		<td class=legend>{server}:</td>
		<td>". Field_text('net_storage_server',$re[3],'width:120px')."</td>
	</tr>
	<tr>
		<td class=legend>{shared_folder}:</td>
		<td>". Field_text('net_storage_share',$re[4],'width:120px')."</td>
	</tr>	
	<tr>
		<td class=legend>{username}:</td>
		<td>". Field_text('net_storage_user',$re[1],'width:120px')."</td>
	</tr>
	<tr>
		<td class=legend>{password}:</td>
		<td>". Field_password('net_storage_password',$re[2],'width:120px')."</td>
	</tr>	
	<tr>
		<td colspan=2 align='right'><hr><input type='button' OnClick=\"javascript:ParseForm('FFMNET','$page',true);\" value='{edit}&nbsp;&raquo;'></td>
	</tr>
	</table>
	</form>				
	";
	
	$tpl=new templates();
	return  $tpl->_ENGINE_parse_body($html);
}

function external_storage_usb_list(){
	include_once("ressources/class.os.system.tools.inc");
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?usb-scan-write=yes");
	$tpl=new templates();
	if(!file_exists('ressources/usb.scan.inc')){return $tpl->_ENGINE_parse_body("<H3 style=color:red'>{error_no_socks}</H3>");}		
	include("ressources/usb.scan.inc");	
	
		$dar=new dar_back();
	
	
	$html="<table style='width:100%'><tr>";
	$count=0;
	$os=new os_system();
	while (list ($uid, $array) = each ($_GLOBAL["usb_list"]) ){
		if(preg_match('#swap#',$array["TYPE"])){continue;}
		if(trim($array["mounted"])=='/'){continue;}
		
		$start=null;
		$end=null;
		if($count==2){
			$start="<tr>";
			$end="</tr>";
			$count=0;
		}
		
		$VENDOR=$array["ID_VENDOR"];
		if(strtoupper($VENDOR)=="TANDBERG"){$img="tandberg-64.png";}else{$img="usb-32.png";}
		
		if($mounted=="/"){return "disk-64.png";}
		if($TYPE=="swap"){return "disk-64.png";}
		
		$html=$html.$start;
		$html=$html."<td valign='top'>";
		$html=$html .RoundedLightWhite("
				<table style='width:280px;margin:5px;'>
				<tr>
					<td valign='top'><img src='img/$img'></td>
					<td valign='top'>".	$os->usb_parse_array($array)."</td>
				</tr>
				<tr>
					<td colspan=2 align='right'><input type='button' OnClick=\"javascript:ExternalUsbSelect('$uid');\" value='{select} {this_device}&nbsp;&raquo;'></td>
				</tr>
			</table>");
		
		$html=$html ."</td>";
		$html=$html.$stop;
		$count=$count+1;
	}
	$html=$html."</tr>
	</table>";
	
	return  $tpl->_ENGINE_parse_body($html);
	
}
	

function external_storage(){
	$tpl=new templates();
	$page=CurrentPageName();
	$html="<h1>{external_storage}</H1>
	<input type='hidden' id='dar-external-storage-formulaire' value=''>
	<p class=caption>{external_storage_text}</p>
	<table style='width:100%'>
		<tr>
			<td valign='top'>" . Paragraphe('64-external-drive.png','{use_usb_storage}','{use_usb_storage_text}',
			"javascript:Loadjs('browse.usb.php?set-field=dar-external-storage-formulaire');")."</td>
			<td valign='top'>" . Paragraphe('64-network_drive.png','{use_network_storage}','{use_network_storage_text}',
			"javascript:Loadjs('smb.browse.php?set-field=dar-external-storage-formulaire');")."</td>
		</tr>
		<tr>
			<td valign='top'>" . Paragraphe('64-templates.png','{browse_external_storage}','{browse_external_storage_text}',
			"javascript:YahooWin4('700','dar.index.php?external-storage-list=yes','Storages collection')")."</td>
			<td>&nbsp;</td>
		</tr>
		
		
	</table>
	
	
	
	";
echo $tpl->_ENGINE_parse_body($html);	
	
}

function external_storage_delete(){
	
	$md=$_GET["ExternalStorageDelete"];
	$dar=new dar_back();
	unset($dar->external_storage[$md]);
	$dar->Save();
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("{deleted}");
	
}

function external_storage_list(){
$tpl=new templates();
	$page=CurrentPageName();
	
	$dar=new dar_back();
	if(file_exists('ressources/usb.scan.inc')){include_once('ressources/usb.scan.inc');}
	
	$html="<H1>{browse_external_storage}</H1>
	<p class=caption>{browse_external_storage_explain}</p>
	<div style='width:100%;height:300px;overflow:auto;' id='external_storage_list'>
	<center>
	<table style='width:50%' class=table_form>
	";
	if(is_array($dar->external_storage)){
	while (list ($num, $line) = each ($dar->external_storage) ){
		
		$delete=null;
		if(preg_match('#^smb:(.+?)@(.+?)/(.+)#',$line,$re)){
			$img="48-samba.png";
			$index=$num;
			$delete=imgtootltip('ed_delete.gif','{delete}',"ExternalStorageDelete('$index')");
			$server=$re[2];
			$folder=$re[3];
			$line=null;
			
		}
		
		if(preg_match('#^smb:.+?@(.+?)#',$line,$re)){
			$img="48-samba.png";
			$index=$num;
			$delete=imgtootltip('ed_delete.gif','{delete}',"ExternalStorageDelete('$index')");
			$server=$re[1];
			$folder=null;
			$line=null;
			
		}		
		
	if(preg_match('#^usb:(.+?)/(.+)#',$line,$re)){
			$img="usb-48-green.png";
			$index=$num;
			$server=$re[1];
			$label=$_GLOBAL["usb_list"][$server]["LABEL"];
			if($label<>null){$server=$label;}
			$delete=imgtootltip('ed_delete.gif','{delete}',"ExternalStorageDelete('$index')");
			$folder="{$re[2]}";
			
			$line=null;
			
		}

	if(preg_match('#^usb:(.+?)#',$line,$re)){
			$img="usb-48-green.png";
			$index=$num;
			$server=$re[1];
			$label=$_GLOBAL["usb_list"][$server]["LABEL"];
			if($label<>null){$server=$label;}
			$delete=imgtootltip('ed_delete.gif','{delete}',"ExternalStorageDelete('$index')");
			$folder="USB";
			$line=null;
			
		}			

		if(preg_match('#^file:(.+)#',$line,$re)){
			$img="hd-48.png";
			$index=$num;
			$server=$re[1];
			$folder="DIR";
			
		}	

	$popuplate=imgtootltip("20-refresh.png","{populate}","DarExternalRessourcePopuplate($num)");

	$html=$html . "
		<tr " . CellRollOver().">
			<td width=1%><img src='img/$img'></td>
			<td style='font-size:12px'>$server</td>
			<td style='font-size:12px'>$folder</td>
			<td style='font-size:12px'>$delete</td>
		</tr>
	
	";		
	}
}
	
$html=$html . "</table></div>";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
	
	
}

function index_rsync(){
	
	
$view_events_server=Paragraphe("routing-domain-relay-events.png","{APP_RSYNC_SERVER_LOG}","{APP_RSYNC_SERVER_LOG_TEXT}","javascript:Loadjs('rsync.server.php?js-logs=yes')");	
$tab=index_tab();	
$html="	
$tab

	<table style='width:100%'>
	<tr>
		<td valign='top'>" . Paragraphe('routing-domain-relay.png','{APP_RSYNC}','{APP_RSYNC_SERVER_TEXT}',"javascript:Loadjs('rsync.server.php')")."</td>
		<td valign='top'>" . Paragraphe('routing-rule.png','{APP_RSYNC_CLIENT}','{APP_RSYNC_CLIENT_TEXT}',"javascript:Loadjs('rsync.client.php')")."</td>
	</tr>
	<tr>
		
		<td valign='top'>$view_events_server</td>
		<td valign='top'>" . Paragraphe('routing-rule-events.png','{APP_RSYNC_CLIENT_LOG}','{APP_RSYNC_CLIENT_LOG_TEXT}',"javascript:Loadjs('rsync.client.php?viewlog-js=yes')")."</td>
			
	</tr>
	</table>";

$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html);
	
}

function index_dar(){
$page=CurrentPageName();	
$tab=index_tab();	

$when=Paragraphe("64-planning.png","{WHEN_TO_BACKUP}","{WHEN_TO_BACKUP_TEXT}","javascript:YahooWin3(500,'$page?schedule=yes','{schedule}')");
$where=Paragraphe("64-dar-index.png","{WHERE_TO_BACKUP}","{WHERE_TO_BACKUP_TEXT}","javascript:YahooWin3(500,'$page?external-storage=yes','{WHERE_TO_BACKUP}')");
$global_settings=Paragraphe('64-parameters.png','{global_settings}','{global_settings_text}',"javascript:YahooWin2(650,'$page?dar-settings=yes','{global_settings}')");
$what=Paragraphe('64-download.png','{what_to_backup}','{what_to_backup_text}',"javascript:YahooWin2(500,'$page?dar-target=yes','{what_to_backup}')");
$run=Buildicon64('DEF_ICO_RUNBACKUP');



$display=Buildicon64('DEF_ICO_DARLIST');
$html="	
$tab

	<table style='width:100%'>
	<tr>
		<td valign='top'>$what</td>
		<td valign='top'>$when</td>
	</tr>
	<tr>
	<td valign='top'>$where</td>
	<td valign='top'>$display</td>
	</tr>
	<tr>
	<td valign='top'>$global_settings</td>
	<td valign='top'>$run</td>
	</tr>	
	
	</table>";

$tpl=new templates();
return  $tpl->_ENGINE_parse_body($html);
	
}

function index_tab(){
	$page=CurrentPageName();
	$tpl=new templates();
	if($_GET["dar-index-tab"]==null){$_GET["dar-index-tab"]="dar";};
	
	$array["dar"]=$tpl->_ENGINE_parse_body('{incremental_backup}');
	$array["rsync"]=$tpl->_ENGINE_parse_body('{APP_RSYNC}');
	
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["dar-index-tab"]==$num){$class="id=tab_current";}else{$class=null;}
		if(strlen($ligne)>28){
			$ligne=texttooltip(substr($ligne,0,25)."...",$ligne,null,null,1);
		}
		$html=$html . "<li><a href=\"javascript:LoadAjax('index-dar','$page?dar-index-tab=$num')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";	
	
}


function index(){
	$dar=new dar_back();
	$tpl=new templates();
	$page=CurrentPageName();
	$users=new usersMenus();
	if(!$users->dar_installed){
		$html=Paragraphe_switch_disable('{no_dar_installed}','{no_dar_installed_text}',null,300);
		echo $tpl->_ENGINE_parse_body($html);
		exit;
	}
	
	
	$html="<H1>{incremental_backup}</H1>
	<table style='width:100%'>
	<tr>
		<td valign='top'>
			<div id='index-dar'>". index_dar()."</div>
			<br>
			" . RoundedLightWhite("
			<table style='width:99%'>
			<tr>
				<td valign='top' width=1%><img src='img/dar-logo.png'></td>
				<td valign='top'><p class=caption>{dar_pub}</p></td>
			</tr>
			</table>")."
			
			
			</td>
			<td valign='top'>
				<div id='darstatus'>".dar_status()."</div>
			</td>
		</tr>
	</table>
";
			echo $tpl->_ENGINE_parse_body($html);
	
}

function dar_status(){
	$page=CurrentPageName();
	$sock=new sockets();
	$ini=new Bs_IniHandler();
	$dar=new dar_back();
	$ini->loadString($sock->getfile('dar_manager_status'));
	//$logslist=$sock->getfile('ArticaBackupLogsList');
	
	
	//warning24.png
	$html="<table style='width:100%'>";
	if($ini->_params["DAR"]["mem"]>0){
		$html=$html . "
		<tr>
			<td width=1%><img src='img/ok24.png'></td>
			<td style='font-size:12px'>{APP_DAR}</td>
		</tr>
		<tr>
			<td></td>
			<td><strong>{$ini->_params["DAR"]["mem"]} MB {memory}</td>
		</tr>";
	}else{
			$html=$html . "
		<tr>
			<td width=1%><img src='img/warning24.png'></td>
			<td style='font-size:12px'>{APP_DAR} ({sleeping})</td>
		</tr>";
	}
	
	$html=$html . "<tr><td colspan=2><hr></td></tr>";
	
	if($ini->_params["DAR_MANAGER"]["mem"]>0){
		$html=$html . "
		<tr>
			<td width=1%><img src='img/ok24.png'></td>
			<td style='font-size:12px'>{APP_DAR_MANAGER}</td>
		</tr>
		<tr>
			<td></td>
			<td><strong>{$ini->_params["DAR_MANAGER"]["mem"]} MB {memory}</td>
		</tr>";
	}else{
			$html=$html . "
		<tr>
			<td width=1%><img src='img/warning24.png'></td>
			<td style='font-size:12px'>{APP_DAR_MANAGER} ({sleeping})</td>
		</tr>";
	}

	$html=$html . "<tr><td colspan=2><hr></td></tr>";
	
	if($ini->_params["ARTICA_BACKUP"]["mem"]>0){
		$html=$html . "
		<tr>
			<td width=1%><img src='img/ok24.png'></td>
			<td style='font-size:12px'>artica-backup</td>
		</tr>
		<tr>
			<td></td>
			<td><strong>{$ini->_params["ARTICA_BACKUP"]["mem"]} MB {memory}</td>
		</tr>";
	}else{
			$html=$html . "
		<tr>
			<td width=1%><img src='img/warning24.png'></td>
			<td style='font-size:12px'>artica-backup ({sleeping})</td>
		</tr>";
	}

	
	if($dar->main_array["GLOBAL"]["external_ressource"]<>null){
		$remote_point="/opt/artica/mount/".md5($dar->main_array["GLOBAL"]["external_ressource"])."/incremental-backup";
		$sock=new sockets();
		$remote_size=trim($sock->getfile("foldersize:$remote_point"));
		$remote=$remote."
		<tr>
			<td valign='top' class=legend nowrap>{remote_foldersize}:</td>
			<td valign='top' nowrap><strong style='font-size:13px'>$remote_size</strong></td>
		</tr>";
	}
	
	
	if(is_file('ressources/logs/dar_backup_status.conf')){
		$prog=new Bs_IniHandler('ressources/logs/dar_backup_status.conf');
		$status=$prog->_params["STATUS"]["progress"];
		$text_info=$prog->_params["STATUS"]["current"];
		if($status>100){$color="#D32D2D";$status=100;$text='{failed}';}else{$color="#5DD13D";$text=$status.'%';}
		$prgress="
		<div style='width:100px;border:1px solid #CCCCCC;margin:3px'>
		<div style='width:{$status}px;text-align:center;color:white;padding-top:3px;padding-bottom:3px;background-color:$color'>
			<strong>{$text}&nbsp;</strong>
		</div>
		</div>
		<i>$text_info...</i>";
	}
	

	
	
	$html=$html . "</table>
	<br>
	<table style='width:99%'>
		<tr>
			<td valign='top' class=legend nowrap>{backup_foldersize}:</td>
			<td valign='top' nowrap><strong style='font-size:13px'>$dar->FolderSize</strong></td>
		</tr>
			$remote
		<tr>
		<tr>
			<td valign='top' colspan=2 align='right' class=legend>" . texttooltip('{view_logs}','{view_logs}',"ViewBackupLogs();")."</td>
			
		</tr>
		<tr>
			<td valign='top' colspan=2 align='right'><hr>$prgress</td>
		</tr>
			<td colspan=2 align='right'><hr>" . imgtootltip('refresh-24.png','{refresh}',"LoadAjax('darstatus','$page?refresh-dar-status=yes')")."</td>
		</table>
	
	";
	$tpl=new templates();
	$html=$tpl->_ENGINE_parse_body($html);
	$html=RoundedLightWhite($html);
	return $html;
	
}


function dar_settings(){
	$tpl=new templates();
	$page=CurrentPageName();	
	$dar=new dar_back();	
	
	$dar->main_array["BACKUP"]["minimal_compress"]=($dar->main_array["BACKUP"]["minimal_compress"]/1024);
	
	for($i=0;$i<10;$i++){
		$arrcompress[$i]=$i;
	}
	
	for($i=-20;$i<20;$i++){
		$nicearr[$i]=$i;
	}	
	
$html="<H1>{backup_engine}</H1>
	<form name='FFM1DGN'>
	<table style='width:100%' class=table_form>
	<tr>
		<td class=legend>{minimal_compress}:</td>
		<td>".Field_text('minimal_compress',$dar->main_array["BACKUP"]["minimal_compress"],'width:40px')." ko</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend>{exclude_compress_file_types}:</td>
		<td><input type='button' OnClick=\"javascript:YahooWin3('500','$page?exclude-types=yes','{exclude_compress_file_types}');\" value='{exclude_compress_file_types}&nbsp;&raquo;'></td>
		<td>&nbsp;</td>
	</tr>
	
	<tr>
		<td class=legend>{nice_int}:</td>
		<td>" . Field_array_Hash($nicearr,"nice_int",$dar->main_array["BACKUP"]["nice_int"])."</td>
		<td>" . help_icon('{nice_int_text}')."</td>
	</tr>	

	<tr>
		<td class=legend>{compress_level}:</td>
		<td>" . Field_array_Hash($arrcompress,"compress_level",$dar->main_array["BACKUP"]["compress_level"])."</td>
		<td>" . help_icon('{compress_level_help}')."</td>
	</tr>	
	
	<tr>
		<td class=legend>{slice_size_mb}:</td>
		<td>".Field_text('slice_size_mb',$dar->main_array["BACKUP"]["slice_size_mb"],'width:40px')." Mb</td>
		<td>" . help_icon('{slice_size_mb_help}')."</td>
	</tr>	
	

	
	<tr>
		<td colspan=3 align='right'><input type='button' OnClick=\"javascript:ParseForm('FFM1DGN','$page',true);\" value='{edit}&nbsp;&raquo;'>		
	</table>
	</form>";


echo $tpl->_ENGINE_parse_body($html);		
	
}


function dar_exclude_types(){
	$tpl=new templates();
	$page=CurrentPageName();	
	$dar=new dar_back();	
	$table=dar_exclude_types_list();
	$html="<H1>{exclude_compress_file_types}</H1>	
	<p class=caption>{exclude_compress_file_types_text}</p>
	<div style='text-align:right'><input type='button' OnClick=\"javascript:AddExcludeDarFileType();\" value='{add}&nbsp;&raquo;'></div>
	<div style='width:100%;height:300px;overflow:auto;text-align:center' id='dar_exclude_types_list'>
	
	$table
	</div>
	
	";
	
	
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function dar_exclude_types_add(){
	
	$ext=trim($_GET["AddExcludeDarFileType"]);
	$dar=new dar_back();	
	$dar->exclude_files_array[]=$ext;
	$dar->Save();
	
}

function dar_exclude_types_list(){
	$dar=new dar_back();	
	if(!is_array($dar->exclude_files_array)){return null;}
	$tpl=new templates();
	
	$html="<center><table style='width:60%' class=table_form>";
	while (list ($num, $line) = each ($dar->exclude_files_array) ){
		if(trim($line)==null){continue;}
		$html=$html . "<tr " . CellRollOver().">
		<td width=1%><img src='img/fw_bold.gif'></td>
		<td style='font-size:12px'>$line</td>
		<td width=1%>". imgtootltip('ed_delete.gif','{delete}',"DeleteDarExcludeFile('$num');")."</td>
		</tr>
		
		
		";
		
	}
	
	$html=$html . "</table></center>";
	return $tpl->_ENGINE_parse_body($html);
}

function global_settings(){
	$tpl=new templates();
	$page=CurrentPageName();	
	$dar=new dar_back();	
	

	
$backup_engine=Paragraphe('64-folder-tools2.png','{backup_engine}',
'{backup_engine_text}',"javascript:YahooWin2(500,'$page?dar-params=yes','{backup_engine}')");
	
$html="<H1>{global_settings}</H1>
<table style='width:100%'>
<tr>
<td valign='top'>
$backup_engine</td>
<td valign='top'>
	<form name='FFM1GN'>
	<table style='width:100%' class=table_form>
	<tr>
		<td>" . Field_checkbox('enable',1,$dar->main_array["GLOBAL"]["enable"])."</td>
		<td class=legend align='left' style='text-align:left'> {enable_incremental}</td>
	</tr>	
	<tr>
		<td>" . Field_checkbox('notify',1,$dar->main_array["GLOBAL"]["notify"])."</td>
		<td class=legend align='left' style='text-align:left'>{smtp_notify}</td>
	</tr>	
	<tr>
		<td>" . Field_checkbox('use_local_external_failed',1,$dar->main_array["GLOBAL"]["use_local_external_failed"])."</td>
		<td class=legend align='left' style='text-align:left'>{use_local_external_failed}</td>
	</tr>		
	</table>
	<table style='width:100%' class=table_form>
	<tr>
		<td class=legend>{container_path}</td>
		<td>" . Field_text("dar_file",$dar->main_array["GLOBAL"]["dar_file"],'width:210px')."</td>
	</tr>
	<tr>
		<td class=legend>{external_storage}:</td>
		<td><input type='button' OnClick=\"javascript:YahooWin3(500,'$page?external-storage=yes','{external_storage}')\" value='{external_storage}&nbsp;&raquo;'></td>
	</tr>
	
	<tr>
		<td class=legend>{schedule}:</td>
		<td><input type='button' OnClick=\"javascript:YahooWin3(500,'$page?schedule=yes','{schedule}')\" value='{schedule}&nbsp;&raquo;'></td>
	</tr>	
	
	
	<tr>
		<td colspan=2 align='right'><input type='button' OnClick=\"javascript:ParseForm('FFM1GN','$page',true);\" value='{edit}&nbsp;&raquo;'>		
	</table>
	</form>
</td></tr></table>";


echo $tpl->_ENGINE_parse_body($html);	
	
}

function WhatToBackup(){
	$tpl=new templates();
	$page=CurrentPageName();
	switch ($_GET["tab"]) {
			case "what_to_backup":echo WhatToBackup_section();exit;
				break;
			case "user_defined":echo WhatToBackup_user_defined();exit;
				break;	
			default:$content=WhatToBackup_section();
				break;
		}	
	
$html="<H1 style='width:103.1%'>{what_to_backup}</H1>
<p class=caption>{what_to_backup_text}</p>
<div id='WhatToBackup'>$content</div>
";

echo $tpl->_ENGINE_parse_body($html);

}


function WhatToBackup_user_defined(){
	$tab=WhatToBackup_tabs();
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($tab.user_defined());
	
}

function WhatToBackup_section(){
	$tpl=new templates();
	$page=CurrentPageName();	
	$dar=new dar_back();
	$tab=WhatToBackup_tabs();

$html="
$tab
<form name='FFM1GB'>
	<table style='width:100%' class=table_form>
	";

while (list ($num, $line) = each ($dar->target_options) ){
	$label="{{$line}}";
	if($line=="shares_folders"){
		$label=texttooltip("{shares_folders}","{shares_folder_exclude}","YahooWin3(500,'$page?exclude-shared-folders=yes','{shares_folder_exclude}')");
	}
	
	if($line=="homes"){
		$label=texttooltip("{homes}","{homes_folder_exclude}","YahooWin3(500,'$page?exclude-homes-folders=yes','{homes_folder_exclude}')");
	}
	
if($line=="user_defined"){
		$label=texttooltip("{user_defined}","{user_defined_text}","LoadAjax('WhatToBackup','$page?dar-target=yes&tab=user_defined')",'{user_defined_text}');
	}
		
	
	
	$form=$form."
		<tr>
		<td class=legend>$label</td>
		<td>" . Field_checkbox($line,1,$dar->main_array["BACKUP"][$line])."</td>
	</tr>	";
	
	
}
	

	$html=$html . "$form
	<tr>
		<td colspan=2 align='right'><input type='button' OnClick=\"javascript:ParseForm('FFM1GB','$page',true);\" value='{edit}&nbsp;&raquo;'>		
	</table>
	</form>";


return  $tpl->_ENGINE_parse_body($html);	
	
}

function WhatToBackup_tabs(){
	$page=CurrentPageName();
	$array["what_to_backup"]='{what_to_backup}';
	$array["user_defined"]='{user_defined}';
	
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["tab"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('WhatToBackup','$page?dar-target=yes&tab=$num&hostname={$_GET["hostname"]}')\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div><br>";		
}



function SaveGeneralSettings(){
	$dar=new dar_back();	
	while (list ($num, $line) = each ($_GET) ){
		$dar->main_array["GLOBAL"][$num]=$line;
		
	}
	
	$dar->Save();
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');
	
}

function SaveBackupSettings() {
$dar=new dar_back();	

if(isset($_GET["minimal_compress"])){
	$_GET["minimal_compress"]=($_GET["minimal_compress"]*1024);
	writelogs("minimal_compress={$_GET["minimal_compress"]}",__FUNCTION__,__FILE__);
	}

	while (list ($num, $line) = each ($_GET) ){
		$dar->main_array["BACKUP"][$num]=$line;
		
	}
	
	$dar->Save();
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');	
	
	
}


function events(){
	$sock=new sockets();
	$list=$sock->getfile("ArticaBackupLogsList");
	if($_GET["lines"]==null){$_GET["lines"]='500';}
	$list=explode("\n",$list);
	if(is_array($list)){
		while (list ($num, $line) = each ($list) ){
			if(!preg_match("#artica-backup-([0-9\-]+)\.debug#",$line,$re)){continue;}
			$arr[$re[1]]=$line;
			$hash[$re[1]]=$re[1];
		
		}	
		if(is_array($arr)){
		 krsort($arr);
		 krsort($hash);
		}
		
	}
	
	if(is_array($arr)){
		$dates=array_keys($arr);
		if($_GET["selected_date"]==null){
			$current_date=$dates[0];
		}else{
			$current_date=$_GET["selected_date"];
		}
		$sock=new sockets();
		$logs=explode("\n",$sock->getfile("ArticaBackupLastLogsList:{$arr[$current_date]};{$_GET["lines"]}"));
		$logs=array_reverse($logs,true);
		$table="<table style='width:99%' class=table_form>";
		while (list ($num, $line) = each ($logs) ){
			if(trim($line)==null){continue;}
			if(preg_match('#^([0-9\-]+)\s+([0-9:]+)\s+([0-9]+)\s+(.+)#',$line,$ri)){
				$date=$ri[1];
				$time=$ri[2];
				$pid=$ri[3];
				$line=$ri[4];
			}
			$table=$table . "
				<tr " . CellRollOver().">
					<td width=1% valign='top'><img src='img/fw_bold.gif'></td>
					<td nowrap valign='top'><strong>$date</strong></td>
					<td nowrap valign='top'><strong>$time</strong></td>
					<td nowrap valign='top'><strong>$pid</strong></td>
					<td valign='top'><span style='font-size:11px'>$line</span></td>
				</tr>
				";
		}
		
		$table=$table . "</table>";
		
		
		
	}
	
	
	$html="<H1>{view_logs} $current_date</H1>
	<table style='width:100%' class=table_form>
		<tr>
			<td class=legend>{view_logs}:</td>
			<td>" . Field_array_Hash($hash,'selected_date',$current_date,"ChangeViewLogsDateDar()")."</td>
			<td class=legend>{lines}:</td>
			<td>" . Field_text('lines',$_GET["lines"],'width:90px')."</td>
			<td><input type='button' OnClick=\"javascript:ChangeViewLogsDateDar();\" value='{change}&nbsp;&raquo;'></td>
		</tr>
	</table>
		
	<div style='width:100%;height:300px;overflow:auto'>

	";

	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html)."$table</div>";
	
	
}

function homes_excludes(){
	$tpl=new templates();
	$page=CurrentPageName();	
	$dar=new dar_back();
	
	if(is_array($dar->homes_shares)){
	$table="
	<form name='FFMSMBEXC'>
	<input type='hidden' id='ExcludeHomesFolder' value='yes' name='ExcludeHomesFolder'>
	
	<table style='width:45%' class=table_form>";
	
	while (list ($num, $line) = each ($dar->homes_shares) ){
		if($dar->exclude_home_shares[$num]<>null){
			$value=1;
		}else{
			$value=0;
		}
		
		
		$table=$table. 
		"<tr " . CellRollOver().">
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td class=legend>$line</td>
			<td width=1%>" . Field_checkbox($num,1,$value)."</td>
		</tr>
			";
		
		$hash[$line]=true;
		
	}
	
	
	$table=$table."</table>";
	}
	
	$html="<h1>{homes_folder_exclude}</H1>
	<p class=caption>{shares_folder_exclude_text}</p>
	<div style='width:100%;text-align:right'><input type='button' OnClick=\"ParseForm('FFMSMBEXC','$page',true)\" value='{edit}&nbsp;&raquo;'></div>
	<center>
	<div style='width:100%;height:350px;overflow:auto'>$table</div></center>
	<div style='width:100%;text-align:right'><input type='button' OnClick=\"ParseForm('FFMSMBEXC','$page',true)\" value='{edit}&nbsp;&raquo;'></div>
	";
	echo $tpl->_ENGINE_parse_body($html);	
	
}


function samba_excludes_shared(){
	$tpl=new templates();
	$page=CurrentPageName();	
	$dar=new dar_back();
	
	if(is_array($dar->smb_shares)){
	$table="
	<form name='FFMSMBEXC'>
	<input type='hidden' id='ExcludeSMBFolder' value='yes' name='ExcludeSMBFolder'>
	
	<table style='width:85%' class=table_form>";
	
	while (list ($num, $line) = each ($dar->smb_shares) ){
		if($dar->exclude_smb_share[$num]<>null){
			$value=1;
		}else{
			$value=0;
		}
		
		
		$table=$table. 
		"<tr " . CellRollOver().">
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td class=legend>$line</td>
			<td width=1%>" . Field_checkbox($num,1,$value)."</td>
		</tr>
			";
		
		$hash[$line]=true;
		
	}
	
	
	$table=$table."</table>";
	}
	
	$html="<h1>{shares_folder_exclude}</H1>
	<p class=caption>{shares_folder_exclude_text}</p>
	<div style='width:100%;text-align:right'><input type='button' OnClick=\"ParseForm('FFMSMBEXC','$page',true)\" value='{edit}&nbsp;&raquo;'></div>
	<center>
	<div style='width:100%;height:350px;overflow:auto'>$table</div></center>
	<div style='width:100%;text-align:right'><input type='button' OnClick=\"ParseForm('FFMSMBEXC','$page',true)\" value='{edit}&nbsp;&raquo;'></div>
	";
	echo $tpl->_ENGINE_parse_body($html);
	}
	
	
function user_defined_save(){
	$path=$_GET["AddDarPersoFolder"];
	$dar=new dar_back();
	$dar->perso_shares[md5($path)]=$path;
	$dar->Save();
	}

function user_defined_del(){
	$path_md5=$_GET["DelDarPersoFolder"];
	$dar=new dar_back();
	unset($dar->perso_shares[$path_md5]);
	$dar->Save();
	}
	
function user_defined(){
	
$tpl=new templates();
	$page=CurrentPageName();	
	$dar=new dar_back();
	$list=user_defined_list();
	
$html="<p class=caption>{user_defined_text}</p>
<table style='width:100%' class=table_form>
<tr>
	<td class=legend>{folder}:</td>
	<td>" . Field_text('dar_perso_folder',null,'width:255px')."&nbsp;". button_browse("dar_perso_folder")."</td>
	<td width=1%><input type='button' OnClick=\"javascript:AddDarPersoFolder();\" value='{add}&nbsp;&raquo;'></td>
</tr>
</table>

<table style='width:100%' class=table_form>
<tr>
	<td valign='top'>$list</td>
	<td valign='top' width=1%>" .Paragraphe('64-new-folder.png',"{add_new_directory}","{add_backup_folder}",
	"javascript:Loadjs('SambaBrowse.php?no-shares=yes&field=dar_perso_folder');")."</td>
</tr>
</table>

";	
	

return  $tpl->_ENGINE_parse_body($html);
	
}

function user_defined_list(){
	
	$dar=new dar_back();
	$dar->ParsePersoShares();
	
	if(!is_array($dar->perso_shares)){return null;}
	$html="<table style='width:99%' class=table_form>";
	while (list ($num, $line) = each ($dar->perso_shares) ){
		if(strlen($line)>20){
			$path=texttooltip(substr($line,0,17)."...",$line);
		}else{$path=$line;}
		
		$arr=$dar->main_array["USER_SCHEDULES"][$num];
		if($arr<>null){
			$newarr=explode(",",$arr);
			$sched="{backup_every}:{$newarr[0]} {minutes}";
		}else{$sched=null;}
		
		$html=$html . "
		<tr " . CellRollOver().">
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td><span style='font-size:12px;font-weight:bold'>$path</span></td>
			<td width=1%>" . imgtootltip('24-connect.png','{schedule}',"SchedulePersoFolder('$num')")."</td>
			<td width=1%>" . imgtootltip('ed_delete.gif',"{delete}","DelDarPersoFolder('$num')")."</td>
		</tr>
		<tr>
			<td colspan=4 class=legend>$sched</td>
		</tr>
		
		";
	}
	
	$html=$html . "</table>";
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
}

function user_defined_schedule(){
	$id=$_GET["user-defined-schedule"];
	$page=CurrentPageName();
	$dar=new dar_back();
	$path=$dar->perso_shares[$id];
	$sch[0]="00";
	for($i=5;$i<60;$i++){
		if($i<10){$mins="0$i";}else{$mins=$i;}
		$sch[$i]=$mins;
	}
	
	$default=explode(",",$dar->main_array["USER_SCHEDULES"][$id]);
	
	$html="<h1>{schedule}</H1>
	<H3>$path</H3>
	<p class=caption>{user_defined_schedule_explain}</p>
	<form name='FFMUSCH'>
	<input type='hidden' name='user-defined-schedule-save' value='$id'>
	<table style='width:100%' class=table_form>
	<tr>
		<td class=legend>{backup_every}:</td>
		<td>" . Field_array_Hash($sch,'user-defined-schedule-min',$default[0])."&nbsp;{minutes}</td>
	</tr>
	<tr>
		<td colspan=2 align='right'><input type='button' OnClick=\"javascript:ParseForm('FFMUSCH','$page',true)\" value='{edit}&nbsp;'>
	</table>
	</form>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function user_defined_schedule_save(){
	$tpl=new templates();
	$id=$_GET["user-defined-schedule-save"];
	$schedule_time=$_GET["user-defined-schedule-min"];
	$dar=new dar_back();
	if($schedule_time<1){
		unset($dar->main_array["USER_SCHEDULES"][$id]);
		$dar->Save();
		echo $tpl->_ENGINE_parse_body("{success} {schedule} {delete}");
		exit;
	}
	
	
	$schedule=$schedule_time;
	$arr[]=$schedule_time;
	while ($i<59) {
		$schedule=$schedule+$schedule_time;
		if($schedule>59){break;}
		$arr[]=$schedule;
	}

	
	$implode=implode(",",$arr);
	$dar->main_array["USER_SCHEDULES"][$id]=$implode;
	$dar->Save();
	
	echo $tpl->_ENGINE_parse_body("{success}\n{backup_every}:\n$implode {minutes}");
}




	
function samba_excludes_shared_save(){
	$dar=new dar_back();
	
	unset($_GET["ExcludeSMBFolder"]);
	unset($dar->exclude_smb_share);
	while (list ($num, $line) = each ($_GET) ){
		if($line==1){
			writelogs("$num {$dar->smb_shares[$num]} will be excluded from backup",__FUNCTION__,__FILE__);
			$dar->exclude_smb_share[$num]=$dar->smb_shares[$num];
		}
		
	}
	
	$dar->Save();
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');
	
}

function homes_excludes_save(){
	$dar=new dar_back();
	
	unset($_GET["ExcludeHomesFolder"]);
	unset($dar->exclude_home_shares);
	while (list ($num, $line) = each ($_GET) ){
		if($line==1){
			writelogs("$num {$dar->homes_shares[$num]} will be excluded from backup",__FUNCTION__,__FILE__);
			$dar->exclude_home_shares[$num]=$dar->homes_shares[$num];
		}
		
	}
	
	$dar->Save();
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body('{success}');	
	
}

function dar_rebuild(){
	$sock=new sockets();
	$sock->getfile('darRebuild');
	
}

function populate(){
	$sock=new sockets();
	$sock->getfile("DarPopulate:{$_GET["populate"]}");
}



class xml2Array {
   
    var $arrOutput = array();
    var $resParser;
    var $strXmlData;
   
    function parse($strInputXML) {
   
            $this->resParser = xml_parser_create ();
            xml_set_object($this->resParser,$this);
            xml_set_element_handler($this->resParser, "tagOpen", "tagClosed");
           
            xml_set_character_data_handler($this->resParser, "tagData");
       
            $this->strXmlData = xml_parse($this->resParser,$strInputXML );
            if(!$this->strXmlData) {
               die(sprintf("XML error: %s at line %d",
            xml_error_string(xml_get_error_code($this->resParser)),
            xml_get_current_line_number($this->resParser)));
            }
                           
            xml_parser_free($this->resParser);
           
            return $this->arrOutput;
    }
    function tagOpen($parser, $name, $attrs) {
       $tag=array("name"=>$name,"attrs"=>$attrs);
       array_push($this->arrOutput,$tag);
    }
   
    function tagData($parser, $tagData) {
       if(trim($tagData)) {
            if(isset($this->arrOutput[count($this->arrOutput)-1]['tagData'])) {
                $this->arrOutput[count($this->arrOutput)-1]['tagData'] .= $tagData;
            }
            else {
                $this->arrOutput[count($this->arrOutput)-1]['tagData'] = $tagData;
            }
       }
    }
   
    function tagClosed($parser, $name) {
       $this->arrOutput[count($this->arrOutput)-2]['children'][] = $this->arrOutput[count($this->arrOutput)-1];
       array_pop($this->arrOutput);
    }
}




?>