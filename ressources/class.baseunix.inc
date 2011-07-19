<?php


class baseunix{
	
	function ForceDirectories($dir){
		if(is_dir($dir)){return true;}
		@mkdir($dir,null,true);
		if(is_dir($dir)){return true;}
		}
		

	function file_time_min($path){
			if(!is_file($path)){return 0;}
			 $last_modified = filemtime($path);
			 
		$data1 = $last_modified;
		
		$data2 = time();
		$difference = ($data2 - $data1); 	 
		return round($difference/60);	 
	}	
	
}


?>