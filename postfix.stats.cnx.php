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
	
	if(isset($_GET["popup"])){tabs();exit;}
	if(isset($_GET["blocked-ips"])){tabs_blocked_ip();exit;}
	if(isset($_GET["blocked-ips-stats"])){blocked_ip_stats();exit;}
	
	if(isset($_GET["blocked-ip-single"])){blocked_ip_single_js();exit;}
	if(isset($_GET["blocked-ip-single-tabs"])){blocked_ip_single_tabs();exit;}
	if(isset($_GET["blocked-ip-single-week"])){blocked_ip_single_week();exit;}
	if(isset($_GET["blocked-ip-single-events"])){blocked_ip_single_events();exit;}
	if(isset($_GET["blocked-ip-single-errors"])){blocked_ip_single_errors();exit;}
	
	if(isset($_GET["blocked-ip-search"])){blocked_ip_search();exit;}
	if(isset($_GET["blocked-ip-search-pattern"])){blocked_ip_search_results();exit;}
	if(isset($_GET["whitelist-ipaddr"])){whitelist_ipaddr();exit;}
	
	
	if(isset($_GET["identity"])){blocked_ip_identity();exit;}
	if(isset($_GET["identity-infos"])){blocked_ip_identity_infos();exit;}
	if(isset($_GET["identity-ban"])){blocked_ip_identity_ban();exit;}
	
	
	js();
	
function js(){
	$page=CurrentPageName();
	echo "
		document.getElementById('BodyContent').innerHTML='<center><img src=img/wait_verybig.gif></center>';
		$('#BodyContent').load('$page?popup=yes');";
	
}	

function blocked_ip_single_js(){
	$page=CurrentPageName();
	echo "YahooWin3('700','$page?blocked-ip-single-tabs=yes&ip=".urlencode($_GET["blocked-ip-single"])."','{$_GET["blocked-ip-single"]}')";
}


function blocked_ip_identity(){
	$page=CurrentPageName();
	$tpl=new templates();
	$ip=$_GET["ip"];
	$ban_this_server=$tpl->javascript_parse_text("{ban_this_server}");
	$md=md5($ip);
	$ban=Paragraphe("64-bann-server.png","{ban_this_server}","{ban_server_iptables}","javascript:BlockedIdentityBan()");
	
	$q=new mysql();
	$sql="SELECT date_created FROM iptables WHERE local_port=25 AND flux='INPUT' AND serverip='$ip'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	if($ligne["date_created"]){
		$banned=Paragraphe("hearth-blocked-64.png","{banned_host}","$ip {banned_host}");
	}
	
	
	
	$html= "
	<div id='identity-div-$md'>
	<div style='font-size:18px'>$ip::{identity}</div>
	<table style='width:100%'>
	<tr>
		<td valign='top'><div id='ip-ident'></div></td>
		<td valign='top' width=1%>$ban$banned</td>
	</tr>
	</table>
	</div>
	<script>
		LoadAjax('ip-ident','$page?identity-infos=yes&ip=$ip');
		
		var X_BlockedIdentityBan= function (obj) {
				var results=obj.responseText;
				if(results.length>0){alert(results);}
				if(document.getElementById('main_postfix_$md')){RefreshTab('main_postfix_$md');}
			}			
		
		function BlockedIdentityBan(){
			if(confirm('$ban_this_server?\\n$ip')){
				var XHR = new XHRConnection();
				XHR.appendData('identity-ban','$ip');
				if(document.getElementById('identity-div-$md')){document.getElementById('identity-div-$md').innerHTML='<center><img src=img/wait_verybig.gif></center>';}
				XHR.sendAndLoad('$page', 'GET',X_BlockedIdentityBan);
			
			}
		
		}
		
		
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}

function blocked_ip_identity_ban(){
	include_once('ressources/class.iptables-chains.inc');
	$tpl=new templates();
	$ipchain=new iptables_chains();
	$IPSRC=$_GET["identity-ban"];
	if(trim($IPSRC)==null){echo "NULL!";return;}
	
	
		if(!preg_match("#[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+#",$IPSRC)){
			$ip=gethostbyname($IPSRC);
			$servername=$IPSRC;
		}else{
			$ip=$IPSRC;
			$servername=gethostbyaddr($IPSRC);
		}
		
		$ipchain=new iptables_chains();
		$ipchain->servername=$servername;
		$ipchain->serverip=$ip;
		$ipchain->EventsToAdd="Manual rule";
		$ipchain->rule_string="iptables -A INPUT -s $ip -p tcp --destination-port 25 -j DROP -m comment --comment \"ArticaInstantPostfix\"";
		if(!$ipchain->addPostfix_chain()){
			echo $tpl->javascript_parse_text("{failed} $ip $servername ");
			return;
		}else{
			echo $tpl->javascript_parse_text("{success} $ip $servername ");
		}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-iptables-compile=yes");
}


function blocked_ip_identity_infos(){
	include_once('ressources/class.rtmm.tools.inc');
	$page=CurrentPageName();
	$tpl=new templates();	
	$ip=$_GET["ip"];
	$hostname=gethostbyaddr($ip);
	if($hostname==null){$hostname="{unknown}";}
	
 	
	
	if(function_exists("geoip_record_by_name")){
		$record = geoip_record_by_name($ip);
		if ($record) {
			$country=$record["country_name"];
			$city=$record["city"];
			
		}
	}
	
	if($country<>null){
		$country="<table><tr><td width=1%><img src='img/".GetFlags($country)."'></td><td style='font-size:14px'>$country</td></tr></table>";
		if($city==null){$city="{unknown}";}
	}else{
		$country="{unknown}";
		$city="{unknown}";
	}

	$sock=new sockets();
	$datas=explode(";",$sock->getFrameWork("cmd.php?rbl-check=$ip"));
	if($datas[0]<>null){
	$rbl="<tr>
		<td class=legend>RBL:</td>
		<td style='font-size:14px'><a href=\"javascript:blur();\" OnClick=\"javascript:s_PopUpFull('{$datas[1]}',600,800,'{$datas[1]}::$ip');\" style='text-decoration:underline;font-size:14px'>{$datas[0]}</a></td>
		</tr>";		
	}else{
		$rbl="<tr>
		<td class=legend>RBL:</td>
		<td style='font-size:14px'>{unknown}</td>
		</tr>";				
	}
	
	$html="
	<table class=form>
	<tr>
		<td class=legend>{hostname}:</td>
		<td style='font-size:14px'>$hostname</td>
	</tr>
	<tr>
		<td class=legend>{country}:</td>
		<td style='font-size:14px'>$country</td>
	</tr>
	<tr>
		<td class=legend>{city}:</td>
		<td style='font-size:14px'>$city</td>
	</tr>
		$rbl
	</table>
	
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}


function tabs(){
	$page=CurrentPageName();
	$tpl=new templates();
	
	$array["blocked-ips"]='{blocked_ips}';
	$array["domains"]='{recipient_domains}';
	$array["rejected_mails"]='{rejected_mails}';
	$array["amavis"]='{content_filtering}';
	
	
	
	
	//$array["filters"]=$filters_settings;
	//$array["filters-connect"]="{filters_connect}";
	

	while (list ($num, $ligne) = each ($array) ){
		
		if($num=="domains"){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"postfix.stats.domains.php\"><span>$ligne</span></a></li>\n");
			continue;
		}
		
		if($num=="amavis"){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"postfix.amavis.stats.php\"><span>$ligne</span></a></li>\n");
			continue;
		}		
		
		if($num=="rejected_mails"){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"postfix.stats.rejected.php\"><span>$ligne</span></a></li>\n");
			continue;
		}		
		
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_postfix_stats style='width:100%;height:850px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_postfix_stats\").tabs();});
		</script>"
	;			
	
	
}

function blocked_ip_single_tabs(){
	$page=CurrentPageName();
	$ip=$_GET["ip"];
	$tpl=new templates();
	$array["identity"]='{identity}';
	$array["blocked-ip-single-week"]="7 {days} $ip";
	$array["blocked-ip-single-errors"]="7 {days} {errors}";
	$array["blocked-ip-single-events"]="7 {days} {events}";
	

	$md=md5($ip);

	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes&ip=$ip\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_postfix_$md style='width:100%;height:650px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_postfix_$md\").tabs();});
		</script>"
	;		
	
	
}
function blocked_ip_single_errors(){
	$tpl=new templates();
	$q=new mysql();
	$ip=$_GET["ip"];
	$sql="SELECT SUM(conx) as conx,smtp_err FROM mail_con_err_stats
	WHERE ipaddr='$ip'
	AND zDate>=DATE_SUB(NOW(),INTERVAL 7 DAY)
	GROUP BY smtp_err ORDER BY smtp_err DESC LIMIT 0,10";
	
	
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th>{connexions}</th>
		<th>$ip:{errors}</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	
	$filename="postfix.$ip.top-errors.png";
	$gp=new artica_graphs(dirname(__FILE__)."/ressources/logs/web/$filename",50);
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo $q->mysql_error;return;}
	
	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		
	$html=$html."
	<tr class=$classtr>
			<td style='font-size:14px;font-weight:bold' width=1% align='center'><a href=\"javascript:blur()\" OnClick=\"javascript:$js\">{$ligne["conx"]}</a></td>
			<td style='font-size:14px;font-weight:bold' nowrap width=99%><a href=\"javascript:blur()\" OnClick=\"javascript:$js\">{$ligne["smtp_err"]}</a></td>
		</tr>
	";		
		
		$gp->xdata[]=$ligne["conx"];
		$gp->ydata[]=$ligne["smtp_err"];			

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
	
	echo $tpl->_ENGINE_parse_body("$html</table>");	

}


function blocked_ip_single_events(){
	$tpl=new templates();
	$ip=$_GET["ip"];

	$sql="SELECT COUNT(ID) as tcount,delivery_user,sender_user,
DATE_FORMAT(time_connect,'%Y-%m-%d') as tdate
FROM
smtp_logs
WHERE smtp_sender='$ip'
AND time_connect>=DATE_SUB(NOW(),INTERVAL 7 DAY)
GROUP BY delivery_user,sender_user,tdate
ORDER BY tdate DESC LIMIT 0,100";
	
	
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th>{day}</th>
		<th>{connexions}</th>
		<th>{sender}</th>
		<th>{recipient}</th>
	</tr>
</thead>
<tbody class='tbody'>";			

	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo "<span style='font-size:16px'>$q->mysql_error</span>";}
	$count=mysql_num_rows($results);
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		
	$html=$html."
	<tr class=$classtr>
			<td style='font-size:14px;font-weight:bold' width=1% align='center'><a href=\"javascript:blur()\" OnClick=\"javascript:$js\">{$ligne["tdate"]}</a></td>
			<td style='font-size:14px;font-weight:bold' nowrap width=1% align='center'>{$ligne["tcount"]}</td>
			<td style='font-size:14px;font-weight:bold' nowrap>{$ligne["sender_user"]}</td>
			<td style='font-size:14px;font-weight:bold' nowrap>{$ligne["delivery_user"]}</td>
		</tr>";	
	}
	
	$html=$html."</table>";
	echo $tpl->_ENGINE_parse_body($html);
	
}



function blocked_ip_single_week(){
	$tpl=new templates();
	$sql="SELECT SUM(conx) AS conx, zday ,zhour as thour
	FROM mail_con_err_stats
	WHERE YEAR( zDate ) = YEAR( NOW( ) ) 
	AND zDate>=DATE_SUB(NOW(),INTERVAL 7 DAY)
	AND ipaddr='{$_GET["ip"]}'
	GROUP BY zday,zhour
	ORDER BY zday,zhour
	";	

	$filename="postfix.{$_GET["ip"]}.hits.7-days.png";
	echo $tpl->_ENGINE_parse_body("<span style='font-size:16px'>{GRAPH_POSTFIX_REJECTED_CONNEXIONS}</span><hr>");
	$gp=new artica_graphs(dirname(__FILE__)."/ressources/logs/web/$filename",50);
	
	$x_title=$tpl->_ENGINE_parse_body("{time}");
	
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo "<span style='font-size:16px'>$q->mysql_error</span>";}
	$count=mysql_num_rows($results);
	writelogs($count." rows",__FUNCTION__,__FILE__,__LINE__);
	
	if($count<2){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("<span style='font-size:14px'>{NO_DATA_COME_BACK_LATER}</span><br>");
		
	}	
	
	
	$table[]="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th>{connexions}</th>
		<th>{time}</th>
	</tr>
</thead>
<tbody class='tbody'>";			
	
	if(!$q->ok){echo $q->mysql_error;return;}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$xdata[]=$ligne["zday"]." {$ligne["thour"]}:00";
		$ydata[]=$ligne["conx"];
		
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		
	$table[]="
	<tr class=$classtr>
			<td style='font-size:14px;font-weight:bold' width=1% align='center'><a href=\"javascript:blur()\" OnClick=\"javascript:$js\">{$ligne["conx"]}</a></td>
			<td style='font-size:14px;font-weight:bold' nowrap width=99%><a href=\"javascript:blur()\" OnClick=\"javascript:$js\">{day}:{$ligne["zday"]} {$ligne["thour"]}:00</a></td>
		</tr>
	";		
		
	}	
	
	$gp->width=600;
	$gp->xdata=$xdata;
	$gp->ydata=$ydata;
	$gp->y_title="Connexions";
	$gp->x_title=$x_title;
	$gp->title=null;
	//$gp->Fillcolor="blue@0.9";
	//$gp->color="146497";
	$gp->line_green();
	$time=time();
	echo "<center><img src='ressources/logs/web/$filename?time=$time'></center>";	
	echo "<hr>";
	
	echo $tpl->_ENGINE_parse_body(@implode("\n",$table)."</table>");
	
}


function tabs_blocked_ip(){
	$page=CurrentPageName();
	$tpl=new templates();
	$array["1"]='{blocked_ips}::{day}';
	$array["2"]='{blocked_ips}::{week}';
	$array["blocked-ip-search"]="{search}";
	
	//$array["filters"]=$filters_settings;
	//$array["filters-connect"]="{filters_connect}";
	

	while (list ($num, $ligne) = each ($array) ){
		
		if($num=="blocked-ip-search"){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?blocked-ip-search=yes\"><span>$ligne</span></a></li>\n");
			continue;
		}
		
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?blocked-ips-stats=yes&time=$num\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_postfix_blockedip_stats style='width:100%;height:790px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_postfix_blockedip_stats\").tabs();});
		</script>"
	;			
		
	
	
}


function blocked_ip_stats(){
	$tpl=new templates();
	$page=CurrentPageName();
	switch ($_GET["time"]) {
		case 1:
			$sql="SELECT SUM(conx) as conx,zhour as ttime FROM mail_con_err_stats WHERE zday=DAY(NOW()) AND YEAR(zDate)=YEAR(NOW()) GROUP BY zhour ORDER BY zhour";
			$x_title="Hours";
			$sql2="
			SELECT SUM( conx ) AS conx, ipaddr
			FROM mail_con_err_stats
			WHERE zday = DAY( NOW( ) )
			AND YEAR( zDate ) = YEAR( NOW( ) )
			GROUP BY ipaddr
			ORDER BY conx DESC
			LIMIT 0 , 10";
			
			break;
			
			
		case 2:
			$sql="SELECT COUNT(conx) AS conx, zday AS ttime
			FROM mail_con_err_stats
			WHERE YEAR( zDate ) = YEAR( NOW( ) ) 
			AND zDate>=DATE_SUB(NOW(),INTERVAL 7 DAY)
			GROUP BY zDate,zday
			ORDER BY zDate
			LIMIT 0 , 8";
			$x_title="Days";
			
			$sql2="
			SELECT SUM( conx ) AS conx, ipaddr
			FROM mail_con_err_stats
			WHERE YEAR( zDate ) = YEAR( NOW( ) )
			AND zDate>=DATE_SUB(NOW(),INTERVAL 7 DAY)
			GROUP BY ipaddr
			ORDER BY conx DESC
			LIMIT 0 , 10";
			
			break;			
			
			
		default:	
			;
		break;
	}
	
	
	echo $tpl->_ENGINE_parse_body("<span style='font-size:16px'>{GRAPH_POSTFIX_REJECTED_CONNEXIONS}</span><hr>");
	$gp=new artica_graphs(dirname(__FILE__)."/ressources/logs/web/postfix.{$_GET["time"]}.hits.png",50);
	
	
	
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo "<span style='font-size:16px'>$q->mysql_error</span>";}
	$count=mysql_num_rows($results);
	writelogs($count." rows",__FUNCTION__,__FILE__,__LINE__);
	
	if(mysql_num_rows($results)==0){
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("<H2>{NO_DATA_COME_BACK_LATER}</H2>");
		return;
	}	
	
	if(!$q->ok){echo $q->mysql_error;return;}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$xdata[]=$ligne["ttime"];
		$ydata[]=$ligne["conx"];
		
	}	
	
	$gp->width=600;
	$gp->xdata=$xdata;
	$gp->ydata=$ydata;
	$gp->y_title="Connexions";
	$gp->x_title=$x_title;
	$gp->title=null;
	//$gp->Fillcolor="blue@0.9";
	//$gp->color="146497";
	$gp->line_green();
	$time=time();
	echo "<center><img src='ressources/logs/web/postfix.{$_GET["time"]}.hits.png?time=$time'></center>";	
	echo "<hr>";
	
	
	
	$gp=new artica_graphs(dirname(__FILE__)."/ressources/logs/web/postfix.{$_GET["time"]}.top-hits.png",50);
	
	$table[]="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th>{connexions}</th>
		<th>{ipaddr}</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	
	
	$results=$q->QUERY_SQL($sql2,"artica_events");
	if(!$q->ok){echo "<span style='font-size:16px'>$q->mysql_error</span>";}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		
		$js="Loadjs('$page?blocked-ip-single=".urlencode($ligne["ipaddr"])."')";
		$style=" style='text-decoration:underline' ";
	if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		
	$table[]="
	<tr class=$classtr>
			<td style='font-size:14px;font-weight:bold' nowrap width=1%><a href=\"javascript:blur()\" OnClick=\"javascript:$js\" $style>{$ligne["conx"]}</a></td>
			<td style='font-size:14px;font-weight:bold' nowrap width=99%><a href=\"javascript:blur()\" OnClick=\"javascript:$js\" $style>{$ligne["ipaddr"]}</a></td>
		</tr>
	";
		$gp->xdata[]=$ligne["conx"];
		$gp->ydata[]=$ligne["ipaddr"];			

	}	
	
	$table[]="</table>";
	$time=time();
	$gp->width=560;
	$gp->height=580;
	$gp->ViewValues=false;
	$gp->x_title="{ipaddr}";
	$gp->pie();	

	echo $tpl->_ENGINE_parse_body("
	<center>
	<img src='ressources/logs/web/postfix.{$_GET["time"]}.top-hits.png?time=$time'  border=0>
	<hr>".
	@implode("\n",$table)
	);
	
	
	
}

function blocked_ip_search(){
	$page=CurrentPageName();
	$tpl=new templates();

	
	
	$html="<table style='width:100%' class=form>
	<tr>
		<td class=legend>{search}:</td>
		<td>". Field_text("blocked-search",null,"font-size:16px;padding:3px;width:100%",null,null,null,false,"blockedSearchcheck(event)")."</td>
		<td width=1%>". button("{submit}","blockedSearch()")."</td>
	</tr>
	</table>
	<hr>
	<div id='blocked-ip-search-result' style='width:100%;height:550px;overflow:auto'></div>
	
	
	<script>
		function blockedSearchcheck(e){
			if(checkEnter(e)){blockedSearch();}
		}
	
		function blockedSearch(){
			var se=escape(document.getElementById('blocked-search').value);
			LoadAjax('blocked-ip-search-result','$page?blocked-ip-search-pattern='+se);
		}
		
		var x_WhiteListcnx= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		}	
		
		function WhiteListcnx(md,ip){
			var XHR = new XHRConnection();
			XHR.appendData('whitelist-ipaddr',ip);
			if(document.getElementById('whitelisted_'+md).checked){XHR.appendData('add',1);}else{XHR.appendData('add',0);}
			XHR.sendAndLoad('$page', 'GET',x_WhiteListcnx);
		}		
		
	</script>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
	
}

function blocked_ip_search_results(){
	$page=CurrentPageName();
	
	$pattern=$_GET["blocked-ip-search-pattern"];
	$pattern="*$pattern*";
	$pattern=str_replace("**","*",$pattern);
	$pattern=str_replace("*","%",$pattern);
	
	$q=new mysql();
	$sql="SELECT * FROM postfix_whitelist_con";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "$q->mysql_error\n";}
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){	
		$WHITELISTED[$ligne["ipaddr"]]=true;
		$WHITELISTED[$ligne["hostname"]]=true;
		
	}

	
	$sql="SELECT count(zmd5) as tcount,hostname,ipaddr FROM mail_con_err 
	WHERE hostname LIKE '$pattern' OR ipaddr LIKE '$pattern'
	GROUP BY hostname,ipaddr ORDER BY hostname LIMIT 0,50";
	
	$table[]="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th colspan=2>{connexions}</th>
		<th>{hostname}</th>
		<th nowrap>{whitelist}</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	$q=new mysql();	
	$classtr=null;
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo "<span style='font-size:16px'>$q->mysql_error<br><code>$sql</code></span>";}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$js="Loadjs('$page?blocked-ip-single=".urlencode($ligne["ipaddr"])."')";
		$style=" style='text-decoration:underline' ";
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		
		if($WHITELISTED[$ligne["ipaddr"]]){$white=1;}else{$white=0;}
		$md5=md5($ligne["ipaddr"]);
		$white_field=Field_checkbox("whitelisted_$md5",1,$white,"WhiteListcnx('$md5','{$ligne["ipaddr"]}')");
	
	
	$table[]="
	<tr class=$classtr>
			<td style='font-size:14px;font-weight:bold' nowrap width=1%><a href=\"javascript:blur()\" OnClick=\"javascript:$js\" $style>{$ligne["tcount"]}</a></td>
			<td style='font-size:14px;font-weight:bold' nowrap width=1%><a href=\"javascript:blur()\" OnClick=\"javascript:$js\" $style>{$ligne["ipaddr"]}</a></td>
			<td style='font-size:14px;font-weight:bold' nowrap width=99%><a href=\"javascript:blur()\" OnClick=\"javascript:$js\" $style>{$ligne["hostname"]}</a></td>
			<td width=1%>$white_field</td>
		</tr>
	";

	}
	$sql="SELECT SUM(conx) as tcount,hostname,ipaddr FROM mail_con_err_stats 
	WHERE hostname LIKE '$pattern' OR ipaddr LIKE '$pattern'
	GROUP BY hostname,ipaddr ORDER BY hostname LIMIT 0,50";
	
$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo "<span style='font-size:16px'>$q->mysql_error<code>$sql</code></span>";}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$js="Loadjs('$page?blocked-ip-single=".urlencode($ligne["ipaddr"])."')";
		$style=" style='text-decoration:underline' ";
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		
		if($WHITELISTED[$ligne["ipaddr"]]){$white=1;}else{$white=0;}
		$md5=md5($ligne["ipaddr"]);
		$white_field=Field_checkbox("whitelisted_$md5",1,$white,"WhiteListcnx('$md5','{$ligne["ipaddr"]}')");
	
	$table[]="
	<tr class=$classtr>
			<td style='font-size:14px;font-weight:bold' nowrap width=1%><a href=\"javascript:blur()\" OnClick=\"javascript:$js\" $style>{$ligne["tcount"]}</a></td>
			<td style='font-size:14px;font-weight:bold' nowrap width=99%><a href=\"javascript:blur()\" OnClick=\"javascript:$js\" $style>{$ligne["ipaddr"]}</a></td>
			<td style='font-size:14px;font-weight:bold' nowrap width=99%><a href=\"javascript:blur()\" OnClick=\"javascript:$js\" $style>{$ligne["hostname"]}</a></td>
			<td width=1%>$white_field</td>
		</tr>
	";

	}

	$table[]="</tbody>
	</table>

	";
	$tpl=new templates();	
	echo $tpl->_ENGINE_parse_body(@implode("",$table));
	
	
}

function whitelist_ipaddr(){
	$sock=new sockets();

	if($_GET["add"]==1){
		if(!preg_match("#[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+#",$_GET["white-list-host"])){
			$ipaddr=gethostbyname($_GET["whitelist-ipaddr"]);
			$hostname=$_GET["whitelist-ipaddr"];
		}else{
			$ipaddr=$_GET["whitelist-ipaddr"];
			$hostname=gethostbyaddr($_GET["whitelist-ipaddr"]);
		}
		
		$sql="INSERT IGNORE INTO postfix_whitelist_con (ipaddr,hostname) VALUES('$ipaddr','$hostname')";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo $q->mysql_error;return;}
	
	}else{
		$server=$_GET["whitelist-ipaddr"];
		$sql="DELETE FROM postfix_whitelist_con WHERE ipaddr='$server'";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		$sql="DELETE FROM postfix_whitelist_con WHERE hostname='$server'";
		$q->QUERY_SQL($sql,"artica_backup");
	}
	

	$sock->getFrameWork("cmd.php?postscreen=yes");
}




