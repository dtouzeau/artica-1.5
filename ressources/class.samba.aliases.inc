<?php
include_once(dirname(__FILE__)."/class.mysql.inc");
include_once(dirname(__FILE__)."/class.samba.inc");
class samba_aliases{
	var $hostname="";
	var $ou="";
	var $workgroup="";
	var $RootDir="";
	var $ipaddr=null;
	var $main_array=array();
	var $SAMBA_VERSION=0;
	var $upTo36=0;
	var $upTo357=0;
	public function samba_aliases($hostname=null){
		if($hostname<>null){
			$this->hostname=$hostname;
			$this->load();
		
		}
		
	}
	
	private function load(){
		$users=new usersMenus();
		$this->SAMBA_VERSION=$users->SAMBA_VERSION;
		$sql="SELECT * FROM samba_hosts WHERE hostname='$this->hostname'";
		$q=new mysql();
		if(!$q->ok){
			if($GLOBALS["VERBOSE"]){writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		}
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
		$this->ou=$ligne["ou"];
		$this->workgroup=$ligne["workgroup"];
		$this->RootDir=$ligne["RootDir"];
		$this->ipaddr=$ligne["ipaddr"];
		$this->main_array=unserialize(base64_decode($ligne["global_settings"]));
		
		$version=$users->SAMBA_VERSION;
		if(preg_match("#^([0-9]+)\.([0-9]+)\.([0-9]+)#",$version,$re)){$major=intval($re[1]);$minor=intval($re[2]);$build=intval($re[3]);}	
		if($major>2){if($minor>4){if($build>6){$this->upTo357=1;}}}
	
	
		if(preg_match("#^([0-9]+)\.([0-9]+)#", $version,$re)){
			$major=intval($re[1]);
			$minor=intval($re[2]);
			if($major>=3){if($minor>=6){$this->upTo36=1;$this->upTo357=1;}}
	}		
		
		$this->buildGlobalDefault();
		if($GLOBALS["VERBOSE"]){writelogs("Starting......: Samba [$this->hostname] v.$users->SAMBA_VERSION loading settings done...",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
	}
	
	private function buildGlobalDefault(){
		if(!isset($this->main_array["global"]["strict_allocate"])){$this->main_array["global"]["strict_allocate"]=0;}
		if(!isset($this->main_array["global"]["strict_locking"])){$this->main_array["global"]["strict_locking"]=1;}
		if(!isset($this->main_array["global"]["sync_always"])){$this->main_array["global"]["sync_always"]=0;}
		if(!isset($this->main_array["global"]["getwd_cache"])){$this->main_array["global"]["getwd_cache"]=1;}
		if(!isset($this->main_array["global"]["smb2_protocol"])){$this->main_array["global"]["smb2_protocol"]=1;}
		
		
		
		if(!is_numeric($this->main_array["global"]["strict_allocate"])){$this->main_array["global"]["strict_allocate"]=0;}
		if(!is_numeric($this->main_array["global"]["strict_locking"])){$this->main_array["global"]["strict_locking"]=0;}
		if(!is_numeric($this->main_array["global"]["sync_always"])){$this->main_array["global"]["sync_always"]=0;}
		if(!is_numeric($this->main_array["global"]["getwd_cache"])){$this->main_array["global"]["getwd_cache"]=1;}
		if(!is_numeric($this->main_array["global"]["smb2_protocol"])){$this->main_array["global"]["smb2_protocol"]=1;}
		
		
		if($this->upTo36==0){$this->main_array["global"]["smb2_protocol"]=0;}
		
	}
	
	public function SaveGlobals(){
		$sql="SELECT hostname FROM samba_hosts WHERE hostname='$this->hostname'";
		$q=new mysql();
		if(!$q->ok){if($GLOBALS["VERBOSE"]){writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);return;}}
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
		
		$global_settings=base64_encode(serialize($this->main_array));
		$global_settings=addslashes($global_settings);
		if($ligne["hostname"]==null){
			$sql="INSERT IGNORE INTO samba_hosts (hostname,global_settings) VALUES ('$this->hostname','$global_settings')";
		}else{
			$sql="UPDATE samba_hosts SET global_settings='$global_settings' WHERE hostname='$this->hostname'";
		}		
		
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo $q->mysql_error;return;}
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?restart-samba=yes");
	}
	
	
	public function build(){
		$sock=new sockets();
		$profile_path=$sock->GET_INFO('SambaProfilePath');
		if(trim($profile_path)==null){$profile_path="/home/export/profile";}
		$hostname=$this->hostname;
		if(strlen($hostname)>19){$hostname=substr($hostname, 0,19);}
		$f[]="[global]";
		$f[]="\tnetbios name = $hostname";
		$f[]="\tworkgroup = $this->workgroup";
		if($this->upTo357==0){
			if($this->main_array["global"]["strict_allocate"]==1){$f[]="\tstrict allocate = Yes";}else{$f[]="\tstrict allocate = No";}
		}
		if($this->main_array["global"]["strict_locking"]==1){$f[]="\tstrict locking = Auto";}else{$f[]="\tstrict locking = No";}
		if($this->main_array["global"]["sync_always"]==1){$f[]="\tsync always = Yes";}else{$f[]="\tsync always = No";}
		if($this->main_array["global"]["getwd_cache"]==1){$f[]="\tgetwd cache = Yes";}else{$f[]="\tgetwd cache = No";}
		if($this->main_array["global"]["smb2_protocol"]==1){$f[]="\tmax protocol = SMB2";}else{$f[]="\tmax protocol = NT1";}
		
		
		
		
		
		if($this->ipaddr<>null){
			$f[]="\tinterfaces = $this->ipaddr";
			$f[]="\tsocket address = $this->ipaddr";
			$f[]="\tbind interfaces only = yes";
		}
		
		
		if($this->ou<>null){
				$ldap=new clladp();
				$ldap_admin_dn="cn={$ldap->ldap_admin},{$ldap->suffix}";
				$suffix= $ldap->suffix;
				$ldap_group_suffix="dc=organizations";
				$ldap_user_suffix="ou=$this->ou,dc=organizations";
				$ldap_machine_suffix="ou=Computer,dc=samba,dc=organizations";
				$idmap_ldap_base_dn="ou=idmap,dc=samba,dc=organizations,{$ldap->suffix}";
				$usessl="off";
				$ldap_uri="ldap://{$ldap->ldap_host}:{$ldap->ldap_port}";				
				$f[]="\tldap delete dn = no";
				$f[]="\tpassdb backend = ldapsam:$ldap_uri";
				$f[]="\tadd machine script = /usr/share/artica-postfix/bin/artica-install --samba-add-computer \"%u\"";				
				$f[]="\tldap admin dn = $ldap_admin_dn";
				$f[]="\tldap suffix = $suffix";
				$f[]="\tldap group suffix = $ldap_group_suffix";
				$f[]="\tldap user suffix =  $ldap_user_suffix";			
				$f[]="\tldap machine suffix = $ldap_machine_suffix";
				$f[]="\tldap idmap suffix = $idmap_ldap_base_dn";
				$f[]="\tldap delete dn = yes";
				$f[]="\tencrypt passwords = true";
				$f[]="\tldap ssl  = off";	
			
			
		}
		
		$f[]="";
		$f[]="[homes]";
		$f[]="\tcomment = Personnal Folder on $this->hostname";
		$f[]="\tbrowseable = No";
		$f[]="\twriteable = Yes\n";

		$f[]="[netlogon]";
		$f[]="\tpath = /home/netlogon/";
		$f[]="\twritable = No";
		$f[]="\tbrowseable = No";
		$f[]="\twrite list = root";		
		
		$f[]="[profiles]";
		$f[]="\tpath = $profile_path";
		$f[]="\toplocks = false";
		$f[]="\tlevel2 oplocks = false";
		$f[]="\tcsc policy = disable";
		$f[]="\tbrowseable = No";
		$f[]="\twriteable = Yes";
		$f[]="\tread only = No";
		$f[]="\tprofile acls = Yes";
		$f[]="\tcreate mask = 0700";
		$f[]="\tdirectory mask = 0700";
		$f[]="\tcomment = Roaming user profile storage";
		$f[]="\thide files=/Desktop.ini/Thumbs.db/lost+found";
		$f[]="";
		$f[]="[profiles.V2]";
	    $f[]="\tcopy = profiles";
	    $f[]="\tbrowseable = No";
	    $f[]="\tcsc policy = disable";	        		
	    $f[]="";
		$f[]="[profile]";
	    $f[]="\tcopy = profiles";
	    $f[]="\tbrowseable = No";			
		
		$q=new mysql();
		$sql="SELECT ID FROM samba_hosts_share WHERE hostname='$this->hostname'";
		$results=$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){if($GLOBALS["VERBOSE"]){writelogs("Starting......: Samba [$this->hostname] $q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}}
		if($GLOBALS["VERBOSE"]){writelogs("Starting......: Samba [$this->hostname] ". mysql_num_rows($results)." rows",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
			
			if($GLOBALS["VERBOSE"]){writelogs("Starting......: Samba [$this->hostname] loading folder [{$ligne["ID"]}]",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
			$ss=new samba_virtuals_folders($ligne["ID"]);
			$f[]=$ss->compile();	
			
		}
		
		
		
		$f[]="";
		if($GLOBALS["AS_ROOT"]){echo "Starting......: Samba [$this->hostname] build configuration done\n";}
		@file_put_contents("/etc/samba/virtual/smb.conf.$this->hostname", @implode("\n", $f));
		if($this->ipaddr<>null){@file_put_contents("/etc/samba/virtual/smb.conf.$this->ipaddr", @implode("\n", $f));}
	}
	
	public function Edit(){
		$q=new mysql();
		$q->BuildTables();
	
		
		$sql="SELECT hostname FROM samba_hosts WHERE hostname='{$this->hostname}'";
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
		
		$sql="INSERT INTO samba_hosts (
			hostname,
			RootDir,
			workgroup,
			ou,
			ipaddr) VALUES (
			'{$this->hostname}',
			'{$this->RootDir}',
			'{$this->workgroup}',
			'{$this->ou}',
			'{$this->ipaddr}'
			);";	
		
		if($ligne["hostname"]<>null){
			$sql="UPDATE samba_hosts SET 
				RootDir='$this->RootDir',
				workgroup='{$this->workgroup}',
				ou='{$this->ou}',
				ipaddr='$this->ipaddr'
				WHERE hostname='{$this->hostname}'";
				
				
		}
		writelogs($sql,__FUNCTION__,__FILE__,__LINE__);
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo $q->mysql_error;return false;}
		return true;
	
	}		
		
		
	
	
}



class samba_virtuals_folders{
	var $ID;
	var $hostname;
	var $ou;
	var $share_name;
	var $share_path;
	var $share_params=array();
	var $RootDir;
	
	function samba_virtuals_folders($ID=0){
		$this->ID=intval($ID);
		if($ID>0){$this->load();}
		
	}
	
	private function load(){
		if(!is_numeric($this->ID)){return false;}
		$sql="SELECT * FROM samba_hosts_share WHERE ID='$this->ID'";
		writelogs("Loading ID:$this->ID parameters",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$q=new mysql();
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
		if(!$q->ok){if($GLOBALS["VERBOSE"]){writelogs("Mysql error: $q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}}
		$this->hostname=$ligne["hostname"];
		$this->share_name=$ligne["share_name"];
		$this->share_path=$ligne["share_path"];
		$this->share_params=unserialize(base64_decode($ligne["share_params"]));
		$this->Setdefault();
		$sql="SELECT * FROM samba_hosts WHERE hostname='$this->hostname'";
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
		$this->ou=$ligne["ou"];
		$this->RootDir=$ligne["RootDir"];
		
		
	}	
	
	private function Setdefault(){
		$smb=new samba();
		if($this->share_params["nt_acl_support"]==null){$this->share_params["nt_acl_support"]=$smb->main_array["global"]["nt acl support"];}
		if($this->share_params["acl_group_control"]==null){$this->share_params["acl_group_control"]=$smb->main_array["global"]["acl group control"];}
		if($this->share_params["map_acl_inherit"]==null){$this->share_params["map_acl_inherit"]=$smb->main_array["global"]["map acl inherit"];}
		if($this->share_params["acl_check_permissions"]==null){$this->share_params["acl_check_permissions"]=$smb->main_array["global"]["acl check permissions"];}
		if($this->share_params["inherit_acls"]==null){$this->share_params["inherit_acls"]=$smb->main_array["global"]["inherit acls"];}		
		if($this->share_params["dos_filemode"]==null){$this->share_params["dos_filemode"]=$smb->main_array["global"]["dos filemode"];}	
	
		if($this->share_params["create_mask"]==null){$this->share_params["create_mask"]= "0775";}
		if($this->share_params["directory_mask"]==null ){$this->share_params["directory_mask"]= "0777";}
		if($this->share_params["force_create_mode"]==null){$this->share_params["force_create_mode"] = "0775";}
		if($this->share_params["create_mode"]==null){$this->share_params["create_mode"]=$this->share_params["create mask"];}	
		reset($this->share_params);
		while (list ($num, $ligne) = each ($this->share_params) ){
			if($ligne=="yes"){$this->share_params[$num]=1;}
			if($ligne=="no"){$this->share_params[$num]=0;}
		}	
		
	}
	
	function compile(){
		if($GLOBALS["VERBOSE"]){writelogs("Starting......: Samba [$this->hostname] compile [{$this->share_name}]",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		reset($this->share_params);
		unset($this->share_params["folder-id"]);
		$maps["nt_acl_support"]="nt acl support";
		$maps["acl_group_control"]="acl group control";
		$maps["map_acl_inherit"]="map acl inherit";
		$maps["acl_check_permissions"]="acl check permissions";
		$maps["inherit_acls"]="inherit acls";
		$maps["dos_filemode"]="dos filemode";
		$maps["create_mask"]="create mask";
		$maps["directory_mask"]="directory mask";
		$maps["force_create_mode"]="force create mode";
		$maps["create_mode"]="create mode";
		$maps["hide_unreadable"]="hide unreadable";
		$maps["hide_unwriteable_files"]="hide unwriteable files";
		$this->share_params["browseable"]=$this->share_params["browsable"];
		
		$f[]="[$this->share_name]";
		$f[]="\tpath = $this->share_path";
		
		while (list ($num, $ligne) = each ($this->share_params) ){
			if($ligne==1){$this->share_params[$num]="yes";}
			if($ligne==0){$this->share_params[$num]="no";}
		}
		
		reset($this->share_params);
		while (list ($num, $ligne) = each ($this->share_params) ){
			
			if($maps[$num]<>null){
				$f[]="\t{$maps[$num]} = $ligne";
				if($GLOBALS["VERBOSE"]){writelogs("Starting......: Samba [$this->hostname] {$maps[$num]} = $ligne",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
				continue;
			}
			$f[]="\t$num = $ligne";
			if($GLOBALS["VERBOSE"]){writelogs("Starting......: Samba [$this->hostname] $num = $ligne",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		}		
		
	
		$f[]="";
		
		return @implode("\n", $f);
		
		
	}
	
	
	function Save(){
		$q=new mysql();
		$this->ID=intval($this->ID);
		$sql="SELECT RootDir FROM samba_hosts WHERE hostname='$this->hostname'";
		if($ligne["RootDir"]<>null){
			if(strpos("    ".$this->share_path, $ligne["RootDir"])==0){echo "$this->share_path Not authorized\n";return false;}
		}
		writelogs("Saving ID:$this->ID parameters",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$share_params=base64_encode(serialize($this->share_params));
		$sql="UPDATE samba_hosts_share SET 
		share_name='$this->share_name',
		share_params='$share_params',
		share_path='$this->share_path' WHERE ID='$this->ID'";
		
		if($this->ID==0){
			$sql="SELECT ID FROM samba_hosts_share WHERE hostname='$this->hostname' AND share_name='$this->share_name'";
			$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
			if($ligne["ID"]>0){
				echo "Cannot add this folder\n$this->share_name is already used";
				return false;
			} 
			
			$sql="INSERT INTO samba_hosts_share (share_name,share_path,hostname,share_params) VALUES (
			'$this->share_name',
			'$this->share_path',
			'$this->hostname',
			'$share_params'
			)
			";
			
		}
		
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo $q->mysql_error."\nFile:". basename(__FILE__)."\nLine:".__LINE__;return false;}
		return true;
		
	}
	
	
}

