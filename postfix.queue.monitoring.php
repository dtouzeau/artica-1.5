<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");	
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.sockets.inc');
	include_once('ressources/class.ini.inc');
	include_once('ressources/class.artica.graphs.inc');
	include_once('ressources/class.maincf.multi.inc');
	
	

$usersmenus=new usersMenus();
if($usersmenus->AsMailBoxAdministrator==false){header('location:users.index.php');exit;}
if(isset($_GET["PostfixLoadeMailsQueue"])){echo PostfixLoadeMailsQueue();exit;}
if(isset($_GET["MailID"])){MailID();exit;}
if(isset($_GET["PostQueueF"])){PostQueueF();exit();}
if(isset($_GET["TableQueue"])){echo Table_queue();exit;}
if(isset($_GET["DeleteMailID"])){DeleteMailID();exit;}
if(isset($_GET["PostCatReprocess"])){reprocessMailID();exit;}
if(isset($_GET["PostfixDeleteMailsQeue"])){PostfixDeleteMailsQeue();exit;}
if(isset($_GET["js"])){popup_js();exit;}
if(isset($_GET["inline-js"])){popup_js();exit;}
if(isset($_GET["popup"])){popup_tabs();exit;}
if(isset($_GET["smtp_queues"])){popup_index();exit;}
if(isset($_GET["show-queue"])){queue_js();exit;}
if(isset($_GET["popup-queue"])){queue_popup();exit;}
if(isset($_GET["read-queue"])){queue_popup_list();exit;}
if(isset($_GET["popup-message"])){popup_message();exit;}
if(isset($_GET["status"])){status();exit;}
if(isset($_GET["params"])){queue_settings();exit;}
if(isset($_GET["settings-queue-save"])){queue_settings_save();exit;}

if(isset($_GET["details"])){popup_postqueue();exit;}
if(isset($_GET["details-list"])){popup_postqueue_details();exit;}
if(isset($_GET["details-list-from"])){popup_postqueue_details_form();exit;}
if(isset($_GET["details-list-search-to"])){popup_postqueue_details_search();exit;}
if(isset($_GET["details-list-search-from"])){popup_postqueue_details_search();exit;}


if(isset($_GET["postqueue-context"])){postqueue_context_popup();exit;}
if(isset($_GET["postqueue-context-list"])){postqueue_context_list();exit;}

if(isset($_GET["ban-to-domain"])){ban_domains_js();exit;}
if(isset($_GET["ban-to-domain-perform"])){ban_domains_perform();exit;}

if(isset($_GET["banned"])){banned_domains();exit;}
if(isset($_GET["banned-list"])){banned_list();exit;}

if(isset($_GET["banned-delete"])){banned_delete_js();exit;}
if(isset($_GET["banned-delete-perform"])){banned_delete_perform();exit;}





if(isset($_GET["js-message"])){queue_js();exit;}

//postfix_queue_monitoring();

function banned_delete_js(){
	$page=CurrentPageName();
	$domain=$_GET["banned-delete"];
	$hostname=$_GET["hostname"];
	$tpl=new templates();
	$ask=$tpl->javascript_parse_text("{delete} ?");
	
	$html="
	
	var x_bandomain_delete= function (obj) {
		var response=obj.responseText;
		if(response){alert(response);}
		RefreshTab('queue_monitor');
	}	
	
	function bandomain_delete(){
		if(confirm('$ask\\n$domain')){
			var XHR = new XHRConnection();
			XHR.appendData('banned-delete-perform','$domain');
			XHR.appendData('hostname','$hostname');
			XHR.sendAndLoad('$page', 'GET',x_bandomain_delete);
		}
	}
	bandomain_delete();
	";
	echo $html;	
	
}

function ban_domains_js(){
	$page=CurrentPageName();
	$domain=$_GET["ban-to-domain"];
	$hostname=$_GET["hostname"];
	$tpl=new templates();
	$ask=$tpl->javascript_parse_text("{ban_to_domain_ask}");
	
	$html="
	
	var x_bandomain= function (obj) {
		var response=obj.responseText;
		if(response){alert(response);}
	}	
	
	function bandomain(){
		if(confirm('$ask\\n$domain')){
			var XHR = new XHRConnection();
			XHR.appendData('ban-to-domain-perform','$domain');
			XHR.appendData('hostname','$hostname');
			XHR.sendAndLoad('$page', 'GET',x_bandomain);
		}
	}
	bandomain();
	";
	echo $html;
}

function ban_domains_perform(){
	$sql="INSERT INTO postfix_bad_domains(domain,hostname)
	VALUES('{$_GET["ban-to-domain-perform"]}','{$_GET["hostname"]}')";
	
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-multi-transport-maps={$_GET["hostname"]}");
	echo "OK";
}

function banned_delete_perform(){
	$sql="DELETE FROM postfix_bad_domains
	WHERE domain='{$_GET["banned-delete-perform"]}' AND hostname='{$_GET["hostname"]}'";
	
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-multi-transport-maps={$_GET["hostname"]}");
	
}

function banned_domains(){
	$page=CurrentPageName();
	$html="<div id='postqueue-banned-".md5($_GET["hostname"])."' style='width:100%;height:650px;overflow:auto'></div>
	
	<script>
		LoadAjax('postqueue-banned-".md5($_GET["hostname"])."','$page?hostname={$_GET["hostname"]}&banned-list=yes');
	</script>
	";
	echo $html;	
	
	
}

function banned_list(){
	$sql="SELECT * FROM postfix_bad_domains WHERE hostname='{$_GET["hostname"]}' ORDER BY domain";
	
	$tpl=new templates();
	$page=CurrentPageName();
	$q=new mysql();
	
$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th>&nbsp;</th>
	<th>{domain}</th>
	<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	$datas=array();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			
			$delete=imgtootltip("delete-32.png","{delete}","Loadjs('$page?banned-delete={$ligne["domain"]}&hostname={$_GET["hostname"]}')");
			$html=$html."
			<tr class=$classtr>
					<td width=1%><img src='img/bandwith-limit-www-32.png'></td>
					<td width=100% style='font-size:16px;' nowrap>{$ligne["domain"]}</td>
					<td width=1% >$delete</td>
			</td>
			</tr>";
		}

	$html=$html."</table>";
	echo $tpl->_ENGINE_parse_body($html);
}


function postqueue_context_popup(){
	$page=CurrentPageName();
	$html="<div id='postqueue-context-".md5($_GET["postqueue-context"])."' style='width:100%;height:650px;overflow:auto'></div>
	
	<script>
		LoadAjax('postqueue-context-".md5($_GET["postqueue-context"])."','$page?hostname={$_GET["hostname"]}&postqueue-context-list=". urlencode($_GET["postqueue-context"])."');
	</script>
	";
	echo $html;
}

function postqueue_context_list(){
	if($_GET["postqueue-context-list"]=="unknown"){$_GET["postqueue-context-list"]="";}
	$sql="SELECT * FROM postqueue WHERE context='{$_GET["postqueue-context-list"]}' AND instance='{$_GET["hostname"]}'";
	
	$tpl=new templates();
	$page=CurrentPageName();
	$q=new mysql();
	
$html1="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th>&nbsp;</th>
	<th>{date}</th>
	<th>{from}</th>
	<th>{to}</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	$datas=array();
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$line["TO"]=trim($ligne["recipients"]);
			$msgid=$ligne["msgid"];
			$line["FROM"]=$ligne["from"];
			$line["STATUS"]=$ligne["event"];
			$line["DATE"]=$ligne["zDate"];
			$datas["SEARCH_FROM"][$ligne["from_domain"]]=$ligne["from_domain"];
			$rr=explode(",",$ligne["recipients"]);
			$to=null;
			while (list ($domain, $other) = each ($rr) ){
				if(preg_match("#(.+?)@(.+)#",$other,$re)){
					$datas["SEARCH_TO"][$re[2]]=$re[2];
				}
				$to=$to."<li>$other</li>";
				
			}
			$status=null;
			if($line["STATUS"]<>null){
				$status="
				<tr class=$classtr>
				<td colspan=4><strong style='font-size:9px'>{$line["STATUS"]}</td>
				</tr>
				";
			}
			
			$js="Loadjs('$page?js-message=$msgid')";
			$html1=$html1."
			<tr class=$classtr>
					<td width=1%>". imgtootltip("email-view-32.png","{view}",$js)."</td>
					<td width=10% style='font-size:12px;' nowrap><span id='DELETE_DATE_$msgid'>{$line["DATE"]}</span></td>
					<td width=33% style='font-size:12px;font-weight:bold' nowrap><span id='DELETE_FROM_$msgid'>{$line["FROM"]}</td>
					<td width=33% style='font-size:12px;font-weight:bold' nowrap><span id='DELETE_TO_$msgid'>$to</td>
					$status
			</td>
			</tr>";
		}

		
	$html1=$html1."</table>";
	
	$html="
	<center>
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' width=220px>
	<thead class='thead'>
		<tr>
		<th width=99%>{to}</th>
		<th width=1%>{ban}</th>
		</tr>
	</thead>
	<tbody class='tbody'>";
	
	
	while (list ($domain, $other) = each ($datas["SEARCH_TO"]) ){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$html=$html."
			<tr class=$classtr>
					<td style='font-size:16px'>$domain</td>
					<td>". imgtootltip("bandwith-limit-www-32.png","{ban_this_destination}","Loadjs('$page?ban-to-domain=$domain&hostname={$_GET["hostname"]}')")."</td>
			</tr>";
		
	}
	$html=$html."</table></center>$html1";
	
	echo $tpl->_ENGINE_parse_body($html);
	
}






function queue_settings(){
	$page=CurrentPageName();
	$tpl=new templates();
	if($_GET["hostname"]=="master"){$_GET["ou"]="master";}
	$master=new maincf_multi($_GET["hostname"],$_GET["ou"]);
	$in_flow_delay=$master->GET("in_flow_delay");
	$minimal_backoff_time=$master->GET("minimal_backoff_time");
	$maximal_backoff_time=$master->GET("maximal_backoff_time");
	$bounce_queue_lifetime=$master->GET("bounce_queue_lifetime");
	$default_process_limit=$master->GET("default_process_limit");
	$maximal_queue_lifetime=$master->GET("maximal_queue_lifetime");
	
	if($in_flow_delay==null){$in_flow_delay="1s";}
	if($minimal_backoff_time==null){$minimal_backoff_time="300s";}
	if($maximal_backoff_time==null){$maximal_backoff_time="4000s";}
	if($default_process_limit==null){$default_process_limit="100";}
	if($bounce_queue_lifetime==null){$bounce_queue_lifetime="5d";}
	if($maximal_queue_lifetime==null){$maximal_queue_lifetime="5d";}	
$html="
<div id='div-queue-parms'>
	<table style='width:99.5%' class=form>
			<tr>
				<td class=legend>{minimal_backoff_time}:</td>
				<td>". Field_text("minimal_backoff_time",$minimal_backoff_time,"width:90px;padding:3px;font-size:13px")."</td>
				<td width=1%>". help_icon("{minimal_backoff_time_text}")."</td>
			</tr>			
			<tr>
				<td class=legend>{maximal_backoff_time}:</td>
				<td>". Field_text("maximal_backoff_time",$maximal_backoff_time,"width:90px;padding:3px;font-size:13px")."</td>
				<td width=1%>". help_icon("{maximal_backoff_time_text}")."</td>
			</tr>
			<tr>
				<td class=legend>{bounce_queue_lifetime}:</td>
				<td>". Field_text("bounce_queue_lifetime",$bounce_queue_lifetime,"width:90px;padding:3px;font-size:13px")."</td>
				<td width=1%>". help_icon("{bounce_queue_lifetime_text}")."</td>
			</tr>
			<tr>
				<td class=legend valign='top' nowrap>{maximal_queue_lifetime}&nbsp;:</strong></td>
				<td>". Field_text("maximal_queue_lifetime",$maximal_queue_lifetime,"width:90px;padding:3px;font-size:13px")."</td>
				<td width=1%>". help_icon("{maximal_queue_lifetime_text}")."</td>
			</tr>
			<tr>
				<td colspan=3 align='right'>
					<hr>". button("{apply}","SaveQueueSettings()")."</td>
			</tr>	
	</table>
	
	<script>
	var x_SaveQueueSettings= function (obj) {
		var results=obj.responseText;
		if(results.length>0){alert(results);}
		RefreshTab('queue_monitor');
	}	
	
	
		function SaveQueueSettings(){	
			var XHR = new XHRConnection();
			XHR.appendData('settings-queue-save','yes');
			XHR.appendData('hostname','{$_GET["hostname"]}');
			XHR.appendData('ou','{$_GET["ou"]}');
			
			XHR.appendData('minimal_backoff_time',document.getElementById('minimal_backoff_time').value);
			XHR.appendData('maximal_backoff_time',document.getElementById('maximal_backoff_time').value);
			XHR.appendData('bounce_queue_lifetime',document.getElementById('bounce_queue_lifetime').value);
			XHR.appendData('maximal_queue_lifetime',document.getElementById('maximal_queue_lifetime').value);
			document.getElementById('div-queue-parms').innerHTML='<center><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',x_SaveQueueSettings);	
			}	
	
	
	</script>
	
	
"	;
	echo $tpl->_ENGINE_parse_body($html);		
	
}

function queue_settings_save(){
	$master=new maincf_multi($_GET["hostname"],$_GET["ou"]);
	$master->SET_VALUE("minimal_backoff_time",$_GET["minimal_backoff_time"]);
	$master->SET_VALUE("maximal_backoff_time",$_GET["maximal_backoff_time"]);
	$master->SET_VALUE("bounce_queue_lifetime",$_GET["bounce_queue_lifetime"]);
	$master->SET_VALUE("maximal_queue_lifetime",$_GET["maximal_queue_lifetime"]);
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?postfix-multi-settings={$_GET["hostname"]}");
}


function status(){
	$page=CurrentPageName();
	$q=new mysql();
	$tpl=new templates();
	$hostname=$_GET["hostname"];
	$ou_encoded=base64_encode("master");
	$pause_queue=Paragraphe('pause-64.png','{pause_the_queue}','{pause_the_queue_text}',"javascript:Loadjs('postfix.freeze.queue.php?hostname=$hostname')");
	$mems=Paragraphe('bg_memory-64.png','{postfix_tmpfs}','{postfix_tmpfs_text}',"javascript:Loadjs('domains.postfix.memory.php?ou=$ou_encoded&hostname=$hostname')");	
	$repro_queue=Paragraphe('restart-queue-64.png','{reprocess_queue}','{reprocess_queues_text}',"javascript:PostQueueFF()");
	$q=new mysql();
	$sql="SELECT SUM(`size`) as tsize,COUNT(msgid) as tcount FROM postqueue WHERE `instance`='master'";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_events'));	
	$tot=$ligne["tcount"];
	$tot_size=$ligne["tsize"]/1024;
	$tot_size=FormatBytes($tot_size);	
	
$table[]="<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
		<th>{mails}</th>
		<th>{errors}</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	
	$filename="postqueue.status.$hostname.png";
	$gp=new artica_graphs(dirname(__FILE__)."/ressources/logs/web/$filename",50);
	$sql="SELECT COUNT(context) as tcount,context FROM postqueue WHERE instance='$hostname' GROUP BY context ORDER BY tcount DESC";
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){$table[]="<tr><td>". $q->mysql_error."</td></tr>";}
	$page=CurrentPageName();
	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($ligne["context"]==null){$ligne["context"]="{unknown}";}
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		$js="PostQueueContext('{$ligne["context"]}');";
		$style=" style='text-decoration:underline' ";
	$table[]="
	<tr class=$classtr>
			<td style='font-size:14px;font-weight:bold' width=1% align='center'><a href=\"javascript:blur()\" OnClick=\"javascript:$js\">{$ligne["tcount"]}</a></td>
			<td style='font-size:14px;font-weight:bold' nowrap width=99%><a href=\"javascript:blur()\" OnClick=\"javascript:$js\" $style>{$ligne["context"]}</a></td>
		</tr>
	";		
		
		$gp->xdata[]=$ligne["tcount"];
		$gp->ydata[]=$tpl->javascript_parse_text($ligne["context"]);			

	}	
	
	
	$table[]="</table>";
	$table_finale=@implode("\n",$table);
	$time=time();
	$gp->width=350;
	$gp->height=450;
	$gp->ViewValues=false;
	$gp->x_title="{errors}";
	$gp->pie();	

	if(count($gp->xdata)>1){
		$img="<center><img src='ressources/logs/web/$filename?time=$time'></center>";
	}
	
	$html="
	<center style='font-size:16px'>$tot {mails}, $tot_size</center>
	<table style='width:100%'>
	<tr>
		<td valign='top'>	
			$img
			<hr>
			$table_finale
		</td>
		<td valign='top'>
			$pause_queue
			$mems
			$repro_queue
		</td>
	</tr>
	</table>
	
	<script>
	
var x_PostQueueFF= function (obj) {
	var results=obj.responseText;
	if(results.length>2){alert(results);}
	RefreshTab('queue_monitor'); 
	}		
	
	function PostQueueFF(){
		var XHR = new XHRConnection();
		XHR.appendData('PostQueueF','yes');
		XHR.appendData('hostname','{$_GET["hostname"]}');
		XHR.appendData('output','yes');
		XHR.setRefreshArea('queuelist');
		XHR.sendAndLoad('postfix.queue.monitoring.php', 'GET',x_PostQueueFF);
	}
	
	function PostQueueContext(context){
		var zcontext=escape(context);
		YahooWin4(850,'$page?postqueue-context='+zcontext+'&hostname=$hostname',context);
	
	}
	
	
</script>	
	";
	echo $tpl->_ENGINE_parse_body($html);		
		
}


function popup_js(){
	
	$page=CurrentPageName();
	$tpl=new templates();
	$js_add=file_get_contents("js/artica_postfix_queue.js");
	$title=$tpl->_ENGINE_parse_body('{queue_monitoring}');
	if(!isset($_GET["hostname"])){$_GET["hostname"]="master";}
	$start="StartIndex();";
	if(isset($_GET["inline-js"])){$start="StartIndexInLine();";}
	$html="
	$js_add
	
	function StartIndex(){
		YahooWinS(720,'$page?popup=yes&hostname={$_GET["hostname"]}','$title');
	
	}
	
	function StartIndexInLine(){
		document.getElementById('BodyContent').innerHTML='<center><img src=img/wait_verybig.gif></center>';
		$('#BodyContent').load('$page?popup=yes&hostname={$_GET["hostname"]}');
	
	}	
	
	$start
	";
	
	
	echo $html;
	}
	
function queue_js(){
$page=CurrentPageName();
$queue=$_GET["show-queue"];
	$tpl=new templates();	
	$delete_message_text=$tpl->javascript_parse_text("{delete_message_text}");
	$start="Start$queue();";
	
	if(isset($_GET["js-message"])){$start="PostCat('{$_GET["js-message"]}');";}
	
	$html="
	
	function redqueue(){
		LoadAjax('{$queue}_queueidlist','$page?read-queue=$queue');
	
	}
	
	function Start$queue(){
		YahooWin(750,'$page?popup-queue=$queue&count={$_GET["count"]}','$queue ({$_GET["count"]}) mails');
		setTimeout(\"redqueue()\",1000);
	}
	
	function PostCat(message){
		YahooWin2('700','$page?popup-message='+message,message);
	}
	
var X_PostCatDelete= function (obj) {
	var results=obj.responseText;
	if(results.length>2){alert(results);}
	YahooWin2Hide();
	RefreshTab('queue_monitor'); 
	}	
	
	function PostCatDelete(message){
		if(confirm('$delete_message_text ?\\n'+message)){
			var XHR = new XHRConnection();
			XHR.appendData('DeleteMailID',message);
			document.getElementById('loupemessage').innerHTML='<center><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',X_PostCatDelete);	
		}
	}
	
	function PostCatReprocess(message){
			var XHR = new XHRConnection();
			XHR.appendData('PostCatReprocess',message);
			document.getElementById('loupemessage').innerHTML='<center><img src=img/wait_verybig.gif></center>';
			XHR.sendAndLoad('$page', 'GET',X_PostCatDelete);		
	}
	
	
function switchDivViewQueue(id){
	document.getElementById('messageidtable').style.display='none';
   	document.getElementById('messageidbody').style.display='none';
   	document.getElementById(id).style.display='block';   
   	
}	
	
	$start";
	
	echo $html;	
}

function popup_postqueue(){
	$page=CurrentPageName();
	
	$html="
		<div class=explain>{postqueue_list_explain}</div>
		<div id='postqueue-details-form' style='margin:5px'></div>
		<div id='postqueue-details' style='heigth:550px;overflow:auto'></div>
		
		
		
		<script>
			function PostQueueDetails(){
				LoadAjax('postqueue-details','$page?details-list=yes&hostname={$_GET["hostname"]}');
			
			}
			
			PostQueueDetails();
		</script>
		";
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body($html);
	
}


function popup_postqueue_details(){
	$tpl=new templates();
	$page=CurrentPageName();
	$q=new mysql();
	
$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th>&nbsp;</th>
	<th>{date}</th>
	<th>{from}</th>
	<th>{to}</th>
	<th>{delete}</th>
	</tr>
</thead>
<tbody class='tbody'>";	
	
	$sql="SELECT * FROM postqueue WHERE instance='{$_GET["hostname"]}' ORDER BY zDate LIMIT 0,200";
	$datas=array();
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
			$line["TO"]=trim($ligne["recipients"]);
			$msgid=$ligne["msgid"];
			$line["FROM"]=$ligne["from"];
			$line["STATUS"]=$ligne["event"];
			$line["DATE"]=$ligne["zDate"];
			$datas["SEARCH_FROM"][$ligne["from_domain"]]=$ligne["from_domain"];
			$rr=explode(",",$ligne["recipients"]);
			$to=null;
			while (list ($domain, $other) = each ($rr) ){
				if(preg_match("#(.+?)@(.+)#",$other,$re)){
					$datas["SEARCH_TO"][$re[2]]=$re[2];
				}
				$to=$to."<li>$other</li>";
				
			}
			
			
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$js="Loadjs('$page?js-message=$msgid')";
		
		
			$html=$html."
			<tr class=$classtr>
					<td width=1%>". imgtootltip("email-view-32.png","{view}",$js)."</td>
					<td width=10% style='font-size:12px;' nowrap><span id='DELETE_DATE_$msgid'>{$line["DATE"]}</span></td>
					<td width=33% style='font-size:12px;font-weight:bold' nowrap><span id='DELETE_FROM_$msgid'>{$line["FROM"]}</td>
					<td width=33% style='font-size:12px;font-weight:bold' nowrap><span id='DELETE_TO_$msgid'>$to</td>
					<td width=1%>". Field_checkbox("DELETE_$msgid",1,0,"DeleteMSG('$msgid')")."</td>
					</tr>
					<tr class=$classtr>
					<td colspan=6><code style='font-size:11px;font-style:italic'>{$line["STATUS"]}</code></td>
					</tr>
			</td>
			</tr>";
			
			$deleteall[]="document.getElementById('DELETE_$msgid').checked=true;";
			$deleteall[]="DeleteMSG('$msgid');";
			
		}
		
		reset($datas);
		if(is_array($datas["SEARCH_FROM"])){
			while (list ($domain, $other) = each ($datas["SEARCH_FROM"]) ){$froms[$domain]=$domain;}
		}
		if(is_array($datas["SEARCH_TO"])){
			while (list ($domain, $other) = each ($datas["SEARCH_TO"]) ){$tos[$domain]=$domain;}
		}
		
		
		
		$froms[null]="{select}";
		$tos[null]="{select}";
		
		$form="
		<table style='width:100%' class=form>
		<tr>
			<td valign='middle' class=legend>{from}:</td>
			<td>". Field_array_Hash($froms,"search_from",null,"PostQueueDetailsSearchFrom()",null,0,"font-size:13px;padding:3px")."</td>
						<td valign='middle' class=legend>{to}:</td>
			<td>". Field_array_Hash($tos,"search_to",null,"PostQueueDetailsSearchFrom()",null,0,"font-size:13px;padding:3px")."</td>
			<td><div style='text-align:right'>". imgtootltip("refresh-32.png","{refresh}","PostQueueDetails()")."</div></td>
		</tr>				
		</tr>
			
		</table>
		
		
		
		";
		
		@file_put_contents("ressources/logs/web/popup_postqueue_details_forms.html",$form);
		
		
	
	
	$html=$html."</tbody></table>
	<div style='text-align:right;margin:5px'>". imgtootltip('database-48-delete.png',"{delete_all}","DeleteAllQueueMessages()")."</div>
	<script>
		LoadAjax('postqueue-details-form','$page?details-list-from=yes&hostname={$_GET["hostname"]}');
		

		
		function PostQueueDetailsSearchFrom(){
			var search_to=escape(document.getElementById('search_from').value);
			var search_to_pattern=escape(document.getElementById('search_to').value);
			LoadAjax('postqueue-details','$page?details-list-search-from=yes&from='+search_to+'&to='+search_to_pattern+'&hostname={$_GET["hostname"]}');		
		}
		
		function PostQueueDetailsSearchFromPress(e){
			if(checkEnter(e)){PostQueueDetailsSearchFrom();}
		}
		
		function PostQueueDetailsSearchToPress(e){
			if(checkEnter(e)){PostQueueDetailsSearchTo();}
		}
		
		function DeleteMSG(msgid){
			if(document.getElementById('DELETE_'+msgid).checked){
				var XHR = new XHRConnection();
				XHR.appendData('DeleteMailID', msgid);
				XHR.appendData('hostname', '{$_GET["hostname"]}');
				
				XHR.sendAndLoad('$page', 'GET');
				
				document.getElementById('DELETE_DATE_'+msgid).style.textDecoration='line-through';
				document.getElementById('DELETE_FROM_'+msgid).style.textDecoration='line-through';
				document.getElementById('DELETE_TO_'+msgid).style.textDecoration='line-through';
			}
		
		}
		
		
		function DeleteAllQueueMessages(){\n". @implode("\n",$deleteall)."\n}
	
	</script>
	
	";

	
	echo $tpl->_ENGINE_parse_body($html);
}

function popup_postqueue_details_search(){
	$tpl=new templates();
	$page=CurrentPageName();
	$q=new mysql();
	$search_from=$_GET["from"];
	$search_to=$_GET["to"];
	
	
	if($search_from<>null){$from=" AND from_domain='$search_from' ";}
	
	if($search_to<>null){
		$to=" AND recipients LIKE '%@$search_to'";
	}	
	
		$sql="SELECT * FROM postqueue WHERE instance='{$_GET["hostname"]}' $from $to ORDER BY zDate LIMIT 0,200";	
		
		
	
	$datas=array();
	$results=$q->QUERY_SQL($sql,"artica_events");
	if(!$q->ok){echo "<H2>$q->mysql_error</H2>";}	

	

	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th>&nbsp;</th>
	<th>{date}</th>
	<th>&nbsp;</th>
	<th>{from}</th>
	<th>{to}</th>
	<th>{delete}</th>
	</tr>
</thead>
<tbody class='tbody'>";		
	
	

		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
			$msgid=$ligne["msgid"];
			$to=$ligne["recipients"];
			$date=$ligne["zDate"];
			$from=$ligne["from"];
			if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
			$js="Loadjs('$page?js-message=$msgid')";
			
		$rr=explode(",",$ligne["recipients"]);
			while (list ($domain, $other) = each ($rr) ){
				$to=$to."<li>$other</li>";
				
			}			
		
		
			$html=$html."
			<tr class=$classtr>
					<td width=1%>". imgtootltip("email-view-32.png","{view} $msgid",$js)."</td>
					<td width=10% style='font-size:12px;' nowrap><span id='DELETE_DATE_$msgid'>$date</span></td>
					<td width=33% style='font-size:12px;font-weight:bold' nowrap><span id='DELETE_FROM_$msgid'><a href=\"javascript:blur();\" OnClick=\"$js\" style='text-decoration:underline'>$from</a></td>
					<td width=1%><img src='img/fw_bold.gif'></td>
					<td width=33% style='font-size:12px;font-weight:bold' nowrap><span id='DELETE_TO_$msgid'>$to</td>
					<td width=1%>". Field_checkbox("DELETE_$msgid",1,0,"DeleteMSG('$msgid')")."</td>
			</td>
			</tr>
			<tr class=$classtr>
				<td colspan=6><i>{$ligne["event"]}</i></td>
			</tr>
			";
			
			$deleteall[]="document.getElementById('DELETE_$msgid').checked=true;";
			$deleteall[]="DeleteMSG('$msgid');";
			
		}

	
		
		$html=$html."</tbody>
		
		</table>
		<div style='text-align:right;margin:5px'>". imgtootltip('database-48-delete.png',"{delete_all}","DeleteAllSearch()")."</div>
		<script>
			function DeleteAllSearch(){\n". @implode("\n",$deleteall)."\n}
		</script>
		
		";
		echo $tpl->_ENGINE_parse_body($html);
	
}

function popup_postqueue_details_form(){
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body(@file_get_contents("ressources/logs/web/popup_postqueue_details_forms.html"));
	
}



function popup_message(){
	include_once(dirname(__FILE__).'/ressources/class.mime.parser.inc');
	include_once(dirname(__FILE__).'/ressources/rfc822_addresses.php');
	$messageid=$_GET["popup-message"];
	$sock=new sockets();
	$datas=$sock->getfile("view_queue_file:$messageid");
	
	if(preg_match('#\*\*\* ENVELOPE RECORDS.+?\*\*\*(.+?)\s+\*\*\*\s+MESSAGE CONTENTS#is',$datas,$re)){
		$table_content=$re[1];
	}
if(preg_match('#\*\*\* MESSAGE CONTENTS.+?\*\*\*(.+?)\*\*\*\s+HEADER EXTRACTED #is',$datas,$re)){
		$message_content=$re[1];
	}	
	
$tbl=explode("\n",$table_content);
while (list ($num, $val) = each ($tbl) ){
	if(trim($val)==null){continue;}
	if(preg_match('#(.+?):(.+)#',$val,$ri)){
		$fields[$ri[1]]=trim($ri[2]);
	}
	
}
if(preg_match('#^([0-9]+)#',$fields["message_size"],$ri)){
	$fields["message_size"]=FormatBytes(($fields["message_size"]/1024));
}

	


$table="
<table style='width:99%'>";
if(is_array($fields)){
	while (list ($num, $val) = each ($fields) ){
		$table=$table . "
		<tr>
			<td class=legend>{{$num}}:</td>
			<td><strong style='width:11px'>{$val}</strong></td>
		</tr>
		";
		
	}
}
$table=$table . "</table>";
$message_content=htmlspecialchars($message_content);
$messagesT=explode("\n",$message_content);
$message_content=null;
while (list ($num, $val) = each ($messagesT) ){
	if(trim($val)==null){continue;}
	$message_content=$message_content."<div><code>$val</code></div>";
	
	
}

$html="
<H1>{show_mail}:$messageid</H1>
<div id='loupemessage'>
<table style='width:100%'>
<tr>
	<td valign='top'>
		<table style='width:100%'>
		<tr>
		
		<td>" . Paragraphe("64-banned-phrases.png",'{routing_info}','{routing_info_text}',"javascript:switchDivViewQueue('messageidtable');")."</td>
		</tr>
		<tr>
		<td>" . Paragraphe("64-banned-regex.png",'{body_message}','{body_message_text}',"javascript:switchDivViewQueue('messageidbody');")."</td>
		</tr>
		<tr>
		<td>" . Paragraphe("64-refresh.png",'{reprocess_message}','{reprocess_message_text}',"javascript:PostCatReprocess('$messageid');")."</td>
		</tr>		
		<tr>
		<td>" . Paragraphe("delete-64.png",'{delete_message}','{delete_message_text}',"javascript:PostCatDelete('$messageid');")."</td>
		</tr>	
	
		
		
		</table>
	</td>
	<td valign='top'>
	<div id='messageidbody' style='display:none;width:100%;height:300px;overflow:auto'>$message_content
	</div>
	<div id='messageidtable' style='display:block;width:100%;height:300px;overflow:auto'>$table</div>
	</td>
</tr>
</table>
</div>
";

$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
}


function queue_popup(){
	$queue=$_GET["popup-queue"];
	$count=$_GET["count"];
	if($queue=="defer"){$qtxt="deferred";}else{$qtxt=$queue;}
	if($queue=="trace"){$qtxt=null;}
	if($queue=="bounce"){$qtxt=null;}
	if($qtxt<>null){$explain="<p class=caption>{{$qtxt}_text}</p>";}
	$html="
	<H1>$queue</H1>
	$explain
	<div id='{$queue}_queueidlist'></div>
	";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($html);	
}

function queue_popup_list(){
	$queue=$_GET["read-queue"];
	$sock=new sockets();
	$datas=$sock->getFrameWork("cmd.php?DumpPostfixQueue=$queue");
	$tbl=explode("\n",$datas);
	
	if(is_array($tbl)){
		$html="<table style='width:99%' class=table_form>
		<tr>
			<th>&nbsp;</th>
			<th>{time}</th>
			<th>{sender}</th>
			<th>{recipient}</th>
			<th>{subject}</th>
			</tr>";
		while (list ($num, $val) = each ($tbl) ){
		$val=str_replace('<sender></sender>','<sender>unknown</sender>',$val);
		$count=0;
		$max=30;

		if(preg_match('#<time>(.+?)</time><named_attr>(.+?)</named_attr><sender>(.+?)</sender><recipient>(.+?)</recipient><subject>(.+?)</subject><MessageID>(.+?)</MessageID>#',$val,$regs)){
			$count=$count+1;
			$file=$regs[1];
			$path=$regs[2];
			$time=PostFixTimeToPhp($regs[1]);
			$named=$regs[2];
			$sender=$regs[3];
			$seemail=imgtootltip('spotlight-18.png','{show_mail}',"PostCat('{$regs[6]}')");
			$recipient=$regs[4];
			$subject=htmlentities('"'.$regs[5].'"');
			
		if(strlen($sender)>$max){$sender=texttooltip(substr($sender,0,27).'...',$sender,null,null,1);}
		if(strlen($recipient)>$max){$recipient=texttooltip(substr($recipient,0,27).'...',$recipient,null,null,1);}			
		if(strlen($subject)>$max){$subject=texttooltip(substr($subject,0,27).'...',$subject,null,null,1);}
			
			$html=$html . "<tr ". CellRollOver().">
			<td width=1% style='font-size:11px'>$seemail</td>
			<td width=2% nowrap style='font-size:11px'>$time</td>
			<td nowrap style='font-size:11px'>$sender</td>
			<td nowrap style='font-size:11px'>$recipient</td>
			<td style='font-size:11px'>$subject</td>
			</tr>
			
			";
			
		}
	}	
	

	
$html=$html . "</table>";}

if($count==0){$err="<div style='font-size:13px;font-weight:bolder;padding:10px;margin:5px;border:1px solid #CCCCCC;background-color:white'>{too_late_or_no_queue_files}</div>";}

$div="<div style='width:100%;height:300px;overflow:auto'>$err$html</div>";
$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($div);
}


function popup_tabs(){
	$array["status"]="{status}";
	$array["details"]="{emails}";
	$array["params"]="{parameters}";
	$array["banned"]="{banned_domains}";
	$array["smtp_queues"]='{smtp_queues}';
	$tpl=new templates();
	$page=CurrentPageName();

	
	while (list ($num, $ligne) = each ($array) ){
		$ligne=$tpl->_ENGINE_parse_body("$ligne");
		$html[]= "<li><a href=\"$page?$num=yes&hostname={$_GET["hostname"]}\"><span>$ligne</span></a></li>\n";
	}
	
	
	echo "
	<div id=queue_monitor style='width:100%;height:750px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#queue_monitor').tabs({
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

	
function popup_index(){
	
	$html="
	
	<input type='hidden' id='remove_mailqueue_text' value=\"{remove_mailqueue_text}\">
	<table style='width:100%'>
	<tr>
		<td width=1%><img src='img/bg_postfix_queue.png'></td>
		<td valign='top'>
			<div id=table_queue>".Table_queue()."</div>
		</td>
	</tr>
	<tr>	
	</table>
	
	";
	
	
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);
	
}



function postfix_queue_monitoring(){
	$page=CurrentPageName();
	if(!isset($_SESSION["uid"])){header('location:logon.php');exit();}
	//".Table_queue() . "
	$html="
	<input type='hidden' id='remove_mailqueue_text' value=\"{remove_mailqueue_text}\">
	<table style='width:100%'>
	<tr>
		<td width=1%><img src='img/bg_postfix_queue.jpg'></td>
		<td valign='top'>
			<div id=table_queue></div>
		</td>
	</tr>
	<tr>
	<td colspan=2>
	<table style='width:100%'>
		<tr>
			<td valign='top' width=50%>
			" . RoundedLightGreen("<H5>{incoming}</H5>{incoming_text}")."<br>".			
				RoundedLightGreen("<H5>{active}</H5>{active_text}")."<br>
			</td>
			<td valign='top' width=50%>".			
				RoundedLightGreen("<H5>{deferred}</H5>{deferred_text}")."<br>".			
				RoundedLightGreen("<H5>{maildrop}</H5>{maildrop_text}")."<br>			
			</td>
		</tr>
	</table>
	</tr>
	</table>
	
			
		
	<div id='queuelist' style='width:650px'></div>
	
	
	<script>LoadAjax('table_queue','$page?TableQueue=yes');</script>
	";
	
	$cfg["JS"][]="js/artica_postfix_queue.js";
	//$cfg["JS"][]="js/mootools.js";
	
	$tpl=new template_users('{queue_monitoring}',$html,0,0,0,0,$cfg);
	echo $tpl->web_page;	
}

function Table_queue(){
	$sock=new sockets();
	$datas=$sock->getFrameWork("cmd.php?postfixQueues=yes");
	$queues=unserialize($sock->getFrameWork("cmd.php?postfixQueues=yes"));
	$page=CurrentPageName();

	$html="<table style='padding:3px;margin:4px;width:240px' class=table_form>
			<tr>
				<th>{queue}&nbsp;&nbsp;</td>
				<th>{email_number}</td>
				<th>&nbsp;</th>
				<th>&nbsp;</th>
			</tr>";

	
	while (list ($queuename, $number) = each ($queues) ){
			if(!is_numeric($number)){continue;}
			$reg[1]=$queuename;
			$reg[2]=$number;
			$tdroll=CellRollOver() ;
			
			$jsDeleteQueue=imgtootltip('x.gif',"{remove_mailqueue} :<strong>{$reg[1]}","PostfixDeleteMailsQeue('{$reg[1]}');");
			$js_showqueue=CellRollOver("Loadjs('$page?show-queue={$reg[1]}&count={$reg[2]}')");
			if($reg[2]>0){
				$seequeue=imgtootltip('spotlight-18.png','{show_queue}',"Loadjs('$page?show-queue={$reg[1]}&count={$reg[2]}')");
			}else{$seequeue="&nbsp;";}
			
			$html=$html . "
			<tr $tdroll>
				<td align='right' $js_showqueue><strong>{$reg[1]}:&nbsp;</strong></td
				<td align='left' $js_showqueue><strong>{$reg[2]}&nbsp;</strong></td>
				<td align='center'>$seequeue</td>
				<td width=1%>$jsDeleteQueue</td>
			</tr>
			";
			
		
		
	}
	$page=CurrentPageName();
	$html=$html . "
	<td><center><input type='button' OnClick=\"javascript:LoadAjax('table_queue','$page?TableQueue=yes');\" value='{refresh}'></center></td>
	<td><center><input type='button' OnClick=\"javascript:PostQueueF();\" value='{reprocess_queue}'></center></td>
	<td>&nbsp;</td>
	</table>
	";
	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
	
}


function Tabs($numberPages,$queue){
	for($i=0;$i<=$numberPages;$i++){
		if($_GET["tab"]==$i){$class="id=tab_current";}else{$class=null;}
		$ligne_number=$i+1;
		$ligne="{page} " . $ligne_number;
		$html=$html . "<li><a href=\"#\" OnClick=\"javascript:PostfixLoadeMailsQueue('$queue','','$i');\" $class>$ligne</a></li>\n";
			
		}
	return "<div id=tablist>$html</div>";	
	
}

function PostfixLoadeMailsQueue(){
	$tpl=new templates();
	$tot=$_GET["total"];
	if(!isset($_GET["numStart"])){$numStart=0;}
	if(!isset($_GET["tab"])){$tab=0;}else{$tab=$_GET["tab"];}
	if($tab==''){$tab=0;}
	$queue_name=$_GET["PostfixLoadeMailsQueue"];
	$ini=new Bs_IniHandler();
	if(!is_file('ressources/databases/postfix-queue-cache.conf')){return $tpl->_ENGINE_parse_body("{no_cache_created}");}
	$ini->loadFile('ressources/databases/postfix-queue-cache.conf');
	$PagesNumber=$ini->get($queue_name,'PagesNumber');
	
	if($PagesNumber>0){$tabublation=Tabs($PagesNumber,$queue_name);}
	
	
	$filetemp="ressources/databases/queue.list.$tab.$queue_name.cache";
	if(!is_file($filetemp)){return $tpl->_ENGINE_parse_body("<strong>{unable_to_locate}: $filetemp</strong>");}
	
	$datas=explode("\n",file_get_contents($filetemp));
	
	$countRows=count($datas);
	$number_pages=round($tot/$countRows);
	

	
	
	$html="
	<H4>{queue} $queue_name $PagesNumber {pages}  $countRows {lines}</H4>
	<div align='right' class=caption>{from_cache_file} $filetemp</div>
	$tabublation
	<table style='width:100%'>
	<tr class='caption'>
	<td><strong>{date}</strong></td>
	<td><strong>{operation}</strong></td>
	<td><strong>{mail_from}</strong></td>
	<td><strong>{mail_to}</strong></td>
	<td><strong>{delete}</strong></td>
	</tr>
	";
	while (list ($num, $val) = each ($datas) ){
		$val=str_replace('<sender></sender>','<sender>unknown</sender>',$val);
		if(preg_match('#<file>(.+?)</file><path>(.+?)</path><time>(.+?)</time><named_attr>(.+?)</named_attr><sender>(.+?)</sender><recipient>(.+?)</recipient><subject>(.+?)</subject>#',$val,$regs)){
			$file=$regs[1];
			$path=$regs[2];
			$time=PostFixTimeToPhp($regs[3]);
			$named=$regs[4];
			$sender=$regs[5];
			$recipient=$regs[6];
			$subject=utf8_decode($subject);
			$subject=htmlentities('"'.$regs[7].'"');
			$varClick="OnClick=\"javascript:LoadMailID('$file','$queue_name','$tab')\"";
			
			$tooltips="<strong>$file</strong><br>$subject";
			$html=$html . "
			<tr " . CellRollOver(null,$tooltips) . " class=caption>
			<td nowrap $varClick>$time</td>
			<td $varClick>$named</td>
			<td $varClick>$sender</td>
			<td $varClick>$recipient</td>
			<td align='center' width=1%>" . imgtootltip('x.gif','{delete}',"DeleteMailID('$queue_name','$tab','$file')")."</td>
			</tr>
			";
			$count=$count+1;
			if($count>100){break;}
			
		}
		
	}
	$tpl=new templates();
	$html=$tpl->_ENGINE_parse_body($html);
	$html=$html . "</table>";
	return $html;
}
function  MailID(){
	$mailid=$_GET["MailID"];
	$sock=new sockets();
	$datas=$sock->getfile('view_queue_file:'.$mailid);
	$datas=htmlentities($datas);
	$datas=str_replace("\n","<br>",$datas);
	$datas=str_replace("<br><br>","<br>",$datas);
	$tab=$_GET["page_number"];
	$queue_name=$_GET["queue_name"];
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("
	<div style='padding:10px;margin:5px;font-size:9px'>
	<H4>$mailid {file}</H4>
	<div style='text-align:right;padding-right:10px'><input type='button' value='{delete}' OnClick=\"javascript:DeleteMailID('$queue_name','$tab','$mailid')\"></div>
	<code>$datas</code></div>");
	
}
function PostQueueF(){
	$mailid=$_GET["MailID"];
	$sock=new sockets();
	$datas=$sock->getFrameWork("cmd.php?postqueue-f=yes&hostname={$_GET["hostname"]}");	
	if($_GET["output"]){echo $datas;return;}
	$datas=htmlentities($datas);
	$datas=str_replace("\n","<br>",$datas);
	$datas=str_replace("<br><br>","<br>",$datas);
	
	
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body("
	<div style='padding:10px;margin:5px;font-size:9px'>
	<H4 style='margin-rigth:20px'>{reprocess_queue}</H4>
	<code>$datas</code>
	</div>");	
}
function DeleteMailID(){
	$mailid=$_GET["DeleteMailID"];
	$sock=new sockets();
	$datas=base64_decode($sock->getFrameWork('cmd.php?postsuper-d-master='.$mailid));	
	echo $datas;
	}
	
function reprocessMailID(){
	$mailid=$_GET["PostCatReprocess"];
	$sock=new sockets();
	$datas=base64_decode($sock->getFrameWork('cmd.php?postsuper-r-master='.$mailid));	
	echo $datas;	
}


	
function PostfixDeleteMailsQeue(){
	$PostfixDeleteMailsQeue=$_GET["PostfixDeleteMailsQeue"];
	$sock=new sockets();
	$datas=$sock->getfile('PostfixDeleteMailsQeue:'.$PostfixDeleteMailsQeue);	
	
}



