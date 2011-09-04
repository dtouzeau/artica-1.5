<?php
	include_once(dirname(__FILE__).'/class.artica.inc');
	include_once(dirname(__FILE__).'/class.mysql.inc');
	include_once(dirname(__FILE__).'/class.ini.inc');
	
	class roundcube{
		var $https_port=443;
		var $lighttp_min_proc;
		var $lighttp_max_proc;
		var $lighttp_max_load_per_proc;
		var $PHP_FCGI_CHILDREN;
		var $PHP_FCGI_MAX_REQUESTS;
		var $RoundCubeLightHTTPD;
		var $RoundCubeHTTPEngineEnabled;
		var $roundCubeArray=array();
		var $ini_flat;
		var $ssl_enabled=1;
		var $roundcube_plugins_line=null;
		var $roundcube_plugins_array=array();
		var $roundcubeWebsites=array();
		var $AsRoot=false;
		
		function roundcube(){
			$artica=new artica_general();
			if(posix_getuid()==0){$this->AsRoot=true;}
			$users=new usersMenus();
			$sock=new sockets();
			$this->RoundCubeLightHTTPD=$artica->RoundCubeLightHTTPD;
			$this->RoundCubeHTTPEngineEnabled=$artica->RoundCubeHTTPEngineEnabled;
			
			if($this->RoundCubeLightHTTPD==null){$this->RoundCubeLightHTTPD=$this->Build_lighthttp();}
			if($this->RoundCubeHTTPEngineEnabled==null){$this->RoundCubeHTTPEngineEnabled=0;}
			$this->Parse_lighttpd();
			$ini=new Bs_IniHandler();
			
			if(trim($artica->RoundCubeArticaConfigurationFile)==null){$artica->RoundCubeArticaConfigurationFile=$sock->getfile("INFOS:RoundCubeArticaConfigurationFile");}
			$this->RoundCubeHTTPEngineEnabled=$artica->RoundCubeHTTPEngineEnabled;		
			$ini->loadString($artica->RoundCubeArticaConfigurationFile);
			$this->roundCubeArray=$ini->_params["CONF"];
			$this->ssl_enabled=$ini->_params["CONF"]["ssl_enabled"];
			$this->roundcubeWebsites=$ini->_params;			
			$this->BuilDefault();
			
			$this->roundcube_plugins_line=$users->roundcube_plugins;
			$this->roundcube_plugins_array=$this->BuildPlugins();
			
			
			
			
		}
		
	function Parse_lighttpd(){
		$tbl=explode("\n",$this->RoundCubeLightHTTPD);
		$this->ssl_enabled=0;
		while (list ($num, $line) = each ($tbl)){
			if(preg_match('#min-procs.+?([0-9]+)#',$line,$re)){$this->lighttp_min_proc=$re[1];}
			if(preg_match('#server\.port\s+=\s+([0-9]+)#',$line,$re)){$this->https_port=$re[1];}
			if(preg_match('#max-procs.+?([0-9]+)#',$line,$re)){$this->lighttp_max_proc=$re[1];}
			if(preg_match('#max-load-per-proc.+?([0-9]+)#',$line,$re)){$this->lighttp_max_load_per_proc=$re[1];}
			if(preg_match('#PHP_FCGI_CHILDREN.+?([0-9]+)#',$line,$re)){$this->PHP_FCGI_CHILDREN=$re[1];}
			if(preg_match('#PHP_FCGI_MAX_REQUESTS.+?([0-9]+)#',$line,$re)){$this->PHP_FCGI_MAX_REQUESTS=$re[1];}
			if(preg_match('#ssl\.engine.+?enable#',$line,$re)){$this->ssl_enabled=1;}
			
			
			
		}
		}
		
		
	function BuildPlugins(){
		$arr=explode(';',$this->roundcube_plugins_line);
		if(!is_array($arr)){return array();}
		while (list ($num, $line) = each ($arr)){
			if(trim($line)==null){continue;}
			$array[$line]="{{$line}}";
		}
		
		return $array;
	}
	
	function BuildConfig(){
		return $this->RoundCubeConfig();
	}

	function Save(){
		$sock=new sockets();
		$sock->SET_INFO("RoundCubeLightHTTPD",$this->RoundCubeLightHTTPD);
		$sock->SET_INFO("RoundCubeHTTPEngineEnabled",$this->RoundCubeHTTPEngineEnabled);
		$sock->SET_INFO('php5UploadMaxFileSize',$this->roundCubeArray["upload_max_filesize"]);	

		$mainconf=$this->Build_lighthttp();
		$sock->SaveConfigFile($mainconf,"RoundCubeLightHTTPD");
		$sock->SaveConfigFile($this->BuildConf(),"RoundCubeArticaConfigurationFile");
		$sock->SaveConfigFile($this->RoundCubeConfig(),"RoundCubeConfigurationFile");
		
		if(!$this->AsRoot){
			$sock->getFrameWork("cmd.php?roundcube-restart=yes");
		}
		
	}
	
	function BuildUserlink(){
		if($this->RoundCubeHTTPEngineEnabled==1){
			$_user=new usersMenus();
			return "https://$_user->fqdn:$this->https_port";
		}else {
			include_once(dirname(__FILE__).'/class.httpd.inc');
			$_user=new usersMenus();
			$h=new httpd();
			return "https://$_user->fqdn:$h->https_port/webmail";
		}
		
	}
	
	function BuilDefault(){
				$sock=new sockets();
				if($this->https_port==null){$this->https_port=443;}
				if($this->lighttp_min_proc==null){$this->lighttp_min_proc=1;}
				if(trim($this->lighttp_max_proc==null)){$this->lighttp_max_proc=2;}
				if($this->lighttp_max_load_per_proc==null){$this->lighttp_max_load_per_proc=2;}
				if($this->PHP_FCGI_CHILDREN==null){$this->PHP_FCGI_CHILDREN=4;}
				if($this->PHP_FCGI_MAX_REQUESTS==null){$this->PHP_FCGI_MAX_REQUESTS=100;}
				$users=new usersMenus();
				if($this->roundCubeArray["debug_level"]==null){$this->roundCubeArray["debug_level"]=1;}
				if($this->roundCubeArray["enable_caching"]==null){$this->roundCubeArray["enable_caching"]="TRUE";}
				if($this->roundCubeArray["auto_create_user"]==null){$this->roundCubeArray["auto_create_user"]="TRUE";}
				if($this->roundCubeArray["default_host"]==null){$this->roundCubeArray["default_host"]="127.0.0.1";}
				if($this->roundCubeArray["locale_string"]==null){$this->roundCubeArray["locale_string"]="us";}
				if($this->roundCubeArray["product_name"]==null){$this->roundCubeArray["product_name"]="RoundCube Webmail for Artica";}				
				if($this->roundCubeArray["user_link"]==null){$this->roundCubeArray["user_link"]=$this->BuildUserlink();}
				if($this->roundCubeArray["ldap_ok"]==null){$this->roundCubeArray["ldap_ok"]=0;}
				if($this->roundCubeArray["skip_deleted"]==null){$this->roundCubeArray["skip_deleted"]="FALSE";}
				if($this->roundCubeArray["flag_for_deletion"]==null){$this->roundCubeArray["flag_for_deletion"]="TRUE";}
				if($this->roundCubeArray["ssl_enabled"]==null){$this->roundCubeArray["ssl_enabled"]="1";}
				
				$this->roundCubeArray["upload_max_filesize"]=$sock->GET_INFO('php5UploadMaxFileSize');
				if($this->roundCubeArray["upload_max_filesize"]==null){$this->roundCubeArray["upload_max_filesize"]="2";}
				
				
				
				
	}
	
	function  BuildConf(){
		$conf="[CONF]\n";
		
		while (list ($num, $line) = each ($this->roundCubeArray)){
			if(!is_array($line)){
				if(!is_array($line)){$conf=$conf. "$num=$line\n";}
			}
			
		}
		
		$conf=$conf. "ssl_enabled=$this->ssl_enabled\n";
		
		
		if(is_array($this->roundcubeWebsites)){
			while (list ($num, $line) = each ($this->roundcubeWebsites)){
				if($num=="CONF"){continue;}
					$conf=$conf. "[$num]\nservername={$line["servername"]}\n";
			
		}}		
		
		return $conf;
		
	}
		
		
function Build_lighthttp(){
						
				$this->BuilDefault();
				$users=new usersMenus();
				
				$conf=$conf."#artica-postfix saved by artica lighttpd-roundcube.conf\n\n";
				$conf=$conf."server.modules = (\n";
				$conf=$conf."\t\"mod_alias\",\n";
				$conf=$conf."\t\"mod_access\",\n";
				$conf=$conf."\t\"mod_accesslog\",\n";
				$conf=$conf."\t\"mod_compress\",\n";
				$conf=$conf."\t\"mod_fastcgi\",\n";
				$conf=$conf."\t\"mod_status\"\n";
				$conf=$conf.")\n";
				$conf=$conf."\n";
				$conf=$conf."server.document-root        = \"$users->roundcube_web_folder\"\n";
				$conf=$conf."server.username = \"www-data\"\n";
				$conf=$conf."server.groupname = \"www-data\"\n";
				$conf=$conf."server.errorlog             = \"/var/log/lighttpd/roundcube-error.log\"\n";
				$conf=$conf."index-file.names            = ( \"index.php\")\n";
				$conf=$conf."\n";
				$conf=$conf."mimetype.assign             = (\n";
				$conf=$conf."\t\".pdf\"          =>      \"application/pdf\",\n";
				$conf=$conf."\t\".sig\"          =>      \"application/pgp-signature\",\n";
				$conf=$conf."\t\".spl\"          =>      \"application/futuresplash\",\n";
				$conf=$conf."\t\".class\"        =>      \"application/octet-stream\",\n";
				$conf=$conf."\t\".ps\"           =>      \"application/postscript\",\n";
				$conf=$conf."\t\".torrent\"      =>      \"application/x-bittorrent\",\n";
				$conf=$conf."\t\".dvi\"          =>      \"application/x-dvi\",\n";
				$conf=$conf."\t\".gz\"           =>      \"application/x-gzip\",\n";
				$conf=$conf."\t\".pac\"          =>      \"application/x-ns-proxy-autoconfig\",\n";
				$conf=$conf."\t\".swf\"          =>      \"application/x-shockwave-flash\",\n";
				$conf=$conf."\t\".tar.gz\"       =>      \"application/x-tgz\",\n";
				$conf=$conf."\t\".tgz\"          =>      \"application/x-tgz\",\n";
				$conf=$conf."\t\".tar\"          =>      \"application/x-tar\",\n";
				$conf=$conf."\t\".zip\"          =>      \"application/zip\",\n";
				$conf=$conf."\t\".mp3\"          =>      \"audio/mpeg\",\n";
				$conf=$conf."\t\".m3u\"          =>      \"audio/x-mpegurl\",\n";
				$conf=$conf."\t\".wma\"          =>      \"audio/x-ms-wma\",\n";
				$conf=$conf."\t\".wax\"          =>      \"audio/x-ms-wax\",\n";
				$conf=$conf."\t\".ogg\"          =>      \"application/ogg\",\n";
				$conf=$conf."\t\".wav\"          =>      \"audio/x-wav\",\n";
				$conf=$conf."\t\".gif\"          =>      \"image/gif\",\n";
				$conf=$conf."\t\".jar\"          =>      \"application/x-java-archive\",\n";
				$conf=$conf."\t\".jpg\"          =>      \"image/jpeg\",\n";
				$conf=$conf."\t\".jpeg\"         =>      \"image/jpeg\",\n";
				$conf=$conf."\t\".png\"          =>      \"image/png\",\n";
				$conf=$conf."\t\".xbm\"          =>      \"image/x-xbitmap\",\n";
				$conf=$conf."\t\".xpm\"          =>      \"image/x-xpixmap\",\n";
				$conf=$conf."\t\".xwd\"          =>      \"image/x-xwindowdump\",\n";
				$conf=$conf."\t\".css\"          =>      \"text/css\",\n";
				$conf=$conf."\t\".html\"         =>      \"text/html\",\n";
				$conf=$conf."\t\".htm\"          =>      \"text/html\",\n";
				$conf=$conf."\t\".js\"           =>      \"text/javascript\",\n";
				$conf=$conf."\t\".asc\"          =>      \"text/plain\",\n";
				$conf=$conf."\t\".c\"            =>      \"text/plain\",\n";
				$conf=$conf."\t\".cpp\"          =>      \"text/plain\",\n";
				$conf=$conf."\t\".log\"          =>      \"text/plain\",\n";
				$conf=$conf."\t\".conf\"         =>      \"text/plain\",\n";
				$conf=$conf."\t\".text\"         =>      \"text/plain\",\n";
				$conf=$conf."\t\".txt\"          =>      \"text/plain\",\n";
				$conf=$conf."\t\".dtd\"          =>      \"text/xml\",\n";
				$conf=$conf."\t\".xml\"          =>      \"text/xml\",\n";
				$conf=$conf."\t\".mpeg\"         =>      \"video/mpeg\",\n";
				$conf=$conf."\t\".mpg\"          =>      \"video/mpeg\",\n";
				$conf=$conf."\t\".mov\"          =>      \"video/quicktime\",\n";
				$conf=$conf."\t\".qt\"           =>      \"video/quicktime\",\n";
				$conf=$conf."\t\".avi\"          =>      \"video/x-msvideo\",\n";
				$conf=$conf."\t\".asf\"          =>      \"video/x-ms-asf\",\n";
				$conf=$conf."\t\".asx\"          =>      \"video/x-ms-asf\",\n";
				$conf=$conf."\t\".wmv\"          =>      \"video/x-ms-wmv\",\n";
				$conf=$conf."\t\".bz2\"          =>      \"application/x-bzip\",\n";
				$conf=$conf."\t\".tbz\"          =>      \"application/x-bzip-compressed-tar\",\n";
				$conf=$conf."\t\".tar.bz2\"      =>      \"application/x-bzip-compressed-tar\",\n";
				$conf=$conf."\t\"\"              =>      \"application/octet-stream\",\n";
				$conf=$conf." )\n";
				$conf=$conf."\n";
				$conf=$conf."\n";
				$conf=$conf."accesslog.filename          = \"/var/log/lighttpd/roundcube-access.log\"\n";
				$conf=$conf."url.access-deny             = ( \"~\", \".inc\" )\n";
				$conf=$conf."\n";
				$conf=$conf."static-file.exclude-extensions = ( \".php\", \".pl\", \".fcgi\" )\n";
				$conf=$conf."server.port                 = $this->https_port\n";
				$conf=$conf."#server.bind                = \"127.0.0.1\"\n";
				$conf=$conf."#server.error-handler-404   = \"/error-handler.html\"\n";
				$conf=$conf."#server.error-handler-404   = \"/error-handler.php\"\n";
				$conf=$conf."server.pid-file             = \"/var/run/lighttpd/lighttpd-roundcube.pid\"\n";
				$conf=$conf."server.max-fds 		     = 2048\n";
				$conf=$conf."cgi.fix_pathinfo 			 = 1\n";
				$conf=$conf."\n";
				$conf=$conf."fastcgi.server = ( \".php\" =>((\n";
				$conf=$conf."\t\t\"bin-path\" => \"$users->phpcgi\",\n";
				$conf=$conf."\t\t\"host\" => \"127.0.0.1\",\"port\" =>1809,\n";
				$conf=$conf."\t\t\"min-procs\" => 1,\n";
				$conf=$conf."\t\t\"max-procs\" => $this->lighttp_max_proc,\n";
				$conf=$conf."\t\t\"max-load-per-proc\" => $this->lighttp_max_load_per_proc,\n";
				$conf=$conf."\t\t\"idle-timeout\" => 10,\n";
				$conf=$conf."\t\t \"bin-environment\" => (\n";
				$conf=$conf."\t\t\t\"PHP_FCGI_CHILDREN\" => \"$this->PHP_FCGI_CHILDREN\",\n";
				$conf=$conf."\t\t\t\"PHP_FCGI_MAX_REQUESTS\" => \"$this->PHP_FCGI_MAX_REQUESTS\"\n";
				$conf=$conf."\t\t),\n";
				$conf=$conf."\t\t\"bin-copy-environment\" => (\n";
				$conf=$conf."\t\t\t\"PATH\", \"SHELL\", \"USER\"\n";
				$conf=$conf."\t\t),\n";
				$conf=$conf."\t\t\"broken-scriptfilename\" => \"enable\"\n";
				$conf=$conf."\t))\n";
				$conf=$conf.")\n";
				if($this->ssl_enabled==1){$ssl="enable";}else{$ssl="disable";}
				$conf=$conf."ssl.engine                 = \"$ssl\"\n";
				$conf=$conf."ssl.pemfile                = \"/opt/artica/ssl/certs/lighttpd.pem\"\n";
				$conf=$conf."status.status-url          = \"/server-status\"\n";
				$conf=$conf."status.config-url          = \"/server-config\"\n";				
				return $conf;		
		
		
	}	

	
	function ParseMysqlInstall(){
		$users=new usersMenus();
		$path=$users->roundcube_mysql_sources;
		$datas=@file_get_contents($path);
		$tables=array();
		$tbl=explode("\n",$datas);
		while (list ($num, $line) = each ($tbl)){
		if(preg_match('#CREATE TABLE (.+?)\s+#',$line,$re)){
			$re[1]=str_replace('`','',$re[1]);
			$tables[$re[1]]=$re[1];
			}
		}
		
		
		$html="<table style='width:1OO%'>";
		
		while (list ($num, $line) = each ($tables)){
			$mysql=new mysql();
			if(!$mysql->TABLE_EXISTS($num,"roundcubemail")){
				$img="icon_mini_off.gif";
			}else{$img="icon-mini-ok.gif";}
			
			$html=$html .  "<tr>
			<td width=1%><img src='img/$img'></td>
			<td width=99%><strong>$line</strong>
			</tr>";
			
		}
		
		return $html."</table>";
	}
	
	function  RebuildMysql(){
		$mysql=new mysql();
		$users=new usersMenus();
		$path=$users->roundcube_mysql_sources;
		$mysql->DELETE_DATABASE('roundcubemail');
		$mysql->CREATE_DATABASE('roundcubemail');
		$this->ParseSQLCommands($path);
	}
	
	
	function ParseSQLCommands($file){
		$datas=@file_get_contents($file);
		$mysql=new  mysql();
		if(preg_match_all("#CREATE(.+?);#is",$datas,$re)){
			while (list ($num, $line) = each ($re[0])){
				$sql=$line;
				$mysql->QUERY_SQL($sql,"roundcubemail");
			}
		}
		
	}
	
	public function plugin_install($basedir,$pluginname){
		if(!is_dir("/usr/share/artica-postfix/bin/install/roundcube/$pluginname")){return;}
		if(!is_dir($basedir)){return;}
		if($this->AsRoot){echo "Starting......: Roundcube installing $pluginname plugin\n";}
		@mkdir("$basedir/plugins/$pluginname",null,true);
		shell_exec("/bin/cp -rf /usr/share/artica-postfix/bin/install/roundcube/$pluginname/* $basedir/plugins/$pluginname/");
		
		
	}
	
	public function plugin_jqueryui($basedir){
		$f[]="<?php";
		$f[]="\$rcmail_config['jquery_ui_i18n'] = array('datepicker');";
		$f[]="?>";
		@file_put_contents("$basedir/plugins/jqueryui/config.inc.php",@implode("\n",$f));
	}
	
	public function plugin_password($basedir,$ou=null){
		if(!$this->AsRoot){return;}
		include_once("/usr/share/artica-postfix/framework/class.unix.inc");
		$unix=new unix();
		$pear=$unix->find_program("pear");
		if(strlen($pear)<5){return null;}
		if(!$unix->PEAR_INSTALL_CHECK("Net_LDAP2")){
			shell_exec("$pear install Net_LDAP2-2.0.10");
		}
		if(!$unix->PEAR_INSTALL_CHECK("Net_LDAP2")){return false;}
		if(!is_file("$basedir/plugins/password/password.php")){return false;}
		$ldap=new clladp();
		if($ou<>null){$dnprefix="ou=$ou,";}
		$conf[]="<?php";
		$conf[]="\$rcmail_config['password_driver'] = 'ldap';";
		$conf[]="\$rcmail_config['password_confirm_current'] = true;";
		$conf[]="\$rcmail_config['password_minimum_length'] = 0;";
		$conf[]="\$rcmail_config['password_require_nonalpha'] = false;";
		$conf[]="\$rcmail_config['password_ldap_host'] = '$ldap->ldap_host';";
		$conf[]="\$rcmail_config['password_ldap_port'] = '$ldap->ldap_port';";
		$conf[]="\$rcmail_config['password_ldap_starttls'] = false;";
		$conf[]="\$rcmail_config['password_ldap_version'] = '3';";
		$conf[]="\$rcmail_config['password_ldap_basedn'] = '$ldap->suffix';";
		$conf[]="\$rcmail_config['password_ldap_method'] = 'admin';";
		$conf[]="\$rcmail_config['password_ldap_adminDN'] = 'cn=$ldap->ldap_admin,$ldap->suffix';";
		$conf[]="\$rcmail_config['password_ldap_adminPW'] = '$ldap->ldap_password';";
		//$conf[]="\$rcmail_config['password_ldap_userDN_mask'] = 'uid=%login,ou=people,dc=exemple,dc=com';";
		$conf[]="\$rcmail_config['password_ldap_searchDN'] = 'cn=$ldap->ldap_admin,$ldap->suffix';";
		$conf[]="\$rcmail_config['password_ldap_searchPW'] = '$ldap->ldap_password';";
		$conf[]="\$rcmail_config['password_ldap_search_base'] = '{$dnprefix}dc=organizations,$ldap->suffix';";
		$conf[]="\$rcmail_config['password_ldap_search_filter'] = '(uid=%login)';";
		$conf[]="\$rcmail_config['password_ldap_encodage'] = 'clear';";
		$conf[]="\$rcmail_config['password_ldap_pwattr'] = 'userPassword';";
		$conf[]="\$rcmail_config['password_ldap_force_replace'] = true;";
		$conf[]="?>";
		if(file_put_contents("$basedir/plugins/password/config.inc.php",@implode("\n",$conf))){return true;}
		
	}
	
	
	function  RoundCubeConfig(){
		$sock=new sockets();
		$RoundCubeEnableSieve=$sock->GET_INFO("RoundCubeEnableSieve");
		$RoundCubeEnableCalendar=$sock->GET_INFO("RoundCubeEnableCalendar");
		$users=new usersMenus();
		$sql="SELECT enabled FROM roundcube_globaladdressbook WHERE hostname='MAIN_INSTANCE'";
		$q=new mysql();
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
		$RoundCubeEnableGlobalAddressBook=$ligne["enabled"];
		
		$plugins='';
			reset($this->roundCubeArray);
			while (list ($num, $line) = each ($this->roundCubeArray)){
					if(preg_match("#plugin\_(.+)#",$num,$re)){
						if($re[1]=="sieverules"){continue;}
						if($line==1){
							$arr[]="'{$re[1]}'";
							//$plugins=$plugins."\$rcmail_config['plugins']['{$re[1]}'] = TRUE;\n";
						}
					}else{
						
					}
				}
				
			reset($this->roundCubeArray);
				
		    if($RoundCubeEnableSieve==1){
		    	if($this->AsRoot){echo "Starting......: Roundcube sieverules Enabled\n";}
		    	$arr[]="'sieverules'";
		    	$sock->getFrameWork("cmd.php?roundcube-install-sieverules=yes");
		    }
		    
			if($RoundCubeEnableCalendar==1){
				if($this->AsRoot){echo "Starting......: Roundcube calendar Enabled\n";}
		    	$arr[]="'calendar'";
		    	$sock->getFrameWork("cmd.php?roundcube-install-calendar=yes");
		    }	

		    
		    writelogs("RoundCubeEnableGlobalAddressBook=$RoundCubeEnableGlobalAddressBook",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		    if($RoundCubeEnableGlobalAddressBook==1){
		    	if($this->AsRoot){echo "Starting......: Roundcube Global Address Book Enabled\n";}
		   		$sock->getFrameWork("cmd.php?roundcube-install-globaladdressbook=yes");
		    	$arr[]="'globaladdressbook'";
		    }
		    
			if($users->roundcube_subscriptions_option){
				$this->roundCubeArray["plugin_subscriptions_option"]=1;
				$arr[]="'subscriptions_option'";
			}
			
			if($this->AsRoot){
				if(!is_file("$users->roundcube_web_folder/plugins/msglistcols/msglistcols.php")){$this->plugin_install($users->roundcube_web_folder,"msglistcols");}
				if(!is_file("$users->roundcube_web_folder/plugins/sticky_notes/sticky_notes.php")){$this->plugin_install($users->roundcube_web_folder,"sticky_notes");}
				if(!is_file("$users->roundcube_web_folder/plugins/jqueryui/jqueryui.php")){$this->plugin_install($users->roundcube_web_folder,"jqueryui");}
				if(!is_file("$users->roundcube_web_folder/plugins/dkimstatus/dkimstatus.php")){$this->plugin_install($users->roundcube_web_folder,"dkimstatus");}
				if(!is_file("$users->roundcube_web_folder/plugins/fail2ban/fail2ban.php")){$this->plugin_install($users->roundcube_web_folder,"fail2ban");}
			
				
				
				
				if(is_file("$users->roundcube_web_folder/plugins/msglistcols/msglistcols.php")){
					echo "Starting......: Roundcube msglistcols Enabled\n";
					$arr[]="'msglistcols'";
				}
				
				
				if(is_file("$users->roundcube_web_folder/plugins/dkimstatus/dkimstatus.php")){
					echo "Starting......: Roundcube dkimstatus Enabled\n";
					$arr[]="'dkimstatus'";
				}
				
				if(is_file("$users->roundcube_web_folder/plugins/fail2ban/fail2ban.php")){
					echo "Starting......: Roundcube fail2ban Enabled\n";
					$arr[]="'fail2ban'";
				}				
				
				
				if(is_file("$users->roundcube_web_folder/plugins/jqueryui/jqueryui.php")){
					echo "Starting......: Roundcube jqueryui Enabled\n";
					$this->plugin_jqueryui($users->roundcube_web_folder);
					$arr[]="'jqueryui'";
					
					if(is_file("$users->roundcube_web_folder/plugins/sticky_notes/sticky_notes.php")){
						echo "Starting......: Roundcube sticky_notes Enabled\n";
						$arr[]="'sticky_notes'";
					}					
				}
				
				if($this->plugin_password($users->roundcube_web_folder,null)){
					echo "Starting......: Roundcube password Enabled\n";
					$arr[]="'password'";
				}else{
					echo "Starting......: Roundcube password failed\n";
				}
				
			
				
				
			}
				
	
				if(is_array($arr)){$plugins="\$rcmail_config['plugins'] = array(".implode(',',$arr).");";}
				$version_bin=$users->roundcube_intversion;

				$conf[]="<?php";
				$conf[]="\$rcmail_config = array();";
				$conf[]="\$rcmail_config['debug_level'] = {$this->roundCubeArray["debug_level"]};";
				$conf[]="\$rcmail_config['log_driver'] = 'file';";
				$conf[]="\$rcmail_config['log_date_format'] = 'd-M-Y H:i:s O';";
				$conf[]="\$rcmail_config['syslog_id'] = 'roundcube';";
				$conf[]="\$rcmail_config['syslog_facility'] = LOG_USER;";
				$conf[]="\$rcmail_config['smtp_log'] = true;";
				$conf[]="\$rcmail_config['log_logins'] = false;";
				$conf[]="\$rcmail_config['sql_debug'] = false;";
				$conf[]="\$rcmail_config['imap_debug'] = false;";
				$conf[]="\$rcmail_config['ldap_debug'] = false;";
				$conf[]="\$rcmail_config['smtp_debug'] = false;";
				$conf[]="\$rcmail_config['default_host'] = '{$this->roundCubeArray["default_host"]}';";
				$conf[]="\$rcmail_config['default_port'] = 143;";
				$conf[]="\$rcmail_config['imap_auth_type'] = null;";
				$conf[]="\$rcmail_config['imap_delimiter'] = null;";
				$conf[]="\$rcmail_config['imap_ns_personal'] = null;";
				$conf[]="\$rcmail_config['imap_ns_other']    = null;";
				$conf[]="\$rcmail_config['imap_ns_shared']   = null;";
				$conf[]="\$rcmail_config['imap_force_caps'] = false;";
				$conf[]="\$rcmail_config['imap_force_lsub'] = false;";
				$conf[]="\$rcmail_config['imap_timeout'] = 0;";
				$conf[]="\$rcmail_config['imap_auth_cid'] = null;";
				$conf[]="\$rcmail_config['imap_auth_pw'] = null;";
				$conf[]="\$rcmail_config['smtp_server'] = '';";
				$conf[]="\$rcmail_config['smtp_port'] = 25;";
				$conf[]="\$rcmail_config['smtp_user'] = '';";
				$conf[]="\$rcmail_config['smtp_pass'] = '';";
				$conf[]="\$rcmail_config['smtp_auth_type'] = '';";
				$conf[]="\$rcmail_config['smtp_auth_cid'] = null;";
				$conf[]="\$rcmail_config['smtp_auth_pw'] = null;";
				$conf[]="\$rcmail_config['smtp_helo_host'] = '';";
				$conf[]="\$rcmail_config['smtp_timeout'] = 0;";
				$conf[]="\$rcmail_config['enable_installer'] = false;";
				$conf[]="\$rcmail_config['log_dir'] = 'logs/';";
				$conf[]="\$rcmail_config['temp_dir'] = 'temp/';";
				$conf[]="\$rcmail_config['enable_caching'] = {$this->roundCubeArray["enable_caching"]};";
				$conf[]="\$rcmail_config['message_cache_lifetime'] = '10d';";
				$conf[]="\$rcmail_config['force_https'] = false;";
				$conf[]="\$rcmail_config['use_https'] = false;";
				$conf[]="\$rcmail_config['login_autocomplete'] = 0;";
				$conf[]="\$rcmail_config['login_lc'] = false;";
				$conf[]="\$rcmail_config['auto_create_user'] = {$this->roundCubeArray["auto_create_user"]};";
				$conf[]="\$rcmail_config['skin_include_php'] = false;";
				$conf[]="\$rcmail_config['session_lifetime'] = 10;";
				$conf[]="\$rcmail_config['ip_check'] = false;";
				$conf[]="\$rcmail_config['double_auth'] = false;";
				$conf[]="\$rcmail_config['referer_check'] = false;";
				$conf[]="\$rcmail_config['des_key'] = 'rcmail-!24ByteDESkey*Str';";
				$conf[]="\$rcmail_config['username_domain'] = '';";
				$conf[]="\$rcmail_config['mail_domain'] = '';";
				$conf[]="\$rcmail_config['password_charset'] = 'ISO-8859-1';";
				$conf[]="\$rcmail_config['sendmail_delay'] = 0;";
				$conf[]="\$rcmail_config['max_recipients'] = 0; ";
				$conf[]="\$rcmail_config['max_group_members'] = 0; ";
				$conf[]="\$rcmail_config['useragent'] = 'Roundcube Webmail/'.RCMAIL_VERSION;";
				$conf[]="\$rcmail_config['product_name'] = '{$this->roundCubeArray["product_name"]}';";
				$conf[]="\$rcmail_config['include_host_config'] = false;";
				$conf[]="\$rcmail_config['generic_message_footer'] = '';";
				$conf[]="\$rcmail_config['generic_message_footer_html'] = '';";
				$conf[]="\$rcmail_config['http_received_header'] = false;";
				$conf[]="\$rcmail_config['http_received_header_encrypt'] = false;";
				$conf[]="\$rcmail_config['mail_header_delimiter'] = NULL;";
				$conf[]="\$rcmail_config['line_length'] = 72;";
				$conf[]="\$rcmail_config['send_format_flowed'] = true;";
				$conf[]="\$rcmail_config['session_domain'] = '';";
				$conf[]="\$rcmail_config['dont_override'] = array();";
				$conf[]="\$rcmail_config['identities_level'] = 0;";
				$conf[]="\$rcmail_config['mime_magic'] = '/usr/share/misc/magic';";
				$conf[]="\$rcmail_config['email_dns_check'] = false;";
				if($this->roundCubeArray["plugin_subscriptions_option"]==1){
									$conf[]="\$rcmail_config['use_subscriptions'] = true;";
								}
				$conf[]="\$rcmail_config['plugins'] = array();";
				$conf[]="$plugins";
				
				$conf[]="\$rcmail_config['message_sort_col'] = '';";
				$conf[]="\$rcmail_config['message_sort_order'] = 'DESC';";
				$conf[]="\$rcmail_config['list_cols'] = array('subject', 'status', 'from', 'date', 'size', 'flag', 'attachment');";
				if($version_bin>=40){
					$conf[]="\$rcmail_config['language'] = '{$this->roundCubeArray["locale_string"]}';";					
				}else{
					$conf[]="\$rcmail_config['locale_string'] = '{$this->roundCubeArray["locale_string"]}';";	
				}
				$conf[]="\$rcmail_config['date_short'] = 'D H:i';";
				$conf[]="\$rcmail_config['date_long'] = 'd.m.Y H:i';";
				$conf[]="\$rcmail_config['date_today'] = 'H:i';";
				$conf[]="\$rcmail_config['drafts_mbox'] = 'Drafts';";
				$conf[]="\$rcmail_config['junk_mbox'] = 'Junk';";
				$conf[]="\$rcmail_config['sent_mbox'] = 'Sent';";
				$conf[]="\$rcmail_config['trash_mbox'] = 'Trash';";
				$conf[]="\$rcmail_config['default_imap_folders'] = array('INBOX', 'Drafts', 'Sent', 'Junk', 'Trash');";
				$conf[]="\$rcmail_config['create_default_folders'] = false;";
				$conf[]="\$rcmail_config['protect_default_folders'] = true;";
				$conf[]="\$rcmail_config['quota_zero_as_unlimited'] = false;";
				$conf[]="\$rcmail_config['enable_spellcheck'] = true;";
				$conf[]="\$rcmail_config['spellcheck_engine'] = 'googie';";
				$conf[]="\$rcmail_config['spellcheck_uri'] = '';";
				$conf[]="\$rcmail_config['spellcheck_languages'] = NULL;";
				$conf[]="\$rcmail_config['max_pagesize'] = 200;";
				$conf[]="\$rcmail_config['min_keep_alive'] = 60;";
				$conf[]="\$rcmail_config['address_book_type'] = 'sql';";
				$conf[]="\$rcmail_config['ldap_public'] = array();";
				$conf[]=$this->enable_ldap();
				$conf[]="\$rcmail_config['autocomplete_addressbooks'] = array('sql');";
				$conf[]="\$rcmail_config['autocomplete_min_length'] = 1;";
				$conf[]="\$rcmail_config['default_charset'] = 'ISO-8859-1';";
				if($version_bin>=40){
					$conf[]="\$rcmail_config['skin'] = 'skins/default/';";
				}else{
					$conf[]="\$rcmail_config['skin_path'] = 'skins/default/';\n";
				}	
				$conf[]="\$rcmail_config['pagesize'] = 40;";
				$conf[]="\$rcmail_config['timezone'] = 'auto';";
				$conf[]="\$rcmail_config['dst_active'] = (bool)date('I');";
				$conf[]="\$rcmail_config['prefer_html'] = true;";
				$conf[]="\$rcmail_config['show_images'] = 0;";
				$conf[]="\$rcmail_config['htmleditor'] = 0;";
				$conf[]="\$rcmail_config['prettydate'] = true;";
				$conf[]="\$rcmail_config['draft_autosave'] = 300;";
				$conf[]="\$rcmail_config['preview_pane'] = false;";
				$conf[]="\$rcmail_config['preview_pane_mark_read'] = 0;";
				$conf[]="\$rcmail_config['focus_on_new_message'] = true;";
				$conf[]="\$rcmail_config['logout_purge'] = false;";
				$conf[]="\$rcmail_config['logout_expunge'] = false;";
				$conf[]="\$rcmail_config['inline_images'] = true;";
				$conf[]="\$rcmail_config['mime_param_folding'] = 1;";
				$conf[]="\$rcmail_config['skip_deleted'] = {$this->roundCubeArray["skip_deleted"]};";
				$conf[]="\$rcmail_config['read_when_deleted'] = true;";
				$conf[]="\$rcmail_config['flag_for_deletion'] = {$this->roundCubeArray["flag_for_deletion"]};";
				$conf[]="\$rcmail_config['keep_alive'] = 60;";
				$conf[]="\$rcmail_config['check_all_folders'] = false;";
				$conf[]="\$rcmail_config['display_next'] = false;";
				$conf[]="\$rcmail_config['autoexpand_threads'] = 0;";
				$conf[]="\$rcmail_config['top_posting'] = false;";
				$conf[]="\$rcmail_config['strip_existing_sig'] = true;";
				$conf[]="\$rcmail_config['show_sig'] = 1;";
				$conf[]="\$rcmail_config['sig_above'] = false;";
				$conf[]="\$rcmail_config['force_7bit'] = false;";
				$conf[]="\$rcmail_config['search_mods'] = null;  ";
				$conf[]="\$rcmail_config['delete_always'] = false;";
				$conf[]="\$rcmail_config['mdn_requests'] = 0;";
				$conf[]="\$rcmail_config['mdn_default'] = 0;";
				$conf[]="\$rcmail_config['dsn_default'] = 0;";
				$conf[]="\$rcmail_config['reply_same_folder'] = false;\n";	
				return @implode("\n", $conf);			
		}
	
	
	function enable_ldap(){
		
		if($this->roundCubeArray["ldap_ok"]==1){
			$ldap=new clladp();
			$ous=$ldap->hash_get_ou(true);
			
			while (list ($ou, $line) = each ($ous)){
			
			$conf=$conf."\$rcmail_config['ldap_public']['$ou'] = array(\n";
			$conf=$conf."\t'name'          => '$ou',\n";
			$conf=$conf."\t'hosts'         => array('$ldap->ldap_host'),\n";
			$conf=$conf."\t'port'          => $ldap->ldap_port,\n";
			$conf=$conf."\t'base_dn'       => 'ou=$ou,dc=organizations,$ldap->suffix',\n";
			$conf=$conf."\t'bind_dn'       => 'cn=$ldap->ldap_admin,$ldap->suffix',\n";
			$conf=$conf."\t'bind_pass'     => '$ldap->ldap_password',\n";
			$conf=$conf."\t'ldap_version'  => 3,       // using LDAPv3\n"; 
			$conf=$conf."\t'search_fields' => array('mail', 'cn','uid','givenName','DisplayName'),  // fields to search in\n";
			$conf=$conf."\t'name_field'    => 'cn',    // this field represents the contact's name\n";
			$conf=$conf."\t'email_field'   => 'mail',  // this field represents the contact's e-mail\n";
			$conf=$conf."\t'surname_field' => 'sn',    // this field represents the contact's last name\n";
			$conf=$conf."\t'firstname_field' => 'gn',  // this field represents the contact's first name\n";
			$conf=$conf."\t'scope'         => 'sub',   // search mode: sub|base|list\n";
			$conf=$conf."\t'LDAP_Object_Classes' => array( 'person', 'inetOrgPerson', 'userAccount'),\n";
			$conf=$conf."\t'filter'        => 'givenName=*',      // used for basic listing (if not empty) and will be &'d with search queries. ex: (status=act)\n";
			$conf=$conf."\t'fuzzy_search'  => true);   // server allows wildcard search\n";
			
			
			
			}
		return $conf;	
		}
		
		
		
	}
	
		
		
	}

?>