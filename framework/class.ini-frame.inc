<?php
define('BS_INIHANDLER_UNQUOTE_NONE',   0);
define('BS_INIHANDLER_UNQUOTE_DOUBLE', 1);
define('BS_INIHANDLER_UNQUOTE_SINGLE', 2);
define('BS_INIHANDLER_UNQUOTE_ALL',    3);
define('BS_INIHANDLER_HEAD_COMMENT',   "#\n\n");


class iniFrameWork {
  	var $commentChars = array('#', '/', ';');
   	var $unQuote = BS_INIHANDLER_UNQUOTE_ALL;
	var $_sections;
  	var $_params=array();
  	var $_comments;
  	var $_fileFullPath;
  
  function iniFrameWork($fileFullPath='') {
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
  	if($GLOBALS["VERBOSE"]){echo "Loading $fileFullPath\n";}
    $length=(strlen(@implode("",$fileContent))/1024)/1000;
    if($length>1){
    	if($GLOBALS["VERBOSE"]){echo "Loading big file !!! $fileFullPath $length Mb\n";}
    }
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
  
  
  /**
  * sets the quote handling.
  * @access public
  * @param  int $mode (see constants)
  * @return void
  */
  function setQuoteHandling($mode=BS_INIHANDLER_UNQUOTE_ALL) {
    $this->unQuote = $mode;
  }
  
  
  /**
  * gets called from loadFile() and loadString() to parse the data.
  * @access private
  * @param  array (vector filled with strings (lines))
  * @return void
  */
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
        
        # Comment (line was trim()ed!)
        if (in_array($line[0], $this->commentChars)) {
          $comment[] = $line;
          break; // try
        }
        
        # Section
                //note: the regexp checking for [] is nice, but i think it's enough if the 
                //first non-white-space is a [. if you change it back, make sure that 
                //the regexp also makes sure there is no = (equals) in front of the [. because 
                //something like 
                // foo = tom[jones]
                //would then be considered a section! 2003-03-22 --andrej
        //if (preg_match('/\[(.*)\]/', $line, $ar)) {
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
          //invalid comment line, whatever.
          //no good if we arrive here. that's some crappy line that should not be in the file.
          //we could issue a warning here.
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
          //the value had unmatching quotes, like "here' or 'here"
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
  
  
  /**
  * saves the ini settings to the file specified.
  * @access public
  * @param  string $fileFullPath (if not specified then the current one will be used. useful for load, modify, save.)
  * @return bool TRUE on sccess, FALSE on error (see getLastError())
  * @see    saveString()
  */
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
  
  /**
  * resets this object so we can re-use it for something else.
  * some setting vars are not reset.
  * 
  * resets:
  *   _sections
  *   _params
  *   _fileFullPath
  *   
  * keeps:
  *   commentChars
  *   unQuote
  * 
  * @access public
  * @return void
  */
  function reset() {
    unset($this->_sections);
    unset($this->_params);
    unset($this->_fileFullPath);
  }
  
  
 /**
  * returns [all parameters|parameter] [for the given section].
  * 
  * examples:
  *   get()                 => returns all sections with all params as 2-D hash.
  *                              array of [<section>][<key>]  => <string>
  *   get('section')        => returns all params for the section specified  as 1-D hash.
  *                              array of [<key>]  => <string>
  *   get('section', 'key') => returns the param specified of the section specified as string.
  * 
  * note: if a param is defined in the 'global scope', use an empty string for the 
  *       $section name. example: get('', 'key')
  * 
  * @access public
  * @param  string $section if not given returns all sections
  * @param  string $key if not given returns all keys
  * @return mixed (see above)
  * @throws null  (if the given section or key does not exist)
  */
  function get($section=NULL, $key=NULL) {
    if (is_null($section)) return $this->_params;
    if (!isSet($this->_params[$section])) return NULL; //throw
    if (is_null($key))     return $this->_params[$section];
    if (!isSet($this->_params[$section][$key])) return NULL; //throw
    return $this->_params[$section][$key];
  }
  
  
  /**
  * tells if the section or key specified is set.
  * 
  * examples:
  *   has('mySection')         => tells if 'mySection' is set
  *   has('mySection', 'myKey' => tells if myKey in mySection is set.
  * 
  * note: case matters!
  * 
  * @access public
  * @param  string $section
  * @param  string $key (default is NULL)
  * @return bool
  */
  function has($section, $key=NULL) {
    if (is_null($key)) {
      return (isSet($this->_params[$section])); //using _params instead of _sections cause it's a hash. in_array is slower.
    } else {
      return (isSet($this->_params[$section]) && isSet($this->_params[$section][$key]));
    }
  }
  
    
    /**
    * sets the given value.
    * @access public
    * @param  string $section
    * @param  string $key
    * @param  string $value
    * @return void
    * @since  bs4.3
    */
    function set($section, $key, $value) {
    if (!isSet($this->_params))           $this->_params = array();
    if (!isSet($this->_params[$section])) $this->_params[$section] = array();
        $this->_params[$section][$key] = $value;
    }
}