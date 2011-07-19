<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__)."/ressources/class.obm.inc");
include_once(dirname(__FILE__)."/ressources/class.templates.inc");
include_once(dirname(__FILE__)."/ressources/class.mysql.inc");
include_once(dirname(__FILE__)."/framework/frame.class.inc");
$param=$argv[1];
if($param=='--all'){all();die();}
if($param=='--fix-db'){fixdb();die();}


if(preg_match("#--user=(.+)#",$param,$re)){
	write_syslog("Checking {$re[1]}",__FILE__);	
	$obm=new obm_user($re[1]);
	$obm2=new OBM2();
	$obm2->CleanOBMGhostAccount();	
	die();
}




function all(){

$ldap=new clladp();
$ous=$ldap->hash_get_ou();
if(!is_array($ous)){
	write_syslog("No organizations found, abort exporting OBM v2 users",__FILE__);
	
	die();
}

while (list ($num, $organization) = each ($ous) ){
	SyncOrg($organization);
	
}

	$obm2=new OBM2();
	$obm2->CleanOBMGhostAccount();	
}


function SyncOrg($ou){
	
	
	$ldap=new clladp();
	$hash=$ldap->UserSearch($ou,"");
	
	for($i=0;$i<$hash["count"];$i++){
		$uid=$hash[$i]["uid"][0];
		write_syslog("Checking $uid",__FILE__);	
		$obm=new obm_user($uid);
		
		
		
	}
	
	
	
	
	
	
	
}


function fixdb(){
	
	
$q=new mysql();

if(!$q->TABLE_EXISTS('Profile','obm2')){
	write_syslog("Create Profile table in obm2 database",__FILE__);
	$sql="CREATE TABLE `Profile` (
	  `profile_id` int(8) NOT NULL auto_increment,
	  `profile_domain_id` int(8) NOT NULL,
	  `profile_timeupdate` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
	  `profile_timecreate` timestamp NOT NULL default '0000-00-00 00:00:00',
	  `profile_userupdate` int(8) default NULL,
	  `profile_usercreate` int(8) default NULL,
	  `profile_name` varchar(64) default NULL,
	  PRIMARY KEY  (`profile_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
	$q->QUERY_SQL($sql,"obm2");
}
if(!$q->TABLE_EXISTS('ProfileModule','obm2')){
	write_syslog("Create ProfileModule table in obm2 database",__FILE__);
	$sql="CREATE TABLE `ProfileModule` (
	  `profilemodule_id` int(8) NOT NULL auto_increment,
	  `profilemodule_domain_id` int(8) NOT NULL,
	  `profilemodule_profile_id` int(8) default NULL,
	  `profilemodule_module_name` varchar(64) NOT NULL default '',
	  `profilemodule_right` int(2) default NULL,
	  PRIMARY KEY  (`profilemodule_id`),
	  KEY `profilemodule_profile_id_profile_id_fkey` (`profilemodule_profile_id`),
	  CONSTRAINT `profilemodule_profile_id_profile_id_fkey` FOREIGN KEY (`profilemodule_profile_id`) REFERENCES `Profile` (`profile_id`) ON DELETE CASCADE ON UPDATE CASCADE
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
	$q->QUERY_SQL($sql,"obm2");
}

if(!$q->TABLE_EXISTS('ProfileModule','obm2')){
	write_syslog("Create ProfileModule table in obm2 database",__FILE__);
	$sql="
		CREATE TABLE `ProfileProperty` (
		  `profileproperty_id` int(8) NOT NULL auto_increment,
		  `profileproperty_profile_id` int(8) default NULL,
		  `profileproperty_name` varchar(32) NOT NULL default '',
		  `profileproperty_value` text NOT NULL,
		  PRIMARY KEY  (`profileproperty_id`),
		  KEY `profileproperty_profile_id_profile_id_fkey` (`profileproperty_profile_id`),
		  CONSTRAINT `profileproperty_profile_id_profile_id_fkey` FOREIGN KEY (`profileproperty_profile_id`) REFERENCES `Profile` (`profile_id`) ON DELETE CASCADE ON UPDATE CASCADE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		$q->QUERY_SQL($sql,"obm2");
	}
}





?>
