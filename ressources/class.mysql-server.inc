<?php
include_once(dirname(__FILE__). '/class.ini.inc');
include_once(dirname(__FILE__). '/class.sockets.inc');
include_once(dirname(__FILE__). '/class.users.menus.inc');

class mysqlserver{
	
	var $main_array=array();
	var $mysql_version_string=0;
	var $mysqlvbin=0;
	var $innodb_file_per_table=0;
	
	function mysqlserver(){
		$this->load();
		$users=new usersMenus();
		$this->mysql_version_string=$users->mysqld_version;
		
	if(posix_getuid()==0){
			echo "Starting......: Mysql version.......:$this->mysql_version_string\n";
			
		}
		
		if(preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+)#",$this->mysql_version_string,$re)){
			if(strlen($re[1])==1){$re[1]=$re[1].'0';}
			if(strlen($re[2])==1){$re[2]=$re[2].'0';}
			if(strlen($re[3])==1){$re[3]=$re[3].'0';}
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
	
	function Mysql_verlow_config(){
		$this->main_array["max_allowed_packet"]=1;
		$this->main_array["key_buffer"]=16;
		$this->main_array["table_cache"]=1;
		if(posix_getuid()==0){
			echo "Starting......: Mysql binary version:$this->mysqlvbin\n";
		}
		
		$conf="[client]\n";
		$conf=$conf ."port=3306\n";
		$conf=$conf ."socket=/var/run/mysqld/mysqld.sock\n";
		$conf=$conf ."\n";
		$conf=$conf ."[mysqld_safe]\n";
		$conf=$conf ."socket=/var/run/mysqld/mysqld.sock\n";
		$conf=$conf ."nice=0\n";
		$conf=$conf ."\n";
		$conf=$conf ."[mysqld]\n";
		$conf=$conf ."user=mysql\n";
		$conf=$conf ."pid-file=/var/run/mysqld/mysqld.pid\n";
		$conf=$conf ."socket=/var/run/mysqld/mysqld.sock\n";
		$conf=$conf ."port=3306\n";	
		if($this->main_array["bind-address"]<>null){
			$conf=$conf ."bind-address={$this->main_array["bind-address"]}\n";
		}		
		
		if($this->main_array["default-character-set"]<>null){
			$conf=$conf ."default-character-set={$this->main_array["default-character-set"]}\n";
		}
		
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
		$conf=$conf ."\n";
		$conf=$conf ."[mysqld_safe]\n";
		$conf=$conf ."socket=/var/run/mysqld/mysqld.sock\n";
		$conf=$conf ."nice=0\n";
		$conf=$conf ."\n";
		$conf=$conf ."[mysqld]\n";
		$conf=$conf ."user=mysql\n";
		$conf=$conf ."pid-file=/var/run/mysqld/mysqld.pid\n";
		$conf=$conf ."socket=/var/run/mysqld/mysqld.sock\n";
		$conf=$conf ."port=3306\n";
		if($this->main_array["bind-address"]<>null){
			$conf=$conf ."bind-address={$this->main_array["bind-address"]}\n";
		}		
		
		if($this->main_array["default-character-set"]<>null){
			$conf=$conf ."default-character-set={$this->main_array["default-character-set"]}\n";
		}
		
		if($this->main_array["skip-character-set-client-handshake"]=="yes"){
			$conf=$conf ."skip-character-set-client-handshake\n";
		}
		
		$conf=$conf ."basedir=/usr\n";
		$conf=$conf ."datadir=/var/lib/mysql\n";
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
		if($GLOBALS["VERBOSE"]){"mysqld: Personal settings loading ". strlen($datas)." bytes\n";}
		$f=explode("\n",$datas);
		while (list ($i, $line) = each ($f) ){
			if($line==null){continue;}
			if(preg_match("#(.+?)=(.+)#",$line,$re)){
				if($GLOBALS["VERBOSE"]){"mysqld: Personal settings loading {$re[1]}={$re[2]}\n";}
				$SpecificConf[trim(strtolower($re[1]))]=trim($re[2]);
			}else{
				$SpecificConf[trim(strtolower($line))]="";
			}
		}
		
		return $SpecificConf;
		
	}
	
	
	
	function BuildConf(){
		if(!isset($GLOBALS["AS_ROOT"])){if(posix_getuid()==0){$GLOBALS["AS_ROOT"]=true;}}
		if($GLOBALS["AS_ROOT"]){echo "\n";}
		$SpecificConf=$this->GetUsersSpecificConf();
		
		$conf="[client]\n";
		$conf=$conf ."port=3306\n";
		$conf=$conf ."socket=/var/run/mysqld/mysqld.sock\n";
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
		
		if($this->main_array["default-character-set"]<>null){
			$mysqld["default-character-set"]="{$this->main_array["default-character-set"]}";
			$mysqld["character-set-server"]="{$this->main_array["default-character-set"]}";
		}	

		if($this->main_array["skip-character-set-client-handshake"]=="yes"){
			$mysqld["skip-character-set-client-handshake"]="";
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