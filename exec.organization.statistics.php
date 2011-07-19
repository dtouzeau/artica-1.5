<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');
if(!Build_pid_func(__FILE__,"MAIN")){
	writelogs(basename(__FILE__).":Already executed.. aborting the process",basename(__FILE__),__FILE__,__LINE__);
	die();
}

$ldap=new clladp();
$hash=$ldap->hash_get_ou(true);
CleanTable();
while (list ($num, $ligne) = each ($hash) ){
	GeneratStats($num);
}


function CleanTable(){
	
$q=new mysql();
$sql="UPDATE smtp_logs SET bounce_error = 'Sended' WHERE bounce_error LIKE '250%'";
$q->QUERY_SQL($sql,"artica_events");

$sql="UPDATE smtp_logs SET bounce_error = 'Sended' WHERE bounce_error LIKE 'delivered via%'";
$q->QUERY_SQL($sql,"artica_events");

$sql="UPDATE smtp_logs SET bounce_error = 'Error' WHERE bounce_error LIKE '4.5.0 Error%'";
$q->QUERY_SQL($sql,"artica_events");

$sql="UPDATE smtp_logs SET bounce_error = 'hostname not found' WHERE bounce_error LIKE '%cannot find your hostname%'";
$q->QUERY_SQL($sql,"artica_events");

$sql="UPDATE smtp_logs SET bounce_error = 'DNS Error' WHERE bounce_error LIKE '%Name service error%'";
$q->QUERY_SQL($sql,"artica_events");

$sql="UPDATE smtp_logs SET bounce_error = 'Relaying denied' WHERE bounce_error LIKE '%550%Relaying denied%'";
$q->QUERY_SQL($sql,"artica_events");

$sql="UPDATE smtp_logs SET bounce_error = 'Mailbox Error' WHERE bounce_error LIKE 'connect to %lmtp%No such file or directory'";
$q->QUERY_SQL($sql,"artica_events");


$sql="UPDATE smtp_logs SET bounce_error = 'Mailbox unknown' WHERE bounce_error LIKE '%550-Mailbox unknown%'";
$q->QUERY_SQL($sql,"artica_events");

$sql="UPDATE smtp_logs SET bounce_error = 'User unknown' WHERE bounce_error LIKE '%550 User unknow%'";
$q->QUERY_SQL($sql,"artica_events");
}


function GeneratStats($ou){

$sql_domain=sql_domain($ou);
if($sql_domain==null){return null;}	
$sql="SELECT COUNT(ID) as tcount,bounce_error FROM smtp_logs 
WHERE $sql_domain AND DATE_FORMAT( time_connect, '%Y-%m-%d' ) = DATE_FORMAT( NOW( ) , '%Y-%m-%d' )
GROUP BY bounce_error";

$q=new mysql();
$results=$q->QUERY_SQL($sql,"artica_events");
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	$bounce_error=TransFormBounceError($ligne["bounce_error"]);

	if($bounce_error==null){$bounce_error="Sended";}
	$arr[]="\"{$ligne["tcount"]}\"=>\"$bounce_error\"";
}
if(!is_array($arr)){$day_camember="array()";}else{$day_camember="array(".implode(",\n",$arr).");";}


$sql="SELECT COUNT(ID) as tcount,sender_domain FROM smtp_logs 
WHERE $sql_domain AND DATE_FORMAT( time_connect, '%Y-%m-%d' ) = DATE_FORMAT( NOW( ) , '%Y-%m-%d' ) AND (
	SPAM=1 OR
	spammy=1 
	OR bounce_error='Discard' 
	OR bounce_error='RBL'
	OR bounce_error='Greylisting'
	OR bounce_error='Domain not found'
	OR bounce_error='Relay access denied'
	OR bounce_error='Relaying denied'
	
	)
GROUP BY sender_domain ORDER BY tcount DESC LIMIT 0,10";

$sql_log_1="$sql\n";

unset($arr);
$results=$q->QUERY_SQL($sql,"artica_events");
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	$arr1[]="\"{$ligne["tcount"]}\"=>\"{$ligne["sender_domain"]}\"";
}
if(!is_array($arr1)){$top_10_spammers="array()";}else{$top_10_spammers="array(".implode(",\n",$arr1).");";}

//--------------------------------------------------------------------------------------------------------------------------

$sql="\n\nSELECT COUNT(ID) as tcount FROM smtp_logs 
WHERE $sql_domain AND DATE_FORMAT( time_connect, '%Y-%m-%d' ) = DATE_FORMAT( NOW( ) , '%Y-%m-%d' ) AND (SPAM=1 OR bounce_error='Discard')";

$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_events"));
$spams=$ligne["tcount"];


$sql="SELECT COUNT(ID) as tcount FROM smtp_logs 
WHERE $sql_domain AND DATE_FORMAT( time_connect, '%Y-%m-%d' ) = DATE_FORMAT( NOW( ) , '%Y-%m-%d' ) AND (whitelisted=1)";

$sql_log_3=$sql;

$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_events"));
$whitelisted=$ligne["tcount"];
if($whitelisted==null){$whitelisted=0;}
if($spams==null){$spams=0;}

//--------------------------------------------------------------------------------------------------------------------------
$sql="SELECT COUNT(ID) as tcount, DATE_FORMAT( time_sended, '%k' ) as th FROM smtp_logs 
WHERE $sql_domain AND DATE_FORMAT( time_connect, '%Y-%m-%d' ) = DATE_FORMAT( NOW( ) , '%Y-%m-%d' ) GROUP BY th ORDER BY th";

$sql_log_2="$sql\n";



$results=$q->QUERY_SQL($sql,"artica_events");
while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
	$arr3[]="\"{$ligne["tcount"]}\"=>\"{$ligne["th"]}\"";
}

if(!is_array($arr3)){$flow_hour="array()";}else{$flow_hour="array(".implode(",\n",$arr3).");";}



	
$file="<?php\n";
$file=$file."/*\n$sql_log_1\n----------\n$sql_log_3\n----------\n$sql_log_2\n*/\n";
$file=$file."\$today_cam_flow=$day_camember\n";
$file=$file."\$today_cam_top_ten_spammers=$top_10_spammers;\n";
$file=$file."\$whitelisted=array('spam'=>$spams,'whitelisted'=>$whitelisted);\n";
$file=$file."\$flow_hour=$flow_hour;\n";
$file=$file."?>";

write_syslog("Generate mail statistics for $num \"".dirname(__FILE__)."/ressources/logs/ou-stats/$ou/",__FILE__);
@mkdir(dirname(__FILE__)."/ressources/logs");
@mkdir(dirname(__FILE__)."/ressources/logs/ou-stats");
@mkdir(dirname(__FILE__)."/ressources/logs/ou-stats/$ou");
@file_put_contents(dirname(__FILE__)."/ressources/logs/ou-stats/$ou/day.inc",$file);
chmod(dirname(__FILE__)."/ressources/logs/ou-stats/$ou/day.inc",0755);

	
}

function sql_domain($ou){
	
	$ldap=new clladp();
	$domains=$ldap->Hash_domains_table($ou);
	if(!is_array($domains)){return null;}
	while (list ($domain,$nothing) = each ($domains) ){
		$array_domain[]="OR delivery_domain='$domain'";
	}
	
	$sql_domain=implode(" ",$array_domain);
	if(substr($sql_domain,0,2)=="OR"){$sql_domain=substr($sql_domain,2,strlen($sql_domain));}
	$sql_domain="(".trim($sql_domain).")";
	return $sql_domain;	
	
}



?>