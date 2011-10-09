<?php
$GLOBALS["FORCE"]=false;
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');
include_once(dirname(__FILE__).'/ressources/class.sockets.inc');
include_once(dirname(__FILE__).'/ressources/class.ccurl.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
if(!is_file("/usr/share/artica-postfix/ressources/settings.inc")){shell_exec("/usr/share/artica-postfix/bin/process1 --force --verbose");}
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["DEBUG"]=true;$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}
if(preg_match("#--reload#",implode(" ",$argv))){$GLOBALS["RELOAD"]=true;}
if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;}

$sock=new sockets();


$pidfile="/etc/artica-postfix/pids/".basename(__FILE__).".pid";
$oldpid=@file_get_contents($pidfile);
$unix=new unix();
if($unix->process_exists($oldpid,basename(__FILE__))){writelogs("Process $oldpid already exists","MAIN",__FILE__,__LINE__);return;}
@file_put_contents($pidfile, getmypid());


if($argv[1]=="--checks"){checkupdates();exit;}


checkupdates();
function checkupdates(){
	$unix=new unix();
	if(!$GLOBALS["FORCE"]){
		$timefile="/etc/artica-postfix/pids/".basename(__FILE__).".".__FUNCTION__.".time";
		if($unix->file_time_min($timefile)<60){return;}
	}
	
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$configDisk=trim($sock->GET_INFO('ArticaAutoUpdateConfig'));	
	$ini->loadString($configDisk);	
	$AUTOUPDATE=$ini->_params["AUTOUPDATE"];
	if(trim($AUTOUPDATE["uri"])==null){$AUTOUPDATE["uri"]="http://www.artica.fr/auto.update.php";}
	if(trim($AUTOUPDATE["enabled"])==null){$AUTOUPDATE["enabled"]="yes";}
	if(trim($AUTOUPDATE["autoinstall"])==null){$AUTOUPDATE["autoinstall"]="yes";}
	if($GLOBALS["FORCE"]){$AUTOUPDATE["autoinstall"]="yes";}
	

	
	$tr=explode("/", $AUTOUPDATE["uri"]);
	unset($tr[count($tr)-1]);
	$uri=implode("/", $tr)."/patchs.php";
	if($GLOBALS["VERBOSE"]){echo "URI: $uri\n";}
	
	$curl=new ccurl($uri);
	$curl->GetFile("/tmp/patchs.txt");
	
	$datas=base64_decode(@file_get_contents("/tmp/patchs.txt"));
	
	if($GLOBALS["VERBOSE"]){echo "datas decoded\n$datas\n";}
	$array=unserialize($datas);
	if(!is_array($array)){if($GLOBALS["VERBOSE"]){echo "No patchs available not an array\n";}}
	
	$prefix="INSERT IGNORE INTO artica_patchs (patch_number,path_explain,filename,`size`) VALUES ";
	while (list ($patch_number, $line) = each ($array) ){
		$line["TEXT"]=addslashes($line["TEXT"]);
		if($line["SIZE"]==0){if($GLOBALS["VERBOSE"]){echo "SIZE=0 !!!\n";}continue;}
		$f[]="('$patch_number','{$line["TEXT"]}','{$line["FILENAME"]}','{$line["SIZE"]}')";
		
	}
	
	
	
	if(count($f)>0){$q=new mysql();$q->QUERY_SQL($prefix.@implode(",", $f),"artica_backup");}
	$EnablePatchUpdates=$sock->GET_INFO("EnablePatchUpdates");
	if(!is_numeric($EnablePatchUpdates)){$EnablePatchUpdates=0;}
	if(!$GLOBALS["FORCE"]){if($EnablePatchUpdates<>1){return;}}
	
	if($AUTOUPDATE["autoinstall"]=="yes"){UpdatePatches();exit;}
}
function UpdatePatches(){
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$configDisk=trim($sock->GET_INFO('ArticaAutoUpdateConfig'));	
	$ini->loadString($configDisk);	
	$AUTOUPDATE=$ini->_params["AUTOUPDATE"];
	if(trim($AUTOUPDATE["uri"])==null){$AUTOUPDATE["uri"]="http://www.artica.fr/auto.update.php";}
	if(trim($AUTOUPDATE["enabled"])==null){$AUTOUPDATE["enabled"]="yes";}
	if(trim($AUTOUPDATE["autoinstall"])==null){$AUTOUPDATE["autoinstall"]="yes";}
	$tr=explode("/", $AUTOUPDATE["uri"]);
	unset($tr[count($tr)-1]);
	$uri=implode("/", $tr);
	if($GLOBALS["VERBOSE"]){echo "URI: $uri\n";}	
	$q=new mysql();
	$unix=new unix();
	$myversion=trim(@file_get_contents("/usr/share/artica-postfix/VERSION"));
	$myversionbin=str_replace(".", "", $myversion);
	if($GLOBALS["VERBOSE"]){echo "$myversionbin = $myversion\n";}
	
	$sql="SELECT * FROM artica_patchs WHERE patch_number>$myversionbin AND updated=0 ORDER BY patch_number";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){writelogs("Fatal Error: $this->mysql_error",__FUNCTION__,__FILE__,__LINE__);return array();}
	if($GLOBALS["VERBOSE"]){echo $sql." => ". mysql_num_rows($results)."\n";}
		
	$nohup=$unix->find_program("nohup");
	$tar=$unix->find_program("tar");
	$killall=$unix->find_program("killall");
	$update=false;
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$filename=$ligne["filename"];
		$patch_number=$ligne["patch_number"];
		if($GLOBALS["VERBOSE"]){echo "Downloading $uri/patchs/$filename...\n";}
		$curl=new ccurl("$uri/patchs/$filename");
		if(!$curl->GetFile("/tmp/$filename")){$unix->send_email_events("Unable to download patch $patch_number", $curl->error, "update");return;}
		$size=@filesize("/tmp/$filename");
		if($size<>$ligne["size"]){if($GLOBALS["VERBOSE"]){echo "Corrupted patch file $filename aborting...\n";}$unix->send_email_events("Corrupted patch file $filename aborting...", $curl->error, "update");return;}
		shell_exec("$killall artica-install >/dev/null 2>&1");
		shell_exec("$killall artica-update >/dev/null 2>&1");
		shell_exec("$killall process1 >/dev/null 2>&1");
		shell_exec("$tar -xf /tmp/$filename -C /usr/share/artica-postfix/");
		@unlink("/tmp/$filename");
		$update=true;
		$unix->send_email_events("Success apply patch number $patch_number", $ligne["path_explain"], "update");
		$sql="UPDATE artica_patchs SET updated=1 WHERE patch_number='$patch_number'";
		$q->QUERY_SQL($sql,"artica_backup");
	}	
	
	if($update){
		shell_exec("$nohup /etc/init.d/artica-postfix restart apache >/dev/null 2>&1 &");
		shell_exec("$nohup /etc/init.d/artica-postfix restart artica-status >/dev/null 2>&1 &");
		shell_exec("$nohup /etc/init.d/artica-postfix restart artica-back >/dev/null 2>&1 &");
		shell_exec("$nohup /etc/init.d/artica-postfix restart artica-exec >/dev/null 2>&1 &");
	}
	
	$sql="UPDATE artica_patchs SET updated=1 WHERE patch_number<=$myversionbin";
	$q->QUERY_SQL($sql,"artica_backup");
	
}


