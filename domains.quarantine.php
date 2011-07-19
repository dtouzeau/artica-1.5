<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');




if(isset($_GET["delete-js"])){delete_js();exit;}
if(isset($_GET["message-id"])){JS_MESSAGE_ID();exit;}
if(isset($_GET["message_id"])){echo quarantine_show();exit;}
if(isset($_GET["message-id-source"])){quarantine_show_source();exit;}
if(isset($_GET["message-id-html"])){quarantine_show_html();exit;}


if(isset($_GET["release-mail-send"])){release_mail_send();exit;}
if(isset($_POST["LiberLigneQueue"])){release_all_mail_send();exit;}
if(isset($_GET["LiberLignePerform"])){quarantine_create_queue();exit;}
if(isset($_GET["LiberLigneText"])){quarantine_create_queue_text();exit;}

if(!GetRights()){
			$tpl=new templates();
			$error="{ERROR_NO_PRIVS}";
			echo $tpl->_ENGINE_parse_body("alert('$error')");
			die();
	}
	
	
if(isset($_GET["js"])){echo quarantine_script();exit;}
if(isset($_GET["popup"])){echo quarantine_index();exit;}
if(isset($_GET["query"])){echo quarantine_query();exit;}


if(isset($_GET["SuperAdmin"])){SuperAdmin();exit;}
if(isset($_GET["SuperAdminQuery"])){SuperAdminQuery();exit;}
if(isset($_GET["quarantine-settings"])){quarantine_ou_settings();exit;}
if(isset($_GET["OuSendQuarantineReports"])){quarantine_ou_settings_save();exit;}

if(isset($_GET["delete-message-id"])){delete_message();exit;}

function GetRights(){
	$users=new usersMenus();
	if($users->AsMessagingOrg){return true;}
	if($users->AsQuarantineAdministrator){return true;}
	return false;
}

function delete_js(){
	$tpl=new templates();
	$page=CurrentPageName();
	$are_you_sure_to_delete=$tpl->javascript_parse_text("{are_you_sure_to_delete}");
	
	$html="
	
	function MessageIDDelete(){
	
		if(confirm('$are_you_sure_to_delete ?')){
				var XHR = new XHRConnection();
				document.getElementById('message-body-show').innerHTML='<center style=\"width:100%\"><img src=\"img/wait_verybig.gif\"></center>';
	        	XHR.appendData('delete-message-id','{$_GET["message-id"]}');
				XHR.sendAndLoad('$page', 'GET',x_delete_message_id);	
		
		}
		
	}
	var x_delete_message_id= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue);}
		YahooWin2Hide();
		}			
	
	
	MessageIDDelete();
	";
	
	echo $html;
	
}


function JS_MESSAGE_ID(){
	$javascript_message=javascript_message();
	$messageid=$_GET["message-id"];
	
	$html="
	$javascript_message
	
	AdminQuarantine('$messageid');
	";
	
	echo $html;
}

function quarantine_script(){
	$page=CurrentPageName();
	$tpl=new templates();
	$success=$tpl->_ENGINE_parse_body('{success}');
	$quarantine_reports=$tpl->_ENGINE_parse_body('{quarantine_reports}');
	$title=$tpl->_ENGINE_parse_body('{quarantine_manager}');
	
	$load="loadMain()";
	if(isset($_GET["Master"])){$load="LoadQuarantineAdmin()";}
	if(isset($_GET["MailSettings"])){$load="QuarantineMailSettings()";}	
	
	if(isset($_GET["inline"])){
		$prefix="<div id='quarantine-inline'></div>\n<script>";
		$suffix="</script>";
		$load="loadMainInLine()";
	}
	
	
	$javascript_message=javascript_message();
	$quarantine_superadmin="
	function LoadQuarantineAdmin(){
		YahooWin(900,'$page?SuperAdmin=yes','$title');
		setTimeout(\"LoadQuarantineAdminQuery()\",1000);
	
	}
	
	function LoadQuarantineAdminQuery(){
		LoadAjax('quarantineADMresults','$page?SuperAdminQuery=yes');
	}
	
	

	

	
	function LoadQuarantineAdminQueryPerf(){
	var MessagesSelected='';
	var zDate=document.getElementById('zDate').value;
	var mailfrom=document.getElementById('mailfrom').value;
	var recipient=document.getElementById('recipient').value;
	var subject=document.getElementById('subject').value;
	if(document.getElementById('liberequeue')){
		MessagesSelected=escape(document.getElementById('liberequeue').value);
	}
	
	LoadAjax('quarantineADMresults','$page?SuperAdminQuery=yes&zDate='+zDate+'&mailfrom='+mailfrom+'&recipient='+recipient+'&subject='+subject+'&MessagesSelected='+MessagesSelected);
	}
	
	function DeleteQuarantineAdminQueryPerf(){
	var zDate=document.getElementById('zDate').value;
	var mailfrom=document.getElementById('mailfrom').value;
	var recipient=document.getElementById('recipient').value;
	var subject=document.getElementById('subject').value;
	LoadAjax('quarantineADMresults','$page?SuperAdminQuery=yes&zDate='+zDate+'&mailfrom='+mailfrom+'&recipient='+recipient+'&subject='+subject+'&delete=yes');
	}
	
	function LoadQuarantineAdminQueryPerfPress(e){
		if(checkEnter(e)){LoadQuarantineAdminQueryPerf();}
	}
	
";
	
	$html="
	$prefix
	function loadMainInLine(){
			LoadAjax('quarantine-inline','$page?popup={$_GET["js"]}');
		
		}
		
function loadMain(){
			YahooWin(850,'$page?popup={$_GET["js"]}','$title');
		}
		
	var x_QuarantineOuQuery= function (obj) {
		var tempvalue=obj.responseText;
		document.getElementById('quarantine_ou_results').innerHTML=tempvalue;
		
	}		

	function QuarantineMailSettings(){
		var ou='{$_GET["js"]}';
		YahooWin3(900,'$page?quarantine-settings=yes&ou={$_GET["js"]}','$quarantine_reports');
	}
		
	function QuarantineOuQuery(next){
				if(!next){next=0;}
				var ou='{$_GET["js"]}';
				var query=document.getElementById('query').value;
				var recipient=document.getElementById('recipient').value;
				var XHR = new XHRConnection();
				document.getElementById('quarantine_ou_results').innerHTML='<center style=\"width:100%\"><img src=\"img/wait_verybig.gif\"></center>';
	        	XHR.appendData('ou','{$_GET["js"]}');
	        	XHR.appendData('query',query);
				XHR.appendData('recipient',recipient);
				XHR.appendData('next',next);
				XHR.sendAndLoad('$page', 'GET',x_QuarantineOuQuery);			
	}

	function QfindPress(e){
		if (checkEnter(e)){QuarantineOuQuery(0);}
	}
	
var x_QuarantineMailSettingsSave= function (obj) {
		var tempvalue=obj.responseText;
		if(tempvalue.length>0){alert(tempvalue);}
		QuarantineMailSettings();
	}		
	
	
	function QuarantineMailSettingsSave(){
		 var OuSendQuarantineReports=document.getElementById('OuSendQuarantineReports').value;
		 var Min=document.getElementById('Min').value; 
		 var hour=document.getElementById('hour').value;
		 var days=document.getElementById('days').value;
		 var mailfrom=document.getElementById('mailfrom').value;
		 var subject=document.getElementById('subject').value;
		 var title1=document.getElementById('title1').value;
		 var title2=document.getElementById('title2').value;
		 var explain=document.getElementById('explain').value;
		 var externalLink=document.getElementById('externalLink').value;
		 
		 
		 var XHR = new XHRConnection();
		 XHR.appendData('ou','{$_GET["js"]}');
	     XHR.appendData('OuSendQuarantineReports',OuSendQuarantineReports);
		 XHR.appendData('Min',Min);
		 XHR.appendData('hour',hour);
		 XHR.appendData('days',days);
		 XHR.appendData('mailfrom',mailfrom);
		 XHR.appendData('subject',subject);
		 XHR.appendData('title1',title1);
		 XHR.appendData('title2',title2);
		 XHR.appendData('explain',explain);
		 XHR.appendData('externalLink',externalLink);
		 
		 AnimateDiv('quarantine_ou_settings');
		 XHR.sendAndLoad('$page', 'GET',x_QuarantineMailSettingsSave);
	
	}

	$javascript_message
	$quarantine_superadmin	
	$load;
$suffix
";
	
	return $html;
	
}

function javascript_message(){
	$page=CurrentPageName();
	$tpl=new templates();
	$success=$tpl->_ENGINE_parse_body('{success}');	
	
	$html="
	function AdminQuarantine(message_id){
		YahooWin2(780,'$page?message_id='+message_id,message_id);
		}
		
	function QReleaseMail(messageid){
		YahooWin3(650,'$page?release-mail-send='+messageid,messageid);
	}		
	
	";
	return $html;
}

function quarantine_index(){
$tooltip=ParseTooltip("{quarantine_reports}");
$users=new usersMenus();

$html="
<input type='hidden' name='ou' id='ou' value='{$_GET["popup"]}'>
<input type='hidden' id='recipient' value=''>
<center><table style='width:80%' class=form>
<tr>
<td class=legend>{options}&nbsp;|&nbsp;|</td>
<td class=legend>{search}:</td>
<td><input type='text' id='query' name='query' style='width:480px;font-size:13px;text-align:left;background:transparent;font-weight:bolder;border:1px solid #CCCCCC' onkeypress=\"javascript:QfindPress(event)\"></td>
</tr>
</table>
</center>
<div id='quarantine_ou_results' style='width:100%'></div>
		
		";
		
$tpl=new templates();
return $tpl->_ENGINE_parse_body($html);
}

function quarantine_query(){
	$result=BuildQuery();
	
	
	if(!$result[0]){
		return  "<p style='border:1px solid red;width:90%;font-size:14px;padding:4px;background-color:#FFFFFF'>{$result[1]}</p>";
	}
	$sql=$result[1];
	$s=new mysql();
	$count=1;
	$results=$s->QUERY_SQL($sql,"artica_backup");
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($bg=="#cce0df"){$bg="#FFFFFF";}else{$bg="#cce0df";}
		$tmpstr=formatQueryResultsTable($ligne,$count,true,"AdminQuarantine('{$ligne["MessageID"]}')",$bg);
		if($tmpstr==null){continue;}
		$html=$html.$tmpstr;
		$count=$count+1;
	}
	
$previous=$_GET["next"]-1;
$next=$_GET["next"]+1;
	
if($previous<0){$previous=0;}
	
$previous=CellRollOver("QuarantineOuQuery($previous);");
$next=CellRollOver("QuarantineOuQuery($next)");
if($count==0){$next=null;}
if($_GET["next"]-1<0){$previous=null;}
	
$tpl=new templates();	
return $tpl->_ENGINE_parse_body("


	<div style='background-color:#FFFFFF;padding:3px;border:1px solid #CCCCCC;height:300px;width:100%;overflow:auto'>
	<table style='width:100%'>
	<tr>
	<td width=50% style='font-size:12px;font-weight:bold' $previous>&laquo&nbsp;{previous}</td>
	<td width=50% style='font-size:12px;font-weight:bold' align='right' $next>{next}&nbsp;&raquo;</td>
	</tr>
	</table>

	$html
	</table>
	</div>");	
}

function quarantine_ou_settings_save(){
	$ou=$_GET["ou"];
	$ouU=strtoupper($ou);
    $ini=new Bs_IniHandler();
	$ini->_params["NEXT"]["hour"]=$_GET["hour"];
	$ini->_params["NEXT"]["Min"]=$_GET["Min"];
	$ini->_params["NEXT"]["Enabled"]=$_GET["OuSendQuarantineReports"];
	$ini->_params["NEXT"]["org"]=$ou;
	$ini->_params["NEXT"]["days"]=$_GET["days"];;
	$ini->_params["NEXT"]["cron"]=$_GET["Min"].' '.$_GET["hour"].' * * *';
	$ini->_params["NEXT"]["subject"]=$_GET["subject"];
	$ini->_params["NEXT"]["title1"]=$_GET["title1"];
	$ini->_params["NEXT"]["title2"]=$_GET["title2"];
	$ini->_params["NEXT"]["explain"]=$_GET["explain"];
	$ini->_params["NEXT"]["mailfrom"]=$_GET["mailfrom"];
	$ini->_params["NEXT"]["externalLink"]=$_GET["externalLink"];
	
	
	
	$sock=new sockets();
	$sock->SaveConfigFile($ini->toString(),"OuSendQuarantineReports$ouU");
	$sock->getfile("RestartDaemon");
	}

function quarantine_ou_settings(){
	$ou=$_GET["ou"];
	$ouU=strtoupper($ou);
	$ldap=new clladp();
	$hash=$ldap->OUDatas($ou);
	$sock=new sockets();
	$users=new usersMenus();
	
	
	$ini=new Bs_IniHandler();
	$ini->loadString($sock->GET_INFO("OuSendQuarantineReports$ouU"));
	
	if($ini->_params["NEXT"]["hour"]==null){$ini->_params["NEXT"]["hour"]="23";}
	if($ini->_params["NEXT"]["Min"]==null){$ini->_params["NEXT"]["Min"]="59";}
	if($ini->_params["NEXT"]["Enabled"]==null){$ini->_params["NEXT"]["Enabled"]="0";}
	if($ini->_params["NEXT"]["days"]==null){$ini->_params["NEXT"]["days"]="2";}
	if($ini->_params["NEXT"]["mailfrom"]==null){$ini->_params["NEXT"]["mailfrom"]="reports@$users->hostname";}
	if($ini->_params["NEXT"]["subject"]==null){$ini->_params["NEXT"]["subject"]="Daily Quarantine report";}
	if($ini->_params["NEXT"]["title1"]==null){$ini->_params["NEXT"]["title1"]="Quarantine domain senders";}
	if($ini->_params["NEXT"]["title2"]==null){$ini->_params["NEXT"]["title2"]="Quarantine list";}
	if($ini->_params["NEXT"]["explain"]==null){$ini->_params["NEXT"]["explain"]="You will find here all mails stored in your quarantine area";}
	if($ini->_params["NEXT"]["externalLink"]==null){$ini->_params["NEXT"]["externalLink"]="https://{$_SERVER["SERVER_NAME"]}/user.quarantine.query.php";}
	
	
	$OuSendQuarantineReports=$ini->_params["NEXT"]["Enabled"];
	
	for($i=0;$i<60;$i++){
		$text=$i;
		if($i<10){$text="0{$i}";}
		$M[$i]=$text;
	}
	
for($i=0;$i<24;$i++){
		$text=$i;
		if($i<10){$text="0{$i}";}
		$H[$i]=$text;
	}	
	
for($i=0;$i<30;$i++){
		$text=$i;
		if($i<10){$text="{$i}";}
		$D[$i]=$text;
	}	
	
	$minutes=Field_array_Hash($M,"Min",$ini->_params["NEXT"]["Min"]);
	$hours=Field_array_Hash($H,"hour",$ini->_params["NEXT"]["hour"]);
	$report=Field_array_Hash($D,"days",$ini->_params["NEXT"]["days"]);
	
	$time="
	
	<table style='width:9%;margin-top:0px;' class=table_form>
	<tr>
	<td class=caption nowrap>{timetosendreport}:</td>
	<td>$hours</td>
	<td><strong>:</strong>
	<td>$minutes</td>
	</tr>
	</table>
	<br>
	<table style='width:99%;margin-top:0px;' class=table_form>
	<tr>
		<td class=legend nowrap>{build_report_for}:</td>
		<td>$report&nbsp;{days}</td>
	</tr>
	<tr>
		<td class=legend>{externalLink}:</td>
		<td>" . Field_text('externalLink',$ini->_params["NEXT"]["externalLink"],"width:250px")."</td>
	</tr>	
	<tr>
		<td class=legend>{sender}:</td>
		<td>" . Field_text('mailfrom',$ini->_params["NEXT"]["mailfrom"],"width:200px")."</td>
	</tr>
	<tr>
		<td class=legend>{subject}:</td>
		<td>" . Field_text('subject',$ini->_params["NEXT"]["subject"],"width:250px")."</td>
	</tr>
	<tr>
		<td class=legend>{explain}:</td>
		<td>" . Field_text('explain',$ini->_params["NEXT"]["explain"],"width:250px")."</td>
	</tr>
	<tr>
		<td class=legend>title 1:</td>
		<td>" . Field_text('title1',$ini->_params["NEXT"]["title1"],"width:250px")."</td>
	</tr>
	<tr>
		<td class=legend>title 2:</td>
		<td>" . Field_text('title2',$ini->_params["NEXT"]["title2"],"width:250px")."</td>
	</tr>							
	</table>
	<div style='margin:0px;width:100%;text-align:right;border-top:1px solid #CCCCCC'><input type='button' OnClick=\"javascript:QuarantineMailSettingsSave();\" value='{apply}&nbsp;&raquo;'></div>
	";
	
	
	$field=Paragraphe_switch_img('{enable_html_reports}','{ou_quarantine_reports_explain}',"OuSendQuarantineReports",$OuSendQuarantineReports);
	
	
	
	$tpl=new templates();
	
	$html="
	<H1>{quarantine_reports}</H1>
	<div id='quarantine_ou_settings'>
	<p class=caption>{quarantine_reports_text}</p>
	<table style='width:100%'>
	<tr>
	<td valign='top' align='center'>
		$field
		<br>$quarantine_robot
	</td>
	<td valign='top'>
	$time
	
	</td>
	</tr>
	</table>
	</div>
	";
	
	echo $tpl->_ENGINE_parse_body($html);
}



 


function BuildQuery(){
	
	$users=new usersMenus();
	$ldap=new clladp();	
	$tpl=new templates();
	$recipient=$_GET["recipient"];
	if($_GET["query"]=='*'){$_GET["query"]=null;}
	
	writelogs("recipient={$_GET["recipient"]}",__FUNCTION__,__FILE__);
	if($recipient==null){$recipient='*';}
	
	
if(!$users->AsArticaAdministrator){	
	
	writelogs("AsArticaAdministrator=FALSE",__FUNCTION__,__FILE__);
	if(strpos(" $recipient",'*')==0){
		$uid=$ldap->uid_from_email($_GET["recipient"]);
		if($uid==null){
			return array(false,$tpl->_ENGINE_parse_body('{error_no_user_exists}'));
		}else{
			$filter1="mailto='$recipient'";
		}
	}
	
	else{
		if(!preg_match('#(.+?)@(.+)#',$recipient,$re)){return array(false,$tpl->_ENGINE_parse_body('{error_bad_recipient_pattern}'));}
		
		$domains=$ldap->hash_get_domains_ou($_GET["ou"]);
		if($domains[trim($re[2])]==null){return array(false,$tpl->_ENGINE_parse_body('{error_match_recipient_domain}'));}
		
		$re[1]=str_replace('*','%',$re[1]);
		$filter1="mailto LIKE '{$re[1]}@{$re[2]}'";
		
	}}
	
	
	if($users->AsPostfixAdministrator){
		writelogs("AsArticaAdministrator=TRUE",__FUNCTION__,__FILE__);
		writelogs("recipient={$_GET["recipient"]}",__FUNCTION__,__FILE__);
		if(strpos(" $recipient",'*')>0){
			$_GET["recipient"]=str_replace('*','%',$_GET["recipient"]);
			$filter1="mailto LIKE '{$_GET["recipient"]}'";
			writelogs("filter=$filter1",__FUNCTION__,__FILE__);
		}else{
			$filter1="mailto='{$_GET["recipient"]}'";
		}
		
	}
	
	
	if($_GET["limit"]==null){$limit=0;}
	
	if($_GET["query"]<>null){
		
		$field2=",MessageBody,MATCH (MessageBody) AGAINST ('{$_GET["query"]}') AS pertinence";
		$ORDER2=",pertinence DESC ";
	}
	
	if($filter1=="mailto LIKE ''"){
		$domains=$ldap->hash_get_domains_ou($_GET["ou"]);
		while (list ($num, $ligne) = each ($domains) ){
			$dd[]="(mailto LIKE '%$num')";
		}
		
		$filter1="(".implode("OR ",$dd).")";
	}
	
	$limit=$_GET["next"]*100;
	
	
	
	$sql="SELECT 
		 MessageID,
		 MessageBody,
		 zDate,
		 mailfrom,
		 subject,
		 mailto
		 $field2
			FROM `quarantine` WHERE 1
			AND $filter1
			ORDER BY zDate DESC $ORDER2 LIMIT $limit,100";
	
	
writelogs($sql,__FUNCTION__,__FILE__);
	return array(true,$sql);
	
}

function delete_message(){
	$message_id=$_GET["delete-message-id"];
	$sql="DELETE FROM quarantine WHERE MessageID='$message_id'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;}
	
}

function quarantine_show(){
	$message_id=$_GET["message_id"];
	$page=CurrentPageName();
	$tpl=new templates();
	$q=new mysql();
	$sq="SELECT message_path,message_size FROM orgmails WHERE MessageID='$message_id'";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sq,"artica_backup"));
	$path=$ligne["message_path"];
	
	$sql="SELECT MessageBody,message_size,mailfrom,mailto,file_path FROM quarantine WHERE MessageID='$message_id'";
	
	
	
	writelogs($sql,__FUNCTION__,__FILE__);
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$ligne["MessageBody"]=CleanMail($ligne["MessageBody"]);
	$ligne["message_size"]=FormatBytes($ligne["message_size"]/1024);
	
	$html="
	
	<table class=form>
	<tr>
		<td style='font-size:14px'>{message_size}:{$ligne["message_size"]}</td>
		<td style='font-size:14px'>&nbsp;|&nbsp;</td>
		<td style='font-size:14px'>{from}:{$ligne["mailfrom"]}</td>
		<td style='font-size:14px'>&nbsp;|&nbsp;</td>
		<td style='font-size:14px'>{to}:{$ligne["mailto"]}</td>
		<td style='font-size:14px'>&nbsp;|&nbsp;</td>
		<td style='font-size:14px'><a href='#' OnClick=\"javascript:ViewMessageSource()\">{view_source}</a></td>
		<td style='font-size:14px'>&nbsp;|&nbsp;</td>
		<td style='font-size:14px'><a href='#' OnClick=\"javascript:ViewMessageHTML()\">{view_html}</a></td>
	</tr>
	</table>
	<div style='width:100%;text-align:right'>". button("{release}","QReleaseMail('$message_id')")."</div>
	<br>
	<div style='width:99%;padding:5px;border:1px solid #CCCCCC;background-color:#FFFFFF' id='message-body-show' class=form>

	<div style='float:left;margin:5px;'>". imgtootltip("delete-48.png","{delete} $message_id","Loadjs('domains.quarantine.php?delete-js=yes&message-id=$message_id')")."</div>
		<div id='QuarMessageBody' style='height:650px;overflow:auto'>
			{$ligne["MessageBody"]}
		</div>
	
	</div>
	
	<script>
		function ViewMessageSource(msgid){
			LoadAjax('QuarMessageBody','$page?message-id-source=$message_id');
		}
		
		function ViewMessageHTML(msgid){
			LoadAjax('QuarMessageBody','$page?message-id-html=$message_id');
		}		
	</script>
	
	
	";
	return $tpl->_ENGINE_parse_body($html);
	
	
}

function quarantine_show_source(){
	$sq="SELECT BinMessg FROM quarantine WHERE MessageID='{$_GET["message-id-source"]}'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sq,"artica_backup"));	
	$message=htmlspecialchars($ligne["BinMessg"]);
	$message=nl2br($message);
	
	echo $message;
	
}

function quarantine_show_html(){
	$sq="SELECT MessageBody FROM quarantine WHERE MessageID='{$_GET["message-id-html"]}'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sq,"artica_backup"));	
	echo $ligne["MessageBody"];
	
}

function release_path(){
	$sock=new sockets();
	$sock->getfile("ReleaseMailPath:{$_GET["release_path"]}");
	
}

function SuperAdmin(){
	
	$tpl=new templates();
	
$html="
		
		<table style='width:99%' class=table_form>
		<tr>
			<th>&nbsp;</th>
			<th>{date}</th>
			<th>{from}</th>
			<th>{recipient}</th>
			<th>{subject}</th>
			<th>&nbsp;</th>
			<th>&nbsp;</th>
		</tr>		
		<tr>
			<td><img src='img/fw_bold.gif'></td>
			<td>" . Field_text('zDate',null,'width:100px',null,null,null,false,"LoadQuarantineAdminQueryPerfPress(event)")."</td>
			<td>" . Field_text('mailfrom',null,'width:150px',null,null,null,false,"LoadQuarantineAdminQueryPerfPress(event)")."</td>
			<td>" . Field_text('recipient',null,'width:150px',null,null,null,false,"LoadQuarantineAdminQueryPerfPress(event)")."</td>
			<td>" . Field_text('subject',null,'width:150px',null,null,null,false,"LoadQuarantineAdminQueryPerfPress(event)")."</td>
			<td><input type='button' OnClick=\"javascript:LoadQuarantineAdminQueryPerf()\" value='{query}&nbsp;&raquo;'></td>
			<td><input type='button' OnClick=\"javascript:DeleteQuarantineAdminQueryPerf()\" value='{delete}&nbsp;&raquo;'></td>
		</tr>
		</table>
		<input type='hidden' id='liberequeue' value=''>	
		<div id='quarantineADMresults' style='width:100%;height:300px;overflow:auto'></div>
		<div id='queue-quar-text'></div>
		
		
		
		";
echo $tpl->_ENGINE_parse_body($html);	
	
}

function SuperAdminQuery(){
	$del=false;
	$okdelete=false;
	$page=CurrentPageName();
	
	if($_GET["zDate"]<>null){
		if(preg_match('#^([0-9]+)#',$_GET["zDate"],$re)){
			$okdelete=true;
			if(strlen($re[1])==4){$q_zdate="AND DATE_FORMAT(zDate,'%Y')={$re[1]}";}
		}
		
		if(preg_match('#^([0-9]+)-([0-9]+)#',$_GET["zDate"],$re)){
			$okdelete=true;
			$q_zdate="AND DATE_FORMAT(zDate,'%Y-%m')='{$re[1]}-{$re[2]}'";
		
		}
		
		if(preg_match('#^([0-9]+)-([0-9]+)-([0-9]+)#',$_GET["zDate"],$re)){
			$okdelete=true;
			$q_zdate="AND DATE_FORMAT(zDate,'%Y-%m-%d')='{$re[1]}-{$re[2]}-{$re[3]}'";
		
		}

			if(preg_match('#^([0-9]+)-([0-9]+)-([0-9]+)\s+([0-9]+)#',$_GET["zDate"],$re)){
				$okdelete=true;
			$q_zdate="AND DATE_FORMAT(zDate,'%Y-%m-%d %H')='{$re[1]}-{$re[2]}-{$re[3]} {$re[4]}'";
		
		}	

			if(preg_match('#^([0-9]+)-([0-9]+)-([0-9]+)\s+([0-9]+):([0-9]+)#',$_GET["zDate"],$re)){
				$okdelete=true;
			$q_zdate="AND DATE_FORMAT(zDate,'%Y-%m-%d %H:%i')='{$re[1]}-{$re[2]}-{$re[3]} {$re[4]}:{$re[5]}'";
		
		}			
		
	}
	
	$limit="LIMIT 0,100";
	
	if($_GET["mailfrom"]<>null){
		$okdelete=true;
		$_GET["mailfrom"]=str_replace('*','%',$_GET["mailfrom"]);
		$q_mailfrom="AND mailfrom LIKE '{$_GET["mailfrom"]}%'";
	}
	
	if($_GET["recipient"]<>null){
		$okdelete=true;
		$_GET["recipient"]=str_replace('*','%',$_GET["recipient"]);
		$q_mailfrom="AND mailto LIKE '{$_GET["recipient"]}%'";
	}

	if($_GET["subject"]<>null){
		$okdelete=true;
		$_GET["subject"]=str_replace('*','%',$_GET["subject"]);
		$q_mailfrom="AND subject LIKE '{$_GET["subject"]}%'";
	}	
		
	if($_GET["delete"]=="yes"){
		if($okdelete){$del=true;}
		$limit=null;
	}
	
	
	
	$sql="SELECT MessageID,zDate,mailfrom,subject,mailto FROM quarantine WHERE 1 $q_zdate $q_mailfrom ORDER BY zDate DESC $limit";
	$sql=str_replace('%%','%',$sql);
	$q=new mysql();
	
	writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
	
	$html="
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:99%'>
	<thead class='thead'>
		<tr>
		<th>&nbsp;</th>
		<th>{date}</th>
		<th>{from}</th>
		<th>{recipient}</th>
		<th>{subject}</th>
		</tr>
	</thead>
	<tbody class='tbody'>	";
$results=$q->QUERY_SQL($sql,"artica_backup");

$array_selected=unserialize(base64_decode($_GET["MessagesSelected"]));

$classtr=null;
while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($del){
			DeleteOrgMail($ligne["MessageID"]);
			$ligne["subject"]="{deleted}";
		}
		
		$year=date('Y');
		$today=date('m-d');
	
	    if(strlen($ligne["subject"])>50){
	    	$ligne["subject"]=texttooltip(substr($ligne["subject"],0,47).'...',$ligne["subject"]);
	    }
	    
	    $js="AdminQuarantine('{$ligne["MessageID"]}')";
	    if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}

	    if(strlen($ligne["mailfrom"])>23){$ligne["mailfrom"]=texttooltip(substr($ligne["mailfrom"],0,20)."...",$ligne["mailfrom"],$js,null,0,"font-size:13px");}
		if(strlen($ligne["mailto"])>23){$ligne["mailto"]=texttooltip(substr($ligne["mailto"],0,20)."...",$ligne["mailto"],$js,null,0,"font-size:13px");}
	    
		$ligne["zDate"]=str_replace("$year-",'',$ligne["zDate"]);
		$ligne["zDate"]=str_replace("$today",'{today}',$ligne["zDate"]);
		$checkbox_md=md5($ligne["MessageID"]);
		$checkbox_md_value=0;
		if($array_selected[$ligne["MessageID"]]){$checkbox_md_value=1;}
		$html=$html . 
		
		"<tr class=$classtr>
			<td width=1%>". Field_checkbox($checkbox_md,1,$checkbox_md_value,"LiberLigne('{$ligne["MessageID"]}','$checkbox_md')")."</td>
			<td nowrap><a href='#' OnClick=\"javascript:$js\" style='font-size:13px;'>{$ligne["zDate"]}</a></td>
			<td nowrap><a href='#' OnClick=\"javascript:$js\" style='font-size:13px;text-decoration:underline'>{$ligne["mailfrom"]}</a></td>
			<td nowrap><a href='#' OnClick=\"javascript:$js\" style='font-size:13px;text-decoration:underline'>{$ligne["mailto"]}</a></td>
			<td><a href='#' OnClick=\"javascript:$js\" style='font-size:13px;text-decoration:underline'>{$ligne["subject"]}</a></td>
		</tr>
			
		";
	   	
		
		
	}
		$tpl=new templates();
		$libere_text="{confirm} {release}";
		
	$html=$html . "</tbody></table>
	
	
	<script>
	
	function x_LiberLigneText(obj){
		var tempvalue=obj.responseText;
		document.getElementById('queue-quar-text').innerHTML=tempvalue;
	}		
	
	function LiberLigneText(){
		var XHR = new XHRConnection();
		XHR.appendData('LiberLigneText',document.getElementById('liberequeue').value);
		XHR.sendAndLoad('$page', 'GET',x_LiberLigneText);
	}
	
	function x_LiberLignePerform(obj){
		var tempvalue=obj.responseText;
		document.getElementById('liberequeue').value=tempvalue;
		LiberLigneText();
	}			
			
	function LiberLignePerform(msgid,add){
		var XHR = new XHRConnection();
		XHR.appendData('LiberLignePerform',msgid);
		XHR.appendData('action',add);
		XHR.appendData('liste',document.getElementById('liberequeue').value);
		XHR.sendAndLoad('$page', 'GET',x_LiberLignePerform);	
	}
	
	function x_LiberQueue(obj){
		var tempvalue=obj.responseText;
		alert(tempvalue);
	}		

	function LiberQueue(num){
		if(confirm('$libere_text '+num+' mails')){
			var XHR = new XHRConnection();
			XHR.appendData('LiberLigneQueue',document.getElementById('liberequeue').value);
			XHR.sendAndLoad('$page', 'POST',x_LiberQueue);
		}
	}
	
	
	function LiberLigne(messageid,id){
		if(document.getElementById(id).checked){LiberLignePerform(messageid,1);}else{LiberLignePerform(messageid,0);}
	}
	</script>";

	echo $tpl->_ENGINE_parse_body($html);	
}

function quarantine_create_queue(){
	$liste=unserialize(base64_decode($_GET["liste"]));
	if($_GET["action"]==1){$liste[$_GET["LiberLignePerform"]]=true;}else{unset($liste[$_GET["LiberLignePerform"]]);}
	echo base64_encode(serialize($liste));
	}
function quarantine_create_queue_text(){
	$liste=unserialize(base64_decode($_GET["LiberLigneText"]));
	$msgs=count($liste);
	
	if($msgs>0){
		$renvoi="&nbsp;|&nbsp;<a href='#' OnClick=\"javascript:LiberQueue($msgs);\" style='text-decoration:underline;font-size:13px;font-weight:bold;color:red'>{release}</a>";
	}
	
	$text="<hr><strong style='font-size:13px'>$msgs {messages_pluri} {selected_accents}$renvoi</strong>";
	$tpl=new templates();
	echo $tpl->_ENGINE_parse_body($text);
	
}

function DeleteOrgMail($messageid){
	
	$sql="SELECT message_path FROM orgmails WHERE MessageID='$messageid';";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$path=$ligne["message_path"];
	$sock=new sockets();
	$sock->getfile("DeleteQuarantineMessage:$path");
	$sql="DELETE FROM quarantine WHERE MessageID='$messageid'";
	$q->QUERY_SQL($sql,"artica_backup");
	$sql="DELETE FROM orgmails WHERE MessageID='$messageid'";
	$q->QUERY_SQL($sql,"artica_backup");
	$sql="DELETE FROM storage_recipients WHERE MessageID='$messageid'";
	$q->QUERY_SQL($sql,"artica_backup");
	
	
	
}

function robotslist(){

	$ou=$_GET["ou"];
	$ldap=new clladp();
	$dn="cn=whitelists,cn=$ou,cn=PostfixRobots,cn=artica,$ldap->suffix";
	$pattern="(&(objectclass=transportTable)(cn=*))";
	$attr=array();
	$sr =@ldap_search($ldap->ldap_connection,$dn,$pattern,$attr);
	$hash=ldap_get_entries($ldap->ldap_connection,$sr);
	if(!is_array($hash)){return null;}
	
	$html="<table style='width:100%'>";
	
	
	for($i=0;$i<$hash["count"];$i++){
		$transport=$hash[$i]["transport"][0];
		if(preg_match("#(.+?):(.+)#",$transport,$re)){
			$email=$re[2];
			$transport=$re[1];
			$img="info-18.png";
			if($transport<>"artica-reportquar"){continue;}
			
			
					
		}
		
		$html=$html . "
			<tr " . CellRollOver().">
				<td width=1%><img src='img/$img'></td>
				<td><code style='font-size:13px'>$email</td>
				<td width=1%>" . imgtootltip('ed_delete.gif',"{delete}","DeleteRobot('$email');")."</td>
			</tr>
				";
		
		
		
	}
	$html=$html . "</table>";
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html);
	
}

function release_all_mail_send(){
	$array=unserialize(base64_decode($_POST["LiberLigneQueue"]));
	if(!is_array($array)){echo "Not an array";return;}
	$sock=new sockets();
	$q=new mysql();
	while (list ($msgid, $none) = each ($array) ){
		if($msgid==null){continue;}
		$array_msg=array();unset($array_msg);
		$sql="SELECT mailfrom,mailto,BinMessg,MessageBody FROM quarantine WHERE MessageID='$msgid'";
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		$mailto=$ligne["mailto"];
		if(trim($mailto)==null){echo "$msgid:mailto=null,aborting\n";continue;}
		$filename=md5($ligne["MessageBody"]);
		$lenght=strlen($ligne["BinMessg"]);
		@file_put_contents("/tmp/$filename",$ligne["BinMessg"]);
		writelogs("Sending message /tmp/$filename from $mailto",__FUNCTION__,__FILE__,__LINE__);
		$array_msg["file"]="/tmp/$filename";
		$array_msg["to"]="$mailto";
		$array_msg["from"]=$ligne["mailfrom"];
		$datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?release-quarantine=". base64_encode(serialize($array_msg)))));
		echo "----------------$msgid---------------------\n";
		while (list ($num, $file) = each ($datas)){
			echo "$file\n";
		}
	}
	
	
}

function release_mail_send(){
	$sql="SELECT mailfrom,mailto,BinMessg,MessageBody FROM quarantine WHERE MessageID='{$_GET['release-mail-send']}'";
	$tpl=new templates();
	$q=new mysql();
	
	writelogs($sql,__FUNCTION__,__FILE__,__LINE__);
	
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	
	$mailto=$ligne["mailto"];
	
	if(!$q->ok){
		echo $tpl->_ENGINE_parse_body("
	<div style='background-color:#005447;border:1px solid #D0C9AD;padding:50px'>
		<center>
			<p style='color:white;font-size:18px;border:1px solid white;padding:10px;margin:10px'>{failed}<br>$mailto<hr>$q->mysql_error<hr>$lenght bytes</p>
		</center>
	</div>");
	exit;
	}
	$filename=md5($ligne["MessageBody"]);
	
	$lenght=strlen($ligne["BinMessg"]);
	writelogs("Sending message /tmp/$filename from $mailto ($lenght bytes)",__FUNCTION__,__FILE__,__LINE__);
	@file_put_contents("/tmp/$filename",$ligne["BinMessg"]);
	writelogs("Sending message /tmp/$filename from $mailto",__FUNCTION__,__FILE__,__LINE__);
	
	$array["file"]="/tmp/$filename";
	$array["to"]="$mailto";
	$array["from"]=$ligne["mailfrom"];
	
	
	//127.0.0.1:33559
	$sock=new sockets();
	$datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?release-quarantine=". base64_encode(serialize($array)))));
	while (list ($num, $file) = each ($datas)){
		$logs=$logs."<div style='color:white;'><code style='color:white;font-size:13px'><strong style='color:white'>$file</strong></code></div>\n";
	}
	
	
	echo $tpl->_ENGINE_parse_body("
	<div style='background-color:#005447;border:1px solid #D0C9AD;padding:50px'>
		<center>
			<p style='color:white;font-size:18px;border:1px solid white;padding:10px;margin:10px'>{success}
				<br> $mailto<hr>
				<span style='color:white;font-size:16px'>$lenght bytes</span>
			</p>
			$logs
		</center>
	</div>");
	
	
}


//
	



?>