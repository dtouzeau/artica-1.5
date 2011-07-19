<?php
include_once(dirname(__FILE__)."/class.ldap.inc");
include_once(dirname(__FILE__)."/class.ini.inc");
include_once(dirname(__FILE__)."/class.templates.inc");
//see http://www.uno-code.com/?q=node/69
// see http://tfm.cz/man/5/policyd-weight.conf
//see http://postfwd.org/
class policydweight{
	var $main_array=array();
	var $dnsbl_array=array();
	var $rhsbl_array=array();
	
	function policydweight(){
		$this->load();
		
		
	}
	
	
	function load(){
		$sock=new sockets();
		$ini=new Bs_IniHandler();
		$datas=$sock->GET_INFO('ArticaPolicydWeightConfig');
		$ini->loadString($datas);
		$this->main_array=$ini->_params["POLICYD"];
		$datas=$sock->GET_INFO('ArticaPolicydWeightDNSBLConfig');
		$ini=new Bs_IniHandler();
		$ini->loadString($datas);
		$this->dnsbl_array=$ini->_params;
		
		$datas=$sock->GET_INFO('ArticaPolicydWeightRHSBLConfig');
		$ini=new Bs_IniHandler();
		$ini->loadString($datas);
		$this->rhsbl_array=$ini->_params;		
		
		
		$this->buildDefault();
	}
	
	function buildDefault(){	
	if($this->main_array["REJECTMSG"]==null){$this->main_array["REJECTMSG"]="550 Mail appeared to be SPAM or forged. Ask your Mail/DNS-Administrator to correct HELO and DNS MX settings or to get removed from DNSBLs";}
	if($this->main_array["DNSERRMSG"]==null){$this->main_array["DNSERRMSG"]="450 No DNS entries for your MTA, HELO and Domain. Contact YOUR administrator";}		
	if($this->main_array["MAXDNSERRMSG"]==null){$this->main_array["MAXDNSERRMSG"]="passed - too many local DNS-errors";}		
	if($this->main_array["MAXDNSBLMSG"]==null){$this->main_array["MAXDNSBLMSG"]="550 Your MTA is listed in too many DNSBLs";}		
	if($this->main_array["REJECTLEVEL"]==null){$this->main_array["REJECTLEVEL"]="4.25";}

	if($this->main_array["MAX_PROC"]==null){$this->main_array["MAX_PROC"]="50";}
	if($this->main_array["MIN_PROC"]==null){$this->main_array["MIN_PROC"]="3";}					
	if($this->main_array["SOMAXCONN"]==null){$this->main_array["SOMAXCONN"]="1024";}
	if($this->main_array["MAXIDLECACHE"]==null){$this->main_array["MAXIDLECACHE"]="60";}
	if($this->main_array["MAINTENANCE_LEVEL"]==null){$this->main_array["MAINTENANCE_LEVEL"]="5";}
	if($this->main_array["MAXDNSBLHITS"]==null){$this->main_array["MAXDNSBLHITS"]="2";}
	if($this->main_array["MAXDNSBLSCORE"]==null){$this->main_array["MAXDNSBLSCORE"]="8";}
	
	
	
	if(count($this->dnsbl_array)==0){		
		$this->dnsbl_array["pbl.spamhaus.org"]["HIT"]="3.25";
		$this->dnsbl_array["pbl.spamhaus.org"]["MISS"]="0";
		$this->dnsbl_array["pbl.spamhaus.org"]["LOG"]="DYN_PBL_SPAMHAUS";
		
		$this->dnsbl_array["sbl-xbl.spamhaus.org"]["HIT"]="4.35";
		$this->dnsbl_array["sbl-xbl.spamhaus.org"]["MISS"]="-1.5";
		$this->dnsbl_array["sbl-xbl.spamhaus.org"]["LOG"]="SBL_XBL_SPAMHAUS";

		$this->dnsbl_array["bl.spamcop.net"]["HIT"]="3.75";
		$this->dnsbl_array["bl.spamcop.net"]["MISS"]="-1.5";
		$this->dnsbl_array["bl.spamcop.net"]["LOG"]="SPAMCOP";	

		$this->dnsbl_array["dnsbl.njabl.org"]["HIT"]="4.25";
		$this->dnsbl_array["dnsbl.njabl.org"]["MISS"]="-1.5";
		$this->dnsbl_array["dnsbl.njabl.org"]["LOG"]="BL_NJABL";

		$this->dnsbl_array["list.dsbl.org"]["HIT"]="4.35";
		$this->dnsbl_array["list.dsbl.org"]["MISS"]="0";
		$this->dnsbl_array["list.dsbl.org"]["LOG"]="DSBL_ORG";		
		
		$this->dnsbl_array["ix.dnsbl.manitu.net"]["HIT"]="4.35";
		$this->dnsbl_array["ix.dnsbl.manitu.net"]["MISS"]="0";
		$this->dnsbl_array["ix.dnsbl.manitu.net"]["LOG"]="IX_MANITU";		
				
		
	}
	if(count($this->rhsbl_array)==0){	
		$this->rhsbl_array["multi.surbl.org"]["HIT"]="4";
		$this->rhsbl_array["multi.surbl.org"]["MISS"]="0";
		$this->rhsbl_array["multi.surbl.org"]["LOG"]="SURBL";			
		
		$this->rhsbl_array["rhsbl.ahbl.org"]["HIT"]="4";
		$this->rhsbl_array["rhsbl.ahbl.org"]["MISS"]="0";
		$this->rhsbl_array["rhsbl.ahbl.org"]["LOG"]="AHBL";			
		
		$this->rhsbl_array["dsn.rfc-ignorant.org"]["HIT"]="3.5";
		$this->rhsbl_array["dsn.rfc-ignorant.org"]["MISS"]="0";
		$this->rhsbl_array["dsn.rfc-ignorant.org"]["LOG"]="DSN_RFCI";			
		
		$this->rhsbl_array["postmaster.rfc-ignorant.org"]["HIT"]="0.1";
		$this->rhsbl_array["postmaster.rfc-ignorant.org"]["MISS"]="0";
		$this->rhsbl_array["postmaster.rfc-ignorant.org"]["LOG"]="PM_RFCI";	

		$this->rhsbl_array["abuse.rfc-ignorant.org"]["HIT"]="0.1";
		$this->rhsbl_array["abuse.rfc-ignorant.org"]["MISS"]="0";
		$this->rhsbl_array["abuse.rfc-ignorant.org"]["LOG"]="ABUSE_RFCI";
	}			
	
	}
	
	function SaveConf(){
		$this->buildDefault();
		$ini=new Bs_IniHandler();
		$ini->_params["POLICYD"]=$this->main_array;
		$conf=$this->buildConf();
		$sock=new sockets();
		$sock->SaveConfigFile($conf,"PolicydWeightConfig");
		$sock->SaveConfigFile($ini->toString(),"ArticaPolicydWeightConfig");
		
		$ini=new Bs_IniHandler();
		$ini->_params=$this->dnsbl_array;
		$sock->SaveConfigFile($ini->toString(),"ArticaPolicydWeightDNSBLConfig");
		
		$ini=new Bs_IniHandler();
		$ini->_params=$this->rhsbl_array;
		$sock->SaveConfigFile($ini->toString(),"ArticaPolicydWeightRHSBLConfig");

		$sock=new sockets();
		$sock->getFrameWork('cmd.php?PolicydWeightReplicConF=yes');
		
		
		
		$tpl=new templates();
		echo $tpl->_ENGINE_parse_body("Policyd-Weight Configuration: {success}\n");
	}
	
	
	function buildConf(){
		$conf="   \$DEBUG        = 0;               # 1 or 0 - don't comment\n";
		$conf=$conf."\n";
		$conf=$conf."   \$REJECTMSG    = \"{$this->main_array["REJECTMSG"]}\";\n";
		$conf=$conf."\n";
		$conf=$conf."   \$REJECTLEVEL  = {$this->main_array["REJECTLEVEL"]};               # Mails with scores which exceed this\n";
		$conf=$conf."                                    # REJECTLEVEL will be rejected\n";
		$conf=$conf."\n";
		$conf=$conf."   \$DEFER_STRING = 'IN_SPAMCOP= BOGUS_MX='; \n";
		$conf=$conf."                                    # A space separated case-sensitive list of\n";
		$conf=$conf."                                    # strings on which if found in the \$RET\n";
		$conf=$conf."                                    # logging-string policyd-weight changes\n";
		$conf=$conf."                                    # its action to \$DEFER_ACTION in case\n";
		$conf=$conf."                                    # of rejects.\n";
		$conf=$conf."                                    # USE WITH CAUTION!\n";
		$conf=$conf."                                    # DEFAULT: \"IN_SPAMCOP= BOGUS_MX=\"\n";
		$conf=$conf."\n";
		$conf=$conf."\n";
		$conf=$conf."   \$DEFER_ACTION = '450';           # Possible values: DEFER_IF_PERMIT,\n";
		$conf=$conf."                                    # DEFER_IF_REJECT, \n";
		$conf=$conf."                                    # 4xx response codes. See also access(5)\n";
		$conf=$conf."                                    # DEFAULT: 450\n";
		$conf=$conf."\n";
		$conf=$conf."   \$DEFER_LEVEL  = 5;               # DEFER mail only up to this level\n";
		$conf=$conf."                                    # scores greater than DEFER_LEVEL will be\n";
		$conf=$conf."                                    # rejected\n";
		$conf=$conf."                                    # DEFAULT: 5\n";
		$conf=$conf."\n";
		$conf=$conf."   \$DNSERRMSG         = \"{$this->main_array["DNSERRMSG"]}\";\n";
		$conf=$conf."\n";
		$conf=$conf."   \$dnsbl_checks_only = 0;          # 1: ON, 0: OFF (default)\n";
		$conf=$conf."                                    # If ON request that ALL clients are only\n";
		$conf=$conf."                                    # checked against RBLs\n";
		$conf=$conf."\n";
		$conf=$conf."   @dnsbl_checks_only_regexps = (\n";
		$conf=$conf."    # qr/[^.]*(exch|smtp|mx|mail).*\..*\../,\n";
		$conf=$conf."    # qr/yahoo.com\$/\n";
		$conf=$conf.");                                  # specify a comma-separated list of regexps\n";
		$conf=$conf."                                    # for client hostnames which shall only\n";
		$conf=$conf."                                    # be RBL checked. This does not work for\n";
		$conf=$conf."                                    # postfix' \"unknown\" clients.\n";
		$conf=$conf."                                    # The usage of this should not be the norm\n";
		$conf=$conf."                                    # and is a tool for people which like to\n";
		$conf=$conf."                                    # shoot in their own foot.\n";
		$conf=$conf."                                    # DEFAULT: empty\n";
		$conf=$conf."                                    \n";
		$conf=$conf."\n";
		$conf=$conf."   \$LOG_BAD_RBL_ONLY  = 1;          # 1: ON (default), 0: OFF\n";
		$conf=$conf."                                    # When set to ON it logs only RBLs which\n";
		$conf=$conf."                                    # affect scoring (positive or negative)\n";
		$conf=$conf."                                    \n";
		$conf=$conf."## DNSBL settings\n";
		$conf=$conf."#    HOST,                    HIT SCORE,  MISS SCORE,  LOG NAME\n";
		$conf=$conf."   @dnsbl_score = (\n";
		while (list ($num, $val) = each ($this->dnsbl_array) ){
			$conf=$conf."    '$num',\t{$val["HIT"]},\t{$val["MISS"]},\t'{$val["LOG"]}',\n";
			
		}
		$conf=$conf.");\n";
		$conf=$conf."\n";
		$conf=$conf."   \$MAXDNSBLHITS  = {$this->main_array["MAXDNSBLHITS"]};  # If Client IP is listed in MORE\n";
		$conf=$conf."                        # DNSBLS than this var, it gets\n";
		$conf=$conf."                        # REJECTed immediately\n";
		$conf=$conf."\n";
		$conf=$conf."   \$MAXDNSBLSCORE = {$this->main_array["MAXDNSBLSCORE"]};  # alternatively, if the score of\n";
		$conf=$conf."                        # DNSBLs is ABOVE this\n";
		$conf=$conf."                        # level, reject immediately\n";
		$conf=$conf."\n";
		$conf=$conf."   \$MAXDNSBLMSG   = '{$this->main_array["MAXDNSBLMSG"]}';\n";
		$conf=$conf."\n";
		$conf=$conf."## RHSBL settings\n";
		$conf=$conf."   @rhsbl_score = (\n";
			while (list ($num, $val) = each ($this->rhsbl_array) ){
			$conf=$conf."    '$num',\t{$val["HIT"]},\t{$val["MISS"]},\t'{$val["LOG"]}',\n";
			
		}
		$conf=$conf.");\n";
		$conf=$conf."\n";
		$conf=$conf."   \$BL_ERROR_SKIP     = 2;  # skip a RBL if this RBL had this many continuous\n";
		$conf=$conf."                            # errors\n";
		$conf=$conf."\n";
		$conf=$conf."   \$BL_SKIP_RELEASE   = 10; # skip a RBL for that many times\n";
		$conf=$conf."\n";
		$conf=$conf."## cache stuff\n";
		$conf=$conf."   \$LOCKPATH          = '/tmp/.policyd-weight/';    # must be a directory (add\n";
		$conf=$conf."                                                    # trailing slash)\n";
		$conf=$conf."\n";
		$conf=$conf."   \$SPATH             = \$LOCKPATH.'/polw.sock';     # socket path for the cache\n";
		$conf=$conf."                                                    # daemon. \n";
		$conf=$conf."\n";
		$conf=$conf."   \$MAXIDLECACHE      = {$this->main_array["MAXIDLECACHE"]}; # how many seconds the cache may be idle\n";
		$conf=$conf."                            # before starting maintenance routines\n";
		$conf=$conf."                            # NOTE: standard maintenance jobs happen\n";
		$conf=$conf."                            # regardless of this setting.\n";
		$conf=$conf."\n";
		$conf=$conf."   \$MAINTENANCE_LEVEL = {$this->main_array["MAINTENANCE_LEVEL"]};  # after this number of requests do following\n";
		$conf=$conf."                            # maintenance jobs:\n";
		$conf=$conf."                            # checking for config changes\n";
		$conf=$conf."\n";
		$conf=$conf."# negative (i.e. SPAM) result cache settings ##################################\n";
		$conf=$conf."\n";
		$conf=$conf."   \$CACHESIZE       = 2000; # set to 0 to disable caching for spam results. \n";
		$conf=$conf."                            # To this level the cache will be cleaned.\n";
		$conf=$conf."\n";
		$conf=$conf."   \$CACHEMAXSIZE    = 4000; # at this number of entries cleanup takes place\n";
		$conf=$conf."\n";
		$conf=$conf."   \$CACHEREJECTMSG  = '550 temporarily blocked because of previous errors';\n";
		$conf=$conf."\n";
		$conf=$conf."   \$NTTL            = 1;    # after NTTL retries the cache entry is deleted\n";
		$conf=$conf."\n";
		$conf=$conf."   \$NTIME           = 30;   # client MUST NOT retry within this seconds in order\n";
		$conf=$conf."                            # to decrease TTL counter\n";
		$conf=$conf."\n";
		$conf=$conf."\n";
		$conf=$conf."# positve (i.,e. HAM) result cache settings ###################################\n";
		$conf=$conf."\n";
		$conf=$conf."   \$POSCACHESIZE    = 1000; # set to 0 to disable caching of HAM. To this number\n";
		$conf=$conf."                            # of entries the cache will be cleaned\n";
		$conf=$conf."\n";
		$conf=$conf."   \$POSCACHEMAXSIZE = 2000; # at this number of entries cleanup takes place\n";
		$conf=$conf."\n";
		$conf=$conf."   \$POSCACHEMSG     = 'using cached result';\n";
		$conf=$conf."\n";
		$conf=$conf."   \$PTTL            = 60;   # after PTTL requests the HAM entry must\n";
		$conf=$conf."                            # succeed one time the RBL checks again\n";
		$conf=$conf."\n";
		$conf=$conf."   \$PTIME           = '3h'; # after \$PTIME in HAM Cache the client\n";
		$conf=$conf."                            # must pass one time the RBL checks again.\n";
		$conf=$conf."                            # Values must be nonfractal. Accepted\n";
		$conf=$conf."                            # time-units: s, m, h, d\n";
		$conf=$conf."\n";
		$conf=$conf."   \$TEMP_PTIME      = '1d'; # The client must pass this time the RBL\n";
		$conf=$conf."                            # checks in order to be listed as hard-HAM\n";
		$conf=$conf."                            # After this time the client will pass\n";
		$conf=$conf."                            # immediately for PTTL within PTIME\n";
		$conf=$conf."\n";
		$conf=$conf."\n";
		$conf=$conf."## DNS settings\n";
		$conf=$conf."   \$DNS_RETRIES     = 2;    # Retries for ONE DNS-Lookup\n";
		$conf=$conf."\n";
		$conf=$conf."   \$DNS_RETRY_IVAL  = 2;    # Retry-interval for ONE DNS-Lookup\n";
		$conf=$conf."\n";
		$conf=$conf."   \$MAXDNSERR       = 3;    # max error count for unresponded queries\n";
		$conf=$conf."                            # in a complete policy query\n";
		$conf=$conf."\n";
		$conf=$conf."   \$MAXDNSERRMSG    = '{$this->main_array["MAXDNSERRMSG"]}';\n";
		$conf=$conf."\n";
		$conf=$conf."   \$PUDP            = 0;    # persistent udp connection for DNS queries.\n";
		$conf=$conf."                            # broken in Net::DNS version 0.51. Works with\n";
		$conf=$conf."                            # Net::DNS 0.53; DEFAULT: off\n";
		$conf=$conf."\n";
		$conf=$conf."   \$USE_NET_DNS     = 0;    # Force the usage of Net::DNS for RBL lookups.\n";
		$conf=$conf."                            # Normally policyd-weight tries to use a faster\n";
		$conf=$conf."                            # RBL lookup routine instead of Net::DNS\n";
		$conf=$conf."\n";
		$conf=$conf."\n";
		$conf=$conf."   \$NS              = '';   # A list of space separated NS IPs\n";
		$conf=$conf."                            # This overrides resolv.conf settings\n";
		$conf=$conf."                            # Example: \$NS = '1.2.3.4 1.2.3.5';\n";
		$conf=$conf."                            # DEFAULT: empty\n";
		$conf=$conf."\n";
		$conf=$conf."\n";
		$conf=$conf."   \$IPC_TIMEOUT     = 2;    # timeout for receiving from cache instance\n";
		$conf=$conf."\n";
		$conf=$conf."   \$TRY_BALANCE     = 0;    # If set to 1 policyd-weight closes connections\n";
		$conf=$conf."                            # to smtpd clients in order to avoid too many\n";
		$conf=$conf."                            # established connections to one policyd-weight\n";
		$conf=$conf."                            # child\n";
		$conf=$conf."\n";
		$conf=$conf."# scores for checks, WARNING: they may manipulate eachother\n";
		$conf=$conf."# or be factors for other scores.\n";
		$conf=$conf."#                                       HIT score, MISS Score\n";
		$conf=$conf."   @client_ip_eq_helo_score          = (1.5,       -1.25 );\n";
		$conf=$conf."   @helo_score                       = (1.5,       -2    );\n";
		$conf=$conf."   @helo_from_mx_eq_ip_score         = (1.5,       -3.1  );\n";
		$conf=$conf."   @helo_numeric_score               = (2.5,        0    );\n";
		$conf=$conf."   @from_match_regex_verified_helo   = (1,         -2    );\n";
		$conf=$conf."   @from_match_regex_unverified_helo = (1.6,       -1.5  );\n";
		$conf=$conf."   @from_match_regex_failed_helo     = (2.5,        0    );\n";
		$conf=$conf."   @helo_seems_dialup                = (1.5,        0    );\n";
		$conf=$conf."   @failed_helo_seems_dialup         = (2,          0    );\n";
		$conf=$conf."   @helo_ip_in_client_subnet         = (0,         -1.2  );\n";
		$conf=$conf."   @helo_ip_in_cl16_subnet           = (0,         -0.41 );\n";
		$conf=$conf."   @client_seems_dialup_score        = (3.75,       0    );\n";
		$conf=$conf."   @from_multiparted                 = (1.09,       0    );\n";
		$conf=$conf."   @from_anon                        = (1.17,       0    );\n";
		$conf=$conf."   @bogus_mx_score                   = (2.1,        0    );\n";
		$conf=$conf."   @random_sender_score              = (0.25,       0    );\n";
		$conf=$conf."   @rhsbl_penalty_score              = (3.1,        0    );\n";
		$conf=$conf."   @enforce_dyndns_score             = (3,          0    );\n";
		$conf=$conf."\n";
		$conf=$conf."\n";
		$conf=$conf."   \$VERBOSE = 0;\n";
		$conf=$conf."\n";
		$conf=$conf."   \$ADD_X_HEADER        = 1;    # Switch on or off an additional \n";
		$conf=$conf."                                # X-policyd-weight: header\n";
		$conf=$conf."                                # DEFAULT: on\n";
		$conf=$conf."\n";
		$conf=$conf."\n";
		$conf=$conf."   \$DEFAULT_RESPONSE    = 'DUNNO default'; # Fallback response in case\n";
		$conf=$conf."                                           # the weighted check didn't\n";
		$conf=$conf."                                           # return any response (should never\n";
		$conf=$conf."                                           # appear).\n";
		$conf=$conf."\n";
		$conf=$conf."\n";
		$conf=$conf."\n";
		$conf=$conf."#\n";
		$conf=$conf."# Syslogging options for verbose mode and for fatal errors.\n";
		$conf=$conf."# NOTE: comment out the \$syslog_socktype line if syslogging does not\n";
		$conf=$conf."# work on your system.\n";
		$conf=$conf."#\n";
		$conf=$conf."\n";
		$conf=$conf."   \$syslog_socktype = 'unix';   # inet, unix, stream, console\n";
		$conf=$conf."\n";
		$conf=$conf."   \$syslog_facility = \"mail\";\n";
		$conf=$conf."   \$syslog_options  = \"pid\";\n";
		$conf=$conf."   \$syslog_priority = \"info\";\n";
		$conf=$conf."   \$syslog_ident    = \"postfix/policyd-weight\";\n";
		$conf=$conf."\n";
		$conf=$conf."\n";
		$conf=$conf."#\n";
		$conf=$conf."# Process Options\n";
		$conf=$conf."#\n";
		$conf=$conf."   \$USER            = \"postfix\";      # User must be a username, no UID\n";
		$conf=$conf."\n";
		$conf=$conf."   \$GROUP           = \"postfix\";          # specify GROUP if necessary\n";
		$conf=$conf."                                   # DEFAULT: empty, will be initialized as \n";
		$conf=$conf."                                   # \$USER\n";
		$conf=$conf."\n";
		$conf=$conf."   \$MAX_PROC        = {$this->main_array["MAX_PROC"]};          # Upper limit if child processes\n";
		$conf=$conf."   \$MIN_PROC        = {$this->main_array["MIN_PROC"]};           # keep that minimum processes alive\n";
		$conf=$conf."\n";
		$conf=$conf."   \$TCP_PORT        = 12525;       # The TCP port on which policyd-weight \n";
		$conf=$conf."                                   # listens for policy requests from postfix\n";
		$conf=$conf."\n";
		$conf=$conf."   \$BIND_ADDRESS    = '127.0.0.1'; # IP-Address on which policyd-weight will\n";
		$conf=$conf."                                   # listen for requests.\n";
		$conf=$conf."                                   # You may only list ONE IP here, if you want\n";
		$conf=$conf."                                   # to listen on all IPs you need to say 'all'\n";
		$conf=$conf."                                   # here. Default is '127.0.0.1'.\n";
		$conf=$conf."                                   # You need to restart policyd-weight if you\n";
		$conf=$conf."                                   # change this.\n";
		$conf=$conf."\n";
		$conf=$conf."   \$SOMAXCONN       = {$this->main_array["SOMAXCONN"]};        # Maximum of client connections \n";
		$conf=$conf."                                   # policyd-weight accepts\n";
		$conf=$conf."                                   # Default: 1024\n";
		$conf=$conf."                                   \n";
		$conf=$conf."\n";
		$conf=$conf."   \$CHILDIDLE       = 240;         # how many seconds a child may be idle before\n";
		$conf=$conf."                                   # it dies.\n";
		$conf=$conf."\n";
		$conf=$conf."   \$PIDFILE         = \"/var/run/policyd-weight.pid\";\n";
		$conf=$conf."   \$LOCKPATH         = \"/var/run/policyd-weight/\";\n";
		$conf=$conf."\n";
		
		return $conf;
		
	}
	
}

?>