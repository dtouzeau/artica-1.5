<?php
/*
 * $Header: /cvsroot/urklesphp/phpCyrusImap/CyrusImap.php,v 1.1 2003/09/01 20:31:22 urkle Exp $
 *
 * Copyright 2002-2003 Edward Rudd <eddie@omegaware.com>
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 */

/**
 * CyrusAdmin class.. v0.4
 * administers a cyrus IMAP server
 *
 * Parameters:
 *  'hostspec'     the hostname or ip of the server
 *  'protocol'	    the protocol the protocol to connect to the server.
 *  'port'         the server port to connect to.
 *  'username'     the username to connect with.
 *  'password'     the password to connect with.
 *
 *  RFC 2060 - imap 4rev1
 *  RFC 2086 - ACL extension
 *  RFC 2087 - QUOTA extension
 *  RFC 2177 - IDLE command
 *  RFC 2342 - NAMESPACE
 *
 */
define('ACLMAP_RIGHTS',0);
define('ACLMAP_SYM',1);
define('NS_PERSONAL',0);
define('NS_USERS',1);
define('NS_SHARED',2);

class CyrusAdmin
{
    /**
     * hash containing connection parameters.
     * @var array $params
     */
    var $params = array();

    /**
     * connection string.
     * @var string $dsn
     */
    var $dsn;

    /**
     * the imap server handle.
     * @var $imap
     */
    var $imap = null;

    /**
     * the capability array.
     * @var $caps
     */
    var $caps = null;

    /**
     * Are we already connected?
     * @var boolean $connected
     */
    var $connected = false;
    var $logged=false;
    var $error="";

    /**
     * Associated Array containing entries for the three namespaces
	 * Each NS member of this array contains an 'ns' member which is
	 * the root for that Namespace (ie user. for the User naemspace)
	 * and a 'delim' member which contains the heirarchy seperator 
	 * which is used for proper concatinating of folder paths.
	 * If a Namespace is false then there is no such namespace available
	 * which on cyrus 2.1.x and above the Personal namespace does not exist
	 * for the Administrative user.
     * @var string $ns
     */
    var $ns = array(
		NS_PERSONAL=>false,
		NS_USERS=>false,
		NS_SHARED=>false
	);

    /**
     * Constructs a new CyrusImap object.
     *
     * @param array $params   A hash containing connection parameters.
     */
    function CyrusAdmin($params = array())
    {
        $this->setParams($params);
    }

    /**
     * Take a hash and build the connection string out of it.
     *
     * @param array $params   A hash specifying an IMAP mailbox.
     */
    function setParams($params)
    {
		if (empty($params['hostspec'])) {
			$params['hostspec'] = 'localhost';
        }
        if (empty($params['protocol'])) {
            $params['protocol']='imap';
        }
        if (empty($params['port'])) {
            $params['port'] = 143;
        }
		$this->params = $params;
    }

    /**
     * Makes a connection to the cyrus imap server
     *
     * @returns boolean    True on success, false on failure
     */
    function _connect()
    {
		if ($this->connected) return true;
		if($this->params['password']==null){
			writelogs("No password set",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			return false;
		}
		$this->imap = fsockopen($this->params["hostspec"],$this->params["port"]);
		if ($this->imap===false) {
			writelogs("fsockopen failed {$this->params["hostspec"]}:{$this->params["port"]}",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			return false;
		}
	    $this->connected = true;
		//authenticate
		$return = array();		
		//if ($this->sendCommand("",$return) ) ;
		if ($this->sendCommand("LOGIN ".$this->params['username']." ".$this->params['password'],$return)) {
			writelogs("Logged, continue...",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			//Get Namespace heirarchy seperator
			if ($this->GetCapability("NAMESPACE")) {
				if ($this->sendCommand("NAMESPACE",$return)) {
					if (preg_match_all("/NIL|\(\(.*?\)\)/",$return[0],$arg)) {
						if ($arg[0][0]!='NIL') {
							preg_match('/\("(.*?)" "(.*?)"\)/',$arg[0][0],$ns);
							$this->ns[NS_PERSONAL]=array('ns'=>$ns[1],'delim'=>$ns[2]);
						}
						if ($arg[0][1]!='NIL') {
							preg_match('/\("(.*?)" "(.*?)"\)/',$arg[0][1],$ns);
							$this->ns[NS_USERS]=array('ns'=>$ns[1],'delim'=>$ns[2]);
						}
						if ($arg[0][2]!='NIL') {
							preg_match('/\("(.*?)" "(.*?)"\)/',$arg[0][2],$ns);
							$this->ns[NS_SHARED]=array('ns'=>$ns[1],'delim'=>$ns[2]);
						}
					}
				}
			} else { //no NameSpace support.. Use List
				if ($this->sendCommand('LIST "" ""',$return)) {
					if (preg_match('/LIST \(/*\) "(.)"/',$return[0],$arg)) {
						$this->ns[NS_PERSONAL]=array('ns'=>"",'delim'=>$arg[1]);
						$this->ns[NS_USERS]=array('ns'=>"",'delim'=>$arg[1]);
						$this->ns[NS_SHARED]=array('ns'=>"",'delim'=>$arg[1]);
					}
				}
			}
			$this->logged=true;
		    return true;
		} else {
			writelogs("unable to login -> disconnect",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			$this->_disconnect();
			return false;
		}
    }

	/**
	 * Check for capability
	 *
	 * @params
	 * $capability string	Capability to check for
	 *
	 * @returns boolean		True on success, false on faliue
	 */
	function GetCapability($capability)
	{
		$this->_connect();

		if (!isset($this->caps)) {
			// Fetch Capabilities
			$this->caps=array();
			if (! $this->sendCommand("CAPABILITY",$ret)) {
				return false;
			}
			foreach(explode(" ",$ret[0]) as $cap) {
				$this->caps[]=$cap;
			}
		}
		return in_array($capability,$this->caps);
	}

    /**
     * Send a command to IMAP stream.
     *
     * @params
	 * $command string	Command to send
	 * &$return array	array of Results from command
	 *
     * @returns boolean  True on success, false on failure
     */
	function sendCommand($command,&$return)
	{
		if ($this->connected) {
			if (!empty($command)) {
				// Send command
				fputs($this->imap,"01 ");
				writelogs("\"$command\"",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				fputs($this->imap,$command."\r\n");
			}
			// read response
			$return = array();
			while ($line = fgets($this->imap)) {
				writelogs("Recieve \"$line\"",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
				if(preg_match("#BAD Unexpected#",$line)){
					$this->error="BAD Unexpected";
					return false;
				}
				
				if(preg_match("#OK User logged in#",$line)){
					return true;
				}	

				if(preg_match("#OK Completed#",$line)){
					return true;
				}	
				
				if(preg_match("#NO Permission denied#",$line)){
					$this->error="Permission denied";
					return false;
				}
				
				if(preg_match("#NO Mailbox already exists#",$line)){
					
					return true;
				}
				
				if(preg_match("#NO Quota root does not exist#",$line)){
					$this->error="Quota root does not exist";
					return false;
				}
				if(preg_match("#NO Authentication failed#",$line)){
					$this->error="Authentication failed";
					return false;
					
				}
				
				
				

				if (preg_match("/^(\*|01) (.*)\r\n$/",$line,$arg)) {
					if (empty($command) && $arg[1]=="*") {
						$return[] = $arg[2];
						if (preg_match("/^OK/",$arg[2])) {
							return true;
						} else {
							return false;
						}
					} elseif (!empty($command) && $arg[1]=="01") {
						if (preg_match("/^OK/",$arg[2])) {
							return true;
						} else {
							$return[] = $arg[2];
							return false;
						}
					}
					$return[] = $arg[2];
				}
			}
		}
		return false;
	}

    /**
     * Closes Cyrus imap connection.
     *
     * @returns boolean  True on success, false on failure
     */
    function _disconnect()
    {
		if ($this->connected) {
			$this->sendCommand("LOGOUT",$ret);
			fclose($this->imap);
			$his->connected=false;
		}
		return true;
    }

    /**
     * Get list of mailboxes
     *
     * @returns mixed	array of mailboxe name, or false on error
     */
    function listmbox($filter="*")
	{
		$mboxes = array();
		if ($this->sendCommand('LIST "" "'.$filter.'"',$mboxes)) {
		    for ($i=0;$i<count($mboxes);$i++) {
				preg_match("/\"([^\"]*)\"$/",$mboxes[$i],$arg);
				$mboxes[$i] = $arg[1];
		    }
		    return $mboxes;
		}
		return false;
    }

	
    /**
     * Get Access Control List
     *
     * @params
     * $mailbox string     Mail box to get ACL for
	 *
	 * @returns mixed    array of acls, or false on error
     */
    function getacl($mailbox)
    {
		if (!$this->GetCapability("ACL"))
			return false;
		if ($this->sendCommand("GETACL \"$mailbox\"",$ret)) {
			preg_match("/ACL [a-zA-Z\/_]* (.*)/",$ret[0],$acl);
			$acls = explode(" ",$acl[1]);
			for ($i=0;$i<count($acls); $i+=2) {
				$return[$acls[$i]] = $this->aclmap($acls[$i+1],ACLMAP_SYM);
			}
			return $return;
		} else {
			return false;
		}
    }

    /**
     * ACL map: maps rights to symbolic name or symbolic name to rights
     *
     * @params
     * $acl		string	symbolic name or rights
	 * $action  enum	ACLMAP_SYM,ACLMAP_RIGHTS default ACLMAP_RIGHTS
	 *
	 * @returns string	rights or symbolic name
     */
    function aclmap($acl,$action=ACLMAP_RIGHTS)
    {
		$rights = array(
			'none'=>'',
			'read'=>'lrs',
			'post'=>'lrsp',
			'append'=>'lrsip',
			'write'=>'lrswipcd',
			'all'=>'lrswipcda'
		);
		switch ($action) {
		case ACLMAP_RIGHTS:
			if (isset($rights[$acl])) {
				return $rights[$acl];
			} else {  //assume already in RIGHTS format.. Should check though
				return $acl;
			}
			break;
		case ACLMAP_SYM:
			if ( ($val = array_search($acl,$rights))!==false) {
				return $val;
			} else {
				return $acl;
			}
			break;
		}
    }

    /**
     * Set Access Control List
     *
     * @params
     * $mailbox string		Mail box to set ACL on
     * $user  	string		User to add to ACL
     * $rights	string		Rights to give to user
	 *
	 * @returns bool		true on success, or false on error
     */
    function setacl($mailbox,$user,$rights)
    {
		if (!$this->GetCapability("ACL"))
			return false;
		$rights = $this->aclmap($rights);
		return $this->sendCommand("SETACL \"$mailbox\" $user $rights",$ret);
    }

    /**
     * Delete user from ACL
     *
     * @params
     * $mailbox string		Mail box to delete user ACL from
     * $user  	string		User to ro remove ACL for
	 *
	 * @returns bool		true on success, or false on error
     */
    function deleteacl($mailbox,$user)
    {
		if (!$this->GetCapability("ACL"))
			return false;
		return $this->sendCommand("DELETEACL \"$mailbox\" $user");
    }

    /**
     * Set Quote limit on a mailbox
     *
     * @params
     * $mailbox string     Mail box to set quote on
     * $limit	string     Max Size in Kilobytes
	 *
	 * @returns bool	true on success, false on failure
     */
    function setquota($mailbox,$limit)
    {
		if (!$this->GetCapability("QUOTA"))
			return false;
		return $this->sendCommand("SETQUOTA \"$mailbox\" (STORAGE $limit)",$ret);
    }

    /**
     * Get Quote limit from a mailbox
     *
     * @params
     * $mailbox string     Mail box to set quote on
     *
     * @returns mixed 		Quota Size in Kilobytes ('usage','limit'), or 
	 *							false on failure or no quota root.
     */
    function getquota($mailbox)
    {
		if (!$this->GetCapability("QUOTA"))
			return false;
		if ($this->sendCommand("GETQUOTA \"$mailbox\"",$ret)) {
			preg_match("/\([A-Z]+ ([0-9]*) ([0-9]*)/",$ret[0],$arg);
			return array($arg[1],$arg[2]);
		}
		return false;
    }
    
    function subscribe($mailbox){
    	
    	$this->sendCommand("SUBSCRIBE \"$mailbox\"",$ret);
    }
    
    function STATUS($mailbox){
    	$this->sendCommand("STATUS \"$mailbox\"",$ret);
    }

    /**
     * Create a new mailbox
     *
     * @params
     * $mailbox string     Mailbox to create
     *
     * @return boolean	   True on success, false on error
     */
    function createmailbox($mailbox)
    {
	//  optional second parameter defines "partition"
		return $this->sendCommand("CREATE \"$mailbox\"",$ret);
    }

    /**
     * Delete a mailbox
     *
     * @params
     * $mailbox string     Mailbox to delete
     *
     * @return boolean	   True on success, false on error
     */
    function deletemailbox($mailbox)
    {
		return $this->sendCommand("DELETE \"$mailbox\"",$ret);
    }

    /**
     * Merge folders within requested Namespace
     *
     * @params
     * $ns	enum		Namespace to merge within
     * $mbox1	string		Mailbox
     * ...
     *
     * @returns mixed		merged mailbox path on success, or false on error
     */
    function merge_folder($ns)
    {
	if (!$this->connected) $this->_connect();
	$numargs = func_num_args();
	$args = func_get_args();
	$ret = $args[1];
	for ($i=2;$i<$numargs;$i++) {
	    if (substr($ret,-1)==$this->ns[$ns]['delim']) {
		if (substr($args[$i],0,1)==$this->ns[$ns]['delim']) {
		    $ret.=substr($args[$i],1);
		} else {
		    $ret.=$args[$i];
		}
	    } else {
		if (substr($args[$i],0,1)==$this->ns[$ns]['delim']) {
		    $ret.=$args[$i];
		} else {
		    $ret.=$this->ns[$ns]['delim'].$args[$i];
		}
	    }
	}
	return $ret;
    }

    /**
     * Get NS Root
     *
     * @params
     * $ns	enum		Namespace root to acquire
     *
     * @returns mixed		ns root on success, or false on error
     */
    function ns_root($ns)
    {
	if (!$this->connected) $this->_connect();
	return $this->ns[$ns]['ns'];
    }
}
?>