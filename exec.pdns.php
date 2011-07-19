<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
include_once(dirname(__FILE__) . '/ressources/class.ldap.inc');
include_once(dirname(__FILE__) . '/framework/class.unix.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');
if(is_array($argv)){if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;	ini_set('html_errors',0);ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);}}


if($argv[1]=="--mysql"){checkMysql();exit;}
if($argv[1]=="--poweradmin"){poweradmin();exit;}





function poweradmin(){
if(!is_file("/usr/share/poweradmin/index.php")){
	echo "Starting......: PowerAdmin is not installed\n";
	return;
}

$q=new mysql();

$f[]="<?php";
$f[]="\$db_host		= '$q->mysql_server';";
$f[]="\$db_user		= '$q->mysql_admin';";
$f[]="\$db_pass		= '$q->mysql_password';";
$f[]="\$db_name		= 'powerdns';";
$f[]="\$db_port		= '$q->mysql_port';";
$f[]="\$db_type		= 'mysql';";
$f[]="\$iface_lang		= 'en_EN';";
$f[]="\$cryptokey		= 'p0w3r4dm1n';";
$f[]="\$iface_style		= 'example';";
$f[]="\$iface_rowamount	= 50;";
$f[]="\$iface_expire	= 1800;";
$f[]="\$iface_zonelist_serial	= false;";
$f[]="\$iface_title = 'Poweradmin For Artica';";
$f[]="\$password_encryption='md5';";
$f[]="\$dns_ttl		= 86400;";
$f[]="\$dns_fancy	= false;";
$f[]="\$dns_strict_tld_check	= true;";
$f[]="\$dns_hostmaster		= 'hostmaster.example.net';";
$f[]="\$dns_ns1		= 'ns1.example.net';";
$f[]="\$dns_ns2		= 'ns2.example.net';";
$f[]="\$syslog_use  = True;";
$f[]="\$syslog_ident = 'poweradmin';";
$f[]="?>";

$sql="DELETE FROM users WHERE id=1";
$q->QUERY_SQL($sql,"powerdns");
$ldap=new clladp();
$pass=md5($ldap->ldap_password);

$sql="SELECT password,fullname,email FROM `users` WHERE id=1";
$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"powerdns"));
if($ligne["password"]<>null){
	$sql="UPDATE `users` SET `username`= '$ldap->ldap_admin',`password`='$pass' ,`perm_templ`=1,`active`=1 WHERE id=1";
	
}else{
	$sql="INSERT INTO `users` (`id`, `username`, `password`, `fullname`, `email`, `description`, `perm_templ`, `active`) VALUES
	(1, '$ldap->ldap_admin', '$pass', 'Administrator', 'admin@example.net', 'Administrator with full rights.', 1, 1);";
}
$q->QUERY_SQL($sql,"powerdns");
if(!$q->ok){echo "Starting......: PowerAdmin $ldap->ldap_admin failed $q->mysql_error\n";}else{
	echo "Starting......: PowerAdmin $ldap->ldap_admin ok\n";
}

@file_put_contents("/usr/share/poweradmin/inc/config.inc.php", @implode("\n", $f));	
echo "Starting......: PowerAdmin config.inc.php done\n";
if(is_dir("/usr/share/poweradmin/install")){shell_exec("/bin/rm -rf /usr/share/poweradmin/install >/dev/null 2>&1");}

}


function checkMysql(){
	
	$q=new mysql();
	if(!$q->DATABASE_EXISTS("powerdns")){
		echo "Starting......: PowerDNS creating 'powerdns' database\n";
		if(!$q->CREATE_DATABASE("powerdns")){
			echo "Starting......: PowerDNS creating 'powerdns' database failed\n"; 
			return;
		}
	}

echo "Starting......: PowerDNS 'powerdns' database OK\n";

	if(!$q->TABLE_EXISTS("domains", "powerdns")){
		echo "Starting......: PowerDNS creating 'domains' table\n";
		$sql="create table domains (
			 id		 INT auto_increment,
			 name		 VARCHAR(255) NOT NULL,
			 master		 VARCHAR(128) DEFAULT NULL,
			 last_check	 INT DEFAULT NULL,
			 type		 VARCHAR(6) NOT NULL,
			 notified_serial INT DEFAULT NULL, 
			 account         VARCHAR(40) DEFAULT NULL,
			 primary key (id)
			) Engine=InnoDB;";
		$q->QUERY_SQL($sql,"powerdns");
		if(!$q->ok){
			echo "Starting......: PowerDNS creating 'domains' table FAILED\n";
		}else{
			$q->QUERY_SQL("CREATE UNIQUE INDEX name_index ON domains(name);","powerdns");
		}
		
	}

	if(!$q->TABLE_EXISTS("records", "powerdns")){
		echo "Starting......: PowerDNS creating 'records' table\n";
		$sql="CREATE TABLE records (
			  id              INT auto_increment,
			  domain_id       INT DEFAULT NULL,
			  name            VARCHAR(255) DEFAULT NULL,
			  type            VARCHAR(10) DEFAULT NULL,
			  content         VARCHAR(255) DEFAULT NULL,
			  ttl             INT DEFAULT NULL,
			  prio            INT DEFAULT NULL,
			  change_date     INT DEFAULT NULL,
			  primary key(id)
			)Engine=InnoDB;";
		$q->QUERY_SQL($sql,"powerdns");
		if(!$q->ok){
			echo "Starting......: PowerDNS creating 'records' table FAILED\n";
		}{
			$q->QUERY_SQL("CREATE INDEX rec_name_index ON records(name);","powerdns");
			$q->QUERY_SQL("CREATE INDEX nametype_index ON records(name,type);","powerdns");
			$q->QUERY_SQL("CREATE INDEX domain_id ON records(domain_id);","powerdns");
			$q->QUERY_SQL("alter table records add ordername VARCHAR(255);","powerdns");
			$q->QUERY_SQL("alter table records add auth bool;","powerdns");
			$q->QUERY_SQL("create index orderindex on records(ordername);","powerdns");
			$q->QUERY_SQL("alter table records change column type type VARCHAR(10);","powerdns");
			
		}
		
	}


	if(!$q->TABLE_EXISTS("supermasters", "powerdns")){
		echo "Starting......: PowerDNS creating 'supermasters' table\n";
		$sql="create table supermasters (
				  ip VARCHAR(25) NOT NULL, 
				  nameserver VARCHAR(255) NOT NULL, 
				  account VARCHAR(40) DEFAULT NULL
				) Engine=InnoDB;";
		$q->QUERY_SQL($sql,"powerdns");
		if(!$q->ok){
			echo "Starting......: PowerDNS creating 'supermasters' table FAILED\n";
		}{
			$q->QUERY_SQL("CREATE INDEX rec_name_index ON records(name);","powerdns");
			$q->QUERY_SQL("CREATE INDEX nametype_index ON records(name,type);","powerdns");
			$q->QUERY_SQL("CREATE INDEX domain_id ON records(domain_id);","powerdns");
		}
		
	}
	if(!$q->TABLE_EXISTS("domainmetadata", "powerdns")){
		echo "Starting......: PowerDNS creating 'domainmetadata' table\n";
		$sql="create table domainmetadata (
			 id              INT auto_increment,
			 domain_id       INT NOT NULL,
			 kind            VARCHAR(16),
			 content        TEXT,
			 primary key(id)
			);";
	$q->QUERY_SQL($sql,"powerdns");
		if(!$q->ok){
			echo "Starting......: PowerDNS creating 'domainmetadata' table FAILED\n";
		}
		
	}	

	if(!$q->TABLE_EXISTS("cryptokeys", "powerdns")){
		echo "Starting......: PowerDNS creating 'cryptokeys' table\n";
		$sql="create table cryptokeys (
			 id             INT auto_increment,
			 domain_id      INT NOT NULL,
			 flags          INT NOT NULL,
			 active         BOOL,
			 content        TEXT,
			 primary key(id)
			); ";
	$q->QUERY_SQL($sql,"powerdns");
		if(!$q->ok){
			echo "Starting......: PowerDNS creating 'cryptokeys' table FAILED\n";
		}
		
	}		

	if(!$q->TABLE_EXISTS("tsigkeys", "powerdns")){
		echo "Starting......: PowerDNS creating 'tsigkeys' table\n";
		$sql="create table tsigkeys (
			 id             INT auto_increment,
			 name           VARCHAR(255), 
			 algorithm      VARCHAR(255),
			 secret         VARCHAR(255),
			 primary key(id)
			);";
	$q->QUERY_SQL($sql,"powerdns");
		if(!$q->ok){
			echo "Starting......: PowerDNS creating 'tsigkeys' table FAILED\n";
		}else{
			$q->QUERY_SQL("create unique index namealgoindex on tsigkeys(name, algorithm);","powerdns");
		}
		
	}



	if(!$q->TABLE_EXISTS("users", "powerdns")){
		echo "Starting......: PowerDNS creating 'users' table\n";
	
		$sql="CREATE TABLE IF NOT EXISTS `users` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `username` varchar(16) NOT NULL DEFAULT '0',
			  `password` varchar(34) NOT NULL DEFAULT '0',
			  `fullname` varchar(255) NOT NULL DEFAULT '0',
			  `email` varchar(255) NOT NULL DEFAULT '0',
			  `description` varchar(1024) NOT NULL DEFAULT '0',
			  `perm_templ` tinyint(4) NOT NULL DEFAULT '0',
			  `active` tinyint(4) NOT NULL DEFAULT '0',
			  PRIMARY KEY (`id`))"; 
		$q->QUERY_SQL($sql,"powerdns");
		if(!$q->ok){
			echo "Starting......: PowerDNS creating 'users' table FAILED\n";
		}
		
	}
	
	
	if(!$q->TABLE_EXISTS("perm_items", "powerdns")){
		echo "Starting......: PowerDNS creating 'perm_items' table\n";
	
		$sql="CREATE TABLE IF NOT EXISTS `perm_items` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` varchar(64) NOT NULL DEFAULT '0',
		  `descr` varchar(1024) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=62 ;";
		
		$q->QUERY_SQL($sql,"powerdns");
		if(!$q->ok){
			echo "Starting......: PowerDNS creating 'perm_items' table FAILED\n";
		}else{
			$sql="INSERT INTO `perm_items` (`id`, `name`, `descr`) VALUES
			(41, 'zone_master_add', 'User is allowed to add new master zones.'),
			(42, 'zone_slave_add', 'User is allowed to add new slave zones.'),
			(43, 'zone_content_view_own', 'User is allowed to see the content and meta data of zones he owns.'),
			(44, 'zone_content_edit_own', 'User is allowed to edit the content of zones he owns.'),
			(45, 'zone_meta_edit_own', 'User is allowed to edit the meta data of zones he owns.'),
			(46, 'zone_content_view_others', 'User is allowed to see the content and meta data of zones he does not own.'),
			(47, 'zone_content_edit_others', 'User is allowed to edit the content of zones he does not own.'),
			(48, 'zone_meta_edit_others', 'User is allowed to edit the meta data of zones he does not own.'),
			(49, 'search', 'User is allowed to perform searches.'),
			(50, 'supermaster_view', 'User is allowed to view supermasters.'),
			(51, 'supermaster_add', 'User is allowed to add new supermasters.'),
			(52, 'supermaster_edit', 'User is allowed to edit supermasters.'),
			(53, 'user_is_ueberuser', 'User has full access. God-like. Redeemer.'),
			(54, 'user_view_others', 'User is allowed to see other users and their details.'),
			(55, 'user_add_new', 'User is allowed to add new users.'),
			(56, 'user_edit_own', 'User is allowed to edit their own details.'),
			(57, 'user_edit_others', 'User is allowed to edit other users.'),
			(58, 'user_passwd_edit_others', 'User is allowed to edit the password of other users.'),
			(59, 'user_edit_templ_perm', 'User is allowed to change the permission template that is assigned to a user.'),
			(60, 'templ_perm_add', 'User is allowed to add new permission templates.'),
			(61, 'templ_perm_edit', 'User is allowed to edit existing permission templates.');";
			$q->QUERY_SQL($sql,"powerdns");
		}
	}
	
	if(!$q->TABLE_EXISTS("perm_templ", "powerdns")){
		echo "Starting......: PowerDNS creating 'perm_templ' table\n";
	
		$sql="CREATE TABLE IF NOT EXISTS `perm_templ` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `name` varchar(128) NOT NULL DEFAULT '0',
			  `descr` varchar(1024) NOT NULL DEFAULT '0',
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;";
		$q->QUERY_SQL($sql,"powerdns");
		if(!$q->ok){
			echo "Starting......: PowerDNS creating 'perm_templ' table FAILED\n";
		}else{
			$sql="INSERT INTO `perm_templ` (`id`, `name`, `descr`) VALUES (1, 'Administrator', 'Administrator template with full rights.');";
			$q->QUERY_SQL($sql,"powerdns");
		}
	}
	
	if(!$q->TABLE_EXISTS("perm_templ_items", "powerdns")){
		echo "Starting......: PowerDNS creating 'perm_templ_items' table\n";
	
		$sql="CREATE TABLE IF NOT EXISTS `perm_templ_items` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `templ_id` int(11) NOT NULL DEFAULT '0',
		  `perm_id` int(11) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=250 ;";
		$q->QUERY_SQL($sql,"powerdns");
		if(!$q->ok){
			echo "Starting......: PowerDNS creating 'perm_templ_items' table FAILED\n";
		}else{
			$sql="INSERT INTO `perm_templ_items` (`id`, `templ_id`, `perm_id`) VALUES (249, 1, 53);";
			$q->QUERY_SQL($sql,"powerdns");
		}
	}

	if(!$q->TABLE_EXISTS("zones", "powerdns")){
		echo "Starting......: PowerDNS creating 'zones' table\n";
	
		$sql="CREATE TABLE IF NOT EXISTS `zones` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `domain_id` int(11) NOT NULL DEFAULT '0',
		  `owner` int(11) NOT NULL DEFAULT '0',
		  `comment` varchar(1024) DEFAULT '0',
		  `zone_templ_id` int(11) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";	
		$q->QUERY_SQL($sql,"powerdns");
		if(!$q->ok){
			echo "Starting......: PowerDNS creating 'zones' table FAILED\n";
		}
	}
	
if(!$q->TABLE_EXISTS("zone_templ", "powerdns")){
		echo "Starting......: PowerDNS creating 'zone_templ' table\n";
		$sql="CREATE TABLE IF NOT EXISTS `zone_templ` (
			  `id` bigint(20) NOT NULL AUTO_INCREMENT,
			  `name` varchar(128) NOT NULL DEFAULT '0',
			  `descr` varchar(1024) NOT NULL DEFAULT '0',
			  `owner` bigint(20) NOT NULL DEFAULT '0',
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";	
		$q->QUERY_SQL($sql,"powerdns");
		if(!$q->ok){
			echo "Starting......: PowerDNS creating 'zone_templ' table FAILED\n";
		}
	}	
	
if(!$q->TABLE_EXISTS("zone_templ_records", "powerdns")){
		echo "Starting......: PowerDNS creating 'zone_templ_records' table\n";
		$sql="CREATE TABLE IF NOT EXISTS `zone_templ_records` (
		  `id` bigint(20) NOT NULL AUTO_INCREMENT,
		  `zone_templ_id` bigint(20) NOT NULL DEFAULT '0',
		  `name` varchar(255) NOT NULL DEFAULT '0',
		  `type` varchar(6) NOT NULL DEFAULT '0',
		  `content` varchar(255) NOT NULL DEFAULT '0',
		  `ttl` bigint(20) NOT NULL DEFAULT '0',
		  `prio` bigint(20) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";	
		$q->QUERY_SQL($sql,"powerdns");
		if(!$q->ok){
			echo "Starting......: PowerDNS creating 'zone_templ_records' table FAILED\n";
		}
	}	
	

echo "Starting......: PowerDNS Mysql done...\n";
poweradmin();
}

?>