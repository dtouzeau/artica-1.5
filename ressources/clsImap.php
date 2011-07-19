<?php
/***************************************************************************************
T�tulo/Title: 	 Modulo para el acceso a servidores de Correo.
				 Mail access module

Autor/Author:	 Daniel Marjos

Descripci�n/     Estos servicios pueden ser utilizados tanto para acceder a casillas IMAP, como tambien 
Descripion:      a casillas POP3 y NNTP
				 This services can be used to access IMAP, POP3 and NNTP accounts

F. Inicio/
Started At:		 21 de Agosto de 2003 / August 21, 2003

Ult Modif./
Last Modified:   21 de Agosto de 2003 / August 21, 2003
***************************************************************************************/

/*

This class represents a single message.
Esta clase representa un unico mensaje.

The fields are quite self-explanatories, but here is a little documentation:

date 					: the date the message was sent
msgID					: the ID the SMTP server gave to this message
from					: who sent the message. 
to						: the message's recipients. Semicolon separated.
cc						: carbon-copy recipients.
cco						: blind carbon copy
hasAttachments			: True if the message has any attachment. False otherwise
subject					: the message subject
body					: the message body
attachments				: array holding all the attachments
*/
class clsMessage { 
	var $date; 
	var $msgID;
	var $from;
        var $size;
	var $to;
	var $cc;
	var $cco;
	var $hasAttachs = false;
	var $subject;
	var $body;
	var $attachments = array();
}

/*
Main class.


*/
class clsImapHandler {

	var $login = ""; // The login name
	var $password = ""; // Password to log in into the account
	var $servername = "localhost"; // the server to access
	var $protocol = "143/imap/notls"; // default protocol
	var $folder = "INBOX"; // default folder
	var $mbox; // internal use - the mbox identifier
	var $Messages; // the number of messages in the folder
	var $dispos; // internal use - used to process attachments
	var $currMsg; // internal use - the number of current processing message 
	
	/*
	Constructor.
	Sets up the internal fields.
	Receives as parameters:
	
	login 			: as explained above
	password		: as explained above
	server			: optional. allows to set a explicit server to access
	proto			: the protocol to use
	folder			: the folder to access
	*/
	function clsImapHandler($login,$password,$server="",$proto="",$folder="") {
		
		$this->login=$login;
		$this->password=$password;
		if (!empty($server)) $this->servername=$server;
		if (!empty($proto)) $this->protocol=$proto;
		if (!empty($folder)) $this->folder=$folder;
		$this->mbox=-1;
		$this->Messages=0;
		$this->dispos=0;		
	}
	
	/*
	function Open
	
	used to establish a connection to the mail server. Once connected succesfully, it retrieves 
	the number of messages, sets the mailbox identifier, and returns true. If unsuccesfull, 
	returns false
	*/
	function open() {
	
		$fqsn="{".$this->servername.":".$this->protocol."}".$this->folder;
		$_mbox=imap_open($fqsn,$this->login,$this->password);
		if ($_mbox) {
			$_headers=imap_headers($_mbox);
			$this->Messages=count($_headers);
			$this->mbox=$_mbox;
			return true;
		} else {
			return false;
		}
		
	}

	/*
	Function Close
	
	used to close the connection to the server
	*/
	function close() {
		
		imap_close($this->mbox);
		
	}

	/*
	
	Internal function _decodeString
	
	used to decode a string from bas64 or quoted_printable
	
	thanks to PHP.net user comments
	*/
	function _decodeString($theString) {
		if(ereg("=\?.{0,}\?[Bb]\?",$theString)){ 
			$arrHead=explode("=\?.{0,}\?[Bb]\?",$theString); 
			while(list($key,$value)=each($arrHead)){ 
				if(ereg("\?=",$value)){ 
					$arrTemp=explode("\?=",$value); 
					$arrTemp[0]=base64_decode($arrTemp[0]); 
					$arrHead[$key]=join("",$arrTemp); 
				} 
			} 
			$strHead=join("",$arrHead); 
		} elseif(ereg("=\?.{0,}\?[Qq]\?",$theString)){ 
			$strHead=quoted_printable_decode($theString); 
			$strHead=ereg_replace("=\?.{0,}\?[Qq]\?","",$strHead); 
			$strHead=ereg_replace("\?=","",$strHead); 
		} else {
			$strHead=$theString;
		}
		
		return $strHead;
	}

	/*
	function getMessage
	
	retrieves the message given as parameter.
	
	*/
	function getMessage($msgNo) {

		// sets the internal message pointer
		$this->currMsg=$msgNo;		
		
		// creates a new instance of clsMessage
		$theMessage = new clsMessage;
		
		// get the header info for the message
		$_theHeader=imap_headerinfo($this->mbox,$msgNo);
		
		// ---- Start Block
		/*
		This block sets the from field
		*/
		$_from=array();
		while (list($k,$v)=each($_theHeader->from)){
			if (!empty($v->personal)) {
				$strHead=$this->_decodeString($v->personal);
				$_address="\"".$strHead."\" <".$v->mailbox."@".$v->host.">";
			} else 
				$_address=$v->mailbox."@".$v->host;
			$_from[]="$_address";
		}
		$theMessage->from=implode(";",$_from);
		// ---- End Block

		// ---- Start Block
		/*
		This block sets the to field
		*/
		$_to=array();
		if (is_array($_theHeader->to)) {
			while (list($k,$v)=each($_theHeader->to)){
				if (!empty($v->personal))  {
					$strHead=$this->_decodeString($v->personal);
					$_address="\"".$strHead."\" <".$v->mailbox."@".$v->host.">";
				} else 
					$_address=$v->mailbox."@".$v->host;
				$_to[]=$_address;
			}
		} else
			$_to[]=$_theHeader->to;
		$theMessage->to=implode(";",$_to);
		// ---- End Block

		// ---- Start Block
		/*
		This block sets the cc field
		*/
		$_cc=array();
		if (is_array($_theHeader->cc)) {
			while (list($k,$v)=each($_theHeader->cc)){
				if (!empty($v->personal))  {
					$strHead=$this->_decodeString($v->personal);
					$_address="\"".$strHead."\" <".$v->mailbox."@".$v->host.">";
				} else 
					$_address=$v->mailbox."@".$v->host;
				$_cc[]=$_address;
			}
		} else
			$_cc[]=$_theHeader->cc;
		$theMessage->cc=implode(";",$_cc);
		// ---- End Block

		// ---- Start Block
		/*
		This block sets the cco field
		*/
		$_cco=array();
		if (is_array($_theHeader->cco)) {
			while (list($k,$v)=each($_theHeader->cco)){
				if (!empty($v->personal))  {
					$strHead=$this->_decodeString($v->personal);
					$_address="\"".$strHead."\" <".$v->mailbox."@".$v->host.">";
				} else 
					$_address=$v->mailbox."@".$v->host;
				$_cco[]=$_address;
			}
		} else
			$_cco[]=$_theHeader->cco;
		$theMessage->cco=implode(";",$_cco);
		// ---- End Block

		// sets date, subject and msgID fields
		$theMessage->date=$_theHeader->udate;
		$theMessage->subject=$_theHeader->subject;
		$theMessage->msgID=$_theHeader->message_id;

		// Fetchs the structure for the message
		$_theStructure=imap_fetchstructure($this->mbox,$msgNo);

                $theMessage->size = ceil(($_theStructure->bytes/1024));

		// tries to guess if the message has or not any attachment.
		if ((count($_theStructure->parts)==2) and (($_theStructure->parts[0]->type==0 and $_theStructure->parts[0]->subtype=="PLAIN") and ($_theStructure->parts[1]->type==0 and $_theStructure->parts[1]->subtype=="HTML"))) {
			$theMessage->hasAttachs=0;
		} else {
			$theMessage->hasAttachs=1;
		}
		
		// if the message has any attach, get them
		if ($theMessage->hasAttachs==1) {
			$this->_getAttachments($theMessage,$_theStructure,1);
		}
		
		$sections = $this->parse($_theStructure);
		if(count($theMessage->attachments)>1) {
			$theMessage->body = imap_fetchbody($this->mbox,  $msgNo, $sections[0]["pid"]);
		} else {
			// sets the body wenn kein attachment
			$theMessage->body = imap_body($this->mbox,$msgNo);
		}
		// returns the message.
		return $theMessage;
	}


	/*
	internal function _getAttachments
	
	This is a recursive function, used to traversing the message to gaet all the attachments to it.
	*/
	function _getAttachments(&$theMessage,$part,$id){

		// How many attachments had we processed so far?		
		$ans=sizeof($theMessage->attachments);

		// if this part has an attach, increase the level.
		if($part->ifdisposition){ 
			$this->dispos++; 
		}
		
		switch($part->type){ // depending on what kind of part we have here...
			case 1: // is this a multipart type? so, skip that...
				if((strtolower($part->subtype)=="mixed") or (strtolower($part->subtype)=="alternative") or (strtolower($part->subtype)=="related")) 
					break;
			default: // Otherwise...

				// How many attachments had we processed so far?		
				$an = sizeof($theMessage->attachments);
				
				if($part->ifdparameters){ // do we have any disposition parameter in this part?
					$dpara = $part->dparameters; // get the parameters
					for ($v=0;$v<sizeof($dpara);$v++){
						if (eregi("filename", $dpara[$v]->attribute)) // is a filename?
							$fname = $dpara[$v]->value; // so, get it...
					}
				}
				if($part->ifparameters){ // do we have any disposition parameter in this part?
					if(empty($fname)){ // is $fname empty? so...
						$para = $part->parameters; // get parameters
						for ($v=0;$v<sizeof($para);$v++){
							if (eregi("name", $para[$v]->attribute)) // do we have a file name ?
								$fname = $para[$v]->value; // so get it...
						}
					}
				}
				
				if ($this->dispos<=1) { // the attachment level is main?
					if(empty($fname)) $fname = "Unknown"; // no file name... :(
					$theMessage->attachments[$an]->id = ($an+1); // sets the attachment ID
					$theMessage->attachments[$an]->part = $id; // sets the attachment part number

					// let's get the mime part number, in the form x.y[.z]
					$_thePartsArray=explode(".",$id);
					$_theParts=array();
					for ($_theIndex=1; $_theIndex<count($_thePartsArray); $_theIndex++){
						$_theParts[]=$_thePartsArray[$_theIndex];
					}
					$_theMimePart=implode(".",$_theParts);
					
					// let's set the fields for this attachment.
					$theMessage->attachments[$an]->mime_part=$_theMimePart;
					$theMessage->attachments[$an]->filename = $fname;
					$theMessage->attachments[$an]->type=$part->type;
					$theMessage->attachments[$an]->subtype=$part->subtype;
					$theMessage->attachments[$an]->dispos=$this->dispos;
					$theMessage->attachments[$an]->disposition=$part->disposition;
					$theMessage->attachments[$an]->size=$part->bytes;
					$theMessage->attachments[$an]->encoding=$part->encoding;
					$theMessage->attachments[$an]->mime_type=$this->_get_mime_type($part);
					$theMessage->attachments[$an]->content=imap_fetchbody($this->mbox,$this->currMsg,$_theMimePart);
				}
				break;
		}
		
		// now, we'll recurse with all the parts found so far...
		for($x = 0; $x < count($part->parts); $x++){
			$this->_getAttachments($theMessage,$part->parts[$x], $id.".".($x+1));
		}
		
		if($part->ifdisposition) $this->dispos--;
	}

	/*
	internal function _get_mime_type
	
	used to get a text representation of the mime part
	*/
	function _get_mime_type(&$structure) {
		$primary_mime_type = array("TEXT", "MULTIPART","MESSAGE", "APPLICATION", "AUDIO","IMAGE", "VIDEO", "OTHER");
		if($structure->subtype) {
			return $primary_mime_type[(int) $structure->type] . '/' .$structure->subtype;
		}
		return "TEXT/PLAIN";
	}
	
	/*
	internal function _get_encoding
	
	not implemented yet. reserved for future extensions.
	*/
	function _get_enconding(&$structure) {
	}

	// parse message body
	
	function parse($structure){
	// Thanks to Harry Wiens
	
	global $type;
	global $encoding;
	
	// create an array to hold message sections
	
	$ret = array();
	
	// split structure into parts
	$parts = $structure->parts;
	
	/*
	iterate through parts
	and create an array whose every element
	represents one part
	each element is itself an associative array
	with keys representing the
	
	- part number
	- part type
	- encoding
	- disposition
	- size
	- filename
	*/
	
	for($x=0; $x<sizeof($parts); $x++) {
		$ret[$x]["pid"] = ($x+1);
		$_this = $parts[$x];
		// default to text
		if ($_this->type == "") $_this->type = 0; 
		$ret[$x]["type"] = $type[$_this->type] . "/" . strtolower($_this->subtype);
		// default to 7bit
		if ($_this->encoding == "") $_this->encoding = 0; 
		$ret[$x]["encoding"] = $encoding[$_this->encoding];
		$ret[$x]["size"] = strtolower($_this->bytes);
		$ret[$x]["disposition"] = strtolower($_this->disposition);
		if (strtolower($_this->disposition) == "attachment") { 
			$params = $_this->dparameters;
			foreach ($params as $p) {
				if($p->attribute == "FILENAME") {
					$ret[$x]["name"] = $p->value;
					break;
				}
			}
		}
		
		return $ret;
	}
	}
	
	
	
}

?>