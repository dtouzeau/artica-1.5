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
				$conf=$conf."\t\t\"socket\" => \"/var/run/lighttpd/php.socket\",\n";
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
				if(!is_file("$users->roundcube_web_folder/plugins/remember_me/remember_me.php")){$this->plugin_install($users->roundcube_web_folder,"remember_me");}
				if(!is_file("$users->roundcube_web_folder/plugins/sticky_notes/sticky_notes.php")){$this->plugin_install($users->roundcube_web_folder,"sticky_notes");}
				if(!is_file("$users->roundcube_web_folder/plugins/jqueryui/jqueryui.php")){$this->plugin_install($users->roundcube_web_folder,"jqueryui");}
				if(!is_file("$users->roundcube_web_folder/plugins/dkimstatus/dkimstatus.php")){$this->plugin_install($users->roundcube_web_folder,"dkimstatus");}
				if(!is_file("$users->roundcube_web_folder/plugins/fail2ban/fail2ban.php")){$this->plugin_install($users->roundcube_web_folder,"fail2ban");}
			
				
				
				
				if(is_file("$users->roundcube_web_folder/plugins/msglistcols/msglistcols.php")){
					echo "Starting......: Roundcube msglistcols Enabled\n";
					$arr[]="'msglistcols'";
				}
				
				if(is_file("$users->roundcube_web_folder/plugins/remember_me/remember_me.php")){
					echo "Starting......: Roundcube remember_me Enabled\n";
					$arr[]="'remember_me'";
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
				
			//if($this->AsRoot){echo "Starting......: Roundcube contextmenu Enabled\n";}
			//$arr[]="'contextmenu'";
			//$sock->getFrameWork("cmd.php?roundcube-install-contextmenu=yes");
			
				if(is_array($arr)){$plugins="\$rcmail_config['plugins'] = array(".implode(',',$arr).");\n";}
				
				//- 'skin_path' was replaced by 'skin'
				//- 'locale_string' was replaced by 'language'
				
				$version_bin=$users->roundcube_intversion;
				
				$conf=$conf."<?php\n";
				$conf=$conf."\n";
				$conf=$conf."/*\n";
				$conf=$conf." +-----------------------------------------------------------------------+\n";
				$conf=$conf." | Main configuration file                                               |\n";
				$conf=$conf." | Saved by artica On ".date('Y-m-d H:i:s')."                     |\n";
				$conf=$conf." | This file is part of the RoundCube Webmail client                     |\n";
				$conf=$conf." | Copyright (C) 2005-2007, RoundCube Dev. - Switzerland                 |\n";
				$conf=$conf." | Licensed under the GNU GPL                                            |\n";
				$conf=$conf." |                                                                       |\n";
				$conf=$conf." +-----------------------------------------------------------------------+\n";
				$conf=$conf."\n";
				$conf=$conf."*/\n";
				$conf=$conf."\n";
				$conf=$conf."\$rcmail_config = array();\n";
				$conf=$conf."\n";
				$conf=$conf."\n";
				$conf=$conf."// system error reporting: 1 = log; 2 = report (not implemented yet), 4 = show, 8 = trace\n";
				$conf=$conf."\$rcmail_config['debug_level'] ={$this->roundCubeArray["debug_level"]};\n";
				$conf=$conf."\$rcmail_config['log_driver'] ='syslog';\n";
				$conf=$conf."\$rcmail_config['syslog_id'] = 'roundcube-master';\n";
				$conf=$conf."\n";
				$conf=$conf."// enable caching of messages and mailbox data in the local database.\n";
				$conf=$conf."// this is recommended if the IMAP server does not run on the same machine\n";
				$conf=$conf."\$rcmail_config['enable_caching'] = {$this->roundCubeArray["enable_caching"]};\n";
				$conf=$conf."\n";
				$conf=$conf."// lifetime of message cache\n";
				$conf=$conf."// possible units: s, m, h, d, w\n";
				$conf=$conf."\$rcmail_config['message_cache_lifetime'] = '10d';\n";
				$conf=$conf."\n";
				$conf=$conf."// automatically create a new RoundCube user when log-in the first time.\n";
				$conf=$conf."// a new user will be created once the IMAP login succeeds.\n";
				$conf=$conf."// set to false if only registered users can use this service\n";
				$conf=$conf."\$rcmail_config['auto_create_user'] = {$this->roundCubeArray["enable_caching"]};\n";
				$conf=$conf."\n";
				$conf=$conf."// the mail host chosen to perform the log-in\n";
				$conf=$conf."// leave blank to show a textbox at login, give a list of hosts\n";
				$conf=$conf."// to display a pulldown menu or set one host as string.\n";
				$conf=$conf."// To use SSL connection, enter ssl://hostname:993\n";
				$conf=$conf."\$rcmail_config['default_host'] = '{$this->roundCubeArray["default_host"]}';\n";
				$conf=$conf."\n";
				$conf=$conf."// TCP port used for IMAP connections\n";
				$conf=$conf."\$rcmail_config['default_port'] = 143;\n";
				$conf=$conf."\n";
				$conf=$conf."// Automatically add this domain to user names for login\n";
				$conf=$conf."// Only for IMAP servers that require full e-mail addresses for login\n";
				$conf=$conf."// Specify an array with 'host' => 'domain' values to support multiple hosts\n";
				$conf=$conf."\$rcmail_config['username_domain'] = '';\n";
				$conf=$conf."\n";
				$conf=$conf."// This domain will be used to form e-mail addresses of new users\n";
				$conf=$conf."// Specify an array with 'host' => 'domain' values to support multiple hosts\n";
				$conf=$conf."\$rcmail_config['mail_domain'] = '';\n";
				$conf=$conf."\n";
				$conf=$conf."// Path to a virtuser table file to resolve user names and e-mail addresses\n";
				$conf=$conf."\$rcmail_config['virtuser_file'] = '';\n";
				$conf=$conf."\n";
				$conf=$conf."// Query to resolve user names and e-mail addresses from the database\n";
				$conf=$conf."// %u will be replaced with the current username for login.\n";
				$conf=$conf."// The query should select the user's e-mail address as first col\n";
				$conf=$conf."\$rcmail_config['virtuser_query'] = '';\n";
				$conf=$conf."\n";
				$conf=$conf."// use this host for sending mails.\n";
				$conf=$conf."// to use SSL connection, set ssl://smtp.host.com\n";
				$conf=$conf."// if left blank, the PHP mail() function is used\n";
				$conf=$conf."\$rcmail_config['smtp_server'] = '127.0.0.1';\n";
				$conf=$conf."\n";
				$conf=$conf."// SMTP port (default is 25; 465 for SSL)\n";
				$conf=$conf."\$rcmail_config['smtp_port'] = 25;\n";
				$conf=$conf."\n";
				$conf=$conf."// SMTP username (if required) if you use %u as the username RoundCube\n";
				$conf=$conf."// will use the current username for login\n";
				$conf=$conf."\$rcmail_config['smtp_user'] = '';\n";
				$conf=$conf."\n";
				$conf=$conf."// SMTP password (if required) if you use %p as the password RoundCube\n";
				$conf=$conf."// will use the current user's password for login\n";
				$conf=$conf."\$rcmail_config['smtp_pass'] = '';\n";
				$conf=$conf."\n";
				$conf=$conf."// SMTP AUTH type (DIGEST-MD5, CRAM-MD5, LOGIN, PLAIN or empty to use\n";
				$conf=$conf."// best server supported one)\n";
				$conf=$conf."\$rcmail_config['smtp_auth_type'] = '';\n";
				$conf=$conf."\n";
				$conf=$conf."// SMTP HELO host \n";
				$conf=$conf."// Hostname to give to the remote server for SMTP 'HELO' or 'EHLO' messages \n";
				$conf=$conf."// Leave this blank and you will get the server variable 'server_name' or \n";
				$conf=$conf."// localhost if that isn't defined. \n";
				$conf=$conf."\$rcmail_config['smtp_helo_host'] = '';\n";
				$conf=$conf."\n";
				$conf=$conf."// Log sent messages\n";
				$conf=$conf."\$rcmail_config['smtp_log'] = TRUE;\n";
				$conf=$conf."\n";
				$conf=$conf."// these cols are shown in the message list\n";
				$conf=$conf."// available cols are: subject, from, to, cc, replyto, date, size, encoding\n";
				$conf=$conf."\$rcmail_config['list_cols'] = array('subject', 'from', 'date', 'size');\n";
				$conf=$conf."\n";

				$conf=$conf."\n";
				
				if($version_bin>=40){
					$conf=$conf."// relative path to the skin folder\n";
					$conf=$conf."\$rcmail_config['skin'] = 'skins/default/';\n";
				}else{
					$conf=$conf."// relative path to the skin folder\n";
					$conf=$conf."\$rcmail_config['skin_path'] = 'skins/default/';\n";
				}				
				
				
				$conf=$conf."// includes should be interpreted as PHP files\n";
				$conf=$conf."\$rcmail_config['skin_include_php'] = FALSE;\n";
				$conf=$conf."\n";
				$conf=$conf."// use this folder to store temp files (must be writebale for apache user)\n";
				$conf=$conf."\$rcmail_config['temp_dir'] = 'temp/';\n";
				$conf=$conf."\n";
				$conf=$conf."// use this folder to store log files (must be writebale for apache user)\n";
				$conf=$conf."\$rcmail_config['log_dir'] = 'logs/';\n";
				$conf=$conf."\n";
				$conf=$conf."// session lifetime in minutes\n";
				$conf=$conf."\$rcmail_config['session_lifetime'] = 10;\n";
				$conf=$conf."\n";
				$conf=$conf."// check client IP in session athorization\n";
				$conf=$conf."\$rcmail_config['ip_check'] = false;\n";
				$conf=$conf."\n";
				$conf=$conf."// Use an additional frequently changing cookie to athenticate user sessions.\n";
				$conf=$conf."// There have been problems reported with this feature.\n";
				$conf=$conf."\$rcmail_config['double_auth'] = false;\n";
				$conf=$conf."\n";
				$conf=$conf."// this key is used to encrypt the users imap password which is stored\n";
				$conf=$conf."// in the session record (and the client cookie if remember password is enabled).\n";
				$conf=$conf."// please provide a string of exactly 24 chars.\n";
				$conf=$conf."\$rcmail_config['des_key'] = 'NIbXC7RaFsZvQTV5NWBbQd9H';\n";
				$conf=$conf."\n";
				
				if($version_bin>=40){
					$conf=$conf."// the default locale setting\n";
					$conf=$conf."\$rcmail_config['language'] = '{$this->roundCubeArray["locale_string"]}';\n";					
				}else{
					$conf=$conf."// the default locale setting\n";
					$conf=$conf."\$rcmail_config['locale_string'] = '{$this->roundCubeArray["locale_string"]}';\n";	
				}
				$conf=$conf."\n";
				$conf=$conf."// use this format for short date display\n";
				$conf=$conf."\$rcmail_config['date_short'] = 'D H:i';\n";
				$conf=$conf."\n";
				$conf=$conf."// use this format for detailed date/time formatting\n";
				$conf=$conf."\$rcmail_config['date_long'] = 'd.m.Y H:i';\n";
				$conf=$conf."\n";
				$conf=$conf."// use this format for today's date display\n";
				$conf=$conf."\$rcmail_config['date_today'] = 'H:i';\n";
				$conf=$conf."\n";
				$conf=$conf."// add this user-agent to message headers when sending\n";
				$conf=$conf."\$rcmail_config['useragent'] = 'RoundCube Webmail/0.1-rc2';\n";
				$conf=$conf."\n";
				$conf=$conf."// use this name to compose page titles\n";
				$conf=$conf."\$rcmail_config['product_name'] = '{$this->roundCubeArray["product_name"]}';\n";
				$conf=$conf."\n";
				$conf=$conf."// only list folders within this path\n";
				$conf=$conf."\$rcmail_config['imap_root'] = null;\n";
				$conf=$conf."\n";
				$conf=$conf."// store draft message is this mailbox\n";
				$conf=$conf."// leave blank if draft messages should not be stored\n";
				$conf=$conf."\$rcmail_config['drafts_mbox'] = 'Drafts';\n";
				$conf=$conf."\n";
				$conf=$conf."// store spam messages in this mailbox\n";
				$conf=$conf."\$rcmail_config['junk_mbox'] = 'Junk';\n";
				$conf=$conf."\n";
				$conf=$conf."// store sent message is this mailbox\n";
				$conf=$conf."// leave blank if sent messages should not be stored\n";
				$conf=$conf."\$rcmail_config['sent_mbox'] = 'Sent';\n";
				$conf=$conf."\n";
				$conf=$conf."// move messages to this folder when deleting them\n";
				$conf=$conf."// leave blank if they should be deleted directly\n";
				$conf=$conf."\$rcmail_config['trash_mbox'] = 'Trash';\n";
				$conf=$conf."\n";
				$conf=$conf."// display these folders separately in the mailbox list.\n";
				$conf=$conf."// these folders will automatically be created if they do not exist\n";
				$conf=$conf."\$rcmail_config['default_imap_folders'] = array('INBOX', 'Drafts', 'Sent', 'Junk', 'Trash');\n";
				$conf=$conf."\n";
				$conf=$conf."// protect the default folders from renames, deletes, and subscription changes\n";
				$conf=$conf."\$rcmail_config['protect_default_folders'] = TRUE;\n";
				$conf=$conf."\n";
				$conf=$conf."// Set TRUE if deleted messages should not be displayed\n";
				$conf=$conf."// This will make the application run slower\n";
				$conf=$conf."\$rcmail_config['skip_deleted'] = {$this->roundCubeArray["skip_deleted"]};\n";
				$conf=$conf."\n";
				$conf=$conf."// Set true to Mark deleted messages as read as well as deleted\n";
				$conf=$conf."// False means that a message's read status is not affected by marking it as deleted\n";
				$conf=$conf."\$rcmail_config['read_when_deleted'] = TRUE;\n";
				$conf=$conf."\n";
				$conf=$conf."// When a Trash folder is not present and a message is deleted, flag \n";
				$conf=$conf."// the message for deletion rather than deleting it immediately.  Setting this to \n";
				$conf=$conf."// false causes deleted messages to be permanantly removed if there is no Trash folder\n";
				$conf=$conf."\$rcmail_config['flag_for_deletion'] = {$this->roundCubeArray["flag_for_deletion"]};\n";
				$conf=$conf."\n";
				$conf=$conf."// Make use of the built-in spell checker. It is based on GoogieSpell.\n";
				$conf=$conf."// Since Google only accepts connections over https your PHP installatation\n";
				$conf=$conf."// requires to be compiled with Open SSL support\n";
				$conf=$conf."\$rcmail_config['enable_spellcheck'] = TRUE;\n";
				$conf=$conf."\n";
				$conf=$conf."// For a locally installed Nox Spell Server, please specify the URI to call it.\n";
				$conf=$conf."// Get Nox Spell Server from http://orangoo.com/labs/?page_id=72\n";
				$conf=$conf."// Leave empty to use the Google spell checking service, what means\n";
				$conf=$conf."// that the message content will be sent to Google in order to check spelling\n";
				$conf=$conf."\$rcmail_config['spellcheck_uri'] = '';\n";
				$conf=$conf."\n";
				$conf=$conf."// These languages can be selected for spell checking.\n";
				$conf=$conf."// Configure as a PHP style hash array: array('en'=>'English', 'de'=>'Deutsch');\n";
				$conf=$conf."// Leave empty for default set of Google spell check languages\n";
				$conf=$conf."\$rcmail_config['spellcheck_languages'] = NULL;\n";
				$conf=$conf."\n";
				$conf=$conf."// path to a text file which will be added to each sent message\n";
				$conf=$conf."// paths are relative to the RoundCube root folder\n";
				$conf=$conf."\$rcmail_config['generic_message_footer'] = '';\n";
				$conf=$conf."\n";
				$conf=$conf."// this string is used as a delimiter for message headers when sending\n";
				$conf=$conf."// leave empty for auto-detection\n";
				$conf=$conf."\$rcmail_config['mail_header_delimiter'] = NULL;\n";
				$conf=$conf."\n";
				$conf=$conf . $this->enable_ldap()."\n";
				$conf=$conf."// in order to enable public ldap search, create a config array\n";
				$conf=$conf."// like the Verisign example below. if you would like to test, \n";
				$conf=$conf."// simply uncomment the Verisign example.\n";
				$conf=$conf."/** \n";
				$conf=$conf." * example config for Verisign directory\n";
				$conf=$conf." *\n";
				$conf=$conf." * \$rcmail_config['ldap_public']['Verisign'] = array(\n";
				$conf=$conf." *  'name'          => 'Verisign.com',\n";
				$conf=$conf." *  'hosts'         => array('directory.verisign.com'),\n";
				$conf=$conf." *  'port'          => 389,\n";
				$conf=$conf." *  'base_dn'       => '',\n";
				$conf=$conf." *  'bind_dn'       => '',\n";
				$conf=$conf." *  'bind_pass'     => '',\n";
				$conf=$conf." *  'ldap_version'  => 3,       // using LDAPv3\n";
				$conf=$conf." *  'search_fields' => array('mail', 'cn'),  // fields to search in\n";
				$conf=$conf." *  'name_field'    => 'cn',    // this field represents the contact's name\n";
				$conf=$conf." *  'email_field'   => 'mail',  // this field represents the contact's e-mail\n";
				$conf=$conf." *  'surname_field' => 'sn',    // this field represents the contact's last name\n";
				$conf=$conf." *  'firstname_field' => 'gn',  // this field represents the contact's first name\n";
				$conf=$conf." *  'scope'         => 'sub',   // search mode: sub|base|list\n";
				$conf=$conf." *  'filter'        => '',      // used for basic listing (if not empty) and will be &'d with search queries. ex: (status=act)\n";
				$conf=$conf." *  'fuzzy_search'  => true);   // server allows wildcard search\n";
				$conf=$conf." */\n";
				$conf=$conf."\n";
				$conf=$conf."// enable composing html formatted messages (experimental)\n";
				$conf=$conf."\$rcmail_config['enable_htmleditor'] = TRUE;\n";
				$conf=$conf."\n";
				$conf=$conf."// don't allow these settings to be overriden by the user\n";
				$conf=$conf."\$rcmail_config['dont_override'] =array('index_sort','trash_mbox','sent_mbox','junk_mbox','drafts_mbox','use_subscriptions');\n";
				
				
				
				if($this->roundCubeArray["plugin_subscriptions_option"]==1){
					$conf=$conf."\$rcmail_config['use_subscriptions'] = true;\n";
				}
				
				$conf=$conf."\n";
				$conf=$conf."// list of configuration option names that need to be available in Javascript.\n";
				$conf=$conf."\$rcmail_config['javascript_config'] = array('read_when_deleted', 'flag_for_deletion');\n";
				$conf=$conf."\n";
				$conf=$conf."// try to load host-specific configuration\n";
				$conf=$conf."\$rcmail_config['include_host_config'] = FALSE;\n";
				$conf=$conf."\n";
				$conf=$conf."\n";
				$conf=$conf."/***** these settings can be overwritten by user's preferences *****/\n";
				$conf=$conf."\n";
				$conf=$conf."// show up to X items in list view\n";
				$conf=$conf."\$rcmail_config['pagesize'] = 40;\n";
				$conf=$conf."\n";
				$conf=$conf."// use this timezone to display date/time\n";
				$conf=$conf."\$rcmail_config['timezone'] = intval(date('O'))/100 - date('I');\n";
				$conf=$conf."\n";
				$conf=$conf."// is daylight saving On?\n";
				$conf=$conf."\$rcmail_config['dst_active'] = (bool)date('I');\n";
				$conf=$conf."\n";
				$conf=$conf."// prefer displaying HTML messages\n";
				$conf=$conf."\$rcmail_config['prefer_html'] = TRUE;\n";
				$conf=$conf."\n";
				$conf=$conf."// show pretty dates as standard\n";
				$conf=$conf."\$rcmail_config['prettydate'] = TRUE;\n";
				$conf=$conf."\n";
				$conf=$conf."// default sort col\n";
				$conf=$conf."\$rcmail_config['message_sort_col'] = 'date';\n";
				$conf=$conf."\n";
				$conf=$conf."// default sort order\n";
				$conf=$conf."\$rcmail_config['message_sort_order'] = 'DESC';\n";
				$conf=$conf."\n";
				$conf=$conf."// save compose message every 300 seconds (5min)\n";
				$conf=$conf."\$rcmail_config['draft_autosave'] = 300;\n";
				$conf=$conf."\n";
				
				$conf=$conf."/***** PLUGINS for Roundcube V3 *****/\n";
				$conf=$conf."\$rcmail_config['plugins'] = array();\n";
				$conf=$conf.$plugins."\n\n";
				
				
				$conf=$conf."// don't let users set pagesize to more than this value if set\n";
				$conf=$conf."\$rcmail_config['max_pagesize'] = 200;\n";
				$conf=$conf."\$rcmail_config['create_default_folders'] = TRUE;\n";
				$conf=$conf."\n";
				$conf=$conf."\n";				
				$conf=$conf."// end of config file\n";
				$conf=$conf."?>";	
				return $conf;	
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