<?php
	if(isset($_GET["verbose"])){$GLOBALS["VERBOSE"]=true;ini_set('html_errors',1);ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);}
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
	
	if(isset($_GET["rejected-7days"])){rejected_7days();exit;}
	
	
tabs();


function tabs(){
	$page=CurrentPageName();
	$tpl=new templates();
	$array["rejected-7days"]='{last_7days}';
	
	
	//$array["filters"]=$filters_settings;
	//$array["filters-connect"]="{filters_connect}";
	

	while (list ($num, $ligne) = each ($array) ){
		
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_postfix_stats_rejected style='width:100%;height:850px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_postfix_stats_rejected\").tabs();});
		</script>"
	;			
	
	
}


function rejected_7days(){ 
	$sql="SELECT SUM(emails) as xcon,bounce_error FROM smtp_logs_day WHERE bounce_error!='Sended'
	AND `day`>=DATE_SUB(NOW(),INTERVAL 7 DAY)
	GROUP BY bounce_error
	ORDER BY xcon DESC
	LIMIT 0,10";
	
	$tpl=new templates();
	$q=new mysql();	
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th>{mails}</th>
		<th>{info}</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	
	$filename="postfix.rejected-7days.top.png";
	$gp=new artica_graphs(dirname(__FILE__)."/ressources/logs/web/$filename",50);
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo $q->mysql_error;return;}
	
	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
	//$js="Loadjs('postfix.stats.single-domain.php?domain=".urlencode($ligne["delivery_domain"])."')";
	//$style=" style='text-decoration:underline' ";
	$html=$html."
	<tr class=$classtr>
			<td style='font-size:14px;font-weight:bold' width=1% align='center'><a href=\"javascript:blur()\" OnClick=\"javascript:$js\">{$ligne["xcon"]}</a></td>
			<td style='font-size:14px;font-weight:bold' nowrap width=99%><a href=\"javascript:blur()\" OnClick=\"javascript:$js\" $style>{$ligne["bounce_error"]}</a></td>
		</tr>
	";		
		
		$gp->xdata[]=$ligne["xcon"];
		$gp->ydata[]=$ligne["bounce_error"];			

	}	
	
	$table[]="</table>";
	$time=time();
	$gp->width=560;
	$gp->height=580;
	$gp->ViewValues=false;
	$gp->x_title=null;
	$gp->pie();	
	echo "<center><img src='ressources/logs/web/$filename?time=$time'></center>";	
	echo "<hr>";
	
	echo $tpl->_ENGINE_parse_body("$html</table>");	
	
}
?>
	