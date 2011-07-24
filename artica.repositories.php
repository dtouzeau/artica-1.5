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
	$usersmenus=new usersMenus();
	if($usersmenus->AsArticaAdministrator==false){header('location:users.index.php');exit;}	
	if(isset($_GET["main"])){main_switch();exit;}
	if(isset($_GET["status"])){main_status();exit;}
	if(isset($_GET["CheckEveryMinutes"])){SaveConf();exit;}
	if(isset($_GET["ArticaUpdateInstallPackage"])){ArticaUpdateInstallPackage();exit;}
	if(isset($_GET["auto_update_perform"])){auto_update_perform();exit;}
	if(isset($_GET["PackageUninstall"])){uninstall_package();exit;}
	if(isset($_GET["PackageUninstallRoolback"])){uninstall_rollback_package();exit;}
	if(isset($_GET["apply_uninstall"])){uninstall_to_system();exit;}
	if(isset($_GET["PackageInstall"])){install_package();exit;}
	if(isset($_GET["PackageInstallRoolback"])){install_rollback_package();exit;}
	if(isset($_GET["DeleteEvent"])){main_event_delete();exit;}
	if(isset($_GET["apply_upgrade"])){upgrade_to_system();exit;}
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["tabs"])){tabs();exit;}
	//main_page();
	
	main_js();
	
	
function main_js(){
	$page=CurrentPageName();
	$prefix=str_replace('.',"_",$page);
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{repositories_manager}');
	
	$default="LoadAjax('main_configapt','$page?main={$_GET["main"]}&hostname=$hostname')";
	if(isset($_GET["show"])){
		$default="LoadAjax('main_configapt','$page?main={$_GET["show"]}&hostname=$hostname')";
	}
	
$html="
var {$prefix}timerID  = null;
var {$prefix}timerID1  = null;
var {$prefix}tant=0;
var {$prefix}reste=0;
var {$prefix}timeout=0;


function LoadAptPage(){
	YahooWinT(750,'$page?tabs=yes','$title');
	setTimeout(\"initapt()\",1000);
}

function initapt(){
	if(!document.getElementById('main_configapt')){
		if({$prefix}timeout<5){
			{$prefix}timeout={$prefix}timeout+1;
			setTimeout(\"initapt()\",1000);
			return false;
		}
	}
	

	{$prefix}ChargeLogs();
	$default;
	
}





var x_refresh_events= function (obj) {
	LoadAjax('main_configapt','artica.repositories.php?main=events&tab=events&hostname=')
}

var x_refresh_packages= function (obj) {

	if(document.getElementById('p_queries')){
		document.getElementById('search_p').value=document.getElementById('p_queries').value;
		SearchPackage();
		return false;
	}
	SearchPackage();
	
}




function PackageInfos(p){
	YahooWin(700,'$page?main=package_info&pname='+ p);
}

function DeleteEvent(id){
	var XHR = new XHRConnection();
	XHR.appendData('DeleteEvent',id);
	XHR.sendAndLoad('$page', 'GET',x_refresh_events);
	

}

function ShowEvent(id){
	YahooWin(700,'$page?main=event&id='+ id);
}

function PackageInfosInstall(p){
	YahooWin(700,'$page?main=package_info&pname='+ p+'&install=yes');
}


function apply_uninstall(){
	if(confirm(document.getElementById('apply_uninstall_help').value)){
		var XHR = new XHRConnection();
		XHR.appendData('apply_uninstall','yes');
		XHR.sendAndLoad('$page', 'GET',x_refresh_events);
	}

}

function apply_upgrade(){
	if(confirm(document.getElementById('apply_upgrade_help').value)){
		var XHR = new XHRConnection();
		XHR.appendData('apply_upgrade','yes');
		XHR.sendAndLoad('$page', 'GET',x_refresh_events);
	}

}

function PackageUninstall(p){
	var XHR = new XHRConnection();
	XHR.appendData('PackageUninstall',p);
	XHR.sendAndLoad('$page', 'GET',x_refresh_packages);
	PackageInfos(p)
}

function PackageInstall(p){
	var XHR = new XHRConnection();
	XHR.appendData('PackageInstall',p);
	XHR.sendAndLoad('$page', 'GET',x_refresh_packages);
	PackageInfosInstall(p);
}

function PackageUninstallRoolback(p){
	var XHR = new XHRConnection();
	XHR.appendData('PackageUninstallRoolback',p);
	XHR.sendAndLoad('$page', 'GET',x_refresh_packages);
	PackageInfos(p);
}


function PackageInstallRoolback(p){
	var XHR = new XHRConnection();
	XHR.appendData('PackageInstallRoolback',p);
	XHR.sendAndLoad('$page', 'GET',x_refresh_packages);
	PackageInfos(p);
}


function SearchPackageEnter(e){
	if(checkEnter(e)){SearchPackage();}
}
function SearchPackage(){
	var tab='';
	var q=document.getElementById('search_p').value;
	if(document.getElementById('switch')){tab=document.getElementById('switch').value;}
	var err=document.getElementById('more_one_car').value;
	document.getElementById('error').innerHTML='';
	LoadAjax('package_list','$page?main=list_packages&query='+q+'&tab='+tab);
	LoadAjax('package_list_install','$page?main=list_install_packages&query='+q+'&tab='+tab);
}


function {$prefix}demarre(){
		{$prefix}tant = {$prefix}tant+1;
		{$prefix}reste=10-{$prefix}tant;
	if ({$prefix}tant < 10 ) {                           
		{$prefix}timerID = setTimeout(\"demarre()\",3000);
      } else {
		{$prefix}tant = 0;
		               //document.getElementById('wait').innerHTML='<img src=img/wait.gif>';
		{$prefix}ChargeLogs();
		{$prefix}demarre();                                //la boucle demarre !
   }
}


function {$prefix}ChargeLogs(){
	LoadAjax('servinfosAPT','$page?status=yes&hostname={$_GET["hostname"]}');
	}
	

LoadAptPage();
	
";
	
echo $html;	
	
}

function tabs(){
	$page=CurrentPageName();
	$array["config"]='{packages_list}';
	$array["uninstall_list"]='{uninstall_packages_list}';
	$array["events"]='{events}';
	$array["update"]='{system_update}';

	$page=CurrentPageName();
	$tpl=new templates();
	while (list ($num, $ligne) = each ($array) ){
		
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?main=$num&tab=$num&hostname={$_GET["hostname"]}\"><span>$ligne</span></a></li>\n");
		}
	
	
echo "
	<div id=admin_apt_settings style='width:99%;height:auto;'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#admin_apt_settings').tabs();
			
			
			});
		</script>";		
	
	
}

	
function popup(){
	
	
	
$html="
<td valign='top'><div class='explain'>{repositories_manager_explain}</p>
<div id='servinfosAPT'></div>
<div id='main_configapt'></div>
";
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
		

}


function packages_status(){
	$q=new mysql();
	$q->BuildTables();
	
	$count=$q->COUNT_ROWS("debian_packages","artica_backup");
	
	$html="<table style='width:100%'>
	<tr>
	<td width=1%><img src='img/icon_info.gif'></td>
		<td nowrap width=1% nowrap><strong>{num_packages_installed}:</strong></td>
		<td nowrap align='left'><strong style='color:red'>$count</strong></td>
	</tr>
	</table>
	";
	
	$tpl=new templates();
	
	$sock=new sockets();
	$datas=trim(@file_get_contents("/etc/artica-postfix/apt.upgrade.cache"));
	if(preg_match('#nb:([0-9]+)\s+#is',$datas,$re)){
		$services=$tpl->_ENGINE_parse_body(Paragraphe('i32.png',"{upgrade_your_system}","{$re[1]}&nbsp;{packages_to_upgrade}","javascript:Loadjs('artica.repositories.php?show=update')"));
		$services="<br>".$services;
	}	
	
	echo RoundedLightWhite($tpl->_ENGINE_parse_body("$html$services"));
	}


function main_configapt(){
	$artica=new artica_general();
	$page=CurrentPageName();
	
	
	$form="
	<input type='hidden' id='more_one_car' value='{more_one_car}'>
	<table style='width:100%' class=form>
	
	<tr>
		<td width=1% nowrap' align='right'><strong>{packages_list}</strong>:</td>
		<td>" . Field_text('search_p',null,'font-size:14px;width:95%x',null,null,null,false,"SearchPackageEnter(event)")."</td>
		<td nowrap><span id='error' style='color:red'></span></d>
		<td>". button("{search}","SearchPackage()")."</td>
	</tr>	
	</table>";
	
	

	$html="$form<br>
	
	
	
	<div id='package_list'></div>
	<div id='package_list_install'></div>
	
	<script>
		SearchPackage();
	</script>
	";
	
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
	
	
}



function main_package_uninstall(){
$html="
<table style='width:100%'>
<tr>
<td style='width:100%' valign='top'>
	<H5>{uninstall_packages_list}</h5>
	<input type='hidden' id='search_p' value=''>
</td>
<td width=1%>" . uninstall_Apply_uninstall()."</td>
</tr>
</table>

<div id='package_list'>".main_package_list()."</div>";
	
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
		
	
}


function main_package_list(){
	
	$q=new mysql();
	
	
	if(strlen($_GET["query"])>1){
		$que="WHERE (package_name LIKE '%{$_GET["query"]}%') OR (package_info LIKE '%{$_GET["query"]}%') OR (package_description LIKE '%{$_GET["query"]}%')";
	}
	
	if($_GET["tab"]=="uninstall_list"){
		$que=null;
		$que1=" WHERE (package_status='a-uu') OR (package_status='a-ii')";
	}
	
	$sql="SELECT * FROM `debian_packages` $que $que1 LIMIT 0 , 50";
	$results=$q->QUERY_SQL($sql,'artica_backup');

	
	$html="
	<input type='hidden' id='p_queries' value='{$_GET["query"]}'>
	<input type='hidden' id='switch' value='{$_GET["tab"]}'>
	<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		
		<th colspan=4>{installed_on_system}&nbsp;|&nbsp;{$_GET["query"]}</th>
	</tr>
</thead>
<tbody class='tbody'>";
	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		
			$r=$ligne["package_status"];
			$uri="<a href='#' OnClick=\"javascript:PackageInfos('{$ligne["package_name"]}')\" style='font-size:14px'>";
			switch ($r) {
				case 'rc':$img=imgtootltip('icon_mini_off.gif','{removed_package}');$uri=null;break;
				case "ii":$img=imgtootltip('icon-mini-ok.gif','{installed_package}');break;
				case "a-uu":$img=imgtootltip('icon_mini_read_off.gif','{to_be_uninstalled}');break;
				case "a-ii":$img=imgtootltip('icon_mini_info.jpg','{to_be_installed}');break;
				default:
					break;
			}
			
			
			
			
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$html=$html."
			<tr class=$classtr>
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td width=1%>$img</td>
			<td width=1% nowrap><strong>$uri{$ligne["package_name"]}</strong></a></td>
			<td width=99% nowrap><strong>$uri{$ligne["package_info"]}</strong></a></td>
			</tr>
			";
	}

	$html=$html . "</table>";
	$html="
	
	<div style='overflow-y:auto;width:100%;height:210px'>$html</div>";
	$tpl=new templates();
	return  RoundedLightWhite($tpl->_ENGINE_parse_body($html));
	
}


function main_status(){
	echo packages_status();
	
}


function main_switch(){
	
	switch ($_GET["main"]) {
		case "config":echo main_configapt();exit;break;
		case "events":echo main_events();exit;break;
		case "list":echo main_updatelist();exit;break;
		case "list_packages":echo main_package_list();exit;break;
		case "package_info":echo main_info_package();exit;break;
		case "uninstall_list":echo main_package_uninstall();exit;break;
		case "list_install_packages":echo main_package_install();exit;break;
		case "event":echo main_event_zoom();exit;break;
		case "update":echo main_update();exit;break;
		default:echo main_configapt();exit;break;
	}
	
}



function SaveConf(){
	$artica=new artica_general();
	
	while (list ($num, $ligne) = each ($_GET) ){
		$artica->ArticaAutoUpdateConfig[$num]=$ligne;
	}
	
	$artica->SaveAutoUpdateConfig();
	
}


	
function main_info_package(){
	$pname=$_GET["pname"];
	$installed=IsPackageInstalled($pname);
$sql="SELECT package_status,package_description FROM debian_packages WHERE package_name='$pname'";
		$q=new mysql();
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
		$package_status=$ligne["package_status"];
	
	if($installed){
		
		$datas=$ligne["package_description"];
		
	}else{
		$sock=new sockets();
		$datas=$sock->getfile("reposinfo:$pname");
		
	}
	
	$tbl=explode("\n",$datas);
	$html="<table style='width:100%'>";
	
	while (list ($num, $ligne) = each ($tbl) ){
		if(trim($ligne)==null){continue;}
		$html=$html."<tr>";
		if(preg_match('#^(.+?):(.+)#',$ligne,$re)){
			if($re[1]=="SHA256"){continue;}
			if($re[1]=="SHA1"){continue;}
			if($re[1]=="MD5sum"){continue;}
			
			
			
			$html=$html."<td nowrap valign='top' align='right'><strong style='font-size:11px'>{$re[1]}</strong>:</td>
			<td>{$re[2]}</td>";
		}else{
			$html=$html . "<td>&nbsp;</td><td>$ligne</td>";
			
		}
		$html=$html . "</tr>";
		
	}
	
	$html=$html . "</table>";
	
	if($installed){
	$delete=RoundedLightWhite(Paragraphe('folder-delete-64.png','{uninstall}','{uninstall_text}',"javascript:PackageUninstall(\"$pname\");","uninstall"));
	if($package_status=="a-uu"){
		$delete=RoundedLightWhite(Paragraphe('folder-quarantine.png','{rollback_uninstall}','{rollback_uninstall_text}',"javascript:PackageUninstallRoolback(\"$pname\");","uninstall"));
	}
	}else{
		$delete=RoundedLightWhite(Paragraphe('64-folder-install.png','{install}','{install_text}',"javascript:PackageInstall(\"$pname\");","install"));
		if($package_status=="a-ii"){
			$delete=RoundedLightWhite(Paragraphe('folder-quarantine.png','{rollback_install}','{rollback_install_text}',"javascript:PackageInstallRoolback(\"$pname\");","install"));
		}
	}
	
	
	$html="<H1>$pname</h1><br>
	
	<table style='width:100%'>
	<tr>
	<td valign='top' width=99%>
		".RoundedLightWhite("
		<div style='overflow-y:auto;width:100%;height:300px'>$html</div>")."
	</td>
	<td valign='top' width=1%>
	$delete
	
	</td>
	</tr>
	</table>";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function uninstall_package(){
	$pname=$_GET["PackageUninstall"];
	writelogs("Mark $pname has uninstalled package...",__FUNCTION__,__FILE__);
	$sql="UPDATE debian_packages SET package_status='a-uu' WHERE package_name='$pname'";
	$q=new mysql();
	$q->QUERY_SQL($sql,'artica_backup');
	}
	
function install_package(){
	$pname=$_GET["PackageInstall"];
	
	if(strlen(PackageSTATUS($pname))>0){
		$sql="UPDATE debian_packages SET package_status='a-ii' WHERE package_name='$pname'";
	}else{
		$sql="INSERT INTO debian_packages (package_status,package_name) VALUES('a-ii','$pname')";
	}
	writelogs("Mark $pname has install package...",__FUNCTION__,__FILE__);
	
	$q=new mysql();
	$q->QUERY_SQL($sql,'artica_backup');	
}
	
function uninstall_rollback_package(){
	$pname=$_GET["PackageUninstallRoolback"];
	writelogs("Mark $pname has installed package...",__FUNCTION__,__FILE__);
	$sql="UPDATE debian_packages SET package_status='ii' WHERE package_name='$pname'";
	$q=new mysql();
	$q->QUERY_SQL($sql,'artica_backup');	
	
}

function install_rollback_package(){
	$pname=$_GET["PackageInstallRoolback"];
	$sql="DELETE FROM debian_packages  WHERE package_name='$pname' AND package_status='a-ii' ";
	$q=new mysql();
	$q->QUERY_SQL($sql,'artica_backup');		
	
}

function uninstall_Apply_uninstall(){
$tpl=new templates();
return $tpl->_ENGINE_parse_body( 
"<input type='hidden'  value='{apply_uninstall_help}' id='apply_uninstall_help'>".
Paragraphe('system-64.png','{apply_uninstall}','{apply_uninstall_text}','javascript:apply_uninstall()','apply_uninstall_text'));
}

function update_Apply_install(){
$tpl=new templates();
return $tpl->_ENGINE_parse_body( 
"<input type='hidden'  value='{apply_upgrade_help}' id='apply_upgrade_help'>".
Paragraphe('system-64.png','{apply_upgrade}','{apply_upgrade_text}','javascript:apply_upgrade()','apply_upgrade_text'));
}

function uninstall_to_system(){
	$sock=new sockets();
	$sock->getfile('reposuninstall');
	
}

function main_package_install(){
	$sock=new sockets();
	$date=date('Y-m-d h:i');
	
	$tmpfile=dirname(__FILE__).'/ressources/logs/repos.query.'.md5($_GET["query"].$date).".cache";
	
	if(file_exists($tmpfile)){
		writelogs("return content of $tmpfile",__FUNCTION__,__FILE__);
		return file_get_contents($tmpfile);
	}
	
	writelogs("apt-cache query {$_GET["query"]}",__FUNCTION__,__FILE__);
	$datas=$sock->getfile('reposfind:'.$_GET["query"]);
	$tbl=explode("\n",$datas);
	writelogs("query {$_GET["query"]} result=".count($tbl),__FUNCTION__,__FILE__);
$html="<table style='width:100%'>";
	
	while (list ($num, $ligne) = each ($tbl) ){
		if(preg_match('#(.+?)\s+-\s+(.+)#',$ligne,$re)){
			$count=$count+1;
			$uri="<a href='#' OnClick=\"javascript:PackageInfosInstall('{$re[1]}')\">";
			$html=$html."
			<tr ". CellRollOver_jaune().">
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td width=1%><img src='img/icon-mini-ok.gif'></td>
			<td width=1% nowrap><strong>$uri{$re[1]}</strong></a></td>
			<td width=99% nowrap><strong>$uri{$re[2]}...</strong></a></td>
			</tr>";
		}
		
		
	}
	
	$html=$html . "</table>";
	
	
	$html="
	<H5>{to_install_on_system}</h5>
	<div style='overflow-y:auto;width:100%;height:210px'>$html</div>";
	$tpl=new templates();
	$w="<br>".RoundedLightWhite($tpl->_ENGINE_parse_body($html));
	
	if($count>0){
		writelogs("writing new file $tmpfile",__FUNCTION__,__FILE__);
		$f = @fopen($tmpfile, 'w');
    	@fwrite($f, $w);
   		@fclose($f);
	}
   		return $w;

}

function IsPackageInstalled($pname){
	$sql="SELECT package_status FROM debian_packages WHERE package_name='$pname'";
	$q=new mysql();
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	writelogs("$pname={$ligne["package_status"]}",__FUNCTION__,__FILE__);
	if($ligne["package_status"]=='ii'){return true;}
	return false;
	}
	
function PackageSTATUS($pname){
	$sql="SELECT package_status FROM debian_packages WHERE package_name='$pname'";
	$q=new mysql();
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	return $ligne["package_status"];
	}	
	
function main_events(){
	
	$html="<H5>{events}</H5>
	<div id='events'>" . main_events_list()."</div>";
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
	
	
}

function main_event_delete(){
	$id=$_GET["DeleteEvent"];
	$sql="DELETE FROM debian_packages_logs WHERE ID='$id'";
	$q=new mysql();
	$q->QUERY_SQL($sql,'artica_backup');
	
}

function main_event_zoom(){
	$id=$_GET["id"];
	$sql="SELECT * FROM debian_packages_logs WHERE ID='$id'";
	$q=new mysql();
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	
	$ev=$ligne["events"];
	$html="<H5>{$ligne["zDate"]}:: {$ligne["package_name"]}</H5>
		<textarea style='border:1px dotted #CCCCCC;width:100%;height:300px'>$ev</textarea>
	
	";
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
	
}
	
function main_events_list(){
	
	$sql="SELECT * FROM debian_packages_logs ORDER BY zDate DESC";
	$q=new mysql();
$results=$q->QUERY_SQL($sql,'artica_backup');

	
	$html="<div style='width:100%;height:100px;overflow:auto'><table style='width:100%'>";
	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
		$ligne["events"]=substr($ligne["events"],0,50);
		
		$uri="<a href='#' OnClick=\"javascript:ShowEvent({$ligne["ID"]});\">";
		
		$html=$html."
			<tr ". CellRollOver_jaune().">
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td width=1% nowrap>$uri{$ligne["install_type"]}</a></td>
			<td width=1% nowrap>$uri{$ligne["zDate"]}</a></td>
			<td width=1% nowrap><strong>$uri{$ligne["package_name"]}</strong></a></td>
			<td width=99% nowrap><strong>$uri{$ligne["events"]}...</strong></a></td>
			<td width=1% nowrap>".imgtootltip('ed_delete.gif','{delete} {events}',"DeleteEvent({$ligne["ID"]})")."</td>
			</tr>
			";
		
	}
	
	$html=$html . "</table></div>";
	$tpl=new templates();
	return  RoundedLightWhite($tpl->_ENGINE_parse_body($html))."<br><h3>Artica {events}</h3>".main_events_artica_events();	
	
}

function main_events_artica_events(){
	
	$sock=new sockets();
	$datas=$sock->getfile('aptarticaevents');
	$tbl=explode("\n",$datas);
	if(!is_array($tbl)){return null;}

	
	$tbl=array_reverse($tbl);
	$t="<table style='width:100%'>";
	
	
	while (list ($num, $ligne) = each ($tbl) ){
		if(trim($ligne)==null){continue;}
			$ligne=htmlspecialchars($ligne);
			$t=$t."
			<tr ". CellRollOver_jaune().">
			<td width=1% valign='top'><img src='img/fw_bold.gif'></td>
			<td width=99%><code>$ligne</code></a></td>
			</tr>";
		
		
	}
	
	$t="<div style='width:100%;height:200px;overflow:auto'>$t</table></div>";
	$t=RoundedLightWhite($t);
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body("$t");	
}

function main_update(){
	$sock=new sockets();
	$datas=trim($sock->getFrameWork('cmd.php?aptcheck=yes'));
	$html="<table style='width:100%'>
	<tr>
	<td valign='top' width=99%>";
	
	if(preg_match('#nb:([0-9]+)\s+#is',$datas,$re)){
		$html=$html . RoundedLightYellow("
		<table style='width:100%'>
		<tr>
			<td valign='top' width=1%><img src='img/48-infos.png'></td>
			<td valign='top'>
				<p style='font-size:12px;font-weight:bold'>{$re[1]}&nbsp;{packages_to_upgrade}</p>
			</td>
		</tr>
		</table>");
		$datas=str_replace($re[0],'',$datas);
	}
	
	$html=$html."</td>
	<td valign='top'>".update_Apply_install()."</td>
	</tr>
	</table>";
	
	$tbl=explode("\n",$datas);
	$t="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:99%'>
<thead class='thead'>
	<tr>
		<th colspan=2>{packages_to_upgrade}&nbsp;|&nbsp;$search_regex&nbsp;|&nbsp;$search_sql</th>
	</tr>
</thead>
<tbody class='tbody'>";
	
	
	while (list ($num, $ligne) = each ($tbl) ){
		if(trim($ligne)<>null){
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$uri="<a href='#' OnClick=\"javascript:PackageInfos('$ligne')\" style='font-size:16px;text-decoration:underline'>";
			$t=$t."
			<tr class=$classtr>
			<td width=1%><img src='img/software-task-48.png'></td>
			<td width=99% nowrap>$uri$ligne</a></td>
			</tr>";
		}
		
	}
	
	$t=$t . "</table>";
	$t="<div style='width:100%;height:300px;overflow:auto'>$t</div>";
	$t=RoundedLightWhite($t);
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body("$html$t");
	
	
}


function upgrade_to_system(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?aptupgrade=yes");
	
	
}
	
	
?>	