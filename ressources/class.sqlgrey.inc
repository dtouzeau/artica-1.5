<?php

class sqlgrey{
	var $hostname;
	var $global_conf;
	var $SqlGreyEnabled;
	var $main_array;
	var $SqlGreyWLHost;
	var $SqlGreyWLIP;
	var $dn;
	
//-----------------------------------------------------------------------------------------------------------------------------------------	
	function sqlgrey($hostname=null,$noload=false){
		$users=new usersMenus();
		if($hostname==null){$this->hostname=$users->hostname;}else{$this->hostname=$hostname;}
		
		if(!$noload){$this->LoadConfig();}
		
		
	}

//-----------------------------------------------------------------------------------------------------------------------------------------	
	function LoadConfig(){
			$sock=new sockets();
			$this->global_conf=$sock->GET_INFO("sqlGreyConf");
			
			if(trim($this->global_conf)==null){
				writelogs("warning, no entries found, build default instead",__CLASS__.'/'.__FUNCTION__,__FILE__);
				$sock->SaveClusterConfigFile($this->BuildConfig(),"sqlGreyConf");
				$this->global_conf=$this->BuildConfig();
			}
			$this->SqlGreyEnabled=$sock->GET_INFO("SqlGreyEnabled");
			$this->SqlGreyWLHost=unserialize($sock->GET_INFO("SqlGreyWLHost"));
			$this->SqlGreyWLIP=unserialize($sock->GET_INFO("SqlGreyWLIP"));
			$this->ParseConfig();
		}
		
//-----------------------------------------------------------------------------------------------------------------------------------------
	
//-----------------------------------------------------------------------------------------------------------------------------------------
		function ParseConfig(){
			
			$table=explode("\n",$this->global_conf);
			while (list ($num, $val) = each ($table) ){
				$val=trim($val);
				if($val<>null){
				if(preg_match('#^([a-zA-Z_]+)[\s=]+(.+)#i',$val,$re)){
					$this->main_array[$re[1]]=$re[2];
				}}
				
				
			}
			
			$this->BuildDefault();
			
		}
		
//-----------------------------------------------------------------------------------------------------------------------------------------	

	function BuildDefault(){
				if($this->main_array["reconnect_delay"]==null){$this->main_array["reconnect_delay"]="15";}
				if($this->main_array["max_connect_age"]==null){$this->main_array["max_connect_age"]="24";}
				if($this->main_array["connect_src_throttle"]==null){$this->main_array["connect_src_throttle"]="5";}
				if($this->main_array["awl_age"]==null){$this->main_array["awl_age"]="60";}
				if($this->main_array["group_domain_level"]==null){$this->main_array["group_domain_level"]="10";}
				if($this->main_array["greymethod"]==null){$this->main_array["greymethod"]="smart";}
				if($this->main_array["discrimination"]==null){$this->main_array["discrimination"]="off";}
				if($this->main_array["discrimination_add_rulenr"]==null){$this->main_array["discrimination_add_rulenr"]="off";}
				if($this->main_array["reject_first_attempt"]==null){$this->main_array["reject_first_attempt"]="delay";}
				if($this->main_array["reject_early_reconnect"]==null){$this->main_array["reject_early_reconnect"]="delay";}
				if($this->main_array["whitelists_host"]==null){$this->main_array["whitelists_host"]="sqlgrey.bouton.name";}
			
				}
				
				
	function Add_whitelist_fqdn($pattern){
		return;
		$ldap=new clladp();
		$upd["SqlGreyWLHost"]=$pattern;
		$ldap->Ldap_add_mod($this->dn,$upd);
		}
		
	function Add_whitelist_ip($pattern){
		return;
		$ldap=new clladp();
		$upd["SqlGreyWLIP"]=$pattern;
		$ldap->Ldap_add_mod($this->dn,$upd);
	}
		
	function Del_whitelist_fqdn($num){
		return;
		$ldap=new clladp();
		$upd["SqlGreyWLHost"]=$this->SqlGreyWLHost[$num];
		$ldap->Ldap_del_mod($this->dn,$upd);
	}
function Del_whitelist_ip($num){
		return;
		$ldap=new clladp();
		$upd["SqlGreyWLIP"]=$this->SqlGreyWLIP[$num];
		$ldap->Ldap_del_mod($this->dn,$upd);
	}
	
	
	
	function BuildConfig(){
		
	$conf=$conf . "#########################\n";
	$conf=$conf . "## SQLgrey config file ##\n";
	$conf=$conf . "#########################\n";
	$conf=$conf . "\n";
	$conf=$conf . "# Notes: \n";
	$conf=$conf . "# - Unless specified otherwise commented settings are SQLgrey's defaults\n";
	$conf=$conf . "# - SQLgrey uses a specific config file when called with -f <conf_file>\n";
	$conf=$conf . "\n";
	$conf=$conf . "## Configuration files\n";
	$conf=$conf . "conf_dir = /opt/artica/etc/sqlgrey\n";
	$conf=$conf . "\n";
	$conf=$conf . "## Log level\n";
	$conf=$conf . "# Uncomment to change the log level (default is normal: 2)\n";
	$conf=$conf . "# nothing: O, errors only: 0, warnings: 1, normal: 2, verbose: 3, debug: 4\n";
	$conf=$conf . "loglevel = 2\n";
	$conf=$conf . "\n";
	$conf=$conf . "## log categories can be fine-tuned,\n";
	$conf=$conf . "# here are the log messages sorted by types and levels,\n";
	$conf=$conf . "# (anything over the loglevel is discarded):\n";
	$conf=$conf . "#\n";
	$conf=$conf . "# grey     : (0) internal errors,\n";
	$conf=$conf . "#	     (2) initial connections, early reconnections,\n";
	$conf=$conf . "#	         awl matches, successful reconnections, AWL additions,\n";
	$conf=$conf . "#	     (3) smart decision process debug,\n";
	$conf=$conf . "# whitelist: (2) whitelisted connections,\n";
	$conf=$conf . "#	     (3) actual whitelist hit,\n";
	$conf=$conf . "#	     (4) whitelists reloads,\n";
	$conf=$conf . "# optin:     (3) optin/optout global result\n";
	$conf=$conf . "#	     (4) optin/optout SQL query results\n";
	$conf=$conf . "# spam     : (2) attempts never retried,\n";
	$conf=$conf . "# mail     : (1) error sending mails,\n";
	$conf=$conf . "#	     (4) rate-limiter debug,\n";
	$conf=$conf . "# dbaccess : (0) DB errors,\n";
	$conf=$conf . "#            (1) DB upgrade,\n";
	$conf=$conf . "#	     (2) DB upgrade details,\n";
	$conf=$conf . "# martians : (2) invalid e-mail addresses,\n";
	$conf=$conf . "# perf     : (2) cleanup time,\n";
	$conf=$conf . "# system   : (0) error forking,\n";
	$conf=$conf . "#	     (3) forked children PIDs, children exits,\n";
	$conf=$conf . "# conf     : (0) errors in config files, missing required file,\n";
	$conf=$conf . "# 	     (1) warnings in config files,\n";
	$conf=$conf . "#	         missing optional configuration files,\n";
	$conf=$conf . "#	     (2) reloading configuration files,\n";
	$conf=$conf . "# other    : (4) Startup cleanup\n";
	$conf=$conf . "# you can set a level to O (capital o) to disable logs completely,\n";
	$conf=$conf . "# but be aware that then SQLgrey can come back to haunt you...\n";
	$conf=$conf . "\n";
	$conf=$conf . "# Provide a coma-separated \"logtype:loglevel\" string\n";
	$conf=$conf . "# For example if you set the loglevel to 3 (verbose) but want SQLgrey to be:\n";
	$conf=$conf . "# . quiet for whitelists\n";
	$conf=$conf . "# . normal for greylisting\n";
	$conf=$conf . "# uncomment the following line.\n";
	$conf=$conf . "# log_override = whitelist:1,grey:2\n";
	$conf=$conf . "# By default, log_override is empty\n";
	$conf=$conf . "\n";
	$conf=$conf . "## Log identification\n";
	$conf=$conf . "# by default this is the process name. If you define the following variable\n";
	$conf=$conf . "# SQLgrey will use whatever you set it to\n";
	$conf=$conf . "# log_ident =\n";
	$conf=$conf . "\n";
	$conf=$conf . "## username and groupname the daemon runs as\n";
	$conf=$conf . "user = sqlgrey\n";
	$conf=$conf . "group = sqlgrey\n";
	$conf=$conf . "\n";
	$conf=$conf . "## Socket\n";
	$conf=$conf . "# On which socket do SQLgrey wait for queries\n";
	$conf=$conf . "# use the following if you need to bind on a public IP address\n";
	$conf=$conf . "inet =127.0.0.1:23675\n";
	$conf=$conf . "# default :\n";
	$conf=$conf . "# inet = 2501    # bind to localhost:2501\n";
	$conf=$conf . "\n";
	$conf=$conf . "## PID\n";
	$conf=$conf . "# where to store the process PID\n";
	$conf=$conf . "pidfile = /opt/artica/var/run/sqlgrey.pid\n";
	$conf=$conf . "\n";
	$conf=$conf . "## Config directory\n";
	$conf=$conf . "# where to look for other configuration files (whitelists)\n";
	$conf=$conf . "# confdir = /etc/sqlgrey\n";
	$conf=$conf . "\n";
	$conf=$conf . "## Greylisting delays\n";
	$conf=$conf . "# If you want to be really strict (RFC-wise) use these\n";
	$conf=$conf . "# This is *not* recommended, you'll have false positives\n";
	$conf=$conf . "# reconnect_delay = 15    # don't allow a reconnection before 15 minutes\n";
	$conf=$conf . "# max_connect_age = 2     # don't allow a reconnection after 2 hours\n";
	$conf=$conf . "# default: (based on real-life experience)\n";
	$conf=$conf . "reconnect_delay = {$this->main_array["reconnect_delay"]}\n";
	$conf=$conf . "max_connect_age = {$this->main_array["max_connect_age"]}\n";
	$conf=$conf . "\n";
	$conf=$conf . "## Throttling too many new entries from new host\n";
	$conf=$conf . "# Setting this optional parameter will refuse an excessive number of\n";
	$conf=$conf . "# new entries in the connect table from the same host, in the following\n";
	$conf=$conf . "# manner:\n";
	$conf=$conf . "# - If there are already \"connect_src_throttle\" entries in the connect\n";
	$conf=$conf . "#   table from the same host (e-mails which have not been retried yet)\n";
	$conf=$conf . "# - And there is NO entry for this host in domain_awl\n";
	$conf=$conf . "# - And there are LESS than \"connect_src_throttle\" entries in the\n";
	$conf=$conf . "#   from_awl table for this host\n";
	$conf=$conf . "# THEN further incoming connections from this host will be (temporarily)\n";
	$conf=$conf . "# refused without new entries being created in the connect table (until\n";
	$conf=$conf . "# some already waiting entries have been successfully retried).\n";
	$conf=$conf . "# This feature may prevent the connect table from growing too big and\n";
	$conf=$conf . "# being polluted by spambots, viruses, zombie machines and the like.\n";
	$conf=$conf . "# If set to \"0\" (default), this feature won't be used.\n";
	$conf=$conf . "connect_src_throttle = {$this->main_array["connect_src_throttle"]}\n";
	$conf=$conf . "\n";
	$conf=$conf . "\n";
	$conf=$conf . "## Auto whitelists settings\n";
	$conf=$conf . "# default is tailored for small sites\n";
	$conf=$conf . "awl_age = {$this->main_array["awl_age"]}\n";
	$conf=$conf . "group_domain_level = {$this->main_array["group_domain_level"]}\n";
	$conf=$conf . "\n";
	$conf=$conf . "# For bigger sites you may want\n";
	$conf=$conf . "# a smaller awl_age and a bigger group_domain_level\n";
	$conf=$conf . "# awl_age = 32            # (monthly newsletter will get through)\n";
	$conf=$conf . "# group_domain_level = 10 # wait for 10 validated adresses to add a whole\n";
	$conf=$conf . "# domain in AWL\n";
	$conf=$conf . "\n";
	$conf=$conf . "## Database settings\n";
	$conf=$conf . "# instead of Pg below use \"mysql\" for MySQL, \"SQLite\" for SQLite\n";
	$conf=$conf . "# any DBD driver is allowed, but only the previous 3 have been tested\n";
	$conf=$conf . "db_type = SQLite\n";
	$conf=$conf . "db_name = /opt/artica/etc/sqlgrey/sqlgrey.db\n";
	$conf=$conf . "# Note: the following are not used with SQLite\n";
	$conf=$conf . "# db_host = localhost\n";
	$conf=$conf . "# db_port = default\n";
	$conf=$conf . "# db_user = sqlgrey\n";
	$conf=$conf . "# db_pass = spaces_are_not_supported\n";
	$conf=$conf . "# db_prepare_cache = 0 # use prepared statements cache\n";
	$conf=$conf . "                       # BEWARE: memory leaks have been reported\n";
	$conf=$conf . "		       # when it is active\n";
	$conf=$conf . "# db_cleandelay = 1800 # in seconds, how much time between database cleanups\n";
	$conf=$conf . "# clean_method = sync # sync : cleanup is done in the main process,\n";
	$conf=$conf . "                      #        delaying other operations\n";
	$conf=$conf . "                      # async: cleanup is done in a forked process,\n";
	$conf=$conf . "                      #        it won't delay mail processing\n";
	$conf=$conf . "                      #        BEWARE: lockups have been reported\n";
	$conf=$conf . "                      #        and are still investigated\n";
	$conf=$conf . "\n";
	$conf=$conf . "## Database clustering (for advanced setups)\n";
	$conf=$conf . "#\n";
	$conf=$conf . "# See README.DBCLUSTER\n";
	$conf=$conf . "#\n";
	$conf=$conf . "# Writes will be done to db_host specified above, and reads will be done\n";
	$conf=$conf . "# from read_hosts specified below\n";
	$conf=$conf . "#\n";
	$conf=$conf . "# Valid options: on/off. If set to 'on', clustering will be enabled.\n";
	$conf=$conf . "# db_cluster = on\n";
	$conf=$conf . "\n";
	$conf=$conf . "#\n";
	$conf=$conf . "# Comma seperated list of read-only db-servers.\n";
	$conf=$conf . "# read_hosts= slave-db-1.test.com, slave-db-2.test.com ,slave-db-3.test.com\n";
	$conf=$conf . "# read_hosts=localhost\n";
	$conf=$conf . "\n";
	$conf=$conf . "\n";
	$conf=$conf . "## X-Greylist header added?\n";
	$conf=$conf . "# This adds delay, whitelist and autowhitelist information in the headers\n";
	$conf=$conf . "prepend = 1\n";
	$conf=$conf . "\n";
	$conf=$conf . "## Greylisting method:\n";
	$conf=$conf . "# - full   : greylist by IP address\n";
	$conf=$conf . "# - classc : greylist by class C network. eg:\n";
	$conf=$conf . "#            2.3.4.6 connection accepted if 2.3.4.145 did connect earlier\n";
	$conf=$conf . "# - smart  : greylist by class C network unless there is no reverse lookup\n";
	$conf=$conf . "#            or it looks like a home-user address\n";
	$conf=$conf . "# Default is smart\n";
	$conf=$conf . "greymethod = {$this->main_array["greymethod"]}\n";
	$conf=$conf . "\n";
	$conf=$conf . "## Optin/Optout (see README.OPTINOUT for details)\n";
	$conf=$conf . "# - none   : everyone is greylisted (default)\n";
	$conf=$conf . "# - optin  : one must optin to have its (incoming) messages being greylisted\n";
	$conf=$conf . "# - optout : one must optout to not have its messages being greylisted\n";
	$conf=$conf . "# optmethod = none\n";
	$conf=$conf . "\n";
	$conf=$conf . "## Discriminating Greylisting (see README.DISCRIMINATION)\n";
	$conf=$conf . "# - off : normal greylisting\n";
	$conf=$conf . "# - on  : Only apply greylisting to senders that do not pass the regexp test.\n";
	$conf=$conf . "#\n";
	$conf=$conf . "discrimination = {$this->main_array["discrimination"]}\n";
	$conf=$conf . "\n";
	$conf=$conf . "# Display the number of the regular expression causing the greylisting\n";
	$conf=$conf . "# at the end of the reject message.\n";
	$conf=$conf . "# Values: on/off\n";
	$conf=$conf . "discrimination_add_rulenr = {$this->main_array["discrimination_add_rulenr"]}\n";
	$conf=$conf . "\n";
	$conf=$conf . "## SQLgrey return value.\n";
	$conf=$conf . "# SQLgrey can tell Postfix to:\n";
	$conf=$conf . "# - immediately reject a message with a temporary reject code\n";
	$conf=$conf . "# - only do so if following rules would allow the message to pass\n";
	$conf=$conf . "# The first choice will prevent Postfix from spending time evaluating\n";
	$conf=$conf . "# potentially expensive rules.\n";
	$conf=$conf . "# In some cases you may want following rules to be aware of the connection\n";
	$conf=$conf . "# this.\n";
	$conf=$conf . "#\n";
	$conf=$conf . "# We can specify a different rejection strategy for the first connection\n";
	$conf=$conf . "# attempt, and for early reconnections. 'immed' chooses immediate rejection\n";
	$conf=$conf . "# 'delay' choose delayed rejection\n";
	$conf=$conf . "#\n";
	$conf=$conf . "# By default we use delay on first attempt\n";
	$conf=$conf . "reject_first_attempt = {$this->main_array["reject_first_attempt"]}\n";
	$conf=$conf . "# Default for early reconnection is the value affected to reject_first_attempt\n";
	$conf=$conf . "# reject_early_reconnect = {$this->main_array["reject_early_reconnect"]}\n";
	$conf=$conf . "\n";
	$conf=$conf . "# Use specific reject code - Only used if reject_first_attempt/reject_early_reconnect = immed\n";
	$conf=$conf . "# (Some \"odd\" mailservers actually bounce on 450 but not 451)\n";
	$conf=$conf . "reject_code = 451\n";
	$conf=$conf . "reject_code = dunno\n";
	$conf=$conf . "\n";
	$conf=$conf . "## Update server\n";
	$conf=$conf . "# where to get updates for whitelists\n";
	$conf=$conf . "whitelists_host = sqlgrey.bouton.name\n";
	$conf=$conf . "\n";
	$conf=$conf . "## Postmaster address\n";
	$conf=$conf . "# who gets urgent notifications (DB is down for example)\n";
	$conf=$conf . "# empty: don't send mail notifications\n";
	$conf=$conf . "# default:\n";
	$conf=$conf . "admin_mail = {$this->main_array["admin_mail"]}\n";		
		
		return $conf;
	}
	
	function SaveToLdap(){
		$sock=new sockets();
		$conf=$this->BuildConfig();
		$sock->SaveClusterConfigFile($conf,"sqlGreyConf");
		$sock->SET_INFO("SqlGreyEnabled",$this->SqlGreyEnabled);
	}
	
	
	
}
