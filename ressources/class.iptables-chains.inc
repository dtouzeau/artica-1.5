<?php
include_once(dirname(__FILE__)."/class.mysql.inc");
include_once(dirname(__FILE__) . '/class.ldap.inc');

class iptables_chains{
	var $id;
	var $servername;
	var $serverip;
	var $service;
	var $localport;
	var $disable;
	var $events_number;
	var $iptables_id;
	var $rule_string;
	var $EventsToAdd;
	var $isRoot=false;
	var $error="";
	var $rulemd5=null;
	
	function iptables_chains($rulemd5=null){
		if(posix_getuid()==0){$this->isRoot=true;}
		if($rulemd5<>null){$this->rulemd5=$rulemd5;}
		
	}
	
public	function add_chain(){
		if($this->rule_string==null){$this->rule_string=time();}
		if(strlen($this->rulemd5)<10){$rulemd5=md5($this->rule_string);}else{$rulemd5=$this->rulemd5;}
		$date=date('Y-m-d H:i:s');
		if($this->serverip==null){return;}
		if($this->servername==null){
			if(preg_match("#^[0-9\.]+$#", $this->serverip)){
				$this->servername=gethostbyaddr($this->serverip);
			}
		}
		
		
		if(strpos($this->localport,",")>0){
			$multiple_port=$this->localport;
			$this->localport=-1;
		}
		
		
		
		$sql="INSERT IGNORE INTO iptables(
			service,
			servername,
			serverip,
			local_port,
			disable,
			events_number,
			rule_string,
			rulemd5,
			flux,
			events_block,
			date_created,
			multiples_ports )
		VALUES(
			'MANUAL',
			'$this->servername',
			'$this->serverip',
			'$this->localport',
			'0',
			'$this->events_number',
			'$this->rule_string',
			'$rulemd5','INPUT',
			'$this->EventsToAdd',
			'$date',
			'$multiple_port')";
		
		
		$ID=$this->postfix_chain_get_id($rulemd5);
		if($ID>0){
			$sql="UPDATE iptables SET servername='$this->servername',serverip='$this->serverip',
			local_port=$this->localport,multiples_ports='$multiple_port' WHERE rulemd5='$rulemd5'";
			
		}	
		
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){
			echo "$q->mysql_error\n$sql\n";
			$this->events("$q->mysql_error");
			return false;
		}
		if($GLOBALS["VERBOSE"]){echo "OK\n";}
		return true;
		
		}	
	
	
public	function addPostfix_chain(){
		if($this->rule_string==null){$this->rule_string=time();}
		$rulemd5=md5($this->rule_string);
		$date=date('Y-m-d H:i:s');
		
		$ID=$this->postfix_chain_get_id($rulemd5);
		if($ID>0){
			if($GLOBALS["VERBOSE"]){echo "$this->serverip: Already exists ID $ID\n";}
			return true;
		}
		
		$sql="INSERT IGNORE INTO iptables(
			servername,
			serverip,
			local_port,
			disable,
			events_number,
			rule_string,
			rulemd5,
			flux,events_block,date_created)
		VALUES(
			'$this->servername',
			'$this->serverip',
			'25',
			'0',
			'$this->events_number',
			'$this->rule_string',
			'$rulemd5','INPUT',
			'$this->EventsToAdd','$date')";
		
		$q=new mysql();
		writelogs($sql,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){
			if($GLOBALS["VERBOSE"]){echo "$q->mysql_error\n$sql\n";}
			$this->events("$q->mysql_error");
			return false;
		}
		if($GLOBALS["VERBOSE"]){echo "OK\n";}
		return true;
		
		}
		
public	function addSSHD_chain(){
		if($this->rule_string==null){$this->rule_string=time();}
		$rulemd5=md5($this->rule_string);
		$date=date('Y-m-d H:i:s');
		
		$ID=$this->postfix_chain_get_id($rulemd5);
		if($ID>0){
			if($GLOBALS["VERBOSE"]){echo "$this->serverip: Already exists ID $ID\n";}
			return true;
		}
		
		$sql="INSERT IGNORE INTO iptables(
			servername,
			serverip,
			local_port,
			disable,
			events_number,
			rule_string,
			rulemd5,
			flux,events_block,date_created)
		VALUES(
			'$this->servername',
			'$this->serverip',
			'22',
			'0',
			'$this->events_number',
			'$this->rule_string',
			'$rulemd5','INPUT',
			'$this->EventsToAdd','$date')";
		
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){
			if($GLOBALS["VERBOSE"]){echo "$q->mysql_error\n$sql\n";}
			$this->events("$q->mysql_error");
			return false;
		}
		if($GLOBALS["VERBOSE"]){echo "OK\n";}
		return true;
		
		}		
		
private function postfix_chain_get_id($rulemd5){
	$sql="SELECT ID FROM iptables WHERE rulemd5='$rulemd5'";
	$q=new mysql();
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	if($GLOBALS["VERBOSE"]){echo "\"$sql\"={$ligne["ID"]}\n";}
	if($ligne["ID"]==null){$ligne["ID"]=0;}
	return $ligne["ID"];
}
		
private function events($text){
			if(!$this->isRoot){writelogs("iptables_chains: $text",__CLASS__,__FILE__);return null;}
	
		$pid=getmypid();
		$date=date('Y-m-d H:i:s');
		$logFile=$_GET["filelogs"];
		if(!is_file($logFile)){echo "iptables_chains::events() unable to stat log file\n";return null;}
		$size=filesize($logFile);
		if($size>1000000){unlink($logFile);}
		$f = @fopen($logFile, 'a');
		@fwrite($f, "$date [$pid] $text\n");
		@fclose($f);	
		}
		
public function deletePostfix_chain($rulemd5){
	$sql="DELETE FROM iptables WHERE rulemd5='$rulemd5'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){$this->events("$q->mysql_error");$this->error=$q->mysql_error;return false;}
	return true;
	}
	
public function deleteAllPostfix_chains(){
	$sql="DELETE FROM iptables WHERE local_port=25 AND flux='INPUT'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){$this->events("$q->mysql_error");
		$this->error=$q->mysql_error;
		return false;
	}
	return true;
	}	
	
function loadPostfix_chains($limit=0,$search=null){
	$q=new mysql();
	if($search<>null){
		if(strpos($search,"*")==0){$search=$search."*";}
		$search=str_replace("*","%",$search);
		$sql_search=" AND (servername LIKE '$search' OR serverip LIKE '$search') ";
		
	}
	
	$sql_count="SELECT COUNT(*) AS tcount FROM iptables WHERE local_port=25 AND flux='INPUT'{$sql_search}";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql_count,"artica_backup"));
	$max=$ligne["tcount"];
	if($limit==null){$limit=0;}
	
	$sql="SELECT * FROM iptables WHERE local_port=25 AND flux='INPUT' {$sql_search}ORDER BY ID DESC LIMIT $limit,50";
	$q=new mysql();	
	$results=$q->QUERY_SQL($sql,"artica_backup");
	return array($max,$results);
		
	//-j LOG --log-prefix "TCP LOGDROP: "
}

function LoadWhiteLists(){
	
	$GLOBALS["WHITELISTED_REGEX"]=array();
	$sock=new sockets();

	$q=new mysql();
	$sql="SELECT * FROM postfix_whitelist_con";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){writelogs("mysql error $q->mysql_error [$q->mysql_admin/$q->mysql_password]",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);return array();}
	
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$WHITELISTED[$ligne["ipaddr"]]=true;
		$WHITELISTED[$ligne["hostname"]]=true;
		
	}
	
	$ldap=new clladp();	
	$nets=$ldap->load_mynetworks();	
	if(is_array($nets)){
	while (list ($num, $network) = each ($nets) ){
		if(trim($network)==null){continue;}
		if(preg_match("#(.+?)\/#",$network,$re)){
			$ip=trim($re[1]);
			if($GLOBALS["VERBOSE"]){echo "Network: $ip\n";}
			if(!preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)#",$ip,$re)){continue;}
			$GLOBALS["WHITELISTED_REGEX"][]="{$re[1]}\.{$re[2]}\.{$re[3]}";
			$already_added_regex["{$re[1]}\.{$re[2]}\.{$re[3]}"]=true;
		}
	}}	
		
	$sql="SELECT * FROM amavisd_bypass ORDER BY ip_addr";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){if($GLOBALS["VERBOSE"]){echo $q->mysql_error."\n";}}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$ip=trim($ligne["ip_addr"]);
		if($ip==null){continue;}
		$ip=str_replace('.','\.',$ip);
		if(!$already_added_regex[$ip]){
			$GLOBALS["WHITELISTED_REGEX"][]=$ip;
			$already_added_regex["{$re[1]}\.{$re[2]}\.{$re[3]}"]=true;
		}
		
	}
		
	if($GLOBALS["VERBOSE"]){
		if(is_array($WHITELISTED)){
			while (list ($ip, $ligne) = each ($WHITELISTED) ){
				echo "Loading Whitelisted:$ip\n ";
			}
		}
		while (list ($index, $ip) = each ($GLOBALS["WHITELISTED_REGEX"]) ){
			if($ip==0){continue;}
			if(trim($ip)==null){continue;}
			echo "Loading Whitelisted: using regex:$ip\n ";
		}		
		
	}
	
	
	
	if($GLOBALS["VERBOSE"]){echo "End of whitelisted ips\n";}
	return $WHITELISTED;
	
}
function isWhiteListed($ip){
	
	if(preg_match("#[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+#",$ip)){
		$hostname=gethostbyaddr($ip);
		if($GLOBALS["VERBOSE"]){echo "hostname=\"$hostname\" [$ip]\n";}
	}else{
		$hostname=$ip;
		$ip=gethostbyname($hostname);
		if($GLOBALS["VERBOSE"]){echo "hostname=\"$hostname\" [$ip]\n";}
	}
	
	if(is_array($GLOBALS["IPTABLES_WHITELISTED"])){
		if($GLOBALS["IPTABLES_WHITELISTED"][$ip]){
			if($GLOBALS["VERBOSE"]){echo "$ip is whitelisted using whitelist table\n";}
			return true;
		}
		if($hostname<>null){
			if($GLOBALS["IPTABLES_WHITELISTED"][$hostname]){
				if($GLOBALS["VERBOSE"]){echo "$hostname is whitelisted using whitelist table\n";}
				return true;
			}
		}
		
	}
	
	if(is_array($GLOBALS["WHITELISTED_REGEX"])){
		reset($GLOBALS["WHITELISTED_REGEX"]);
		while (list ($index, $pattern) = each ($GLOBALS["WHITELISTED_REGEX"]) ){
			if(trim($pattern)==null){continue;}
			if(preg_match("#$pattern#",$ip)){
				if($GLOBALS["VERBOSE"]){echo "$ip is regex whitelisted (matching $pattern)\n";}
				return true;
			}
			if($hostname<>null){
				if(preg_match("#$pattern#",$hostname)){
					if($GLOBALS["VERBOSE"]){echo "$ip is regex whitelisted (matching $pattern)\n";}
					return true;
				}	
			}		
			
		}
		
	}
	
	//if($GLOBALS["VERBOSE"]){echo "$ip not whitelisted\n";}
	return false;
	
}

}

?>