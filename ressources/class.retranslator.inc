<?php

class retranslator{
	var $RetranslatorHttpdEnabled=0;
	var $RetranslatorEnabled=0;
	var $RetranslatorHttpdPort=80;
	var $RetranslatorCronMinutes=60;
	var $RetranslatorRegionSettings="ru";
	var $RetranslatorUseProxy="no";
	var $RetranslatorProxyAddress=null;
	var $RetranslatorUseUpdateServerUrl="no";
	var $RetranslatorUpdateServerUrl=null;
	var $RetranslatorUseUpdateServerUrlOnly="no";
	var $RetranslatorRetranslateComponentsList="AVS, CORE, BLST, Updater";
	var $RetranslatorReportLevel=2;
	var $MyRetranslateComponentsList=array();
	var $master_array=array();
	
	
	function retranslator(){
		$this->BuildArray();
		$sock=new sockets();
		
		$this->httpd_enabled=$sock->GET_INFO('RetranslatorEnabled');
		$this->RetranslatorHttpdEnabled=trim($sock->GET_INFO('RetranslatorHttpdEnabled'));
		$this->RetranslatorHttpdPort=trim($sock->GET_INFO('RetranslatorHttpdPort'));
		$this->RetranslatorEnabled=trim($sock->GET_INFO('RetranslatorEnabled'));
		$this->RetranslatorCronMinutes=trim($sock->GET_INFO('RetranslatorCronMinutes'));
		$this->RetranslatorRegionSettings=trim($sock->GET_INFO('RetranslatorRegionSettings'));
		$this->RetranslatorUseProxy=trim($sock->GET_INFO('RetranslatorUseProxy'));
		$this->RetranslatorProxyAddress=trim($sock->GET_INFO('RetranslatorProxyAddress'));
		$this->RetranslatorUseUpdateServerUrl=trim($sock->GET_INFO('RetranslatorUseUpdateServerUrl'));
		$this->RetranslatorUpdateServerUrl=trim($sock->GET_INFO('RetranslatorUpdateServerUrl'));
		$this->RetranslatorUseUpdateServerUrlOnly=trim($sock->GET_INFO('RetranslatorUseUpdateServerUrlOnly'));
		$this->RetranslatorRetranslateComponentsList=trim($sock->GET_INFO('RetranslatorRetranslateComponentsList'));
		$this->RetranslatorReportLevel=trim($sock->GET_INFO('RetranslatorReportLevel'));
		
		if($this->RetranslatorHttpdEnabled==null){$this->RetranslatorHttpdEnabled=0;}
		if($this->RetranslatorHttpdPort==null){$this->RetranslatorHttpdPort=80;}
		if($this->RetranslatorEnabled==null){$this->RetranslatorEnabled=0;}
		if($this->RetranslatorCronMinutes==null){$this->RetranslatorCronMinutes=60;}
		if($this->RetranslatorRegionSettings==null){$this->RetranslatorRegionSettings="ru";}
		if($this->RetranslatorUseProxy==null){$this->RetranslatorUseProxy="no";}
		if($this->RetranslatorProxyAddress==null){$this->RetranslatorProxyAddress="http://username:password@server:port/";}
		if($this->RetranslatorUseUpdateServerUrl==null){$this->RetranslatorUseUpdateServerUrl="no";}
		if($this->RetranslatorUseUpdateServerUrlOnly==null){$this->RetranslatorUseUpdateServerUrlOnly="no";}
		if($this->RetranslatorRetranslateComponentsList==null){$this->RetranslatorRetranslateComponentsList="AVS,CORE,BLST,UPDATER";}
		if($this->RetranslatorReportLevel==null){$this->RetranslatorReportLevel=2;}
		
		$arr=explode(",",$this->RetranslatorRetranslateComponentsList);
	
			while (list ($num, $ligne) = each ($arr) ){
				if(trim($ligne)==null){continue;}
				$this->MyRetranslateComponentsList[trim(strtoupper($ligne))]=true;
			}
			
			
		
	}
	
	function SaveToServer(){
		$sock=new sockets();
		reset($this->MyRetranslateComponentsList);
		while (list ($num, $ligne) = each ($this->MyRetranslateComponentsList) ){
			$arr[]=$num;
			
		}
		
		$this->RetranslatorRetranslateComponentsList=implode(",",$arr);

		$sock->SET_INFO("RetranslatorHttpdEnabled",$this->RetranslatorHttpdEnabled);
		$sock->SET_INFO("RetranslatorHttpdPort",$this->RetranslatorHttpdPort);
		$sock->SET_INFO("RetranslatorEnabled",$this->RetranslatorEnabled);
		$sock->SET_INFO("RetranslatorCronMinutes",$this->RetranslatorCronMinutes);
		$sock->SET_INFO("RetranslatorRegionSettings",$this->RetranslatorRegionSettings);
		$sock->SET_INFO("RetranslatorUseProxy",$this->RetranslatorUseProxy);
		$sock->SET_INFO("RetranslatorProxyAddress",$this->RetranslatorProxyAddress);
		$sock->SET_INFO("RetranslatorUseUpdateServerUrl",$this->RetranslatorUseUpdateServerUrl);
		$sock->SET_INFO("RetranslatorUpdateServerUrl",$this->RetranslatorUpdateServerUrl);
		$sock->SET_INFO("RetranslatorUseUpdateServerUrlOnly",$this->RetranslatorUseUpdateServerUrlOnly);
		$sock->SET_INFO("RetranslatorRetranslateComponentsList",$this->RetranslatorRetranslateComponentsList);
		$sock->SET_INFO("RetranslatorReportLevel",$this->RetranslatorReportLevel);
		$sock->getFrameWork("cmd.php?RestartRetranslator=yes");
		
	}
	
	function TestsRetranslations($array){
		
		if(!is_array($array)){return false;}
		while (list ($num, $ligne) = each ($array) ){
			if(!$this->MyRetranslateComponentsList[$ligne]){
				return false;
			}
		}
		return true;
		
	}
	
	function RetranslatorSitesList(){
		$sock=new sockets();
		$datas=unserialize(base64_decode($sock->getFrameWork("cmd.php?RetranslatorSitesList=yes")));
		//
		if(!is_array($datas)){return array();}
		while (list ($num, $ligne) = each ($datas) ){
			if(preg_match("#Other keepup2date instance is running#",$ligne)){
				$re[]="{please_wait}:&nbsp;&nbsp; &laquo;$ligne&raquo;";
				continue;
			}
			
			if(preg_match("#.+?tp:.+#",$ligne)){
				$re[]=$ligne;
				continue;
			}
		}
		
		if(!is_array($re)){return array();}
		return $re;
	}
	
	
	
	
	function BuildArray(){
		
		$this->master_array["BETA KAV for Windows Workstations"]=array(
		"VERSION"=>"8.0",
		"BUILD"=>"8.0.0.1015",
		//AS
		"COMPONENTS"=>array("UPDATER","KDB","ARK","VLNS","ADBU","AH","APU","AS","BB","BB2","CORE","BLST","KAV8EXEC","RM","RT","WAVI386","EMU","PAS","PARCTL","ASTRM")
		);
		
		$this->master_array["KAV for Windows Workstations 6.0R2"]=array(
		"VERSION"=>"6.0r2 >2010",
		"BUILD"=>"r2",
		"COMPONENTS"=>array("UPDATER","AVS","KDB","ARK","ADB","ADBU","AH","APU","BLST2","WMUF","WA","EMU","PAS","ASTRM","AHI386","AHX64","AP","AS","BB","BB2","CORE","KAV2006EXEC","NEWS","RM","RT","WM","AK6","INDEX60")
		);		
	
		
		$this->master_array["BETA KAV SOS"]=array(
		"VERSION"=>"8.0",
		"BUILD"=>"8.0.0.1015",
		"COMPONENTS"=>array("UPDATER","KDB","ARK","VLNS","EMU")
		);	
		
		$this->master_array["BETA KAV for Windows Server"]=array(
		"VERSION"=>"8.0",
		"BUILD"=>"8.0.0.1015",
		"COMPONENTS"=>array("UPDATER","KDB","ARK","VLNS","CORE","BLST","KAV8EXEC","RT","EMU")
		);	

		$this->master_array["BETA KAV for Windows Server EE"]=array(
		"VERSION"=>"8.0",
		"BUILD"=>"8.0",
		"COMPONENTS"=>array("UPDATER","KDB","BLST2","KAV8WSEE","RT","EMU")
		);	


		$this->master_array["Administration KIT"]=array(
		"VERSION"=>"8.0",
		"BUILD"=>"8.0.1838",
		"COMPONENTS"=>array("UPDATER","CORE","AKP8","RT","RTAK7","AK6","AK7","INDEX60","INDEX50","BLST")
		);		

		
			
		
		$this->master_array["KAV 2009 8.0.0[454 to 506]"]=array(
		"VERSION"=>"2009",
		"BUILD"=>"8.0.0[454?506]",
		"COMPONENTS"=>array("UPDATER","KDBI386","AH2I386","AH2X64","APU","BB","BB2","CORE","BLST","KAV8EXEC","INFO","RT","WAVI386","SSA")
		);	
	
		$this->master_array["KIS 2009 8.0.0[454 to 506]"]=array(
		"VERSION"=>"2009",
		"BUILD"=>"8.0.0[454?506]",
		//AS
		"COMPONENTS"=>array("UPDATER","KDBI386","ADBU","AH2I386","AH2X64","AS","APU","BB","BB2","CORE","BLST","KAV8EXEC","INFO","RT","WAVI386","PAS","PARCTL","SSA","ASTRM","HIPS")
		);	
		
$this->master_array["KIS 2009 8.0.0.357"]=array(
		"VERSION"=>"2009",
		"BUILD"=>"8.0.0[454?506]",
		//AS
		"COMPONENTS"=>array("UPDATER","KDBI386","ADB","AH2I386","AH2X64","AS","AP","BB","BB2","CORE","BLST","KAV8EXEC","INFO","RT","WAVI386","PAS","PARCTL","SSA","ASTRM","HIPS")
		);			

$this->master_array["Kav For Proxy Server 5.5-[41 to 51]"]=array(
		"VERSION"=>"5.5",
		"BUILD"=>"5.5-[41-51]",
		"COMPONENTS"=>array("UPDATER","AVS","RT","AK6","INDEX5")
		);			

$this->master_array["KAV for Sendmail with Milter API"]=array(
		"VERSION"=>"5.6",
		"BUILD"=>"5.5-[41-51]",
		"COMPONENTS"=>array("UPDATER","AVS","RT","AK6","INDEX5")
		);	

$this->master_array["KAV for Samba Server 5.5[9 to 14]"]=array(
		"VERSION"=>"5.6",
		"BUILD"=>"5.5-[41-51]",
		"COMPONENTS"=>array("UPDATER","AVS","RT","AK6","INDEX5")
		);

$this->master_array["KAV for Unix Mail Server 5.5.33"]=array(
		"VERSION"=>"5.5",
		"BUILD"=>"5.5.33",
		"COMPONENTS"=>array("UPDATER","AVS","RT","AK6","INDEX5")
		);

$this->master_array["KAV for Linux Mail Server 5.6[26.3 to 38.1]"]=array(
		"VERSION"=>"5.5",
		"BUILD"=>"5.5.33",
		"COMPONENTS"=>array("UPDATER","AVS","AVS_OLD","RT","AK6","INDEX5")
		);				

$this->master_array["KAV for Unix File Server and WKS 5.5"]=array(
		"VERSION"=>"5.5",
		"BUILD"=>"5.5.33",
		"COMPONENTS"=>array("UPDATER","AVS","RT","AK6","INDEX50")
		);		
$this->master_array["KAV for Linux File Server and WKS 5.7[17 to 26]"]=array(
		"VERSION"=>"5.5",
		"BUILD"=>"5.5.33",
		"COMPONENTS"=>array("UPDATER","AVS","RT","AK6","INDEX50")
		);	
		
$this->master_array["KAV for NetWare 5.7.3"]=array(
		"VERSION"=>"5.5",
		"BUILD"=>"5.5.33",
		"COMPONENTS"=>array("AVS","RT","AK6","INDEX50")
		);		

$this->master_array["KAV for ISA 2000 EE 5.6.991"]=array(
		"VERSION"=>"5.5",
		"BUILD"=>"5.5.33",
		"COMPONENTS"=>array("UPDATER","AVS","RT","AK6","INDEX50")
		);	

$this->master_array["KAV for ISA 2000 SE 5.1.658"]=array(
		"VERSION"=>"5.5",
		"BUILD"=>"5.5.33",
		"COMPONENTS"=>array("UPDATER","AVS","RT","AK6","INDEX50")
		);

$this->master_array["KAV for ISA 2004 EE 5.5.1128"]=array(
		"VERSION"=>"5.5",
		"BUILD"=>"5.5.33",
		"COMPONENTS"=>array("UPDATER","AVS","RT","AK6","INDEX50")
		);

$this->master_array["KAV for ISA 2004 SE 5.5.1137"]=array(
		"VERSION"=>"5.5",
		"BUILD"=>"5.5.33",
		"COMPONENTS"=>array("UPDATER","AVS","RT","AK6","INDEX50")
		);	

$this->master_array["KAV for ISA 2004&2006 EE 5.6.1158"]=array(
		"VERSION"=>"5.5",
		"BUILD"=>"5.5.33",
		"COMPONENTS"=>array("UPDATER","AVS","RT","AK6","INDEX50")
		);				
$this->master_array["KAV for ISA 2004&2006 SE 5.6.1181"]=array(
		"VERSION"=>"5.5",
		"BUILD"=>"5.5.33",
		"COMPONENTS"=>array("UPDATER","AVS","RT","AK6","INDEX50")
		);	
		
$this->master_array["KS for Exchange 5.5.1388.0"]=array(
		"VERSION"=>"5.5",
		"BUILD"=>"5.5.33",
		"COMPONENTS"=>array("UPDATER","AVS","BLST","KAS20EXCH","RT","AK6","INDEX50")
		);			
		
$this->master_array["KS for Exchange 5.5.1354.0"]=array(
		"VERSION"=>"5.5",
		"BUILD"=>"5.5.33",
		"COMPONENTS"=>array("UPDATER","AVS","BLST","KAS20EXCH","RT","AK6","INDEX50")
		);
		
$this->master_array["KAV for Exchange 5.5.1185.0"]=array(
		"VERSION"=>"5.5",
		"BUILD"=>"5.5.33",
		"COMPONENTS"=>array("UPDATER","AVS","RT","AK6","INDEX50")
		);

$this->master_array["KS for Exchange 6.0.744"]=array(
		"VERSION"=>"5.5",
		"BUILD"=>"5.5.33",
		"COMPONENTS"=>array("UPDATER","AVS","AVS_OLD","CORE","RT","AK6","INDEX50")
		);	

$this->master_array["KS for Exchange 6.0.715"]=array(
		"VERSION"=>"5.5",
		"BUILD"=>"5.5.33",
		"COMPONENTS"=>array("UPDATER","AVS","AVS_OLD","CORE","RT","AK6","INDEX60")
		);	

$this->master_array["KAV for Windows File Server 5.0[77 to 78]"]=array(
		"VERSION"=>"5.5",
		"BUILD"=>"5.5.33",
		"COMPONENTS"=>array("UPDATER","AVS","RT","AK6","INDEX50")
		);	

$this->master_array["KAV for Windows Workstations 5.0[225 to 228]"]=array(
		"VERSION"=>"5.5",
		"BUILD"=>"5.5.33",
		"COMPONENTS"=>array("UPDATER","AVS","RT","AK6","INDEX50")
		);				

$this->master_array["KAV for Windows Workstations 5.0[676 to 712]"]=array(
		"VERSION"=>"5.5",
		"BUILD"=>"5.5.33",
		"COMPONENTS"=>array("UPDATER","AH","AVS","RT","AK6","INDEX50")
		);	
		
		
$this->master_array["Administration Kit 6.0[1405 to 1710]"]=array(
		"VERSION"=>"5.5",
		"BUILD"=>"5.5.33",
		"COMPONENTS"=>array("UPDATER","AH","RT","AK6","INDEX50","INDEX60")
		);			

$this->master_array["KAV for Windows Server EE 6.0.2.551"]=array(
		"VERSION"=>"5.5",
		"BUILD"=>"5.5.33",
		"COMPONENTS"=>array("UPDATER","AVS","BLST","KAV6WSEE","RT","INDEX60","INDEX60")
		);	
$this->master_array["KAV for Windows Server EE 6.0.1.511"]=array(
		"VERSION"=>"5.5",
		"BUILD"=>"5.5.33",
		"COMPONENTS"=>array("UPDATER","AVS","BLST","KAV6WSEE","RT","INDEX60","INDEX60")
		);	
		
$this->master_array["KAV for Windows Server EE 6.0.0.454"]=array(
		"VERSION"=>"5.5",
		"BUILD"=>"5.5.33",
		"COMPONENTS"=>array("UPDATER","AVS","BLST","KAV6WSEE","RT","INDEX60","INDEX60")
		);
		
$this->master_array["KAV for Windows Server 6.0.3[830 to 837]"]=array(
		"VERSION"=>"5.5",
		"BUILD"=>"5.5.33",
		"COMPONENTS"=>array("UPDATER","AVS","CORE","KAV2006EXEC","NEWS","RT","AK6","INDEX60")
		);	
		
$this->master_array["KAV for Windows Server 6.0.2[678 to 690]"]=array(
		"VERSION"=>"5.5",
		"BUILD"=>"5.5.33",
		"COMPONENTS"=>array("UPDATER","AVS","CORE","KAV2006EXEC","NEWS","RT","AK6","INDEX60")
		);				

$this->master_array["KAV for Windows Workstation 6.0.3[830 to 837]"]=array(
		"VERSION"=>"5.5",
		"BUILD"=>"5.5.33",
		//AS
		"COMPONENTS"=>array("UPDATER","AVS","ADB","AHI386","AHX64","AS","AP","BB","BB2","CORE","KAV2006EXEC","NEWS","RM","RT","WM","AK6","INDEX60")
		);

$this->master_array["KAV for Windows Workstation 6.0.2[678 to 690]"]=array(
		"VERSION"=>"5.5",
		"BUILD"=>"5.5.33",
		//AS
		"COMPONENTS"=>array("UPDATER","AVS","ADB","AHI386","AHX64","AP","AS","BB","BB2","CORE","KAV2006EXEC","NEWS","RM","RT","WM","AK6","INDEX60")
		);			

$this->master_array["KIS 6.0.2[614 to 621]"]=array(
		"VERSION"=>"5.5",
		"BUILD"=>"5.5.33",
		//AS
		"COMPONENTS"=>array("UPDATER","AVS","AS","ADB","AHI386","AHX64","AP","BB","BB2","CORE","KAV2006EXEC","NEWS","RM","RT","WM","AK6","INDEX60")
		);			
		
		
$this->master_array["KAV 6.0.2[614 to 621]"]=array(
		"VERSION"=>"5.5",
		"BUILD"=>"5.5.33",
		"COMPONENTS"=>array("UPDATER","AVS","AHI386","AHX64","BB","BB2","CORE","KAV2006EXEC","NEWS","RM","RT","WM","AK6","INDEX60")
		);			
		
	
	
$this->master_array["KIS 7.0.1[321 to 325]"]=array(
		"VERSION"=>"5.5",
		"BUILD"=>"5.5.33",
		//AS
		"COMPONENTS"=>array("UPDATER","AVS","ADB","AS","AHI386","AHX64","AP","BB","BB2","BLST","KAV2006EXEC","NEWS","RM","RT","WAI386","EMUI386","PAS","PCDB","AK6","INDEX60")
		);

$this->master_array["KAV 7.0.1[321 to 325]"]=array(
		"VERSION"=>"5.5",
		"BUILD"=>"5.5.33",
		"COMPONENTS"=>array("UPDATER","AVS","AHI386","AHX64","BB","BB2","BLST","KAV2006EXEC","NEWS","RM","RT","WAI386","EMUI386","AK6","INDEX60")
		);	

$this->master_array["KIS 7.0.0[124 to 125]"]=array(
		"VERSION"=>"5.5",
		"BUILD"=>"5.5.33",
		//AS
		"COMPONENTS"=>array("UPDATER","AVS","ADB","AS","AHI386","AHX64","AP","BB","BB2","KAV2006EXEC","NEWS","RM","RT","WAI386","EMUI386","PAS","PCDB","AK6","INDEX60")
		);

$this->master_array["KAV 7.0.0[124 to 125]"]=array(
		"VERSION"=>"5.5",
		"BUILD"=>"5.5.33",
		"COMPONENTS"=>array("UPDATER","AVS","AHI386","AHX64","BB","BB2","KAV2006EXEC","NEWS","RM","RT","WAI386","EMUI386","AK6","INDEX60")
		);	

		
$this->master_array["KIS 2009 8.0.0.357"]=array(
		"VERSION"=>"5.5",
		"BUILD"=>"5.5.33",
		//AS
		"COMPONENTS"=>array("UPDATER","KDBI386","AHI386","ADB","AS","AH2I386","AH2X64","AP","BB","BB2","CORE","BLST","KAV8EXEC","INFO","RT","WAVI386","PAS","PARCTL","SSA","ASTRM","HIPS")
		);

$this->master_array["KAV 2009 8.0.0.357"]=array(
		"VERSION"=>"5.5",
		"BUILD"=>"5.5.33",
		"COMPONENTS"=>array("UPDATER","KDBI386","AHI386","AH2I386","AH2X64","AP","BB","BB2","CORE","BLST","KAV8EXEC","INFO","RT","WAVI386","SSA")
		);

$this->master_array["KIS 2009 8.0.0[454 to 506]"]=array(
		"VERSION"=>"5.5",
		"BUILD"=>"5.5.33",
		//AS
		"COMPONENTS"=>array("UPDATER","KDBI386","AHI386","ADBU","AS","AH2I386","AH2X64","APU","BB","BB2","CORE","BLST","KAV8EXEC","INFO","RT","WAVI386","PAS","PARCTL","SSA","ASTRM","HIPS")
		);

$this->master_array["KAV 2009 8.0.0[454 to 506]"]=array(
		"VERSION"=>"5.5",
		"BUILD"=>"5.5.33",
		//AS
		"COMPONENTS"=>array("UPDATER","KDBI386","AHI386","AH2I386","AS","AH2X64","APU","BB","BB2","CORE","BLST","KAV8EXEC","INFO","RT","WAVI386","SSA")
		);			
			
		
	}	


}



class UpdateUtility{
	var $main_array=array();
	
	function UpdateUtility(){
		include_once(dirname(__FILE__)."/class.sockets.inc");
		$sock=new sockets();
		$datas=$sock->GET_INFO("KUpdateUtility",true);
		if($datas==null){
			$sock=new sockets();
			$datas=$sock->getFrameWork("cmd.php?UpdateUtilitySource=yes");
			$sock->SaveConfigFile($datas,"KUpdateUtility");
		}
		$ini=new Bs_IniHandler();
		$ini->loadString($datas);
		$this->main_array=$ini->_params;
		if($this->main_array["DirectoriesSettings"]["UpdatesFolder"]==null){
			$this->main_array["DirectoriesSettings"]["UpdatesFolder"]="/usr/share/UpdateUtility";
		}
		
		if($this->main_array["DirectoriesSettings"]["TempFolder"]==null){
			$this->main_array["DirectoriesSettings"]["TempFolder"]="/usr/share/UpdateUtility/tmp";
		}	

		$this->main_array["ReportSettings"]["SaveReportsToFile"]="true";
		$this->main_array["ReportSettings"]["AppendToPreviousFile"]="false";
		
	}
	
	function Save(){
		$sock=new sockets();
		$ini=new Bs_IniHandler();
		$ini->_params=$this->main_array;
		$sock->SaveConfigFile($ini->toString(),"KUpdateUtility");
		
	}
	
	
}