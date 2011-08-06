<?php

if($argv[1]=="--dirs"){parsedirs($argv[2]);die();}
if($argv[1]=="www"){www();die();}
if($argv[1]=="--countries"){countries();die();}

if($argv[1]=="--replace"){replacesex();die();}



function www(){
	
	for($i=0;$i<150;$i++){
		echo "http://client$i.dropbox.com\n";
		
	}
}


importlangs();



function importlangs(){
	echo "importlangs()\n";
	$GLOBALS["langs"]=array("fr","en","po","es","it","br","pol");
	while (list ($num, $val) = each ($GLOBALS["langs"]) ){
		echo "COmpile $val\n";
		CompileLangs($val);
	}	
	
}

function CompileLangs($language){
	if(trim($language)==null){return;}
	$base="/usr/share/artica-postfix/ressources/language/$language";
	$pattern='#<([a-zA-Z0-9\_\-\s\.]+)>(.+?)<\/([a-zA-Z0-9\_\-\s\.]+)>#is';
	$files=DirFiles($base);
	while (list ($num, $val) = each ($files) ){
		$datas=@file_get_contents("$base/$val");
		if(preg_match_all($pattern,$datas,$reg)){
				while (list ($index, $word) = each ($reg[1]) ){
					$langs[$word]=$reg[2][$index];
					}
			}
			
		
		
	}	
	
	echo "writing /usr/share/artica-postfix/ressources/language/$language.db ". count($langs)." words\n";
	file_put_contents("/usr/share/artica-postfix/ressources/language/$language.db",serialize($langs));			
}

function DirFiles($path){
	$dir_handle = @opendir($path);
	if(!$dir_handle){
		return array();
	}
	$count=0;	
	while ($file = readdir($dir_handle)) {
	  if($file=='.'){continue;}
	  if($file=='..'){continue;}
	  if(!is_file("$path/$file")){continue;}
		
			$array[$file]=$file;
			continue;
		
		
	  }
	if(!is_array($array)){return array();}
	@closedir($dir_handle);
	return $array;
}


function parsedirs($path){
$iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path), 
            RecursiveIteratorIterator::SELF_FIRST);

foreach($iterator as $file) {
    if($file->isDir()) {
    	$dir=$file->getRealpath();
    	if($dir=="/"){continue;}
    	if(isset($GLOBALS["ALREADY"][$dir])){continue;}
        echo "\$f[".$file->getRealpath()."]=\"".$file->getRealpath()."\";\n";
   		$GLOBALS["ALREADY"][$dir]=true;
    }
}
	
	
}


function countries(){
	$f=explode("\n",@file_get_contents("/tmp/countries.txt"));
	while (list ($num, $val) = each ($f) ){
	if(preg_match("#([A-Z]+)\s+\(([A-Z]+)\)#", $val,$re)){
		echo "\"".strtolower($re[2])."\"=>\"".strtolower($re[1])."\",\n";
	}
	}
	
}


function replacesex(){
	foreach (glob("/usr/share/artica-postfix/*.php") as $filename) {
		$content=@file_get_contents($filename);
		if(strpos($content, 'tempvalue.length>0')>0){
			$content=str_replace('if(tempvalue.length>3){', 'if(tempvalue.length>3){', $content);
			echo "Check: $filename\n";
			@file_put_contents($filename, $content);
		}
		
		
	}
	
	
}

