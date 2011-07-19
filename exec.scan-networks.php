<?php
include_once(dirname(__FILE__)."/ressources/class.templates.inc");
include_once(dirname(__FILE__) .'/ressources/class.computers.inc');
include_once(dirname(__FILE__) .'/ressources/class.groups.inc');
include_once(dirname(__FILE__) .'/computer-browse.php');

if(posix_getuid()<>0){
	die("Cannot be used in web server mode\n\n");
}


if(!is_file("/usr/bin/nmap")){
	events('Unable to stat /usr/bin/nmap');
	die();
}
$cmdline=implode(" ",$argv);
if(preg_match("#--verbose#",$cmdline)){$_GET["VERBOSE"]=true;}
if($argv[1]=='--parse'){parseContent($argv[2]);die();}

$net=new networkscanner();
$net->save();

if(!is_file("/etc/artica-postfix/settings/Daemons/NetworkScannerMasks")){
	compevents("Unable to stat /etc/artica-postfix/settings/Daemons/NetworkScannerMasks");
	die();
}

if(!isBuildPid(__FILE__)){compevents("Error execution");die();}

	$disabled_content=@file_get_contents("/etc/artica-postfix/settings/Daemons/NetworkScannerMasksDisabled");
		$exploded=explode("\n",$disabled_content);
		if(is_array($exploded)){
			while (list ($num, $disabled_mask) = each ($exploded) ){
				if(trim($disabled_mask)==null){continue;}
					$notThisMask[trim($disabled_mask)]=true;
				}	
			
		}
		
$datas=file_get_contents("/etc/artica-postfix/settings/Daemons/NetworkScannerMasks");
$tbl=explode("\n",$datas);		


while (list ($num, $maks) = each ($tbl) ){
		if(trim($maks)==null){continue;}
			$arr[trim($maks)]=trim($maks);
		}
		
	if(is_array($arr)){
			$max=count($arr);
			while (list ($num, $net) = each ($arr)){
				if(trim($net)<>null){
					if($notThisMask[$net]){continue;}
					$count=$count+1;
					compevents("Parsing $net");
					$pourc=(round($count/$max)*100)+30;
					if($pourc>100){$pourc=100;}
					WriteProgress($pourc,"Scanning $net");
					launchscanner($net);
				}
			}
		}
		
		WriteProgress('100',"{success}");

		
	
function launchscanner($net){
	$tmp_file="/tmp/".md5(date('Y-m-d h:I:s'.__FILE__));
	$cmd="/usr/bin/nmap -O $net -oN $tmp_file --system-dns -p1";
	compevents("Execute network scanning on $net...");
	compevents("$cmd");
	compevents("Create temporaty file $tmp_file");
	exec($cmd,$arr);
	compevents("Parsing ".basename($tmp_file));
	parseContent($tmp_file);
	}

function parseContent($file){
	if(!is_file($file)){exit;}
	
	$gp=new groups(null);
	$gp->BuildOrdinarySambaGroups();
	
	$datas=file_get_contents($file);
	$tbl=explode("\n",$datas);
			while (list ($num, $line) = each ($tbl) ){
				if(trim($line)==null){continue;}
				$computer_name=null;
				$ip=null;
				$line=trim($line);
				if(preg_match("#^Interesting ports on (.+?)\s+\((.+?)\)#",$line,$re)){
						if(preg_match('#([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)#',$re[2])){
							$ip=$re[2];	
							$computer_name=$re[1];
							compevents("found computer: $computer_name/$ip (line $num)");
							$arr[$computer_name]["IP"]=$ip;
							continue;
						}
				}
					
				if(preg_match("#^Interesting ports on ([0-9\.]+):#",$line,$re)){
						$ip=$re[1];
						$computer_name="$ip";
						compevents("found $computer_name/$ip (line $num)");
						$arr[$computer_name]["IP"]=$ip;
						continue;
					}
					
					
					
				if(preg_match("#^Nmap scan report for\s+(.+?)\s+\((.+?)\)#",$line,$re)){
					$ip=$re[2];	
					$computer_name=$re[1];
					compevents("found computer: $computer_name/$ip (line $num)");
					$arr[$computer_name]["IP"]=$ip;
					continue;						
				}
				
				if(preg_match("#^Nmap scan report for\s+(.+?)$#",trim($line),$re)){
					$ip=$re[1];	
					$computer_name=$ip;
					compevents("found computer: just $ip (line $num)");
					$arr[$computer_name]["IP"]=$ip;
					continue;						
				}				
				
				
				
				
				
				
				if(preg_match("#^MAC Address.+?\((.+?)\)#",$line,$re)){
					if($_GET["VERBOSE"]){echo "$computer_name:: Found type={$re[1]}\n";}
					compevents("$computer_name: found type={$re[1]} (line $num)");
					$arr[$computer_name]["TYPE"]=$re[1];
					continue;
				}
				
				if(preg_match('#^MAC Address:\s+(.+?)\s+#',$line,$re)){
					compevents("$computer_name: found MAC={$re[1]} (line $num)");
					if($_GET["VERBOSE"]){echo "$computer_name:: Found MAC={$re[1]}\n";}
					$arr[$computer_name]["MAC"]=$re[1];
					continue;
				}
				
				if(preg_match('#Running:\s+(.+)#',$line,$re)){
					compevents("$computer_name: found OS={$re[1]} (line $num)");
					if($_GET["VERBOSE"]){echo "$computer_name:: Found OS={$re[1]}\n";}
					$arr[$computer_name]["OS"]=$re[1];
					continue;
				}
				
				
			}
			
		
if(!is_array($arr)){return null;}			
			while (list ($num, $line) = each ($arr) ){
					if(trim($num)==null){continue;}
					compevents("Add entry: ".$num.'$' );
					$cp=new computers($num.'$');
					$cp->ComputerIP=$line["IP"];
					$cp->ComputerOS=$line["OS"];
					$cp->ComputerMacAddress=$line["MAC"];
					if($line["TYPE"]<>null){
						$cp->ComputerMachineType=$line["TYPE"];
					}
					
					if(!$cp->Add()){compevents($cp->ldap_error ." for $num$");}
					
					
			}
			
	@unlink($file);	
}


function WriteProgress($pourc,$text){
	$ini=new Bs_IniHandler();
	$ini->set('NMAP','pourc',$pourc);
	$ini->set('NMAP','text',$text);
	$ini->saveFile('/usr/share/artica-postfix/ressources/logs/nmap.progress.ini');
	@chmod("/usr/share/artica-postfix/ressources/logs/nmap.progress.ini",0755);
}

function compevents($text){
		$pid=getmypid();
		$logFile="/usr/share/artica-postfix/ressources/logs/nmap.log";
		$date=date('d H:i:s');
		$size=@filesize($logFile);
		if($size>1000000){unlink($logFile);}
		$f = @fopen($logFile, 'a');
		@fwrite($f, "$date [$pid]:: $text\n");
		@fclose($f);
		@chmod("/usr/share/artica-postfix/ressources/logs/nmap.log",0755);	
		}

?>