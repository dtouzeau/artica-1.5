<?php


$src="https://beta.hostedsecurity.biz";
$template="Kav4Proxy";
	

	foreach (glob("ressources/templates/$template/css/*.css") as $filename) {
			$datas=explode("\n",@file_get_contents("$filename"));
			
			while (list ($num, $ligne) = each ($datas) ){
				if(preg_match('#url\("(.+?)"#',$ligne,$re )){$f[]=$re[1];continue;}
				if(preg_match("#url\('(.+?)'#",$ligne,$re )){$f[]=$re[1];continue;}
				if(preg_match("#url\((.+?)\)#",$ligne,$re )){$f[]=$re[1];continue;}
			}
			
	}
	
	while (list ($num, $filename) = each ($f) ){
		if(substr($filename, 0,1)=="/"){
			if(is_file("/usr/share/artica-postfix/$filename")){
				echo "Conflict for $filename\n";
			}else{
				$cmd="wget $src$filename --no-check-certificate -O /usr/share/artica-postfix$filename";
				shell_exec($cmd);
			}
		}else{
			$cmd="wget $src/css/$filename --no-check-certificate -O ressources/templates/$template/css/$filename";
			shell_exec($cmd);
		}	
		
	}
	