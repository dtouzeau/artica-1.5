<?php
include_once(dirname(__FILE__).'/class.templates.inc');
include_once(dirname(__FILE__).'/class.ldap.inc');
include_once(dirname(__FILE__).'/class.mysql.inc');
include_once(dirname(__FILE__).'/class.user.inc');





class joomla{
	var $ou;
	var $params;
	var $sql_db;
	function GenPassword($ClearPassword){
		$salt		= $this->genRandomPassword(32);
		$crypt		= $this->getCryptedPassword($ClearPassword, $salt);
		$password	= $crypt.':'.$salt;
		return $password;
		
	}
	
	
	function joomla($ou=null){
		if($ou<>null){
			$this->ou=$ou;
			$name=str_replace('.','_',$ou);
			$name=str_replace('-','_',$name);
			$this->sql_db="joomla_".$name;
			$this->LoadSettings();
		}
		
	}
	
	
	function LoadSettings(){
		$sock=new sockets();
		$ini=new Bs_IniHandler();
		$ou=$this->ou;
		$name=str_replace('.','_',$ou);
		$name=str_replace('-','_',$name);
		writelogs("loading JoomlaConfOrg_$name",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);				
		$ini->loadString($sock->GET_INFO("JoomlaConfOrg_$name"));
		$this->params=$ini->_params;
		}
	
	function SaveParams(){
		$ini=new Bs_IniHandler();
		$ini->_params=$this->params;
		$ou=$this->ou;
		$name=str_replace('.','_',$ou);
		$name=str_replace('-','_',$name);			
		$sock=new sockets();
		$sock->SaveConfigFile($ini->toString(),"JoomlaConfOrg_$name");
		$this->SaveLDAPParams();
		$this->SaveAdminPassword();
		$sock->getfile('JoomlaReload');
		
		
	}
	
	function SaveAdminPasswordDatabase($database,$password){
		$sql="SELECT id FROM jos_users WHERE username=\"admin\"";
		$q=new mysql();	
		$newpass=$this->GenPassword($password);
		$nullDate=null;
		$installdate 	= date('Y-m-d H:i:s');
		$sql="SELECT gid FROM jos_users WHERE id=62";
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,'artica_backup'));
		if($ligne["gid"]==0){
			$query = "INSERT INTO jos_users VALUES (62, 'Administrator', 'admin', 'admin@localhost', '$newpass', 'Super Administrator', 0, 1, 25, '$installdate', '$nullDate', '', '')";	
			$q->QUERY_SQL($query,$database);
			if(!$q->ok){
				writelogs("set admin/password failed...",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);	
			}
			
			$query = "INSERT INTO jos_core_acl_aro VALUES (10,'users','62',0,'Administrator',0)";
			$q->QUERY_SQL($query,$database);
			if(!$q->ok){
				writelogs("set admin/password failed...",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);	
			}		
			$query = "INSERT INTO jos_core_acl_groups_aro_map VALUES (25,'',10)";		
			$q->QUERY_SQL($query,$database);
			if(!$q->ok){
				writelogs("set admin/password failed...",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);	
			}	
		}else{
			writelogs("updating $uid/password...",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);	
			$sql="UPDATE jos_users SET password='$cryptpass' WHERE id=62";
			$q->QUERY_SQL($query,$database);
			if(!$q->ok){
				writelogs("set admin/password failed...",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);	
			}				
		}		
		
		
	}
	
	
	
	function SaveAdminPassword(){
		$password=$this->params["CONF"]["joomlaadminpassword"];
		if($password==null){return null;}
		$q=new mysql();
		
		if(!$q->DATABASE_EXISTS($this->sql_db)){return null;}
		
		$sql="SELECT id FROM jos_users WHERE username=\"admin\"";
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,$this->sql_db)); 
		if(trim($ligne["id"]==null)){return null;}
		
		$newpass=$this->GenPassword($password);
		$sql="UPDATE jos_users SET password='$newpass' WHERE id={$ligne["id"]}";
		$q->QUERY_SQL($sql,$this->sql_db);
		
	}
	
	
	function SaveLDAPParams() {
		$ldap=new clladp();
		$q=new mysql();
		$dn="cn=$ldap->ldap_admin,$ldap->suffix";
		$password=$ldap->ldap_password;
		
			$conf="";		
			$conf=$conf."host=$ldap->ldap_host\n";
			$conf=$conf."port=$ldap->ldap_port\n";
			$conf=$conf."use_ldapV3=1\n";
			$conf=$conf."negotiate_tls=0\n";
			$conf=$conf."no_referrals=0\n";
			$conf=$conf."auth_method=search\n";
			$conf=$conf."base_dn=ou=users,ou=$this->ou,dc=organizations,$ldap->suffix\n";
			$conf=$conf."search_string=uid=[search]\n";
			$conf=$conf."users_dn=\n";
			$conf=$conf."username=$dn\n";
			$conf=$conf."password=$password\n";
			$conf=$conf."ldap_fullname=displayName\n";
			$conf=$conf."ldap_email=mail\n";
			$conf=$conf."ldap_uid=uid\n";
			$sql="UPDATE jos_plugins SET published=1,params=\"$conf\" WHERE id=2";
			
			writelogs($sql);
			$q=new mysql();
			if(!$q->QUERY_SQL($sql,$this->sql_db)){
				echo $q->mysql_error;
			}
		
		
	}
	
	
	
	private function getSalt($encryption = 'md5-hex', $seed = '', $plaintext = ''){
		// Encrypt the password.
		switch ($encryption)
		{
			case 'crypt' :
			case 'crypt-des' :
				if ($seed) {
					return substr(preg_replace('|^{crypt}|i', '', $seed), 0, 2);
				} else {
					return substr(md5(mt_rand()), 0, 2);
				}
				break;

			case 'crypt-md5' :
				if ($seed) {
					return substr(preg_replace('|^{crypt}|i', '', $seed), 0, 12);
				} else {
					return '$1$'.substr(md5(mt_rand()), 0, 8).'$';
				}
				break;

			case 'crypt-blowfish' :
				if ($seed) {
					return substr(preg_replace('|^{crypt}|i', '', $seed), 0, 16);
				} else {
					return '$2$'.substr(md5(mt_rand()), 0, 12).'$';
				}
				break;

			case 'ssha' :
				if ($seed) {
					return substr(preg_replace('|^{SSHA}|', '', $seed), -20);
				} else {
					return mhash_keygen_s2k(MHASH_SHA1, $plaintext, substr(pack('h*', md5(mt_rand())), 0, 8), 4);
				}
				break;

			case 'smd5' :
				if ($seed) {
					return substr(preg_replace('|^{SMD5}|', '', $seed), -16);
				} else {
					return mhash_keygen_s2k(MHASH_MD5, $plaintext, substr(pack('h*', md5(mt_rand())), 0, 8), 4);
				}
				break;

			case 'aprmd5' :
				/* 64 characters that are valid for APRMD5 passwords. */
				$APRMD5 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

				if ($seed) {
					return substr(preg_replace('/^\$apr1\$(.{8}).*/', '\\1', $seed), 0, 8);
				} else {
					$salt = '';
					for ($i = 0; $i < 8; $i ++) {
						$salt .= $APRMD5 {
							rand(0, 63)
							};
					}
					return $salt;
				}
				break;

			default :
				$salt = '';
				if ($seed) {
					$salt = $seed;
				}
				return $salt;
				break;
		}
	}	
	
	
	
function getCryptedPassword($plaintext, $salt = '', $encryption = 'md5-hex', $show_encrypt = false){
		// Get the salt to use.
		$salt = $this->getSalt($encryption, $salt, $plaintext);

		// Encrypt the password.
		switch ($encryption)
		{
			case 'plain' :
				return $plaintext;

			case 'sha' :
				$encrypted = base64_encode(mhash(MHASH_SHA1, $plaintext));
				return ($show_encrypt) ? '{SHA}'.$encrypted : $encrypted;

			case 'crypt' :
			case 'crypt-des' :
			case 'crypt-md5' :
			case 'crypt-blowfish' :
				return ($show_encrypt ? '{crypt}' : '').crypt($plaintext, $salt);

			case 'md5-base64' :
				$encrypted = base64_encode(mhash(MHASH_MD5, $plaintext));
				return ($show_encrypt) ? '{MD5}'.$encrypted : $encrypted;

			case 'ssha' :
				$encrypted = base64_encode(mhash(MHASH_SHA1, $plaintext.$salt).$salt);
				return ($show_encrypt) ? '{SSHA}'.$encrypted : $encrypted;

			case 'smd5' :
				$encrypted = base64_encode(mhash(MHASH_MD5, $plaintext.$salt).$salt);
				return ($show_encrypt) ? '{SMD5}'.$encrypted : $encrypted;

			case 'aprmd5' :
				$length = strlen($plaintext);
				$context = $plaintext.'$apr1$'.$salt;
				$binary = $this->_bin(md5($plaintext.$salt.$plaintext));

				for ($i = $length; $i > 0; $i -= 16) {
					$context .= substr($binary, 0, ($i > 16 ? 16 : $i));
				}
				for ($i = $length; $i > 0; $i >>= 1) {
					$context .= ($i & 1) ? chr(0) : $plaintext[0];
				}

				$binary = $this->_bin(md5($context));

				for ($i = 0; $i < 1000; $i ++) {
					$new = ($i & 1) ? $plaintext : substr($binary, 0, 16);
					if ($i % 3) {
						$new .= $salt;
					}
					if ($i % 7) {
						$new .= $plaintext;
					}
					$new .= ($i & 1) ? substr($binary, 0, 16) : $plaintext;
					$binary = $this->_bin(md5($new));
				}

				$p = array ();
				for ($i = 0; $i < 5; $i ++) {
					$k = $i +6;
					$j = $i +12;
					if ($j == 16) {
						$j = 5;
					}
					$p[] = $this->_toAPRMD5((ord($binary[$i]) << 16) | (ord($binary[$k]) << 8) | (ord($binary[$j])), 5);
				}

				return '$apr1$'.$salt.'$'.implode('', $p).$this->_toAPRMD5(ord($binary[11]), 3);

			case 'md5-hex' :
			default :
				$encrypted = ($salt) ? md5($plaintext.$salt) : md5($plaintext);
				return ($show_encrypt) ? '{MD5}'.$encrypted : $encrypted;
		}
	}
	
	private function _bin($hex){
		$bin = '';
		$length = strlen($hex);
		for ($i = 0; $i < $length; $i += 2) {
			$tmp = sscanf(substr($hex, $i, 2), '%x');
			$bin .= chr(array_shift($tmp));
		}
		return $bin;
	}
	
	private function _toAPRMD5($value, $count){
		/* 64 characters that are valid for APRMD5 passwords. */
		$APRMD5 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

		$aprmd5 = '';
		$count = abs($count);
		while (-- $count) {
			$aprmd5 .= $APRMD5[$value & 0x3f];
			$value >>= 6;
		}
		return $aprmd5;
	}	
	
private function genRandomPassword($length = 8){
		$salt = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$len = strlen($salt);
		$makepass = '';

		$stat = @stat(__FILE__);
		if(empty($stat) || !is_array($stat)) $stat = array(php_uname());

		mt_srand(crc32(microtime() . implode('|', $stat)));

		for ($i = 0; $i < $length; $i ++) {
			$makepass .= $salt[mt_rand(0, $len -1)];
		}

		return $makepass;
	}	
	
	
}

class SugarCRM{
	var $ou;
	var $sql_db;
	var $sql_admin;
	var $sql_password;
	var $sql_host;
	var $sugar_supposed_version;
	var $servername;
	var $params;
	var $manager_name;
	var $manager_password;
	
	function SugarCRM(){
		
	}
	
	
	
	function CreateAdminPassword($admin,$password){
		
		$id=$this->GetUserid($admin);
		writelogs("$admin ID=$id ".strlen($password)." password length",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		if($id==null){$this->CreateAdminUser($admin,$password);}else{$this->UpdateAdminPassword($id,$password);}
		
	}
	
	
	function UpdateLDAPConfig(){
		$ldap=new clladp();
		$this->SetLdapConfig('hostname',$ldap->ldap_host);
		$this->SetLdapConfig('port',$ldap->ldap_port);
		$this->SetLdapConfig('base_dn',"ou=$this->ou,dc=organizations,$ldap->suffix");
		$this->SetLdapConfig('bind_attr',"dn");
		$this->SetLdapConfig('login_attr',"uid");
		$this->SetLdapConfig('admin_user',"$ldap->ldap_admin,$ldap->suffix");
		$this->SetLdapConfig('admin_password',"$ldap->ldap_password");
		$this->SetLdapConfig('auto_create_users',"1");
		$this->SeSystemConfig('ldap_enabled','1');
		}
	
	function LoadSettings(){
		$sock=new sockets();
		$ini=new Bs_IniHandler();
		$ou=$this->ou;
		$name=str_replace('.','_',$ou);
		$name=str_replace('-','_',$name);		
		writelogs("loading JoomlaConfOrg_$name",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);	
		$ini->loadString($sock->GET_INFO("JoomlaConfOrg_$name"));
		$this->params=$ini->_params;			
	}
	
	public function TestTables(){
		$tables=array( "accounts","accounts_audit ","accounts_bugs","accounts_cases ","accounts_contacts","accounts_opportunities ",
		"acl_actions","acl_roles","acl_roles_actions","acl_roles_users","address_book ","bugs ","bugs_audit","calls","calls_contacts ",
		"calls_leads","calls_users","campaign_log ","campaign_trkrs ","campaigns","campaigns_audit","cases","cases_audit","cases_bugs",
		"config","contacts ","contacts_audit ","contacts_bugs","contacts_cases ","contacts_users ","currencies","custom_fields","dashboards",
		"document_revisions ","documents","email_addr_bean_rel","email_addresses","email_cache","email_marketing","email_marketing_prospect_lists",
		"email_templates","emailman ","emails","emails_beans ","emails_email_addr_rel","emails_text","feeds","fields_meta_data","folders","folders_rel",
		"folders_subscriptions","iframes","import_maps","inbound_email","inbound_email_autoreply","inbound_email_cache_ts ","leads","leads_audit",
		"linked_documents","meetings ","meetings_contacts","meetings_leads ","meetings_users ","notes","opportunities","opportunities_audit",
		"opportunities_contacts ","outbound_email ","project","project_task ","project_task_audit ","projects_accounts","projects_bugs","projects_cases ",
		"projects_contacts","projects_opportunities ","projects_products","prospect_list_campaigns","prospect_lists ","prospect_lists_prospects",
		"prospects","relationships","releases ","roles","roles_modules","roles_users","saved_search ","schedulers","schedulers_times","sugarfeed",
		"tasks","tracker","upgrade_history","user_preferences","users","users_feeds","users_last_import","users_signatures","vcals","versions");
		
		$q=new mysql();
		while (list ($num, $val) = each ($tables) ){
			if(!$q->TABLE_EXISTS(trim($val),$this->sql_db)){
				$notTables[]=$val;
			}
			
		}
		
		if(count($notTables)>0){
			echo count($notTables)." Tables does not exists in $this->sql_db database,\n".implode(",",$notTables)." try to repair...\n";
			return false;
		}
		return true;
		
	}
	
	private function GetUserid($username){
		$q=new mysql();
		$username=strtolower($username);
		$sql="SELECT id FROM users WHERE user_name=\"$username\"";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,$this->sql_db)); 
		if(trim($ligne["id"]==null)){return null;}	
		return $ligne["id"];
	}
	
	private function CreateAdminUser($username,$password){
		$password=md5($password);
		$date_entered=date('Y-m-d H:i:s');
		$sql="INSERT INTO users(user_name,user_hash,sugar_login,last_name,status,date_entered,date_modified,is_admin)
		VALUES('$username','$password',1,'Administrator','Active','$date_entered','$date_entered',1)";
		$q=new mysql();
		$q->QUERY_SQL($sql,$this->sql_db);
		
	}
	private function UpdateAdminPassword($id,$password){
		$password=md5($password);
		$sql="UPDATE users SET user_hash='$password',is_admin=1 WHERE id='$id'";
		writelogs("$sql",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		$q=new mysql();
		$q->QUERY_SQL($sql,$this->sql_db);
				
	}
	
	private function SetLdapConfig($key,$value){
		$q=new mysql();
		$sql="SELECT `value` FROM config WHERE category='ldap' AND name='$key' LIMIT 0,1";
		$ligne=@mysql_fetch_array($q->QUERY_SQL($sql,$this->sql_db)); 
		if(trim($ligne["value"])==null){
			$sql="INSERT INTO config(`category`,`name`,`value`) VALUES('ldap','$key','$value')";
		}else{
			$sql="UPDATE `config` SET `value`='$value' WHERE `category`='ldap' AND `name`='$key'";
		}
		
		$q=new mysql();
		$q->QUERY_SQL($sql,$this->sql_db);
		
	}
	
	private function SeSystemConfig($key,$value){
		$q=new mysql();
		$sql="SELECT value FROM config WHERE category='system' AND name='$key' LIMIT 0,1";
		$ligne=mysql_fetch_array($q->QUERY_SQL($sql,$this->sql_db)); 
		if(trim($ligne["value"])==null){
			$sql="INSERT INTO config(category,name,value) VALUES('system','$key','$value')";
		}else{
			$sql="UPDATE `config` SET `value`='$value' WHERE `category`='system' AND `name`='$key'";
		}
		
		
		$q->QUERY_SQL($sql,$this->sql_db);
		
	}
	
	public function BuildSilentInstallConf($ssl){
		$sock=new sockets();
		$ApacheGroupWarePort=$sock->GET_INFO('ApacheGroupWarePort');
		$users=new usersMenus();
		if($ApacheGroupWarePort<>'80'){$ApacheGroupWarePort=":$ApacheGroupWarePort";}
		if($ssl){$ApacheGroupWarePort="443";}
		$f[]="<?php";
		$f[]="\$sugar_config_si = array (";
		$f[]="    'setup_db_host_name' => '$this->sql_host',";
		$f[]="    'setup_db_database_name' => '$this->sql_db',";
		$f[]="    'setup_db_drop_tables' => 0,";
		$f[]="    'setup_db_create_database' => 1,";
		$f[]="    'setup_db_pop_demo_data' => 0,";
		$f[]="    'setup_site_admin_password' => '$this->manager_password',";
		$f[]="    'setup_db_create_sugarsales_user' => 0,";
		$f[]="    'setup_db_admin_user_name' => '$this->sql_admin',";
		$f[]="    'setup_site_admin_user_name' => '$this->manager_name',";
		$f[]="    'setup_db_admin_password' => '$this->sql_password',";
		$f[]="    'setup_db_sugarsales_user' => '$this->sql_admin',";
		$f[]="    'setup_db_sugarsales_password' => '$this->sql_password',";
		$f[]="    'setup_db_type' => 'mysql',";
		$f[]="    'setup_license_key_users' => \${slkeyusers},";
		$f[]="    'setup_license_key_expire_date' => '\${slkeyexpiredate}',";
		$f[]="    'setup_license_key_oc_licences' => \${slkey_oc_licenses},";
		$f[]="    'setup_license_key' => '\${slkey}',";
		$f[]="    'setup_site_url' => 'http://$this->servername:$ApacheGroupWarePort',";
		$f[]="    'setup_system_name' => '$this->servername',";
		$f[]="    'default_currency_iso4217' => 'USD',";
		$f[]="    'default_currency_name' => 'US Dollars',";
		$f[]="    'default_currency_significant_digits' => '2',";
		$f[]="    'default_currency_symbol' => '\$',";
		$f[]="    'default_date_format' => 'Y-m-d',";
		$f[]="    'default_time_format' => 'H:i',";
		$f[]="    'default_decimal_seperator' => '.',";
		$f[]="    'default_export_charset' => 'ISO-8859-1',";
		$f[]="    'default_language' => 'en_us',";
		$f[]="    'default_locale_name_format' => 's f l',";
		$f[]="    'default_number_grouping_seperator' => ',',";
		$f[]="    'export_delimiter' => ',',";
		$f[]=");";
		$f[]="?>";
		return @implode("\n",$f);
		
	}

	
	function BuildSugarConf($ssl=false){
			$sock=new sockets();
			$q=new mysql();
			$ApacheGroupWarePort=$sock->GET_INFO('ApacheGroupWarePort');
			$users=new usersMenus();
			if($ApacheGroupWarePort<>'80'){$ApacheGroupWarePort=":$ApacheGroupWarePort";}
			if($ssl){$ApacheGroupWarePort="443";}
			$conf[]='<?php';
			$conf[]='// created: '.date('Y-m-d H:i:s');
			$conf[]='$sugar_config = array (';
			$conf[]='  "admin_access_control" => false,';
			$conf[]='  "admin_export_only" => false,';
			$conf[]='  "cache_dir" => "cache/",';
			$conf[]='  "calculate_response_time" => true,';
			$conf[]='  "common_ml_dir" => "",';
			$conf[]='  "create_default_user" => false,';
			$conf[]='  "currency" => "",';
			$conf[]='  "dashlet_display_row_options" => ';
			$conf[]='  array (';
			$conf[]='    0 => "1",';
			$conf[]='    1 => "3",';
			$conf[]='    2 => "5",';
			$conf[]='    3 => "10",';
			$conf[]='  ),';
			$conf[]='  "date_formats" => ';
			$conf[]='  array (';
			$conf[]='    "Y-m-d" => "2006-12-23",';
			$conf[]='    "m-d-Y" => "12-23-2006",';
			$conf[]='    "d-m-Y" => "23-12-2006",';
			$conf[]='    "Y/m/d" => "2006/12/23",';
			$conf[]='    "m/d/Y" => "12/23/2006",';
			$conf[]='    "d/m/Y" => "23/12/2006",';
			$conf[]='    "Y.m.d" => "2006.12.23",';
			$conf[]='    "d.m.Y" => "23.12.2006",';
			$conf[]='    "m.d.Y" => "12.23.2006",';
			$conf[]='  ),';
			$conf[]='  "datef" => "m/d/Y",';
			$conf[]='  "dbconfig" => ';
			$conf[]='  array (';
			$conf[]='    "db_host_name" => "'.$q->mysql_server.':'.$q->mysql_port.'",';
			$conf[]='    "db_host_instance" => "SQLEXPRESS",';
			$conf[]='    "db_user_name" => "'.$this->sql_admin.'",';
			$conf[]='    "db_password" => "'.$this->sql_password.'",';
			$conf[]='    "db_name" => "'.$this->sql_db.'",';
			$conf[]='    "db_type" => "mysql",';
			$conf[]='  ),';
			$conf[]='  "dbconfigoption" => ';
			$conf[]='  array (';
			$conf[]='    "persistent" => true,';
			$conf[]='    "autofree" => false,';
			$conf[]='    "debug" => 0,';
			$conf[]='    "seqname_format" => "%s_seq",';
			$conf[]='    "portability" => 0,';
			$conf[]='    "ssl" => false,';
			$conf[]='  ),';
			$conf[]='  "default_action" => "index",';
			$conf[]='  "default_charset" => "UTF-8",';
			$conf[]='  "default_currencies" => ';
			$conf[]='  array (';
			$conf[]='    "AUD" => ';
			$conf[]='    array (';
			$conf[]='      "name" => "Australian Dollars",';
			$conf[]='      "iso4217" => "AUD",';
			$conf[]='      "symbol" => "$",';
			$conf[]='    ),';
			$conf[]='    "BRL" => ';
			$conf[]='    array (';
			$conf[]='      "name" => "Brazilian Reais",';
			$conf[]='      "iso4217" => "BRL",';
			$conf[]='      "symbol" => "R$",';
			$conf[]='    ),';
			$conf[]='    "GBP" => ';
			$conf[]='    array (';
			$conf[]='      "name" => "British Pounds",';
			$conf[]='      "iso4217" => "GBP",';
			$conf[]='      "symbol" => "Â£",';
			$conf[]='    ),';
			$conf[]='    "CAD" => ';
			$conf[]='    array (';
			$conf[]='      "name" => "Canadian Dollars",';
			$conf[]='      "iso4217" => "CAD",';
			$conf[]='      "symbol" => "$",';
			$conf[]='    ),';
			$conf[]='    "CNY" => ';
			$conf[]='    array (';
			$conf[]='      "name" => "Chinese Yuan",';
			$conf[]='      "iso4217" => "CNY",';
			$conf[]='      "symbol" => "ï¿¥",';
			$conf[]='    ),';
			$conf[]='    "EUR" => ';
			$conf[]='    array (';
			$conf[]='      "name" => "Euro",';
			$conf[]='      "iso4217" => "EUR",';
			$conf[]='      "symbol" => "â¬",';
			$conf[]='    ),';
			$conf[]='    "HKD" => ';
			$conf[]='    array (';
			$conf[]='      "name" => "Hong Kong Dollars",';
			$conf[]='      "iso4217" => "HKD",';
			$conf[]='      "symbol" => "$",';
			$conf[]='    ),';
			$conf[]='    "INR" => ';
			$conf[]='    array (';
			$conf[]='      "name" => "Indian Rupees",';
			$conf[]='      "iso4217" => "INR",';
			$conf[]='      "symbol" => "âš",';
			$conf[]='    ),';
			$conf[]='    "KRW" => ';
			$conf[]='    array (';
			$conf[]='      "name" => "Korean Won",';
			$conf[]='      "iso4217" => "KRW",';
			$conf[]='      "symbol" => "â©",';
			$conf[]='    ),';
			$conf[]='    "YEN" => ';
			$conf[]='    array (';
			$conf[]='      "name" => "Japanese Yen",';
			$conf[]='      "iso4217" => "JPY",';
			$conf[]='      "symbol" => "Â¥",';
			$conf[]='    ),';
			$conf[]='    "MXM" => ';
			$conf[]='    array (';
			$conf[]='      "name" => "Mexican Pesos",';
			$conf[]='      "iso4217" => "MXM",';
			$conf[]='      "symbol" => "$",';
			$conf[]='    ),';
			$conf[]='    "SGD" => ';
			$conf[]='    array (';
			$conf[]='      "name" => "Singaporean Dollars",';
			$conf[]='      "iso4217" => "SGD",';
			$conf[]='      "symbol" => "$",';
			$conf[]='    ),';
			$conf[]='    "CHF" => ';
			$conf[]='    array (';
			$conf[]='      "name" => "Swiss Franc",';
			$conf[]='      "iso4217" => "CHF",';
			$conf[]='      "symbol" => "SFr.",';
			$conf[]='    ),';
			$conf[]='    "THB" => ';
			$conf[]='    array (';
			$conf[]='      "name" => "Thai Baht",';
			$conf[]='      "iso4217" => "THB",';
			$conf[]='      "symbol" => "àž¿",';
			$conf[]='    ),';
			$conf[]='    "USD" => ';
			$conf[]='    array (';
			$conf[]='      "name" => "US Dollars",';
			$conf[]='      "iso4217" => "USD",';
			$conf[]='      "symbol" => "$",';
			$conf[]='    ),';
			$conf[]='  ),';
			$conf[]='  "default_currency_iso4217" => "EUR",';
			$conf[]='  "default_currency_name" => "Euro",';
			$conf[]='  "default_currency_significant_digits" => "2",';
			$conf[]='  "default_currency_symbol" => "â&#65533;¬",';
			$conf[]='  "default_date_format" => "Y/m/d",';
			$conf[]='  "default_decimal_seperator" => ".",';
			$conf[]='  "default_email_charset" => "ISO-8859-1",';
			$conf[]='  "default_email_client" => "sugar",';
			$conf[]='  "default_email_editor" => "html",';
			$conf[]='  "default_export_charset" => "ISO-8859-1",';
			$conf[]='  "default_language" => "en_us",';
			$conf[]='  "default_locale_name_format" => "s f l",';
			$conf[]='  "default_max_subtabs" => "12",';
			$conf[]='  "default_max_tabs" => "12",';
			$conf[]='  "default_module" => "Home",';
			$conf[]='  "default_navigation_paradigm" => "m",';
			$conf[]='  "default_number_grouping_seperator" => ",",';
			$conf[]='  "default_password" => "",';
			$conf[]='  "default_permissions" => ';
			$conf[]='  array (';
			$conf[]='    "dir_mode" => 1528,';
			$conf[]='    "file_mode" => 432,';
			$conf[]='    "user" => "",';
			$conf[]='    "group" => "",';
			$conf[]='  ),';
			$conf[]='  "default_subpanel_links" => false,';
			$conf[]='  "default_subpanel_tabs" => true,';
			$conf[]='  "default_swap_last_viewed" => false,';
			$conf[]='  "default_swap_shortcuts" => false,';
			$conf[]='  "default_theme" => "Sugar",';
			$conf[]='  "default_time_format" => "h:iA",';
			$conf[]='  "default_user_is_admin" => false,';
			$conf[]='  "default_user_name" => "",';
			$conf[]='  "demoData" => "no",';
			$conf[]='  "disable_export" => false,';
			$conf[]='  "disable_persistent_connections" => "false",';
			$conf[]='  "display_email_template_variable_chooser" => false,';
			$conf[]='  "display_inbound_email_buttons" => false,';
			$conf[]='  "dump_slow_queries" => false,';
			$conf[]='  "email_default_client" => "sugar",';
			$conf[]='  "email_default_delete_attachments" => true,';
			$conf[]='  "email_default_editor" => "html",';
			$conf[]='  "email_num_autoreplies_24_hours" => 10,';
			$conf[]='  "export_delimiter" => ",",';
			$conf[]='  "history_max_viewed" => 10,';
			$conf[]='  "host_name" => "localhost",';
			$conf[]='  "import_dir" => "cache/import/",';
			$conf[]='  "import_max_execution_time" => 3600,';
			$conf[]='  "import_max_records_per_file" => "1000",';
			$conf[]='  "installer_locked" => true,';
			$conf[]='  "js_custom_version" => "",';
			$conf[]='  "js_lang_version" => 1,';
			$conf[]='  "languages" => ';
			$conf[]='  array (';
			$conf[]='    "en_us" => "English (US)",';
			$conf[]='    "fr_FR" => "Francais",';
			$conf[]='  ),';
			$conf[]='  "large_scale_test" => false,';
			$conf[]='  "list_max_entries_per_page" => 20,';
			$conf[]='  "list_max_entries_per_subpanel" => 10,';
			$conf[]='  "lock_default_user_name" => false,';
			$conf[]='  "lock_homepage" => false,';
			$conf[]='  "lock_subpanels" => false,';
			$conf[]='  "log_dir" => ".",';
			$conf[]='  "log_file" => "sugarcrm.log",';
			$conf[]='  "log_memory_usage" => false,';
			$conf[]='  "logger" => ';
			$conf[]='  array (';
			$conf[]='    "level" => "fatal",';
			$conf[]='    "file" => ';
			$conf[]='    array (';
			$conf[]='      "ext" => ".log",';
			$conf[]='      "name" => "sugarcrm",';
			$conf[]='      "dateFormat" => "%c",';
			$conf[]='      "maxSize" => "10MB",';
			$conf[]='      "maxLogs" => 10,';
			$conf[]='      "suffix" => "%m_%Y",';
			$conf[]='    ),';
			$conf[]='  ),';
			$conf[]='  "login_nav" => false,';
			$conf[]='  "max_dashlets_homepage" => "15",';
			$conf[]='  "portal_view" => "single_user",';
			$conf[]='  "require_accounts" => true,';
			$conf[]='  "resource_management" => ';
			$conf[]='  array (';
			$conf[]='    "special_query_limit" => 50000,';
			$conf[]='    "special_query_modules" => ';
			$conf[]='    array (';
			$conf[]='      0 => "Reports",';
			$conf[]='      1 => "Export",';
			$conf[]='      2 => "Import",';
			$conf[]='      3 => "Administration",';
			$conf[]='      4 => "Sync",';
			$conf[]='    ),';
			$conf[]='    "default_limit" => 1000,';
			$conf[]='  ),';
			$conf[]='  "rss_cache_time" => "10800",';
			$conf[]='  "save_query" => "all",';
			$conf[]='  "session_dir" => "",';
			$conf[]='  "showDetailData" => true,';
			$conf[]='  "showThemePicker" => true,';
			$conf[]='  "site_url" => "http://'.$this->servername.$ApacheGroupWarePort.'",';
			$conf[]='  "slow_query_time_msec" => "100",';
			$conf[]='  "sugar_version" => "'.$this->sugar_supposed_version.'",';
			$conf[]='  "sugarbeet" => true,';
			$conf[]='  "time_formats" => ';
			$conf[]='  array (';
			$conf[]='    "H:i" => "23:00",';
			$conf[]='    "h:ia" => "11:00pm",';
			$conf[]='    "h:iA" => "11:00PM",';
			$conf[]='    "H.i" => "23.00",';
			$conf[]='    "h.ia" => "11.00pm",';
			$conf[]='    "h.iA" => "11.00PM",';
			$conf[]='  ),';
			$conf[]='  "timef" => "H:i",';
			$conf[]='  "tmp_dir" => "cache/xml/",';
			$conf[]='  "translation_string_prefix" => false,';
			$conf[]='  "unique_key" => "cb26d19f5632365f305dd21bcb35ec11",';
			$conf[]='  "upload_badext" => ';
			$conf[]='  array (';
			$conf[]='    0 => "php",';
			$conf[]='    1 => "php3",';
			$conf[]='    2 => "php4",';
			$conf[]='    3 => "php5",';
			$conf[]='    4 => "pl",';
			$conf[]='    5 => "cgi",';
			$conf[]='    6 => "py",';
			$conf[]='    7 => "asp",';
			$conf[]='    8 => "cfm",';
			$conf[]='    9 => "js",';
			$conf[]='    10 => "vbs",';
			$conf[]='    11 => "html",';
			$conf[]='    12 => "htm",';
			$conf[]='  ),';
			$conf[]='  "upload_dir" => "cache/upload/",';
			$conf[]='  "upload_maxsize" => 3000000,';
			$conf[]='  "use_common_ml_dir" => false,';
			$conf[]='  "use_php_code_json" => true,';
			$conf[]='  "vcal_time" => "2",';
			$conf[]='  "verify_client_ip" => true';
			$conf[]=');';
			$conf[]='?>';	

			return implode("\n",$conf);
			
	}
	
	function checkRootFiles($root){
				$files[]="vCard.php";
		$files[]="maintenance.php";
		$files[]="themes/Love/config.php";
		$files[]="themes/Love/header.php";
		$files[]="themes/Love/layout_utils.php";
		$files[]="themes/Love/footer.php";
		$files[]="themes/WhiteSands/config.php";
		$files[]="themes/WhiteSands/header.php";
		$files[]="themes/WhiteSands/layout_utils.php";
		$files[]="themes/GoldenGate/config.php";
		$files[]="themes/GoldenGate/header.php";
		$files[]="themes/GoldenGate/layout_utils.php";
		$files[]="themes/Legacy/config.php";
		$files[]="themes/Legacy/header.php";
		$files[]="themes/Legacy/layout_utils.php";
		$files[]="themes/RipCurl/config.php";
		$files[]="themes/RipCurl/header.php";
		$files[]="themes/RipCurl/layout_utils.php";
		$files[]="themes/Retro/config.php";
		$files[]="themes/Retro/header.php";
		$files[]="themes/Retro/layout_utils.php";
		$files[]="themes/TrailBlazers/config.php";
		$files[]="themes/TrailBlazers/header.php";
		$files[]="themes/TrailBlazers/layout_utils.php";
		$files[]="themes/Links/config.php";
		$files[]="themes/Links/header.php";
		$files[]="themes/Links/layout_utils.php";
		$files[]="themes/Links/footer.php";
		$files[]="themes/VintageSugar/config.php";
		$files[]="themes/VintageSugar/header.php";
		$files[]="themes/VintageSugar/layout_utils.php";
		$files[]="themes/Awesome80s/config.php";
		$files[]="themes/Awesome80s/header.php";
		$files[]="themes/Awesome80s/layout_utils.php";
		$files[]="themes/Sugar/config.php";
		$files[]="themes/Sugar/header.php";
		$files[]="themes/Sugar/layout_utils.php";
		$files[]="themes/BoldMove/config.php";
		$files[]="themes/BoldMove/header.php";
		$files[]="themes/BoldMove/layout_utils.php";
		$files[]="themes/Shred/config.php";
		$files[]="themes/Shred/header.php";
		$files[]="themes/Shred/layout_utils.php";
		$files[]="themes/Paradise/config.php";
		$files[]="themes/Paradise/header.php";
		$files[]="themes/Paradise/layout_utils.php";
		$files[]="themes/Sugar2006/config.php";
		$files[]="themes/Sugar2006/header.php";
		$files[]="themes/Sugar2006/layout_utils.php";
		$files[]="themes/SugarLite/config.php";
		$files[]="themes/SugarLite/header.php";
		$files[]="themes/SugarLite/layout_utils.php";
		$files[]="themes/FinalFrontier/config.php";
		$files[]="themes/FinalFrontier/header.php";
		$files[]="themes/FinalFrontier/layout_utils.php";
		$files[]="themes/default/footer.php";
		$files[]="themes/Sunset/config.php";
		$files[]="themes/Sunset/header.php";
		$files[]="themes/Sunset/layout_utils.php";
		$files[]="themes/SugarClassic/config.php";
		$files[]="themes/SugarClassic/header.php";
		$files[]="themes/SugarClassic/layout_utils.php";
		$files[]="themes/Pipeline/config.php";
		$files[]="themes/Pipeline/header.php";
		$files[]="themes/Pipeline/layout_utils.php";
		$files[]="WebToLeadCapture.php";
		$files[]="include/vCard.php";
		$files[]="include/nusoap/class.soap_server.php";
		$files[]="include/nusoap/class.xmlschema.php";
		$files[]="include/nusoap/nusoap.php";
		$files[]="include/nusoap/class.wsdl.php";
		$files[]="include/nusoap/class.soap_fault.php";
		$files[]="include/nusoap/class.soap_parser.php";
		$files[]="include/nusoap/class.wsdlcache.php";
		$files[]="include/nusoap/class.nusoap_base.php";
		$files[]="include/nusoap/class.soap_val.php";
		$files[]="include/nusoap/nusoapmime.php";
		$files[]="include/nusoap/class.soapclient.php";
		$files[]="include/nusoap/class.soap_transport_http.php";
		$files[]="include/javascript/jsAlerts.php";
		$files[]="include/javascript/javascript.php";
		$files[]="include/globalControlLinks.php";
		$files[]="include/SugarObjects/templates/company/language/en_us.lang.php";
		$files[]="include/SugarObjects/templates/company/language/application/en_us.lang.php";
		$files[]="include/SugarObjects/templates/company/config.php";
		$files[]="include/SugarObjects/templates/company/Company.php";
		$files[]="include/SugarObjects/templates/company/vardefs.php";
		$files[]="include/SugarObjects/templates/company/metadata/metafiles.php";
		$files[]="include/SugarObjects/templates/company/metadata/editviewdefs.php";
		$files[]="include/SugarObjects/templates/company/metadata/popupdefs.php";
		$files[]="include/SugarObjects/templates/company/metadata/detailviewdefs.php";
		$files[]="include/SugarObjects/templates/company/metadata/subpanels/default.php";
		$files[]="include/SugarObjects/templates/company/metadata/listviewdefs.php";
		$files[]="include/SugarObjects/templates/company/metadata/SearchFields.php";
		$files[]="include/SugarObjects/templates/company/metadata/sidecreateviewdefs.php";
		$files[]="include/SugarObjects/templates/company/metadata/searchdefs.php";
		$files[]="include/SugarObjects/templates/file/views/view.edit.php";
		$files[]="include/SugarObjects/templates/file/File.php";
		$files[]="include/SugarObjects/templates/file/language/en_us.lang.php";
		$files[]="include/SugarObjects/templates/file/language/application/en_us.lang.php";
		$files[]="include/SugarObjects/templates/file/controller.php";
		$files[]="include/SugarObjects/templates/file/vardefs.php";
		$files[]="include/SugarObjects/templates/file/metadata/metafiles.php";
		$files[]="include/SugarObjects/templates/file/metadata/editviewdefs.php";
		$files[]="include/SugarObjects/templates/file/metadata/dashletviewdefs.php";
		$files[]="include/SugarObjects/templates/file/metadata/detailviewdefs.php";
		$files[]="include/SugarObjects/templates/file/metadata/subpanels/default.php";
		$files[]="include/SugarObjects/templates/file/metadata/listviewdefs.php";
		$files[]="include/SugarObjects/templates/file/metadata/SearchFields.php";
		$files[]="include/SugarObjects/templates/file/metadata/sidecreateviewdefs.php";
		$files[]="include/SugarObjects/templates/file/metadata/searchdefs.php";
		$files[]="include/SugarObjects/templates/sale/language/en_us.lang.php";
		$files[]="include/SugarObjects/templates/sale/language/application/en_us.lang.php";
		$files[]="include/SugarObjects/templates/sale/config.php";
		$files[]="include/SugarObjects/templates/sale/vardefs.php";
		$files[]="include/SugarObjects/templates/sale/metadata/metafiles.php";
		$files[]="include/SugarObjects/templates/sale/metadata/editviewdefs.php";
		$files[]="include/SugarObjects/templates/sale/metadata/popupdefs.php";
		$files[]="include/SugarObjects/templates/sale/metadata/dashletviewdefs.php";
		$files[]="include/SugarObjects/templates/sale/metadata/detailviewdefs.php";
		$files[]="include/SugarObjects/templates/sale/metadata/subpanels/default.php";
		$files[]="include/SugarObjects/templates/sale/metadata/listviewdefs.php";
		$files[]="include/SugarObjects/templates/sale/metadata/SearchFields.php";
		$files[]="include/SugarObjects/templates/sale/metadata/sidecreateviewdefs.php";
		$files[]="include/SugarObjects/templates/sale/metadata/searchdefs.php";
		$files[]="include/SugarObjects/templates/sale/Chance.php";
		$files[]="include/SugarObjects/templates/sale/Sale.php";
		$files[]="include/SugarObjects/templates/person/language/en_us.lang.php";
		$files[]="include/SugarObjects/templates/person/Person.php";
		$files[]="include/SugarObjects/templates/person/config.php";
		$files[]="include/SugarObjects/templates/person/vardefs.php";
		$files[]="include/SugarObjects/templates/person/metadata/metafiles.php";
		$files[]="include/SugarObjects/templates/person/metadata/editviewdefs.php";
		$files[]="include/SugarObjects/templates/person/metadata/popupdefs.php";
		$files[]="include/SugarObjects/templates/person/metadata/detailviewdefs.php";
		$files[]="include/SugarObjects/templates/person/metadata/subpanels/default.php";
		$files[]="include/SugarObjects/templates/person/metadata/listviewdefs.php";
		$files[]="include/SugarObjects/templates/person/metadata/SearchFields.php";
		$files[]="include/SugarObjects/templates/person/metadata/sidecreateviewdefs.php";
		$files[]="include/SugarObjects/templates/person/metadata/searchdefs.php";
		$files[]="include/SugarObjects/templates/issue/language/en_us.lang.php";
		$files[]="include/SugarObjects/templates/issue/language/application/en_us.lang.php";
		$files[]="include/SugarObjects/templates/issue/Issue.php";
		$files[]="include/SugarObjects/templates/issue/config.php";
		$files[]="include/SugarObjects/templates/issue/vardefs.php";
		$files[]="include/SugarObjects/templates/issue/metadata/metafiles.php";
		$files[]="include/SugarObjects/templates/issue/metadata/editviewdefs.php";
		$files[]="include/SugarObjects/templates/issue/metadata/popupdefs.php";
		$files[]="include/SugarObjects/templates/issue/metadata/detailviewdefs.php";
		$files[]="include/SugarObjects/templates/issue/metadata/subpanels/default.php";
		$files[]="include/SugarObjects/templates/issue/metadata/listviewdefs.php";
		$files[]="include/SugarObjects/templates/issue/metadata/SearchFields.php";
		$files[]="include/SugarObjects/templates/issue/metadata/sidecreateviewdefs.php";
		$files[]="include/SugarObjects/templates/issue/metadata/searchdefs.php";
		$files[]="include/SugarObjects/templates/basic/language/en_us.lang.php";
		$files[]="include/SugarObjects/templates/basic/vardefs.php";
		$files[]="include/SugarObjects/templates/basic/metadata/metafiles.php";
		$files[]="include/SugarObjects/templates/basic/metadata/editviewdefs.php";
		$files[]="include/SugarObjects/templates/basic/metadata/popupdefs.php";
		$files[]="include/SugarObjects/templates/basic/metadata/dashletviewdefs.php";
		$files[]="include/SugarObjects/templates/basic/metadata/detailviewdefs.php";
		$files[]="include/SugarObjects/templates/basic/metadata/subpanels/default.php";
		$files[]="include/SugarObjects/templates/basic/metadata/listviewdefs.php";
		$files[]="include/SugarObjects/templates/basic/metadata/SearchFields.php";
		$files[]="include/SugarObjects/templates/basic/metadata/sidecreateviewdefs.php";
		$files[]="include/SugarObjects/templates/basic/metadata/searchdefs.php";
		$files[]="include/SugarObjects/templates/basic/Dashlets/Dashlet/m-n-Dashlet.meta.php";
		$files[]="include/SugarObjects/templates/basic/Dashlets/Dashlet/m-n-Dashlet.php";
		$files[]="include/SugarObjects/templates/basic/Basic.php";
		$files[]="include/SugarObjects/implements/team_security/language/en_us.lang.php";
		$files[]="include/SugarObjects/implements/team_security/vardefs.php";
		$files[]="include/SugarObjects/implements/assignable/language/en_us.lang.php";
		$files[]="include/SugarObjects/implements/assignable/vardefs.php";
		$files[]="include/SugarObjects/SugarConfig.php";
		$files[]="include/SugarObjects/LanguageManager.php";
		$files[]="include/SugarObjects/SugarRegistry.php";
		$files[]="include/SugarObjects/SugarSession.php";
		$files[]="include/SugarObjects/VardefManager.php";
		$files[]="include/JSON.php";
		$files[]="include/templates/TemplateDragDropChooser.php";
		$files[]="include/templates/TemplateGroupChooser.php";
		$files[]="include/templates/Template.php";
		$files[]="include/language/jsLanguage.php";
		$files[]="include/language/en_us.lang.php";
		$files[]="include/MVC/Controller/ControllerFactory.php";
		$files[]="include/MVC/Controller/SugarController.php";
		$files[]="include/MVC/Controller/file_access_control_map.php";
		$files[]="include/MVC/Controller/action_file_map.php";
		$files[]="include/MVC/Controller/action_view_map.php";
		$files[]="include/MVC/Controller/entry_point_registry.php";
		$files[]="include/MVC/SugarApplication.php";
		$files[]="include/MVC/View/views/view.multiedit.php";
		$files[]="include/MVC/View/views/view.config.php";
		$files[]="include/MVC/View/views/view.json.php";
		$files[]="include/MVC/View/views/view.vcard.php";
		$files[]="include/MVC/View/views/view.list.php";
		$files[]="include/MVC/View/views/view.serialized.php";
		$files[]="include/MVC/View/views/view.sidequickcreate.php";
		$files[]="include/MVC/View/views/view.edit.php";
		$files[]="include/MVC/View/views/view.popup.php";
		$files[]="include/MVC/View/views/view.detail.php";
		$files[]="include/MVC/View/views/view.xml.php";
		$files[]="include/MVC/View/views/view.noaccess.php";
		$files[]="include/MVC/View/views/view.html.php";
		$files[]="include/MVC/View/views/view.classic.config.php";
		$files[]="include/MVC/View/views/view.ajax.php";
		$files[]="include/MVC/View/views/view.classic.php";
		$files[]="include/MVC/View/SugarView.php";
		$files[]="include/MVC/View/ViewFactory.php";
		$files[]="include/DetailView/DetailView.php";
		$files[]="include/DetailView/DetailView2.php";
		$files[]="include/tabs.php";
		$files[]="include/json_config.php";
		$files[]="include/SugarPHPMailer.php";
		$files[]="include/SugarEmailAddress/SugarEmailAddress.php";
		$files[]="include/controller/Controller.php";
		$files[]="include/database/MysqlHelper.php";
		$files[]="include/database/FreeTDSManager.php";
		$files[]="include/database/MysqlManager.php";
		$files[]="include/database/MssqlManager.php";
		$files[]="include/database/PearDatabase.php";
		$files[]="include/database/MssqlHelper.php";
		$files[]="include/database/MysqliManager.php";
		$files[]="include/database/DBHelper.php";
		$files[]="include/database/DBManagerFactory.php";
		$files[]="include/database/DBManager.php";
		$files[]="include/database/MysqliHelper.php";
		$files[]="include/database/FreeTDSHelper.php";
		$files[]="include/Sugar_Smarty.php";
		$files[]="include/MassUpdate.php";
		$files[]="include/charts/Charts.php";
		$files[]="include/reCaptcha/recaptchalib.php";
		$files[]="include/timezone/timezones.php";
		$files[]="include/SubPanel/registered_layout_defs.php";
		$files[]="include/SubPanel/SubPanelViewer.php";
		$files[]="include/SubPanel/SubPanelTiles.php";
		$files[]="include/SubPanel/SubPanelTilesTabs.php";
		$files[]="include/SubPanel/SubPanelDefinitions.php";
		$files[]="include/SubPanel/SubPanel.php";
		$files[]="include/SubPanel/SugarTab.php";
		$files[]="include/VarDefHandler/vardef_meta_arrays.php";
		$files[]="include/VarDefHandler/SugarTabs/SugarTab.php";
		$files[]="include/VarDefHandler/VarDefHandler.php";
		$files[]="include/VarDefHandler/listvardefoverride.php";
		$files[]="include/pdf/class.ezpdf.php";
		$files[]="include/pdf/class.pdf.php";
		$files[]="include/entryPoint.php";
		$files[]="include/ListView/ListViewFacade.php";
		$files[]="include/ListView/ListViewData.php";
		$files[]="include/ListView/ListViewXTPL.php";
		$files[]="include/ListView/ListView.php";
		$files[]="include/ListView/ListViewDisplay.php";
		$files[]="include/ListView/ListViewSmarty.php";
		$files[]="include/HTTP_WebDAV_Server/Server.php";
		$files[]="include/HTTP_WebDAV_Server/Tools/_parse_proppatch.php";
		$files[]="include/HTTP_WebDAV_Server/Tools/_parse_lockinfo.php";
		$files[]="include/HTTP_WebDAV_Server/Tools/_parse_propfind.php";
		$files[]="include/export_utils.php";
		$files[]="include/MySugar/DashletsDialog/DashletsDialog.php";
		$files[]="include/MySugar/MySugar.php";
		$files[]="include/tabConfig.php";
		$files[]="include/SugarTabs/SugarTab.php";
		$files[]="include/Smarty/plugins/function.sugar_connector_display.php";
		$files[]="include/Smarty/plugins/modifier.multienum_to_array.php";
		$files[]="include/Smarty/plugins/modifier.in_array.php";
		$files[]="include/Smarty/plugins/function.html_options.php";
		$files[]="include/Smarty/plugins/function.math.php";
		$files[]="include/Smarty/plugins/modifier.regex_replace.php";
		$files[]="include/Smarty/plugins/outputfilter.trimwhitespace.php";
		$files[]="include/Smarty/plugins/modifier.truncate.php";
		$files[]="include/Smarty/plugins/modifier.replace.php";
		$files[]="include/Smarty/plugins/function.sugar_evalcolumn_old.php";
		$files[]="include/Smarty/plugins/modifier.count_words.php";
		$files[]="include/Smarty/plugins/modifier.count_sentences.php";
		$files[]="include/Smarty/plugins/function.sugar_translate.php";
		$files[]="include/Smarty/plugins/function.fetch.php";
		$files[]="include/Smarty/plugins/function.sugar_field.php";
		$files[]="include/Smarty/plugins/function.debug.php";
		$files[]="include/Smarty/plugins/function.sugar_currency_format.php";
		$files[]="include/Smarty/plugins/modifier.date_format.php";
		$files[]="include/Smarty/plugins/function.popup.php";
		$files[]="include/Smarty/plugins/function.sugar_getjspath.php";
		$files[]="include/Smarty/plugins/function.html_table.php";
		$files[]="include/Smarty/plugins/function.cycle.php";
		$files[]="include/Smarty/plugins/modifier.default.php";
		$files[]="include/Smarty/plugins/modifier.strip_tags.php";
		$files[]="include/Smarty/plugins/modifier.count_paragraphs.php";
		$files[]="include/Smarty/plugins/modifier.indent.php";
		$files[]="include/Smarty/plugins/function.counter.php";
		$files[]="include/Smarty/plugins/function.ext_includes.php";
		$files[]="include/Smarty/plugins/modifier.strip.php";
		$files[]="include/Smarty/plugins/modifier.strip_semicolon.php";
		$files[]="include/Smarty/plugins/function.html_select_date.php";
		$files[]="include/Smarty/plugins/function.sugar_include.php";
		$files[]="include/Smarty/plugins/modifier.nl2br.php";
		$files[]="include/Smarty/plugins/function.sugar_phone.php";
		$files[]="include/Smarty/plugins/shared.make_timestamp.php";
		$files[]="include/Smarty/plugins/function.popup_init.php";
		$files[]="include/Smarty/plugins/function.html_checkboxes.php";
		$files[]="include/Smarty/plugins/modifier.count_characters.php";
		$files[]="include/Smarty/plugins/function.sugarvar.php";
		$files[]="include/Smarty/plugins/modifier.string_format.php";
		$files[]="include/Smarty/plugins/modifier.lower.php";
		$files[]="include/Smarty/plugins/function.overlib_includes.php";
		$files[]="include/Smarty/plugins/modifier.escape.php";
		$files[]="include/Smarty/plugins/function.html_image.php";
		$files[]="include/Smarty/plugins/function.sugarvar_connector.php";
		$files[]="include/Smarty/plugins/modifier.default_date_value.php";
		$files[]="include/Smarty/plugins/compiler.assign.php";
		$files[]="include/Smarty/plugins/function.eval.php";
		$files[]="include/Smarty/plugins/function.sugar_help.php";
		$files[]="include/Smarty/plugins/function.sugar_replace_vars.php";
		$files[]="include/Smarty/plugins/function.mailto.php";
		$files[]="include/Smarty/plugins/function.sugar_evalcolumn.php";
		$files[]="include/Smarty/plugins/function.sugar_getimagepath.php";
		$files[]="include/Smarty/plugins/modifier.wordwrap.php";
		$files[]="include/Smarty/plugins/shared.escape_special_chars.php";
		$files[]="include/Smarty/plugins/modifier.spacify.php";
		$files[]="include/Smarty/plugins/function.sugar_getwebpath.php";
		$files[]="include/Smarty/plugins/modifier.to_url.php";
		$files[]="include/Smarty/plugins/function.sugar_variable_constructor.php";
		$files[]="include/Smarty/plugins/function.html_radios.php";
		$files[]="include/Smarty/plugins/modifier.cat.php";
		$files[]="include/Smarty/plugins/function.html_select_time.php";
		$files[]="include/Smarty/plugins/function.assign_debug_info.php";
		$files[]="include/Smarty/plugins/function.sugar_button.php";
		$files[]="include/Smarty/plugins/block.textformat.php";
		$files[]="include/Smarty/plugins/function.sugar_image.php";
		$files[]="include/Smarty/plugins/modifier.upper.php";
		$files[]="include/Smarty/plugins/modifier.debug_print_var.php";
		$files[]="include/Smarty/plugins/function.config_load.php";
		$files[]="include/Smarty/plugins/modifier.capitalize.php";
		$files[]="include/Smarty/internals/core.smarty_include_php.php";
		$files[]="include/Smarty/internals/core.assign_smarty_interface.php";
		$files[]="include/Smarty/internals/core.get_microtime.php";
		$files[]="include/Smarty/internals/core.read_cache_file.php";
		$files[]="include/Smarty/internals/core.create_dir_structure.php";
		$files[]="include/Smarty/internals/core.get_php_resource.php";
		$files[]="include/Smarty/internals/core.write_compiled_resource.php";
		$files[]="include/Smarty/internals/core.get_include_path.php";
		$files[]="include/Smarty/internals/core.display_debug_console.php";
		$files[]="include/Smarty/internals/core.is_trusted.php";
		$files[]="include/Smarty/internals/core.assemble_plugin_filepath.php";
		$files[]="include/Smarty/internals/core.process_compiled_include.php";
		$files[]="include/Smarty/internals/core.write_cache_file.php";
		$files[]="include/Smarty/internals/core.load_resource_plugin.php";
		$files[]="include/Smarty/internals/core.rm_auto.php";
		$files[]="include/Smarty/internals/core.is_secure.php";
		$files[]="include/Smarty/internals/core.rmdir.php";
		$files[]="include/Smarty/internals/core.write_compiled_include.php";
		$files[]="include/Smarty/internals/core.write_file.php";
		$files[]="include/Smarty/internals/core.process_cached_inserts.php";
		$files[]="include/Smarty/internals/core.run_insert_handler.php";
		$files[]="include/Smarty/internals/core.load_plugins.php";
		$files[]="include/Smarty/Smarty_Compiler.class.php";
		$files[]="include/Smarty/Config_File.class.php";
		$files[]="include/Smarty/Smarty.class.php";
		$files[]="include/resource/ResourceManager.php";
		$files[]="include/resource/Observers/ResourceObserver.php";
		$files[]="include/resource/Observers/WebResourceObserver.php";
		$files[]="include/resource/Observers/SoapResourceObserver.php";
		$files[]="include/ytree/Node.php";
		$files[]="include/ytree/ExtNode.php";
		$files[]="include/ytree/Tree.php";
		$files[]="include/QuickSearchDefaults.php";
		$files[]="include/utils/activity_utils.php";
		$files[]="include/utils/mvc_utils.php";
		$files[]="include/utils/user_utils.php";
		$files[]="include/utils/sugar_file_utils.php";
		$files[]="include/utils/external_cache/SugarCache_ExternalAbstract.php";
		$files[]="include/utils/external_cache/SugarCache_Memcache.php";
		$files[]="include/utils/external_cache/SugarCache_Base.php";
		$files[]="include/utils/external_cache/SugarCache_Zend.php";
		$files[]="include/utils/external_cache/SugarCache_sMash.php";
		$files[]="include/utils/external_cache/SugarCache.php";
		$files[]="include/utils/external_cache/SugarCache_APC.php";
		$files[]="include/utils/array_utils.php";
		$files[]="include/utils/LogicHook.php";
		$files[]="include/utils/logic_utils.php";
		$files[]="include/utils/security_utils.php";
		$files[]="include/utils/encryption_utils.php";
		$files[]="include/utils/external_cache.php";
		$files[]="include/utils/db_utils.php";
		$files[]="include/utils/file_utils.php";
		$files[]="include/utils/zip_utils.php";
		$files[]="include/utils/progress_bar_utils.php";
		$files[]="include/utils.php";
		$files[]="include/OutboundEmail/OutboundEmail.php";
		$files[]="include/domit_rss/xml_domit_rss_shared.php";
		$files[]="include/domit_rss/php_text_cache.php";
		$files[]="include/domit_rss/xml_domit_rss_lite.php";
		$files[]="include/domit_rss/example_domit_rss_lite.php";
		$files[]="include/domit_rss/timer.php";
		$files[]="include/domit_rss/xml_domit_rss.php";
		$files[]="include/domit_rss/testing_domitrss.php";
		$files[]="include/CurrencyService/CurrencyService.php";
		$files[]="include/PHP_Compat/convert_uuencode.php";
		$files[]="include/PHP_Compat/convert_uudecode.php";
		$files[]="include/connectors/sources/ext/rest/rest.php";
		$files[]="include/connectors/sources/ext/soap/soap.php";
		$files[]="include/connectors/sources/default/source.php";
		$files[]="include/connectors/sources/SourceFactory.php";
		$files[]="include/connectors/sources/loc/xml.php";
		$files[]="include/connectors/formatters/FormatterFactory.php";
		$files[]="include/connectors/formatters/default/formatter.php";
		$files[]="include/connectors/utils/ConnectorUtils.php";
		$files[]="include/connectors/component.php";
		$files[]="include/connectors/ConnectorFactory.php";
		$files[]="include/connectors/filters/FilterFactory.php";
		$files[]="include/connectors/filters/default/filter.php";
		$files[]="include/Pear/Crypt_Blowfish/Blowfish.php";
		$files[]="include/Pear/Crypt_Blowfish/Blowfish/DefaultKey.php";
		$files[]="include/Pear/HTML_Safe/Safe.php";
		$files[]="include/Pear/XML_HTMLSax3/HTMLSax3/States.php";
		$files[]="include/Pear/XML_HTMLSax3/HTMLSax3/Decorators.php";
		$files[]="include/Pear/XML_HTMLSax3/HTMLSax3.php";
		$files[]="include/EditView/EditView2.php";
		$files[]="include/EditView/QuickCreate.php";
		$files[]="include/EditView/SugarVCR.php";
		$files[]="include/EditView/SubpanelQuickCreate.php";
		$files[]="include/EditView/SideQuickCreate.php";
		$files[]="include/EditView/EditView.php";
		$files[]="include/CacheHandler.php";
		$files[]="include/SugarTinyMCE.php";
		$files[]="include/pclzip/pclzip.lib.php";
		$files[]="include/Localization/Localization.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetSubPanelLoadSignedButton.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetSubPanelIcon.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetSubPanelTopArchiveEmailButton.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetSubPanelEditRoleButton.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetFieldchar.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetSubPanelTopCreateNoteButton.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetFieldcurrency.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetFieldbool.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetSubPanelTopScheduleMeetingButton.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetSubPanelRemoveButtonProjects.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetFieldrelate.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetSubPanelEditButton.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetFieldlongtext.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetSubPanelRemoveButton.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetSubPanelTopSelectContactsButton.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetSubPanelTopSummaryButton.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetFielddate.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetFieldfloat.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetSubPanelTopCreateAccountNameButton.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetField.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetSubPanelActivitiesStatusField.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetSubPanelTopScheduleCallButton.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetFieldradioenum.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetSubPanelTopSelectUsersButton.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetFieldtext.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetFieldmultienum.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetSubPanelTopCreateCampaignLogEntryButton.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetSubPanelEmailLink.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetFieldsingleenum.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetSubPanelCloseButton.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetFieldnum.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetSubPanelDetailViewLink.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetFieldemail.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetReportField.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetFieldname.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetFieldint.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetFieldenum.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetSubPanelTopComposeEmailButton.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetSubPanelTopCreateLeadNameButton.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetFielddatetime.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetSubPanelTopButton.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetSubPanelRemoveButtonMeetings.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetFieldphone.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetSubPanelConcat.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetFielddouble.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetFielddecimal.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetFielduser_name.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetFieldtime.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetFieldurl.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetSubPanelTopCreateTaskButton.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetFieldid.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetFielddatepicker.php";
		$files[]="include/generic/SugarWidgets/SugarWidget.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetSubPanelTopButtonQuickCreate.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetFieldvarchar.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetSubPanelGetLatestButton.php";
		$files[]="include/generic/SugarWidgets/SugarWidgetSubPanelTopSelectButton.php";
		$files[]="include/generic/LayoutManager.php";
		$files[]="include/generic/Save2.php";
		$files[]="include/generic/DeleteRelationship.php";
		$files[]="include/SugarFolders/SugarFolders.php";
		$files[]="include/GroupedTabs/GroupedTabStructure.php";
		$files[]="include/phpmailer/language/phpmailer.lang-hu.php";
		$files[]="include/phpmailer/language/phpmailer.lang-en.php";
		$files[]="include/phpmailer/language/phpmailer.lang-fi.php";
		$files[]="include/phpmailer/language/phpmailer.lang-no.php";
		$files[]="include/phpmailer/language/phpmailer.lang-de.php";
		$files[]="include/phpmailer/language/phpmailer.lang-tr.php";
		$files[]="include/phpmailer/language/phpmailer.lang-nl.php";
		$files[]="include/phpmailer/language/phpmailer.lang-br.php";
		$files[]="include/phpmailer/language/phpmailer.lang-ru.php";
		$files[]="include/phpmailer/language/phpmailer.lang-ro.php";
		$files[]="include/phpmailer/language/phpmailer.lang-cz.php";
		$files[]="include/phpmailer/language/phpmailer.lang-ca.php";
		$files[]="include/phpmailer/language/phpmailer.lang-se.php";
		$files[]="include/phpmailer/language/phpmailer.lang-dk.php";
		$files[]="include/phpmailer/language/phpmailer.lang-ja.php";
		$files[]="include/phpmailer/language/phpmailer.lang-pl.php";
		$files[]="include/phpmailer/language/phpmailer.lang-es.php";
		$files[]="include/phpmailer/language/phpmailer.lang-fr.php";
		$files[]="include/phpmailer/language/phpmailer.lang-it.php";
		$files[]="include/phpmailer/language/phpmailer.lang-fo.php";
		$files[]="include/phpmailer/class.smtp.php";
		$files[]="include/phpmailer/class.phpmailer.php";
		$files[]="include/modules.php";
		$files[]="include/TemplateHandler/TemplateHandler.php";
		$files[]="include/SugarPDF.php";
		$files[]="include/dir_inc.php";
		$files[]="include/SearchForm/SearchForm2.php";
		$files[]="include/SearchForm/SearchForm.php";
		$files[]="include/SugarCharts/SugarChart.php";
		$files[]="include/Dashlets/DashletGeneric.php";
		$files[]="include/Dashlets/DashletCacheBuilder.php";
		$files[]="include/Dashlets/DashletGenericChart.php";
		$files[]="include/Dashlets/Dashlet.php";
		$files[]="include/formbase.php";
		$files[]="include/SugarFields/Fields/Username/SugarFieldUsername.php";
		$files[]="include/SugarFields/Fields/Readonly/SugarFieldReadonly.php";
		$files[]="include/SugarFields/Fields/Parent/SugarFieldParent.php";
		$files[]="include/SugarFields/Fields/Text/SugarFieldText.php";
		$files[]="include/SugarFields/Fields/Datetimecombo/SugarFieldDatetimecombo.php";
		$files[]="include/SugarFields/Fields/Datetime/SugarFieldDatetime.php";
		$files[]="include/SugarFields/Fields/Relate/SugarFieldRelate.php";
		$files[]="include/SugarFields/Fields/Download/SugarFieldDownload.php";
		$files[]="include/SugarFields/Fields/Enum/SugarFieldEnum.php";
		$files[]="include/SugarFields/Fields/Base/SugarFieldBase.php";
		$files[]="include/SugarFields/Fields/Bool/SugarFieldBool.php";
		$files[]="include/SugarFields/Fields/File/SugarFieldFile.php";
		$files[]="include/SugarFields/Fields/Address/SugarFieldAddress.php";
		$files[]="include/SugarFields/Fields/Html/SugarFieldHtml.php";
		$files[]="include/SugarFields/Fields/Multienum/SugarFieldMultienum.php";
		$files[]="include/SugarFields/SugarFieldHandler.php";
		$files[]="include/SugarFields/Parsers/MetaParser.php";
		$files[]="include/SugarFields/Parsers/EditViewMetaParser.php";
		$files[]="include/SugarFields/Parsers/DetailViewMetaParser.php";
		$files[]="include/SugarFields/Parsers/QuickCreateMetaParser.php";
		$files[]="include/SugarFields/Parsers/SearchFormMetaParser.php";
		$files[]="include/SugarFields/Parsers/Rules/CampaignsParseRule.php";
		$files[]="include/SugarFields/Parsers/Rules/DocumentsParseRule.php";
		$files[]="include/SugarFields/Parsers/Rules/BaseRule.php";
		$files[]="include/SugarFields/Parsers/Rules/ActivitiesParseRule.php";
		$files[]="include/SugarFields/Parsers/Rules/QuotesParseRule.php";
		$files[]="include/SugarFields/Parsers/Rules/CallsParseRule.php";
		$files[]="include/SugarFields/Parsers/Rules/VariableSubstitutionRule.php";
		$files[]="include/SugarFields/Parsers/Rules/ContactsParseRule.php";
		$files[]="include/SugarFields/Parsers/Rules/EmptyRowRule.php";
		$files[]="include/SugarFields/Parsers/Rules/ProductsParseRule.php";
		$files[]="include/SugarFields/Parsers/Rules/EmailAddressRule.php";
		$files[]="include/SugarFields/Parsers/Rules/MeetingsParseRule.php";
		$files[]="include/SugarFields/Parsers/Rules/LeadsParseRule.php";
		$files[]="include/SugarFields/Parsers/Rules/BugsParseRule.php";
		$files[]="include/SugarFields/Parsers/Rules/AddressRule.php";
		$files[]="include/SugarFields/Parsers/Rules/AccountsParseRule.php";
		$files[]="include/SugarFields/Parsers/Rules/UndefinedVardefRule.php";
		$files[]="include/SugarFields/Parsers/Rules/ContractsParseRule.php";
		$files[]="include/SugarFields/Parsers/Rules/NotesParseRule.php";
		$files[]="include/SugarFields/Parsers/Rules/OpportunitiesParseRule.php";
		$files[]="include/SugarFields/Parsers/Rules/VariableCleanupRule.php";
		$files[]="include/SugarFields/Parsers/Rules/ParseRules.php";
		$files[]="include/domit/xml_saxy_parser.php";
		$files[]="include/domit/php_http_client_include.php";
		$files[]="include/domit/xml_domit_lite_parser.php";
		$files[]="include/domit/testing_domit.php";
		$files[]="include/domit/xml_domit_doctor.php";
		$files[]="include/domit/timer.php";
		$files[]="include/domit/xml_domit_include.php";
		$files[]="include/domit/xml_domit_shared.php";
		$files[]="include/domit/xml_saxy_lite_parser.php";
		$files[]="include/domit/xml_domit_cache.php";
		$files[]="include/domit/xml_domit_lite_include.php";
		$files[]="include/domit/php_http_client_generic.php";
		$files[]="include/domit/php_http_exceptions.php";
		$files[]="include/domit/php_file_utilities.php";
		$files[]="include/domit/xml_domit_parser.php";
		$files[]="include/domit/xml_domit_nodemaps.php";
		$files[]="include/domit/xml_saxy_shared.php";
		$files[]="include/domit/php_http_connector.php";
		$files[]="include/domit/xml_domit_getelementsbypath.php";
		$files[]="include/domit/php_http_proxy.php";
		$files[]="include/domit/xml_domit_utilities.php";
		$files[]="include/SugarLogger/LoggerManager.php";
		$files[]="include/SugarLogger/SugarLogger.php";
		$files[]="include/contextMenus/contextMenu.php";
		$files[]="include/contextMenus/menuDefs/sugarPerson.php";
		$files[]="include/contextMenus/menuDefs/sugarObject.php";
		$files[]="include/contextMenus/menuDefs/sugarAccount.php";
		$files[]="include/upload_file.php";
		$files[]="include/SugarDependentDropdown/SugarDependentDropdown.php";
		$files[]="include/SugarDependentDropdown/metadata/dependentDropdown.php";
		$files[]="include/Popups/PopupSmarty.php";
		$files[]="include/Popups/Popup_picker.php";
		$files[]="include/TimeDate.php";
		$files[]="include/time.php";
		$files[]="removeme.php";
		$files[]="ModuleInstall/PackageManager/PackageManagerDisplay.php";
		$files[]="ModuleInstall/PackageManager/PackageController.php";
		$files[]="ModuleInstall/PackageManager/PackageManager.php";
		$files[]="ModuleInstall/PackageManager/metadata/listviewdefs.php";
		$files[]="ModuleInstall/PackageManager/PackageManagerComm.php";
		$files[]="ModuleInstall/PackageManager/ListViewPackages.php";
		$files[]="ModuleInstall/PackageManager/PackageManagerDownloader.php";
		$files[]="ModuleInstall/ModuleInstaller.php";
		$files[]="soap.php";
		$files[]="HandleAjaxCall.php";
		$files[]="examples/FormValidationTest.php";
		$files[]="examples/SoapTest.php";
		$files[]="examples/ProgressBarTest.php";
		$files[]="examples/SoapTestPortal2.php";
		$files[]="examples/ExampleLeadCapture.php";
		$files[]="examples/SoapTestPortal.php";
		$files[]="export.php";
		$files[]="TreeData.php";
		$files[]="acceptDecline.php";
		$files[]="cron.php";
		$files[]="modules/iFrames/field_arrays.php";
		$files[]="modules/iFrames/Save.php";
		$files[]="modules/iFrames/language/en_us.lang.php";
		$files[]="modules/iFrames/index.php";
		$files[]="modules/iFrames/Menu.php";
		$files[]="modules/iFrames/Forms.php";
		$files[]="modules/iFrames/iFrameFormBase.php";
		$files[]="modules/iFrames/vardefs.php";
		$files[]="modules/iFrames/Dashlets/iFrameDashlet/iFrameDashlet.php";
		$files[]="modules/iFrames/Dashlets/iFrameDashlet/iFrameDashlet.meta.php";
		$files[]="modules/iFrames/Dashlets/SugarNewsDashlet/SugarNewsDashlet.meta.php";
		$files[]="modules/iFrames/Dashlets/SugarNewsDashlet/SugarNewsDashlet.php";
		$files[]="modules/iFrames/header.php";
		$files[]="modules/iFrames/iFrame.php";
		$files[]="modules/Tasks/views/view.edit.php";
		$files[]="modules/Tasks/field_arrays.php";
		$files[]="modules/Tasks/Save.php";
		$files[]="modules/Tasks/language/en_us.lang.php";
		$files[]="modules/Tasks/MyTasks.php";
		$files[]="modules/Tasks/TasksQuickCreate.php";
		$files[]="modules/Tasks/Menu.php";
		$files[]="modules/Tasks/Forms.php";
		$files[]="modules/Tasks/vardefs.php";
		$files[]="modules/Tasks/metadata/editviewdefs.php";
		$files[]="modules/Tasks/metadata/detailviewdefs.php";
		$files[]="modules/Tasks/metadata/subpanels/ForEmails.php";
		$files[]="modules/Tasks/metadata/subpanels/ForHistory.php";
		$files[]="modules/Tasks/metadata/subpanels/ForActivities.php";
		$files[]="modules/Tasks/metadata/subpanels/default.php";
		$files[]="modules/Tasks/metadata/studio.php";
		$files[]="modules/Tasks/metadata/listviewdefs.php";
		$files[]="modules/Tasks/metadata/SearchFields.php";
		$files[]="modules/Tasks/metadata/additionalDetails.php";
		$files[]="modules/Tasks/metadata/sidecreateviewdefs.php";
		$files[]="modules/Tasks/metadata/searchdefs.php";
		$files[]="modules/Tasks/Dashlets/MyTasksDashlet/MyTasksDashlet.data.php";
		$files[]="modules/Tasks/Dashlets/MyTasksDashlet/MyTasksDashlet.php";
		$files[]="modules/Tasks/Dashlets/MyTasksDashlet/MyTasksDashlet.meta.php";
		$files[]="modules/Tasks/Task.php";
		$files[]="modules/UpgradeWizard/preflightJson.php";
		$files[]="modules/UpgradeWizard/language/en_us.lang.php";
		$files[]="modules/UpgradeWizard/uw_emptyFunctions.php";
		$files[]="modules/UpgradeWizard/systemCheck.php";
		$files[]="modules/UpgradeWizard/upgradeMetaHelper.php";
		$files[]="modules/UpgradeWizard/start.php";
		$files[]="modules/UpgradeWizard/uw_utils.php";
		$files[]="modules/UpgradeWizard/systemCheckJson.php";
		$files[]="modules/UpgradeWizard/index.php";
		$files[]="modules/UpgradeWizard/end.php";
		$files[]="modules/UpgradeWizard/cancel.php";
		$files[]="modules/UpgradeWizard/uw_ajax.php";
		$files[]="modules/UpgradeWizard/Menu.php";
		$files[]="modules/UpgradeWizard/Forms.php";
		$files[]="modules/UpgradeWizard/upgradeTimeCounter.php";
		$files[]="modules/UpgradeWizard/commitJson.php";
		$files[]="modules/UpgradeWizard/deleteCache.php";
		$files[]="modules/UpgradeWizard/commit.php";
		$files[]="modules/UpgradeWizard/UploadFileCheck.php";
		$files[]="modules/UpgradeWizard/uw_files.php";
		$files[]="modules/UpgradeWizard/upload.php";
		$files[]="modules/UpgradeWizard/populateColumns.php";
		$files[]="modules/UpgradeWizard/SugarMerge/SideQuickCreateMerge.php";
		$files[]="modules/UpgradeWizard/SugarMerge/SugarMerge.php";
		$files[]="modules/UpgradeWizard/SugarMerge/SearchMerge.php";
		$files[]="modules/UpgradeWizard/SugarMerge/EditViewMerge.php";
		$files[]="modules/UpgradeWizard/SugarMerge/DetailViewMerge.php";
		$files[]="modules/UpgradeWizard/SugarMerge/SubpanelMerge.php";
		$files[]="modules/UpgradeWizard/SugarMerge/ListViewMerge.php";
		$files[]="modules/UpgradeWizard/SugarMerge/QuickCreateMerge.php";
		$files[]="modules/UpgradeWizard/preflight.php";
		$files[]="modules/UpgradeWizard/silentUpgrade.php";
		$files[]="modules/Users/field_arrays.php";
		$files[]="modules/Users/Logout.php";
		$files[]="modules/Users/Save.php";
		$files[]="modules/Users/language/en_us.lang.php";
		$files[]="modules/Users/User.php";
		$files[]="modules/Users/DetailView.php";
		$files[]="modules/Users/index.php";
		$files[]="modules/Users/authentication/AuthenticationController.php";
		$files[]="modules/Users/authentication/SugarAuthenticate/SugarAuthenticate.php";
		$files[]="modules/Users/authentication/SugarAuthenticate/SugarAuthenticateUser.php";
		$files[]="modules/Users/authentication/EmailAuthenticate/EmailAuthenticateUser.php";
		$files[]="modules/Users/authentication/EmailAuthenticate/EmailAuthenticate.php";
		$files[]="modules/Users/authentication/LDAPAuthenticate/LDAPConfigs/default.php";
		$files[]="modules/Users/authentication/LDAPAuthenticate/LDAPAuthenticate.php";
		$files[]="modules/Users/authentication/LDAPAuthenticate/LDAPAuthenticateUser.php";
		$files[]="modules/Users/Menu.php";
		$files[]="modules/Users/ListView.php";
		$files[]="modules/Users/SaveTimezone.php";
		$files[]="modules/Users/Forms.php";
		$files[]="modules/Users/vardefs.php";
		$files[]="modules/Users/Login.php";
		$files[]="modules/Users/PopupSignature.php";
		$files[]="modules/Users/metadata/subpaneldefs.php";
		$files[]="modules/Users/metadata/popupdefs.php";
		$files[]="modules/Users/metadata/reassignScriptMetadata.php";
		$files[]="modules/Users/metadata/subpanels/ForEmails.php";
		$files[]="modules/Users/metadata/subpanels/ForProject.php";
		$files[]="modules/Users/metadata/subpanels/ForProspectLists.php";
		$files[]="modules/Users/metadata/subpanels/ForTeams.php";
		$files[]="modules/Users/metadata/subpanels/default.php";
		$files[]="modules/Users/metadata/subpanels/ForMeetings.php";
		$files[]="modules/Users/metadata/subpanels/ForCalls.php";
		$files[]="modules/Users/metadata/listviewdefs.php";
		$files[]="modules/Users/metadata/SearchFields.php";
		$files[]="modules/Users/ListRoles.php";
		$files[]="modules/Users/SetTimezone.php";
		$files[]="modules/Users/Error.php";
		$files[]="modules/Users/Authenticate.php";
		$files[]="modules/Users/reassignUserRecords.php";
		$files[]="modules/Users/SaveSignature.php";
		$files[]="modules/Users/UserSignature.php";
		$files[]="modules/Users/ChangePassword.php";
		$files[]="modules/Users/PopupUsers.php";
		$files[]="modules/Users/EditView.php";
		$files[]="modules/vCals/field_arrays.php";
		$files[]="modules/vCals/vCal.php";
		$files[]="modules/vCals/HTTP_WebDAV_Server_vCal.php";
		$files[]="modules/vCals/vardefs.php";
		$files[]="modules/vCals/Server.php";
		$files[]="modules/UserPreferences/field_arrays.php";
		$files[]="modules/UserPreferences/UserPreference.php";
		$files[]="modules/UserPreferences/index.php";
		$files[]="modules/UserPreferences/vardefs.php";
		$files[]="modules/MySettings/language/en_us.lang.php";
		$files[]="modules/MySettings/TabController.php";
		$files[]="modules/MySettings/LoadTabSubpanels.php";
		$files[]="modules/MySettings/StoreQuery.php";
		$files[]="modules/Emails/views/view.classic.config.php";
		$files[]="modules/Emails/EmailUI.php";
		$files[]="modules/Emails/field_arrays.php";
		$files[]="modules/Emails/MassDelete.php";
		$files[]="modules/Emails/Save.php";
		$files[]="modules/Emails/Grab.php";
		$files[]="modules/Emails/language/en_us.lang.php";
		$files[]="modules/Emails/SugarRoutingAsync.php";
		$files[]="modules/Emails/Popup.php";
		$files[]="modules/Emails/Status.php";
		$files[]="modules/Emails/EmailUIAjax.php";
		$files[]="modules/Emails/SubPanelViewRecipients.php";
		$files[]="modules/Emails/ListViewHome.php";
		$files[]="modules/Emails/PessimisticLock.php";
		$files[]="modules/Emails/Email.php";
		$files[]="modules/Emails/DetailView.php";
		$files[]="modules/Emails/subpanels/ForUsers.php";
		$files[]="modules/Emails/subpanels/ForHistory.php";
		$files[]="modules/Emails/subpanels/ForContacts.php";
		$files[]="modules/Emails/subpanels/ForQueues.php";
		$files[]="modules/Emails/Check.php";
		$files[]="modules/Emails/index.php";
		$files[]="modules/Emails/Delete.php";
		$files[]="modules/Emails/Menu.php";
		$files[]="modules/Emails/ListView.php";
		$files[]="modules/Emails/Forms.php";
		$files[]="modules/Emails/vardefs.php";
		$files[]="modules/Emails/ListViewAll.php";
		$files[]="modules/Emails/ListViewGroup.php";
		$files[]="modules/Emails/metadata/subpaneldefs.php";
		$files[]="modules/Emails/metadata/popupdefs.php";
		$files[]="modules/Emails/metadata/subpanels/ForUsers.php";
		$files[]="modules/Emails/metadata/subpanels/ForHistory.php";
		$files[]="modules/Emails/metadata/subpanels/ForContacts.php";
		$files[]="modules/Emails/metadata/subpanels/ForQueues.php";
		$files[]="modules/Emails/metadata/subpanels/ForUnlinkedEmailHistory.php";
		$files[]="modules/Emails/metadata/additionalDetails.php";
		$files[]="modules/Emails/PopupDocuments.php";
		$files[]="modules/Emails/EditView.php";
		$files[]="modules/Emails/Popup_picker.php";
		$files[]="modules/Emails/Distribute.php";
		$files[]="modules/Emails/Compose.php";
		$files[]="modules/DynamicFields/templates/Files/DetailView.php";
		$files[]="modules/DynamicFields/templates/Files/EditView.php";
		$files[]="modules/DynamicFields/templates/Fields/Forms/radioenum.php";
		$files[]="modules/DynamicFields/templates/Fields/Forms/date.php";
		$files[]="modules/DynamicFields/templates/Fields/Forms/multienum.php";
		$files[]="modules/DynamicFields/templates/Fields/Forms/relate.php";
		$files[]="modules/DynamicFields/templates/Fields/Forms/iframe.php";
		$files[]="modules/DynamicFields/templates/Fields/Forms/url.php";
		$files[]="modules/DynamicFields/templates/Fields/Forms/enum2.php";
		$files[]="modules/DynamicFields/templates/Fields/Forms/encrypt.php";
		$files[]="modules/DynamicFields/templates/Fields/Forms/parent.php";
		$files[]="modules/DynamicFields/templates/Fields/Forms/html.php";
		$files[]="modules/DynamicFields/templates/Fields/TemplateCurrencyId.php";
		$files[]="modules/DynamicFields/templates/Fields/TemplateText.php";
		$files[]="modules/DynamicFields/templates/Fields/TemplateFloat.php";
		$files[]="modules/DynamicFields/templates/Fields/TemplateEncrypt.php";
		$files[]="modules/DynamicFields/templates/Fields/TemplateAddress.php";
		$files[]="modules/DynamicFields/templates/Fields/TemplateMultiEnum.php";
		$files[]="modules/DynamicFields/templates/Fields/TemplateRelatedTextField.php";
		$files[]="modules/DynamicFields/templates/Fields/TemplateField.php";
		$files[]="modules/DynamicFields/templates/Fields/TemplateIFrame.php";
		$files[]="modules/DynamicFields/templates/Fields/TemplateBoolean.php";
		$files[]="modules/DynamicFields/templates/Fields/TemplateParentType.php";
		$files[]="modules/DynamicFields/templates/Fields/TemplateHTML.php";
		$files[]="modules/DynamicFields/templates/Fields/TemplateURL.php";
		$files[]="modules/DynamicFields/templates/Fields/TemplateParent.php";
		$files[]="modules/DynamicFields/templates/Fields/TemplateAddressCountry.php";
		$files[]="modules/DynamicFields/templates/Fields/TemplateEnum.php";
		$files[]="modules/DynamicFields/templates/Fields/TemplateEmail.php";
		$files[]="modules/DynamicFields/templates/Fields/TemplateId.php";
		$files[]="modules/DynamicFields/templates/Fields/TemplatePhone.php";
		$files[]="modules/DynamicFields/templates/Fields/TemplateInt.php";
		$files[]="modules/DynamicFields/templates/Fields/TemplateDate.php";
		$files[]="modules/DynamicFields/templates/Fields/TemplateRadioEnum.php";
		$files[]="modules/DynamicFields/templates/Fields/TemplateTextArea.php";
		$files[]="modules/DynamicFields/templates/Fields/TemplateCurrency.php";
		$files[]="modules/DynamicFields/FieldViewer.php";
		$files[]="modules/DynamicFields/Save.php";
		$files[]="modules/DynamicFields/language/en_us.lang.php";
		$files[]="modules/DynamicFields/UpgradeFields.php";
		$files[]="modules/DynamicFields/DynamicField.php";
		$files[]="modules/DynamicFields/FieldCases.php";
		$files[]="modules/BeanDictionary.php";
		$files[]="modules/ACLActions/language/en_us.lang.php";
		$files[]="modules/ACLActions/ACLAction.php";
		$files[]="modules/ACLActions/actiondefs.php";
		$files[]="modules/ACLActions/Menu.php";
		$files[]="modules/ACLActions/Forms.php";
		$files[]="modules/ACLActions/vardefs.php";
		$files[]="modules/ACLActions/metadata/subpaneldefs.php";
		$files[]="modules/Relationships/field_arrays.php";
		$files[]="modules/Relationships/language/en_us.lang.php";
		$files[]="modules/Relationships/vardefs.php";
		$files[]="modules/Relationships/RelationshipHandler.php";
		$files[]="modules/Relationships/Relationship.php";
		$files[]="modules/Calendar/views/view.list.php";
		$files[]="modules/Calendar/templates/templates_calendar.php";
		$files[]="modules/Calendar/templates/template_shared_calendar.php";
		$files[]="modules/Calendar/language/en_us.lang.php";
		$files[]="modules/Calendar/DateTime.php";
		$files[]="modules/Calendar/DateTimeUtil.php";
		$files[]="modules/Calendar/index.php";
		$files[]="modules/Calendar/Calendar.php";
		$files[]="modules/Calendar/Menu.php";
		$files[]="modules/Calendar/Forms.php";
		$files[]="modules/Calendar/TasksListView.php";
		$files[]="modules/Calendar/small_month.php";
		$files[]="modules/Calendar/metadata/listviewdefs.php";
		$files[]="modules/Calendar/SubPanelSharedCalendar.php";
		$files[]="modules/Meetings/views/view.edit.php";
		$files[]="modules/Meetings/MeetingsQuickCreate.php";
		$files[]="modules/Meetings/field_arrays.php";
		$files[]="modules/Meetings/Save.php";
		$files[]="modules/Meetings/language/en_us.lang.php";
		$files[]="modules/Meetings/MeetingFormBase.php";
		$files[]="modules/Meetings/Menu.php";
		$files[]="modules/Meetings/Forms.php";
		$files[]="modules/Meetings/vardefs.php";
		$files[]="modules/Meetings/SubPanelViewInvitees.php";
		$files[]="modules/Meetings/metadata/subpaneldefs.php";
		$files[]="modules/Meetings/metadata/editviewdefs.php";
		$files[]="modules/Meetings/metadata/detailviewdefs.php";
		$files[]="modules/Meetings/metadata/subpanels/ForHistory.php";
		$files[]="modules/Meetings/metadata/subpanels/ForActivities.php";
		$files[]="modules/Meetings/metadata/subpanels/default.php";
		$files[]="modules/Meetings/metadata/studio.php";
		$files[]="modules/Meetings/metadata/listviewdefs.php";
		$files[]="modules/Meetings/metadata/SearchFields.php";
		$files[]="modules/Meetings/metadata/additionalDetails.php";
		$files[]="modules/Meetings/metadata/sidecreateviewdefs.php";
		$files[]="modules/Meetings/metadata/searchdefs.php";
		$files[]="modules/Meetings/Dashlets/MyMeetingsDashlet/MyMeetingsDashlet.php";
		$files[]="modules/Meetings/Dashlets/MyMeetingsDashlet/MyMeetingsDashlet.meta.php";
		$files[]="modules/Meetings/Dashlets/MyMeetingsDashlet/MyMeetingsDashlet.data.php";
		$files[]="modules/Meetings/Meeting.php";
		$files[]="modules/Charts/language/en_us.lang.php";
		$files[]="modules/Charts/PredefinedChart.php";
		$files[]="modules/Charts/code/Chart_lead_source_by_outcome.php";
		$files[]="modules/Charts/code/predefined_charts.php";
		$files[]="modules/Charts/code/Chart_pipeline_by_sales_stage.php";
		$files[]="modules/Charts/code/Chart_my_pipeline_by_sales_stage.php";
		$files[]="modules/Charts/code/Chart_pipeline_by_lead_source.php";
		$files[]="modules/Charts/code/Chart_outcome_by_month.php";
		$files[]="modules/Charts/Dashlets/OpportunitiesByLeadSourceDashlet/OpportunitiesByLeadSourceDashlet.php";
		$files[]="modules/Charts/Dashlets/OpportunitiesByLeadSourceDashlet/OpportunitiesByLeadSourceDashlet.meta.php";
		$files[]="modules/Charts/Dashlets/OpportunitiesByLeadSourceDashlet/OpportunitiesByLeadSourceDashlet.en_us.lang.php";
		$files[]="modules/Charts/Dashlets/OpportunitiesByLeadSourceDashlet/OpportunitiesByLeadSourceDashlet.data.php";
		$files[]="modules/Charts/Dashlets/MyPipelineBySalesStageDashlet/MyPipelineBySalesStageDashlet.data.php";
		$files[]="modules/Charts/Dashlets/MyPipelineBySalesStageDashlet/MyPipelineBySalesStageDashlet.meta.php";
		$files[]="modules/Charts/Dashlets/MyPipelineBySalesStageDashlet/MyPipelineBySalesStageDashlet.en_us.lang.php";
		$files[]="modules/Charts/Dashlets/MyPipelineBySalesStageDashlet/MyPipelineBySalesStageDashlet.php";
		$files[]="modules/Charts/Dashlets/CampaignROIChartDashlet/CampaignROIChartDashlet.data.php";
		$files[]="modules/Charts/Dashlets/CampaignROIChartDashlet/CampaignROIChartDashlet.meta.php";
		$files[]="modules/Charts/Dashlets/CampaignROIChartDashlet/CampaignROIChartDashlet.en_us.lang.php";
		$files[]="modules/Charts/Dashlets/CampaignROIChartDashlet/CampaignROIChartDashlet.php";
		$files[]="modules/Charts/Dashlets/OpportunitiesByLeadSourceByOutcomeDashlet/OpportunitiesByLeadSourceByOutcomeDashlet.data.php";
		$files[]="modules/Charts/Dashlets/OpportunitiesByLeadSourceByOutcomeDashlet/OpportunitiesByLeadSourceByOutcomeDashlet.php";
		$files[]="modules/Charts/Dashlets/OpportunitiesByLeadSourceByOutcomeDashlet/OpportunitiesByLeadSourceByOutcomeDashlet.en_us.lang.php";
		$files[]="modules/Charts/Dashlets/OpportunitiesByLeadSourceByOutcomeDashlet/OpportunitiesByLeadSourceByOutcomeDashlet.meta.php";
		$files[]="modules/Charts/Dashlets/OutcomeByMonthDashlet/OutcomeByMonthDashlet.en_us.lang.php";
		$files[]="modules/Charts/Dashlets/OutcomeByMonthDashlet/OutcomeByMonthDashlet.data.php";
		$files[]="modules/Charts/Dashlets/OutcomeByMonthDashlet/OutcomeByMonthDashlet.php";
		$files[]="modules/Charts/Dashlets/OutcomeByMonthDashlet/OutcomeByMonthDashlet.meta.php";
		$files[]="modules/Charts/Dashlets/PipelineBySalesStageDashlet/PipelineBySalesStageDashlet.meta.php";
		$files[]="modules/Charts/Dashlets/PipelineBySalesStageDashlet/PipelineBySalesStageDashlet.php";
		$files[]="modules/Charts/Dashlets/PipelineBySalesStageDashlet/PipelineBySalesStageDashlet.en_us.lang.php";
		$files[]="modules/Charts/Dashlets/PipelineBySalesStageDashlet/PipelineBySalesStageDashlet.data.php";
		$files[]="modules/Charts/chartdefs.php";
		$files[]="modules/Releases/field_arrays.php";
		$files[]="modules/Releases/Save.php";
		$files[]="modules/Releases/language/en_us.lang.php";
		$files[]="modules/Releases/DetailView.php";
		$files[]="modules/Releases/index.php";
		$files[]="modules/Releases/Menu.php";
		$files[]="modules/Releases/Forms.php";
		$files[]="modules/Releases/Release.php";
		$files[]="modules/Releases/vardefs.php";
		$files[]="modules/Releases/EditView.php";
		$files[]="modules/Releases/Popup_picker.php";
		$files[]="modules/Opportunities/views/view.sidequickcreate.php";
		$files[]="modules/Opportunities/views/view.edit.php";
		$files[]="modules/Opportunities/views/view.detail.php";
		$files[]="modules/Opportunities/field_arrays.php";
		$files[]="modules/Opportunities/ListViewTop.php";
		$files[]="modules/Opportunities/Save.php";
		$files[]="modules/Opportunities/Opportunity.php";
		$files[]="modules/Opportunities/language/en_us.lang.php";
		$files[]="modules/Opportunities/SubPanelViewProjects.php";
		$files[]="modules/Opportunities/OpportunityFormBase.php";
		$files[]="modules/Opportunities/SaveOverload.php";
		$files[]="modules/Opportunities/Menu.php";
		$files[]="modules/Opportunities/Forms.php";
		$files[]="modules/Opportunities/OpportunitiesQuickCreate.php";
		$files[]="modules/Opportunities/SugarFeeds/OppFeed.php";
		$files[]="modules/Opportunities/vardefs.php";
		$files[]="modules/Opportunities/SubPanelView.php";
		$files[]="modules/Opportunities/metadata/subpaneldefs.php";
		$files[]="modules/Opportunities/metadata/metafiles.php";
		$files[]="modules/Opportunities/metadata/editviewdefs.php";
		$files[]="modules/Opportunities/metadata/popupdefs.php";
		$files[]="modules/Opportunities/metadata/quickcreatedefs.php";
		$files[]="modules/Opportunities/metadata/acldefs.php";
		$files[]="modules/Opportunities/metadata/detailviewdefs.php";
		$files[]="modules/Opportunities/metadata/subpanels/ForEmails.php";
		$files[]="modules/Opportunities/metadata/subpanels/ForAccounts.php";
		$files[]="modules/Opportunities/metadata/subpanels/default.php";
		$files[]="modules/Opportunities/metadata/studio.php";
		$files[]="modules/Opportunities/metadata/listviewdefs.php";
		$files[]="modules/Opportunities/metadata/SearchFields.php";
		$files[]="modules/Opportunities/metadata/additionalDetails.php";
		$files[]="modules/Opportunities/metadata/sidecreateviewdefs.php";
		$files[]="modules/Opportunities/metadata/searchdefs.php";
		$files[]="modules/Opportunities/Dashlets/MyOpportunitiesDashlet/MyOpportunitiesDashlet.meta.php";
		$files[]="modules/Opportunities/Dashlets/MyOpportunitiesDashlet/MyOpportunitiesDashlet.data.php";
		$files[]="modules/Opportunities/Dashlets/MyOpportunitiesDashlet/MyOpportunitiesDashlet.php";
		$files[]="modules/Opportunities/Dashlets/MyClosedOpportunitiesDashlet/MyClosedOpportunitiesDashlet.meta.php";
		$files[]="modules/Opportunities/Dashlets/MyClosedOpportunitiesDashlet/MyClosedOpportunitiesDashlet.php";
		$files[]="modules/Notes/field_arrays.php";
		$files[]="modules/Notes/language/en_us.lang.php";
		$files[]="modules/Notes/controller.php";
		$files[]="modules/Notes/Note.php";
		$files[]="modules/Notes/NoteSoap.php";
		$files[]="modules/Notes/NoteFormBase.php";
		$files[]="modules/Notes/Menu.php";
		$files[]="modules/Notes/NotesQuickCreate.php";
		$files[]="modules/Notes/Forms.php";
		$files[]="modules/Notes/vardefs.php";
		$files[]="modules/Notes/SubPanelView.php";
		$files[]="modules/Notes/metadata/editviewdefs.php";
		$files[]="modules/Notes/metadata/detailviewdefs.php";
		$files[]="modules/Notes/metadata/subpanels/ForHistory.php";
		$files[]="modules/Notes/metadata/subpanels/default.php";
		$files[]="modules/Notes/metadata/studio.php";
		$files[]="modules/Notes/metadata/listviewdefs.php";
		$files[]="modules/Notes/metadata/SearchFields.php";
		$files[]="modules/Notes/metadata/additionalDetails.php";
		$files[]="modules/Notes/metadata/sidecreateviewdefs.php";
		$files[]="modules/Notes/metadata/searchdefs.php";
		$files[]="modules/Bugs/views/view.edit.php";
		$files[]="modules/Bugs/views/view.detail.php";
		$files[]="modules/Bugs/field_arrays.php";
		$files[]="modules/Bugs/language/en_us.lang.php";
		$files[]="modules/Bugs/BugsQuickCreate.php";
		$files[]="modules/Bugs/Bug.php";
		$files[]="modules/Bugs/Menu.php";
		$files[]="modules/Bugs/Forms.php";
		$files[]="modules/Bugs/vardefs.php";
		$files[]="modules/Bugs/metadata/subpaneldefs.php";
		$files[]="modules/Bugs/metadata/metafiles.php";
		$files[]="modules/Bugs/metadata/editviewdefs.php";
		$files[]="modules/Bugs/metadata/popupdefs.php";
		$files[]="modules/Bugs/metadata/quickcreatedefs.php";
		$files[]="modules/Bugs/metadata/detailviewdefs.php";
		$files[]="modules/Bugs/metadata/subpanels/ForEmails.php";
		$files[]="modules/Bugs/metadata/subpanels/default.php";
		$files[]="modules/Bugs/metadata/studio.php";
		$files[]="modules/Bugs/metadata/listviewdefs.php";
		$files[]="modules/Bugs/metadata/SearchFields.php";
		$files[]="modules/Bugs/metadata/additionalDetails.php";
		$files[]="modules/Bugs/metadata/sidecreateviewdefs.php";
		$files[]="modules/Bugs/metadata/searchdefs.php";
		$files[]="modules/Bugs/Dashlets/MyBugsDashlet/MyBugsDashlet.meta.php";
		$files[]="modules/Bugs/Dashlets/MyBugsDashlet/MyBugsDashlet.data.php";
		$files[]="modules/Bugs/Dashlets/MyBugsDashlet/MyBugsDashlet.php";
		$files[]="modules/Import/ImportMapSalesforce.php";
		$files[]="modules/Import/views/view.undo.php";
		$files[]="modules/Import/views/view.last.php";
		$files[]="modules/Import/views/view.step4.php";
		$files[]="modules/Import/views/view.step2.php";
		$files[]="modules/Import/views/view.step3.php";
		$files[]="modules/Import/views/view.error.php";
		$files[]="modules/Import/views/view.step1.php";
		$files[]="modules/Import/language/en_us.lang.php";
		$files[]="modules/Import/ImportDuplicateCheck.php";
		$files[]="modules/Import/controller.php";
		$files[]="modules/Import/ImportFileSplitter.php";
		$files[]="modules/Import/ImportCacheFiles.php";
		$files[]="modules/Import/ImportMapCsv.php";
		$files[]="modules/Import/Menu.php";
		$files[]="modules/Import/ImportMapAct.php";
		$files[]="modules/Import/Forms.php";
		$files[]="modules/Import/vardefs.php";
		$files[]="modules/Import/ImportMapOutlook.php";
		$files[]="modules/Import/ImportMapTab.php";
		$files[]="modules/Import/ImportMap.php";
		$files[]="modules/Import/UsersLastImport.php";
		$files[]="modules/Import/ImportMapOther.php";
		$files[]="modules/Import/ImportFieldSanitize.php";
		$files[]="modules/Import/ImportFile.php";
		$files[]="modules/Import/ImportMapJigsaw.php";
		$files[]="modules/Project/views/view.templatesedit.php";
		$files[]="modules/Project/views/view.templatesdetail.php";
		$files[]="modules/Project/views/view.list.php";
		$files[]="modules/Project/views/view.edit.php";
		$files[]="modules/Project/views/view.detail.php";
		$files[]="modules/Project/field_arrays.php";
		$files[]="modules/Project/Save.php";
		$files[]="modules/Project/language/en_us.lang.php";
		$files[]="modules/Project/Project.php";
		$files[]="modules/Project/Popup.php";
		$files[]="modules/Project/Delete.php";
		$files[]="modules/Project/Menu.php";
		$files[]="modules/Project/Forms.php";
		$files[]="modules/Project/vardefs.php";
		$files[]="modules/Project/SubPanelView.php";
		$files[]="modules/Project/metadata/subpaneldefs.php";
		$files[]="modules/Project/metadata/metafiles.php";
		$files[]="modules/Project/metadata/editviewdefs.php";
		$files[]="modules/Project/metadata/popupdefs.php";
		$files[]="modules/Project/metadata/detailviewdefs.php";
		$files[]="modules/Project/metadata/subpanels/ForEmails.php";
		$files[]="modules/Project/metadata/subpanels/default.php";
		$files[]="modules/Project/metadata/studio.php";
		$files[]="modules/Project/metadata/listviewdefs.php";
		$files[]="modules/Project/metadata/SearchFields.php";
		$files[]="modules/Project/metadata/additionalDetails.php";
		$files[]="modules/Project/metadata/sidecreateviewdefs.php";
		$files[]="modules/Project/metadata/searchdefs.php";
		$files[]="modules/Project/action_view_map.php";
		$files[]="modules/Project/ProjectQuickCreate.php";
		$files[]="modules/Project/Popup_picker.php";
		$files[]="modules/OptimisticLock/LockResolve.php";
		$files[]="modules/OptimisticLock/language/en_us.lang.php";
		$files[]="modules/OptimisticLock/Menu.php";
		$files[]="modules/OptimisticLock/Forms.php";
		$files[]="modules/EmailMan/field_arrays.php";
		$files[]="modules/EmailMan/Save.php";
		$files[]="modules/EmailMan/language/en_us.lang.php";
		$files[]="modules/EmailMan/campaignconfig.php";
		$files[]="modules/EmailMan/subpanels/default.php";
		$files[]="modules/EmailMan/index.php";
		$files[]="modules/EmailMan/config.php";
		$files[]="modules/EmailMan/Menu.php";
		$files[]="modules/EmailMan/ListView.php";
		$files[]="modules/EmailMan/Forms.php";
		$files[]="modules/EmailMan/vardefs.php";
		$files[]="modules/EmailMan/metadata/subpanels/default.php";
		$files[]="modules/EmailMan/metadata/SearchFields.php";
		$files[]="modules/EmailMan/EmailManDelivery.php";
		$files[]="modules/EmailMan/EmailMan.php";
		$files[]="modules/SchedulersJobs/field_arrays.php";
		$files[]="modules/SchedulersJobs/language/en_us.lang.php";
		$files[]="modules/SchedulersJobs/SchedulersJob.php";
		$files[]="modules/SchedulersJobs/vardefs.php";
		$files[]="modules/SchedulersJobs/metadata/subpanels/default.php";
		$files[]="modules/Studio/TabGroups.php";
		$files[]="modules/Studio/wizard.php";
		$files[]="modules/Studio/TabGroups/TabGroupHelper.php";
		$files[]="modules/Studio/TabGroups/EditViewTabs.php";
		$files[]="modules/Studio/parsers/StudioParser.php";
		$files[]="modules/Studio/language/en_us.lang.php";
		$files[]="modules/Studio/config.php";
		$files[]="modules/Studio/SaveTabs.php";
		$files[]="modules/Studio/DropDowns/DropDownHelper.php";
		$files[]="modules/Studio/DropDowns/EditView.php";
		$files[]="modules/Studio/Forms.php";
		$files[]="modules/Studio/wizards/EditDropDownWizard.php";
		$files[]="modules/Studio/wizards/StudioWizard.php";
		$files[]="modules/EmailMarketing/field_arrays.php";
		$files[]="modules/EmailMarketing/EmailMarketing.php";
		$files[]="modules/EmailMarketing/Save.php";
		$files[]="modules/EmailMarketing/language/en_us.lang.php";
		$files[]="modules/EmailMarketing/DetailView.php";
		$files[]="modules/EmailMarketing/subpanels/default.php";
		$files[]="modules/EmailMarketing/Delete.php";
		$files[]="modules/EmailMarketing/Menu.php";
		$files[]="modules/EmailMarketing/Forms.php";
		$files[]="modules/EmailMarketing/vardefs.php";
		$files[]="modules/EmailMarketing/SubPanelView.php";
		$files[]="modules/EmailMarketing/metadata/subpaneldefs.php";
		$files[]="modules/EmailMarketing/metadata/subpanels/default.php";
		$files[]="modules/EmailMarketing/EditView.php";
		$files[]="modules/CampaignLog/language/en_us.lang.php";
		$files[]="modules/CampaignLog/CampaignLog.php";
		$files[]="modules/CampaignLog/Menu.php";
		$files[]="modules/CampaignLog/Forms.php";
		$files[]="modules/CampaignLog/vardefs.php";
		$files[]="modules/CampaignLog/metadata/subpanels/ForTargets.php";
		$files[]="modules/CampaignLog/metadata/subpanels/default.php";
		$files[]="modules/CampaignLog/Popup_picker.php";
		$files[]="modules/Groups/Group.php";
		$files[]="modules/Groups/Save.php";
		$files[]="modules/Groups/language/en_us.lang.php";
		$files[]="modules/Groups/DetailView.php";
		$files[]="modules/Groups/index.php";
		$files[]="modules/Groups/Delete.php";
		$files[]="modules/Groups/Menu.php";
		$files[]="modules/Groups/ListView.php";
		$files[]="modules/Groups/Forms.php";
		$files[]="modules/Groups/vardefs.php";
		$files[]="modules/Groups/EditView.php";
		$files[]="modules/SavedSearch/field_arrays.php";
		$files[]="modules/SavedSearch/language/en_us.lang.php";
		$files[]="modules/SavedSearch/index.php";
		$files[]="modules/SavedSearch/Menu.php";
		$files[]="modules/SavedSearch/ListView.php";
		$files[]="modules/SavedSearch/Forms.php";
		$files[]="modules/SavedSearch/UpgradeSavedSearch.php";
		$files[]="modules/SavedSearch/vardefs.php";
		$files[]="modules/SavedSearch/metadata/listviewdefs.php";
		$files[]="modules/SavedSearch/SavedSearch.php";
		$files[]="modules/Campaigns/WebToLeadCreation.php";
		$files[]="modules/Campaigns/views/view.detail.php";
		$files[]="modules/Campaigns/views/view.newsletterlist.php";
		$files[]="modules/Campaigns/field_arrays.php";
		$files[]="modules/Campaigns/RoiDetailView.php";
		$files[]="modules/Campaigns/Charts.php";
		$files[]="modules/Campaigns/Save.php";
		$files[]="modules/Campaigns/language/en_us.lang.php";
		$files[]="modules/Campaigns/QueueCampaign.php";
		$files[]="modules/Campaigns/controller.php";
		$files[]="modules/Campaigns/WebToLeadCapture.php";
		$files[]="modules/Campaigns/WizardMarketingSave.php";
		$files[]="modules/Campaigns/CampaignDiagnostic.php";
		$files[]="modules/Campaigns/WizardNewsletter.php";
		$files[]="modules/Campaigns/Campaign.php";
		$files[]="modules/Campaigns/Tracker.php";
		$files[]="modules/Campaigns/TrackDetailView.php";
		$files[]="modules/Campaigns/ProcessBouncedEmails.php";
		$files[]="modules/Campaigns/MailMerge.php";
		$files[]="modules/Campaigns/RemoveMe.php";
		$files[]="modules/Campaigns/Schedule.php";
		$files[]="modules/Campaigns/WizardMarketing.php";
		$files[]="modules/Campaigns/WebToLeadFormSave.php";
		$files[]="modules/Campaigns/PopupCampaignRoi.php";
		$files[]="modules/Campaigns/WizardEmailSetupSave.php";
		$files[]="modules/Campaigns/image.php";
		$files[]="modules/Campaigns/Delete.php";
		$files[]="modules/Campaigns/Subscriptions.php";
		$files[]="modules/Campaigns/utils.php";
		$files[]="modules/Campaigns/Menu.php";
		$files[]="modules/Campaigns/Forms.php";
		$files[]="modules/Campaigns/vardefs.php";
		$files[]="modules/Campaigns/WizardNewsletterSave.php";
		$files[]="modules/Campaigns/metadata/subpaneldefs.php";
		$files[]="modules/Campaigns/metadata/editviewdefs.php";
		$files[]="modules/Campaigns/metadata/popupdefs.php";
		$files[]="modules/Campaigns/metadata/detailviewdefs.php";
		$files[]="modules/Campaigns/metadata/subpanels/ForEmailMarketing.php";
		$files[]="modules/Campaigns/metadata/subpanels/default.php";
		$files[]="modules/Campaigns/metadata/studio.php";
		$files[]="modules/Campaigns/metadata/listviewdefs.php";
		$files[]="modules/Campaigns/metadata/SearchFields.php";
		$files[]="modules/Campaigns/metadata/additionalDetails.php";
		$files[]="modules/Campaigns/metadata/sidecreateviewdefs.php";
		$files[]="modules/Campaigns/metadata/searchdefs.php";
		$files[]="modules/Campaigns/Dashlets/TopCampaignsDashlet/TopCampaignsDashlet.php";
		$files[]="modules/Campaigns/Dashlets/TopCampaignsDashlet/TopCampaignsDashlet.meta.php";
		$files[]="modules/Campaigns/WizardEmailSetup.php";
		$files[]="modules/Campaigns/EmailQueue.php";
		$files[]="modules/Campaigns/Charts1.php";
		$files[]="modules/Campaigns/GenerateWebToLeadForm.php";
		$files[]="modules/Campaigns/WizardHome.php";
		$files[]="modules/Campaigns/CaptchaValidate.php";
		$files[]="modules/Campaigns/Popup_picker.php";
		$files[]="modules/ModuleBuilder/views/view.displaydeployresult.php";
		$files[]="modules/ModuleBuilder/views/view.modulefield.php";
		$files[]="modules/ModuleBuilder/views/view.main.php";
		$files[]="modules/ModuleBuilder/views/view.dashlet.php";
		$files[]="modules/ModuleBuilder/views/view.package.php";
		$files[]="modules/ModuleBuilder/views/view.dropdown.php";
		$files[]="modules/ModuleBuilder/views/view.deletemodule.php";
		$files[]="modules/ModuleBuilder/views/view.listview.php";
		$files[]="modules/ModuleBuilder/views/view.searchview.php";
		$files[]="modules/ModuleBuilder/views/view.module.php";
		$files[]="modules/ModuleBuilder/views/view.layoutview.php";
		$files[]="modules/ModuleBuilder/views/view.deletepackage.php";
		$files[]="modules/ModuleBuilder/views/view.tree.php";
		$files[]="modules/ModuleBuilder/views/view.relationship.php";
		$files[]="modules/ModuleBuilder/views/view.history.php";
		$files[]="modules/ModuleBuilder/views/view.dropdowns.php";
		$files[]="modules/ModuleBuilder/views/view.home.php";
		$files[]="modules/ModuleBuilder/views/view.labels.php";
		$files[]="modules/ModuleBuilder/views/view.displaydeploy.php";
		$files[]="modules/ModuleBuilder/views/view.wizard.php";
		$files[]="modules/ModuleBuilder/views/view.exportcustomizations.php";
		$files[]="modules/ModuleBuilder/views/view.modulelabels.php";
		$files[]="modules/ModuleBuilder/views/view.relationships.php";
		$files[]="modules/ModuleBuilder/views/view.property.php";
		$files[]="modules/ModuleBuilder/views/view.modulefields.php";
		$files[]="modules/ModuleBuilder/parsers/views/DashletMetaDataParser.php";
		$files[]="modules/ModuleBuilder/parsers/views/AbstractMetaDataImplementation.php";
		$files[]="modules/ModuleBuilder/parsers/views/ListLayoutMetaDataParser.php";
		$files[]="modules/ModuleBuilder/parsers/views/GridLayoutMetaDataParser.php";
		$files[]="modules/ModuleBuilder/parsers/views/DeployedSubpanelImplementation.php";
		$files[]="modules/ModuleBuilder/parsers/views/MetaDataImplementationInterface.php";
		$files[]="modules/ModuleBuilder/parsers/views/HistoryInterface.php";
		$files[]="modules/ModuleBuilder/parsers/views/UndeployedMetaDataImplementation.php";
		$files[]="modules/ModuleBuilder/parsers/views/DeployedMetaDataImplementation.php";
		$files[]="modules/ModuleBuilder/parsers/views/History.php";
		$files[]="modules/ModuleBuilder/parsers/views/ListViewMetaDataParser.php";
		$files[]="modules/ModuleBuilder/parsers/views/AbstractMetaDataParser.php";
		$files[]="modules/ModuleBuilder/parsers/views/UndeployedSubpanelImplementation.php";
		$files[]="modules/ModuleBuilder/parsers/views/MetaDataParserInterface.php";
		$files[]="modules/ModuleBuilder/parsers/views/SearchViewMetaDataParser.php";
		$files[]="modules/ModuleBuilder/parsers/views/SubpanelMetaDataParser.php";
		$files[]="modules/ModuleBuilder/parsers/ModuleBuilderParser.php";
		$files[]="modules/ModuleBuilder/parsers/parser.modifylistview.php";
		$files[]="modules/ModuleBuilder/parsers/constants.php";
		$files[]="modules/ModuleBuilder/parsers/parser.modifylayoutview.php";
		$files[]="modules/ModuleBuilder/parsers/relationships/OneToManyRelationship.php";
		$files[]="modules/ModuleBuilder/parsers/relationships/AbstractRelationship.php";
		$files[]="modules/ModuleBuilder/parsers/relationships/OneToOneRelationship.php";
		$files[]="modules/ModuleBuilder/parsers/relationships/DeployedRelationships.php";
		$files[]="modules/ModuleBuilder/parsers/relationships/ManyToManyRelationship.php";
		$files[]="modules/ModuleBuilder/parsers/relationships/UndeployedRelationships.php";
		$files[]="modules/ModuleBuilder/parsers/relationships/AbstractRelationships.php";
		$files[]="modules/ModuleBuilder/parsers/relationships/ActivitiesRelationship.php";
		$files[]="modules/ModuleBuilder/parsers/relationships/RelationshipsInterface.php";
		$files[]="modules/ModuleBuilder/parsers/relationships/RelationshipFactory.php";
		$files[]="modules/ModuleBuilder/parsers/parser.label.php";
		$files[]="modules/ModuleBuilder/parsers/ParserFactory.php";
		$files[]="modules/ModuleBuilder/parsers/parser.dropdown.php";
		$files[]="modules/ModuleBuilder/parsers/parser.modifysubpanel.php";
		$files[]="modules/ModuleBuilder/language/en_us.lang.php";
		$files[]="modules/ModuleBuilder/Module/StudioModule.php";
		$files[]="modules/ModuleBuilder/Module/MainTree.php";
		$files[]="modules/ModuleBuilder/Module/StudioBrowser.php";
		$files[]="modules/ModuleBuilder/Module/StudioTree.php";
		$files[]="modules/ModuleBuilder/Module/DropDownTree.php";
		$files[]="modules/ModuleBuilder/Module/DropDownBrowser.php";
		$files[]="modules/ModuleBuilder/controller.php";
		$files[]="modules/ModuleBuilder/Forms.php";
		$files[]="modules/ModuleBuilder/MB/AjaxCompose.php";
		$files[]="modules/ModuleBuilder/MB/MBPackage.php";
		$files[]="modules/ModuleBuilder/MB/MBVardefs.php";
		$files[]="modules/ModuleBuilder/MB/MBModule.php";
		$files[]="modules/ModuleBuilder/MB/MBLanguage.php";
		$files[]="modules/ModuleBuilder/MB/ModuleBuilder.php";
		$files[]="modules/ModuleBuilder/MB/MBPackageTree.php";
		$files[]="modules/ModuleBuilder/MB/MBField.php";
		$files[]="modules/ModuleBuilder/MB/header.php";
		$files[]="modules/ModuleBuilder/MB/MBRelationship.php";
		$files[]="modules/ModuleBuilder/action_view_map.php";
		$files[]="modules/ACLRoles/DetailUserRole.php";
		$files[]="modules/ACLRoles/Save.php";
		$files[]="modules/ACLRoles/language/en_us.lang.php";
		$files[]="modules/ACLRoles/EditRole.php";
		$files[]="modules/ACLRoles/DetailView.php";
		$files[]="modules/ACLRoles/index.php";
		$files[]="modules/ACLRoles/Delete.php";
		$files[]="modules/ACLRoles/Menu.php";
		$files[]="modules/ACLRoles/ListView.php";
		$files[]="modules/ACLRoles/Forms.php";
		$files[]="modules/ACLRoles/vardefs.php";
		$files[]="modules/ACLRoles/ACLRole.php";
		$files[]="modules/ACLRoles/ListUsers.php";
		$files[]="modules/ACLRoles/metadata/subpaneldefs.php";
		$files[]="modules/ACLRoles/metadata/popupdefs.php";
		$files[]="modules/ACLRoles/metadata/subpanels/admin.php";
		$files[]="modules/ACLRoles/metadata/subpanels/default.php";
		$files[]="modules/ACLRoles/EditView.php";
		$files[]="modules/ACLRoles/Popup_picker.php";
		$files[]="modules/EmailAddresses/language/en_us.lang.php";
		$files[]="modules/EmailAddresses/EmailAddress.php";
		$files[]="modules/EmailAddresses/vardefs.php";
		$files[]="modules/Cases/CasesQuickCreate.php";
		$files[]="modules/Cases/field_arrays.php";
		$files[]="modules/Cases/language/en_us.lang.php";
		$files[]="modules/Cases/Case.php";
		$files[]="modules/Cases/Menu.php";
		$files[]="modules/Cases/Forms.php";
		$files[]="modules/Cases/SugarFeeds/CaseFeed.php";
		$files[]="modules/Cases/vardefs.php";
		$files[]="modules/Cases/metadata/subpaneldefs.php";
		$files[]="modules/Cases/metadata/editviewdefs.php";
		$files[]="modules/Cases/metadata/popupdefs.php";
		$files[]="modules/Cases/metadata/accountsquickcreatedefs.php";
		$files[]="modules/Cases/metadata/quickcreatedefs.php";
		$files[]="modules/Cases/metadata/detailviewdefs.php";
		$files[]="modules/Cases/metadata/subpanels/ForEmails.php";
		$files[]="modules/Cases/metadata/subpanels/ForAccounts.php";
		$files[]="modules/Cases/metadata/subpanels/default.php";
		$files[]="modules/Cases/metadata/studio.php";
		$files[]="modules/Cases/metadata/listviewdefs.php";
		$files[]="modules/Cases/metadata/SearchFields.php";
		$files[]="modules/Cases/metadata/additionalDetails.php";
		$files[]="modules/Cases/metadata/sidecreateviewdefs.php";
		$files[]="modules/Cases/metadata/searchdefs.php";
		$files[]="modules/Cases/Dashlets/MyCasesDashlet/MyCasesDashlet.data.php";
		$files[]="modules/Cases/Dashlets/MyCasesDashlet/MyCasesDashlet.php";
		$files[]="modules/Cases/Dashlets/MyCasesDashlet/MyCasesDashlet.meta.php";
		$files[]="modules/EditCustomFields/Save.php";
		$files[]="modules/EditCustomFields/language/en_us.lang.php";
		$files[]="modules/EditCustomFields/Popup.php";
		$files[]="modules/EditCustomFields/CustomFieldsTableSchema.php";
		$files[]="modules/EditCustomFields/index.php";
		$files[]="modules/EditCustomFields/Delete.php";
		$files[]="modules/EditCustomFields/CustomFieldsTable.php";
		$files[]="modules/EditCustomFields/Saveold.php";
		$files[]="modules/EditCustomFields/Menu.php";
		$files[]="modules/EditCustomFields/ListView.php";
		$files[]="modules/EditCustomFields/Forms.php";
		$files[]="modules/EditCustomFields/FieldsMetaData.php";
		$files[]="modules/EditCustomFields/vardefs.php";
		$files[]="modules/EditCustomFields/DisplayDropDownValues.php";
		$files[]="modules/EditCustomFields/EditCustomFields.php";
		$files[]="modules/EditCustomFields/EditView.php";
		$files[]="modules/Feeds/field_arrays.php";
		$files[]="modules/Feeds/Feed.php";
		$files[]="modules/Feeds/Save.php";
		$files[]="modules/Feeds/language/en_us.lang.php";
		$files[]="modules/Feeds/MoveUp.php";
		$files[]="modules/Feeds/MyFeeds.php";
		$files[]="modules/Feeds/DeleteFavorite.php";
		$files[]="modules/Feeds/MoveDown.php";
		$files[]="modules/Feeds/DetailView.php";
		$files[]="modules/Feeds/index.php";
		$files[]="modules/Feeds/Menu.php";
		$files[]="modules/Feeds/ListView.php";
		$files[]="modules/Feeds/FeedFormBase.php";
		$files[]="modules/Feeds/AddFavorite.php";
		$files[]="modules/Feeds/Forms.php";
		$files[]="modules/Feeds/vardefs.php";
		$files[]="modules/Feeds/EditView.php";
		$files[]="modules/Configurator/LogView.php";
		$files[]="modules/Configurator/language/en_us.lang.php";
		$files[]="modules/Configurator/DetailView.php";
		$files[]="modules/Configurator/index.php";
		$files[]="modules/Configurator/Menu.php";
		$files[]="modules/Configurator/Forms.php";
		$files[]="modules/Configurator/Configurator.php";
		$files[]="modules/Configurator/UploadFileCheck.php";
		$files[]="modules/Configurator/EditView.php";
		$files[]="modules/Documents/views/view.edit.php";
		$files[]="modules/Documents/field_arrays.php";
		$files[]="modules/Documents/Save.php";
		$files[]="modules/Documents/language/en_us.lang.php";
		$files[]="modules/Documents/GetLatestRevision.php";
		$files[]="modules/Documents/Popup.php";
		$files[]="modules/Documents/DocumentSoap.php";
		$files[]="modules/Documents/TreeData.php";
		$files[]="modules/Documents/subpanels/ForContractType.php";
		$files[]="modules/Documents/subpanels/default.php";
		$files[]="modules/Documents/Delete.php";
		$files[]="modules/Documents/Menu.php";
		$files[]="modules/Documents/Forms.php";
		$files[]="modules/Documents/vardefs.php";
		$files[]="modules/Documents/metadata/subpaneldefs.php";
		$files[]="modules/Documents/metadata/editviewdefs.php";
		$files[]="modules/Documents/metadata/quickcreatedefs.php";
		$files[]="modules/Documents/metadata/detailviewdefs.php";
		$files[]="modules/Documents/metadata/subpanels/ForContractType.php";
		$files[]="modules/Documents/metadata/subpanels/default.php";
		$files[]="modules/Documents/metadata/studio.php";
		$files[]="modules/Documents/metadata/listviewdefs.php";
		$files[]="modules/Documents/metadata/SearchFields.php";
		$files[]="modules/Documents/metadata/sidecreateviewdefs.php";
		$files[]="modules/Documents/metadata/searchdefs.php";
		$files[]="modules/Documents/Document.php";
		$files[]="modules/Documents/Popup_picker.php";
		$files[]="modules/History/language/en_us.lang.php";
		$files[]="modules/History/metadata/subpaneldefs.php";
		$files[]="modules/Currencies/field_arrays.php";
		$files[]="modules/Currencies/language/en_us.lang.php";
		$files[]="modules/Currencies/ListCurrency.php";
		$files[]="modules/Currencies/index.php";
		$files[]="modules/Currencies/Menu.php";
		$files[]="modules/Currencies/Forms.php";
		$files[]="modules/Currencies/EditCurrency.php";
		$files[]="modules/Currencies/vardefs.php";
		$files[]="modules/Currencies/Currency.php";
		$files[]="modules/CampaignTrackers/Save.php";
		$files[]="modules/CampaignTrackers/language/en_us.lang.php";
		$files[]="modules/CampaignTrackers/DetailView.php";
		$files[]="modules/CampaignTrackers/Menu.php";
		$files[]="modules/CampaignTrackers/Forms.php";
		$files[]="modules/CampaignTrackers/CampaignTracker.php";
		$files[]="modules/CampaignTrackers/vardefs.php";
		$files[]="modules/CampaignTrackers/metadata/subpanels/default.php";
		$files[]="modules/CampaignTrackers/EditView.php";
		$files[]="modules/LabelEditor/Save.php";
		$files[]="modules/LabelEditor/language/en_us.lang.php";
		$files[]="modules/LabelEditor/Menu.php";
		$files[]="modules/LabelEditor/Forms.php";
		$files[]="modules/LabelEditor/LabelList.php";
		$files[]="modules/LabelEditor/EditView.php";
		$files[]="modules/Home/sitemap.php";
		$files[]="modules/Home/views/view.list.php";
		$files[]="modules/Home/UnifiedSearch.php";
		$files[]="modules/Home/language/en_us.lang.php";
		$files[]="modules/Home/TrainingPortal.php";
		$files[]="modules/Home/About.php";
		$files[]="modules/Home/AddToFavorites.php";
		$files[]="modules/Home/index.php";
		$files[]="modules/Home/SaveSubpanelLayout.php";
		$files[]="modules/Home/DynamicAction.php";
		$files[]="modules/Home/Menu.php";
		$files[]="modules/Home/Forms.php";
		$files[]="modules/Home/SubpanelCreates.php";
		$files[]="modules/Home/dashlets.php";
		$files[]="modules/Home/LastViewed.php";
		$files[]="modules/Home/Dashlets/ChartsDashlet/ChartsDashlet.en_us.lang.php";
		$files[]="modules/Home/Dashlets/ChartsDashlet/ChartsDashlet.meta.php";
		$files[]="modules/Home/Dashlets/ChartsDashlet/ChartsDashlet.php";
		$files[]="modules/Home/Dashlets/JotPadDashlet/JotPadDashlet.en_us.lang.php";
		$files[]="modules/Home/Dashlets/JotPadDashlet/JotPadDashlet.php";
		$files[]="modules/Home/Dashlets/JotPadDashlet/JotPadDashlet.meta.php";
		$files[]="modules/Home/Dashlets/InvadersDashlet/InvadersDashlet.en_us.lang.php";
		$files[]="modules/Home/Dashlets/InvadersDashlet/InvadersDashlet.meta.php";
		$files[]="modules/Home/Dashlets/InvadersDashlet/InvadersDashlet.php";
		$files[]="modules/Home/quicksearchQuery.php";
		$files[]="modules/Home/UnifiedSearchAdvanced.php";
		$files[]="modules/Home/AdditionalDetailsRetrieve.php";
		$files[]="modules/Home/PopupSugar.php";
		$files[]="modules/ProspectLists/field_arrays.php";
		$files[]="modules/ProspectLists/Save.php";
		$files[]="modules/ProspectLists/language/en_us.lang.php";
		$files[]="modules/ProspectLists/ProspectList.php";
		$files[]="modules/ProspectLists/index.php";
		$files[]="modules/ProspectLists/Delete.php";
		$files[]="modules/ProspectLists/Duplicate.php";
		$files[]="modules/ProspectLists/Menu.php";
		$files[]="modules/ProspectLists/ListView.php";
		$files[]="modules/ProspectLists/Forms.php";
		$files[]="modules/ProspectLists/vardefs.php";
		$files[]="modules/ProspectLists/SubPanelView.php";
		$files[]="modules/ProspectLists/metadata/subpaneldefs.php";
		$files[]="modules/ProspectLists/metadata/editviewdefs.php";
		$files[]="modules/ProspectLists/metadata/popupdefs.php";
		$files[]="modules/ProspectLists/metadata/detailviewdefs.php";
		$files[]="modules/ProspectLists/metadata/subpanels/default.php";
		$files[]="modules/ProspectLists/metadata/SearchFields.php";
		$files[]="modules/ProspectLists/ProspectListFormBase.php";
		$files[]="modules/TableDictionary.php";
		$files[]="modules/Help/language/en_us.lang.php";
		$files[]="modules/Help/index.php";
		$files[]="modules/Help/Menu.php";
		$files[]="modules/Help/Forms.php";
		$files[]="modules/Contacts/ContactFormBase.php";
		$files[]="modules/Contacts/Contact.php";
		$files[]="modules/Contacts/views/view.contactaddresspopup.php";
		$files[]="modules/Contacts/views/view.validportalusername.php";
		$files[]="modules/Contacts/views/view.retrieveemail.php";
		$files[]="modules/Contacts/views/view.edit.php";
		$files[]="modules/Contacts/views/view.closecontactaddresspopup.php";
		$files[]="modules/Contacts/views/view.detail.php";
		$files[]="modules/Contacts/views/view.mailmergepopup.php";
		$files[]="modules/Contacts/field_arrays.php";
		$files[]="modules/Contacts/ContactOpportunityRelationship.php";
		$files[]="modules/Contacts/Save.php";
		$files[]="modules/Contacts/language/en_us.lang.php";
		$files[]="modules/Contacts/controller.php";
		$files[]="modules/Contacts/BusinessCard.php";
		$files[]="modules/Contacts/AcceptDecline.php";
		$files[]="modules/Contacts/ContactsQuickCreate.php";
		$files[]="modules/Contacts/ContactOpportunityRelationshipEdit.php";
		$files[]="modules/Contacts/Menu.php";
		$files[]="modules/Contacts/contactSeedData.php";
		$files[]="modules/Contacts/Forms.php";
		$files[]="modules/Contacts/SugarFeeds/ContactFeed.php";
		$files[]="modules/Contacts/vardefs.php";
		$files[]="modules/Contacts/ShowDuplicates.php";
		$files[]="modules/Contacts/ImportVCard.php";
		$files[]="modules/Contacts/contactSeedData_jp.php";
		$files[]="modules/Contacts/metadata/subpaneldefs.php";
		$files[]="modules/Contacts/metadata/popupdefsEmail.php";
		$files[]="modules/Contacts/metadata/metafiles.php";
		$files[]="modules/Contacts/metadata/editviewdefs.php";
		$files[]="modules/Contacts/metadata/popupdefs.php";
		$files[]="modules/Contacts/metadata/quickcreatedefs.php";
		$files[]="modules/Contacts/metadata/detailviewdefs.php";
		$files[]="modules/Contacts/metadata/subpanels/ForEmails.php";
		$files[]="modules/Contacts/metadata/subpanels/ForContacts.php";
		$files[]="modules/Contacts/metadata/subpanels/ForOpportunities.php";
		$files[]="modules/Contacts/metadata/subpanels/ForCases.php";
		$files[]="modules/Contacts/metadata/subpanels/ForProject.php";
		$files[]="modules/Contacts/metadata/subpanels/ForAccounts.php";
		$files[]="modules/Contacts/metadata/subpanels/default.php";
		$files[]="modules/Contacts/metadata/subpanels/ForMeetings.php";
		$files[]="modules/Contacts/metadata/subpanels/ForCalls.php";
		$files[]="modules/Contacts/metadata/studio.php";
		$files[]="modules/Contacts/metadata/listviewdefs.php";
		$files[]="modules/Contacts/metadata/SearchFields.php";
		$files[]="modules/Contacts/metadata/additionalDetails.php";
		$files[]="modules/Contacts/metadata/sidecreateviewdefs.php";
		$files[]="modules/Contacts/metadata/searchdefs.php";
		$files[]="modules/Contacts/SaveContactOpportunityRelationship.php";
		$files[]="modules/Contacts/Dashlets/MyContactsDashlet/MyContactsDashlet.meta.php";
		$files[]="modules/Contacts/Dashlets/MyContactsDashlet/MyContactsDashlet.data.php";
		$files[]="modules/Contacts/Dashlets/MyContactsDashlet/MyContactsDashlet.php";
		$files[]="modules/Contacts/Popup_picker.php";
		$files[]="modules/MailMerge/modules_array.php";
		$files[]="modules/MailMerge/Save.php";
		$files[]="modules/MailMerge/language/en_us.lang.php";
		$files[]="modules/MailMerge/get_doc.php";
		$files[]="modules/MailMerge/MailMerge.php";
		$files[]="modules/MailMerge/Step4.php";
		$files[]="modules/MailMerge/Merge.php";
		$files[]="modules/MailMerge/DetailView.php";
		$files[]="modules/MailMerge/index.php";
		$files[]="modules/MailMerge/Step1.php";
		$files[]="modules/MailMerge/Step2.php";
		$files[]="modules/MailMerge/Step3.php";
		$files[]="modules/MailMerge/Menu.php";
		$files[]="modules/MailMerge/Forms.php";
		$files[]="modules/MailMerge/Step5.php";
		$files[]="modules/MailMerge/EditView.php";
		$files[]="modules/Audit/Audit.php";
		$files[]="modules/Audit/language/en_us.lang.php";
		$files[]="modules/Audit/field_assoc.php";
		$files[]="modules/Audit/vardefs.php";
		$files[]="modules/Audit/Popup_picker.php";
		$files[]="modules/SugarFeed/AdminSettings.php";
		$files[]="modules/SugarFeed/language/en_us.lang.php";
		$files[]="modules/SugarFeed/SugarFeedFlush.php";
		$files[]="modules/SugarFeed/feedLogicBase.php";
		$files[]="modules/SugarFeed/Forms.php";
		$files[]="modules/SugarFeed/vardefs.php";
		$files[]="modules/SugarFeed/metadata/metafiles.php";
		$files[]="modules/SugarFeed/metadata/editviewdefs.php";
		$files[]="modules/SugarFeed/metadata/popupdefs.php";
		$files[]="modules/SugarFeed/metadata/dashletviewdefs.php";
		$files[]="modules/SugarFeed/metadata/detailviewdefs.php";
		$files[]="modules/SugarFeed/metadata/subpanels/default.php";
		$files[]="modules/SugarFeed/metadata/listviewdefs.php";
		$files[]="modules/SugarFeed/metadata/SearchFields.php";
		$files[]="modules/SugarFeed/metadata/sidecreateviewdefs.php";
		$files[]="modules/SugarFeed/metadata/searchdefs.php";
		$files[]="modules/SugarFeed/Dashlets/SugarFeedDashlet/SugarFeedDashlet.meta.php";
		$files[]="modules/SugarFeed/Dashlets/SugarFeedDashlet/SugarFeedDashlet.php";
		$files[]="modules/SugarFeed/linkHandlers/YouTube.php";
		$files[]="modules/SugarFeed/linkHandlers/Link.php";
		$files[]="modules/SugarFeed/linkHandlers/Image.php";
		$files[]="modules/SugarFeed/SugarFeed.php";
		$files[]="modules/EmailTemplates/field_arrays.php";
		$files[]="modules/EmailTemplates/Save.php";
		$files[]="modules/EmailTemplates/PopupDocumentsCampaignTemplate.php";
		$files[]="modules/EmailTemplates/language/en_us.lang.php";
		$files[]="modules/EmailTemplates/CheckDeletable.php";
		$files[]="modules/EmailTemplates/EmailTemplate.php";
		$files[]="modules/EmailTemplates/AttachFiles.php";
		$files[]="modules/EmailTemplates/DetailView.php";
		$files[]="modules/EmailTemplates/index.php";
		$files[]="modules/EmailTemplates/Delete.php";
		$files[]="modules/EmailTemplates/Menu.php";
		$files[]="modules/EmailTemplates/ListView.php";
		$files[]="modules/EmailTemplates/EmailTemplateFormBase.php";
		$files[]="modules/EmailTemplates/Forms.php";
		$files[]="modules/EmailTemplates/vardefs.php";
		$files[]="modules/EmailTemplates/metadata/SearchFields.php";
		$files[]="modules/EmailTemplates/EditView.php";
		$files[]="modules/Employees/views/view.edit.php";
		$files[]="modules/Employees/views/view.detail.php";
		$files[]="modules/Employees/WapMenu.php";
		$files[]="modules/Employees/field_arrays.php";
		$files[]="modules/Employees/Save.php";
		$files[]="modules/Employees/language/en_us.lang.php";
		$files[]="modules/Employees/controller.php";
		$files[]="modules/Employees/Employee.php";
		$files[]="modules/Employees/WapAuthenticate.php";
		$files[]="modules/Employees/index.php";
		$files[]="modules/Employees/Menu.php";
		$files[]="modules/Employees/ListView.php";
		$files[]="modules/Employees/Forms.php";
		$files[]="modules/Employees/vardefs.php";
		$files[]="modules/Employees/metadata/editviewdefs.php";
		$files[]="modules/Employees/metadata/detailviewdefs.php";
		$files[]="modules/Employees/metadata/listviewdefs.php";
		$files[]="modules/Employees/metadata/SearchFields.php";
		$files[]="modules/Employees/metadata/searchdefs.php";
		$files[]="modules/Employees/Error.php";
		$files[]="modules/Employees/Popup_picker.php";
		$files[]="modules/Employees/EmployeeStatus.php";
		$files[]="modules/InboundEmail/EditGroupFolder.php";
		$files[]="modules/InboundEmail/field_arrays.php";
		$files[]="modules/InboundEmail/Save.php";
		$files[]="modules/InboundEmail/language/en_us.lang.php";
		$files[]="modules/InboundEmail/Popup.php";
		$files[]="modules/InboundEmail/DetailView.php";
		$files[]="modules/InboundEmail/InboundEmailTest.php";
		$files[]="modules/InboundEmail/index.php";
		$files[]="modules/InboundEmail/Delete.php";
		$files[]="modules/InboundEmail/Menu.php";
		$files[]="modules/InboundEmail/ListView.php";
		$files[]="modules/InboundEmail/Forms.php";
		$files[]="modules/InboundEmail/vardefs.php";
		$files[]="modules/InboundEmail/ShowInboundFoldersList.php";
		$files[]="modules/InboundEmail/InboundEmail.php";
		$files[]="modules/InboundEmail/parseEncoding.php";
		$files[]="modules/InboundEmail/EditView.php";
		$files[]="modules/InboundEmail/SaveGroupFolder.php";
		$files[]="modules/Versions/field_arrays.php";
		$files[]="modules/Versions/language/en_us.lang.php";
		$files[]="modules/Versions/InstallDefaultVersions.php";
		$files[]="modules/Versions/CheckVersions.php";
		$files[]="modules/Versions/DefaultVersions.php";
		$files[]="modules/Versions/vardefs.php";
		$files[]="modules/Versions/ExpectedVersions.php";
		$files[]="modules/Versions/Version.php";
		$files[]="modules/Prospects/views/view.detail.php";
		$files[]="modules/Prospects/field_arrays.php";
		$files[]="modules/Prospects/ProspectFormBase.php";
		$files[]="modules/Prospects/Save.php";
		$files[]="modules/Prospects/language/en_us.lang.php";
		$files[]="modules/Prospects/Prospect.php";
		$files[]="modules/Prospects/Delete.php";
		$files[]="modules/Prospects/Menu.php";
		$files[]="modules/Prospects/Forms.php";
		$files[]="modules/Prospects/vardefs.php";
		$files[]="modules/Prospects/metadata/subpaneldefs.php";
		$files[]="modules/Prospects/metadata/editviewdefs.php";
		$files[]="modules/Prospects/metadata/popupdefs.php";
		$files[]="modules/Prospects/metadata/quickcreatedefs.php";
		$files[]="modules/Prospects/metadata/detailviewdefs.php";
		$files[]="modules/Prospects/metadata/subpanels/default.php";
		$files[]="modules/Prospects/metadata/studio.php";
		$files[]="modules/Prospects/metadata/listviewdefs.php";
		$files[]="modules/Prospects/metadata/SearchFields.php";
		$files[]="modules/Prospects/metadata/additionalDetails.php";
		$files[]="modules/Prospects/metadata/sidecreateviewdefs.php";
		$files[]="modules/Prospects/metadata/searchdefs.php";
		$files[]="modules/Prospects/Import.php";
		$files[]="modules/Roles/field_arrays.php";
		$files[]="modules/Roles/SaveUserRelationship.php";
		$files[]="modules/Roles/Save.php";
		$files[]="modules/Roles/language/en_us.lang.php";
		$files[]="modules/Roles/DetailView.php";
		$files[]="modules/Roles/index.php";
		$files[]="modules/Roles/Delete.php";
		$files[]="modules/Roles/Menu.php";
		$files[]="modules/Roles/ListView.php";
		$files[]="modules/Roles/Forms.php";
		$files[]="modules/Roles/Role.php";
		$files[]="modules/Roles/vardefs.php";
		$files[]="modules/Roles/metadata/subpaneldefs.php";
		$files[]="modules/Roles/metadata/subpanels/default.php";
		$files[]="modules/Roles/DeleteUserRelationship.php";
		$files[]="modules/Roles/SubPanelViewUsers.php";
		$files[]="modules/Roles/EditView.php";
		$files[]="modules/Calls/views/view.edit.php";
		$files[]="modules/Calls/field_arrays.php";
		$files[]="modules/Calls/Save.php";
		$files[]="modules/Calls/language/en_us.lang.php";
		$files[]="modules/Calls/CallHelper.php";
		$files[]="modules/Calls/Menu.php";
		$files[]="modules/Calls/CallFormBase.php";
		$files[]="modules/Calls/Forms.php";
		$files[]="modules/Calls/CallsQuickCreate.php";
		$files[]="modules/Calls/vardefs.php";
		$files[]="modules/Calls/SubPanelViewInvitees.php";
		$files[]="modules/Calls/metadata/subpaneldefs.php";
		$files[]="modules/Calls/metadata/editviewdefs.php";
		$files[]="modules/Calls/metadata/detailviewdefs.php";
		$files[]="modules/Calls/metadata/subpanels/ForHistory.php";
		$files[]="modules/Calls/metadata/subpanels/ForActivities.php";
		$files[]="modules/Calls/metadata/subpanels/default.php";
		$files[]="modules/Calls/metadata/studio.php";
		$files[]="modules/Calls/metadata/listviewdefs.php";
		$files[]="modules/Calls/metadata/SearchFields.php";
		$files[]="modules/Calls/metadata/additionalDetails.php";
		$files[]="modules/Calls/metadata/sidecreateviewdefs.php";
		$files[]="modules/Calls/metadata/searchdefs.php";
		$files[]="modules/Calls/Dashlets/MyCallsDashlet/MyCallsDashlet.meta.php";
		$files[]="modules/Calls/Dashlets/MyCallsDashlet/MyCallsDashlet.php";
		$files[]="modules/Calls/Dashlets/MyCallsDashlet/MyCallsDashlet.data.php";
		$files[]="modules/Calls/Call.php";
		$files[]="modules/Connectors/views/view.sourceproperties.php";
		$files[]="modules/Connectors/views/view.modifydisplay.php";
		$files[]="modules/Connectors/views/view.displayproperties.php";
		$files[]="modules/Connectors/views/view.modifymapping.php";
		$files[]="modules/Connectors/views/view.mappingproperties.php";
		$files[]="modules/Connectors/views/view.modifyproperties.php";
		$files[]="modules/Connectors/views/view.connectorsettings.php";
		$files[]="modules/Connectors/language/en_us.lang.php";
		$files[]="modules/Connectors/ConnectorRecord.php";
		$files[]="modules/Connectors/controller.php";
		$files[]="modules/Connectors/Menu.php";
		$files[]="modules/Connectors/Forms.php";
		$files[]="modules/Connectors/connectors/sources/ext/rest/linkedin/mapping.php";
		$files[]="modules/Connectors/connectors/sources/ext/rest/linkedin/language/en_us.lang.php";
		$files[]="modules/Connectors/connectors/sources/ext/rest/linkedin/config.php";
		$files[]="modules/Connectors/connectors/sources/ext/rest/linkedin/vardefs.php";
		$files[]="modules/Connectors/connectors/sources/ext/rest/linkedin/linkedin.php";
		$files[]="modules/Connectors/connectors/formatters/ext/rest/linkedin/linkedin.php";
		$files[]="modules/Connectors/action_view_map.php";
		$files[]="modules/Leads/LeadsQuickCreate.php";
		$files[]="modules/Leads/field_arrays.php";
		$files[]="modules/Leads/Save.php";
		$files[]="modules/Leads/language/en_us.lang.php";
		$files[]="modules/Leads/controller.php";
		$files[]="modules/Leads/Lead.php";
		$files[]="modules/Leads/Menu.php";
		$files[]="modules/Leads/Forms.php";
		$files[]="modules/Leads/Capture.php";
		$files[]="modules/Leads/SugarFeeds/LeadFeed.php";
		$files[]="modules/Leads/LeadFormBase.php";
		$files[]="modules/Leads/vardefs.php";
		$files[]="modules/Leads/ImportVCard.php";
		$files[]="modules/Leads/SubPanelView.php";
		$files[]="modules/Leads/metadata/subpaneldefs.php";
		$files[]="modules/Leads/metadata/editviewdefs.php";
		$files[]="modules/Leads/metadata/popupdefs.php";
		$files[]="modules/Leads/metadata/quickcreatedefs.php";
		$files[]="modules/Leads/metadata/detailviewdefs.php";
		$files[]="modules/Leads/metadata/subpanels/ForEmails.php";
		$files[]="modules/Leads/metadata/subpanels/default.php";
		$files[]="modules/Leads/metadata/subpanels/ForMeetings.php";
		$files[]="modules/Leads/metadata/subpanels/ForCalls.php";
		$files[]="modules/Leads/metadata/studio.php";
		$files[]="modules/Leads/metadata/listviewdefs.php";
		$files[]="modules/Leads/metadata/SearchFields.php";
		$files[]="modules/Leads/metadata/additionalDetails.php";
		$files[]="modules/Leads/metadata/sidecreateviewdefs.php";
		$files[]="modules/Leads/metadata/searchdefs.php";
		$files[]="modules/Leads/Dashlets/MyLeadsDashlet/MyLeadsDashlet.data.php";
		$files[]="modules/Leads/Dashlets/MyLeadsDashlet/MyLeadsDashlet.php";
		$files[]="modules/Leads/Dashlets/MyLeadsDashlet/MyLeadsDashlet.meta.php";
		$files[]="modules/Leads/MyLeads.php";
		$files[]="modules/Leads/ConvertLead.php";
		$files[]="modules/ProjectTask/views/view.list.php";
		$files[]="modules/ProjectTask/field_arrays.php";
		$files[]="modules/ProjectTask/Save.php";
		$files[]="modules/ProjectTask/language/en_us.lang.php";
		$files[]="modules/ProjectTask/Popup.php";
		$files[]="modules/ProjectTask/ProjectTask.php";
		$files[]="modules/ProjectTask/ProjectTaskQuickCreate.php";
		$files[]="modules/ProjectTask/Delete.php";
		$files[]="modules/ProjectTask/Menu.php";
		$files[]="modules/ProjectTask/Forms.php";
		$files[]="modules/ProjectTask/vardefs.php";
		$files[]="modules/ProjectTask/SubPanelView.php";
		$files[]="modules/ProjectTask/metadata/subpaneldefs.php";
		$files[]="modules/ProjectTask/metadata/editviewdefs.php";
		$files[]="modules/ProjectTask/metadata/popupdefs.php";
		$files[]="modules/ProjectTask/metadata/acldefs.php";
		$files[]="modules/ProjectTask/metadata/detailviewdefs.php";
		$files[]="modules/ProjectTask/metadata/subpanels/default.php";
		$files[]="modules/ProjectTask/metadata/studio.php";
		$files[]="modules/ProjectTask/metadata/listviewdefs.php";
		$files[]="modules/ProjectTask/metadata/SearchFields.php";
		$files[]="modules/ProjectTask/metadata/additionalDetails.php";
		$files[]="modules/ProjectTask/metadata/searchdefs.php";
		$files[]="modules/ProjectTask/MyProjectTasks.php";
		$files[]="modules/ProjectTask/Dashlets/MyProjectTaskDashlet/MyProjectTaskDashlet.data.php";
		$files[]="modules/ProjectTask/Dashlets/MyProjectTaskDashlet/MyProjectTaskDashlet.php";
		$files[]="modules/ProjectTask/Dashlets/MyProjectTaskDashlet/MyProjectTaskDashlet.meta.php";
		$files[]="modules/Administration/ncc_config.php";
		$files[]="modules/Administration/DiagnosticDelete.php";
		$files[]="modules/Administration/CustomizeFields.php";
		$files[]="modules/Administration/Updater.php";
		$files[]="modules/Administration/clear_chart_cache.php";
		$files[]="modules/Administration/Save.php";
		$files[]="modules/Administration/language/en_us.lang.php";
		$files[]="modules/Administration/repairSelectModule.php";
		$files[]="modules/Administration/RepairXSS.php";
		$files[]="modules/Administration/RebuildFulltextIndices.php";
		$files[]="modules/Administration/RepairIE.php";
		$files[]="modules/Administration/DiagnosticRun.php";
		$files[]="modules/Administration/ConfigureTabs.php";
		$files[]="modules/Administration/RebuildSchedulers.php";
		$files[]="modules/Administration/RebuildConfig.php";
		$files[]="modules/Administration/repairUniSearch.php";
		$files[]="modules/Administration/RebuildDashlets.php";
		$files[]="modules/Administration/Async.php";
		$files[]="modules/Administration/updateTimezonePrefs.php";
		$files[]="modules/Administration/DstFix.php";
		$files[]="modules/Administration/updater_utils.php";
		$files[]="modules/Administration/ImportCustomFieldStructure.php";
		$files[]="modules/Administration/Upgrade.php";
		$files[]="modules/Administration/RepairFieldCasing.php";
		$files[]="modules/Administration/expandDatabase.php";
		$files[]="modules/Administration/RepairActivities.php";
		$files[]="modules/Administration/UpgradeWizard.php";
		$files[]="modules/Administration/RebuildAudit.php";
		$files[]="modules/Administration/Diagnostic.php";
		$files[]="modules/Administration/index.php";
		$files[]="modules/Administration/SaveTabs.php";
		$files[]="modules/Administration/DisplayWarnings.php";
		$files[]="modules/Administration/UpgradeFields.php";
		$files[]="modules/Administration/Development.php";
		$files[]="modules/Administration/RebuildJSLang.php";
		$files[]="modules/Administration/Menu.php";
		$files[]="modules/Administration/Backups.php";
		$files[]="modules/Administration/UpgradeWizard_prepare.php";
		$files[]="modules/Administration/Forms.php";
		$files[]="modules/Administration/RepairSeedUsers.php";
		$files[]="modules/Administration/repairDatabase.php";
		$files[]="modules/Administration/vardefs.php";
		$files[]="modules/Administration/UpgradeWizardCommon.php";
		$files[]="modules/Administration/RebuildRelationship.php";
		$files[]="modules/Administration/metadata/SearchFields.php";
		$files[]="modules/Administration/ExportCustomFieldStructure.php";
		$files[]="modules/Administration/Administration.php";
		$files[]="modules/Administration/CheckReports.php";
		$files[]="modules/Administration/RepairJSFile.php";
		$files[]="modules/Administration/RepairIndex.php";
		$files[]="modules/Administration/Locale.php";
		$files[]="modules/Administration/QuickRepairAndRebuild.php";
		$files[]="modules/Administration/UpgradeHistory.php";
		$files[]="modules/Administration/SupportPortal.php";
		$files[]="modules/Administration/Common.php";
		$files[]="modules/Administration/RebuildExtensions.php";
		$files[]="modules/Administration/UpgradeAccess.php";
		$files[]="modules/Administration/UpgradeWizard_commit.php";
		$files[]="modules/Administration/callJSRepair.php";
		$files[]="modules/Administration/DiagnosticDownload.php";
		$files[]="modules/DocumentRevisions/field_arrays.php";
		$files[]="modules/DocumentRevisions/Save.php";
		$files[]="modules/DocumentRevisions/language/en_us.lang.php";
		$files[]="modules/DocumentRevisions/DetailView.php";
		$files[]="modules/DocumentRevisions/subpanels/default.php";
		$files[]="modules/DocumentRevisions/Menu.php";
		$files[]="modules/DocumentRevisions/Forms.php";
		$files[]="modules/DocumentRevisions/vardefs.php";
		$files[]="modules/DocumentRevisions/metadata/subpanels/default.php";
		$files[]="modules/DocumentRevisions/DocumentRevision.php";
		$files[]="modules/DocumentRevisions/EditView.php";
		$files[]="modules/Accounts/views/view.detail.php";
		$files[]="modules/Accounts/field_arrays.php";
		$files[]="modules/Accounts/Save.php";
		$files[]="modules/Accounts/language/en_us.lang.php";
		$files[]="modules/Accounts/AccountFormBase.php";
		$files[]="modules/Accounts/Menu.php";
		$files[]="modules/Accounts/Forms.php";
		$files[]="modules/Accounts/vardefs.php";
		$files[]="modules/Accounts/ShowDuplicates.php";
		$files[]="modules/Accounts/metadata/subpaneldefs.php";
		$files[]="modules/Accounts/metadata/metafiles.php";
		$files[]="modules/Accounts/metadata/editviewdefs.php";
		$files[]="modules/Accounts/metadata/popupdefs.php";
		$files[]="modules/Accounts/metadata/quickcreatedefs.php";
		$files[]="modules/Accounts/metadata/acldefs.php";
		$files[]="modules/Accounts/metadata/detailviewdefs.php";
		$files[]="modules/Accounts/metadata/subpanels/ForEmails.php";
		$files[]="modules/Accounts/metadata/subpanels/default.php";
		$files[]="modules/Accounts/metadata/studio.php";
		$files[]="modules/Accounts/metadata/listviewdefs.php";
		$files[]="modules/Accounts/metadata/fieldGroups.php";
		$files[]="modules/Accounts/metadata/SearchFields.php";
		$files[]="modules/Accounts/metadata/additionalDetails.php";
		$files[]="modules/Accounts/metadata/sidecreateviewdefs.php";
		$files[]="modules/Accounts/metadata/searchdefs.php";
		$files[]="modules/Accounts/AccountsQuickCreate.php";
		$files[]="modules/Accounts/Dashlets/MyAccountsDashlet/MyAccountsDashlet.meta.php";
		$files[]="modules/Accounts/Dashlets/MyAccountsDashlet/MyAccountsDashlet.data.php";
		$files[]="modules/Accounts/Dashlets/MyAccountsDashlet/MyAccountsDashlet.php";
		$files[]="modules/Accounts/Account.php";
		$files[]="modules/MergeRecords/language/en_us.lang.php";
		$files[]="modules/MergeRecords/controller.php";
		$files[]="modules/MergeRecords/MergeRecord.php";
		$files[]="modules/MergeRecords/index.php";
		$files[]="modules/MergeRecords/Step1.php";
		$files[]="modules/MergeRecords/Step2.php";
		$files[]="modules/MergeRecords/Step3.php";
		$files[]="modules/MergeRecords/Menu.php";
		$files[]="modules/MergeRecords/Forms.php";
		$files[]="modules/MergeRecords/vardefs.php";
		$files[]="modules/MergeRecords/SaveMerge.php";
		$files[]="modules/ACL/Save.php";
		$files[]="modules/ACL/language/en_us.lang.php";
		$files[]="modules/ACL/remove_actions.php";
		$files[]="modules/ACL/ACLController.php";
		$files[]="modules/ACL/ACLJSController.php";
		$files[]="modules/ACL/install_actions.php";
		$files[]="modules/ACL/Menu.php";
		$files[]="modules/ACL/Forms.php";
		$files[]="modules/ACL/vardefs.php";
		$files[]="modules/ACL/metadata/subpaneldefs.php";
		$files[]="modules/ACL/List.php";
		$files[]="modules/Dashboard/language/en_us.lang.php";
		$files[]="modules/Dashboard/Dashboard.php";
		$files[]="modules/Dashboard/index.php";
		$files[]="modules/Dashboard/DynamicAction.php";
		$files[]="modules/Dashboard/Menu.php";
		$files[]="modules/Dashboard/Forms.php";
		$files[]="modules/Dashboard/vardefs.php";
		$files[]="modules/Dashboard/dashlets.php";
		$files[]="modules/Dashboard/EditDashboard.php";
		$files[]="modules/Activities/views/view.list.php";
		$files[]="modules/Activities/OpenListView.php";
		$files[]="modules/Activities/language/en_us.lang.php";
		$files[]="modules/Activities/SetAcceptStatus.php";
		$files[]="modules/Activities/config.php";
		$files[]="modules/Activities/Menu.php";
		$files[]="modules/Activities/Forms.php";
		$files[]="modules/Activities/SubPanelView.php";
		$files[]="modules/Activities/metadata/subpaneldefs.php";
		$files[]="modules/Activities/Popup_picker.php";
		$files[]="modules/Schedulers/SchedulerDaemon.php";
		$files[]="modules/Schedulers/field_arrays.php";
		$files[]="modules/Schedulers/Save.php";
		$files[]="modules/Schedulers/language/en_us.lang.php";
		$files[]="modules/Schedulers/_AddJobsHere.php";
		$files[]="modules/Schedulers/DetailView.php";
		$files[]="modules/Schedulers/index.php";
		$files[]="modules/Schedulers/Delete.php";
		$files[]="modules/Schedulers/Scheduler.php";
		$files[]="modules/Schedulers/Menu.php";
		$files[]="modules/Schedulers/ListView.php";
		$files[]="modules/Schedulers/Forms.php";
		$files[]="modules/Schedulers/vardefs.php";
		$files[]="modules/Schedulers/Scheduled.php";
		$files[]="modules/Schedulers/metadata/subpaneldefs.php";
		$files[]="modules/Schedulers/metadata/subpanels/default.php";
		$files[]="modules/Schedulers/JobThread.php";
		$files[]="modules/Schedulers/EditView.php";
		$files[]="modules/Schedulers/DeleteScheduled.php";
		$files[]="modules/Trackers/Metric.php";
		$files[]="modules/Trackers/language/en_us.lang.php";
		$files[]="modules/Trackers/populateSeedData.php";
		$files[]="modules/Trackers/store/SugarLogStore.php";
		$files[]="modules/Trackers/store/TrackerSessionsDatabaseStore.php";
		$files[]="modules/Trackers/store/Store.php";
		$files[]="modules/Trackers/store/DatabaseStore.php";
		$files[]="modules/Trackers/store/TrackerQueriesDatabaseStore.php";
		$files[]="modules/Trackers/monitor/tracker_monitor.php";
		$files[]="modules/Trackers/monitor/BlankMonitor.php";
		$files[]="modules/Trackers/monitor/Monitor.php";
		$files[]="modules/Trackers/Tracker.php";
		$files[]="modules/Trackers/Trackable.php";
		$files[]="modules/Trackers/config.php";
		$files[]="modules/Trackers/Forms.php";
		$files[]="modules/Trackers/vardefs.php";
		$files[]="modules/Trackers/TrackerManager.php";
		$files[]="modules/Trackers/TrackerSettings.php";
		$files[]="modules/Trackers/BreadCrumbStack.php";
		$files[]="index.php";
		$files[]="config.php";
		$files[]="image.php";
		$files[]="json_server.php";
		$files[]="sugar_version.php";
		$files[]="campaign_tracker.php";
		$files[]="vcal_server.php";
		$files[]="json.php";
		$files[]="jssource/minify.php";
		$files[]="jssource/jsmin.php";
		$files[]="jssource/JSGroupings.php";
		$files[]="pdf.php";
		$files[]="emailmandelivery.php";
		$files[]="log4php/LoggerManager.php";
		$files[]="leadCapture.php";
		$files[]="XTemplate/ex4.php";
		$files[]="XTemplate/ex1.php";
		$files[]="XTemplate/ex5.php";
		$files[]="XTemplate/xtpl.php";
		$files[]="XTemplate/ex2.php";
		$files[]="XTemplate/ex6.php";
		$files[]="XTemplate/ex7.php";
		$files[]="XTemplate/ex3.php";
		$files[]="XTemplate/debug.php";
		$files[]="SugarSecurity.php";
		$files[]="metadata/custom_fieldsMetaData.php";
		$files[]="metadata/users_signaturesMetaData.php";
		$files[]="metadata/acl_roles_actionsMetaData.php";
		$files[]="metadata/queues_queueMetaData.php";
		$files[]="metadata/projects_accountsMetaData.php";
		$files[]="metadata/project_bugsMetaData.php";
		$files[]="metadata/cases_bugsMetaData.php";
		$files[]="metadata/opportunities_contactsMetaData.php";
		$files[]="metadata/contacts_casesMetaData.php";
		$files[]="metadata/calls_leadsMetaData.php";
		$files[]="metadata/outboundEmailMetaData.php";
		$files[]="metadata/email_addressesMetaData.php";
		$files[]="metadata/email_cacheMetaData.php";
		$files[]="metadata/fields_meta_dataMetaData.php";
		$files[]="metadata/contacts_bugsMetaData.php";
		$files[]="metadata/inboundEmail_autoreplyMetaData.php";
		$files[]="metadata/emails_beansMetaData.php";
		$files[]="metadata/calls_contactsMetaData.php";
		$files[]="metadata/accounts_casesMetaData.php";
		$files[]="metadata/kbdocuments_views_ratingsMetaData.php";
		$files[]="metadata/schedulers_timesMetaData.php";
		$files[]="metadata/accounts_contactsMetaData.php";
		$files[]="metadata/addressBookMetaData.php";
		$files[]="metadata/prospect_lists_prospectsMetaData.php";
		$files[]="metadata/email_marketing_prospect_listsMetaData.php";
		$files[]="metadata/meetings_usersMetaData.php";
		$files[]="metadata/configMetaData.php";
		$files[]="metadata/projects_opportunitiesMetaData.php";
		$files[]="metadata/accounts_opportunitiesMetaData.php";
		$files[]="metadata/product_bundle_noteMetaData.php";
		$files[]="metadata/queues_beansMetaData.php";
		$files[]="metadata/prospect_list_campaignsMetaData.php";
		$files[]="metadata/meetings_contactsMetaData.php";
		$files[]="metadata/audit_templateMetaData.php";
		$files[]="metadata/projects_contactsMetaData.php";
		$files[]="metadata/usersMetaData.php";
		$files[]="metadata/project_relationMetaData.php";
		$files[]="metadata/users_last_importMetaData.php";
		$files[]="metadata/project_task_project_tasksMetaData.php";
		$files[]="metadata/projects_quotesMetaData.php";
		$files[]="metadata/user_feedsMetaData.php";
		$files[]="metadata/import_mapsMetaData.php";
		$files[]="metadata/contacts_usersMetaData.php";
		$files[]="metadata/inboundEmail_cacheTimestampMetaData.php";
		$files[]="metadata/linked_documentsMetaData.php";
		$files[]="metadata/project_productsMetaData.php";
		$files[]="metadata/roles_usersMetaData.php";
		$files[]="metadata/accounts_bugsMetaData.php";
		$files[]="metadata/roles_modulesMetaData.php";
		$files[]="metadata/acl_roles_usersMetaData.php";
		$files[]="metadata/meetings_leadsMetaData.php";
		$files[]="metadata/calls_usersMetaData.php";
		$files[]="metadata/project_casesMetaData.php";
		$files[]="metadata/foldersMetaData.php";
		$files[]="install.php";
		$files[]="dictionary.php";
		$files[]="download.php";
		$files[]="install/demoData.en_us.php";
		$files[]="install/seed_data/basicSeedData.php";
		$files[]="install/siteConfig_a.php";
		$files[]="install/installSystemCheck.php";
		$files[]="install/UserDemoData.php";
		$files[]="install/language/fr_fr.lang.php";
		$files[]="install/language/ja.lang.php";
		$files[]="install/language/pt_br.lang.php";
		$files[]="install/language/en_us.lang.php";
		$files[]="install/language/es_es.lang.php";
		$files[]="install/language/zh_cn.lang.php";
		$files[]="install/language/ge_ge.lang.php";
		$files[]="install/demoData.zh_cn.php";
		$files[]="install/localization.php";
		$files[]="install/populateSeedData.php";
		$files[]="install/welcome.php";
		$files[]="install/installType.php";
		$files[]="install/register.php";
		$files[]="install/download_patches.php";
		$files[]="install/checkDBSettings.php";
		$files[]="install/licensePrint.php";
		$files[]="install/systemOptions.php";
		$files[]="install/installHelp.php";
		$files[]="install/license.php";
		$files[]="install/siteConfig_b.php";
		$files[]="install/installDisabled.php";
		$files[]="install/demoData.ja_jp.php";
		$files[]="install/dbConfig_a.php";
		$files[]="install/confirmSettings.php";
		$files[]="install/install_utils.php";
		$files[]="install/performSetup.php";
		$files[]="install/UploadLangFileCheck.php";
		$files[]="install/TeamDemoData.php";
		$files[]="install/install_defaults.php";
		$files[]="install/data/disc_client.php";
		$files[]="install/download_modules.php";
		$files[]="data/SugarBean.php";
		$files[]="data/Tracker.php";
		$files[]="data/Link.php";
		$files[]="campaign_trackerv2.php";
		$files[]="metagen.php";
		$files[]="soap/SoapData.php";
		$files[]="soap/SoapSugarUsers.php";
		$files[]="soap/SoapStudio.php";
		$files[]="soap/SoapPortalUsers.php";
		$files[]="soap/SoapPortalHelper.php";
		$files[]="soap/SoapRelationshipHelper.php";
		$files[]="soap/SoapError.php";
		$files[]="soap/SoapDeprecated.php";
		$files[]="soap/SoapTypes.php";
		$files[]="soap/SoapHelperFunctions.php";
		$files[]="soap/SoapErrorDefinitions.php";
		$ff=array();
		while (list ($index, $file) = each ($files) ){
			if(!is_file("$root/$file")){
				$ff[]=$file;
			}
		}
		
		return $ff;
		
	}
	

	
	
}






?>