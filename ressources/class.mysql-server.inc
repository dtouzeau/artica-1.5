<?php
include_once(dirname(__FILE__). '/class.ini.inc');
include_once(dirname(__FILE__). '/class.sockets.inc');
include_once(dirname(__FILE__). '/class.users.menus.inc');
include_once(dirname(__FILE__). '/class.mysql.inc');

class mysqlserver{
	
	var $main_array=array();
	var $mysql_version_string=0;
	var $mysqlvbin=0;
	var $innodb_file_per_table=0;
	var $VERSION_MAJOR;
	var $VERSION_MINOR;
	var $VERSION_REV;
	
	function mysqlserver(){
		$this->load();
		$users=new usersMenus();
		$this->mysql_version_string=$users->mysqld_version;
		
	if(posix_getuid()==0){
			$GLOBALS["AS_ROOT"]=true;
			echo "Starting......: Mysql version.......:$this->mysql_version_string\n";
			
		}
		
		if(preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+)#",$this->mysql_version_string,$re)){
			if(strlen($re[1])==1){$re[1]=$re[1].'0';}
			if(strlen($re[2])==1){$re[2]=$re[2].'0';}
			if(strlen($re[3])==1){$re[3]=$re[3].'0';}
			$this->VERSION_MAJOR=$re[1];
			$this->VERSION_MINOR=$re[2];
			$this->VERSION_REV=$re[3];
			$this->mysqlvbin=$re[1].$re[2].$re[3];
		}
		
	}
	
	
	private function load(){
		$ini=new Bs_IniHandler();
		$sock=new sockets();
		$datas=$sock->GET_INFO("MysqlParameters");
		$this->innodb_file_per_table=$sock->GET_INFO("innodb_file_per_table");
		$ini->loadString($datas);
		$this->main_array=$ini->_params["MYSQL"];
		$this->BuildDefault();
		$this->LOAD_VARIABLES();
		
	}
	
	private function LOAD_VARIABLES(){
		$q=new mysql();
		return $q->SHOW_VARIABLES();
	}
	
	private function BuildDefault(){
		if($this->main_array["skip_external_locking"]==null){$this->main_array["skip_external_locking"]='yes';}
		//if($this->main_array["bind-address"]==null){$this->main_array["bind-address"]='127.0.0.1';}
		if($this->main_array["tmp_table_size"]==null){$this->main_array["tmp_table_size"]='64';}
		if($this->main_array["table_cache"]==null){$this->main_array["table_cache"]='512';}		
		if($this->main_array["max_allowed_packet"]==null){$this->main_array["max_allowed_packet"]='100';}
		if($this->main_array["sort_buffer_size"]==null){$this->main_array["sort_buffer_size"]='8';}
		if($this->main_array["key_buffer_size"]==null){$this->main_array["key_buffer_size"]='256';}
		if($this->main_array["table_open_cache"]==null){$this->main_array["table_open_cache"]='256';}
		if($this->main_array["read_buffer_size"]==null){$this->main_array["read_buffer_size"]='1';}
		if($this->main_array["read_rnd_buffer_size"]==null){$this->main_array["read_rnd_buffer_size"]='4';}
		if($this->main_array["myisam_sort_buffer_size"]==null){$this->main_array["myisam_sort_buffer_size"]='64';}
		if($this->main_array["query_cache_size"]==null){$this->main_array["query_cache_size"]='16';}		
	}
	
	public function save(){
		$ini=new Bs_IniHandler();
		$ini->_params["MYSQL"]=$this->main_array;
		$sock=new sockets();
		$datas=$ini->toString();
		$sock->SET_INFO("MysqlParameters",$datas);
		$sock->getFrameWork("cmd.php?restart-mysql=yes");
		
	}
	
	private function default_character_set(){
		
		if($this->VERSION_MAJOR>4){
				if($this->VERSION_MAJOR<6){
					if($this->VERSION_REV<3){
						return "default-character-set";
					}
				}
			}
			
			if($this->VERSION_MAJOR>4){
				if($this->VERSION_MAJOR>4){
					if($this->VERSION_REV>=3){
						return "character-set-server";
					}
				}
			}
		
	}
	
	private function setssl(){
		$q=new mysql();
		$q->SET_VARIABLES("have_openssl","YES");
		$q->SET_VARIABLES("have_ssl", "YES");
	}
	
	function Mysql_verlow_config(){
		
		$this->main_array["max_allowed_packet"]=1;
		$this->main_array["key_buffer"]=16;
		$this->main_array["table_cache"]=1;
		if(posix_getuid()==0){
			$GLOBALS["AS_ROOT"]=true;
			echo "Starting......: Mysql binary version:$this->mysqlvbin\n";
			if(!is_file("/etc/mysql/debian-start")){@file_put_contents("/etc/mysql/debian-start", "");}
			shell_exec("/bin/chmod 755 /etc/mysql/debian-start");
		}
		
		$conf="[client]\n";
		$conf=$conf ."port=3306\n";
		$conf=$conf ."socket=/var/run/mysqld/mysqld.sock\n";
		$conf=$conf . $this->ssl_client_mode();
		$conf=$conf ."\n";
		$conf=$conf ."[mysqld_safe]\n";
		$conf=$conf ."socket=/var/run/mysqld/mysqld.sock\n";
		$conf=$conf ."nice=0\n";
		$conf=$conf ."\n";
		$conf=$conf ."[mysqld]\n";
		$SpecificConf=$this->GetUsersSpecificConf();
		if($SpecificConf<>null){$conf=$conf.$SpecificConf."\n";}
		$conf=$conf ."user=mysql\n";
		$conf=$conf ."log-error\n";
		$conf=$conf ."pid-file=/var/run/mysqld/mysqld.pid\n";
		$conf=$conf ."socket=/var/run/mysqld/mysqld.sock\n";
		$conf=$conf ."port=3306\n";	
		$conf=$conf .$this->ssl_server_mode();
		if($this->main_array["bind-address"]<>null){
			$conf=$conf ."bind-address={$this->main_array["bind-address"]}\n";
		}		
		
		if($GLOBALS["AS_ROOT"]){echo "Starting......: Mysql Major:$this->VERSION_MAJOR; Minor:$this->VERSION_MAJOR; rev: $this->VERSION_REV\n";}
		
		if($this->main_array["default-character-set"]<>null){$conf=$conf.$this->default_character_set()."=".$this->main_array["default-character-set"];}
		
		if($this->main_array["skip-character-set-client-handshake"]=="yes"){
			$conf=$conf ."skip-character-set-client-handshake\n";
		}
		
		$conf=$conf ."basedir=/usr\n";
		$conf=$conf ."datadir=/var/lib/mysql\n";
		$conf=$conf ."tmpdir=/tmp\n";
		$conf=$conf. "skip-locking\n";
		$conf=$conf. "skip-bdb\n";
		$conf=$conf. "skip-innodb\n";		
		$conf=$conf. "key_buffer = 12K\n";
		$conf=$conf. "max_allowed_packet = {$this->main_array["max_allowed_packet"]}M\n";
		$conf=$conf. "table_cache = {$this->main_array["table_cache"]}\n";
		$conf=$conf. "sort_buffer_size = 16K\n";
		$conf=$conf. "read_buffer_size = 16K\n";
		$conf=$conf. "read_rnd_buffer_size = 1K\n";
		$conf=$conf. "net_buffer_length = 2K\n";
		$conf=$conf. "thread_stack =16K\n";
		$conf=$conf ."connect_timeout=10\n";
		$conf=$conf ."thread_concurrency=2\n";
		$conf=$conf ."thread_cache_size = 3\n";
		$conf=$conf ."max_connections = 10\n";
		$conf=$conf ."max_connect_errors=1000\n";
		$conf=$conf ."query_cache_limit = 1048576\n";
		$conf=$conf ."query_cache_size = 1048576\n";
		$conf=$conf ."query_cache_type = 0\n";
		$conf=$conf ."default-storage-engine=MyISAM\n";	
		$conf=$conf ."\n";
		$conf=$conf ."[mysqldump]\n";	
		$conf=$conf ."quick\n";	
		$conf=$conf ."max_allowed_packet = 14K\n";	
		$conf=$conf ."[isamchk]\n";
		$conf=$conf ."key_buffer = 14K\n";
		$conf=$conf ."sort_buffer_size = 14K\n";
		$conf=$conf ."\n";
		$conf=$conf ."[myisamchk]\n";
		$conf=$conf ."key_buffer = 14K\n";
		$conf=$conf ."sort_buffer_size = 14K\n";
		$conf=$conf ."\n";
		$conf=$conf ."[mysqlhotcopy]\n";
		$conf=$conf ."interactive-timeout\n";	
		return $conf;			
		
	}
	
	
	function Mysql_low_config(){
		$this->main_array["max_allowed_packet"]=1;
		$this->main_array["key_buffer"]=16;
		$this->main_array["table_cache"]=1;
		if(posix_getuid()==0){
			echo "Starting......: Mysql binary version:$this->mysqlvbin\n";
		}
		
		
		$conf="[client]\n";
		$conf=$conf ."port=3306\n";
		$conf=$conf ."socket=/var/run/mysqld/mysqld.sock\n";
		$conf=$conf . $this->ssl_client_mode();
		$conf=$conf ."\n";
		$conf=$conf ."[mysqld_safe]\n";
		$conf=$conf ."socket=/var/run/mysqld/mysqld.sock\n";
		$conf=$conf ."nice=0\n";
		$conf=$conf ."\n";
		$conf=$conf ."[mysqld]\n";
		$SpecificConf=$this->GetUsersSpecificConf();
		if($SpecificConf<>null){$conf=$conf.$SpecificConf."\n";}
		$conf=$conf ."user=mysql\n";
		$conf=$conf ."pid-file=/var/run/mysqld/mysqld.pid\n";
		$conf=$conf ."socket=/var/run/mysqld/mysqld.sock\n";
		$conf=$conf ."port=3306\n";
		$conf=$conf .$this->ssl_server_mode();
		if($this->main_array["bind-address"]<>null){
			$conf=$conf ."bind-address={$this->main_array["bind-address"]}\n";
		}		
		
	
		if($this->main_array["default-character-set"]<>null){$conf=$conf.$this->default_character_set()."=".$this->main_array["default-character-set"];}
		
		
		if($this->main_array["skip-character-set-client-handshake"]=="yes"){
			$conf=$conf ."skip-character-set-client-handshake\n";
		}
		
		$conf=$conf ."basedir=/usr\n";
		$conf=$conf ."datadir=/var/lib/mysql\n";
		$conf=$conf ."log-error\n";
		$conf=$conf ."tmpdir=/tmp\n";
		$conf=$conf. "skip-locking\n";
		$conf=$conf. "skip-bdb\n";
		$conf=$conf. "skip-innodb\n";		
		$conf=$conf. "key_buffer = 16K\n";
		$conf=$conf. "max_allowed_packet = {$this->main_array["max_allowed_packet"]}M\n";
		$conf=$conf. "table_cache = {$this->main_array["table_cache"]}\n";
		$conf=$conf. "sort_buffer_size = 16K\n";
		$conf=$conf. "read_buffer_size = 16K\n";
		$conf=$conf. "read_rnd_buffer_size = 1K\n";
		$conf=$conf. "net_buffer_length = 1K\n";
		$conf=$conf. "thread_stack =16K\n";
		$conf=$conf ."wait_timeout=7200\n";
		$conf=$conf ."connect_timeout=10\n";
		$conf=$conf ."max_connect_errors=1000\n";
		$conf=$conf ."query_cache_type=1\n";
		$conf=$conf ."query_cache_limit = 1048576\n";
		$conf=$conf ."query_cache_size = 1048576\n";
		$conf=$conf ."query_cache_type = 1\n";	
		$conf=$conf ."default-storage-engine=MyISAM\n";	
		if($this->innodb_file_per_table==1){
			$conf=$conf ."innodb_file_per_table\n";	
		}	
		$conf=$conf ."\n";
		$conf=$conf ."[mysqldump]\n";	
		$conf=$conf ."quick\n";	
		$conf=$conf ."max_allowed_packet = 16K\n";	
		$conf=$conf ."[isamchk]\n";
		$conf=$conf ."key_buffer = 16KM\n";
		$conf=$conf ."sort_buffer_size = 16K\n";
		$conf=$conf ."\n";
		$conf=$conf ."[myisamchk]\n";
		$conf=$conf ."key_buffer = 16K\n";
		$conf=$conf ."sort_buffer_size = 16K\n";
		$conf=$conf ."\n";
		$conf=$conf ."[mysqlhotcopy]\n";
		$conf=$conf ."interactive-timeout\n";	
		return $conf;
		
	}
	
	private function GetUsersSpecificConf(){
		
			
		
		
		if(!$GLOBALS["AS_ROOT"]){return;}
		if(!is_file("/etc/artica-postfix/my.cnf.mysqld")){
			if($GLOBALS["VERBOSE"]){"mysqld: /etc/artica-postfix/my.cnf.mysqld no such file\n";}
		}
		$datas=@file_get_contents("/etc/artica-postfix/my.cnf.mysqld");
		$tbl=explode("\n",$datas);
		
		
		
		while (list ($index, $value) = each ($tbl) ){
			if(substr($value, 0,1)=="#"){continue;}
			if(preg_match("#(.+?)=(.+)#", $value,$re)){
				$key=trim(strtolower($re[1]));
				if($this->TestsParametersToDelete($key)){
					echo "Starting......: Mysql \"$key\" is depreciated\n";
					$tbl[$index]="#$key={$re[2]} - depreciated";
					$save=true;
					continue;
				}
				
				echo "Starting......: Mysql checking $key done\n";
			}
			
		}
		
		if($save){@file_put_contents("/etc/artica-postfix/my.cnf.mysqld", @implode("\n", $tbl));}
		
		return @implode("\n", $tbl);
		
	}
	
	private function TestsParametersToDelete($param){
		if($this->VERSION_MAJOR>4){
			if($this->VERSION_MAJOR>4){
				if($this->VERSION_REV>=3){
						$array["master-connect-retry"]=true;
					}
				}
			}

		if(!is_array($array)){return false;}
		if(isset($array[$param])){return true;}
		
	}
	
	
	private function ssl_client_mode(){
		if(!isset($GLOBALS["AS_ROOT"])){if(posix_getuid()==0){$GLOBALS["AS_ROOT"]=true;}}
		if(!$GLOBALS["AS_ROOT"]){return;}
		$sock=new sockets();
		$mysqlSSL=$sock->GET_INFO("mysqlSSL");
		if(!is_numeric($mysqlSSL)){$mysqlSSL=0;}
		if($mysqlSSL==0){return null;}		
		$files[]="/etc/ssl/certs/mysql/cacert.pem";
		$files[]="/etc/ssl/certs/mysql-client/client-cert.pem";
		$files[]="/etc/ssl/certs/mysql-client/client-key.pem";		
		$continue=true;
		while (list ($i, $filename) = each ($files) ){if(!is_file($filename)){echo "$filename no such file\n";$continue=false;}}
		if(!$continue){return null;}
		if(is_file("/etc/ssl/certs/mysql-client/cacert.pem")){$cacert="/etc/ssl/certs/mysql-client/cacert.pem";}else{$cacert="/etc/ssl/certs/mysql/cacert.pem";}
		$f[]="ssl-ca=$cacert";
		$f[]="ssl-cert=/etc/ssl/certs/mysql-client/client-cert.pem";
		$f[]="ssl-key=/etc/ssl/certs/mysql-client/client-key.pem";
		$f[]="";
		return @implode("\n", $f);		
	}
	

	
	private function ssl_server_mode(){
		if(!isset($GLOBALS["AS_ROOT"])){if(posix_getuid()==0){$GLOBALS["AS_ROOT"]=true;}}
		if(!$GLOBALS["AS_ROOT"]){return;}
		$sock=new sockets();
		$mysqlSSL=$sock->GET_INFO("mysqlSSL");
		if(!is_numeric($mysqlSSL)){$mysqlSSL=0;}
		if($mysqlSSL==0){return null;}
		$files[]="/var/lib/mysql/mysql-ca-cert.pem";
		$files[]="/var/lib/mysql/mysql-server-cert.pem";
		$files[]="/var/lib/mysql/mysql-server-key.pem";
		$continue=true;
		while (list ($i, $filename) = each ($files) ){if(!is_file($filename)){echo "$filename no such file\n";$continue=false;}}
		if(!$continue){
			shell_exec("/usr/share/artica-postfix/bin/artica-install --mysql-certificate");
			reset($files);
			$continue=true;
			while (list ($i, $filename) = each ($files) ){if(!is_file($filename)){echo "$filename no such file\n";$continue=false;}}
		}
		if(!$continue){return null;}
		$f[]="ssl";
		$f[]="ssl-ca=/var/lib/mysql/mysql-ca-cert.pem";
		$f[]="ssl-cert=/var/lib/mysql/mysql-server-cert.pem";
		$f[]="ssl-key=/var/lib/mysql/mysql-server-key.pem";
		$f[]=""; 	
		$this->setssl();	
		return @implode("\n", $f);
		
	}
	
	function BuildConf(){
		if(!isset($GLOBALS["AS_ROOT"])){if(posix_getuid()==0){$GLOBALS["AS_ROOT"]=true;}}
		if($GLOBALS["AS_ROOT"]){echo "\n";}
		$SpecificConf=$this->GetUsersSpecificConf();
		
		$conf="[client]\n";
		$conf=$conf ."port=3306\n";
		$conf=$conf ."socket=/var/run/mysqld/mysqld.sock\n";
		$conf=$conf . $this->ssl_client_mode();
		$conf=$conf ."\n";
		$conf=$conf ."[mysqld_safe]\n";
		$conf=$conf ."socket=/var/run/mysqld/mysqld.sock\n";
		if($this->main_array["default-character-set"]<>null){
			$conf=$conf ."default-character-set={$this->main_array["default-character-set"]}\n";
		}		
		$conf=$conf ."nice=0\n";
		$conf=$conf ."\n";
		
		if($GLOBALS["AS_ROOT"]){
			echo "Starting......: Mysql ". count($SpecificConf)." specific tokens\n";
		}
		$conf=$conf ."[mysqld]\n";
		if($SpecificConf<>null){
			$conf=$conf.$SpecificConf."\n";
		}
		
		if($this->main_array["default-character-set"]<>null){$mysqld[$this->default_character_set()]=$this->main_array["default-character-set"];}	

		if($this->main_array["skip-character-set-client-handshake"]=="yes"){
			$mysqld["skip-character-set-client-handshake"]="";
		}
		
		$ssl=$this->ssl_server_mode();
		if($ssl<>null){
			$mysqld["ssl"]=null;
			$mysqld["ssl-ca"]="/var/lib/mysql/mysql-ca-cert.pem";
			$mysqld["ssl-cert"]="/var/lib/mysql/mysql-server-cert.pem";
			$mysqld["ssl-key"]="/var/lib/mysql/mysql-server-key.pem";	
		}
		
		$mysqld["user"]="mysql";
		$mysqld["pid-file"]="/var/run/mysqld/mysqld.pid";
		$mysqld["socket"]="/var/run/mysqld/mysqld.sock";
		$mysqld["port"]="3306";
		$mysqld["basedir"]="/usr";
		$mysqld["datadir"]="/var/lib/mysql";
		$mysqld["tmpdir"]="/tmp";
		
		if($this->main_array["skip_external_locking"]=="yes"){
			$mysqld["skip-external-locking"]="";
		}
		if($this->main_array["bind-address"]<>null){
			$mysqld["bind-address"]=$this->main_array["bind-address"];
		}
		
		if($this->main_array["key_buffer"]<>null){
			$mysqld["key_buffer"]="{$this->main_array["key_buffer"]}M";
		}
		$mysqld["myisam-recover"]="BACKUP";
		$mysqld["expire_logs_days"]="10";
		$mysqld["max_binlog_size"]="100M";
		$mysqld["back_log"]="75";
		if($this->main_array["read_rnd_buffer_size"]<>null){
			$mysqld["read_rnd_buffer_size"]="{$this->main_array["read_rnd_buffer_size"]}M";
		}

		if($this->main_array["sort_buffer"]<>null){
			$mysqld["sort_buffer"]="{$this->main_array["sort_buffer"]}M";
		}	
		if($GLOBALS["AS_ROOT"]){echo "Starting......: Mysql binary version :$this->mysqlvbin";}
		if($this->mysqlvbin>0){
		if($this->mysqlvbin<501030){
			if($this->main_array["table_cache"]<>null){
				$mysqld["table_cache"]="{$this->main_array["table_cache"]}";
		}}}	
		
		if($this->main_array["tmp_table_size"]<>null){
			$mysqld["tmp_table_size"]="{$this->main_array["tmp_table_size"]}M";
		}
		
		if($this->main_array["innodb_additional_mem_pool_size"]<>null){
			$mysqld["innodb_additional_mem_pool_size"]="{$this->main_array["innodb_additional_mem_pool_size"]}M";
		}	
		
		if($this->main_array["max_allowed_packet"]<>null){
			$mysqld["max_allowed_packet"]="{$this->main_array["max_allowed_packet"]}M";
		}	
		
		if($this->main_array["max_connections"]<>null){
			$mysqld["max_connections"]="{$this->main_array["max_connections"]}";
		}

		if($this->main_array["myisam_sort_buffer_size"]<>null){
			$mysqld["myisam_sort_buffer_size"]="{$this->main_array["myisam_sort_buffer_size"]}M";
		}		
		
		if($this->main_array["sort_buffer_size"]<>null){
			$mysqld["sort_buffer_size"]="{$this->main_array["sort_buffer_size"]}M";
		}				
		
		if($this->main_array["net_buffer_length"]<>null){
			$mysqld["net_buffer_length"]="{$this->main_array["net_buffer_length"]}M";
		}	

		if($this->main_array["join_buffer_size"]<>null){
			$mysqld["join_buffer_size"]="{$this->main_array["join_buffer_size"]}M";
		}			
		
		if($this->main_array["read_buffer_size"]<>null){
			$mysqld["read_buffer_size"]="{$this->main_array["read_buffer_size"]}M";
		}	
		if($this->main_array["thread_cache_size"]<>null){
			$mysqld["thread_cache_size"]="{$this->main_array["thread_cache_size"]}";
		}	
		if($this->main_array["query_cache_limit"]<>null){
			$mysqld["query_cache_limit"]="{$this->main_array["query_cache_limit"]}M";
		}		
		
		if($this->main_array["key_buffer_size"]<>null){
			$mysqld["key_buffer_size"]="{$this->main_array["key_buffer_size"]}M";
		}		
		if($this->main_array["query_cache_size"]<>null){
			$mysqld["query_cache_size"]="{$this->main_array["query_cache_size"]}M";
		}	
		if($this->mysqlvbin>0){
			if($this->mysqlvbin>501030){
				if($this->main_array["table_cache"]<>null){
				if($mysql->main_array["table_cache"]==0){$mysql->main_array["table_cache"]=1;}
				$mysqld["table_open_cache"]="{$mysql->main_array["table_cache"]}";
			}}
		}			

		$mysqld["wait_timeout"]="7200";
		$mysqld["connect_timeout"]="10";
		$mysqld["max_connect_errors"]="1000";
		$mysqld["query_cache_type"]="1";
		$mysqld["default-storage-engine"]="MyISAM";
		$mysqld["log-error"]="";
		
		if(count($SpecificConf)>0){
			while (list ($key, $value) = each ($SpecificConf) ){
				if($GLOBALS["VERBOSE"]){echo "mysqld:Personal settings $key = $value\n";}
				$mysqld[$key]=$value;
			}
		}
		
		unset($mysqld["version-string"]);
		
		while (list ($key, $value) = each ($mysqld) ){
			if($GLOBALS["VERBOSE"]){echo "mysqld: Building: $key = $value\n";}
			if(strlen(trim($value))>0){
				$conf=$conf ."$key=$value\n";
			}else{
				$conf=$conf ."$key\n";
			}
		}
		
		
		$conf=$conf ."[mysqldump]\n";
		$conf=$conf ."quick\n";
		$conf=$conf ."quote-names\n";
		$conf=$conf ."max_allowed_packet=16M\n";		

		$conf=$conf ."[mysql]\n";
		if($this->main_array["default-character-set"]<>null){
			$conf=$conf ."default-character-set={$this->main_array["default-character-set"]}\n";
		}		
		$conf=$conf ."\n";
		
		return $conf;
	}
	
	
}
?>