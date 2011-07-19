<?php
	header("Pragma: no-cache");	
	header("Expires: 0");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.computers.inc');
	include_once('ressources/class.mysql.inc');
	
if(is_array($_GET)){writelogs("Receive:: ",implode("&",$_GET),'main()',__LINE__);}
	
if(isset($_GET["noop"])){receive_noop_connection();die();}
if(isset($_GET["setdrive"])){receive_directories_list();die();}
if(isset($_GET["start-backup-rsync"])){receive_rsync_start();die();}
if(isset($_GET["write-error"])){receive_rsync_error();die();}
if(isset($_GET["rsync-finish"])){receive_rsync_finish();die();}
if(isset($_GET["order-executed-id"])){receive_order_executed();die();}
if(isset($_GET["wmi-infos"])){receive_wmi();die();}

function receive_noop_connection(){
	
	$datas=base64_decode($_GET["noop"]);
	
// 00-0C-29-09-BB-77|192.168.1.248|GRAPHICS|Windows XP SP3|Windows XP|SP3|=Intel;Intel(R) Core(TM)2 Quad CPU    Q6600  @ 2.40GHz;2400;MMX|C:;34949382144
   $datas=explode("|",$datas);
   @file_put_contents("/tmp/datas.txt",implode("\n",$datas));
   
   while (list ($num, $ligne) = each ($datas) ){
   	writelogs("=>$num): $ligne",__FUNCTION__,__FILE__,__LINE__);
   }
   
   $MAC=$datas[0];
   $MAC=str_replace("-",":",$MAC);
   $LOCAL_IP=$datas[1];
   $COMPUTER_NAME=$datas[2];
   $OS_VER=$datas[3];
   $CPUAR=explode(";",$datas[6]);
   $CPU_INFO=$CPUAR[1];
   $CPU_INFO=str_replace("  "," ",$CPU_INFO);
   $domain=strtolower($datas[9]);
   $uptime=strtolower($datas[13]);
   if(!preg_match("#(.+?)\.(.+)#",$domain)){$domain=null;}
   
	
	
	$computer=new computers();
	$uid=$computer->ComputerIDFromMAC($MAC);
	writelogs("[$uid] ($COMPUTER_NAME):: mac=$MAC, ip=$LOCAL_IP, system $OS_VER ($CPU_INFO) uptime=$uptime", __FUNCTION__,__LINE__);
	
	if($uid==null){
		$computer->uid="$COMPUTER_NAME$";
		$computer->ComputerMacAddress=$MAC;
		$computer->ComputerIP=$LOCAL_IP;
		if($domain<>null){$computer->DnsZoneName=$domain;}
		$computer->ComputerRealName=$COMPUTER_NAME;
		$computer->ComputerCPU=$CPU_INFO;
		$computer->ComputerOS=$OS_VER;	
		if($uptime<>null){$computer->ComputerUpTime=$uptime;}	
		$computer->Add();
	}else{
		$computer=new computers($uid);
		$computer->ComputerIP=$LOCAL_IP;
		$computer->ComputerRealName=$COMPUTER_NAME;
		$computer->ComputerCPU=$CPU_INFO;
		$computer->ComputerOS=$OS_VER;
		$computer->ComputerMacAddress=$MAC;
		if($domain<>null){$computer->DnsZoneName=$domain;}
		if($uptime<>null){$computer->ComputerUpTime=$uptime;}
		$computer->Edit();

		
	}
	
	
	
$MAC=str_replace("-",':',$MAC);
$sql="INSERT INTO computers_events (MAC,zDate,events_type,events) VALUES('$MAC',NOW(),'communication','{success_artica_agent_updateinfo}')";
$q=new mysql();
$q->QUERY_SQL($sql,'artica_backup');


$sql="SELECT * FROM computers_tasks WHERE MAC='$MAC' AND task_enabled=1 ORDER BY ID DESC";
$results=$q->QUERY_SQL($sql,"artica_backup");
while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
	echo "<task>{$ligne["ID"]}</task><task_type>{$ligne["task_type"]}</task_type><schedule>{$ligne["schedule"]}</schedule><path>{$ligne["path"]}</path>\n";
}
	
$sql="SELECT * FROM computers_orders WHERE MAC='$MAC' ORDER BY ID DESC";
$results=$q->QUERY_SQL($sql,"artica_backup");
while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
	$ligne["parameters"]=base64_encode($ligne["parameters"]);
	echo "<order>{$ligne["ID"]}</order><task_type>{$ligne["task_type"]}</task_type><parameters>{$ligne["parameters"]}</parameters><taskid>{$ligne["taskid"]}</taskid>\n";
}

	
}




function receive_directories_list(){
	$MAC=$_GET["MAC"];
	$MAC=str_replace("-",":",$MAC);
	$drive=$_GET["setdrive"];
	$content=explode("\n",base64_decode($_POST["setdrive-content"]));
	$sql="DELETE FROM computers_drives WHERE MAC='$MAC' AND letter='$drive'";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	while (list ($num, $ligne) = each ($content) ){
		$ligne=addslashes($ligne);
		$sql="INSERT INTO computers_drives (MAC,letter,path) VALUES('$MAC','$drive','$ligne');";
		$q->QUERY_SQL($sql,"artica_backup");
		
	}
$count=count($content);	
$sql="INSERT INTO computers_events (MAC,zDate,events_type,events) 
		VALUE('$MAC',NOW(),'communication','{success_artica_agent_directorylist} \"$drive\" $count {folders}')";
		$q=new mysql();
		$q->QUERY_SQL($sql,'artica_backup');	
	
}


function receive_rsync_start(){
		$MAC=$_GET["MAC"];
		$MAC=str_replace("-",":",$MAC);
		$path=base64_decode($_GET["path"]);
		$IP=$_GET["IP"];
		$path=addslashes($path);
$sql="INSERT INTO computers_events (MAC,zDate,events_type,events) 
		VALUE('$MAC',NOW(),'backup','{artica_agent_rsync_start} \"$path\"')";
		$q=new mysql();
		$q->QUERY_SQL($sql,'artica_backup');

		$sock=new sockets();
		$RsyncPort=$sock->GET_INFO("RsyncPort");
		
		echo "[RSYNC]\n";
		echo "port=$RsyncPort\n";
}

function receive_rsync_error(){
		$MAC=$_GET["MAC"];
		$MAC=str_replace("-",":",$MAC);
		$path=base64_decode($_GET["path"]);
		$IP=$_GET["IP"];
		$error=addslashes(base64_decode($_GET["write-error"]));
		$sql="INSERT INTO computers_events (MAC,zDate,events_type,events) VALUE('$MAC',NOW(),'error','$error')";
		$q=new mysql();
		$q->QUERY_SQL($sql,'artica_backup');	
}
function receive_order_executed(){
	$MAC=$_GET["MAC"];
	$MAC=str_replace("-",":",$MAC);	
	$order=$_GET["order-executed-id"];
	$sql="DELETE FROM computers_orders WHERE ID=$order";
	$q=new mysql();
	$q->QUERY_SQL($sql,'artica_backup');
	$sql="INSERT INTO computers_events (MAC,zDate,events_type,events) VALUE('$MAC',NOW(),'order','{artica_agent_order_executed} ID:$order')";
	$q->QUERY_SQL($sql,'artica_backup');
	}

function receive_rsync_finish(){
		$MAC=$_GET["MAC"];
		$IP=$_GET["IP"];
		$MAC=str_replace("-",":",$MAC);
		$infos=explode(";",base64_decode($_GET["content"]));
		$infos[1]=round($infos[1]/1024);
		$theme="backup_finish";
		if(preg_match("#(.+?)\.#",$infos[2],$ri)){$infos[2]=$ri[1];}
		$infos[2]=round($infos[2]/1024);
		$file=$_GET["rsync-finish"];
		if(preg_match("#([0-9]+)-([0-9]+)-([0-9]+)-([0-9]+)-([0-9]+)_#",$file,$re)){
			$date="{$re[1]}-{$re[2]}-{$re[3]} {$re[4]}:{$re[5]}:00";
		}
		
		$text="{success_artica_agent_backup_finish} {$infos[0]} {files_transfered} {$infos[1]}ko {sent} {speed}: {$infos[2]} Ko/s";
		
		if(trim($infos[3])<>null){
			$theme="backup_error";
			
			$text="{failed_artica_agent_backup_finish} {$infos[3]}";
			$text=addslashes($text);
		}
		
		writelogs("date:$date ,$text",__FUNCTION__,__FILE__,__LINE__);
		
		
		$sql="INSERT INTO computers_events (MAC,zDate,events_type,events) VALUE('$MAC','$date','$theme','$text')";
		$q=new mysql();
		$q->QUERY_SQL($sql,'artica_backup');	
		if(!$q->ok){
			writelogs("mysql error $q->mysql_error",__FUNCTION__,__FILE__,__LINE__);
		}
	
}

function receive_wmi(){
	//wmi_bios.wmi 
	writelogs("wmi-infos={$_GET["wmi-infos"]}",__FUNCTION__,__FILE__,__LINE__);
  /* while (list ($num, $ligne) = each ($_GET) ){
   	writelogs("=>$num): $ligne",__FUNCTION__,__FILE__,__LINE__);
   }*/
	
writelogs(base64_decode($_POST["wmi-content"]),__FUNCTION__,__FILE__,__LINE__);	
	
	
}


?>