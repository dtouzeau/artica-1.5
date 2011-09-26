<?php
include_once(dirname(__FILE__) . '/class.ldap.inc');
include_once(dirname(__FILE__) . '/class.mysql.inc');
class fetchmail{
	var $array_servers;
	var $GLOBAL_CONFIG;
	var $fetchmailrc;
	var $FetchGetLive;
	var $FetchmailPoolingTime;
	var $FetchmailDaemonPostmaster;
	var $ISPDB=array();
	var $dap;
	
	
	function fetchmail(){
		$ldap=new clladp();
		$this->ISPS();
		if(is_file(dirname(__FILE__) . '/settings.inc')){include(dirname(__FILE__) . '/settings.inc');
		$this->GLOBAL_CONFIG=$_GLOBAL;}else{echo "unable to stat " . dirname(__FILE__) . "/settings.inc";}	
		$this->CreateDefaults();
		
	}
	
	
	
	private function ISPS(){
		
	$this->ISPDB["ARRAY_IMAP_ISP"]=array(
		"Neuf"=>"imap.neuf.fr",
		"Alice"=>"imap.aliceadsl.fr",
		"Aol France"=>"imap.fr.aol.com",
		"Club Internet"=>"imap.club-internet.fr",
		"Free"=>"imap.free.fr",
		"Gmail"=>"imap.gmail.com",
		"La Poste"=>"imap.laposte.net",
		"Noos"=>"imap.noos.fr",
		"Orange"=>"imap.orange.fr",
		"One And One"=>"imap.1and1.fr",
		"Bouygues"=>"imap.bbox.fr");
	
	
	$this->ISPDB["ARRAY_POP_ISP"]=array(
		"Neuf"=>"pop.neuf.fr",
		"Alice"=>"pop.aliceadsl.fr",
		"Darty Box"=>"pop.dbmail.com",
		"Magic OnLine"=>"pop2.magic.fr",
		"Free"=>"pop.free.fr",
		"Tele2"=>"pop.tele2.fr",
		"Wanadoo"=>"pop.wanadoo.fr",
		"Yahoo"=>"pop.mail.yahoo.fr",
		"Aol"=>"pop.aol.com",
		"Altitude Telecom"=>"pop.altitudetelecom.fr",
		"La Poste"=>"pop.laposte.net",
		"Noos"=>"pop.noos.fr",
		"Orange"=>"pop.orange.fr",
		"Bouygues"=>"pop.bbox.fr",
		);
	}
	
	
	function CreateDefaults(){
		$sock=new sockets();
		$this->FetchmailPoolingTime=$sock->GET_INFO("FetchmailPoolingTime");
		$this->FetchmailDaemonPostmaster=$sock->GET_INFO("FetchmailDaemonPostmaster");
		if($this->FetchmailPoolingTime==null){$this->FetchmailPoolingTime=600;}
		if($this->FetchmailDaemonPostmaster==null){$this->FetchmailDaemonPostmaster="root";}
		}
	
	
	function Save(){
		$sock=new sockets();
		$sock->SET_INFO("FetchmailPoolingTime",$this->FetchmailPoolingTime);
		$sock->SET_INFO("FetchmailDaemonPostmaster",$this->FetchmailDaemonPostmaster);
		$sock->getFrameWork('cmd.php?restart-fetchmail=yes');	
	}
	
	
	
}


class Fetchmail_settings{
	var $main_array;
	function parse_config($datas){
		$tb=explode("\n",$datas);
		//writelogs("spliting=\"" . count($tb) . "\" lines",__CLASS__.'/'.__FUNCTION__,__FILE__);
		while (list ($num, $line) = each ($tb) ){
			
			if(trim($line)==null){unset($tb[$num]);}
			if(preg_match("#^([\s]+)[a-zA-Z0-9]+.*#",$line,$re)){
				$tb[$num]=str_replace($re[1],'',$tb[$num]);
				$tb[$num]=str_replace('"','',$tb[$num]);
				
				}
			
		}
		reset($tb);
		
		while (list ($num, $line) = each ($tb) ){
			if(trim($line)==null){continue;}
			$line=$line." ";
			if(preg_match('#(poll\s+|server|skip|defaults)#i',$line)){$i=$i+1;}
				$arr[$i]["disabled"]=false;
				$arr[$i]["multidrop"]=false;
				
				if(preg_match('#poll\s+(.+?)\s+#',$line,$re)){
					$arr[$i]["poll"]=trim($re[1]);
				}
				if(preg_match('#proto\s+([a-z0-9]+)#',$line,$re)){$arr[$i]["proto"]=$re[1];}				
				if(preg_match('#aka\s+(.+)#',$line,$re)){$arr[$i]["aka"]=$re[1];}	
				if(preg_match('#via\s+(.+)#',$line,$re)){$arr[$i]["poll"]=$re[1];}								
				if(preg_match('#user\s+([a-zA-Z0-9\.\-_@]+)#',$line,$re)){$arr[$i]["user"]=$re[1];}					
				if(preg_match('#(pass|password)\s+(.+?)\s+#',$line,$re)){$arr[$i]["pass"]=$re[2];}									
				if(preg_match('#is\s+(.+?)\s+#',$line,$re)){$arr[$i]["is"]=$re[1];}													
				if(preg_match('#\s+ssl\s+#',$line,$re)){$arr[$i]["ssl"]=true;}						
				if(preg_match('#\s+keep\s+#',$line,$re)){$arr[$i]["keep"]=true;}																	
				if(preg_match('#nokeep#',$line,$re)){$arr[$i]["nokeep"]=false;}					
				if(preg_match('#fetchall#',$line,$re)){$arr[$i]["fetchall"]=true;}					
				if(preg_match('#nofetchall#',$line,$re)){$arr[$i]["fetchall"]=false;}					
				if(preg_match('#options\s+(.+)#',$line,$re)){$arr[$i]["options"][]=$re[1];}	
				if(preg_match('#port\s+([0-9]+)#',$line,$re)){$arr[$i]["port"]=$re[1];}	
				if(preg_match('#interval\s+([0-9]+)#',$line,$re)){$arr[$i]["interval"]=$re[1];}
				if(preg_match('#tracepolls#i',$line,$re)){$arr[$i]["tracepolls"]=true;}					
				if(preg_match('#folder\s+(.+)#',$line,$re)){$arr[$i]["folder"]=$re[1];}	
				if(preg_match('#disabled#',$line,$re)){$arr[$i]["disabled"]=true;}
				if(preg_match('#multidrop#',$line,$re)){$arr[$i]["multidrop"]=true;}
				if(preg_match('#sslcertck#',$line,$re)){$arr[$i]["sslcertck"]=true;}
				if(preg_match('#dropdelivered#',$line,$re)){$arr[$i]["dropdelivered"]=true;}
				
				
				if(preg_match('#sslfingerprint\s+\'(.+?)\'\s+#',$line,$re)){$arr[$i]["sslfingerprint"]=$re[1];}		
				
				
			
				}
		
	 $this->main_array=$arr;
	 return $arr;
	}
	
		
	function compile($array){
		if(!isset($GLOBALS["FetchMailGLobalDropDelivered"])){
			$sock=new sockets();
			$GLOBALS["FetchMailGLobalDropDelivered"]=$sock->GET_INFO("FetchMailGLobalDropDelivered");
			if(!is_numeric($GLOBALS["FetchMailGLobalDropDelivered"])){$GLOBALS["FetchMailGLobalDropDelivered"]=0;}
			
		}
		
		
		if(!is_array($array)){return false;}
		if($array["poll"]==null){return false;}
		if($array["proto"]==null){return false;}
		
		if($GLOBALS["FetchMailGLobalDropDelivered"]==1){$array["dropdelivered"]=1;}
		
		if($array["fetchall"]<>null){
			$fetchall="\n\tfetchall";
		}
		
		if($array["proto"]=="httpp"){
			$array["proto"]="pop3";
		}
		
		if($array["port"]<>null){
			$port="port {$array["port"]}";
		}
		
		if($array["aka"]<>null){
			$aka="\n\taka {$array["aka"]}";
		}		
		
		if($array["ssl"]<>null){
			$ssl="\n\tssl ";
		}	
		
		if($array["timeout"]<>null){
			$timeout="\n\ttimeout {$array["timeout"]}";
		}
		
		if($array["folder"]<>null){
			$folder="\n\tfolder {$array["folder"]}";
		}				
		
		if($array["tracepolls"]<>null){
			$tracepolls="\n\ttracepolls";
		}
		if($array["interval"]<>null){
			$interval="\n\\tinterval {$array["interval"]}";
		}		
		if($array["keep"]<>null){
			$keep="\n\tkeep ";
		}
		if($array["disabled"]==0){
			$disabled="\n\tdisabled";
		}
		if($array["multidrop"]==1){
			$multidrop="\n\tmultidrop";
		}		
		if($array["sslfingerprint"]<>null){
			$sslfingerprint="\n\tsslfingerprint '{$array["sslfingerprint"]}'";
		}
		if($array["sslfingerprint"]<>null){
			$sslcertck="\n\tsslcertck";
		}		
		
		if($array["dropdelivered"]==1){
			$dropdelivered="\n\tdropdelivered is {$array["is"]} here";
		}
		
		
		$line="poll {$array["poll"]} \n\t$tracepolls\n\tproto {$array["proto"]} $port\n\tuser {$array["user"]}\n\t pass {$array["pass"]}\n\tis {$array["is"]}$dropdelivered$aka$folder$ssl$fetchall$interval$timeout$keep$disabled$multidrop$sslfingerprint$sslcertck";
		return $line;
	}
	
	
	function EditRule($POSTED_ARRAY,$ID){
		writelogs("ID=$ID",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$ID=trim($ID);
		if($ID==null){$ID=0;}
		if($ID<0){$ID=0;}
		
		if($POSTED_ARRAY["uid"]==null){
			if($POSTED_ARRAY["is"]<>null){
				$ldap=new clladp();
				$POSTED_ARRAY["uid"]=$ldap->uid_from_email($POSTED_ARRAY["is"]);
			
		}}
	
		if(isset($POSTED_ARRAY["poll"])){
			$sqa["`poll`"]=$POSTED_ARRAY["poll"];
			$sq[]=" `poll`='{$POSTED_ARRAY["poll"]}'";
		}
		$md5=md5($POSTED_ARRAY["uid"].$POSTED_ARRAY["is"].$POSTED_ARRAY["user"].$POSTED_ARRAY["poll"].$POSTED_ARRAY["proto"]);
		if(isset($POSTED_ARRAY["uid"])){$sqa["`uid`"]=$POSTED_ARRAY["uid"];}
		if(isset($POSTED_ARRAY["proto"])){$sqa["`proto`"]=$POSTED_ARRAY["proto"];$sq[]=" `proto`='{$POSTED_ARRAY["proto"]}'";}
		if(isset($POSTED_ARRAY["timeout"])){$sqa["`timeout`"]=$POSTED_ARRAY["timeout"];$sq[]=" `timeout`='{$POSTED_ARRAY["timeout"]}'";}
		if(isset($POSTED_ARRAY["interval"])){$sqa["`interval`"]=$POSTED_ARRAY["interval"];$sq[]=" `interval`='{$POSTED_ARRAY["interval"]}'";}
		if(isset($POSTED_ARRAY["user"])){$sqa["`user`"]=$POSTED_ARRAY["user"];$sq[]=" `user`='{$POSTED_ARRAY["user"]}'";}
		if(isset($POSTED_ARRAY["pass"])){$sqa["`pass`"]=$POSTED_ARRAY["pass"];$sq[]=" `pass`='{$POSTED_ARRAY["pass"]}'";}
		if(isset($POSTED_ARRAY["is"])){$sqa["`is`"]=$POSTED_ARRAY["is"];$sq[]=" `is`='{$POSTED_ARRAY["is"]}'";}
		if(isset($POSTED_ARRAY["enabled"])){$sqa["`enabled`"]=$POSTED_ARRAY["enabled"];$sq[]=" `enabled`='{$POSTED_ARRAY["enabled"]}'";}
		if(isset($POSTED_ARRAY["aka"])){$sqa["`aka`"]=$POSTED_ARRAY["aka"];$sq[]=" `aka`='{$POSTED_ARRAY["aka"]}'";}
		if(isset($POSTED_ARRAY["multidrop"])){$sqa["`multidrop`"]=$POSTED_ARRAY["multidrop"];$sq[]=" `multidrop`='{$POSTED_ARRAY["multidrop"]}'";}
		if(isset($POSTED_ARRAY["tracepolls"])){$sqa["`tracepolls`"]=$POSTED_ARRAY["tracepolls"];$sq[]=" `tracepolls`='{$POSTED_ARRAY["tracepolls"]}'";}
		if(isset($POSTED_ARRAY["ssl"])){$sqa["`ssl`"]=$POSTED_ARRAY["ssl"];$sq[]=" `ssl`='{$POSTED_ARRAY["ssl"]}'";}
		if(isset($POSTED_ARRAY["fetchall"])){$sqa["`fetchall`"]=$POSTED_ARRAY["fetchall"];$sq[]=" `fetchall`='{$POSTED_ARRAY["fetchall"]}'";}
		if(isset($POSTED_ARRAY["keep"])){$sqa["`keep`"]=$POSTED_ARRAY["keep"];$sq[]=" `keep`='{$POSTED_ARRAY["keep"]}'";}	
		if(isset($POSTED_ARRAY["nokeep"])){$sqa["`nokeep`"]=$POSTED_ARRAY["nokeep"];$sq[]=" `nokeep`='{$POSTED_ARRAY["nokeep"]}'";}
		if(isset($POSTED_ARRAY["smtp_host"])){$sqa["`smtp_host`"]=$POSTED_ARRAY["smtp_host"];$sq[]=" `smtp_host`='{$POSTED_ARRAY["smtp_host"]}'";}
		if(isset($POSTED_ARRAY["sslfingerprint"])){$sqa["`sslfingerprint`"]=$POSTED_ARRAY["sslfingerprint"];$sq[]=" `sslfingerprint`='{$POSTED_ARRAY["sslfingerprint"]}'";}
		if(isset($POSTED_ARRAY["sslcertck"])){$sqa["`sslcertck`"]=$POSTED_ARRAY["sslcertck"];$sq[]=" `sslcertck`='{$POSTED_ARRAY["sslcertck"]}'";}
		if(isset($POSTED_ARRAY["dropdelivered"])){$sqa["`dropdelivered`"]=$POSTED_ARRAY["dropdelivered"];$sq[]=" `dropdelivered`='{$POSTED_ARRAY["dropdelivered"]}'";}

		
		
		
		$sqa["`md5`"]=$md5;
		
		while (list ($num, $line) = each ($sqa) ){
			$fields[]="$num";
			$values[]="'$line'";
			$sql_add="INSERT INTO fetchmail_rules (".implode(",",$fields).") VALUES(".implode(",",$values).")";
		}
		
		$sql_upd="UPDATE fetchmail_rules SET ".implode(", ",$sq). " WHERE ID='$ID'";
		
		if($ID>0){
			writelogs("Editing rule $ID",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$sql=$sql_upd;
		}else{
			$sql=$sql_add;
		}
		
		writelogs($sql,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$q=new mysql();
		if(!$q->QUERY_SQL($sql,"artica_backup")){
			writelogs("ERROR: ".$q->mysql_error,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			return false;
		}
			$sock=new sockets();
			$sock->getFrameWork('cmd.php?restart-fetchmail=yes');
			if($sock->GET_INFO("ArticaMetaEnabled")==1){$sock->getFrameWork("cmd.php?artica-meta-fetchmail-rules=yes");}
				
			return true;	
		
	}
	
	
	function AddRule($POSTED_ARRAY){
		
		$md5=md5($POSTED_ARRAY["uid"].$POSTED_ARRAY["is"].$POSTED_ARRAY["user"].$POSTED_ARRAY["poll"].$POSTED_ARRAY["proto"]);
		$sql="INSERT INTO fetchmail_rules (`uid`,`is`,`user`,`pass`,`poll`,`proto`,`keep`,`nokeep`,`enabled`,`md5`,`smtp_host`)
		VALUES('{$POSTED_ARRAY["uid"]}',
		'{$POSTED_ARRAY["is"]}',
		'{$POSTED_ARRAY["user"]}',
		'{$POSTED_ARRAY["pass"]}',
		'{$POSTED_ARRAY["poll"]}',
		'{$POSTED_ARRAY["proto"]}',
		'{$POSTED_ARRAY["keep"]}',
		'{$POSTED_ARRAY["nokeep"]}',
		 1,
		 '$md5',
		 '{$POSTED_ARRAY["smtp_host"]}')";
		
		$q=new mysql();
		if(!$q->QUERY_SQL($sql,"artica_backup")){
			echo $q->mysql_error;
			return false;
		}
			$sock=new sockets();
			if($sock->GET_INFO("ArticaMetaEnabled")==1){$sock->getFrameWork("cmd.php?artica-meta-fetchmail-rules=yes");}
			$sock->getFrameWork('cmd.php?restart-fetchmail=yes');
		return true;
	}
	
	function LoadUsersRules($uid){
		$sql="SELECT * FROM fetchmail_rules WHERE uid='$uid'";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		$array=array();
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$array[$ligne["ID"]]=array(
				"is"=>$ligne["is"],
				"user"=>$ligne["user"],
				"pass"=>$ligne["pass"],
				"poll"=>$ligne["poll"],
				"proto"=>$ligne["proto"],
				"keep"=>$ligne["keep"],
				"enabled"=>$ligne["enabled"],
				"tracepolls"=>$ligne["tracepolls"],
				"ssl"=>$ligne["ssl"],
				"fetchall"=>$ligne["fetchall"],
				"port"=>$ligne["port"],
				"timeout"=>$ligne["timeout"],
				"interval"=>$ligne["interval"],
				"aka"=>$ligne["aka"],
				"multidrop"=>$ligne["multidrop"],
				"nokeep"=>$ligne["nokeep"],
				"smtp_host"=>$ligne["smtp_host"],
				"sslfingerprint"=>$ligne["sslfingerprint"],
				"sslcertck"=>$ligne["sslcertck"],
				"dropdelivered"=>$ligne["dropdelivered"]
			);
		}
		
		return $array;
	}
	
	function LoadAllRules(){
		$sql="SELECT * FROM fetchmail_rules ORDER BY uid";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		$array=array();
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$array[$ligne["ID"]]=array(
				"is"=>$ligne["is"],
				"user"=>$ligne["user"],
				"pass"=>$ligne["pass"],
				"poll"=>$ligne["poll"],
				"proto"=>$ligne["proto"],
				"keep"=>$ligne["keep"],
				"enabled"=>$ligne["enabled"],
				"tracepolls"=>$ligne["tracepolls"],
				"ssl"=>$ligne["ssl"],
				"fetchall"=>$ligne["fetchall"],
				"port"=>$ligne["port"],
				"timeout"=>$ligne["timeout"],
				"interval"=>$ligne["interval"],
				"aka"=>$ligne["aka"],
				"uid"=>$ligne["uid"],
				"multidrop"=>$ligne["multidrop"],
				"nokeep"=>$ligne["nokeep"],
				"smtp_host"=>$ligne["smtp_host"],
				"sslfingerprint"=>$ligne["sslfingerprint"],
				"sslcertck"=>$ligne["sslcertck"],
				"dropdelivered"=>$ligne["dropdelivered"]
			);
		}
		
		return $array;
	}	
	
	function LoadRule($ID){
		$sql="SELECT * FROM fetchmail_rules WHERE ID='$ID'";
		$q=new mysql();
		$array=array();
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
			return array(
				"is"=>$ligne["is"],
				"user"=>$ligne["user"],
				"pass"=>$ligne["pass"],
				"poll"=>$ligne["poll"],
				"proto"=>$ligne["proto"],
				"keep"=>$ligne["keep"],
				"enabled"=>$ligne["enabled"],
				"tracepolls"=>$ligne["tracepolls"],
				"ssl"=>$ligne["ssl"],
				"fetchall"=>$ligne["fetchall"],
				"port"=>$ligne["port"],
				"timeout"=>$ligne["timeout"],
				"interval"=>$ligne["interval"],
				"aka"=>$ligne["aka"],
				"multidrop"=>$ligne["multidrop"],
				"nokeep"=>$ligne["nokeep"],
				"smtp_host"=>$ligne["smtp_host"],
				"sslfingerprint"=>$ligne["sslfingerprint"],
				"dropdelivered"=>$ligne["dropdelivered"]
			);
		
		
		return $array;
	}

	function DeleteRule($ID,$uid){
		if($uid==null){echo "Uid is null, aborting\n";return false;}
		if(!is_numeric($ID)){echo "ID is corrupted, aborting\n";return false;}
		$sql="DELETE FROM fetchmail_rules WHERE ID='$ID' and uid='$uid'";
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo $q->mysql_error;return false;}
		$sock=new sockets();
		if($sock->GET_INFO("ArticaMetaEnabled")==1){$sock->getFrameWork("cmd.php?artica-meta-fetchmail-rules=yes");}
		$sock->getFrameWork('cmd.php?restart-fetchmail=yes');
		return true; 
	}
	
}





