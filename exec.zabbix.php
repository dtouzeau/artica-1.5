<?php

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__)."/ressources/class.system.network.inc");
include_once(dirname(__FILE__)."/ressources/class.mysql.inc");
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");

if($argv[1]=="--db"){TestDatabase();die();}
if($argv[1]=="--server-conf"){ServerConfig();die();}
if($argv[1]=="--export"){export();die();}

function TestDatabase(){
	
	$sql=new mysql();
	if(!$sql->DATABASE_EXISTS("zabbix")){
		echo "Starting......: Zabbix server daemon creating database\n";
		$sql->CREATE_DATABASE("zabbix");
		CreateTables();
	}
	
	if(!$sql->DATABASE_EXISTS("zabbix")){
		echo "Starting......: Zabbix server daemon creating database FAILED\n";
		die();
	}
	
	
	$TablesCount=TablesCount();
	echo "Starting......: Zabbix server $TablesCount tables\n";
	if($TablesCount<66){CreateTables();}
	echo "Starting......: Zabbix server daemon database success\n";
	UpdateAdmin();
}

function CreateTables(){
	$f[]=sql_file();
	$f[]=data_file();
	$f[]=image_file();
	$q=new mysql();
	$unix=new unix();
	$mysql=$unix->find_program("mysql");
	if($q->mysql_password<>null){$password=" --password=$q->mysql_password ";}
	$cmd="$mysql --port=$q->mysql_port --skip-column-names --database=zabbix --silent --xml ";
	$cmd=$cmd." --user=$q->mysql_admin$password <";
	
	while (list ($num, $file) = each ($f) ){
		if($file==null){continue;}
		echo "Starting......: Zabbix server daemon importing ". basename($file)."\n";
		shell_exec($cmd.$file);
	}	
	
}

function TablesCount(){
	$sql="SHOW TABLES";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"zabbix");
	return mysql_num_rows($results);
	
}

function UpdateAdmin(){
	
	$sock=new sockets();
	$EnableZabbixServer=$sock->GET_INFO("EnableZabbixServer");
	$EnableZabbixAgent=$sock->GET_INFO("EnableZabbixAgent");
	$ZabbixAgentServerIP=$sock->GET_INFO("ZabbixAgentServerIP");
	if($ZabbixAgentServerIP==null){$ZabbixAgentServerIP="127.0.0.1";}
	if($EnableZabbixServer==null){$EnableZabbixServer=1;}
	if($EnableZabbixAgent==null){$EnableZabbixAgent=1;}	
	if($EnableZabbixServer<>1){return null;}		
	
	$ldap=new clladp();
	$users=new usersMenus();
	
	$autologin=",autologin=1";
		$q=new mysql();
	if(!$q->FIELD_EXISTS("users","autologin","zabbix")){$autologin=null;}
	
	$sql="UPDATE users SET alias='$ldap->ldap_admin',
	 name='$ldap->ldap_admin',
	 surname='$ldap->ldap_admin',
	 passwd='".md5($ldap->ldap_password)."' $autologin,type=3
	 WHERE userid=1";

	$q->QUERY_SQL($sql,"zabbix");
	if(!$q->ok){
		echo __FUNCTION__." ".  $q->mysql_error;
	}
	
	$sql="UPDATE hosts SET dns='$users->hostname', useip=1,ip='127.0.0.1',available=1 WHERE hostid=10017";
	
	$q->QUERY_SQL($sql,"zabbix");
	if(!$q->ok){
		echo __FUNCTION__." ". $q->mysql_error;
	}
	
	$sql="SELECT hosttemplateid FROM hosts_templates WHERE  hostid=10017 AND templateid=10001";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"zabbix"));	
	if($ligne["hosttemplateid"]==null){
		$sql="INSERT INTO `hosts_templates` (`hosttemplateid`, `hostid`, `templateid`) VALUES (1, 10017, 10001);";
		$q->QUERY_SQL($sql,"zabbix");
		if(!$q->ok){echo __FUNCTION__." ". $q->mysql_error;}		
	}
	
	
}

function ServerConfig(){
	$unix=new unix();
	$q=new mysql();
	@mkdir("/var/run/zabbix-server",0755,true);
	@mkdir("/var/log/zabbix-server",0666,true);
	if(!is_file("/var/log/zabbix-server/zabbix_server.log")){
		@file_put_contents("/var/log/zabbix-server/zabbix_server.log","~");
	}
	@chown("/var/log/zabbix-server/zabbix_server.log","zabbix");
	@chown("/var/run/zabbix-server","zabbix");
	
	$sock=new sockets();
	$EnableZabbixServer=$sock->GET_INFO("EnableZabbixServer");
	$EnableZabbixAgent=$sock->GET_INFO("EnableZabbixAgent");
	$ZabbixAgentServerIP=$sock->GET_INFO("ZabbixAgentServerIP");
	if($ZabbixAgentServerIP==null){$ZabbixAgentServerIP="127.0.0.1";}
	if($EnableZabbixServer==null){$EnableZabbixServer=1;}
	if($EnableZabbixAgent==null){$EnableZabbixAgent=1;}		
if($EnableZabbixServer==1){	
		$f[]="#NodeID=0";
		$f[]="#StartPollers=5";
		$f[]="#StartPollersUnreachable=1";
		$f[]="#StartTrappers=5";
		$f[]="#StartPingers=1";
		$f[]="#StartDiscoverers=1";
		$f[]="#StartHTTPPollers=1";
		$f[]="#ListenPort=10051";
		$f[]="#ListenIP=127.0.0.1";
		$f[]="#HousekeepingFrequency=1";
		$f[]="SenderFrequency=30";
		$f[]="#DisableHousekeeping=1";
		$f[]="DebugLevel=3";
		$f[]="Timeout=5";
		$f[]="#TrapperTimeout=5";
		$f[]="#UnreachablePeriod=45";
		$f[]="#UnavailableDelay=15";
		$f[]="#UnavailableDelay=60";
		$f[]="PidFile=".ValueData("PidFile");
		$f[]="LogFile=".ValueData("LogFile");
		$f[]="#LogFileSize=1";
		$f[]="AlertScriptsPath=".ValueData("AlertScriptsPath");
		$f[]="FpingLocation=".$unix->find_program("fping");
		$f[]="#PingerFrequency=60";
		$f[]="DBHost=$q->mysql_server";
		$f[]="DBName=zabbix";
		$f[]="DBUser=$q->mysql_admin";
		$f[]="DBPassword=$q->mysql_password";
		$f[]="#DBSocket=/tmp/mysql.sock";
		@file_put_contents(server_conf_path(),implode("\n",$f));	
		echo "Starting......: Zabbix server configuration file done\n";
		
		unset($f);
		$f[]="<?php";
		$f[]="global \$DB_TYPE, \$DB_SERVER, \$DB_PORT, \$DB_DATABASE, \$DB_USER, \$DB_PASSWORD, \$IMAGE_FORMAT_DEFAULT;";
		$f[]="";
		$f[]="\$DB[\"TYPE\"]      = \"mysql\";";
		$f[]="\$DB[\"SERVER\"]    = \"$q->mysql_server\";";
		$f[]="\$DB[\"PORT\"]      = \"$q->mysql_port\";";
		$f[]="\$DB[\"DATABASE\"]  = \"zabbix\";";
		$f[]="\$DB[\"USER\"]      = \"$q->mysql_admin\";";
		$f[]="\$DB[\"PASSWORD\"]  = \"$q->mysql_password\";";
		$f[]="\$ZBX_SERVER      = \"127.0.0.1\";";
		$f[]="\$ZBX_SERVER_PORT = \"10051\";";
		$f[]="\$DB_TYPE	= \"MYSQL\";";
		$f[]="\$DB_SERVER	= \"localhost\";";
		$f[]="\$DB_PORT	= \"0\";";
		$f[]="\$DB_DATABASE	= \"zabbix\";";
		$f[]="\$DB_USER	= \"admin\";";
		$f[]="\$DB_PASSWORD	= \"secret\";";		
		$f[]="";
		$f[]="\$IMAGE_FORMAT_DEFAULT    = IMAGE_FORMAT_PNG;";
		$f[]="";
		$f[]="";
		$f[]="## dont remove this!";
		$f[]="## This is a work-around for dbconfig-common";
		$f[]="if(\$DB[\"TYPE\"] == \"mysql\") ";
		$f[]="	\$DB[\"TYPE\"] = \"MYSQL\";";
		$f[]="";
		$f[]="if(\$DB[\"TYPE\"] == \"pgsql\")";
		$f[]="	\$DB[\"TYPE\"] = \"POSTGRESQL\";";
		$f[]="##";
		$f[]="?>";
		@file_put_contents("/usr/share/zabbix/conf/zabbix.conf.php",implode("\n",$f));
		echo "Starting......: Zabbix web configuration file done\n";
		shell_exec($unix->find_program("ln")." -s /usr/share/zabbix /usr/share/artica-postfix/zabbix >/dev/null 2>&1");		
	}
unset($f);



if($EnableZabbixAgent==1){
		$f[]="Server=$ZabbixAgentServerIP";
		$f[]="Timeout=3";
		$f[]="UserParameter=mysql.ping,mysqladmin -u{$q->mysql_admin} -p{$q->mysql_password} ping|grep alive|wc -l";
		$f[]="UserParameter=mysql.uptime,mysqladmin -u{$q->mysql_admin} -p{$q->mysql_password} status|cut -f2 -d\":\"|cut -f1 -d\"T\"";
		$f[]="UserParameter=mysql.threads,mysqladmin -u{$q->mysql_admin} -p{$q->mysql_password} status|cut -f3 -d\":\"|cut -f1 -d\"Q\"";
		$f[]="UserParameter=mysql.questions,mysqladmin -u{$q->mysql_admin} -p{$q->mysql_password} status|cut -f4 -d\":\"|cut -f1 -d\"S\"";
		$f[]="UserParameter=mysql.slowqueries,mysqladmin -u{$q->mysql_admin} -p{$q->mysql_password} status|cut -f5 -d\":\"|cut -f1 -d\"O\"";
		$f[]="UserParameter=mysql.qps,mysqladmin -u{$q->mysql_admin} -p{$q->mysql_password} status|cut -f9 -d\":\"";
		$f[]="UserParameter=mysql.version,mysql -V";
		@file_put_contents("/etc/zabbix/zabbix_agent.conf",implode("\n",$f));	
		echo "Starting......: Zabbix agent configuration file done\n";
		unset($f);
		$user=new usersMenus();
		$f[]="Server=$ZabbixAgentServerIP";
		$f[]="#ServerPort=10051";
		$f[]="Hostname=$user->hostname";
		$f[]="#ListenPort=10050";
		$f[]="#ListenIP=127.0.0.1";
		$f[]="StartAgents=5";
		$f[]="#RefreshActiveChecks=120";
		$f[]="#DisableActive=1";
		$f[]="#EnableRemoteCommands=1";
		$f[]="# Specifies debug level";
		$f[]="# 0 - debug is not created";
		$f[]="# 1 - critical information";
		$f[]="# 2 - error information";
		$f[]="# 3 - warnings";
		$f[]="# 4 - information (default)";
		$f[]="# 5 - for debugging (produces lots of information)";
		$f[]="";
		$f[]="DebugLevel=3";
		$f[]="PidFile=".ValueDataAgent('PidFile');
		$f[]="LogFile=".ValueDataAgent('LogFile');
		$f[]="#LogFileSize=1";
		$f[]="Timeout=3";
		@file_put_contents("/etc/zabbix/zabbix_agentd.conf",implode("\n",$f));
		echo "Starting......: Zabbix agent configuration daemon file done\n";
	}

}


function sql_file(){
	if(is_file("/usr/share/zabbix-server/mysql.sql")){return "/usr/share/zabbix-server/mysql.sql";}
	if(is_file("/usr/share/doc/zabbix-1.4.6/dbinit/schema/mysql.sql")){return "/usr/share/doc/zabbix-1.4.6/dbinit/schema/mysql.sql";}
	if(is_file("/usr/share/doc/zabbix/create/schema/mysql.sql")){return "/usr/share/doc/zabbix/create/schema/mysql.sql";}
	}

function data_file(){
	if(is_file("/usr/share/zabbix-server/data.sql")){return "/usr/share/zabbix-server/data.sql";}
	if(is_file("/usr/share/doc/zabbix-1.4.6/dbinit/data/data.sql")){return "/usr/share/doc/zabbix-1.4.6/dbinit/data/data.sql";}
	if(is_file("/usr/share/doc/zabbix/create/data/data.sql")){return "/usr/share/doc/zabbix/create/data/data.sql";}
	}

function image_file(){
	if(is_file("/usr/share/doc/zabbix-1.4.6/dbinit/data/images_mysql.sql")){return "/usr/share/doc/zabbix-1.4.6/dbinit/data/images_mysql.sql";}
	if(is_file("/usr/share/doc/zabbix/create/data/images_mysql.sql")){return "/usr/share/doc/zabbix/create/data/images_mysql.sql";}
	}
	
function ValueData($key){
	$f=explode("\n",@file_get_contents(server_conf_path()));
	while (list ($num, $line) = each ($f) ){
		if(preg_match("#^$key(.+)#",$line,$re)){
			$re[1]=str_replace("=","",$re[1]);
			return trim($re[1]);
		}
	}
	
}
	function ValueDataAgent($key){
	$f=explode("\n",@file_get_contents("/etc/zabbix/zabbix_agentd.conf"));
	while (list ($num, $line) = each ($f) ){
		if(preg_match("#^$key(.+)#",$line,$re)){
			$re[1]=str_replace("=","",$re[1]);
			return trim($re[1]);
		}
	}
	
}	
function server_conf_path(){
	if(is_file("/etc/zabbix/zabbix_server.conf")){return "/etc/zabbix/zabbix_server.conf";}
	return "/etc/zabbix/zabbix_server.conf";
	
}


function export(){
	$File="/home/dtouzeau/export.csv";
	$q=new mysql();
	$sql="SELECT NOM,PRENOM,EMAIL,TEL_DIRECT,CP,VILLE,addresse FROM contacts";
		$results=$q->QUERY_SQL($sql,"kasperskyinfo ");
		$fh = fopen($File, 'w') or die("can't open file");
		
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			echo "{$ligne["EMAIL"]}\n";
			$datas="\"\",\"{$ligne["PRENOM"]}\",\"{$ligne["NOM"]}\",\"{$ligne["EMAIL"]}\",\"{$ligne["TEL_DIRECT"]}\",\"{$ligne["VILLE"]}\",\"{$ligne["CP"]}\",,\"{$ligne["addresse"]}\"\n";
			fwrite($fh, $datas);
		}
			
	fclose($fh);
	
}

?>