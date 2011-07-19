<?php
$GLOBALS["DEBUG_INCLUDES"]=false;
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;$GLOBALS["debug"]=true;}
if(preg_match("#--simule#",implode(" ",$argv))){$GLOBALS["SIMULE"]=true;$GLOBALS["SIMULE"]=true;}
if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;$GLOBALS["FORCE"]=true;}


if($argv[1]=="--remove-inetd"){RemoveFromInetd();die();}
if($argv[1]=="--add-inetd"){AddFromInetd();die();}
if($argv[1]=="--tftp-type"){echo "TFTP server:".tftpd_product()."\n";die();}
if($argv[1]=="--restart"){RemoveFromInetd();AddFromInetd();die();}


function tftpd_product(){
	$unix=new unix();
	$bin_path=$unix->find_program("in.tftpd");
	
	if($GLOBALS["VERBOSE"]){echo "bin path: $bin_path\n";}
	exec("$bin_path -V 2<&1",$results);
	while (list ($index, $line) = each ($results) ){
		if(preg_match("#^atftp-.+?#",$line)){return "ATFTP";}
		
	}
	return "UNKNOWN";
	
	
}


function RemoveFromInetd(){
	
	$unix=new unix();
	$bin_path=$unix->find_program("in.tftpd");
	$update_inted=$unix->find_program("update-inetd");
	
	
	
	
	
	if(!is_file($bin_path)){
		echo "Starting......: tftpd not installed\n";	
	}
	
	if(is_file("/etc/inetd.conf")){
		$f=explode("\n",@file_get_contents("/etc/inetd.conf"));
		$found=false;
		while (list ($index, $line) = each ($f) ){
			if(preg_match("#^tftp#",$line)){
				$found=true;
			}
		}
	
		if($found){
			echo "Starting......: tftpd removing service from inetd\n";
			shell_exec("$update_inted --disable tftp");
		
		}
		
	}
	
	if(is_file("/etc/xinetd.d/tftp")){
		echo "Starting......: tftpd removing service from xinetd\n";
		@unlink("/etc/xinetd.d/tftp");
		
	}
	
	
	
}

function AddFromInetd(){
	$unix=new unix();
	$bin_path=$unix->find_program("in.tftpd");
	$update_inted=$unix->find_program("update-inetd");
	$tftpd_product=tftpd_product();
	$tcpd_bin=$unix->find_program("tcpd");
	
	if(!is_file($bin_path)){
		echo "Starting......: tftpd not installed\n";
	}	
	echo "Starting......: tftpd type: $tftpd_product\n";
	echo "Starting......: tcpd......: $tcpd_bin\n";
	echo "Starting......: tftpd.....: $bin_path\n";
	
	$aftpd_options=" --tftpd-timeout 300 --retry-timeout 5  --mcast-port 1758 --mcast-addr 239.239.239.0-255 --mcast-ttl 1 --maxthread 100 --verbose=5  /var/lib/tftpboot";
	$aftpd_line="tftp\tdgram\tudp\twait\troot\t$tcpd_bin $bin_path $aftpd_options";
	
	
	$tftp_default_options=" -s /var/lib/tftpboot";
	$tftp_default="tftp\tdgram\tudp\twait\troot\t$bin_path $bin_path -s /var/lib/tftpboot";
	
	if($tftpd_product=="ATFTP"){
		echo "Starting......: tftpd use settings for \"Advanced TFTP server\"\n";
		$tftp_default_options=$aftpd_options;
		$tftp_default=$aftpd_line;
	}
	
	if(is_file("/etc/inetd.conf")){
		$f=explode("\n",@file_get_contents("/etc/inetd.conf"));
		$found=false;
		while (list ($index, $line) = each ($f) ){
			if(preg_match("#tftp\s+#",$line)){
				$f[$index]="tftp\tdgram\tudp\twait\troot\t$bin_path $bin_path -s /var/lib/tftpboot";
				echo "Starting......: line $index modified\n";
				
				$found=true;
				@file_put_contents("\n",@implode("\n",$f));
			}
		}
	
		if(!$found){
			$f[]="tftp\tdgram\tudp\twait\troot\t$bin_path $bin_path -s /var/lib/tftpboot";
			echo "Starting......: service added...\n";
			@file_put_contents("\n",@implode("\n",$f));
			
		}
		
		shell_exec("$update_inted --enable tftp");
	}
	
	if(is_dir("/etc/xinetd.d")){
		$xinet[]="service tftp";
		$xinet[]="{";
		$xinet[]="socket_type = dgram";
		$xinet[]="protocol = udp";
		$xinet[]="wait = yes";
		$xinet[]="user = username ; Enter your user name";
		$xinet[]="server = $bin_path";
		$xinet[]="server_args =$tftp_default_options";
		$xinet[]="per_source = 11";
		$xinet[]="cps = 100 2";
		$xinet[]="disable = no";
		$xinet[]="}"; 	
		$xinet[]="";	
		echo "Starting......: add service into xinetd...\n";
		@file_put_contents("/etc/xinetd.d/tftp",@implode("\n",$xinet));
	}
	unset($xinet);
	if(is_file("/etc/default/atftpd")){
		$xinet[]="USE_INETD=true";
		$xinet[]="OPTIONS=\"--daemon --port 69 --tftpd-timeout 300 --retry-timeout 5     --mcast-port 1758 --mcast-addr 239.239.239.0-255 --mcast-ttl 1 --maxthread 100 --verbose=5  /var/lib/tftpboot\"";
		echo "Starting......: update /etc/default/atftpd...\n";
		@file_put_contents("/etc/default/atftpd",@implode("\n",$xinet));	
	}
	
	
	
}