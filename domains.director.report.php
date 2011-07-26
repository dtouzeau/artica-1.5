<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.cron.inc');
	
	
	
	$user=new usersMenus();
	if(!$user->AllowEditOuSecurity){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["lastdays"])){SAVE();exit;}
	js();
	
	
	
function js(){
$page=CurrentPageName();
$users=new usersMenus();
$tpl=new templates();
$title=$tpl->_ENGINE_parse_body('{global_director_report}');
$ou_decrypted=base64_decode($_GET["ou"]);
$html="

function DIRECTOR_REPORT(){
	YahooWin3('600','$page?popup=yes&ou={$_GET["ou"]}','$title');
	
	}
	
var X_DIRECTOR_SAVE= function (obj) {
	var results=obj.responseText;
	if(results.length>0){alert(results);}
	DIRECTOR_REPORT();
	}	
	
function DIRECTOR_SAVE(){
	var XHR = new XHRConnection();
	if(document.getElementById('report_enabled').checked){
		XHR.appendData('report_enabled',1);
	}else{
		XHR.appendData('report_enabled',0);
	}
	XHR.appendData('day',document.getElementById('day').value);
	XHR.appendData('ou','{$_GET["ou"]}');
	XHR.appendData('hour',document.getElementById('hour').value);
	XHR.appendData('min',document.getElementById('min').value);
	XHR.appendData('lastdays',document.getElementById('lastdays').value);
	XHR.appendData('recipient',document.getElementById('recipient').value);
	XHR.appendData('ID',document.getElementById('ID').value);
	document.getElementById('report-image').src='img/wait_verybig.gif';
	XHR.sendAndLoad('$page', 'GET',X_DIRECTOR_SAVE);	
}
	
DIRECTOR_REPORT();
";

echo $html;	
	
}	

function SAVE(){
	$ou=base64_decode($_GET["ou"]);
	$datas=base64_encode(serialize($_GET));
	$sqladd="INSERT INTO reports (report_type,report_datas,enabled,ou) VALUES
	(1,'$datas','{$_GET["report_enabled"]}','$ou');";
	
	$sql_edit="UPDATE reports SET report_datas='$datas',enabled='{$_GET["report_enabled"]}' WHERE ID={$_GET["ID"]}";
	
	if($_GET["ID"]>0){$sql=$sql_edit;}else{$sql=$sqladd;}
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo $q->mysql_error;return;}
	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?RestartDaemon=yes");
}


function popup(){
	$ou=base64_decode($_GET["ou"]);
	$sql="SELECT ID,enabled,report_datas FROM reports WHERE ou='$ou' AND report_type=1";
	$q=new mysql();
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$array=unserialize(base64_decode($ligne["report_datas"]));
	
	if($array["day"]==null){$array["day"]=1;}
	if($array["hour"]==null){$array["hour"]="5";}
	if($array["min"]==null){$array["min"]="0";}
	if($array["lastdays"]==null){$array["lastdays"]="7";}
	
	$cron=new cron_macros(1);
	
	$days=$cron->cron_days;
	
	for($i=1;$i<60;$i++){
		$lastdays[$i]=$i;
	}
	
	
	$html="
	". Field_hidden("ID",$ligne["ID"])."
	<table style='width:100%'>
	<tr>
		<td valign='top'><img src='img/postmaster-identity-128.png' id='report-image'></td>
		<td>
		<div style='font-size:14px' class=explain>{global_director_report_explain}</div>
		<table style='width:100%' class=form>
		<tr>
			<td class=legend style='font-size:13px'>{enabled}:</td>
			<td>". Field_checkbox("report_enabled",1,$ligne["enabled"])."</td>
		</tr>		
		<tr>
			<td class=legend style='font-size:13px'>{generate_report_each}:</td>
			<td>". Field_array_Hash($days,"day",$array["day"],null,null,0,"font-size:13px;padding:3px")."</td>
		</tr>
		<tr>
			<td class=legend style='font-size:13px'>{time}:</td>
			<td><table style='width:1%'>
				<tr>
					<td>
						". Field_array_Hash($cron->cron_hours,"hour",$array["hour"],null,null,0,"font-size:13px;padding:3px")."</td>
					<td width=1% style='font-size:13px'>:</td>
					<td>". Field_array_Hash($cron->cron_mins,"min",$array["min"],null,null,0,"font-size:13px;padding:3px")."</td>
				</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td class=legend style='font-size:13px'>{recipient}:</td>
			<td>". Field_text("recipient",$array["recipient"],"font-size:13px;padding:3px")."</td>
		</tr>		
		<tr>
			<td class=legend style='font-size:13px'>{last_days}:</td>
			<td>". Field_array_Hash($lastdays,"lastdays",$array["lastdays"],null,null,0,"font-size:13px;padding:3px")."</td>
		</tr>
		
		<tr>
			<td colspan='2' align='right'><hr>". button("{apply}","DIRECTOR_SAVE()")."</td>
		</tr>
		
		</table>
		
		
		</td>
	</tr>
	</table>
	
	";
	
$tpl=new templates();
echo $tpl->_ENGINE_parse_body($html);		
	
}

?>