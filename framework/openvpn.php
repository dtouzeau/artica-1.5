<?php
include_once(dirname(__FILE__)."/frame.class.inc");
include_once(dirname(__FILE__)."/class.unix.inc");
include_once(dirname(__FILE__)."/class.postfix.inc");

if(isset($_GET["build-vpn-user"])){BuildWindowsClient();exit;}
if(isset($_GET["restart-clients"])){RestartClients();exit;}
if(isset($_GET["restart-clients-tenir"])){RestartClientsTenir();exit;}
if(isset($_GET["is-client-running"])){vpn_client_running();exit;}
if(isset($_GET["client-events"])){vpn_client_events();exit;}
if(isset($_GET["client-reconnect"])){vpn_client_hup();exit;}
if(isset($_GET["client-reconfigure"])){vpn_client_reconfigure();exit;}
if(isset($_GET["certificate-infos"])){certificate_infos();}

function certificate_infos(){
	$unix=new unix();
	$openssl=$unix->find_program("openssl");
	$l=$unix->FILE_TEMP();
	$cmd="$openssl x509 -in /etc/artica-postfix/openvpn/keys/vpn-server.key -text -noout >$l 2>&1";
	
	if($cmd<>null){
		shell_exec($cmd);
		$datas=explode("\n",@file_get_contents($l));
		writelogs_framework($cmd." =".count($datas)." rows",__FUNCTION__,__FILE__,__LINE__);
		@unlink($l);
	}
	echo "<articadatascgi>". base64_encode(serialize($datas))."</articadatascgi>";
}


function RestartClients(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup ".LOCATE_PHP5_BIN2() ." /usr/share/artica-postfix/exec.openvpn.php --client-restart >/dev/null 2>&1 &");
	shell_exec($cmd);
	}
	
function RestartClientsTenir(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.openvpn.php --client-restart",$results);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
	
}
	
function vpn_client_running(){
	if(isset($_GET["verbose"])){$GLOBALS["VERBOSE"]=true;}
	$id=$_GET["is-client-running"];
	$pid=trim(@file_get_contents("/etc/artica-postfix/openvpn/clients/$id/pid"));
	$unix=new unix();
	writelogs_framework("/etc/artica-postfix/openvpn/clients/$id/pid -> $pid",__FUNCTION__,__FILE__,__LINE__);
	
	if($unix->process_exists($pid)){
		echo "<articadatascgi>TRUE</articadatascgi>";
		return;
	}
	writelogs_framework("$id: pid $pid",__FUNCTION__,__FILE__,__LINE__);
	
	exec($unix->find_program("pgrep") ." -l -f \"openvpn.+?clients\/2\/settings.ovpn\" 1>&1",$results);
	while (list ($num, $ligne) = each ($results) ){
		if(preg_match("#^([0-9]+)\s+.*openvpn#",$ligne)){
			writelogs_framework("pid= preg_match= {$re[1]}",__FUNCTION__,__FILE__,__LINE__);
			echo "<articadatascgi>TRUE</articadatascgi>";
			return;
		}
	}
	writelogs_framework("$pid NOT RUNNING",__FUNCTION__,__FILE__,__LINE__);
}	


function BuildWindowsClient(){
	if(isset($_GET["site-id"])){$site_id=$_GET["site-id"];}
	if(isset($_GET["verbose"])){$GLOBALS["VERBOSE"]=true;}
	$commonname=$_GET["build-vpn-user"];
	$basepath=$_GET["basepath"];
	$unix=new unix();
	@mkdir($basepath,0755,true);
	$workingDir="/etc/artica-postfix/openvpn/$commonname";
	@mkdir($workingDir);
	if(!is_file('/usr/bin/zip')){
		echo "<articadatascgi>ERROR: unable to stat \"zip\", please advise your Administrator</articadatascgi>";
		exit;
	}
	
	if(!is_file("/etc/artica-postfix/settings/Daemons/$commonname.ovpn")){
		echo "<articadatascgi>ERROR: unable to stat \"$commonname.ovpn\", please advise your Administrator</articadatascgi>";
		exit;
	}
	
	
	$filesize=filesize("/etc/artica-postfix/settings/Daemons/$commonname.ovpn");
	if($filesize==0){
		echo "<articadatascgi>ERROR: corrupted \"$commonname.ovpn\" 0 bytes, please advise your Administrator</articadatascgi>";
		exit;
	}	
	
	
	
	
	
	echo "<articadatascgi>";
	echo "$commonname.ovpn: ". filesize("/etc/artica-postfix/settings/Daemons/$commonname.ovpn")." bytes length\n";
	
	
	$password=trim(@file_get_contents("/etc/artica-postfix/settings/Daemons/OpenVpnPasswordCert"));
	if($password==null){$password="MyKey";}
	
	$zipfile=$basepath."/ressources/logs/$commonname.zip";
	@mkdir("$basepath/ressources/logs",0755,true);
	
	if(!ChangeCommonName($commonname)){exit;}
	if(is_file($zipfile)){@unlink($zipfile);}
	
	$config_path="/etc/artica-postfix/openvpn/openssl.cnf";
	//if(is_file("/etc/artica-postfix/ssl.certificate.conf")){$config_path="/etc/artica-postfix/ssl.certificate.conf";}
	
       
    chdir('/etc/artica-postfix/openvpn');
    $filetemp=$unix->FILE_TEMP();
    shell_exec("source ./vars");   
    copy("/etc/artica-postfix/openvpn/keys/allca.crt","$workingDir/$commonname-ca.crt");
    copy("/etc/artica-postfix/settings/Daemons/$commonname.ovpn","$workingDir/$commonname.ovpn"); 
    @unlink("/etc/artica-postfix/openvpn/$commonname.ovpn");
    @unlink("/etc/artica-postfix/openvpn/keys/index.txt");
    shell_exec("/bin/touch /etc/artica-postfix/openvpn/keys/index.txt");
    
    if($GLOBALS["VERBOSE"]){
    	echo "keyout: $workingDir/$commonname.key\n";
    	echo "Keyfile: /etc/artica-postfix/openvpn/keys/openvpn-ca.key\n";
    	echo "/etc/artica-postfix/openvpn/keys/openvpn-ca.crt\n";
    	echo "config: $ssl_config_path\n";
    	echo "$workingDir/$commonname.csr\n";
    	
    }
    
    $cmd="echo 01 > /etc/artica-postfix/openvpn/keys/serial";
    $CMDLOGS[]=$cmd;
	shell_exec("$cmd");       
	echo @file_get_contents($filetemp);
    
    $cmd="openssl req -batch -days 3650 -nodes -new -newkey rsa:1024 -keyout \"$workingDir/$commonname.key\" -out \"$workingDir/$commonname.csr\" -config \"$ssl_config_path\"";   
    $cmd="openssl req -nodes -new -keyout \"$workingDir/$commonname.key\" -out \"$workingDir/$commonname.csr\" -batch -config $config_path";
    
    
    
    if($GLOBALS["VERBOSE"]){echo "$cmd\n";}else{echo substr($cmd,0,60)."...\n";}
    $CMDLOGS[]=$cmd;
	exec("$cmd 2>&1",$results);
	while (list ($num, $ligne) = each ($results) ){echo $ligne."\n";$CMDLOGS[]=$ligne;}
	       
	
	
	$server_ca="/etc/artica-postfix/openvpn/keys/openvpn-ca.key";
	//$server_ca="/etc/artica-postfix/openvpn/keys/vpn-server.key";
	$servercert="/etc/artica-postfix/openvpn/keys/openvpn-ca.crt";
	//$servercert="/etc/artica-postfix/openvpn/keys/vpn-server.crt";
	
	
	$cmd="openssl ca -batch -days 3650 -out \"$workingDir/$commonname.crt\" -in \"$workingDir/$commonname.csr\" -md sha1 -config \"$config_path\"";
	$cmd="openssl ca -keyfile $server_ca -cert $servercert";
	$cmd=$cmd." -out \"$workingDir/$commonname.crt\" -in \"$workingDir/$commonname.csr\" -batch -config $config_path  -passin pass:$password";
	
	if($GLOBALS["VERBOSE"]){echo "$cmd\n";}else{echo substr($cmd,0,60)."...\n";}
	$CMDLOGS[]=$cmd;$results=array();
	exec("$cmd 2>&1",$results);
	while (list ($num, $ligne) = each ($results) ){echo $ligne."\n";$CMDLOGS[]=$ligne;}
	   
	echo @file_get_contents($filetemp);
	  $mycurrentdir=getcwd();
	  chdir($workingDir);
      @file_put_contents("$workingDir/password",$password);
	  
	  $cmd="/usr/bin/zip $zipfile";
      
      $cmd=$cmd. " $commonname.crt $commonname.csr $commonname.key $commonname.ovpn $commonname-ca.crt password >$filetemp 2>&1";;
	  if($GLOBALS["VERBOSE"]){echo "$cmd\n";}else{echo substr($cmd,0,60)."...\n";}
	  $CMDLOGS[]=$cmd;
      shell_exec($cmd);
      chdir($mycurrentdir);
      echo @file_get_contents($filetemp);
      
   @chmod($zipfile,0755);
   @unlink($filetemp);
   @unlink("$workingDir/$commonname-ca.crt");
   @unlink("$workingDir/$commonname.crt");
   @unlink("$workingDir/$commonname.csr");
   @unlink("$workingDir/$commonname.key");
   @unlink("$workingDir/$commonname.ovpn");
   @unlink("$workingDir/password");		
    echo "----------------------------------\n";
    echo "{success} !!!\n";
    echo "----------------------------------\n";
	echo "</articadatascgi>";
	@file_put_contents("/root/openss.cmds", @implode("\n", $CMDLOGS));
}


function ChangeCommonName($commonname){

if(!is_file("/etc/artica-postfix/openvpn/openssl.cnf")){
	echo "<articadatascgi>ERROR: Unable to stat /etc/artica-postfix/openvpn/openssl.cnf</articadatascgi>";
	return false;
}
	
$tbl=explode("\n",@file_get_contents("/etc/artica-postfix/openvpn/openssl.cnf"));
while (list ($num, $ligne) = each ($tbl) ){
	if(preg_match("#^commonName_default#",$ligne)){
		$tbl[$num]="commonName_default=\t$commonname";
	}
}

@file_put_contents("/etc/artica-postfix/openvpn/openssl.cnf",implode("\n",$tbl));
return true;
}

function vpn_client_events(){
	$unix=new unix();
	$php=$unix->LOCATE_PHP5_BIN();
	$tail=$unix->find_program("tail");
	$cmd=trim("$tail -n 300 /etc/artica-postfix/openvpn/clients/{$_GET["ID"]}/log 2>&1 ");
	
	exec($cmd,$results);		
	writelogs_framework($cmd ." ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
}

function vpn_client_hup(){
	$pid=@file_get_contents("/etc/artica-postfix/openvpn/clients/{$_GET["ID"]}/pid");
	$unix=new unix();
	$kill=$unix->find_program("kill");
	$php=$unix->LOCATE_PHP5_BIN();
	$nohup=$unix->find_program("nohup");		
	$cmd=trim("$nohup $php /usr/share/artica-postfix/exec.openvpn.php --client-configure-start {$_GET["ID"]} 2>&1 &");
	if($unix->process_exists($pid)){shell_exec("$kill -9 $pid");}
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec("$cmd");	
	
}

function vpn_client_reconfigure(){
	$unix=new unix();
	$php=$unix->LOCATE_PHP5_BIN();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup $php /usr/share/artica-postfix/exec.openvpn.php --client-conf 2>&1 &");
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec("$cmd");
	
}


?>