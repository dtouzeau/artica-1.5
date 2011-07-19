<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.samba.inc');
	include_once('ressources/class.user.inc');
	include_once('ressources/class.kav4samba.inc');
	
	

	
	if(!CheckSambaRights()){$tpl=new templates();echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";die();exit();}
	if(isset($_GET["ajax"])){popup();exit;}
	if(isset($_GET["events"])){popup_list();exit;}
	if(isset($_GET["download-file"])){popup_download();exit;}
	if(isset($_GET["delete-file-logs"])){popup_delete();exit;}
	popup();


function popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	
	if(isset($_GET["in-line"])){
		echo "document.getElementById('BodyContent').innerHTML='<center><img src=img/wait_verybig.gif></center>';
		$('#BodyContent').load('$page');";
		return;
		
	}
	
	if(isset($_GET["ajax"])){
		$title=$tpl->_ENGINE_parse_body("{APP_SAMBA}::{events}");
		echo "YahooWin5(650,'$page','$title');";
		exit;
	}
	
	
	$start="SambaRefreshEvents();";
	
	$title=$tpl->_ENGINE_parse_body("{download}");
	
	$scripts="
		
	
	
		function SambaRefreshEvents(){
			LoadAjax('samba-events-list','$page?events=yes');
		}
		
		function SambaDownloadEvent(filename){
			YahooWin3(430,'$page?download-file='+filename,'$title '+filename);
		}
		
		var x_SambaDeleteEvent= function (obj) {
				var results=obj.responseText;
				if(results.length>0){alert(results);}
				SambaRefreshEvents();
		}
			
		
		function SambaDeleteEvent(filename){
		 var XHR = new XHRConnection();
		 XHR.appendData('delete-file-logs',filename);
		 XHR.sendAndLoad('$page', 'GET',x_SambaDeleteEvent);
		}
		
		$start";
	
	
	
	$html="<div id='samba-events-list' style='width:100%;height:350px;overflowl:auto'></div>
	
	<script>
		$scripts
	</script>
	";
	
	echo $html;
	
}
	
function popup_list(){

	$tpl=new templates();
	$sock=new sockets();
	$array=unserialize(base64_decode($sock->getFrameWork("cmd.php?samba-events-list=yes")));
	if(!is_array($array)){
		echo $tpl->_ENGINE_parse_body("<H2>{error_no_datas}</H2>");
		exit;
	}
	
	$html="
	<table style='width:100%'>
	<tr>
		<th>&nbsp;</th>
		<th>{filename}</th>
		<th>{size}</th>
		<th>&nbsp;</th>
	</tr>
	";
	
	while (list ($filename, $size) = each ($array) ){
		$img="30-computer.png";
		if(preg_match("#log\.(.+)$#",$filename,$re)){$hostname=$re[1];}
		$text_addon=null;
		if(preg_match("#winbindd#",$filename)){$img="30-logs.png";$text_addon=" ({APP_SAMBA_WINBIND})";}
		if(preg_match("#nmbd#",$filename)){$img="30-logs.png";$text_addon=" ({APP_SAMBA_NMBD})";}
		if(preg_match("#smbd#",$filename)){$img="30-logs.png";$text_addon=" ({APP_SAMBA_SMBD})";}
		
		$html=$html."
		<tr ". CellRollOver().">
			<td width=1%>". imgtootltip($img,"{download}","SambaDownloadEvent('$filename')")."</td>
			<td><strong style='font-size:13px'>". texttooltip("$hostname$text_addon","{download}","SambaDownloadEvent('$filename')",null,0,"font-size:13px")."</td>
			<td><strong style='font-size:13px'>". FormatBytes($size)."</td>
			<td width=1%>".imgtootltip("delete-30.png","{delete}","SambaDeleteEvent('$filename')")."</td>
		</tr>
		";
	}
	$html=$html."</table>";
	echo $tpl->_ENGINE_parse_body($html);
	
}


function popup_download(){
	$filename=$_GET["download-file"];
	$sock=new sockets();
	$tpl=new templates();
	$filename=base64_decode($sock->getFrameWork("cmd.php?samba-move-logs=". base64_encode($filename)));
	if($filename==null){
		echo $tpl->_ENGINE_parse_body("<H2>{ERROR_COMPRESSOR}</H2>");exit;
	}
	
	if(!is_file("ressources/logs/$filename")){
		echo $tpl->_ENGINE_parse_body("<H2>{ERROR_COMPRESSOR}</H2>");exit;
	}
	
	$size=FormatBytes(@filesize("ressources/logs/$filename")/1024);
	
	$html="<p>&nbsp;</p>
	<center style='margin:5px'>
	<span style='border-bottom:1px solid black'>
	<a href='ressources/logs/$filename' style='font-size:16px'>{download} $filename ($size)</a>
	</span>
	</center>
	";
	echo $tpl->_ENGINE_parse_body($html);
}

function popup_delete(){
	$filename=$_GET["delete-file-logs"];
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?samba-delete-logs=". base64_encode($filename));	
	
}

	
?>