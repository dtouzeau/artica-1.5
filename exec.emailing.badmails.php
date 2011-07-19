<?php
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/ressources/class.emailings.inc');
include_once(dirname(__FILE__).'/ressources/class.phpmailer.inc');
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;$GLOBALS["debug"]=true;}



if($argv[1]=="--account"){ParseMailbox_task($argv[2]);die();}
if($argv[1]=="--clean"){CleanDatabases_perf();die();}

ParseAllMailboxes();

function ParseAllMailboxes(){
	$sql="SELECT account_name FROM emailing_campain_imap WHERE enabled=1";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo ("$q->mysql_error");}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
		ParseMailbox_task($ligne["account_name"]);
	}

}



function ParseMailbox_task($account){
	$GLOBALS["ACCOUNT_IMAP"]=$account;
	$q=new mysql();
	$sql="SELECT parameters FROM emailing_campain_imap WHERE account_name='$account'";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	$PARAMS=unserialize(base64_decode($ligne["parameters"]));
	if(!$q->ok){events("Unable to get informations",$q->mysql_error,$account,__LINE__);return;}
	if($PARAMS["enabled"]<>1){events("This account is disabled",$q->mysql_error,$account,__LINE__);return;}
	if($PARAMS["use_ssl"]==1){
		$server_pattern="{{$PARAMS["servername"]}:993/imap/ssl/novalidate-cert}";
		
	}else{
		$server_pattern="{{$PARAMS["servername"]}:143/novalidate-cert}";
	}
	$GLOBALS["SERVER_IMAP_PATTERN"]=$server_pattern;
	$GLOBALS["MBXCON"] = imap_open("{$server_pattern}INBOX", $PARAMS["username"], $PARAMS["password"]);
	if(!$GLOBALS["MBXCON"]){events("Unable to connect to imap server ",imap_last_error(),$account,__LINE__);@imap_close($mbox);return;}
	
	
	if(!imap_createmailbox($GLOBALS["MBXCON"], imap_utf7_encode("{$server_pattern}INBOX/emailing_read"))){
		$error=imap_last_error();
		if(!preg_match("#already exists#",$error)){
			events("{$server_pattern}INBOX/emailing_read failed to create mbox ",$error,$account,__LINE__);
			@imap_close($GLOBALS["MBXCON"]);
			return;
		}
	}	
	
	$status = imap_status($GLOBALS["MBXCON"], "{$server_pattern}INBOX", SA_ALL);
	if ($status) {$messages_number=$status->messages;}else{
	events("imap_status failed ",imap_last_error(),$account,__LINE__);
	@imap_close($GLOBALS["MBXCON"]);
	return;
	}
	
	if($messages_number==0){
		events("No messages, aborting",imap_last_error(),$account,__LINE__);
		@imap_close($GLOBALS["MBXCON"]);
		return;		
	}
	
	
	$TOSEARCH=array("Returned mail: see transcript for details",
	"Undelivered Mail Returned to Sender",
	"Delivery Status Notification",
	"failure notice",
	"DELIVERY FAILURE:",
	"Returned mail:",
	"Delivery Notification",
	"Mail delivery failed",
	"Non remis","Undeliverable","Notification","Mail could not be delivered","Delivery Final Failure Notice","Unzustellbar","Mail System Error",
	"Error sending message","Permanent Delivery Failure","Mail routing error","Delivery failure","Mail could not be delivered","Your message can not be delivered",
	"auto-reply concerning","Delivery Status","User unknown","Rapport de Non-Remise","Undeliverable","Automatically rejected mail","NDN:",
	"Delivery Final Failure Notice"
	);
	
	$i=0;
	foreach($TOSEARCH as $findstr) {
		$result = @imap_search($GLOBALS["MBXCON"],'SUBJECT "'.$findstr.'"');
		if(is_array($result)){
		foreach($result as $msgno) {
			CheckMessage($msgno,$findstr);
			purge(false);
			$i++;	
			}
		}
		
		if($i>0){
			events("$findstr: $i message(s)",null,$account,__LINE__);
			@imap_expunge($GLOBALS["MBXCON"]);
			$i=0;
		}
	}
	
	if($GLOBALS["MUSTCLEAN"]){CleanAllDatabases();$GLOBALS["MUSTCLEAN"]=false;}
	
	$TOSEARCH=array("Absence du bureau","Warning: could not send message for past","Out of Office AutoReply","Read: ","Out of Office",
	"Message d\'absence","Réponse automatique","R=E9ponse_automatique","R=E9ponse_automatique","Lu :","Lu:","Lu : ","Message you sent blocked","out of the office"
	,"absence", "Auto:","est absent","Accusé de réception","Lu :","Your message to support awaits moderator approval","Auto Reply:",
	"Delivery Delayed","delayed 48 hours","delayed 24 hours","delayed 72 hours","autoconfirm");
	$i=0;
	foreach($TOSEARCH as $findstr) {
		$result = @imap_search($GLOBALS["MBXCON"],'SUBJECT "'.$findstr.'"');
		if(is_array($result)){
			foreach($result as $msgno) {
			@imap_delete($GLOBALS["MBXCON"], "$msgno:$msgno");
			$i++;	
			}
		}
		
		if($i>0){
			events("$findstr: $i message(s)",null,$account,__LINE__);
			@imap_expunge($GLOBALS["MBXCON"]);
			$i=0;
		}
		
	}	
	
	
	if($i>0){
			events("$findstr: $i message(s)",null,$account,__LINE__);
			@imap_expunge($GLOBALS["MBXCON"]);
			$i=0;
		}	
	
	
	$check = imap_check($GLOBALS["MBXCON"]);
	if(!$check){
		events("imap_check failed ",imap_last_error(),$account,__LINE__);
		@imap_close($GLOBALS["MBXCON"]);
		return;	
	}
	

	
	
	events("Parsing $check->Nmsgs message(s)",imap_last_error(),$account,__LINE__);	
	
	
	if($check->Nmsgs>500){$max=500;}else{$max=$check->Nmsgs;}
	$overviews = imap_fetch_overview($GLOBALS["MBXCON"],"1:$max");
	$i=0;
	foreach($overviews as $overview){
		$i++;
    	
    	if(preg_match("#Mail delivery failed#",$overview->subject)){
    		CheckMessage($overview->uid,$overview->subject);
    		continue;
    	}
    	
    	if(preg_match("#Undelivered Mail Returned to Sender#",$overview->subject)){
    		CheckMessage($overview->uid,$overview->subject);
    		continue;	
    	}
    	
    	if(preg_match("#Undeliverable mail:#",$overview->subject)){
    		CheckMessage($overview->uid,$overview->subject);
    		continue;	
    	}
    	
    	if(preg_match("#Delivery Status Notification#",$overview->subject)){
    		CheckMessage($overview->uid,$overview->subject);
    		continue;	
    	}
    	
    	if(preg_match("#Returned mail: see transcript for details#",$overview->subject)){
    		CheckMessage($overview->uid,$overview->subject);
    		continue;	
    	}
    	
    	if(preg_match("#DELIVERY FAILURE#",$overview->subject)){
    		CheckMessage($overview->uid,$overview->subject);
    		continue;	
    	}
    	
    	if(preg_match("#Delivery Final Failure Notice#",$overview->subject)){
    		CheckMessage($overview->uid,$overview->subject);
    		continue;	
    	}
    	
	    if(preg_match("#R=E9ponse_automatique=A0#",$overview->subject)){
    		@imap_delete($GLOBALS["MBXCON"], "$overview->uid:$overview->uid");
    		continue;	
    	}
    	

    	
    	
    	if(preg_match("#Out of Office#",$overview->subject)){
    		@imap_delete($GLOBALS["MBXCON"], "$overview->uid:$overview->uid");
    		continue;	
    	}
    	
    	if(preg_match("#Diffusion des messages#",$overview->subject)){
    		@imap_delete($GLOBALS["MBXCON"], "$overview->uid:$overview->uid");
    		continue;	
    	}
    	
    	if(preg_match("#Warning: could not send message for past#",$overview->subject)){
    		@imap_delete($GLOBALS["MBXCON"], "$overview->uid:$overview->uid");
    		continue;	
    	}
    	
    	if(preg_match("#Company=20Vacancy=20#",$overview->subject)){
    		@imap_delete($GLOBALS["MBXCON"], "$overview->uid:$overview->uid");
    		continue;	
    	}
    	
    	if(preg_match("#R=E9ponse_automatique_d'absence_du_bureau#",$overview->subject)){
    		@imap_delete($GLOBALS["MBXCON"], "$overview->uid:$overview->uid");
    		continue;	
    	}
    	
    	if(preg_match("#Automated Reply#",$overview->subject)){
    		@imap_delete($GLOBALS["MBXCON"], "$overview->uid:$overview->uid");
    		continue;	
    	}
    	
    	if(preg_match("#R=E9ponse_automatique_d=27absence_du_bureau#",$overview->subject)){
    		@imap_delete($GLOBALS["MBXCON"], "$overview->uid:$overview->uid");
    		continue;	
    	}
    	
    	if(preg_match("#est absent\(e\)\.#",$overview->subject)){
    		@imap_delete($GLOBALS["MBXCON"], "$overview->uid:$overview->uid");
    		continue;	
    	}
    	
    	if(preg_match("#OUT OF OFFICE#",$overview->subject)){
    		@imap_delete($GLOBALS["MBXCON"], "$overview->uid:$overview->uid");
    		continue;	
    	}   

    	if(preg_match("#Absence du bureau#",$overview->subject)){
    		@imap_delete($GLOBALS["MBXCON"], "$overview->uid:$overview->uid");
    		continue;	
    	}   
    	
    	if(preg_match("#failure notice#",$overview->subject)){
    		CheckMessage($overview->uid,$overview->subject);
    		continue;	
    	}
    	
    	if(preg_match("#Validation de votre mail a destination#",$overview->subject)){
    		CheckMessage($overview->uid,$overview->subject);
    		continue;	
    	}
    	
    	if(preg_match("#Non remis#",$overview->subject)){
    		CheckMessage($overview->uid,$overview->subject);
    		continue;	
    	}
    	
    	if(preg_match("#Mail delivery failure#",$overview->subject)){
    		CheckMessage($overview->uid,$overview->subject);
    		continue;	
    	}
    	
		if(preg_match("#Notification\s+d'.+?AOk-tat\s+de\s+remise#",$overview->subject)){
    		CheckMessage($overview->uid,$overview->subject);
    		continue;	
    	}

    	if(preg_match("#Message you sent blocked#",$overview->subject)){
    		CheckMessage($overview->uid,$overview->subject);
    		continue;	
    	}
    	
    	if(preg_match("#Delivery Status Notification#",$overview->subject)){
    		CheckMessage($overview->uid,$overview->subject);
    		continue;	
    	}
    	
    	if(preg_match("#Notifications Mail Delivery System#",$overview->from)){
    		CheckMessage($overview->uid,$overview->subject);
    		continue;	
    	}
    	if(preg_match("#Delivery Notification: Delivery has failed#",$overview->subject)){
    		CheckMessage($overview->uid,$overview->subject);
    		continue;	
    	}
    	
    	if(preg_match("#Envoi du message impossible#",$overview->subject)){
    		CheckMessage($overview->uid,$overview->subject);
    		continue;	
    	}
    	
    	if(preg_match("#Notification__d'+AOk-tat__de__remise__=28+AOk-chec#",$overview->subject)){
    		CheckMessage($overview->uid,$overview->subject);
    		continue;	
    	}
    	
    	if(preg_match("#Delivery status notification#",$overview->subject)){
    		CheckMessage($overview->uid,$overview->subject);
    		continue;	
    	}
    	
    	if(preg_match("#Returned mail: User unknown#",$overview->subject)){
    		CheckMessage($overview->uid,$overview->subject);
    		continue;	
    	}
    	
    	if(preg_match("#Delivery Status#",$overview->subject)){
    		CheckMessage($overview->uid,$overview->subject);
    		continue;	
    	}
    	
    	if(preg_match("#Undeliverable:#",$overview->subject)){
    		CheckMessage($overview->uid,$overview->subject);
    		continue;	
    	} 
    	
    	if(preg_match("#Delivery failure#",$overview->subject)){
    		CheckMessage($overview->uid,$overview->subject);
    		continue;	
    	} 
    	
    	if(preg_match("#Message Notification#",$overview->subject)){
    		CheckMessage($overview->uid,$overview->subject);
    		continue;	
    	} 
    	
    	if(preg_match("#Votre message.+?a ete rejete#",$overview->subject)){
    		CheckMessage($overview->uid,$overview->subject);
    		continue;	
    	} 
    	
    	if(preg_match("#Warning: could not send message for past#",$overview->subject)){
    		CheckMessage($overview->uid,$overview->subject);
    		continue;	
    	} 
    	
    	if(preg_match("#Undeliverable mail#",$overview->subject)){
    		CheckMessage($overview->uid,$overview->subject);
    		continue;	
    	} 
    	
    	if(preg_match("#Changement adresse email#",$overview->subject)){
    		@unlink($file);
			if(imap_mail_move($GLOBALS["MBXCON"],"$uid:$uid","INBOX/emailing_read")){continue;}else{events("INBOX/emailing_read: unable to mode message $uid:$uid ",imap_last_error(),$GLOBALS["ACCOUNT_IMAP"],__LINE__);}
			continue;
    	} 
    	
    	if(preg_match("#protected against spam by SpamWars#",$overview->subject)){
    		@unlink($file);
			if(imap_mail_move($GLOBALS["MBXCON"],"$uid:$uid","INBOX/emailing_read")){continue;}else{events("INBOX/emailing_read: unable to mode message $uid:$uid ",imap_last_error(),$GLOBALS["ACCOUNT_IMAP"],__LINE__);}
			continue;
    	} 
    	
    	if(preg_match("#Validation de votre mail a destination de#",$overview->subject)){
    		@unlink($file);
			if(imap_mail_move($GLOBALS["MBXCON"],"$uid:$uid","INBOX/emailing_read")){continue;}else{events("INBOX/emailing_read: unable to mode message $uid:$uid ",imap_last_error(),$GLOBALS["ACCOUNT_IMAP"],__LINE__);}
			continue;
    	}  
    	
    	if(preg_match("#is blocked in my spam folder awaiting your authentication#",$overview->subject)){
    		@unlink($file);
			if(imap_mail_move($GLOBALS["MBXCON"],"$uid:$uid","INBOX/emailing_read")){continue;}else{events("INBOX/emailing_read: unable to mode message $uid:$uid ",imap_last_error(),$GLOBALS["ACCOUNT_IMAP"],__LINE__);}
			continue;
    	} 
    	
    	if(preg_match("#Merci de me retirer de votre liste de distribution#",$overview->subject)){
    		@unlink($file);
			if(imap_mail_move($GLOBALS["MBXCON"],"$uid:$uid","INBOX/emailing_read")){continue;}else{events("INBOX/emailing_read: unable to mode message $uid:$uid ",imap_last_error(),$GLOBALS["ACCOUNT_IMAP"],__LINE__);}
			continue;
    	} 
    	
    	if(preg_match("#Notre nom de domaine .+? a chang#",$overview->subject)){
    		@unlink($file);
			if(imap_mail_move($GLOBALS["MBXCON"],"$uid:$uid","INBOX/emailing_read")){continue;}else{events("INBOX/emailing_read: unable to mode message $uid:$uid ",imap_last_error(),$GLOBALS["ACCOUNT_IMAP"],__LINE__);}
			continue;
    	} 
    	
    	if(preg_match("#METTRE A JOUR VOTRE CARNET D'ADRESSES#",$overview->subject)){
    		@unlink($file);
			if(imap_mail_move($GLOBALS["MBXCON"],"$uid:$uid","INBOX/emailing_read")){continue;}else{events("INBOX/emailing_read: unable to mode message $uid:$uid ",imap_last_error(),$GLOBALS["ACCOUNT_IMAP"],__LINE__);}
			continue;
    	} 
    	
    	if(preg_match("#Demande de support#",$overview->subject)){
    		@unlink($file);
			if(imap_mail_move($GLOBALS["MBXCON"],"$uid:$uid","INBOX/emailing_read")){continue;}else{events("INBOX/emailing_read: unable to mode message $uid:$uid ",imap_last_error(),$GLOBALS["ACCOUNT_IMAP"],__LINE__);}
			continue;
    	} 
    	
    	if(preg_match("#Returned mail:#",$overview->subject)){
    		CheckMessage($overview->uid,$overview->subject);
    		continue;	
    	} 
    	
    	if(preg_match("#Delivery Notification#",$overview->subject)){
    		CheckMessage($overview->uid,$overview->subject);
    		continue;	
    	} 
    	
    	if(preg_match("#Mail Notification#",$overview->subject)){
    		CheckMessage($overview->uid,$overview->subject);
    		continue;	
    	}

    	if(preg_match("#Mail System Error#",$overview->subject)){
    		CheckMessage($overview->uid,$overview->subject);
    		continue;	
    	}
    	
    	if(preg_match("#DELIVERY_FAILURE#",$overview->subject)){
    		CheckMessage($overview->uid,$overview->subject);
    		continue;	
    	}
    	
    	echo "$overview->date: From: $overview->from uid:$overview->uid \"$overview->subject\"\n";
    	
    	if($i>$max){break;}		     
	}
	

	
	
purge();
if($GLOBALS["MUSTCLEAN"]){CleanAllDatabases();$GLOBALS["MUSTCLEAN"]=false;}
@imap_close($GLOBALS["MBXCON"]);	

}


function CleanAllDatabases(){
	sys_THREAD_COMMAND_SET(LOCATE_PHP5_BIN2()." ". __FILE__." --clean");
	
}

function purge($expunge=true){
	$q=new mysql();
	if(is_array($GLOBALS["FOUND_BAD_MAIL"])){
		events("PURGE ". count($GLOBALS["FOUND_BAD_MAIL"])." bad emails",imap_last_error(),$account,__LINE__);
		while (list ($email, $none) = each ($GLOBALS["FOUND_BAD_MAIL"]) ){
			if(preg_match("#(.+?)@(.+?)>#",$email,$re)){$email="{$re[1]}@{$re[2]}";}
			$email=str_replace("\"","",$email);
			$email=str_replace("'","",$email);
			$email=str_replace("<","",$email);
			$email=str_replace(">","",$email);
			if(!preg_match("#.+?@.+#",$email));
			$email=trim(addslashes(strtolower($email)));
			$sqla[]="('$email','Bad email')";
			
			
		}
	}
	
	
if(is_array($sqla)){
	$sql="INSERT INTO emailing_campain_blacklist (`email`,`reason`) VALUES ".@implode(",",$sqla);
	$q->QUERY_SQL($sql,"artica_backup");
	echo $sql;
	if(!$q->ok){
		events("Mysql failed ",$q->mysql_error."\n$sql",$account,__LINE__);
		return;
	}
	unset($GLOBALS["FOUND_BAD_MAIL"]);
	$GLOBALS["MUSTCLEAN"]=true;
	if($expunge){@imap_expunge($GLOBALS["MBXCON"]);}	
}		
	
}

function CheckMessage($uid,$subject){
	
	
	
	$file="/tmp/imap-$uid.msg";
	
	
	
	if(!imap_savebody($GLOBALS["MBXCON"], $file, $uid)){
		events("Failed save message $uid",imap_last_error(),$GLOBALS["ACCOUNT_IMAP"],__LINE__);
		return false;
	}
	
	$datas=@file_get_contents($file);
	if(preg_match("#The following addresses failed:\s+<(.+?)>#is",$datas,$re)){	
		if(preg_match("#(.+?)@(.+?)>#",$re[1],$ri)){$re[1]="{$ri[1]}@{$ri[2]}";}
		@unlink($file);
		@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
		$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
		return true;
	}
	
	if(preg_match("#Return Receipt#",$datas,$re)){	
		@unlink($file);
		@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");	
	}
	
	if(preg_match("#Final-Recipient: rfc822;.+?\s+Action: delayed#",$datas,$re)){	
		@unlink($file);
		@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");	
	}

	if(preg_match("#delivery temporarily suspended#",$datas,$re)){	
		@unlink($file);
		@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");	
	}
	
	if(preg_match("#said: 550 5\.1\.1 <(.+?)>: Recipient address rejected#is",$datas,$re)){
		
		if(preg_match("#(.+?)@(.+?)>#",$re[1],$ri)){$re[1]="{$ri[1]}@{$ri[2]}";}
		@unlink($file);
		@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
		$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
		return true;
	}
	
	if(preg_match("#R=E9ponse automatique d'absence du bureau#i",$datas,$re)){
		@unlink($file);
		@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
		return true;
	}
	
	if(preg_match("#Je suis absent entre#",$datas,$re)){
		@unlink($file);
		@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
		return true;
	}
	
	if(preg_match("#Je suis en cong.+?du#",$datas,$re)){
		@unlink($file);
		@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
		return true;
	}
	
	 if(preg_match("#550\s+<(.+?)>: User unknown#",$datas,$re)){
		
		if(preg_match("#(.+?)@(.+?)>#",$re[1],$ri)){$re[1]="{$ri[1]}@{$ri[2]}";}
		@unlink($file);
		@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
		$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
		return true;
	}
	
	if(preg_match("#550\s+<(.+?)>: Recipient address\s+rejected#",$datas,$re)){
		if(preg_match("#(.+?)@(.+?)>#",$re[1],$ri)){$re[1]="{$ri[1]}@{$ri[2]}";}
		@unlink($file);
		@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
		$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
		return true;
	}
	
	if(preg_match("#550 5\.1\.1 <(.+?)>: Recipient\s+address rejected:#",$datas,$re)){
		
		if(preg_match("#(.+?)@(.+?)>#",$re[1],$ri)){$re[1]="{$ri[1]}@{$ri[2]}";}
		@unlink($file);
		@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
		$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
		return true;
	}	
	
	
	if(preg_match("#<(.+?)>: Host or domain name not found#",$datas,$re)){
		
		if(preg_match("#(.+?)@(.+?)>#",$re[1],$ri)){$re[1]="{$ri[1]}@{$ri[2]}";}
		@unlink($file);
		@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
		$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
		return true;
	}	
	
	
	if(preg_match("#<mailto:(.+?)>\s+The recipient's e-mail address was not found#",$datas,$re)){
		
		if(preg_match("#(.+?)@(.+?)>#",$re[1],$ri)){$re[1]="{$ri[1]}@{$ri[2]}";}
		@unlink($file);
		@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
		$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
		return true;
	}	
	
	if(preg_match("#<(.+?)>: host .+?said: 550.+?5\.1\.0\s+Address rejected#",$datas,$re)){
		
		if(preg_match("#(.+?)@(.+?)>#",$re[1],$ri)){$re[1]="{$ri[1]}@{$ri[2]}";}
		@unlink($file);
		@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
		$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
		return true;
	}	

	if(preg_match("#<(.+?)>: Recipient address rejected#",$datas,$re)){
		if(preg_match("#(.+?)@(.+?)>#",$re[1],$ri)){$re[1]="{$ri[1]}@{$ri[2]}";}
		@unlink($file);
		@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
		$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
		return true;
	}	
	
    if(preg_match("#YOU DO NOT NEED TO RESEND YOUR MESSAGE#",$datas,$re)){
    		@imap_delete($GLOBALS["MBXCON"], "$overview->uid:$overview->uid");
    		return;
    	}	
	
	if(preg_match("#Delivery to the following recipients failed\.\s+(.+?)@(.+?)\s+#",$datas,$re)){
		$re[1]=trim("{$re[1]}@{$re[2]}");
		if(preg_match("#(.+?)@(.+?)>#",$re[1],$ri)){$re[1]="{$ri[1]}@{$ri[2]}";}
		@unlink($file);
		@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
		$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
		return true;
	}
	
	if(preg_match("#[0-9]+ [0-9\.]+ SPAM is not accepted here#",$datas,$re)){
		@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
		return true;
	}

	if(preg_match("#<(.+?)>: Relay access denied#",$datas,$re)){
		
		@unlink($file);
		@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
		$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
		return true;
	}	
	
	if(preg_match("#<(.+?)>\.\.\. User unknown#",$datas,$re)){
		@unlink($file);
		@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
		$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
		return true;
	}	
	
	if(preg_match("#after 0 second\(s\):\s+\*\s+(.+?)\s+#",$datas,$re)){
			
			@unlink($file);
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			return true;
		}
	if(preg_match("#<(.+?)>\.\.\. sorry, that domain isn't in my list#",$datas,$re)){
			
			@unlink($file);
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			return true;
		}

	if(preg_match("#Utilisateur.+?\((.+?)\)\s+non recens#",$datas,$re)){
			@unlink($file);
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			return true;
		}
		
	if(preg_match("#<(.+?)>: host\s+.+?\[.+?said: 550 5\.1\.1 User unknown#",$datas,$re)){
			@unlink($file);
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			return true;
		}	
		
	if(preg_match("#RCPT TO: <(.+?)>\s+Received <<< 550 5\.1\.1 User unknown#",$datas,$re)){
			
			@unlink($file);
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			return true;
		}	
		
	if(preg_match("#chec de la remise aux destinataires suivants\.\s+(.+?)\s+#",$datas,$re)){
			
			@unlink($file);
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			return true;
		}	
		
	if(preg_match("#<(.+?)>:\s+[0-9\.]+ does not like recipient\.#",$datas,$re)){
			
			@unlink($file);
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			return true;
		}	
		
	if(preg_match("#<(.+?)>: host.+?\s+said: 550.+?5\.1\.0 Address rejected#",$datas,$re)){
			
			@unlink($file);
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			return true;
		}
	if(preg_match("#<(.+?)>: host.+?said: 550.+?5\.1\.0 Address\s+rejected#",$datas,$re)){
			@unlink($file);
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			return true;
		}	

	if(preg_match("#<(.+?)>: host.+?said: 550 sorry, no\s+mailbox#",$datas,$re)){
			
			@unlink($file);
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			return true;
		}			

	if(preg_match("#<(.+?)>: host .+?said: 553 sorry, that\s+domain isn't#",$datas,$re)){
			@unlink($file);
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			return true;
		}	

		
	if(preg_match("#<(.+?)>: host .+?said: 550 Requested\s+action not taken: mailbox unavailable#",$datas,$re)){
			
			@unlink($file);
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			return true;
		}
	if(preg_match("#<(.+?)> Recipient not allowed#",$datas,$re)){
			@unlink($file);
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			return true;
		}	

	if(preg_match("#<(.+?)>: host .+?said: 550\s+.+?5\.1\.0 Address rejected#",$datas,$re)){
			@unlink($file);
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			return true;
		}

	if(preg_match("#<(.+?)>: host .+?said: 550 Sorry, no mailbox here#",$datas,$re)){
			
			@unlink($file);
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			return true;
		}

	if(preg_match("#<(.+?)>: host .+?said: 550 unrouteable#",$datas,$re)){
			@unlink($file);
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			return true;
		}
		
	if(preg_match("#<(.+?)>: host .+?said: 550 5\.7\.1 Message\s+rejected as spam#",$datas,$re)){
			
			@unlink($file);
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;
		}

	if(preg_match("#Cette identification n'est &agrav=\s+e; faire qu'une seule fois\. Tous vos futurs messages me parviendront direct#",$datas,$re)){
			@unlink($file);
			if(imap_mail_move($GLOBALS["MBXCON"],"$uid:$uid","INBOX/emailing_read")){return true;}else{
				events("INBOX/emailing_read: unable to mode message $uid:$uid ",imap_last_error(),$GLOBALS["ACCOUNT_IMAP"],__LINE__);
			}
			return;
	}
	
	if(preg_match("#enlevez moi de votre mailing list#",$datas,$re)){
			@unlink($file);
			if(imap_mail_move($GLOBALS["MBXCON"],"$uid:$uid","INBOX/emailing_read")){return true;}else{
				events("INBOX/emailing_read: unable to mode message $uid:$uid ",imap_last_error(),$GLOBALS["ACCOUNT_IMAP"],__LINE__);
			}
			return;
	}
	
	if(preg_match("#the fight against spam requires our outside senders to be recognized#",$datas,$re)){
			@unlink($file);
			if(imap_mail_move($GLOBALS["MBXCON"],"$uid:$uid","INBOX/emailing_read")){return true;}else{
				events("INBOX/emailing_read: unable to mode message $uid:$uid ",imap_last_error(),$GLOBALS["ACCOUNT_IMAP"],__LINE__);
			}
			return;
	}
	
	if(preg_match("#Our policy in security and the fight against spam requires.+?outside senders to be recognized#",$datas,$re)){
			@unlink($file);
			if(imap_mail_move($GLOBALS["MBXCON"],"$uid:$uid","INBOX/emailing_read")){return true;}else{
				events("INBOX/emailing_read: unable to mode message $uid:$uid ",imap_last_error(),$GLOBALS["ACCOUNT_IMAP"],__LINE__);
			}
			return;
	}
	
	if(preg_match("#Our policy for security and our willingness to fight against spam messages#",$datas,$re)){
			@unlink($file);
			if(imap_mail_move($GLOBALS["MBXCON"],"$uid:$uid","INBOX/emailing_read")){return true;}else{
				events("INBOX/emailing_read: unable to mode message $uid:$uid ",imap_last_error(),$GLOBALS["ACCOUNT_IMAP"],__LINE__);
			}
			return;
	}
	
	if(preg_match("#Please click the below link to allow your mail to be transmitted#",$datas,$re)){
			@unlink($file);
			if(imap_mail_move($GLOBALS["MBXCON"],"$uid:$uid","INBOX/emailing_read")){return true;}else{
				events("INBOX/emailing_read: unable to mode message $uid:$uid ",imap_last_error(),$GLOBALS["ACCOUNT_IMAP"],__LINE__);
			}
			return;
	}
	
	if(preg_match("#Please click on the following link in order to identify yourself to me and to allow your message to reach me#",$datas,$re)){
			@unlink($file);
			if(imap_mail_move($GLOBALS["MBXCON"],"$uid:$uid","INBOX/emailing_read")){return true;}else{
				events("INBOX/emailing_read: unable to mode message $uid:$uid ",imap_last_error(),$GLOBALS["ACCOUNT_IMAP"],__LINE__);
			}
			return;
	}
	
	if(preg_match("#The address mail of your correspondent changed#",$datas,$re)){
			@unlink($file);
			if(imap_mail_move($GLOBALS["MBXCON"],"$uid:$uid","INBOX/emailing_read")){return true;}else{
				events("INBOX/emailing_read: unable to mode message $uid:$uid ",imap_last_error(),$GLOBALS["ACCOUNT_IMAP"],__LINE__);
			}
			return;
	}
		
		
	if(preg_match("#<(.+?)>: host .+?said: 550\s+5\.1\.1 User unknown#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;
		}	

	if(preg_match("#<mailto:(.+?)>\s+L'adresse de messagerie que vous avez entr#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;
		}

	if(preg_match("#<(.+?)>: host.+?said: 550 5.7.1 Unable\s+to relay for#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;
		}

	if(preg_match("#<(.+?)>:\s+User's Disk Quota Exceeded#",$datas,$re)){
			@unlink($file);
			if(imap_mail_move($GLOBALS["MBXCON"],"$uid:$uid","INBOX/emailing_read")){return true;}else{
				events("INBOX/emailing_read: unable to mode message $uid:$uid ",imap_last_error(),$GLOBALS["ACCOUNT_IMAP"],__LINE__);
			}
			return;
		}
		
	if(preg_match("#Delivery failed: Over quota#",$datas,$re)){
			@unlink($file);
			if(imap_mail_move($GLOBALS["MBXCON"],"$uid:$uid","INBOX/emailing_read")){return true;}else{
				events("INBOX/emailing_read: unable to mode message $uid:$uid ",imap_last_error(),$GLOBALS["ACCOUNT_IMAP"],__LINE__);
			}
			return;
		}		
		
		
	if(preg_match("#Raison : Over quota#",$datas,$re)){
			@unlink($file);
			if(imap_mail_move($GLOBALS["MBXCON"],"$uid:$uid","INBOX/emailing_read")){return true;}else{
				events("INBOX/emailing_read: unable to mode message $uid:$uid ",imap_last_error(),$GLOBALS["ACCOUNT_IMAP"],__LINE__);
			}
			return;
		}
		
	if(preg_match("#the recipients email address has changed#",$datas,$re)){
			@unlink($file);
			if(imap_mail_move($GLOBALS["MBXCON"],"$uid:$uid","INBOX/emailing_read")){return true;}else{
				events("INBOX/emailing_read: unable to mode message $uid:$uid ",imap_last_error(),$GLOBALS["ACCOUNT_IMAP"],__LINE__);
			}
			return;
		}

	if(preg_match("#Please note that I have resigned from#",$datas,$re)){
			@unlink($file);
			if(imap_mail_move($GLOBALS["MBXCON"],"$uid:$uid","INBOX/emailing_read")){return true;}else{
				events("INBOX/emailing_read: unable to mode message $uid:$uid ",imap_last_error(),$GLOBALS["ACCOUNT_IMAP"],__LINE__);
			}
			return;
		}

		
	if(preg_match("#http:\/\/.+?\.mailinblack.com#",$datas,$re)){
			@unlink($file);
			if(imap_mail_move($GLOBALS["MBXCON"],"$uid:$uid","INBOX/emailing_read")){return true;}else{
				events("INBOX/emailing_read: unable to mode message $uid:$uid ",imap_last_error(),$GLOBALS["ACCOUNT_IMAP"],__LINE__);
			}
			return;
		}

	if(preg_match("#Our policy in security and the fight against spam requires our#",$datas,$re)){
			@unlink($file);
			if(imap_mail_move($GLOBALS["MBXCON"],"$uid:$uid","INBOX/emailing_read")){return true;}else{
				events("INBOX/emailing_read: unable to mode message $uid:$uid ",imap_last_error(),$GLOBALS["ACCOUNT_IMAP"],__LINE__);
			}
			return;
		}
		
		if(preg_match("#Veuillez.+?confirmer.+?votre envoi.+?en cliquant sur le lien ci-dessous#",$datas,$re)){
			@unlink($file);
			if(imap_mail_move($GLOBALS["MBXCON"],"$uid:$uid","INBOX/emailing_read")){return true;}else{
				events("INBOX/emailing_read: unable to mode message $uid:$uid ",imap_last_error(),$GLOBALS["ACCOUNT_IMAP"],__LINE__);
			}
			return;
		}		
		
		if(preg_match("#This mailbox is not used anymore. For any urgent matters, please contact#",$datas,$re)){
			@unlink($file);
			if(imap_mail_move($GLOBALS["MBXCON"],"$uid:$uid","INBOX/emailing_read")){return true;}else{
				events("INBOX/emailing_read: unable to mode message $uid:$uid ",imap_last_error(),$GLOBALS["ACCOUNT_IMAP"],__LINE__);
			}
			return;
		}
		
		if(preg_match("#You can now reach following receipients#",$datas,$re)){
			@unlink($file);
			if(imap_mail_move($GLOBALS["MBXCON"],"$uid:$uid","INBOX/emailing_read")){return true;}else{
				events("INBOX/emailing_read: unable to mode message $uid:$uid ",imap_last_error(),$GLOBALS["ACCOUNT_IMAP"],__LINE__);
			}
			return;
		}
		
		if(preg_match("#is blocked in my spam folder awaiting your authentication#",$datas,$re)){
			@unlink($file);
			if(imap_mail_move($GLOBALS["MBXCON"],"$uid:$uid","INBOX/emailing_read")){return true;}else{
				events("INBOX/emailing_read: unable to mode message $uid:$uid ",imap_last_error(),$GLOBALS["ACCOUNT_IMAP"],__LINE__);
			}
			return;
		}		

		

	if(preg_match("#<mailto:(.+?)>\s+L'adresse de messagerie de ce destinataire est introuvable#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;
		}		
	
	if(preg_match("#Reason: content policy violation#",$datas,$re)){
		@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
		@unlink($file);
		return true;
	}
	
	if(preg_match("#Out of Office AutoReply#",$datas,$re)){
		@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
		@unlink($file);
		return true;
	}
	
	if(preg_match("#I am away until#",$datas,$re)){
		@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
		@unlink($file);
		return true;
	}
	
	if(preg_match("#I am out of office until#",$datas,$re)){
		@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
		@unlink($file);
		return true;
	}
	
	if(preg_match("#Je suis absent et serai#",$datas,$re)){
		@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
		@unlink($file);
		return true;
	}
	
	if(preg_match("#I am travelling till#",$datas,$re)){
		@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
		@unlink($file);
		return true;
	}
	
	if(preg_match("#I will be out of the office#",$datas,$re)){
		@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
		@unlink($file);
		return true;
	}
	
	if(preg_match("#Votre message est bien arriv=E9#",$datas,$re)){
		@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
		@unlink($file);
		return true;
	}
	
	if(preg_match("#I am out of the office until#",$datas,$re)){
		@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
		@unlink($file);
		return true;
	}
	
	if(preg_match("#Je serai absent\(e\)#",$datas,$re)){
		@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
		@unlink($file);
		return true;
	}
	
	if(preg_match("#I AM BACK ON#",$datas,$re)){
		@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
		@unlink($file);
		return true;
	}
	
	if(preg_match("#Je suis en cong.+?jusqu#",$datas,$re)){
		@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
		@unlink($file);
		return true;
	}
	
	if(preg_match("#Je suis en cong=E9s jusqu#",$datas,$re)){
		@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
		@unlink($file);
		return true;
	}
	
	if(preg_match("#Je suis absent\(e\) du bureau jusqu#",$datas,$re)){
		@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
		@unlink($file);
		return true;
	}
	
	if(preg_match("#I'm out of office until#",$datas,$re)){
		@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
		@unlink($file);
		return true;
	}
	if(preg_match("#R=E9ponse_automatique_d=27absence_du_bureau#",$datas,$re)){
		@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
		@unlink($file);
		return true;
	}
	
	if(preg_match("#Error writing message to safe storage#",$datas,$re)){
		@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
		@unlink($file);
		return true;
	}
	
	if(preg_match("#could not be stored to disk#",$datas,$re)){
		@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
		@unlink($file);
		return true;
	}
	
	if(preg_match("#<(.+?)>: host .+?said: 571\s+.+?prohibited#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;
		}

	if(preg_match("#failed:\s+(.+?)\s+retry time not reached#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;
		}
		
	if(preg_match("#<(.+?)>: host .+?said: 550 5\.2\.1 This\s+mailbox has been blocked due to inactivity#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;
		}		

	if(preg_match("#Final-recipient: rfc822; (.+?)\s+Action: failed\s+Status: 5\.1\.1#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;
		}	

	if(preg_match("#<(.+?)>\.\.\. User is unknown#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;
		}

	if(preg_match("#<(.+?)>: host .+?said: 501 5\.5\.4\s+Unrecognized parameter#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;
		}

	if(preg_match("#<(.+?)>: host .+?said:\s+550.+?5\.1\.0 Address rejected#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;
		}

	if(preg_match("#<(.+?)>: host\s+.+?said: 550-Invalid recipient#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;
		}

	if(preg_match("#\s+(.+?)@(.+?)\s+.+?550 5\.1\.1 RESOLVER\.ADR\.RecipNotFound; not found#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]@$re[2]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;
		} 

	if(preg_match("#<(.+?)> was not found in#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;
		}

	if(preg_match("#<(.+?)>: host .+?said: 550\s+5\.4\.1 Relay Access Denied#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;
		} 

	if(preg_match("#<(.+?)>: host.+?said:\s+550-Callout verification failed#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;
		} 

	if(preg_match("#expanded from <(.+?)>\): Host\s+or domain name not found\.#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;
		}

	if(preg_match("#<(.+?)>: Name service error for#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;
		}	

	if(preg_match("#<(.+?)>: host.+?said:\s+520 5\.2\.1 Mailbox Inactive#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;
		}
		
	if(preg_match("#<(.+?)>: host.+?said:\s+550 sorry, no mailbox here#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;
		}		

	if(preg_match("#<(.+?)>.+?: maildir\s+delivery failed: Sorry#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;
		}
	if(preg_match("#Final-Recipient: rfc822;(.+?)\s+Action: failed\s+Status: 5\.1\.1#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;
		}

	if(preg_match("#Final-Recipient: rfc822;(.+?)\s+Action: failed\s+Status: [0-9\.]+#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;
		}		

		
	if(preg_match("#<(.+?)> recipient rejected#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;
		}
		
	if(preg_match("#<(.+?)>: [0-9]+.+?\.\.\. No such user#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;
		}

	if(preg_match("#<(.+?)>: host .+?said: [0-9]+ No such\s+user#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;
		}

	if(preg_match("#<(.+?)>: host .+?said: [0-9]+\s+\"Unknown User#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;
		}

	if(preg_match("#<(.+?)>:\s+host.+?said: [0-9]+\s+Requested action not taken#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;
		}

	if(preg_match("#<(.+?)> is not a valid mailbox#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;
		}
		
	if(preg_match("#<(.+?)>: host.+?said:\s+[0-9]+ No relaying allowed#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;
		}

	if(preg_match("#<(.+?)>.+?Mailbox does not exist#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;
		}	

	if(preg_match("#<(.+?)>: host.+?said: [0-9]+ Recipient\s+address rejected#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;
		}

	if(preg_match("#<(.+?)>: host.+?said: [0-9]+\s+.+?is not a known user#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;
		} 
		
	if(preg_match("#<(.+?)>: host.+?said: [0-9]+ sorry,#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;
		} 		
		
	if(preg_match("#<(.+?)>: host.+?said:\s+[0-9]+ Invalid recipient#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;
		} 		

		

	if(preg_match("#<(.+?)>: host.+?said: [0-9]+\s+.+?unroutable#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;
		} 

		
if(preg_match("#<(.+?)>: host .+?said:\s+.+?User unknow#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;
		} 
		
		

	 if(preg_match("#<(.+?)>: invalid address#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;
		}
		
	if(preg_match("#<(.+?)>: host .+?said: [0-9]+ unroutable#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;
		}		

	if(preg_match("#[0-9]+ [0-9\.]+\s+(.+?)\.\.\. User unknown#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;
		}
		
	if(preg_match("#<(.+?)>, Recipient unknown#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
	}

	if(preg_match("#<(.+?)>: host .+?\s+said:.+?No such user#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
	}	
	
	if(preg_match("#<(.+?)>: host .+said: [0-9]+\s+.+?Recipient address rejected#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
	}	

	if(preg_match("#\s+(.+?)\s+- no such user here\.#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
	}	
	
	if(preg_match("#<(.+?)>: host .+?said: [0-9]+\s+delivery error#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
	}

	if(preg_match("#<(.+?)>: host .+?said:\s+[0-9\-\.]+.+?does not exist#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}	
	
	if(preg_match("#<(.+?)>: host .+?said: [0-9]+ [0-9\.]+ This\s+.+?not configured to#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}

	if(preg_match("#<(.+?)>: host .+?said: [0-9]+ Sorry#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}
			
	if(preg_match("#following:\s+(.+?) \(user not found#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}			
	
	if(preg_match("#Original-Recipient: rfc822;(.+?)\s+Action: failed#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}

	if(preg_match("#failed:\s+(.+?)\s+retry timeout exceeded#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}
			
	if(preg_match("#<(.+?)>:\s+user does not exist#",$datas,$re)){
			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}			

	if(preg_match("#failed:\s+(.+?)\s+Unrouteable address#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}			


		if(preg_match("#Delevery to the following recipients failed permanently:\s+\* (.+?)\s+#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}	

		if(preg_match("#Your message could not be delivered to (.+?)\s+#",$datas,$re)){			
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}	

		if(preg_match("#Recipient address: (.+?)\s+Reason: Remote SMTP server has rejected address#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}	

		if(preg_match("#had permanent fatal errors .+?\s+<(.+?)>#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}		

		if(preg_match("#Could not deliver mail to this user\.\s+(.+?)\s+#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}	

		if(preg_match("#Original-Recipient: <(.+?)>\s+Action: failed#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}	

		if(preg_match("#Your mail could not be delivered to the following address\(es\):\s+(.+?)\s+#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}	

		if(preg_match("#Recipient address: (.+?)\s+Reason: Not found in directory#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}
			
		if(preg_match("#L'adresse \"(.+?)\" n'existe plus\.#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}

			
		if(preg_match("#Final-Recipient: rfc822;<(.+?)>\s+Diagnostic-Code: [0-9]+\s+Action: failed#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}		

			
		if(preg_match("#Your message\s+To:\s+(.+?)\s+.+?did not reach the following recipient#s",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;
		}	
			
		if(preg_match("#User unknown.+?X-Deliver-To: (.+?)\s+#s",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}

		if(preg_match("#delivery problems\s+<(.+?)>	Message exceeded maximum hop count#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}	

		if(preg_match("#<(.+?)>:\s+Sorry, no mailbox here#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}	

		if(preg_match("#delivery problems\s+<(.+?)>#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}	
			
		if(preg_match("#<(.+?)>:\s+Cette adresse mail n'existe pas#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}	
			
		if(preg_match("#L'adresse (.+?) n'est plus active#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}

		if(preg_match("#Returned mail: unreachable recipients:(.+?)\s+#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}
			
		if(preg_match("#failed:\s+(.+?)\s+mailbox is full#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}

		if(preg_match("#failed:\s+(.+?)\s+\(.+?\s+Unrouteable address#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}	

		if(preg_match("#did not receive this message:\s+<(.+?)>\s+Please reply#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}

		if(preg_match("#<(.+?)>: host .+?said: [0-9]+\s+.+?No such user#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}		

		if(preg_match("#The following address\(es\) failed:\s+(.+?)\s+#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}		

  		if(preg_match("#Unable to deliver mail.+?recipients.\s+(.+?)\s+#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}		                        
 
		if(preg_match("#out\.\s+<(.+?)>:\s+user is over quota#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}

		if(preg_match("#<(.+?)>:\s+Mailaddress is administratively disabled#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}			

		if(preg_match("#Final-recipient: rfc822;(.+?)\s+Action: failed\s+Status: [0-9\.]+ \(Over quota#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}	
			
		if(preg_match("#domain '(.+?)' is not an Email domain#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}	
			
		if(preg_match("#Unknown address error [0-9]+.+?'(.+?)\.\.\. No such user#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}				

		if(preg_match("#The following message to <(.+?)> was undeliverable#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}				
		
		if(preg_match("#<(.+?)>:\s+L'adresse email entr.+?e est inexistante ou erron.+?#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}

		if(preg_match("#Final-Recipient: rfc822;(.+?)\s+Diagnostic-Code: smtp; [0-9]+ Requested action not taken: mailbox unavailable#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}	

		if(preg_match("#DELIVERY FAILURE: User .+? \((.+?)\)#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}		

		if(preg_match("#Failed to deliver to '<(.+?)>'#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}	

		if(preg_match("#Could not find a gateway for (.+?)\s+#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}	
			
		if(preg_match("#<(.+?)>: [0-9]+ No such recipient#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}		

		if(preg_match("#<(.+?)>: [0-9]+ [0-9\.]+ Hop count exceeded#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}	

		if(preg_match("#<(.+?)>: [0-9]+ Invalid recipient#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}	
			
		if(preg_match("#failed permanently:\s+(.+?)\s+Technical#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}	
			
		if(preg_match("#<(.+?)>: host .+?said: [0-9]+\s+[0-9\.]+ Message contains invalid header#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}				

     	if(preg_match("#smtp;[0-9]+ Mailbox unavailable or access denied - <(.+?)>#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}				

		if(preg_match("#<(.+?)>:\s+.+?fatal: Sorry, I don't accept#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}	

		if(preg_match("#Diagnostic-Code: smtp; [0-9]+ (.+?)\.\.\. No such user#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}	
			
		if(preg_match("#Original-Recipient: rfc822;<(.+?)>\s+Final-Recipient.+?\s+Action: failed#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}	

		if(preg_match("#Unable to deliver message to <(.+?)>\.#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}

		if(preg_match("#Originally addressed to (.+?)\)\s+User not known#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}	

		if(preg_match("#Final-Recipient: rfc822;(.+?)\s+Diagnostic-Code: smtp;[0-9]+ User unknown#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}	

		if(preg_match("#RCPT To:<(.+?)>\s+.+?[0-9]+ [0-9\.]+ User unknown#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}	

		if(preg_match("#The email address (.+?) \(and (.+?)\) is no longer in use#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			$GLOBALS["FOUND_BAD_MAIL"]["$re[2]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}	

		if(preg_match("#Unable to deliver message to <(.+?)>#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}	

		if(preg_match("#Final-Recipient: rfc822;(.+?)\s+Diagnostic-Code: smtp; [0-9\.]+ None of the mail servers#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}	

		if(preg_match("#[0-9\.]+, '(.+?)\.\.\. No such user#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}	

		if(preg_match("#Final-Recipient: RFC822; (.+?)\s+Action: failed#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}	

		if(preg_match("#X-Greylist: Delayed for [0-9]+:[0-9]+:[0-9]+#",$datas,$re)){
			@unlink($file);
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}	
			
		if(preg_match("#Your message to <(.+?)> was automatically rejected:\s+Quota exceeded#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}	
		if(preg_match("#<(.+?)>: [0-9]+ [0-9\.]+ User unknown#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}

		if(preg_match("#Invalid final delivery userid: (.+?)\s+#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}		
		
		if(preg_match("#<(.+?)>:\s+vdeliver: Invalid or unknown#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}	

		if(preg_match("#<(.+?)>, sorry#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}	
		if(preg_match("#<(.+?)>:\s+.+?:.+?I'm sorry#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}	

		if(preg_match("#<(.+?)>:\s+Sorry,#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}

		if(preg_match("#<(.+?)>:\s+The users mailfolder#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}
			
		if(preg_match("#Final-Recipient: RFC822; (.+?)\s+X-Actual-Recipient.+?\s+Action: failed#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}		

		if(preg_match("#Remote-Recipient: rfc822;<(.+?)>\s+Diagnostic-Code: smtp;[0-9]+ SMTP-Deliver:QueuedTooLong#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}					

		if(preg_match("#<(.+?)>:\s+Unable to chdir to maildir#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}			

		if(preg_match("#X-TM-AS-User-Approved-Sender: No.+?To: (.+?)\s+From:#is",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}		

		if(preg_match("#\s+(.+?)\s+Error Type: SMTP#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}	
		if(preg_match("#<(.+?)>:\s+Cet adresse email n'existe pas#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}	 								
		if(preg_match("#<(.+?)>:\s+[0-9\.]+ failed after I sent the message#",$datas,$re)){
			@unlink($file);
			$GLOBALS["FOUND_BAD_MAIL"]["$re[1]"]=true;
			@imap_delete($GLOBALS["MBXCON"], "$uid:$uid");
			return true;	
			}	 								
			
	echo "NOT FOUND  message $uid $file \"$subject\"\n";
	return false;
	
}



function events($subject,$text,$account,$line){
	$subject=$subject." line $line";
	$text=addslashes($text);
	$subject=addslashes($subject);
	writelogs("$account:: $subject $text",__FUNCTION__,__FILE__,__LINE__);	
	
	$sql="INSERT INTO emailing_campain_imap_events (`zDate`,`subject`,`content`,`account`)
	VALUES(NOW(),'$subject','$text','$account')";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){writelogs("$q->mysql_error",__FUNCTION__,__FILE__,__LINE__);}
}

function CleanDatabases_perf(){
	
	$sql="SELECT databasename FROM emailing_db_paths WHERE merged=0";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){writelogs("$q->mysql_error",__FUNCTION__,__FILE__,__LINE__);return;}	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
		$db["emailing_{$ligne["databasename"]}"]="emailing_{$ligne["databasename"]}";
	}
	
	if(count($db)==0){return;}
	
	$sql="SELECT email FROM emailing_campain_blacklist";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){echo ("$q->mysql_error");}
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
		$email=$ligne["email"];
		while (list ($tablename, $none) = each ($db) ){
			$q->QUERY_SQL("DELETE FROM $tablename WHERE email='$email'","artica_backup");
		}
		reset($db);
		
	}	
	
	
	
}
