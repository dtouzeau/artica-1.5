<?php
include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.clamav.inc');
	include_once('ressources/class.artica.graphs.inc');
	include_once('ressources/class.mysql.inc');

	$user=new usersMenus();
	if($user->AsSystemAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["status"])){status();exit;}
	if(isset($_GET["popup"])){scan_engine_settings();exit;}
	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_GET["ClamavStreamMaxLength"])){save();exit;}
	if(isset($_GET["clamd-graphs"])){clamd_graphs();exit;}
	
js();


function status(){
	$tpl=new templates();
	$page=CurrentPageName();
	if(is_file("ressources/logs/global.status.ini")){
		
		$ini=new Bs_IniHandler("ressources/logs/global.status.ini");
	}else{
		writelogs("ressources/logs/global.status.ini no such file");
		$sock=new sockets();
		$datas=base64_decode($sock->getFrameWork('cmd.php?Global-Applications-Status=yes'));
		$ini=new Bs_IniHandler($datas);
	}
	
	$sock=new sockets();
	$datas=$sock->getFrameWork('cmd.php?refresh-status=yes');	
	$status=DAEMON_STATUS_ROUND("CLAMAV",$ini,null,1);
	$html="
	<div style='width:100%'>$status</div>
	<center style='margin-top:10px' id='clamd-graphs'></center>
	
	<script>
		LoadAjax('clamd-graphs','$page?clamd-graphs=yes');
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}


function js(){
	
	$tpl=new templates();
	$page=CurrentPageName();
	$title=$tpl->_ENGINE_parse_body("{APP_CLAMAV}");
	
	echo "YahooWin3('650','$page?tabs=yes','$title');";
	
}


function clamd_graphs(){
	$tpl=new templates();	
	$page=CurrentPageName();
	$q=new mysql();	
	$sql="SELECT AVG(rss) as rss,AVG(vm) as vm,MINUTE(zDate) as tdate,DATE_FORMAT(zDate,'%Y-%m-%d %H:%i') as tdar FROM clamd_mem WHERE zDate<DATE_FORMAT(NOW(),'%Y-%m-%d %H:%i') GROUP BY tdar";
	$results=$q->QUERY_SQL($sql,"artica_events");
	
	

	if(!$q->ok){echo "<H2>$q->mysql_error</H2><center style='font-size:11px'><code>$sql</code></center>";}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$xdata[]=$ligne["tdate"];
		$xdata2[]=$ligne["tdate"];
		$ydata[]=$ligne["rss"];
		$ydata2[]=$ligne["vm"];
	}
	
	$targetedfile="ressources/logs/".basename(__FILE__).".".__FUNCTION__.".clamd.1.". date('Y-m-d-H').".png";
	$gp=new artica_graphs();
	$gp->width=550;
	$gp->height=350;
	$gp->filename="$targetedfile";
	$gp->xdata=$xdata;
	$gp->ydata=$ydata;
	$gp->xdata2=$xdata2;
	$gp->ydata2=$ydata2;	
	$gp->y_title=null;
	$gp->x_title=$tpl->_ENGINE_parse_body("{minutes}");
	$gp->title=null;
	$gp->margin0=true;
	$gp->Fillcolor="blue@0.9";
	$gp->color="146497";

	$gp->line_green_double();
	if(!is_file($targetedfile)){writelogs("Fatal \"$targetedfile\" no such file!",__FUNCTION__,__FILE__,__LINE__);return;}	
	echo "<img src='$targetedfile' style='margin:5px'>";
	
	$sql="SELECT AVG(rss) as rss,AVG(vm) as vm,HOUR(zDate) as tdate,DATE_FORMAT(zDate,'%Y-%m-%d %H') as tdar FROM clamd_mem WHERE 
	zDate=DATE_FORMAT(NOW(),'%Y-%m-%d') GROUP BY tdar";
	$results=$q->QUERY_SQL($sql,"artica_events");
	
	$xdata=array();
	$xdata2=array();
	$ydata=array();
	$ydata2=array();

	if(!$q->ok){echo "<H2>$q->mysql_error</H2><center style='font-size:11px'><code>$sql</code></center>";}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$xdata[]=$ligne["tdate"];
		$xdata2[]=$ligne["tdate"];
		$ydata[]=$ligne["rss"];
		$ydata2[]=$ligne["vm"];
	}
	
	$targetedfile="ressources/logs/".basename(__FILE__).".".__FUNCTION__.".clamd.2.". date('Y-m-d-H').".png";
	$gp=new artica_graphs();
	$gp->width=550;
	$gp->height=350;
	$gp->filename="$targetedfile";
	$gp->xdata=$xdata;
	$gp->ydata=$ydata;
	$gp->xdata2=$xdata2;
	$gp->ydata2=$ydata2;	
	$gp->y_title=null;
	$gp->x_title=$tpl->_ENGINE_parse_body("{hours}");
	$gp->title=null;
	$gp->margin0=true;
	$gp->Fillcolor="blue@0.9";
	$gp->color="146497";

	$gp->line_green_double();
	if(!is_file($targetedfile)){writelogs("Fatal \"$targetedfile\" no such file!",__FUNCTION__,__FILE__,__LINE__);return;}	
	echo "<img src='$targetedfile' style='margin:5px'>";	
	
}
	


function tabs(){
	$tpl=new templates();	
	$page=CurrentPageName();
	$array["status"]='{status}';
	$array["popup"]='{parameters}';
	$array["clamav_unofficial"]='{clamav_unofficial}';
	while (list ($num, $ligne) = each ($array) ){
		
		if($num=="clamav_unofficial"){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"clamav.unofficial.php?popup=yes\"><span>$ligne</span></a></li>\n");
			continue;
		}
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_clamav style='width:100%;height:700px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_config_clamav\").tabs();});
		</script>";		
	
}



function scan_engine_settings(){
	$sock=new sockets();
	$tpl=new templates();
	$page=CurrentPageName();
	$ClamavStreamMaxLength=$sock->GET_INFO("ClamavStreamMaxLength");
	$ClamavMaxRecursion=$sock->GET_INFO("ClamavMaxRecursion");
	$ClamavMaxFiles=$sock->GET_INFO("ClamavMaxFiles");
	$ClamavMaxFileSize=$sock->GET_INFO("ClamavMaxFileSize");
	$PhishingScanURLs=$sock->GET_INFO("PhishingScanURLs");
	$ClamavMaxScanSize=$sock->GET_INFO("ClamavMaxScanSize");
	$ClamavRefreshDaemonTime=$sock->GET_INFO("ClamavRefreshDaemonTime");
	$ClamavRefreshDaemonMemory=$sock->GET_INFO("ClamavRefreshDaemonMemory");
	if(!is_numeric($ClamavRefreshDaemonTime)){$ClamavRefreshDaemonTime=60;}
	if(!is_numeric($ClamavRefreshDaemonMemory)){$ClamavRefreshDaemonMemory=350;}
	if($ClamavStreamMaxLength==null){$ClamavStreamMaxLength="12";}
	if(!is_numeric($ClamavMaxRecursion)){$ClamavMaxRecursion="5";}
	if(!is_numeric($ClamavMaxFiles)){$ClamavMaxFiles="10000";}
	if(!is_numeric($PhishingScanURLs)){$PhishingScanURLs="1";}
	if(!is_numeric($ClamavMaxScanSize)){$ClamavMaxScanSize="15";}
	if(!is_numeric($ClamavMaxFileSize)){$ClamavMaxFileSize="20";}
	
	$hoursEX[15]="15 {minutes}";
	$hoursEX[30]="30 {minutes}";
	$hoursEX[60]="1 {hour}";
	$hoursEX[120]="2 {hours}";
	$hoursEX[180]="3 {hours}";
	$hoursEX[420]="4 {hours}";
	$hoursEX[480]="8 {hours}";	
	
	$html="
	
	
	
	<div id='ffmcc3'>
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{srv_clamav.RefreshDaemon}:</td>
		<td style=';font-size:13px'>" . Field_array_Hash($hoursEX,"ClamavRefreshDaemonTime",$ClamavRefreshDaemonTime,'style:font-size:13px;padding:3px')."</td>
		<td>" . help_icon('{srv_clamav.RefreshDaemon_text}')."</td>
	</tr>
	<tr>
		<td class=legend>{refresh_daemon_MB}:</td>
		<td style=';font-size:13px'>" . Field_text("ClamavRefreshDaemonMemory",$ClamavRefreshDaemonMemory,'style:font-size:13px;padding:3px;width:50px')."&nbsp;MB</td>
		<td>" . help_icon('{srv_clamav.ClamavRefreshDaemonMemory}')."</td>
	</tr>			
	<tr>
		<td class=legend>{srv_clamav.StreamMaxLength}:</td>
		<td style=';font-size:13px'>" . Field_text('ClamavStreamMaxLength',$ClamavStreamMaxLength,'width:30px;font-size:13px;padding:3px')."&nbsp;M</td>
		<td>" . help_icon('{srv_clamav.StreamMaxLength_text}')."</td>
	</tr>
	<tr>
		<td class=legend>{srv_clamav.MaxObjectSize}:</td>
		<td style=';font-size:13px'>" . Field_text('ClamavMaxFileSize',$ClamavMaxFileSize,'width:30px;font-size:13px;padding:3px')."&nbsp;M</td>
		<td>" . help_icon('{srv_clamav.MaxObjectSize_text}')."</td>
	</tr>
	
	<tr>
		<td class=legend>{srv_clamav.MaxScanSize}:</td>
		<td style=';font-size:13px'>" . Field_text('ClamavMaxScanSize',$ClamavMaxScanSize,'width:30px;font-size:13px;padding:3px')."&nbsp;M</td>
		<td>" . help_icon('{srv_clamav.MaxScanSize_text}')."</td>
	</tr>	
	
	

	<tr>
		<td class=legend>{srv_clamav.ClamAvMaxFilesInArchive}:</td>
		<td style=';font-size:13px'>" . Field_text('ClamavMaxFiles',$ClamavMaxFiles,'width:60px;font-size:13px;padding:3px')."&nbsp;{files}</td>
		<td>" . help_icon('{srv_clamav.ClamAvMaxFilesInArchive}')."</td>
	</tr>	
	
	<tr>
		<td class=legend>{srv_clamav.MaxFileSize}:</td>
		<td style=';font-size:13px'>" . Field_text('MaxFileSize',$ClamavMaxFileSize,'width:30px;font-size:13px;padding:3px')."&nbsp;M</td>
		<td>" . help_icon('{srv_clamav.ClamAvMaxFileSizeInArchive}')."</td>
	</tr>

	<tr>
		<td class=legend>{srv_clamav.ClamAvMaxRecLevel}:</td>
		<td style=';font-size:13px'>" . Field_text('ClamavMaxRecursion',$ClamavMaxRecursion,'width:30px;font-size:13px;padding:3px')."</td>
		<td>" . help_icon('{srv_clamav.ClamAvMaxRecLevel}')."</td>
	</tr>
	<tr>
		<td class=legend>{srv_clamav.PhishingScanURLs}:</td>
		<td style=';font-size:13px'>" . Field_checkbox('PhishingScanURLs',1,$PhishingScanURLs)."</td>
		<td>" . help_icon('{srv_clamav.PhishingScanURLs_text}')."</td>
	</tr>
	
	
	<tr>
		<td colspan=3 align='right'><hr>
		". button("{apply}","SaveClamdInfos()")."
			
		</td>
	</tr>
	</table>
	</div>
<script>

var X_SaveClamdInfos= function (obj) {
		var results=trim(obj.responseText);
		if(results.length>0){alert(results);}
		RefreshTab('main_config_clamav');
	}	

	function SaveClamdInfos(){
		var XHR=XHRParseElements('ffmcc3');
		document.getElementById('ffmcc3').innerHTML='<center><img src=img/wait_verybig.gif></center>';   
		XHR.sendAndLoad('$page', 'GET',X_SaveClamdInfos);
	
	}
	
</script>	
	
";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}


function save(){
	$sock=new sockets();
	$tpl=new templates();
	$page=CurrentPageName();
	$sock->SET_INFO("ClamavStreamMaxLength",$_GET["ClamavStreamMaxLength"]);
	$sock->SET_INFO("ClamavMaxRecursion",$_GET["ClamavMaxRecursion"]);
	$sock->SET_INFO("ClamavMaxFiles",$_GET["ClamavMaxFiles"]);
	$sock->SET_INFO("ClamavMaxFileSize",$_GET["ClamavMaxFileSize"]);
	$sock->SET_INFO("PhishingScanURLs",$_GET["PhishingScanURLs"]);
	$sock->SET_INFO("ClamavMaxScanSize",$_GET["ClamavMaxScanSize"]);
	$sock->SET_INFO("ClamavRefreshDaemonTime",$_GET["ClamavRefreshDaemonTime"]);
	$sock->SET_INFO("ClamavRefreshDaemonMemory",$_GET["ClamavRefreshDaemonMemory"]);
	$sock->getFrameWork("cmd.php?clamd-reload=yes");
	$sock->getFrameWork("cmd.php?restart-artica-status=yes");		
	
	
	
}
