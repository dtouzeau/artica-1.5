<?php
include_once(dirname(__FILE__) . '/class.ldap.inc');
include_once(dirname(__FILE__) . '/class.mysql.inc');
include_once(dirname(__FILE__) . '/class.system.network.inc');




class assp_multi{
	var $ou;
	var $AsspEnabled=0;
	var $q;
	var $MAIN_ARRAY=array();
	var $running;
	
	function assp_multi($ou,$nocheck=0){
		$this->ou=$ou;	
		if($nocheck==0){
			$q=new mysql();
			$this->q=$q;
			$this->AsspEnabled=$this->GET_VALUE("ASSPEnabled");
			$this->CheckConfig();
		}
		$this->running=$this->isRunning();
	}
	
	private function isRunning(){
		$instance=str_replace(" ","-",$this->ou);
		$pid=trim(@file_get_contents("/usr/share/assp-$instance/pid"));
		if(is_file("/proc/$pid/exe")){
			return true;
		}
	}
	
	public function WriteConf(){
		
		while (list ($num, $val) = each ($this->MAIN_ARRAY) ){
			$conf[]="$num:=$val";
			
		}
		$instance=str_replace(" ","-",$this->ou);
		@file_put_contents("/usr/share/assp-$instance/assp.cfg",implode("\n",$conf));
		
	}
	
	private function CheckConfig(){
		$this->LoadConf();
		$this->inet_interfaces_load();
		$this->RBL();
		$this->WhiteListConnections();
		$this->MAIN_ARRAY["AsADaemon"]=1;
		$this->MAIN_ARRAY["AutoReloadCfg"]=1;
		$this->MAIN_ARRAY["myName"]=$this->MyHostname();
		
		
	}
	
	public function GET_VALUE($key){
		$sql="SELECT `value` FROM postfix_multi WHERE `key`='$key' AND `ou`='$this->ou' LIMIT 0,1";
		$ligne=mysql_fetch_array($this->q->QUERY_SQL($sql,"artica_backup"));	
		return $ligne["value"];
	}
	
	
	public function SET_VALUE($key,$value){
		$sql="SELECT `ID` FROM postfix_multi WHERE `key`='$key' AND `ou`='$this->ou' LIMIT 0,1";
		$ligne=mysql_fetch_array($this->q->QUERY_SQL($sql,"artica_backup"));	
		if($ligne["ID"]==null){
			$sql="INSERT INTO postfix_multi  (`ou`,`key`,`value`) VALUES('$this->ou','$key','$value')";
		}else{
			$sql="UPDATE postfix_multi SET `value`='$value' WHERE ID={$ligne["ID"]}";
		}
		$this->q->QUERY_SQL($sql,"artica_backup");
		if(!$this->q->ok){
			echo "{$this->q->mysql_error}\nSET_VALUE($key,$value)";
		}else{
			$this->Apply();
		}
	}
	
	private function Apply(){
			$sock=new sockets();
			$sock->getFrameWork("cmd.php?postfix-multi-configure-ou=$this->ou");	
	}
	
	private function LoadConf(){
		$AsspConfig=$this->GET_VALUE("AsspConfig");
		
		if($AsspConfig==null){
			$sock=new sockets();
			$AsspConfig=$sock->getFrameWork("cmd.php?assp-multi-load-config=".base64_encode($this->ou));
		}
		
		$this->MAIN_ARRAY=unserialize(base64_decode($AsspConfig));
		if(count($this->MAIN_ARRAY<10)){
			$AsspConfig=$sock->getFrameWork("cmd.php?assp-multi-load-config=". base64_encode("DEFAULT"));
		}
		$this->MAIN_ARRAY=unserialize(base64_decode($AsspConfig));
	}
	
	
	
	private function inet_interfaces_load(){
		$sql="SELECT nic,ipaddr FROM nics_virtuals WHERE org='$this->ou'";
		$q=new mysql();
		$ip=new networking();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			if($ligne["ipaddr"]==null){
				if($ligne["nic"]<>null){
					$ligne["ipaddr"]=$net->array_TCP[$ligne["nic"]];
				}
			}
			if($ligne["ipaddr"]<>null){
				$listen[]=$ligne["ipaddr"].":25";
				$send[]=$ligne["ipaddr"].":6000";
				$web[]=$ligne["ipaddr"].":55555";
			}			
		}
		
		
		if(is_array($listen)){
			$this->MAIN_ARRAY["listenPort"]=implode("|",$listen);
			$this->MAIN_ARRAY["smtpDestination"]=implode("|",$send);
			$this->MAIN_ARRAY["webAdminPort"]=implode("|",$web);
		}
		
	}
	
	private function WhiteListConnections(){
		$sql="SELECT value FROM postfix_multi WHERE `key`='ip_white_listed' AND `ou`='$this->ou' ORDER BY ID DESC;";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			if($ligne["value"]==null){continue;}
			$arr[]=$ligne["value"];
		}
		
		if(is_array($arr)){
			$value=implode("|",$arr);
			$value_text=implode("\n",$arr);
		}else{
			$value=null;
		}
		$this->MAIN_ARRAY["noRBL"]=$value;
		$this->MAIN_ARRAY["noBlockingIPs"]="127.0.0|192.168|$value";
		$this->MAIN_ARRAY["noDelay"]="file:files/nodelay.txt";
		$instance=str_replace(" ","-",$this->ou);
		@file_put_contents("/usr/share/assp-$instance/files/nodelay.txt",implode("\n",$newRBL));
		
	}
	

	private function RBL(){
		$this->MAIN_ARRAY["RBLServiceProvider"]="file:files/dnsbls.txt";
		$sql="SELECT ValueTEXT FROM postfix_multi WHERE `key`='smtpd_client_restrictions' AND `ou`='$this->ou'";
		$q=new mysql();
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
		$mainconf=unserialize(base64_decode($ligne["ValueTEXT"]));
		if(is_array($mainconf)){$EnablePostfixAntispamPack=$array["EnablePostfixAntispamPack"];}
		$sql="SELECT * FROM postfix_multi WHERE `key`='RBL' AND `ou`='$this->ou' ORDER BY ID DESC;";
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			if($ligne["value"]<>null){$RBL[$ligne["value"]]=$ligne["value"];}
		}
		if($EnablePostfixAntispamPack==1){
			$RBL["bl.spamcop.net"]="bl.spamcop.net";
	        $RBL["b.barracudacentral.org"]="b.barracudacentral.org";
	        $RBL["zen.spamhaus.org"]="zen.spamhaus.org";
	        $RBL["psbl.surriel.com"]="psbl.surriel.com";
	        $RBL["ix.dnsbl.manitu.net"]="ix.dnsbl.manitu.net";		
		}
		
		if(is_array($RBL)){
			while (list ($num, $val) = each ($RBL) ){
				if($num==null){continue;}
				$newRBL[]="$num=>1";
			}
			if(is_array($newRBL)){
				$newRBL[]="";
				$instance=str_replace(" ","-",$this->ou);
				@file_put_contents("/usr/share/assp-$instance/files/dnsbls.txt",implode("\n",$newRBL));
			}
			
		}
		
		
	}
	
	private function MyHostname(){
		$sql="SELECT `value` FROM postfix_multi WHERE `key`='myhostname' AND `ou`='$this->ou' LIMIT 0,1;";
		$q=new mysql();
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));	
		writelogs("\"{$ligne["value"]}\"",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);	
		if(trim($ligne["value"])==null){
			$users=new usersMenus();
			writelogs("FQDN=\"{$users->fqdn}\"",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);	
			return $users->fqdn;
		}
		return $ligne["value"];
	}	
	
	
}