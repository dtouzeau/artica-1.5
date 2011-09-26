<?php

class joomla17{
	var $WORKING_DIRECTORY;
	var $mysql_database=null;
	var $servername=null;
	var $mysql_password=null;
	var $mysql_username=null;
	var $www_dir=null;
	var $ou=null;
	function joomla17($servername){
		$this->servername=$servername;
		if($this->servername<>null){$this->load();}
	}
	
	
	private function load(){
			$sql="SELECT * from freeweb WHERE servername='$this->servername'";
			$q=new mysql();
			$ligne=mysql_fetch_array($q->QUERY_SQL($sql,"artica_backup"));
			$this->groupware=$ligne["groupware"];
			$this->servername=$ligne["servername"];
			$this->ou=$ligne["ou"];
			$this->www_dir=$ligne["www_dir"];
			$this->ServerPort=$ligne["ServerPort"];
			$this->mysql_password=$ligne["mysql_password"];
			$this->mysql_username=$ligne["mysql_username"];			
			if($this->www_dir==null){$this->www_dir="/var/www/$this->servername";}
			$this->mysql_database="joomla_".time();
			if($ligne["mysql_database"]<>null){$this->mysql_database=$ligne["mysql_database"];}else{
				$sql="UPDATE freeweb SET mysql_database='{$this->mysql_database}' WHERE servername='$this->servername'";
				$q->QUERY_SQL($sql,"artica_backup");
			}		
		
	}
	
	public function installsite(){
		$servername=$this->servername;
		$unix=new unix();
		$apacheusername=$unix->APACHE_SRC_ACCOUNT();
		$apachegroup=$unix->APACHE_SRC_GROUP();			
		if(!$this->CheckFolder($this->www_dir)){
			$sources="/usr/local/share/artica/joomla17_src";
			writelogs("[$servername] install from \"$sources\"",__FUNCTION__,__FILE__,__LINE__);
			
			$cp=$unix->find_program("cp");
			
			if(!is_dir($sources)){writelogs("[$servername] $sources no such directory",__FUNCTION__,__FILE__,__LINE__);return;}
			if(!is_dir($this->www_dir)){writelogs("[$servername] $this->www_dir no such directory",__FUNCTION__,__FILE__,__LINE__);return;}
	
			writelogs("[$servername] copy sources...",__FUNCTION__,__FILE__,__LINE__);
			$cmd="$cp -rf $sources/* $this->www_dir/";
			writelogs("[$servername] \"$cmd\"",__FUNCTION__,__FILE__,__LINE__);
			shell_exec($cmd);

			
			if(!is_dir("$this->www_dir/tmp")){@mkdir("$this->www_dir/tmp",0644,true);}
			if(!is_dir("$this->www_dir/log")){@mkdir("$this->www_dir/log",0644,true);}
			
			shell_exec("/bin/chown -R $apacheusername:$apachegroup $this->www_dir");
		}
		
		if(!$this->CheckFolder($this->www_dir)){
			writelogs("[$servername] testing directories failed",__FUNCTION__,__FILE__,__LINE__);
			return;
		}

		if(!$this->testTables()){$this->builddatabase();}
			if(!$this->testTables()){
				writelogs("[$servername] testing tables $this->mysql_database failed",__FUNCTION__,__FILE__,__LINE__);
			return;
		}else{
			writelogs("[$servername] testing tables on $this->mysql_database OK",__FUNCTION__,__FILE__,__LINE__);	
		}
		
		if(is_dir("$this->www_dir/installation")){
			$this->WriteConfig();
			shell_exec("/bin/chown $apacheusername:$apachegroup $this->www_dir/*");
			shell_exec("/bin/rm -rf $this->www_dir/installation");
		}
		writelogs("[$servername] Installation done",__FUNCTION__,__FILE__,__LINE__);
		
	}
	
	private function WriteConfig(){
			$q=new mysql();
			if($this->mysql_username==null){$this->mysql_username=$q->mysql_admin;}
			if($this->mysql_password==null){$this->mysql_password=$q->mysql_password;}
			
			
			$f[]="<?php";
			$f[]="class JConfig {";
			$f[]="	public \$offline = '0';";
			$f[]="	public \$offline_message = 'Ce site est en maintenance.<br /> Merci de revenir ultérieurement.';";
			$f[]="	public \$sitename = '$this->servername';";
			$f[]="	public \$editor = 'tinymce';";
			$f[]="	public \$list_limit = '20';";
			$f[]="	public \$access = '1';";
			$f[]="	public \$debug = '0';";
			$f[]="	public \$debug_lang = '0';";
			$f[]="	public \$dbtype = 'mysql';";
			$f[]="	public \$host = '$q->mysql_server';";
			$f[]="	public \$user = '$this->mysql_username';";
			$f[]="	public \$password = '$this->mysql_password';";
			$f[]="	public \$db = '$this->mysql_database';";
			$f[]="	public \$dbprefix = 'jos_';";
			$f[]="	public \$live_site = '';";
			$f[]="	public \$secret = 'aNzZ3nUzacj77yDn';";
			$f[]="	public \$gzip = '0';";
			$f[]="	public \$error_reporting = '-1';";
			$f[]="	public \$helpurl = 'http://help.joomla.org/proxy/index.php?option=com_help&amp;keyref=Help{major}{minor}:{keyref}';";
			$f[]="	public \$ftp_host = '127.0.0.1';";
			$f[]="	public \$ftp_port = '21';";
			$f[]="	public \$ftp_user = '';";
			$f[]="	public \$ftp_pass = '';";
			$f[]="	public \$ftp_root = '';";
			$f[]="	public \$ftp_enable = '0';";
			$f[]="	public \$offset = 'UTC';";
			$f[]="	public \$offset_user = 'UTC';";
			$f[]="	public \$mailer = 'mail';";
			$f[]="	public \$mailfrom = 'admin@root.com';";
			$f[]="	public \$fromname = '$this->servername';";
			$f[]="	public \$sendmail = '/usr/sbin/sendmail';";
			$f[]="	public \$smtpauth = '0';";
			$f[]="	public \$smtpuser = '';";
			$f[]="	public \$smtppass = '';";
			$f[]="	public \$smtphost = 'localhost';";
			$f[]="	public \$smtpsecure = 'none';";
			$f[]="	public \$smtpport = '25';";
			$f[]="	public \$caching = '0';";
			$f[]="	public \$cache_handler = 'file';";
			$f[]="	public \$cachetime = '15';";
			$f[]="	public \$MetaDesc = '';";
			$f[]="	public \$MetaKeys = '';";
			$f[]="	public \$MetaAuthor = '1';";
			$f[]="	public \$sef = '1';";
			$f[]="	public \$sef_rewrite = '0';";
			$f[]="	public \$sef_suffix = '0';";
			$f[]="	public \$unicodeslugs = '0';";
			$f[]="	public \$feed_limit = '10';";
			$f[]="	public \$log_path = '$this->www_dir/logs';";
			$f[]="	public \$tmp_path = '$this->www_dir/tmp';";
			$f[]="	public \$lifetime = '15';";
			$f[]="	public \$session_handler = 'database';";
			$f[]="}";		
			
			@file_put_contents("$this->www_dir/configuration.php", @implode("\n", $f));
			
		
	}
	
	
	private function CheckFolder($path){
		$f[]="/plugins/system/log/log.xml";
		$f[]="/plugins/system/log/index.html";
		$f[]="/plugins/system/logout/logout.xml";
		$f[]="/plugins/system/logout/index.html";
		$f[]="/plugins/system/sef/sef.xml";
		$f[]="/plugins/system/sef/index.html";
		$f[]="/plugins/system/redirect/redirect.xml";
		$f[]="/plugins/system/redirect/index.html";
		$f[]="/plugins/system/cache/cache.xml";
		$f[]="/plugins/system/cache/index.html";
		$f[]="/plugins/system/p3p/p3p.xml";
		$f[]="/plugins/system/p3p/index.html";
		$f[]="/plugins/system/remember/remember.xml";
		$f[]="/plugins/system/remember/index.html";
		$f[]="/plugins/system/debug/index.html";
		$f[]="/plugins/system/debug/debug.xml";
		$f[]="/plugins/system/languagefilter/languagefilter.xml";
		$f[]="/plugins/system/languagefilter/index.html";
		$f[]="/plugins/system/index.html";
		$f[]="/plugins/user/contactcreator/contactcreator.xml";
		$f[]="/plugins/user/contactcreator/index.html";
		$f[]="/plugins/user/profile/profiles/profile.xml";
		$f[]="/plugins/user/profile/profiles/index.html";
		$f[]="/plugins/user/profile/profile.xml";
		$f[]="/plugins/user/profile/index.html";
		$f[]="/plugins/user/index.html";
		$f[]="/plugins/user/joomla/joomla.xml";
		$f[]="/plugins/user/joomla/index.html";
		$f[]="/plugins/editors/codemirror/codemirror.xml";
		$f[]="/plugins/editors/codemirror/index.html";
		$f[]="/plugins/editors/none/none.xml";
		$f[]="/plugins/editors/none/index.html";
		$f[]="/plugins/editors/tinymce/tinymce.xml";
		$f[]="/plugins/editors/tinymce/index.html";
		$f[]="/plugins/editors/index.html";
		$f[]="/plugins/extension/index.html";
		$f[]="/plugins/extension/joomla/joomla.xml";
		$f[]="/plugins/extension/joomla/index.html";
		$f[]="/plugins/index.html";
		$f[]="/plugins/authentication/gmail/gmail.xml";
		$f[]="/plugins/authentication/gmail/index.html";
		$f[]="/plugins/authentication/ldap/ldap.xml";
		$f[]="/plugins/authentication/ldap/index.html";
		$f[]="/plugins/authentication/index.html";
		$f[]="/plugins/authentication/joomla/joomla.xml";
		$f[]="/plugins/authentication/joomla/index.html";
		$f[]="/plugins/search/categories/categories.xml";
		$f[]="/plugins/search/categories/index.html";
		$f[]="/plugins/search/newsfeeds/newsfeeds.xml";
		$f[]="/plugins/search/newsfeeds/index.html";
		$f[]="/plugins/search/contacts/contacts.xml";
		$f[]="/plugins/search/contacts/index.html";
		$f[]="/plugins/search/weblinks/weblinks.xml";
		$f[]="/plugins/search/weblinks/index.html";
		$f[]="/plugins/search/index.html";
		$f[]="/plugins/search/content/content.xml";
		$f[]="/plugins/search/content/index.html";
		$f[]="/plugins/content/pagenavigation/pagenavigation.xml";
		$f[]="/plugins/content/pagenavigation/index.html";
		$f[]="/plugins/content/geshi/geshi.xml";
		$f[]="/plugins/content/geshi/geshi/geshi/index.html";
		$f[]="/plugins/content/geshi/geshi/index.html";
		$f[]="/plugins/content/geshi/index.html";
		$f[]="/plugins/content/vote/vote.xml";
		$f[]="/plugins/content/vote/index.html";
		$f[]="/plugins/content/loadmodule/loadmodule.xml";
		$f[]="/plugins/content/loadmodule/index.html";
		$f[]="/plugins/content/emailcloak/emailcloak.xml";
		$f[]="/plugins/content/emailcloak/index.html";
		$f[]="/plugins/content/pagebreak/pagebreak.xml";
		$f[]="/plugins/content/pagebreak/index.html";
		$f[]="/plugins/content/index.html";
		$f[]="/plugins/content/joomla/joomla.xml";
		$f[]="/plugins/content/joomla/index.html";
		$f[]="/plugins/editors-xtd/article/article.xml";
		$f[]="/plugins/editors-xtd/article/index.html";
		$f[]="/plugins/editors-xtd/pagebreak/pagebreak.xml";
		$f[]="/plugins/editors-xtd/pagebreak/index.html";
		$f[]="/plugins/editors-xtd/index.html";
		$f[]="/plugins/editors-xtd/readmore/readmore.xml";
		$f[]="/plugins/editors-xtd/readmore/index.html";
		$f[]="/plugins/editors-xtd/image/image.xml";
		$f[]="/plugins/editors-xtd/image/index.html";
		$f[]="/images/banners/index.html";
		$f[]="/images/sampledata/parks/landscape/index.html";
		$f[]="/images/sampledata/parks/animals/index.html";
		$f[]="/images/sampledata/parks/index.html";
		$f[]="/images/sampledata/fruitshop/index.html";
		$f[]="/images/sampledata/index.html";
		$f[]="/images/index.html";
		$f[]="/administrator/manifests/files/joomla.xml";
		$f[]="/administrator/manifests/files/index.html";
		$f[]="/administrator/manifests/libraries/phpmailer.xml";
		$f[]="/administrator/manifests/libraries/phputf8.xml";
		$f[]="/administrator/manifests/libraries/simplepie.xml";
		$f[]="/administrator/manifests/libraries/joomla.xml";
		$f[]="/administrator/manifests/libraries/index.html";
		$f[]="/administrator/manifests/packages/pkg_joomla.xml";
		$f[]="/administrator/manifests/packages/index.html";
		$f[]="/administrator/manifests/index.html";
		$f[]="/administrator/templates/system/images/index.html";
		$f[]="/administrator/templates/system/css/index.html";
		$f[]="/administrator/templates/system/index.html";
		$f[]="/administrator/templates/system/html/index.html";
		$f[]="/administrator/templates/bluestork/images/system/index.html";
		$f[]="/administrator/templates/bluestork/images/admin/index.html";
		$f[]="/administrator/templates/bluestork/images/menu/index.html";
		$f[]="/administrator/templates/bluestork/images/toolbar/index.html";
		$f[]="/administrator/templates/bluestork/images/header/index.html";
		$f[]="/administrator/templates/bluestork/images/index.html";
		$f[]="/administrator/templates/bluestork/js/index.html";
		$f[]="/administrator/templates/bluestork/language/en-GB/index.html";
		$f[]="/administrator/templates/bluestork/language/index.html";
		$f[]="/administrator/templates/bluestork/css/index.html";
		$f[]="/administrator/templates/bluestork/index.html";
		$f[]="/administrator/templates/bluestork/html/index.html";
		$f[]="/administrator/templates/bluestork/templateDetails.xml";
		$f[]="/administrator/templates/hathor/images/system/index.html";
		$f[]="/administrator/templates/hathor/css/index.html";
		$f[]="/administrator/templates/hathor/index.html";
		$f[]="/administrator/templates/hathor/html/index.html";
		$f[]="/administrator/templates/hathor/html/com_redirect/links/index.html";
		$f[]="/administrator/templates/hathor/html/com_redirect/index.html";
		$f[]="/administrator/templates/hathor/templateDetails.xml";
		$f[]="/administrator/templates/index.html";
		$f[]="/administrator/cache/index.html";
		$f[]="/administrator/language/overrides/index.html";
		$f[]="/administrator/language/en-GB/index.html";
		$f[]="/administrator/language/en-GB/en-GB.xml";
		$f[]="/administrator/language/index.html";
		$f[]="/administrator/includes/index.html";
		$f[]="/administrator/components/com_contact/access.xml";
		$f[]="/administrator/components/com_contact/views/contacts/tmpl/index.html";
		$f[]="/administrator/components/com_contact/views/contacts/index.html";
		$f[]="/administrator/components/com_contact/views/contact/tmpl/index.html";
		$f[]="/administrator/components/com_contact/views/contact/index.html";
		$f[]="/administrator/components/com_contact/views/index.html";
		$f[]="/administrator/components/com_contact/config.xml";
		$f[]="/administrator/components/com_contact/tables/index.html";
		$f[]="/administrator/components/com_contact/helpers/index.html";
		$f[]="/administrator/components/com_contact/helpers/html/index.html";
		$f[]="/administrator/components/com_contact/controllers/index.html";
		$f[]="/administrator/components/com_contact/elements/index.html";
		$f[]="/administrator/components/com_contact/contact.xml";
		$f[]="/administrator/components/com_contact/sql/index.html";
		$f[]="/administrator/components/com_contact/index.html";
		$f[]="/administrator/components/com_contact/models/forms/contact.xml";
		$f[]="/administrator/components/com_contact/models/forms/index.html";
		$f[]="/administrator/components/com_contact/models/fields/modal/index.html";
		$f[]="/administrator/components/com_contact/models/fields/index.html";
		$f[]="/administrator/components/com_contact/models/index.html";
		$f[]="/administrator/components/com_cache/views/cache/tmpl/index.html";
		$f[]="/administrator/components/com_cache/views/cache/index.html";
		$f[]="/administrator/components/com_cache/views/purge/tmpl/index.html";
		$f[]="/administrator/components/com_cache/views/purge/index.html";
		$f[]="/administrator/components/com_cache/views/index.html";
		$f[]="/administrator/components/com_cache/config.xml";
		$f[]="/administrator/components/com_cache/cache.xml";
		$f[]="/administrator/components/com_cache/helpers/index.html";
		$f[]="/administrator/components/com_cache/index.html";
		$f[]="/administrator/components/com_cache/models/index.html";
		$f[]="/administrator/components/com_cpanel/views/cpanel/tmpl/index.html";
		$f[]="/administrator/components/com_cpanel/views/cpanel/index.html";
		$f[]="/administrator/components/com_cpanel/views/index.html";
		$f[]="/administrator/components/com_cpanel/cpanel.xml";
		$f[]="/administrator/components/com_cpanel/index.html";
		$f[]="/administrator/components/com_admin/views/profile/tmpl/index.html";
		$f[]="/administrator/components/com_admin/views/profile/index.html";
		$f[]="/administrator/components/com_admin/views/sysinfo/tmpl/index.html";
		$f[]="/administrator/components/com_admin/views/sysinfo/index.html";
		$f[]="/administrator/components/com_admin/views/index.html";
		$f[]="/administrator/components/com_admin/views/help/tmpl/index.html";
		$f[]="/administrator/components/com_admin/views/help/index.html";
		$f[]="/administrator/components/com_admin/helpers/index.html";
		$f[]="/administrator/components/com_admin/helpers/html/index.html";
		$f[]="/administrator/components/com_admin/controllers/index.html";
		$f[]="/administrator/components/com_admin/admin.xml";
		$f[]="/administrator/components/com_admin/sql/updates/mysql/index.html";
		$f[]="/administrator/components/com_admin/sql/updates/index.html";
		$f[]="/administrator/components/com_admin/sql/index.html";
		$f[]="/administrator/components/com_admin/index.html";
		$f[]="/administrator/components/com_admin/models/forms/profile.xml";
		$f[]="/administrator/components/com_admin/models/forms/index.html";
		$f[]="/administrator/components/com_admin/models/index.html";
		$f[]="/administrator/components/com_weblinks/access.xml";
		$f[]="/administrator/components/com_weblinks/config.xml";
		$f[]="/administrator/components/com_weblinks/tables/index.html";
		$f[]="/administrator/components/com_weblinks/helpers/index.html";
		$f[]="/administrator/components/com_weblinks/controllers/index.html";
		$f[]="/administrator/components/com_weblinks/sql/index.html";
		$f[]="/administrator/components/com_weblinks/index.html";
		$f[]="/administrator/components/com_weblinks/models/forms/weblink.xml";
		$f[]="/administrator/components/com_weblinks/models/forms/index.html";
		$f[]="/administrator/components/com_weblinks/models/fields/index.html";
		$f[]="/administrator/components/com_weblinks/models/index.html";
		$f[]="/administrator/components/com_categories/views/categories/tmpl/index.html";
		$f[]="/administrator/components/com_categories/views/categories/index.html";
		$f[]="/administrator/components/com_categories/views/category/tmpl/index.html";
		$f[]="/administrator/components/com_categories/views/category/index.html";
		$f[]="/administrator/components/com_categories/views/index.html";
		$f[]="/administrator/components/com_categories/config.xml";
		$f[]="/administrator/components/com_categories/categories.xml";
		$f[]="/administrator/components/com_categories/helpers/index.html";
		$f[]="/administrator/components/com_categories/controllers/index.html";
		$f[]="/administrator/components/com_categories/index.html";
		$f[]="/administrator/components/com_categories/models/forms/category.xml";
		$f[]="/administrator/components/com_categories/models/forms/index.html";
		$f[]="/administrator/components/com_categories/models/fields/index.html";
		$f[]="/administrator/components/com_categories/models/index.html";
		$f[]="/administrator/components/com_banners/access.xml";
		$f[]="/administrator/components/com_banners/views/client/index.html";
		$f[]="/administrator/components/com_banners/views/index.html";
		$f[]="/administrator/components/com_banners/config.xml";
		$f[]="/administrator/components/com_banners/banners.xml";
		$f[]="/administrator/components/com_banners/tables/index.html";
		$f[]="/administrator/components/com_banners/helpers/index.html";
		$f[]="/administrator/components/com_banners/controllers/index.html";
		$f[]="/administrator/components/com_banners/sql/index.html";
		$f[]="/administrator/components/com_banners/index.html";
		$f[]="/administrator/components/com_banners/models/forms/banner.xml";
		$f[]="/administrator/components/com_banners/models/forms/client.xml";
		$f[]="/administrator/components/com_banners/models/forms/download.xml";
		$f[]="/administrator/components/com_banners/models/forms/index.html";
		$f[]="/administrator/components/com_banners/models/fields/index.html";
		$f[]="/administrator/components/com_banners/models/index.html";
		$f[]="/administrator/components/com_menus/views/menu/tmpl/index.html";
		$f[]="/administrator/components/com_menus/views/menu/index.html";
		$f[]="/administrator/components/com_menus/views/item/tmpl/index.html";
		$f[]="/administrator/components/com_menus/views/item/index.html";
		$f[]="/administrator/components/com_menus/views/menus/tmpl/index.html";
		$f[]="/administrator/components/com_menus/views/menus/index.html";
		$f[]="/administrator/components/com_menus/views/items/tmpl/index.html";
		$f[]="/administrator/components/com_menus/views/items/index.html";
		$f[]="/administrator/components/com_menus/views/index.html";
		$f[]="/administrator/components/com_menus/config.xml";
		$f[]="/administrator/components/com_menus/menus.xml";
		$f[]="/administrator/components/com_menus/helpers/index.html";
		$f[]="/administrator/components/com_menus/helpers/html/index.html";
		$f[]="/administrator/components/com_menus/controllers/index.html";
		$f[]="/administrator/components/com_menus/index.html";
		$f[]="/administrator/components/com_menus/models/forms/item_alias.xml";
		$f[]="/administrator/components/com_menus/models/forms/menu.xml";
		$f[]="/administrator/components/com_menus/models/forms/item_url.xml";
		$f[]="/administrator/components/com_menus/models/forms/item.xml";
		$f[]="/administrator/components/com_menus/models/forms/item_separator.xml";
		$f[]="/administrator/components/com_menus/models/forms/item_component.xml";
		$f[]="/administrator/components/com_menus/models/forms/index.html";
		$f[]="/administrator/components/com_menus/models/fields/index.html";
		$f[]="/administrator/components/com_menus/models/index.html";
		$f[]="/administrator/components/com_users/views/levels/tmpl/index.html";
		$f[]="/administrator/components/com_users/views/levels/index.html";
		$f[]="/administrator/components/com_users/views/mail/tmpl/index.html";
		$f[]="/administrator/components/com_users/views/mail/index.html";
		$f[]="/administrator/components/com_users/views/debuggroup/tmpl/index.html";
		$f[]="/administrator/components/com_users/views/debuggroup/index.html";
		$f[]="/administrator/components/com_users/views/debuguser/tmpl/index.html";
		$f[]="/administrator/components/com_users/views/debuguser/index.html";
		$f[]="/administrator/components/com_users/views/groups/tmpl/index.html";
		$f[]="/administrator/components/com_users/views/groups/index.html";
		$f[]="/administrator/components/com_users/views/user/tmpl/index.html";
		$f[]="/administrator/components/com_users/views/user/index.html";
		$f[]="/administrator/components/com_users/views/level/tmpl/index.html";
		$f[]="/administrator/components/com_users/views/level/index.html";
		$f[]="/administrator/components/com_users/views/users/tmpl/index.html";
		$f[]="/administrator/components/com_users/views/users/index.html";
		$f[]="/administrator/components/com_users/views/group/tmpl/index.html";
		$f[]="/administrator/components/com_users/views/group/index.html";
		$f[]="/administrator/components/com_users/views/index.html";
		$f[]="/administrator/components/com_users/config.xml";
		$f[]="/administrator/components/com_users/users.xml";
		$f[]="/administrator/components/com_users/helpers/index.html";
		$f[]="/administrator/components/com_users/controllers/index.html";
		$f[]="/administrator/components/com_users/index.html";
		$f[]="/administrator/components/com_users/models/forms/user.xml";
		$f[]="/administrator/components/com_users/models/forms/group.xml";
		$f[]="/administrator/components/com_users/models/forms/level.xml";
		$f[]="/administrator/components/com_users/models/forms/mail.xml";
		$f[]="/administrator/components/com_users/models/forms/index.html";
		$f[]="/administrator/components/com_users/models/fields/index.html";
		$f[]="/administrator/components/com_users/models/index.html";
		$f[]="/administrator/components/com_content/access.xml";
		$f[]="/administrator/components/com_content/views/articles/tmpl/index.html";
		$f[]="/administrator/components/com_content/views/articles/index.html";
		$f[]="/administrator/components/com_content/views/article/tmpl/index.html";
		$f[]="/administrator/components/com_content/views/article/index.html";
		$f[]="/administrator/components/com_content/views/featured/tmpl/index.html";
		$f[]="/administrator/components/com_content/views/featured/index.html";
		$f[]="/administrator/components/com_content/views/index.html";
		$f[]="/administrator/components/com_content/config.xml";
		$f[]="/administrator/components/com_content/content.xml";
		$f[]="/administrator/components/com_content/tables/index.html";
		$f[]="/administrator/components/com_content/helpers/index.html";
		$f[]="/administrator/components/com_content/helpers/html/index.html";
		$f[]="/administrator/components/com_content/controllers/index.html";
		$f[]="/administrator/components/com_content/elements/index.html";
		$f[]="/administrator/components/com_content/index.html";
		$f[]="/administrator/components/com_content/models/forms/article.xml";
		$f[]="/administrator/components/com_content/models/forms/index.html";
		$f[]="/administrator/components/com_content/models/fields/modal/index.html";
		$f[]="/administrator/components/com_content/models/fields/index.html";
		$f[]="/administrator/components/com_content/models/index.html";
		$f[]="/administrator/components/com_login/views/login/tmpl/index.html";
		$f[]="/administrator/components/com_login/views/login/index.html";
		$f[]="/administrator/components/com_login/views/index.html";
		$f[]="/administrator/components/com_login/login.xml";
		$f[]="/administrator/components/com_login/index.html";
		$f[]="/administrator/components/com_login/models/index.html";
		$f[]="/administrator/components/com_modules/views/preview/tmpl/index.html";
		$f[]="/administrator/components/com_modules/views/preview/index.html";
		$f[]="/administrator/components/com_modules/views/positions/tmpl/index.html";
		$f[]="/administrator/components/com_modules/views/positions/index.html";
		$f[]="/administrator/components/com_modules/views/select/tmpl/index.html";
		$f[]="/administrator/components/com_modules/views/select/index.html";
		$f[]="/administrator/components/com_modules/views/module/tmpl/index.html";
		$f[]="/administrator/components/com_modules/views/module/index.html";
		$f[]="/administrator/components/com_modules/views/index.html";
		$f[]="/administrator/components/com_modules/views/modules/tmpl/index.html";
		$f[]="/administrator/components/com_modules/views/modules/index.html";
		$f[]="/administrator/components/com_modules/config.xml";
		$f[]="/administrator/components/com_modules/helpers/index.html";
		$f[]="/administrator/components/com_modules/helpers/html/index.html";
		$f[]="/administrator/components/com_modules/modules.xml";
		$f[]="/administrator/components/com_modules/controllers/index.html";
		$f[]="/administrator/components/com_modules/index.html";
		$f[]="/administrator/components/com_modules/models/forms/module.xml";
		$f[]="/administrator/components/com_modules/models/forms/index.html";
		$f[]="/administrator/components/com_modules/models/fields/index.html";
		$f[]="/administrator/components/com_modules/models/index.html";
		$f[]="/administrator/components/com_search/views/searches/tmpl/index.html";
		$f[]="/administrator/components/com_search/views/searches/index.html";
		$f[]="/administrator/components/com_search/views/index.html";
		$f[]="/administrator/components/com_search/config.xml";
		$f[]="/administrator/components/com_search/helpers/index.html";
		$f[]="/administrator/components/com_search/controllers/index.html";
		$f[]="/administrator/components/com_search/search.xml";
		$f[]="/administrator/components/com_search/index.html";
		$f[]="/administrator/components/com_search/models/index.html";
		$f[]="/administrator/components/com_plugins/views/plugins/tmpl/index.html";
		$f[]="/administrator/components/com_plugins/views/plugins/index.html";
		$f[]="/administrator/components/com_plugins/views/plugin/tmpl/index.html";
		$f[]="/administrator/components/com_plugins/views/plugin/index.html";
		$f[]="/administrator/components/com_plugins/views/index.html";
		$f[]="/administrator/components/com_plugins/config.xml";
		$f[]="/administrator/components/com_plugins/helpers/index.html";
		$f[]="/administrator/components/com_plugins/controllers/index.html";
		$f[]="/administrator/components/com_plugins/plugins.xml";
		$f[]="/administrator/components/com_plugins/index.html";
		$f[]="/administrator/components/com_plugins/models/forms/plugin.xml";
		$f[]="/administrator/components/com_plugins/models/forms/index.html";
		$f[]="/administrator/components/com_plugins/models/fields/index.html";
		$f[]="/administrator/components/com_plugins/models/index.html";
		$f[]="/administrator/components/com_newsfeeds/access.xml";
		$f[]="/administrator/components/com_newsfeeds/views/newsfeed/tmpl/index.html";
		$f[]="/administrator/components/com_newsfeeds/views/newsfeed/index.html";
		$f[]="/administrator/components/com_newsfeeds/views/newsfeeds/tmpl/index.html";
		$f[]="/administrator/components/com_newsfeeds/views/newsfeeds/index.html";
		$f[]="/administrator/components/com_newsfeeds/views/index.html";
		$f[]="/administrator/components/com_newsfeeds/config.xml";
		$f[]="/administrator/components/com_newsfeeds/newsfeeds.xml";
		$f[]="/administrator/components/com_newsfeeds/tables/index.html";
		$f[]="/administrator/components/com_newsfeeds/helpers/index.html";
		$f[]="/administrator/components/com_newsfeeds/helpers/html/index.html";
		$f[]="/administrator/components/com_newsfeeds/controllers/index.html";
		$f[]="/administrator/components/com_newsfeeds/elements/index.html";
		$f[]="/administrator/components/com_newsfeeds/sql/index.html";
		$f[]="/administrator/components/com_newsfeeds/index.html";
		$f[]="/administrator/components/com_newsfeeds/models/forms/newsfeed.xml";
		$f[]="/administrator/components/com_newsfeeds/models/forms/index.html";
		$f[]="/administrator/components/com_newsfeeds/models/fields/modal/index.html";
		$f[]="/administrator/components/com_newsfeeds/models/fields/index.html";
		$f[]="/administrator/components/com_newsfeeds/models/index.html";
		$f[]="/administrator/components/com_languages/views/language/tmpl/index.html";
		$f[]="/administrator/components/com_languages/views/language/index.html";
		$f[]="/administrator/components/com_languages/views/installed/tmpl/index.html";
		$f[]="/administrator/components/com_languages/views/installed/index.html";
		$f[]="/administrator/components/com_languages/views/languages/tmpl/index.html";
		$f[]="/administrator/components/com_languages/views/languages/index.html";
		$f[]="/administrator/components/com_languages/views/index.html";
		$f[]="/administrator/components/com_languages/config.xml";
		$f[]="/administrator/components/com_languages/languages.xml";
		$f[]="/administrator/components/com_languages/helpers/index.html";
		$f[]="/administrator/components/com_languages/helpers/html/index.html";
		$f[]="/administrator/components/com_languages/controllers/index.html";
		$f[]="/administrator/components/com_languages/index.html";
		$f[]="/administrator/components/com_languages/models/forms/language.xml";
		$f[]="/administrator/components/com_languages/models/forms/index.html";
		$f[]="/administrator/components/com_languages/models/index.html";
		$f[]="/administrator/components/com_config/views/close/index.html";
		$f[]="/administrator/components/com_config/views/component/tmpl/index.html";
		$f[]="/administrator/components/com_config/views/component/index.html";
		$f[]="/administrator/components/com_config/views/application/tmpl/index.html";
		$f[]="/administrator/components/com_config/views/application/index.html";
		$f[]="/administrator/components/com_config/views/index.html";
		$f[]="/administrator/components/com_config/config.xml";
		$f[]="/administrator/components/com_config/controllers/index.html";
		$f[]="/administrator/components/com_config/index.html";
		$f[]="/administrator/components/com_config/models/forms/application.xml";
		$f[]="/administrator/components/com_config/models/forms/index.html";
		$f[]="/administrator/components/com_config/models/index.html";
		$f[]="/administrator/components/com_templates/views/templates/tmpl/index.html";
		$f[]="/administrator/components/com_templates/views/templates/index.html";
		$f[]="/administrator/components/com_templates/views/template/tmpl/index.html";
		$f[]="/administrator/components/com_templates/views/template/index.html";
		$f[]="/administrator/components/com_templates/views/styles/tmpl/index.html";
		$f[]="/administrator/components/com_templates/views/styles/index.html";
		$f[]="/administrator/components/com_templates/views/source/tmpl/index.html";
		$f[]="/administrator/components/com_templates/views/source/index.html";
		$f[]="/administrator/components/com_templates/views/prevuuw/tmpl/index.html";
		$f[]="/administrator/components/com_templates/views/prevuuw/index.html";
		$f[]="/administrator/components/com_templates/views/index.html";
		$f[]="/administrator/components/com_templates/views/style/tmpl/index.html";
		$f[]="/administrator/components/com_templates/views/style/index.html";
		$f[]="/administrator/components/com_templates/config.xml";
		$f[]="/administrator/components/com_templates/templates.xml";
		$f[]="/administrator/components/com_templates/tables/index.html";
		$f[]="/administrator/components/com_templates/helpers/index.html";
		$f[]="/administrator/components/com_templates/helpers/html/index.html";
		$f[]="/administrator/components/com_templates/controllers/index.html";
		$f[]="/administrator/components/com_templates/index.html";
		$f[]="/administrator/components/com_templates/models/forms/style.xml";
		$f[]="/administrator/components/com_templates/models/forms/source.xml";
		$f[]="/administrator/components/com_templates/models/forms/style_site.xml";
		$f[]="/administrator/components/com_templates/models/forms/index.html";
		$f[]="/administrator/components/com_templates/models/forms/style_administrator.xml";
		$f[]="/administrator/components/com_templates/models/index.html";
		$f[]="/administrator/components/com_messages/views/config/tmpl/index.html";
		$f[]="/administrator/components/com_messages/views/config/index.html";
		$f[]="/administrator/components/com_messages/views/messages/tmpl/index.html";
		$f[]="/administrator/components/com_messages/views/messages/index.html";
		$f[]="/administrator/components/com_messages/views/index.html";
		$f[]="/administrator/components/com_messages/views/message/tmpl/index.html";
		$f[]="/administrator/components/com_messages/views/message/index.html";
		$f[]="/administrator/components/com_messages/config.xml";
		$f[]="/administrator/components/com_messages/messages.xml";
		$f[]="/administrator/components/com_messages/tables/index.html";
		$f[]="/administrator/components/com_messages/helpers/index.html";
		$f[]="/administrator/components/com_messages/helpers/html/index.html";
		$f[]="/administrator/components/com_messages/controllers/index.html";
		$f[]="/administrator/components/com_messages/index.html";
		$f[]="/administrator/components/com_messages/models/forms/config.xml";
		$f[]="/administrator/components/com_messages/models/forms/message.xml";
		$f[]="/administrator/components/com_messages/models/forms/index.html";
		$f[]="/administrator/components/com_messages/models/fields/index.html";
		$f[]="/administrator/components/com_messages/models/index.html";
		$f[]="/administrator/components/com_checkin/checkin.xml";
		$f[]="/administrator/components/com_checkin/views/checkin/tmpl/index.html";
		$f[]="/administrator/components/com_checkin/views/checkin/index.html";
		$f[]="/administrator/components/com_checkin/views/index.html";
		$f[]="/administrator/components/com_checkin/config.xml";
		$f[]="/administrator/components/com_checkin/index.html";
		$f[]="/administrator/components/com_checkin/models/index.html";
		$f[]="/administrator/components/index.html";
		$f[]="/administrator/components/com_redirect/views/links/tmpl/index.html";
		$f[]="/administrator/components/com_redirect/views/links/index.html";
		$f[]="/administrator/components/com_redirect/views/link/tmpl/index.html";
		$f[]="/administrator/components/com_redirect/views/link/index.html";
		$f[]="/administrator/components/com_redirect/views/index.html";
		$f[]="/administrator/components/com_redirect/config.xml";
		$f[]="/administrator/components/com_redirect/tables/index.html";
		$f[]="/administrator/components/com_redirect/helpers/index.html";
		$f[]="/administrator/components/com_redirect/helpers/html/index.html";
		$f[]="/administrator/components/com_redirect/controllers/index.html";
		$f[]="/administrator/components/com_redirect/redirect.xml";
		$f[]="/administrator/components/com_redirect/index.html";
		$f[]="/administrator/components/com_redirect/models/forms/link.xml";
		$f[]="/administrator/components/com_redirect/models/forms/index.html";
		$f[]="/administrator/components/com_redirect/models/index.html";
		$f[]="/administrator/components/com_media/media.xml";
		$f[]="/administrator/components/com_media/views/images/tmpl/index.html";
		$f[]="/administrator/components/com_media/views/images/index.html";
		$f[]="/administrator/components/com_media/views/imageslist/tmpl/index.html";
		$f[]="/administrator/components/com_media/views/imageslist/index.html";
		$f[]="/administrator/components/com_media/views/media/tmpl/index.html";
		$f[]="/administrator/components/com_media/views/media/index.html";
		$f[]="/administrator/components/com_media/views/medialist/tmpl/index.html";
		$f[]="/administrator/components/com_media/views/medialist/index.html";
		$f[]="/administrator/components/com_media/views/index.html";
		$f[]="/administrator/components/com_media/config.xml";
		$f[]="/administrator/components/com_media/helpers/index.html";
		$f[]="/administrator/components/com_media/controllers/index.html";
		$f[]="/administrator/components/com_media/index.html";
		$f[]="/administrator/components/com_media/models/forms/index.html";
		$f[]="/administrator/components/com_media/models/index.html";
		$f[]="/administrator/modules/mod_toolbar/tmpl/index.html";
		$f[]="/administrator/modules/mod_toolbar/index.html";
		$f[]="/administrator/modules/mod_toolbar/mod_toolbar.xml";
		$f[]="/administrator/modules/mod_title/tmpl/index.html";
		$f[]="/administrator/modules/mod_title/mod_title.xml";
		$f[]="/administrator/modules/mod_title/index.html";
		$f[]="/administrator/modules/mod_submenu/tmpl/index.html";
		$f[]="/administrator/modules/mod_submenu/index.html";
		$f[]="/administrator/modules/mod_submenu/mod_submenu.xml";
		$f[]="/administrator/modules/mod_logged/tmpl/index.html";
		$f[]="/administrator/modules/mod_logged/mod_logged.xml";
		$f[]="/administrator/modules/mod_logged/index.html";
		$f[]="/administrator/modules/mod_status/tmpl/index.html";
		$f[]="/administrator/modules/mod_status/mod_status.xml";
		$f[]="/administrator/modules/mod_status/index.html";
		$f[]="/administrator/modules/mod_custom/tmpl/index.html";
		$f[]="/administrator/modules/mod_custom/mod_custom.xml";
		$f[]="/administrator/modules/mod_custom/index.html";
		$f[]="/administrator/modules/mod_latest/tmpl/index.html";
		$f[]="/administrator/modules/mod_latest/mod_latest.xml";
		$f[]="/administrator/modules/mod_latest/index.html";
		$f[]="/administrator/modules/mod_quickicon/tmpl/index.html";
		$f[]="/administrator/modules/mod_quickicon/mod_quickicon.xml";
		$f[]="/administrator/modules/mod_quickicon/index.html";
		$f[]="/administrator/modules/mod_popular/tmpl/index.html";
		$f[]="/administrator/modules/mod_popular/mod_popular.xml";
		$f[]="/administrator/modules/mod_popular/index.html";
		$f[]="/administrator/modules/mod_menu/tmpl/index.html";
		$f[]="/administrator/modules/mod_menu/mod_menu.xml";
		$f[]="/administrator/modules/mod_menu/index.html";
		$f[]="/administrator/modules/index.html";
		$f[]="/administrator/modules/mod_login/tmpl/index.html";
		$f[]="/administrator/modules/mod_login/mod_login.xml";
		$f[]="/administrator/modules/mod_login/index.html";
		$f[]="/administrator/modules/mod_feed/tmpl/index.html";
		$f[]="/administrator/modules/mod_feed/mod_feed.xml";
		$f[]="/administrator/modules/mod_feed/index.html";
		$f[]="/administrator/help/en-GB/Extensions_Module_Manager.html";
		$f[]="/administrator/help/en-GB/Components_Weblinks_Categories.html";
		$f[]="/administrator/help/en-GB/Components_Banners_Tracks.html";
		$f[]="/administrator/help/en-GB/Components_Search.html";
		$f[]="/administrator/help/en-GB/Extensions_Extension_Manager_Warnings.html";
		$f[]="/administrator/help/en-GB/Users_Access_Levels_Edit.html";
		$f[]="/administrator/help/en-GB/Start_Here.html";
		$f[]="/administrator/help/en-GB/Site_Maintenance_Global_Check-in.html";
		$f[]="/administrator/help/en-GB/Extensions_Extension_Manager_Manage.html";
		$f[]="/administrator/help/en-GB/Components_Content_Categories.html";
		$f[]="/administrator/help/en-GB/Components_Contact_Categories.html";
		$f[]="/administrator/help/en-GB/Users_Mass_Mail_Users.html";
		$f[]="/administrator/help/en-GB/Components_Newsfeeds_Category_Edit.html";
		$f[]="/administrator/help/en-GB/Menus_Menu_Manager.html";
		$f[]="/administrator/help/en-GB/Components_Banners_Category_Edit.html";
		$f[]="/administrator/help/en-GB/Extensions_Plugin_Manager.html";
		$f[]="/administrator/help/en-GB/Extensions_Language_Manager_Content.html";
		$f[]="/administrator/help/en-GB/Extensions_Template_Manager_Styles_Edit.html";
		$f[]="/administrator/help/en-GB/Users_Groups.html";
		$f[]="/administrator/help/en-GB/Glossary.html";
		$f[]="/administrator/help/helpsites.xml";
		$f[]="/administrator/help/index.html";
		$f[]="/templates/system/images/index.html";
		$f[]="/templates/system/css/index.html";
		$f[]="/templates/system/index.html";
		$f[]="/templates/system/html/index.html";
		$f[]="/templates/atomic/images/index.html";
		$f[]="/templates/atomic/js/index.html";
		$f[]="/templates/atomic/language/en-GB/index.html";
		$f[]="/templates/atomic/language/index.html";
		$f[]="/templates/atomic/css/blueprint/plugins/link-icons/icons/index.html";
		$f[]="/templates/atomic/css/blueprint/plugins/link-icons/index.html";
		$f[]="/templates/atomic/css/blueprint/plugins/silksprite/index.html";
		$f[]="/templates/atomic/css/blueprint/plugins/tabs/index.html";
		$f[]="/templates/atomic/css/blueprint/plugins/fancy-type/index.html";
		$f[]="/templates/atomic/css/blueprint/plugins/liquidgrid/index.html";
		$f[]="/templates/atomic/css/blueprint/plugins/rtl/index.html";
		$f[]="/templates/atomic/css/blueprint/plugins/joomla-nav/index.html";
		$f[]="/templates/atomic/css/blueprint/plugins/index.html";
		$f[]="/templates/atomic/css/blueprint/plugins/buttons/icons/index.html";
		$f[]="/templates/atomic/css/blueprint/plugins/buttons/index.html";
		$f[]="/templates/atomic/css/blueprint/src/templates/psd/index.html";
		$f[]="/templates/atomic/css/blueprint/src/templates/index.html";
		$f[]="/templates/atomic/css/blueprint/src/index.html";
		$f[]="/templates/atomic/css/blueprint/index.html";
		$f[]="/templates/atomic/css/index.html";
		$f[]="/templates/atomic/index.html";
		$f[]="/templates/atomic/html/mod_custom/index.html";
		$f[]="/templates/atomic/html/mod_search/index.html";
		$f[]="/templates/atomic/html/mod_menu/index.html";
		$f[]="/templates/atomic/html/index.html";
		$f[]="/templates/atomic/html/mod_login/index.html";
		$f[]="/templates/atomic/templateDetails.xml";
		$f[]="/templates/index.html";
		$f[]="/templates/beez_20/images/system/index.html";
		$f[]="/templates/beez_20/images/nature/index.html";
		$f[]="/templates/beez_20/images/personal/index.html";
		$f[]="/templates/beez_20/images/index.html";
		$f[]="/templates/beez_20/language/en-GB/index.html";
		$f[]="/templates/beez_20/language/index.html";
		$f[]="/templates/beez_20/css/index.html";
		$f[]="/templates/beez_20/fonts/index.html";
		$f[]="/templates/beez_20/index.html";
		$f[]="/templates/beez_20/html/index.html";
		$f[]="/templates/beez_20/templateDetails.xml";
		$f[]="/templates/beez_20/javascript/index.html";
		$f[]="/templates/beez5/images/system/index.html";
		$f[]="/templates/beez5/images/index.html";
		$f[]="/templates/beez5/language/en-GB/index.html";
		$f[]="/templates/beez5/language/index.html";
		$f[]="/templates/beez5/css/index.html";
		$f[]="/templates/beez5/fonts/index.html";
		$f[]="/templates/beez5/index.html";
		$f[]="/templates/beez5/html/com_contact/contact/index.html";
		$f[]="/templates/beez5/html/com_contact/index.html";
		$f[]="/templates/beez5/html/com_content/categories/index.html";
		$f[]="/templates/beez5/html/com_content/article/index.html";
		$f[]="/templates/beez5/html/com_content/featured/index.html";
		$f[]="/templates/beez5/html/com_content/category/index.html";
		$f[]="/templates/beez5/html/com_content/archive/index.html";
		$f[]="/templates/beez5/html/com_content/index.html";
		$f[]="/templates/beez5/html/index.html";
		$f[]="/templates/beez5/templateDetails.xml";
		$f[]="/templates/beez5/javascript/index.html";
		$f[]="/cache/index.html";
		$f[]="/media/system/images/modal/index.html";
		$f[]="/media/system/images/index.html";
		$f[]="/media/system/js/index.html";
		$f[]="/media/system/css/index.html";
		$f[]="/media/system/swf/index.html";
		$f[]="/media/system/index.html";
		$f[]="/media/mailto/images/index.html";
		$f[]="/media/mailto/index.html";
		$f[]="/media/media/images/mime-icon-32/index.html";
		$f[]="/media/media/images/mime-icon-16/index.html";
		$f[]="/media/media/images/index.html";
		$f[]="/media/media/js/index.html";
		$f[]="/media/media/css/index.html";
		$f[]="/media/media/index.html";
		$f[]="/media/contacts/images/index.html";
		$f[]="/media/contacts/index.html";
		$f[]="/media/editors/codemirror/js/index.html";
		$f[]="/media/editors/codemirror/css/index.html";
		$f[]="/media/editors/codemirror/index.html";
		$f[]="/media/editors/index.html";
		$f[]="/media/mod_languages/images/index.html";
		$f[]="/media/mod_languages/css/index.html";
		$f[]="/media/mod_languages/index.html";
		$f[]="/media/index.html";
		$f[]="/language/overrides/index.html";
		$f[]="/language/en-GB/index.html";
		$f[]="/language/en-GB/en-GB.xml";
		$f[]="/language/index.html";
		$f[]="/joomla.xml";
		$f[]="/includes/index.html";
		$f[]="/libraries/phpmailer/language/index.html";
		$f[]="/libraries/phpmailer/index.html";
		$f[]="/libraries/phputf8/utils/index.html";
		$f[]="/libraries/phputf8/mbstring/index.html";
		$f[]="/libraries/phputf8/native/index.html";
		$f[]="/libraries/phputf8/index.html";
		$f[]="/libraries/index.html";
		$f[]="/libraries/simplepie/idn/index.html";
		$f[]="/libraries/simplepie/index.html";
		$f[]="/libraries/joomla/utilities/index.html";
		$f[]="/libraries/joomla/plugin/index.html";
		$f[]="/libraries/joomla/mail/index.html";
		$f[]="/libraries/joomla/error/index.html";
		$f[]="/libraries/joomla/filter/index.html";
		$f[]="/libraries/joomla/application/component/index.html";
		$f[]="/libraries/joomla/application/input/index.html";
		$f[]="/libraries/joomla/application/module/index.html";
		$f[]="/libraries/joomla/application/index.html";
		$f[]="/libraries/joomla/cache/storage/helpers/index.html";
		$f[]="/libraries/joomla/cache/storage/index.html";
		$f[]="/libraries/joomla/cache/index.html";
		$f[]="/libraries/joomla/cache/controller/index.html";
		$f[]="/libraries/joomla/updater/adapters/index.html";
		$f[]="/libraries/joomla/updater/index.html";
		$f[]="/libraries/joomla/language/index.html";
		$f[]="/libraries/joomla/session/storage/index.html";
		$f[]="/libraries/joomla/session/index.html";
		$f[]="/libraries/joomla/client/index.html";
		$f[]="/libraries/joomla/base/index.html";
		$f[]="/libraries/joomla/user/index.html";
		$f[]="/libraries/joomla/form/rules/index.html";
		$f[]="/libraries/joomla/form/fields/index.html";
		$f[]="/libraries/joomla/form/index.html";
		$f[]="/libraries/joomla/event/index.html";
		$f[]="/libraries/joomla/document/error/index.html";
		$f[]="/libraries/joomla/document/raw/index.html";
		$f[]="/libraries/joomla/document/xml/index.html";
		$f[]="/libraries/joomla/document/json/index.html";
		$f[]="/libraries/joomla/document/opensearch/index.html";
		$f[]="/libraries/joomla/document/feed/index.html";
		$f[]="/libraries/joomla/document/feed/renderer/index.html";
		$f[]="/libraries/joomla/document/index.html";
		$f[]="/libraries/joomla/document/html/index.html";
		$f[]="/libraries/joomla/document/html/renderer/index.html";
		$f[]="/libraries/joomla/filesystem/support/index.html";
		$f[]="/libraries/joomla/filesystem/streams/index.html";
		$f[]="/libraries/joomla/filesystem/archive/index.html";
		$f[]="/libraries/joomla/filesystem/index.html";
		$f[]="/libraries/joomla/registry/format/index.html";
		$f[]="/libraries/joomla/registry/index.html";
		$f[]="/libraries/joomla/index.html";
		$f[]="/libraries/joomla/access/index.html";
		$f[]="/libraries/joomla/html/parameter/element/index.html";
		$f[]="/libraries/joomla/html/parameter/index.html";
		$f[]="/libraries/joomla/html/toolbar/button/index.html";
		$f[]="/libraries/joomla/html/toolbar/index.html";
		$f[]="/libraries/joomla/html/index.html";
		$f[]="/libraries/joomla/html/html/index.html";
		$f[]="/libraries/joomla/environment/index.html";
		$f[]="/libraries/joomla/database/table/index.html";
		$f[]="/libraries/joomla/database/index.html";
		$f[]="/libraries/joomla/database/database/index.html";
		$f[]="/components/com_contact/views/categories/tmpl/index.html";
		$f[]="/components/com_contact/views/categories/tmpl/default.xml";
		$f[]="/components/com_contact/views/categories/index.html";
		$f[]="/components/com_contact/views/featured/tmpl/index.html";
		$f[]="/components/com_contact/views/featured/tmpl/default.xml";
		$f[]="/components/com_contact/views/featured/metadata.xml";
		$f[]="/components/com_contact/views/featured/index.html";
		$f[]="/components/com_contact/views/category/tmpl/index.html";
		$f[]="/components/com_contact/views/category/tmpl/default.xml";
		$f[]="/components/com_contact/views/category/metadata.xml";
		$f[]="/components/com_contact/views/category/index.html";
		$f[]="/components/com_contact/views/contact/tmpl/index.html";
		$f[]="/components/com_contact/views/contact/tmpl/default.xml";
		$f[]="/components/com_contact/views/contact/metadata.xml";
		$f[]="/components/com_contact/views/contact/index.html";
		$f[]="/components/com_contact/views/index.html";
		$f[]="/components/com_contact/metadata.xml";
		$f[]="/components/com_contact/helpers/index.html";
		$f[]="/components/com_contact/controllers/index.html";
		$f[]="/components/com_contact/index.html";
		$f[]="/components/com_contact/models/forms/form.xml";
		$f[]="/components/com_contact/models/forms/contact.xml";
		$f[]="/components/com_contact/models/forms/index.html";
		$f[]="/components/com_contact/models/rules/index.html";
		$f[]="/components/com_contact/models/index.html";
		$f[]="/components/com_weblinks/views/categories/tmpl/index.html";
		$f[]="/components/com_weblinks/views/categories/tmpl/default.xml";
		$f[]="/components/com_weblinks/views/categories/index.html";
		$f[]="/components/com_weblinks/views/weblink/tmpl/index.html";
		$f[]="/components/com_weblinks/views/weblink/index.html";
		$f[]="/components/com_weblinks/views/category/tmpl/index.html";
		$f[]="/components/com_weblinks/views/category/tmpl/default.xml";
		$f[]="/components/com_weblinks/views/category/metadata.xml";
		$f[]="/components/com_weblinks/views/category/index.html";
		$f[]="/components/com_weblinks/views/form/tmpl/index.html";
		$f[]="/components/com_weblinks/views/form/tmpl/edit.xml";
		$f[]="/components/com_weblinks/views/form/metadata.xml";
		$f[]="/components/com_weblinks/views/form/index.html";
		$f[]="/components/com_weblinks/views/index.html";
		$f[]="/components/com_weblinks/metadata.xml";
		$f[]="/components/com_weblinks/helpers/index.html";
		$f[]="/components/com_weblinks/controllers/index.html";
		$f[]="/components/com_weblinks/index.html";
		$f[]="/components/com_weblinks/models/forms/weblink.xml";
		$f[]="/components/com_weblinks/models/forms/index.html";
		$f[]="/components/com_weblinks/models/index.html";
		$f[]="/components/com_banners/helpers/index.html";
		$f[]="/components/com_banners/index.html";
		$f[]="/components/com_banners/models/index.html";
		$f[]="/components/com_users/views/remind/tmpl/index.html";
		$f[]="/components/com_users/views/remind/tmpl/default.xml";
		$f[]="/components/com_users/views/remind/metadata.xml";
		$f[]="/components/com_users/views/remind/index.html";
		$f[]="/components/com_users/views/login/tmpl/index.html";
		$f[]="/components/com_users/views/login/tmpl/default.xml";
		$f[]="/components/com_users/views/login/metadata.xml";
		$f[]="/components/com_users/views/login/index.html";
		$f[]="/components/com_users/views/profile/tmpl/index.html";
		$f[]="/components/com_users/views/profile/tmpl/edit.xml";
		$f[]="/components/com_users/views/profile/tmpl/default.xml";
		$f[]="/components/com_users/views/profile/metadata.xml";
		$f[]="/components/com_users/views/profile/index.html";
		$f[]="/components/com_users/views/registration/tmpl/index.html";
		$f[]="/components/com_users/views/registration/tmpl/default.xml";
		$f[]="/components/com_users/views/registration/metadata.xml";
		$f[]="/components/com_users/views/registration/index.html";
		$f[]="/components/com_users/views/index.html";
		$f[]="/components/com_users/views/reset/tmpl/index.html";
		$f[]="/components/com_users/views/reset/tmpl/default.xml";
		$f[]="/components/com_users/views/reset/metadata.xml";
		$f[]="/components/com_users/views/reset/index.html";
		$f[]="/components/com_users/metadata.xml";
		$f[]="/components/com_users/helpers/index.html";
		$f[]="/components/com_users/helpers/html/index.html";
		$f[]="/components/com_users/controllers/index.html";
		$f[]="/components/com_users/index.html";
		$f[]="/components/com_users/models/forms/registration.xml";
		$f[]="/components/com_users/models/forms/reset_request.xml";
		$f[]="/components/com_users/models/forms/frontend_admin.xml";
		$f[]="/components/com_users/models/forms/reset_confirm.xml";
		$f[]="/components/com_users/models/forms/reset_complete.xml";
		$f[]="/components/com_users/models/forms/remind.xml";
		$f[]="/components/com_users/models/forms/login.xml";
		$f[]="/components/com_users/models/forms/frontend.xml";
		$f[]="/components/com_users/models/forms/profile.xml";
		$f[]="/components/com_users/models/forms/index.html";
		$f[]="/components/com_users/models/index.html";
		$f[]="/components/com_mailto/mailto.xml";
		$f[]="/components/com_mailto/views/mailto/tmpl/index.html";
		$f[]="/components/com_mailto/views/mailto/metadata.xml";
		$f[]="/components/com_mailto/views/mailto/index.html";
		$f[]="/components/com_mailto/views/sent/tmpl/index.html";
		$f[]="/components/com_mailto/views/sent/metadata.xml";
		$f[]="/components/com_mailto/views/sent/index.html";
		$f[]="/components/com_mailto/views/index.html";
		$f[]="/components/com_mailto/index.html";
		$f[]="/components/com_content/views/categories/tmpl/index.html";
		$f[]="/components/com_content/views/categories/tmpl/default.xml";
		$f[]="/components/com_content/views/categories/index.html";
		$f[]="/components/com_content/views/article/tmpl/index.html";
		$f[]="/components/com_content/views/article/tmpl/default.xml";
		$f[]="/components/com_content/views/article/metadata.xml";
		$f[]="/components/com_content/views/article/index.html";
		$f[]="/components/com_content/views/featured/tmpl/index.html";
		$f[]="/components/com_content/views/featured/tmpl/default.xml";
		$f[]="/components/com_content/views/featured/metadata.xml";
		$f[]="/components/com_content/views/featured/index.html";
		$f[]="/components/com_content/views/category/tmpl/blog.xml";
		$f[]="/components/com_content/views/category/tmpl/index.html";
		$f[]="/components/com_content/views/category/tmpl/default.xml";
		$f[]="/components/com_content/views/category/metadata.xml";
		$f[]="/components/com_content/views/category/index.html";
		$f[]="/components/com_content/views/form/tmpl/index.html";
		$f[]="/components/com_content/views/form/tmpl/edit.xml";
		$f[]="/components/com_content/views/form/metadata.xml";
		$f[]="/components/com_content/views/form/index.html";
		$f[]="/components/com_content/views/archive/tmpl/index.html";
		$f[]="/components/com_content/views/archive/tmpl/default.xml";
		$f[]="/components/com_content/views/archive/metadata.xml";
		$f[]="/components/com_content/views/archive/index.html";
		$f[]="/components/com_content/views/index.html";
		$f[]="/components/com_content/metadata.xml";
		$f[]="/components/com_content/helpers/index.html";
		$f[]="/components/com_content/controllers/index.html";
		$f[]="/components/com_content/index.html";
		$f[]="/components/com_content/models/forms/article.xml";
		$f[]="/components/com_content/models/forms/index.html";
		$f[]="/components/com_content/models/index.html";
		$f[]="/components/com_search/views/index.html";
		$f[]="/components/com_search/views/search/tmpl/index.html";
		$f[]="/components/com_search/views/search/tmpl/default.xml";
		$f[]="/components/com_search/views/search/metadata.xml";
		$f[]="/components/com_search/views/search/index.html";
		$f[]="/components/com_search/index.html";
		$f[]="/components/com_search/models/index.html";
		$f[]="/components/com_newsfeeds/views/categories/tmpl/index.html";
		$f[]="/components/com_newsfeeds/views/categories/tmpl/default.xml";
		$f[]="/components/com_newsfeeds/views/categories/index.html";
		$f[]="/components/com_newsfeeds/views/newsfeed/tmpl/index.html";
		$f[]="/components/com_newsfeeds/views/newsfeed/tmpl/default.xml";
		$f[]="/components/com_newsfeeds/views/newsfeed/metadata.xml";
		$f[]="/components/com_newsfeeds/views/newsfeed/index.html";
		$f[]="/components/com_newsfeeds/views/category/tmpl/index.html";
		$f[]="/components/com_newsfeeds/views/category/tmpl/default.xml";
		$f[]="/components/com_newsfeeds/views/category/metadata.xml";
		$f[]="/components/com_newsfeeds/views/category/index.html";
		$f[]="/components/com_newsfeeds/views/index.html";
		$f[]="/components/com_newsfeeds/metadata.xml";
		$f[]="/components/com_newsfeeds/helpers/index.html";
		$f[]="/components/com_newsfeeds/index.html";
		$f[]="/components/com_newsfeeds/models/index.html";
		$f[]="/components/com_wrapper/views/wrapper/tmpl/index.html";
		$f[]="/components/com_wrapper/views/wrapper/tmpl/default.xml";
		$f[]="/components/com_wrapper/views/wrapper/metadata.xml";
		$f[]="/components/com_wrapper/views/wrapper/index.html";
		$f[]="/components/com_wrapper/views/index.html";
		$f[]="/components/com_wrapper/wrapper.xml";
		$f[]="/components/com_wrapper/metadata.xml";
		$f[]="/components/com_wrapper/index.html";
		$f[]="/components/index.html";
		$f[]="/components/com_media/helpers/index.html";
		$f[]="/components/com_media/index.html";
		$f[]="/logs/index.html";
		$f[]="/modules/mod_related_items/tmpl/index.html";
		$f[]="/modules/mod_related_items/mod_related_items.xml";
		$f[]="/modules/mod_related_items/index.html";
		$f[]="/modules/mod_articles_category/tmpl/index.html";
		$f[]="/modules/mod_articles_category/index.html";
		$f[]="/modules/mod_articles_category/mod_articles_category.xml";
		$f[]="/modules/mod_banners/tmpl/index.html";
		$f[]="/modules/mod_banners/mod_banners.xml";
		$f[]="/modules/mod_banners/index.html";
		$f[]="/modules/mod_weblinks/tmpl/index.html";
		$f[]="/modules/mod_weblinks/mod_weblinks.xml";
		$f[]="/modules/mod_weblinks/index.html";
		$f[]="/modules/mod_articles_latest/tmpl/index.html";
		$f[]="/modules/mod_articles_latest/mod_articles_latest.xml";
		$f[]="/modules/mod_articles_latest/index.html";
		$f[]="/modules/mod_articles_news/tmpl/index.html";
		$f[]="/modules/mod_articles_news/mod_articles_news.xml";
		$f[]="/modules/mod_articles_news/index.html";
		$f[]="/modules/mod_syndicate/tmpl/index.html";
		$f[]="/modules/mod_syndicate/mod_syndicate.xml";
		$f[]="/modules/mod_syndicate/index.html";
		$f[]="/modules/mod_breadcrumbs/tmpl/index.html";
		$f[]="/modules/mod_breadcrumbs/mod_breadcrumbs.xml";
		$f[]="/modules/mod_breadcrumbs/index.html";
		$f[]="/modules/mod_random_image/tmpl/index.html";
		$f[]="/modules/mod_random_image/mod_random_image.xml";
		$f[]="/modules/mod_random_image/index.html";
		$f[]="/modules/mod_articles_categories/tmpl/index.html";
		$f[]="/modules/mod_articles_categories/mod_articles_categories.xml";
		$f[]="/modules/mod_articles_categories/index.html";
		$f[]="/modules/mod_articles_archive/tmpl/index.html";
		$f[]="/modules/mod_articles_archive/mod_articles_archive.xml";
		$f[]="/modules/mod_articles_archive/index.html";
		$f[]="/modules/mod_custom/tmpl/index.html";
		$f[]="/modules/mod_custom/mod_custom.xml";
		$f[]="/modules/mod_custom/index.html";
		$f[]="/modules/mod_wrapper/tmpl/index.html";
		$f[]="/modules/mod_wrapper/mod_wrapper.xml";
		$f[]="/modules/mod_wrapper/index.html";
		$f[]="/modules/mod_search/tmpl/index.html";
		$f[]="/modules/mod_search/mod_search.xml";
		$f[]="/modules/mod_search/index.html";
		$f[]="/modules/mod_whosonline/tmpl/index.html";
		$f[]="/modules/mod_whosonline/mod_whosonline.xml";
		$f[]="/modules/mod_whosonline/index.html";
		$f[]="/modules/mod_users_latest/tmpl/index.html";
		$f[]="/modules/mod_users_latest/mod_users_latest.xml";
		$f[]="/modules/mod_users_latest/index.html";
		$f[]="/modules/mod_footer/tmpl/index.html";
		$f[]="/modules/mod_footer/mod_footer.xml";
		$f[]="/modules/mod_footer/index.html";
		$f[]="/modules/mod_articles_popular/tmpl/index.html";
		$f[]="/modules/mod_articles_popular/mod_articles_popular.xml";
		$f[]="/modules/mod_articles_popular/index.html";
		$f[]="/modules/mod_menu/tmpl/index.html";
		$f[]="/modules/mod_menu/mod_menu.xml";
		$f[]="/modules/mod_menu/index.html";
		$f[]="/modules/mod_languages/tmpl/index.html";
		$f[]="/modules/mod_languages/mod_languages.xml";
		$f[]="/modules/mod_languages/index.html";
		$f[]="/modules/index.html";
		$f[]="/modules/mod_login/tmpl/index.html";
		$f[]="/modules/mod_login/mod_login.xml";
		$f[]="/modules/mod_login/index.html";
		$f[]="/modules/mod_feed/tmpl/index.html";
		$f[]="/modules/mod_feed/mod_feed.xml";
		$f[]="/modules/mod_feed/index.html";
		$f[]="/modules/mod_stats/tmpl/index.html";
		$f[]="/modules/mod_stats/mod_stats.xml";
		$f[]="/modules/mod_stats/index.html";
		
		while (list ($none, $xpath) = each ($f) ){
			$testfile="$path$xpath";
			if(!is_file($testfile)){
				if(!is_dir($testfile)){
					echo "echo Starting......: Joomla 1.7x $testfile No such file or directory\n";
					return false;
				}
			}
			
		}
		
		return true;		
		
	}
	
	private function testTables(){
		
		$sql="SHOW TABLES";
		$q=new mysql();
		
		
				$d["jos_assets"]=true;
				$d["jos_associations"]=true;
				$d["jos_banner_clients"]=true;
				$d["jos_banner_tracks"]=true;
				$d["jos_banners"]=true;
				$d["jos_categories"]=true;
				$d["jos_contact_details"]=true;
				$d["jos_content"]=true;
				$d["jos_content_frontpage"]=true;
				$d["jos_content_rating"]=true;
				$d["jos_core_log_searches"]=true;
				$d["jos_extensions"]=true;
				$d["jos_languages"]=true;
				$d["jos_menu"]=true;
				$d["jos_menu_types"]=true;
				$d["jos_messages"]=true;
				$d["jos_messages_cfg"]=true;
				$d["jos_modules"]=true;
				$d["jos_modules_menu"]=true;
				$d["jos_newsfeeds"]=true;
				$d["jos_redirect_links"]=true;
				$d["jos_schemas"]=true;
				$d["jos_session"]=true;
				$d["jos_template_styles"]=true;
				$d["jos_update_categories"]=true;
				$d["jos_update_sites"]=true;
				$d["jos_update_sites_extensions"]=true;
				$d["jos_updates"]=true;
				$d["jos_user_profiles"]=true;
				$d["jos_user_usergroup_map"]=true;
				$d["jos_usergroups"]=true;
				$d["jos_users"]=true;
				$d["jos_viewlevels"]=true;
				$d["jos_weblinks"]=true;

				$q=new mysql();
				while (list ($table, $none) = each ($d) ){
					if(!$q->TABLE_EXISTS($table, $this->mysql_database,true)){return false;}
					
				}
				
				return true;
		
	}
	
	
	private function builddatabase(){
		$q=new mysql();
		if(!$q->TABLE_EXISTS("jos_assets", $this->mysql_database)){
		$sql="
			CREATE TABLE IF NOT EXISTS `jos_assets` (`id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',`parent_id` int(11) NOT NULL DEFAULT '0' COMMENT 'Nested set parent.',`lft` int(11) NOT NULL DEFAULT '0' COMMENT 'Nested set lft.',`rgt` int(11) NOT NULL DEFAULT '0' COMMENT 'Nested set rgt.',`level` int(10) unsigned NOT NULL COMMENT 'The cached level in the nested tree.',`name` varchar(50) NOT NULL COMMENT 'The unique name for the asset.\n',`title` varchar(100) NOT NULL COMMENT 'The descriptive title for the asset.',`rules` varchar(5120) NOT NULL COMMENT 'JSON encoded access control.',PRIMARY KEY (`id`),UNIQUE KEY `idx_asset_name` (`name`),KEY `idx_lft_rgt` (`lft`,`rgt`),KEY `idx_parent_id` (`parent_id`))";		
			$q->QUERY_SQL($sql,$this->mysql_database);
			if($q->ok){
					$sql="INSERT INTO `jos_assets` (`id`, `parent_id`, `lft`, `rgt`, `level`, `name`, `title`, `rules`) VALUES
					(1, 0, 1, 414, 0, 'root.1', 'Root Asset', '{\"core.login.site\":{\"6\":1,\"2\":1},\"core.login.admin\":{\"6\":1},\"core.login.offline\":{\"6\":1},\"core.admin\":{\"8\":1},\"core.manage\":{\"7\":1},\"core.create\":{\"6\":1,\"3\":1},\"core.delete\":{\"6\":1},\"core.edit\":{\"6\":1,\"4\":1},\"core.edit.state\":{\"6\":1,\"5\":1},\"core.edit.own\":{\"6\":1,\"3\":1}}'),
					(2, 1, 1, 2, 1, 'com_admin', 'com_admin', '{}'),
					(3, 1, 3, 6, 1, 'com_banners', 'com_banners', '{\"core.admin\":{\"7\":1},\"core.manage\":{\"6\":1},\"core.create\":[],\"core.delete\":[],\"core.edit\":[],\"core.edit.state\":[]}'),
					(4, 1, 7, 8, 1, 'com_cache', 'com_cache', '{\"core.admin\":{\"7\":1},\"core.manage\":{\"7\":1}}'),
					(5, 1, 9, 10, 1, 'com_checkin', 'com_checkin', '{\"core.admin\":{\"7\":1},\"core.manage\":{\"7\":1}}'),
					(6, 1, 11, 12, 1, 'com_config', 'com_config', '{}'),
					(7, 1, 13, 16, 1, 'com_contact', 'com_contact', '{\"core.admin\":{\"7\":1},\"core.manage\":{\"6\":1},\"core.create\":[],\"core.delete\":[],\"core.edit\":[],\"core.edit.state\":[],\"core.edit.own\":[]}'),
					(8, 1, 17, 20, 1, 'com_content', 'com_content', '{\"core.admin\":{\"7\":1},\"core.manage\":{\"6\":1},\"core.create\":{\"3\":1},\"core.delete\":[],\"core.edit\":{\"4\":1},\"core.edit.state\":{\"5\":1},\"core.edit.own\":[]}'),
					(9, 1, 21, 22, 1, 'com_cpanel', 'com_cpanel', '{}'),
					(10, 1, 23, 24, 1, 'com_installer', 'com_installer', '{\"core.admin\":{\"7\":1},\"core.manage\":{\"7\":1},\"core.delete\":[],\"core.edit.state\":[]}'),
					(11, 1, 25, 26, 1, 'com_languages', 'com_languages', '{\"core.admin\":{\"7\":1},\"core.manage\":[],\"core.create\":[],\"core.delete\":[],\"core.edit\":[],\"core.edit.state\":[]}'),
					(12, 1, 27, 28, 1, 'com_login', 'com_login', '{}'),
					(13, 1, 29, 30, 1, 'com_mailto', 'com_mailto', '{}'),
					(14, 1, 31, 32, 1, 'com_massmail', 'com_massmail', '{}'),
					(15, 1, 33, 34, 1, 'com_media', 'com_media', '{\"core.admin\":{\"7\":1},\"core.manage\":{\"6\":1},\"core.create\":{\"3\":1},\"core.delete\":{\"5\":1}}'),
					(16, 1, 35, 36, 1, 'com_menus', 'com_menus', '{\"core.admin\":{\"7\":1},\"core.manage\":[],\"core.create\":[],\"core.delete\":[],\"core.edit\":[],\"core.edit.state\":[]}'),
					(17, 1, 37, 38, 1, 'com_messages', 'com_messages', '{\"core.admin\":{\"7\":1},\"core.manage\":{\"7\":1}}'),
					(18, 1, 39, 40, 1, 'com_modules', 'com_modules', '{\"core.admin\":{\"7\":1},\"core.manage\":[],\"core.create\":[],\"core.delete\":[],\"core.edit\":[],\"core.edit.state\":[]}'),
					(19, 1, 41, 44, 1, 'com_newsfeeds', 'com_newsfeeds', '{\"core.admin\":{\"7\":1},\"core.manage\":{\"6\":1},\"core.create\":[],\"core.delete\":[],\"core.edit\":[],\"core.edit.state\":[],\"core.edit.own\":[]}'),
					(20, 1, 45, 46, 1, 'com_plugins', 'com_plugins', '{\"core.admin\":{\"7\":1},\"core.manage\":[],\"core.edit\":[],\"core.edit.state\":[]}'),
					(21, 1, 47, 48, 1, 'com_redirect', 'com_redirect', '{\"core.admin\":{\"7\":1},\"core.manage\":[]}'),
					(22, 1, 49, 50, 1, 'com_search', 'com_search', '{\"core.admin\":{\"7\":1},\"core.manage\":{\"6\":1}}'),
					(23, 1, 51, 52, 1, 'com_templates', 'com_templates', '{\"core.admin\":{\"7\":1},\"core.manage\":[],\"core.create\":[],\"core.delete\":[],\"core.edit\":[],\"core.edit.state\":[]}'),
					(24, 1, 53, 54, 1, 'com_users', 'com_users', '{\"core.admin\":{\"7\":1},\"core.manage\":[],\"core.create\":[],\"core.delete\":[],\"core.edit\":[],\"core.edit.own\":{\"6\":1},\"core.edit.state\":[]}'),
					(25, 1, 55, 58, 1, 'com_weblinks', 'com_weblinks', '{\"core.admin\":{\"7\":1},\"core.manage\":{\"6\":1},\"core.create\":{\"3\":1},\"core.delete\":[],\"core.edit\":{\"4\":1},\"core.edit.state\":{\"5\":1},\"core.edit.own\":[]}'),
					(26, 1, 59, 60, 1, 'com_wrapper', 'com_wrapper', '{}'),
					(27, 8, 18, 19, 2, 'com_content.category.2', 'Uncategorised', '{\"core.create\":[],\"core.delete\":[],\"core.edit\":[],\"core.edit.state\":[],\"core.edit.own\":[]}'),
					(28, 3, 4, 5, 2, 'com_banners.category.3', 'Uncategorised', '{\"core.create\":[],\"core.delete\":[],\"core.edit\":[],\"core.edit.state\":[]}'),
					(29, 7, 14, 15, 2, 'com_contact.category.4', 'Uncategorised', '{\"core.create\":[],\"core.delete\":[],\"core.edit\":[],\"core.edit.state\":[],\"core.edit.own\":[]}'),
					(30, 19, 42, 43, 2, 'com_newsfeeds.category.5', 'Uncategorised', '{\"core.create\":[],\"core.delete\":[],\"core.edit\":[],\"core.edit.state\":[],\"core.edit.own\":[]}'),
					(31, 25, 56, 57, 2, 'com_weblinks.category.6', 'Uncategorised', '{\"core.create\":[],\"core.delete\":[],\"core.edit\":[],\"core.edit.state\":[],\"core.edit.own\":[]}');";
					$q->QUERY_SQL($sql,$this->mysql_database);
								
				}
		
		
		}
		
		
		if(!$q->TABLE_EXISTS("jos_associations", $this->mysql_database)){
		$sql="CREATE TABLE IF NOT EXISTS `jos_associations` (
		  `id` varchar(50) NOT NULL COMMENT 'A reference to the associated item.',
		  `context` varchar(50) NOT NULL COMMENT 'The context of the associated item.',
		  `key` char(32) NOT NULL COMMENT 'The key for the association computed from an md5 on associated ids.',
		  PRIMARY KEY (`context`,`id`),
		  KEY `idx_key` (`key`))";
		$q->QUERY_SQL($sql,$this->mysql_database);
		}
		
		

		if(!$q->TABLE_EXISTS("jos_banners", $this->mysql_database)){
		$sql="CREATE TABLE IF NOT EXISTS `jos_banners` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `cid` int(11) NOT NULL DEFAULT '0',
			  `type` int(11) NOT NULL DEFAULT '0',
			  `name` varchar(255) NOT NULL DEFAULT '',
			  `alias` varchar(255) NOT NULL DEFAULT '',
			  `imptotal` int(11) NOT NULL DEFAULT '0',
			  `impmade` int(11) NOT NULL DEFAULT '0',
			  `clicks` int(11) NOT NULL DEFAULT '0',
			  `clickurl` varchar(200) NOT NULL DEFAULT '',
			  `state` tinyint(3) NOT NULL DEFAULT '0',
			  `catid` int(10) unsigned NOT NULL DEFAULT '0',
			  `description` text NOT NULL,
			  `custombannercode` varchar(2048) NOT NULL,
			  `sticky` tinyint(1) unsigned NOT NULL DEFAULT '0',
			  `ordering` int(11) NOT NULL DEFAULT '0',
			  `metakey` text NOT NULL,
			  `params` text NOT NULL,
			  `own_prefix` tinyint(1) NOT NULL DEFAULT '0',
			  `metakey_prefix` varchar(255) NOT NULL DEFAULT '',
			  `purchase_type` tinyint(4) NOT NULL DEFAULT '-1',
			  `track_clicks` tinyint(4) NOT NULL DEFAULT '-1',
			  `track_impressions` tinyint(4) NOT NULL DEFAULT '-1',
			  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
			  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			  `reset` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			  `language` char(7) NOT NULL DEFAULT '',
			  PRIMARY KEY (`id`),
			  KEY `idx_state` (`state`),
			  KEY `idx_own_prefix` (`own_prefix`),
			  KEY `idx_metakey_prefix` (`metakey_prefix`),
			  KEY `idx_banner_catid` (`catid`),
			  KEY `idx_language` (`language`))";
		$q->QUERY_SQL($sql,$this->mysql_database);
		}	

		if(!$q->TABLE_EXISTS("jos_banner_tracks", $this->mysql_database)){
		$sql="CREATE TABLE IF NOT EXISTS `jos_banner_tracks` (
			  `track_date` datetime NOT NULL,
			  `track_type` int(10) unsigned NOT NULL,
			  `banner_id` int(10) unsigned NOT NULL,
			  `count` int(10) unsigned NOT NULL DEFAULT '0',
			  PRIMARY KEY (`track_date`,`track_type`,`banner_id`),
			  KEY `idx_track_date` (`track_date`),
			  KEY `idx_track_type` (`track_type`),
			  KEY `idx_banner_id` (`banner_id`)
			)";
		$q->QUERY_SQL($sql,$this->mysql_database);
		}			
		
	
		if(!$q->TABLE_EXISTS("jos_categories", $this->mysql_database)){
		$sql="CREATE TABLE IF NOT EXISTS `jos_categories` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
			  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
			  `lft` int(11) NOT NULL DEFAULT '0',
			  `rgt` int(11) NOT NULL DEFAULT '0',
			  `level` int(10) unsigned NOT NULL DEFAULT '0',
			  `path` varchar(255) NOT NULL DEFAULT '',
			  `extension` varchar(50) NOT NULL DEFAULT '',
			  `title` varchar(255) NOT NULL,
			  `alias` varchar(255) NOT NULL DEFAULT '',
			  `note` varchar(255) NOT NULL DEFAULT '',
			  `description` varchar(5120) NOT NULL DEFAULT '',
			  `published` tinyint(1) NOT NULL DEFAULT '0',
			  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
			  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			  `access` tinyint(3) unsigned NOT NULL DEFAULT '0',
			  `params` text NOT NULL,
			  `metadesc` varchar(1024) NOT NULL COMMENT 'The meta description for the page.',
			  `metakey` varchar(1024) NOT NULL COMMENT 'The meta keywords for the page.',
			  `metadata` varchar(2048) NOT NULL COMMENT 'JSON encoded metadata properties.',
			  `created_user_id` int(10) unsigned NOT NULL DEFAULT '0',
			  `created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			  `modified_user_id` int(10) unsigned NOT NULL DEFAULT '0',
			  `modified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			  `hits` int(10) unsigned NOT NULL DEFAULT '0',
			  `language` char(7) NOT NULL,
			  PRIMARY KEY (`id`),
			  KEY `cat_idx` (`extension`,`published`,`access`),
			  KEY `idx_access` (`access`),
			  KEY `idx_checkout` (`checked_out`),
			  KEY `idx_path` (`path`),
			  KEY `idx_left_right` (`lft`,`rgt`),
			  KEY `idx_alias` (`alias`),
			  KEY `idx_language` (`language`)
			)";
		$q->QUERY_SQL($sql,$this->mysql_database);
		
		$sql="INSERT INTO `jos_categories` (`id`, `asset_id`, `parent_id`, `lft`, `rgt`, `level`, `path`, `extension`, `title`, `alias`, `note`, `description`, `published`, `checked_out`, `checked_out_time`, `access`, `params`, `metadesc`, `metakey`, `metadata`, `created_user_id`, `created_time`, `modified_user_id`, `modified_time`, `hits`, `language`) VALUES
(1, 0, 0, 0, 11, 0, '', 'system', 'ROOT', 'root', '', '', 1, 0, '0000-00-00 00:00:00', 1, '{}', '', '', '', 0, '2009-10-18 16:07:09', 0, '0000-00-00 00:00:00', 0, '*'),
(2, 27, 1, 1, 2, 1, 'uncategorised', 'com_content', 'Uncategorised', 'uncategorised', '', '', 1, 0, '0000-00-00 00:00:00', 1, '{\"target\":\"\",\"image\":\"\"}', '', '', '{\"page_title\":\"\",\"author\":\"\",\"robots\":\"\"}', 42, '2010-06-28 13:26:37', 0, '0000-00-00 00:00:00', 0, '*'),
(3, 28, 1, 3, 4, 1, 'uncategorised', 'com_banners', 'Uncategorised', 'uncategorised', '', '', 1, 0, '0000-00-00 00:00:00', 1, '{\"target\":\"\",\"image\":\"\",\"foobar\":\"\"}', '', '', '{\"page_title\":\"\",\"author\":\"\",\"robots\":\"\"}', 42, '2010-06-28 13:27:35', 0, '0000-00-00 00:00:00', 0, '*'),
(4, 29, 1, 5, 6, 1, 'uncategorised', 'com_contact', 'Uncategorised', 'uncategorised', '', '', 1, 0, '0000-00-00 00:00:00', 1, '{\"target\":\"\",\"image\":\"\"}', '', '', '{\"page_title\":\"\",\"author\":\"\",\"robots\":\"\"}', 42, '2010-06-28 13:27:57', 0, '0000-00-00 00:00:00', 0, '*'),
(5, 30, 1, 7, 8, 1, 'uncategorised', 'com_newsfeeds', 'Uncategorised', 'uncategorised', '', '', 1, 0, '0000-00-00 00:00:00', 1, '{\"target\":\"\",\"image\":\"\"}', '', '', '{\"page_title\":\"\",\"author\":\"\",\"robots\":\"\"}', 42, '2010-06-28 13:28:15', 0, '0000-00-00 00:00:00', 0, '*'),
(6, 31, 1, 9, 10, 1, 'uncategorised', 'com_weblinks', 'Uncategorised', 'uncategorised', '', '', 1, 0, '0000-00-00 00:00:00', 1, '{\"target\":\"\",\"image\":\"\"}', '', '', '{\"page_title\":\"\",\"author\":\"\",\"robots\":\"\"}', 42, '2010-06-28 13:28:33', 0, '0000-00-00 00:00:00', 0, '*');";
		$q->QUERY_SQL($sql,$this->mysql_database);
		
		
		}

		

if(!$q->TABLE_EXISTS("jos_contact_details", $this->mysql_database)){
		$sql="CREATE TABLE IF NOT EXISTS `jos_contact_details` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `name` varchar(255) NOT NULL DEFAULT '',
			  `alias` varchar(255) NOT NULL DEFAULT '',
			  `con_position` varchar(255) DEFAULT NULL,
			  `address` text,
			  `suburb` varchar(100) DEFAULT NULL,
			  `state` varchar(100) DEFAULT NULL,
			  `country` varchar(100) DEFAULT NULL,
			  `postcode` varchar(100) DEFAULT NULL,
			  `telephone` varchar(255) DEFAULT NULL,
			  `fax` varchar(255) DEFAULT NULL,
			  `misc` mediumtext,
			  `image` varchar(255) DEFAULT NULL,
			  `imagepos` varchar(20) DEFAULT NULL,
			  `email_to` varchar(255) DEFAULT NULL,
			  `default_con` tinyint(1) unsigned NOT NULL DEFAULT '0',
			  `published` tinyint(1) NOT NULL DEFAULT '0',
			  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
			  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			  `ordering` int(11) NOT NULL DEFAULT '0',
			  `params` text NOT NULL,
			  `user_id` int(11) NOT NULL DEFAULT '0',
			  `catid` int(11) NOT NULL DEFAULT '0',
			  `access` tinyint(3) unsigned NOT NULL DEFAULT '0',
			  `mobile` varchar(255) NOT NULL DEFAULT '',
			  `webpage` varchar(255) NOT NULL DEFAULT '',
			  `sortname1` varchar(255) NOT NULL,
			  `sortname2` varchar(255) NOT NULL,
			  `sortname3` varchar(255) NOT NULL,
			  `language` char(7) NOT NULL,
			  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			  `created_by` int(10) unsigned NOT NULL DEFAULT '0',
			  `created_by_alias` varchar(255) NOT NULL DEFAULT '',
			  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			  `modified_by` int(10) unsigned NOT NULL DEFAULT '0',
			  `metakey` text NOT NULL,
			  `metadesc` text NOT NULL,
			  `metadata` text NOT NULL,
			  `featured` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Set if article is featured.',
			  `xreference` varchar(50) NOT NULL COMMENT 'A reference to enable linkages to external data sets.',
			  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			  PRIMARY KEY (`id`),
			  KEY `idx_access` (`access`),
			  KEY `idx_checkout` (`checked_out`),
			  KEY `idx_state` (`published`),
			  KEY `idx_catid` (`catid`),
			  KEY `idx_createdby` (`created_by`),
			  KEY `idx_featured_catid` (`featured`,`catid`),
			  KEY `idx_language` (`language`),
			  KEY `idx_xreference` (`xreference`)
			)";$q->QUERY_SQL($sql,$this->mysql_database);}		

if(!$q->TABLE_EXISTS("jos_content", $this->mysql_database)){
		$sql="CREATE TABLE IF NOT EXISTS `jos_content` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `title_alias` varchar(255) NOT NULL DEFAULT '',
  `introtext` mediumtext NOT NULL,
  `fulltext` mediumtext NOT NULL,
  `state` tinyint(3) NOT NULL DEFAULT '0',
  `sectionid` int(10) unsigned NOT NULL DEFAULT '0',
  `mask` int(10) unsigned NOT NULL DEFAULT '0',
  `catid` int(10) unsigned NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(10) unsigned NOT NULL DEFAULT '0',
  `created_by_alias` varchar(255) NOT NULL DEFAULT '',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `images` text NOT NULL,
  `urls` text NOT NULL,
  `attribs` varchar(5120) NOT NULL,
  `version` int(10) unsigned NOT NULL DEFAULT '1',
  `parentid` int(10) unsigned NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `metakey` text NOT NULL,
  `metadesc` text NOT NULL,
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  `metadata` text NOT NULL,
  `featured` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Set if article is featured.',
  `language` char(7) NOT NULL COMMENT 'The language code for the article.',
  `xreference` varchar(50) NOT NULL COMMENT 'A reference to enable linkages to external data sets.',
  PRIMARY KEY (`id`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_state` (`state`),
  KEY `idx_catid` (`catid`),
  KEY `idx_createdby` (`created_by`),
  KEY `idx_featured_catid` (`featured`,`catid`),
  KEY `idx_language` (`language`),
  KEY `idx_xreference` (`xreference`)
)";$q->QUERY_SQL($sql,$this->mysql_database);}

if(!$q->TABLE_EXISTS("jos_content_frontpage", $this->mysql_database)){
		$sql="CREATE TABLE IF NOT EXISTS `jos_content_frontpage` (
  `content_id` int(11) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`content_id`)
)";$q->QUERY_SQL($sql,$this->mysql_database);} 

if(!$q->TABLE_EXISTS("jos_content_rating", $this->mysql_database)){
		$sql="CREATE TABLE IF NOT EXISTS `jos_content_rating` (
  `content_id` int(11) NOT NULL DEFAULT '0',
  `rating_sum` int(10) unsigned NOT NULL DEFAULT '0',
  `rating_count` int(10) unsigned NOT NULL DEFAULT '0',
  `lastip` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`content_id`)
)";$q->QUERY_SQL($sql,$this->mysql_database);} 		
		
if(!$q->TABLE_EXISTS("jos_core_log_searches", $this->mysql_database)){
		$sql="CREATE TABLE IF NOT EXISTS `jos_core_log_searches` (
  `search_term` varchar(128) NOT NULL DEFAULT '',
  `hits` int(10) unsigned NOT NULL DEFAULT '0'
)";$q->QUERY_SQL($sql,$this->mysql_database);} 

if(!$q->TABLE_EXISTS("jos_extensions", $this->mysql_database)){
		$sql="CREATE TABLE IF NOT EXISTS `jos_extensions` (
  `extension_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `type` varchar(20) NOT NULL,
  `element` varchar(100) NOT NULL,
  `folder` varchar(100) NOT NULL,
  `client_id` tinyint(3) NOT NULL,
  `enabled` tinyint(3) NOT NULL DEFAULT '1',
  `access` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `protected` tinyint(3) NOT NULL DEFAULT '0',
  `manifest_cache` text NOT NULL,
  `params` text NOT NULL,
  `custom_data` text NOT NULL,
  `system_data` text NOT NULL,
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ordering` int(11) DEFAULT '0',
  `state` int(11) DEFAULT '0',
  PRIMARY KEY (`extension_id`),
  KEY `element_clientid` (`element`,`client_id`),
  KEY `element_folder_clientid` (`element`,`folder`,`client_id`),
  KEY `extension` (`type`,`element`,`folder`,`client_id`)
)";$q->QUERY_SQL($sql,$this->mysql_database);
		
$sql="INSERT INTO `jos_extensions` (`extension_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES
(1, 'com_mailto', 'component', 'com_mailto', '', 0, 1, 1, 1, '{\"legacy\":false,\"name\":\"com_mailto\",\"type\":\"component\",\"creationDate\":\"April 2006\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 - 2011 Open Source Matters. All rights reserved.\\t\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"COM_MAILTO_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(2, 'com_wrapper', 'component', 'com_wrapper', '', 0, 1, 1, 1, '{\"legacy\":false,\"name\":\"com_wrapper\",\"type\":\"component\",\"creationDate\":\"April 2006\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 - 2011 Open Source Matters. All rights reserved.\\n\\t\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"COM_WRAPPER_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(3, 'com_admin', 'component', 'com_admin', '', 1, 1, 1, 1, '{\"legacy\":false,\"name\":\"com_admin\",\"type\":\"component\",\"creationDate\":\"April 2006\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 - 2011 Open Source Matters. All rights reserved.\\n\\t\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"COM_ADMIN_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(4, 'com_banners', 'component', 'com_banners', '', 1, 1, 1, 0, '{\"legacy\":false,\"name\":\"com_banners\",\"type\":\"component\",\"creationDate\":\"April 2006\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 - 2011 Open Source Matters. All rights reserved.\\n\\t\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"COM_BANNERS_XML_DESCRIPTION\",\"group\":\"\"}', '{\"purchase_type\":\"3\",\"track_impressions\":\"0\",\"track_clicks\":\"0\",\"metakey_prefix\":\"\"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(5, 'com_cache', 'component', 'com_cache', '', 1, 1, 1, 1, '{\"legacy\":false,\"name\":\"com_cache\",\"type\":\"component\",\"creationDate\":\"April 2006\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 - 2011 Open Source Matters. All rights reserved.\\t\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"COM_CACHE_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(6, 'com_categories', 'component', 'com_categories', '', 1, 1, 1, 1, '{\"legacy\":false,\"name\":\"com_categories\",\"type\":\"component\",\"creationDate\":\"December 2007\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"COM_CATEGORIES_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(7, 'com_checkin', 'component', 'com_checkin', '', 1, 1, 1, 1, '{\"legacy\":false,\"name\":\"com_checkin\",\"type\":\"component\",\"creationDate\":\"Unknown\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 - 2008 Open Source Matters. All rights reserved.\\n\\t\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"COM_CHECKIN_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(8, 'com_contact', 'component', 'com_contact', '', 1, 1, 1, 0, '{\"legacy\":false,\"name\":\"com_contact\",\"type\":\"component\",\"creationDate\":\"April 2006\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 - 2011 Open Source Matters. All rights reserved.\\n\\t\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"COM_CONTACT_XML_DESCRIPTION\",\"group\":\"\"}', '{\"show_contact_category\":\"hide\",\"show_contact_list\":\"0\",\"presentation_style\":\"sliders\",\"show_name\":\"1\",\"show_position\":\"1\",\"show_email\":\"0\",\"show_street_address\":\"1\",\"show_suburb\":\"1\",\"show_state\":\"1\",\"show_postcode\":\"1\",\"show_country\":\"1\",\"show_telephone\":\"1\",\"show_mobile\":\"1\",\"show_fax\":\"1\",\"show_webpage\":\"1\",\"show_misc\":\"1\",\"show_image\":\"1\",\"image\":\"\",\"allow_vcard\":\"0\",\"show_articles\":\"0\",\"show_profile\":\"0\",\"show_links\":\"0\",\"linka_name\":\"\",\"linkb_name\":\"\",\"linkc_name\":\"\",\"linkd_name\":\"\",\"linke_name\":\"\",\"contact_icons\":\"0\",\"icon_address\":\"\",\"icon_email\":\"\",\"icon_telephone\":\"\",\"icon_mobile\":\"\",\"icon_fax\":\"\",\"icon_misc\":\"\",\"show_headings\":\"1\",\"show_position_headings\":\"1\",\"show_email_headings\":\"0\",\"show_telephone_headings\":\"1\",\"show_mobile_headings\":\"0\",\"show_fax_headings\":\"0\",\"allow_vcard_headings\":\"0\",\"show_suburb_headings\":\"1\",\"show_state_headings\":\"1\",\"show_country_headings\":\"1\",\"show_email_form\":\"1\",\"show_email_copy\":\"1\",\"banned_email\":\"\",\"banned_subject\":\"\",\"banned_text\":\"\",\"validate_session\":\"1\",\"custom_reply\":\"0\",\"redirect\":\"\",\"show_category_crumb\":\"0\",\"metakey\":\"\",\"metadesc\":\"\",\"robots\":\"\",\"author\":\"\",\"rights\":\"\",\"xreference\":\"\"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(9, 'com_cpanel', 'component', 'com_cpanel', '', 1, 1, 1, 1, '{\"legacy\":false,\"name\":\"com_cpanel\",\"type\":\"component\",\"creationDate\":\"April 2006\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"COM_CPANEL_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(10, 'com_installer', 'component', 'com_installer', '', 1, 1, 1, 1, '{\"legacy\":false,\"name\":\"com_installer\",\"type\":\"component\",\"creationDate\":\"April 2006\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 - 2011 Open Source Matters. All rights reserved.\\t\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"COM_INSTALLER_XML_DESCRIPTION\",\"group\":\"\"}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(11, 'com_languages', 'component', 'com_languages', '', 1, 1, 1, 1, '{\"legacy\":false,\"name\":\"com_languages\",\"type\":\"component\",\"creationDate\":\"2006\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 - 2011 Open Source Matters. All rights reserved.\\n\\t\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"COM_LANGUAGES_XML_DESCRIPTION\",\"group\":\"\"}', '{\"administrator\":\"en-GB\",\"site\":\"en-GB\"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(12, 'com_login', 'component', 'com_login', '', 1, 1, 1, 1, '{\"legacy\":false,\"name\":\"com_login\",\"type\":\"component\",\"creationDate\":\"April 2006\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 - 2011 Open Source Matters. All rights reserved.\\t\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"COM_LOGIN_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(13, 'com_media', 'component', 'com_media', '', 1, 1, 0, 1, '{\"legacy\":false,\"name\":\"com_media\",\"type\":\"component\",\"creationDate\":\"April 2006\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 - 2011 Open Source Matters. All rights reserved.\\t\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"COM_MEDIA_XML_DESCRIPTION\",\"group\":\"\"}', '{\"upload_extensions\":\"bmp,csv,doc,gif,ico,jpg,jpeg,odg,odp,ods,odt,pdf,png,ppt,swf,txt,xcf,xls,BMP,CSV,DOC,GIF,ICO,JPG,JPEG,ODG,ODP,ODS,ODT,PDF,PNG,PPT,SWF,TXT,XCF,XLS\",\"upload_maxsize\":\"10\",\"file_path\":\"images\",\"image_path\":\"images\",\"restrict_uploads\":\"1\",\"allowed_media_usergroup\":\"3\",\"check_mime\":\"1\",\"image_extensions\":\"bmp,gif,jpg,png\",\"ignore_extensions\":\"\",\"upload_mime\":\"image\\/jpeg,image\\/gif,image\\/png,image\\/bmp,application\\/x-shockwave-flash,application\\/msword,application\\/excel,application\\/pdf,application\\/powerpoint,text\\/plain,application\\/x-zip\",\"upload_mime_illegal\":\"text\\/html\",\"enable_flash\":\"0\"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(14, 'com_menus', 'component', 'com_menus', '', 1, 1, 1, 1, '{\"legacy\":false,\"name\":\"com_menus\",\"type\":\"component\",\"creationDate\":\"April 2006\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 - 2011 Open Source Matters. All rights reserved.\\t\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"COM_MENUS_XML_DESCRIPTION\",\"group\":\"\"}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(15, 'com_messages', 'component', 'com_messages', '', 1, 1, 1, 1, '{\"legacy\":false,\"name\":\"com_messages\",\"type\":\"component\",\"creationDate\":\"April 2006\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 - 2011 Open Source Matters. All rights reserved.\\t\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"COM_MESSAGES_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(16, 'com_modules', 'component', 'com_modules', '', 1, 1, 1, 1, '{\"legacy\":false,\"name\":\"com_modules\",\"type\":\"component\",\"creationDate\":\"April 2006\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 - 2011 Open Source Matters. All rights reserved.\\t\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"COM_MODULES_XML_DESCRIPTION\",\"group\":\"\"}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(17, 'com_newsfeeds', 'component', 'com_newsfeeds', '', 1, 1, 1, 0, '{\"legacy\":false,\"name\":\"com_newsfeeds\",\"type\":\"component\",\"creationDate\":\"April 2006\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 - 2011 Open Source Matters. All rights reserved.\\t\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"COM_NEWSFEEDS_XML_DESCRIPTION\",\"group\":\"\"}', '{\"show_feed_image\":\"1\",\"show_feed_description\":\"1\",\"show_item_description\":\"1\",\"feed_word_count\":\"0\",\"show_headings\":\"1\",\"show_name\":\"1\",\"show_articles\":\"0\",\"show_link\":\"1\",\"show_description\":\"1\",\"show_description_image\":\"1\",\"display_num\":\"\",\"show_pagination_limit\":\"1\",\"show_pagination\":\"1\",\"show_pagination_results\":\"1\",\"show_cat_items\":\"1\"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(18, 'com_plugins', 'component', 'com_plugins', '', 1, 1, 1, 1, '{\"legacy\":false,\"name\":\"com_plugins\",\"type\":\"component\",\"creationDate\":\"April 2006\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 - 2011 Open Source Matters. All rights reserved.\\t\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"COM_PLUGINS_XML_DESCRIPTION\",\"group\":\"\"}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(19, 'com_search', 'component', 'com_search', '', 1, 1, 1, 1, '{\"legacy\":false,\"name\":\"com_search\",\"type\":\"component\",\"creationDate\":\"April 2006\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 - 2011 Open Source Matters. All rights reserved.\\n\\t\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"COM_SEARCH_XML_DESCRIPTION\",\"group\":\"\"}', '{\"enabled\":\"0\",\"show_date\":\"1\"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(20, 'com_templates', 'component', 'com_templates', '', 1, 1, 1, 1, '{\"legacy\":false,\"name\":\"com_templates\",\"type\":\"component\",\"creationDate\":\"April 2006\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 - 2011 Open Source Matters. All rights reserved.\\t\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"COM_TEMPLATES_XML_DESCRIPTION\",\"group\":\"\"}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(21, 'com_weblinks', 'component', 'com_weblinks', '', 1, 1, 1, 0, '{\"legacy\":false,\"name\":\"com_weblinks\",\"type\":\"component\",\"creationDate\":\"April 2006\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 - 2011 Open Source Matters. All rights reserved.\\n\\t\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"COM_WEBLINKS_XML_DESCRIPTION\",\"group\":\"\"}', '{\"show_comp_description\":\"1\",\"comp_description\":\"\",\"show_link_hits\":\"1\",\"show_link_description\":\"1\",\"show_other_cats\":\"0\",\"show_headings\":\"0\",\"show_numbers\":\"0\",\"show_report\":\"1\",\"count_clicks\":\"1\",\"target\":\"0\",\"link_icons\":\"\"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(22, 'com_content', 'component', 'com_content', '', 1, 1, 0, 1, '{\"legacy\":false,\"name\":\"com_content\",\"type\":\"component\",\"creationDate\":\"April 2006\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 - 2011 Open Source Matters. All rights reserved.\\t\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"COM_CONTENT_XML_DESCRIPTION\",\"group\":\"\"}', '{\"article_layout\":\"_:default\",\"show_title\":\"1\",\"link_titles\":\"1\",\"show_intro\":\"1\",\"show_category\":\"1\",\"link_category\":\"1\",\"show_parent_category\":\"0\",\"link_parent_category\":\"0\",\"show_author\":\"1\",\"link_author\":\"0\",\"show_create_date\":\"0\",\"show_modify_date\":\"0\",\"show_publish_date\":\"1\",\"show_item_navigation\":\"1\",\"show_vote\":\"0\",\"show_readmore\":\"1\",\"show_readmore_title\":\"1\",\"readmore_limit\":\"100\",\"show_icons\":\"1\",\"show_print_icon\":\"1\",\"show_email_icon\":\"1\",\"show_hits\":\"1\",\"show_noauth\":\"0\",\"category_layout\":\"_:blog\",\"show_category_title\":\"0\",\"show_description\":\"0\",\"show_description_image\":\"0\",\"maxLevel\":\"1\",\"show_empty_categories\":\"0\",\"show_no_articles\":\"1\",\"show_subcat_desc\":\"1\",\"show_cat_num_articles\":\"0\",\"show_base_description\":\"1\",\"maxLevelcat\":\"-1\",\"show_empty_categories_cat\":\"0\",\"show_subcat_desc_cat\":\"1\",\"show_cat_num_articles_cat\":\"1\",\"num_leading_articles\":\"1\",\"num_intro_articles\":\"4\",\"num_columns\":\"2\",\"num_links\":\"4\",\"multi_column_order\":\"0\",\"orderby_pri\":\"order\",\"orderby_sec\":\"rdate\",\"order_date\":\"published\",\"show_pagination_limit\":\"1\",\"filter_field\":\"hide\",\"show_headings\":\"1\",\"list_show_date\":\"0\",\"date_format\":\"\",\"list_show_hits\":\"1\",\"list_show_author\":\"1\",\"show_pagination\":\"2\",\"show_pagination_results\":\"1\",\"show_feed_link\":\"1\",\"feed_summary\":\"0\",\"filters\":{\"1\":{\"filter_type\":\"BL\",\"filter_tags\":\"\",\"filter_attributes\":\"\"},\"6\":{\"filter_type\":\"BL\",\"filter_tags\":\"\",\"filter_attributes\":\"\"},\"7\":{\"filter_type\":\"BL\",\"filter_tags\":\"\",\"filter_attributes\":\"\"},\"2\":{\"filter_type\":\"BL\",\"filter_tags\":\"\",\"filter_attributes\":\"\"},\"3\":{\"filter_type\":\"BL\",\"filter_tags\":\"\",\"filter_attributes\":\"\"},\"4\":{\"filter_type\":\"BL\",\"filter_tags\":\"\",\"filter_attributes\":\"\"},\"5\":{\"filter_type\":\"BL\",\"filter_tags\":\"\",\"filter_attributes\":\"\"},\"10\":{\"filter_type\":\"BL\",\"filter_tags\":\"\",\"filter_attributes\":\"\"},\"12\":{\"filter_type\":\"BL\",\"filter_tags\":\"\",\"filter_attributes\":\"\"},\"8\":{\"filter_type\":\"BL\",\"filter_tags\":\"\",\"filter_attributes\":\"\"}}}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(23, 'com_config', 'component', 'com_config', '', 1, 1, 0, 1, '{\"legacy\":false,\"name\":\"com_config\",\"type\":\"component\",\"creationDate\":\"April 2006\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 - 2011 Open Source Matters. All rights reserved.\\t\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"COM_CONFIG_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(24, 'com_redirect', 'component', 'com_redirect', '', 1, 1, 0, 1, '{\"legacy\":false,\"name\":\"com_redirect\",\"type\":\"component\",\"creationDate\":\"April 2006\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 - 2011 Open Source Matters. All rights reserved.\\t\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"COM_REDIRECT_XML_DESCRIPTION\",\"group\":\"\"}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(25, 'com_users', 'component', 'com_users', '', 1, 1, 0, 1, '{\"legacy\":false,\"name\":\"com_users\",\"type\":\"component\",\"creationDate\":\"April 2006\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 - 2011 Open Source Matters. All rights reserved.\\t\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"COM_USERS_XML_DESCRIPTION\",\"group\":\"\"}', '{\"allowUserRegistration\":\"1\",\"new_usertype\":\"2\",\"useractivation\":\"1\",\"frontend_userparams\":\"1\",\"mailSubjectPrefix\":\"\",\"mailBodySuffix\":\"\"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(100, 'PHPMailer', 'library', 'phpmailer', '', 0, 1, 1, 1, '{\"legacy\":false,\"name\":\"PHPMailer\",\"type\":\"library\",\"creationDate\":\"2008\",\"author\":\"PHPMailer\",\"copyright\":\"Copyright (C) PHPMailer.\",\"authorEmail\":\"\",\"authorUrl\":\"http:\\/\\/phpmailer.codeworxtech.com\\/\",\"version\":\"1.7.0\",\"description\":\"Classes for sending email\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(101, 'SimplePie', 'library', 'simplepie', '', 0, 1, 1, 1, '{\"legacy\":false,\"name\":\"SimplePie\",\"type\":\"library\",\"creationDate\":\"2008\",\"author\":\"SimplePie\",\"copyright\":\"Copyright (C) 2008 SimplePie\",\"authorEmail\":\"\",\"authorUrl\":\"http:\\/\\/simplepie.org\\/\",\"version\":\"1.0.1\",\"description\":\"A PHP-Based RSS and Atom Feed Framework.\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(102, 'phputf8', 'library', 'phputf8', '', 0, 1, 1, 1, '{\"legacy\":false,\"name\":\"phputf8\",\"type\":\"library\",\"creationDate\":\"2008\",\"author\":\"Harry Fuecks\",\"copyright\":\"Copyright various authors\",\"authorEmail\":\"\",\"authorUrl\":\"http:\\/\\/sourceforge.net\\/projects\\/phputf8\",\"version\":\"1.7.0\",\"description\":\"Classes for UTF8\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(103, 'Joomla! Web Application Framework', 'library', 'joomla', '', 0, 1, 1, 1, '{\"legacy\":false,\"name\":\"Joomla! Web Application Framework\",\"type\":\"library\",\"creationDate\":\"2008\",\"author\":\"Joomla\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"http:\\/\\/www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"The Joomla! Web Application Framework is the Core of the Joomla! Content Management System\",\"group\":\"\"}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(200, 'mod_articles_archive', 'module', 'mod_articles_archive', '', 0, 1, 1, 1, '{\"legacy\":false,\"name\":\"mod_articles_archive\",\"type\":\"module\",\"creationDate\":\"July 2006\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters.\\n\\t\\tAll rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"MOD_ARTICLES_ARCHIVE_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(201, 'mod_articles_latest', 'module', 'mod_articles_latest', '', 0, 1, 1, 1, '{\"legacy\":false,\"name\":\"mod_articles_latest\",\"type\":\"module\",\"creationDate\":\"July 2004\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"MOD_LATEST_NEWS_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(202, 'mod_articles_popular', 'module', 'mod_articles_popular', '', 0, 1, 1, 0, '{\"legacy\":false,\"name\":\"mod_articles_popular\",\"type\":\"module\",\"creationDate\":\"July 2006\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"MOD_POPULAR_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(203, 'mod_banners', 'module', 'mod_banners', '', 0, 1, 1, 1, '{\"legacy\":false,\"name\":\"mod_banners\",\"type\":\"module\",\"creationDate\":\"July 2006\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"MOD_BANNERS_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(204, 'mod_breadcrumbs', 'module', 'mod_breadcrumbs', '', 0, 1, 1, 1, '{\"legacy\":false,\"name\":\"mod_breadcrumbs\",\"type\":\"module\",\"creationDate\":\"July 2006\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"MOD_BREADCRUMBS_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(205, 'mod_custom', 'module', 'mod_custom', '', 0, 1, 1, 1, '{\"legacy\":false,\"name\":\"mod_custom\",\"type\":\"module\",\"creationDate\":\"July 2004\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"MOD_CUSTOM_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(206, 'mod_feed', 'module', 'mod_feed', '', 0, 1, 1, 1, '{\"legacy\":false,\"name\":\"mod_feed\",\"type\":\"module\",\"creationDate\":\"July 2005\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"MOD_FEED_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(207, 'mod_footer', 'module', 'mod_footer', '', 0, 1, 1, 1, '{\"legacy\":false,\"name\":\"mod_footer\",\"type\":\"module\",\"creationDate\":\"July 2006\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"MOD_FOOTER_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(208, 'mod_login', 'module', 'mod_login', '', 0, 1, 1, 1, '{\"legacy\":false,\"name\":\"mod_login\",\"type\":\"module\",\"creationDate\":\"July 2006\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"MOD_LOGIN_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(209, 'mod_menu', 'module', 'mod_menu', '', 0, 1, 1, 1, '{\"legacy\":false,\"name\":\"mod_menu\",\"type\":\"module\",\"creationDate\":\"July 2004\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"MOD_MENU_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(210, 'mod_articles_news', 'module', 'mod_articles_news', '', 0, 1, 1, 0, '{\"legacy\":false,\"name\":\"mod_articles_news\",\"type\":\"module\",\"creationDate\":\"July 2006\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"MOD_ARTICLES_NEWS_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(211, 'mod_random_image', 'module', 'mod_random_image', '', 0, 1, 1, 0, '{\"legacy\":false,\"name\":\"mod_random_image\",\"type\":\"module\",\"creationDate\":\"July 2006\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"MOD_RANDOM_IMAGE_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(212, 'mod_related_items', 'module', 'mod_related_items', '', 0, 1, 1, 0, '{\"legacy\":false,\"name\":\"mod_related_items\",\"type\":\"module\",\"creationDate\":\"July 2004\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"MOD_RELATED_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(213, 'mod_search', 'module', 'mod_search', '', 0, 1, 1, 0, '{\"legacy\":false,\"name\":\"mod_search\",\"type\":\"module\",\"creationDate\":\"July 2004\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"MOD_SEARCH_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(214, 'mod_stats', 'module', 'mod_stats', '', 0, 1, 1, 0, '{\"legacy\":false,\"name\":\"mod_stats\",\"type\":\"module\",\"creationDate\":\"July 2004\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"MOD_STATS_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(215, 'mod_syndicate', 'module', 'mod_syndicate', '', 0, 1, 1, 1, '{\"legacy\":false,\"name\":\"mod_syndicate\",\"type\":\"module\",\"creationDate\":\"May 2006\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"MOD_SYNDICATE_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(216, 'mod_users_latest', 'module', 'mod_users_latest', '', 0, 1, 1, 1, '{\"legacy\":false,\"name\":\"mod_users_latest\",\"type\":\"module\",\"creationDate\":\"December 2009\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"MOD_USERS_LATEST_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(217, 'mod_weblinks', 'module', 'mod_weblinks', '', 0, 1, 1, 0, '{\"legacy\":false,\"name\":\"mod_weblinks\",\"type\":\"module\",\"creationDate\":\"July 2009\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"MOD_WEBLINKS_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(218, 'mod_whosonline', 'module', 'mod_whosonline', '', 0, 1, 1, 0, '{\"legacy\":false,\"name\":\"mod_whosonline\",\"type\":\"module\",\"creationDate\":\"July 2004\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"MOD_WHOSONLINE_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(219, 'mod_wrapper', 'module', 'mod_wrapper', '', 0, 1, 1, 0, '{\"legacy\":false,\"name\":\"mod_wrapper\",\"type\":\"module\",\"creationDate\":\"October 2004\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"MOD_WRAPPER_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(220, 'mod_articles_category', 'module', 'mod_articles_category', '', 0, 1, 1, 1, '{\"legacy\":false,\"name\":\"mod_articles_category\",\"type\":\"module\",\"creationDate\":\"February 2010\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters.\\n\\t\\tAll rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"MOD_ARTICLES_CATEGORY_XML_DESCRIPTION\\n\\t\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(221, 'mod_articles_categories', 'module', 'mod_articles_categories', '', 0, 1, 1, 1, '{\"legacy\":false,\"name\":\"mod_articles_categories\",\"type\":\"module\",\"creationDate\":\"February 2010\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"MOD_ARTICLES_CATEGORIES_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(222, 'mod_languages', 'module', 'mod_languages', '', 0, 1, 1, 1, '{\"legacy\":false,\"name\":\"mod_languages\",\"type\":\"module\",\"creationDate\":\"February 2010\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"MOD_LANGUAGES_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(300, 'mod_custom', 'module', 'mod_custom', '', 1, 1, 1, 1, '{\"legacy\":false,\"name\":\"mod_custom\",\"type\":\"module\",\"creationDate\":\"July 2004\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"MOD_CUSTOM_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(301, 'mod_feed', 'module', 'mod_feed', '', 1, 1, 1, 0, '{\"legacy\":false,\"name\":\"mod_feed\",\"type\":\"module\",\"creationDate\":\"July 2005\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"MOD_FEED_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(302, 'mod_latest', 'module', 'mod_latest', '', 1, 1, 1, 0, '{\"legacy\":false,\"name\":\"mod_latest\",\"type\":\"module\",\"creationDate\":\"July 2004\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"MOD_LATEST_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(303, 'mod_logged', 'module', 'mod_logged', '', 1, 1, 1, 0, '{\"legacy\":false,\"name\":\"mod_logged\",\"type\":\"module\",\"creationDate\":\"January 2005\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"MOD_LOGGED_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(304, 'mod_login', 'module', 'mod_login', '', 1, 1, 1, 1, '{\"legacy\":false,\"name\":\"mod_login\",\"type\":\"module\",\"creationDate\":\"March 2005\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"MOD_LOGIN_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(305, 'mod_menu', 'module', 'mod_menu', '', 1, 1, 1, 0, '{\"legacy\":false,\"name\":\"mod_menu\",\"type\":\"module\",\"creationDate\":\"March 2006\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"MOD_MENU_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(306, 'mod_online', 'module', 'mod_online', '', 1, 1, 1, 1, 'false', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(307, 'mod_popular', 'module', 'mod_popular', '', 1, 1, 1, 0, '{\"legacy\":false,\"name\":\"mod_popular\",\"type\":\"module\",\"creationDate\":\"July 2004\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"MOD_POPULAR_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(308, 'mod_quickicon', 'module', 'mod_quickicon', '', 1, 1, 1, 1, '{\"legacy\":false,\"name\":\"mod_quickicon\",\"type\":\"module\",\"creationDate\":\"Nov 2005\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"MOD_QUICKICON_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(309, 'mod_status', 'module', 'mod_status', '', 1, 1, 1, 0, '{\"legacy\":false,\"name\":\"mod_status\",\"type\":\"module\",\"creationDate\":\"Feb 2006\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"MOD_STATUS_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(310, 'mod_submenu', 'module', 'mod_submenu', '', 1, 1, 1, 0, '{\"legacy\":false,\"name\":\"mod_submenu\",\"type\":\"module\",\"creationDate\":\"Feb 2006\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"MOD_SUBMENU_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(311, 'mod_title', 'module', 'mod_title', '', 1, 1, 1, 0, '{\"legacy\":false,\"name\":\"mod_title\",\"type\":\"module\",\"creationDate\":\"Nov 2005\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"MOD_TITLE_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(312, 'mod_toolbar', 'module', 'mod_toolbar', '', 1, 1, 1, 1, '{\"legacy\":false,\"name\":\"mod_toolbar\",\"type\":\"module\",\"creationDate\":\"Nov 2005\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"MOD_TOOLBAR_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(313, 'mod_unread', 'module', 'mod_unread', '', 1, 1, 1, 1, 'false', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(400, 'plg_authentication_gmail', 'plugin', 'gmail', 'authentication', 0, 0, 1, 0, '{\"legacy\":false,\"name\":\"plg_authentication_gmail\",\"type\":\"plugin\",\"creationDate\":\"February 2006\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"PLG_GMAIL_XML_DESCRIPTION\",\"group\":\"\"}', '{\"applysuffix\":\"0\",\"suffix\":\"\",\"verifypeer\":\"1\",\"user_blacklist\":\"\"}', '', '', 0, '0000-00-00 00:00:00', 1, 0),
(401, 'plg_authentication_joomla', 'plugin', 'joomla', 'authentication', 0, 1, 1, 1, '{\"legacy\":false,\"name\":\"plg_authentication_joomla\",\"type\":\"plugin\",\"creationDate\":\"November 2005\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"PLG_AUTH_JOOMLA_XML_DESCRIPTION\",\"group\":\"\"}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(402, 'plg_authentication_ldap', 'plugin', 'ldap', 'authentication', 0, 0, 1, 0, '{\"legacy\":false,\"name\":\"plg_authentication_ldap\",\"type\":\"plugin\",\"creationDate\":\"November 2005\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"PLG_LDAP_XML_DESCRIPTION\",\"group\":\"\"}', '{\"host\":\"\",\"port\":\"389\",\"use_ldapV3\":\"0\",\"negotiate_tls\":\"0\",\"no_referrals\":\"0\",\"auth_method\":\"bind\",\"base_dn\":\"\",\"search_string\":\"\",\"users_dn\":\"\",\"username\":\"admin\",\"password\":\"bobby7\",\"ldap_fullname\":\"fullName\",\"ldap_email\":\"mail\",\"ldap_uid\":\"uid\"}', '', '', 0, '0000-00-00 00:00:00', 3, 0),
(404, 'plg_content_emailcloak', 'plugin', 'emailcloak', 'content', 0, 1, 1, 0, '{\"legacy\":false,\"name\":\"plg_content_emailcloak\",\"type\":\"plugin\",\"creationDate\":\"November 2005\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"PLG_CONTENT_EMAILCLOAK_XML_DESCRIPTION\",\"group\":\"\"}', '{\"mode\":\"1\"}', '', '', 0, '0000-00-00 00:00:00', 1, 0),
(405, 'plg_content_geshi', 'plugin', 'geshi', 'content', 0, 0, 1, 0, '{\"legacy\":false,\"name\":\"plg_content_geshi\",\"type\":\"plugin\",\"creationDate\":\"November 2005\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"\",\"authorUrl\":\"qbnz.com\\/highlighter\",\"version\":\"1.7.0\",\"description\":\"PLG_CONTENT_GESHI_XML_DESCRIPTION\",\"group\":\"\"}', '{}', '', '', 0, '0000-00-00 00:00:00', 2, 0),
(406, 'plg_content_loadmodule', 'plugin', 'loadmodule', 'content', 0, 1, 1, 0, '{\"legacy\":false,\"name\":\"plg_content_loadmodule\",\"type\":\"plugin\",\"creationDate\":\"November 2005\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"PLG_LOADMODULE_XML_DESCRIPTION\",\"group\":\"\"}', '{\"style\":\"none\"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(407, 'plg_content_pagebreak', 'plugin', 'pagebreak', 'content', 0, 1, 1, 1, '{\"legacy\":false,\"name\":\"plg_content_pagebreak\",\"type\":\"plugin\",\"creationDate\":\"November 2005\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"PLG_CONTENT_PAGEBREAK_XML_DESCRIPTION\",\"group\":\"\"}', '{\"title\":\"1\",\"multipage_toc\":\"1\",\"showall\":\"1\"}', '', '', 0, '0000-00-00 00:00:00', 4, 0),
(408, 'plg_content_pagenavigation', 'plugin', 'pagenavigation', 'content', 0, 1, 1, 1, '{\"legacy\":false,\"name\":\"plg_content_pagenavigation\",\"type\":\"plugin\",\"creationDate\":\"January 2006\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"PLG_PAGENAVIGATION_XML_DESCRIPTION\",\"group\":\"\"}', '{\"position\":\"1\"}', '', '', 0, '0000-00-00 00:00:00', 5, 0),
(409, 'plg_content_vote', 'plugin', 'vote', 'content', 0, 1, 1, 1, '{\"legacy\":false,\"name\":\"plg_content_vote\",\"type\":\"plugin\",\"creationDate\":\"November 2005\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"PLG_VOTE_XML_DESCRIPTION\",\"group\":\"\"}', '{}', '', '', 0, '0000-00-00 00:00:00', 6, 0),
(410, 'plg_editors_codemirror', 'plugin', 'codemirror', 'editors', 0, 1, 1, 1, '{\"legacy\":false,\"name\":\"plg_editors_codemirror\",\"type\":\"plugin\",\"creationDate\":\"28 March 2011\",\"author\":\"Marijn Haverbeke\",\"copyright\":\"\",\"authorEmail\":\"N\\/A\",\"authorUrl\":\"\",\"version\":\"1.0\",\"description\":\"PLG_CODEMIRROR_XML_DESCRIPTION\",\"group\":\"\"}', '{\"linenumbers\":\"0\",\"tabmode\":\"indent\"}', '', '', 0, '0000-00-00 00:00:00', 1, 0),
(411, 'plg_editors_none', 'plugin', 'none', 'editors', 0, 1, 1, 1, '{\"legacy\":false,\"name\":\"plg_editors_none\",\"type\":\"plugin\",\"creationDate\":\"August 2004\",\"author\":\"Unknown\",\"copyright\":\"\",\"authorEmail\":\"N\\/A\",\"authorUrl\":\"\",\"version\":\"1.7.0\",\"description\":\"PLG_NONE_XML_DESCRIPTION\",\"group\":\"\"}', '{}', '', '', 0, '0000-00-00 00:00:00', 2, 0),
(412, 'plg_editors_tinymce', 'plugin', 'tinymce', 'editors', 0, 1, 1, 1, '{\"legacy\":false,\"name\":\"plg_editors_tinymce\",\"type\":\"plugin\",\"creationDate\":\"2005-2011\",\"author\":\"Moxiecode Systems AB\",\"copyright\":\"Moxiecode Systems AB\",\"authorEmail\":\"N\\/A\",\"authorUrl\":\"tinymce.moxiecode.com\\/\",\"version\":\"3.4.3.2\",\"description\":\"PLG_TINY_XML_DESCRIPTION\",\"group\":\"\"}', '{\"mode\":\"1\",\"skin\":\"0\",\"compressed\":\"0\",\"cleanup_startup\":\"0\",\"cleanup_save\":\"2\",\"entity_encoding\":\"raw\",\"lang_mode\":\"0\",\"lang_code\":\"en\",\"text_direction\":\"ltr\",\"content_css\":\"1\",\"content_css_custom\":\"\",\"relative_urls\":\"1\",\"newlines\":\"0\",\"invalid_elements\":\"script,applet,iframe\",\"extended_elements\":\"\",\"toolbar\":\"top\",\"toolbar_align\":\"left\",\"html_height\":\"550\",\"html_width\":\"750\",\"element_path\":\"1\",\"fonts\":\"1\",\"paste\":\"1\",\"searchreplace\":\"1\",\"insertdate\":\"1\",\"format_date\":\"%Y-%m-%d\",\"inserttime\":\"1\",\"format_time\":\"%H:%M:%S\",\"colors\":\"1\",\"table\":\"1\",\"smilies\":\"1\",\"media\":\"1\",\"hr\":\"1\",\"directionality\":\"1\",\"fullscreen\":\"1\",\"style\":\"1\",\"layer\":\"1\",\"xhtmlxtras\":\"1\",\"visualchars\":\"1\",\"nonbreaking\":\"1\",\"template\":\"1\",\"blockquote\":\"1\",\"wordcount\":\"1\",\"advimage\":\"1\",\"advlink\":\"1\",\"autosave\":\"1\",\"contextmenu\":\"1\",\"inlinepopups\":\"1\",\"safari\":\"0\",\"custom_plugin\":\"\",\"custom_button\":\"\"}', '', '', 0, '0000-00-00 00:00:00', 3, 0),
(413, 'plg_editors-xtd_article', 'plugin', 'article', 'editors-xtd', 0, 1, 1, 1, '{\"legacy\":false,\"name\":\"plg_editors-xtd_article\",\"type\":\"plugin\",\"creationDate\":\"October 2009\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"PLG_ARTICLE_XML_DESCRIPTION\",\"group\":\"\"}', '{}', '', '', 0, '0000-00-00 00:00:00', 1, 0),
(414, 'plg_editors-xtd_image', 'plugin', 'image', 'editors-xtd', 0, 1, 1, 0, '{\"legacy\":false,\"name\":\"plg_editors-xtd_image\",\"type\":\"plugin\",\"creationDate\":\"August 2004\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"PLG_IMAGE_XML_DESCRIPTION\",\"group\":\"\"}', '{}', '', '', 0, '0000-00-00 00:00:00', 2, 0),
(415, 'plg_editors-xtd_pagebreak', 'plugin', 'pagebreak', 'editors-xtd', 0, 1, 1, 0, '{\"legacy\":false,\"name\":\"plg_editors-xtd_pagebreak\",\"type\":\"plugin\",\"creationDate\":\"August 2004\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"PLG_EDITORSXTD_PAGEBREAK_XML_DESCRIPTION\",\"group\":\"\"}', '{}', '', '', 0, '0000-00-00 00:00:00', 3, 0),
(416, 'plg_editors-xtd_readmore', 'plugin', 'readmore', 'editors-xtd', 0, 1, 1, 0, '{\"legacy\":false,\"name\":\"plg_editors-xtd_readmore\",\"type\":\"plugin\",\"creationDate\":\"March 2006\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"PLG_READMORE_XML_DESCRIPTION\",\"group\":\"\"}', '{}', '', '', 0, '0000-00-00 00:00:00', 4, 0),
(417, 'plg_search_categories', 'plugin', 'categories', 'search', 0, 1, 1, 0, '{\"legacy\":false,\"name\":\"plg_search_categories\",\"type\":\"plugin\",\"creationDate\":\"November 2005\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"PLG_SEARCH_CATEGORIES_XML_DESCRIPTION\",\"group\":\"\"}', '{\"search_limit\":\"50\",\"search_content\":\"1\",\"search_archived\":\"1\"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(418, 'plg_search_contacts', 'plugin', 'contacts', 'search', 0, 1, 1, 0, '{\"legacy\":false,\"name\":\"plg_search_contacts\",\"type\":\"plugin\",\"creationDate\":\"November 2005\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"PLG_SEARCH_CONTACTS_XML_DESCRIPTION\",\"group\":\"\"}', '{\"search_limit\":\"50\",\"search_content\":\"1\",\"search_archived\":\"1\"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(419, 'plg_search_content', 'plugin', 'content', 'search', 0, 1, 1, 0, '{\"legacy\":false,\"name\":\"plg_search_content\",\"type\":\"plugin\",\"creationDate\":\"November 2005\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"PLG_SEARCH_CONTENT_XML_DESCRIPTION\",\"group\":\"\"}', '{\"search_limit\":\"50\",\"search_content\":\"1\",\"search_archived\":\"1\"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(420, 'plg_search_newsfeeds', 'plugin', 'newsfeeds', 'search', 0, 1, 1, 0, '{\"legacy\":false,\"name\":\"plg_search_newsfeeds\",\"type\":\"plugin\",\"creationDate\":\"November 2005\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"PLG_SEARCH_NEWSFEEDS_XML_DESCRIPTION\",\"group\":\"\"}', '{\"search_limit\":\"50\",\"search_content\":\"1\",\"search_archived\":\"1\"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(421, 'plg_search_weblinks', 'plugin', 'weblinks', 'search', 0, 1, 1, 0, '{\"legacy\":false,\"name\":\"plg_search_weblinks\",\"type\":\"plugin\",\"creationDate\":\"November 2005\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"PLG_SEARCH_WEBLINKS_XML_DESCRIPTION\",\"group\":\"\"}', '{\"search_limit\":\"50\",\"search_content\":\"1\",\"search_archived\":\"1\"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(422, 'plg_system_languagefilter', 'plugin', 'languagefilter', 'system', 0, 0, 1, 1, '{\"legacy\":false,\"name\":\"plg_system_languagefilter\",\"type\":\"plugin\",\"creationDate\":\"July 2010\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"PLG_SYSTEM_LANGUAGEFILTER_XML_DESCRIPTION\",\"group\":\"\"}', '{}', '', '', 0, '0000-00-00 00:00:00', 1, 0),
(423, 'plg_system_p3p', 'plugin', 'p3p', 'system', 0, 1, 1, 1, '{\"legacy\":false,\"name\":\"plg_system_p3p\",\"type\":\"plugin\",\"creationDate\":\"September 2010\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"PLG_P3P_XML_DESCRIPTION\",\"group\":\"\"}', '{\"headers\":\"NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM\"}', '', '', 0, '0000-00-00 00:00:00', 2, 0),
(424, 'plg_system_cache', 'plugin', 'cache', 'system', 0, 0, 1, 1, '{\"legacy\":false,\"name\":\"plg_system_cache\",\"type\":\"plugin\",\"creationDate\":\"February 2007\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"PLG_CACHE_XML_DESCRIPTION\",\"group\":\"\"}', '{\"browsercache\":\"0\",\"cachetime\":\"15\"}', '', '', 0, '0000-00-00 00:00:00', 9, 0),
(425, 'plg_system_debug', 'plugin', 'debug', 'system', 0, 1, 1, 0, '{\"legacy\":false,\"name\":\"plg_system_debug\",\"type\":\"plugin\",\"creationDate\":\"December 2006\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"PLG_DEBUG_XML_DESCRIPTION\",\"group\":\"\"}', '{\"profile\":\"1\",\"queries\":\"1\",\"memory\":\"1\",\"language_files\":\"1\",\"language_strings\":\"1\",\"strip-first\":\"1\",\"strip-prefix\":\"\",\"strip-suffix\":\"\"}', '', '', 0, '0000-00-00 00:00:00', 4, 0),
(426, 'plg_system_log', 'plugin', 'log', 'system', 0, 1, 1, 1, '{\"legacy\":false,\"name\":\"plg_system_log\",\"type\":\"plugin\",\"creationDate\":\"April 2007\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"PLG_LOG_XML_DESCRIPTION\",\"group\":\"\"}', '{}', '', '', 0, '0000-00-00 00:00:00', 5, 0),
(427, 'plg_system_redirect', 'plugin', 'redirect', 'system', 0, 1, 1, 1, '{\"legacy\":false,\"name\":\"plg_system_redirect\",\"type\":\"plugin\",\"creationDate\":\"April 2009\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"PLG_REDIRECT_XML_DESCRIPTION\",\"group\":\"\"}', '{}', '', '', 0, '0000-00-00 00:00:00', 6, 0),
(428, 'plg_system_remember', 'plugin', 'remember', 'system', 0, 1, 1, 1, '{\"legacy\":false,\"name\":\"plg_system_remember\",\"type\":\"plugin\",\"creationDate\":\"April 2007\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"PLG_REMEMBER_XML_DESCRIPTION\",\"group\":\"\"}', '{}', '', '', 0, '0000-00-00 00:00:00', 7, 0),
(429, 'plg_system_sef', 'plugin', 'sef', 'system', 0, 1, 1, 0, '{\"legacy\":false,\"name\":\"plg_system_sef\",\"type\":\"plugin\",\"creationDate\":\"December 2007\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"PLG_SEF_XML_DESCRIPTION\",\"group\":\"\"}', '{}', '', '', 0, '0000-00-00 00:00:00', 8, 0),
(430, 'plg_system_logout', 'plugin', 'logout', 'system', 0, 1, 1, 1, '{\"legacy\":false,\"name\":\"plg_system_logout\",\"type\":\"plugin\",\"creationDate\":\"April 2009\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"PLG_SYSTEM_LOGOUT_XML_DESCRIPTION\",\"group\":\"\"}', '{}', '', '', 0, '0000-00-00 00:00:00', 3, 0),
(431, 'plg_user_contactcreator', 'plugin', 'contactcreator', 'user', 0, 0, 1, 1, '{\"legacy\":false,\"name\":\"plg_user_contactcreator\",\"type\":\"plugin\",\"creationDate\":\"August 2009\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"PLG_CONTACTCREATOR_XML_DESCRIPTION\",\"group\":\"\"}', '{\"autowebpage\":\"\",\"category\":\"34\",\"autopublish\":\"0\"}', '', '', 0, '0000-00-00 00:00:00', 1, 0),
(432, 'plg_user_joomla', 'plugin', 'joomla', 'user', 0, 1, 1, 0, '{\"legacy\":false,\"name\":\"plg_user_joomla\",\"type\":\"plugin\",\"creationDate\":\"December 2006\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 - 2009 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"PLG_USER_JOOMLA_XML_DESCRIPTION\",\"group\":\"\"}', '{\"autoregister\":\"1\"}', '', '', 0, '0000-00-00 00:00:00', 2, 0);
";		
$q->QUERY_SQL($sql,$this->mysql_database);		
		
$sql="
INSERT INTO `jos_extensions` (`extension_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES 
(433, 'plg_user_profile', 'plugin', 'profile', 'user', 0, 0, 1, 1, '{\"legacy\":false,\"name\":\"plg_user_profile\",\"type\":\"plugin\",\"creationDate\":\"January 2008\",\"author\":\"Joomla! Project\",\"copyright\":\"(C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"PLG_USER_PROFILE_XML_DESCRIPTION\",\"group\":\"\"}', '{\"register-require_address1\":\"1\",\"register-require_address2\":\"1\",\"register-require_city\":\"1\",\"register-require_region\":\"1\",\"register-require_country\":\"1\",\"register-require_postal_code\":\"1\",\"register-require_phone\":\"1\",\"register-require_website\":\"1\",\"register-require_favoritebook\":\"1\",\"register-require_aboutme\":\"1\",\"register-require_tos\":\"1\",\"register-require_dob\":\"1\",\"profile-require_address1\":\"1\",\"profile-require_address2\":\"1\",\"profile-require_city\":\"1\",\"profile-require_region\":\"1\",\"profile-require_country\":\"1\",\"profile-require_postal_code\":\"1\",\"profile-require_phone\":\"1\",\"profile-require_website\":\"1\",\"profile-require_favoritebook\":\"1\",\"profile-require_aboutme\":\"1\",\"profile-require_tos\":\"1\",\"profile-require_dob\":\"1\"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(434, 'plg_extension_joomla', 'plugin', 'joomla', 'extension', 0, 1, 1, 1, '{\"legacy\":false,\"name\":\"plg_extension_joomla\",\"type\":\"plugin\",\"creationDate\":\"May 2010\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"PLG_EXTENSION_JOOMLA_XML_DESCRIPTION\",\"group\":\"\"}', '{}', '', '', 0, '0000-00-00 00:00:00', 1, 0),
(435, 'plg_content_joomla', 'plugin', 'joomla', 'content', 0, 1, 1, 0, '{\"legacy\":false,\"name\":\"plg_content_joomla\",\"type\":\"plugin\",\"creationDate\":\"November 2010\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"PLG_CONTENT_JOOMLA_XML_DESCRIPTION\",\"group\":\"\"}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(500, 'atomic', 'template', 'atomic', '', 0, 1, 1, 0, '{\"legacy\":false,\"name\":\"atomic\",\"type\":\"template\",\"creationDate\":\"10\\/10\\/09\",\"author\":\"Ron Severdia\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.\",\"authorEmail\":\"contact@kontentdesign.com\",\"authorUrl\":\"http:\\/\\/www.kontentdesign.com\",\"version\":\"1.7.0\",\"description\":\"TPL_ATOMIC_XML_DESCRIPTION\",\"group\":\"\"}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(502, 'bluestork', 'template', 'bluestork', '', 1, 1, 1, 0, '{\"legacy\":false,\"name\":\"bluestork\",\"type\":\"template\",\"creationDate\":\"07\\/02\\/09\",\"author\":\"Ron Severdia\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.\",\"authorEmail\":\"contact@kontentdesign.com\",\"authorUrl\":\"http:\\/\\/www.kontentdesign.com\",\"version\":\"1.7.0\",\"description\":\"TPL_BLUESTORK_XML_DESCRIPTION\",\"group\":\"\"}', '{\"useRoundedCorners\":\"1\",\"showSiteName\":\"0\",\"textBig\":\"0\",\"highContrast\":\"0\"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(503, 'beez_20', 'template', 'beez_20', '', 0, 1, 1, 0, '{\"legacy\":false,\"name\":\"beez_20\",\"type\":\"template\",\"creationDate\":\"25 November 2009\",\"author\":\"Angie Radtke\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.\",\"authorEmail\":\"a.radtke@derauftritt.de\",\"authorUrl\":\"http:\\/\\/www.der-auftritt.de\",\"version\":\"1.7.0\",\"description\":\"TPL_BEEZ2_XML_DESCRIPTION\",\"group\":\"\"}', '{\"wrapperSmall\":\"53\",\"wrapperLarge\":\"72\",\"sitetitle\":\"\",\"sitedescription\":\"\",\"navposition\":\"center\",\"templatecolor\":\"nature\"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(504, 'hathor', 'template', 'hathor', '', 1, 1, 1, 0, '{\"legacy\":false,\"name\":\"hathor\",\"type\":\"template\",\"creationDate\":\"May 2010\",\"author\":\"Andrea Tarr\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.\",\"authorEmail\":\"hathor@tarrconsulting.com\",\"authorUrl\":\"http:\\/\\/www.tarrconsulting.com\",\"version\":\"1.7.0\",\"description\":\"TPL_HATHOR_XML_DESCRIPTION\",\"group\":\"\"}', '{\"showSiteName\":\"0\",\"colourChoice\":\"0\",\"boldText\":\"0\"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(505, 'beez5', 'template', 'beez5', '', 0, 1, 1, 0, '{\"legacy\":false,\"name\":\"beez5\",\"type\":\"template\",\"creationDate\":\"21 May 2010\",\"author\":\"Angie Radtke\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.\",\"authorEmail\":\"a.radtke@derauftritt.de\",\"authorUrl\":\"http:\\/\\/www.der-auftritt.de\",\"version\":\"1.7.0\",\"description\":\"TPL_BEEZ5_XML_DESCRIPTION\",\"group\":\"\"}', '{\"wrapperSmall\":\"53\",\"wrapperLarge\":\"72\",\"sitetitle\":\"\",\"sitedescription\":\"\",\"navposition\":\"center\",\"html5\":\"0\"}', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(600, 'English (United Kingdom)', 'language', 'en-GB', '', 0, 1, 1, 1, '{\"legacy\":false,\"name\":\"English (United Kingdom)\",\"type\":\"language\",\"creationDate\":\"2008-03-15\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"en-GB site language\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(601, 'English (United Kingdom)', 'language', 'en-GB', '', 1, 1, 1, 1, '{\"legacy\":false,\"name\":\"English (United Kingdom)\",\"type\":\"language\",\"creationDate\":\"2008-03-15\",\"author\":\"Joomla! Project\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"en-GB administrator language\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(700, 'files_joomla', 'file', 'joomla', '', 0, 1, 1, 1, '{\"legacy\":false,\"name\":\"files_joomla\",\"type\":\"file\",\"creationDate\":\"July 2011\",\"author\":\"Joomla!\",\"copyright\":\"(C) 2005 - 2011 Open Source Matters. All rights reserved\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"FILES_JOOMLA_XML_DESCRIPTION\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0),
(800, 'Joomla! Content Management System', 'package', 'pkg_joomla', '', 0, 1, 1, 1, '{\"legacy\":false,\"name\":\"Joomla! Content Management System\",\"type\":\"package\",\"creationDate\":\"2006\",\"author\":\"Joomla!\",\"copyright\":\"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.\",\"authorEmail\":\"admin@joomla.org\",\"authorUrl\":\"http:\\/\\/www.joomla.org\",\"version\":\"1.7.0\",\"description\":\"The Joomla! Content Management System is one of the most popular content management system''s available today.\",\"group\":\"\"}', '', '', '', 0, '0000-00-00 00:00:00', 0, 0);";
$q->QUERY_SQL($sql,$this->mysql_database);
}


if(!$q->TABLE_EXISTS("jos_languages", $this->mysql_database)){
		$sql="CREATE TABLE IF NOT EXISTS `jos_languages` (
  `lang_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `lang_code` char(7) NOT NULL,
  `title` varchar(50) NOT NULL,
  `title_native` varchar(50) NOT NULL,
  `sef` varchar(50) NOT NULL,
  `image` varchar(50) NOT NULL,
  `description` varchar(512) NOT NULL,
  `metakey` text NOT NULL,
  `metadesc` text NOT NULL,
  `published` int(11) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`lang_id`),
  UNIQUE KEY `idx_sef` (`sef`),
  KEY `idx_ordering` (`ordering`)
)";$q->QUERY_SQL($sql,$this->mysql_database);
$sql="INSERT INTO `jos_languages` (`lang_id`, `lang_code`, `title`, `title_native`, `sef`, `image`, `description`, `metakey`, `metadesc`, `published`, `ordering`) VALUES
(1, 'en-GB', 'English (UK)', 'English (UK)', 'en', 'en', '', '', '', 1, 1);";;$q->QUERY_SQL($sql,$this->mysql_database);}

if(!$q->TABLE_EXISTS("jos_menu", $this->mysql_database)){
		$sql="CREATE TABLE IF NOT EXISTS `jos_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `menutype` varchar(24) NOT NULL COMMENT 'The type of menu this item belongs to. FK to #__menu_types.menutype',
  `title` varchar(255) NOT NULL COMMENT 'The display title of the menu item.',
  `alias` varchar(255) NOT NULL COMMENT 'The SEF alias of the menu item.',
  `note` varchar(255) NOT NULL DEFAULT '',
  `path` varchar(1024) NOT NULL COMMENT 'The computed path of the menu item based on the alias field.',
  `link` varchar(1024) NOT NULL COMMENT 'The actually link the menu item refers to.',
  `type` varchar(16) NOT NULL COMMENT 'The type of link: Component, URL, Alias, Separator',
  `published` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'The published state of the menu link.',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'The parent menu item in the menu tree.',
  `level` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The relative level in the tree.',
  `component_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to #__extensions.id',
  `ordering` int(11) NOT NULL DEFAULT '0' COMMENT 'The relative ordering of the menu item in the tree.',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to #__users.id',
  `checked_out_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'The time the menu item was checked out.',
  `browserNav` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'The click behaviour of the link.',
  `access` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'The access level required to view the menu item.',
  `img` varchar(255) NOT NULL COMMENT 'The image of the menu item.',
  `template_style_id` int(10) unsigned NOT NULL DEFAULT '0',
  `params` text NOT NULL COMMENT 'JSON encoded data for the menu item.',
  `lft` int(11) NOT NULL DEFAULT '0' COMMENT 'Nested set lft.',
  `rgt` int(11) NOT NULL DEFAULT '0' COMMENT 'Nested set rgt.',
  `home` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Indicates if this menu item is the home or default page.',
  `language` char(7) NOT NULL DEFAULT '',
  `client_id` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_client_id_parent_id_alias` (`client_id`,`parent_id`,`alias`),
  KEY `idx_componentid` (`component_id`,`menutype`,`published`,`access`),
  KEY `idx_menutype` (`menutype`),
  KEY `idx_left_right` (`lft`,`rgt`),
  KEY `idx_alias` (`alias`),
  KEY `idx_path` (`path`(333)),
  KEY `idx_language` (`language`)
)";$q->QUERY_SQL($sql,$this->mysql_database); 
$sql="
INSERT INTO `jos_menu` (`id`, `menutype`, `title`, `alias`, `note`, `path`, `link`, `type`, `published`, `parent_id`, `level`, `component_id`, `ordering`, `checked_out`, `checked_out_time`, `browserNav`, `access`, `img`, `template_style_id`, `params`, `lft`, `rgt`, `home`, `language`, `client_id`) VALUES
(1, '', 'Menu_Item_Root', 'root', '', '', '', '', 1, 0, 0, 0, 0, 0, '0000-00-00 00:00:00', 0, 0, '', 0, '', 0, 41, 0, '*', 0),
(2, 'menu', 'com_banners', 'Banners', '', 'Banners', 'index.php?option=com_banners', 'component', 0, 1, 1, 4, 0, 0, '0000-00-00 00:00:00', 0, 0, 'class:banners', 0, '', 1, 10, 0, '*', 1),
(3, 'menu', 'com_banners', 'Banners', '', 'Banners/Banners', 'index.php?option=com_banners', 'component', 0, 2, 2, 4, 0, 0, '0000-00-00 00:00:00', 0, 0, 'class:banners', 0, '', 2, 3, 0, '*', 1),
(4, 'menu', 'com_banners_categories', 'Categories', '', 'Banners/Categories', 'index.php?option=com_categories&extension=com_banners', 'component', 0, 2, 2, 6, 0, 0, '0000-00-00 00:00:00', 0, 0, 'class:banners-cat', 0, '', 4, 5, 0, '*', 1),
(5, 'menu', 'com_banners_clients', 'Clients', '', 'Banners/Clients', 'index.php?option=com_banners&view=clients', 'component', 0, 2, 2, 4, 0, 0, '0000-00-00 00:00:00', 0, 0, 'class:banners-clients', 0, '', 6, 7, 0, '*', 1),
(6, 'menu', 'com_banners_tracks', 'Tracks', '', 'Banners/Tracks', 'index.php?option=com_banners&view=tracks', 'component', 0, 2, 2, 4, 0, 0, '0000-00-00 00:00:00', 0, 0, 'class:banners-tracks', 0, '', 8, 9, 0, '*', 1),
(7, 'menu', 'com_contact', 'Contacts', '', 'Contacts', 'index.php?option=com_contact', 'component', 0, 1, 1, 8, 0, 0, '0000-00-00 00:00:00', 0, 0, 'class:contact', 0, '', 11, 16, 0, '*', 1),
(8, 'menu', 'com_contact', 'Contacts', '', 'Contacts/Contacts', 'index.php?option=com_contact', 'component', 0, 7, 2, 8, 0, 0, '0000-00-00 00:00:00', 0, 0, 'class:contact', 0, '', 12, 13, 0, '*', 1),
(9, 'menu', 'com_contact_categories', 'Categories', '', 'Contacts/Categories', 'index.php?option=com_categories&extension=com_contact', 'component', 0, 7, 2, 6, 0, 0, '0000-00-00 00:00:00', 0, 0, 'class:contact-cat', 0, '', 14, 15, 0, '*', 1),
(10, 'menu', 'com_messages', 'Messaging', '', 'Messaging', 'index.php?option=com_messages', 'component', 0, 1, 1, 15, 0, 0, '0000-00-00 00:00:00', 0, 0, 'class:messages', 0, '', 17, 22, 0, '*', 1),
(11, 'menu', 'com_messages_add', 'New Private Message', '', 'Messaging/New Private Message', 'index.php?option=com_messages&task=message.add', 'component', 0, 10, 2, 15, 0, 0, '0000-00-00 00:00:00', 0, 0, 'class:messages-add', 0, '', 18, 19, 0, '*', 1),
(12, 'menu', 'com_messages_read', 'Read Private Message', '', 'Messaging/Read Private Message', 'index.php?option=com_messages', 'component', 0, 10, 2, 15, 0, 0, '0000-00-00 00:00:00', 0, 0, 'class:messages-read', 0, '', 20, 21, 0, '*', 1),
(13, 'menu', 'com_newsfeeds', 'News Feeds', '', 'News Feeds', 'index.php?option=com_newsfeeds', 'component', 0, 1, 1, 17, 0, 0, '0000-00-00 00:00:00', 0, 0, 'class:newsfeeds', 0, '', 23, 28, 0, '*', 1),
(14, 'menu', 'com_newsfeeds_feeds', 'Feeds', '', 'News Feeds/Feeds', 'index.php?option=com_newsfeeds', 'component', 0, 13, 2, 17, 0, 0, '0000-00-00 00:00:00', 0, 0, 'class:newsfeeds', 0, '', 24, 25, 0, '*', 1),
(15, 'menu', 'com_newsfeeds_categories', 'Categories', '', 'News Feeds/Categories', 'index.php?option=com_categories&extension=com_newsfeeds', 'component', 0, 13, 2, 6, 0, 0, '0000-00-00 00:00:00', 0, 0, 'class:newsfeeds-cat', 0, '', 26, 27, 0, '*', 1),
(16, 'menu', 'com_redirect', 'Redirect', '', 'Redirect', 'index.php?option=com_redirect', 'component', 0, 1, 1, 24, 0, 0, '0000-00-00 00:00:00', 0, 0, 'class:redirect', 0, '', 37, 38, 0, '*', 1),
(17, 'menu', 'com_search', 'Search', '', 'Search', 'index.php?option=com_search', 'component', 0, 1, 1, 19, 0, 0, '0000-00-00 00:00:00', 0, 0, 'class:search', 0, '', 29, 30, 0, '*', 1),
(18, 'menu', 'com_weblinks', 'Weblinks', '', 'Weblinks', 'index.php?option=com_weblinks', 'component', 0, 1, 1, 21, 0, 0, '0000-00-00 00:00:00', 0, 0, 'class:weblinks', 0, '', 31, 36, 0, '*', 1),
(19, 'menu', 'com_weblinks_links', 'Links', '', 'Weblinks/Links', 'index.php?option=com_weblinks', 'component', 0, 18, 2, 21, 0, 0, '0000-00-00 00:00:00', 0, 0, 'class:weblinks', 0, '', 32, 33, 0, '*', 1),
(20, 'menu', 'com_weblinks_categories', 'Categories', '', 'Weblinks/Categories', 'index.php?option=com_categories&extension=com_weblinks', 'component', 0, 18, 2, 6, 0, 0, '0000-00-00 00:00:00', 0, 0, 'class:weblinks-cat', 0, '', 34, 35, 0, '*', 1),
(101, 'mainmenu', 'Home', 'home', '', 'home', 'index.php?option=com_content&view=featured', 'component', 1, 1, 1, 22, 0, 0, '0000-00-00 00:00:00', 0, 1, '', 0, '{\"featured_categories\":[\"\"],\"num_leading_articles\":\"1\",\"num_intro_articles\":\"3\",\"num_columns\":\"3\",\"num_links\":\"0\",\"orderby_pri\":\"\",\"orderby_sec\":\"front\",\"order_date\":\"\",\"multi_column_order\":\"1\",\"show_pagination\":\"2\",\"show_pagination_results\":\"1\",\"show_noauth\":\"\",\"article-allow_ratings\":\"\",\"article-allow_comments\":\"\",\"show_feed_link\":\"1\",\"feed_summary\":\"\",\"show_title\":\"\",\"link_titles\":\"\",\"show_intro\":\"\",\"show_category\":\"\",\"link_category\":\"\",\"show_parent_category\":\"\",\"link_parent_category\":\"\",\"show_author\":\"\",\"show_create_date\":\"\",\"show_modify_date\":\"\",\"show_publish_date\":\"\",\"show_item_navigation\":\"\",\"show_readmore\":\"\",\"show_icons\":\"\",\"show_print_icon\":\"\",\"show_email_icon\":\"\",\"show_hits\":\"\",\"menu-anchor_title\":\"\",\"menu-anchor_css\":\"\",\"menu_image\":\"\",\"show_page_heading\":1,\"page_title\":\"\",\"page_heading\":\"\",\"pageclass_sfx\":\"\",\"menu-meta_description\":\"\",\"menu-meta_keywords\":\"\",\"robots\":\"\",\"secure\":0}', 39, 40, 1, '*', 0);
";$q->QUERY_SQL($sql,$this->mysql_database);}

if(!$q->TABLE_EXISTS("jos_menu_types", $this->mysql_database)){
		$sql="CREATE TABLE IF NOT EXISTS `jos_menu_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `menutype` varchar(24) NOT NULL,
  `title` varchar(48) NOT NULL,
  `description` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_menutype` (`menutype`)
)";$q->QUERY_SQL($sql,$this->mysql_database);
		$sql="INSERT INTO `jos_menu_types` (`id`, `menutype`, `title`, `description`) VALUES
(1, 'mainmenu', 'Main Menu', 'The main menu for the site');";$q->QUERY_SQL($sql,$this->mysql_database);}
		
if(!$q->TABLE_EXISTS("jos_messages", $this->mysql_database)){
		$sql="CREATE TABLE IF NOT EXISTS `jos_messages` (
  `message_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id_from` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id_to` int(10) unsigned NOT NULL DEFAULT '0',
  `folder_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `date_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `state` tinyint(1) NOT NULL DEFAULT '0',
  `priority` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `subject` varchar(255) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  PRIMARY KEY (`message_id`),
  KEY `useridto_state` (`user_id_to`,`state`)
)";$q->QUERY_SQL($sql,$this->mysql_database);}		

if(!$q->TABLE_EXISTS("jos_messages_cfg", $this->mysql_database)){
		$sql="CREATE TABLE IF NOT EXISTS `jos_messages_cfg` (
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `cfg_name` varchar(100) NOT NULL DEFAULT '',
  `cfg_value` varchar(255) NOT NULL DEFAULT '',
  UNIQUE KEY `idx_user_var_name` (`user_id`,`cfg_name`)
)";$q->QUERY_SQL($sql,$this->mysql_database);}	 

if(!$q->TABLE_EXISTS("jos_modules", $this->mysql_database)){
		$sql="CREATE TABLE IF NOT EXISTS `jos_modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL DEFAULT '',
  `note` varchar(255) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `position` varchar(50) DEFAULT NULL,
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `module` varchar(50) DEFAULT NULL,
  `access` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `showtitle` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `params` text NOT NULL,
  `client_id` tinyint(4) NOT NULL DEFAULT '0',
  `language` char(7) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `published` (`published`,`access`),
  KEY `newsfeeds` (`module`,`published`),
  KEY `idx_language` (`language`)
)";$q->QUERY_SQL($sql,$this->mysql_database);
$sql="
INSERT INTO `jos_modules` (`id`, `title`, `note`, `content`, `ordering`, `position`, `checked_out`, `checked_out_time`, `publish_up`, `publish_down`, `published`, `module`, `access`, `showtitle`, `params`, `client_id`, `language`) VALUES
(1, 'Main Menu', '', '', 1, 'position-7', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_menu', 1, 1, '{\"menutype\":\"mainmenu\",\"startLevel\":\"0\",\"endLevel\":\"0\",\"showAllChildren\":\"0\",\"tag_id\":\"\",\"class_sfx\":\"\",\"window_open\":\"\",\"layout\":\"\",\"moduleclass_sfx\":\"_menu\",\"cache\":\"1\",\"cache_time\":\"900\",\"cachemode\":\"itemid\"}', 0, '*'),
(2, 'Login', '', '', 1, 'login', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_login', 1, 1, '', 1, '*'),
(3, 'Popular Articles', '', '', 3, 'cpanel', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_popular', 3, 1, '{\"count\":\"5\",\"catid\":\"\",\"user_id\":\"0\",\"layout\":\"_:default\",\"moduleclass_sfx\":\"\",\"cache\":\"0\",\"automatic_title\":\"1\"}', 1, '*'),
(4, 'Recently Added Articles', '', '', 4, 'cpanel', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_latest', 3, 1, '{\"count\":\"5\",\"ordering\":\"c_dsc\",\"catid\":\"\",\"user_id\":\"0\",\"layout\":\"_:default\",\"moduleclass_sfx\":\"\",\"cache\":\"0\",\"automatic_title\":\"1\"}', 1, '*'),
(8, 'Toolbar', '', '', 1, 'toolbar', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_toolbar', 3, 1, '', 1, '*'),
(9, 'Quick Icons', '', '', 1, 'icon', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_quickicon', 3, 1, '', 1, '*'),
(10, 'Logged-in Users', '', '', 2, 'cpanel', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_logged', 3, 1, '{\"count\":\"5\",\"name\":\"1\",\"layout\":\"_:default\",\"moduleclass_sfx\":\"\",\"cache\":\"0\",\"automatic_title\":\"1\"}', 1, '*'),
(12, 'Admin Menu', '', '', 1, 'menu', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_menu', 3, 1, '{\"layout\":\"\",\"moduleclass_sfx\":\"\",\"shownew\":\"1\",\"showhelp\":\"1\",\"cache\":\"0\"}', 1, '*'),
(13, 'Admin Submenu', '', '', 1, 'submenu', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_submenu', 3, 1, '', 1, '*'),
(14, 'User Status', '', '', 1, 'status', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_status', 3, 1, '', 1, '*'),
(15, 'Title', '', '', 1, 'title', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_title', 3, 1, '', 1, '*'),
(16, 'Login Form', '', '', 7, 'position-7', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_login', 1, 1, '{\"greeting\":\"1\",\"name\":\"0\"}', 0, '*'),
(17, 'Breadcrumbs', '', '', 1, 'position-2', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_breadcrumbs', 1, 1, '{\"moduleclass_sfx\":\"\",\"showHome\":\"1\",\"homeText\":\"Home\",\"showComponent\":\"1\",\"separator\":\"\",\"cache\":\"1\",\"cache_time\":\"900\",\"cachemode\":\"itemid\"}', 0, '*'),
(18, 'Book Store', '', '', 1, 'position-10', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, 'mod_banners', 1, 0, '{\"target\":\"1\",\"count\":\"1\",\"cid\":\"3\",\"catid\":[\"\"],\"tag_search\":\"0\",\"ordering\":\"0\",\"header_text\":\"\",\"footer_text\":\"Books!\",\"layout\":\"_:default\",\"moduleclass_sfx\":\"\",\"cache\":\"1\",\"cache_time\":\"900\"}', 0, '*');
";$q->QUERY_SQL($sql,$this->mysql_database);}

if(!$q->TABLE_EXISTS("jos_modules_menu", $this->mysql_database)){
		$sql="CREATE TABLE IF NOT EXISTS `jos_modules_menu` (
  `moduleid` int(11) NOT NULL DEFAULT '0',
  `menuid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`moduleid`,`menuid`)
)";$q->QUERY_SQL($sql,$this->mysql_database);
$sql="INSERT INTO `jos_modules_menu` (`moduleid`, `menuid`) VALUES (1, 0),(2, 0),(3, 0),(4, 0),(6, 0),(7, 0),(8, 0),(9, 0),(10, 0),(12, 0),(13, 0),(14, 0),(15, 0),(16, 0),(17, 0),(18, 0);";		
$q->QUERY_SQL($sql,$this->mysql_database);}



if(!$q->TABLE_EXISTS("jos_newsfeeds", $this->mysql_database)){
		$sql="CREATE TABLE IF NOT EXISTS `jos_newsfeeds` (
  `catid` int(11) NOT NULL DEFAULT '0',
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `alias` varchar(100) NOT NULL DEFAULT '',
  `link` varchar(200) NOT NULL DEFAULT '',
  `filename` varchar(200) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `numarticles` int(10) unsigned NOT NULL DEFAULT '1',
  `cache_time` int(10) unsigned NOT NULL DEFAULT '3600',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `rtl` tinyint(4) NOT NULL DEFAULT '0',
  `access` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `language` char(7) NOT NULL DEFAULT '',
  `params` text NOT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(10) unsigned NOT NULL DEFAULT '0',
  `created_by_alias` varchar(255) NOT NULL DEFAULT '',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(10) unsigned NOT NULL DEFAULT '0',
  `metakey` text NOT NULL,
  `metadesc` text NOT NULL,
  `metadata` text NOT NULL,
  `xreference` varchar(50) NOT NULL COMMENT 'A reference to enable linkages to external data sets.',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_state` (`published`),
  KEY `idx_catid` (`catid`),
  KEY `idx_createdby` (`created_by`),
  KEY `idx_language` (`language`),
  KEY `idx_xreference` (`xreference`)
)";$q->QUERY_SQL($sql,$this->mysql_database);}

if(!$q->TABLE_EXISTS("jos_redirect_links", $this->mysql_database)){
		$sql="CREATE TABLE IF NOT EXISTS `jos_redirect_links` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `old_url` varchar(150) NOT NULL,
  `new_url` varchar(150) NOT NULL,
  `referer` varchar(150) NOT NULL,
  `comment` varchar(255) NOT NULL,
  `published` tinyint(4) NOT NULL,
  `created_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_link_old` (`old_url`),
  KEY `idx_link_modifed` (`modified_date`)
)";$q->QUERY_SQL($sql,$this->mysql_database);}

if(!$q->TABLE_EXISTS("jos_schemas", $this->mysql_database)){
		$sql="CREATE TABLE IF NOT EXISTS `jos_schemas` (
  `extension_id` int(11) NOT NULL,
  `version_id` varchar(20) NOT NULL,
  PRIMARY KEY (`extension_id`,`version_id`)
)";$q->QUERY_SQL($sql,$this->mysql_database);$sql="INSERT INTO `jos_schemas` (`extension_id`, `version_id`) VALUES (700, '1.7.0-2011-06-06-2');";;$q->QUERY_SQL($sql,$this->mysql_database);}		

if(!$q->TABLE_EXISTS("jos_session", $this->mysql_database)){
		$sql="CREATE TABLE IF NOT EXISTS `jos_session` (
  `session_id` varchar(32) NOT NULL DEFAULT '',
  `client_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `guest` tinyint(4) unsigned DEFAULT '1',
  `time` varchar(14) DEFAULT '',
  `data` varchar(20480) DEFAULT NULL,
  `userid` int(11) DEFAULT '0',
  `username` varchar(150) DEFAULT '',
  `usertype` varchar(50) DEFAULT '',
  PRIMARY KEY (`session_id`),
  KEY `whosonline` (`guest`,`usertype`),
  KEY `userid` (`userid`),
  KEY `time` (`time`)
)";$q->QUERY_SQL($sql,$this->mysql_database);} 		

if(!$q->TABLE_EXISTS("jos_template_styles", $this->mysql_database)){
		$sql="CREATE TABLE IF NOT EXISTS `jos_template_styles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `template` varchar(50) NOT NULL DEFAULT '',
  `client_id` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `home` char(7) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `params` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_template` (`template`),
  KEY `idx_home` (`home`)
)";$q->QUERY_SQL($sql,$this->mysql_database);
$sql="INSERT INTO `jos_template_styles` (`id`, `template`, `client_id`, `home`, `title`, `params`) VALUES
(2, 'bluestork', 1, '1', 'Bluestork - Default', '{\"useRoundedCorners\":\"1\",\"showSiteName\":\"0\"}'),
(3, 'atomic', 0, '0', 'Atomic - Default', '{}'),
(4, 'beez_20', 0, '1', 'Beez2 - Default', '{\"wrapperSmall\":\"53\",\"wrapperLarge\":\"72\",\"logo\":\"images\\/joomla_black.gif\",\"sitetitle\":\"Joomla!\",\"sitedescription\":\"Open Source Content Management\",\"navposition\":\"left\",\"templatecolor\":\"personal\",\"html5\":\"0\"}'),
(5, 'hathor', 1, '0', 'Hathor - Default', '{\"showSiteName\":\"0\",\"colourChoice\":\"\",\"boldText\":\"0\"}'),
(6, 'beez5', 0, '0', 'Beez5 - Default-Fruit Shop', '{\"wrapperSmall\":\"53\",\"wrapperLarge\":\"72\",\"logo\":\"images\\/sampledata\\/fruitshop\\/fruits.gif\",\"sitetitle\":\"Matuna Market \",\"sitedescription\":\"Fruit Shop Sample Site\",\"navposition\":\"left\",\"html5\":\"0\"}');";
$q->QUERY_SQL($sql,$this->mysql_database);}


if(!$q->TABLE_EXISTS("jos_updates", $this->mysql_database)){
		$sql="CREATE TABLE IF NOT EXISTS `jos_updates` (
  `update_id` int(11) NOT NULL AUTO_INCREMENT,
  `update_site_id` int(11) DEFAULT '0',
  `extension_id` int(11) DEFAULT '0',
  `categoryid` int(11) DEFAULT '0',
  `name` varchar(100) DEFAULT '',
  `description` text NOT NULL,
  `element` varchar(100) DEFAULT '',
  `type` varchar(20) DEFAULT '',
  `folder` varchar(20) DEFAULT '',
  `client_id` tinyint(3) DEFAULT '0',
  `version` varchar(10) DEFAULT '',
  `data` text NOT NULL,
  `detailsurl` text NOT NULL,
  PRIMARY KEY (`update_id`)
)";$q->QUERY_SQL($sql,$this->mysql_database);} 

if(!$q->TABLE_EXISTS("jos_update_categories", $this->mysql_database)){
		$sql="CREATE TABLE IF NOT EXISTS `jos_update_categories` (
  `categoryid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) DEFAULT '',
  `description` text NOT NULL,
  `parent` int(11) DEFAULT '0',
  `updatesite` int(11) DEFAULT '0',
  PRIMARY KEY (`categoryid`)
)";$q->QUERY_SQL($sql,$this->mysql_database);} 		


if(!$q->TABLE_EXISTS("jos_update_sites", $this->mysql_database)){
		$sql="CREATE TABLE IF NOT EXISTS `jos_update_sites` (
  `update_site_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT '',
  `type` varchar(20) DEFAULT '',
  `location` text NOT NULL,
  `enabled` int(11) DEFAULT '0',
  PRIMARY KEY (`update_site_id`)
)";$q->QUERY_SQL($sql,$this->mysql_database);
$sql="INSERT INTO `jos_update_sites` (`update_site_id`, `name`, `type`, `location`, `enabled`) VALUES
(1, 'Joomla Core', 'collection', 'http://update.joomla.org/core/list.xml', 1),
(2, 'Joomla Extension Directory', 'collection', 'http://update.joomla.org/jed/list.xml', 1);";
$q->QUERY_SQL($sql,$this->mysql_database);
}
if(!$q->TABLE_EXISTS("jos_update_sites_extensions", $this->mysql_database)){
$sql="CREATE TABLE IF NOT EXISTS `jos_update_sites_extensions` (`update_site_id` int(11) NOT NULL DEFAULT '0', `extension_id` int(11) NOT NULL DEFAULT '0', PRIMARY KEY (`update_site_id`,`extension_id`))";$q->QUERY_SQL($sql,$this->mysql_database);
$sql="INSERT INTO `jos_update_sites_extensions` (`update_site_id`, `extension_id`) VALUES (1, 700),(2, 700);";
$q->QUERY_SQL($sql,$this->mysql_database);
}		
if(!$q->TABLE_EXISTS("jos_usergroups", $this->mysql_database)){
$sql="CREATE TABLE IF NOT EXISTS `jos_usergroups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Adjacency List Reference Id',
  `lft` int(11) NOT NULL DEFAULT '0' COMMENT 'Nested set lft.',
  `rgt` int(11) NOT NULL DEFAULT '0' COMMENT 'Nested set rgt.',
  `title` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_usergroup_parent_title_lookup` (`parent_id`,`title`),
  KEY `idx_usergroup_title_lookup` (`title`),
  KEY `idx_usergroup_adjacency_lookup` (`parent_id`),
  KEY `idx_usergroup_nested_set_lookup` (`lft`,`rgt`) USING BTREE
)"; $q->QUERY_SQL($sql,$this->mysql_database);
$sql="INSERT INTO `jos_usergroups` (`id`, `parent_id`, `lft`, `rgt`, `title`) VALUES (1, 0, 1, 20, 'Public'),(2, 1, 6, 17, 'Registered'),(3, 2, 7, 14, 'Author'),(4, 3, 8, 11, 'Editor'),(5, 4, 9, 10, 'Publisher'),(6, 1, 2, 5, 'Manager'),(7, 6, 3, 4, 'Administrator'),(8, 1, 18, 19, 'Super Users');";
$q->QUERY_SQL($sql,$this->mysql_database);}

if(!$q->TABLE_EXISTS("jos_users", $this->mysql_database)){
$sql="CREATE TABLE IF NOT EXISTS `jos_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `username` varchar(150) NOT NULL DEFAULT '',
  `email` varchar(100) NOT NULL DEFAULT '',
  `password` varchar(100) NOT NULL DEFAULT '',
  `usertype` varchar(25) NOT NULL DEFAULT '',
  `block` tinyint(4) NOT NULL DEFAULT '0',
  `sendEmail` tinyint(4) DEFAULT '0',
  `registerDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `lastvisitDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `activation` varchar(100) NOT NULL DEFAULT '',
  `params` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `usertype` (`usertype`),
  KEY `idx_name` (`name`),
  KEY `idx_block` (`block`),
  KEY `username` (`username`),
  KEY `email` (`email`))"; $q->QUERY_SQL($sql,$this->mysql_database);
$sql="INSERT INTO `jos_users` (`id`, `name`, `username`, `email`, `password`, `usertype`, `block`, `sendEmail`, `registerDate`, `lastvisitDate`, `activation`, `params`) VALUES (42, 'Super User', 'admin', 'admin@root.com', 'dc2a289928ab7fa89084479f0f21a1c6:7mPe7MZFHeAd8VXVx7RK6PiGXcfuMqv8', 'deprecated', 0, 1, '2011-08-22 14:56:56', '0000-00-00 00:00:00', '', '');";
$q->QUERY_SQL($sql,$this->mysql_database);}

if(!$q->TABLE_EXISTS("jos_user_profiles", $this->mysql_database)){
$sql="CREATE TABLE IF NOT EXISTS `jos_user_profiles` (
  `user_id` int(11) NOT NULL,
  `profile_key` varchar(100) NOT NULL,
  `profile_value` varchar(255) NOT NULL,
  `ordering` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `idx_user_id_profile_key` (`user_id`,`profile_key`)
)";$q->QUERY_SQL($sql,$this->mysql_database);}

if(!$q->TABLE_EXISTS("jos_user_usergroup_map", $this->mysql_database)){
$sql="CREATE TABLE IF NOT EXISTS `jos_user_usergroup_map` (
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Foreign Key to #__users.id',
  `group_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Foreign Key to #__usergroups.id',
  PRIMARY KEY (`user_id`,`group_id`)
)";$q->QUERY_SQL($sql,$this->mysql_database);
$sql="INSERT INTO `jos_user_usergroup_map` (`user_id`, `group_id`) VALUES (42, 8);";
$q->QUERY_SQL($sql,$this->mysql_database);}


if(!$q->TABLE_EXISTS("jos_viewlevels", $this->mysql_database)){
$sql="CREATE TABLE IF NOT EXISTS `jos_viewlevels` (  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',  `title` varchar(100) NOT NULL DEFAULT '',  `ordering` int(11) NOT NULL DEFAULT '0',  `rules` varchar(5120) NOT NULL COMMENT 'JSON encoded access control.',  PRIMARY KEY (`id`),  UNIQUE KEY `idx_assetgroup_title_lookup` (`title`))";
$q->QUERY_SQL($sql,$this->mysql_database); 
$sql="INSERT INTO `jos_viewlevels` (`id`, `title`, `ordering`, `rules`) VALUES (1, 'Public', 0, '[1]'),(2, 'Registered', 1, '[6,2,8]'),(3, 'Special', 2, '[6,3,8]');";
$q->QUERY_SQL($sql,$this->mysql_database);}

if(!$q->TABLE_EXISTS("jos_user_usergroup_map", $this->mysql_database)){
$sql="CREATE TABLE IF NOT EXISTS `jos_user_usergroup_map` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `catid` int(11) NOT NULL DEFAULT '0',
  `sid` int(11) NOT NULL DEFAULT '0',
  `title` varchar(250) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(250) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `hits` int(11) NOT NULL DEFAULT '0',
  `state` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int(11) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `archived` tinyint(1) NOT NULL DEFAULT '0',
  `approved` tinyint(1) NOT NULL DEFAULT '1',
  `access` int(11) NOT NULL DEFAULT '1',
  `params` text NOT NULL,
  `language` char(7) NOT NULL DEFAULT '',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(10) unsigned NOT NULL DEFAULT '0',
  `created_by_alias` varchar(255) NOT NULL DEFAULT '',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(10) unsigned NOT NULL DEFAULT '0',
  `metakey` text NOT NULL,
  `metadesc` text NOT NULL,
  `metadata` text NOT NULL,
  `featured` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Set if link is featured.',
  `xreference` varchar(50) NOT NULL COMMENT 'A reference to enable linkages to external data sets.',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_state` (`state`),
  KEY `idx_catid` (`catid`),
  KEY `idx_createdby` (`created_by`),
  KEY `idx_featured_catid` (`featured`,`catid`),
  KEY `idx_language` (`language`),
  KEY `idx_xreference` (`xreference`)
)";$q->QUERY_SQL($sql,$this->mysql_database);}
if(!$q->TABLE_EXISTS("jos_banner_clients", $this->mysql_database)){
$sql="
CREATE TABLE IF NOT EXISTS `jos_banner_clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `contact` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `extrainfo` text NOT NULL,
  `state` tinyint(3) NOT NULL DEFAULT '0',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `metakey` text NOT NULL,
  `own_prefix` tinyint(4) NOT NULL DEFAULT '0',
  `metakey_prefix` varchar(255) NOT NULL DEFAULT '',
  `purchase_type` tinyint(4) NOT NULL DEFAULT '-1',
  `track_clicks` tinyint(4) NOT NULL DEFAULT '-1',
  `track_impressions` tinyint(4) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`id`),
  KEY `idx_own_prefix` (`own_prefix`),
  KEY `idx_metakey_prefix` (`metakey_prefix`)
) ";$q->QUERY_SQL($sql,$this->mysql_database);}


if(!$q->TABLE_EXISTS("jos_weblinks", $this->mysql_database)){
$sql="CREATE TABLE IF NOT EXISTS `jos_weblinks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `catid` int(11) NOT NULL DEFAULT '0',
  `sid` int(11) NOT NULL DEFAULT '0',
  `title` varchar(250) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(250) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `hits` int(11) NOT NULL DEFAULT '0',
  `state` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int(11) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `archived` tinyint(1) NOT NULL DEFAULT '0',
  `approved` tinyint(1) NOT NULL DEFAULT '1',
  `access` int(11) NOT NULL DEFAULT '1',
  `params` text NOT NULL,
  `language` char(7) NOT NULL DEFAULT '',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(10) unsigned NOT NULL DEFAULT '0',
  `created_by_alias` varchar(255) NOT NULL DEFAULT '',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(10) unsigned NOT NULL DEFAULT '0',
  `metakey` text NOT NULL,
  `metadesc` text NOT NULL,
  `metadata` text NOT NULL,
  `featured` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Set if link is featured.',
  `xreference` varchar(50) NOT NULL COMMENT 'A reference to enable linkages to external data sets.',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_state` (`state`),
  KEY `idx_catid` (`catid`),
  KEY `idx_createdby` (`created_by`),
  KEY `idx_featured_catid` (`featured`,`catid`),
  KEY `idx_language` (`language`),
  KEY `idx_xreference` (`xreference`)
)";$q->QUERY_SQL($sql,$this->mysql_database);}
		
	}	
	
}