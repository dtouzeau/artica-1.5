<?php
if(!is_file('/etc/postfix/main.cf')){die();}
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.user.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__).'/framework/frame.class.inc');


WhiteListed();

function WhiteListed(){
	$ldap=new clladp();
	
	$whites=$ldap->WhitelistsFromDomain();

$unix=new unix();
$unix->miltergreylist_cleanconf();
$SPAMASSASSIN_LOCAL_CF=$unix->SPAMASSASSIN_LOCAL_CF();
$spammassDirectory=dirname($SPAMASSASSIN_LOCAL_CF);
$assp=array();
$spamassassin=array();
$miltergrey=array();

if(is_array($whites)){
	while (list ($to, $array) = each ($whites)){
		$spamassassin[]="#rcpt :$to";
		while (list ($index, $from) = each ($array)){
			if($from=="*@*"){continue;}
			if(preg_match("#(.+?)@(.+)#",$from,$re)){
				$first_part=$re[1];
				$domain=$re[2];
			}else{
				$first_part="*";
				$domain=$from;
			}
			
			$s="$first_part@$domain";
			$s=str_replace("*@",'',$s);
			$s=str_replace("@*",'',$s);
			
			$asspwbl_string="$first_part@$domain";
			$asspwbl_string=str_replace('.','\.',$asspwbl_string);
			$asspwbl_string=str_replace('*','.*?',$asspwbl_string);
			
			$assp[]=$asspwbl_string;
			$sender_scores_sitewide[]="$s\t-7.0";
			$spamassassin[]="whitelist_from\t$first_part@$domain";
			$unix->miltergreylist_addacl($unix->miltergreylist_buildacl("$first_part@$domain"));
		}
		
	}
}

$blacks=$ldap->BlackListFromDomain();
if(is_array($blacks)){
while (list ($to, $array) = each ($blacks)){
		$spamassassin[]="#rcpt :$to";
		while (list ($index, $from) = each ($array)){
			if($from=="*@*"){continue;}
			if(preg_match("#(.+?)@(.+)#",$from,$re)){
				$first_part=$re[1];
				$domain=$re[2];
			}else{
				$first_part="*";
				$domain=$from;
			}
			
			$spamassassin[]="blacklist_from\t$first_part@$domain";
			
		}
		
	}
}


echo "Starting......: writing whitelist/blacklists for ASSP\n";
@mkdir("/usr/share/assp/files");
@mkdir("/usr/local/etc");
@file_put_contents("/usr/share/assp/files/whiteorg.txt",implode("\n",$assp));
echo "Starting......: writing whitelist/blacklists for Amavis\n";
@file_put_contents("/usr/local/etc/sender_scores_sitewide",implode("\n",$sender_scores_sitewide));
@chmod("/usr/local/etc/sender_scores_sitewide",0750);
if(is_file('/usr/local/sbin/amavisd')){
	if(is_file('/usr/local/etc/amavisd.conf')){
		if(is_file('/var/spool/postfix/var/run/amavisd-new/amavisd-new.pid')){
			sys_THREAD_COMMAND_SET('/usr/local/sbin/amavisd -c /usr/local/etc/amavisd.conf -P /var/spool/postfix/var/run/amavisd-new/amavisd-new.pid reload');
		}
	}
}



echo "Starting......: writing whitelist/blacklists for spamassassin\n";
@file_put_contents("$spammassDirectory/wbl.cf",implode("\n",$spamassassin));
}

function CleanMilterGreyList(){
	
}




?>