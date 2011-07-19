<?php

/*
 * $Id: Managesieve.php,v 1.15 2006/05/22 15:23:57 stephengrier Exp $
 *
 * Copyright 2002-2006 Stephen Grier <stephengrier@users.sourceforge.net>
 *
 * See the inclosed NOTICE file for conditions of use and distribution.
 */

// Managesieve response states
define ("F_NO", 0);             
define ("F_OK", 1);
define ("F_BYE", 2);
define ("F_UNKNOWN", 3);
define ("F_DATA", 4);
define ("F_EOF", 5);

// Managesieve response codes
define ("RC_QUOTA", 10);
define ("RC_SASL", 20);
define ("RC_REFERRAL", 30);
define ("RC_UNKNOWN", 255);

// Managesieve states
define ("S_NOCONNECTION", 1);
define ("S_CONNECTED", 2);
define ("S_AUTHENTICATED", 3);

// Version info
define ("MS_VERSION", '$Revision: 1.15 $');


/**
 * Class Managesieve is an implementation of the managesieve protocol 
 * for uploading and managing Sieve scripts.
 *
 * References:
 * draft-martin-managesieve-04.txt
 *
 * @author  Stephen Grier <stephengrier@users.sourceforge.net>
 * @version $Revision: 1.15 $
 */
class Managesieve {

   /**
    * The managesieve server to connect to.
    * @var string
    * @access public
    */
    var $server = '127.0.0.1';

   /**
    * The port of the managesieve server.
    * @var string
    * @access public
    */
    var $port = '2000';

   /**
    * The user to authenticate as.
    * @var string
    * @access public
    */
    var $auth = '';

   /**
    * The authentication user's password.
    * @var string
    * @access public
    */
    var $passwd = '';

   /**
    * The user to authorize as.
    * @var string
    * @access public
    */
    var $authz;

   /**
    * SASL auth mechanism to use.
    * @var string
    * @access public
    */
    var $sasl_mech;

   /**
    * The capabilities of the server as advertised in response to the 
    * CAPABILITY command.
    * @var array
    * @access private
    */
    var $_capabilities = array();

   /**
    * The file pointer to the open socket.
    * @var resource
    * @access private
    */
    var $_socket;

   /**
    * Timeout state of last socket read.
    * @var boolean
    * @access private
    */
    var $_sock_timed_out = false;

   /**
    * Connection state. One of the S_* contants.
    * @var integer
    * @access private
    */
    var $_state;

   /**
    * Whether TLS is enabled on connection.
    * @var boolean
    * @access private
    */
    var $_tls = false;

   /**
    * An array containing values from the last server response.
    * @var array
    * @public
    */
    var $resp;

   /**
    * Error message.
    * @var string
    * @access private
    */
    var $_errstr = '';


    /**
     * Class constructor. 
     */
    function Managesieve()
    {
        $this->_errstr = '';
        $this->_state = S_NOCONNECTION;
    }


    // class methods

   /**
    * Open a connection to the server and parse server capabilities.
    *
    * @param server string Server to connect to.
    * @param port   string The server port.
    * @param socket_timeout int The socket timeout in seconds.
    * @return true on success, false on failure.
    */
    function open($server, $port, $socket_timeout=2)
    {
        unset($this->resp);
        $this->_errstr = '';

        if (is_resource($this->_socket)){
            $this->_errstr = 'open: socket already open';
            return false;
        }

        if (isset($server)){
            $this->server = $server;
        }
        if (isset($port)){
            $this->port = $port;
        }

        $this->_socket = fsockopen($this->server, $this->port, $errno, $errstr, "60");
        if (!$this->_socket) {
            $this->_errstr = "open: fsockopen failed with $errno: $errstr";
            return false;
        }
        $this->_state = S_CONNECTED;

        socket_set_timeout($this->_socket, $socket_timeout);
        socket_set_blocking($this->_socket, true);

        return $this->parseCapability();
    }


   /**
    * Parse the server's capability response. This might result from an 
    * initial connection to the server, or from the capability command.
    *
    * @return true on success, false on failure.
    */
    function parseCapability()
    {
        unset($this->resp);
        $this->_errstr = '';

        if (!is_resource($this->_socket)){
            $this->_errstr = 'parseCapability: no server connection';
            return false;
        }

        // Cyrus v2.x response will look like: "IMPLEMENTATION" "Cyrus timsieved..."
        // Cyrus v1.x will look like: "Cyrus timsieved v1.0.0" "SASL={PLAIN,...}"

        while ($this->getResponse() == F_DATA) {
            $tokens = explode("\"", $this->resp['data']);
            if (substr($tokens[1], 0, 15) == 'Cyrus timsieved') {
                // Cyrus v1.x
                $this->_capabilities['implementation'] = $tokens[1];
                // $tokens[3] should look like "SASL={PLAIN, LOGIN}"
                $mechstr = substr(strstr($tokens[3], '{'), 1, strlen($tokens[3])-1);
                $this->_capabilities['sasl'] = explode(", ", $mechstr);
                return true;
            }
            switch ($tokens[1]) {
                case "IMPLEMENTATION":
                    // "IMPLEMENTATION" "Cyrus timsieved v2.2.3"
                    $this->_capabilities['implementation'] = $tokens[3];
                    break;
                case "SASL":
                    // "SASL" "PLAIN DIGEST-MD5"
                    $this->_capabilities['sasl'] = explode(" ", $tokens[3]);
                    break;
                case "SIEVE":
                    // "SIEVE" "fileinto reject envelope vacation"
                    $this->_capabilities['extensions'] = explode(" ", $tokens[3]);
                    break;
                case "STARTTLS":
                    $this->_capabilities['starttls'] = true;
                    break;
                default:
                    $this->_capabilities['unknown_banners'][] = $line;
                    break;
            }
        }
        if ($this->resp['state'] == F_OK) {
            return true;
        }
        $this->_errstr = 'parseCapability: capability failed: ' . $this->responseToString();
        return false;
    }


   /**
    * Read a line from socket. We continue reading until we reach a CRLF 
    * or until fread() times out. The socket should be in blocking mode 
    * with timeout as set in $this->open(). We include the CRLF.
    *
    * @return string line read from socket.
    */
    function read()
    {
        $buffer = '';
        $this->_sock_timed_out = false;

        if (!is_resource($this->_socket)){
            return $buffer;
        }

        /* read one character at a time and add to $buffer. */
        $char = fread($this->_socket,1);
        while ($char != '') {

            $buffer .= $char;

            /* return $buffer if we've reached CRLF. */
            if (substr($buffer, -2) == "\r\n"){
                return $buffer;
            }
            $char = fread($this->_socket,1);
        }
        $this->_sock_timed_out = true;
        return $buffer;
    }


   /**
    * Get and parse a server response line. The value returned will be one of 
    * the RC_* values, reflect a server response of NO, OK, BYE, unspecified 
    * response data, or a socket timeout. Any response code, error or data 
    * will be set in $this->resp[];
    *
    * return int one of the F_* codes
    */
    function getResponse()
    {
        unset($this->resp);
        $this->resp = array();
        $this->_errstr = '';
        $line = $this->read();

        // Check for socket timeout
        if ($this->_sock_timed_out === true){
            $this->resp['data'] = $line;
            return $this->resp['state'] = F_EOF;
        }
        // match NO
        elseif (substr($line, 0, 2) == 'NO') {
            // match NO {34}\r\n...\r\n...
            // {34} is lenth of error message string, including CRLFs.
            if (preg_match("/^NO \{(\d+)\}\r\n$/", $line, $m)){
                $len_read = 0;
                while ($len_read < $m[1]) {
                    $line = $this->read();
                    if ($this->_sock_timed_out === true) {
                        return $this->resp['state'] = F_EOF;
                    }
                    $this->resp['errstr'][] = substr($line, 0, -2);
                    $len_read += strlen($line);
                }
                // read the CRLF which terminates the multiline error string.
                $this->read();
            }
            // match NO "error msg"
            elseif (preg_match("/^NO \"(.+)\"\r\n$/", $line, $m)){
                $this->resp['errstr'][] = $m[1];
            }
            // match NO ("QUOTA") "xxxx"
            // match NO ("SASL" "authentication failure") "Authentication error"
            elseif (preg_match("/^NO \(\"(.+?)\"( \".+?\")?\) \"(.+)\"\r\n$/",$line,$m)){
                switch ($m[1]) {
                    case "QUOTA":
                        $this->resp['code'] = RC_QUOTA;
                        break;
                    case "SASL":
                        $this->resp['code'] = RC_SASL;
                        break;
                    default:
                        $this->resp['code'] = RC_UNKNOWN;
                        $this->resp['data'] = $line;
                        break;
                }
                if ($m[2] !== '') {
                    $this->resp['code_args'] = substr($m[2], 2, -1);
                }
                $this->resp['errstr'][] = $m[3];
            }
            else {
                $this->resp['code'] = RC_UNKNOWN;
                $this->resp['data'] = $line;
            }
            return $this->resp['state'] = F_NO;
        }
        // match OK
        elseif (substr($line, 0, 2) == 'OK' ||
                substr($line, 0, 2) == 'Ok') {
            // match OK\r\n
            if ($line == "OK\r\n") {
            }
            // match OK "Logout Complete"
            // Cyrus v2.0 returns Ok "Logout Complete"
            elseif (preg_match("/^O[Kk] \"(.+)\"\r\n$/", $line, $m)) {
                $this->resp['errstr'][] = $m[1];
            }
            // match OK (SASL "cnNwYX1ZG...NzmY3MDN==")
            elseif (preg_match("/^OK \((\w+?) \"(.+)\"\)\r\n$/",$line,$m)) {
                switch ($m[1]) {
                    case "SASL":
                        $this->resp['code'] = RC_SASL;
                        break;
                    default:
                        $this->resp['code'] = RC_UNKNOWN;
                        $this->resp['data'] = $line;
                        break;
                }
                $this->resp['code_args'] = $m[2];
            }
            else {
                $this->resp['code'] = RC_UNKNOWN;
                $this->resp['data'] = $line;
            }
            return $this->resp['state'] = F_OK;
        }
        // match BYE
        elseif (substr($line, 0, 3) == 'BYE') {
            // match BYE (REFERRAL "server") "Try Remote."
            if (preg_match("/^BYE \((.+) \"(.+)\"\) \"(.+)\"\r\n$/",$line, $m)){
                switch ($m[1]){
                    case "REFERRAL":
                        $this->resp['code'] = RC_REFERRAL;
                        break;
                    default:
                        $this->resp['code'] = RC_UNKNOWN;
                        $this->resp['data'] = $line;
                        break;
                }
                $this->resp['code_args'] = $m[2];
                $this->resp['errstr'][] = $m[3];
            }
            else {
                $this->resp['code'] = RC_UNKNOWN;
                $this->resp['data'] = $line;
            }
            return $this->resp['state'] = F_BYE;
        }
        // match {123}\r\n.........\r\n
        elseif (preg_match("/^\{(\d+)\+?\}\r\n$/", $line, $m)){
            $str = '';
            while (strlen($str) <= $m[1]) {
                $str .= $this->read();
                if ($this->_sock_timed_out === true) {
                    $this->resp['data'] = $str;
                    return $this->resp['state'] = F_EOF;
                }
            }
            $this->resp['data'] = substr($str, 0, $m[1]);
            return $this->resp['state'] = F_DATA;
        }
        // match listscripts and capability response "......"
        // Not told how much data to read, so return each line as F_DATA.
        elseif (preg_match("/^\".+\"( ACTIVE| \".+\")?\r\n$/", $line)) {
            $this->resp['data'] = substr($line, 0, -2);
            return $this->resp['state'] = F_DATA;
        }
        // else an unrecognised response.
        $this->resp['data'] = $line;
        return $this->resp['state'] = F_UNKNOWN;
    }


   /**
    * Return a textual version of the $resp structure returned by getResponse().
    *
    * @return string message
    */
    function responseToString()
    {
        $msg = '';
        if (empty($this->resp)){
            return $msg;
        }
        $resp = $this->resp;
        if ($resp['state'] == F_OK){
            $msg .= '[' . F_OK . '] ';
            if (isset($resp['code'])){
                if ($resp['code'] == RC_SASL){
                    $msg .= 'received final SASL response';
                }else{
                    $msg .= 'unknown response code: ' . $resp['data'];
                }
            }
            if (isset($resp['errstr'])){
                foreach ($resp['errstr'] as $errstr) {
                    $msg .= $errstr;
                }
            }
        }
        if ($resp['state'] == F_NO){
            $msg .= '[' . F_NO . '] ';
            if (isset($resp['code'])){
                if ($resp['code'] == RC_QUOTA){
                    $msg .= 'over quota: ' . $resp['errstr'][0];
                } elseif ($resp['code'] == RC_SASL) {
                    $msg .= 'SASL error: ' . $resp['code_args'] . ': ' . $resp['errstr'][0];
                }else{
                    $msg .= 'unknown response code: ' . $resp['data'];
                }
            }
            elseif (isset($resp['errstr'])){
                foreach ($resp['errstr'] as $errstr) {
                    $msg .= "$errstr ";
                }
            }
        }
        if ($resp['state'] == F_BYE){
            $msg .= 'BYE received: ';
            if (isset($resp['code'])){
                if ($resp['code'] == RC_REFERRAL){
                    $msg .= "referred to '" . $resp['code_args'] . "': " . $resp['errstr'][0];
                }else{
                    $msg .= 'unknown response code: ' . $resp['data'];
                }
            }
        }
        if ($resp['state'] == F_EOF){
            $msg .= 'socket timed out while reading server response';
            if ($resp['data'] != '') {
                $msg .= ': ' . $resp['data'];
            } elseif (!empty($resp['errstr'])) {
                $msg .= ': ';
                foreach ($resp['errstr'] as $errstr) {
                    $msg .= "$errstr ";
                }
            }
        }
        if ($resp['state'] == F_DATA){
            $msg .= 'data received';
        }
        if ($resp['state'] == F_UNKNOWN){
            $msg .= 'unknown response: ' . $resp['data'];
        }
        return $msg;
    }


   /**
    * Select which SASL mechanism to use for authentication.
    * The server advertises which mechanisms it will allow. If 
    * none of the mechanisms SmartSieve supports are available, 
    * this will return false, and authentication will fail.
    *
    * @param mech string containing user specified SASL mechanism
    * @return mixed string containing mechanism to use or boolean false
    */
    function _selectSaslMech($mech=null)
    {
        // List of SASL mechanisms this class supports.
        $supported_mechs = array('digest-md5','plain');

        if (!empty($mech)){
            if (!in_array(strtolower($mech), $supported_mechs)) {
                $this->_errstr = "_selectsaslmech: mechanism \"$mech\" not supported";
                return false;
            }
            $use_mechs = array($mech);
        } else {
            $use_mechs = $supported_mechs;
        }

        // Return first supported mech available on server.
        foreach ($use_mechs as $use_mech) {
            if (in_array(strtoupper($use_mech), $this->_capabilities['sasl'])) {
                return $use_mech;
            }
        }
        $this->_errstr = "_selectsaslmech: no available mechanisms";
        return false;
    }


   /**
    * Authenticate user against the server.
    *
    * @param auth string authentication user
    * @param passwd string authentication password
    * @param authz string authorization user
    * @param sasl_mech string SASL auth method
    * @return boolean true on success, false on failure
    */
    function authenticate($auth, $passwd, $authz=null, $sasl_mech=null)
    {
        unset($this->resp);
        $this->_errstr = '';

        if (!is_resource($this->_socket)){
            $this->_errstr = 'authenticate: no server connection';
            return false;
        }
        if (empty($auth)){
            $this->_errstr = 'authenticate: no authentication user specified';
            return false;
        }
        $this->auth = $auth;
        if (empty($passwd)){
            $this->_errstr = 'authenticate: no password specified';
            return false;
        }
        if (isset($authz)){
            $this->authz = $authz;
        }
        if (isset($sasl_mech)){
            $this->sasl_mech = $sasl_mech;
        }

        if (!$sasl_mech = $this->_selectSaslMech($this->sasl_mech)){
            return false;
        }
        switch ($sasl_mech){

            case "plain":
                $authstr = $this->authz . "\x00" . $this->auth . "\x00" . $passwd;
                $authstr = base64_encode($authstr);
                $len = strlen($authstr);
                fputs($this->_socket, sprintf("AUTHENTICATE \"PLAIN\" {%s+}\r\n", $len));
                fputs($this->_socket,"$authstr\r\n");

                if ($this->getResponse() == F_OK) {
                    $this->_state = S_AUTHENTICATED;
                    return true;
                }
                $this->_errstr = "authenticate: authentication failure connecting to $this->server: " . $this->responseToString();
                return false;
                break;

            case "digest-md5":
                // follows rfc2831 for generating the $response to $challenge
                fputs($this->_socket, "AUTHENTICATE \"DIGEST-MD5\"\r\n");
                // read the challenge. the max length for this is 2048.
                // don't include the CRLF returned by $this->read().
                $this->getResponse();
                if ($this->resp['state'] != F_DATA) {
                    $this->_errstr = 'authenticate: ' . $this->responseToString();
                    return false;
                }
                $challenge = substr($this->resp['data'], 0, -2);
                // vars used when building $response_value and $response
                $cnonce = base64_encode(md5(microtime()));
                $ncount = "00000001";
                $qop_value = "auth"; 
                $digest_uri_value = 'sieve/' . $this->server;
                // decode the challenge string
                $result = array();
                $challenge = base64_decode($challenge);
                preg_match("/nonce=\"(.*)\"/U",$challenge, $matches);
                $result['nonce'] = $matches[1];
                preg_match("/realm=\"(.*)\"/U",$challenge, $matches);
                $result['realm'] = $matches[1];
                preg_match("/qop=\"(.*)\"/U",$challenge, $matches);
                $result['qop'] = $matches[1];
                // verify server supports qop=auth 
                $qop = explode(",",$result['qop']);
                if (!in_array($qop_value, $qop)) {
                   // rfc2831: client MUST fail if no qop methods supported
                   return false;
                }
                // build the $response_value
                $string_a1 = utf8_encode($this->auth).":";
                $string_a1 .= utf8_encode($result['realm']).":";
                $string_a1 .= utf8_encode($passwd);
                $string_a1 = pack('H*', md5($string_a1));
                $A1 = $string_a1.":".$result['nonce'].":".$cnonce.":".utf8_encode($this->authz);
                $A1 = md5($A1);
                $A2 = md5("AUTHENTICATE:$digest_uri_value");
                $string_response = $result['nonce'].":".$ncount.":".$cnonce.":".$qop_value;
                $response_value = md5($A1.":".$string_response.":".$A2);
                // build the challenge $response
                $reply = "charset=utf-8,username=\"" . $this->auth . "\",realm=\"" . $result['realm'] . "\",";
                $reply .= "nonce=\"" . $result['nonce'] . "\",nc=$ncount,cnonce=\"" . $cnonce . "\",";
                $reply .= "digest-uri=\"" . $digest_uri_value . "\",response=" . $response_value . ",";
                $reply .= "qop=" . $qop_value . ",authzid=\"" . utf8_encode($this->authz)."\"";
                $response = base64_encode($reply);
                fputs($this->_socket, "\"$response\"\r\n");

                $this->getResponse();

                // With SASL v2 the server sends the final response success data within the 
                // data portion of the SASL response code. With SASL v1.x, however, the server 
                // sends this as a separate response and we must send an extra empty request 
                // before the server sends the final SASL response.
                if (!preg_match("/Cyrus timsieved (v1\.1|v2\.\d)/", $this->_capabilities['implementation'])){
                    // Response should be either "{xxx}\r\nresponse string\r\n" or "NO".
                    if ($this->resp['state'] == F_DATA) {
                        // put the empty request.
                        fputs($this->_socket, "{0+}\r\n");
                        fputs($this->_socket, "\r\n");
                        $this->getResponse();
                    }
                }

                if ($this->resp['state'] == F_OK) {
                    $this->_state = S_AUTHENTICATED;
                    return true;
                }
                $this->_errstr = "authenticate: authentication failure connecting to $this->server: " . $this->responseToString();
                return false;
                break;

            default:
                $this->_errstr = "authenticate: mechanism '" . $selected_mech . "' not supported";
                return false;
                break;

        } //end switch.
    }


   /**
    * Logout of the current session.
    *
    * @return boolean true on success, false on failure
    */
    function logout()
    {
        unset($this->resp);
        $this->_errstr = '';

        if (!is_resource($this->_socket)){
            $this->_errstr = 'logout: no server connection';
            return false;
        }
        fputs($this->_socket,"LOGOUT\r\n");
        if ($this->getResponse() == F_OK) {
            $this->_state = S_CONNECTED;
            return true;
        }
        $this->_errstr = 'logout: failed to logout: ' . $this->responseToString();
        return false;
    }


   /**
    * Close the socket.
    *
    * @return boolean true on success, false on failure
    */
    function close() 
    {
        unset($this->resp);
        $this->_errstr = '';

        if (is_resource($this->_socket)) {
            if ($this->_tls === true && function_exists('stream_socket_enable_crypto')) {
                stream_socket_enable_crypto($this->_socket, false);
            }
            $this->logout();
            if (!fclose($this->_socket)){
                $this->_errstr = "close: failed closing socket to $this->server";
                return false; 
            }
        }
        $this->_socket = null;
        $this->_state = S_NOCONNECTION;
        return true;
    }


   /**
    * Return an array containing server Capabilities.
    *
    * @return mixed array of server capability values, or false on failure
    */
    function capability()
    {
        unset($this->resp);
        $this->_errstr = '';
                                                                                           
        if (!is_resource($this->_socket)) {
            $this->_errstr = 'capability: no server connection';
            return false;
        }
        fputs($this->_socket,"CAPABILITY\r\n");
        if ($this->parseCapability()) {
            // Work-around for extra NO response with Cyrus v2.0.
            if (substr($this->_capabilities['implementation'], -6) == 'v1.0.0') {
                $this->read();
            }
            return $this->_capabilities;
        }
        return false;
    }


   /**
    * Intiate TLS negotiation with the server.
    *
    * @return boolean true on success, false on failure
    */
    function starttls()
    {
        unset($this->resp);
        $this->_errstr = '';

        if (!is_resource($this->_socket)) {
            $this->_errstr = 'starttls: no server connection';
            return false;
        }
        if (!isset($this->_capabilities['starttls']) ||
            $this->_capabilities['starttls'] !== true) {
            $this->_errstr = 'starttls: STARTTLS not advertised by server';
            return false;
        }
        if (!function_exists('stream_socket_enable_crypto')) {
            $this->_errstr = 'starttls: TLS not available';
            return false;
        }
        fputs($this->_socket, "STARTTLS\r\n");
        if ($this->getResponse() === F_OK) {
            if (stream_socket_enable_crypto($this->_socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                $this->_tls = true;
                return ($this->capability() !== false) ? true : false;
            }
            // Issue bogus capability command.
            fputs($this->_socket, "CAPABILITY\r\n");
            $this->getResponse();
        }
        $this->_errstr = 'starttls: TLS initialization failed: ' . $this->responseToString();
        return false;
    }


   /**
    * Return a list of the Sieve scripts owned by the authzed user.
    *
    * @return mixed array containing Sieve scripts, or false on failure
    */
    function listScripts()
    {
        unset($this->resp);
        $this->_errstr = '';

        if (!is_resource($this->_socket)) {
            $this->_errstr = 'listScripts: no server connection';
            return false;
        }
        $scripts = array();
        fputs($this->_socket,"LISTSCRIPTS\r\n");
        while ($this->getResponse() == F_DATA) {
            $last_quote = strrpos($this->resp['data'], '"');
            $sname = substr($this->resp['data'], 1, ($last_quote - 1));
            $active = false;
            // Cyrus v2 active script: "script" ACTIVE
            if (substr($this->resp['data'], -6) == 'ACTIVE') {
                $active = true;
            }
            // Cyrus v1 active script: "script*"
            if (substr($sname, -1) == '*') {
                $sname = substr($sname, 0, -1);
                $active = true;
            }
            $scripts[$sname] = $active;
        }
        if ($this->resp['state'] == F_OK) {
            return $scripts;
        }
        $this->_errstr = 'listScripts: failed: ' . $this->responseToString();
        return false;
    }


   /**
    * Retrieve the contents of a Sieve script.
    *
    * @param string script name
    * @return mixed script text or false if failure
    */
    function getScript($name)
    {
        unset($this->resp);
        $this->_errstr = '';

        if (!is_resource($this->_socket)) {
            $this->_errstr = "getScript: no server connection";
            return false;
        }
        $script = array('raw'=>'','size'=>0);
        fputs($this->_socket, sprintf("GETSCRIPT \"%s\"\r\n", $this->prepareQuotedString($name)));
        if ($this->getResponse() == F_DATA) {
            $script['raw'] = $this->resp['data'];
            $script['size'] = strlen($this->resp['data']);
            if ($this->getResponse() == F_OK) {
                return $script;
            }
        }
        $this->_errstr = "getScript: could not get script \"$name\": " . $this->responseToString();
        return false;
    }


   /**
    * Set a script as the active script on the server. A zero lenth script name
    * will deactivate the existing active script.
    *
    * @param name string name of script to set as the active script
    * @return boolean true on success, false on failure
    */
    function setActive($name='')
    {
        unset($this->resp);
        $this->_errstr = '';

        if (!is_resource($this->_socket)) {
            $this->_errstr = "setActive: no server connection";
            return false;
        }
        fputs($this->_socket, sprintf("SETACTIVE \"%s\"\r\n", $this->prepareQuotedString($name)));
        if ($this->getResponse() == F_OK) {
            return true;
        }
        $this->_errstr = "setActive: could not activate script \"$name\": " . $this->responseToString();
        return false;
    }


   /**
    * Check that the user will not exceed sieve_maxscriptsize or 
    * sieve_maxscripts by uploading script $name of size $size bytes.
    * Note: HAVESPACE is broken in Cyrus up to v2.0.16.
    *
    * @param name string containing script name
    * @param size integer size of script in bytes
    * return boolean true if server will allow, false if not
    */
    function haveSpace($name, $size)
    {
        unset($this->resp);
        $this->_errstr = '';

        if (!is_resource($this->_socket)) {
            $this->_errstr = 'haveSpace: no server connection';
            return false;
        }
        fputs($this->_socket, sprintf("HAVESPACE \"%s\" %s\r\n", $this->prepareQuotedString($name), $size));
        if ($this->getResponse() == F_OK) {
            return true;
        }
        $this->_errstr = 'haveSpace: ' . $this->responseToString();
        return false;
    }


   /**
    * Put script $name containing $text on the server. The script will not be 
    * set active. Zero length scripts should be allowed.
    *
    * @param name string containing name of script
    * @param text string containing script content
    * @return boolean true on success, or false on failure
    */
   
   function writelogs($text=null){
		$logFile="/tmp/smartsieve.log";
		$f = @fopen($logFile, 'a');
		$date=date("Y-m-d H:i:s");
		@fwrite($f, "$text\n");
		@fclose($f);
		}
   
    function putScript($name,$text=''){
        unset($this->resp);
        $this->_errstr = '';

        if (!is_resource($this->_socket)) {
            $this->_errstr = "putScript: no server connection";
            return false;
        }
        $len = strlen($text);
       $this->writelogs(sprintf("PUTSCRIPT \"%s\" {%s+}\r\n", $this->prepareQuotedString($name), $len));
         $this->writelogs("$text\r\n");
        fputs($this->_socket, sprintf("PUTSCRIPT \"%s\" {%s+}\r\n", $this->prepareQuotedString($name), $len));
        fputs($this->_socket,"$text\r\n");
        if ($this->getResponse() == F_OK){
            return true;
        }
        $this->_errstr = "putScript: could not put script \"$name\": " . $this->responseToString();
        /* Work-around for extra response bug in Cyrus 2.0. */
        if ($this->resp['state'] == F_NO && 
            $this->resp['errstr'][0] == 'Did not specify script data' &&
            substr($this->_capabilities['implementation'], -6) == 'v1.0.0') {
            while ($this->read()) {
            }
        } 
        return false;
    }


   /**
    * Delete a Sieve script
    *
    * @param name string script to delete
    * @return boolean true on success, false on failure
    */
    function deleteScript($name)
    {
        unset($this->resp);
        $this->_errstr = '';

        if (!is_resource($this->_socket)) {
            $this->_errstr = "deleteScript: no server connection";
            return false;
        }
        fputs($this->_socket, sprintf("DELETESCRIPT \"%s\"\r\n", $this->prepareQuotedString($name)));
        if ($this->getResponse() == F_OK){
            return true;
        }
        $this->_errstr = "deleteScript: could not delete script \"$name\": " . $this->responseToString();
        return false;
    }


   /**
    * Return the last error string.
    *
    * @return string $this->_errstr
    */
    function getError()
    {
        return $this->_errstr;
    }

   /**
    * Return the most recent response structure.
    *
    * @return mixed array response, or false if not set
    */
    function getLastResponse()
    {
        return (isset($this->resp)) ? $this->resp : false;
    }


   /**
    * Prepare a quoted string. Escape backslash and double quote chars.
    *
    * @param string $str The string to prepare
    * @return The prepared string
    */
    function prepareQuotedString($str)
    {
        $i = 0;
        $qs = '';
        while ($i < strlen($str)) {
            if ($str[$i] == '\\') {
                $i++;
                if ($str[$i] == '\\') {
                    $qs .= '\\\\';
                } elseif ($str[$i] == '"') {
                    $qs .= '\\"';
                } else {
                    $qs .= '\\\\' . $str[$i];
                }
            } elseif ($str[$i] == '"') {
                $qs .= '\\"';
            } else {
                $qs .= $str[$i];
            }
            $i++;
        }
        return $qs;
    }

   /**
    * Return the version of class Managesieve.
    *
    * @return string The version of this class
    */
    function getVersion()
    {
        return MS_VERSION;
    }



} // class Managesieve


?>
