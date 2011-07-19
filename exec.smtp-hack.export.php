<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__) . '/ressources/class.users.menus.inc');
include_once(dirname(__FILE__) . '/ressources/class.templates.inc');
include_once(dirname(__FILE__) . '/framework/class.unix.inc');
include_once(dirname(__FILE__) . '/framework/frame.class.inc');
include_once(dirname(__FILE__) . '/ressources/class.ldap.inc');
include_once(dirname(__FILE__) . '/ressources/class.maincf.multi.inc');




if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["DEBUG"]=true;$GLOBALS["VERBOSE"]=true;ini_set('display_errors', 1);ini_set('error_reporting', E_ALL);ini_set('error_prepend_string',null);ini_set('error_append_string',null);}

if($argv[1]=="--export"){export();exit;}
if($argv[1]=="--export-datas"){export_datas();exit;}
if($argv[1]=="--send-datas"){export_datas_to_repo();exit;}
if($argv[1]=="--import"){import();exit;}



function import(){
include_once("HTTP/Request.php");
	$ArticaProxyServerEnabled="no";
	$sock=new sockets();
	$ArticaMetaEnabled=$sock->GET_INFO("ArticaMetaEnabled");
	$datas=$sock->GET_INFO("ArticaProxySettings");
	if($ArticaMetaEnabled==null){$ArticaMetaEnabled=0;}
	if(strlen($datas)>5){
		$ini=new Bs_IniHandler();
		$ini->loadString($datas);
		$ArticaProxyServerEnabled=$ini->_params["PROXY"]["ArticaProxyServerEnabled"];
		$ArticaProxyServerName=$ini->_params["PROXY"]["ArticaProxyServerName"];
		$ArticaProxyServerPort=$ini->_params["PROXY"]["ArticaProxyServerPort"];
		$ArticaProxyServerUsername=$ini->_params["PROXY"]["ArticaProxyServerUsername"];
		$ArticaProxyServerUserPassword=$ini->_params["PROXY"]["ArticaProxyServerUserPassword"];
	}

	
	$req =new HTTP_Request("http://www.artica.fr/smtphack-import.php?export=yes");  
	$req->setURL("http://www.artica.fr/smtphack-import.php?export=yes");	
	$req->setMethod(HTTP_REQUEST_METHOD_GET);
	if($ArticaProxyServerEnabled=="yes"){$req->setProxy($ArticaProxyServerName, $ArticaProxyServerPort, $ArticaProxyServerUsername, $ArticaProxyServerUserPassword);}
	
	$req->sendRequest();
	$code = $req->getResponseCode();
	$datas= trim($req->getResponseBody());
	$array=unserialize(base64_decode($datas));
	if(!is_array($array)){return;}
	
	$sock=new sockets();
	$q=new mysql();
	$count_before=$q->COUNT_ROWS("iptables","artica_backup");
	if($GLOBALS["DEBUG"]){echo "import:: before: $count_before array of ".count($array)." rows";}
	while (list ($num, $sql) = each ($array) ){
		$q=new mysql();
		$q->QUERY_SQL($sql,"artica_backup");
		if($GLOBALS["DEBUG"]){echo "import:: SQL:: $sql\n";}
		if(!$q->ok){if($GLOBALS["DEBUG"]){echo "import:: ERROR:: $q->mysql_error\n";}}
	}
	$q=new mysql();
	$count_after=$q->COUNT_ROWS("iptables","artica_backup");
	if($GLOBALS["DEBUG"]){echo "import:: after: $count_after array of ".count($array)." rows\n";}
	if($count_after<>$count_before){
		$added=$count_after-$count_before;
		if($GLOBALS["DEBUG"]){echo "import:: Send email notification for $added rows\n";}
		if($GLOBALS["DEBUG"]){echo "import:: SMTP HACK: $added iptables rules from Artica repository\n";}
		send_email_events("SMTP HACK: $added iptables rules from Artica repository","Check SMTP Hack panel to see new rules added","update");
		if($GLOBALS["DEBUG"]){$add_verb=" --verbose";}
		shell_exec(LOCATE_PHP5_BIN2()." ".dirname(__FILE__)."/exec.postfix.iptables.php --compile$add_verb");
		if($ArticaMetaEnabled==1){
			shell_exec(LOCATE_PHP5_BIN2()." ".dirname(__FILE__)."/exec.artica.meta.users.php --iptables");
		}
		
		
	}
	
}


function export(){
include_once("HTTP/Request.php");

	
	if(!class_exists("HTTP_Request")){
	 	if($GLOBALS["VERBOSE"]){echo "export:: HTTP_Request doesn't exists\n";}
		$unix=new unix();
		$pear_bin=$unix->find_program("pear");
		if($GLOBALS["DEBUG"]){echo "export:: pear=$pear_bin\n";}
		if($pear_bin==null){
			if($GLOBALS["DEBUG"]){echo "export:: pear binary no exists\n";}
		   	send_email_events("SMTP HACK: unable to stat pear binary","Trying to install HTTP_Request pear package but pear seems not to be installed","update");		
		   	return;
		}
		
		if($GLOBALS["DEBUG"]){echo "export:: $pear_bin install HTTP_Request\n";}
		shell_exec("$pear_bin channel-update pear.php.net");
		shell_exec("$pear_bin install HTTP_Request");
		return;
	}
	import();
	if(export_datas()==0){return;}
	if(export_datas_to_repo()){export_datas_flush();}
	
}

function export_datas_to_repo(){
	$file="/etc/artica-postfix/smtp.hacks.export.db";
	if(!is_file($file)){echo "$file no such file\n";return;}
	include_once("HTTP/Request.php");
	$ini=new Bs_IniHandler();
	$sock=new sockets();
	$datas=$sock->GET_INFO("ArticaProxySettings");
	$ini->loadString($datas);
	$ArticaProxyServerEnabled=$ini->_params["PROXY"]["ArticaProxyServerEnabled"];
	$ArticaProxyServerName=$ini->_params["PROXY"]["ArticaProxyServerName"];
	$ArticaProxyServerPort=$ini->_params["PROXY"]["ArticaProxyServerPort"];
	$ArticaProxyServerUsername=$ini->_params["PROXY"]["ArticaProxyServerUsername"];
	$ArticaProxyServerUserPassword=$ini->_params["PROXY"]["ArticaProxyServerUserPassword"];
		
	$req =new HTTP_Request("http://www.artica.fr/smtphack-import.php");  
	$req->setURL("http://www.artica.fr/smtphack-import.php");	
	$req->setMethod('POST');
	if($ArticaProxyServerEnabled=="yes"){
			$req->setProxy($ArticaProxyServerName, $ArticaProxyServerPort, $ArticaProxyServerUsername, $ArticaProxyServerUserPassword); 
	}	
	$req->addPostData('xyz', time()); 
	$result = $req->addFile('smtp-hack-file', $file, 'multipart/form-data');
	
//	$req->sendRequest();
	if (PEAR::isError($result)) {
    	echo $result->getMessage();
	} else {

    $response = $req->sendRequest();
    if (PEAR::isError($response)) {echo $response->getMessage();return false;} 
    if(preg_match("#SUCCESS#",$req->getResponseBody())){
   		 if($GLOBALS["DEBUG"]){echo "Central server success\n".$req->getResponseBody()."\n";}
    	return true;
    }
    
    if($GLOBALS["DEBUG"]){echo "Central server failed\n".$req->getResponseBody()."\n";}
    
}	
	
	
}


function export_datas(){
	$sql="SELECT * FROM iptables WHERE local_port=25 AND flux='INPUT' AND sended=0 and community IS NULL and disable=0";
	$q=new mysql();
	$sock=new sockets();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	$users=new usersMenus();
	$hostname=$users->hostname;
	if(!$q->ok){
		if($GLOBALS["DEBUG"]){echo "export_datas:: $q->mysql_error\n";}
		send_email_events("SMTP HACK: Mysql error while exporting...","$q->mysql_error","update");
		return 0;
	}
	
	
	$SYSTEMID=$sock->GET_INFO("SYSTEMID");
	if($SYSTEMID==null){
		shell_exec("/usr/share/artica-postfix/bin/artica-update --system-id");
		$SYSTEMID=$sock->GET_INFO("SYSTEMID");	
	}	
	
	$num_rows = mysql_num_rows($results);
	if($num_rows==0){
		if($GLOBALS["DEBUG"]){echo "export_datas:: mysql_num_rows=0\n";}
		return 0;}
	
	if($GLOBALS["DEBUG"]){echo "export_datas:: mysql_num_rows=$num_rows\n";}
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		$ARRAY[$ligne["serverip"]]=array(
			"servername"=>$ligne["servername"],
			"serverip"=>$ligne["serverip"],
			"artica-hostname"=>$hostname,
			"artica-id"=>$SYSTEMID);
	}
	
	@file_put_contents("/etc/artica-postfix/smtp.hacks.export.db",base64_encode(serialize($ARRAY)));
	return $num_rows;
	
}

function export_datas_flush(){
	$q=new mysql();
	$sql="UPDATE iptables SET sended=1 WHERE local_port=25 AND flux='INPUT' AND sended=0 and community IS NULL and disable=0";
	$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		if($GLOBALS["DEBUG"]){echo "export_datas_flush:: $q->mysql_error\n";}
		send_email_events("SMTP HACK: Mysql error while flag exported rules...","$q->mysql_error","update");
		return;
	}
	if($GLOBALS["DEBUG"]){echo "export_datas_flush:: Success\n";}
	
}



	
	
//exec.smtp-hack.export.php