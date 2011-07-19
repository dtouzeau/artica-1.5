<?php
class pflogsumm{
	var $main_array=array();
	var $notif_enabled;
	var $smtp_server_name;
	var $smtp_server_port;
	var $tls_enabled;
	var $smtp_auth_passwd;
	
	function pflogsumm(){
		$sock=new sockets();
		$ini=new Bs_IniHandler();
		$ini->loadString($sock->GET_INFO('pflogsumm'));
		$this->main_array=$ini->_params;
		$this->GetDefaults();
		$this->LoadNotifs();
	}
	
	function GetDefaults(){
		
		$users=new usersMenus();
		if($this->main_array["SETTINGS"]["sender"]==null){$this->main_array["SETTINGS"]["sender"]="root@$users->hostname";}
		if($this->main_array["SETTINGS"]["subject"]==null){$this->main_array["SETTINGS"]["subject"]="[$users->hostname]: Postfix report summarize";}
		if($this->main_array["SETTINGS"]["hour"]==null){$this->main_array["SETTINGS"]["hour"]="23";}
		if($this->main_array["SETTINGS"]["min"]==null){$this->main_array["SETTINGS"]["min"]="30";}
		if($this->main_array["SETTINGS"]["use_send_mail"]==null){$this->main_array["SETTINGS"]["use_send_mail"]="0";}		
			
		
		
	}
	
	
	function LoadNotifs(){
		
	$sock=new sockets();
	$ini=new Bs_IniHandler();
	$ini->loadString($sock->GET_INFO("SmtpNotificationConfig"));
	$this->notif_enabled=$ini->_params["SMTP"]["enabled"];
	$this->smtp_server_name=$ini->_params["SMTP"]["smtp_server_name"];
	$this->smtp_server_port=$ini->_params["SMTP"]["smtp_server_port"];
	$this->tls_enabled=$ini->_params["SMTP"]["tls_enabled"];
	$this->smtp_auth_passwd=$ini->_params["SMTP"]["smtp_auth_passwd"];		
	}
	
	
	function Save(){
		$ini=new Bs_IniHandler();
		$hour=intval($this->main_array["SETTINGS"]["hour"]);
		$min=intval($this->main_array["SETTINGS"]["min"]);
		$this->main_array["SETTINGS"]["schedule"]="$min $hour * * *";
		$ini->_params=$this->main_array;
		
		$sock=new sockets();
		$sock->SaveConfigFile($ini->toString(),"pflogsumm");
		$sock->getfile("RestartDaemon");
		
	}
	
	
	

}
?>