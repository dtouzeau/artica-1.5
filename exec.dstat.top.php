<?php
if(!is_file('/usr/bin/gnuplot')){die();}
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.user.inc');
include_once(dirname(__FILE__)."/framework/class.unix.inc");
include_once(dirname(__FILE__)."/framework/frame.class.inc");
	cpulimit();

if($argv[1]='--verbose'){$_GET["debug"]=true;}

if(!Build_pid_func(__FILE__,"MAIN")){
	writelogs(basename(__FILE__).":Already executed.. aborting the process",basename(__FILE__),__FILE__,__LINE__);
	die();
}


$pid=getmypid();
$users=new usersMenus();
if(!$users->GNUPLOT_PNG){write_syslog("gnuplot is not compiled with png support... aborting",__FILE__);die();}
topmem();
topcpu();

function topcpu(){
	if(!is_file("/var/log/artica-postfix/dstat_topcpu.csv")){
		events("Processing dstat unable to stat/var/log/artica-postfix/dstat_topcpu.csv");
		return null;
	}
	
events( "Processing dstat content file /var/log/artica-postfix/dstat_topcpu.csv");
$content=file_get_contents("/var/log/artica-postfix/dstat_topcpu.csv");

$content=explode("\n",$content);
$maxline=count($content);
events("Processing $maxline rows");

while (list ($num, $line) = each ($content)){
	$line=trim($line);
	if($line==null){continue;}
	if(preg_match("#(.+?)\|(.+?)([0-9]+)#",$line,$re)){
		$date=$re[1];
		$process=trim($re[2]);
		$cpu=$re[3];
		usleep(1000);
		$array[$process][]=array($date,$cpu);
		}
	}
	
		if($maxline>5000){
			events("remove stat file and restart dstat");
			@unlink("/var/log/artica-postfix/dstat_topcpu.csv");
			exec("/etc/init.d/artica-postfix restart dstat &");
		}	
	
	
	if(is_array($array)){
		system("/bin/rm /usr/share/artica-postfix/ressources/logs/dstat.topcpu.*.png");	
		while (list ($process, $array_datas) = each ($array)){WriteGnuPlotCPU($process,$array_datas);}
	}
	
	
	
	
	
}

function topmem(){
	if(!is_file("/var/log/artica-postfix/dstat_topmem.csv")){
		return null;
	}
	
echo "Processing dstat content file /var/log/artica-postfix/dstat_topmem.csv\n";
$content=file_get_contents("/var/log/artica-postfix/dstat_topmem.csv");

$content=explode("\n",$content);
$maxline=count($content);
while (list ($num, $line) = each ($content)){
	$line=trim($line);
	if($line==null){continue;}
	if(preg_match("#(.+?)\|(.+?)([0-9]+)M#",$line,$re)){
		$date=$re[1];
		$process=trim($re[2]);
		$mem=$re[3];
		usleep(1000);
		$array[$process][]=array($date,$mem);
		}
	}
	
	if($maxline>5000){
			events("remove /var/log/artica-postfix/dstat_topmem.csv file and restart dstat");
			@unlink("/var/log/artica-postfix/dstat_topmem.csv");
			exec("/etc/init.d/artica-postfix restart dstat &");
		}		
	
	if(is_array($array)){
		system("/bin/rm /usr/share/artica-postfix/ressources/logs/dstat.topmem.*.png");	
		while (list ($process, $array_datas) = each ($array)){WriteGnuPlot($process,$array_datas);}	
	}
	
}

function WriteGnuPlot($processname,$array){
	
	

   $processname_path=str_replace(".","-",$processname);
   $processname_path=str_replace(" ","-",$processname_path);
   $processname_path=strtolower($processname_path);
   $countlines=count($array);	
   $shellpath="/tmp/gnuplot.".md5($processname).".plot";
   $imagepath="/usr/share/artica-postfix/ressources/logs/dstat.topmem.$countlines.$processname_path.png";
   $datafile="/tmp/gnuplot.".md5($processname).".datas";
   
 
   
while (list ($num, $datas) = each ($array)){
	usleep(1000);	
	$line=$line."{$datas[0]} {$datas[1]}\n";
	}
file_put_contents($datafile,$line);	

   $conf=$conf.'#!/usr/bin/gnuplot -persist'."\n";
   $conf=$conf.'reset'."\n";
   $conf=$conf."set xlabel \"time\" #font \"Helvetica,12\"\n";
   $conf=$conf."set ylabel \"Mo\" #font \"Helvetica,12\"\n";
   $conf=$conf.'set autoscale'."\n";
   $conf=$conf.'set grid'."\n";
   $conf=$conf.'set xdata time'."\n";
   $conf=$conf.'set format x "%H:%M"'."\n";
   $conf=$conf.'set timefmt "%d-%m %H:%M:%S"'."\n";
   $conf=$conf.'set term png transparent size 500,250'."\n";
   $conf=$conf.'set datafile commentschars "-"'."\n";
   $conf=$conf."set title \"$processname\"\n";
   $conf=$conf."set output \"$imagepath\"\n";
   $conf=$conf."plot \"$datafile\" using 1:3 title \"$processname Memory\" with lines\n";
   
   file_put_contents($shellpath,$conf);
   system("/bin/chmod 777 $shellpath");
echo "Processing \"$imagepath\"\n"; 
echo "Processing $datafile\n";     
echo "Processing $shellpath\n";        
   
   sleep(1);
   system("$shellpath");
   if(!is_file($imagepath)){echo "FATAL ERROR on $imagepath\n";}
   if(is_file($imagepath)){system("/bin/chmod 755 $imagepath");}

	
}

function WriteGnuPlotCPU($processname,$array){
   $processname_path=str_replace(".","-",$processname);
   $processname_path=str_replace(" ","-",$processname_path);
   $processname_path=strtolower($processname_path);
   $countlines=count($array);	
   $shellpath="/tmp/gnuplot.cpu.".md5($processname).".plot";
   $imagepath="/usr/share/artica-postfix/ressources/logs/dstat.topcpu.$countlines.$processname_path.png";
   $datafile="/tmp/gnuplot.cpu.".md5($processname).".datas";
   
 
   
while (list ($num, $datas) = each ($array)){	
	$line=$line."{$datas[0]} {$datas[1]}\n";
	}
file_put_contents($datafile,$line);	

   $conf=$conf.'#!/usr/bin/gnuplot -persist'."\n";
   $conf=$conf.'reset'."\n";
   $conf=$conf."set xlabel \"time\" #font \"Helvetica,12\"\n";
   $conf=$conf."set ylabel \"% CPU\" #font \"Helvetica,12\"\n";
   $conf=$conf.'set autoscale'."\n";
   $conf=$conf.'set grid'."\n";
   $conf=$conf.'set xdata time'."\n";
   $conf=$conf.'set format x "%H:%M"'."\n";
   $conf=$conf.'set timefmt "%d-%m %H:%M:%S"'."\n";
   $conf=$conf.'set term png transparent size 500,250'."\n";
   $conf=$conf.'set datafile commentschars "-"'."\n";
   $conf=$conf."set title \"$processname\"\n";
   $conf=$conf."set output \"$imagepath\"\n";
   $conf=$conf."plot \"$datafile\" using 1:3 title \"$processname CPU\" with lines\n";
   
   file_put_contents($shellpath,$conf);
   system("/bin/chmod 777 $shellpath");
echo "Processing \"$imagepath\"\n"; 
echo "Processing $datafile\n";     
echo "Processing $shellpath\n";        
   
   sleep(1);
   system("$shellpath");
   if(!is_file($imagepath)){echo "FATAL ERROR on $imagepath\n";}
   if(is_file($imagepath)){system("/bin/chmod 755 $imagepath");}

	
}

function events($text){
		$filename=basename(__FILE__);
		$pid=getmypid();
		$date=date("H:i:s");
		$logFile="/var/log/artica-postfix/artica-statistics.log";
		$size=filesize($logFile);
		if($size>1000000){unlink($logFile);}
		
		$f = @fopen($logFile, 'a');
		@fwrite($f, "$filename[$pid] $date $text\n");
		if($_GET["debug"]){echo "$filename[$pid] $date $text\n";}
		@fclose($f);	
		}


?>