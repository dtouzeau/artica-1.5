<?php

class postfix_maillog_buffer{
	var $buffer=null;
	
	
	function postfix_maillog_buffer($buffer){
		$this->buffer=$buffer;
	}
	
	public function parse(){
		$buffer=$this->buffer;

		if(preg_match("#fatal: master_spawn: exec\s+(.+?)\/postscreen: No such file or directory#",$buffer,$re)){
			$postscreen=$GLOBALS["CLASS_UNIX"]->LOCATE_POSTFIX_POSTSCREEN();
			$timefile="/etc/artica-postfix/croned.1/postscreen.no.such.file";
			$this->events("Missing Postscreen binary..",__LINE__);
			if($this->file_time_min($timefile)>10){
				if(is_file($postscreen)){
					if(is_file($GLOBALS["LN_BIN"])){
						$required=$re[1]."/postscreen";
						$this->events("Do symbolic link from $postscreen to $required",__LINE__);
						shell_exec("{$GLOBALS["LN_BIN"]} -s $postscreen $required");
					}
				}else{
					$this->email_events("Could not find PostScreen binary",
				"Postfix claim \n$buffer\nArtica is unable to find postscreen binary","postfix");
				@file_put_contents($file,"#");
				}
			}
		return true;
		}	
		
		// postfix/tlsmgr[4476]: fatal: open database /var/lib/postfix/smtpd_tls_cache.db: Invalid argument
		if(preg_match("#tlsmgr\[.+?fatal: open database\s+(.+?):\s+Invalid argument#", $buffer)){
			$timefile="/etc/artica-postfix/croned.1/".md5($re[1]).".Invalid.argument";
			$this->events("Database {$re[1]} Failed",__LINE__);
			if($this->file_time_min($timefile)>5){
				$this->events("Database {$re[1]} Failed -> Remove it",__LINE__);
				@unlink($re[1]);
				shell_exec(trim("{$GLOBALS["NOHUP_PATH"]} {$GLOBALS["postfix_bin_path"]} stop && {$GLOBALS["postfix_bin_path"]} start >/dev/null 2>&1"));
			}
			return true;
		}		
		
		
		
		
		

	// ----------------------------------------------------------------------------------------------------------------
	
	}
	
	
	private function events($text,$line){
		$filename=basename(__FILE__);
		$logFile="/var/log/artica-postfix/postfix-logger.debug";
		if(!isset($GLOBALS["CLASS_UNIX"])){return;}
		$GLOBALS["CLASS_UNIX"]->events("$filename $text (line $line)",$logFile);		
	}	
	
   private function email_events($subject,$text,$context){
	 if(!isset($GLOBALS["CLASS_UNIX"])){return;}
	 $GLOBALS["CLASS_UNIX"]->send_email_events($subject,$text,$context);
	}

	private function file_time_min($path){
		if(!is_file($path)){if($GLOBALS["VERBOSE"]){echo "file_time_min() -> unable to stat $path\n";}return 100000;}
		$last_modified = filemtime($path);
	 	$data1 = $last_modified;
	 	$data2 = time();
		$difference = ($data2 - $data1); 	 
		return round($difference/60);	 
	}	
	
	
}