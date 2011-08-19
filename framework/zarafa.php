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


while (list ($num, $ligne) = each ($_GET) ){$a[]="$num=$ligne";}
writelogs_framework("unable to unserstand ".@implode("&",$a),__FUNCTION__,__FILE__,__LINE__);


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
function csv_export(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");	
	$php5=$unix->LOCATE_PHP5_BIN();
	$cmd=trim("$nohup $php5 /usr/share/artica-postfix/exec.zarafa.contacts-zarafa.php --export-zarafa {$_GET["uid"]} >/dev/null 2>&1 &");
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);		
}

function removeidb(){
	$unix=new unix();
	$nohup=$unix->find_program("nohup");	
	@unlink("/var/lib/mysql/ib_logfile0");
	@unlink("/var/lib/mysql/ib_logfile1");
	$cmd="$nohup /etc/init.d/artica-postfix restart mysql >/dev/null 2>&1 &";
	writelogs_framework($cmd,__FUNCTION__,__FILE__,__LINE__);
	shell_exec($cmd);	
	 
}
