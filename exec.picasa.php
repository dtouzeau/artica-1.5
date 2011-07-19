<?php
include_once(dirname(__FILE__).'/framework/frame.class.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.user.inc');
	
	if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
	if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
	if(preg_match("#--debug#",implode(" ",$argv))){$GLOBALS["VERBOSE2"]=true;}


user_sessions();
function user_sessions(){
	require_once 'Zend/Loader.php';
	//require_once 'externals/Zend/Gdata/Photos/AlbumQuery.php';
	Zend_Loader::loadClass('Zend_Gdata_Photos');
	Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
	Zend_Loader::loadClass('Zend_Gdata_AuthSub');
	Zend_Loader::loadClass('Zend_Gdata_App_HttpException');  
	Zend_Loader::loadClass('Zend_Gdata_Docs');  
	Zend_Loader::loadClass('Zend_Http_Client_Exception');  
	Zend_Loader::loadClass('Zend_Http_Client');  
	Zend_Loader::loadClass('Zend_Http_Client_Adapter_Proxy');  	
	$serviceName = Zend_Gdata_Photos::AUTH_SERVICE_NAME;
	
	$sql="SELECT * FROM picasa WHERE enabled=1";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($ligne["uid"]==null){continue;}
		$user=new user($ligne["uid"]);
		if($user->homeDirectory==null){continue;}
		
		
		$localfolder=$ligne["localfolder"];
		if($localfolder==null){$localfolder="MyPhotos";}
		$homeDirectory="$user->homeDirectory/$localfolder";			
		@unlink("/var/log/artica-postfix/artica-picasa.log");		
		if($GLOBALS["VERSBOSE"]){events("Parsing directory for $homeDirectory",__FUNCTION__,__FILE__,__LINE__);}	
		$arrayPhotos=ParseDirectory($homeDirectory);
		if(!is_array($arrayPhotos)){
			if($GLOBALS["VERSBOSE"]){events("Parsing directory for {$ligne["uid"]} no images files",__FUNCTION__,__FILE__,__LINE__);}
			continue;	
		}

		$GLOBALS["ByPassProxy"]=$ligne["ByPassProxy"];
		if($ligne["album_name"]==null){$ligne["album_name"]="default";}
		
		$user = $ligne["username"];
		$pass = $ligne["password"];
		
		if(!InitClient($user,$pass,$serviceName)){
			send_email_events("Picasa: {$ligne["uid"]} failed to connect to Picasa web services",@file_get_contents("/var/log/artica-postfix/artica-picasa.log"),"system");
			events("Aborting, communication or authentication failed",__FUNCTION__,__FILE__,__LINE__);
			return;
		}
	
		events("Init Zend_Gdata_Photos ".count($arrayPhotos)." photos",__FUNCTION__,__FILE__,__LINE__);
		$gp = new Zend_Gdata_Photos($GLOBALS["GA_CLIENT"], "Google-DevelopersGuide-1.0");
	
		$success=0;
		$failed=0;
		$filesCount=0;
		$target_directory="$homeDirectory/Uploaded";
		if(!is_dir($target_directory)){
			@mkdir($target_directory,755,true);
			shell_exec("/bin/chown {$ligne["uid"]} $target_directory");
			shell_exec("/bin/chmod 755 $target_directory");
		}
		
		while (list ($filename, $basename) = each ($arrayPhotos) ){
			if(!is_file($filename)){continue;}
			$filesCount++;
			events("Uploading $basename",__FUNCTION__,__FILE__,__LINE__);
			if(UploadPhoto($gp,$filename,$ligne["album_name"])){
				events("moving $basename success",__FUNCTION__,__FILE__,__LINE__);
				rename($filename,"$target_directory/".basename($filename));
				$success++;
			}else{
				events("Uploading $basename FAILED",__FUNCTION__,__FILE__,__LINE__);
				$failed++;
				continue;
			}
		}
		if($filesCount>0){
			events("uploading photos for {$ligne["uid"]} success=$success, failed=$failed",__FUNCTION__,__FILE__,__LINE__);
			send_email_events("Picasa: {$ligne["uid"]} $success success photos, $failed failed photos",
			@file_get_contents("/var/log/artica-postfix/artica-picasa.log"),"system");
		}
		
	}
}

function ParseDirectory($homeDirectory){
	
	
	$allowed_ext["jpg"]=true;
	$allowed_ext["gif"]=true;
	$allowed_ext["bmp"]=true;
	$allowed_ext["psd"]=true;
	$allowed_ext["avi"]=true;
	$allowed_ext["mov"]=true;
	$allowed_ext["mpg"]=true;
	$allowed_ext["wmv"]=true;
	$allowed_ext["asf"]=true;
	$allowed_ext["tif"]=true;
	$allowed_ext["png"]=true;
	
	
	if(!is_dir($homeDirectory)){return;}
	foreach (glob("$homeDirectory/*") as $filename) {
		if(!is_file($filename)){continue;}
		$file=basename($filename);
		$ext=file_ext(strtolower($file));
		writelogs("$filename ($ext)",__FUNCTION__,__FILE__,__LINE__);
		if(!$allowed_ext){continue;}
		$array[$filename]=$file;
	}
	
	return $array;
}

function InitClient($user,$pass, $serviceName){
	if($GLOBALS["ByPassProxy"]<>1){
		if(!is_array($GLOBALS["ArticaProxySettings"])){
			$ini2=new Bs_IniHandler();
			$sock=new sockets();
			$datas=$sock->GET_INFO("ArticaProxySettings");
			$ini2->loadString($datas);
			$GLOBALS["ArticaProxySettings"]=$ini2;
		}
			
			$ini2=$GLOBALS["ArticaProxySettings"];
			$ArticaProxyServerEnabled=$ini2->_params["PROXY"]["ArticaProxyServerEnabled"];
			if($ArticaProxyServerEnabled=="yes"){return _GetClientProxy($user,$pass, $serviceName);}
	}
		return _GetClientDirect($user,$pass, $serviceName);
	}

function _GetClientDirect($user,$pass, $serviceName){
	events("Init authentication as $user (direct mode)",__FUNCTION__,__FILE__,__LINE__);
	
	try {$client = Zend_Gdata_ClientLogin::getHttpClient($user, $pass, $serviceName);} 
	catch (Zend_Gdata_App_AuthException $e) {
		events("Error: Direct: " . $e->getMessage(),__FUNCTION__,__FILE__,__LINE__);
		return false; 
	}	
	$GLOBALS["GA_CLIENT"]=$client;
	return true;	
}


function _GetClientProxy($user,$pass, $serviceName){
	
		$ini2=$GLOBALS["ArticaProxySettings"];
		$ArticaProxyServerEnabled=$ini2->_params["PROXY"]["ArticaProxyServerEnabled"];
		$ArticaProxyServerName=$ini2->_params["PROXY"]["ArticaProxyServerName"];
		$ArticaProxyServerPort=$ini2->_params["PROXY"]["ArticaProxyServerPort"];
		$ArticaProxyServerUsername=$ini2->_params["PROXY"]["ArticaProxyServerUsername"];
		$ArticaProxyServerUserPassword=$ini2->_params["PROXY"]["ArticaProxyServerUserPassword"];
		events("ArticaProxyServerEnabled {$ini2->_params["PROXY"]["ArticaProxyServerEnabled"]} http://{$ini2->_params["PROXY"]["ArticaProxyServerName"]}:{$ini2->_params["PROXY"]["ArticaProxyServerPort"]}",__FUNCTION__,__FILE__,__LINE__);
		$httpConfig = array(
               'adapter'      => 'Zend_Http_Client_Adapter_Proxy',
               'proxy_host'   => $ArticaProxyServerName,
               'proxy_port'   => $ArticaProxyServerPort,
			   'proxy_user'    => $ArticaProxyServerUsername,
        	   'proxy_pass'    => $ArticaProxyServerUserPassword,

              );	
	$clientWeb = new Zend_Gdata_HttpClient("http://www.google.com:443",$httpConfig);
	events("Init authentication as $user (proxy mode)",__FUNCTION__,__FILE__,__LINE__);
	
	try {$client = Zend_Gdata_ClientLogin::getHttpClient($user,$pass, $serviceName, $clientWeb, 'project-test-1.0');} 
	
	catch (Zend_Gdata_App_AuthException $e) {
		events("Error: Proxy: " . $e->getMessage(),__FUNCTION__,__FILE__,__LINE__);
		return false; 
	}		

 	//$client->setConfig($httpConfig);
 	$GLOBALS["GA_CLIENT"]=$client; 
	return true;
  
}


function UploadPhoto($gp,$filename,$AlbumName){
$BaseName=basename($filename);	
$extention=file_ext($BaseName);
$photoName = str_replace(".$extention","",$BaseName);
$photoName = str_replace("_"," ",$photoName);
$photoName = str_replace("-"," ",$photoName);
$date=date('Y-m-d H:i');
$photoCaption = "Uploaded on $date" ;
$last_modified = filemtime($filename);
$photoTags = date("Y-m-d",$last_modified);

// We use the albumId of 'default' to indicate that we'd like to upload
// this photo into the 'drop box'.  This drop box album is automatically 
// created if it does not already exist.
//

if($AlbumName=="default"){$albumId = "default";}

$exts["jpg"]="image/jpeg";
$exts["gif"]="image/gif";
$exts["bmp"]="image/bmp";
$exts["psd"]="application/photoshop";
$exts["avi"]="video/x-msvideo";
$exts["mov"]="video/quicktime";
$exts["mpg"]="video/mpeg";
$exts["wmv"]="video/x-ms-wmv";
$exts["asf"]="video/x-ms-asf";
$exts["tif"]="image/tiff";
$exts["png"]="image/png";

$fd = $gp->newMediaFileSource($filename);
$fd->setContentType($exts[$extention]);

// Create a PhotoEntry
$photoEntry = $gp->newPhotoEntry();

$photoEntry->setMediaSource($fd);
events("$BaseName: title: $photoName",__FUNCTION__,__FILE__,__LINE__);
$photoEntry->setTitle($gp->newTitle($photoName));
$photoEntry->setSummary($gp->newSummary($photoCaption));

// add some tags
$keywords = new Zend_Gdata_Media_Extension_MediaKeywords();
$keywords->setText($photoTags);
$photoEntry->mediaGroup = new Zend_Gdata_Media_Extension_MediaGroup();
$photoEntry->mediaGroup->keywords = $keywords;
$albumQuery = $gp->newAlbumQuery();
//$albumQuery->setUser("David Touzeau");
if($albumId<>null){
	$albumQuery->setAlbumId($albumId);
}else{
	$albumQuery->setAlbumName($AlbumName);
}
//

try{
	$insertedEntry = $gp->insertPhotoEntry($photoEntry, $albumQuery->getQueryUrl());	
} 
catch (Zend_Gdata_App_HttpException $e) {
	events("Error: Zend_Gdata_App_HttpException:: " . $e->getMessage(),__FUNCTION__,__FILE__,__LINE__);
	return false; 
}	
catch (Zend_Uri_Exception $e) {
		events("Error: Zend_Uri_Exception:: " . $e->getMessage(),__FUNCTION__,__FILE__,__LINE__);
		return false; 
}	
return true;	
	
 	
	
}
function events($text=null,$function,$file=null,$line=0){
		$pid=@getmypid();
		$file=basename(__FILE__);
		$date=@date("H:i:s");
		$logFile="/var/log/artica-postfix/artica-picasa.log";
		$size=@filesize($logFile);
		if($size>1000000){@unlink($logFile);}
		$f = @fopen($logFile, 'a');
		$text="$file[$pid] $date $function:: $text (L.$line)\n";
		if($GLOBALS["VERBOSE"]){echo $text;}
		@fwrite($f, $text);
		@fclose($f);	
		}	

?>