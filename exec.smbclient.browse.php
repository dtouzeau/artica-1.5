<?php
$_GET["BIN"]="/usr/bin/smbclient";
if(!is_file($_GET["BIN"])){die("Unable to stat {$_GET["BIN"]}");}
if(!is_array($argv)){die("No parameters");}

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.user.inc');
include_once(dirname(__FILE__).'/ressources/class.computers.inc');

$args=implode(" ",$argv);
$opts=$opts." -O \"IPTOS_LOWDELAY TCP_NODELAY SO_RCVBUF=262144 SO_SNDBUF=262144 SO_KEEPALIVE\" ";


$_GET["TMP_FILE"]="/tmp/artica-browse-".md5($args);
$_GET["SOCKS_OPTIONS"]=$opts;



if(preg_match("#--browse=(.+?)\s+#",$args,$re)){
	
	$computer_id=$re[1];
	if(substr($computer_id,strlen($computer_id)-1,1)<>'$'){$computer_id=$computer_id."$";}
	
	$computer=new computers($computer_id);
	$ini=new Bs_IniHandler();
	$ini->loadString($computer->ComputerCryptedInfos);
	
	
	if($computer->ComputerIP<>null){$computer_addr=$computer->ComputerIP;}else{$computer_addr=$computer->uid;}
	
	$username=$ini->_params["ACCOUNT"]["USERNAME"];
	$password=$ini->_params["ACCOUNT"]["PASSWORD"];	
	
	if($username<>null){
		$cmd_auth=" -U{$username}%$password ";
	}
	

	if(substr($computer_addr,strlen($computer_addr)-1,1)=='$'){$computer_addr=substr($computer_addr,0,strlen($computer_addr)-1);}
	
	if(preg_match('#--filew=(.+?)\s+-#',$args,$re)){
		$_GET["WRITE"]=$re[1];
	}
	
	$cmd="{$_GET["BIN"]} {$_GET["SOCKS_OPTIONS"]} -N $cmd_auth--list=$computer_addr";

	//echo $args."\n";
	if(preg_match('#--listf=(.+?)\s+-#',$args,$re)){
		
		$folders=$re[1];
		$folders=stripslashes($folders);
		if(strpos($folders,'/')>0){
			$listfolders=explode("/",$folders);
			$first=$listfolders[0];
			unset($listfolders[0]);
			if(is_array($listfolders)){
				$command="cd \\\"". implode("/",$listfolders)."\\".'"';
			}

		}else{
			$first=$folders;	
		}
	
		$cmd="{$_GET["BIN"]} {$_GET["SOCKS_OPTIONS"]} -N $cmd_auth //$computer_addr/$first -c \"$command;ls\"";
		echo "$cmd\n";
		system($cmd." >{$_GET["TMP_FILE"]} 2>&1");	
		ParseResultsLIST();
		@unlink($_GET["TMP_FILE"]);
		die();
		
	}
	
	if(!is_file($_GET["TMP_FILE"])){
		system($cmd." >{$_GET["TMP_FILE"]} 2>&1");	
	}
	
	ParseResultsLIST();
	@unlink($_GET["TMP_FILE"]);
	die();
	
}


die("Unable to understand $args\n");



function ParseResultsLIST(){
	
	$f=@file_get_contents($_GET["TMP_FILE"]);
	echo $f;
	$tbl=explode("\n",$f);
	
	while (list ($num, $line) = each ($tbl)){
		if(preg_match("#\s+(.+?)Disk#",$line,$re)){
		
			if((trim($re[1])=='.') OR (trim($re[1])=='..') OR (trim($re[1])==null)){continue;}
			$a[]="<folder>".trim($re[1])."</folder>\n";
		}
		
		if(preg_match("#\(Error\s+(.+?)\)#",$line,$re)){
			
			$a[]= "<folder>{".trim($re[1])."}</folder>\n";
		}
		
		
		if(preg_match("#session setup failed:(.+)#",$line,$re)){
			$a[]= "<folder>{".trim($re[1])."}</folder>\n";
			
		}
		
		if(preg_match("#tree connect failed:(.+)#",$line,$re)){
				$a[]= "<folder>{".trim($re[1])."}</folder>\n";
		}
		
		if(preg_match("#(.+?)\s+\s+D.+?\s+[0-9]+#",$line,$re)){
		
		if((trim($re[1])=='.') OR (trim($re[1])=='..') OR (trim($re[1])==null)){continue;}
			if(trim(strtoupper($re[1]))=="ADMIN$"){$re[1]="c$";}
			$a[]= "<folder>".trim($re[1])."</folder>\n";
		}
				
		
	}
	
	if(is_array($a)){
		file_put_contents($_GET["WRITE"],implode("\n",$a));
		if(is_file("/usr/bin/iconv")){
	 	system("/usr/bin/iconv {$_GET["WRITE"]} -t ISO-8859-1 --output={$_GET["WRITE"]}");
	 	file_put_contents($_GET["WRITE"],implode("\n",$a));
		}
	}
	
	
}






?>