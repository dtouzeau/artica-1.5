<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.maincf.multi.inc');
	include_once('ressources/class.main_cf_filtering.inc');
	
	if(isset($_GET["org"])){$_GET["ou"]=$_GET["org"];}
	if(isset($_POST["ou"])){$_GET["ou"]=$_POST["ou"];}
	if(isset($_POST["hostname"])){$_GET["hostname"]=$_POST["hostname"];}
	
	$users=new usersMenus();
	
	
	if(!$users->AsPostfixAdministrator){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}

	if(isset($_GET["events"])){section_events();exit;}
	if(isset($_GET["quarantine"])){section_quarantine();exit;}
	if(isset($_GET["logs"])){section_logs();exit;}
	
	
	
	if(isset($_GET["tabs"])){tabs();exit;}
	if(isset($_GET["search-quarantine"])){search_quarantine();exit;}
	if(isset($_GET["search-events"])){search_events();exit;}
	if(isset($_GET["search-logs"])){search_logs();exit;}
	
	
	if(isset($_GET["antispam"])){antispam_popup();exit;}
	if(isset($_GET["search-amavis"])){antispam_search();exit;}
	
	if(isset($_GET["params"])){parameters();exit;}
	if(isset($_GET["BackupMailLogPath"])){BackupMailLogPathSave();exit;}
	
	js();
	
	
function js(){
	$page=CurrentPageName();
	echo "
		document.getElementById('BodyContent').innerHTML='<center><img src=img/wait_verybig.gif></center>';
		$('#BodyContent').load('$page?tabs=yes');";	
	
}

function search_logs(){
	$users=new usersMenus();
	$maillog_path=$users->maillog_path;
	$sock=new sockets();
	$search=urlencode($_GET["pattern"]);
	$datas=explode("\n",$sock->getFrameWork("cmd.php?maillog-query=$search&maillog-path=$maillog_path"));
	
	$page=CurrentPageName();
	$tpl=new templates();	
	
	$html="
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
	<thead class='thead'>
		<tr>
			<th>{$_GET["pattern"]}</th>
			
		</tr>
	</thead>
	<tbody class='tbody'>";			
	
	
	while (list ($num, $ligne) = each ($datas) ){
		if(trim($ligne)==null){continue;}
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$ligne=htmlspecialchars($ligne);
		
		
	$html=$html."
	<tr class=$classtr>	
		<td style='font-size:12px'><code>$ligne</code></td>
	</tr>
	";		
		
	}
	
	echo $tpl->_ENGINE_parse_body($html."</table>");
	
}


function section_logs(){
		$page=CurrentPageName();
	$tpl=new templates();	
	$html="
		<div class=explain>{postfix_search_logs}</div>
	
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
	<thead class='thead'>
		<tr>
			<th align='center'>{pattern}</th>
			<th align='center'>&nbsp;</th>
		</tr>
	</thead>
	<tbody class='tbody'>	
		<tr>
			<td>" . Field_text('pattern-query-logs',"",'width:530px;font-size:14px',null,null,null,false,"PostfixSearchLogsCheck(event)")."</td>
			<td>". button("{search}","PostfixSearchLogs()")."</td>
		</tr>
	</tbody>
	</table>
	<div id='search_logs'></div>
		
	<script>
		function PostfixSearchLogsCheck(e){
			if(checkEnter(e)){PostfixSearchLogs();exit;}
		
		}
	
	
		function PostfixSearchLogs(){
			var pattern=escape(document.getElementById('pattern-query-logs').value);
			LoadAjax('search_logs','$page?search-logs=yes&pattern='+pattern);
		}		
		PostfixSearchLogs();
	</script>
		
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);	
	
	
}
	
	
function tabs(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();
	$array["events"]='{events}';
	$array["queue"]="{queue_management}";
	$array["quarantine"]="{quarantine}";
	$array["antispam"]="Anti-Spam";
	$array["logs"]="{postfix_events}";
	$array["postfix-finder"]="{postfix_finder}";
	$array["params"]="{parameters}";

	$EnableArticaSMTPStatistics=$sock->GET_INFO("EnableArticaSMTPStatistics");
	if(!is_numeric($EnableArticaSMTPStatistics)){$EnableArticaSMTPStatistics=1;}	
	if($EnableArticaSMTPStatistics==0){
		unset($array["events"]);
		unset($array["antispam"]);
	}
	
	

	while (list ($num, $ligne) = each ($array) ){
		if($num=="queue"){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"postfix.queue.monitoring.php?details=yes&hostname=master\"><span>$ligne</span></a></li>\n");
			continue;
			
		}
		
		if($num=="postfix-finder"){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"postfix.finder.php\"><span>$ligne</span></a></li>\n");
			continue;
		}
		
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_postfix_search style='width:100%;height:850px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_config_postfix_search\").tabs();});
		</script>";		
}	

function section_events(){
	$page=CurrentPageName();
	$time=time();
	$tpl=new templates();	
	$html="
		<div class=explain>{postfix_search_events}</div>
	
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
	<thead class='thead'>
		<tr>
			<th align='center'>{date}</th>
			<th align='center'>{from}</th>
			<th align='center'>{recipient}</th>
			<th align='center'>&nbsp;</th>
		</tr>
	</thead>
	<tbody class='tbody'>	
		<tr>
			<td>" . Field_text('zDate',null,'width:100px;font-size:14px',null,null,null,false,"PostfixSearchEventsCheck(event)")."</td>
			<td>" . Field_text('mailfrom',null,'width:150px;font-size:14px',null,null,null,false,"PostfixSearchEventsCheck(event)")."</td>
			<td>" . Field_text('recipient',null,'width:150px;font-size:14px',null,null,null,false,"PostfixSearchEventsCheck(event)")."</td>
			<td>". button("{search}","PostfixSearchEvents()")."</td>
		</tr>
	</tbody>
	</table>
	<div id='search_events-$time'></div>
		
	<script>
		function PostfixSearchEventsCheck(e){
			if(checkEnter(e)){PostfixSearchQuarantine();exit;}
		
		}
	
	
		function PostfixSearchEvents(){
			var zDate=document.getElementById('zDate').value;
			var mailfrom=document.getElementById('mailfrom').value;
			var recipient=escape(document.getElementById('recipient').value);
			LoadAjax('search_events-$time','$page?search-events=yes&zDate='+escape(zDate)+'&mailfrom='+escape(mailfrom)+'&recipient='+recipient);
		}		
		PostfixSearchEvents();
	</script>
		
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);	
	
}




function section_quarantine(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$html="
		<div class=explain>{postfix_search_quarantine}</div>
	
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
	<thead class='thead'>
		<tr>
			<th align='center'>{date}</th>
			<th align='center'>{from}</th>
			<th align='center'>{recipient}</th>
			<th align='center'>{subject}</th>
			<th align='center'>&nbsp;</th>
		</tr>
	</thead>
	<tbody class='tbody'>	
		<tr>
			<td>" . Field_text('q-zDate',null,'width:100px;font-size:14px',null,null,null,false,"PostfixSearchQuarantineCheck(event)")."</td>
			<td>" . Field_text('q-mailfrom',null,'width:150px;font-size:14px',null,null,null,false,"PostfixSearchQuarantineCheck(event)")."</td>
			<td>" . Field_text('q-recipient',null,'width:150px;font-size:14px',null,null,null,false,"PostfixSearchQuarantineCheck(event)")."</td>
			<td>" . Field_text('subject',null,'width:150px;font-size:14px',null,null,null,false,"PostfixSearchQuarantineCheck(event)")."</td>
			<td>". button("{search}","PostfixSearchQuarantine()")."</td>
		</tr>
	</tbody>
	</table>
	<div id='search_quarantine'></div>
		
	<script>
		function PostfixSearchQuarantineCheck(e){
			if(checkEnter(e)){PostfixSearchQuarantine();exit;}
		
		}
	
		function PostfixSearchQuarantine(){
			var zDate=escape(document.getElementById('q-zDate').value);
			var mailfrom=escape(document.getElementById('q-mailfrom').value);
			var recipient=escape(document.getElementById('q-recipient').value);
			var subject=escape(document.getElementById('subject').value);
			LoadAjax('search_quarantine','$page?search-quarantine=yes&zDate='+zDate+'&mailfrom='+mailfrom+'&recipient='+recipient+'&subject='+subject);
		}		
		PostfixSearchQuarantine();
	</script>
		
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function search_events(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$textsearch="{select_messages} ";
	if($_GET["zDate"]<>null){
		$q_zdate=ParseQueryDate($_GET["zDate"],"time_stamp");
		$textsearch=$textsearch."{from_date}: {$_GET["zDate"]}";
	}
	
	$limit="LIMIT 0,100";
	
	if($_GET["mailfrom"]<>null){
		$_GET["mailfrom"]=str_replace('*','%',$_GET["mailfrom"]);
		$q_mailfrom="AND sender_user LIKE '{$_GET["mailfrom"]}%'";
		$textsearch=$textsearch." {and} {from}:{$_GET["mailfrom"]}";
	}
	
	if($_GET["recipient"]<>null){
		$_GET["recipient"]=str_replace('*','%',$_GET["recipient"]);
		$q_mailfrom=$q_mailfrom." AND delivery_user LIKE '{$_GET["recipient"]}%' ";
		$textsearch=$textsearch." {and} {recipient}:{$_GET["recipient"]}";
	}	
	
	
	$sql="SELECT * FROM smtp_logs WHERE LENGTH(sender_user) >3  AND LENGTH(delivery_user) >3 $q_zdate $q_mailfrom ORDER BY time_stamp DESC LIMIT 0,100";
	
	$sql=str_replace('%%','%',$sql);
	$q=new mysql();	
	
	$html="
	<div class=explain>$textsearch</div>
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
	<thead class='thead'>
		<tr>
			<th>{date}</th>
			<th>{from}</th>
			<th>{to}</th>
			<th>{infos}</th>
		</tr>
	</thead>
	<tbody class='tbody'>";		
	
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo $q->mysql_error."<hr><code>$sql</code>";return;}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$size=$ligne["bytes"];
		if($size<1024){$size="$size bytes";}else{$size=FormatBytes($size/1024);}
		if(trim($ligne["sender_user"])==null){$ligne["sender_user"]="&nbsp;";}
		if(trim($ligne["delivery_user"])==null){$ligne["sender_user"]="&nbsp;";}
		$ligne["sender_user"] = wordwrap($ligne["sender_user"], 40, "<br />\n",true);
		$ligne["delivery_user"] = wordwrap($ligne["delivery_user"], 40, "<br />\n",true);
		
		
	$html=$html."
	<tr class=$classtr>	
		<td width=1% nowrap style='font-size:11px;height:auto'>{$ligne["time_stamp"]}</td>
		<td width=1% nowrap style='font-size:11px;height:auto'>{$ligne["sender_user"]}</td>
		<td width=1% nowrap style='font-size:11px;height:auto'>{$ligne["delivery_user"]}</td>
		<td width=99% style='font-size:11px;height:auto'>{$ligne["bounce_error"]}</td>
	</tr>
	";
	}
	
	$html=$html."</table>";
	echo $tpl->_ENGINE_parse_body($html);	
	
	
	
}

function antispam_search(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$textsearch="{select_messages} ";
	if($_GET["zDate"]<>null){
		$q_zdate=ParseQueryDate($_GET["zDate"],"zDate");
		$textsearch=$textsearch."{from_date}: {$_GET["zDate"]}";
	}
	
	$limit="LIMIT 0,100";
	
	if($_GET["mailfrom"]<>null){
		$_GET["mailfrom"]=str_replace('*','%',$_GET["mailfrom"]);
		$q_mailfrom="AND `from` LIKE '{$_GET["mailfrom"]}%'";
		$textsearch=$textsearch." {and} {from}:{$_GET["mailfrom"]}";
	}
	
	if($_GET["recipient"]<>null){
		$_GET["recipient"]=str_replace('*','%',$_GET["recipient"]);
		$q_mailfrom=$q_mailfrom." AND `to` LIKE '{$_GET["recipient"]}%'";
		$textsearch=$textsearch." {and} {recipient}:{$_GET["recipient"]}";
	}	
	
	if($_GET["subject"]<>null){
		$_GET["subject"]=str_replace('*','%',$_GET["subject"]);
		$q_mailfrom=$q_mailfrom." AND `subject` LIKE '{$_GET["subject"]}%'";
		$textsearch=$textsearch." {and} {subject}:{$_GET["subject"]}";
	}	
	
	
	$sql="SELECT * FROM amavis_event WHERE 1 $q_zdate $q_mailfrom ORDER BY zDate DESC LIMIT 0,100";
	
	$sql=str_replace('%%','%',$sql);
	$q=new mysql();	
	
	$html="
	<div class=explain>$textsearch</div>
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
	<thead class='thead'>
		<tr>
			<th>{date}</th>
			<th>{from}</th>
			<th>{to}</th>
			<th>{size}</th>
			<th>&nbsp;</th>
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
		$ligne["from"] = wordwrap($ligne["from"], 35, "<br />\n",true);
		$ligne["to"] = wordwrap($ligne["to"], 35, "<br />\n",true);
		mb_internal_encoding("UTF-8");
		$ligne["subject"] = mb_decode_mimeheader($ligne["subject"]); 		
		$ligne["subject"] = wordwrap($ligne["subject"], 85, "<br />\n",true);
		
		$color=null;
		if($ligne["bounce_error"]<>"PASS"){$color="style='background-color:#FFBCBF'";}
		
	$html=$html."
	<tr class=$classtr $color>	
		<td width=1% nowrap style='font-size:14px'>{$ligne["zDate"]}</td>
		<td width=1% nowrap style='font-size:14px'>{$ligne["from"]}</td>
		<td width=1% nowrap style='font-size:14px'>{$ligne["to"]}</td>
		<td width=1% nowrap style='font-size:14px'>$size</td>
		<td width=1% style='font-size:14px'>{$ligne["bounce_error"]}</td>
	</tr>
	<tr class=$classtr $color>	
		<td width=99% style='font-size:14px' colspan=5><i style='font-weight:bold'>{$ligne["subject"]}</a></i></td>
	</tr>
	";
	}
	
	$html=$html."</table>";
	echo $tpl->_ENGINE_parse_body($html);		
	
}

function antispam_popup(){
	
	$page=CurrentPageName();
	$tpl=new templates();	
	$html="
		<div class=explain>{postfix_search_amavis}</div>
	
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
	<thead class='thead'>
		<tr>
			<th align='center'>{date}</th>
			<th align='center'>{from}</th>
			<th align='center'>{recipient}</th>
			<th align='center'>{subject}</th>
			<th align='center'>&nbsp;</th>
		</tr>
	</thead>
	<tbody class='tbody'>	
		<tr>
			<td>" . Field_text('am-zDate',null,'width:100px;font-size:14px',null,null,null,false,"PostfixSearchAmavisCheck(event)")."</td>
			<td>" . Field_text('am-mailfrom',null,'width:150px;font-size:14px',null,null,null,false,"PostfixSearchAmavisCheck(event)")."</td>
			<td>" . Field_text('am-recipient',null,'width:150px;font-size:14px',null,null,null,false,"PostfixSearchAmavisCheck(event)")."</td>
			<td>" . Field_text('am-subject',null,'width:150px;font-size:14px',null,null,null,false,"PostfixSearchAmavisCheck(event)")."</td>
			<td>". button("{search}","PostfixSearchQuarantine()")."</td>
		</tr>
	</tbody>
	</table>
	<div id='search_amavis'></div>
	
	
	<script>
		function PostfixSearchAmavisCheck(e){
			if(checkEnter(e)){PostfixSearchAmavis();exit;}
		
		}
	
		function PostfixSearchAmavis(){
			var zDate=escape(document.getElementById('am-zDate').value);
			var mailfrom=escape(document.getElementById('am-mailfrom').value);
			var recipient=escape(document.getElementById('am-recipient').value);
			var subject=escape(document.getElementById('am-subject').value);
			LoadAjax('search_amavis','$page?search-amavis=yes&zDate='+zDate+'&mailfrom='+mailfrom+'&recipient='+recipient+'&subject='+subject);
		}		
		PostfixSearchAmavis();
	</script>	
	
	";	
	echo $tpl->_ENGINE_parse_body($html);
}




function ParseQueryDate($pattern,$field){
	$signe=null;
	$pattern=trim($pattern);
	if(preg_match("#>.*?(.+)#",$pattern,$re)){
		$signe=">";
		$pattern=$re[1];
	}
	
	if(preg_match("#<.*?(.+)#",$pattern,$re)){
		$signe="<";
		$pattern=$re[1];
	}	
	
	
		if(preg_match('#^([0-9]+)$#',$pattern,$re)){
			if(strlen($re[1])==4){$q_zdate="AND DATE_FORMAT(zDate,'%Y')$signe={$re[1]}";}
			if(strlen($re[1])==2){$q_zdate="AND DATE_FORMAT(zDate,'%d')$signe={$re[1]} AND MONTH(zDate)=MONTH(NOW()) AND YEAR(zDate)=YEAR(NOW())";}
		
		}
		
		if(preg_match('#^([0-9]+):([0-9]+)$#',$pattern,$re)){$q_zdate="AND DATE_FORMAT(zDate,'%H:%i')$signe='{$re[4]}:{$re[5]}' AND MONTH(zDate)=MONTH(NOW()) AND YEAR(zDate)=YEAR(NOW()) AND DAY(zDate)=DAY(NOW())";}
		if(preg_match('#^([0-9]+)-([0-9]+)$#',$pattern,$re)){$q_zdate="AND DATE_FORMAT(zDate,'%Y-%m')$signe='{$re[1]}-{$re[2]}' AND YEAR(zDate)=YEAR(NOW())";}		
		if(preg_match('#^([0-9]+)-([0-9]+)-([0-9]+)$#',$pattern,$re)){$q_zdate="AND DATE_FORMAT(zDate,'%Y-%m-%d')$signe='{$re[1]}-{$re[2]}-{$re[3]}'";}
		if(preg_match('#^([0-9]+)-([0-9]+)-([0-9]+)\s+([0-9]+)$#',$pattern,$re)){$q_zdate="AND DATE_FORMAT(zDate,'%Y-%m-%d %H')$signe='{$re[1]}-{$re[2]}-{$re[3]} {$re[4]}'";}	
		if(preg_match('#^([0-9]+)-([0-9]+)-([0-9]+)\s+([0-9]+):([0-9]+)$#',$pattern,$re)){$q_zdate="AND DATE_FORMAT(zDate,'%Y-%m-%d %H:%i')$signe='{$re[1]}-{$re[2]}-{$re[3]} {$re[4]}:{$re[5]}'";}
	return $q_zdate;
}

function cutstr($str, $length, $ellipsis=''){
   $cut=(array)explode('\n\n',wordwrap($str),$length,'\n\n');
   return $cut[0].((strlen($cut)<strlen($str))?$ellipsis:'');
}


function search_quarantine(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$textsearch="{select_messages} ";
	if($_GET["zDate"]<>null){
		$q_zdate=ParseQueryDate($_GET["zDate"],"zDate");
		$textsearch=$textsearch."{from_date}: {$_GET["zDate"]}";
	}
	
	$limit="LIMIT 0,100";
	
	if($_GET["mailfrom"]<>null){
		$_GET["mailfrom"]=str_replace('*','%',$_GET["mailfrom"]);
		$q_mailfrom="AND mailfrom LIKE '{$_GET["mailfrom"]}%'";
		$textsearch=$textsearch." {and} {from}:{$_GET["mailfrom"]}";
	}
	
	if($_GET["recipient"]<>null){
		$_GET["recipient"]=str_replace('*','%',$_GET["recipient"]);
		$q_mailfrom=$q_mailfrom." AND mailto LIKE '{$_GET["recipient"]}%'";
		$textsearch=$textsearch." {and} {recipient}:{$_GET["recipient"]}";
	}

	if($_GET["subject"]<>null){
		$_GET["subject"]=str_replace('*','%',$_GET["subject"]);
		$q_mailfrom=$q_mailfrom." AND subject LIKE '{$_GET["subject"]}%'";
		$textsearch=$textsearch." {and} {subject}:{$_GET["subject"]}";
	}	
	
	
	
	$sql="SELECT MessageID,zDate,mailfrom,subject,mailto FROM quarantine WHERE 1 $q_zdate $q_mailfrom ORDER BY zDate DESC $limit";
	$sql=str_replace('%%','%',$sql);
	$q=new mysql();	
	
	$html="
	<div class=explain>$textsearch</div>
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th>{date}</th>
		<th>{from}</th>
		<th>{to}</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		
		if(trim($ligne["mailfrom"])==null){$ligne["mailfrom"]="&nbsp;";}
		if(trim($ligne["delivery_user"])==null){$ligne["sender_user"]="&nbsp;";}
		$ligne["mailfrom"] = wordwrap($ligne["mailfrom"], 20, "<br />\n",true);
		$ligne["mailto"] = wordwrap($ligne["mailto"], 20, "<br />\n",true);
		
		
		mb_internal_encoding("UTF-8");
		$ligne["subject"] = mb_decode_mimeheader($ligne["subject"]); 		
		$ligne["subject"] = wordwrap($ligne["subject"], 85, "<br />\n",true);
		//$ligne["subject"]=utf8_encode($ligne["subject"]);
		
			$link="<a href=\"javascript:blur()\" OnClick=\"javascript:Loadjs('domains.quarantine.php?message-id=".urlencode($ligne["MessageID"])."')\" style='text-decoration:underline'>";
			$html=$html."
			<tr class=$classtr>	
			<td width=1% nowrap style='font-size:14px'>$link{$ligne["zDate"]}</a></td>
			<td width=1% nowrap style='font-size:14px'>$link{$ligne["mailfrom"]}</a></td>
			<td width=1% nowrap style='font-size:14px'>$link{$ligne["mailto"]}</a></td>
			</tr>
			<tr class=$classtr>
			<td width=99% style='font-size:14px' colspan=3><i style='font-weight:bold'>$link{$ligne["subject"]}</a></i></td>
			</tr>
			";
	}
	
	$html=$html."</table>";
	echo $tpl->_ENGINE_parse_body($html);
	
}

function parameters(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();
	$BackupMailLogPath=$sock->GET_INFO("BackupMailLogPath");
	$BackupMailLogMaxTimeCompressed=$sock->GET_INFO("BackupMailLogMaxTimeCompressed");
	if(!is_numeric($BackupMailLogMaxTimeCompressed)){$BackupMailLogMaxTimeCompressed=10080;}
	if($BackupMailLogPath==null){$BackupMailLogPath="/home/maillog-backup";}	
	
	$times[10080]="7 {days}";
	$times[14400]="10 {days}";
	$times[21600]="15 {days}";
	$times[43200]="1 {month}";
	$times[129600]="3 {months}";
	
	
	$html="
	<div id='postfinder_parameters_div'>
	<div class=explain>{postfinder_parameters_explain}</div>
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{directory}:</td>
		<td>". Field_text("BackupMailLogPath",$BackupMailLogPath,"font-size:13px;padding:3px;width:210px")."</td>
	</tr>
	<tr>
		<td colspan=2 align=right><i style='font-weight:bold;width:13px'>{size}:".@file_get_contents("ressources/logs/postfinder.dirsize.txt")."</i></td>
	</tr>
	<tr>
		<td class=legend>{compress_files_after}:</td>
		<td>". Field_array_Hash($times,"BackupMailLogMaxTimeCompressed",$BackupMailLogMaxTimeCompressed,"style:font-size:13px;padding:3px")."</td>
	</tr>	
	<tr>
		<td colspan=2 align='right'><hr>". button("{apply}","PostFinderLogParamSave()")."</td>
	</tr>
	</table>
	</div>
	<script>
	var x_PostFinderLogParamSave= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>3){alert(tempvalue);}	
		RefreshTab('main_config_postfix_search');
	}		
	
	function PostFinderLogParamSave(key){
		var XHR = new XHRConnection();
		XHR.appendData('BackupMailLogPath',document.getElementById('BackupMailLogPath').value);
		XHR.appendData('BackupMailLogMaxTimeCompressed',document.getElementById('BackupMailLogMaxTimeCompressed').value);		
		document.getElementById('postfinder_parameters_div').innerHTML='<center style=\"width:100%\"><img src=img/wait_verybig.gif></center>';
		XHR.sendAndLoad('$page', 'GET',x_PostFinderLogParamSave);
		}	

	</script>
	
	";
	echo $tpl->_ENGINE_parse_body($html);
	
}

function BackupMailLogPathSave(){
	$sock=new sockets();
	$sock->SET_INFO("BackupMailLogPath",$_GET["BackupMailLogPath"]);
	$sock->SET_INFO("BackupMailLogMaxTimeCompressed",$_GET["BackupMailLogMaxTimeCompressed"]);
	
}


	