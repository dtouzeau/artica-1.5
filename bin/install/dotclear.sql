CREATE TABLE IF NOT EXISTS `dotclear_blog` (
  `blog_id` varchar(32) collate utf8_bin NOT NULL,
  `blog_uid` varchar(32) collate utf8_bin NOT NULL,
  `blog_creadt` datetime NOT NULL default '1970-01-01 00:00:00',
  `blog_upddt` datetime NOT NULL default '1970-01-01 00:00:00',
  `blog_url` varchar(255) collate utf8_bin NOT NULL,
  `blog_name` varchar(255) collate utf8_bin NOT NULL,
  `blog_desc` longtext collate utf8_bin,
  `blog_status` smallint(6) NOT NULL default '1',
  PRIMARY KEY  (`blog_id`),
  KEY `dotclear_idx_blog_blog_upddt` USING BTREE (`blog_upddt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `dotclear_blog` (`blog_id`, `blog_uid`, `blog_creadt`, `blog_upddt`, `blog_url`, `blog_name`, `blog_desc`, `blog_status`) VALUES
('default', '2a40ef3067279b07a1ed2b7e3f997afd', '2009-01-25 19:35:58', '2009-01-25 19:35:59', 'http://localhost:82/index.php?', 'My first blog', NULL, 1);

CREATE TABLE IF NOT EXISTS `dotclear_category` (
  `cat_id` bigint(20) NOT NULL,
  `blog_id` varchar(32) collate utf8_bin NOT NULL,
  `cat_title` varchar(255) collate utf8_bin NOT NULL,
  `cat_url` varchar(255) collate utf8_bin NOT NULL,
  `cat_desc` longtext collate utf8_bin,
  `cat_position` int(11) default '0',
  `cat_lft` int(11) default NULL,
  `cat_rgt` int(11) default NULL,
  PRIMARY KEY  (`cat_id`),
  UNIQUE KEY `dotclear_uk_cat_url` (`cat_url`,`blog_id`),
  KEY `dotclear_idx_category_blog_id` USING BTREE (`blog_id`),
  KEY `dotclear_idx_category_cat_lft_blog_id` USING BTREE (`blog_id`,`cat_lft`),
  KEY `dotclear_idx_category_cat_rgt_blog_id` USING BTREE (`blog_id`,`cat_rgt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


CREATE TABLE IF NOT EXISTS `dotclear_comment` (
  `comment_id` bigint(20) NOT NULL,
  `post_id` bigint(20) NOT NULL,
  `comment_dt` datetime NOT NULL default '1970-01-01 00:00:00',
  `comment_tz` varchar(128) collate utf8_bin NOT NULL default 'UTC',
  `comment_upddt` datetime NOT NULL default '1970-01-01 00:00:00',
  `comment_author` varchar(255) collate utf8_bin default NULL,
  `comment_email` varchar(255) collate utf8_bin default NULL,
  `comment_site` varchar(255) collate utf8_bin default NULL,
  `comment_content` longtext collate utf8_bin,
  `comment_words` longtext collate utf8_bin,
  `comment_ip` varchar(39) collate utf8_bin default NULL,
  `comment_status` smallint(6) default '0',
  `comment_spam_status` varchar(128) collate utf8_bin default '0',
  `comment_spam_filter` varchar(32) collate utf8_bin default NULL,
  `comment_trackback` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`comment_id`),
  KEY `dotclear_idx_comment_post_id` USING BTREE (`post_id`),
  KEY `dotclear_idx_comment_post_id_dt_status` USING BTREE (`post_id`,`comment_dt`,`comment_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


CREATE TABLE IF NOT EXISTS `dotclear_link` (
  `link_id` bigint(20) NOT NULL,
  `blog_id` varchar(32) collate utf8_bin NOT NULL,
  `link_href` varchar(255) collate utf8_bin NOT NULL,
  `link_title` varchar(255) collate utf8_bin NOT NULL,
  `link_desc` varchar(255) collate utf8_bin default NULL,
  `link_lang` varchar(5) collate utf8_bin default NULL,
  `link_xfn` varchar(255) collate utf8_bin default NULL,
  `link_position` int(11) NOT NULL default '0',
  PRIMARY KEY  (`link_id`),
  KEY `dotclear_idx_link_blog_id` USING BTREE (`blog_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


CREATE TABLE IF NOT EXISTS `dotclear_log` (
  `log_id` bigint(20) NOT NULL,
  `user_id` varchar(32) collate utf8_bin default NULL,
  `log_table` varchar(255) collate utf8_bin NOT NULL,
  `log_dt` datetime NOT NULL default '1970-01-01 00:00:00',
  `log_ip` varchar(39) collate utf8_bin NOT NULL,
  `log_msg` varchar(255) collate utf8_bin NOT NULL,
  PRIMARY KEY  (`log_id`),
  KEY `dotclear_idx_log_user_id` USING BTREE (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


CREATE TABLE IF NOT EXISTS `dotclear_media` (
  `media_id` bigint(20) NOT NULL,
  `user_id` varchar(32) collate utf8_bin NOT NULL,
  `media_path` varchar(255) collate utf8_bin NOT NULL,
  `media_title` varchar(255) collate utf8_bin NOT NULL,
  `media_file` varchar(255) collate utf8_bin NOT NULL,
  `media_dir` varchar(255) collate utf8_bin NOT NULL default '.',
  `media_meta` longtext collate utf8_bin,
  `media_dt` datetime NOT NULL default '1970-01-01 00:00:00',
  `media_creadt` datetime NOT NULL default '1970-01-01 00:00:00',
  `media_upddt` datetime NOT NULL default '1970-01-01 00:00:00',
  `media_private` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`media_id`),
  KEY `dotclear_idx_media_user_id` USING BTREE (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;



CREATE TABLE IF NOT EXISTS `dotclear_meta` (
  `meta_id` varchar(255) collate utf8_bin NOT NULL,
  `meta_type` varchar(64) collate utf8_bin NOT NULL,
  `post_id` bigint(20) NOT NULL,
  PRIMARY KEY  (`meta_id`,`meta_type`,`post_id`),
  KEY `dotclear_idx_meta_post_id` USING BTREE (`post_id`),
  KEY `dotclear_idx_meta_meta_type` USING BTREE (`meta_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


CREATE TABLE IF NOT EXISTS `dotclear_permissions` (
  `user_id` varchar(32) collate utf8_bin NOT NULL,
  `blog_id` varchar(32) collate utf8_bin NOT NULL,
  `permissions` longtext collate utf8_bin,
  PRIMARY KEY  (`user_id`,`blog_id`),
  KEY `dotclear_idx_permissions_blog_id` USING BTREE (`blog_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


CREATE TABLE IF NOT EXISTS `dotclear_ping` (
  `post_id` bigint(20) NOT NULL,
  `ping_url` varchar(255) collate utf8_bin NOT NULL,
  `ping_dt` datetime NOT NULL default '1970-01-01 00:00:00',
  PRIMARY KEY  (`post_id`,`ping_url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;



CREATE TABLE IF NOT EXISTS `dotclear_post` (
  `post_id` bigint(20) NOT NULL,
  `blog_id` varchar(32) collate utf8_bin NOT NULL,
  `user_id` varchar(32) collate utf8_bin NOT NULL,
  `cat_id` bigint(20) default NULL,
  `post_dt` datetime NOT NULL default '1970-01-01 00:00:00',
  `post_tz` varchar(128) collate utf8_bin NOT NULL default 'UTC',
  `post_creadt` datetime NOT NULL default '1970-01-01 00:00:00',
  `post_upddt` datetime NOT NULL default '1970-01-01 00:00:00',
  `post_password` varchar(32) collate utf8_bin default NULL,
  `post_type` varchar(32) collate utf8_bin NOT NULL default 'post',
  `post_format` varchar(32) collate utf8_bin NOT NULL default 'xhtml',
  `post_url` varchar(255) collate utf8_bin NOT NULL,
  `post_lang` varchar(5) collate utf8_bin default NULL,
  `post_title` varchar(255) collate utf8_bin default NULL,
  `post_excerpt` longtext collate utf8_bin,
  `post_excerpt_xhtml` longtext collate utf8_bin,
  `post_content` longtext collate utf8_bin,
  `post_content_xhtml` longtext collate utf8_bin NOT NULL,
  `post_notes` longtext collate utf8_bin,
  `post_words` longtext collate utf8_bin,
  `post_status` smallint(6) NOT NULL default '0',
  `post_selected` smallint(6) NOT NULL default '0',
  `post_position` int(11) NOT NULL default '0',
  `post_open_comment` smallint(6) NOT NULL default '0',
  `post_open_tb` smallint(6) NOT NULL default '0',
  `nb_comment` int(11) NOT NULL default '0',
  `nb_trackback` int(11) NOT NULL default '0',
  `post_meta` longtext collate utf8_bin,
  PRIMARY KEY  (`post_id`),
  UNIQUE KEY `dotclear_uk_post_url` (`post_url`,`post_type`,`blog_id`),
  KEY `dotclear_idx_post_cat_id` USING BTREE (`cat_id`),
  KEY `dotclear_idx_post_user_id` USING BTREE (`user_id`),
  KEY `dotclear_idx_post_blog_id` USING BTREE (`blog_id`),
  KEY `dotclear_idx_post_post_dt` USING BTREE (`post_dt`),
  KEY `dotclear_idx_post_post_dt_post_id` USING BTREE (`post_dt`,`post_id`),
  KEY `dotclear_idx_blog_post_post_dt_post_id` USING BTREE (`blog_id`,`post_dt`,`post_id`),
  KEY `dotclear_idx_blog_post_post_status` USING BTREE (`blog_id`,`post_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


CREATE TABLE IF NOT EXISTS `dotclear_post_media` (
  `media_id` bigint(20) NOT NULL,
  `post_id` bigint(20) NOT NULL,
  PRIMARY KEY  (`media_id`,`post_id`),
  KEY `dotclear_idx_post_media_post_id` USING BTREE (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;



CREATE TABLE IF NOT EXISTS `dotclear_session` (
  `ses_id` varchar(40) collate utf8_bin NOT NULL,
  `ses_time` int(11) NOT NULL default '0',
  `ses_start` int(11) NOT NULL default '0',
  `ses_value` longtext collate utf8_bin NOT NULL,
  PRIMARY KEY  (`ses_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


CREATE TABLE IF NOT EXISTS `dotclear_setting` (
  `setting_id` varchar(255) collate utf8_bin NOT NULL,
  `blog_id` varchar(32) collate utf8_bin default NULL,
  `setting_ns` varchar(32) collate utf8_bin NOT NULL default 'system',
  `setting_value` longtext collate utf8_bin,
  `setting_type` varchar(8) collate utf8_bin NOT NULL default 'string',
  `setting_label` longtext collate utf8_bin,
  UNIQUE KEY `dotclear_uk_setting` (`setting_id`,`blog_id`),
  KEY `dotclear_idx_setting_blog_id` USING BTREE (`blog_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


INSERT INTO `dotclear_setting` (`setting_id`, `blog_id`, `setting_ns`, `setting_value`, `setting_type`, `setting_label`) VALUES
('allow_comments', NULL, 'system', '1', 'boolean', 'Allow comments on blog'),
('allow_trackbacks', NULL, 'system', '1', 'boolean', 'Allow trackbacks on blog'),
('blog_timezone', NULL, 'system', 'Europe/London', 'string', 'Blog timezone'),
('comments_nofollow', NULL, 'system', '1', 'boolean', 'Add rel="nofollow" to comments URLs'),
('comments_pub', NULL, 'system', '1', 'boolean', 'Publish comments immediatly'),
('comments_ttl', NULL, 'system', '0', 'integer', 'Number of days to keep comments open (0 means no ttl)'),
('copyright_notice', NULL, 'system', '', 'string', 'Copyright notice (simple text)'),
('date_format', NULL, 'system', '%A, %B %e %Y', 'string', 'Date format. See PHP strftime function for patterns'),
('editor', NULL, 'system', '', 'string', 'Person responsible of the content'),
('enable_html_filter', NULL, 'system', '0', 'boolean', 'Enable HTML filter'),
('enable_xmlrpc', NULL, 'system', '0', 'boolean', 'Enable XML/RPC interface'),
('lang', NULL, 'system', 'en', 'string', 'Default blog language'),
('media_exclusion', NULL, 'system', '', 'string', 'File name exclusion pattern in media manager. (PCRE value)'),
('media_img_m_size', NULL, 'system', '448', 'integer', 'Image medium size in media manager'),
('media_img_s_size', NULL, 'system', '240', 'integer', 'Image small size in media manager'),
('media_img_t_size', NULL, 'system', '100', 'integer', 'Image thumbnail size in media manager'),
('media_img_title_pattern', NULL, 'system', 'Title ;; Date(%b %Y) ;; separator(, )', 'string', 'Pattern to set image title when you insert it in a post'),
('nb_post_per_page', NULL, 'system', '20', 'integer', 'Number of entries on home page and category pages'),
('nb_post_per_feed', NULL, 'system', '20', 'integer', 'Number of entries on feeds'),
('nb_comment_per_feed', NULL, 'system', '20', 'integer', 'Number of comments on feeds'),
('post_url_format', NULL, 'system', '{y}/{m}/{d}/{t}', 'string', 'Post URL format. {y}: year, {m}: month, {d}: day, {id}: post id, {t}: entry title'),
('public_path', NULL, 'system', 'public', 'string', 'Path to public directory, begins with a / for a full system path'),
('public_url', NULL, 'system', '/public', 'string', 'URL to public directory'),
('robots_policy', NULL, 'system', 'INDEX,FOLLOW', 'string', 'Search engines robots policy'),
('short_feed_items', NULL, 'system', '0', 'boolean', 'Display short feed items'),
('theme', NULL, 'system', 'default', 'string', 'Blog theme'),
('themes_path', NULL, 'system', 'themes', 'string', 'Themes root path'),
('themes_url', NULL, 'system', '/themes', 'string', 'Themes root URL'),
('time_format', NULL, 'system', '%H:%M', 'string', 'Time format. See PHP strftime function for patterns'),
('tpl_allow_php', NULL, 'system', '0', 'boolean', 'Allow PHP code in templates'),
('tpl_use_cache', NULL, 'system', '1', 'boolean', 'Use template caching'),
('trackbacks_pub', NULL, 'system', '1', 'boolean', 'Publish trackbacks immediatly'),
('trackbacks_ttl', NULL, 'system', '0', 'integer', 'Number of days to keep trackbacks open (0 means no ttl)'),
('url_scan', NULL, 'system', 'query_string', 'string', 'URL handle mode (path_info or query_string)'),
('use_smilies', NULL, 'system', '0', 'boolean', 'Show smilies on entries and comments'),
('wiki_comments', NULL, 'system', '0', 'boolean', 'Allow commenters to use a subset of wiki syntax'),
('blog_timezone', 'default', 'system', 'Europe/Berlin', 'string', 'Blog timezone'),
('lang', 'default', 'system', 'fr', 'string', 'Default blog language');


CREATE TABLE IF NOT EXISTS `dotclear_spamrule` (
  `rule_id` bigint(20) NOT NULL,
  `blog_id` varchar(32) collate utf8_bin default NULL,
  `rule_type` varchar(16) collate utf8_bin NOT NULL default 'word',
  `rule_content` varchar(128) collate utf8_bin NOT NULL,
  PRIMARY KEY  (`rule_id`),
  KEY `dotclear_idx_spamrule_blog_id` USING BTREE (`blog_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


INSERT INTO `dotclear_spamrule` (`rule_id`, `blog_id`, `rule_type`, `rule_content`) VALUES
(1, NULL, 'word', '/-credit(\\s+|$)/'),
(2, NULL, 'word', '/-digest(\\s+|$)/'),
(3, NULL, 'word', '/-loan(\\s+|$)/'),
(4, NULL, 'word', '/-online(\\s+|$)/'),
(5, NULL, 'word', '4u'),
(6, NULL, 'word', 'adipex'),
(7, NULL, 'word', 'advicer'),
(8, NULL, 'word', 'ambien'),
(9, NULL, 'word', 'baccarat'),
(10, NULL, 'word', 'baccarrat'),
(11, NULL, 'word', 'blackjack'),
(12, NULL, 'word', 'bllogspot'),
(13, NULL, 'word', 'bolobomb'),
(14, NULL, 'word', 'booker'),
(15, NULL, 'word', 'byob'),
(16, NULL, 'word', 'car-rental-e-site'),
(17, NULL, 'word', 'car-rentals-e-site'),
(18, NULL, 'word', 'carisoprodol'),
(19, NULL, 'word', 'cash'),
(20, NULL, 'word', 'casino'),
(21, NULL, 'word', 'casinos'),
(22, NULL, 'word', 'chatroom'),
(23, NULL, 'word', 'cialis'),
(24, NULL, 'word', 'craps'),
(25, NULL, 'word', 'credit-card'),
(26, NULL, 'word', 'credit-report-4u'),
(27, NULL, 'word', 'cwas'),
(28, NULL, 'word', 'cyclen'),
(29, NULL, 'word', 'cyclobenzaprine'),
(30, NULL, 'word', 'dating-e-site'),
(31, NULL, 'word', 'day-trading'),
(32, NULL, 'word', 'debt'),
(33, NULL, 'word', 'digest-'),
(34, NULL, 'word', 'discount'),
(35, NULL, 'word', 'discreetordering'),
(36, NULL, 'word', 'duty-free'),
(37, NULL, 'word', 'dutyfree'),
(38, NULL, 'word', 'estate'),
(39, NULL, 'word', 'favourits'),
(40, NULL, 'word', 'fioricet'),
(41, NULL, 'word', 'flowers-leading-site'),
(42, NULL, 'word', 'freenet'),
(43, NULL, 'word', 'freenet-shopping'),
(44, NULL, 'word', 'gambling'),
(45, NULL, 'word', 'gamias'),
(46, NULL, 'word', 'health-insurancedeals-4u'),
(47, NULL, 'word', 'holdem'),
(48, NULL, 'word', 'holdempoker'),
(49, NULL, 'word', 'holdemsoftware'),
(50, NULL, 'word', 'holdemtexasturbowilson'),
(51, NULL, 'word', 'hotel-dealse-site'),
(52, NULL, 'word', 'hotele-site'),
(53, NULL, 'word', 'hotelse-site'),
(54, NULL, 'word', 'incest'),
(55, NULL, 'word', 'insurance-quotesdeals-4u'),
(56, NULL, 'word', 'insurancedeals-4u'),
(57, NULL, 'word', 'jrcreations'),
(58, NULL, 'word', 'levitra'),
(59, NULL, 'word', 'macinstruct'),
(60, NULL, 'word', 'mortgage'),
(61, NULL, 'word', 'online-gambling'),
(62, NULL, 'word', 'onlinegambling-4u'),
(63, NULL, 'word', 'ottawavalleyag'),
(64, NULL, 'word', 'ownsthis'),
(65, NULL, 'word', 'palm-texas-holdem-game'),
(66, NULL, 'word', 'paxil'),
(67, NULL, 'word', 'pharmacy'),
(68, NULL, 'word', 'phentermine'),
(69, NULL, 'word', 'pills'),
(70, NULL, 'word', 'poker'),
(71, NULL, 'word', 'poker-chip'),
(72, NULL, 'word', 'poze'),
(73, NULL, 'word', 'prescription'),
(74, NULL, 'word', 'rarehomes'),
(75, NULL, 'word', 'refund'),
(76, NULL, 'word', 'rental-car-e-site'),
(77, NULL, 'word', 'roulette'),
(78, NULL, 'word', 'shemale'),
(79, NULL, 'word', 'slot'),
(80, NULL, 'word', 'slot-machine'),
(81, NULL, 'word', 'soma'),
(82, NULL, 'word', 'taboo'),
(83, NULL, 'word', 'tamiflu'),
(84, NULL, 'word', 'texas-holdem'),
(85, NULL, 'word', 'thorcarlson'),
(86, NULL, 'word', 'top-e-site'),
(87, NULL, 'word', 'top-site'),
(88, NULL, 'word', 'tramadol'),
(89, NULL, 'word', 'trim-spa'),
(90, NULL, 'word', 'ultram'),
(91, NULL, 'word', 'v1h'),
(92, NULL, 'word', 'vacuum'),
(93, NULL, 'word', 'valeofglamorganconservatives'),
(94, NULL, 'word', 'viagra'),
(95, NULL, 'word', 'vicodin'),
(96, NULL, 'word', 'vioxx'),
(97, NULL, 'word', 'xanax'),
(98, NULL, 'word', 'zolus');



CREATE TABLE IF NOT EXISTS `dotclear_user` (
  `user_id` varchar(32) collate utf8_bin NOT NULL,
  `user_super` smallint(6) default NULL,
  `user_status` smallint(6) NOT NULL default '1',
  `user_pwd` varchar(40) collate utf8_bin NOT NULL,
  `user_recover_key` varchar(32) collate utf8_bin default NULL,
  `user_name` varchar(255) collate utf8_bin default NULL,
  `user_firstname` varchar(255) collate utf8_bin default NULL,
  `user_displayname` varchar(255) collate utf8_bin default NULL,
  `user_email` varchar(255) collate utf8_bin default NULL,
  `user_url` varchar(255) collate utf8_bin default NULL,
  `user_desc` longtext collate utf8_bin,
  `user_default_blog` varchar(32) collate utf8_bin default NULL,
  `user_options` longtext collate utf8_bin,
  `user_lang` varchar(5) collate utf8_bin default NULL,
  `user_tz` varchar(128) collate utf8_bin NOT NULL default 'UTC',
  `user_post_status` smallint(6) NOT NULL default '-2',
  `user_creadt` datetime NOT NULL default '1970-01-01 00:00:00',
  `user_upddt` datetime NOT NULL default '1970-01-01 00:00:00',
  PRIMARY KEY  (`user_id`),
  KEY `dotclear_idx_user_user_default_blog` USING BTREE (`user_default_blog`),
  KEY `dotclear_idx_user_user_super` USING BTREE (`user_super`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


CREATE TABLE IF NOT EXISTS `dotclear_version` (
  `module` varchar(64) collate utf8_bin NOT NULL,
  `version` varchar(32) collate utf8_bin NOT NULL,
  PRIMARY KEY  (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;



INSERT INTO `dotclear_version` (`module`, `version`) VALUES
('antispam', '1.2'),
('blogroll', '1.1'),
('core', '2.1.4'),
('metadata', '1.0.2');

ALTER TABLE `dotclear_category`
  ADD CONSTRAINT `dotclear_fk_category_blog` FOREIGN KEY (`blog_id`) REFERENCES `dotclear_blog` (`blog_id`) ON DELETE CASCADE ON UPDATE CASCADE;


ALTER TABLE `dotclear_comment`
  ADD CONSTRAINT `dotclear_fk_comment_post` FOREIGN KEY (`post_id`) REFERENCES `dotclear_post` (`post_id`) ON DELETE CASCADE ON UPDATE CASCADE;


ALTER TABLE `dotclear_link`
  ADD CONSTRAINT `dotclear_fk_link_blog` FOREIGN KEY (`blog_id`) REFERENCES `dotclear_blog` (`blog_id`) ON DELETE CASCADE ON UPDATE CASCADE;


ALTER TABLE `dotclear_media`
  ADD CONSTRAINT `dotclear_fk_media_user` FOREIGN KEY (`user_id`) REFERENCES `dotclear_user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `dotclear_meta`
  ADD CONSTRAINT `dotclear_fk_meta_post` FOREIGN KEY (`post_id`) REFERENCES `dotclear_post` (`post_id`) ON DELETE CASCADE ON UPDATE CASCADE;


ALTER TABLE `dotclear_permissions`
  ADD CONSTRAINT `dotclear_fk_permissions_user` FOREIGN KEY (`user_id`) REFERENCES `dotclear_user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `dotclear_fk_permissions_blog` FOREIGN KEY (`blog_id`) REFERENCES `dotclear_blog` (`blog_id`) ON DELETE CASCADE ON UPDATE CASCADE;


ALTER TABLE `dotclear_ping`
  ADD CONSTRAINT `dotclear_fk_ping_post` FOREIGN KEY (`post_id`) REFERENCES `dotclear_post` (`post_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `dotclear_post`
  ADD CONSTRAINT `dotclear_fk_post_blog` FOREIGN KEY (`blog_id`) REFERENCES `dotclear_blog` (`blog_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `dotclear_fk_post_category` FOREIGN KEY (`cat_id`) REFERENCES `dotclear_category` (`cat_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `dotclear_fk_post_user` FOREIGN KEY (`user_id`) REFERENCES `dotclear_user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;


ALTER TABLE `dotclear_post_media`
  ADD CONSTRAINT `dotclear_fk_media_post` FOREIGN KEY (`post_id`) REFERENCES `dotclear_post` (`post_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `dotclear_fk_media` FOREIGN KEY (`media_id`) REFERENCES `dotclear_media` (`media_id`) ON DELETE CASCADE ON UPDATE CASCADE;


ALTER TABLE `dotclear_setting`
  ADD CONSTRAINT `dotclear_fk_setting_blog` FOREIGN KEY (`blog_id`) REFERENCES `dotclear_blog` (`blog_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `dotclear_spamrule`
  ADD CONSTRAINT `dotclear_fk_spamrule_blog` FOREIGN KEY (`blog_id`) REFERENCES `dotclear_blog` (`blog_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `dotclear_user`
  ADD CONSTRAINT `dotclear_fk_user_default_blog` FOREIGN KEY (`user_default_blog`) REFERENCES `dotclear_blog` (`blog_id`) ON DELETE SET NULL ON UPDATE CASCADE;

