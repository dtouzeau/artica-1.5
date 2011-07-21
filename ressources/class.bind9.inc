<?php
include_once(dirname(__FILE__)."/class.ldap.inc");
include_once(dirname(__FILE__)."/class.system.network.inc");


class bind9{
	var $dn;
	var $NamedConf;
	var $_blok_options;
	var $forwarders=array();
	var $global_directory="/var/cache/bind";
	var $zones;
	var $hash_zones=array();
	
	
	function bind9(){
		$ldap=new clladp();
		$this->LoadMasterConf();
		$this->ParseOptions();
		$this->Parse_forwarders();
		$this->ParseZones();
		$this->hash_zones[null]="{select}";
		
		
		
		
	}
	
	function LoadMasterConf(){

	}
	
	function AddNewForwarder($name){
		$name=trim(strtolower($name));
		writelogs("add forwared $name",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$forwarder=$this->forwarders;
		if(!is_array($forwarder)){
			$this->forwarders[]=$name;
			$this->Compile();
			return true;
		}
		while (list ($num, $ligne) = each ($forwarder) ){
			if(trim(strtolower($ligne))==$name){
				return true;
			}
		}
		
		$this->forwarders[]=$name;
		$this->Compile();
		return true;	
		
	}
	
	function DeleteForwarder($name){
		writelogs("Delete $name",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$name=trim(strtolower($name));
		$forwarder=$this->forwarders;
		writelogs("forwarder array of ".count($forwarder).' cells',__CLASS__.'/'.__FUNCTION__,__FILE__);
		while (list ($num, $ligne) = each ($forwarder) ){
			if(trim(strtolower($ligne))==$name){
				writelogs("success found/delete $name",__CLASS__.'/'.__FUNCTION__,__FILE__);
				unset($this->forwarders[$num]);
				$this->Compile();		
				break;
			}
		}
	}
	
	function Compile(){
		writelogs("Compile forwarders",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$sock=new sockets();
		if(is_array($this->forwarders)){
			$forwarders=implode("\n",$this->forwarders);
			$sock->SaveConfigFile($forwarders,"Bind9ForwardersList");
		}else{
			writelogs("No forwaders found...",__CLASS__.'/'.__FUNCTION__,__FILE__);
		}
		
		writelogs("Compile computers footers",__CLASS__.'/'.__FUNCTION__,__FILE__);
		writelogs("Get Zones",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$bind=new Bind9Zone(null);
		if(is_array($bind->array_zones)){
				while (list ($num, $zone) = each ($bind->array_zones) ){
					writelogs("Build computer for Zone $zone",__CLASS__.'/'.__FUNCTION__,__FILE__);
					$zone=new Bind9Zone($zone);
					$zone->CompileZone();
				}
		}
		
		
		$sock=new sockets();
		$sock->getfile('Bind9Compile');
		
	}
	
	
	function ParseOptions(){
		if(preg_match("#options\s+\{(.+?)\}#is",$this->NamedConf,$re)){
			$this->_blok_options=$re[1];
		}
	}
	
	function Parse_forwarders(){
	    $sock=new sockets();
	    $datas=$sock->GET_INFO('Bind9ForwardersList');
		$tbl=explode("\n",$datas);
		if(!is_array($tbl)){$this->forwarders=array();}
		$f=false;
		$res=array();
		
		while (list ($num, $ligne) = each ($tbl) ){
		 	if(trim($ligne)==null){continue;}
		 	$this->forwarders[]=$ligne;
		 	}
	}
	
	function ParseZones(){
		$tbl=explode("\n",$this->NamedConf);
		while (list ($num, $ligne) = each ($tbl) ){
			if(preg_match('#zone\s+"(.+?)"\s+{#',$ligne,$re)){
				$this->zones[$re[1]]=$this->ParseSingleZone($re[1]);
				$this->hash_zones[$re[1]]=$re[1];
				}
			if(preg_match('#directory\s+"(.+?)"#',$ligne,$re)){$this->global_directory=$re[1];}
			
		}
	}
	
	function ParseSingleZone($zone){
		$tbl=explode("\n",$this->NamedConf);
		$f=false;
		$res=array();
		while (list ($num, $ligne) = each ($tbl) ){
		 if(preg_match('#zone\s+"'.$zone.'"\s+{#',$ligne)){$f=true;continue;}
		 if($f){
		 	if(preg_match('#type\s+(.+)#',$ligne,$re)){$res["type"]=$re[1];}
		 	if(preg_match('#file\s+"(.+?)"#',$ligne,$re)){$res["file"]=$re[1];}
		 	if(preg_match('#\};#',$ligne,$re)){break;}
		 	}
		}
		$res["type"]=str_replace(';','',$res["type"]);
		$res["file"]=str_replace(';','',$res["file"]);
		return $res;
	}
	
	function SaveToLdap(){
		
		$options=$this->build_options();
		$conf=$conf ."$options\n".$this->BuildZones();
		
		$ldap=new clladp();
		$arr["NamedConf"][0]=$conf;
		
		writelogs("Adding Global config in $this->dn",__FUNCTION__.'/'.__CLASS__,__FILE__);
		
		$ldap->Ldap_modify($this->dn,$arr);
		
	}
	
	
	function BuildZones(){
		$zones=$this->zones;
		if(is_array($zones)){
			while (list ($num, $ligne) = each ($zones) ){
				$filename=$num;
				if($filename=='.'){$filename='dot';}
				
				if($ligne["file"]==null){$ligne["file"]="/etc/bind/hosts.$filename.cf";}
				$conf=$conf."zone \"$num\" {\n";
				$conf=$conf."\ttype {$ligne["type"]};\n";
				$conf=$conf."\tfile \"{$ligne["file"]}\";\n";
				$conf=$conf."};\n\n";
			}
		return $conf;
		}
	}
	
	
	function build_options(){
			@reset($this->forwarders);
			$conf=$conf."options {\n";
			$conf=$conf."\tdirectory \"$this->global_directory\";\n";
			$conf=$conf."\tpid-file    \"/var/run/bind/bind.pid\";\n";
			$conf=$conf."\tdump-file    \"/var/dump/bind/named_dump.db\";\n";
			$conf=$conf."\tstatistics-file    \"/var/log/bind/named.stats\";\n";			
			$conf=$conf."\n";
			if(count($this->forwarders)==0){
				$conf=$conf."\t// forwarders {\n";
				$conf=$conf."\t// \t0.0.0.0;\n";
				$conf=$conf."\n\t// };\n";
			}else{
				$conf=$conf."\tforwarders {\n";
				while (list ($num, $ligne) = each ($this->forwarders) ){
					$conf=$conf."\t$ligne;\n";
				}
				$conf=$conf."\n\t};\n";
				
			}
		
			$conf=$conf."\n";
			$conf=$conf."\tauth-nxdomain no;    # conform to RFC1035\n";
			$conf=$conf."\tlisten-on-v6 { any; };\n";
			$conf=$conf."};\n";
			$conf=$conf."\n";
			$conf=$conf."logging {\n";
			$conf=$conf."\tchannel update_debug {\n";
			$conf=$conf."\t\tfile \"/var/log/bind/log-update-debug.log\";\n";
			$conf=$conf."\t\tseverity  debug 3;\n";
			$conf=$conf."\t\tprint-category yes;\n";
			$conf=$conf."\t\tprint-severity yes;\n";
			$conf=$conf."\t\tprint-time     yes;\n";
			$conf=$conf."\t};\n";
			$conf=$conf."\tchannel security_info    {\n";
			$conf=$conf."\t\tfile \"/var/log/bind/log-named-auth.info\";\n";
			$conf=$conf."\t\tseverity  info;\n";
			$conf=$conf."\t\tprint-category yes;\n";
			$conf=$conf."\t\tprint-severity yes;\n";
			$conf=$conf."\t\tprint-time     yes;\n";
			$conf=$conf."\t};\n";
			$conf=$conf."\n";
			$conf=$conf."\tchannel queries_info        {\n";
			$conf=$conf."\t\tfile \"/var/log/bind/log-queries.info\";\n";
			$conf=$conf."\t\tseverity  info;\n";
			$conf=$conf."\t\tprint-category yes;\n";
			$conf=$conf."\t\tprint-severity yes;\n";
			$conf=$conf."\t\tprint-time     yes;\n";
			$conf=$conf."\t};\n";
			$conf=$conf."\n";
			$conf=$conf."\t channel lame_info        {\n";
			$conf=$conf."\t\tfile \"/var/log/bind/log-lame.info\";\n";
			$conf=$conf."\t\tseverity  info;\n";
			$conf=$conf."\t\tprint-category yes;\n";
			$conf=$conf."\t\tprint-severity yes;\n";
			$conf=$conf."\t\tprint-time     yes;\n";
			$conf=$conf."\t};\n";
			$conf=$conf."\n";
			$conf=$conf."\tcategory update { update_debug; };\n";
			$conf=$conf."\tcategory security { security_info; };\n";
			$conf=$conf."\tcategory queries { queries_info; };\n";
			$conf=$conf."\tcategory lame-servers { lame_info; };\n";
			$conf=$conf."};\n";				
			return $conf;
		
		
	}
	
}

class bind9_zones{
	var $zone_name;
	var $dn;
	var $ZoneContent;
	var $PrimaryServerName;
	var $Hostmaster_email;
	var $hostname='';
	var $numeric_options=array();
	var $ttl;
	
	

	
	
	function bind9_zones($zone){
		$user=new usersMenus();
		$this->hostname=$user->hostname;
		if($zone<>null){
			$this->zone_name=$zone;
			$ldap=new clladp();
			$this->dn="cn=$zone,cn=bind9,cn=artica,$ldap->suffix";
			$sr =@ldap_read($ldap->ldap_connection,$this->dn,'objectClass=Bind');
			if ($sr) {
				$hash=ldap_get_entries($ldap->ldap_connection,$sr);	
				$this->ZoneContent=$hash[0][strtolower("ZoneContent")][0];	
			}
		$this->ParseConfig();}
		
		
	}
	
	function AddZone(){
		$users=new usersMenus();
		$this->PrimaryServerName=$users->hostname;
		$this->Hostmaster_email="root@localhost";
		$this->ttl=86400;
		$this->numeric_options[1]=28800;
		$this->numeric_options[2]=14400;
		$this->numeric_options[3]=2592000;
		$this->numeric_options[4]=86400;
		$config=$this->BuildConfig();
		
		$upd["objectClass"][]="Bind";
		$upd["objectClass"][]="top";
		$upd["cn"][]=$this->zone_name;
		$upd["ZoneContent"][]=$config;
		
		writelogs("Adding new zone settings in $this->dn",__FUNCTION__.'/'.__CLASS__,__FILE__);
		
		$ldap=new clladp();
		$tpl=new templates();
		if(!$ldap->ldap_add($this->dn,$upd)){
			echo $ldap->ldap_last_error;
		}else{
			echo $tpl->_ENGINE_parse_body('{success}');
		}
		
		
		
	}
	
	function ParseConfig(){
		$soa=false;
		$tbl=explode("\n",$this->ZoneContent);
		while (list ($num, $ligne) = each ($tbl) ){
			if(preg_match('#\$ttl\s+([0-9]+)#',$ligne,$re)){
				$this->ttl=$re[1];
			}
			
			if(preg_match('#IN\s+SOA\s+(.+?)\s+(.+?)\s+#',$ligne,$re)){
				$this->PrimaryServerName=$re[1];
				$this->Hostmaster_email=$re[2];
				$soa=true;
				continue;
			}
			
			if($soa){
				if(preg_match('#([0-9]+)#',$ligne,$re)){
					$this->numeric_options[]=$re[1];
				}
			}
			
			if(preg_match('#\)#',$ligne)){
				$soa=false;
			}
			
			
		}
		
		if(strpos($this->PrimaryServerName,'.')>0){
			$splitServer=substr($this->PrimaryServerName,0,strpos($this->PrimaryServerName,'.'));
			$this->PrimaryServerName=$splitServer;
		}
		
		
		
		$tb=explode('\.',$this->Hostmaster_email);
		$this->Hostmaster_email=$tb[0]."@".$tb[1];
		
		
	}
	
	function DeleteThisZone(){
		$ldap=new clladp();
		$ldap->ldap_delete($this->dn,true);
		
	}
	
	
	
	function SaveToLdap(){
		$arr["ZoneContent"][0]=$this->BuildConfig();
		$ldap=new clladp();
		$tpl=new templates();
		
		if(!$ldap->ExistsDN($this->dn)){
			$arr["objectClass"][]="Bind";
			$arr["objectClass"][]="top";
			$arr["cn"][]=$this->zone_name;
			if($ldap->ldap_add($this->dn,$arr)){
				echo $tpl->_ENGINE_parse_body("{success}\n");
				return true;
			}else{
				echo $ldap->ldap_last_error;
				return false;
			}
			
		}
		
		if($ldap->Ldap_modify($this->dn,$arr)){
			echo $tpl->_ENGINE_parse_body("{success}\n");
			return true;
		}
		
		echo $ldap->ldap_last_error;
		
	}
	
	function BuildConfig(){
		$root=str_replace('@','.',$this->Hostmaster_email);
		$time=$this->numeric_options[0];
		if($time==null){$time=date('Ymd')."00";}
		$numeric=substr($time,strlen($time)-1,1);
		writelogs("numeric=$numeric",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$numeric=$numeric+1;
		$time=date('Ym')."0".date('d').$numeric;
		writelogs("time=$time",__CLASS__.'/'.__FUNCTION__,__FILE__);
		
		if(strpos($this->PrimaryServerName,'.')>0){
			$splitServer=substr($this->PrimaryServerName,0,strpos($this->PrimaryServerName,'.'));
			$this->PrimaryServerName=$splitServer;
		}		
	
		
		$conf=$conf."\$ttl $this->ttl\n";
		$conf=$conf."$this->zone_name.	IN	SOA	$this->PrimaryServerName. $root. (\n";
		$conf=$conf."\t$time\n";
		$conf=$conf."\t{$this->numeric_options[1]}\n";
		$conf=$conf."\t{$this->numeric_options[2]}\n";
		$conf=$conf."\t{$this->numeric_options[3]}\n";
		$conf=$conf."\t{$this->numeric_options[4]})\n";
		$conf=$conf."$this->zone_name.	IN	NS	$this->PrimaryServerName.\n";
		return $conf.$this->CompileComputers();
}

function LoadComputers(){
	$query="(&(objectClass=ArticaComputerInfos)(DnsZoneName=$this->zone_name))";
	$filter=array();
	$ldap=new clladp();
	$conf="\n";
	$hash=$ldap->Ldap_search($ldap->suffix,$query,$filter);
	$count=$hash["count"];
	writelogs("compile $count computers for $this->zone_name",__CLASS__.'/'.__FUNCTION__,__FILE__);
	for($i=0;$i<$count;$i++){
		$uid=str_replace('$','',$hash[$i]["uid"][0]);
		$ip=$hash[$i]["computerip"][0];
		$type=$hash[$i]["dnstype"][0];
		$DnsMXLength=$hash[$i][strtolower("DnsMXLength")][0];
		if($DnsMXLength==null){$DnsMXLength=10;}
		
		$array[$uid]=array(
			"IP"=>$ip,
			"type"=>$type,
			"DnsMXLength"=>$DnsMXLength,
		);
		
	}
	
	
	
	
	
}


function CompileComputers(){
	$query="(&(objectClass=ArticaComputerInfos)(DnsZoneName=$this->zone_name))";
	$filter=array();
	$ldap=new clladp();
	$conf="\n";
	$hash=$ldap->Ldap_search($ldap->suffix,$query,$filter);
	$count=$hash["count"];
	
	writelogs("compile $count computers for $this->zone_name",__CLASS__.'/'.__FUNCTION__,__FILE__);
	for($i=0;$i<$count;$i++){
		$uid=str_replace('$','',$hash[$i]["uid"][0]);
		$ip=$hash[$i]["computerip"][0];
		$type=$hash[$i]["dnstype"][0];
		$DnsMXLength=$hash[$i][strtolower("DnsMXLength")][0];
		if($DnsMXLength==null){$DnsMXLength=10;}
		if(trim($ip)=='0.0.0.0'){$ip=null;}
		
		if(trim($ip)==null){
			writelogs("$uid has no ip address, aborting this record",__CLASS__.'/'.__FUNCTION__,__FILE__);
			continue;}
		if(preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)#",$uid,$re)){
			writelogs("$uid is an ip address, aborting this record",__CLASS__.'/'.__FUNCTION__,__FILE__);
			continue;}
			
		if(strpos($uid,'.')>0){
			$uid=substr($uid,0,strpos($uid,'.'));
		}
		
		if($type=="MX"){
			$conf=$conf ."$uid\tIN\tA\t$ip\n";
			$conf=$conf ."$this->zone_name.\tIN\tMX\t$DnsMXLength\t$uid\n";
			
		}else{
		$conf=$conf ."$uid\tIN\t$type\t$ip\n";
		}
		
		for($c=0;$c<$hash[$i][strtolower("DnsCname")]["count"];$c++){
			$conf=$conf ."{$hash[$i][strtolower("DnsCname")][$c]}\tIN\tCNAME\t$uid\n";
		}
		
		if($hash[$i][strtolower("ComputerCPU")][0]<>null){
			if($hash[$i][strtolower("ComputerOS")][0]==null){$hash[$i][strtolower("ComputerOS")][0]="Unknown";}
			$conf=$conf ."$uid\tIN\tHINFO\t\"{$hash[$i][strtolower("ComputerCPU")][0]}\"\t\"{$hash[$i][strtolower("ComputerOS")][0]}\"\n";
		}
		
		
		$conf=$conf ."\n";

		
		
		
	}
	$conf=$conf ."\n";
	return $conf;
	
}



	
	
	
}


class Bind9Zone{
	var $domain;
	var $array_zones=array();
	var $array_zone_computers=array();
	var $ttl=38400;
	var $count_computers=0;
	var $array_arpa=array();
	function Bind9Zone($domain){
		$this->domain=$domain;
		$this->ListZones();
		if($domain<>null){$this->ListComputers($domain);}
		
		
	}
	
	
	function ListZones(){
		$sock=new sockets();
		$datas=$sock->getfile("Bind9ListZones");
		$datas=explode("\n",$datas);
		if(!is_array($datas)){return array();}
		while (list ($num, $ligne) = each ($datas) ){
			if(trim($ligne)==null){continue;}
			if(preg_match('#Bind9Zone\.(.+?).zone\.hosts$#',$ligne,$re)){
			$this->array_zones[$re[1]]=$re[1];
			}
		}
		
	}
	
	function BuildZonesArpaHeader($arpareverse){
		$date=$this->BuildTime();
		$net=new networking();
		if($net->array_TCP["eth0"]<>null){$ip=$net->array_TCP["eth0"];}
		if($ip==null){if($net->array_TCP["eth1"]<>null){$ip=$net->array_TCP["eth0"];}}
		if($ip==null){if($net->array_TCP["eth2"]<>null){$ip=$net->array_TCP["eth2"];}}		
		
		
		$conf=$conf."\$ttl $this->ttl\n";
		$conf=$conf."@\tIN\tSOA\tns.$this->domain.\thostmaster.$this->domain.(\n";
		$conf=$conf."\t$date\n";
		$conf=$conf."\t10800\n";
		$conf=$conf."\t3600\n";
		$conf=$conf."\t604800\n";
		$conf=$conf."\t38400 )\n";
		$conf=$conf."\t\tNS\tns.$this->domain.\n";
		//$conf=$conf."\$ORIGIN $arpareverse.in-addr.arpa.\n";
		if(preg_match('#\.([0-9]+)$#',$ip,$re)){
			$conf=$conf."{$re[1]}\tIN\tPTR\tns.$this->domain.\n";
			
		}
		return $conf;
	}	
	
	
	function BuildZonesHeader($nons=0){
		$date=$this->BuildTime();
		$net=new networking();
		if($net->array_TCP["eth0"]<>null){$ip=$net->array_TCP["eth0"];}
		if($ip==null){if($net->array_TCP["eth1"]<>null){$ip=$net->array_TCP["eth0"];}}
		if($ip==null){if($net->array_TCP["eth2"]<>null){$ip=$net->array_TCP["eth2"];}}		
		
		
		$conf=$conf."\$ttl $this->ttl\n";
		$conf=$conf."@\tIN\tSOA\tns.$this->domain.\thostmaster.$this->domain.(\n";
		$conf=$conf."\t$date\n";
		$conf=$conf."\t10800\n";
		$conf=$conf."\t3600\n";
		$conf=$conf."\t604800\n";
		$conf=$conf."\t38400 )\n";
		//$conf=$conf."\$ORIGIN\tns.$this->domain.\n";
		$conf=$conf."\t\tNS\tns.$this->domain.\n";
		$conf=$conf."ns\tA\t$ip\n";
		return $conf;
	}
	
function BuildTime(){
		$time=date('Ymd')."00";
		$numeric=substr($time,strlen($time)-1,1);
		$numeric=$numeric+1;
		$time=date('Ym')."0".date('d').$numeric;
		return $time;
		}
		
		
function CompileZone(){
	$headers=$this->BuildZonesHeader();
	$footer=$this->ListComputers($this->domain);
	$this->CompileArpa();
	writelogs("Saving Bind9Zone.$this->domain.hosts",__CLASS__.'/'.__FUNCTION__,__FILE__);
	$sock=new sockets();
	$sock->SaveConfigFile($headers,"Bind9Zone.$this->domain.hosts");
	$sock->SaveConfigFile($footer,"Bind9Zone.$this->domain.footer");	
}


function CompileArpa(){
	if(!is_array($this->array_arpa)){return null;}
	$sock=new sockets();
	while (list ($arpafile, $array_ip) = each ($this->array_arpa)){
		writelogs("Checking $arpafile",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$sock->SaveConfigFile($this->BuildZonesArpaHeader($arpafile),"Bind9Zone.$arpafile.zone.arpa.header");
		$this->BuildZoneArpa($arpafile);
		$conf=null;
		while (list ($num, $array) = each ($array_ip)){
			if(preg_match('#\.([0-9]+)$#',$array[0],$re)){
			$conf=$conf . "{$re[1]}\tIN\tPTR\t{$array[1]}.$this->domain.\n";
			writelogs("Checking $arpafile:: {$array[0]} -> {$array[1]}",__CLASS__.'/'.__FUNCTION__,__FILE__);
			}
		}
		$sock->SaveConfigFile($conf,"Bind9Zone.$arpafile.zone.arpa.footer");
		
		
	}
}

function BuildZoneArpa($arpareversed){
		$users=new usersMenus();
		$notify="no";
		if($users->dhcp_installed){
			$artica=new artica_general();
			if($artica->EnableDHCPServer==1){
				$notify="yes";
			}
			
		}
		$conf="\nzone \"$arpareversed.in-addr.arpa\" {\n";
		$conf=$conf ."\ttype master;\n";
		$conf=$conf ."\tfile \"/etc/bind/$arpareversed.arpa\";\n";
		$conf=$conf ."\tnotify $notify;\n";
		$conf=$conf ."\tallow-update { key \"globalkey\";};\n";
		$conf=$conf ."};\n";
		$sock=new sockets();
		writelogs("saving Bind9Zone.$arpareversed.zone.arpa",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$sock->SaveConfigFile($conf,"Bind9Zone.$arpareversed.zone.arpa");
	
	
}
		
function SaveBind9Zone($ip){
		$users=new usersMenus();
		$notify="no";
		if($users->dhcp_installed){
			$artica=new artica_general();
			if($artica->EnableDHCPServer==1){
				$notify="yes";
			}
			
		}
		
		if(!preg_match('#([0-9]+)\.([0-9]+)\.([0-9]+)#',$ip,$re)){return null;}		
		
		$arpa="{$re[3]}.{$re[2]}.{$re[1]}";
		
		$conf=$conf ."zone \"$this->domain\" {\n";
		$conf=$conf ."\ttype master;\n";
		$conf=$conf ."\tfile \"/etc/bind/Bind9Zone.$this->domain.hosts\";\n";
		$conf=$conf ."\tnotify $notify;\n";
		$conf=$conf ."\tallow-update { key \"globalkey\";};\n";		
		$conf=$conf ."};\n";
		$conf=$conf ."\n";
		
		$zone_host=$conf;
		$conf=null;
		
		$this->BuildZoneArpa($arpa);
		
		
		
		$sock=new sockets();
		
		writelogs("Save new config file : Bind9Zone.$this->domain.zone.hosts",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$sock->SaveConfigFile($zone_host,"Bind9Zone.$this->domain.zone.hosts");
		

		$headers=$this->BuildZonesHeader();
		$headers_hosts=$headers;
		$footer=$this->ListComputers($this->domain);
		writelogs("Saving Bind9Zone.$this->domain.hosts",__CLASS__.'/'.__FUNCTION__,__FILE__);
		$sock->SaveConfigFile($headers_hosts,"Bind9Zone.$this->domain.hosts");
		$sock->SaveConfigFile($footer,"Bind9Zone.$this->domain.footer");
		$sock->SaveConfigFile($headers,"Bind9Zone.$arpa.arpa.header");
		return $conf;
		
		
	}

function ListComputers($zone_name){
	unset($this->array_zone_computers);
	unset($this->array_arpa);
	$this->count_computers=0;
	$query="(&(objectClass=ArticaComputerInfos)(DnsZoneName=$zone_name))";
	$filter=array();
	$ldap=new clladp();
	$conf="\n";
	$hash=$ldap->Ldap_search($ldap->suffix,$query,$filter);
	$count=$hash["count"];
	
	writelogs("compile $count computers for $zone_name",__CLASS__.'/'.__FUNCTION__,__FILE__);
	for($i=0;$i<$count;$i++){
		$DnsMXLength=null;
		$uid=str_replace('$','',$hash[$i]["uid"][0]);
		$ip=$hash[$i]["computerip"][0];
		$type=$hash[$i]["dnstype"][0];
		if(isset($hash[$i][strtolower("DnsMXLength")][0])){$DnsMXLength=$hash[$i][strtolower("DnsMXLength")][0];}
		if($DnsMXLength==null){$DnsMXLength=10;}
		
		if(trim($ip)==null){
			writelogs("$uid has no ip address, aborting this record",__CLASS__.'/'.__FUNCTION__,__FILE__);
			continue;}
		if(preg_match("#([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)#",$uid,$re)){
			writelogs("$uid is an ip address, aborting this record",__CLASS__.'/'.__FUNCTION__,__FILE__);
			continue;}
			
		if(strpos($uid,'.')>0){
			$uid=substr($uid,0,strpos($uid,'.'));
		}
		
		if($type=="MX"){
			$conf=$conf ."$uid\tIN\tA\t$ip\n";
			$conf=$conf ."$zone_name.\tIN\tMX\t$DnsMXLength\t$uid\n";
			
		}else{
		$conf=$conf ."$uid\tIN\t$type\t$ip\n";
		
		if(preg_match('#([0-9]+)\.([0-9]+)\.([0-9]+)#',$ip,$re)){
			$arpa="{$re[3]}.{$re[2]}.{$re[1]}";
			$this->array_arpa[$arpa][]=array($ip,$uid);
			
		}		
		
		
		
		
		$this->array_zone_computers[$uid]=array($type,$ip);
		$this->count_computers=$this->count_computers+1;
		}
		
		if(isset($hash[$i][strtolower("DnsCname")]["count"])){
			for($c=0;$c<$hash[$i][strtolower("DnsCname")]["count"];$c++){
				$conf=$conf ."{$hash[$i][strtolower("DnsCname")][$c]}\tIN\tCNAME\t$uid\n";
			}
		}
		
		if($hash[$i][strtolower("ComputerCPU")][0]<>null){
			if($hash[$i][strtolower("ComputerOS")][0]==null){$hash[$i][strtolower("ComputerOS")][0]="Unknown";}
			$conf=$conf ."$uid\tIN\tHINFO\t\"{$hash[$i][strtolower("ComputerCPU")][0]}\"\t\"{$hash[$i][strtolower("ComputerOS")][0]}\"\n";
		}
		
		
		$conf=$conf ."\n";

		
		
		
	}
	$conf="\n\n$conf\n";
	return $conf;
	
}	
	
	

	
}