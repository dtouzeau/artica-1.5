<?php
include_once(dirname(__FILE__).'/ressources/class.crons.inc');



$Enable=GET_INFO("QuarantineAutoCleanEnabled");
$MaxDay=GET_INFO("QuarantineMaxDayToLive");
if($MaxDay==null){$MaxDay=15;}
if($Enable==null){$Enable=1;}
if($Enable<>1){die("Not Enabled, die\n\n");}


echo "\n\n####### Max day to live: $MaxDay days #######\n\n";

$sql="SELECT count(MessageID) AS tcount FROM quarantine WHERE zDate<DATE_SUB(NOW(),INTERVAL $MaxDay DAY)";
$q=new mysql_cron();
$q->database="artica_backup";
$ligne=@mysql_fetch_array($q->QUERY_SQL($sql));
if($ligne["tcount"]<1){
	die("Nothing to do ....\n\n");
}

echo "{$ligne["tcount"]} messages to clean...\n";


$sql="SELECT quarantine.MessageID, quarantine.zDate,orgmails.message_path  FROM quarantine,orgmails WHERE orgmails.MessageID=quarantine.MessageID AND zDate<DATE_SUB(NOW(),INTERVAL $MaxDay DAY)";

$results=$q->QUERY_SQL($sql);
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				$path=$ligne["message_path"];
				$MessageID=$ligne["MessageID"];
				if(file_exists($path)){
					if(@!unlink($path)){
						echo "Unable to delete $path\n";continue;
					}
				}else{
					echo "Unable to locate $MessageID for $path\n";
				}
				$sql="DELETE FROM quarantine WHERE MessageID='$MessageID'";
				$q->QUERY_SQL($sql);
				$sql="DELETE FROM orgmails WHERE MessageID='$MessageID'";
				$q->QUERY_SQL($sql);	
				$sql="DELETE FROM storage_recipients WHERE MessageID='$MessageID'";
				$q->QUERY_SQL($sql);									
				
	}

echo "messages cleaned...\n";

$sql="SELECT count(MessageID) AS tcount FROM quarantine WHERE zDate<DATE_SUB(NOW(),INTERVAL $MaxDay DAY)";
$ligne=@mysql_fetch_array($q->QUERY_SQL($sql));
if($ligne["tcount"]<1){die();}
echo "Delete {$ligne["tcount"]} bad messages";
$sql="DELETE FROM quarantine WHERE zDate<DATE_SUB(NOW(),INTERVAL $MaxDay DAY)";
$q->QUERY_SQL($sql);
echo "\n\n";
die();


?>