<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__).'/ressources/class.system.network.inc');
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;}



if($argv[1]=="--printkey"){echo "Key:\t\"".getlongKey()."\"\n";die();}
if($argv[1]=="--getkey"){getlongKey();die();}



function getlongkey(){
	
	$sock=new sockets();
	$RacconKey=$sock->GET_INFO("RacconKey");
	if($sock->GET_INFO("RacconKey")<>null){return $RacconKey;}
	
	$unix=new unix();
	$xxd_bin=$unix->find_program("xxd");
	$dd_bin=$unix->find_program("dd");
	$cmd="$dd_bin if=/dev/random count=24 bs=1|$xxd_bin -ps 2>&1";
	if($GLOBALS["VERBOSE"]){echo "$cmd\n";}
	exec($cmd,$results);
	
	while (list ($num, $ligne) = each ($results) ){
		if(preg_match("#\s+[a-z]+#",trim($ligne))){
			if($GLOBALS["VERBOSE"]){echo "skipped \"$ligne\" -> \s+[a-z]+\n";}
			continue;
		}
		if(strlen(trim($ligne))<48){
			if($GLOBALS["VERBOSE"]){echo "skipped \"". strlen(trim($ligne)). "\" length+\n";}
			continue;
		}
		$key=trim($ligne);
		
	}
	
	$sock->SET_INFO("RacconKey",$key);
	return $key;
	
	
}
