<?php


class install_groupwares{
	var $servername='';
	var $root='';
	
	
	function install_groupwares($hostname){
		$sock=new sockets();
		$this->EnablePostfixMultiInstance=$sock->GET_INFO("EnablePostfixMultiInstance");	
			
	}
	
	
private function ROUNDCUBE_SRC_FOLDER(){
	if(is_file('/usr/share/roundcube/index.php')){return '/usr/share/roundcube';}
	if(is_file('/usr/share/roundcubemail/index.php')){return '/usr/share/roundcubemail';}	
	
}
	
	
private function ROUNDCUBE_INSTALL(){
	$srcfolder=ROUNDCUBE_SRC_FOLDER();
	$sock=new sockets();
	$ldap=new clladp();
	
	echo "Starting......: Roundcube $this->servername\n"; 
	
	$GLOBALS["ADDLOG"]="/var/log/artica-postfix/$this->servername.log";	
	if($this->root==null){$this->events("Starting install roundcube Unable to stat root dir");return false;}
	if(!is_dir($srcfolder)){
		$this->events("Starting install roundcube Unable to stat SRC");
		return false;
	}
	$sql_file="$srcfolder/SQL/mysql.initial.sql";
	
	if(!is_file($sql_file)){
		$this->events("Starting install roundcube Unable to stat $srcfolder");
		return false;
	}
	

	$user=$hash["wwwmysqluser"][0];
	$mysql_password=$hash[strtolower("WWWMysqlPassword")][0];
			
	$server_database=str_replace(".","_",$this->servername);
	$server_database=str_replace("-","_",$server_database);	
	$this->events("Starting install roundcube sub-system mysql database $server_database...");	
	
	if($user==null){$this->events("Starting install roundcube Unable to stat Mysql username");return false;}
	if($mysql_password==null){$this->events("Starting install roundcube Unable to stat Mysql password");return false;}
	@mkdir($this->root,0755,true);
	
	$this->events("Starting install roundcube sub-system mysql database $server_database...");
	$q=new mysql();
	$q->CREATE_DATABASE($server_database);
	
	if(!$q->DATABASE_EXISTS($server_database)){
		$this->events("Starting install roundcube unable to create MYSQL Database");
		return false;
	}
	
	$this->events("Starting setting permissions on Database with user $user");
	echo "Starting......: Roundcube $this->servername set permissions on Database with user $user\n"; 
	$q->PRIVILEGES($user,$mysql_password,$server_database);
	
	
	$this->events("Starting install roundcube installing source code");
	echo "Starting......: Roundcube $this->servername installing source code\n"; 
	
	shell_exec("/bin/rm -rf $this->root/*");
	shell_exec("/bin/cp -rf $srcfolder/* $this->root/");
	if($q->mysql_password<>null){
		$password=" --password=$q->mysql_password ";
	}else{
		$this->events("Starting install roundcube installing tables datas with null password");
	}

	$files[]=$sql_file;
	$files[]="$srcfolder/SQL/mysql.update.sql";
	
	while (list ($num, $line) = each ($files) ){
		$this->events("Starting install roundcube installing tables $server_database/$num");
		$cmd="mysql --port=$q->mysql_port --skip-column-names --database=$server_database --silent --xml ";
		$cmd=$cmd." --user=$q->mysql_admin$password <$line";
		shell_exec($cmd);
		
		$this->events("Starting install roundcube installing datas $server_database/$num");
		$cmd="mysql --port=$q->mysql_port --skip-column-names --database=$server_database --silent --xml ";
		$cmd=$cmd." --user=$q->mysql_admin$password <$sql_datas";
		shell_exec($cmd);		
		

	}

	if(is_file("$this->root/plugins/subscriptions_option/subscriptions_option.php")){$subscriptions_option=1;}
	

	$q->checkRoundCubeTables($server_database);
	$conf[]="<?php";
	$conf[]="\$rcmail_config = array();";
	$conf[]="\$rcmail_config[\"db_dsnw\"] = \"mysql://$q->mysql_admin:$q->mysql_password@$q->mysql_server/$server_database\";";
	$conf[]="\$rcmail_config[\"db_dsnr\"] = \"\";";
	$conf[]="\$rcmail_config[\"db_max_length\"] = 512000;  // 500K";
	$conf[]="\$rcmail_config[\"db_persistent\"] = FALSE;";
	$conf[]="\$rcmail_config[\"db_table_users\"] = \"users\";";
	$conf[]="\$rcmail_config[\"db_table_identities\"] = \"identities\";";
	$conf[]="\$rcmail_config[\"db_table_contacts\"] = \"contacts\";";
	$conf[]="\$rcmail_config[\"db_table_session\"] = \"session\";";
	$conf[]="\$rcmail_config[\"db_table_cache\"] = \"cache\";";
	$conf[]="\$rcmail_config[\"db_table_messages\"] = \"messages\";";
	$conf[]="\$rcmail_config[\"db_sequence_users\"] = \"user_ids\";";
	$conf[]="\$rcmail_config[\"db_sequence_identities\"] = \"identity_ids\";";
	$conf[]="\$rcmail_config[\"db_sequence_contacts\"] = \"contact_ids\";";
	$conf[]="\$rcmail_config[\"db_sequence_cache\"] = \"cache_ids\";";
	$conf[]="\$rcmail_config[\"db_sequence_messages\"] = \"message_ids\";";
	$conf[]="?>";
	$this->events("Starting install roundcube saving $this->root/config/db.inc.php");
	echo "Starting......: Roundcube $this->servername db.inc.php OK\n";
	@file_put_contents("$this->root/config/db.inc.php",@implode("\n",$conf));	
	
	unset($conf);
	
	$wwwmultismtpsender=$hash["wwwmultismtpsender"][0];
	$WWWEnableAddressBook=$hash["wwwenableaddressbook"][0];
	$this->events("OU={$hash["OU"][0]} EnablePostfixMultiInstance=$this->EnablePostfixMultiInstance, SMTP=$wwwmultismtpsender");
	
	
	
	$conf[]="<?php";
	$conf[]="\$rcmail_config = array();";
	$conf[]="\$rcmail_config['debug_level'] =1;";
	$conf[]="\$rcmail_config['enable_caching'] = TRUE;";
	$conf[]="\$rcmail_config['message_cache_lifetime'] = '10d';";
	$conf[]="\$rcmail_config['auto_create_user'] = TRUE;";
	$conf[]="\$rcmail_config['default_host'] = '127.0.0.1';";
	$conf[]="\$rcmail_config['default_port'] = 143;";
	
	if($this->EnablePostfixMultiInstance==1){
		if(trim($wwwmultismtpsender)<>null){
		$conf[]="// SMTP server used for sending mails.";
		$conf[]="\$rcmail_config['smtp_server'] = '$wwwmultismtpsender';";
		$conf[]="\$rcmail_config['smtp_port'] = 25;"; 
		
	} }else{
		$conf[]="\$rcmail_config['smtp_server'] = '127.0.0.1';";
		$conf[]="\$rcmail_config['smtp_port'] = 25;"; 	
	}
	
	
	$conf[]="\$rcmail_config['smtp_user'] = '';";
	$conf[]="\$rcmail_config['smtp_pass'] = '';";	
	$conf[]="\$rcmail_config['smtp_auth_type'] = '';";
	$conf[]="\$rcmail_config['smtp_helo_host'] = '';";
	$conf[]="\$rcmail_config['smtp_log'] = TRUE;";
	$conf[]="\$rcmail_config['username_domain'] = '';";
	$conf[]="\$rcmail_config['mail_domain'] = '';";
	$conf[]="\$rcmail_config['virtuser_file'] = '';";
	$conf[]="\$rcmail_config['virtuser_query'] = '';";
	$conf[]="\$rcmail_config['list_cols'] = array('subject', 'from', 'date', 'size');";
	$conf[]="\$rcmail_config['skin_path'] = 'skins/default/';";
	$conf[]="\$rcmail_config['skin_include_php'] = FALSE;";
	$conf[]="#LOGS";
	$conf[]="\$rcmail_config['log_driver'] ='syslog';";
	$conf[]="\$rcmail_config['syslog_id'] = 'roundcube-$this->servername';";	
	$conf[]="\$rcmail_config['temp_dir'] = 'temp/';";
	$conf[]="\$rcmail_config['log_dir'] = 'logs/';";
	$conf[]="\$rcmail_config['session_lifetime'] = 10;";
	$conf[]="\$rcmail_config['ip_check'] = false;";
	$conf[]="\$rcmail_config['double_auth'] = false;";
	$conf[]="\$rcmail_config['des_key'] = 'NIbXC7RaFsZvQTV5NWBbQd9H';";
	$conf[]="\$rcmail_config['locale_string'] = 'us';";
	$conf[]="\$rcmail_config['date_short'] = 'D H:i';";
	$conf[]="\$rcmail_config['date_long'] = 'd.m.Y H:i';";
	$conf[]="\$rcmail_config['date_today'] = 'H:i';";
	$conf[]="\$rcmail_config['useragent'] = 'RoundCube Webmail/0.1-rc2';";
	$conf[]="\$rcmail_config['product_name'] = 'RoundCube Webmail for {$hash["OU"][0]}';";
	$conf[]="\$rcmail_config['imap_root'] = null;";
	$conf[]="\$rcmail_config['drafts_mbox'] = 'Drafts';";
	$conf[]="\$rcmail_config['junk_mbox'] = 'Junk';";
	$conf[]="\$rcmail_config['sent_mbox'] = 'Sent';";
	$conf[]="\$rcmail_config['trash_mbox'] = 'Trash';";
	$conf[]="\$rcmail_config['default_imap_folders'] = array('INBOX', 'Drafts', 'Sent', 'Junk', 'Trash');";
	$conf[]="\$rcmail_config['protect_default_folders'] = TRUE;";
	$conf[]="\$rcmail_config['skip_deleted'] = TRUE;";
	$conf[]="\$rcmail_config['read_when_deleted'] = TRUE;";
	$conf[]="\$rcmail_config['flag_for_deletion'] = TRUE;";
	$conf[]="\$rcmail_config['enable_spellcheck'] = TRUE;";
	$conf[]="\$rcmail_config['spellcheck_uri'] = '';";
	$conf[]="\$rcmail_config['spellcheck_languages'] = NULL;";
	$conf[]="\$rcmail_config['generic_message_footer'] = '';";
	$conf[]="\$rcmail_config['mail_header_delimiter'] = NULL;";
	$conf[]="";
	
	if($WWWEnableAddressBook==1){
		$conf[]="\$rcmail_config['ldap_public']['{$hash["OU"][0]}'] = array(";
		$conf[]="	'name'          => '{$hash["OU"][0]}',";
		$conf[]="	'hosts'         => array('$ldap->ldap_host'),";
		$conf[]="	'port'          => $ldap->ldap_port,";
		$conf[]="	'base_dn'       => 'ou={$hash["OU"][0]},dc=organizations,$ldap->suffix',";
		$conf[]="	'bind_dn'       => 'cn=$ldap->ldap_admin,$ldap->suffix',";
		$conf[]="	'bind_pass'     => '$ldap->ldap_password',";
		$conf[]="	'ldap_version'  => 3,       // using LDAPv3";
		$conf[]="	'search_fields' => array('mail', 'cn','uid','givenName','DisplayName'),  // fields to search in";
		$conf[]="	'name_field'    => 'cn',    // this field represents the contact's name";
		$conf[]="	'email_field'   => 'mail',  // this field represents the contact's e-mail";
		$conf[]="	'surname_field' => 'sn',    // this field represents the contact's last name";
		$conf[]="	'firstname_field' => 'gn',  // this field represents the contact's first name";
		$conf[]="	'scope'         => 'sub',   // search mode: sub|base|list";
		$conf[]="	'LDAP_Object_Classes' => array( 'person', 'inetOrgPerson', 'userAccount'),";
		$conf[]="	'filter'        => 'givenName=*',      // used for basic listing (if not empty) and will be &'d with search queries. ex: (status=act)";
		$conf[]="	'fuzzy_search'  => true);   // server allows wildcard search";
	}
	$conf[]="// enable composing html formatted messages (experimental)";
	$conf[]="\$rcmail_config['enable_htmleditor'] = TRUE;";
	$conf[]="\$rcmail_config['dont_override'] =array('index_sort','trash_mbox','sent_mbox','junk_mbox','drafts_mbox','subscriptions_option');";
	$conf[]="\$rcmail_config['javascript_config'] = array('read_when_deleted', 'flag_for_deletion');";
	$conf[]="\$rcmail_config['include_host_config'] = FALSE;";
	$conf[]="";
	$conf[]="";
	$conf[]="/***** these settings can be overwritten by user's preferences *****/";
	$conf[]="";
	$conf[]="// show up to X items in list view";
	$conf[]="\$rcmail_config['pagesize'] = 40;";
	$conf[]="";
	$conf[]="// use this timezone to display date/time";
	$conf[]="\$rcmail_config['timezone'] = intval(date('O'))/100 - date('I');";
	$conf[]="";
	$conf[]="// is daylight saving On?";
	$conf[]="\$rcmail_config['dst_active'] = (bool)date('I');";
	$conf[]="";
	$conf[]="// prefer displaying HTML messages";
	$conf[]="\$rcmail_config['prefer_html'] = TRUE;";
	$conf[]="";
	$conf[]="// show pretty dates as standard";
	$conf[]="\$rcmail_config['prettydate'] = TRUE;";
	$conf[]="";
	$conf[]="// default sort col";
	$conf[]="\$rcmail_config['message_sort_col'] = 'date';";
	$conf[]="";
	$conf[]="// default sort order";
	$conf[]="\$rcmail_config['message_sort_order'] = 'DESC';";
	$conf[]="";
	$conf[]="// save compose message every 300 seconds (5min)";
	$conf[]="\$rcmail_config['draft_autosave'] = 300;";
	$conf[]="";
	$conf[]="/***** PLUGINS for Roundcube V3 *****/";
	$conf[]="\$rcmail_config['plugins'] = array();";
	
	
	ROUNDCUBE_CONTEXTMENU($this->root);
	if(is_file("$this->root/plugins/contextmenu/contextmenu.php")){
		$conf[]="\$rcmail_config['plugins'][] = 'contextmenu';";
	}
	if($subscriptions_option==1){
		$conf[]="\$rcmail_config['plugins'][] = 'subscriptions_option';";
	}
	
	$NAB=new roundcube_globaladdressbook($this->servername);
	if($NAB->enabled==1){
		ROUNDCUBE_GLOBALADDRESSBOOK();
		if(is_file("$this->root/plugins/globaladdressbook/globaladdressbook.php")){
			echo "Starting......: Roundcube $this->servername Enable Global AddressBook \n";
			$conf[]="\$rcmail_config['plugins'][] = 'globaladdressbook';";
			$nab_conf=$NAB->BuildConfig();
			@file_put_contents("$this->root/plugins/globaladdressbook/config.inc.php",$nab_conf);
			shell_exec("/bin/chmod -R 770 $this->root/plugins/globaladdressbook");
			shell_exec("/bin/chmod 660 $this->root/plugins/globaladdressbook/*.php");
			chmod("$this->root/plugins/globaladdressbook/config.inc.php",755);
			
		}
	}
	$roundcube_class=new roundcube();
	
	if(!is_file("$this->root/plugins/msglistcols/msglistcols.php")){$roundcube_class->plugin_install($this->root,"msglistcols");}
	if(!is_file("$this->root/plugins/sticky_notes/sticky_notes.php")){$roundcube_class->plugin_install($this->root,"sticky_notes");}
	if(!is_file("$this->root/plugins/jqueryui/jqueryui.php")){$roundcube_class->plugin_install($this->root,"jqueryui");}
	if(!is_file("$this->root/plugins/dkimstatus/dkimstatus.php")){$roundcube_class->plugin_install($this->root,"dkimstatus");}
	if(!is_file("$this->root/plugins/fail2ban/fail2ban.php")){$roundcube_class->plugin_install($this->root,"fail2ban");}
	
	
	if(is_file("$this->root/plugins/msglistcols/msglistcols.php")){$conf[]="\$rcmail_config['plugins'][] = 'msglistcols';";}
	if(is_file("$this->root/plugins/dkimstatus/dkimstatus.php")){$conf[]="\$rcmail_config['plugins'][] = 'dkimstatus';";}
	if(is_file("$this->root/plugins/fail2ban/fail2ban.php")){$conf[]="\$rcmail_config['plugins'][] = 'fail2ban';";}
	if($roundcube_class->plugin_password($this->root,$hash["OU"][0])){
		if(is_file("$this->root/plugins/dkimstatus/dkimstatus.php")){$conf[]="\$rcmail_config['plugins'][] = 'password';";}
	}
	
	if(is_file("$this->root/plugins/jqueryui/jqueryui.php")){
		$conf[]="\$rcmail_config['plugins'][] = 'jqueryui';";
		$roundcube_class->plugin_jqueryui($this->root);
		if(is_file("$this->root/plugins/sticky_notes/sticky_notes.php")){$conf[]="\$rcmail_config['plugins'][] = 'sticky_notes';";}	
	
	}
	
	
	if(is_file("$this->root/plugins/sieverules/sieverules.php")){
		$users=new usersMenus();
		$sieverules_port=4190;
		if(is_numeric($users->SIEVE_PORT)){if($users->SIEVE_PORT>0){$sieverules_port=$users->SIEVE_PORT;}}
		$conf[]="\$rcmail_config['plugins'][] = 'sieverules';";
		$sieve[]="<?php";
		$sieve[]="\$rcmail_config[\"sieverules_host\"] = \"127.0.0.1\";";
		$sieve[]="\$rcmail_config[\"sieverules_port\"] = $sieverules_port;";
		$sieve[]="\$rcmail_config[\"sieverules_usetls\"] = FALSE;";
		$sieve[]="\$rcmail_config[\"sieverules_folder_delimiter\"] = null;";
		$sieve[]="\$rcmail_config[\"sieverules_folder_encoding\"] = null;";
		$sieve[]="\$rcmail_config[\"sieverules_include_imap_root\"] = null;";
		$sieve[]="\$rcmail_config[\"sieverules_ruleset_name\"] = \"roundcube\";";
		$sieve[]="\$rcmail_config[\"sieverules_multiple_actions\"] = TRUE;";
		$sieve[]="\$rcmail_config[\"sieverules_allowed_actions\"] = array(\"fileinto\" => TRUE,\"vacation\" => TRUE,\"reject\" => TRUE,\"redirect\" => TRUE,\"keep\" => TRUE,\"discard\" => TRUE,\"imapflags\" => TRUE,\"notify\" => TRUE,\"stop\" => TRUE);";
		$sieve[]="\$rcmail_config[\"sieverules_other_headers\"] = array(\"Reply-To\", \"List-Id\", \"MailingList\", \"Mailing-List\",\"X-ML-Name\", \"X-List\", \"X-List-Name\", \"X-Mailing-List\",\"Resent-From\",";
		$sieve[]="	\"Resent-To\", \"X-Mailer\", \"X-MailingList\",\"X-Spam-Status\", \"X-Priority\", \"Importance\", \"X-MSMail-Priority\",\"Precedence\", \"Return-Path\", \"Received\", \"Auto-Submitted\",\"X-Spam-Flag\", \"X-Spam-Tests\");";
		$sieve[]="\$rcmail_config[\"sieverules_predefined_rules\"] = array();";
		$sieve[]="\$rcmail_config[\"sieverules_adveditor\"] = 0;";
		$sieve[]="\$rcmail_config[\"sieverules_multiplerules\"] = FALSE;";
		$sieve[]="\$rcmail_config[\"sieverules_default_file\"] = \"/etc/dovecot/sieve/default\";";
		$sieve[]="\$rcmail_config[\"sieverules_auto_load_default\"] = FALSE;";
		$sieve[]="\$rcmail_config[\"sieverules_example_file\"] = \"/etc/dovecot/sieve/example\";";
		$sieve[]="\$rcmail_config[\"sieverules_force_vacto\"] = TRUE;";
		$sieve[]="\$rcmail_config[\"sieverules_use_elsif\"] = TRUE;";
		$sieve[]="?>";		
		@file_put_contents("$this->root/plugins/sieverules/config.inc.php",@implode("\n",$sieve));
		
	}
	
	$conf[]="";
	$conf[]="";
	$conf[]="// don't let users set pagesize to more than this value if set";
	$conf[]="\$rcmail_config['max_pagesize'] = 200;";
	$conf[]="\$rcmail_config['create_default_folders'] = TRUE;";
	$conf[]="";
	$conf[]="";
	$conf[]="// end of config file";
	$conf[]="?>";	
		
	@file_put_contents("$this->root/config/main.inc.php",@implode("\n",$conf));
	
	
	$sock=new sockets();
	$this->EnablePostfixMultiInstance=$sock->GET_INFO("EnablePostfixMultiInstance");
	
	if($this->EnablePostfixMultiInstance==1){
		echo "Starting......: Roundcube $this->servername Postfix Multi Instance Enabled \n";
		$smtp=$hash[strtolower("WWWMultiSMTPSender")][0];
		$tbl=@explode("\n",@file_get_contents("$this->root/config/main.inc.php"));
		while (list ($i, $line) = each ($tbl) ){
			if(preg_match("#rcmail_config.+?smtp_server#",$line)){
				echo "Starting......: Roundcube $this->servername Postfix change line $i to $smtp\n";
				$tbl[$i]="\$rcmail_config['smtp_server'] = '$smtp';";
			}
		}
		@file_put_contents("$this->root/config/main.inc.php",@implode("\n",$tbl));
	}
}



private function ROUNDCUBE_CONTEXTMENU($dir){
	if(is_file("$dir/plugins/contextmenu/contextmenu.php")){return;}
	writelogs("Installing in $dir/plugins/contextmenu",__FUNCTION__,__FILE__,__LINE__);
	@mkdir("$dir/plugins/contextmenu",0755,true);
	shell_exec("/bin/cp -rf /usr/share/artica-postfix/bin/install/roundcube/contextmenu/* $dir/plugins/contextmenu/");
	shell_exec("/bin/chmod -R 755 $dir/plugins/contextmenu");
	writelogs("Installing in $dir/plugins/contextmenu done...",__FUNCTION__,__FILE__,__LINE__);
}
private function ROUNDCUBE_GLOBALADDRESSBOOK($dir){
	if(is_file("$dir/plugins/globaladdressbook/globaladdressbook.php")){return;}
	writelogs("Installing in $dir/plugins/globaladdressbook",__FUNCTION__,__FILE__,__LINE__);
	@mkdir("$dir/plugins/globaladdressbook",0755,true);
	shell_exec("/bin/cp -rf /usr/share/artica-postfix/bin/install/roundcube/globaladdressbook/* $dir/plugins/globaladdressbook/");
	shell_exec("/bin/chmod -R 755 $dir/plugins/globaladdressbook");
	writelogs("Installing in $dir/plugins/globaladdressbook done...",__FUNCTION__,__FILE__,__LINE__);
}	
	
	
}