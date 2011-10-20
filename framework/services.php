<?php

include_once(dirname(__FILE__)."/frame.class.inc");
include_once(dirname(__FILE__)."/class.unix.inc");


if(isset($_GET["syslogger"])){syslogger();exit;}
if(isset($_GET["openvpn"])){openvpn();exit;}
if(isset($_GET["postfix-single"])){postfix_single();exit;}
if(isset($_GET["nsswitch"])){nsswitch();exit;}
if(isset($_GET["changeRootPasswd"])){changeRootPasswd();exit;}
if(isset($_GET["process1"])){process1();exit;}
if(isset($_GET["mysql-status"])){mysql_status();exit;}
if(isset($_GET["greensql-status"])){greensql_status();exit;}
if(isset($_GET["greensql-reload"])){greensql_reload();exit;}
if(isset($_GET["greensql-logs"])){greensql_logs();exit;}
if(isset($_GET["restart-postfix-all"])){restart_postfix_all();exit;}
if(isset($_GET["restart-apache-groupware"])){restart_apache_groupware();exit;}
if(isset($_GET["restart-artica-status"])){restart_artica_status();exit;}
if(isset($_GET["stop-nscd"])){stop_nscd();exit;}
if(isset($_GET["restart-lighttpd"])){restart_lighttpd();exit;}
if(isset($_GET["restart-ldap"])){restart_ldap();exit;}
if(isset($_GET["restart-mysql"])){restart_mysql();exit;}
if(isset($_GET["restart-cron"])){restart_cron();exit;}
if(isset($_GET["restart-dhcpd"])){restart_dhcpd();exit;}
if(isset($_GET["total-memory"])){total_memory();exit;}
if(isset($_GET["mysql-ssl-keys"])){mysql_ssl_key();exit;}
if(isset($_GET["restart-tomcat"])){retart_tomcat();exit;}
if(isset($_GET["mysqld-perso"])){mysqld_perso();exit;}
if(isset($_GET["mysqld-perso-save"])){mysqld_perso_save();exit;}
if(isset($_GET["openemm-status"])){openemm_status();exit;}
if(isset($_GET["restart-openemm"])){openemm_restart();exit;}
if(isset($_GET["kerbauth"])){kerbauth();exit;}
if(isset($_GET["reload-pure-ftpd"])){pureftpd_reload();exit;}
if(isset($_GET["restart-ftp"])){pureftpd_restart();exit;}
if(isset($_GET["dmicode"])){dmicode();exit;}
if(isset($_GET["php-ini-set"])){PHP_INI_SET();exit;}
if(isset($_GET["mysql-events"])){mysql_events();exit;}
if(isset($_GET["AdCacheMysql"])){AdCacheMysql();exit;}
if(isset($_GET["kav4Proxy-reload"])){kav4proxy_reload();exit;}
if(isset($_GET["clock"])){GETclock();exit;}
if(isset($_GET["phpldapadmin"])){phpldapadmin();exit;}
if(isset($_GET["ntpd-status"])){ntpd_status();exit;}
if(isset($_GET["artica-update-cron"])){artica_schedule_cron();exit;}
if(isset($_GET["AutoRebootSchedule"])){artica_schedule_reboot();exit;}
if(isset($_GET["artica-patchs"])){artica_patchs();exit;}
if(isset($_GET["patchs-force"])){artica_patchs_force();exit;}
if(isset($_GET["mysql-ocs"])){mysql_ocs();exit;}
if(isset($_GET["optimize-mysql-db"])){mysql_optimize_db();exit;}
if(isset($_GET["optimize-mysql-cron"])){mysql_optimize_cron();exit;}





while (list ($num, $line) = each ($_GET)){$f[]="$num=$line";}

writelogs_framework("unable to understand query !!!!!!!!!!!..." .@implode(",",$f),"main()",__FILE__,__LINE__);
die();
function mysql_status(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --mysql --nowachdog",$results);
	echo "<articadatascgi>". base64_encode(@implode("\n",$results))."</articadatascgi>";		
}
function greensql_status(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --greensql --nowachdog",$results);
	echo "<articadatascgi>". base64_encode(@implode("\n",$results))."</articadatascgi>";		
}
function syslogger(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup /etc/init.d/artica-postfix restart sysloger >/dev/null 2>&1 &");
	shell_exec($cmd);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	
}

function dmicode(){
	if(is_file("/etc/artica-postfix/dmidecode.cache")){
		echo "<articadatascgi>". @file_get_contents("/etc/artica-postfix/dmidecode.cache")."</articadatascgi>";
		return;
	}
	$unix=new unix();
	
	$php5=$unix->LOCATE_PHP5_BIN();
	$cmd=trim("/usr/share/artica-postfix/exec.dmidecode.php >/dev/null 2>&1");
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);	
	shell_exec($cmd);
	echo "<articadatascgi>". @file_get_contents("/etc/artica-postfix/dmidecode.cache")."</articadatascgi>";
	
}

function total_memory(){
	$unix=new unix();
	echo "<articadatascgi>". $unix->TOTAL_MEMORY_MB()."</articadatascgi>";
}

function restart_ldap(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup /etc/init.d/artica-postfix restart ldap >/dev/null 2>&1 &");
	shell_exec($cmd);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
}
function restart_cron(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup /etc/init.d/artica-postfix restart fcron >/dev/null 2>&1 &");
	shell_exec($cmd);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
}

function restart_dhcpd(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup /etc/init.d/artica-postfix restart dhcp >/dev/null 2>&1 &");
	shell_exec($cmd);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	
}

function restart_tomcat(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$php5=$unix->LOCATE_PHP5_BIN();
	$cmd=trim("$nohup /usr/share/artica-postfix/exec.freeweb.php --httpd >/dev/null 2>&1 &");
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);
	$cmd=trim("$nohup /etc/init.d/artica-postfix restart tomcat >/dev/null 2>&1 &");
	shell_exec($cmd);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
}
function restart_mysql(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup ".$unix->LOCATE_PHP5_BIN(). " /usr/share/artica-postfix/exec.mysql.build.php --build >/dev/null 2>&1");
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);	
	shell_exec($cmd);
	$cmd=trim("$nohup /etc/init.d/artica-postfix restart mysql >/dev/null 2>&1 &");
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);	
	shell_exec($cmd);
	
}
function mysql_optimize_db(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup ".$unix->LOCATE_PHP5_BIN(). " /usr/share/artica-postfix/exec.mysql.optimize.php --optimize >/dev/null 2>&1");
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);	
	shell_exec($cmd);
	
}
function mysql_optimize_cron(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup ".$unix->LOCATE_PHP5_BIN(). " /usr/share/artica-postfix/exec.mysql.optimize.php --cron >/dev/null 2>&1");
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);	
	shell_exec($cmd);
	
}
function restart_postfix_all(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup /etc/init.d/artica-postfix restart postfix-heavy >/dev/null 2>&1 &");
	shell_exec($cmd);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);	
}

function restart_apache_groupware(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup /etc/init.d/artica-postfix restart apache-groupware >/dev/null 2>&1 &");
	shell_exec($cmd);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);		
}
function restart_artica_status(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup /etc/init.d/artica-postfix restart artica-status >/dev/null 2>&1 &");
	shell_exec($cmd);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);		
}
function stop_nscd(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup /etc/init.d/nscd stop >/dev/null 2>&1 &");
	shell_exec($cmd);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);		
}

function kerbauth(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup ".$unix->LOCATE_PHP5_BIN(). " /usr/share/artica-postfix/exec.kerbauth.php --build");
	shell_exec($cmd);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	}

function artica_patchs(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup ".$unix->LOCATE_PHP5_BIN(). " /usr/share/artica-postfix/exec.patchs.php");
	shell_exec($cmd);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	}
function artica_patchs_force(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup ".$unix->LOCATE_PHP5_BIN(). " /usr/share/artica-postfix/exec.patchs.php --force");
	shell_exec($cmd);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);	
}
	

function openvpn(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup /etc/init.d/artica-postfix restart openvpn >/dev/null 2>&1 &");
	shell_exec($cmd);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	
}
function postfix_single(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup /etc/init.d/artica-postfix restart postfix-single >/dev/null 2>&1 &");
	shell_exec($cmd);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	
}

function nsswitch(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup /usr/share/artica-postfix/bin/artica-install --nsswitch >/dev/null 2>&1 &");
	shell_exec($cmd);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);	
}

function process1(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup /usr/share/artica-postfix/bin/process1 --force --verbose ". time()." >/dev/null 2>&1 &");
	shell_exec($cmd);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);		
}

function greensql_reload(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup /usr/share/artica-postfix/bin/artica-install --greensql-reload ". time()." >/dev/null 2>&1 &");
	shell_exec($cmd);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);		
}

function mysql_ssl_key(){
	$cmd=trim("/usr/share/artica-postfix/bin/artica-install --mysql-certificate 2>&1");
	exec($cmd,$results);
	writelogs_framework("$cmd " .count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	while (list ($num, $line) = each ($results)){writelogs_framework("$line",__FUNCTION__,__FILE__,__LINE__);}

}

function restart_lighttpd(){
	$unix=new unix();
	$unix->THREAD_COMMAND_SET("/etc/init.d/artica-postfix restart apache");
}

function changeRootPasswd(){
	$unix=new unix();
	$echo=$unix->find_program("echo");
	$passwd=base64_decode($_GET["pass"]);
	$chpasswd=$unix->find_program("chpasswd");
	$pass=$unix->shellEscapeChars($pass);
	$cmd="$echo \"root:$passwd\" | $chpasswd 2>&1";
	exec("$cmd",$results);
	writelogs_framework("$cmd " .count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	while (list ($num, $line) = each ($results)){writelogs_framework("$line",__FUNCTION__,__FILE__,__LINE__);}
	
}
function greensql_logs(){
	$unix=new unix();
	$php=$unix->LOCATE_PHP5_BIN();
	$tail=$unix->find_program("tail");
	$cmd=trim("$tail -n 300 /var/log/greensql.log 2>&1 ");
	
	exec($cmd,$results);		
	writelogs_framework($cmd ." ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
}
function openemm_status(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --openemm --nowachdog",$results);
	echo "<articadatascgi>". base64_encode(@implode("\n",$results))."</articadatascgi>";		
}

function ntpd_status(){
	exec(LOCATE_PHP5_BIN2()." /usr/share/artica-postfix/exec.status.php --ntpd --nowachdog",$results);
	echo "<articadatascgi>". base64_encode(@implode("\n",$results))."</articadatascgi>";		
}

function openemm_restart(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup /etc/init.d/artica-postfix restart openemm >/dev/null 2>&1 &");
	shell_exec($cmd);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);		
}

function pureftpd_reload(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup /usr/share/artica-postfix/bin/artica-install --pure-ftp-reload >/dev/null 2>&1 &");
	shell_exec($cmd);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);		
}
function pureftpd_restart(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup /etc/init.d/artica-postfix restart ftp >/dev/null 2>&1 &");
	shell_exec($cmd);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);		
}

function mysqld_perso(){
	$datas=base64_encode(@file_get_contents("/etc/artica-postfix/my.cnf.mysqld"));
	echo "<articadatascgi>$datas</articadatascgi>";	
}
function mysqld_perso_save(){
	$datas=base64_decode($_GET["mysqld-perso-save"]);
	@file_put_contents("/etc/artica-postfix/my.cnf.mysqld", trim($datas));
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup /etc/init.d/artica-postfix restart mysql >/dev/null 2>&1 &");
	shell_exec($cmd);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);			
	
}
function PHP_INI_SET(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup /usr/share/artica-postfix/bin/artica-install --php-ini >/dev/null 2>&1 &");
	shell_exec($cmd);
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);		
}

function AdCacheMysql(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup ".$unix->LOCATE_PHP5_BIN(). " /usr/share/artica-postfix/exec.activedirectory-import.php >/dev/null 2>&1");
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);	
	shell_exec($cmd);
	
}

function kav4proxy_reload(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup ".$unix->LOCATE_PHP5_BIN(). " /usr/share/artica-postfix/exec.kav4proxy.php --reload >/dev/null 2>&1");
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);	
	shell_exec($cmd);	
}

function mysql_events(){
	
	if(!is_file("/var/run/mysqld/mysqld.err")){
		$datas=base64_encode(serialize(array("{error_no_datas}")));
		echo "<articadatascgi>$datas</articadatascgi>";
		return;
	}
	
	$unix=new unix();
	$tail=$unix->find_program("tail");
	$cmd="$tail -n 300 /var/run/mysqld/mysqld.err 2>&1";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	exec($cmd,$results);
	$datas=base64_encode(serialize($results));
	echo "<articadatascgi>$datas</articadatascgi>";
	
	
}

function artica_schedule_cron(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup ".$unix->LOCATE_PHP5_BIN(). " /usr/share/artica-postfix/exec.fcron.php --artica-schedule >/dev/null 2>&1");
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);	
	shell_exec($cmd);		
	
}
function artica_schedule_reboot(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup ".$unix->LOCATE_PHP5_BIN(). " /usr/share/artica-postfix/exec.fcron.php --artica-reboot-schedule >/dev/null 2>&1");
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);	
	shell_exec($cmd);	
}


function phpldapadmin(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim("$nohup ".$unix->LOCATE_PHP5_BIN(). " /usr/share/artica-postfix/exec.phpldapadmin.php --build >/dev/null 2>&1");
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);	
	shell_exec($cmd);		
}

function mysql_ocs(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$cmd=trim($unix->LOCATE_PHP5_BIN(). " /usr/share/artica-postfix/exec.mysql.build.php --checks >/dev/null 2>&1");
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);	
	shell_exec($cmd);
}

function GETclock(){
	$unix=new unix();
	$date=$unix->find_program("date");
	$hwclock=$unix->find_program("hwclock");
	exec("$date +\"%Y-%m-%d;%H:%M:%S\" 2>&1",$results);
	$dateTEXT=@implode("",$results);
	if(is_file($hwclock)){
		exec("$hwclock --show 2>&1",$results2);
		writelogs_framework("$hwclock --show ". count($results2)." rows",__FUNCTION__,__FILE__,__LINE__);
		$hwclockTEXT=@implode("",$results2);
	}else{
		writelogs_framework("hwclock no such binary",__FUNCTION__,__FILE__,__LINE__);
	}
	writelogs_framework("$dateTEXT|$hwclockTEXT",__FUNCTION__,__FILE__,__LINE__);
	$array[0]=$dateTEXT;
	$array[1]=$hwclockTEXT;
	$finale=base64_encode(serialize($array));
	
	echo "<articadatascgi>$finale</articadatascgi>";
	
}


