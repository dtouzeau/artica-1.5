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
	var $interface=null;
	var $noproxyload=false;
	var $authname=null;
	var $authpass=null;
	function ccurl($uri,$noproxyload=false,$interface=null){
			$this->uri=$uri;
			$this->interface=$interface;
			if($noproxyload){
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
	}
	
	function get(){
		$curlPost=null;
			if(isset($this->parms)){
				if(count($this->parms)>0){
					while (list ($num, $ligne) = each ($this->parms)){$curlPost .='&'.$num.'=' . urlencode($ligne);}	
				}
			}	
				
			$ch = curl_init();
			if($this->interface<>null){curl_setopt($ch, CURLOPT_INTERFACE,$interface);}
			curl_setopt($ch, CURLOPT_URL, "$this->uri");
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			if($this->authname<>null){
				if($GLOBALS["VERBOSE"]){writelogs("Enable website authentication has $this->authname",__CLASS__.'/'.__FUNCTION__,__FILE__);}
				curl_setopt($ch, CURLOPT_USERPWD, "$this->authname:$this->authpass");
			}else{
				if($GLOBALS["VERBOSE"]){writelogs("no website authentication has enabled",__CLASS__.'/'.__FUNCTION__,__FILE__);}
			}
			if($curlPost<>null){curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);}	
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
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
			curl_setopt($ch, CURLOPT_FILE, $fp = fopen($targetpath,'w'));
			curl_setopt($ch, CURLOPT_URL, "$this->uri");
			curl_setopt($ch, CURLINFO_HEADER_OUT,FALSE);
			
			//curl_setopt($ch, CURLOPT_NOBODY,true);
			
			if(preg_match("#https:#",$this->uri)){
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			}
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
			curl_setopt($ch, CURLOPT_TIMEOUT, 50);
			//$this->ArticaProxyServerEnabled=null;
			
			if($this->ArticaProxyServerEnabled=="yes"){
				if($GLOBALS["VERBOSE"]){echo "Loading proxy configuration...\n";}
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
			}else{
				if($GLOBALS["VERBOSE"]){echo "Proxy disabled....\n";}
			}
			
			
			if($GLOBALS["VERBOSE"]){echo "Downloading \"$this->uri\"\n";}
			$result=curl_exec($ch); 
			fclose($fp);
			
			$error=curl_errno($ch);	
			curl_close($ch);
			if(!is_file($targetpath)){
			if($GLOBALS["VERBOSE"]){echo "path \"$targetpath\" no such file\n";}
			}
			
			
			if(!$this->ParseError($error)){
				writelogs("datas: $this->uri (". strlen($this->data)." bytes) error:$error",__CLASS__.'/'.__FUNCTION__,__FILE__);
				return false;
			}
			writelogs("datas: $this->uri (". strlen($result)." bytes) filesize=".filesize($targetpath)." bytes error:$error",__CLASS__.'/'.__FUNCTION__,__FILE__);
			
			
			//@file_put_contents($this->data,$targetpath);
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