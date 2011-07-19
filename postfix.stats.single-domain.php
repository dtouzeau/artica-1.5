<?php
	include_once('ressources/class.artica.graphs.inc');
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	
	
	$user=new usersMenus();
	if($user->AsPostfixAdministrator==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["tab"])){tabs();exit;}
	if(isset($_GET["top10-recipients"])){recipients();exit;}
	if(isset($_GET["top10-senders"])){senders();exit;}
	
	
js();


function js(){
	$page=CurrentPageName();
	$domain=$_GET["domain"];
	echo "YahooWin4(700,'$page?tab=yes&domain=".urlencode($domain)."','$domain');";
	}
	
	
function tabs(){
	$page=CurrentPageName();
	$domain=$_GET["domain"];
	$md=md5($domain);
	$tpl=new templates();
	$array["top10-recipients"]='{top10}::{recipients} 7 {days}';
	$array["top10-senders"]='{top10}::{senders} 7 {days}';
	
	
	
	//$array["filters"]=$filters_settings;
	//$array["filters-connect"]="{filters_connect}";
	

	while (list ($num, $ligne) = each ($array) ){
		
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&domain=". urlencode($domain)."\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_postfix_stats_domain_$md style='width:100%;height:850px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_postfix_stats_domain_$md\").tabs();});
		</script>"
	;			
	
	
}

function recipients(){
	$domain=$_GET["domain"];
	$q=new mysql();
	$tpl=new templates();
	$sql="
	SELECT SUM(hits) as hits, recipient_user as mailto 
	FROM smtp_logs_day_users
	WHERE zDay>=DATE_SUB(NOW(),INTERVAL 7 DAY)
	AND sender_domain='$domain'
	GROUP BY recipient_user
	ORDER BY hits DESC 
	LIMIT 0,10";
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th>{hits}</th>
		<th>{recipient}</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	
	$filename="postfix.domain.$domain.top.user.png";
	$gp=new artica_graphs(dirname(__FILE__)."/ressources/logs/web/$filename",50);
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo $q->mysql_error;return;}
	
	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($ligne["mailto"]==null){continue;}
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	//$js="Loadjs('postfix.stats.single-domain.php?domain=".urlencode($ligne["delivery_domain"])."')";
	//$style=" style='text-decoration:underline' ";
	$html=$html."
	<tr class=$classtr>
			<td style='font-size:14px;font-weight:bold' width=1% align='center'><a href=\"javascript:blur()\" OnClick=\"javascript:$js\">{$ligne["hits"]}</a></td>
			<td style='font-size:14px;font-weight:bold' nowrap width=99%><a href=\"javascript:blur()\" OnClick=\"javascript:$js\" $style>{$ligne["mailto"]}</a></td>
		</tr>
	";		
		
		$gp->xdata[]=$ligne["hits"];
		$gp->ydata[]=$ligne["mailto"];			

	}	
	
	$table[]="</table>";
	$time=time();
	$gp->width=560;
	$gp->height=580;
	$gp->ViewValues=false;
	$gp->x_title="{mailto}";
	$gp->pie();	
	echo "<center><img src='ressources/logs/web/$filename?time=$time'></center>";	
	echo "<hr>";
	
	echo $tpl->_ENGINE_parse_body("$html</table>");		
	
	
}

function senders(){
	$domain=$_GET["domain"];
	$q=new mysql();
	$tpl=new templates();
	$sql="
	SELECT SUM(hits) as hits, sender_user as mailto 
	FROM smtp_logs_day_users
	WHERE zDay>=DATE_SUB(NOW(),INTERVAL 7 DAY)
	AND recipient_domain='$domain'
	GROUP BY sender_user
	ORDER BY hits DESC 
	LIMIT 0,10";
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th>{hits}</th>
		<th>{senders}</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	
	$filename="postfix.domain.$domain.top.senders.png";
	$gp=new artica_graphs(dirname(__FILE__)."/ressources/logs/web/$filename",50);
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo $q->mysql_error;return;}
	
	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($ligne["mailto"]==null){continue;}
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	//$js="Loadjs('postfix.stats.single-domain.php?domain=".urlencode($ligne["delivery_domain"])."')";
	//$style=" style='text-decoration:underline' ";
	$html=$html."
	<tr class=$classtr>
			<td style='font-size:14px;font-weight:bold' width=1% align='center'><a href=\"javascript:blur()\" OnClick=\"javascript:$js\">{$ligne["hits"]}</a></td>
			<td style='font-size:14px;font-weight:bold' nowrap width=99%><a href=\"javascript:blur()\" OnClick=\"javascript:$js\" $style>{$ligne["mailto"]}</a></td>
		</tr>
	";		
		
		$gp->xdata[]=$ligne["hits"];
		$gp->ydata[]=$ligne["mailto"];			

	}	
	
	$table[]="</table>";
	$time=time();
	$gp->width=560;
	$gp->height=580;
	$gp->ViewValues=false;
	$gp->x_title="{mailto}";
	$gp->pie();	
	echo "<center><img src='ressources/logs/web/$filename?time=$time'></center>";	
	echo "<hr>";
	
	echo $tpl->_ENGINE_parse_body("$html</table>");			
	
	
}


