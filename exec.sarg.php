<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");
include_once(dirname(__FILE__)."/ressources/class.user.inc");
include_once(dirname(__FILE__)."/ressources/class.squid.inc");

if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["debug"]=true;$GLOBALS["VERBOSE"]=true;}
if(preg_match("#--reload#",implode(" ",$argv))){$GLOBALS["RELOAD"]=true;$GLOBALS["RESTART"]=true;}

$sock=new sockets();
$EnableSargGenerator=$sock->GET_INFO("EnableSargGenerator");
if($EnableSargGenerator<>1){
	if($GLOBALS["VERBOSE"]){echo "SARG IS DISABLED BY EnableSargGenerator\n";}
	die();
}

if($argv[1]=="--exec"){execute();die();}
if($argv[1]=="--conf"){buildconf();die();}
if($argv[1]=="--passwords"){users_access();die();}


function users_access(){
	$sock=new sockets();
	$ldap=new clladp();
	$array=unserialize(base64_decode($sock->GET_INFO("SargMembers")));
	if(is_array($array)){
	while (list ($uid, $conf) = each ($array) ){
		if($uid==null){continue;}
		$ct=new user($uid);
		if($ct->password==null){continue;}
		echo "Starting......: Sarg, access to $uid\n";
		$f[]="$uid:$ct->password";
	}}
	
	echo "Starting......: Sarg, access to $ldap->ldap_admin\n";
	$f[]="$ldap->ldap_admin:$ldap->ldap_password";
	$f[]="";
	@mkdir("/etc/lighttpd",666,true);
	@file_put_contents("/etc/lighttpd/squid-users.passwd",@implode("\n",$f));
	$unix=new unix();
	$lighttpd_user=$unix->LIGHTTPD_USER();
	@chown("/etc/lighttpd/squid-users.passwd",$lighttpd_user);	
}

function SargDefault($SargConfig){
	if($SargConfig["report_type"]==null){$SargConfig["report_type"]="topusers topsites sites_users users_sites date_time denied auth_failures site_user_time_date downloads";}
	if(!is_numeric($SargConfig["topuser_num"])){$SargConfig["topuser_num"]=0;}
	if(!is_numeric($SargConfig["long_url"])){$SargConfig["long_url"]=0;}
	if(!is_numeric($SargConfig["graphs"])){$SargConfig["graphs"]=1;}
	if(!is_numeric($SargConfig["user_ip"])){$SargConfig["user_ip"]=1;}
	if(!is_numeric($SargConfig["resolve_ip"])){$SargConfig["resolve_ip"]=1;}
	if(!is_numeric($SargConfig["lastlog"])){$SargConfig["lastlog"]=0;}
	
	
	
	if(!is_numeric($SargConfig["topsites_num"])){$SargConfig["topsites_num"]=100;}
	if(!is_numeric($SargConfig["topuser_num"])){$SargConfig["topuser_num"]=0;}
	if($SargConfig["topsites_sort_order"]==null){$SargConfig["topsites_sort_order"]="D";}
	if($SargConfig["index_sort_order"]==null){$SargConfig["index_sort_order"]="D";}
	if($SargConfig["topsites_num"]<2){$SargConfig["topsites_num"]=100;}
	
	
	if($SargConfig["language"]==null){$SargConfig["language"]="English";}
	if($SargConfig["title"]==null){$SargConfig["title"]="Squid User Access Reports";}
	if($SargConfig["date_format"]==null){$SargConfig["date_format"]="e";}
	if($SargConfig["records_without_userid"]==null){$SargConfig["records_without_userid"]="ip";}
	
	if($SargConfig["graphs"]==1){$SargConfig["graphs"]="yes";}else{$SargConfig["graphs"]="no";}
	if($SargConfig["user_ip"]==1){$SargConfig["user_ip"]="yes";}else{$SargConfig["user_ip"]="no";}
	if($SargConfig["resolve_ip"]==1){$SargConfig["resolve_ip"]="yes";}else{$SargConfig["resolve_ip"]="no";}
	if($SargConfig["long_url"]==1){$SargConfig["long_url"]="yes";}else{$SargConfig["long_url"]="no";}
	
	
	
	return $SargConfig;
}


function buildconf(){
	$sock=new sockets();
	$SargConfig=unserialize(base64_decode($sock->GET_INFO("SargConfig")));
	$SargConfig=SargDefault($SargConfig);	
	$conf[]="language {$SargConfig["language"]}";
	$conf[]="graphs {$SargConfig["graphs"]}";
	$conf[]="title \"{$SargConfig["title"]}\"";
	$conf[]="topsites_num {$SargConfig["topsites_num"]}";
	$conf[]="topuser_num {$SargConfig["topuser_num"]}";
	$conf[]="topsites_sort_order CONNECT {$SargConfig["topsites_sort_order"]}";
	$conf[]="index_sort_order {$SargConfig["index_sort_order"]}";
	$conf[]="resolve_ip {$SargConfig["resolve_ip"]}";
	$conf[]="user_ip {$SargConfig["user_ip"]}";
	$conf[]="exclude_hosts /etc/squid3/sarg.hosts";
	$conf[]="date_format {$SargConfig["date_format"]}";
	$conf[]="records_without_userid {$SargConfig["records_without_userid"]}";
	$conf[]="long_url {$SargConfig["long_url"]}";
	$conf[]="lastlog {$SargConfig["lastlog"]}";
	$conf[]="index yes";
	$conf[]="index_tree file";
	$conf[]="overwrite_report yes";
	$conf[]="mail_utility mail";
	$conf[]="temporary_dir /tmp";
	$conf[]="date_time_by bytes";
	$conf[]="show_sarg_info no";
	$conf[]="show_sarg_logo no";
	$conf[]="external_css_file /squid/sarg.css";
	$conf[]="ulimit none";
	$conf[]="squid24 off";
	$conf[]="output_dir /usr/share/artica-postfix/squid";
	$conf[]="logo_image /css/images/logo.gif";
	$conf[]="image_size 160 35";
	$conf[]="access_log /var/log/squid/sarg.log";
	$conf[]="realtime_access_log_lines 5000";
	$conf[]="graph_days_bytes_bar_color orange";
	$conf[]="";	

@file_put_contents("/etc/squid3/sarg.conf",@implode("\n",$conf));
echo "Starting......: Sarg, sarg.conf done\n";


$ips[]="127.0.0.1";
$ips[]="localhost";


@file_put_contents("/etc/squid3/sarg.hosts",@implode("\n",$ips));
if($GLOBALS["VERBOSE"]){"/etc/squid3/sarg.hosts done\n";}
echo "Starting......: Sarg, sarg.hosts done\n";

$i[]="html{background-color:#005447;}";
$i[]="table{background-color:white}";
$i[]=".body {font-family:Verdana,Tahoma,Arial;color:#000000;background-color:#005447;}";
$i[]=".info {font-family:Verdana,Tahoma,Arial;font-size:9px;}";
$i[]=".info a:link,a:visited {font-family:Verdana,Tahoma,Arial;color:#0000ff;font-size:9px;text-decoration:none;}";
$i[]=".title {font-family:Verdana,Tahoma,Arial;font-size:11px;color:#7D7171;background-color:#ffffff;}";
$i[]=".title2 {font-family:Verdana,Tahoma,Arial;font-size:11px;color:darkblue;background-color:#ffffff;text-align:left;}";
$i[]=".title3 {font-family:Verdana,Tahoma,Arial;font-size:11px;color:darkblue;background-color:#ffffff;text-align:right;}";
$i[]=".header {font-family:Verdana,Tahoma,Arial;font-size:9px;color:darkblue;background-color:#dddddd;text-align:left;border-right:1px solid #666666;border-bottom:1px solid #666666;}";
$i[]=".header2 {font-family:Verdana,Tahoma,Arial;font-size:9px;color:darkblue;background-color:#dddddd;text-align:right;border-right:1px solid #666666;border-bottom:1px solid #666666;}";
$i[]=".header3 {font-family:Verdana,Tahoma,Arial;font-size:9px;color:darkblue;background-color:#dddddd;text-align:center;border-right:1px solid #666666;border-bottom:1px solid #666666;}";
$i[]=".text {font-family:Verdana,Tahoma,Arial;color:#000000;font-size:9px;}";
//$i[]=".data {font-family:Verdana,Tahoma,Arial;color:#000000;font-size:9px;background-color:lavender;text-align:right;border-right:1px solid #6a5acd;border-bottom:1px solid #6a5acd;}";
//$i[]=".data a:link,a:visited {font-family:Verdana,Tahoma,Arial;color:#0000ff;font-size:9px;background-color:lavender;text-align:right;text-decoration:none;}";
//$i[]=".data2 {font-family:Verdana,Tahoma,Arial;color:#000000;font-size:9px;background-color:lavender;border-right:1px solid #6a5acd;border-bottom:1px solid #6a5acd;}";
//$i[]=".data3 {font-family:Verdana,Tahoma,Arial;color:#000000;font-size:9px,text-align:center;background-color:lavender;border-right:1px solid #6a5acd;border-bottom:1px solid #6a5acd;}";
//$i[]=".data3 a:link,a:visited {font-family:Verdana,Tahoma,Arial;color:#0000ff;font-size:9px;text-align:center;background-color:lavender;text-decoration:none;}";
$i[]=".text {font-family:Verdana,Tahoma,Arial;color:#000000;font-size:9px;text-align:right;}";
$i[]="table thead th ,.header_l { text-align: left; font-weight: normal; font-size: 16px; }";
$i[]="table thead th ,.header_l { height: 23px; border-bottom: 1px #b1b1b1 solid; padding: 0px 5px 0px 10px; }";
$i[]="table thead tr ,.header_l{ background: transparent url( /ressources/templates/default/img/thead_bg.jpg ) repeat-x left bottom;}";
$i[]="table tbody tr thead,.header_l{ background-color: #ffffff; }";
$i[]="table{ width: 100%; font-family:Arial;}";
$i[]="th,.header_l {
-moz-background-clip:border;
-moz-background-inline-policy:continuous;
-moz-background-origin:padding;
background:#005447 url(/ressources/templates/default/images/ui-bg_highlight.png) repeat-x scroll 50% 50%;
border:1px solid #000000;
color:#FFFFFF;
font-weight:bold;
text-align:left;
text-transform:uppercase;
padding:3px;
}";
$i[]="td.data2 { border-bottom: 1px #d5d5d5 solid; ;padding: 0px 5px 0px 10px;background-color: #fafafa;font-size:14px }";
$i[]="td.data2 a:link,a:visited {font-size:14px;text-decoration:underline;color:black}";
$i[]="td.data a:link,a:visited {font-size:14px;text-decoration:underline;color:black}";
$i[]="td.data{ border-bottom: 1px #d5d5d5 solid; ;padding: 0px 5px 0px 10px;height: 23px; border-bottom: 1px #b1b1b1 solid; padding: 0px 5px 0px 10px; }";
$i[]="td.data3{ border-bottom: 1px #d5d5d5 solid; ;padding: 0px 5px 0px 10px;height: 23px; border-bottom: 1px #b1b1b1 solid; padding: 0px 5px 0px 10px;text-align:center }";
$i[]="td.header_c{font-size:13px;height:auto}";
$i[]="img {border-style: none}";
$i[]="td.data2 a:hover{font-size:14px;text-decoration:underline;font-weight:bold;color:#B10000}";
$i[]="td.data a:hover{font-size:14px;text-decoration:underline;font-weight:bold;color:#B10000}";
$i[]=".logo{background-color:#015548;height:50px;padding:5px}";
$i[]="th.title_c{
	background-color:#8F8785;
	font-size:16px;letter-spacing:1px;font-weight:bold;color:#FFFFFF;background-image:none;border:0px;text-align:left;text-transform:capitalize;}";
$i[]="td.header_c,th.header_c{font-size:14px;padding:4px;
	text-transform:capitalize;font-style:italic;color:#7D7171;font-weight:bolder;
	background-image:none;border:0px;text-align:right;background-color:#FFFFFF}";
$i[]="td.link a:link,a:visited{height:auto;color:#B10000}";
$i[]="td.link{height:auto;color:#B10000}";
$i[]="td.link a:hover{height:auto;color:#B10000;font-weight:bolder}";

if($GLOBALS["VERBOSE"]){"/usr/share/artica-postfix/squid/sarg.css done\n";}
@file_put_contents("/usr/share/artica-postfix/squid/sarg.css",@implode("\n",$i));

$unix=new unix();
$lighttpd_user=$unix->LIGHTTPD_USER();


echo "Starting......: lighttpd user: $lighttpd_user\n";
@chown("/usr/share/artica-postfix/squid/sarg.css",$lighttpd_user);
echo "Starting......: Sarg, css done\n";
	$nice=EXEC_NICE();
	$unix=new unix();
	$sarg_bin=$unix->find_program("sarg");

unset($f);
$f[]="#!/bin/bash";
$f[]="#Get current date";
$f[]="TODAY=\$(date +%d/%m/%Y)"; 
$f[]="YESTERDAY=\$(date --date \"1 day ago\" +%d/%m/%Y)"; 
$f[]="mkdir -p /usr/share/artica-postfix/squid/daily";
$f[]="chown -R  $lighttpd_user.$lighttpd_user /usr/share/artica-postfix/squid/daily";
$f[]="NAAT=\"/var/www-naat/html/genfiles/modules/squid-reports/daily\"";
$f[]="if [ -d \${NAAT} ]; then";
$f[]="    chown -R apache \${NAAT}";
$f[]="fi";
$f[]="export LC_ALL=C";
$f[]="$nice$sarg_bin -f /etc/squid3/sarg.conf -l /var/log/squid/sarg.log -o /usr/share/artica-postfix/squid/daily -z -d \$YESTERDAY-\$TODAY > /dev/null 2>&1";
$f[]="";
@file_put_contents("/etc/cron.daily/0sarg",@implode("\n",$f));
echo "Starting......: Sarg, cron cron.daily done\n";
unset($f);
$f[]="#!/bin/bash";
$f[]="if [ \$cnt -eq 4 ]; then";
$f[]="#Get yesterday date";
$f[]="YESTERDAY=\$(date --date \"1 day ago\" +%d/%m/%Y)";
$f[]="";
$f[]="#Get 4 weeks ago date";
$f[]="WEEKSAGO=\$(date --date \"4 weeks ago\" +%d/%m/%Y)";
$f[]="";
$f[]="mkdir -p  /usr/share/artica-postfix/squid/monthly";
$f[]="#chown -R apache /usr/share/artica-postfix/squid/monthly";
$f[]="";
$f[]="#NAAT=\"/var/www-naat/html/genfiles/modules/squid-reports/monthly \"";
$f[]="#if [ -d \${NAAT} ]; then";
$f[]="#    chown -R apache \${NAAT}";
$f[]="#fi";
$f[]="";
$f[]="export LC_ALL=C";
$f[]="$nice$sarg_bin -f /etc/squid3/sarg.conf -l /var/log/squid/sarg.log -o /usr/share/artica-postfix/squid/monthly -d \$WEEKSAGO-\$YESTERDAY > /dev/null 2>&1";
$f[]="";
$f[]="/usr/sbin/squid -k rotate";
$f[]="";
$f[]="#don't move next line to upper, reason is that sed change the cnt assignment of the first 7 lines";
$f[]="cnt=1";
$f[]="else";
$f[]="let cnt++";
$f[]="fi";
$f[]="#echo Will rename itself \(\$0\) with cnt \(\$cnt\) increased. 1>&2";
$f[]="sargtmp=/var/tmp/`basename \$0`";
$f[]="sed \"1,7s/^cnt=.*/cnt=\$cnt/";
$f[]="\" \$0 >|\$sargtmp";
$f[]="chmod -f 775 \$sargtmp";
$f[]="mv -f \$sargtmp \$0";
@file_put_contents("/etc/cron.monthly/0sarg",@implode("\n",$f));
shell_exec("/bin/chmod 755 /etc/cron.monthly/0sarg");


echo "Starting......: Sarg, cron cron.monthly done\n";
unset($f);
$f[]="#!/bin/bash";
$f[]="";
$f[]="#Get current date";
$f[]="TODAY=\$(date +%d/%m/%Y) ";
$f[]="";
$f[]="#Get one week ago today";
$f[]="LASTWEEK=\$(date --date \"1 week ago\" +%d/%m/%Y)";
$f[]="";
$f[]="mkdir -p /usr/share/artica-postfix/squid/weekly ";
$f[]="chown -R apache.apache /usr/share/artica-postfix/squid/weekly ";
$f[]="";
$f[]="NAAT=\"/var/www-naat/html/genfiles/modules/squid-reports/weekly\"";
$f[]="if [ -d \${NAAT} ]; then";
$f[]="    chown -R apache \${NAAT}";
$f[]="fi";
$f[]="";
$f[]="export LC_ALL=C";
$f[]="$nice$sarg_bin -f /etc/squid3/sarg.conf -l /var/log/squid/sarg.log -o /usr/share/artica-postfix/squid/weekly -z -d \$LASTWEEK-\$TODAY >/dev/null 2>";
$f[]="";
@file_put_contents("/etc/cron.weekly/0sarg",@implode("\n",$f));
shell_exec("/bin/chmod 755 /etc/cron.weekly/0sarg");
echo "Starting......: Sarg, cron cron.weekly done\n";
users_access();
}


function execute(){
	$nice=EXEC_NICE();
	if(is_file(dirname(__FILE__)."/exec.sarg.gilou.php")){
		shell_exec($nice.LOCATE_PHP5_BIN2()." ".dirname(__FILE__)."/exec.sarg.gilou.php --exec");
		return;
	}
	
	$nice=EXEC_NICE();
	$unix=new unix();
	$today=date("d/m/Y");
	$sarg_bin=$unix->find_program("sarg");
	if(!is_file($sarg_bin)){echo "Sarg not installed";return;}
	buildconf();
	
	$usersauth=false;
	
	$squid=new squidbee();
	if($squid->LDAP_AUTH==1){$usersauth=true;}
	if($squid->LDAP_EXTERNAL_AUTH==1){$usersauth=true;}
	
	if(!is_file("/etc/squid/exclude_codes")){@file_put_contents("/etc/squid/exclude_codes","\nNONE/400\n");}
	@mkdir("/usr/share/artica-postfix/squid",755,true);
	
	if($usersauth){
		echo "Starting......: Sarg, user authentification enabled\n";
		$u=" -i ";
	}else{
		echo "Starting......: Sarg, user authentification disabled\n";
	}
	$cmd="$nice$sarg_bin -d {$today}-{$today} $u-f /etc/squid3/sarg.conf -l /var/log/squid/sarg.log -o /usr/share/artica-postfix/squid -x -z 2>&1";
	$t1=time();
	echo "Starting......: Sarg, $cmd\n";
	exec($cmd,$results);
	
	while (list ($index, $line) = each ($results) ){
		if(preg_match("#SARG: No records found#",$line)){$subject_add="(No records found)";}
		
		if(preg_match("#SARG:\s+.+?mixed records format#",$line)){
			send_email_events("SARG: Error, squid was reloaded",
			"It seems that there is a mixed log file format detected in squid
			This reason is Artica change squid log format from orginial to http access mode.
			In this case, the log will be moved and squid will be reloaded 
			in order to build a full log file with only one log format.
			\n".@implode("\n",$results),"proxy");
			shell_exec(LOCATE_PHP5_BIN2()." ". dirname(__FILE__)."/exec.squid.php --reconfigure");
			shell_exec($unix->LOCATE_SQUID_BIN() ." -k rotate");
			shell_exec("/etc/init.d/artica-postfix restart squid-tail");
			return;
			}
		
		if(preg_match("#SARG:\s+.+?enregistrements de plusieurs formats#",$line)){
			send_email_events("SARG: Error, squid was reloaded",
			"It seems that there is a mixed log file format detected in squid
			This reason is Artica change squid log format from orginial to http access mode.
			In this case, the log will be moved and squid will be reloaded 
			in order to build a full log file with only one log format.
			\n".@implode("\n",$results),"proxy");
			shell_exec(LOCATE_PHP5_BIN2()." ". dirname(__FILE__)."/exec.squid.php --reconfigure");
			shell_exec($unix->LOCATE_SQUID_BIN() ." -k rotate");
			shell_exec("/etc/init.d/artica-postfix restart squid-tail");
			return;
			}
			
		if(preg_match("#SARG.+?Unknown input log file format#",$line)){
			send_email_events("SARG: \"Unknown input log file format\", squid was reloaded",
			"It seems that there is a input log file format log file format detected in squid
			This reason is Artica change squid log format from orginial to log_fqn on, this will be disabled
			In this case, the log will be moved and squid will be reloaded 
			in order to build a full log file with only one log format.
			\n".@implode("\n",$results),"proxy");
			shell_exec(LOCATE_PHP5_BIN2()." ". dirname(__FILE__)."/exec.squid.php --reconfigure");
			shell_exec($unix->LOCATE_SQUID_BIN() ." -k rotate");
			shell_exec("/etc/init.d/artica-postfix restart squid-tail");
			return;
			}
	}
	
	$unix=new unix();
	$lighttpd_user=$unix->LIGHTTPD_USER();
	echo "Starting......: Sarg, lighttpd user: $lighttpd_user\n";
	$chown=$unix->find_program("chown");
	echo "Starting......: Sarg,$chown -R $lighttpd_user:$lighttpd_user /usr/share/artica-postfix/squid/*\n";
	exec("$chown -R $lighttpd_user:$lighttpd_user /usr/share/artica-postfix/squid/* >/dev/null 2>&1",$results2);	
	echo "Starting......: Sarg,\n". @implode("\n".$results2)."\n";
	
	$t2=time();
	$distanceOfTimeInWords=distanceOfTimeInWords($t1,$t2);
	echo "Starting......: Sarg, $distanceOfTimeInWords\n";
	if($GLOBALS["VERBOSE"]){
		echo "SARG: Statistics generated ($distanceOfTimeInWords)\n\n";
		echo @implode("\n",$results)."\n";
		
	}
	send_email_events("SARG: Statistics generated ($distanceOfTimeInWords) $subject_add","Command line:\n-----------\n$cmd\n".@implode("\n",$results),"proxy");
	}


