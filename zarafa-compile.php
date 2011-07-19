<?php


if(!is_dir($argv[1])){
	echo "{$argv[1]} no such dir\n";
	return;
}
@mkdir("{$argv[1]}/build",666,true);
foreach (glob($argv[1]."/*.deb") as $filename) {
	
	echo $filename."\n";
	system("dpkg-deb --extract \"$filename\" \"{$argv[1]}/build\"");
	@unlink($filename);
	
	
	
}

system("chown dtouzeau:dtouzeau {$argv[1]}/build");
