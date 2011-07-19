<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');
include_once(dirname(__FILE__).'/ressources/class.sockets.inc');
if(!is_file("/usr/share/artica-postfix/ressources/settings.inc")){shell_exec("/usr/share/artica-postfix/bin/process1 --force --verbose");}
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["DEBUG"]=true;$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}
if(preg_match("#--reload#",implode(" ",$argv))){$GLOBALS["RELOAD"]=true;}

if($argv[1]=="--mount"){mounts();exit;}
if($argv[1]=="--mounted"){mounted();exit;}
if($argv[1]=="--restart"){restart();exit;}



BuildConf();


function restart(){
	umounts();
	BuildConf();
}

function BuildConf(){
	
$sock=new sockets();
$unix=new unix();
$mklessfs=$unix->find_program("mklessfs");
$lessfs=$unix->find_program("lessfs");
if(!is_file($mklessfs)){
	echo "Starting......: LessFS mklessfs no such file\n";
	die();	
}
if(!is_file($mklessfs)){
	echo "Starting......: LessFS lessfs no such file\n";
	die();	
}
$arrayConf=unserialize(base64_decode($sock->GET_INFO("lessfsConf")));
if(!is_array($arrayConf)){$arrayConf=array();}


if(!is_numeric($arrayConf["DEBUG"])){$arrayConf["DEBUG"]=2;}
if(!is_numeric($arrayConf["DB_FILEBLOCK"])){$arrayConf["DB_FILEBLOCK"]=1048576;}
if(!is_numeric($arrayConf["CACHESIZE"])){$arrayConf["CACHESIZE"]=1024;}
if(!is_numeric($arrayConf["COMMIT_INTERVAL"])){$arrayConf["COMMIT_INTERVAL"]=30;}
if(!is_numeric($arrayConf["MAX_THREADS"])){$arrayConf["MAX_THREADS"]=2;}
if($arrayConf["MAIN_PATH"]==null){$arrayConf["MAIN_PATH"]="/data";}
if(!is_numeric($arrayConf["DYNAMIC_DEFRAGMENTATION"])){$arrayConf["DYNAMIC_DEFRAGMENTATION"]=1;}
if(!is_numeric($arrayConf["BACKGROUND_DELETE"])){$arrayConf["BACKGROUND_DELETE"]=1;}
if(!is_numeric($arrayConf["ENABLE_TRANSACTIONS"])){$arrayConf["ENABLE_TRANSACTIONS"]=1;}
if(!is_numeric($arrayConf["BLKSIZE"])){$arrayConf["BLKSIZE"]=131072;}
if(!is_numeric($arrayConf["REPLICATION"])){$arrayConf["REPLICATION"]=0;}
if(!is_numeric($arrayConf["REPLICATION_ENABLED"])){$arrayConf["REPLICATION_ENABLED"]=0;}
if($arrayConf["REPLICATION_ROLE"]==null){$arrayConf["REPLICATION_ROLE"]="master";}
if($arrayConf["REPLICATION_LISTEN_IP"]==null){$arrayConf["REPLICATION_LISTEN_IP"]="127.0.0.1";}
if($arrayConf["REPLICATION_PARTNER_IP"]==null){$arrayConf["REPLICATION_PARTNER_IP"]="127.0.0.1";}



if(!is_numeric($arrayConf["REPLICATION_LISTEN_PORT"])){$arrayConf["REPLICATION_LISTEN_PORT"]=102;}
if(!is_numeric($arrayConf["REPLICATION_PARTNER_PORT"])){$arrayConf["REPLICATION_PARTNER_PORT"]=102;}


if($arrayConf["DYNAMIC_DEFRAGMENTATION"]==1){$arrayConf["DYNAMIC_DEFRAGMENTATION"]="on";}else{$arrayConf["DYNAMIC_DEFRAGMENTATION"]="off";}
if($arrayConf["BACKGROUND_DELETE"]==1){$arrayConf["BACKGROUND_DELETE"]="on";}else{$arrayConf["BACKGROUND_DELETE"]="off";}
if($arrayConf["ENABLE_TRANSACTIONS"]==1){$arrayConf["ENABLE_TRANSACTIONS"]="on";}else{$arrayConf["ENABLE_TRANSACTIONS"]="off";}
if($arrayConf["REPLICATION"]==1){$arrayConf["REPLICATION"]="masterslave";}else{$arrayConf["REPLICATION"]="off";}
if($arrayConf["REPLICATION_ENABLED"]==1){$arrayConf["REPLICATION_ENABLED"]="off";}else{$arrayConf["REPLICATION_ENABLED"]="on";}



$conf[]="# Enable informational messages about compression. 0 -5";
$conf[]="DEBUG = {$arrayConf["DEBUG"]}";
$conf[]="HASHNAME=MHASH_TIGER192";
//$conf[]="#HASHNAME=MHASH_SHA256";
$conf[]="HASHLEN = 20";
$conf[]="BLOCKDATA_IO_TYPE=file_io";
$conf[]="BLOCKDATA_PATH={$arrayConf["MAIN_PATH"]}/dta/blockdata.dta";
$conf[]="BLOCKUSAGE_PATH={$arrayConf["MAIN_PATH"]}/mta";
$conf[]="DIRENT_PATH={$arrayConf["MAIN_PATH"]}/mta";
$conf[]="FILEBLOCK_PATH={$arrayConf["MAIN_PATH"]}/mta";
$conf[]="META_PATH={$arrayConf["MAIN_PATH"]}/mta";
$conf[]="HARDLINK_PATH={$arrayConf["MAIN_PATH"]}/mta";
$conf[]="SYMLINK_PATH={$arrayConf["MAIN_PATH"]}/mta";
$conf[]="FREELIST_PATH={$arrayConf["MAIN_PATH"]}/mta";
//$conf[]="BLOCKDATA_BS={$arrayConf["DB_FILEBLOCK"]}"; tokyo
$conf[]="BLOCKUSAGE_BS={$arrayConf["DB_FILEBLOCK"]}";
$conf[]="DIRENT_BS={$arrayConf["DB_FILEBLOCK"]}";
$conf[]="FILEBLOCK_BS={$arrayConf["DB_FILEBLOCK"]}";
$conf[]="META_BS={$arrayConf["DB_FILEBLOCK"]}";
$conf[]="HARDLINK_BS={$arrayConf["DB_FILEBLOCK"]}";
$conf[]="SYMLINK_BS={$arrayConf["DB_FILEBLOCK"]}";
$conf[]="FREELIST_BS={$arrayConf["DB_FILEBLOCK"]}";
$conf[]="CACHESIZE={$arrayConf["CACHESIZE"]}";
$conf[]="COMMIT_INTERVAL={$arrayConf["COMMIT_INTERVAL"]}";
$conf[]="MAX_THREADS={$arrayConf["MAX_THREADS"]}";
$conf[]="DYNAMIC_DEFRAGMENTATION={$arrayConf["DYNAMIC_DEFRAGMENTATION"]}";
$conf[]="LISTEN_IP=127.0.0.1";
$conf[]="LISTEN_PORT=100";
$conf[]="COREDUMPSIZE=2560000000";
$conf[]="SYNC_RELAX=0";
$conf[]="BACKGROUND_DELETE={$arrayConf["BACKGROUND_DELETE"]}";
$conf[]="ENCRYPT_DATA=off";
$conf[]="ENCRYPT_META=off";
$conf[]="ENABLE_TRANSACTIONS={$arrayConf["ENABLE_TRANSACTIONS"]}";
$conf[]="BLKSIZE={$arrayConf["BLKSIZE"]}";
$conf[]="COMPRESSION=qlz";
$conf[]="REPLICATION={$arrayConf["REPLICATION"]}";
$conf[]="REPLICATION_ENABLED={$arrayConf["REPLICATION_ENABLED"]}";
if($arrayConf["REPLICATION_PARTNER_IP"]<>"127.0.0.1"){
	$conf[]="REPLICATION_PARTNER_IP={$arrayConf["REPLICATION_PARTNER_IP"]}";
	$conf[]="REPLICATION_PARTNER_PORT={$arrayConf["REPLICATION_PARTNER_PORT"]}";
}
$conf[]="REPLICATION_ROLE={$arrayConf["REPLICATION_ROLE"]}";
if($arrayConf["REPLICATION_ROLE"]=="slave"){
	$conf[]="REPLICATION_LISTEN_IP={$arrayConf["REPLICATION_LISTEN_IP"]}";
	$conf[]="REPLICATION_LISTEN_PORT={$arrayConf["REPLICATION_LISTEN_PORT"]}";
}
$conf[]="#BLOCKDATA_PATH={$arrayConf["MAIN_PATH"]}/dta";
$conf[]="#BLOCKDATA_BS={$arrayConf["DB_FILEBLOCK"]}";
$conf[]="";	

if(!is_dir("{$arrayConf["MAIN_PATH"]}")){mkdir("{$arrayConf["MAIN_PATH"]}");}
if(!is_dir("{$arrayConf["MAIN_PATH"]}/dta")){mkdir("{$arrayConf["MAIN_PATH"]}/dta");}
if(!is_dir("{$arrayConf["MAIN_PATH"]}/mta")){mkdir("{$arrayConf["MAIN_PATH"]}/mta");}

@file_put_contents("/etc/lessfs.cfg",@implode("\n",$conf));
echo "Starting......: LessFS configuration done\n";

if(!is_file("{$arrayConf["MAIN_PATH"]}/mta/fileblock.tch")){
	echo "Starting......: LessFS Building filesystem\n";
	shell_exec("$mklessfs /etc/lessfs.cfg");
}

mounts();

// $lessfs /etc/lessfs.cfg /media/lessfs -o negative_timeout=0,entry_timeout=0,attr_timeout=0,use_ino,readdir_ino,default_permissions,allow_other,big_writes,max_read=65536,max_write=65536

}

function umounts(){
$sock=new sockets();
$unix=new unix();
$mklessfs=$unix->find_program("mklessfs");
$lessfs=$unix->find_program("lessfs");	
$umount=$unix->find_program("umount");
if(!is_file($mklessfs)){
	echo "Starting......: LessFS not installed\n";
	die();	
}
if(!is_file($mklessfs)){
	echo "Starting......: LessFS not installed\n";
	die();	
}
$arrayConf=unserialize(base64_decode($sock->GET_INFO("lessfsConf")));
if(!is_array($arrayConf["FOLDERS"])){return;}

$mounted=$unix->LESSFS_ARRAY();
while (list ($folder, $none) = each ($arrayConf["FOLDERS"]) ){
	if($mounted[$folder]){
		echo "Starting......: LessFS umount $folder\n";
		shell_exec("$umount $folder");
		$mounted=$unix->LESSFS_ARRAY();
		if($mounted[$folder]){
			echo "Starting......: LessFS force umount $folder\n";
			shell_exec("$umount -l $folder");
		}
	}
	
}
	
	
}



function mounts(){
	
$sock=new sockets();
$unix=new unix();
$mklessfs=$unix->find_program("mklessfs");
$lessfs=$unix->find_program("lessfs");	
$arrayConf=unserialize(base64_decode($sock->GET_INFO("lessfsConf")));
if(!is_file($mklessfs)){
	echo "Starting......: LessFS not installed\n";
	die();	
}
if(!is_file($mklessfs)){
	echo "Starting......: LessFS not installed\n";
	die();	
}
$arrayConf=unserialize(base64_decode($sock->GET_INFO("lessfsConf")));

if(!is_array($arrayConf["FOLDERS"])){return;}
$mounted=$unix->LESSFS_ARRAY();
while (list ($folder, $none) = each ($arrayConf["FOLDERS"]) ){
	if($mounted[$folder]){continue;}
	if(!is_dir($folder)){@mkdir($folder,0755);}
	$cmd="$lessfs /etc/lessfs.cfg \"$folder\" -o negative_timeout=0,entry_timeout=0,attr_timeout=0,use_ino,readdir_ino,default_permissions,allow_other,big_writes,max_read=65536,max_write=65536";
	echo "Starting......: LessFS mounting $folder\n";
	exec($cmd." 2>&1",$results);
	if(count($results)>0){while (list ($num, $line) = each ($results) ){echo "Starting......: LessFS $line\n";}}
	$mounted=$unix->LESSFS_ARRAY();
	if(!$mounted[$folder]){
		echo "Starting......: LessFS mounting $folder failed\n";
		echo "Starting......: LessFS try yourself $cmd\n";
	}
	
	for($i=0;$i<3;$i++){
		if(!file_exists("$folder/.lessfs/replication/enabled")){
			sleep(1);
			continue;
		}else{
			echo "Starting......: LessFS settings $folder/.lessfs/replication/enabled ({$arrayConf["REPLICATION"]}) done\n";
			@file_put_contents("$folder/.lessfs/replication/enabled",$arrayConf["REPLICATION"]);
			break;
		}
	}
	
	
}
}


function mounted(){
	$unix=new unix();
	print_r($unix->LESSFS_ARRAY());
	
}

?>