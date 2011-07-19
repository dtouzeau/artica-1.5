<?php
	include_once(dirname(__FILE__).'/class.artica.inc');
	include_once(dirname(__FILE__).'/class.mysql.inc');
	include_once(dirname(__FILE__).'/class.ini.inc');

	
	
	class mailsync{
		var $uid;
		
		function mailsync($uid){
			if($uid<>null){
				$this->uid=$uid;
				$this->load();
			}
			
		}
		
		
		private function load(){
			
			
		}
		
		
		
	}
?>