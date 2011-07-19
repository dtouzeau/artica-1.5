<?php
if(!is_file('/etc/postfix/main.cf')){die();}
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.user.inc');
include_once(dirname(__FILE__) .'/ressources/smtp/htmlMimeMail.php');

$pid=getmypid();


if(file_exists('/etc/artica-postfix/croned.1/robot.whitelist.pid')){
	$currentpid=trim(file_get_contents('/etc/artica-postfix/croned.1/robot.whitelist.pid'));
	echo date('Y-m-d h:i:s')." NewPID PID: $pid\n";
	echo date('Y-m-d h:i:s')." Current PID: $currentpid\n";
	if($currentpid<>$pid){
		if(is_dir('/proc/'.$currentpid)){
			die(date('Y-m-d h:i:s')." Already instance executed");
	}else{
		echo date('Y-m-d h:i:s')." $currentpid is not executed continue...\n";
	}
		
	}
}

@mkdir("/etc/artica-postfix/croned.1");
file_put_contents('/etc/artica-postfix/croned.1/robot.whitelist.pid',$pid);

$count=ParseQueue();
if($count>0){
	write_syslog("Compile white & black lists",__FILE__);
	system('/usr/share/artica-postfix/bin/artica-ldap --wbld >/dev/null 2>&1');
}
die();

function ParseQueue(){
	
$array=DirList('/var/mail/artica-wbl');
if(!is_array($array)){return null;}
$ldap=new clladp();
while (list ($key, $filename) = each ($array) ){
	$continue=true;
	$noscan=false;
	if(!preg_match("#.+?\.ini$#",$filename)){continue;}
	$ini=new Bs_IniHandler("/var/mail/artica-wbl/$filename");
	$robot=$ini->_params["MAIL"]["action"];
	$robot_from=$ini->_params["MAIL"]["robotname"];
	if(preg_match("#.+?@(.+?)@(.+)#",$robot_from,$re)){$robot_from="{$re[1]}@{$re[2]}";}
	$user=$ini->_params["MAIL"]["orignal_from"];
	if($user==null){$user=$ini->_params["MAIL"]["From"];}
	$filecontent=$ini->_params["MAIL"]["Content"];
	$subject=$ini->_params["MAIL"]["subject"];
	$uid=$ldap->uid_from_email($user);
	
	
	write_syslog("ParseQueue():: Robot \"$robot_from\" Subject:$subject From:\"$uid\" <$user> ($robot)",__FILE__);
	if($robot==null){
		$continue=false;
		write_syslog("ParseQueue():: No robot specified",__FILE__);
	}
	
	if($uid==null){
		$uid=CreateThisUser($user);
		if($uid==null){
			write_syslog("ParseQueue():: Unable to detect internal user for $user",__FILE__);
			$continue=false;
		}
	}	
	
	if(!is_file($filecontent)){
			$continue=false;
			write_syslog("ParseQueue():: Unable to stat $filecontent",__FILE__);
	}
if(!$continue){
		@unlink("/var/mail/artica-wbl/$filename");
		@unlink($filecontent);
		continue;
	}
	
if($robot=="report"){$noscan=true;}
if($robot=="quarantine"){$noscan=true;}		
	
	$content=@file_get_contents($filecontent);
   if(!$noscan){
		$emails=DetecteMails($content,$subject);
		if(!is_array($emails)){
			write_syslog("ParseQueue():: Unable to detect mails in contents",__FILE__);
			@unlink("/var/mail/artica-wbl/$filename");
			@unlink($filecontent);
			continue;}
   }
	$ct=new user($uid);
	
	
	if($robot=="white"){
		$count=$count+1;
		while (list ($num, $addr) = each ($emails) ){	
			if(trim($addr)==null){continue;}
			if(substr($addr,0,1)=='-'){
				$addr=substr($addr,1,strlen($addr));
				if(!$ct->del_whitelist($addr)){$res[$addr]="failed ($robot) (delete)";}else{$res[$addr]="Success ($robot) (delete)";}
				continue;
			}
			if(!$ct->add_whitelist($addr)){$res[$addr]="failed ($robot)";}else{$res[$addr]="Success ($robot)";}
		}
	}
	
	if($robot=="black"){
		$count=$count+1;
		while (list ($num, $addr) = each ($emails) ){	
		if(trim($addr)==null){continue;}
		if(substr($addr,0,1)=='-'){
				$addr=substr($addr,1,strlen($addr));
				if(!$ct->del_blacklist($addr)){$res[$addr]="failed ($robot) (delete)";}else{$res[$addr]="Success ($robot) (delete)";}
				continue;
			}			
			
			if(!$ct->add_blacklist($addr)){$res[$addr]="failed ($robot)";}else{$res[$addr]="Success ($robot)";}
		}
	}

	if($robot=="report"){
		$sender=$robot_from;
		$recipient=$ct->mail;
		$uid=$ct->uid;
		write_syslog("ParseQueue():: build white & black lists report for $uid",__FILE__);
		BuildWhiteListReport($uid,$sender,$subject);
		@unlink("/var/mail/artica-wbl/$filename");
		@unlink($filecontent);	
		continue;	
	}
	
	if($robot=="quarantine"){
		$sender=$robot_from;
		$recipient=$ct->mail;
		$uid=$ct->uid;
		write_syslog("ParseQueue():: build quarantine report for $uid",__FILE__);
		$usr=new usersMenus();
		$PHP_BIN_PATH=$usr->PHP_BIN_PATH;
		$EXEC_NICE=$usr->EXEC_NICE;
		$cmd="$EXEC_NICE$PHP_BIN_PATH " . dirname(__FILE__)."/exec.quarantine.reports.php --single $uid $ct->ou $sender \"$subject\" &";
		write_syslog("ParseQueue():: processing \"$cmd\"",__FILE__);
		system($cmd);
		@unlink("/var/mail/artica-wbl/$filename");
		@unlink($filecontent);	
		continue;	
	}	
	
	
	if(is_array($res)){
		SendNotification($ct->mail,$robot_from,$subject,$res);
		write_syslog("ParseQueue():: Success for $robot list ordered by \"$ct->mail\" with ".implode(",",$emails)." addresses",__FILE__);
	}
	write_syslog("ParseQueue():: Deleting  $filename & ". basename($filecontent)." in queue dir",__FILE__);
	unlink("/var/mail/artica-wbl/$filename");
	unlink($filecontent);
	continue;	

	
	
	}
	
	return $count;
}

function SendNotification($mailto,$mailfrom,$subject,$results){
	$tmpfile="/tmp/".md5(date('Y-m-d H:i:s'));
	$html="<html>
		<header>
		</header>
		<body>";
	while (list ($email, $result) = each ($results) ){	
		$html=$html."<div><strong>$email</strong> <code>$result</code></div>\n";
		
	}
	$html=$html."</body></html>";
	$subject="Re:$subject";
	file_put_contents($tmpfile,$html);
	$cmd="/usr/share/artica-postfix/bin/artica-mime --sendmail --from=$mailfrom --to=$mailto --subject=\"$subject\" --content=\"$tmpfile\" --";
	system($cmd);
	@unlink($tmpfile);
	return true;
	
}


function DetecteMails($content,$subject){
	$tbl=explode("\n",$content);
	write_syslog("$subject " . count($tbl)." lines",__FILE__);
	if(preg_match('#([A-Za-z0-9\.\_\-\+\*]+)@([A-Za-z0-9\.\_\-\+\*]+)#',$subject,$re)){
		$email[]=trim($re[1])."@".$re[2];
	}
	
	while (list ($num, $line) = each ($tbl) ){
		
		if($line==null){continue;}
		if(preg_match('#(.+?)@(.+?)\s+#',$line,$re)){
			$email[]=trim($re[1])."@".trim($re[2]);
			continue;
		}
		
		if(preg_match('#([A-Za-z0-9\.\_\-\+]+)@([A-Za-z0-9\.\_\-\+]+)#',$line,$re)){
			$email[]=trim($re[1])."@".$re[2];
			continue;
		}
		
		write_syslog("Not detected $line",__FILE__);
		
	}
	return $email;
	
}



function DirList($path){
	$dir_handle = @opendir($path);
	if(!$dir_handle){
		write_syslog("DirList():: Unable to open \"$path\"",__FILE__);
		return array();
	}
	
while ($file = readdir($dir_handle)) {
  if($file=='.'){continue;}
  if($file=='..'){continue;}
  if(!is_file("$path/$file")){continue;}
  $array[$file]=$file;
  }
if(!is_array($array)){return array();}
@closedir($dir_handle);
return $array;
	
	
}

function CreateThisUser($email){
	
	if(!preg_match("#(.+?)@(.+)#",$email,$re)){return null;}
	
	$domain=$re[2];
	$uid=$re[1];	
	$ldap=new clladp();
	$ou=$ldap->ou_by_smtp_domain($domain);
	if($ou==null){
		write_syslog("CreateThisUser():: Unable to detect organization by domain \"$domain\"",__FILE__);
		return null;
	}
	
	$ct=new user($uid);
	$ct->ou=$ou;
	$ct->mail=$email;
	$ct->uid=$uid;
	if(!$ct->add_user()){
		write_syslog("CreateThisUser():: Unable to Create user $uid \"$email\"",__FILE__);
		return null;
	}
	
	$uid2=$ldap->uid_from_email($email);
	write_syslog("CreateThisUser():: new user \"$uid2\"",__FILE__);
	return $uid2;
	
}

function BuildWhiteListReport($uid,$sender_email,$subject){
	$ct=new user($uid);
	if(preg_match("#(.+?)@(.+)#",$ct->mail,$re)){
		$domain=$re[2];
		$global_white=report_build_white_from_domain($domain);
		$global_black=report_build_black_from_domain($domain);
		}
	
	
	if(is_array($ct->amavisBlacklistSender)){
		
		$html=$html . "
		<hr>
		<H2 style='font-size:13px'>Deny email addresses</h2>
		<table style='width:100%'>";
		
		while (list ($num, $email) = each ($ct->amavisBlacklistSender) ){
			$html=$html . "
				<tr>
				<td><strong style='font-size:12px'>From $email</td>
				<td><strong style='font-size:12px'>To you $ct->DisplayName</td>
				<td><div style='width:5px;background-color:red'>&nbsp;</div></td>
				<td><strong style='font-size:12px'>Deny</td>
				</tr>
				<tr><td colspan=4><hr></tr>";
				}
				
			
		$html=$html . "</table>";
		
		
		
	}
	
	if(is_array($ct->amavisWhitelistSender)){
		
		$html=$html . "
		<hr>
		<H2 style='font-size:13px'>Allow email addresses</h2>
		<table style='width:100%'>";
		
		while (list ($num, $email) = each ($ct->amavisBlacklistSender) ){
			$html=$html . "
				<tr>
				<td><strong style='font-size:12px'>From $email</td>
				<td><strong style='font-size:12px'>To you $ct->DisplayName</td>
				<td><div style='width:5px;background-color:green'>&nbsp;</div></td>
				<td><strong style='font-size:12px'>Bypass filters</td>
				</tr>
				<tr><td colspan=4><hr></tr>";
				}
				
			
		$html=$html . "</table>";
		
		
		
	}
	
$html="
<html>
<head></head>
<body>
$global_white
$global_black
<hr>
$html
</body>
</html>
";
	$tmpfile="/tmp/".md5($html);
	$subject="Re:$subject $ct->DisplayName white and black list report";
	file_put_contents($tmpfile,$html);
	$cmd="/usr/share/artica-postfix/bin/artica-mime --sendmail --from=$sender_email --to=$ct->mail --subject=\"$subject\" --content=\"$tmpfile\" --";
	system($cmd);
	@unlink($tmpfile);
	return true;

//get global filters from domain.
	
}

function report_build_white_from_domain($domain){
if($domain==null){return null;}	
$ldap=new clladp();


$hash=$ldap->WhitelistsFromDomain($domain);
if(!is_array($hash)){return null;}
	
		$html=$html . "
		<hr>
		<H2 style='font-size:13px'>Accept email addresses (Global)</h2>
		<table style='width:100%'>";
		
		while (list ($email, $array) = each ($hash) ){
			
			while (list ($num, $match) = each ($array) ){
			$html=$html . "
				<tr>
				<td><strong style='font-size:12px'>From $match</td>
				<td><strong style='font-size:12px'>To $email</td>
				<td><div style='width:5px;background-color:green'>&nbsp;</div></td>
				<td><strong style='font-size:12px'>Bypass filters</td>
				</tr>
				<tr><td colspan=4><hr></tr>";
				}
		}
				
			
		$html=$html . "</table>";
	
return $html;
}
function report_build_black_from_domain($domain){
if($domain==null){return null;}	
$ldap=new clladp();


$hash=$ldap->BlackListFromDomain($domain);
if(!is_array($hash)){return null;}
	
		$html=$html . "
		<hr>
		<H2 style='font-size:13px'>Deny email addresses (Global)</h2>
		<table style='width:100%'>";
		
		while (list ($email, $array) = each ($hash) ){
			
			while (list ($num, $match) = each ($array) ){
			$html=$html . "
				<tr>
				<td><strong style='font-size:12px'>From $match</td>
				<td><strong style='font-size:12px'>To $email</td>
				<td><div style='width:5px;background-color:red'>&nbsp;</div></td>
				<td><strong style='font-size:12px'>Deny</td>
				</tr>
				<tr><td colspan=4><hr></tr>";
				}
		}
				
			
		$html=$html . "</table>";
	
return $html;
}



?>