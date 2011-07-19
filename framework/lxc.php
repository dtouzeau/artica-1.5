<?php
include_once(dirname(__FILE__)."/frame.class.inc");
include_once(dirname(__FILE__)."/class.unix.inc");
include_once(dirname(__FILE__)."/class.postfix.inc");

if(isset($_GET["lxc-version"])){lxc_version();exit;}
if(isset($_GET["install-bridge"])){install_bridge();exit;}
if(isset($_GET["uninstall-bridge"])){uninstall_bridge();exit;}
if(isset($_GET["check-master"])){check_lxc();exit;}
if(isset($_GET["single-status"])){status_single();exit;}
if(isset($_GET["lxc-ps"])){lxc_ps();exit;}
if(isset($_GET["vps-restart"])){vps_restart();exit;}
if(isset($_GET["vps-stop"])){vps_stop();exit;}
if(isset($_GET["vps-start"])){vps_start();exit;}
if(isset($_GET["vps-running"])){vps_running();exit;}
if(isset($_GET["lxc-events"])){lxc_events();exit;}
if(isset($_GET["vps-reconfig"])){lxc_reconfig();exit;}
if(isset($_GET["lxc-templates"])){lxc_templates();exit;}
if(isset($_GET["artica-make"])){artica_make();exit;}
if(isset($_GET["artica-make-status"])){artica_make_status();exit;}
if(isset($_GET["IsArtica"])){IsArtica();exit;}
if(isset($_GET["tpl-delete"])){lxc_templates_delete();exit;}
if(isset($_GET["checkconfig"])){checkconfig();exit;}
if(isset($_GET["vps-freeze"])){vps_freeze();exit;}
if(isset($_GET["vps-unfreeze"])){vps_unfreeze();exit;}



while (list ($num, $ligne) = each ($_GET) ){$a[]="$num=$ligne";}
writelogs_framework("unable to unserstand ".@implode("&",$a),__FUNCTION__,__FILE__,__LINE__);

function lxc_version(){
	$unix=new unix();
	$lxc_version=$unix->find_program("lxc-version");
	if(!is_file($lxc_version)){return;}
	exec("$lxc_version 2>&1",$results);
	$pattern=@implode("",$results);
	if(preg_match("#([0-9\.]+)#",$pattern,$re)){
		echo "<articadatascgi>". $re[1]."</articadatascgi>";
	}
}

function checkconfig(){
	$unix=new unix();
	$lxc_checkconfig=$unix->find_program("lxc-checkconfig");
	writelogs_framework("$lxc_checkconfig",__FUNCTION__,__FILE__,__LINE__);
	$cmd="$lxc_checkconfig 2>&1";
	exec($cmd,$results);
	writelogs_framework("$cmd ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	while (list ($num, $ligne) = each ($results) ){
		$ligne=str_replace("[0;39m","",$ligne);
		$ligne=str_replace("[0;39m","",$ligne);
		$ligne=str_replace("[1;33m","",$ligne);
		if(preg_match("#Broken pipe#",$ligne)){continue;}
		
		
		if(preg_match("#(.+?):(.+)#",$ligne,$re)){
			
			$re[1]=trim($re[1]);
			if($re[1]=="Note"){continue;}
			if($re[1]=="usage"){continue;}
			$value=trim($re[2]);
			if(preg_match("#enabled#",$re[2])){
				writelogs_framework("{$re[1]}=TRUE",__FUNCTION__,__FILE__,__LINE__);
				$array[trim($re[1])]=true;
			}else{
				writelogs_framework("{$re[1]}=FALSE {$re[2]}",__FUNCTION__,__FILE__,__LINE__);
				$array[trim($re[1])]=false;
			}
		}
		
		
	}
	echo "<articadatascgi>". base64_encode(serialize($array))."</articadatascgi>";
} 

function artica_make(){
	$unix=new unix();
	$cachefile="/usr/share/artica-postfix/ressources/install/{$_GET["artica-make"]}.dbg";
	@unlink($cachefile);
	@file_put_contents($cachefile,"#");
	shell_exec("/bin/chmod 777 $cachefile");
	$nohup=$unix->find_program("nohup");
	$cmd="$nohup /usr/share/artica-postfix/bin/artica-make {$_GET["artica-make"]} > $cachefile 2>&1 &";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	shell_exec(trim($cmd));
}

function artica_make_status(){
	$cachefile=$_GET["artica-make-status"];
	$unix=new unix();
	$pgrep=$unix->find_program("pgrep");
	$cmd="$pgrep -f \"artica-make $cachefile\" -l 2>&1";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	exec("$cmd",$results);
	while (list ($num, $ligne) = each ($results) ){
		if(preg_match("#([0-9]+)\s+#",$ligne,$re)){
			if($unix->process_exists($re[1])){
				echo "<articadatascgi>TRUE</articadatascgi>";
				return;
			}
		}
	
	}
	echo "<articadatascgi>FALSE</articadatascgi>";
	
}


function lxc_reconfig(){
	$unix=new unix();
	$php5=$unix->LOCATE_PHP5_BIN();
	if($_GET["vps-reconfig"]>0){
		$cmd="$php5 /usr/share/artica-postfix/exec.vservers.php --vps-server-mod {$_GET["vps-reconfig"]}";
	}else{
		$cmd="$php5 /usr/share/artica-postfix/exec.vservers.php --vps-servers";
	}
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	$unix->THREAD_COMMAND_SET($cmd);	
	
}

function install_bridge(){
	$unix=new unix();
	$php5=$unix->LOCATE_PHP5_BIN();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup $php5 /usr/share/artica-postfix/exec.vservers.php --install-bridge >/dev/null 2>&1 &");
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);
	
}
function uninstall_bridge(){
	$unix=new unix();
	$php5=$unix->LOCATE_PHP5_BIN();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup $php5 /usr/share/artica-postfix/exec.vservers.php --uninstall-bridge >/dev/null 2>&1 &");
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);
}

function check_lxc(){
	$unix=new unix();
	$php5=$unix->LOCATE_PHP5_BIN();
	$cmd="$php5 /usr/share/artica-postfix/exec.vservers.php --check";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	$unix->THREAD_COMMAND_SET($cmd);	
	
}
function lxc_ps(){
	$unix=new unix();
	$php5=$unix->LOCATE_PHP5_BIN();
	$cmd="$php5 /usr/share/artica-postfix/exec.vservers.php --lxc-ps {$_GET["lxc-ps"]} 2>&1";
	exec($cmd,$results);	
	writelogs_framework("$cmd ->" .count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
	
}
function status_single(){
	$ID=$_GET["single-status"];
	$unix=new unix();
	$php5=$unix->LOCATE_PHP5_BIN();
	if(!is_file($php5)){
		$results[]="php/php5 no such file";
	}
	$cmd="$php5 /usr/share/artica-postfix/exec.vservers.php --status-single $ID --nowatchdog 2>&1";	
	exec($cmd,$results);
	writelogs_framework("$cmd ->" .count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>". base64_encode(@implode("\n",$results))."</articadatascgi>";
	
}

function vps_restart(){
	$ID=$_GET["vps-restart"];
	$unix=new unix();
	$php5=$unix->LOCATE_PHP5_BIN();
	$cmd="$php5 /usr/share/artica-postfix/exec.vservers.php --lxc-restart $ID 2>&1";
	exec($cmd,$results);	
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	writelogs_framework(@implode("\n",$results),__FUNCTION__,__FILE__,__LINE__);
}
function vps_start(){
	$ID=$_GET["vps-start"];
	$unix=new unix();
	$php5=$unix->LOCATE_PHP5_BIN();
	$cmd="$php5 /usr/share/artica-postfix/exec.vservers.php --lxc-start $ID";
	$unix->THREAD_COMMAND_SET($cmd);
}

function vps_freeze(){
	$ID=$_GET["vps-freeze"];
	$unix=new unix();
	$php5=$unix->LOCATE_PHP5_BIN();
	$nohup=$unix->find_program("nohup");
	$cmd="$php5 /usr/share/artica-postfix/exec.vservers.php --lxc-freeze $ID >/dev/null";
	shell_exec($cmd);
}
function vps_unfreeze(){
	$ID=$_GET["vps-unfreeze"];
	$unix=new unix();
	$php5=$unix->LOCATE_PHP5_BIN();
	$nohup=$unix->find_program("nohup");
	$cmd="$php5 /usr/share/artica-postfix/exec.vservers.php --lxc-unfreeze $ID >/dev/null 2>&1";
	shell_exec($cmd);	
}

function vps_stop(){
	$ID=$_GET["vps-stop"];
	$unix=new unix();
	$php5=$unix->LOCATE_PHP5_BIN();
	$cmd="$php5 /usr/share/artica-postfix/exec.vservers.php --lxc-stop $ID 2>&1";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);	
}

function vps_running(){
	$ID=$_GET["vps-running"];
	if(!isset($GLOBALS["CLASS_UNIX"])){$GLOBALS["CLASS_UNIX"]=new unix();}
	$lxcps=$GLOBALS["CLASS_UNIX"]->find_program("lxc-info");
	exec("$lxcps -n vps-$ID 2>&1",$results);
	$text=@implode("",$results);
	writelogs_framework("$lxcps -n vps-$ID 2>&1 ->$text",__FUNCTION__,__FILE__,__LINE__);
	if(preg_match("#RUNNING#",@implode("",$results))){
		echo "<articadatascgi>TRUE</articadatascgi>";
	}
	if(preg_match("#FROZEN#",@implode("",$results))){
		echo "<articadatascgi>FROZEN</articadatascgi>";
	}	
	
	
}
function lxc_events(){
	$ID=$_GET["lxc-events"];
	$LXCVpsDir=trim(@file_get_contents("/etc/artica-postfix/settings/Daemons/LXCVpsDir"));
	if($LXCVpsDir==null){$LXCVpsDir="/home/vps-servers";}
	$log="$LXCVpsDir/vps-$ID/start.log";
	$results=explode("\n",@file_get_contents($log));
	echo "<articadatascgi>". base64_encode(@serialize($results))."</articadatascgi>";
	
}

function lxc_templates(){
	$LXCVpsDir=trim(@file_get_contents("/etc/artica-postfix/settings/Daemons/LXCVpsDir"));
	if($LXCVpsDir==null){$LXCVpsDir="/home/vps-servers";}	
	$tpldir="/home/vps-servers/templates";
	$array=array();
	foreach (glob("$tpldir/*.tar.gz") as $filename) {
		
		if(preg_match("#(.+?)-minimal-(.+?)-([0-9\.]+)\.tar\.gz#",basename($filename),$re)){
			writelogs_framework("Found ". basename($filename).":{$re[1]} ,{$re[2]},{$re[3]}",__FUNCTION__,__FILE__,__LINE__);
			$array[basename($filename)]=array("TYPE"=>$re[1],"PROC"=>$re[2],"VERSION"=>$re[3]);
		}
	}
	echo "<articadatascgi>". base64_encode(@serialize($array))."</articadatascgi>";
}
function lxc_templates_delete(){
	$filename=$_GET["tpl-delete"];
	$LXCVpsDir=trim(@file_get_contents("/etc/artica-postfix/settings/Daemons/LXCVpsDir"));
	if($LXCVpsDir==null){$LXCVpsDir="/home/vps-servers";}	
	$tplfile="/home/vps-servers/templates/$filename";
	if(is_file($tplfile)){@unlink($tplfile);}
}




function IsArtica(){
	$root=$_GET["IsArtica"];
	$ID=$_GET["ID"];
	if($root==null){
		$LXCVpsDir=trim(@file_get_contents("/etc/artica-postfix/settings/Daemons/LXCVpsDir"));
		if($LXCVpsDir==null){$root="/home/vps-servers/vps-$ID/rootfs";}else{$root="$LXCVpsDir/vps-$ID/rootfs";}
	}
	if(is_file("$root/usr/share/artica-postfix/VERSION")){
		echo "<articadatascgi>".@file_get_contents("$root/usr/share/artica-postfix/VERSION")."</articadatascgi>";
	}else{
		writelogs_framework("$root/usr/share/artica-postfix/VERSION no such file",__FUNCTION__,__FILE__,__LINE__);
	}
	
}

