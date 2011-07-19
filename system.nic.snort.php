<?php
	if(isset($_GET["verbose"])){$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.system.network.inc');
	include_once('ressources/class.system.nics.inc');
	include_once('ressources/class.maincf.multi.inc');
	include_once('ressources/class.tcpip.inc');
	
		$usersmenus=new usersMenus();
		if($usersmenus->AsSystemAdministrator==false){exit;}
		if(isset($_GET["tabs"])){tabs();exit;}
		if(isset($_GET["status"])){status();exit;}
		if(isset($_POST["EnableSnort"])){EnableSnort();exit;}
		if(isset($_GET["snort-status"])){snort_status();exit;}
		if(isset($_GET["params"])){params();exit;}
		if(isset($_GET["events"])){events();exit;}
		if(isset($_GET["snort-query"])){snort_query();exit;}
		if(isset($_GET["snort-query-search"])){snort_query_search();exit;}
		if(isset($_GET["events-js"])){events_js();exit;}
		if(isset($_POST["SnortReconfigure"])){SnortReconfigure();exit;}
		
start();

function events_js(){
	$page=CurrentPageName();
	echo "
	AnimateDiv('BodyContent');
	$('#BodyContent').load('$page?events=yes&zoom=yes');";
	
}

function start(){
	
	$page=CurrentPageName();
	$html="<div id='snort-start-page' style='width:780px'></div>
	
	<script>
		LoadAjax('snort-start-page','$page?tabs=yes');
	</script>
		
	
	";
	echo $html;
}

function tabs(){
	$tpl=new templates();
	$page=CurrentPageName();
	$array["status"]="{status}";
	$array["params"]="{parameters}";
	$array["events"]="{events}";
	
	while (list ($num, $ligne) = each ($array) ){
		
		$html[]= "<li><a href=\"$page?$num=yes\"><span>". $tpl->_ENGINE_parse_body($ligne)."</span></a></li>\n";
	}
	
	
	echo "
	<div id='main_config_snort' style='width: 91%;margin:-8px;height:490px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>

	<script>
		$(document).ready(function() {
			$(\"#main_config_snort\").tabs();});
			
	</script>";		
}


function status(){
	$tpl=new templates();
	$page=CurrentPageName();	
	$sock=new sockets();
	$EnableSnort=$sock->GET_INFO("EnableSnort");
	
	$paragraphe=Paragraphe_switch_img("{enable_ids}","{enable_ids_text}","EnableSnort",$EnableSnort,null,335);
	
	$html="
	<table style='width:100%'>
	<tr>
	<td valign='top' width=1%><div id='snort-status'></div></td>
	<td valign='top' width=99%><div class=explain>{APP_SNORT_ABOUT}</div><br>$paragraphe<div style='text-align:right'><hr>". button("{apply}","SaveEnableSnort();")."</td>
	</tr>
	</table>
	<script>
	var x_SaveEnableSnort=function (obj) {
			var results=obj.responseText;
			if(results.length>10){alert(results);}			
			RefreshTab('main_config_snort');
		}	
		
		function SaveEnableSnort(){
				var XHR = new XHRConnection();
				XHR.appendData('EnableSnort',document.getElementById('EnableSnort').value);
 				AnimateDiv('snort-status');
    			XHR.sendAndLoad('$page', 'POST',x_SaveEnableSnort);
		}


	
	
	
	
		LoadAjax('snort-status','$page?snort-status=yes');
		
		
		
	</script>
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}
function snort_status(){
	$refresh="<div style='text-align:right;margin-top:8px'>".imgtootltip("refresh-24.png","{refresh}","RefreshTab('main_config_snort')")."</div>";
	$sock=new sockets();
	$tpl=new templates();
	$ini=new Bs_IniHandler();
	$page=CurrentPageName();	
	$datas=$sock->getFrameWork("cmd.php?snort-status=yes");
	writelogs(strlen($datas)." bytes for snort status",__CLASS__,__FUNCTION__,__FILE__,__LINE__);
	$ini->loadString(base64_decode($datas));
	$status[]=DAEMON_STATUS_ROUND("APP_SNORT",$ini,null,0);
	while (list ($index, $ligne) = each ($ini->_params) ){
		if(preg_match("#APP_SNORT:(.+)#",$index,$re)){
			$status[]=DAEMON_STATUS_ROUND("$index",$ini,"{nic}:{$re[1]}",0);
		}
		
	}
	
	$html=@implode("\n",$status).$refresh;
	echo $tpl->_ENGINE_parse_body($html);	
	
}
function EnableSnort(){
	
	$sock=new sockets();
	$sock->SET_INFO("EnableSnort",$_POST["EnableSnort"]);
	$sock->getFrameWork("cmd.php?restart-snort=yes");
}
function SnortReconfigure(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?restart-snort=yes");	
}

function params(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?snort-networks=yes");
	$SnortMaxMysqlEvents=$sock->GET_INFO("SnortMaxMysqlEvents");
	$tpl=new templates();
	$page=CurrentPageName();		
	$array=unserialize(@file_get_contents("ressources/logs/web/snort.networks"));
	$networs=Paragraphe("64-win-nic-loupe.png","{edit_networks}",'{edit_networks_text}',"javascript:ViewNetwork()","edit_networks",210);
	if(!is_numeric($SnortMaxMysqlEvents)){$SnortMaxMysqlEvents=700000;}
	$html="<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th colspan=2>{networks}</th>
	</tr>
</thead>
<tbody class='tbody'>";	

	while (list ($num, $val) = each ($array) ){
				if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
				if(trim($val)==null){continue;}
				$html=$html . "
				<tr class=$classtr>
					<td width=1%><img src='img/folder-network-32.png'></td>
					<td style='font-size:16px'><a href=\"javascript:blur();\" OnClick=\"javascript:ViewNetwork();\" style='font-size:16px;text-decoration:underline'>$val</a></td>
				</tr>";
			}
	
	
	
	$html=$html . "
	</tbody>
	</table>";
	$snortInterfaces=unserialize(base64_decode($sock->GET_INFO("SnortNics")));
	$html=$html."<p>&nbsp;</p
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th colspan=2>{nics}</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	
	while (list ($num, $val) = each ($snortInterfaces) ){
				if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
				if(trim($num)==null){continue;}
				$html=$html . "
				<tr class=$classtr>
					<td width=1%><img src='img/folder-network-32.png'></td>
					<td style='font-size:16px'>$num</td>
				</tr>";
			}	
			
	$others="<table style='width:100%' class=form>
			<tr>
				<td class=legend>{max_events_inmysql}:</td>
				<td>". Field_text("SnortMaxMysqlEvents",$SnortMaxMysqlEvents,"font-size:13px;width:90px")."</td>
			</tr>
			<tr>
				<td colspan=2 align='right'><hr>". button("{apply}","SaveSnortOtherConf()")."</td>
			</tr>
			</table>";
			
	$reboot=Paragraphe("64-refresh.png","{reconfigure_snort}","{reconfigure_snort_text}","javascript:SnortReconfigure()");		
	$html2="
	<div id='snort-settings'>
	<table style='width:100%'>
	<tr>
		<td valign='top' style='width:75%'>$html</table>$others</td>
		<td valign='top'>$reboot$networs</td>
	</tr>
	</table>
	<br>

	
	
	</div>
	
	<script>
	var x_SnortReconfigure=function (obj) {
			var results=obj.responseText;
			if(results.length>10){alert(results);}			
			RefreshTab('main_config_snort');
		}			
		
		
		function SnortReconfigure(){
				var XHR = new XHRConnection();
				XHR.appendData('SnortReconfigure',document.getElementById('EnableSnort').value);
 				AnimateDiv('snort-settings');
    			XHR.sendAndLoad('$page', 'POST',x_SnortReconfigure);		
		
		}
		
		Loadjs('computer-browse.php?no-start-js=yes');
		
	</script>	
	
	";
	
	echo $tpl->_ENGINE_parse_body($html2);
	
	
}

function events(){
	$height="550";
	if(isset($_GET["zoom"])){
		$zoom="&zoom=yes";
		$height="700";
	}
	$page=CurrentPageName();	
	$html="<div style='width:100%;height:{$height}px;overflow:auto' id='snort-logs'></div>
	<script>
		LoadAjax('snort-logs','$page?snort-query=yes$zoom');
	</script>
	
	
	";
	echo $html;
	
}

function snort_query(){
if(isset($_GET["zoom"])){
		$zoom="&zoom=yes";
		
	}
$page=CurrentPageName();	
$tpl=new templates();
$q=new mysql();
$sql="SELECT priority FROM snort GROUP BY priority ORDER BY priority";
$results=$q->QUERY_SQL($sql,"artica_events");
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
	$prio[$ligne["priority"]]=$ligne["priority"];
}

$sql="SELECT classification FROM snort GROUP BY classification ORDER BY classification";
$results=$q->QUERY_SQL($sql,"artica_events");
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
	$cl[$ligne["classification"]]=$ligne["classification"];
}

$prio[null]="{select}";
$cl[null]="{select}";

$html="<table style='width:70%;margin-bottom:10px'>
<tr>
	<td class=legend>{search}:</td>
	<td>". Field_text("snort-query-field",null,"font-size:14px;padding:3px",null,null,null,flase,"SnortQueryCheck(event)")."</td>
</tr>
<tr>
	<td class=legend>PRIO:</td>
	<td>". Field_array_Hash($prio,"prio",null,"style:font-size:14px;padding:3px")."</td>
</tr>
<tr>
	<td class=legend>&nbsp;</td>
	<td>". Field_array_Hash($cl,"classification",null,"style:font-size:11px;padding:3px")."</td>
</tr>
<tr><td colspan=2 align='right'><hr>". button("{search}","SnortQuery()")."</td></tr>
</table>
<div id='snort-list'></div>

<script>
	function SnortQueryCheck(e){if(checkEnter(e)){SnortQuery();}}
	
	function SnortQuery(){
		var se=escape(document.getElementById('snort-query-field').value);
		var prio=document.getElementById('prio').value;
		var classification=escape(document.getElementById('classification').value);
		LoadAjax('snort-list','$page?snort-query-search='+se+'&prio='+prio+'$zoom&classification='+classification);
	
	}
	
	SnortQuery();
</script>

";
	echo $tpl->_ENGINE_parse_body($html);
	
}

function snort_query_search(){
	$page=CurrentPageName();	
	$tpl=new templates();	
	include_once(dirname(__FILE__) . '/ressources/class.rtmm.tools.inc');
	$limit=100;
	if(isset($_GET["zoom"])){$limit=200;}
	if(strlen($_GET["snort-query-search"])>1){
		$search=$_GET["snort-query-search"];
		$search="*$search*";
		$search=str_replace("*","%",$search);
		$search=str_replace("%%","%",$search);
	}
	if(is_numeric($_GET["prio"])){$prio=" AND priority={$_GET["prio"]}";}
	if(strlen($_GET["classification"])>5){$classification=" AND classification='{$_GET["classification"]}'";}
	
	if($search<>null){
	$quersearch="AND (hostname LIKE '$search'$prio$classification) 
	OR (ipaddr LIKE '$search'$prio$classification) 
	OR (port LIKE '$search'$prio$classification) 
	OR (infos LIKE '$search'$prio$classification)";
	}else{
		$quersearch="$classification$prio";
	}
	
	$sql="SELECT * FROM snort WHERE 1 $quersearch ORDER BY zDate DESC LIMIT 0,$limit";
	
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}	
	$events=$q->COUNT_ROWS("snort","artica_events");
	$events=FormatNumber($events,0,'.',' ',3);
	
	
	
$html="
<p>&nbsp;</p>
<div style='text-align:right'><i style='font-size:13px;font-weight:bold'>$events {events}&nbsp;|&nbsp;{your_ip_address}:{$_SERVER["REMOTE_ADDR"]} (".gethostbyaddr($_SERVER["REMOTE_ADDR"]).")&nbsp;&nbsp;</i></div>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th width=1%>&nbsp;</th>
		<th width=1%>{date}</th>
		<th>PRIO</th>
		<th>{hostname} $search</th>
		<th>{proto}</th>
		
	</tr>
</thead>
<tbody class='tbody'>";	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	
	
	if($ligne["success"]==1){$img='fleche-20-right.png';}else{$img='fleche-20-red-right.png';}
	$flag=imgtootltip(GetFlags($ligne["country"]),$ligne["country"],null);
	$html=$html."
	<tr  class=$classtr>
	<td style='font-size:12px;font-weight:bold' width=1%>$flag</td>
	<td style='font-size:12px;font-weight:bold'  width=1% nowrap>{$ligne["zDate"]}</a></td>
	<td style='font-size:12px;font-weight:bold' width=1% align='center'>{$ligne["priority"]}</a></td>
	<td style='font-size:12px;font-weight:bold' nowrap>{$ligne["hostname"]} [{$ligne["ipaddr"]}]</a></td>
	<td style='font-size:12px;font-weight:bold' nowrap>{port}:{$ligne["port"]} [{$ligne["proto"]}]</a></td>
	</tr>
	<tr  class=$classtr>
	<td>&nbsp;</td>
	<td colspan=4 style='font-size:12px'>({$ligne["classification"]}) <i>{$ligne["infos"]}</i></td>
	</tr>";
	}
	
$html=$html."</table>\n";


echo $tpl->_ENGINE_parse_body($html);return;	
	
}
function FormatNumber($number, $decimals = 0, $thousand_separator = '&nbsp;', $decimal_point = '.'){ 
	$tmp1 = round((float) $number, $decimals);
  while (($tmp2 = preg_replace('/(\d+)(\d\d\d)/', '\1 \2', $tmp1)) != $tmp1)
    $tmp1 = $tmp2;
  return strtr($tmp1, array(' ' => $thousand_separator, '.' => $decimal_point));
} 

?>