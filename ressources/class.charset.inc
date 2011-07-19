<?php


function iconv_convert($ligne){
	

	$ligne=str_replace("‚","iconv(0082)",$ligne);
	$ligne=str_replace("Š","iconv(008A)",$ligne);
	$ligne=str_replace("","iconv(0090)",$ligne);
	$ligne=str_replace("…","iconv(0085)",$ligne);	
	return $ligne;
	
	
	
}

?>