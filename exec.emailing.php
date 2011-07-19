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


if($argv[1]=='--build-queues'){build_queues($argv[2]);die();}
if($argv[1]=='--build-single-queue'){construct_queue($argv[2],$argv[3]);die();}


function build_queues($ou){
	if(is_base64_encoded($ou)){$ou=base64_decode($ou);}
	event(__FUNCTION__,__LINE__,"Checking $ou organization");
	$sql="SELECT ID FROM emailing_campain_linker WHERE ou='$ou' AND queue_builder_pourc=0 ORDER BY ID DESC";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		event(__FUNCTION__,__LINE__,"Checking campaign N.{$ligne["ID"]}");
		$ID=$ligne["ID"];construct_queue($ID,$ou);
	}
}

function isBlockedMail($email){
	$q=new mysql();
	$email=trim(strtolower($email));
	$sql="SELECT email FROM emailing_campain_blacklist WHERE email='$email'";
	$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
	if($ligne["email"]<>null){return true;}
	return false;
}


function construct_queue($ID,$ou){
	$q=new mysql();
	$GLOBALS["campain_linker_id"]=$ID;
	$q->QUERY_SQL("UPDATE `emailing_campain_linker` SET `queue_builder_pourc`=0 `locked`=1 WHERE `ID`={$GLOBALS["campain_linker_id"]}","artica_backup");
	$sql="SELECT * FROM emailing_campain_linker WHERE ID='$ID'";
	$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
	$unix=new unix();
	$email_relay_submit=$unix->find_program("emailrelay-submit");
	$GLOBALS["SartON"]=time();
	
	if($email_relay_submit==null){
		event(__FUNCTION__,__LINE__,"Unable to stat $email_relay_submit");
		mass_mailing_finish(0);
		return;
	}
	
	$parameters=unserialize(base64_decode($ligne["parameters"]));	
	$template_id=$ligne["template_id"];
	$database_id=$ligne["database_id"];
	$GLOBALS["template_id"]=$template_id;
	
	$GLOBALS["database_id"]=$database_id;
	
	
	
	$simulation=$parameters["simulation"];
	$GLOBALS["start_date_task"]=date('Y-m-d H:i:s');
	
	
	$simulation_recipipent=$parameters["recipient"];
	$database_name=emailing_get_database_name($database_id);
	event(__FUNCTION__,__LINE__,"database name=$database_name");
	$database_name=format_mysql_table($database_name);
	$template_name=emailing_get_template_name($template_id);
	$template_parameters=emailing_get_template_parameters($template_id);
	event(__FUNCTION__,__LINE__,"Opening $ID {$ligne["name"]} campaign template $template_name N.$template_id, database $database_name N.$database_id");
	event(__FUNCTION__,__LINE__,"simulation=$simulation =>$simulation_recipipent");
	
	@mkdir("/tmp/emailing/$ID",0666,true);
	if(is_array($template_parameters["ATTACHS"])){
		while (list ($filename, $attch_ID) = each ($template_parameters["ATTACHS"])){
			event(__FUNCTION__,__LINE__,"saving /tmp/emailing/$ID/$filename");
			emailing_save_attachment($attch_ID,"/tmp/emailing/$ID/$filename");
			
	}reset($template_parameters["ATTACHS"]);}
	
	$advanced_options=unserialize(base64_decode($template_parameters["advopts"]));
	if($advanced_options["From_name"]==null){$advanced_options["From_name"]=$advanced_options["From"];}
	if($advanced_options["From"]==null){
		event(__FUNCTION__,__LINE__,"From field is null, aborting");
		mass_mailing_finish(0);
		return false;
	}
	
	
	$sql="SELECT ID,smtpserver,parameters FROM emailing_mailers WHERE ou='$ou'";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$datas=unserialize(base64_decode($ligne["parameters"]));
		$smtp_servs[]=$ligne["ID"];	
	}
	
	
	if(!is_array($smtp_servs)){
		event(__FUNCTION__,__LINE__,"Unable to define SMTP servers");
		mass_mailing_finish(0);
		return false;
	}

	
	$sql="SELECT * FROM emailing_$database_name";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	$t=0;
	$max=mysql_num_rows($results);
	event(__FUNCTION__,__LINE__,"$max contacts in emailing_$database_name table");
	$smtp_servs_count=0;
	$smtp_servs_max=count($smtp_servs)-1;
	$messages_number=0;
	event(__FUNCTION__,__LINE__,$smtp_servs_max+1 ." SMTP servers");
	

	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$final_count=$final_count+1;
		$contact_id=$ligne["ID"];
		$gender=$ligne["gender"]; 	
		$firstname=$ligne["firstname"];
		$lastname=$ligne["lastname"];
		$email=$ligne["email"];
		if(isBlockedMail($email)){continue;}
		$phone=$ligne["phone"];
		$city=$ligne["city"];
		$cp=$ligne["cp"];
		$postaladdress=$ligne["postaladdress"];
		if($simulation==1){$email=$simulation_recipipent;}
		$unikey=md5("$template_id$database_id$contact_id");
		$htmldatas=$template_parameters["template_datas"];
		$htmldatas=str_replace("%gender%",$gender,$htmldatas);
		$htmldatas=str_replace("%lastname%",$lastname,$htmldatas);
		$htmldatas=str_replace("%firstname%",$firstname,$htmldatas);
		$htmldatas=str_replace("%email%",$email,$htmldatas);
		$htmldatas=str_replace("%phone%",$phone,$htmldatas);
		$htmldatas=str_replace("%city%",$city,$htmldatas);
		$htmldatas=str_replace("%cp%",$cp,$htmldatas);
		$htmldatas=str_replace("%postaladdress%",$postaladdress,$htmldatas);
		
		$mailer=new PHPMailer();
		$mailer->AddAddress($email,"$firstname $lastname");
		$mailer->From=$advanced_options["From"];
		$mailer->FromName=$advanced_options["From_name"];
		
		
		$mailer->Subject=$template_parameters["subject"]; 
		$mailer->Body=$htmldatas;
		$mailer->IsHTML(true);
		$mailer->Mailer='smtp';
		
		
		if($advanced_options["Reply-to"]<>null){$mailer->AddReplyTo($advanced_options["Reply-to"]);}
		if($advanced_options["Disposition-Notification-To"]<>null){$mailer->AddCustomHeader("Disposition-Notification-To: {$advanced_options["Disposition-Notification-To"]}");}
		if($advanced_options["Return-Path"]<>null){$mailer->AddCustomHeader("Return-Path: {$advanced_options["Return-Path"]}");}
		if($advanced_options["X-Mailer"]<>null){$mailer->AddCustomHeader("X-Mailer: {$advanced_options["X-Mailer"]}");}		
		
		if(is_array($template_parameters["ATTACHS"])){
			while (list ($filename, $attch_ID) = each ($template_parameters["ATTACHS"])){$mailer->AddAttachment("/tmp/emailing/$ID/$filename",$filename);}
			reset($template_parameters["ATTACHS"]);
		}
	
		
		$queue_ID="/var/spool/artica-emailing/queues/{$smtp_servs[$smtp_servs_count]}";

		$smtp_servs_count=$smtp_servs_count+1;
		if($smtp_servs_count>$smtp_servs_max){$smtp_servs_count=0;}
		
		$maildata=$mailer->Send(true);
		
		
		// send mail into queue
		
		$mail_from="{$advanced_options["From"]}";
		$recipient=$email;
		$filetemp=$unix->FILE_TEMP()."$contact_id.msg";
		@file_put_contents($filetemp,$maildata);
		$GLOBALS["massmailing_size"]=$GLOBALS["massmailing_size"]+filesize($filetemp);
		
		
		$cmd="$email_relay_submit --spool-dir=$queue_ID --from=$mail_from $recipient < $filetemp";
		if($GLOBALS["VERBOSE"]){event(__FUNCTION__,__LINE__,"$cmd");}
		shell_exec("$email_relay_submit --spool-dir=$queue_ID --from=$mail_from $recipient < $filetemp");
		if($GLOBALS["VERBOSE"]){event(__FUNCTION__,__LINE__,@implode("\n",$results));}
		@unlink($filetemp);
		$maildata=null;
		$mailer=nil;
		$GLOBALS["messages_number"]=$GLOBALS["messages_number"]+1;
		
		
		$t=$t+1;

		if($t>200){
			$pourcentage=round(($final_count/$max)*100,0);
			echo "$pourcentage% $final_count/$max\n";
			$q=new mysql();
			event(__FUNCTION__,__LINE__,"$pourcentage%");
			$q->QUERY_SQL("UPDATE emailing_campain_linker SET queue_builder_pourc=$pourcentage WHERE ID=$ID","artica_backup");
			$t=0;			
		}
			
	}
	
	mass_mailing_finish(1);
	
	
}



function event($function,$line,$text){
	writelogs("$text",$function,__FILE__,$line);
	$GLOBALS["EVENTS_DETAILS"][]="[$function]:$line: $text";
	echo "[$function]:$line: $text\n";
	
}

function mass_mailing_finish($task_success){
	$SartOff=time();
	$q=new mysql();
	$q->check_emailing_tables();
	
	$time_duration=distanceOfTimeInWords($GLOBALS["SartON"],$SartOff);
	$logs=@implode("\n",$GLOBALS["EVENTS_DETAILS"]);
	
	unset($GLOBALS["EVENTS_DETAILS"]);
	
	
	event(__FUNCTION__,__LINE__,"Building queue finish");
	$q->QUERY_SQL("UPDATE `emailing_campain_linker` SET `queue_builder_pourc`=100,`locked`=0 WHERE `ID`={$GLOBALS["campain_linker_id"]}","artica_backup");
	
	if(!$q->ok){event(__FUNCTION__,__LINE__,$q->mysql_error);}
	
	$sql="INSERT INTO `emailing_campain_events` (`campain_linker_id`,`template_id`,`database_id`,`zDate`,
	`time_duration`,
	`messages_number`,
	`massmailing_size`,
	`events_details`,
	`task_success`)
	VALUES('{$GLOBALS["campain_linker_id"]}','{$GLOBALS["template_id"]}','{$GLOBALS["database_id"]}','{$GLOBALS["start_date_task"]}',
	'$time_duration',
	'{$GLOBALS["messages_number"]}',
	'{$GLOBALS["massmailing_size"]}',
	'$logs',
	'$task_success'
	)";
	
	$q->QUERY_SQL($sql,"artica_backup");
	
	if(!$q->ok){event(__FUNCTION__,__LINE__,$q->mysql_error."\n\n$sql\n\n");}
	$GLOBALS["messages_number"]=0;
	$GLOBALS["start_date_task"]=null;
	$GLOBALS["massmailing_size"]=0;
	
	
}

?>