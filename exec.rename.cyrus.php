<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.user.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__)."/framework/class.unix.inc");
include_once(dirname(__FILE__)."/framework/frame.class.inc");


explode_cmd($argv);

if(!is_dir($GLOBALS["CYR_PATH"])){echo "no path\n";die();}
if(!is_numeric($GLOBALS["CYR_NUM"])){$GLOBALS["CYR_NUM"]=10000;}



$fileList = rglob("*~*", GLOB_MARK, $GLOBALS["CYR_PATH"]);

foreach($fileList as $index => $file) { 
	if($file[strlen($file) - 1] != "/") { 
		$GLOBALS["countf"]=$GLOBALS["CYR_NUM"];
		copy_cyrus_file($file);
	}
}


function copy_cyrus_file($oldfile){
	$dir=dirname($oldfile);
	$newfile="$dir/{$GLOBALS["countf"]}.";
	if(is_file($newfile)){
		$GLOBALS["countf"]=$GLOBALS["countf"]+1;
		copy_cyrus_file($oldfile);
		return;
	}
	
	echo "$oldfile -> ". basename($newfile)."\n";
	@copy($oldfile,$newfile);
	@unlink($oldfile);
}



function explode_cmd($array){
while (list ($num, $cmd) = each ($array) ){
	if(preg_match("#--path=(.+)#",$cmd,$re)){
		$GLOBALS["CYR_PATH"]=$re[1];
	}
	
	if(preg_match("#--startnum=([0-9]+)#",$cmd,$re)){
		$GLOBALS["CYR_NUM"]=$re[1];
	}	
	
}	
	
}

 function rglob($pattern, $flags = 0, $path = '') {
   
      if (!$path && ($dir = dirname($pattern)) != '.') {
   
      if ($dir == '\\' || $dir == '/') $dir = '';
   			return rglob(basename($pattern), $flags, $dir . '/');
   	  }
   
      $paths = glob($path . '*', GLOB_ONLYDIR | GLOB_NOSORT);
   
      $files = glob($path . $pattern, $flags);
      foreach ($paths as $p) $files = array_merge($files, rglob($pattern, $flags, $p . '/'));
         return $files;
    }


?>