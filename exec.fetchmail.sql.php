<?php

$GLOBALS["PARSE_PATH"]="/var/log/artica-postfix/fetchmail";
if(!is_dir($GLOBALS["PARSE_PATH"])){die();}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.users.menus.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__)."/framework/class.unix.inc");
include_once(dirname(__FILE__)."/framework/frame.class.inc");

cpulimit();
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["DEBUG"]=true;$GLOBALS["VERBOSE"]=true;}

if(!Build_pid_func(__FILE__,"MAIN")){
	writelogs(basename(__FILE__).":Already executed.. aborting the process",basename(__FILE__),__FILE__,__LINE__);
	die();
}
if(system_is_overloaded()){events("die, overloaded");die();}

if($GLOBALS["DEBUG"]){echo "->DirListsql({$GLOBALS["PARSE_PATH"]})\n";}
$files=DirListsql($GLOBALS["PARSE_PATH"]);
$max=count($files);
if($max==0){if($GLOBALS["DEBUG"]){"max=$max\n -> die()\n";} die();}
if(!is_array($files)){if($GLOBALS["DEBUG"]){echo "no files\n";}return null;}
events("Parse $max sql files in {$GLOBALS["PARSE_PATH"]}");

while (list ($num, $file) = each ($files) ){
	$q=new mysql();
	$sql=@file_get_contents("{$GLOBALS["PARSE_PATH"]}/$file");
	$q->QUERY_SQL($sql,"artica_events");
	if($q->ok){
		events("success Parse $file sql file");
		@unlink("{$GLOBALS["PARSE_PATH"]}/$file");
	}
	
}





function events($text){
	$q=new debuglogs();
	$text=dirname(__FILE__)." ".$text;
	if($GLOBALS["DEBUG"]){echo $text."\n";}
	$q->events($text,"/var/log/artica-postfix/postfix-logger.sql.debug");
	
}


function DirListsql($path){
	$dir_handle = @opendir($path);
	if(!$dir_handle){
		events("Unable to open \"$path\"");
		return array();
	}
		$count=0;	
		while ($file = readdir($dir_handle)) {
		  if($file=='.'){continue;}
		  if($file=='..'){continue;}
		  if(!is_file("$path/$file")){continue;}
		   if(!preg_match("#\.sql$#",$file)){continue;}
		  	$array[$file]=$file;
		  }
		if(!is_array($array)){return array();}
		@closedir($dir_handle);
		return $array;
}

?>