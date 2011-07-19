<?php
$GLOBALS["VERBOSE"]=false;
$GLOBALS["NORELOAD"]=false;
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.samba.inc');
include_once(dirname(__FILE__).'/ressources/class.autofs.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");


if(is_array($argv)){
	if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
	if(preg_match("#--no-reload#",implode(" ",$argv))){$GLOBALS["NORELOAD"]=true;}
	if($GLOBALS["VERBOSE"]){ini_set('html_errors',0);ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);}
}


if($argv[1]=="--count"){Autocount();die();}
if($argv[1]=="--davfs"){davfs();die();}



$ldap=new clladp();
$suffix="dc=organizations,$ldap->suffix";
$filter="(&(ObjectClass=SharedFolders)(SharedFolderList=*))";
$attr=array("gidNumber");

$sr =@ldap_search($ldap->ldap_connection,$suffix,$filter,$attr);
		$hash=ldap_get_entries($ldap->ldap_connection,$sr);
		for($i=0;$i<$hash["count"];$i++){
			$gpid=$hash[$i][strtolower("gidNumber")][0];
			$auto=new autofs();
			$auto->AutofsSharedDir($gpid);
			
		}


function Autocount(){
$auto=new autofs();
$hash=$auto->automounts_Browse();
$sock=new sockets();
$count=count($hash);
echo "Starting......: AutoFS $count mounted directories\n";
$sock->SET_INFO("AutoFSCountDirs",$count);
}

function davfs(){
	Autocount();
$f=array();	
$f[]="# davfs2 configuration file 2009-04-12";
$f[]="# version 9";
$f[]="# ------------------------------------";
$f[]="";
$f[]="# Copyright (C) 2006, 2007, 2008, 2009 Werner Baumann";
$f[]="";
$f[]="# Copying and distribution of this file, with or without modification, are";
$f[]="# permitted in any medium without royalty provided the copyright notice";
$f[]="# and this notice are preserved.";
$f[]="";
$f[]="";
$f[]="# Please read the davfs2.conf (5) man page for a description of the";
$f[]="# configuration options and syntax rules.";
$f[]="";
$f[]="";
$f[]="# Available options and default values";
$f[]="# ====================================";
$f[]="";
$f[]="# General Options";
$f[]="# ---------------";
$f[]="";
$f[]="# dav_user        davfs2            # system wide config file only";
$f[]="# dav_group       davfs2            # system wide config file only";
$f[]="# ignore_home                       # system wide config file only";
$f[]="# kernel_fs       fuse";
$f[]="# buf_size        16                 # KiByte";
$f[]="";
$f[]="# WebDAV Related Options";
$f[]="# ----------------------";
$f[]="";
$f[]="use_proxy       1                 # system wide config file only";
$f[]="# proxy                             # system wide config file only";
$f[]="# servercert";
$f[]="# clientcert";
$f[]="ask_auth        0";
$f[]="# use_locks       1";
$f[]="# lock_owner      <user-name>";
$f[]="# lock_timeout    1800              # seconds";
$f[]="# lock_refresh    60                # seconds";
$f[]="# use_expect100   0";
$f[]="# if_match_bug    0";
$f[]="# drop_weak_etags 0";
$f[]="# allow_cookie    0";
$f[]="# precheck        1";
$f[]="# ignore_dav_header 0";
$f[]="# server_charset";
$f[]="# connect_timeout 10                # seconds";
$f[]="# read_timeout    30                # seconds";
$f[]="# retry           30                # seconds";
$f[]="# max_retry       300               # seconds";
$f[]="# add_header";
$f[]="";
$f[]="# Cache Related Options";
$f[]="# ---------------------";
$f[]="";
$f[]="# backup_dir      lost+found";
$f[]="# cache_dir       /var/cache/davfs2 # system wide cache";
$f[]="#                 ~/.davfs2/cache   # per user cache";
$f[]="# cache_size      50                # MiByte";
$f[]="# table_size      1024";
$f[]="# dir_refresh     60                # seconds";
$f[]="# file_refresh    1                 # second";
$f[]="# delay_upload    10";
$f[]="# gui_optimize    0";
$f[]="";
$f[]="# Debugging Options";
$f[]="# -----------------";
$f[]="";
$f[]="# debug           # possible values: config, kernel, cache, http, xml,";
$f[]="                  #      httpauth, locks, ssl, httpbody, secrets, most";
$f[]="";
echo "Starting......: AutoFS davfs2.conf done\n";
@file_put_contents("/etc/davfs2/davfs2.conf",@implode("\n",$f));

$f=array();
	$q=new mysql();
	$sql="SELECT * FROM automount_davfs";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	$c=0;
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if($ligne["local_dir"]==null){continue;}
		$c++;
		$f[]="/automounts/{$ligne["local_dir"]}\t{$ligne["user"]}\t{$ligne["password"]}";
		
	}
	$f[]="";
echo "Starting......: AutoFS secrets file with $c credential(s) done\n";
@file_put_contents("/etc/davfs2/secrets",@implode("\n",$f));

if(!$GLOBALS["NORELOAD"]){
	$unix=new unix();
	if(!is_file("/usr/bin/service")){shell_exec("/usr/bin/service autofs reload");return;}
	shell_exec("/etc/init.d/autofs reload");
}

}








?>