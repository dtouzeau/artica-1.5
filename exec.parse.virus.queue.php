<?php
if(posix_getuid()<>0){
	die("Cannot be used in web server mode\n\n");
}
    include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
	include_once(dirname(__FILE__) . '/ressources/class.users.menus.inc');
	include_once(dirname(__FILE__)."/framework/class.unix.inc");

cpulimit();
	
	
$verbose=$argv[1];	
if($verbose=="--verbose"){$_GET["debug"]=true;}
ScanCyrusScan();

function ScanCyrusScan(){
	$dir="/var/log/artica-postfix/antivirus/cyrus-imap";
	$files=DirListClamQueues($dir);
	if(count($files)==0){return null;}
	$users=new usersMenus();
	
	$TaskName="Mailbox Scan";
	$ComputerName=$users->hostname;
	
	while (list ($num, $file) = each ($files) ){
		events("Parsing file $file");
		if(ParseClamFile("$dir/$file",$TaskName,$ComputerName)){
			events("ScanCyrusScan():: Deleting $dir/$file");
			@unlink("$dir/$file");
		}else{
			events("ScanCyrusScan():: skiping $dir/$file because ParseClamFile() return failed");
		}
		
		
	}
}

function ParseClamFile($filename,$TaskName,$ComputerName){
	$basename=basename($filename);
	
	
	if(preg_match("#([0-9\-]+)_([0-9\-]+)#",$basename,$re)){
		$re[2]=str_replace("-",":",$re[2]);
		$zDate=$re[1]." ".$re[2].":00";
		events("Scan date: $zDate");
	}else{
		events("Enable to parse date in filename $basename");
		return false;
	}
	
	$q=new mysql();
	$datas=@file_get_contents($filename);
	$tbl=explode("\n",$datas);
	events("$basename: ". count($tbl). " events");
	
	while (list ($num, $line) = each ($tbl) ){
		if(preg_match("#(.+?):\s+(.+?)\s+FOUND#",$line,$re)){
			if(trim($line)==null){continue;}
			events("$basename: virus {$re[2]} found in {$re[1]}");
			$viruspath=$re[1];
			$virusname=$re[2];
			$zmd5=md5($TaskName.$viruspath.$virusname.$zDate.$ComputerName);
			$sql="INSERT INTO antivirus_events (zDate,TaskName,VirusName,InfectedPath,ComputerName,zmd5)
			VALUES('$zDate','$TaskName','$virusname','$viruspath','$ComputerName','$zmd5');";
			$q->QUERY_SQL($sql,"artica_events");
			if(!$q->ok){
				events("$basename: $line $num, SQL failed \"$sql\" $q->mysql_error");
				return false;
			}
			
			$VIRLIST[]="$virusname in \"$viruspath\"";
			continue;
			}
			if(preg_match("#Scanned directories:\s+([0-9]+)#",$line,$re)){
				$Scanned_directories=$re[1];
			}
			
			if(preg_match("#Scanned files:\s+([0-9]+)#",$line,$re)){
				$Scanned_files=$re[1];
			}

			if(preg_match("#Infected files:\s+([0-9]+)#",$line,$re)){
				$Infected_files=$re[1];
			}	

			if(preg_match("#Data scanned:\s+([0-9]+)#",$line,$re)){
				$Data_scanned=$re[1];
			}
			if(preg_match("#Time:\s+(.+)#",$line,$re)){
				$Time=$re[1];
			}							
			

			
		
		
	}
	
	if($Time==null){
		events("$basename: unable to stat 'Time:' aborting");
		return false;
	}
	
	events("$Infected_files (my array has ". count($VIRLIST)." events ) in $Scanned_files scanned files and $Scanned_directories scanned directories in $Time time");
	
	if($Infected_files==0){return true;}
	
	$text="Antivirus scanner executed on $zDate\n$Infected_files infected files has been found in $Scanned_files files and $Scanned_directories directories\n";
	$text=$text."Data_scanned: $Data_scanned\n\n";
	$text=$text."You will find here viruses found list\n";
	$text=$text. implode("\n",$VIRLIST);
	send_email_events("$TaskName:: $Infected_files infected files found",$text,"system");
	unset($VIRLIST);
	return true;
}




function DirListClamQueues($path){
	$dir_handle = @opendir($path);
	if(!$dir_handle){
		return array();
	}
		$count=0;	
		while ($file = readdir($dir_handle)) {
		  if($file=='.'){continue;}
		  if($file=='..'){continue;}
		  if(!is_file("$path/$file")){continue;}
		   if(!preg_match("#\.scan$#",$file)){continue;}
		  	$array[$file]=$file;
		  }
		if(!is_array($array)){return array();}
		@closedir($dir_handle);
		return $array;
}

function events($text){
		$pid=getmypid();
		$logFile="/var/log/artica-postfix/artica-virus-stats.debug";
		$size=filesize($logFile);
		if($size>1000000){unlink($logFile);}
		$f = @fopen($logFile, 'a');
		if($_GET["debug"]){echo "$pid $text\n";}
		@fwrite($f, "$pid $text\n");
		@fclose($f);	
		}


?>