<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");


if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["debug"]=true;$GLOBALS["VERBOSE"]=true;}
if(preg_match("#--reload#",implode(" ",$argv))){$GLOBALS["RELOAD"]=true;$GLOBALS["RESTART"]=true;}

if($argv[1]=="--patch"){patchbin();die();}




function patchbin(){
	$unix=new unix();
	$sabnzbdplus=$unix->find_program("sabnzbdplus");
	if(strlen($sabnzbdplus)<5){
		echo "Starting......: sabnzbdplus sabnzbdplus no such file\n";
		return;
	}
	echo "Starting......: sabnzbdplus $sabnzbdplus\n";
	$f=explode("\n",@file_get_contents($sabnzbdplus));
	
	while (list ($index, $line) = each ($f) ){
		if(preg_match("#^import sys#",$line)){
			$nextline=$f[$index+1];
			echo "Starting......: sabnzbdplus line $index\n";
			if(preg_match("#sys\.path.insert\(0#",$nextline)){
				echo "Starting......: sabnzbdplus Patched OK\n";
				return;
			}else{
				echo "Starting......: sabnzbdplus patching line $index\n";
				$f[$index]="import sys\nsys.path.insert(0,'/usr/share/sabnzbdplus')";
				@file_put_contents($sabnzbdplus,@implode("\n",$f));
				return;
			}	
		}		
	}

}
