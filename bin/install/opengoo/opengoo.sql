SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


CREATE TABLE IF NOT EXISTS `og_administration_tools` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50)  NOT NULL DEFAULT '',
  `controller` varchar(50)  NOT NULL DEFAULT '',
  `action` varchar(50)  NOT NULL DEFAULT '',
  `order` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS `og_application_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `taken_by_id` int(10) unsigned DEFAULT NULL,
  `rel_object_id` int(10) NOT NULL DEFAULT '0',
  `object_name` text ,
  `rel_object_manager` varchar(50)  NOT NULL DEFAULT '',
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by_id` int(10) unsigned DEFAULT NULL,
  `action` enum('upload','open','close','delete','edit','add','trash','untrash','subscribe','unsubscribe','tag','comment','link','unlink','login','untag','archive','unarchive')  DEFAULT NULL,
  `is_private` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_silent` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `log_data` text ,
  PRIMARY KEY (`id`),
  KEY `created_on` (`created_on`)
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS `og_billing_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT '',
  `description` text,
  `default_value` float NOT NULL DEFAULT '0',
  `report_name` varchar(100) DEFAULT '',
  `created_on` datetime DEFAULT NULL,
  `created_by_id` int(10) unsigned NOT NULL DEFAULT '0',
  `updated_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_by_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;



CREATE TABLE IF NOT EXISTS `og_comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rel_object_id` int(10) unsigned NOT NULL DEFAULT '0',
  `rel_object_manager` varchar(30)  NOT NULL DEFAULT '',
  `text` text ,
  `is_private` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_anonymous` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `author_name` varchar(50)  DEFAULT NULL,
  `author_email` varchar(100)  DEFAULT NULL,
  `author_homepage` varchar(100)  NOT NULL DEFAULT '',
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by_id` int(10) unsigned DEFAULT NULL,
  `updated_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_by_id` int(10) unsigned DEFAULT NULL,
  `trashed_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `trashed_by_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `object_id` (`rel_object_id`,`rel_object_manager`),
  KEY `created_on` (`created_on`)
) ENGINE=InnoDB;

--
-- Contenu de la table `og_comments`
--




--
-- Structure de la table `og_companies`
--

CREATE TABLE IF NOT EXISTS `og_companies` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `client_of_id` smallint(5) unsigned DEFAULT NULL,
  `name` varchar(100)  DEFAULT NULL,
  `email` varchar(100)  DEFAULT NULL,
  `notes` text ,
  `homepage` varchar(100)  DEFAULT NULL,
  `address` varchar(100)  DEFAULT NULL,
  `address2` varchar(100)  DEFAULT NULL,
  `city` varchar(50)  DEFAULT NULL,
  `state` varchar(50)  DEFAULT NULL,
  `zipcode` varchar(30)  DEFAULT NULL,
  `country` varchar(10)  DEFAULT NULL,
  `phone_number` varchar(50)  DEFAULT NULL,
  `fax_number` varchar(50)  DEFAULT NULL,
  `logo_file` varchar(44)  DEFAULT NULL,
  `timezone` float(3,1) NOT NULL DEFAULT '0.0',
  `hide_welcome_info` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by_id` int(10) unsigned DEFAULT NULL,
  `updated_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_by_id` int(10) unsigned DEFAULT NULL,
  `trashed_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `trashed_by_id` int(10) unsigned DEFAULT NULL,
  `archived_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `archived_by_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `created_on` (`created_on`),
  KEY `client_of_id` (`client_of_id`)
) ENGINE=InnoDB;

--
-- Contenu de la table `og_companies`
--



--
-- Structure de la table `og_config_categories`
--

CREATE TABLE IF NOT EXISTS `og_config_categories` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50)  NOT NULL DEFAULT '',
  `is_system` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `category_order` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `order` (`category_order`)
) ENGINE=InnoDB;

--
-- Contenu de la table `og_config_categories`
--



CREATE TABLE IF NOT EXISTS `og_config_options` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `category_name` varchar(30)  NOT NULL DEFAULT '',
  `name` varchar(50)  NOT NULL DEFAULT '',
  `value` text ,
  `config_handler_class` varchar(50)  NOT NULL DEFAULT '',
  `is_system` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `option_order` smallint(5) unsigned NOT NULL DEFAULT '0',
  `dev_comment` varchar(255)  DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `order` (`option_order`),
  KEY `category_id` (`category_name`)
) ENGINE=InnoDB;

--
-- Contenu de la table `og_config_options`
--





CREATE TABLE IF NOT EXISTS `og_contacts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `firstname` varchar(50)  DEFAULT NULL,
  `lastname` varchar(50)  DEFAULT NULL,
  `middlename` varchar(50)  DEFAULT NULL,
  `department` varchar(50)  DEFAULT NULL,
  `job_title` varchar(50)  DEFAULT NULL,
  `company_id` int(10) DEFAULT NULL,
  `email` varchar(100)  DEFAULT NULL,
  `email2` varchar(100)  DEFAULT NULL,
  `email3` varchar(100)  DEFAULT NULL,
  `w_web_page` text ,
  `w_address` varchar(200)  DEFAULT NULL,
  `w_city` varchar(50)  DEFAULT NULL,
  `w_state` varchar(50)  DEFAULT NULL,
  `w_zipcode` varchar(50)  DEFAULT NULL,
  `w_country` varchar(50)  DEFAULT NULL,
  `w_phone_number` varchar(50)  DEFAULT NULL,
  `w_phone_number2` varchar(50)  DEFAULT NULL,
  `w_fax_number` varchar(50)  DEFAULT NULL,
  `w_assistant_number` varchar(50)  DEFAULT NULL,
  `w_callback_number` varchar(50)  DEFAULT NULL,
  `h_web_page` text ,
  `h_address` varchar(200)  DEFAULT NULL,
  `h_city` varchar(50)  DEFAULT NULL,
  `h_state` varchar(50)  DEFAULT NULL,
  `h_zipcode` varchar(50)  DEFAULT NULL,
  `h_country` varchar(50)  DEFAULT NULL,
  `h_phone_number` varchar(50)  DEFAULT NULL,
  `h_phone_number2` varchar(50)  DEFAULT NULL,
  `h_fax_number` varchar(50)  DEFAULT NULL,
  `h_mobile_number` varchar(50)  DEFAULT NULL,
  `h_pager_number` varchar(50)  DEFAULT NULL,
  `o_web_page` text ,
  `o_address` varchar(200)  DEFAULT NULL,
  `o_city` varchar(50)  DEFAULT NULL,
  `o_state` varchar(50)  DEFAULT NULL,
  `o_zipcode` varchar(50)  DEFAULT NULL,
  `o_country` varchar(50)  DEFAULT NULL,
  `o_phone_number` varchar(50)  DEFAULT NULL,
  `o_phone_number2` varchar(50)  DEFAULT NULL,
  `o_fax_number` varchar(50)  DEFAULT NULL,
  `o_birthday` datetime DEFAULT NULL,
  `picture_file` varchar(44)  DEFAULT NULL,
  `timezone` float(3,1) NOT NULL DEFAULT '0.0',
  `notes` text ,
  `user_id` int(10) DEFAULT NULL,
  `is_private` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by_id` int(10) unsigned NOT NULL DEFAULT '0',
  `updated_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_by_id` int(10) unsigned NOT NULL DEFAULT '0',
  `trashed_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `trashed_by_id` int(10) unsigned DEFAULT NULL,
  `archived_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `archived_by_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `created_by_id` (`created_by_id`),
  KEY `company_id` (`company_id`)
) ENGINE=InnoDB   ;

CREATE TABLE IF NOT EXISTS `og_contact_im_values` (
  `contact_id` int(10) unsigned NOT NULL DEFAULT '0',
  `im_type_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `value` varchar(50)  NOT NULL DEFAULT '',
  `is_default` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`contact_id`,`im_type_id`),
  KEY `is_default` (`is_default`)
) ENGINE=InnoDB ;

CREATE TABLE IF NOT EXISTS `og_cron_events` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45)  NOT NULL DEFAULT '',
  `recursive` tinyint(1) NOT NULL DEFAULT '1',
  `delay` int(10) unsigned NOT NULL DEFAULT '0',
  `is_system` tinyint(1) NOT NULL DEFAULT '0',
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_name` (`name`)
) ENGINE=InnoDB   AUTO_INCREMENT=9 ;

--


CREATE TABLE IF NOT EXISTS `og_custom_properties` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `object_type` varchar(255)  NOT NULL,
  `name` varchar(255)  NOT NULL,
  `type` varchar(255)  NOT NULL,
  `description` text  NOT NULL,
  `values` text  NOT NULL,
  `default_value` varchar(255)  NOT NULL,
  `is_required` tinyint(1) NOT NULL,
  `is_multiple_values` tinyint(1) NOT NULL,
  `property_order` int(10) NOT NULL,
  `visible_by_default` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB   ;

CREATE TABLE IF NOT EXISTS `og_custom_properties_by_co_type` (
  `co_type_id` int(10) unsigned NOT NULL,
  `cp_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`co_type_id`,`cp_id`)
) ENGINE=InnoDB ;


CREATE TABLE IF NOT EXISTS `og_custom_property_values` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `object_id` int(10) NOT NULL,
  `custom_property_id` int(10) NOT NULL,
  `value` varchar(255)  NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB   ;


CREATE TABLE IF NOT EXISTS `og_event_invitations` (
  `event_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `invitation_state` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`event_id`,`user_id`)
) ENGINE=InnoDB ;



CREATE TABLE IF NOT EXISTS `og_file_repo` (
  `id` varchar(40)  NOT NULL DEFAULT '',
  `content` longblob NOT NULL,
  `order` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `order` (`order`)
) ENGINE=InnoDB ;



CREATE TABLE IF NOT EXISTS `og_file_repo_attributes` (
  `id` varchar(40)  NOT NULL DEFAULT '',
  `attribute` varchar(50)  NOT NULL DEFAULT '',
  `value` text  NOT NULL,
  PRIMARY KEY (`id`,`attribute`)
) ENGINE=InnoDB ;



CREATE TABLE IF NOT EXISTS `og_file_types` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `extension` varchar(10)  NOT NULL DEFAULT '',
  `icon` varchar(30)  NOT NULL DEFAULT '',
  `is_searchable` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_image` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `extension` (`extension`)
) ENGINE=InnoDB;






CREATE TABLE IF NOT EXISTS `og_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` text  NOT NULL,
  `created_on` datetime NOT NULL,
  `created_by_id` int(10) unsigned NOT NULL,
  `updated_on` datetime NOT NULL,
  `updated_by_id` int(10) unsigned NOT NULL,
  `can_edit_company_data` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `can_manage_security` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `can_manage_workspaces` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `can_manage_configuration` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `can_manage_contacts` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `can_manage_templates` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `can_manage_reports` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `can_manage_time` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `can_add_mail_accounts` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;



CREATE TABLE IF NOT EXISTS `og_group_users` (
  `group_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `created_on` datetime NOT NULL,
  `created_by_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`group_id`,`user_id`),
  KEY `USER` (`user_id`)
) ENGINE=InnoDB ;



CREATE TABLE IF NOT EXISTS `og_gs_books` (
  `BookId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `BookName` varchar(45)  NOT NULL DEFAULT '',
  `UserId` int(10) unsigned NOT NULL COMMENT 'Book Owner',
  PRIMARY KEY (`BookId`)
) ENGINE=InnoDB   COMMENT='System Workbooks';


CREATE TABLE IF NOT EXISTS `og_gs_borderstyles` (
  `BorderStyleId` int(11) NOT NULL AUTO_INCREMENT,
  `BorderColor` varchar(7)  DEFAULT NULL,
  `BorderWidth` int(11) NOT NULL DEFAULT '0',
  `BorderStyle` varchar(64)  DEFAULT NULL,
  `BookId` int(11) DEFAULT NULL,
  PRIMARY KEY (`BorderStyleId`)
) ENGINE=InnoDB   ;

CREATE TABLE IF NOT EXISTS `og_gs_cells` (
  `SheetId` int(10) unsigned NOT NULL,
  `DataColumn` int(10) unsigned NOT NULL,
  `DataRow` int(10) unsigned NOT NULL,
  `CellFormula` varchar(255)  DEFAULT NULL,
  `CellValue` text  NOT NULL,
  `FontStyleId` int(10) unsigned NOT NULL DEFAULT '0',
  `LayoutStyleId` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`SheetId`,`DataColumn`,`DataRow`)
) ENGINE=InnoDB  COMMENT='Sheet data';

CREATE TABLE IF NOT EXISTS `og_gs_columns` (
  `SheetId` int(11) NOT NULL,
  `ColumnIndex` int(11) NOT NULL,
  `ColumnSize` int(11) NOT NULL,
  `FontStyleId` int(11) NOT NULL,
  `LayerStyleId` int(11) NOT NULL,
  `LayoutStyleId` int(11) NOT NULL,
  PRIMARY KEY (`SheetId`,`ColumnIndex`)
) ENGINE=InnoDB ;

CREATE TABLE IF NOT EXISTS `og_gs_fonts` (
  `FontId` int(11) NOT NULL AUTO_INCREMENT,
  `FontName` varchar(63)  NOT NULL DEFAULT '',
  PRIMARY KEY (`FontId`)
) ENGINE=InnoDB   AUTO_INCREMENT=7 ;

CREATE TABLE IF NOT EXISTS `og_gs_fontstyles` (
  `FontStyleId` int(11) NOT NULL,
  `BookId` int(11) NOT NULL,
  `FontId` int(11) NOT NULL,
  `FontSize` decimal(8,1) NOT NULL DEFAULT '10.0',
  `FontBold` tinyint(1) NOT NULL DEFAULT '0',
  `FontItalic` tinyint(1) NOT NULL DEFAULT '0',
  `FontUnderline` tinyint(1) NOT NULL DEFAULT '0',
  `FontColor` varchar(7)  NOT NULL DEFAULT '',
  `FontVAlign` int(11) NOT NULL DEFAULT '0',
  `FontHAlign` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`FontStyleId`,`BookId`)
) ENGINE=InnoDB ;


CREATE TABLE IF NOT EXISTS `og_gs_layoutstyles` (
  `LayoutStyleId` int(11) NOT NULL AUTO_INCREMENT,
  `BorderLeftStyleId` int(11) DEFAULT NULL,
  `BackgroundColor` varchar(7)  DEFAULT NULL,
  `BorderRightStyleId` int(11) DEFAULT NULL,
  `BorderTopStyleId` int(11) DEFAULT NULL,
  `BorderBottomStyleId` int(11) DEFAULT NULL,
  `BookId` int(11) DEFAULT NULL,
  PRIMARY KEY (`LayoutStyleId`)
) ENGINE=InnoDB   ;


CREATE TABLE IF NOT EXISTS `og_gs_mergedcells` (
  `SheetId` int(11) NOT NULL,
  `MergedCellRow` int(11) NOT NULL,
  `MergedCellCol` int(11) NOT NULL,
  `MergedRows` int(11) DEFAULT NULL,
  `MergedCols` int(11) DEFAULT NULL
) ENGINE=InnoDB ;

CREATE TABLE IF NOT EXISTS `og_gs_rows` (
  `SheetId` int(11) NOT NULL,
  `RowIndex` int(11) NOT NULL,
  `RowSize` int(11) NOT NULL,
  `FontStyleId` int(11) NOT NULL,
  `LayerStyleId` int(11) NOT NULL,
  `LayoutStyleId` int(11) NOT NULL,
  PRIMARY KEY (`SheetId`,`RowIndex`)
) ENGINE=InnoDB ;

CREATE TABLE IF NOT EXISTS `og_gs_sheets` (
  `SheetId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `BookId` int(10) unsigned NOT NULL,
  `SheetName` varchar(45)  NOT NULL DEFAULT '',
  `SheetIndex` int(10) unsigned NOT NULL,
  PRIMARY KEY (`SheetId`)
) ENGINE=InnoDB   COMMENT='Workbooks Sheets' ;

CREATE TABLE IF NOT EXISTS `og_gs_userbooks` (
  `UserBookId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `UserId` int(10) unsigned NOT NULL,
  `BookId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`UserBookId`)
) ENGINE=InnoDB   ;


CREATE TABLE IF NOT EXISTS `og_gs_users` (
  `UserId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `UserName` varchar(45)  NOT NULL DEFAULT '',
  `UserLastName` varchar(45)  NOT NULL DEFAULT '',
  `UserNickname` varchar(45)  NOT NULL DEFAULT '',
  `UserPassword` varchar(45)  NOT NULL DEFAULT '',
  `LanguageId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`UserId`)
) ENGINE=InnoDB   COMMENT='Sytem Users' AUTO_INCREMENT=4 ;



CREATE TABLE IF NOT EXISTS `og_guistate` (
  `user_id` int(10) unsigned NOT NULL DEFAULT '1',
  `name` varchar(100)  NOT NULL,
  `value` text  NOT NULL
) ENGINE=InnoDB ;

CREATE TABLE IF NOT EXISTS `og_im_types` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30)  NOT NULL DEFAULT '',
  `icon` varchar(30)  NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS `og_linked_objects` (
  `rel_object_manager` varchar(50)  NOT NULL DEFAULT '',
  `rel_object_id` int(10) unsigned NOT NULL DEFAULT '0',
  `object_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by_id` int(10) unsigned DEFAULT NULL,
  `object_manager` varchar(50)  NOT NULL DEFAULT '',
  PRIMARY KEY (`rel_object_manager`,`rel_object_id`,`object_id`,`object_manager`)
) ENGINE=InnoDB ;

CREATE TABLE IF NOT EXISTS `og_mail_accounts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(40)  NOT NULL DEFAULT '',
  `email` varchar(100)  DEFAULT '',
  `email_addr` varchar(100)  NOT NULL DEFAULT '',
  `password` varchar(40)  DEFAULT '',
  `server` varchar(100)  NOT NULL DEFAULT '',
  `is_imap` int(1) NOT NULL DEFAULT '0',
  `incoming_ssl` int(1) NOT NULL DEFAULT '0',
  `incoming_ssl_port` int(11) DEFAULT '995',
  `smtp_server` varchar(100)  NOT NULL DEFAULT '',
  `smtp_use_auth` int(10) unsigned NOT NULL DEFAULT '0',
  `smtp_username` varchar(100)  DEFAULT NULL,
  `smtp_password` varchar(100)  DEFAULT NULL,
  `smtp_port` int(10) unsigned NOT NULL DEFAULT '25',
  `del_from_server` int(11) NOT NULL DEFAULT '0',
  `outgoing_transport_type` varchar(5)  NOT NULL DEFAULT '',
  `last_checked` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `signature` text  NOT NULL,
  `workspace` int(10) NOT NULL DEFAULT '0',
  `sender_name` varchar(100)  NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB   ;



CREATE TABLE IF NOT EXISTS `og_mail_account_imap_folder` (
  `account_id` int(10) unsigned NOT NULL DEFAULT '0',
  `folder_name` varchar(100)  NOT NULL DEFAULT '',
  `check_folder` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`account_id`,`folder_name`)
) ENGINE=InnoDB ;


CREATE TABLE IF NOT EXISTS `og_mail_account_users` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `account_id` int(10) NOT NULL,
  `user_id` int(10) NOT NULL,
  `can_edit` tinyint(1) NOT NULL DEFAULT '0',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `signature` text  NOT NULL,
  `sender_name` varchar(100)  NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_useracc` (`account_id`,`user_id`),
  KEY `ix_account` (`account_id`),
  KEY `ix_user` (`user_id`)
) ENGINE=InnoDB   ;

CREATE TABLE IF NOT EXISTS `og_mail_contents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` int(10) unsigned NOT NULL DEFAULT '0',
  `uid` varchar(255)  NOT NULL DEFAULT '',
  `from` varchar(100)  NOT NULL DEFAULT '',
  `from_name` varchar(250)  DEFAULT NULL,
  `to` text  NOT NULL,
  `cc` text  NOT NULL,
  `bcc` text  NOT NULL,
  `sent_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `received_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `subject` text ,
  `body_plain` longtext ,
  `body_html` longtext ,
  `has_attachments` int(1) NOT NULL DEFAULT '0',
  `size` int(10) NOT NULL DEFAULT '0',
  `state` int(1) NOT NULL DEFAULT '0' COMMENT '0:nothing, 1:sent; 2:draft',
  `is_deleted` int(1) NOT NULL DEFAULT '0',
  `is_shared` int(1) NOT NULL DEFAULT '0',
  `is_private` int(1) NOT NULL DEFAULT '0',
  `created_on` datetime DEFAULT NULL,
  `created_by_id` int(10) unsigned NOT NULL DEFAULT '0',
  `trashed_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `trashed_by_id` int(10) unsigned DEFAULT NULL,
  `imap_folder_name` varchar(100)  NOT NULL DEFAULT '',
  `account_email` varchar(100)  DEFAULT '',
  `content_file_id` varchar(40)  NOT NULL DEFAULT '',
  `content` varchar(1)  NOT NULL DEFAULT '',
  `archived_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `archived_by_id` int(10) unsigned DEFAULT NULL,
  `message_id` varchar(255)  NOT NULL COMMENT 'Message-Id header',
  `in_reply_to_id` varchar(255)  NOT NULL COMMENT 'Message-Id header of the previous email in the conversation',
  `conversation_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `account_id` (`account_id`),
  KEY `sent_date` (`sent_date`),
  KEY `received_date` (`received_date`),
  KEY `uid` (`uid`),
  KEY `conversation_id` (`conversation_id`),
  KEY `message_id` (`message_id`),
  KEY `state` (`state`)
) ENGINE=InnoDB ;


CREATE TABLE IF NOT EXISTS `og_mail_conversations` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `og_object_handins` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` text ,
  `text` text ,
  `responsible_user_id` int(10) unsigned DEFAULT NULL,
  `rel_object_id` int(10) unsigned NOT NULL DEFAULT '0',
  `rel_object_manager` varchar(50)  NOT NULL,
  `order` int(10) unsigned DEFAULT '0',
  `completed_by_id` int(10) unsigned DEFAULT NULL,
  `completed_on` datetime DEFAULT NULL,
  `responsible_company_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB   ;



CREATE TABLE IF NOT EXISTS `og_object_properties` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rel_object_id` int(10) unsigned NOT NULL,
  `rel_object_manager` varchar(50)  NOT NULL,
  `name` text  NOT NULL,
  `value` text  NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ObjectID` (`rel_object_id`,`rel_object_manager`)
) ENGINE=InnoDB   ;



CREATE TABLE IF NOT EXISTS `og_object_reminders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `object_id` int(10) unsigned NOT NULL DEFAULT '0',
  `object_manager` varchar(50)  NOT NULL,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `type` varchar(40)  NOT NULL DEFAULT '',
  `context` varchar(40)  NOT NULL DEFAULT '',
  `minutes_before` int(10) DEFAULT NULL,
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB   ;



CREATE TABLE IF NOT EXISTS `og_object_reminder_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(40)  NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS `og_object_subscriptions` (
  `object_id` int(10) unsigned NOT NULL DEFAULT '0',
  `object_manager` varchar(50)  NOT NULL,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`object_id`,`object_manager`,`user_id`)
) ENGINE=InnoDB ;

CREATE TABLE IF NOT EXISTS `og_object_user_permissions` (
  `rel_object_id` int(10) unsigned NOT NULL,
  `rel_object_manager` varchar(50)  NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `can_read` tinyint(1) unsigned NOT NULL,
  `can_write` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`rel_object_id`,`user_id`,`rel_object_manager`)
) ENGINE=InnoDB ;

CREATE TABLE IF NOT EXISTS `og_projects` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50)  DEFAULT NULL,
  `description` text ,
  `show_description_in_overview` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `completed_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `completed_by_id` int(11) DEFAULT NULL,
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by_id` int(10) unsigned DEFAULT NULL,
  `updated_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_by_id` int(10) unsigned DEFAULT NULL,
  `color` int(10) unsigned DEFAULT '0',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `p1` int(10) unsigned NOT NULL DEFAULT '0',
  `p2` int(10) unsigned NOT NULL DEFAULT '0',
  `p3` int(10) unsigned NOT NULL DEFAULT '0',
  `p4` int(10) unsigned NOT NULL DEFAULT '0',
  `p5` int(10) unsigned NOT NULL DEFAULT '0',
  `p6` int(10) unsigned NOT NULL DEFAULT '0',
  `p7` int(10) unsigned NOT NULL DEFAULT '0',
  `p8` int(10) unsigned NOT NULL DEFAULT '0',
  `p9` int(10) unsigned NOT NULL DEFAULT '0',
  `p10` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `completed_on` (`completed_on`)
) ENGINE=InnoDB   ;

CREATE TABLE IF NOT EXISTS `og_project_charts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type_id` int(10) unsigned DEFAULT NULL,
  `display_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(100)  DEFAULT NULL,
  `show_in_project` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `show_in_parents` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by_id` int(10) unsigned DEFAULT NULL,
  `updated_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_by_id` int(10) unsigned DEFAULT NULL,
  `trashed_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `trashed_by_id` int(10) unsigned DEFAULT NULL,
  `archived_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `archived_by_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB   ;


CREATE TABLE IF NOT EXISTS `og_project_chart_params` (
  `id` int(10) unsigned NOT NULL,
  `chart_id` int(10) unsigned NOT NULL,
  `value` varchar(80)  NOT NULL,
  PRIMARY KEY (`id`,`chart_id`)
) ENGINE=InnoDB ;


CREATE TABLE IF NOT EXISTS `og_project_companies` (
  `project_id` int(10) unsigned NOT NULL DEFAULT '0',
  `company_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`project_id`,`company_id`)
) ENGINE=InnoDB ;

CREATE TABLE IF NOT EXISTS `og_project_contacts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `contact_id` int(10) unsigned NOT NULL DEFAULT '0',
  `project_id` int(10) unsigned NOT NULL DEFAULT '0',
  `role` varchar(255)  DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `contact_project_ids` (`contact_id`,`project_id`)
) ENGINE=InnoDB   ;

CREATE TABLE IF NOT EXISTS `og_project_co_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `object_manager` varchar(45)  NOT NULL,
  `name` varchar(45)  NOT NULL,
  `created_by_id` int(10) unsigned NOT NULL,
  `created_on` datetime NOT NULL,
  `updated_by_id` int(10) unsigned NOT NULL,
  `updated_on` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `object_manager` (`object_manager`)
) ENGINE=InnoDB   ;

CREATE TABLE IF NOT EXISTS `og_project_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_by_id` int(11) NOT NULL DEFAULT '0',
  `updated_by_id` int(11) DEFAULT NULL,
  `updated_on` datetime DEFAULT NULL,
  `created_on` datetime DEFAULT NULL,
  `trashed_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `trashed_by_id` int(10) unsigned DEFAULT NULL,
  `archived_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `archived_by_id` int(10) unsigned DEFAULT NULL,
  `start` datetime DEFAULT NULL,
  `duration` datetime DEFAULT NULL,
  `subject` varchar(255)  DEFAULT NULL,
  `description` text ,
  `private` char(1)  NOT NULL DEFAULT '0',
  `repeat_end` date DEFAULT NULL,
  `repeat_forever` tinyint(1) unsigned NOT NULL,
  `repeat_num` mediumint(9) NOT NULL DEFAULT '0',
  `repeat_d` smallint(6) NOT NULL DEFAULT '0',
  `repeat_m` smallint(6) NOT NULL DEFAULT '0',
  `repeat_y` smallint(6) NOT NULL DEFAULT '0',
  `repeat_h` smallint(6) NOT NULL DEFAULT '0',
  `repeat_dow` int(10) unsigned NOT NULL DEFAULT '0',
  `repeat_wnum` int(10) unsigned NOT NULL DEFAULT '0',
  `repeat_mjump` int(10) unsigned NOT NULL DEFAULT '0',
  `type_id` int(11) NOT NULL DEFAULT '0',
  `special_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB   ;

CREATE TABLE IF NOT EXISTS `og_project_files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(100)  NOT NULL DEFAULT '',
  `description` text ,
  `is_private` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_important` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_locked` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_visible` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `expiration_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comments_enabled` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `anonymous_comments_enabled` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by_id` int(10) unsigned DEFAULT '0',
  `updated_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_by_id` int(10) unsigned DEFAULT '0',
  `trashed_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `trashed_by_id` int(10) unsigned DEFAULT NULL,
  `checked_out_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `checked_out_by_id` int(10) unsigned DEFAULT '0',
  `archived_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `archived_by_id` int(10) unsigned DEFAULT NULL,
  `was_auto_checked_out` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `type` int(1) NOT NULL DEFAULT '0',
  `url` varchar(255)  DEFAULT NULL,
  `mail_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB   ;



CREATE TABLE IF NOT EXISTS `og_project_file_revisions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `file_id` int(10) unsigned NOT NULL DEFAULT '0',
  `file_type_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `repository_id` varchar(40)  NOT NULL DEFAULT '',
  `thumb_filename` varchar(44)  DEFAULT NULL,
  `revision_number` int(10) unsigned NOT NULL DEFAULT '0',
  `comment` text ,
  `type_string` varchar(255)  NOT NULL DEFAULT '',
  `filesize` int(10) unsigned NOT NULL DEFAULT '0',
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by_id` int(10) unsigned DEFAULT NULL,
  `updated_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_by_id` int(10) unsigned DEFAULT NULL,
  `trashed_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `trashed_by_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `file_id` (`file_id`),
  KEY `updated_on` (`updated_on`),
  KEY `revision_number` (`revision_number`)
) ENGINE=InnoDB   ;


CREATE TABLE IF NOT EXISTS `og_project_forms` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(50)  NOT NULL DEFAULT '',
  `description` text  NOT NULL,
  `success_message` text  NOT NULL,
  `action` enum('add_comment','add_task')  NOT NULL DEFAULT 'add_comment',
  `in_object_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_on` datetime DEFAULT NULL,
  `created_by_id` int(10) unsigned NOT NULL DEFAULT '0',
  `updated_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_by_id` int(10) unsigned NOT NULL DEFAULT '0',
  `trashed_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `trashed_by_id` int(10) unsigned DEFAULT NULL,
  `is_visible` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_enabled` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `order` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB   ;



CREATE TABLE IF NOT EXISTS `og_project_messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `milestone_id` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(100)  DEFAULT NULL,
  `text` text ,
  `additional_text` text ,
  `is_important` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_private` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `comments_enabled` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `anonymous_comments_enabled` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by_id` int(10) unsigned DEFAULT NULL,
  `updated_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_by_id` int(10) unsigned DEFAULT NULL,
  `trashed_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `trashed_by_id` int(10) unsigned DEFAULT NULL,
  `archived_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `archived_by_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `milestone_id` (`milestone_id`),
  KEY `created_on` (`created_on`)
) ENGINE=InnoDB   ;



CREATE TABLE IF NOT EXISTS `og_project_milestones` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100)  DEFAULT NULL,
  `description` text ,
  `due_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `assigned_to_company_id` smallint(10) NOT NULL DEFAULT '0',
  `assigned_to_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `is_private` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `completed_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `completed_by_id` int(10) unsigned DEFAULT NULL,
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by_id` int(10) unsigned DEFAULT NULL,
  `updated_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_by_id` int(10) unsigned DEFAULT NULL,
  `trashed_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `trashed_by_id` int(10) unsigned DEFAULT NULL,
  `archived_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `archived_by_id` int(10) unsigned DEFAULT NULL,
  `is_template` tinyint(1) NOT NULL DEFAULT '0',
  `from_template_id` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `due_date` (`due_date`),
  KEY `completed_on` (`completed_on`),
  KEY `created_on` (`created_on`)
) ENGINE=InnoDB   ;



CREATE TABLE IF NOT EXISTS `og_project_tasks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `title` text ,
  `text` text ,
  `due_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `start_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `assigned_to_company_id` smallint(5) unsigned DEFAULT NULL,
  `assigned_to_user_id` int(10) unsigned DEFAULT NULL,
  `assigned_on` datetime DEFAULT NULL,
  `assigned_by_id` int(10) unsigned DEFAULT NULL,
  `time_estimate` int(10) unsigned NOT NULL DEFAULT '0',
  `completed_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `completed_by_id` int(10) unsigned DEFAULT NULL,
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by_id` int(10) unsigned DEFAULT NULL,
  `updated_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_by_id` int(10) unsigned DEFAULT NULL,
  `trashed_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `trashed_by_id` int(10) unsigned DEFAULT NULL,
  `archived_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `archived_by_id` int(10) unsigned DEFAULT NULL,
  `started_on` datetime DEFAULT NULL,
  `started_by_id` int(10) unsigned NOT NULL,
  `priority` int(10) unsigned DEFAULT '200',
  `state` int(10) unsigned DEFAULT NULL,
  `order` int(10) unsigned DEFAULT '0',
  `milestone_id` int(10) unsigned DEFAULT NULL,
  `is_private` tinyint(1) NOT NULL DEFAULT '0',
  `is_template` tinyint(1) NOT NULL DEFAULT '0',
  `from_template_id` int(10) NOT NULL DEFAULT '0',
  `repeat_end` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `repeat_forever` tinyint(1) NOT NULL,
  `repeat_num` int(10) unsigned NOT NULL DEFAULT '0',
  `repeat_d` int(10) unsigned NOT NULL,
  `repeat_m` int(10) unsigned NOT NULL,
  `repeat_y` int(10) unsigned NOT NULL,
  `repeat_by` varchar(15)  NOT NULL DEFAULT '',
  `object_subtype` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `completed_on` (`completed_on`),
  KEY `created_on` (`created_on`),
  KEY `order` (`order`)
) ENGINE=InnoDB   ;

CREATE TABLE IF NOT EXISTS `og_project_users` (
  `project_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_on` datetime DEFAULT NULL,
  `created_by_id` int(10) unsigned NOT NULL DEFAULT '0',
  `can_read_messages` tinyint(1) unsigned DEFAULT '0',
  `can_write_messages` tinyint(1) unsigned DEFAULT '0',
  `can_read_tasks` tinyint(1) unsigned DEFAULT '0',
  `can_write_tasks` tinyint(1) unsigned DEFAULT '0',
  `can_read_milestones` tinyint(1) unsigned DEFAULT '0',
  `can_write_milestones` tinyint(1) unsigned DEFAULT '0',
  `can_read_files` tinyint(1) unsigned DEFAULT '0',
  `can_write_files` tinyint(1) unsigned DEFAULT '0',
  `can_read_events` tinyint(1) unsigned DEFAULT '0',
  `can_write_events` tinyint(1) unsigned DEFAULT '0',
  `can_read_weblinks` tinyint(1) unsigned DEFAULT '0',
  `can_write_weblinks` tinyint(1) unsigned DEFAULT '0',
  `can_read_mails` tinyint(1) unsigned DEFAULT '0',
  `can_write_mails` tinyint(1) unsigned DEFAULT '0',
  `can_read_contacts` tinyint(1) unsigned DEFAULT '0',
  `can_write_contacts` tinyint(1) unsigned DEFAULT '0',
  `can_read_comments` tinyint(1) unsigned DEFAULT '0',
  `can_write_comments` tinyint(1) unsigned DEFAULT '0',
  `can_assign_to_owners` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `can_assign_to_other` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`project_id`,`user_id`)
) ENGINE=InnoDB ;






CREATE TABLE IF NOT EXISTS `og_project_webpages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `url` text ,
  `title` varchar(100)  DEFAULT '',
  `description` text ,
  `created_on` datetime DEFAULT NULL,
  `created_by_id` int(10) unsigned NOT NULL DEFAULT '0',
  `updated_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_by_id` int(10) unsigned DEFAULT NULL,
  `trashed_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `trashed_by_id` int(10) unsigned DEFAULT NULL,
  `is_private` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `archived_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `archived_by_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB   ;

CREATE TABLE IF NOT EXISTS `og_queued_emails` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `to` text ,
  `from` text ,
  `subject` text ,
  `body` text ,
  `timestamp` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB   ;


CREATE TABLE IF NOT EXISTS `og_read_objects` (
  `rel_object_manager` varchar(50)  NOT NULL DEFAULT '',
  `rel_object_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `is_read` int(1) NOT NULL DEFAULT '0',
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`rel_object_manager`,`rel_object_id`,`user_id`)
) ENGINE=InnoDB ;


CREATE TABLE IF NOT EXISTS `og_reports` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255)  NOT NULL,
  `description` varchar(255)  NOT NULL,
  `object_type` varchar(255)  NOT NULL,
  `order_by` varchar(255)  NOT NULL,
  `is_order_by_asc` tinyint(1) NOT NULL,
  `workspace` int(11) NOT NULL,
  `tags` varchar(45)  NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB   ;


CREATE TABLE IF NOT EXISTS `og_report_columns` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `report_id` int(10) NOT NULL,
  `custom_property_id` int(10) NOT NULL,
  `field_name` varchar(255)  NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB   ;


CREATE TABLE IF NOT EXISTS `og_report_conditions` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `report_id` int(10) NOT NULL,
  `custom_property_id` int(10) NOT NULL,
  `field_name` varchar(255)  NOT NULL,
  `condition` varchar(255)  NOT NULL,
  `value` varchar(255)  NOT NULL,
  `is_parametrizable` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB   ;


CREATE TABLE IF NOT EXISTS `og_searchable_objects` (
  `rel_object_manager` varchar(50)  NOT NULL DEFAULT '',
  `rel_object_id` int(10) unsigned NOT NULL DEFAULT '0',
  `column_name` varchar(50)  NOT NULL DEFAULT '',
  `content` text  NOT NULL,
  `project_id` int(10) unsigned NOT NULL DEFAULT '0',
  `is_private` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`rel_object_manager`,`rel_object_id`,`column_name`),
  KEY `project_id` (`project_id`),
  FULLTEXT KEY `content` (`content`)
) ENGINE=MyISAM ;


CREATE TABLE IF NOT EXISTS `og_shared_objects` (
  `object_id` int(10) unsigned NOT NULL,
  `object_manager` varchar(45)  NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `created_on` datetime NOT NULL,
  `created_by_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`object_id`,`object_manager`,`user_id`)
) ENGINE=InnoDB ;


CREATE TABLE IF NOT EXISTS `og_tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tag` varchar(30)  NOT NULL DEFAULT '',
  `rel_object_id` int(10) unsigned NOT NULL DEFAULT '0',
  `rel_object_manager` varchar(50)  NOT NULL DEFAULT '',
  `created_on` datetime DEFAULT NULL,
  `created_by_id` int(10) unsigned NOT NULL DEFAULT '0',
  `is_private` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `tag` (`tag`),
  KEY `object_id` (`rel_object_id`,`rel_object_manager`)
) ENGINE=InnoDB   ;

CREATE TABLE IF NOT EXISTS `og_templates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255)  NOT NULL DEFAULT '',
  `description` text ,
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by_id` int(10) unsigned NOT NULL,
  `updated_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_by_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `updated_on` (`updated_on`)
) ENGINE=InnoDB   ;

CREATE TABLE IF NOT EXISTS `og_template_objects` (
  `template_id` int(10) unsigned NOT NULL DEFAULT '0',
  `object_manager` varchar(50)  NOT NULL DEFAULT '',
  `object_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_by_id` int(10) unsigned DEFAULT NULL,
  `created_on` datetime DEFAULT NULL,
  PRIMARY KEY (`template_id`,`object_manager`,`object_id`)
) ENGINE=InnoDB ;


CREATE TABLE IF NOT EXISTS `og_template_object_properties` (
  `template_id` int(10) NOT NULL,
  `object_id` int(10) NOT NULL,
  `object_manager` varchar(50)  NOT NULL,
  `property` varchar(255)  NOT NULL,
  `value` text  NOT NULL,
  PRIMARY KEY (`template_id`,`object_id`,`object_manager`,`property`)
) ENGINE=InnoDB ;

CREATE TABLE IF NOT EXISTS `og_template_parameters` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `template_id` int(10) NOT NULL,
  `name` varchar(255)  NOT NULL,
  `type` varchar(255)  NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB   ;


CREATE TABLE IF NOT EXISTS `og_timeslots` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `object_id` int(10) unsigned NOT NULL,
  `object_manager` varchar(50)  NOT NULL,
  `start_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_id` int(10) unsigned NOT NULL,
  `description` text  NOT NULL,
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by_id` int(10) unsigned NOT NULL,
  `updated_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_by_id` int(10) unsigned NOT NULL,
  `paused_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `subtract` int(10) unsigned NOT NULL DEFAULT '0',
  `fixed_billing` float NOT NULL DEFAULT '0',
  `hourly_billing` float NOT NULL DEFAULT '0',
  `is_fixed_billing` float NOT NULL DEFAULT '0',
  `billing_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ObjectID` (`object_id`,`object_manager`)
) ENGINE=InnoDB   ;



CREATE TABLE IF NOT EXISTS `og_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `personal_project_id` int(10) unsigned NOT NULL DEFAULT '0',
  `username` varchar(50)  NOT NULL DEFAULT '',
  `email` varchar(100)  DEFAULT NULL,
  `token` varchar(40)  NOT NULL DEFAULT '',
  `salt` varchar(13)  NOT NULL DEFAULT '',
  `twister` varchar(10)  NOT NULL DEFAULT '',
  `display_name` varchar(50)  DEFAULT NULL,
  `title` varchar(30)  DEFAULT NULL,
  `avatar_file` varchar(44)  DEFAULT NULL,
  `timezone` float(3,1) NOT NULL DEFAULT '0.0',
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by_id` int(10) unsigned DEFAULT NULL,
  `updated_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_by_id` int(10) unsigned DEFAULT NULL,
  `last_login` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_visit` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_activity` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `can_edit_company_data` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `can_manage_security` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `can_manage_workspaces` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `can_manage_configuration` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `can_manage_contacts` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `can_manage_templates` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `can_manage_reports` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `can_manage_time` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `can_add_mail_accounts` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `auto_assign` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `default_billing_id` int(10) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `last_visit` (`last_visit`),
  KEY `company_id` (`company_id`),
  KEY `last_login` (`last_login`)
) ENGINE=InnoDB   ;


CREATE TABLE IF NOT EXISTS `og_user_passwords` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL,
  `password` varchar(40)  NOT NULL,
  `password_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB   ;



CREATE TABLE IF NOT EXISTS `og_user_ws_config_categories` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50)  NOT NULL DEFAULT '',
  `is_system` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `category_order` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `order` (`category_order`)
) ENGINE=InnoDB;



CREATE TABLE IF NOT EXISTS `og_user_ws_config_options` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_name` varchar(30)  NOT NULL DEFAULT '',
  `name` varchar(50)  NOT NULL DEFAULT '',
  `default_value` text ,
  `config_handler_class` varchar(50)  NOT NULL DEFAULT '',
  `is_system` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `option_order` smallint(5) unsigned NOT NULL DEFAULT '0',
  `dev_comment` varchar(255)  DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `order` (`option_order`),
  KEY `category_id` (`category_name`)
) ENGINE=InnoDB;



CREATE TABLE IF NOT EXISTS `og_user_ws_config_option_values` (
  `option_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `workspace_id` int(10) unsigned NOT NULL DEFAULT '0',
  `value` text ,
  PRIMARY KEY (`option_id`,`user_id`,`workspace_id`),
  KEY `option_id` (`option_id`)
) ENGINE=InnoDB ;

CREATE TABLE IF NOT EXISTS `og_workspace_billings` (
  `project_id` int(10) unsigned NOT NULL,
  `billing_id` int(10) unsigned NOT NULL,
  `value` float NOT NULL DEFAULT '0',
  `created_on` datetime DEFAULT NULL,
  `created_by_id` int(10) unsigned NOT NULL DEFAULT '0',
  `updated_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_by_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`project_id`,`billing_id`)
) ENGINE=InnoDB ;



CREATE TABLE IF NOT EXISTS `og_workspace_objects` (
  `workspace_id` int(10) unsigned NOT NULL DEFAULT '0',
  `object_manager` varchar(50)  NOT NULL DEFAULT '',
  `object_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_by_id` int(10) unsigned DEFAULT NULL,
  `created_on` datetime DEFAULT NULL,
  PRIMARY KEY (`workspace_id`,`object_manager`,`object_id`),
  KEY `workspace_id` (`workspace_id`),
  KEY `object_manager` (`object_manager`),
  KEY `object_id` (`object_id`)
) ENGINE=InnoDB ;



CREATE TABLE IF NOT EXISTS `og_workspace_templates` (
  `workspace_id` int(10) unsigned NOT NULL DEFAULT '0',
  `template_id` int(10) unsigned NOT NULL DEFAULT '0',
  `include_subws` int(1) unsigned NOT NULL DEFAULT '0',
  `created_by_id` int(10) unsigned DEFAULT NULL,
  `created_on` datetime DEFAULT NULL,
  PRIMARY KEY (`workspace_id`,`template_id`),
  KEY `workspace_id` (`workspace_id`),
  KEY `object_id` (`template_id`)
) ENGINE=InnoDB ;

INSERT INTO `og_user_ws_config_options` (`id`, `category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`, `dev_comment`) VALUES
(1, 'dashboard', 'show calendar widget', '1', 'BoolConfigHandler', 0, 80, ''),
(2, 'dashboard', 'show late tasks and milestones widget', '1', 'BoolConfigHandler', 0, 100, ''),
(3, 'dashboard', 'show pending tasks widget', '1', 'BoolConfigHandler', 0, 200, ''),
(4, 'dashboard', 'pending tasks widget assigned to filter', '0:0', 'UserCompanyConfigHandler', 0, 210, ''),
(5, 'dashboard', 'show emails widget', '1', 'BoolConfigHandler', 0, 300, ''),
(6, 'dashboard', 'show messages widget', '1', 'BoolConfigHandler', 0, 400, ''),
(7, 'dashboard', 'show documents widget', '1', 'BoolConfigHandler', 0, 500, ''),
(8, 'dashboard', 'show charts widget', '1', 'BoolConfigHandler', 0, 600, ''),
(9, 'dashboard', 'show tasks in progress widget', '1', 'BoolConfigHandler', 0, 700, ''),
(10, 'dashboard', 'show comments widget', '1', 'BoolConfigHandler', 0, 800, ''),
(11, 'dashboard', 'show dashboard info widget', '1', 'BoolConfigHandler', 0, 900, ''),
(12, 'dashboard', 'always show unread mail in dashboard', '0', 'BoolConfigHandler', 0, 10, 'when false, active workspace email is shown'),
(13, 'dashboard', 'calendar_widget_expanded', '1', 'BoolConfigHandler', 1, 0, ''),
(14, 'dashboard', 'emails_widget_expanded', '1', 'BoolConfigHandler', 1, 0, ''),
(15, 'dashboard', 'messages_widget_expanded', '1', 'BoolConfigHandler', 1, 0, ''),
(16, 'dashboard', 'active_tasks_widget_expanded', '1', 'BoolConfigHandler', 1, 0, ''),
(17, 'dashboard', 'pending_tasks_widget_expanded', '1', 'BoolConfigHandler', 1, 0, ''),
(18, 'dashboard', 'late_tasks_widget_expanded', '1', 'BoolConfigHandler', 1, 0, ''),
(19, 'dashboard', 'comments_widget_expanded', '1', 'BoolConfigHandler', 1, 0, ''),
(20, 'dashboard', 'documents_widget_expanded', '1', 'BoolConfigHandler', 1, 0, ''),
(21, 'dashboard', 'charts_widget_expanded', '1', 'BoolConfigHandler', 1, 0, ''),
(22, 'dashboard', 'show getting started widget', '1', 'BoolConfigHandler', 0, 1000, NULL),
(23, 'dashboard', 'getting_started_widget_expanded', '1', 'BoolConfigHandler', 1, 0, NULL),
(24, 'dashboard', 'dashboard_info_widget_expanded', '1', 'BoolConfigHandler', 1, 0, ''),
(25, 'dashboard', 'show_two_weeks_calendar', '1', 'BoolConfigHandler', 0, 0, NULL),
(26, 'task panel', 'can notify from quick add', '1', 'BoolConfigHandler', 0, 0, 'Notification checkbox default value'),
(27, 'task panel', 'tasksShowWorkspaces', '1', 'BoolConfigHandler', 1, 0, ''),
(28, 'task panel', 'tasksShowTime', '1', 'BoolConfigHandler', 1, 0, ''),
(29, 'task panel', 'tasksShowDates', '1', 'BoolConfigHandler', 1, 0, ''),
(30, 'task panel', 'tasksShowTags', '1', 'BoolConfigHandler', 1, 0, ''),
(31, 'task panel', 'tasksGroupBy', 'milestone', 'StringConfigHandler', 1, 0, ''),
(32, 'task panel', 'tasksOrderBy', 'priority', 'StringConfigHandler', 1, 0, ''),
(33, 'task panel', 'task panel status', '1', 'IntegerConfigHandler', 1, 0, ''),
(34, 'task panel', 'task panel filter', 'assigned_to', 'StringConfigHandler', 1, 0, ''),
(35, 'task panel', 'task panel filter value', '0:0', 'UserCompanyConfigHandler', 1, 0, ''),
(36, 'task panel', 'noOfTasks', '8', 'IntegerConfigHandler', 0, 100, NULL),
(37, 'task panel', 'task_display_limit', '500', 'IntegerConfigHandler', 0, 200, NULL),
(38, 'time panel', 'TM show time type', '0', 'IntegerConfigHandler', 1, 0, ''),
(39, 'time panel', 'TM report show time type', '0', 'IntegerConfigHandler', 1, 0, ''),
(40, 'time panel', 'TM user filter', '0', 'IntegerConfigHandler', 1, 0, ''),
(41, 'time panel', 'TM tasks user filter', '0', 'IntegerConfigHandler', 1, 0, ''),
(42, 'general', 'localization', '', 'LocalizationConfigHandler', 0, 100, ''),
(43, 'general', 'initialWorkspace', '0', 'InitialWorkspaceConfigHandler', 0, 200, ''),
(44, 'general', 'search_engine', 'match', 'SearchEngineConfigHandler', 0, 700, ''),
(45, 'general', 'lastAccessedWorkspace', '0', 'IntegerConfigHandler', 1, 0, ''),
(46, 'general', 'rememberGUIState', '1', 'RememberGUIConfigHandler', 0, 300, ''),
(47, 'general', 'work_day_start_time', '9:00', 'TimeConfigHandler', 0, 400, 'Work day start time'),
(48, 'general', 'time_format_use_24', '0', 'BoolConfigHandler', 0, 500, 'Use 24 hours time format'),
(49, 'general', 'date_format', 'd/m/Y', 'StringConfigHandler', 0, 600, 'Date objects will be displayed using this format.'),
(50, 'general', 'descriptive_date_format', 'l, j F', 'StringConfigHandler', 0, 700, 'Descriptive dates will be displayed using this format.'),
(51, 'general', 'custom_report_tab', 'tasks', 'StringConfigHandler', 1, 0, NULL),
(52, 'general', 'show_context_help', 'until_close', 'ShowContextHelpConfigHandler', 0, 0, NULL),
(53, 'general', 'last_mail_format', 'html', 'StringConfigHandler', 1, 0, NULL),
(54, 'general', 'amount_objects_to_show', '5', 'IntegerConfigHandler', 0, 0, NULL),
(55, 'general', 'reset_password', '', 'StringConfigHandler', 1, 0, 'Used to store per-user tokens to validate password reset requests'),
(56, 'general', 'drag_drop_prompt', 'prompt', 'DragDropPromptConfigHandler', 0, 0, NULL),
(57, 'general', 'autodetect_time_zone', '1', 'BoolConfigHandler', 0, 0, NULL),
(58, 'general', 'detect_mime_type_from_extension', '0', 'BoolConfigHandler', 0, 0, NULL),
(59, 'calendar panel', 'calendar view type', 'viewweek', 'StringConfigHandler', 1, 0, ''),
(60, 'calendar panel', 'calendar user filter', '0', 'IntegerConfigHandler', 1, 0, ''),
(61, 'calendar panel', 'calendar status filter', '', 'StringConfigHandler', 1, 0, ''),
(62, 'calendar panel', 'start_monday', '', 'BoolConfigHandler', 0, 0, ''),
(63, 'calendar panel', 'show_week_numbers', '', 'BoolConfigHandler', 0, 0, ''),
(64, 'mails panel', 'view deleted accounts emails', '1', 'BoolConfigHandler', 0, 0, NULL),
(65, 'mails panel', 'block_email_images', '1', 'BoolConfigHandler', 0, 0, NULL),
(66, 'mails panel', 'draft_autosave_timeout', '60', 'IntegerConfigHandler', 0, 100, NULL),
(67, 'mails panel', 'attach_docs_content', '0', 'BoolConfigHandler', 0, 0, NULL),
(68, 'mails panel', 'email_polling', '0', 'IntegerConfigHandler', 1, 0, NULL),
(69, 'mails panel', 'show_unread_on_title', '0', 'BoolConfigHandler', 1, 0, NULL),
(70, 'mails panel', 'max_spam_level', '0', 'IntegerConfigHandler', 0, 100, NULL),
(71, 'mails panel', 'create_contacts_from_email_recipients', '0', 'BoolConfigHandler', 0, 101, NULL),
(72, 'mails panel', 'mail_drag_drop_prompt', 'prompt', 'MailDragDropPromptConfigHandler', 0, 102, NULL),
(73, 'mails panel', 'show_emails_as_conversations', '1', 'BoolConfigHandler', 0, 0, NULL),
(74, 'mails panel', 'mails account filter', '', 'StringConfigHandler', 1, 0, NULL),
(75, 'mails panel', 'mails classification filter', 'all', 'StringConfigHandler', 1, 0, NULL),
(76, 'mails panel', 'mails read filter', 'all', 'StringConfigHandler', 1, 0, NULL),
(77, 'mails panel', 'hide_quoted_text_in_emails', '1', 'BoolConfigHandler', 0, 110, NULL),
(78, 'context help', 'show_tasks_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(79, 'context help', 'show_account_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(80, 'context help', 'show_active_tasks_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(81, 'context help', 'show_general_timeslots_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(82, 'context help', 'show_late_tasks_widget_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(83, 'context help', 'show_pending_tasks_widget_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(84, 'context help', 'show_documents_widget_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(85, 'context help', 'show_active_tasks_widget_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(86, 'context help', 'show_calendar_widget_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(87, 'context help', 'show_messages_widget_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(88, 'context help', 'show_dashboard_info_widget_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(89, 'context help', 'show_comments_widget_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(90, 'context help', 'show_emails_widget_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(91, 'context help', 'show_reporting_panel_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(92, 'context help', 'show_add_file_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(93, 'context help', 'show_administration_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(94, 'context help', 'show_member_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(95, 'context help', 'show_add_contact_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(96, 'context help', 'show_add_company_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(97, 'context help', 'show_upload_file_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(98, 'context help', 'show_upload_file_workspace_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(99, 'context help', 'show_upload_file_tags_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(100, 'context help', 'show_upload_file_description_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(101, 'context help', 'show_upload_file_custom_properties_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(102, 'context help', 'show_upload_file_subscribers_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(103, 'context help', 'show_upload_file_linked_objects_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(104, 'context help', 'show_add_note_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(105, 'context help', 'show_add_note_workspace_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(106, 'context help', 'show_add_note_tags_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(107, 'context help', 'show_add_note_custom_properties_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(108, 'context help', 'show_add_note_subscribers_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(109, 'context help', 'show_add_note_linked_object_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(110, 'context help', 'show_add_milestone_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(111, 'context help', 'show_add_milestone_workspace_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(112, 'context help', 'show_add_milestone_tags_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(113, 'context help', 'show_add_milestone_description_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(114, 'context help', 'show_add_milestone_reminders_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(115, 'context help', 'show_add_milestone_custom_properties_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(116, 'context help', 'show_add_milestone_linked_object_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(117, 'context help', 'show_add_milestone_subscribers_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(118, 'context help', 'show_add_workspace_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(119, 'context help', 'show_print_report_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(120, 'context help', 'show_add_task_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(121, 'context help', 'show_add_task_workspace_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(122, 'context help', 'show_add_task_tags_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(123, 'context help', 'show_add_task_reminders_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(124, 'context help', 'show_add_task_custom_properties_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(125, 'context help', 'show_add_task_linked_objects_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(126, 'context help', 'show_add_task_subscribers_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(127, 'context help', 'show_list_task_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(128, 'context help', 'show_time_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(129, 'context help', 'show_add_webpage_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(130, 'context help', 'show_add_webpage_workspace_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(131, 'context help', 'show_add_webpage_tags_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(132, 'context help', 'show_add_webpage_description_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(133, 'context help', 'show_add_webpage_custom_properties_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(134, 'context help', 'show_add_webpage_subscribers_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(135, 'context help', 'show_add_webpage_linked_objects_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(136, 'context help', 'show_add_event_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(137, 'context help', 'show_add_event_workspace_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(138, 'context help', 'show_add_event_tag_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(139, 'context help', 'show_add_event_description_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(140, 'context help', 'show_add_event_repeat_options_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(141, 'context help', 'show_add_event_reminders_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(142, 'context help', 'show_add_event_custom_properties_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(143, 'context help', 'show_add_event_subscribers_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(144, 'context help', 'show_add_event_linked_objects_context_help', '1', 'BoolConfigHandler', 1, 0, NULL),
(145, 'context help', 'show_add_event_inivitation_context_help', '1', 'BoolConfigHandler', 1, 0, NULL);


INSERT INTO `og_user_ws_config_option_values` (`option_id`, `user_id`, `workspace_id`, `value`) VALUES
(45, 1, 0, '0'),
(74, 1, 0, '1'),
(75, 1, 0, 'all'),
(76, 1, 0, 'all');

INSERT INTO `og_project_users` (`project_id`, `user_id`, `created_on`, `created_by_id`, `can_read_messages`, `can_write_messages`, `can_read_tasks`, `can_write_tasks`, `can_read_milestones`, `can_write_milestones`, `can_read_files`, `can_write_files`, `can_read_events`, `can_write_events`, `can_read_weblinks`, `can_write_weblinks`, `can_read_mails`, `can_write_mails`, `can_read_contacts`, `can_write_contacts`, `can_read_comments`, `can_write_comments`, `can_assign_to_owners`, `can_assign_to_other`) VALUES
(1, 1, '2010-02-17 23:41:45', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1);
