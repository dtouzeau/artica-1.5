<?php
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__).'/ressources/class.templates.inc');
include_once(dirname(__FILE__).'/ressources/class.ldap.inc');
include_once(dirname(__FILE__).'/ressources/class.mysql.inc');
include_once(dirname(__FILE__).'/ressources/class.ini.inc');
include_once(dirname(__FILE__).'/ressources/class.kavmilterd.inc');
include_once(dirname(__FILE__).'/ressources/class.kas-filter.inc');
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");


	$users=new usersMenus();
	if(!$users->kas_installed){die();}

	if($argv[1]=='--rebuild-tables'){rebuildtables();die();}
	if($argv[1]=='--dograph'){dograph();die();}

	
	

	BuildRobots();
	filter_conf();
	removes();
	ListOU();
	BuildDefault();
	shell_exec("/bin/chown mailflt3:mailflt3 /usr/local/ap-mailfilter3/conf/def/group/*");
	shell_exec("/usr/local/ap-mailfilter3/bin/mkprofiles");
	shell_exec("/usr/local/ap-mailfilter3/bin/kas-restart -f -p -m");
	
	
function dograph(){
	
	exec("/usr/local/ap-mailfilter3/control/bin/stat -c /usr/local/ap-mailfilter3/control/stat/stat.conf >/dev/null 2>&1");
	exec("/usr/local/ap-mailfilter3/control/bin/statvisual -c /usr/local/ap-mailfilter3/control/stat/stat.conf");
}	

function removes(){
	
	
@unlink("/usr/local/ap-mailfilter3/conf/def/common/common-allow.xml");
shell_exec('/bin/touch /usr/local/ap-mailfilter3/conf/def/common/common-allow.xml');

@unlink("/usr/local/ap-mailfilter3/conf/def/common/common-deny.xml");
shell_exec('/bin/touch /usr/local/ap-mailfilter3/conf/def/common/common-deny.xml');
	
$dir_handle = @opendir("/usr/local/ap-mailfilter3/conf/def/group");
	if(!$dir_handle){
		return array();
	}
	$count=0;	
	while ($file = readdir($dir_handle)) {
		  if($file=='.'){continue;}
		  if($file=='..'){continue;}
		  if(!is_file("/usr/local/ap-mailfilter3/conf/def/group/$file")){continue;}
		  
		  if(preg_match("#^([0-9]+)-#",$file,$re)){
		  	$s=intval($re[1]);
		  	if($s==0){continue;}
		  	$AR[$s]=$s;
		  }
		  
		  continue;
		}	
	
	
}

function rebuildtables(){
	$sql="DROP TABLE `kas3`";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	$q->Check_quarantine_table();
	
}





function ListOU(){
	$sql="SELECT ou FROM kas3 GROUP BY ou";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	$count=0;
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if(trim($ligne["ou"])==null){continue;}
		if($ligne["ou"]=="filter.conf"){continue;}
		if($ligne["ou"]=="default"){continue;}
		$count=$count+1;
		$ou=$ligne["ou"];
		Build_action_def($ou,$count);
		build_rule_def($ou,$count);
		build_profile_xml($ou,$count);
		build_members_xml($ou,$count);
		build_deny_xml($ou,$count);
		build_allow_xml($ou,$count);
		build_ipdeny_xml($ou,$count);
		build_ipallow_xml($ou,$count);
	}
	

	
}

function BuildDefault(){
		$ou="default";
		$count=0;
		Build_action_def($ou,$count);
		build_rule_def($ou,$count);
		build_profile_xml($ou,$count);
		build_members_xml($ou,$count);
		build_deny_xml($ou,$count);
		build_allow_xml($ou,$count);
		build_ipdeny_xml($ou,$count);
		build_ipallow_xml($ou,$count);	
	
}

function build_profile_xml($ou,$count){
	if($count<10){$num="0$count";}else{$num=$count;}		
	$conf[]="<?xml version=\"1.0\" encoding=\"utf-8\"?>";
	$conf[]="#include <sys/system.def>";
	$conf[]="#include \"000000$num-rule.def\"";
	$conf[]="#include \"000000$num-action.def\"";
	$conf[]="#include <base/group.xml.templ>";
	$conf[]="";
	@file_put_contents("/usr/local/ap-mailfilter3/conf/def/group/000000$num-profile.xml",@implode("\n",$conf));	
}
function build_deny_xml($ou,$count){
	if($count<10){$numero="0$count";}else{$numero=$count;}		
	$conf[]="<?xml version=\"1.0\" encoding=\"utf-8\"?>";
	$conf[]="#include <base/group-deny.xml.macro>";
	$conf[]="BEGIN_GROUP_DENY_EMAIL_LIST(0x000000$numero)";
	$ldap=new clladp();
	
	if($ldap->ldapFailed){return null;}	
	$domains=$ldap->hash_get_domains_ou($ou);
	if(is_array($domains)){
		while (list ($num, $val) = each ($domains) ){
			$emails=$ldap->BlackListFromDomain($val);
			while (list ($to, $froms) = each ($emails) ){
				while (list ($index, $senders) = each ($froms)){
					$conf[]="EMAIL_ENTRY(\"$senders\")";
				}
				
			}
		}
	}
	$conf[]="END_GROUP_DENY_EMAIL_LIST";
	$conf[]="";
	@file_put_contents("/usr/local/ap-mailfilter3/conf/def/group/000000$numero-deny.xml",@implode("\n",$conf));	
}
function build_ipdeny_xml($ou,$count){
if($count<10){$num="0$count";}else{$num=$count;}		
$conf[]="<?xml version=\"1.0\" encoding=\"utf-8\"?>";
$conf[]="#include <base/group-ipdeny.xml.macro>";
$conf[]="BEGIN_GROUP_DENY_IP_LIST(0x000000$count)";
$conf[]="END_GROUP_DENY_IP_LIST";
$conf[]="";
@file_put_contents("/usr/local/ap-mailfilter3/conf/def/group/000000$num-ipdeny.xml",@implode("\n",$conf));
}





function build_ipallow_xml($ou,$count){
if($count<10){$num="0$count";}else{$num=$count;}
$conf[]="<?xml version=\"1.0\" encoding=\"utf-8\"?>";
$conf[]="#include <base/group-ipallow.xml.macro>";
$conf[]="BEGIN_GROUP_ALLOW_IP_LIST(0x000000$num)";
$conf[]="END_GROUP_ALLOW_IP_LIST";
$conf[]="";
@file_put_contents("/usr/local/ap-mailfilter3/conf/def/group/000000$num-ipallow.xml",@implode("\n",$conf));
}

function build_allow_xml($ou,$count){
	if($count<10){$numero="0$count";}else{$numero=$count;}		
	$conf[]="<?xml version=\"1.0\" encoding=\"utf-8\"?>";
	$conf[]="#include <base/group-allow.xml.macro>";
	$conf[]="BEGIN_GROUP_ALLOW_EMAIL_LIST(0x000000$numero)";

	$ldap=new clladp();
	
	if($ldap->ldapFailed){return null;}	
	$domains=$ldap->hash_get_domains_ou($ou);
	if(is_array($domains)){
		while (list ($num, $val) = each ($domains) ){
			$emails=$ldap->WhitelistsFromDomain($val);
			while (list ($to, $froms) = each ($emails) ){
				while (list ($index, $senders) = each ($froms)){
					$conf[]="EMAIL_ENTRY(\"$senders\")";
				}
				
			}
		}
	}
	
	$sql="SELECT * FROM postfix_global_whitelist WHERE enabled=1 ORDER BY sender";	
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$pattern=trim($ligne["sender"]);
		if($pattern==null){continue;}
		$conf[]="EMAIL_ENTRY(\"$pattern\")";
	}		
	
	
	
	$conf[]="END_GROUP_ALLOW_EMAIL_LIST";
	$conf[]="";
	@file_put_contents("/usr/local/ap-mailfilter3/conf/def/group/000000$numero-allow.xml",@implode("\n",$conf));	
}



function build_members_xml($ou,$count){
if($count<10){$numero="0"."$count";}else{$numero=$count;}		
$conf[]="<?xml version=\"1.0\" encoding=\"utf-8\"?>";
$conf[]="#include <base/group-member.xml.macro>";
$conf[]="BEGIN_GROUP_MEMBER_LIST(0x000000$numero)";

$ldap=new clladp();
	if($ldap->ldapFailed){return null;}
	$domains=$ldap->hash_get_domains_ou($ou);
	
	if(is_array($domains)){
		while (list ($num, $val) = each ($domains) ){
			$num="*@$num";
			$conf[]="EMAIL_ENTRY(\"$num\")";
		}
	}
$conf[]="END_GROUP_MEMBER_LIST";	
$conf[]="";
echo "ou=\"$ou\" ($count)". count($domains)." domains in 000000$numero-members.xml - $count/$numero\n";

@file_put_contents("/usr/local/ap-mailfilter3/conf/def/group/000000$numero-members.xml",@implode("\n",$conf));	
}


function build_rule_def($ou,$count){
if($count<10){$num="0$count";}else{$num=$count;}	

$kas=new kas_mysql($ou);

$OPT_USE_SURBL=$kas->GET_KEY("OPT_USE_SURBL");
$OPT_CF_OBSCENE=$kas->GET_KEY("OPT_CF_OBSCENE");
$OPT_USE_DNS=$kas->GET_KEY("OPT_USE_DNS");
$OPT_FILTRATION_ON=$kas->GET_KEY("OPT_FILTRATION_ON");
$OPT_HEADERS_FROM_OR_TO_NO_DOMAIN=$kas->GET_KEY("OPT_HEADERS_FROM_OR_TO_NO_DOMAIN");

$OPT_HEADERS_FROM_OR_TO_DIGITS=$kas->GET_KEY("OPT_HEADERS_FROM_OR_TO_DIGITS");
$OPT_HEADERS_SUBJECT_WS_OR_DOTS=$kas->GET_KEY("OPT_HEADERS_SUBJECT_WS_OR_DOTS");
$OPT_HEADERS_FROM_OR_TO_NO_DOMAIN=$kas->GET_KEY("OPT_HEADERS_FROM_OR_TO_NO_DOMAIN");
$OPT_SPF=$kas->GET_KEY("OPT_SPF");
$OPT_DNS_HOST_IN_DNS=$kas->GET_KEY("OPT_DNS_HOST_IN_DNS");
$OPT_LANG_THAI=$kas->GET_KEY("OPT_LANG_THAI");
$OPT_HEADERS_SUBJECT_TOO_LONG=$kas->GET_KEY("OPT_HEADERS_SUBJECT_TOO_LONG");
$OPT_HEADERS_TO_UNDISCLOSED=$kas->GET_KEY("OPT_HEADERS_TO_UNDISCLOSED");
$OPT_LANG_KOREAN=$kas->GET_KEY("OPT_LANG_KOREAN");
$OPT_LANG_JAPANESE=$kas->GET_KEY("OPT_LANG_JAPANESE");
$OPT_SPAM_RATE_LIMIT=$kas->GET_KEY("OPT_SPAM_RATE_LIMIT");
$OPT_LANG_CHINESE=$kas->GET_KEY("OPT_LANG_CHINESE");
$OPT_HEADERS_SUBJECT_DIGIT_OR_TIME_ID=$kas->GET_KEY("OPT_HEADERS_SUBJECT_DIGIT_OR_TIME_ID");
$OPT_USE_LISTS=$kas->GET_KEY("OPT_LANG_CHINESE");




if($OPT_USE_SURBL==null){$OPT_USE_SURBL=0;}
if($OPT_CF_OBSCENE==null){$OPT_CF_OBSCENE=1;}
if($OPT_USE_DNS==null){$OPT_USE_DNS=0;}
if($OPT_FILTRATION_ON==null){$OPT_FILTRATION_ON=0;}
if($OPT_HEADERS_FROM_OR_TO_NO_DOMAIN==null){$OPT_HEADERS_FROM_OR_TO_NO_DOMAIN=0;}
if($OPT_HEADERS_FROM_OR_TO_DIGITS==null){$OPT_HEADERS_FROM_OR_TO_DIGITS=0;}
if($OPT_HEADERS_SUBJECT_WS_OR_DOTS==null){$OPT_HEADERS_SUBJECT_WS_OR_DOTS=0;}
if($OPT_SPF==null){$OPT_SPF=1;}
if($OPT_DNS_HOST_IN_DNS==null){$OPT_DNS_HOST_IN_DNS=0;}
if($OPT_LANG_THAI==null){$OPT_LANG_THAI=1;}
if($OPT_HEADERS_SUBJECT_TOO_LONG==null){$OPT_HEADERS_SUBJECT_TOO_LONG=0;}
if($OPT_HEADERS_TO_UNDISCLOSED==null){$OPT_HEADERS_TO_UNDISCLOSED=0;}
if($OPT_LANG_KOREAN==null){$OPT_LANG_KOREAN=1;}
if($OPT_LANG_JAPANESE==null){$OPT_LANG_JAPANESE=1;}
if($OPT_SPAM_RATE_LIMIT==null){$OPT_SPAM_RATE_LIMIT=0;}
if($OPT_LANG_JAPANESE==null){$OPT_LANG_JAPANESE=0;}
if($OPT_LANG_CHINESE==null){$OPT_LANG_CHINESE=0;}
if($OPT_HEADERS_SUBJECT_DIGIT_OR_TIME_ID==null){$OPT_HEADERS_SUBJECT_DIGIT_OR_TIME_ID=0;}
if($OPT_USE_LISTS==null){$OPT_USE_LISTS=1;}



$conf[]="#pragma once";
$conf[]="#include <common/common-rule.def>";
$conf[]="";
$conf[]="BEGIN_GROUP_DEFS(0x000000$num,\"$ou\",\"\")";
$conf[]="#ifndef _GROUP_ID";
$conf[]="#define _GROUP_ID 0x000000$num";
$conf[]="#define _GROUP_ID_STR \"000000$num\"";
$conf[]="#define _GROUP_NAME \"$ou\"";
$conf[]="#define _GROUP_MEMO \"\"";
$conf[]="#define _GROUP_MEMBERS \"000000$num-members.xml\"";
$conf[]="#define _GROUP_ALLOWED_EMAILS \"000000$num-allow.xml\"";
$conf[]="#define _GROUP_ALLOWED_IPS \"000000$num-ipallow.xml\"";
$conf[]="#define _GROUP_DENIED_EMAILS \"000000$num-deny.xml\"";
$conf[]="#define _GROUP_DENIED_IPS \"000000$num-ipdeny.xml\"";
$conf[]="#endif";
$conf[]="END_GROUP_HEADER";
$conf[]="#define OPT_USE_SURBL 	\"$OPT_USE_SURBL\"";
$conf[]="#define _N_OPT_USE_SURBL 	$OPT_USE_SURBL";
$conf[]="#define _L_OPT_USE_SURBL 	1";
$conf[]="#define OPT_USE_DNS 	\"$OPT_USE_DNS\"";
$conf[]="#define _N_OPT_USE_DNS 	$OPT_USE_DNS";
$conf[]="#define _L_OPT_USE_DNS 	1";
$conf[]="#define OPT_CF_OBSCENE 	\"$OPT_CF_OBSCENE\"";
$conf[]="#define _N_OPT_CF_OBSCENE 	$OPT_CF_OBSCENE";
$conf[]="#define _L_OPT_CF_OBSCENE 	1";
$conf[]="#define OPT_FILTRATION_ON 	\"$OPT_FILTRATION_ON\"";
$conf[]="#define _N_OPT_FILTRATION_ON 	$OPT_FILTRATION_ON";
$conf[]="#define _L_OPT_FILTRATION_ON 	1";
$conf[]="#define OPT_DNS_DNSBL_SECOND 	\"0\"";
$conf[]="#define _N_OPT_DNS_DNSBL_SECOND 	0";
$conf[]="#define _L_OPT_DNS_DNSBL_SECOND 	1";
$conf[]="#define OPT_HEADERS_FROM_OR_TO_NO_DOMAIN 	\"$OPT_HEADERS_FROM_OR_TO_NO_DOMAIN\"";
$conf[]="#define _N_OPT_HEADERS_FROM_OR_TO_NO_DOMAIN 	$OPT_HEADERS_FROM_OR_TO_NO_DOMAIN";
$conf[]="#define _L_OPT_HEADERS_FROM_OR_TO_NO_DOMAIN 	1";
$conf[]="#define OPT_HEADERS_FROM_OR_TO_DIGITS 	\"$OPT_HEADERS_FROM_OR_TO_DIGITS\"";
$conf[]="#define _N_OPT_HEADERS_FROM_OR_TO_DIGITS 	$OPT_HEADERS_FROM_OR_TO_DIGITS";
$conf[]="#define _L_OPT_HEADERS_FROM_OR_TO_DIGITS 	1";
$conf[]="#define OPT_HEADERS_SUBJECT_WS_OR_DOTS 	\"$OPT_HEADERS_SUBJECT_WS_OR_DOTS\"";
$conf[]="#define _N_OPT_HEADERS_SUBJECT_WS_OR_DOTS 	$OPT_HEADERS_SUBJECT_WS_OR_DOTS";
$conf[]="#define _L_OPT_HEADERS_SUBJECT_WS_OR_DOTS 	1";
$conf[]="#define OPT_SPF 	\"$OPT_SPF\"";
$conf[]="#define _N_OPT_SPF 	$OPT_SPF";
$conf[]="#define _L_OPT_SPF 	1";
$conf[]="#define OPT_DNS_HOST_IN_DNS 	\"$OPT_DNS_HOST_IN_DNS\"";
$conf[]="#define _N_OPT_DNS_HOST_IN_DNS 	$OPT_DNS_HOST_IN_DNS";
$conf[]="#define _L_OPT_DNS_HOST_IN_DNS 	1";
$conf[]="#define OPT_LANG_THAI 	\"$OPT_LANG_THAI\"";
$conf[]="#define _N_OPT_LANG_THAI 	$OPT_LANG_THAI";
$conf[]="#define _L_OPT_LANG_THAI 	1";
$conf[]="#define OPT_HEADERS_SUBJECT_TOO_LONG 	\"$OPT_HEADERS_SUBJECT_TOO_LONG\"";
$conf[]="#define _N_OPT_HEADERS_SUBJECT_TOO_LONG 	$OPT_HEADERS_SUBJECT_TOO_LONG";
$conf[]="#define _L_OPT_HEADERS_SUBJECT_TOO_LONG 	1";

$conf[]="#define OPT_HEADERS_TO_UNDISCLOSED 	\"$OPT_HEADERS_TO_UNDISCLOSED\"";
$conf[]="#define _N_OPT_HEADERS_TO_UNDISCLOSED 	$OPT_HEADERS_TO_UNDISCLOSED";
$conf[]="#define _L_OPT_HEADERS_TO_UNDISCLOSED 	1";

$conf[]="#define OPT_LANG_KOREAN 	\"$OPT_LANG_KOREAN\"";
$conf[]="#define _N_OPT_LANG_KOREAN 	$OPT_LANG_KOREAN";
$conf[]="#define _L_OPT_LANG_KOREAN 	1";

$conf[]="#define OPT_LANG_JAPANESE 	\"$OPT_LANG_JAPANESE\"";
$conf[]="#define _N_OPT_LANG_JAPANESE 	$OPT_LANG_JAPANESE";
$conf[]="#define _L_OPT_LANG_JAPANESE 	1";

$conf[]="#define OPT_SPAM_RATE_LIMIT 	\"$OPT_SPAM_RATE_LIMIT\"";
$conf[]="#define _N_OPT_SPAM_RATE_LIMIT 	$OPT_SPAM_RATE_LIMIT";
$conf[]="#define _L_OPT_SPAM_RATE_LIMIT 	1";

$conf[]="#define OPT_LANG_CHINESE 	\"$OPT_LANG_CHINESE\"";
$conf[]="#define _N_OPT_LANG_CHINESE 	$OPT_LANG_CHINESE";
$conf[]="#define _L_OPT_LANG_CHINESE 	1";

$conf[]="#define OPT_HEADERS_SUBJECT_DIGIT_OR_TIME_ID 	\"$OPT_HEADERS_SUBJECT_DIGIT_OR_TIME_ID\"";
$conf[]="#define _N_OPT_HEADERS_SUBJECT_DIGIT_OR_TIME_ID 	$OPT_HEADERS_SUBJECT_DIGIT_OR_TIME_ID";
$conf[]="#define _L_OPT_HEADERS_SUBJECT_DIGIT_OR_TIME_ID 	1";
$conf[]="#define OPT_USE_LISTS 	\"$OPT_USE_LISTS\"";
$conf[]="#define _N_OPT_USE_LISTS 	$OPT_USE_LISTS";
$conf[]="#define _L_OPT_USE_LISTS 	1";
$conf[]="END_GROUP_DEFS";
$conf[]="";
@file_put_contents("/usr/local/ap-mailfilter3/conf/def/group/000000$num-rule.def",@implode("\n",$conf));

}


function Build_action_def($ou,$count){
	
if($count<10){$num="0$count";}else{$num=$count;}

$kas=new kas_mysql($ou);

$ACTION_PROBABLE_SUBJECT_PREFIX=$kas->GET_KEY("ACTION_PROBABLE_SUBJECT_PREFIX");
$ACTION_PROBABLE_SUBJECT_PREFIX_RX=GetRX($ACTION_PROBABLE_SUBJECT_PREFIX);
$ACTION_PROBABLE_MODE=$kas->GET_KEY("ACTION_PROBABLE_MODE");
if($ACTION_PROBABLE_MODE==null){$ACTION_PROBABLE_MODE=0;}

$ACTION_BLACKLISTED_SUBJECT_PREFIX=$kas->GET_KEY("ACTION_BLACKLISTED_SUBJECT_PREFIX");
$ACTION_BLACKLISTED_SUBJECT_PREFIX_RX=GetRX($ACTION_PROBABLE_SUBJECT_PREFIX);
$ACTION_BLACKLISTED_MODE=$kas->GET_KEY("ACTION_BLACKLISTED_SUBJECT_PREFIX");
if($ACTION_BLACKLISTED_MODE==null){$ACTION_BLACKLISTED_MODE=0;}


$ACTION_SPAM_MODE=$kas->GET_KEY("ACTION_SPAM_MODE");
$ACTION_SPAM_SUBJECT_PREFIX=$kas->GET_KEY("ACTION_SPAM_SUBJECT_SUFFIX");
$ACTION_SPAM_SUBJECT_PREFIX_RX=GetRX($ACTION_SPAM_SUBJECT_SUFFIX);
if($ACTION_SPAM_MODE==null){$ACTION_SPAM_MODE=0;}



$conf[]="#pragma once";
$conf[]="#include <common/common-action.def>";
$conf[]="";
$conf[]="BEGIN_GROUP_DEFS(0x000000$num,\"$ou\",\"\")";
$conf[]="#ifndef _GROUP_ID";
$conf[]="#define _GROUP_ID 0x000000$num";
$conf[]="#define _GROUP_ID_STR \"000000$num\"";
$conf[]="#define _GROUP_NAME \"$ou\"";
$conf[]="#define _GROUP_MEMO \"Artica Builder\"";
$conf[]="#define _GROUP_MEMBERS \"000000$num-members.xml\"";
$conf[]="#define _GROUP_ALLOWED_EMAILS \"000000$num-allow.xml\"";
$conf[]="#define _GROUP_ALLOWED_IPS \"000000$num-ipallow.xml\"";
$conf[]="#define _GROUP_DENIED_EMAILS \"000000$num-deny.xml\"";
$conf[]="#define _GROUP_DENIED_IPS \"000000$num-ipdeny.xml\"";
$conf[]="#endif";
$conf[]="END_GROUP_HEADER";

$conf[]="#define ACTION_PROBABLE_SUBJECT_PREFIX 	\"$ACTION_PROBABLE_SUBJECT_PREFIX\"";
$conf[]="#define _N_ACTION_PROBABLE_SUBJECT_PREFIX 	0";
$conf[]="#define _L_ACTION_PROBABLE_SUBJECT_PREFIX 	".strlen($ACTION_PROBABLE_SUBJECT_PREFIX);
$conf[]="#define ACTION_PROBABLE_SUBJECT_PREFIX_RX 	\"$ACTION_PROBABLE_SUBJECT_PREFIX_RX\"";
$conf[]="#define _N_ACTION_PROBABLE_SUBJECT_PREFIX_RX 	0";
$conf[]="#define _L_ACTION_PROBABLE_SUBJECT_PREFIX_RX 	".strlen($ACTION_PROBABLE_SUBJECT_PREFIX_RX);
$conf[]="#define ACTION_PROBABLE_MODE 	\"$ACTION_PROBABLE_MODE\"";
$conf[]="#define _N_ACTION_PROBABLE_MODE 	$ACTION_PROBABLE_MODE";
$conf[]="#define _L_ACTION_PROBABLE_MODE 	".strlen($ACTION_PROBABLE_MODE);
$conf[]="#define ACTION_PROBABLE_SUBJECT_SUFFIX 	\"$ACTION_PROBABLE_SUBJECT_PREFIX\"";
$conf[]="#define _N_ACTION_PROBABLE_SUBJECT_SUFFIX 	0";
$conf[]="#define _L_ACTION_PROBABLE_SUBJECT_SUFFIX 	".strlen($ACTION_PROBABLE_SUBJECT_PREFIX);
$conf[]="#define ACTION_PROBABLE_EMAIL 	\"xspam@localhost.localdomain\"";
$conf[]="#define _N_ACTION_PROBABLE_EMAIL 	0";
$conf[]="#define _L_ACTION_PROBABLE_EMAIL 	".strlen("xspam@localhost.localdomain");
$conf[]="#define ACTION_PROBABLE_KEYWORDS 	\"PROBABLE\"";
$conf[]="#define _N_ACTION_PROBABLE_KEYWORDS 	0";
$conf[]="#define _L_ACTION_PROBABLE_KEYWORDS 	8";


$conf[]="#define ACTION_BLACKLISTED_MODE 	\"$ACTION_BLACKLISTED_MODE\"";
$conf[]="#define _N_ACTION_BLACKLISTED_MODE 	$ACTION_BLACKLISTED_MODE";
$conf[]="#define _L_ACTION_BLACKLISTED_MODE 	".strlen($ACTION_BLACKLISTED_MODE);
$conf[]="#define ACTION_BLACKLISTED_SUBJECT_PREFIX 	\"$ACTION_BLACKLISTED_SUBJECT_PREFIX\"";
$conf[]="#define ACTION_BLACKLISTED_SUBJECT_PREFIX_RX 	\"$ACTION_BLACKLISTED_SUBJECT_PREFIX_RX\"";
$conf[]="#define _N_ACTION_BLACKLISTED_SUBJECT_PREFIX_RX 	0";
$conf[]="#define _L_ACTION_BLACKLISTED_SUBJECT_PREFIX_RX 	".strlen($ACTION_BLACKLISTED_SUBJECT_PREFIX_RX);
$conf[]="#define _N_ACTION_BLACKLISTED_SUBJECT_PREFIX 	0";
$conf[]="#define _L_ACTION_BLACKLISTED_SUBJECT_PREFIX 	".strlen($ACTION_BLACKLISTED_SUBJECT_PREFIX);
$conf[]="#define ACTION_BLACKLISTED_KEYWORDS 	\"BLACKLISTED\"";
$conf[]="#define _N_ACTION_BLACKLISTED_KEYWORDS 	0";
$conf[]="#define _L_ACTION_BLACKLISTED_KEYWORDS 	11";
$conf[]="#define ACTION_BLACKLISTED_SUBJECT_SUFFIX 	\"\"";
$conf[]="#define _N_ACTION_BLACKLISTED_SUBJECT_SUFFIX 	0";
$conf[]="#define _L_ACTION_BLACKLISTED_SUBJECT_SUFFIX 	0";

$conf[]="#define ACTION_BLACKLISTED_EMAIL 	\"xspam@localhost.localdomain\"";
$conf[]="#define _N_ACTION_BLACKLISTED_EMAIL 	0";
$conf[]="#define _L_ACTION_BLACKLISTED_EMAIL 	".strlen("xspam@localhost.localdomain");




$conf[]="#define ACTION_SPAM_MODE 	\"$ACTION_SPAM_MODE\"";
$conf[]="#define _N_ACTION_SPAM_MODE 	$ACTION_SPAM_MODE";
$conf[]="#define _L_ACTION_SPAM_MODE 	".strlen($ACTION_SPAM_MODE);
$conf[]="#define ACTION_SPAM_KEYWORDS 	\"SPAM\"";
$conf[]="#define _N_ACTION_SPAM_KEYWORDS 	0";
$conf[]="#define _L_ACTION_SPAM_KEYWORDS 	4";
$conf[]="#define ACTION_SPAM_EMAIL 	\"xspam@localhost.localdomain\"";
$conf[]="#define _N_ACTION_SPAM_EMAIL 	0";
$conf[]="#define _L_ACTION_SPAM_EMAIL 	".strlen("xspam@localhost.localdomain");
$conf[]="#define ACTION_SPAM_SUBJECT_SUFFIX 	\"\"";
$conf[]="#define _N_ACTION_SPAM_SUBJECT_SUFFIX 	0";
$conf[]="#define _L_ACTION_SPAM_SUBJECT_SUFFIX 	0";
$conf[]="#define ACTION_SPAM_SUBJECT_PREFIX_RX 	\"$ACTION_SPAM_SUBJECT_PREFIX_RX\"";
$conf[]="#define _N_ACTION_SPAM_SUBJECT_PREFIX_RX 	0";
$conf[]="#define _L_ACTION_SPAM_SUBJECT_PREFIX_RX 	".strlen($ACTION_SPAM_SUBJECT_PREFIX_RX);
$conf[]="#define ACTION_SPAM_SUBJECT_PREFIX 	\"$ACTION_SPAM_SUBJECT_PREFIX\"";
$conf[]="#define _N_ACTION_SPAM_SUBJECT_PREFIX 	0";
$conf[]="#define _L_ACTION_SPAM_SUBJECT_PREFIX 	".strlen($ACTION_SPAM_SUBJECT_PREFIX);


$conf[]="#define ACTION_FORMAL_MODE 	\"0\"";
$conf[]="#define _N_ACTION_FORMAL_MODE 	0";
$conf[]="#define _L_ACTION_FORMAL_MODE 	1";
$conf[]="#define ACTION_FORMAL_SUBJECT_SUFFIX 	\"\"";
$conf[]="#define _N_ACTION_FORMAL_SUBJECT_SUFFIX 	0";
$conf[]="#define _L_ACTION_FORMAL_SUBJECT_SUFFIX 	0";
$conf[]="#define ACTION_FORMAL_SUBJECT_PREFIX_RX 	\"&#92;[&#92;-&#92;-Formal&#92; Message&#92;-&#92;-&#92;]\"";
$conf[]="#define _N_ACTION_FORMAL_SUBJECT_PREFIX_RX 	0";
$conf[]="#define _L_ACTION_FORMAL_SUBJECT_PREFIX_RX 	27";


$conf[]="#define ACTION_TRUSTED_SUBJECT_PREFIX 	\"\"";
$conf[]="#define _N_ACTION_TRUSTED_SUBJECT_PREFIX 	0";
$conf[]="#define _L_ACTION_TRUSTED_SUBJECT_PREFIX 	0";
$conf[]="#define ACTION_TRUSTED_SUBJECT_PREFIX_RX 	\".\"";
$conf[]="#define _N_ACTION_TRUSTED_SUBJECT_PREFIX_RX 	0";
$conf[]="#define _L_ACTION_TRUSTED_SUBJECT_PREFIX_RX 	1";
$conf[]="#define ACTION_TRUSTED_SUBJECT_SUFFIX 	\"\"";
$conf[]="#define _N_ACTION_TRUSTED_SUBJECT_SUFFIX 	0";
$conf[]="#define _L_ACTION_TRUSTED_SUBJECT_SUFFIX 	0";
$conf[]="#define ACTION_TRUSTED_KEYWORDS 	\"TRUSTED\"";
$conf[]="#define _N_ACTION_TRUSTED_KEYWORDS 	0";
$conf[]="#define _L_ACTION_TRUSTED_KEYWORDS 	7";


$conf[]="#define ACTION_NORMAL_MODE 	\"0\"";
$conf[]="#define _N_ACTION_NORMAL_MODE 	0";
$conf[]="#define _L_ACTION_NORMAL_MODE 	1";
$conf[]="#define ACTION_NORMAL_SUBJECT_PREFIX 	\"\"";
$conf[]="#define _N_ACTION_NORMAL_SUBJECT_PREFIX 	0";
$conf[]="#define _L_ACTION_NORMAL_SUBJECT_PREFIX 	0";
$conf[]="#define ACTION_NORMAL_SUBJECT_SUFFIX 	\"\"";
$conf[]="#define _N_ACTION_NORMAL_SUBJECT_SUFFIX 	0";
$conf[]="#define _L_ACTION_NORMAL_SUBJECT_SUFFIX 	0";
$conf[]="#define ACTION_NORMAL_KEYWORDS 	\"\"";
$conf[]="#define _N_ACTION_NORMAL_KEYWORDS 	0";
$conf[]="#define _L_ACTION_NORMAL_KEYWORDS 	0";

$conf[]="#define ACTION_FORMAL_SUBJECT_PREFIX 	\"[--Formal Message--]\"";
$conf[]="#define _N_ACTION_FORMAL_SUBJECT_PREFIX 	0";
$conf[]="#define _L_ACTION_FORMAL_SUBJECT_PREFIX 	20";
$conf[]="#define ACTION_FORMAL_KEYWORDS 	\"FORMAL\"";
$conf[]="#define _N_ACTION_FORMAL_KEYWORDS 	0";
$conf[]="#define _L_ACTION_FORMAL_KEYWORDS 	6";

$conf[]="#define ACTION_TRUSTED_MODE 	\"0\"";
$conf[]="#define _N_ACTION_TRUSTED_MODE 	0";
$conf[]="#define _L_ACTION_TRUSTED_MODE 	1";
$conf[]="#define ACTION_NORMAL_SUBJECT_PREFIX_RX 	\".\"";
$conf[]="#define _N_ACTION_NORMAL_SUBJECT_PREFIX_RX 	0";
$conf[]="#define _L_ACTION_NORMAL_SUBJECT_PREFIX_RX 	1";
$conf[]="END_GROUP_DEFS";
$conf[]="";
@file_put_contents("/usr/local/ap-mailfilter3/conf/def/group/000000$num-action.def",@implode("\n",$conf));

}

function GetRX($line){
		if($line==null){return ".";}
		$line12= addcslashes($line,"\ \\[]()!?-;$@{}#/=%*;,:<>&~\"'|+");
		$line12=htmlentities($line12);
		$len=strlen($line12);
		$line12=str_replace('\\','&#92;',$line12);
		return $line12;
	}
	
	
function filter_conf(){
	
	$kas=new kas_filter();
		$conf[]="RootPath /usr/local/ap-mailfilter3";
		$conf[]="User mailflt3";
		$conf[]="Group mailflt3";
		$conf[]="LogFacility mail";
		$conf[]="";
		$conf[]="# LogLevel -- controls the number of messages to be written to syslog.";
		$conf[]="# Possible values:";
		$conf[]="# 0 - minimum (errors only),";
		$conf[]="# 1 - low (errors and warnings),";
		$conf[]="# 2 - normal (errors, warnings and notices),";
		$conf[]="# 3 - high (errors, warnings, notices and info messages)";
		$conf[]="# 4 - debug messages,";
		$conf[]="# 5 - more debug messages.";
		$conf[]="# The default value is 2.";
		$conf[]="#";
		$conf[]="LogLevel 3";
		$conf[]="ServerListen tcp:127.0.0.1:2277";
		$conf[]="FilterPath /usr/local/ap-mailfilter3/bin/ap-mailfilter";
		$conf[]="ServerMaxFilters {$kas->array_datas["ServerMaxFilters"]}";
		$conf[]="ServerStartFilters {$kas->array_datas["ServerStartFilters"]}";
		$conf[]="ServerSpareFilters {$kas->array_datas["ServerSpareFilters"]}";
		$conf[]="FilterMaxMessages {$kas->array_datas["FilterMaxMessages"]}";
		$conf[]="FilterRandMessages {$kas->array_datas["FilterRandMessages"]}";
		$conf[]="FilterMaxIdle {$kas->array_datas["FilterMaxIdle"]}";
		$conf[]="FilterDelayedExit {$kas->array_datas["FilterDelayedExit"]}";
		$conf[]="FilterDataTimeout {$kas->array_datas["FilterDataTimeout"]}";
		$conf[]="FilterLicenseConnectTimeout {$kas->array_datas["FilterLicenseConnectTimeout"]}";
		$conf[]="FilterLicenseDataTimeout {$kas->array_datas["FilterLicenseDataTimeout"]}";
		$conf[]="FilterSPFDataTimeout 1";
		$conf[]="FilterDNSTimeout 10";
		$conf[]="FilterLicenseConnectTo /usr/local/ap-mailfilter3/run/kas-license.socket";
		$conf[]="FilterSPFConnectTo /usr/local/ap-mailfilter3/run/ap-spfd.socket";
		$conf[]="FilterReceivedHeadersLimit 2";
		$conf[]="FilterParseMSOffice {$kas->array_datas["FilterParseMSOffice"]}";
		$conf[]="FilterUserLogFile /usr/local/ap-mailfilter3/log/filter.log";
		$conf[]="FilterStatLogFile /usr/local/ap-mailfilter3/log/filter.log";
		$conf[]="FilterUDSCfgFile /usr/local/ap-mailfilter3/conf/uds.cfg";
		$conf[]="FilterUDSTimeout 10";
		$conf[]="FilterUDSEnabled no";
		$conf[]="LicenseListen /usr/local/ap-mailfilter3/run/kas-license.socket";
		$conf[]="LicenseKeysPath /usr/local/ap-mailfilter3/conf/lk-license/";
		$conf[]="LicenseMaxConnections 200";
		$conf[]="LicenseIdleTimeout 30";
		$conf[]="LicenseDataTimeout 1";
		$conf[]="SPFDListen /usr/local/ap-mailfilter3/run/ap-spfd.socket";
		$conf[]="SPFDPoolSize 5";
		$conf[]="SPFDMaxRequestsPerChild 1000";
		$conf[]="SPFDMaxQueueSize 200";
		$conf[]="SPFDCleanupInterval 600";
		$conf[]="ClientConnectTo tcp:127.0.0.1:2277";
		$conf[]="ClientConnectTimeout 40";
		$conf[]="ClientDataTimeout 30";
		$conf[]="ClientOnError accept";
		$conf[]="ClientDefaultDomain localhost";
		$conf[]="ClientFilteringSizeLimit 500";
		$conf[]="ClientMessageStoreMem 0";
		$conf[]="ClientTempDir /var/tmp";
		$conf[]="PipeInProtocol smtp";
		$conf[]="PipeOutProtocol smtp";
		$conf[]="PipeHELOGreeting kas30pipe.domain.tld";
		$conf[]="PipeOutgoingAddr exec:/usr/sbin/sendmail -bs -C /my/sendmail.cf";
		$conf[]="PipeOutgoingAddr tcp:127.0.0.1:9025";
		$conf[]="PipeOutConnectTimeout 40";
		$conf[]="PipeOutDataTimeout 300";
		$conf[]="PipeMultipleMessagesAllowed yes";
		$conf[]="Pipe8BitHack yes";
		$conf[]="PipeBufferedIO yes";
		$conf[]="PipeUseXForward no";
		$conf[]="SendMailAddress unix:/var/run/kas-milter.socket";
		$conf[]="SendMailDaemonise yes";
		$conf[]="QMailOriginalQueue /var/qmail/bin/qmail-queue.kas";
		$conf[]="#CGProSubmittedFolder (no defaults)";
		$conf[]="#CGProLoopHeader (no defaults)";
		$conf[]="#CGProMaxThreadCount 12";
		$conf[]="CGProAllTransports no";
		$conf[]="ControlCenterSendAlertsTo postmaster";
		$conf[]="ControlCenterLang en";
		$conf[]="MonitoringHttpd yes";
		$conf[]="MonitoringKasMilter yes";
		$conf[]="UseDefaultKasDNSBL no";
		
		@file_put_contents("/usr/local/ap-mailfilter3/etc/filter.conf",@implode("\n",$conf));


}

function BuildRobots(){
	$ldap=new clladp();
	
	$dn="cn=PostfixRobots,cn=artica,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd['cn'][0]="PostfixRobots";
		$upd['objectClass'][0]='PostFixStructuralClass';
		$upd['objectClass'][1]='top';
		if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error;}
		unset($upd);		
	}
	
	$dn="cn=artica,cn=PostfixRobots,cn=artica,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd['cn'][0]="artica";
		$upd['objectClass'][0]='PostFixStructuralClass';
		$upd['objectClass'][1]='top';
		if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error;}
		unset($upd);		
	}	
	
	$dn="cn=xspam@localhost.localdomain,cn=artica,cn=PostfixRobots,cn=artica,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd['cn'][0]="xspam@localhost.localdomain";
		$upd['objectClass'][0]='transportTable';
		$upd['objectClass'][1]='top';
		$upd["transport"][0]="artica-spam:xspam@localhost.localdomain";
		if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error;}
		unset($upd);		
	}
	
	$dn="cn=relay_domains,cn=artica,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd['cn'][0]="relay_domains";
		$upd['objectClass'][0]='PostFixStructuralClass';
		$upd['objectClass'][1]='top';
		if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error;}
		unset($upd);		
	}	
	
	$dn="cn=relay_recipient_maps,cn=artica,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd['cn'][0]="relay_recipient_maps";
		$upd['objectClass'][0]='PostFixStructuralClass';
		$upd['objectClass'][1]='top';
		if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error;}
		unset($upd);		
	}		
	
	$dn="cn=localhost.localdomain,cn=relay_domains,cn=artica,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd['cn'][0]="localhost.localdomain";
		$upd['objectClass'][0]='PostFixRelayDomains';
		$upd['objectClass'][1]='top';
		if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error;}
		unset($upd);		
	}

	$dn="cn=@localhost.localdomain,cn=relay_recipient_maps,cn=artica,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd['cn'][0]="@localhost.localdomain";
		$upd['objectClass'][0]='PostfixRelayRecipientMaps';
		$upd['objectClass'][1]='top';
		if(!$ldap->ldap_add($dn,$upd)){echo $ldap->ldap_last_error;}
		unset($upd);		
	}

	$dn="cn=transport_map,cn=artica,$ldap->suffix";
	if(!$ldap->ExistsDN($dn)){
		$upd['cn'][0]="transport_map";
		$upd['objectClass'][0]='PostFixStructuralClass';
		$upd['objectClass'][1]='top';
		$ldap->ldap_add($dn,$upd);
		unset($upd);		
		}	

	$dn="cn=localhost.localdomain,cn=transport_map,cn=artica,$ldap->suffix";
	$upd['cn'][0]="localhost.localdomain";
	$upd['objectClass'][0]='transportTable';
	$upd['objectClass'][1]='top';
	$upd["transport"][]="artica-spam:xspam@localhost.localdomain";
	$ldap->ldap_add($dn,$upd);	
	unset($upd);		
		

	
	

	
		
	
	
}


?>