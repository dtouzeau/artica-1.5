<?php
/*
+----------------------------------------------------------------------+
| BasiliX - Copyright (C) 2000-2002 Murat Arslan <arslanm@basilix.org> |
| Artica - Copyright (C) 2007-2008 David Touzeau <david.touzeau@fr.kaspersky.com>
+----------------------------------------------------------------------+
*/

// IMAP package, this is used to handle imap related functions easier
// -----------------------------------------------------------------------
// Thansk to Murat to developp this usefull class...

include_once(dirname(__FILE__) . '/class.imap.decode.inc');
include_once(dirname(__FILE__) . '/class.imap.mime.parser.inc');
include_once(dirname(__FILE__) . '/class.imap.rfc822_addresses.inc');

class IMAP {
   var $imapstr = 0;
   var $user = "";
   var $pass = "";
   var $host = "";
   var $port = "";
   var $array_folders;

   function IMAP() {
        // do nothing
   }
   
   // create an imap connection
   function open($username, $password, $host, $port, $notls = 0) {
       if($notls>0)$notls_str="/notls";
       $i = @imap_open("{" . $host . ":" . $port . $notls_str . "}INBOX", $username, $password);
       if(!$i) return false;
       $this->imapstr = $i;
       $this->user = $username;
       $this->pass = $password;
       $this->host = $host;
       $this->port = $port;
       return true;
   }

   // close the imap connection
   function close() {
       if($this->imapstr) imap_close($this->imapstr);
       $this->imapstr = 0;
       return true;
   }

   // are we connected?
   function ifok() {
      if(!$this->imapstr) return false;
      return true;
   }

   // create a mbox
   function crtmbox($mbox) {
       if(!$this->ifok()) return false;
       return imap_createmailbox($this->imapstr, "{" . $this->host . ":" . $this->port . "}" . $mbox);
   }

   // delete a mbox
   function delmbox($mbox) {
       if(!$this->ifok()) return false;
       return imap_deletemailbox($this->imapstr, "{" . $this->host . ":" . $this->port . "}" . $mbox);
   }
  
   // rename a mbox
   function renmbox($old, $new) {
       if(!$this->ifok()) return false;
       return imap_renamemailbox($this->imapstr, 
			"{" . $this->host . ":" . $this->port . "}" . $old,
			"{" . $this->host . ":" . $this->port . "}" . $new);
   }
	   
   // list the subscribed mboxes in a dir (cyrus/courier)
   function lstscrbed($dir) {
       if(!$this->ifok()) return false;
       return imap_listsubscribed($this->imapstr, "{" . $this->host . ":" . $this->port . "}", $dir);
   }

   // list the mboxes in a dir
   function lstmbox($dir) {
       if(!$this->ifok()) return false;
       return imap_listmailbox($this->imapstr, "{" . $this->host . ":" . $this->port . "}", $dir);
   }

   function getmailboxes($dir) {
       if(!$this->ifok()) return false;
       return imap_getmailboxes($this->imapstr, "{" . $this->host . ":" . $this->port . "}", $dir);
   }
   function getmboxes($dir) {
       $mboxes = $this->getmailboxes($dir);
       $i = 0;
       $ret = array();
       if(empty($mboxes)) return $ret;
       while(list($key, $val) = each($mboxes)) {
	  $delim = $val->delimiter;
	  $name = imap_utf7_decode($val->name);
	  $name_arr = explode($delim, $name);
	  $j = count($name_arr) - 1;
	  $mbox_name = $name_arr[$j];
	  if($mbox_name == "") continue; // the DIRECTORY itself
	  $ret[$i++] = $mbox_name;
       }
       sort($ret);
       return $ret;
   }

   // reopen the desired mbox (just the name of the mbox)
   function reopbox($mbox) {
       if(!$this->ifok()) return false;
       return imap_reopen($this->imapstr, "{" . $this->host . ":" . $this->port . "}" . $mbox);
   }

   // reopen the desired mbox (full mbox name should be given as $mbox)
   function reopbox2($mbox) {
       if(!$this->ifok()) return false;
       return imap_reopen($this->imapstr, $mbox);
   }

   // mailbox info
   function mboxinfo() {
       if(!$this->ifok()) return false;
       return imap_mailboxmsginfo($this->imapstr);
   }
  
   // sort the mbox
   function mboxsrt($criteria, $reverse) {
       if(!$this->ifok()) return false;
       return imap_sort($this->imapstr, $criteria, $reverse, SE_NOPREFETCH);
   }

   // retrieve the header of the message
   function msghdr($msgnum) {
       if(!$this->ifok()) return false;
       return imap_header($this->imapstr, $msgnum);
   }
   
   // get the UID of the message
   function msguid($msgnum) {
       if(!$this->ifok()) return false;
       return imap_uid($this->imapstr, $msgnum);
   }
   
   // get the NO of the message
   function msgno($msguid) {
       if(!$this->ifok()) return false;
       return imap_msgno($this->imapstr, $msguid);
   }

   // fetch the structure
   function ftchstr($msgnum) {
       if(!$this->ifok()) return false;
       return imap_fetchstructure($this->imapstr, $msgnum);
   }

   // fetch the header of the message
   function ftchhdr($msgnum) {
       if(!$this->ifok()) return false;
       return imap_fetchheader($this->imapstr, $msgnum);
   }
   

   // delete the specified message
   function rmmail($uid) {
       if(!$this->ifok()) return false;
       $msgno = $this->msgno($uid);
       return imap_delete($this->imapstr, $msgno);
   }

   // move the specifed msg to mbox B
   function mvmail($uid, $tombox) {
       if(!$this->ifok()) return false;
       return imap_mail_move($this->imapstr, $uid, $tombox, CP_UID);
   }

   // expunge the mailbox
   function expng() {
       if(!$this->ifok()) return false;
       return imap_expunge($this->imapstr);
   }   

   // fetch the body of the message
   function ftchbody($msgno, $part) {
       if(!$this->ifok()) return false;
       return imap_fetchbody($this->imapstr, $msgno, $part, NONE);
   }

   // set the flags
   function setflg($seq, $flg) {
       if(!$this->ifok()) return false;
       return imap_setflag_full($this->imapstr, $seq, $flg);
   }

   // search messages
   function srch($q) {
       if(!$this->ifok()) return false;
       return imap_search($this->imapstr, $q, SE_UID);
   }
 
   // append to sent mail
   function apnd($m, $b) {
       if(!$this->ifok()) return false;
       return @imap_append($this->imapstr, "{" . $this->host . ":" . $this->port . "}" . $m, $b);
   }
   
   
   // List folders and return hash
   function HashListFolders($folder){
   	 $folders=$this->lstmbox($folder.'/%');
   	 if(!is_array($folders)){return null;}
   	 while(list($key, $val) = each($folders)) {
   	 	if(preg_match('#\{(.+?)\}(.+)#',$val,$re)){
   	 		$spl=explode('/',$re[2]);
   	 		if(is_array($spl)){
   	 		unset($spl[0]);
   	 		while(list($index, $new_folder) = each($spl)) {
   	 			$arr[$re[2]]=$this->HashListFolders("$folder/$new_folder");
   	 			
   	 		}
   	 		}else{$arr[$re[2]]=$spl[1];}
   	 		
   	 		
   	 		
   	 		
   	 		
   	 	}
   	 }
   	 
   return $arr;}
   
   
   function ListMessagesInFolder($folder){
		$this->reopbox($folder);
		$decode=new msg_DecodeMessage();
		$array=$this->mboxsrt('SORTARRIVAL',1);
		if(is_array($array)){
			while(list($nul, $msg_id) = each($array)) {
				$header=$this->ftchhdr($msg_id);
				
				$decode->InitHeaderAndBody($header,'');
				$subject=$decode->Headers('Subject');
				if(preg_match('#From:[\s"]+(.+?)[\s"]+<(.+?)>#',$header,$a)){$from=texttooltip($a[1],$a[2]);}else{$from=$decode->Headers('From');}
				$date=$this->StrToTime($decode->headers('Date'));
				if(preg_match('#=\?([A-Z\-0-9]+)\?#i',$subject,$r)){
					$subject=iconv_mime_decode($decode->Headers('Subject'),0,'ISO-8859-1');
					$subject=htmlentities($subject);
				}
				$res[]=array('msg_id'=>$msg_id,"subject"=>$subject,"date"=>$date,"header"=>$header,"from"=>$from);
			}
		}   	
   	
  return $res;}
  
function StrToTime($str){
	$IMAP_MONTHS=array("Jan"=>1,"Feb"=>2,"Mar"=>3,"Apr"=>4,"May"=>5,"Jun"=>6,"Jul"=>7,"Aug"=>8,"Sep"=>9,"Oct"=>10,"Nov"=>11,"Dec"=>12);
	$IMAP_SERVER_TZ = date('Z');
		
	
	
	

	$str = trim($str);
	$str = str_replace("  ", " ", $str);
	
	
	$pos=strpos($str, " ");
	if (!is_numeric(substr($str, 0, $pos))) $str = substr($str, $pos+1);
	$a=explode(" ",$str);
	$month_str=$a[1];
	$month=$IMAP_MONTHS[$month_str];
	$day=(int)$a[0];
	$year=(int)$a[2];
	$time=$a[3];
	$tz_str = $a[4];
	$tz = substr($tz_str, 0, 3);
	$ta = explode(":",$time);
	$hour=(int)$ta[0]-(int)$tz;
	$minute=(int)$ta[1];
	$second=(int)$ta[2];
	
	//make UNIX timestamp
	$time2 = mktime($hour, $minute, $second, $month, $day, $year);
	//echo '<!--'.$time1.' '.$time2.' //-->'."\n";
	return date('Y-m-d H:i',$time2);
}


function DecodeMessage($msgid,$folder){
	$this->reopbox($folder);
	$decode=new msg_DecodeMessage();
	$page=CurrentPageName();
	$header=$this->ftchhdr($msgid);
	if(preg_match('#From:[\s"]+(.+?)[\s"]+<(.+?)>#',$header,$a)){
		$from=texttooltip($a[1],$a[2]);
		
	}
	$struct=$this->_decode_msg($msgid);
//print_r($struct);
	$decode->InitHeaderAndBody($header,'');
	$subject=$decode->Headers('Subject');
	if($from==null){
		$from=$decode->headers('From');
	}
	$date=$this->StrToTime($decode->headers('Date'));
if(preg_match('#=\?([A-Z\-0-9]+)\?#i',$subject,$r)){
					$subject=iconv_mime_decode($decode->Headers('Subject'),0,'ISO-8859-1');
					$subject=htmlentities($subject);
				}	

	if(($struct["content"]["HTML"][0]<>null)){
		$contents=$struct["content"]["HTML"][0];
		$contents["content"]=imap_qprint($contents["content"]);
		
		//retrive cid,
		if(preg_match_all('#cid:(.+?)[\s+"\'\>]+#',$contents["content"],$re)){
			while(list($num, $cid) = each($re[1])) {
				$contents["content"]=str_replace("cid:$cid","imap.index.php?ShowMessageAttachment=$cid&msgid=$msgid",$contents["content"]);
			}
		}
		
		}
		
		else{
			
			while(list($num, $plain) = each($struct["content"]["PLAIN"])) {
				
				$mess=$plain["content"];
				if($mess<>null){
			
				if($plain["iso"]<>null){
					$mess=iconv_mime_decode($mess,0,$plain["iso"]);
				}
				$mess=nl2br($mess);
				$body=$body."$mess<hr>";}
				
				
			}
		$contents["content"]=$body;
				
			}
		

	return array($contents["iso"],$contents["content"],$subject,$date,$from);
	}

function _decode_msg($msgid){

		$struct = imap_fetchstructure($this->imapstr, $msgid);
		$parts = $struct->parts;
		//var_dump($parts);
        $i = 0;

        if (!$parts) { /* Simple message, only 1 piece */
          $attachment = array(); /* No attachments */
		$Rcontent["PLAIN"][]=array("content"=>imap_body($this->imapstr, $msgid));
        } else { /* Complicated message, multiple parts */
       
          $endwhile = false;
       
          $stack = array(); /* Stack while parsing message */
          $content = array();    /* Content of message */
          $attachment = array(); /* Attachments */
       
          while (!$endwhile) {
            if (!$parts[$i]) {
              if (count($stack) > 0) {
                $parts = $stack[count($stack)-1]["p"];
                $i     = $stack[count($stack)-1]["i"] + 1;
                array_pop($stack);
              } else {
                $endwhile = true;
              }
            }
         
            if (!$endwhile) {
              /* Create message part first (example '1.2.3') */
              $partstring = "";
              foreach ($stack as $s) {
                $partstring .= ($s["i"]+1) . ".";
              }
              $partstring .= ($i+1);
           	 
              if (strtoupper($parts[$i]->disposition) == "ATTACHMENT") { 
              	$message=$this->encode_attach($parts[$i], imap_fetchbody($this->imapstr, $msgid, $partstring));
                $attachment[$parts[$i]->parameters->value] = array(
                	"filename" => $parts[$i]->parameters->value,
                	"filedata" => $message,
                	"encoding"=>$parts[$i]->encoding,
                	"type"=>$this->encode_attach_type($parts[$i]->type)
                	
                	);
              }
              
                        
              
              else{
              	//var_dump($parts[$i]);
              	
			if ($parts[$i]->type == 5) { 
				$name=$parts[$i]->parameters->value;
				if(trim($name)==null){
					$name=$parts[$i]->id;
					$name=str_replace("<",'',$name);
					$name=str_replace(">",'',$name);
					}
				
				$message=$this->encode_attach($parts[$i], imap_fetchbody($this->imapstr, $msgid, $partstring));
				
             	 $attachment[$name] = array(
                	"filename" => $name,
                	"filedata" => $message,
                	"encoding"=>$parts[$i]->encoding,
                	"type"=>$this->encode_attach_type($parts[$i]->type) . $parts[$i]->subtype
                	
                	);
              }                  
              
              if(strtoupper($parts[$i]->subtype) == "PLAIN") { /* Message */
					$iso=strtoupper($parts[$i]->parameters->value);
                	$Rcontent["PLAIN"][]=array("content"=>imap_fetchbody($this->imapstr, $msgid, $partstring),"iso"=>$iso);
              		}
              		
              if(strtoupper($parts[$i]->subtype) == "ALTERNATIVE") { /* Message */
					$iso=strtoupper($parts[$i]->parameters->value);
					$message=$this->encode_attach($parts[$i], imap_fetchbody($this->imapstr, $msgid, $partstring));
                	$Rcontent["PLAIN"][]=array("content"=>$message,"iso"=>$iso);
              		}              		
              	
               if(strtoupper($parts[$i]->subtype) == "HTML") { /* Message */
              		$iso=strtoupper($parts[$i]->parameters->value);
              		$message=$this->encode_attach($parts[$i], imap_fetchbody($this->imapstr, $msgid, $partstring));
                	$Rcontent["HTML"][]=array("content"=>$message,"iso"=>$iso);
               		}
            }
            }

            if ($parts[$i]->parts) {
              $stack[] = array("p" => $parts, "i" => $i);
              $parts = $parts[$i]->parts;
              $i = 0;
            } else {
              $i++;
            }
          } /* while */
        } /* complicated message */
//print_r($attachment);
       return array('content'=>$Rcontent,'iso'=>$iso,"attachment"=>$attachment);
}

function encode_attach($parts,$message){
	$coding =$parts->encoding;
	if ($coding == 0){return $message;}
	elseif ($coding == 1){$message = imap_8bit($message);}
	elseif ($coding == 2){$message = imap_binary($message);}
	elseif ($coding == 3){$message = imap_base64($message);}
	elseif ($coding == 4){$message = $message;}
	elseif ($coding == 5){$message = $message;}
	return  $message; 
	}
	
	function encode_attach_type($num)	{
		switch ($num) {
			case 0:
				return "text/";
				break;
			case 1:
				return "multipart/";
				break;	
			case 2:
				return "message/";
				break;
	
			case 3:
				return "application/";
				break;	
			case 4:
				return "audio/";
				break;
			case 5:
				return "image/";
				break;
			case 6:
				return "video/";
				break;	
			case 7:
				return "other/";
				break;																				
		
			default:
				break;
		}
	
	}




}






$imap = new IMAP;

?>