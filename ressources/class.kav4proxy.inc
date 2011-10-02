<?php
include_once(dirname(__FILE__). '/class.ini.inc');
include_once(dirname(__FILE__). '/class.mysql.inc');
include_once(dirname(__FILE__). '/class.artica.inc');
class Kav4Proxy{
	var $q;
	var $main_array=array();
	var $VERSION=0;
	function Kav4Proxy(){
		$users=new usersMenus();
		if(!$GLOBALS["AS_ROOT"]){
			$this->VERSION=$users->KAV4PROXY_VERSION;
		}else{
			$this->VERSION=$this->GET_ROOT_VERSION();
		}
		$q=new mysql();
		$this->q=$q;
		$firstconfig=$this->GET("firstconfig","firstconfig");
		if($firstconfig<>1){
			$this->WriteDefault();
		}
		$this->Build_defaults();
	}
	
	public function GET($key,$value){
		$sql="SELECT `data` FROM kav4Proxy WHERE `key`='$key' AND `value`='$value' LIMIT 0,1";
		$ligne=mysql_fetch_array($this->q->QUERY_SQL($sql,"artica_backup"));	
		return trim($ligne["data"]);
	}
	
	private function WriteDefault(){
		$this->SET("icapserver.filter","ExcludeMimeType","audio/mpeg");
		$this->SET("icapserver.filter","ExcludeMimeType","video/x-msvideo");
		$this->SET("icapserver.filter","ExcludeMimeType","image/gif");
		$this->SET("icapserver.filter","ExcludeMimeType","image/jpeg");
		$this->SET("icapserver.filter","ExcludeMimeType","application/x-msmediaview");
		$this->SET("icapserver.filter","ExcludeMimeType","video/mpeg");
		$this->SET("icapserver.filter","ExcludeMimeType","audio/x-pn-realaudio");
		$this->SET("icapserver.filter","ExcludeMimeType","application/vnd.ms.wms-hdr.asfv");
		$this->SET("icapserver.filter","ExcludeMimeType","video/mp4");
		$this->SET("icapserver.filter","ExcludeMimeType","video/mpeg");
		$this->SET("icapserver.filter","ExcludeMimeType","video/mpeg4");
		$this->SET("icapserver.filter","ExcludeMimeType","video/mpg");
		$this->SET("icapserver.filter","ExcludeMimeType","video/quicktime");
		$this->SET("icapserver.filter","ExcludeMimeType","video/vnd.rn-realvideo");
		$this->SET("icapserver.filter","ExcludeMimeType","video/x-dv");
		$this->SET("icapserver.filter","ExcludeMimeType","video/x-la-asf");
		$this->SET("icapserver.filter","ExcludeMimeType","video/x-mpeg");
		$this->SET("icapserver.filter","ExcludeMimeType","video/x-ms-asf");
		$this->SET("icapserver.filter","ExcludeMimeType","video/x-ms-wmv");
		$this->SET("icapserver.filter","ExcludeMimeType","video/x-ms-wmx");
		$this->SET("icapserver.filter","ExcludeMimeType","video/x-ms-wvx");
		$this->SET("icapserver.filter","ExcludeMimeType","video/mp4");
		$this->SET("icapserver.filter","ExcludeMimeType","video/mpeg");
		$this->SET("icapserver.filter","ExcludeMimeType","video/mpeg4");
		$this->SET("icapserver.filter","ExcludeMimeType","video/mpg");
		$this->SET("icapserver.filter","ExcludeMimeType","video/quicktime");
		$this->SET("icapserver.filter","ExcludeMimeType","video/vnd.rn-realvideo");
		$this->SET("icapserver.filter","ExcludeMimeType","video/x-dv");
		$this->SET("icapserver.filter","ExcludeMimeType","video/x-la-asf");
		$this->SET("icapserver.filter","ExcludeMimeType","video/x-mpeg");
		$this->SET("icapserver.filter","ExcludeMimeType","video/x-ms-asf");
		$this->SET("icapserver.filter","ExcludeMimeType","video/x-ms-wmv");
		$this->SET("icapserver.filter","ExcludeMimeType","video/x-ms-wmx");
		$this->SET("icapserver.filter","ExcludeMimeType","video/x-ms-wvx");	
		$this->SET("icapserver.filter","ExcludeMimeType","application/x-mms-framed");
		$this->SET("icapserver.filter","ExcludeMimeType","application/vnd.rn-realmedia");
		$this->SET("icapserver.filter","ExcludeMimeType","application/x-troff-msvideo");
		$this->SET("icapserver.filter","ExcludeMimeType","application/x-rtsp-tunnelled");
		$this->SET("icapserver.filter","ExcludeMimeType","application/application/x-pncmd");
		$this->SET("icapserver.filter","ExcludeMimeType","application/x-dvi");
		$this->SET("icapserver.filter","ExcludeMimeType","application/x-isoview");
		$this->SET("icapserver.filter","ExcludeMimeType","video/3gpp");
		$this->SET("icapserver.filter","ExcludeMimeType","video/dv");
		$this->SET("icapserver.filter","ExcludeMimeType","video/isivideo");
		$this->SET("icapserver.filter","ExcludeMimeType","video/mpeg");
		$this->SET("icapserver.filter","ExcludeMimeType","video/quicktime");
		$this->SET("icapserver.filter","ExcludeMimeType","video/vivo");
		$this->SET("icapserver.filter","ExcludeMimeType","video/vnd.rn-realvideo");
		$this->SET("icapserver.filter","ExcludeMimeType","video/wavelet");
		$this->SET("icapserver.filter","ExcludeMimeType","video/x-3gpp2");
		$this->SET("icapserver.filter","ExcludeMimeType","video/x-anim");
		$this->SET("icapserver.filter","ExcludeMimeType","video/x-avi");
		$this->SET("icapserver.filter","ExcludeMimeType","video/x-flic");
		$this->SET("icapserver.filter","ExcludeMimeType","video/x-mng");
		$this->SET("icapserver.filter","ExcludeMimeType","video/x-ms-asf");
		$this->SET("icapserver.filter","ExcludeMimeType","video/x-ms-wmv");
		$this->SET("icapserver.filter","ExcludeMimeType","video/x-msvideo");
		$this->SET("icapserver.filter","ExcludeMimeType","video/x-nsv");
		$this->SET("icapserver.filter","ExcludeMimeType","video/x-real-video");
		$this->SET("icapserver.filter","ExcludeMimeType","video/x-sgi-movie");
		$this->SET("icapserver.filter","ExcludeMimeType","video/flv");
		$this->SET("icapserver.filter","ExcludeMimeType","video/x-flv");
		$this->SET("icapserver.filter","ExcludeMimeType","video/divx");
		$this->SET("icapserver.filter","ExcludeMimeType","video/x-matroska");
		$this->SET("icapserver.filter","ExcludeMimeType","video/x-quicktime");
		$this->SET("icapserver.filter","ExcludeMimeType","video/mp4");
		$this->SET("icapserver.filter","ExcludeMimeType","flv-application/octet-stream");
		$this->SET("icapserver.filter","ExcludeMimeType","application/x-shockwave-flash");		
		$this->SET("icapserver.filter","ExcludeURL","deb");
		$this->SET("icapserver.filter","ExcludeURL","rpm");
		$this->SET("icapserver.filter","ExcludeURL","iso");
		$this->SET("icapserver.filter","ExcludeURL","img");
		$this->SET("icapserver.filter","ExcludeURL","nrg");		
		$this->SET("icapserver.filter","ExcludeURL","cue");
		$this->SET("icapserver.filter","ExcludeURL","img");
		$this->SET("icapserver.filter","ExcludeURL","jpg");
		$this->SET("icapserver.filter","ExcludeURL","gif");
		$this->SET("icapserver.filter","ExcludeURL","jpeg");
		$this->SET("icapserver.filter","ExcludeURL","css");
		$this->SET("icapserver.filter","ExcludeURL","png");
		$this->SET("icapserver.filter","ExcludeURL","ico");
		
		
		
		$this->SET("icapserver.filter","MaxReqLength","5120000");		
		$this->SET("icapserver.protocol","PreviewSize",128);
		$this->SET("icapserver.process","MaxChildren","10");
		$this->SET("icapserver.process","IdleChildren","1");
		$this->SET("icapserver.process","MaxReqsPerChild","15000");
		$this->SET("icapserver.process","MaxEnginesPerChild","10");
		$this->SET("icapserver.process","MaxEnginesPerChild","10");
		$this->SET("icapserver.actions","MaxReqLengthAction","skip");
		$this->SET("icapserver.actions","PartialResponseAction","check");
		$this->SET("icapserver.actions","PartialRequestAction","check");
		
		}
	
	public function SET($key,$value,$data){
		$md5=md5($key.$value.$data);
		$sql="INSERT IGNORE INTO kav4Proxy (`key`,`value`,`data`,`zMD5`) VALUES('$key','$value','$data','$md5');";
		$this->q->QUERY_SQL($sql,"artica_backup");
	}
	public function MOD($key,$value,$data){
		$md5=md5($key.$value.$data);
		if($this->GET($key,$value)==null){$this->SET($key,$value,$data);}
		
		
		$sql="UPDATE kav4Proxy SET `data`='$data' WHERE `key`='$key' AND `value`='$value'";
		$this->q->QUERY_SQL($sql,"artica_backup");
		if(!$this->q->ok){
			echo $sql."\n".$kav->q->mysql_error;
			return;
		}

		writelogs("$sql OK",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		
	}	
	
	private function ExcludeMimeType(){
		$sql="SELECT data FROM kav4Proxy WHERE `key`='icapserver.filter' AND `value`='ExcludeMimeType'";
		$results=$this->q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$f[]="ExcludeMimeType=^{$ligne["data"]}$";
		}
		return implode("\n",$f);
		
	}
	
	private function ExcludeURL(){
		$sql="SELECT data FROM kav4Proxy WHERE `key`='icapserver.filter' AND `value`='ExcludeURL'";
		$results=$this->q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$f[]="ExcludeURL=.*?\.{$ligne["data"]}$";
		}
		return implode("\n",$f);
		
	}	
	
	private function GET_ROOT_VERSION(){
		exec("/opt/kaspersky/kav4proxy/sbin/kav4proxy-kavicapserver -v 2>&1",$results);
		while (list ($num, $ligne) = each ($results) ){
		if(preg_match("#version ([0-9\.]+)\/RELEASE build#", $ligne,$re)){return $re[1];}
		}
		
	}
	
	
function Build_defaults(){
		$artica=new artica_general();
		if($artica->ArticaProxyServerEnabled=='yes'){
			$this->main_array["UseProxy"]='yes';
			$this->main_array["ProxyAddress"]=$artica->ArticaCompiledProxyUri;
		}
		
		$this->main_array["MaxReqLength"]=$this->GET("icapserver.filter","MaxReqLength");
		$this->main_array["PreviewSize"]=$this->GET("icapserver.protocol","PreviewSize");	
		$this->main_array["MaxChildren"]=$this->GET("icapserver.process","MaxChildren");	
		$this->main_array["IdleChildren"]=$this->GET("icapserver.process","IdleChildren");	
		$this->main_array["MaxReqsPerChild"]=$this->GET("icapserver.process","MaxReqsPerChild");	
		$this->main_array["MaxEnginesPerChild"]=$this->GET("icapserver.process","MaxEnginesPerChild");	
		$this->main_array["PreviewSize"]=$this->GET("icapserver.protocol","PreviewSize");
		$this->main_array["ListenAddress"]=$this->GET("icapserver.network","ListenAddress");
					
		if(!isset($this->main_array["Timeout"])){$this->main_array["Timeout"]=null;}
		if(!isset($this->main_array["AnswerMode"])){$this->main_array["AnswerMode"]=null;}
		if(!isset($this->main_array["MaxSendDelayTime"])){$this->main_array["MaxSendDelayTime"]=null;}
		if(!isset($this->main_array["MaxConnections"])){$this->main_array["MaxConnections"]=null;}
		if(!isset($this->main_array["Allow204"])){$this->main_array["Allow204"]=null;}
		
		
		if($this->main_array["Timeout"]==null){$this->main_array["Timeout"]=0;}
		if($this->main_array["MaxChildren"]==null){$this->main_array["MaxChildren"]=10;}
		if($this->main_array["IdleChildren"]==null){$this->main_array["IdleChildren"]=1;}
		if($this->main_array["MaxReqsPerChild"]==null){$this->main_array["MaxReqsPerChild"]="15000";}
		if($this->main_array["MaxEnginesPerChild"]==null){$this->main_array["MaxEnginesPerChild"]=10;}
		if($this->main_array["AnswerMode"]==null){$this->main_array["AnswerMode"]="partial";}
		if($this->main_array["MaxSendDelayTime"]==null){$this->main_array["MaxSendDelayTime"]="10";}
		if($this->main_array["PreviewSize"]==null){$this->main_array["PreviewSize"]="128";}
		if($this->main_array["MaxConnections"]==null){$this->main_array["MaxConnections"]="5000";}
		if($this->main_array["Allow204"]==null){$this->main_array["Allow204"]="yes";}
		
		if(!isset($this->main_array["ScanMailBases"])){$this->main_array["ScanMailBases"]=null;}
		if(!isset($this->main_array["ScanMailPlain"])){$this->main_array["ScanMailPlain"]=null;}
		if(!isset($this->main_array["UseHeuristic"])){$this->main_array["UseHeuristic"]=null;}
		if(!isset($this->main_array["Cure"])){$this->main_array["Cure"]=null;}
		if(!isset($this->main_array["UseAVbasesSet"])){$this->main_array["UseAVbasesSet"]=null;}
		if(!isset($this->main_array["MaxScanTime"])){$this->main_array["MaxScanTime"]=null;}
		if(!isset($this->main_array["UseUpdateServerUrl"])){$this->main_array["UseUpdateServerUrl"]=null;}
		if(!isset($this->main_array["UseUpdateServerUrlOnly"])){$this->main_array["UseUpdateServerUrlOnly"]=null;}
		if(!isset($this->main_array["RegionSettings"])){$this->main_array["RegionSettings"]=null;}
		if(!isset($this->main_array["UseProxy"])){$this->main_array["UseProxy"]=null;}
		if(!isset($this->main_array["ListenAddress"])){$this->main_array["ListenAddress"]=null;}
		if(!isset($this->main_array["ScanPacked"])){$this->main_array["ScanPacked"]=null;}
		if(!isset($this->main_array["ScanArchives"])){$this->main_array["ScanArchives"]=null;}
		if(!isset($this->main_array["UpdateServerUrl"])){$this->main_array["UpdateServerUrl"]=null;}
		if(!isset($this->main_array["MaxReqLengthAction"])){$this->main_array["MaxReqLengthAction"]=null;}
		if(!isset($this->main_array["PartialResponseAction"])){$this->main_array["PartialResponseAction"]=null;}
		if(!isset($this->main_array["PartialRequestAction"])){$this->main_array["PartialRequestAction"]=null;}								
		
		if($this->main_array["ScanPacked"]==null){$this->main_array["ScanPacked"]="yes";}
		if($this->main_array["ScanArchives"]==null){$this->main_array["ScanArchives"]="yes";}
		if($this->main_array["ScanMailBases"]==null){$this->main_array["ScanMailBases"]="yes";}
		if($this->main_array["ScanMailPlain"]==null){$this->main_array["ScanMailPlain"]="yes";}
		if($this->main_array["UseHeuristic"]==null){$this->main_array["UseHeuristic"]="yes";}
		if($this->main_array["Cure"]==null){$this->main_array["Cure"]="no";}
		if($this->main_array["UseAVbasesSet"]==null){$this->main_array["UseAVbasesSet"]="extended";}
		if($this->main_array["MaxScanTime"]==null){$this->main_array["MaxScanTime"]="300";}
		if($this->main_array["UseUpdateServerUrl"]==null){$this->main_array["UseUpdateServerUrl"]="no";}
		if($this->main_array["UpdateServerUrl"]==null){$this->main_array["UpdateServerUrl"]="ftp://downloads1.kaspersky-labs.com";}
		if($this->main_array["UseUpdateServerUrlOnly"]==null){$this->main_array["UseUpdateServerUrlOnly"]="no";}
		if($this->main_array["RegionSettings"]==null){$this->main_array["RegionSettings"]="Europe";}
		if($this->main_array["UseProxy"]==null){$this->main_array["UseProxy"]="no";}
		if($this->main_array["ListenAddress"]==null){$this->main_array["ListenAddress"]="127.0.0.1:1344";}
		if($this->main_array["MaxReqLengthAction"]==null){$this->main_array["MaxReqLengthAction"]="skip";}
		if($this->main_array["PartialResponseAction"]==null){$this->main_array["PartialResponseAction"]="check";}
		if($this->main_array["PartialRequestAction"]==null){$this->main_array["PartialRequestAction"]="check";}    
		

		
	}
	
	public function is_tmpfs_mounted(){
		$unix=new unix();
		$mount=$unix->find_program("mount");
		exec("$mount 2>&1",$results);
		while (list ($num, $ligne) = each ($results) ){if(preg_match("#^tmpfs.+?Kav4proxy.+?tmpfs#", $ligne)){return true;}}
		return false;
		
	}
	
	
	function build_config(){
		$sock=new sockets();
		$TempPath="/tmp";
		if($GLOBALS["AS_ROOT"]){
			$unix=new unix();
			$mount=$unix->find_program("mount");
			$umount=$unix->find_program("umount");
			$Kav4ProxyTMPFS=$sock->GET_INFO("Kav4ProxyTMPFS");
			if(!is_numeric($Kav4ProxyTMPFS)){$Kav4ProxyTMPFS=0;}
			$Kav4ProxyTMPFSMB=$sock->GET_INFO("Kav4ProxyTMPFSMB");
			if(!is_numeric($Kav4ProxyTMPFSMB)){$Kav4ProxyTMPFSMB=512;}	
			if($Kav4ProxyTMPFS==1){
				echo "Starting......: Kav4proxy tmpfs is enabled for {$Kav4ProxyTMPFSMB}MB\n";
				if(!$this->is_tmpfs_mounted()){
					if(!is_dir("/tmp/Kav4proxy")){@mkdir("/tmp/Kav4proxy",0755,true);}
					$cmd="$mount -t tmpfs -o size={$Kav4ProxyTMPFSMB}M tmpfs /tmp/Kav4proxy";
					if($GLOBALS["VERBOSE"]){echo $cmd."\n";}
					shell_exec($cmd);
					
					if($this->is_tmpfs_mounted()){
						echo "Starting......: Kav4proxy tmpfs mounted...\n";
						$TempPath="/tmp/Kav4proxy";
					}else{
						echo "Starting......: Kav4proxy tmpfs failed to mount return back to disk\n";
					}
					
				}else{
					echo "Starting......: Kav4proxy tmpfs mounted...\n";
					$TempPath="/tmp/Kav4proxy";	
					
				}
			
			}else{
				echo "Starting......: Kav4proxy tmpfs is disabled\n";
				if($this->is_tmpfs_mounted()){
					echo "Starting......: Kav4proxy unmounting filesystem\n";
					shell_exec("$umount -f /tmp/Kav4proxy");
				}
			}
			
		}
		
		
				if($GLOBALS["AS_ROOT"]){
					if(preg_match("#([0-9])\.([0-9]).([0-9]+)#",  $this->VERSION,$re)){
						$rev=$re[3];
					}
					echo "Starting......: Kav4proxy current version is $this->VERSION (revision $rev)\n";
				}
				$sock=new sockets();
				$bases="/var/opt/kaspersky/kav4proxy/bases";
				
				$this->Build_defaults();
				$conf[]="[path]\n";
				$conf[]="BasesPath=$bases";
				$conf[]="LicensePath=/var/opt/kaspersky/kav4proxy/licenses";
				$conf[]="TempPath=$TempPath";
				if($rev>=67){$conf[]="KLPluginsPath=/opt/kaspersky/kav4proxy/lib/ppl";}
				
				$conf[]="";				
				$conf[]="[options]";
				$conf[]="User=kluser";
				$conf[]="Group=klusers";
				 
				
				$conf[]="";
				$conf[]="[locale]";
				$conf[]="DateFormat=%d-%m-%Y";
				$conf[]="TimeFormat=%H:%M:%S";
				$conf[]="";
				$conf[]="[icapserver.network]";
				$conf[]="ListenAddress={$this->main_array["ListenAddress"]}";
				$conf[]="Timeout={$this->main_array["Timeout"]}";
				$conf[]="";
				$conf[]="[icapserver.process]";
				$conf[]="MaxChildren={$this->main_array["MaxChildren"]}";
				$conf[]="IdleChildren={$this->main_array["IdleChildren"]}";
				$conf[]="MaxReqsPerChild={$this->main_array["MaxReqsPerChild"]}";
				$conf[]="MaxEnginesPerChild={$this->main_array["MaxEnginesPerChild"]}";
				
				$conf[]="";
				$conf[]="[icapserver.protocol]";
				$conf[]="AnswerMode={$this->main_array["AnswerMode"]}";
				$conf[]="MaxSendDelayTime={$this->main_array["MaxSendDelayTime"]}";
				$conf[]="PreviewSize=".$this->GET("icapserver.protocol","PreviewSize");
				$conf[]="MaxConnections={$this->main_array["MaxConnections"]}";
				$conf[]="Allow204={$this->main_array["Allow204"]}";
				if($rev>=67){
					$conf[]="SendAVScanResult=false";
					$conf[]="ReqModeServiceUrl=av/reqmod";
					$conf[]="RespModeServiceUrl=av/respmod";						
				}
		
				
				
				$conf[]="SendAVScanResult=no";
				$conf[]="HTTPClientIpICAPHeader=X-Client-IP";
				$conf[]="HTTPUserNameICAPHeader=X-Client-Username";				
				$conf[]="";
				$conf[]="[icapserver.path]";
				$conf[]="PidFile=/var/run/kav4proxy/kavicapserver.pid";
				$conf[]="CorePath=";
				$conf[]="[icapserver.groups]";
				$conf[]="Priority=0";
				$conf[]="ClientIP=.*";
				$conf[]="URL=.*";
				
				$conf[]="";
				$conf[]="[icapserver.engine.options]";
				$conf[]="ScanPacked={$this->main_array["ScanPacked"]}";
				$conf[]="ScanArchives={$this->main_array["ScanArchives"]}";
				$conf[]="ScanMailBases={$this->main_array["ScanMailBases"]}";
				$conf[]="ScanMailPlain={$this->main_array["ScanMailPlain"]}";
				$conf[]="Cure={$this->main_array["Cure"]}";
				if($rev<67){
					$conf[]="UseAVbasesSet={$this->main_array["UseAVbasesSet"]}";
					$conf[]="UseHeuristic={$this->main_array["UseHeuristic"]}";
				}
				if($rev>=67){
					$conf[]="UseAnalyzer=yes";
					$conf[]="HeuristicLevel=recommended";
				}
				$conf[]="MaxScanTime={$this->main_array["MaxScanTime"]}";
				$conf[]="";
				$conf[]="[icapserver.filter]";
				$conf[]=$this->ExcludeMimeType();
				$conf[]=$this->ExcludeURL();
				$conf[]="MaxReqLength=".$this->main_array["MaxReqLength"];
				
				$conf[]="";
				$conf[]="[icapserver.actions]";
				$conf[]="InfectedAction=deny";
				$conf[]="SuspiciousAction=deny";
				$conf[]="WarningAction=deny";
				$conf[]="ErrorAction=skip";
				$conf[]="ProtectedAction=skip";
				$conf[]="CorruptedAction=skip";
				$conf[]="CuredAction=skip";
				$conf[]="LicenseErrorAction=skip";
				$conf[]="BasesErrorAction=skip";
				if($rev>=67){
					if($this->main_array["MaxReqLengthAction"]=="check"){$this->main_array["MaxReqLengthAction"]="skip";}
					$conf[]="MaxReqLengthAction={$this->main_array["MaxReqLengthAction"]}";
					$conf[]="PartialResponseAction={$this->main_array["PartialResponseAction"]}";
					$conf[]="PartialRequestAction={$this->main_array["PartialRequestAction"]}";
				}
						
				
				$conf[]="";
				$conf[]="[icapserver.notify]";
				$conf[]="NotifyTemplateDir=/opt/kaspersky/kav4proxy/share/notify";
				$conf[]="NotifyScript=/opt/kaspersky/kav4proxy/share/examples/artica.sh";
				$conf[]="[icapserver.statistics]";
				$conf[]="CounterStatisticsFile=/var/log/kaspersky/kav4proxy/counter.stats";
				$conf[]="AVStatisticsFile=/var/log/kaspersky/kav4proxy/av.stats";
				$conf[]="AVStatisticsAddress=";
				
				$conf[]="";
				$conf[]="[icapserver.report]";
				$conf[]="ReportFileName=/var/log/kaspersky/kav4proxy/kavicapserver.log";
				$conf[]="Buffered=no";
				$conf[]="ReportLevel=4";
				$conf[]="ShowOk=yes";
				$conf[]="Append=yes";
				
				$conf[]="";
				$conf[]="[updater.path]";
				$conf[]="BackUpPath=/var/opt/kaspersky/kav4proxy/bases.backup";
				$conf[]="AVBasesTestPath=/opt/kaspersky/kav4proxy/lib/bin/avbasestest";
				
				$conf[]="";
				$conf[]="[updater.options]";
				$conf[]="KeepSilent=no";
				$conf[]="UseUpdateServerUrl={$this->main_array["UseUpdateServerUrl"]}";
				$conf[]="UseUpdateServerUrlOnly={$this->main_array["UseUpdateServerUrlOnly"]}";
				$conf[]="PostUpdateCmd=/etc/init.d/kav4proxy reload_avbase";
				$conf[]="RegionSettings={$this->main_array["RegionSettings"]}";
				$conf[]="ConnectTimeout=30";
				$conf[]="ProxyAddress={$this->main_array["ProxyAddress"]}";
				$conf[]="UseProxy={$this->main_array["UseProxy"]}";
				$conf[]="PassiveFtp=yes";
				
				
				$conf[]="";
				$conf[]="[updater.report]";
				$conf[]="ReportFileName=/var/log/kaspersky/kav4proxy/keepup2date.log";
				$conf[]="ReportLevel=4";
				$conf[]="Append=true";
				$conf[]="";
				
				$conf[]=$this->Kav4Groups();
				
				$sock=new sockets();
				$config=implode("\n",$conf);
				$sock->SaveConfigFile($config,"Kav4ProxyMainConf");
				return $config;
		
	}

	private function Kav4Groups(){
		$sql="SELECT * FROM Kav4Proxy_groups WHERE enabled=1 ORDER BY priority DESC";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");	
		if(!$q->ok){die();}
		if(mysql_num_rows($results)==0){
			echo "Starting......: Kav4proxy no groups...\n";
			return;
		}
		
		if($GLOBALS["AS_ROOT"]){
			if(preg_match("#([0-9])\.([0-9]).([0-9]+)#",  $this->VERSION,$re)){
				$rev=$re[3];
			}
		}		
		
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$EngineOption=unserialize(base64_decode($ligne["EngineOption"]));
			$ClientIP=$EngineOption["ClientIP"];
			$ClientURI=$EngineOption["ClientURI"];
			$ExcludeMimeType=$EngineOption["ExcludeMimeType"];
			$ExcludeURL=$EngineOption["ExcludeURL"];
			$hash=unserialize(base64_decode($ligne["EngineAction"]));
			if(!is_array($ExcludeMimeType)){$ExcludeMimeType=array();}
			if(!is_array($ExcludeURL)){$ExcludeURL=array();}
			if(count($ClientIP)==0){$ClientIP[]=".*";}
			if(count($ClientURI)==0){$ClientURI[]=".*";}
	
			if(!is_numeric($hash["Cure"])){$hash["Cure"]=1;}
			if(!is_numeric($hash["ScanArchives"])){$hash["ScanArchives"]=1;} 
			if(!is_numeric($hash["ScanPacked"])){$hash["ScanPacked"]=1;} 
			if(!is_numeric($hash["ScanMailBases"])){$hash["ScanMailBases"]=1;} 
			if(!is_numeric($hash["ScanMailPlain"])){$hash["ScanMailPlain"]=1;}
			if(!is_numeric($hash["MaxScanTime"])){$hash["MaxScanTime"]=300;}
			if(!is_numeric($hash["MaxReqLength"])){$hash["MaxReqLength"]=512000;}		
			
			$groupname=$ligne["groupname"];
			echo "Starting......: Kav4proxy check group \"$groupname\"...\n";
			$f[]="\n[icapserver.groups:$groupname]";
			$f[]="Priority=$Priority";
			while (list ($num, $ligne) = each ($ClientIP) ){
				if(trim($ligne)==null){continue;}
				$f[]="ClientIP=$ligne";
			}
			while (list ($num, $ligne) = each ($ClientURI) ){
				if(trim($ligne)==null){continue;}
				$f[]="URL=$ligne";
			}

			$f[]="\n[icapserver.notify:$groupname]";
			$f[]="NotifyScript=/opt/kaspersky/kav4proxy/share/examples/artica.sh";
			$f[]="NotifyTemplateDir=/opt/kaspersky/kav4proxy/share/notify";		
			$f[]="\n[icapserver.filter:$groupname]";
			$f[]="MaxReqLength={$hash["MaxReqLength"]}";
			while (list ($num, $ligne) = each ($ExcludeMimeType) ){
				if(trim($ligne)==null){continue;}
				$f[]="ExcludeMimeType=$ligne";
			}	
			while (list ($num, $ligne) = each ($ExcludeURL) ){
				if(trim($ligne)==null){continue;}
				$f[]="ExcludeURL=$ligne";
			}
			
			if($hash["Cure"]==1){$hash["Cure"]="yes";}else{$hash["Cure"]="no";}
			if($hash["ScanArchives"]==1){$hash["ScanArchives"]="yes";}else{$hash["ScanArchives"]="no";}
			if($hash["ScanMailBases"]==1){$hash["ScanMailBases"]="yes";}else{$hash["ScanMailBases"]="no";}
			if($hash["ScanMailPlain"]==1){$hash["ScanMailPlain"]="yes";}else{$hash["ScanMailPlain"]="no";}
			if($hash["ScanPacked"]==1){$hash["ScanPacked"]="yes";}else{$hash["ScanPacked"]="no";}
			if($hash["UseAVbasesSet"]==null){$hash["UseAVbasesSet"]="extended";}
			
			    
			if($hash["InfectedAction"]=="no"){$hash["InfectedAction"]="deny";}
			if($hash["ErrorAction"]=="no"){$hash["ErrorAction"]="deny";}
			if($hash["CuredAction"]=="no"){$hash["CuredAction"]="deny";}
			if($hash["CorruptedAction"]=="no"){$hash["CorruptedAction"]="deny";}
			if($hash["BasesErrorAction"]=="no"){$hash["BasesErrorAction"]="deny";}
			if($hash["ErrorAction"]==null){$hash["ErrorAction"]="skip";}
			
			
			$f[]="\n[icapserver.engine.options:$groupname]";
			$f[]="Cure={$hash["Cure"]}";
			$f[]="MaxScanTime={$hash["MaxScanTime"]}";
			$f[]="ScanArchives={$hash["ScanArchives"]}";
			$f[]="ScanMailBases={$hash["ScanMailBases"]}";
			$f[]="ScanMailPlain={$hash["ScanMailPlain"]}";
			$f[]="ScanPacked={$hash["ScanPacked"]}";
			if($rev<67){
				$f[]="UseAVbasesSet={$hash["UseAVbasesSet"]}";
				$f[]="UseHeuristic=yes";
			}
			
			$f[]="\n[icapserver.actions:$groupname]";
			$f[]="BasesErrorAction={$hash["BasesErrorAction"]}";
			$f[]="CorruptedAction={$hash["CorruptedAction"]}";
			$f[]="CuredAction={$hash["CuredAction"]}";
			$f[]="ErrorAction={$hash["ErrorAction"]}";
			$f[]="InfectedAction={$hash["InfectedAction"]}";
			$f[]="LicenseErrorAction=skip";
			$f[]="ProtectedAction=skip";
			$f[]="SuspiciousAction=deny";
			$f[]="WarningAction=deny";			
		}

		return @implode("\n",$f);
		
	}
	
	
}

?>