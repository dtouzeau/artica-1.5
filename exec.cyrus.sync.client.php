<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");

if(!Build_pid_func(__FILE__,"MAIN")){
	writelogs(basename(__FILE__).":Already executed.. aborting the process",basename(__FILE__),__FILE__,__LINE__);
	die();
}
$_GET["LOG_FILE"]="/usr/share/artica-postfix/ressources/logs/sync_client.log";
if($argv[1]=='--silent'){events("Silent specified");$_GET["SILENT"]=true;}
$users=new usersMenus();
if(!is_file($users->ctl_mboxlist)){
	events("Unable to stat ctl_mailboxlist");
	die();
}

if(!is_file($users->cyrus_sync_client_path)){
	events("Unable to stat sync_client");
	die();
}

events("Exporting mailbox list");
system("su - cyrus -c \"$users->ctl_mboxlist -d\" >/tmp/ctl_mboxlist");

$datas=file_get_contents("/tmp/ctl_mboxlist");
$tbl=explode("\n",$datas);
if(!is_array($tbl)){
	events("FATAL ERROR");
	die();
}

while (list ($num, $ligne) = each ($tbl) ){
	if(!preg_match("#^user\..+?\s+[0-9]+\s+default(.+?)\s+#",$ligne,$re)){continue;}
	$arr[trim($re[1])]=true;
}

if(!is_array($arr)){
	events("No mailboxes here");
	die();
}

if(is_file($_GET["LOG_FILE"])){chmod($_GET["LOG_FILE"],0755);}

$count=count($arr);
$ct=0;
events("$count mailboxes here");
if(is_file($_GET["LOG_FILE"])){chmod($_GET["LOG_FILE"],0755);}

while (list ($num, $ligne) = each ($arr) ){
	$ct=$ct+1;
	events("$ct/$count sync \"$num\"");
	system($users->cyrus_sync_client_path." -u $num");
	if(is_file($_GET["LOG_FILE"])){chmod($_GET["LOG_FILE"],0755);}
	
}
if(is_file($_GET["LOG_FILE"])){chmod($_GET["LOG_FILE"],0755);}
events("done");
die();

function events($text){
			$pid=getmypid();
		if(!$_GET["SILENT"]){echo "$date [$pid]: $text\n";}
		$date=date('Y-m-d H:i:s');
		$logFile=$_GET["LOG_FILE"];
		$size=@filesize($logFile);
		if($size>5000000){@unlink($logFile);}
		$f = @fopen($logFile, 'a');
		$towrite="$date ".basename(__FILE__)."[$pid]: $text\n";
		if($_GET["DEBUG"]){echo $towrite;}
		@fwrite($f, $towrite);
		@fclose($f);	
		
		}

?>