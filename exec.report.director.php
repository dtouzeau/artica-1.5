<?php

include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.users.menus.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.user.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__)."/framework/class.unix.inc");
include_once(dirname(__FILE__)."/ressources/class.ldap.inc");
include_once(dirname(__FILE__)."/framework/frame.class.inc");
include_once(dirname(__FILE__)."/ressources/class.ezpdf.inc");
include_once(dirname(__FILE__)."/ressources/class.artica.graphs.inc");
include_once(dirname(__FILE__)."/class.cronldap.inc");

if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;$GLOBALS["debug"]=true;}

if($argv[1]=="--schedules"){schedules();die();}
if($argv[1]=="--build"){report_mysql($argv[2]);die();}

if(isset($_GET["flow"])){
	echo "<H1>{$_GET["ou"]}</H1>";
	$GLOBALS["OU"]=$_GET["ou"];
	FlowMessages();
	echo "<img src='PDFs/graph1.png'>";
	die();
}
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}


parsecmdlines($argv);

if($GLOBALS["FLOW"]<>null){sql_domain();FlowMessages();die();}
if($GLOBALS["FLOW"]<>null){sql_domain();FlowMessages();die();}





buildpdf();

function parsecmdlines($array){
	
	while (list ($i, $line) = each ($array)){
		if(preg_match("#--ou=(.+)#",$line,$re)){$GLOBALS["OU"]=$re[1];}
		if(preg_match("#--last=([0-9]+)#",$line,$re)){$GLOBALS["LAST_DAYS"]=$re[1];}
		if(preg_match("#--flow#",$line,$re)){$GLOBALS["FLOW"]="yes";}
		if(preg_match("#--rcpt#",$line,$re)){$GLOBALS["RCPT_TO"]=$re[1];}
		
		
		
	}
	
	if($GLOBALS["LAST_DAYS"]==null){$GLOBALS["LAST_DAYS"]=7;}
}


function report_mysql($ID){
	$sql="SELECT ID,ou,enabled,report_datas FROM reports WHERE ID=$ID";
	$q=new mysql();
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$array=unserialize(base64_decode($ligne["report_datas"]));
	if($array["day"]==null){$array["day"]=1;}
	if($array["hour"]==null){$array["hour"]="5";}
	if($array["min"]==null){$array["min"]="0";}
	if($array["lastdays"]==null){$array["lastdays"]="7";}	
	
	$GLOBALS["LAST_DAYS"]=$array["lastdays"];
	$GLOBALS["OU"]=$ligne["ou"];
	$GLOBALS["RCPT_TO"]=$array["recipient"];
	buildpdf();
}


function getSommaire(){

$ldap=new clladp();
$GLOBALS["OU-USERS"]=$ldap->hash_users_ou($GLOBALS["OU"]);
echo __FUNCTION__." ".count($GLOBALS["OU-USERS"])." users...\n";

	
}

function buildpdf(){
	@mkdir("/usr/share/artica-postfix/PDFs",666,true);
	
	if($GLOBALS["RCPT_TO"]==null){
		echo "No recipient set...\n";
		return;
	}
	
if($GLOBALS["OU"]==null){
		echo "No organization set...\n";
		return;
	}	
	
	getSommaire();
	sql_domain();
	$date=date("Y-m-d");
	
$pdf = new Cezpdf('a4','portrait');
echo __FUNCTION__." Creating instance done...\n";

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
$pdf->ezText("{$GLOBALS["OU"]}\n",30,array('justification'=>'centre'));
$pdf->ezText("Messaging report\n",20,array('justification'=>'centre'));
$pdf->ezText("$date",18,array('justification'=>'centre'));
$pdf->ezText(count($GLOBALS["OU-USERS"]) . " users",18,array('justification'=>'centre'));
$pdf->ezStartPageNumbers(100, 30, 12, "left", "Page {PAGENUM}/{TOTALPAGENUM}");
$pdf->ezNewPage();

$pdf->ezText("The report:",28,array('justification'=>'left'));
$pdf->ezText("");
$pdf->ezText("The current report is based on ". count($GLOBALS["mydomains"]). " domains",12,array('justification'=>'left'));
$pdf->ezText("Including ". @implode(", ",$GLOBALS["mydomains"]). " for the last {$GLOBALS["LAST_DAYS"]} days",12,array('justification'=>'left'));


$sql="SELECT COUNT(bounce_error) as tcount,bounce_error FROM smtp_logs WHERE {$GLOBALS["SQL_DOMAINS"]} AND time_stamp>DATE_ADD(NOW(), INTERVAL -{$GLOBALS["LAST_DAYS"]} DAY) GROUP BY bounce_error";
$q=new mysql();
$results=$q->QUERY_SQL($sql,"artica_events");
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	$data[]=array($ligne["tcount"],$ligne["bounce_error"]);
	
}

$pdf->ezText("");


$title="Global email status during the period";
// 005447 = 0,0.32,0.278
// CCCCCC = 0.8,0.8,0.8

$options = array('showLines'=> 2,'showHeadings' => 0,'shaded'=> 2,'shadeCol' => array(1,1,1),'shadeCol2' => array(0.8,0.8,0.8),'fontSize' => 11,'textCol' => array(0,0,0),'textCol2' => array(1,1,1),'titleFontSize' => 16,'titleGap' => 8,'rowGap' => 5,'colGap' => 10,'lineCol' => array(1,1,1),'xPos' => 'left','xOrientation' => 'right','width' => 500,'maxWidth' => 500);
$pdf->ezTable($data,$cols,$title,$options);
$file=FlowMessages();
$pdf->ezNewPage();
echo __FUNCTION__." image $file\n";
$pdf->ezImage("/usr/share/artica-postfix/PDFs/graph1.png",5,500,"none",'left',1); 
$pdf->ezText("");
$pdf->ezImage("/usr/share/artica-postfix/PDFs/graph2.png",5,500,"none",'left',1); 
$pdf->ezNewPage();

//----------------------------------------------------------------------------------------------------------
$sql="SELECT COUNT( ID ) AS tcount,delivery_user
FROM smtp_logs
WHERE {$GLOBALS["SQL_DOMAINS"]}
AND time_stamp > DATE_ADD( NOW( ) , INTERVAL -{$GLOBALS["LAST_DAYS"]}
DAY )
GROUP BY delivery_user ORDER BY tcount DESC LIMIT 0,10 ";
$q=new mysql();
$results=$q->QUERY_SQL($sql,"artica_events");
echo $sql;
unset($data);
$data[]=array("nb","recipients");

while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	if($ligne["delivery_user"]==null){continue;}
	$data[]=array($ligne["tcount"],$ligne["delivery_user"]);
}
$title="Most active users (recipients) during the period";
$options = array('showLines'=> 2,'showHeadings' => 0,'shaded'=> 2,'shadeCol' => array(1,1,1),'shadeCol2' => array(0.8,0.8,0.8),'fontSize' => 11,'textCol' => array(0,0,0),'textCol2' => array(1,1,1),'titleFontSize' => 16,'titleGap' => 8,'rowGap' => 5,'colGap' => 10,'lineCol' => array(1,1,1),'xPos' => 'left','xOrientation' => 'right','width' => 500,'maxWidth' => 500);
$pdf->ezTable($data,$cols,$title,$options);
//----------------------------------------------------------------------------------------------------------
$pdf->ezText("\n");
//----------------------------------------------------------------------------------------------------------
$sql="SELECT COUNT( ID ) AS tcount,sender_user
FROM smtp_logs
WHERE {$GLOBALS["SQL_OUT_DOMAINS"]}
AND time_stamp > DATE_ADD( NOW( ) , INTERVAL -{$GLOBALS["LAST_DAYS"]}
DAY )
GROUP BY sender_user ORDER BY tcount DESC LIMIT 0,10 ";
$q=new mysql();
$results=$q->QUERY_SQL($sql,"artica_events");
echo $sql;
unset($data);
$data[]=array("nb","senders");

while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	if($ligne["sender_user"]==null){continue;}
	$data[]=array($ligne["tcount"],$ligne["sender_user"]);
}
$title="Most active users (senders) during the period";
$options = array('showLines'=> 2,'showHeadings' => 0,'shaded'=> 2,'shadeCol' => array(1,1,1),'shadeCol2' => array(0.8,0.8,0.8),'fontSize' => 11,'textCol' => array(0,0,0),'textCol2' => array(1,1,1),'titleFontSize' => 16,'titleGap' => 8,'rowGap' => 5,'colGap' => 10,'lineCol' => array(1,1,1),'xPos' => 'left','xOrientation' => 'right','width' => 500,'maxWidth' => 500);
$pdf->ezTable($data,$cols,$title,$options);

//----------------------------------------------------------------------------------------------------------
$pdf->ezNewPage();
//----------------------------------------------------------------------------------------------------------
$sql="SELECT COUNT( ID ) AS tcount,sender_user
FROM smtp_logs
WHERE {$GLOBALS["SQL_DOMAINS"]}
AND time_stamp > DATE_ADD( NOW( ) , INTERVAL -{$GLOBALS["LAST_DAYS"]}
DAY )
GROUP BY sender_user ORDER BY tcount DESC LIMIT 0,32 ";
$q=new mysql();
$results=$q->QUERY_SQL($sql,"artica_events");
echo $sql;
unset($data);
$data[]=array("nb","Internet senders");

while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	if($ligne["sender_user"]==null){continue;}
	$data[]=array($ligne["tcount"],$ligne["sender_user"]);
}
$title="Most active sender internet users during the period";
$options = array('showLines'=> 2,'showHeadings' => 0,'shaded'=> 2,'shadeCol' => array(1,1,1),'shadeCol2' => array(0.8,0.8,0.8),'fontSize' => 10,'textCol' => array(0,0,0),'textCol2' => array(1,1,1),'titleFontSize' => 16,'titleGap' => 8,'rowGap' => 5,'colGap' => 10,'lineCol' => array(1,1,1),'xPos' => 'left','xOrientation' => 'right','width' => 500,'maxWidth' => 500);
$pdf->ezTable($data,$cols,$title,$options);
//----------------------------------------------------------------------------------------------------------
$pdf->ezNewPage();
//----------------------------------------------------------------------------------------------------------
$sql="SELECT COUNT( ID ) AS tcount,delivery_user
FROM smtp_logs
WHERE {$GLOBALS["SQL_OUT_DOMAINS"]}
AND time_stamp > DATE_ADD( NOW( ) , INTERVAL -{$GLOBALS["LAST_DAYS"]}
DAY )
GROUP BY delivery_user ORDER BY tcount DESC LIMIT 0,32 ";
$q=new mysql();
$results=$q->QUERY_SQL($sql,"artica_events");
echo $sql;
unset($data);
$data[]=array("nb","Internet recipients");

while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	if($ligne["delivery_user"]==null){continue;}
	$data[]=array($ligne["tcount"],$ligne["delivery_user"]);
}
$title="Most active internet recipients during the period";
$options = array('showLines'=> 2,'showHeadings' => 0,'shaded'=> 2,'shadeCol' => array(1,1,1),'shadeCol2' => array(0.8,0.8,0.8),'fontSize' => 10,'textCol' => array(0,0,0),'textCol2' => array(1,1,1),'titleFontSize' => 16,'titleGap' => 8,'rowGap' => 5,'colGap' => 10,'lineCol' => array(1,1,1),'xPos' => 'left','xOrientation' => 'right','width' => 500,'maxWidth' => 500);
$pdf->ezTable($data,$cols,$title,$options);

//----------------------------------------------------------------------------------------------------------
$pdf->ezNewPage();
//----------------------------------------------------------------------------------------------------------;

$pdf->ezText("Per users report",28,array('justification'=>'center'));
$pdf->ezText("");
$pdf->ezText(count($GLOBALS["OU-USERS"]). " users detailed report",18,array('justification'=>'center'));
//----------------------------------------------------------------------------------------------------------
$pdf->ezNewPage();
//----------------------------------------------------------------------------------------------------------;

while (list ($uid) = each ($GLOBALS["OU-USERS"])){
	$u=new user($uid);
	$displayname=$u->DisplayName;
	echo "Generate report for $u->uid\n";
	$pdf->ezText("$displayname",22,array('justification'=>'left'));
	$pdf->ezText("");
	$pdf->ezText("The current report is based on ". count($u->HASH_ALL_MAILS). " email addresses",10,array('justification'=>'left'));
	$pdf->ezText("Including ". @implode(", ",$u->HASH_ALL_MAILS). " mails",10,array('justification'=>'left'));
	$pdf->ezText("\n");	
	FlowMessages_users($uid,$u->HASH_ALL_MAILS);
	
	if(is_file("/usr/share/artica-postfix/PDFs/$uid-inbound.png")){
		$pdf->ezImage("/usr/share/artica-postfix/PDFs/$uid-inbound.png",5,500,"none",'left',1); 
		$pdf->ezText("");
	}
	
	if(is_file("/usr/share/artica-postfix/PDFs/$uid-outbound.png")){
		$pdf->ezImage("/usr/share/artica-postfix/PDFs/$uid-outbound.png",5,500,"none",'left',1); 
		$pdf->ezText("");
	}	
	
	if(is_array($GLOBALS[$uid]["RECEIVE"])){
		$title="Most Internet senders for  $displayname during the period";
		$options = array('showLines'=> 2,'showHeadings' => 0,'shaded'=> 2,'shadeCol' => array(1,1,1),'shadeCol2' => array(0.8,0.8,0.8),'fontSize' => 11,'textCol' => array(0,0,0),'textCol2' => array(1,1,1),'titleFontSize' => 16,'titleGap' => 8,'rowGap' => 5,'colGap' => 10,'lineCol' => array(1,1,1),'xPos' => 'left','xOrientation' => 'right','width' => 500,'maxWidth' => 500);
		$pdf->ezTable($GLOBALS[$uid]["RECEIVE"],$cols,$title,$options);
	}
	
	if(is_array($GLOBALS[$uid]["SENT"])){
		$title="Most Internet recipients for  $displayname during the period";
		$options = array('showLines'=> 2,'showHeadings' => 0,'shaded'=> 2,'shadeCol' => array(1,1,1),'shadeCol2' => array(0.8,0.8,0.8),'fontSize' => 11,'textCol' => array(0,0,0),'textCol2' => array(1,1,1),'titleFontSize' => 16,'titleGap' => 8,'rowGap' => 5,'colGap' => 10,'lineCol' => array(1,1,1),'xPos' => 'left','xOrientation' => 'right','width' => 500,'maxWidth' => 500);
		$pdf->ezTable($GLOBALS[$uid]["SENT"],$cols,$title,$options);
	}	
	
	
	$pdf->ezNewPage();
	
}



$pdfcode = $pdf->output();

 $fname = "/usr/share/artica-postfix/PDFs/report-director-{$GLOBALS["OU"]}.pdf";
if($GLOBALS["VERBOSE"] ){echo "$pdf->messages\nbuilding $fname\n";}  

 @unlink($fname);
 if($GLOBALS["VERBOSE"] ){echo "Building $fname\n";} 
  $fp = fopen($fname,'w');
  fwrite($fp,$pdfcode);
  fclose($fp);

  $users=new usersMenus();
  send_email_events("[ARTICA]: ($users->hostname) {$GLOBALS["OU"]}:: weekly report sended to {$GLOBALS["RCPT_TO"]}","",
  "mailbox",
  date('Y-m-d H:i:s'),
  array($fname),
  $GLOBALS["RCPT_TO"]
  );	
  if($GLOBALS["VERBOSE"] ){echo "Sending mail\n";} 
  SendMailNotif("you will find in attached file the weekly report of your $users->hostname mail server",
  "[ARTICA]: ($users->hostname) {$GLOBALS["OU"]}:: weekly messaging report",
  null,
  $GLOBALS["RCPT_TO"],
  $GLOBALS["VERBOSE"], 
  array($fname));
  
  
}
function sql_domain(){
	
	$ldap=new clladp();
	$domains=$ldap->Hash_domains_table($GLOBALS["OU"]);
	if(!is_array($domains)){
		echo __FUNCTION__." No domains for '{$GLOBALS["OU"]}'\n";
		return null;}
	while (list ($domain,$nothing) = each ($domains) ){
		$array_domain[]="OR delivery_domain='$domain'";
		$array_out_domain[]="OR sender_domain='$domain'";
		echo __FUNCTION__." $domain...\n";
		$GLOBALS["mydomains"][]=$domain;
	}
	
	$sql_domain=implode(" ",$array_domain);
	
	$sql_out_domain=implode(" ",$array_out_domain);
	
	if(substr($sql_domain,0,2)=="OR"){$sql_domain=substr($sql_domain,2,strlen($sql_domain));}
	if(substr($sql_out_domain,0,2)=="OR"){$sql_out_domain=substr($sql_out_domain,2,strlen($sql_out_domain));}
	$sql_domain="(".trim($sql_domain).")";
	$GLOBALS["SQL_DOMAINS"]=$sql_domain;
	$GLOBALS["SQL_OUT_DOMAINS"]=$sql_out_domain;		
	
}

function FlowMessages_users($uid,$arraymails){
	
while (list ($nothing,$email) = each ($arraymails) ){
		$array_in[]="OR delivery_user='$email'";
		$array_out[]="OR sender_user='$domain'";
		
	}	
	$_in=implode(" ",$array_in);
	$_out=implode(" ",$array_out);
	
	if(substr($_in,0,2)=="OR"){$_in=substr($_in,2,strlen($_in));}
	if(substr($_out,0,2)=="OR"){$_out=substr($_out,2,strlen($_out));}		
	
$sql="SELECT COUNT( ID ) AS tcount, DATE_FORMAT( time_stamp, '%m-%d %h:00' ) AS ttime
FROM smtp_logs
WHERE {$_in}
AND time_stamp > DATE_ADD( NOW( ) , INTERVAL -{$GLOBALS["LAST_DAYS"]} DAY )
GROUP BY DATE_FORMAT( time_stamp, '%m-%d %h:00' )";
$q=new mysql();

$g=new artica_graphs($fileName,60);
$results=$q->QUERY_SQL($sql,"artica_events");
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	$g->ydata[]=$ligne["tcount"];
	$g->xdata[]=$ligne["ttime"];
}

$fileName="/usr/share/artica-postfix/PDFs/$uid-inbound.png";

$g->title="Inbound messages";
$g->x_title="messages number";
$g->y_title="days-month hour";
$g->filename=$fileName;
$g->width=600;
$g->height=400;;
@unlink($fileName);
$g->line_green();
	

$sql="SELECT COUNT( ID ) AS tcount, DATE_FORMAT( time_stamp, '%m-%d %h:00' ) AS ttime
FROM smtp_logs
WHERE {$_out}
AND time_stamp > DATE_ADD( NOW( ) , INTERVAL -{$GLOBALS["LAST_DAYS"]}
DAY )
GROUP BY DATE_FORMAT( time_stamp, '%m-%d %h:00' )";
$q=new mysql();

$g=new artica_graphs($fileName,60);
$results=$q->QUERY_SQL($sql,"artica_events");
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	$g->ydata[]=$ligne["tcount"];
	$g->xdata[]=$ligne["ttime"];
}

$fileName="/usr/share/artica-postfix/PDFs/$uid-outbound.png";

$g->title="Outbound messages";
$g->x_title="messages number";
$g->y_title="days-month hour";
$g->filename=$fileName;
$g->width=600;
$g->height=400;;
@unlink($fileName);
$g->line_green();


$sql="SELECT COUNT( ID ) AS tcount,sender_user
FROM smtp_logs
WHERE {$_in}
AND time_stamp > DATE_ADD( NOW( ) , INTERVAL -{$GLOBALS["LAST_DAYS"]}
DAY )
GROUP BY sender_user ORDER BY tcount DESC LIMIT 0,20";
$q=new mysql();
$results=$q->QUERY_SQL($sql,"artica_events");
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	if($ligne["sender_user"]==null){continue;}
	$GLOBALS[$uid]["RECEIVE"][]=array($ligne["tcount"],$ligne["sender_user"]);
}


$sql="SELECT COUNT( ID ) AS tcount,delivery_user
FROM smtp_logs
WHERE {$_out}
AND time_stamp > DATE_ADD( NOW( ) , INTERVAL -{$GLOBALS["LAST_DAYS"]}
DAY )
GROUP BY delivery_user ORDER BY tcount DESC LIMIT 0,20";
$q=new mysql();
$results=$q->QUERY_SQL($sql,"artica_events");
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	if($ligne["delivery_user"]==null){continue;}
	$GLOBALS[$uid]["SENT"][]=array($ligne["tcount"],$ligne["delivery_user"]);
}



}


function FlowMessages(){
	
	
$sql="SELECT COUNT( ID ) AS tcount, DATE_FORMAT( time_stamp, '%m-%d' ) AS tday
FROM smtp_logs
WHERE {$GLOBALS["SQL_DOMAINS"]}
AND time_stamp > DATE_ADD( NOW( ) , INTERVAL -{$GLOBALS["LAST_DAYS"]}
DAY )
GROUP BY DATE_FORMAT( time_stamp, '%m-%d' )";
$q=new mysql();

$g=new artica_graphs($fileName,60);
$results=$q->QUERY_SQL($sql,"artica_events");
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	$g->ydata[]=$ligne["tcount"];
	$g->xdata[]  =$ligne["tday"];
}

$fileName="/usr/share/artica-postfix/PDFs/graph1.png";

$g->title="Inbound messages";
$g->x_title="messages number";
$g->y_title="days-month";
$g->filename=$fileName;
$g->width=600;
$g->height=400;;
@unlink($fileName);
$g->line_green();
	
$sql="SELECT COUNT( ID ) AS tcount, DATE_FORMAT( time_stamp, '%m-%d' ) AS tday
FROM smtp_logs
WHERE {$GLOBALS["SQL_OUT_DOMAINS"]}
AND time_stamp > DATE_ADD( NOW( ) , INTERVAL -{$GLOBALS["LAST_DAYS"]}
DAY )
GROUP BY DATE_FORMAT( time_stamp, '%m-%d' )";
$q=new mysql();



$g=new artica_graphs($fileName,60);
$results=$q->QUERY_SQL($sql,"artica_events");
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	$g->ydata[]=$ligne["tcount"];
	$g->xdata[]=$ligne["tday"];
}

$fileName="/usr/share/artica-postfix/PDFs/graph2.png";

$g->title="Outbound messages";
$g->x_title="messages number";
$g->y_title="days-month";
$g->filename=$fileName;
$g->width=600;
$g->height=400;;
@unlink($fileName);
$g->line_green();	
}

function schedules(){
	$sql="SELECT * FROM reports WHERE enabled=1 AND report_type=1";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$array=unserialize(base64_decode($ligne["report_datas"]));
		if($array["day"]==null){$array["day"]=1;}
		if($array["hour"]==null){$array["hour"]="5";}
		if($array["min"]==null){$array["min"]="0";}
		if($array["lastdays"]==null){$array["lastdays"]="7";}
		
		$f[]="{$array["min"]} {$array["hour"]} * * {$array["day"]} ".LOCATE_PHP5_BIN2()." ".__FILE__." {$ligne["ID"]}";
	}
	
	if(is_array($f)){
		echo "Starting......: Daemon (fcron) ". count($f)." scheduled reports\n";
		@file_put_contents("/etc/artica-postfix/reports.tasks",@implode("\n",$f));
	}
	
	
}




?>