<?php
class ccurl{
	var $uri;
	var $parms=array();
	var $data='';
	var $error;
	var $ArticaProxyServerEnabled="no";
	var $ArticaProxyServerName='';
	var $ArticaProxyServerPort=3128;
	var $ArticaProxyServerUsername=null;
	var $ArticaProxyServerUserPassword=null;
	function ccurl($uri){
			$this->uri=$uri;
			$ini=new Bs_IniHandler();
			$sock=new sockets();
			$datas=$sock->GET_INFO("ArticaProxySettings");
			$ini->loadString($datas);
			$this->ArticaProxyServerEnabled=$ini->_params["PROXY"]["ArticaProxyServerEnabled"];
			$this->ArticaProxyServerName=$ini->_params["PROXY"]["ArticaProxyServerName"];
			$this->ArticaProxyServerPort=$ini->_params["PROXY"]["ArticaProxyServerPort"];
			$this->ArticaProxyServerUsername=trim($ini->_params["PROXY"]["ArticaProxyServerUsername"]);
			$this->ArticaProxyServerUserPassword=$ini->_params["PROXY"]["ArticaProxyServerUserPassword"];			
	}
	
	function get(){
				
			while (list ($num, $ligne) = each ($this->parms)){
				$curlPost .='&'.$num.'=' . urlencode($ligne);
			}		
				
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "$this->uri");
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);	
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
			$this->data=curl_exec($ch);
			$error=curl_errno($ch);		
			writelogs("datas: $this->uri (". strlen($this->data)." bytes) error:$error",__CLASS__.'/'.__FUNCTION__,__FILE__);
			curl_close($ch);
			return $this->ParseError($error);
	}
	
	private function ParseError($error){
			if($GLOBALS["VERBOSE"]){echo "ccurl/ParseError($error)\n";}
			if($error==6){
				writelogs("error 6 Couldn't resolve host. The given remote host was not resolved",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$this->error="{CURLE_COULDNT_RESOLVE_HOST}";
				return false;
			}
			
			if($error==7){
				writelogs("error 7 Failed to connect() to host or proxy",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$this->error="{CURLE_COULDNT_CONNECT}";
				return false;
			}
			if($error==26){
				writelogs("Curl:: error 26 There was a problem reading a local file or an error returned by the read callback.",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$this->error="{CURLE_READ_ERROR}";
				return false;
			}
			if($error==56){
				writelogs("Curl:: error 56 Failure with receiving network data. ",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$this->error="{CURLE_RECV_ERROR}";
				return false;
			}			
			


			
			return true;
		
		
	}
	
	
	function GetFile($targetpath){
		 if (!extension_loaded('curl') or ($ch = curl_init($this->uri)) === false) {echo "No curl extentions can be loaded\n";return;}
		
			curl_setopt($ch, CURLOPT_FILE, $fp = fopen($targetpath,'w'));
			curl_setopt($ch, CURLOPT_URL, "$this->uri");
			if(preg_match("#https:#",$this->uri)){
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			}
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
			curl_setopt($ch, CURLOPT_TIMEOUT, 50);
			//$this->ArticaProxyServerEnabled=null;
			
			if($this->ArticaProxyServerEnabled=="yes"){
				//curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch,CURLOPT_HTTPPROXYTUNNEL,FALSE); 
				curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
				if($GLOBALS["VERBOSE"]){echo "setting proxy for $this->ArticaProxyServerName:$this->ArticaProxyServerPort\n";}
				curl_setopt ($ch, CURLOPT_PROXY,"$this->ArticaProxyServerName:$this->ArticaProxyServerPort");
				curl_setopt ($ch, CURLOPT_TIMEOUT, 120);
				//curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC | CURLAUTH_NTLM);
				if($this->ArticaProxyServerUsername<>null){
					curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
					curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->ArticaProxyServerUsername.':'.$this->ArticaProxyServerUserPassword);
				}
			}
			
			
			if($GLOBALS["VERBOSE"]){echo "Downloading \"$this->uri\"\n";}
			curl_exec($ch); fclose($fp);
			$error=curl_errno($ch);	
			curl_close($ch);
			
			
			
			if(!$this->ParseError($error)){
				writelogs("datas: $this->uri (". strlen($this->data)." bytes) error:$error",__CLASS__.'/'.__FUNCTION__,__FILE__);
				return false;
			}
			@file_put_contents($this->data,$targetpath);
			return true;
	}
	
	
	function postFile($fieldname,$filepath){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$this->uri");
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
    curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));   
    // same as <input type="file" name="file_box">
    $post = array(
        "$fieldname"=>"@$filepath",
    );
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post); 
    writelogs("Executing posting $fieldname/$filepath",__CLASS__.'/'.__FUNCTION__,__FILE__);
    $this->data = curl_exec($ch);

	$error=curl_errno($ch);		
			writelogs("datas: $this->uri (". strlen($this->data)." bytes) error:$error",__CLASS__.'/'.__FUNCTION__,__FILE__);
			
			if($error==6){
				writelogs("error 6 ($this->uri) in protocol",__CLASS__.'/'.__FUNCTION__,__FILE__);
				curl_close($ch);
				return false;
			}
			
			
			curl_close($ch);
			return true;    
    
    
	}
	
	
}