<?php
include_once(dirname(__FILE__)."/ressources/class.charset.inc");
include_once(dirname(__FILE__)."/ressources/class.ini.inc");
include_once(dirname(__FILE__)."/ressources/class.mysql.inc");
include_once(dirname(__FILE__)."/ressources/class.users.menus.inc");

if(posix_getuid()<>0){events("Cannot be used in web server mode");die();}
$_GET["CMD_LINE"]=implode(' ',$argv);
$_GET["RESTORE_DIR"]=false;
if(preg_match("#--verbose#",$_GET["CMD_LINE"])){$_GET["DEBUG"]=true;}
if(preg_match("#--directory#",$_GET["CMD_LINE"])){$_GET["RESTORE_DIR"]=true;}
if($argv[1]==null){events("No id specified");die();}
if($argv[1]=='--restore'){restore($argv[2],$argv[3],$argv[4]);die();}

	$id=$argv[1];
	$sql="SELECT * FROM dar_index where filekey='$id'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	
	$ressource=$ligne["mount_md5"];
	$sourcefile=$ligne["filepath"];
	$cmd="/usr/share/artica-postfix/bin/artica-backup --dar-find \"$ressource\" \"$sourcefile\" >/usr/share/artica-postfix/ressources/logs/dar.find.$id.txt";
	events($cmd);
	system($cmd);
	
	chmod("/usr/share/artica-postfix/ressources/logs/dar.find.$id.txt",0755);
	
	



function events($text){
		$pid=getmypid();
		$date=date('Y-m-d H:i:s');
		$logFile="/var/log/artica-postfix/artica-parse-dar.debug";
		$size=@filesize($logFile);
		if($size>5000000){@unlink($logFile);}
		$f = @fopen($logFile, 'a');
		$towrite="$date ".basename(__FILE__)."[$pid]: $text\n";
		if($_GET["DEBUG"]){echo $towrite;}
		@fwrite($f, $towrite);
		@fclose($f);	
		}
		
function restore($id,$target_resource,$db){
$sql="SELECT * FROM dar_index where filekey='$id'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	
	$ressource=$ligne["mount_md5"];
	$sourcefile=$ligne["filepath"];
	if($_GET["RESTORE_DIR"]){$sourcefile=dirname($sourcefile);}
	
	$ini=new Bs_IniHandler("/tmp/restore.$id.ini");
	$ini->set('INFO',"backup_resource",$ressource);
	$ini->set('INFO',"target_resource",$target_resource);
	$ini->set('INFO',"database",$db);
	$ini->set('INFO',"source_path",$sourcefile);
	$ini->saveFile("/tmp/restore.$id.ini");
	
	$ini=new Bs_IniHandler(dirname(__FILE__)."/ressources/logs/exec.dar.find.restore.ini");
	$ini->set("STATUS","progress",15);
	$ini->set("STATUS","text","{Executing}...");
	$ini->saveFile(dirname(__FILE__)."/ressources/logs/exec.dar.find.restore.ini");
	
	$cmd="/usr/share/artica-postfix/bin/artica-backup --dar-restore-path /tmp/restore.$id.ini";
	events($cmd);
	system($cmd);	
	
	
}
?>