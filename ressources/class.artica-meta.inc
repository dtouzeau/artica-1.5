<?php
include_once(dirname(__FILE__) . '/class.users.menus.inc');
include_once(dirname(__FILE__) . "/class.sockets.inc");


class artica_meta{
	var $GLOBAL_ARRAY=array();
	var $RegisterDatas=array();
	var $ArticaMetaHostname;
	var $class_socket;
	var $debug=false;
	var $uuid;
	var $serial;
	
	function artica_meta($debug=false){
		$this->debug=$debug;
		$this->class_socket=new sockets();
		$users=new usersMenus();
		$ArticaMetaRegisterDatas=base64_decode($this->class_socket->GET_INFO("ArticaMetaRegisterDatas"));
		$trace=debug_backtrace();
		$called=" called by ". basename($trace[1]["file"])." {$trace[1]["function"]}() line {$trace[1]["line"]}";
		
		$this->ArticaMetaHostname=$this->class_socket->GET_INFO("ArticaMetaHostname");
		if($this->ArticaMetaHostname==null){$this->ArticaMetaHostname="https://articatechnology.com";}
		$this->RegisterDatas=unserialize($ArticaMetaRegisterDatas);
		if(!is_array($this->RegisterDatas)){if($GLOBALS["VERBOSE"]){echo "No datas, not an array...\n$ArticaMetaRegisterDatas\n";return;}}
		$serial=$this->class_socket->GET_INFO("ArticaMetaSerial");
		
		if($this->debug){$this->events("SERIAL: $serial$called",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		
		if($serial==null){
			$this->RegisterDatas["ERROR"]="No serial...";
			$this->RegisterDatas["REGISTERED"]=false;
			if($GLOBALS["VERBOSE"]){echo "No serial...\n";$this->Save();return;}
		}
		
		$SYSTEMID=$this->class_socket->GET_INFO("SYSTEMID");
		if($this->debug){$this->events("SYSTEMID: $SYSTEMID",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		
		if($SYSTEMID==null){
			$SYSTEMID=base64_decode($this->class_socket->getFrameWork("cmd.php?system-unique-id=yes"));
			if($SYSTEMID<>null){$this->class_socket->SET_INFO("SYSTEMID",$SYSTEMID);}
			return;
		}
		
		if($SYSTEMID==null){
			if($this->debug){$this->events("SYSTEMID is null!!!$called",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
			return;
		}		
		$this->uuid=$SYSTEMID;
		$this->serial=$serial;
		$hostname=$users->hostname;
		$post["uuid"]=$SYSTEMID;
		$post["hostname"]=$hostname;
		$post["serial"]=$serial;
		$post["ident"]="{$this->RegisterDatas["email"]}:".md5($this->RegisterDatas["password"]);
		$this->events("IDENT:{$this->RegisterDatas["email"]}:****$called",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		
		if($GLOBALS["VERBOSE"]){echo "ident:{$post["ident"]}\n";}
		if($GLOBALS["VERBOSE"]){echo "serial:$serial\n";}
		if($GLOBALS["VERBOSE"]){echo "hostname:$hostname\n";}
		if($GLOBALS["VERBOSE"]){$post["VERBOSE"]=1;}
		$this->GLOBAL_ARRAY=$post;
	}
	
	function Save(){
		if($GLOBALS["VERBOSE"]){echo "\nsave array of ". count($this->RegisterDatas)." rows\n";}
		$this->class_socket->SaveConfigFile(base64_encode(serialize($this->RegisterDatas)),"ArticaMetaRegisterDatas");
		
	}
	
	function DetectError($body){
		if(preg_match("#<b>Fatal error<\/b>:\s+(.+?)<br \/>#is",$body,$re)){return $re[1];}
		if(preg_match("#<ERROR>(.+?)<\/ERROR>#is",$body,$re)){return $re[1];}
		if(preg_match("#<title>404 Not Found<\/title>#is",$body,$re)){return "404 Not Found";}
		if(preg_match("#<title>400 Bad Request<\/title>#is",$body,$re)){return "400 Bad Request";}
		
	}
	
	function events($text,$function,$file=null,$line=0){
		if($file==null){$file=basename(__FILE__);}else{$file=basename($file);}
		$pid=@getmypid();
		$date=@date("H:i:s");
		$logFile="/var/log/artica-postfix/artica-meta-agent.log";
		$size=@filesize($logFile);
		if($size>1000000){@unlink($logFile);}
		$f = @fopen($logFile, 'a');
		$text="[$file][$pid] $date $function:: $text (L.$line)\n";
		if($GLOBALS["VERBOSE"]){echo $text;}
		@fwrite($f, $text);
		@fclose($f);	
		}	
	
	
}