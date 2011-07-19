<?php
include_once(dirname(__FILE__).'/class.ldap.inc');
include_once(dirname(__FILE__).'/class.html.pages.inc');
include_once(dirname(__FILE__).'/class.user.inc');
class getlive{
	var $main_conf=array();
	var $uid=null;
	
	function getlive($userid){
		$sock=new sockets();
		$datas=$sock->GET_INFO('GetLiveAccounts');
		$ini=new Bs_IniHandler();
		$ini->loadString($datas);
		$this->main_conf=$ini->_params;
		$this->uid=$userid;
		
	}
	
	function ParseConfig($uid){
		$array=$this->main_conf[$uid];
		$user=new user($uid);
		if(!is_array($array)){
			$array["UserName"]=null;
			$array["Password"]=null;
			$array["Domain"]='hotmail.com';
			$array["Downloaded"]="/etc/artica-postfix/settings/GetLive/$uid.cache";
			$array["FetchOnlyUnread"]="no";
			$array["RetryLimit"]=2;
			$array["Delete"]="no";
			$array["MarkRead"]="no";
			$array["user"]=$user->mail;
			
		}
		$array["user"]=$user->mail;
		return $array;
		
	}
	
	function Save(){
		$ini=new Bs_IniHandler();
		$ini->_params=$this->main_conf;
		$data=$ini->toString();
		$sock=new sockets();
		$sock->SaveConfigFile($data,'GetLiveAccounts');
		
		
	}
	
	
}