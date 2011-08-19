<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.status.inc');
include_once(dirname(__FILE__).'/ressources/class.os.system.inc');
include_once(dirname(__FILE__).'/ressources/class.system.network.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["debug"]=true;$GLOBALS["VERBOSE"]=true;}


if($argv[1]=='--reconfigure'){build_conf();die();}
if($argv[1]=='--execute'){execute();die();}


function execute(){
	$pid=getmypid();
	$pidfile="/etc/artica-postfix/".basename(__FILE__).".pid";
	$unix=new unix();
	if($unix->process_exists($unix->get_pid_from_file($pidfile))){
		die();
	}
	
	file_put_contents($pidfile,$pid);
	build_conf();
	if(is_file("/etc/spamassassin/sa-learn-cyrus.conf")){
		exec("/usr/share/artica-postfix/bin/sa-learn-cyrus",$results);
		$unix->send_email_events("Junk learning successfully executed for {$GLOBALS["USERS_LIST_COUNT"]} user(s)", @implode("\n", $results), 'mailbox');
	}
}


function build_conf(){
	
	@unlink("/etc/spamassassin/sa-learn-cyrus.conf");
	$users=new usersMenus();
	if(!$users->cyrus_imapd_installed){return null;}
	if($users->ZARAFA_INSTALLED){return null;}
	if(!$users->spamassassin_installed){return null;}
	
	$usersList=ListUsers();
	if(count($usersList)==0){return null;}
	$unix=new unix();
	$salearn=$unix->find_program("sa-learn");
	$ipurge=$unix->LOCATE_CYRUS_IPURGE();
	$users_list=@implode(" ",$usersList);
	$GLOBALS["USERS_LIST_COUNT"]=count($usersList);
	
	$sock=new sockets();
	$EnableVirtualDomainsInMailBoxes=$sock->GET_INFO("EnableVirtualDomainsInMailBoxes");
	
	if($EnableVirtualDomainsInMailBoxes==1){
		$ldap=new clladp();
		$domains=$ldap->hash_get_all_local_domains();
		while (list ($num, $ligne) = each ($domains) ){
			if(trim($ligne)==null){continue;}
			$doms[]=$ligne;
		}
		
		$domains_list=@implode(" ",$doms);
	}
	
	
	
$l[]="# Configuration for sa-learn-cyrus";
$l[]="#";
$l[]="# hjb -- 2008-02-12";
$l[]="#";
$l[]="# -------------------------------------------------------";
$l[]="# global parameters";
$l[]="#";
$l[]="[global]";
$l[]="";
$l[]="# Directory to store output of sa-learn and ipurge temporarily ";
$l[]="tmp_dir = /tmp";
$l[]="";
$l[]="# To avoid race conditions, we use a lock file.";
$l[]="lock_file = /var/lock/sa-learn-cyrus.lock";
$l[]="";
$l[]="# level of verbosity (0 .. 3)?";
$l[]="verbose	= 2";
$l[]="";
$l[]="# Don't excute commands, show only what would be executed,";
$l[]="# Change this to 'no' after testing.";
$l[]="simulate = no";
$l[]="";
$l[]="# -------------------------------------------------------";
$l[]="# Mailbox";
$l[]="#";
$l[]="[mailbox]";
$l[]="";
$l[]="# List of mailboxes/users which will be considered.";
$l[]="# If this list is empty all mailboxes will be searched.";
$l[]="#";
$l[]="include_list = '$users_list'";
$l[]="";
$l[]="# If include_list is empty, only mailboxes matching this pattern will be considered";
$l[]="include_regexp = '.*'";
$l[]="";
$l[]="# List of mailboxes/users which will be ignored";
$l[]="exclude_list = ''";
$l[]="";
$l[]="# If exclude_list is empty, mailboxes matching this pattern will be ignored";
$l[]="exclude_regexp = ''";
$l[]="";
$l[]="# Spam folder relative to INBOX (cyrus nomenclature: e.g. 'junk.Spam')";
$l[]="spam_folder = 'Junk'";
$l[]="";
$l[]="# Ham folder relative to INBOX (cyrus nomenclature: e.g. 'junk.Ham')";
$l[]="ham_folder = 'Ham'";
$l[]="";
$l[]="# Remove spam after feeding it to SA";
$l[]="remove_spam = yes";
$l[]="";
$l[]="# Remove ham after feeding it to SA";
$l[]="remove_ham = no";
$l[]="";
$l[]="# -------------------------------------------------------";
$l[]="# Spamassassin";
$l[]="#";
$l[]="[sa]";
$l[]="";
$l[]="# Path with system-wide SA preferences";
$l[]="site_config_path = /etc/spamassassin";
$l[]="";
$l[]="# SA configuration file";
$l[]="prefs_file = /etc/spamassassin/local.cf";
$l[]="";
$l[]="# Path to sa-learn";
$l[]="learn_cmd = $salearn";
$l[]="";
$l[]="# SA user and group";
$l[]="user = root";
$l[]="group = root";
$l[]="";
$l[]="# run sa-learn in debug mode (useful to examine problems)";
$l[]="debug = no";
$l[]="";
$l[]="# -------------------------------------------------------";
$l[]="# IMAP";
$l[]="#";
$l[]="[imap]";
$l[]="";
$l[]="# Base directory of IMAP spool (below that mailboxes are located)";
$l[]="base_dir = $users->cyr_partition_default";
$l[]="";
$l[]="# If base_dir has subdivisions with initial letters of mailbox names";
$l[]="# set initial_letter = yes (default), otherwise choose no.";
$l[]="# Example for joe's mailbox:";
$l[]="#   yes: <base_dir>/j/user/joe/";
$l[]="#    no: <base_dir>/user/joe/";
$l[]="initial_letter = yes";
$l[]="";
$l[]="# If your cyrus spool uses domain hierarchy give a list of domains";
$l[]="# Example for mailbox fritz@bar.org and joe@foo.com";
$l[]="#   <base_dir>/domain/b/bar.org/f/fritz";
$l[]="#   <base_dir>/domain/f/foo.com/j/joe";
$l[]="# domains = foo.com bar.org";
$l[]="#";
$l[]="# If you don't use Cyrus's domain support leave the entry empty.";
$l[]="# The initial_letter option (see above) is applied to domains, too.    ";
$l[]="domains = '$domains_list'";
$l[]="";
$l[]="# Choose 'unixhierarchysep = yes' if Cyrus is configured to accept usernames";
$l[]="# like 'hans.mueller.somedomain.tld'";
$l[]="unixhierarchysep = yes";
$l[]="";
$l[]="# imap command to purge mail messages";
$l[]="purge_cmd = $ipurge";
$l[]="";
$l[]="# Cyrus-IMAPd user";
$l[]="user = cyrus";
	
@file_put_contents("/etc/spamassassin/sa-learn-cyrus.conf",@implode("\n",$l));	
system(LOCATE_PHP5_BIN2()." ".dirname(__FILE__)."/exec.spamassassin.php");
	
}
	
	
function ListUsers(){	
	
	
	$ldap=new clladp();
	$search="(&(objectclass=UserArticaClass)(EnableUserSpamLearning=1))";
	
		$sr =@ldap_search($ldap->ldap_connection,$ldap->suffix,$search,array('uid'));
		if ($sr) {
			$hash=ldap_get_entries($ldap->ldap_connection,$sr);			
			for($i=0;$i<$hash["count"];$i++){
				if($hash[$i]["uid"][0]==null){continue;}
				$hash[$i]["uid"][0]=str_replace(".","^",$hash[$i]["uid"][0]);
				$userid[]=$hash[$i]["uid"][0];
				}
		}

		return $userid;

}	
	
	

?>