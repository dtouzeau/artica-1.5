<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.tcpip.inc');
include_once(dirname(__FILE__).'/ressources/class.system.network.inc');
include_once(dirname(__FILE__).'/ressources/class.computers.inc');
include_once(dirname(__FILE__).'/ressources/class.autofs.inc');
include_once(dirname(__FILE__).'/ressources/class.auditd.inc');
if(posix_getuid()<>0){
	
	if(!checkrights()){
		$tpl=new templates();
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}
}
if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["status"])){status();exit;}
if(isset($_GET["force-status"])){status_real();exit;}
if(isset($_GET["config"])){config();exit;}
if(isset($_GET["EnableAuditd"])){SaveConfig();exit;}
if(isset($_GET["folder"])){folders();exit;}
if(isset($_GET["events"])){events();exit;}
if(isset($_GET["events-list"])){events_list();exit;}
if(isset($_GET["reindex"])){reindex();exit;}
if(isset($_GET["import-now"])){importnow();exit;}
if(isset($_GET["js-folder"])){js_folder();exit;}


js();
function checkrights(){
	$users=new usersMenus();
	if($users->AsSambaAdministrator){return true;}
	if($users->AsSystemAdministrator){return true;}
	return false;
	
}

function js_folder(){
	$page=CurrentPageName();	
	echo "$('#BodyContent').load('$page?folder=yes');";
}

function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{APP_AUDITD}");
	
	if(isset($_GET["in-front-ajax"])){
	$html="
		$('#BodyContent').load('$page?popup=yes');
	
	";	

		echo $html;
		return ;
		
	}
	
	
	$html="
		YahooWin4(650,'$page?popup=yes&path={$_GET["path"]}&id={$_GET["id"]}','$title');
	
	";
		
	echo $html;
	
	
}

function popup(){
	$tpl=new templates();
	
	$page=CurrentPageName();
	$array["status"]=$tpl->_ENGINE_parse_body('{status}');
	$array["config"]=$tpl->_ENGINE_parse_body('{parameters}');
	$array["folder"]=$tpl->_ENGINE_parse_body('{folders}');
	
	while (list ($num, $ligne) = each ($array) ){
		$html[]= "<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n";
		
		
			
		}
	echo "<div id=main_auditd_daemon style='width:100%;height:600px;overflow:auto;background-color:white;'>
				<ul>". implode("\n",$html)."</ul>
		</div>
		<script>
				$(document).ready(function(){
					$('#main_auditd_daemon').tabs({
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


function status(){
	$page=CurrentPageName();
	$html="<table style='width:100%'>
	<tr>
		<td valign='top' width=1% nowrap width=1%><img src='img/folder-watch-128.png' id='audit-picture' style='margin:10px'></td>
		<td valign='top' width=1% ><div class=explain>{APP_AUDITD_TEXT}</div>
		<div id='audtid-status'></div>
		
	</tr>
	</table>	
	<script>
		function audtid_status(){
			LoadAjax('audtid-status','$page?force-status=yes');
		}
		audtid_status();
	</script>
	";
	
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
	
}

function status_real(){
	$sock=new sockets();
	$ini=new Bs_IniHandler();
	$ini->loadString(base64_decode($sock->getFrameWork('cmd.php?auditd-ini-status=yes')));
	$status=DAEMON_STATUS_ROUND("APP_AUDITD",$ini);
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($status);
}

function config(){
	$aud=new auditd();
	$page=CurrentPageName();
	$config=$aud->parseConfig();
	$sock=new sockets();
	$EnableAuditd=$sock->GET_INFO("EnableAuditd");
	if($EnableAuditd==null){$EnableAuditd=1;}
	
	for($i=50;$i<100;$i++){
		$space_left_ar[$i]=$i;
	}
	
	$space_left_action_array=array(
		"ignore"=>"{action_ignore}",
		"syslog"=>"{action_syslog}",
		"email"=>"{action_email}",
		"suspend"=>"{action_auditd_suspend}",
		"halt"=>"{action_halt}",
	
	);
	
	$AuditFrequency=$sock->GET_INFO("AuditFrequency");
	if($AuditFrequency==null){$AuditFrequency=10;}
	
	$MaxEventsInDatabase=$sock->GET_INFO("AuditMaxEventsInDatabase");
	if($MaxEventsInDatabase==null){$MaxEventsInDatabase=1000000;}

	$html="<div id='audtiddiv'>
	<table style='width:100%'>
	<tr>
		<td valign='top' width=1% nowrap style='font-size:13px' align='right'>{EnableAuditd}:</td>
		<td>". Field_checkbox("EnableAuditd",1,$EnableAuditd,"EnableAuditd()")."</td>
	</tr>
	<tr>
		<td valign='top' width=1% nowrap style='font-size:13px' align='right'>{AuditFrequency}:</td>
		<td style='font-size:13px'>". Field_text("AuditFrequency",$AuditFrequency,"font-size:13px;padding:3px;width:60px")."&nbsp;{minutes}</td>
		<td width=1%>". help_icon("{AuditFrequency_explain}")."</td>
	</tr>	
	<tr>
		<td valign='top' width=1% nowrap style='font-size:13px' align='right'>{MaxEventsInDatabase}:</td>
		<td style='font-size:13px'>". Field_text("MaxEventsInDatabase",$MaxEventsInDatabase,"font-size:13px;padding:3px;width:60px")."&nbsp;{minutes}</td>
		<td width=1%>". help_icon("{MaxEventsInDatabase_explain}")."</td>
	</tr>	
	
	
	<tr>
		<td valign='top' width=1% nowrap style='font-size:13px' align='right'>{mail}:</td>
		<td>". Field_text("action_mail_acct",$config["action_mail_acct"],"font-size:13px;padding:3px")."</td>
		<td width=1%>". help_icon("{action_mail_acct}")."</td>
	</tr>		
	<tr>
		<td valign='top' width=1% nowrap style='font-sjs-folderize:13px' align='right'>{space_left}:</td>
		<td style='font-size:13px'>". Field_text("space_left",$config["space_left"],"font-size:13px;padding:3px;width:90px")."&nbsp;MB</td>
		<td width=1%>". help_icon("{space_left_explain}")."</td>
	</tr>		
	<tr>
		<td valign='top' width=1% nowrap style='font-size:13px' align='right'>{space_left_action}:</td>
		<td style='font-size:13px'>".Field_array_Hash($space_left_action_array,"space_left_action",$config["space_left_action"],null,null,0,"font-size:13px;padding:3px") ."</td>
		<td width=1%>&nbsp;</td>
	</tr>		
	<tr>
		<td valign='top' width=1% nowrap style='font-size:13px' align='right'>{admin_space_left}:</td>
		<td style='font-size:13px'>". Field_text("admin_space_left",$config["admin_space_left"],"font-size:13px;padding:3px;width:90px")."&nbsp;MB</td>
		<td width=1%>". help_icon("{admin_space_left_explain}")."</td>
	</tr>		
	<tr>
		<td valign='top' width=1% nowrap style='font-size:13px' align='right'>{space_left_action}:</td>
		<td style='font-size:13px'>".Field_array_Hash($space_left_action_array,"admin_space_left_action",$config["admin_space_left_action"],null,null,0,"font-size:13px;padding:3px") ."</td>
		<td width=1%>&nbsp;</td>
	</tr>	
	<tr>
		<td valign='top' width=1% nowrap style='font-size:13px' align='right'>{disk_full_action}:</td>
		<td style='font-size:13px'>".Field_array_Hash($space_left_action_array,"disk_full_action",$config["disk_full_action"],null,null,0,"font-size:13px;padding:3px") ."</td>
		<td width=1%>&nbsp;</td>
	</tr>		
	<tr>
		<td valign='top' width=1% nowrap style='font-size:13px' align='right'>{disk_error_action}:</td>
		<td style='font-size:13px'>".Field_array_Hash($space_left_action_array,"disk_error_action",$config["disk_error_action"],null,null,0,"font-size:13px;padding:3px") ."</td>
		<td width=1%>&nbsp;</td>
	</tr>	

	<tr>
		<td colspan=3 align='right'><hr>
			". button('{apply}',"SaveAuditDConfig()")."</td>
		</tr>
	
	</table>
	</div>
	<script>
	
	
		var x_SaveAuditDConfig=function (obj) {
		 	text=obj.responseText;
		 	if(text.length>0){alert(text);}
			RefreshTab('main_auditd_daemon');
			
			}

			
		function SaveAuditDConfig(){
			
	        var XHR = new XHRConnection();
	        if(document.getElementById('EnableAuditd').checked){
	        	XHR.appendData('EnableAuditd',1);
			}else{
				XHR.appendData('EnableAuditd',0);
			}
	        XHR.appendData('action_mail_acct',document.getElementById('action_mail_acct').value);
	        XHR.appendData('space_left',document.getElementById('space_left').value);
	        XHR.appendData('space_left_action',document.getElementById('space_left_action').value);
	        XHR.appendData('admin_space_left',document.getElementById('admin_space_left').value);
	        XHR.appendData('disk_full_action',document.getElementById('disk_full_action').value);
	        XHR.appendData('disk_error_action',document.getElementById('disk_error_action').value);
	        XHR.appendData('AuditFrequency',document.getElementById('AuditFrequency').value);
	        XHR.appendData('MaxEventsInDatabase',document.getElementById('MaxEventsInDatabase').value);
	        
	        
	        
	        XHR.appendData('admin_space_left_action',document.getElementById('admin_space_left_action').value);
	        document.getElementById('audtiddiv').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
	        XHR.sendAndLoad('$page', 'GET',x_SaveAuditDConfig);
		
		}
		
		function EnableAuditd(){
			document.getElementById('action_mail_acct').disabled=true;
			document.getElementById('space_left').disabled=true;
			document.getElementById('space_left_action').disabled=true;
			document.getElementById('admin_space_left').disabled=true;
			document.getElementById('disk_full_action').disabled=true;
			document.getElementById('disk_error_action').disabled=true;
			document.getElementById('admin_space_left_action').disabled=true;
			document.getElementById('AuditFrequency').disabled=true;
			document.getElementById('MaxEventsInDatabase').disabled=true;
			
			
			
			if(document.getElementById('EnableAuditd').checked){
			document.getElementById('AuditFrequency').disabled=false;
			document.getElementById('action_mail_acct').disabled=false;
			document.getElementById('space_left').disabled=false;
			document.getElementById('space_left_action').disabled=false;
			document.getElementById('admin_space_left').disabled=false;
			document.getElementById('disk_full_action').disabled=false;
			document.getElementById('disk_error_action').disabled=false;
			document.getElementById('MaxEventsInDatabase').disabled=false;
			document.getElementById('admin_space_left_action').disabled=false;

			
			}
		}
	EnableAuditd();
	</script>
	";
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
}

function SaveConfig(){
	$sock=new sockets();
	$sock->SET_INFO("EnableAuditd",$_GET["EnableAuditd"]);
	$page=CurrentPageName();
	if($_GET["AuditFrequency"]<10){$_GET["AuditFrequency"]=10;}
	if($_GET["AuditFrequency"]>30){$_GET["AuditFrequency"]=30;}
	
	
	$sock->SET_INFO("AuditFrequency",$_GET["AuditFrequency"]);
	$sock->SET_INFO("AuditMaxEventsInDatabase",$_GET["AuditMaxEventsInDatabase"]);
	
	unset($_GET["EnableAuditd"]);
	unset($_GET["AuditFrequency"]);
	
	
	if($_GET["space_left"]<5){$_GET["space_left"]=75;}
	if($_GET["admin_space_left"]>=$_GET["space_left"]){$_GET["admin_space_left"]=$_GET["space_left"]-5;}
	
	$aud=new auditd();
	$config=$aud->parseConfig();
	while (list ($num, $ligne) = each ($_GET) ){
		$config[$num]=$ligne;
	}
	
	while (list ($num, $ligne) = each ($config) ){
		$conf[]="$num = $ligne";
	}	
	
	$sock->SaveConfigFile(@implode("\n",$conf),"AuditDDaemonConf");
	$sock->getFrameWork("cmd.php?auditd-apply=yes");
	
}

function folders(){
	$page=CurrentPageName();
	$sql="SELECT * FROM auditd_dir";
	$q=new mysql();
	$tpl=new templates();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	$events=$tpl->_ENGINE_parse_body("{events}");
	if(!$q->ok){
		echo "<H3>$sql $q->mysql_error</H3>";
		return;
	}
	
	$html="
<div class=explain>{AUDITD_FOLDERS_LIST_EXPLAIN}</div>	
	
	
<table cellspacing='0' cellpadding='0' border='0' class='tableView'>
<thead class='thead'>
	<tr>
	<th>&nbsp;</th>
	<th>{directory}</th>
	<th>{events}</th>
	<th>{delete}</th>
	</tr>
</thead>	
	
	";
	
	$aud=new auditd();
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}

		$entries=$aud->ENTRIES_NUMBER($ligne["key"]);
		$img=imgtootltip("folder-watch-24.png","{view}","AuditdEvents('{$ligne["key"]}')");
		
		
		$html=$html."
		<tr class=$classtr>
			<td width=1% style='padding:3px'>$img</td>
			<td width=99%><strong style='font-size:14px'>{$ligne["dir"]}</a></td>
			<td width=1% align=center><strong style='font-size:14px'>$entries</strong></td>
			<td width=1% align=center>". imgtootltip("delete-24.png","{delete}","DeployTaskDelete({$ligne["key"]})")."</td>
			
		</tr>";
		
		
	}
	
	$html=$html."</table>
	
	<div style='width:100%;text-align:right;margin-top:8px'>". button("{reindex}","reindex()")."</div>
	<div style='width:100%;text-align:right;margin-top:8px'>". button("{import}","import()")."</div>
	<script>
		function AuditdEvents(key){
			YahooWin6('700','$page?events=yes&key='+key,'$events');
		
		}
		
		
		var x_reindex=function (obj) {
		 	text=obj.responseText;
		 	if(text.length>0){alert(text);}
			RefreshTab('main_auditd_daemon');
			
			}

			
		function reindex(){
	        var XHR = new XHRConnection();
	        XHR.appendData('reindex',1);
	        XHR.sendAndLoad('$page', 'GET',x_reindex);
		
		}	

		function import(){
	        var XHR = new XHRConnection();
	        XHR.appendData('import-now',1);
	        XHR.sendAndLoad('$page', 'GET',x_reindex);
		
		}		
		
	</script>
	
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);	
	
	
	
	
}

function events(){
	$page=CurrentPageName();
	$tpl=new templates();
	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top' class=legend>{search}:</td>
		<td valign='top'>". Field_text("SearchStringAuditd",null,"font-size:13px",null,null,null,false,"SearchAuditdKey(event)")."</td>
	</tr>
	</table>
			
	<div id='eventsAuditD' style='width:100%;height:350px;overflow:auto'></div>
	<script>
		LoadAjax('eventsAuditD','$page?events-list=yes&key={$_GET["key"]}&f={$_GET["f"]}');
		
		function AuditdByFile(filename){
			var ActionToSearch=escape(document.getElementById('ActionToSearch').value);	
			LoadAjax('eventsAuditD','$page?events-list=yes&key={$_GET["key"]}&f='+filename+'&a='+ActionToSearch);
						
			
		}
		
		function AuditdByAction(action){
			var pattern=escape(document.getElementById('SearchStringAuditd').value);
			var fileToSearch=escape(document.getElementById('fileToSearch').value);
			LoadAjax('eventsAuditD','$page?events-list=yes&key={$_GET["key"]}&f='+fileToSearch+'&search='+pattern+'&a='+action);
		}
		
		function SearchAuditdKey(e){
			if(checkEnter(e)){
				var pattern=escape(document.getElementById('SearchStringAuditd').value);
				var fileToSearch=escape(document.getElementById('fileToSearch').value);
				var ActionToSearch=escape(document.getElementById('ActionToSearch').value);
				LoadAjax('eventsAuditD','$page?events-list=yes&key={$_GET["key"]}&f='+fileToSearch+'&search='+pattern+'&a='+ActionToSearch);
			}
		}
		
		
	</script>
	";
	echo $tpl->_ENGINE_parse_body($html);		
}

function events_list(){
	$tpl=new templates();
	$q=new mysql();
	
	$aud=new auditd();
	$page=CurrentPageName();
	if($_GET["n"]==null){$_GET["n"]=0;}
	if($_GET["n"]<0){$_GET["n"]=0;}
	
	$limit=$_GET["n"]*250;
	
	if($_GET["f"]<>null){
		$filename_decoded=addslashes(base64_decode($_GET["f"]));
		$filename=" AND `file`='$filename_decoded'";
		
	}
	if($_GET["a"]<>null){
		$action=$_GET["a"];
		$action_sql=" AND `syscall`='$action'";
		
	}	
	
	if($_GET["search"]<>null){
		$search=str_replace("*","%",$_GET["search"]);
		$filename=" AND `file` LIKE '$search'";
	}
	
	$sql="SELECT * FROM auditd_files WHERE `key_path`='{$_GET["key"]}' $filename$action_sql ORDER BY `time` DESC LIMIT $limit,250";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "<H3>$sql $q->mysql_error</H3>";return;}	
	
	$path=basename($aud->GetPath($_GET["key"]));
	
	$html="
	
<strong style='margin-bottom:5px'><a href=\"javascript:LoadAjax('eventsAuditD','$page?events-list=yes&key={$_GET["key"]}');\">$path</a>&nbsp;&raquo;&nbsp;{$filename_decoded}&nbsp;|&nbsp;</strong>	<hr>
<input type='hidden' id='fileToSearch' value='{$_GET["f"]}'>
<input type='hidden' id='ActionToSearch' value='{$_GET["a"]}'>

<table cellspacing='0' cellpadding='0' border='0' class='tableView'>
<thead class='thead'>
	<tr>
	<th>{time}</th>
	<th>{file}</th>
	<th>&nbsp;</th>
	<th>{action}</th>
	<th>&nbsp;</th>
	<th>{members}</th>
	
	</tr>
</thead>";

while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	
		$fu=base64_encode($ligne["file"]);
		$futext=basename($ligne["file"]);
		$time=str_replace(date('Y')."-","",$ligne["time"]);
		$html=$html."
		<tr class=$classtr>
			<td width=1% nowrap style='height:auto'>$time</td>
			<td width=99% style='height:auto'><strong>{$ligne["file"]}</td>
			<td width=1% nowrap style='height:auto'>". imgtootltip("tree_loupe.gif","{filter}:{filename}<hr>$futext<hr>","AuditdByFile('$fu')")."</td>
			<td width=1% align=center style='height:auto'><strong>{{$ligne["syscall"]}}</strong></td>
			<td width=1% nowrap style='height:auto'>". imgtootltip("tree_loupe.gif","{filter}:{action}<hr>{$ligne["syscall"]}<hr>","AuditdByAction('{$ligne["syscall"]}')")."</td>
			<td width=1% align=center style='height:auto'><strong>{$ligne["uid"]}:{$ligne["gid"]}</strong></td>
		</tr>
		";
		
		
	}
	
$html=$html."</table>";

$next=$_GET["n"]+1;
$back=$_GET["n"]-1;
$button_next=button("{next}","LoadAjax('eventsAuditD','$page?events-list=yes&key={$_GET["key"]}&f={$_GET["f"]}&n=$next&search={$_GET["search"]}');");
$button_back=button("{back}","LoadAjax('eventsAuditD','$page?events-list=yes&key={$_GET["key"]}&f={$_GET["f"]}&n=$back&search={$_GET["search"]}');");

$html=$html."<table style='width:100%'>
<tr>
	<td align='left' width=50%>$button_back</td>
	<td align='right' width=50%>$button_next</td>
</tr>
</table>";
	

	
	echo $tpl->_ENGINE_parse_body($html);		
	
}

function reindex(){
	
	$sql="TRUNCATE TABLE `auditd_files`";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		echo $q->mysql_error;
		return;
	}
	$sock=new sockets();
	$sock->SET_INFO("AuditdTimeCode","0");
	$sock->getFrameWork("cmd.php?auditd-force=yes");
	
	
}

function importnow(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?auditd-force=yes");	
	
}


?>