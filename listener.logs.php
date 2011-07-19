<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
session_start();
if(!isset($_SESSION["uid"])){echo "<H1>Session Out</H1>";exit;}
include_once("ressources/class.templates.inc");
include_once("ressources/class.ldap.inc");
include_once("ressources/class.sockets.inc");

if(isset($_GET["maillog"])){echo maillog();exit;}

loadconsole();

function loadconsole(){
$html="
<table style='width:99%;border:1px solid #CCCCCC'>
<tr>
	<td width='1%' valign='top'><img src='img/log-monitor-128.gif'>".Head()."</td>
	<td valign='top'><div id='logs_windows'>" . maillog() . "</div></td>
</tr>
</table>
<script>Start();</script></b>";


$tpl=new template_users('logs Monitor',$html,1,1,1);
echo $tpl->web_page;	
	
}



function maillog(){
$user=new usersMenus();
$tpl=new Templates();
if($user->AsPostfixAdministrator==false){echo DIV_SHADOW($tpl->_ENGINE_parse_body('{no privileges}'));exit;}
	$sock=new sockets();
	
	
	
	if($_GET["maillog_filter"]<>null){
		switch ($_GET["maillog_filter"]) {
			case 'articaweb':
				$datas=file_get_contents(dirname(__FILE__) . '/ressources/logs/artica-web.log');
				break;
			case 'procmail':
				$datas=$sock->getfile('procmail_logs');break;
				
			default:$datas=$sock->getfile('maillog:'. $_GET["maillog_filter"]);break;
		}

		
	
	
	
	}else{
	$tbl=unserialize(base64_decode($sock->getFrameWork('cmd.php?postfix-tail=yes')));
	}

	
	$datas=htmlentities($datas);
	$tbl=explode("\n",$datas);
	$tbl=array_reverse ($tbl, TRUE);
	$html="<table style='width:100%'>";
while (list ($num, $val) = each ($tbl) ){
		if(trim($val)<>null){
		if($class=="rowA"){$class="rowB";}else{$class="rowA";}
		$html=$html . "<tr>
		<td width=1% style='border-bottom:1px dotted #CCCCCC' align='center'><img src='" . statusLogs($val) . "'></td>
		<td width=99%' style='border-bottom:1px dotted #CCCCCC;font-size:10px;'>$val</td>
		</tr>
		";
		}
		
	}
	
$html= $html . "</table>";	
return  $html;


	
	
}

function Head(){
	$menu=new usersMenus();
	if(!isset($_GET["Refresh"])){$_GET["Refresh"]=5;}
	if($_GET["Refresh"]<5){$_GET["Refresh"]=5;}
	
	$pages=new HtmlPages();
	$filter[""]="[filter]";
	$filter["postfix"]="postfix";

	if($pages->fetchmail_installed==true){
		$filter["fetchmail"]="fetchmail";
		}
	if($pages->cyrus_imapd_installed==true){
		$filter["cyrus"]="cyrus";
	}
	
	if($pages->procmail_installed==true){
		$filter["procmail"]="procmail";
	}
	
	if($menu->AsArticaAdministrator==true){
		$filter["articaweb"]="Artica web";
	}
	
	
	
	$filters=Field_array_Hash($filter,'maillog_filter',$_GET["maillog_filter"],"SendRequests()");
	
	
$html="
<input type='hidden' name='reduced' id='reduced' value='{$_GET["reduced"]}'>
<table style='width:100%'>
<tr>
	<td><strong>{counter}</strong>:</td>
	<td><input type='text' id='timertext' style='width:20px;color:#CCCCCC;border:0px'></td>
</tr>
<tr>
	
	<td colspan=2><strong style='font-size:10px'>{refresh time} (seconds)</strong>:</td>
</tr>
<tr>
	<td colspan=2><input type='text' id='ExecSecs' value='{$_GET["Refresh"]}' style='width:30px'></td>
</tr>
<tr>
	
	<td colspan=2><strong style='font-size:10px'>{filter}</strong>:</td>
</tr>
<tr>
	<td colspan=2>$filters</td>
</tr>

</table>

"	;
return $html;
	
	
}

?>