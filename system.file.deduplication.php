<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.system.network.inc');
	$usersmenus=new usersMenus();
	if(!$usersmenus->AsArticaAdministrator){
			$tpl=new templates();
			echo "alert('".$tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
			die();
		}
	
		

	if(isset($_GET["popup"])){popup();exit;}	
	if(isset($_GET["install-status"])){popup_install_status();exit;}
	
	if(isset($_GET["index"])){index();exit;}
	if(isset($_GET["settings"])){settings();exit;}
	if(isset($_GET["REPLICATION"])){saveconfig();exit;}
	if(isset($_GET["MAIN_PATH"])){saveconfig();exit;}
	if(isset($_GET["folders"])){folders();exit;}
	if(isset($_GET["folders-add"])){folders_add();exit;}
	if(isset($_GET["folders-list"])){folders_list();exit;}
	if(isset($_GET["folders-delete"])){folders_delete();exit;}
	if(isset($_GET["tree-path"])){tree_js();exit;}
	
	if(isset($_GET["replication"])){replication();exit;}
	
	if(isset($_GET["restart"])){restart_js();exit;}
	if(isset($_GET["restart-popup"])){restart_popup();exit;}
	if(isset($_GET["restart-perform"])){restart_perform();exit;}
	if(isset($_GET["restart-list"])){restart_list();exit;}
	
		
js();

function restart_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$ttle=$tpl->_ENGINE_parse_body("{restart_lessfs_service}");	
	$are_you_sure_to_restart_umount_mount=$tpl->javascript_parse_text("{are_you_sure_to_restart_umount_mount}");
	echo "
	
	if(confirm('$are_you_sure_to_restart_umount_mount')){
		YahooWin5('600','$page?restart-popup=yes','$ttle');
		}";
	
	
}

function restart_popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$prefix="restart_lessfs";
	
	$html="
	<div id='lessfs-restart-div' style='width:100%' height:250px;overflow:auto'></div>
	<script>
	
	var {$prefix}timerID  = null;
	var {$prefix}tant=0;
	var {$prefix}reste=0;

	function {$prefix}demarre(){
		{$prefix}tant = {$prefix}tant+1;
		{$prefix}reste=20-{$prefix}tant;
		if(!YahooWin5Open()){return false;}
		
		if ({$prefix}tant < 10 ) {                           
		{$prefix}timerID =setTimeout(\"{$prefix}demarre()\",3000);
	      } else {
			{$prefix}tant = 0;
			refreshstatus();
			{$prefix}demarre(); 
			                              
	   }
	}

	function {$prefix}Loadpage(){
		var XHR = new XHRConnection();
		XHR.appendData('restart-perform','yes');
		XHR.sendAndLoad(\"$page\", 'GET');
		setTimeout('{$prefix}demarre()',1000);
	}
	function refreshstatus(){
		LoadAjax('lessfs-restart-div','$page?restart-list=yes');
	}
	
	{$prefix}Loadpage();
	refreshstatus();	
	
</script>	
	
	";
	echo $tpl->_ENGINE_parse_body($html);	
}

function restart_list(){
	
	$tr=explode("\n",@file_get_contents("ressources/logs/web/LESS_FS_RESTART"));
	while (list ($eth, $ip) = each ($tr) ){
		if($ip==null){continue;}
		echo "<div style='font-size:13px'><code>$ip</code></div>";
		
	}
}

function restart_perform(){
	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?lessfs-restart=yes");
}


function tree_js(){
	$page=CurrentPageName();
	$path=base64_decode($_GET["tree-path"]);
	$id=$_GET["id"];
	$html="
		var x_AddTreeDedupFolder= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			RefreshFolder('$path','$id');
			
		}	
		
		function AddTreeDedupFolder(){
			var XHR = new XHRConnection();
			XHR.appendData('folders-add','$path');
			document.getElementById('browser-infos').innerHTML=\"<center style='margin:10px'><img src='img/wait_verybig.gif'></center>\";
			XHR.sendAndLoad(\"$page\", 'GET',x_AddTreeDedupFolder);
		}
		
		AddTreeDedupFolder();
		";	
	echo $html;
}





function replication(){
	
$page=CurrentPageName();
	$tpl=new templates();	
	$sock=new sockets();
	$arrayConf=unserialize(base64_decode($sock->GET_INFO("lessfsConf")));
	if(!is_array($arrayConf)){$arrayConf=array();}	
	
	
	if(!is_numeric($arrayConf["DEBUG"])){$arrayConf["DEBUG"]=2;}
	if(!is_numeric($arrayConf["REPLICATION"])){$arrayConf["REPLICATION"]=0;}
	if(!is_numeric($arrayConf["REPLICATION_ENABLED"])){$arrayConf["REPLICATION_ENABLED"]=0;}
	if(!is_numeric($arrayConf["REPLICATION_LISTEN_PORT"])){$arrayConf["REPLICATION_LISTEN_PORT"]=102;}
	if($arrayConf["REPLICATION_LISTEN_IP"]==null){$arrayConf["REPLICATION_LISTEN_IP"]="127.0.0.1";}
	if($arrayConf["REPLICATION_PARTNER_IP"]==null){$arrayConf["REPLICATION_PARTNER_IP"]="127.0.0.1";}
	if(!is_numeric($arrayConf["REPLICATION_PARTNER_PORT"])){$arrayConf["REPLICATION_PARTNER_PORT"]=102;}
	
	$net=new networking();
	while (list ($eth, $ip) = each ($net->array_TCP) ){
		if($ip==null){continue;}
		$REPLICATION_LISTEN_IP_R[$ip]=$ip;
		
	}
	
	
	
	
	$REPLICATION_LISTEN_IP_R=Field_array_Hash($REPLICATION_LISTEN_IP_R,"REPLICATION_LISTEN_IP",$arrayConf["REPLICATION_LISTEN_IP"],
	null,null,0,"font-size:13px;padding:3px");
	$REPLICATION_ROLE_R=array("master"=>"{lessfs_master}","slave"=>"{slave}");
	$REPLICATION_ROLE_R=Field_array_Hash($REPLICATION_ROLE_R,"REPLICATION_ROLE",$arrayConf["REPLICATION_ROLE"],
	"CheckReplicFields()",null,0,"font-size:13px;padding:3px");
	$html="
	<div id='lessfscfg-replic'>
	<table style='width:98%' class=form>
	
	<tr>
		<td class=legend>{enable_replication}:</td>
		<td>". Field_checkbox("REPLICATION",1,$arrayConf["REPLICATION"],"CheckReplicFields()")."</td>
		<td width=1%></td>
	</tr>
	<tr>
		<td class=legend>{REPLICATION_ROLE}:</td>
		<td>$REPLICATION_ROLE_R</td>
		<td width=1%></td>
	</tr>	
	<tr>
		<td class=legend>{listen_ip}:</td>
		<td>$REPLICATION_LISTEN_IP_R</td>
		<td width=1%>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend>{listen_port}:</td>
		<td>". Field_text("REPLICATION_LISTEN_PORT",$arrayConf["REPLICATION_LISTEN_PORT"],"font-size:13px;padding:3px;width:60px")."</td>
		<td width=1%>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend>{freeze_replication}:</td>
		<td>". Field_checkbox("REPLICATION_ENABLED",1,$arrayConf["REPLICATION_ENABLED"])."</td>
		<td width=1%>". help_icon("{lessfs_freeze_replication}")."</td>
	</tr>		
	<tr>
		<td class=legend>{slave_ip}:</td>
		<td>". Field_text("REPLICATION_PARTNER_IP",$arrayConf["REPLICATION_PARTNER_IP"],"font-size:13px;padding:3px;width:95px")."</td>
		<td width=1%>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend>{slave_port}:</td>
		<td>". Field_text("REPLICATION_PARTNER_PORT",$arrayConf["REPLICATION_PARTNER_PORT"],"font-size:13px;padding:3px;width:60px")."</td>
		<td width=1%>&nbsp;</td>
	</tr>		
	

	
	<tr>
		<td colspan=3 align='right'>
			<hr>
			". button("{apply}","SaveLessFSReplicConfig()")."
		</td>
	</table>
	</div>
	
	<script>
	
	function CheckReplicFields(){
		DisableFieldsFromId('lessfscfg-replic');
		document.getElementById('REPLICATION').disabled=false;
		if(!document.getElementById('REPLICATION').checked){return;}

		document.getElementById('REPLICATION_ROLE').disabled=false;
		if(document.getElementById('REPLICATION_ROLE').value=='slave'){
			document.getElementById('REPLICATION_LISTEN_IP').disabled=false;
			document.getElementById('REPLICATION_LISTEN_PORT').disabled=false;
			return;
		}
		
		document.getElementById('REPLICATION_PARTNER_IP').disabled=false;
		document.getElementById('REPLICATION_PARTNER_PORT').disabled=false;
		document.getElementById('REPLICATION_ENABLED').disabled=false;
	}

		var x_SaveLessFSReplicConfig= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			RefreshTab('main_lessfs_config');
			
		}	
		
		function SaveLessFSReplicConfig(){
			var XHR = XHRParseElements('lessfscfg-replic');
			document.getElementById('lessfscfg-replic').innerHTML=\"<center style='margin:10px'><img src='img/wait_verybig.gif'></center>\";
			XHR.sendAndLoad(\"$page\", 'GET',x_SaveLessFSReplicConfig);
		}
CheckReplicFields();
	</script>
	
	
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);	
	
	
}


function index(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$sock=new sockets();
	$arrayConf=unserialize(base64_decode($sock->GET_INFO("lessfsConf")));
	
	$size=$sock->getFrameWork("cmd.php?du-dir-size=yes&path=".urlencode($arrayConf["MAIN_PATH"]));
	$size=$size*1024;
	$size=FormatBytes($size);
	
	$restart=Paragraphe("64-usb-refresh.png","{restart_lessfs_service}","{restart_lessfs_service_text}","javascript:Loadjs('$page?restart=yes')");
	
	$html="
	<table style='width:100%'>
	<td valign='top'><center><img src='img/deduplication-148.png'></center>
	<hr>$restart<hr>
	<table style='width:100%'>
	<tr>
		<td class=legend>{storage_size}:</td><td><strong style='font-size:13px'>{$size}</strong></td>
	</tr>
	</table>
	</td>
	<td valign='top'><div class=explain>{file_deduplication_explain}</div></td>
	</table>
	
	
	
		
		
	";
	
	echo $tpl->_ENGINE_parse_body($html);	

}

function settings(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$sock=new sockets();
	$arrayConf=unserialize(base64_decode($sock->GET_INFO("lessfsConf")));
	if(!is_array($arrayConf)){$arrayConf=array();}	
	if(!is_numeric($arrayConf["DEBUG"])){$arrayConf["DEBUG"]=2;}
	if($arrayConf["MAIN_PATH"]==null){$arrayConf["MAIN_PATH"]="/data";}
	if(!is_numeric($arrayConf["DB_FILEBLOCK"])){$arrayConf["DB_FILEBLOCK"]=1048576;}
	if(!is_numeric($arrayConf["CACHESIZE"])){$arrayConf["CACHESIZE"]=1024;}
	if(!is_numeric($arrayConf["COMMIT_INTERVAL"])){$arrayConf["COMMIT_INTERVAL"]=30;}
	if(!is_numeric($arrayConf["MAX_THREADS"])){$arrayConf["MAX_THREADS"]=2;}
	if(!is_numeric($arrayConf["DYNAMIC_DEFRAGMENTATION"])){$arrayConf["DYNAMIC_DEFRAGMENTATION"]=1;}
	if(!is_numeric($arrayConf["BACKGROUND_DELETE"])){$arrayConf["BACKGROUND_DELETE"]=1;}
	if(!is_numeric($arrayConf["ENABLE_TRANSACTIONS"])){$arrayConf["ENABLE_TRANSACTIONS"]=1;}
	if(!is_numeric($arrayConf["BLKSIZE"])){$arrayConf["BLKSIZE"]=131072;}
	for($i=0;$i<6;$i++){$DEBUG_R[$i]=$i;}
	$MAX_THREADS_AR=array(1=>1,2=>2,4=>4,6=>6,8=>8,16=>16);
	$MAX_THREADS_F=Field_array_Hash($MAX_THREADS_AR,"MAX_THREADS",$arrayConf["MAX_THREADS"],"style:font-size:13px;padding:3px");
	
	$BLKSIZE_AR=array(4096=>4096,8192=>8192,16384=>16384,32768=>32768,65536=>65536,131072=>131072);
	$BLKSIZE_AR_F=Field_array_Hash($BLKSIZE_AR,"BLKSIZE",$arrayConf["BLKSIZE"],"style:font-size:13px;padding:3px");
	$DEBUG_F=Field_array_Hash($DEBUG_R,"DEBUG",$arrayConf["DEBUG"],"style:font-size:13px;padding:3px");
	
	$html="
	<div id='lessfscfg'>
	<table style='width:98%' class=form>
	<tr>
		<td class=legend>{database_path}:</td>
		<td>". Field_text("MAIN_PATH",$arrayConf["MAIN_PATH"],"font-size:13px;padding:3px;width:190px")."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend>{bucketsize_database}:</td>
		<td>". Field_text("DB_FILEBLOCK",$arrayConf["DB_FILEBLOCK"],"font-size:13px;padding:3px;width:60px")."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend>{cache_size}:</td>
		<td>". Field_text("CACHESIZE",$arrayConf["CACHESIZE"],"font-size:13px;padding:3px;width:60px")."&nbsp;<span style='font-size:13px;'>MB</span></td>
		<td width=1%>". help_icon("{lessfs_cachesize}")."</td>
	</tr>		
	<tr>
		<td class=legend>{LESSFS_COMMIT_INTERVAL_FIELD}:</td>
		<td>". Field_text("COMMIT_INTERVAL",$arrayConf["COMMIT_INTERVAL"],"font-size:13px;padding:3px;width:60px")."&nbsp;<span style='font-size:13px;'>{seconds}</span></td>
		<td width=1%>". help_icon("{LESSFS_COMMIT_INTERVAL}")."</td>
	</tr>	
	<tr>
		<td class=legend>{log_level}:</td>
		<td>$DEBUG_F</td>
		<td width=1%></td>
	</tr>	
	<tr>
		<td class=legend>{processes_number}:</td>
		<td>$MAX_THREADS_F</td>
		<td width=1%>". help_icon("{LESSFS_MAX_THREADS}")."</td>
	</tr>		
	<tr>
		<td class=legend>{LESSFS_BLKSIZE}:</td>
		<td>$BLKSIZE_AR_F</td>
		<td width=1%>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend>{BACKGROUND_DELETE}:</td>
		<td>". Field_checkbox("BACKGROUND_DELETE",1,$arrayConf["BACKGROUND_DELETE"])."</td>
		<td width=1%>". help_icon("{LESSFS_BACKGROUND_DELETE}")."</td>
	</tr>
	<tr>
		<td class=legend>{ENABLE_TRANSACTIONS}:</td>
		<td>". Field_checkbox("ENABLE_TRANSACTIONS",1,$arrayConf["ENABLE_TRANSACTIONS"])."</td>
		<td width=1%>". help_icon("{LESSFS_ENABLE_TRANSACTIONS}")."</td>
	</tr>	
	
	<tr>
		<td colspan=3 align='right'>
			<hr>
			". button("{apply}","SaveLessFSConfig()")."
		</td>
	</table>
	</div>
	<script>

		var x_SaveLessFSConfig= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			RefreshTab('main_lessfs_config');
			
		}	
		
		function SaveLessFSConfig(){
			var XHR = XHRParseElements('lessfscfg');
			document.getElementById('lessfscfg').innerHTML=\"<center style='margin:10px'><img src='img/wait_verybig.gif'></center>\";
			XHR.sendAndLoad(\"$page\", 'GET',x_SaveLessFSConfig);
		}

	</script>
	
	
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
}


function folders(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$sock=new sockets();	
	
	$html="
	<div class=explain>{lessfs_mount_explain}</div>
	<hr>
	<table style='width:98%' class=form style='margin-bottom:10px'>
	<tr>
		<td class=legend>{folder}:</td>
		<td>". Field_text("folder-dd-add",null,"font-size:13px;padding:3px;width:340px")."</td>
		<td width=1%><input type='button' value='{browse}...' OnClick=\"javascript:Loadjs('SambaBrowse.php?no-shares=yes&field=folder-dd-add&protocol=no');\"></td>
		<td width=1%>". button('{add}',"AddDedupFolder()")."</td>
	</tr>
	</table>
	
	<div style='width:100%;height:250px;overflow:auto' id='dedup-folders-list'></div>
	
	
		<script>
	
		var x_AddDedupFolder= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			RefreshDedupFolders();
			
		}	
		
		function AddDedupFolder(){
			var XHR = new XHRConnection();
			XHR.appendData('folders-add',document.getElementById('folder-dd-add').value);
			document.getElementById('dedup-folders-list').innerHTML=\"<center style='margin:10px'><img src='img/wait_verybig.gif'></center>\";
			XHR.sendAndLoad(\"$page\", 'GET',x_AddDedupFolder);
		}
		
		
		function RefreshDedupFolders(){
			LoadAjax('dedup-folders-list','$page?folders-list=yes');
		
		}
	RefreshDedupFolders();
	</script>";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function folders_add(){
	if($_GET["folders-add"]==null){echo "!";return;}
	$sock=new sockets();
	$arrayConf=unserialize(base64_decode($sock->GET_INFO("lessfsConf")));	
	if(!is_array($arrayConf)){$arrayConf=array();}
	$arrayConf["FOLDERS"][$_GET["folders-add"]]=true;
	$sock->SaveConfigFile(base64_encode(serialize($arrayConf)),"lessfsConf");
	$sock->getFrameWork("cmd.php?lessfs=yes&mount=yes");
	
}

function folders_delete(){
	if($_GET["folders-delete"]==null){echo "!";return;}
	$sock=new sockets();
	$arrayConf=unserialize(base64_decode($sock->GET_INFO("lessfsConf")));	
	if(!is_array($arrayConf)){$arrayConf=array();}
	unset($arrayConf["FOLDERS"][$_GET["folders-delete"]]);
	$sock->SaveConfigFile(base64_encode(serialize($arrayConf)),"lessfsConf");
	$sock->getFrameWork("cmd.php?umount-disk=". urlencode($_GET["folders-delete"]));	
}

function folders_list(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$sock=new sockets();	
	$arrayConf=unserialize(base64_decode($sock->GET_INFO("lessfsConf")));	
	
			$html="<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th>&nbsp;</th>
	<th>{folder}</th>
	<th>&nbsp;</th>
	<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";	
			
$mounted=unserialize(base64_decode($sock->getFrameWork("cmd.php?lessfs-mounts=yes")));			
			
if(is_array($arrayConf["FOLDERS"])){
while (list ($folder, $none) = each ($arrayConf["FOLDERS"]) ){
		$folder_enc=base64_encode($folder);
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$img="okdanger32.png";
		if($mounted[$folder]){$img="ok32.png";}
		unset($mounted[$folder]);
		$html=$html."
		<tr class=$classtr>
			<td width=1%><img src='img/Database32.png'></td>
			<td><strong style='font-size:16px'>$folder</strong></td>
			<td width=1%  align='center'><img src='img/$img'></td>
			<td width=1% align='center'>".imgtootltip("delete-24.png",'{delete}',"DeleteDedupFolder('$folder')")."</td>
		</tr>";					
		}
	}
	
	if(is_array($mounted)){
		while (list ($folder, $none) = each ($mounted) ){
				if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
				$img="ok32.png";
				$html=$html."
				<tr class=$classtr>
					<td width=1%><img src='img/Database32.png'></td>
					<td><strong style='font-size:16px'>$folder</strong></td>
					<td width=1%  align='center'><img src='img/$img'></td>
					<td width=1% align='center'>".imgtootltip("delete-24.png",'{delete}',"DeleteDedupFolder('$folder')")."</td>
				</tr>";					
			}
	}	
	
	

	$html=$html."</table>
	<script>
	
		function DeleteDedupFolder(folder){
			var XHR = new XHRConnection();
			XHR.appendData('folders-delete',folder);
			document.getElementById('dedup-folders-list').innerHTML=\"<center style='margin:10px'><img src='img/wait_verybig.gif'></center>\";
			XHR.sendAndLoad(\"$page\", 'GET',x_AddDedupFolder);
		}
	</script>	
	
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}




function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	echo "YahooWin3('650','$page?popup=yes','".$tpl->_ENGINE_parse_body("{file_deduplication}")."');";
	}
	
	
function popup(){
	$users=new usersMenus();
	if(!$users->deduplication_installed){popup_install();return;}
	
	$page=CurrentPageName();
	$users=new usersMenus();
	$tpl=new templates();
	$array["index"]='{index}';
	$array["settings"]="{parameters}";
	$array["folders"]="{folders}";
	$array["replication"]="{replication}";
	
	
	while (list ($num, $ligne) = each ($array) ){
		$tab[]="<li><a href=\"$page?$num=yes&hostname={$_GET["hostname"]}&ou={$_GET["ou"]}\"><span>$ligne</span></a></li>\n";
			
	}

	$html="
		<div id='main_lessfs_config' style='background-color:white'>
		<ul>
		". implode("\n",$tab). "
		</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_lessfs_config').tabs();
				});
		</script>
	
	";
		
	
	echo $tpl->_ENGINE_parse_body($html);	
	
	
}

function popup_install(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$prefix="dedupscr";
	$html="
	<table style='width:100%'>
	<tr>
	<td valign='top'><img src='img/deduplication-148.png'></td>
	<td valign='top'><div class=explain>{file_deduplication_explain}</div>
	</tr>
	</table>
	<div id='file_deduplication_status'></div>
	
	
	<script>
	
	var {$prefix}timerID  = null;
	var {$prefix}tant=0;
	var {$prefix}reste=0;

	function {$prefix}demarre(){
		{$prefix}tant = {$prefix}tant+1;
		{$prefix}reste=20-{$prefix}tant;
		if(!YahooWin3Open()){return false;}
		
		if ({$prefix}tant < 10 ) {                           
		{$prefix}timerID =setTimeout(\"{$prefix}demarre()\",3000);
	      } else {
			{$prefix}tant = 0;
			DedpupStatus();
			{$prefix}demarre(); 
			                              
	   }
	}

	function {$prefix}Loadpage(){
		setTimeout('{$prefix}demarre()',1000);
	}
	function DedpupStatus(){
		LoadAjax('file_deduplication_status','$page?install-status=yes');
	}
	
	{$prefix}Loadpage();
	DedpupStatus();	
	
</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	}
	
	
function popup_install_status(){
	$users=new usersMenus();
	if($users->deduplication_installed){
		$page=CurrentPageName();
		echo "
		<script>Loadjs('$page');</script>";
		
		return;
	}
	
	
	$page=CurrentPageName();
	$tpl=new templates();	
		if(count($GLOBALS["GLOBAL_VERSIONS_CONF"]==0)){
			$GlobalApplicationsStatus=@file_get_contents("ressources/logs/global.versions.conf");
			$tb=explode("\n",$GlobalApplicationsStatus);
			while (list ($num, $line) = each ($tb) ){
				if(preg_match('#\[(.+?)\]\s+"(.+?)"#',$line,$re)){
					$GLOBALS["GLOBAL_VERSIONS_CONF"][trim($re[1])]=trim($re[2]);
				}
			}			
		}

	$APP_FUSE_IMG="ok32.png";		
	$APP_FUSE_TITLE="{installed}";
	
	$APP_ZFS_FUSE_IMG="ok32.png";		
	$APP_ZFS_FUSE_TITLE="{installed}";	
	
	$APP_TOKYOCABINET_IMG="ok32.png";	
	$APP_TOKYOCABINET_TITLE="{installed}";	
	
	$APP_LESSFS_IMG="ok32.png";	
	$APP_LESSFS_TITLE="{installed}";	
	
	if($GLOBALS["GLOBAL_VERSIONS_CONF"]["APP_FUSE"]<>null){
		$ver=explode(".",$GLOBALS["GLOBAL_VERSIONS_CONF"]["APP_FUSE"]);
		if($ver[0]<2){$GLOBALS["GLOBAL_VERSIONS_CONF"]["APP_FUSE"]=null;}
		if($ver[1]<8){$GLOBALS["GLOBAL_VERSIONS_CONF"]["APP_FUSE"]=null;}
		
	}
	
		
	if($GLOBALS["GLOBAL_VERSIONS_CONF"]["APP_FUSE"]==null){
		$APP_FUSE_IMG="danger32.png";
		$APP_FUSE_TITLE="{not_installed}";
		$js="javascript:Loadjs('setup.index.progress.php?product=APP_FUSE&start-install=yes')";
		$APP_FUSE_BUTTON=button("{install_upgrade}",$js);
		$APP_INFO=popup_install_status_install_status("APP_FUSE");
		if($APP_INFO<>null){
			$APP_FUSE_BUTTON="<a href=\"javascript:blur();\" OnClick=\"$js\" style='font-size:14px;'>
				$APP_INFO
			</a>";
		}
		
	}
		
	if($GLOBALS["GLOBAL_VERSIONS_CONF"]["APP_ZFS_FUSE"]==null){
		$APP_ZFS_FUSE_IMG="danger32.png";
		$APP_ZFS_FUSE_TITLE="{not_installed}";
		$js="javascript:Loadjs('setup.index.progress.php?product=APP_ZFS_FUSE&start-install=yes')";
		$APP_ZFS_FUSE_BUTTON=button("{install_upgrade}",$js);
		$APP_INFO=popup_install_status_install_status("APP_ZFS_FUSE");
		if($APP_INFO<>null){
			$APP_ZFS_FUSE_BUTTON="<a href=\"javascript:blur();\" OnClick=\"$js\" style='font-size:14px;'>
				$APP_INFO
			</a>";
		}	

		if($GLOBALS["GLOBAL_VERSIONS_CONF"]["APP_FUSE"]==null){$APP_ZFS_FUSE_BUTTON=null;}
		
	}	
	
	if($GLOBALS["GLOBAL_VERSIONS_CONF"]["APP_TOKYOCABINET"]==null){
		$APP_TOKYOCABINET_IMG="danger32.png";
		$APP_TOKYOCABINET_TITLE="{not_installed}";
		$js="javascript:Loadjs('setup.index.progress.php?product=APP_HAMSTERDB&start-install=yes')";
		$APP_TOKYOCABINET_BUTTON=button("{install_upgrade}",$js);
		$APP_INFO=popup_install_status_install_status("APP_HAMSTERDB");
		if($APP_INFO<>null){
			$APP_TOKYOCABINET_BUTTON="<a href=\"javascript:blur();\" OnClick=\"$js\" style='font-size:14px;'>
				$APP_INFO
			</a>";
		}

		if($GLOBALS["GLOBAL_VERSIONS_CONF"]["APP_ZFS_FUSE"]==null){$APP_TOKYOCABINET_BUTTON=null;}
		
		
	}	
	
	
	if($GLOBALS["GLOBAL_VERSIONS_CONF"]["APP_LESSFS"]==null){
		$APP_LESSFS_IMG="danger32.png";
		$APP_LESSFS_TITLE="{not_installed}";
		$js="javascript:Loadjs('setup.index.progress.php?product=APP_LESSFS&start-install=yes')";
		$APP_LESSFS_BUTTON=button("{install_upgrade}",$js);
		$APP_INFO=popup_install_status_install_status("APP_LESSFS");
		if($APP_INFO<>null){
			$APP_LESSFS_BUTTON="<a href=\"javascript:blur();\" OnClick=\"$js\" style='font-size:14px;'>
				$APP_INFO
			</a>";
		}

		if($GLOBALS["GLOBAL_VERSIONS_CONF"]["APP_TOKYOCABINET"]==null){$APP_LESSFS_BUTTON=null;}		
	}	
	
	
	$html="
	<H3>{status}<H3><hr>
	<table style='width:98%' class=form>
	<tr>
		<td width=1%><img src='img/$APP_FUSE_IMG'></td>
		<td style='font-size:14px'>{APP_FUSE}</td>
		<td style='font-size:14px'>$APP_FUSE_TITLE</td>
		<td width=1% nowrap>$APP_FUSE_BUTTON</td>
	</tr>
	<tr>
		<td width=1%><img src='img/$APP_ZFS_FUSE_IMG'></td>
		<td style='font-size:14px'>{APP_ZFS_FUSE}</td>
		<td style='font-size:14px' nowrap>$APP_ZFS_FUSE_TITLE</td>
		<td width=1%>$APP_ZFS_FUSE_BUTTON</td>
	</tr>	
	<tr>
		<td width=1%><img src='img/$APP_TOKYOCABINET_IMG'></td>
		<td style='font-size:14px'>{APP_HAMSTERDB}</td>
		<td style='font-size:14px' nowrap>$APP_TOKYOCABINET_TITLE</td>
		<td width=1%>$APP_TOKYOCABINET_BUTTON</td>
		
	</tr>	
	<tr>
		<td width=1%><img src='img/$APP_LESSFS_IMG'></td>
		<td style='font-size:14px'>{APP_LESSFS}</td>
		<td style='font-size:14px'>$APP_LESSFS_TITLE</td>
		<td style='font-size:14px' nowrap>$APP_LESSFS_BUTTON</td>
	</tr>	
	</table>
	";
	
	
	echo $tpl->_ENGINE_parse_body($html);

}


function saveconfig(){
	$sock=new sockets();
	$arrayConf=unserialize(base64_decode($sock->GET_INFO("lessfsConf")));	
	if(!is_array($arrayConf)){$arrayConf=array();}
	
	while (list ($num, $ligne) = each ($_GET) ){
		$arrayConf[$num]=$ligne;
	}
	
	$sock->SaveConfigFile(base64_encode(serialize($arrayConf)),"lessfsConf");
	$sock->getFrameWork("cmd.php?lessfs=yes");
	
}

function popup_install_status_install_status($appli){
	$appname=$appli;
	$ini=new Bs_IniHandler();
	$dbg_exists=false;
	if(!is_file(dirname(__FILE__). "/ressources/install/$appname.ini")){return null;}
	
	    $data=file_get_contents(dirname(__FILE__). "/ressources/install/$appname.ini");
		$ini->loadString($data);
		$status=$ini->_params["INSTALL"]["STATUS"];
		$text_info=$ini->_params["INSTALL"]["INFO"];
		if(strlen($text_info)>0){$text_info="$text_info...";}
		if($status==null){$status=0;}
		if($status>100){$color="#D32D2D";$status=100;$text='{failed}';}else{$color="#5DD13D";$text=$status.'%';}
		return"<strong>{$text}&nbsp;$text_info</strong>";
	}

