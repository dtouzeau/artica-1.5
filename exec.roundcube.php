<?php
if(!is_file(dirname(__FILE__) .  '/ressources/settings.inc')){die("Unable to stat ".dirname(__FILE__) . '/ressources/settings.inc');}
include_once(dirname(__FILE__) . '/ressources/settings.inc');
include_once(dirname(__FILE__) . '/ressources/class.ldap.inc');
include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
include_once(dirname(__FILE__) . '/ressources/class.roundcube.inc');
include_once(dirname(__FILE__) . '/ressources/class.apache.inc');
include_once(dirname(__FILE__) . '/framework/class.unix.inc');
include_once(dirname(__FILE__) . '/framework/frame.class.inc');

if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
if($GLOBALS["VERBOSE"]){ini_set('html_errors',0);ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);}
$bd="roundcubemail";
$GLOBALS["MYSQL_DB"]=$bd;	


if($argv[1]=="--sieverules"){plugin_sieverules();die();}
if($argv[1]=="--calendar"){plugin_calendar();die();}
if($argv[1]=="--database"){check_databases($bd);die();}
if($argv[1]=="--contextmenu"){plugin_contextmenu();die();}
if($argv[1]=="--build"){build();die();}
if($argv[1]=="--addressbook"){plugin_globaladdressbook();die();}
if($argv[1]=="--verifyTables"){verifyTables();die();}
if($argv[1]=="--hacks"){RoundCubeHacks();die();}
if($argv[1]=="--tableslist"){RoundCubeMysqlTablesList();die();}





if(!$_GLOBAL["roundcube_installed"]){die("Roundcube is not installed, aborting");}

$pid=getmypid();
$pidfile="/etc/artica-postfix/".basename(__FILE__).".pid";

$unix=new unix();
if($unix->process_exists($pid)){die();}
@file_put_contents($pidfile,$pid);

$mailhost=$_GLOBAL["fqdn_hostname"];
echo "Get user list....\n";

$ldap=new clladp();
$users=$ldap->Hash_GetALLUsers();

echo count($users)." user(s) to scan\n";


if(!is_array($users)){
	writelogs("No users stored in local database, aborting ","MAIN",__FILE__,__LINE__);
	die();
}

$q=new mysql();
while (list ($num, $val) = each ($users) ){
		usleep(400000);
		$user_id=GetidFromUser($bd,$num);
		echo " user \"$num\" $val user_id=$user_id\n";
		$sql="UPDATE identities SET `email`='$val', `reply-to`='$val' WHERE name='$num';";
		echo $sql."\n";
		$q->QUERY_SQL($sql,$bd);	
		if(!$q->ok){echo "$sql \n$q->mysql_error\n";}	
		
		if($user_id==0){
			CreateRoundCubeUser($bd,$num,$val,'127.0.0.1');
			$user_id=GetidFromUser($bd,$num);
		}
		
		if($user_id==0){continue;}
		$identity_id=GetidentityFromuser_id($bd,$user_id);
		if($identity_id==0){
			CreateRoundCubeIdentity($bd,$user_id,$num,$val);
			$identity_id=GetidentityFromuser_id($bd,$user_id);
			}
		
		if($identity_id==0){continue;}
		
		$count=$count+1;
		UpdateRoundCubeIdentity($bd,$identity_id,$val);
		
		
		
		
}

echo "\n\nsuccess ".$count." user(s) updated\n";

function GetidFromUser($bd,$uid){
	$sql="SELECT user_id FROM users where username='$uid'";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,$bd);
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				$userid[]=$ligne["user_id"];
	}
	
	if(!is_array($userid)){return 0;}else{return $userid[0];}
			
	
	
}

function build(){
	$unix=new unix();
	$rm=$unix->find_program("rm");
	if(is_dir("/usr/share/roundcube/plugins/remember_me")){shell_exec("$rm -rf /usr/share/roundcube/plugins/remember_me");}
	
	
	$r=new roundcube();
	$conf=$r->RoundCubeConfig();
	if(is_file("/var/log/lighttpd/roundcube-access.log")){@unlink("/var/log/lighttpd/roundcube-access.log");}
	if(is_file("/var/log/lighttpd/roundcube-error.log")){@unlink("/var/log/lighttpd/roundcube-error.log");}
	$users=new usersMenus();
	$roundcube_folder=$users->roundcube_folder;
	if(!@file_put_contents("$roundcube_folder/config/main.inc.php",$conf)){
		echo "Starting......: Roundcube saving main.inc.php failed.\n";
	}else{
		echo "Starting......: Roundcube saving main.inc.php Success.\n";
	}	
	
	echo "Starting......: Roundcube building main configuration done.\n";
	RoundCubeHacks();
}


function CreateRoundCubeUser($bd,$user_id,$email,$mailhost){
	$date=date('Y-m-d H:i:s');
	$sql="INSERT INTO `users` (`username`, `mail_host`, `language`,`created`) VALUES 
	('$user_id','127.0.0.1','en_US','$date');
	";
	$q=new mysql();
	$q->QUERY_SQL($sql,$bd);
	if(!$q->ok){
		echo $q->mysql_error."\n";
	}
	
}

function CreateRoundCubeIdentity($bd,$user_id,$num,$val){
	$sql="INSERT INTO `identities` (`user_id`, `del`, `standard`, `name`, `organization`, `email`, `reply-to`) VALUES ('$user_id','0','1','$num','','$val','$val');";
	$q=new mysql();
	$q->QUERY_SQL($sql,$bd);
}


function GetidentityFromuser_id($bd,$user_id){
	$sql="SELECT identity_id FROM identities where user_id='$user_id'";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,$bd);
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				$id[]=$ligne["identity_id"];
	}
	
	if(!is_array($id)){return 0;}else{return $id[0];}
}

function UpdateRoundCubeIdentity($bd,$identity_id,$val){
	echo "Update $identity_id to $val\n";
	$sql="UPDATE identities SET email='$val', `reply-to`='$val' WHERE identity_id='$identity_id'";
	$q=new mysql();
	$q->QUERY_SQL($sql,$bd);
	
}

function plugin_sieverules(){
	$users=new usersMenus();
	if(!$users->roundcube_installed){
		writelogs("RoundCube is not installed",__FUNCTION__,__FILE__,__LINE__);
		return ;
	}
	
	$dir=$users->roundcube_folder."/plugins";
	if(!is_dir($dir)){
		writelogs("Unable to stat directory '$dir'",__FUNCTION__,__FILE__,__LINE__);
		return ;		
	}
	writelogs("Roundcube plugins: $dir",__FUNCTION__,__FILE__,__LINE__);
	
	writelogs("remove $dir/sieverules",__FUNCTION__,__FILE__,__LINE__);
	shell_exec("/bin/rm -rf $dir/sieverules >/dev/null 2>&1");
	writelogs("Installing in $dir/sieverules",__FUNCTION__,__FILE__,__LINE__);
	@mkdir("$dir/sieverules",0755,true);
	shell_exec("/bin/cp -rf /usr/share/artica-postfix/bin/install/roundcube/sieverules/* $dir/sieverules/");
	shell_exec("/bin/chmod -R 755 $dir/sieverules");
	writelogs("Installing in $dir/sieverules done...",__FUNCTION__,__FILE__,__LINE__);
	
	///usr/share/roundcube/plugins
	
	
}

function plugin_contextmenu(){
	$users=new usersMenus();
	if(!$users->roundcube_installed){
		writelogs("RoundCube is not installed",__FUNCTION__,__FILE__,__LINE__);
		return ;
	}
	
	$dir=$users->roundcube_folder."/plugins";
	if(!is_dir($dir)){
		writelogs("Unable to stat directory '$dir'",__FUNCTION__,__FILE__,__LINE__);
		return ;		
	}
	writelogs("Roundcube plugins: $dir",__FUNCTION__,__FILE__,__LINE__);
	if(!is_file("$dir/contextmenu/contextmenu.php")){
		writelogs("Installing in $dir/contextmenu",__FUNCTION__,__FILE__,__LINE__);
		@mkdir("$dir/contextmenu",0755,true);
		shell_exec("/bin/cp -rf /usr/share/artica-postfix/bin/install/roundcube/contextmenu/* $dir/contextmenu/");
	}
	

	shell_exec("/bin/chmod -R 755 $dir/contextmenu");
	writelogs("Installing in $dir/contextmenu done...",__FUNCTION__,__FILE__,__LINE__);
	
	///usr/share/roundcube/plugins
}


function plugin_globaladdressbook(){
	include_once(dirname(__FILE__) . '/ressources/class.apache.inc');
	$users=new usersMenus();
	if(!$users->roundcube_installed){
		writelogs("RoundCube is not installed",__FUNCTION__,__FILE__,__LINE__);
		return ;
	}
	
	$dir=$users->roundcube_folder."/plugins";
	if(!is_dir($dir)){
		writelogs("Unable to stat directory '$dir'",__FUNCTION__,__FILE__,__LINE__);
		return ;		
	}
	writelogs("Roundcube plugins: $dir",__FUNCTION__,__FILE__,__LINE__);
	if(!is_file("$dir/globaladdressbook/globaladdressbook.php")){
		writelogs("Installing in $dir/globaladdressbook",__FUNCTION__,__FILE__,__LINE__);
		@mkdir("$dir/globaladdressbook",0755,true);
		shell_exec("/bin/cp -rf /usr/share/artica-postfix/bin/install/roundcube/globaladdressbook/* $dir/globaladdressbook/");
		
	}
	

	
	$r=new roundcube_globaladdressbook("MAIN_INSTANCE");
	$config=$r->BuildConfig();
	@file_put_contents("$dir/globaladdressbook/config.inc.php",$config);
	$q=new mysql();
	$q->checkRoundCubeTables($GLOBALS["MYSQL_DB"]);
	
	shell_exec("/bin/chmod -R 755 $dir/globaladdressbook");
	shell_exec("/bin/chmod -R 770 $dir/plugins/globaladdressbook");
	shell_exec("/bin/chmod 660 $dir/plugins/globaladdressbook/*.php");
	
	writelogs("Installing in $dir/globaladdressbook done...",__FUNCTION__,__FILE__,__LINE__);	
	plugin_contextmenu();

	
	
}


function plugin_calendar(){
	$users=new usersMenus();
	if(!$users->roundcube_installed){
		writelogs("RoundCube is not installed",__FUNCTION__,__FILE__,__LINE__);
		return ;
	}
	
	$dir=$users->roundcube_folder."/plugins";
	if(!is_dir($dir)){
		writelogs("Unable to stat directory '$dir'",__FUNCTION__,__FILE__,__LINE__);
		return ;		
	}
	writelogs("Roundcube plugins: $dir",__FUNCTION__,__FILE__,__LINE__);
	
	writelogs("remove $dir/sieverules",__FUNCTION__,__FILE__,__LINE__);
	shell_exec("/bin/rm -rf $dir/calendar >/dev/null 2>&1");
	writelogs("Installing in $dir/calendar",__FUNCTION__,__FILE__,__LINE__);
	@mkdir("$dir/calendar",0755,true);
	shell_exec("/bin/cp -rf /usr/share/artica-postfix/bin/install/roundcube/calendar/* $dir/calendar/");
	shell_exec("/bin/chmod -R 755 $dir/calendar");
	
	
	$sql="CREATE TABLE `events` (
  `event_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `start` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `end` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `summary` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `category` varchar(255) NOT NULL DEFAULT '',
  `all_day` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY(`event_id`),
  CONSTRAINT `user_id_fk_events` FOREIGN KEY (`user_id`)
    REFERENCES `users`(`user_id`)
    /*!40008
      ON DELETE CASCADE
      ON UPDATE CASCADE */
)";
	
	$q=new mysql();
	$q->QUERY_SQL($sql,$GLOBALS["MYSQL_DB"]);
	
	writelogs("Installing in $dir/calendar done...",__FUNCTION__,__FILE__,__LINE__);
	
	///usr/share/roundcube/plugins
	
	
}

function check_databases($bd){
	include_once(dirname(__FILE__)."/ressources/class.os.system.inc");
	if(systemMaxOverloaded()){die();}
	$q=new mysql();
	$q->checkRoundCubeTables($bd);
	
}

function verifyTables(){
	$mysqlfile="/usr/share/roundcube/SQL/mysql.initial.sql";
	if(!is_file($mysqlfile)){return null;}
	$q=new mysql();
	$users=new usersMenus();
	$f=RoundCubeMysqlTablesList();
	
	$unix=new unix();
	$mysqlbin=$unix->find_program("mysql");
	$token[]="--host=$users->mysql_server";
	if($users->mysql_admin<>null){$token[]="--user=$users->mysql_admin";}
	if($users->mysql_password<>null){$token[]="--password=$users->mysql_password";}
	
	$token[]="--database={$GLOBALS["MYSQL_DB"]}";
	$token[]="--silent";
	
	$cmdline="$mysqlbin ". @implode(" ",$token);
	
	
	$verif=true;
	while (list ($num, $table) = each ($f) ){
		if(!$q->TABLE_EXISTS($table,$GLOBALS["MYSQL_DB"])){
			echo "\"$table\" no such table in {$GLOBALS["MYSQL_DB"]}\n";
			$verif=false;
		}
	}
	
if(!$verif){
	$initial=$cmdline." < ". $users->roundcube_folder."/SQL/mysql.initial.sql";
	shell_exec($initial);
	if($GLOBALS["VERBOSE"]){echo "$initial\n";}
	$update=$cmdline." < ". $users->roundcube_folder."/SQL/mysql.update.sql";
	shell_exec($update);
	if($GLOBALS["VERBOSE"]){echo "$update\n";}
	return;
}	

	unset($f);
	$f[]="contactgroupmembers";
	$f[]="contactgroups";
	$verif=true;
	while (list ($num, $table) = each ($f) ){
		if(!$q->TABLE_EXISTS($table,$GLOBALS["MYSQL_DB"])){
			echo "\"$table\" no such table in {$GLOBALS["MYSQL_DB"]}\n";
			$verif=false;
		}
	}	
	
if(!$verif){
	$update=$cmdline." < ". $users->roundcube_folder."/SQL/mysql.update.sql";
	$q->QUERY_SQL($update,$GLOBALS["MYSQL_DB"]);
	shell_exec($update);
	if($GLOBALS["VERBOSE"]){echo "$update\n";}
	return;
}	
	echo "All are ok, nothing to do...\n";
	
}


function RoundCubeHacks(){
	$sock=new sockets();
	$unix=new unix();
	$unix->IPTABLES_DELETE_REGEX_ENTRIES("RoundCubeHacks");
	$RoundCubeHackEnabled=$sock->GET_INFO("RoundCubeHackEnabled");
	if($RoundCubeHackEnabled==null){$RoundCubeHackEnabled=1;}
	if($RoundCubeHackEnabled==0){echo "Starting......: Roundcube anti-hack is disabled\n";return;}
	$RoundCubeHackConfig=unserialize(base64_decode($sock->GET_INFO("RoundCubeHackConfig")));
	if(!is_array($RoundCubeHackConfig)){if($GLOBALS["VERBOSE"]){echo "RoundCubeHacks:: Not an array::RoundCubeHackConfig\n";}return;}
	if(count($RoundCubeHackConfig)==0){if($GLOBALS["VERBOSE"]){echo "RoundCubeHacks:: O rows\n";}return;}
	
		
	if($GLOBALS["VERBOSE"]){echo "RoundCubeHacks:: array of ". count($RoundCubeHackConfig)." rows\n";}
	while (list ($instance, $conf) = each ($RoundCubeHackConfig) ){
		if(!is_array($conf)){continue;}
		while (list ($ip, $enabled) = each ($conf) ){
			if($GLOBALS["VERBOSE"]){echo "RoundCubeHacks:: instance $instance [$ip] enabled=$enabled\n";}
			if(!$enabled){continue;}
			if($instance=="master"){$iptables[]=RoundCubeHacks_master($ip);continue;}
			$iptables[]=RoundCubeHacks_vhosts($instance,$ip);
			}
	}

	if($GLOBALS["VERBOSE"]){echo "RoundCubeHacks:: array of ". count($iptables)." iptables commands\n";}
	if(count($iptables)==0){return;}
	
	$unix=new unix();
	$iptables_bin=$unix->find_program("iptables");
	if(!is_file($iptables_bin)){
		if($GLOBALS["VERBOSE"]){echo "RoundCubeHacks:: no iptables installed, aborting\n";}
		return;
	}
	
	echo "Starting......: Roundcube anti-hack ". count($iptables)." iptables rule(s)\n";
	while (list ($num, $cmd) = each ($iptables) ){
		$cmd="$iptables_bin $cmd";
		if($GLOBALS["VERBOSE"]){echo "RoundCubeHacks:: $cmd\n";}
		shell_exec("$cmd >/dev/null 2>&1");
		
	}
	
	
	
}

function RoundCubeHacks_master($ip){
	if($GLOBALS["LIGHTTPD_PORT"]==null){
		$unix=new unix();
		$GLOBALS["LIGHTTPD_PORT"]=$unix->LIGHTTPD_PORT();
		$GLOBALS["LIGHTTPD_PORT_ROUNDCUBE"]=$unix->LIGHTTPD_PORT("/etc/artica-postfix/lighttpd-roundcube.conf");
		
		
	}
	
	if($GLOBALS["LIGHTTPD_PORT_ROUNDCUBE"]==null){$GLOBALS["LIGHTTPD_PORT_ROUNDCUBE"]=443;}
	
	if($GLOBALS["VERBOSE"]){echo "RoundCubeHacks_master:: Artica port: {$GLOBALS["LIGHTTPD_PORT"]}: Roundcube instance port:{$GLOBALS["LIGHTTPD_PORT_ROUNDCUBE"]} \n";}
	return "-A INPUT -s $ip -p tcp -m multiport --dport {$GLOBALS["LIGHTTPD_PORT"]},{$GLOBALS["LIGHTTPD_PORT_ROUNDCUBE"]} -j DROP -m comment --comment \"RoundCubeHacks\"";
}

function RoundCubeHacks_vhosts($instance,$ip){
	
	if($GLOBALS["ApacheGroupWarePort"]==null){
		$sock=new sockets();
		$GLOBALS["ApacheGroupWarePort"]=$sock->GET_INFO("ApacheGroupWarePort");
	}
	if($GLOBALS["VERBOSE"]){echo "RoundCubeHacks_vhosts:: $instance port = {$GLOBALS["ApacheGroupWarePort"]}\n";}
	return "-A INPUT -s $ip -p tcp --dport {$GLOBALS["ApacheGroupWarePort"]} -j DROP -m comment --comment \"RoundCubeHacks\"";
	
	
}


function RoundCubeMysqlTablesList(){
	$mysqlfile="/usr/share/roundcube/SQL/mysql.initial.sql";
	
	if(!is_file($mysqlfile)){
		echo "$mysqlfile No such file";
		return;
	}
	
	$f=explode("\n", @file_get_contents($mysqlfile));
	while (list ($index, $line) = each ($f) ){
		if(preg_match("#CREATE TABLE[\s+|`]+(.+?)[\s+|`]#", $line,$re)){
			$array[]=$re[1];
		}
	}
	return $array;
	
}

	

			
			









?>