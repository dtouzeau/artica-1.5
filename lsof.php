<?php


exec("lsof -r 0",$results);


while (list ($num, $ligne) = each ($results) ){
	if(preg_match("#^(.+?)\s+[0-9]+#",$ligne,$re)){
		if(!isset($array[$re[1]])){$array[$re[1]]=0;}
		$array[$re[1]]=$array[$re[1]]+1;
	}
	
}

while (list ($prc, $count) = each ($array) ){
	
	echo "$prc\t$count\n";
}

?>