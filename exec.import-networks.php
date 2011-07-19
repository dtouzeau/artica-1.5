<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.computers.inc');
include_once(dirname(__FILE__).'/ressources/class.user.inc');
include_once(dirname(__FILE__).'/ressources/class.ccurl.inc');
include_once(dirname(__FILE__).'/ressources/class.pdns.inc');
include_once(dirname(__FILE__).'/ressources/class.openvpn.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.dhcpd.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");
if(isset($_POST["artica_ip_addr"])){BuildList();die();}

include_once(dirname(__FILE__).'/framework/class.unix.inc');
if(!Build_pid_func(__FILE__,"MAIN")){
	writelogs(basename(__FILE__).":Already executed.. aborting the process",basename(__FILE__),__FILE__,__LINE__);
	die();
}
if(posix_getuid()<>0){die();}

if($argv[1]=='--force'){$GLOBALS["FORCE"]=true;}
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}

ImportNets();
die();


function BuildList(){
	if(!isset($_SESSION["uid"])){
		if(!logon()){die();}
	}
	
	$users=new usersMenus();
	if(!$users->AsSystemAdministrator){die();}
	
	
	$filter_search="(&(objectClass=ArticaComputerInfos)(|(cn=*)(ComputerIP=*)(uid=*))(gecos=computer))";
	$attrs=array("uid","ComputerIP","ComputerOS","ComputerMachineType","ComputerMacAddress","ComputerRealName","DnsZoneName","DnsType");
	$ldap=new clladp();
	$dn="$ldap->suffix";
	$hash["COMPUTERS"]=$ldap->Ldap_search($dn,$filter_search,$attrs);
	$hash["VPN"]=export_vpn_remotes_sites();
	echo base64_encode(serialize($hash));
	
}


function ImportNets(){
	$sock=new sockets();
	$ini=new Bs_IniHandler();
	$ini->loadString($sock->GET_INFO("ComputersImportArtica"));
	if(!is_array($ini->_params)){writelogs("No artica masters defined",__FUNCTION__,__FILE__,__LINE__);die();}
	if(count($ini->_params)==0){writelogs("No artica masters defined",__FUNCTION__,__FILE__,__LINE__);die();}
	
	while (list ($ip, $array) = each ($ini->_params)){
		if(trim($ip)==null){continue;}
		$curl=new ccurl("https://$ip:{$array["port"]}/exec.import-networks.php");
		while (list ($key, $value) = each ($array)){$curl->parms[$key]=$value;}
			
		$curl->get();
		$datas=$curl->data;
		if(trim($datas)==null){continue;}
		$md5=md5($datas);
		import_vpn_remotes_sites($datas);
		if(!$GLOBALS["FORCE"]){
			if($array["md5"]<>$md5){ImportDatas($datas);}
		}else{
			ImportDatas($datas);
		}
		$ini->_params[$ip]["md5"]=$md5;
		
	}
	reset($ini->_params);
	$ini->saveFile("/etc/artica-postfix/settings/Daemons/ComputersImportArtica");
	
	
}


function import_vpn_remotes_sites($datas){
	$datas=unserialize(base64_decode($datas));
	if(!is_array($datas["VPN"])){return null;}
	$router=$_SERVER['REMOTE_ADDR'];
	$dhcp=new dhcpd();
	while (list ($index, $line) = each ($datas["VPN"])){
		if($line==null){continue;}
		$tbl=explode(";",$line);
		if($tbl[0]==null){continue;}
		if($tbl[1]==null){continue;}
		$dhcp->AddRoute($tbl[0],$tbl[1],$router);
	}
	
}

function ImportDatas($datas){
	$import=unserialize(base64_decode($datas));
	$hash=$import["COMPUTERS"];
	for($i=0;$i<$hash["count"];$i++){
		$comp=new computers();
		$uidorg=$hash[$i]["uid"][0];;
		$dnszonename=$hash[$i]["dnszonename"][0];
		$dnstype=$hash[$i]["dnstype"][0];
		$computermachinetype=$hash[$i]["computermachinetype"][0];
		$computerip=$hash[$i]["computerip"][0];
		$computermacaddress=$hash[$i]["computermacaddress"][0];
		$ComputerRealName=$hash[$i][strtolower("ComputerRealName")][0];
		
		if($ComputerRealName==null){$ComputerRealName=str_replace('$','',$uidorg);}
		writelogs("Importing computer: $uid $computerip $computermacaddress",__FUNCTION__,__FILE__,__LINE__);
		$uid=$comp->ComputerIDFromMAC($computermacaddress);
		
		
if($uid==null){
		$uid=$uidorg;
		$comp=new computers();
		$comp->ComputerRealName=$ComputerRealName;
		$comp->ComputerMacAddress=$computermacaddress;
		$comp->ComputerIP=$computerip;
		$comp->DnsZoneName=$dnszonename;
		$comp->ComputerMachineType=$computermachinetype;
		$comp->uid=$uid;
		$comp->Add();
	}else{
		
		$comp=new computers($uid);
		$comp->ComputerIP=$computerip;
		$comp->DnsZoneName=$dnszonename;
		$comp->ComputerMachineType=$computermachinetype;
		$comp->Edit();
		
		
		
	}
	
	
	$dns=new pdns($dnszonename);
	$dns->EditIPName(strtolower($ComputerRealName),$computerip,$dnstype,$computermacaddress);
			
		
		
	}
	
}

function export_vpn_remotes_sites(){
	$array=array();
	$sql="SELECT IP_START,netmask FROM vpnclient WHERE connexion_type=1 ORDER BY sitename DESC";
	$q=new mysql();
	
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$array[]="{$ligne["IP_START"]};{$ligne["netmask"]}";
	}
	return $array;
	
}




function logon(){
	
	include("ressources/settings.inc");
if($_POST["artica_user"]==$_GLOBAL["ldap_admin"]){
		if($_POST["password"]<>$_GLOBAL["ldap_password"]){
			$_GET["ERROR"]="bad password";
			return false;
		}else{
			session_start();
			$_SESSION["uid"]='-100';
			$_SESSION["groupid"]='-100';
			$_SESSION["passwd"]=$_POST["password"];
			$_SESSION["privileges"]='
			[AllowAddGroup]="yes"
			[AllowAddUsers]="yes"
			[AllowChangeKav]="yes"
			[AllowChangeKas]="yes"
			[AllowChangeUserPassword]="yes"
			[AllowEditAliases]="yes"
			[AllowEditAsWbl]="yes"
			[AsSystemAdministrator]="yes"
			[AsPostfixAdministrator]="yes"
			[AsArticaAdministrator]="yes"
			';
		return true;
		}
	}
	
	
	writelogs('This is not Global admin, so test user...',__FUNCTION__,__FILE__);
	$u=new user($_POST["artica_user"]);
	$userPassword=$u->password;
	if(trim($u->uidNumber)==null){
		writelogs('Unable to get user infos abort',__FUNCTION__,__FILE__);
		return false;;
	}
	
	
	
	if(trim($_POST["password"])==trim($userPassword)){
			$ldap=new clladp();
			$ouprivs=$ldap->_Get_privileges_ou($u->uid,$u->ou);
			$_SESSION["OU_LANG"]=$ouprivs["ForceLanguageUsers"];
			$_SESSION["uid"]=$_POST["artica_user"];
			$_SESSION["passwd"]=$_POST["password"];
			$_SESSION["privileges"]["ArticaGroupPrivileges"]=$ldap->_Get_privileges_userid($_POST["artica_user"]);
			
			return true;
	}
}


?>