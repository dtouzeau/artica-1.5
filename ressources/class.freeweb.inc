<?php
	include_once(dirname(__FILE__).'/class.templates.inc');
	include_once(dirname(__FILE__).'/class.ldap.inc');
	include_once(dirname(__FILE__).'/class.users.menus.inc');
	include_once(dirname(__FILE__).'/class.mysql.inc');
	include_once(dirname(__FILE__).'/class.groups.inc');
	include_once(dirname(__FILE__).'/class.lvm.root.inc');
	include_once(dirname(__FILE__).'/class.drupal.inc');
	include_once(dirname(__FILE__).'/class.ccurl.inc');
	
	
	
	class freeweb{
		var $servername;
		var $groupware='';
		var $APACHE_DIR_SITES_ENABLED='';
		var $SSL_enabled=false;
		var $AS_ROOT=false;
		var $WORKING_DIRECTORY='';
		var $SQUID_CACHMGR='';
		var $APACHE_RUN_USER='';
		var $APACHE_RUN_GROUP='';
		var $LimitByIp=0;
		var $Params=array();
		var $mod_rewrite='';
		var $lvm_size=0;
		var $lvm_vg="";
		var $UseLoopDisk=0;
		var $LoopMounts="";
		var $useMysql=0;
		var $mysql_database;
		var $mysql_password;
		var $mysql_username;
		var $ftpuser;
		var $ftppassword;
		var $useSSL=0;
		var $uid;
		var $useFTP=0;
		var $ou;
		var $error;
		var $www_dir;
		var $ServerPort=0;
		var $UseReverseProxy=0;
		var $ProxyPass;
		var $Forwarder=0;
		var $ForwardTo=0;
		var $APACHE_MOD_TOMCAT=false;
		var $COUNTRIES_ISO=array("AALAND ISLANDS"=>"AX","AFGHANISTAN"=>"AF","ALBANIA"=>"AL","ALGERIA"=>"DZ","AMERICAN SAMOA"=>"AS","ANDORRA"=>"AD","ANGOLA"=>"AO","ANGUILLA"=>"AI","ANTARCTICA"=>"AQ","ANTIGUA AND BARBUDA"=>"AG","ARGENTINA"=>"AR","ARMENIA"=>"AM","ARUBA"=>"AW","AUSTRALIA"=>"AU","AUSTRIA"=>"AT","AZERBAIJAN"=>"AZ","BAHAMAS"=>"BS","BAHRAIN"=>"BH","BANGLADESH"=>"BD","BARBADOS"=>"BB","BELARUS"=>"BY","BELGIUM"=>"BE","BELIZE"=>"BZ","BENIN"=>"BJ","BERMUDA"=>"BM","BHUTAN"=>"BT","BOLIVIA"=>"BO","BOSNIA AND HERZEGOWINA"=>"BA","BOTSWANA"=>"BW","BOUVET ISLAND"=>"BV","BRAZIL"=>"BR","BRITISH INDIAN OCEAN TERRITORY"=>"IO","BRUNEI DARUSSALAM"=>"BN","BULGARIA"=>"BG","BURKINA FASO"=>"BF","BURUNDI"=>"BI","CAMBODIA"=>"KH","CAMEROON"=>"CM","CANADA"=>"CA","CAPE VERDE"=>"CV","CAYMAN ISLANDS"=>"KY","CENTRAL AFRICAN REPUBLIC"=>"CF","CHAD"=>"TD","CHILE"=>"CL","CHINA"=>"CN","CHRISTMAS ISLAND"=>"CX","COCOS (KEELING) ISLANDS"=>"CC","COLOMBIA"=>"CO","COMOROS"=>"KM","CONGO, Democratic Republic of (was Zaire)"=>"CD","CONGO, Republic of"=>"CG","COOK ISLANDS"=>"CK","COSTA RICA"=>"CR","COTE D'IVOIRE"=>"CI","CROATIA"=>"HR","CUBA"=>"CU","CYPRUS"=>"CY","CZECH REPUBLIC"=>"CZ","DENMARK"=>"DK","DJIBOUTI"=>"DJ","DOMINICA"=>"DM","DOMINICAN REPUBLIC"=>"DO","ECUADOR"=>"EC","EGYPT"=>"EG","EL SALVADOR"=>"SV","EQUATORIAL GUINEA"=>"GQ","ERITREA"=>"ER","ESTONIA"=>"EE","ETHIOPIA"=>"ET","FALKLAND ISLANDS"=>"FK","FAROE ISLANDS"=>"FO","FIJI"=>"FJ","FINLAND"=>"FI","FRANCE"=>"FR","FRENCH GUIANA"=>"GF","FRENCH POLYNESIA"=>"PF","FRENCH SOUTHERN TERRITORIES"=>"TF","GABON"=>"GA","GAMBIA"=>"GM","GEORGIA"=>"GE","GERMANY"=>"DE","GHANA"=>"GH","GIBRALTAR"=>"GI","GREECE"=>"GR","GREENLAND"=>"GL","GRENADA"=>"GD","GUADELOUPE"=>"GP","GUAM"=>"GU","GUATEMALA"=>"GT","GUINEA"=>"GN","GUINEA-BISSAU"=>"GW","GUYANA"=>"GY","HAITI"=>"HT","HEARD AND MC DONALD ISLANDS"=>"HM","HONDURAS"=>"HN","HONG KONG"=>"HK","HUNGARY"=>"HU","ICELAND"=>"IS","INDIA"=>"IN","INDONESIA"=>"ID","IRAN (ISLAMIC REPUBLIC OF)"=>"IR","IRAQ"=>"IQ","IRELAND"=>"IE","ISRAEL"=>"IL","ITALY"=>"IT","JAMAICA"=>"JM","JAPAN"=>"JP","JORDAN"=>"JO","KAZAKHSTAN"=>"KZ","KENYA"=>"KE","KIRIBATI"=>"KI","KOREA, DEMOCRATIC PEOPLE'S REPUBLIC OF"=>"KP","KOREA, REPUBLIC OF"=>"KR","KUWAIT"=>"KW","KYRGYZSTAN"=>"KG","LAO PEOPLE'S DEMOCRATIC REPUBLIC"=>"LA","LATVIA"=>"LV","LEBANON"=>"LB","LESOTHO"=>"LS","LIBERIA"=>"LR","LIBYAN ARAB JAMAHIRIYA"=>"LY","LIECHTENSTEIN"=>"LI","LITHUANIA"=>"LT","LUXEMBOURG"=>"LU","MACAU"=>"MO","MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF"=>"MK","MADAGASCAR"=>"MG","MALAWI"=>"MW","MALAYSIA"=>"MY","MALDIVES"=>"MV","MALI"=>"ML","MALTA"=>"MT","MARSHALL ISLANDS"=>"MH","MARTINIQUE"=>"MQ","MAURITANIA"=>"MR","MAURITIUS"=>"MU","MAYOTTE"=>"YT","MEXICO"=>"MX","MICRONESIA, FEDERATED STATES OF"=>"FM","MOLDOVA, REPUBLIC OF"=>"MD","MONACO"=>"MC","MONGOLIA"=>"MN","MONTSERRAT"=>"MS","MOROCCO"=>"MA","MOZAMBIQUE"=>"MZ","MYANMAR"=>"MM","NAMIBIA"=>"NA","NAURU"=>"NR","NEPAL"=>"NP","NETHERLANDS"=>"NL","NETHERLANDS ANTILLES"=>"AN","NEW CALEDONIA"=>"NC","NEW ZEALAND"=>"NZ","NICARAGUA"=>"NI","NIGER"=>"NE","NIGERIA"=>"NG","NIUE"=>"NU","NORFOLK ISLAND"=>"NF","NORTHERN MARIANA ISLANDS"=>"MP","NORWAY"=>"NO","OMAN"=>"OM","PAKISTAN"=>"PK","PALAU"=>"PW","PALESTINIAN TERRITORY, Occupied"=>"PS","PANAMA"=>"PA","PAPUA NEW GUINEA"=>"PG","PARAGUAY"=>"PY","PERU"=>"PE","PHILIPPINES"=>"PH","PITCAIRN"=>"PN","POLAND"=>"PL","PORTUGAL"=>"PT","PUERTO RICO"=>"PR","QATAR"=>"QA","REUNION"=>"RE","ROMANIA"=>"RO","RUSSIAN FEDERATION"=>"RU","RWANDA"=>"RW","SAINT HELENA"=>"SH","SAINT KITTS AND NEVIS"=>"KN","SAINT LUCIA"=>"LC","SAINT PIERRE AND MIQUELON"=>"PM","SAINT VINCENT AND THE GRENADINES"=>"VC","SAMOA"=>"WS","SAN MARINO"=>"SM","SAO TOME AND PRINCIPE"=>"ST","SAUDI ARABIA"=>"SA","SENEGAL"=>"SN","SERBIA AND MONTENEGRO"=>"CS","SEYCHELLES"=>"SC","SIERRA LEONE"=>"SL","SINGAPORE"=>"SG","SLOVAKIA"=>"SK","SLOVENIA"=>"SI","SOLOMON ISLANDS"=>"SB","SOMALIA"=>"SO","SOUTH AFRICA"=>"ZA","SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS"=>"GS","SPAIN"=>"ES","SRI LANKA"=>"LK","SUDAN"=>"SD","SURINAME"=>"SR","SVALBARD AND JAN MAYEN ISLANDS"=>"SJ","SWAZILAND"=>"SZ","SWEDEN"=>"SE","SWITZERLAND"=>"CH","SYRIAN ARAB REPUBLIC"=>"SY","TAIWAN"=>"TW","TAJIKISTAN"=>"TJ","TANZANIA, UNITED REPUBLIC OF"=>"TZ","THAILAND"=>"TH","TIMOR-LESTE"=>"TL","TOGO"=>"TG","TOKELAU"=>"TK","TONGA"=>"TO","TRINIDAD AND TOBAGO"=>"TT","TUNISIA"=>"TN","TURKEY"=>"TR","TURKMENISTAN"=>"TM","TURKS AND CAICOS ISLANDS"=>"TC","TUVALU"=>"TV","UGANDA"=>"UG","UKRAINE"=>"UA","UNITED ARAB EMIRATES"=>"AE","UNITED KINGDOM"=>"GB","UNITED STATES"=>"US","UNITED STATES MINOR OUTLYING ISLANDS"=>"UM","URUGUAY"=>"UY","UZBEKISTAN"=>"UZ","VANUATU"=>"VU","VATICAN CITY STATE (HOLY SEE)"=>"VA","VENEZUELA"=>"VE","VIET NAM"=>"VN","VIRGIN ISLANDS (BRITISH)"=>"VG","VIRGIN ISLANDS (U.S.)"=>"VI","WALLIS AND FUTUNA ISLANDS"=>"WF","WESTERN SAHARA"=>"EH","YEMEN"=>"YE","ZAMBIA"=>"ZM","ZIMBABWE"=>"ZW");
		var $APACHE_MOD_INFO=false;
		var $APACHE_MOD_STATUS=false;
		var $phpcgi=null;
		
		function freeweb($servername=null){
			$users=new usersMenus();
			if($GLOBALS["posix_getuid"]==0){$this->AS_ROOT=true;}
			$this->APACHE_DIR_SITES_ENABLED=$users->APACHE_DIR_SITES_ENABLED;
			$users=new usersMenus();
			$this->APACHE_MOD_INFO=$users->APACHE_MOD_INFO;
			$this->APACHE_MOD_STATUS=$users->APACHE_MOD_STATUS;
			$this->SQUID_CACHMGR=$users->SQUID_CACHMGR;
			$this->phpcgi=$users->phpcgi;
			
			
			
			$this->APACHE_MOD_TOMCAT=$users->APACHE_MOD_TOMCAT;
			
			if($servername<>null){
				$this->WORKING_DIRECTORY="/var/www/$servername";
				$this->servername=$servername;
				$this->load();
			}
		}
		
		private function ApacheAccount(){
			if(!$this->AS_ROOT){return;}
				$unix=new unix();
				$this->APACHE_RUN_USER=$unix->APACHE_SRC_ACCOUNT();
				$this->APACHE_RUN_GROUP=$unix->APACHE_SRC_GROUP();
			

			if($this->APACHE_RUN_USER==null){
				if($this->AS_ROOT){writelogs("Starting......: Apache \"$this->servername\" WARNING unable to stat Apache Account !",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
			}
			
		}
		
		private function load(){
			if($this->AS_ROOT){writelogs("Starting......: Apache \"$this->servername\" Load DB..",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
			$sql="SELECT * from freeweb WHERE servername='$this->servername'";
			$q=new mysql();
			$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
			if(!$q->ok){writelogs("Fatal $q->mysql_error",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
			$this->groupware=$ligne["groupware"];
			$this->servername=$ligne["servername"];
			if(strlen($ligne["Params"])>5){$this->Params=@unserialize(base64_decode($ligne["Params"]));}
			$this->lvm_size=$ligne["lvm_size"];
			$this->lvm_vg=$ligne["lvm_vg"];
			$this->UseLoopDisk=$ligne["UseLoopDisk"];
			$this->LoopMounts=$ligne["LoopMounts"];
			$this->useMysql=$ligne["useMysql"];
			if($this->AS_ROOT){writelogs("Starting......: Apache \"$this->servername\" groupware:$this->groupware",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);}
			if($ligne["useSSL"]==1){$this->SSL_enabled=true;}
			if(isset($this->Params["LimitByIp"]["enabled"])){$this->LimitByIp=$this->Params["LimitByIp"]["enabled"];}
			$this->mod_rewrite=base64_decode($ligne["mod_rewrite"]);
			if($this->UseLoopDisk==1){$this->lvm_vg=null;$this->lvm_size=0;}
			$this->mysql_database=$ligne["mysql_database"];
			$this->mysql_password=$ligne["mysql_password"];
			$this->mysql_username=$ligne["mysql_username"];
			$this->ftpuser=$ligne["ftpuser"];
			$this->ftppassword=$ligne["ftppassword"];
			$this->useSSL=$ligne["useSSL"];
			$this->uid=$ligne["uid"];
			$this->useFTP=$ligne["useFTP"];
			$this->ou=$ligne["ou"];
			$this->www_dir=$ligne["www_dir"];
			$this->ServerPort=$ligne["ServerPort"];
			$this->UseReverseProxy=$ligne["UseReverseProxy"];
			$this->ProxyPass=$ligne["ProxyPass"];
			$this->Forwarder=$ligne["Forwarder"];
			$this->ForwardTo=$ligne["ForwardTo"];
			$this->HasDefaults();
			$this->ApacheAccount();
			
			
		}
		

		
		private function HasDefaults(){
			if(!isset($this->Params["MOD_FCGID"]["enabled"])){$this->Params["MOD_FCGID"]["enabled"]=0;}
			if(!isset($this->Params["MOD_FCGID"]["PHP_FCGI_MAX_REQUESTS"])){$this->Params["MOD_FCGID"]["PHP_FCGI_MAX_REQUESTS"]=5000;}
			if(!isset($this->Params["MOD_FCGID"]["PHP_FCGI_CHILDREN"])){$this->Params["MOD_FCGID"]["PHP_FCGI_CHILDREN"]=8;}
			if(!isset($this->Params["MOD_FCGID"]["FcgidBusyTimeout"])){$this->Params["MOD_FCGID"]["FcgidBusyTimeout"]=300;}
			if(!isset($this->Params["MOD_FCGID"]["FcgidIdleTimeout"])){$this->Params["MOD_FCGID"]["FcgidIdleTimeout"]=300;}
			if(!isset($this->Params["MOD_FCGID"]["FcgidMaxRequestInMem"])){$this->Params["MOD_FCGID"]["FcgidMaxRequestInMem"]=65536;}
			if(!isset($this->Params["MOD_FCGID"]["FcgidMaxProcessesPerClass"])){$this->Params["MOD_FCGID"]["FcgidMaxProcessesPerClass"]=100;}
			if(!isset($this->Params["MOD_FCGID"]["FcgidMaxRequestLen"])){$this->Params["MOD_FCGID"]["FcgidMaxRequestLen"]=131072;}
			if(!isset($this->Params["MOD_FCGID"]["FcgidOutputBufferSize"])){$this->Params["MOD_FCGID"]["FcgidOutputBufferSize"]=65536;}			
			if(!is_numeric($this->Params["MOD_FCGID"]["enabled"])){$this->Params["MOD_FCGID"]["enabled"]=0;}
			if(!is_numeric($this->Params["MOD_FCGID"]["PHP_FCGI_MAX_REQUESTS"])){$this->Params["MOD_FCGID"]["PHP_FCGI_MAX_REQUESTS"]=5000;}
			if(!is_numeric($this->Params["MOD_FCGID"]["PHP_FCGI_CHILDREN"])){$this->Params["MOD_FCGID"]["PHP_FCGI_CHILDREN"]=8;}
			if(!is_numeric($this->Params["MOD_FCGID"]["FcgidBusyTimeout"])){$this->Params["MOD_FCGID"]["FcgidBusyTimeout"]=300;}
			if(!is_numeric($this->Params["MOD_FCGID"]["FcgidIdleTimeout"])){$this->Params["MOD_FCGID"]["FcgidIdleTimeout"]=300;}
			if(!is_numeric($this->Params["MOD_FCGID"]["FcgidMaxRequestInMem"])){$this->Params["MOD_FCGID"]["FcgidMaxRequestInMem"]=65536;}
			if(!is_numeric($this->Params["MOD_FCGID"]["FcgidMaxProcessesPerClass"])){$this->Params["MOD_FCGID"]["FcgidMaxProcessesPerClass"]=100;}
			if(!is_numeric($this->Params["MOD_FCGID"]["FcgidMaxRequestLen"])){$this->Params["MOD_FCGID"]["FcgidMaxRequestLen"]=131072;}
			if(!is_numeric($this->Params["MOD_FCGID"]["FcgidOutputBufferSize"])){$this->Params["MOD_FCGID"]["FcgidOutputBufferSize"]=65536;}
						
			
		}
		
		
		public function groupwares_InstallLink(){
			$prefix="http";
			if(!is_numeric($this->ServerPort)){$this->ServerPort=80;}
			if($this->ServerPort==0){$this->ServerPort=80;}
			if($this->useSSL==1){$prefix="https";}
			
			$prefix="$prefix://$this->servername:$this->ServerPort";
			
			switch ($this->groupware) {
			case "WORDPRESS":
				return "$prefix/wp-admin/install.php";
			break;
			
			case "JOOMLA17":
				return "$prefix/administrator";
			break;				
			
			}
			
		}
		
		
		public function groupwares_textintro(){
		switch ($this->groupware) {
			case "WORDPRESS":
				return "{howto_wordpress_text}";
			break;
			
			case "JOOMLA17":
				return "{howto_joomla_text}";
			break;				
			
			}
			
		}
		
		public function CreateSite(){
			include_once(dirname(__FILE__)."/class.user.inc");
			
			$sqladd="INSERT INTO freeweb (mysql_password,mysql_username,ftpuser,ftppassword,useSSL,
			servername,mysql_database,uid,useMysql,
			useFTP,lvm_vg,lvm_size,UseLoopDisk,LoopMounts,ou,groupware,www_dir)
			VALUES('$this->mysql_password','$this->mysql_username','$this->ftpuser','$this->ftppassword','$this->useSSL',
			'$this->servername','$this->mysql_database','$this->uid','$this->useMysql',
			'$this->useFTP','$this->lvm_vg','$this->lvm_size','$this->UseLoopDisk','$this->LoopMounts','$this->ou','$this->groupware','$this->www_dir')";
			
			
		$sqledit="UPDATE freeweb SET 
			mysql_password='$this->mysql_password',
			mysql_username=''$this->mysql_username',
			ftpuser=''$this->ftpuser',
			ftppassword=''$this->ftppassword',
			uid=''$this->uid',
			useMysql='$this->useMysql',
			useFTP='$this->useFTP',
			lvm_vg='$this->lvm_vg',
			lvm_size='$this->lvm_size',
			UseLoopDisk='$this->UseLoopDisk',
			LoopMounts='$this->LoopMounts',
			useSSL='$this->useSSL',
			ou='$this->ou',
			groupware='$this->groupware',
			www_dir='$this->www_dir'
			WHERE servername='$this->servername'";	
				
			
			$sql="SELECT servername FROM freeweb WHERE servername='$this->servername'";
			$q=new mysql();
			$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
			if($ligne["servername"]<>null){$sql=$sqledit;}else{$sql=$sqladd;}
			if(!$q->QUERY_SQL($sql,"artica_backup")){
				$this->error=$q->mysql_error;
				return false;
			}
			$sock=new sockets();
			$sock->getFrameWork("cmd.php?freeweb-website=$this->servername&servername=$this->servername");
			$sock->getFrameWork("cmd.php?freeweb-groupware=$this->servername&servername=$this->servername");
			return true;
			
		}
		
		public function AddDefaultSite(){
			$this->servername="_default_";
			$this->www_dir="/var/www/html";
			$this->CreateSite();
		}
		
		
		public function LimitByIp_list(){
			if(!isset($this->Params["LimitByIp"]["IPS"])){return array();}
			if(!is_array($this->Params["LimitByIp"]["IPS"])){return array();}
			reset($this->Params["LimitByIp"]["IPS"]);
			return $this->Params["LimitByIp"]["IPS"];
			
		}
		
		public function LimitByIp_add($ip){
			if($this->servername==null){return;}
			$this->Params["LimitByIp"]["enabled"]=$this->LimitByIp;
			$this->Params["LimitByIp"]["IPS"][]=$ip;
			$this->SaveParams();
		}
		
		public function LimitByIp_del($ip){
			if($this->servername==null){return;}
			writelogs("Deleting ip $ip",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);
			$this->Params["LimitByIp"]["enabled"]=$this->LimitByIp;
			
			while (list ($num, $ligne) = each ($this->Params["LimitByIp"]["IPS"]) ){
				writelogs("found $ligne",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);
				if($ligne==$ip){
					writelogs("unset $ligne",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);
					unset($this->Params["LimitByIp"]["IPS"][$num]);
				}
			}
			
			$this->SaveParams();
		}
		
		private function locate_geoip_db(){
			$f[]="/usr/share/GeoIP/GeoIP.dat";
			$f[]="/usr/local/share/GeoIP/GeoIP.dat";
			$f[]="/root/.mldonkey/web_infos/GeoIP.dat";
			while (list ($key, $path) = each ($f) ){
				if(is_file($path)){return $path;}
			}
		}
		
		public function ServerAlias(){
			if(!isset($this->Params["ServerAlias"])){return;}
			if(count($this->Params["ServerAlias"])==0){return;}
			$f=array();
			while (list ($host,$num) = each ($this->Params["ServerAlias"]) ){
				if(trim($host)==null){continue;}
				$f[]="\tServerAlias $host";
			}	
			if(count($f)>0){return @implode("\n", $f);}
			
		}
		
		
		
		public function mod_pagespeed(){
			if(!isset($this->Params["PageSpeed"])){return null;}
			if(count($this->Params["PageSpeed"])==0){return null;}
			echo "Starting......: Apache \"$this->servername\" mod_pagespeed Enabled\n";

			$f[]="\t<IfModule pagespeed_module>";
  			$f[]="\t\tModPagespeed On";
  			$f[]="\t\tModPagespeedStatistics Off";
  			
  			
  			if(!isset($this->Params["PageSpeedParams"]["ModPagespeedFileCacheSizeKb"])){$this->Params["PageSpeedParams"]["ModPagespeedFileCacheSizeKb"]=null;}
			if(!isset($this->Params["PageSpeedParams"]["ModPagespeedFileCacheCleanIntervalMs"])){$this->Params["PageSpeedParams"]["ModPagespeedFileCacheCleanIntervalMs"]=null;}
			if(!isset($this->Params["PageSpeedParams"]["ModPagespeedLRUCacheKbPerProcess"])){$this->Params["PageSpeedParams"]["ModPagespeedLRUCacheKbPerProcess"]=null;}
			if(!isset($this->Params["PageSpeedParams"]["ModPagespeedLRUCacheByteLimit"])){$this->Params["PageSpeedParams"]["ModPagespeedLRUCacheByteLimit"]=null;}
  			$CONF=$this->Params["PageSpeedParams"];
			$ModPagespeedFileCacheSizeKb=$CONF["ModPagespeedFileCacheSizeKb"];
			$ModPagespeedFileCacheCleanIntervalMs=$CONF["ModPagespeedFileCacheCleanIntervalMs"];
			$ModPagespeedLRUCacheKbPerProcess=$CONF["ModPagespeedLRUCacheKbPerProcess"];
			$ModPagespeedLRUCacheByteLimit=$CONF["ModPagespeedLRUCacheByteLimit"];
	
			if(!is_numeric($ModPagespeedFileCacheSizeKb)){$ModPagespeedFileCacheSizeKb=102400;}
			if(!is_numeric($ModPagespeedFileCacheCleanIntervalMs)){$ModPagespeedFileCacheCleanIntervalMs=3600000;}
			if(!is_numeric($ModPagespeedLRUCacheKbPerProcess)){$ModPagespeedLRUCacheKbPerProcess=1024;}
			if(!is_numeric($ModPagespeedLRUCacheByteLimit)){$ModPagespeedLRUCacheByteLimit=16384;}			
  			$f[]="\t\tModPagespeedFileCacheSizeKb $ModPagespeedFileCacheSizeKb";
  			$f[]="\t\tModPagespeedFileCacheCleanIntervalMs $ModPagespeedFileCacheCleanIntervalMs";
  			$f[]="\t\tModPagespeedLRUCacheKbPerProcess $ModPagespeedLRUCacheKbPerProcess";
  			$f[]="\t\tModPagespeedLRUCacheByteLimit $ModPagespeedLRUCacheByteLimit";
			
			if(!is_dir("/var/cache/apache2/mod_pagespeed")){@mkdir("/var/cache/apache2/mod_pagespeed",0755,true);}
  			if(!is_dir("/var/cache/apache2/mod_pagespeed/$this->servername")){@mkdir("/var/cache/apache2/mod_pagespeed/$this->servername",0755,true);}
  			if(!is_dir("/var/cache/apache2/mod_pagespeed/$this->servername/files")){@mkdir("/var/cache/apache2/mod_pagespeed/$this->servername/files",0755,true);}
    		$f[]="\t\tModPagespeedFileCachePath            \"/var/cache/apache2/mod_pagespeed/$this->servername\"";
		    $f[]="\t\tModPagespeedGeneratedFilePrefix      \"/var/cache/apache2/mod_pagespeed/$this->servername/files/\"";
			$f[]="\t</IfModule>";
			return @implode("\n", $f);
		}		
		
		public function mod_geoip(){
			if(!isset($this->Params["GEOIP"])){return null;}
			if(count($this->Params["GEOIP"])==0){return null;}
			echo "Starting......: Apache \"$this->servername\" GeoIP Enabled\n";
			$goip=$this->locate_geoip_db();
			if(!is_file($goip)){
				echo "Starting......: WARNING Apache \"$this->servername\" GeoIP unable to locate GeoIP.dat !!!\n";
				return null;
			}
			echo "Starting......: Apache \"$this->servername\" GeoIP ". count($this->Params["GEOIP"]). " countries to block\n";
			$f[]="\t<IfModule mod_geoip.c>";
  			$f[]="\t\tGeoIPEnable On";
  			$f[]="\t\tGeoIPDBFile $goip MemoryCache";
			
  			while (list ($country, $none) = each ($this->Params["GEOIP"]) ){
  				$f[]="\t\tSetEnvIf GEOIP_COUNTRY_CODE $country BlockCountry";
  			}
  			
  			
			$f[]="\t</IfModule>";
			return @implode("\n", $f);
		}
		
		
		public function QUOS(){
			if(!isset($this->Params["QOS"])){$this->Params["QOS"]["EnableQOS"]=0;}
			$EnableQOS=$this->Params["QOS"]["EnableQOS"];
			if(!is_numeric($EnableQOS)){$EnableQOS=0;}
			if(isset($this->Params["QOS"]["QS_ClientEntries"])){$QS_ClientEntries=$this->Params["QOS"]["QS_ClientEntries"];}
			if(isset($this->Params["QOS"]["QS_SrvMaxConnPerIP"])){$QS_SrvMaxConnPerIP=$this->Params["QOS"]["QS_SrvMaxConnPerIP"];}
			if(isset($this->Params["QOS"]["MaxClients"])){$MaxClients=$this->Params["QOS"]["MaxClients"];}
			if(isset($this->Params["QOS"]["QS_SrvMaxConnClose"])){$QS_SrvMaxConnClose=$this->Params["QOS"]["QS_SrvMaxConnClose"];}
			if(isset($this->Params["QOS"]["QS_SrvMinDataRate"])){$QS_SrvMinDataRate=$this->Params["QOS"]["QS_SrvMinDataRate"];}
			if(isset($this->Params["QOS"]["LimitRequestFields"])){$LimitRequestFields=$this->Params["QOS"]["LimitRequestFields"];}
			if(isset($this->Params["QOS"]["QS_LimitRequestBody"])){$QS_LimitRequestBody=$this->Params["QOS"]["QS_LimitRequestBody"];}
			
			if($EnableQOS==0){
				//$f[]="\t<IfModule mod_qos.c>";
				//$f[]="\t\tQS_InvalidUrlEncoding off";
				//$f[]="\t\tQS_ResponseHeaderFilter off";
				//$f[]="\t\tQS_DenyQueryBody off";
				//$f[]="\t\tQS_PermitUriBody off";
				//$f[]="\t</IfModule>";
				return @implode("\n",$f);
			}
			
			
			
			if(!is_numeric($QS_ClientEntries)){$QS_ClientEntries=100000;}
			if(!is_numeric($QS_SrvMaxConnPerIP)){$QS_SrvMaxConnPerIP=50;}
			if(!is_numeric($MaxClients)){$MaxClients=256;}
			if(!is_numeric($QS_SrvMaxConnClose)){$QS_SrvMaxConnClose=180;}
			if($QS_SrvMinDataRate==null){$QS_SrvMinDataRate="150 1200";}
			if(!is_numeric($LimitRequestFields)){$LimitRequestFields=30;}
			if(!is_numeric($QS_LimitRequestBody)){$QS_LimitRequestBody=102400;}
			
			
		
			$f[]="\t<IfModule mod_qos.c>";
		  	if($QS_ClientEntries>0){ $f[]="\t\tQS_ClientEntries $QS_ClientEntries";}
		  	if($QS_SrvMaxConnPerIP>0){ $f[]="\t\tQS_SrvMaxConnPerIP $QS_SrvMaxConnPerIP";}
			if($MaxClients>0){ $f[]="\t\tMaxClients $MaxClients";}
			if($QS_SrvMaxConnClose>0){ $f[]="\t\tQS_SrvMaxConnClose $QS_SrvMaxConnClose";}
			if(preg_match("#[0-9]+\s+[0-9]+#",$QS_SrvMinDataRate)){
				$f[]="\t\tQS_SrvMinDataRate $QS_SrvMinDataRate";
			}		 		  		   
			if($LimitRequestFields>0){ $f[]="\t\tLimitRequestFields $LimitRequestFields";}
			if($QS_LimitRequestBody>0){ $f[]="\t\tQS_LimitRequestBody $QS_LimitRequestBody";}			
			$f[]="\t</IfModule>";
			return @implode("\n",$f);
			
		}
		
		
		public function AllowOverride(){
			$AllowOverride="all";
			if(isset($this->Params["SECURITY"])){
				if($this->Params["SECURITY"]["DisableHtAccess"]==1){return "\t\tAllowOverride None";}
			}
			return "\t\tAllowOverride $AllowOverride";
		
		}
		
		public function Charsets(){
			if(!isset($this->Params["AddDefaultCharset"])){return;}
			$this->Params["AddDefaultCharset"]=trim($this->Params["AddDefaultCharset"]);
			if(trim($this->Params["AddDefaultCharset"])==null){return;}
			$f[]="\tAddDefaultCharset {$this->Params["AddDefaultCharset"]}";
			if(strtolower($this->Params["AddDefaultCharset"])=="off"){return @implode("\n",$f);}
			if(strtolower($this->Params["AddDefaultCharset"])=="on"){return @implode("\n",$f);}
			$f[]="\tAddCharset {$this->Params["AddDefaultCharset"]} .php";
			$f[]="\tAddCharset {$this->Params["AddDefaultCharset"]} .html";
			$f[]="\tAddCharset {$this->Params["AddDefaultCharset"]} .xml";
			$f[]="\tAddCharset {$this->Params["AddDefaultCharset"]} .js";
			@implode("\n",$f);
		}

		public function SaveParams(){
			reset($this->Params);
			$conf=base64_encode(serialize($this->Params));
			$sql="UPDATE freeweb SET `Params`='$conf' WHERE servername='$this->servername'";
			$q=new mysql();
			$q->QUERY_SQL($sql,"artica_backup");
			if(!$q->ok){
				writelogs("$this->servername:: $q->mysql_error",__CLASS__."/".__FUNCTION__,__FILE__,__LINE__);
				echo $q->mysql_error."\nclass: ".__CLASS__."\nFile: ".basename(__FILE__)."\nLine:".__LINE__;return;
			}
			$sock=new sockets();
			$sock->getFrameWork("cmd.php?freeweb-website=yes&servername=$this->servername");
			
		}
		
		
		public function AuthUsers(){
			if(!isset($this->Params["LDAP"]["members"])){
				echo "Starting......: Apache \"$this->servername\" AUTH: No members set\n";
				return null;
			}
			$users=$this->Params["LDAP"]["members"];
			echo "Starting......: Apache \"$this->servername\" AUTH: ".count($users)." member(s)\n";
			if(count($users)==0){return null;}
			while (list ($num, $ligne) = each ($users) ){
				if($num==null){continue;}
				if(preg_match("#^group:@(.+?):([0-9]+)#",$num,$re)){
					$gid=$re[2];
					$group=new groups($gid);
					$f[]="\t\tRequire ldap-group $group->dn";
				}
				
				if(preg_match("#^user:(.+)#",$num,$re)){
					$f[]="\t\tRequire ldap-user {$re[1]}";
				}				
			}
			echo "Starting......: Apache \"$this->servername\" AUTH: ".count($f)." member(s) compiled\n";
			if(count($f)==0){
				echo "Starting......: Apache \"$this->servername\" AUTH: No user defined\n";
				return null;
			}
			
			if(!isset($GLOBALS["MAANAGER_NAME"])){
				$ldap=new clladp();
				$GLOBALS["MAANAGER_NAME"]=$ldap->ldap_admin;
			}
			echo "Starting......: Apache \"$this->servername\" AUTH:{$GLOBALS["MAANAGER_NAME"]}\n";
			$f[]="\t\tRequire ldap-user {$GLOBALS["MAANAGER_NAME"]}";
			return @implode("\n", $f);
			
		}
		public function mod_status(){
			if(!$this->APACHE_MOD_INFO){echo "Starting......: Apache \"$this->servername\" mod_info, no such module\n";return;}
			if(!$this->APACHE_MOD_STATUS){echo "Starting......: Apache \"$this->servername\" mod_status, no such module\n";return;}
			if(isset($GLOBALS["HTTPD_CONF_MOD_STATUS"])){return $GLOBALS["HTTPD_CONF_MOD_STATUS"];}
			
			if(!isset($GLOBALS["MYINTERFACESAllowFROM"])){
				include_once(dirname(__FILE__)."/class.system.network.inc");
				$tcp=new networking();
				$sock=new sockets();
				$ips=$tcp->ALL_IPS_GET_ARRAY();
				while (list ($ip, $line) = each ($ips) ){$GLOBALS["MYINTERFACESAllowFROM"][]="\t\tAllow from $ip";}
				$hash=unserialize(base64_decode($sock->GET_INFO("FreeWedMoStatusAllowFrom")));
				if(isset($hash)){if(is_array($hash)){while (list ($ip, $line) = each ($hash) ){$GLOBALS["MYINTERFACESAllowFROM"][]="\t\tAllow from $ip";}}}
			}
			
			$dirMD=md5($this->servername);
			$f[]="\t<Location /$dirMD/$dirMD-status>";
			$f[]="\t\tSetHandler server-status";
			$f[]="\t\tOrder deny,allow";
			$f[]="\t\tDeny from all";
			$f[]=@implode("\n", $GLOBALS["MYINTERFACESAllowFROM"]);
			$f[]="\t\tSatisfy Any";
			$f[]="\t</Location>";
			$mod_status=@implode("\n",$f);
			$GLOBALS["HTTPD_CONF_MOD_STATUS"]=$mod_status;
			return $mod_status;
			
		}
		
		public function RewriteEngine(){
			if($GLOBALS["VERBOSE"]){echo "Starting......: Apache \"$this->servername\" is forwarder ? ($this->Forwarder) groupware=$this->groupware\n";}
			if($this->Forwarder==1){
				echo "Starting......: Apache \"$this->servername\" is forwared to $this->ForwardTo\n";
				$conf[]="\tRewriteEngine On";
				$conf[]="\tRewriteRule (.*) $this->ForwardTo";
				return @implode("\n", $conf);
			}
			
			if($this->groupware=="WORDPRESS"){
				$f[]="\t<IfModule mod_rewrite.c>";
				$f[]="\t\tRewriteEngine On";
				$f[]="\t\tRewriteCond %{REQUEST_URI} !". md5($this->servername);				
				$f[]="\t</IfModule>";
				return @implode("\n", $f);
			}
			
			
		}
		
		
		public function mod_evasive(){
			$sock=new sockets();
			$FreeWebsEnableModEvasive=$sock->GET_INFO("FreeWebsEnableModEvasive");
			if($FreeWebsEnableModEvasive<>1){return null;}			
			$q=new mysql();
			if(!isset($this->Params["mod_evasive"])){return;}
			$Params=$this->Params["mod_evasive"];	
			if(!is_numeric($Params["DOSHashTableSize"])){$Params["DOSHashTableSize"]=1024;}
			if(!is_numeric($Params["DOSPageCount"])){$Params["DOSPageCount"]=10;}
			if(!is_numeric($Params["DOSSiteCount"])){$Params["DOSSiteCount"]=150;}
			if(!is_numeric($Params["DOSPageInterval"])){$Params["DOSPageInterval"]=1.5;}
			if(!is_numeric($Params["DOSSiteInterval"])){$Params["DOSSiteInterval"]=1.5;}
			if(!is_numeric($Params["DOSBlockingPeriod"])){$Params["DOSBlockingPeriod"]=10.7;}	
			if(!is_numeric($Params["DOSEnable"])){$Params["DOSEnable"]=0;}	
			if($Params["DOSEnable"]==0){
				echo "Starting......: Apache \"$this->servername\" DDOS evasive disabled\n";
				return; 
			}		

			
			echo "Starting......: Apache \"$this->servername\" DDOS evasive Enabled\n";
 
					
			
			$f[]="<IfModule mod_evasive20.c>";
			$f[]="   DOSHashTableSize {$Params["DOSHashTableSize"]}";
			$f[]="   DOSPageCount {$Params["DOSPageCount"]}";
			$f[]="   DOSSiteCount {$Params["DOSSiteCount"]}";
			$f[]="   DOSPageInterval {$Params["DOSPageInterval"]}";
			$f[]="   DOSSiteInterval {$Params["DOSSiteInterval"]}";
			$f[]="   DOSBlockingPeriod {$Params["DOSBlockingPeriod"]}";
			$f[]="   DOSLogDir  \"/var/log/apache2/$this->servername/mod_evasive.log\"";
			$f[]="   DOSSystemCommand \"/bin/echo `date '+%F %T'` $this->servername  %s >> /var/log/apache2/dos_evasive_attacks.log\"";
			
			
			@mkdir("/var/log/apache2/$this->servername",755,true);
			$sql="SELECT * FROM freewebs_whitelist WHERE servername='$this->servername' $qsearch ORDER BY ID DESC";
			$results=$q->QUERY_SQL($sql,'artica_backup');
			if(!$q->ok){echo "$q->mysql_error\n";}			
			while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){	
				$f[]="   DOSWhitelist {$ligne["ipaddr"]}";
			}
			
			$f[]="</IfModule>"; 
			$f[]="";			
			return @implode("\n",$f);
		}
		
		public function ReverseProxy(){
			if($this->UseReverseProxy==0){return;}
			if(substr($this->ProxyPass, strlen($this->ProxyPass)-1,1)<>'/'){$this->ProxyPass=$this->ProxyPass."/";}
			if(!is_numeric($this->Params["Proxy"]["ProxyRequests"])){$this->Params["Proxy"]["ProxyRequests"]=0;}
			if(!is_numeric($this->Params["Proxy"]["ProxyVia"])){$this->Params["Proxy"]["ProxyVia"]=0;}
			if(!is_numeric($this->Params["Proxy"]["KeepAlive"])){$this->Params["Proxy"]["KeepAlive"]=0;}
			
			if(!isset($this->Params["Proxy"]["ProxyTimeout"])){$this->Params["Proxy"]["ProxyTimeout"]=300;}
			if(!is_numeric($this->Params["Proxy"]["ProxyTimeout"])){$this->Params["Proxy"]["ProxyTimeout"]=300;}
			
			
			
			if($this->Params["Proxy"]["ProxyRequests"]==0){$this->Params["Proxy"]["ProxyRequests"]="Off";}else{$this->Params["Proxy"]["ProxyRequests"]="On";}
			if($this->Params["Proxy"]["ProxyVia"]==0){$this->Params["Proxy"]["ProxyVia"]="Off";}else{$this->Params["Proxy"]["ProxyVia"]="On";}
			if($this->Params["Proxy"]["KeepAlive"]==0){$this->Params["Proxy"]["KeepAlive"]="Off";}else{$this->Params["Proxy"]["KeepAlive"]="On";}
			
			$f[]="ProxyPreserveHost On";
			$f[]="ProxyRequests {$this->Params["Proxy"]["ProxyRequests"]}";
			$f[]="ProxyVia {$this->Params["Proxy"]["ProxyVia"]}";
			$f[]="KeepAlive {$this->Params["Proxy"]["KeepAlive"]}";
			$f[]="ProxyTimeout {$this->Params["Proxy"]["ProxyTimeout"]}";
			if(strlen($this->Params["Proxy"]["AllowCONNECT"])>1){$f[]="AllowCONNECT {$this->Params["Proxy"]["AllowCONNECT"]}";}
			$sql="SELECT * FROM freewebs_proxy WHERE servername='$this->servername'";
			$q=new mysql();
			$results=$q->QUERY_SQL($sql,'artica_backup');
			if(mysql_num_rows($results)==0){return;}
			while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
				if(!preg_match("#(.+?)\s+(.+)#", $ligne["ProxyPass"],$re)){continue;}
				$request_from=trim($re[1]);$redirect_to=trim($re[2]);
				if(substr($request_from, strlen($request_from)-1)<>'/'){$request_from=$request_from."/";}
				if(substr($redirect_to, strlen($redirect_to)-1)<>'/'){$redirect_to=$redirect_to."/";}
				$f[]="ProxyPass $request_from\t$redirect_to";
				$f[]="ProxyPassReverse $request_from\t$redirect_to";
				
			}
			return @implode("\n", $f);
		}
		
		
		
		public function mod_security(){
			$sock=new sockets();
			$FreeWebsEnableModSecurity=$sock->GET_INFO("FreeWebsEnableModSecurity");
			if($FreeWebsEnableModSecurity<>1){return null;}
			if(!isset($this->Params["mod_security"])){return null;}
			$q=new mysql();
			
			$Params=$this->Params["mod_security"];
			if($Params["SecFilterEngine"]<>1){$Params["SecFilterEngine"]="Off";}else{$Params["SecFilterEngine"]="On";}
			if($Params["SecFilterCheckURLEncoding"]<>1){$Params["SecFilterCheckURLEncoding"]="Off";}else{$Params["SecFilterCheckURLEncoding"]="On";}
			if($Params["SecFilterCheckUnicodeEncoding"]<>1){$Params["SecFilterCheckUnicodeEncoding"]="Off";}else{$Params["SecFilterCheckUnicodeEncoding"]="On";}
			if($Params["SecFilterScanPOST"]<>1){$Params["SecFilterScanPOST"]="Off";}else{$Params["SecFilterScanPOST"]="On";}
			if($Params["SecFilterDefaultAction"]==null){$Params["SecFilterDefaultAction"]="Hello World!";}
		
			$f[]="<IfModule security2_module>";
			$f[]="   SecRuleEngine {$Params["SecFilterEngine"]}";
			$f[]="   SecAuditEngine RelevantOnly";
			$f[]="   SecAuditLog /var/log/apache2/$this->servername/modsec_audit_log";
			$f[]="   SecDebugLog /var/log/apache2/$this->servername/modsec_debug_log";
			$f[]="   SecDebugLogLevel 1";
			$f[]="   SecRequestBodyAccess {$Params["SecFilterScanPOST"]}";
			$f[]="   SecDefaultAction \"phase:2,deny,log,status:500\"";
			@mkdir("/var/log/apache2/$this->servername",755,true);
			$sql="SELECT * FROM freewebs_secfilters WHERE servername='$this->servername' $qsearch ORDER BY ID DESC";
			$results=$q->QUERY_SQL($sql,'artica_backup');
			if(!$q->ok){echo "$q->mysql_error\n";}
	
			while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){	
					$ligne["value"]=str_replace("+","\+",$ligne["value"]);
					$ligne["value"]=str_replace(".","\.",$ligne["value"]);
					$ligne["value"]=str_replace("?","\?",$ligne["value"]);
					$ligne["value"]=str_replace(":","\:",$ligne["value"]);
					$ligne["value"]=str_replace("(","\(",$ligne["value"]);
					$ligne["value"]=str_replace(")","\)",$ligne["value"]);
					$ligne["value"]=str_replace("[","\[",$ligne["value"]);
					$ligne["value"]=str_replace("]","\]",$ligne["value"]);
					$ligne["value"]=str_replace("*",".*?",$ligne["value"]);				
				if($ligne["key"]==null){

					$f[]="   SecRule REQUEST_LINE   \"{$ligne["value"]}\" \"deny,log,status:403,msg:'Rule: {$ligne["ID"]}:severity %{HIGHEST_SEVERITY}'\"";
				}else{
					if(preg_match("#(.+?)\s+([deny|pass|log|,]+)#",$ligne["value"],$re)){
						$pattern=$re[1];
						$action=$re[2];
						$f[]="SecRule {$ligne["key"]}   \"$pattern\" \"$action,status:403,msg:'Rule: {$ligne["ID"]}:severity %{HIGHEST_SEVERITY}'\"";
					}
					
					
				}
				   
				
				
			}
			
			$f[]="</IfModule>";
			$f[]="";			
			return @implode("\n",$f);
			
		}
		
		
		
		
		public function CheckWorkingDirectory(){
			
			if($this->IsGroupWareFromArtica()){
				$this->CheckDirectoryRights();
				return;
			}
			
			if($this->groupware=="ZARAFA"){
				$this->WORKING_DIRECTORY="/var/www/$this->servername";
				$this->InstallZarafa();
				$this->CheckDirectoryRights();
				return;
			}
			
			if($this->groupware=="ZARAFA_MOBILE"){
				$this->WORKING_DIRECTORY="/var/www/$this->servername";
				$this->InstallZarafaMobile();
				$this->CheckDirectoryRights();
				return;
			}			
			
			if($this->groupware=="Z-PUSH"){
				$this->WORKING_DIRECTORY="/usr/share/z-push";
				shell_exec("/bin/chmod 777 /usr/share/z-push/state");
				return;
			}
			
			
			if($this->groupware=="POWERADMIN"){
				$this->WORKING_DIRECTORY="/usr/share/poweradmin";
				return;
			}
			
			if($this->servername=="_default_"){
				if($GLOBALS["VERBOSE"]){echo "Starting......: Apache \"$this->servername\" DEBUG: is a default website (L.".__LINE__.")\n";} 
				if(trim($this->www_dir)<>null){$this->WORKING_DIRECTORY=trim($this->www_dir);return;}
				$this->WORKING_DIRECTORY="/var/www/html";return;
			}
			
			if($this->CheckWorkingDirectoryLoop()){return;}
			if($this->CheckWorkingDirectoryLVM()){return;}

			
			if(trim($this->www_dir)<>null){
				$this->WORKING_DIRECTORY="$this->www_dir";
				if(strpos($this->WORKING_DIRECTORY,$this->servername)==0){$this->WORKING_DIRECTORY="$this->WORKING_DIRECTORY/$this->servername";}
				$this->CheckDirectoryRights();
				return;
			}
			
			$this->WORKING_DIRECTORY="/var/www/$this->servername";
			$this->CheckDirectoryRights();
			
		}
		
		private function CheckWorkingDirectoryLoop(){
			if($this->UseLoopDisk==0){return false;}
			if(!is_dir($this->LoopMounts)){
				if($this->AS_ROOT){echo "Starting......: Apache \"$this->servername\" $this->LoopMounts no such directory\n";}
				return false;
			}
			
			$this->WORKING_DIRECTORY=$this->LoopMounts;
			$this->CheckDirectoryRights();
		}
		
		
		private function CheckWorkingDirectoryLVM(){
			$sock=new sockets();
			$vgservices=unserialize(base64_decode($sock->GET_INFO("vgservices")));
			if($vgservices["freewebs"]==null){return false;}
			if($this->lvm_size==0){return false;}
			if($this->lvm_vg==null){return false;}
			if($this->AS_ROOT){
				echo "Starting......: Apache \"$this->servername\" stored on an LVM group $this->lvm_vg partition ({$this->lvm_size}MB)\n";
				$lvm=new lvm_root();
				if(!$lvm->CreateLVS($this->lvm_vg,$this->servername,$this->lvm_size,"$this->lvm_vg-$this->servername")){return false;}
				echo "Starting......: Apache \"$this->servername\" $this->lvm_vg/$this->lvm_vg-$this->servername success\n";
			}
			$this->WORKING_DIRECTORY="/automounts/$this->lvm_vg-$this->servername";
			if($this->AS_ROOT){
				exec("/bin/ls -lah $this->WORKING_DIRECTORY 2>&1",$results);
				if($GLOBALS["VERBOSE"]){while (list ($num, $line) = each ($results)){echo "CheckWorkingDirectoryLVM() -> $line\n";}}
				$this->CheckDirectoryRights();
			}
			return true;
			
			
		}
		
		public function IsGroupWareFromArtica(){
			if($this->groupware=="ARTICA_USR"){$this->WORKING_DIRECTORY="/usr/share/artica-postfix/user-backup";return true;}
			if($this->groupware=="ARTICA_ADM"){$this->WORKING_DIRECTORY="/usr/share/artica-postfix";return true;}
			if($this->groupware=="ARTICA_REGISTER_OU"){$this->WORKING_DIRECTORY="/usr/share/artica-postfix";return true;}
			if($this->groupware=="ARTICA_MINIADM"){$this->WORKING_DIRECTORY="/usr/share/artica-postfix";return true;}
			return false;
			
		}
		
		
		private function CheckDirectoryRights(){
			if(!$this->AS_ROOT){return;}
			$this->WORKING_DIRECTORY=str_replace("//","/",$this->WORKING_DIRECTORY);
			echo "Starting......: Apache \"$this->servername\" working directory on $this->WORKING_DIRECTORY\n";
			if(!is_dir($this->WORKING_DIRECTORY)){@mkdir($this->WORKING_DIRECTORY,666,true);}
			if(!is_dir($this->WORKING_DIRECTORY)){
				echo "Starting......: Apache \"$this->servername\" working directory failed !!!\n";
				return;
			}
			
			
			if(!is_dir("$this->WORKING_DIRECTORY/php_logs")){@mkdir("$this->WORKING_DIRECTORY/php_logs",666,true);}
			if(!is_dir("$this->WORKING_DIRECTORY/www_logs")){@mkdir("$this->WORKING_DIRECTORY/www_logs",666,true);}
			
			if(!is_dir("/var/log/apache2")){@mkdir("/var/log/apache2",666,true);}
			
			$this->chown($this->WORKING_DIRECTORY);
			
			if(strlen($this->APACHE_RUN_USER)>3){
				shell_exec("/bin/chown -R $this->APACHE_RUN_USER:$this->APACHE_RUN_GROUP /var/log/apache2 2>&1 &");
			}
			
			
			shell_exec("/bin/chmod -R 755 $this->WORKING_DIRECTORY/* 2>&1 &");
			shell_exec("/bin/chmod 755 $this->WORKING_DIRECTORY");		
			
			
		}
		
		public function CheckDefaultPage(){
		if(!$this->AS_ROOT){return;}
		if($this->IsGroupWareFromArtica()){return;}
		$wel[]="<?php";
		$wel[]="echo \"<H1>It Works!</H1>\";";
		$wel[]="while (list(\$index,\$line)=each(\$_SERVER)){";
		$wel[]="echo \"<H2 style='font-size:16px'>\$index:<span style='color:red'>\$line</span></H2>\";";
		$wel[]=";}";
		$wel[]="?>";			
		$index_php=@implode("\n",$wel);	
		if($this->groupware=="cachemgr"){$index_php="<?php echo header('location:cgi-bin/cachemgr.cgi');die();?>";}
			
			if(!is_file("$this->WORKING_DIRECTORY/index.php")){
				@file_put_contents("$this->WORKING_DIRECTORY/index.php",$index_php);
			}			
			
		}
		
		public function ScriptAliases(){
			if($this->groupware=="cachemgr"){	
				@mkdir("$this->WORKING_DIRECTORY/cgi-bin",0755,true);
				shell_exec("/bin/cp -f $this->SQUID_CACHMGR $this->WORKING_DIRECTORY/cgi-bin/cachemgr.cgi");
				$f[]="<Directory \"$this->WORKING_DIRECTORY/cgi-bin\">";
				$f[]="\tOptions -Indexes FollowSymLinks";
				$f[]="\tAllowOverride AuthConfig FileInfo";
				$f[]="\tOrder allow,deny";
				$f[]="\tAllow from all";
				$f[]="</Directory>";
				$f[]="";
				$f[]="ScriptAlias /cgi-bin/cachemgr.cgi $this->WORKING_DIRECTORY/cgi-bin/cachemgr.cgi";
	        	$f[]="<location \"/cgi-bin/cachemgr.cgi\">";
				$f[]="\torder allow,deny";
				$f[]=$this->AllowFrom();
	        	$f[]="</location>"; 
			}			
			return @implode("\n",$f);
			
		}
		
		public function JkMount(){
			if(!$this->APACHE_MOD_TOMCAT){return null;}
			if(!isset($this->Params["JkMount"])){return null;}
			if($this->Params["JkMount"]==0){return null;}
			$users=new usersMenus();
			if($users->OPENEMM_INSTALLED){
				$sock=new sockets();
				$OpenEMMEnable=$sock->GET_INFO("OpenEMMEnable");
				if(!is_numeric($OpenEMMEnable)){$OpenEMMEnable=1;}
				if($OpenEMMEnable==1){return;}
			}	
			
			if($this->AS_ROOT){echo "Starting......: Apache \"$this->servername\" Adding tomcat connector\n";}
			$f[]="\t<IfModule mod_jk.c>";
			$f[]="\t\tJkMount / default";
			$f[]="\t\tJkMount /* default";
			$f[]="\t\tJkMount /*.jsp ajp13_worker";
			$f[]="\t\tJkMount /*.jsp ajp13_worker";
			$f[]="\t\tJkMount /*.cfm ajp13_worker";
			$f[]="\t\tJkMount /*.cfc ajp13_worker";
			$f[]="\t\tJkMount /*.do ajp13_worker";
			$f[]="\t\tJkMount /*.jsp ajp13_worker";
			$f[]="\t\tJkMount /*.cfchart ajp13_worker";
			$f[]="\t\tJkMount /*.cfres ajp13_worker";
   			$f[]="\t\tJkLogFile /var/log/apache2/mod_jk.log";
			$f[]="\t</IfModule>";
			return @implode("\n", $f);
		}
		
		
		public function AllowFrom(){
			
			if(!isset($this->Params["LimitByIp"]["enabled"])){$this->Params["LimitByIp"]["enabled"]=0;}
			$fc=array();
			if($GLOBALS["VERBOSE"]){echo "DEBUG::AllowFrom:: LimitByIp:{$this->Params["LimitByIp"]["enabled"]}\n";}
			if($this->LimitByIp==1){
				$hash=$this->LimitByIp_list();
				if($GLOBALS["VERBOSE"]){echo "DEBUG::AllowFrom:: ".count($hash)." items\n";}
				while (list ($num, $ligne) = each ($hash) ){
					if($ligne==null){continue;}
					if(is_array($ligne)){continue;}
					if($GLOBALS["VERBOSE"]){echo "DEBUG::AllowFrom:: allow from $ligne\n";}
					$fc[]="\t\tallow from $ligne";
				}
			}
			
			
			if($GLOBALS["VERBOSE"]){echo "DEBUG::AllowFrom::  ".count($fc)." items\n";}
			if(count($fc)==0){return "\t\tallow from all";}else{ return @implode("\n",$fc)."\n";}
			return @implode("\n",$fc)."\n";
		}
		
		public function WebdavHeader(){
				if(!isset($this->Params["WEBDAV"]["ENABLED"])){$this->Params["WEBDAV"]["ENABLED"]=0;}
				if($this->Params["WEBDAV"]["ENABLED"]==0){return null;}
				$conf[]="\tDavLockDB \"$this->WORKING_DIRECTORY/DavLock\"";
				$conf[]="\tBrowserMatch \"Microsoft Data Access Internet Publishing Provider\" redirect-carefully";
				$conf[]="\tBrowserMatch \"MS FrontPage\" redirect-carefully";
				$conf[]="\tBrowserMatch \"^WebDrive\" redirect-carefully";
				$conf[]="\tBrowserMatch \"^WebDAVFS/1.[0123]\" redirect-carefully";
				$conf[]="\tBrowserMatch \"^gnome-vfs/1.0\" redirect-carefully";
				$conf[]="\tBrowserMatch \"^XML Spy\" redirect-carefully";
				$conf[]="\tBrowserMatch \"^Dreamweaver-WebDAV-SCM1\" redirect-carefully";
				return @implode("\n",$conf);				
			
		}
		
		public function open_basedir(){
				if($this->groupware=="GROUPOFFICE"){
					$f[]="/home/$this->servername";
					$f[]="/home/$this->servername/cache";
				}
				$f[]="/usr/share/php";
				$f[]="/usr/share/php5";
				$f[]="/var/lighttpd/upload";
			
			if($this->IsGroupWareFromArtica()){
				$f[]="/usr/share/artica-postfix/ressources";
				$f[]="/usr/share/artica-postfix/framework";
				$f[]="/usr/share/artica-postfix/user-backup";
				$f[]="/usr/share/artica-postfix/user-backup/ressources";
				$f[]="/etc/ssl/certs/mysql-client-download";
			}
			
			if(($this->groupware=="ZARAFA") OR ($this->groupware=="ZARAFA_MOBILE") OR ($this->groupware=="Z-PUSH")){
				$f[]="/usr/share/php/mapi";
				$f[]="/usr/share/php5/mapi";
			}
			
			
			$f[]=$this->WORKING_DIRECTORY;
			return @implode(":",$f);
			include_once("/usr/share/artica-postfix/framework/class.unix.inc");
			$unix=new unix();
			$GLOBALS["MAX_RECUSIVE_DIRS"]=40;
			$GLOBALS["RECURSVIVE_INC"]=0;
			$dirs=$unix->getDirectories($this->WORKING_DIRECTORY);
			while (list ($num, $ligne) = each ($dirs) ){$f[]=$ligne;}
			
			return @implode(":",$f);
		}
		
		public function Aliases(){
			$already=array();
			if($this->groupware=="Z-PUSH"){
				$f[]="Alias /Microsoft-Server-ActiveSync $this->WORKING_DIRECTORY/index.php";
				$already["/Microsoft-Server-ActiveSync"]=true;
			}
			
			$sql="SELECT * FROM freewebs_aliases WHERE servername='$this->servername' ORDER BY alias";
			$q=new mysql();
			$results=$q->QUERY_SQL($sql,'artica_backup');
			while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
				$Alias=trim($ligne["alias"]);
				$directory=trim($ligne["directory"]);
				if($Alias==null){continue;}
				$regex=false;
				if(preg_match("#^REGEX:(.+)#", $Alias,$re)){$Alias=$re[1];$regex=true;}
				
				if(substr($Alias, 0,1)<>'/'){$Alias="/$Alias";}
				if(substr($directory, 0,1)<>'/'){$directory="/$directory";}
				if(isset($already[$Alias])){continue;}
				$directory=$this->WORKING_DIRECTORY.$directory;
				$already[$Alias]=true;
				if(!$regex){$f[]="Alias $Alias $directory";}else{$f[]="AliasMatch $Alias $directory";}
			}
			
			return @implode("\n",$f);
			
		}
		
		private function UserInSystem($uid){
			if(!isset($GLOBALS["UserInSystem"][$uid])){
				$unix=new unix();
				$id=$unix->find_program("id");
				exec("$id \"$uid\" 2>&1",$results);
				writelogs("$id \"$uid\" 2>&1 ". count($results)." rows",__FUNCTION__,__FILE__,__LINE__);
				$datas=trim(@implode("", $results));
				if(!preg_match("#uid=([0-9]+).+?#", $datas)){
					$GLOBALS["UserInSystem"][$uid]=false;
					return false;}
				$GLOBALS["UserInSystem"][$uid]=true;
				return true;
			}else{
				return $GLOBALS["UserInSystem"][$uid];
			}
		}
		
		
		public function chown($directory){
			if(!is_dir($directory)){echo "Starting......: Apache \"$this->servername\" chown \"$directory\" no such directory\n";return;}
			
			if(!isset($GLOBALS["CHOWNCOMMANDSFREEWBS"])){
				$apacheusername=$this->APACHE_RUN_USER;
				$apachegroup=$this->APACHE_RUN_GROUP;
				
				
				if($this->Params["MOD_FCGID"]["enabled"]==1){
					if($this->uid<>null){
						if($this->UserInSystem($this->uid)){
							$user=new user($this->uid);
							$apacheusername=$user->uidNumber;
							$apachegroup=$user->group_id;
						}
					}
				}
				
				$unix=new unix();
				$chown=$unix->find_program("chown");
				$GLOBALS["CHOWNCOMMANDSFREEWBS"]["CHOWN"]="$chown";
				$GLOBALS["CHOWNCOMMANDSFREEWBS"]["USER"]="$apacheusername";
				$GLOBALS["CHOWNCOMMANDSFREEWBS"]["GROUP"]="$apachegroup";
			}

			if($GLOBALS["CHOWNCOMMANDSFREEWBS"]["USER"]==null){
				echo "Starting......: Apache \"$this->servername\" chown \"$directory\" no such user\n";
				return;
			}
			
			echo "Starting......: Apache \"$this->servername\" set permissions for {$GLOBALS["CHOWNCOMMANDSFREEWBS"]["USER"]} on \"$directory\"\n"; 
			shell_exec("{$GLOBALS["CHOWNCOMMANDSFREEWBS"]["CHOWN"]}  {$GLOBALS["CHOWNCOMMANDSFREEWBS"]["USER"]}:{$GLOBALS["CHOWNCOMMANDSFREEWBS"]["GROUP"]} \"$directory\"");
			shell_exec("{$GLOBALS["CHOWNCOMMANDSFREEWBS"]["CHOWN"]} -R {$GLOBALS["CHOWNCOMMANDSFREEWBS"]["USER"]}:{$GLOBALS["CHOWNCOMMANDSFREEWBS"]["GROUP"]} \"$directory\" &");	
			
			
		}
		
		public function mod_fcgid(){
				$apacheusername=$this->APACHE_RUN_USER;
				$apachegroup=$this->APACHE_RUN_GROUP;			
			
				if($this->Params["MOD_FCGID"]["enabled"]==0){return null;}
				$unix=new unix();
				$target_fcgi_path="/var/www/php-fcgi-scripts/$this->servername/php-fcgi-starter";
				$php_ini_path="/var/www/php-fcgi-scripts/$this->servername/php.ini";
				$fa[]="\t<IfModule mod_fcgid.c>";
				if($this->uid<>null){
					if($this->UserInSystem($this->uid)){
						$apacheusername=$user;
						$apachegroup=$user;
						$user=$this->uid;$group=$this->uid;
						$fa[]="\t\tSuexecUserGroup $user $group";
					}
				}
				
				$fa[]="\t\tFcgidBusyTimeout {$this->Params["MOD_FCGID"]["FcgidBusyTimeout"]}";
				$fa[]="\t\tFcgidIdleTimeout {$this->Params["MOD_FCGID"]["FcgidIdleTimeout"]}";
				$fa[]="\t\tFcgidMaxRequestInMem {$this->Params["MOD_FCGID"]["FcgidMaxRequestInMem"]}";
				$fa[]="\t\tFcgidMaxProcessesPerClass {$this->Params["MOD_FCGID"]["FcgidMaxProcessesPerClass"]}";
				$fa[]="\t\tFcgidMaxRequestLen {$this->Params["MOD_FCGID"]["FcgidMaxRequestLen"]}";
				$fa[]="\t\tFcgidOutputBufferSize {$this->Params["MOD_FCGID"]["FcgidOutputBufferSize"]}";
				
				$fa[]="\t\t<IfModule mod_php5.c>";
				$fa[]="\t\t\tphp_admin_flag engine off";
				$fa[]="\t\t</IfModule>";

	
				$fa[]="\t\t\tAddHandler fcgid-script .fcgi .php .phtml .php3 .php4 .php5 .php6";
				$fa[]="\t\t\tFCGIWrapper $target_fcgi_path .php";
				$fa[]="\t\t\tFCGIWrapper $target_fcgi_path .phtml";
				$fa[]="\t\t\tFCGIWrapper $target_fcgi_path .php3";
				$fa[]="\t\t\tFCGIWrapper $target_fcgi_path .php4";
				$fa[]="\t\t\tFCGIWrapper $target_fcgi_path .php5";
				$fa[]="\t\t\tFCGIWrapper $target_fcgi_path .php6";

				$phpfiles[]="php";
				$phpfiles[]="phtml";
				$phpfiles[]="inc";
				$phpfiles[]="php3";
				$phpfiles[]="php4";
				$phpfiles[]="php5";
				$phpfiles[]="php6";
				
				
				while (list ($num, $ext) = each ($phpfiles) ){				
						$fa[]="\t\t<Files *.$ext>";
						$fa[]="\t\t\tSetHandler fcgid-script";
						$fa[]="\t\t</Files>";
				}			
				
				$fa[]="\t</IfModule>";
				
				if(!is_dir("/var/www/php-fcgi-scripts/$this->servername")){
					@mkdir("/var/www/php-fcgi-scripts/$this->servername",755,true);
				}
				
				
				
				
				
				$phpini=$this->build_php_ini();
				@file_put_contents($php_ini_path, $phpini);
				
				$f[]="#!/bin/sh";
				$f[]="PHPRC=$php_ini_path";
				$f[]="export PHPRC";
				$f[]="export PHP_FCGI_MAX_REQUESTS={$this->Params["MOD_FCGID"]["PHP_FCGI_MAX_REQUESTS"]}";
				$f[]="export PHP_FCGI_CHILDREN={$this->Params["MOD_FCGID"]["PHP_FCGI_CHILDREN"]}";
				$f[]="exec $this->phpcgi\n";
				@file_put_contents($target_fcgi_path, @implode("\n", $f));
				$chmod=$unix->find_program("chmod");
				$chown=$unix->find_program("chown");
				shell_exec("$chown -R $apacheusername /var/www/php-fcgi-scripts/$this->servername >/dev/null");
				shell_exec("$chmod -R 755 /var/www/php-fcgi-scripts >/dev/null");
				return @implode("\n", $fa);
			
			
		}
		
		public function php_values(){
			
			$php_open_base_dir=$this->open_basedir();
			$includes[]=".";
			$includes[]="/usr/share/php";
			$includes[]="$this->WORKING_DIRECTORY";
			$includes[]="/usr/share/php5";
			$includes[]="/usr/local/share/php";
			$includes[]="/usr/share/php5/PEAR";
			$includes[]="/usr/share/pear";
			
			$this->Params["PHP_VALUES"]["include_path"]=@implode(":", $includes);
			$this->Params["PHP_VALUES"]["error_log"]="$this->WORKING_DIRECTORY/php_logs/php.log";
			

			if($php_open_base_dir<>null){$this->Params["PHP_VALUES"]["open_basedir"]=$php_open_base_dir;}
			

			if(($this->groupware=="ZARAFA") OR ($this->groupware=="ZARAFA_MOBILE")){
				$sock=new sockets();
				$ZarafaSessionTime=$sock->GET_INFO("ZarafaSessionTime");
				if(!is_numeric($ZarafaSessionTime)){$ZarafaSessionTime=1440;}
				$this->Params["PHP_VALUES"]["magic_quotes_gpc"]="Off";
				$this->Params["PHP_VALUES"]["register_globals"]="Off";
				$this->Params["PHP_VALUES"]["session.gc_maxlifetime"]=$ZarafaSessionTime;	
			}
			
			if($this->groupware=="Z-PUSH"){
				$sock=new sockets();
				$ZarafaSessionTime=$sock->GET_INFO("ZarafaSessionTime");
				if(!is_numeric($ZarafaSessionTime)){$ZarafaSessionTime=1440;}				
				$this->Params["PHP_VALUES"]["magic_quotes_gpc"]="Off";
				$this->Params["PHP_VALUES"]["register_globals"]="Off";
				$this->Params["PHP_VALUES"]["magic_quotes_runtime"]="Off";
				$this->Params["PHP_VALUES"]["short_open_tag"]="On";
				$this->Params["PHP_VALUES"]["session.gc_maxlifetime"]=$ZarafaSessionTime;	
			}
			
			if($this->groupware=="DRUPAL"){
				$this->Params["PHP_VALUES"]["session.auto_start"]="0";
				$this->Params["PHP_VALUES"]["xdebug.show_exception_trace"]="0";
				$this->Params["PHP_VALUES"]["session.cache_limiter"]="nocache";
				$this->Params["PHP_VALUES"]["magic_quotes_gpc"]="Off";
				$this->Params["PHP_VALUES"]["allow_url_fopen"]="On";

			}
			
			if($this->groupware=="GROUPOFFICE"){
				$this->Params["PHP_VALUES"]["output_buffering"]="Off";
				$this->Params["PHP_VALUES"]["magic_quotes_gpc"]="Off";
				$this->Params["PHP_VALUES"]["session.cache_limiter"]="nocache";
				$this->Params["PHP_VALUES"]["magic_quotes_gpc"]="Off";
				$this->Params["PHP_VALUES"]["allow_url_fopen"]="On";				
				$f[]="\tphp_admin_flag engine \"on\"";
        		$f[]="\tphp_admin_value open_basedir \"none\"";
        		$f[]="UseCanonicalName On";
				
			}
			
		if($this->groupware=="JOOMLA17"){
			$this->Params["PHP_VALUES"]["register_globals"]="Off";
			$this->Params["PHP_VALUES"]["magic_quotes_gpc"]="On";
			$this->Params["PHP_VALUES"]["upload_max_filesize"]="100M";
			$this->Params["PHP_VALUES"]["post_max_size"]="100M";
		}
		
		if($this->groupware=="LMB"){
			$this->Params["PHP_VALUES"]["error_reporting"]="0";
			$this->Params["PHP_VALUES"]["allow_url_fopen"]="On";
			$this->Params["PHP_VALUES"]["allow_url_include"]="On";
			$this->Params["PHP_VALUES"]["allow_url_ftp_get"]="On";
			$this->Params["PHP_VALUES"]["magic_quotes_gpc"]="Off";
		}
			
			
			if($this->groupware=="EYEOS"){
				$f[]="php_value error_reporting   \"E_ALL & ~E_NOTICE\"";
				$f[]="php_value display_errors   Off ";
				$f[]="php_value max_execution_time   30 ";
				$f[]="php_value max_input_time   60 ";
				$f[]="php_value memory_limit   128M ";
				$f[]="php_value post_max_size   200M ";
				$f[]="php_value upload_max_filesize   100M ";
				$f[]="php_value allow_url_fopen   On ";
				$f[]="php_value safe_mode   Off ";
				$f[]="php_value short_open_tag   On ";
				$f[]="php_value magic_quotes_runtime   Off ";
				$f[]="php_value file_uploads   On ";				
				
			}
			
		if(is_array($this->Params["PHP_VALUES"])){
			$array=$this->Params["PHP_VALUES"];
			while (list ($key, $val) = each ($array) ){
				$f[]="\tphp_value $key \"$val\"";
			}
		
		}
			if($this->Params["MOD_FCGID"]["enabled"]==1){return null;}
			return @implode("\n",$f);
		}
		
		public function DirectoryIndex(){
			if($this->groupware=="ARTICA_USR"){return "logon.php";}
			if($this->groupware=="ARTICA_ADM"){return "logon.php";}
			if($this->groupware=="ARTICA_REGISTER_OU"){return "miniadm.register.php";}
			if($this->groupware=="ARTICA_MINIADM"){return "miniadm.php";}		
			return "index.php index.html";
			
		}
		
		public function FilesRestrictions(){
			if(!$this->IsGroupWareFromArtica()){
				if($this->AS_ROOT){echo "Starting......: Apache \"$this->servername\" not an artica groupware interface\n";}
				return null;
			}
			if($this->groupware=="ARTICA_ADM"){
				$array["miniadm.register.php"]="miniadm.register.php";
			}
			if($this->groupware=="ARTICA_USR"){return null;}
			if($this->groupware=="ARTICA_REGISTER_OU"){
				$array=$this->ParseArticaDirectory();
				if($this->AS_ROOT){echo "Starting......: Apache \"$this->servername\" restrictions on ". count($array). " files\n";}
				unset($array["miniadm.register.php"]);
			}
			
			
			if($this->groupware=="ARTICA_MINIADM"){
				$array["miniadm.register.php"]="miniadm.register.php";
				$array["index.php"]="index.php";
				$array["logoff.php"]="logoff.php";
				$array["logon.php"]="logon.php";
				
			}
			
			if(is_array($array)){
				while (list ($num, $file) = each ($array) ){
					$f[]="<Files \"$file\">";
					$f[]="\tOrder allow,deny";
					$f[]="\tDeny from all";
					$f[]="</Files>";
				}
			}
			return @implode("\n",$f);
						
			
		}
		
		
		private function ParseArticaDirectory(){
			if(isset($GLOBALS["ARTICA_FILES_BAN"])){return $GLOBALS["ARTICA_FILES_BAN"];}
			if($this->AS_ROOT){echo "Starting......: Apache \"$this->servername\" parsing /usr/share/artica-postfix directory\n";}
			foreach (glob("/usr/share/artica-postfix/*.php") as $filename) {
				$f=basename($filename);
				$GLOBALS["ARTICA_FILES_BAN"][$f]=$f;
			}
			
			if($this->AS_ROOT){echo "Starting......: Apache \"$this->servername\" ". count($GLOBALS["ARTICA_FILES_BAN"]). " files\n";}
			return $GLOBALS["ARTICA_FILES_BAN"];
		}
		
		
		public function mod_rewrite(){
			
			if($this->groupware=="DRUPAL"){
				$f[]="\t<IfModule mod_rewrite.c>";
				$f[]="\t\tRewriteEngine on";
   			 	$f[]="\t\tRewriteBase /";
   			 	$f[]="\t\tRewriteCond %{REQUEST_URI} !=/". md5($this->servername);
			   	$f[]="\t\tRewriteCond %{REQUEST_FILENAME} !-f";
			   	$f[]="\t\tRewriteCond %{REQUEST_FILENAME} !-d";
			   	$f[]="\t\tRewriteRule ^(.*)$ index.php?q=$1 [L,QSA]";
			   	$f[]=$this->mod_rewrite;
			   	$f[]="</IfModule>";
			   	return @implode("\n",$f);
				
			}
			
			if($this->groupware=="JOOMLA17"){
				$f[]="\t<IfModule mod_rewrite.c>";
				$f[]="\t\tRewriteEngine On";
				$f[]="\t\tRewriteCond %{REQUEST_URI} !=/". md5($this->servername);
				$f[]="\t\tRewriteCond %{QUERY_STRING} base64_encode[^(]*\([^)]*\) [OR]";
				$f[]="\t\tRewriteCond %{QUERY_STRING} (<|%3C)([^s]*s)+cript.*(>|%3E) [NC,OR]";
				$f[]="\t\tRewriteCond %{QUERY_STRING} GLOBALS(=|\[|\%[0-9A-Z]{0,2}) [OR]";
				$f[]="\t\tRewriteCond %{QUERY_STRING} _REQUEST(=|\[|\%[0-9A-Z]{0,2})";
				$f[]="\t\tRewriteRule .* index.php [F]";
				$f[]="\t\tRewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]";
				$f[]="\t\tRewriteCond %{REQUEST_URI} !^/index\.php";
				$f[]="\t\tRewriteCond %{REQUEST_URI} /component/|(/[^.]*|\.(php|html?|feed|pdf|vcf|raw))$ [NC]";
				$f[]="\t\tRewriteCond %{REQUEST_FILENAME} !-f";
				$f[]="\t\tRewriteCond %{REQUEST_FILENAME} !-d";
				$f[]="\t\tRewriteRule .* index.php [L]";
				$f[]=$this->mod_rewrite;				
				$f[]="</IfModule>";
			   	return @implode("\n",$f);
			}
			
			if(trim($this->mod_rewrite)==null){return;}
			$f[]="\t<IfModule mod_rewrite.c>";
			$f[]="\t\tRewriteEngine On";
			$f[]="\t\tRewriteCond %{REQUEST_URI} !=/". md5($this->servername);			
			$f[]=$this->mod_rewrite;
			$f[]="</IfModule>";
			return @implode("\n",$f);
			
			
		}
		
		public function mod_cache(){
			if(!$this->AS_ROOT){return;}
			if(!isset($this->Params["MOD_CACHE"]["Modenabled"])){$this->Params["MOD_CACHE"]["Modenabled"]=0;}
			if($this->Params["MOD_CACHE"]["Modenabled"]==0){
				if($this->AS_ROOT){echo "Starting......: Apache \"$this->servername\" Cache is not enabled\n";}
				return null;
			}
			if(!is_numeric($this->Params["MOD_CACHE"]["CacheDefaultExpire"])){$this->Params["MOD_CACHE"]["CacheDefaultExpire"]=3600;}
			if(!is_numeric($this->Params["MOD_CACHE"]["CacheMinExpire"])){$this->Params["MOD_CACHE"]["CacheMinExpire"]=3600;}
			if(!is_numeric($this->Params["MOD_CACHE"]["CacheMinFileSize"])){$this->Params["MOD_CACHE"]["CacheMinFileSize"]=64;}
			if(!is_numeric($this->Params["MOD_CACHE"]["CacheMaxFileSize"])){$this->Params["MOD_CACHE"]["CacheMaxFileSize"]=64000;}			
			

	
			echo "Starting......: Apache \"$this->servername\" Cache is enabled\n";
			$f[]="\t<IfModule mod_cache.c>";
			$f[]="\t\t<IfModule mod_disk_cache.c>";
			$f[]="\t\t\tCacheEnable disk /";
			$f[]="\t\t\tCacheRoot \"/var/cache/apache/$this->servername\"";
			$f[]="\t\t\tCacheDirLevels 5";
			$f[]="\t\t\tCacheDirLength 4";
			
			if(isset($this->Params["MOD_CACHE"]["CacheDisable"])){
				$array=$this->Params["MOD_CACHE"]["CacheDisable"];
				if(is_array($array)){
					while (list ($num, $ligne) = each ($array) ){
						echo "Starting......: Apache \"$this->servername\" CacheDisable \"$num\"\n";
						if($num==null){continue;}
						$f[]="\t\t\tCacheDisable $num";
					}
				}else{
					echo "Starting......: Apache \"$this->servername\" CacheDisable no row\n";
				}
			}else{
				echo "Starting......: Apache \"$this->servername\" CacheDisable not filled\n"; 
				
			}			
			
			$f[]="\t\t\tCacheMaxFileSize {$this->Params["MOD_CACHE"]["CacheMaxFileSize"]}";
			$f[]="\t\t\tCacheMinFileSize {$this->Params["MOD_CACHE"]["CacheMinFileSize"]}";
			$f[]="\t\t\tCacheIgnoreCacheControl On";
			$f[]="\t\t\tCacheIgnoreNoLastMod On";
			$f[]="\t\t\tCacheIgnoreQueryString Off";
			$f[]="\t\t\tCacheIgnoreHeaders None";
			$f[]="\t\t\tCacheLastModifiedFactor 0.2";
			$f[]="\t\t\tCacheDefaultExpire {$this->Params["MOD_CACHE"]["CacheDefaultExpire"]}";
			//$f[]="\t\t\tCacheMinExpire {$this->Params["MOD_CACHE"]["CacheMinExpire"]}";
			$f[]="\t\t\tCacheStoreNoStore Off";
			$f[]="\t\t\tCacheStorePrivate On";
			//$f[]="\t\t\tCacheStaleOnError On";
			//$f[]="\t\t\tCacheStoreExpired On";
			$f[]="\t\t\tCacheIgnoreQueryString Off";
			$f[]="\t\t\tCacheIgnoreNoLastMod On";
			$f[]="\t\t</IfModule>";
			$f[]="\t</IfModule>";
			
			$f[]="\t<IfModule mod_headers.c>";
			$f[]="\t\t<IfModule mod_expires.c>";
			$f[]="\t\t\tExpiresActive On";
			$f[]="\t\t</IfModule>";
			$f[]="\t\tHeader unset Pragma";
			$f[]="\t\tFileETag None";
			$f[]="\t\tHeader unset ETag";
			$f[]="\t\t<FilesMatch \"\.(ico|pdf|flv|jpg|jpeg|png|gif|swf|mp3|mp4)\$\">";
			$f[]="\t\t\tHeader set Cache-Control \"public, no-transform\"";
			$f[]="\t\t\tHeader set Expires A1209600";
			$f[]="\t\t\tHeader unset Last-Modified";
			$f[]="\t\t</FilesMatch>";
			$f[]="\t</IfModule>";
			$f[]="";
			
			if(!is_dir("/var/cache/apache/$this->servername")){
				@mkdir("/var/cache/apache/$this->servername",755,true);
				$sock=new sockets();
				$apacheusername=$sock->GET_INFO('APACHE_SRC_ACCOUNT');
				echo "Starting......: Apache \"$this->servername\" cache enabled with apache user $apacheusername\n";				
				shell_exec("/bin/chown $apacheusername:$apacheusername /var/cache/apache/$this->servername");
			}
			return @implode("\n", $f);
		}
		
		
		public function WebDav(){
			
			if(!isset($this->Params["WEBDAV"]["ENABLED"])){$this->Params["WEBDAV"]["ENABLED"]=0;}
			if($this->Params["WEBDAV"]["ENABLED"]==0){
				if($this->AS_ROOT){echo "Starting......: Apache \"$this->servername\" WebDAV is not enabled\n";}
				return null;
			}
			if($GLOBALS["VERBOSE"]){echo "DEBUG::WebDav:: WebDav enabled\n";}
			$users=new usersMenus();
			if(!$users->APACHE_MODE_WEBDAV){
				if($GLOBALS["VERBOSE"]){echo "DEBUG::WebDav:: WebDav module not installed\n";}
				return null;
			}
			
			$ldap=new clladp();
			if($this->AS_ROOT){echo "Starting......: Apache \"$this->servername\" WebDav is enabled\n";}
         	$f[]="DAV On";
          	$f[]="DAVMinTimeout 600";
           	$f[]="AuthType Basic";
           	$f[]="AuthBasicProvider ldap";
           	$f[]="AuthName \"$this->servername Users Only\"";
           	$f[]="AuthLDAPURL ldap://$ldap->ldap_host:$ldap->ldap_port/dc=organizations,$ldap->suffix?uid?sub";
           	$f[]="AuthLDAPBindDN cn=$ldap->ldap_admin,$ldap->suffix";
           	$f[]="AuthLDAPBindPassword $ldap->ldap_password";
           	
          	$f[]="AuthLDAPGroupAttribute memberUid";
          	if(!is_array($this->Params["WEBDAV"]["MEMBERS"])){
          		if($this->AS_ROOT){echo "Starting......: Apache \"$this->servername\" Authentication for ". count($this->Params["WEBDAV"]["MEMBERS"])." items\n";}
          		while (list ($member, $asnull) = each ($this->Params["WEBDAV"]["MEMBERS"]) ){
          			if($member==null){continue;}
          			if(preg_match("#@(.+):([0-9]+)$#",$member,$re)){
          				$groups=new groups($re[2]);
          				$f[]="Require group $groups->dn";
          			}else{
          				$f[]="Require user $member";
          			}
          		}
          		
          	}
			$f[]="Require valid-user";		
			return @implode("\n",$f);
			
		}
		
		private function DeleteLVM(){
			$sock=new sockets();
			$vgservices=unserialize(base64_decode($sock->GET_INFO("vgservices")));
			if($vgservices["freewebs"]==null){return true;}
			if($this->lvm_vg==null){return true;}	
			$lvm=new lvm_root();	
			if(!$lvm->DeleteLVS($this->lvm_vg,$this->servername,"$this->lvm_vg-$this->servername")){return false;}	
			return true;
		}
		
		function delete(){
			if(!$this->DeleteLVM()){return;}
			$sql="SELECT * FROM freeweb WHERE servername='$this->servername'";
			$q=new mysql();
			$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));	
			$database=$ligne["mysql_database"];
			if(strlen($database)>0){
				$q->DELETE_DATABASE($database);
				if(!$q->ok){
					echo "Unable to delete DB $database\n";
					return;
				}
				
				$sql="DELETE FROM `db` WHERE `Db`='$database'";
				$q->QUERY_SQL($sql,'mysql');
			}
			
			
			$sql="DELETE FROM `awstats` WHERE `website`='$this->servername'";
			$q->QUERY_SQL($sql,'artica_backup');
			if(!$q->ok){echo $q->mysql_error;}				
			
			if($ligne["mysql_username"]<>null){
				if($q->COUNT_DB_FROM_USER($ligne["mysql_username"])==0){
					$q->DELETE_USER_INMYSQL($ligne["mysql_username"]);
				}
				
			}
			
			
			$sql="DELETE FROM freeweb WHERE servername='$this->servername'";
			$q->QUERY_SQL($sql,"artica_backup");
			if(!$q->ok){echo $q->mysql_error;return;}
			
			$sql="DELETE FROM awstats_files WHERE `servername`='$this->servername'";
			$q->QUERY_SQL($sql,"artica_backup");
			
			$sql="DELETE FROM freewebs_proxy WHERE `servername`='$this->servername'";
			$q->QUERY_SQL($sql,"artica_backup");		

			$sql="DELETE FROM freewebs_aliases WHERE `servername`='$this->servername'";
			$q->QUERY_SQL($sql,"artica_backup");				
			
			
			
			$ldap=new clladp();
			$dn="dc=$this->servername,dc=pureftpd,dc=organizations,$ldap->suffix";
			if($ldap->ExistsDN($dn)){$ldap->ldap_delete($dn,true);}
			
			$sock=new sockets();
			$sock->getFrameWork("cmd.php?pure-ftpd-users=yes");	
			$sock->getFrameWork("cmd.php?freeweb-restart=yes");
			
			
		}
		
		private function InstallZarafa(){
			if(!is_dir($this->WORKING_DIRECTORY)){@mkdir($this->WORKING_DIRECTORY,644,true);}
			if(is_dir($this->WORKING_DIRECTORY)){shell_exec("/bin/rm -rf $this->WORKING_DIRECTORY/*");}
			echo "Starting......: Apache $this->servername installing Zarafa\n";
			shell_exec("/bin/rm -f /usr/share/zarafa-webaccess/zarafa-webaccess >/dev/null 2>&1");
			shell_exec("/bin/cp -rf /usr/share/zarafa-webaccess/* $this->WORKING_DIRECTORY/");
			@mkdir($this->WORKING_DIRECTORY."/tmp",755,true);
			@unlink("$this->WORKING_DIRECTORY/config.php");
			$this->InstallZarafaConfigPHP();
			echo "Starting......: Apache $this->servername installing Zarafa Webmail done...\n";
		}
		
		private function InstallZarafaMobile(){
			if(!is_dir($this->WORKING_DIRECTORY)){@mkdir($this->WORKING_DIRECTORY,644,true);}
			if(is_dir($this->WORKING_DIRECTORY)){shell_exec("/bin/rm -rf $this->WORKING_DIRECTORY/*");}
			echo "Starting......: Apache $this->servername installing Zarafa\n";
			shell_exec("/bin/rm -f /usr/share/zarafa-webaccess-mobile/zarafa-webaccess-mobile >/dev/null 2>&1");
			@mkdir("$this->WORKING_DIRECTORY/cache",755,true);
			@mkdir("$this->WORKING_DIRECTORY/templates_c",755,true);			
			@mkdir($this->WORKING_DIRECTORY."/tmp",755,true);
			@mkdir($this->WORKING_DIRECTORY."/config",755,true);
			shell_exec("/bin/cp -rf /usr/share/zarafa-webaccess-mobile/* $this->WORKING_DIRECTORY/");
			echo "Starting......: Apache $this->servername installing Zarafa-webaccess-mobile done...\n";	
			$this->InstallZarafaMobileConfigPHP("$this->WORKING_DIRECTORY");	
			
		}
		
		private function InstallZarafaMobileConfigPHP(){
			$f[]="<?php";
			$f[]="define(\"CONFIG_CHECK\", true);";
			$f[]="define(\"SERVER\", \"file:///var/run/zarafa\");";
			$f[]="define('BASE_PATH', dirname(\$_SERVER['SCRIPT_FILENAME']) . \"/\");";
			$f[]="set_include_path(BASE_PATH.\"include/\" . PATH_SEPARATOR . BASE_PATH . PATH_SEPARATOR . \".\" . PATH_SEPARATOR . \"/usr/share/php/\");";
			$f[]="define(\"COOKIE_EXPIRE\", 60*60*24*365);";
			$f[]="define(\"SMARTY_TEMPLATE_DIR\", \"render/html\");";
			$f[]="define(\"SMARTY_CONFIG_DIR\", \"$this->WORKING_DIRECTORY/config\");";
			$f[]="define(\"SMARTY_CACHE_DIR\",	\"$this->WORKING_DIRECTORY/cache\"); // must be writable";
			$f[]="define(\"SMARTY_COMPILE_DIR\", \"$this->WORKING_DIRECTORY/templates_c\"); // must be writable";
			$f[]="\$config[\"renderer\"]=\"html\";";
			$f[]="define('NOSENDERMSG', _(\"No sender\"));";
			$f[]="define('NOSUBJECTMSG', _(\"No subject\"));";
			$f[]="define('CELLSHOUR', 1);";
			$f[]="define('SCREENWIDTH', 240);";
			$f[]="\$GLOBALS[\"emailsonpage\"]=10;";
			$f[]="\$GLOBALS[\"appointmentsonpage\"]=10;";
			$f[]="\$GLOBALS[\"contactsonpage\"]=1000;";
			$f[]="\$GLOBALS[\"tasksonpage\"]=100;";
			$f[]="ini_set('post_max_size', \"31M\");";
			$f[]="ini_set('upload_max_filesize', \"30M\");";
			$f[]="ini_set('memory_limit', -1);  // -1 = no limit";
			$f[]="ini_set('max_execution_time', 300); // 5 minutes";
			$f[]="define('BLOCK_SIZE', 1048576);";
			$f[]="define('MAX_BODY_SIZE', 100 * 1024);	";
			$f[]="\$debug = false;";
			$f[]="";
			$f[]="	if (\$debug){";
			$f[]="		error_reporting(E_ALL);";
			$f[]="		ini_set(\"display_errors\", true);";
			$f[]="		if (file_exists(\"debug.php\")){";
			$f[]="			include (\"debug.php\");";
			$f[]="		}else{";
			$f[]="			// define empty dump function in case we still use it somewhere";
			$f[]="			function dump(){}";
			$f[]="		}	";
			$f[]="	}else{";
			$f[]="		ini_set(\"display_errors\", false);";
			$f[]="	}";
			$f[]="";
			$f[]="?>";	
			@unlink("$this->WORKING_DIRECTORY/config.php");	
			@file_put_contents("$this->WORKING_DIRECTORY/config.php",@implode("\n",$f));	
		}
		
		
		private function InstallZarafaConfigPHP($Otherdirectory=null){
				$sock=new sockets();
				$ZarafaAspellEnabled=$sock->GET_INFO("ZarafaAspellEnabled");
				$ZarafaWebNTLM=$sock->GET_INFO("ZarafaWebNTLM");	
				if(!is_numeric($ZarafaAspellEnabled)){$ZarafaAspellEnabled=0;}
				if(!is_numeric($ZarafaWebNTLM)){$ZarafaWebNTLM=0;}
				$users=new usersMenus();
				if(!$users->ASPELL_INSTALLED){$ZarafaAspellEnabled=0;}
			
				$conf[]="<?php";
				$conf[]="ini_set(\"zend.ze1_compatibility_mode\", false);";
				$conf[]="ini_set(\"max_execution_time\", 300); // 5 minutes";
				$conf[]="ini_set(\"display_errors\", false);";
				$conf[]="define(\"CONFIG_CHECK\", TRUE);";
				$conf[]="define(\"DEFAULT_SERVER\",\"file:///var/run/zarafa\");";
				$conf[]="define(\"SSLCERT_FILE\", NULL);";
				$conf[]="define(\"SSLCERT_PASS\", NULL);";
				if($ZarafaWebNTLM==1){
					$conf[]="define(\"LOGINNAME_STRIP_DOMAIN\", true);";
				}else{
					$conf[]="define(\"LOGINNAME_STRIP_DOMAIN\", false);";
				}
				$conf[]="if (isset(\$_GET[\"external\"]) && preg_match(\"/[a-z][a-z0-9_]+/i\",\$_GET[\"external\"])){define(\"COOKIE_NAME\",\$_GET[\"external\"]);}else{define(\"COOKIE_NAME\",\"ZARAFA_WEBACCESS\");}";
				$conf[]="define(\"THEME_COLOR\", \"default\");\$base_url = dirname(\$_SERVER[\"PHP_SELF\"]);if(substr(\$base_url,-1)!=\"/\") \$base_url .=\"/\";";
				$conf[]="define(\"BASE_URL\", \$base_url);";
				$conf[]="define(\"BASE_PATH\", dirname(\$_SERVER[\"SCRIPT_FILENAME\"]) . \"/\");";
				$conf[]="define(\"MIME_TYPES\", BASE_PATH . \"server/mimetypes.dat\");";
				$conf[]="define(\"TMP_PATH\", \"$this->WORKING_DIRECTORY/tmp\");";
				$conf[]="set_include_path(BASE_PATH. PATH_SEPARATOR . BASE_PATH.\"server/PEAR/\" .  PATH_SEPARATOR . \"/usr/share/php/\");";
				$conf[]="define(\"DIALOG_URL\", \"index.php?load=dialog&\");";
				$conf[]="define(\"DND_FILEUPLOAD_URL\", \"index.php?load=upload_attachment&\");";
				$conf[]="define(\"PATH_PLUGIN_DIR\", \"plugins\");";
				$conf[]="define(\"ENABLE_PLUGINS\", false);";
				$conf[]="define(\"DISABLED_PLUGINS_LIST\", \"\");";
				$conf[]="define(\"DISABLE_FULL_GAB\", false);";
				$conf[]="define(\"DISABLE_FULL_CONTACTLIST_THRESHOLD\", -1);";
				$conf[]="define(\"ENABLE_GAB_ALPHABETBAR\", false);";
				$conf[]="define(\"FREEBUSY_DAYBEFORE_COUNT\", 7);";
				$conf[]="define(\"FREEBUSY_NUMBEROFDAYS_COUNT\", 90);";
				$conf[]="define(\"BLOCK_SIZE\", 1048576);";
				$conf[]="define(\"CLIENT_TIMEOUT\", 5*60*1000);";
				$conf[]="define(\"EXPIRES_TIME\", 60*60*24*7*13);";
				$conf[]="define(\"UPLOADED_ATTACHMENT_MAX_LIFETIME\", 6*60*60);";
				$conf[]="define(\"FCKEDITOR_PATH\",dirname(\$_SERVER[\"SCRIPT_FILENAME\"]).\"/client/widgets/fckeditor\");";
				$conf[]="define(\"FCKEDITOR_JS_PATH\",\"client/widgets/fckeditor\");";
				
				if($ZarafaAspellEnabled==1){
					$asspellbin=$unix->find_program("aspell");
					$f[]="define(\"FCKEDITOR_SPELLCHECKER_ENABLED\", true);";
					$f[]="define(\"FCKEDITOR_SPELLCHECKER_PATH\", \"$asspellbin\");";	
					echo "Starting zarafa..............: Aspell checker is enabled\n";		
					
				}else{
					$f[]="define(\"FCKEDITOR_SPELLCHECKER_ENABLED\", false);";	
					$f[]="define(\"FCKEDITOR_SPELLCHECKER_PATH\", \"/usr/bin/aspell\");";
					echo "Starting zarafa..............: Aspell checker is disabled\n";
				}				
				

				$conf[]="define(\"FCKEDITOR_SPELLCHECKER_LANGUAGE\", FALSE); // set FALSE to use the language chosen by the user, but make sure that these languages are installed with aspell!";
				$conf[]="define(\"LANGUAGE_DIR\", \"server/language/\");";
				$conf[]="if (isset(\$_ENV[\"LANG\"]) && \$_ENV[\"LANG\"]!=\"C\"){";
				$conf[]="	define(\"LANG\", \$_ENV[\"LANG\"]); // This means the server environment language determines the web client language.";
				$conf[]="	}else{";
				$conf[]="define(\"LANG\", \"en_EN\"); // default fallback language";
				$conf[]="	}";
				$conf[]="";
				$conf[]="if (function_exists(\"date_default_timezone_set\")){date_default_timezone_set(\"Europe/London\");}";
				$conf[]="error_reporting(0);";
				$conf[]="if (file_exists(\"debug.php\")){include(\"debug.php\");}else{function dump(){}}";
				$conf[]="?>";
				if($Otherdirectory==null){$Otherdirectory=$this->WORKING_DIRECTORY;}
				@file_put_contents("$Otherdirectory/config.php",@implode("\n",$conf));			
		}
		
		
		function phpmyadmin(){
			echo "Starting......: Apache \"$this->servername\" directory \"$this->WORKING_DIRECTORY\"\n";
			$phpmyadmin_dir="$this->WORKING_DIRECTORY/phpmyadmin";
			if($this->IsGroupWareFromArtica()){return;}
			if($this->groupware=="ZARAFA"){return;}
			if($this->groupware=="ZARAFA_MOBILE"){return;}
			if(!is_numeric($this->useMysql)){$this->useMysql=0;}
			
			
			
			if($this->useMysql==0){
				echo "Starting......: Apache \"$this->servername\" Mysql is not enabled\n";
				if(is_dir("$phpmyadmin_dir")){
					echo "Starting......: Apache \"$this->servername\" removing $phpmyadmin_dir\n";
					shell_exec("/bin/rm -rf $phpmyadmin_dir");
				}
				return;
				
			}
			if(is_file("/usr/share/phpmyadmin/index.php")){
				echo "Starting......: Apache \"$this->servername\" installing phpmyadmin /usr/share/phpmyadmin/index.php no such file\n";
				return;
			}
			
			
			echo "Starting......: Apache \"$this->servername\" Mysql is enabled\n";
			echo "Starting......: Apache checks $phpmyadmin_dir\n";
			
			
			$q=new mysql();
			@mkdir("$phpmyadmin_dir",666,true);
			$blowfish_secret=md5($this->servername);
			shell_exec("/bin/cp -rf /usr/share/phpmyadmin/* $phpmyadmin_dir/ >/dev/null 2>&1");
			$f[]="<?php";
			$f[]="\$cfg['blowfish_secret'] = '$blowfish_secret';";
			$f[]="\$i = 0;";
			$f[]="\$i++;";
			$f[]="";
			$f[]="\$cfg['Servers'][\$i]['auth_type'] = 'cookie';";
			$f[]="\$cfg['Servers'][\$i]['host'] = '$q->mysql_server';";
			$f[]="\$cfg['Servers'][\$i]['port'] = '$q->mysql_port';";
			
			$f[]="\$cfg['Servers'][\$i]['connect_type'] = 'tcp';";
			$f[]="\$cfg['Servers'][\$i]['compress'] = false;";
			$f[]="\$cfg['Servers'][\$i]['extension'] = 'mysql';";
			$f[]="\$cfg['Servers'][\$i]['AllowNoPassword'] = false;";
			$f[]="\$cfg['Servers'][\$i]['bs_garbage_threshold'] = 50;";
			$f[]="\$cfg['Servers'][\$i]['bs_repository_threshold'] = '32M';";
			$f[]="\$cfg['Servers'][\$i]['bs_temp_blob_timeout'] = 600;";
			$f[]="\$cfg['Servers'][\$i]['bs_temp_log_threshold'] = '32M';";
			$f[]="\$cfg['Servers'][\$i]['only_db']='$this->mysql_database';";
			$f[]="\$cfg['Servers'][\$i]['hide_db'] = 'information_schema';";			
			$f[]="\$cfg['UploadDir'] = 'upload';";
			$f[]="\$cfg['SaveDir'] = 'upload';";
			$f[]="\$cfg['DefaultLang'] = 'en-utf-8';";
			$f[]="\$cfg['ServerDefault'] = 1;";
			$f[]="\$cfg['AllowUserDropDatabase']=FALSE;";
			$f[]="\$cfg['ShowPhpInfo']=true;";
			$f[]="\$cfg['DisplayDatabasesList']=FALSE;";
			$f[]="\$cfg['ShowServerInfo']=FALSE;";
			$f[]="\$cfg['ShowCreateDb']=FALSE;";
			$f[]="\$cfg['LeftDisplayServers']=FALSE;";
			$f[]="\$cfg['ShowBlob']=TRUE;";
			$f[]="\$cfg['DefaultTabServer']='main.php';";
			
			$f[]="?>\n";		
			
			$sock=new sockets();
			$apacheusername=$GLOBALS["apacheusername"];
			$varWwwPerms=$sock->GET_INFO("varWwwPerms");
			if($varWwwPerms==null){$varWwwPerms=755;}
			
			
			@file_put_contents("$phpmyadmin_dir/config.inc.php",@implode("\n",$f));
			shell_exec("/bin/chown root:root $phpmyadmin_dir/config.inc.php");
			shell_exec("/bin/chmod -R 755 $phpmyadmin_dir/phpmyadmin");
			shell_exec("/bin/chmod 644 $phpmyadmin_dir/phpmyadmin/config.inc.php");
			shell_exec("/bin/chown -R root:root $phpmyadmin_dir");
			@mkdir("$phpmyadmin_dir/upload");
			if($apacheusername==null){
				shell_exec("/bin/chmod $varWwwPerms /var/www &");
				shell_exec("/bin/chmod -R 777 $phpmyadmin_dir/upload");
			}else{
				shell_exec("/bin/chmod $varWwwPerms /var/www &");
				$this->chown($phpmyadmin_dir/upload);
				
			}
			unset($f);
		}			
	
	
	private function build_php_ini(){
			if(!$this->AS_ROOT){return;}
			$this->php_defaults();
			$this->php_values();
			
			$f=explode("\n",@file_get_contents($this->locate_php_ini()));
				while(list($index,$v) = each($f)){
		 			if(preg_match("#extension_dir.*?=(.+)#", $v,$re)){
		 				$this->Params["PHP_VALUES"]["extension_dir"]=trim($re[1]);
		 				break;
		 			}
	 			}			
			
			
			$f[]="[PHP]";
			$f[]="safe_mode = \"{$this->Params["PHP_VALUES"]["safe_mode"]}\"";
			$f[]="safe_mode_gid = \"{$this->Params["PHP_VALUES"]["safe_mode_gid"]}\"";
			$f[]="engine = \"{$this->Params["PHP_VALUES"]["engine"]}\"";
			$f[]="precision = \"{$this->Params["PHP_VALUES"]["precision"]}\"";
			$f[]="y2k_compliance = \"{$this->Params["PHP_VALUES"]["y2k_compliance"]}\"";
			$f[]="output_buffering = \"{$this->Params["PHP_VALUES"]["output_buffering"]}\"";
			$f[]="serialize_precision = \"{$this->Params["PHP_VALUES"]["serialize_precision"]}\"";
			$f[]="expose_php = \"{$this->Params["PHP_VALUES"]["expose_php"]}\"";
			$f[]="max_execution_time = \"{$this->Params["PHP_VALUES"]["max_execution_time"]}\"";
			$f[]="max_input_time = \"{$this->Params["PHP_VALUES"]["max_input_time"]}\"";
			$f[]="memory_limit = \"{$this->Params["PHP_VALUES"]["memory_limit"]}\"";
			$f[]="error_reporting = \"{$this->Params["PHP_VALUES"]["error_reporting"]}\"";
			$f[]="display_errors = \"{$this->Params["PHP_VALUES"]["display_errors"]}\"";
			$f[]="display_startup_errors = \"{$this->Params["PHP_VALUES"]["display_startup_errors"]}\"";
			$f[]="log_errors = \"{$this->Params["PHP_VALUES"]["log_errors"]}\"";
			$f[]="log_errors_max_len = \"{$this->Params["PHP_VALUES"]["log_errors_max_len"]}\"";
			$f[]="ignore_repeated_errors = \"{$this->Params["PHP_VALUES"]["ignore_repeated_errors"]}\"";
			$f[]="ignore_repeated_source = \"{$this->Params["PHP_VALUES"]["ignore_repeated_source"]}\"";
			$f[]="report_memleaks = \"{$this->Params["PHP_VALUES"]["report_memleaks"]}\"";
			$f[]="track_errors = \"{$this->Params["PHP_VALUES"]["track_errors"]}\"";
			$f[]="error_append_string = \"{$this->Params["PHP_VALUES"]["error_append_string"]}\"";
			$f[]="html_errors = \"{$this->Params["PHP_VALUES"]["html_errors"]}\"";
			$f[]="error_log = \"{$this->Params["PHP_VALUES"]["error_log"]}\"";
			$f[]="variables_order = \"{$this->Params["PHP_VALUES"]["variables_order"]}\"";
			$f[]="register_argc_argv = \"{$this->Params["PHP_VALUES"]["register_argc_argv"]}\"";
			$f[]="auto_globals_jit = \"{$this->Params["PHP_VALUES"]["auto_globals_jit"]}\"";
			$f[]="post_max_size = \"{$this->Params["PHP_VALUES"]["post_max_size"]}\"";
			$f[]="default_mimetype = \"{$this->Params["PHP_VALUES"]["default_mimetype"]}\"";
			$f[]="default_charset = \"{$this->Params["PHP_VALUES"]["default_charset"]}\"";
			$f[]="unicode.semantics = \"{$this->Params["PHP_VALUES"]["unicode.semantics"]}\"";
			$f[]="unicode.runtime_encoding = \"{$this->Params["PHP_VALUES"]["unicode.runtime_encoding"]}\"";
			$f[]="unicode.script_encoding = \"{$this->Params["PHP_VALUES"]["unicode.script_encoding"]}\"";
			$f[]="unicode.output_encoding = \"{$this->Params["PHP_VALUES"]["unicode.output_encoding"]}\"";
			$f[]="unicode.from_error_mode = \"{$this->Params["PHP_VALUES"]["unicode.from_error_mode"]}\"";
			$f[]="unicode.from_error_subst_char = \"{$this->Params["PHP_VALUES"]["unicode.from_error_subst_char"]}\"";
			$f[]="include_path = \"{$this->Params["PHP_VALUES"]["include_path"]}\"";
			$f[]="extension_dir = \"{$this->Params["PHP_VALUES"]["extension_dir"]}\"";
			$f[]="cgi.force_redirect = \"{$this->Params["PHP_VALUES"]["cgi.force_redirect"]}\"";
			$f[]="cgi.fix_pathinfo = \"{$this->Params["PHP_VALUES"]["cgi.fix_pathinfo"]}\"";
			$f[]="file_uploads = \"{$this->Params["PHP_VALUES"]["file_uploads"]}\"";
			$f[]="upload_tmp_dir = \"{$this->Params["PHP_VALUES"]["upload_tmp_dir"]}\"";
			$f[]="upload_max_filesize = \"{$this->Params["PHP_VALUES"]["upload_max_filesize"]}\"";
			$f[]="allow_url_fopen = \"{$this->Params["PHP_VALUES"]["allow_url_fopen"]}\"";
			$f[]="allow_url_include = \"{$this->Params["PHP_VALUES"]["allow_url_include"]}\"";
			$f[]="from = \"{$this->Params["PHP_VALUES"]["from"]}\"";
			$f[]="default_socket_timeout = \"{$this->Params["PHP_VALUES"]["default_socket_timeout"]}\"";
			$f[]="safe_mode = \"{$this->Params["PHP_VALUES"]["safe_mode"]}\"";
			$f[]="[Date]";
			$f[]="date.timezone = \"{$this->Params["PHP_VALUES"]["date.timezone"]}\"";
			$f[]="[filter]";
			$f[]="[iconv]";
			$f[]="iconv.input_encoding = \"{$this->Params["PHP_VALUES"]["iconv.input_encoding"]}\"";
			$f[]="iconv.internal_encoding = \"{$this->Params["PHP_VALUES"]["iconv.internal_encoding"]}\"";
			$f[]="iconv.output_encoding = \"{$this->Params["PHP_VALUES"]["iconv.output_encoding"]}\"";
			$f[]="[Syslog]";
			$f[]="define_syslog_variables = \"{$this->Params["PHP_VALUES"]["define_syslog_variables"]}\"";
			$f[]="[mail function]";
			$f[]="[SQL]";
			$f[]="sql.safe_mode = \"{$this->Params["PHP_VALUES"]["sql.safe_mode"]}\"";
			$f[]="[ODBC]";
			$f[]="odbc.allow_persistent = \"{$this->Params["PHP_VALUES"]["odbc.allow_persistent"]}\"";
			$f[]="odbc.check_persistent = \"{$this->Params["PHP_VALUES"]["odbc.check_persistent"]}\"";
			$f[]="odbc.max_persistent = \"{$this->Params["PHP_VALUES"]["odbc.max_persistent"]}\"";
			$f[]="odbc.max_links = \"{$this->Params["PHP_VALUES"]["odbc.max_links"]}\"";
			$f[]="odbc.defaultlrl = \"{$this->Params["PHP_VALUES"]["odbc.defaultlrl"]}\"";
			$f[]="odbc.defaultbinmode = \"{$this->Params["PHP_VALUES"]["odbc.defaultbinmode"]}\"";
			$f[]="[MySQL]";
			$f[]="mysql.allow_persistent = \"{$this->Params["PHP_VALUES"]["mysql.allow_persistent"]}\"";
			$f[]="mysql.max_persistent = \"{$this->Params["PHP_VALUES"]["mysql.max_persistent"]}\"";
			$f[]="mysql.max_links = \"{$this->Params["PHP_VALUES"]["mysql.max_links"]}\"";
			$q=new mysql();
			if($this->mysql_username==null){$mysql_username=$q->mysql_admin;$mysql_password=$q->mysql_password;}
			if($this->mysql_username<>null){$mysql_username=$this->mysql_username;$mysql_password=$this->mysql_password;}
			
			$f[]="mysql.default_port = \"$q->mysql_port\"";
			$f[]="mysql.default_socket = \"/var/run/mysqld/mysqld.sock\"";
			$f[]="mysql.default_host = \"$q->mysql_server\"";
			$f[]="mysql.default_user = \"$mysql_username\"";
			$f[]="mysql.default_password = \"$mysql_password\"";
			$f[]="mysql.connect_timeout = \"{$this->Params["PHP_VALUES"]["mysql.connect_timeout"]}\"";
			$f[]="mysql.trace_mode = \"{$this->Params["PHP_VALUES"]["mysql.trace_mode"]}\"";
			$f[]="[LDAP]";
			$f[]="ldap.max_links = \"{$this->Params["PHP_VALUES"]["ldap.max_links"]}\"";
			$f[]="ldap.allow_persistent = \"{$this->Params["PHP_VALUES"]["ldap.allow_persistent"]}\"";
			$f[]="ldap.check_persistent = \"{$this->Params["PHP_VALUES"]["ldap.check_persistent"]}\"";
			$f[]="[MySQLi]";
			$f[]="mysqli.max_links = \"{$this->Params["PHP_VALUES"]["mysqli.max_links"]}\"";
			$f[]="mysqli.default_port = \"$q->mysql_port\"";
			$f[]="mysqli.default_socket = \"/var/run/mysqld/mysqld.sock\"";
			$f[]="mysqli.default_host = \"$q->mysql_server\"";
			$f[]="mysqli.default_user = \"$mysql_username\"";
			$f[]="mysqli.default_pw = \"$mysql_password\"";
			$f[]="mysqli.reconnect = \"{$this->Params["PHP_VALUES"]["mysqli.reconnect"]}\"";
			$f[]="[mSQL]";
			$f[]="msql.allow_persistent = \"{$this->Params["PHP_VALUES"]["msql.allow_persistent"]}\"";
			$f[]="msql.max_persistent = \"{$this->Params["PHP_VALUES"]["msql.max_persistent"]}\"";
			$f[]="msql.max_links = \"{$this->Params["PHP_VALUES"]["msql.max_links"]}\"";
			$f[]="[OCI8]";
			$f[]="[PostgresSQL]";
			$f[]="[Sybase]";
			$f[]="[Sybase-CT]";
			$f[]="[bcmath]";
			$f[]="[browscap]";
			$f[]="[Informix]";
			$f[]="[Session]";
			$f[]="session.save_handler = \"{$this->Params["PHP_VALUES"]["session.save_handler"]}\"";
			$f[]="session.save_path = \"{$this->Params["PHP_VALUES"]["session.save_path"]}\"";
			$f[]="session.use_cookies = \"{$this->Params["PHP_VALUES"]["session.use_cookies"]}\"";
			$f[]="session.use_only_cookies = \"{$this->Params["PHP_VALUES"]["session.use_only_cookies"]}\"";
			$f[]="session.name = \"{$this->Params["PHP_VALUES"]["session.name"]}\"";
			$f[]="session.auto_start = \"{$this->Params["PHP_VALUES"]["session.auto_start"]}\"";
			$f[]="session.cookie_lifetime = \"{$this->Params["PHP_VALUES"]["session.cookie_lifetime"]}\"";
			$f[]="session.cookie_path = \"{$this->Params["PHP_VALUES"]["session.cookie_path"]}\"";
			$f[]="session.serialize_handler = \"{$this->Params["PHP_VALUES"]["session.serialize_handler"]}\"";
			$f[]="session.gc_probability = \"{$this->Params["PHP_VALUES"]["session.gc_probability"]}\"";
			$f[]="session.gc_divisor = \"{$this->Params["PHP_VALUES"]["session.gc_divisor"]}\"";
			$f[]="session.gc_maxlifetime = \"{$this->Params["PHP_VALUES"]["session.gc_maxlifetime"]}\"";
			$f[]="session.entropy_length = \"{$this->Params["PHP_VALUES"]["session.entropy_length"]}\"";
			$f[]="session.cache_limiter = \"{$this->Params["PHP_VALUES"]["session.cache_limiter"]}\"";
			$f[]="session.cache_expire = \"{$this->Params["PHP_VALUES"]["session.cache_expire"]}\"";
			$f[]="session.use_trans_sid = \"{$this->Params["PHP_VALUES"]["session.use_trans_sid"]}\"";
			$f[]="session.hash_function = \"{$this->Params["PHP_VALUES"]["session.hash_function"]}\"";
			$f[]="session.bug_compat_warn = \"{$this->Params["PHP_VALUES"]["session.bug_compat_warn"]}\"";
			$f[]="session.hash_bits_per_character = \"{$this->Params["PHP_VALUES"]["session.hash_bits_per_character"]}\"";
			$f[]="[MSSQL]";
			$f[]="mssql.allow_persistent = \"{$this->Params["PHP_VALUES"]["mssql.allow_persistent"]}\"";
			$f[]="mssql.max_persistent = \"{$this->Params["PHP_VALUES"]["mssql.max_persistent"]}\"";
			$f[]="mssql.max_links = \"{$this->Params["PHP_VALUES"]["mssql.max_links"]}\"";
			$f[]="mssql.min_error_severity = \"{$this->Params["PHP_VALUES"]["mssql.min_error_severity"]}\"";
			$f[]="mssql.min_message_severity = \"{$this->Params["PHP_VALUES"]["mssql.min_message_severity"]}\"";
			$f[]="mssql.compatability_mode = \"{$this->Params["PHP_VALUES"]["mssql.compatability_mode"]}\"";
			$f[]="mssql.connect_timeout = \"{$this->Params["PHP_VALUES"]["mssql.connect_timeout"]}\"";
			$f[]="mssql.timeout = \"{$this->Params["PHP_VALUES"]["mssql.timeout"]}\"";
			$f[]="mssql.textlimit = \"{$this->Params["PHP_VALUES"]["mssql.textlimit"]}\"";
			$f[]="mssql.textsize = \"{$this->Params["PHP_VALUES"]["mssql.textsize"]}\"";
			$f[]="mssql.batchsize = \"{$this->Params["PHP_VALUES"]["mssql.batchsize"]}\"";
			$f[]="mssql.datetimeconvert = \"{$this->Params["PHP_VALUES"]["mssql.datetimeconvert"]}\"";
			$f[]="mssql.secure_connection = \"{$this->Params["PHP_VALUES"]["mssql.secure_connection"]}\"";
			$f[]="mssql.max_procs = \"{$this->Params["PHP_VALUES"]["mssql.max_procs"]}\"";
			$f[]="mssql.charset = \"{$this->Params["PHP_VALUES"]["mssql.charset"]}\"";
			$f[]="[Assertion]";
			$f[]="[COM]";
			$f[]="[mbstring]";
			$f[]="[FrontBase]";
			$f[]="[gd]";
			$f[]="[exif]";
			$f[]="[Tidy]";
			$f[]="tidy.clean_output = \"{$this->Params["PHP_VALUES"]["tidy.clean_output"]}\"";
			$f[]="[soap]";
			$f[]="soap.wsdl_cache_ttl = \"{$this->Params["PHP_VALUES"]["soap.wsdl_cache_ttl"]}\"";
			
			if(!isset($GLOBALS["PHP_EXTENTIONS"])){
				$t=explode("\n",@file_get_contents($this->locate_php_ini()));
				while(list($index,$v) = each($t)){
					if(trim($v)==null){continue;}
		 			if(preg_match("#extension.*?=(.+?).so#", $v,$rie)){
		 				$GLOBALS["PHP_EXTENTIONS"][trim($rie[1])]="extension=".trim($rie[1]).".so";
		 			}
	 			}
			}
			
			
			print_r($GLOBALS["PHP_EXTENTIONS"]);
			
	 		$f[]=@implode("\n", $GLOBALS["PHP_EXTENTIONS"]);
	 		return @implode("\n", $f);
			
			
	}

	private function locate_php_ini(){
	  $f[]="/etc/php.ini";
	  $f[]="/etc/php5/cli/php.ini";
	  $f[]="/etc/php5/cgi/php.ini";
	  $f[]="/etc/php5/apache2/php.ini";
	  $f[]="/etc/php/php.ini";
	  $f[]="/etc/php-cgi-fcgi.ini";
	  $f[]="/etc/php5/fastcgi/php.ini"; 
	  while (list ($num, $filename) = each ($f) ){if(is_file($filename)){return $filename;}}
	}
	
	
public function php_defaults(){
		if(!isset($this->Params["PHP_VALUES"]["safe_mode"])){$this->Params["PHP_VALUES"]["safe_mode"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["safe_mode_gid"])){$this->Params["PHP_VALUES"]["safe_mode_gid"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["engine"])){$this->Params["PHP_VALUES"]["engine"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["precision"])){$this->Params["PHP_VALUES"]["precision"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["y2k_compliance"])){$this->Params["PHP_VALUES"]["y2k_compliance"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["output_buffering"])){$this->Params["PHP_VALUES"]["output_buffering"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["serialize_precision"])){$this->Params["PHP_VALUES"]["serialize_precision"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["expose_php"])){$this->Params["PHP_VALUES"]["expose_php"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["max_execution_time"])){$this->Params["PHP_VALUES"]["max_execution_time"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["max_input_time"])){$this->Params["PHP_VALUES"]["max_input_time"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["memory_limit"])){$this->Params["PHP_VALUES"]["memory_limit"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["error_reporting"])){$this->Params["PHP_VALUES"]["error_reporting"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["display_errors"])){$this->Params["PHP_VALUES"]["display_errors"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["display_startup_errors"])){$this->Params["PHP_VALUES"]["display_startup_errors"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["log_errors"])){$this->Params["PHP_VALUES"]["log_errors"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["log_errors_max_len"])){$this->Params["PHP_VALUES"]["log_errors_max_len"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["ignore_repeated_errors"])){$this->Params["PHP_VALUES"]["ignore_repeated_errors"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["ignore_repeated_source"])){$this->Params["PHP_VALUES"]["ignore_repeated_source"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["report_memleaks"])){$this->Params["PHP_VALUES"]["report_memleaks"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["track_errors"])){$this->Params["PHP_VALUES"]["track_errors"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["error_append_string"])){$this->Params["PHP_VALUES"]["error_append_string"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["html_errors"])){$this->Params["PHP_VALUES"]["html_errors"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["error_log"])){$this->Params["PHP_VALUES"]["error_log"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["variables_order"])){$this->Params["PHP_VALUES"]["variables_order"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["register_argc_argv"])){$this->Params["PHP_VALUES"]["register_argc_argv"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["auto_globals_jit"])){$this->Params["PHP_VALUES"]["auto_globals_jit"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["post_max_size"])){$this->Params["PHP_VALUES"]["post_max_size"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["default_mimetype"])){$this->Params["PHP_VALUES"]["default_mimetype"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["default_charset"])){$this->Params["PHP_VALUES"]["default_charset"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["unicode.semantics"])){$this->Params["PHP_VALUES"]["unicode.semantics"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["unicode.runtime_encoding"])){$this->Params["PHP_VALUES"]["unicode.runtime_encoding"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["unicode.script_encoding"])){$this->Params["PHP_VALUES"]["unicode.script_encoding"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["unicode.output_encoding"])){$this->Params["PHP_VALUES"]["unicode.output_encoding"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["unicode.from_error_mode"])){$this->Params["PHP_VALUES"]["unicode.from_error_mode"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["unicode.from_error_subst_char"])){$this->Params["PHP_VALUES"]["unicode.from_error_subst_char"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["include_path"])){$this->Params["PHP_VALUES"]["include_path"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["extension_dir"])){$this->Params["PHP_VALUES"]["extension_dir"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["cgi.force_redirect"])){$this->Params["PHP_VALUES"]["cgi.force_redirect"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["cgi.fix_pathinfo"])){$this->Params["PHP_VALUES"]["cgi.fix_pathinfo"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["file_uploads"])){$this->Params["PHP_VALUES"]["file_uploads"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["upload_tmp_dir"])){$this->Params["PHP_VALUES"]["upload_tmp_dir"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["upload_max_filesize"])){$this->Params["PHP_VALUES"]["upload_max_filesize"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["allow_url_fopen"])){$this->Params["PHP_VALUES"]["allow_url_fopen"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["allow_url_include"])){$this->Params["PHP_VALUES"]["allow_url_include"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["from"])){$this->Params["PHP_VALUES"]["from"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["default_socket_timeout"])){$this->Params["PHP_VALUES"]["default_socket_timeout"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["safe_mode"])){$this->Params["PHP_VALUES"]["safe_mode"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["date.timezone"])){$this->Params["PHP_VALUES"]["date.timezone"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["iconv.input_encoding"])){$this->Params["PHP_VALUES"]["iconv.input_encoding"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["iconv.internal_encoding"])){$this->Params["PHP_VALUES"]["iconv.internal_encoding"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["iconv.output_encoding"])){$this->Params["PHP_VALUES"]["iconv.output_encoding"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["define_syslog_variables"])){$this->Params["PHP_VALUES"]["define_syslog_variables"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["sql.safe_mode"])){$this->Params["PHP_VALUES"]["sql.safe_mode"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["odbc.allow_persistent"])){$this->Params["PHP_VALUES"]["odbc.allow_persistent"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["odbc.check_persistent"])){$this->Params["PHP_VALUES"]["odbc.check_persistent"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["odbc.max_persistent"])){$this->Params["PHP_VALUES"]["odbc.max_persistent"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["odbc.max_links"])){$this->Params["PHP_VALUES"]["odbc.max_links"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["odbc.defaultlrl"])){$this->Params["PHP_VALUES"]["odbc.defaultlrl"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["odbc.defaultbinmode"])){$this->Params["PHP_VALUES"]["odbc.defaultbinmode"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["mysql.allow_persistent"])){$this->Params["PHP_VALUES"]["mysql.allow_persistent"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["mysql.max_persistent"])){$this->Params["PHP_VALUES"]["mysql.max_persistent"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["mysql.max_links"])){$this->Params["PHP_VALUES"]["mysql.max_links"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["mysql.default_port"])){$this->Params["PHP_VALUES"]["mysql.default_port"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["mysql.default_socket"])){$this->Params["PHP_VALUES"]["mysql.default_socket"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["mysql.default_host"])){$this->Params["PHP_VALUES"]["mysql.default_host"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["mysql.default_user"])){$this->Params["PHP_VALUES"]["mysql.default_user"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["mysql.default_password"])){$this->Params["PHP_VALUES"]["mysql.default_password"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["mysql.connect_timeout"])){$this->Params["PHP_VALUES"]["mysql.connect_timeout"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["mysql.trace_mode"])){$this->Params["PHP_VALUES"]["mysql.trace_mode"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["ldap.max_links"])){$this->Params["PHP_VALUES"]["ldap.max_links"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["ldap.allow_persistent"])){$this->Params["PHP_VALUES"]["ldap.allow_persistent"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["ldap.check_persistent"])){$this->Params["PHP_VALUES"]["ldap.check_persistent"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["mysqli.max_links"])){$this->Params["PHP_VALUES"]["mysqli.max_links"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["mysqli.default_port"])){$this->Params["PHP_VALUES"]["mysqli.default_port"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["mysqli.default_socket"])){$this->Params["PHP_VALUES"]["mysqli.default_socket"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["mysqli.default_host"])){$this->Params["PHP_VALUES"]["mysqli.default_host"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["mysqli.default_user"])){$this->Params["PHP_VALUES"]["mysqli.default_user"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["mysqli.default_pw"])){$this->Params["PHP_VALUES"]["mysqli.default_pw"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["mysqli.reconnect"])){$this->Params["PHP_VALUES"]["mysqli.reconnect"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["msql.allow_persistent"])){$this->Params["PHP_VALUES"]["msql.allow_persistent"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["msql.max_persistent"])){$this->Params["PHP_VALUES"]["msql.max_persistent"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["msql.max_links"])){$this->Params["PHP_VALUES"]["msql.max_links"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["session.save_handler"])){$this->Params["PHP_VALUES"]["session.save_handler"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["session.save_path"])){$this->Params["PHP_VALUES"]["session.save_path"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["session.use_cookies"])){$this->Params["PHP_VALUES"]["session.use_cookies"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["session.use_only_cookies"])){$this->Params["PHP_VALUES"]["session.use_only_cookies"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["session.name"])){$this->Params["PHP_VALUES"]["session.name"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["session.auto_start"])){$this->Params["PHP_VALUES"]["session.auto_start"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["session.cookie_lifetime"])){$this->Params["PHP_VALUES"]["session.cookie_lifetime"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["session.cookie_path"])){$this->Params["PHP_VALUES"]["session.cookie_path"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["session.serialize_handler"])){$this->Params["PHP_VALUES"]["session.serialize_handler"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["session.gc_probability"])){$this->Params["PHP_VALUES"]["session.gc_probability"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["session.gc_divisor"])){$this->Params["PHP_VALUES"]["session.gc_divisor"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["session.gc_maxlifetime"])){$this->Params["PHP_VALUES"]["session.gc_maxlifetime"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["session.entropy_length"])){$this->Params["PHP_VALUES"]["session.entropy_length"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["session.cache_limiter"])){$this->Params["PHP_VALUES"]["session.cache_limiter"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["session.cache_expire"])){$this->Params["PHP_VALUES"]["session.cache_expire"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["session.use_trans_sid"])){$this->Params["PHP_VALUES"]["session.use_trans_sid"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["session.hash_function"])){$this->Params["PHP_VALUES"]["session.hash_function"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["session.bug_compat_warn"])){$this->Params["PHP_VALUES"]["session.bug_compat_warn"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["session.hash_bits_per_character"])){$this->Params["PHP_VALUES"]["session.hash_bits_per_character"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["mssql.allow_persistent"])){$this->Params["PHP_VALUES"]["mssql.allow_persistent"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["mssql.max_persistent"])){$this->Params["PHP_VALUES"]["mssql.max_persistent"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["mssql.max_links"])){$this->Params["PHP_VALUES"]["mssql.max_links"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["mssql.min_error_severity"])){$this->Params["PHP_VALUES"]["mssql.min_error_severity"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["mssql.min_message_severity"])){$this->Params["PHP_VALUES"]["mssql.min_message_severity"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["mssql.compatability_mode"])){$this->Params["PHP_VALUES"]["mssql.compatability_mode"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["mssql.connect_timeout"])){$this->Params["PHP_VALUES"]["mssql.connect_timeout"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["mssql.timeout"])){$this->Params["PHP_VALUES"]["mssql.timeout"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["mssql.textlimit"])){$this->Params["PHP_VALUES"]["mssql.textlimit"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["mssql.textsize"])){$this->Params["PHP_VALUES"]["mssql.textsize"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["mssql.batchsize"])){$this->Params["PHP_VALUES"]["mssql.batchsize"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["mssql.datetimeconvert"])){$this->Params["PHP_VALUES"]["mssql.datetimeconvert"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["mssql.secure_connection"])){$this->Params["PHP_VALUES"]["mssql.secure_connection"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["mssql.max_procs"])){$this->Params["PHP_VALUES"]["mssql.max_procs"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["mssql.charset"])){$this->Params["PHP_VALUES"]["mssql.charset"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["tidy.clean_output"])){$this->Params["PHP_VALUES"]["tidy.clean_output"]=null;}
		if(!isset($this->Params["PHP_VALUES"]["soap.wsdl_cache_ttl"])){$this->Params["PHP_VALUES"]["soap.wsdl_cache_ttl"]=null;}
		if($this->Params["PHP_VALUES"]["safe_mode_gid"]==null){$this->Params["PHP_VALUES"]["safe_mode_gid"]="Off";}
		if($this->Params["PHP_VALUES"]["engine"]==null){$this->Params["PHP_VALUES"]["engine"]="On";}
		if($this->Params["PHP_VALUES"]["precision"]==null){$this->Params["PHP_VALUES"]["precision"]="12";}
		if($this->Params["PHP_VALUES"]["y2k_compliance"]==null){$this->Params["PHP_VALUES"]["y2k_compliance"]="On";}
		if($this->Params["PHP_VALUES"]["output_buffering"]==null){$this->Params["PHP_VALUES"]["output_buffering"]="On";}
		if($this->Params["PHP_VALUES"]["serialize_precision"]==null){$this->Params["PHP_VALUES"]["serialize_precision"]="100";}
		if($this->Params["PHP_VALUES"]["expose_php"]==null){$this->Params["PHP_VALUES"]["expose_php"]="Off";}
		if($this->Params["PHP_VALUES"]["max_execution_time"]==null){$this->Params["PHP_VALUES"]["max_execution_time"]="3600";}
		if($this->Params["PHP_VALUES"]["max_input_time"]==null){$this->Params["PHP_VALUES"]["max_input_time"]="3600";}
		if($this->Params["PHP_VALUES"]["memory_limit"]==null){$this->Params["PHP_VALUES"]["memory_limit"]="500M";}
		if($this->Params["PHP_VALUES"]["error_reporting"]==null){$this->Params["PHP_VALUES"]["error_reporting"]="E_ALL & ~E_NOTICE";}
		if($this->Params["PHP_VALUES"]["display_errors"]==null){$this->Params["PHP_VALUES"]["display_errors"]="Off";}
		if($this->Params["PHP_VALUES"]["display_startup_errors"]==null){$this->Params["PHP_VALUES"]["display_startup_errors"]="Off";}
		if($this->Params["PHP_VALUES"]["log_errors"]==null){$this->Params["PHP_VALUES"]["log_errors"]="On";}
		if($this->Params["PHP_VALUES"]["log_errors_max_len"]==null){$this->Params["PHP_VALUES"]["log_errors_max_len"]="2048";}
		if($this->Params["PHP_VALUES"]["ignore_repeated_errors"]==null){$this->Params["PHP_VALUES"]["ignore_repeated_errors"]="Off";}
		if($this->Params["PHP_VALUES"]["ignore_repeated_source"]==null){$this->Params["PHP_VALUES"]["ignore_repeated_source"]="Off";}
		if($this->Params["PHP_VALUES"]["report_memleaks"]==null){$this->Params["PHP_VALUES"]["report_memleaks"]="On";}
		if($this->Params["PHP_VALUES"]["track_errors"]==null){$this->Params["PHP_VALUES"]["track_errors"]="Off";}
		if($this->Params["PHP_VALUES"]["error_append_string"]==null){$this->Params["PHP_VALUES"]["error_append_string"]="</code></font><br>";}
		if($this->Params["PHP_VALUES"]["html_errors"]==null){$this->Params["PHP_VALUES"]["html_errors"]="false";}
		if($this->Params["PHP_VALUES"]["error_log"]==null){$this->Params["PHP_VALUES"]["error_log"]="/usr/share/artica-postfix/ressources/logs/php.log";}
		if($this->Params["PHP_VALUES"]["variables_order"]==null){$this->Params["PHP_VALUES"]["variables_order"]="EGPCS";}
		if($this->Params["PHP_VALUES"]["register_argc_argv"]==null){$this->Params["PHP_VALUES"]["register_argc_argv"]="On";}
		if($this->Params["PHP_VALUES"]["auto_globals_jit"]==null){$this->Params["PHP_VALUES"]["auto_globals_jit"]="On";}
		if($this->Params["PHP_VALUES"]["post_max_size"]==null){$this->Params["PHP_VALUES"]["post_max_size"]="128M";}
		if($this->Params["PHP_VALUES"]["default_mimetype"]==null){$this->Params["PHP_VALUES"]["default_mimetype"]="text/html";}
		if($this->Params["PHP_VALUES"]["default_charset"]==null){$this->Params["PHP_VALUES"]["default_charset"]="utf-8";}
		if($this->Params["PHP_VALUES"]["unicode.semantics"]==null){$this->Params["PHP_VALUES"]["unicode.semantics"]="off";}
		if($this->Params["PHP_VALUES"]["unicode.runtime_encoding"]==null){$this->Params["PHP_VALUES"]["unicode.runtime_encoding"]="utf-8";}
		if($this->Params["PHP_VALUES"]["unicode.script_encoding"]==null){$this->Params["PHP_VALUES"]["unicode.script_encoding"]="utf-8";}
		if($this->Params["PHP_VALUES"]["unicode.output_encoding"]==null){$this->Params["PHP_VALUES"]["unicode.output_encoding"]="utf-8";}
		if($this->Params["PHP_VALUES"]["unicode.from_error_mode"]==null){$this->Params["PHP_VALUES"]["unicode.from_error_mode"]="U_INVALID_SUBSTITUTE";}
		if($this->Params["PHP_VALUES"]["unicode.from_error_subst_char"]==null){$this->Params["PHP_VALUES"]["unicode.from_error_subst_char"]="3f";}
		if($this->Params["PHP_VALUES"]["include_path"]==null){$this->Params["PHP_VALUES"]["include_path"]=".:/usr/share/php:/usr/share/obm:/usr/share/php5:/usr/share/obm2:/usr/local/share/php:/usr/share/artica-postfix/ressources/externals:/usr/share/artica-postfix/ressources/externals/Gdata:/usr/share/php5/PEAR:/usr/share/pear";}
		if($this->Params["PHP_VALUES"]["extension_dir"]==null){$this->Params["PHP_VALUES"]["extension_dir"]="/usr/lib/php5/20090626+lfs";}
		if($this->Params["PHP_VALUES"]["cgi.force_redirect"]==null){$this->Params["PHP_VALUES"]["cgi.force_redirect"]="1";}
		if($this->Params["PHP_VALUES"]["cgi.fix_pathinfo"]==null){$this->Params["PHP_VALUES"]["cgi.fix_pathinfo"]="1";}
		if($this->Params["PHP_VALUES"]["file_uploads"]==null){$this->Params["PHP_VALUES"]["file_uploads"]="On";}
		if($this->Params["PHP_VALUES"]["upload_tmp_dir"]==null){$this->Params["PHP_VALUES"]["upload_tmp_dir"]="/var/lighttpd/upload";}
		if($this->Params["PHP_VALUES"]["upload_max_filesize"]==null){$this->Params["PHP_VALUES"]["upload_max_filesize"]="256M";}
		if($this->Params["PHP_VALUES"]["allow_url_fopen"]==null){$this->Params["PHP_VALUES"]["allow_url_fopen"]="On";}
		if($this->Params["PHP_VALUES"]["allow_url_include"]==null){$this->Params["PHP_VALUES"]["allow_url_include"]="Off";}
		if($this->Params["PHP_VALUES"]["from"]==null){$this->Params["PHP_VALUES"]["from"]="anonymous@anonymous.com";}
		if($this->Params["PHP_VALUES"]["default_socket_timeout"]==null){$this->Params["PHP_VALUES"]["default_socket_timeout"]="60";}
		if($this->Params["PHP_VALUES"]["safe_mode"]==null){$this->Params["PHP_VALUES"]["safe_mode"]="Off";}
		if($this->Params["PHP_VALUES"]["date.timezone"]==null){$this->Params["PHP_VALUES"]["date.timezone"]="Europe/Belgrade";}
		if($this->Params["PHP_VALUES"]["iconv.input_encoding"]==null){$this->Params["PHP_VALUES"]["iconv.input_encoding"]="utf-8";}
		if($this->Params["PHP_VALUES"]["iconv.internal_encoding"]==null){$this->Params["PHP_VALUES"]["iconv.internal_encoding"]="utf-8";}
		if($this->Params["PHP_VALUES"]["iconv.output_encoding"]==null){$this->Params["PHP_VALUES"]["iconv.output_encoding"]="utf-8";}
		if($this->Params["PHP_VALUES"]["define_syslog_variables"]==null){$this->Params["PHP_VALUES"]["define_syslog_variables"]="Off";}
		if($this->Params["PHP_VALUES"]["sql.safe_mode"]==null){$this->Params["PHP_VALUES"]["sql.safe_mode"]="Off";}
		if($this->Params["PHP_VALUES"]["odbc.allow_persistent"]==null){$this->Params["PHP_VALUES"]["odbc.allow_persistent"]="On";}
		if($this->Params["PHP_VALUES"]["odbc.check_persistent"]==null){$this->Params["PHP_VALUES"]["odbc.check_persistent"]="On";}
		if($this->Params["PHP_VALUES"]["odbc.max_persistent"]==null){$this->Params["PHP_VALUES"]["odbc.max_persistent"]="-1";}
		if($this->Params["PHP_VALUES"]["odbc.max_links"]==null){$this->Params["PHP_VALUES"]["odbc.max_links"]="-1";}
		if($this->Params["PHP_VALUES"]["odbc.defaultlrl"]==null){$this->Params["PHP_VALUES"]["odbc.defaultlrl"]="4096";}
		if($this->Params["PHP_VALUES"]["odbc.defaultbinmode"]==null){$this->Params["PHP_VALUES"]["odbc.defaultbinmode"]="1";}
		if($this->Params["PHP_VALUES"]["mysql.allow_persistent"]==null){$this->Params["PHP_VALUES"]["mysql.allow_persistent"]="On";}
		if($this->Params["PHP_VALUES"]["mysql.max_persistent"]==null){$this->Params["PHP_VALUES"]["mysql.max_persistent"]="-1";}
		if($this->Params["PHP_VALUES"]["mysql.max_links"]==null){$this->Params["PHP_VALUES"]["mysql.max_links"]="-1";}
		if($this->Params["PHP_VALUES"]["mysql.default_port"]==null){$this->Params["PHP_VALUES"]["mysql.default_port"]="3306";}
		if($this->Params["PHP_VALUES"]["mysql.default_socket"]==null){$this->Params["PHP_VALUES"]["mysql.default_socket"]="/var/run/mysqld/mysqld.sock";}
		if($this->Params["PHP_VALUES"]["mysql.default_host"]==null){$this->Params["PHP_VALUES"]["mysql.default_host"]="127.0.0.1";}
		if($this->Params["PHP_VALUES"]["mysql.default_user"]==null){$this->Params["PHP_VALUES"]["mysql.default_user"]="admin";}
		if($this->Params["PHP_VALUES"]["mysql.default_password"]==null){$this->Params["PHP_VALUES"]["mysql.default_password"]="180872";}
		if($this->Params["PHP_VALUES"]["mysql.connect_timeout"]==null){$this->Params["PHP_VALUES"]["mysql.connect_timeout"]="60";}
		if($this->Params["PHP_VALUES"]["mysql.trace_mode"]==null){$this->Params["PHP_VALUES"]["mysql.trace_mode"]="Off";}
		if($this->Params["PHP_VALUES"]["ldap.max_links"]==null){$this->Params["PHP_VALUES"]["ldap.max_links"]="-1";}
		if($this->Params["PHP_VALUES"]["ldap.allow_persistent"]==null){$this->Params["PHP_VALUES"]["ldap.allow_persistent"]="On";}
		if($this->Params["PHP_VALUES"]["ldap.check_persistent"]==null){$this->Params["PHP_VALUES"]["ldap.check_persistent"]="On";}
		if($this->Params["PHP_VALUES"]["mysqli.max_links"]==null){$this->Params["PHP_VALUES"]["mysqli.max_links"]="-1";}
		if($this->Params["PHP_VALUES"]["mysqli.default_port"]==null){$this->Params["PHP_VALUES"]["mysqli.default_port"]="3306";}
		if($this->Params["PHP_VALUES"]["mysqli.default_socket"]==null){$this->Params["PHP_VALUES"]["mysqli.default_socket"]="/var/run/mysqld/mysqld.sock";}
		if($this->Params["PHP_VALUES"]["mysqli.default_host"]==null){$this->Params["PHP_VALUES"]["mysqli.default_host"]="127.0.0.1";}
		if($this->Params["PHP_VALUES"]["mysqli.default_user"]==null){$this->Params["PHP_VALUES"]["mysqli.default_user"]="admin";}
		if($this->Params["PHP_VALUES"]["mysqli.default_pw"]==null){$this->Params["PHP_VALUES"]["mysqli.default_pw"]="180872";}
		if($this->Params["PHP_VALUES"]["mysqli.reconnect"]==null){$this->Params["PHP_VALUES"]["mysqli.reconnect"]="Off";}
		if($this->Params["PHP_VALUES"]["msql.allow_persistent"]==null){$this->Params["PHP_VALUES"]["msql.allow_persistent"]="On";}
		if($this->Params["PHP_VALUES"]["msql.max_persistent"]==null){$this->Params["PHP_VALUES"]["msql.max_persistent"]="-1";}
		if($this->Params["PHP_VALUES"]["msql.max_links"]==null){$this->Params["PHP_VALUES"]["msql.max_links"]="-1";}
		if($this->Params["PHP_VALUES"]["session.save_handler"]==null){$this->Params["PHP_VALUES"]["session.save_handler"]="files";}
		if($this->Params["PHP_VALUES"]["session.save_path"]==null){$this->Params["PHP_VALUES"]["session.save_path"]="/var/lib/php5";}
		if($this->Params["PHP_VALUES"]["session.use_cookies"]==null){$this->Params["PHP_VALUES"]["session.use_cookies"]="1";}
		if($this->Params["PHP_VALUES"]["session.use_only_cookies"]==null){$this->Params["PHP_VALUES"]["session.use_only_cookies"]="1";}
		if($this->Params["PHP_VALUES"]["session.name"]==null){$this->Params["PHP_VALUES"]["session.name"]="PHPSESSID";}
		if($this->Params["PHP_VALUES"]["session.auto_start"]==null){$this->Params["PHP_VALUES"]["session.auto_start"]="0";}
		if($this->Params["PHP_VALUES"]["session.cookie_lifetime"]==null){$this->Params["PHP_VALUES"]["session.cookie_lifetime"]="0";}
		if($this->Params["PHP_VALUES"]["session.cookie_path"]==null){$this->Params["PHP_VALUES"]["session.cookie_path"]="/";}
		if($this->Params["PHP_VALUES"]["session.serialize_handler"]==null){$this->Params["PHP_VALUES"]["session.serialize_handler"]="php";}
		if($this->Params["PHP_VALUES"]["session.gc_probability"]==null){$this->Params["PHP_VALUES"]["session.gc_probability"]="1";}
		if($this->Params["PHP_VALUES"]["session.gc_divisor"]==null){$this->Params["PHP_VALUES"]["session.gc_divisor"]="100";}
		if($this->Params["PHP_VALUES"]["session.gc_maxlifetime"]==null){$this->Params["PHP_VALUES"]["session.gc_maxlifetime"]="1440";}
		if($this->Params["PHP_VALUES"]["session.entropy_length"]==null){$this->Params["PHP_VALUES"]["session.entropy_length"]="0";}
		if($this->Params["PHP_VALUES"]["session.cache_limiter"]==null){$this->Params["PHP_VALUES"]["session.cache_limiter"]="nocache";}
		if($this->Params["PHP_VALUES"]["session.cache_expire"]==null){$this->Params["PHP_VALUES"]["session.cache_expire"]="420";}
		if($this->Params["PHP_VALUES"]["session.use_trans_sid"]==null){$this->Params["PHP_VALUES"]["session.use_trans_sid"]="0";}
		if($this->Params["PHP_VALUES"]["session.hash_function"]==null){$this->Params["PHP_VALUES"]["session.hash_function"]="0";}
		if($this->Params["PHP_VALUES"]["session.bug_compat_warn"]==null){$this->Params["PHP_VALUES"]["session.bug_compat_warn"]="Off";}
		if($this->Params["PHP_VALUES"]["session.hash_bits_per_character"]==null){$this->Params["PHP_VALUES"]["session.hash_bits_per_character"]="4";}
		if($this->Params["PHP_VALUES"]["mssql.allow_persistent"]==null){$this->Params["PHP_VALUES"]["mssql.allow_persistent"]="On";}
		if($this->Params["PHP_VALUES"]["mssql.max_persistent"]==null){$this->Params["PHP_VALUES"]["mssql.max_persistent"]="-1";}
		if($this->Params["PHP_VALUES"]["mssql.max_links"]==null){$this->Params["PHP_VALUES"]["mssql.max_links"]="-1";}
		if($this->Params["PHP_VALUES"]["mssql.min_error_severity"]==null){$this->Params["PHP_VALUES"]["mssql.min_error_severity"]="10";}
		if($this->Params["PHP_VALUES"]["mssql.min_message_severity"]==null){$this->Params["PHP_VALUES"]["mssql.min_message_severity"]="10";}
		if($this->Params["PHP_VALUES"]["mssql.compatability_mode"]==null){$this->Params["PHP_VALUES"]["mssql.compatability_mode"]="Off";}
		if($this->Params["PHP_VALUES"]["mssql.connect_timeout"]==null){$this->Params["PHP_VALUES"]["mssql.connect_timeout"]="5";}
		if($this->Params["PHP_VALUES"]["mssql.timeout"]==null){$this->Params["PHP_VALUES"]["mssql.timeout"]="60";}
		if($this->Params["PHP_VALUES"]["mssql.textlimit"]==null){$this->Params["PHP_VALUES"]["mssql.textlimit"]="4096";}
		if($this->Params["PHP_VALUES"]["mssql.textsize"]==null){$this->Params["PHP_VALUES"]["mssql.textsize"]="4096";}
		if($this->Params["PHP_VALUES"]["mssql.batchsize"]==null){$this->Params["PHP_VALUES"]["mssql.batchsize"]="0";}
		if($this->Params["PHP_VALUES"]["mssql.datetimeconvert"]==null){$this->Params["PHP_VALUES"]["mssql.datetimeconvert"]="On";}
		if($this->Params["PHP_VALUES"]["mssql.secure_connection"]==null){$this->Params["PHP_VALUES"]["mssql.secure_connection"]="Off";}
		if($this->Params["PHP_VALUES"]["mssql.max_procs"]==null){$this->Params["PHP_VALUES"]["mssql.max_procs"]="-1";}
		if($this->Params["PHP_VALUES"]["mssql.charset"]==null){$this->Params["PHP_VALUES"]["mssql.charset"]="ISO-8859-1";}
		if($this->Params["PHP_VALUES"]["tidy.clean_output"]==null){$this->Params["PHP_VALUES"]["tidy.clean_output"]="Off";}
		if($this->Params["PHP_VALUES"]["soap.wsdl_cache_ttl"]==null){$this->Params["PHP_VALUES"]["soap.wsdl_cache_ttl"]="86400";}
		
		
		
		}	
		
	
	
	}
	
	
	
	
	
?>