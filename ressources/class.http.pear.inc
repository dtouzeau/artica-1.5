<?php
include_once(dirname(__FILE__) . '/HTTP/Request2.php');
include_once(dirname(__FILE__) . '/class.templates.inc');
class httpget{
	var $req;
	var $uploads=array();
	
	
	function httpget(){
		$req =new HTTP_Request2();  
		$this->req=$req;
		$this->loadProxyInfos();
		$this->req->setHeader('user-agent', 'Artica-Agent');
	}
	private function loadProxyInfos(){
		$ini=new Bs_IniHandler();
		$sock=new sockets();
		$datas=$sock->GET_INFO("ArticaProxySettings");
		$ini->loadString($datas);
		$ArticaProxyServerEnabled=$ini->_params["PROXY"]["ArticaProxyServerEnabled"];
		$ArticaProxyServerName=$ini->_params["PROXY"]["ArticaProxyServerName"];
		$ArticaProxyServerPort=$ini->_params["PROXY"]["ArticaProxyServerPort"];
		$ArticaProxyServerUsername=$ini->_params["PROXY"]["ArticaProxyServerUsername"];
		$ArticaProxyServerUserPassword=$ini->_params["PROXY"]["ArticaProxyServerUserPassword"];
		if($ArticaProxyServerEnabled=="yes"){
			$this->req->setConfig(array(
			    'proxy_host'        => $ArticaProxyServerName,
			    'proxy_port'        => $ArticaProxyServerPort,
			    'proxy_user'        => $ArticaProxyServerUsername,
			    'proxy_password'    => $ArticaProxyServerUserPassword,
			    'proxy_auth_scheme' => HTTP_Request2::AUTH_DIGEST
			));
			}
	
	}
	
	public function send($uri,$method="post",$values=array()){
		if($GLOBALS["VERBOSE2"]){writelogs("Send $uri ($method)",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		if($method=="post"){
			if(preg_match("#https#",$uri)){
				if($GLOBALS["VERBOSE2"]){writelogs("Send SSL connection detected...",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
				$this->req->setConfig(array('ssl_verify_peer'=>false,'ssl_verify_host'=>false));
			}
			$this->req->setMethod(HTTP_Request2::METHOD_POST);
			$this->req->setMethod(HTTP_Request2::METHOD_POST)->addPostParameter($values);
			if(count($this->uploads)){
				while (list ($filefield, $filepath) = each ($this->uploads) ){
					$this->req->setMethod(HTTP_Request2::METHOD_POST)->addUpload($filefield, $filepath);		
				}
			}
			
			
		
		}else{$this->req->setMethod(HTTP_Request2::METHOD_GET);}
		$this->req->setUrl($uri);
		if($GLOBALS["VERBOSE2"]){writelogs("settings done, send socket...",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		try{
		$datas=$this->req->send()->getBody();
		}
		catch(HTTP_Request2_Exception $e){
			writelogs("HTTP_Request2_Exception: \"{$e->getMessage()}\"",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			return "FAILED_CONNECT";
			
		}
		$dataslog=$datas;
		if(strlen($datas)>255){$dataslog=substr(0,255)."...";}
		
		writelogs("FINISH: \"". trim($dataslog)."\"",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		return $datas;	
		
	}
	
	
	
}