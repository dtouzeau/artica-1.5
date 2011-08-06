<?php

class auth_tail{
	var $maxttempts;
	
	function auth_tail(){
		if(!isset($GLOBALS["CLASS_UNIX"])){$GLOBALS["CLASS_UNIX"]=new unix();}
		if(!isset($GLOBALS["CLASS_SOCKET"])){$GLOBALS["CLASS_SOCKET"]=new sockets();}
		$this->maxttempts=$GLOBALS["CLASS_SOCKET"]->GET_INFO("SSHDHackMaxAttempts");
		if(!is_numeric($this->maxttempts)){$this->maxttempts=3;}
	}
	
	
	public function ParseLog($buffer){
		if($buffer==null){
			$this->events("Buffer is null");
			return false;
		}
		if(strpos($buffer,"monit[")>0){return false;}
		if(strpos($buffer,"session opened for user")>0){return true;}
		if(strpos($buffer,"session closed for user")>0){return true;}
		if(strpos($buffer,"subsystem request for sftp")>0){return true;}
		if(strpos($buffer,"error: Bind to port 22 on ")>0){return true;}
		if(strpos($buffer,"fatal: Cannot bind any address")>0){return true;}
		if(strpos($buffer,"server_exit     : master exited")>0){return true;}
		if(strpos($buffer,"PAM unable to dlopen")>0){return true;}
		if(strpos($buffer,"PAM adding faulty module")>0){return true;}
		if(strpos($buffer,"pam_unix(")>0){return true;}
		if(strpos($buffer,"slapcat:")>0){return true;}
		if(strpos($buffer,"Successful su")>0){return true;}
		
		if(preg_match("#pam_ldap: error trying to bind as user \"uid=root,.+?\"\s+\(Invalid credentials\)#", $buffer)){
			$GLOBALS["CLASS_UNIX"]->send_email_events("Error, root password is not set in LDAP Database", "pam_ldap claim $buffer\nYou should set your root password in the Artica web interface", "security");
			return true;
		}
		
		
		if(preg_match("#sshd.+?Accepted keyboard-interactive\/pam for\s+(.+?)\s+from\s+(.+?)\s+port#",$buffer,$re)){
			$this->events("{$re[2]} [{$re[1]}] connected");
			@mkdir("/var/log/artica-postfix/sshd-success",644,true);
			$array[$re[2]]=$re[1];
			$time=time();
			@file_put_contents("/var/log/artica-postfix/sshd-success/$time",serialize($array));	
			return true;		
		}
		
		if(preg_match("#lastlog_filetype: Couldn.+?t stat\s+(.+?): No such file or directory#",$buffer,$re)){
			$this->events("touch {$re[1]}");
			shell_exec("/bin/touch {$re[1]}");
			return true;
		}
		
		if(preg_match("#sshd.+?Accepted password for\s+(.+?)\s+from\s+(.+?)\s+#",$buffer,$re)){
			$this->events("{$re[2]} [{$re[1]}] connected");
			@mkdir("/var/log/artica-postfix/sshd-success",644,true);
			$array[$re[2]]=$re[1];
			$time=time();
			@file_put_contents("/var/log/artica-postfix/sshd-success/$time",serialize($array));	
			return true;		
		}
		
		
		if(preg_match("#sshd.+?Address\s+(.+?)\s+maps to (.+?),.+? POSSIBLE BREAK-IN ATTEMPT#i",$buffer,$re)){
			$this->events("POSSIBLE BREAK-IN ATTEMPT {$re[2]} [{$re[1]}] ");
			$filename=md5($re[1]);
			if($this->IfFileTime($filename,5)){
				@mkdir("/etc/artica-postfix/sshd-fw",644,true);
				$this->Addrule($re[1],$re[2]);
				$this->WriteFileCache($filename);
				return true;
			}
		}
		
		if(preg_match("#sshd.+?Invalid user\s+(.+?)\s+from\s+(.+?)$#",$buffer,$re)){
			$this->events("invalid user: {$re[2]} [{$re[1]}] ");
			$this->count_attack($re[2]);
			@mkdir("/var/log/artica-postfix/sshd-failed",644,true);
			$array[$re[2]]=$re[1];
			$time=time();
			@file_put_contents("/var/log/artica-postfix/sshd-failed/$time",serialize($array));
			return true;
		}
		
		if(preg_match("#sshd.+?error: PAM: Authentication failure for\s+(.+?)\s+from\s+(.+)#",$buffer,$re)){
			$this->events("Authentication failure: {$re[2]} [{$re[1]}] ");
			$this->count_attack($re[2]);
			@mkdir("/var/log/artica-postfix/sshd-failed",644,true);
			$array[$re[2]]=$re[1];
			$time=time();
			@file_put_contents("/var/log/artica-postfix/sshd-failed/$time",serialize($array));
			return true;
		}
		
		if(preg_match("#sshd.+?Accepted password for (.+?)\s+from\s+(.+?)\s+port#i",$buffer,$re)){
			$this->events("{$re[2]} [{$re[1]}] connected");
			@mkdir("/var/log/artica-postfix/sshd-success",644,true);
			$array[$re[2]]=$re[1];
			$time=time();
			@file_put_contents("/var/log/artica-postfix/sshd-success/$time",serialize($array));
		}
		
		if(preg_match("#Failed password for invalid user\s+(.+?)\s+from\s+(.+?)\s+#",$buffer,$re)){
			$this->count_attack($re[2]);
			@mkdir("/var/log/artica-postfix/sshd-failed",644,true);
			$array[$re[2]]=$re[1];
			$time=time();
			@file_put_contents("/var/log/artica-postfix/sshd-failed/$time",serialize($array));
			return true;
			
		}
		
		if(preg_match("#Failed password for\s+(.+?)\s+from\s+(.+?)\s+#",$buffer,$re)){
			$this->count_attack($re[2]);
			@mkdir("/var/log/artica-postfix/sshd-failed",644,true);
			$array[$re[2]]=$re[1];
			$time=time();
			@file_put_contents("/var/log/artica-postfix/sshd-failed/$time",serialize($array));
			return true;
		}
		
		if(preg_match("#pam_ldap: could not open secret file (.+?)\s+\(No such file or directory#",$buffer,$re)){
			include_once(dirname(__FILE__)."/class.ldap.inc");
			$ldap=new clladp();
			@file_put_contents("{$re[1]}",$ldap->ldap_password);
			return true;
		}
		
		if(preg_match("#sshd\[[0-9]+]\]:#",$buffer)){$this->events("Not Filtered : $buffer");return true;}
		
		return false;
	}
	
	private function Addrule($ip,$hostname){
		if($hostname==null){$hostname=gethostbyaddr($ip);}
		@mkdir("/etc/artica-postfix/sshd-fw",644,true);
		$array[$ip]=$hostname;
		$time=time();
		$this->events("Put $hostname [$ip] into firewall rule");
		@file_put_contents("/etc/artica-postfix/sshd-fw/$time",serialize($array));
	}
	
	private function count_attack($ip){
		$time=date('m-d-h');
		if(!isset($GLOBALS["SSHD-ATTACK"][$time][$ip])){$GLOBALS["SSHD-ATTACK"][$time][$ip]=0;}
		if(!is_numeric($GLOBALS["SSHD-ATTACK"][$time][$ip])){$GLOBALS["SSHD-ATTACK"][$time][$ip]=0;}
		$count=$GLOBALS["SSHD-ATTACK"][$time][$ip];
		$count=$count+1;
		$GLOBALS["SSHD-ATTACK"][$time][$ip]=$count;
		$this->events("$ip $count attempts");
		if($this->maxttempts==0){
			unset($GLOBALS["SSHD-ATTACK"][$time][$ip]);
			$this->events("maxttempts=0, disable firewall rules");
			return;
		}
		if($GLOBALS["SSHD-ATTACK"][$time][$ip]>$this->maxttempts){
			$this->Addrule($ip,null);
			unset($GLOBALS["SSHD-ATTACK"][$time][$ip]);
		}
		
	}
	
	
	private function events($text){
		$pid=@getmypid();
		$filename=basename(__FILE__);
		$date=@date("h:i:s");
		$logFile="/var/log/artica-postfix/auth-tail.debug";
		$size=@filesize($logFile);
		if($size>1000000){@unlink($logFile);}
		$f = @fopen($logFile, 'a');
		@fwrite($f, "$pid ".basename(__FILE__)." $text\n");
		@fclose($f);	
		$logFile="/var/log/artica-postfix/syslogger.debug";
		if(!isset($GLOBALS["CLASS_UNIX"])){
			include_once(dirname(__FILE__)."/framework/class.unix.inc");
			$GLOBALS["CLASS_UNIX"]=new unix();
		}
		$GLOBALS["CLASS_UNIX"]->events("$filename $text",$logFile);
		}
		
	private function WriteFileCache($filename){
		@unlink("/etc/artica-postfix/croned.1/sshd.$filename");
		@file_put_contents("/etc/artica-postfix/croned.1/sshd.$filename","#");	
	}	

	function IfFileTime($file,$min=10){
		$time=file_time_min("/etc/artica-postfix/croned.1/sshd.$file");
		$this->events("$file = {$time}Mn Max:$min");
		if($time>$min){return true;}
		return false;
	}	

}
?>