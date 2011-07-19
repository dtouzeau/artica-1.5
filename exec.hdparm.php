<?php
include_once(dirname(__FILE__)."/ressources/class.sockets.inc");
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");

$unix=new unix();
$GLOBALS["FIND_DF"]=$unix->find_program("df");
$GLOBALS["FIND_HDPARM"]=$unix->find_program("hdparm");

launch_tests();

function GetDisks(){
	if(!is_file($GLOBALS["FIND_DF"])){
		writelogs("Unable to stat 'df'",__FUNCTION__,__FILE__,__LINE__);
		die();
	}
	exec($GLOBALS["FIND_DF"],$results);
	while (list ($index, $line) = each ($results) ){
		if(preg_match("#\/(.+?)[0-9]+\s+.+?\%#",$line,$re)){
			$a["/{$re[1]}"]="/{$re[1]}";
		}
	}
	
	return $a;
	
}


function launch_tests(){
	$datafile="/etc/artica-postfix/settings/Daemons/HdparmInfos";
	writelogs("Testing hard drives ($datafile)",__FUNCTION__,__FILE__,__LINE__);
	$timenum=file_time_min($datafile);
	if(is_file($datafile)){if(file_time_min($datafile)<60){
		writelogs("{$timenum}Mn executed, waiting 60Mn",__FUNCTION__,__FILE__,__LINE__);
		die();
	}}	
	if($GLOBALS["FIND_HDPARM"]==null){
		writelogs("Unable to stat 'hdparm'",__FUNCTION__,__FILE__,__LINE__);
		die();}
	$disks=GetDisks();
	if(!is_array($disks)){return null;}
	while (list ($index, $line) = each ($disks) ){
		unset($results);
		
		exec("{$GLOBALS["FIND_HDPARM"]} -t $index",$results);
		while (list ($num, $line_result) = each ($results) ){
			if(preg_match("#=\s+([0-9\.]+)\s+MB\/sec$#",$line_result,$re)){
				
				if(preg_match("#.+?\/(.+)$#",$index,$ri)){
					writelogs("testing disk {$ri[1]}:{$re[1]}MB/sec...",__FUNCTION__,__FILE__,__LINE__);
						$array[$ri[1]]=$re[1];
				}
			}
		}
	}
	
	@unlink($datafile);
	@file_put_contents($datafile,base64_encode(serialize($array)));
	if(!is_file($datafile)){
		writelogs("$datafile no such file or directory",__FUNCTION__,__FILE__,__LINE__);
	}
	
}


?>