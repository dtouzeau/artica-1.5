<?php
include_once(dirname(__FILE__)."/logs.inc");
include_once(dirname(__FILE__)."/class.user.inc");
/**
 * Telnet class
 * 
 * Used to execute remote commands via telnet connection 
 * Usess sockets functions and fgetc() to process result
 * 
 * All methods throw Exceptions on error
 * 
 * Written by Dalibor Andzakovic <dali@swerve.co.nz>
 * Based on the code originally written by Marc Ennaji and extended by 
 * Matthias Blaser <mb@adfinis.ch>
 */
class EmuleTelnet {
	
    private $host;
    private $port;
    private $timeout;
    
	private $socket  = NULL;
    private $buffer = NULL;
	private $prompt;
	private $errno;
	public $errstr;
	
	private $NULL;
	private $DC1;
    private $WILL;
    private $WONT;
    private $DO;
    private $DONT;
    private $IAC;
    public $ok=false;
    public $HASH_USERS=array();
    private $HASH_GROUPS=array();
    public $AUTH=false;
    public $userid="";
    const TELNET_ERROR = FALSE;
    const TELNET_OK = TRUE;        

    /**
     * Constructor. Initialises host, port and timeout parameters
     * defaults to localhost port 23 (standard telnet port)
     * 
     * @param string $host Host name or IP addres
     * @param int $port TCP port number
     * @param int $timeout Connection timeout in seconds
     * @return void
     */
    public function __construct($host = '127.0.0.1', $port = '4000', $timeout = 5){
        	    	
		$this->host = $host;
    	$this->port = $port;
    	$this->timeout = $timeout;
    	
    	// set some telnet special characters
    	$this->NULL = chr(0);
    	$this->DC1 = chr(17);
    	$this->WILL = chr(251);
	    $this->WONT = chr(252);
	    $this->DO = chr(253);
	    $this->DONT = chr(254);
	    $this->IAC = chr(255);
	        		    
    	$this->connect();  		
    	
    }
    
    /**
     * Destructor. Cleans up socket connection and command buffer
     * 
     * @return void 
     */
    public function __destruct() {
    	
    	// cleanup resources
    	$this->disconnect();
    	$this->buffer = NULL;
    	
    }

    /**
     * Attempts connection to remote host. Returns TRUE if sucessful.      
     * 
     * @return boolean
     */
    public function connect(){
        
    	// check if we need to convert host to IP
    	if (!preg_match('/([0-9]{1,3}\\.){3,3}[0-9]{1,3}/', $this->host)) {		
    		$ip = gethostbyname($this->host);
    		if($this->host == $ip){
    			    			
    			throw new Exception("Cannot resolve $this->host");
    			
    		} else{
    			$this->host = $ip; 
    		}
    	}
    	
    	// attempt connection
    	writelogs("connect to $this->host on port $this->port",__CLASS__."/".__FUNCTION__,__FILE__);
    	$this->socket = fsockopen($this->host, $this->port, $this->errno, $this->errstr, $this->timeout);

        if (!$this->socket){       
        	$this->errstr=__FUNCTION__. "::Cannot connect to $this->host on port $this->port";	
        	writelogs("Cannot connect to $this->host on port $this->port",__CLASS__."/".__FUNCTION__,__FILE__);
        }
        $this->ok=true;
        return self::TELNET_OK;
    }

    /**
     * Closes IP socket
     * 
     * @return boolean
     */
	public function disconnect(){
        if ($this->socket){
            if (! fclose($this->socket)){
                throw new Exception("Error while closing telnet socket");                
            }
            $this->socket = NULL;
        }        
        return self::TELNET_OK;
    }
    
    /**
     * Executes command and returns a string with result.
     * This method is a wrapper for lower level private methods
     * 
     * @param string $command Command to execute      
     * @return string Command result
     */
 	public function exec($command) {
 		$this->connect();
 		writelogs("[$this->userid]: first command=\"$command\"",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);
 		
 		
 		if(!$this->socket){
 			writelogs("Connection failed",__CLASS__."/".__FUNCTION__,__FILE__);
 			$this->ok=false;
 			return array();
 		} 		
 		
 		if($this->AUTH){
 			if(trim($this->userid)<>null){
 				$user=new user($this->userid);
 				writelogs("authentication has [$this->userid]",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);
 				$command="auth $this->userid $user->password\n$command";
 			}
 		}
 		
 		writelogs("[$this->userid]: execute: \"$command\nq\n\"",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);
    	fputs($this->socket, "$command\nq\n");	
    	
    	
		$timeout = 3;
		$start = time();    	
    	
 		while (!feof($this->socket) && time() < ($start + $timeout)){
 			
 			$value= fgets($this->socket, 4096);
			if(trim($value)==null){continue;}
			
			
			if(strpos($value,"Welcome to MLDonkey")>0){continue;}
			$value = preg_replace("/\[\dm/", "", $value);
			$value = preg_replace("/\[3\dm/", "", $value);
			$value = preg_replace("/\[2;37;0m/", "", $value);
			$value = preg_replace("/^\s*/", "", $value);
			$value = preg_replace("/\e/", "", $value);
			$value = preg_replace("/��.*/", "", $value);
			$value=str_replace("> > $command","",$value);
			$value=str_replace("MLdonkey command-line:","",$value);
			$value=str_replace("Use ? for help","",$value);
			$value=str_replace("Welcome on mldonkey command-line","",$value);
			if(preg_match("#SECURITY WARNING#",$value)){continue;}
			if(preg_match("#Full access enabled#",$value)){continue;}
 			if(preg_match("#exception\s+\[#",$value)){
 				$return[]=$value;
 				break;
 				$this->disconnect();
 			}
 			
			if(preg_match("#> >\s+#",$value)){continue;}
 			if(trim($value)==">"){
 				break;
 				$this->disconnect();
 				continue;
 			}
			writelogs("$value",__CLASS__."/".__FUNCTION__,__FILE__);
			$return[]=$value;
 			
	}
	
	writelogs("execute: \"$command\" ". count($return)." rows",__CLASS__."/".__FUNCTION__,__FILE__);
	$this->ok=true;
	return $return;
 		
 		
    }
    
    function GetListOfAllServer(){
    	$array=$this->exec("vma");
    	while (list ($num, $val) = each ($array) ){
    		if(preg_match("#\[(.+?)\s+([0-9]+)\]\s+([0-9\.:]+)\s+(.+)#",$val,$re)){
    			$server_ip=$re[3];
    			$servers[$server_ip]["INDEX"]=$re[2];
    			$servers[$server_ip]["TYPE"]=$re[1];
    			$servers[$server_ip]["NAME"]=$re[4];
    		}
    		if(preg_match("#(.+?)\s+Users:([0-9]+)\s+Files:([0-9]+)\s+State:(.*)#",$val,$re)){
    			$servers[$server_ip]["COUNTRY"]=$re[1];
    			$servers[$server_ip]["USERS"]=$re[2];
    			$servers[$server_ip]["FILES"]=$re[3];
    			$servers[$server_ip]["STATE"]=$re[4];
    		}
    		
    		
    		
    	}
    	
    	return $servers;
    	
    }
    
    public function LoadUsers(){
    	$array=$this->exec("users");
    	while (list ($num, $val) = each ($array) ){
    		$admin=false;
    		if(preg_match("#^(.+?)\s+(true|false)\s+(.*)\s+(.+?)\s+(.*?)\s+(.*?)\s+(unlimited|[0-9]+)\s+([0-9]+)#",$val,$re)){
    		if($re[2]=="true"){$admin=true;}
    		if($re[4]=="none"){$re[4]=null;}
    		if($re[7]=="unlimited"){$re[7]=0;}
    			$this->HASH_USERS[trim($re[1])]=array(
    				"ADMIN"=>$admin,
    				"ENABLED"=>true,
    				"GROUP"=>trim($re[3]),
    				"DGROUP"=>$re[4],
    				"EMAIL"=>$re[5],
    				"HomeDirectory"=>$re[6],
    				"MAX_DOWNLOADS"=>$re[7],
    				"DOWNLOADS"=>$re[8],
					);
					continue;
    		}
    	}
    	
    	$admin=false;
    	if(preg_match("#^(.+?)\s+(true|false)\s+([0-9]+)\s+([0-9]+)$#",trim($val),$re)){
    		if($re[2]=="true"){$admin=true;}
    		$this->HASH_GROUPS[$re[1]]=array("ADMIN"=>$admin,"MEMBERS"=>$re[2],"DOWNLOADS"=>$re[3]);
    	}
	}
	
	public function UserDelete($uid){
		unset($_SESSION["MLDONKEY_$uid"]);
		$array=$this->exec("userdel $uid");
		$results=trim(implode(" ",$array));
		if(preg_match("#does not exist#",$results)){
			return true;
		}
		echo "<$results>";
	}
	
	public function UserAdd($uid,$max_downloads=0){
		
		$user=new user($uid);
		if($max_downloads==null){$max_downloads=0;}
		writelogs("Adding user :$uid ($user->DisplayName) max downloads:$max_downloads",__CLASS__."/".__FUNCTION__,__FILE__);
		$hash=$user->Groups_list();
		
		while (list ($gidNumber,$groupname) = each ($hash) ){
			$this->GroupAdd($gidNumber);
		}
		
		$commit_dir=$user->homeDirectory."/downloads";
		if($max_downloads>0){$cmd_max_downloads=$max_downloads;}
		$string="useradd $uid $user->password $user->group_id $commit_dir $user->mail $cmd_max_downloads";
		$results=trim(@implode(" ",$this->exec($string)));
		
		if(preg_match("#added#",$results)){
			$_SESSION["MLDONKEY_$uid"]["ENABLED"]=true;
			return true;
		}
		
		if(preg_match("#Password of.+?changed#",$results)){
			$_SESSION["MLDONKEY_$uid"]["ENABLED"]=true;
			return true;
		}
		$_SESSION["MLDONKEY_$uid"]["ENABLED"]=false;
		echo "<$results>";
		
	}
	
	private function GroupAdd($gpid=null){
		$results=trim(@implode(" ",$this->exec("groupadd $gpid admin:false")));
		writelogs("Adding group $gpid \"$results\"",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);
	}
	
	
	
	public function UserIsActivated($uid){
		if(is_array($_SESSION["MLDONKEY_$uid"])){
			writelogs("$uid -> {$_SESSION["MLDONKEY_$uid"]["ENABLED"]}",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);
			return $_SESSION["MLDONKEY_$uid"]["ENABLED"];
		}
		
		$this->LoadUsers();
		if(!is_array($this->HASH_USERS[$uid])){
			writelogs("HASH_USERS[$uid] is not an array",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);
			$_SESSION["MLDONKEY_$uid"]["ENABLED"]=false;
			return false;
		}
		$_SESSION["MLDONKEY_$uid"]["ENABLED"]=true;
		return true;
	}
	
	public function import_servers(){
		//$results=trim(@implode(" ",$this->exec("servers http://www.gruk.org/server.met")));
		$results=$results. "\n".trim(@implode(" ",$this->exec("servers http://www.peerates.net/SERVERS.PHP")));
		writelogs("-> $results",__CLASS__."/".__FUNCTION__,__FILE__);
		
		$results=$results. "\n".trim(@implode(" ",$this->exec("ov_web http://www.peerates.net/SERVERS.PHP")));
		
		
		echo $results;
		
	}
	
	public function connect_server($index){
		$results=trim(@implode(" ",$this->exec("c $index")));
		echo $results;
	}
	public function disconnect_server($index){
		$results=trim(@implode(" ",$this->exec("x $index")));
		echo $results;
	}
	
	public function add_search($uid,$string,$minsizeMB=0,$type=null,$and2=null){
		$this->AUTH=true;
		$this->userid=$uid;
		$f[]="s";
		if($type<>null){$f[]="-media $type";}
		if($minsizeMB>0){
			$minsizeMB=($minsizeMB*1000)*1024;
			$f[]="-minsize $minsizeMB";
		}
		
		if($and2<>null){$f[]="-and $and2";}
		
		$f[]="\"$string\"";
		$results=$this->exec(@implode(" ",$f));
		echo trim(@implode(" ",$results));
		$this->exec("xs");
		$this->exec("save");
	}
	
	public function del_search($uid,$num){
		$this->AUTH=true;
		$this->userid=$uid;
		$results=$this->exec("forget $num");
		echo trim(@implode(" ",$results));
	}
	
	public function list_searchs($uid){
		$this->AUTH=true;
		$this->userid=$uid;	
		$results=$this->exec("vs");
		if(!is_array($results)){return array();}
		while (list ($num, $val) = each ($results) ){
			if(preg_match("#^\[([0-9]+).*?\](.+)#",$val,$re)){
				$res[$re[1]]=$this->understand_query_pattern($re[2]);
			}
			
		}
		return $res;
	}
	
	public function list_searchs_results($uid,$index){
		$this->AUTH=true;
		$this->userid=$uid;	
		$results=$this->exec("vr $index");
		while (list ($num, $val) = each ($results) ){
			if(preg_match("#\[([0-9\s]+)\]\s+([0-9]+)\s+([0-9]+)\s+(.+?)\s+\s+.+?urn:(.+)#",$val,$re)){
				if($re[2]<1){continue;}
				if(preg_match("#ed2k:(.+)#",$re[5],$t)){$EDK=$t[1];}
				$res[$re[1]]=array(
					"LINK_EDK"=>base64_encode($re[2]."|$EDK"),
					"SIZE"=>FormatBytes($re[2]/1024),
					"SOURCES"=>$re[3],
					"FILE"=>htmlspecialchars($re[4]),
					"LINK"=>"urn:{$re[5]}"
				
				);
			}
			
		}
		return $res;
		
		
	}
	
	public function eMuleIDServers(){
		$results=$this->exec("id");
		$array=array();
		while (list ($num, $val) = each ($results) ){
			if(preg_match("#For\s+(.+?)\s+\((.+?)\)\s+--->\s+(.+?)\s+\((.+?)\)#",$val,$re)){
				$array[trim($re[1])]=array("NAME"=>$re[2],"PUBLIC_IP"=>$re[3],"ID"=>$re[4]);
			}
			
		}
		
		return $array;
	}

	
	
	public function download_donkey($uid,$size,$md4){
		$this->AUTH=true;
		$this->userid=$uid;	
		if($_SESSION["MLDONKEY_$uid"]["DOWNLOAD_TASK"][md5($size.$md4)]){
			$tpl=new templates();
			echo $tpl->javascript_parse_text('{FILE_ALREADY_SCHEDULED}');
			return;
		}
		
		
		$results=$this->exec("dd $size $md4");
		$_SESSION["MLDONKEY_$uid"]["DOWNLOAD_TASK"][md5($size.$md4)]=true;
		echo trim(@implode(" ",$results));

	}
	
	public function parameters(){
		$results=$this->exec("voo");
		while (list ($num, $val) = each ($results) ){
			if(preg_match("#(.+?)=(.+)#",$val,$re)){
				$array[trim($re[1])]=trim($re[2]);
			}
			
		}
		return $array;
	}
	
	public function parameters_save($key,$value){
		$results=$this->exec("set $key $value");
		return trim(@implode(" ",$results));
	}	
	
	public function SaveConfig(){
		$results=$this->exec("save");
	}
	
	public function download_queue($uid){
		$this->AUTH=true;
		$this->userid=$uid;	
		$results=$this->exec("vd");
		$array=array();
		while (list ($num, $val) = each ($results) ){
			if(preg_match("#Down:\s+([0-9a-zA-Z\/\.\s]+).+?\|\s+Up:\s+([0-9a-zA-Z\/\.\s]+).+?\|\s+Shared:\s+([0-9a-zA-Z\/\.\s]+).+?\|\s+Downloaded:\s+([0-9a-zA-Z\/\.\s]+).+?\|\s+Uploaded:\s+([0-9a-zA-Z\/\.\s]+)#",$val,$re)){
				$array["INFOS"]=array(
					"DOWNLOAD_KBS"=>$re[1],
					"UPLOADED_KBS"=>$re[2],
					"SHARED"=>$re[3],
					"DOWNLOADED"=>$re[4],
					"UPLOADED"=>$re[5]);
					continue;	
					}
			
			
			if(preg_match("#\[[A-Z]+..*?([0-9]+)\].*?([0-9]+)\s+(.+?)\s+(.+?)\s+(.+?)\s+([0-9\-\.]+)\s+([0-9\.a-z]+)\s+([0-9\.a-z]+)\s+([0-9\:\-]+)\s+([0-9\:\-]+)\s+([0-9\/]+)\s+([0-9\.\-|Paused]+)\s+([0-9]+)#",$val,$re)){
				$INDEX=$re[1];
				$INFOS=$this->FileINFOD($uid,$INDEX);
				$FILE=$re[5];
				$POURC_ACCOMPLISH=$re[6];
				$SIZE_ACCOMPLISH=$re[7];
				$SIZE=$re[8];
				$LAST_SEEN=$re[9];
				$OLD=$re[10];
				$SOURCE_ACTIVE=$re[11];
				$RATE=$re[12];
				$PRIORITY=$re[13];
				$array["INFOS"]["LIST"][$INDEX]=array(
					"FILE"=>$FILE,"POURC_ACCOMPLISH"=>$POURC_ACCOMPLISH,"SIZE_ACCOMPLISH"=>$SIZE_ACCOMPLISH,"SIZE"=>$SIZE,
					"LAST_SEEN"=>$LAST_SEEN,
					"OLD"=>$OLD,
					"SOURCE_ACTIVE"=>$SOURCE_ACTIVE,
					"RATE"=>$RATE,
					"PRIORITY"=>$PRIORITY,
					"INFOS"=>$INFOS
				);
				continue;
				}
		}
		
		return $array;
		
	}
	
	public function FileINFOD($uid,$index){
		$this->AUTH=true;
		$this->userid=$uid;	
		$results=$this->exec("vd $index");
		$array=array();	
		while (list ($num, $found) = each ($results) ){
			if(preg_match("#Probable name:#",$found)){
				$name=true;
				continue;
			}
			if(preg_match("#[0-9]+\s+sources:#",$found,$re)){$name=false;$sources=true;continue;}
			
			if($name){$array["NAMES"][]=$found;}
			
			
			
			if($sources){
				if(preg_match("#\[.*?([0-9]+)\]\s+(.*?)\s+\(last_ok <(.+?)>\)#",$found,$re)){
					$array["SOURCES"][$re[1]]=array("name"=>$re[2],"last"=>$re[3]);
				}
				
			}
		}
		
		return $array;
	}
	
	
	public function download_FileTP($uid,$uri){
		$this->AUTH=true;
		$this->userid=$uid;	
		$results=trim(@implode(" ",$this->exec("http $uri")));
		echo $results;	
		
	}
	
	
        
	private function understand_query_pattern($pattern){
		
		if(!preg_match_all("#\[([a-z]+)\]([A-Z<>]+)(.+?)\)#",$pattern,$re)){return $pattern;}
		
		if(preg_match("#\(found ([0-9]+)#",$pattern,$ra)){$count=$ra[1];}
		if(preg_match_all("#\(CONTAINS\[(.*?)\]\)#",$pattern,$rz)){
				$string=@implode(",",$rz[1]);
		}
		
		while (list ($num, $found) = each ($re[0]) ){
			$re[3][$num]=str_replace("[","",$re[3][$num]);
			$re[3][$num]=str_replace("]","",$re[3][$num]);
			$re[3][$num]=trim($re[3][$num]);
			$re[2][$num]=trim($re[2][$num]);
			$re[2][$num]=str_replace(">","higher_than",$re[2][$num]);
			if(is_numeric(trim($re[3][$num]))){
				$re[3][$num]=FormatBytes($re[3][$num]/1024);
				
			}else{
			$re[3][$num]="{{$re[3][$num]}}";
			}
			$f[]="
			<li>{{$re[1][$num]}} {{$re[2][$num]}} {$re[3][$num]}</li>";
			
		}
		
		$a="<strong style='color:#005447;font-size:13px'>&laquo;$string&raquo;</strong>".@implode(" ",$f)."<hr><div style='text-align:right'><strong><i>$count {files}</i></strong></div>";
		writelogs("$pattern =\"$a\"",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);
		return $a;
		}
		
		
	public function download_file_queue_run($uid,$index){
		$this->AUTH=true;
		$this->userid=$uid;	
		$results=trim(@implode(" ",$this->exec("resume $index")));
		echo $results;
	}
	
	public function download_file_queue_pause($uid,$index){
		$this->AUTH=true;
		$this->userid=$uid;	
		$results=trim(@implode(" ",$this->exec("pause $index")));
		echo $results;
	}	
	
	public function download_file_queue_stop($uid,$index){
		$this->AUTH=true;
		$this->userid=$uid;	
		$results=trim(@implode(" ",$this->exec("cancel $index")));
		$this->exec("confirm yes");
		
	}		
	
    
    
    /**
     * Attempts login to remote host.
     * This method is a wrapper for lower level private methods and should be 
     * modified to reflect telnet implementation details like login/password
     * and line prompts. Defaults to standard unix non-root prompts
     * 
     * @param string $username Username
     * @param string $password Password
     * @return boolean 
     */
    public function login($username, $password) {
    	
    	try{
    		$this->setPrompt('login:');
			$this->waitPrompt();
			$this->write($username);
			$this->setPrompt('Password:');
			$this->waitPrompt();
			$this->write($password);
			$this->setPrompt();
			$this->waitPrompt();
    	} catch(Exception $e){
    		
    		throw new Exception("Login failed.");
    	}
    	
    	return self::TELNET_OK;
    }
    
    /**
     * Sets the string of characters to respond to.
     * This should be set to the last character of the command line prompt
     * 
     * @param string $s String to respond to
     * @return boolean
     */
	public function setPrompt($s = '$'){
        $this->prompt = $s;
        return self::TELNET_OK;
    }
    
    /**
     * Gets character from the socket
     *     
     * @return void
     */
    private function getc() {
    	return fgetc($this->socket); 
    }
    
    /**
     * Clears internal command buffer
     * 
     * @return void
     */
    private function clearBuffer() {
    	$this->buffer = '';
    }

    /**
     * Reads characters from the socket and adds them to command buffer.
     * Handles telnet control characters. Stops when prompt is ecountered.
     * 
     * @param string $prompt
     * @return boolean
     */
    private function readTo($prompt){

        if (!$this->socket){
            throw new Exception("Telnet connection closed");            
        }
        
        // clear the buffer 
		$this->clearBuffer();
		
		do{
			
			$c = $this->getc();
			
            if ($c === false){
                throw new Exception("Couldn't find the requested : '" . $prompt . "', it was not in the data returned from server : '" . $buf . "'");                
            }            

            // Interpreted As Command            
            if ($c == $this->IAC){                            	
            	if ($this->negotiateTelnetOptions()){                        
                    continue;
                }
            }

            // append current char to global buffer           
            $this->buffer .= $c;

            // we've encountered the prompt. Break out of the loop
            if ((substr($this->buffer, strlen($this->buffer) - strlen($prompt))) == $prompt){
                return self::TELNET_OK;                
            }
            
		} while($c != $this->NULL || $c != $this->DC1);
    }

    /**
     * Write command to a socket
     * 
     * @param string $buffer Stuff to write to socket
     * @param boolean $addNewLine Default true, adds newline to the command 
     * @return boolean
     */
    private function write($buffer, $addNewLine = true){

    	if (!$this->socket){
    		writelogs("Telnet connection closed",__CLASS__."/".__FUNCTION__,__FILE__);
            return;            
        }

        // clear buffer from last command
        $this->clearBuffer();
        
        if ($addNewLine == true){
            $buffer .= "\n";
        }
		writelogs("$buffer",__CLASS__."/".__FUNCTION__,__FILE__); 
        if (!fwrite($this->socket, $buffer) < 0){
            writelogs("Error writing to socket",__CLASS__."/".__FUNCTION__,__FILE__); 
            return;          
        }
        
        return self::TELNET_OK;
    }
    
    /**
     * Returns the content of the command buffer
     * 
     * @return string Content of the command buffer 
     */
    private function getBuffer(){        
        // cut last line (is always prompt)
        writelogs("$buffer",__CLASS__."/".__FUNCTION__,__FILE__); 
        $buf = explode("\n", $this->buffer);
        unset($buf[count($buf)-1]);
        $buf = implode("\n",$buf);
        return trim($buf);
    }

    /**
     * Telnet control character magic
     * 
     * @param string $command Character to check
     * @return boolean
     */
	private function negotiateTelnetOptions(){
		
		$c = $this->getc();

        if ($c != $this->IAC){

	    	if (($c == $this->DO) || ($c == $this->DONT)){
	             
	    		$opt = $this->getc();
	            fwrite($this->socket, $this->IAC . $this->WONT . $opt);
	            
	        } else if (($c == $this->WILL) || ($c == $this->WONT)) {
	            
	        	$opt = $this->getc();            
	            fwrite($this->socket, $this->IAC . $this->DONT . $opt);
	            
	        } else {
	            throw new Exception('Error: unknown control character ' . ord($c ));            
	        }
        } else{
        	throw new Exception('Error: Something Wicked Happened');        	
        }

        return self::TELNET_OK;
    }

    /**
     * Reads socket until prompt is encountered
     */
    private function waitPrompt(){
        return $this->readTo($this->prompt);
    }       
}


function todo(){
	$host = 'localhost';
$dbname = 'mldonkey';
$dbuser = 'mldonkey';
$dbpass = '';

$path = $_ENV['INCOMING'] . "/" . $_ENV['FILENAME'];
$fileowner = $_ENV['FILE_OWNER'];
$filegroup = $_ENV['FILE_GROUP'];

$filename = htmlentities($_ENV['FILENAME'], ENT_QUOTES);
$filetype = htmlentities(`file -b "$path"`, ENT_QUOTES);
$filesize = $_ENV['FILESIZE'];
$end = time();
$duration = $_ENV['DURATION'];

$rate = ($_ENV['FILESIZE'] / $_ENV['DURATION']);
$network = $_ENV['NETWORK'];
$hash = substr($_ENV['FILEID'], 9, 32);
if ($network == "Donkey") {
    $ed2klink = $_ENV['ED2K_HASH'];
}

$connect = mysql_connect($hostname, $dbuser, $dbpass) or die (mysql_error());
mysql_select_db($dbname, $connect);
$insert = "INSERT INTO mldonkey (filename, filetype, filesize, duration, end, rate, network, hash, ed2klink, fileowner, filegroup) VALUES ('$filename', '$filetype', '$filesize', '$duration', '$end', '$rate', '$network', '$hash', '$ed2klink', '$fileowner', '$filegroup')";
$results = mysql_query($insert) or die(mysql_error());
mysql_close($connect);
	
}
?> 
