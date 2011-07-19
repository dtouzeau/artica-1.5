<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.mysql.inc');
	
	
	$users=new usersMenus();
	if(!$users->AsPostfixAdministrator){die("alert('No privileges')");}
	if(isset($_GET["main"])){main();exit;}
	if(isset($_GET["flow"])){main_list();exit;}
	
	js();
	
	
	
function js(){
	
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{postfix_realtime_events}');
	$page=CurrentPageName();
	$html="
		function loadPostfixEvents(){
			YahooLogWatcher(850,'$page?main=yes','$title');
			 setTimeout(\"LoadEventsPostfix()\",1000);
		
		}
		
		function CheckQuery(e){
			if(checkEnter(e)){LoadEventsPostfix();}
		
		}
		
		function LoadEventsPostfix(){
		 LoadAjax('postfixflow','$page?flow=yes&date='+document.getElementById('date_query').value+'&from='+document.getElementById('mailfrom').value+'&to='+document.getElementById('mailto').value);
		}
		
		loadPostfixEvents();
";
	
echo $html;	
}


function main(){
	
$html="<H1>{postfix_realtime_events}</H1>
<div style='float:right'>" . imgtootltip('icon_sync.gif','{refresh}',"LoadEventsPostfix()")."</div>
<p class=caption>{postfix_realtime_events_text}</p>
<table style='width:100%' class=table_form>
<tr>
	<td class=legend>{date}:</td>
	<td>" . Field_text('date_query',null,'width:110px',null,"CheckQuery(event)",null,false,"CheckQuery(event)")."</td>
	<td class=legend nowrap>{sender_address}:</td>
	<td>" . Field_text('mailfrom',null,null,null,"CheckQuery(event)",null,false,"CheckQuery(event)")."</td>
	<td class=legend nowrap>{recipient_address}:</td>
	<td>" . Field_text('mailto',null,null,null,"CheckQuery(event)",null,false,"CheckQuery(event)")."</td>
</tr>
</table>
" . RoundedLightWhite("
<div id='postfixflow' style='width:100%;height:300px;overflow:auto'>
</div>");

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);	
}


function main_list(){
	$date=$_GET["date"];
	$from=$_GET["from"];
	$to=$_GET["to"];
	if(preg_match('#^([0-9]+)$#',$date,$re)){
		$date=date('Y-m') ."-{$re[1]}";;
	}
	
	if(preg_match('#^([0-9]+)-([0-9]+)$#',$date,$re)){
		if(strlen($re[1])>2){
			$date="{$re[1]}-{$re[2]}-".date('d');
		}else{
			$date=date('Y')."-{$re[1]}-{$re[2]}";
		}
		
	}
	
	if($from<>null){
		if(preg_match('#(.*)\@(.+)#',$from,$re)){
			if(trim($re[1])<>null){$q_from=" AND sender_user LIKE '$re[1]'";}
			$q_from_domain=" AND sender_domain LIKE '$re[1]'";
			}else{$q_from=" AND sender_user LIKE '$from'";}
	}
	
		
	if($to<>null){	
	if(preg_match('#(.*)\@(.+)#',$to,$re)){
		if(trim($re[1])<>null){$q_to=" AND delivery_user LIKE '$re[1]'";}
		$q_to_domain=" AND delivery_domain LIKE '$re[1]'";
	}else{$q_to=" AND delivery_user LIKE '$to'";}
	}	
		
	$q_from=str_replace('*','%',$q_from);
	$q_from_domain=str_replace('*','%',$q_from_domain);
	
	$q_to=str_replace('*','%',$q_to);
	$q_to_domain=str_replace('*','%',$q_to_domain);
			
	if($date==null){$date=date('Y-m-d');}
	
	
	$q_date=" AND DATE_FORMAT(time_sended,'%Y-%m-%d')='{$date}'";
	if($date=='*'){$q_date=null;}
	
	$q=new mysql();
	$sql="SELECT id,sender_user,msg_id_text,sender_domain,delivery_user,delivery_domain,delivery_success,time_connect,time_sended,bounce_error,SPAM,filter_reject
	FROM smtp_logs WHERE 1 $q_date$q_from$q_from_domain$q_to_domain$q_to ORDER BY time_sended DESC LIMIT 0,50";

	$results=$q->QUERY_SQL($sql,'artica_events');

	$html="
	<H3>$date &laquo;$from&raquo; &laquo;$to&raquo;</H3>
	<hr>
	<table style='width:100%'>
	<tr>
		<th>&nbsp;</th>
		<th>{date}</th>
		<th>{sender_address}</th>
		<th>&nbsp;</th>
		<th>{recipient_address}</th>
		<th>&nbsp;</th>
	</tR>
		";
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
		if($ligne["sender_user"]==null){$ligne["sender_user"]="unknown";}
		if($ligne["sender_domain"]==null){$ligne["sender_domain"]="unknown";}
		$from="{$ligne["sender_user"]}";
		$top="{$ligne["delivery_user"]}";
		
		$img="icon_mini_read.gif";
		
		if($ligne["delivery_success"]=="no"){$img="icon_err.gif";}
		if($ligne["SPAM"]=="1"){$img="icon_mini_warning.gif";}
		
		
		if(strlen($from)>30){$from=texttooltip(substr($from,0,27).'...',$from);}
		if(strlen($top)>30){$top=texttooltip(substr($top,0,27).'...',$top);}
		
		$tooltip="<div style=font-size:13px>{received}:&nbsp;{$ligne["time_connect"]}<div style=font-size:13px></div><div style=font-size:13px>{$ligne["filter_reject"]}</div></div>";
		$js_tooltip=imgtootltip($img,$tooltip);
		
		
		
		$html=$html ."
		<tr " . CellRollOver().">
			<td valign='top' style=width:1%'>$js_tooltip</td>
			<td valign='top' nowrap><strong>{$ligne["time_sended"]}</strong></td>
			<td valign='top'><strong>$from</strong></td>
			<td valign='top' style=width:1%'><img src='img/fw_bold.gif'></td>
			<td valign='top'><strong>$top</strong></td>
			<td valign='top'><strong>{$ligne["bounce_error"]}</strong></td>
		</tr>
		
		";
		
		
	}
	
	$html=$html . "</table>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);
	
	
}
	
?>