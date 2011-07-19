<?php

include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.computers.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;$GLOBALS["debug"]=true;}
if(preg_match("#--simule#",implode(" ",$argv))){$GLOBALS["SIMULE"]=true;$GLOBALS["SIMULE"]=true;}
if(preg_match("#--force#",implode(" ",$argv))){$GLOBALS["FORCE"]=true;$GLOBALS["FORCE"]=true;}

if($argv[1]=="--workstations"){compile_workstations();die();}

compile();

function compile(){
	
	$sql="SELECT `module` FROM thinclient_hardware_modules";
	$q=new mysql;
	$results=$q->QUERY_SQL($sql,'artica_backup');
	
$modules[]="module\tpcm";
$modules[]="module\tserial";
$modules[]="module\tacpi";
$modules[]="module\tagpgart";	
$modules[]="";	
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$modules[]="module\t".trim($ligne["module"]);
	}	
	
	
	
	$sql="SELECT `package` FROM thinclient_package_modules";
	$q=new mysql;
	$results=$q->QUERY_SQL($sql,'artica_backup');
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$packages[]="package\t".trim($ligne["package"]);
	}

	$sets[]="param bootlogo       true";
	$sets[]="param bootresolution 800x600";
	$sets[]="#param desktop	     ./background.jpg";
	$sets[]="param defaultconfig  thinstation.conf.buildtime";
	$sets[]="param basename       thinstation";
	$sets[]="param basepath       .";
	$sets[]="#param keyfile       ./id_rsa";
	$sets[]="#param knownhosts    ./known_hosts";
	$sets[]="param localpkgs      false";
	$sets[]="param fulllocales    false";
	$sets[]="param icaencryption  false";
	$sets[]="#param haltonerror   false";
	$sets[]="param bootverbosity  3"; 
	
	
	
	
	$conf=@implode("\n",$modules)."\n".@implode("\n",$packages)."\n".@implode("\n",$sets);
	
	$GLOBALS["LOGS"][]="Compiling ".count($modules)." drivers and ". count($packages)." packages";
	
	shell_exec("/bin/cp /usr/share/artica-postfix/bin/install/thinstation/boot-splash/silent-800x600.jpg /opt/thinstation/utils/tools/boot/silent-800x600.jpg");
	
	@file_put_contents("/opt/thinstation/build.conf",$conf);
	$GLOBALS["LOGS"][]="saving build.conf done...";
	compile_workstations();
	chdir("/opt/thinstation");
	@mkdir("/var/lib/tftpboot/pxe",0666,true);
	shell_exec("cd /opt/thinstation && ./build >/tmp/build.thin 2>&1");
	shell_exec("cp -rf boot-images/pxe/* /var/lib/tftpboot/pxe/");
	
	
	$GLOBALS["LOGS"][]=@file_get_contents("/tmp/build.thin");
	thinevents("Compile thinclient systems done",@implode("\n",$GLOBALS["LOGS"]));
	
	
}


function compile_workstations(){
	
	foreach (glob("/var/lib/tftpboot/thinstation.*") as $filename) {@unlink($filename);}
	$sql="SELECT * FROM thinclient_computers";
	$q=new mysql;
	$results=$q->QUERY_SQL($sql,'artica_backup');
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$cmp=new computers($ligne["uid"]);
		$mac=strtoupper($cmp->ComputerMacAddress);
		$mac=str_replace("-","",$mac);
		$mac=str_replace(":","",$mac);
		echo "Building thinclient $cmp->ComputerRealName $cmp->ComputerMacAddress\n";
		$GLOBALS["LOGS"][]="Building thinclient $cmp->ComputerRealName $cmp->ComputerMacAddress";
		$parameters=unserialize(base64_decode($ligne["parameters"]));
		if($parameters["AUDIO_LEVEL"]==null){$parameters["AUDIO_LEVEL"]=10;}
		if($parameters["USB_ENABLED"]==null){$parameters["USB_ENABLED"]=0;}
		if($parameters["DAILY_REBOOT"]==null){$parameters["DAILY_REBOOT"]=0;}
		if($parameters["CUSTOM_CONFIG"]==null){$parameters["CUSTOM_CONFIG"]=0;}
		if($parameters["RECONNECT_PROMPT"]==null){$parameters["RECONNECT_PROMPT"]="ON";}
		if($parameters["SCREEN_RESOLUTION_SEQUENCE_ENABLED"]==null){$parameters["SCREEN_RESOLUTION_SEQUENCE_ENABLED"]="1";}
		if($parameters["SCREEN_RESOLUTION_SEQUENCE"]==null){$parameters["SCREEN_RESOLUTION_SEQUENCE"]="1024x768|800x600|640x480|*";}		
		if($parameters["SCREEN_RESOLUTION"]==null){$parameters["SCREEN_RESOLUTION"]="1024x768";}
		if($parameters["SCREEN_BLANK_TIME"]==null){$parameters["SCREEN_BLANK_TIME"]="10";}
		if($parameters["SCREEN_STANDBY_TIME"]==null){$parameters["SCREEN_STANDBY_TIME"]="20";}
		if($parameters["SCREEN_SUSPEND_TIME"]==null){$parameters["SCREEN_SUSPEND_TIME"]="30";}
		if($parameters["SCREEN_OFF_TIME"]==null){$parameters["SCREEN_OFF_TIME"]="60";}		
		if($parameters["DONT_VT_SWITCH_STATE"]==null){$parameters["DONT_VT_SWITCH_STATE"]="0";}
		if($parameters["DONT_ZAP_STATE"]==null){$parameters["DONT_ZAP_STATE"]="0";}
		if($parameters["KEYBOARD_MAP"]==null){$parameters["KEYBOARD_MAP"]="us";}

		
	
		while (list ($index, $val) = each ($parameters) ){
			if($index=="AUDIO_LEVEL"){continue;}
			if($index=="SCREEN_RESOLUTION_SEQUENCE"){continue;}
			if($index=="SCREEN_RESOLUTION_SEQUENCE_ENABLED"){continue;}
			if($index=="SCREEN_RESOLUTION"){continue;}
			if($index=="SCREEN_BLANK_TIME"){continue;}
			if($index=="SCREEN_SUSPEND_TIME"){continue;}
			if($index=="SCREEN_OFF_TIME"){continue;}
			
			if(is_numeric($val)){
				if($val==0){$parameters[$index]="Off";}
				if($val==1){$parameters[$index]="On";}
			}
		}
		
		echo "Building thinclient $cmp->ComputerRealName enable sequence for screen={$parameters["SCREEN_RESOLUTION_SEQUENCE_ENABLED"]}\n";
		
		$conf[]="AUDIO_LEVEL={$parameters["AUDIO_LEVEL"]}";
		$conf[]="USB_ENABLED={$parameters["USB_ENABLED"]}";
		$conf[]="DAILY_REBOOT={$parameters["DAILY_REBOOT"]}";
		$conf[]="CUSTOM_CONFIG={$parameters["CUSTOM_CONFIG"]}";
		$conf[]="RECONNECT_PROMPT={$parameters["RECONNECT_PROMPT"]}";
		$conf[]="KEYBOARD_MAP={$parameters["KEYBOARD_MAP"]}";
		$conf[]="SCREEN=0";
		$conf[]="WORKSPACE=1";
		$conf[]="AUTOSTART=On";
		$conf[]="ICONMODE=AUTO";
		$conf[]="SCREEN_COLOR_DEPTH=\"24 | 16 | 8 | *\"";
		$conf[]="X_DRIVER_OPTION1=\"swcursor On\"";
		$conf[]="SCREEN_BLANK_TIME={$parameters["SCREEN_BLANK_TIME"]}";
		$conf[]="SCREEN_STANDBY_TIME={$parameters["SCREEN_STANDBY_TIME"]}";
		$conf[]="SCREEN_SUSPEND_TIME={$parameters["SCREEN_SUSPEND_TIME"]}";
		$conf[]="SCREEN_OFF_TIME={$parameters["SCREEN_OFF_TIME"]}";
		$conf[]="DONT_VT_SWITCH_STATE={$parameters["DONT_VT_SWITCH_STATE"]}";
		$conf[]="DONT_ZAP_STATE={$parameters["DONT_ZAP_STATE"]}";
		$conf[]="NET_HOSTNAME=$cmp->ComputerRealName";
		
		
		if($parameters["SCREEN_RESOLUTION_SEQUENCE_ENABLED"]==1){
			$conf[]="SCREEN_RESOLUTION=\"{$parameters["SCREEN_RESOLUTION_SEQUENCE"]}\"";
		}else{
			$conf[]="SCREEN_RESOLUTION=\"{$parameters["SCREEN_RESOLUTION"]}\"";
		}
		

		
		if(!is_array($parameters["SESSIONS"])){
			$GLOBALS["LOGS"][]="Building thinclient $cmp->ComputerRealName no sessions set...";
			continue;
			}
			while (list ($index, $array) = each ($parameters["SESSIONS"]) ){
				$conf[]=BuildSession($index,$array);
			}
		
		@file_put_contents("/var/lib/tftpboot/thinstation.conf-$mac",@implode("\n",$conf));
		unset($conf);
	}	
	
}


function BuildSession($index,$array){
	if($array["TITLE"]==null){$array["TITLE"]="Remote Desktop";}
	if($array["TYPE"]==null){$array["TYPE"]="rdesktop";}	
	
	$f[]="SESSION_{$index}_TITLE=\"{$array["TITLE"]}\"";
	$f[]="SESSION_{$index}_TYPE={$array["TYPE"]}";
	$f[]="SESSION_{$index}_SCREEN=1";
	$f[]="SESSION_{$index}_SCREEN_POSITION=2";
	if($array["TYPE"]=="rdesktop"){
		$f[]="SESSION_{$index}_RDESKTOP_SERVER={$array["RDESKTOP_SERVER"]}";
		if($array["username"]<>null){$cmd[]="-u '{$array["username"]}'";}
		if($array["password"]<>null){$cmd[]="-p '{$array["password"]}'";}
		if($array["domain"]<>null){$cmd[]="-d {$array["domain"]}";}
		if($array["COLOUR"]>0){$cmd[]="-a {$array["COLOUR"]}";}
		$mycommand=@implode(" ",$cmd);
		$f[]="SESSION_{$index}_RDESKTOP_OPTIONS=\"$mycommand\"";
		$f[]="SESSION_{$index}_AUTOSTART=On";
	}

	return @implode("\n",$f);
		
}



function thinevents($subject,$text){
	$text=addslashes($text);
	$sql="INSERT INTO thinclient_compile_logs (`zdate`,`subject`,`event`) VALUES(NOW(),'$subject','$text')";
	$q=new mysql();
	$q->QUERY_SQL($sql,'artica_backup');
	if(!$q->ok){writelogs("$q->mysql_error",__FUNCTION__,__FILE__,__LINE__);}
}

?>