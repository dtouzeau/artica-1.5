<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");
include_once(dirname(__FILE__).'/ressources/class.cluebringer.inc');
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');

if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["debug"]=true;$GLOBALS["VERBOSE"]=true;}
if(preg_match("#--reload#",implode(" ",$argv))){$GLOBALS["RELOAD"]=true;$GLOBALS["RESTART"]=true;}

if($argv[1]=="--build"){build();die();}
if($argv[1]=="--sql"){testsDatabase();die();}
if($argv[1]=="--internal-domains"){testsDatabase();internal_domains();die();}
if($argv[1]=="--passwords"){web_password();die();}



function build(){
testsDatabase();
internal_domains();	
$q=new mysql();	

$sock=new sockets();
$Params=unserialize(base64_decode($sock->GET_INFO("ClubringerMasterConf")));

if($Params["perfs"]==null){$Params["perfs"]=1;}


$conf[]="# Server configuration";
$conf[]="#";
$conf[]="[server]";
$conf[]="# Protocols to load";
$conf[]="protocols=<<EOT";
$conf[]="Postfix";
$conf[]="EOT";
$conf[]="";
$conf[]="modules=<<EOT";
$conf[]="Core";
$conf[]="AccessControl";
$conf[]="CheckHelo";
$conf[]="CheckSPF";
$conf[]="Greylisting";
$conf[]="Quotas";
$conf[]="EOT";
$conf[]="";
$conf[]="# User to run this daemon as";
$conf[]="user=root";
$conf[]="group=root";
$conf[]="pid_file=/var/run/cbpolicyd.pid";
$conf[]="";
$conf[]="# Uncommenting the below option will prevent cbpolicyd going into the background";
$conf[]="#background=no";
$conf[]="";
$conf[]="# Preforking configuration";
$conf[]="#";
$conf[]="# min_server		- Minimum servers to keep around";
$conf[]="# min_spare_servers	- Minimum spare servers to keep around ready to ";
$conf[]="# 			  handle requests";
$conf[]="# max_spare_servers	- Maximum spare servers to have around doing nothing";
$conf[]="# max_servers		- Maximum servers alltogether";
$conf[]="# max_requests		- Maximum number of requests each child will serve";
$conf[]="#";
$conf[]="# One may want to use the following as a rough guideline...";



$conf[]="#";
if($Params["perfs"]==0){
	$conf[]="# Small mailserver:  2, 2, 4, 10, 1000";
	$conf[]="min_servers=2";
	$conf[]="min_spare_servers=2";
	$conf[]="max_spare_servers=4";
	$conf[]="max_servers=10";
	$conf[]="max_requests=1000";
}


if($Params["perfs"]==1){
	$conf[]="# Medium mailserver: 4, 4, 12, 25, 1000";
	$conf[]="min_servers=4";
	$conf[]="min_spare_servers=4";
	$conf[]="max_spare_servers=12";
	$conf[]="max_servers=25";
	$conf[]="max_requests=1000";
}

if($Params["perfs"]==2){
	$conf[]="# Large mailserver: 8, 8, 16, 64, 1000";
	$conf[]="min_servers=8";
	$conf[]="min_spare_servers=8";
	$conf[]="max_spare_servers=16";
	$conf[]="max_servers=64";
	$conf[]="max_requests=1000";
}
$conf[]="";
$conf[]="";
$conf[]="";
$conf[]="# Log level:";
$conf[]="# 0 - Errors only";
$conf[]="# 1 - Warnings and errors";
$conf[]="# 2 - Notices, warnings, errors";
$conf[]="# 3 - Info, notices, warnings, errors";
$conf[]="# 4 - Debugging ";
$conf[]="log_level=2";
$conf[]="";
$conf[]="# File to log to instead of stdout";
$conf[]="#log_file=/var/log/cbpolicyd.log";
$conf[]="";
$conf[]="# Log destination for mail logs...";
$conf[]="# main		- Default. Log to policyd's main log mechanism, accepts NO args";
$conf[]="# syslog	- log mail via syslog";
$conf[]="#			format: log_mail=facility@method,args";
$conf[]="#";
$conf[]="# Valid methods for syslog:";
$conf[]="# native	- Let Sys::Syslog decide";
$conf[]="# unix		- Unix socket";
$conf[]="# udp		- UDP socket";
$conf[]="# stream	- Stream (for Solaris)";
$conf[]="#";
$conf[]="# Example: unix native";
$conf[]="log_mail=mail@syslog:native";
$conf[]="#";
$conf[]="# Example: unix socket ";
$conf[]="#log_mail=mail@syslog:unix";
$conf[]="#";
$conf[]="# Example: udp";
$conf[]="#log_mail=mail@syslog:udp,127.0.0.1";
$conf[]="#";
$conf[]="# Example: Solaris ";
$conf[]="#log_mail=local0@syslog:stream,/dev/log";
$conf[]="#log_mail=maillog";
$conf[]="";
$conf[]="# Things to log in extreme detail";
$conf[]="# modules 	- Log detailed module running information";
$conf[]="# tracking 	- Log detailed tracking information";
$conf[]="# policies 	- Log policy resolution";
$conf[]="# protocols 	- Log general protocol info, but detailed";
$conf[]="# bizanga 	- Log the bizanga protocol";
$conf[]="#";
$conf[]="# There is no default for this configuration option. Options can be";
$conf[]="# separated by commas. ie. protocols,modules";
$conf[]="#";
$conf[]="#log_detail=";
$conf[]="";
$conf[]="# IP to listen on, * for all";
$conf[]="host=127.0.0.1";
$conf[]="";
$conf[]="# Port to run on";
$conf[]="port=13331";
$conf[]="";
$conf[]="# Timeout in communication with clients";
$conf[]="timeout=120";
$conf[]="";
$conf[]="# cidr_allow/cidr_deny";
$conf[]="# Comma, whitespace or semi-colon separated. Contains a CIDR block to ";
$conf[]="# compare the clients IP to.  If cidr_allow or cidr_deny options are ";
$conf[]="# given, the incoming client must match a cidr_allow and not match a ";
$conf[]="# cidr_deny or the client connection will be closed.";
$conf[]="#cidr_allow=0.0.0.0/0";
$conf[]="#cidr_deny=";
$conf[]="";
$conf[]="";
$conf[]="";
$conf[]="[database]";
$conf[]="#DSN=DBI:SQLite:dbname=policyd.sqlite";
$conf[]="DSN=DBI:mysql:database=policyd;host=$q->mysql_server";
$conf[]="Username=$q->mysql_admin";
$conf[]="Password=$q->mysql_password";
$conf[]="#";
$conf[]="";
$conf[]="# What do we do when we have a database connection problem";
$conf[]="# tempfail	- Return temporary failure";
$conf[]="# pass		- Return success";
$conf[]="bypass_mode=tempfail";
$conf[]="";
$conf[]="# How many seconds before we retry a DB connection";
$conf[]="bypass_timeout=30";
$conf[]="";
$conf[]="";
$conf[]="";
$conf[]="# Access Control module";
$conf[]="[AccessControl]";
$conf[]="enable=1";
$conf[]="";
$conf[]="";
$conf[]="# Greylisting module";
$conf[]="[Greylisting]";
$conf[]="enable=1";
$conf[]="";
$conf[]="";
$conf[]="# CheckHelo module";
$conf[]="[CheckHelo]";
$conf[]="enable=1";
$conf[]="";
$conf[]="";
$conf[]="# CheckSPF module";
$conf[]="[CheckSPF]";
$conf[]="enable=1";
$conf[]="";
$conf[]="";
$conf[]="# Quotas module";
$conf[]="[Quotas]";
$conf[]="enable=1";
$conf[]="";
$conf[]="";
	
@mkdir("/etc/cluebringer",666,true);
@file_put_contents("/etc/cluebringer/cluebringer.conf",@implode("\n",$conf));
echo "Starting......: cluebringer /etc/cluebringer/cluebringer.conf done\n";

$php[]="<?php";
$php[]="\$DB_DSN=\"mysql:host=$q->mysql_server;dbname=policyd\";";
$php[]="\$DB_USER=\"$q->mysql_admin\";";
$php[]="\$DB_PASS=\"$q->mysql_password\";";
$php[]="?>";
@file_put_contents("/usr/share/artica-postfix/cluebringer/includes/config.php",@implode("\n",$php));
echo "Starting......: cluebringer includes/config.php done\n";
$unix=new unix();
$lighttpd=$unix->LIGHTTPD_USER();
$chown=$unix->find_program("chown");
shell_exec("/bin/chown -R $lighttpd:$lighttpd /usr/share/artica-postfix/cluebringer/* >/dev/null 2>&1 &");

}

function testsDatabase(){
$f[]="access_control";
$f[]="amavis_rules";
$f[]="checkhelo";
$f[]="checkhelo_blacklist";
$f[]="checkhelo_tracking";
$f[]="checkhelo_whitelist";
$f[]="checkspf";
$f[]="greylisting";
$f[]="greylisting_autoblacklist";
$f[]="greylisting_autowhitelist";
$f[]="greylisting_tracking";
$f[]="greylisting_whitelist";
$f[]="policies";
$f[]="policy_group_members";
$f[]="policy_groups";
$f[]="policy_members";
$f[]="quotas";
$f[]="quotas_limits";
$f[]="quotas_tracking";
$f[]="session_tracking";


	$rebuild=false;
	$q=new mysql();
	$unix=new unix();
	$dbfile="/usr/share/artica-postfix/bin/install/cluebringer/policyd.mysql";
	
	if(!$q->DATABASE_EXISTS("policyd")){
		$q->CREATE_DATABASE("policyd");
		$rebuild=true;
	}
	
	
if(!$rebuild){
	while (list ($num, $val) = each ($f) ){
		if($q->TABLE_EXISTS($val,"policyd")){
			echo "Starting......: cluebringer mysql table $val OK\n";
		}else{
			echo "Starting......: cluebringer mysql table $val FAILED\n";
			$rebuild=true;
			break;
		}
	}
	
	
}	
	
	
	if($rebuild){
		
		$mysqlbin=$unix->find_program("mysql");
		if(!is_file("$mysqlbin")){echo "Starting......: cluebringer mysql binary no such file\n";return;}
		$cmd="$mysqlbin --batch --host=$q->mysql_server --port=$q->mysql_port --user=$q->mysql_admin --password=$q->mysql_password --database=policyd";
		$cmd=$cmd." <$dbfile";
		if($GLOBALS["VERBOSE"]){echo $cmd."\n";}
		
		shell_exec($cmd);
		
		
		
	}

}


function internal_domains(){
	$ldap=new clladp();
	$hash=$ldap->hash_get_all_domains();
	$clue=new cluebringer();
	while (list ($domain, $val) = each ($hash) ){$clue->local_domain_add($domain);}
	echo "Starting......: cluebringer ". count($hash)." domains updated\n";
}

function web_password(){
	$sock=new sockets();
	$ldap=new clladp();
	$array=unserialize(base64_decode($sock->GET_INFO("ClueBringerMembers")));
	while (list ($uid, $conf) = each ($array) ){
		if($uid==null){continue;}
		$ct=new user($uid);
		if($ct->password==null){continue;}
		echo "Starting......: ClueBringer, access to $uid\n";
		$f[]="$uid:$ct->password";
	}
	
	echo "Starting......: ClueBringer, access to $ldap->ldap_admin\n";
	$f[]="$ldap->ldap_admin:$ldap->ldap_password";
	$f[]="";
	@mkdir("/etc/lighttpd",666,true);
	@file_put_contents("/etc/lighttpd/cluebringer.passwd",@implode("\n",$f));
	$unix=new unix();
	$lighttpd_user=$unix->LIGHTTPD_USER();
	@chown("/etc/lighttpd/cluebringer.passwd",$lighttpd_user);		
	
}

?>