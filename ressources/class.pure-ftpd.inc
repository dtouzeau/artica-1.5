<?php



class pureftpd{
	var $global_conf;
	var $hostname;
	var $PureFtpdEnabled;
	var $main_array;
	var $dn;
	
	function pureftpd(){
		$users=new usersMenus();
		writelogs("******************************************************",__CLASS__.'/'.__FUNCTION__,__FILE__);
		if($hostname==null){$this->hostname=$users->hostname;}else{$this->hostname=$hostname;}
		$this->LoadConfig();
		}



	
	function LoadConfig(){
			$sock=new sockets();
			$PureFtpdEnabled=$sock->GET_INFO('PureFtpdEnabled');
			$this->global_conf=$sock->GET_INFO('PureFtpdConf');
			$this->main_array=unserialize(base64_decode($sock->GET_INFO('PureFtpdConfArray')));
			
			//PureFtpdEnabled ---------------------------------------------------------------------PureFtpdEnabled
			
			if($PureFtpdEnabled==null){
				$sock->SET_INFO('PureFtpdEnabled',1);
				$this->PureFtpdEnabled=1;
			}else{
				$this->PureFtpdEnabled=$PureFtpdEnabled;
				}
			//---------------------------------------------------------------------------------------------------
			
			
	
		$this->ParseConfig();
		}
		
//-----------------------------------------------------------------------------------------------------------------------------------------
		function ParseConfig(){
			
			$table=explode("\n",$this->global_conf);
			writelogs("global configuration handle ". strlen($this->global_conf)." bytes len for ". count($table)." rows",
			__CLASS__.'/'.__FUNCTION__,__FILE__);
			while (list ($num, $val) = each ($table) ){
				if(trim($val)==null){continue;}
				if(preg_match('#^([a-zA-Z]+)\s+(.+)#i',$val,$re)){
					writelogs("{$re[1]}={$re[2]}",__CLASS__.'/'.__FUNCTION__,__FILE__);
					$this->main_array[trim($re[1])]=trim($re[2]);
				}
				
				
			}
			
			
			
			$this->BuildDefault();
			
		}
		
//-----------------------------------------------------------------------------------------------------------------------------------------	
	function SaveToLdap(){
		$PureFtpdConf=$this->BuildConfig();
		$sock=new sockets();
		$sock->SET_INFO('PureFtpdEnabled',$this->PureFtpdEnabled);
		$sock->SaveConfigFile($PureFtpdConf,"PureFtpdConf");
		$sock->SaveConfigFile(base64_encode(serialize($this->main_array)),"PureFtpdConfArray");
		$sock->getFrameWork("pure-ftpd.php?restart=yes");
		return true;
		
	}




//-----------------------------------------------------------------------------------------------------------------------------------------	
	function BuildDefault(){
		
				if($this->main_array["BrokenClientsCompatibility"]==null){$this->main_array["BrokenClientsCompatibility"]="no";}
				if($this->main_array["MaxClientsNumber"]==null){$this->main_array["MaxClientsNumber"]="50";}
				if($this->main_array["MaxClientsPerIP"]==null){$this->main_array["MaxClientsPerIP"]="8";}
				if($this->main_array["DisplayDotFiles"]==null){$this->main_array["DisplayDotFiles"]="yes";}
				if($this->main_array["AnonymousOnly"]==null){$this->main_array["AnonymousOnly"]="no";}
				if($this->main_array["NoAnonymous"]==null){$this->main_array["NoAnonymous"]="no";}
				if($this->main_array["LimitRecursion"]==null){$this->main_array["LimitRecursion"]="2000 8";}
				if($this->main_array["AnonymousCanCreateDirs"]==null){$this->main_array["AnonymousCanCreateDirs"]="no";}
				if($this->main_array["MaxLoad"]==null){$this->main_array["MaxLoad"]="4";}
				if($this->main_array["AnonymousBandwidth"]==null){$this->main_array["AnonymousBandwidth"]="8";}
				if($this->main_array["ProhibitDotFilesWrite"]==null){$this->main_array["ProhibitDotFilesWrite"]="no";}
				if($this->main_array["ProhibitDotFilesRead"]==null){$this->main_array["ProhibitDotFilesRead"]="no";}
				if($this->main_array["AutoRename"]==null){$this->main_array["AutoRename"]="no";}
				if($this->main_array["AnonymousCantUpload"]==null){$this->main_array["AnonymousCantUpload"]="no";}
				if($this->main_array["MaxDiskUsage"]==null){$this->main_array["MaxDiskUsage"]="80";}
				if($this->main_array["AntiWarez"]==null){$this->main_array["AntiWarez"]="yes";}
				if($this->main_array["MaxIdleTime"]==null){$this->main_array["MaxIdleTime"]="15";}
				if($this->main_array["PassivePortRange"]==null){$this->main_array["PassivePortRange"]="30000 50000";}
				if(!is_numeric($this->main_array["listen_port"])){$this->main_array["listen_port"]=21;}
				
		}


	
	function BuildConfig(){

				
				$this->BuildDefault();
				
		
		
				$conf=$conf . "############################################################\n";
				$conf=$conf . "#                                                          #\n";
				$conf=$conf . "#         Configuration file for pure-ftpd wrappers        #\n";
				$conf=$conf . "#                                                          #\n";
				$conf=$conf . "############################################################\n";
				$conf=$conf . "\n";
				$conf=$conf . "# If you want to run Pure-FTPd with this configuration   \n";
				$conf=$conf . "# instead of command-line options, please run the\n";
				$conf=$conf . "# following command :\n";
				$conf=$conf . "#\n";
				$conf=$conf . "# /opt/artica/sbin/pure-config.pl /opt/artica/etc/pure-ftpd.conf\n";
				$conf=$conf . "#\n";
				$conf=$conf . "# Please don't forget to have a look at documentation at\n";
				$conf=$conf . "# http://www.pureftpd.org/documentation.shtml for a complete list of\n";
				$conf=$conf . "# options.\n";
				$conf=$conf . "\n";
				$conf=$conf . "# Cage in every user in his home directory\n";
				$conf=$conf . "\n";
				$conf=$conf . "ChrootEveryone              yes\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# If the previous option is set to \"no\", members of the following group\n";
				$conf=$conf . "# won't be caged. Others will be. If you don't want chroot()ing anyone,\n";
				$conf=$conf . "# just comment out ChrootEveryone and TrustedGID.\n";
				$conf=$conf . "\n";
				$conf=$conf . "# TrustedGID                    100\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# Turn on compatibility hacks for broken clients\n";
				$conf=$conf . "\n";
				$conf=$conf . "BrokenClientsCompatibility  {$this->main_array["BrokenClientsCompatibility"]}\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# Maximum number of simultaneous users\n";
				$conf=$conf . "\n";
				$conf=$conf . "MaxClientsNumber            {$this->main_array["MaxClientsNumber"]}\n";
				$conf=$conf . "MaxIdleTime	{$this->main_array["MaxIdleTime"]}\n";				
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# Fork in background\n";
				$conf=$conf . "\n";
				$conf=$conf . "Daemonize                   yes\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# Maximum number of sim clients with the same IP address\n";
				$conf=$conf . "\n";
				$conf=$conf . "MaxClientsPerIP             {$this->main_array["MaxClientsPerIP"]}\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# If you want to log all client commands, set this to \"yes\".\n";
				$conf=$conf . "# This directive can be duplicated to also log server responses.\n";
				$conf=$conf . "\n";
				$conf=$conf . "VerboseLog                  no\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# List dot-files even when the client doesn't send \"-a\".\n";
				$conf=$conf . "\n";
				$conf=$conf . "DisplayDotFiles             {$this->main_array["DisplayDotFiles"]}\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# Don't allow authenticated users - have a public anonymous FTP only.\n";
				$conf=$conf . "\n";
				$conf=$conf . "AnonymousOnly               {$this->main_array["AnonymousOnly"]}\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# Disallow anonymous connections. Only allow authenticated users.\n";
				$conf=$conf . "\n";
				$conf=$conf . "NoAnonymous                 {$this->main_array["NoAnonymous"]}\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# Syslog facility (auth, authpriv, daemon, ftp, security, user, local*)\n";
				$conf=$conf . "# The default facility is \"ftp\". \"none\" disables logging.\n";
				$conf=$conf . "\n";
				$conf=$conf . "SyslogFacility              ftp\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# Display fortune cookies\n";
				$conf=$conf . "\n";
				$conf=$conf . "# FortunesFile              /usr/share/fortune/zippy\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# Don't resolve host names in log files. Logs are less verbose, but \n";
				$conf=$conf . "# it uses less bandwidth. Set this to \"yes\" on very busy servers or\n";
				$conf=$conf . "# if you don't have a working DNS.\n";
				$conf=$conf . "\n";
				$conf=$conf . "DontResolve                 {$this->main_array["DontResolve"]}\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# Maximum idle time in minutes (default = 15 minutes)\n";
				$conf=$conf . "\n";
				$conf=$conf . "MaxIdleTime                 {$this->main_array["MaxIdleTime"]}\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# LDAP configuration file (see README.LDAP)\n";
				$conf=$conf . "\n";
				$conf=$conf . "# LDAPConfigFile                /etc/pureftpd-ldap.conf\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# MySQL configuration file (see README.MySQL)\n";
				$conf=$conf . "\n";
				$conf=$conf . "# MySQLConfigFile               /etc/pureftpd-mysql.conf\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# Postgres configuration file (see README.PGSQL)\n";
				$conf=$conf . "\n";
				$conf=$conf . "# PGSQLConfigFile               /etc/pureftpd-pgsql.conf\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# PureDB user database (see README.Virtual-Users)\n";
				$conf=$conf . "\n";
				$conf=$conf . "# PureDB                        /etc/pureftpd.pdb\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# Path to pure-authd socket (see README.Authentication-Modules)\n";
				$conf=$conf . "\n";
				$conf=$conf . "# ExtAuth                       /var/run/ftpd.sock\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# If you want to enable PAM authentication, uncomment the following line\n";
				$conf=$conf . "\n";
				$conf=$conf . "# PAMAuthentication             yes\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# If you want simple Unix (/etc/passwd) authentication, uncomment this\n";
				$conf=$conf . "\n";
				$conf=$conf . "# UnixAuthentication            yes\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# Please note that LDAPConfigFile, MySQLConfigFile, PAMAuthentication and\n";
				$conf=$conf . "# UnixAuthentication can be used only once, but they can be combined\n";
				$conf=$conf . "# together. For instance, if you use MySQLConfigFile, then UnixAuthentication,\n";
				$conf=$conf . "# the SQL server will be asked. If the SQL authentication fails because the\n";
				$conf=$conf . "# user wasn't found, another try # will be done with /etc/passwd and\n";
				$conf=$conf . "# /etc/shadow. If the SQL authentication fails because the password was wrong,\n";
				$conf=$conf . "# the authentication chain stops here. Authentication methods are chained in\n";
				$conf=$conf . "# the order they are given. \n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# 'ls' recursion limits. The first argument is the maximum number of\n";
				$conf=$conf . "# files to be displayed. The second one is the max subdirectories depth\n";
				$conf=$conf . "\n";
				$conf=$conf . "LimitRecursion              {$this->main_array["LimitRecursion"]}\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# Are anonymous users allowed to create new directories ?\n";
				$conf=$conf . "\n";
				$conf=$conf . "AnonymousCanCreateDirs      {$this->main_array["AnonymousCanCreateDirs"]}\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# If the system is more loaded than the following value,\n";
				$conf=$conf . "# anonymous users aren't allowed to download.\n";
				$conf=$conf . "\n";
				$conf=$conf . "MaxLoad                     {$this->main_array["MaxLoad"]}\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# Port range for passive connections replies. - for firewalling.\n";
				$conf=$conf . "\n";
				$conf=$conf . "PassivePortRange          {$this->main_array["PassivePortRange"]}\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# Force an IP address in PASV/EPSV/SPSV replies. - for NAT.\n";
				$conf=$conf . "# Symbolic host names are also accepted for gateways with dynamic IP\n";
				$conf=$conf . "# addresses.\n";
				$conf=$conf . "\n";
				$conf=$conf . "# ForcePassiveIP                192.168.0.1\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# Upload/download ratio for anonymous users.\n";
				$conf=$conf . "\n";
				$conf=$conf . "# AnonymousRatio                1 10\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# Upload/download ratio for all users.\n";
				$conf=$conf . "# This directive superscedes the previous one.\n";
				$conf=$conf . "\n";
				$conf=$conf . "# UserRatio                 1 10\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# Disallow downloading of files owned by \"ftp\", ie.\n";
				$conf=$conf . "# files that were uploaded but not validated by a local admin.\n";
				$conf=$conf . "\n";
				$conf=$conf . "AntiWarez                   {$this->main_array["AntiWarez"]}\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# IP address/port to listen to (default=all IP and port 21).\n";
				$conf=$conf . "\n";
				$conf=$conf . "Bind                      {$this->main_array["listen_ip"]},{$this->main_array["listen_port"]}\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# Maximum bandwidth for anonymous users in KB/s\n";
				$conf=$conf . "\n";
				$conf=$conf . "AnonymousBandwidth            {$this->main_array["AnonymousBandwidth"]}\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# Maximum bandwidth for *all* users (including anonymous) in KB/s\n";
				$conf=$conf . "# Use AnonymousBandwidth *or* UserBandwidth, both makes no sense.\n";
				$conf=$conf . "\n";
				$conf=$conf . "# UserBandwidth             8\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# File creation mask. <umask for files>:<umask for dirs> .\n";
				$conf=$conf . "# 177:077 if you feel paranoid.\n";
				$conf=$conf . "\n";
				$conf=$conf . "Umask                       133:022\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# Minimum UID for an authenticated user to log in.\n";
				$conf=$conf . "\n";
				$conf=$conf . "MinUID                      100\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# Allow FXP transfers for authenticated users.\n";
				$conf=$conf . "\n";
				$conf=$conf . "AllowUserFXP                no\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# Allow anonymous FXP for anonymous and non-anonymous users.\n";
				$conf=$conf . "\n";
				$conf=$conf . "AllowAnonymousFXP           no\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# Users can't delete/write files beginning with a dot ('.')\n";
				$conf=$conf . "# even if they own them. If TrustedGID is enabled, this group\n";
				$conf=$conf . "# will have access to dot-files, though.\n";
				$conf=$conf . "\n";
				$conf=$conf . "ProhibitDotFilesWrite       {$this->main_array["ProhibitDotFilesWrite"]}\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# Prohibit *reading* of files beginning with a dot (.history, .ssh...)\n";
				$conf=$conf . "\n";
				$conf=$conf . "ProhibitDotFilesRead        {$this->main_array["ProhibitDotFilesRead"]}\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# Never overwrite files. When a file whoose name already exist is uploaded,\n";
				$conf=$conf . "# it get automatically renamed to file.1, file.2, file.3, ...\n";
				$conf=$conf . "\n";
				$conf=$conf . "AutoRename                  {$this->main_array["AutoRename"]}\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# Disallow anonymous users to upload new files (no = upload is allowed)\n";
				$conf=$conf . "\n";
				$conf=$conf . "AnonymousCantUpload         {$this->main_array["AnonymousCantUpload"]}\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# Only connections to this specific IP address are allowed to be\n";
				$conf=$conf . "# non-anonymous. You can use this directive to open several public IPs for\n";
				$conf=$conf . "# anonymous FTP, and keep a private firewalled IP for remote administration.\n";
				$conf=$conf . "# You can also only allow a non-routable local IP (like 10.x.x.x) to\n";
				$conf=$conf . "# authenticate, and keep a public anon-only FTP server on another IP.\n";
				$conf=$conf . "\n";
				$conf=$conf . "#TrustedIP                  10.1.1.1\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# If you want to add the PID to every logged line, uncomment the following\n";
				$conf=$conf . "# line.\n";
				$conf=$conf . "\n";
				$conf=$conf . "#LogPID                     yes\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# Create an additional log file with transfers logged in a Apache-like format :\n";
				$conf=$conf . "# fw.c9x.org - jedi [13/Dec/1975:19:36:39] \"GET /ftp/linux.tar.bz2\" 200 21809338\n";
				$conf=$conf . "# This log file can then be processed by www traffic analyzers.\n";
				$conf=$conf . "\n";
				$conf=$conf . "# AltLog                     clf:/var/log/pureftpd.log\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# Create an additional log file with transfers logged in a format optimized\n";
				$conf=$conf . "# for statistic reports.\n";
				$conf=$conf . "\n";
				$conf=$conf . "# AltLog                     stats:/var/log/pureftpd.log\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# Create an additional log file with transfers logged in the standard W3C\n";
				$conf=$conf . "# format (compatible with most commercial log analyzers)\n";
				$conf=$conf . "\n";
				$conf=$conf . "# AltLog                     w3c:/var/log/pureftpd.log\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# Disallow the CHMOD command. Users can't change perms of their files.\n";
				$conf=$conf . "\n";
				$conf=$conf . "#NoChmod                     yes\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# Allow users to resume and upload files, but *NOT* to delete them.\n";
				$conf=$conf . "\n";
				$conf=$conf . "#KeepAllFiles                yes\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# Automatically create home directories if they are missing\n";
				$conf=$conf . "\n";
				$conf=$conf . "CreateHomeDir               yes\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# Enable virtual quotas. The first number is the max number of files.\n";
				$conf=$conf . "# The second number is the max size of megabytes.\n";
				$conf=$conf . "# So 1000:10 limits every user to 1000 files and 10 Mb.\n";
				$conf=$conf . "\n";
				$conf=$conf . "#Quota                       1000:10\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# If your pure-ftpd has been compiled with standalone support, you can change\n";
				$conf=$conf . "# the location of the pid file. The default is /var/run/pure-ftpd.pid\n";
				$conf=$conf . "\n";
				$conf=$conf . "#PIDFile                     /var/run/pure-ftpd.pid\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# If your pure-ftpd has been compiled with pure-uploadscript support,\n";
				$conf=$conf . "# this will make pure-ftpd write info about new uploads to\n";
				$conf=$conf . "# /var/run/pure-ftpd.upload.pipe so pure-uploadscript can read it and\n";
				$conf=$conf . "# spawn a script to handle the upload.\n";
				$conf=$conf . "\n";
				$conf=$conf . "#CallUploadScript yes\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# This option is useful with servers where anonymous upload is \n";
				$conf=$conf . "# allowed. As /var/ftp is in /var, it save some space and protect \n";
				$conf=$conf . "# the log files. When the partition is more that X percent full,\n";
				$conf=$conf . "# new uploads are disallowed.\n";
				$conf=$conf . "\n";
				$conf=$conf . "MaxDiskUsage               {$this->main_array["MaxDiskUsage"]}\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# Set to 'yes' if you don't want your users to rename files.\n";
				$conf=$conf . "\n";
				$conf=$conf . "#NoRename                  yes\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# Be 'customer proof' : workaround against common customer mistakes like\n";
				$conf=$conf . "# 'chmod 0 public_html', that are valid, but that could cause ignorant\n";
				$conf=$conf . "# customers to lock their files, and then keep your technical support busy\n";
				$conf=$conf . "# with silly issues. If you're sure all your users have some basic Unix\n";
				$conf=$conf . "# knowledge, this feature is useless. If you're a hosting service, enable it.\n";
				$conf=$conf . "\n";
				$conf=$conf . "CustomerProof              yes\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# Per-user concurrency limits. It will only work if the FTP server has\n";
				$conf=$conf . "# been compiled with --with-peruserlimits (and this is the case on\n";
				$conf=$conf . "# most binary distributions) .\n";
				$conf=$conf . "# The format is : <max sessions per user>:<max anonymous sessions>\n";
				$conf=$conf . "# For instance, 3:20 means that the same authenticated user can have 3 active\n";
				$conf=$conf . "# sessions max. And there are 20 anonymous sessions max.\n";
				$conf=$conf . "\n";
				$conf=$conf . "# PerUserLimits            3:20\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# When a file is uploaded and there is already a previous version of the file\n";
				$conf=$conf . "# with the same name, the old file will neither get removed nor truncated.\n";
				$conf=$conf . "# Upload will take place in a temporary file and once the upload is complete,\n";
				$conf=$conf . "# the switch to the new version will be atomic. For instance, when a large PHP\n";
				$conf=$conf . "# script is being uploaded, the web server will still serve the old version and\n";
				$conf=$conf . "# immediatly switch to the new one as soon as the full file will have been\n";
				$conf=$conf . "# transfered. This option is incompatible with virtual quotas.\n";
				$conf=$conf . "\n";
				$conf=$conf . "# NoTruncate               yes\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# This option can accept three values :\n";
				$conf=$conf . "# 0 : disable SSL/TLS encryption layer (default).\n";
				$conf=$conf . "# 1 : accept both traditional and encrypted sessions.\n";
				$conf=$conf . "# 2 : refuse connections that don't use SSL/TLS security mechanisms,\n";
				$conf=$conf . "#     including anonymous sessions.\n";
				$conf=$conf . "# Do _not_ uncomment this blindly. Be sure that :\n";
				$conf=$conf . "# 1) Your server has been compiled with SSL/TLS support (--with-tls),\n";
				$conf=$conf . "# 2) A valid certificate is in place,\n";
				$conf=$conf . "# 3) Only compatible clients will log in.\n";
				$conf=$conf . "\n";
				$conf=$conf . "# TLS                      1\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# Listen only to IPv4 addresses in standalone mode (ie. disable IPv6)\n";
				$conf=$conf . "# By default, both IPv4 and IPv6 are enabled.\n";
				$conf=$conf . "\n";
				$conf=$conf . "# IPV4Only                 yes\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "\n";
				$conf=$conf . "# Listen only to IPv6 addresses in standalone mode (ie. disable IPv4)\n";
				$conf=$conf . "# By default, both IPv4 and IPv6 are enabled.\n";
				$conf=$conf . "\n";
				$conf=$conf . "# IPV6Only                 yes\n";
				$conf=$conf . "\n";
				$conf=$conf . "# UTF-8 support for file names (RFC 2640)\n";
				$conf=$conf . "# Define charset of the server filesystem and optionnally the default charset\n";
				$conf=$conf . "# for remote clients if they don't use UTF-8.\n";
				$conf=$conf . "# Works only if pure-ftpd has been compiled with --with-rfc2640\n";
				$conf=$conf . "\n";
				$conf=$conf . "FileSystemCharset	big5\n";
				$conf=$conf . "ClientCharset		big5\n";
				$conf=$conf . "PIDFile	/var/run/pure-ftpd.pid\n";
				$conf=$conf . "AltLog	w3c:/var/log/pureftpd.log\n";
				$conf=$conf . "CreateHomeDir	yes\n";
				$conf=$conf . "\n";
				$conf=$conf . "#LDAPConfigFile	/opt/artica/etc/pure-ftpd.ldap.conf\n";
				$conf=$conf . "#PureDB	/opt/artica/var/pureftpd/pureftpd.pdb\n";
				return $conf;
		
	}
}


class pureftpd_user{
	
	function pureftpd_user(){
		$this->CreateBranchs();
		
	}
	
	function CreateBranchs(){
		$ldap=new clladp();
		$update_array=array();
		$update_array['objectClass'][]='top';
		$update_array['objectClass'][]='dcObject';
		$update_array['objectClass'][]='organization';
		$update_array["dc"][0]="pureftpd";
		$update_array["o"][0]="pureftpd";
		
		$dn="dc=pureftpd,dc=organizations,$ldap->suffix";
		if(!$ldap->ExistsDN($dn)){
			$dn=$ldap->dn_escape($dn);
			if(!$ldap->ldap_add($dn,$update_array)){
			if($ldap->ldap_last_error_num<>68){echo "Error: Adding
				dn:$dn
				".$ldap->ldap_last_error."
				File: ".__FILE__."
				Function: ".__FUNCTION__."
				Class: ".__CLASS__."
				Line: ".__LINE__."\n";return false;}
			}
		}
	
		
	}
	
	
	function CreateUser($username,$password,$servername){
		
		
		$update_array=array();
		$ldap=new clladp();
		
		
		$dn="dc=$servername,dc=pureftpd,dc=organizations,$ldap->suffix";
		if($ldap->ExistsDN($dn)){$ldap->ldap_delete($dn,true);}
		
		
		
		if(!$ldap->ExistsDN($dn)){
				$update_array['objectClass'][]='top';
				$update_array['objectClass'][]='dcObject';
				$update_array['objectClass'][]='organization';
				$update_array["dc"][0]="$servername";
				$update_array["o"][0]="$servername";
								
			if(!$ldap->ldap_add($dn,$update_array)){
				if($ldap->ldap_last_error_num<>68){echo "Error: Adding\ndn:$dn\n".$ldap->ldap_last_error."
				File: ".__FILE__."
				Function: ".__FUNCTION__."
				Class: ".__CLASS__."
				Line: ".__LINE__."\n";return false;}
			}
		}		
		
		unset($update_array);
		$dir="/var/www/$servername";
		$sock=new sockets();
		$vgservices=unserialize(base64_decode($sock->GET_INFO("vgservices")));
		if($vgservices["freewebs"]<>null){
			$dir="/automounts/{$vgservices["freewebs"]}/$servername";
		}
		
		$dn="cn=$username,dc=$servername,dc=pureftpd,dc=organizations,$ldap->suffix";
		if($ldap->ExistsDN($dn)){$ldap->ldap_delete($dn,true);}
		
		if(!$ldap->ExistsDN($dn)){
			$update_array["objectClass"][]="userAccount";
			$update_array["objectClass"][]="PureFTPdUser";
			$update_array["accountActive"][0]="TRUE";
			$update_array["accountGroup"][0]="ftp";
			$update_array["domainName"][0]="$servername";
			$update_array["homeDirectory"][0]=$dir;
			$update_array["mailDir"][0]=$dir;
			$update_array["sn"][0]="$username";
			$update_array["uid"][0]="$username";
			$update_array["cn"][0]="$username";
			$update_array["userPassword"][0]="$password";
			$update_array["FTPStatus"][0]="TRUE";
			
			if(!$ldap->ldap_add($dn,$update_array)){
				if($ldap->ldap_last_error_num<>68){echo "Error: Adding\ndn:$dn\n".$ldap->ldap_last_error."
				in ".__FILE__."
				Function ".__FUNCTION__."Line ".__LINE__."
				Class ".__CLASS__;return false;}
			}
				
		}else{
			$update_array["domainName"][0]="$servername";
			$update_array["homeDirectory"][0]=$dir;
			$update_array["mailDir"][0]=$dir;
			$update_array["sn"][0]="$username";
			$update_array["uid"][0]="$username";
			$update_array["userPassword"][0]="$password";
			$update_array["FTPStatus"][0]="TRUE";
			
			if(!$ldap->Ldap_modify($dn,$update_array)){
				if($ldap->ldap_last_error_num<>68){echo "Error: Adding\ndn:$dn\n".$ldap->ldap_last_error."\n".__FILE__."\n".__FUNCTION__."\n".__LINE__;return false;}
			}			
		}
	$sock=new sockets();
	$sock->getFrameWork("services.php?reload-pure-ftpd=yes");
	return true;
	}
	
}

?>