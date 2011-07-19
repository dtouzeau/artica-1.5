<?php
include_once(dirname(__FILE__).'/class.mime.parser.inc');
include_once(dirname(__FILE__).'/class.rfc822.addresses.inc');
include_once(dirname(__FILE__).'/class.user.inc');
include_once(dirname(__FILE__).'/class.ldap.inc');



class demime{
		var $file_source="";
		var $mailfrom;
		var $message_id="";
		var $mailto_array=array();
		var $subject="";
		var $message_date;
		var $error;
		var $MustkillMail=false;
	function demime($path){
		if(!is_file($path)){return null;}
		$this->file_source=$path;
		
		
	}
	
function GetOuFromEmail($email){
	$email=trim($email);
	if($GLOBALS["FALSE_EMAILS"][$email]){return null;}
	if(preg_match("#(.+?)@(.+)#",$email,$re)){$domain=$re[2];}else{return null;}
	$domain=trim($domain);
	$this->events("$email=[$domain]");
	
	
	if($GLOBALS["DOMAINS"][$domain]<>null){return $GLOBALS["DOMAINS"][$domain];}
	if($GLOBALS["EMAILS"][$email]<>null){return $GLOBALS["EMAILS"][$email];}
	
	$ldap=new clladp();
	$ou=$ldap->ou_by_smtp_domain($domain);
	if($ou<>null){
		$GLOBALS["DOMAINS"][$domain]=$ou;
		return $ou;
		
	}
	
	$this->events("[$domain] is not found in LDAP");

	$uid=$ldap->uid_from_email($email);
	if($uid==null){
		$this->events("[$email] is not found in LDAP");
		return null;
	}else{
		$this->events("[$email] = $uid account");
	}
	
	$ct=new user($uid);
	$ou=$ct->ou;
	$GLOBALS["EMAILS"][$recipient]=$ou;
	return $ou;
	
	$GLOBALS["FALSE_EMAILS"][$email]=true;
}	

private function DecodeDate($decoded){
	if(trim($decoded["date:"])<>null){return $decoded["date:"];}
		
	if((!is_array($decoded["received:"])) && ($decoded["received:"]<>null)){
		if(preg_match("#[;\s]+([A-Za-z]+),\s+([0-9]+)\s+(.+?)$#",$decoded["received:"],$re)){
			return "{$re[1]}, {$re[2]} {$re[3]}";
		}
	}
	
	if((is_array($decoded["received:"])) && ($decoded["received:"][0]<>null)){
		if(preg_match("#[;\s]+([A-Za-z]+),\s+([0-9]+)\s+(.+?)$#",$decoded["received:"][0],$re)){
			return "{$re[1]}, {$re[2]} {$re[3]}";
		}
	}
	
	$this->events("Unable to locate a date in this mail !! {$decoded["received:"]}");
	
}

	
	function unpack(){
		$mime=new mime_parser_class();
		$mime->decode_bodies = 0;
		$mime->ignore_syntax_errors = 1;
		$decoded=array();
		$parameters=array('File'=>$this->file_source,'SkipBody'=>1);	
		if(!$mime->Decode($parameters, $decoded)){
			$this->error='MIME message decoding error: '.$mime->error.' at position '.$mime->error_position;
			$this->MustkillMail=true;
			return false;
			
		}
		
		if(!is_array($decoded[0]["Headers"])){
			$this->error='MIME message decoding no array found';
			return false;
		}
		$mime_decoded=$decoded[0]["Headers"];
		$mime_decoded["from:"]=str_replace("'","",$mime_decoded["from:"]);
		$mime_decoded["from:"]=str_replace('"',"",$mime_decoded["from:"]);
		
		if($mime_decoded["x-envelope-from:"]<>null){
			if(preg_match("#<(.+?)>#",$mime_decoded["x-envelope-from:"],$re)){
				$this->mailfrom=$re[1];}else{$this->mailfrom=$mime_decoded["x-envelope-from:"];}
		}else{
			if($mime_decoded["from:"]<>null){
			if(preg_match("#<(.+?)>#",$mime_decoded["from:"],$re)){
				$this->mailfrom=$re[1];}else{$this->mailfrom=$mime_decoded["from:"];}
			}
		}
		
		$this->message_date=$this->DecodeDate($mime_decoded);
		
		if($this->message_date==null){
			$this->message_date=date("d M Y H:i:s", filemtime($this->file_source));
		}
		$this->message_date=$this->DateMessageToSql($this->message_date);
		$this->message_id=$mime_decoded["message-id:"];
		if(preg_match("#<(.+?)>#",$this->message_id,$re)){$this->message_id=$re[1];}
		$mime_decoded["to:"]=str_replace('"',"",$mime_decoded["to:"]);
		$mime_decoded["to:"]=str_replace("'","",$mime_decoded["to:"]);
		$mime_decoded["cc:"]=str_replace('"',"",$mime_decoded["cc:"]);
		$mime_decoded["cc:"]=str_replace("'","",$mime_decoded["cc:"]);		
		if($mime_decoded["to:"]<>null){$mime_decoded["to:"]=$this->Explodecc($mime_decoded["to:"]);}
		if($mime_decoded["cc:"]<>null){$mime_decoded["cc:"]=$this->Explodecc($mime_decoded["cc:"]);}
		$this->events("x-envelope-to-blocked=".$mime_decoded["x-envelope-to-blocked:"]." line ".__LINE__);
		$this->events("x-envelope-to=".$mime_decoded["x-envelope-to:"]." line ".__LINE__);
		$this->events("to=".$mime_decoded["to:"]." line ".__LINE__);
		$this->events("cc={$mime_decoded["cc:"]}".__LINE__);
		
		
		
		$this->events("From=$this->mailfrom line ".__LINE__);
		if($mime_decoded["x-envelope-to-blocked:"]==null){$mime_decoded["x-envelope-to-blocked:"]=$mime_decoded["x-envelope-to:"];}
		
		
		
		if($mime_decoded["x-envelope-to-blocked:"]<>null){
			$mime_decoded["x-envelope-to-blocked:"]=$mime_decoded["x-envelope-to-blocked:"].',';
			if($mime_decoded["cc:"]<>null){$mime_decoded["x-envelope-to-blocked:"]=$mime_decoded["x-envelope-to-blocked:"].$mime_decoded["cc:"];}
			$mailtos=explode(",",$mime_decoded["x-envelope-to-blocked:"]);
		}else{
			if($mime_decoded["cc:"]<>null){$mime_decoded["to:"]=$mime_decoded["to:"].",".$mime_decoded["cc:"];}
			$mailtos=explode(",",$mime_decoded["to:"]);
		}
		
		$this->subject=$mime_decoded["subject:"];
		$this->subject=str_replace("'","`",$this->subject);
		
		while (list ($num, $email) = each ($mailtos) ){
			$email=trim($email);
			if(trim($email)==null){continue;}
			if(!preg_match("#<(.+?)>#",$email,$re)){
				$this->events("mailto finale=$email ".__LINE__);
				$this->mailto_array[]=$email;
				continue;
			}else{
				$this->mailto_array[]=$re[1];
			}
			
		}
		if(!is_array($this->mailto_array)){return false;
			$this->error="No recipients found !";
		}
		
		return true;
		}
		
		function ExportToHtml($file){
			if(!is_file($file)){return null;}
			if(!is_file("/usr/bin/mhonarc")){$this->events("Unable to stat /usr/bin/mhonarc");return null;}
			$prefix=EXEC_NICE();
			
			
				
			
			$file_mho=$file;
			$file_mho=str_replace('$','\$',$file_mho);
			$file_mho=str_replace('!','\!',$file_mho);			
			$file_mho=str_replace('&','\&',$file_mho);
			$file_mho=str_replace(';','\;',$file_mho);					
			$workingfile="/tmp/".md5($file_mho).".html";
			
			
			$attachmentdir="/tmp/attchs";
			@mkdir("/tmp/attchs");
		//$attachmenturl='images.listener.php?mailattach=';
		   $cmd="$prefix/usr/bin/mhonarc ";
		   $cmd=$cmd."-attachmentdir $attachmentdir ";
		   //$cmd=$cmd."-attachmenturl $attachmenturl ";
		   $cmd=$cmd."-nodoc ";
		   $cmd=$cmd."-nofolrefs ";
		   $cmd=$cmd."-nomsgpgs ";
		   $cmd=$cmd."-nospammode ";
		   $cmd=$cmd."-nosubjectthreads ";
		   $cmd=$cmd."-idxfname storage ";
		   $cmd=$cmd."-nosubjecttxt \"no subject\" ";
		   $cmd=$cmd."-single ";
		   $cmd=$cmd." $file_mho ";
		   $cmd=$cmd." >$workingfile 2>&1";
		   $results=system($cmd);
		   shell_exec("/bin/rm -rf /tmp/attchs");
		   $datas=@file_get_contents($workingfile);
		   @unlink($workingfile);
		   $this->events("html ". strlen($datas)." bytes");
		   if(strlen($datas)==0){$this->events("\"$cmd\" => \"$results\"");}
		   return $datas;

 }
 
 private function Explodecc($cc){
 	if(preg_match_all("#<(.+?)>#is",$cc,$re)){
 		return implode(",",$re[0]);
 	}
 	return $cc;
 }
 
function events($text){
		$pid=getmypid();
		$logFile="/var/log/artica-postfix/artica-mailarchive.debug";
		$date=date('Y-m-d H:i:s');
		$size=filesize($logFile);
		if($size>1000000){unlink($logFile);}
		$f = @fopen($logFile, 'a');
		@fwrite($f, "$date demime[$pid]: $text\n");
		@fclose($f);	
		}
		
private function reportdate($string){
	
	if(preg_match("#^([A-Za-z\.]+),\s+([0-9]+)\s+(.+?)\s+[0-9]+\s+([0-9\:]+)#",$string,$re)){
		return array("DAY"=>$re[2],"MONTH"=>$re[3],"TIME"=>$re[4]);
	}
	
	if(preg_match("#^([A-Za-z]+)\s+([A-Za-z]+)\s+([0-9]+)\s+([0-9\:]+)\s+([A-Z\-\+]+)#",$string,$re)){
		return array("DAY"=>$re[3],"MONTH"=>$re[2],"TIME"=>$re[4]);
	}
	
	if(preg_match("#.+?,\s+([0-9]+)\s+([A-Za-z]+)\s+([0-9]+)\s+([0-9\:]+)#",$string,$re)){
		return array("DAY"=>$re[1],"MONTH"=>$re[2],"TIME"=>$re[4]);
	}
	
	if(preg_match("#([0-9]+)\s+([A-Za-z]+)\s+([0-9]+)\s+([0-9\:]+)#",$string,$re)){
		return array("DAY"=>$re[1],"MONTH"=>$re[2],"TIME"=>$re[4]);
	}	
	
	$this->events("reportdate() unable to preg_match <$string>");
}
		
function DateMessageToSql($date){
        $Mtom=array(
          "jan"=>"01",
          "feb"=>"02",
          "mar"=>"03",
          "apr"=>"04",
          "may"=>"05",
          "jun"=>"06",
          "jul"=>"07",
          "aug"=>"08",
          "sep"=>"09",
          "oct"=>"10",
          "nov"=>"11",
          "dec"=>"12",
          "janvier"=>"01",
          "février"=>"02",
          "mars"=>"03","avril"=>"04","mai"=>"05","juin"=>"06","juillet"=>"07","août"=>"08","septembre"=>"09","octobre"=>"10","novembre"=>"11","décembre"=>"12",
        );	
        
    if(is_array($date)){
    	while (list ($num, $string) = each ($date) ){
    		$res=$this->reportdate($string);
    		if(is_array($res)){break;}
    	}
    }else{
    	$res=$this->reportdate($date);
    }
	
	if(!is_array($res)){
		$this->events("DateMessageToSql() unable to preg_match $date");
		return null;
	}
	
	$day=$res["DAY"];
	if(strlen($day)==1){$day="0$day";}
	$month=$Mtom[strtolower(trim($res["MONTH"]))];
	if($month==null){$month=date('m');}
	$year=$re[3];
	if($year<>date('Y')){$year=date('Y');}
	
	$time=$res["TIME"];
	return "$year-$month-$day $time";

	

	
}
		
	
	
}
?>