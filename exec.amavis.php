<?php
include(dirname(__FILE__).'/ressources/class.amavis.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');

	
	if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
	if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;}
	$GLOBALS["EXECUTED_AS_ROOT"]=true;
	
	
	
	
	
	echo "Starting......: amavisd-new build configuration\n";
	
	$amavis=new amavis();
	$amavis->CheckDKIM();
	$conf=$amavis->buildconf();
	
	PatchPyzor();
	
	$tpl[]="template-spam-admin.txt";
	$tpl[]="template-spam-sender.txt";
	$tpl[]="template-dsn.txt";
	$tpl[]="template-virus-admin.txt";
	$tpl[]="template-virus-recipient.txt";
	$tpl[]="template-virus-sender.txt";
	
	@mkdir("/usr/local/etc/amavis",0755,true);
	while (list ($index, $file) = each ($tpl)){
		if(!is_file("/usr/local/etc/amavis/$file")){
			echo "Starting......: amavisd-new installing template $file\n";
			@copy("/usr/share/artica-postfix/bin/install/amavis/$file","/usr/local/etc/amavis/$file");
			
		}
	}
	
	
	echo "Starting......: amavisd-new ". strlen($conf)." bytes length\n";
	@file_put_contents("/usr/local/etc/amavisd.conf",$conf);
	shell_exec("/bin/chown -R postfix:postfix /etc/amavis/dkim >/dev/null 2>&1");
	shell_exec("/bin/chown -R postfix:postfix /usr/local/etc/amavis >/dev/null 2>&1");
	shell_exec("/bin/chown -R postfix:postfix /usr/local/etc/amavis/* >/dev/null 2>&1");
	shell_exec("/bin/chown root:root /var/amavis-plugins/check-external-users.conf");
	shell_exec("/bin/chown root:root /var/amavis-plugins");
	shell_exec("/bin/chmod 755 /var/amavis-plugins");
	shell_exec("/bin/chmod -R 755 /etc/amavis/dkim >/dev/null 2>&1");
	shell_exec("/bin/chmod -R 755 /usr/local/etc/amavis >/dev/null 2>&1");
	shell_exec("/bin/chmod -R 755 /usr/local/etc/amavis/* >/dev/null 2>&1");
	
	
	if(is_dir("/etc/mail/spamassassin")){
		shell_exec("/bin/chmod -R 666 /etc/mail/spamassassin");
		shell_exec("/bin/chown -R postfix:postfix /etc/mail/spamassassin");
		shell_exec("/bin/chmod 755 /etc/mail/spamassassin");		
	}
	if(is_dir("/etc/spamassassin")){
		shell_exec("/bin/chmod -R 666 /etc/spamassassin");
		shell_exec("/bin/chmod 755 /etc/spamassassin");
		shell_exec("/bin/chown -R postfix:postfix /etc/spamassassin");
	}
	
	if(is_dir("/var/lib/spamassassin")){
		shell_exec("/bin/chmod -R 755 /var/lib/spamassassin");
		shell_exec("/bin/chown -R postfix:postfix /var/lib/spamassassin");
	}	
	

	
	echo "Starting......: amavisd-new done\n";

	$unix=new unix();
	$unix->THREAD_COMMAND_SET($unix->LOCATE_PHP5_BIN()." /usr/share/artica-postfix/exec.spamassassin.php");
	
	
function PatchPyzor(){
	$unix=new unix();
	$pyzor=$unix->find_program("pyzor");
	if(!is_file($pyzor)){
		echo "Starting......: amavisd-new pyzor is not installed\n";
		return;
	}
	
	$f[]="#!/usr/bin/python -W ignore::DeprecationWarning";
	$f[]="import os";
	$f[]="os.umask(0077)";
	$f[]="import pyzor.client";
	$f[]="pyzor.client.run()";	

	@file_put_contents($pyzor, @implode("\n", $f));
	echo "Starting......: amavisd-new pyzor is now patched\n";

}







	
	
?>