<?php

if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}
include_once(dirname(__FILE__)."/ressources/class.user.inc");
include_once(dirname(__FILE__)."/ressources/class.groups.inc");
include_once(dirname(__FILE__)."/ressources/class.ldap.inc");
include_once(dirname(__FILE__)."/ressources/class.system.network.inc");
include_once(dirname(__FILE__)."/ressources/class.dansguardian.inc");
include_once(dirname(__FILE__)."/ressources/class.mysql.inc");
include_once(dirname(__FILE__).'/framework/class.unix.inc');
include_once(dirname(__FILE__)."/framework/frame.class.inc");

if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["VERBOSE"]=true;$GLOBALS["cmdlineadd"]=" --verbose";}
$_GET["LOGFILE"]="/var/log/artica-postfix/dansguardian.compile.log";
if(preg_match("#--verbose#",implode(" ",$argv))){$GLOBALS["debug"]=true;}
if(posix_getuid()<>0){die("Cannot be used in web server mode\n\n");}

include_once(dirname(__FILE__).'/framework/class.unix.inc');
if(!Build_pid_func(__FILE__,"MAIN")){
	writelogs(basename(__FILE__).":Already executed.. aborting the process",basename(__FILE__),__FILE__,__LINE__);
	die();
}


if($argv[1]=="--missing-groups"){FixMissingGroupsFiles();FixMissingFiles();die();}
if($argv[1]=="--patterns"){BuildPatterns();echo "\n";die();}
if($argv[1]=="--clean-db"){CleanDB();echo "\n";die();}
//exec.dansguardian.compile.php --patterns
echo "\n";
echo "Starting......: Dansguardian reconfigure settings\n";
$q=new mysql();
if(!$q->test_mysql_connection()){
	echo "Starting......: Dansguardian Mysql error\n";
	die();
}
$q->CheckTable_dansguardian();

LoadGlobal_exceptionsitelist();

$dans=new dansguardian();
$dans->SaveSettings();
$cmd=LOCATE_PHP5_BIN2()." ".dirname(__FILE__)."/exec.web-community-filter.php --patterns{$GLOBALS["cmdlineadd"]}";
events("MAIN:: $cmd");
system($cmd);

HtmlTemplate();
BuildPersonalCategories();
bannedsitelist_userdefined();
weightedphraselist_userdefined();
BuildRules();
BuildWhiteIpList();
BuildBannedIPList();
FixMissingGroupsFiles();
FixMissingFiles();
BuildMasterRule();

echo "Starting......: Dansguardian reconfigure settings done\n";

function HtmlTemplate(){
	$sock=new sockets();
	$DansGuardianHTMLTemplate=$sock->GET_INFO("DansGuardianHTMLTemplate");
	if(strlen($DansGuardianHTMLTemplate)>100){
		@file_put_contents("/etc/dansguardian/languages/ukenglish",$DansGuardianHTMLTemplate);
	}
	
}




function FixMissingFiles(){
	
	$goodphrases[]="weighted_general_polish";
	$goodphrases[]="weighted_general_swedish";
	$goodphrases[]="exception";
	$goodphrases[]="exception_email";
	$goodphrases[]="weighted_general_danish";
	$goodphrases[]="weighted_general_portuguese";
	$goodphrases[]="weighted_general_dutch";
	$goodphrases[]="weighted_general_malay";
	$goodphrases[]="weighted_general";
	$goodphrases[]="weighted_news";
	
	@mkdir("/etc/dansguardian/lists/phraselists/goodphrases",0666,true);
	
	while (list ($num, $file) = each ($goodphrases) ){
		if(!is_file("/etc/dansguardian/lists/phraselists/goodphrases/$file")){
			echo "Starting......: Dansguardian installing goodphrases/$file\n";
			if(is_file("/etc/dansguardian/phraselists/goodphrases/$file")){
				@copy("/etc/dansguardian/phraselists/goodphrases/$file","/etc/dansguardian/lists/phraselists/goodphrases/$file");
				continue;
			}
			
			@file_put_contents("/etc/dansguardian/lists/phraselists/goodphrases/$file","#");
		}
	}
	
	
	
		
	if(!is_dir("/etc/dansguardian/languages")){
		if(is_dir("/usr/share/dansguardian/languages")){
			shell_exec("/bin/cp -rf /usr/share/dansguardian/languages /etc/dansguardian/");
		}
	}
	
	$f[]="/etc/dansguardian/languages/ukenglish/messages";
	
	
	
}


function FixMissingGroupsFiles(){
	$unix=new unix();
	$f=$unix->dirdir("/etc/dansguardian");
	while (list ($num, $line) = each ($f) ){
		if(preg_match("#\/group_#",$num)){
			
			//----------------------------------------
			if(is_file("/etc/dansguardian/pics")){
				if(!is_file("$num/pics")){
					@copy("/etc/dansguardian/pics","$num/pics");
				}
			}
			//----------------------------------------
			
		}
	}
	
}




function events($text){
		$pid=getmypid();
		$logFile=$_GET["LOGFILE"];
		$size=filesize($logFile);
		if($size>1000000){unlink($logFile);}
		$f = @fopen($logFile, 'a');
		if($GLOBALS["debug"]){echo "$pid $text\n";}
		@fwrite($f, "$pid $text\n");
		@fclose($f);	
		}


function BuildMasterRule(){
	$dans=new dansguardian();
	$file=$dans->BuildConfig();
	events(__FUNCTION__.":: Writing /etc/dansguardian/dansguardian.conf ". strlen($file)." bytes");
	@file_put_contents("/etc/dansguardian/dansguardian.conf",$file);
	CheckFilesDatabases($file,1);
	CheckAuthMethods();
	DEFAULT_RULE_BANNEDSITE_LISTS();
	}
	
	
function CheckAuthMethods(){
	
$sock=new sockets();
	$hasProxyTransparent=$sock->GET_INFO("hasProxyTransparent");
	
	$squid=new squidbee();
	$LDAP_AUTH=$squid->LDAP_AUTH;
	
	if($hasProxyTransparent==1){
		$IP_SET=true;
	}else{
		if($LDAP_AUTH==0){$IP_SET=true;}
		if($LDAP_AUTH==1){$IP_SET=FALSE;}
	}
	
	if($IP_SET){
		WriteConfigFile("/etc/dansguardian/dansguardian.conf","authplugin","/etc/dansguardian/authplugins/ip.conf");
		DeleteConfigFile("/etc/dansguardian/dansguardian.conf","filtergroupslist");
		
		BuildIPAuth();
		return;
	}
		WriteConfigFile("/etc/dansguardian/dansguardian.conf","authplugin","/etc/dansguardian/authplugins/proxy-basic.conf");
		WriteConfigFile("/etc/dansguardian/dansguardian.conf","filtergroupslist","/etc/dansguardian/filtergroupslist");	
		BuildProxyBasic();
		return;		
	
}

function BuildProxyBasic(){
	
$conf=$conf."# Proxy-Basic auth plugin\n";
$conf=$conf."# Identifies usernames in \"Proxy-Authorization: Basic\" headers;\n";
$conf=$conf."# relies upon the upstream proxy (squid) to perform the actual password check.\n";
$conf=$conf."\n";
$conf=$conf."plugname = 'proxy-basic'\n";	
@mkdir("/etc/dansguardian/lists/authplugins");
@file_put_contents("/etc/dansguardian/authplugins/proxy-basic.conf",$conf);	
$conf=null;
$sql="SELECT * FROM dansguardian_groups ORDER BY RuleID DESC";
$q=new mysql();
$results=$q->QUERY_SQL($sql,"artica_backup");
 while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$num=$ligne["ID"];
			$gpid=$ligne["group_id"];
			$group=new groups($gpid);
			$dansrule=$GLOBALS["DANSGUARDIAN_RULES_INDEX"][$ligne["RuleID"]];
			if($dansrule==0){events(__FUNCTION__.":: RULE IS 0, banned integer, set to 1");$dansrule=1;}
			if($GLOBALS["debug"]){events(__FUNCTION__.":: {$ligne["pattern"]} = filter$dansrule");}
			if(!is_array($group->members)){continue;}
			while (list ($num, $user) = each ($group->members) ){
				if(strpos($user,'$')>0){continue;}
				if($user==null){continue;}
				$conf=$conf."$user=filter$dansrule\n";
				$count=$count+1;
			}
			
			
		
	}
	
	events(__FUNCTION__.":: Writing /etc/dansguardian/filtergroupslist for $count users");
	echo "Starting......: Dansguardian rules match authenticated $count users\n";
	@file_put_contents("/etc/dansguardian/filtergroupslist",$conf);
}

	
function BuildIPAuth(){
	
@mkdir("/etc/dansguardian/authplugins",666,true);	
$conf=$conf."# IP-based auth plugin\n";
$conf=$conf."#\n";
$conf=$conf."# Maps client IPs to filter groups.\n";
$conf=$conf."# If \"usexforwardedfor\" is enabled, grabs the IP from the X-Forwarded-For\n";
$conf=$conf."# header, if available.\n";
$conf=$conf."\n";
$conf=$conf."plugname = 'ip'\n";
$conf=$conf."#ipgroups file\n";
$conf=$conf."#List file assigning IP addresses, subnets and ranges to filter groups\n";
$conf=$conf."ipgroups = '/etc/dansguardian/lists/authplugins/ipgroups'\n";	
@mkdir("/etc/dansguardian/lists/authplugins");
@file_put_contents("/etc/dansguardian/authplugins/ip.conf",$conf);
$conf=null;	
	
	$sql="SELECT * FROM dansguardian_ipgroups ORDER BY RuleID";
	$q=new mysql();
	$count=0;
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$count=$count+1;
		$dansrule=$GLOBALS["DANSGUARDIAN_RULES_INDEX"][$ligne["RuleID"]];
		if($dansrule==0){events(__FUNCTION__.":: RULE IS 0, banned integer, set to 1");$dansrule=1;}
		if($GLOBALS["debug"]){events(__FUNCTION__.":: {$ligne["pattern"]} = filter$dansrule");}
		$conf=$conf."{$ligne["pattern"]}=filter$dansrule\n";
	}
	
	events(__FUNCTION__.":: Writing /etc/dansguardian/lists/authplugins/ipgroups for $count addresses");
	echo "Starting......: Dansguardian rules match $count IP addresses\n";
	@file_put_contents("/etc/dansguardian/lists/authplugins/ipgroups",$conf);
}


function BuildRules(){
	$dans=new dansguardian();
	$sql="SELECT RuleID,RuleName,RuleText FROM dansguardian_rules WHERE RuleID>1 ORDER BY RuleID";
		$q=new mysql();
		$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			if($ligne["RuleID"]==1){continue;}
			$count=$count+1;
			$GLOBALS["DANSGUARDIAN_RULES_INDEX"][$ligne["RuleID"]]=$count;
			
			$dansguardian=new dansguardian_rules(null,$ligne["RuleID"]);
			$ligne["RuleText"]=$dansguardian->BuildMainRule();
			
			events(__FUNCTION__.":: ----------------------------------------------------------------------------------------");
			events(__FUNCTION__.":: Writing /etc/dansguardian/dansguardianf$count.conf ID:{$ligne["RuleID"]} Name:{$ligne["RuleName"]} ". strlen($ligne["RuleText"])." bytes");
			events(__FUNCTION__.":: ----------------------------------------------------------------------------------------");
			@file_put_contents("/etc/dansguardian/dansguardianf$count.conf",$ligne["RuleText"]);
			CheckFilesDatabases($ligne["RuleText"],$ligne["RuleID"]);
			ChangeRuleName($count,$ligne["RuleName"]);
			if($dans->ContentScannerMustEnabled()){
				events(__FUNCTION__.":: clamav is enabled checkit");
				WriteConfigFile("/etc/dansguardian/dansguardianf$count.conf","contentscanner","'/etc/dansguardian/contentscanners/clamdscan.conf'");
			}else{
				events(__FUNCTION__.":: clamav is disbaled delete it");
				DeleteConfigFile("/etc/dansguardian/dansguardianf$count.conf","contentscanner");
			}
			
		}
		
		if(!is_file("/etc/dansguardian/dansguardianf1.conf")){copy("/etc/dansguardian/dansguardian.conf /etc/dansguardian/dansguardianf1.conf");}
	
}

function ChangeRuleName($index,$name){
	$file="/etc/dansguardian/dansguardianf$index.conf";
$f=explode("\n",@file_get_contents("$file"));
if(!is_array($f)){
	events(__FUNCTION__.":: $file fatal error on explode");
	return;	
}

while (list ($num, $line) = each ($f) ){
	if(preg_match("#^groupname?(.+)#",$line,$re)){
		$re[1]=str_replace("'","",$re[1]);
		$re[1]=str_replace('"',"",$re[1]);
		$re[1]=str_replace('=',"",$re[1]);
		$re[1]=trim($re[1]);
		events(__FUNCTION__.":: $file-> name={$re[1]}");
		if($re[1]=="Default rule"){
			$f[$num]="groupname = $name";
			events(__FUNCTION__.":: $file-> name=$name");
		}
	}
}

@file_put_contents($file,implode("\n",$f));
	
}

function bannedsitelist_userdefined(){
	$sql="SELECT category_name,	pattern FROM dansguardian_categories";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$array[$ligne["category_name"]][]=$ligne["pattern"];
			
		}
		
	if(is_array($array)){
		while (list ($filename, $arrayP) = each ($array) ){
			$path="/etc/dansguardian/lists/blacklists/$filename";
			@mkdir($path,0666,true);
			while (list ($index, $domain) = each ($arrayP) ){
				$conf=$conf."$domain\n";
			}
			@file_put_contents("$path/domain",$conf);
			$conf=null;
			
		}
	}
	
}
function weightedphraselist_userdefined(){
	$sql="SELECT category_name,	pattern FROM dansguardian_weightedphraselist";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
		while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
			$array[$ligne["category_name"]][]=$ligne["pattern"];
			
		}
	///etc/dansguardian/lists/phraselists/$pattern/weighted	
	if(is_array($array)){
		while (list ($filename, $arrayP) = each ($array) ){
			$path="/etc/dansguardian/lists/phraselists/$filename";
			@mkdir($path,0666,true);
			while (list ($index, $domain) = each ($arrayP) ){
				$conf=$conf."$domain\n";
			}
			@file_put_contents("$path/weighted",$conf);
			$conf=null;
			
		}
	}
	
}
	
	
function CheckFilesDatabases($content,$ruleid){
	$filesToCheck=LoadDansArray();
	$config=ConfigToArray($content);
	events(__FUNCTION__.":: Checking ". count($filesToCheck). " files databases with ". count($config). " rows config");
	reset($filesToCheck);
	while (list ($num, $key) = each ($filesToCheck) ){
		
		if($config[$key]<>null){
			$filepath=trim($config[$key]);
			$dirpath=dirname($filepath);
			@mkdir($dirpath,0666,true);
			$basename=basename($filepath);
			events(__FUNCTION__.":: Checking rule N.$ruleid in file=$basename");
			if(!is_file($filepath)){@file_put_contents($filepath,'#');}
			$content=getDatabaseContent($ruleid,$basename);
			@file_put_contents($filepath,$content);
			continue;
		}else{
			events(__FUNCTION__.":: config[$key] SKIPPED !");
		}
	}
	
}


function DEFAULT_RULE_BANNEDSITE_LISTS(){
	
	$conf=bannedsitelist(1);
	@file_put_contents("/etc/dansguardian/bannedsitelist",$conf);
	DeleteConfigFile("/etc/dansguardian/dansguardian.conf","bannedsitelist");
	if(strlen($conf)>0){
		WriteConfigFile("/etc/dansguardian/dansguardian.conf","bannedsitelist","/etc/dansguardian/bannedsitelist");
	}
	
}





function LoadGlobal_exceptionsitelist(){
	$sql="SELECT * FROM dansguardian_files WHERE filename='exceptionsitelist' AND RuleID=1 AND enabled=1";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$count=$count+1;
		$_GET["GLOBAL_EXCEPTIONS"][]="#{$ligne["infos"]} (Global Rule)\n{$ligne["pattern"]}";
		}
	
}

function getDatabaseContent($ruleid,$basename){
	
	if($basename=="bannedsitelist"){return bannedsitelist($ruleid);}
	if($basename=="weightedphraselist"){return weightedphraselist($ruleid);}
	
	$sql="SELECT * FROM dansguardian_files WHERE filename='$basename' AND RuleID=$ruleid AND enabled=1";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	$count=0;
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$count=$count+1;
		$conf=$conf. "#{$ligne["infos"]}\n{$ligne["pattern"]}\n\n";
		
	}
	if($count>0){events(__FUNCTION__.":: Rule $ruleid ($basename) $count lines");}
	
	if($ruleid<>1){
		if($basename=="exceptionsitelist"){
			if(is_array($_GET["GLOBAL_EXCEPTIONS"])){
				$conf=$conf.implode("\n",$_GET["GLOBAL_EXCEPTIONS"])."\n";
			}
		}
	}
	
	return $conf;
	
}

function bannedsitelist($rulid){
	$fid=$GLOBALS["DANSGUARDIAN_RULES_INDEX"][$rulid];
	events(__FUNCTION__."::checking bannedsitelist Mysqlid=$rulid; dansguardianf$fid.conf");
	
	
	$sql="SELECT pattern,infos FROM dansguardian_files WHERE filename='bannedsitelist' AND RuleID=$rulid";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	$count=0;
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$pattern=trim($ligne["pattern"]);
		if(is_file("/etc/dansguardian/lists/blacklists/$pattern/domains")){
			$count=$count+1;
			if($ligne["infos"]<>null){$ligne["infos"]="#{$ligne["infos"]}\n";}
			$conf=$conf. "{$ligne["infos"]}.Include</etc/dansguardian/lists/blacklists/$pattern/domains>\n";
		}else{
			events(__FUNCTION__."::$rulid;[$pattern] could not find /etc/dansguardian/lists/blacklists/$pattern/domains");
		}
		
		
		if(is_file("/etc/dansguardian/lists/web-filter-plus/BL/$pattern/domains")){
			$count=$count+1;
			if($ligne["infos"]<>null){$ligne["infos"]="#{$ligne["infos"]}\n";}
			$conf=$conf. "{$ligne["infos"]}.Include</etc/dansguardian/lists/web-filter-plus/BL/$pattern/domains>\n";
		}else{
			events(__FUNCTION__."::$rulid;[$pattern] could not find /etc/dansguardian/lists/web-filter-plus/BL/$pattern/domains");
		}
		
		if(is_file("/etc/dansguardian/lists/blacklist-artica/$pattern/domains")){
			$count=$count+1;
			if($ligne["infos"]<>null){$ligne["infos"]="#{$ligne["infos"]}\n";}
			$conf=$conf. "{$ligne["infos"]}.Include</etc/dansguardian/lists/blacklist-artica/$pattern/domains>\n";
		}else{
		  events(__FUNCTION__."::$rulid;[$pattern] could not find /etc/dansguardian/lists/blacklist-artica/$pattern/domains");		
		}
		
		
		
		
	}
	
	$sql="SELECT category FROM dansguardian_personal_categories WHERE category_type = 'enabled' AND RuleID=$rulid GROUP BY category";
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$pattern=trim($ligne["category"]);
		if(is_file("/etc/dansguardian/lists/personal-blacklists/$pattern/domains")){
			$count=$count+1;
			$conf=$conf. "#Personal Category $pattern\n.Include</etc/dansguardian/lists/personal-blacklists/$pattern/domains>\n";
		}else{
			events(__FUNCTION__."::$rulid;[$pattern] could not find /etc/dansguardian/lists/personal-blacklists/$pattern/domains");
		}
		
	}	
	

	
	events(__FUNCTION__.":: Rule $rulid (bannedsitelist) $count lines");
	return $conf;
}

function BuildPersonalCategories(){

	@mkdir("/etc/dansguardian/lists/personal-blacklists",755,true);
	$sql="SELECT category,pattern FROM `dansguardian_personal_categories`";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	$count=0;
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		if(trim($ligne["pattern"])==null){continue;}
		if(trim($ligne["category"])==null){continue;}
		$array[$ligne["category"]][]=$ligne["pattern"];	
	}
	
	if(!is_array($array)){
		events(__FUNCTION__."::Building personal category no category found"); 
		return null;
	}
	
	
	while (list ($num, $array2) = each ($array) ){
		$datas=implode("\n",$array2);
		events(__FUNCTION__."::Building personal category personal-blacklists/$num"); 
		@mkdir("/etc/dansguardian/lists/personal-blacklists/$num",755,true);
		@file_put_contents("/etc/dansguardian/lists/personal-blacklists/$num/domains",$datas);
	}

}






function weightedphraselist($rulid){
	$good[]="exception";
	$good[]="exception_email";
	$good[]="weighted_general";  
	$good[]="weighted_general_danish";
	$good[]="weighted_general_dutch";
	$good[]="weighted_general_malay";
	$good[]="weighted_general_polish";
	$good[]="weighted_general_portuguese";
	$good[]="weighted_general_swedish";
	$good[]="weighted_news";
	$conf="#Good Phrases (to allow medical, education, news and other good sites)\n";
	while (list ($num, $filename) = each ($good) ){
		if(!is_file("/etc/dansguardian/lists/phraselists/goodphrases/$filename")){
			$conf=$conf.".Include</etc/dansguardian/lists/phraselists/goodphrases/$filename>\n";
		}
		
	}
	
	$conf=$conf."\n\n";
	$sql="SELECT pattern,infos FROM dansguardian_files WHERE filename='weightedphraselist' AND RuleID=$rulid";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	$count=0;
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){	
	$pattern=trim($ligne["pattern"]);
		if(is_file("/etc/dansguardian/lists/phraselists/$pattern/weighted")){
			$count=$count+1;
			$conf=$conf. "#{$ligne["infos"]}\n.Include</etc/dansguardian/lists/phraselists/$pattern/weighted>\n\n";
		}
		
	}
	events(__FUNCTION__.":: Rule $rulid (weightedphraselist) $count lines");
	return $conf;
}

	
function LoadDansArray(){
	$DANS_FILES[]="bannedphraselist";
	$DANS_FILES[]="weightedphraselist";
	$DANS_FILES[]="exceptionphraselist";
	$DANS_FILES[]="bannedsitelist";
	$DANS_FILES[]="greysitelist";
	$DANS_FILES[]="exceptionsitelist";
	$DANS_FILES[]="bannedurllist";
	$DANS_FILES[]="greyurllist";
	$DANS_FILES[]="exceptionurllist";
	$DANS_FILES[]="exceptionregexpurllist";
	$DANS_FILES[]="bannedregexpurllist";
	$DANS_FILES[]="contentregexplist";
	$DANS_FILES[]="urlregexplist";
	$DANS_FILES[]="exceptionextensionlist";
	$DANS_FILES[]="exceptionmimetypelist";
	$DANS_FILES[]="bannedextensionlist";
	$DANS_FILES[]="bannedmimetypelist";
	$DANS_FILES[]="exceptionfilesitelist";
	$DANS_FILES[]="exceptionfileurllist";
	$DANS_FILES[]="logsitelist";
	$DANS_FILES[]="logurllist";
	$DANS_FILES[]="logregexpurllist";
	$DANS_FILES[]="headerregexplist";
	$DANS_FILES[]="bannedregexpheaderlist";
	$DANS_FILES[]="filtergroupslist";
	$DANS_FILES[]="bannediplist";
	$DANS_FILES[]="exceptioniplist";
	$DANS_FILES[]="banneduserlist";
	$DANS_FILES[]="exceptionuserlist";
	
	return $DANS_FILES;
}

function ConfigToArray($content){
	$tb=explode("\n",$content);
	while (list ($num, $val) = each ($tb) ){
		if(preg_match('#(.+?)[=\s]+(.+)#',$val,$re)){
			$re[2]=str_replace("'","",$re[2]);
			$array[trim($re[1])]=trim($re[2]);
			}
				
		}
	return $array;
}

function BuildWhiteIpList(){
	$sql="SELECT pattern,infos FROM dansguardian_files WHERE filename='exceptioniplist' AND RuleID=1";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	$count=0;
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$pattern=trim($ligne["pattern"]);
		if($pattern==null){continue;}
		$conf=$conf. "$pattern\n";
		$count=$count+1;
		
	}
	
	events(__FUNCTION__.":: Rule 1 (exceptioniplist) $count lines ". strlen($conf)." bytes");
	@file_put_contents("/etc/dansguardian/exceptioniplist",$conf);
	
}

function BuildBannedIPList(){
	$sql="SELECT pattern,infos FROM dansguardian_files WHERE filename='bannediplist' AND RuleID=1";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	$count=0;
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$pattern=trim($ligne["pattern"]);
		if($pattern==null){continue;}
		$conf=$conf. "$pattern\n";
		$count=$count+1;
		
	}
	
	events(__FUNCTION__.":: Rule 1 (exceptioniplist) $count lines ". strlen($conf)." bytes");
	@file_put_contents("/etc/dansguardian/bannediplist",$conf);	
}

function BuildPatterns(){
	$unix=new unix();
	cpulimit();
	
	$dirs=$unix->dirdir("/etc/dansguardian/lists/blacklists");
	if($GLOBALS["VERBOSE"]){echo "open /etc/dansguardian/lists/blacklists array of ". count($dirs)."\n";}
	
	if(!is_array($dirs)){writelogs("Unable to dir /etc/dansguardian/lists/blacklists",__FUNCTION__,__FILE__,__LINE__);return;}
	reset($dirs);
	while (list ($num, $val) = each ($dirs) ){
		$category=basename($num);
		if($GLOBALS["VERBOSE"]){echo "$category:: $num -> $val\n";}
		
		writelogs("Checking $category",__FUNCTION__,__FILE__,__LINE__);
		
		if($category=="blacklists"){
			if($GLOBALS["VERBOSE"]){echo "$category == blacklists, aborting\n";}
			continue;
		}
		$domains=0;
		$urls=0;
		$expressions=0;
		
		
		if(is_file("$num/domains")){
			$domains=$unix->COUNT_LINES_OF_FILE("$num/domains");
			if($GLOBALS["VERBOSE"]){echo "$category:: $domains number\n";}
			$filetime=date("Y-m-d H:i:s",filemtime("$num/domains"));
			
		}else{
			if($GLOBALS["VERBOSE"]){echo "$category:: unable to stat $num/domains\n";}
		}
		
		if(is_file("$num/urls")){
			$urls=$unix->COUNT_LINES_OF_FILE("$num/urls");
			
		}else{if($GLOBALS["VERBOSE"]){echo "$category:: unable to stat $num/urls\n";}}
	
		
			if($GLOBALS["VERBOSE"]){echo "$category=$domains,$urls,$filetime\n";}
			$array["$category"]=array($domains,$urls,$filetime);
		
		}
		
		
		
		$datas=base64_encode(serialize($array));
		writelogs("writing /usr/share/artica-postfix/ressources/logs/dansguardian.patterns",__FUNCTION__,__FILE__,__LINE__);
		@file_put_contents("/usr/share/artica-postfix/ressources/logs/dansguardian.patterns",$datas);
		
		@chmod("/usr/share/artica-postfix/ressources/logs/dansguardian.patterns",0755);
		if(!is_file("/usr/share/artica-postfix/ressources/logs/dansguardian.patterns")){
			writelogs("Error writing dansguardian.patterns",__FUNCTION__,__FILE__,__LINE__);
		}
		return;		
}

function WriteConfigFile($file,$key,$value){
	$f=false;
	$datas=explode("\n",@file_get_contents($file));
	while (list ($num, $line) = each ($datas) ){
		if(preg_match("#^$key#",$line)){
			$f=true;
			$datas[$num]="$key='$value'";
		}
	}
	
	if(!$f){$datas[]="$key='$value'";}
	@file_put_contents($file,implode("\n",$datas));
}

function DeleteConfigFile($file,$key){
$datas=explode("\n",@file_get_contents($file));
	while (list ($num, $line) = each ($datas) ){
		if(preg_match("#^$key#",$line)){unset($datas[$num]);break;}
	}
	@file_put_contents($file,implode("\n",$datas));	
	
}

function CleanDB(){
	$sql="TRUNCATE TABLE `dansguardian_categories`";
	$q=new mysql();
	$q->QUERY_SQL($sql,"artica_backup");
	
	$sql="TRUNCATE TABLE `dansguardian_files`";
	$q->QUERY_SQL($sql,"artica_backup");	
	
	$sql="TRUNCATE TABLE `dansguardian_groups`";
	$q->QUERY_SQL($sql,"artica_backup");

	$sql="TRUNCATE TABLE `dansguardian_ipgroups`";
	$q->QUERY_SQL($sql,"artica_backup");
	
	$sql="TRUNCATE TABLE `dansguardian_rules`";
	$q->QUERY_SQL($sql,"artica_backup");
	
	$sql="TRUNCATE TABLE `dansguardian_weightedphraselist`";
	$q->QUERY_SQL($sql,"artica_backup");	
	
	
}




		
?>