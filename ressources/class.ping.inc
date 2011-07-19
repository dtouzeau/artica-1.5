<?php
class ping{
	var $ip='';
	
	
	public function ping($ip){
		$this->ip=$ip;
	}
	
	public function Isping(){
		if(trim($this->ip)==null){return false;}
		$ftmp="/tmp/". md5(__FILE__);
		exec("/bin/ping -q -c 1 -s 16 -W1 -Q 0x02 $this->ip >$ftmp 2>&1");
		$results=explode("\n",@file_get_contents($ftmp) );
		@unlink($ftmp);
		if(!is_array($results)){return false;}
		while (list ($index, $line) = each ($results) ){
			if(preg_match("#[0-9]+\s+[a-zA-Z]+\s+[a-zA-Z]+,\s+([0-9]+)\s+received#",$line,$re)){
				if($re[1]>0){
					return true;
				}else{
					return false;
				}
			}
		}
		
	}
	
}
?>