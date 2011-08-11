<?php
include_once(dirname(__FILE__) . '/ressources/class.ini.inc');
include_once(dirname(__FILE__) . '/ressources/class.mail.inc');
include_once(dirname(__FILE__) . '/ressources/class.phpmailer.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');

class cronldap{
	
	var $ldap_admin;
	var $ldap_password;
	var $suffix;
	var $ldap_host;
	var $ldap_port;
	var $cyrus_ldap_admin;
	var $cyrus_ldap_admin_password;
	var $cyrus_imapd_installed;
	var $ldap_connection;
	var $ldap_connected=false;
	
	
		function cronldap(){
			include(dirname(__FILE__) . "/ressources/settings.inc");
			$this->ldap_admin=$_GLOBAL["ldap_admin"];
			$this->ldap_password=$_GLOBAL["ldap_password"];
			$this->suffix=$_GLOBAL["ldap_root_database"];
			$this->ldap_host=$_GLOBAL["ldap_host"];
			$this->ldap_port=$_GLOBAL["ldap_port"];
			$this->cyrus_ldap_admin_password=$_GLOBAL["cyrus_ldap_admin_password"];
			$this->cyrus_imapd_installed=$_GLOBAL["cyrus_imapd_installed"];
			
			if($this->ldap_host==null){$this->ldap_host="127.0.0.1";}
			if($this->ldap_port==null){$this->ldap_port="389";}
			$this->Connect();
		}
		
		
		function Connect(){
		
		$this->ldap_connection=ldap_connect($this->ldap_host, $this->ldap_port ) ;
		if(!$this->ldap_connection){ echo("Unable to connect to ldap server {$this->ldap_host}");exit;}

		ldap_set_option($this->ldap_connection, LDAP_OPT_PROTOCOL_VERSION, 3); // on passe le LDAP en version 3, necessaire pour travailler avec le AD
		ldap_set_option($this->ldap_connection, LDAP_OPT_REFERRALS, 0); 				
		
		$bind=ldap_bind($this->ldap_connection, "cn=$this->ldap_admin,$this->suffix", $this->ldap_password);
		if(!$bind){
			$errornumber= ldap_errno($this->ldap_connection);
				
				switch (ldap_errno($this->ldap_connection)) {
						case 0x31:
							$error=$error . "\nBad username or password. Please try again.";
							break;
						case 0x32:
							$error=$error . "\nInsufficient access rights.";
							break;
						case 81:
							$error=$error . "\nUnable to connect to the LDAP server\n
							$ldap_host:$ldapport\nplease,\nverify if ldap daemon is running\n or the ldap server address";
							break;						
						case -1:
							
							break;
						default:
							$error=$error . "\nCould not bind to the $ldap_host LDAP server." ."\n". ldap_err2str($this->ldap_connection);
	 				}				
				
				echo "ERROR number : $errornumber\nWarning\n unable to connect with following\n cn=$this->ldap_admin,$this->suffix)\n$error\n";
				die($error);
			}
		$this->ldap_connected=true;	
		return true;
		}
		
	function ExistsDN($dn){
		$attrs=array();
		$search =@ldap_read($this->ldap_connection,$dn,'(objectClass=*)',$attrs);
		if (! $search){
			return false;}else{return true;}
	
		}

   	function ldap_add($dn,$update_array){
   		$r=@ldap_add($this->ldap_connection,$this->dn_escape($dn),$update_array);
   		if(!$r){
   			$error=ldap_errno($this->ldap_connection);
   			$msg=ldap_err2str(ldap_errno($this->ldap_connection));
   			if($error==20){return true;}
   			if($error==68){return true;}
   			echo "\n****\nError number $error\nAction:LDAP add\ndn:$dn\n$msg****\n";
   			return false;
   			}
   			
   		return true;}	
   		
	function ldap_add_mod($cn,$update_array){
		$res=@ldap_mod_add($this->ldap_connection,$this->dn_escape($cn),$update_array);
		
  		if(!$r){
			$nolog=false;
			if(ldap_errno($this->ldap_connection)=="0"){return true;}
   			if(ldap_errno($this->ldap_connection)<>0){
   				switch (ldap_errno($this->ldap_connection)) {
   					case 20:$nolog=true;break;
   					case 21:$nolog=true;return true;break;
   					case 0:$nolog=true;return true;break;
   					default:$nolog=false;break;
   				}
   			}
   			if($nolog==false){return false;}
   			
   		return true;
  		
  		}
			return true;
   		}   		

	function dn_escape($dn) {	
		$olddn = $dn;
		# Check if the RDN has a comma and escape it.
		while (preg_match('/([^\\\\]),(\s*[^=]*\s*),/',$dn))
			$dn = preg_replace('/([^\\\\]),(\s*[^=]*\s*),/','$1\\\\2C$2,',$dn);
			$dn = preg_replace('/([^\\\\]),(\s*[^=]*\s*)([^,])$/','$1\\\\2C$2$3',$dn);
		return $dn;
		}  

   	function Ldap_modify($dn,$update_array){
   		if(!is_array($update_array)){
   			//$this->ldap_last_error="No datas in this command ! on line " . __LINE__ . " in file " .__FILE__;
   			echo "No datas in this command !".__CLASS__ . "/" . __FUNCTION__.' '.__FILE__."\n";
   			return false;
   		}
   		$r=@ldap_modify($this->ldap_connection,$this->dn_escape($dn),$update_array);	
   		if(!$r){
   			
   			
   			$nolog=false;
   			if(ldap_errno($this->ldap_connection)<>0){
   				switch (ldap_errno($this->ldap_connection)) {
   					case 20:$nolog=true;break;
   					case 0:$nolog=true;break;
   					case '0':$nolog=true;break;
   					default:$nolog=false;break;
   				}
   			}
   			if($nolog==false){
   				$this->ldap_last_error='Error number ' . ldap_errno($this->ldap_connection) . "\nAction:LDAP Modify\ndn:$dn\n" . ldap_err2str(ldap_errno($this->ldap_connection));
   				echo "ERROR Number " . ldap_errno($this->ldap_connection) . " Ldap_modify  \"dn=$dn\" :  " . ldap_err2str(ldap_errno($this->ldap_connection))." ".__CLASS__ 
   				. "/" . __FUNCTION__." ".__FILE__."\n";
	   				while (list ($num, $ligne) = each ($update_array) ){
	   					if(is_array($ligne)){
	   						while (list ($a, $b) = each ($ligne) ){
	   							echo "ERROR $num($a)=$b",__CLASS__ . "/" . __FUNCTION__." ".__FILE__."\n";
	   						}
	   					}else{
	   						echo "ERROR $num=$ligne",__CLASS__ . "/" . __FUNCTION__." ".__FILE__."\n";
	   					}
	   					
	   				
	   			}
	   			return false;}
   			
   			return true;}
   		
   		return true;}		

}


class filesClasses{
	
		function FindFileInFolder($dir,$extension){
		$f=new filesClass();
		$array=$f->DirListTable($dir,true);
		while (list($num,$val)=each($array)){
			if($f->Get_extension($val)==$extension){return $val;}
		}
	}
	
	function Get_extension($filename){
		   	$parts = explode('.',$filename);
   			$last = count($parts) - 1;
   			$ext = $parts[$last];
   			return $ext;
		
		
		}
		
			
	function DirListTable($dir,$onlyfiles=false){
		$Files = array();
		$It=  @opendir($dir);
		if (! $It){return null;}
		while ($Filename = readdir($It)){
			if ($Filename == '.' || $Filename == '..')
			continue;
			
			if($onlyfiles==true){
				$ext=$this->Get_extension($Filename);
				if($ext<>null)
					$Files[]=$Filename;
				}
			else{$Files[]=$Filename;}
		}

		return $Files;
	}
	
}

class cronsql{
	var $connection;
	var $hostname;
	
function cronsql(){
		include(dirname(__FILE__) . '/ressources/settings.inc');
		$mysql_server=$_GLOBAL["mysql_server"];
		$mysql_admin=$_GLOBAL["mysql_admin"];
		$mysql_password=$_GLOBAL["mysql_password"];
		$mysql_port=$_GLOBAL["mysql_port"];
		$this->hostname=$_GLOBAL["fqdn_hostname"];
		
		if(($mysql_server=="localhost") OR ($mysql_server=="127.0.0.1")){
			$bd=@mysql_connect(":/var/run/mysqld/mysqld.sock",$mysql_admin,$mysql_password);
		}else{
			$bd=@mysql_connect("$mysql_server:$mysql_port",$mysql_admin,$mysql_password);
		}		
		
		if(!$bd){
    		$des=mysql_error();
    		write_syslogs(basename(__FILE__),"Unable to connect to mysql server $des");
			return false;
		}
		
		$this->connection=$bd;	
	}
	
function QUERY_SQL($sql,$database){
		$ok=@mysql_select_db($database,$this->connection);
    	if (!$ok){
    		$errnum=mysql_error();
    		$des=mysql_error();
    		write_syslogs(basename(__FILE__),"QUERY_SQL:: Error Number ($errnum) ($des)");
    		error_log("Error Number ($errnum) ($des)");
    		return false;
    
    	}	

    $results=mysql_query($sql,$this->connection);
	
		if(mysql_error()){
				$errnum=mysql_error();
				$des=mysql_error();
				error_log("Error Number $errnum $des");
				echo "$errnum $des\n";
				
		}	
	return $results;
}	

}

function write_syslogs($text,$file){
	echo date("Y-m-d h:i:s")." : $text\n";
	$file=basename($file);
	if(!function_exists('syslog')){return null;}
	openlog($file, LOG_PID | LOG_PERROR, LOG_LOCAL0);
	syslog(LOG_INFO, $text);
	closelog();	
}


function SendMailNotifWithSendMail($text,$subject,$smtp_sender,$email,$debug=false){
	
	
if(file_exists('/etc/artica-postfix/settings/Daemons/PostmasterAdress')){
	$PostmasterAdress=trim(file_get_contents('/etc/artica-postfix/settings/Daemons/PostmasterAdress'));
}	

if(!is_file("/usr/sbin/sendmail")){
	write_syslog("SendMailNotifWithSendMail():: Unable to stat /usr/sbin/sendmail",__FILE__);
	return null;
}

$mail=new simplemail();
$mail->set_mode='socket';
$mail->addrecipient($email,$email);
$mail->addfrom($smtp_sender,$smtp_sender);
$mail->addsubject($subject);
$mail->text = $text;

$mail->makebody();
$header=$mail->makeheader();
$mail->AddField2Header("To",$mail->makenameplusaddress($email,$email));
$content= $mail->headers."\r\n".$mail->CleanMailDataString($mail->body)."\r\n";
$tmpfile="/tmp/".md5(date('Y-m-d H:is')."-msmtp");
file_put_contents($tmpfile,$content);
write_syslog("Sending postfix report trought sendmail from $smtp_sender for $email width $tmpfile",__FILE__);
$cmd="/usr/sbin/sendmail -bm -t -f $smtp_sender <$tmpfile";
system($cmd);
@unlink($tmpfile);
}


function SendMailNotif($text=null,$subject=null,$smtp_sender,$smtp_dest,$verbose=false,$attached_files=array()){
	if($GLOBALS["VERBOSE"] ){$verbose=true;}

	$unix=new unix();
	$emailrelay_submit=$unix->find_program("emailrelay-submit");
	if(!is_file($emailrelay_submit)){
		SendMailNotifMSMTP($text,$subject,$smtp_sender,$smtp_dest,$verbose,$attached_files);
		return;
	}
	
	if(!is_file("$emailrelay_submit")){
		write_syslogs("Unable to stat emailrelay-submit, notifications cannot be sent"); 
		return;
	}
	
	$path="/etc/artica-postfix/smtpnotif.conf";
	if(!file_exists($path)){
		events_notif_class("SendMailNotif():: unable to stat $path");
		if($verbose){echo "unable to stat $path\n";}
		return null;
	}
	
$ini=new Bs_IniHandler($path);

if(file_exists('/etc/artica-postfix/settings/Daemons/PostfixPostmaster')){
	$PostmasterAdress=trim(file_get_contents('/etc/artica-postfix/settings/Daemons/PostfixPostmaster'));
}


if(trim($smtp_dest)==null){$smtp_dest="$PostmasterAdress";}
if(trim($smtp_sender)==null){$smtp_sender="$PostmasterAdress";}
if(trim($smtp_sender)==null){$smtp_sender="$smtp_dest";}

$mail = new PHPMailer(true); 
$mail->IsSendmail();
$mail->AddAddress($smtp_dest,$smtp_dest);
$mail->AddReplyTo($smtp_sender,$smtp_sender);
$mail->From=$smtp_sender;
$mail->Subject=$subject;
$mail->Body= $text;


if(count($attached_files)>0){
	while (list ($num, $file) = each ($attached_files)){
		if($verbose){echo "Add attachment $file\n";}
		events_notif_class("SendMailNotif():: Add attachment $file");
		$mail->AddAttachment($file,basename($file));
		
	}
	
}

$content=$mail->Send(true);
if($verbose){echo "Building notification from $smtp_sender with $msmtp to $smtp_dest $subject\n";}
events_notif_class("SendMailNotif():: Building notification from $smtp_sender with $msmtp to $smtp_dest $subject");
write_syslogs("Building notification from $smtp_sender with $msmtp to $smtp_dest $subject ". strlen($content) . " bytes",__FILE__);
$f=$unix->FILE_TEMP();
@file_put_contents($f,$content);
$cmd="$emailrelay_submit --spool-dir /var/spool/artica-notifier --from $smtp_sender $smtp_dest <$f 2>&1";
events_notif_class("SendMailNotif():: $cmd");
exec($cmd,$results);
events_notif_class("$cmd",__FILE__);
events_notif_class(@implode("\n",$results));
}

function SendMailNotifMSMTP($text=null,$subject=null,$smtp_sender,$smtp_dest,$verbose=false){
	$path="/etc/artica-postfix/smtpnotif.conf";
	if(!file_exists($path)){return null;}
	$ini=new Bs_IniHandler($path);
	
	if(file_exists('/etc/artica-postfix/settings/Daemons/PostmasterAdress')){
		$PostmasterAdress=trim(file_get_contents('/etc/artica-postfix/settings/Daemons/PostmasterAdress'));
	}
	
	$smtp_server_name=$ini->_params["SMTP"]["smtp_server_name"];
	$smtp_server_port=$ini->_params["SMTP"]["smtp_server_port"];
	$smtp_auth_user=$ini->_params["SMTP"]["smtp_auth_user"];
	$smtp_auth_passwd=$ini->_params["SMTP"]["smtp_auth_passwd"];
	$tls_enabled=$ini->_params["SMTP"]["tls_enabled"];
	
	if(trim($smtp_server_name)==null){$smtp_server_name="127.0.0.1";}
	if(trim($smtp_server_port)==null){$smtp_server_port="25";}
	if(trim($smtp_dest)==null){$smtp_dest="$PostmasterAdress";}
	
	$conf=$conf . "syslog on\n";
	$conf=$conf . "from $smtp_sender\n";
	$conf=$conf . "protocol smtp\n";
	$conf=$conf . "host $smtp_server_name\n";
	$conf=$conf . "port $smtp_server_port\n";
	if(trim($smtp_auth_user)<>null){
		$conf=$conf . "user $smtp_auth_user\n";
		$conf=$conf . "password $smtp_auth_passwd\n";
		$conf=$conf . "auth on\n";
	}
	
	if($tls_enabled==1){$conf=$conf . "tls on\n";}
	
	if(is_file("/usr/share/artica-postfix/bin/artica-msmtp")){$msmtp="/usr/share/artica-postfix/bin/artica-msmtp";}
	if(is_file("/usr/bin/msmtp")){$msmtp="/usr/bin/msmtp";}
	
	$mail=new simplemail();
	$mail->set_mode='socket';
	$mail->addrecipient($smtp_dest,$smtp_dest);
	
	$mail->addfrom($smtp_sender,$smtp_sender);
	$mail->addsubject($subject);
	$mail->text = $text;
	
	
	
	$mail->makebody();
	$header=$mail->makeheader();
	$mail->AddField2Header("To",$mail->makenameplusaddress($smtp_dest,$smtp_dest));
	$content= $mail->headers."\r\n".$mail->CleanMailDataString($mail->body)."\r\n";
	
	write_syslogs("Building notification from $smtp_sender with $msmtp to $smtp_dest $subject",__FILE__);
	
	if($verbose){$cmd_verbose=" -d";}
	
	$tmpfile="/tmp/".md5(date('Y-m-d H:is')."-msmtp");
	$tmpfileConf="/tmp/".md5(date('Y-m-d H:is')."-msmtp.conf");
	$logfile="/tmp/".md5(date('Y-m-d H:is')."-msmtp.log");
	file_put_contents($tmpfile,$content);
	file_put_contents($tmpfileConf,$conf);
	chmod($tmpfileConf,0600);
	$cmd="$msmtp $cmd_verbose --tls-certcheck=off --timeout=10 --file=$tmpfileConf --syslog=on  --logfile=$logfile -- $smtp_dest <$tmpfile";
	$data=system($cmd);
	$data=@file_get_contents($logfile);
	if($verbose){echo $mail->error_log."\n".$data;}
	@unlink($tmpfile);
	@unlink($tmpfileConf);
	@unlink($logfile);				
}


function SendMailNotification($text=null,$subject=null,$verbose=false,$attached_files=array(),$recipient=null){
$path="/etc/artica-postfix/smtpnotif.conf";
if(!file_exists($path)){return null;}
$ini=new Bs_IniHandler($path);
if(file_exists('/etc/artica-postfix/settings/Daemons/PostmasterAdress')){$PostmasterAdress=trim(file_get_contents('/etc/artica-postfix/settings/Daemons/PostmasterAdress'));}

$smtp_dest=$ini->_params["SMTP"]["smtp_dest"];
$smtp_sender=$ini->_params["SMTP"]["smtp_sender"];
if($smtp_sender==null){if($PostmasterAdress<>null){$smtp_sender=$PostmasterAdress;}}

$list_m=@file_get_contents("/etc/artica-postfix/settings/Daemons/SmtpNotificationConfigCC");
$list_cc=explode("\n",$list_m);

if(trim($subject)==null){$subject="SMTP NOTIFICATION TEST...";}
if(trim($text)==null){$text="No body was sent,\nthis simply a test body\n";}

events_notif_class("$subject to $smtp_dest");
if(trim($recipient)<>null){$smtp_dest=$recipient;}


	SendMailNotif($text,$subject,$smtp_sender,$smtp_dest,$verbose);
	events_notif_class("list_cc: ". count($list_cc)." recipient(s)");
	if(is_array($list_cc)){
		while (list ($num, $email_addr) = each ($list_cc) ){
			if(trim($email_addr)==null){continue;}
			events_notif_class("$email_addr -> SendMailNotif()");
			SendMailNotif($text,$subject,$smtp_sender,$email_addr,$verbose,$attached_files);
		}
	}
		
}

function BuildPid($file){
$pid=getmypid();
$file=basename($file);
$pidfile="/etc/artica-postfix/croned.1/$file.pid";
if(file_exists($pidfile)){
	$currentpid=trim(file_get_contents($pidfile));
	write_syslog("NewPID PID: $pid");
	write_syslog("Current PID: $currentpid");
	if($currentpid<>$pid){
		if(is_dir('/proc/'.$currentpid)){
			write_syslog("Already instance executed");
			return false;
	}else{
		write_syslog("$currentpid is not executed continue...");
		return true;
	}
		
	}
	
}	

return true;
}

function events_notif_class($text){
		$pid=getmypid();
		$filename=basename(__FILE__);
		$date=date("H:i:s");
		$logFile="/var/log/artica-postfix/notifications.debug";
		$size=filesize($logFile);
		if($size>1000000){unlink($logFile);}
		$f = @fopen($logFile, 'a');
		$line="$date {$filename}[{$pid}] $text\n";
		if($_GET["DEBUG"]){echo $line;}
		@fwrite($f,$line);
		@fclose($f);
	}

	





?>