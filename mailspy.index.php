<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');

	
	$user=new usersMenus();
	if($user->AsPostfixAdministrator==false){header('location:users.index.php');exit();}
	

	if(isset($_GET["main"])){main_switch();exit;}
	if(isset($_GET["status"])){status();exit;}
	if(isset($_GET["ViewReportPage"])){echo reports();exit;}

main_page();

function mailspy_main_tabs(){
	if(!isset($_GET["main"])){$_GET["main"]="mailspylogs";};
	if($_GET["hostname"]==null){$hostname=$users->hostname;$_GET["hostname"]=$hostname;}else{$hostname=$_GET["hostname"];}
	$page=CurrentPageName();
	$array["mailspylogs"]='{realtime_events}';
	$array["reports"]='{reports}';
	while (list ($num, $ligne) = each ($array) ){
		if($_GET["main"]==$num){$class="id=tab_current";}else{$class=null;}
		$html=$html . "<li><a href=\"javascript:LoadAjax('main_config','$page?main=$num')\" $class>$ligne</a></li>\n";
			
		}
		
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body("<div id=tablist>$html</div>");		
}

function main_switch(){
	$tabs=mailspy_main_tabs();
	if($_GET["main"]=="emails_received"){$tabs=null;}
	switch ($_GET["main"]) {
		case "mailspylogs":echo $tabs.mailspylogs();exit;break;
		case "reports":echo $tabs."<div id='allreports' style='width:700px'>".reports()."</div>";exit;break;
		case "conf":echo main_conf();exit;break;
		case "connections":
		default:echo $tabs.mailspylogs();break;
	}
	
	
}

	
function main_page(){
	
$page=CurrentPageName();
	if($_GET["hostname"]==null){
		$user=new usersMenus();
		$_GET["hostname"]=$user->hostname;}
if(isset($_GET["table"])){
	$main="reports";
}else{$main="default";}
	$html=
"
<script language=\"JavaScript\">       
var timerID  = null;
var timerID1  = null;
var tant=0;
var reste=0;

function demarre(){
   tant = tant+1;
   reste=5-tant;
	if (tant < 5 ) {                           
      timerID = setTimeout(\"demarre()\",3000);
      } else {
               tant = 0;
               ChargeLogs();
               demarre();                                //la boucle demarre !
   }
}


function ChargeLogs(){
	LoadAjax('status','$page?status=yes');
	if(document.getElementById('spyrevents')){
		LoadAjax('main_config','mailspy.index.php?main=mailspylogs')
	}
	
	
	
	}
</script>		
	
	<table style='width:100%'>
	<tr>
	<td width=50% valign='top'><img src='img/300-milterspy.png'></td>
	<td valign='top'>
		<div id='status'></div>
		<br>
		<p class='caption'>{APP_MAILSPY_DEFS}</p>
	</td>
	</tr>
	<tr>
		<td colspan=2 valign='top'>
			<div id='main_config'></div>
		</td>
	</tr>
	</table>
	<script>demarre();ChargeLogs();LoadAjax('main_config','$page?main=$main');</script>
	<script>
	
	";
$cfg["LANG_FILE"][]="postfix.plugins.php";
	$tpl=new template_users('{APP_MAILSPY}',$html,0,0,0,0,$cfg);
	
	echo $tpl->web_page;
	
	
	
}

function status(){
	$tpl=new templates();
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$ini->loadString($sock->getfile('mailspystatus'));
	$status=DAEMON_STATUS_ROUND("MAILSPY",$ini,null);
	echo $tpl->_ENGINE_parse_body($status);	
}

function mailspylogs(){
	$tpl=new templates();
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$datas=$sock->getfile('mailspylogs');	
	$title="<H3>{realtime_events}</h3>";
	$tbl=explode("\n",$datas);
	$tbl=array_reverse ($tbl, TRUE);		
//08-12-22 22:09	time=1229983741	from=<mbr+vUHCrR4wQftTwqSzKtzLw35In-C5WzEIiCw1e0fHZPiazB@bounce.linkedin.com>	to=<david.touzeau@klf.fr>	size=4354	subject=Matthieu Brignone has accepted your LinkedIn invitation
	if($_GET["main"]=="emails_received"){$title=null;}
	$html="
	<div id='spyrevents' style='height:550px;overflow:auto'>
	<br>$title
	<table style='width:100%'>
	<tr>
	<th>&nbsp;</th>
	<th>{date}</th>
	<th>{from}</th>
	<th>{to}</th>
	<th>{size}</th>
	<th>{subject}</th>
	</tr>
	";
	while (list ($num, $val) = each ($tbl) ){
		if(preg_match('#([0-9\-]+)\s+([0-9:]+)\s+time=([0-9]+)\s+from=<(.*?)>\s+to=<(.+?)>\s+size=([0-9]+)\s+subject=(.+)#',$val,$reg)){
			$date=$reg[1];
			$file=null;
			$time=$reg[2];
			$numeric_time=$reg[3];
			$from=substr($reg[4],0,20);
			if($from==null){$from="Undisclosed";}
			$to=substr($reg[5],0,30);
			$size=ParseBytes($reg[6]);
			
			if(preg_match("#(.+?)\s+file=(.+)#",$reg[7],$e)){
				$reg[7]=$e[1];
				$file=$e[2];
			}
			
			$subject=substr($reg[7],0,40);
			$subject=htmlentities($subject);
			$reg[7]=htmlentities($reg[7]);
			
			$tip="<div style=font-size:12px><div><strong>{from}:</strong>{$reg[4]}</div><div><strong>{to}:</strong>{$reg[5]}</div><div><strong>{subject}:</strong>{$reg[7]}</div>";
			$tip=$tip."<div><strong>{file}:</strong>$file</div></div>";
			
			$html=$html."
			<tr ". CellRollOver(null,$tip).">
			<td width=1% nowrap style='border-bottom:1px solid #CCCCCC'><img src='img/fw_bold.gif'></td>
			<td width=1% nowrap style='border-bottom:1px solid #CCCCCC'>$date&nbsp;$time</td>
			<td style='border-bottom:1px solid #CCCCCC'>$from</td>
			<td style='border-bottom:1px solid #CCCCCC'>$to</td>
			<td width=1% nowrap style='border-bottom:1px solid #CCCCCC' align='right'>$size</td>
			<td style='border-bottom:1px solid #CCCCCC'>$subject</td>
			</tr>
			
			
			";
			}
		}
		
	$html=$html . "</table></div>";
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
	
	
	
}

function reports(){
	
	$sock=new sockets();
	
	if($_GET["ViewReportPage"]==null){$_GET["ViewReportPage"]="index.html";}
	$_GET["ViewReportPage"]=str_replace('./','',$_GET["ViewReportPage"]);
	$dir=dirname($_GET["ViewReportPage"]);
	if($dir<>null){$dir=$dir.'/';}
	
	$results=$sock->getfile("mailspyreports:{$_GET["ViewReportPage"]}");
	$results=str_replace('<H1>',"<H3>",$results);
	$results=str_replace('</H1>',"</H3>",$results);
	
	$results=str_ireplace('<H2>',"<div style='font-size:13px;border-bottom:1px dotted black;font-weight:bold;margin-bottom:4px;'>",$results);
	$results=str_ireplace('</H2>',"</div>",$results);
	
	$results=str_replace('<HTML>',"",$results);
	$results=str_replace('<HEAD>',"",$results);
	$results=str_replace('</HEAD>',"",$results);
	$results=str_replace('</BODY>',"",$results);
	$results=str_replace('<BODY>',"",$results);
	$results=str_replace('</HTML>',"",$results);
	$results=str_replace('bgcolor=#DDDDDD',"style='background-color:#CCCCCC;font-size:11px;font-weight:bold;border:1px solid #DDDDDD'",$results);
	$results=str_replace('<TR>',"<TR " . CellRollOver().">",$results);
	$results=str_replace('<TABLE width=100%>',"<table style='width:100%;margin:3px;padding:3px;border:1px solid #DDDDDD'>",$results);
	
	

	
	if(preg_match_all('#<A href=([a-z\/A-Z0-9\-\.]+)>(.+?)</A>#i',$results,$re)){
		//print_r($re);
		while (list ($num, $val) = each ($re[0]) ){
			$source=$val;
			$link=$re[1][$num];
			$newlink="<A href=\"#\" OnClick=\"javascript:LoadAjax('allreports','$page?ViewReportPage=$dir$link');\" style='font-size:12px;text-decoration:underline'>{$re[2][$num]}</A>";
			$results=str_replace($source,$newlink,$results);
		}
	}
	
	$results="<div style='margin-top:10px;width:100%;height:500px;overflow:auto'>$results</div>";
	
	return $results;
	
	
}