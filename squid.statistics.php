<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.squid.inc');
	include_once('ressources/class.rtmm.tools.inc');

	
	
	$user=new usersMenus();
	if(!$user->AsSquidAdministrator){
		$tpl=new templates();
		echo "alert('".$tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		exit;
		
	}	
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["graph"])){echo graph();exit;}	
	if(isset($_GET["browsed"])){echo browsed_websites();exit;}
	if(isset($_GET["browsed-popup"])){echo browsed_websites_popup();exit;}
	if(isset($_GET["queries"])){queries();exit;}
	if(isset($_GET["last-events"])){last_events();exit;}
	if(isset($_GET["query-menu"])){query_menu();exit;}
	if(isset($_GET["popup-filter"])){popup_filter();exit;}
	if(isset($_GET["Q_CLIENT"])){saveFilter();exit;}
	if(isset($_GET["show-hits"])){showhits();exit;}
	
	
js();


function js(){
	$tpl=new templates();
	$page=CurrentPageName();
	$title=$tpl->_ENGINE_parse_body("Squid {statistics}");
	$title2=$tpl->_ENGINE_parse_body("{filter}");
	$html="
		function SQUID_STATS_LOAD(){
			RTMMail(900,'$page?popup=yes','$title');
		}
		
		function SQUID_SYS_RELOAD(){
			RefreshTab('main_config_squid_stats');
		}
		
		function TableauFilter(){
			YahooWin2(600,'$page?popup-filter=yes','$title2');
		}
		
	var x_SaveQueryFilter= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			LoadAjax('squid_status_main','$page?last-events=yes');
		}			
		
		
		  
		function SaveQueryFilter(){
			var XHR = new XHRConnection();
			XHR.appendData('Q_CLIENT',document.getElementById('Q_CLIENT').value);
			XHR.appendData('Q_TIME',document.getElementById('Q_TIME').value);
			XHR.appendData('Q_EVENTS_NUM',document.getElementById('Q_EVENTS_NUM').value);
			document.getElementById('squid_status_main').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_SaveQueryFilter);	

		}
		
		function WebSiteStatistics(website){
			Loadjs('squid.website.stats.php?domain='+website);
		}
		
		function WebClientStatistics(user){
			Loadjs('squid.user.stats.php?user='+user);
		}
	
	
	SQUID_STATS_LOAD();";
	
	echo $html;
	
}

function saveFilter(){
	
	SET_CACHED("squid_filter","filter","settings",serialize($_GET));
	
	
	
}



function popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$array["graph"]='{system}';
	$array["queries"]='{queries}';
	$array["synthesis"]='{synthesis}:{hits_number}';
	$array["browsed"]='{browsed_websites}';

	

	
	while (list ($num, $ligne) = each ($array) ){
		
		if($num=="synthesis"){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"squid.stats.general.php\"><span>$ligne</span></a></li>\n");
			continue;
		}
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_squid_stats style='width:100%;height:850px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_squid_stats').tabs({
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

function queries(){
	$page=CurrentPageName();
	$html="<table style='width:100%'>
	<tr>
		<td valign='top'><div id='squid_stats_menu'>
		</div></td>
		<td valign='top'><div id='squid_status_main'></div></td>
	</tr>
	</table>
	
	<script>
		LoadAjax('squid_status_main','$page?last-events=yes');
		LoadAjax('squid_stats_menu','$page?query-menu=yes');
	</script>
	
	
	";
	
	echo $html;
	
}


function Graph(){
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("content-type:text/html");

if(!isset($_GET["hostname"])){
	$users=new usersMenus();
	$hostname=$users->hostname;
}

$time=md5(date('Y-m-d H:i:s'));

$html="
<center><input type='button' value='&laquo;&nbsp;{refresh}&nbsp;&raquo;' OnClick=\"javascript:SQUID_SYS_RELOAD();\">
<table style='width:100%'>
<tr>
<td style='padding:3px'><img src='images.listener.php?uri=squid/rrd/connections.day.png&hostname=$hostname&time=$time'></td>
<td style='padding:3px'><img src='images.listener.php?uri=squid/rrd/hitratio.day.png&hostname=$hostname&time=$time'></td>
</tr>
<tr>
<td style='padding:3px'><img src='images.listener.php?uri=squid/rrd/cpu.day.png&hostname=$hostname&time=$time'></td>
<td style='padding:3px'><img src='images.listener.php?uri=squid/rrd/memory.day.png&hostname=$hostname&time=$time'></td>
</tr>
<tr>
<td style='padding:3px'><img src='images.listener.php?uri=squid/rrd/fd.day.png&hostname=$hostname&time=$time'></td>
<td style='padding:3px'><img src='images.listener.php?uri=squid/rrd/svctime.day.png&hostname=$hostname&time=$time'></td>
</tr>
<tr>
<td style='padding:3px'><img src='images.listener.php?uri=squid/rrd/select.day.png&hostname=$hostname&time=$time'></td>
<td>&nbsp;</td>
</tr>
</table>
";
$tpl=new templates();

echo $tpl->_ENGINE_parse_body($html);
	
}

function last_events(){
	
	$q=new mysql();
	$myYear=date('Y');
	$today=date('Y-m-d');
	
	$cache=GET_CACHED("squid_filter","filter","settings",true);
	if($cache<>null){$settings=unserialize($cache);}	
	
	if($settings["Q_CLIENT"]<>null){
		if($settings["Q_CLIENT"]<>"0"){
			$Q_CLIENT=" AND `CLIENT`='{$settings["Q_CLIENT"]}'";
		}
	}
	
	if(preg_match("#d([0-9]+)#",$settings["Q_TIME"],$re)){
		$Q_TIME=" AND `zDate`<= DATE_ADD(NOW(),INTERVAL -{$re[1]} DAY)";
	}
	
	if($settings["Q_TIME"]=="w1"){
		$Q_FIELD_ADD1=", WEEK(zdate) as tweek";
		$Q_TIME=" AND tweek=WEEK(NOW())";
	}
	
	if($settings["Q_TIME"]=="w2"){
		//$Q_FIELD_ADD1=", WEEK(dansguardian_events.zdate) AS tweek";
		$Q_TIME=" AND WEEK(dansguardian_events.zdate)=WEEK(NOW())-1";
	}	
	
	if($settings["Q_EVENTS_NUM"]==null){
		$settings["Q_EVENTS_NUM"]=200;
	}
	
	
	
	$sql="SELECT dansguardian_events.*{$Q_FIELD_ADD1} FROM `dansguardian_events` WHERE 1{$Q_CLIENT}{$Q_TIME} ORDER BY ID DESC LIMIT 0,{$settings["Q_EVENTS_NUM"]}";
	$results=$q->QUERY_SQL($sql,"artica_events");
	$tpl=new templates();
	if(!$q->ok){
		echo $q->mysql_error."<hr>". htmlspecialchars($sql)."<hr>";return ;
	}
	
	$num_rows = mysql_num_rows($results);
	
	$html=$tpl->_ENGINE_parse_body("
	<H3>$num_rows {events}</H3>
	<table style='width:100%'>");
	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		
		
		$country=$ligne["country"];
		$date=$ligne["zDate"];
		$date=str_replace("$today","{today}",$date);
		$date=str_replace("$myYear-","",$date);
		$style='font-size:12px';
		$QuerySize=round($ligne["QuerySize"]/1024,1)." Kb";
		
		if($country<>null){$img_country=GetFlags($country);}else{$img_country="flags/name.png";}
		
		$ligne["sitename"]=texttooltip($ligne["sitename"],"{display_statistics_of_this_website}","WebSiteStatistics('{$ligne["sitename"]}')",null,0,$style);
		$ligne["CLIENT"]=texttooltip($ligne["CLIENT"],"{display_statistics_of_this_user}","WebClientStatistics('{$ligne["CLIENT"]}')",null,0,$style);
		
		$html=$html . "<tr ". CellRollOver().">";
		$html=$html . "<td valign='top' width=1% style='$style'>". imgtootltip($img_country,"<li style=font-size:14px>{$ligne["remote_ip"]}</li><li style=font-size:14px>$country</li>")."</td>";
		$html=$html . "<td valign='top' width=1% style='$style' nowrap>$date</td>";
		$html=$html . "<td valign='top' width=1% style='$style'>{$ligne["CLIENT"]}</td>";
		$html=$html . "<td valign='top' width=1% style='$style'><img src='img/fw_bold.gif'></td>";
		$html=$html . "<td valign='top' width=1% style='$style'>{$ligne["sitename"]}</td>";
		$html=$html . "<td valign='top' width=1% style='$style' nowrap align='right'>$QuerySize</td>";
		
		$html=$html . "</tr>";
		
		$html=$tpl->_ENGINE_parse_body($html);
		
	}
	$html=$html . "</table>";
	echo $html;
	
	
}

function query_menu(){
	
	$filter=Paragraphe("filter-64.png","{filter}","{squid_filter_stats_text}","javascript:TableauFilter()");
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($filter);
	
}


function popup_filter(){
	$array_time[null]="{select}";
	$array_time["d0"]="{today}";
	$array_time["d1"]="{last_24_h}";
	$array_time["d2"]="{last_48_h}";
	$array_time["w1"]="{this_week}";
	$array_time["w2"]="{last_week}";
	
	$cache=GET_CACHED("squid_filter","filter","settings",true);
	if($cache<>null){$settings=unserialize($cache);}
	
	if($settings["Q_EVENTS_NUM"]==null){
		$settings["Q_EVENTS_NUM"]=200;
	}
	
	$tous_les_clients=tous_les_clients();
	$tous_les_clients[]="{select}";
	
	$html="
	<table style='width:100%'>
	<tr>
		<td class=legend>{computer}:</td>
		<td>". Field_array_Hash($tous_les_clients,'Q_CLIENT',$settings["Q_CLIENT"],null,null,0,"font-size:13px;padding:3px")."</td>
	</tr>
	<tr>
		<td class=legend>{when}:</td>
		<td>". Field_array_Hash($array_time,'Q_TIME',$settings["Q_TIME"],null,null,0,"font-size:13px;padding:3px")."</td>
	</tr>
	<tr>
		<td class=legend>{events}:</td>
		<td>". Field_text("Q_EVENTS_NUM",$settings["Q_EVENTS_NUM"],"font-size:13px;padding:3px;width:35px") ."</td>
	</tr>		
	
	<tr>
	<td colspan=2 align='right'>
		<hr>
			". button("{apply}","SaveQueryFilter()")."
	</td>
	</tr>
	</table>
	
	
	
	";
	
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
	
}

function tous_les_clients(){
	$sock=new sockets();
	$d=$sock->APC_GET("SQUID_STATS_CLIENTS_TOT");
	if($d<>null){
		return unserialize($d);
	}
	
	$q=new mysql();
	$sql="SELECT CLIENT FROM squid_events_clients_day GROUP BY CLIENT ORDER BY CLIENT";
	$results=$q->QUERY_SQL($sql,"artica_events");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		
		$array[$ligne["CLIENT"]]=$ligne["CLIENT"];
	}
	
	$sock->APC_SAVE("SQUID_STATS_CLIENTS_TOT",serialize($array));
	return $array;
}

function browsed_websites(){
	$page=CurrentPageName();
	echo "<div id='browsed_websites'></div>
	<script>LoadAjax('browsed_websites','$page?browsed-popup=yes');</script>
	";
	
}
function browsed_websites_popup(){
	$page=CurrentPageName();
	$tpl=new templates();
	$display_hits=$tpl->_ENGINE_parse_body('{display_hits}');
	$sql="SELECT SUM(website_hits) as tcount,websites FROM squid_events_sites_day GROUP BY websites ORDER BY tcount DESC LIMIT 0,200";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){
		echo "<H2>$q->mysql_error</H2>";
	}
	$html="<table style=width:100%>
	<tr>
		<th>{websites}</th>
		<th>{category}</th>
		<th colspan=3>{hits_number}</th>
	</tr>
	
	";
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($ligne["websites"]=="127.0.0.1"){continue;}
		if($ligne["websites"]=="localhost"){continue;}
		$md5=md5($ligne["websites"]."showcat");
		$js="LoadAjaxTiny('$md5','squid.categorize.php?categories-of=". base64_encode($ligne["websites"])."');";
		
		$html=$html."
		<tr ". CellRollOver().">
			<td nowrap><strong style='font-size:12px'>{$ligne["websites"]}</strong></td>
			<td nowrap width=1%>". texttooltip("{hits}","{display_hits}:{$ligne["websites"]}","WebSiteHits('{$ligne["websites"]}')")."</td>
			<td nowrap width=1%><span id='$md5'><a href='#' OnClick=\"javascript:$js\" style='text-decoration:unbderline'>{view_categories}</a></span></td>
			<td nowrap width=1%>". texttooltip("{categorize}","{categorize}:{$ligne["websites"]}","Loadjs('squid.categorize.php?www={$ligne["websites"]}')")."</td>
			<td nowrap width=1% align='right'><strong style='font-size:12px' align='right'>{$ligne["tcount"]}</strong></td>
		</tr>";
		
	}
	
	$html=$html."</table>
	<script>
		". @implode("\n",$js)."
		function WebSiteHits(domain){
			YahooWin4(650,'$page?show-hits='+domain,'$display_hits:'+domain);
		}
	</script>";
	
	echo $tpl->_ENGINE_parse_body($html);		
}
function showhits(){
	$www=$_GET["show-hits"];
	$tpl=new templates();
	$dansguardian_events="dansguardian_events_".date('Ym');	
	$sql="SELECT COUNT(ID) as tcount,uri FROM $dansguardian_events WHERE sitename='$www' GROUP BY uri ORDER BY tcount DESC LIMIT 0,200";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");	

	
	$html="
	<div style='height:450px;overflow:auto'>
	<table style=width:100%>
		<tr>
		<th>{hits_number}</th>
		<th></th>
		
	</tr>";
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$js="s_PopUp('{$ligne["uri"]}',800,800)";
		$html=$html."
		<tr ". CellRollOver($js,"{open}").">
			<td nowrap width=1%><strong style='font-size:12px'>{$ligne["tcount"]}</strong></td>
			<td nowrap width=99%><strong><code>{$ligne["uri"]}</code></strong></td>
		</tr>";
		
	}
		$html=$html."</table></div>";
		echo $tpl->_ENGINE_parse_body($html);
}
	
	
	
