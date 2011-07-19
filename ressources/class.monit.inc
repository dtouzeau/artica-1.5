<?php
include_once(dirname(__FILE__)."/class.ini.inc");
include_once(dirname(__FILE__)."/class.mysql.inc");


class monit{
	var $params;
	
	function monit(){
		$this->Load();
		
		
	}
	
	private function Load(){
		$sock=new sockets();
		$ini=new Bs_IniHandler();
		$ini->loadString($sock->GET_INFO("MonitServicesSettings"));
		$this->params=$ini->_params;
		$this->write_default();
		
	}
	
	private function write_default(){
		if($this->params["APP_DANSGUARDIAN"]["totalmem"]==null){$this->params["APP_DANSGUARDIAN"]["totalmem"]=1200;}
		if($this->params["APP_KAVMILTER"]["totalmem"]==null){$this->params["APP_KAVMILTER"]["totalmem"]=200;}
		if($this->params["APP_CLAMAV"]["totalmem"]==null){$this->params["APP_CLAMAV"]["totalmem"]=200;}
		if($this->params["APP_SPAMASSASSIN"]["totalmem"]==null){$this->params["APP_SPAMASSASSIN"]["totalmem"]=900;}
		if($this->params["APP_AMAVISD_NEW"]["totalmem"]==null){$this->params["APP_AMAVISD_NEW"]["totalmem"]=300;}
		
	}
	
	public function save(){
		$ini=new Bs_IniHandler();
		$ini->_params=$this->params;
		$sock=new sockets();
		$sock->SaveConfigFile($ini->toString(),"MonitServicesSettings");
		$sock=new sockets();
		$sock->getFrameWork('cmd.php?status-forced=yes');
	}
	
	
	
}


?>