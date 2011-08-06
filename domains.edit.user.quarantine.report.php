<?php
session_start();
include_once(dirname(__FILE__)."/ressources/class.templates.inc");
include_once(dirname(__FILE__)."/ressources/class.mysql.inc");
include_once(dirname(__FILE__)."/ressources/class.functions.inc");

if($_GET["uid"]==null){$_GET["uid"]=$_SESSION["uid"];}

if(!Verifrights()){$tpl=new templates();echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";die();exit();}




if(isset($_GET["popup"])){popup();exit;}
if(isset($_GET["hour_execution"])){save();exit;}
if(isset($_GET["sendnow"])){send();exit;}

js();

function js(){
	
	$tpl=new templates();
	$page=CurrentPageName();
	$title=$tpl->_ENGINE_parse_body("{quarantine_email_report}");
	
	$html="
	
	function quarantine_report_start(){
		YahooWin2('400','$page?popup=yes&uid={$_GET["uid"]}','$title');
	
	}
	
	var x_quarantine_report_save= function (obj) {
			var tempvalue=obj.responseText;
			if(tempvalue.length>3){alert(tempvalue)};
			quarantine_report_start();
		}			
		
function quarantine_report_save(){

	var XHR = new XHRConnection();
	XHR.appendData('uid','{$_GET["uid"]}');
	XHR.appendData('hour_execution',document.getElementById('hour_execution').value);
	XHR.appendData('min_execution',document.getElementById('min_execution').value);
	XHR.appendData('days',document.getElementById('days').value);
	XHR.appendData('enabled',document.getElementById('enable_quarantine_report').value);
	XHR.appendData('subject',document.getElementById('subject_report').value);
	XHR.appendData('URI',document.getElementById('URI').value);
	document.getElementById('quarantine_report_div').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
	XHR.sendAndLoad('$page', 'GET',x_quarantine_report_save);	
	}
	
function quarantine_report_send(){
	var XHR = new XHRConnection();
	XHR.appendData('uid','{$_GET["uid"]}');
	XHR.appendData('sendnow','yes');
	document.getElementById('quarantine_report_div').innerHTML='<center style=\"margin:20px;padding:20px\"><img src=\"img/wait_verybig.gif\"></center>';
	XHR.sendAndLoad('$page', 'GET',x_quarantine_report_save);	
}
	
	quarantine_report_start();
	";
	
	
	echo $html;
}


function popup(){
	
	
	$q=new mysql();
	$sql="SELECT * FROM quarantine_report_users WHERE userid='{$_GET["uid"]}' and `type`=1";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	
	
	
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
	
	$params=unserialize(base64_decode($ligne["parameters"]));
	$tpl=new templates();
	if($params["days"]==null){$params["days"]="2";}
	if($params["subject"]==null){$params["subject"]=$tpl->javascript_parse_text("{pdf_report_quarantine_default_subject}");}
	
	
	$minutes=Field_array_Hash($M,"hour_execution",$params["hour_execution"],null,null,0,"font-size:13px;padding:3px");
	$hours=Field_array_Hash($H,"min_execution",$params["min_execution"],null,null,0,"font-size:13px;padding:3px");
	$days=Field_array_Hash($D,"days",$params["days"],null,null,0,"font-size:13px;padding:3px");
	$field=Paragraphe_switch_img('{enable_pdf_reports}','{enable_pdf_report_user_explain}',
	"enable_quarantine_report",$ligne["enabled"]);
	$sendnow="<div style='text-align:right'>". button("{send_now}","quarantine_report_send()")."</div>";
	if($ligne["enabled"]<>1){$sendnow=null;}
	
	
	$html="
	<input type='hidden' id='URI' value='{$params["URI"]}'>
	<div id='quarantine_report_div'>
	<table>
	<tr>
		<td colspan=4>$field
		
		<hr></td>
	</tR>
	
	<tr>
	<td class=caption nowrap style='font-size:13px'>{timetosendreport}:</td>
	<td>$hours</td>
	<td><strong>:</strong>
	<td>$minutes</td>
	</tr>
	<tr>
	<td colspan=4>
		<table style='margin-top:0px;'>
		<tr>
			<td class=caption align='right' style='font-size:13px'>{build_report_for}:</td>
			<td style='font-size:13px'>$days&nbsp;{days}</td>
		</tr>
		</table>
	</td>	
	</tr>
	<tr>
	<td colspan=4>
		<table style='margin-top:0px;'>
		<tr>
			<td class=caption align='right' style='font-size:13px'>{subject}:</td>
			<td style='font-size:13px'>". Field_text("subject_report",$params["subject"],'font-size:13px;padding:3px;width:100%')."</td>
		</tr>
		<tr>
			<td class=caption align='right' style='font-size:13px'>{server}:</td>
			<td style='font-size:13px'>{$params["URI"]}</td>
		</tr>		
		</table>
	</td>
	</tr>
	<tr>
		<td colspan=4 align='right'><hr>". button("{apply}","quarantine_report_save()")."</td>
	</tr>
	</table>
	</div>
	";


echo $tpl->_ENGINE_parse_body($html);
	
	
}

function save(){
	$q=new mysql();
	$user=new usersMenus();
	if(basename(__FILE__)=="quarantine.report.php"){
		$prefix="http://";
		if($_SERVER["HTTPS"]=="on"){$prefix="https://";}
		$_GET["URI"]="$prefix{$_SERVER['SERVER_NAME']}:{$_SERVER['SERVER_PORT']}";
	}
		
	$datas=base64_encode(serialize($_GET));
	$sql_add="INSERT INTO quarantine_report_users (userid,type,enabled,parameters)
	VALUES('{$_GET["uid"]}','1','{$_GET["enabled"]}','$datas')";
	
	$sql_edit="UPDATE quarantine_report_users SET 
	enabled='{$_GET["enabled"]}',
	parameters='$datas' WHERE userid='{$_GET["uid"]}'";
	
	
	$sql="SELECT userid FROM quarantine_report_users WHERE userid='{$_GET["uid"]}' and `type`=1";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	if($ligne["userid"]==null){$sql=$sql_add;}else{$sql=$sql_edit;}
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		echo $q->mysql_error;
		return;
	}
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?pdf-quarantine-cron=yes");
	
}

function send(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?pdf-quarantine-send={$_GET["uid"]}");
	$tpl=new templates();
	echo $tpl->javascript_parse_text('{task_success_check_mailbox}');
	
}

function Verifrights(){
	
	$users=new usersMenus();
	if($users->AsSystemAdministrator){return true;}
	if($users->AsMessagingOrg){return true;}
	if($users->AllowEditOuSecurity){return true;}
	if($_SESSION["uid"]==null){return false;}
	if($_SESSION["uid"]==$_GET["uid"]){return true;}
	
}

?>