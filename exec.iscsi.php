<?php
$GLOBALS["VERBOSE"]=false;
$GLOBALS["NORELOAD"]=false;
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.samba.inc');
include_once(dirname(__FILE__).'/ressources/class.autofs.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.user.inc');
include_once(dirname(__FILE__) . '/framework/class.unix.inc');
include_once(dirname(__FILE__) . '/framework/frame.class.inc');

if(is_array($argv)){
	if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
	if(preg_match("#--no-reload#",implode(" ",$argv))){$GLOBALS["NORELOAD"]=true;}
	if($GLOBALS["VERBOSE"]){ini_set_verbosed();}
}

if($argv[1]=="--build"){build();die();}
if($argv[1]=="--clients"){clients();die();}
if($argv[1]=="--stat"){statfile($argv[2]);die();}

function statfile($path){
	echo "$path\n---------------------------------\n";
	$array=stat($path);
	print_r($array);
	echo filetype($path)."\n";
	if(!is_file($path)){echo "is_file:false\n";}
	print_r( pathinfo($path));
}


function build(){
	
	$unix=new unix();
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__."pid";
	$pid=@file_get_contents($pidfile);
	if($unix->process_exists($pid)){
		echo "Already process exists $pid\n";
		return;
	}
	
	@file_put_contents($pidfile,getmypid());	
	$year=date('Y');
	$month=date('m');
	
	$sql="SELECT * FROM iscsi_params ORDER BY ID DESC";
	$q=new mysql();
	$c=0;
	$dd=$unix->find_program("dd");
	$results=$q->QUERY_SQL($sql,'artica_backup');
	
	if(!$q->ok){
		echo "Starting......: ietd $q->mysql_error\n";
		return;		
	}
	
	if(mysql_num_rows($results)==0){
		echo "Starting......: ietd no iSCSI disk scheduled\n";
		return;
	}
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){	
		$hostname=$ligne["hostname"];
		$artica_type=$ligne["type"];
		$tbl=explode(".",$hostname);
		
		echo "Starting......: ietd [{$ligne["ID"]}] ressource type:$artica_type {$ligne["dev"]}\n";
		
		if($artica_type=="file"){
			if(!stat_system($ligne["dev"])){
				echo "Starting......: ietd [{$ligne["ID"]}] creating file {$ligne["dev"]} {$ligne["file_size"]}Go\n";
				$countsize=$ligne["file_size"]*1000;
				$cmd="$dd if=/dev/zero of={$ligne["dev"]} bs=1M count=$countsize";
				if($GLOBALS["VERBOSE"]){echo "Starting......: ietd [{$ligne["ID"]}] $cmd\n";}
				shell_exec($cmd);
				if(!stat_system($ligne["dev"])){
					echo "Starting......: ietd [{$ligne["ID"]}] failed\n";
					continue;
				}
			}
		}
		
		krsort($tbl);
		$newhostname=@implode(".",$tbl);
		$Params=unserialize(base64_decode($ligne["Params"]));
		if(!isset($Params["ImmediateData"])){$Params["ImmediateData"]=1;}
		if(!isset($Params["MaxConnections"])){$Params["MaxConnections"]=1;}
		if(!isset($Params["Wthreads"])){$Params["Wthreads"]=8;}
		if(!isset($Params["IoType"])){$Params["IoType"]="fileio";}
		if(!isset($Params["mode"])){$Params["mode"]="wb";}

		
		if(!is_numeric($Params["MaxConnections"])){$Params["MaxConnections"]=1;}
		if(!is_numeric($Params["ImmediateData"])){$Params["ImmediateData"]=1;}
		if(!is_numeric($Params["Wthreads"])){$Params["Wthreads"]=8;}
		if($Params["IoType"]==null){$Params["IoType"]="fileio";}
		if($Params["mode"]==null){$Params["mode"]="wb";}
		$EnableAuth=$ligne["EnableAuth"];	
		$uid=trim($ligne["uid"]);

		if($GLOBALS["VERBOSE"]){echo "Starting......: ietd [{$ligne["ID"]}] EnableAuth={$ligne["EnableAuth"]}\n";}
		if($GLOBALS["VERBOSE"]){echo "Starting......: ietd [{$ligne["ID"]}] uid=\"$uid\"\n";}
		
		if($Params["ImmediateData"]==1){$Params["ImmediateData"]="Yes";}else{$Params["ImmediateData"]="No";}
		
		$f[]="Target iqn.$year-$month.$newhostname:{$ligne["shared_folder"]}";
		if($EnableAuth==1){
			if(strlen($uid)>2){
				echo "Starting......: ietd Authentication enabled for {$ligne["dev"]} with member {$ligne["uid"]}\n";
				$user=new user($ligne["uid"]);
				if($user->password<>null){
					$f[]="\tIncomingUser {$ligne["uid"]} $user->password";
				}
			}
		}
		$f[]="\tLun $c Path={$ligne["dev"]},Type={$Params["IoType"]},IOMode={$Params["mode"]}";
		$f[]="\tMaxConnections {$Params["MaxConnections"]}";
		$f[]="\tImmediateData {$Params["MaxConnections"]}";
		$f[]="\tWthreads {$Params["Wthreads"]}";
		$f[]="";
		
		
		$c++;
	}
	
	@mkdir("/etc/iet",true,0600);
	
	@file_put_contents("/etc/iet/ietd.conf",@implode("\n",$f));
	@file_put_contents("/etc/ietd.conf",@implode("\n",$f));
	
	if(is_file("/etc/init.d/iscsitarget")){shell_exec("/etc/init.d/iscsitarget restart");}
	
}

function clients(){
	$unix=new unix();
	$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__."pid";
	$pid=@file_get_contents($pidfile);
	if($unix->process_exists($pid)){
		echo "Already process exists $pid\n";
		return;
	}
	
	$iscsiadm=$unix->find_program("iscsiadm");
	if(!is_file($iscsiadm)){
		if($GLOBALS["VERBOSE"]){echo "Starting......: iscsiadm no such file\n";}
		return;
	}	
	
	@file_put_contents($pidfile,getmypid());
	
	
	
$f[]="node.startup = automatic";
$f[]="#node.session.auth.authmethod = CHAP";
$f[]="#node.session.auth.username = username";
$f[]="#node.session.auth.password = password";
$f[]="#node.session.auth.username_in = username_in";
$f[]="#node.session.auth.password_in = password_in";
$f[]="#discovery.sendtargets.auth.authmethod = CHAP";
$f[]="#discovery.sendtargets.auth.username = username";
$f[]="#discovery.sendtargets.auth.password = password";
$f[]="#discovery.sendtargets.auth.username_in = username_in";
$f[]="#discovery.sendtargets.auth.password_in = password_in";
$f[]="node.session.timeo.replacement_timeout = 120";
$f[]="node.conn[0].timeo.login_timeout = 15";
$f[]="node.conn[0].timeo.logout_timeout = 15";
$f[]="node.conn[0].timeo.noop_out_interval = 10";
$f[]="node.conn[0].timeo.noop_out_timeout = 15";
$f[]="node.session.initial_login_retry_max = 4";
$f[]="#node.session.iscsi.InitialR2T = Yes";
$f[]="node.session.iscsi.InitialR2T = No";
$f[]="#node.session.iscsi.ImmediateData = No";
$f[]="node.session.iscsi.ImmediateData = Yes";
$f[]="node.session.iscsi.FirstBurstLength = 262144";
$f[]="node.session.iscsi.MaxBurstLength = 16776192";
$f[]="node.conn[0].iscsi.MaxRecvDataSegmentLength = 131072";
$f[]="discovery.sendtargets.iscsi.MaxRecvDataSegmentLength = 32768";
$f[]="#node.conn[0].iscsi.HeaderDigest = CRC32C,None";
$f[]="#node.conn[0].iscsi.DataDigest = CRC32C,None";
$f[]="#node.conn[0].iscsi.HeaderDigest = None,CRC32C";
$f[]="#node.conn[0].iscsi.DataDigest = None,CRC32C";
$f[]="#node.conn[0].iscsi.HeaderDigest = CRC32C";
$f[]="#node.conn[0].iscsi.DataDigest = CRC32C";
$f[]="#node.conn[0].iscsi.HeaderDigest = None";
$f[]="#node.conn[0].iscsi.DataDigest = None";
$f[]="";	

@file_put_contents("/etc/iscsi/iscsid.conf",@implode("\n",$f));
	
	
	
	
	
	$sql="SELECT * FROM iscsi_client";
	if($GLOBALS["VERBOSE"]){echo "Starting......: iscsiadm $sql\n";}
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,'artica_backup');
	
	if(!$q->ok){
		echo "Starting......: iscsiadm $q->mysql_error\n";
		return;		
	}
	
	if(mysql_num_rows($results)==0){
		echo "Starting......: iscsiadm no iSCSI disk connection scheduled\n";
		return;
	}	
	
	
	if(!$q->ok){if($GLOBALS["VERBOSE"]){echo "Starting......: $q->mysql_error\n";}return;}
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){		
		$subarray2=unserialize(base64_decode($ligne["Params"]));	
		$iqn="{$subarray2["ISCSI"]}:{$subarray2["FOLDER"]}";
		$port=$subarray2["PORT"];
		$ip=$subarray2["IP"];
		echo "Starting......: $iqn -> $ip:$port Auth:{$ligne["EnableAuth"]} Persistane:{$ligne["Persistante"]}\n";
		if($ligne["EnableAuth"]==1){
			$cmds[]="$iscsiadm -m node --targetname $iqn -p $ip:$port -o update -n node.session.auth.username -v \"{$ligne["username"]}\" 2>&1";
			$cmds[]="$iscsiadm -m node --targetname $iqn -p $ip:$port -o update -n node.session.auth.password -v \"{$ligne["password"]}\" 2>&1";
		}else{
			$cmds[]="$iscsiadm -m node --targetname $iqn -p $ip:$port --login 2>&1";
		}
		
		if($ligne["Persistante"]==1){
			$cmds[]="$iscsiadm -m node --targetname $iqn -p $ip:$port -o update -n node.startup -v automatic 2>&1";
		}else{
			$cmds[]="$iscsiadm -m node --targetname $iqn -p $ip:$port -o update -n node.startup -v manual 2>&1";
		}
		
		$cmds[]="$iscsiadm -m node --logoutall all 2>&1";
		
	}
		

	if(is_array($cmds)){
		while (list ($num, $line) = each ($cmds)){
			if($GLOBALS["VERBOSE"]){echo "--------------------------\n$line\n";}
			$results=array();
			exec($line,$results);
			if($GLOBALS["VERBOSE"]){@implode("\n",$results);}
		}
		
	}
	
	if($GLOBALS["VERBOSE"]){echo "--------------------------\n$iscsiadm -m node --loginall all\n";}
	shell_exec("$iscsiadm -m node --loginall all");
	$unix->THREAD_COMMAND_SET("/usr/share/artica-postfix/bin/artica-install --usb-scan-write");
	
		
		
}

function stat_system($path){
	exec("stat -f $path -c %b 2>&1",$results);
	$line=trim(@implode("",$results));
	if(preg_match("#^[0-9]+#",$line,$results)){return true;}
	return false;
}

