<?php

$datas=file_get_contents('ressources/dnsrbl.db');
$datas1=file_get_contents('ressources/tmp.rbl.txt');
$tb=explode("\n",$datas);


while (list ($num, $ligne) = each ($tb) ){
	if($ligne<>null){
		$tr=explode(':',$ligne);
		$array[$tr[0]][$tr[1]]="yes";
	}
	
	
	
}

$datas1=explode("\n",$datas1);

while (list ($num, $ligne) = each ($datas1) ){
	if($ligne<>null){
		$add=false;
		if(preg_match('#dnsbl#',$ligne)){
			$array["RHSBL"][$ligne]="yes";
			$add=true;
		}
		
		if(preg_match('#dsbl#',$ligne)){
			$array["RHSBL"][$ligne]="yes";
			$add=true;
		}		
		
		
		if($add==false){$array["RBL"][$ligne]="yes";}
		
		
	}
	
	
	
}

while (list ($num, $ligne) = each ($array["RBL"]) ){
	$l=$l . "RBL:$num\n";
	
}
while (list ($num, $ligne) = each ($array["RHSBL"]) ){
	$l=$l . "RHSBL:$num\n";
	
}

echo $l


?>