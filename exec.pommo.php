<?php
$GLOBALS["KAV4PROXY_NOSESSION"]=true;
if(posix_getuid()<>0){parseTemplate();die();}

include_once(dirname(__FILE__)."/ressources/class.user.inc");
include_once(dirname(__FILE__)."/ressources/class.groups.inc");
include_once(dirname(__FILE__)."/ressources/class.ldap.inc");
include_once(dirname(__FILE__)."/ressources/class.system.network.inc");
include_once(dirname(__FILE__)."/ressources/class.mysql.inc");
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");

if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;$GLOBALS["debug"]=true;}
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}

include_once(dirname(__FILE__).'/framework/class.unix.inc');
if(!Build_pid_func(__FILE__,"MAIN")){
	writelogs(basename(__FILE__).":Already executed.. aborting the process",basename(__FILE__),__FILE__,__LINE__);
	die();
}

if(!is_dir("/usr/share/pommo")){die();}

pommo_config();

function pommo_config(){
	
$q=new mysql();	
$sock=new sockets();
$users=new usersMenus();
$unix=new unix();

$PommoFieldlang=$sock->GET_INFO('PommoFieldlang');
if(trim($PommoFieldlang)==null){$PommoFieldlang='en';}

$PommoFieldhostname=$sock->GET_INFO('PommoFieldhostname');
if(trim($PommoFieldhostname)==null){
	$PommoFieldhostname=$users->hostname;
	$sock->SET_INFO("PommoFieldhostname",$PommoFieldhostname);
}

$lighttpd_port=$unix->LIGHTTPD_PORT();

$t[]="[db_hostname] = $q->mysql_server:$q->mysql_port";
$t[]="[db_username] = $q->mysql_admin";
$t[]="[db_password] = $q->mysql_password";
$t[]="[db_database] = pommo";
$t[]="[db_prefix] = pommo_";
$t[]="[lang] = en";
$t[]="[debug] = off";
$t[]="[verbosity] = 3";
$t[]="[date_format] = 3";
$t[]="[workDir] = \"/usr/share/pommo/cache\"";
$t[]="[baseURL] = \"/mailing/\"";
$t[]="[hostname] =$PommoFieldhostname";
$t[]="[hostport] = $lighttpd_port";

@mkdir("/usr/share/pommo/cache",0777,true);
@mkdir("/usr/share/pommo/smarty",0777,true);
@file_put_contents("/usr/share/pommo/config.php",implode("\n",$t));
echo "Starting Pommo...............: Saving configuration done\n";
fix_set_magic_quotes_runtime();
tests_mysql();


}


function fix_set_magic_quotes_runtime(){
	$f[]="/usr/share/pommo/bootstrap.php";
	while (list ($num, $file) = each ($f)){
		if(is_file($file)){
			_fix_set_magic_quotes_runtime($file);
		}
		
	}
}
function _fix_set_magic_quotes_runtime($path){
	$rp=false;
	$tb=explode("\n",@file_get_contents($path));
	while (list ($num, $line) = each ($tb)){
		if(preg_match("#set_magic_quotes_runtime#",$line)){
			echo "Starting Pommo...............: remove line $num in ". basename($path)."\n";
			unset($tb[$num]);
			$rp=true;
		}
	}
	
	if($rp){
		@file_put_contents($path,implode("\n",$tb));
	}
}


function tests_mysql(){
	
$r[]="pommo_config";
$r[]="pommo_fields";
$r[]="pommo_groups";
$r[]="pommo_group_rules";
$r[]="pommo_mailings";
$r[]="pommo_mailing_current";
$r[]="pommo_mailing_notices";
$r[]="pommo_queue";
$r[]="pommo_scratch";
$r[]="pommo_subscribers";
$r[]="pommo_subscriber_data";
$r[]="pommo_subscriber_pending";
$r[]="pommo_templates";
$r[]="pommo_updates";	
$test=true;
$q=new mysql();
while (list ($num, $table) = each ($r)){
	if(!$q->TABLE_EXISTS($table,"pommo")){
		$test=false;
		echo "Starting Pommo...............: $table does not exists, rebuild it\n";
		break;
	}
	
}

if(!$q->DATABASE_EXISTS("pommo")){$test=false;}

if(!$test){buildMysql();}


 $ldap=new clladp();
 $q=new mysql();
 $sql="UPDATE pommo_config SET config_value ='$ldap->ldap_admin' WHERE config_name='admin_username'";
 $q->QUERY_SQL($sql,"pommo");
 $password=md5($ldap->ldap_password);
 $sql="UPDATE pommo_config SET config_value ='$password' WHERE config_name='admin_password'";
 $q->QUERY_SQL($sql,"pommo");
 echo "Starting Pommo...............: checking tables OK\n";
 
 
}

function buildMysql(){
$q=new mysql();
	
	if(!$q->DATABASE_EXISTS("pommo")){
		$q->CREATE_DATABASE("pommo");
	}

$sql="CREATE TABLE IF NOT EXISTS `pommo_config` (
  `config_name` varchar(64) NOT NULL default '',
  `config_value` text NOT NULL,
  `config_description` tinytext NOT NULL,
  `autoload` enum('on','off') NOT NULL default 'on',
  `user_change` enum('on','off') NOT NULL default 'on',
  PRIMARY KEY  (`config_name`)
) ENGINE=MyISAM;
";

$q->QUERY_SQL($sql,"pommo");

$sql="INSERT INTO `pommo_config` (`config_name`, `config_value`, `config_description`, `autoload`, `user_change`) VALUES
('admin_username', 'admin', 'Username', 'off', 'on'),
('admin_password', '9dd3ba637ec2fcaf383415617d39e002', 'Password', 'off', 'on'),
('admin_email', 'root@localhost.localdomain', 'Administrator Email', 'on', 'on'),
('site_name', 'Artica For poMMo', 'Website Name', 'on', 'on'),
('site_url', 'http://www.pommo-rocks.com', 'Website URL', 'on', 'on'),
('site_success', '', 'Signup Success URL', 'off', 'on'),
('site_confirm', '', '', 'off', 'on'),
('list_name', 'Artica Mailing List', 'List Name', 'on', 'on'),
('list_fromname', 'poMMo Administrative Team', 'From Name', 'off', 'on'),
('list_fromemail', 'pommo@yourdomain.com', 'From Email', 'off', 'on'),
('list_frombounce', 'bounces@yourdomain.com', 'Bounces', 'off', 'on'),
('list_exchanger', 'sendmail', 'List Exchanger', 'off', 'on'),
('list_confirm', 'on', 'Confirmation Messages', 'off', 'on'),
('list_charset', 'ISO-8859-1', '', 'off', 'on'),
('list_wysiwyg', 'on', '', 'off', 'off'),
('maxRuntime', '80', '', 'off', 'on'),
('messages', 'a:6:{s:9:\"subscribe\";a:4:{s:3:\"msg\";s:152:\"Welcome to our mailing list. You can always login to update your records or unsubscribe by visiting: \n  https://192.168.1.12:9000/mailing/user/login.php\";s:3:\"sub\";s:30:\"Welcome to Artica Mailing List\";s:3:\"web\";s:45:\"Welcome to our mailing list. Enjoy your stay.\";s:5:\"email\";b:0;}s:11:\"unsubscribe\";a:4:{s:3:\"sub\";s:33:\"Farewell from Artica Mailing List\";s:3:\"msg\";s:106:\"You have been unsubscribed and will not receive any more mailings from us. Feel free to come back anytime!\";s:3:\"web\";s:55:\"You have successfully unsubscribed. Enjoy your travels.\";s:5:\"email\";b:0;}s:7:\"confirm\";a:2:{s:3:\"msg\";s:253:\"You have requested to subscribe to Artica Mailing List. We would like to validate your email address before adding you as a subscriber. Please click the link below to be added ->\r\n	[[url]]\r\n\r\nIf you have received this message in error, please ignore it.\";s:3:\"sub\";s:20:\"Subscription request\";}s:8:\"activate\";a:2:{s:3:\"msg\";s:222:\"Someone has requested to access to your records for Artica Mailing List. You may edit your information or unsubscribe by visiting the link below ->\r\n	[[url]]\r\n\r\nIf you have received this message in error, please ignore it.\";s:3:\"sub\";s:36:\"Artica Mailing List: Account Access.\";}s:8:\"password\";a:2:{s:3:\"msg\";s:201:\"You have requested to change your password for Artica Mailing List. Please validate this request by clicking the link below ->\r\n	[[url]]\r\n\r\nIf you have received this message in error, please ignore it.\";s:3:\"sub\";s:23:\"Change Password request\";}s:6:\"update\";a:2:{s:3:\"msg\";s:198:\"You have requested to update your records for Artica Mailing List. Please validate this request by clicking the link below ->\n\n	[[url]]\n\nIf you have received this message in error, please ignore it.\";s:3:\"sub\";s:22:\"Update Records request\";}}', '', 'off', 'off'),
('notices', '', '', 'off', 'off'),
('demo_mode', 'off', 'Demonstration Mode', 'on', 'on'),
('smtp_1', 'a:5:{s:4:\"host\";s:9:\"127.0.0.1\";s:4:\"port\";s:4:\"2525\";s:4:\"auth\";s:3:\"off\";s:4:\"user\";s:0:\"\";s:4:\"pass\";s:0:\"\";}', '', 'off', 'off'),
('smtp_2', '', '', 'off', 'off'),
('smtp_3', '', '', 'off', 'off'),
('smtp_4', '', '', 'off', 'off'),
('throttle_DBPP', '0', '', 'off', 'on'),
('throttle_DP', '10', '', 'off', 'on'),
('throttle_DMPP', '0', '', 'off', 'on'),
('throttle_BPS', '0', '', 'off', 'on'),
('throttle_MPS', '3', '', 'off', 'on'),
('throttle_SMTP', 'individual', '', 'off', 'on'),
('public_history', 'on', 'Public Mailing History', 'off', 'on'),
('version', 'Aardvark PR16.1', 'poMMo Version', 'on', 'off'),
('key', '1e0b60', 'Unique Identifier', 'on', 'off'),
('revision', '42', 'Internal Revision', 'on', 'off');
";
$q->QUERY_SQL($sql,"pommo");

$sql="CREATE TABLE IF NOT EXISTS `pommo_fields` (
  `field_id` smallint(5) unsigned NOT NULL auto_increment,
  `field_active` enum('on','off') NOT NULL default 'off',
  `field_ordering` smallint(5) unsigned NOT NULL default '0',
  `field_name` varchar(60) default NULL,
  `field_prompt` varchar(60) default NULL,
  `field_normally` varchar(60) default NULL,
  `field_array` text,
  `field_required` enum('on','off') NOT NULL default 'off',
  `field_type` enum('checkbox','multiple','text','date','number','comment') default NULL,
  PRIMARY KEY  (`field_id`),
  KEY `active` (`field_active`,`field_ordering`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
";
$q->QUERY_SQL($sql,"pommo");
$sql="CREATE TABLE IF NOT EXISTS `pommo_groups` (
  `group_id` smallint(5) unsigned NOT NULL auto_increment,
  `group_name` tinytext NOT NULL,
  PRIMARY KEY  (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
";
$q->QUERY_SQL($sql,"pommo");
$sql="CREATE TABLE IF NOT EXISTS `pommo_group_rules` (
  `rule_id` int(10) unsigned NOT NULL auto_increment,
  `group_id` int(10) unsigned NOT NULL default '0',
  `field_id` tinyint(3) unsigned NOT NULL default '0',
  `type` tinyint(1) NOT NULL default '0' COMMENT '0: OFF, (and), 1: ON (or)',
  `logic` enum('is','not','greater','less','true','false','is_in','not_in') NOT NULL,
  `value` text,
  PRIMARY KEY  (`rule_id`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
";
$q->QUERY_SQL($sql,"pommo");
$sql="CREATE TABLE IF NOT EXISTS `pommo_mailings` (
  `mailing_id` int(10) unsigned NOT NULL auto_increment,
  `fromname` varchar(60) NOT NULL default '',
  `fromemail` varchar(60) NOT NULL default '',
  `frombounce` varchar(60) NOT NULL default '',
  `subject` varchar(60) NOT NULL default '',
  `body` mediumtext NOT NULL,
  `altbody` mediumtext,
  `ishtml` enum('on','off') NOT NULL default 'off',
  `mailgroup` varchar(60) NOT NULL default 'Unknown',
  `subscriberCount` int(10) unsigned NOT NULL default '0',
  `started` datetime NOT NULL,
  `finished` datetime default NULL,
  `sent` int(10) unsigned NOT NULL default '0',
  `charset` varchar(15) NOT NULL default 'UTF-8',
  `status` tinyint(1) NOT NULL default '1' COMMENT '0: finished, 1: processing, 2: cancelled',
  PRIMARY KEY  (`mailing_id`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
";
$q->QUERY_SQL($sql,"pommo");
$sql="CREATE TABLE IF NOT EXISTS `pommo_mailing_current` (
  `current_id` int(10) unsigned NOT NULL,
  `command` enum('none','restart','stop','cancel') NOT NULL default 'none',
  `serial` int(10) unsigned default NULL,
  `securityCode` char(32) default NULL,
  `notices` longtext,
  `current_status` enum('started','stopped') NOT NULL default 'stopped',
  `touched` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`current_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
";
$q->QUERY_SQL($sql,"pommo");
$sql="CREATE TABLE IF NOT EXISTS `pommo_mailing_notices` (
  `mailing_id` int(10) unsigned NOT NULL,
  `notice` varchar(255) NOT NULL,
  `touched` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `id` smallint(5) unsigned NOT NULL,
  KEY `mailing_id` (`mailing_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
";
$q->QUERY_SQL($sql,"pommo");
$sql="CREATE TABLE IF NOT EXISTS `pommo_queue` (
  `subscriber_id` int(10) unsigned NOT NULL,
  `status` tinyint(1) NOT NULL default '0' COMMENT '0: unsent, 1: sent, 2: failed',
  `smtp` tinyint(1) NOT NULL default '0' COMMENT '0: none, 1-4: Designated to SMTP relay #',
  PRIMARY KEY  (`subscriber_id`),
  KEY `status` (`status`,`smtp`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
";
$q->QUERY_SQL($sql,"pommo");
$sql="CREATE TABLE IF NOT EXISTS `pommo_scratch` (
  `scratch_id` int(10) unsigned NOT NULL auto_increment,
  `time` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `type` smallint(5) unsigned NOT NULL default '0' COMMENT 'Used to identify row type. 0 = undifined, 1 = ',
  `int` bigint(20) default NULL,
  `str` text,
  PRIMARY KEY  (`scratch_id`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='General Purpose Table for caches, counts, etc.' AUTO_INCREMENT=1 ;
";
$q->QUERY_SQL($sql,"pommo");
$sql="CREATE TABLE IF NOT EXISTS `pommo_subscribers` (
  `subscriber_id` int(10) unsigned NOT NULL auto_increment,
  `email` char(60) NOT NULL default '',
  `time_touched` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `time_registered` datetime NOT NULL,
  `flag` tinyint(1) NOT NULL default '0' COMMENT '0: NULL, 1-8: REMOVE, 9: UPDATE',
  `ip` int(10) unsigned default NULL COMMENT 'Stored with INET_ATON(), Fetched with INET_NTOA()',
  `status` tinyint(1) NOT NULL default '2' COMMENT '0: Inactive, 1: Active, 2: Pending',
  PRIMARY KEY  (`subscriber_id`),
  KEY `status` (`status`,`subscriber_id`),
  KEY `status_2` (`status`,`email`),
  KEY `status_3` (`status`,`time_touched`),
  KEY `status_4` (`status`,`time_registered`),
  KEY `status_5` (`status`,`ip`),
  KEY `flag` (`flag`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
";
$q->QUERY_SQL($sql,"pommo");
$sql="CREATE TABLE IF NOT EXISTS `pommo_subscriber_data` (
  `data_id` bigint(20) unsigned NOT NULL auto_increment,
  `field_id` int(10) unsigned NOT NULL default '0',
  `subscriber_id` int(10) unsigned NOT NULL default '0',
  `value` char(60) NOT NULL default '',
  PRIMARY KEY  (`data_id`),
  KEY `subscriber_id` (`subscriber_id`,`field_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
";

$q->QUERY_SQL($sql,"pommo");
$sql="CREATE TABLE IF NOT EXISTS `pommo_subscriber_pending` (
  `pending_id` int(10) unsigned NOT NULL auto_increment,
  `subscriber_id` int(10) unsigned NOT NULL default '0',
  `pending_code` char(32) NOT NULL,
  `pending_type` enum('add','del','change','password') default NULL,
  `pending_array` text,
  PRIMARY KEY  (`pending_id`),
  KEY `code` (`pending_code`),
  KEY `subscriber_id` (`subscriber_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
";
$q->QUERY_SQL($sql,"pommo");


$sql="CREATE TABLE IF NOT EXISTS `pommo_templates` (
  `template_id` smallint(5) unsigned NOT NULL auto_increment,
  `name` varchar(60) NOT NULL default 'name',
  `description` varchar(255) default NULL,
  `body` mediumtext,
  `altbody` mediumtext,
  PRIMARY KEY  (`template_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;
";
$q->QUERY_SQL($sql,"pommo");

$sql="INSERT INTO `pommo_templates` (`template_id`, `name`, `description`, `body`, `altbody`) VALUES
(1, 'CSS Example', 'poMMo default, featuring a plain CSS based HTML design. Includes a weblink (to view online) in the header, and a unsubscribe link in the footer.', '<style type=\"text/css\" media=\"all\">\r\n\r\n/* base styling */\r\n\r\ndiv.pommoMailing {\r\n\r\n  background-color: white; /* background color */\r\n  color: #333; /* text color */\r\n  width: 100%;\r\n  padding: 6px;\r\n\r\n}\r\n\r\ndiv.pommoMailing a, div.pommoMailing a:visited {\r\n\r\n  text-decoration: none;\r\n  color: #0067ff; /* link & visited link color */\r\n\r\n}\r\n\r\n/* header styling */\r\n\r\ndiv.pommoMailing div.pommoHeader {\r\n  \r\n  border: 1px solid black;\r\n  padding: 6px;\r\n  background-color: #DDF0BD; /* light green */\r\n  color: green;\r\n  width: 100%;\r\n  text-align: center;\r\n}\r\n\r\n/* footer styling */\r\n\r\ndiv.pommoMailing div.pommoFooter {\r\n  width: 100%;\r\n  padding: 5px 12px;\r\n  background-color: black;\r\n  color: #DDD;\r\n}\r\n\r\ndiv.pommoMailing p.smaller {\r\n  font-size: 80%;\r\n}\r\n\r\n\r\n</style>\r\n\r\n<div class=\"pommoMailing\">\r\n\r\n  <div class=\"pommoHeader\">\r\n    If you are having trouble viewing this email, <a href=\"[[!weblink]]\">click here</a>.\r\n  </div>\r\n\r\n  <h2>Bonjour!</h2>\r\n  <br />\r\n \r\n  <p>\r\n  Aliquam tempor erat eu sapien. Proin nisl lorem, hendrerit ut, venenatis vel, consequat in, est. In hac habitasse platea dictumst. Praesent malesuada tristique massa. Donec nec dui. Nulla at ligula quis diam auctor vulputate. Sed ligula ligula, elementum ac, tincidunt nec, accumsan non, risus. Aliquam convallis blandit tortor. In porta nisi interdum ante. Maecenas sem. Maecenas at felis ac massa dictum malesuada. Maecenas quis lectus. In hac habitasse platea dictumst. Ut sit amet nunc. Donec non lacus. Nulla facilisi. Vestibulum molestie. Aenean at enim sit amet augue auctor bibendum. Duis in ipsum.\r\n  </p>\r\n\r\n  <div class=\"pommoFooter\">\r\n  To unsubscribe or update your records, <a href=\"[[!unsubscribe]]\">Click here</a>.\r\n  </div>\r\n\r\n  <p class=\"smaller\">\r\n    Message sent with <a href=\"http://www.pommo.org/\">poMMo</a>.\r\n  </p>\r\n\r\n</div>', NULL);";
$q->QUERY_SQL($sql,"pommo");


$sql="CREATE TABLE IF NOT EXISTS `pommo_updates` (
  `serial` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`serial`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
";

$sql="INSERT INTO `pommo_updates` (`serial`) VALUES (24);";	
$q->QUERY_SQL($sql,"pommo");
}





?>