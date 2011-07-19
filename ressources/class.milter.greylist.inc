<?php
include_once(dirname(__FILE__)."/class.ldap.inc");
include_once(dirname(__FILE__)."/class.maincf.multi.inc");

class milter_greylist{
	var $hostname;
	var $ou;
	var $dn;
	var $global_conf;
	var $main_array;
	var $acl;
	var $dnsrbl;
	var $dnsrbl_array;
	var $dnsrbl_class;
	var $MilterGreyListEnabled;
	var $__noparse;
	var $mynetworks=array();
	var $actionlist=array(
							null=>"{select}",
							"addr"=>"{addr}",
							"urlcheck"=>"{urlcheck}",
							"body"=>"{body}",
							"header"=>"{header}",
							"domain"=>"{domain}",
							"from"=>"{from}",
							"rcpt"=>"{rcpt}",
							"dnsrbl"=>"{dnsrbl}");
	
	
function milter_greylist($noparse=false,$hostname=null,$ou=null){
	$this->__noparse=$noparse;
	$this->hostname=$hostname;
	$this->ou=$ou;
	if($noparse){
		$this->loadConfig();
		return true;
		}
	

	$this->loadConfig();
	$this->ParseConfig();
	
}




function LoadConfig(){
	
	if(($this->hostname<>null) && ($this->ou<>null)){
		WriteLogs("Multi-postfix instance: $this->hostname, $this->ou",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$main=new maincf_multi($this->hostname,$this->ou);
		$this->global_conf=base64_decode($main->GET_BIGDATA("milter-greylist-config"));
		$this->mynetworks=unserialize($main->GET_BIGDATA("mynetworks"));
		return ;
	}
	
	$sock=new sockets();
	$ldap=new clladp();
	if(!$ldap->ldapFailed){
		$this->mynetworks=$ldap->load_mynetworks();
	}
	$this->global_conf=base64_decode($sock->GET_INFO("GreyListConf"));
	$this->MilterGreyListEnabled=$sock->GET_INFO('MilterGreyListEnabled');
	
		
}

function SaveToLdap(){
		$sock=new sockets();	
	if(($this->hostname<>null) && ($this->ou<>null)){
		WriteLogs("Multi-postfix instance: $this->hostname, $this->ou",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$main=new maincf_multi($this->hostname,$this->ou);
		$main->SET_BIGDATA("milter-greylist-config",base64_encode($this->BuildConfig()));
		$sock->getFrameWork("cmd.php?milter-greylist-reconfigure=yes");		
		return ;
	}	
	
	WriteLogs("Single instance: $this->hostname, $this->ou",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
	$sock->SaveConfigFile(base64_encode($this->BuildConfig()),"GreyListConf");	
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?milter-greylist-reconfigure=yes&hostname=$this->hostname&ou=".base64_encode($this->ou));
	
	
}


function ParseConfig(){
	WriteLogs("Parsing start",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
	if(trim($this->global_conf)==null){$this->global_conf=$this->BuildConfig();}
	
	$greylist=$this->regconf("greylist");
	if(preg_match('#([0-9]+)(h|m|d)#',$greylist,$re)){
		$this->main_array["greylist"]=$re[1];
		$this->main_array["greylist_TIME"]=$re[2];
	}
	
	
	
	$this->main_array["autowhite"]=$this->regconf("autowhite");
	if(preg_match('#([0-9]+)(h|m|d)#',$this->main_array["autowhite"],$re)){
		$this->main_array["autowhite"]=$re[1];
		$this->main_array["autowhite_TIME"]=$re[2];
		}	
	
	$this->main_array["timeout"]=$this->regconf("timeout");
		if(preg_match('#([0-9]+)(h|m|d)#',$this->main_array["timeout"],$re)){
		$this->main_array["timeout"]=$re[1];
		$this->main_array["timeout_TIME"]=$re[2];
		}	

	

	$this->Parse_Acls();
	if(!is_array($this->dnsrbl_array)){$this->defaultDnsrbl();}
	if(!is_array($this->acl)){$this->DefaultAcls();}
	
	$this->BuildDefault();
	WriteLogs("Parsing end",__CLASS__.'/'.__FUNCTION__,__FILE__);
}


function BuildDefault(){
	if($this->main_array["greylist_TIME"]==null){$this->main_array["greylist_TIME"]="h";}
	if($this->main_array["autowhite_TIME"]==null){$this->main_array["autowhite_TIME"]="d";}
	if($this->main_array["timeout_TIME"]==null){$this->main_array["timeout_TIME"]="h";}
	if($this->main_array["autowhite"]==null){$this->main_array["autowhite"]=3;}	
	if($this->main_array["timeout"]==null){$this->main_array["timeout"]=5;}
	if($this->main_array["greylist"]==null){$this->main_array["greylist"]=1;}	
	
	$sql="SELECT * FROM postfix_global_whitelist WHERE enabled=1 ORDER BY sender";	
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
		$pattern=trim($ligne["sender"]);
		if($pattern==null){continue;}
			if(preg_match("#(.*?)@(.+)#",$pattern)){
				$re[1]=str_replace("*","",$re[1]);
				if($re[1]==null){
					$this->acl[]="acl whitelist domain {$re[2]}  #$pattern";
				}else{
					$this->acl[]="acl whitelist from {$re[1]}@{$re[2]}  #$pattern";
			}
		}
	}

			include_once(dirname(__FILE__)."/class.user.inc");
			$sql="SELECT * FROM whitelist_uid_greylist";
			$results=$q->QUERY_SQL($sql,"artica_backup");
			while($ligne=@mysql_fetch_array($results,MYSQL_ASSOC)){
				$u=new user($ligne["uid"]);
				if(is_array($u->HASH_ALL_MAILS)){
					while (list ($num, $mail) = each ($u->HASH_ALL_MAILS) ){
						if(trim($mail)==null){continue;}
						if(!preg_match("#.+?@.+?$#",$mail)){continue;}
						$this->acl[]="acl whitelist rcpt $mail";
					}
				}
			}	
	
}

function Parse_Acls(){
	$t=explode("\n",$this->global_conf);
		while (list ($num, $ligne) = each ($t) ){
			if(preg_match("#^(racl|dacl|acl)#",$ligne)){$this->acl[]=$ligne;}
			if(preg_match("#^dnsrbl\s+#",$ligne)){$this->dnsrbl[]=$ligne;}
			}
		$this->__Parse_DNSBL();
	}



function regconf($value){
	
	$t=explode("\n",$this->global_conf);
		while (list ($num, $ligne) = each ($t) ){
			if(preg_match("#^$value\s+([0-9a-z]+)#",$ligne,$re)){
				WriteLogs("$value={$re[1]}",__CLASS__.'/'.__FUNCTION__,__FILE__);
				return $re[1];
			}
		}
	
}



	

	
function BuildConfig(){
			
			if(preg_match('#([0-9]+)#',$this->main_array["greylist"],$re)){$this->main_array["greylist"]=$re[1];}
			if(preg_match('#([0-9]+)#',$this->main_array["timeout"],$re)){$this->main_array["timeout"]=$re[1];}
			if(preg_match('#([0-9]+)#',$this->main_array["autowhite"],$re)){$this->main_array["autowhite"]=$re[1];}
			$this->BuildDefault();
			$this->__Build_DNSBL();
			
			if(!is_array($this->acl)){
				$this->DefaultAcls();
			}
			
			if(is_array($this->mynetworks)){
				while (list ($num, $net) = each ($this->mynetworks) ){
					if(trim($net)==null){continue;}
					
					if(strpos($net,"[::ffff:127.0.0.0]/104 [::1]/128")>0){
						if($GLOBALS["VERBOSE"]){echo "Network: remove [::ffff:127.0.0.0]/104 [::1]/128\n";}
						$net=str_replace("[::ffff:127.0.0.0]/104 [::1]/128","",$net);
					}					
					if($GLOBALS["VERBOSE"]){echo "Network: $net\n";}	
					$this->acl[]="acl whitelist addr $net # postfix local network";
				}
			}
			
			$this->multinets();
			
			
			if(is_array($this->acl)){
				while (list ($num, $ligne) = each ($this->acl) ){
					
					if(strpos($ligne,"[::ffff:127.0.0.0]/104 [::1]/128")>0){
						if($GLOBALS["VERBOSE"]){echo "BuildConfig: remove [::ffff:127.0.0.0]/104 [::1]/128\n";}
						continue;
					}	
					$array_acl[$ligne]=$ligne;
				}
				if(is_array($array_acl)){
					while (list ($num, $ligne) = each ($array_acl) ){
						$array_acl2[]=$ligne;
					}				
				}
				
			}
			
			if(!is_array($this->dnsrbl)){
				$this->defaultDnsrbl();
			}
			
			if(is_array($array_acl2)){
				while (list ($num, $ligne) = each ($array_acl2) ){
					if(trim($ligne)==null){continue;}
					if(preg_match("#whitelist domain\s+(.+)#",$ligne)){
					if(strpos($ligne,"@")>0){$array_acl2[$num]=str_replace("@","",$ligne);}
					}
				}	
			}

	
			$conf=$conf ."# Greylisting config file with a lot of options explained\n";
			$conf=$conf ."#\n";
			$conf=$conf ."# \$Id: greylist2.conf,v 1.1 2006/08/20 04:49:49 manu Exp $\n";
			$conf=$conf ."#\n";
			$conf=$conf ."\n";
			$conf=$conf ."# Uncomment this to enable debug output.\n";
			$conf=$conf ."# Note that options appearing before the \"verbose\" option in this\n";
			$conf=$conf ."# file will not be treated verbosely.\n";
			$conf=$conf ."# May be overridden by the \"-v\" command line argument.\n";
			$conf=$conf ."#verbose\n";
			$conf=$conf ."\n";
			$conf=$conf ."# If you work with multiple MXs, list them with\n";
			$conf=$conf ."# peer entries to enable greylist sync among the MXs.\n";
			$conf=$conf ."#peer 192.0.2.17\n";
			$conf=$conf ."#peer 192.0.2.18\n";
			$conf=$conf ."\n";
			$conf=$conf ."\n";
			$conf=$conf ."# You may wish to use a specific local address or port for\n";
			$conf=$conf ."# syncing between MXs. Of course one of your interfaces\n";
			$conf=$conf ."# must have the address assigned. An '*' for the address\n";
			$conf=$conf ."# means any address.\n";
			$conf=$conf ."#syncaddr *\n";
			$conf=$conf ."#syncaddr * port 7689\n";
			$conf=$conf ."#syncaddr 192.0.2.2\n";
			$conf=$conf ."#syncaddr 192.0.2.2 port 9785\n";
			$conf=$conf ."#syncaddr 2001:db8::1:c3b5:123\n";
			$conf=$conf ."#syncaddr 2001:db8::1:c3b5:123 port 1234\n";
			$conf=$conf ."\n";
			$conf=$conf ."# Specific IP-Address for outbound sync-connections\n";
			$conf=$conf ."# between MXs. If blank, system selects an ip.\n";
			$conf=$conf ."# syncsrcaddr 123.45.678.9\n";
			$conf=$conf ."\n";
			$conf=$conf ."# Greylisting your own MTA is a very bad idea: never\n";
			$conf=$conf ."# comment this line, except for testing purposes.\n";
			$conf=$conf ."acl whitelist addr 127.0.0.0/8\n";
			$conf=$conf ."\n";
			$conf=$conf ."# If you use IPv6, uncomment this.\n";
			$conf=$conf ."#acl whitelist addr ::1/128\n";
			$conf=$conf ."\n";
			$conf=$conf ."# You will want to avoid greylisting your own clients\n";
			$conf=$conf ."# as well, by filtering out your IP address blocks.\n";
			$conf=$conf ."# Here is an example if you use 192.0.2.0/16.\n";
			$conf=$conf ."#acl whitelist addr 192.0.2.0/16\n";
			$conf=$conf ."\n";
			$conf=$conf ."\n";
			$conf=$conf ."# You can avoid greylisting by filtering on the sender\n";
			$conf=$conf ."# envelope address, but this is not a good idea: it\n";
			$conf=$conf ."# can be trivially forged.\n";
			$conf=$conf ."#acl whitelist from friendly@example.com\n";
			$conf=$conf ."\n";
			$conf=$conf ."# Some of your users do not get any spam because\n";
			$conf=$conf ."# their addresses have never been collected by\n";
			$conf=$conf ."# spammers. They will want to avoid the extra delivery\n";
			$conf=$conf ."# delay caused by grey listing. You can filter on the\n";
			$conf=$conf ."# recipient envelope address to achieve that.\n";
			$conf=$conf ."#acl whitelist rcpt John.Doe@example.net\n";
			$conf=$conf ."\n";
			$conf=$conf ."# It is possible to use regular expressions in domain, from\n";
			$conf=$conf ."# and rcpt lines. The expression must be enclosed by\n";
			$conf=$conf ."# slashes (/). Note that no escaping is available to\n";
			$conf=$conf ."# provide slashes inside the regular expression.\n";
			$conf=$conf ."#acl whitelist rcpt /.*@example\.net/\n";
			$conf=$conf ."\n";
			$conf=$conf ."# This option tells milter-greylist when it should\n";
			$conf=$conf ."# add an X-Greylist header. Default is all, which\n";
			$conf=$conf ."# causes a header to always be added. Other possible\n";
			$conf=$conf ."# values are none, delays and nodelays\n";
			$conf=$conf ."report all\n";
			$conf=$conf ."\n";
			$conf=$conf ."# This option attempts to make milter-greylist more\n";
			$conf=$conf ."# friendly with sender callback systems. When the\n";
			$conf=$conf ."# message is from <>, it will be temporarily\n";
			$conf=$conf ."# rejected at the DATA stage instead of the RCPT\n";
			$conf=$conf ."# stage of the SMTP transaction. In the case of a\n";
			$conf=$conf ."# multi recipient DSN, whitelisted recipient will\n";
			$conf=$conf ."# not be honoured.\n";
			$conf=$conf ."#delayedreject\n";
			$conf=$conf ."\n";
			$conf=$conf ."# Uncomment if you want auto-whitelist to work for\n";
			$conf=$conf ."# the IP rather than for the (IP, sender, receiver)\n";
			$conf=$conf ."# tuple.\n";
			$conf=$conf ."#lazyaw\n";
			$conf=$conf ."\n";
			$conf=$conf ."# How often should we dump to the dumpfile (0: on each change, -1: never).\n";
			$conf=$conf ."#dumpfreq 10m\n";
			$conf=$conf ."\n";
			$conf=$conf ."# This option disables the conversion of the time specified in the\n";
			$conf=$conf ."# integer format to humanly readable format in the comment of each\n";
			$conf=$conf ."# line in the dumpfile.\n";
			$conf=$conf ."# Time needed in order to dump large dumpfiles (several milion \n";
			$conf=$conf ."# entries/few 100's of MB) can be significantly improved.\n";
			$conf=$conf ."dump_no_time_translation\n";
			$conf=$conf ."\n";
			$conf=$conf ."# This option causes greylist entries that expire to be logged via\n";
			$conf=$conf ."# syslog.  This allows you to collect the IP addresses and sender\n";
			$conf=$conf ."# names and use them for blacklisting, SPAM scoring, etc.\n";
			$conf=$conf ."logexpired\n";
			
			
			
			if(!preg_match('#([0-9]+)#',$this->main_array["timeout"])){$this->main_array["timeout"]=5;}
			if(strlen($this->main_array["timeout_TIME"])>1){$this->main_array["timeout_TIME"]='h';}
			if(preg_match('#(d|h|m)#',$this->main_array["timeout_TIME"],$pre)){$this->main_array["timeout_TIME"]=$pre[1];}
			if($GLOBALS["ROOT"]){echo "Starting......: milter-greylist timeout:{$this->main_array["timeout"]}{$this->main_array["timeout_TIME"]}\n";}
			if($GLOBALS["ROOT"]){echo "Starting......: milter-greylist greylist:{$this->main_array["greylist"]}{$this->main_array["greylist_TIME"]}\n";}
			$conf=$conf ."# How long will the greylist database retain tuples.\n";
			$conf=$conf ."timeout {$this->main_array["timeout"]}{$this->main_array["timeout_TIME"]}\n";
			$conf=$conf ."\n";
			$conf=$conf ."# Do not use \${greylist} macros from sendmail's access DB.\n";
			$conf=$conf ."#noaccessdb\n";
			$conf=$conf ."\n";
			$conf=$conf ."# Use extended regular expressions instead of basic\n";
			$conf=$conf ."# regular expressions.\n";
			$conf=$conf ."#extendedregex\n";
			$conf=$conf ."\n";
			$conf=$conf ."#\n";
			$conf=$conf ."# All of the following options have command-line equivalents.\n";
			$conf=$conf ."# See greylist.conf(5) for the exact equivalences.\n";
			$conf=$conf ."#\n";
			$conf=$conf ."\n";
			$conf=$conf ."# How long a client has to wait before we accept\n";
			$conf=$conf ."# the messages it retries to send. Here, 1 hour.\n";
			$conf=$conf ."# May be overridden by the \"-w greylist_delay\" command line argument.\n";
			$conf=$conf ."greylist {$this->main_array["greylist"]}{$this->main_array["greylist_TIME"]}\n";
			$conf=$conf ."\n";
			$conf=$conf ."# How long does auto-whitelisting last (set it to 0\n";
			$conf=$conf ."# to disable auto-whitelisting). Here, 3 days.\n";
			$conf=$conf ."# May be overridden by the \"-a autowhite_delay\" command line argument.\n";
			$conf=$conf ."autowhite {$this->main_array["autowhite"]}{$this->main_array["autowhite_TIME"]}\n";
			$conf=$conf ."\n";
			$conf=$conf ."# Specify the netmask to be used when checking IPv4 addresses\n";
			$conf=$conf ."# in the greylist.\n";
			$conf=$conf ."# May be overridden by the \"-L cidrmask\" command line argument.\n";
			$conf=$conf ."#subnetmatch /24\n";
			$conf=$conf ."\n";
			$conf=$conf ."# Specify the netmask to be used when checking IPv6 addresses\n";
			$conf=$conf ."# in the greylist.\n";
			$conf=$conf ."# May be overridden by the \"-M prefixlen\" command line argument.\n";
			$conf=$conf ."#subnetmatch6 /64\n";
			$conf=$conf ."\n";
			$conf=$conf ."# Normally, clients that succeed SMTP AUTH are not\n";
			$conf=$conf ."# greylisted. Uncomment this if you want to\n";
			$conf=$conf ."# greylist them regardless of SMTP AUTH.\n";
			$conf=$conf ."# May be overridden by the \"-A\" command line argument.\n";
			$conf=$conf ."#noauth\n";
			$conf=$conf ."\n";
			$conf=$conf ."# If milter-greylist was built with SPF support, then\n";
			$conf=$conf ."# SPF-compliant senders are not greylisted. Uncomment\n";
			$conf=$conf ."# this to greylist them regardless of SPF compliance.\n";
			$conf=$conf ."# May be overridden by the \"-S\" command line argument.\n";
			$conf=$conf ."#nospf\n";
			$conf=$conf ."\n";
			$conf=$conf ."# If milter-greylist was built with DRAC support, \n";
			$conf=$conf ."# then DRAC DB location can be specified here\n";
			$conf=$conf ."#drac db \"/usr/local/etc/drac.db\"\n";
			$conf=$conf ."\n";
			$conf=$conf ."# Uncomment this to disable DRAC\n";
			$conf=$conf ."#nodrac\n";
			$conf=$conf ."\n";
			$conf=$conf ."# Uncomment if you want milter-greylist to remain\n";
			$conf=$conf ."# in the foreground (no daemon).\n";
			$conf=$conf ."# May be overridden by the \"-D\" command line argument.\n";
			$conf=$conf ."#nodetach\n";
			$conf=$conf ."\n";
			$conf=$conf ."# Uncomment this if you do not want milter-greylist\n";
			$conf=$conf ."# to tell its clients how long they are greylisted.\n";
			$conf=$conf ."# May be overridden by the \"-q\" command line argument.\n";
			$conf=$conf ."#quiet\n";
			$conf=$conf ."\n";
			$conf=$conf ."# You can specify a file where milter-greylist will\n";
			$conf=$conf ."# store its PID.\n";
			$conf=$conf ."# May be overridden by the \"-P pidfile\" command line argument.\n";
			$conf=$conf ."pidfile \"/var/run/milter-greylist.pid\"\n";
			$conf=$conf ."\n";
			$conf=$conf ."# You can specify the socket file used to communicate\n";
			$conf=$conf ."# with sendmail.\n";
			$conf=$conf ."# May be overridden by the \"-p socket\" command line argument.\n";
			$conf=$conf ."socket \"/var/run/milter-greylist/milter-greylist.sock\"\n";
			$conf=$conf ."\n";
			$conf=$conf ."# The dumpfile location.\n";
			$conf=$conf ."# May be overridden by the \"-d dumpfile\" command line argument.\n";
			$conf=$conf ."#dumpfile \"/opt/artica/var/milter-greylist/greylist.db\"\n";
			$conf=$conf ."\n";
			$conf=$conf ."# The user the milter should run as.\n";
			$conf=$conf ."# May be overridden by the \"-u username\" command line argument.\n";
			$conf=$conf ."#user \"greylist\"\n";
			
			/*$user=new usersMenus();
			if(strlen($user->GeoIPDat_Path)>2){
			$conf=$conf ."geoipdb \"$user->GeoIPDat_Path\"\n";	
			}
			
			$conf=$conf ."\n";
			$conf=$conf ."\n";
			$conf=$conf ."\n";
			$conf=$conf ."#DNSRBL Classes\n";
			if(is_array($this->dnsrbl)){
				$conf=$conf . implode("\n",$this->dnsrbl);
			}*/
			$conf=$conf ."\n";
			$conf=$conf ."\n";
			$conf=$conf ."#ACLS Rules\n\n";
			$conf=$conf . implode("\n",$array_acl2);
			$conf=$conf ."\n";
	      	
		    
			return $conf;
}

private function multinets(){
	include_once dirname(__FILE__)."/class.mysql.inc";
	$sql="SELECT ipaddr FROM nics_virtuals";
	$q=new mysql();
	$results=$q->QUERY_SQL($sql,"artica_backup");
	if(!$q->ok){
		WriteLogs("$q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__);
		return;
	}
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if(strpos($ligne["ipaddr"],"[::ffff:127.0.0.0]/104 [::1]/128")>0){
			$ligne["ipaddr"]=str_replace("[::ffff:127.0.0.0]/104 [::1]/128","",$ligne["ipaddr"]);
		}	
		$this->acl[]="acl whitelist addr {$ligne["ipaddr"]} # postfix local network";
	}
	
	
		$q=new mysql();
		$sql="SELECT * FROM postfix_whitelist_con";
		$results=$q->QUERY_SQL($sql,"artica_backup");
		if(!$q->ok){echo "$q->mysql_error\n";}
	
		while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
			if(preg_match("#[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+#",$ligne["ipaddr"])){
				$this->acl[]="acl whitelist addr {$ligne["ipaddr"]}";
			}
		}	
	
	$ldap=new clladp();
	$nets=$ldap->load_mynetworks();
	if(!is_array($nets)){
		$this->acl[]="acl whitelist addr 127.0.0.0/8";
	}
	

	while (list ($num, $network) = each ($nets) ){$cleaned[$network]=$network;}
	unset($nets);
	while (list ($network, $network2) = each ($cleaned) ){$nets[]=$network;}
	while (list ($a, $b) = each ($nets) ){
		$this->acl[]="acl whitelist addr $b";
	}	
	
}


function ParseAcl($line){
	if(preg_match('#[a-z]+\s+(whitelist|blacklist|greylist)\s+(addr|urlcheck|body|domain|from|rcpt|dnsrbl)\s+(.+)\s+\#(.+)#',$line,$re)){
		return $re;
	}
	
}

function __Parse_DNSBL(){
	if(!is_array($this->dnsrbl)){$this->defaultDnsrbl();}
	$table=$this->dnsrbl;
	WriteLogs("Parsing DNSBL " . count($this->dnsrbl) . " rows",__CLASS__.'/'.__FUNCTION__,__FILE__);
	$pattern='#dnsrbl\s+"(.+?)"\s+(.+?)\s+([0-9\.]+)\s+\#(.+)#';
	reset($this->dnsrbl);
	if(!is_array($table)){return null;}
		while (list ($num, $val) = each ($table) ){
		if(preg_match($pattern,$val,$re)){
			$this->dnsrbl_array[$re[1]]=array($re[2],$re[3],$re[4]);
			$this->dnsrbl_class[$re[1]]=$re[1];
		}else{
			WriteLogs("Parsing DNSBL doesn't match $val for $pattern",__CLASS__.'/'.__FUNCTION__,__FILE__);
		}
		
	}
}

function __Build_DNSBL(){
	if(is_array($this->dnsrbl_array)){reset($this->dnsrbl_array);}
	unset($this->dnsrbl);
	if(is_array($this->dnsrbl_array)){
		while (list ($num, $val) = each ($this->dnsrbl_array) ){
			$class=$num;
			$this->dnsrbl[]="dnsrbl \"$class\" {$val[0]} {$val[1]} #{$val[2]}";
			}
		}
}

public function GetWhiteListed(){
	$array=$this->acl;
	if(!is_array($array)){return array();}
	while (list ($num, $val) = each ($array) ){
		if(preg_match("#acl whitelist addr\s+(.+?)\s+#",$val,$re)){
			$ar[$re[1]]=true;
		}
		
	}
	return $ar;
}


function DefaultAcls(){
			$this->acl[]="acl whitelist addr 127.0.0.1/8 # local network";
			$this->acl[]="acl whitelist addr 10.0.0.0/8 # local network";
			$this->acl[]="acl whitelist addr 192.168.0.0/24 # local network";  
			$this->acl[]="acl whitelist addr 192.0.1.0/24 # local network";
			$this->acl[]="acl whitelist addr 12.5.136.141/32    # Southwest Airlines (unique sender)";
			$this->acl[]="acl whitelist addr 12.5.136.142/32    # Southwest Airlines";
			$this->acl[]="acl whitelist addr 12.5.136.143/32    # Southwest Airlines";
			$this->acl[]="acl whitelist addr 12.5.136.144/32    # Southwest Airlines";
			$this->acl[]="acl whitelist addr 12.107.209.244/32  # kernel.org (unique sender)";
			$this->acl[]="acl whitelist addr 12.107.209.250/32  # sourceware.org (unique sender)";
			$this->acl[]="acl whitelist addr 63.82.37.110/32    # SLmail";
			$this->acl[]="acl whitelist addr 64.7.153.18/32     # sentex.ca (common pool)";
			$this->acl[]="acl whitelist addr 64.12.136.0/24     # AOL (common pool)";
			$this->acl[]="acl whitelist addr 64.12.137.0/24     # AOL";
			$this->acl[]="acl whitelist addr 64.12.138.0/24     # AOL";
			$this->acl[]="acl whitelist addr 64.124.204.39      # moveon.org (unique sender)";
			$this->acl[]="acl whitelist addr 64.125.132.254/32  # collab.net (unique sender)";
			$this->acl[]="acl whitelist addr 66.94.237.16/28    # Yahoo Groups servers (common pool)";
			$this->acl[]="acl whitelist addr 66.94.237.32/28    # Yahoo Groups servers (common pool)";
			$this->acl[]="acl whitelist addr 66.94.237.48/30    # Yahoo Groups servers (common pool)";
			$this->acl[]="acl whitelist addr 66.100.210.82/32   # Groupwise?";
			$this->acl[]="acl whitelist addr 66.135.192.0/19    # Ebay";
			$this->acl[]="acl whitelist addr 66.162.216.166/32  # Groupwise?";
			$this->acl[]="acl whitelist addr 66.206.22.82/32    # Plexor";
			$this->acl[]="acl whitelist addr 66.206.22.83/32    # Plexor";
			$this->acl[]="acl whitelist addr 66.206.22.84/32    # Plexor";
			$this->acl[]="acl whitelist addr 66.206.22.85/32    # Plexor";
			$this->acl[]="acl whitelist addr 66.218.66.0/23     # Yahoo Groups servers (common pool)";
			$this->acl[]="acl whitelist addr 66.218.67.0/23     # Yahoo Groups servers (common pool)";
			$this->acl[]="acl whitelist addr 66.218.68.0/23     # Yahoo Groups servers (common pool)";
			$this->acl[]="acl whitelist addr 66.27.51.218/32    # ljbtc.com (Groupwise)";
			$this->acl[]="acl whitelist addr 152.163.225.0/24   # AOL";
			$this->acl[]="acl whitelist addr 194.245.101.88/32  # Joker.com";
			$this->acl[]="acl whitelist addr 195.235.39.19/32   # Tid InfoMail Exchanger v2.20";
			$this->acl[]="acl whitelist addr 195.46.220.208/32  # mgn.net";
			$this->acl[]="acl whitelist addr 195.46.220.209/32  # mgn.net";
			$this->acl[]="acl whitelist addr 195.46.220.210/32  # mgn.net";
			$this->acl[]="acl whitelist addr 195.46.220.211/32  # mgn.net";
			$this->acl[]="acl whitelist addr 195.46.220.221/32  # mgn.net";
			$this->acl[]="acl whitelist addr 195.46.220.222/32  # mgn.net";
			$this->acl[]="acl whitelist addr 195.238.2.0/24     # skynet.be (wierd retry pattern)";
			$this->acl[]="acl whitelist addr 195.238.3.0/24     # skynet.be";
			$this->acl[]="acl whitelist addr 204.107.120.10/32  # Ameritrade (no retry)";
			$this->acl[]="acl whitelist addr 205.188.0.0/16     # AOL";
			$this->acl[]="acl whitelist addr 205.206.231.0/24   # SecurityFocus.com (unique sender)";
			$this->acl[]="acl whitelist addr 207.115.63.0/24    # Prodigy - retries continually";
			$this->acl[]="acl whitelist addr 207.171.168.0/24   # Amazon.com";
			$this->acl[]="acl whitelist addr 207.171.180.0/24   # Amazon.com";
			$this->acl[]="acl whitelist addr 207.171.187.0/24   # Amazon.com";
			$this->acl[]="acl whitelist addr 207.171.188.0/24   # Amazon.com";
			$this->acl[]="acl whitelist addr 207.171.190.0/24   # Amazon.com";
			$this->acl[]="acl whitelist addr 211.29.132.0/24    # optusnet.com.au (wierd retry pattern)";
			$this->acl[]="acl whitelist addr 213.136.52.31/32   # Mysql.com (unique sender)";
			$this->acl[]="acl whitelist addr 216.33.244.0/24    # Ebay";
			$this->acl[]="acl whitelist addr 217.158.50.178/32  # AXKit mailing list (unique sender)";	
			


}

function defaultDnsrbl(){
			$this->dnsrbl[]="dnsrbl \"NJABL-RELAY\" combined.njabl.org 127.0.0.2 #open relays";
			$this->dnsrbl[]="dnsrbl \"NJABL-DYNABLOCK\" combined.njabl.org 127.0.0.3 #dynamic IPs";
			$this->dnsrbl[]="dnsrbl \"NJABL-SPAMSRC\" combined.njabl.org 127.0.0.4 #source of spam";
			$this->dnsrbl[]="dnsrbl \"NJABL-RELAY-MULTI\" combined.njabl.org 127.0.0.5 #relay which accepts feeds from another server which is open #note - 127.0.0.6 exists, but it's unwise to use unless you're being aggressive";
			$this->dnsrbl[]="dnsrbl \"NJABL-CGI\" combined.njabl.org 127.0.0.8 #open formmail and similar CGI issues";
			$this->dnsrbl[]="dnsrbl \"NJABL-PROXY\" combined.njabl.org 127.0.0.9 #open proxies";
			$this->dnsrbl[]="dnsrbl \"SPAMHAUS SBL\" sbl-xbl.spamhaus.org 127.0.0.2 #spamhaus";
			$this->dnsrbl[]="dnsrbl \"SPAMHAUS XBL CBL\" sbl-xbl.spamhaus.org 127.0.0.4 #spamhaus";
			$this->dnsrbl[]="dnsrbl \"SPAMHAUS XBL NJABL\" sbl-xbl.spamhaus.org 127.0.0.5 #spamhaus";
			$this->dnsrbl[]="dnsrbl \"SPAMHAUS XBL OTHER\" sbl-xbl.spamhaus.org 127.0.0.6 ##note: code 6 seems unused now, but I'm including for completeness";
			$this->dnsrbl[]="dnsrbl \"SORBS-HTTP\" dnsbl.sorbs.net 127.0.0.2 #open http proxy";
			$this->dnsrbl[]="dnsrbl \"SORBS-SOCKS\" dnsbl.sorbs.net 127.0.0.3 #open socks proxy";
			$this->dnsrbl[]="dnsrbl \"SORBS-BLOCK\" dnsbl.sorbs.net 127.0.0.8 #sites which have requested SORBs not test their servers";
			$this->dnsrbl[]="dnsrbl \"SORBS-MISC\" dnsbl.sorbs.net 127.0.0.4 #open misc other proxy";
			$this->dnsrbl[]="dnsrbl \"SORBS-SMTP\" dnsbl.sorbs.net 127.0.0.5 #open relays";
			$this->dnsrbl[]="dnsrbl \"SORBS-SPAM\" dnsbl.sorbs.net 127.0.0.6 #spam source";
			$this->dnsrbl[]="dnsrbl \"SORBS-WEB\" dnsbl.sorbs.net 127.0.0.7 #web";
			$this->dnsrbl[]="dnsrbl \"SORBS-ZOMBIE\" dnsbl.sorbs.net 127.0.0.9 #hijacked network";
			$this->dnsrbl[]="dnsrbl \"SORBS-DUL\" dnsbl.sorbs.net 127.0.0.10 #dialup";
			$this->dnsrbl[]="dnsrbl \"SORBS DUN\" dnsbl.sorbs.net 127.0.0.10 # config example";
			$this->dnsrbl[]="dnsrbl	\"NJABL_2\" dnsbl.njabl.org 127.0.0.2 # config example";
			$this->dnsrbl[]="dnsrbl	\"NJABL_3\" dnsbl.njabl.org 127.0.0.3 # config example";
			$this->dnsrbl[]="dnsrbl	\"NJABL_4\" dnsbl.njabl.org 127.0.0.4 # config example";
			$this->dnsrbl[]="dnsrbl	\"NJABL_5\" dnsbl.njabl.org 127.0.0.9 # config example";
	
}
	
	
}
?>