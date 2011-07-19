<?php
class semaphores{
	var $id;
	var $shmid;
	var $bytesmem;
	var $ok=true;
	var $index=1;
	
	function semaphores($id,$memorybytes,$index=1){
		if(!function_exists("shm_attach")){$this->ok=false;return;}
		$this->id=$id;
		$this->ok=true;
		$this->bytesmem=$memorybytes;
		$this->index=$index;
		if($GLOBALS["LOGON-PAGE"]){error_log("@shm_attach($this->id,$this->bytesmem,0777) ". basename(__FILE__). " line ". __LINE__);}
		$this->shmid=@shm_attach($this->id,$this->bytesmem,0777);
		if(!$this->shmid){$this->ok=false;}
		
	}
	
	public  function MyArray(){
		if(!$this->ok){return array();}
		$data=@shm_get_var($this->shmid,$this->index);
		if(!is_array($data)){return array();}
		return $data;	
	}
	
	function GET($key){
		if(!$this->ok){return null;}
		$data=@shm_get_var($this->shmid,$this->index);
		return $data[$key];
	}
	
	public function DUMP(){
	if(!$this->ok){return array();}
		$data=@shm_get_var($this->shmid,$this->index);
		return $data;
	}
	
	function SET($key,$value){
		if(is_array($key)){return null;}
		if(!$this->ok){return null;}
		$array=$this->MyArray();
		if(!is_array($array)){$array=array();}
		$array[$key]=$value;
		if($GLOBALS["LOGON-PAGE"]){error_log("@shm_put_var($this->shmid,$this->index,$array); ". basename(__FILE__). " line ". __LINE__);}
		@shm_put_var($this->shmid,$this->index,$array);
		if($GLOBALS["LOGON-PAGE"]){error_log("@shm_put_var() OK ". basename(__FILE__). " line ". __LINE__);}
		}
	
	function CLOSE(){
		if(!$this->ok){return null;}
		if(!$this->shmid){return null;}
		shm_detach($this->shmid);
	}
	
	function Delete(){
		if(!function_exists("shm_attach")){$this->ok=false;return;}
		shm_remove($this->shmid);
		shm_detach($this->shmid);
	}
	
	function removekey(){
		if(!function_exists("shm_remove_var")){$this->ok=false;return;}
		@shm_remove_var($this->shmid,$this->id);
	}
	
	 
	
}
?>