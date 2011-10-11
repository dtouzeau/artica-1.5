<?php
include_once(dirname(__FILE__).'/class.mysql.squid.builder.php');

class mysql_builder{
	var $ClassSQL;
	var $ok=false;
	var $mysql_error;
	var $UseMysql=true;
	
	function mysql_builder(){
		$this->ClassSQL=new mysql();
		$this->UseMysql=$this->ClassSQL->UseMysql;
		
	}
	
	private function TABLE_EXISTS($table,$database){
		return $this->ClassSQL->TABLE_EXISTS($table,$database);
	}
	private function DATABASE_EXISTS($database){
		return $this->ClassSQL->DATABASE_EXISTS($database);
	}
	
	private function FIELD_EXISTS($table,$field,$database){
		return $this->ClassSQL->FIELD_EXISTS($table,$field,$database);
	}
	
	private function QUERY_SQL($sql,$database=null){
		$this->ClassSQL->QUERY_SQL($sql,$database);
		$this->ok=$this->ClassSQL->ok;
		$this->mysql_error=$this->ClassSQL->mysql_error;
	}
	
	private function FIELD_TYPE($table,$field,$database){
		return $this->ClassSQL->FIELD_TYPE($table,$field,$database);
	}
	
	private FUNCTION INDEX_EXISTS($table,$index,$database){
		return $this->ClassSQL->INDEX_EXISTS($table,$index,$database);
	}
	
	private FUNCTION CREATE_DATABASE($database){
		return $this->ClassSQL->CREATE_DATABASE($database);
	}
	
	
	public function Check_reports_table(){
		if(!$this->TABLE_EXISTS('reports','artica_backup')){		
			$sql="CREATE TABLE `artica_backup`.`reports` (
			`ID` INT( 5 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`report_type` INT( 1 ) NOT NULL ,
			`report_datas` TEXT NOT NULL ,
			`enabled` INT( 1 ) NOT NULL ,
			`ou` VARCHAR( 255 ) NOT NULL ,
			INDEX ( `report_type` , `enabled` , `ou` )
			)";		
			$this->QUERY_SQL($sql,'artica_backup');
		}
	}	
	
public function check_storage_table(){
		if(!$this->DATABASE_EXISTS("artica_backup")){$this->CREATE_DATABASE("artica_backup");}
		if(!$this->DATABASE_EXISTS("eyeOS")){$this->CREATE_DATABASE("eyeOS");}	

		
		if(!$this->TABLE_EXISTS('amanda_dumptype','artica_backup')){		
			$sql="CREATE TABLE `artica_backup`.`amanda_dumptype` (
				`dumpname` VARCHAR( 90 ) NOT NULL ,
				`auth` VARCHAR( 20 ) NOT NULL ,
				`comment` VARCHAR( 128 ) NOT NULL ,
				`comprate` VARCHAR( 10 ) NOT NULL ,
				`compress` VARCHAR( 10 ) NOT NULL ,
				`dumpcycle` VARCHAR(20 ) NOT NULL ,
				`estimate` VARCHAR( 10 ) NOT NULL ,
				`holdingdisk` INT( 1 ) NOT NULL DEFAULT '1',
				`index` INT( 1 ) NOT NULL DEFAULT '1',
				`maxdumps` INT( 1 ) NOT NULL DEFAULT '1',
				`maxpromoteday` INT( 5 ) NOT NULL DEFAULT '1000',
				`priority` VARCHAR( 10 ) NOT NULL DEFAULT 'medium',
				`program` VARCHAR( 10 ) NOT NULL DEFAULT 'DUMP',
				`skip-full` INT( 1 ) NOT NULL DEFAULT '1',
				`skip-incr` INT( 1 ) NOT NULL DEFAULT '0',
				`strategy` VARCHAR( 10 ) NOT NULL ,
				PRIMARY KEY ( `dumpname` )
				)";
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);}
		}

		
		if(!$this->TABLE_EXISTS('amanda_hosts','artica_backup')){		
			$sql="CREATE TABLE `artica_backup`.`amanda_hosts` (
			`ID` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`hostname` VARCHAR( 128 ) NOT NULL ,
			`dumptype` VARCHAR( 50 ) NOT NULL ,
			`directory` VARCHAR( 255 ) NOT NULL ,
			`resolved` VARCHAR( 90 ) NOT NULL ,
			INDEX ( `hostname` )
			)";
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);}
		} 		
		
		
		if(!$this->TABLE_EXISTS('ldap_ou_import','artica_backup')){		
			$sql="CREATE TABLE IF NOT EXISTS `ldap_ou_import` (
			  `ID` int(11) NOT NULL AUTO_INCREMENT,
			  `ou` varchar(250) NOT NULL,
			  `ScheduleMin` varchar(250) NOT NULL,
			  `ldapdatas` blob NOT NULL,
			  `enabled` int(1) NOT NULL DEFAULT '0',
			  `connexion_name` VARCHAR( 255 ) NOT NULL ,
			  PRIMARY KEY (`ID`),
			  KEY `ScheduleMin` (`ScheduleMin`),
			  KEY `ou` (`ou`),
			  KEY `enabled` (`enabled`),
			  KEY `connexion_name` (`connexion_name`)
			)";
			$this->QUERY_SQL($sql,'artica_backup');
		}			 		
	
	
		if(!$this->TABLE_EXISTS('storage','artica_backup')){		
			$sql="CREATE TABLE IF NOT EXISTS `storage` (
				  `MessageID` varchar(80) NOT NULL,
				  `zDate` datetime NOT NULL,
				  `mailfrom` varchar(255) NOT NULL,
				  `subject` varchar(255) NOT NULL,
				  `MessageBody` text NOT NULL,
				  `organization` varchar(80) NOT NULL,
				  `uid` varchar(50) NOT NULL,
				  `file_path` varchar(255) DEFAULT NULL,
				  `original_messageid` varchar(80) NOT NULL,
				  `message_size` int(10) NOT NULL,
				  `mailfrom_domain` varchar(128) NOT NULL,
				  `mailto` varchar(255) NOT NULL,
				  `BinMessg` longblob NOT NULL,
				  `filename` varchar(128) NOT NULL,
				  `filesize` int(10) NOT NULL,
				  PRIMARY KEY (`MessageID`),
				  KEY `mailfrom` (`mailfrom`),
				  KEY `organization` (`organization`),
				  KEY `uid` (`uid`),
				  KEY `original_messageid` (`original_messageid`),
				  KEY `mailfrom_domain` (`mailfrom_domain`),
				  KEY `mailto` (`mailto`),
				  KEY `filename` (`filename`),
				  FULLTEXT KEY `MessageBody` (`MessageBody`)
				) ;";
				
				$this->QUERY_SQL($sql,'artica_backup');
				}
				
		if(!$this->FIELD_EXISTS("storage","BinMessg","artica_backup")){
			$sql="ALTER TABLE `storage` ADD `BinMessg` LONGBLOB NOT NULL ";
			$this->QUERY_SQL($sql,"artica_backup");
		}

		if(!$this->FIELD_EXISTS("storage","filename","artica_backup")){
			$sql="ALTER TABLE `storage` ADD `filename` VARCHAR( 128 ) NOT NULL ,ADD INDEX ( filename )";
			$this->QUERY_SQL($sql,"artica_backup");
		}	
		
		if(!$this->FIELD_EXISTS("storage","filesize","artica_backup")){
			$sql="ALTER TABLE `storage` ADD `filesize` INT( 10 ) NOT NULL";
			$this->QUERY_SQL($sql,"artica_backup");
		}			

		if(!$this->TABLE_EXISTS('backup_schedules','artica_backup')){				 
				$sql="CREATE TABLE IF NOT EXISTS `backup_schedules` (
				  `ID` int(5) NOT NULL AUTO_INCREMENT,
				  `zMD5` varchar(90) NOT NULL,
				  `resource_type` varchar(10) NOT NULL,
				  `pattern` varchar(255) NOT NULL,
				  `schedule` varchar(90) NOT NULL,
				  `datasbackup` text NOT NULL,
				  `container` varchar(20) NOT NULL,
				  `pid` INT( 5 ) NOT NULL,
				  PRIMARY KEY (`ID`),
				  UNIQUE KEY `zMD5` (`zMD5`),
				  KEY `resource_type` (`resource_type`,`pattern`),
				  KEY `container` (`container`)) ";
				$this->QUERY_SQL($sql,'artica_backup');
		}
		
		if(!$this->FIELD_EXISTS("backup_schedules","pid","artica_backup")){
			$sql="ALTER TABLE `backup_schedules` ADD `pid` INT( 5 ) NOT NULL";
			$this->QUERY_SQL($sql,"artica_backup");
		}	

		if(!$this->TABLE_EXISTS('backup_storages','artica_backup')){				 
				$sql="CREATE TABLE IF NOT EXISTS `backup_storages` (
			  `taskid` int(10) NOT NULL,
			  `zDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			  `cnx_params` text NOT NULL,
			  `size` int(100) NOT NULL,
			  `ID` int(100) NOT NULL AUTO_INCREMENT,
			  `zmd5` varchar(90) ,
			  PRIMARY KEY (`ID`),
			  UNIQUE KEY `zmd5` (`zmd5`),
			  KEY `taskid` (`taskid`,`zDate`,`size`)
			) ";
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}	
		}						
		
		


		if(!$this->TABLE_EXISTS('files_storage','artica_backup')){		
			$sql="CREATE TABLE IF NOT EXISTS `files_storage` (
				  `id_files` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
				  `bin_data` longblob NOT NULL,
				  `description` tinytext NOT NULL,
				  `filename` varchar(50) NOT NULL,
				  `filesize` varchar(50) NOT NULL,
				  `filetype` varchar(50) NOT NULL,
				  PRIMARY KEY (`id_files`)
					)";
			$this->QUERY_SQL($sql,'artica_backup');
		}
	
		if(!$this->FIELD_EXISTS("files_storage","commandline","artica_backup")){
			$sql="ALTER TABLE `files_storage` ADD `commandline` VARCHAR( 200 ) NOT NULL";
			$this->QUERY_SQL($sql,"artica_backup");
		}

		if(!$this->FIELD_EXISTS("files_storage","ExecuteAfter","artica_backup")){
			$sql="ALTER TABLE `files_storage` ADD `ExecuteAfter` VARCHAR( 200 ) NOT NULL";
			$this->QUERY_SQL($sql,"artica_backup");
		}

		if(!$this->FIELD_EXISTS("files_storage","OCS_PACKAGE","artica_backup")){
			$sql="ALTER TABLE `files_storage` ADD `OCS_PACKAGE` INT( 1 ) NOT NULL DEFAULT '0',ADD INDEX ( OCS_PACKAGE ) ";
			$this->QUERY_SQL($sql,"artica_backup");
		}	

	if(!$this->TABLE_EXISTS('deploy_tasks','artica_backup')){	
		$sql="CREATE TABLE IF NOT EXISTS `deploy_tasks` (
			  `ID` int(5) NOT NULL AUTO_INCREMENT,
			  `files_id` int(5) NOT NULL,
			  `computer_id` varchar(90) NOT NULL,
			  `results` text NOT NULL,
			  `executed` int(1) NOT NULL DEFAULT '0',
			  `commandline` varchar(255) NOT NULL,
			  `username` varchar(128) NOT NULL,
			  `password` varchar(128) NOT NULL,
			  `PROGRESS` varchar(128) NOT NULL,
			  `pourcent` INT( 2 ) NOT NULL,
			  `TASK_TYPE` VARCHAR( 60 ) NOT NULL,
			  PRIMARY KEY (`ID`),
			  KEY `files_id` (`files_id`,`computer_id`),
			  KEY `computer_id` (`computer_id`),
			  KEY `executed` (`executed`),
			  KEY `TASK_TYPE` (`TASK_TYPE`),
			  KEY `PROGRESS` (`PROGRESS`)
			) ";
		$this->QUERY_SQL($sql,'artica_backup');	
	
		}
		
	if(!$this->FIELD_EXISTS("deploy_tasks","TASK_TYPE","artica_backup")){
		$sql="ALTER TABLE `deploy_tasks` ADD `TASK_TYPE` VARCHAR( 60 ) NOT NULL,ADD INDEX ( `TASK_TYPE` )";
		$this->QUERY_SQL($sql,"artica_backup");
	}

	if(!$this->FIELD_EXISTS("deploy_tasks","PROGRESS","artica_backup")){
		$sql="ALTER TABLE `deploy_tasks` ADD `PROGRESS` VARCHAR( 60 ) NOT NULL,ADD INDEX ( `PROGRESS` )";
		$this->QUERY_SQL($sql,"artica_backup");
	}	

	if(!$this->FIELD_EXISTS("deploy_tasks","debug_mode","artica_backup")){
		$sql="ALTER TABLE `deploy_tasks` ADD `debug_mode` INT( 1 ) NOT NULL DEFAULT '0'";
		$this->QUERY_SQL($sql,"artica_backup");
	}		
		
	if(!$this->FIELD_EXISTS("deploy_tasks","pourcent","artica_backup")){
		$sql="ALTER TABLE `deploy_tasks` ADD `pourcent` INT(2) NOT NULL DEFAULT '0'";
		$this->QUERY_SQL($sql,"artica_backup");
	}	

	if(!$this->FIELD_EXISTS("storage","file_path","artica_backup")){
		$sql="ALTER TABLE `storage` ADD `file_path` varchar(255) NOT NULL ,ADD INDEX ( `file_path` )";
		$this->QUERY_SQL($sql,"artica_backup");
	}
	
	if(!$this->FIELD_EXISTS("storage","original_messageid","artica_backup")){
		$sql="ALTER TABLE `storage` ADD `original_messageid` varchar(80) NOT NULL ,ADD INDEX ( `original_messageid` )";
		$this->QUERY_SQL($sql,"artica_backup");
	}	
	
	if(!$this->FIELD_EXISTS("storage","message_size","artica_backup")){
		$sql="ALTER TABLE `storage` ADD `message_size` INT(10) NOT NULL ";
		$this->QUERY_SQL($sql,"artica_backup");
	}		

	if(!$this->FIELD_EXISTS("storage","mailfrom_domain","artica_backup")){
		$sql="ALTER TABLE `storage` ADD `mailfrom_domain` varchar(255) NOT NULL, ADD INDEX ( `mailfrom_domain` )";
		$this->QUERY_SQL($sql,"artica_backup");
	}	

	
	if(!$this->FIELD_EXISTS("storage","mailto","artica_backup")){
		$sql="ALTER TABLE `storage` ADD `mailto` varchar(255) NOT NULL ,ADD INDEX ( `mailto` )";
		$this->QUERY_SQL($sql,"artica_backup");
	}
	
	
	if(!$this->TABLE_EXISTS('officials_domains','artica_backup')){		
			$sql="CREATE TABLE `artica_backup`.`officials_domains` (`domain` VARCHAR( 255 ) NOT NULL ,UNIQUE (`domain`))";
			$this->QUERY_SQL($sql,"artica_backup");
	}
	
	
	if(!$this->TABLE_EXISTS('register_orgs','artica_backup')){		
			$sql="CREATE TABLE IF NOT EXISTS `register_orgs` (
					  `email` varchar(255)  NOT NULL,
					  `ou` varchar(128)  NOT NULL,
					  `company` varchar(128)  NOT NULL,
					  `domain` varchar(256)  NOT NULL,
					  `username` varchar(128)  NOT NULL,
					  `password` varchar(256)  NOT NULL,
					  `zmd5` varchar(90)  NOT NULL,
					  `sended` int(1) NOT NULL DEFAULT '0',
					  `register_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
					  UNIQUE KEY `email` (`email`),
					  UNIQUE KEY `ou` (`ou`),
					  UNIQUE KEY `zmd5` (`zmd5`),
					  KEY `username` (`username`),
					  KEY `sended` (`sended`),
					  KEY `time` (`register_date`)
					)";
	$this->QUERY_SQL($sql,"artica_backup");
	}			
				
	
	if(!$this->TABLE_EXISTS('computers_storage','artica_backup')){		
			$sql="CREATE TABLE `artica_backup`.`computers_storage` (
			`ID` INT( 5 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`PUBLISHER` VARCHAR( 255 ) NOT NULL ,
			`NAME` VARCHAR( 255 ) NOT NULL ,
			`local_path` VARCHAR( 255 ) NOT NULL ,
			`command_line` VARCHAR( 255 ) NOT NULL ,
			`version` VARCHAR(45) NOT NULL ,
			KEY `PUBLISHER` (`PUBLISHER`),
			KEY `NAME` (`NAME`)
			
			)	";
			$this->QUERY_SQL($sql,"artica_backup");
	}
	
	
	if(!$this->TABLE_EXISTS('smbstatus_users','artica_events')){		
			$sql="CREATE TABLE IF NOT EXISTS `smbstatus_users` (
				  `ID` int(10) NOT NULL AUTO_INCREMENT,
				  `pid` int(5) NOT NULL,
				  `username` varchar(128) NOT NULL,
				  `usersgroup` varchar(128) NOT NULL,
				  `computer` varchar(128) NOT NULL,
				  `ip_addr` varchar(50) NOT NULL,
				  `sharename` varchar(128) NOT NULL,
				  `zDate` datetime NOT NULL,
				  PRIMARY KEY (`ID`),
				  KEY `username` (`username`,`usersgroup`),
				  KEY `zDate` (`zDate`),
				  KEY `pid` (`pid`)
				) ";	
	
			$this->QUERY_SQL($sql,"artica_events");
	}		

	if(!$this->TABLE_EXISTS('smbstatus_users_dirs','artica_events')){		
			$sql="CREATE TABLE IF NOT EXISTS `smbstatus_users_dirs` (
				  `zmd5` varchar(90) NOT NULL,
				  `directory` varchar(128) NOT NULL,
				  `filepath` varchar(255) NOT NULL,
				  `zDate` datetime NOT NULL,
				  `pid` int(5) NOT NULL,
				  PRIMARY KEY (`zmd5`),
				  KEY `zDate` (`zDate`),
				  KEY `directory` (`directory`),
				  KEY `filepath` (`filepath`),
				  KEY `pid` (`pid`)
				) ";	
	
			$this->QUERY_SQL($sql,"artica_events");
	}	
	if(!$this->TABLE_EXISTS('ps_mem_tot','artica_events')){		
			$sql="CREATE TABLE `artica_events`.`ps_mem_tot` (
			`zDate` TIMESTAMP NOT NULL ,
			`mem` BIGINT( 100 ) NOT NULL ,
			PRIMARY KEY ( `zDate` ) ,
			INDEX ( `mem` )
			)";	
	
			$this->QUERY_SQL($sql,"artica_events");
	}	
	
	

	
	if(!$this->TABLE_EXISTS('ps_mem','artica_events')){		
			$sql="CREATE TABLE IF NOT EXISTS `ps_mem` (
			  `zDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			  `process` varchar(90) NOT NULL,
			  `memory` BIGINT(100) NOT NULL,
			  `zmd5` varchar(90) NOT NULL,
			  `process_num` INT(5) NOT NULL,
			  PRIMARY KEY (`zmd5`),
			  KEY `zDate` (`zDate`,`process`),
			  KEY `process_num` (`process_num`)
			)";	
	
			$this->QUERY_SQL($sql,"artica_events");
	}	
	
	

	
	
	if(!$this->TABLE_EXISTS('samba_recycle_bin','artica_backup')){		
			$sql="CREATE TABLE `artica_backup`.`samba_recycle_bin` (
				`sharename` VARCHAR( 90 ) NOT NULL ,
				`path` VARCHAR( 255 ) NOT NULL ,
				`MaxFilesDay` INT( 2 ) NOT NULL ,
				PRIMARY KEY ( `sharename` ) ,
				INDEX ( `MaxFilesDay` )
				) ";
			$this->QUERY_SQL($sql,"artica_backup");
	}	
	
	
	if(!$this->TABLE_EXISTS('samba_recycle_bin_list','artica_backup')){		
				$sql="CREATE TABLE IF NOT EXISTS `samba_recycle_bin_list` (
			  `path` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			  `uid` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
			  `sharename` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
			  `filesize` int(50) NOT NULL,
			  `restore` int(1) DEFAULT NULL,
				  `delete` int(1) DEFAULT NULL,
				  `zDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				  PRIMARY KEY (`path`),
				  KEY `uid` (`uid`),
				  KEY `sharename` (`sharename`),
				  KEY `filesize` (`filesize`),
				  KEY `zDate` (`zDate`),
				  KEY `restore` (`restore`),
				  KEY `delete` (`delete`)
				)";
			$this->QUERY_SQL($sql,"artica_backup");
	}	
	
	
	if(!$this->TABLE_EXISTS('icons_db','artica_backup')){		
				$sql="CREATE TABLE IF NOT EXISTS `icons_db` (
				  `zmd5` varchar(90) NOT NULL,
				  `title` varchar(255) NOT NULL,
				  `text` text NOT NULL,
				  `icon` varchar(40) NOT NULL,
				  `js` varchar(255) NOT NULL,
				  `lang` varchar(10) NOT NULL,
				  `family` varchar(40) NOT NULL,
				  `uid` VARCHAR( 90 ) NOT NULL ,
				  PRIMARY KEY (`zmd5`),
				  KEY `lang` (`lang`,`family`),
				  KEY `title` (`title`),
				  KEY `uid` (`uid`),
				  KEY `family` (`family`),
				  FULLTEXT KEY `title_2` (`title`),
				  FULLTEXT KEY `text` (`text`)
				)";
			$this->QUERY_SQL($sql,"artica_backup");
	}	
	 	
	
	if(!$this->FIELD_EXISTS("samba_recycle_bin_list","restore","artica_backup")){
		$sql="ALTER TABLE `samba_recycle_bin_list` ADD `restore` INT(1) NOT NULL ,ADD INDEX ( `restore` )";
		$this->QUERY_SQL($sql,"artica_backup");
	}	
	
	if(!$this->FIELD_EXISTS("samba_recycle_bin_list","delete","artica_backup")){
		$sql="ALTER TABLE `samba_recycle_bin_list` ADD `delete` INT(1) NOT NULL,ADD INDEX ( `delete` )";
		$this->QUERY_SQL($sql,"artica_backup");
	}		
	
	
	if(!$this->TABLE_EXISTS('getent_users','artica_backup')){		
			$sql="CREATE TABLE `artica_backup`.`getent_users` (
		`uid` VARCHAR( 128 ) NOT NULL ,
		PRIMARY KEY ( `uid` )
		)";
		$this->QUERY_SQL($sql,"artica_backup");
	}

	if(!$this->TABLE_EXISTS('getent_groups','artica_backup')){		
			$sql="CREATE TABLE `artica_backup`.`getent_groups` (
			`group` VARCHAR( 128 ) NOT NULL ,
			PRIMARY KEY ( `group` )
			)";
		$this->QUERY_SQL($sql,"artica_backup");
	}
	
	if(!$this->TABLE_EXISTS('samba_domains_info','artica_backup')){		
			$sql="CREATE TABLE `artica_backup`.`samba_domains_info` (
				`domain` VARCHAR( 80 ) NOT NULL ,
				`Alt_Name` VARCHAR( 128 ) NOT NULL ,
				`SID` VARCHAR( 90 ) NOT NULL ,
				`AD` INT( 1 ) NOT NULL ,
				`Native` INT( 1 ) NOT NULL ,
				`Primary` INT( 1 ) NOT NULL ,
				INDEX ( `Primary` ) ,
				UNIQUE (`domain`)
				)";
		$this->QUERY_SQL($sql,"artica_backup");
	}	
	
	
	if(!$this->TABLE_EXISTS('cgroups_groups','artica_backup')){		
			$sql="CREATE TABLE IF NOT EXISTS `cgroups_groups` (
		  `ID` int(5) NOT NULL AUTO_INCREMENT,
		  `groupname` varchar(50) NOT NULL,
		  `group_description` varchar(255) NOT NULL,
		  `memory_limit_in_bytes` int(10) NOT NULL,
		  `memory_force_empty` int(1) NOT NULL DEFAULT '0',
		  `memory_memsw_limit_in_bytes` int(10) NOT NULL,
		  `memory_soft_limit_in_bytes` int(10) NOT NULL,
		  `memory_swappiness` int(1) NOT NULL DEFAULT '0',
		  `cpu_shares` int(10) NOT NULL,
		  `cpu_rt_runtime_us` int(10) NOT NULL,
		  `cpu_rt_period_us` int(10) NOT NULL,
		  `cpuset_cpus` varchar(60) NOT NULL DEFAULT '0,1,2,3,4,5,6,7,8',
		  PRIMARY KEY (`ID`),
		  UNIQUE KEY `groupname` (`groupname`),
		  KEY `cpu_shares` (`cpu_shares`),
		  KEY `memory_limit_in_bytes` (`memory_limit_in_bytes`)
		)";
		$this->QUERY_SQL($sql,"artica_backup");
	} 

	if(!$this->TABLE_EXISTS('nmap_events','artica_events')){		
			$sql="CREATE TABLE IF NOT EXISTS `nmap_events` (
			  `ID` int(100) NOT NULL AUTO_INCREMENT,
			  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			  `zDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  `text` text NOT NULL,
			  `uid` varchar(90) NOT NULL,
			  PRIMARY KEY (`ID`),
			  KEY `zDate` (`zDate`),
			  KEY `uid` (`uid`)
			)";	
			$this->QUERY_SQL($sql,"artica_events");
	}
	
	if(!$this->TABLE_EXISTS('artica_patchs','artica_backup')){		
			$sql="CREATE TABLE `artica_backup`.`artica_patchs` (
			`patch_number` INT( 100 ) NOT NULL ,
			`path_explain` TEXT NOT NULL ,
			`filename` varchar(50) NOT NULL,
			`size` INT( 100 ) NOT NULL,
			`updated` INT( 1 ) NOT NULL DEFAULT '0',
			PRIMARY KEY ( `patch_number` ) ,
			INDEX ( `updated` ,`filename`)
			)";	
			$this->QUERY_SQL($sql,"artica_backup");
	}	
	
	
	if(!$this->TABLE_EXISTS('cgroups_processes','artica_backup')){		
			$sql="CREATE TABLE IF NOT EXISTS `cgroups_processes` (
				  `process_name` varchar(255) NOT NULL,
				  `groupid` int(10) NOT NULL,
				  `user` varchar(60) NOT NULL,
				  PRIMARY KEY (`process_name`),
				  KEY `groupid` (`groupid`),
				  KEY `user` (`user`)
				)"; 

		$this->QUERY_SQL($sql,"artica_backup");
	}
	
	if(!$this->FIELD_EXISTS("cgroups_processes","user","artica_backup")){
		$sql="ALTER TABLE `cgroups_processes` ADD `user` varchar(60) NOT NULL,ADD INDEX ( `user` )";
		$this->QUERY_SQL($sql,"artica_backup");
	}		

	
	if(!$this->TABLE_EXISTS('activedirectory_users','artica_backup')){		
			$sql="CREATE TABLE IF NOT EXISTS `activedirectory_users` (
			  `dn` varchar(255) NOT NULL,
			  `samaccountname` varchar(128) NOT NULL,
			  `mail` varchar(255) NOT NULL,
			  `userprincipalname` varchar(128) NOT NULL,
			  `displayname` varchar(128) NOT NULL,
			  `ou` varchar(90) NOT NULL,
			  `telephonenumber` varchar(30) NOT NULL,
			  `mobile` varchar(30) NOT NULL,
			  `givenname` varchar(60) NOT NULL,
			  `title` varchar(120) NOT NULL,
			  `sn` varchar(128) NOT NULL,
			  PRIMARY KEY (`dn`),
			  KEY `samaccountname` (`samaccountname`),
			  KEY `mail` (`mail`),
			  KEY `userprincipalname` (`userprincipalname`),
			  KEY `displayname` (`displayname`),
			  KEY `ou` (`ou`),
			  KEY `givenname` (`givenname`,`title`,`sn`)
			) ";
		$this->QUERY_SQL($sql,"artica_backup");
	}	
	
	

	if(!$this->TABLE_EXISTS('activedirectory_groups','artica_backup')){		
			$sql="CREATE TABLE IF NOT EXISTS `activedirectory_groups` (
			  `groupdn` varchar(255) NOT NULL,
			  `userdn` varchar(255) NOT NULL,
			  `mdkey` varchar(90) NOT NULL,
			  PRIMARY KEY (`mdkey`),
			  KEY `userdn` (`userdn`),
			  KEY `groupdn` (`groupdn`)
			) ";
			$this->QUERY_SQL($sql,"artica_backup");
	} 	
	
	
	
	if(!$this->TABLE_EXISTS('activedirectory_groupsNames','artica_backup')){		
			$sql="CREATE TABLE IF NOT EXISTS `activedirectory_groupsNames` (
				  `dn` varchar(255) NOT NULL,
				  `groupname` varchar(128) NOT NULL,
				  `UsersCount` int(5) NOT NULL,
				  `description` VARCHAR( 255 ) NOT NULL,
				  PRIMARY KEY (`dn`),
				  KEY `groupname` (`groupname`,`UsersCount`)
				) ";
			$this->QUERY_SQL($sql,"artica_backup");
	} 
	
	if(!$this->FIELD_EXISTS("activedirectory_groupsNames","description","artica_backup")){
		$sql="ALTER TABLE `activedirectory_groupsNames` ADD `description` VARCHAR( 255 ) NOT NULL ";
		$this->QUERY_SQL($sql,"artica_backup");
	}	

	
	if(!$this->TABLE_EXISTS('activedirectory_orgs','artica_backup')){		
			$sql="CREATE TABLE IF NOT EXISTS `activedirectory_orgs` (
			  `ou` varchar(80) NOT NULL,
			  `dn` varchar(255) NOT NULL,
			  UNIQUE KEY `dn` (`dn`),
			  KEY `ou` (`ou`)
			) ";
			$this->QUERY_SQL($sql,"artica_backup");
	} 	
	
	if(!$this->FIELD_EXISTS("activedirectory_orgs","enabled","artica_backup")){
		$sql="ALTER TABLE `activedirectory_orgs` ADD `enabled` INT(1) NOT NULL DEFAULT '1',ADD INDEX ( `enabled` )";
		$this->QUERY_SQL($sql,"artica_backup");
	}	

	if(!$this->FIELD_EXISTS("activedirectory_orgs","OnlyBranch","artica_backup")){
		$sql="ALTER TABLE `activedirectory_orgs` ADD `OnlyBranch` INT(1) NOT NULL DEFAULT '0',ADD INDEX ( `OnlyBranch` )";
		$this->QUERY_SQL($sql,"artica_backup");
	}		
	
	
	
	if(!$this->TABLE_EXISTS('fetchmail_debug_execute','artica_events')){		
			$sql="CREATE TABLE IF NOT EXISTS `fetchmail_debug_execute` (
			  `ID` int(5) NOT NULL AUTO_INCREMENT,
			  `account_id` int(5) NOT NULL,
			  `zDate` datetime NOT NULL,
			  `events` longtext COLLATE utf8_unicode_ci NOT NULL,
			  `PID` int(5) NOT NULL,
			  PRIMARY KEY (`ID`),
			  KEY `account_id` (`account_id`,`zDate`),
			  KEY `PID` (`PID`)
			)";
			$this->QUERY_SQL($sql,"artica_events");
			if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
	} 
	
	
	if(!$this->TABLE_EXISTS('computers_deploy_tasks','artica_backup')){		
			$sql="CREATE TABLE `artica_backup`.`computers_deploy_tasks` (
			`ID` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`storage_id` INT( 10 ) NOT NULL ,
			`task_type` INT( 1 ) NOT NULL DEFAULT '1',
			`group_id` INT( 5 ) NOT NULL ,
			`progress` INT( 2 ) NOT NULL ,
			`status` INT( 1 ) NOT NULL DEFAULT '0',
			`events` LONGTEXT NOT NULL ,
			`events_computers` LONGTEXT NOT NULL,
			INDEX ( `storage_id` , `task_type` , `group_id` ))";
			$this->QUERY_SQL($sql,"artica_backup");
	} 	
	

	if(!$this->FIELD_EXISTS("computers_deploy_tasks","events_computers","artica_backup")){
		$sql="ALTER TABLE `computers_deploy_tasks` ADD `events_computers` LONGTEXT NOT NULL";
		$this->QUERY_SQL($sql,"artica_backup");
	}

	
	if(!$this->TABLE_EXISTS('computers_deploy_tasks_sub','artica_backup')){		
			$sql="CREATE TABLE `artica_backup`.`computers_deploy_tasks_sub` (
				`ID` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`taskid` INT( 10 ) NOT NULL ,
				`package_id` INT( 10 ) NOT NULL ,
				`status` INT( 1 ) NOT NULL ,
				`progress` INT( 2 ) NOT NULL ,
				`events` LONGTEXT NOT NULL ,
				`uid` VARCHAR( 90 ) NOT NULL ,
				INDEX ( `taskid` , `status` , `progress` , `uid`,`package_id` ))";
			$this->QUERY_SQL($sql,"artica_backup");
	}
	
	if(!$this->TABLE_EXISTS('cgroups_stats','artica_events')){		
			$sql="CREATE TABLE IF NOT EXISTS `cgroups_stats` (
			  `zmd5` varchar(90) NOT NULL,
			  `zDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  `structure` varchar(50) NOT NULL,
			  `key` varchar(50) NOT NULL,
			  `value` int(100) NOT NULL,
			  `groupname` varchar(80) NOT NULL,
			  UNIQUE KEY `zmd5` (`zmd5`),
			  KEY `zDate` (`zDate`,`value`),
			  KEY `structure` (`structure`),
			  KEY `key` (`key`),
			  KEY `groupname` (`groupname`)
			)";
		$this->QUERY_SQL($sql,"artica_events");
	}
			
			
	
	if(!$this->TABLE_EXISTS('roundcube_globaladdressbook','artica_backup')){		
			$sql="CREATE TABLE `artica_backup`.`roundcube_globaladdressbook` (
				`hostname` VARCHAR( 255 ) NOT NULL ,
				`groups` TEXT NOT NULL ,
				`admins` TEXT NOT NULL ,
				`readonly` INT( 1 ) NOT NULL DEFAULT '0',
				`enabled` INT( 1 ) NOT NULL DEFAULT '1',
				UNIQUE (`hostname`))";
			$this->QUERY_SQL($sql,"artica_backup");
			if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		}

	if(!$this->TABLE_EXISTS('samba_usb_share','artica_backup')){	
		$sql="CREATE TABLE `artica_backup`.`samba_usb_share` (`uuid` VARCHAR( 255 ) NOT NULL ,`share_name` VARCHAR( 255 ) NOT NULL ,`settings`
		 TEXT NOT NULL ,
		 UNIQUE (`uuid` ),
		 UNIQUE(`share_name`)
		 ) ";
		$this->QUERY_SQL($sql,"artica_backup");
		if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}	
	}
	
	
	if(!$this->TABLE_EXISTS('samba_remote_announce','artica_backup')){	
		$sql="CREATE TABLE `artica_backup`.`samba_remote_announce`(  
			 `ID` int(11) NOT NULL AUTO_INCREMENT,
			  `ipaddr` varchar(90) NOT NULL,
			  `domain` varchar(90) NOT NULL,
			  `hostname` varchar(50) NOT NULL,
			  PRIMARY KEY (`ID`),
			  KEY `hostname` (`hostname`)
			) ";	
		$this->QUERY_SQL($sql,"artica_backup");
		if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}	
	}		
	
	
	
		$this->CheckSnort();
	}
	function Check_quarantine_table(){
		if(!$this->TABLE_EXISTS('quarantine','artica_backup')){		
			$sql="CREATE TABLE IF NOT EXISTS `quarantine` (
					  `MessageID` varchar(80) NOT NULL,
					  `zDate` datetime NOT NULL,
					  `mailfrom` varchar(255) NOT NULL,
					  `mailfrom_domain` varchar(255) NOT NULL,
					  `mailto` varchar(255) NOT NULL,
					  `subject` varchar(255) NOT NULL,
					  `MessageBody` text NOT NULL,
					  `fullmesg` text NOT NULL,
					  `header` text NOT NULL,
					  `organization` varchar(80) NOT NULL,
					  PRIMARY KEY  (`MessageID`),
					  KEY `mailfrom` (`mailfrom`),
					  KEY `mailfrom_domain` (`mailfrom_domain`),
					  KEY `mailto` (`mailto`),
					  FULLTEXT KEY `MessageBody` (`MessageBody`),  FULLTEXT KEY `header` (`header`)
					  );";
				
				$this->QUERY_SQL($sql,'artica_backup');
				}
	
		if(!$this->TABLE_EXISTS('smtp_attachments_blocking','artica_backup')){			
			$sql="CREATE TABLE IF NOT EXISTS `smtp_attachments_blocking` (
			  `ID` int(11) NOT NULL AUTO_INCREMENT,
			  `ou` varchar(255) NOT NULL,
			  `IncludeByMime` varchar(90) NOT NULL,
			  `ExcludeByMime` varchar(90) NOT NULL,
			  `IncludeByName` varchar(90) NOT NULL,
			  `ExcludeByName` varchar(90) NOT NULL,
			  `hostname` varchar(255) NOT NULL,
			  PRIMARY KEY (`ID`),
			  	KEY `ou` (`ou`),
			  	KEY `IncludeByMime` (`IncludeByMime`),
			  	KEY `ExcludeByMime` (`ExcludeByMime`),
			  	KEY `IncludeByName` (`IncludeByName`),
			 	KEY `hostname` (`hostname`)
			 	)	";
			$this->QUERY_SQL($sql,'artica_backup');
			}
			
			if(!$this->FIELD_EXISTS("smtp_attachments_blocking","hostname","artica_backup")){
				$sql="ALTER TABLE `smtp_attachments_blocking` ADD `hostname` VARCHAR( 255 ) NOT NULL ,ADD INDEX ( `hostname` )";
				$this->QUERY_SQL($sql,"artica_backup");
			}			
	
	if(!$this->TABLE_EXISTS('kavmilter','artica_backup')){	
	$sql="CREATE TABLE `artica_backup`.`kavmilter` (
			`ou` VARCHAR( 125 ) NOT NULL ,
			`config` TEXT NOT NULL ,
			`configlms` TEXT NOT NULL ,
			PRIMARY KEY ( `ou` ))";
	 $this->QUERY_SQL($sql,'artica_backup');
	}
	
	if(!$this->FIELD_EXISTS("kavmilter","configlms","artica_backup")){
		$sql="ALTER TABLE `kavmilter` ADD `configlms` TEXT NOT NULL ";
		$this->QUERY_SQL($sql,"artica_backup");
	}
	
	
if(!$this->TABLE_EXISTS('networks_infos','artica_backup')){	
			$sql="CREATE TABLE `artica_backup`.`networks_infos` (
			`ipaddr` VARCHAR( 128 ) NOT NULL ,
			`netinfos` VARCHAR( 255 ) NOT NULL ,
			PRIMARY KEY ( `ipaddr` )
			)";
			$this->QUERY_SQL($sql,"artica_backup");
		} 	
	
	
		if(!$this->TABLE_EXISTS('kas3','artica_backup')){	
			$sql="CREATE TABLE IF NOT EXISTS `kas3` (
				  `ID` int(10) NOT NULL AUTO_INCREMENT,
				  `ou` varchar(255) NOT NULL,
				  `key` varchar(255) NOT NULL DEFAULT '',
				  `value` varchar(255) NOT NULL DEFAULT '',
				  	PRIMARY KEY (`ID`),
				  	KEY `ou` (`ou`),
				  	KEY `key` (`key`)
					)";
			$this->QUERY_SQL($sql,"artica_backup");
		}
		
		if(!$this->TABLE_EXISTS('spamassassin_spf_wl','artica_backup')){	
			$sql="CREATE TABLE `artica_backup`.`spamassassin_spf_wl` (
				`ID` INT( 20 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`domain` VARCHAR( 255 ) NOT NULL ,
				UNIQUE (`domain`))";
			$this->QUERY_SQL($sql,"artica_backup");
		}
		if(!$this->TABLE_EXISTS('spamassassin_keywords','artica_backup')){	
			$sql="CREATE TABLE `artica_backup`.`spamassassin_keywords` (
			`ID` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`pattern` VARCHAR( 255 ) NOT NULL ,
			`header` VARCHAR( 40 ) NOT NULL ,
			`score` VARCHAR( 4 ) NOT NULL ,
			`enabled` INT( 1 ) NOT NULL DEFAULT '1',
			INDEX ( `header` ),
			KEY  `pattern` (`pattern`),
			KEY  `enabled` (`enabled`)
			)";
			$this->QUERY_SQL($sql,"artica_backup");
			if(!$this->ok){writelogs($this->mysql_error,__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);	}
		}
		
		
		if(!$this->TABLE_EXISTS('spamassassin_dkim_wl','artica_backup')){	
			$sql="CREATE TABLE `artica_backup`.`spamassassin_dkim_wl` (
				`ID` INT( 20 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`domain` VARCHAR( 255 ) NOT NULL ,
				UNIQUE (`domain`))";
			$this->QUERY_SQL($sql,"artica_backup");
		}	
		
		if(!$this->TABLE_EXISTS('spamassassin_table','artica_backup')){	
			$sql="CREATE TABLE `artica_backup`.`spamassassin_table` (
				`ID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`value` VARCHAR( 255 ) NOT NULL ,
				`spam_type` VARCHAR( 20 ) NOT NULL ,
				`enabled` INT( 1 ) NOT NULL DEFAULT '1',
					KEY  `value` (`value`),
					KEY `spam_type` (`spam_type`), 
					KEY `enabled` (`enabled`)
				)";
				$this->QUERY_SQL($sql,"artica_backup");
				if(!$this->ok){writelogs($this->mysql_error,__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);	}
		}

		
	if(!$this->FIELD_EXISTS("quarantine","file_path","artica_backup")){
		$sql="ALTER TABLE `quarantine` ADD `file_path` varchar(255) NOT NULL ,ADD INDEX ( `file_path` )";
		$this->QUERY_SQL($sql,"artica_backup");
	}
	
	if(!$this->FIELD_EXISTS("quarantine","original_messageid","artica_backup")){
		$sql="ALTER TABLE `quarantine` ADD `original_messageid` varchar(200) NOT NULL ,ADD INDEX ( `original_messageid` )";
		$this->QUERY_SQL($sql,"artica_backup");
	}	
	
	if(!$this->FIELD_EXISTS("quarantine","message_size","artica_backup")){
		$sql="ALTER TABLE `quarantine` ADD `message_size` INT(10) NOT NULL";
		$this->QUERY_SQL($sql,"artica_backup");
	}

	if(!$this->FIELD_EXISTS("quarantine","mailto","artica_backup")){
		$sql="ALTER TABLE `quarantine` ADD `mailto` varchar(255) NOT NULL ,ADD INDEX ( `mailto` )";
		$this->QUERY_SQL($sql,"artica_backup");
	}		
	
	if(!$this->FIELD_EXISTS("quarantine","BinMessg","artica_backup")){
		$sql="ALTER TABLE `quarantine` ADD `BinMessg` LONGBLOB NOT NULL";
		$this->QUERY_SQL($sql,"artica_backup");
	}

	if(!$this->FIELD_EXISTS("quarantine","filesize","artica_backup")){
		$sql="ALTER TABLE `quarantine` ADD `filesize` INT( 10 ) NOT NULL 	";
		$this->QUERY_SQL($sql,"artica_backup");
	}	

	if(!$this->FIELD_EXISTS("quarantine","filename","artica_backup")){
		$sql="ALTER TABLE `quarantine` ADD `filename` VARCHAR( 128 ) NOT NULL ,ADD INDEX ( filename )";
		$this->QUERY_SQL($sql,"artica_backup");
	}	

	if(!$this->TABLE_EXISTS('postfix_sender_quotas','artica_backup')){	
		$sql="CREATE TABLE `artica_backup`.`postfix_sender_quotas` (
		`uid` VARCHAR( 128 ) NOT NULL ,
		`MaxMailsDay` INT( 5 ) NOT NULL ,
		`MaxMailDaySize` INT( 5 ) NOT NULL ,
		`OnlyNotify` INT( 1 ) NOT NULL DEFAULT '0',
		PRIMARY KEY ( `uid` ) ,
		KEY `MaxMailsDay` ( `MaxMailsDay`),
		KEY `MaxMailDaySize` (`MaxMailDaySize`) ,
		KEY `OnlyNotify` (`OnlyNotify`))";
		$this->QUERY_SQL($sql,"artica_backup");
		if(!$this->ok){writelogs($this->mysql_error,__FUNCTION__,__FILE__,__LINE__);}
	}
	
	
	if(!$this->TABLE_EXISTS('smtpd_data_restrictions','artica_backup')){	
		$sql="CREATE TABLE `artica_backup`.`smtpd_data_restrictions` (
			`ID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`restriction` VARCHAR( 50 ) NOT NULL ,
			`hostname` VARCHAR( 128 ) NOT NULL ,
			`restrictions_datas` LONGTEXT NOT NULL ,
			`enabled` INT( 1 ) NOT NULL DEFAULT '1',
			`zorder` INT( 2 ) NOT NULL ,
			KEY `hostname`(`hostname` ),
			INDEX ( `restriction` , `enabled` , `zorder` )
			) ";
		$this->QUERY_SQL($sql,"artica_backup");
		if(!$this->ok){writelogs($this->mysql_error,__FUNCTION__,__FILE__,__LINE__);}
	}	

	
	if(!$this->TABLE_EXISTS('postfix_perso_settings','artica_backup')){	
		$sql="CREATE TABLE `artica_backup`.`postfix_perso_settings` (
			`postfix_key` VARCHAR( 90 ) NOT NULL ,
			`postfix_value` VARCHAR( 255 ) NOT NULL ,
			`postfix_type` VARCHAR( 10 ) NOT NULL ,
			PRIMARY KEY ( `postfix_key` )
			)";
		$this->QUERY_SQL($sql,"artica_backup");
		if(!$this->ok){writelogs($this->mysql_error,__FUNCTION__,__FILE__,__LINE__);}
	} 	

	
		
	}

function Check_iptables_table(){
		if(!$this->TABLE_EXISTS('iptables','artica_backup')){	
			$sql="
			CREATE TABLE IF NOT EXISTS `iptables` (
			  `ID` int(20) NOT NULL auto_increment,
			  `servername` varchar(80) default NULL,
			  `serverip` varchar(30) NOT NULL,
			  `service` varchar(20) NOT NULL,
			  `local_port` int(10) NOT NULL,
			  `disable` int(1) NOT NULL,
			  `events_number` int(10) NOT NULL,
			  `iptables_id` int(5) NOT NULL,
			  `rule_string` varchar(255) NOT NULL,
			  `saved_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
			  `rulemd5` varchar(255) NOT NULL,
			  `flux` varchar(10) NOT NULL,
			  `log` int(1) NOT NULL default '0',
			  `sended` INT( 1 ) NOT NULL ,
			  `community` INT( 1 ),
			  PRIMARY KEY  (`ID`),
			  UNIQUE KEY `rulemd5` (`rulemd5`),
			  KEY `servername` (`servername`,`serverip`,`service`,`local_port`,`disable`,`events_number`),
			  KEY `iptables_id` (`iptables_id`),
			  KEY `rule_string` (`rule_string`),
			  KEY `saved_date` (`saved_date`),
			  KEY `flux` (`flux`,`log`),
			  KEY `sended` (`sended`),
			  KEY `community` (`community`)) 
			";
		
			$this->QUERY_SQL($sql,'artica_backup');
		}
		
		if(!$this->FIELD_EXISTS("iptables","rulemd5","artica_backup")){	
			$sql="ALTER TABLE `iptables` ADD `rulemd5` VARCHAR( 255 ) NOT NULL ,ADD UNIQUE (`rulemd5`)";
			$this->QUERY_SQL($sql,'artica_backup');
		}

		if(!$this->FIELD_EXISTS("iptables","flux","artica_backup")){	
			$sql="ALTER TABLE `iptables` ADD `flux` VARCHAR( 10 ) NOT NULL ,ADD INDEX (`flux`)";
			$this->QUERY_SQL($sql,'artica_backup');
		}		

		if(!$this->FIELD_EXISTS("iptables","log","artica_backup")){	
			$sql="ALTER TABLE `iptables` ADD `log` INT( 1 ) NOT NULL ,ADD INDEX (`log`)";
			$this->QUERY_SQL($sql,'artica_backup');
		}	

		if(!$this->FIELD_EXISTS("iptables","events_block","artica_backup")){	
			$sql="ALTER TABLE `iptables` ADD `events_block` MEDIUMTEXT NOT NULL";
			$this->QUERY_SQL($sql,'artica_backup');
			$sql="ALTER TABLE `iptables` ADD `date_created` DATETIME NOT NULL ,ADD INDEX ( `date_created` )";
			$this->QUERY_SQL($sql,'artica_backup');			
		}
		
		if(!$this->FIELD_EXISTS("iptables","sended","artica_backup")){	
			$sql="ALTER TABLE `iptables` ADD `sended` INT( 1 ) NOT NULL ,ADD `community` INT( 1 ) NOT NULL ,ADD INDEX ( `sended` , `community` )";
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}	
		}	
		
		if(!$this->FIELD_EXISTS("iptables","multiples_ports","artica_backup")){	
			$sql="ALTER TABLE `iptables` ADD `multiples_ports` VARCHAR( 255 ) NOT NULL ";
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}	
		}			
		
		
		
	
		if(!$this->TABLE_EXISTS('iptables_bridge','artica_backup')){	
			$sql="CREATE TABLE IF NOT EXISTS `iptables_bridge` (
				  `ID` int(5) NOT NULL AUTO_INCREMENT,
				  `nics_virtuals_id` int(2) NOT NULL,
				  `nic_linked` varchar(20) NOT NULL,
				  `zmd5` varchar(90) NOT NULL,
				  PRIMARY KEY (`ID`),
				  UNIQUE KEY `ID` (`ID`),
				  UNIQUE KEY `zmd5` (`zmd5`),
				  KEY `nics_virtuals_id` (`nics_virtuals_id`,`nic_linked`))";
				$this->QUERY_SQL($sql,'artica_backup');
		}
		
		
		if(!$this->TABLE_EXISTS('automount_davfs','artica_backup')){	
			$sql="CREATE TABLE IF NOT EXISTS `automount_davfs` (
				  `local_dir` varchar(255) NOT NULL,
				  `user` varchar(255) NOT NULL,
				  `password` varchar(255) NOT NULL,
				  `uri` varchar(255) NOT NULL,
				  UNIQUE KEY `local_dir` (`local_dir`),
				  KEY `uri` (`uri`)
				)";
								
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		}
		
		if(!$this->TABLE_EXISTS('tcp_account_rules','artica_backup')){	
					$sql="CREATE TABLE IF NOT EXISTS `tcp_account_rules` (
						  `ID` int(10) NOT NULL AUTO_INCREMENT,
						  `rulename` varchar(128) NOT NULL,
						  `mode` varchar(40) NOT NULL,
						  `ipaddr` varchar(80) NOT NULL,
						  PRIMARY KEY (`ID`),
						  KEY `mode` (`mode`),
						  KEY `rulename` (`rulename`)
							)";
					$this->QUERY_SQL($sql,'artica_backup');
					if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		}	


		if(!$this->TABLE_EXISTS('tcp_account_events','artica_events')){	
					$sql="CREATE TABLE `artica_events`.`tcp_account_events` (
					`ID` INT( 100 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
					`rule_id` INT( 10 ) NOT NULL ,
					`zDate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
					`ipaddr` VARCHAR( 50 ) NOT NULL ,
					`src_parckets` BIGINT( 100 ) NOT NULL ,
					`src_bytes` BIGINT( 100 ) NOT NULL ,
					`dst_packets` BIGINT( 100 ) NOT NULL ,
					`dst_bytes` BIGINT( 100 ) NOT NULL ,
					INDEX ( `rule_id` , `zDate` , `ipaddr` , `dst_bytes` )
					)";
					$this->QUERY_SQL($sql,'artica_events');
					if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		}	

		
		
		if(!$this->TABLE_EXISTS('iscsi_client','artica_backup')){	
		
			  $sql="CREATE TABLE IF NOT EXISTS `iscsi_client` (
			  `ID` int(2) NOT NULL AUTO_INCREMENT,
			  `username` varchar(255) NOT NULL,
			  `password` varchar(255) NOT NULL,
			  `Params` blob NOT NULL,
			  `hostname` varchar(255) NOT NULL,
			  `directory` varchar(255) NOT NULL,
			  `Persistante` INT( 1 ) NOT NULL DEFAULT '0',
			  PRIMARY KEY (`ID`),
			  KEY `directory` (`directory`),
			  KEY `Persistante` (`Persistante`)
			)";
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		}	

		
		if(!$this->TABLE_EXISTS('iscsi_params','artica_backup')){	
		
			  $sql="CREATE TABLE IF NOT EXISTS `iscsi_params` (
			  `ID` int(11) NOT NULL AUTO_INCREMENT,
			  `shared_folder` varchar(90) NOT NULL,
			  `hostname` varchar(255) NOT NULL,
			  `dev` varchar(255) NOT NULL,
			  `type` varchar(10) NOT NULL,
			  `file_size` int(2) NOT NULL,
			  `uid` varchar(90) NOT NULL,
			  `EnableAuth` int(1) NOT NULL,
			  `Params` blob NOT NULL,
			  PRIMARY KEY (`ID`),
			  KEY `type` (`type`),
			  KEY `shared_folder` (`shared_folder`),
			  KEY `dev` (`dev`),
			  KEY `uid` (`uid`)
			)";
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		}

		if(!$this->TABLE_EXISTS('acl_directories','artica_backup')){	
			  $sql="CREATE TABLE IF NOT EXISTS `acl_directories` (
				  `md5` varchar(90) NOT NULL,
				  `directory` varchar(255) NOT NULL,
				  `chmod` LONGTEXT NOT NULL,
				  `enabled` int(1) NOT NULL,
				  `acls` blob NOT NULL,
				  `events` blob NOT NULL,
				  `update` int(1) NOT NULL DEFAULT '1',
				  PRIMARY KEY (`md5`),
				  KEY `enabled` (`enabled`),
				  KEY `update` (`update`)
				)";	
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		}			  	
	
				
		
		
		if(!$this->TABLE_EXISTS('nic_routes','artica_backup')){	
			$sql="CREATE TABLE IF NOT EXISTS `nic_routes` (
				  `zmd5` varchar(90) NOT NULL,
				  `type` int(1) NOT NULL,
				  `gateway` varchar(90) NOT NULL,
				  `pattern` varchar(255) NOT NULL,
				  `status` int(1) NOT NULL,
				  `nic` varchar(20) NOT NULL,
				  PRIMARY KEY (`zmd5`),
				  KEY `type` (`type`,`status`),
				  KEY `nic` (`nic`)
				) ";
					$this->QUERY_SQL($sql,'artica_backup');
			}
			
		
		if(!$this->TABLE_EXISTS('infected_count','artica_events')){		
			$sql="CREATE TABLE IF NOT EXISTS
				`artica_events`.`infected_count` (`zMD5` VARCHAR( 80 ) NOT NULL ,
				`zDate` DATETIME NOT NULL ,
				`daemon` VARCHAR( 20 ) NOT NULL ,
				`event_text` VARCHAR( 255 ) NOT NULL ,
				`virusname` VARCHAR( 80 ) NOT NULL ,
				PRIMARY KEY ( `zMD5` ) , INDEX ( `zDate` , `daemon` , `virusname` ));";
				$this->QUERY_SQL($sql,"artica_events");	
		}

		
		if(!$this->TABLE_EXISTS('loadavg','artica_events')){		
			$sql="CREATE TABLE IF NOT EXISTS `loadavg` (`stime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,`load` double(100,2) NOT NULL,PRIMARY KEY (`stime`)) ";
			$this->QUERY_SQL($sql,"artica_events");	
			if(!$q->ok){writelogs("WARNING !!!! $q->mysql_error : $sql",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
			include_once(dirname(__FILE__)."/class.sockets.inc");
			$sock=new sockets();
			if(!is_numeric($sock->GET_INFO("MysqlChangeloadavgDOUBLE"))){
				$sql="ALTER TABLE `loadavg` CHANGE `load` `load` DOUBLE( 100, 2 ) NOT NULL";
				$this->QUERY_SQL($sql,"artica_events");	
				$sock->SET_INFO("MysqlChangeloadavgDOUBLE", 1);
			}
		}
		
		
		if(!$this->TABLE_EXISTS('dhcpd_sharednets','artica_backup')){		
			$sql="CREATE TABLE IF NOT EXISTS `dhcpd_sharednets` (
				  `ID` int(11) NOT NULL AUTO_INCREMENT,
				  `sharednet_name` varchar(50) NOT NULL,
				  `scopename` varchar(128) NOT NULL,
				  `subnet` varchar(50) NOT NULL,
				  `netmask` varchar(50) NOT NULL,
				  `range1` varchar(50) NOT NULL,
				  `range2` varchar(50) NOT NULL,
				  `domain-name-servers1` varchar(50) NOT NULL,
				  `domain-name-servers2` varchar(50) NOT NULL,
				  `domain-name` varchar(150) NOT NULL,
				  `routers` varchar(255) NOT NULL,
				  `subnet-mask` varchar(50) NOT NULL,
				  `tftp-server-name` varchar(128) NOT NULL,
				  `server-name` varchar(128) NOT NULL,
				  `next-server` varchar(128) NOT NULL,
				  `pxe_filename` varchar(255) NOT NULL,
				  `pxe_root-path` varchar(255) NOT NULL,
				  `option-176` text NOT NULL,
				  PRIMARY KEY (`ID`),
				  KEY `sharednet_name` (`sharednet_name`,`scopename`,`subnet`),
				  KEY `subnet` (`subnet`),
				  KEY `range1` (`range1`),
				  KEY `range2` (`range2`)
				)";
			$this->QUERY_SQL($sql,"artica_backup");	
		}
		
		if(!$this->FIELD_EXISTS("dhcpd_sharednets","wins-server","artica_backup")){	
			$sql="ALTER TABLE `dhcpd_sharednets` ADD `wins-server` VARCHAR( 50 ) NOT NULL";
			$this->QUERY_SQL($sql,'artica_backup');
		}			

		
		if(!$this->TABLE_EXISTS('dhcpd_leases','artica_backup')){		
			$sql="CREATE TABLE IF NOT EXISTS `dhcpd_leases` (
				  `mac` varchar(90) NOT NULL,
				  `hostname` varchar(128) NOT NULL,
				  `starts` datetime NOT NULL,
				  `ends` datetime NOT NULL,
				  `cltt` datetime NOT NULL,
				  `tstp` datetime NOT NULL,
				  `atsfp` datetime NOT NULL,
				  `lastseen` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				  `ipaddr` varchar(50) NOT NULL,
				  PRIMARY KEY (`mac`),
				  KEY `hostname` (`hostname`,`starts`,`ends`,`cltt`,`tstp`,`atsfp`,`lastseen`),
				  KEY `ipaddr` (`ipaddr`)
					) ";		
			$this->QUERY_SQL($sql,"artica_backup");	
		}
		

					
		
		if(!$this->TABLE_EXISTS('syslogs','artica_events')){	
			$sql="CREATE TABLE IF NOT EXISTS `syslogs` (
			  `host` varchar(32) default NULL,
			  `facility` varchar(10) default NULL,
			  `priority` varchar(10) default NULL,
			  `level` varchar(10) default NULL,
			  `tag` varchar(10) default NULL,
			  `date` datetime default NULL,
			  `program` varchar(15) default NULL,
			  `msg` text,
			  `seq` int(10) unsigned NOT NULL auto_increment,
			  PRIMARY KEY  (`seq`),
			  KEY `host` (`host`),
			  KEY `seq` (`seq`),
			  KEY `program` (`program`),
			  KEY `date` (`date`),
			  KEY `priority` (`priority`),
			  KEY `facility` (`facility`)
			) ENGINE=MyISAM ";
			$this->QUERY_SQL($sql,"artica_events");	
		}	

		
	}
	
function checkTablesSamba(){
		if(!$this->TABLE_EXISTS('samba_hosts','artica_backup')){		
		$sql="CREATE TABLE IF NOT EXISTS `samba_hosts` (
			  `hostname` varchar(60) NOT NULL,
			  `ou` varchar(128) NOT NULL,
			  `RootDir` varchar(255) NOT NULL,
			  `workgroup` varchar(90) NOT NULL,
			  `global_settings` LONGTEXT NOT NULL,
			  PRIMARY KEY (`hostname`),
			  KEY `ou` (`ou`)
			) ";

		$this->QUERY_SQL($sql,'artica_backup');
		if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		} 
		
		if(!$this->FIELD_EXISTS("samba_hosts","ipaddr","artica_backup")){	
			$sql="ALTER TABLE `samba_hosts` ADD `ipaddr` VARCHAR( 90 ) NOT NULL ,ADD INDEX ( `ipaddr` )";
			$this->QUERY_SQL($sql,'artica_backup');
		}	
		
		if(!$this->FIELD_EXISTS("samba_hosts","global_settings","artica_backup")){	
			$sql="ALTER TABLE `samba_hosts` ADD `global_settings` LONGTEXT NOT NULL ";
			$this->QUERY_SQL($sql,'artica_backup');
		}			

		
if(!$this->TABLE_EXISTS('samba_hosts','artica_backup')){		
		$sql="CREATE TABLE IF NOT EXISTS `samba_hosts_share` (
			  `ID` int(10) NOT NULL AUTO_INCREMENT,
			  `share_name` varchar(40) NOT NULL,
			  `share_path` varchar(255) NOT NULL,
			  `share_params` longtext NOT NULL,
			  `enabled` int(1) NOT NULL DEFAULT '1',
			  `hostname` varchar(90) NOT NULL DEFAULT '',
			  PRIMARY KEY (`ID`),
			  KEY `share_name` (`share_name`,`hostname`),
			  KEY `share_path` (`share_path`))";
		$this->QUERY_SQL($sql,'artica_backup');
		if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		} 		

		 
		
	
}

function check_postfwd2_table(){
	if(!$this->TABLE_EXISTS('postfwd2','artica_backup')){		
		$sql="CREATE TABLE IF NOT EXISTS `postfwd2` (
			  `ID` int(11) NOT NULL AUTO_INCREMENT,
			  `instance` varchar(90) NOT NULL,
			  `enabled` int(1) NOT NULL DEFAULT '1',
			  `action` varchar(50) NOT NULL,
			  `rule` blob NOT NULL,
			  `rank` int(1) NOT NULL DEFAULT '0',
			  PRIMARY KEY (`ID`),
			  KEY `rank` (`rank`))";
		$this->QUERY_SQL($sql,'artica_backup');
		if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
	} 		
}	
function Check_smtp_logs_table(){
	if(!$this->TABLE_EXISTS('greylist_turples','artica_events')){				
			$sql="CREATE TABLE IF NOT EXISTS `greylist_turples` (
				  `zmd5` varchar(90) NOT NULL,
				  `mailfrom` varchar(255) NOT NULL,
				  `mailto` varchar(255) NOT NULL,
				  `stime` int(50) NOT NULL,
				  `hostname` varchar(255) NOT NULL,
				  `ip_addr` varchar(50) NOT NULL,
				  PRIMARY KEY (`zmd5`),
				  KEY `stime` (`stime`),
				  KEY `mailfrom` (`mailfrom`),
				  KEY `mailto` (`mailto`),
				  KEY `hostname` (`hostname`),
				  KEY `ip_addr` (`ip_addr`)
				)";
			$this->QUERY_SQL($sql,'artica_events');
			if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		} 		
	
		
		
		
		if(!$this->FIELD_EXISTS("smtp_logs","time_amavis","artica_events")){	
			$sql="ALTER TABLE `smtp_logs` ADD `time_amavis` DATETIME NULL ,ADD INDEX ( `time_amavis` )";
			$this->QUERY_SQL($sql,'artica_events');
		}
		
		if(!$this->FIELD_EXISTS("smtp_logs","Country","artica_events")){	
			$sql="ALTER TABLE `smtp_logs` ADD `Country` VARCHAR( 25 ) NULL ,ADD INDEX ( `Country` )";
			$this->QUERY_SQL($sql,'artica_events');
		}	

		if(!$this->FIELD_EXISTS("smtp_logs","Region","artica_events")){	
			$sql="ALTER TABLE `smtp_logs` ADD `Region` VARCHAR( 25 ) NULL ,ADD INDEX ( `Region` )";
			$this->QUERY_SQL($sql,'artica_events');
		}

		if(!$this->FIELD_EXISTS("smtp_logs","kas","artica_events")){	
			$sql="ALTER TABLE `smtp_logs` ADD `kas` INT( 3 ) NOT NULL DEFAULT '0',ADD INDEX ( `kas` ) ";
			$this->QUERY_SQL($sql,'artica_events');
		}	

		if(!$this->FIELD_EXISTS("smtp_logs","infected","artica_events")){	
			$sql="ALTER TABLE `smtp_logs` ADD `infected` INT( 1 ) NOT NULL DEFAULT '0',ADD INDEX ( `infected` )";
			$this->QUERY_SQL($sql,'artica_events');
		}

		if(!$this->FIELD_EXISTS("smtp_logs","spammy","artica_events")){	
			$sql="ALTER TABLE `smtp_logs` ADD `spammy` INT( 1 ) NOT NULL DEFAULT '0',ADD INDEX ( `spammy` )";
			$this->QUERY_SQL($sql,'artica_events');
		}	

		if(!$this->FIELD_EXISTS("smtp_logs","blacklisted","artica_events")){	
			$sql="ALTER TABLE `smtp_logs` ADD `blacklisted` INT( 1 ) NOT NULL DEFAULT '0',ADD INDEX ( `blacklisted` )";
			$this->QUERY_SQL($sql,'artica_events');
		}

		if(!$this->FIELD_EXISTS("smtp_logs","whitelisted","artica_events")){	
			$sql="ALTER TABLE `smtp_logs` ADD `whitelisted` INT( 1 ) NOT NULL DEFAULT '0',ADD INDEX ( `whitelisted` )";
			$this->QUERY_SQL($sql,'artica_events');
		}	
		
		if(!$this->FIELD_EXISTS("smtp_logs","smtp_sender","artica_events")){	
			$sql="ALTER TABLE `smtp_logs` ADD `smtp_sender` VARCHAR( 55 ) NULL ,ADD INDEX ( `smtp_sender` ) ";
			$this->QUERY_SQL($sql,'artica_events');
		}

		if(!$this->FIELD_EXISTS("smtp_logs","metasended","artica_events")){	
			$sql="ALTER TABLE `smtp_logs` ADD `metasended` SMALLINT( 1 ) NOT NULL DEFAULT '0',ADD INDEX ( `metasended` )";
			$this->QUERY_SQL($sql,'artica_events');
		}		
		
	if(!$this->TABLE_EXISTS('mail_con','artica_events')){		
		$sql="CREATE TABLE `artica_events`.`mail_con` (
		`zmd5` VARCHAR( 90 ) NOT NULL ,
		`zDate` TIMESTAMP NOT NULL ,
		`hostname` VARCHAR( 255 ) NOT NULL ,
		`ipaddr` VARCHAR( 40 ) NOT NULL ,
		PRIMARY KEY ( `zmd5` ) ,
		INDEX ( `zDate` , `hostname` , `ipaddr` )
		)";		
		$this->QUERY_SQL($sql,'artica_events');
		if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
	}
	
	if(!$this->TABLE_EXISTS('ip_rotator_smtp','artica_backup')){		
		$sql="CREATE TABLE IF NOT EXISTS `ip_rotator_smtp` (
			  `ID` int(11) NOT NULL AUTO_INCREMENT,
			  `ipsource` varchar(60) NOT NULL,
			  `ipdest` varchar(60) NOT NULL,
			  `mode` varchar(10) NOT NULL,
			  `mode_value` varchar(5) NOT NULL,
			  `hostname` varchar(255) NOT NULL,
			  `ou` varchar(255) NOT NULL,
			  PRIMARY KEY (`ID`),
			  KEY `ipsource` (`ipsource`,`mode`),
			  KEY `hostname` (`hostname`),
			  KEY `ou` (`ou`)
			) ";		
		$this->QUERY_SQL($sql,'artica_backup');
		if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
	}
	
	
if(!$this->TABLE_EXISTS('whitelist_uid_greylist','artica_backup')){		
		$sql="CREATE TABLE `artica_backup`.`whitelist_uid_greylist` (`uid` VARCHAR( 128 ) NOT NULL ,UNIQUE (`uid`))";
		$this->QUERY_SQL($sql,'artica_backup');
		if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}	
}		


	if(!$this->TABLE_EXISTS('master_cf','artica_backup')){		
		$sql="CREATE TABLE IF NOT EXISTS `master_cf` (
			  `SERVICE_NAME` varchar(50) NOT NULL,
			  `TYPE` varchar(20) NOT NULL,
			  `hostname` varchar(255) NOT NULL,
			  `PRIVATE` varchar(50) NOT NULL,
			  `UNIPRIV` varchar(50) NOT NULL,
			  `CHROOT` varchar(3) NOT NULL,
			  `WAKEUP` varchar(50) NOT NULL,
			  `MAXPROC` varchar(50) NOT NULL,
			  `COMMAND` text NOT NULL,
			  `zmd5` varchar(90) NOT NULL,
			  PRIMARY KEY (`zmd5`),
			  KEY `SERVICE_NAME` (`SERVICE_NAME`,`PRIVATE`,`UNIPRIV`,`CHROOT`,`WAKEUP`,`MAXPROC`,`TYPE`),
			  KEY `hostname` (`hostname`)
			) ";
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}	
	}	

	
	if(!$this->TABLE_EXISTS('nics_vlan','artica_backup')){		
		$sql="CREATE TABLE IF NOT EXISTS `nics_vlan` (
			  `nic` varchar(20) NOT NULL,
			  `ID` int(11) NOT NULL AUTO_INCREMENT,
			  `org` varchar(255) NOT NULL,
			  `ipaddr` varchar(25) NOT NULL,
			  `netmask` varchar(25) NOT NULL,
			  `cdir` varchar(30) NOT NULL,
			  `gateway` varchar(30) NOT NULL,
			  `broadcast` varchar(30) NOT NULL,
			  `network` varchar(30) NOT NULL,
			  PRIMARY KEY (`ID`),
			  KEY `nic` (`nic`),
			  KEY `org` (`org`,`ipaddr`,`netmask`),
			  KEY `cdir` (`cdir`)
			)";
	$this->QUERY_SQL($sql,'artica_backup');
		if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
	}
		
	
	
	if(!$this->TABLE_EXISTS('amavisd_tests','artica_backup')){		
		$sql="CREATE TABLE IF NOT EXISTS `amavisd_tests` (
			  `ID` int(5) NOT NULL AUTO_INCREMENT,
			  `sender` varchar(255) NOT NULL,
			  `recipients` text NOT NULL,
			  `message` longtext NOT NULL,
			  `amavisd_results` longtext NOT NULL,
			  `spamassassin_results` text NOT NULL,
			  `spamassassin_results_header` text NOT NULL,
			  `finish` int(1) NOT NULL DEFAULT '0',
			  `saved_date` datetime NOT NULL,
			  `subject` VARCHAR( 255 ) NOT NULL,
			  PRIMARY KEY (`ID`),
			  KEY `finish` (`finish`),
			  KEY `saved_date` (`saved_date`))";
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
	}
	
	
	if(!$this->TABLE_EXISTS('amavisd_bypass','artica_backup')){		
		$sql="CREATE TABLE `artica_backup`.`amavisd_bypass` (
		`ip_addr` VARCHAR( 255 ) NOT NULL ,
		PRIMARY KEY ( `ip_addr` )
		) ";	
	$this->QUERY_SQL($sql,'artica_backup');
	if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
	}
	
	
if(!$this->TABLE_EXISTS('smtp_logs_day_users','artica_events')){		
		$sql="CREATE TABLE IF NOT EXISTS `smtp_logs_day_users` (
			  `zmd5` varchar(90) NOT NULL,
			  `hits` int(10) NOT NULL,
			  `size` int(10) NOT NULL,
			  `sender_user` varchar(255) NOT NULL,
			  `sender_domain` varchar(255) NOT NULL,
			  `recipient_user` varchar(255) NOT NULL,
			  `recipient_domain` varchar(255) NOT NULL,
			  `ipaddr` varchar(40) NOT NULL,
			  `zDay` date NOT NULL,
			  PRIMARY KEY (`zmd5`),
			  KEY `hits` (`hits`,`size`),
			  KEY `sender_user` (`sender_user`),
			  KEY `sender_domain` (`sender_domain`),
			  KEY `recipient_user` (`recipient_user`),
			  KEY `recipient_domain` (`recipient_domain`),
			  KEY `ipaddr` (`ipaddr`),
			  KEY `zDay` (`zDay`))";	
		$this->QUERY_SQL($sql,'artica_events');
		if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
	}	

	
	if(!$this->TABLE_EXISTS('greyhole_spools','artica_backup')){		
			$sql="CREATE TABLE `artica_backup`.`greyhole_spools` (
					`dn` VARCHAR( 255 ) NOT NULL ,
					`free_g` INT( 3 ) NOT NULL ,
					PRIMARY KEY ( `dn` ))";
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
	}		
	
	
	
	if(!$this->TABLE_EXISTS('greyhole_dirs','artica_backup')){		
			$sql="CREATE TABLE `artica_backup`.`greyhole_dirs` (
				`shared_dir` VARCHAR( 255 ) NOT NULL ,
				`num_copies` INT( 2 ) NOT NULL ,
				PRIMARY KEY ( `shared_dir` ) ,
				INDEX ( `num_copies` ))";
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
	}		

	
	if(!$this->TABLE_EXISTS('mail_con_err','artica_events')){	
		
		$sql="CREATE TABLE `artica_events`.`mail_con_err` (
			`zmd5` VARCHAR( 90 ) NOT NULL ,
			`zDate` TIMESTAMP NOT NULL ,
			`hostname` VARCHAR( 255 ) NOT NULL ,
			`ipaddr` VARCHAR( 40 ) NOT NULL ,
			`smtp_err` VARCHAR( 255 ) NOT NULL ,
			
			PRIMARY KEY ( `zmd5` ) ,
			KEY `zDate` (`zDate`),
			KEY `hostname` (`hostname`),
			KEY `ipaddr` (`ipaddr`),
			KEY `smtp_err` (`smtp_err`)
			)";		
		$this->QUERY_SQL($sql,'artica_events');
		if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
	} 
	
	
	if(!$this->TABLE_EXISTS('snort','artica_events')){	
		$sql="CREATE TABLE IF NOT EXISTS `snort` (
			  `zDate` datetime NOT NULL,
			  `hostname` varchar(255) NOT NULL,
			  `ipaddr` varchar(50) NOT NULL,
			  `local_ipaddr` varchar(50) NOT NULL,
			  `port` int(3) NOT NULL,
			  `infos` varchar(255) NOT NULL,
			  `classification` varchar(255) NOT NULL,
			  `priority` int(2) NOT NULL,
			  `proto` varchar(10) NOT NULL,
			  `country` varchar(50) NOT NULL,
			  KEY `zDate` (`zDate`,`ipaddr`,`local_ipaddr`,`port`,`priority`,`proto`),
			  KEY `hostname` (`hostname`),
			  KEY `ipaddr` (`ipaddr`),
			  KEY `country` (`country`)
			)"; 	
			$this->QUERY_SQL($sql,'artica_events');
		if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
	} 	
	
	if(!$this->TABLE_EXISTS('adminevents','artica_events')){	
		$sql="CREATE TABLE IF NOT EXISTS `adminevents` (
			  `zDate` datetime NOT NULL,
			  `zmd5` varchar(90) NOT NULL,
			  `subject` varchar(255) NOT NULL,
			  `text` varchar(255) NOT NULL,
			  `jslink` varchar(255) NOT NULL,
			  `icon` varchar(90) NOT NULL,
			  `critical` int(1) NOT NULL,
			  `enabled` int(1) NOT NULL,
			  PRIMARY KEY ( `zmd5` ) ,
			  KEY `zDate` (`zDate`),
			  KEY `subject` (`subject`),
			  KEY `critical` (`critical`),
			  KEY `enabled` (`enabled`)
			)"; 	
		$this->QUERY_SQL($sql,'artica_events');
		if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
	} 

	
	
	if(!$this->TABLE_EXISTS('postfinder','artica_events')){	
		
		$sql="CREATE TABLE `artica_events`.`postfinder` (
				`md5` VARCHAR( 90 ) NOT NULL ,
				`date_start` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
				`date_end` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
				`finish` INT( 1 ) NOT NULL DEFAULT '0',
				`search_datas` LONGTEXT NOT NULL ,
				`pattern` VARCHAR( 255 ) NOT NULL ,
				`msg_num` INT( 2 ) NOT NULL ,
				PRIMARY KEY ( `md5` ) ,
				INDEX ( `date_start` , `date_end` , `finish`,`msg_num` )
				)";
		$this->QUERY_SQL($sql,'artica_events');
		if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
	} 

		

	if(!$this->TABLE_EXISTS('postfix_regex_words','artica_backup')){		
		$sql="CREATE TABLE `artica_backup`.`postfix_regex_words` (
			`ID` INT( 5 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`words` VARCHAR( 255 ) NOT NULL ,
			`hostname` VARCHAR( 255 ) NOT NULL ,
			`enabled` INT( 1 ) NOT NULL DEFAULT '1',
			KEY `words` (`words`),
			KEY `hostname` (`hostname`),
			INDEX(`enabled`)
			)";		
		$this->QUERY_SQL($sql,'artica_backup');
		if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
	} 
	
	
	if(!$this->TABLE_EXISTS('postfix_relais_domains_users','artica_backup')){		
		$sql="CREATE TABLE IF NOT EXISTS `postfix_relais_domains_users` (
		  `email` varchar(255) NOT NULL,
		  `password` varchar(255) NOT NULL,
		  `domain` varchar(80) NOT NULL,
		  `ou` varchar(255) NOT NULL,
		  PRIMARY KEY (`email`),
		  KEY `domain` (`domain`),
		  KEY `ou` (`ou`))";		
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
	}  	
	
	
	if(!$this->TABLE_EXISTS('amavis_bypass_rcpt','artica_backup')){		
		$sql="CREATE TABLE `artica_backup`.`amavis_bypass_rcpt` (`pattern` VARCHAR( 255 ) NOT NULL ,UNIQUE (`pattern`))";
		$this->QUERY_SQL($sql,'artica_backup');
		if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
	}		
	

	if(!$this->TABLE_EXISTS('postfix_duplicate_maps','artica_backup')){		
		$sql="CREATE TABLE IF NOT EXISTS `postfix_duplicate_maps` (
			  `pattern` varchar(255)  NOT NULL,
			  `relay` varchar(128)  NOT NULL,
			  `port` int(11) NOT NULL,
			  `ou` varchar(256)  NOT NULL,
			  `nextdomain` varchar(255)  NOT NULL,
			  UNIQUE KEY `pattern` (`pattern`),
			  KEY `relay` (`relay`),
			  KEY `ou` (`ou`)) ";
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
	}	
	
	
	if(!$this->TABLE_EXISTS('postfix_aliases_domains','artica_backup')){		
		$sql="CREATE TABLE IF NOT EXISTS `postfix_aliases_domains` (
			  `alias` varchar(255) NOT NULL,
			  `ou` varchar(128) NOT NULL,
			  `domain` varchar(255) NOT NULL,
			  PRIMARY KEY (`alias`),
			  KEY `ou` (`ou`),
			  KEY `domain` (`domain`))";
		$this->QUERY_SQL($sql,'artica_backup');
		if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}		
	} 	
	
	
	if(!$this->TABLE_EXISTS('postfix_whitelist_con','artica_backup')){		
		$sql="CREATE TABLE IF NOT EXISTS `postfix_whitelist_con` (
			  `ipaddr` varchar(255) NOT NULL,
			  `hostname` varchar(255) NOT NULL,
			  PRIMARY KEY (`ipaddr`),
			  KEY `hostname` (`hostname`))";
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
	} 				
	
		if(!$this->TABLE_EXISTS('postfix_global_blacklist','artica_backup')){		
		$sql="CREATE TABLE `artica_backup`.`postfix_global_blacklist` (
			`ID` INT( 5 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`sender` VARCHAR( 255 ) NOT NULL ,
			`hostname` VARCHAR( 255 ) NOT NULL ,
			`enabled` INT( 1 ) NOT NULL DEFAULT '1',
			`score` INT( 10 ) NOT NULL DEFAULT '0',
			KEY `sender` (`sender`),
			KEY `hostname` (`hostname`),
			INDEX(`enabled`,`score`)
			)";		
		$this->QUERY_SQL($sql,'artica_backup');
		if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
	}
	
	if(!$this->FIELD_EXISTS("postfix_global_blacklist","score","artica_backup")){
		$sql="ALTER TABLE `postfix_global_blacklist` ADD `score` INT( 10 ) NOT NULL DEFAULT '0',ADD INDEX ( `score` )";
		$this->QUERY_SQL($sql,'artica_backup');
	}		
	
	
	 
	
	if(!$this->TABLE_EXISTS('postfix_global_whitelist','artica_backup')){		
		$sql="CREATE TABLE `artica_backup`.`postfix_global_whitelist` (
			`ID` INT( 5 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`sender` VARCHAR( 255 ) NOT NULL ,
			`hostname` VARCHAR( 255 ) NOT NULL ,
			`enabled` INT( 1 ) NOT NULL DEFAULT '1',
			`score` INT( 10 ) NOT NULL DEFAULT '0',
			KEY `sender` (`sender`),
			KEY `hostname` (`hostname`),
			INDEX(`enabled`,`score`)
			)";		
		$this->QUERY_SQL($sql,'artica_backup');
		if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
	}	
	
	if(!$this->FIELD_EXISTS("postfix_global_whitelist","score","artica_backup")){
		$sql="ALTER TABLE `postfix_global_whitelist` ADD `score` INT( 10 ) NOT NULL DEFAULT '0',ADD INDEX ( `score` )";
		$this->QUERY_SQL($sql,'artica_backup');
	}		

	
	if(!$this->TABLE_EXISTS('mail_con_err_stats','artica_events')){	
		
		$sql="CREATE TABLE `artica_events`.`mail_con_err_stats` (
			`zmd5` VARCHAR( 120 ) NOT NULL ,
			`conx` INT( 5 ) NOT NULL ,
			`zweek` INT( 2 ) NOT NULL ,
			`zday` INT( 2 ) NOT NULL ,
			`zhour` INT( 2 ) NOT NULL ,
			`hostname` VARCHAR( 255 ) NOT NULL ,
			`ipaddr` VARCHAR( 40 ) NOT NULL ,
			`smtp_err` VARCHAR( 50 ) NOT NULL ,
			`zDate` DATETIME NOT NULL,
			PRIMARY KEY ( `zmd5` ) ,
			INDEX ( `conx` , `zweek` , `zday` , `zhour` , `ipaddr` , `smtp_err` ),
			KEY `zDate` (`zDate`)
			)";
		$this->QUERY_SQL($sql,'artica_events');
		if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
	}

	if(!$this->TABLE_EXISTS('smtp_generic_maps','artica_backup')){	
			$sql="CREATE TABLE `artica_backup`.`smtp_generic_maps` (
			`ID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`generic_from` VARCHAR( 255 ) NOT NULL ,
			`generic_to` VARCHAR( 255 ) NOT NULL ,
			`ou` VARCHAR( 255 ) NOT NULL ,
			`zmd5` VARCHAR( 90 ) NOT NULL ,
			KEY `generic_from` (`generic_from`),
			KEY `generic_to` (`generic_to`),
			KEY `ou` (`ou`),
			UNIQUE (`zmd5`)
			) ";
			$this->QUERY_SQL($sql,"artica_backup");
			if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		}else{
			if($GLOBALS["VERBOSE"]){echo "artica_backup\smtp_generic_maps Exists\n";}
		}	

		if(!$this->TABLE_EXISTS('redirectmail','artica_backup')){	
			$sql="CREATE TABLE `redirectmail` (
			  `id` int(11) NOT NULL auto_increment,
			  `from` varchar(255) default NULL,
			  `mail` varchar(255) default NULL,
			  `del` int(1) NOT NULL default '0',
					  PRIMARY KEY  (`id`),
					  KEY `from` (`from`),
					  KEY `del` (`del`)
					  )";	

			$this->QUERY_SQL($sql,"artica_backup");
			if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
			
		}
		
		
		if(!$this->TABLE_EXISTS('postfix_viptrack','artica_backup')){	
			$sql="CREATE TABLE `artica_backup`.`postfix_viptrack` (
			`email` VARCHAR( 128 ) NOT NULL ,
			`enabled` INT( 1 ) NOT NULL DEFAULT '1',
			PRIMARY KEY ( `email` )
			)";	
			$this->QUERY_SQL($sql,"artica_backup");
			if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
			
		}

		
if(!$this->TABLE_EXISTS('viptrack_connections','artica_events')){	
			$sql="CREATE TABLE IF NOT EXISTS `viptrack_connections` (
			  `zmd5` varchar(90) NOT NULL,
			  `domain_from` varchar(128) NOT NULL,
			  `domain_to` varchar(128) NOT NULL,
			  `from` varchar(255) NOT NULL,
			  `to` varchar(255) NOT NULL,
			  `zDate` datetime NOT NULL,
			  `bounce_error` varchar(50) NOT NULL,
			  `smtp_sender` varchar(40) NOT NULL,
			  `Country` varchar(40) NOT NULL,
			  PRIMARY KEY (`zmd5`),
			  KEY `zDate` (`zDate`,`bounce_error`),
			  KEY `domain_from` (`domain_from`),
			  KEY `domain_to` (`domain_to`),
			  KEY `from` (`from`),
			  KEY `to` (`to`),
			  KEY `smtp_sender` (`smtp_sender`,`Country`))";
			$this->QUERY_SQL($sql,"artica_events");
			if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
			
		}

		
if(!$this->TABLE_EXISTS('viptrack_content','artica_events')){	
			$sql="CREATE TABLE IF NOT EXISTS `viptrack_content` (
				  `zmd5` varchar(90) NOT NULL,
				  `domain_from` varchar(128) NOT NULL,
				  `domain_to` varchar(128) NOT NULL,
				  `from` varchar(255) NOT NULL,
				  `to` varchar(255) NOT NULL,
				  `zDate` datetime NOT NULL,
				  `subject` varchar(255) NOT NULL,
				  `bounce_error` varchar(50) NOT NULL,
				  `smtp_sender` varchar(40) NOT NULL,
				  `Country` varchar(40) NOT NULL,
				  `size` INT(10) NOT NULL,
				  PRIMARY KEY (`zmd5`),
				  KEY `zDate` (`zDate`,`bounce_error`,`size`),
				  KEY `domain_from` (`domain_from`),
				  KEY `domain_to` (`domain_to`),
				  KEY `subject` (`subject`),
				  KEY `from` (`from`),
				  KEY `to` (`to`),
				  KEY `smtp_sender` (`smtp_sender`,`Country`)
					)";
	$this->QUERY_SQL($sql,"artica_events");
	if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		}					
		
if(!$this->TABLE_EXISTS('mails_stats','artica_events')){
		$sql="CREATE TABLE IF NOT EXISTS `mails_stats` (
			  `zmd5` varchar(90) NOT NULL,
			  `zDate` datetime NOT NULL,
			  `instance` varchar(255) NOT NULL,
			  `sender` varchar(255) NOT NULL,
			  `sender_domain` varchar(255) NOT NULL,
			  `recipient` varchar(255) NOT NULL,
			  `recipient_domain` varchar(255) NOT NULL,
			  `sender_ip` varchar(255) NOT NULL,
			  `recipient_ip` varchar(255) NOT NULL,
			  `smtpcode` int(2) NOT NULL,
			  `mailsize` int(5) NOT NULL,
			  `artica_meta` int(1) NOT NULL DEFAULT '0',
			  UNIQUE KEY `zmd5` (`zmd5`),
			  KEY `instance` (`instance`),
			  KEY `sender` (`sender`),
			  KEY `sender_domain` (`sender_domain`),
			  KEY `recipient` (`recipient`),
			  KEY `recipient_domain` (`recipient_domain`),
			  KEY `sender_ip` (`sender_ip`),
			  KEY `recipient_ip` (`recipient_ip`),
			  KEY `smtpcode` (`smtpcode`),
			  KEY `artica_meta` (`artica_meta`),
			  KEY `zDate` (`zDate`)
			) ";
			$this->QUERY_SQL($sql,"artica_events");
			if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		}

		if(!$this->TABLE_EXISTS('postqueue','artica_events')){		
			$sql="CREATE TABLE IF NOT EXISTS `postqueue` (
			  `msgid` varchar(15) NOT NULL,
			  `instance` varchar(255) NOT NULL,
			  `zDate` datetime NOT NULL,
			  `from` varchar(128) NOT NULL,
			  `recipients` tinytext NOT NULL,
			  `context` varchar(50) NOT NULL,
			  `event` varchar(255) NOT NULL,
			  `removed` int(1) NOT NULL DEFAULT '0',
			  `from_domain` varchar(128) NOT NULL,
			  `size` int(10) NOT NULL,
			  PRIMARY KEY (`msgid`),
			  KEY `context` (`context`,`removed`),
			  KEY `removed` (`removed`),
			  KEY `from_domain` (`from_domain`),
			  KEY `from` (`from`),
			  KEY `zDate` (`zDate`),
			  KEY `instance` (`instance`),
			  KEY `size` (`size`),
			  FULLTEXT KEY `recipients` (`recipients`))";
			$this->QUERY_SQL($sql,"artica_events");
			if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		}

	if(!$this->TABLE_EXISTS('amavis_event','artica_events')){		
			$sql="CREATE TABLE IF NOT EXISTS `amavis_event` (
			  `zDate` datetime NOT NULL,
			  `ID` int(10) NOT NULL AUTO_INCREMENT,
			  `from` varchar(255) NOT NULL,
			  `to` varchar(255) NOT NULL,
			  `subject` varchar(255) NOT NULL,
			  `size` int(10) NOT NULL,
			  `bounce_error` varchar(50) NOT NULL,
			  `country` varchar(25) NOT NULL,
			  `city` varchar(30) NOT NULL,
			  `from_domain` varchar(128) NOT NULL,
			  `to_domain` varchar(128) NOT NULL,
			  `ipaddr` varchar(40) NOT NULL,
			  PRIMARY KEY (`ID`),
			  KEY `size` (`size`,`bounce_error`),
			  KEY `zDate` (`zDate`),
			  KEY `from` (`from`),
			  KEY `to` (`to`),
			  KEY `bounce_error` (`bounce_error`),
			  KEY `country` (`country`),
			  KEY `city` (`city`),
			  KEY `from_domain` (`from_domain`),
			  KEY `to_domain` (`to_domain`),
			  KEY `ipaddr` (`ipaddr`))";
				$this->QUERY_SQL($sql,"artica_events");
			if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
	}
	
	
if(!$this->TABLE_EXISTS('amavis_event_hours','artica_events')){		
			$sql="CREATE TABLE IF NOT EXISTS `amavis_event_hours` (
				 `zmd5` VARCHAR( 90 ), 
				  `zDate` date NOT NULL,
				  `hour` int(2) NOT NULL,
				  `domain_from` varchar(128) NOT NULL,
				  `domain_to` varchar(128) NOT NULL,
				  `size` int(10) NOT NULL,
				  `messages` int(10) NOT NULL,
				  `bounce_error` varchar(50) NOT NULL,
				  `country` VARCHAR( 20 ) NOT NULL ,
				  `ipaddr` VARCHAR( 40 ),
				  PRIMARY KEY  (`zmd5`),
				  KEY `zDate` (`zDate`,`hour`,`size`,`messages`),
				  KEY `domain_from` (`domain_from`),
				  KEY `domain_to` (`domain_to`),
				  KEY `country` (`country`),
				  KEY `ipaddr` (`ipaddr`),
				  KEY `bounce_error` (`bounce_error`)
				  )"; 	
				$this->QUERY_SQL($sql,"artica_events");
			if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
	}

	if(!$this->TABLE_EXISTS('postfix_bad_domains','artica_backup')){		
			$sql="CREATE TABLE IF NOT EXISTS `postfix_bad_domains` (
				  `domain` varchar(128) NOT NULL,
				  `hostname` varchar(255) NOT NULL,
				  PRIMARY KEY (`domain`),
				  KEY `hostname` (`hostname`)
				)  "; 	
				$this->QUERY_SQL($sql,"artica_backup");
				if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
	}	
	
	
	
			

		
	if(!$this->TABLE_EXISTS('bandwith_stats','artica_events')){	
			$sql="CREATE TABLE `artica_events`.`bandwith_stats` (
				`ID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`zDate` DATETIME NOT NULL ,
				`bandwith` BIGINT( 10 ) NOT NULL ,
				KEY `zDate` (`zDate`),
				KEY `bandwith` (`bandwith`)
				)";
			$this->QUERY_SQL($sql,"artica_events");
			if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		}
	if(!$this->TABLE_EXISTS('picasa','artica_backup')){	
			$sql="CREATE TABLE IF NOT EXISTS `picasa` (
				  `uid` varchar(200) NOT NULL,
				  `enabled` smallint(1) NOT NULL default '0',
				  `username` varchar(150) NOT NULL,
				  `password` varchar(150) NOT NULL,
				  `localfolder` varchar(255) NOT NULL,
				  `album_name` varchar(50) NOT NULL,
				  `ByPassProxy` smallint(1) NOT NULL default '1',
				  `CreateAlbum` smallint(1) NOT NULL default '0',
				  PRIMARY KEY  (`uid`)
				) ";
			$this->QUERY_SQL($sql,"artica_backup");
			if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		}

		$this->CheckTablesAwstats();
	}

public function CheckTablesAwstats(){
	
	if(!$this->TABLE_EXISTS('awstats_files','artica_backup')){	
			$sql="CREATE TABLE `artica_backup`.`awstats_files` (
			`servername` VARCHAR( 255 ) NOT NULL ,
			`awstats_file` VARCHAR( 80 ) NOT NULL ,
			`content` BLOB NOT NULL ,
			KEY `servername` (`servername`),
			KEY `awstats_file` (`awstats_file`)
			)";
		$this->QUERY_SQL($sql,"artica_backup");
		if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}	
	}

	
	if(!$this->TABLE_EXISTS('awstats','artica_backup')){	
			$sql="CREATE TABLE `artica_backup`.`awstats` (`website` VARCHAR( 255 ) NOT NULL ,
			`key` VARCHAR( 100 ) NOT NULL ,`value` VARCHAR( 255 ) NOT NULL ,
			KEY `website` (`website`),
			KEY `key` (`key`),
			KEY `value` (`value`)
			)";
		$this->QUERY_SQL($sql,"artica_backup");
		if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
	}	
	
}

public function CheckTablesPostfilter(){
		if(!$this->DATABASE_EXISTS("postfilter")){$this->CREATE_DATABASE("postfilter");}
		if(!$this->DATABASE_EXISTS("postfilter")){return false;}
		
		if(!$this->TABLE_EXISTS('spamtrap','postfilter')){
			$sql="CREATE TABLE spamtrap (
				_key CHAR(64) NOT NULL default '',
				_value CHAR(32) NOT NULL default '',
				_timestamp TIMESTAMP,
				UNIQUE INDEX (_key)
			) TYPE=MyISAM;"; 
			$this->QUERY_SQL($sql,'postfilter');
	
		}

	if(!$this->TABLE_EXISTS('dha','postfilter')){
			$sql="CREATE TABLE dha (
					_key CHAR(64) NOT NULL default '',
					_value CHAR(32) NOT NULL default '',
					_timestamp TIMESTAMP,
					UNIQUE INDEX (_key)
				) TYPE=MyISAM;"; 
			$this->QUERY_SQL($sql,'postfilter');
	
		}

	if(!$this->TABLE_EXISTS('greylist_white','postfilter')){
			$sql="CREATE TABLE greylist_white (
				_key CHAR(64) NOT NULL default '',
				_value CHAR(32) NOT NULL default 'auto-whitelisted',
				_timestamp TIMESTAMP,
				UNIQUE INDEX (_key)
			) TYPE=MyISAM;"; 
			$this->QUERY_SQL($sql,'postfilter');
	
		}			
		
	if(!$this->TABLE_EXISTS('greylist','postfilter')){
			$sql="CREATE TABLE greylist (
				_sender CHAR(64) NOT NULL default '',
				_recipient CHAR(64) NOT NULL default '',
				_ip char(40) NOT NULL default '',
				_last_seen TIMESTAMP,
				_first_seen TIMESTAMP,
				_count SMALLINT UNSIGNED NOT NULL default 0,
				UNIQUE KEY _ip (_ip,_sender,_recipient),
				KEY _last_seen (_last_seen),
				KEY _first_seen (_first_seen)
			) TYPE=MyISAM;"; 
			$this->QUERY_SQL($sql,'postfilter');
	
		}
		
		if(!$this->TABLE_EXISTS('honeypot','postfilter')){
			$sql="CREATE TABLE honeypot (
					_key CHAR(64) NOT NULL default '',
					_value CHAR(32) NOT NULL default '',
					_timestamp TIMESTAMP,
					UNIQUE INDEX (_key)
				) TYPE=MyISAM;"; 
			$this->QUERY_SQL($sql,'postfilter');
	
		}		
		
		

		if(!$this->TABLE_EXISTS('throttle','postfilter')){
			$sql="CREATE TABLE throttle (
					_address CHAR(64) NOT NULL default '',
					_count_max MEDIUMINT UNSIGNED NOT NULL default 0,
					_count_curr MEDIUMINT UNSIGNED NOT NULL default 1,
					_rcpt_max MEDIUMINT UNSIGNED NOT NULL default 0,
					_rcpt_curr MEDIUMINT UNSIGNED NOT NULL default 1,
					_quota_max INT UNSIGNED NOT NULL default 0,
					_quota_curr INT UNSIGNED NOT NULL default 0,
					_disabled INT UNSIGNED NOT NULL default 0,
					_date INT UNSIGNED NOT NULL default 0,
					_time_period MEDIUMINT UNSIGNED NOT NULL default 0,
					_priority TINYINT UNSIGNED NOT NULL default 0,
					UNIQUE KEY (_address)
				) TYPE=MyISAM;"; 
			$this->QUERY_SQL($sql,'postfilter');
	
		}
		
		if(!$this->TABLE_EXISTS('captcha','postfilter')){
			$sql="CREATE TABLE captcha (
				_sender CHAR(64) NOT NULL DEFAULT '',
				_recipient CHAR(64) NOT NULL DEFAULT '',
				_captcha CHAR(32),
				_timestamp TIMESTAMP,
				_try TIMESTAMP,
				_probes TINYINT UNSIGNED DEFAULT 0,
				_used TINYINT UNSIGNED DEFAULT 0,
				UNIQUE INDEX (_sender,_recipient)
			) TYPE=MyISAM;"; 
			$this->QUERY_SQL($sql,'postfilter');
	
		}	
		
		if(!$this->TABLE_EXISTS('cookie','postfilter')){
			$sql="CREATE TABLE cookie (
				_address CHAR(64) NOT NULL DEFAULT '',
				_cookie CHAR(32),
				_timestamp TIMESTAMP,
				UNIQUE INDEX (_address)
			) TYPE=MyISAM;"; 
			$this->QUERY_SQL($sql,'postfilter');
	
		}	

		if(!$this->TABLE_EXISTS('banned_client','postfilter')){
			$sql="CREATE TABLE banned_client (
				_key CHAR(64) NOT NULL default '',
				_value CHAR(32) NOT NULL default '',
				_timestamp TIMESTAMP,
				UNIQUE INDEX (_key)
			) TYPE=MyISAM;"; 
			$this->QUERY_SQL($sql,'postfilter');
	
		}

		if(!$this->TABLE_EXISTS('banned_sender','postfilter')){
			$sql="CREATE TABLE banned_sender (
					_key CHAR(64) NOT NULL default '',
					_value CHAR(32) NOT NULL default '',
					_timestamp TIMESTAMP,
					UNIQUE INDEX (_key)
				) TYPE=MyISAM;"; 
			$this->QUERY_SQL($sql,'postfilter');
	
		}	

		if(!$this->TABLE_EXISTS('banned_helo','postfilter')){
			$sql="CREATE TABLE banned_helo (
				_key CHAR(64) NOT NULL default '',
				_value CHAR(32) NOT NULL default '',
				_timestamp TIMESTAMP,
				UNIQUE INDEX (_key)
			) TYPE=MyISAM;"; 
			$this->QUERY_SQL($sql,'postfilter');
	
		}	

		if(!$this->TABLE_EXISTS('blacklist','postfilter')){
			$sql="CREATE TABLE blacklist (
				_key CHAR(64) NOT NULL default '',
				_value CHAR(32) NOT NULL default '',
				_timestamp TIMESTAMP,
				UNIQUE INDEX (_key)
			) TYPE=MyISAM;"; 
			$this->QUERY_SQL($sql,'postfilter');
	
		}	

		if(!$this->TABLE_EXISTS('whitelist','postfilter')){
			$sql="CREATE TABLE whitelist (
				_key CHAR(64) NOT NULL default '',
				_value CHAR(32) NOT NULL default '',
				_timestamp TIMESTAMP,
				UNIQUE INDEX (_key)
			) TYPE=MyISAM;"; 
			$this->QUERY_SQL($sql,'postfilter');
	
		}	

		if(!$this->TABLE_EXISTS('helo','postfilter')){
			$sql="CREATE TABLE helo (
				_key CHAR(64) NOT NULL default '',
				_value CHAR(32) NOT NULL default '',
				_timestamp TIMESTAMP,
				UNIQUE INDEX (_key)
			) TYPE=MyISAM;"; 
			$this->QUERY_SQL($sql,'postfilter');
	
		}

		if(!$this->TABLE_EXISTS('user_whitelist','postfilter')){
			$sql="CREATE TABLE user_whitelist (
				_userid INT UNSIGNED NOT NULL,
				_address CHAR(64) NOT NULL default '',
				_type BOOL DEFAULT 0,
				_timestamp TIMESTAMP,
				INDEX (_userid),
				UNIQUE INDEX (_userid, _address)
			) TYPE=MyISAM;"; 
			$this->QUERY_SQL($sql,'postfilter');
	
		}
		
		if(!$this->TABLE_EXISTS('user','postfilter')){
			$sql="CREATE TABLE user (
				_userid INT UNSIGNED NOT NULL AUTO_INCREMENT,
				_master INT UNSIGNED NOT NULL DEFAULT 0,
				_address CHAR(64) NOT NULL default '',
				_passwd CHAR(40) DEFAULT '',
				_type BOOL DEFAULT 0,
				_mode BOOL DEFAULT 0,
				_hidden BOOL DEFAULT 0,
				_filter CHAR(255) NOT NULL DEFAULT 'permit',
				_lang CHAR(2) DEFAULT '',
				PRIMARY KEY (_userid),
				INDEX (_master),
				UNIQUE INDEX (_address)
			) TYPE=MyISAM;	"; 
			$this->QUERY_SQL($sql,'postfilter');
			$sql="ALTER TABLE user AUTO_INCREMENT=1;";
			$this->QUERY_SQL($sql,'postfilter');
		}	

			if(!$this->TABLE_EXISTS('config','postfilter')){
			$sql="CREATE TABLE config (
				_version INT UNSIGNED NOT NULL AUTO_INCREMENT,
				_config MEDIUMTEXT,
				_timestamp TIMESTAMP,
				UNIQUE INDEX (_version)
			) TYPE=MyISAM"; 
			$this->QUERY_SQL($sql,'postfilter');
		}

			if(!$this->TABLE_EXISTS('report','postfilter')){
			$sql="CREATE TABLE report (
				_sender CHAR(64),
				_recipient CHAR(64) NOT NULL DEFAULT '',
				_client_name CHAR(128),
				_client_address CHAR(40),
				_username CHAR(64) NOT NULL DEFAULT '',
				_class CHAR(32),
				_policy CHAR(32),
				_report BOOL DEFAULT 1,
				_timestamp TIMESTAMP,
				INDEX (_recipient)
			) TYPE=MyISAM;"; 
			$this->QUERY_SQL($sql,'postfilter');
		}			

			if(!$this->TABLE_EXISTS('statistics','postfilter')){
			$sql="CREATE TABLE statistics (
				_entry CHAR(64) NOT NULL DEFAULT '',
				_type CHAR(64) NOT NULL DEFAULT '',
				_info CHAR(128) NOT NULL DEFAULT '',
				_count INT UNSIGNED NOT NULL DEFAULT 0,
				UNIQUE KEY (_entry, _type)
			) TYPE=MyISAM;"; 
			$this->QUERY_SQL($sql,'postfilter');
		}

		return true;
	}
	
	public function checkRoundCubeTables($database){
		$posix_getuid=posix_getuid();
		if($posix_getuid==0){echo "Starting......: roundcube checking $database.contactgroupmembers\n";}
		if(!$this->TABLE_EXISTS('contactgroupmembers',$database)){
			if($posix_getuid==0){echo "Starting......: roundcube $database.contactgroupmembers did not exists (create it)\n";}
			
		$sql="CREATE TABLE `contactgroupmembers` (
  `contactgroup_id` int(10) UNSIGNED NOT NULL,
  `contact_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  PRIMARY KEY (`contactgroup_id`, `contact_id`),
  CONSTRAINT `contactgroup_id_fk_contactgroups` FOREIGN KEY (`contactgroup_id`)
    REFERENCES `contactgroups`(`contactgroup_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `contact_id_fk_contacts` FOREIGN KEY (`contact_id`)
    REFERENCES `contacts`(`contact_id`) ON DELETE CASCADE ON UPDATE CASCADE) 
	";
		$this->QUERY_SQL($sql,$database);
		if(!$this->ok){
			writelogs($this->mysql_error,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			if($posix_getuid==0){echo "Starting......: roundcube $this->mysql_error\n";}}
		}else{
			if($posix_getuid==0){echo "Starting......: roundcube $database.contactgroupmembers exists\n";}
		}
		
		if($posix_getuid==0){echo "Starting......: roundcube checking $database.contactgroups\n";}
		if(!$this->TABLE_EXISTS('contactgroups',$database)){
			if($posix_getuid==0){echo "Starting......: roundcube $database.contactgroups did not exists (create it)\n";}
			$sql="CREATE TABLE `contactgroups` (
			  `contactgroup_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
			  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
			  `changed` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
			  `del` tinyint(1) NOT NULL DEFAULT '0',
			  `name` varchar(128) NOT NULL DEFAULT '',
			  PRIMARY KEY(`contactgroup_id`),
			  CONSTRAINT `user_id_fk_contactgroups` FOREIGN KEY (`user_id`)
			    REFERENCES `users`(`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
			  INDEX `contactgroups_user_index` (`user_id`,`del`)
			)";

		
			$this->QUERY_SQL($sql,$database);
			if(!$this->ok){
				writelogs($this->mysql_error,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				if($posix_getuid==0){echo "Starting......: roundcube $this->mysql_error\n";}}
		}else{
			if($posix_getuid==0){echo "Starting......: roundcube $database.contactgroups exists\n";}
		}
		
		if($posix_getuid==0){echo "Starting......: roundcube checking identities.contactgroups\n";}
		if(!$this->FIELD_EXISTS("identities","changed",$database)){	
			if($posix_getuid==0){echo "Starting......: roundcube $database.identities.changed did not exists (create it)\n";}
			$sql="ALTER TABLE `identities` ADD INDEX `user_identities_index` (`user_id`, `del`);";	
			$this->QUERY_SQL($sql,$database);
			if(!$this->ok){if($posix_getuid==0){echo "Starting......: roundcube $this->mysql_error\n";}}
			$sql="ALTER TABLE `identities` ADD `changed` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' AFTER `user_id`;";
			$this->QUERY_SQL($sql,$database);
			if(!$this->ok){if($posix_getuid==0){echo "Starting......: roundcube $this->mysql_error\n";}}
		}
		
		if(!$this->TABLE_EXISTS('notes',$database)){	
					$sql="CREATE TABLE IF NOT EXISTS `notes` (
					  `id` int(11) NOT NULL auto_increment,
					  `nid` int(11) NOT NULL,
					  `width` int(11) default '0',
					  `height` int(11) default '0',
					  `pos_x` int(11) default '0',
					  `pos_y` int(11) default '0',
					  `text` text,
					  `user_id` int(10) unsigned NOT NULL,
					  `timestamp` timestamp NOT NULL default '0000-00-00 00:00:00',
					  PRIMARY KEY  (`id`),
					  KEY `user_id` (`user_id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
					$this->QUERY_SQL($sql,$database);
					if(!$this->ok){if($posix_getuid==0){echo "Starting......: roundcube notes table: $this->mysql_error\n";}}
		
					$sql="ALTER TABLE `notes` ADD CONSTRAINT `notes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;";		
					if(!$this->ok){if($posix_getuid==0){echo "Starting......: roundcube notes table: $this->mysql_error\n";}}
			}
		
		if($posix_getuid==0){echo "Starting......: roundcube checking done\n";}
	}
	public function checkAgentTables(){
			if(!$this->TABLE_EXISTS('computers_drives','artica_backup')){
			$sql="CREATE TABLE `artica_backup`.`computers_drives` (
				`ID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`MAC` VARCHAR( 25 ) NOT NULL ,
				`letter` VARCHAR( 1 ) NOT NULL ,
				`path` VARCHAR( 255 ) NOT NULL ,
				INDEX ( `MAC` , `letter` , `path` )
				)"; 
			$this->QUERY_SQL($sql,'artica_backup');
			}
			
			if(!$this->TABLE_EXISTS('computers_tasks','artica_backup')){
			$sql="CREATE TABLE IF NOT EXISTS `computers_tasks` (
					  `ID` int(11) NOT NULL AUTO_INCREMENT,
					  `MAC` varchar(60) NOT NULL,
					  `task_type` varchar(20) NOT NULL,
					  `schedule` varchar(80) NOT NULL,
					  `task_enabled` int(1) NOT NULL,
					  `path` varchar(255) NOT NULL,
					  PRIMARY KEY (`ID`),
					  KEY `MAC` (`MAC`,`task_type`,`task_enabled`)
					)";	
			$this->QUERY_SQL($sql,'artica_backup');
			}
			
			
			if(!$this->TABLE_EXISTS('computers_tasks','artica_backup')){
			$sql="CREATE TABLE `artica_backup`.`computers_events` (
				`ID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`MAC` VARCHAR( 80 ) NOT NULL ,
				`zDate` DATETIME NOT NULL ,
				`events` TEXT NOT NULL ,
				`events_type` VARCHAR( 60 ) NOT NULL ,
				INDEX ( `MAC` , `zDate` , `events_type` )
				)";	
				$this->QUERY_SQL($sql,'artica_backup'); 
			}
			
		if(!$this->TABLE_EXISTS('computers_orders','artica_backup')){	
			$sql="CREATE TABLE `artica_backup`.`computers_orders` (
						`ID` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
						`taskid` INT( 10 ) NOT NULL ,
						`task_type` VARCHAR( 20 ) NOT NULL ,
						`parameters` TEXT NOT NULL ,
						`MAC` VARCHAR( 80 ) NOT NULL ,
						INDEX ( `taskid` , `task_type` , `MAC` )
						)";	
				$this->QUERY_SQL($sql,'artica_backup'); 
			}
			
			
		if(!$this->TABLE_EXISTS('computers_parameters','artica_backup')){	
			$sql="CREATE TABLE `artica_backup`.`computers_parameters` (
				`ID` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`MAC` VARCHAR( 50 ) NOT NULL ,
				`parameters` TEXT NOT NULL ,
				INDEX ( `MAC` )
				)";	
				$this->QUERY_SQL($sql,'artica_backup'); 
			}	
	}
	public function CheckTableWebsites(){
		
	if(!$this->TABLE_EXISTS('joomla_users','artica_backup')){
			$sql="CREATE TABLE IF NOT EXISTS `joomla_users` (
		  `servername` varchar(255) NOT NULL,
		  `uid` varchar(90) NOT NULL,
		  `ou` varchar(255) NOT NULL,
		  `language` varchar(10) NOT NULL,
		  `username` varchar(255) NOT NULL,
		  `password` varchar(255) NOT NULL,
		  `databasename` varchar(255) NOT NULL,
		  `status` varchar(60) NOT NULL,
		  `events` text NOT NULL,
		  `install` int(1) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`servername`),
		  KEY `uid` (`uid`),
		  KEY `ou` (`ou`),
		  KEY `status` (`status`),
		  KEY `install` (`install`)
		) ";
					
			$this->QUERY_SQL($sql,'artica_backup');
			if($this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		}
		
		
		
	if(!$this->TABLE_EXISTS('freeweb','artica_backup')){
			$sql="CREATE TABLE IF NOT EXISTS `freeweb` (
				  `servername` varchar(255) NOT NULL,
				  `useSSL` int(1) NOT NULL DEFAULT '0',
				  `useFTP` int(1) NOT NULL DEFAULT '1',
				  `useMysql` int(1) NOT NULL DEFAULT '1',
				  `mysql_database` varchar(255) NOT NULL,
				  `uid` varchar(255) NOT NULL,
				  `mysql_username` varchar(255) NOT NULL,
				  `mysql_password` varchar(255) NOT NULL,
				  `Params` longtext NOT NULL,
				  `ftpuser` varchar(255) NOT NULL,
				  `ftppassword` varchar(255) NOT NULL,
				  `groupware` varchar(40) NOT NULL,
				  `mod_rewrite` blob NOT NULL,
				  `EnbaleFDPermissions` int(1) NOT NULL DEFAULT '0',
				  `FDPermissions` blob NOT NULL,
				  `lvm_vg` varchar(90) DEFAULT NULL,
				  `lvm_size` int(3) NOT NULL,
				  `UseLoopDisk` int(1) NOT NULL DEFAULT '0',
				  `LoopMounts` varchar(255) NOT NULL,
				  `ou` varchar(255) NOT NULL,
				  `domainname` VARCHAR( 255 ),
				  `www_dir` VARCHAR( 255 ) NOT NULL,
				  `ServerPort` INT( 5 ) NOT NULL, 
				  UNIQUE KEY `servername` (`servername`),
				  KEY `useSSL` (`useSSL`,`uid`),
				  KEY `useMysql` (`useMysql`),
				  KEY `useFTP` (`useFTP`),
				  KEY `groupware` (`groupware`),
				  KEY `EnbaleFDPermissions` (`EnbaleFDPermissions`),
				  KEY `lvm_vg` (`lvm_vg`),
				  KEY `UseLoopDisk` (`UseLoopDisk`),
				  KEY `domainname` (`domainname`),
				  KEY `ou` (`ou`)
				) ";
					
			$this->QUERY_SQL($sql,'artica_backup');
			if($this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		}

		if(!$this->FIELD_EXISTS("freeweb","useMysql","artica_backup")){
			$sql="ALTER TABLE `freeweb` ADD `useMysql` INT(1) NOT NULL DEFAULT '1' AFTER `useSSL` ,ADD INDEX ( `UseMysql` )";
			$this->QUERY_SQL($sql,'artica_backup');
			}

		if(!$this->FIELD_EXISTS("freeweb","Forwarder","artica_backup")){
			$sql="ALTER TABLE `freeweb` ADD `Forwarder` INT(1) NOT NULL DEFAULT '0' ,ADD INDEX ( `Forwarder` )";
			$this->QUERY_SQL($sql,'artica_backup');
			}			
			
		if(!$this->FIELD_EXISTS("freeweb","useFTP","artica_backup")){
			$sql="ALTER TABLE `freeweb` ADD `useFTP` INT(1) NOT NULL DEFAULT '1' AFTER `useSSL` ,ADD INDEX ( `useFTP` )";
			$this->QUERY_SQL($sql,'artica_backup');
			}
			
		if(!$this->FIELD_EXISTS("freeweb","groupware","artica_backup")){
			$sql="ALTER TABLE `freeweb` ADD `groupware` VARCHAR( 40 ) NOT NULL ,ADD INDEX ( `groupware` )";
			$this->QUERY_SQL($sql,'artica_backup');
			}
		
		if(!$this->FIELD_EXISTS("freeweb","mod_rewrite","artica_backup")){
			$sql="ALTER TABLE `freeweb` ADD `mod_rewrite` BLOB NOT NULL";
			$this->QUERY_SQL($sql,'artica_backup');
			}	

		if(!$this->FIELD_EXISTS("freeweb","EnbaleFDPermissions","artica_backup")){
			$sql="ALTER TABLE `freeweb` ADD `EnbaleFDPermissions` INT( 1 ) NOT NULL DEFAULT '0',ADD `FDPermissions` BLOB NOT NULL , ADD INDEX ( `EnbaleFDPermissions` )";
			$this->QUERY_SQL($sql,'artica_backup');
			}	

		if(!$this->FIELD_EXISTS("freeweb","lvm_vg","artica_backup")){
			$sql="ALTER TABLE `freeweb` ADD `lvm_vg` VARCHAR( 90 ) NULL ,ADD INDEX ( `lvm_vg` )";
			$this->QUERY_SQL($sql,'artica_backup');
			}	
			
		if(!$this->FIELD_EXISTS("freeweb","lvm_size","artica_backup")){
			$sql="ALTER TABLE `freeweb` ADD `lvm_size` INT( 3 ) NOT NULL ";
			$this->QUERY_SQL($sql,'artica_backup');
			}

		if(!$this->FIELD_EXISTS("freeweb","UseLoopDisk","artica_backup")){
			$sql="ALTER TABLE `freeweb` ADD `UseLoopDisk` INT( 1 ) NOT NULL DEFAULT '0',ADD `LoopMounts` VARCHAR( 255 ) NOT NULL ,ADD INDEX ( `UseLoopDisk` )";
			$this->QUERY_SQL($sql,'artica_backup');
			}	
			
		if(!$this->FIELD_EXISTS("freeweb","ou","artica_backup")){	
			$sql="ALTER TABLE `freeweb` ADD `ou` VARCHAR( 255 ) NOT NULL,ADD INDEX ( `ou` )";
			$this->QUERY_SQL($sql,'artica_backup');				
			}	

		if(!$this->FIELD_EXISTS("freeweb","domainname","artica_backup")){	
			$sql="ALTER TABLE `freeweb` ADD `domainname` VARCHAR( 255 ) NOT NULL ,ADD INDEX ( `domainname` );";
			$this->QUERY_SQL($sql,'artica_backup');				
			}

		if(!$this->FIELD_EXISTS("freeweb","www_dir","artica_backup")){	
			$sql="ALTER TABLE `freeweb` ADD `www_dir` VARCHAR( 255 ) NOT NULL;";
			$this->QUERY_SQL($sql,'artica_backup');				
			}

		if(!$this->FIELD_EXISTS("freeweb","ServerPort","artica_backup")){	
			$sql="ALTER TABLE `freeweb` ADD `ServerPort` INT( 5 ) NOT NULL;";
			$this->QUERY_SQL($sql,'artica_backup');				
			}	
		if(!$this->FIELD_EXISTS("freeweb","UseReverseProxy","artica_backup")){	
			$sql="ALTER TABLE `freeweb` ADD `UseReverseProxy` INT( 1 ) NOT NULL DEFAULT '0',ADD INDEX ( `UseReverseProxy` )";
			$this->QUERY_SQL($sql,'artica_backup');				
			}	
		if(!$this->FIELD_EXISTS("freeweb","ProxyPass","artica_backup")){	
			$sql="ALTER TABLE `freeweb` ADD `ProxyPass` VARCHAR( 255 ) NOT NULL ,ADD `ProxyPassReverse` VARCHAR( 255 ) NOT NULL ";
			$this->QUERY_SQL($sql,'artica_backup');				
			}
		if(!$this->FIELD_EXISTS("freeweb","ForwardTo","artica_backup")){	
			$sql="ALTER TABLE `freeweb` ADD `ForwardTo` VARCHAR( 255 ) NOT NULL";
			$this->QUERY_SQL($sql,'artica_backup');				
		}	

		if(!$this->FIELD_EXISTS("freeweb","resolved_ipaddr","artica_backup")){	
			$sql="ALTER TABLE `freeweb` ADD `resolved_ipaddr` VARCHAR( 255 ) NOT NULL";
			$this->QUERY_SQL($sql,'artica_backup');				
		}	

		if(!$this->FIELD_EXISTS("freeweb","DrupalInfos","artica_backup")){
			$sql="ALTER TABLE `freeweb` ADD `DrupalInfos`longtext NOT NULL";
			$this->QUERY_SQL($sql,'artica_backup');
		}		

		if(!$this->FIELD_EXISTS("freeweb","DrupalModules","artica_backup")){
			$sql="ALTER TABLE `freeweb` ADD `DrupalModules`longtext NOT NULL";
			$this->QUERY_SQL($sql,'artica_backup');
		}			
			

		  
		if(!$this->TABLE_EXISTS('freewebs_secfilters','artica_backup')){
			$sql="CREATE TABLE IF NOT EXISTS `freewebs_secfilters` (
				  `ID` int(5) NOT NULL AUTO_INCREMENT,
				  `servername` varchar(255) NOT NULL,
				  `key` varchar(40) NOT NULL,
				  `value` varchar(255) NOT NULL,
				  `enabled` int(1) NOT NULL DEFAULT '1',
				  `zmd5` varchar(90) NOT NULL,
				  PRIMARY KEY (`ID`),
				  UNIQUE KEY `zmd5` (`zmd5`),
				  KEY `key` (`key`,`enabled`),
				  KEY `servername` (`servername`)
				) ";
			$this->QUERY_SQL($sql,'artica_backup');
			if($this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		}
		
		
		if(!$this->TABLE_EXISTS('freewebs_proxy','artica_backup')){
			$sql="CREATE TABLE `artica_backup`.`freewebs_proxy` (
				`servername` VARCHAR( 255 ) NOT NULL ,
				`ProxyPass` VARCHAR( 255 ) NOT NULL ,
				`zmd5` VARCHAR( 95 ) NOT NULL ,
				PRIMARY KEY ( `zmd5` ) ,
				KEY `servername` (`servername`),
				KEY `ProxyPass` (`ProxyPass`)
				
				) ";
			$this->QUERY_SQL($sql,'artica_backup');
			if($this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		}		
		

		
		if(!$this->TABLE_EXISTS('freewebs_whitelist','artica_backup')){
			$sql="CREATE TABLE IF NOT EXISTS `freewebs_whitelist` (
				  `ipaddr` varchar(255) NOT NULL,
				  `ID` int(10) NOT NULL AUTO_INCREMENT,
				  `servername` varchar(255) NOT NULL,
				  PRIMARY KEY (`ID`) )";
			$this->QUERY_SQL($sql,'artica_backup');
			if($this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		}
		
		
		if(!$this->TABLE_EXISTS('freewebs_aliases','artica_backup')){
			$sql="CREATE TABLE `artica_backup`.`freewebs_aliases` (
				`ID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`servername` VARCHAR( 128 ) NOT NULL ,
				`alias` VARCHAR( 255 ) NOT NULL ,
				`directory` VARCHAR( 255 ) NOT NULL ,
				INDEX ( `servername` )
				) ";
			$this->QUERY_SQL($sql,'artica_backup');
			if($this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		}	

		
		if(!$this->TABLE_EXISTS('drupal_queue_orders','artica_backup')){
			$sql="CREATE TABLE IF NOT EXISTS `drupal_queue_orders` (
			  `ID` int(11) NOT NULL AUTO_INCREMENT,
			  `ORDER` varchar(20) NOT NULL,
			  `value` varchar(255) NOT NULL,
			  `servername` varchar(255) NOT NULL,
			  PRIMARY KEY (`ID`),
			  KEY `ORDER` (`ORDER`),
			  KEY `servername` (`servername`)
			) ";		
		$this->QUERY_SQL($sql,'artica_backup');
			if($this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		}
	
 			

		if(!$this->TABLE_EXISTS('rbl_servers','artica_backup')){
			$sql="CREATE TABLE IF NOT EXISTS `rbl_servers` (
			  `rbl` varchar(128) NOT NULL,
			  `enabled` int(1) NOT NULL DEFAULT '1',
			  UNIQUE KEY `rbl` (`rbl`))";	
			$this->QUERY_SQL($sql,'artica_backup');
			if($this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		}

		if(!$this->TABLE_EXISTS('ipblocks_db','artica_backup')){
			$sql="CREATE TABLE `artica_backup`.`ipblocks_db` (
				`cdir` VARCHAR( 50 ) NOT NULL ,
				`country` VARCHAR( 5 ) NOT NULL ,
				`zDate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
				PRIMARY KEY ( `cdir` ) ,
				INDEX ( `country` , `zDate` ))";	
			$this->QUERY_SQL($sql,'artica_backup');
			if($this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		}		
		
		
	if(!$this->TABLE_EXISTS('ipblocks_set','artica_backup')){
			$sql="CREATE TABLE IF NOT EXISTS `ipblocks_set` (`country` varchar(10) NOT NULL,PRIMARY KEY (`country`))"; 		
			$this->QUERY_SQL($sql,'artica_backup');
			if($this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
 	}
 	
 	
}
	public function CheckTableVirus(){
		
	if(!$this->TABLE_EXISTS('antivirus_events','artica_events')){
			$sql="CREATE TABLE IF NOT EXISTS `antivirus_events` (
			  `ID` int(100) NOT NULL AUTO_INCREMENT,
			  `zDate` datetime NOT NULL,
			  `TaskName` varchar(90) NOT NULL,
			  `email` int(1) NOT NULL DEFAULT '0',
			  `VirusName` varchar(60) NOT NULL,
			  `InfectedPath` varchar(255) NOT NULL DEFAULT '',
			  `ComputerName` varchar(150) NOT NULL,
			  `zmd5` varchar(90) NOT NULL,
			  PRIMARY KEY (`ID`),
			  UNIQUE KEY `zmd5` (`zmd5`),
			  KEY `zDate` (`zDate`,`TaskName`,`email`,`VirusName`),
			  KEY `ComputerName` (`ComputerName`)
			)";	
			$this->QUERY_SQL($sql,'artica_events');
			}
			
		if(!$this->FIELD_EXISTS("antivirus_events","zmd5","artica_events")){	
			$sql="ALTER TABLE `antivirus_events` ADD `zmd5` varchar(90) NOT NULL ,ADD UNIQUE KEY `zmd5` (`zmd5`)";
			$this->QUERY_SQL($sql,'artica_events');
		}				
		
	}

	public function check_emailing_tables(){
		if(posix_getuid()==0){$ASROOT=true;}
		if(!$this->TABLE_EXISTS('emailing_db_paths','artica_backup')){
			$sql="CREATE TABLE IF NOT EXISTS `emailing_db_paths` (
			  `ID` int(11) NOT NULL AUTO_INCREMENT,
			  `filepath` varchar(255) NOT NULL,
			  `databasename` varchar(255) NOT NULL,
			  `progress` int(2) NOT NULL,
			  `finish` int(1) NOT NULL DEFAULT '0',
			  `reports_import` text NOT NULL,
			  `ou` varchar(255) NOT NULL,
			  `zDate` datetime NOT NULL,
			  `progress_export` int(11) NOT NULL,
			  `options` TINYTEXT NOT NULL,
			  `merged` INT( 1 ) NOT NULL,
			  PRIMARY KEY (`ID`),
			  UNIQUE KEY `databasename` (`databasename`),
			  KEY `finish` (`finish`),
			  KEY `ou` (`ou`),
			  KEY `zDate` (`zDate`)
			) ";
				$this->QUERY_SQL($sql,'artica_backup');
		}
		
		if(!$this->FIELD_EXISTS("emailing_db_paths","zDate","artica_backup")){
				$sql="ALTER TABLE `emailing_db_paths` ADD `zDate` datetime NOT NULL,ADD INDEX ( `zDate` )";
				$this->QUERY_SQL($sql,'artica_backup');
		}
		
		if(!$this->FIELD_EXISTS("emailing_db_paths","merged","artica_backup")){
			$sql="ALTER TABLE `emailing_db_paths` ADD `merged` INT( 1 ) NOT NULL ,ADD INDEX ( `merged` )";
			$this->QUERY_SQL($sql,'artica_backup');
			}

		if(!$this->FIELD_EXISTS("emailing_db_paths","progress_export","artica_backup")){
				$sql="ALTER TABLE `emailing_db_paths` ADD `progress_export` int(11) NOT NULL";
				$this->QUERY_SQL($sql,'artica_backup');
		}	

		if(!$this->FIELD_EXISTS("emailing_db_paths","options","artica_backup")){
				$sql="ALTER TABLE `emailing_db_paths` ADD `options` TINYTEXT NOT NULL";
				$this->QUERY_SQL($sql,'artica_backup');
		}

		if(!$this->TABLE_EXISTS('emailing_templates','artica_backup')){		
			$sql="CREATE TABLE `artica_backup`.`emailing_templates` (
					`ID` INT( 2 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
					`zdate` DATETIME NOT NULL ,
					`template_name` VARCHAR( 255 ) NOT NULL ,
					`template_datas` longtext NOT NULL,
					`ou` VARCHAR( 255 ) NOT NULL ,
					`template_css` longtext NOT NULL,
					`subject` VARCHAR( 255 ) NOT NULL,
					 `advopts` MEDIUMTEXT NOT NULL,
					 KEY `ou` (`ou`)
				)";		
			$this->QUERY_SQL($sql,'artica_backup');
		}
		
	if(!$this->TABLE_EXISTS('emailing_tbl_files','artica_backup')){			
		$sql="CREATE TABLE emailing_tbl_files (
		     ID tinyint(3) unsigned NOT NULL auto_increment,
		     bin_data longblob NOT NULL,
		     template_id INT( 10 ) NOT NULL,
		     filename varchar(50) NOT NULL,
		     filesize varchar(50) NOT NULL,
		     filetype varchar(50) NOT NULL,
		     PRIMARY KEY (ID),
		     KEY `template_id` (`template_id`))";		
			$this->QUERY_SQL($sql,'artica_backup');	
		}

		
		
	if(!$this->TABLE_EXISTS('emailing_mailers','artica_backup')){			
			$sql="CREATE TABLE `artica_backup`.`emailing_mailers` (
					`ID` INT( 5 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
					`smtpserver` VARCHAR( 255 ) NOT NULL ,
					`parameters` LONGTEXT NOT NULL ,
					`ou` VARCHAR( 255 ) NOT NULL ,
					INDEX ( `ou` ))";
			$this->QUERY_SQL($sql,'artica_backup');	
		}
		
	if(!$this->TABLE_EXISTS('emailing_campain_linker','artica_backup')){			
			$sql="CREATE TABLE IF NOT EXISTS `emailing_campain_linker` (
				  `ID` int(5) NOT NULL AUTO_INCREMENT,
				  `template_id` int(5) NOT NULL,
				  `database_id` int(5) NOT NULL,
				  `parameters` longtext NOT NULL,
				  `finish_queue_processing` int(1) NOT NULL DEFAULT '0',
				  `queue_builder_pourc` INT( 1 ) NOT NULL DEFAULT '0',
				  `zdate` datetime NOT NULL,
				  `name` varchar(255) NOT NULL,
				  `ou` varchar(255) NOT NULL,
				  `locked` INT( 1 ) NOT NULL DEFAULT '0',
				  PRIMARY KEY (`ID`),
				  KEY `template_id` (`template_id`),
				  KEY `database_id` (`database_id`),
				  KEY `finish_queue_processing` (`finish_queue_processing`),
				  KEY `ou` (`ou`)
				) ";
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){writelogs("emailing_campain_linker.artica_backup -> $this->mysql_error\n\n$sql\n",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}	
	}

	if(!$this->TABLE_EXISTS('emailing_campain_imap','artica_backup')){			
			$sql="CREATE TABLE IF NOT EXISTS `emailing_campain_imap` (
			  `account_name` varchar(255) NOT NULL,
			  `parameters` longtext NOT NULL,
			  `ou` varchar(255) NOT NULL,
			  `enabled` int(1) NOT NULL,
			  PRIMARY KEY (`account_name`),
			  UNIQUE KEY `account_name` (`account_name`),
			  KEY `ou` (`ou`)
			) ";
			$this->QUERY_SQL($sql,'artica_backup');	
	}
	
	
	
	if(!$this->TABLE_EXISTS('emailing_campain_imap_events','artica_backup')){			
			$sql="CREATE TABLE IF NOT EXISTS `emailing_campain_imap_events` (
					  `ID` int(11) NOT NULL AUTO_INCREMENT,
					  `zDate` datetime NOT NULL,
					  `subject` varchar(255) NOT NULL,
					  `content` longtext NOT NULL,
					  `ou` varchar(255) NOT NULL,
					  `account` varchar(255) NOT NULL,
					  PRIMARY KEY (`ID`),
					  KEY `zDate` (`zDate`),
					  KEY `ou` (`ou`),
					  KEY `account` (`account`))";
			$this->QUERY_SQL($sql,'artica_backup');	
	} 

	
	
	if(!$this->TABLE_EXISTS('emailing_campain_blacklist','artica_backup')){			
			$sql="CREATE TABLE `artica_backup`.`emailing_campain_blacklist` (
			`email` VARCHAR( 255 ) NOT NULL ,
			`reason` VARCHAR( 255 ) NOT NULL ,
			UNIQUE (`email`))";	
			$this->QUERY_SQL($sql,'artica_backup');	
			
	}

	
	
	
	if(!$this->FIELD_EXISTS("emailing_campain_linker","queue_builder_pourc","artica_backup")){
				$sql="ALTER TABLE `emailing_campain_linker` ADD `queue_builder_pourc` INT( 1 ) NOT NULL DEFAULT '0'";
				$this->QUERY_SQL($sql,'artica_backup');
	}	
	
	if(!$this->FIELD_EXISTS("emailing_campain_linker","locked","artica_backup")){
				$sql="ALTER TABLE `emailing_campain_linker` ADD `locked` INT( 1 ) NOT NULL DEFAULT '0'";
				$this->QUERY_SQL($sql,'artica_backup');
	}		
	
	
if(!$this->TABLE_EXISTS('emailing_campain_queues','artica_backup')){			
			$sql="CREATE TABLE `artica_backup`.`emailing_campain_queues` (
					`ID` 				INT( 10 ) NOT NULL AUTO_INCREMENT,
					`body` 				BLOB NOT NULL ,
					`from` 				VARCHAR( 255 ) NOT NULL ,
					`rcpt_to` 			VARCHAR( 255 ) NOT NULL ,
					`msmtp` 			BLOB NOT NULL ,
					`sended` 			INT( 1 ) NOT NULL DEFAULT '0',
					`msg_size` 			INT( 5 ) NOT NULL DEFAULT '0',
					`campain_linker_id` INT( 10 ) NOT NULL ,
					`ou` 		   		VARCHAR( 255 ) NOT NULL ,
					`time_created` 		DATETIME NOT NULL ,
					`time_sended`  		DATETIME NOT NULL ,
					`template_id`  		INT( 10 ) NOT NULL ,
					`contact_id`   		INT( 10 ) NOT NULL ,
					`unique_key`   		VARCHAR( 255 ) NOT NULL,
					 PRIMARY KEY (`ID`),
					 KEY `sended` (`sended`),
					 KEY `rcpt_to` (`rcpt_to`),
					 KEY `ou` (`ou`),
					 INDEX ( `campain_linker_id` , `template_id` , `contact_id` )
					)";	
		$this->QUERY_SQL($sql,'artica_backup');	
	}
	
	
	
	if(!$this->TABLE_EXISTS('emailing_campain_events','artica_backup')){			
			$sql="CREATE TABLE `artica_backup`.`emailing_campain_events` (
				`ID` INT( 5 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`campain_linker_id` INT( 5 ) NOT NULL ,
				`zDate` DATETIME NOT NULL ,
				`time_duration` VARCHAR( 255 ) NOT NULL ,
				`template_id` INT( 5 ) NOT NULL ,
				`database_id` INT( 5 ) NOT NULL ,
				`messages_number` INT( 5 ) NOT NULL ,
				`massmailing_size` INT( 10 ) NOT NULL ,
				`events_details` LONGTEXT NOT NULL,
				`task_success` INT( 1 ) NOT NULL DEFAULT '0',
				 KEY `campain_linker_id` ( `campain_linker_id`),
				 KEY `zDate` (`zDate`))";

				$this->QUERY_SQL($sql,'artica_backup');	
				if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}	
		}
	}

	public function CheckTableEmailingContacts($tablename,$merged=array()){
		if(!$this->TABLE_EXISTS($tablename,'artica_backup')){	
				$sql="CREATE TABLE `artica_backup`.`$tablename` (
				`ID` INT( 10 ) NOT NULL AUTO_INCREMENT ,
				`gender` VARCHAR( 10 ) NOT NULL ,
				`firstname` VARCHAR( 35 ) NOT NULL ,
				`lastname` VARCHAR( 255 ) NOT NULL ,
				`email` VARCHAR( 255 ) NOT NULL ,
				`domain` VARCHAR( 90 ) NOT NULL ,
				`phone` VARCHAR( 65 ) NOT NULL ,
				`city` VARCHAR( 35 ) NOT NULL ,
				`cp` VARCHAR( 10 ) NOT NULL ,
				`postaladdress` VARCHAR( 255 ) NOT NULL ,
				PRIMARY KEY ( `ID` ) ,
			    UNIQUE KEY `email` (`email`),
				KEY `lastname` (`lastname`),
				KEY `domain` (`domain`),
				KEY `city` (`city`),
				KEY `cp` (`cp`)
				)";
				
		if(count($merged)>1){		
			$sql=$sql. " TYPE=MERGE UNION=(".implode(",",$merged).") INSERT_METHOD=LAST";
			$sql=str_replace("UNIQUE KEY","KEY",$sql);
			$sql=str_replace("AUTO_INCREMENT","",$sql);
			$sql=str_replace("PRIMARY","",$sql);
		}		
				
		$this->QUERY_SQL($sql,'artica_events');
		writelogs("$sql",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		
		if(!$this->ok){
			$this->mysql_error=$this->mysql_error."\n".$sql;
			writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		}
		
	}

	public function CheckTableThinClient(){
		if(!$this->TABLE_EXISTS('thinclient_hardware_modules','artica_backup')){		
			$sql="CREATE TABLE `artica_backup`.`thinclient_hardware_modules` (`module` VARCHAR( 60 ) NOT NULL ,UNIQUE (`module`))";
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		}
		if(!$this->TABLE_EXISTS('thinclient_package_modules','artica_backup')){	
			$sql="CREATE TABLE `artica_backup`.`thinclient_package_modules` (`package` VARCHAR( 60 ) NOT NULL ,UNIQUE (`package`))";		
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		}

		
		if(!$this->TABLE_EXISTS('thinclient_computers','artica_backup')){	
			$sql="CREATE TABLE `artica_backup`.`thinclient_computers` (`uid` VARCHAR( 90 ) NOT NULL ,`parameters` LONGTEXT NOT NULL ,UNIQUE (`uid`))";
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		}
		
		
		if(!$this->TABLE_EXISTS('thinclient_compile_logs','artica_backup')){	
			$sql="CREATE TABLE IF NOT EXISTS `thinclient_compile_logs` (
			  `ID` int(11) NOT NULL auto_increment,
			  `zdate` datetime NOT NULL,
			  `event` longtext NOT NULL,
			  `subject` varchar(255) NOT NULL,
			  PRIMARY KEY  (`ID`),
			  KEY `zdate` (`zdate`),
			  KEY `subject` (`subject`))";		
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		}
		
		if(!$this->TABLE_EXISTS('virtualbox_watchdog','artica_backup')){	
			$sql="CREATE TABLE `artica_backup`.`virtualbox_watchdog` (`uuid` VARCHAR( 255 ) NOT NULL ,UNIQUE (`uuid`)) ";		
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		}
		
		if(!$this->TABLE_EXISTS('virtualbox_nat','artica_backup')){	
			$sql="CREATE TABLE `artica_backup`.`virtualbox_nat` (
			`vboxport` INT( 3 ) NOT NULL ,
			`vboxid` VARCHAR( 100 ) NOT NULL ,
			`localport` INT( 3 ) NOT NULL ,
			INDEX ( `vboxid` ) ,
			UNIQUE (
			`localport`
			))";
				
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
			$sql="ALTER TABLE `virtualbox_nat` DROP INDEX `vboxport`";
			$this->QUERY_SQL($sql,'artica_backup');
			$sql="ALTER TABLE `virtualbox_nat` ADD UNIQUE (`localport`)";
			$this->QUERY_SQL($sql,'artica_backup');
		}
		
		
	}

	public function CheckTableIMAP(){
		if(!$this->TABLE_EXISTS('mbx_con','artica_events')){
			$sql="CREATE TABLE IF NOT EXISTS `mbx_con` (
			  `ID` int(100) NOT NULL auto_increment,
			  `zDate` datetime NOT NULL,
			  `mbx_service` varchar(30) NOT NULL,
			  `client_name` varchar(90) NOT NULL,
			  `client_ip` varchar(20) NOT NULL,
			  `uid` varchar(50) NOT NULL,
			  `imap_server` varchar(255) NOT NULL,
			  PRIMARY KEY  (`ID`),
			  KEY `zDate` (`zDate`,`mbx_service`,`client_name`,`client_ip`,`uid`),
			  KEY `imap_server` (`imap_server`)
			)";	
			$this->QUERY_SQL($sql,'artica_events');
		}
		if(!$this->TABLE_EXISTS('mbx_migr_users','artica_backup')){
			$sql="CREATE TABLE `artica_backup`.`mbx_migr_users` (
				`zmd5` VARCHAR( 90 ) NOT NULL ,
				`mbx_migr_id` INT( 10 ) NOT NULL ,
				`ou` VARCHAR( 90 ) NOT NULL ,
				`imap_server` VARCHAR( 255 ) NOT NULL ,
				`usessl` INT( 1 ) NOT NULL DEFAULT '0',
				`username` VARCHAR( 90 ) NOT NULL ,
				`password` VARCHAR( 90 ) NOT NULL ,
				`uid` VARCHAR( 90 ) NOT NULL ,
				`imported` INT( 1 ) NOT NULL DEFAULT '0',
				`mbx_created` INT( 1 ) NOT NULL DEFAULT '0',
				`PID` INT( 5 ) NOT NULL DEFAULT '0',
				`events` LONGTEXT NOT NULL ,
				 KEY `mbx_migr_id` ( `mbx_migr_id` ),
				 KEY `ou` ( `ou` ),
				 KEY `username` ( `username` ),
				 KEY `mbx_created` ( `mbx_created` ),
				 KEY `uid` ( `uid` ),
				 UNIQUE (`zmd5`))";
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){writelogs("$q->mysql_error",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);}
		}			

		
		
		if(!$this->TABLE_EXISTS('mbx_migr','artica_backup')){
			$sql="CREATE TABLE `artica_backup`.`mbx_migr` (
				`ID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`ou` VARCHAR( 90 ) NOT NULL ,
				`filepath` VARCHAR( 255 ) NOT NULL ,
				`imported` INT( 1 ) NOT NULL ,
				`finish` INT( 1 ) NOT NULL ,
				`members_count` INT( 10 ) NOT NULL,
				`local_domain` VARCHAR( 255 ) NOT NULL ,
				 KEY `ou` ( `ou` ),
				 KEY `imported` (`imported`),
				 KEY `local_domain` (`local_domain`),
				 KEY `finish` (`finish`)
				 )";
			$this->QUERY_SQL($sql,'artica_backup'); 
			if(!$this->ok){writelogs("$q->mysql_error",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);}

		}
		
		if(!$this->TABLE_EXISTS('fetchmail_events','artica_events')){
			$sql="CREATE TABLE `artica_events`.`fetchmail_events` (
				`ID` INT( 100 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`zDate` DATETIME NOT NULL ,
				`server` VARCHAR( 128 ) NOT NULL ,
				`account` VARCHAR( 128 ) NOT NULL ,
				`size` INT( 10 ) NOT NULL ,
				INDEX ( `zDate` , `server` , `account` )
				);";
			
			$this->QUERY_SQL($sql,'artica_events');
			if(!$this->ok){writelogs("$q->mysql_error",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);}
		}
		
			if(!$this->TABLE_EXISTS('fetchmail_rules','artica_backup')){
			$sql="CREATE TABLE IF NOT EXISTS `fetchmail_rules` (
				  `ID` int(10) NOT NULL AUTO_INCREMENT,
				  `uid` varchar(255) NOT NULL,
				  `is` varchar(255) NOT NULL,
				  `user` varchar(255) NOT NULL,
				  `pass` varchar(255) NOT NULL,
				  `poll` varchar(255) NOT NULL,
				  `proto` varchar(20) NOT NULL,
				  `keep` int(1) NOT NULL,
				  `enabled` int(1) NOT NULL,
				  `md5` varchar(120) NOT NULL,
				  `tracepolls` int(1) NOT NULL DEFAULT '0',
				  `fetchall` int(1) NOT NULL DEFAULT '0',
				  `ssl` int(1) NOT NULL DEFAULT '0',
				  `sslcertck` int(1) NOT NULL DEFAULT '0',
				  `port` int(5) NOT NULL,
				  `timeout` int(5) NOT NULL,
				  `interval` int(5) NOT NULL,
				  `aka` varchar(255) NOT NULL,
				  `nokeep` int(1) NOT NULL DEFAULT '0',
				  `multidrop` int(1) NOT NULL DEFAULT '0',
				  `dropdelivered` int(1) NOT NULL DEFAULT '0',
				  `smtp_host` VARCHAR( 75 ) NOT NULL DEFAULT '127.0.0.1',
				  `sslfingerprint` VARCHAR( 255 ) NOT NULL,
				  PRIMARY KEY (`ID`),
				  UNIQUE KEY `md5` (`md5`),
				  KEY `smtp_host` (`smtp_host`),
				  KEY `uid` (`uid`)
				) ";
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){writelogs("$q->mysql_error",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);}
		}

		if(!$this->FIELD_EXISTS("fetchmail_rules","nokeep","artica_backup")){
				$sql="ALTER TABLE `fetchmail_rules` ADD `nokeep` INT( 1 ) NOT NULL DEFAULT '0'";
				$this->QUERY_SQL($sql,'artica_backup');
		}
		
		if(!$this->FIELD_EXISTS("fetchmail_rules","sslcertck","artica_backup")){
				$sql="ALTER TABLE `fetchmail_rules` ADD `sslcertck` INT( 1 ) NOT NULL DEFAULT '0'";
				$this->QUERY_SQL($sql,'artica_backup');
		}		
		
		
		if(!$this->FIELD_EXISTS("fetchmail_rules","smtp_host","artica_backup")){
				$sql="ALTER TABLE `fetchmail_rules` ADD `smtp_host` VARCHAR( 75 ) NOT NULL,ADD INDEX ( `smtp_host` )";
				$this->QUERY_SQL($sql,'artica_backup');
		}
		
		if(!$this->FIELD_EXISTS("fetchmail_rules","sslfingerprint","artica_backup")){
			$sql="ALTER TABLE `fetchmail_rules` ADD `sslfingerprint` VARCHAR( 255 ) NOT NULL";
			$this->QUERY_SQL($sql,'artica_backup');
		}	
		
		if(!$this->FIELD_EXISTS("fetchmail_rules","dropdelivered","artica_backup")){
			$sql="ALTER TABLE `fetchmail_rules` ADD `dropdelivered` INT( 1 ) NOT NULL DEFAULT '0'";
			$this->QUERY_SQL($sql,'artica_backup');
		}		

		

		
		
	if(!$this->TABLE_EXISTS('postfix_multi','artica_backup')){
		
			$sql="CREATE TABLE IF NOT EXISTS `postfix_multi` (
				  `ID` int(11) NOT NULL AUTO_INCREMENT,
				  `key` varchar(255) NOT NULL,
				  `value` varchar(255) NOT NULL,
				  `ou` varchar(255) NOT NULL,
				  `ValueTEXT` LONGTEXT NOT NULL,
				  `uuid` VARCHAR( 255 ) NOT NULL ,
				  `ip_address` VARCHAR( 16 ) NOT NULL,
				  PRIMARY KEY (`ID`),
				  UNIQUE KEY `ID` (`ID`),
				  KEY `key` (`key`),
				  KEY `value`(`value`),
				  KEY `ou` (`ou`),
				  KEY `uuid` (`uuid`),
				  KEY `ip_address` (`ip_address`)
				  )";	
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);}
		}
		
		if(!$this->FIELD_EXISTS("postfix_multi","uuid","artica_backup")){
			$sql="ALTER TABLE `postfix_multi` ADD `uuid` VARCHAR( 255 ) NOT NULL ,
			ADD `ip_address` VARCHAR( 16 ) NOT NULL ,
			ADD INDEX ( `uuid` , `ip_address` )";
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);}
		}
		
		if($this->FIELD_TYPE("postfix_multi","ValueTEXT","artica_backup")<>"longtext"){
			$sql="ALTER TABLE `postfix_multi` CHANGE `ValueTEXT` `ValueTEXT` LONGTEXT";
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);}
		}
		
		

		
		if(!$this->TABLE_EXISTS('nics_virtuals','artica_backup')){	
			$sql="CREATE TABLE IF NOT EXISTS `nics_virtuals` (
				  `nic` varchar(20) NOT NULL,
				  `ID` int(11) NOT NULL AUTO_INCREMENT,
				  `org` varchar(255) NOT NULL,
				  `ipaddr` varchar(25) NOT NULL,
				  `netmask` varchar(25) NOT NULL,
				  `cdir` varchar(30) NOT NULL,
				  `gateway` varchar(30) NOT NULL,
				  `openvpn_nic` INT( 1 ) NOT NULL,
				  PRIMARY KEY (`ID`),
				  KEY `nic` (`nic`),
				  KEY `org` (`org`,`ipaddr`,`netmask`),
				  KEY `cdir` (`cdir`)
				)";
			$this->QUERY_SQL($sql,'artica_backup');
			}
			
			 
			
			if(!$this->FIELD_EXISTS("nics_virtuals","gateway","artica_backup")){
				$sql="ALTER TABLE `nics_virtuals` ADD `gateway` VARCHAR( 30 ) NOT NULL";
				$this->QUERY_SQL($sql,'artica_backup');
			}	
			
			if(!$this->FIELD_EXISTS("nics_virtuals","openvpn_nic","artica_backup")){
				$sql="ALTER TABLE `nics_virtuals` ADD `openvpn_nic` INT( 1 ) NOT NULL ";
				$this->QUERY_SQL($sql,'artica_backup');
			}				

			if(!$this->INDEX_EXISTS("nics_virtuals","cdir_2","artica_backup")){
				$sql="ALTER TABLE `nics_virtuals` DROP INDEX `cdir_2` ";
				$this->QUERY_SQL($sql,'artica_backup');
			}

		if(!$this->TABLE_EXISTS('vpn_events','artica_events')){		
				$sql="CREATE TABLE `artica_events`.`vpn_events` (
				`stime` BIGINT( 100 ) NOT NULL ,
				`subject` VARCHAR( 255 ) NOT NULL ,
				`text` LONGTEXT NOT NULL ,
				`IPPARAM` VARCHAR( 255 ) NOT NULL ,
				INDEX ( `IPPARAM` ) ,
				UNIQUE (`stime`)
				)";			
				$this->QUERY_SQL($sql,'artica_events');	
				if(!$this->ok){
					writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
					writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				}
		}	
	}

	public function CheckTableEvents(){
		if(!$this->TABLE_EXISTS('auth_events','artica_events')){	
			$sql="CREATE TABLE IF NOT EXISTS `auth_events` (
				  `ID` int(100) NOT NULL AUTO_INCREMENT,
				  `ipaddr` varchar(90) NOT NULL,
				  `hostname` varchar(255) NOT NULL,
				  `Country` varchar(50) NOT NULL,
				  `success` int(1) NOT NULL DEFAULT '1',
				  `uid` varchar(128) NOT NULL,
				  `zDate` datetime NOT NULL,
				  PRIMARY KEY (`ID`),
				  KEY `ipaddr` (`ipaddr`,`success`,`zDate`),
				  KEY `hostname` (`hostname`),
				  KEY `Country` (`Country`)
				  
				  )";
			$this->QUERY_SQL($sql,'artica_events');
		}
		
		
		
		if(!$this->TABLE_EXISTS('admin_cnx','artica_events')){	
			$sql="CREATE TABLE IF NOT EXISTS `admin_cnx` (
				  `connected` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				  `session_id` varchar(255) NOT NULL,
				  `ipaddr` varchar(90) NOT NULL,
				  `InterfaceType` varchar(50) NOT NULL,
				  `webserver` varchar(255) NOT NULL,
				  `hostname` varchar(255) NOT NULL,
				  `uid` varchar(255) NOT NULL,
				  UNIQUE KEY `session_id` (`session_id`),
				  KEY `connected` (`connected`,`ipaddr`),
				  KEY `InterfaceType` (`InterfaceType`),
				  KEY `hostname` (`hostname`),
				  KEY `uid` (`uid`)
				) ";
			$this->QUERY_SQL($sql,'artica_events');
		}		
		
		if(!$this->FIELD_EXISTS("auth_events","Country","artica_events")){
				$sql="ALTER TABLE `auth_events` ADD `Country` VARCHAR( 50 ) NOT NULL,ADD INDEX ( Country )";
				$this->QUERY_SQL($sql,'artica_events');
				if(!$this->ok){
					writelogs("Fatal $sql",__FUNCTION__,__FILE__,__LINE__);
					writelogs("Fatal $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				}
		}		
	
	
		if(!$this->TABLE_EXISTS('events','artica_events')){	
			$sql="CREATE TABLE IF NOT EXISTS `events` (
			  	  `ID` int(100) NOT NULL AUTO_INCREMENT,
				  `zDate` datetime NOT NULL,
				  `event_id` int(100) NOT NULL,
				  `event_type` int(11) NOT NULL,
				  `hostname` varchar(75) NOT NULL,
				  `process` varchar(75) NOT NULL,
				  `text` varchar(255) NOT NULL,
				  `context` varchar(75) NOT NULL DEFAULT 'unknown',
				  `sended` int(1) NOT NULL DEFAULT '0',
				  `content` text NOT NULL,
				  `attached_files` TEXT NOT NULL,
				  `recipient` VARCHAR( 255 ) NULL,
				  PRIMARY KEY (`ID`),
				  KEY `zDate` (`zDate`,`event_id`,`event_type`,`process`,`hostname`),
				  KEY `context` (`context`),
				  KEY `sended` (`sended`))";
			$this->QUERY_SQL($sql,'artica_events');
		}
		
		
		if(!$this->TABLE_EXISTS('debian_mirror_events','artica_events')){	
			$sql="CREATE TABLE `artica_events`.`debian_mirror_events` (`ID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,`zDate` DATETIME NOT NULL ,`subject` VARCHAR( 255 ) NOT NULL ,`text` LONGTEXT NOT NULL ,INDEX ( `zDate` ))";
			$this->QUERY_SQL($sql,'artica_events');
				if(!$this->ok){
					writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
					writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				}	
		}		
		
		
		
		
		if(!$this->FIELD_EXISTS("events","context","artica_events")){
			 	writelogs("create events.context",__FUNCTION__,__FILE__,__LINE__);
				$sql="ALTER TABLE `events` ADD `context` VARCHAR( 75 ) NOT NULL DEFAULT 'unknown',ADD INDEX ( context )";
				$this->QUERY_SQL($sql,'artica_events');
				if(!$this->ok){
					writelogs("$sql",__FUNCTION__,__FILE__,__LINE__);
					writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				}
		}
		
		if(!$this->FIELD_EXISTS("events","recipient","artica_events")){
				writelogs("create events.recipient",__FUNCTION__,__FILE__,__LINE__);
				$sql="ALTER TABLE `events` ADD `recipient` VARCHAR( 255 ) NOT NULL";
				$this->QUERY_SQL($sql,'artica_events');
				if(!$this->ok){
					writelogs("$sql",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
					writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				}				
		}		

		if(!$this->FIELD_EXISTS("events","sended","artica_events")){
				$sql="ALTER TABLE `events` ADD `sended` INT( 1 ) NOT NULL DEFAULT '0',ADD INDEX ( sended )";
				$this->QUERY_SQL($sql,'artica_events');
			}
			
			if(!$this->FIELD_EXISTS("events","attached_files","artica_events")){
				$sql="ALTER TABLE `events` ADD `attached_files` TEXT NOT NULL ";
				$this->QUERY_SQL($sql,'artica_events');
			}

			if(!$this->FIELD_EXISTS("events","sended","artica_events")){
				$sql="ALTER TABLE `events` ADD `sended` INT( 1 ) NOT NULL DEFAULT '0',ADD INDEX ( sended )";
				$this->QUERY_SQL($sql,'artica_events');
			}	
		if(!$this->TABLE_EXISTS('cyrus_backup_events','artica_events')){	
			$sql="CREATE TABLE `artica_events`.`cyrus_backup_events` (
					`ID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
					`zDate` DATETIME NOT NULL ,
					`local_ressource` VARCHAR( 50 ) NOT NULL ,
					`events` TINYTEXT NOT NULL ,
					`success` INT( 1 ) NOT NULL DEFAULT '0',
					INDEX ( `local_ressource` , `success` )
					) ";
			 
			$this->QUERY_SQL($sql,'artica_events');
			}
	
	if(!$this->FIELD_EXISTS("cyrus_backup_events","remote_ressource","artica_events")){
		$sql="ALTER TABLE `cyrus_backup_events` ADD `remote_ressource` VARCHAR( 125 ) NOT NULL ,ADD INDEX ( remote_ressource )";
		$this->QUERY_SQL($sql,'artica_events');
	}
	
	if(!$this->TABLE_EXISTS('backup_events','artica_events')){	
			$sql="CREATE TABLE `artica_events`.`backup_events` (
					`ID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
					`task_id` INT( 10 ) NOT NULL ,
					`zdate` DATETIME NOT NULL ,
					`backup_source` VARCHAR( 255 ) NOT NULL ,
					`event` MEDIUMTEXT NOT NULL ,
					INDEX ( `task_id` , `zdate` , `backup_source` ))";
			 
			$this->QUERY_SQL($sql,'artica_events');
			}	
			
	if(!$this->TABLE_EXISTS('quarantine_report_users','artica_backup')){				
		$sql="CREATE TABLE IF NOT EXISTS `quarantine_report_users` (
			  `userid` varchar(255) NOT NULL,
			  `MaxDays` int(1) NOT NULL,
			  `hour_execution` int(2) NOT NULL,
			  `min_execution` int(2) NOT NULL,
			  `type` int(1) NOT NULL,
			  `enabled` int(1) NOT NULL,
			  `parameters` TEXT NOT NULL,
			  PRIMARY KEY (`userid`),
			  KEY `enabled` (`enabled`),
			  KEY `type` (`type`))";
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){writelogs("quarantine_report_users:: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		}
	}

	
	public function CheckOutOfOfficeTable(){
		$sql="CREATE TABLE `artica_events`.`OutOfOffice` (
			`zMD5` VARCHAR( 100 ) NOT NULL ,
			`uid` VARCHAR( 90 ) NOT NULL ,
			`zDate` DATETIME NOT NULL ,
			`mailfrom` VARCHAR( 90 ) NOT NULL ,
			PRIMARY KEY ( `zMD5` ) ,
			INDEX ( `uid` , `mailfrom` )
			) ";
		
		if(!$this->TABLE_EXISTS('OutOfOffice','artica_events')){$this->QUERY_SQL($sql,'artica_events');}	
	}

	public function CheckTables_gluster(){
		if(!$this->TABLE_EXISTS('glusters_clients','artica_backup')){				
		$sql="CREATE TABLE IF NOT EXISTS `glusters_clients` (
			  `ID` int(11) NOT NULL AUTO_INCREMENT,
			  `client_ip` varchar(90) NOT NULL,
			  `client_port` int(3) NOT NULL,
			  `client_notified` int(1) NOT NULL,
			  `parameters` text NOT NULL,
			  `NotifToDelete` int(11) NOT NULL DEFAULT '0',
			  PRIMARY KEY (`ID`),
			  UNIQUE KEY `client_ip` (`client_ip`),
			  KEY `client_notified` (`client_notified`),
			  KEY `NotifToDelete` (`NotifToDelete`)
			) ";
		
		$this->QUERY_SQL($sql,'artica_backup');
		if(!$this->ok){writelogs("glusters_clients:: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		}
		
	if(!$this->FIELD_EXISTS("glusters_clients","NotifToDelete","artica_backup")){
		$sql="ALTER TABLE `glusters_clients` ADD `NotifToDelete` int(11) NOT NULL DEFAULT '0' ,ADD INDEX ( NotifToDelete )";
		$this->QUERY_SQL($sql,'artica_backup');
		if(!$this->ok){writelogs("glusters_clients.NotifToDelete:: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
	}
			
		
		
	if(!$this->TABLE_EXISTS('glusters_servers','artica_backup')){				
		$sql="CREATE TABLE IF NOT EXISTS `glusters_servers` (
		  `ID` int(11) NOT NULL AUTO_INCREMENT,
		  `server_ip` varchar(90) NOT NULL,
		  `server_port` int(3) NOT NULL,
		  `parameters` text NOT NULL,
		  PRIMARY KEY (`ID`),
		  UNIQUE KEY `server_ip` (`server_ip`))";
		$this->QUERY_SQL($sql,'artica_backup');
		if(!$this->ok){writelogs("glusters_servers:: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		}
		
		
	if(!$this->TABLE_EXISTS('gluster_paths','artica_backup')){				
			$sql="CREATE TABLE IF NOT EXISTS `gluster_paths` (
				  `ID` int(10) NOT NULL AUTO_INCREMENT,
				  `cluster_path` varchar(255) NOT NULL,
				  `zmd` varchar(90) NOT NULL,
				  PRIMARY KEY (`ID`),
				  UNIQUE KEY `cluster_path` (`cluster_path`),
				  UNIQUE KEY `zmd` (`zmd`))";		
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){writelogs("gluster_paths:: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		}
		
		if(!$this->TABLE_EXISTS('gluster_clients_brick','artica_backup')){				
			$sql="CREATE TABLE IF NOT EXISTS `gluster_clients_brick` (
				`brickname` VARCHAR( 20 ) NOT NULL ,
				`source` VARCHAR( 255 ) NOT NULL ,INDEX ( `source` ) ,UNIQUE (`brickname`))";		
				$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){writelogs("gluster_clients_brick:: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		}		

	}

	function BuildTables(){
		if(!$this->UseMysql){
			$q=new lib_sqlite();
			$q->CheckDatabases();
			return;
		}
		 if(!$this->DATABASE_EXISTS('artica_events')){if(!$this->CREATE_DATABASE('artica_events')){return null;}}
		 if(!$this->DATABASE_EXISTS('artica_backup')){if(!$this->CREATE_DATABASE('artica_backup')){return null;}}	
		 $squidTBLS=new mysql_squid_builder();	
		 $this->Check_quarantine_table();
		 $this->Check_iptables_table();
		 $this->check_storage_table();
		 $this->Check_smtp_logs_table();
		 $this->CheckTables_rsync();
		 $this->CheckTables_dar();
		 $this->CheckTableIMAP();
		 $this->CheckTableVirus();
		 $this->CheckTableEvents();	
		 $this->CheckTable_dansguardian();	
		 $this->CheckTables_repositories();
		 $this->CheckTables_openvpn();
		 $this->CheckOutOfOfficeTable();
		 $this->checkAgentTables();
		 $this->CheckTablesSquid();
		 $this->CheckTablesPostfilter();
		 $this->check_emailing_tables();
		 $this->Check_reports_table();
		 $this->Check_mldonkey_table();
		 $this->CheckTables_gluster();
		 $this->CheckTableWebsites();
		 $this->CheckTableThinClient();
		 $this->CheckTables_qos();
		 $this->CheckTablesAwstats();
		 $this->check_postfwd2_table();
		 $this->check_vps_tables();
		 $this->check_networks_tables();
		 $this->checkTablesSamba();
		if(!$this->FIELD_EXISTS("smtp_logs_day","emails","artica_events")){
			$sql="ALTER TABLE `smtp_logs_day` ADD `emails` int(10) NOT NULL ";
			$this->QUERY_SQL($sql,'artica_events');
		}	
	
	if(!$this->TABLE_EXISTS('smtp_logs_day','artica_events')){		
			$sql="CREATE TABLE `artica_events`.`smtp_logs_day` (
				`key` VARCHAR( 75 ) NOT NULL ,
				`day` DATE NOT NULL ,
				`delivery_domain` VARCHAR( 80 ) NOT NULL ,
				`bounce_error` VARCHAR( 80 ) NOT NULL ,
				`emails` int(10) NOT NULL,
				PRIMARY KEY ( `key` ) ,
				INDEX ( `day` , `delivery_domain` , `bounce_error` )
				)";
						
				 $this->QUERY_SQL($sql,'artica_events');
		}	
	
	
		//ALTER TABLE `quarantine` CHANGE `file_path` `file_path` VARCHAR( 255 ) NOT NULL 
		
		if(!$this->TABLE_EXISTS('spam_events','artica_events')){
			
			$sql="CREATE TABLE IF NOT EXISTS `spam_events` (
					  `msgid` varchar(90) NOT NULL,
					  `zDate` datetime NOT NULL,
					  `mailfrom` varchar(255) NOT NULL,
					  `rcpt_to` varchar(255) NOT NULL,
					  `filter` int(11) NOT NULL,
					  `ipfrom` varchar(25) NOT NULL,
					  `mailfrom_domain` varchar(255) NOT NULL,
					  `rcpt_to_domain` varchar(255) NOT NULL,
					  `spam_level` varchar(50) NOT NULL,
					  PRIMARY KEY  (`msgid`),
					  KEY `mailfrom` (`mailfrom`),
					  KEY `rcpt_to` (`rcpt_to`),
					  KEY `filter` (`filter`),
					  KEY `zDate` (`zDate`),
					  KEY `ipfrom` (`ipfrom`),
					  KEY `mailfrom_domain` (`mailfrom_domain`),
					  KEY `rcpt_to_domain` (`rcpt_to_domain`),
					  KEY `spam_level` (`spam_level`)
					)";
						
				 $this->QUERY_SQL($sql,'artica_events');
		}	
		
			
		if(!$this->DATABASE_EXISTS('artica_backup')){if(!$this->CREATE_DATABASE('artica_backup')){return null;}}		

		if(!$this->TABLE_EXISTS('imapsync','artica_backup')){
			if($GLOBALS["DEBUG"]){writelogs("Table imapsync does not exists....",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);}
			$sql="CREATE TABLE IF NOT EXISTS `imapsync` (
			  `ID` int(11) NOT NULL AUTO_INCREMENT,
			  `uid` varchar(255) NOT NULL,
			  `imap_server` varchar(255) NOT NULL,
			  `username` varchar(255) NOT NULL,
			  `password` varchar(255) NOT NULL,
			  `ssl` int(1) NOT NULL DEFAULT '0',
			  `enabled` int(1) NOT NULL,
			  `state` int(1) NOT NULL,
			  `state_event` longtext NOT NULL,
			  `pid` int(5) NOT NULL,
			  `zDate` datetime NOT NULL,
			  `CronSchedule` varchar(90) NOT NULL,
			  `parameters` text NOT NULL,
			  `folders` text NOT NULL,
			  PRIMARY KEY (`ID`),
			  KEY `uid` (`uid`),
			  KEY `imap_server` (`imap_server`)
			)";
			$this->QUERY_SQL($sql,'artica_backup');
			if($GLOBALS["DEBUG"]){writelogs("$sql",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);}
			if(!$this->ok){echo "$sql\n$this->mysql_error\n";}else{writelogs("SUCCESS!",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);}
		}
		
		
	if(!$this->FIELD_EXISTS("imapsync","parameters","artica_backup")){
		$sql="ALTER TABLE `imapsync` ADD `parameters` text NOT NULL";
		$this->QUERY_SQL($sql,"artica_backup");
	}		
		
		if(!$this->FIELD_EXISTS("imapsync","folders","artica_backup")){
		$sql="ALTER TABLE `imapsync` ADD `folders` text NOT NULL";
		$this->QUERY_SQL($sql,"artica_backup");
	}
		
		if(!$this->TABLE_EXISTS('dspam_stats','artica_events')){	
		$sql="create table dspam_stats (
				  uid smallint unsigned primary key,
				  spam_learned int not null,
				  innocent_learned int not null,
				  spam_misclassified int not null,
				  innocent_misclassified int not null,
				  spam_corpusfed int not null,
				  innocent_corpusfed int not null,
				  spam_classified int not null,
				  innocent_classified int not null
				) type=MyISAM;";
		
		$this->QUERY_SQL($sql,'artica_events');
		}
		
		if(!$this->TABLE_EXISTS('dspam_token_data','artica_events')){	
		$sql="create table dspam_token_data (
			  uid smallint unsigned not null,
			  token bigint unsigned not null,
			  spam_hits int not null,
			  innocent_hits int not null,
			  last_hit date not null
			) type=MyISAM PACK_KEYS=1;";
		$this->QUERY_SQL($sql,'artica_events');
		}
		
		if(!$this->TABLE_EXISTS('dspam_signature_data','artica_events')){	
		$sql="create table dspam_signature_data (
			  uid smallint unsigned not null,
			  signature char(32) not null,
			  data blob not null,
			  length smallint not null,
			  created_on date not null
			) type=MyISAM max_rows=2500000 avg_row_length=8096;";	
		$this->QUERY_SQL($sql,'artica_events');
		}	
		
		
		if(!$this->TABLE_EXISTS('smtp_logs','artica_events')){	
				$sql="CREATE TABLE IF NOT EXISTS `smtp_logs` (
				  `id` bigint(100) NOT NULL auto_increment,
				  `newmsg_id` bigint(20) default NULL,
				  `bounce_qp` int(11) default NULL,
				  `bytes` int(11) NOT NULL default '0',
				  `sender_user` varchar(128) NOT NULL default '',
				  `sender_domain` varchar(128) NOT NULL default '',
				  `delivery_id` bigint(20) default NULL,
				  `delivery_user` varchar(128) NOT NULL default '',
				  `delivery_domain` varchar(128) NOT NULL default '',
				  `delivery_success` enum('yes','no') NOT NULL default 'no',
				  `time_stamp` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
				  `delivery_id_text` varchar(128) NOT NULL,
				  `msg_id_text` varchar(128) NOT NULL,
				  `time_connect` datetime NOT NULL,
				  `time_sended` datetime NOT NULL,
				  `bounce_error` varchar(255) NOT NULL,
				  `SPAM` int(11) NOT NULL,
				  `filter_reject` varchar(255) NOT NULL,
				  `time_amavis` datetime NOT NULL,
				  `Country` varchar(25) NOT NULL,
				  `Region` varchar(30) default NULL,
				  `kas` int(3) NOT NULL default '0',
				  `infected` int(1) NOT NULL default '0',
				  `spammy` int(1) NOT NULL default '0',
				  `blacklisted` int(1) NOT NULL default '0',
				  `whitelisted` int(1) NOT NULL default '0',
				  `smtp_sender` varchar(55) default NULL,
				  PRIMARY KEY  (`id`),
				  UNIQUE KEY `bounce_qp` (`bounce_qp`),
				  UNIQUE KEY `newmsg_id` (`newmsg_id`),
				  KEY `sender_domain` (`sender_domain`),
				  KEY `delivery_domain` (`delivery_domain`),
				  KEY `delivery_id_text` (`delivery_id_text`),
				  KEY `msg_id_text` (`msg_id_text`),
				  KEY `time_connect` (`time_connect`),
				  KEY `time_sended` (`time_sended`),
				  KEY `SPAM` (`SPAM`),
				  KEY `filter_reject` (`filter_reject`),
				  KEY `time_amavis` (`time_amavis`),
				  KEY `Country` (`Country`),
				  KEY `Region` (`Region`),
				  KEY `kas` (`kas`),
				  KEY `infected` (`infected`),
				  KEY `spammy` (`spammy`),
				  KEY `blacklisted` (`blacklisted`),
				  KEY `whitelisted` (`whitelisted`),
				  KEY `smtp_sender` (`smtp_sender`)
				) ";	
				$this->QUERY_SQL($sql,'artica_events');
		}else{
			$this->Check_smtp_logs_table();		
		}

		if(!$this->TABLE_EXISTS('dspam_preferences','artica_events')){	
		$sql="
			CREATE TABLE IF NOT EXISTS `dspam_preferences` (
			  `uid` smallint(5) unsigned NOT NULL,
			  `preference` varchar(32) NOT NULL,
			  `value` varchar(64) NOT NULL,
			  UNIQUE KEY `id_preferences_01` (`uid`,`preference`)
			)";
		$this->QUERY_SQL($sql,'artica_events');
		}
		
		if(!$this->TABLE_EXISTS('dspam_virtual_uids','artica_events')){	
		$sql="create table dspam_virtual_uids (
			  uid smallint unsigned primary key AUTO_INCREMENT,
			  username varchar(128)
			)";
		$this->QUERY_SQL($sql,'artica_events');
		}		
				
		

	
	if(!$this->TABLE_EXISTS('storage','artica_backup')){		
	$sql="CREATE TABLE IF NOT EXISTS `storage` (
			  `MessageID` varchar(80) NOT NULL,
			  `zDate` datetime NOT NULL,
			  `mailfrom` varchar(255) NOT NULL,
			  `subject` varchar(255) NOT NULL,
			  `MessageBody` text NOT NULL,
			  `organization` varchar(80) NOT NULL,
			  `uid` varchar(50) NOT NULL,
			  `file_path` varchar(255) default NULL,
			  `original_messageid` varchar(80) NOT NULL,
			  `message_size` int(10) NOT NULL,
			  `mailto` varchar(255) NOT NULL,
			  `mailfrom_domain` varchar(255) NOT NULL,
			  `commandline` varchar(200) NOT NULL,
			  `ExecuteAfter` varchar(200) NOT NULL,
			  `BinMessg` longblob NOT NULL,
			  `filename` varchar(128) NOT NULL,
			  `filesize` int(10) NOT NULL,
			  PRIMARY KEY  (`MessageID`),
			  KEY `MessageID` (`MessageID`),
			  KEY `mailfrom` (`mailfrom`),
			  KEY `organization` (`organization`),
			  KEY `uid` (`uid`),
			  KEY `file_path` (`file_path`),
			  KEY `original_messageid` (`original_messageid`),
			  KEY `mailto` (`mailto`),
			  KEY `mailfrom_domain` (`mailfrom_domain`),
			  KEY `filename` (`filename`),
			  FULLTEXT KEY `MessageBody` (`MessageBody`)
			) ";
		$this->QUERY_SQL($sql,'artica_backup');
	}
	
	
	
	if(!$this->TABLE_EXISTS('storage_recipients','artica_backup')){	
			$sql="CREATE TABLE IF NOT EXISTS `storage_recipients` (
				  `id` int(10) NOT NULL auto_increment,
				  `MessageID` varchar(80) NOT NULL,
				  `recipient` varchar(255) NOT NULL,
				  `recipient_domain` varchar(255) NOT NULL,
				  	PRIMARY KEY  (`id`),
				  	KEY `MessageID` (`MessageID`),
				  	KEY `recipient` (`recipient`),
				  	KEY `recipient_domain` (`recipient_domain`)
				);";
	
			$this->QUERY_SQL($sql,'artica_backup');
	}
	
	
	
	
	
	if(!$this->TABLE_EXISTS('orgmails','artica_backup')){	
			$sql="CREATE TABLE IF NOT EXISTS `orgmails` (
			  `MessageID` varchar(255) NOT NULL,
			  `message_path` varchar(255) NOT NULL,
			  `MessageSize` int(20) NOT NULL,
			  PRIMARY KEY  (`MessageID`),
			  UNIQUE KEY `message_path` (`message_path`)
			) ;";
	
			$this->QUERY_SQL($sql,'artica_backup');
	}	
	
	if(!$this->TABLE_EXISTS('artica_whitelist','artica_backup')){	
			$sql=" CREATE TABLE `artica_backup`.`artica_whitelist` (
						`zMD5` VARCHAR( 90 ) NOT NULL ,
						`mailfrom` VARCHAR( 255 ) NOT NULL ,
						`mailto` VARCHAR( 255 ) NOT NULL ,
						`zDate` DATETIME NOT NULL ,
						PRIMARY KEY ( `zMD5` ) ,
						KEY `mailfrom` (`mailfrom`),
						KEY `mailto` (`mailto`),
						INDEX ( `zDate` ))";
			$this->QUERY_SQL($sql,'artica_backup');
	
 			}
 			
 			
	if(!$this->TABLE_EXISTS('notify','artica_events')){	
			$sql="CREATE TABLE IF NOT EXISTS `notify` (
				  `daemon` varchar(20) NOT NULL,
				  `event_text` varchar(255) NOT NULL,
				  `error_id` int(2) NOT NULL,
				  `zMD5` varchar(80) NOT NULL,
				  PRIMARY KEY  (`zMD5`),
				  KEY `daemon` (`daemon`,`error_id`)
				); ";
			$this->QUERY_SQL($sql,'artica_events');
			}
			
	if(!$this->TABLE_EXISTS('audit2','artica_events')){			
		$sql="CREATE TABLE IF NOT EXISTS `audit2` (
			  `id` int(100) unsigned NOT NULL auto_increment,
			  `log_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
			  `username` varchar(18) default '',
			  `useraddr` varchar(18) default '',
			  `op_name` varchar(255) default '',
			  `err_msg` text,
			  `op_msg` text,
			  `share` varchar(255) default '',
			  `cnt` smallint(6) default '1',
			  PRIMARY KEY  (`id`)
			)";
		$this->QUERY_SQL($sql,'artica_events');
		}
		
		

		
		
		
if(!$this->TABLE_EXISTS('sys_events','artica_events')){			
		$sql="CREATE TABLE IF NOT EXISTS `sys_events` (
				  `md5` varchar(90) NOT NULL,
				  `ID` int(20) NOT NULL auto_increment,
				  `zDate` datetime NOT NULL,
				  `type` int(1) NOT NULL,
				  `event_text` varchar(255) NOT NULL,
				  `daemon` varchar(20) NOT NULL,
				  `msg_id` varchar(20) default NULL,
				  PRIMARY KEY  (`md5`,`ID`),
				  KEY `type` (`type`,`daemon`),
				  KEY `msg_id` (`msg_id`)
				);";
		$this->QUERY_SQL($sql,'artica_events');
		}				
		
	if(!$this->TABLE_EXISTS('what2','artica_events')){			
		$sql="CREATE TABLE what2 (
			  what varchar(50) NOT NULL,
			  visible tinyint(1) default '0',
			  description varchar(255) default '',
			  PRIMARY KEY  (what)
			);";
		
		$this->QUERY_SQL($sql,'artica_events');
		
		$sql="INSERT INTO `what2` (`what`, `visible`, `description`) VALUES ('connect', 1, ''),
				('disconnect', 1, ''),
				('disk_free', 0, ''),
				('get_quota', 0, ''),
				('set_quota', 0, ''),
				('get_shadow_copy_data', 0, ''),
				('statvfs', 0, ''),
				('opendir', 1, ''),
				('readdir', 0, ''),
				('seekdir', 0, ''),
				('telldir', 0, ''),
				('rewinddir', 0, ''),
				('mkdir', 1, ''),
				('rmdir', 1, ''),
				('closedir', 1, ''),
				('open', 1, ''),
				('close', 1, ''),
				('read', 1, ''),
				('pread', 0, ''),
				('write', 1, ''),
				('pwrite', 0, ''),
				('lseek', 0, ''),
				('rename', 1, ''),
				('fsync', 0, ''),
				('stat', 0, ''),
				('fstat', 0, ''),
				('lstat', 0, ''),
				('unlink', 1, ''),
				('chmod', 1, ''),
				('fchmod', 1, ''),
				('chown', 0, ''),
				('fchown', 0, ''),
				('chdir', 1, ''),
				('getwd', 0, ''),
				('utime', 1, ''),
				('ftruncate', 0, ''),
				('lock', 0, ''),
				('symlink', 0, ''),
				('readlink', 0, ''),
				('link', 0, ''),
				('mknod', 0, ''),
				('realpath', 0, ''),
				('fget_nt_acl', 0, ''),
				('get_nt_acl', 0, ''),
				('fset_nt_acl', 0, ''),
				('set_nt_acl', 0, ''),
				('chmod_acl', 1, ''),
				('fchmod_acl', 1, ''),
				('sys_acl_get_entry', 0, ''),
				('sys_acl_get_tag_type', 0, ''),
				('sys_acl_get_permset', 0, ''),
				('sys_acl_get_qualifier', 0, ''),
				('sys_acl_get_file', 0, ''),
				('sys_acl_get_fd', 0, ''),
				('sys_acl_clear_perms', 0, ''),
				('sys_acl_add_perm', 0, ''),
				('sys_acl_to_text', 0, ''),
				('sys_acl_init', 0, ''),
				('sys_acl_create_entry', 0, ''),
				('sys_acl_set_tag_type', 0, ''),
				('sys_acl_set_qualifier', 0, ''),
				('sys_acl_set_permset', 0, ''),
				('sys_acl_valid', 0, ''),
				('sys_acl_set_file', 0, ''),
				('sys_acl_set_fd', 0, ''),
				('sys_acl_delete_def_file', 0, ''),
				('sys_acl_get_perm', 0, ''),
				('sys_acl_free_text', 0, ''),
				('sys_acl_free_acl', 0, ''),
				('sys_acl_free_qualifier', 0, ''),
				('getxattr', 0, ''),
				('lgetxattr', 0, ''),
				('fgetxattr', 0, ''),
				('listxattr', 0, ''),
				('llistxattr', 0, ''),
				('flistxattr', 0, ''),
				('removexattr', 0, ''),
				('lremovexattr', 0, ''),
				('fremovexattr', 0, ''),
				('setxattr', 0, ''),
				('lsetxattr', 0, ''),
				('fsetxattr', 0, ''),
				('aio_read', 0, ''),
				('aio_write', 0, ''),
				('aio_return', 0, ''),
				('aio_cancel', 0, ''),
				('aio_error', 0, ''),
				('aio_fsync', 0, ''),
				('aio_suspend', 0, '');";
		$this->QUERY_SQL($sql,'artica_events');
		
	}
	
	
	if(!$this->TABLE_EXISTS('shares','artica_events')){			
		$sql="CREATE TABLE `shares` (
			  `id` int(11) NOT NULL auto_increment,
			  `share` varchar(255) NOT NULL,
			  UNIQUE KEY `id` (`id`)
			) AUTO_INCREMENT=1 ;";
		$this->QUERY_SQL($sql,'artica_events');
	}
	
	if(!$this->TABLE_EXISTS('dansguardian_sites','artica_events')){
		$sql="CREATE TABLE `dansguardian_sites` (
				`website_md5` VARCHAR( 90 ) NOT NULL ,
				`website` VARCHAR( 255 ) NOT NULL ,
				UNIQUE (`website_md5`)
				)";
		
		$this->QUERY_SQL($sql,'artica_events');
		
	}
	
	if(!$this->TABLE_EXISTS('dansguardian_sitesinfos','artica_backup')){
		$sql="CREATE TABLE IF NOT EXISTS `dansguardian_sitesinfos` (
		  `website` varchar(255) NOT NULL,
		  `category` varchar(255) NOT NULL,
		  `community` int(1) NOT NULL DEFAULT '0',
		  `squidguard` int(1) NOT NULL DEFAULT '0',
		  `dbpath` varchar(255) NOT NULL,
		  `country` varchar(255) NOT NULL DEFAULT 'unknown',
		  `ipaddr` varchar(40) NOT NULL,
		  UNIQUE KEY `website` (`website`),
		  KEY `dbpath` (`dbpath`),
		  KEY `category` (`category`),
		  KEY `community` (`community`),
		  KEY `country` (`country`),
		  KEY `ipaddr` (`ipaddr`),
		  KEY `squidguard` (`squidguard`))";

		$this->QUERY_SQL($sql,'artica_backup');
		if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__LINE__);}
	}
	
	if(!$this->TABLE_EXISTS('dansguardian_uris','artica_events')){
				$sql="CREATE TABLE IF NOT EXISTS `dansguardian_uris` (
			  `uri_MD5` varchar(90) NOT NULL,
			  `uri` varchar(255) NOT NULL,
			  `site_md5` varchar(90) NOT NULL,
			  UNIQUE KEY `uri_MD5` (`uri_MD5`),
			  KEY `site_md5` (`site_md5`)
			) ";
			$this->QUERY_SQL($sql,'artica_events');
		}

		
	
	if(!$this->TABLE_EXISTS("mails_events","artica_events")){
		$sql="CREATE TABLE IF NOT EXISTS `mails_events` (
			  `ID` int(100) NOT NULL auto_increment,
			  `mailfrom` varchar(255) NOT NULL,
			  `rcpt_to` varchar(255) NOT NULL,
			  `zDate` datetime NOT NULL,
			  `mailfrom_domain` varchar(255) NOT NULL,
			  `rcpt_to_domain` varchar(255) default NULL,
			  `relayhost` varchar(20) NOT NULL,
			  `Country` varchar(75) NOT NULL,
			  `Region` varchar(55) NOT NULL,
			  PRIMARY KEY  (`ID`),
			  KEY `rcpt_to_domain` (`rcpt_to_domain`),
			  KEY `rcpt_to` (`rcpt_to`),
			  KEY `zDate` (`zDate`),
			  KEY `mailfrom_domain` (`mailfrom_domain`),
			  KEY `relayhost` (`relayhost`),
			  KEY `Country` (`Country`),
			  KEY `Region` (`Region`)
				) AUTO_INCREMENT=1 ;";
		$this->QUERY_SQL($sql,'artica_events');
	}
	
	if(!$this->FIELD_EXISTS("mails_events","mailto","artica_events")){	
		$sql="ALTER TABLE `mails_events` ADD `mailto` VARCHAR( 255 ) NOT NULL ,ADD INDEX ( mailto )";	
		$this->QUERY_SQL($sql,'artica_events');
	}
	if(!$this->FIELD_EXISTS("mails_events","Country","artica_events")){
		$sql="ALTER TABLE `mails_events` ADD `Country` VARCHAR( 75 ) NOT NULL ,ADD INDEX ( Country )";
		$this->QUERY_SQL($sql,'artica_events');
	}
	
	if(!$this->FIELD_EXISTS("mails_events","Region","artica_events")){
		$sql="ALTER TABLE `mails_events` ADD `Region` VARCHAR( 55 ) NOT NULL ,ADD INDEX ( Region )";
		$this->QUERY_SQL($sql,'artica_events');
	}	
	
	if(!$this->FIELD_EXISTS("mails_events","City","artica_events")){
		$sql="ALTER TABLE `mails_events` ADD `City` VARCHAR( 75 ) NOT NULL ,ADD INDEX ( City )";
		$this->QUERY_SQL($sql,'artica_events');
	}	
	
	
	if(!$this->FIELD_EXISTS("mails_events","message_id","artica_events")){
		$sql="ALTER TABLE `mails_events` ADD `message_id` VARCHAR(120) NOT NULL ,ADD INDEX (message_id)";
		$this->QUERY_SQL($sql,'artica_events');
	}

	if(!$this->FIELD_EXISTS("mails_events","subject","artica_events")){
		$sql="ALTER TABLE `mails_events` ADD `subject` VARCHAR(120) NOT NULL ,ADD INDEX (subject)";
		$this->QUERY_SQL($sql,'artica_events');
	}		
	
	if(!$this->FIELD_EXISTS("mails_events","infected","artica_events")){
		$sql="ALTER TABLE `mails_events` ADD `infected` INT(1) NOT NULL ,ADD INDEX ( infected )";
		$this->QUERY_SQL($sql,'artica_events');
	}		
	
	if(!$this->FIELD_EXISTS("mails_events","spammy","artica_events")){
		$sql="ALTER TABLE `mails_events` ADD `spammy` INT(1) NOT NULL ,ADD INDEX ( spammy )";
		$this->QUERY_SQL($sql,'artica_events');
	}

	if(!$this->FIELD_EXISTS("mails_events","spam","artica_events")){
		$sql="ALTER TABLE `mails_events` ADD `spam` INT(1) NOT NULL ,ADD INDEX ( spam )";
		$this->QUERY_SQL($sql,'artica_events');
	}		

	if(!$this->FIELD_EXISTS("mails_events","banned","artica_events")){
		$sql="ALTER TABLE `mails_events` ADD `banned` INT(1) NOT NULL ,ADD INDEX (banned)";
		$this->QUERY_SQL($sql,'artica_events');
	}	
}

public	function CheckTables_repositories(){
		
		$sql="CREATE TABLE IF NOT EXISTS `debian_packages_logs` (
			  `ID` int(5) NOT NULL auto_increment,
			  `zDate` datetime NOT NULL,
			  `package_name` varchar(255) NOT NULL,
			  `events` text NOT NULL,
			  `install_type` varchar(50) NOT NULL,
			  PRIMARY KEY  (`ID`),
			  KEY `package_name` (`package_name`),
			  KEY `install_type` (`install_type`)
			) ";
		
		$this->QUERY_SQL($sql,'artica_backup');
		
		$sql="CREATE TABLE IF NOT EXISTS `debian_packages` (
			  `package_name` varchar(255) NOT NULL,
			  `package_version` varchar(255) NOT NULL,
			  `package_info` varchar(255) NOT NULL,
			  `package_description` text NOT NULL,
			  `package_status` varchar(10) NOT NULL,
			  UNIQUE KEY `package_name` (`package_name`),
			  KEY `package_version` (`package_version`),
			  KEY `package_status` (`package_status`)
				) ";
		
		$this->QUERY_SQL($sql,'artica_backup');
		if(!$this->TABLE_EXISTS('logon_scripts','artica_backup')){	
			$sql="CREATE TABLE `artica_backup`.`logon_scripts` (`gpid` INT( 10 ) NOT NULL ,`script_code` TEXT NOT NULL ,PRIMARY KEY ( `gpid` ))";
			$this->QUERY_SQL($sql,'artica_backup');
		}
		
		
	}
public function CheckTables_openvpn(){
		
				if(!$this->TABLE_EXISTS('hamachi','artica_backup')){		
					$sql="CREATE TABLE `artica_backup`.`hamachi` (
					`ID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
					`pattern` TEXT NOT NULL)";
			 
					$this->QUERY_SQL($sql,'artica_backup');
				}
			
		
			if(!$this->TABLE_EXISTS('vpnclient','artica_backup')){	
				$sql="		
					CREATE TABLE IF NOT EXISTS `vpnclient` (
					  `ID` int(11) NOT NULL AUTO_INCREMENT,
					  `servername` varchar(180) NOT NULL,
					  `serverport` int(3) NOT NULL,
					  `admin` varchar(50) NOT NULL,
					  `password` varchar(128) NOT NULL,
					  `connexion_name` varchar(128) NOT NULL,
					  `sitename` varchar(128) NOT NULL,
					  `IP_START` varchar(25) NOT NULL,
					  `netmask` varchar(25) NOT NULL,
					  `ethlisten` varchar(10) NOT NULL,
					  `keypassword` varchar(255) NOT NULL,
					  `connexion_type` int(1) NOT NULL,
					  `ca_bin` longblob NOT NULL,
					  `key_bin` longblob NOT NULL,
					  `cert_bin` longblob NOT NULL,
					  `ovpn` longblob NOT NULL,
					  `routes` text NOT NULL,
					  `enabled` int(1) NOT NULL,
					  `routes_additionnal` TEXT NOT NULL, 
					  PRIMARY KEY (`ID`),
					  KEY `sitename` (`sitename`),
					  KEY `ethlisten` (`ethlisten`),
					  KEY `connexion_type` (`connexion_type`),
					  KEY `enabled` (`enabled`)
					) ";
							
		
				if(!$this->QUERY_SQL($sql,'artica_backup')){return false;}
				
			}
			
	
				
				if(!$this->FIELD_EXISTS("vpnclient","enabled","artica_backup")){
					$sql="ALTER TABLE `vpnclient` ADD `enabled` int(1) NOT NULL ,ADD INDEX ( `enabled` )";
					if(!$this->QUERY_SQL($sql,"artica_backup")){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);return false;}
				}
				
				if(!$this->FIELD_EXISTS("vpnclient","ethlisten","artica_backup")){
					$sql="ALTER TABLE `vpnclient` ADD `ethlisten` varchar(10) NOT NULL ,ADD INDEX ( `ethlisten` )";
					if(!$this->QUERY_SQL($sql,"artica_backup")){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);return false;}
				}	
				
				if(!$this->FIELD_EXISTS("vpnclient","keypassword","artica_backup")){
					$sql="ALTER TABLE `vpnclient` ADD `keypassword` varchar(255) NOT NULL";
					if(!$this->QUERY_SQL($sql,"artica_backup")){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);return false;}
				}	
				
				if(!$this->FIELD_EXISTS("vpnclient","routes_additionnal","artica_backup")){
					$sql="ALTER TABLE `vpnclient` ADD `routes_additionnal` TEXT NOT NULL";
					if(!$this->QUERY_SQL($sql,"artica_backup")){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);return false;}
				}

				if(!$this->FIELD_EXISTS("vpnclient","wakeupip","artica_backup")){
					$sql="ALTER TABLE `vpnclient` ADD `wakeupip` VARCHAR( 50 ) NOT NULL ";
					if(!$this->QUERY_SQL($sql,"artica_backup")){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);return false;}
				}

				if(!$this->FIELD_EXISTS("vpnclient","remote_site_routes","artica_backup")){
					$sql="ALTER TABLE `vpnclient` ADD `remote_site_routes` LONGTEXT NOT NULL";
					if(!$this->QUERY_SQL($sql,"artica_backup")){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);return false;}
				}				
				if(!$this->FIELD_EXISTS("vpnclient","wakeup_results","artica_backup")){
					$sql="ALTER TABLE `vpnclient` ADD `wakeup_results` TEXT NOT NULL ,ADD `wakeupok` INT( 1 ) NOT NULL DEFAULT '0'";
					if(!$this->QUERY_SQL($sql,"artica_backup")){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);return false;}
				}
				
				if(!$this->FIELD_EXISTS("vpnclient","wakeupok","artica_backup")){
					$sql="ALTER TABLE `vpnclient` ADD `wakeupok` INT( 1 ) NOT NULL DEFAULT '0'";
					if(!$this->QUERY_SQL($sql,"artica_backup")){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);return false;}
				}				
				
				if(!$this->FIELD_EXISTS("vpnclient","EnableAuth","artica_backup")){
					$sql="ALTER TABLE `vpnclient` ADD `EnableAuth` INT( 1 ) NOT NULL DEFAULT '0',ADD `AuthUsername` VARCHAR( 128 ) NOT NULL ,ADD `AuthPassword` VARCHAR( 255 ) NOT NULL";
					if(!$this->QUERY_SQL($sql,"artica_backup")){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);return false;}
				}	

				if(!$this->FIELD_EXISTS("vpnclient","FixedIPAddr","artica_backup")){
					$sql="ALTER TABLE `vpnclient` ADD `FixedIPAddr` int( 3 ) NOT NULL DEFAULT '0'";
					if(!$this->QUERY_SQL($sql,"artica_backup")){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);return false;}
				}					

				
				 
				
		return true;

	}
	public function  CheckTables_qos(){
		if(!$this->TABLE_EXISTS('qos_eth','artica_backup')){	
			$sql="	CREATE TABLE `artica_backup`.`qos_eth` (
			`ID` INT( 2 ) NOT NULL AUTO_INCREMENT ,
			`NIC` VARCHAR( 5 ) NOT NULL ,
			`default_class_id` INT( 2 ) NOT NULL ,
			`name` VARCHAR( 255 ) NOT NULL,
			`bandwith` INT( 2 ) NOT NULL DEFAULT '100',
			`enabled` INT( 1 ) NOT NULL DEFAULT '1',
			PRIMARY KEY ( `ID` ) ,
			KEY `enabled` (`enabled`),
			KEY `NIC` (`NIC`)
			)";		
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){writelogs($this->mysql_error,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		}
		
		
		if(!$this->TABLE_EXISTS('qos_class','artica_backup')){	
			$sql="CREATE TABLE `artica_backup`.`qos_class` (
				`ID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`name` VARCHAR( 50 ) NOT NULL ,
				`service_id` INT( 2 ) NOT NULL ,
				`prio` INT NOT NULL DEFAULT '0',
				`rate` INT( 5 ) NOT NULL ,
				`ceil` INT( 5 ) NOT NULL ,
				`enabled` INT( 1 ) NOT NULL DEFAULT '1',
				KEY `enabled` (`enabled`),
				KEY `service_id` (`service_id`),
				KEY `prio` (`prio`)
				)"; 
			
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){writelogs($this->mysql_error,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		}

		
		if(!$this->TABLE_EXISTS('qos_rules','artica_backup')){	
			$sql="CREATE TABLE `artica_backup`.`qos_rules` (
			`ID` INT( 5 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`class_id` INT( 5 ) NOT NULL ,
			`name` VARCHAR( 255 ) NOT NULL ,
			`sip` VARCHAR( 255 ) NOT NULL ,
			`sport` VARCHAR( 255 ) NOT NULL ,
			`dip` VARCHAR( 255 ) NOT NULL ,
			`dport` VARCHAR( 255 ) NOT NULL ,
			`proto` VARCHAR( 5 ) NOT NULL ,
			`enabled` INT( 1 ) NOT NULL,
			KEY `class_id` (`class_id`),
			KEY `enabled` (`enabled`)
			)"; 		
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){writelogs($this->mysql_error,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		}
		
	}
	public function CheckTables_dar(){
		
			if(!$this->TABLE_EXISTS('dar_index','artica_backup')){	
				$sql="		
				CREATE TABLE IF NOT EXISTS `dar_index` (
				  `filekey` varchar(90) NOT NULL,
				  `filedate` datetime NOT NULL,
				  `filepath` varchar(255) NOT NULL,
				  `basepath` varchar(255) NOT NULL,
				  `database_name` varchar(90) NOT NULL,
				  `source_path` varchar(255) NOT NULL,
				  `mount_md5` varchar(90) NOT NULL,
				  `servername` varchar(90) NOT NULL,
				  `filesize` int(10) NOT NULL default '0',
				  PRIMARY KEY  (`filekey`),
				  KEY `filepath` (`filepath`),
				  KEY `source_path` (`source_path`),
				  KEY `basepath` (`basepath`),
				  KEY `database_name` (`database_name`),
				  KEY `mount_md5` (`mount_md5`),
				  KEY `servername` (`servername`)
				)";
		
				if(!$this->QUERY_SQL($sql,'artica_backup')){return false;}
				return true;
			}
			if(!$this->FIELD_EXISTS("dar_index","mount_md5","artica_backup")){
				$sql="ALTER TABLE `dar_index` ADD `mount_md5` varchar(90) NOT NULL ,ADD INDEX ( `mount_md5` )";
				if(!$this->QUERY_SQL($sql,"artica_backup")){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);return false;}
			}
			
			if(!$this->FIELD_EXISTS("dar_index","servername","artica_backup")){
				$sql="ALTER TABLE `dar_index` ADD `servername` varchar(90) NOT NULL ,ADD INDEX ( `servername` )";
				if(!$this->QUERY_SQL($sql,"artica_backup")){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);return false;}
			}
		return true;	
			
	}
	public function CheckTable_dansguardian(){

		
		
		if(!$this->TABLE_EXISTS('dansguardian_groups','artica_backup')){
		$sql="CREATE TABLE IF NOT EXISTS `dansguardian_groups` (
			  `ID` int(11) NOT NULL AUTO_INCREMENT,
			  `RuleID` int(5) NOT NULL,
			  `group_id` int(10) NOT NULL,
			  `dngroup` VARCHAR( 255 ) NOT NULL,
			  PRIMARY KEY (`ID`),
			  UNIQUE KEY `group_id` (`group_id`),
			  KEY `RuleID` (`RuleID`)
			) ";
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){$this->mysql_error=$this->mysql_error."\n$sql";writelogs("$this->mysql_error\n$sql",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		}
		
		if(!$this->FIELD_EXISTS("dansguardian_groups","dngroup","artica_backup")){
			$sql="ALTER TABLE `dansguardian_groups` ADD `dngroup` VARCHAR(255) NOT NULL";
			$this->QUERY_SQL($sql,"artica_backup");
			if(!$this->ok){writelogs("$this->mysql_error\n$sql",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
			}		
		
		
			if(!$this->TABLE_EXISTS('dansguardian_personal_categories','artica_backup')){
			$sql="CREATE TABLE IF NOT EXISTS `dansguardian_personal_categories` (
				  `ID` int(100) NOT NULL AUTO_INCREMENT,
				  `category` varchar(255) NOT NULL,
				  `pattern` varchar(255) NOT NULL,
				  `category_type` varchar(80) NOT NULL,
				  `RuleID` int(10) NOT NULL,
				  PRIMARY KEY (`ID`),
				  KEY `category` (`category`),
				  KEY `pattern`(`pattern`),
				  KEY `category_type` (`category_type`),
				  KEY `RuleID` (`RuleID`)
				)";
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){$this->mysql_error=$this->mysql_error."\n$sql";}
		}		
		
		

		
		if(!$this->TABLE_EXISTS('dansguardian_files','artica_backup')){
			 $sql="CREATE TABLE IF NOT EXISTS `dansguardian_files` (
			  `ID` int(100) NOT NULL AUTO_INCREMENT,
			  `filename` varchar(50) NOT NULL,
			  `pattern` text NOT NULL,
			  `infos` varchar(125) NOT NULL,
			  `RuleID` int(2) NOT NULL,
			  `enabled` int(1) NOT NULL DEFAULT '1',
			  `userdefined` int(1) NOT NULL DEFAULT '0',
			  `uid` VARCHAR( 90 ) NOT NULL,
			  PRIMARY KEY (`ID`),
			  KEY `filename` (`filename`),
			  KEY `RuleID` (`RuleID`),
			  KEY `enabled` (`enabled`),
			  KEY `userdefined` (`userdefined`),
			  KEY `uid` (`uid`)
			)";
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){$this->mysql_error=$this->mysql_error."\n$sql";}
		}
		
		if(!$this->FIELD_EXISTS("dansguardian_files","uid","artica_backup")){
			$sql="ALTER TABLE `dansguardian_files` ADD `uid` VARCHAR( 90 ) NOT NULL ,ADD INDEX ( `uid` )";
			$this->QUERY_SQL($sql,"artica_backup");
			if(!$this->ok){writelogs("$this->mysql_error\n$sql",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);return false;}
		}
		if(!$this->TABLE_EXISTS('loop_disks','artica_backup')){
			 $sql="CREATE TABLE IF NOT EXISTS `loop_disks` (
				  `path` varchar(255) NOT NULL,
				  `size` int(10) NOT NULL,
				  `loop_dev` varchar(128) NOT NULL,
				  `disk_name` varchar(255) NOT NULL,
				  UNIQUE KEY `path` (`path`),
				  UNIQUE KEY `disk_name` (`disk_name`),
				  KEY `size` (`size`,`loop_dev`)
				)";
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){$this->mysql_error=$this->mysql_error."\n$sql";}	 
		}
		if(!$this->FIELD_EXISTS("loop_disks","disk_name","artica_backup")){
			$this->QUERY_SQL('ALTER TABLE `loop_disks` ADD `disk_name` VARCHAR( 255 ) NOT NULL,ADD UNIQUE (`disk_name`)','artica_backup');
			if(!$this->ok){$this->mysql_error=$this->mysql_error."\n$sql";}
		}
		 
		
		if(!$this->TABLE_EXISTS('dansguardian_rules','artica_backup')){
			 $sql="CREATE TABLE IF NOT EXISTS `dansguardian_rules` (
				  `RuleID` int(1) NOT NULL AUTO_INCREMENT,
				  `RuleOrder` int(1) NOT NULL,
				  `RuleName` varchar(120) NOT NULL,
				  `RuleText` text NOT NULL,
				  PRIMARY KEY (`RuleID`),
				  KEY `RuleOrder` (`RuleOrder`,`RuleName`))";
			 $this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){$this->mysql_error=$this->mysql_error."\n$sql";}
		}
		
		if(!$this->TABLE_EXISTS('dansguardian_weightedphraselist','artica_backup')){
			 $sql="CREATE TABLE IF NOT EXISTS `dansguardian_weightedphraselist` (
			  `ID` int(100) NOT NULL AUTO_INCREMENT,
			  `category_name` varchar(75) NOT NULL,
			  `pattern` varchar(255) NOT NULL,
			  PRIMARY KEY (`ID`),
			  KEY `category_name` (`category_name`,`pattern`))";
			 $this->QUERY_SQL($sql,'artica_backup'); 
			 
			if(!$this->ok){
				writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				$this->mysql_error=$this->mysql_error."\n$sql";}
			} 
		
		if(!$this->TABLE_EXISTS('dansguardian_ipgroups','artica_backup')){
			 $sql="CREATE TABLE `artica_backup`.`dansguardian_ipgroups` (
				`ID` INT( 100 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`pattern` VARCHAR( 128 ) NOT NULL ,
				`RuleID` INT( 1 ) NOT NULL ,
				INDEX ( `pattern` , `RuleID` )
				)";
			 $this->QUERY_SQL($sql,'artica_backup'); 
			 
			 if(!$this->ok){
			 	writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			 	$this->mysql_error=$this->mysql_error."\n$sql";
			 }
		} 
		
		
		

		if(!$this->TABLE_EXISTS('dansguardian_whitelists','artica_events')){
			 $sql="CREATE TABLE IF NOT EXISTS `dansguardian_whitelists` (
				  `ID` int(100) NOT NULL AUTO_INCREMENT,
				  `zDate` datetime NOT NULL,
				  `uri` varchar(255) NOT NULL,
				  `uid` varchar(255) NOT NULL,
				  PRIMARY KEY (`ID`),
				  UNIQUE KEY `ID` (`ID`),
				  KEY `uri` (`uri`),
				  KEY `uid` (`uid`)
				) ";
			 $this->QUERY_SQL($sql,'artica_events'); 
			 
			 if(!$this->ok){
			 	$this->mysql_error=$this->mysql_error."\n$sql";
			 	writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			 }
		}
		
		if(!$this->TABLE_EXISTS('blocked_websites','artica_events')){		
			$sql="CREATE TABLE `artica_events`.`blocked_websites` (
			`ID` INT( 100 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`zDate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
			`client` VARCHAR( 90 ) NOT NULL ,
			`website` VARCHAR( 125 ) NOT NULL ,
			`category` VARCHAR( 50 ) NOT NULL ,
			`rulename` VARCHAR( 50 ) NOT NULL ,
			`public_ip` VARCHAR( 40 ) NOT NULL ,
			KEY `zDate` (`zDate`),
			KEY `client` (`client`),
			KEY `website` (`website`),
			KEY `category` (`category`),
			KEY `rulename` (`rulename`),
			KEY `public_ip` (`public_ip`)
			
			)"; 
		$this->QUERY_SQL($sql,'artica_events'); 
		
		if(!$this->ok){
			writelogs("$this->mysql_error\n$sql",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$this->mysql_error=$this->mysql_error."\n$sql";
		}

		}
		

	
		if(!$this->FIELD_EXISTS("dansguardian_ipgroups","uid","artica_backup")){
				$sql="ALTER TABLE `dansguardian_ipgroups` ADD `uid` VARCHAR( 90 ) NOT NULL ";
				$this->QUERY_SQL($sql,"artica_backup");
				if(!$this->ok){writelogs("$this->mysql_error\n$sql",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);return false;}
			}		
			
		
	
		
		return true;
		
		
	}

	public function CheckTablesSquid(){
		if(!$this->TABLE_EXISTS('squid_block','artica_backup')){
		$sql="CREATE TABLE `artica_backup`.`squid_block` (
			`ID` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`uri` VARCHAR( 255 ) NOT NULL ,`task_type` VARCHAR( 90 ) NOT NULL ,
			`zDate` DATETIME NOT NULL ,INDEX ( `task_type` , `zDate` ) ,UNIQUE (`uri`))";
		$this->QUERY_SQL($sql,"artica_backup");
		}
		
		if(!$this->TABLE_EXISTS('squid_white','artica_backup')){
			$sql="CREATE TABLE `artica_backup`.`squid_white` (
			`ID` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`uri` VARCHAR( 255 ) NOT NULL ,`task_type` VARCHAR( 90 ) NOT NULL ,
			`zDate` DATETIME NOT NULL ,INDEX ( `task_type` , `zDate` ) ,UNIQUE (`uri`))";
			$this->QUERY_SQL($sql,"artica_backup");
		}		
		
		if(!$this->TABLE_EXISTS('squid_accel','artica_backup')){
			$sql="CREATE TABLE `artica_backup`.`squid_accel` (
			`ID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`website_name` VARCHAR( 255 ) NOT NULL ,
			`website_ip` VARCHAR( 25 ) NOT NULL ,
			`website_port` INT NOT NULL ,
			`options` TEXT NOT NULL ,
			INDEX ( `website_name` )) ";
			$this->QUERY_SQL($sql,"artica_backup");
		}
		
		
		if(!$this->TABLE_EXISTS('squid_speed','artica_backup')){		
		$sql="CREATE TABLE `artica_backup`.`squid_speed` (
			`ID` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`domain` VARCHAR( 255 ) NOT NULL ,
			`refresh_pattern_min` INT( 5 ) NOT NULL DEFAULT '0',
			`refresh_pattern_perc` INT( 2 ) NOT NULL DEFAULT '20',
			`refresh_pattern_max` INT( 5 ) NOT NULL DEFAULT '4320',
			`refresh_pattern_options` VARCHAR( 255 ) NOT NULL ,
			INDEX ( `domain` ));";
		$this->QUERY_SQL($sql,"artica_backup");	
		}
		
		if(!$this->TABLE_EXISTS('kav4Proxy','artica_backup')){		
			$sql="CREATE TABLE IF NOT EXISTS `kav4Proxy` (
				  `ID` int(11) NOT NULL AUTO_INCREMENT,
				  `key` varchar(50) NOT NULL,
				  `value` varchar(50) NOT NULL,
				  `data` varchar(255) NOT NULL,
				  `zMD5` varchar(95) NOT NULL,
				  PRIMARY KEY (`ID`),
				  UNIQUE KEY `zMD5` (`zMD5`),
				  KEY `key` (`key`,`value`)
				) "; 		
			$this->QUERY_SQL($sql,"artica_backup");
		}
		
		
		if(!$this->TABLE_EXISTS('Kav4Proxy_groups','artica_backup')){		
			$sql="CREATE TABLE `artica_backup`.`Kav4Proxy_groups` (
				`groupname` VARCHAR( 128 ) NOT NULL ,
				`priority` INT( 2 ) NOT NULL ,
				`ClientIP` LONGTEXT NOT NULL ,
				`URL` LONGTEXT NOT NULL ,
				`EngineOption` TEXT NOT NULL ,
				`EngineAction` TEXT NOT NULL ,
				`enabled` INT( 1 ) NOT NULL DEFAULT '1',
				PRIMARY KEY ( `groupname` ) ,
				INDEX ( `priority` , `enabled` )
				)"; 		
			$this->QUERY_SQL($sql,"artica_backup");
		}		
		
			if(!$this->TABLE_EXISTS('squid_parents','artica_backup')){		
			$sql="
				CREATE TABLE `artica_backup`.`squid_parents` (
				`ID` INT( 3 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`servername` VARCHAR( 255 ) NOT NULL ,
				`server_port` INT( 3 ) NOT NULL ,
				`server_type` VARCHAR( 50 ) NOT NULL ,
				`icp_port` INT( 3 ) NOT NULL ,
				`options` TEXT NOT NULL ,
				INDEX ( `servername` ))"; 		
			$this->QUERY_SQL($sql,"artica_backup");
		}	

		
		
		if(!$this->TABLE_EXISTS('squid_ssl','artica_backup')){		
			$sql="CREATE TABLE `artica_backup`.`squid_ssl` (
			`ID` INT( 5 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`website_name` VARCHAR( 255 ) NOT NULL ,
			`type` VARCHAR( 40 ) NOT NULL ,
			`enabled` INT( 1 ) NOT NULL DEFAULT '1',
			INDEX ( `type` , `enabled`,`website_name` ))";
			$this->QUERY_SQL($sql,"artica_backup");
			}
			
		if($this->FIELD_TYPE("squid_ssl","website_name","artica_backup")<>"varchar(255)"){
			$sql="ALTER TABLE `squid_ssl` CHANGE `website_name` `website_name` VARCHAR( 255 ) NOT NULL";
			$this->QUERY_SQL($sql,"artica_backup");
		}
			
			
		if(!$this->TABLE_EXISTS('UserAgents','artica_backup')){		
			$sql="CREATE TABLE `artica_backup`.`UserAgents` (
				`unique_key` VARCHAR( 95 ) NOT NULL ,
				`browser` VARCHAR( 60 ) NOT NULL ,
				`string` VARCHAR( 255 ) NOT NULL ,
				PRIMARY KEY ( `unique_key` ) ,
				INDEX ( `browser` , `string` )
				) ";
		$this->QUERY_SQL($sql,"artica_backup");
	}
	
	if(!$this->TABLE_EXISTS('zarafa_orphaned','artica_backup')){		
			$sql="CREATE TABLE `artica_backup`.`zarafa_orphaned` (
			`storeid` VARCHAR( 128 ) NOT NULL ,
			`size` INT( 10 ) NOT NULL ,
			`zDate` TIMESTAMP NOT NULL ,
			`uid` VARCHAR( 256 ) NOT NULL ,
			PRIMARY KEY ( `storeid` ) ,
			INDEX ( `size` , `zDate` , `uid` )
			)";
				
 		$this->QUERY_SQL($sql,"artica_backup");
		} 
	
	if(!$this->TABLE_EXISTS('squid_pools','artica_backup')){		
			$sql="CREATE TABLE `artica_backup`.`squid_pools` (
			`ID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`rulename` VARCHAR( 255 ) NOT NULL ,
			`rule_class` INT( 1 ) NOT NULL DEFAULT '2',
			`total_net` VARCHAR( 90 ) NOT NULL ,
			`total_users` VARCHAR( 90 ) NOT NULL ,
			INDEX ( `rulename` ))";
				
 		$this->QUERY_SQL($sql,"artica_backup");
		}
		
		if(!$this->FIELD_EXISTS("squid_pools","enable","artica_backup")){
			$sql="ALTER TABLE `squid_pools` ADD `enable` INT( 1 ) NOT NULL DEFAULT '1',ADD INDEX ( `enable` )";
			$this->QUERY_SQL($sql,"artica_backup");
			if(!$this->ok){
				writelogs("$this->mysql_error\n$sql",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			}
		}

		
		
	if(!$this->TABLE_EXISTS('squid_pools_acls','artica_backup')){		
			$sql="CREATE TABLE `artica_backup`.`squid_pools_acls` (
			`ID` INT( 5 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`pool_id` INT( 5 ) NOT NULL ,
			`ACL_TYPE` VARCHAR( 20 ) NOT NULL ,
			`ACL_DATAS` TEXT NOT NULL ,
			`enabled` INT( 1 ) NOT NULL DEFAULT '1',
			INDEX ( `pool_id` , `ACL_TYPE` ))";
			$this->QUERY_SQL($sql,"artica_backup");
			if(!$this->ok){
				writelogs("$this->mysql_error\n$sql",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			}
		 
		}
		
		if($this->TABLE_EXISTS('squid_events_sites','artica_events')){$sql="DROP TABLE `squid_events_sites`";$this->QUERY_SQL($sql,"artica_events");if(!$this->ok){writelogs("$this->mysql_error\n$sql",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}}
		if($this->TABLE_EXISTS('squid_events_clients_sites','artica_events')){$sql="DROP TABLE `artica_events`.`squid_events_clients_sites`"; $this->QUERY_SQL($sql,"artica_events");if(!$this->ok){writelogs("$this->mysql_error\n$sql",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}			}
		
		
	if(!$this->TABLE_EXISTS('updates_categories','artica_backup')){		
			writelogs("Creating 'updates_categories/artica_backup",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$sql="CREATE TABLE IF NOT EXISTS `updates_categories` (
				  `filename` varchar(128) NOT NULL,
				  `categories` varchar(50) NOT NULL,
				  `filesize` int(3) NOT NULL,
				  `progress` int(1) NOT NULL,
				  `finish` int(1) NOT NULL DEFAULT '0',
				  `zDate` datetime NOT NULL,
				  `subject` varchar(255) NOT NULL,
				  `zmd5` varchar(90) NOT NULL,
				  PRIMARY KEY (`filename`),
				  UNIQUE KEY `zmd5` (`zmd5`),
				  KEY `categories` (`categories`,`filesize`,`progress`,`finish`),
				  KEY `zDate` (`zDate`)
				) ";		
			$this->QUERY_SQL($sql,"artica_backup");
			if(!$this->ok){
				writelogs("$this->mysql_error\n$sql",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			}	
	}
	
	if(!$this->FIELD_EXISTS("updates_categories","zmd5","artica_backup")){
		$sql="ALTER TABLE `updates_categories` ADD `zmd5` VARCHAR( 90 ) NOT NULL,ADD UNIQUE KEY `zmd5` (`zmd5`)";
		$this->QUERY_SQL($sql,"artica_backup");
		if(!$this->ok){writelogs("$this->mysql_error\n$sql",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		}	

		
if(!$this->TABLE_EXISTS('kav4proxy_av_stats','artica_events')){		
			writelogs("Creating 'kav4proxy_av_stats/artica_events",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$sql="CREATE TABLE IF NOT EXISTS `kav4proxy_av_stats` (
  			`zDate` datetime NOT NULL,
			  `total_requests` INT(5) NOT NULL,
			  `infected_requests` INT(5) NOT NULL,
			  `protected_requests` INT(5) NOT NULL,
			  `error_requests` INT(5) NOT NULL,
			  `requests_per_min` INT(5) NOT NULL,
			  `processed_traffic` double(100,2) NOT NULL,
			  `clean_traffic` double(100,2) NOT NULL,
			  `infected_traffic` double(100,2) NOT NULL,
			  `traffic_per_min` double(100,2) NOT NULL,
			  `engine_errors` INT(5) NOT NULL,
			  `total_connections` INT(5) NOT NULL,
			  `total_processes` INT(5) NOT NULL,
			  `idle_processes` INT(5) NOT NULL,
			   KEY `sindex` (`zDate`,`total_requests`,`infected_requests`,`protected_requests`,`error_requests`),
			   KEY `sindex2`(`requests_per_min`,`processed_traffic`,`clean_traffic`,`infected_traffic`,`traffic_per_min`),
			   KEY `sindex3`(`engine_errors`,`total_connections`,`total_processes`,`idle_processes`)
			  )";	
	
			$this->QUERY_SQL($sql,"artica_events");
			if(!$this->ok){
				writelogs("$this->mysql_error\n$sql",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			}			
		}				
		
	if(!$this->TABLE_EXISTS('kav4proxy_updates','artica_events')){		
			writelogs("Creating 'kav4proxy_updates/artica_events",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$sql="CREATE TABLE IF NOT EXISTS `kav4proxy_updates` (
  			`zDate` datetime NOT NULL,
			  `subject` varchar(255) NOT NULL,
			  `content` longtext NOT NULL,
			  PRIMARY KEY (`zDate`),
			  FULLTEXT KEY `content` (`content`),
			  FULLTEXT KEY `subject` (`subject`)
			)";	
	
			$this->QUERY_SQL($sql,"artica_events");
			if(!$this->ok){
				writelogs("$this->mysql_error\n$sql",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			}			
		}	

		
	if(!$this->TABLE_EXISTS('kav4proxyDays','artica_events')){		
			writelogs("Creating 'kav4proxyDays/artica_events",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$sql="CREATE TABLE IF NOT EXISTS `kav4proxyDays` (
				  `days` date NOT NULL,
				  `size` int(10) NOT NULL,
				  `websites` varchar(255) NOT NULL,
				  `category` varchar(128) NOT NULL,
				  `hits` int(10) NOT NULL,
				  `Country` varchar(50) NOT NULL,
				  `zmd5` varchar(90) NOT NULL,
				  PRIMARY KEY (`zmd5`),
				  KEY `size` (`size`,`category`,`hits`),
				  KEY `websites` (`websites`),
				  KEY `Country` (`Country`)
				) ";	
	
			$this->QUERY_SQL($sql,"artica_events");
			if(!$this->ok){
				writelogs("$this->mysql_error\n$sql",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			}			
		}

		
	if(!$this->TABLE_EXISTS('kav4proxyVirus','artica_events')){		
			writelogs("Creating 'kav4proxyVirus/artica_events",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$sql="CREATE TABLE IF NOT EXISTS `kav4proxyVirus` (
				  `zmd5` varchar(90) NOT NULL,
				  `days` date NOT NULL,
				  `size` int(50) NOT NULL,
				  `client` varchar(50) NOT NULL,
				  `country` varchar(50) NOT NULL,
				  `sitename` varchar(128) NOT NULL,
				  `category` varchar(50) NOT NULL,
				  `hits` int(50) NOT NULL,
				  PRIMARY KEY (`zmd5`),
				  KEY `days` (`days`,`size`,`client`,`country`,`category`,`hits`),
				  KEY `sitename` (`sitename`)
				)";	
	
			$this->QUERY_SQL($sql,"artica_events");
			if(!$this->ok){
				writelogs("$this->mysql_error\n$sql",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			}			
		} 		

		$this->squid_events_sites_day();
						
		
		if($this->TABLE_EXISTS('squid_events_hours','artica_events')){
			$sql="DROP TABLE `squid_events_hours`";
			$this->QUERY_SQL($sql,"artica_events");
			if(!$this->ok){writelogs("$this->mysql_error\n$sql",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}			
		}			
		
		if($this->TABLE_EXISTS('squid_events_clients_day','artica_events')){		
			$sql="DROP TABLE `artica_events`.`squid_events_clients_day`"; 
			$this->QUERY_SQL($sql,"artica_events");
			if(!$this->ok){writelogs("$this->mysql_error\n$sql",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}			
		}
	
		if(!$this->TABLE_EXISTS('squid_adzapper','artica_backup')){	
		
		$sql="CREATE TABLE `artica_backup`.`squid_adzapper` (
				`ID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`uri` VARCHAR( 255 ) NOT NULL ,
				`uri_type` VARCHAR( 20 ) NOT NULL ,
				`enabled` INT( 1 ) NOT NULL DEFAULT '1',
				`zmd5` VARCHAR( 120 ) NOT NULL ,
				 KEY `uri` (`uri`),
				 KEY `uri_type` (`uri_type`),
				 KEY `enabled` (`enabled`),
				 UNIQUE (`zmd5`))";		
		
				$this->QUERY_SQL($sql,'artica_backup');
				if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
				}
				
		if(!$this->TABLE_EXISTS('squid_templates','artica_backup')){			
		$sql="CREATE TABLE `artica_backup`.`squid_templates` (
			`TEMPLATE_NAME` VARCHAR( 80 ) NOT NULL ,
			`TEMPLATE_DATA` TEXT NOT NULL ,
			`TEMPLATE_DATA_SOURCE` TEXT NOT NULL ,
			PRIMARY KEY ( `TEMPLATE_NAME` ))";
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		}	
	
	}
	
	
	
	
	
	public function squid_events_sites_day_patch(){
	if(!$this->FIELD_EXISTS("squid_events_sites_day","category","artica_events")){
			$sql="ALTER TABLE `squid_events_sites_day` ADD `category` VARCHAR( 90 ) NOT NULL DEFAULT '1',ADD INDEX ( `category` )";
			$this->QUERY_SQL($sql,"artica_events");
			if(!$this->ok){
				writelogs("$this->mysql_error\n$sql",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			}
		}
	}
	public function squid_events_sites_day($check=true){
			if($check){
				if($this->TABLE_EXISTS('squid_events_sites_day','artica_events')){$this->squid_events_sites_day_patch();return;}
			}	
			
			$sql="CREATE TABLE `artica_events`.`squid_events_sites_day` (
			`ID` INT( 100 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`days` DATE NOT NULL ,
			`websites` VARCHAR( 255 ) NOT NULL ,
			`website_size` INT( 10 ) NOT NULL ,
			`website_hits` INT( 10 ) NOT NULL ,
			`category` VARCHAR( 90 ) NOT NULL,
			`country` VARCHAR( 50 ) NOT NULL,
			KEY `category` (`category`),
			KEY `country` (`country`),
			INDEX ( `days` , `websites` , `website_size` , `website_hits` )
			)"; 
			$this->QUERY_SQL($sql,"artica_events");
			if(!$this->ok){writelogs("$this->mysql_error\n$sql",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}		
		}
	function CheckTables_rsync(){
		if(!$this->TABLE_EXISTS('rsync_events','artica_events')){	
					$sql="
						CREATE TABLE IF NOT EXISTS `rsync_events` (
					  `ID` int(100) NOT NULL AUTO_INCREMENT,
					  `folder_md5` varchar(90) NOT NULL,
					  `date_start` datetime NOT NULL,
					  `date_end` datetime NOT NULL,
					  `events` text NOT NULL,
					  `path` varchar(255)  NOT NULL,
					  `failed` int(1) NOT NULL,
					  `sent_size` float NOT NULL,
					  `speed` float NOT NULL,
					  `numfiles` int(5) NOT NULL,
					  `storage_server` varchar(80) NOT NULL,
					  PRIMARY KEY (`ID`),
					  KEY `failed` (`failed`),
					  KEY `storage_server` (`storage_server`),
					  KEY `folder_md5` (`folder_md5`))";		
								
					$this->QUERY_SQL($sql,'artica_events');
					if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
				}
				
			if(!$this->TABLE_EXISTS('dar_events','artica_events')){	
					$sql="
					CREATE TABLE IF NOT EXISTS `dar_events` (
					  `ID` int(100) NOT NULL auto_increment,
					  `date_start` datetime NOT NULL,
					  `date_end` datetime NOT NULL,
					  `db_path` varchar(255) NOT NULL,
					  `xml` varchar(255) NOT NULL,
					  `source` varchar(255) NOT NULL,
					  `failed` int(1) NOT NULL default '0',
					  `builded` int(1) NOT NULL,
					  `error` varchar(255) NOT NULL,
					  PRIMARY KEY  (`ID`),
					  KEY `date_start`  (`date_start`),
					  KEY `date_end` (`date_end`),
					  KEY `db_path` (`db_path`),
					  KEY `xml` (`xml`),
					  KEY `source` (`source`),
					  KEY `failed` (`failed`),
					  KEY `builded` (`builded`)) ";
	
					$this->QUERY_SQL($sql,'artica_events');
					if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
				}

		if(!$this->TABLE_EXISTS('backup_folders','artica_backup')){			
				
			$sql="CREATE TABLE `artica_backup`.`backup_folders` (
					`ID` INT( 100 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
					`taskid` INT( 10 ) NOT NULL ,
					`path` VARCHAR( 255 ) NOT NULL ,
					`zmd5` VARCHAR( 90 ) NOT NULL ,
					`recursive` INT( 1 ) NOT NULL DEFAULT '0',
						KEY `taskid`  (`taskid`),
						KEY `path`  (`path`),
						KEY `zmd5`  (`zmd5`)
					)"; 
					$this->QUERY_SQL($sql,'artica_backup');
					if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
	
		}
		
			if(!$this->TABLE_EXISTS('rsync_server','artica_events')){			
				
			$sql="CREATE TABLE `artica_events`.`rsync_server` (
					`ID` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
					`ip_address` VARCHAR( 90 ) NOT NULL ,
					`zDate` DATETIME NOT NULL ,
					`transaction_size` INT( 20 ) NOT NULL ,
					INDEX ( `ip_address` , `zDate` )) "; 
					$this->QUERY_SQL($sql,'artica_events');
					if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
	
		}

		
			if(!$this->TABLE_EXISTS('auditd_dir','artica_backup')){			
				$sql="CREATE TABLE `artica_backup`.`auditd_dir` (
				`key` VARCHAR( 120 ) NOT NULL ,`dir` VARCHAR( 255 ) NOT NULL ,UNIQUE (`key`))";
				$this->QUERY_SQL($sql,'artica_backup');
				if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
			}
			
			
			if(!$this->TABLE_EXISTS('auditd_files','artica_backup')){			
				$sql="CREATE TABLE `artica_backup`.`auditd_files` (
				`ID` INT( 100 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`time` DATETIME NOT NULL ,
				`file` VARCHAR( 255 ) NOT NULL ,
				`syscall` VARCHAR( 20 ) NOT NULL ,
				`uid` VARCHAR( 120 ) NOT NULL ,
				`gid` VARCHAR( 120 ) NOT NULL ,
				`executable` VARCHAR( 255 ) NOT NULL ,
				`key_path` VARCHAR( 110 ) NOT NULL ,
				 KEY `time` (`time`),
				 KEY `file` (`file`),
				 KEY `syscall` (`syscall`),
				 KEY `uid` (`uid`),
				 KEY `gid` (`gid`),
				 KEY `executable` (`executable`),
				 KEY `key_path` (`key_path`))";
				$this->QUERY_SQL($sql,'artica_backup');
				if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
			} 
			
			
			
			if(!$this->TABLE_EXISTS('squid_cache_perfs','artica_events')){			
				$sql="CREATE TABLE IF NOT EXISTS `squid_cache_perfs` (
				  `zDate` datetime NOT NULL,
				  `size` int(100) NOT NULL,
				  `cached` int(1) NOT NULL,
				  `zmd5` varchar(90) NOT NULL,
				  PRIMARY KEY (`zmd5`),
				  KEY `size` (`size`,`cached`),
				  KEY `zDate` (`zDate`)
				) ";
				$this->QUERY_SQL($sql,'artica_events');
				if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
			} 
							
	
	}
function check_networks_tables(){
	
	if(!$this->TABLE_EXISTS('nics','artica_backup')){		
		$sql="CREATE TABLE IF NOT EXISTS `nics` (
			  `Interface` varchar(40) NOT NULL,
			  `AUTO` varchar(10) NOT NULL,
			  `BRIDGE_PORTS` varchar(10) NOT NULL,
			  `BRIDGE_STP` varchar(10) NOT NULL,
			  `BRIDGE_MAXWAIT` int(2) NOT NULL,
			  `DNS_SEARCH` varchar(128) NOT NULL,
			  `VLAN_ROW_DEVICE` varchar(40) NOT NULL,
			  `MTU` int(2) NOT NULL,
			  `IPADDR` varchar(55) NOT NULL,
			  `NETMASK` varchar(55) NOT NULL,
			  `BOOTPROTO` varchar(10) NOT NULL,
			  `GATEWAY` varchar(55) NOT NULL,
			  `BROADCAST` varchar(55) NOT NULL,
			  `DNS1` varchar(55) NOT NULL,
			  `DNS2` varchar(55) NOT NULL,
			  `dhcp` int(1) NOT NULL DEFAULT '0',
			  `enabled` int(1) NOT NULL DEFAULT '0',
			  `routes` longtext NOT NULL,
			  `NETWORK` varchar(55) NOT NULL,
			  `IsBridge` int(1) NOT NULL DEFAULT '0',
			  `Bridged` int(1) NOT NULL DEFAULT '0',
			  `BridgedTo` varchar(50) NOT NULL,
			  PRIMARY KEY (`Interface`),
			  KEY `BridgedTo` (`BridgedTo`)
				)";	
		$this->QUERY_SQL($sql,"artica_backup");
		if(!$this->ok){writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		}
	
	if(!$this->FIELD_EXISTS("nics","dhcp","artica_backup")){
		$sql="ALTER TABLE `nics` ADD `dhcp` INT( 1 ) NOT NULL DEFAULT '0'";
		$this->QUERY_SQL($sql,'artica_backup');
		}		
			
	if(!$this->FIELD_EXISTS("nics","enabled","artica_backup")){
		$sql="ALTER TABLE `nics` ADD `enabled` INT( 1 ) NOT NULL DEFAULT '0'";
		$this->QUERY_SQL($sql,'artica_backup');
		}

	if(!$this->FIELD_EXISTS("nics","routes","artica_backup")){
		$sql="ALTER TABLE `nics` ADD `routes` LONGTEXT NOT NULL ";
		$this->QUERY_SQL($sql,'artica_backup');
		}	

	if(!$this->FIELD_EXISTS("nics","NETWORK","artica_backup")){
		$sql="ALTER TABLE `nics` ADD `NETWORK` varchar(55) NOT NULL";
		$this->QUERY_SQL($sql,'artica_backup');
		}	
	if(!$this->FIELD_EXISTS("nics","IsBridge","artica_backup")){
		$sql="ALTER TABLE `nics` ADD `IsBridge` INT( 1 ) NOT NULL DEFAULT '0'";
		$this->QUERY_SQL($sql,'artica_backup');
	}

	if(!$this->FIELD_EXISTS("nics","Bridged","artica_backup")){
		$sql="ALTER TABLE `nics` ADD `Bridged` INT( 1 ) NOT NULL DEFAULT '0'";
		$this->QUERY_SQL($sql,'artica_backup');
	}	

	if(!$this->FIELD_EXISTS("nics","BridgedTo","artica_backup")){
		$sql="ALTER TABLE `nics` ADD `BridgedTo` VARCHAR( 50 ) NOT NULL ,ADD INDEX ( `BridgedTo` )";
		$this->QUERY_SQL($sql,'artica_backup');
	}	

	
	if(!$this->TABLE_EXISTS('crossroads_smtp','artica_backup')){		
		$sql="CREATE TABLE `artica_backup`.`crossroads_smtp` (`ipaddr` VARCHAR( 90 ) NOT NULL ,`parameters` BLOB NOT NULL ,PRIMARY KEY ( `ipaddr` ))";
		$this->QUERY_SQL($sql,'artica_backup');
	}

}
function check_vps_tables(){
	$error=array();
	if(!$this->TABLE_EXISTS('lxc_machines','artica_backup')){		
		$sql="CREATE TABLE IF NOT EXISTS `lxc_machines` (
			  `ID` int(2) NOT NULL AUTO_INCREMENT,
			  `uid` varchar(255) NOT NULL,
			  `machine_name` varchar(255) NOT NULL,
			  `hostname` varchar(125) NOT NULL,
			  `state` varchar(40) NOT NULL,
			  `enabled` int(1) NOT NULL DEFAULT '1',
			  `ipaddr` varchar(40) NOT NULL,
			  `template` varchar(60) NOT NULL,
			  `autostart` int(1) NOT NULL,
			  `rootpwd` varchar(255) NOT NULL,
			  `root_directory` varchar(255) NOT NULL,
			  `events` blob NOT NULL,
			  `VirtualInterface` varchar(20) NOT NULL,
			  `cgroup` BLOB NOT NULL,
			  `ChangeMac` int(1) NOT NULL,
			  `MacAddr` VARCHAR( 55 ),
			  `UsePhys` INT( 1 ) NOT NULL DEFAULT '0',
			  `PhysNic` VARCHAR( 15 ) NOT NULL,
			  PRIMARY KEY (`ID`),
			  UNIQUE KEY `MacAddr` (`MacAddr`),
			  UNIQUE KEY `PhysNic` (`PhysNic`),
			  KEY `enabled` (`enabled`),
			  KEY `template` (`template`),
			  KEY `autostart` (`autostart`),
			  KEY `hostname` (`hostname`),
			  KEY `uid` (`uid`),
			  KEY `VirtualInterface` (`VirtualInterface`)
			) ";
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){
				$error[]="$this->mysql_error (line ".__LINE__.")";
				writelogs("Fatal: $this->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
			}
			
		if(!$this->FIELD_EXISTS("lxc_machines","events","artica_backup")){	
			$sql="ALTER TABLE `lxc_machines` ADD `events` BLOB NOT NULL";
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){$error[]="$this->mysql_error (line ".__LINE__.")";}
		}
		
		if(!$this->FIELD_EXISTS("lxc_machines","MacAddr","artica_backup")){	
			$sql="ALTER TABLE `lxc_machines` ADD `MacAddr` VARCHAR( 55 ),ADD UNIQUE `MacAddr` (`MacAddr`)";
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){$error[]="$this->mysql_error (line ".__LINE__.")";}
		}	

		if(!$this->FIELD_EXISTS("lxc_machines","ChangeMac","artica_backup")){	
			$sql="ALTER TABLE `lxc_machines` ADD `ChangeMac` int(1)";
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){$error[]="$this->mysql_error (line ".__LINE__.")";}
		}		

		if(!$this->FIELD_EXISTS("lxc_machines","VirtualInterface","artica_backup")){	
			$sql="ALTER TABLE `lxc_machines` ADD `VirtualInterface` VARCHAR( 20 ) NOT NULL";
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){$error[]="$this->mysql_error (line ".__LINE__.")";}
		}
		
		if(!$this->FIELD_EXISTS("lxc_machines","cgroup","artica_backup")){	
			$sql="ALTER TABLE `lxc_machines` ADD `cgroup` BLOB NOT NULL ";
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){$error[]="$this->mysql_error (line ".__LINE__.")";}
		}		
		
		if(!$this->FIELD_EXISTS("lxc_machines","ChangeMac","artica_backup")){	
			$sql="ALTER TABLE `lxc_machines` ADD `ChangeMac` INT( 1 ) NOT NULL DEFAULT '0',
			ADD `MacAddr` VARCHAR( 55 ) NOT NULL ,ADD UNIQUE (`MacAddr`)";
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){$error[]="$this->mysql_error (line ".__LINE__.")";}
		}	
		
		if(!$this->FIELD_EXISTS("lxc_machines","UsePhys","artica_backup")){	
			$sql="ALTER TABLE `lxc_machines` ADD `UsePhys` INT( 1 ) NOT NULL DEFAULT '0', ADD `PhysNic` VARCHAR( 15 ) NOT NULL,ADD UNIQUE (`PhysNic`)";
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){$error[]="$this->mysql_error (line ".__LINE__.")";}
		}		

		if(!$this->FIELD_EXISTS("lxc_machines","UsePhys","artica_backup")){	
			$sql="ALTER TABLE `lxc_machines` ADD `UsePhys` INT( 1 ) NOT NULL DEFAULT '0'";
			$this->QUERY_SQL($sql,'artica_backup');
			if(!$this->ok){$error[]="$this->mysql_error (line ".__LINE__.")";}
		}	
		
		
		if(count($error)>0){
			writelogs(@implode("\n",$error),__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			return @implode("<br>",$error);
		}
	}
	public function Check_mldonkey_table(){
		if(!$this->TABLE_EXISTS('mldonkey','artica_backup')){	
		$sql="CREATE TABLE `artica_backup`.`mldonkey` (
			`ID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`schedule_time` INT( 10 ) NOT NULL ,
			`parameters` MEDIUMTEXT NOT NULL ,
			INDEX ( `schedule_time` ))";
			$this->QUERY_SQL($sql,"artica_backup");
		}
		
	}

    public function CheckSnort(){
    	
    	if(!$this->DATABASE_EXISTS("snort")){$this->CREATE_DATABASE("snort");}
    	
    	
		if(!$this->TABLE_EXISTS('schema','snort')){	
			$sql="CREATE TABLE `schema` ( vseq        INT      UNSIGNED NOT NULL,
		                      ctime       DATETIME NOT NULL,
		                      PRIMARY KEY (vseq));";
			
			$this->QUERY_SQL($sql,"snort");
			$this->QUERY_SQL("INSERT INTO `schema`  (vseq, ctime) VALUES ('107', now());","snort");       
		}
		
		if(!$this->TABLE_EXISTS('event','snort')){	
			$sql="CREATE TABLE event  ( sid 	  INT 	   UNSIGNED NOT NULL,
                      cid 	  INT 	   UNSIGNED NOT NULL,
                      signature   INT      UNSIGNED NOT NULL, 
                      timestamp 	   DATETIME NOT NULL,
                      PRIMARY KEY (sid,cid),
                      INDEX       sig (signature),
                      INDEX       time (timestamp));";		
		$this->QUERY_SQL($sql,"snort");
		}
	




	if(!$this->TABLE_EXISTS('signature','snort')){	
			$sql="CREATE TABLE signature ( sig_id       INT          UNSIGNED NOT NULL AUTO_INCREMENT,
                         sig_name     VARCHAR(255) NOT NULL,
                         sig_class_id INT          UNSIGNED NOT NULL,
                         sig_priority INT          UNSIGNED,
                         sig_rev      INT          UNSIGNED,
                         sig_sid      INT          UNSIGNED,
                         sig_gid      INT          UNSIGNED,
                         PRIMARY KEY (sig_id),
                         INDEX   sign_idx (sig_name(20)),
                         INDEX   sig_class_id_idx (sig_class_id));";		
		$this->QUERY_SQL($sql,"snort");
		}

	if(!$this->TABLE_EXISTS('sig_reference','snort')){	
			$sql="CREATE TABLE sig_reference (sig_id  INT    UNSIGNED NOT NULL,
                            ref_seq INT    UNSIGNED NOT NULL,
                            ref_id  INT    UNSIGNED NOT NULL,
                            PRIMARY KEY(sig_id, ref_seq));";		
		$this->QUERY_SQL($sql,"snort");
		}

	if(!$this->TABLE_EXISTS('reference','snort')){	
			$sql="CREATE TABLE reference (  ref_id        INT         UNSIGNED NOT NULL AUTO_INCREMENT,
                          ref_system_id INT         UNSIGNED NOT NULL,
                          ref_tag       TEXT NOT NULL,
                          PRIMARY KEY (ref_id));";		
		$this->QUERY_SQL($sql,"snort");
		}

if(!$this->TABLE_EXISTS('reference_system','snort')){	
			$sql="CREATE TABLE reference_system ( ref_system_id   INT         UNSIGNED NOT NULL AUTO_INCREMENT,
                                ref_system_name VARCHAR(20),
                                PRIMARY KEY (ref_system_id));";		
		$this->QUERY_SQL($sql,"snort");
		}

if(!$this->TABLE_EXISTS('sig_class','snort')){	
			$sql="CREATE TABLE sig_class ( sig_class_id        INT    UNSIGNED NOT NULL AUTO_INCREMENT,
                         sig_class_name      VARCHAR(60) NOT NULL,
                         PRIMARY KEY (sig_class_id),
                         INDEX       (sig_class_id),
                         INDEX       (sig_class_name));";		
		$this->QUERY_SQL($sql,"snort");
		}

# store info about the sensor supplying data
if(!$this->TABLE_EXISTS('sensor','snort')){	
			$sql="CREATE TABLE sensor ( sid	  INT 	   UNSIGNED NOT NULL AUTO_INCREMENT,
                      hostname    TEXT,
                      interface   TEXT,
                      filter	  TEXT,
                      detail	  TINYINT,
                      encoding	  TINYINT,
                      last_cid    INT      UNSIGNED NOT NULL,
                      PRIMARY KEY (sid));";		
		$this->QUERY_SQL($sql,"snort");
		}

# All of the fields of an ip header
if(!$this->TABLE_EXISTS('iphdr','snort')){	
			$sql="CREATE TABLE iphdr  ( sid 	  INT 	   UNSIGNED NOT NULL,
                      cid 	  INT 	   UNSIGNED NOT NULL,
                      ip_src      INT      UNSIGNED NOT NULL,
                      ip_dst      INT      UNSIGNED NOT NULL,
                      ip_ver      TINYINT  UNSIGNED,
                      ip_hlen     TINYINT  UNSIGNED,
                      ip_tos  	  TINYINT  UNSIGNED,
                      ip_len 	  SMALLINT UNSIGNED,
                      ip_id    	  SMALLINT UNSIGNED,
                      ip_flags    TINYINT  UNSIGNED,
                      ip_off      SMALLINT UNSIGNED,
                      ip_ttl   	  TINYINT  UNSIGNED,
                      ip_proto 	  TINYINT  UNSIGNED NOT NULL,
                      ip_csum 	  SMALLINT UNSIGNED,
                      PRIMARY KEY (sid,cid),
                      INDEX ip_src (ip_src),
                      INDEX ip_dst (ip_dst));";		
		$this->QUERY_SQL($sql,"snort");
		}

# All of the fields of a tcp header
if(!$this->TABLE_EXISTS('tcphdr','snort')){	
			$sql="CREATE TABLE tcphdr(  sid 	  INT 	   UNSIGNED NOT NULL,
                      cid 	  INT 	   UNSIGNED NOT NULL,
                      tcp_sport   SMALLINT UNSIGNED NOT NULL,
                      tcp_dport   SMALLINT UNSIGNED NOT NULL,
                      tcp_seq     INT      UNSIGNED,
                      tcp_ack     INT      UNSIGNED,
                      tcp_off     TINYINT  UNSIGNED,
                      tcp_res     TINYINT  UNSIGNED,
                      tcp_flags   TINYINT  UNSIGNED NOT NULL,
                      tcp_win     SMALLINT UNSIGNED,
                      tcp_csum    SMALLINT UNSIGNED,
                      tcp_urp     SMALLINT UNSIGNED,
                      PRIMARY KEY (sid,cid),
                      INDEX       tcp_sport (tcp_sport),
                      INDEX       tcp_dport (tcp_dport),
                      INDEX       tcp_flags (tcp_flags));";		
		$this->QUERY_SQL($sql,"snort");
		}

# All of the fields of a udp header
if(!$this->TABLE_EXISTS('udphdr','snort')){	
			$sql="CREATE TABLE udphdr(  sid 	  INT 	   UNSIGNED NOT NULL,
                      cid 	  INT 	   UNSIGNED NOT NULL,
                      udp_sport   SMALLINT UNSIGNED NOT NULL,
                      udp_dport   SMALLINT UNSIGNED NOT NULL,
                      udp_len     SMALLINT UNSIGNED,
                      udp_csum    SMALLINT UNSIGNED,
                      PRIMARY KEY (sid,cid),
                      INDEX       udp_sport (udp_sport),
                      INDEX       udp_dport (udp_dport));";		
		$this->QUERY_SQL($sql,"snort");
		}

# All of the fields of an icmp header
if(!$this->TABLE_EXISTS('icmphdr','snort')){	
			$sql="CREATE TABLE icmphdr( sid 	  INT 	   UNSIGNED NOT NULL,
                      cid 	  INT  	   UNSIGNED NOT NULL,
                      icmp_type   TINYINT  UNSIGNED NOT NULL,
                      icmp_code   TINYINT  UNSIGNED NOT NULL,
                      icmp_csum   SMALLINT UNSIGNED,
                      icmp_id     SMALLINT UNSIGNED,
                      icmp_seq    SMALLINT UNSIGNED,
                      PRIMARY KEY (sid,cid),
                      INDEX       icmp_type (icmp_type));";		
		$this->QUERY_SQL($sql,"snort");
		}

# Protocol options
if(!$this->TABLE_EXISTS('opt','snort')){	
			$sql="CREATE TABLE opt    ( sid         INT      UNSIGNED NOT NULL,
                      cid         INT      UNSIGNED NOT NULL,
                      optid       INT      UNSIGNED NOT NULL,
                      opt_proto   TINYINT  UNSIGNED NOT NULL,
                      opt_code    TINYINT  UNSIGNED NOT NULL,
                      opt_len     SMALLINT,
                      opt_data    TEXT,
                      PRIMARY KEY (sid,cid,optid));";		
		$this->QUERY_SQL($sql,"snort");
		}

# Packet payload
if(!$this->TABLE_EXISTS('data','snort')){	
			$sql="CREATE TABLE data   ( sid           INT      UNSIGNED NOT NULL,
                      cid           INT      UNSIGNED NOT NULL,
                      data_payload  TEXT,
                      PRIMARY KEY (sid,cid));";		
		$this->QUERY_SQL($sql,"snort");
		}

# encoding is a lookup table for storing encoding types
if(!$this->TABLE_EXISTS('encoding','snort')){	
			$sql="CREATE TABLE encoding(encoding_type TINYINT UNSIGNED NOT NULL,
                      encoding_text TEXT NOT NULL,
                      PRIMARY KEY (encoding_type));";		
		$this->QUERY_SQL($sql,"snort");
		$this->QUERY_SQL("INSERT INTO encoding (encoding_type, encoding_text) VALUES (0, 'hex');","snort");
		$this->QUERY_SQL("INSERT INTO encoding (encoding_type, encoding_text) VALUES (1, 'base64');","snort");
		$this->QUERY_SQL("INSERT INTO encoding (encoding_type, encoding_text) VALUES (2, 'ascii');","snort");
		}


# detail is a lookup table for storing different detail levels
if(!$this->TABLE_EXISTS('detail','snort')){	
			$sql="CREATE TABLE detail  (detail_type TINYINT UNSIGNED NOT NULL,
                      detail_text TEXT NOT NULL,
                      PRIMARY KEY (detail_type));";		
		$this->QUERY_SQL($sql,"snort");
		$this->QUERY_SQL("INSERT INTO detail (detail_type, detail_text) VALUES (0, 'fast');","snort");
		$this->QUERY_SQL("INSERT INTO detail (detail_type, detail_text) VALUES (1, 'full');","snort");
    	
    }
   }
	
}

?>
