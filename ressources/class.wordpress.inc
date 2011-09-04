<?php

class wordpress{
	var $groupware;
	var $ou;
	var $www_dir;
	var $ServerPort;	
	var $servername;
	var $database;
	var $uid;
	var $rebuildb=false;
	var $mysql_password=null;	
	var $mysql_username=null;
	function wordpress($servername){
		if($servername<>null){$this->servername=$servername;}
		$this->load();
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
			$this->mysql_password=$ligne["mysql_password"];
			$this->mysql_username=$ligne["mysql_username"];			
			if($this->www_dir==null){$this->www_dir="/var/www/$this->servername";}
			writelogs("[$this->servername] WORDPRESS mysql user=$this->mysql_username",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			
			$this->database="wordpress_".time();
			if($ligne["mysql_database"]<>null){$this->database=$ligne["mysql_database"];}else{
				$sql="UPDATE freeweb SET mysql_database='{$this->database}' WHERE servername='$this->servername'";
				$q->QUERY_SQL($sql,"artica_backup");
			}
			$this->uid=$ligne["uid"];
			
	}	
	
	public function CheckInstall(){
		if(!$this->checkFiles()){
		    writelogs("[$this->servername] WORDPRESS Installing core application",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
			shell_exec("/bin/cp -rf /usr/local/share/artica/wordpress_src/* $this->www_dir/");
		}
		
		$this->InstallDB();
		
	}
	
	private function InstallDB(){
		$q=new mysql();
		writelogs("[$this->servername] WORDPRESS mysql user=$this->mysql_username",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
		if($this->mysql_username==null){$this->mysql_username=$q->mysql_admin;}
		if($this->mysql_password==null){$this->mysql_password=$q->mysql_password;}
		
		$f[]="<?php";
		$f[]="define('DB_NAME', '$this->database');";
		$f[]="define('DB_USER', '$this->mysql_username');";
		$f[]="define('DB_PASSWORD', '$this->mysql_password');";
		$f[]="define('DB_HOST', '$q->mysql_server');";
		$f[]="define('DB_CHARSET', 'utf8');";
		$f[]="define('DB_COLLATE', '');";
		$f[]="define('AUTH_KEY',         '$this->servername');";
		$f[]="define('SECURE_AUTH_KEY',  '$this->servername');";
		$f[]="define('LOGGED_IN_KEY',    '$this->servername');";
		$f[]="define('NONCE_KEY',        '$this->servername');";
		$f[]="define('AUTH_SALT',        '$this->servername');";
		$f[]="define('SECURE_AUTH_SALT', '$this->servername');";
		$f[]="define('LOGGED_IN_SALT',   '$this->servername');";
		$f[]="define('NONCE_SALT',       '$this->servername');";
		$f[]="\$table_prefix  = 'wp_';";
		$f[]="define('WPLANG', '');";
		$f[]="define('WP_DEBUG', false);";
		$f[]="if ( !defined('ABSPATH') )";
		$f[]="	define('ABSPATH', dirname(__FILE__) . '/');";
		$f[]="require_once(ABSPATH . 'wp-settings.php');";	
		@file_put_contents("$this->www_dir/wp-config.php", @implode("\n", $f));	
		
		
		
		
	}
	
	
	
	private function checkFiles(){
			$f[]="wp-cron.php";
			$f[]="wp-pass.php";
			$f[]="wp-mail.php";
			$f[]="wp-feed.php";
			$f[]="wp-includes/ms-load.php";
			$f[]="wp-includes/functions.wp-scripts.php";
			$f[]="wp-includes/default-widgets.php";
			$f[]="wp-includes/class-feed.php";
			$f[]="wp-includes/ms-files.php";
			$f[]="wp-includes/class-snoopy.php";
			$f[]="wp-includes/template-loader.php";
			$f[]="wp-includes/nav-menu-template.php";
			$f[]="wp-includes/class-wp-error.php";
			$f[]="wp-includes/ms-blogs.php";
			$f[]="wp-includes/ms-default-constants.php";
			$f[]="wp-includes/class-phpmailer.php";
			$f[]="wp-includes/category-template.php";
			$f[]="wp-includes/ms-default-filters.php";
			$f[]="wp-includes/query.php";
			$f[]="wp-includes/comment-template.php";
			$f[]="wp-includes/media.php";
			$f[]="wp-includes/link-template.php";
			$f[]="wp-includes/ms-settings.php";
			$f[]="wp-includes/theme-compat/comments-popup.php";
			$f[]="wp-includes/theme-compat/sidebar.php";
			$f[]="wp-includes/theme-compat/header.php";
			$f[]="wp-includes/theme-compat/footer.php";
			$f[]="wp-includes/theme-compat/comments.php";
			$f[]="wp-includes/taxonomy.php";
			$f[]="wp-includes/script-loader.php";
			$f[]="wp-includes/cron.php";
			$f[]="wp-includes/ms-deprecated.php";
			$f[]="wp-includes/meta.php";
			$f[]="wp-includes/theme.php";
			$f[]="wp-includes/capabilities.php";
			$f[]="wp-includes/Text/Diff/Renderer/inline.php";
			$f[]="wp-includes/Text/Diff/Renderer.php";
			$f[]="wp-includes/Text/Diff/Engine/native.php";
			$f[]="wp-includes/Text/Diff/Engine/string.php";
			$f[]="wp-includes/Text/Diff/Engine/xdiff.php";
			$f[]="wp-includes/Text/Diff/Engine/shell.php";
			$f[]="wp-includes/Text/Diff.php";
			$f[]="wp-includes/feed-rss.php";
			$f[]="wp-includes/compat.php";
			$f[]="wp-includes/js/tinymce/plugins/spellchecker/classes/EnchantSpell.php";
			$f[]="wp-includes/js/tinymce/plugins/spellchecker/classes/utils/Logger.php";
			$f[]="wp-includes/js/tinymce/plugins/spellchecker/classes/utils/JSON.php";
			$f[]="wp-includes/js/tinymce/plugins/spellchecker/classes/SpellChecker.php";
			$f[]="wp-includes/js/tinymce/plugins/spellchecker/classes/PSpell.php";
			$f[]="wp-includes/js/tinymce/plugins/spellchecker/classes/GoogleSpell.php";
			$f[]="wp-includes/js/tinymce/plugins/spellchecker/classes/PSpellShell.php";
			$f[]="wp-includes/js/tinymce/plugins/spellchecker/includes/general.php";
			$f[]="wp-includes/js/tinymce/plugins/spellchecker/config.php";
			$f[]="wp-includes/js/tinymce/plugins/spellchecker/rpc.php";
			$f[]="wp-includes/js/tinymce/wp-tinymce.php";
			$f[]="wp-includes/js/tinymce/langs/wp-langs.php";
			$f[]="wp-includes/js/tinymce/wp-mce-help.php";
			$f[]="wp-includes/rewrite.php";
			$f[]="wp-includes/class-oembed.php";
			$f[]="wp-includes/class.wp-dependencies.php";
			$f[]="wp-includes/wp-db.php";
			$f[]="wp-includes/class.wp-styles.php";
			$f[]="wp-includes/class-pop3.php";
			$f[]="wp-includes/feed-rdf.php";
			$f[]="wp-includes/ms-functions.php";
			$f[]="wp-includes/post.php";
			$f[]="wp-includes/feed-rss2-comments.php";
			$f[]="wp-includes/shortcodes.php";
			$f[]="wp-includes/update.php";
			$f[]="wp-includes/widgets.php";
			$f[]="wp-includes/class-wp-http-ixr-client.php";
			$f[]="wp-includes/feed.php";
			$f[]="wp-includes/bookmark-template.php";
			$f[]="wp-includes/registration-functions.php";
			$f[]="wp-includes/class-IXR.php";
			$f[]="wp-includes/version.php";
			$f[]="wp-includes/user.php";
			$f[]="wp-includes/default-constants.php";
			$f[]="wp-includes/deprecated.php";
			$f[]="wp-includes/default-filters.php";
			$f[]="wp-includes/bookmark.php";
			$f[]="wp-includes/feed-rss2.php";
			$f[]="wp-includes/feed-atom.php";
			$f[]="wp-includes/author-template.php";
			$f[]="wp-includes/rss.php";
			$f[]="wp-includes/class-wp-ajax-response.php";
			$f[]="wp-includes/functions.php";
			$f[]="wp-includes/general-template.php";
			$f[]="wp-includes/cache.php";
			$f[]="wp-includes/nav-menu.php";
			$f[]="wp-includes/admin-bar.php";
			$f[]="wp-includes/http.php";
			$f[]="wp-includes/class-wp-xmlrpc-server.php";
			$f[]="wp-includes/pluggable.php";
			$f[]="wp-includes/class-wp-admin-bar.php";
			$f[]="wp-includes/load.php";
			$f[]="wp-includes/comment.php";
			$f[]="wp-includes/category.php";
			$f[]="wp-includes/l10n.php";
			$f[]="wp-includes/post-thumbnail-template.php";
			$f[]="wp-includes/atomlib.php";
			$f[]="wp-includes/wp-diff.php";
			$f[]="wp-includes/class-simplepie.php";
			$f[]="wp-includes/class-json.php";
			$f[]="wp-includes/class-http.php";
			$f[]="wp-includes/vars.php";
			$f[]="wp-includes/locale.php";
			$f[]="wp-includes/class-phpass.php";
			$f[]="wp-includes/class-smtp.php";
			$f[]="wp-includes/registration.php";
			$f[]="wp-includes/functions.wp-styles.php";
			$f[]="wp-includes/class.wp-scripts.php";
			$f[]="wp-includes/post-template.php";
			$f[]="wp-includes/formatting.php";
			$f[]="wp-includes/pomo/po.php";
			$f[]="wp-includes/pomo/translations.php";
			$f[]="wp-includes/pomo/entry.php";
			$f[]="wp-includes/pomo/mo.php";
			$f[]="wp-includes/pomo/streams.php";
			$f[]="wp-includes/kses.php";
			$f[]="wp-includes/feed-atom-comments.php";
			$f[]="wp-includes/class-wp.php";
			$f[]="wp-includes/canonical.php";
			$f[]="wp-includes/pluggable-deprecated.php";
			$f[]="wp-includes/class-wp-walker.php";
			$f[]="wp-includes/rss-functions.php";
			$f[]="wp-includes/plugin.php";
			$f[]="wp-rss2.php";
			$f[]="index.php";
			$f[]="wp-settings.php";
			$f[]="wp-atom.php";
			$f[]="wp-rdf.php";
			$f[]="wp-commentsrss2.php";
			$f[]="wp-activate.php";
			$f[]="wp-admin/import.php";
			$f[]="wp-admin/ms-sites.php";
			$f[]="wp-admin/async-upload.php";
			$f[]="wp-admin/network.php";
			$f[]="wp-admin/edit-tags.php";
			$f[]="wp-admin/options-privacy.php";
			$f[]="wp-admin/edit-form-advanced.php";
			$f[]="wp-admin/edit.php";
			$f[]="wp-admin/media.php";
			$f[]="wp-admin/install-helper.php";
			$f[]="wp-admin/media-new.php";
			$f[]="wp-admin/options-reading.php";
			$f[]="wp-admin/edit-tag-form.php";
			$f[]="wp-admin/install.php";
			$f[]="wp-admin/admin-footer.php";
			$f[]="wp-admin/ms-users.php";
			$f[]="wp-admin/options-head.php";
			$f[]="wp-admin/link-parse-opml.php";
			$f[]="wp-admin/admin-functions.php";
			$f[]="wp-admin/post-new.php";
			$f[]="wp-admin/ms-upgrade-network.php";
			$f[]="wp-admin/profile.php";
			$f[]="wp-admin/edit-comments.php";
			$f[]="wp-admin/link.php";
			$f[]="wp-admin/js/revisions-js.php";
			$f[]="wp-admin/index.php";
			$f[]="wp-admin/theme-editor.php";
			$f[]="wp-admin/menu-header.php";
			$f[]="wp-admin/edit-link-form.php";
			$f[]="wp-admin/options-writing.php";
			$f[]="wp-admin/load-scripts.php";
			$f[]="wp-admin/plugin-editor.php";
			$f[]="wp-admin/post.php";
			$f[]="wp-admin/admin-ajax.php";
			$f[]="wp-admin/options-general.php";
			$f[]="wp-admin/update.php";
			$f[]="wp-admin/media-upload.php";
			$f[]="wp-admin/freedoms.php";
			$f[]="wp-admin/widgets.php";
			$f[]="wp-admin/setup-config.php";
			$f[]="wp-admin/custom-background.php";
			$f[]="wp-admin/credits.php";
			$f[]="wp-admin/ms-options.php";
			$f[]="wp-admin/includes/import.php";
			$f[]="wp-admin/includes/template.php";
			$f[]="wp-admin/includes/class-wp-terms-list-table.php";
			$f[]="wp-admin/includes/media.php";
			$f[]="wp-admin/includes/class-wp-plugins-list-table.php";
			$f[]="wp-admin/includes/taxonomy.php";
			$f[]="wp-admin/includes/ms-deprecated.php";
			$f[]="wp-admin/includes/class-wp-filesystem-ftpext.php";
			$f[]="wp-admin/includes/class-wp-links-list-table.php";
			$f[]="wp-admin/includes/theme.php";
			$f[]="wp-admin/includes/class-ftp-pure.php";
			$f[]="wp-admin/includes/class-ftp-sockets.php";
			$f[]="wp-admin/includes/image.php";
			$f[]="wp-admin/includes/ms.php";
			$f[]="wp-admin/includes/class-wp-media-list-table.php";
			$f[]="wp-admin/includes/class-pclzip.php";
			$f[]="wp-admin/includes/class-wp-importer.php";
			$f[]="wp-admin/includes/meta-boxes.php";
			$f[]="wp-admin/includes/class-wp-filesystem-base.php";
			$f[]="wp-admin/includes/post.php";
			$f[]="wp-admin/includes/class-ftp.php";
			$f[]="wp-admin/includes/class-wp-comments-list-table.php";
			$f[]="wp-admin/includes/update.php";
			$f[]="wp-admin/includes/widgets.php";
			$f[]="wp-admin/includes/class-wp-users-list-table.php";
			$f[]="wp-admin/includes/class-wp-filesystem-direct.php";
			$f[]="wp-admin/includes/internal-linking.php";
			$f[]="wp-admin/includes/class-wp-filesystem-ftpsockets.php";
			$f[]="wp-admin/includes/dashboard.php";
			$f[]="wp-admin/includes/class-wp-posts-list-table.php";
			$f[]="wp-admin/includes/file.php";
			$f[]="wp-admin/includes/image-edit.php";
			$f[]="wp-admin/includes/class-wp-filesystem-ssh2.php";
			$f[]="wp-admin/includes/continents-cities.php";
			$f[]="wp-admin/includes/class-wp-upgrader.php";
			$f[]="wp-admin/includes/user.php";
			$f[]="wp-admin/includes/deprecated.php";
			$f[]="wp-admin/includes/bookmark.php";
			$f[]="wp-admin/includes/class-wp-plugin-install-list-table.php";
			$f[]="wp-admin/includes/nav-menu.php";
			$f[]="wp-admin/includes/theme-install.php";
			$f[]="wp-admin/includes/schema.php";
			$f[]="wp-admin/includes/upgrade.php";
			$f[]="wp-admin/includes/update-core.php";
			$f[]="wp-admin/includes/class-wp-ms-sites-list-table.php";
			$f[]="wp-admin/includes/manifest.php";
			$f[]="wp-admin/includes/class-wp-list-table.php";
			$f[]="wp-admin/includes/misc.php";
			$f[]="wp-admin/includes/plugin-install.php";
			$f[]="wp-admin/includes/comment.php";
			$f[]="wp-admin/includes/list-table.php";
			$f[]="wp-admin/includes/admin.php";
			$f[]="wp-admin/includes/class-wp-theme-install-list-table.php";
			$f[]="wp-admin/includes/class-wp-themes-list-table.php";
			$f[]="wp-admin/includes/class-wp-ms-themes-list-table.php";
			$f[]="wp-admin/includes/menu.php";
			$f[]="wp-admin/includes/export.php";
			$f[]="wp-admin/includes/class-wp-ms-users-list-table.php";
			$f[]="wp-admin/includes/plugin.php";
			$f[]="wp-admin/revision.php";
			$f[]="wp-admin/options.php";
			$f[]="wp-admin/user/profile.php";
			$f[]="wp-admin/user/index.php";
			$f[]="wp-admin/user/admin.php";
			$f[]="wp-admin/user/user-edit.php";
			$f[]="wp-admin/user/menu.php";
			$f[]="wp-admin/user/index-extra.php";
			$f[]="wp-admin/edit-form-comment.php";
			$f[]="wp-admin/theme-install.php";
			$f[]="wp-admin/users.php";
			$f[]="wp-admin/upgrade.php";
			$f[]="wp-admin/moderation.php";
			$f[]="wp-admin/update-core.php";
			$f[]="wp-admin/plugins.php";
			$f[]="wp-admin/user-new.php";
			$f[]="wp-admin/admin-post.php";
			$f[]="wp-admin/nav-menus.php";
			$f[]="wp-admin/options-discussion.php";
			$f[]="wp-admin/ms-edit.php";
			$f[]="wp-admin/load-styles.php";
			$f[]="wp-admin/plugin-install.php";
			$f[]="wp-admin/comment.php";
			$f[]="wp-admin/ms-admin.php";
			$f[]="wp-admin/admin.php";
			$f[]="wp-admin/gears-manifest.php";
			$f[]="wp-admin/custom-header.php";
			$f[]="wp-admin/link-add.php";
			$f[]="wp-admin/ms-themes.php";
			$f[]="wp-admin/user-edit.php";
			$f[]="wp-admin/ms-delete-site.php";
			$f[]="wp-admin/my-sites.php";
			$f[]="wp-admin/menu.php";
			$f[]="wp-admin/network/site-info.php";
			$f[]="wp-admin/network/edit.php";
			$f[]="wp-admin/network/profile.php";
			$f[]="wp-admin/network/sites.php";
			$f[]="wp-admin/network/site-users.php";
			$f[]="wp-admin/network/setup.php";
			$f[]="wp-admin/network/index.php";
			$f[]="wp-admin/network/theme-editor.php";
			$f[]="wp-admin/network/site-settings.php";
			$f[]="wp-admin/network/site-themes.php";
			$f[]="wp-admin/network/plugin-editor.php";
			$f[]="wp-admin/network/update.php";
			$f[]="wp-admin/network/settings.php";
			$f[]="wp-admin/network/theme-install.php";
			$f[]="wp-admin/network/users.php";
			$f[]="wp-admin/network/upgrade.php";
			$f[]="wp-admin/network/update-core.php";
			$f[]="wp-admin/network/plugins.php";
			$f[]="wp-admin/network/user-new.php";
			$f[]="wp-admin/network/site-new.php";
			$f[]="wp-admin/network/plugin-install.php";
			$f[]="wp-admin/network/admin.php";
			$f[]="wp-admin/network/user-edit.php";
			$f[]="wp-admin/network/menu.php";
			$f[]="wp-admin/network/index-extra.php";
			$f[]="wp-admin/network/themes.php";
			$f[]="wp-admin/link-manager.php";
			$f[]="wp-admin/options-media.php";
			$f[]="wp-admin/tools.php";
			$f[]="wp-admin/index-extra.php";
			$f[]="wp-admin/themes.php";
			$f[]="wp-admin/export.php";
			$f[]="wp-admin/upload.php";
			$f[]="wp-admin/press-this.php";
			$f[]="wp-admin/maint/repair.php";
			$f[]="wp-admin/upgrade-functions.php";
			$f[]="wp-admin/admin-header.php";
			$f[]="wp-admin/options-permalink.php";
			$f[]="wp-content/plugins/index.php";
			$f[]="wp-content/plugins/akismet/akismet.php";
			$f[]="wp-content/plugins/akismet/legacy.php";
			$f[]="wp-content/plugins/akismet/admin.php";
			$f[]="wp-content/plugins/akismet/widget.php";
			$f[]="wp-content/plugins/hello.php";
			$f[]="wp-content/index.php";
			$f[]="wp-content/themes/index.php";
			$f[]="wp-content/themes/twentyeleven/content-gallery.php";
			$f[]="wp-content/themes/twentyeleven/search.php";
			$f[]="wp-content/themes/twentyeleven/sidebar-page.php";
			$f[]="wp-content/themes/twentyeleven/sidebar-footer.php";
			$f[]="wp-content/themes/twentyeleven/single.php";
			$f[]="wp-content/themes/twentyeleven/tag.php";
			$f[]="wp-content/themes/twentyeleven/404.php";
			$f[]="wp-content/themes/twentyeleven/image.php";
			$f[]="wp-content/themes/twentyeleven/searchform.php";
			$f[]="wp-content/themes/twentyeleven/index.php";
			$f[]="wp-content/themes/twentyeleven/content-featured.php";
			$f[]="wp-content/themes/twentyeleven/author.php";
			$f[]="wp-content/themes/twentyeleven/inc/theme-options.php";
			$f[]="wp-content/themes/twentyeleven/inc/widgets.php";
			$f[]="wp-content/themes/twentyeleven/archive.php";
			$f[]="wp-content/themes/twentyeleven/sidebar.php";
			$f[]="wp-content/themes/twentyeleven/content-image.php";
			$f[]="wp-content/themes/twentyeleven/header.php";
			$f[]="wp-content/themes/twentyeleven/content-status.php";
			$f[]="wp-content/themes/twentyeleven/content-quote.php";
			$f[]="wp-content/themes/twentyeleven/functions.php";
			$f[]="wp-content/themes/twentyeleven/content-aside.php";
			$f[]="wp-content/themes/twentyeleven/content-link.php";
			$f[]="wp-content/themes/twentyeleven/category.php";
			$f[]="wp-content/themes/twentyeleven/footer.php";
			$f[]="wp-content/themes/twentyeleven/page.php";
			$f[]="wp-content/themes/twentyeleven/content-single.php";
			$f[]="wp-content/themes/twentyeleven/showcase.php";
			$f[]="wp-content/themes/twentyeleven/content-page.php";
			$f[]="wp-content/themes/twentyeleven/comments.php";
			$f[]="wp-content/themes/twentyeleven/content.php";
			$f[]="wp-content/themes/twentyeleven/content-intro.php";
			$f[]="wp-content/themes/twentyten/search.php";
			$f[]="wp-content/themes/twentyten/sidebar-footer.php";
			$f[]="wp-content/themes/twentyten/single.php";
			$f[]="wp-content/themes/twentyten/loop-single.php";
			$f[]="wp-content/themes/twentyten/tag.php";
			$f[]="wp-content/themes/twentyten/404.php";
			$f[]="wp-content/themes/twentyten/index.php";
			$f[]="wp-content/themes/twentyten/loop-attachment.php";
			$f[]="wp-content/themes/twentyten/author.php";
			$f[]="wp-content/themes/twentyten/archive.php";
			$f[]="wp-content/themes/twentyten/sidebar.php";
			$f[]="wp-content/themes/twentyten/header.php";
			$f[]="wp-content/themes/twentyten/functions.php";
			$f[]="wp-content/themes/twentyten/attachment.php";
			$f[]="wp-content/themes/twentyten/category.php";
			$f[]="wp-content/themes/twentyten/footer.php";
			$f[]="wp-content/themes/twentyten/loop.php";
			$f[]="wp-content/themes/twentyten/page.php";
			$f[]="wp-content/themes/twentyten/onecolumn-page.php";
			$f[]="wp-content/themes/twentyten/comments.php";
			$f[]="wp-content/themes/twentyten/loop-page.php";
			$f[]="wp-signup.php";
			$f[]="wp-register.php";
			$f[]="wp-login.php";
			$f[]="wp-blog-header.php";
			$f[]="xmlrpc.php";
			$f[]="wp-load.php";
			$f[]="wp-app.php";
			$f[]="wp-links-opml.php";
			$f[]="wp-trackback.php";
			$f[]="wp-rss.php";
			$f[]="wp-comments-post.php";		
		
		while (list ($none, $xpath) = each ($f) ){
			$testfile="$this->www_dir/$xpath";
			if(!is_file($testfile)){
				if(!is_dir($testfile)){
					writelogs("[$this->servername] WORDPRESS $testfile no such file or directory",__CLASS__.'/'.__FUNCTION__,__FILE__,__LINE__);
					return false;
				}
			}
			
		}
		
		return true;			
		
		
	}
	
	
}