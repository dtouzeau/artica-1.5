<?php
	include_once('ressources/class.artica.graphs.inc');
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.mysql.inc');
	
	$user=new usersMenus();
	if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["day-pie"])){week_pie(false,true);exit;}
	if(isset($_GET["week-pie"])){week_pie();exit;}
	if(isset($_GET["month-pie"])){week_pie(true);exit;}
	if(isset($_GET["category-tabs"])){category_tabs();exit;}
	if(isset($_GET["categories-show"])){category_show();exit;}
	if(isset($_GET["mails-list"])){mails_list();exit;}
	if(isset($_GET["mails-list-query"])){mails_list_query();exit;}
	
	
tabs();


function tabs(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$array["day-pie"]='anti-spam {today}';
	$array["week-pie"]='anti-spam 7 {days}';
	$array["month-pie"]='anti-spam 30 {days}';
	
	
	//$array["filters"]=$filters_settings;
	//$array["filters-connect"]="{filters_connect}";
	

	while (list ($num, $ligne) = each ($array) ){
		
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_postfix_amavis_stats style='width:100%;height:850px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_postfix_amavis_stats\").tabs();});
		</script>"
	;	
	
	
	
}


function mails_list(){
	unset($_GET["mails-list"]);
	$page=CurrentPageName();
	while (list ($num, $ligne) = each ($_GET) ){
		$f[]="&$num=$ligne";
		
	}
	$md5=md5(@implode("",$f));
	$query=@implode("",$f);
	echo "<div id='$md5' style='height:450px;overflow:auto'></div>
	
	<script>
		LoadAjax('$md5','$page?mails-list-query=yes$query');
	
	</script>
	";
	
	
}


function mails_list_query(){
	$tpl=new templates();
	$page=CurrentPageName();
	if($_GET["user"]=="domain_from"){$dom=" AND from_domain='{$_GET["domain"]}'";}
	if($_GET["user"]=="domain_to"){$dom=" AND to_domain='{$_GET["domain"]}'";}
	$sql="SELECT * FROM amavis_event WHERE bounce_error='{$_GET["category"]}' $dom AND zDate>=DATE_SUB(NOW(),INTERVAL {$_GET["interval"]} DAY) ORDER BY zDate DESC LIMIT 0,150";

	$q=new mysql();	
	
	$html="
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
	<thead class='thead'>
		<tr>
			<th>{date}</th>
			<th>{from}</th>
			<th>{to}</th>
			<th>{size}</th>
		</tr>
	</thead>
	<tbody class='tbody'>";		
	
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo $q->mysql_error."<br><code>$sql</code>";return;}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$size=$ligne["size"];
		if($size<1024){$size="$size bytes";}else{$size=FormatBytes($size/1024);}
		if(trim($ligne["from"])==null){$ligne["from"]="&nbsp;";}
		if(trim($ligne["to"])==null){$ligne["to"]="&nbsp;";}
		$ligne["from"] = wordwrap($ligne["from"], 40, "<br />\n",true);
		$ligne["to"] = wordwrap($ligne["to"], 40, "<br />\n",true);
		mb_internal_encoding("UTF-8");
		$ligne["subject"] = mb_decode_mimeheader($ligne["subject"]); 		
		$ligne["subject"] = wordwrap($ligne["subject"], 85, "<br />\n",true);
		
		$color=null;
		
		
	$html=$html."
	<tr class=$classtr $color>	
		<td width=1% nowrap style='font-size:14px'>{$ligne["zDate"]}</td>
		<td width=1% nowrap style='font-size:14px'>{$ligne["from"]}</td>
		<td width=1% nowrap style='font-size:14px'>{$ligne["to"]}</td>
		<td width=1% nowrap style='font-size:14px'>$size</td>
	</tr>
	<tr class=$classtr $color>	
		<td width=99% style='font-size:14px' colspan=4><i style='font-weight:bold'>{$ligne["subject"]}</a></i></td>
	</tr>
	";
	}
	
	$html=$html."</table>";
	echo $tpl->_ENGINE_parse_body($html);	
	
}


function week_pie($month=false,$day=false){
	$tpl=new templates();
	$page=CurrentPageName();
	$q=new mysql();
	$interval=7;
	if($month){$interval=30;}
	if($day){$interval=1;}
	
	$sql="SELECT SUM(messages) as conx,bounce_error FROM amavis_event_hours
	WHERE zDate>=DATE_SUB(NOW(),INTERVAL $interval DAY)
	GROUP BY bounce_error";
	
	
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th>{connexions}</th>
		<th>{errors}</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	
	$filename="postfix.amavis.days-$interval.as.png";
	$gp=new artica_graphs(dirname(__FILE__)."/ressources/logs/web/$filename",50);
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo $q->mysql_error;return;}
	
	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	$style="style='text-decoration:underline'";
	$js="YahooWin4(700,'$page?category-tabs={$ligne["bounce_error"]}&interval=$interval','{$ligne["bounce_error"]} &raquo; $interval {days}');";
	
	$html=$html."
	<tr class=$classtr>
			<td style='font-size:14px;font-weight:bold' width=1% align='center'><a href=\"javascript:blur()\" OnClick=\"javascript:$js\" $style>{$ligne["conx"]}</a></td>
			<td style='font-size:14px;font-weight:bold' nowrap width=99%><a href=\"javascript:blur()\" OnClick=\"javascript:$js\" $style>{$ligne["bounce_error"]}</a></td>
		</tr>
	";		
		
		$gp->xdata[]=$ligne["conx"];
		$gp->ydata[]=$ligne["bounce_error"];			

	}	
	
	$table[]="</table>";
	$time=time();
	$gp->width=560;
	$gp->height=580;
	$gp->ViewValues=false;
	$gp->x_title="{ipaddr}";
	$gp->pie();	
	echo "<center><img src='ressources/logs/web/$filename?time=$time'></center>";	
	echo "<hr>";
	
	echo $tpl->_ENGINE_parse_body("$html</table>")	;
	
	
}

function category_tabs(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$array["domain_from"]='{senders}';
	$array["domain_to"]='{recipients}';
	
	
	
	//$array["filters"]=$filters_settings;
	//$array["filters-connect"]="{filters_connect}";
	

	while (list ($num, $ligne) = each ($array) ){
		
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?categories-show=yes&user=$num&category={$_GET["category-tabs"]}&interval={$_GET["interval"]}\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_postfix_amavis_stats_category style='width:100%;height:850px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_postfix_amavis_stats_category\").tabs();});
		</script>"
	;	
	
	
	
}

function category_show(){
	$tpl=new templates();
	$page=CurrentPageName();
	$q=new mysql();
	$interval=$_GET["interval"];
	
	
	$cat=$_GET["category"];
	$sql="SELECT SUM(messages) as conx,SUM(size) AS tsize,{$_GET["user"]} as tdomain FROM amavis_event_hours
	WHERE zDate>=DATE_SUB(NOW(),INTERVAL $interval DAY)
	AND bounce_error='$cat'
	GROUP BY tdomain ORDER BY conx DESC LIMIT 0,10
	";
	
	
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th>{connexions}</th>
		<th>{size}</th>
		<th>{domains}</th>
	</tr>
</thead>
<tbody class='tbody'>";			
$filename="postfix.amavis.$cat.$interval.{$_GET["user"]}.as.png";
	$gp=new artica_graphs(dirname(__FILE__)."/ressources/logs/web/$filename",50);
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo $q->mysql_error."<hr><code>$sql</code><hr>";return;}
	
	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	$style="style='text-decoration:underline'";
	$js="YahooWin5(700,'$page?mails-list=yes&category={$_GET["category"]}&interval=$interval&user={$_GET["user"]}&domain={$ligne["tdomain"]}','{$_GET["category"]} &raquo; $interval {days} &raquo; {$ligne["tdomain"]} &raquo; {$_GET["user"]}');";
	$size=$ligne["tsize"]/1024;
	$size=FormatBytes($size);
	if($ligne["tdomain"]==null){$ligne["tdomain"]="undisclosed";}
	$html=$html."
	<tr class=$classtr>
			<td style='font-size:14px;font-weight:bold' width=1% align='center'><a href=\"javascript:blur()\" OnClick=\"javascript:$js\" $style>{$ligne["conx"]}</a></td>
			<td style='font-size:14px;font-weight:bold' width=1% align='center'><a href=\"javascript:blur()\" OnClick=\"javascript:$js\" $style>$size</a></td>
			<td style='font-size:14px;font-weight:bold' nowrap width=99%><a href=\"javascript:blur()\" OnClick=\"javascript:$js\" $style>{$ligne["tdomain"]}</a></td>
		</tr>
	";		
		
		$gp->xdata[]=$ligne["conx"];
		$gp->ydata[]=$ligne["tdomain"];			

	}	
	
	$table[]="</table>";
	$time=time();
	$gp->width=560;
	$gp->height=580;
	$gp->ViewValues=false;
	$gp->x_title="{ipaddr}";
	$gp->pie();	
	echo "<center><img src='ressources/logs/web/$filename?time=$time'></center>";	
	echo "<hr>";
	
	echo $tpl->_ENGINE_parse_body("$html</table>")	;	
	
}

