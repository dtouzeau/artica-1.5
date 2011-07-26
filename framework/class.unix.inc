<?php
ini_set("memory_limit","400M");
include_once(dirname(__FILE__).'/class.ini-frame.inc');
include_once("/usr/share/artica-postfix/ressources/class.sockets.inc");
include_once("/usr/share/artica-postfix/ressources/class.ini.inc");
$GLOBALS["SSLKEY_PATH"]="/etc/ssl/certs/apache";
$GLOBALS["MAX_RECUSIVE_DIRS"]=200;
$GLOBALS["RECURSVIVE_INC"]=0;
class unix {
	
	var $proc_status;
	
	function unix(){
		
	}
	
	function __construct() {
	
	}
	
	function __destruct() {
	
	}
	

	PUBLIC function LOCATE_SYSLOG_INITD(){
			if(is_file("/etc/init.d/rsyslog")){return "/etc/init.d/rsyslog";}
			if(is_file("/etc/init.d/syslog")){return "/etc/init.d/syslog";}
			if(is_file("/etc/init.d/sysklogd")){return "/etc/init.d/sysklogd";}
	}
	
	
	
	PUBLIC function LOCATE_SSHD_PID_PATH(){
		if(is_file("/var/run/sshd.pid")){return "/var/run/sshd.pid";}
		if(is_file("/var/run/sshd.init.pid")){return "/var/run/sshd.init.pid";}
	}
	
	PUBLIC function LOCATE_SSHD_CONFIG_PATH(){
		if(is_file("/etc/ssh/sshd_config")){return "/etc/ssh/sshd_config";}
		
	}	
	
	
public function LOCATE_VBoxHeadless(){
	if(is_file("/usr/lib/virtualbox/VBoxHeadless")){return "/usr/lib/virtualbox/VBoxHeadless";}
	if(is_file("/usr/local/lib/virtualbox/VBoxHeadless")){return "/usr/local/lib/virtualbox/VBoxHeadless";}
	}	
	
	public function LOCATE_CYRRECONSTRUCT(){
		if(is_file("/usr/sbin/cyrreconstruct")){return "/usr/sbin/cyrreconstruct";}
		if(is_file("/usr/lib/cyrus/bin/reconstruct")){return "/usr/lib/cyrus/bin/reconstruct";}
		if(is_file("/usr/lib/cyrus-imapd/reconstruct")){return "/usr/lib/cyrus-imapd/reconstruct";}
		$this->events("Unable to stat cyrus reconstruct tool");
	}
	
	public function LOCATE_ROUNDCUBE_WEBFOLDER(){
		if(is_file("/usr/share/roundcube/index.php")){return "/usr/share/roundcube";}
	    if(is_file("/usr/share/roundcubemail/index.php")){return "/usr/share/roundcubemail";}
 	}
	
	public function LOCATE_SLPAD_PATH(){
		if(is_file("/usr/sbin/slapd")){return "/usr/sbin/slapd";}
		if(is_file("/usr/lib/openldap/slapd")){return "/usr/lib/openldap/slapd";}
		return $this->find_program("slpad");
	}
	
	public function LOCATE_SIEVEC(){
		if(is_file("/usr/sbin/sievec")){return "/usr/sbin/sievec";}
		if(is_file("/usr/lib/cyrus/bin/sievec")){return "/usr/lib/cyrus/bin/sievec";}
		if(is_file("/usr/lib/cyrus-imapd/sievec")){return "/usr/lib/cyrus-imapd/sievec";}
		$this->events("Unable to stat cyrus sievec tool");
	}
	
	public function LOCATE_CLAMDCONF(){
		if(is_file("/etc/clamav/clamd.conf")){return "/etc/clamav/clamd.conf";}
		if(is_file("/etc/clamd.conf")){return "/etc/clamd.conf";}
		if(is_file("/usr/local/etc/clamav/clamd.conf")){return "/usr/local/etc/clamav/clamd.conf";}
		if(is_file("/usr/local/etc/clamd.conf")){return "/usr/local/etc/clamd.conf";}
		}
		
	public function LOCATE_AWSTATS_BIN(){
		$f[]="/usr/local/awstats/cgi-bin/awstats.pl";
		$f[]="/usr/local/awstats/wwwroot/cgi-bin/awstats.pl";
		$f[]="/usr/lib/cgi-bin/awstats.pl";
		$f[]="/usr/share/awstats/wwwroot/cgi-bin/awstats.pl";
		$f[]="/var/www/awstats/awstats.pl";
		$f[]="/srv/www/cgi-bin/awstats.pl";
		$f[]="/opt/artica/awstats/wwwroot/cgi-bin/awstats.pl";
		$f[]="/root/awstats-6.3/wwwroot/cgi-bin/awstats.pl";
		$f[]="/home/apache/cgi-bin/awstats.pl";
		$f[]="/srv/www/cgi-bin/awstats/awstats.pl";
		while (list ($num, $path) = each ($f) ){if(is_file($path)){return $path;}}			
	}
	public function LOCATE_AWSTATS_BUILDSTATICPAGES_BIN(){
		$f[]="/usr/local/awstats/tools/awstats_buildstaticpages.pl";
		$f[]="/usr/share/awstats/tools/awstats_buildstaticpages.pl";
		$f[]="/root/awstats-6.3/tools/awstats_buildstaticpages.pl";
		$f[]="/usr/share/awstats/awstats_buildstaticpages.pl";
		$f[]="/usr/share/doc/awstats/examples/awstats_buildstaticpages.pl";
		$f[]="/srv/www/ispcp/engine/awstats/awstats_buildstaticpages.pl";
		while (list ($num, $path) = each ($f) ){if(is_file($path)){return $path;}}
		
		
	}
	
	public function LOCATE_VBOX_ADDITIONS_PID(){	
		if(is_file("/var/run/vboxadd-service")){return "/var/run/vboxadd-service";}
		if(is_file("/var/lock/subsys/vboxadd-service")){return "/var/lock/subsys/vboxadd-service";}
		if(is_file("/var/run/vboxadd-service")){return "/var/run/vboxadd-service";}
		if(is_file("/var/run/vboxadd-service.pid")){return "/var/run/vboxadd-service.pid";}
	}
	
	
	public function CreateUnixUser($username,$groupname=null,$displayname=null){
		if($this->UnixUserExists($username)){return true;}
		include_once(dirname(__FILE__)."/class.settings.inc");
		$user=new usersMenus();
		if($groupname==null){$groupname=$username;}
		if($displayname==null){$displayname=$username;}
		
		if(!$this->UnixGroupExists($groupname)){
			$groupadd=$this->find_program("groupadd");
			$cmd="$groupadd $groupname >/dev/null 2>&1";
			if($GLOBALS["VERBOSE"]){echo "CreateUnixUser() $user->LinuxDistriCode \"$cmd\"\n";}
			shell_exec($cmd);	
			if(!$this->UnixGroupExists($groupname)){return false;}
		}
		
		$cmd=null;
		$useradd=$this->find_program("useradd");
		if($user->LinuxDistriCode=="CENTOS"){
			$cmd="$useradd -m -g $groupname -d /home/$username -c \"$displayname\" $username >/dev/null 2>&1";
		}
		if($user->LinuxDistriCode=="SUSE"){
			$cmd="$useradd -m -g $groupname -d /home/$username -c \"$displayname\" $username >/dev/null 2>&1";
		}		
		if($user->LinuxDistriCode=="FEDORA"){
			$cmd="$useradd -m -g $groupname -d /home/$username -c \"$displayname\" $username >/dev/null 2>&1";
		}
		if($user->LinuxDistriCode=="MANDRAKE"){
			$cmd="$useradd -m -g $groupname -d /home/$username -c \"$displayname\" $username >/dev/null 2>&1";
		}						
		if($cmd==null){
			$cmd="useradd -m -g $groupname -G adm -d /home/$username -s /bin/bash -c \"$displayname\" $username >/dev/null 2>&1";
		}
		
		
		
		if($GLOBALS["VERBOSE"]){echo "CreateUnixUser() $user->LinuxDistriCode \"$cmd\"\n";}
		shell_exec($cmd);	
		return $this->UnixUserExists($username);
		
		
	}
	
	public function JAVA_HOME_GET(){
		$f=explode("\n", @file_get_contents("/etc/environment"));
		while (list ($num, $val) = each ($f) ){
			if(preg_match("#JAVA_HOME=(.+)#", $val,$re)){
				$re[1]=str_replace('"', "", $re[1]);
				if(is_dir($re[1])){return $re[1];}
			}
		}
		
	}
	
	public function JAVA_HOME_SET($dir){
		$f=explode("\n", @file_get_contents("/etc/environment"));
		while (list ($num, $val) = each ($f) ){
			if(preg_match("#JAVA_HOME=(.+)#", $val,$re)){
				$re[1]=str_replace('"', "", $re[1]);
				if($re[1]==$dir){return;}
				$f[$num]="JAVA_HOME=\"$dir\"";
				@file_put_contents("/etc/environment", @implode("\n", $f));
				return;
			}
		}
		
		$f[]="JAVA_HOME=\"$dir\"";
		@file_put_contents("/etc/environment", @implode("\n", $f));
		
	}	
	
	private function UnixGroupExists($groupname){
		$getent=$this->find_program("getent");
		exec("$getent group $groupname 2>&1",$results);
		while (list ($num, $val) = each ($results) ){
			if(preg_match("#^$groupname#", $val)){
				if($GLOBALS["VERBOSE"]){echo "UnixGroupExists() $val Match\n";}
				return true;}else{if($GLOBALS["VERBOSE"]){echo "UnixGroupExists() $val no match\n";}}
		}
		if($GLOBALS["VERBOSE"]){echo "UnixGroupExists() group:$groupname does not exists in database\n";}	
		return false;
		
	}
	
	private function UnixUserExists($username){
		
		$getent=$this->find_program("getent");
		$cmd="$getent shadow $username 2>&1";
		if($GLOBALS["VERBOSE"]){echo "UnixUserExists() \"$cmd\"\n";}
		exec($cmd,$results);
		while (list ($num, $val) = each ($results) ){
			if(preg_match("#^$username#", $val)){
				if($GLOBALS["VERBOSE"]){echo "UnixUserExists() $val Match\n";}
				return true;}
				if($GLOBALS["VERBOSE"]){echo "UnixUserExists() $val No match\n";}
		}
		if($GLOBALS["VERBOSE"]){echo "UnixUserExists() user:$username does not exists in database\n";}	
		return false;
	}
	


	public function sysctl($key,$value){
		$keyS=str_replace(".", "\.", $key);
		$t=false;
		$f=explode("\n",@file_get_contents("/etc/sysctl.conf"));
		while (list ($num, $val) = each ($f) ){
			if(substr($val, 0,1)=="#"){continue;}
			if(preg_match("#^$keyS.*?=#", $val)){
				$f[$num]="$key = $value";
				$t=true;
				break;
			}
		}
		
		if(!$t){
			$f[]="$key = $value";
		}
		reset($f);
		@file_put_contents("/etc/sysctl.conf", implode("\n", $f));
		
	}
	
	
	
	
	
	public function LOCATE_CLAMDSOCKET(){
		$f=@explode("\n",@file_get_contents($this->LOCATE_CLAMDCONF()));
		while (list ($num, $val) = each ($f) ){
			if(preg_match("#LocalSocket\s+(.+)#",$val,$re)){
				return trim($re[1]);
			}
		}
	}
	
	public function POLICYD_WEIGHT_GET($key){
		$f=@explode("\n",@file_get_contents($this->LOCATE_POLICYDWEIGHT_CONF()));
		while (list ($num, $val) = each ($f) ){
			if(preg_match("#\$$key.*?=(.+?);#",$val)){
				return trim($re[1]);
			}
		}
	}
	
	public function SQUID_GET_DATADIR(){
		$unix=new unix();
		$squidbin=$this->find_program("squid3");
		if(!is_file($squidbin)){$squidbin=$this->find_program("squid");}
		exec("$squidbin -v",$res);
		$line=@implode(" ",$res);
		if(preg_match("#--datadir=(.+?)'#",$line,$re)){return trim($re[1]);}
	}
	
	
	public function LOCATE_POLICYDWEIGHT_CONF(){
		    $l[]='/etc/policyd-weight.conf';
    		$l[]='/etc/postfix/policyd-weight.cf';	
    		$l[]='/usr/local/etc/policyd-weight.conf';

    		while (list ($num, $path) = each ($l) ){
    			if(is_file($path)){return $path;}
    		}
		
	}
	
	
private function icmpChecksum($data){
	if (strlen($data)%2)
	$data .= "\x00";
	 
	$bit = unpack('n*', $data);
	$sum = array_sum($bit);
	 
	while ($sum >> 16)
	$sum = ($sum >> 16) + ($sum & 0xffff);
	 
	return pack('n*', ~$sum);
}	


public function PingHost($cible){
	$timeout=1;
    $package = "\x08\x00\x7d\x4b\x00\x00\x00\x00PingHost";
    $socket  = socket_create(AF_INET, SOCK_RAW, 1);
    socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => $timeout, 'usec' => 0));
	socket_connect($socket, $cible, null);
	$ts = microtime(true);
    socket_send($socket, $package, strLen($package), 0);
    if (socket_read($socket, 255))
    $result = microtime(true) - $ts;
    else    $result = false;
	socket_close($socket);
	return $result;
	}
	
	public function KAV4PROXY_GET_VALUE($key,$value){
		    $ini=new iniFrameWork($this->KAV4PROXY_GET_CONF_PATH());
		    return $ini->get($key,$value);
		
	}	


	public function KAV4PROXY_GET_CONF_PATH(){
		if(is_file('/etc/opt/kaspersky/kav4proxy.conf')){return '/etc/opt/kaspersky/kav4proxy.conf';}
		if(is_file('/opt/kaspersky/kav4proxy/etc/opt/kaspersky/kav4proxy.conf')){return '/opt/kaspersky/kav4proxy/etc/opt/kaspersky/kav4proxy.conf';}
	}
	
	public function KAV_MILTER_PATTERN_FILE(){
		if(is_file("/var/db/kav/5.6/kavmilter/bases/master.xml")){return "/var/db/kav/5.6/kavmilter/bases/master.xml";}
		if(is_file("/var/db/kav/5.6/kavmilter/bases/kavset.xml")){return "/var/db/kav/5.6/kavmilter/bases/kavset.xmll";}
		if(is_file("/var/db/kav/5.6/kavmilter/bases/av-i386-0607g.xml")){return "/var/db/kav/5.6/kavmilter/bases/av-i386-0607g.xml";}
	}
	
	
	public function LOCATE_SMBD_PID(){
		if(is_file("/var/run/smbd.pid")){return "/var/run/smbd.pid";}
		if(is_file("/var/run/samba/smbd.pid")){return "/var/run/samba/smbd.pid";}
		
	}
	public function LOCATE_NMBD_PID(){
		if(is_file("/var/run/nmbd.pid")){return "/var/run/nmbd.pid";}
		if(is_file("/var/run/samba/nmbd.pid")){return "/var/run/samba/nmbd.pid";}
		
	}	
	public function LOCATE_WINBINDD_PID(){
		if(is_file("/var/run/winbindd.pid")){return "/var/run/winbindd.pid";}
		if(is_file("/var/run/samba/winbindd.pid")){return "/var/run/samba/winbindd.pid";}
		
	}	

	public function LOCATE_CYRUS_IPURGE(){
		if(is_file("/usr/sbin/ipurge")){return "/usr/sbin/ipurge";}
		if(is_file("/usr/lib/cyrus/bin/ipurge")){return "/usr/lib/cyrus/bin/ipurge";}
		if(is_file("/usr/lib/cyrus-imapd/ipurge")){return "/usr/lib/cyrus-imapd/ipurge";}
		$this->events("Unable to stat cyrus ipurge tool");
	}	

	
	public function LOCATE_CYRQUOTA(){
		if(is_file("/usr/sbin/cyrquota")){return "/usr/sbin/cyrquota";}
		if(is_file("/usr/lib/cyrus/bin/quota")){return "/usr/lib/cyrus/bin/quota";}
		if(is_file("/usr/lib/cyrus-imapd/quota")){return "/usr/lib/cyrus-imapd/quota";}
		$this->events("Unable to stat cyrus cyrquota tool");
	}
	
	public function LOCATE_CYRUS_PIDPATH(){
		if(is_file("/var/run/cyrus-master.pid")){return "/var/run/cyrus-master.pid";}
		if(is_file("/var/run/cyrmaster.pid")){return "/var/run/cyrmaster.pid";}
		if(is_file("/var/run/cyrus.pid")){return "/var/run/cyrus.pid";}
		$this->events("Unable to stat cyrus pid path");
	}

	public function KAV4PROXY_PATTERN(){
		exec("/usr/share/artica-postfix/bin/artica-update --kav4proxy--pattern",$results);
		return trim(@implode("",$results));
		
	}
	

	
	public function LOCATE_MILTER_GREYLIST_PID(){
		if(is_file("/var/run/milter-greylist/milter-greylist.pid")){return "/var/run/milter-greylist/milter-greylist.pid";}
	}
	
	public function IS_OPENLDAP_RUNNING(){
		$pid_path=$this->LOCATE_SLAPD_PID_PATH();
		$pidnum=$this->get_pid_from_file($pid_path);
		if($GLOBALS["DEBUG"]){echo __FUNCTION__." pid num: $pidnum\n";}
		return $this->process_exists($pidnum);
	}
	
	private function LOCATE_SLAPD_PID_PATH(){
		if(isset($GLOBALS[__FUNCTION__])){if($GLOBALS[__FUNCTION__]<>null){return $GLOBALS[__FUNCTION__];}}
		$path=$this->LOCATE_SLAPD_CONF_PATH();
		$f=explode("\n",@file_get_contents($path));
		while (list ($num, $line) = each ($f)){
			if(preg_match("#pidfile\s+(.+)#",$line,$re)){
				if($GLOBALS["DEBUG"]){echo __FUNCTION__." pid path: $path\n";}
				$GLOBALS[__FUNCTION__]=trim($re[1]);
				return trim($re[1]);
			}else{
			
			}
		}
		
	}
	
	private function LOCATE_SLAPD_CONF_PATH(){
		if(isset($GLOBALS[__FUNCTION__])){if($GLOBALS[__FUNCTION__]<>null){return $GLOBALS[__FUNCTION__];}}
		$f[]="/etc/ldap/slapd.conf";
		$f[]="/etc/openldap/slapd.conf";
		$f[]="/usr/local/etc/openldap/slapd.conf";
		while (list ($num, $path) = each ($f)){
			if(is_file($path)){
				if($GLOBALS["DEBUG"]){echo __FUNCTION__." path: $path\n";}
				$GLOBALS[__FUNCTION__]=$path;
				return $path;
			}
		}
		
		
	}
	
	private function VERBOSE_COMMAND_LINE($pid){
		return @file_get_contents("/proc/$pid/cmdline");
	}
	
	Public function GetSingleMemoryOf($pid){
		if($pid<1){
			if($GLOBALS["DEBUG"]){echo __FUNCTION__." bad pid \"$pid\"\n";}
			return null;
		}
		$rss=$this->PROCESS_MEMORY($pid,true);
		$vm=$this->PROCESS_CACHE_MEMORY($pid,true);
		if($this->process_exists($pid)){$l[]="installed=1";$l[]="running=1";}
		$l[]="master_pid=$pid";	
   	 	$l[]="master_memory=$rss";
    	$l[]="master_cached_memory=$vm";
    	$l[]="processes_number=1";
    	
    	if(!is_file("/usr/share/artica-postfix/bin/procstat")){
    		$gcc=$this->find_program("gcc");
    		if(!is_file($gcc)){return;}
    		shell_exec("$gcc -o /usr/share/artica-postfix/bin/procstat /usr/share/artica-postfix/bin/install/procstat.c");
    	} 	
    	
    	if(!is_file("/usr/share/artica-postfix/bin/procstat")){
    		$this->events("Unable to compile procstat !!! in " .__CLASS__."/".__FUNCTION__." Line".__LINE__);
    	}
		
  	exec("/usr/share/artica-postfix/bin/procstat $pid 2>&1",$results);
    while (list ($num, $ligne) = each ($results) ){
    	if(preg_match("#GLIBC.+?not found#",$ligne)){
    		@unlink("/usr/share/artica-postfix/bin/procstat");
    		return;
    	}
    	
    	if(preg_match("#start_time:.+?\(([0-9]+)#",$ligne,$re)){
    		$tt=$this->time_duration($re[1]);
    		$tt=str_replace(" Months,","M",$tt);
    		$tt=str_replace(" days,","d",$tt);
    		$tt=str_replace(" hours,","h",$tt);
    		$tt=str_replace(" minutes,","mn",$tt);
    		$tt=str_replace(" seconds","s",$tt);
    		$tt=str_replace(" hour,","h",$tt);
    		$tt=str_replace(" minute,","mn",$tt);
    		$tt=str_replace(" second","s",$tt);    		
    		$l[]="uptime=$tt";}
    		
    	}
    $l[]="";	
    return implode("\n",$l);return;    	
    	
		
	}
	
function THREAD_COMMAND_SET($zcommands){
	  $md5command=md5(trim($zcommands));
	  $FileDataCommand=@file_get_contents('/etc/artica-postfix/background');
	  $tbl=explode("\n",$FileDataCommand);
	 
	if(is_array($tbl)){
		while (list ($num, $ligne) = each ($tbl) ){
			if($ligne==null){continue;}
			 $mdtmp=md5(trim($ligne));
			 if($md5command==$mdtmp){
			 	return false;
			 }
			}
		reset($tbl);
		}
	$tbl[]=$zcommands;
	@file_put_contents('/etc/artica-postfix/background',implode("\n",$tbl));
}	
	
	
public function GetMemoriesOf($pid){
	if($pid<1){
	if($GLOBALS["DEBUG"]){echo __FUNCTION__." bad pid \"$pid\"\n";}
		return null;
	}
	$pid=$this->PROCESS_PPID($pid);
	$rss=$this->PROCESS_MEMORY($pid,true);
	$vm=$this->PROCESS_CACHE_MEMORY($pid,true);
	$cmd=$this->find_program("pgrep")." -P $pid";
	if($GLOBALS["DEBUG"]){echo __FUNCTION__." $cmd\n";}
	$mpid=getmypid();
	exec($cmd,$results);
	$count=1;
	while (list ($num, $ligne) = each ($results) ){
		$ligne=trim($ligne);
		if($ligne<50){continue;}
		if($ligne==$mpid){continue;}
		$count=$count+1;
		$rss0=$this->PROCESS_MEMORY($ligne,true);
		$vm0=$this->PROCESS_CACHE_MEMORY($ligne,true);
		$rss=$rss+$rss0;
		$vm=$vm+$vm0;		
		if($GLOBALS["DEBUG"]){echo __FUNCTION__."PID $ligne \"$count\" rss:$rss0 vm:$vm0 Process: \"". $this->VERBOSE_COMMAND_LINE($ligne)."\"\n";}
	}
	if($count==0){$count=1;}
	if($this->process_exists($pid)){$l[]="installed=1";$l[]="running=1";}
	$l[]="application_installed=1";
	$l[]="master_pid=$pid";	
    $l[]="master_memory=$rss";
    $l[]="master_cached_memory=$vm";
    $l[]="processes_number=$count";
    
    
    exec("/usr/share/artica-postfix/bin/procstat $pid 2>&1",$results);
    while (list ($num, $ligne) = each ($results) ){
    	if(preg_match("#start_time:.+?\(([0-9]+)#",$ligne,$re)){
    		$tt=$this->time_duration($re[1]);
    		$tt=str_replace(" Months,","M",$tt);
    		$tt=str_replace(" days,","d",$tt);
    		$tt=str_replace(" hours,","h",$tt);
    		$tt=str_replace(" minutes,","mn",$tt);
    		$tt=str_replace(" seconds","s",$tt);
    		$tt=str_replace(" hour,","h",$tt);
    		$tt=str_replace(" minute,","mn",$tt);
    		$tt=str_replace(" second","s",$tt);    		
    		$l[]="uptime=$tt";}
    		
    	}
    $l[]="";	
    return implode("\n",$l);return;
	
}

function PROCESS_TTL($pid){
	if($pid<2){return 0;}
	exec("/usr/share/artica-postfix/bin/procstat $pid 2>&1",$results);
	while (list ($num, $ligne) = each ($results) ){
		if(preg_match("#start_time:.+?\(([0-9]+)#",$ligne,$re)){
			$seconds=$re[1];
			return round(($seconds/60));
		}
	}
}

function vhosts_BuildCertificate($hostname){
	$dir=$GLOBALS["SSLKEY_PATH"];
	if(is_file("$dir/$hostname.crt")){
		if(is_file("$dir/$hostname.key")){
			return true;
		}
	}
	@mkdir($dir,0600,true);
	
	$sock=new sockets();
	$CertificateMaxDays=$sock->GET_INFO('CertificateMaxDays');
	if($CertificateMaxDays==null){$CertificateMaxDays=730;}
	if(!class_exists("Bs_IniHandler")){
		$ini=new iniFrameWork();
	}else{
		$ini=new Bs_IniHandler();
	}
	
	$ini->loadFile("/etc/artica-postfix/ssl.certificate.conf");
	$conf="/etc/ssl/certs/apache/$hostname.conf";
	$openssl=$this->find_program("openssl");
	echo "Starting......: Certificate \"$conf\" ". count($ini->_params) ." Keys\n";
	
	unset($ini->_params["HOSTS_ADDONS"]);
	$ini->_params["default_db"]["default_days"]=$CertificateMaxDays;
	$ini->_params["server_policy"]["commonName"]=$hostname;
	$ini->_params["user_policy"]["commonName"]=$hostname;
	$ini->_params["default_ca"]["commonName"]=$hostname;
	$ini->_params["default_ca"]["commonName_value"]=$hostname;
	$ini->_params["policy_match"]["commonName"]=$hostname;
	$ini->_params["policy_anything"]["commonName"]=$hostname;
	reset($ini->_params);
	$ini->saveFile($conf);
	$cmd="$openssl req -new -x509 -batch -config $conf -nodes -out $dir/$hostname.crt -keyout $dir/$hostname.key -days $CertificateMaxDays 2>&1";
	exec($cmd,$results);
	if($GLOBALS["VERBOSE"]){
		echo "$cmd\n".@implode("\n",$results)."\n";
	}
	
}

function getLOCALEUSED(){
	$locale=$this->find_program("env");
	writelogs_framework("$locale 2>&1",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);
	exec("$locale 2>&1",$results);
	while (list ($num, $ligne) = each ($results) ){
		writelogs_framework("$ligne",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);
		if(preg_match("#LANG=(.+)#",$ligne,$re)){return trim($re[1]);}
	}
	
	shell_exec("LANG=en_US.utf8");
	return "en_US.utf8";
}


function SSHD_GET_FINGERPRINT($path){
	$path=$this->shellEscapeChars($path);
	$sshkeygen=$this->find_program("ssh-keygen");
	$maincmd="$sshkeygen -lf $path";

	writelogs_framework("$maincmd 2>&1" ,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
	exec($maincmd,$results);
	while (list ($num, $ligne) = each ($results) ){
		writelogs_framework("$ligne" ,__FUNCTION__,__FILE__,__LINE__);
		if(preg_match("#^[0-9]+\s+(.+?)\s+#",$ligne,$re)){
			writelogs_framework("FOUND {$re[1]}" ,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			return $re[1];
		}
	}
}

function RESTART_SYSLOG(){
			if(is_file("/etc/init.d/rsyslog")){shell_exec("/etc/init.d/rsyslog restart");}
			if(is_file("/etc/init.d/syslog")){shell_exec("/etc/init.d/syslog restart");}
			if(is_file("/etc/init.d/sysklogd")){shell_exec("/etc/init.d/sysklogd restart");}
			if(is_file("/etc/init.d/syslog-ng")){shell_exec("/etc/init.d/syslog-ng restart");}
			
			shell_exec("/etc/init.d/artica-postfix restart sysloger");
			shell_exec("/etc/init.d/artica-postfix restart postfix-logger");
			shell_exec("/etc/init.d/artica-postfix restart squid-tail");
			shell_exec("/etc/init.d/artica-postfix restart ufdb-tail");	
	
}

function send_email_events($subject,$text,$context,$date=null,$attached_files=array(),$recipient=null){
	
	$arr_APP=array(
		"APP_OPENEMM"=>"OpenEMM service",
		"APP_TOMCAT"=>"Tomcat Web server",
		"APP_GREENSQL"=>"GreenSQL Firewall",
		"APP_ZARAFA_WEB"=>"Zarafa web server",
		"APP_ZARAFA_SERVER"=>"Zarafa server",
		"APP_ZARAFA_SPOOLER"=>"Zarafa Spooler",
		"APP_ZARAFA_DAGENT"=>"Zarafa Delivery Agent",
		"APP_ZARAFA_GATEWAY"=>"Zarafa Gateway",			
		"APP_ZARAFA_MONITOR"=>"Zarafa Monitor",	
		"APP_ZARAFA_ICAL"=>"iCal, CalDav Gateway",
		"APP_ZARAFA_LICENSED"=>"Zarafa license service",
		"APP_ZARAFA"=>"Zarafa Mail server",
		"APP_SNORT"=>"Intrusion Detection System (Snort)",
		"APP_OCSI"=>"OCS Inventory",
		"APP_ARTICA_AUTH_TAIL"=>"Authentication Watchdog",
		"APP_VNSTAT"=>"VNStat",
		"APP_POSTFWD2"=>"Postfix Postfwd2",
		"APP_VMTOOLS"=>"VMWare tools",
		"APP_IETD"=>"iSCSI service",
		"APP_GREYHOLE"=>"Greyhole Samba relatime Backup",
		"APP_AUTOFS"=>"AutoFS, Automount service",
		"APP_MUNIN"=>"Munin Statistics Client",
		"APP_BACKUPPC"=>"BackupPC Daemon",
		"APP_STUNNEL"=>"sTunnel daemon",
		
		"APP_UFDBGUARD_TAIL"=>"UfdbGuard Watchdog",
		"APP_OPENVPN"=>"OpenVPN Server",
		"OPENVPN_SERVER"=>"OpenVPN server",
		"APP_VBOXADDITIONS"=>"VirtualBox guest additions",
		
		"APP_ARTICA_EXECUTOR"=>"Artica Executor Daemon",
		"APP_GLUSTER"=>"Gluster Cluster daemon",
		"APP_APACHE_SRC"=>"Apache source Daemon",
		"APP_CLUEBRINGER"=>"ClueBringer (Policyd V2)",
		"APP_DDCLIENT"=>"Dynamic DNS Client",
		"APP_SQUID_CLAMAV_TAIL"=>"squidclamav RealTime log",
		"APP_ARTICA_SQUID_TAIL"=>"Squid RealTime log",
		"APP_CROSSROADS"=>"Crossroads Load balancing service",
		"APP_TFTPD"=>"Trivial File Transfer Protocol service",
		"APP_POLICYD_WEIGHT"=>"Policy Daemon for Postfix",
		"APP_VIRTUALBOX_WEBSERVICE"=>"VirtualBox Web service",
		"APP_ARTICA_POLICY"=>"Artica-Policy service",
		"APP_MILTER_DKIM"=>"Milter-DKIM service",
		"APP_DROPBOX"=>"DropBox service",
		"APP_ARTICA_SQUIDGUARDTAIL"=>"squidGuard watchdog daemon",
		"APP_UFDBGUARD"=>"UfdbGuard Filtering service",
		"APP_OPENDKIM"=>"OpenDKIM Filter service",
		"APP_SQUIDGUARD_HTTP"=>"SquidGuard HTTP service",
		"APP_DNSMASQ"=>"DNS forwarder caching",
		"APP_SMARTMONTOOLS"=>"SMART hard disk monitoring",
		"APP_AUDITD"=>"Auditd Daemon",
		"APP_OPENSSH"=>"OpenSSH server",
		"APP_KAV4FS"=>"Kaspersky For File server",
		"APP_MLDONKEY"=>"MLDonkey server",
		"APP_DHCP"=>"DHCP Server",
		"APP_ARTICA_NOTIFIER"=>"Artica SMTP Notifier",
		"APP_GROUPWARE_APACHE"=>"Apache for groupware applications",
		"APP_ROUNDCUBE"=>"Web service For RoundCube",
		"APP_ZABBIX_SERVER"=>"Zabbix server",
		"APP_MYSQL_ARTICA"=>"Mysql server",
		"APP_ARTICA_MYSQMAIL"=>"Postfix Monitor",
		"APP_ARTICA_DANSGUARDIAN_TAIL"=>"DansGuardian Monitor",
		"APP_ARTICA_WATCHDOG"=>"Artica Watchdog",
		"APP_ASSP"=>"Anti-Spam SMTP Proxy",
		"APP_BOA"=>"Boa Daemon",
		"APP_CLAMAV"=>"ClamAV Daemon",
		"APP_CUPS"=>"Printer service",
		"APP_CYRUS"=>"Cyrus-imap service",
		"APP_DANSGUARDIAN"=>"DansGuardian",
		"APP_FETCHMAIL_LOGGER"=>"Fetchmail Monitor",
		"APP_FRAMEWORK"=>"Artica FrameWork",
		"APP_FETCHMAIL"=>"Fetchmail Daemon",
		"APP_MAILMAN"=>"Mailman mailinglist Daemon",
		"APP_FRESHCLAM"=>"ClamAV Updater",
		"APP_KAS3_MILTER"=>"Kaspersky Anti-Spam hook",
		"APP_KAS3"=>"Kaspersky Anti-Spam service",
		"APP_KAVMILTER"=>"Kaspersky Anti-virus For Mail",
		"APP_KRETRANSLATOR_HTTPD"=>"Kaspersky Antivirus updater web service",
		"APP_LDAP"=>"OpenLDAP service",
		"APP_LIGHTTPD"=>"Artica Web server",
		"APP_MONIT"=>"Watchdog system",
		"APP_RSYNC"=>"Rsync service",
		"APP_PDNS_RECURSOR"=>"PowerDNS external query ",
		"APP_PDNS"=>"PowerDNS service",
		"APP_POSTFIX"=>"Postfix",
		"APP_SAMBA_NMBD"=>"Samba NetBIOS name service",
		"APP_SAMBA_WINBIND"=>"Samba Name Service Switch",
		"APP_SAMBA_SMBD"=>"Samba Filesharing and printing services",
		"APP_SASLAUTHD"=>"System Authentication",
		"APP_SPAMASSASSIN"=>"SpamAssassin",
		"APP_SQUID"=>"Proxy service (squid)",
		"APP_SYSLOGER"=>"Artica System monitor",
		"APP_KAV4PROXY"=>"Kaspersky Antivirus For Squid",
		"APP_C_ICAP"=>"C-ICAP For Squid",
		"APP_SCANNED_ONLY"=>"Samba Antivirus Scanner",
		"APP_PUREFTPD"=>"FTP Service",
		"APP_MILTERGREYLIST"=>"Greylist service for Postfix",
		"APP_AMAVISD_MILTER"=>"Amavis listener milter",
		"APP_AMAVISD_NEW"=>"Amavis Daemon",
		"APP_CLAMAV_MILTER"=>"ClamAV For Postfix",
		"APP_MAILSPY"=>"MailSpy",
		"APP_GROUPWARE_APACHE"=>"Apache for Groupwares",
		"APP_ARTICA"=>"Artica service",
		
		
	);
	

	while (list ($tok, $service_name) = each ($arr_APP) ){
		$subject=str_replace($tok,$service_name,$subject);
		$text=str_replace($tok,$service_name,$text);
	}
	include_once("/usr/share/artica-postfix/ressources/class.users.menus.inc");	
	$users=new usersMenus();
	if($date==null){
		$date=date('Y-m-d H:i:s');
		$datesubject=date('H:i:s');
	}else{
		$datesubject=$date;
	}
	$filename="/var/log/artica-postfix/events/".md5($context.$subject);
	if(is_file($filename)){return null;}
	$ini=new iniFrameWork($filename);
	$ini->set('LOG','processname',basename(__FILE__));
	$ini->set('LOG','date',$date);
	$ini->set('LOG','context',$context);
	$ini->set('LOG','subject',"[$datesubject]: [ARTICA]: ($users->hostname) : $subject");
	$ini->set('LOG','recipient',$recipient);
	$ini->saveFile($filename);
	$datas=@file_get_contents($filename);
	$datas=$datas."\n<text>$text</text>\n";
	$datas=$datas."\n<attachedfiles>". base64_encode(serialize($attached_files))."</attachedfiles>\n";
	
	
if($GLOBALS["VERBOSE"]){echo strlen($datas)." bytes file $filename text\n";}
	
	@file_put_contents($filename,$datas);
	@mkdir("/usr/share/artica-postfix/ressources/logs/jGrowl",0775,true);
	@file_put_contents("/usr/share/artica-postfix/ressources/logs/jGrowl/".basename($filename).".txt",$datas);
	@chmod("/usr/share/artica-postfix/ressources/logs/jGrowl/".basename($filename).".txt",0777);
	}

private function time_duration($seconds, $use = null, $zeros = false){
    // Define time periods
    $periods = array (
        'years'     => 31556926,
        'Months'    => 2629743,
        'weeks'     => 604800,
        'days'      => 86400,
        'hours'     => 3600,
        'minutes'   => 60,
        'seconds'   => 1
        );

    // Break into periods
    $seconds = (float) $seconds;
    $segments = array();
    foreach ($periods as $period => $value) {
        if ($use && strpos($use, $period[0]) === false) {
            continue;
        }
        $count = floor($seconds / $value);
        if ($count == 0 && !$zeros) {
            continue;
        }
        $segments[strtolower($period)] = $count;
        $seconds = $seconds % $value;
    }

    // Build the string
    $string = array();
    foreach ($segments as $key => $value) {
        $segment_name = substr($key, 0, -1);
        $segment = $value . ' ' . $segment_name;
        if ($value != 1) {
            $segment .= 's';
        }
        $string[] = $segment;
    }

    return implode(', ', $string);
}

public function GetVersionOf($name){
	if(!isset($GLOBALS["version-of-$name"])){$GLOBALS["version-of-$name"]=null;}
	if($GLOBALS["version-of-$name"]<>null){return $GLOBALS["version-of-$name"];}
	exec("/usr/share/artica-postfix/bin/artica-install --export-version $name",$results);
	$version=trim(implode("",$results));
	$version=trim(implode("",$results));
	$GLOBALS["version-of-$name"]=$version;
	return $version;	
}

  function unix_memory_usage() {
        $mem_usage = memory_get_usage(true);
       
        if ($mem_usage < 1024)
            echo $mem_usage." bytes";
        elseif ($mem_usage < 1048576)
            return round($mem_usage/1024,2)." kilobytes";
        else
            return round($mem_usage/1048576,2)." megabytes";
           
        
    } 

	
	public function DANSGUARDIAN_CATEGORIES(){
			$path="/etc/dansguardian/lists/blacklists";
			$unix=new unix();
			$dirs=$unix->dirdir($path);
			while (list ($num, $line) = each ($dirs)){
				
				
			}
		}
		
		
public function shellEscapeChars($path){
		$path=str_replace(" ","\ ",$path);
		$path=str_replace('$','\$',$path);
		$path=str_replace("&","\&",$path);
		$path=str_replace("?","\?",$path);
		$path=str_replace("#","\#",$path);
		$path=str_replace("[","\[",$path);
		$path=str_replace("]","\]",$path);
		$path=str_replace("{","\{",$path);
		$path=str_replace("}","\}",$path);
		$path=str_replace("*","\*",$path);
		$path=str_replace('"','\\"',$path);
		$path=str_replace("'","\\'",$path);
		$path=str_replace("(","\(",$path);
		$path=str_replace(")","\)",$path);
		$path=str_replace("<","\<",$path);				
		$path=str_replace(">","\>",$path);
		$path=str_replace("!","\!",$path);
		$path=str_replace("+","\+",$path);
		$path=str_replace(";","\;",$path);
		return $path;	
}		
		
		
	public function APACHE_GROUPWARE_ACCOUNT(){
		$tbl=explode("\n",@file_get_contents("/usr/local/apache-groupware/conf/apache-groupware.conf"));
		while (list ($num, $line) = each ($tbl)){
			if(preg_match("#User\s+(.+)#",$line,$re)){
				return "{$re[1]}:{$re[1]}";
			}
			
		}
		
	}
	
	public function APACHE_SRC_ACCOUNT(){
		$httpd_conf=$this->LOCATE_APACHE_CONF_PATH();
		$envars=dirname($httpd_conf)."/envvars";
		if(is_file($envars)){
			$tbl=explode("\n",@file_get_contents($envars));
			while (list ($num, $line) = each ($tbl)){
				if(preg_match("#export APACHE_RUN_USER=(.+)#",$line,$re)){
					return trim($re[1]);
				}
			}
		}
		if(is_file($httpd_conf)){
			$tbl=explode("\n",@file_get_contents($httpd_conf));
			while (list ($num, $line) = each ($tbl)){if(preg_match("#User\s+(.+)#",$line,$re)){return trim("{$re[1]}");}}
		}
		
	}	
	
public function EXEC_NICE(){
	$tmp=null;
	$useIonice=trim(@file_get_contents("/etc/artica-postfix/settings/Daemons/useIonice"));
	$useNice=trim(@file_get_contents("/etc/artica-postfix/settings/Daemons/useNice"));
	if($useIonice==null){$useIonice=1;}
	if($useNice==null){$useNice=1;}
	
	if(!class_exists("Bs_IniHandler")){
		$ini=new iniFrameWork("/etc/artica-postfix/performances.conf");
	}else{
		$ini=new Bs_IniHandler("/etc/artica-postfix/performances.conf");
	}
	
	
	if(isset($ini->_params)){
		if(isset($ini->_params["PERFORMANCES"]['ProcessNice'])){
			$tmp=$ini->_params["PERFORMANCES"]['ProcessNice'];
		}
	}
	if($tmp==null){$tmp=19;}
	if(!is_numeric($tmp)){$tmp=19;}
	if($useIonice==1){
		$ionice=$this->find_program("ionice");
		if(trim($ionice)<>null){$ionice="$ionice -c2 -n7 ";}
	}
	
	if($useNice==1){
		$nice=$this->find_program("nice");
		if(trim($nice)<>null){$nice="$nice --adjustment=$tmp ";}
	}
	if(!isset($ionice)){$ionice=null;}
	return "$ionice$nice";    	
}	
		
		
	public function LIGHTTPD_PORT($conf=null){
		if($conf==null){$conf=$this->LIGHTTPD_CONF_PATH();}
		$tbl=explode("\n",@file_get_contents($conf));
		while (list ($num, $line) = each ($tbl)){
			if(preg_match("#^server\.port.*?=.*?([0-9]+)#",$line,$re)){
				return $re[1];
			}
		}
		
	}
	
	public function LIGHTTPD_USER($conf=null){
		if($conf==null){$conf=$this->LIGHTTPD_CONF_PATH();}
		$tbl=explode("\n",@file_get_contents($conf));
		while (list ($num, $line) = each ($tbl)){
			if(preg_match("#^server\.username.*?=(.+?)$#",$line,$re)){
				$re[1]=str_replace("\"","",$re[1]);
				return trim($re[1]);
			}
		}
		
	}
	
	private function LIGHTTPD_CONF_PATH(){
		if(is_file('/etc/lighttpd/lighttpd.conf')){return '/etc/lighttpd/lighttpd.conf';}
		if(is_file('/usr/local/etc/lighttpd/lighttpd.conf')){return '/usr/local/etc/lighttpd/lighttpd.conf';}
		
	}
	
	public function MEMORY_OF($pid){
		$datas=explode("\n",@file_get_contents("/proc/$pid/status"));
		while (list ($index, $line) = each ($datas)){
		 if(preg_match("#^VmSize.+?([0-9]+)#",$line,$re)){
		 	return $re[1];
		 }
		}
		
	}
	
	public function PPID_OF($pid){
		$datas=explode("\n",@file_get_contents("/proc/$pid/status"));
		while (list ($index, $line) = each ($datas)){
		 if(preg_match("#^PPid.+?([0-9]+)#i",$line,$re)){
		 	if($re[1]<3){return $pid;}
		 	return $re[1];
		 }
		}
		
		return $pid;
		
	}

	public function GET_WIRELESS_CARD(){
		
		$t=explode("\n",@file_get_contents("/proc/net/wireless"));
		while (list ($index, $line) = each ($t)){
			if(preg_match("#(.+?):\s+([0-9]+)#",$line,$re)){return $re[1];}
		}
		
		$iwconfig=$this->find_program("iwconfig");
		if($iwconfig==null){
			if($GLOBALS["VERBOSE"]){echo "Unable to find iwconfig...\n";}
			return;
		}
		
		exec("$iwconfig 2>&1",$results);
		
		while (list ($index, $line) = each ($results)){
			if(preg_match("#^(.+?)\s+.+?ESSID#",$line,$re)){
				return $re[1];
			}else{
				if($GLOBALS["VERBOSE"]){echo "$line NO MATCH...\n";}
			}
		}
		
	}
	
	public function LOCATE_NET_BIN_PATH(){
		$net=$this->find_program("net");
		if(is_file($net)){return $net;}
		$net=$this->find_program("net.samba3");
		if(is_file($net)){return $net;}
	}
	
	
	public FUNCTION GET_LOCAL_SID(){
		if($GLOBALS[__FUNCTION__]<>null){return $GLOBALS[__FUNCTION__];}
		if($_SESSION[__CLASS__][__FUNCTION__]<>null){return $_SESSION[__CLASS__][__FUNCTION__];}
		if(is_file("/etc/artica-postfix/LOCAL_SID")){
			$filtime=$this->file_time_min("/etc/artica-postfix/LOCAL_SID");
			if($filtime<520){return @file_get_contents("/etc/artica-postfix/LOCAL_SID");}
		}		
		$net=$this->LOCATE_NET_BIN_PATH();
		
		exec("$net getlocalsid 2>&1",$results);
		while (list ($index, $line) = each ($results)){
			if(preg_match("#is:\s+(.+)#",$line,$re)){
				$GLOBALS[__FUNCTION__]=trim($re[1]);
				$_SESSION[__CLASS__][__FUNCTION__]=trim($re[1]);
				@file_put_contents("/etc/artica-postfix/LOCAL_SID",$re[1]);
				writelogs_framework("SID:{$re[1]}",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				return trim($re[1]);
			}
			
			if(preg_match("#Can.+?t fetch domain SID for#",$line,$re)){
				$date=time();
				$newsid='S-1-5-21-2596694876-1976058220-'.$date; 
				exec("$net setlocalsid $newsid",$rr);
				while (list ($a, $b) = each ($rr)){writelogs_framework($b,__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
				$GLOBALS[__FUNCTION__]=$newsid;
				$_SESSION[__CLASS__][__FUNCTION__]=trim($newsid);
				writelogs_framework("SID:$newsid",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				@file_put_contents("/etc/artica-postfix/LOCAL_SID",$newsid);
				return trim($newsid);
			}
		}
	}
	
	
	public function COUNT_LINES_OF_FILE($path){
		$wc=$this->find_program("wc");
		if($wc==null){
			$array=explode("\n",@file_get_contents("$path"));
			return count($array);
			
		}
		
		exec("$wc -l $path",$results);
		$line=@implode(" ",$results);
		if(preg_match("#^([0-9]+)\s+#",$line,$re)){
			return $re[1];
		}
		
	}
	
	private function NETWORK_ADD_ROUTE_REDHAT($nic,$array){
		$count=0;
	if(is_array($array)){
	   	 while (list ($ip, $ip_array) = each ($array) ){
	   	 	$f[]="GATEWAY$count={$ip_array["GATEWAY"]}";
	   	 	$f[]="NETMASK$count={$ip_array["NETMASK"]}";
	   	 	$f[]="ADDRESS$count=$ip";
	   	 	$suse[]="$ip {$ip_array["GATEWAY"]} {$ip_array["NETMASK"]} $nic";
	   	}
	   }		
	  if(is_dir("/etc/sysconfig/network-scripts")){
	  	@file_put_contents("/etc/sysconfig/network-scripts/route-$nic",@implode("\n",$f)."\n");
	  	@chmod("/etc/sysconfig/network-scripts/route-$nic",0777);
	  }
	  if(is_dir("/etc/sysconfig/network/scripts")){
	  	@file_put_contents("/etc/sysconfig/network/scripts/ifroute-$nic",@implode("\n",$suse)."\n");
	  	@chmod("/etc/sysconfig/network/scripts/ifroute-$nic",0777);
	  	shell_exec("ln -s /etc/sysconfig/network/scripts/ifup-route /etc/sysconfig/network/if-up.d/ifup-route >/dev/null");
	  }	  
		
	}
	
	public function NETWORK_ADD_ROUTE($nic,$array){
		if(!is_file("/etc/network/interfaces")){$this->NETWORK_ADD_ROUTE_REDHAT($nic,$array);return;}
		$start=false;
		$newf=array();
		$f=explode("\n",@file_get_contents("/etc/network/interfaces"));
		while (list ($num, $ligne) = each ($f) ){
			if(trim($ligne)==null){unset($f[$num]);continue;}
			if(preg_match("#^iface\s+$nic#",$ligne)){$start=true;$newf[]=$ligne;unset($f[$num]);continue;}
			if($start){
				if(preg_match("#up route add -net#",$ligne)){unset($f[$num]);continue;}
				if(preg_match("#down route del#",$ligne)){unset($f[$num]);continue;}
				$newf[]=$ligne;
				unset($f[$num]);
				if(preg_match("#^iface#",$ligne)){break;}
			}
			
		}
		
	   if(is_array($array)){
	   	 while (list ($ip, $ip_array) = each ($array) ){
	   		$newf[]="\tup route add -net $ip netmask {$ip_array["NETMASK"]} gw {$ip_array["GATEWAY"]}";
	   		$newf[]="\tdown route del -net $ip netmask {$ip_array["NETMASK"]} gw {$ip_array["GATEWAY"]}";
	   	}
	   }
		
	   @file_put_contents("/etc/artica-postfix/routes-interfaces-$nic",@implode("\n",$newf));
	   $new_interface=@implode("\n",$f)."\n".@implode("\n",$newf)."\n";
	   @file_put_contents("/etc/network/interfaces",$new_interface);
		
	}

	public function NETWORK_SAVE_DEBIAN_NIC($array){
		
	}
	
	public function NETWORK_DEBIAN_PARSE_ARRAY($tmpfile=null){
	$file="/etc/network/interfaces";
		if(is_file($tmpfile)){$file=$tmpfile;}
		if($GLOBALS["VERBOSE"]){echo "Parse $file..\n";}
		$f=explode("\n",@file_get_contents($file));
		$nic=array();
		while (list ($num, $ligne) = each ($f) ){
			if(trim($ligne)==null){unset($f[$num]);continue;}
			if(preg_match("#auto\s+(.+?)\s+(.+)$#",$ligne,$re)){
				$iface=trim($re[1]);
				$nic[$iface]["AUTO"]=trim($re[2]);
				continue;
			}
			
			if(preg_match("#iface\s+(.+?)inet\s+(.+)$#",$ligne,$re)){
				$iface=trim($re[1]);
				$nic[$iface]["BOOTPROTO"]=trim($re[2]);
				continue;
			}
			
			if(preg_match("#address\s+([0-9\.]+)$#",$ligne,$re)){$nic[$iface]["IPADDR"]=trim($re[1]);continue;}
			if(preg_match("#gateway\s+([0-9\.]+)$#",$ligne,$re)){$nic[$iface]["GATEWAY"]=trim($re[1]);continue;}
			if(preg_match("#\s+netmask\s+([0-9\.]+)$#",$ligne,$re)){$nic[$iface]["NETMASK"]=trim($re[1]);continue;}
			if(preg_match("#^netmask\s+([0-9\.]+)$#",$ligne,$re)){$nic[$iface]["NETMASK"]=trim($re[1]);continue;}
			if(preg_match("#broadcast\s+([0-9\.]+)$#",$ligne,$re)){$nic[$iface]["BROADCAST"]=trim($re[1]);continue;}
			if(preg_match("#bridge_ports\s+(.+)$#",$ligne,$re)){$nic[$iface]["BRIDGE_PORTS"]=trim($re[1]);continue;}
			if(preg_match("#bridge_stp\s+(.+)$#",$ligne,$re)){$nic[$iface]["BRIDGE_STP"]=trim($re[1]);continue;}
			if(preg_match("#bridge_maxwait\s+(.+)$#",$ligne,$re)){$nic[$iface]["BRIDGE_MAXWAIT"]=trim($re[1]);continue;}
			if(preg_match("#dns-search\s+(.+)$#",$ligne,$re)){$nic[$iface]["DNS_SEARCH"]=trim($re[1]);continue;}
			if(preg_match("#vlan_raw_device\s+(.+)$#",$ligne,$re)){$nic[$iface]["VLAN_ROW_DEVICE"]=trim($re[1]);continue;}
			if(preg_match("#mtu\s+(.+)$#",$ligne,$re)){$nic[$iface]["MTU"]=trim($re[1]);continue;}
			
			
			
			if(preg_match("#dns-nameservers\s+(.+)$#",$ligne,$re)){
				if(preg_match("#([0-9\.]+)\s+([0-9\.]+)#",$re[1],$ri)){
					$nic[$iface]["DNS1"]=$ri[1];
					$nic[$iface]["DNS2"]=$ri[2];
				}else{
					$nic[$iface]["DNS1"]=trim($re[1]);}
				continue;
				}

 			if(preg_match("#up\s+route\s+add.+?net\s+([0-9\.]+)\s+netmask\s+([0-9\.]+)\s+gw\s+([0-9\.]+)#",$ligne,$re)){
 				$nic[$iface]["ROUTES"][$re[1]]=array("NETMASK"=>$re[2],"GATEWAY"=>$re[3]);
 				continue;
 			}			
				
				
			}
		return $nic;
		
	}
	
	public function NETWORK_DEBIAN_SAVE($eth,$arrayToSave=array(),$norestart=false){
		$array_eth=$this->NETWORK_DEBIAN_PARSE_ARRAY();
		
		writelogs_framework("Modify $eth configuration with an array of ". count($arrayToSave) . " elements",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		if(count($arrayToSave)>0){
			while (list ($a, $b) = each ($arrayToSave) ){
				writelogs_framework("Modify $eth configuration with $a=$b",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			}
		}
		
		
		if($arrayToSave["UNSET"]){unset($array_eth[$eth]);}
		if(isset($arrayToSave["AUTO"])){$array_eth[$eth]["AUTO"]=$arrayToSave["AUTO"];}
		if(isset($arrayToSave["BRIDGE_PORTS"])){$array_eth[$eth]["BRIDGE_PORTS"]=$arrayToSave["BRIDGE_PORTS"];}
		if(isset($arrayToSave["BRIDGE_STP"])){$array_eth[$eth]["BRIDGE_STP"]=$arrayToSave["BRIDGE_STP"];}
		if(isset($arrayToSave["BRIDGE_MAXWAIT"])){$array_eth[$eth]["BRIDGE_MAXWAIT"]=$arrayToSave["BRIDGE_MAXWAIT"];}
		if(isset($arrayToSave["DNS_SEARCH"])){$array_eth[$eth]["DNS_SEARCH"]=$arrayToSave["DNS_SEARCH"];}
		if(isset($arrayToSave["VLAN_ROW_DEVICE"])){$array_eth[$eth]["VLAN_ROW_DEVICE"]=$arrayToSave["VLAN_ROW_DEVICE"];}
		if(isset($arrayToSave["MTU"])){$array_eth[$eth]["MTU"]=$arrayToSave["MTU"];}
		if(isset($arrayToSave["IPADDR"])){if($arrayToSave["IPADDR"]<>null){$array_eth[$eth]["IPADDR"]=$arrayToSave["IPADDR"];}}
		if(isset($arrayToSave["NETMASK"])){if($arrayToSave["NETMASK"]<>null){$array_eth[$eth]["NETMASK"]=$arrayToSave["NETMASK"];}}
		if(isset($arrayToSave["BOOTPROTO"])){if($arrayToSave["BOOTPROTO"]<>null){$array_eth[$eth]["BOOTPROTO"]=$arrayToSave["BOOTPROTO"];}}
		if(isset($arrayToSave["GATEWAY"])){if($arrayToSave["GATEWAY"]<>null){$array_eth[$eth]["GATEWAY"]=$arrayToSave["GATEWAY"];}}
		if(isset($arrayToSave["BROADCAST"])){if($arrayToSave["BROADCAST"]<>null){$array_eth[$eth]["BROADCAST"]=$arrayToSave["BROADCAST"];}}
		if(isset($arrayToSave["DNS1"])){if($arrayToSave["DNS1"]<>null){$array_eth[$eth]["DNS1"]=$arrayToSave["DNS1"];}}
		if(isset($arrayToSave["DNS2"])){if($arrayToSave["DNS2"]<>null){$array_eth[$eth]["DNS2"]=$arrayToSave["DNS2"];}}
		if(is_array($arrayToSave["ROUTES"])){$array_eth[$eth]["ROUTES"]=$arrayToSave["ROUTES"];}	

		if(isset($array_eth["lo"]["AUTO"])){$AUTOLO=" ".$array_eth["lo"]["AUTO"];}
		$f[]="auto lo$AUTOLO";
		$f[]="iface lo inet loopback";
		
		unset($array_eth["lo"]);
		$array=array();
		
		while (list ($eth, $array) = each ($array_eth) ){
			if(!isset($array["GATEWAY"])){$array["GATEWAY"]=null;}
			
			writelogs_framework("$eth BOOTPROTO= {$array["BOOTPROTO"]}",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			writelogs_framework("$eth address = {$array["IPADDR"]}",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			
			$f[]="";
			$f[]="auto $eth";
			$f[]="iface $eth inet {$array["BOOTPROTO"]}";
			if($array["BOOTPROTO"]=="dhcp"){continue;}
			
	        $f[]="\taddress {$array["IPADDR"]}";
	        if($array["GATEWAY"]<>null){$f[]="\tgateway {$array["GATEWAY"]}";}
	        $f[]="\tnetmask {$array["NETMASK"]}";
	        if($array["BROADCAST"]<>null){$f[]="\tbroadcast {$array["BROADCAST"]}";}
	        if(isset($array["BRIDGE_PORTS"])){$f[]="\tbridge_ports {$array["BRIDGE_PORTS"]}";}
			if(isset($array["BRIDGE_STP"])){$f[]="\tbridge_stp {$array["BRIDGE_STP"]}";}
			if(isset($array["BRIDGE_MAXWAIT"])){$f[]="\tbridge_maxwait {$array["BRIDGE_MAXWAIT"]}";}
			if(isset($array["DNS_SEARCH"])){$f[]="\tdns-search {$array["DNS_SEARCH"]}";}
			if(isset($array["VLAN_ROW_DEVICE"])){$f[]="\tvlan_raw_device {$array["VLAN_ROW_DEVICE"]}";}
			if(isset($array["MTU"])){$f[]="\tmtu {$array["MTU"]}";}
			
			
	        
	        $dns="{$array["DNS1"]} {$array["DNS2"]}";
	        if(trim($dns)<>null){$f[]="\tdns-nameservers $dns";}
	        if(is_array($array["ROUTES"])){
	         	while (list ($ip, $ip_array) = each ($array["ROUTES"]) ){
	   				$f[]="\tup route add -net $ip netmask {$ip_array["NETMASK"]} gw {$ip_array["GATEWAY"]}";
	   				$f[]="\tdown route del -net $ip netmask {$ip_array["NETMASK"]} gw {$ip_array["GATEWAY"]}";
	   			}	
	        }
	        
		}
		$f[]="";
		@file_put_contents("/etc/network/interfaces",@implode("\n",$f));
		if(is_file("/etc/network/if-up.d/sendmail")){@unlink("/etc/network/if-up.d/sendmail");}
		if(is_file("/etc/network/if-up.d/mountnfs")){@unlink("/etc/network/if-up.d/mountnfs");}
		
		
		
		if(!$norestart){$this->NETWORK_DEBIAN_RESTART();}			
	}
	
public function NETWORK_DEBIAN_RESTART(){
	$nohup=$this->find_program("nohup");
	if(is_file("/etc/init.d/networking")){
		shell_exec("/etc/init.d/networking stop");
		shell_exec("/etc/init.d/networking start");
	}
	if(is_file("/etc/init.d/network")){
		shell_exec("/etc/init.d/network stop");
		shell_exec("/etc/init.d/network start");
	}
	
}

public function NETWORK_REDHAT_RESTART(){
	
	if(is_file("/etc/init.d/NetworkManager")){
		$this->NETWORK_REDHAT_NETWORKMANAGER_DISABLE();
	}
	
	$nohup=$this->find_program("nohup");
	if(is_file("/etc/init.d/network")){shell_exec(trim("$nohup /etc/init.d/network stop  && /etc/init.d/network start >/dev/null 2>&1 &"));}
}

private function NETWORK_REDHAT_NETWORKMANAGER_DISABLE(){
	$cacheFile="/etc/artica-postfix/redhat.network.manager.disabled";
	if(is_file($cacheFile)){return;}
	$chkconfig=$this->find_program("chkconfig");
	$service=$this->find_program("service");
	if(!is_file($chkconfig)){return;}
	shell_exec("$chkconfig NetworkManager off");
	shell_exec("$chkconfig network on");
	shell_exec("$service NetworkManager stop");
	shell_exec("$service network start");
	@file_put_contents("$cacheFile",time());
	}

	
	public function LOCATE_SQUID_CONF(){
		   if(is_file('/etc/squid3/squid.conf')){ return '/etc/squid3/squid.conf';}
	   	   if(is_file('/opt/artica/etc/squid.conf')){ return '/opt/artica/etc/squid.conf';}
	   	   if(is_file('/etc/squid/squid.conf')){ return '/etc/squid/squid.conf';}
			}
	
	public function LOCATE_DHCPD_PID_PATH(){
		   if(is_file('/var/run/dhcpd.pid')){ return '/var/run/dhcpd.pid';}
	   	   if(is_file('/var/run/dhcpd/dhcpd.pid')){ return '/var/run/dhcpd/dhcpd.pid';}
	   	   if(is_file('/var/run/dhcp3-server/dhcpd.pid')){ return '/var/run/dhcp3-server/dhcpd.pid';}
			}	
	
	public function LOCATE_PURE_FTPD_PID_PATH(){
		if(is_file('/var/run/pure-ftpd/pure-ftpd.pid')){return "/var/run/pure-ftpd/pure-ftpd.pid";}
		if(is_file('/var/run/pure-ftpd.pid')){return "/var/run/pure-ftpd.pid";}
		}
	
	
	 public function SQUID_CONFIG_PATH(){
	   if(is_file("/etc/squid3/squid.conf")){return '/etc/squid3/squid.conf';}
	   if(is_file('/opt/artica/etc/squid.conf')){return '/opt/artica/etc/squid.conf';}
	   if(is_file('/etc/squid/squid.conf')){return '/etc/squid/squid.conf';}
		}	
	
	public function BetterFS(){
		if(!$this->IsExt4()){return "ext3";}
		if(is_file($this->find_program("mkfs.ext4"))){return "ext4";}
		if(is_file($this->find_program("mkfs.ext3"))){return "ext3";}
		return "ext3";
	}
	
	public function IsExt4(){
		$f=explode("\n",@file_get_contents("/proc/filesystems"));
		while (list ($num, $ligne) = each ($f) ){
			if(preg_match("#.+?\s+ext4#")){return true;}
		}
		return false;
	}

	public function hostname_g(){
		$tmp=$this->FILE_TEMP();
 		shell_exec("/bin/hostname -f >$tmp 2>&1");
 		$res=trim(@file_get_contents($tmp));
 		@unlink($tmp);
 		return $res; 
		}
		
		
	function BACKUPPC_locate_config_path(){
		if(is_file("/etc/backuppc/config.pl")){return "/etc/backuppc";}
		if(is_file("/etc/BackupPC/config.pl")){return "/etc/BackupPC";}
	
	}
	
	function PEAR_INSTALL_CHECK($packagename){
		$pear=$this->find_program("pear");
		if(strlen($pear)<5){return false;}
		exec("$pear list 2>&1",$array);
		while (list ($num, $ligne) = each ($array) ){
			if(preg_match("#^$packagename\s+#",$ligne,$re)){
				return true;
			}
		}
		return false;
	}
	

	function BACKUPPC_GET_CONFIG_INFOS($key){
		$f=explode("\n",@file_get_contents($this->BACKUPPC_locate_config_path()."/config.pl"));
		while (list ($num, $ligne) = each ($f) ){
			if(preg_match("#Conf\{$key\}.*?'(.+?)';#i",$ligne,$re)){
				$re[1]=trim($re[1]);
				$re[2]=str_replace("'","",$re[1]);
				return $re[1];
			}
		}
		
	}
	
	function BACKUPPC_GET_CGIBIN_PATH(){
		if(is_file("/usr/share/backuppc/cgi-bin/index.cgi")){return "/usr/share/backuppc/cgi-bin/index.cgi";}
		if(is_file("/usr/share/backuppc/cgi-bin/BackupPC_Admin")){return "/usr/share/backuppc/cgi-bin/BackupPC_Admin";}
		if(is_file("/usr/share/BackupPC/sbin/BackupPC_Admin")){return "/usr/share/BackupPC/sbin/BackupPC_Admin";}
		}
		
	function BACKUPPC_GET_IMG_DIR(){
		if(is_dir("/usr/share/backuppc/image")){return "/usr/share/backuppc/image";}
		if(is_dir("/usr/share/BackupPC/html")){return "/usr/share/BackupPC/html";}
		
		}
		
	function ZARAFA_VERSION(){
		$func=__CLASS__.'/'.__FUNCTION__;
		if(strlen($GLOBALS["ZARAFA_VERSION"])>5){return $GLOBALS["ZARAFA_VERSION"];}
		$zarafa_server=$this->find_program("zarafa-server");
		if(!is_file($zarafa_server)){
			if($GLOBALS["VERBOSE"]){echo "$func:: zarafa-server no such file\n";}
			return;
		}
		exec("$zarafa_server -V 2>&1",$results);
		while (list ($num, $ligne) = each ($results) ){
			if(preg_match("#Product version:\s+([0-9,\.]+)#",$ligne,$re)){
				$re[1]=trim($re[1]);
				$re[1]=str_replace(",",".",$re[1]);
				$GLOBALS["ZARAFA_VERSION"]=$re[1];
				return $re[1];
			}else{
				if($GLOBALS["VERBOSE"]){echo "$func:: $ligne no match\n";}
			}
			
		}
		
	}
	
	function ASSP_VERSION(){
		$func=__CLASS__.'/'.__FUNCTION__;
		if(strlen($GLOBALS["ASSP_VERSION"])>2){return $GLOBALS["ASSP_VERSION"];}
		$results=@explode("\n",@file_get_contents("/usr/share/assp/assp.pl"));
		if($GLOBALS["VERBOSE"]){echo count($results)." lines to parse\n";}
		while (list ($num, $ligne) = each ($results) ){
			if(preg_match("#our\s+\$version[\s=]+.+?([0-9\.]+)#",$ligne,$re)){
				$re[1]=trim($re[1]);
				if($GLOBALS["VERBOSE"]){echo "$func:: match $ligne\n";}
				$GLOBALS["ASSP_VERSION"]=$re[1];
				return $re[1];
			}
			
			if(preg_match('#our.*?\$version\s+.*?([0-9\.]+)#',$ligne,$re)){
				$re[1]=trim($re[1]);
				$GLOBALS["ASSP_VERSION"]=$re[1];
				if($GLOBALS["VERBOSE"]){echo "$func:: match $ligne\n";}
				return $re[1];
			}
			
		}
		
		
	}

	public function del_EtcHosts($ip){
		$DisableEtcHosts=trim(@file_get_contents("/etc/artica-postfix/settings/Daemons/DisableEtcHosts"));
		if($DisableEtcHosts==1){return;}
		$f=explode("\n",@file_get_contents("/etc/hosts"));
		while (list ($num, $ligne) = each ($f) ){
			if(preg_match("#([0-9\.:a-z]+)\s+(.+)#",$ligne,$re)){
				if($re[1]==$ip){continue;}
				$a[]=$ligne;
			}
		}
		@file_put_contents("/etc/hosts",@implode("\n",$a));
		
	}
		
		
	public function add_EtcHosts($hostname,$ip){
		$DisableEtcHosts=trim(@file_get_contents("/etc/artica-postfix/settings/Daemons/DisableEtcHosts"));
		if($DisableEtcHosts==1){
			writelogs_framework("/etc/hosts:: DisableEtcHosts is active, skip this procedure",__CLASS__.'/'.__FUNCTION__,__FILE__);
			return;
		}
		
		
		writelogs_framework("Add/edit $ip -> $hostname in /etc/hosts",__CLASS__.'/'.__FUNCTION__,__FILE__);
		if(trim($ip)==trim($hostname)){
			writelogs_framework("$ip == $hostname ABORT",__CLASS__.'/'.__FUNCTION__,__FILE__);
			return;
		}
		if(preg_match("#([0-9]+)\.([0-9]+).([0-9]+)\.([0-9]+)#",$hostname)){
			writelogs_framework(" $hostname is an IP ABORT",__CLASS__.'/'.__FUNCTION__,__FILE__);	
			return;
		}
		
		$aliases=array();
		$f=explode("\n",@file_get_contents("/etc/hosts"));
		while (list ($num, $ligne) = each ($f) ){
			if(preg_match("#([0-9\.:a-z]+)\s+(.+?)\s+(.+)#",$ligne,$re)){
				if(strlen(trim($re[2]))>1){
					if(preg_match("#([0-9]+)\.([0-9]+).([0-9]+)\.([0-9]+)#",$re[2])){continue;}
					$array[$re[2]]=$re[1];
					$aliases[$re[2]]=$re[3];
				}
				continue;
			}
			
			if(preg_match("#([0-9\.:a-z]+)\s+(.+)#",$ligne,$re)){
				if(strlen(trim($re[2]))>1){
					if(preg_match("#([0-9]+)\.([0-9]+).([0-9]+)\.([0-9]+)#",$re[2])){continue;}
					$array[$re[2]]=$re[1];
				}
				continue;
			}			
			
		}
		
		
		$array[$hostname]=$ip;
		$md5s=array();
		while (list ($hostname,$ip) = each ($array) ){
				$aliases=null;
				if($md5s[md5("$ip$hostname")]){continue;}
				if($aliases[$hostname]<>null){$aliases="\t{$aliases[$hostname]}";}
				$conf[]="$ip\t$hostname$aliases";
				$md5s[md5("$ip$hostname")]=true;
		}
		
		@file_put_contents("/etc/hosts",@implode("\n",$conf));
		
	}
		
		
		
	public function DISK_LIST(){
		$tmp=$this->FILE_TEMP();
		$results=array();
		exec($this->find_program("df") ." -P -h -B G",$returns);
		$pattern="#(.+)\s+([0-9]+)([A-Z])\s+([0-9]+)([A-Z])\s+([0-9]+)([A-Z])\s+([0-9]+)\%\s+(.+?)$#";
		while (list ($num, $ligne) = each ($returns) ){
			if(!preg_match($pattern,$ligne,$re)){continue;}
			$results[trim($re[1])]=array("SIZE"=>$re[2],"UNIT"=>$re[3],"USED"=>$re[4],"DISP"=>$re[6],"POURC"=>trim($re[8]));
			
		}
		
		return $results;
	}
	
public function file_size($path){

	exec($this->find_program("stat")." $path ",$results);
	while (list ($num, $line) = each ($results)){
		if(preg_match("#Size:\s+([0-9]+)\s+Blocks#",$line,$re)){
			return $re[1];break;
		}
	}
	return 0;
}

public function file_size_human($path){

	exec($this->find_program("du")." -h $path ",$results);
	while (list ($num, $line) = each ($results)){
		if(preg_match("#^([0-9A-Z]+)\s+#",$line,$re)){
			return $re[1];break;
		}
	}
	return 0;
}
	
public function alt_stat($file) {
	 $ss=@stat($file);
	 if(!$ss) return false; //Couldnt stat file
	 
	 $ts=array(
	  0140000=>'ssocket',
	  0120000=>'llink',
	  0100000=>'-file',
	  0060000=>'bblock',
	  0040000=>'ddir',
	  0020000=>'cchar',
	  0010000=>'pfifo'
	 );
	 
	 $p=$ss['mode'];
	 $t=decoct($ss['mode'] & 0170000); // File Encoding Bit
	 
	 $str =(array_key_exists(octdec($t),$ts))?$ts[octdec($t)]{0}:'u';
	 $str.=(($p&0x0100)?'r':'-').(($p&0x0080)?'w':'-');
	 $str.=(($p&0x0040)?(($p&0x0800)?'s':'x'):(($p&0x0800)?'S':'-'));
	 $str.=(($p&0x0020)?'r':'-').(($p&0x0010)?'w':'-');
	 $str.=(($p&0x0008)?(($p&0x0400)?'s':'x'):(($p&0x0400)?'S':'-'));
	 $str.=(($p&0x0004)?'r':'-').(($p&0x0002)?'w':'-');
	 $str.=(($p&0x0001)?(($p&0x0200)?'t':'x'):(($p&0x0200)?'T':'-'));
 
		 $s=array(
		 'perms'=>array(
		  'umask'=>sprintf("%04o",@umask()),
		  'human'=>$str,
		  'octal1'=>sprintf("%o", ($ss['mode'] & 000777)),
		  'octal2'=>sprintf("0%o", 0777 & $p),
		  'decimal'=>sprintf("%04o", $p),
		  'fileperms'=>@fileperms($file),
		  'mode1'=>$p,
		  'mode2'=>$ss['mode']),
		 
		 'owner'=>array(
		  'fileowner'=>$ss['uid'],
		  'filegroup'=>$ss['gid'],
		  'owner'=>
		  (function_exists('posix_getpwuid'))?
		  @posix_getpwuid($ss['uid']):'',
		  'group'=>
		  (function_exists('posix_getgrgid'))?
		  @posix_getgrgid($ss['gid']):''
		  ),
		 
		 'file'=>array(
		  'filename'=>$file,
		  'realpath'=>(@realpath($file) != $file) ? @realpath($file) : '',
		  'dirname'=>@dirname($file),
		  'basename'=>@basename($file)
		  ),
		
		 'filetype'=>array(
		  'type'=>substr($ts[octdec($t)],1),
		  'type_octal'=>sprintf("%07o", octdec($t)),
		  'is_file'=>@is_file($file),
		  'is_dir'=>@is_dir($file),
		  'is_link'=>@is_link($file),
		  'is_readable'=> @is_readable($file),
		  'is_writable'=> @is_writable($file)
		  ),
		 
		 'device'=>array(
		  'device'=>$ss['dev'], //Device
		  'device_number'=>$ss['rdev'], //Device number, if device.
		  'inode'=>$ss['ino'], //File serial number
		  'link_count'=>$ss['nlink'], //link count
		  'link_to'=>($s['type']=='link') ? @readlink($file) : ''
		  ),
		 
		 'size'=>array(
		  'size'=>$ss['size'], //Size of file, in bytes.
		  'blocks'=>$ss['blocks'], //Number 512-byte blocks allocated
		  'block_size'=> $ss['blksize'] //Optimal block size for I/O.
		  ),
		 
		 'time'=>array(
		  'mtime'=>$ss['mtime'], //Time of last modification
		  'atime'=>$ss['atime'], //Time of last access.
		  'ctime'=>$ss['ctime'], //Time of last status change
		  'accessed'=>@date('Y M D H:i:s',$ss['atime']),
		  'modified'=>@date('Y M D H:i:s',$ss['mtime']),
		  'created'=>@date('Y M D H:i:s',$ss['ctime'])
		  ),
		 );

 return $s;
}	
	
	private function MapperDetect($dev){
	$mapper=@readlink($dev);
	if($mapper==null){return $dev;}
	$mapper=str_replace("../mapper","/dev/mapper",$mapper);
	return $mapper;
	}
	
	private function folder_to_regex($path){
		$path=str_replace("/","\/",$path);
		$path=str_replace(".","\.",$path);
		$path=str_replace("$","\$",$path);
		$path=str_replace("[","\[",$path);		
		$path=str_replace("]","\]",$path);
		$path=str_replace("(","\(",$path);
		$path=str_replace(")","\)",$path);
		$path=str_replace("?","\?",$path);
		$path=str_replace("!","\!",$path);
		$path=str_replace("{","\{",$path);
		$path=str_replace("}","\}",$path);
		return $path;
	}
	
	function LESSFS_ARRAY(){
		$datas=explode("\n",@file_get_contents("/proc/mounts"));
		$array=array();
		while (list ($num, $ligne) = each ($datas) ){
		 if(preg_match("#^lessfs\s+(.+?)\s+fuse\.lessfs\s+#",$ligne,$re)){$array[trim($re[1])]=true;}
		}
		return $array;
		
	}
	
	public function DISK_MOUNTED($dev){
		$dev=$this->MapperDetect($dev);
		$datas=explode("\n",@file_get_contents("/proc/mounts"));
		$dev=$this->folder_to_regex($dev);
		
		while (list ($num, $ligne) = each ($datas) ){
			if(preg_match("#$dev#",$ligne)){
				writelogs_framework("\"$dev\"=$ligne",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				return true;
			}else{
				
			}
		}
	return false;}
	
	public function MOUNTED_PATH($dev){
		$dev=$this->MapperDetect($dev);
		$dev=$this->folder_to_regex($dev);
		$datas=explode("\n",@file_get_contents("/proc/mounts"));
		while (list ($num, $ligne) = each ($datas) ){
			if(preg_match("#^$dev\s+(.+?)\s+(ext|rw)#",$ligne,$re)){
				return trim($re[1]);	
			}
		}
		
	}
	
	public function LSOF_PIDS($directory){
		$lsof=$this->find_program("lsof");
		exec("$lsof $directory 2>&1",$results);
		while (list ($num, $ligne) = each ($results) ){
			if(preg_match("#^.+?\s+([0-9]+)#",$ligne,$re)){
				$pids[]=$re[1];
			}
		}
		return $pids;
	}
	
	
	public function MOUNTED_TMPFS_MEM($directory){
		$datas=explode("\n",@file_get_contents("/proc/mounts"));
		while (list ($num, $ligne) = each ($datas) ){
			if(preg_match("#^tmpfs\s+(.+?)\s+tmpfs\s+.+?size=([0-9]+)k\s+#",$ligne,$re)){
				$tmpfs[trim($re[1])]=round($re[2]/1024);
			}
		}
		if($tmpfs[trim($directory)]==null){$tmpfs[trim($directory)]=0;}
		return $tmpfs[trim($directory)];
		
	}
	
	public function PROCCESS_TIME_MIN($pid){
		$sec=null;
		if($pid<5){return 0;}
		if($pid==null){return 0;}
		exec("/usr/share/artica-postfix/bin/procstat $pid 2>&1",$results);
		while (list ($num, $ligne) = each ($results) ){
			if(preg_match("#start_time:.+?\(([0-9]+)#",$ligne,$re)){
				$sec=$re[1];
			}
		}
		
		if($sec==null){$sec=0;}
		if($sec>0){$sec=$sec/60;return round($sec);}
		return 0;
	}
	
	public function PROCESS_MEMORY($pid,$noMo=false){
		//$PPID=$this->PROCESS_PPID();
		//if($PPID>1)
		
		if(!is_file("/proc/$pid/status")){return 0;}
		$f=@explode("\n",@file_get_contents("/proc/$pid/status"));
		$pattern="#^VmRSS:\s+([0-9]+)#";
		while (list ($num, $ligne) = each ($f) ){
			if(preg_match($pattern,$ligne,$re)){
				if($noMo){return $re[1];}
				return round($re[1]/1024,2);
			}
		}
		
	}
	
	public function PROCESS_CACHE_MEMORY($pid,$noMo=false){
		//$PPID=$this->PROCESS_PPID();
		//if($PPID>1)
		
		if(!is_file("/proc/$pid/status")){return 0;}
		$f=@explode("\n",@file_get_contents("/proc/$pid/status"));
		$pattern="#^VmSize:\s+([0-9]+)#";
		while (list ($num, $ligne) = each ($f) ){
			if(preg_match($pattern,$ligne,$re)){
				if($noMo){return $re[1];}
				return round($re[1]/1024,2);
			}
		}
		
	}	
	
	private function PROCESS_PPID($pid){
	if(!is_file("/proc/$pid/status")){
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__.":: unable to stat /proc/$pid/status\n";}
		return 0;}
		$f=@explode("\n",@file_get_contents("/proc/$pid/status"));
		$pattern="#^PPid:\s+([0-9]+)#";
		while (list ($num, $ligne) = each ($f) ){
			if(preg_match($pattern,$ligne,$re)){
				if($GLOBALS["VERBOSE"]){echo __FUNCTION__.":: Found {$re[1]}\n";}
				if(trim($re[1])<50){return $pid;}
				return trim($re[1]);
			}
		}
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__.":: not Found return $pid\n";}
		return $pid;
		
	}
	
	
	public function PROCESS_STATUS($pid){
		$datas=explode("\n",@file_get_contents("/proc/$pid/status"));
		if(!is_array($datas)){return false;}
		while (list ($num, $ligne) = each ($datas) ){
			if(preg_match("#State:\s+([A-Z])\s+#",$ligne,$re)){
				$STATUS=$re[1];
			}
			if(preg_match("#PPid:\s+([0-9]+)#",$ligne,$re)){
				$PPID=$re[1];
			}			
		}
		if($STATUS==null){return false;}
		return array($STATUS,$PPID);
	}
	
	
	public function SPAMASSASSIN_LOCAL_CF(){
		if(is_file("/etc/spamassassin/local.cf")){return('/etc/spamassassin/local.cf');}
		if(is_file("/etc/mail/spamassassin/local.cf")){return('/etc/mail/spamassassin/local.cf');}
		if(is_file("/opt/artica/etc/spamassassin/local.cf")){return('/opt/artica/etc/spamassassin/local.cf');}
	    @mkdir('/etc/spamassassin');
		shell_exec('/bin/touch /etc/spamassassin/local.cf');
		return '/etc/spamassassin/local.cf';
		}
		
		
	public function LOCATE_maillogconvert(){
		$f[]="/usr/local/awstats/tools/maillogconvert.pl";
		$f[]="/usr/bin/maillogconvert.pl";
		$f[]="/usr/share/awstats/maillogconvert.pl";
		$f[]="/usr/share/doc/awstats/examples/maillogconvert.pl";
		$f[]="/var/www/cgi-bin/awstats/maillogconvert.pl";
		while (list ($num, $filename) = each ($f) ){if(is_file($filename)){return $filename;}}
	}
		
	public function get_pid_from_file($file){
		$datas=trim(@file_get_contents($file));
		if(preg_match("#([0-9]+)#",trim($datas),$re)){return $re[1];}
	}
	
	
	public function HostToIp($hostname){
		if(trim($hostname)==null){return;}
		$hostname=str_replace('$','',$hostname);
		$ip=gethostbyname($hostname);
		writelogs_framework("$hostname=$ip",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);
		
		if($ip<>$hostname){
			if(preg_match("#([0-9]+)\.([0-9]+).([0-9]+)\.([0-9]+)#",$ip)){return $ip;}
		}
		$nmblookup=$this->find_program("nmblookup");
		writelogs_framework("nmblookup=$nmblookup",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);

		if($nmblookup==null){return;}
		if(preg_match("#([0-9]+)\.([0-9]+).([0-9]+)\.([0-9]+)#",$hostname)){return;}
		$cmd="$nmblookup $hostname";
		exec($cmd,$results);
	
		while (list ($num, $ligne) = each ($results) ){
			writelogs_framework("$ligne",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);
			if(preg_match("#^([0-9\.]+)\s+#",$ligne,$re)){return $re[1];}
		}		
		
	}
	
	
	
	public function IpToMac($ip){
		$arp=$this->find_program("arp");
		if($arp==null){return;}
		writelogs_framework("arp=$arp",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);
		exec("$arp $ip",$ri);
		while (list ($num, $line) = each ($ri)){
			writelogs_framework("$line",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);
			if(!preg_match("#.+?\s+.+?\s+(.+?)\s+#",$line,$re)){continue;}
			$arp_mac=strtolower(trim($re[1]));
			if(strtolower($arp_mac)==strtolower("HWaddress")){continue;}
			break;
		
		}
		return $arp_mac;
	}
	
	
	public function AddFSTab($dev,$mount){
		if($dev==null){return null;}
		if($mount==null){return null;}
		writelogs_framework("create directory $mount ",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		@mkdir($mount);
		
		$datas=explode("\n",@file_get_contents("/etc/fstab"));
		$chk=$datas;
		
		while (list ($num, $ligne) = each ($chk) ){
			if(preg_match("#(.+?)\s+(.+?)\s+#",$ligne,$re)){
				if(trim(strtolower($dev))==trim(strtolower($re[1]))){
					if(trim(strtolower($mount))==trim(strtolower($re[2]))){
						return null;
					}
				}
			}
			
		}
		
		$datas[]="$dev\t$mount\tauto\tdefaults\t0\t0\n";
		if(!file_put_contents("/etc/fstab",implode("\n",$datas))){
			writelogs_framework("Unable to save /etc/fstab",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		}
		
	}
	
	public function DelFSTab($dev){
		if($dev==null){return null;}
		 $datas=explode("\n",@file_get_contents("/etc/fstab"));
		 while (list ($num, $ligne) = each ($datas) ){
		 	if($ligne==null){continue;}
		 	if(preg_match("#(.+?)\s+(.+?)\s+#",$ligne)){
		 		if(trim(strtolower($dev))==trim(strtolower($re[1]))){
		 			shell_exec($this->find_program("umount")." -f $dev");
		 			unset($datas[$num]);
		 			@rmdir(trim($re[2]));
		 		}
		 	}
		 }
		 
	if(!file_put_contents("/etc/fstab",implode("\n",$datas))){
			writelogs_framework("Unable to save /etc/fstab",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		}	 
	
	
	}
	
	public function GetFSTabMountPoint($dev){
	if($dev==null){return null;}
	$dev=$this->MapperDetect($dev);
	$array=array();
	$dev=str_replace("/","\/",$dev);
	$datas=explode("\n",@file_get_contents("/etc/fstab"));
	while (list ($num, $ligne) = each ($datas) ){
		 	if($ligne==null){continue;}
		 	if(preg_match("#$dev\s+(.+?)\s+#",$ligne,$re)){
		 			$array[]=trim($re[1]);
		 			continue;
		 		//	writelogs_framework("#$dev\s+(.+?)\s+# success => $ligne",__FUNCTION__,__FILE__,__LINE__);
		 		}else{
		 		//	writelogs_framework("#$dev\s+(.+?)\s+# failed => $ligne",__FUNCTION__,__FILE__,__LINE__);
		 		}
		 	}
	
	return $array;
	
	}
	
	public function TOTAL_MEMORY_MB(){
		$unix=new unix();
		$free=$unix->find_program("free");
		exec("$free -m 2>&1",$results);
		while (list ($num, $ligne) = each ($results) ){
			if(preg_match("#Mem:\s+([0-9]+)\s+#",$ligne,$re)){
				return $re[1];
			}
		}
		
	}

	public function TOTAL_MEMORY_MB_USED(){
		$unix=new unix();
		$free=$unix->find_program("free");
		exec("$free -m 2>&1",$results);
		while (list ($num, $ligne) = each ($results) ){
			if(preg_match("#Mem:\s+([0-9]+)\s+([0-9]+)#",$ligne,$re)){
				return $re[2];
			}
		}
		
	}
	
	public function process_exists($pid,$filename=null){
		$pid=trim($pid);
		if(!is_numeric($pid)){return false;}
		if($this->PID_IS_CHROOTED($pid)){return false;}
		
		
		if($pid<50){return false;}
		if(is_file("/proc/$pid/exe")){
			if(is_file("/proc/$pid/status")){$this->proc_status=@file_get_contents("/proc/$pid/status");}
			if($GLOBALS["VERBOSE"]){echo "filename: $filename\n";}
			if($filename<>null){
				$filename=str_replace(".", "\.", $filename);
				$cmdline=@file_get_contents("/proc/$pid/cmdline");
				if($GLOBALS["VERBOSE"]){echo "cmdline: $cmdline\n";}
				if(preg_match("#$filename#",$cmdline)){return true;}else{return false;}
			}
			return true;
		}else{
			if($GLOBALS["VERBOSE"]){echo "/proc/$pid/exe no such file\n";}
		}
		if(is_file("/proc/$pid/cmdline")){
			if($GLOBALS["VERBOSE"]){echo "filename: $filename\n";}	
			$cmdline=@file_get_contents("/proc/$pid/cmdline");
			if($GLOBALS["VERBOSE"]){echo "cmdline: $cmdline\n";}
			if(preg_match("#$filename#",$cmdline)){return true;}else{return false;}
			if(is_file("/proc/$pid/status")){$this->proc_status=@file_get_contents("/proc/$pid/status");}
			return true;
		}
		return false;
	}
	
	public function file_time_min($path){
		if(!is_dir($path)){
			if(!is_file($path)){return 100000;}
			}
	 		$last_modified = filemtime($path);
	 		$data1 = $last_modified;
			$data2 = time();
			$difference = ($data2 - $data1); 	 
			return round($difference/60);	 
		}

	PUBLIC function LOCATE_SENDMAIL_PATH(){
		$path=$this->find_program("sendmail");
		if(is_file($path)){return $path;}
		if(is_file("/usr/lib/sendmail")){return "/usr/lib/sendmail";}	
		
	}
	
	public function LOCATE_APACHE_BIN_PATH(){
		if($GLOBALS[__FUNCTION__]<>null){return $GLOBALS[__FUNCTION__];}
		$f=$this->find_program("apache2");
		if(is_file($f)){$GLOBALS[__FUNCTION__]=$f;return $f;}
		$f=$this->find_program("apache");
		if(is_file($f)){$GLOBALS[__FUNCTION__]=$f;return $f;}
		
		$f=$this->find_program("httpd2");
		if(is_file($f)){$GLOBALS[__FUNCTION__]=$f;return $f;}				
		
		$f=$this->find_program("httpd");
		if(is_file($f)){$GLOBALS[__FUNCTION__]=$f;return $f;}	
		
		if(is_file('/usr/local/obm-apache/bin/obm-apache')){return '/usr/local/obm-apache/bin/obm-apache';}
		if(is_file('/usr/local/apache-groupware/bin/apache-groupware')){return '/usr/local/apache-groupware/bin/apache-groupware';} 		
	}
	
	public function LOCATE_SQUID_BIN(){
		$squid=$this->find_program("squid3");
		if(!is_file($squid)){$squid=$this->find_program("squid");}
		return $squid;
		
	}
	
	public function LOCATE_APACHE_CONF_PATH(){
		$bin=$this->LOCATE_APACHE_BIN_PATH();
		if(strlen($bin)<3){
			if($GLOBALS["VERBOSE"]){echo "\n\nWARNING !!! LOCATE_APACHE_BIN_PATH return a non value !!\n";}
			return;
		}
		
		
		exec("$bin -V 2>&1",$results);
		while (list ($num, $ligne) = each ($results) ){
			if(preg_match('#HTTPD_ROOT="(.+?)"#',$ligne,$re)){$r["HTTPD_ROOT"]=$re[1];}
			if(preg_match('#SERVER_CONFIG_FILE="(.+?)"#',$ligne,$re)){$r["SERVER_CONFIG_FILE"]=$re[1];}
		}
		
		if(!isset($r["HTTPD_ROOT"])){
			if(is_file($r["SERVER_CONFIG_FILE"])){
				$GLOBALS[__FUNCTION__]=$r["SERVER_CONFIG_FILE"];
				return $GLOBALS[__FUNCTION__];
			}
			
		}
		
		if(file_exists("{$r["HTTPD_ROOT"]}/{$r["SERVER_CONFIG_FILE"]}")){
			if(isset($r["HTTPD_ROOT"])){$GLOBALS[__FUNCTION__]="{$r["HTTPD_ROOT"]}/{$r["SERVER_CONFIG_FILE"]}";}
			if(isset($GLOBALS[__FUNCTION__])){$GLOBALS[__FUNCTION__]=str_replace("//","/",$GLOBALS[__FUNCTION__]);}
		}
		if(isset($GLOBALS[__FUNCTION__])){return $GLOBALS[__FUNCTION__];}
		
		
	}
	
	public function LOCATE_APACHE_PID_PATH(){
		if($GLOBALS[__FUNCTION__]<>null){return $GLOBALS[__FUNCTION__];}
		$bin_path=$this->LOCATE_APACHE_BIN_PATH();
		exec("$bin_path -V 2>&1",$results);
		while (list ($num, $ligne) = each ($results) ){
			if(preg_match('#HTTPD_ROOT="(.+?)"#',$ligne,$re)){
				$httpd_root=$re[1];
			}
			
			if(preg_match('#DEFAULT_PIDLOG="(.+?)"#',$ligne,$re)){$DEFAULT_PIDLOG=$re[1];}
		}
		
		if(!is_file($DEFAULT_PIDLOG)){
			if(is_file("$httpd_root/$DEFAULT_PIDLOG")){$DEFAULT_PIDLOG="$httpd_root/$DEFAULT_PIDLOG";}
		}
		$GLOBALS[__FUNCTION__]=$DEFAULT_PIDLOG;
		return $GLOBALS[__FUNCTION__];
		
	}
	
	public function LOCATE_APACHE_VERSION(){
		if($GLOBALS[__FUNCTION__]<>null){return $GLOBALS[__FUNCTION__];}
		$bin_path=$this->LOCATE_APACHE_BIN_PATH();
		if($GLOBALS["VERBOSE"]){echo "LOCATE_APACHE_VERSION : \"$bin_path\"\n";}
		exec("$bin_path -v 2>&1",$results);
		while (list ($num, $ligne) = each ($results) ){
			if(preg_match('#^Server version:.+?([0-9\.]+)#',$ligne,$re)){
				$GLOBALS[__FUNCTION__]=$re[1];
				return $GLOBALS[__FUNCTION__];
			}
			if($GLOBALS["VERBOSE"]){echo "NO MATCH:: \"^Server version:.+?([0-9\.]+)\" -> \"$ligne\"\n";}
		}
		
	}	
	

	public function IMAPD_GET($key){
		if($GLOBALS["IMAPD_GET"]==null){
			$GLOBALS["IMAPD_GET"]=@file_get_contents("/etc/imapd.conf");
		}
		$datas=explode("\n",$GLOBALS["IMAPD_GET"]);
		while (list ($num, $ligne) = each ($datas) ){
			if(preg_match("#^$key:(.+)#",$ligne,$re)){
				return trim($re[1]);
			}
		}
	}
	
public function ServicesCMDArray(){
		$f[]="ldap";
		$f[]="saslauthd";
		$f[]="imap";
		$f[]="kav6";
		$f[]="squid";
		$f[]="dansgardian";
		$f[]="boa";
		$f[]="ftp";
		$f[]="mysql";
		$f[]="apache";
		$f[]="spamd";
		$f[]="clamd";
		$f[]="freshclam";
		$f[]="mgreylist";
		$f[]="daemon";
		$f[]="clammilter";
		$f[]="postfix";
		$f[]="dnsmasq";
		$f[]="stunnel";
		$f[]="postfix";
		$f[]="mailgraph";
		$f[]="mimedefang";
		$f[]="roundcube";
		$f[]="kav4samba";
		$f[]="bind9";
		$f[]="obm";
		$f[]="yorel";
		$f[]="p3scan";
		$f[]="syslogng";
		$f[]="mailarchive";
		$f[]="bogom";
		$f[]="collectd";
		$f[]="mysql";
		$f[]="fetchmail";
		$f[]="mailspy";
		$f[]="amavis";
		$f[]="retranslator";
		$f[]="spfmilter";
		$f[]="dotclear";
		$f[]="jcheckmail";
		$f[]="mailman";
		$f[]="kas3";
		$f[]="dhcp";
		$f[]="cicap";
		$f[]="openvpn";
		$f[]="postfix-logger";
		$f[]="dansguardian-tail";
		$f[]="apache-groupware";
		$f[]="cups";
		$f[]="dstat";
		$f[]="dstat-top-mem";
		$f[]="dstat-top-cpu";
		$f[]="rsync";
		$f[]="policydw";
		$f[]="autofs";
		$f[]="mysql-cluster";
		$f[]="assp";
		$f[]="pdns";
		$f[]="gluster";
		$f[]="gluster-cli";
		$f[]="sysloger";
		$f[]="zabbix";
		$f[]="smartd";
		return $f;	
}


public function monit_array(){
	exec("/usr/share/artica-postfix/bin/artica-install --monit-status",$array);
	if(!is_array($array)){return array();}
	while (list ($num, $ligne) = each ($array) ){
		if(preg_match("#Process\s+'(.+)'#",$ligne,$re)){
			$product=$re[1];
			$ARR[$product]["monit"]=true;
			continue;
		}
		
	if(preg_match("#status\s+(.+)#",$ligne,$re)){
			$ARR[$product]["status"]=$re[1];
			continue;
		}

		
		if(preg_match("#monitoring status\s+(.+)#",$ligne,$re)){
			$ARR[$product]["monitoring status"]=$re[1];
			continue;
		}

		if(preg_match("#uptime\s+(.+)#",$ligne,$re)){
			$ARR[$product]["uptime"]=$re[1];
			continue;
		}		
		if(preg_match("#children\s+(.+)#",$ligne,$re)){
			$ARR[$product]["children"]=$re[1];
			continue;
		}
	
		
		if(preg_match("#memory kilobytes total\s+(.+)#",$ligne,$re)){
			$ARR[$product]["memory kilobytes total"]=$re[1];
			continue;
		}
		
		if(preg_match("#^pid\s+([0-9]+)$#",trim($ligne),$re)){
			$ARR[$product]["pid"]=$re[1];
			continue;
		}		

		if(preg_match("#memory percent total\s+(.+)#",$ligne,$re)){
			$ARR[$product]["memory percent total"]=$re[1];
			continue;
		}
			if(preg_match("#cpu percent total\s+(.+)#",$ligne,$re)){
			$ARR[$product]["cpu percent total"]=$re[1];
			continue;
		}			
	}
	
	return $ARR;
	
}
	
	
public function IsProtectedDirectory($folder){
	if($folder==null){return true;}
	if($folder=="/"){return true;}
	$l["/cdrom"]=true;
	$l["/sys"]=true;
	$l["/initrd"]=true;
	$l["/dev"]=true;
	$l["/etc"]=true;
	$l["/boot"]=true;
	$l["/var/lib"]=true;
	$l["/sbin"]=true;
	$l["/lib"]=true;
	$l["/bin"]=true;
	$l["/usr/libexec"]=true;
	$l["/usr/sbin"]=true;
	$l["/usr/bin"]=true;	
	$l["/usr/include"]=true;	
	$l["/usr/local"]=true;	
	$l["/usr/src"]=true;		
	$l["/usr/usr"]=true;
	$l["/usr/X11R6"]=true;
	$l["/usr/lib"]=true;
	$l["/usr/lib64"]=true;
	$l["/usr/src"]=true;	
	$l["/srv"]=true;
	$l["/var/log"]=true;
	$l["/var/db"]=true;
	$l["/var/lib"]=true;
	$l["/var/lock"]=true;
	$l["/var/mail"]=true;
	$l["/var/milter-greylist"]=true;
	$l["/var/spool"]=true;
	$l["/var/opt"]=true;
	$l["/usr/share"]=true;
	$l["/opt"]=true;
	$l["/var/cache"]=true;
	$l["/var/local"]=true;
	$l["/var/run"]=true;
	$l["/var/tmp"]=true;
	$l["/var/webmin"]=true;
	$l["/lost+found"]=true;
	
	while (list ($num, $ligne) = each ($l) ){
		$p=str_replace("/","\/",$num);
		$p=str_replace(".","\.",$p);
		if(preg_match("#$p#",$folder)){return true;}
	}
}

	
	public function LOCATE_HEAD(){
		return $this->find_program("head");
		$this->events("Unable to stat head !");
		
	}

	public function LOCATE_DB_RECOVER(){
		$file=$this->find_program("db4.9_recover");
		if(strlen($file)>3){return $file;}		
		$file=$this->find_program("db4.8_recover");
		if(strlen($file)>3){return $file;}
		$file=$this->find_program("db4.7_recover");
		if(strlen($file)>3){return $file;}		
		$file=$this->find_program("db4.6_recover");
		if(strlen($file)>3){return $file;}	
		$file=$this->find_program("db4.5_recover");
		if(strlen($file)>3){return $file;}						
		$file=$this->find_program("db_recover");
		if(strlen($file)>3){return $file;}

		
	}

	public function LOCATE_DB_STAT(){
		$file=$this->find_program("db4.9_stat");
		if(strlen($file)>3){return $file;}				
		$file=$this->find_program("db4.8_stat");
		if(strlen($file)>3){return $file;}
		$file=$this->find_program("db4.7_stat");
		if(strlen($file)>3){return $file;}		
		$file=$this->find_program("db4.6_stat");
		if(strlen($file)>3){return $file;}
		$file=$this->find_program("db4.5_stat");
		if(strlen($file)>3){return $file;}						
		$file=$this->find_program("db_stat");
		if(strlen($file)>3){return $file;}

		
	}		

	
	
	public function LOCATE_SYSLOG_PATH(){
		if(is_file("/etc/artica-postfix/settings/Daemons/syslog_path")){
			$datas=trim(@file_get_contents("/etc/artica-postfix/settings/Daemons/syslog_path"));
			if(!is_file($datas)){$datas="/var/log/syslog";}
			
		}
		return $datas;
		
	}
	
	public function LOCATE_DU(){
		return $this->find_program("du");
		$this->events("Unable to stat du !");
		
	}

	public function FILE_TEMP(){
		if(function_exists("sys_get_temp_dir")){
			return tempnam(sys_get_temp_dir(), 'artica-php');
		}
		return "/tmp/artica-php-".md5(date("Y-m-d H:i:s"));
		
	}
	
	
	public function KERNEL_VERSION_BIN(){
		$cmd=$this->find_program("uname")." -r";
		exec($cmd,$datas);
	if(preg_match("#^([0-9]+)\.([0-9]+)\.([0-9]+)#",$datas[0],$re)){
			if(strlen($re[2])==1){$re[2]='0'.$re[2];}
			if(strlen($re[3])==1){$re[3]='0'.$re[3];}
			$kernel=$re[1].$re[2].$re[3];
			
		}
		
		return $kernel;
	}
	
	
	public function  miltergreylist_addacl($complete_string){
	   $ConfigurationFile=$this->MILTER_GREYLIST_CONF_PATH();
	   if(!is_file($ConfigurationFile)){return null;}
		$datas=@file_get_contents($ConfigurationFile);
		$tbl=explode("\n",$datas);
		while (list ($num, $ligne) = each ($tbl) ){
	   		if(strtolower(trim($ligne))==strtolower(trim($complete_string))){
	   			return ;
	   		}
		}
	
	  $tbl[]=$complete_string;
	  @file_put_contents($ConfigurationFile,implode("\n",$tbl)."\n");
	  
	}
	
	public function miltergreylist_cleanconf(){
		$ConfigurationFile=$this->MILTER_GREYLIST_CONF_PATH();
		$datas=@file_get_contents($ConfigurationFile);
		$tbl=explode("\n",$datas);
		while (list ($num, $ligne) = each ($tbl) ){
			if(trim($ligne)==null){unset($tbl[$num]);}
			if(substr($ligne,0,1)=="#"){unset($tbl[$num]);}
		}
		
		$tbl[]="\n";
		file_put_contents($ConfigurationFile,implode("\n",$tbl));
		
	 	$this->miltergreylist_check();
	}
	
	private function miltergreylist_check(){
		$ConfigurationFile=$this->MILTER_GREYLIST_CONF_PATH();
		$MILTER_GREYLIST_BIN_PATH=$this->MILTER_GREYLIST_BIN_PATH();
		$tmpf=$this->FILE_TEMP();
		for($i=0;$i<50;$i++){
			exec("$MILTER_GREYLIST_BIN_PATH -f $ConfigurationFile -c >$tmpf 2>&1");
			$dd=@file_get_contents($tmpf);
			
			@unlink($tmpf);
			if(preg_match("#config error at line\s+([0-9]+)#is",$dd,$ri)){
				if(!$this->miltergreylist_removeline($ri[1])){break;}
				continue;
			}
			
			else{break;}
		}
	}
	
	private function miltergreylist_removeline($line_number){
		 $ConfigurationFile=$this->MILTER_GREYLIST_CONF_PATH();
		 if(!is_file($ConfigurationFile)){return null;}
		 $datas=@file_get_contents($ConfigurationFile);
		 $tbl=explode("\n",$datas);
		 if(count($tbl)==$line_number){
		 	$tbl[]="";
		 	file_put_contents($ConfigurationFile,implode("\n",$tbl));
		 	return false;
		 }
		 echo "Starting......: milter-greylist Config...: error, removing  \"{$tbl[$line_number]}\" line $line_number";
		 unset($tbl[$line_number]);
		 file_put_contents($ConfigurationFile,implode("\n",$tbl));
		 return true;
	}
	
	
	
	public function miltergreylist_buildacl($pattern){
	 	if(preg_match("#(.+?)@(.+)#",$pattern,$re)){
	 		$part=trim($re[1]);
	 		$domain=trim($re[2]);
	 	}
	   	 $part=str_replace('*','',$part);
	 	
	 	 if(strlen($part)==0){ 
	           $suffix='acl whitelist domain';
	           $domain=str_replace("","@",$domain);
	           $pattern=$domain;
	 	 }else{
	 	 	$suffix="acl whitelist from";
	 	 }
	   
		if(strpos($pattern,'*')>0){
	        $pattern=str_replace('.','\.',$pattern);
	        $pattern=str_replace('*','.*',$pattern);
	       	$pattern="/$pattern/";
		}
	     
		return "$suffix $pattern";
	}
	
 
	public function MILTER_GREYLIST_CONF_PATH(){
		if(is_file('/etc/milter-greylist/greylist.conf')){return '/etc/milter-greylist/greylist.conf';}
		if(is_file('/etc/mail/greylist.conf')){return '/etc/mail/greylist.conf';}
		if(is_file('/opt/artica/etc/milter-greylist/greylist.conf')){return '/opt/artica/etc/milter-greylist/greylist.conf';}
		return '/etc/mail/greylist.conf';
	}
	
	public function MILTER_GREYLIST_BIN_PATH(){
		if(is_file('/usr/local/bin/milter-greylist')){return '/usr/local/bin/milter-greylist';}
		if(is_file('/usr/sbin/milter-greylist')){return '/usr/sbin/milter-greylist';}
		if(is_file('/usr/bin/milter-greylist')){return '/usr/bin/milter-greylist';}	
		}
	
	
	
	public function DIRSIZE_KO($directory){
		$cmd=$this->find_program("du")." -s $directory";
		echo "$cmd\n";
		
		exec($cmd,$datas);

		if(preg_match("#^([0-9]+)\s+#",$datas[0],$re)){
			return trim($re[1]);
		}
		return 0;
	}	
	
	public function LOCATE_PHP5_BIN(){
	  if(isset($GLOBALS["LOCATE_PHP5_BIN"])){
	  		if(strlen($GLOBALS["LOCATE_PHP5_BIN"])>3){return $GLOBALS["LOCATE_PHP5_BIN"];}
	  }
	  if(is_file('/usr/bin/php5')){$GLOBALS["LOCATE_PHP5_BIN"]='/usr/bin/php5';return '/usr/bin/php5';}
	  if(is_file('/usr/bin/php')){$GLOBALS["LOCATE_PHP5_BIN"]='/usr/bin/php';return '/usr/bin/php';}
	  if(is_file('/usr/local/apache-groupware/php5/bin/php')){$GLOBALS["LOCATE_PHP5_BIN"]='/usr/bin/php5';return '/usr/local/apache-groupware/php5/bin/php';}
	  $php5=$this->find_program("php");
	  if(is_file($php5)){return $php5;}
	  $php5=$this->find_program("php5");
	  if(is_file($php5)){return $php5;}	 	  
	  $this->events("Unable to stat php5 !");
	  
	}
	public function LOCATE_CUPS_ACCEPT(){
 		if(is_file('/usr/sbin/cupsaccept')){return '/usr/sbin/cupsaccept';}
    	if(is_file('/usr/sbin/accept')){return '/usr/sbin/accept';}
	}
 //############################################################################# 

public function getmodpathfromconf(){
	$httpdconf=$this->LOCATE_APACHE_CONF_PATH();
	if($httpdconf=="/etc/httpd/conf/httpd.conf"){return "/etc/httpd";}
	if($httpdconf=="/usr/local/etc/httpd/conf/httpd.conf"){return "/usr/local/etc/httpd";}
	return dirname($httpdconf);
}	


public function APACHE_DIR_SITES_ENABLED(){
	$httpdconf=$this->LOCATE_APACHE_CONF_PATH();
	$DAEMON_PATH=$this->getmodpathfromconf($httpdconf);
	return $DAEMON_PATH."/sites-enabled";
	$mods_enabled=$DAEMON_PATH."/mods-enabled";	
	
}

public function LOCATE_APACHE_CTL(){
	$apachectl=$this->find_program("apachectl");
	if(is_file($apachectl)){return $apachectl;}
	$apachectl=$this->find_program("apache2ctl");
	if(is_file($apachectl)){return $apachectl;}	
}

public function LOCATE_STUNNEL_BIN(){
	$stunnel=$this->find_program("stunnel");
	if(strlen($stunnel)>0){return $stunnel;}
	$stunnel=$this->find_program("stunnel4");
	if(strlen($stunnel)>0){return $stunnel;}	
	$stunnel=$this->find_program("stunnel3");
	if(strlen($stunnel)>0){return $stunnel;}	
	
}


	public function PATH_LDAP_DIRECTORY_DATA(){
		$file=file_get_contents("/etc/ldap/slapd.conf");
		$datas=explode("\n",$file);
		if(!is_array($datas)){
			$this->events("Unable to stat /etc/ldap/slpad.conf !");
			return null;}
		while (list ($num, $ligne) = each ($datas) ){
			if(preg_match("#^directory\s+(.+)#",$ligne,$re)){
				return trim($re[1]);
			}
			
		}
		
		$this->events("Unable to stat directory in /etc/ldap/slpad.conf ! in ".count($datas)." rows");
	}
	
	public function find_program($strProgram) {
	$key=md5($strProgram);
	if(function_exists("apc_store")){
		$value=trim(apc_fetch("LOCATE_PROGRAM_$key"));
   		if($value<>null){
   			if(basename($value)==$strProgram){return trim($value);}
		}
	}
	  if(!isset($GLOBALS["find_program"]["$strProgram"])){$GLOBALS["find_program"]["$strProgram"]=null;}
	  if(!isset($_SESSION["find_program"]["$strProgram"])){$_SESSION["find_program"]["$strProgram"]=null;}
	
	  if($_SESSION["find_program"]["$strProgram"]<>null){return $_SESSION["find_program"]["$strProgram"];}
	  if($GLOBALS["find_program"]["$strProgram"]<>null){return $GLOBALS["find_program"]["$strProgram"];}	
		
		$value=$this->internal_find_program($strProgram);
		if(function_exists("apc_store")){ 
			apc_store("LOCATE_PROGRAM_$key",$value);
	  		return $value;
	 	}	
		
	 	$_SESSION["find_program"]["$strProgram"]=$value;
	    $GLOBALS["find_program"]["$strProgram"]=$value;
    	return $value;
		
	}
	
	private function internal_find_program($strProgram){
	  global $addpaths;	
	  $arrPath = array('/bin', '/sbin', '/usr/bin', '/usr/sbin', '/usr/local/bin', 
	  '/usr/local/sbin',
	  '/usr/kerberos/bin',
	  
	  );
	  
	  if (function_exists("is_executable")) {
	    foreach($arrPath as $strPath) {
	      $strProgrammpath = $strPath . "/" . $strProgram;
	      if (is_executable($strProgrammpath)) {
	      	  return $strProgrammpath;
	      }
	    }
	  } else {
	   	return strpos($strProgram, '.exe');
	  }
	}
	
	public function POSTCONF_SET($key,$value){
	$value=str_replace('$','\$',$value);
	if($GLOBALS["MEM_POSTCONF"]<>null){$postconf=$GLOBALS["MEM_POSTCONF"];}else{$postconf=$this->find_program("postconf");$GLOBALS["MEM_POSTCONF"]=$postconf;}
		shell_exec("$postconf -e \"$key = $value\" >/dev/null 2>&1");
	}
	
	
	public function POSTCONF_GET($value){
		if($GLOBALS["MEM_POSTCONF"]<>null){$postconf=$GLOBALS["MEM_POSTCONF"];}else{$postconf=$this->find_program("postconf");$GLOBALS["MEM_POSTCONF"]=$postconf;}
		if($GLOBALS["DEBUG"]){echo "POSTCONF_GET:: $postconf -h $value\n";}
		exec("$postconf -h $value",$results);
		return trim(implode("",$results));
		
	}
	
	public function POSTCONF_MULTI_GET($instance,$value){
		if($GLOBALS["MEM_postmulti"]<>null){$postmulti=$GLOBALS["MEM_postmulti"];}else{$postmulti=$this->find_program("postmulti");$GLOBALS["MEM_postmulti"]=$postmulti;}
		
		if($GLOBALS["DEBUG"]){echo "POSTCONF_MULTI_GET:: postmulti=$postmulti\n";}
		exec("$postmulti -i postfix-$instance -x postconf -h $value",$results);
		return trim(implode("\n",$results));
		
	}

	public function POSTCONF_MULTI_SET($instance,$key,$value){
		$value=str_replace('$','\$',$value);
		if($GLOBALS["MEM_postmulti"]<>null){$postmulti=$GLOBALS["MEM_postmulti"];}else{$postmulti=$this->find_program("postmulti");$GLOBALS["MEM_postmulti"]=$postmulti;}
		$cmd="$postmulti -i postfix-$instance -x postconf -e \"$key=$value\" >/dev/null 2>&1";
		if($GLOBALS["DEBUG"]){echo "POSTCONF_MULTI_SET:: $cmd\n";}
		shell_exec("$postmulti -i postfix-$instance -x postconf -e \"$key=$value\" >/dev/null 2>&1");
	}		
	
	public function POSTFIX_MULTI_PID($instance){
		if($GLOBALS["DEBUG"]){echo "POSTFIX_MULTI_PID:: $instance\n";}
		$path=$this->POSTCONF_MULTI_GET($instance,"queue_directory")."/pid/master.pid";
		$data=trim(@file_get_contents($path));
		return trim($data);
	}	
	
	public function POSTFIX_PID(){
		$path=$this->POSTCONF_GET("queue_directory")."/pid/master.pid";
		$GLOBALS["DEBUG_LOGS"][]="Pid path : $path";
		if($GLOBALS["DEBUG"]){echo "POSTFIX_PID:: $path\n";}
		$data=trim(@file_get_contents($path));
		$data=trim($data);
		$GLOBALS["DEBUG_LOGS"][]="Pid content : $data";
		if(preg_match("#([0-9]+)#",$data,$re)){$data=$re[1];}
		if($GLOBALS["DEBUG"]){echo "POSTFIX_PID:: $data\n";}
		return $data;
	}
	
	public function POSTFIX_MASTER_BIN_PATH(){
		$path=trim($this->POSTCONF_GET("daemon_directory")."/master");
		$GLOBALS["DEBUG_LOGS"][]="Bin path : $path";
		if($GLOBALS["DEBUG"]){echo "POSTFIX_PID:: $path\n";}
		return $path;
	}	
	
	
	
	
	
	public function POSTFIX_VERSION(){return $this->POSTCONF_GET("mail_version");}
	
	public function LOCATE_POSTFIX_DAEMON_DIRECTORY(){
		if(isset($GLOBALS[__FUNCTION__])){
			if(strlen($GLOBALS[__FUNCTION__])){return $GLOBALS[__FUNCTION__];}
		}
		$locate1=$this->LOCATE_POSTFIX_DAEMON_DIRECTORY_1();
		$locate2=$this->LOCATE_POSTFIX_DAEMON_DIRECTORY_2();
		$locate3=$this->LOCATE_POSTFIX_DAEMON_DIRECTORY_3();
		if($locate2<>null){if($locate1<>$locate2){$locate1=$locate2;}}
		if($locate3<>null){if($locate1<>$locate3){$locate1=$locate3;}}
		$GLOBALS[__FUNCTION__]=$locate1;
		return $locate1;
	}
	
	
	private function LOCATE_POSTFIX_DAEMON_DIRECTORY_1(){
		if(is_file('/usr/lib/postfix/master')){return ('/usr/lib/postfix');}
		if(is_file('/usr/libexec/postfix/master')){return ('/usr/libexec/postfix');}
		if(is_file('/usr/local/libexec/postfix/master')){return ('/usr/local/libexec/postfix');}
		
	}
	
	private function LOCATE_POSTFIX_DAEMON_DIRECTORY_2(){
		if(is_file('/usr/lib/postfix/postscreen')){return ('/usr/lib/postfix');}
		if(is_file('/usr/libexec/postfix/postscreen')){return ('/usr/libexec/postfix');}
		if(is_file('/usr/local/libexec/postfix/postscreen')){return ('/usr/local/libexec/postfix');}
		
	}	
	
	private function LOCATE_POSTFIX_DAEMON_DIRECTORY_3(){
		if(is_file('/usr/lib/postfix/postmulti-script')){return ('/usr/lib/postfix');}
		if(is_file('/usr/libexec/postfix/postmulti-script')){return ('/usr/libexec/postfix');}
		if(is_file('/usr/local/libexec/postfix/postmulti-script')){return ('/usr/local/libexec/postfix');}
	}	
	
	
	
	public function IPTABLES_DELETE_REGEX_ENTRIES($regexPat){
		if(trim($regexPat)==null){return;}
		$iptables_save=$this->find_program("iptables-save");
		$iptables_restore=$this->find_program("iptables-restore");
		$cmd="$iptables_save > /etc/artica-postfix/iptables.conf";
		shell_exec($cmd);
		$data=file_get_contents("/etc/artica-postfix/iptables.conf");
		$datas=explode("\n",$data);
		if($GLOBALS["VERBOSE"]){echo __CLASS__.'/'.__FUNCTION__." iptables.conf: ". count($data)." lines L.". __LINE__."\n";}
		$pattern="#$regexPat#";	
		$count=0;
		while (list ($num, $ligne) = each ($datas) ){
			if($ligne==null){continue;}
			if(preg_match($pattern,$ligne)){
				if($GLOBALS["VERBOSE"]){echo __CLASS__.'/'.__FUNCTION__." Delete \"$ligne\" L.". __LINE__."\n";}		
				$count++;continue;
			}
			$conf=$conf . $ligne."\n";
		}

		file_put_contents("/etc/artica-postfix/iptables.new.conf",$conf);
		if($count>0){
			$cmd="$iptables_restore < /etc/artica-postfix/iptables.new.conf";
			if($GLOBALS["VERBOSE"]){echo __CLASS__.'/'.__FUNCTION__."$cmd L.". __LINE__."\n";}
			shell_exec("$cmd");
		}
		
		
	}
	
	
	public function LOCATE_SENDMAIL_PID_PATH(){
		if(is_file("/var/run/sendmail.pid")){return "/var/run/sendmail.pid";}
	}
	
	
	
	public function events($text,$logFile=null,$phplog=false){
		$common="/var/log/artica-postfix/syslog.log";
		if($logFile<>null){$size=@filesize($logFile);}
		$size=@filesize($common);
		if($size>100000){@unlink($common);}
		
		if($logFile<>null){
			$size=@filesize($logFile);
			if($size>100000){@unlink($logFile);}
		}
		
		$pid=getmypid();
		$date=date("Y-m-d H:i:s");
		
		if($logFile<>null){$f = @fopen($logFile, 'a');}
		$h = @fopen($common, 'a');
		
		$sline="[$pid] $text";
		$line="$date [$pid] $text\n";
		if($GLOBALS["VERBOSE"]){echo $line."\n";}
		if($phplog){error_log("$sline");}
		if($logFile<>null){@fwrite($f,$line);}
		@fwrite($h,$line);
		if($logFile<>null){@fclose($f);}
		@fclose($h);
	}	

	public function dirdir($path){
		foreach (glob("$path/*",GLOB_ONLYDIR) as $directory) {
			$directory=utf8_decode($directory);
			$array[$directory]=$directory;
		}
		
		return $array;
	}
	
public	function getDirectories($directory){
	$GLOBALS["RECURSVIVE_INC"]=$GLOBALS["RECURSVIVE_INC"]+1;
	
	if($GLOBALS["RECURSVIVE_INC"]>$GLOBALS["MAX_RECUSIVE_DIRS"]){
		error_log("PHP Fatal: cannot parse directory [instance {$GLOBALS["RECURSVIVE_INC"]}], maximum recursive of {$GLOBALS["MAX_RECUSIVE_DIRS"]} has been reached ");
		return;
	}
	$array_items = array();
	if ($handle = opendir($directory)) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != "..") {
				if (is_dir($directory. "/" . $file)) {
						$newarrayDirectories= $this->getDirectories($directory. "/" . $file);
						if(is_array($newarrayDirectories)){$array_items = array_merge($array_items,$newarrayDirectories);}
						$file = $directory . "/" . $file;
						$array_items[] = preg_replace("/\/\//si", "/", $file);
				} else {
					//$file = $directory . "/" . $file;
					//$array_items[] = preg_replace("/\/\//si", "/", $file);
				}
			}
		}
		closedir($handle);
	}
	return $array_items;
	

} 

function DirRecursiveFiles($dir){
    $files = Array();
    $file_tmp= glob($dir.'*',GLOB_MARK | GLOB_NOSORT);

    foreach($file_tmp as $item){
        if(substr($item,-1)!=DIRECTORY_SEPARATOR)
            $files[] = $item;
        else
            $files = array_merge($files,$this->DirRecursiveFiles($item));
    }

    return $files;
}
	
function DirFiles($path){
	$dir_handle = @opendir($path);
	if(!$dir_handle){
		return array();
	}
	$count=0;	
	while ($file = readdir($dir_handle)) {
	  if($file=='.'){continue;}
	  if($file=='..'){continue;}
	  if(!is_file("$path/$file")){continue;}
		
			$array[$file]=$file;
			continue;
		
		
	  }
	if(!is_array($array)){return array();}
	@closedir($dir_handle);
	return $array;
}

	function GLFS_ismounted($path){
		$pathString=str_replace("/","\/",$path);
		$pathString=str_replace(".","\.",$pathString);
		$f=explode("\n",@file_get_contents("/proc/mounts"));
		while (list ($index, $line) = each ($f) ){
			if(preg_match("#$pathString\s+fuse\.glusterfs#",$line)){
				return true;
			}
			
		}
		
	}



	public function dir_count_files_recursive($path) {
	    $files = 0;
	    $dir = opendir($path);
	    if (!$dir){return 0;}
	    while (($file = readdir($dir)) !== false) {
	        if ($file[0] == '.'){ continue; }
	        if (is_dir($path.$file)){        
	            $files += $this->dir_count_files_recursive($path.$file.DIRECTORY_SEPARATOR);
	        }
	        else {
	            $files++;
	        }
	    }    
	    closedir($dir);
	    return $files;
		}




	public function PIDOF($binpath,$noppid=false){
		$cmd=$this->find_program('pidof')." -s $binpath";
		if($GLOBALS["VERBOSE"]){echo __FUNCTION__.":: $cmd\n";}
		exec($this->find_program('pidof')." -s $binpath",$re);
		while (list ($num, $ligne) = each ($re) ){	
			if(preg_match("#[0-9]+#",$ligne)){
				$pid=trim($ligne);
				if($this->PID_IS_CHROOTED($pid)){continue;}
				if($noppid){
					if($GLOBALS["VERBOSE"]){echo "PIDOF -> $pid\n";}
					return $pid;}
				
				return $this->PROCESS_PPID($pid);
			}
		}
		
	}
	
	public function PID_IS_CHROOTED($pid){
		$pid=trim($pid);
		if(!is_numeric($pid)){return;}
		if(is_file("/etc/artica-postfix/AS_VPS_CLIENT")){return false;}
		if(!is_file("/proc/$pid/cgroup")){return false;}
		if(preg_match("#[0-9]+.+?:/(.+?)$#",@file_get_contents("/proc/$pid/cgroup"),$re)){
			$vps=trim($re[1]);
			if(strlen($vps)>1){return true;}
		}
		return false;
	}
	
	public function PIDOF_PATTERN($pattern){
		$pattern=str_replace("/","\/",$pattern);
		$pattern=$this->find_program('pgrep')." -l -f \"$pattern\"";
		exec($pattern,$re);
		
		if(preg_match("#pgrep -l#",$re[0])){$re[0]=$re[1];}
		if(preg_match("#^([0-9]+)\s+#",$re[0],$i)){
			if(!$this->PID_IS_CHROOTED($i[1])){return $i[1];}
		}
		
	}	
	
	public function PROCESS_TIME_TO_LIVE($pid){
		exec("/usr/share/artica-postfix/bin/procstat",$array);
		while (list ($num, $ligne) = each ($array) ){
			if(preg_match("#start_time:.+?\((.+?\.#",$ligne,$re)){
				$mins=($re[1]/60);
				return $mins;
			}
		}
	}
	
	
	public function POSTFIX_MULTI_INSTANCES_LIST(){
		if($GLOBALS["MEM_postmulti"]<>null){$postmulti=$GLOBALS["MEM_postmulti"];}else{$postmulti=$this->find_program("postmulti");$GLOBALS["MEM_postmulti"]=$postmulti;}
		if(strlen($postmulti)<3){return null;}
		exec("$postmulti -l 2>&1",$results);
		while (list ($num, $ligne) = each ($results) ){
			if(preg_match("#^(.+?)\s+([a-z\-])\s+(.+)#",$ligne,$re)){
				$re[1]=trim($re[1]);
				if(preg_match("#postfix-(.+)#",$re[1],$ri)){
					$instance[$ri[1]]=$ri[1];
				}
			}
		}
		
		return $instance;
	}
	

	
	
	public function squid_get_cache_infos(){
		$squidclient=$this->find_program("squidclient");
		$array=array();
		if($squidclient==null){
		writelogs_framework("Unable to stat squidclient !!",__FUNCTION__,__LINE__);;return;}
	
	$ini=new iniFrameWork("/etc/artica-postfix/settings/Daemons/ArticaSquidParameters");
	if($ini->_params["NETWORK"]["LDAP_AUTH"]==1){
		$ldappassword=trim(@file_get_contents("/etc/artica-postfix/ldap_settings/password"));
		$auth=" -u squidinternalauth -w $ldappassword ";
	}
	
	
	
	$cmd="$squidclient /usr/bin/squidclient -h 127.0.0.1$auth mgr:storedir";
	writelogs_framework("$cmd",__FUNCTION__,__LINE__);;
	
	
	exec($cmd,$results);
	while (list ($index, $line) = each ($results) ){
		if(preg_match("#Connection refused#",$line)){
			writelogs_framework($line,__FUNCTION__,__LINE__);;
			return;
		}
		
		if(preg_match("#Store Directory\s+\#([0-9]+).+?:\s+(.+)#",$line,$re)){
			$path=trim($re[2]);
			$array[$path]["index"]=$re[1];
			continue;
		}
		if(preg_match("#Maximum Size:\s+([0-9]+)#",$line,$re)){
			$array[$path]["MAX"]=$re[1];
		}
		
		if(preg_match("#Current Size:\s+([0-9]+)#",$line,$re)){
			$array[$path]["CURRENT"]=$re[1];
		}		

		if(preg_match("#Percent Used:\s+([0-9\.]+)#",$line,$re)){
			$array[$path]["POURC"]=$re[1];
		}

		if(preg_match("#ERR_ACCESS_DENIED#",$line)){
			writelogs_framework("ACCESS DENIED",__FUNCTION__,__LINE__);;
			break;
		}
		
		
	}		
		return $array;
		
	}
	
	
	public function MYSQL_SOCKET_PATH(){
		$cnf=$this->MYSQL_MYCNF_PATH();
		$ini=new iniFrameWork($cnf);
		$return=$ini->get("mysqld","socket");
		if(trim($return==null)){return "/var/run/mysqld/mysqld.sock";}
	}
	
	public function MYSQL_MYCNF_PATH(){
		if(is_file('/etc/mysql/my.cnf')){return('/etc/mysql/my.cnf');}
  		if(is_file('/etc/my.cnf')){return '/etc/my.cnf';}

	} 
	
	public function MYSQL_DATADIR(){
		$cnf=$this->MYSQL_MYCNF_PATH();
		$ini=new iniFrameWork($cnf);
		$return=$ini->get("mysqld","datadir");
		if(trim($return==null)){return "/var/lib/mysql";}

	} 	
	
	
	
	public function FSTAB_GETDEV($targetPointPath){
		$datas=explode("\n",@file_get_contents("/etc/fstab"));
		while (list ($num, $ligne) = each ($datas) ){
			if(strpos($ligne,$targetPointPath)>0){
				if(preg_match("#(.+?)\s+#",$ligne,$re)){
				return $re[1];
			}}
		}
		
	}
	
	
	public function FSTAB_QUOTA($dev,$enabled=0){
		$rows=explode("\n",@file_get_contents("/etc/fstab"));
		$dev_pattern=str_replace("/","\/",$dev);
		if(!is_array($rows)){writelogs_framework("no rows, abort",__FUNCTION__,__FILE__);return ;}
		while (list ($index, $line) = each ($rows) ){
		if(preg_match("#$dev_pattern\s+(.+?)\s+(.*?)\s+(.*?)\s+(.+)#",$line,$re)){
					$row_index=$index;
					$mount_path=$re[1];
					$type=$re[2];
					$option=$re[3];
					$other=$re[4];
					break;
				}
			}
		if($mount_path==null){return;}
		$options=explode(",",$option);
		while (list ($i, $token) = each ($options) ){$tokens[$token]=true;}
		writelogs_framework("OPTIONS=$option mount on $mount_path",__FUNCTION__,__FILE__);
		unset($tokens["usrquota"]);
		
		if($enabled==0){
			unset($tokens["usrquota"]);
			unset($tokens["usrjquota=aquota.user"]);
			unset($tokens["grpjquota=aquota.group"]);
			unset($tokens["jqfmt=vfsv"]);
			unset($tokens["jqfmt=vfsv0"]);		
			
		}else{
			$tokens["usrjquota=aquota.user"]=true;
			$tokens["grpjquota=aquota.group"]=true;
			$tokens["jqfmt=vfsv0"]=true;
		}
		while (list ($token,$i) = each ($tokens) ){$final[]=$token;}
		$new_options=implode(",",$final);
		writelogs_framework("$dev\t$mount_path\t$type\t$new_options\t$other",__FUNCTION__,__FILE__,__LINE__);
		reset($rows);
		$rows[$row_index]="$dev\t$mount_path\t$type\t$new_options\t$other";
		@file_put_contents("/etc/fstab",@implode("\n",$rows));
		$mount=$this->find_program("mount");
		writelogs_framework("$mount -o remount $dev",__FUNCTION__,__FILE__,__LINE__);
		exec("$mount -o remount $dev");	
		$quotacheck=$this->find_program("quotacheck");
		if(is_file($quotacheck)){shell_exec("$quotacheck -afgum &");}
		$quotaon=$this->find_program("quotaon");
		if(is_file($quotaon)){shell_exec("$quotaon -avug &");}
		
				
	}
	
	
	public function GET_QUOTA_MOUNTED(){
		$mount=$this->find_program("mount");
		exec("$mount 2>&1",$results);
		while (list ($i, $line) = each ($results) ){
			if(!preg_match("#(.+?)\s+on\s+(.+?)\s+.+?usrjquota#",$line,$re)){continue;}
			$rr[$re[2]]=true;
		}
		
		return $rr;
		
	}
	
function distanceOfTimeInWords($fromTime, $toTime = 0, $showLessThanAMinute = true) {
	    $distanceInSeconds = round(abs($toTime - $fromTime));
	    $distanceInMinutes = round($distanceInSeconds / 60);
	       
	        if ( $distanceInMinutes <= 1 ) {
	            if ( !$showLessThanAMinute ) {
	                return ($distanceInMinutes == 0) ? 'less than a minute' : '1 minute';
	            } else {
	                if ( $distanceInSeconds < 5 ) {
	                    return 'less than 5 seconds ('.$distanceInSeconds.'s)';
	                }
	                if ( $distanceInSeconds < 10 ) {
	                    return 'less than 10 seconds ('.$distanceInSeconds.'s)';
	                }
	                if ( $distanceInSeconds < 20 ) {
	                    return 'less than 20 seconds ('.$distanceInSeconds.'s) ';
	                }
	                if ( $distanceInSeconds < 40 ) {
	                    return 'about half a minute ('.$distanceInSeconds.'s)';
	                }
	                if ( $distanceInSeconds < 60 ) {
	                    return 'less than a minute';
	                }
	               
	                return '1 minute';
	            }
	        }
	        if ( $distanceInMinutes < 45 ) {
	            return $distanceInMinutes . ' minutes';
	        }
	        if ( $distanceInMinutes < 90 ) {
	            return 'about 1 hour';
	        }
	        if ( $distanceInMinutes < 1440 ) {
	            return 'about ' . round(floatval($distanceInMinutes) / 60.0) . ' hours';
	        }
	        if ( $distanceInMinutes < 2880 ) {
	            return '1 day';
	        }
	        if ( $distanceInMinutes < 43200 ) {
	            return 'about ' . round(floatval($distanceInMinutes) / 1440) . ' days';
	        }
	        if ( $distanceInMinutes < 86400 ) {
	            return 'about 1 month';
	        }
	        if ( $distanceInMinutes < 525600 ) {
	            return round(floatval($distanceInMinutes) / 43200) . ' months';
	        }
	        if ( $distanceInMinutes < 1051199 ) {
	            return 'about 1 year';
	        }
	       
	        return 'over ' . round(floatval($distanceInMinutes) / 525600) . ' years';
	}	
	
	
	public function FSTAB_ACL($dev,$aclenabled=0){
	
			$rows=explode("\n",@file_get_contents("/etc/fstab"));
			
			$dev_pattern=str_replace("/","\/",$dev);
			if(!is_array($rows)){writelogs_framework("no rows, abort",__FUNCTION__,__FILE__);return ;}
			while (list ($index, $line) = each ($rows) ){
				if(preg_match("#$dev_pattern\s+(.+?)\s+(.*?)\s+(.*?)\s+(.+)#",$line,$re)){
					$row_index=$index;
					$mount_path=$re[1];
					$type=$re[2];
					$option=$re[3];
					$other=$re[4];
					break;
				}
			}
			
			
			if($mount_path==null){return;}
			$options=explode(",",$option);
			
			while (list ($i, $token) = each ($options) ){$tokens[$token]=true;}
			
			writelogs_framework("OPTIONS=$option mount on $mount_path",__FUNCTION__,__FILE__);
			if($aclenabled==0){
				unset($tokens["acl"]);
				unset($tokens["user_xattr"]);
				$tune2fs_cmd="-acl";
			}else{
				$tokens["acl"]=true;
				$tokens["user_xattr"]=true;
				$tune2fs_cmd="+acl";
			}
			while (list ($token,$i) = each ($tokens) ){$final[]=$token;}	
			
			$new_options=implode(",",$final);
			writelogs_framework("$dev\t$mount_path\t$type\t$new_options\t$other",__FUNCTION__,__FILE__,__LINE__);
			reset($rows);
			$rows[$row_index]="$dev\t$mount_path\t$type\t$new_options\t$other";
			@file_put_contents("/etc/fstab",@implode("\n",$rows));
			$mount=$this->find_program("mount");
			exec("$mount -o remount $dev");	
			$tune2fs=$this->find_program("tune2fs");
			if($tune2fs<>null){
				exec("$tune2fs -o user_xattr,acl $dev 2>&1",$rs);
				writelogs_framework("$tune2fs -o user_xattr,acl $dev",__FUNCTION__,__FILE__,__LINE__);
				writelogs_framework(implode(",",$rs),__FUNCTION__,__FILE__,__LINE__);
				
			}
			
			}
	
	
	public function DISK_GET_TYPE($dev_path){
		$dev_path=str_replace("/","\/",$dev_path);
		$blkid=$this->find_program("blkid");
		$tmp=$this->FILE_TEMP();
		shell_exec("$blkid >$tmp 2>&1");
		$datas=explode("\n",@file_get_contents($tmp));
		@unlink($tmp);
		while (list ($num, $ligne) = each ($datas) ){
			if(preg_match("#^$dev_path.+?TYPE=\"(.+?)\"#",$ligne,$re)){
				return $re[1];
			}
			
		}
	}

}

function BuildingExecStatus($text,$pourc){
	$ini=new iniFrameWork("/usr/share/artica-postfix/ressources/logs/exec.status.ini");
	$ini->set("status","text","$text");
	$ini->set("status","pourc","$pourc");
	$ini->set("status","gpourc","$magor_pourc");
	$ini->saveFile("/usr/share/artica-postfix/ressources/logs/exec.status.ini");
	@chmod("/usr/share/artica-postfix/ressources/logs/exec.status.ini",0755);
	error_log(__FUNCTION__.":: status $pourc% $text");
	
}
function BuildingExecRightStatus($text,$pourc){
	$file="/usr/share/artica-postfix/ressources/logs/exec-right.status.ini";
	$ini=new iniFrameWork($file);
	$ini->set("status","text","$text");
	$ini->set("status","pourc","$pourc");
	$ini->saveFile($file);
	@chmod($file,0755);	
}


function cpulimit($forced=0){
	return;
	$unix=new unix();
	if($GLOBALS["cpulimit_path"]==null){
		$GLOBALS["cpulimit_path"]=$unix->find_program("cpulimit");
	}
	if($GLOBALS["cpulimit_path"]==null){return null;}
	if($forced==0){
		$cpu=trim(@file_get_contents("/etc/artica-postfix/settings/Daemons/cpulimit"));
		if($cpu==null){$cpu=0;}
	}else{
		$cpu=$forced;
	}
	if($cpu==0){return null;}
	$pid=getmypid();
	$cmd="{$GLOBALS["cpulimit_path"]} -p $pid -l $cpu -z >>/var/log/cpulimit 2>&1 &";
	if($GLOBALS["VERBOSE"]){writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);}
	shell_exec($cmd);
	
}


function GET_INFO_DAEMON($key){
   	if(function_exists("apc_store")){$value=apc_fetch($key);if($value<>null){return trim($value);}}	
	if($GLOBALS["GET_INFO_DAEMON"][$key]<>null){return $GLOBALS["GET_INFO_DAEMON"][$key];}
	$result=trim(@file_get_contents("/etc/artica-postfix/settings/Daemons/$key"));
	$GLOBALS["GET_INFO_DAEMON"][$key]=$result;
	if(function_exists("apc_store")){apc_store($key, $result);}	
	return $result;
}

function cpulimitProcessName($processName){
	$unix=new unix();
	$cpulimit=$unix->find_program("cpulimit");
	if($cpulimit==null){return null;}
	$cpu=GET_INFO_DAEMON('cpulimit');
	if($cpu==null){$cpu=0;}
	if($cpu==0){return null;}
	$cmd="$cpulimit -e $processName -l $cpu &";
	writelogs_framework("$cmd",__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);
	
}

function cpulimitProcessNameKill($processName){
	$unix=new unix();
	$cpulimit=$unix->find_program("cpulimit");
	if($cpulimit==null){return null;}	
	$cmd=$unix->find_program("pgrep")." -l -f \"$cpulimit -e $processName\"";
	exec($cmd,$results);
	if(!is_array($results)){return null;}
	while (list ($num, $ligne) = each ($results) ){
		if(preg_match("#([0-9]+)\s+(.+)#",$ligne,$re)){
			if(preg_match("#cpulimit#",$re[2])){
				if($re[1]>2){shell_exec($unix->find_program("kill")," -9 {$re[1]}");}	
			}
		}
	}
	
}


function writelogs_framework($text=null,$function=null,$file_source=null,$line=null){
if($file_source==null){$file_source= __FILE__ ;}
if(posix_getuid()==0){$prefix="[DAEMON]";}else{$prefix="[CONSOLE]";}
if(function_exists("getmypid")){$pid=getmypid();}

if($line<>null){$line=" line $line";}
$file_source=str_replace("/usr/share/artica-postfix/","",$file_source);
$file_source=str_replace("/home/dtouzeau/developpement/artica-postfix/","",$file_source);
if($GLOBALS["VERBOSE"]){echo "[$pid] $prefix::$function:: $text in $file_source$line\n";}
$text="[$pid] $prefix::$function:: $text in $file_source$line";
error_log("$text");
writeFrameOtherlogs('/var/log/artica-postfix/framework.debug',$text);
}
function writeFrameOtherlogs($file,$text=null){
			@mkdir(dirname($file));
		    $logFile=$file;
		    if(!is_dir(dirname($logFile))){mkdir(dirname($logFile));}
   		if (is_file($logFile)) { 
   			$size=filesize($logFile);
		    	if($size>100000){unlink($logFile);}
   		}
		 $logFile=str_replace("//","/",$logFile);
		$f = @fopen($logFile, 'a');
		
		@fwrite($f, "$text\n");
		@fclose($f);
}


class services_status{
	
	function services_status(){
		
	}
	
	public function artica_backup_status(){
		$unix=new unix();
		$pid=$unix->PIDOF("artica-backup");
		
		
	}
	
}


class kav4fsUnix{
	var	$MAIN=array();
	var $TASKS=array();
	
	
	function kav4fsUnix(){
		exec('/opt/kaspersky/kav4fs/bin/kav4fs-control --get-app-settings 2>&1',$results);
		writelogs_framework(count($results)." rows for --get-app-setting",__CLASS__."/".__FUNCTION__,__FILE__);
		$ini=new iniFrameWork();
		$ini->loadString("[INFOS]".@implode("\n",$results));
		$this->MAIN=$ini->_params["INFOS"];
	}
	
	
	function GetTaskList(){
		exec('/opt/kaspersky/kav4fs/bin/kav4fs-control --get-task-list 2>&1',$results);
		
		if(preg_match_all('#Name:\s+(.+?)\s+Id:\s+(.+?)\s+Class:\s+(.+?)\s+State:\s+([a-zA-Z0-9]+)#is',
		@implode("\n",$results),$re)){
			while (list ($index, $ID) = each ($re[2]) ){
				if(preg_match("#([0-9]+)\s+Runtime ID:\s+([0-9]+)#is",$ID,$ri)){
					$ID=$ri[1];
					$RUNTIMEID=$ri[2];
				}
				$array[$ID]=array(
					"NAME"=>$re[1][$index],"RUNTIMEID"=>$RUNTIMEID,
					"CLASS"=>$re[3][$index],"STATE"=>$re[4][$index]);
				
				$classes[$re[3][$index]][]=$ID;
			}
		
		}
		
		$this->TASKS["LIST"]=$array;
		$this->TASKS["CLASSES"]=$classes;
		
	}
	
	
}

class WakeOnLanClass{
 private $nic;
 var $error=array();
 	public function wake($mac){
 		$this->nic = fsockopen("udp://255.255.255.255", 9,$errno, $errstr);
 		if( !$this->nic ){
 			fclose($this->nic);
 			$this->error[]="fsockopen:\nERROR $errno \nudp 255.255.255.255\n {failed} $errstr";
 			return false;
 		}
 		else{
 			fwrite($this->nic, $this->pacquet($mac));
 			fclose($this->nic);
 			$this->error[]="{success}";
 			return true;
 		}
 }

 private function pacquet($Mac){
 	$packet = "";
 		for($i = 0; $i < 6; $i++){$packet .= chr(0xFF);}
 		for ($j = 0; $j < 16; $j++){
 		for($i = 0; $i < 12; $i=$i + 2){$packet .= chr(hexdec(substr($Mac, $i, 2)));}
 	}
 	return $packet;
 	}
 } 

class debuglogs{
	function debuglogs(){}
	
	public function events($text,$logFile,$phplog=false){
		$unix=new unix();
		$unix->events($text,$logFile,$phplog);
	}
	
}


function ini_set_verbosed(){
			ini_set('html_errors',0);
			ini_set('display_errors', 1);
			ini_set('error_reporting', E_ALL);
			ini_set('error_prepend_string','');
			ini_set('error_append_string','');	
}

?>