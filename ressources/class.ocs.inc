<?php
include_once(dirname(__FILE__).'/class.templates.inc');
include_once(dirname(__FILE__).'/class.ldap.inc');
include_once(dirname(__FILE__).'/class.mysql.inc');

	class ocs{
		var $mac=null;
		var $HARDWARE_ID=0;
		var $ComputerName;
		var $ComputerOS;
		var $ComputerIP;
		var $PROLOG_FREQ=0;
		
		function ocs($mac=null){
			if($mac=="unknown"){$mac=null;}
			if($mac<>null){
				$this->mac=trim($mac);
				$this->Loadinfos();
			}
		}
		
		private function Loadinfos(){
			$this->mac=strtoupper($this->mac);
			$sql="SELECT HARDWARE_ID FROM networks WHERE UPPER(MACADDR)='$this->mac'";
			
			$q=new mysql();
			$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'ocsweb'));
			if($ligne["HARDWARE_ID"]>0){
				$this->HARDWARE_ID=$ligne["HARDWARE_ID"];
				
			}else{
				writelogs("$sql -> NULL",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			}
			
		}
		
		public function LoadParams(){
			if($this->HARDWARE_ID==0){return null;}
			$q=new mysql();
			$sql="SELECT * FROM hardware WHERE ID='$this->HARDWARE_ID'";
			$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'ocsweb'));
			$this->ComputerName=$ligne["NAME"];
			$this->ComputerOS=$ligne["OSNAME"];
			$this->ComputerIP=$ligne["IPADDR"];
		}
		
		
		private function COMPUTER_ID_FROM_IP_INTERNAL($IPADDR){
			$q=new mysql();
			$sql="SELECT ID FROM hardware WHERE IPADDR='$IPADDR'";
			$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"ocsweb"));
			if(!is_numeric($ligne["ID"])){$ligne["ID"]=0;}	
			return $ligne["ID"];
		}
		
		
		public function EditComputer(){
			if($this->ComputerName==null){return;}
			if($this->mac==null){return;}
			$MAC=strtolower($this->mac);
			$NAME=strtolower($this->ComputerName);
			$IPADDR=$this->ComputerIP;
			$ComputerOS_edit=null;$ComputerOS_add_field=null;$ComputerOS_add_datd=null;
			
			$date=date('Y-m-d H:i:s');
			$q=new mysql();
			$sql="SELECT ID FROM networks WHERE UPPER(MACADDR)='$this->mac'";
			$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"ocsweb"));
			if(!$q->ok){if($GLOBALS["VERBOSE"]){writelogs($q->mysql_error,__CLASS__."/".__FILE__,__FILE__,__LINE__);}return false;}	
			if(!is_numeric($ligne["ID"])){$ligne["ID"]=0;}
			
			if($this->ComputerOS<>null){
				$this->ComputerOS=addslashes($this->ComputerOS);
				$ComputerOS_add_field=",OSNAME";
				$ComputerOS_add_data=",'$this->ComputerOS'";
				$ComputerOS_edit=",OSNAME='$this->ComputerOS'";
			}
			
			if($ligne["ID"]==0){
				$HARDWARE_ID=$this->COMPUTER_ID_FROM_IP_INTERNAL($IPADDR);
				if($HARDWARE_ID==0){
					$sql="INSERT INTO hardware (NAME,IPADDR,LASTDATE$ComputerOS_add_field) VALUES('$NAME','$IPADDR','$date'$ComputerOS_add_data)";
					$q->QUERY_SQL($sql,"ocsweb");
					if(!$q->ok){if($GLOBALS["VERBOSE"]){writelogs($q->mysql_error,__CLASS__."/".__FILE__,__FILE__,__LINE__);}return false;}
					$HARDWARE_ID=$this->COMPUTER_ID_FROM_IP_INTERNAL($IPADDR);
					if($HARDWARE_ID==0){return false;}
				}
				
				$sql="INSERT INTO networks (HARDWARE_ID,IPADDRESS,MACADDR) VALUES('$HARDWARE_ID','$IPADDR','$MAC')";
				$q->QUERY_SQL($sql,"ocsweb");
				if(!$q->ok){if($GLOBALS["VERBOSE"]){writelogs($q->mysql_error,__CLASS__."/".__FILE__,__FILE__,__LINE__);}return false;}
			}else{
				$NET_ID=$ligne["ID"];
				$sql="SELECT HARDWARE_ID FROM networks WHERE ID='{$ligne["ID"]}'";
				$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"ocsweb"));
				$HARDWARE_ID=$ligne["HARDWARE_ID"];
				
				if($IPADDR<>null){
					$sql="UPDATE networks SET IPADDRESS='$IPADDR' WHERE ID='$NET_ID'";
					$q->QUERY_SQL($sql,"ocsweb");
					if(!$q->ok){if($GLOBALS["VERBOSE"]){writelogs($q->mysql_error,__CLASS__."/".__FILE__,__FILE__,__LINE__);}return false;}
				}
				if($IPADDR<>null){$IPADDR_SQL=",IPADDR='$IPADDR'";}
				$sql="UPDATE hardware SET NAME='$NAME',LASTDATE='$date'$IPADDR_SQL$ComputerOS_edit WHERE ID='$HARDWARE_ID'";
				$q->QUERY_SQL($sql,"ocsweb");
				if(!$q->ok){if($GLOBALS["VERBOSE"]){writelogs($q->mysql_error,__CLASS__."/".__FILE__,__FILE__,__LINE__);}return false;}			
			}
		
		}
		
		public function GET_SERVER_SETTINGS(){
			$sql="SELECT * FROM config";
			$q=new mysql();
			$results=$q->QUERY_SQL($sql,"ocsweb");
			while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
				$array[$ligne["NAME"]]=$ligne["IVALUE"];
			}
			
			return $array;
		}
		
		public function SET_SERVER_SETTINGS($key,$value){
			$sql="UPDATE config SET IVALUE='$value' WHERE NAME='$key'";
			$q=new mysql();
			$q->QUERY_SQL($sql,"ocsweb");
			if(!$q->ok){echo __FUNCTION__.":".$q->mysql_error;return;}
			return true;
		}
		
		
		public function BuildFirstInfos(){
			if($this->HARDWARE_ID==0){
				writelogs("HARDWARE_ID=0",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				return null;
			}
			
			$html="
			<table style='width:100%'>
			<tr>
			<td valign='top' width=1%><img src='img/folder-network-64.png'></td>
			<td valign='top'>
			<H3>{tcp_main_settings}</H3>";
			$q=new mysql();
			$sql="SELECT * FROM networks WHERE HARDWARE_ID='$this->HARDWARE_ID'";
			$results=$q->QUERY_SQL($sql,'ocsweb');
			while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
				
			$ligne["DESCRIPTION"]=utf8_encode($ligne["DESCRIPTION"]);
					
			$tr[]="
			<table style='width:292px;margin:2px;border:1px solid #CCCCCC'>
			<tr>
				<td class=legend nowrap width=1%>{tcp_address}:</td>
				<td><span style='font-size:11px;font-weight:bold'>{$ligne["IPADDRESS"]}</span></td>
			</tr>
			<tr>
				<td class=legend nowrap width=1%>{netmask}:</td>
				<td><span style='font-size:11px;font-weight:bold'>{$ligne["IPMASK"]}</span></td>
			</tr>
			<tr>
				<td class=legend nowrap width=1%>{gateway}:</td>
				<td><span style='font-size:11px;font-weight:bold'>{$ligne["IPGATEWAY"]}</span></td>
			</tr>	
			<tr>
				<td class=legend nowrap width=1% valign='top'>{NIC_NAME}:</td>
				<td><span style='font-size:11px;font-weight:bold' valign='top'>{$ligne["DESCRIPTION"]}</span></td>
			</tr>								
			</table>";
			
			}
			
$tables[]="<table style='width:100%'><tr>";
$t=0;
while (list ($key, $line) = each ($tr) ){
		$line=trim($line);
		if($line==null){continue;}
		$t=$t+1;
		$tables[]="<td valign='top'>$line</td>";
		if($t==2){$t=0;$tables[]="</tr><tr>";}
		}

if($t<2){
	for($i=0;$i<=$t;$i++){
		$tables[]="<td valign='top'>&nbsp;</td>";				
	}
}				
			
			
			
	$html=$html.implode("\n",$tables)."</td>
			</tr>
			</table>";
			
			return $this->SystemInfos()."<hr>".$html."<hr>".$this->BuidStorage()."<hr>".$this->BuidMemory();
		}
		
		private function BuidStorage(){
			$sql="SELECT * FROM storages WHERE HARDWARE_ID='$this->HARDWARE_ID'";
			$q=new mysql();
			$results=$q->QUERY_SQL($sql,"ocsweb");
			$html="<table style='width:99%'>";
			while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
				
				$DISKSIZE=$ligne["DISKSIZE"];
				if($DISKSIZE==0){continue;}
				$name=$ligne["NAME"];
				$MANUFACTURER=$ligne["MANUFACTURER"];
				$DESCRIPTION=$ligne["DESCRIPTION"];
				$TYPE=$ligne["TYPE"];
				
				$tooltip="{manufacturer}:<b>$MANUFACTURER</b><br>{description}:<b>$DESCRIPTION</b><br>{type}:$TYPE";
				if(trim($name)==null){
					if($MANUFACTURER<>null){$name=$MANUFACTURER;}
				}
				$DISKSIZE_TOT=$DISKSIZE_TOT+$DISKSIZE;
				$DISKSIZE=FormatBytes(($DISKSIZE*1024));
				if(trim($name)==null){$name="unknown";}
				$name=texttooltip("{name}:$name",$tooltip,null,null,1);
				
				$html=$html . "
				<tr ". CellRollOver().">
					<td width=1%><img src='img/mailbox_hd.gif'></td>
					<td><span style='font-size:11px;font-weight:bold'>$name</span>
					<td><span style='font-size:11px;font-weight:bold'>{$DISKSIZE}</span>
				</tr>
				
				";
				
			}
			$DISKSIZE_TOT=FormatBytes(($DISKSIZE_TOT*1024));
			$html=$html."
			<tr ". CellRollOver().">
					<td width=1%>&nbsp;</td>
					<td><span style='font-size:11px;font-weight:bold;'>&nbsp;</span>
					<td><div style='font-size:13px;font-weight:bold;border-top:2px solid #CCCCCC;width:35%'>{$DISKSIZE_TOT}</div></td>
				</tr>
			</table>";
			$page="			<table style='width:99%'>
			<tr>
			<td valign='top' width=1%><img src='img/hd-64.png'></td>
			<td valign='top'>
			<H3>{internal_hard_drives}</H3>
			$html
			</td>
			</tr>
			</table>";
			
			return $page;
			
			
		}
	    private function BuidMemory(){
			$sql="SELECT * FROM memories WHERE HARDWARE_ID='$this->HARDWARE_ID'";
			$q=new mysql();
			$results=$q->QUERY_SQL($sql,"ocsweb");
			$html="<table style='width:100%'>";
			while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
				
				$DISKSIZE=$ligne["CAPACITY"];
				$name=$ligne["CAPTION"];
				$DESCRIPTION=$ligne["DESCRIPTION"];
				$TYPE=$ligne["TYPE"];
				if($DISKSIZE==0){continue;}
				$DISKSIZE_TOT=$DISKSIZE_TOT+$DISKSIZE;
				$DISKSIZE=FormatBytes(($DISKSIZE*1024));
				
				$tooltip="<br>{description}:<b>$DESCRIPTION</b><br>{type}:$TYPE";
				
				$name=texttooltip($name,$tooltip,null,null,1);
				
				$html=$html . "
				<tr ". CellRollOver().">
					<td width=1%><img src='img/fw_bold.gif'></td>
					<td><span style='font-size:11px;font-weight:bold'>$name</span>
					<td><span style='font-size:11px;font-weight:bold'>{$DISKSIZE}</span>
				</tr>
				
				";
				
			}
			$DISKSIZE_TOT=FormatBytes(($DISKSIZE_TOT*1024));
			$html=$html."
			<tr ". CellRollOver().">
					<td width=1%>&nbsp;</td>
					<td><span style='font-size:11px;font-weight:bold'>&nbsp;</span>
					<td><div style='font-size:13px;font-weight:bold;border-top:2px solid #CCCCCC;width:35%'>{$DISKSIZE_TOT}</div>
				</tr>			
			</table>";
			$page="			<table style='width:99%'>
			<tr>
			<td valign='top' width=1%><img src='img/bg_memory-64.png'></td>
			<td valign='top'>
			<H3>{memory}</H3>
			$html
			</td>
			</tr>
			</table>";
			
			return RoundedLightWhite($page);
			
			
		}
		
		private function SystemInfos(){
			if($this->HARDWARE_ID==0){return null;}
			$q=new mysql();
			$sql="SELECT * FROM hardware WHERE ID='$this->HARDWARE_ID'";
			$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'ocsweb'));
			$computeros=utf8_encode("{$ligne["OSNAME"]} ({$ligne["OSVERSION"]}) ({$ligne["OSCOMMENTS"]})");
			$html="
			<table style='width:99%'>
			<tr>
			<td valign='top' width=1%><img src='img/64-computer.png'></td>
			<td valign='top'>
			
			<table style='width:100%'>
			<tr>
				<td class=legend nowrap width=1% valign='top'>{computer_name}:</td>
				<td><span style='font-size:11px;font-weight:bold'>{$ligne["NAME"]}</span></td>
			</tr>
			<tr>
				<td class=legend nowrap width=1% valign='top'>{WORKGROUP}:</td>
				<td><span style='font-size:11px;font-weight:bold'>{$ligne["WORKGROUP"]}</span></td>
			</tr>
			<tr>
				<td class=legend nowrap width=1% valign='top'>{ComputerOS}:</td>
				<td><span style='font-size:11px;font-weight:bold'>$computeros</span></td>
			</tr>	
			<tr>
				<td class=legend nowrap width=1% valign='top'>{ComputerCPU}:</td>
				<td><span style='font-size:11px;font-weight:bold'>{$ligne["PROCESSORT"]} ({$ligne["PROCESSORN"]})</span></td>
			</tr>								
			</table>
			</tr>
			</table>";			
			return RoundedLightWhite($html);
			
		}
		
		function PACKAGE_ID_FROM_FILEID($FILEID){
			if($_SESSION["PACKAGE_ID_FROM_FILEID"][$FILEID]>0){return $_SESSION["PACKAGE_ID_FROM_FILEID"][$FILEID];}
			if($FILEID<1){return 0;}
			$sql="SELECT ID FROM download_enable WHERE FILEID='$FILEID'";
			$q=new mysql();
			$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'ocsweb'));
			$ID=$ligne["ID"];
			if($ID==null){return 0;}
			$_SESSION["PACKAGE_ID_FROM_FILEID"][$FILEID]=$ID;
			return $ID;
		}
		
		function PACKAGE_NAME_FROM_FILEID($FILEID){
			$sql="SELECT NAME FROM download_available WHERE FILEID=$FILEID";
			$q=new mysql();
			$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'ocsweb'));
			return $ligne["NAME"];
			
		}
		
		function COMPUTER_SEARCH_QUERY($pattern=null,$nolimit=0){
				$pattern=str_replace("*","%",$pattern);
				$pattern=$pattern."%";
				$pattern=str_replace("%%","%",$pattern);
				$osc=new ocs();
				
				if($nolimit==0){$LIMIT="LIMIT 0,50";}
				
				return "SELECT hardware.LASTCOME,hardware.OSNAME,hardware.PROCESSORT,
				networks.MACADDR,networks.IPADDRESS,networks.HARDWARE_ID,hardware.ID,hardware.NAME,hardware.IPADDR,hardware.IPSRC FROM networks,hardware WHERE 1 AND
				(networks.HARDWARE_ID=hardware.ID AND hardware.NAME LIKE '$pattern') 
				OR (networks.HARDWARE_ID=hardware.ID AND hardware.IPADDR LIKE '$pattern')
				OR (networks.HARDWARE_ID=hardware.ID AND hardware.IPSRC LIKE '$pattern')
				ORDER BY hardware.LASTCOME DESC
				$LIMIT
				";			
			
		}
		
		FUNCTION GET_HARDWARE_ID_FROM_MAC($MAC){
			$MAC=strtoupper(trim($MAC));
			if($_SESSION[__FUNCTION__][$MAC]<>null){return $_SESSION[__FUNCTION__][$MAC];}
			$sql="SELECT HARDWARE_ID FROM networks WHERE UPPER(MACADDR)='$MAC'";
			$q=new mysql();
			$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'ocsweb'));
			if(!$q->ok){writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
			if($ligne["HARDWARE_ID"]>0){
				$_SESSION[__FUNCTION__][$MAC]=$ligne["HARDWARE_ID"];
			}
			return $ligne["HARDWARE_ID"];
		}
		
		FUNCTION GET_UID_FROM_HARDWARE_ID($ID){
			$sql="SELECT UPPER(MACADDR) as MACADDR FROM networks WHERE HARDWARE_ID=$ID";
			if($_SESSION[__FUNCTION__][$ID]<>null){return $_SESSION[__FUNCTION__][$ID];}
			
			$q=new mysql();
			$results=$q->QUERY_SQL($sql,"ocsweb");
			
			if(!$q->ok){
				writelogs("$sql $q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			}
			
			$cmp=new computers();
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				if(trim($ligne["MACADDR"])==null){
					writelogs("MACADDR -> NULL",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
					writelogs("$sql $q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
					continue;
				}
				$uid=trim($cmp->ComputerIDFromMAC($ligne["MACADDR"]));
				if($uid<>null){
					$_SESSION[__FUNCTION__][$ID]=$uid;
					writelogs("{$ligne["MACADDR"]} -> $uid",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
					return $uid;}
				writelogs("{$ligne["MACADDR"]} not in LDAP",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			}
			
		}
		
		
		FUNCTION GET_AGENTVER($MAC){
			
			$HARDWARE_ID=$this->GET_HARDWARE_ID_FROM_MAC($MAC);
			if($HARDWARE_ID<1){return null;}
			if($_SESSION[__FUNCTION__][$MAC]<>null){return $_SESSION[__FUNCTION__][$MAC];}
			$sql="SELECT USERAGENT FROM hardware WHERE ID=$HARDWARE_ID";
			$q=new mysql();
			$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'ocsweb'));
			if(!$q->ok){writelogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
			if(trim($ligne["USERAGENT"])<>null){$_SESSION[__FUNCTION__][$MAC]=$ligne["USERAGENT"];}
			return $ligne["USERAGENT"];
			
		}
		
		FUNCTION REMOTE_AGENT_INSTALL_PROGRESS($uid){
			$sql="SELECT ID,PROGRESS,pourcent FROM deploy_tasks WHERE computer_id='$uid' AND TASK_TYPE='OCS_AGENT'";
			$q=new mysql();
			$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
			return array($ligne["ID"],$ligne["pourcent"],$ligne["PROGRESS"]);
		}
		
		/*
		 * return tableau HARDWARE_ID=>1 d'un fileid spécifié.
		 */
		
		function PACKAGE_HASH_AFFECTED_COMPUTERS($FILEID){
			if(isset($_SESSION["PACKAGE_HASH_AFFECTED_COMPUTERS"])){return $_SESSION["PACKAGE_HASH_AFFECTED_COMPUTERS"];}
			$ID=$this->PACKAGE_ID_FROM_FILEID($FILEID);
			$sql="SELECT HARDWARE_ID FROM devices WHERE IVALUE=$ID AND NAME='DOWNLOAD'";
			$q=new mysql();
			$results=$q->QUERY_SQL($sql,"ocsweb");
			while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
				$_SESSION["PACKAGE_HASH_AFFECTED_COMPUTERS"][$ligne["HARDWARE_ID"]]=1;
				
			}
			
			return $_SESSION["PACKAGE_HASH_AFFECTED_COMPUTERS"];
			
		}
		
		function PACKAGE_IS_AFFECTED($FILEID,$HARDWARE_ID){
			$ID=$this->PACKAGE_ID_FROM_FILEID($FILEID);
			$sql="SELECT NAME FROM devices WHERE HARDWARE_ID='$HARDWARE_ID' AND IVALUE='$ID' AND NAME='DOWNLOAD'";
			$q=new mysql();
			$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'ocsweb'));
			if($ligne["NAME"]=='DOWNLOAD'){return true;}
		}
		
		function PACKAGE_AFFECT($FILEID,$HARDWARE_ID){
			if($this->PACKAGE_IS_AFFECTED($FILEID,$HARDWARE_ID)){return;}
			$ID=$this->PACKAGE_ID_FROM_FILEID($FILEID);
			$sql="INSERT INTO devices (NAME,HARDWARE_ID,IVALUE) VALUES('DOWNLOAD',$HARDWARE_ID,$ID)";
			$q=new mysql();
			$q->QUERY_SQL($sql,"ocsweb");
			writelogs($sql,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			if(!$q->ok){echo __FUNCTION__.":".$q->mysql_error;return;}
			$_SESSION["PACKAGE_HASH_AFFECTED_COMPUTERS"][$HARDWARE_ID]=1;
			
		}
		function PACKAGE_DESAFFECT($FILEID,$HARDWARE_ID){
			if(!$this->PACKAGE_IS_AFFECTED($FILEID,$HARDWARE_ID)){return;}
			$ID=$this->PACKAGE_ID_FROM_FILEID($FILEID);
			$sql="DELETE FROM devices WHERE HARDWARE_ID='$HARDWARE_ID' AND IVALUE='$ID' AND NAME='DOWNLOAD'";
			$q=new mysql();
			$q->QUERY_SQL($sql,"ocsweb");
			if(!$q->ok){echo __FUNCTION__.":".$q->mysql_error;return;}
			unset($_SESSION["PACKAGE_HASH_AFFECTED_COMPUTERS"][$HARDWARE_ID]);
			
		}
		
		function PACKAGE_DOWNLOAD_INFOS_HASH($FILEID){
			$sql="SELECT * FROM download_enable WHERE FILEID='$FILEID'";
			$q=new mysql();
			$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'ocsweb'));
			return $ligne;
		}
		
	function PACKAGE_AFFECT_COUNT_COMPUTERS($FILEID){
		$ID=$this->PACKAGE_ID_FROM_FILEID($FILEID);
		$sql="SELECT COUNT(HARDWARE_ID) as tcount FROM devices WHERE IVALUE='$ID' AND NAME='DOWNLOAD'";
		$q=new mysql();
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'ocsweb'));
		writelogs($sql."={$ligne["tcount"]}",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		return $ligne["tcount"];
	}

	function PACKAGE_UPDATE_ALL_RESSOURCES(){
			$sock=new sockets();
			$OCSCertInfos=unserialize(base64_decode($sock->GET_INFO("OCSCertInfos")));
			$servername=$OCSCertInfos["OCSCertServerName"];
			$domainname=$OCSCertInfos["OCSCertDomainName"];
			$OCSWebPort=$sock->GET_INFO("OCSWebPort");
			$OCSWebPortSSL=$sock->GET_INFO("OCSWebPortSSL");
			if($OCSWebPort==null){$OCSWebPort=9080;}
			if($OCSWebPortSSL==null){$OCSWebPortSSL=$OCSWebPort+50;}	
			if($servername==null){return;}
			if($domainname<>null){
				$servername=$servername.".$domainname";
			}
			
			$sql="UPDATE download_enable SET INFO_LOC='$servername:$OCSWebPortSSL/download',PACK_LOC='$servername:$OCSWebPort/download'";
			$q=new mysql();
			$q->QUERY_SQL($sql,"ocsweb");
			if(!$q->ok){echo __CLASS__.'/'.__FUNCTION__."\n".$q->mysql_error;}
		
	}
	
	function SSL_SERVER_NAME(){
			$sock=new sockets();
			$OCSCertInfos=unserialize(base64_decode($sock->GET_INFO("OCSCertInfos")));
			$servername=$OCSCertInfos["OCSCertServerName"];
			$domainname=$OCSCertInfos["OCSCertDomainName"];
			$users=new usersMenus();
			if($servername==null){$servername=$users->hostname;}
			if($domainname<>null){
				if(preg_match("#^(.+?)\.#",$servername,$re)){$servername=$re[1];}
				$servername=$servername.".$domainname";
			}
			
			return $servername;
		
	}
	
	function INJECT_COMPUTER_TOLDAP($MACADDR){
		$q=new mysql();	
		$MACADDR=trim($MACADDR);
		if($MACADDR==null){
		$GLOBALS["INJECT_COMPUTER_TOLDAP"][]="INJECT_COMPUTER_TOLDAP:: MAC ADDRESS IS NULL";return ;}
		
		if($GLOBALS["MEM"][md5(__CLASS__.__FUNCTION__)][md5($MACADDR)]){return true;}
		$sql="SELECT HARDWARE_ID FROM networks WHERE MACADDR='$MACADDR'";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"ocsweb"));
		
		if($ligne["HARDWARE_ID"]<1){
			$GLOBALS["INJECT_COMPUTER_TOLDAP"][]="INJECT_COMPUTER_TOLDAP:: MACADDR <1";
			echo "{corrupted_database}";
			return false;
		}
		$HARDWARE_ID=$ligne["HARDWARE_ID"];
		$sql="SELECT * FROM hardware WHERE ID='{$HARDWARE_ID}'";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"ocsweb"));
		$cp=new computers();
		$cp->ComputerIP=$ligne["IPSRC"];
		$cp->ComputerMacAddress=$MACADDR;
		$cp->ComputerRealName=$ligne["NAME"];
		if($ligne["OSNAME"]<>null){$cp->ComputerOS=$ligne["OSNAME"];}
		$cp->ComputerCPU=$ligne["PROCESSORT"];
		$cp->uid=$ligne["NAME"];
		
		
		
		writelogs("Adding computer MAC:$MACADDR IPSRC:{$ligne["IPSRC"]} NAME:{$ligne["NAME"]} in LDAP",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		
		$sql="SELECT *  FROM bios WHERE HARDWARE_ID='$HARDWARE_ID'";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"ocsweb"));
		$cp->ComputerMachineType=trim("{$ligne["SMODEL"]} {$ligne["SMANUFACTURER"]}");
		
		
		$val=$cp->Add();
		$GLOBALS["INJECT_COMPUTER_TOLDAP"][]="INJECT_COMPUTER_TOLDAP:: ->Add=$val";
		if($val){$GLOBALS["MEM"][md5(__CLASS__.__FUNCTION__)][md5($MACADDR)]=true;}
		return $val;		
	}
	
	function GET_OCSSERVER_URI(){
		$sock=new sockets();
		$users=new usersMenus();
		$ocswebservername=$sock->GET_INFO("ocswebservername");
		$OCSImportToLdap=$sock->GET_INFO("OCSImportToLdap");
		$UseFusionInventoryAgents=$sock->GET_INFO("UseFusionInventoryAgents");
		if($UseFusionInventoryAgents==null){$UseFusionInventoryAgents=1;}
		if($OCSImportToLdap==null){$OCSImportToLdap=60;}

	
		$OCSWebPort=$sock->GET_INFO("OCSWebPort");
		$OCSWebPortSSL=$sock->GET_INFO("OCSWebPortSSL");
		if($OCSWebPort==null){$OCSWebPort=9080;}
		if($OCSWebPortSSL==null){$OCSWebPortSSL=$OCSWebPort+50;}
		if($ocswebservername==null){$ocswebservername=$users->hostname;}
		if($UseFusionInventoryAgents==1){
			$http_suffix="https";
			$OCSWebPortLink=$OCSWebPortSSL;
			}else{
				$http_suffix="http";
				$OCSWebPortLink=$OCSWebPort;
			}
		return "$http_suffix://$ocswebservername:$OCSWebPortLink/ocsinventory";
	}
	
	function COUNT_HARDWARE_SOFTWARES($PUBLISHER=null,$NAME=null){
	$NAME=trim($NAME);
	$md5=md5("$PUBLISHER$NAME");
	
	
	if($_SESSION["COUNT_HARDWARE_SOFTWARES"][$md5]>0){return $_SESSION["COUNT_HARDWARE_SOFTWARES"][$md5];}
	
	$q=new mysql();
	$PUBLISHER=$q->mysql_real_escape_string2($PUBLISHER);
	$NAME=$q->mysql_real_escape_string2($NAME);
	$sql="SELECT PUBLISHER,NAME,HARDWARE_ID FROM softwares 
	WHERE PUBLISHER='$PUBLISHER' AND NAME='$NAME' GROUP BY PUBLISHER,NAME,HARDWARE_ID";
	if($NAME==null){
		$sql="SELECT PUBLISHER,HARDWARE_ID FROM softwares WHERE PUBLISHER='$PUBLISHER' GROUP BY PUBLISHER,HARDWARE_ID";
	}
	
	
	$results=$q->QUERY_SQL($sql,"ocsweb");
	if(!$q->ok){
		writelogs($q->mysql_error,__FUNCTION__,__FILE__,__LINE__);
	}
	$num=@mysql_num_rows($results);
	$_SESSION["COUNT_HARDWARE_SOFTWARES"][$md5]=$num;
	return $num;
}	

}

?>