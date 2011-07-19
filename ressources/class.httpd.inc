<?php
include_once(dirname(__FILE__) . '/class.templates.inc');
include_once(dirname(__FILE__) . '/class.artica.inc');
include_once(dirname(__FILE__) . '/class.sockets.inc');


class httpd{
	
	
	
	var $ApacheConfig;
	var $lighttpConfig;
	var $ApacheArticaEnabled;
	var $https_port;
	var $lighttp_min_proc;
	var $lighttp_max_proc;
	var $lighttp_max_load_per_proc;
	var $PHP_FCGI_CHILDREN;
	var $PHP_FCGI_MAX_REQUESTS;
	var $LighttpdUseLdap;
	var $LighttpdUserAndGroup;
	
	
	function httpd(){
		$artica=new artica_general();
		$sock=new sockets();
		
		$this->ApacheArticaEnabled=$artica->ApacheArticaEnabled;
		$this->lighttpConfig=$artica->lighttpConfig;
		$this->ApacheConfig=$artica->ApacheConfig;
		$this->LighttpdUseLdap=$artica->LighttpdUseLdap;
		$sock->DeleteCache();
		$this->LighttpdUserAndGroup=$sock->GET_INFO('LighttpdUserAndGroup');
		if($this->LighttpdUserAndGroup==null){
			$sock->SET_INFO('LighttpdUserAndGroup',"www-data:www-data");
			$this->LighttpdUserAndGroup="www-data:www-data";
		}
		$this->ParseConfig();
		$this->Parse_lighttpd();
		
	}
	
	
	function ParseConfig(){
		$conf=$this->lighttpConfig;
		if($this->lighttpConfig==null){
			$this->lighttpConfig=$this->Build_lighthttp();
		}
		
		$tbl=explode("\n",$this->lighttpConfig);
		while (list ($num, $val) = each ($tbl) ){
			if(preg_match('#server\.port\s+=([\s0-9]+)#',$val,$re)){
				$this->https_port=trim($re[1]);
			}
			
		}
		
		if($this->https_port==null){
			writelogs("unable to preg match ssl port in " . strlen($this->lighttpConfig) . ' bytes ' .count($tbl) . " lines" ,__CLASS__.'/'.__FUNCTION__,__FILE__);
		}
		
		
		
	}
	
	
	function SaveToServer(){
		$artica=new artica_general();
		$artica->ApacheArticaEnabled=$this->ApacheArticaEnabled;
		$artica->lighttpConfig=$this->Build_lighthttp();
		$artica->ApacheConfig=$this->BuildHttpdConf();
		$artica->LighttpdUseLdap=$this->LighttpdUseLdap;
		$artica->Save();
		$artica->SaveMysqlSettings();
		$sock=new sockets();
		$sock->SET_INFO('LighttpdUserAndGroup',$this->LighttpdUserAndGroup);
		$sock->SaveConfigFile($artica->ApacheConfig,'httpdCONF');
		
		
		$sock->DeleteCache();
		$tpl=new templates();
		if($artica->LighttpdUseLdap==1){
			$infos="Auth mode: PopUp\n";
		}else{$infos="Auth mode: Form\n";}
		
		echo $tpl->javascript_parse_text("{success}\n$infos{interface_restarted}\n");
		$sock=new sockets();
		$sock->getFrameWork('cmd.php?restart-http-engine=yes');
		
		
		
	}
	
	
	function Parse_lighttpd(){
		$tbl=explode("\n",$this->lighttpConfig);
		while (list ($num, $line) = each ($tbl)){
			if(preg_match('#min-procs.+?([0-9]+)#',$line,$re)){$this->lighttp_min_proc=$re[1];}
			if(preg_match('#max-procs.+?([0-9]+)#',$line,$re)){$this->lighttp_max_proc=$re[1];}
			if(preg_match('#max-load-per-proc.+?([0-9]+)#',$line,$re)){$this->lighttp_max_load_per_proc=$re[1];}
			if(preg_match('#PHP_FCGI_CHILDREN.+?([0-9]+)#',$line,$re)){$this->PHP_FCGI_CHILDREN=$re[1];}
			if(preg_match('#PHP_FCGI_MAX_REQUESTS.+?([0-9]+)#',$line,$re)){$this->PHP_FCGI_MAX_REQUESTS=$re[1];}
			
			
			
			
		}
		
		
	}
	
	
	function Build_lighthttp(){
				$users=new usersMenus();
				if($users->roundcube_installed){
					if(strlen($users->roundcube_folder)>5){
						$alias_roundcube="alias.url += (	\"/webmail\" 			 => \"$users->roundcube_folder\")\n";
						$alias_roundcube=$alias_roundcube.'$'."HTTP[\"url\"] =~ \"^/webmail\" {\n";
  						$alias_roundcube=$alias_roundcube."\tserver.follow-symlink = \"enable\"\n";
						$alias_roundcube=$alias_roundcube."}\n";
						$alias_roundcube=$alias_roundcube.'$'."HTTP[\"url\"] =~ \"^/webmail/config|/webmail/temp|/webmail/logs\" { url.access-deny = ( \"\" )}\n";
					}
				}
		
		
				if($this->https_port==null){$this->https_port=9000;}
				if($this->lighttp_min_proc==null){$this->lighttp_min_proc=1;}
				if(trim($this->lighttp_max_proc==null)){$this->lighttp_max_proc=2;}
				if($this->lighttp_max_load_per_proc==null){$this->lighttp_max_load_per_proc=2;}
				if($this->lighttp_max_load_per_proc==null){$this->lighttp_max_load_per_proc=2;}
				if($this->PHP_FCGI_CHILDREN==null){$this->PHP_FCGI_CHILDREN=4;}
				if($this->PHP_FCGI_MAX_REQUESTS==null){$this->PHP_FCGI_MAX_REQUESTS=100;}
				$ldap=new clladp();
				
				if($this->LighttpdUseLdap==1){
					writelogs("Building auth backend...",__CLASS__.'/'.__FUNCTION__,__FILE__);
					$l=$l."auth.backend               = \"ldap\"\n";
					$l=$l."auth.backend.ldap.hostname = \"$ldap->ldap_host:$ldap->ldap_port\"\n";
					$l=$l."auth.backend.ldap.base-dn  = \"$ldap->suffix\"\n";
					$l=$l."auth.backend.ldap.filter   = \"(&(objectClass=UserArticaClass)(ArticaInterfaceLogon=$))\"\n";
					$l=$l."auth.backend.ldap.bind-dn  = \"cn=$ldap->ldap_admin,$ldap->suffix\"\n";
					$l=$l."auth.backend.ldap.bind-pw  = \"$ldap->ldap_password\"\n";
					$l=$l."\n";
					$l=$l."auth.require               = (\"/\" =>\n";
					$l=$l."                                (\n";
					$l=$l."                                  \"method\"  => \"basic\",\n";
					$l=$l."                                  \"realm\"   => \"artica-postfix interface\",\n";
					$l=$l."                                  \"require\" => \"valid-user\"\n";
					$l=$l."                                ),\n";
					$l=$l."                              )\n\n";
				}
				
				preg_match('#(.+?):(.+)#',$this->LighttpdUserAndGroup,$username);
				$username[0]=trim($username[0]);
				$username[1]=trim($username[1]);
				
				$conf=$conf."#artica-postfix saved by artica lighttpd.conf\n\n";
				$conf=$conf."server.modules = (\n";
				$conf=$conf."        \"mod_alias\",\n";
				$conf=$conf."        \"mod_access\",\n";
				$conf=$conf."        \"mod_accesslog\",\n";
				$conf=$conf."        \"mod_compress\",\n";
				$conf=$conf."        \"mod_fastcgi\",\n";
				$conf=$conf."        \"mod_cgi\",\n";
				$conf=$conf."		 \"mod_status\",\n";
				$conf=$conf."		 \"mod_auth\"\n";				
				$conf=$conf.")\n";
				$conf=$conf."\n$l";
				$conf=$conf."server.document-root        = \"/usr/share/artica-postfix\"\n";
				$conf=$conf."server.username = \"{$username[0]}\"\n";
				$conf=$conf."server.groupname = \"{$username[1]}\"\n";
				$conf=$conf."server.errorlog             = \"/var/log/lighttpd/error.log\"\n";
				$conf=$conf."index-file.names            = ( \"index.php\")\n";
				$conf=$conf."\n";
				$conf=$conf."mimetype.assign             = (\n";
				$conf=$conf."  \".pdf\"          =>      \"application/pdf\",\n";
				$conf=$conf."  \".sig\"          =>      \"application/pgp-signature\",\n";
				$conf=$conf."  \".spl\"          =>      \"application/futuresplash\",\n";
				$conf=$conf."  \".class\"        =>      \"application/octet-stream\",\n";
				$conf=$conf."  \".ps\"           =>      \"application/postscript\",\n";
				$conf=$conf."  \".torrent\"      =>      \"application/x-bittorrent\",\n";
				$conf=$conf."  \".dvi\"          =>      \"application/x-dvi\",\n";
				$conf=$conf."  \".gz\"           =>      \"application/x-gzip\",\n";
				$conf=$conf."  \".pac\"          =>      \"application/x-ns-proxy-autoconfig\",\n";
				$conf=$conf."  \".swf\"          =>      \"application/x-shockwave-flash\",\n";
				$conf=$conf."  \".tar.gz\"       =>      \"application/x-tgz\",\n";
				$conf=$conf."  \".tgz\"          =>      \"application/x-tgz\",\n";
				$conf=$conf."  \".tar\"          =>      \"application/x-tar\",\n";
				$conf=$conf."  \".zip\"          =>      \"application/zip\",\n";
				$conf=$conf."  \".mp3\"          =>      \"audio/mpeg\",\n";
				$conf=$conf."  \".m3u\"          =>      \"audio/x-mpegurl\",\n";
				$conf=$conf."  \".wma\"          =>      \"audio/x-ms-wma\",\n";
				$conf=$conf."  \".wax\"          =>      \"audio/x-ms-wax\",\n";
				$conf=$conf."  \".ogg\"          =>      \"application/ogg\",\n";
				$conf=$conf."  \".wav\"          =>      \"audio/x-wav\",\n";
				$conf=$conf."  \".gif\"          =>      \"image/gif\",\n";
				$conf=$conf."  \".jar\"          =>      \"application/x-java-archive\",\n";
				$conf=$conf."  \".jpg\"          =>      \"image/jpeg\",\n";
				$conf=$conf."  \".jpeg\"         =>      \"image/jpeg\",\n";
				$conf=$conf."  \".png\"          =>      \"image/png\",\n";
				$conf=$conf."  \".xbm\"          =>      \"image/x-xbitmap\",\n";
				$conf=$conf."  \".xpm\"          =>      \"image/x-xpixmap\",\n";
				$conf=$conf."  \".xwd\"          =>      \"image/x-xwindowdump\",\n";
				$conf=$conf."  \".css\"          =>      \"text/css\",\n";
				$conf=$conf."  \".html\"         =>      \"text/html\",\n";
				$conf=$conf."  \".htm\"          =>      \"text/html\",\n";
				$conf=$conf."  \".js\"           =>      \"text/javascript\",\n";
				$conf=$conf."  \".asc\"          =>      \"text/plain\",\n";
				$conf=$conf."  \".c\"            =>      \"text/plain\",\n";
				$conf=$conf."  \".cpp\"          =>      \"text/plain\",\n";
				$conf=$conf."  \".log\"          =>      \"text/plain\",\n";
				$conf=$conf."  \".conf\"         =>      \"text/plain\",\n";
				$conf=$conf."  \".text\"         =>      \"text/plain\",\n";
				$conf=$conf."  \".txt\"          =>      \"text/plain\",\n";
				$conf=$conf."  \".dtd\"          =>      \"text/xml\",\n";
				$conf=$conf."  \".xml\"          =>      \"text/xml\",\n";
				$conf=$conf."  \".mpeg\"         =>      \"video/mpeg\",\n";
				$conf=$conf."  \".mpg\"          =>      \"video/mpeg\",\n";
				$conf=$conf."  \".mov\"          =>      \"video/quicktime\",\n";
				$conf=$conf."  \".qt\"           =>      \"video/quicktime\",\n";
				$conf=$conf."  \".avi\"          =>      \"video/x-msvideo\",\n";
				$conf=$conf."  \".asf\"          =>      \"video/x-ms-asf\",\n";
				$conf=$conf."  \".asx\"          =>      \"video/x-ms-asf\",\n";
				$conf=$conf."  \".wmv\"          =>      \"video/x-ms-wmv\",\n";
				$conf=$conf."  \".bz2\"          =>      \"application/x-bzip\",\n";
				$conf=$conf."  \".tbz\"          =>      \"application/x-bzip-compressed-tar\",\n";
				$conf=$conf."  \".tar.bz2\"      =>      \"application/x-bzip-compressed-tar\",\n";
				$conf=$conf."  \"\"              =>      \"application/octet-stream\",\n";
				$conf=$conf." )\n";
				$conf=$conf."\n";
				$conf=$conf."\n";
				$conf=$conf."accesslog.filename          = \"/var/log/lighttpd/access.log\"\n";
				$conf=$conf."url.access-deny             = ( \"~\", \".inc\" )\n";
				$conf=$conf."\n";
				$conf=$conf."static-file.exclude-extensions = ( \".php\", \".pl\", \".fcgi\" )\n";
				$conf=$conf."server.port                 = $this->https_port\n";
				$conf=$conf."#server.bind                = \"127.0.0.1\"\n";
				$conf=$conf."#server.error-handler-404   = \"/error-handler.html\"\n";
				$conf=$conf."#server.error-handler-404   = \"/error-handler.php\"\n";
				$conf=$conf."server.pid-file             = \"/var/run/lighttpd/lighttpd.pid\"\n";
				$conf=$conf."server.max-fds 		    = 2048\n";
				$conf=$conf."\n";
				$conf=$conf."fastcgi.server = ( \".php\" =>((\n";
				$conf=$conf."                \"bin-path\" => \"$users->phpcgi\",\n";
				$conf=$conf."                \"socket\" => \"/var/run/lighttpd/php.socket\",\n";
				$conf=$conf."		\"min-procs\" => 1,\n";
				$conf=$conf."                \"max-procs\" => $this->lighttp_max_proc,\n";
				$conf=$conf."		\"max-load-per-proc\" => $this->lighttp_max_load_per_proc,\n";
				$conf=$conf."                \"idle-timeout\" => 10,\n";
				$conf=$conf."                \"bin-environment\" => (\n";
				$conf=$conf."                        \"PHP_FCGI_CHILDREN\" => \"$this->PHP_FCGI_CHILDREN\",\n";
				$conf=$conf."                        \"PHP_FCGI_MAX_REQUESTS\" => \"$this->PHP_FCGI_MAX_REQUESTS\"\n";
				$conf=$conf."                ),\n";
				$conf=$conf."                \"bin-copy-environment\" => (\n";
				$conf=$conf."                        \"PATH\", \"SHELL\", \"USER\"\n";
				$conf=$conf."                ),\n";
				$conf=$conf."                \"broken-scriptfilename\" => \"enable\"\n";
				$conf=$conf."        ))\n";
				$conf=$conf.")\n";
				$conf=$conf."ssl.engine                 = \"enable\"\n";
				$conf=$conf."ssl.pemfile                = \"/opt/artica/ssl/certs/lighttpd.pem\"\n";
				$conf=$conf."status.status-url          = \"/server-status\"\n";
				$conf=$conf."status.config-url          = \"/server-config\"\n";				
				$conf=$conf.$alias_roundcube;
				$conf=$conf."alias.url +=(\"/monitorix\"  => \"/var/www/monitorix/\")\n";
				$conf=$conf."alias.url += (\"/blocked_attachments\"=> \"/var/spool/artica-filter/bightml\")\n";
				
				if(strlen($users->awstats_www_path)>0){
					$conf=$conf."alias.url += (\"/awstats\"=> \"$users->awstats_www_path\")\n";
				}
				if($users->MAILMAN_INSTALLED){
					$conf=$conf."alias.url += (\"/pipermail/\" => \"/var/lib/mailman/archives/public/\")\n";
				}
				
				$conf=$conf."alias.url += ( \"/cgi-bin/\" => \"/usr/lib/cgi-bin/\" )\n";
				$conf=$conf."\n";
				$conf=$conf."cgi.assign= (\n";
				$conf=$conf."\t\".pl\"  => \"/usr/bin/perl\",\n";
				$conf=$conf."\t\".php\" => \"/usr/bin/php-cgi\",\n";
				$conf=$conf."\t\".py\"  => \"/usr/bin/python\",\n";
				$conf=$conf."\t\".cgi\"  => \"/usr/bin/perl\",\n";
				
				if($users->MAILMAN_INSTALLED){
					$conf=$conf."\t\"/admin\" => \"\",\n";
					$conf=$conf."\t\"/admindb\" => \"\",\n";
					$conf=$conf."\t\"/confirm\" => \"\",\n";
					$conf=$conf."\t\"/create\" => \"\",\n";
					$conf=$conf."\t\"/edithtml\" => \"\",\n";
					$conf=$conf."\t\"/listinfo\" => \"\",\n";
					$conf=$conf."\t\"/options\" => \"\",\n";
					$conf=$conf."\t\"/private\" => \"\",\n";
					$conf=$conf."\t\"/rmlist\" => \"\",\n";
					$conf=$conf."\t\"/roster\" => \"\",\n";
					$conf=$conf."\t\"/subscribe\" => \"\"\n";	
				}
				
				$conf=$conf.")\n";
							
				return $conf;		
		
		
	}
	
	
	function BuildHttpdConf(){
			$user=new usersMenus();
			preg_match('#(.+?):(.+)#',$this->LighttpdUserAndGroup,$username);
			$username[0]=trim($username[0]);
			$username[1]=trim($username[1]);			
			
			$conf=$conf."ServerRoot \"/usr/share/artica-postfix\"\n";
			$conf=$conf."Listen $this->https_port\n";
			$conf=$conf."SSLPassPhraseDialog  builtin\n";
			$conf=$conf."SSLSessionCache        \"shmcb:/var/log/artica-postfix/apache/ssl_scache(512000)\"\n";
			$conf=$conf."SSLSessionCacheTimeout  300\n";
			$conf=$conf."SSLMutex  \"file:/opt/artica/logs/ssl_mutex\"\n";
			$conf=$conf."SSLEngine on\n";
			$conf=$conf."SSLCipherSuite ALL:!ADH:!EXPORT56:RC4+RSA:+HIGH:+MEDIUM:+LOW:+SSLv2:+EXP:+eNULL\n";
			$conf=$conf."SSLCertificateFile \"/etc/ssl/certs/apache/server.crt\"\n";
			$conf=$conf."SSLCertificateKeyFile \"/etc/ssl/certs/apache/server.key\"\n";
			$conf=$conf."\n";
			$conf=$conf."<IfModule !mpm_netware_module>\n";
			$conf=$conf."User {$username[0]}\n";
			$conf=$conf."Group {$username[1]}\n";
			$conf=$conf."ServerName $user->fqdn\n";
			$conf=$conf."</IfModule>\n";
			$conf=$conf."\n";
			$conf=$conf."ServerAdmin you@example.com\n";
			$conf=$conf."DocumentRoot \"/usr/share/artica-postfix\"\n";
			$conf=$conf."\n";
			$conf=$conf."<Directory />\n";
			$conf=$conf."    SSLOptions +StdEnvVars\n";
			$conf=$conf."    Options FollowSymLinks\n";
			$conf=$conf."    AllowOverride None\n";
			$conf=$conf."    Order deny,allow\n";
			$conf=$conf."    Deny from all\n";
			$conf=$conf."</Directory>\n";
			$conf=$conf."\n";
			$conf=$conf."\n";
			$conf=$conf."<Directory \"/usr/share/artica-postfix\">\n";
			$conf=$conf."    Options Indexes FollowSymLinks\n";
			$conf=$conf."    AllowOverride None\n";
			$conf=$conf."    Order allow,deny\n";
			$conf=$conf."    Allow from all\n";
			$conf=$conf."\n";
			$conf=$conf."</Directory>\n";
			$conf=$conf."\n";
			$conf=$conf."<IfModule dir_module>\n";
			$conf=$conf."    DirectoryIndex index.php\n";
			$conf=$conf."</IfModule>\n";
			$conf=$conf."\n";
			$conf=$conf."<FilesMatch \"^\.ht\">\n";
			$conf=$conf."    Order allow,deny\n";
			$conf=$conf."    Deny from all\n";
			$conf=$conf."    Satisfy All\n";
			$conf=$conf."</FilesMatch>\n";
			$conf=$conf."\n";
			$conf=$conf."ErrorLog logs/error_log\n";
			$conf=$conf."\n";
			$conf=$conf."LogLevel warn\n";
			$conf=$conf."\n";
			$conf=$conf."<IfModule log_config_module>\n";
			$conf=$conf."    LogFormat \"%h %l %u %t \\\"%r\\\" %>s %b \\\"%{Referer}i\\\" \\\"%{User-Agent}i\\\"\" combined\n";
			$conf=$conf."    LogFormat \"%h %l %u %t \\\"%r\\\" %>s %b\" common\n";
			$conf=$conf."\n";
			$conf=$conf."    <IfModule logio_module>\n";
			$conf=$conf."      LogFormat \"%h %l %u %t \\\"%r\\\" %>s %b \\\"%{Referer}i\\\" \\\"%{User-Agent}i\\\" %I %O\" combinedio\n";
			$conf=$conf."    </IfModule>\n";
			$conf=$conf."\n";
			$conf=$conf."CustomLog logs/access_log common\n";
			$conf=$conf."</IfModule>\n";
			$conf=$conf."\n";
			$conf=$conf."<IfModule alias_module>\n";
			$conf=$conf."    ScriptAlias /cgi-bin/ \"/opt/artica/cgi-bin/\"\n";
			$conf=$conf."\n";
			$conf=$conf."</IfModule>\n";
			$conf=$conf."\n";
			$conf=$conf."<IfModule cgid_module>\n";
			$conf=$conf."</IfModule>\n";
			$conf=$conf."\n";
			$conf=$conf."<Directory \"/opt/artica/cgi-bin\">\n";
			$conf=$conf."    AllowOverride None\n";
			$conf=$conf."    Options None\n";
			$conf=$conf."    Order allow,deny\n";
			$conf=$conf."    Allow from all\n";
			$conf=$conf."</Directory>\n";
			$conf=$conf."\n";
			$conf=$conf."DefaultType text/plain\n";
			$conf=$conf."\n";
			$conf=$conf."<IfModule mime_module>\n";
			$conf=$conf."    TypesConfig conf/mime.types\n";
			$conf=$conf."\n";
			$conf=$conf."    #AddType application/x-gzip .tgz\n";
			$conf=$conf."    #AddEncoding x-compress .Z\n";
			$conf=$conf."    #AddEncoding x-gzip .gz .tgz\n";
			$conf=$conf."    AddType application/x-compress .Z\n";
			$conf=$conf."    AddType application/x-gzip .gz .tgz\n";
			$conf=$conf."    AddType application/x-httpd-php .php .phtml\n";
			$conf=$conf."    AddType application/x-httpd-php-source .phps\n";
			$conf=$conf."    #AddHandler cgi-script .cgi\n";
			$conf=$conf."    #AddType text/html .shtml\n";
			$conf=$conf."    #AddOutputFilter INCLUDES .shtml\n";
			$conf=$conf."</IfModule>\n";
			$conf=$conf."\n";
			$conf=$conf."#MIMEMagicFile conf/magic\n";
			$conf=$conf."#ErrorDocument 500 \"The server made a boo boo.\"\n";
			$conf=$conf."#ErrorDocument 404 /missing.html\n";
			$conf=$conf."#ErrorDocument 404 \"/cgi-bin/missing_handler.pl\"\n";
			$conf=$conf."#ErrorDocument 402 http://www.example.com/subscription_info.html\n";
			$conf=$conf."#EnableMMAP off\n";
			$conf=$conf."#EnableSendfile off\n";
			$conf=$conf."\n";
			$conf=$conf."<IfModule ssl_module>\n";
			$conf=$conf."SSLRandomSeed startup builtin\n";
			$conf=$conf."SSLRandomSeed connect builtin\n";
			$conf=$conf."</IfModule>\n";
			$conf=$conf."\n";
			$conf=$conf."LoadModule php5_module        modules/libphp5.so\n";
			$conf=$conf."LoadModule ssl_module 		   modules/mod_ssl.so\n"; 
			
			$conf=$conf."\n";
			return $conf;
		
		
		
	}
	
	
	
	
	
	
	
	
}

//db4.7_stat -h /var/lib/ldap -m | head -n 2

?>