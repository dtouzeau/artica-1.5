<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.samba.inc');
	if(isset($_GET["debug-page"])){ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);$GLOBALS["VERBOSE"]=true;}
	$users=new usersMenus();
	if(!$users->AsSambaAdministrator){die("<H1>EXIT</H1>");}
	
	if(isset($_GET["in-line"])){js_inline();exit;}
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_GET["config"])){config();exit;}
	if(isset($_GET["browse-events-list"])){events_list();exit;}
	if(isset($_POST["SetLogLevel"])){SetLogLevel();exit;}
	
function js_inline(){
	$page=CurrentPageName();
	echo "$('#BodyContent').load('$page?tabs=yes');";	
}

function tabs(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$array["popup"]="Watch";
	$array["config"]="{config}";
		
	while (list ($num, $ligne) = each ($array) ){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_watch_samba style='width:100%;height:750px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_watch_samba').tabs();
			});
		</script>";		
	
	
}

function config(){
	$smb=new samba();
	$sock=new sockets();
	$page=CurrentPageName();
	$tpl=new templates();	
	$hostname=$smb->main_array["global"]["netbios name"];
	$datas=unserialize(base64_decode($sock->getFrameWork("samba.php?testparm=$hostname")));
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th>{config}</th>
	</tr>
</thead>
<tbody class='tbody'>";		

	
while (list ($num, $ligne) = each ($datas) ){
		if(trim($ligne)==null){continue;}
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$ligne=str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $ligne);
		$ligne=str_replace("  ", "&nbsp;&nbsp;&nbsp;", $ligne);
		$html=$html."
		<tr class=$classtr>
			<td style='font-size:12px;font-weight:bold;height:auto'>{$ligne}</td>
		</tr>
		";
	}
	
	$html=$html."</table>";
echo $tpl->_ENGINE_parse_body($html);	
	
}


function popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$smb=new samba();
	for($i=1;$i<11;$i++){
		$logs[$i]=$i;
	}
	
	$loglevel=Field_array_Hash($logs, "smb-logs-level",$smb->main_array["global"]["log level"],"style:font-size:14px");
	
	
	$html="
	
	<center>
	<table style='width:70%' class=form>
	<tr>
		<td class=legend>{events}:</td>
		<td>". Field_text("browse-events-search",null,"font-size:14px;padding:3px",null,null,null,false,"EventsSambaSearchCheck(event)")."</td>
		<td class=legend>{rows}:</td>
		<td>". Field_text("browse-events-rows",500,"font-size:14px;padding:3px;width:40px",null,null,null,false,"EventsSambaSearchCheck(event)")."</td>
		<td>". button("{search}","BrowseEventsSearch()")."</td>
	</tr>
	</table>
	</center>
	<div id='browse-events-list' style='width:100%;height:450px;overflow:auto;text-align:center'></div>
	<center>
	<table style='width:70%' class=form>
	<tr>
		<td class=legend>{log_level}:</td>
		<td>$loglevel</td>
		<td>". button("{apply}","SetLogLevel()")."</td>
	</tr>
	</table>	
</center>
<script>
		function EventsSambaSearchCheck(e){
			if(checkEnter(e)){BrowseEventsSearch();}
		}
		
		function BrowseEventsSearch(){
			var se=escape(document.getElementById('browse-events-search').value);
			var row=document.getElementById('browse-events-rows').value
			LoadAjax('browse-events-list','$page?browse-events-list=yes&search='+se+'&field={$_GET["field"]}&rows='+row);
		}
		
		var x_SetLogLevel=function (obj) {
		    	BrowseEventsSearch();

		}
	
	
		function SetLogLevel(){
			var XHR = new XHRConnection();
			XHR.appendData('SetLogLevel',document.getElementById('smb-logs-level').value);
    		AnimateDiv('browse-events-list');
    		XHR.sendAndLoad('$page', 'POST',x_SetLogLevel);
		}
			
	BrowseEventsSearch();
</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
}

function events_list(){
	$page=CurrentPageName();
	$tpl=new templates();	
	if(!is_numeric($_GET["rows"])){$_GET["rows"]=500;}
	$se=urlencode(base64_encode("*".$_GET["search"]."*"));
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->getFrameWork("samba.php?smbd-logs=yes&search=$se&rows={$_GET["rows"]}")));
	krsort($datas);
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th>{events}&nbsp;|&nbsp;{$_GET["search"]}&nbsp;|&nbsp;". count($datas)."&nbsp;rows</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	
	while (list ($num, $ligne) = each ($datas) ){
		if(trim($ligne)==null){continue;}
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$ligne=str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $ligne);
		$ligne=str_replace("  ", "&nbsp;&nbsp;&nbsp;", $ligne);
		$html=$html."
		<tr class=$classtr style='height:auto'>
			<td style='font-size:12px;font-weight:bold;height:auto'>{$ligne}</td>
		</tr>
		";
	}
	
	$html=$html."</table>";
echo $tpl->_ENGINE_parse_body($html);
	
	
}

function SetLogLevel(){
	$smb=new samba();
	$smb->main_array["global"]["log level"]=$_POST["SetLogLevel"];
	$smb->SaveToLdap();
	
}