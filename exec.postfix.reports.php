<?php
if(posix_getuid()<>0){
	die("Cannot be used in web server mode\n\n");
}


	include_once(dirname(__FILE__) . '/ressources/class.pflogsumm.inc');
	include_once(dirname(__FILE__) . '/ressources/class.users.menus.inc');
	include_once(dirname(__FILE__) . '/ressources/class.artica.inc');
	include_once(dirname(__FILE__) . '/class.cronldap.inc');
	
$verbose=$argv[1];	
if($verbose=="--verbose"){$debug=true;}

if(!is_file("/usr/sbin/pflogsumm")){
	write_syslog("wants pflogsumm reporting but /usr/sbin/pflogsumm does not exists",__FILE__);
	die();
}

if(is_file("/tmp/pflogsumm")){@unlink("/tmp/pflogsumm");}


$users=new usersMenus();
$maillog=$users->maillog_path;
$pflogsumm=new pflogsumm();
$rec=explode(",",$pflogsumm->main_array["SETTINGS"]["recipients"]);
$subject=$pflogsumm->main_array["SETTINGS"]["subject"] . " " . date('F l d');
$smtp_sender=$pflogsumm->main_array["SETTINGS"]["sender"];
$use_send_mail=$pflogsumm->main_array["SETTINGS"]["use_send_mail"];


	if($pflogsumm->notif_enabled<>1){
			write_syslog("wants pflogsumm reporting but smtp notifications are disabled",__FILE__);
			die();
		}



	if(!is_array($rec)){
		write_syslog("wants pflogsumm reporting but no recipients set",__FILE__);
		die();
		}

while (list ($num, $email) = each ($rec) ){
	if(trim($email)==null){continue;}
		$recipients[$email]=$email;
	}
	
	
	if(!is_array($recipients)){
		write_syslog("wants pflogsumm reporting but no recipients set",__FILE__);
		die();
	}	

	if(!is_file("$maillog")){
		write_syslog("wants pflogsumm reporting but cannot stat maillog or mail.log",__FILE__);
		die();
	}

system("/usr/sbin/pflogsumm -d today --verbose_msg_detail $maillog >/tmp/pflogsumm 2>&1");
$reporting=file_get_contents("/tmp/pflogsumm");
write_syslog("pflogsumm reporting generated for " .count($recipients) . " recipients with " . strlen($reporting). " bytes length",__FILE__);
@unlink("/tmp/pflogsumm");

	
while (list ($num, $email) = each ($recipients) ){
	if(trim($email)==null){continue;}
	if($use_send_mail==1){
		SendMailNotifWithSendMail($reporting,$subject,$smtp_sender,$email,$debug);
	}else{
		SendMailNotif($reporting,$subject,$smtp_sender,$email,$debug);
	}

}


?>