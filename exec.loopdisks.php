<?php

$GLOBALS["FORCE"]=false;
if(is_array($argv)){
	if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
	if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;}
	if($GLOBALS["VERBOSE"]){ini_set('html_errors',0);ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);}
}
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
$GLOBALS["posix_getuid"]=0;
include_once(dirname(__FILE__) . '/ressources/class.users.menus.inc');
include_once(dirname(__FILE__) . '/ressources/class.mysql.inc');
include_once(dirname(__FILE__) . '/ressources/class.autofs.inc');
include_once(dirname(__FILE__) . '/ressources/logs.inc');
include_once(dirname(__FILE__) . '/framework/class.unix.inc');
include_once(dirname(__FILE__) . '/framework/frame.class.inc');
$unix=new unix();
$GLOBALS["losetup"]=$unix->find_program("losetup");

if($argv[1]=="--remove"){remove($argv[2]);die();}


build();


function build(){
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".pid";
	$oldpid=@file_get_contents($pidfile);
	$unix=new unix();
	if($unix->process_exists($oldpid)){
		writelogs("Already process exists pid $oldpid",__FUNCTION__,__FILE__,__LINE__);
		echo "Already process exists pid $oldpid\n";
		return;
	}
	
	@file_put_contents($pidfile,getmypid());
	
	
	$q=new mysql();
	$sql="SELECT * FROM loop_disks ORDER BY `size` DESC";$sql="SELECT * FROM loop_disks ORDER BY `size` DESC";
	$results=$q->QUERY_SQL($sql,"artica_backup");	
	if(!$q->ok){echo "$q->mysql_error\n";return;}
	
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$path=$ligne["path"];
		$size=$ligne["size"];
		writelogs("check $path ($size)",__FUNCTION__,__FILE__,__LINE__);
		if(!stat_system($path)){
			writelogs("buil_dd $path ($size)",__FUNCTION__,__FILE__,__LINE__);
			if(!build_dd($path,$size)){continue;}
		}
		$GetLoops=GetLoops();
		if(!stat_system($path)){
			writelogs("$path no such file",__FUNCTION__,__FILE__,__LINE__);
			continue;
		}
		if($GetLoops[$path]==null){
			writelogs("$path no such loop",__FUNCTION__,__FILE__,__LINE__);
			if(!build_loop($path)){writelogs("$path unable to create loop",__FUNCTION__,__FILE__,__LINE__);continue;}
			$GetLoops=GetLoops();
			if($GetLoops[$path]==null){writelogs("$path no such loop",__FUNCTION__,__FILE__,__LINE__);continue;}
		}
		
		writelogs("$path loop={$GetLoops[$path]}",__FUNCTION__,__FILE__,__LINE__);
		$sql="UPDATE loop_disks SET loop_dev='{$GetLoops[$path]}' WHERE `path`='$path'";
		$q=new mysql();
		$q->QUERY_SQL($sql,'artica_backup');
		if(!$q->ok){echo "$q->mysql_error\n";continue;}
		$dev=$GetLoops[$path];
		echo "Starting......: $path is $dev\n";	
		if(!ifFileSystem($dev)){if(!mke2fs($dev)){continue;}}
		$uuid=Getuuid($dev);
		echo "Starting......: $dev uuid=$uuid\n";
		if($uuid==null){continue;}		
		$autofs=new autofs();
		$autofs->uuid=$uuid;
		$autofs->by_uuid_addmedia($ligne["disk_name"],"auto");
		shell_exec("/etc/init.d/autofs reload");
	}	
	
	
}

function GetLastLo(){
	exec("{$GLOBALS["losetup"]} -f 2>&1",$results);
	return trim(@implode("",$results));
}

function mke2fs($dev){
	$debug=$GLOBALS["VERBOSE"];
	$unix=new unix();
	$mkfs_ext4=$unix->find_program("mkfs.ext4");
		
	if(!is_file($mkfs_ext4)){$mkfs_ext4=$unix->find_program("mkfs.ext3");}
	
	if(!$unix->IsExt4()){
		$mkfs_ext4=$unix->find_program("mkfs.ext3");
	}	
	
	echo "Starting......: $dev formatting...\n";		
	$cmd="$mkfs_ext4 -q $dev 2>&1";
	exec($cmd,$results);
	if($debug){echo "mke2fs($dev) -> $cmd ". count($results)." rows\n";}	
	if($debug){while (list ($num, $line) = each ($results)){echo "mke2fs() -> $line\n";}}
	if(ifFileSystem($dev)){return true;}
}

function build_dd($path,$size){
	$dir=dirname($path);
	if(!is_dir($dir)){
		writelogs("$dir no such directory, create it",__FUNCTION__,__FILE__,__LINE__);
		@mkdir(dirname($path),644,true);
		
	}
	
	if(!is_dir($dir)){
		writelogs("$dir no such directory",__FUNCTION__,__FILE__,__LINE__);
		return false;
	}
	
	
	
	$unix=new unix();
	$dd=$unix->find_program("dd");
	$size=$size*1024;
	$cmd="$dd if=/dev/zero of=$path bs=1024 count=$size 2>&1";
	exec($cmd,$results);
	echo "build_dd() $cmd ". count($results)." rows\n";
	while (list ($num, $ligne) = each ($results) ){echo "build_dd() $ligne\n";}
	if(!stat_system($path)){echo "build_dd() $path no such block\n";return false;}
	if(build_loop($path)){return true;}
	}
	
function build_loop($path){
	$loop_free=GetLastLo();
	$cmd="{$GLOBALS["losetup"]} $loop_free $path 2>&1";
	exec($cmd,$results);
	echo "build_loop() $cmd ". count($results)." rows\n";
	while (list ($num, $ligne) = each ($results) ){echo "build_loop() $ligne\n";}
	$GetLoops=GetLoops();
	if($GetLoops[$path]<>null){
		echo "build_loop() done {$GetLoops[$path]}\n";
		return true;	
	}	
	return false;
}

function GetLoops(){
	$cmd="{$GLOBALS["losetup"]} -a 2>&1";
	exec($cmd,$results);	
	while (list ($num, $ligne) = each ($results) ){if(preg_match("#^(.+?):.+?\((.+?)\)#",$ligne,$re)){$array[trim($re[2])]=trim($re[1]);}}	
	return $array;
	
}

function remove($path){
	$unix=new unix();
	$umount=$unix->find_program("umount");
	$sql="SELECT * FROM loop_disks WHERE `path`='$path'";
	$q=new mysql();
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$disk_name=$ligne["disk_name"];
	$GetLoops=GetLoops();
	$dev=$GetLoops[$path];
	$uuid=Getuuid($dev);
	if($dev<>null){
		echo "Starting......: $dev umounting...\n";
		exec("$umount -l $dev 2>&1",$results);
		exec("$umount -l $dev 2>&1",$results);
		exec("$umount -l $dev 2>&1",$results);
		while (list ($num, $ligne) = each ($results) ){echo "Starting......: $dev $ligne\n";}
		
	}
	$results=array();
	if($uuid<>null){
		echo "Starting......: $dev disconnect $uuid...$disk_name\n";
		$autofs=new autofs();
		$autofs->uuid=$uuid;
		$autofs->by_uuid_removemedia($disk_name,"auto");		
	}
	echo "Starting......: $dev remove media\n";
	$cmd="{$GLOBALS["losetup"]} -d $dev 2>&1";
	exec($cmd,$results);	
	while (list ($num, $ligne) = each ($results) ){echo "Starting......: $dev $ligne\n";}	
	echo "Starting......: $dev remove file\n";
	shell_exec("/bin/rm -f $path");
	echo "Starting......: $dev remove entry in database\n";
	$sql="DELETE FROM loop_disks WHERE `path`='$path'";
	$q->QUERY_SQL($sql,"artica_backup");
	echo "Starting......: $dev removed\n";
	shell_exec("/etc/init.d/autofs restart");
	
}


function ifFileSystem($dev){
		$debug=$GLOBALS["VERBOSE"];
		$unix=new unix();
		$tune2fs=$unix->find_program("tune2fs");
		$cmd="$tune2fs -l $dev 2>&1";
		exec($cmd,$results);
		$array=array();	
		if($debug){echo "ifFileSystem($dev) -> $cmd ". count($results)." rows\n";}	
		while (list ($num, $line) = each ($results)){
			
			if(preg_match("#Filesystem magic number:\s+(.+)#i",$line,$re)){
				if($debug){echo "ifFileSystem($dev) ->  Filesystem magic number = {$re[1]}\n";}
				return true;
			}
			
		}
		if($debug){echo "ifFileSystem($dev) FALSE\n";}
		return false;
		
	}




function stat_system($path){
	exec("stat -f $path -c %b 2>&1",$results);
	$line=trim(@implode("",$results));
	if(preg_match("#^[0-9]+#",$line,$results)){return true;}
	return false;
}
function Getuuid($dev){
	$debug=$GLOBALS["VERBOSE"];
	$unix=new unix();
	$tune2fs=$unix->find_program("tune2fs");
	$cmd="$tune2fs -l $dev 2>&1";
	exec($cmd,$results);
	$array=array();	
	if($debug){echo "Getuuid($dev) -> $cmd ". count($results)." rows\n";}	
	while (list ($num, $line) = each ($results)){
		if(preg_match("#UUID:\s+(.+)#i",$line,$re)){
		if($debug){echo "Getuuid($dev) -> ". trim($re[1])."\n";}	
		return trim($re[1]);
		}
	}
		
}
?>