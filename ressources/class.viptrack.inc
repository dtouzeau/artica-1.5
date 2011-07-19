<?php
include_once(dirname(__FILE__).'/class.mysql.inc');
class viptrack{
	var $email;
	var $LastHours=72;
	var $CountDeMessagesSent=0;
	var $CountDeBadMessagesSent=0;
	var $CountDeMessagesReceive=0;
	var $CountDeBadMessagesReceive=0;
	var $Quarantined=0;

	
	function viptrack($email){
		$this->email=$email;
		$sock=new sockets();
		$VIPTrackLastHour=$sock->GET_INFO("VIPTrackLastHour");
		if(!is_numeric($VIPTrackLastHour)){$VIPTrackLastHour="24";}
		$this->LastHours=$VIPTrackLastHour;
		
		
		if($GLOBALS["VERBOSE"]){echo "viptrack():: last $this->LastHours Hours\n";}
		$this->CountDeMessagesSended();
		$this->CountDeMessagesReceived();
		$this->CountDeBadMessagesSended();
		$this->CountDeBadMessagesReceived();
		$this->CountDeQuarantine();
		$total=0;
		$total=$this->CountDeBadMessagesSent+$this->CountDeBadMessagesReceive+$this->Quarantined;
		if($GLOBALS["VERBOSE"]){echo "$this->email:: total:$total\n";}
		if($total>0){
			$this->build_report();
		}
	}
	
	
	private function CountDeMessagesSended(){
		$sql="SELECT COUNT(zmd5) as tcount FROM viptrack_connections WHERE `bounce_error`='Sended' 
		AND `from`='$this->email'
		AND zDate>=DATE_SUB(NOW(),INTERVAL $this->LastHours HOUR)";
		$q=new mysql();		
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_events"));
		if(!$q->ok){if($GLOBALS["VERBOSE"]){echo "$this->email:: CountDeMessagesSended() error $q->mysql_error\n";}}
		$this->CountDeMessagesSent=$ligne["tcount"];
		if($GLOBALS["VERBOSE"]){echo "$this->email:: Sent: $this->CountDeMessagesSent\n";}
		
	}
	
	private function CountDeMessagesReceived(){
		$sql="SELECT COUNT(zmd5) as tcount FROM viptrack_connections WHERE `bounce_error`='Sended' 
		AND `to`='$this->email'
		AND zDate>=DATE_SUB(NOW(),INTERVAL $this->LastHours HOUR)";
		$q=new mysql();		
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_events"));
		if(!$q->ok){if($GLOBALS["VERBOSE"]){echo "$this->email:: CountDeMessagesReceived() error $q->mysql_error\n";}}
		$this->CountDeMessagesReceive=$ligne["tcount"];
		if($GLOBALS["VERBOSE"]){echo "$this->email:: Receive: $this->CountDeMessagesReceive\n";}
		
	}	
	
	private function CountDeBadMessagesSended(){
		$sql="SELECT COUNT(zmd5) as tcount FROM viptrack_connections WHERE `bounce_error`!='Sended' 
		AND `from`='$this->email'
		AND zDate>=DATE_SUB(NOW(),INTERVAL $this->LastHours HOUR)";
		$q=new mysql();		
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_events"));
		if(!$q->ok){if($GLOBALS["VERBOSE"]){echo "$this->email:: ".__FUNCTION__." error $q->mysql_error\n";}}
		$this->CountDeBadMessagesSent=$ligne["tcount"];
		if($GLOBALS["VERBOSE"]){echo "$this->email:: Bad Sent: $this->CountDeBadMessagesSent\n";}
	}
	
	private function CountDeBadMessagesReceived(){
		$sql="SELECT COUNT(zmd5) as tcount FROM viptrack_connections WHERE `bounce_error`!='Sended' 
		AND `to`='$this->email'
		AND zDate>=DATE_SUB(NOW(),INTERVAL $this->LastHours HOUR)";
		$q=new mysql();		
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_events"));
		if(!$q->ok){if($GLOBALS["VERBOSE"]){echo "$this->email:: ".__FUNCTION__." error $q->mysql_error\n";}}
		$this->CountDeBadMessagesReceive=$ligne["tcount"];
		//if($GLOBALS["VERBOSE"]){echo "$this->email:: Bad Receive: $this->CountDeBadMessagesReceive\n";}
		
		$sql="SELECT COUNT(zmd5) as tcount FROM viptrack_content WHERE `bounce_error`!='PASS' 
		AND `to`='$this->email'
		AND zDate>=DATE_SUB(NOW(),INTERVAL $this->LastHours HOUR)";	
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_events"));
		if(!$q->ok){if($GLOBALS["VERBOSE"]){echo "$this->email:: ".__FUNCTION__." error $q->mysql_error\n";}}
		$this->CountDeBadMessagesReceive=$this->CountDeBadMessagesReceive+$ligne["tcount"];
		if($GLOBALS["VERBOSE"]){echo "$this->email:: Bad Receive: $this->CountDeBadMessagesReceive\n";}			
		
	}

	private function CountDeQuarantine(){
		$sql="SELECT COUNT(MessageID) as tcount FROM quarantine WHERE `mailto`='$this->email'
		AND zDate>=DATE_SUB(NOW(),INTERVAL $this->LastHours HOUR)";
		$q=new mysql();		
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
		if(!$q->ok){if($GLOBALS["VERBOSE"]){echo "$this->email:: ".__FUNCTION__." error $q->mysql_error\n";}}
		$this->Quarantined=$ligne["tcount"];
		if($GLOBALS["VERBOSE"]){echo "$this->email:: quarantine: $this->Quarantined\n";}
	}

	private function build_report(){
		$text="Here it is report from the messaging server for $this->email since last $this->LastHours Hour(s)\n";
		if($this->CountDeBadMessagesSent>0){
			$subject[]="$this->CountDeBadMessagesSent Sent blocked";
			$text=$text.$this->BadMessagesSentDetails();
		}
		
		if($this->CountDeBadMessagesReceive>0){
			$subject[]="$this->CountDeBadMessagesReceive Receive blocked";
			$text=$text.$this->BadMessagesReceiveDetails();
		}	

		if($this->Quarantined>0){
			$subject[]="$this->Quarantined in quarantine";
			$text=$text.$this->QuarantinedDetails();
		}		
		
		$subject="[VIP] $this->email ".@implode(",",$subject);
		$unix=new unix();
		if(!$GLOBALS["VERBOSE"]){$unix->send_email_events($subject,$text,"VIPTrack");}

		if($GLOBALS["VERBOSE"]){echo "\n\n--------------------------------\n$subject\n$text\n";}
	}
	
	private function BadMessagesSentDetails(){
		$text=array();
		$sql="SELECT *,DATE_FORMAT(zDate,'%W %d %M %H:%i') as tdate FROM viptrack_connections WHERE `bounce_error`!='Sended' AND `from`='$this->email'AND zDate>=DATE_SUB(NOW(),INTERVAL $this->LastHours HOUR) ORDER BY zDate DESC";	
		$q=new mysql();	
		$results=$q->QUERY_SQL($sql,"artica_events");
		if(!$q->ok){if($GLOBALS["VERBOSE"]){echo "$this->email:: ".__FUNCTION__." error $q->mysql_error\n";}}
		$count=mysql_num_rows($results);
		if($count>0){
			$aa=0;
			$text[]="";	
			$text[]="";				
			$text[]="$count refused `Sended` Messages (connections)";
			$text[]="----------------------------------------------";		
			
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				if($ligne["bounce_error"]=="WHITELISTED"){continue;}
				$aa=0;
				$text[]="{$ligne["bounce_error"]} on {$ligne["tdate"]} for {$ligne["to"]}";
			}
			$text[]='';
		}
		
		$sql="SELECT *,DATE_FORMAT(zDate,'%W %d %M %H:%i') as tdate FROM viptrack_content WHERE `bounce_error`!='PASS' AND `from`='$this->email'AND zDate>=DATE_SUB(NOW(),INTERVAL $this->LastHours HOUR) ORDER BY zDate DESC";
		$results=$q->QUERY_SQL($sql,"artica_events");
		if(!$q->ok){if($GLOBALS["VERBOSE"]){echo "$this->email:: ".__FUNCTION__." error $q->mysql_error\n";}}
		$count=mysql_num_rows($results);
		if($count>0){
			mb_internal_encoding("UTF-8");
						
			$aa=0;
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				if($ligne["bounce_error"]=="WHITELISTED"){continue;}
				$aa++;
				$ligne["subject"] = mb_decode_mimeheader($ligne["subject"]); 	
				$text2[]="{$ligne["bounce_error"]} on {$ligne["tdate"]} for {$ligne["to"]}";
				$text2[]="Subject: {$ligne["subject"]}";
				$text2[]="";
			}
		
		
		}
		if($aa==0){return;}
			$text[]="";	
			$text[]="";				
			$text[]="$aa refused `Sended` Messages (content filters)";
			$text[]="----------------------------------------------";	
			$text[]=@implode("\n",$text2);
			

		return @implode("\n",$text);
		
		
	}
	
	private function BadMessagesReceiveDetails(){
		$text=array();
		$sql="SELECT *,DATE_FORMAT(zDate,'%W %d %M %H:%i') as tdate FROM viptrack_connections WHERE `bounce_error`!='Sended' AND `to`='$this->email' AND zDate>=DATE_SUB(NOW(),INTERVAL $this->LastHours HOUR) ORDER BY zDate DESC";
		if($GLOBALS["VERBOSE"]){echo "$sql\n";}
		
		
		$q=new mysql();	
		$results=$q->QUERY_SQL($sql,"artica_events");
		if(!$q->ok){if($GLOBALS["VERBOSE"]){echo "$this->email:: ".__FUNCTION__." error $q->mysql_error\n";}}
		$count=mysql_num_rows($results);
			$text[]="";	
			$text[]="";			
			$text[]="$count refused `received` Messages (connections) since last $this->LastHours hours";
			$text[]="----------------------------------------------";	
		
		if($count>100){
			$sql="SELECT COUNT(zmd5) as tcount,`from`,bounce_error FROM viptrack_connections 
			WHERE `to`='$this->email' AND bounce_error!='Sended' AND zDate>=DATE_SUB(NOW(),INTERVAL $this->LastHours HOUR)
			GROUP BY `from`,bounce_error ORDER BY tcount DESC";
			$results=$q->QUERY_SQL($sql,"artica_events");
			
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				$text[]="{$ligne["tcount"]} messages from {$ligne["from"]} blocked by `{$ligne["bounce_error"]}` rejected method";
			}			
		}else{
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				$text[]="on {$ligne["tdate"]}  from {$ligne["from"]} with {$ligne["smtp_sender"]} (located in {$ligne["Country"]}) blocked by `{$ligne["bounce_error"]}` rejected method";
			}
		}

		
		$text[]='';
		$sql="SELECT *,DATE_FORMAT(zDate,'%W %d %M %H:%i') as tdate FROM viptrack_content WHERE `bounce_error`!='PASS' AND `to`='$this->email' 
		AND bounce_error!='PASS'
		AND zDate>=DATE_SUB(NOW(),INTERVAL $this->LastHours HOUR) ORDER BY zDate DESC";
		$results=$q->QUERY_SQL($sql,"artica_events");
		if(!$q->ok){if($GLOBALS["VERBOSE"]){echo "$this->email:: ".__FUNCTION__." error $q->mysql_error\n";}}
		$count=mysql_num_rows($results);
		$bb=0;
		if($count>0){
			if($count>100){
				$sql="SELECT COUNT( zmd5 ) AS tcount, `from` , bounce_error
					FROM viptrack_content
					WHERE `to` = '$this->email'
					AND zDate>=DATE_SUB(NOW(),INTERVAL $this->LastHours HOUR)
					AND bounce_error!='PASS'
					GROUP BY `from` , bounce_error
					ORDER BY tcount DESC";
				$results=$q->QUERY_SQL($sql,"artica_events");
				while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
					$bb++;
					if($ligne["bounce_error"]=="WHITELISTED"){continue;}
					$text2[]="{$ligne["tcount"]} messages {$ligne["from"]} Bloqued by `{$ligne["bounce_error"]}` rejected method";
				}					
						
				
			}else{
				mb_internal_encoding("UTF-8");
				$ligne["subject"] = mb_decode_mimeheader($ligne["subject"]); 				
				while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
					if($ligne["bounce_error"]=="WHITELISTED"){continue;}
					$bb++;
					$text2[]="{$ligne["bounce_error"]} on {$ligne["tdate"]} from {$ligne["from"]}";
					$text2[]="Subject: {$ligne["subject"]}";
					$text2[]="";
				}			
			}
		}
		
		if($bb>0){
			$text[]="";				
			$text[]="$bb refused `received` Messages (content filters) since last $this->LastHours hours";
			$text[]="----------------------------------------------";
			$text[]=@implode("\n",$text2);		
		}
		
		return @implode("\n",$text);
	}
	
	
	function QuarantinedDetails(){
		$sql="SELECT DATE_FORMAT(zDate,'%W %d %M %H:%i') as zDate,mailfrom,subject,message_size FROM quarantine WHERE mailto='$this->email'
		AND zDate>=DATE_SUB(NOW(),INTERVAL $this->LastHours HOUR)";
		$q=new mysql();	
		$results=$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){if($GLOBALS["VERBOSE"]){echo "$this->email:: ".__FUNCTION__." error $q->mysql_error\n";}}
		$count=mysql_num_rows($results);
		if($count==0){return;}
			$text[]="";	
			$text[]="";	
			$text[]="$count messages stored in quarantine area since last $this->LastHours hours";
			$text[]="----------------------------------------------------";			
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				mb_internal_encoding("UTF-8");
				$ligne["subject"] = mb_decode_mimeheader($ligne["subject"]); 
				$text[]="on {$ligne["zDate"]} from {$ligne["mailfrom"]}";
				$text[]="Subject: {$ligne["subject"]}";	
				$text[]="";	
				
			}
		return @implode("\n",$text);
	}
	
	
	
	
	
	
}