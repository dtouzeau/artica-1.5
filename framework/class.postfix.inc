<?php

include_once(dirname(__FILE__).'/class.unix.inc');
class postfix_system{
	var $postfix_queues_dir=array();
	
		function __construct() {
				$this->load_queue_directory_array();
			}
	
	private function LOCATE_POSTCONF(){
		$unix=new unix();
		$postconf=$unix->find_program('postconf');
		if(trim($postconf)<>null){return $postconf;}
		if(is_file("/usr/sbin/postconf")){return "/usr/sbin/postconf";}
	}
	
	private function load_queue_directory_array(){
		    $directory_name[]='incoming'; 
		    $directory_name[]='active'; 
		    $directory_name[]='deferred'; 
		    $directory_name[]='bounce'; 
		    $directory_name[]='defer'; 
		    $directory_name[]='trace'; 
		    $directory_name[]='maildrop'; 
		    $directory_name[]='corrupt'; 
		    $this->postfix_queues_dir=$directory_name;
		    return $directory_name;
	}
	
	private function queue_directory(){
		$postconf=$this->LOCATE_POSTCONF();
		if($postconf==null){return null;}
		return trim(shell_exec("$postconf -h queue_directory"));
		}
		
	public function getQueuesNumber(){
		$queues=$this->load_queue_directory_array();
		$queue_directory=$this->queue_directory();
		if(!is_dir($queue_directory)){return array();}
		while (list ($num, $ligne) = each ($queues) ){
			if($ligne=="defer"){continue;}
			$array[$ligne]=$this->DirCount("$queue_directory/$ligne");
		}
		
		if(!is_array($array)){return $array;}
		return $array;
	}
	
	private function DirCount($path){
		if(!is_dir($path)){return 0;}
		$array=$this->DirListFiles($path);
		if($array){
			$res=count($array);
			unset($array);
		}else{$res=0;}
		
		return $res;
	}
	
private	 function DirListFiles($dir,$recursive = TRUE) {
    if (is_dir($dir)) {
      for ($list = array(),$handle = opendir($dir); (FALSE !== ($file = readdir($handle)));) {
        if (($file != '.' && $file != '..') && (file_exists($path = $dir.'/'.$file))) {
          if (is_dir($path) && ($recursive)) {
            $list = array_merge($list, $this->DirListFiles($path, TRUE));
          } else {
            $entry[]="$dir/$file";
	         do if (!is_dir($path)) {

              break;
            } else {
              break;
            } while (FALSE);
            $list = $entry;
          }
        }
      }
      closedir($handle);
      return $list;
    } else return FALSE;
  }
    

	private function QUEUE_PATH(){
		if($GLOBALS["POSTFIX_QUEUE_PATH"]<>null){return $GLOBALS["POSTFIX_QUEUE_PATH"];}
		exec("/usr/sbin/postconf -h queue_directory",$results);
		$GLOBALS["POSTFIX_QUEUE_PATH"]=$results[0];
		return $results[0];
		
	}
	
private function READ_MESSAGE($messageid){
	exec("/usr/sbin/postcat -q $messageid",$results);
	 while (list ($num, $ligne) = each ($results) ){
	 	
	 	if(preg_match("#message_arrival_time: (.+)#",$ligne,$re)){
	 		$m_Time=$re[1];
	 		continue;
	 	}
	 	
		 if(preg_match("#named_attribute: (.+)#",$ligne,$re)){
		 		$named_attribute=$re[1];
		 		continue;
		 	}	
		 if(preg_match("#sender: (.+)#",$ligne,$re)){
		 		$sender=$re[1];
		 		continue;
		 	}
		 if(preg_match("#sender: ([a-zA-Z0-9\.@\-_]+)#",$ligne,$re)){
		 		$sender=$re[1];
		 		continue;
		 	}
	
	 	if(preg_match("#recipient: ([a-zA-Z0-9\.@\-_]+)#",$ligne,$re)){
		 		$recipient=$re[1];
		 		continue;
		 	}
	
		 if(preg_match("#Subject: (.+)#",$ligne,$re)){
		 		$Subject=$re[1];
		 		continue;
		 	}	 	
	 	
	 }

	 return "<time>$m_Time</time><named_attr>$named_attribute</named_attr><sender>$sender</sender><recipient>$recipient</recipient><subject>$Subject</subject><MessageID>$messageid</MessageID>";
		
}
	
public function READ_QUEUE($queuename){
		
		$queuepath=$this->QUEUE_PATH();
		if(!is_dir($queuepath)){
			$this->events("unable to stat queue path \"$queuepath\"",__FUNCTION__);
			return null;
		}
   
   	$path="$queuepath/$queuename";
   	$this->events("$path",__FUNCTION__);
  	$array=$this->DirListFiles($path);
    $this->events("Files: ".count($array),__FUNCTION__);
    $count=0;
    while (list ($num, $ligne) = each ($array) ){
    	print_r($ligne);
    	if(!is_file($ligne)){
    		 $this->events("unable to stat $ligne");
    		 continue;
    	}
		$messageid=basename($ligne);
        if(strlen($messageid)<4){continue;}
        $count++;
        $return=$return.$this->READ_MESSAGE($messageid)."\n";
      
      	if($count>200){break;}	
		
	}	
	echo $return;
	return $return;
	
}

  private function events($text,$function){
		error_log("framework:: $function $text in ". basename(__FILE__));
		}
	
	
	
	
}
?>