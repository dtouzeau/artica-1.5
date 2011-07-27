<?php

class group_office{
	var $groupware;
	var $ou;
	var $www_dir;
	var $ServerPort;	
	var $servername;
	var $database;
	var $uid;
	var $rebuildb=false;
	function group_office($servername){
		if($servername<>null){
			$this->servername=$servername;
			
			$this->load();
			
		}
		
	}
	

	private function Load(){
			$sql="SELECT * from freeweb WHERE servername='$this->servername'";
			$q=new mysql();
			$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
			$this->groupware=$ligne["groupware"];
			$this->servername=$ligne["servername"];
			$this->ou=$ligne["ou"];
			$this->www_dir=$ligne["www_dir"];
			$this->ServerPort=$ligne["ServerPort"];
			if($this->www_dir==null){$this->www_dir="/var/www/$this->servername";}
			$this->database="groupOffice_".md5(strtolower(trim($this->servername)));
			if($ligne["mysql_database"]<>null){$this->database=$ligne["mysql_database"];}else{
				$sql="UPDATE freeweb SET mysql_database='{$this->database} WHERE servername='$this->servername'";
				$q->QUERY_SQL($sql,"artica_backup");
			}
			$this->uid=$ligne["uid"];
			
	}	
		
		
	public function writeconfigfile(){
		$unix=new unix();
		$q=new mysql();
		$firstinstall=false;
		if($this->rebuildb){$q->DELETE_DATABASE($this->database);}
		if($GLOBALS["REINSTALL"]){$q->DELETE_DATABASE($this->database);}
		
		if(!$q->DATABASE_EXISTS($this->database)){
			echo "Starting......: Apache \"$this->servername\" create database $this->database\n";
			$q->CREATE_DATABASE($this->database);
			
		}else{
			echo "Starting......: Apache \"$this->servername\" create $this->database OK\n";
		}
		
		if(!$q->DATABASE_EXISTS($this->database)){
			echo "Starting......: Apache \"$this->servername\" create database $this->database FAILED\n";
		}
		
		if(!$this->testtables()){
			$mysql=$unix->find_program("mysql");
			if(is_file("$this->www_dir/install/sql/groupoffice.sql")){
				$cmd="$mysql -u $q->mysql_admin -p\"$q->mysql_password\" --batch --database=$this->database < $this->www_dir/install/sql/groupoffice.sql";
				echo "Starting......: Apache \"$this->servername\" Creating tables....\n";
				echo "Starting......: Apache $cmd\n";
				
				system($cmd);
				if(!$this->testtables()){
					echo "Starting......: Apache \"$this->servername\" Creating tables FAILED\n";
					$firstinstall=true;
				}
			}else{
				echo "Starting......: Apache \"$this->servername\" $this->www_dir/install/sql/groupoffice.sql no such file\n";
			}
		}else{
			echo "Starting......: Apache \"$this->servername\" tables OK\n";
		}
		
		
		$gpoffice[]="<?php";
		$gpoffice[]="\$config['enabled']=true;";
		$gpoffice[]="\$config['id']=\"groupoffice\";";
		$gpoffice[]="\$config['debug']=false;";
		$gpoffice[]="\$config['debug_log']=false;";
		$gpoffice[]="\$config['info_log']=\"/home/$this->servername/log/info.log\";";
		$gpoffice[]="\$config['debug_display_errors']=true;";
		$gpoffice[]="\$config['log']=false;";
		$gpoffice[]="\$config['language']=\"en\";";
		$gpoffice[]="\$config['default_country']=\"NL\";";
		$gpoffice[]="\$config['default_timezone']=\"Europe/Amsterdam\";";
		$gpoffice[]="\$config['default_currency']=\"€\";";
		$gpoffice[]="\$config['default_date_format']=\"dmY\";";
		$gpoffice[]="\$config['default_date_separator']=\"-\";";
		$gpoffice[]="\$config['default_time_format']=\"G:i\";";
		$gpoffice[]="\$config['default_sort_name']=\"last_name\";";
		$gpoffice[]="\$config['default_first_weekday']=\"1\";";
		$gpoffice[]="\$config['default_decimal_separator']=\",\";";
		$gpoffice[]="\$config['default_thousands_separator']=\".\";";
		$gpoffice[]="\$config['theme']=\"Default\";";
		$gpoffice[]="\$config['allow_themes']=true;";
		$gpoffice[]="\$config['allow_password_change']=true;";
		$gpoffice[]="\$config['allow_profile_edit']=true;";
		$gpoffice[]="\$config['allow_registration']=false;";
		$gpoffice[]="\$config['registration_fields']=\"title_initials,sex,birthday,address,home_phone,fax,cellular,company,department,function,work_address,work_phone,work_fax,homepage\";";
		$gpoffice[]="\$config['required_registration_fields']=\"company,address\";";
		$gpoffice[]="\$config['allow_duplicate_email']=false;";
		$gpoffice[]="\$config['auto_activate_accounts']=false;";
		$gpoffice[]="\$config['notify_admin_of_registration']=true;";
		$gpoffice[]="\$config['register_modules_read']=\"summary,email,calendar,tasks,addressbook,files,notes,emailportlet,log,dav,tools,settings,groups,links,blacklist,mailings,bookmarks,sieve,comments,users,search,modules\";";
		$gpoffice[]="\$config['register_modules_write']=\"summary,email,calendar,tasks,addressbook,files,notes,emailportlet,log,dav,tools,settings,groups,links,blacklist,mailings,bookmarks,sieve,comments,users,search,modules\";";
		$gpoffice[]="\$config['allowed_modules']=\"\";";
		$gpoffice[]="\$config['register_user_groups']=\"\";";
		$gpoffice[]="\$config['register_visible_user_groups']=\",\";";
		$gpoffice[]="\$config['host']=\"/\";";
		$gpoffice[]="\$config['force_login_url']=false;";
		$gpoffice[]="\$config['full_url']=\"http://$this->servername/\";";
		$gpoffice[]="\$config['title']=\"$this->servername\";";
		$gpoffice[]="\$config['webmaster_email']=\"webmaster@example.com\";";
		$gpoffice[]="\$config['help_link']=\"http://www.group-office.com/wiki/\";";
		$gpoffice[]="\$config['root_path']=\"$this->www_dir/\";";
		$gpoffice[]="\$config['tmpdir']=\"/tmp/\";";
		$gpoffice[]="\$config['max_users']=\"0\";";
		$gpoffice[]="\$config['quota']=\"0\";";
		$gpoffice[]="\$config['db_type']=\"mysql\";";
		$gpoffice[]="\$config['db_host']=\"$q->mysql_server\";";
		$gpoffice[]="\$config['db_name']=\"$this->database\";";
		$gpoffice[]="\$config['db_user']=\"$q->mysql_admin\";";
		$gpoffice[]="\$config['db_pass']=\"$q->mysql_password\";";
		$gpoffice[]="\$config['db_port']=\"$q->mysql_port\";";
		$gpoffice[]="\$config['db_socket']=\"\";";
		$gpoffice[]="\$config['db_auto_increment_increment']=\"1\";";
		$gpoffice[]="\$config['db_auto_increment_offset']=\"1\";";
		$gpoffice[]="\$config['file_storage_path']=\"/home/$this->servername/\";";
		$gpoffice[]="\$config['max_file_size']=\"10000000\";";
		$gpoffice[]="\$config['smtp_server']=\"127.0.0.1\";";
		$gpoffice[]="\$config['smtp_port']=\"25\";";
		$gpoffice[]="\$config['smtp_username']=\"\";";
		$gpoffice[]="\$config['smtp_password']=\"\";";
		$gpoffice[]="\$config['smtp_encryption']=\"\";";
		$gpoffice[]="\$config['smtp_local_domain']=\"\";";
		$gpoffice[]="\$config['restrict_smtp_hosts']=\"\";";
		$gpoffice[]="\$config['max_attachment_size']=\"10000000\";";
		$gpoffice[]="\$config['cmd_zip']=\"/usr/bin/zip\";";
		$gpoffice[]="\$config['cmd_unzip']=\"/usr/bin/unzip\";";
		$gpoffice[]="\$config['cmd_tar']=\"/bin/tar\";";
		$gpoffice[]="\$config['cmd_chpasswd']=\"/usr/sbin/chpasswd\";";
		$gpoffice[]="\$config['cmd_sudo']=\"/usr/bin/sudo\";";
		$gpoffice[]="\$config['cmd_xml2wbxml']=\"/usr/bin/xml2wbxml\";";
		$gpoffice[]="\$config['cmd_wbxml2xml']=\"/usr/bin/wbxml2xml\";";
		$gpoffice[]="\$config['cmd_tnef']=\"/usr/bin/tnef\";";
		$gpoffice[]="\$config['cmd_php']=\"php\";";
		$gpoffice[]="\$config['phpMyAdminUrl']=\"\";";
		$gpoffice[]="\$config['allow_unsafe_scripts']=\"\";";
		$gpoffice[]="\$config['default_password_length']=\"6\";";
		$gpoffice[]="\$config['session_inactivity_timeout']=\"0\";";
		$gpoffice[]="\$config['callto_template']=\"callto:{phone}\";";
		$gpoffice[]="\$config['disable_flash_upload']=false;";
		$gpoffice[]="\$config['disable_security_token_check']=false;";
		$gpoffice[]="\$config['nav_page_size']=\"50\";";
		
		$ldap=new clladp();
		$gpoffice[]="\$config['ldap_host']='$ldap->ldap_host';";
		$gpoffice[]="\$config['ldap_port']='$ldap->ldap_port';";
		$gpoffice[]="\$config['ldap_user']='cn=$ldap->ldap_admin,$ldap->suffix';";
		$gpoffice[]="\$config['ldap_pass']='$ldap->ldap_password';";
		if($this->uid<>null){
			$ct=new user($this->uid);
			$dn="ou=$ct->ou,dc=organizations,$ldap->suffix";
			$dnusers="ou=users,$dn";
			$dngroups="ou=groups,$dn";
		}else{
			$dn="dc=organizations,$ldap->suffix";
			$dnusers="$dn";
			$dngroups="$dn";			
		}
		$gpoffice[]="\$config['ldap_basedn']='$dn';";
		$gpoffice[]="\$config['ldap_peopledn']='$dnusers';";
		$gpoffice[]="\$config['ldap_groupsdn']='$dngroups';";
		$gpoffice[]="\$config['ldap_tls']=false;";
		$gpoffice[]="\$config['ldap_auth_dont_update_profiles']=True;"; //set to true if you don't want ldap to overwrite the Group-Office user profile on each login
		$gpoffice[]="\$config['ldap_use_uid_with_email_domain']='';"; //leave empty to use the default mapping. Set to a domain name to use username@example.com as e-mail address.		
		
		$gpoffice[]="?>";		
		@file_put_contents("$this->www_dir/config.php", @implode("\n", $gpoffice));
		
		$mapping[]="<?php";
		$mapping[]="\$mapping=array(";
		$mapping[]="						'username'	=> 'uid',";
		$mapping[]="						'password'	=> 'userpassword',";
		$mapping[]="						'first_name'	=> 'givenname',";
		$mapping[]="						'middle_name'	=> 'middlename',";
		$mapping[]="						'last_name'	=> 'sn',";
		$mapping[]="						'initials'	=> 'initials',";
		$mapping[]="						'title'	=> 'title',";
		$mapping[]="						'sex'		=> 'gender',";
		$mapping[]="						'birthday'	=> 'birthday',";
		$mapping[]="						'email'	=> 'mail',";
		$mapping[]="						'company'	=> 'o',";
		$mapping[]="						'department'	=> 'ou',";
		$mapping[]="						'function'	=> 'businessrole',";
		$mapping[]="						'home_phone'	=> 'homephone',";
		$mapping[]="						'work_phone'	=> 'telephonenumber',";
		$mapping[]="						'fax'		=> 'homefacsimiletelephonenumber',";
		$mapping[]="						'cellular'	=> 'mobile',";
		$mapping[]="						'country'	=> 'homecountryname',";
		$mapping[]="						'state'	=> 'homestate',";
		$mapping[]="						'city'	=> 'homelocalityname',";
		$mapping[]="						'zip'		=> 'homepostalcode',";
		$mapping[]="						'address'	=> 'homepostaladdress',";
		$mapping[]="						'homepage'	=> 'homeurl',";
		$mapping[]="						'work_address'=> 'postaladdress',";
		$mapping[]="						'work_zip'	=> 'postalcode',";
		$mapping[]="						'work_country'=> 'c',";
		$mapping[]="						'work_state'	=> 'st',";
		$mapping[]="						'work_city'	=> 'l',";
		$mapping[]="						'work_fax'	=> 'facsimiletelephonenumber',";
		$mapping[]="						'currency'	=> 'gocurrency',";
		$mapping[]="						'max_rows_list'	=> 'gomaxrowslist',";
		$mapping[]="						'timezone'	=> 'gotimezone',";
		$mapping[]="						'start_module'=> 'gostartmodule',";
		$mapping[]="						'theme'	=> 'gotheme',";
		$mapping[]="						'language'	=> 'golanguage',";
		$mapping[]="			);";
		$mapping[]="";		
		@file_put_contents("$this->www_dir/ldapauth.config.php", @implode("\n", $mapping));
		if($firstinstall){$this->autoinstall();}
		
	}
	
	private function autoinstall(){
			global $GO_LANGUAGE, $lang, $GO_EVENTS;
			require("$this->www_dir/Group-Office.php");
			require_once("$this->www_dir/classes/filesystem.class.inc");
			require_once("$this->www_dir/install/gotest.php");
			include_once("$this->www_dir/classes/base/events.class.inc.php");
			require("$this->www_dir/language/languages.inc.php");
			require("$this->www_dir/install/sql/updates.inc.php");
			require_once("$this->www_dir/classes/base/users.class.inc.php");
			require_once("$this->www_dir/classes/base/groups.class.inc.php");
			require($GO_LANGUAGE->get_base_language_file('countries'));
			require_once("$this->www_dir/classes/base/theme.class.inc.php");
			
			$GO_THEME = new GO_THEME();			
			$db = new db();
			$GO_EVENTS = new GO_EVENTS();
			$GO_CONFIG->save_setting('version', count($updates));
			$GO_LANGUAGE->set_language($GO_CONFIG->language);
			
			$GO_USERS = new GO_USERS();
			$user['id'] = $GO_USERS->nextid("go_users");
			echo "Starting......: Apache \"$this->servername\" userid \"{$user['id']}\"\n";
			
			
			$GO_GROUPS = new GO_GROUPS();


			$GO_GROUPS->query("DELETE FROM go_db_sequence WHERE seq_name='groups'");
			$GO_GROUPS->query("DELETE FROM go_groups");
			$admin_group_id = $GO_GROUPS->add_group(1, $lang['common']['group_admins']);
			$everyone_group_id = $GO_GROUPS->add_group(1, $lang['common']['group_everyone']);
			$internal_group_id = $GO_GROUPS->add_group(1, $lang['common']['group_internal']);
			$GO_MODULES->load_modules();
			
			require_once("$this->www_dir/install/upgrade.php");
			$fs = new filesystem();
			$module_folders = $fs->get_folders($GO_CONFIG->root_path.'modules/');

				$available_modules=array();
				foreach($module_folders as $folder){
					if(!file_exists($folder['path'].'/install/noautoinstall')){
						echo "Starting......: Apache \"$this->servername\" checking module {$folder['name']}\n";
						$available_modules[]=$folder['name'];
					}
				}
				
				$priority_modules=array('summary','email','calendar','tasks','addressbook','files', 'notes', 'projects','ldapauth');

				for($i=0;$i<count($priority_modules);$i++){
					if(in_array($priority_modules[$i], $available_modules)){
						echo "Starting......: Apache \"$this->servername\" add module \"{$priority_modules[$i]}\" [".$i."/".count($priority_modules)."]\n";
						$GO_MODULES->add_module($priority_modules[$i]);
					}
				}
				
				for($i=0;$i<count($available_modules);$i++){
					if(!in_array($available_modules[$i], $priority_modules)){
						if($available_modules[$i]=="mailings"){continue;}
						/*
						if($available_modules[$i]=="blacklist"){continue;}
						if($available_modules[$i]=="search"){continue;}
						if($available_modules[$i]=="modules"){continue;}
						//if($available_modules[$i]=="users"){continue;}
						//if($available_modules[$i]=="comments"){continue;}
						//if($available_modules[$i]=="sieve"){continue;}
						//if($available_modules[$i]=="bookmarks"){continue;}
						//if($available_modules[$i]=="links"){continue;}
						*/
						echo "Starting......: Apache \"$this->servername\" add module \"{$available_modules[$i]}\" [".$i."/".count($available_modules)."]\n";
						try {
							$GO_MODULES->add_module($available_modules[$i]);	
						} catch (Exception $e) {
							echo "Starting......: Apache \"$this->servername\" failed adding \"{$available_modules[$i]}\" module\n";
						}
						
					}
				}
				
				$GO_MODULES->add_module('ldapauth');
				echo "Starting......: Apache \"$this->servername\" save_setting upgrade_mtime\n";
				
				$GO_CONFIG->save_setting('upgrade_mtime', $GO_CONFIG->mtime);
				
				if($uid<>null){
					$u=new user($uid);
					$password=$u->password;
					$mail=$u->mail;
				}else{
					$ldap=new clladp();
					$uid=$ldap->ldap_admin;
					$password=$ldap->ldap_password;
					$mail="root@localhost.local";
				}
				
				$GO_USERS->nextid('go_users');
				echo "Starting......: Apache \"$this->servername\" adding \"$uid\" language {$GO_LANGUAGE->language} user $mail\n";
				$user['id']=1;
				$user['language'] = $GO_LANGUAGE->language;
				$user['first_name']=$GO_CONFIG->product_name;
				$user['middle_name']='';
				$user['last_name']="en";
				$user['username'] = "$uid";
				$user['password'] ="$password";
				$user['email'] = "$mail";
				$user['sex'] = 'M';
				$user['enabled']='1';
				$user['country']=$GO_CONFIG->default_country;
				$user['work_country']=$GO_CONFIG->default_country;

				//$GO_USERS->debug=true;
				
				$GO_USERS->add_user($user,array(1,2,3),array($GO_CONFIG->group_everyone));
				
		
	}
	
	
	
	private function testtables(){
			$tables[]="go_acl";
			$tables[]="go_acl_items";
			$tables[]="go_address_format";
			$tables[]="go_cache";
			$tables[]="go_countries";
			$tables[]="go_db_sequence";
			$tables[]="go_groups";
			$tables[]="go_holidays";
			$tables[]="go_iso_address_format";
			$tables[]="go_link_descriptions";
			$tables[]="go_link_folders";
			$tables[]="go_log";
			$tables[]="go_mail_counter";
			$tables[]="go_modules";
			$tables[]="go_reminders";
			$tables[]="go_reminders_users";
			$tables[]="go_saved_search_queries";
			$tables[]="go_search_cache";
			$tables[]="go_search_sync";
			$tables[]="go_settings";
			$tables[]="go_state";
			$tables[]="go_users";
			$tables[]="go_users_groups";

			$q=new mysql();
			while (list ($index, $yable) = each ($tables) ){
				if(!$q->TABLE_EXISTS($yable, $this->database)){
					echo "Starting......: Apache \"$this->servername\" create $yable no such table\n";
					return false;
				}
			}
			
			return true;
			
	}
	
}