<?php

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.acls.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");

if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;}

if($argv[1]=='--acls'){applyAcls();die();}
if($argv[1]=='--acls-single'){ApplySingleAcls_cmdline($argv[2]);die();}


function applyAcls(){
	
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$unix=new unix();
	if($unix->process_exists(@file_get_contents("$pidfile"))){
		echo "Already process exists\n";
		return;
	}
	
	@file_put_contents($pidfile,getmypid());
	
	$sql="SELECT `directory` FROM acl_directories";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,'artica_backup');
	if(!$q->ok){if($GLOBALS["VERBOSE"]){echo $q->mysql_error."\n";return;}}
	
	$count=mysql_num_rows($results);
	echo "Starting......: acls $count items\n";
	if($count==0){return;}
	

	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		ApplySingleAcls($ligne["directory"]);
	}
	
	
}

function ApplySingleAcls_cmdline($md5){
	$q=new mysql();
	$ligne=mysql_fetch_array($q->QUERY_SQL("SELECT `directory` FROM acl_directories WHERE `md5`='$md5'","artica_backup"));
	ApplySingleAcls($ligne["directory"]);
}


function ApplySingleAcls($directory){
		$unix=new unix();	
		$chmod_bin=$unix->find_program("chmod");
		$setfacl_bin=$unix->find_program("setfacl");	
		
		$recursive=null;
		$chmod=null;
		$q=new mysql();
		$dir=$unix->shellEscapeChars($directory);
		
		
		if(!is_dir($directory)){
			echo "Starting......: acls $directory no such directory\n";
			$q->QUERY_SQL("DELETE FROM acl_directories WHERE `directory`='$directory'");
			if(!$q->ok){echo $q->mysql_error."\n";}
			return;
		}
		
		$acls=new aclsdirs($directory);
		
		echo "Starting......: acls \"$dir\" directory\n";
		
		if(!is_numeric($acls->chmod_octal)){$events[]="octal is not a numeric value...";}
		if(is_numeric($acls->chmod_octal)){
			$events[]="octal \"$acls->chmod_octal\"";
			if(chmod_recursive==1){$events[]="Recursive mode";$recursive=" -R ";}
			$chmod=" ".$acls->chmod_octal;
		}
		
		if($chmod<>null){
			$cmd="$chmod_bin$recursive$chmod $dir";
			$events[]="$cmd";
			exec("$chmod_bin$recursive$chmod $dir 2>&1",$events);
		}
		
		if(strlen($setfacl_bin)<3){
			$events[]="ERROR: setfacl no such binary file";
			$events_text=@implode("\n",$events);
			if($GLOBALS["VERBOSE"]){echo $events_text."\n";}			
			$sql="UPDATE acl_directories SET events='".addslashes($events_text)."' WHERE `md5`='$acls->md5'";
			if($GLOBALS["VERBOSE"]){echo $sql."\n";}
			$q->QUERY_SQL($sql,"artica_backup");
			if(!$q->ok){echo "$q->mysql_error\n";}
			return;
			
		}
		
		$cmd="$setfacl_bin -b $dir 2>&1";
		$events[]=$cmd;
		exec("$cmd",$events);
		
		if($GLOBALS["VERBOSE"]){
			if(!is_array($acls->acls_array)){echo "acls_array not an Array\n";}
		}
		
		print_r($acls->acls_array);
		
		if(is_array($acls->acls_array["GROUPS"])){
			while (list ($groupname, $array) = each ($acls->acls_array["GROUPS"]) ){	
				$perms=array();
				$perms_strings=null;
				$recurs=null;
				if($array["r"]==1){$perms[]="r";}
				if($array["w"]==1){$perms[]="w";}
				if($array["x"]==1){$perms[]="x";}
				$perms_strings=@implode("",$perms);
				if($perms_strings==null){
					$events[]="No permissions set for $groupname";
					continue;
				}
				if($acls->acls_array["recursive"]==1){$recurs="-R ";}
				$cmd="$setfacl_bin $recurs-m g:\"$groupname\":$perms_strings $dir 2>&1";
				$events[]=$cmd;
				exec("$cmd",$events);
				
				
				if($acls->acls_array["default"]==1){
					$cmd="$setfacl_bin $recurs-m d:g:\"$groupname\":$perms_strings $dir 2>&1";
					$events[]=$cmd;
					exec("$cmd",$events);					
				}
			}	
		
		}else{
			$events[]="Groups: No acls\n";
		}
		
	if(is_array($acls->acls_array["MEMBERS"])){
			while (list ($member, $array) = each ($acls->acls_array["MEMBERS"]) ){	
				$perms=array();
				$perms_strings=null;
				$recurs=null;
				if($array["r"]==1){$perms[]="r";}
				if($array["w"]==1){$perms[]="w";}
				if($array["x"]==1){$perms[]="x";}
				$perms_strings=@implode("",$perms);
				if($perms_strings==null){
					$events[]="No permissions set for $member";
					continue;
				}
				if($aclsClass->acls_array["recursive"]==1){$recurs="R";}
				$cmd="$setfacl_bin -m$recurs u:\"$member\":$perms_strings $dir 2>&1";
				$events[]=$cmd;
				exec("$cmd",$events);
				
				
				if($aclsClass->acls_array["default"]==1){
					$cmd="$setfacl_bin -m$recurs d:u:\"$member\":$perms_strings $dir 2>&1";
					$events[]=$cmd;
					exec("$cmd",$events);					
				}
			}	
		
		}else{
			$events[]="Members: No acls\n";
		}
		$events_text=@implode("\n",$events);
		if($GLOBALS["VERBOSE"]){echo $events_text."\n";}
		
		$sql="UPDATE acl_directories SET events='".addslashes($events_text)."' WHERE `md5`='$acls->md5'";
		$q->QUERY_SQL($sql,"artica_backup");		
			
	
}