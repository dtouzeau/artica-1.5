<?php


class periodics_status{
	function periodics_status(){
		if(!isset($GLOBALS["CLASS_UNIX"])){$GLOBALS["CLASS_UNIX"]=new unix();}
	}
	
	function ps_mem(){
		
		if(!$GLOBALS["FORCE"]){
			$pidtimeFile="/etc/artica-postfix/pids/".basename(__FILE__).".".__CLASS__.".".__FUNCTION__.".time";
			if($GLOBALS["CLASS_UNIX"]->file_time_min($pidtimeFile)<15){return;}
		}
		
		@unlink($pidtimeFile);
		@file_put_contents($pidtimeFile, time());
		$dir_handle = @opendir("/proc");
		$date=date('Y-m-d H:i:s');
		if(!$dir_handle){return;}
		while ($file = readdir($dir_handle)) {
	 		if($file=='.'){continue;}
	  		if($file=='..'){continue;}
	  		if(!is_numeric($file)){continue;}
	  		$results=array();
	  		exec("/usr/share/artica-postfix/bin/meminfo.pl $file 2>&1",$results);
	  		if(!preg_match("#<(.+?)>\s+<([0-9\.]+)\s+([A-Z]+)#",trim(@implode("", $results)),$re)){continue;}
	  		$mem_org=$re[2];
	  		if($mem_org==0){continue;}
	  		$mem=$mem_org;
	  		$unit=$re[3];
	  		$process=$re[1];
	  		if($unit=="KB"){$mem=$mem*1024;}
			if($unit=="MB"){$mem=($mem*1024)*1000;}
			if($unit=="GB"){$mem=(($mem*1024)*1000)*1000;}
			if(isset($array[$re[1]])){$array[$re[1]]=$array[$re[1]]+$mem;continue;}
	  		$array[$re[1]]=$mem;
		}
		
		while (list ($process, $mem) = each ($array) ){
			$array2=array();
			$array2["mem"]=$mem;
			$array2["process"]=$process;
			$array2["time"]=$date;
			$kb=$mem/1024;
			if($GLOBALS["VERBOSE"]){include_once(dirname(__FILE__)."/class.templates.inc");echo "$process Mem: $mem (".FormatBytes($kb).")\n";}
			$serial=serialize($array2);
			@file_put_contents("/var/log/artica-postfix/ps-mem/".md5($serial), $serial);		
		}
		
		
		$results=array();
		$free=$GLOBALS["CLASS_UNIX"]->find_program("free");
		exec("$free -b -o 2>&1",$results);
		while (list ($index, $line) = each ($results) ){
			if(preg_match("#Mem:\s+[0-9]+\s+([0-9]+)\s+#",$line,$re)){
				$array["mem"]=$re[1];
				$array["time"]=date('Y-m-d H:i:s');	
				$serial=serialize($array);
				@file_put_contents("/var/log/artica-postfix/ps-mem-tot/".md5($serial), $serial);				
			}			
		}		
		$php5=$GLOBALS["CLASS_UNIX"]->LOCATE_PHP5_BIN();
		$nohup=$GLOBALS["CLASS_UNIX"]->find_program("nohup");
		shell_exec("$nohup $php5 /usr/share/artica-postfix/exec.syslog-engine.php --psmem >/dev/null 2>&1 &");
		
		
	}
	
	
}