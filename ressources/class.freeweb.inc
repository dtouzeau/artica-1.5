<?php
	include_once(dirname(__FILE__).'/class.templates.inc');
	include_once(dirname(__FILE__).'/class.ldap.inc');
	include_once(dirname(__FILE__).'/class.users.menus.inc');
	include_once(dirname(__FILE__).'/class.mysql.inc');
	include_once(dirname(__FILE__).'/class.groups.inc');
	include_once(dirname(__FILE__).'/class.lvm.root.inc');
	
	
	class freeweb{
		var $servername;
		var $groupware='';
		var $APACHE_DIR_SITES_ENABLED='';
		var $SSL_enabled=false;
		var $AS_ROOT=false;
		var $WORKING_DIRECTORY='';
		var $SQUID_CACHMGR='';
		var $APACHE_RUN_USER='';
		var $LimitByIp=0;
		var $Params=array();
		var $mod_rewrite='';
		var $lvm_size=0;
		var $lvm_vg="";
		var $UseLoopDisk=0;
		var $LoopMounts="";
		var $useMysql=0;
		var $mysql_database;
		var $mysql_password;
		var $mysql_username;
		var $ftpuser;
		var $ftppassword;
		var $useSSL=0;
		var $uid;
		var $useFTP=0;
		var $ou;
		var $error;
		var $www_dir;
		var $ServerPort=0;
		var $UseReverseProxy=0;
		var $ProxyPass;
		var $Forwarder=0;
		var $ForwardTo=0;
		
		function freeweb($servername=null){
			$users=new usersMenus();
			$this->APACHE_DIR_SITES_ENABLED=$users->APACHE_DIR_SITES_ENABLED;
			$this->SQUID_CACHMGR=$users->SQUID_CACHMGR;
			$this->APACHE_RUN_USER=$users->APACHE_RUN_USER;
			if($GLOBALS["posix_getuid"]==0){$this->AS_ROOT=true;}
			if($servername<>null){
				$this->WORKING_DIRECTORY="/var/www/$servername";
				$this->servername=$servername;
				$this->load();
			}
		}
		
		private function load(){
			if($this->AS_ROOT){echo "Starting......: Apache \"$this->servername\" Load DB..\n";}
			$sql="SELECT * from freeweb WHERE servername='$this->servername'";
			$q=new mysql();
			$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
			$this->groupware=$ligne["groupware"];
			$this->servername=$ligne["servername"];
			if(strlen($ligne["Params"])>5){$this->Params=@unserialize(base64_decode($ligne["Params"]));}
			$this->lvm_size=$ligne["lvm_size"];
			$this->lvm_vg=$ligne["lvm_vg"];
			$this->UseLoopDisk=$ligne["UseLoopDisk"];
			$this->LoopMounts=$ligne["LoopMounts"];
			$this->useMysql=$ligne["useMysql"];
			if($ligne["useSSL"]==1){$this->SSL_enabled=true;}
			if(isset($this->Params["LimitByIp"]["enabled"])){$this->LimitByIp=$this->Params["LimitByIp"]["enabled"];}
			$this->mod_rewrite=base64_decode($ligne["mod_rewrite"]);
			if($this->UseLoopDisk==1){$this->lvm_vg=null;$this->lvm_size=0;}
			$this->mysql_database=$ligne["mysql_database"];
			$this->mysql_password=$ligne["mysql_password"];
			$this->mysql_username=$ligne["mysql_username"];
			$this->ftpuser=$ligne["ftpuser"];
			$this->ftppassword=$ligne["ftppassword"];
			$this->useSSL=$ligne["useSSL"];
			$this->uid=$ligne["uid"];
			$this->useFTP=$ligne["useFTP"];
			$this->ou=$ligne["ou"];
			$this->www_dir=$ligne["www_dir"];
			$this->ServerPort=$ligne["ServerPort"];
			$this->UseReverseProxy=$ligne["UseReverseProxy"];
			$this->ProxyPass=$ligne["ProxyPass"];
			$this->Forwarder=$ligne["Forwarder"];
			$this->ForwardTo=$ligne["ForwardTo"];
			
		}
		
		public function CreateSite(){
			include_once(dirname(__FILE__)."/class.user.inc");
			
			$sqladd="INSERT INTO freeweb (mysql_password,mysql_username,ftpuser,ftppassword,useSSL,
			servername,mysql_database,uid,useMysql,
			useFTP,lvm_vg,lvm_size,UseLoopDisk,LoopMounts,ou,groupware,www_dir)
			VALUES('$this->mysql_password','$this->mysql_username','$this->ftpuser','$this->ftppassword','$this->useSSL',
			'$this->servername','$this->mysql_database','$this->uid','$this->useMysql',
			'$this->useFTP','$this->lvm_vg','$this->lvm_size','$this->UseLoopDisk','$this->LoopMounts','$this->ou','$this->groupware','$this->www_dir')";
			
			
		$sqledit="UPDATE freeweb SET 
			mysql_password='$this->mysql_password',
			mysql_username=''$this->mysql_username',
			ftpuser=''$this->ftpuser',
			ftppassword=''$this->ftppassword',
			uid=''$this->uid',
			useMysql='$this->useMysql',
			useFTP='$this->useFTP',
			lvm_vg='$this->lvm_vg',
			lvm_size='$this->lvm_size',
			UseLoopDisk='$this->UseLoopDisk',
			LoopMounts='$this->LoopMounts',
			useSSL='$this->useSSL',
			ou='$this->ou',
			groupware='$this->groupware',
			www_dir='$this->www_dir'
			WHERE servername='$this->servername'";	
				
			
			$sql="SELECT servername FROM freeweb WHERE servername='$this->servername'";
			$q=new mysql();
			$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
			if($ligne["servername"]<>null){$sql=$sqledit;}else{$sql=$sqladd;}
			if(!$q->QUERY_SQL($sql,"artica_backup")){
				$this->error=$q->mysql_error;
				return false;
			}
			$sock=new sockets();
			$sock->getFrameWork("cmd.php?freeweb-website=$this->servername&servername=$this->servername");
			$sock->getFrameWork("cmd.php?freeweb-groupware=$this->servername&servername=$this->servername");
			return true;
			
		}
		
		public function AddDefaultSite(){
			$this->servername="_default_";
			$this->www_dir="/var/www/html";
			$this->CreateSite();
		}
		
		
		public function LimitByIp_list(){
			if(!isset($this->Params["LimitByIp"]["IPS"])){return array();}
			if(!is_array($this->Params["LimitByIp"]["IPS"])){return array();}
			reset($this->Params["LimitByIp"]["IPS"]);
			return $this->Params["LimitByIp"]["IPS"];
			
		}
		
		public function LimitByIp_add($ip){
			if($this->servername==null){return;}
			$this->Params["LimitByIp"]["enabled"]=$this->LimitByIp;
			$this->Params["LimitByIp"]["IPS"][]=$ip;
			$this->SaveParams();
		}
		
		public function LimitByIp_del($ip){
			if($this->servername==null){return;}
			writelogs("Deleting ip $ip",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);
			$this->Params["LimitByIp"]["enabled"]=$this->LimitByIp;
			
			while (list ($num, $ligne) = each ($this->Params["LimitByIp"]["IPS"]) ){
				writelogs("found $ligne",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);
				if($ligne==$ip){
					writelogs("unset $ligne",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);
					unset($this->Params["LimitByIp"]["IPS"][$num]);
				}
			}
			
			$this->SaveParams();

		}
		
		public function QUOS(){
			if(!isset($this->Params["QOS"])){$this->Params["QOS"]["EnableQOS"]=0;}
			$EnableQOS=$this->Params["QOS"]["EnableQOS"];
			if(!is_numeric($EnableQOS)){$EnableQOS=0;}
			if(isset($this->Params["QOS"]["QS_ClientEntries"])){$QS_ClientEntries=$this->Params["QOS"]["QS_ClientEntries"];}
			if(isset($this->Params["QOS"]["QS_SrvMaxConnPerIP"])){$QS_SrvMaxConnPerIP=$this->Params["QOS"]["QS_SrvMaxConnPerIP"];}
			if(isset($this->Params["QOS"]["MaxClients"])){$MaxClients=$this->Params["QOS"]["MaxClients"];}
			if(isset($this->Params["QOS"]["QS_SrvMaxConnClose"])){$QS_SrvMaxConnClose=$this->Params["QOS"]["QS_SrvMaxConnClose"];}
			if(isset($this->Params["QOS"]["QS_SrvMinDataRate"])){$QS_SrvMinDataRate=$this->Params["QOS"]["QS_SrvMinDataRate"];}
			if(isset($this->Params["QOS"]["LimitRequestFields"])){$LimitRequestFields=$this->Params["QOS"]["LimitRequestFields"];}
			if(isset($this->Params["QOS"]["QS_LimitRequestBody"])){$QS_LimitRequestBody=$this->Params["QOS"]["QS_LimitRequestBody"];}
			
			if($EnableQOS==0){
				$f[]="\t<IfModule mod_qos.c>";
				//$f[]="\t\tQS_InvalidUrlEncoding off";
				//$f[]="\t\tQS_ResponseHeaderFilter off";
				//$f[]="\t\tQS_DenyQueryBody off";
				//$f[]="\t\tQS_PermitUriBody off";
				$f[]="\t</IfModule>";
				return @implode("\n",$f);
			}
			
			
			
			if(!is_numeric($QS_ClientEntries)){$QS_ClientEntries=100000;}
			if(!is_numeric($QS_SrvMaxConnPerIP)){$QS_SrvMaxConnPerIP=50;}
			if(!is_numeric($MaxClients)){$MaxClients=256;}
			if(!is_numeric($QS_SrvMaxConnClose)){$QS_SrvMaxConnClose=180;}
			if($QS_SrvMinDataRate==null){$QS_SrvMinDataRate="150 1200";}
			if(!is_numeric($LimitRequestFields)){$LimitRequestFields=30;}
			if(!is_numeric($QS_LimitRequestBody)){$QS_LimitRequestBody=102400;}
			
			
		
			$f[]="\t<IfModule mod_qos.c>";
		  	if($QS_ClientEntries>0){ $f[]="\t\tQS_ClientEntries $QS_ClientEntries";}
		  	if($QS_SrvMaxConnPerIP>0){ $f[]="\t\tQS_SrvMaxConnPerIP $QS_SrvMaxConnPerIP";}
			if($MaxClients>0){ $f[]="\t\tMaxClients $MaxClients";}
			if($QS_SrvMaxConnClose>0){ $f[]="\t\tQS_SrvMaxConnClose $QS_SrvMaxConnClose";}
			if(preg_match("#[0-9]+\s+[0-9]+#",$QS_SrvMinDataRate)){
				$f[]="\t\tQS_SrvMinDataRate $QS_SrvMinDataRate";
			}		 		  		   
			if($LimitRequestFields>0){ $f[]="\t\tLimitRequestFields $LimitRequestFields";}
			if($QS_LimitRequestBody>0){ $f[]="\t\tQS_LimitRequestBody $QS_LimitRequestBody";}			
			$f[]="\t</IfModule>";
			return @implode("\n",$f);
			
		}
		
		
		public function AllowOverride(){
			$AllowOverride="all";
			if(isset($this->Params["SECURITY"])){
				if($this->Params["SECURITY"]["DisableHtAccess"]==1){return "\t\tAllowOverride None";}
			}
			return "\t\tAllowOverride $AllowOverride";
		
		}
		
		public function Charsets(){
			if(!isset($this->Params["AddDefaultCharset"])){return;}
			$this->Params["AddDefaultCharset"]=trim($this->Params["AddDefaultCharset"]);
			if(trim($this->Params["AddDefaultCharset"])==null){return;}
			$f[]="\tAddDefaultCharset {$this->Params["AddDefaultCharset"]}";
			if(strtolower($this->Params["AddDefaultCharset"])=="off"){return @implode("\n",$f);}
			if(strtolower($this->Params["AddDefaultCharset"])=="on"){return @implode("\n",$f);}
			$f[]="\tAddCharset {$this->Params["AddDefaultCharset"]} .php";
			$f[]="\tAddCharset {$this->Params["AddDefaultCharset"]} .html";
			$f[]="\tAddCharset {$this->Params["AddDefaultCharset"]} .xml";
			$f[]="\tAddCharset {$this->Params["AddDefaultCharset"]} .js";
			@implode("\n",$f);
		}

		public function SaveParams(){
			reset($this->Params);
			$conf=base64_encode(serialize($this->Params));
			$sql="UPDATE freeweb SET `Params`='$conf' WHERE servername='$this->servername'";
			$q=new mysql();
			$q->QUERY_SQL($sql,"artica_backup");
			if(!$q->ok){echo $q->mysql_error."\nclass: ".__CLASS__."\nFile: ".basename(__FILE__)."\nLine:".__LINE__;return;}
			$sock=new sockets();
			$sock->getFrameWork("cmd.php?freeweb-website=yes&servername=$this->servername");
			
		}
		
		
		public function AuthUsers(){
			if(!isset($this->Params["LDAP"]["members"])){
				echo "Starting......: Apache \"$this->servername\" AUTH: No members set\n";
				return null;
			}
			$users=$this->Params["LDAP"]["members"];
			echo "Starting......: Apache \"$this->servername\" AUTH: ".count($users)." members set\n";
			if(count($users)==0){return null;}
			while (list ($num, $ligne) = each ($users) ){
				if($num==null){continue;}
				if(preg_match("#^group:@(.+?):([0-9]+)#",$num,$re)){
					$gid=$re[2];
					$group=new groups($gid);
					$f[]="\t\tRequire ldap-group $group->dn";
				}
				
				if(preg_match("#^user:(.+)#",$num,$re)){
					$f[]="\t\tRequire ldap-user {$re[1]}";
				}				
			}
			echo "Starting......: Apache \"$this->servername\" AUTH: ".count($f)." compiled\n";
			if(count($f)==0){return null;}
			return @implode("\n", $f);
			
		}
		
		
		public function mod_evasive(){
			$sock=new sockets();
			$FreeWebsEnableModEvasive=$sock->GET_INFO("FreeWebsEnableModEvasive");
			if($FreeWebsEnableModEvasive<>1){return null;}			
			$q=new mysql();
			if(!isset($this->Params["mod_evasive"])){return;}
			$Params=$this->Params["mod_evasive"];	
			if(!is_numeric($Params["DOSHashTableSize"])){$Params["DOSHashTableSize"]=1024;}
			if(!is_numeric($Params["DOSPageCount"])){$Params["DOSPageCount"]=10;}
			if(!is_numeric($Params["DOSSiteCount"])){$Params["DOSSiteCount"]=150;}
			if(!is_numeric($Params["DOSPageInterval"])){$Params["DOSPageInterval"]=1.5;}
			if(!is_numeric($Params["DOSSiteInterval"])){$Params["DOSSiteInterval"]=1.5;}
			if(!is_numeric($Params["DOSBlockingPeriod"])){$Params["DOSBlockingPeriod"]=10.7;}	
			if(!is_numeric($Params["DOSEnable"])){$Params["DOSEnable"]=0;}	
			if($Params["DOSEnable"]==0){
				echo "Starting......: Apache \"$this->servername\" DDOS evasive disabled\n";
				return; 
			}		

			
			echo "Starting......: Apache \"$this->servername\" DDOS evasive Enabled\n";
 
					
			
			$f[]="<IfModule mod_evasive20.c>";
			$f[]="   DOSHashTableSize {$Params["DOSHashTableSize"]}";
			$f[]="   DOSPageCount {$Params["DOSPageCount"]}";
			$f[]="   DOSSiteCount {$Params["DOSSiteCount"]}";
			$f[]="   DOSPageInterval {$Params["DOSPageInterval"]}";
			$f[]="   DOSSiteInterval {$Params["DOSSiteInterval"]}";
			$f[]="   DOSBlockingPeriod {$Params["DOSBlockingPeriod"]}";
			$f[]="   DOSLogDir  \"/var/log/apache2/$this->servername/mod_evasive.log\"";
			$f[]="   DOSSystemCommand \"/bin/echo `date '+%F %T'` $this->servername  %s >> /var/log/apache2/dos_evasive_attacks.log\"";
			
			
			@mkdir("/var/log/apache2/$this->servername",755,true);
			$sql="SELECT * FROM freewebs_whitelist WHERE servername='$this->servername' $qsearch ORDER BY ID DESC";
			$results=$q->QUERY_SQL($sql,'artica_backup');
			if(!$q->ok){echo "$q->mysql_error\n";}			
			while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){	
				$f[]="   DOSWhitelist {$ligne["ipaddr"]}";
			}
			
			$f[]="</IfModule>"; 
			$f[]="";			
			return @implode("\n",$f);
		}
		
		public function ReverseProxy(){
			if($this->UseReverseProxy==0){return;}
			if(substr($this->ProxyPass, strlen($this->ProxyPass)-1,1)<>'/'){$this->ProxyPass=$this->ProxyPass."/";}
			if(!is_numeric($this->Params["Proxy"]["ProxyRequests"])){$this->Params["Proxy"]["ProxyRequests"]=0;}
			if(!is_numeric($this->Params["Proxy"]["ProxyVia"])){$this->Params["Proxy"]["ProxyVia"]=0;}
			if(!is_numeric($this->Params["Proxy"]["KeepAlive"])){$this->Params["Proxy"]["KeepAlive"]=0;}
			
			if(!isset($this->Params["Proxy"]["ProxyTimeout"])){$this->Params["Proxy"]["ProxyTimeout"]=300;}
			if(!is_numeric($this->Params["Proxy"]["ProxyTimeout"])){$this->Params["Proxy"]["ProxyTimeout"]=300;}
			
			
			
			if($this->Params["Proxy"]["ProxyRequests"]==0){$this->Params["Proxy"]["ProxyRequests"]="Off";}else{$this->Params["Proxy"]["ProxyRequests"]="On";}
			if($this->Params["Proxy"]["ProxyVia"]==0){$this->Params["Proxy"]["ProxyVia"]="Off";}else{$this->Params["Proxy"]["ProxyVia"]="On";}
			if($this->Params["Proxy"]["KeepAlive"]==0){$this->Params["Proxy"]["KeepAlive"]="Off";}else{$this->Params["Proxy"]["KeepAlive"]="On";}
			
			$f[]="ProxyPreserveHost On";
			$f[]="ProxyRequests {$this->Params["Proxy"]["ProxyRequests"]}";
			$f[]="ProxyVia {$this->Params["Proxy"]["ProxyVia"]}";
			$f[]="KeepAlive {$this->Params["Proxy"]["KeepAlive"]}";
			$f[]="ProxyTimeout {$this->Params["Proxy"]["ProxyTimeout"]}";
			if(strlen($this->Params["Proxy"]["AllowCONNECT"])>1){$f[]="AllowCONNECT {$this->Params["Proxy"]["AllowCONNECT"]}";}
			$sql="SELECT * FROM freewebs_proxy WHERE servername='$this->servername'";
			$q=new mysql();
			$results=$q->QUERY_SQL($sql,'artica_backup');
			if(mysql_num_rows($results)==0){return;}
			while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
				if(!preg_match("#(.+?)\s+(.+)#", $ligne["ProxyPass"],$re)){continue;}
				$request_from=trim($re[1]);$redirect_to=trim($re[2]);
				if(substr($request_from, strlen($request_from)-1)<>'/'){$request_from=$request_from."/";}
				if(substr($redirect_to, strlen($redirect_to)-1)<>'/'){$redirect_to=$redirect_to."/";}
				$f[]="ProxyPass $request_from\t$redirect_to";
				$f[]="ProxyPassReverse $request_from\t$redirect_to";
				
			}
			return @implode("\n", $f);
		}
		
		
		
		public function mod_security(){
			$sock=new sockets();
			$FreeWebsEnableModSecurity=$sock->GET_INFO("FreeWebsEnableModSecurity");
			if($FreeWebsEnableModSecurity<>1){return null;}
			if(!isset($this->Params["mod_security"])){return null;}
			$q=new mysql();
			
			$Params=$this->Params["mod_security"];
			if($Params["SecFilterEngine"]<>1){$Params["SecFilterEngine"]="Off";}else{$Params["SecFilterEngine"]="On";}
			if($Params["SecFilterCheckURLEncoding"]<>1){$Params["SecFilterCheckURLEncoding"]="Off";}else{$Params["SecFilterCheckURLEncoding"]="On";}
			if($Params["SecFilterCheckUnicodeEncoding"]<>1){$Params["SecFilterCheckUnicodeEncoding"]="Off";}else{$Params["SecFilterCheckUnicodeEncoding"]="On";}
			if($Params["SecFilterScanPOST"]<>1){$Params["SecFilterScanPOST"]="Off";}else{$Params["SecFilterScanPOST"]="On";}
			if($Params["SecFilterDefaultAction"]==null){$Params["SecFilterDefaultAction"]="Hello World!";}
		
			$f[]="<IfModule security2_module>";
			$f[]="   SecRuleEngine {$Params["SecFilterEngine"]}";
			$f[]="   SecAuditEngine RelevantOnly";
			$f[]="   SecAuditLog /var/log/apache2/$this->servername/modsec_audit_log";
			$f[]="   SecDebugLog /var/log/apache2/$this->servername/modsec_debug_log";
			$f[]="   SecDebugLogLevel 1";
			$f[]="   SecRequestBodyAccess {$Params["SecFilterScanPOST"]}";
			$f[]="   SecDefaultAction \"phase:2,deny,log,status:500\"";
			@mkdir("/var/log/apache2/$this->servername",755,true);
			$sql="SELECT * FROM freewebs_secfilters WHERE servername='$this->servername' $qsearch ORDER BY ID DESC";
			$results=$q->QUERY_SQL($sql,'artica_backup');
			if(!$q->ok){echo "$q->mysql_error\n";}
	
			while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){	
					$ligne["value"]=str_replace("+","\+",$ligne["value"]);
					$ligne["value"]=str_replace(".","\.",$ligne["value"]);
					$ligne["value"]=str_replace("?","\?",$ligne["value"]);
					$ligne["value"]=str_replace(":","\:",$ligne["value"]);
					$ligne["value"]=str_replace("(","\(",$ligne["value"]);
					$ligne["value"]=str_replace(")","\)",$ligne["value"]);
					$ligne["value"]=str_replace("[","\[",$ligne["value"]);
					$ligne["value"]=str_replace("]","\]",$ligne["value"]);
					$ligne["value"]=str_replace("*",".*?",$ligne["value"]);				
				if($ligne["key"]==null){

					$f[]="   SecRule REQUEST_LINE   \"{$ligne["value"]}\" \"deny,log,status:403,msg:'Rule: {$ligne["ID"]}:severity %{HIGHEST_SEVERITY}'\"";
				}else{
					if(preg_match("#(.+?)\s+([deny|pass|log|,]+)#",$ligne["value"],$re)){
						$pattern=$re[1];
						$action=$re[2];
						$f[]="SecRule {$ligne["key"]}   \"$pattern\" \"$action,status:403,msg:'Rule: {$ligne["ID"]}:severity %{HIGHEST_SEVERITY}'\"";
					}
					
					
				}
				   
				
				
			}
			
			$f[]="</IfModule>";
			$f[]="";			
			return @implode("\n",$f);
			
		}
		
		
		
		
		public function CheckWorkingDirectory(){
			
			if($this->IsGroupWareFromArtica()){
				$this->CheckDirectoryRights();
				return;
			}
			
			if($this->groupware=="ZARAFA"){
				$this->WORKING_DIRECTORY="/var/www/$this->servername";
				$this->InstallZarafa();
				$this->CheckDirectoryRights();
				return;
			}
			
			if($this->groupware=="ZARAFA_MOBILE"){
				$this->WORKING_DIRECTORY="/var/www/$this->servername";
				$this->InstallZarafaMobile();
				$this->CheckDirectoryRights();
				return;
			}			
			
			if($this->groupware=="Z-PUSH"){
				$this->WORKING_DIRECTORY="/usr/share/z-push";
				shell_exec("/bin/chmod 777 /usr/share/z-push/state");
				return;
			}
			
			
			if($this->groupware=="POWERADMIN"){
				$this->WORKING_DIRECTORY="/usr/share/poweradmin";
				return;
			}
			
			if($this->servername=="_default_"){
				if($GLOBALS["VERBOSE"]){echo "Starting......: Apache \"$this->servername\" DEBUG: is a default website (L.".__LINE__.")\n";} 
				if(trim($this->www_dir)<>null){$this->WORKING_DIRECTORY=trim($this->www_dir);return;}
				$this->WORKING_DIRECTORY="/var/www/html";return;
			}
			
			if($this->CheckWorkingDirectoryLoop()){return;}
			if($this->CheckWorkingDirectoryLVM()){return;}

			
			if(trim($this->www_dir)<>null){
				$this->WORKING_DIRECTORY="$this->www_dir";
				if(strpos($this->WORKING_DIRECTORY,$this->servername)==0){$this->WORKING_DIRECTORY="$this->WORKING_DIRECTORY/$this->servername";}
				$this->CheckDirectoryRights();
				return;
			}
			
			$this->WORKING_DIRECTORY="/var/www/$this->servername";
			$this->CheckDirectoryRights();
			
		}
		
		private function CheckWorkingDirectoryLoop(){
			if($this->UseLoopDisk==0){return false;}
			if(!is_dir($this->LoopMounts)){
				if($this->AS_ROOT){echo "Starting......: Apache \"$this->servername\" $this->LoopMounts no such directory\n";}
				return false;
			}
			
			$this->WORKING_DIRECTORY=$this->LoopMounts;
			$this->CheckDirectoryRights();
		}
		
		
		private function CheckWorkingDirectoryLVM(){
			$sock=new sockets();
			$vgservices=unserialize(base64_decode($sock->GET_INFO("vgservices")));
			if($vgservices["freewebs"]==null){return false;}
			if($this->lvm_size==0){return false;}
			if($this->lvm_vg==null){return false;}
			if($this->AS_ROOT){
				echo "Starting......: Apache \"$this->servername\" stored on an LVM group $this->lvm_vg partition ({$this->lvm_size}MB)\n";
				$lvm=new lvm_root();
				if(!$lvm->CreateLVS($this->lvm_vg,$this->servername,$this->lvm_size,"$this->lvm_vg-$this->servername")){return false;}
				echo "Starting......: Apache \"$this->servername\" $this->lvm_vg/$this->lvm_vg-$this->servername success\n";
			}
			$this->WORKING_DIRECTORY="/automounts/$this->lvm_vg-$this->servername";
			if($this->AS_ROOT){
				exec("/bin/ls -lah $this->WORKING_DIRECTORY 2>&1",$results);
				if($GLOBALS["VERBOSE"]){while (list ($num, $line) = each ($results)){echo "CheckWorkingDirectoryLVM() -> $line\n";}}
				$this->CheckDirectoryRights();
			}
			return true;
			
			
		}
		
		public function IsGroupWareFromArtica(){
			if($this->groupware=="ARTICA_USR"){$this->WORKING_DIRECTORY="/usr/share/artica-postfix/user-backup";return true;}
			if($this->groupware=="ARTICA_ADM"){$this->WORKING_DIRECTORY="/usr/share/artica-postfix";return true;}
			if($this->groupware=="ARTICA_REGISTER_OU"){$this->WORKING_DIRECTORY="/usr/share/artica-postfix";return true;}
			if($this->groupware=="ARTICA_MINIADM"){$this->WORKING_DIRECTORY="/usr/share/artica-postfix";return true;}
			return false;
			
		}
		
		
		private function CheckDirectoryRights(){
			if(!$this->AS_ROOT){return;}
			$this->WORKING_DIRECTORY=str_replace("//","/",$this->WORKING_DIRECTORY);
			echo "Starting......: Apache \"$this->servername\" working directory on $this->WORKING_DIRECTORY\n";
			if(!is_dir($this->WORKING_DIRECTORY)){@mkdir($this->WORKING_DIRECTORY,666,true);}
			if(!is_dir($this->WORKING_DIRECTORY)){
				echo "Starting......: Apache \"$this->servername\" working directory failed !!!\n";
				return;
			}
			
			
			if(!is_dir("$this->WORKING_DIRECTORY/php_logs")){@mkdir("$this->WORKING_DIRECTORY/php_logs",666,true);}
			if(!is_dir("$this->WORKING_DIRECTORY/www_logs")){@mkdir("$this->WORKING_DIRECTORY/www_logs",666,true);}
			
			if(!is_dir("/var/log/apache2")){@mkdir("/var/log/apache2",666,true);}
			
			if(strlen($this->APACHE_RUN_USER)>3){
				shell_exec("/bin/chown -R $this->APACHE_RUN_USER:$this->APACHE_RUN_USER $this->WORKING_DIRECTORY 2>&1 &");
				shell_exec("/bin/chown -R $this->APACHE_RUN_USER:$this->APACHE_RUN_USER $this->WORKING_DIRECTORY/* 2>&1 &");
				shell_exec("/bin/chown -R $this->APACHE_RUN_USER:$this->APACHE_RUN_USER /var/log/apache2 2>&1 &");
			}
			
			
			shell_exec("/bin/chmod -R 755 $this->WORKING_DIRECTORY/* 2>&1 &");
			shell_exec("/bin/chmod 755 $this->WORKING_DIRECTORY");		
			
			
		}
		
		public function CheckDefaultPage(){
		if(!$this->AS_ROOT){return;}
		if($this->IsGroupWareFromArtica()){return;}
		$wel[]="<?php";
		$wel[]="echo \"<H1>It Works!</H1>\";";
		$wel[]="while (list(\$index,\$line)=each(\$_SERVER)){";
		$wel[]="echo \"<H2 style='font-size:16px'>\$index:<span style='color:red'>\$line</span></H2>\";";
		$wel[]="}";
		$wel[]="?>";			
		$index_php=@implode("\n",$wel);	
		if($this->groupware=="cachemgr"){$index_php="<?php echo header('location:cgi-bin/cachemgr.cgi');die();?>";}
			
			if(!is_file("$this->WORKING_DIRECTORY/index.php")){
				@file_put_contents("$this->WORKING_DIRECTORY/index.php",$index_php);
			}			
			
		}
		
		public function ScriptAliases(){
			if($this->groupware=="cachemgr"){	
				@mkdir("$this->WORKING_DIRECTORY/cgi-bin",0755,true);
				shell_exec("/bin/cp -f $this->SQUID_CACHMGR $this->WORKING_DIRECTORY/cgi-bin/cachemgr.cgi");
				$f[]="<Directory \"$this->WORKING_DIRECTORY/cgi-bin\">";
				$f[]="\tOptions -Indexes FollowSymLinks";
				$f[]="\tAllowOverride AuthConfig FileInfo";
				$f[]="\tOrder allow,deny";
				$f[]="\tAllow from all";
				$f[]="</Directory>";
				$f[]="";
				$f[]="ScriptAlias /cgi-bin/cachemgr.cgi $this->WORKING_DIRECTORY/cgi-bin/cachemgr.cgi";
	        	$f[]="<location \"/cgi-bin/cachemgr.cgi\">";
				$f[]="\torder allow,deny";
				$f[]=$this->AllowFrom();
	        	$f[]="</location>"; 
			}			
			return @implode("\n",$f);
			
		}
		
		public function AllowFrom(){
			
			if(!isset($this->Params["LimitByIp"]["enabled"])){$this->Params["LimitByIp"]["enabled"]=0;}
			$fc=array();
			if($GLOBALS["VERBOSE"]){echo "DEBUG::AllowFrom:: LimitByIp:{$this->Params["LimitByIp"]["enabled"]}\n";}
			if($this->LimitByIp==1){
				$hash=$this->LimitByIp_list();
				if($GLOBALS["VERBOSE"]){echo "DEBUG::AllowFrom:: ".count($hash)." items\n";}
				while (list ($num, $ligne) = each ($hash) ){
					if($ligne==null){continue;}
					if(is_array($ligne)){continue;}
					if($GLOBALS["VERBOSE"]){echo "DEBUG::AllowFrom:: allow from $ligne\n";}
					$fc[]="\tallow from $ligne";
				}
			}
			
			
			if($GLOBALS["VERBOSE"]){echo "DEBUG::AllowFrom::  ".count($fc)." items\n";}
			if(count($fc)==0){return "\tallow from all";}else{ return @implode("\n",$fc)."\n";}
			return @implode("\n",$fc)."\n";
		}
		
		public function WebdavHeader(){
				if(!isset($this->Params["WEBDAV"]["ENABLED"])){$this->Params["WEBDAV"]["ENABLED"]=0;}
				if($this->Params["WEBDAV"]["ENABLED"]==0){return "#\tWebDav headers not enabled";}
				$conf[]="\tDavLockDB \"$this->WORKING_DIRECTORY/DavLock\"";
				$conf[]="\tBrowserMatch \"Microsoft Data Access Internet Publishing Provider\" redirect-carefully";
				$conf[]="\tBrowserMatch \"MS FrontPage\" redirect-carefully";
				$conf[]="\tBrowserMatch \"^WebDrive\" redirect-carefully";
				$conf[]="\tBrowserMatch \"^WebDAVFS/1.[0123]\" redirect-carefully";
				$conf[]="\tBrowserMatch \"^gnome-vfs/1.0\" redirect-carefully";
				$conf[]="\tBrowserMatch \"^XML Spy\" redirect-carefully";
				$conf[]="\tBrowserMatch \"^Dreamweaver-WebDAV-SCM1\" redirect-carefully";
				return @implode("\n",$conf);				
			
		}
		
		public function open_basedir(){
				$f[]="/usr/share/php";
				$f[]="/usr/share/php5";
			
			if($this->IsGroupWareFromArtica()){
				$f[]="/usr/share/artica-postfix/ressources";
				$f[]="/usr/share/artica-postfix/framework";
				$f[]="/usr/share/artica-postfix/user-backup";
				$f[]="/usr/share/artica-postfix/user-backup/ressources";
				$f[]="/etc/ssl/certs/mysql-client-download";
			}
			
			if(($this->groupware=="ZARAFA") OR ($this->groupware=="ZARAFA_MOBILE") OR ($this->groupware=="Z-PUSH")){
				$f[]="/usr/share/php/mapi";
				$f[]="/usr/share/php5/mapi";
			}
			
			
			$f[]=$this->WORKING_DIRECTORY;
			include_once("/usr/share/artica-postfix/framework/class.unix.inc");
			$unix=new unix();
			$GLOBALS["MAX_RECUSIVE_DIRS"]=40;
			$GLOBALS["RECURSVIVE_INC"]=0;
			$dirs=$unix->getDirectories($this->WORKING_DIRECTORY);
			while (list ($num, $ligne) = each ($dirs) ){$f[]=$ligne;}
			
			return @implode(":",$f);
		}
		
		public function Aliases(){
			$already=array();
			if($this->groupware=="Z-PUSH"){
				$f[]="Alias /Microsoft-Server-ActiveSync $this->WORKING_DIRECTORY/index.php";
				$already["/Microsoft-Server-ActiveSync"]=true;
			}
			
			$sql="SELECT * FROM freewebs_aliases WHERE servername='{$_GET["servername"]}' ORDER BY alias";
			$q=new mysql();
			$results=$q->QUERY_SQL($sql,'artica_backup');
			while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
				$Alias=trim($ligne["alias"]);
				$directory=trim($ligne["directory"]);
				if($Alias==null){continue;}
				$regex=false;
				if(preg_match("^REGEX:(.+)", $Alias,$re)){$Alias=$re[1];$regex=true;}
				
				if(substr($Alias, 0,1)<>'/'){$Alias="/$Alias";}
				if(substr($directory, 0,1)<>'/'){$directory="/$directory";}
				if(isset($already[$Alias])){continue;}
				$directory=$this->WORKING_DIRECTORY.$directory;
				$already[$Alias]=true;
				if(!$regex){$f[]="Alias $Alias $directory";}else{$f[]="AliasMatch $Alias $directory";}
			}
			
			return @implode("\n",$f);
			
		}
		
		
		public function php_values(){
			
			$includes[]=".";
			$includes[]="/usr/share/php";
			$includes[]="$this->WORKING_DIRECTORY";
			$includes[]="/usr/share/php5";
			$includes[]="/usr/local/share/php";
			$includes[]="/usr/share/php5/PEAR";
			$includes[]="/usr/share/pear";
			$f[]="\tphp_value  include_path \"". @implode(":", $includes)."\"";

			if(($this->groupware=="ZARAFA") OR ($this->groupware=="ZARAFA_MOBILE")){
				$f[]="\tphp_value magic_quotes_gpc \"off\"";
			}
			
			if($this->groupware=="Z-PUSH"){
				$f[]="\tphp_value magic_quotes_gpc \"off\"";
				$f[]="\tphp_value register_globals \"off\"";
				$f[]="\tphp_value magic_quotes_runtime \"off\"";
				$f[]="\tphp_value short_open_tag \"off\"";
			}
			
		
			
			
			if($this->groupware=="EYEOS"){
				$f[]="php_value error_reporting   \"E_ALL & ~E_NOTICE\"";
				$f[]="php_value display_errors   Off ";
				$f[]="php_value max_execution_time   30 ";
				$f[]="php_value max_input_time   60 ";
				$f[]="php_value memory_limit   128M ";
				$f[]="php_value post_max_size   200M ";
				$f[]="php_value upload_max_filesize   100M ";
				$f[]="php_value allow_url_fopen   On ";
				$f[]="php_value safe_mode   Off ";
				$f[]="php_value short_open_tag   On ";
				$f[]="php_value magic_quotes_runtime   Off ";
				$f[]="php_value file_uploads   On ";				
				
			}
			
			return @implode("\n",$f);
		}
		
		public function DirectoryIndex(){
			if($this->groupware=="ARTICA_USR"){return "logon.php";}
			if($this->groupware=="ARTICA_ADM"){return "logon.php";}
			if($this->groupware=="ARTICA_REGISTER_OU"){return "miniadm.register.php";}
			if($this->groupware=="ARTICA_MINIADM"){return "miniadm.php";}		
			return "index.php index.html";
			
		}
		
		public function FilesRestrictions(){
			if(!$this->IsGroupWareFromArtica()){
				if($this->AS_ROOT){echo "Starting......: Apache \"$this->servername\" not an artica groupware interface\n";}
				return null;
			}
			if($this->groupware=="ARTICA_ADM"){
				$array["miniadm.register.php"]="miniadm.register.php";
			}
			if($this->groupware=="ARTICA_USR"){return null;}
			if($this->groupware=="ARTICA_REGISTER_OU"){
				$array=$this->ParseArticaDirectory();
				if($this->AS_ROOT){echo "Starting......: Apache \"$this->servername\" restrictions on ". count($array). " files\n";}
				unset($array["miniadm.register.php"]);
			}
			
			
			if($this->groupware=="ARTICA_MINIADM"){
				$array["miniadm.register.php"]="miniadm.register.php";
				$array["index.php"]="index.php";
				$array["logoff.php"]="logoff.php";
				$array["logon.php"]="logon.php";
				
			}
			
			if(is_array($array)){
				while (list ($num, $file) = each ($array) ){
					$f[]="<Files \"$file\">";
					$f[]="\tOrder allow,deny";
					$f[]="\tDeny from all";
					$f[]="</Files>";
				}
			}
			return @implode("\n",$f);
						
			
		}
		
		
		private function ParseArticaDirectory(){
			if(isset($GLOBALS["ARTICA_FILES_BAN"])){return $GLOBALS["ARTICA_FILES_BAN"];}
			if($this->AS_ROOT){echo "Starting......: Apache \"$this->servername\" parsing /usr/share/artica-postfix directory\n";}
			foreach (glob("/usr/share/artica-postfix/*.php") as $filename) {
				$f=basename($filename);
				$GLOBALS["ARTICA_FILES_BAN"][$f]=$f;
			}
			
			if($this->AS_ROOT){echo "Starting......: Apache \"$this->servername\" ". count($GLOBALS["ARTICA_FILES_BAN"]). " files\n";}
			return $GLOBALS["ARTICA_FILES_BAN"];
		}
		
		
		public function mod_rewrite(){
			if(trim($this->mod_rewrite)==null){return;}
			$f[]="\t<IfModule mod_rewrite.c>";
			$f[]=$this->mod_rewrite;
			$f[]="</IfModule>";
			return @implode("\n",$f);
			
			
		}
		
		public function mod_cache(){
			if(!$this->AS_ROOT){return;}
			if(!isset($this->Params["MOD_CACHE"]["Modenabled"])){$this->Params["MOD_CACHE"]["Modenabled"]=0;}
			if($this->Params["MOD_CACHE"]["Modenabled"]==0){
				if($this->AS_ROOT){echo "Starting......: Apache \"$this->servername\" Cache is not enabled\n";}
				return "#\t\tmode_cache not enabled";
			}
			if(!is_numeric($this->Params["MOD_CACHE"]["CacheDefaultExpire"])){$this->Params["MOD_CACHE"]["CacheDefaultExpire"]=3600;}
			if(!is_numeric($this->Params["MOD_CACHE"]["CacheMinExpire"])){$this->Params["MOD_CACHE"]["CacheMinExpire"]=3600;}
			if(!is_numeric($this->Params["MOD_CACHE"]["CacheMinFileSize"])){$this->Params["MOD_CACHE"]["CacheMinFileSize"]=64;}
			if(!is_numeric($this->Params["MOD_CACHE"]["CacheMaxFileSize"])){$this->Params["MOD_CACHE"]["CacheMaxFileSize"]=64000;}			
			

	
			echo "Starting......: Apache \"$this->servername\" Cache is enabled\n";
			$f[]="\t<IfModule mod_cache.c>";
			$f[]="\t\t<IfModule mod_disk_cache.c>";
			$f[]="\t\t\tCacheEnable disk /";
			$f[]="\t\t\tCacheRoot \"/var/cache/apache/$this->servername\"";
			$f[]="\t\t\tCacheDirLevels 5";
			$f[]="\t\t\tCacheDirLength 4";
			
			if(isset($this->Params["MOD_CACHE"]["CacheDisable"])){
				$array=$this->Params["MOD_CACHE"]["CacheDisable"];
				if(is_array($array)){
					while (list ($num, $ligne) = each ($array) ){
						echo "Starting......: Apache \"$this->servername\" CacheDisable \"$num\"\n";
						if($num==null){continue;}
						$f[]="\t\t\tCacheDisable $num";
					}
				}else{
					echo "Starting......: Apache \"$this->servername\" CacheDisable no row\n";
				}
			}else{
				echo "Starting......: Apache \"$this->servername\" CacheDisable not filled\n"; 
				
			}			
			
			$f[]="\t\t\tCacheMaxFileSize {$this->Params["MOD_CACHE"]["CacheMaxFileSize"]}";
			$f[]="\t\t\tCacheMinFileSize {$this->Params["MOD_CACHE"]["CacheMinFileSize"]}";
			$f[]="\t\t\tCacheIgnoreCacheControl On";
			$f[]="\t\t\tCacheIgnoreNoLastMod On";
			$f[]="\t\t\tCacheIgnoreQueryString Off";
			$f[]="\t\t\tCacheIgnoreHeaders None";
			$f[]="\t\t\tCacheLastModifiedFactor 0.2";
			$f[]="\t\t\tCacheDefaultExpire {$this->Params["MOD_CACHE"]["CacheDefaultExpire"]}";
			//$f[]="\t\t\tCacheMinExpire {$this->Params["MOD_CACHE"]["CacheMinExpire"]}";
			$f[]="\t\t\tCacheStoreNoStore Off";
			$f[]="\t\t\tCacheStorePrivate On";
			//$f[]="\t\t\tCacheStaleOnError On";
			//$f[]="\t\t\tCacheStoreExpired On";
			$f[]="\t\t\tCacheIgnoreQueryString Off";
			$f[]="\t\t\tCacheIgnoreNoLastMod On";
			$f[]="\t\t</IfModule>";
			$f[]="\t</IfModule>";
			
			$f[]="\t<IfModule mod_headers.c>";
			$f[]="\t\t<IfModule mod_expires.c>";
			$f[]="\t\t\tExpiresActive On";
			$f[]="\t\t</IfModule>";
			$f[]="\t\tHeader unset Pragma";
			$f[]="\t\tFileETag None";
			$f[]="\t\tHeader unset ETag";
			$f[]="\t\t<FilesMatch \"\.(ico|pdf|flv|jpg|jpeg|png|gif|swf|mp3|mp4)\$\">";
			$f[]="\t\t\tHeader set Cache-Control \"public, no-transform\"";
			$f[]="\t\t\tHeader set Expires A1209600";
			$f[]="\t\t\tHeader unset Last-Modified";
			$f[]="\t\t</FilesMatch>";
			$f[]="\t</IfModule>";
			$f[]="";
			
			if(!is_dir("/var/cache/apache/$this->servername")){
				@mkdir("/var/cache/apache/$this->servername",755,true);
				$sock=new sockets();
				$apacheusername=$sock->GET_INFO('APACHE_SRC_ACCOUNT');
				echo "Starting......: Apache \"$this->servername\" cache enabled with apache user $apacheusername\n";				
				shell_exec("/bin/chown $apacheusername:$apacheusername /var/cache/apache/$this->servername");
			}
			return @implode("\n", $f);
		}
		
		
		public function WebDav(){
			
			if(!isset($this->Params["WEBDAV"]["ENABLED"])){$this->Params["WEBDAV"]["ENABLED"]=0;}
			if($this->Params["WEBDAV"]["ENABLED"]==0){
				if($this->AS_ROOT){echo "Starting......: Apache \"$this->servername\" WebDAV is not enabled\n";}
				return "#\t\tWebDav not enabled";
			}
			if($GLOBALS["VERBOSE"]){echo "DEBUG::WebDav:: WebDav enabled\n";}
			$users=new usersMenus();
			if(!$users->APACHE_MODE_WEBDAV){
				if($GLOBALS["VERBOSE"]){echo "DEBUG::WebDav:: WebDav module not installed\n";}
				return null;
			}
			
			$ldap=new clladp();
			if($this->AS_ROOT){echo "Starting......: Apache \"$this->servername\" WebDav is enabled\n";}
         	$f[]="DAV On";
          	$f[]="DAVMinTimeout 600";
           	$f[]="AuthType Basic";
           	$f[]="AuthBasicProvider ldap";
           	$f[]="AuthName \"$this->servername Users Only\"";
           	$f[]="AuthLDAPURL ldap://$ldap->ldap_host:$ldap->ldap_port/dc=organizations,$ldap->suffix?uid?sub";
           	$f[]="AuthLDAPBindDN cn=$ldap->ldap_admin,$ldap->suffix";
           	$f[]="AuthLDAPBindPassword $ldap->ldap_password";
           	
          	$f[]="AuthLDAPGroupAttribute memberUid";
          	if(!is_array($this->Params["WEBDAV"]["MEMBERS"])){
          		if($this->AS_ROOT){echo "Starting......: Apache \"$this->servername\" Authentication for ". count($this->Params["WEBDAV"]["MEMBERS"])." items\n";}
          		while (list ($member, $asnull) = each ($this->Params["WEBDAV"]["MEMBERS"]) ){
          			if($member==null){continue;}
          			if(preg_match("#@(.+):([0-9]+)$#",$member,$re)){
          				$groups=new groups($re[2]);
          				$f[]="Require group $groups->dn";
          			}else{
          				$f[]="Require user $member";
          			}
          		}
          		
          	}
			$f[]="Require valid-user";		
			return @implode("\n",$f);
			
		}
		
		private function DeleteLVM(){
			$sock=new sockets();
			$vgservices=unserialize(base64_decode($sock->GET_INFO("vgservices")));
			if($vgservices["freewebs"]==null){return true;}
			if($this->lvm_vg==null){return true;}	
			$lvm=new lvm_root();	
			if(!$lvm->DeleteLVS($this->lvm_vg,$this->servername,"$this->lvm_vg-$this->servername")){return false;}	
			return true;
		}
		
		function delete(){
			if(!$this->DeleteLVM()){return;}
			$sql="SELECT * FROM freeweb WHERE servername='$this->servername'";
			$q=new mysql();
			$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
			$database=$ligne["mysql_database"];
			if(strlen($database)>0){
				$q->DELETE_DATABASE($database);
				if(!$q->ok){
					echo "Unable to delete DB $database\n";
					return;
				}
				
				$sql="DELETE FROM `db` WHERE `Db`='$database'";
				$q->QUERY_SQL($sql,'mysql');
			}
			
			
			$sql="DELETE FROM `awstats` WHERE `website`='$this->servername'";
			$q->QUERY_SQL($sql,'artica_backup');
			if(!$q->ok){echo $q->mysql_error;}				
			
			if($ligne["mysql_username"]<>null){
				if($q->COUNT_DB_FROM_USER($ligne["mysql_username"])==0){
					$q->DELETE_USER_INMYSQL($ligne["mysql_username"]);
				}
				
			}
			
			
			$sql="DELETE FROM freeweb WHERE servername='$this->servername'";
			$q->QUERY_SQL($sql,"artica_backup");
			if(!$q->ok){echo $q->mysql_error;return;}
			
			$sql="DELETE FROM awstats_files WHERE `servername`='$this->servername'";
			$q->QUERY_SQL($sql,"artica_backup");
			
			$sql="DELETE FROM freewebs_proxy WHERE `servername`='$this->servername'";
			$q->QUERY_SQL($sql,"artica_backup");		

			$sql="DELETE FROM freewebs_aliases WHERE `servername`='$this->servername'";
			$q->QUERY_SQL($sql,"artica_backup");				
			
			
			
			$ldap=new clladp();
			$dn="dc=$this->servername,dc=pureftpd,dc=organizations,$ldap->suffix";
			if($ldap->ExistsDN($dn)){$ldap->ldap_delete($dn,true);}
			
			$sock=new sockets();
			$sock->getFrameWork("cmd.php?pure-ftpd-users=yes");	
			$sock->getFrameWork("cmd.php?freeweb-restart=yes");
			
			
		}
		
		private function InstallZarafa(){
			if(!is_dir($this->WORKING_DIRECTORY)){@mkdir($this->WORKING_DIRECTORY,644,true);}
			if(is_dir($this->WORKING_DIRECTORY)){shell_exec("/bin/rm -rf $this->WORKING_DIRECTORY/*");}
			echo "Starting......: Apache $this->servername installing Zarafa\n";
			shell_exec("/bin/rm -f /usr/share/zarafa-webaccess/zarafa-webaccess >/dev/null 2>&1");
			shell_exec("/bin/cp -rf /usr/share/zarafa-webaccess/* $this->WORKING_DIRECTORY/");
			@mkdir($this->WORKING_DIRECTORY."/tmp",755,true);
			@unlink("$this->WORKING_DIRECTORY/config.php");
			$this->InstallZarafaConfigPHP();
			echo "Starting......: Apache $this->servername installing Zarafa Webmail done...\n";
		}
		
		private function InstallZarafaMobile(){
			if(!is_dir($this->WORKING_DIRECTORY)){@mkdir($this->WORKING_DIRECTORY,644,true);}
			if(is_dir($this->WORKING_DIRECTORY)){shell_exec("/bin/rm -rf $this->WORKING_DIRECTORY/*");}
			echo "Starting......: Apache $this->servername installing Zarafa\n";
			shell_exec("/bin/rm -f /usr/share/zarafa-webaccess-mobile/zarafa-webaccess-mobile >/dev/null 2>&1");
			@mkdir("$this->WORKING_DIRECTORY/cache",755,true);
			@mkdir("$this->WORKING_DIRECTORY/templates_c",755,true);			
			@mkdir($this->WORKING_DIRECTORY."/tmp",755,true);
			@mkdir($this->WORKING_DIRECTORY."/config",755,true);
			shell_exec("/bin/cp -rf /usr/share/zarafa-webaccess-mobile/* $this->WORKING_DIRECTORY/");
			echo "Starting......: Apache $this->servername installing Zarafa-webaccess-mobile done...\n";	
			$this->InstallZarafaMobileConfigPHP("$this->WORKING_DIRECTORY");	
			
		}
		
		private function InstallZarafaMobileConfigPHP(){
			$f[]="<?php";
			$f[]="define(\"CONFIG_CHECK\", true);";
			$f[]="define(\"SERVER\", \"file:///var/run/zarafa\");";
			$f[]="define('BASE_PATH', dirname(\$_SERVER['SCRIPT_FILENAME']) . \"/\");";
			$f[]="set_include_path(BASE_PATH.\"include/\" . PATH_SEPARATOR . BASE_PATH . PATH_SEPARATOR . \".\" . PATH_SEPARATOR . \"/usr/share/php/\");";
			$f[]="define(\"COOKIE_EXPIRE\", 60*60*24*365);";
			$f[]="define(\"SMARTY_TEMPLATE_DIR\", \"render/html\");";
			$f[]="define(\"SMARTY_CONFIG_DIR\", \"$this->WORKING_DIRECTORY/config\");";
			$f[]="define(\"SMARTY_CACHE_DIR\",	\"$this->WORKING_DIRECTORY/cache\"); // must be writable";
			$f[]="define(\"SMARTY_COMPILE_DIR\", \"$this->WORKING_DIRECTORY/templates_c\"); // must be writable";
			$f[]="\$config[\"renderer\"]=\"html\";";
			$f[]="define('NOSENDERMSG', _(\"No sender\"));";
			$f[]="define('NOSUBJECTMSG', _(\"No subject\"));";
			$f[]="define('CELLSHOUR', 1);";
			$f[]="define('SCREENWIDTH', 240);";
			$f[]="\$GLOBALS[\"emailsonpage\"]=10;";
			$f[]="\$GLOBALS[\"appointmentsonpage\"]=10;";
			$f[]="\$GLOBALS[\"contactsonpage\"]=1000;";
			$f[]="\$GLOBALS[\"tasksonpage\"]=100;";
			$f[]="ini_set('post_max_size', \"31M\");";
			$f[]="ini_set('upload_max_filesize', \"30M\");";
			$f[]="ini_set('memory_limit', -1);  // -1 = no limit";
			$f[]="ini_set('max_execution_time', 300); // 5 minutes";
			$f[]="define('BLOCK_SIZE', 1048576);";
			$f[]="define('MAX_BODY_SIZE', 100 * 1024);	";
			$f[]="\$debug = false;";
			$f[]="";
			$f[]="	if (\$debug){";
			$f[]="		error_reporting(E_ALL);";
			$f[]="		ini_set(\"display_errors\", true);";
			$f[]="		if (file_exists(\"debug.php\")){";
			$f[]="			include (\"debug.php\");";
			$f[]="		}else{";
			$f[]="			// define empty dump function in case we still use it somewhere";
			$f[]="			function dump(){}";
			$f[]="		}	";
			$f[]="	}else{";
			$f[]="		ini_set(\"display_errors\", false);";
			$f[]="	}";
			$f[]="";
			$f[]="?>";	
			@unlink("$this->WORKING_DIRECTORY/config.php");	
			@file_put_contents("$this->WORKING_DIRECTORY/config.php",@implode("\n",$f));	
		}
		
		
		private function InstallZarafaConfigPHP($Otherdirectory=null){
				$conf[]="<?php";
				$conf[]="ini_set(\"zend.ze1_compatibility_mode\", false);";
				$conf[]="ini_set(\"max_execution_time\", 300); // 5 minutes";
				$conf[]="ini_set(\"display_errors\", false);";
				$conf[]="define(\"CONFIG_CHECK\", TRUE);";
				$conf[]="define(\"DEFAULT_SERVER\",\"file:///var/run/zarafa\");";
				$conf[]="define(\"SSLCERT_FILE\", NULL);";
				$conf[]="define(\"SSLCERT_PASS\", NULL);";
				$conf[]="define(\"LOGINNAME_STRIP_DOMAIN\", false);";
				$conf[]="if (isset(\$_GET[\"external\"]) && preg_match(\"/[a-z][a-z0-9_]+/i\",\$_GET[\"external\"])){define(\"COOKIE_NAME\",\$_GET[\"external\"]);}else{define(\"COOKIE_NAME\",\"ZARAFA_WEBACCESS\");}";
				$conf[]="define(\"THEME_COLOR\", \"default\");\$base_url = dirname(\$_SERVER[\"PHP_SELF\"]);if(substr(\$base_url,-1)!=\"/\") \$base_url .=\"/\";";
				$conf[]="define(\"BASE_URL\", \$base_url);";
				$conf[]="define(\"BASE_PATH\", dirname(\$_SERVER[\"SCRIPT_FILENAME\"]) . \"/\");";
				$conf[]="define(\"MIME_TYPES\", BASE_PATH . \"server/mimetypes.dat\");";
				$conf[]="define(\"TMP_PATH\", \"$this->WORKING_DIRECTORY/tmp\");";
				$conf[]="set_include_path(BASE_PATH. PATH_SEPARATOR . BASE_PATH.\"server/PEAR/\" .  PATH_SEPARATOR . \"/usr/share/php/\");";
				$conf[]="define(\"DIALOG_URL\", \"index.php?load=dialog&\");";
				$conf[]="define(\"DND_FILEUPLOAD_URL\", \"index.php?load=upload_attachment&\");";
				$conf[]="define(\"PATH_PLUGIN_DIR\", \"plugins\");";
				$conf[]="define(\"ENABLE_PLUGINS\", false);";
				$conf[]="define(\"DISABLED_PLUGINS_LIST\", \"\");";
				$conf[]="define(\"DISABLE_FULL_GAB\", false);";
				$conf[]="define(\"DISABLE_FULL_CONTACTLIST_THRESHOLD\", -1);";
				$conf[]="define(\"ENABLE_GAB_ALPHABETBAR\", false);";
				$conf[]="define(\"FREEBUSY_DAYBEFORE_COUNT\", 7);";
				$conf[]="define(\"FREEBUSY_NUMBEROFDAYS_COUNT\", 90);";
				$conf[]="define(\"BLOCK_SIZE\", 1048576);";
				$conf[]="define(\"CLIENT_TIMEOUT\", 5*60*1000);";
				$conf[]="define(\"EXPIRES_TIME\", 60*60*24*7*13);";
				$conf[]="define(\"UPLOADED_ATTACHMENT_MAX_LIFETIME\", 6*60*60);";
				$conf[]="define(\"FCKEDITOR_PATH\",dirname(\$_SERVER[\"SCRIPT_FILENAME\"]).\"/client/widgets/fckeditor\");";
				$conf[]="define(\"FCKEDITOR_JS_PATH\",\"client/widgets/fckeditor\");";
				$conf[]="define(\"FCKEDITOR_SPELLCHECKER_ENABLED\", false);";
				$conf[]="define(\"FCKEDITOR_SPELLCHECKER_PATH\", \"/usr/bin/aspell\");";
				$conf[]="define(\"FCKEDITOR_SPELLCHECKER_LANGUAGE\", FALSE); // set FALSE to use the language chosen by the user, but make sure that these languages are installed with aspell!";
				$conf[]="define(\"LANGUAGE_DIR\", \"server/language/\");";
				$conf[]="if (isset(\$_ENV[\"LANG\"]) && \$_ENV[\"LANG\"]!=\"C\"){";
				$conf[]="	define(\"LANG\", \$_ENV[\"LANG\"]); // This means the server environment language determines the web client language.";
				$conf[]="	}else{";
				$conf[]="define(\"LANG\", \"en_EN\"); // default fallback language";
				$conf[]="	}";
				$conf[]="";
				$conf[]="if (function_exists(\"date_default_timezone_set\")){date_default_timezone_set(\"Europe/London\");}";
				$conf[]="error_reporting(0);";
				$conf[]="if (file_exists(\"debug.php\")){include(\"debug.php\");}else{function dump(){}}";
				$conf[]="?>";
				if($Otherdirectory==null){$Otherdirectory=$this->WORKING_DIRECTORY;}
				@file_put_contents("$Otherdirectory/config.php",@implode("\n",$conf));			
		}
		
		
		function phpmyadmin(){
			echo "Starting......: Apache \"$this->servername\" directory \"$this->WORKING_DIRECTORY\"\n";
			$phpmyadmin_dir="$this->WORKING_DIRECTORY/phpmyadmin";
			if($this->IsGroupWareFromArtica()){return;}
			if($this->groupware=="ZARAFA"){return;}
			if($this->groupware=="ZARAFA_MOBILE"){return;}
			if(!is_numeric($this->useMysql)){$this->useMysql=0;}
			
			
			
			if($this->useMysql==0){
				echo "Starting......: Apache \"$this->servername\" Mysql is not enabled\n";
				if(is_dir("$phpmyadmin_dir")){
					echo "Starting......: Apache \"$this->servername\" removing $phpmyadmin_dir\n";
					shell_exec("/bin/rm -rf $phpmyadmin_dir");
				}
				return;
				
			}
			if(is_file("/usr/share/phpmyadmin/index.php")){
				echo "Starting......: Apache \"$this->servername\" installing phpmyadmin /usr/share/phpmyadmin/index.php no such file\n";
				return;
			}
			
			
			echo "Starting......: Apache \"$this->servername\" Mysql is enabled\n";
			echo "Starting......: Apache checks $phpmyadmin_dir\n";
			
			
			$q=new mysql();
			@mkdir("$phpmyadmin_dir",666,true);
			$blowfish_secret=md5($this->servername);
			shell_exec("/bin/cp -rf /usr/share/phpmyadmin/* $phpmyadmin_dir/ >/dev/null 2>&1");
			$f[]="<?php";
			$f[]="\$cfg['blowfish_secret'] = '$blowfish_secret';";
			$f[]="\$i = 0;";
			$f[]="\$i++;";
			$f[]="";
			$f[]="\$cfg['Servers'][\$i]['auth_type'] = 'cookie';";
			$f[]="\$cfg['Servers'][\$i]['host'] = '$q->mysql_server';";
			$f[]="\$cfg['Servers'][\$i]['port'] = '$q->mysql_port';";
			
			$f[]="\$cfg['Servers'][\$i]['connect_type'] = 'tcp';";
			$f[]="\$cfg['Servers'][\$i]['compress'] = false;";
			$f[]="\$cfg['Servers'][\$i]['extension'] = 'mysql';";
			$f[]="\$cfg['Servers'][\$i]['AllowNoPassword'] = false;";
			$f[]="\$cfg['Servers'][\$i]['bs_garbage_threshold'] = 50;";
			$f[]="\$cfg['Servers'][\$i]['bs_repository_threshold'] = '32M';";
			$f[]="\$cfg['Servers'][\$i]['bs_temp_blob_timeout'] = 600;";
			$f[]="\$cfg['Servers'][\$i]['bs_temp_log_threshold'] = '32M';";
			$f[]="\$cfg['Servers'][\$i]['only_db']='$this->mysql_database';";
			$f[]="\$cfg['Servers'][\$i]['hide_db'] = 'information_schema';";			
			$f[]="\$cfg['UploadDir'] = 'upload';";
			$f[]="\$cfg['SaveDir'] = 'upload';";
			$f[]="\$cfg['DefaultLang'] = 'en-utf-8';";
			$f[]="\$cfg['ServerDefault'] = 1;";
			$f[]="\$cfg['AllowUserDropDatabase']=FALSE;";
			$f[]="\$cfg['ShowPhpInfo']=true;";
			$f[]="\$cfg['DisplayDatabasesList']=FALSE;";
			$f[]="\$cfg['ShowServerInfo']=FALSE;";
			$f[]="\$cfg['ShowCreateDb']=FALSE;";
			$f[]="\$cfg['LeftDisplayServers']=FALSE;";
			$f[]="\$cfg['ShowBlob']=TRUE;";
			$f[]="\$cfg['DefaultTabServer']='main.php';";
			
			$f[]="?>\n";		
			
			$sock=new sockets();
			$apacheusername=$GLOBALS["apacheusername"];
			$varWwwPerms=$sock->GET_INFO("varWwwPerms");
			if($varWwwPerms==null){$varWwwPerms=755;}
			
			
			@file_put_contents("$phpmyadmin_dir/config.inc.php",@implode("\n",$f));
			shell_exec("/bin/chown root:root $phpmyadmin_dir/config.inc.php");
			shell_exec("/bin/chmod -R 755 $phpmyadmin_dir/phpmyadmin");
			shell_exec("/bin/chmod 644 $phpmyadmin_dir/phpmyadmin/config.inc.php");
			shell_exec("/bin/chown -R root:root $phpmyadmin_dir");
			@mkdir("$phpmyadmin_dir/upload");
			if($apacheusername==null){
				shell_exec("/bin/chmod $varWwwPerms /var/www &");
				shell_exec("/bin/chmod -R 777 $phpmyadmin_dir/upload");
			}else{
				shell_exec("/bin/chmod $varWwwPerms /var/www &");
				shell_exec("/bin/chown $apacheusername:$apacheusername $phpmyadmin_dir/upload");
			}
			unset($f);
		}			
	}
?>