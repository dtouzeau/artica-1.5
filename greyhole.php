<?php
if(isset($_GET["verbose"])){$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.tcpip.inc');
include_once(dirname(__FILE__).'/ressources/class.system.network.inc');
include_once(dirname(__FILE__).'/ressources/class.computers.inc');
include_once(dirname(__FILE__).'/ressources/class.autofs.inc');
include_once(dirname(__FILE__).'/ressources/class.samba.inc');

if(posix_getuid()<>0){
	$users=new usersMenus();
	if((!$users->AsSambaAdministrator) OR (!$users->AsSystemAdministrator)){
		$tpl=new templates();
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}
}

if(isset($_GET["tabs"])){tabs();exit;}
if(isset($_GET["status"])){status();exit;}
if(isset($_GET["greyhole-status"])){status_service();exit;}
if(isset($_GET["pools"])){pools();exit;}
if(isset($_GET["pools-list"])){pools_list();exit;}
if(isset($_GET["greyhole-pools"])){pools_add();exit;}
if(isset($_GET["logs"])){mounts_events();exit;}
if(isset($_GET["syslog-table"])){mounts_events_query();exit;}
if(isset($_GET["EnableGreyHoleDebug"])){EnableGreyHoleDebugSave();exit;}



js();

function js(){
		$page=CurrentPageName();
		$html="
		
		
		function GreyHoleConfigLoad(){
			$('#BodyContent').load('$page?tabs=yes');
			}
			
		GreyHoleConfigLoad();
		";
		echo $html;
		
	
}
function status_service(){
	$sock=new sockets();
	$tpl=new templates();
	$ini=new Bs_IniHandler();
	$page=CurrentPageName();	
	$ini->loadString(base64_decode($sock->getFrameWork("cmd.php?greyhole-ini-status=yes")));
	$status=DAEMON_STATUS_ROUND("APP_GREYHOLE",$ini,null,0);
	echo $tpl->_ENGINE_parse_body($status);		
	
}


function tabs(){
	
	
	$page=CurrentPageName();
	$tpl=new templates();
	$array["status"]='{status}';
	$array["pools"]='{storage_pool}';
	$array["shared_folders"]='{shared_folders}';
	$array["logs"]='{events}';
	
	
	

	while (list ($num, $ligne) = each ($array) ){
		
		if($num=="shared_folders"){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"samba.index.php?SharedFoldersList=yes\"><span>$ligne</span></a></li>\n");
			continue;
		}
		
		
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_greyhole style='width:100%;height:750px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_config_greyhole\").tabs();});
		</script>";		
		
	
}

function pools(){
	$page=CurrentPageName();
	$tpl=new templates();
	$autofs=new autofs();
	$hash=$autofs->automounts_Browse();
	
	while (list ($foldername, $ligne) = each ($hash) ){
		$array[$ligne["DN"]]="$foldername ({$ligne["SRC"]})";
	}
	
	$array[null]="{select}";
	$html="
	<table style='width:100%' class=form>
	<tr>
		<td class=legend style='font-size:14px' WIDTH=1% nowrap>{add_storage}:</td>
		<td>". Field_array_Hash($array,"pool_to_add",null,"style:font-size:14px;padding:3px")."</td>
		<td class=legend style='font-size:14px' width=1%>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend style='font-size:14px' WIDTH=1% nowrap>{maxsize} (Gb):</td>
		<td>". Field_text("free_g",2,"font-size:14px;padding:3px;width:60px")."</td>
		<td class=legend style='font-size:14px' width=1%>". button("{add}","AddGreholePool()")."</td>
	<tr>
		<td colspan=3 align='right'><a href=\"javascript:blur();\" OnClick=\"javascript:Loadjs('autofs.php');\" style='font-size:13px;text-decoration:underline'><i>{add_mount_point_text}</i></a></td>
	</table>
	<hr>
	<div id='greyhole-pools' style='height:225px'></div>
	<script>
	var x_AddGreholePool= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);return;}
		RefreshTab('main_config_greyhole');
	 }	
		
	function AddGreholePool(){
		var XHR = new XHRConnection();
		var p=document.getElementById('pool_to_add').value;
		if(p.length<5){return;}
		XHR.appendData('greyhole-pools',document.getElementById('pool_to_add').value);
		XHR.appendData('free_g',document.getElementById('free_g').value);
		//document.getElementById('greyhole-pools').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';	
		XHR.sendAndLoad('$page', 'GET',x_AddGreholePool);
	}	

	function RefreshPool(){
		LoadAjax('greyhole-pools','$page?pools-list=yes');
	}	
	RefreshPool();
</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
	}
	
function pools_add(){
	
	$q=new mysql();
	$sql="INSERT IGNORE INTO greyhole_spools (dn,free_g) VALUES('{$_GET["greyhole-pools"]}','{$_GET["free_g"]}');";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	
	
}	

function pools_list(){
	$page=CurrentPageName();
	$tpl=new templates();
	$autofs=new autofs();
	$hash=$autofs->automounts_Browse();
	$q=new mysql();
	$sql="SELECT * FROM greyhole_spools ORDER BY free_g DESC";
	$results=$q->QUERY_SQL($sql,"artica_backup");

		if(!$q->ok){
			echo $q->mysql_error;
			return null;
		}
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th>&nbsp;</th>
		<th>{type}</th>
		<th>{local_directory_name}</th>
		<th>{source}</th>
		<th>{size}</th>
		<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";			
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$dn=$ligne["dn"];
		if($GLOBALS["VERBOSE"]){echo "$dn\n";}
		$array=$autofs->hash_by_dn[$dn];
		$FOLDER=$array["FOLDER"];
		$FS=$array["INFOS"]["FS"];
		$SRC=$array["INFOS"]["SRC"];
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$color="black";
		$delete=imgtootltip("delete-32.png","{delete}","AutoFSUSB('$UUID')");
		$html=$html . "
		<tr  class=$classtr>
			<td width=1%><img src='img/usb-32-green.png'></td>
			<td width=1% align='center' nowrap><strong style='font-size:14px'><code style='color:$color'>$FS</code></td>
			<td width=1% align='left'><strong style='font-size:14px'>$FOLDER</strong></td>
			<td width=99% align='left' nowrap><strong style='font-size:14px'>$SRC</strong></td>
			<td width=1% align='left'><strong style='font-size:14px'>{$ligne["free_g"]}Gb</strong></td>
			<td width=1%>$delete</td>
		</tr>";		
		
		
	}
	
	$html=$html."</tbody></table>
	<script>

	var x_AutoFSUSB= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);return;}	
		YahooWin4Hide();
		RefreshTab('main_config_autofs');
	}		
	
	function AutoFSUSB(key){
		var XHR = new XHRConnection();
		XHR.appendData('USB_UUID',key);	
		var localdir=document.getElementById('USB_LOCAL_DIR').value;
		if(localdir.length==0){alert('LOCAL DIR !');return;}
		XHR.appendData('USB_LOCAL_DIR',document.getElementById('USB_LOCAL_DIR').value);
		XHR.sendAndLoad('$page', 'GET',x_AutoFSUSB);
		}	

	</script>	
	
	";
	echo $tpl->_ENGINE_parse_body($html);
	
	
}
	

function status(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();
	
	
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top'>
		<div id='greyhole-status'></div>
		</td>
		<td valign='top' width=99%'>
			<div class=explain>{APP_GREYHOLE_TEXT}</div>
		
	</tr>
	</table>
	<script>
		LoadAjax('greyhole-status','$page?greyhole-status=yes');
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function EnableGreyHoleDebugSave(){
	$sock=new sockets();
	$sock->SET_INFO("EnableGreyHoleDebug",$_GET["EnableGreyHoleDebug"]);
	$sock->getFrameWork("cmd.php?greyhole-restart=yes");
	
}

function mounts_events(){
	$page=CurrentPageName();
	$sock=new sockets();
	$EnableGreyHoleDebug=$sock->GET_INFO("EnableGreyHoleDebug");
	if(!is_numeric($EnableGreyHoleDebug)){$EnableGreyHoleDebug=0;}
	
	$html="
	
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{debug}:</td>
		<td width=1%>". Field_checkbox("EnableGreyHoleDebug",1,$EnableGreyHoleDebug,"EnableGreyHoleDebugSave()")."</td>
	</tr>
	</table>
	
	
	
	<table style='width:100%'>
	<tr>
		<td class=legend valign='middle'>{search}:</td>
		<td>". Field_text("syslog-search",null,"font-size:13px;padding:3px;",null,null,null,false,"SyslogSearchPress(event)")."</td>
		<td align='right' width=1%>". imgtootltip("32-refresh.png","{refresh}","SyslogRefresh()")."</td>
	</tr>
	</table>
	
	<div style='widht:99%;height:600px;overflow:auto;margin:5px' id='syslog-table'></div>
	<script>
		var x_EnableGreyHoleDebugSave= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue);return;}	
		
		}		
	
	
		function EnableGreyHoleDebugSave(){
			var XHR = new XHRConnection();
			if(document.getElementById('EnableGreyHoleDebug').checked){
				XHR.appendData('EnableGreyHoleDebug',1);
			}else{
				XHR.appendData('EnableGreyHoleDebug',0);
			}
			XHR.sendAndLoad('$page', 'GET',x_EnableGreyHoleDebugSave);
		}
	
	
		function SyslogSearchPress(e){
			if(checkEnter(e)){SearchSyslog();}
		}
	
	
		function SearchSyslog(){
			var pat=escape(document.getElementById('syslog-search').value);
			LoadAjax('syslog-table','$page?syslog-table=yes&search='+pat);
		
		}
		
		function SyslogRefresh(){
			SearchSyslog();
		}
	
	SearchSyslog();
	</script>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function mounts_events_query(){
	
	$pattern=base64_encode($_GET["search"]);
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->getFrameWork("cmd.php?syslog-query=$pattern&syslog-path=/var/log/greyhole.log")));
	if(!is_array($array)){return null;}
	
	$html="<table class=TableView>";
	
	while (list ($key, $line) = each ($array) ){
		if($line==null){continue;}
		if($tr=="class=oddrow"){$tr=null;}else{$tr="class=oddrow";}
		
			$html=$html."
			<tr $tr>
			<td><code>$line</cod>
			</tr>
		
		";
		
	}
	
	
	$html=$html."</table>";

	echo $html;
}