<?php

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__).'/ressources/class.computers.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");
if($argv[1]=='--parse'){
	parsefile("/etc/artica-postfix/{$argv[2]}.map",$argv[3]);
	die();
}

if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}



$GLOBALS["COMPUTER"]=$argv[1];
$GLOBALS["COMPUTER"]=str_replace('$',"",$GLOBALS["COMPUTER"]);

if($GLOBALS["COMPUTER"]==null){
	echo "no computer name set {$argv[1]}!\n";
	die();
}

$users=new usersMenus();
$sock=new sockets();
$ComputersAllowNmap=$sock->GET_INFO("ComputersAllowNmap");
if($ComputersAllowNmap==null){$ComputersAllowNmap=1;}
if($ComputersAllowNmap==0){die();}


if(!is_file($users->NMAP_PATH)){
	echo "Unable to stat nmap binary file...\n";
	exit;
}

$computer=new computers($GLOBALS["COMPUTER"].'$');

echo "Scanning \"{$GLOBALS["COMPUTER"]}\":[$computer->ComputerIP]\n";

if($computer->ComputerIP=="0.0.0.0"){$computer->ComputerIP=null;}
if($computer->ComputerIP==null){$computer->ComputerIP=gethostbyname($GLOBALS["COMPUTER"]);}
if($computer->ComputerIP<>null){$cdir=$computer->ComputerIP;}else{$cdir=$GLOBALS["COMPUTER"];}

echo "Scanning $cdir and save results to /etc/artica-postfix/$cdir.map\n";

$cmd=$users->NMAP_PATH." -v -F -PE -O $cdir -oN /etc/artica-postfix/$cdir.map --system-dns --version-light >/dev/null 2>&1";

echo "Executing $cmd\n";

system($cmd);
echo "Parsing results for $cdir\n";

if(!is_file("/etc/artica-postfix/$cdir.map")){
	echo "Unable to stat /etc/artica-postfix/$cdir.map\n";
	exit;
}

parsefile("/etc/artica-postfix/$cdir.map",$GLOBALS["COMPUTER"]);   


function parsefile($filename,$uid){
	
	$datas=file_get_contents($filename);
	$tbl=explode("\n",$datas);
	if(!is_array($tbl)){return null;}
	
	while (list ($num, $ligne) = each ($tbl) ){
		
		if(preg_match("#([0-9]+).+?open\s+(.+)#",$ligne,$re)){
			$array[]=$re[1].":".$re[2];
			continue;
			
		}
		
		if(preg_match("#^Running:(.+)#",$ligne,$re)){
			$ComputerRunning=$re[1];
			continue;
		}
		
		if(preg_match("#^OS details:(.+)#",$ligne,$re)){
			$ComputerOS=$re[1];
			continue;
		}	
	if(preg_match("#^MAC Address:(.+).+?\((.+?)\)#",$ligne,$re)){
			$ComputerMacAddress=$re[1];
			$ComputerMachineType=$re[2];
			continue;
		}				
		
		
		
	if(preg_match("#^MAC Address:(.+)#",$ligne,$re)){
			$ComputerMacAddress=$re[1];
			continue;
		}		
		
		 
		
		
	}
	
	
	if($ComputerMacAddress<>null){
		$computer=new computers();
		$cpid=$computer->ComputerIDFromMAC($ComputerMacAddress);
		
	}
if($cpid==null){$cpid=$uid;}
	echo "Save infos for $cpid\n";
	echo "ComputerMacAddress: $ComputerMacAddress\n";
	echo "ComputerOS: $ComputerOS\n";
	
	
	$computer=new computers($cpid."$");
	if($ComputerMacAddress<>null){$computer->ComputerMacAddress=$ComputerMacAddress;}
	if($ComputerOS<>null){$computer->ComputerOS=$ComputerOS;}
	if($ComputerRunning<>null){$computer->ComputerRunning=$ComputerRunning;}
	if($ComputerMachineType<>null){$computer->ComputerMachineType=$ComputerMachineType;}
	if(is_array($array)){
		$computer->ComputerOpenPorts=implode("\n",$array);
	}
	$computer->Edit();
	echo $datas;
	
}
?>