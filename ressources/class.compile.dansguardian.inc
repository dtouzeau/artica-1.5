<?php
include_once(dirname(__FILE__)."/class.mysql.inc");
include_once(dirname(__FILE__)."/class.groups.inc");

class compile_dansguardian{
	var $squid_port=0;
	var $listen_port=0;
	
	function compile_dansguardian(){
		$ini=new Bs_IniHandler();
		$ini->loadFile("/etc/artica-postfix/settings/Daemons/ArticaSquidParameters");
		$this->squid_port=$ini->_params["NETWORK"]["ALT_PORT"];
		$this->listen_port=$ini->_params["NETWORK"]["LISTEN_PORT"];
	}
	
	
	function build(){
		$this->compile_default();
		
	}
	
	private function loadgroupsrules(){
		$GLOBALS["RULES"][0]=1;
		$q=new mysql_squid_builder();
		$sql="SELECT ID FROM webfilter_rules WHERE enabled=1";
		$results=$q->QUERY_SQL($sql);
		if(!$q->ok){echo "Starting......: DansGuardian Fatal error $q->mysql_error LINE: ". __LINE__."\n";}
		$c=1;;
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
			$c++;
			$GLOBALS["DGRULES"][$ligne["ID"]]=$c;
		}
		
	}
	
	private function filtergroupslist_assoc($ruleid,$groupid){
		$f=array();
		$q=new mysql_squid_builder();
		$sql="SELECT * FROM webfilter_group WHERE enabled=1 AND ID=$groupid";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql));
		if($ligne["ID"]==null){return;}
		$DGRULE=$GLOBALS["DGRULES"][$ruleid];
		$f[]="# rule id:$ruleid -> $DGRULE, groupid = $groupid";
		
		if($ligne["localldap"]==1){
			$gp=new groups($ligne["gpid"]);
				while (list ($num, $member) = each ($gp->members)){
					if(trim($member)==null){continue;}
					if(strpos($member, "$")>0){continue;}
					$f[]="$member=filter$DGRULE";
				}
		}
		
		$sql="SELECT * FROM webfilter_members WHERE enabled=1 AND groupid=$groupid";
		$results=$q->QUERY_SQL($sql);
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
			if(trim($ligne2["pattern"])==null){continue;}
			if($GLOBALS["VERBOSE"]){writelogs("$groupname {$ligne2["pattern"]}={$ligne2["membertype"]}",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
			if($ligne2["membertype"]==0){$f[]="{$ligne2["pattern"]}=filter$DGRULE";}
			if($ligne2["membertype"]==2){$f[]="{$ligne2["pattern"]}=filter$DGRULE";}
			if($ligne2["membertype"]==1){$f[]="{$ligne2["pattern"]}=filter$DGRULE";}		
		}
		
		return @implode("\n", $f);
		
	}
	
	
	private function filtergroupslist(){
		$q=new mysql_squid_builder();
		$sql="SELECT * FROM webfilter_assoc_groups";
		$results=$q->QUERY_SQL($sql);
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
			$webfilter_id=$ligne["webfilter_id"];
			$group_id=$ligne["group_id"];
			$f[]=$this->filtergroupslist_assoc($webfilter_id,$group_id);
		}
		
		if(!is_dir("/etc/dansguardian/lists")){@mkdir("/etc/dansguardian/lists",755,true);}
		@file_put_contents("/etc/dansguardian/lists/filtergroupslist", @implode("\n", $f));
		
	}
	
	private function dansguardianFrules(){
		$rules=$GLOBALS["DGRULES"];
		$q=new mysql_squid_builder();
		while (list ($ruleID, $DGRULE) = each ($rules)){
			$sql="SELECT * FROM webfilter_rules WHERE ID=$ruleID";
			$ligne=mysql_fetch_array($q->QUERY_SQL($sql));			
			$ligne["groupname"]=str_replace("'", "`", $ligne["groupname"]);
			if($ligne["blockdownloads"]==0){$ligne["blockdownloads"]="off";}else{$ligne["blockdownloads"]="on";}
			if($ligne["deepurlanalysis"]==0){$ligne["deepurlanalysis"]="off";}else{$ligne["deepurlanalysis"]="on";}
			if($ligne["sslcertcheck"]==0){$ligne["sslcertcheck"]="off";}else{$ligne["sslcertcheck"]="on";}
			$bannedsitelistname="bannedsitelist{$ruleID}";
			$exceptionsitelistname="exceptionsitelist{$ruleID}";
			
			$f=array();
			$f[]="groupmode = {$ligne["groupmode"]}";
			$f[]="groupname = '{$ligne["groupname"]}'";
			$f[]="bannedphraselist = '/etc/dansguardian/lists/bannedphraselist'";
			$f[]="weightedphraselist = '/etc/dansguardian/lists/weightedphraselist'";
			$f[]="exceptionphraselist = '/etc/dansguardian/lists/exceptionphraselist'";
			$f[]="bannedsitelist = '/etc/dansguardian/lists/$bannedsitelistname'";
			$f[]="greysitelist = '/etc/dansguardian/lists/greysitelist'";
			$f[]="exceptionsitelist = '/etc/dansguardian/lists/$exceptionsitelistname'";
			$f[]="bannedurllist = '/etc/dansguardian/lists/bannedurllist'";
			$f[]="greyurllist = '/etc/dansguardian/lists/greyurllist'";
			$f[]="exceptionurllist = '/etc/dansguardian/lists/exceptionurllist'";
			$f[]="exceptionregexpurllist = '/etc/dansguardian/lists/exceptionregexpurllist'";
			$f[]="bannedregexpurllist = '/etc/dansguardian/lists/bannedregexpurllist'";
			$f[]="picsfile = '/etc/dansguardian/lists/pics'";
			$f[]="contentregexplist = '/etc/dansguardian/lists/contentregexplist'";
			$f[]="urlregexplist = '/etc/dansguardian/lists/urlregexplist'";
			$f[]="blockdownloads = {$ligne["blockdownloads"]}";
			$f[]="exceptionextensionlist = '/etc/dansguardian/lists/exceptionextensionlist'";
			$f[]="exceptionmimetypelist = '/etc/dansguardian/lists/exceptionmimetypelist'";
			$f[]="bannedextensionlist = '/etc/dansguardian/lists/bannedextensionlist'";
			$f[]="bannedmimetypelist = '/etc/dansguardian/lists/bannedmimetypelist'";
			$f[]="exceptionfilesitelist = '/etc/dansguardian/lists/exceptionfilesitelist'";
			$f[]="exceptionfileurllist = '/etc/dansguardian/lists/exceptionfileurllist'";
			$f[]="#logsitelist = '/etc/dansguardian/lists/logsitelist'";
			$f[]="#logurllist = '/etc/dansguardian/lists/logurllist'";
			$f[]="#logregexpurllist = '/etc/dansguardian/lists/logregexpurllist'";
			$f[]="headerregexplist = '/etc/dansguardian/lists/headerregexplist'";
			$f[]="bannedregexpheaderlist = '/etc/dansguardian/lists/bannedregexpheaderlist'";
			$f[]="#weightedphrasemode = 0";
			$f[]="naughtynesslimit = {$ligne["naughtynesslimit"]}";
			$f[]="#searchengineregexplist = '/etc/dansguardian/lists/searchengineregexplist'";
			$f[]="#searchtermlimit = {$ligne["searchtermlimit"]}";
			$f[]="#bannedsearchtermlist = '/etc/dansguardian/lists/bannedsearchtermlist'";
			$f[]="#weightedsearchtermlist = '/etc/dansguardian/lists/weightedsearchtermlist'";
			$f[]="#exceptionsearchtermlist = '/etc/dansguardian/lists/exceptionsearchtermlist'";
			$f[]="categorydisplaythreshold = 0";
			$f[]="embeddedurlweight = 0";
			$f[]="enablepics = off";
			$f[]="bypass = {$ligne["bypass"]}";
			$f[]="bypasskey = ''";
			$f[]="infectionbypass = 0";
			$f[]="infectionbypasskey = ''";
			$f[]="infectionbypasserrorsonly = on";
			$f[]="disablecontentscan = off";
			$f[]="deepurlanalysis = {$ligne["deepurlanalysis"]}";
			$f[]="reportinglevel = 3";
			$f[]="#accessdeniedaddress = 'http://YOURSERVER.YOURDOMAIN/cgi-bin/dansguardian.pl'";
			$f[]="#htmltemplate = 'custom.html'";
			$f[]="sslcertcheck = {$ligne["sslcertcheck"]}";
			$f[]="sslmitm = off";
			@file_put_contents("/etc/dansguardian/dansguardianf$DGRULE.conf", @implode("\n", $f));
			$this->bannedsitelist($ruleID);
			$this->exceptionsitelist($ruleID);
						
		}
	}
	
	private function bannedsitelist($ruleID){
		$bannedsitelistname="bannedsitelist{$ruleID}";
		if(!is_dir("/etc/dansguardian/lists")){@mkdir("/etc/dansguardian/lists",755,true);}
		$sql="SELECT webfilter_id,category FROM webfilter_blks WHERE modeblk=0 AND webfilter_id=$ruleID"; 
		$q=new mysql_squid_builder();
		$f=array();
		$results=$q->QUERY_SQL($sql);
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
			$category=$ligne["category"];
			$categorypath="/var/lib/squidguard/".$q->category_transform_name($category)."/domains";
			if(!is_file($categorypath)){continue;}
			$f[]=".Include<$categorypath>";
		}
		@file_put_contents("/etc/dansguardian/lists/$bannedsitelistname", @implode("\n", $f));
	}
	
	private function exceptionsitelist($ruleID){
		$exceptionsitelistname="exceptionsitelist{$ruleID}";
		if(!is_dir("/etc/dansguardian/lists")){@mkdir("/etc/dansguardian/lists",755,true);}
		$sql="SELECT webfilter_id,category FROM webfilter_blks WHERE modeblk=1 AND webfilter_id=$ruleID"; 
		$q=new mysql_squid_builder();
		$f=array();
		$results=$q->QUERY_SQL($sql);
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
			$category=$ligne["category"];
			$categorypath="/var/lib/squidguard/".$q->category_transform_name($category)."/domains";
			if(!is_file($categorypath)){continue;}
			$f[]=".Include<$categorypath>";
		}
		@file_put_contents("/etc/dansguardian/lists/$bannedsitelistname", @implode("\n", $f));
	}	
	
	
	private function compile_default(){
			$this->loadgroupsrules();
			$filterNumber=count($GLOBALS["DGRULES"]);
			$f[]="reportinglevel = 3";
			$f[]="languagedir = '/usr/share/dansguardian/languages'";
			$f[]="language = 'ukenglish'";
			$f[]="loglevel = 2";
			$f[]="logexceptionhits = 2";
			$f[]="logfileformat = 1";
			$f[]="maxlogitemlength = 0";
			$f[]="anonymizelogs = off";
			$f[]="logsyslog = on";
			$f[]="loglocation = '/var/log/dansguardian/access.log'";
			$f[]="statlocation = '/var/log/dansguardian/stats'";
			$f[]="filterip =";
			$f[]="filterports = $this->listen_port";
			$f[]="proxyip = 127.0.0.1";
			$f[]="proxyport = $this->squid_port";
			$f[]="originalip = off";
			$f[]="accessdeniedaddress = 'http://YOURSERVER.YOURDOMAIN/cgi-bin/dansguardian.pl'";
			$f[]="nonstandarddelimiter = on";
			$f[]="usecustombannedimage = on";
			$f[]="custombannedimagefile = '/usr/share/dansguardian/transparent1x1.gif'";
			$f[]="usecustombannedflash = on";
			$f[]="custombannedflashfile = '/usr/share/dansguardian/blockedflash.swf'";
			$f[]="filtergroups = $filterNumber";
			$f[]="filtergroupslist = '/etc/dansguardian/lists/filtergroupslist'";
			$f[]="bannediplist = '/etc/dansguardian/lists/bannediplist'";
			$f[]="exceptioniplist = '/etc/dansguardian/lists/exceptioniplist'";
			$f[]="perroomblockingdirectory = '/etc/dansguardian/lists/bannedrooms/'";
			$f[]="showweightedfound = on";
			$f[]="weightedphrasemode = 2";
			$f[]="urlcachenumber = 1000";
			$f[]="urlcacheage = 900";
			$f[]="scancleancache = on";
			$f[]="phrasefiltermode = 2";
			$f[]="preservecase = 0";
			$f[]="hexdecodecontent = off";
			$f[]="forcequicksearch = off";
			$f[]="reverseaddresslookups = off";
			$f[]="reverseclientiplookups = off";
			$f[]="logclienthostnames = off";
			$f[]="createlistcachefiles = on";
			$f[]="prefercachedlists = off";
			$f[]="maxuploadsize = -1";
			$f[]="maxcontentfiltersize = 256";
			$f[]="maxcontentramcachescansize = 2000";
			$f[]="maxcontentfilecachescansize = 20000";
			$f[]="filecachedir = '/tmp'";
			$f[]="deletedownloadedtempfiles = on";
			$f[]="initialtrickledelay = 20";
			$f[]="trickledelay = 10";
			$f[]="downloadmanager = '/etc/dansguardian/downloadmanagers/fancy.conf'";
			$f[]="downloadmanager = '/etc/dansguardian/downloadmanagers/default.conf'";
			$f[]="#contentscanner = '/etc/dansguardian/contentscanners/icapscan.conf'";
			$f[]="contentscannertimeout = 60";
			$f[]="contentscanexceptions = off";
			$f[]="recheckreplacedurls = off";
			$f[]="forwardedfor = on";
			$f[]="usexforwardedfor = on";
			$f[]="logconnectionhandlingerrors = on";
			$f[]="logchildprocesshandling = off";
			$f[]="maxchildren = 120";
			$f[]="minchildren = 8";
			$f[]="minsparechildren = 4";
			$f[]="preforkchildren = 6";
			$f[]="maxsparechildren = 32";
			$f[]="maxagechildren = 500";
			$f[]="maxips = 0";
			$f[]="ipcfilename = '/tmp/.dguardianipc'";
			$f[]="urlipcfilename = '/tmp/.dguardianurlipc'";
			$f[]="ipipcfilename = '/tmp/.dguardianipipc'";
			$f[]="pidfilename = '/var/run/dansguardian.pid/dansguardian.pid'";
			$f[]="nodaemon = off";
			$f[]="nologger = off";
			$f[]="logadblocks = off";
			$f[]="loguseragent = off";
			$f[]="daemonuser = 'squid'";
			$f[]="daemongroup = 'squid'";
			$f[]="softrestart = off";
			$f[]="#sslcertificatepath = '/etc/ssl/certs/'";
			$f[]="#cacertificatepath = '/home/stephen/dginstall/ca.pem'";
			$f[]="#caprivatekeypath = '/home/stephen/dginstall/ca.key'";
			$f[]="#certprivatekeypath = '/home/stephen/dginstall/cert.key'";
			$f[]="#generatedcertpath = '/home/stephen/dginstall/generatedcerts/'";
			$f[]="#generatedlinkpath = '/home/stephen/dginstall/generatedlinks/'";		
			if(!is_dir("/etc/dansguardian")){@mkdir("/etc/dansguardian",755,true);}
			@file_put_contents("/etc/dansguardian/dansguardian.conf", @implode("\n", $f));
			$this->dansguardianFrules();
		
	}
	
	
	
}
