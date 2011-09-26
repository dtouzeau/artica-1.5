<?php

//phpToArray();

die();
apc_compile();



function apc_compile(){
	
if (!function_exists('apc_compile_file')) {
	if(posix_getuid()==0){echo "Starting lighttpd............: Compiling engine no APC engine found\n";}
    return;
}


if(posix_getuid()==0){echo "Starting lighttpd............: Please wait, compiling engine to APC\n";}
compile_files(dirname(__FILE__));
compile_files(dirname(__FILE__).'/ressources');
compile_files(dirname(__FILE__).'/framework');
compile_files(dirname(__FILE__).'/user-backup');
compile_files(dirname(__FILE__).'/user-backup/framework');
compile_files(dirname(__FILE__).'/user-backup/ressources');

if(is_dir("/usr/share/roundcube")){
	compile_files(dirname(__FILE__));
	compile_files("/usr/share/roundcube");
	compile_files("/usr/share/roundcube/bin");
	compile_files("/usr/share/roundcube/program");
	compile_files("/usr/share/roundcube/program/lib");
	compile_files("/usr/share/roundcube/program/lib/include");
}

if(posix_getuid()==0){echo "Starting lighttpd............: done: {$GLOBALS["COMPILED"]} APC files compiled\n";}
else{
writelogs("{$GLOBALS["COMPILED"]} APC files compiled",__FUNCTION__,__FILE__,__LINE__);}
}

function compile_files($dir){
    $dirs = glob($dir . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR);
    
    $files = glob($dir . DIRECTORY_SEPARATOR . '*.php');
    if (is_array($files) && count($files) > 0) {
        while(list(,$v) = each($files)){
         if(apc_compile_file($v)){
         	$GLOBALS["COMPILED"]=$GLOBALS["COMPILED"]+1;
         }
        }
    }
    
    $files = glob($dir . DIRECTORY_SEPARATOR . '*.inc');
    if (is_array($files) && count($files) > 0) {
        while(list(,$v) = each($files)){
         if(apc_compile_file($v)){
         	$GLOBALS["COMPILED"]=$GLOBALS["COMPILED"]+1;
         }
         
        }
    }    
}


function phpToArray(){
	$f=explode("\n", @file_get_contents("/etc/php5/cli/php.ini"));
	 while(list($index,$v) = each($f)){
	 	
	 	if(strpos($v,"]")>0){
	 		$p[]="\$f[]=\"$v\";";
	 		continue;
	 	}
	 	
	 	if(preg_match("#(.+)=(.+)#", $v,$re)){
	 		$key=trim($re[1]);
	 		$value=trim($re[2]);
	 		$value=str_replace('"', '', $value);
	 		$t[]= "if(!isset(\$this->Params[\"PHP_VALUES\"][\"$key\"])){\$this->Params[\"PHP_VALUES\"][\"$key\"]=null;}";
	 		$s[]= "if(\$this->Params[\"PHP_VALUES\"][\"$key\"]==null){\$this->Params[\"PHP_VALUES\"][\"$key\"]=\"$value\";}";
	 		$p[]="\$f[]=\"$key = \\\"{\$this->Params[\"PHP_VALUES\"][\"$key\"]}\\\"\";";
	 		
	 		
	 	}
	 	
	 	if(preg_match("#extension.*?=(.+)#", $v,$re)){
	 		$w[]=trim($re[1]);
	 		
	 	}
	 	
	 	
	 }
	 echo @implode("\n", $t);
	 echo @implode("\n", $s);
	 echo @implode("\n", $p);
	echo @implode("\n", $w);
	
}

 





?>