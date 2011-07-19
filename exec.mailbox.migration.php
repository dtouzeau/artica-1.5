<?php
if(!function_exists("posix_getuid")){echo "posix_getuid !! not exists\n";}
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}

include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");
include_once(dirname(__FILE__)."/ressources/class.user.inc");

if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
if($argv[1]=="--files"){import_files();die();}
if($argv[1]=="--accounts"){accounts();die();}
if($argv[1]=="--member"){member($argv[2]);die();}
if($argv[1]=="--check"){checkTask($argv[2]);die();}



function import_files(){
	writelogs("Starting importing files to migrate",__FUNCTION__,__FILE__,__LINE__);
	$sql="SELECT ID,filepath,local_domain,ou FROM mbx_migr WHERE imported=0 and finish=0";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){writelogs("$sql $q->mysql_error",__FUNCTION__,__FILE__,__LINE__);}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if(!is_file($ligne["filepath"])){
			$sql="UPDATE mbx_migr SET imported=1,finish=1 WHERE ID={$ligne["ID"]}";
			$q->QUERY_SQL($sql,"artica_backup");
			continue;
		}
		
		import_single_file($ligne["filepath"],$ligne["ID"],$ligne["ou"],$ligne["local_domain"]);
		
	}
	
}

function import_single_file($filepath,$ID,$ou,$localdomain){
	writelogs("$ID:: importing $filepath for $ou",__FUNCTION__,__FILE__,__LINE__);
	
	if($ou==null){writelogs("$ID:: OU IS NULL !!! ",__FUNCTION__,__FILE__,__LINE__);return;}
	$GLOBALS["OU"]=$ou;
	$f=explode("\n",@file_get_contents($filepath));
	$ldap=new clladp();
	$count=0;
	while (list ($num, $line) = each ($f) ){
		if($line==null){continue;}
		$tbl=explode(";",$line);
		$account=$tbl[0];
		$password=$tbl[1];
		$imap_server=$tbl[2];
		$new_uid=null;
		$uid=null;
		$usessl=0;
		$zmd5=md5("$account$imap_server");
		if(preg_match("#(.+?)@(.+?)$#",trim($account),$re)){$new_uid=$re[1];}else{$new_uid=$account;}
		
		writelogs("$ID:: local user=$new_uid@$localdomain",__FUNCTION__,__FILE__,__LINE__);
		
		
		$uid=$ldap->uid_from_email("$new_uid@$localdomain");
		
		if($uid==null){
			if(preg_match("#(.+?)@(.+?)$#",trim($new_uid),$re)){$new_uid=$re[1];}
			writelogs("$ID:: Add uid=\"$new_uid\" ou={$GLOBALS["OU"]} mail=$new_uid@$localdomain",__FUNCTION__,__FILE__,__LINE__);	
			$user_uid=new user();
			$user_uid->uid=$new_uid;
			$user_uid->ou=$GLOBALS["OU"];
			$user_uid->password=$password;
			$user_uid->mail="$new_uid@$localdomain";
			$user_uid->domainname=$localdomain;
			if(!$user_uid->add_user()){writelogs("$ID:: failed to add $user_uid->uid in LDAP database",__FUNCTION__,__FILE__,__LINE__);continue;}
			else{$new_uid=$user_uid->uid;}
		}else{
			
			$new_uid=$uid;
		}
		$count++;
		writelogs("$ID:: local uid:$uid",__FUNCTION__,__FILE__,__LINE__);
		
		if(preg_match("#ssl:(.+?)$#",$imap_server,$re)){$usessl=1;$imap_server=$re[1];}
		
		
		
		$sql="INSERT INTO mbx_migr_users (`zmd5`,`mbx_migr_id`,`ou`, `imap_server`,`usessl`,`username`,`password`,`uid`)
		VALUES('$zmd5','$ID','{$GLOBALS["OU"]}','$imap_server','$usessl','$account','$password','$new_uid')";
		writelogs("$ID:: \"$sql\"",__FUNCTION__,__FILE__,__LINE__);
		
		
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){writelogs("$ID:: $q->mysql_error \"$sql\"",__FUNCTION__,__FILE__,__LINE__);}
	}
	
	
	$sql="UPDATE mbx_migr SET imported=1,members_count=$count WHERE ID=$ID";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){writelogs("$ID:: $q->mysql_error \"$sql\"",__FUNCTION__,__FILE__,__LINE__);return;}
	
	$users=new usersMenus();
	if(!$users->offlineimap_installed){
		shell_exec("/usr/share/artica-postfix/bin/artica-make APP_OFFLINEIMAP");
	}
	
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." ".__FILE__." --accounts");

}

function accounts(){
	$sql="SELECT * FROM mbx_migr_users WHERE imported=0";
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){writelogs("$sql $q->mysql_error",__FUNCTION__,__FILE__,__LINE__);}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$PID=$ligne["PID"];
		if($PID<>null){
			if($unix->process_exists($PID)){continue;}
		}
		$cmd="$nohup ".LOCATE_PHP5_BIN2()." ".__FILE__." --member {$ligne["zmd5"]} >/dev/null 2>&1 &";
		shell_exec("$cmd");
	}	
}


function member($ID){
	$sql="SELECT * FROM mbx_migr_users WHERE zmd5='$ID'";
	if($GLOBALS["VERBOSE"]){echo "ID:$ID\n";}
	if($GLOBALS["VERBOSE"]){echo "$sql\n";}
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	$pid=$ligne["PID"];
	$TASKID=$ligne["mbx_migr_id"];
	$unix=new unix();
	if($pid<>null){if($unix->process_exists($pid)){writelogs("$ID:: $pid already executed",__FUNCTION__,__FILE__,__LINE__);die();}}
	$pid=getmypid();
	$sql="UPDATE mbx_migr_users SET PID='$pid' WHERE zmd5='$ID'";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){writelogs("$ID:: $q->mysql_error \"$sql\"",__FUNCTION__,__FILE__,__LINE__);return;}
	
	$imap_server=$ligne["imap_server"];
	$uid=$ligne["uid"];
	$usessl=$ligne["usessl"];
	$remote_username=$ligne["username"];
	$ct=new user($uid);
	
	
		$offlineImapConf[]="[general]";
		$offlineImapConf[]="accounts = $remote_username";
		$offlineImapConf[]="maxsyncaccounts = 1";
		$offlineImapConf[]="ui =Noninteractive.Basic, Noninteractive.Quiet";
		$offlineImapConf[]="ignore-readonly = no";
		$offlineImapConf[]="socktimeout = 60";
		$offlineImapConf[]="fsync = true";
		$offlineImapConf[]="";
		$offlineImapConf[]="";
		$offlineImapConf[]="[ui.Curses.Blinkenlights]";
		$offlineImapConf[]="statuschar = .";
		$offlineImapConf[]="";
		$offlineImapConf[]="[Account $remote_username]";
		$offlineImapConf[]="localrepository = TargetServer";
		$offlineImapConf[]="remoterepository = SourceServer";
		$offlineImapConf[]="";
		$offlineImapConf[]="[Repository TargetServer]";
		$offlineImapConf[]="type = IMAP";
		$offlineImapConf[]="remotehost = 127.0.0.1";
		$offlineImapConf[]="ssl = 0";
		$offlineImapConf[]="remoteport = 143";
		$offlineImapConf[]="remoteuser = $uid";
		$offlineImapConf[]="remotepass = $ct->password";
		$offlineImapConf[]="maxconnections = 1";
		$offlineImapConf[]="holdconnectionopen = no";
		$offlineImapConf[]="expunge = no";
		$offlineImapConf[]="subscribedonly = no";
		
		
		$offlineImapConf[]="";
		$offlineImapConf[]="";
		$offlineImapConf[]="[Repository SourceServer]";
		$offlineImapConf[]="type = IMAP";
		$offlineImapConf[]="remotehost = $imap_server";
		if($usessl==1){
			$offlineImapConf[]="ssl = 1";
			$offlineImapConf[]="remoteport = 993";
		}else{
			$offlineImapConf[]="ssl = 0";
			$offlineImapConf[]="remoteport = 143";	
		}
		$offlineImapConf[]="remoteuser = {$ligne["username"]}";
		$offlineImapConf[]="remotepass = {$ligne["password"]}";
		$offlineImapConf[]="maxconnections = 1";
		$offlineImapConf[]="holdconnectionopen = no";
		$offlineImapConf[]="expunge = no";
		$offlineImapConf[]="subscribedonly = no";
	
		@mkdir("/etc/artica-postfix/offline-imap",666,true);
		@file_put_contents("/etc/artica-postfix/offline-imap/$ID.cfg",@implode("\n",$offlineImapConf));
		$unix=new unix();
		$t1=time();
		$NICE=EXEC_NICE();
		$offlineimap=$unix->find_program("offlineimap");
		
		if(is_file("$offlineimap")){
				exec("$NICE$offlineimap -c /etc/artica-postfix/offline-imap/$ID.cfg 2>&1",$results);
				$t2=time();
				
				$messages_count=0;
				while (list ($num, $pp) = each ($results) ){
					if(preg_match("#WARNING:#",$pp)){
						$tolog[]=$pp;
					}
					
					if(preg_match("#Syncing#",$pp)){
						$tolog[]=$pp;
					}
					
					if(preg_match("#Copy message#",$pp)){$messages_count++;}
					
					
				}
				
			$tolog[]="Messages replicated.: $messages_count";
			$tolog[]="Execution time......: ".distanceOfTimeInWords($t1,$t2);

	}else{
		$tolog[]="UNABLE TO STAT OFFLINEIMAP TOOL !!!";
		}
		
		
	$sql="UPDATE mbx_migr_users SET events='".addslashes(@implode("\n",$tolog))."',
	imported=1
	WHERE zmd5='$ID'";
	if($GLOBALS["VERBOSE"]){echo "$sql\n";}
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){writelogs("$ID:: $q->mysql_error \"$sql\"",__FUNCTION__,__FILE__,__LINE__);return;}
	checkTask($TASKID);
	
}

function checkTask($ID){
	$sql="SELECT COUNT(*) AS tcount FROM mbx_migr_users WHERE mbx_migr_id=$ID AND imported=0";
	$q=new mysql();
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	if($ligne["tcount"]==null){$ligne["tcount"]=0;}
	if($ligne["tcount"]==0){
		$sql="UPDATE mbx_migr SET finish=1 WHERE ID=$ID";
		$q->QUERY_SQL($sql,'artica_backup');
	}
	
}

