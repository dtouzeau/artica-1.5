<?php
include_once(dirname(__FILE__). '/class.ini.inc');
include_once(dirname(__FILE__). '/class.mysql.inc');
include_once(dirname(__FILE__). '/class.artica.inc');
class Kav4Proxy{
	var $q;
	var $main_array=array();
	
	function Kav4Proxy(){
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
		$this->SET("firstconfig","firstconfig","1");
		
		}
	
	public function SET($key,$value,$data){
		$md5=md5($key.$value.$data);
		$sql="INSERT INTO kav4Proxy (`key`,`value`,`data`,`zMD5`) VALUES('$key','$value','$data','$md5');";
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
		 
		
	}
	
	
	function build_config(){
				$sock=new sockets();
				$bases="/var/opt/kaspersky/kav4proxy/bases";
				
				$this->Build_defaults();
				$conf[]="[path]\n";
				$conf[]="BasesPath=$bases";
				$conf[]="LicensePath=/var/opt/kaspersky/kav4proxy/licenses";
				$conf[]="TempPath=/tmp";
				
				$conf[]="";				
				$conf[]="[options]";
				$conf[]="User=kluser";
				$conf[]="Group=klusers";
				
				
				$conf[]="";
				$conf[]="[locale]";
				$conf[]="DateFormat=%d-%m-%Y";
				$conf[]="TimeFormat=%H:%M:%S";
				$conf[]="[icapserver.network]";
				$conf[]="ListenAddress=127.0.0.1:1344";
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
				$conf[]="UseHeuristic={$this->main_array["UseHeuristic"]}";
				$conf[]="Cure={$this->main_array["Cure"]}";
				$conf[]="UseAVbasesSet={$this->main_array["UseAVbasesSet"]}";
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
				
				$sock=new sockets();
				$config=implode("\n",$conf);
				$sock->SaveConfigFile($config,"Kav4ProxyMainConf");
				return $config;
		
	}	
	
	
}

?>