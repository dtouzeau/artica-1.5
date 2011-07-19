<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}

include_once(dirname(__FILE__) . '/ressources/class.users.menus.inc');
include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
include_once(dirname(__FILE__) . '/ressources/class.user.inc');
include_once(dirname(__FILE__) . '/ressources/class.ini.inc');
include_once(dirname(__FILE__) . '/ressources/Rmail.php');
include_once(dirname(__FILE__)."/ressources/class.ezpdf.inc");
include_once(dirname(__FILE__) . '/class.cronldap.inc');
include_once(dirname(__FILE__)."/ressources/class.phpmailer.inc");


if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;$GLOBALS["debug"]=true;}


if($argv[1]=="--single"){
	$_GET["SINGLE"]=true;
	write_syslog("build quarantine report for organization=<{$argv[3]}> and user=<{$argv[2]}>",__FILE__);
	$_GET["mailfrom"]=$argv[4];
	$_GET["subject"]="Re:{$argv[5]}";
	BuildReport($argv[2],$argv[3]);
	die();
}

if($argv[1]=="--user"){
	$_GET["SINGLE"]=true;
	if($GLOBALS["VERBOSE"]){echo "build quarantine report for user=<{$argv[2]}>\n";}
	BuildPDFReport($argv[2]);
	die();
}
if($argv[1]=="--build-cron-users"){
	BuildPDFReportCron();
	die();
}


$ou=$argv[1];
if(trim($ou)==null){die("no organization specified");}

$ldap=new clladp();
$hash=$ldap->HashMembersFromOU($ou);
if(!is_array($hash)){
	write_syslog("\"$ou\ has no organization, shutdown...",__FILE__);
	die("no members");
}

while (list ($email, $ligne) = each ($hash) ){
	BuildReport($email,$ou);
	
}


function BuildReport($uid,$ou){
	$usr=new usersMenus();
	$user=new user($uid);
	$emailsnumbers=count($user->HASH_ALL_MAILS);
	
	if($emailsnumbers==0){
		write_syslog("BuildReport() user=<$uid> has no email addresses",__FILE__);
		return null;
	}
	
	$ouU=strtoupper($ou);
	$ini=new Bs_IniHandler("/etc/artica-postfix/settings/Daemons/OuSendQuarantineReports$ouU");
	$days=$ini->_params["NEXT"]["days"];
	if($days==null){$days=2;}
	
if($ini->_params["NEXT"]["title1"]==null){$ini->_params["NEXT"]["title1"]="Quarantine domain senders";}
if($ini->_params["NEXT"]["title2"]==null){$ini->_params["NEXT"]["title2"]="Quarantine list";}
if($ini->_params["NEXT"]["explain"]==null){$ini->_params["NEXT"]["explain"]="You will find here all mails stored in your quarantine area";}
if($ini->_params["NEXT"]["externalLink"]==null){$ini->_params["NEXT"]["externalLink"]="https://$usr->hostname:9000/user.quarantine.query.php";}

if(preg_match("#([0-9]+) (jours|days)#",$_GET["subject"],$re)){
	write_syslog("Change to {$re[1]} days from subject",__FILE__);
	$days=$re[1];
}


	write_syslog("Starting HTML report ($days days) for $uid $user->DisplayName ($emailsnumbers recipient emails)",__FILE__);
	$date=date('Y-m-d');
	$font_normal="<FONT FACE=\"Arial, Helvetica, sans-serif\" SIZE=2>";
	$font_title="<FONT FACE=\"Arial, Helvetica, sans-serif\" SIZE=4>";
	
while (list ($num, $ligne) = each ($user->HASH_ALL_MAILS) ){
	$recipient_sql[]="mailto='$ligne'";
	
}
	$recipients=implode(" OR ",$recipient_sql);
	$sql="SELECT mailfrom,zDate,MessageID,DATE_FORMAT(zdate,'%W %D %H:%i') as tdate,subject FROM quarantine
	WHERE (zDate>DATE_ADD('$date', INTERVAL -$days DAY)) AND ($recipients)  ORDER BY zDate DESC;";
	
	$q=new mysql();
//	echo "$sql\n";
	$results=$q->QUERY_SQL($sql,"artica_backup");	
	if(!$q->ok){
		write_syslog("Wrong sql query $q->mysql_error",__FILE__);
		return null;
	}
	$style="font-size:11px;border-bottom:1px solid #CCCCCC;margin:3px;padding:3px";
	
	$session=md5($user->password);
	
while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
	$subject=htmlspecialchars($ligne["subject"]);
	$from=trim($ligne["mailfrom"]);
	$zDate=$ligne["tdate"];
	$MessageID=$ligne["MessageID"];
	if($from==null){$from="unknown";}{$domain="unknown";}
	if(preg_match("#(.+?)@(.+)#",$from,$re)){
		$domain=$re[2];
	}
	
	$uri="<a href=\"{$ini->_params["NEXT"]["externalLink"]}?uid=$user->uid&session=$session&mail=$MessageID\">";
	
	$array[$domain][]="<tr>
		<td style=\"$style\" nowrap>$uri$font_normal$zDate</FONT></a></td>
		<td style=\"$style\" nowrap>$uri$font_normal<code>$from</code></FONT></a></td>
		<td style=\"$style\">$uri$font_normal<strong>$subject</strong></FONT></a></td>
		
		</tr>";
	
}
write_syslog("BuildReport: Single ???=<{$_GET["SINGLE"]}>",__FILE__);
$count_domains=count($array);
if(!$_GET["SINGLE"]){
	if($count_domains==0){
		write_syslog("BuildReport() user=<$uid> has no spam domains senders",__FILE__);
		return null;
	}
}
$html="<H1>$font_title$days {$ini->_params["NEXT"]["title1"]}</FONT></H1>
<p style=\"font-size:12px;font-weight:bold\">$font_title{$ini->_params["NEXT"]["explain"]}</FONT> </p>
<hr>
<H2>$font_title$count_domains Domains</FONT></H2>
<table style=\"width:100%\">";
if(is_array($array)){
	while (list ($num, $ligne) = each ($array) ){
		$html=$html."<tr><td><li><strong style=\"font-size:12px\">$font$num</FONT></li></td></tr>\n";
		}
	reset($array);
}



$html=$html."</table>
<hr>
<h2>$font_title{$ini->_params["NEXT"]["title2"]}</FONT></h2>
<table style=\"width:100%;border:1px solid #CCCCCC;margin:5px;padding:5px\">";

if(is_array($array)){
while (list ($num, $ligne) = each ($array) ){
	$html=$html."<hr>
	<table border=1 style=\"width:100%;border:1px solid #CCCCCC;margin:5px;padding:5px\">
	<tr>
		<td colspan=3><strong style=\"font-size:16px\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$font_title$num</FONT></td>
	</tr>
	".implode("\n",$ligne);
	
	
	$html=$html."
	</table>
	";
	
}}
	
if($ini->_params["NEXT"]["mailfrom"]==null){$ini->_params["NEXT"]["mailfrom"]=$_GET["mailfrom"];}
if($ini->_params["NEXT"]["mailfrom"]==null){$ini->_params["NEXT"]["mailfrom"]="root@localhostlocaldomain";}
if($ini->_params["NEXT"]["subject"]==null){$ini->_params["NEXT"]["subject"]=$_GET["subject"];}
if($ini->_params["NEXT"]["subject"]==null){$ini->_params["NEXT"]["subject"]="Daily Quarantine report";}
$tpl=new templates();
$subject=$ini->_params["NEXT"]["subject"];
$mail = new Rmail();
$mail->setFrom("quarantine <{$ini->_params["NEXT"]["mailfrom"]}>");
$mail->setSubject($subject);
$mail->setPriority('normal');
$mail->setText(strip_tags($html));
$mail->setHTML($html);
$address = $user->mail;
$result  = $mail->send(array($address));
write_syslog("From=<{$ini->_params["NEXT"]["mailfrom"]}> to=<{$user->mail}> Send Quarantine Report=<$result>",__FILE__);
	
}

function BuildPDFReport($userid){
	$GLOBALS["SartON"]=time();
	$q=new mysql();
	$sql="SELECT * FROM quarantine_report_users WHERE userid='$userid' and `type`=1 and `enabled`=1";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	if($ligne["enabled"]==0){return;}
	$params=unserialize(base64_decode($ligne["parameters"]));
	$days=$params["days"];
	$subject=$params["subject"];
	$subject=str_replace("{","",$subject);
	$subject=str_replace("}","",$subject);
	$session=md5($user->password);
	if($days<1){$days=2;}
	$user=new user($userid);
	while (list ($num, $ligne) = each ($user->HASH_ALL_MAILS) ){
		$recipient_sql[]="mailto='$ligne'";
	
	}
	$date=date('Y-m-d');
	$recipients=implode(" OR ",$recipient_sql);
	$sql="SELECT mailfrom,zDate,MessageID,DATE_FORMAT(zdate,'%W %D %H:%i') as tdate,subject FROM quarantine
	WHERE (zDate>DATE_ADD('$date', INTERVAL -$days DAY)) AND ($recipients)  ORDER BY zDate DESC;";
	
	if($GLOBALS["VERBOSE"]){echo "$sql\n";}
	
	$datepdf=date('Y-m-d');
	$results=$q->QUERY_SQL($sql,"artica_backup");	
	$num_rows = mysql_num_rows($results);
	if(!$q->ok){
		send_email_events("Build SMTP quarantine report failed for $uid","$sql\n$q->mysql_error","postfix");
		return null;
	}	
	
	if($num_rows==0){return;}
	
	$pdf = new Cezpdf('a4','portrait');
	
$pdf->ezSetMargins(50,70,50,50);	
$all = $pdf->openObject();
$pdf->saveState();
//$pdf->setStrokeColor(0,0,0,1);
$pdf->line(20,40,578,40);
$pdf->line(20,822,578,822);
$pdf->addText(50,34,6,$date);
$pdf->restoreState();
$pdf->closeObject();
$pdf->addObject($all,'all');
$mainFont = dirname(__FILE__)."/ressources/fonts/Helvetica.afm";
$codeFont = dirname(__FILE__)."/ressources/fonts/Courier.afm";
$pdf->selectFont($mainFont);
$pdf->ezText("$user->DisplayName\n",30,array('justification'=>'centre'));
$pdf->ezText("$subject\n",20,array('justification'=>'centre'));
$pdf->ezText("$date ($num_rows message(s))",18,array('justification'=>'centre'));
$pdf->ezStartPageNumbers(100, 30, 12, "left", "Page {PAGENUM}/{TOTALPAGENUM}");
$pdf->ezNewPage();	
	
$options = array('showLines'=> 2,
	'showHeadings' => 0,
	'shaded'=> 2,
	'shadeCol' => array(1,1,1),
	'shadeCol2' => array(0.8,0.8,0.8),
	'fontSize' => 11,
	'textCol' => array(0,0,0),
	'textCol2' => array(1,1,1),
	'titleFontSize' => 16,
	'titleGap' => 8,
	'rowGap' => 5,
	'colGap' => 10,
	'lineCol' => array(1,1,1),
	'xPos' => 'left',
	'xOrientation' => 'right',
	'width' => 500,
	'maxWidth' => 500);



while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	
	$mail_subject=$ligne["subject"];
	$from=trim($ligne["mailfrom"]);
	$zDate=$ligne["tdate"];
	$MessageID=$ligne["MessageID"];
	if($from==null){$from="unknown";}{$domain="unknown";}
	if(preg_match("#(.+?)@(.+)#",$from,$re)){
		$domain=$re[2];
	}
	$mail_subject=str_replace("{","",$mail_subject);
	$mail_subject=str_replace("}","",$mail_subject);
	$uri="<c:alink:{{$params["URI"]}/user.quarantine.query.php?uid=$user->uid&session=$session&mail=$MessageID>$mail_subject</c:alink>";
	
	
	$data[]=array(
		$zDate,
		$from,
		$uri
		
		);
}

$pdf->ezTable($data,$cols,$subject,$options);
$pdfcode = $pdf->output();

 $fname = "/tmp/".date('Ymdhi')."-$user->mail-quarantines.pdf";
if($GLOBALS["VERBOSE"] ){echo "$pdf->messages\nbuilding $fname\n";}  

 @unlink($fname);
 if($GLOBALS["VERBOSE"] ){echo "Building $fname\n";} 
  $fp = fopen($fname,'w');
  fwrite($fp,$pdfcode);
  fclose($fp);
	
if(preg_match("#(.+?)@(.+)#",$user->mail,$re)){
	$domain=$re[2];
}  
$PostmasterAdress="no-reply-quarantine@$domain";
$ini=new Bs_IniHandler("/etc/artica-postfix/smtpnotif.conf");
if($ini->_params["SMTP"]["smtp_sender"]<>null){$PostmasterAdress=$ini->_params["SMTP"]["smtp_sender"];}
if(file_exists('/etc/artica-postfix/settings/Daemons/PostfixPostmaster')){
	$PostmasterAdress=trim(file_get_contents('/etc/artica-postfix/settings/Daemons/PostfixPostmaster'));
}

$unix=new unix();
$sendmail=$unix->find_program("sendmail");
$mail = new PHPMailer(true); 
$mail->IsSendmail();
$mail->AddAddress($user->mail,$user->DisplayName);
$mail->AddReplyTo($PostmasterAdress,$PostmasterAdress);
$mail->From=$PostmasterAdress;
$mail->Subject=$subject;
$mail->Body="$subject\nSee attached file...\n";
$mail->AddAttachment($fname,basename($fname));
$content=$mail->Send(true);
$tmpfile="/tmp/".md5(date('Y-m-d H:is')."-$user->uid-msmtp");
file_put_contents($tmpfile,$content);
$cmd="$sendmail -bm -t -f $PostmasterAdress <$tmpfile";
system($cmd);
@unlink($tmpfile);
@unlink($fname);
$SartOff=time();
$time_duration=distanceOfTimeInWords($GLOBALS["SartON"],$SartOff);
send_email_events("SMTP quarantine report for $user->mail (success) $num_rows message(s)","duration:$time_duration\nnothing to say more...","postfix");

	
}

function BuildPDFReportCron(){
	$q=new mysql();
	$sql="SELECT * FROM quarantine_report_users WHERE `type`=1 and `enabled`=1";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	
	if(!$q->ok){return null;}
	
	foreach (glob("/etc/cron.d/artica-usr-quar*") as $filename) {
		@unlink($filename);
	}
	
	
	$count=0;
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$params=unserialize(base64_decode($ligne["parameters"]));
		$count=$count+1;
		$f[]="MAILTO=\"\"";
		$f[]="{$params["min_execution"]} {$params["hour_execution"]} * * * root ".LOCATE_PHP5_BIN2()." ".__FILE__." --user {$ligne["userid"]} >/dev/null 2>&1"; 
		@file_put_contents("/etc/cron.d/artica-usr-quar-$count",@implode("\n",$f) );
		unset($f);
	}
	
}






?>