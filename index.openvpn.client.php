<?php
session_start();
include_once('ressources/class.templates.inc');
include_once('ressources/class.users.menus.inc');
include_once('ressources/class.openvpn.inc');
include_once('ressources/class.system.network.inc');
include_once('ressources/class.mysql.inc');
$users=new usersMenus();
if(!$users->AsSystemAdministrator){die("alert('no access');");}

if(isset($_GET["tabs"])){tabs();exit;}
if(isset($_GET["status"])){status();exit;}
if(isset($_GET["events"])){events();exit;}
if(isset($_GET["build-client-events"])){events_details();exit;}
if(isset($_POST["ReconnectSession"])){ReconnectSession();exit;}
if(isset($_POST["DisableConnection"])){DisableConnection();exit;}
if(isset($_POST["EnableConnection"])){EnableConnection();exit;}
if(isset($_POST["DeleteConnection"])){DeleteConnection();exit;}



js();


function js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{connection}:{$_GET["cname"]}");
	echo "YahooWin3('680','$page?tabs=yes&ID={$_GET["ID"]}','$title');";
	
	
}


function status(){
	$page=CurrentPageName();
	$tpl=new templates();
	$OPENVPN_SERVER_CONNECT=$tpl->_ENGINE_parse_body("{OPENVPN_SERVER_CONNECT}");	
	$sql="SELECT * FROM vpnclient WHERE ID={$_GET["ID"]}";
	$q=new mysql();
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
	$infos="
	<table style='width:100%' class=form>
	<tr>
		<td class=legend style='font-size:14px'>ID:</td>
		<td><strong style='font-size:14px'>{$_GET["ID"]}</strong>
	</tr>	
	<tr>
		<td class=legend style='font-size:14px'>{connection_name}:</td>
		<td><strong style='font-size:14px'>{$ligne["connexion_name"]}</strong>
	</tr>
	<tr>
		<td class=legend style='font-size:14px'>{openvpn_server}:</td>
		<td><strong style='font-size:14px'>{$ligne["servername"]}:{$ligne["serverport"]}</strong>
	</tr>	
	
	
	";
	
	$sock=new sockets();
	$tbl=unserialize(base64_decode($sock->getFrameWork("cmd.php?openvpn-client-sesssions={$_GET["ID"]}")));
	
	while (list ($num, $line) = each ($tbl) ){
		if(preg_match("#(.+?),(.+?)$#",$line,$re)){
			if(preg_match("#bytes#",$re[1])){
				$re[2]=FormatBytes($re[2]/1024);
			}
			
			$re[1]=str_replace("Updated", "{updated_on}",$re[1]);
			
			$infos=$infos."<tr>
				<td class=legend style='font-size:14px'>{$re[1]}:</td>
				<td><strong style='font-size:14px'>{$re[2]}</strong></td>
			</tr>";
			
		}
	}
	
	$infos=$infos."</table>";
	
	
	$reload=LocalParagraphe("reconnect", "reconnect_openvpn_text", "ReconnectSession()", "48-refresh.png");
	if($ligne["enabled"]==1){
		$disable=LocalParagraphe("disable", "disable_openvpn_connection", "DisableConnection()", "shutdown-computer-48.png");
	}else{
		$disable=LocalParagraphe("activate", "activate_openvpn_connection", "EnableConnection()", "48-run.png");
	}
	$delete=LocalParagraphe("delete", "delete_openvpn_connection", "DeleteConnection()", "delete-48.png");
	
	$config=LocalParagraphe("parameters", "edit_openvpn_connection", "EditConnection()", "rouage-48.png");
	
	
	
	
	
	$html="
	<div id='client-status-id'>
	<table style='width:100%'>
	<tr>
		<td valign='top' width=100%'>$infos
		<div style='text-align:right'>". imgtootltip("refresh-24.png","{refresh}","RefreshTab('main_openvpn_clientconf')")."</div>
		
		</td>
		<td valign='top' width=1%><table class=form><tr><td>$config<br>$reload<br>$disable<br>$delete</td></tr></table></td>
	</tr>
	</table>
	</div>
	<script>
	
	var x_ReconnectSession= function (obj) {
			if(document.getElementById('main_openvpn_config')){RefreshTab('main_openvpn_config');}
			RefreshTab('main_openvpn_clientconf');
			if(document.getElementById('master-list')){RefreshOpenVPNMasterList();}
		}	
		
	var x_DeleteConnection= function (obj) {
			if(document.getElementById('main_openvpn_config')){RefreshTab('main_openvpn_config');}
			if(document.getElementById('master-list')){RefreshOpenVPNMasterList();}
			YahooWin3Hide();
		}	
		
	
	function ReconnectSession(){
				var XHR = new XHRConnection();
				XHR.appendData('ReconnectSession','yes');
				XHR.appendData('ID','{$_GET["ID"]}');
				AnimateDiv('client-status-id');
				XHR.sendAndLoad('$page', 'POST',x_ReconnectSession); 
		} 
		
	function DisableConnection(){
				var XHR = new XHRConnection();
				XHR.appendData('DisableConnection','yes');
				XHR.appendData('ID','{$_GET["ID"]}');
				AnimateDiv('client-status-id');
				XHR.sendAndLoad('$page', 'POST',x_ReconnectSession); 
		} 

	function EnableConnection(){
				var XHR = new XHRConnection();
				XHR.appendData('EnableConnection','yes');
				XHR.appendData('ID','{$_GET["ID"]}');
				AnimateDiv('client-status-id');
				XHR.sendAndLoad('$page', 'POST',x_ReconnectSession); 
		} 

	function DeleteConnection(){
				var XHR = new XHRConnection();
				XHR.appendData('DeleteConnection','yes');
				XHR.appendData('ID','{$_GET["ID"]}');
				AnimateDiv('client-status-id');
				XHR.sendAndLoad('$page', 'POST',x_DeleteConnection); 
		} 	

	function EditConnection(){
			Loadjs('openvpn.servers-connect.php?inline-js-id={$_GET["ID"]}');
		}		
		
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);	
	
}


function tabs(){
	$page=CurrentPageName();
	$tpl=new templates();	
	
	$array["status"]="{status}";
	$array["events"]="{events}";
	

	
	
	while (list ($num, $ligne) = each ($array) ){
		
		$tab[]="<li><a href=\"$page?$num=yes&ID={$_GET["ID"]}\"><span>$ligne</span></a></li>\n";
			
		}
	$tpl=new templates();
	
	

	$html="
		<div id='main_openvpn_clientconf' style='background-color:white;width:100%'>
		<ul>
		". implode("\n",$tab). "
		</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_openvpn_clientconf').tabs();
			});
		</script>
	
	";
		
	$tpl=new templates();
	$html=$tpl->_ENGINE_parse_body($html);	
	echo $html;
}


function events(){
	
	$page=CurrentPageName();
	
	$html="
	
	<div style='width:100%;height:750px;overflow:auto' id='build-client-events'></div>
	<script>
		LoadAjax('build-client-events','$page?build-client-events=yes&ID={$_GET["ID"]}');
	</script>
	
	
	
	";
		
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);		
	
}

function events_details(){
	$sock=new sockets();
	$page=CurrentPageName();
	$datas=unserialize(base64_decode($sock->getFrameWork("openvpn.php?client-events=yes&ID={$_GET["ID"]}")));
	$tpl=new templates();
	$tbl=array_reverse($datas);
	
$html=$tpl->_ENGINE_parse_body("<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th width=1%>". imgtootltip("refresh-24.png","{refresh}","LoadAjax('build-client-events','$page?build-client-events=yes&ID={$_GET["ID"]}');")."</th>
	<th>{events}</th>
	</tr>
</thead>
<tbody class='tbody'>");		
	
	while (list ($num, $ligne) = each ($tbl) ){
		if(trim($ligne)==null){continue;}
		if(strpos($ligne, "WWWWWW")>0){continue;}
		if(strpos($ligne, "WRWRWRW")>0){continue;}
		if(trim($ligne)=="WWWR"){continue;}
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		if(preg_match("#^([A-Za-z]+)\s+([A-Za-z]+)\s+([0-9]+)\s+([0-9\:]+)\s+([0-9]+)\s+(.+)#", $ligne,$re)){
			$ligne=$re[6];
			$time=$re[4];
		}
		
		
		
		$ligne=htmlentities($ligne);
		if(preg_match("#[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+#", $ligne,$re)){
			$ligne=str_replace($re[0], "<b>{$re[0]}</b>", $ligne);
		}
		
		
		$html=$html . "<tr class=$classtr>
		<td style='font-size:11px'>$time</td>
		<td><code style='font-size:11px'>$ligne</td>
		</tr>";
	}
	
	echo "$html</table>";
	
}
function LocalParagraphe($title,$text,$js,$img){
		$js=str_replace("javascript:","",$js);
		$id=md5($js);
		$img_id="{$id}_img";
		if(strpos($text,"}")==0){$text="{{$text}}";}
		
		
	$html="
	<table style='width:198px;'>
	<tr>
	<td width=1% valign='top'>" . imgtootltip($img,$text,"$js",null,$img_id)."</td>
	<td><strong style='font-size:12px;font-family:Verdana;letter-spacing:-1px'>{{$title}}</strong><div style='font-size:10px;font-family:Verdana;letter-spacing:-1px'>$text</div></td>
	</tr>
	</table>";
	

return "<div style=\"width:200px;margin:2px\" 
	OnMouseOver=\"javascript:ParagrapheWhiteToYellow('$id',0);this.style.cursor='pointer';\" 
	OnMouseOut=\"javascript:ParagrapheWhiteToYellow('$id',1);this.style.cursor='auto'\" OnClick=\"javascript:$js\">
  <b id='{$id}_1' class=\"RLightWhite\">
  <b id='{$id}_2' class=\"RLightWhite1\"><b></b></b>
  <b id='{$id}_3' class=\"RLightWhite2\"><b></b></b>
  <b id='{$id}_4' class=\"RLightWhite3\"></b>
  <b id='{$id}_5' class=\"RLightWhite4\"></b>
  <b id='{$id}_6' class=\"RLightWhite5\"></b></b>

  <div id='{$id}_0' class=\"RLightWhitefg\" style='padding:2px;'>
   $html
  </div>

  <b id='{$id}_7' class=\"RLightWhite\">
  <b id='{$id}_8' class=\"RLightWhite5\"></b>
  <b id='{$id}_9' class=\"RLightWhite4\"></b>
  <b id='{$id}_10' class=\"RLightWhite3\"></b>
  <b id='{$id}_11' class=\"RLightWhite2\"><b></b></b>
  <b id='{$id}_12' class=\"RLightWhite1\"><b></b></b></b>
</div>
";		
		
	
}

function ReconnectSession(){
	$sock=new sockets();
	
	$sock->getFrameWork("openvpn.php?client-reconnect=yes&ID={$_POST["ID"]}");
}

function DisableConnection(){
	if(!is_numeric($_POST["ID"])){return;}
	$sql="UPDATE vpnclient SET enabled=0 WHERE ID={$_POST["ID"]}";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("openvpn.php?restart-clients=yes");
	
	
	
}

function EnableConnection(){
	if(!is_numeric($_POST["ID"])){return;}
	$sql="UPDATE vpnclient SET enabled=1 WHERE ID={$_POST["ID"]}";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("openvpn.php?restart-clients=yes");	
}

function DeleteConnection(){
	if(!is_numeric($_POST["ID"])){return;}
	$sql="DELETE FROM vpnclient WHERE ID={$_POST["ID"]}";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("openvpn.php?restart-clients=yes");		
}

?>


