<?php
include_once(dirname(__FILE__)."/ressources/class.user.inc");
include_once(dirname(__FILE__)."/ressources/class.templates.inc");


if(isset($_GET["popup-index"])){index();exit;}
if(isset($_GET["RsyncDaemonEnable"])){save_parameters();exit;}
if(isset($_GET["rsync-status"])){echo status();exit;}

if(isset($_GET["js-logs"])){echo js_logs();exit;}
if(isset($_GET["popup-logs"])){echo popup_logs();exit;}
if(isset($_GET["rsync-logs"])){echo rsync_logs();exit;}

js();


function js_logs(){
$page=CurrentPageName();
$prefix="events_".str_replace(".","_",$page);
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{APP_RSYNC}');
	
	$users=new usersMenus();
	if(!$users->AsAnAdministratorGeneric){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}

$html="
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
		RsyncLogs();
		{$prefix}demarre(); 
		                              
   }
}

function {$prefix}Loadpage(){
	YahooWin5('650','$page?popup-logs=yes','$title');
	setTimeout('RsyncLogs()',700);
	setTimeout('{$prefix}demarre()',1000);
	
	
	}
function RsyncLogs(){
	LoadAjax('rsynclogs','$page?rsync-logs=yes');
}
	
{$prefix}Loadpage();

";
	
	echo $html;	
	
	
}

function rsync_logs(){
	
	$sock=new sockets();
	$datas=$sock->getfile("RsyncdEvents");
		$tbl=explode("\n",$datas);
		$tbl=array_reverse ($tbl, TRUE);
		$img="img/fw_bold.gif";		
		while (list ($num, $val) = each ($tbl) ){
			if(trim($val)==null){continue;}
			
			$html=$html . "
			<div style='black;margin-bottom:1px;padding:2px;border-bottom:1px dotted #CCCCCC;border-left:5px solid #CCCCCC;width:100%;margin-left:-30px'>
					<table style='width:99%'>
					<tr>
					<td><code style='font-size:10px'>$val</code></td>
					</tr>
					</table>
			</div>";
			
		}	
	
	echo $html;
	
	
}

function popup_logs(){
	
	$html="<H1>{events}</H1>
	<br>
	" . RoundedLightWhite("<div style='width:99.8%;height:300px;overflow:auto' id='rsynclogs'></div>");

	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function js(){
$page=CurrentPageName();
$prefix=str_replace(".","_",$page);
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{APP_RSYNC}');
	
	$users=new usersMenus();
	if(!$users->AsAnAdministratorGeneric){
		$error=$tpl->_ENGINE_parse_body("{ERROR_NO_PRIVS}");
		echo "alert('$error')";
		die();
	}

$html="
var {$prefix}timerID  = null;
var {$prefix}tant=0;
var {$prefix}reste=0;
var stopm='$stop_monitor';


function {$prefix}demarre(){
	{$prefix}tant = {$prefix}tant+1;
	{$prefix}reste=20-{$prefix}tant;
	if(!YahooWin4Open()){return false;}
	
	if ({$prefix}tant < 10 ) {                           
	{$prefix}timerID =setTimeout(\"{$prefix}demarre()\",2000);
      } else {
		{$prefix}tant = 0;
		RsyncStatus();
		{$prefix}demarre(); 
		                              
   }
}

function {$prefix}Loadpage(){
	YahooWin4('700','$page?popup-index=yes','$title');
	setTimeout('RsyncStatus()',900);
	setTimeout('{$prefix}demarre()',1000);
	
	
	}
	

	var x_SaveRsyncConf= function (obj) {
		var response=obj.responseText;
		if (response.length>0){alert(response);}
		 {$prefix}Loadpage();
		}	
		
function RsyncStatus(){
	LoadAjax('rsyncstatus','$page?rsync-status=yes');
}
	
function SaveRsyncConf(){
	ParseForm('rsyncsrverForm','$page',false,false,false,'rsyncsrver','',x_SaveRsyncConf);
}

 {$prefix}Loadpage();

";
	
	echo $html;
}

function index(){
	
	
	$users=new usersMenus();
	
	$sock=new sockets();
	$RsyncDaemonEnable=$sock->GET_INFO("RsyncDaemonEnable");
	$RsyncBwlimit=$sock->GET_INFO("RsyncBwlimit");
	$RsyncPort=$sock->GET_INFO("RsyncPort");
	$RsyncMaxConnections=$sock->GET_INFO("RsyncMaxConnections");
	$RsyncStoragePath=$sock->GET_INFO("RsyncStoragePath");
	$RsyncEnableStunnel=$sock->GET_INFO("RsyncEnableStunnel");
	
	if($RsyncBwlimit==null){$RsyncBwlimit="1000";}
	if($RsyncPort==null){$RsyncPort="873";}
	if($RsyncMaxConnections==null){$RsyncMaxConnections="4";}
	if($RsyncPort==null){$RsyncPort="873";}
	if($RsyncStoragePath==null){$RsyncStoragePath="/var/spool/rsync";}
	if($RsyncEnableStunnel==null){$RsyncEnableStunnel="0";}	
	if($RsyncEnableStunnelPort==null){$RsyncEnableStunnelPort=8000;}		
	
	$enable=Paragraphe_switch_img_32('{ENABLE_RSYNC_SERVER}','{ENABLE_RSYNC_SERVER_TEXT}','RsyncDaemonEnable',$RsyncDaemonEnable,'{enable_disable}',220);
	
	
	if($users->stunnel4_installed){
		$stunnel="	<tr>
		<td class=legend nowrap>{RsyncEnableStunnel}:</td>
		<td>" . Field_numeric_checkbox_img('RsyncEnableStunnel',$RsyncEnableStunnel,"{enable_disable}")."</td>
	</tr>
	<tr>
		<td class=legend nowrap>{RsyncEnableStunnelPort}:</td>
		<td>" . Field_text("RsyncEnableStunnelPort",$RsyncEnableStunnelPort,"width:60px")."</td>
	</tr>";
		
	}else{
		$stunnel=null;
	}
	
	$view_events=Paragraphe("routing-domain-relay-events.png","{APP_RSYNC_SERVER_LOG}","{APP_RSYNC_SERVER_LOG_TEXT}","javascript:Loadjs('rsync.server.php?js-logs=yes')");
	
	
	$form="
	<table style='width:100%'>
	<tr>
		<td class=legend nowrap>{RsyncBwlimit}:</td>
		<td>" . Field_text("RsyncBwlimit",$RsyncBwlimit,"width:60px")."&nbsp;KBytes</td>
	</tr>
$stunnel	
	<tr>
		<td class=legend nowrap>{listen_port}:</td>
		<td>" . Field_text("RsyncPort",$RsyncPort,"width:60px")."</td>
	</tr>	
	<tr>
		<td class=legend nowrap>{max_connections}:</td>
		<td>" . Field_text("RsyncMaxConnections",$RsyncMaxConnections,"width:20px")."</td>
	</tr>	
	<tr>
		<td class=legend nowrap>{storage_path}:</td>
		<td>" . Field_text("RsyncStoragePath",$RsyncStoragePath,"width:120px")."&nbsp;" .button_browse("RsyncStoragePath")."</td>
	</tr>			
	</table>
";
	
	
	
	
	$html="<H1>{APP_RSYNC}</H1>
	<div id='rsyncsrver'>
	<form name='rsyncsrverForm'>
	<p class=caption>{APP_RSYNC_SERVER_TEXT}</p>
	<table style='width:100%'>
	<tr>
	<td valign='top'>$enable</td>
	<td valign='top'>$form</td>
	</tr>
	<tr>
	<td colspan=2><hr></td></tr>
	<tr>
	<td valign='top'><div id='rsyncstatus' style='margin:4px'></div></td>
	<td valign='top'>$view_events</td>
	</tr>	
	<tr><td colspan=2 align='right'>
		<hr>
		<input type='button' OnClick=\"javascript:SaveRsyncConf();\" value='{edit}&nbsp;&raquo;'>
	</td>
	</tr>
	</table>
	
	</form>
	</div>
	";
	
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
}

function save_parameters(){
	$sock=new sockets();
	
	while (list ($num, $ligne) = each ($_GET) ){
		$sock->SET_INFO($num,$ligne);
		
	}
	
	$sock->getFrameWork("cmd.php?RestartRsyncServer=yes");
}

function status(){
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString($sock->getfile('rsyncstatus',$_GET["hostname"]));
	$status=DAEMON_STATUS_ROUND("APP_RSYNC",$ini)."<br>".DAEMON_STATUS_ROUND("APP_RSYNC_STUNNEL",$ini);
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($status);	
	}
	

?>