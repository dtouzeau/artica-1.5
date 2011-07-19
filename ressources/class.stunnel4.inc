<?php
include_once(dirname(__FILE__)."/class.ldap.inc");
include_once(dirname(__FILE__)."/class.ini.inc");



class stunnel4{
	var $dn;
	var $main_array=array();
	var $global_conf;
	var $Disclaimer;

	
	function stunnel4(){
		
		$this->LoadSettings();
		
		
	}
	


function LoadSettings(){
		$sock=new sockets();
		$this->global_conf=$sock->GET_INFO("stunnelconfINI");
		$ini=new Bs_IniHandler();
		
		$ini->loadString($this->global_conf);
		$this->main_array=$ini->_params;
		
		
}

function SaveConf(){
	$ini=new Bs_IniHandler();
	$ini->_params=$this->main_array;
	$conf=$ini->toString();
	$conf=str_replace("[]","",$conf);
	$conf=str_replace("\n\n","\n",$conf);
	$sock=new sockets();	
	$sock->SaveConfigFile($conf,"stunnelconfINI");
	$builder=$this->BuildConf().$conf;
	$sock->SaveConfigFile($builder,"stunnelconf");
	return true;

}


	function BuildConf(){
		$conf=$conf ."cert = /etc/stunnel/stunnel.pem\n";
		$conf=$conf ."sslVersion = SSLv3\n";
		$conf=$conf ."chroot = /var/lib/stunnel4/\n";
		$conf=$conf ."setuid = stunnel4\n";
		$conf=$conf ."setgid = stunnel4\n";
		$conf=$conf ."pid =/stunnel4.pid\n";
		$conf=$conf ."socket = l:TCP_NODELAY=1\n";
		$conf=$conf ."socket = r:TCP_NODELAY=1\n";
		$conf=$conf ."client = yes\n";
		$conf=$conf ."foreground=no\n";
		$conf=$conf ."\n";	
		
		return $conf;
		
		
	}
	
	function SaveToserver(){
		$users=new usersMenus();
		if(!$users->stunnel4_installed){return null;}
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?stunnel-restart=yes");
		//return $sock->getfile('stunnel4save');
		}






}