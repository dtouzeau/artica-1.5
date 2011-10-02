<?php
include_once(dirname(__FILE__)."/frame.class.inc");
include_once(dirname(__FILE__)."/class.unix.inc");
include_once(dirname(__FILE__)."/class.postfix.inc");



if(isset($_GET["locales"])){locales();exit;}
if(isset($_GET["foldersnames"])){foldersnames();exit;}
if(isset($_GET["zarafa-user-create-store"])){zarafa_user_create_store();exit;}
if(isset($_GET["DbAttachConverter"])){DbAttachConverter();exit;}
if(isset($_GET["mbx-infos"])){mbx_infos();exit;}
if(isset($_GET["csv-export"])){csv_export();exit;}
if(isset($_GET["removeidb"])){removeidb();exit;}
if(isset($_GET["zarafa-orphan-kill"])){orphan_delete();exit();}
if(isset($_GET["zarafa-orphan-link"])){orphan_link();exit();}
if(isset($_GET["zarafa-orphan-scan"])){orphan_scan();exit();}
if(isset($_GET["getversion"])){getversion();exit();}
if(isset($_GET["restart"])){restart();exit();}




while (list ($num, $ligne) = each ($_GET) ){$a[]="$num=$ligne";}
writelogs_framework("unable to unserstand ".@implode("&",$a),__FUNCTION__,__FILE__,__LINE__);

function orphan_link(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$zarafa_admin=$unix->find_program("zarafa-admin");
	$cmd="$zarafa_admin --hook-store {$_GET["zarafa-orphan-link"]} -u {$_GET["uid"]}";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);	
	$php5=$unix->LOCATE_PHP5_BIN();
	$cmd="$php5 /usr/share/artica-postfix/exec.zarafa.build.stores.php --exoprhs --nomail";
	$unix->THREAD_COMMAND_SET($cmd);
}
function orphan_scan(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$php5=$unix->LOCATE_PHP5_BIN();
	$cmd="$php5 /usr/share/artica-postfix/exec.zarafa.build.stores.php --exoprhs --nomail";
	$unix->THREAD_COMMAND_SET($cmd);
}
function locales(){
	$unix=new unix();
	$locale=$unix->find_program("locale");
	exec("$locale -a 2>&1",$results);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
}

function foldersnames(){
	$unix=new unix();
	$php5=$unix->LOCATE_PHP5_BIN();
	$zarafa=$unix->find_program("zarafa-server");
	exec("$zarafa -V 2>&1",$results);
	$major=6;
	while (list ($num, $ligne) = each ($results) ){
		if(preg_match("Product version:\s+([0-9]+),#", $re)){$major=$re[1];break;}
	}
	
	writelogs_framework("zarafa-server version $major.x",__FUNCTION__,__FILE__,__LINE__);
	
	if($major==6){
		writelogs_framework("-> exec.zarafa6.foldersnames.php",__FUNCTION__,__FILE__,__LINE__);
		shell_exec("$php5 /usr/share/artica-postfix/exec.zarafa6.foldersnames.php {$_GET["uid"]} {$_GET["lang"]}");
	}
	if($major==7){
		writelogs_framework("-> exec.zarafa7.foldersnames.php",__FUNCTION__,__FILE__,__LINE__);
		shell_exec("$php5 /usr/share/artica-postfix/exec.zarafa7.foldersnames.php {$_GET["uid"]} {$_GET["lang"]}");
	}	
	
	
}
function zarafa_user_create_store(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	if(strlen($nohup)>3){$nohup="$nohup ";}
	$zarafa_admin=$unix->find_program("zarafa-admin");
	$cmd="$nohup $zarafa_admin --create-store {$_GET["zarafa-user-create-store"]} --lang {$_GET["lang"]} >/dev/null 2>&1 &";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);
	$cmd="$nohup $zarafa_admin --lang {$_GET["lang"]} --create-store {$_GET["zarafa-user-create-store"]} >/dev/null 2>&1 &";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);	
	
}

function DbAttachConverter(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$perl=$unix->find_program("perl");
	$sqladm=base64_decode($_GET["sqladm"]);
	$sqlpass=base64_decode($_GET["mysqlpass"]);
	$sqlpass=$unix->shellEscapeChars($sqlpass);
	$path=$_GET["path"];
	if(!is_dir($path)){@mkdir($path,644,true);}
	$cmd="$nohup $perl /usr/share/doc/zarafa/db-convert-attachments-to-files $sqladm $sqlpass zarafa $path delete >/dev/null 2>&1 &";	
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);	
}

function mbx_infos(){
	$unix=new unix();
	$zarafa_admin=$unix->find_program("zarafa-admin");
	exec("$zarafa_admin --details {$_GET["mbx-infos"]} 2>&1",$results);
	echo "<articadatascgi>". base64_encode(serialize($results))."</articadatascgi>";
}
function orphan_delete(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");
	$zarafa_admin=$unix->find_program("zarafa-admin");
	$cmd="$zarafa_admin --remove-store {$_GET["zarafa-orphan-kill"]}";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);	
	$php5=$unix->LOCATE_PHP5_BIN();
	$cmd="$php5 /usr/share/artica-postfix/exec.zarafa.build.stores.php --exoprhs --nomail";
	$unix->THREAD_COMMAND_SET($cmd);
}

function csv_export(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");	
	$php5=$unix->LOCATE_PHP5_BIN();
	$cmd=trim("$nohup $php5 /usr/share/artica-postfix/exec.zarafa.contacts-zarafa.php --export-zarafa {$_GET["uid"]} >/dev/null 2>&1 &");
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);		
}

function restart(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");	
	$cmd=trim("$nohup /etc/init.d/artica-postfix restart zarafa >/dev/null 2>&1 &");
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);		
}

function removeidb(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");	
	$php5=$unix->LOCATE_PHP5_BIN();
	$cmd=trim("$nohup $php5 /usr/share/artica-postfix/exec.zarafa.build.stores.php --remove-database >/dev/null 2>&1 &");
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);	
}
function getversion(){
	$unix=new unix();
	$zarafa_server=$unix->find_program("zarafa-server");
	if(strlen($zarafa_server)<5){return null;}
	exec("$zarafa_server -V 2>&1",$results);
	while (list ($num, $ligne) = each ($results) ){
		if(preg_match("#Product version:\s+([0-9,]+)#", $ligne,$re)){
			$version=trim($re[1]);
			$version=str_replace(",", ".", $version);
			echo "<articadatascgi>$version</articadatascgi>";
			return;
		}
	}
}

