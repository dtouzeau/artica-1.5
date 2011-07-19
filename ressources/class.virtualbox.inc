<?php
include_once(dirname(__FILE__) . '/class.mysql.inc');
include_once(dirname(__FILE__) . '/class.sockets.inc');

class virtualbox{
	var $uuid=null;
	
	
	function virtualbox($uuid){
		$this->uuid=$uuid;
		
	}
	
	function AddNatPort($from,$to){
		if($from==0){echo "0 is not a correct value\n";return;}
		if($to==0){echo "0 is not a correct value\n";return;}
		if(strlen($this->uuid)<5){
			writelogs("$this->uuid is not a correct uuid",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			return;
		}
		$sql="INSERT INTO `virtualbox_nat`(`vboxport`,`localport`,`vboxid`) VALUES('$to','$from','$this->uuid')";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){
			writelogs($q->mysql_error,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			echo $q->mysql_error;
			return;
		}
		$sock=new sockets();
		echo base64_decode($sock->getFrameWork("cmd.php?virtualbox-nats=yes"));
		
	}
	
	function guestmemoryballoon($memory){
		$sock=new sockets();
		echo base64_decode($sock->getFrameWork("cmd.php?virtualbox-guestmemoryballoon=$this->uuid&mem=$memory"));		
		
	}
	
	function SaveSettingsOnOff($value,$key){
		$sock=new sockets();
		if($value==1){$value="on";}else{$value="off";}
		echo base64_decode($sock->getFrameWork("cmd.php?virtualbox-set-params=$this->uuid&value=$value&key=$key"));		
	}
	
	function SaveSettingsInt($value,$key){
		$sock=new sockets();
		if(!is_numeric($value)){return null;}
		echo base64_decode($sock->getFrameWork("cmd.php?virtualbox-set-params=$this->uuid&value=$value&key=$key"));		
	}	
	
	function RebuildNats(){
		$sock=new sockets();
		echo base64_decode($sock->getFrameWork("cmd.php?virtualbox-nats-rebuild=$this->uuid"));
	}
	
	function DelNatPort($from,$to){
		if($from==0){echo "0 is not a correct value\n";return;}
		if($to==0){echo "0 is not a correct value\n";return;}
		if(strlen($this->uuid)<5){
			writelogs("$this->uuid is not a correct uuid",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			return;
		}
		$sql="DELETE FROM `virtualbox_nat` WHERE `localport`='$from' AND  `vboxid`='$this->uuid'";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){
			writelogs($q->mysql_error,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			echo $q->mysql_error;
			return;
		}
		$sock=new sockets();
		echo base64_decode($sock->getFrameWork("cmd.php?virtualbox-nat-del=yes&localport=$from&vboxport=$to&uuid=$this->uuid"));
		
	}	
	
}