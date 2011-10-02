<?php


class periodics_status{
	function periodics_status(){
		if(!isset($GLOBALS["CLASS_UNIX"])){$GLOBALS["CLASS_UNIX"]=new unix();}
	}
	
	function ps_mem(){
		$pidtimeFile="/etc/artica-postfix/pids/".basename(__FILE__).".".__CLASS__.".".__FUNCTION__.".time";
		if(!$GLOBALS["VERBOSE"]){
			if($GLOBALS["CLASS_UNIX"]->file_time_min($pidtimeFile)<15){return;}
		}
		@unlink($pidtimeFile);
		@file_put_contents($pidtimeFile, time());
		
		$python=$GLOBALS["CLASS_UNIX"]->find_program("python");
		if(strlen($python)<strlen("python")){writelogs("Fatal,Unable to stat python",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);return;}
		exec("$python /usr/share/artica-postfix/bin/ps_mem.py 2>&1",$results);
		$match="#[0-9\.]+.+?\+\s+[0-9\.]+.*?=.*\s+([0-9\.]+)\s+([A-Za-z]+)\s+(.+)#";
		$matchTOT="#^\s+([0-9\.]+)\s+([A-Za-z]+)$#";
		
		while (list ($index, $line) = each ($results) ){
			if(preg_match($matchTOT,$line,$re)){
				if($GLOBALS["VERBOSE"]){echo "TOTAL ::: MATCH \"$match\"\n";}
				$total=$re[1];
				$value=$re[2];
				if($value=="KiB"){$num=$re[1]*1024;$num=$num*1000;}
				if($value=="MiB"){$num=$re[1]*1024;$num=$num*1000;$num=$num*1000;}
				if($value=="GiB"){$num=$re[1]*1024;$num=$num*1000;$num=$num*1000;$num=$num*1000;}
				if(!is_dir("/var/log/artica-postfix/ps-mem-tot")){@mkdir("/var/log/artica-postfix/ps-mem-tot");}	
				$array["mem"]=$num;
				$array["time"]=date('Y-m-d H:i:s');	
				$serial=serialize($array);
				@file_put_contents("/var/log/artica-postfix/ps-mem-tot/".md5($serial), $serial);
				$serial=null;$array=array();$re=array();$total=null;$value=null;	
				continue;
			}
			
			if(!preg_match($match,$line,$re)){if($GLOBALS["VERBOSE"]){echo "'$line' NO MATCH \"$match\"\n";}continue;}
			$num=$re[1];
			$orgmemory=$num;
			$value=$re[2];
			if($value=="KiB"){$num=$re[1]*1024;$num=$num*1000;}
			if($value=="MiB"){$num=$re[1]*1024;$num=$num*1000;$num=$num*1000;}
			if($value=="GiB"){$num=$re[1]*1024;$num=$num*1000;$num=$num*1000;$num=$num*1000;}
			$process=$re[3];
			if(preg_match("#(.+?)\s+\(([0-9]+)#", $process,$ri)){
				$processnum=$ri[2];
				$process=trim($ri[1]);
			}
			
			if(!is_dir("/var/log/artica-postfix/ps-mem")){@mkdir("/var/log/artica-postfix/ps-mem");}
			$array["mem"]=$num;
			$array["process"]=$process;
			$array["time"]=date('Y-m-d H:i:s');
			if($GLOBALS["VERBOSE"]){
				include_once(dirname(__FILE__)."/class.templates.inc");
				$kbits=$num/1024;
				echo "$process Mem: $orgmemory $value =  $num (".ParseBytes($kbits).")\n";}
			$serial=serialize($array);
			@file_put_contents("/var/log/artica-postfix/ps-mem/".md5($serial), $serial);
			
		}
		
		
		$php5=$GLOBALS["CLASS_UNIX"]->LOCATE_PHP5_BIN();
		$nohup=$GLOBALS["CLASS_UNIX"]->find_program("nohup");
		shell_exec("$nohup $php5 /usr/share/artica-postfix/exec.syslog-engine.php --psmem >/dev/null 2>&1 &");
		
		
	}
	
	
}