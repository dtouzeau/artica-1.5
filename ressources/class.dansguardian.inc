<?php
include_once(dirname(__FILE__)."/class.user.inc");
include_once(dirname(__FILE__)."/class.system.network.inc");
include_once(dirname(__FILE__)."/class.mysql.inc");



class dansguardian{
	var $hostname;
	var $GlobalConf;
	var $Master_array;
	var $Master_rules_index;
	var $last_errors;
	var $_defaults;
	var $template;
	var $enable_clamav;
	var $DansGuardianEnableUserFrontEnd;
	var $DansGuardianEnableUserArticaIP;
	var $UserCategoryBlackList=array(); 
	var $UserCategoryWeightedPhraseList=array();
	var $RulesList=array();
	
	
	function dansguardian(){
		$this->LoadMasterRule();
		$this->LoadRules();
		$this->LoadTemplate();
		$socket=new sockets();
		$this->enable_clamav=$socket->GET_INFO('DansGuardianEnableClamav');
		
		$this->DansGuardianEnableUserFrontEnd=$socket->GET_INFO('DansGuardianEnableUserFrontEnd');
		$this->DansGuardianEnableUserArticaIP=$socket->GET_INFO('DansGuardianEnableUserArticaIP');
		if($this->enable_clamav==null){$this->enable_clamav=1;}
		if($this->DansGuardianEnableUserFrontEnd==null){$this->DansGuardianEnableUserFrontEnd=1;}
		if(trim($this->DansGuardianEnableUserArticaIP)==null){$this->DansGuardianEnableUserArticaIP=$this->GetMyLocalIP();}
		$this->Master_array["DansGuardianEnableUserFrontEnd"]=$this->DansGuardianEnableUserFrontEnd;
		$this->Master_array["enable_clamav"]=$this->enable_clamav;
			
	}
	
	public function ContentScannerMustEnabled(){
		if($this->enable_clamav<>1){
			writelogs("enable_clamav is disabled FAILED",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			return false;
		}	
			$users=new usersMenus();
			if(!$users->CLAMD_INSTALLED){
				writelogs("Clamav is not installed",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				return false;
			}
			
			if($users->MEM_TOTAL_INSTALLEE<716800){
				writelogs("$users->MEM_TOTAL_INSTALLEE<716800",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				return false;
			}
			return true;
			
		
	}
	
	
	public function ContentScanners(){
		if($this->ContentScannerMustEnabled()){
			$conf=$conf ."contentscanner = '/etc/dansguardian/contentscanners/clamdscan.conf'\n";
			$conf=$conf ."contentscannertimeout = 60\n";
			$conf=$conf ."contentscanexceptions = off\n";
			return $conf;
		}
	}
						
	
	function LoadRules(){
		$sql="SELECT RuleID,RuleName,RuleOrder FROM dansguardian_rules ORDER BY RuleID";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$this->RulesList[$ligne["RuleID"]]=$ligne["RuleName"];
			$this->Master_rules_index[$ligne["RuleID"]]=$ligne["RuleName"];
		}
		if(count($this->RulesList)==0){
			$RuleText=addslashes($this->BuildConfig());
			$sql="INSERT INTO dansguardian_rules(RuleName,RuleOrder,RuleText) VALUES('Default Rule',0,'$RuleText');";
			$q->QUERY_SQL($sql,"artica_backup");
			$this->RulesList[0]="Default Rule";
			$this->Master_rules_index[0]=$ligne["Default Rule"];
		}
		
	}
	
	function RulesNumber(){
		$sql="SELECT COUNT(RuleID) as tcount FROM dansguardian_rules";
		$q=new mysql();
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		if($ligne["tcount"]==null){$ligne["tcount"]=0;}
		return $ligne["tcount"];
	}
	
	
		
	function GetMyLocalIP(){
		$net=new networking();
			while (list ($num, $ip) = each ($net->array_TCP) ){
				if(trim($ip)<>null){
					return $ip;
					break;
				}
			}
		
	}
		
	function SaveSettings(){
		$conf=$this->BuildConfig();	
		$rules=new dansguardian_rules($this->hostname,1);
		$this->events('Saving settings...');
		$sock=new sockets();
		$sock->SET_INFO("DansGuardianEnableClamav",$this->Master_array["enable_clamav"]);
		$sock->SET_INFO("DansGuardianEnableUserArticaIP",$this->DansGuardianEnableUserArticaIP);
		$sock->SET_INFO("DansGuardianEnableUserFrontEnd",$this->Master_array["DansGuardianEnableUserFrontEnd"]);
		$sql="UPDATE dansguardian_rules SET RuleText='".addslashes($conf)."' WHERE RuleID=1";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo $q->mysql_error;return false;}
		
		
		$upd["FilterGroupListConf"][0]=$rules->BuildMembersList();		
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?reload-dansguardian=yes");
		return true;

		}
		
		
	function LoadTemplate(){
		$sock=new sockets();
		$this->template=$sock->GET_INFO('DansGuardianHTMLTemplate');
		$this->template=str_replace("\n\n","\n",$this->template);
		
		
	}
	
	function DefinedCategoryWeightedPhraseListLoad(){
		$q=new mysql();
		$sql="SELECT pattern FROM dansguardian_files WHERE filename='weightedphraselist' AND userdefined=1";
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$this->UserCategoryWeightedPhraseList[trim($ligne["pattern"])]=trim($ligne["pattern"]);
		}
		if(!is_array($this->UserCategoryWeightedPhraseList)){return array();}
		return $this->UserCategoryWeightedPhraseList;
	}

	function DefinedCategoryWeightedPhraseListAdd($categoryName){
		$categoryName=strtolower($categoryName);
		$sql="INSERT INTO dansguardian_files(RuleID,filename,pattern,userdefined)
			VALUE(0,'weightedphraselist','$categoryName','1');";
			$q=new mysql();
			$q->QUERY_SQL($sql,"artica_backup");
			$this->UserCategoryBlackList[$categoryName]=$categoryName;		
			$this->UserCategoryWeightedPhraseList[$categoryName]=$categoryName;
			$sock=new sockets();
			$sock->getFrameWork("cmd.php?reload-dansguardian=yes");				
		
	}	
	
	
	function DefinedCategoryBlackListLoad(){
		$q=new mysql();
		$sql="SELECT pattern FROM dansguardian_files WHERE filename='bannedsitelist' AND userdefined=1";
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$this->UserCategoryBlackList[$ligne["pattern"]]=$ligne["pattern"];
		}
		if(!is_array($this->UserCategoryBlackList)){return array();}
		return $this->UserCategoryBlackList;
	}
	
	function DefinedCategoryBlackListAdd($categoryName){
			$categoryName=strtolower($categoryName);
			$sql="INSERT INTO dansguardian_files(RuleID,filename,pattern,userdefined)
			VALUE(0,'bannedsitelist','$categoryName','1');";
			$q=new mysql();
			$q->QUERY_SQL($sql,"artica_backup");
			$this->UserCategoryBlackList[$categoryName]=$categoryName;
			$sock=new sockets();
			$sock->getFrameWork("cmd.php?reload-dansguardian=yes");				
		
		}
	

	
	
	
	function DefinedCategoryWeightedPhraseListDeleteRule($ID){
			$sql="DELETE FROM dansguardian_weightedphraselist WHERE ID=$ID";
			$q=new mysql();
			$q->QUERY_SQL($sql,"artica_backup");
			$sock=new sockets();
			$sock->getFrameWork("cmd.php?reload-dansguardian=yes");					
		}
	
	function DefinedCategoryWeightedPhraseListAddRule($category,$words,$score){
		
		if(strpos($words,",")>0){
			$arraywords=explode(",",$words);
			while (list ($num, $w) = each ($arraywords) ){
				$ws[]="<$w>";
			}
			
			$words=implode(",",$ws);
		}else{
			$words="<$words>";
		}
		
		if(!is_numeric($score)){$score=0;}
		$pattern="$words<$score>";
		$sql="INSERT INTO dansguardian_weightedphraselist (category_name,pattern) VALUES('$category','$pattern');";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?reload-dansguardian=yes");			
		
		}
	
	

	
	function DefinedCategoryBlackListAddRule($category,$website){
		$sql="INSERT INTO dansguardian_categories (category_name,pattern)  VALUES('$category','$website')";		
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?reload-dansguardian=yes");			
		
	}
	
	function DefinedCategoryBlackListDelRule($ID){
		$sql="DELETE FROM dansguardian_categories WHERE ID=$ID";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");	
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?reload-dansguardian=yes");		
		
	}	
	
	
	function delete_rule($ruleIndex){
			writelogs("Delete rule id = $ruleIndex",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		
			if($ruleIndex==1){return null;}
			$sql="DELETE FROM dansguardian_rules WHERE RuleID=$ruleIndex";
			$q=new mysql();
			$q->QUERY_SQL($sql,"artica_backup");
			if(!$q->ok){
				writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			}
			
			$sql="DELETE FROM dansguardian_groups WHERE RuleID=$ruleIndex";
			$q->QUERY_SQL($sql,"artica_backup");
			if(!$q->ok){
				writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			}			
			
			$sock=new sockets();
			$sock->getFrameWork("cmd.php?reload-dansguardian=yes");
		}
		
	
		

		
		
		private function IpAuthNumber(){
			$sql="SELECT COUNT(ID) as tcount FROM dansguardian_ipgroups";
			$q=new mysql();
			$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
			if($ligne["tcount"]==null){$ligne["tcount"]=0;}
			return $ligne["tcount"];
		}
		

		
	private function events($text){
		if(posix_getuid()<>0){return null;}
		$pid=getmypid();
		$logFile=$_GET["LOGFILE"];
		$size=filesize($logFile);
		if($size>1000000){unlink($logFile);}
		$f = @fopen($logFile, 'a');
		if($_GET["debug"]){echo "$pid $text\n";}
		@fwrite($f, "$pid $text\n");
		@fclose($f);	
		}		
		

	function BuildConfig(){
				$this->BuildDefaults();
				include_once(dirname(__FILE__) . '/class.squid.inc');
				$squid=new squidbee();
				if($this->Master_array["minchildren"]==null){$this->Master_array["minchildren"]=8;}
				
				
				$ports=$squid->listen_port;
				if(strpos($ports,':')>0){
							$tb=explode(':',$ports);
							$http_port=$tb[1];
							$filterip=$tb[0];
				}else{
						$http_port=$ports;
						$filterip=null;
						}
				
				if($filterip==null){$filterip='127.0.0.1';}
				if(preg_match('#([0-9]+)#',$ports,$re)){$http_port=$re[1];}
				$this->Master_array["filterport"]=$http_port;
				
				$user_class=new usersMenus();
				$logfileformat=1;
				if($user_class->SARG_INSTALLED){$logfileformat=3;}
				$logfileformat=2;
				$groups=count($this->Master_rules_index)-1;
				if($groups==0){$groups=1;}
				$this->events(__FUNCTION__.":: $groups number");
				writelogs("this->Master_array[\"filterport\"]={$this->Master_array["filterport"]} / SQUID=$http_port",__CLASS__ . '/' . __FUNCTION__,__FILE__);
				$conf=$conf ."reportinglevel = 3\n";
				$conf=$conf ."groupname = 'Default rule'\n";
				$conf=$conf ."languagedir = '/etc/dansguardian/languages'\n";
				$conf=$conf ."language = 'ukenglish'\n";
				$conf=$conf ."loglevel = 3\n";
				$conf=$conf ."logexceptionhits = 2\n";
				$conf=$conf ."logfileformat = $logfileformat\n";
				$conf=$conf ."loglocation = '/var/log/dansguardian/access.log'\n";
				$conf=$conf ."statlocation = '/var/log/dansguardian/stats'\n";
				$conf=$conf ."#\n";
				$conf=$conf ."#routing to squid proxy port : $squid->alt_listen_port but local port is $http_port\n";
				$conf=$conf ."filterip = {$this->Master_array["filterip"]}\n";
				$conf=$conf ."filterport =  {$this->Master_array["filterport"]}\n";
				$conf=$conf ."proxyip = $filterip\n";
				$conf=$conf ."proxyport = $squid->alt_listen_port\n";
				$conf=$conf ."originalip = off\n";
				$conf=$conf ."#\n";
				$conf=$conf ."accessdeniedaddress = 'http://YOURSERVER.YOURDOMAIN/cgi-bin/dansguardian.pl'\n";
				$conf=$conf ."nonstandarddelimiter = on\n";
				$conf=$conf ."usecustombannedimage = on\n";
				$conf=$conf ."custombannedimagefile = '/etc/dansguardian/transparent1x1.gif'\n";
				$conf=$conf ."filtergroups = $groups\n";
				$conf=$conf ."filtergroupslist = '/etc/dansguardian/filtergroupslist'\n";
				$conf=$conf ."bannediplist = '/etc/dansguardian/bannediplist'\n";
				$conf=$conf ."exceptioniplist = /etc/dansguardian/exceptioniplist'\n";
				$conf=$conf ."banneduserlist = '/etc/dansguardian/banneduserlist'\n"; 
				$conf=$conf ."exceptionuserlist = '/etc/dansguardian/exceptionuserlist'\n"; 
				$conf=$conf ."exceptionphraselist = '/etc/dansguardian/lists/exceptionphraselist'\n";
				$conf=$conf ."exceptionsitelist = '/etc/dansguardian/lists/exceptionsitelist'\n";
				$conf=$conf ."showweightedfound = on\n";
				$conf=$conf ."weightedphrasemode = 2\n";
				$conf=$conf ."urlcachenumber = {$this->Master_array["urlcachenumber"]}\n";
				$conf=$conf ."urlcacheage = {$this->Master_array["urlcacheage"]}\n";
				$conf=$conf ."scancleancache = {$this->Master_array["scancleancache"]}\n";
				$conf=$conf ."phrasefiltermode = {$this->Master_array["phrasefiltermode"]}\n";
				$conf=$conf ."preservecase = {$this->Master_array["preservecase"]}\n";
				$conf=$conf ."hexdecodecontent = {$this->Master_array["hexdecodecontent"]}\n";
				$conf=$conf ."forcequicksearch = {$this->Master_array["forcequicksearch"]}\n";
				$conf=$conf ."reverseaddresslookups = {$this->Master_array["reverseaddresslookups"]}\n";
				$conf=$conf ."reverseclientiplookups = {$this->Master_array["reverseclientiplookups"]}\n";
				$conf=$conf ."logclienthostnames = {$this->Master_array["logclienthostnames"]}\n";
				$conf=$conf ."createlistcachefiles = {$this->Master_array["createlistcachefiles"]}\n";
				$conf=$conf ."maxuploadsize = {$this->Master_array["maxuploadsize"]}\n";
				$conf=$conf ."maxcontentfiltersize = {$this->Master_array["maxcontentfiltersize"]}\n";
				$conf=$conf ."maxcontentramcachescansize = {$this->Master_array["maxcontentramcachescansize"]}\n";
				$conf=$conf ."maxcontentfilecachescansize = {$this->Master_array["maxcontentfilecachescansize"]}\n";
				$conf=$conf ."filecachedir = '/tmp'\n";
				$conf=$conf ."deletedownloadedtempfiles = {$this->Master_array["deletedownloadedtempfiles"]}\n";
				$conf=$conf ."initialtrickledelay = {$this->Master_array["initialtrickledelay"]}\n";
				$conf=$conf ."trickledelay = {$this->Master_array["trickledelay"]}\n";
				$conf=$conf ."#downloadmanager = '/etc/dansguardian/downloadmanagers/fancy.conf'\n";
				$conf=$conf ."downloadmanager = '/etc/dansguardian/downloadmanagers/default.conf'\n";
				$conf=$conf ."#downloadmanager = '/etc/dansguardian/downloadmanagers/trickle.conf'\n";
				
				$conf=$conf ."\n#--------- AV/ICAP\n";
				$users=new usersMenus();
				$conf=$conf.$this->ContentScanners();
				
				$conf=$conf ."\n";
				$conf=$conf ."recheckreplacedurls = off\n";
				$conf=$conf ."forwardedfor = on\n";
				$conf=$conf ."usexforwardedfor = on\n";
				$conf=$conf ."logconnectionhandlingerrors = on\n";
				$conf=$conf ."logchildprocesshandling = off\n";
				
				
				$conf=$conf ."\n#auth plugins\n";
				if($this->IpAuthNumber()>0){
					$conf=$conf ."authplugin='/etc/dansguardian/authplugins/ip.conf'";
					$conf=$conf ."\n";
					
				}
				//
				
				$conf=$conf ."\n#--------- DAEMON\n";
				$conf=$conf ."maxchildren = {$this->Master_array["maxchildren"]}\n";
				$conf=$conf ."minchildren = {$this->Master_array["minchildren"]}\n";
				$conf=$conf ."minsparechildren = {$this->Master_array["minsparechildren"]}\n";
				$conf=$conf ."preforkchildren = {$this->Master_array["preforkchildren"]}\n";
				$conf=$conf ."maxsparechildren = {$this->Master_array["maxsparechildren"]}\n";
				$conf=$conf ."maxagechildren = {$this->Master_array["maxagechildren"]}\n";
				$conf=$conf ."maxips = {$this->Master_array["maxips"]}\n\n";
				$conf=$conf ."ipcfilename = '/tmp/.dguardianipc'\n";
				$conf=$conf ."urlipcfilename = '/tmp/.dguardianurlipc'\n";
				$conf=$conf ."ipipcfilename = '/tmp/.dguardianipipc'\n";
				$conf=$conf ."pidfilename = '/var/run/dansguardian.pid'\n";
				$conf=$conf ."nodaemon = off\n";
				$conf=$conf ."nologger = off\n";
				$conf=$conf ."logadblocks = off\n";
				$conf=$conf ."loguseragent = off\n";
				$conf=$conf ."daemonuser = 'squid'\n";
				$conf=$conf ."daemongroup = 'squid'\n";
				$conf=$conf ."softrestart = off\n";
				$conf=$conf ."forkscanlength = 32768\n";
		

				return $conf;
			}
			
			private function LoadMasterRule(){
				$sql="SELECT RuleText FROM dansguardian_rules WHERE RuleID=1";
				$q=new mysql();
				$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
				$this->Main_rule=$ligne["RuleText"];
				$this->ParseConfig();
				$this->BuildDefaults();
			}
			
			private function ParseConfig(){
				
				$tb=explode("\n",$this->Main_rule);
				writelogs("Rule $this->ruleindex store ". strlen($this->Main_rule). " bytes length (line ".__LINE__.")",__CLASS__.'/'.__FUNCTION__,__FILE__);
				writelogs("This config has ".count($tb)." lines :".__LINE__,__CLASS__.'/'.__FUNCTION__,__FILE__);
				while (list ($num, $val) = each ($tb) ){
					if(preg_match('#(.+?)[=\s]+(.+)#',$val,$re)){
						$re[2]=str_replace("'","",$re[2]);
						$this->Master_array[trim($re[1])]=trim($re[2]);
					}
				
				}
		
				
				
			}
	

	


	private function BuildDefaults(){
				$this->_defaults=array("reportinglevel"=>"3",
							"languagedir"=>"/etc/dansguardian/languages",
							"language"=>"ukenglish",
							"loglevel"=>"2",
							"logexceptionhits"=>"2",
							"logfileformat"=>"1",
							"loglocation"=>"/var/log/dansguardian/access.log",
							"statlocation"=>"/var/log/dansguardian/stats.log",
							"filterip =",
							"filterport"=>"8080",
							"proxyip"=>"127.0.0.1",
							"proxyport"=>"3128",
							"accessdeniedaddress"=>"http://YOURSERVER.YOURDOMAIN/cgi-bin/dansguardian.pl",
							"nonstandarddelimiter"=>"on",
							"usecustombannedimage"=>"on",
							"custombannedimagefile"=>"/etc/dansguardian/transparent1x1.gif",
							"filtergroups"=>"1",
							"filtergroupslist"=>"/etc/dansguardian/filtergroupslist",
							"bannediplist"=>"/etc/dansguardian/bannediplist",
							"exceptioniplist"=>"/etc/dansguardian/exceptioniplist",
							"showweightedfound"=>"on",
							"weightedphrasemode"=>"2",
							"urlcachenumber"=>"1000",
							"urlcacheage"=>"900",
							"scancleancache"=>"on",
							"phrasefiltermode"=>"2",
							"preservecase"=>"0",
							"hexdecodecontent"=>"off",
							"forcequicksearch"=>"off",
							"reverseaddresslookups"=>"off",
							"reverseclientiplookups"=>"off",
							"logclienthostnames"=>"off",
							"createlistcachefiles"=>"on",
							"maxuploadsize"=>"-1",
							"maxcontentfiltersize"=>"256",
							"maxcontentramcachescansize"=>"2000",
							"maxcontentfilecachescansize"=>"20000",
							"filecachedir"=>"/tmp",
							"deletedownloadedtempfiles"=>"on",
							"initialtrickledelay"=>"20",
							"trickledelay"=>"10",
							"downloadmanager"=>"/etc/dansguardian/downloadmanagers/fancy.conf",
							"downloadmanager"=>"/etc/dansguardian/downloadmanagers/default.conf",
							"contentscannertimeout"=>"60",
							"contentscanexceptions"=>"off",
							"recheckreplacedurls"=>"off",
							"forwardedfor"=>"on",
							"usexforwardedfor"=>"on",
							"logconnectionhandlingerrors"=>"on",
							"logchildprocesshandling"=>"off",
							"maxchildren"=>"120",
							"minchildren"=>"8",
							"minsparechildren"=>"4",
							"preforkchildren"=>"6",
							"maxsparechildren"=>"32",
							"maxagechildren"=>"500",
							"maxips"=>"0",
							"ipcfilename"=>"/tmp/.dguardianipc",
							"urlipcfilename"=>"/tmp/.dguardianurlipc",
							"ipipcfilename"=>"/tmp/.dguardianipipc",
							"pidfilename"=>"/var/run/dansguardian.pid",
							"nodaemon"=>"off",
							"nologger"=>"off",
							"logadblocks"=>"off",
							"loguseragent"=>"off",
							"daemonuser"=>"squid",
							"daemongroup"=>"squid",
							"softrestart"=>"off");
							
				reset($this->_defaults);
				while (list ($num, $val) = each ($this->_defaults) ){
					if(trim($this->Master_array[$num])==null){
						//writelogs("Building default values:: [$num]=$val",__CLASS__ . '/' . __FUNCTION__,__FILE__);
						$this->Master_array[$num]=$val;}
					}							
	
					}
					
		function AddRule($rulename){
			$u=new user(null);
			$rulename=$u->stripAccents($rulename);
			$rulename=str_replace(";",".",$rulename);
			$q=new mysql();
			$RuleText=addslashes($this->BuildConfig());
			$sql="INSERT INTO dansguardian_rules(RuleName,RuleOrder,RuleText) VALUES('$rulename',0,'$RuleText');";
			$q->QUERY_SQL($sql,"artica_backup");	
		}
		
		
}



class dansguardian_rules{
	var $error;
	var $hostname;
	var $ruleindex;
	var $array_blacksites=array(
									"porn"=>"{adult}",
									"sex/lingerie"=>"{sex_lingerie}",
									"mixed_adult"=>"{mixed_adult}",
									"sexual_education"=>"{sexual_education}",
									"dating"=>"{dating}",
	
									
									"agressive"=>"{agressif}",
									"violence"=>"{violence}",

									
									"automobile/bikes"=>"{automobile-bikes}",
									"automobile/boats"=>"{automobile-boats}",
									"automobile/cars"=>"{automobile-cars}",
									"automobile/planes"=>"{automobile-planes}",
									"automobile/carpool"=>"{automobile-carpool}",

									
									"publicite"=>"{publicite}",
									
									
									
									"cleaning"=>"{cleaning}",
									"dangerous_material"=>"{dangerous_material}",
									"downloads"=>"{www_downloads}",
									"chat"=>"{www_chat}",
									"passwords"=>"{cat_passwords}",
									
									
									"drugs"=>"{drogue}",
									"dynamic"=>"{www_dynamic}",
									
									"financial"=>"{financial}",
									"finance/banking"=>"{finance-banking}",
									"finance/insurance"=>"{finance-insurance}",
									"finance/moneylending"=>"{finance-moneylending}",
									"finance/realestate"=>"{finance-realestate}",
									"finance/other"=>"{finance-other}",

									"forums"=>"{forums}",
									//"forum"=>"{forums} (2)",
									"socialnet"=>"{socialnet}",
									"jobsearch"=>"{jobsearch}",
									"humanitarian"=>"{www_humanitarian_associations}",
	
									
									"gamble"=>"{gambling}",

									"hacking"=>"{hacking}",
									"warez"=>"{warez}",
	
									"hobby/cooking"=>"{hobby-cooking}",
									"hobby/arts"=>"{hobby-arts}",
									
									"isp"=>"{www_isp}",
									"webmail"=>"{webmail}",
									
									"liste_bu"=>"{liste_bu}",
									"mobile-phone"=>"{mobile-phone}",
									"marketingware"=>"{marketingware}",
									
									"webradio"=>"{webradio}",
									"audio-video"=>"{audio-video}",
									"webtv"=>"{webtv}",
									"music"=>"{music}",
									"movies"=>"{movies}",
									
									
									
									"blog"=>"{blog}",
									"news"=>"{www_news}",
									"books"=>"{www_books}",
									"phishing"=>"{phishing}",
									
									"redirector"=>"{redirector}",
									"strict_redirector"=>"{strict_redirector}",
									"strong_redirector"=>"{strong_redirector}",
									"reaffected"=>"{reaffected}",
									
									"tricheur"=>"{tricheur}",
									
									
									"webphone"=>"{webphone}",
									"weapons"=>"{weapons}",
									
									"games"=>"{games}",
									"hobby/pets"=>"{hobby-pets}",
									
									"filehosting"=>"{filehosting}",
									"pictures"=>"{ww_pictures}",
									"imagehosting"=>"{www_imagehosting}",
									"religion"=>"{religion}",
									"sect"=>"{sect}",
									"genealogy"=>"{www_genealogy}",
									
									"ringtones"=>"{ringtones}",
									"recreation/wellness"=>"{recreation_wellness}",
									"recreation/travel"=>"{recreation_travel}",
									"recreation/nightout"=>"{recreation_nightout}",
									"governments"=>"{www_government}",
									"recreation/schools"=>"{www_schools}",
									"housing/reale_state_office"=>"{www_reale_state_office}",
									
									"recreation/sports"=>"{recreation_sports}",
									"recreation/humor"=>"{recreation_humor}",
									"children"=>"{www_children}",
									"shopping"=>"{www_shopping}",
									"gifts"=>"{www_gifts}",
									"clothing"=>"{www_clothing}",
									"models"=>"{models}",
									"celebrity"=>"{www_celebrity}",
									
									
									"science/chemistry"=>"{science_chemistry}",
									"astrology"=>"{astrology}",
									"science/astronomy"=>"{science_astronomy}",
									"science/weather"=>"{science_weather}",
									"nature"=>"{www_nature}",
									"browsersplugins"=>"{www_browsersplugins}",
									"webplugins"=>"{webplugins}",
									"maps"=>"{www_maps}",
									"science/computing"=>"{cat_computing}",
									"remote-control"=>"{cat_remote-control}",
									"hospitals"=>"{cat_hospitals}",
									"medical"=>"{cat_medical}",
									"sslsites"=>"{cat_sslsites}",
									"updatesites"=>"{updatesites}",
									"internal"=>"{www_internal}",
									
									"searchengines"=>"{searchengines}",
									"translators"=>"{www_translators}",
									"spyware"=>"{www_spyware}",
									"malware"=>"{www_malware}",
									"tracker"=>"{www_tracker}",
									
									
									);
									
		  var $array_pics=array(
		  	"reaffected"=>"20-reaffect.png",
		  	"mixed_adult"=>"sex-icon.png",
		  	"sex/lingerie"=>"sex-icon.png",
		  	"porn"=>"sex-icon.png",
		 	"adult"=>"sex-icon.png",
		  	"sexual_education"=>"kiss-icon.png",
		  	"music"=>"fire-guitar-icon.png",
		  	"agressif"=>"icon_fight.png",
		  	"agressive"=>"icon_fight.png",
		  	"violence"=>"icon_fight.png",
		  	"socialnet"=>"tree-groups.gif",
		  	"forum"=>"icon_forum.png",
		  	"forums"=>"icon_forum.png",
		  	"jobsearch"=>"icon_forum.png",
		  	"tracker"=>"icon_eye.png",
		  	"gambling"=>"icon_poker.png",
		  	"gamble"=>"icon_poker.png",
		  	"automobile/bikes"=>"icon-moto.gif",
		  	"publicite"=>"icon-ads.png",
		  	"adv"=>"icon-ads.png",
			"financial"=>"icon-bank.png",
			"finance/banking"=>"icon-bank.png",
			"finance/insurance"=>"icon-bank.png",
			"finance/moneylending"=>"icon-bank.png",
			"finance/other"=>"icon-bank.png",	
			"dangerous_material"=>"icon-bomb.png",
			"drogue"=>"icon-drugs.png",
			"drugs"=>"icon-drugs.png",
		  	"hobby/cooking"=>"icon-food.png",
		  	"astrology"=>"icon-astrologie.png",	
		  	"webmail"=>"icon-webmail.png",	
		  	"mobile-phone"=>"icon-mobile-phone.png", 
		  	"hacking"=>"icon-hacks.png",
		  	"phishing"=>"icon-fish.png",
		  	"automobile/boats"=>"icon-boat.png",
		  	"automobile/cars"=>"icon-car.png",
		  	"automobile/planes"=>"icon-plane.png",
		  	"cleaning"=>"icon-clean.png",
		  	"webtv"=>"icon-tv.png",
		  	"recreation/sports"=>"icon-sport.png",
		  	"recreation/travel"=>"icon-travel.png",
		  	"searchengines"=>"icon-search.png",
		  	"games"=>"icon-games.png",
		  	"hobby/games"=>"icon-games.png",
		 	"hobby/pets"=>"icon-pets.png",
		  	"weapons"=>"icon-armes.png",
		  	"sect"=>"icon-sect.png",
		  	"science/computing"=>"base.gif",
		  	"passwords"=>"lock.gif",
		  	"downloads"=>"icon-download-20.png",
		 	"updatesites"=>"20-cd-scan.png",
		  	"science/astronomy"=>"astronomy-20.png",
		  	"science/weather"=>"20-climat.png",
		  	"recreation/humor"=>"20-humour.png",
		  	"ringtones"=>"20-ringtone.png",
		  	"mobile-phone"=>"20-mobile-phone.png",
		  	"tricheur"=>"20-exams.png",
		  	"clothing"=>"20-clothing.png",
		  	"shopping"=>"20-shopping.png",
		  	"filehosting"=>"20-storage.png",
		  	"religion"=>"20-christians.png",
		  	"recreation/wellness"=>"20-heathly.png",
		  	"science/chemistry"=>"20-chemistery.png",
		  	"spyware"=>"20-setup.png",
		  	"recreation/governments"=>"20-towns.png",
		  	"redirector"=>"20-redirect.png",
		  	"strict_redirector"=>"20-redirect.png",
		  	"strong_redirector"=>"20-redirect-plus.png",
		  	"recreation/schools"=>"20-schools.png",
		  	"webradio"=>"20-webradio.png",
		  	"radio"=>"20-webradio.png",
		  	"movies"=>"20-cinema.png",
		  	"dating"=>"20-dating.png",
		  	"webplugins"=>"20-webplugins.png",
		  	"audio-video"=>"20-audio-video.png",
		  	"blog"=>"20-blog.png",
		  	"models"=>"20-model-agency.png",
		  	"liste_bu"=>"flags/fr.png",
		  	"news"=>"20-news.png",
		  	"marketingware"=>"20-marketing.png",
		  	"warez"=>"20-warez.png",
		  	"finance/realestate"=>"icon-house-20.gif",
			"chat"=>"icon-chat-20.png",
			"hospitals"=>"hospital.gif",
		  	"medical"=>"hospital.gif",
		  	"sslsites"=>"icon-ssl-20.png",
		  	"translators"=>"icon-translation.png",
		  	"humanitarian"=>"humanitarian-20.png",
		  	"hobby/arts"=>"art-20.png",
		  	"remote-control"=>"20-win-nic.png",
			"recreation/nightout"=>"icon_nightclub.png",	
			"imagehosting"=>"picasa_icon.png",
		  	"pictures"=>"cat_pictures.png",
		  	"malware"=>"icon_malwares.png",
		  	"celebrity"=>"celebrity-20.png",
		  	"books"=>"books_20.png",
		  	"internal"=>"network-1-20.gif",
		  	"maps"=>"20-maps.png",
		  	"press"=>"20-news.png",
		  	"nature"=>"nature-20.png",
		  	"gifts"=>"gifts-20.png",
		  	"browsersplugins"=>"20-webplugins.png",
		  	"genealogy"=>"genealogy-20.png",
		  	"children"=>"baby-20.png",
		  	"automobile/carpool"=>"carpool-20.png",
		  	"nature"=>"nature-20.png"
		  	
		  	);
									
									
			var $array_banned_phrases_list=array("gambling"=>"{gambling}",
												"intolerance"=>"{intolerance}",
												"safelabel"=>"{safelabel}",
												"googlesearches"=>"{googlesearches}",
												"rta"=>"{rta}",
												"pornography"=>"{pornography}",
												"illegaldrugs"=>"{illegaldrugs}");
												
	         
			
							
									
			var $array_weighted_phrases_lists=array("sport"=>"{sport}",
													"news"=>"{news}",
													"personals"=>"{personals}",
													"warezhacking"=>"{warezhacking}",
													"music"=>"{music}",
													"weapons"=>"{weapons}",
													"chat"=>"{chat}",
													"malware"=>"{malware}",
													"peer2peer"=>"{peer2peer}",
													"games"=>"{games}",
													"gambling"=>"{gambling}",
													"secretsocieties"=>"{secretsocieties}",
													"intolerance"=>"{intolerance}",
													"badwords"=>"{badwords}",
													"translation"=>"{translation}",
													"goodphrases"=>"{goodphrases}",
													"gore"=>"{gore}",
													"idtheft"=>"{idtheft}",
													"violence"=>"{violence}",
													"domainsforsale"=>"{domainsforsale}",
													"conspiracy"=>"{conspiracy}",
													"legaldrugs"=>"{legaldrugs}",
													"upstreamfilter"=>"{upstreamfilter}",
													"nudism"=>"{nudism}",
													"pornography"=>"{pornography}",
													"drugadvocacy"=>"{drugadvocacy}",
													"forums"=>"{forums}",
													"webmail"=>"{webmail}",
													"illegaldrugs"=>"{illegaldrugs}",
													"proxies"=>"{proxies}",
													"travel"=>"{travel}");
													
													
	var $array_bannedextensionlist=array(	".ade"=>"Microsoft Access project extension",
											".adp"=>"Microsoft Access project",
											".asx"=>"Windows Media Audio / Video",
											".bas"=>"Microsoft Visual Basic class module",
											".bat"=>"Batch file",
											".cab"=>"Windows setup file",
											".chm"=>"Compiled HTML Help file",
											".cmd"=>"Microsoft Windows NT Command script",
											".com"=>"Microsoft MS-DOS program",
											".cpl"=>"Control Panel extension",
											".crt"=>"Security certificate ",
											".dll"=>"Windows system file",
											".exe"=>"Program",
											".hlp"=>"Help file",
											".ini"=>"Windows system file",
											".hta"=>"HTML program",
											".inf"=>"Setup Information",
											".ins"=>"Internet Naming Service",
											".isp"=>"Internet Communication settings",
											".lnk"=>"Windows Shortcut",
											".mda"=>"Microsoft Access add-in program ",
											".mdb"=>"Microsoft Access program",
											".mde"=>"Microsoft Access MDE database",
											".mdt"=>"Microsoft Access workgroup information ",
											".mdw"=>"Microsoft Access workgroup information ",
											".mdz"=>"Microsoft Access wizard program ",
											".msc"=>"Microsoft Common Console document",
											".msi"=>"Microsoft Windows Installer package",
											".msp"=>"Microsoft Windows Installer patch",
											".mst"=>"Microsoft Visual Test source files",
											".pcd"=>"Photo CD image, Microsoft Visual compiled script",
											".pif"=>"Shortcut to MS-DOS program",
											".prf"=>"Microsoft Outlook profile settings",
											".reg"=>"Windows registry entries",
											".scf"=>"Windows Explorer command",
											".scr"=>"Screen saver",
											".sct"=>"Windows Script Component",
											".sh "=>"Shell script",
											".shs"=>"Shell Scrap object",
											".shb"=>"Shell Scrap object",
											".sys"=>"Windows system file",
											".url"=>"Internet shortcut",
											".vb "=>"VBScript file",
											".vbe"=>"VBScript Encoded script file",
											".vbs"=>"VBScript file",
											".vxd"=>"Windows system file",
											".wsc"=>"Windows Script Component",
											".wsf"=>"Windows Script file",
											".wsh"=>"Windows Script Host Settings file",
											".otf"=>"Font file - can be used to instant reboot 2k and xp",
											".ops"=>"Office XP settings",
											".doc"=>"Word document",
											".xls"=>"Excel document",
											".pps"=>"MicrosoftPower Point presentation",
											".gz "=>"Gziped file",
											".tar"=>"Tape ARchive file",
											".zip"=>"Windows compressed file",
											".tgz"=>"Unix compressed file",
											".bz2"=>"Unix compressed file",
											".cdr"=>"Mac disk image",
											".dmg"=>"Mac disk image",
											".smi"=>"Mac self mounting disk image",
											".sit"=>"Mac compressed file",
											".sea"=>"Mac compressed file, self extracting",
											".bin"=>"Mac binary compressed file",
											".hqx"=>"Mac binhex encoded file",
											".rar"=>"Similar to zip",
											".mp3"=>"Music file",
											".mpeg"=>"Movie file",
											".mpg"=>"Movie file",
											".avi"=>"Movie file",
											".asf"=>"this can also exploit a security hole allowing virus infection",
											".iso"=>"CD ISO image",
											".ogg"=>"Music file",
											".wmf"=>"Movie file",
											".bin"=>"CD ISO image",
											".cue"=>"CD ISO image");
			
	       var $array_default_mimetype=array("audio/mpeg",
											"audio/x-mpeg",
											"audio/x-pn-realaudio",
											"audio/x-wav",
											"video/mpeg",
											"video/x-mpeg2",
											"video/acorn-replay",
											"video/quicktime",
											"video/x-msvideo",
											"video/msvideo",
											"application/gzip",
											"application/x-gzip",
											"application/zip",
											"application/compress",
											"application/x-compress",
											"application/java-vm");										
													
																						
	var $bannedregexpurllist_array=array();
	var $exceptionsitelist_array=array();
	var $bannedextensionlist_array=array();
	var $bannedMimetypelist_array=array();
	var $exceptionfilesitelist=array();
	var $Main_rule;
	var $Main_array=array();
	
	
	function dansguardian_rules($hostname=null,$RuleID=0){
		ksort($this->array_weighted_phrases_lists);
		//ksort($this->array_blacksites);
		ksort($this->array_banned_phrases_list);
		if($RuleID>0){
				$this->ruleindex=$RuleID;
				$this->LoadMainRule();
				$this->LoadOtherRules($this->ruleindex);
				$this->ParseMainConfig();
				$this->BuildDefaults();
				writelogs("$RuleID) Parsing end...",__CLASS__.'/'.__FUNCTION__,__FILE__);
				}
			
		
		
	}
	
	
	function hash_RulesList(){
		$q=new mysql();
		$sql="SELECT RuleID,RuleName FROM dansguardian_rules ORDER BY RuleName";
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			if($ligne["RuleID"]<2){continue;}
			$hash[$ligne["RuleID"]]=$ligne["RuleName"];
		}
		
		return $hash;
	}
		
	private function LoadMainRule(){
		$q=new mysql();
		$sql="SELECT RuleText FROM dansguardian_rules WHERE RuleID=$this->ruleindex";
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		$this->Main_rule=$ligne["RuleText"];
		writelogs("Rule $this->ruleindex store ". strlen($this->Main_rule). " bytes length (line ".__LINE__.")",__CLASS__.'/'.__FUNCTION__,__FILE__);
		if(trim($this->Main_rule)==null){
			$this->Main_rule=$this->BuildMainRule();
		    $this->SaveRuleID();
		}
		
	}
	
	private function SaveRuleID(){
		$this->Main_rule=$this->BuildMainRule();
		$sql="UPDATE dansguardian_rules SET RuleText='". addslashes($this->Main_rule) ."' WHERE RuleID=$this->ruleindex";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){
			writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__);
			return false;
		}
		$sock=new sockets();
		$this->RestartFilters();
		
		return true;
	}
	
	
	
	function ParseMainConfig(){
		$tb=explode("\n",$this->Main_rule);
			writelogs("Rule $this->ruleindex store ". strlen($this->Main_rule). " bytes length (line ".__LINE__.")",__CLASS__.'/'.__FUNCTION__,__FILE__);
			writelogs("This config has ".count($tb)." lines :".__LINE__,__CLASS__.'/'.__FUNCTION__,__FILE__);
			while (list ($num, $val) = each ($tb) ){
				if(preg_match('#(.+?)[=\s]+(.+)#',$val,$re)){
					$re[2]=str_replace("'","",$re[2]);
					$this->Main_array[trim($re[1])]=trim($re[2]);
				}
				
			}
		
		
	}
	
	function AddCategory($category,$ruleindex){
		$sql="INSERT INTO dansguardian_files (RuleID,filename,infos,pattern,enabled) VALUES('$ruleindex','bannedsitelist','$info','$category','1');";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");		
		if(!$q->ok){writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__);}	
		$sock=new sockets();
		$this->RestartFilters();
	}
	
	function AddPersonalCategory($category,$ruleindex){
		$sql="INSERT INTO dansguardian_personal_categories(RuleID,category,category_type) 
		VALUES('$ruleindex','$category','enabled');";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");		
		if(!$q->ok){writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__);return;}
		$sock=new sockets();
		$this->RestartFilters();				
	}	
	
	function DelPersonalCategory($index){
		writelogs("Delete content id $index",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$sql="DELETE FROM dansguardian_personal_categories WHERE ID=$index";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__);return;}	
		$sock=new sockets();
		$this->RestartFilters();				
		
	}
	
	function AddPersonalWebSiteOnCategory($WebsiteToAdd,$category,$ruleindex){
	$sql="INSERT INTO dansguardian_personal_categories(RuleID,category,category_type,pattern) 
		VALUES('$ruleindex','$category','data','$WebsiteToAdd');";
		$q=new mysql_squid_builder();
		$q->QUERY_SQL($sql,"artica_backup");	
			
		if(!$q->ok){
			echo $q->mysql_error;
			writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__);
			return;
		}
		$sock=new sockets();
		$this->RestartFilters();

		
		if(preg_match("#www\.(.+?)$#i",$WebsiteToAdd,$re)){$WebsiteToAdd=$re[1];}
		$md5=md5($www.$category);
		$uuid=base64_decode($sock->getFrameWork("cmd.php?system-unique-id=yes"));
		$sql="INSERT INTO dansguardian_community_categories (zmd5,zDate,category,pattern,uuid) VALUES('$md5',NOW(),'$category','$www','$uuid')";
		$q=new mysql_squid_builder();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__);return;}
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?export-community-categories=yes");	
			
		
		
	}
	
	function DeletePersonalCategoryWebSite($ID){
		writelogs("Delete content id $ID",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$sql="DELETE FROM dansguardian_personal_categories WHERE ID=$ID";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__);return;}
		$sock=new sockets();
		$this->RestartFilters();
	}	
	
	
	function AddIpToFilter($pattern,$ruleindex){
		$sql="INSERT INTO dansguardian_ipgroups (pattern,RuleID) VALUES('$pattern','$ruleindex')";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");		
		if(!$q->ok){writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__);return false;}
		$this->RestartFilters();
		return true;	
		}
	
	function DelIpToFilter($ID){
		if(!is_numeric($ID)){
			writelogs("Not a numeric $ID",__CLASS__.'/'.__FUNCTION__,__FILE__);
			return;}
		writelogs("Delete content id $ID",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$sql="DELETE FROM dansguardian_ipgroups WHERE ID=$ID";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__);return;}
		$this->RestartFilters();	
	}
	
	
	public function RestartFilters(){
		$sock=new sockets();
		$enable_cicap=$sock->GET_INFO('CicapEnabled');
		$enable_squidguard=$sock->GET_INFO('squidGuardEnabled');
		$DansGuardianEnabled=$sock->GET_INFO('DansGuardianEnabled');
		$EnableUfdbGuard=$sock->GET_INFO('EnableUfdbGuard');
		$users=new usersMenus();
		if($users->C_ICAP_INSTALLED){
			if($enable_squidguard==1){
				$sock->getFrameWork("cmd.php?cicap-restart=yes");
				return;
			}
		}
		
		if($users->DANSGUARDIAN_INSTALLED){
			if($DansGuardianEnabled==1){
				$sock->getFrameWork("cmd.php?reload-dansguardian=yes");
				return;
			}
		}
		
		if($users->SQUIDGUARD_INSTALLED){
			if($enable_squidguard==1){
				$sock->getFrameWork("cmd.php?reload-dansguardian=yes");
				return;
			}
		}
		
		if($users->APP_UFDBGUARD_INSTALLED){
			if($EnableUfdbGuard==1){
				$sock->getFrameWork("cmd.php?reload-ufdbguard=yes");
				return;
			}
		}
		
		
	}
	

	

	
	
	function AddCategory_phrase_weight($category,$ruleindex){
		$sql="INSERT INTO dansguardian_files (RuleID,filename,infos,pattern,enabled) VALUES('$ruleindex','weightedphraselist','$info','$category','1');";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");		
		if(!$q->ok){writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__);return;}
		$this->RestartFilters();	
		}

	
	function AddCategory_phrase_banned($category,$ruleindex){
		$sql="INSERT INTO dansguardian_files (RuleID,filename,infos,pattern,enabled) VALUES('$ruleindex','bannedphraselist','$info','$category','1');";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");		
		if(!$q->ok){writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__);return;}
		$this->RestartFilters();		
		}	
	
	
	function DelCategory($ruleindex,$cat_index){
		writelogs("Delete content id $index",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$sql="DELETE FROM dansguardian_files WHERE ID='$cat_index'";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__);return;}
		$this->RestartFilters();	
		}
	
	function DelCategory_phrase_weight($ID){
		writelogs("Delete content id $ID",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$sql="DELETE FROM dansguardian_files WHERE ID='$ID'";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__);}		
		}
		
	function DelCategory_phrase_banned($ID){
		writelogs("Delete content id $ID",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$sql="DELETE FROM dansguardian_files WHERE ID='$ID'";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__);}
		}	

	

	
	
	function LoadIpGroup($ruleindex){
		$ldap=new clladp();
		$dn="cn=$ruleindex,cn=dansguardian,cn=artica,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){return array();}
		$res=@ldap_read($ldap->ldap_connection,$dn,"(objectClass=DansGuardianRules)",array('DansGuardianFilterIPGroup'));
		
			if($res){
						$hash=ldap_get_entries($ldap->ldap_connection,$res);
						
						for($i=0;$i<$hash[0][strtolower('DansGuardianFilterIPGroup')]["count"];$i++){
							if(trim($hash[0][strtolower('DansGuardianFilterIPGroup')][$i])<>null){
							$results[$i]=$hash[0][strtolower('DansGuardianFilterIPGroup')][$i];}
						}
				}else{writelogs("no DansGuardianFilterIPGroup $dn",__CLASS__.'/'.__FUNCTION__,__FILE__);return array();}
				
		return $results;		
	}
	
	
	
	function LoadOtherRules($ruleindex){
		$sql="SELECT COUNT(ID) AS Tcount FROM dansguardian_files WHERE filename='bannedextensionlist' AND RuleID=$ruleindex";
		$q=new mysql();
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		writelogs("RuleID $ruleindex bannedextensionlist={$ligne["Tcount"]} rows",__CLASS__.'/'.__FUNCTION__,__FILE__);
		
		if($ligne["Tcount"]==0){
			$this->Default_Build_BannedExtensionList($ruleindex);	
		}
		
		$sql="SELECT COUNT(ID) AS Tcount FROM dansguardian_files WHERE filename='bannedmimetypelist' AND RuleID=$ruleindex";
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		writelogs("RuleID $ruleindex bannedmimetypelist={$ligne["Tcount"]} rows",__CLASS__.'/'.__FUNCTION__,__FILE__);
		if($ligne["Tcount"]==0){
			$this->Default_Build_BannedMimeTypeList($ruleindex);	
		}
		
		$sql="SELECT COUNT(ID) AS Tcount FROM dansguardian_files WHERE filename='bannedregexpurllist' AND RuleID=$ruleindex";
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		writelogs("RuleID $ruleindex bannedmimetypelist={$ligne["Tcount"]} rows",__CLASS__.'/'.__FUNCTION__,__FILE__);
		if($ligne["Tcount"]==0){
			$this->Default_Build_bannedregexpurllist($ruleindex);	
		}		
		
	}

	
	function Add_bannedregexpurllist($ruleindex,$pattern,$info,$enabled){
		$info=addslashes($info);
		$pattern=addslashes($pattern);
		$sql="INSERT INTO dansguardian_files (RuleID,filename,infos,pattern,enabled) VALUES('$ruleindex','bannedregexpurllist','$info','$pattern','$enabled');";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");		
		if(!$q->ok){
			echo $q->mysql_error."\n";
			writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__);
			writelogs("$sql",__CLASS__.'/'.__FUNCTION__,__FILE__);
			return;
		}
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?reload-dansguardian=yes");			
		}
		
	function Add_exceptionsitelist($ruleindex,$www){
		//exceptionsitelist
		$www=str_replace("http://",'',$www);
		$www=str_replace("https://",'',$www);
		$www=str_replace("ftp://",'',$www);
		$www=str_replace("ftps://",'',$www);
		$www=str_replace("www.",'',$www);
		$slashpos=strpos($www,'/');
		if($slashpos>0){$www=substr($www,0,$slashpos);}
		
		$q=new mysql();
		$sql="SELECT ID FROM dansguardian_files WHERE pattern='$www' AND filename='exceptionsitelist' AND RuleID='$ruleindex'";
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
		if($ligne["ID"]>0){
			return true;
		}
		
		$sql="INSERT INTO dansguardian_files (RuleID,filename,infos,pattern,enabled) VALUES('$ruleindex','exceptionsitelist','$www','$www','1');";
		
		$q->QUERY_SQL($sql,"artica_backup");		
		if(!$q->ok){writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__);}
		$sock=new sockets();
		$this->RestartFilters();	
		
		
		}
		
	function Add_bannedextensionlist($ruleindex,$extension,$info,$enabled){
		if(substr($extension,0,1)<>'.'){$extension='.'.$extension;}
		$extension=str_replace("*",'',$extension);
		writelogs("add new banned extension <info>$info</info><pattern>$extension</pattern><enabled>$enabled</enabled>",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$sql="INSERT INTO dansguardian_files (RuleID,filename,infos,pattern,enabled) VALUES('$ruleindex','bannedextensionlist','$info','$extension','$enabled');";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");		
		if(!$q->ok){writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__);}
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?reload-dansguardian=yes");			
		}
		
	function Add_ExeptionFileSiteList($ruleindex,$pattern,$info,$enabled){
		$pattern=str_replace("http://",'',$pattern);
		$pattern=str_replace("https://",'',$pattern);
		$pattern=str_replace("ftp://",'',$pattern);
		$pattern=str_replace("ftps://",'',$pattern);
		$pattern=str_replace("www.",'',$pattern);
		
		$q=new mysql();
		$sql="SELECT ID FROM dansguardian_files WHERE pattern='$www' AND filename='exceptionfilesitelist' AND RuleID='$ruleindex'";
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
		if($ligne["ID"]>0){
			return true;
		}		
		
		$sql="INSERT INTO dansguardian_files (RuleID,filename,infos,pattern,enabled) VALUES('$ruleindex','exceptionfilesitelist','$info','$pattern','$enabled');";
		$q->QUERY_SQL($sql,"artica_backup");		
		if(!$q->ok){writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__);}	
		$sock=new sockets();
		$this->RestartFilters();				
		}	

	function Add_exceptioniplist($pattern,$info,$uid=null,$enabled=1){
	
		$q=new mysql();
		$sql="SELECT ID FROM dansguardian_files WHERE pattern='$pattern' AND filename='exceptioniplist' AND RuleID='1'";
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
		if($ligne["ID"]>0){
			return true;
		}		
		
		$sql="INSERT INTO dansguardian_files (RuleID,filename,infos,pattern,uid,enabled) VALUES('1','exceptioniplist','$info','$pattern','$uid','$enabled');";
		$q->QUERY_SQL($sql,"artica_backup");		
		if(!$q->ok){
			writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$this->error=$q->mysql_error;
			return false;
		}	
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?squid-rebuild=yes");
		$this->RestartFilters();
		return true;				
		}
	function Add_bannediplist($pattern,$info,$uid,$enabled=1){
		if(!preg_match("#[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+#",$pattern)){
			$this->error="$pattern: Wrong value";
			return false;
		}
		$q=new mysql();
		$sql="SELECT ID FROM dansguardian_files WHERE pattern='$pattern' AND filename='bannediplist' AND RuleID='1'";
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
		if($ligne["ID"]>0){return true;}		
		
		$sql="INSERT INTO dansguardian_files (RuleID,filename,infos,pattern,enabled,uid) VALUES('1','bannediplist','$info','$pattern','$enabled','$uid');";
		$q->QUERY_SQL($sql,"artica_backup");		
		if(!$q->ok){
			writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$this->error=$q->mysql_error;
			return false;
		}	
		$sock=new sockets();
		$this->RestartFilters();
		$sock->getFrameWork("cmd.php?squid-rebuild=yes");
		return true;				
		}	
		
	function Del_bannediplist($ruleindex,$index){
		writelogs("Delete content id $index",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$sql="DELETE FROM dansguardian_files WHERE ID='$index'";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__);}	
		if(!$q->ok){
			writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$this->error=$q->mysql_error;
			return false;
		}			
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?squid-rebuild=yes");
		$this->RestartFilters();	
		}					

	function Del_exceptioniplist($ruleindex,$index){
		writelogs("Delete content id $index",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$sql="DELETE FROM dansguardian_files WHERE ID='$index'";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__);}	
		if(!$q->ok){
			writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__);
			$this->error=$q->mysql_error;
			return false;
		}			
		$sock=new sockets();
		$sock->getFrameWork("cmd.php?squid-rebuild=yes");
		$this->RestartFilters();
		}		
		
		
		
	function Add_bannedmimetype($ruleindex,$pattern,$info,$enabled){
		$sql="INSERT INTO dansguardian_files (RuleID,filename,infos,pattern,enabled) VALUES('$ruleindex','bannedmimetypelist','$info','$pattern','$enabled');";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");		
		if(!$q->ok){writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__);}	
		$sock=new sockets();
		$this->RestartFilters();				
		}		
		
	function DelBannedExtensionList($ruleindex,$index){
		writelogs("Delete content id $index",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$sql="DELETE FROM dansguardian_files WHERE ID='$index'";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__);}	
		$sock=new sockets();
		$this->RestartFilters();			
	}
	
	function DelBannedMimeTypeList($ruleindex,$index){
		writelogs("Delete content id $index",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$sql="DELETE FROM dansguardian_files WHERE ID='$index'";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__);}	
		$sock=new sockets();
		$this->RestartFilters();						
	}	
		
	function DelExceptionsitelist($ID){
		writelogs("Delete content id $ID",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$sql="DELETE FROM dansguardian_files WHERE ID='$ID'";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__);}	
		$sock=new sockets();
		$this->RestartFilters();					
	}

	function DelExeptionFileSiteList($ID){
		writelogs("Delete content id $ID",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$sql="DELETE FROM dansguardian_files WHERE ID='$ID'";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__);}	
	}

	function Edit_bannedregexpurllist_enabled($ID,$enabled=1){
		writelogs("enable=$enabled rule id $ID",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$sql="UPDATE `dansguardian_files` SET `enabled`=\"$enabled\" WHERE ID=$ID";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){writelogs("$q->mysql_error\n\n$sql\n\n",__CLASS__.'/'.__FUNCTION__,__FILE__);}
		$sock=new sockets();
		$this->RestartFilters();				
		}
		
	function Edit_bannedregexpurllist($ID,$pattern,$info,$enabled=null){
		$pattern=stripslashes($pattern);
		$pattern=str_replace("\n","",$pattern);
		$pattern=str_replace("\r","",$pattern);
		$info=addslashes($info);
		$sql="UPDATE `dansguardian_files` SET `pattern`=\"$pattern\",infos='$info' WHERE ID=$ID";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){writelogs("$q->mysql_error\n\n$sql\n\n",__CLASS__.'/'.__FUNCTION__,__FILE__);}	
		$sock=new sockets();
		$this->RestartFilters();			
		}
		
function Edit_bannedextensionlist($ID,$enabled=null){
		
		$sql="UPDATE dansguardian_files SET enabled='$enabled' WHERE ID=$ID";
		writelogs($sql,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){writelogs($q->mysql_error,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
		$sock=new sockets();
		$this->RestartFilters();							
		}
		
function Edit_ExeptionFileSiteList($ID,$enabled=null){
		$sql="UPDATE dansguardian_files SET enabled='$enabled' WHERE ID=$ID";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		$sock=new sockets();
		$this->RestartFilters();					
		}		

function Edit_bannedMimeTypelist($ID,$enabled=null){
		$sql="UPDATE dansguardian_files SET enabled='$enabled' WHERE ID=$ID";
		writelogs("$sql",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){
			writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		}
		$sock=new sockets();
		$this->RestartFilters();			
		}		
		
function DelBannedregexpurllist($ruleindex,$listindex){
		writelogs("Delete rule number $listindex in group $ruleindex Enabled={$rules["enabled"]}",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$sql="DELETE FROM dansguardian_files WHERE ID='$listindex'";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__);}	
		$sock=new sockets();
		$this->RestartFilters();					
		
		}
		
		
	private function Default_Build_BannedExtensionList($ruleindex){
		$arr=$this->array_bannedextensionlist;
		while (list ($num, $val) = each ($arr) ){
			$this->Add_bannedextensionlist($ruleindex,$num,$val,1);
			
		}
		
	}
	
	private function Default_Build_BannedMimeTypeList($ruleindex){
		$arr=$this->array_default_mimetype;
		while (list ($num, $val) = each ($arr) ){
			$this->Add_bannedmimetype($ruleindex,$val,$val,1);
		}
		
	}	
		
	private function Default_Build_bannedregexpurllist($ruleindex){
		 $this->Add_bannedregexpurllist($ruleindex,
		 '(^|[-\?+=&/_])(big|cyber|hard|huge|mega|small|soft|super|tiny)?(anal|babe|bharath|boob|breast|busen|busty|clit|cum|cunt|dick|fetish|fuck|hooter|lez|lust|naked|nude|oral|orgy|porn|porno|pupper|pussy|rotten|sex|shit|smutpump|teen|tit|topp?les|xxx)s?([-\?+=&/_]|$)',
		 'Pornography, Modelling and Adult Sites These have been taken from www.squidguard.org',
		 0);
		 
		 
		 $this->Add_bannedregexpurllist(
		 $ruleindex,
		 '(^|[\?+=&/])(girls|babes|toples)([\?+=&/]|$)',
		 'Pornography, Modelling and Adult Sites These have been taken from www.squidguard.org',0);
		 
		 $this->Add_bannedregexpurllist(
		 $ruleindex,
		 '(^|[-\?+=&/_])(big|cyber|hard|huge|mega|small|soft|super|tiny|bare|naked|nude|anal|oral|topp?les|sex|phone){1,}.*(anal|babe|bharath|boob|breast|busen|busty|clit|cum|cunt|dick|fetish|fuck|girl|hooter|lez|lust|naked|nude|oral|orgy|penis|porn|porno|pupper|pussy|rotten|sex|shit|smutpump|teen|tit|topp?les|xxx)s?([-\?+=&/_]|$)
(^|[-\?+=&/_])(anal|babe|bharath|boob|breast|busen|busty|clit|cum|cunt|dick|fetish|fuck|girl|hooter|lez|lust|naked|nude|oral|orgy|penis|porn|porno|pupper|pussy|rotten|sex|shit|smutpump|teen|tit|topp?les|xxx){1,}.*(big|cyber|hard|huge|mega|small|soft|super|tiny|bare|naked|nude|anal|oral|topp?les|sex){1,}([-\?+=&/_]|$)',
		'Pornography, Modelling and Adult Sites Philip Pearce 9/11/2004'
		,1);
		 
		 		 
		$this->Add_bannedregexpurllist(
				 $ruleindex,
				 '(^|[-\?+=&/_])(anal|babe|bharath|boob|breast|busen|busty|clit|cum|cunt|dick|fetish|fuck|girl|hooter|lez|lust|naked|nude|oral|orgy|penis|porn|porno|pupper|pussy|rotten|sex|shit|smutpump|teen|tit|topp?les|xxx){1,}.*(big|cyber|hard|huge|mega|small|soft|super|tiny|bare|naked|nude|anal|oral|topp?les|sex){1,}([-\?+=&/_]|$)',
				'Pornography, Modelling and Adult Sites Philip Pearce 9/11/2004'
				,1);			
				
		$this->Add_bannedregexpurllist(
				 $ruleindex,
				 '(adultsight|adultsite|adultsonly|adultweb|blowjob|bondage|centerfold|cumshot|cyberlust|cybercore|hardcore|masturbat)',
				 'HardCore phrases'
				,1);
		
		$this->Add_bannedregexpurllist(
				 $ruleindex,
				 '(bangbros|pussylip|playmate|pornstar|sexdream|showgirl|softcore|striptease)',
				 'HardCore phrases'
				,1);
		
		$this->Add_bannedregexpurllist(
				 $ruleindex,
				 '(incest|obscene|pedophil|pedofil)',
				 'SoftCore phrases - more likely to overblock - possibly on news sites'
				,0);
		
		$this->Add_bannedregexpurllist(
				 $ruleindex,
				 '(male|m[ae]n|boy|girl|beaut|agen[ct]|glam){1,}.*(model|talent)',
				 'Photo Modeling - supplied by David Burkholder'
				,0);	
				
		$this->Add_bannedregexpurllist(
				 $ruleindex,
				 '(sex|fuck|boob|cunt|fetish|tits|anal|hooter|asses|shemale|submission|porn|xxx|busty|knockers|slut|nude|naked|pussy){1,}.*(\.jpg|\.wmv|\.mpg|\.mpeg|\.gif|\.mov)',
				 'Block explicit media files (images and video)'
				,1);	
				
				
		$this->Add_bannedregexpurllist(
				 $ruleindex,
				 '(girls|babes|bikini|model){1,}.*(\.jpg|\.wmv|\.mpg|\.mpeg|\.gif|\.mov)',
				 'Block explicit media files (images and video)'
				,1);

		$this->Add_bannedregexpurllist(
				 $ruleindex,
				 '(naturism|naturist|nude|nudist|nudism|nekkid|nakt|naakt)',
				 'Block Naturism and Nudist sites'
				,0);	

		$this->Add_bannedregexpurllist(
				 $ruleindex,
				 '(^|[\?+=&/])(.*\.google\..*/.*\?.*safe=off)([\?+=&/]|$)',
				 'Block unfiltered options on various search engines'
				,0);								

		$this->Add_bannedregexpurllist(
				 $ruleindex,
				 '(^|[\?+=&/])(.*\.alltheweb.com/customize\?.*copt_offensive=off)([\?+=&/]|$)',
				 'Block unfiltered options on various search engines'
				,0);				
				
		$this->Add_bannedregexpurllist(
				 $ruleindex,
				 '(yahoo.com\/image\/)',
				 'Block images and video on altavista, alltheweb, yahoo etc - as they are anonomised'
				,0);	
				
				
		$this->Add_bannedregexpurllist(
				 $ruleindex,
				 '(yimg.com\/image\/)',
				 'Block images and video on altavista, alltheweb, yahoo etc - as they are anonomised'
				,0);

		$this->Add_bannedregexpurllist(
				 $ruleindex,
				 '(altavista.com\/image\/)',
				 'Block images and video on altavista, alltheweb, yahoo etc - as they are anonomised'
				,0);

		$this->Add_bannedregexpurllist(
				 $ruleindex,
				 '(picsearch.com\/is)',
				 'Block images and video on altavista, alltheweb, yahoo etc - as they are anonomised'
				,0);

		$this->Add_bannedregexpurllist(
				 $ruleindex,
				 '(images.google){1,}.*(\.jpg|\.wmv|\.mpg|\.mpeg|\.gif|\.mov)',
				 'Block images and video on google'
				,0);	

		$this->Add_bannedregexpurllist(
				 $ruleindex,
				 '(google.com\/video)',
				 'Block images and video on google (videos)'
				,0);


		$this->Add_bannedregexpurllist(
				 $ruleindex,
				 '(google.com\/ThumbnailServer)',
				 'Block images and video on google (Thumbnail)'
				,0);
				

		$this->Add_bannedregexpurllist(
				 $ruleindex,
				 '(google.com\/videoplay)',
				 'Block images and video on google (playing video)'
				,0);

		$this->Add_bannedregexpurllist(
				 $ruleindex,
				 '(cecid.php|nph-webpr|nph-pro|/dmirror|cgiproxy|phpwebproxy|__proxy_url|proxy.php)',
				 'Block Cgiproxy, Poxy, PHProxy and other Web-based proxies'
				,1);

		$this->Add_bannedregexpurllist(
				 $ruleindex,
				 '(anonymizer|proxify|megaproxy)',
				 'Block websites containing proxy lists'
				,1);	

		$this->Add_bannedregexpurllist(
				 $ruleindex,
				 '(casino|bet(ting|s)|lott(ery|o)|gam(e[rs]|ing|bl(e|ing))|sweepstake|poker)',
				 'Gambling - supplied by David Burkholder'
				,0);	

		$this->Add_bannedregexpurllist(
				 $ruleindex,
				 '(bowling|badminton|box(e[dr]|ing)|skat(e[rs]|ing)|hockey|soccer|nascar|wrest|rugby|tennis|sports|cheerlead|rodeo|cricket|badminton|stadium|derby)',
				 'Sport - supplied by David Burkholder'
				,0);

		$this->Add_bannedregexpurllist(
				 $ruleindex,
				 '(speed(st|wa|y)|corvette|rac[eiy]|wrest|harley|motorcycle|nascar)',
				 'Racing - supplied by David Burkholder (1)'
				,0);

		$this->Add_bannedregexpurllist(
				 $ruleindex,
				 '(news(watch|pap|cast)|herald|sentinel|courier|gazet|tribune|chronicle|daily|ning)news)',
				 'News sites - supplied by David Burkholder (2)'
				,0);	

		$this->Add_bannedregexpurllist(
				 $ruleindex,
				 '(meet|hook|mailord|latin|(asi|mexic|dominic|russi|kore|colombi|balk)an|brazil|filip|french|chinese|ukrain|thai|tour|foreign|date){1,}.*(dar?[lt]ing|(sing|coup)le|m[ae]n|girl|boy|guy|mat(e|ing)|l[ou]ve?|partner|meet)',
				 'Dating Sites - supplied by David Burkholder (1)'
				,0);	

		$this->Add_bannedregexpurllist(
				 $ruleindex,
				 '(marr(y|i[ae])|roman(ce|tic)|fiance|bachelo|dating|affair|personals)',
				 'Dating Sites - supplied by David Burkholder (2)'
				,0);

		$this->Add_bannedregexpurllist(
				 $ruleindex,
				 '(adlog.php|cnt.cgi|count.cgi|count.dat|count.jsp|count.pl|count.php|counter.cgi|counter.js|counter.pl|countlink.cgi|fpcount.exe|logitpro.cgi|rcounter.dll|track.pl|w_counter.js)',
				 'block web counters'
				,0);

		$this->Add_bannedregexpurllist(
				 $ruleindex,
				 '(free|phone|mobile){1,}.*(love|music|movie|dvd|video|stuff|site|arcade|wallpaper|mp3)',
				 'Free stuff - supplied by David Burkholder (1)'
				,0);							

		$this->Add_bannedregexpurllist(
				 $ruleindex,
				 '((ring|real)tone)',
				 'Free stuff - supplied by David Burkholder (2)'
				,0);
				
		$this->Add_bannedregexpurllist(
				 $ruleindex,
				 '(rock|pop|jazz|rap|punk){1,}.*(cult|roll|geek|drum|music|history|band)',
				 'Music - supplied by David Burkholder'
				,0);						
				
		$this->Add_bannedregexpurllist(
				 $ruleindex,
				 '(Variants/AVTest)',
				 'his is part of the URL for the dansguardian.org sample virus archive.You probably don\'t want to go there unintentionally'
				,1);				

				
						
		
		}
		
	function BuildBranch(){}
	
	private function BuildDefaults(){
		if($this->Main_array["groupmode"]==null){$this->Main_array["groupmode"]=1;}
		if($this->Main_array["naughtynesslimit"]==null){$this->Main_array["naughtynesslimit"]=50;}
		if($this->Main_array["categorydisplaythreshold"]==null){$this->Main_array["categorydisplaythreshold"]=0;}
		if($this->Main_array["embeddedurlweight"]==null){$this->Main_array["embeddedurlweight"]=0;}
		if($this->Main_array["deepurlanalysis"]==null){$this->Main_array["deepurlanalysis"]="off";}
		
		
	}
		
		
	function BuildMainRule(){
		$folder_name="group_{$this->ruleindex}";
		$this->BuildDefaults();
		$dansguardian=new dansguardian();

		$conf=$conf."groupmode = {$this->Main_array["groupmode"]}\n";
		$conf=$conf."groupname = {$this->Main_array["groupname"]}\n";
		$conf=$conf."bannedphraselist = '/etc/dansguardian/$folder_name/bannedphraselist'\n";
		$conf=$conf."weightedphraselist = '/etc/dansguardian/$folder_name/weightedphraselist'\n";
		$conf=$conf."exceptionphraselist = '/etc/dansguardian/$folder_name/exceptionphraselist'\n";
		$conf=$conf."bannedsitelist = '/etc/dansguardian/$folder_name/bannedsitelist'\n";
		$conf=$conf."greysitelist = '/etc/dansguardian/$folder_name/greysitelist'\n";
		$conf=$conf."exceptionsitelist = '/etc/dansguardian/$folder_name/exceptionsitelist'\n";
		$conf=$conf."bannedurllist = '/etc/dansguardian/$folder_name/bannedurllist'\n";
		$conf=$conf."greyurllist = '/etc/dansguardian/$folder_name/greyurllist'\n";
		$conf=$conf."exceptionurllist = '/etc/dansguardian/$folder_name/exceptionurllist'\n";
		$conf=$conf."exceptionregexpurllist = '/etc/dansguardian/$folder_name/exceptionregexpurllist'\n";
		$conf=$conf."bannedregexpurllist = '/etc/dansguardian/$folder_name/bannedregexpurllist'\n";
		$conf=$conf."bannedregexpheaderlist = '/etc/dansguardian/$folder_name/bannedregexpheaderlist'\n";
		$conf=$conf."picsfile = '/etc/dansguardian/$folder_name/pics'\n";
		$conf=$conf."contentregexplist = '/etc/dansguardian/$folder_name/contentregexplist'\n";
		$conf=$conf."urlregexplist = '/etc/dansguardian/$folder_name/urlregexplist'\n";	
		
		$conf=$conf."#blockdownloads = off\n";	
		$conf=$conf."exceptionextensionlist = '/etc/dansguardian/exceptionextensionlist'\n";	
		$conf=$conf."exceptionmimetypelist = '/etc/dansguardian/exceptionmimetypelist'\n";
		
		$conf=$conf."exceptionfilesitelist = '/etc/dansguardian/$folder_name/exceptionfilesitelist'\n";
		$conf=$conf."exceptionfileurllist = '/etc/dansguardian/$folder_name/exceptionfileurllist'\n";
		
		$conf=$conf."bannedextensionlist = '/etc/dansguardian/$folder_name/bannedextensionlist'\n";
		$conf=$conf."bannedmimetypelist = '/etc/dansguardian/$folder_name/bannedmimetypelist'\n";	

		$conf=$conf."#logsitelist = '/etc/dansguardian/logsitelist'\n";
		$conf=$conf."#logurllist = '/etc/dansguardian/logurllist'\n";
		$conf=$conf."#logregexpurllist = '/etc/dansguardian/logregexpurllist'\n";	
		$conf=$conf."headerregexplist = '/etc/dansguardian/headerregexplist'\n";	
		$conf=$conf."#bannedregexpheaderlist = '/etc/dansguardian/bannedregexpheaderlist'\n";	

		$conf=$conf."naughtynesslimit = {$this->Main_array["naughtynesslimit"]}\n";
		$conf=$conf."categorydisplaythreshold = {$this->Main_array["categorydisplaythreshold"]}\n";
		$conf=$conf."embeddedurlweight = {$this->Main_array["embeddedurlweight"]}\n";
		$conf=$conf."enablepics = off\n";
		$conf=$conf."bypass = 0\n";	
		$conf=$conf."bypasskey = ''\n";

		$conf=$conf."\n#AV/ICAP\n";
		$conf=$conf.$dansguardian->ContentScanners();
		$conf=$conf."infectionbypass = 0\n";
		$conf=$conf."infectionbypasskey = ''\n";		
		$conf=$conf."contentscannertimeout = 60\n";
		$conf=$conf."disablecontentscan = off\n";
		$conf=$conf."\n";
		$conf=$conf."deepurlanalysis = {$this->Main_array["deepurlanalysis"]}\n";
		$conf=$conf."reportinglevel = 3\n";
		$conf=$conf."#accessdeniedaddress = 'http://YOURSERVER.YOURDOMAIN/cgi-bin/dansguardian.pl'\n";
		$conf=$conf."#htmltemplate = 'custom.html'\n";
		

		
		return $conf;
	}
	
	public function SaveRule(){
		$this->SaveRuleID();
	}
	
	
	function BuildMembersList(){
		$dans=new dansguardian($this->hostname);
		
		$conf=$conf . "# Filter Groups List file for DansGuardian\n";
		$conf=$conf . "#\tGenerated on " . date("Y-m-d H:i:s")."\n";
		$conf=$conf . "#\n";
		$conf=$conf . "# Format is <user>=filter<1-9> where 1-9 are the groups\n";
		$conf=$conf . "#\n";
		$conf=$conf . "# Eg:\n";
		$conf=$conf . "# daniel=filter2\n";
		$conf=$conf . "#\n";
		$conf=$conf . "# This file is only of use if you have more than 1 filter group\n";
		$conf=$conf . "#\n\n";
				
		
		$rule_array=$dans->Master_rules_index;
		
		$pattern="(&(objectClass=posixGroup)(objectClass=ArticaSettings)(ArticaDansGuardianGroupRuleEnabled=*))";
		$filter=array("ArticaDansGuardianGroupRuleEnabled","memberUid");
		$ldap=new clladp();
		$sr =@ldap_search($ldap->ldap_connection,$ldap->suffix,$pattern,$filter);
		if(!$sr){return $conf;}
		
		
		$hash = ldap_get_entries($ldap->ldap_connection,$sr);
		
		for($i=0;$i<$hash["count"];$i++){
			$ruleindex=$hash[$i]["articadansguardiangroupruleenabled"][0];
			
			if($ruleindex>0){
				$rulename="filter{$ruleindex}";
				$conf=$conf ."\n######################################################\n";
				for($t=0;$t<$hash[$i]["memberuid"]["count"];$t++){
					$member=$hash[$i]["memberuid"][$t];
					if(preg_match('#(.+?);#',$rule_array[$ruleindex],$re)){
						$rulelog=$re[1];
					}else{$rulelog=$rule_array[$ruleindex];}
					if(substr($member,strlen($member)-1,1)=='$'){continue;}
					$conf=$conf ."$member=$rulename\t# $member using rule $rulelog\n";
					
				}
				
			}
			
		}
	return $conf;}
	
	
	
	
	
	
}
function IsDansGuardianrights(){
	$users=new usersMenus();
	writelogs("AsDansGuardianGroupRule=$users->AsDansGuardianGroupRule",__FUNCTION__,__FILE__,__LINE__);
	if(!$users->AsDansGuardianGroupRule){return false;}else{return true;}
	if(!$users->AsSquidAdministrator){return false;}
	return true;
}



?>
