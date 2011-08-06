<?php

class openssh{
	
	
	var $fullconf;
	var $main_array=array();
	var $ListenAddress=array();
	var $HostKey=array();
	
	function openssh(){
		$sock=new sockets();
		$this->fullconf=base64_decode($sock->getFrameWork("cmd.php?openssh-config=yes"));
		if($GLOBALS["VERBOSE"]){writelogs("config=".strlen($this->fullconf)." bytes lenght" ,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		$this->parseconf();
		$this->defaults();
	}
	
	private function parseconf(){
		$tb=explode("\n",$this->fullconf);
		if($GLOBALS["VERBOSE"]){writelogs("config=".count($tb)." rows" ,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		while (list ($num, $line) = each ($tb)){
			if(trim($line)==null){continue;}
			if(preg_match("#^ListenAddress\s+(.+)#",$line,$re)){$this->ListenAddress[]=$re[1];continue;}
			if(preg_match("#^HostKey\s+(.+)#",$line,$re)){$this->HostKey[]=$re[1];continue;}			
			
			if(preg_match("#^([A-Za-z0-9]+)\s+(.+)#",$line,$re)){								
				$this->main_array[$re[1]]=trim($re[2]);
			}else{
				if($GLOBALS["VERBOSE"]){writelogs("$line -> NO MATCH" ,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
			}
			
		}
		
		
		
	}		
	private function defaults(){
		if($this->main_array["LoginGraceTime"]==null){$this->main_array["LoginGraceTime"]=120;}
		if($this->main_array["PermitRootLogin"]==null){$this->main_array["PermitRootLogin"]="yes";}
		if($this->main_array["PermitTunnel"]==null){$this->main_array["PermitTunnel"]="no";}
		if($this->main_array["Port"]==null){$this->main_array["Port"]="22";}
		if($this->main_array["UsePAM"]==null){$this->main_array["UsePAM"]="no";}
		if($this->main_array["UseDNS"]==null){$this->main_array["UseDNS"]="yes";}
		if($this->main_array["PermitEmptyPasswords"]==null){$this->main_array["PermitEmptyPasswords"]="no";}
		if($this->main_array["MaxSessions"]==null){$this->main_array["MaxSessions"]="10";}
		if($this->main_array["MaxAuthTries"]==null){$this->main_array["MaxAuthTries"]="6";}
		if($this->main_array["Protocol"]==null){$this->main_array["Protocol"]="2";}
		if($this->main_array["UsePrivilegeSeparation"]==null){$this->main_array["UsePrivilegeSeparation"]="yes";}
		if($this->main_array["KeyRegenerationInterval"]==null){$this->main_array["KeyRegenerationInterval"]="3600";}
		if($this->main_array["ServerKeyBits"]==null){$this->main_array["ServerKeyBits"]="768";}
		if($this->main_array["SyslogFacility"]==null){$this->main_array["SyslogFacility"]="AUTH";}
		if($this->main_array["LogLevel"]==null){$this->main_array["LogLevel"]="INFO";}
		if($this->main_array["StrictModes"]==null){$this->main_array["StrictModes"]="yes";}
		if($this->main_array["RSAAuthentication"]==null){$this->main_array["RSAAuthentication"]="yes";}
		if($this->main_array["PubkeyAuthentication"]==null){$this->main_array["PubkeyAuthentication"]="yes";}
		if($this->main_array["IgnoreRhosts"]==null){$this->main_array["IgnoreRhosts"]="yes";}
		if($this->main_array["RhostsRSAAuthentication"]==null){$this->main_array["RhostsRSAAuthentication"]="no";}
		if($this->main_array["HostbasedAuthentication"]==null){$this->main_array["HostbasedAuthentication"]="no";}
		if($this->main_array["ChallengeResponseAuthentication"]==null){$this->main_array["ChallengeResponseAuthentication"]="no";}
		if($this->main_array["PermitEmptyPasswords"]==null){$this->main_array["PermitEmptyPasswords"]="no";}
		if($this->main_array["X11Forwarding"]==null){$this->main_array["X11Forwarding"]="yes";}
		if($this->main_array["X11DisplayOffset"]==null){$this->main_array["X11DisplayOffset"]="10";}
		if($this->main_array["PrintMotd"]==null){$this->main_array["PrintMotd"]="no";}
		if($this->main_array["PrintLastLog"]==null){$this->main_array["PrintLastLog"]="yes";}
		if($this->main_array["TCPKeepAlive"]==null){$this->main_array["TCPKeepAlive"]="yes";}
		if($this->main_array["AcceptEnv"]==null){$this->main_array["AcceptEnv"]="LANG LC_*";}
		if($this->main_array["Subsystem"]==null){$this->main_array["Subsystem"]="sftp /usr/lib/openssh/sftp-server";}											
		if($this->main_array["AuthorizedKeysFile"]==null){$this->main_array["AuthorizedKeysFile"]="%h/.ssh/authorized_keys";}
		if($this->main_array["PasswordAuthentication"]==null){$this->main_array["PasswordAuthentication"]="yes";}
			
		
		
		if(count($this->ListenAddress)==0){$this->ListenAddress[]="0.0.0.0";}
	}
	
	function save(){
		$users=new usersMenus();
		if(preg_match("#([0-9]+)\.([0-9]+)#",$users->OPENSSH_VER,$re)){$opensshver="{$re[1]}{$re[2]}";}
		if($opensshver<50){unset($this->main_array["MaxSessions"]);}
		unset($this->main_array["finduser"]);
		
		while (list ($num, $line) = each ($this->main_array)){
			if(trim($line)==null){continue;}
			$conf[]="$num\t$line";
		}
		
		while (list ($num, $line) = each ($this->ListenAddress)){
			$conf[]="ListenAddress\t$line";
		}
		
			while (list ($num, $line) = each ($this->HostKey)){
			$conf[]="HostKey\t$line";
		}		
		$conf[]="";
		$sock=new sockets();
		$sock->SaveConfigFile(@implode("\n",$conf),"OpenSSHDConfig");
		$sock->getFrameWork("cmd.php?sshd-restart=yes");
		
	}
		
	
	
	
}
?>