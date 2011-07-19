<?php
include_once('ressources/class.artica.graphs.inc');
include_once('ressources/class.templates.inc');
include_once('ressources/class.html.pages.inc');
include_once('ressources/class.system.network.inc');
include_once('ressources/class.mysql.inc');
include_once('ressources/class.tcpip.inc');

$users=new usersMenus();
if(!$users->AsSystemAdministrator){die();}

if(isset($_GET["stats-tabs"])){tabs();exit;}
if(isset($_GET["stats-hour"])){stats_gen_hour();exit;}
if(isset($_GET["stats-day"])){stats_gen_day();exit;}
if(isset($_GET["stats-week"])){stats_gen_week();exit;}
if(isset($_GET["stats-month"])){stats_gen_month();exit;}

if(isset($_GET["detail-ipaddr"])){details_addr_js();exit;}
if(isset($_GET["detail-ipaddr-show"])){details_addr_popup();exit;}

page();

function details_addr_js(){
	$array_src["src_bytes"]="{outgoing}";
	$array_src["dst_bytes"]="{tcp_incoming}";	
	$array_T["H"]="{this_hour}";
	$array_T["D"]="{last_24h}";
	$array_T["W"]="{this_week}";
	$array_T["M"]="{this_month}";
	
	$t1=$array_src[$_GET["flow"]];
	$t2=$array_T[$_GET["t"]];
	
	$title="{$_GET["ipaddr"]}&raquo;$t1&raquo;$t2";
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body($title);
	$html="YahooWin2(700,'$page?detail-ipaddr-show=yes&rule-id={$_GET["rule-id"]}&t={$_GET["t"]}&flow={$_GET["flow"]}&ipaddr={$_GET["ipaddr"]}','$title');";
	echo $html;
	}
function details_addr_popup(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$array_src["src_bytes"]="{outgoing}";
	$array_src["dst_bytes"]="{tcp_incoming}";	
	$array_T["H"]="{this_hour}";
	$array_T["D"]="{last_24h}";
	$array_T["W"]="{this_week}";
	$array_T["M"]="{this_month}";	
	$t1=$array_src[$_GET["flow"]];
	$t2=$array_T[$_GET["t"]];	
	$title="{$_GET["ipaddr"]}&raquo;$t1&raquo;$t2";
	if($_GET["t"]=="H"){
		$sql="SELECT {$_GET["flow"]} AS tflow,
		ipaddr,
		DATE_FORMAT(zDate,'%H:%i') as tdate 
		FROM tcp_account_events 
		WHERE ipaddr='{$_GET["ipaddr"]}' AND zDate>=DATE_SUB(NOW(),INTERVAL 1 HOUR) AND rule_id={$_GET["rule-id"]} ORDER by tdate";
		$label_title="{minutes}";
	}
	
	if($_GET["t"]=="D"){
		$sql="SELECT SUM({$_GET["flow"]}) AS tflow, ipaddr, 
		DATE_FORMAT(zDate,'%d/%Hh') as tdate FROM tcp_account_events 
		WHERE ipaddr='{$_GET["ipaddr"]}' AND zDate>=DATE_SUB(NOW(),INTERVAL 24 HOUR) AND rule_id={$_GET["rule-id"]} 
		GROUP BY ipaddr,DATE_FORMAT(zDate,'%Hh') ORDER by tdate";
		$label_title="{hours}";
		
	}

	if($_GET["t"]=="W"){
		$sql="SELECT SUM({$_GET["flow"]}) AS tflow, ipaddr, 
		DATE_FORMAT(zDate,'%d') as tdate FROM tcp_account_events 
		WHERE ipaddr='{$_GET["ipaddr"]}' AND WEEK(zDate)=WEEK(NOW()) AND rule_id={$_GET["rule-id"]} 
		GROUP BY ipaddr,DATE_FORMAT(zDate,'%d') ORDER by tdate";
		$label_title="{days}";
		
	}

	if($_GET["t"]=="M"){
		$sql="SELECT SUM({$_GET["flow"]}) AS tflow, ipaddr, 
		DATE_FORMAT(zDate,'%d') as tdate FROM tcp_account_events 
		WHERE ipaddr='{$_GET["ipaddr"]}' AND MONTH(zDate)=MONTH(NOW()) AND rule_id={$_GET["rule-id"]} 
		GROUP BY ipaddr,DATE_FORMAT(zDate,'%d') ORDER by tdate";
		$label_title="{days}";
		
	}	
	
	
	
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	
	$count=mysql_num_rows($results);
	
	writelogs($count." rows",__FUNCTION__,__FILE__,__LINE__);
	
	echo $tpl->_ENGINE_parse_body("<center style='font-size:16px;margin:5px'>$title</center>");
	
	if(mysql_num_rows($results)<2){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("<H2>{NO_DATA_COME_BACK_LATER}</H2>");
		return;
	}	
	
	$filename="iptaccount.{$_GET["rule-id"]}.{$_GET["t"]}.{$_GET["flow"]}.{$_GET["ipaddr"]}.png";
	$gp=new artica_graphs(dirname(__FILE__)."/ressources/logs/web/$filename",0);
	
if(!$q->ok){echo $q->mysql_error;}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$xdata[]=$ligne["tdate"];
		$ziz=round($ligne["tflow"]/1024);
		$ziz=$ziz/1000;
		$ydata[]=$ziz;
		
	}	
	

	$gp->xdata=$xdata;
	$gp->width=600;
	$gp->height=550;
	$gp->ydata=$ydata;
	$gp->y_title="MB";
	$gp->x_title=$label_title;
	$gp->title=null;
	$gp->Fillcolor="blue@0.9";
	$gp->color="146497";
	$gp->line_green();
	echo "<img src='ressources/logs/web/$filename'>";
		
	
	
}


function page(){
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql();	
	$sql="SELECT rulename,ID FROM tcp_account_rules";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){$rules[$ligne["ID"]]=$ligne["rulename"];}
	
	$array_src["src_bytes"]="{outgoing}";
	$array_src["dst_bytes"]="{tcp_incoming}";
	
	
	
	$html="
	<center>
		<table style='width:60%' class=form>
		<tr>
			<td class=legend>{rules}</td>
			<td>". Field_array_Hash($rules, "rule-selected","script:font-size:16px;padding:3px")."</td>
			<td>". Field_array_Hash($array_src, "flow","script:font-size:16px;padding:3px")."</td>
			<td>". button("{view}","RefreshIptAccountStats()")."</td>
		</tr>
		</table>
	</center>
	<div id='iptaccount-stats' style='width:100%;height:550px;overflow:auto'></div>
	
	<script>
		function RefreshIptAccountStats(){
			LoadAjax('iptaccount-stats','$page?stats-tabs=yes&rule-id='+document.getElementById('rule-selected').value+'&flow='+document.getElementById('flow').value);
		}
	RefreshIptAccountStats();
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
}

function tabs(){
	$array_src["dst_bytes"]="{tcp_incoming}";
	$array_src["src_bytes"]="{outgoing}";

	$flow_text=$array_src[$_GET["flow"]];
	$rule_id=$_GET["rule-id"];
	$page=CurrentPageName();
	$tpl=new templates();
	$array["stats-hour"]="$flow_text:{this_hour}";
	$array["stats-day"]="$flow_text:{last_24h}";
	$array["stats-week"]="$flow_text:{this_week}";
	$array["stats-month"]="$flow_text:{this_month}";
	while (list ($num, $ligne) = each ($array) ){
		
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&rule-id=$rule_id&flow={$_GET["flow"]}\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_iptaccount_stats_$rule_id style='width:99%;height:500px;overflow:auto'><ul>". implode("\n",$html)."</ul></div>
	<script>
	  $(document).ready(function() {
		$(\"#main_config_iptaccount_stats_$rule_id\").tabs();});
	</script>";	
}


function stats_gen_hour(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$q=new mysql();	
	$array_src["dst_bytes"]="{tcp_incoming}";
	$array_src["src_bytes"]="{outgoing}";	
	$flow_text=$array_src[$_GET["flow"]];
	//dst_bytes = statistics in bytes for "incoming" traffic of that host. Field is followed by five numbers. The first number is the total, the second one is TCP, the third one UDP, the fourth one is ICMP and finally the fifth one is traffic for all other protocols
	// src_bytes =statistics in bytes for "outgoing" traffic of that host. Field is followed by five numbers. The first number is the total, the second one is TCP, the third one UDP, the fourth one is ICMP and finally the fifth one is traffic for all other protocols
	$sql="SELECT SUM({$_GET["flow"]}) as tflow,ipaddr FROM tcp_account_events 
	WHERE zDate>=DATE_SUB(NOW(),INTERVAL 1 HOUR) AND rule_id={$_GET["rule-id"]} GROUP BY ipaddr ORDER BY tflow DESC";
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:50%'>
<thead class='thead'>
	<tr>
		<th colspan=2 >{ipaddr}</th>
		<th colspan=4>$flow_text</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		if($ligne["tflow"]==0){continue;}
		$k=$ligne["tflow"]/1024;
		$size=FormatBytes($k);
		$href="<a href=\"javascript:blur();\" OnClick=\"javascript:Loadjs('$page?detail-ipaddr=yes&rule-id={$_GET["rule-id"]}&t=H&flow={$_GET["flow"]}&ipaddr={$ligne["ipaddr"]}');\" style='font-size:14px;font-weight:bold;text-decoration:underline'>";
		$html=$html."<tr class=$classtr>
		<td width=1%><img src='img/folder-network-32.png'></td>
		<td width=1%><strong style='font-size:14px'>$href{$ligne["ipaddr"]}</a></td>
		<td width=99%><strong style='font-size:14px'>$size</td>
		</tr>";
	}
	
	$html=$html."</table>";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}
function stats_gen_day(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$q=new mysql();	
	$array_src["dst_bytes"]="{tcp_incoming}";
	$array_src["src_bytes"]="{outgoing}";	
	$flow_text=$array_src[$_GET["flow"]];
	//dst_bytes = statistics in bytes for "incoming" traffic of that host. Field is followed by five numbers. The first number is the total, the second one is TCP, the third one UDP, the fourth one is ICMP and finally the fifth one is traffic for all other protocols
	// src_bytes =statistics in bytes for "outgoing" traffic of that host. Field is followed by five numbers. The first number is the total, the second one is TCP, the third one UDP, the fourth one is ICMP and finally the fifth one is traffic for all other protocols
	$sql="SELECT SUM({$_GET["flow"]}) as tflow,ipaddr FROM tcp_account_events 
	WHERE DATE_FORMAT(zDate,'%Y-%m-%d')=DATE_FORMAT(NOW(),'%Y-%m-%d') AND rule_id={$_GET["rule-id"]} GROUP BY ipaddr ORDER BY tflow DESC";
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:50%'>
<thead class='thead'>
	<tr>
		<th colspan=2 >{ipaddr}</th>
		<th colspan=4>$flow_text</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		if($ligne["tflow"]==0){continue;}
		$k=$ligne["tflow"]/1024;
		$size=FormatBytes($k);
		$href="<a href=\"javascript:blur();\" OnClick=\"javascript:Loadjs('$page?detail-ipaddr=yes&rule-id={$_GET["rule-id"]}&t=D&flow={$_GET["flow"]}&ipaddr={$ligne["ipaddr"]}');\" style='font-size:14px;font-weight:bold;text-decoration:underline'>";
		$html=$html."<tr class=$classtr>
		<td width=1%><img src='img/folder-network-32.png'></td>
		<td width=1%><strong style='font-size:14px'>$href{$ligne["ipaddr"]}</a></td>
		<td width=99%><strong style='font-size:14px'>$size</td>
		</tr>";
	}
	
	$html=$html."</table>";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}
function stats_gen_week(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$q=new mysql();	
	$array_src["dst_bytes"]="{tcp_incoming}";
	$array_src["src_bytes"]="{outgoing}";	
	$flow_text=$array_src[$_GET["flow"]];
	//dst_bytes = statistics in bytes for "incoming" traffic of that host. Field is followed by five numbers. The first number is the total, the second one is TCP, the third one UDP, the fourth one is ICMP and finally the fifth one is traffic for all other protocols
	// src_bytes =statistics in bytes for "outgoing" traffic of that host. Field is followed by five numbers. The first number is the total, the second one is TCP, the third one UDP, the fourth one is ICMP and finally the fifth one is traffic for all other protocols
	$sql="SELECT SUM({$_GET["flow"]}) as tflow,ipaddr FROM tcp_account_events 
	WHERE WEEK(zDate)=WEEK(NOW()) AND rule_id={$_GET["rule-id"]} GROUP BY ipaddr ORDER BY tflow DESC";
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:50%'>
<thead class='thead'>
	<tr>
		<th colspan=2 >{ipaddr}</th>
		<th colspan=4>$flow_text</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		if($ligne["tflow"]==0){continue;}
		$k=$ligne["tflow"]/1024;
		$size=FormatBytes($k);
		$href="<a href=\"javascript:blur();\" OnClick=\"javascript:Loadjs('$page?detail-ipaddr=yes&rule-id={$_GET["rule-id"]}&t=W&flow={$_GET["flow"]}&ipaddr={$ligne["ipaddr"]}');\" style='font-size:14px;font-weight:bold;text-decoration:underline'>";
		$html=$html."<tr class=$classtr>
		<td width=1%><img src='img/folder-network-32.png'></td>
		<td width=1%><strong style='font-size:14px'>$href{$ligne["ipaddr"]}</a></td>
		<td width=99%><strong style='font-size:14px'>$size</td>
		</tr>";
	}
	
	$html=$html."</table>";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}
function stats_gen_month(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$q=new mysql();	
	$array_src["dst_bytes"]="{tcp_incoming}";
	$array_src["src_bytes"]="{outgoing}";	
	$flow_text=$array_src[$_GET["flow"]];
	//dst_bytes = statistics in bytes for "incoming" traffic of that host. Field is followed by five numbers. The first number is the total, the second one is TCP, the third one UDP, the fourth one is ICMP and finally the fifth one is traffic for all other protocols
	// src_bytes =statistics in bytes for "outgoing" traffic of that host. Field is followed by five numbers. The first number is the total, the second one is TCP, the third one UDP, the fourth one is ICMP and finally the fifth one is traffic for all other protocols
	$sql="SELECT SUM({$_GET["flow"]}) as tflow,ipaddr FROM tcp_account_events 
	WHERE MONTH(zDate)=MONTH(NOW()) AND rule_id={$_GET["rule-id"]} GROUP BY ipaddr ORDER BY tflow DESC";
	
	
	
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
	$html="<center>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:50%'>
<thead class='thead'>
	<tr>
		<th colspan=2 >{ipaddr}</th>
		<th colspan=4>$flow_text</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		if($ligne["tflow"]==0){continue;}
		$k=$ligne["tflow"]/1024;
		$href="<a href=\"javascript:blur();\" OnClick=\"javascript:Loadjs('$page?detail-ipaddr=yes&rule-id={$_GET["rule-id"]}&t=M&flow={$_GET["flow"]}&ipaddr={$ligne["ipaddr"]}');\" style='font-size:14px;font-weight:bold;text-decoration:underline'>";
		$size=FormatBytes($k);
		$html=$html."<tr class=$classtr>
		<td width=1%><img src='img/folder-network-32.png'></td>
		<td width=1%><strong style='font-size:14px'>$href{$ligne["ipaddr"]}</a></td>
		<td width=99%><strong style='font-size:14px'>$size</td>
		</tr>";
	}
	
	$html=$html."</table>";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}