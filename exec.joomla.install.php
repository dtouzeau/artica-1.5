<?php

include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.user.inc');
include_once(dirname(__FILE__)."/framework/class.unix.inc");
include_once(dirname(__FILE__)."/framework/frame.class.inc");
define( '_JEXEC', 1 );
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;$GLOBALS["cmdlineadd"]=" --verbose";}

if($argv[1]=='--vhosts'){vhosts();exit;}

CheckInstall();
CheckUninstall();

function CheckInstall(){
	$sql="SELECT * FROM joomla_users WHERE install=0";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		unset($arrayconfig);
		$GLOBALS["joom_servername"]=$ligne["servername"];
		set_status('{checking}');
		write_events("starting installation of {$GLOBALS["joom_servername"]}");
		$arrayconfig["LANG"]=$ligne["language"];
		$arrayconfig["DB"]=$ligne["databasename"];
		$arrayconfig["uid"]=$ligne["uid"];
		$u=new user($arrayconfig["uid"]);
		$arrayconfig["password"]=$u->password;
		$root=$u->homeDirectory."/www/{$ligne["servername"]}";
		$arrayconfig["root"]=$root;
		
		if(CreateDatabase($arrayconfig)){
			write_events("Database & tables are successfully created");
			if(CreateMysqlAdmin($arrayconfig)){
					writeconfig($arrayconfig);
					set_status("{installing}");
					
			}
			
			if(CopySources($arrayconfig)){
				set_status("{installed}");
				set_install_status(1);
				shell_exec("/usr/share/artica-postfix/bin/artica-install --reload-apache-groupware");
			}else{
				write_events("Unable to install sources");
				set_status("{failed}");
			}
			
			
		}else{
			continue;
		}
	}
}

function CheckUninstall(){
	$sql="SELECT * FROM joomla_users WHERE install=-1";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	$q=new mysql();
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		unset($arrayconfig);	
		$GLOBALS["joom_servername"]=$ligne["servername"];
		set_status('{checking}');
		$q->DELETE_DATABASE($ligne["databasename"]);
		DeleteMysqlAdmin($ligne["databasename"]);
		$u=new user($uid);
		$arrayconfig["password"]=$u->password;
		$root=$u->homeDirectory."/www/{$ligne["servername"]}";
		$arrayconfig["root"]=$root;		
		shell_exec("/bin/rm -rf $root >/dev/null 2>&1");
		$sql="DELETE FROM joomla_users WHERE servername='{$GLOBALS["joom_servername"]}'";
		$q->QUERY_SQL($sql,"artica_backup");
		
	}
	shell_exec("/usr/share/artica-postfix/bin/artica-install --reload-apache-groupware");
	
}


function set_status($status){
	$sql="UPDATE joomla_users SET status='$status' WHERE servername='{$GLOBALS["joom_servername"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
}
function set_install_status($success=0){
	$sql="UPDATE joomla_users SET install='$success' WHERE servername='{$GLOBALS["joom_servername"]}'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
}

function write_events($text){
	$text=addslashes($text);
	if($GLOBALS["VERBOSE"]){echo date('Y-m-d H:i:s').": $text\n";}
	$sql="SELECT events FROM joomla_users WHERE servername='{$GLOBALS["joom_servername"]}'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	$events=$ligne["events"]."<div>".date('Y-m-d H:i:s').": $text</div>";
	$sql="UPDATE joomla_users SET events='$events' WHERE servername='{$GLOBALS["joom_servername"]}'";
	$q->QUERY_SQL($sql,"artica_backup");
	}


	
function WriteAdmin($uid,$database){
	
		$user=new user($uid);
		$salt = genRandomPassword(32);
		$crypt = getCryptedPassword($user->password, $salt);
		$cryptpass = $crypt.':'.$salt;		
		$nullDate=null;
		$installdate 	= date('Y-m-d H:i:s');
		$q=new mysql();
		
		$sql="SELECT gid FROM jos_users WHERE id=62";
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
		if($ligne["gid"]==0){
			$query = "INSERT INTO jos_users VALUES (62, 'Administrator', '$user->uid', '{$user->mail}', '$cryptpass', 'Super Administrator', 0, 1, 25, '$installdate', '$nullDate', '', '')";	
			$q->QUERY_SQL($query,$database);
			if(!$q->ok){
				write_events("set admin/password failed...");
			}
			
			$query = "INSERT INTO jos_core_acl_aro VALUES (10,'users','62',0,'Administrator',0)";
			$q->QUERY_SQL($query,$database);
			if(!$q->ok){
				write_events("set admin/password failed...");
			}		
			$query = "INSERT INTO jos_core_acl_groups_aro_map VALUES (25,'',10)";		
			$q->QUERY_SQL($query,$database);
			if(!$q->ok){
				write_events("set admin/password failed...");
			}	
		}else{
			write_events("updating $uid/password...");	
			$sql="UPDATE jos_users SET password='$cryptpass' WHERE id=62";
			$q->QUERY_SQL($query,$database);
			if(!$q->ok){
				write_events("set admin/password failed...");
			}				
		}
		
}


function getCryptedPassword($plaintext, $salt = '',$show_encrypt = false){
	$encrypted = ($salt) ? md5($plaintext.$salt) : md5($plaintext);
	return ($show_encrypt) ? '{MD5}'.$encrypted : $encrypted;
}


function genRandomPassword($length = 8){
		$salt = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$len = strlen($salt);
		$makepass = '';
		$stat = @stat(__FILE__);
		if(empty($stat) || !is_array($stat)) $stat = array(php_uname());
		mt_srand(crc32(microtime() . implode('|', $stat)));
		for ($i = 0; $i < $length; $i ++) {$makepass .= $salt[mt_rand(0, $len -1)];}
		return $makepass;
	}
	
function CopySources($arrayconfig){
	$root=$arrayconfig["root"];
	write_events("Installing to destination folder $root");
	if($root==null){
		write_events("root configuration is not set");
		return false;
	}
	if(!is_dir("/usr/local/share/artica/joomla_src")){
		write_events("Unable to stat sources files");
		return false;
	}
	@mkdir("$root",755,true);
	@mkdir("$root/tmp",755,true);
	@mkdir("$root/logs",755,true);	
	shell_exec("/bin/cp -rf /usr/local/share/artica/joomla_src/* $root/");
	$unix=new unix();
	$user=$unix->APACHE_GROUPWARE_ACCOUNT();
	shell_exec("/bin/chown -R $user $root");
	if(!is_dir($root)){
		write_events("Unable to stat $root destination folder");
		return false;
	}
	
	if(is_dir("$root/installation")){shell_exec("/bin/rm -rf $root/installation");}
	
	return true;
	}
	

function CreateDatabase($arrayconfig){
		$q=new mysql();
		$errors 	= null;
		$lang 		= $arrayconfig["LANG"];
		$DBcreated	= false;
		$DBtype 	= 'mysql';
		$DBhostname = $q->mysql_server;
		$DBuserName = $q->mysql_admin;
		$DBpassword = $q->mysql_password;
		$DBname 	=  $arrayconfig["DB"];
		$DBPrefix 	='jos_';
		$DBOld 		= "bu";
		$DBversion 		= null;

		if($q->DATABASE_EXISTS($arrayconfig["DB"])){
			write_events("Database already exists");
			return true;
		}
			
		$q->CREATE_DATABASE($arrayconfig["DB"]);
		if(!$q->ok){write_events("Error: $q->mysql_error");}
		if(!$q->DATABASE_EXISTS($arrayconfig["DB"])){
				write_events("Failed to create database {$arrayconfig["DB"]} \"$q->mysql_error\"");
				set_status("{failed}");
				return false;
			}
			
			write_events("Database successfully created");
			$dbscheme = '/usr/local/share/artica/joomla_src/installation/sql/mysql/joomla.sql';
			populateDatabase($dbscheme,$arrayconfig["DB"]);
			$dbscheme = '/usr/local/share/artica/joomla_src/installation/sql/mysql/sample_data.sql';
			populateDatabase($dbscheme,$arrayconfig["DB"]);
			WriteAdmin($arrayconfig["uid"],$arrayconfig["DB"]);
			return true;
	}
	
function DeleteMysqlAdmin($database){
	$q=new mysql();
	$sql="DELETE FROM `mysql`.`db` WHERE `db`.`Db` = '$server_database'";
	$q->QUERY_SQL($sql,"mysql");
	
}
	
function CreateMysqlAdmin($arrayconfig){
	$server_database=$arrayconfig["DB"];
	$mysql_password=$arrayconfig["password"];
	$user=$arrayconfig["uid"];
	
	$sql="SELECT User FROM user WHERE User='$user'";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'mysql'));	
	$userfound=$ligne["User"];
	
	
	
	$q=new mysql();
	$sql="DELETE FROM `mysql`.`db` WHERE `db`.`Db` = '$server_database'";
	$q->QUERY_SQL($sql,"mysql");
	if(!$q->ok){
		write_events("Failed to set privileges \"$q->mysql_error\"");
		set_status("{failed}");
		return false;	
	}
	
	if($userfound==null){
		$sql="CREATE USER '$user'@'%' IDENTIFIED BY '$mysql_password';";
		$q->QUERY_SQL($sql,"mysql");
		if(!$q->ok){
			write_events("Failed to set privileges operation - Create user -\"$q->mysql_error\"");
			set_status("{failed}");
			return false;	
		}
	}else{
		write_events("$user already exists");	
	}

	$sql="GRANT USAGE ON `$server_database`. * 
	TO '$user'@'%' IDENTIFIED BY '$mysql_password' 
	WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0 ;";
	$q->QUERY_SQL($sql);
	if(!$q->ok){
		write_events("Failed to set privileges - GRANT usage -\"$q->mysql_error\"");
		set_status("{failed}");
		return false;	
	}	

	$sql="GRANT ALL PRIVILEGES ON `$server_database` . * TO '$user'@'%' WITH GRANT OPTION ;";
	$q->QUERY_SQL($sql);
	if(!$q->ok){
		write_events("Failed to set privileges \"$q->mysql_error\"");
		set_status("{failed}");
		return false;	
	}

		write_events("success set privileges on $server_database");
		return true;		
		
}
	
function populateDatabase($sqlfile,$db){
		if( !($buffer = file_get_contents($sqlfile)) ){return -1;}

		$queries =splitSql($buffer);
		$q=new mysql();
		foreach ($queries as $query){
			$query = trim($query);
			if ($query != '' && $query {0} != '#'){
				$query=str_replace("#__","jos_",$query);
				$q->QUERY_SQL($query,$db);
				$count=$count+1;
				write_events("Execute query $count");
				if(!$q->ok){
					write_events($q->mysql_error);
				}

				
			}
		}
		
		
		
		
	}

	/**
	 * @param string
	 * @return array
	 */
	function splitSql($sql){
		$sql = trim($sql);
		$sql = preg_replace("/\n\#[^\n]*/", '', "\n".$sql);
		$buffer = array ();
		$ret = array ();
		$in_string = false;

		for ($i = 0; $i < strlen($sql) - 1; $i ++) {
			if ($sql[$i] == ";" && !$in_string)
			{
				$ret[] = substr($sql, 0, $i);
				$sql = substr($sql, $i +1);
				$i = 0;
			}

			if ($in_string && ($sql[$i] == $in_string) && $buffer[1] != "\\")
			{
				$in_string = false;
			}
			elseif (!$in_string && ($sql[$i] == '"' || $sql[$i] == "'") && (!isset ($buffer[0]) || $buffer[0] != "\\"))
			{
				$in_string = $sql[$i];
			}
			if (isset ($buffer[1]))
			{
				$buffer[0] = $buffer[1];
			}
			$buffer[1] = $sql[$i];
		}

		if (!empty ($sql))
		{
			$ret[] = $sql;
		}
		return ($ret);
	}

	
function writeconfig($arrayconfig){
	$root=$arrayconfig["root"];
	$q=new mysql();
$conf[]="<?php";
$conf[]="class JConfig {";
$conf[]="	/**";
$conf[]="	* -------------------------------------------------------------------------";
$conf[]="	* Site configuration section";
$conf[]="	* -------------------------------------------------------------------------";
$conf[]="	*/";
$conf[]="	/* Site Settings */";
$conf[]="	var \$offline = '0';";
$conf[]="	var \$offline_message = 'This site is down for maintenance.<br /> Please check back again soon.';";
$conf[]="	var \$sitename = '{$arrayconfig["uid"]} Site ';			// Name of Joomla site";
$conf[]="	var \$editor = 'tinymce';";
$conf[]="	var \$list_limit = '20';";
$conf[]="	var \$legacy = '0';";
$conf[]="";
$conf[]="	/**";
$conf[]="	* -------------------------------------------------------------------------";
$conf[]="	* Database configuration section";
$conf[]="	* -------------------------------------------------------------------------";
$conf[]="	*/";
$conf[]="	/* Database Settings */";
$conf[]="	var \$dbtype = 'mysql';					// Normally mysql";
$conf[]="	var \$host = '$q->mysql_server';				// This is normally set to localhost";
$conf[]="	var \$user = '{$arrayconfig["uid"]}';							// MySQL username";
$conf[]="	var \$password = '{$arrayconfig["password"]}';						// MySQL password";
$conf[]="	var \$db = '{$arrayconfig["DB"]}';							// MySQL database name";
$conf[]="	var \$dbprefix = 'jos_';					// Do not change unless you need to!";
$conf[]="";
$conf[]="	/* Server Settings */";
$conf[]="	var \$secret = 'FBVtggIk5lAzEU9H'; 		//Change this to something more secure";
$conf[]="	var \$gzip = '0';";
$conf[]="	var \$error_reporting = '-1';";
$conf[]="	var \$helpurl = 'http://help.joomla.org';";
$conf[]="	var \$xmlrpc_server = '1';";
$conf[]="	var \$ftp_host = '';";
$conf[]="	var \$ftp_port = '';";
$conf[]="	var \$ftp_user = '';";
$conf[]="	var \$ftp_pass = '';";
$conf[]="	var \$ftp_root = '';";
$conf[]="	var \$ftp_enable = '';";
$conf[]="	var \$tmp_path	= '$root/tmp';";
$conf[]="	var \$log_path	= '$root/logs';";
$conf[]="	var \$offset = '0';";
$conf[]="	var \$live_site = ''; 					// Optional, Full url to Joomla install.";
$conf[]="	var \$force_ssl = 0;		//Force areas of the site to be SSL ONLY.  0 = None, 1 = Administrator, 2 = Both Site and Administrator";
$conf[]="";
$conf[]="	/* Session settings */";
$conf[]="	var \$lifetime = '15';					// Session time";
$conf[]="	var \$session_handler = 'database';";
$conf[]="";
$conf[]="	/* Mail Settings */";
$conf[]="	var \$mailer = 'mail';";
$conf[]="	var \$mailfrom = '';";
$conf[]="	var \$fromname = '';";
$conf[]="	var \$sendmail = '/usr/sbin/sendmail';";
$conf[]="	var \$smtpauth = '0';";
$conf[]="	var \$smtpuser = '';";
$conf[]="	var \$smtppass = '';";
$conf[]="	var \$smtphost = 'localhost';";
$conf[]="";
$conf[]="	/* Cache Settings */";
$conf[]="	var \$caching = '0';";
$conf[]="	var \$cachetime = '15';";
$conf[]="	var \$cache_handler = 'file';";
$conf[]="";
$conf[]="	/* Debug Settings */";
$conf[]="	var \$debug      = '0';";
$conf[]="	var \$debug_db 	= '0';";
$conf[]="	var \$debug_lang = '0';";
$conf[]="";
$conf[]="	/* Meta Settings */";
$conf[]="	var \$MetaDesc = 'Joomla! - the dynamic portal engine and content management system';";
$conf[]="	var \$MetaKeys = 'joomla, Joomla';";
$conf[]="	var \$MetaTitle = '1';";
$conf[]="	var \$MetaAuthor = '1';";
$conf[]="";
$conf[]="	/* SEO Settings */";
$conf[]="	var \$sef = '0';";
$conf[]="	var \$sef_rewrite = '0';";
$conf[]="	var \$sef_suffix = '';";
$conf[]="";
$conf[]="	/* Feed Settings */";
$conf[]="	var \$feed_limit   = 10;";
$conf[]="	var \$feed_email   = 'author';";
$conf[]="}";
$conf[]="?>";

@mkdir($root,755,true);
@mkdir("$root/tmp",755,true);
@mkdir("$root/logs",755,true);
@file_put_contents("$root/configuration.php",@implode("\n",$conf));

}

function vhosts(){
	$sock=new sockets();
	$unix=new unix();
	$ApacheGroupWarePort=$sock->GET_INFO("ApacheGroupWarePort");
	$user=$unix->APACHE_GROUPWARE_ACCOUNT();
	
	$conf[]="\n# Joomla personal web sites\n";
	$sql="SELECT * FROM joomla_users WHERE install=1";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		writelogs("$q->mysql_error",__FUNCTION__,__FILE__,__LINE__);
		return;
	}
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){	
		$vhost=$ligne["servername"];
		echo "Starting Apache..............: Joomla personal website: $vhost\n";
		$uid=$ligne["uid"];
		$u=new user($uid);
		$root=$u->homeDirectory."/www/{$ligne["servername"]}";
		if(is_dir($root)){		
			$conf[]="\n<VirtualHost *:$ApacheGroupWarePort>";
			$conf[]="\tServerName $vhost";
			$conf[]="\tServerAdmin $u->mail";
			$conf[]="\tDocumentRoot $root";
			$conf[]="\tphp_value  error_log  \"$root/logs/php.log\"";
			$conf[]="\tphp_value open_basedir \"$root\"";
			//$conf[]="\tphp_value magic_quotes_gpc off";
			$conf[]="\t<Directory \"$root\">";
			$conf[]="\t\tDirectoryIndex index.php";
			$conf[]="\t\tOptions Indexes FollowSymLinks MultiViews";
			$conf[]="\t\tAllowOverride all";
			$conf[]="\t\tOrder allow,deny";
			$conf[]="\tAllow from all";
			$conf[]="\t</Directory>";
			$conf[]="\tCustomLog $root/logs/access.log \"%h %l %u %t \\\"%r\\\" %>s %b \\\"%{Referer}i\\\" \\\"%{User-Agent}i\\\" %V\"\n";
			$conf[]="\tErrorLog $root/logs/webserver.errors.log";
			$conf[]="</VirtualHost>\n";	
			shell_exec("/bin/chown -R $user $root");
		}
		
	}
	
	@file_put_contents("/usr/local/apache-groupware/conf/joomla-vhosts.conf",@implode("\n",$conf));
	
	
}
	



?>