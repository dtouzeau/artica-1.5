<?php
	include_once(dirname(__FILE__)."/class.templates.inc");
	include_once(dirname(__FILE__)."/class.ldap.inc");
	include_once(dirname(__FILE__)."/class.os.system.inc");
	include_once(dirname(__FILE__).'/class.user.inc');
	include_once(dirname(__FILE__).'/class.computers.inc');	
	include_once(dirname(__FILE__).'/class.ini.inc');
	
	class rsync{
		var $ou_storages=array();
		
		
		function rsync(){
			$this->orgsharedlist();
		}
		
		
		private function orgsharedlist(){
			$ldap=new clladp();
			$pattern="objectClass=OuBackupStorage";
			$suffix="dc=organizations,$ldap->suffix";
			$filter=array("cn");
			$sr =@ldap_search($ldap->ldap_connection,$suffix,'objectClass=OuBackupStorage',array("cn"));
			if(!$sr){return null;}
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);	
			$os=new usb();
			
			for($i=0;$i<$hash["count"];$i++){
				$dn=$hash[$i]["dn"];
				$folder=$hash[$i]["cn"][0];
				if(!preg_match("#cn=storages_enabled,ou=(.+?),dc=organizations#",$dn,$re)){continue;}
				$mounted=$os->isMounted($folder);
				$this->ou_storages[]=array("DEV"=>$folder,"OU"=>$re[1],"mounted"=>$mounted);
				
			}
			
			
			
			
		}
		
		
		
	}
	
	
	class rsyncd_conf{
		
		var $paths=array();
		var $paths_users=array();
		var $main_array=array();
		var $RsyncMaxConnections;
		
		function rsyncd_conf(){
			$this->load();
		}
		
		private function load(){
			$sock=new sockets();
			$this->RsyncMaxConnections=$sock->GET_INFO("RsyncMaxConnections");
			
			if((posix_getuid()==0)){$datas=@file_get_contents("/etc/rsync/rsyncd.conf");}else{
			$datas=base64_decode($sock->getFrameWork("cmd.php?rsyncd-conf=yes"));}
			
			$ini=new Bs_IniHandler();
			$ini->loadString($datas);
			if(!is_array($ini->_params)){return;}
			while (list ($num, $line) = each ($ini->_params)){
				if($ini->_params[$num]["path"]<>null){
					$this->paths[$ini->_params[$num]["path"]]=$num;
					$this->main_array[$ini->_params[$num]["path"]]=$ini->_params[$num];
					$this->main_array[$ini->_params[$num]["path"]]["NAME"]=$num;
				}
				
			}
			
			
		}
		
		
		public function save(){
			if($this->RsyncMaxConnections==null){$this->RsyncMaxConnections="4";}
			$this->oustorages();
			$conf[]="max connections =$this->RsyncMaxConnections";
			$conf[]="syslog facility =daemon";
			$conf[]="pid file =/var/run/rsync/rsyncd.pid";
			$conf[]="transfer logging = yes";
	    	reset($this->main_array);
			while (list ($num, $line) = each ($this->main_array)){
				if($line["use chroot"]==null){$line["use chroot"]="false";}
				if($line["read only"]==null){$line["read only"]="false";}
				if($line["uid"]==null){$line["uid"]="root";}
				if($line["gid"]==null){$line["gid"]="root";}

				$conf[]="[{$line["NAME"]}]";
				$conf[]="\tpath=$num";
				$conf[]="\tcomment={$line["comment"]}";
				$conf[]="\thosts allow={$line["hosts allow"]}";
				$conf[]="\tuse chroot={$line["use chroot"]}";
				$conf[]="\tuid={$line["uid"]}";
				$conf[]="\tgid={$line["gid"]}";
				$conf[]="\tread only=false";
				$conf[]="\ttransfer logging=yes";
				$conf[]="\tlog format=%o %h [%a] %m (%u) %f %l";
				if($line["auth users"]<>null){
					$conf[]="\tauth users={$line["auth users"]}";
					$conf[]="\tsecrets file=/etc/rsync/secrets/".md5($line["NAME"]);
					
					
					
				}	
				
				$conf[]="";
			}
			
			$datas=implode("\n",$conf);
			$sock=new sockets();
			if((posix_getuid()==0)){
				@file_put_contents("/etc/rsync/rsyncd.conf",$datas);
				return;
				
			}
			
			$sock->getFrameWork("cmd.php?rsync-save-conf=".base64_encode($datas));
			
			
		}
		
		
		private function oustorages(){
		$rsync=new rsync();
		if(!is_array($rsync->ou_storages)){
			return null;
		}

		while (list ($num, $array) = each ($rsync->ou_storages) ){
			$ou=$array["OU"];
			$mounted=$array["mounted"];
			if($mounted==null){continue;}
			$this->GetConfUsersForOu($ou,$mounted);
	
			}
		
		}
		
	private function GetConfUsersForOu($ou,$mounted){
		$ldap=new clladp();
		$tofind='*';
		$filter="(&(objectClass=userAccount)(|(cn=$tofind)(mail=$tofind)(displayName=$tofind)(uid=$tofind)(givenname=$tofind) ))";
		$attrs=array("uid","userpassword","homeDirectory");
		$dn="ou=$ou,dc=organizations,$ldap->suffix";
		$hash=$ldap->Ldap_search($dn,$filter,$attrs);
		$number=$hash["count"];
	
		for($i=0;$i<$hash["count"];$i++){
			$uid=$hash[$i]["uid"][0];
			$homeDirectory=$hash[$i][strtolower("homeDirectory")][0];
			$ips=$this->GetComputers($uid);
			if(!is_array($ips)){continue;}
			$path="$homeDirectory/.backup";
			writelogs(count($ips)." computers for ".$uid ." path=$homeDirectory/.backup",__FUNCTION__,__FILE__,__LINE__);
			$this->main_array[$path]["NAME"]=$uid;
			$this->main_array[$path]["comment"]="$uid backup ressources";
			$this->main_array[$path]["hosts allow"]=implode(",",$ips);
			$this->main_array[$path]["use chroot"]="false";
			$this->main_array[$path]["read only"]="false";
			$this->main_array[$path]["uid"]="root";
			$this->main_array[$path]["gid"]="root";
			$d++;
			}
	
		if(posix_getuid()==0){echo "Starting......: Rsync $d users for $ou $mounted\n";}
	}

	private function GetComputers($user_id){
			$userid=new user($user_id);
			$dn=$userid->dn;
			$ldap=new clladp();
			$pattern="(&(objectClass=ComputerAfectation)(cn=*))";
			$attr=array();
			$sr=@ldap_search($ldap->ldap_connection,$dn,$pattern,$attr);
			if(!$sr){return null;}
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);
			if($hash["count"]==0){return;}
			
			for($i=0;$i<$hash["count"];$i++){
				$uid=$hash[$i]["uid"][0];
				
				$computer=new computers($uid);
				$array[]=$computer->ComputerIP;
			}	
		
		return $array;
		}

}
	
?>