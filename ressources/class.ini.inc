<?php
define('BS_INIHANDLER_UNQUOTE_NONE',   0);
define('BS_INIHANDLER_UNQUOTE_DOUBLE', 1);
define('BS_INIHANDLER_UNQUOTE_SINGLE', 2);
define('BS_INIHANDLER_UNQUOTE_ALL',    3);
define('BS_INIHANDLER_HEAD_COMMENT',   "#\n\n");

class Bs_IniHandler {
  	var $commentChars = array('#', '/', ';');
   	var $unQuote = BS_INIHANDLER_UNQUOTE_ALL;
	var $_sections;
  	var $_params=array();
  	var $_comments;
  	var $_fileFullPath;
  
  function Bs_IniHandler($fileFullPath='') {
    if (!empty($fileFullPath)) {
    	$this->_fileFullPath=$fileFullPath;
      $this->loadFile($fileFullPath);
    }
  }
  
  
  function loadFile($fileFullPath) {
    $this->reset();
    
    if (!file_exists($fileFullPath)) {
      return FALSE;
    }
    if (!is_readable($fileFullPath)) {
      return FALSE;
    }
    
    $this->_fileFullPath = $fileFullPath;
    
    $fileContent = file($fileFullPath);
    $length=(strlen(@implode("",$fileContent))/1024)/1000;
    if($length>1){error_log("Loading big file !!! $fileFullPath $length Mb");}
    $this->_parseFromArray($fileContent);
    
    return TRUE;
  }
  
  
  function loadString($str) {
    $this->reset();
    //error_log("Loading ". strlen($str) . " bytes");
    $arr = explode("\n", $str);
    $this->_parseFromArray($arr);
    
    return TRUE;
  }

  function setQuoteHandling($mode=BS_INIHANDLER_UNQUOTE_ALL) {
    $this->unQuote = $mode;
  }

  function _parseFromArray($arr) {
    $this->_comments = array();
    $comment = array();
    $section = '';
    if(count($arr)>100000){
    	error_log("Warning, Array expected ". count($arr). "(path=" .$this->_fileFullPath.") in ". __FILE__. " line ". __LINE__);
    	$this->reset();
    	return null;
    }
    foreach($arr as $line) {
      $sectionFound = $valueFound = FALSE;
      $param = array('key'=>'', 'val'=>'');
      do { // try
        $line = trim($line);
        
        # Skip empty lines
        if (empty($line)) break; // try
        

        if (in_array($line[0], $this->commentChars)) {
          $comment[] = $line;
          break; // try
        }
        

        if ($line[0] === '[') {
                    if (preg_match('/\[(.*)\]/', $line, $ar)) {
              $section = $ar[1];
              $sectionFound = TRUE;
              break; // try
                    }
        }
        
        # Parameter
        // split 1x at first '='
        $tmp = explode('=', $line);
        if (!is_array($tmp)) break; // try 
        if (sizeOf($tmp) < 2) {
          $comment[] = @$tmp[0];
          break;
        }
        
        $param['key'] = trim($tmp[0]);
        array_shift($tmp);
        if (sizeOf($tmp)>1) $tmp[0] = implode('=', $tmp);
        $param['val'] = isSet($tmp[0]) ? trim($tmp[0]) : '';
        if (empty($param['val'])) {
          $valueFound = TRUE;
          break; // try
        }
        
        
        $unQuote = '';
        if ($this->unQuote & BS_INIHANDLER_UNQUOTE_DOUBLE) $unQuote .= '"';
        if ($this->unQuote & BS_INIHANDLER_UNQUOTE_SINGLE) $unQuote .= "'";
        if (empty($unQuote)) {
          $valueFound = TRUE;
          break; // try
        }
        
        // trim quote
        $regEx = '/^(['.$unQuote.']?)(.*)\1$/';
        if (preg_match($regEx, $param['val'], $ar)) {
          $param['val'] = $ar[2];
          $valueFound = TRUE;
          break; // try
        } else {
          break; // try
        }
      } while(FALSE);
      
      if ($sectionFound) {
        $this->_sections[] = $section;
        if (!empty($comment)) $this->_comments[$section] = $comment;
        $comment = array();
      } else if ($valueFound) {
        $this->_params[$section][$param['key']] = $param['val'];
        if (!empty($comment)) $this->_comments[$section .'__'. $param['key']] = $comment;
        $comment = array();
      }
    } // foreach
    if (!empty($comment)) $this->_comments['__LastComment__'] = $comment;
  }
  
  
  /**
  * 
  */
  function toString() {
    $outStr = BS_INIHANDLER_HEAD_COMMENT;
    if(!is_array($this->_params)){return "#";}
    foreach ($this->_params as $section => $params) {
      if (isSet($this->_comments[$section])) {
                $oldHeaderFound = FALSE;
                foreach ($this->_comments[$section] as $comment) {
                  if (!$oldHeaderFound AND (trim(BS_INIHANDLER_HEAD_COMMENT) == trim($comment))) {
                      $oldHeaderFound = TRUE;
                        continue;
                    }
                    $outStr .= "{$comment}\n";
        }
            }
      $outStr .= "[".$section."]\n"; 
      foreach ($params as $key => $value) {
        if (isSet($this->_comments[$section .'__'. $key])) {
          foreach ($this->_comments[$section .'__'. $key] as $comment) $outStr .= "  {$comment}\n";
        }
        $outStr .= $key. "=" .$value. "\n"; 
      }
      $outStr .= "\n";
    }
    
    if (isSet($this->_comments['__LastComment__'])) {
      foreach ($this->_comments['__LastComment__'] as $comment) $outStr .= "{$comment}\n";
    }
    return $outStr;
  }

  function saveFile($fileFullPath=null) {
    $outStr = $this->toString();
    if (empty($fileFullPath)) $fileFullPath = $this->_fileFullPath;
    if (empty($fileFullPath)) return FALSE;
    if (!$fp = @fopen($fileFullPath, 'wb')) {
      //Bs_Error::setError("Failed open the file for writing: [{$fileFullPath}]", 'ERROR');
      return FALSE;
    }
    if (!@fwrite($fp, $outStr)){
      //Bs_Error::setError("Failed to write (but was able to open) the file: [{$fileFullPath}]", 'ERROR');
      return FALSE;
    }
    
    @fclose($fp);
    return TRUE;
  }
  
 
  function reset() {
    unset($this->_sections);
    unset($this->_params);
    unset($this->_fileFullPath);
  }
  
 
  function get($section=NULL, $key=NULL) {
    if (is_null($section)) return $this->_params;
    if (!isSet($this->_params[$section])) return NULL; //throw
    if (is_null($key))     return $this->_params[$section];
    if (!isSet($this->_params[$section][$key])) return NULL; //throw
    return $this->_params[$section][$key];
  }
  

  function has($section, $key=NULL) {
    if (is_null($key)) {
      return (isSet($this->_params[$section])); //using _params instead of _sections cause it's a hash. in_array is slower.
    } else {
      return (isSet($this->_params[$section]) && isSet($this->_params[$section][$key]));
    }
  }
 
    function set($section, $key, $value) {
    if (!isSet($this->_params))           $this->_params = array();
    if (!isSet($this->_params[$section])) $this->_params[$section] = array();
        $this->_params[$section][$key] = $value;
    }
}