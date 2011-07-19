<?php
include_once ("ressources/class.templates.inc");
include_once ("ressources/charts.php");
include_once('ressources/class.mysql.inc');



writequeries('head',__FILE__);
if(isset($_GET["USER_STORAGE_USAGE"])){pie_single_mailbox_user();exit;}
if(isset($_GET["USER_QUARANTINE"])){pie_single_quarantine();exit;}
if(isset($_GET["tempsQuarantine"])){CourbeQuarantine();exit;}
if(isset($_GET["weekMessagesPerDay"])){weekMessagesPerDay();exit;}
if(isset($_GET["QuarMessagesPerDay"])){QuarMessagesPerDay();exit;}
if(isset($_GET["domainsByQuarantine"])){domainsByQuarantine();exit;}
if(isset($_GET["allSpamInfos"])){allSpamInfos();exit;}
if(isset($_GET["AllSpamInfosOU"])){AllSpamInfosOU();}
if(isset($_GET["SpamInfosReasonsOU"])){SpamInfosReasonsOU();}
if(isset($_GET["AllInfectedDomainsOU"])){AllInfectedDomainsOU();}
if(isset($_GET["LinesMessagesDay"])){Courbe_LinesMessagesDay(null,$_GET["LinesMessagesDay"]);}
if(isset($_GET["geoip1"])){pie_TOP_10_GEOIP_TODAY();exit;}
if(isset($_GET["MonthsBlocketType"])){echo pie_month_by_blocked_type();exit;}
if(isset($_GET["MonthCourbeDay"])){echo CourbeMonthDayActions();exit;}
if(isset($_GET["MonthCourbeHour"])){echo CourbeMonthHourActions();exit();}
if(isset($_GET["DayLine"])){echo courbe_DayLine();exit();}
if(isset($_GET["HourLine"])){echo courbe_HourLine();exit;}
if(isset($_GET["kavmilterd"])){echo kavmilterd();exit;}
if(isset($_GET["kav4proxy"])){echo kav4proxy();exit;}
if(isset($_GET["graphfromdir"])){echo graphfromdir();exit;}
if(isset($_GET["FollowHardDisks"])){echo FollowHardDisks();exit;}
if(isset($_GET["ArticaMailBackup"])){echo ArticaMailBackup();exit;}
if(isset($_GET["ORG_MAIL_STAT"])){echo ORG_MAIL_STAT();exit;}



function ArticaMailBackup(){
$textes[]='title';
$donnees[]='title';

$textes[]='Indexed queue '.FormatBytes($_GET["eml"]);
$donnees[]=$_GET["eml"];

$textes[]='Quarantines '.FormatBytes($_GET["quarantines"]);
$donnees[]=$_GET["quarantines"];

$textes[]='backup '.FormatBytes($_GET["backup"]);
$donnees[]=$_GET["backup"];

$textes[]='attachments '.FormatBytes($_GET["attachments"]);
$donnees[]=$_GET["attachments"];

BuildPieChart(array($textes,$donnees));
	
}

function ORG_MAIL_STAT(){
	$G=$_GET["G"];
	$ou=$_GET["ORG_MAIL_STAT"];
	$file="ressources/logs/ou-stats/".strtolower($ou)."/day.inc";
	if(!is_file($file)){return null;}
	include($file);
	if($G==1){$don=$today_cam_flow;}
	if($G==2){$don=$today_cam_top_ten_spammers;}
	if($G==3){ORG_MAIL_STAT_LINE_HOUR();exit;}

	while (list ($num, $ligne) = each ($don) ){
		$textes[]=$ligne;
		$donnees[]=$num;
	}
	BuildPieChart(array($textes,$donnees));
}	
	
function ORG_MAIL_STAT_LINE_HOUR(){
	$G=$_GET["G"];
	$ou=$_GET["ORG_MAIL_STAT"];
	$file="ressources/logs/ou-stats/".strtolower($ou)."/day.inc";
	
	include($file);	
	if($G==3){$don=$flow_hour;}
	

	
	
while (list ($num, $ligne) = each ($don) ){
		$array[$ligne]=$num;
	}
	
ksort($array);	
while (list ($num, $ligne) = each ($array) ){
$textes[]=$num;
		$donnees[]=$ligne;	
	}


	
	BuildGraphCourbe(array($textes,$donnees),array());
	
	
}


function domainsByQuarantine(){
$email=$_GET["domainsByQuarantine"];	
$sql="SELECT count( ID ) as tcount ,mailfrom_domain,filter_action,mail_to  FROM `messages` GROUP BY mailfrom_domain,filter_action,mail_to HAVING filter_action = 'quarantine' AND count( ID )>0 AND mail_to LIKE '%$email%'  
ORDER BY count( ID ) DESC
limit 0,2";
$results=QUERY_SQL($sql);
	
$textes[]='title';
$donnees[]='title';
	while($ligne=sqlite3_fetch_array($results)){	
		$textes[]=$ligne["mailfrom_domain"];
		$donnees[]=$ligne["tcount"];
		}	
		
BuildPieChart(array($textes,$donnees));
}

function kavmilterd(){
	include_once("ressources/class.kavmilterd.inc");
	$kavmilter=new kavmilterd();
	$kavmilter->BuildStatistics();
	if($_GET["kavmilterd"]=='viruses'){
		$textes[]='title';
		$donnees[]='';

	  $donnees[] =$kavmilter->stats_array["messages.protected_messages"];
	  $textes[] = "Protected messages ({$kavmilter->stats_array["messages.protected_messages"]})";
	  
	  
	  $donnees[] = $kavmilter->stats_array["messages.infected_messages"];
	  $textes[] = "Infected messages ({$kavmilter->stats_array["messages.infected_messages"]})";
	  
	  $donnees[] = $kavmilter->stats_array["messages.scanned_messages"];
	  $textes[] = "Scanned messages ({$kavmilter->stats_array["messages.scanned_messages"]})";	  
	  
	  $donnees[] = $kavmilter->stats_array["messages.suspicious_messages"];
	  $textes[] = "Suspicious messages ({$kavmilter->stats_array["messages.suspicious_messages"]})";		  
	  
	  $donnees[] = $kavmilter->stats_array["messages.error_messages"];
	  $textes[] = "Error messages ({$kavmilter->stats_array["messages.error_messages"]})";		  
	  
	  
	  
	BuildPieChart(array($textes,$donnees),array(),-5);	
	exit;
	}
	
if($_GET["kavmilterd"]=='perf'){
		$textes[]='title';
		$donnees[]='';

	  
	  
	  if(preg_match('#(.+?)\%#',$kavmilter->stats_array["resources.cpu_usage_user"],$re)){
	  	$kavmilter->stats_array["resources.cpu_usage_user"]=$re[1];
	  }
	  
	  if(preg_match('#(.+?)\%#',$kavmilter->stats_array["resources.cpu_usage_system"],$re)){
	  	$kavmilter->stats_array["resources.cpu_usage_system"]=$re[1];
	  }	  
	  
	  $textes[] = "Cpu user ({$kavmilter->stats_array["resources.cpu_usage_user"]}%)";
	  $donnees[] =$kavmilter->stats_array["resources.cpu_usage_user"];
	  
	  $donnees[] = $kavmilter->stats_array["resources.cpu_usage_system"];
	  $textes[] = "Cpu System ({$kavmilter->stats_array["resources.cpu_usage_system"]}%)";
	  
		  
	  
	  
	  
	BuildPieChart(array($textes,$donnees),array(),-5);	
	exit;
	}	
	


	
}

function graphfromdir(){
	$max=$_GET["max"];
	include_once("ressources/class.sockets.inc");
	$sock=new sockets();
	$value=$sock->getfile("foldersize:{$_GET["graphfromdir"]}",$_GET["hostname"]);
	if(preg_match('#([0-9]+)#',$value,$re)){$value=$re[1];}
		$textes[]='title';
		$donnees[0]='';	
		
		$donnees[1] = trim($value);
		$textes[]=trim($value) . " MB";
	
		$donnees[2]=$max;
		$textes[]="$max MB Free";
		BuildPieChart(array($textes,$donnees));	
		
	
}


function kav4proxy(){
	include_once("ressources/class.kav4proxy.inc");
	$kav=new kav4proxy();
	$hash=$kav->BuildStatistics();
	if($_GET["kav4proxy"]=='viruses'){
		$textes[]='title';
		$donnees[]='';
		
  	  $donnees[] = $hash["total_requests"];
	  $textes[] = "Total uris ({$hash["total_requests"]})";	  		

	  $donnees[] =$hash["infected_requests"];
	  $textes[] = "infected uris ({$hash["infected_requests"]})";
	  
	  
	  $donnees[] = $hash["protected_requests"];
	  $textes[] = "protected uris ({$hash["protected_requests"]})";
	  
  	  $donnees[] = $hash["error_requests"];
	  $textes[] = "errors uris ({$hash["error_requests"]})";


	  
	  
	  
	BuildPieChart(array($textes,$donnees),array(),-5);	
	exit;
	}
	
if($_GET["kav4proxy"]=='perf'){
		$textes[]='title';
		$donnees[]='';
		
	   $donnees[] = $hash["processed_traffic"];
	   $textes[] = "Processed traffic ({$hash["processed_traffic"]})";		

	   $donnees[] = $hash["clean_traffic"];
	   $textes[] = "Clean traffic ({$hash["clean_traffic"]})";		  
	   
	   $donnees[] = $hash["infected_traffic"];
	   $textes[] = "infected traffic ({$hash["infected_traffic"]})";	   
	    
	 	  
	BuildPieChart(array($textes,$donnees),array(),-5);	
	exit;
	}	
	


	
}


function allSpamInfos(){
	$sql="SELECT count(ID) as tcount FROM `messages` WHERE filter_action='send'";
	$ligne=sqlite3_fetch_array(QUERY_SQL($sql));
	$all_sended=$ligne["tcount"];
	$sql="SELECT count(ID) as tcount FROM `messages`";
	$ligne=sqlite3_fetch_array(QUERY_SQL($sql));
	$all=$ligne["tcount"];
	$others=$all-$all_sended;
$textes[]='';
$donnees[]='';	
	$textes[]="Sended";
	$textes[]="detected";
	
	$donnees[]=$all_sended;
	$donnees[]=$others;
	
	BuildPieChart(array($textes,$donnees));
	
}

function CourbeMonthDayActions(){
	$action=$_GET["MonthCourbeDay"];
	
$sql="SELECT count(ID) as tcount,strftime('%Y-%m',received_date),strftime('%d',received_date) as tdate, filter_action  FROM messages GROUP BY 
	filter_action,strftime('%Y-%m',received_date),tdate HAVING strftime('%Y-%m',received_date)='" . date('Y-m') ."' AND filter_action='$action' ORDER BY tdate";	

$textes[]='title';
$donnees[]='nb mails/hour';
	
	$results=QUERY_SQL($sql);
	while($ligne=sqlite3_fetch_array($results)){	
		$textes[]=$ligne["tdate"];
		$donnees[]=$ligne["tcount"];
		}
		
	$links=array("url"=>"javascript:LoadAjax('graph3','system_statistics.php?BuildByHour=$action',_category_)","target"=>"javascript");		
	BuildGraphCourbe(array($textes,$donnees),$links);
}

function CourbeMonthHourActions(){
	$action=$_GET["MonthCourbeHour"];
	$date=$_GET["date"];
	
	
$sql="SELECT count(ID) as tcount,strftime('%Y-%m-%d',received_date),strftime('%H',received_date) as tdate, filter_action  FROM messages GROUP BY 
	filter_action,strftime('%Y-%m-%d',received_date),tdate HAVING strftime('%Y-%m-%d',received_date)='" . date('Y-m'). "-$date' AND filter_action='$action' ORDER BY tdate";	

$textes[]='title';
$donnees[]='nb mails/hour';
	
	$results=QUERY_SQL($sql);
	while($ligne=sqlite3_fetch_array($results)){	
		$textes[]=$ligne["tdate"];
		$donnees[]=$ligne["tcount"];
		}
		
	//$links=array("url"=>"javascript:LoadAjax('graph3','system_statistics.php?BuildByHour=$action',_category_)","target"=>"javascript");		
	BuildGraphCourbe(array($textes,$donnees),$links);	
	
}

function Courbe_LinesMessagesDay($user=null,$ou=null){
	if($ou<>null){$q1="AND ou='$ou'";$addfield1=", ou";$group1=", ou";}
	$today=date('Y-m-d');
	$sql="SELECT COUNT(zDate) as tcount ,
		DATE_FORMAT('%Y-%m-%d',received_date) as tday ,
		DATE_FORMAT('%H',received_date) as thour
		$addfield1
		FROM messages
			group by DATE_FORMAT('%Y-%m-%d',received_date),
			DATE_FORMAT('%H',received_date) 
			$group1
HAVING tday=DATE_FORMAT('%Y-%m-%d','now') $q1
ORDER BY tday desc limit 0,24";
	
	
$textes[]='title';
$donnees[]='nb mails/hour';
	
	$results=QUERY_SQL($sql);
	while($ligne=sqlite3_fetch_array($results)){	
		$textes[]=$ligne["thour"];
		$donnees[]=$ligne["tcount"];
		}	
	
	$links=array("url"=>"javascript:MyHref('system_statistics.php?LinesMessagesHour=yes',_category_)","target"=>"javascript");	
	BuildLittleGraphCourbe(array($textes,$donnees),$links);
}

function users_LinesMessagesDay($user=null){
	
	$today=date('Y-m-d');
	$sql="SELECT COUNT(zDate) as tcount ,
		DATE_FORMAT('%Y-%m-%d',received_date) as tday ,
		DATE_FORMAT('%H',received_date) as thour
		$addfield1
		FROM messages
			group by DATE_FORMAT('%Y-%m-%d',received_date),
			DATE_FORMAT('%H',received_date) 
			$group1
HAVING tday=DATE_FORMAT('%Y-%m-%d','now') $q1
ORDER BY tday desc limit 0,24";
	
	
$textes[]='title';
$donnees[]='nb mails/hour';
	
	$results=QUERY_SQL($sql);
	while($ligne=sqlite3_fetch_array($results)){	
		$textes[]=$ligne["thour"];
		$donnees[]=$ligne["tcount"];
		}	
	
	$links=array("url"=>"javascript:MyHref('system_statistics.php?LinesMessagesHour=yes',_category_)","target"=>"javascript");	
	BuildLittleGraphCourbe(array($textes,$donnees),$links);
}


function courbe_DayLine(){
	$day=$_GET["DayLine"];
$sql="SELECT COUNT(zDate) as tcount ,strftime('%Y-%m-%d',received_date) as tday ,strftime('%H',received_date) as thour FROM messages group by tday,thour	HAVING tday='$day'
ORDER BY tday desc limit 0,24";	
	$results=QUERY_SQL($sql);
	while($ligne=sqlite3_fetch_array($results)){	
		$textes[]=$ligne["thour"];
		$donnees[]=$ligne["tcount"];
		}	

	$links=array("url"=>"javascript:LoadAjax('graph2','system_statistics.php?report_minutes=$day',_category_)","target"=>"javascript");		
	BuildGraphCourbe(array($textes,$donnees),$links);

}
function courbe_HourLine(){
	$day=$_GET["HourLine"];
	$hour=$_GET["hour"];
$sql="SELECT COUNT(zDate) as tcount ,strftime('%Y-%m-%d',received_date) as tday ,strftime('%H',received_date) as thour,
strftime('%M',received_date) as tmin FROM messages group by tday,thour,tmin	HAVING tday='$day' AND thour='$hour'
ORDER BY tmin limit 0,60";	
	$results=QUERY_SQL($sql);
	while($ligne=sqlite3_fetch_array($results)){	
		$textes[]=$ligne["tmin"];
		$donnees[]=$ligne["tcount"];
		}	

	//$links=array("url"=>"javascript:LoadAjax('graph3','system_statistics.php?DayLineHour=$day',_category_)","target"=>"javascript");		
	BuildGraphCourbe(array($textes,$donnees),$links);

}


function pie_TOP_10_GEOIP_TODAY(){
	$mysql=new MySqlQueries();
	if($_GET["geoip1"]=="admin"){
		$results=$mysql->today_OU_GEOIP();
	}
$textes[]='title';
$donnees[]='nb mails/hour';	
while($ligne=sqlite3_fetch_array($results)){	
		$textes[]=$ligne["GeoCountry"];
		$donnees[]=$ligne["tcount"];
		
		}	
		$links=array("url"=>"javascript:LoadAjax('GraphDatas','users.index.php?GeoipCity=yes&today=yes',_category_)","target"=>"javascript");
		//javascript:display_info( _col_, _row_, _value_, _category_, _series_, 'Hello World!' )" target='javascript'
		
			BuildPieChart(array($textes,$donnees),$links);
}


function AllInfectedDomainsOU(){
	
	
 $sql="SELECT COUNT( ID ) AS tcount,mailfrom_domain,filter_action,ou from messages
 GROUP BY mailfrom_domain,filter_action,ou
HAVING `filter_action` = 'infected'  
AND ou = '{$_GET["AllInfectedDomainsOU"]}'  ORDER BY COUNT( ID ) DESC LIMIT 0,10";
 
 
		$results=QUERY_SQL($sql);
$textes[]='';
$donnees[]='';
	while($ligne=sqlite3_fetch_array($results)){
		if($ligne["mailfrom_domain"]<>null){
			if($ligne["mailfrom_domain"]<>"none"){
				$textes[]=$ligne["mailfrom_domain"];
				$donnees[]=$ligne["tcount"];
			}
		}
		}	
BuildPieChart(array($textes,$donnees));	
 
	
}

function AllSpamInfosOU(){
	$sql="SELECT count(ID) as tcount FROM `messages` WHERE filter_action='send' and ou='{$_GET["AllSpamInfosOU"]}'";
	$ligne=sqlite3_fetch_array(QUERY_SQL($sql));
	$all_sended=$ligne["tcount"];
	$sql="SELECT count(ID) as tcount FROM `messages` WHERE ou='{$_GET["AllSpamInfosOU"]}'";
	$ligne=sqlite3_fetch_array(QUERY_SQL($sql));
	$all=$ligne["tcount"];
	$others=$all-$all_sended;
$textes[]='';
$donnees[]='';	
	$textes[]="Sended";
	$textes[]="detected";
	
	$donnees[]=$all_sended;
	$donnees[]=$others;
	
	BuildPieChart(array($textes,$donnees));
	
}

function SpamInfosReasonsOU(){
	
	$sql="SELECT COUNT( SpamInfos ) AS tcount, SpamInfos,ou
		FROM messages
		GROUP BY SpamInfos
		HAVING ou = '{$_GET["SpamInfosReasonsOU"]}'
		ORDER BY COUNT( ID ) DESC 
		LIMIT 0 , 10";
		$results=QUERY_SQL($sql);
$textes[]='';
$donnees[]='';
	while($ligne=sqlite3_fetch_array($results)){
		if($ligne["SpamInfos"]<>null){
			if($ligne["SpamInfos"]<>"none"){
				$textes[]=$ligne["SpamInfos"];
				$donnees[]=$ligne["tcount"];
			}
		}
		}	
BuildPieChart(array($textes,$donnees));		
}


function weekMessagesPerDay(){

$email=$_GET["weekMessagesPerDay"];
$sql="
SELECT count( ID ) as tcount,
DAY( zDate ) AS tday
FROM `messages`
WHERE WEEK( zDate ) = WEEK( NOW( ) )
AND YEAR( zDate ) = YEAR( NOW( ) )
AND mail_to='$email'
GROUP BY DAY( zDate )
LIMIT 0 ,60";
	$results=QUERY_SQL($sql);
	
$textes[]='';
$donnees[]='';
	while($ligne=sqlite3_fetch_array($results)){	
		$textes[]=$ligne["tday"];
		$donnees[]=$ligne["tcount"];
		}
BuildGraphCourbe(array($textes,$donnees));	
}
function QuarMessagesPerDay(){

$email=$_GET["QuarMessagesPerDay"];
$sql="
SELECT count( ID ) as tcount,
DAY( zDate ) AS tday
FROM `messages`
WHERE WEEK( zDate ) = WEEK( NOW( ) )
AND YEAR( zDate ) = YEAR( NOW( ) )
AND mail_to='$email'
AND filter_action='quarantine'
GROUP BY DAY( zDate )
LIMIT 0 ,60";
	$results=QUERY_SQL($sql);
	
$textes[]='title';
$donnees[]='title';
	while($ligne=sqlite3_fetch_array($results)){	
		$textes[]=$ligne["tday"];
		$donnees[]=$ligne["tcount"];
		
	}


BuildGraphCourbe(array($textes,$donnees));
	
}


function CourbeQuarantine(){
	$email=$_GET["tempsQuarantine"];

	
	
	
	$sql="SELECT strftime('%m',zDate) as tmonth,strftime('%Y',zDate),COUNT(ID) as tcount FROM messages 
	WHERE mail_to LIKE '%$email%' AND strftime('%m',zDate)='" . date('m') . "' AND strftime('%Y',zDate)='" . date('Y') ."' AND  quarantine=1 AND Deleted IS NULL
	GROUP BY strftime('%m',zDate),strftime('%Y',zDate)
	ORDER BY strftime('%Y',zDate), strftime('%m',zDate)";
	$results=QUERY_SQL($sql);
	
$textes[]='title';
$donnees[]='';
	while($ligne=sqlite3_fetch_array($results)){	
		$textes[]=$ligne["tmonth"];
		$donnees[]=$ligne["tcount"];
		
	}
	
BuildGraphCourbe(array($textes,$donnees));

	
	
	
}
function BuildGraphCourbe($arrayDatas,$links=array()){
$chart[ 'axis_category' ] = array ( 'size'=>10, 'color'=>"000000", 'alpha'=>50, 'font'=>"arial", 'bold'=>true, 'skip'=>0 ,'orientation'=>"horizontal",'prefix'=>"day" ); 
$chart[ 'axis_ticks' ] = array ( 'value_ticks'=>true, 'category_ticks'=>true, 'major_thickness'=>2, 'minor_thickness'=>1, 'minor_count'=>1, 'major_color'=>"000000", 'minor_color'=>"ffffff" ,'position'=>"inside" );
$chart[ 'axis_value' ] = array (  'min'=>0, 'font'=>"arial", 'bold'=>true, 'size'=>10, 'color'=>"000000", 'alpha'=>50, 'steps'=>6, 'prefix'=>"", 'suffix'=>"", 'decimals'=>0, 'separator'=>"", 'show_min'=>true );
$chart[ 'chart_border' ] = array ( 'color'=>"005447", 'top_thickness'=>0, 'bottom_thickness'=>1, 'left_thickness'=>1, 'right_thickness'=>0 );
$chart[ 'chart_data' ] =$arrayDatas;
$chart[ 'chart_grid_h' ] = array ( 'alpha'=>10, 'color'=>"000000", 'thickness'=>1, 'type'=>"solid" );
$chart[ 'chart_grid_v' ] = array ( 'alpha'=>10, 'color'=>"000000", 'thickness'=>1, 'type'=>"solid" );
$chart[ 'chart_pref' ] = array ( 'line_thickness'=>1, 'point_shape'=>"none", 'fill_shape'=>false );
//$chart[ 'chart_rect' ] = array ( 'x'=>40, 'y'=>25, 'width'=>335, 'height'=>200, 'positive_color'=>"000000", 'positive_alpha'=>30, 'negative_color'=>"ff0000",  'negative_alpha'=>10 );
$chart[ 'chart_type' ] = "Line";
$chart[ 'chart_value' ] = array ( 'prefix'=>"", 'suffix'=>" emails", 'decimals'=>0, 'separator'=>"", 'position'=>"cursor", 'hide_zero'=>true, 'as_percentage'=>false, 'font'=>"arial", 'bold'=>true, 'size'=>12, 'color'=>"000000", 'alpha'=>75 );

//$chart[ 'draw' ] = array ( array ( 'type'=>"text", 'color'=>"ffffff", 'alpha'=>15, 'font'=>"arial", 'rotation'=>-90, 'bold'=>true, 'size'=>50, 'x'=>-10, 'y'=>348, 'width'=>300, 'height'=>150, 'text'=>"hertz", 'h_align'=>"center", 'v_align'=>"top" ),
    //                       array ( 'type'=>"text", 'color'=>"000000", 'alpha'=>15, 'font'=>"arial", 'rotation'=>0, 'bold'=>true, 'size'=>60, 'x'=>0, 'y'=>0, 'width'=>320, 'height'=>300, 'text'=>"output", 'h_align'=>"left", 'v_align'=>"bottom" ) );
$chart["link_data"]=$links;
$chart[ 'legend_rect' ] = array ( 'x'=>-100, 'y'=>-100, 'width'=>10, 'height'=>10, 'margin'=>10 ); 

$chart[ 'series_color' ] = array ( "77bb11", "cc5511" ); 
$chart [ 'chart_transition' ] = array ('type'=>'dissolve','delay'=>0,'duration'=>0.5,'order'=>'all');	

		

SendChartData ( $chart );
}
function BuildLittleGraphCourbe($arrayDatas,$links=array()){
$chart[ 'axis_category' ] = array ( 'size'=>8, 'color'=>"000000", 'alpha'=>50, 'font'=>"arial", 'bold'=>true, 'skip'=>0 ,'orientation'=>"horizontal",'prefix'=>"day" ); 
//$chart[ 'axis_ticks' ] = array ( 'value_ticks'=>true, 'category_ticks'=>true, 'major_thickness'=>2, 'minor_thickness'=>1, 'minor_count'=>1, 'major_color'=>"000000", 'minor_color'=>"ffffff" ,'position'=>"inside" );
$chart[ 'axis_value' ] = array (  'min'=>0, 'font'=>"arial", 'bold'=>true, 'size'=>10, 'color'=>"000000", 'alpha'=>50, 'steps'=>6, 'prefix'=>"", 'suffix'=>"", 'decimals'=>0, 'separator'=>"", 'show_min'=>true );
$chart[ 'chart_border' ] = array ( 'color'=>"005447", 'top_thickness'=>0, 'bottom_thickness'=>1, 'left_thickness'=>1, 'right_thickness'=>0 );
$chart[ 'chart_data' ] =$arrayDatas;
$chart[ 'chart_grid_h' ] = array ( 'alpha'=>10, 'color'=>"000000", 'thickness'=>1, 'type'=>"solid" );
$chart[ 'chart_grid_v' ] = array ( 'alpha'=>10, 'color'=>"000000", 'thickness'=>1, 'type'=>"solid" );
$chart[ 'chart_pref' ] = array ( 'line_thickness'=>1, 'point_shape'=>"none", 'fill_shape'=>false );
$chart[ 'chart_type' ] = "Line";
$chart[ 'chart_value' ] = array ( 'prefix'=>"", 'suffix'=>" emails", 'decimals'=>0, 'separator'=>"", 'position'=>"cursor", 'hide_zero'=>true, 'as_percentage'=>false, 'font'=>"arial", 'bold'=>true, 'size'=>12, 'color'=>"000000", 'alpha'=>75 );
$chart["link_data"]=$links;
//$chart[ 'draw' ] = array ( array ( 'text'=>$title));
//$chart[ 'draw' ] = array ( array ( 'transition'=>"dissolve", 'delay'=>0, 'duration'=>.5, 'type'=>"text", 'color'=>"000000", 'alpha'=>8, 'font'=>"Arial", 'rotation'=>0, 'bold'=>true, 'size'=>48, 'x'=>8, 'y'=>7, 'width'=>400, 'height'=>75, 'text'=>"annual report", 'h_align'=>"center", 'v_align'=>"bottom" ) );    

$chart[ 'legend_rect' ] = array ( 'x'=>-100, 'y'=>-100, 'width'=>0, 'height'=>0, 'margin'=>0 ); 
$chart[ 'series_color' ] = array ( "77bb11", "cc5511" ); 
$chart [ 'chart_transition' ] = array ('type'=>'dissolve','delay'=>0,'duration'=>0.5,'order'=>'all');	
$chart [ 'live_update' ] = array (   'url'    => basename($_SERVER['SCRIPT_FILENAME']) . '?' . $_SERVER['QUERY_STRING'],
                                     'delay'  => 20,
                                     'fail'   =>  false
                                ); 
		

SendChartData ( $chart );
}


function BuildPieChart($array,$link_array_datas=array(),$legend_rect=-5){
$chart [ 'axis_value' ] = array ( 'bold'=>false,'size'=>8);

		$chart [ 'axis_category' ] = array (   'skip' =>  1, 'font'  =>  "Tahoma",  'bold'         =>  true,  'size'         =>  8, 'color'        =>  "000000", 'alpha'        =>  75,'orientation'  =>  "horizontal");
		$chart["legend_label"]=array('bullet'=>'square' ,'font'=>'arial','bold'=>'true','size'=>'10','color'=>'black','alpha'=>'100');		
		$chart [ 'chart_type' ] = "3d pie";
		$chart[ 'chart_pref' ] = array ( 'line_thickness'=>2, 'point_shape'=>"none",'fill_shape'=>true );
		$chart [ 'chart_data' ] =$array;
		//$chart [ 'live_update' ] =  array ( 'url'=>"statistiques-generator.php?articles-jour=yes", 'delay'=>30 );
		$chart ['series_color']=array('005447','8B2D2D','ddaa41','A4781E','88dd11','634812','4e62dd','ff8811','4d4d4d','5a4b6e','C60000','FF3333','FF0099','DFDF00');
		if(is_array($link_array_datas)){
		$chart["link_data"]=$link_array_datas;
		//array("url"=>"http://toto.com","target"=>"_new");
		}
		$chart["series_explode"]=array(30,15,10,10,10,10,10);
		$chart["chart_value"]=array('color'=>'ffffff', 'alpha'=>'90', 'font'=>'arial', 'bold'=>'true','size'=>'10','position'=>'inside','prefix'=>'','suffix'=>'','decimals'=>'0','separator'=>'');
		$chart [ 'legend_rect' ] = array (   'x' =>  $legend_rect,'y' =>  $legend_rect, 'height' =>  10,  'margin' =>  5,'fill_color'  =>  "FFFFFF",'fill_alpha'      =>  100, 'line_color'      =>  "FFFFFF",  'line_alpha'      =>  100,  'line_thickness'  =>  2); 
				
		
		
		
		$chart [ 'chart_transition' ] = array (
		 'type'=>'dissolve',
		 'delay'=>0,
		  'duration'=>0.5,
		   'order'=>'all');		
		SendChartData ( $chart );	
}
function BuildPieBigChart($array,$array_value_text=array(),$link_array_datas=array(),$legend_rect=-5){
	
$uri=substr($_SERVER["REQUEST_URI"],1,strlen($_SERVER["REQUEST_URI"]));
$legend_rectx=50;
$legend_recty=0;



$chart [ 'axis_value' ] = array ( 'bold'=>false,'size'=>8);

		$chart [ 'axis_category' ] = array (   'skip' =>  1, 'font'  =>  "Tahoma",  'bold'         =>  true,  'size'         =>  8, 'color'        =>  "000000", 'alpha'        =>  75,'orientation'  =>  "horizontal");
		$chart["legend_label"]=array('bullet'=>'square' ,'font'=>'arial','bold'=>'true','size'=>'10','color'=>'black','alpha'=>'100');		
		$chart [ 'chart_type' ] = "3d pie";
		$chart[ 'chart_pref' ] = array ( 'line_thickness'=>2, 'point_shape'=>"none",'fill_shape'=>true );
		$chart [ 'chart_data' ] =$array;
		$chart [ 'live_update' ] =  array ( 'url'=>$uri, 'delay'=>20 );
		$chart ['series_color']=array('005447','8B2D2D','ddaa41','A4781E','88dd11','634812','4e62dd','ff8811','4d4d4d','5a4b6e','C60000','FF3333','FF0099','DFDF00');
		if(is_array($link_array_datas)){
		$chart["link_data"]=$link_array_datas;
		//array("url"=>"http://toto.com","target"=>"_new");
		}
		$chart["series_explode"]=array(35,20,15,10,10,10,10);
		$chart["chart_value"]=array('color'=>'ffffff', 'alpha'=>'90', 
			'font'=>'arial', 'bold'=>'true','size'=>'10','position'=>'inside','prefix'=>'','suffix'=>'','decimals'=>'0','separator'=>'');
		$chart [ 'legend_rect' ] = array (   
		'x' =>  $legend_rectx,
		'y' =>  $legend_recty, 'height' =>  10,  'margin' =>  5,'fill_color'  =>  "FFFFFF",'fill_alpha'      =>  0, 'line_color'      =>  "FFFFFF",  'line_alpha'      =>  0,  'line_thickness'  =>  2); 
				
		$chart [ 'chart_value_text' ] =$array_value_text; 
		
		
		$chart [ 'chart_transition' ] = array (
		 'type'=>'dissolve',
		 'delay'=>0,
		  'duration'=>0.5,
		   'order'=>'all');		
		SendChartData ( $chart );	
}

function pie_month_by_blocked_type(){
	$sql="SELECT count(ID) as tcount,strftime('%Y-%m',received_date),filter_action  FROM messages GROUP BY 
	filter_action,strftime('%Y-%m',received_date) HAVING strftime('%Y-%m',received_date)='" . date('Y-m') ."' AND filter_action!='send' ORDER BY tcount DESC";
$results=QUERY_SQL($sql);
	
$textes[]='title';
$donnees[]='';
	while($ligne=sqlite3_fetch_array($results)){	
		$textes[]=$ligne["filter_action"];
		$donnees[]=$ligne["tcount"];
		}	
		
		
	$links=array("url"=>"javascript:LoadAjax('graph2','system_statistics.php?MonthCourbeDay=yes',_category_)","target"=>"javascript");	

	BuildPieChart(array($textes,$donnees),$links);
	
}

function FollowHardDisks(){
	include_once('ressources/class.harddrive.inc');
	$hard=new harddrive();
	$hard->BuildSizes();
$textes[]='title';
$donnees[]='';
if(is_array($hard->main_array["folders_list"])){reset($hard->main_array["folders_list"]);}
while (list ($num, $ligne) = each ($hard->main_array["folders_list"])){
	$tsize=$hard->main_array["folders_size"][$ligne]/1000;
	if($tsize>1000){$tsize=round($tsize/1000,2);$tsize=$tsize.' Go';}else{$tsize=$tsize.' Mb';}
	
	$textes[]="$ligne: $tsize";
	$donnees[]=$hard->main_array["folders_size"][$ligne]/1000;
	$array_value_text[]=($hard->main_array["folders_size"][$ligne]/1000) . " Mb";
	
}
$total=$hard->main_array["sum"]["total"]/1000;
if($total>1000){$total=round($total /1000,2) . " Go";}else{$total=$total . " mb";}

$textes[]="Total: " .$total;

$donnees[]=$hard->main_array["sum"]["total"]/1000;

$links=array("url"=>"javascript:LoadAjax('folderslist','system.harddisk.php?follow=yes',_category_)","target"=>"javascript");


	BuildPieBigChart(array($textes,$donnees),$array_value_text,$links);
}

function pie_single_quarantine(){

$tpl=new Templates();	
$USER_QUARANTINE=$_GET["USER_QUARANTINE"];
$SAFE=$_GET["SAFE"];


$title=$tpl->_ENGINE_parse_body("{quarantines_graph}");

	$date=date('Y-m-d');	
$textes=array();
$donnees=array();
$zlabel=array();
$date=date('Y-m-d');


$textes[]='title';
$donnees[]='';
	  $donnees[] =$SAFE;
	  $textes[] = "Sended";
	  
	  
	  $donnees[] = $USER_QUARANTINE;
	  $textes[] = "Quarantine";	

$chart [ 'axis_value' ] = array ( 'bold'=>false,'size'=>8);
		$chart [ 'axis_category' ] = array (   'skip' =>  1, 'font'         =>  "Tahoma",  'bold'         =>  true,  'size'         =>  8, 'color'        =>  "000000", 'alpha'        =>  75,'orientation'  =>  "horizontal");
		$chart[ 'legend_label' ] = array ('layout'=>'vertical','bullet'=>'square','font'=>'Arial','bold'=>true,'size'=>11,'color'=>"000000",'alpha'=>90); 			
		$chart [ 'chart_type' ] = "3d pie";
		$chart[ 'chart_pref' ] = array ( 'line_thickness'=>2, 'point_shape'=>"none",'fill_shape'=>true );
		$chart [ 'chart_data' ] = array($textes,$donnees,);
		
		//$chart [ 'live_update' ] =  array ( 'url'=>"statistiques-generator.php?articles-jour=yes", 'delay'=>30 );
		$chart ['series_color']=array('005447','009D86','ddaa41','A4781E','88dd11','634812','4e62dd','ff8811','4d4d4d','5a4b6e','C60000','FF3333','FF0099','DFDF00');
		$chart["series_explode"]=array(10,10,10);
		$chart["chart_value"]=array('color'=>'ffffff', 'alpha'=>'90', 'font'=>'arial', 'bold'=>'true','size'=>'10','position'=>'inside','prefix'=>'','suffix'=>'','decimals'=>'0','separator'=>'');
				
				
		$chart["legend_label"]=array(
		'layout'=>'vertical',
		 'bullet'=>'circle' ,
		 'font'=>'arial',
		 'bold'=>'true',
		 'size'=>'11',
		 'color'=>'005447',
		 'alpha'=>'85');
		
		
		$chart [ 'chart_transition' ] = array (
		 'type'=>'dissolve',
		 'delay'=>0,
		  'duration'=>0.5,
		   'order'=>'all');		
		SendChartData ( $chart );	
	}


function pie_single_mailbox_user(){

$tpl=new Templates();	

$USER_STORAGE_USAGE=$_GET["USER_STORAGE_USAGE"];
$USER_STORAGE_LIMIT=$_GET["STORAGE_LIMIT"];
$FREE=$_GET["FREE"];
writelogs("USER_STORAGE_USAGE=$USER_STORAGE_USAGE",__FUNCTION__,__FILE__);
writelogs("STORAGE_LIMIT=$USER_STORAGE_LIMIT",__FUNCTION__,__FILE__);

if($USER_STORAGE_LIMIT==null){
	$USER_STORAGE_LIMIT=1000000;
	$USER_STORAGE_USAGE=0;
	$FREE=$USER_STORAGE_LIMIT;
}


$USER_STORAGE_RESTANT=$USER_STORAGE_LIMIT-$USER_STORAGE_USAGE;
if($USER_STORAGE_RESTANT>1){
	$reste=round(($USER_STORAGE_RESTANT/1024));
	$data = array($USER_STORAGE_USAGE,$USER_STORAGE_RESTANT);
}else{$data=array($USER_STORAGE_USAGE);}

$title=$tpl->_ENGINE_parse_body("{your mailbox usage}\n ($reste mb free)");

writelogs("USER_STORAGE_USAGE=$USER_STORAGE_USAGE - USER_STORAGE_LIMIT=$USER_STORAGE_LIMIT FREE=$FREE",__FUNCTION__,__FILE__);


	$date=date('Y-m-d');	
$textes=array();
$donnees=array();
$zlabel=array();
$date=date('Y-m-d');


	 $textes[]='title';
	 $donnees[]='';
	  $donnees[] =$FREE;
	  $textes[] = "Free";
	  
	  
	  $donnees[] = $USER_STORAGE_USAGE;
	  $textes[] = "used";	
	BuildPieChart(array($textes,$donnees));
	}

function JpGraph(){
$graph = new PieGraph(230,200,"auto");
//$graph->SetShadow();

// Set A title for the plot
$graph->title->Set($tpl->_ENGINE_parse_body("{your mailbox usage}\n ($kb mb free)"));
$graph->title->SetFont(FF_FONT1,FS_BOLD,12); 
$graph->title->SetColor("black");
$p1 = new PiePlot($data);
$p1->SetSliceColors(array("red","green","yellow","green"));
$p1->SetTheme("water");

$p1->value->SetFont(FF_FONT1,FS_NORMAL,10);
$p1->Explode(array(0,15,15,25,15));
//$p1->SetLabelType(PIE_VALUE_PER);
//$lbl = array("{used} $USER_STORAGE_USAGE","{free}");
//$p1->SetLabels($lbl); 

$graph->Add($p1);
//$graph->img->SetAntiAliasing();
$graph->Stroke();	
		
	
}
?>