-- phpMyAdmin SQL Dump
-- version 3.2.1-rc1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 27, 2009 at 12:44 PM
-- Server version: 5.0.75
-- PHP Version: 5.2.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE IF NOT EXISTS `accounts` (
  `id` char(36) NOT NULL,
  `name` varchar(150) default NULL,
  `date_entered` datetime default NULL,
  `date_modified` datetime default NULL,
  `modified_user_id` char(36) default NULL,
  `created_by` char(36) default NULL,
  `description` text,
  `deleted` tinyint(1) default '0',
  `assigned_user_id` char(36) default NULL,
  `account_type` varchar(50) default NULL,
  `industry` varchar(50) default NULL,
  `annual_revenue` varchar(25) default NULL,
  `phone_fax` varchar(25) default NULL,
  `billing_address_street` varchar(150) default NULL,
  `billing_address_city` varchar(100) default NULL,
  `billing_address_state` varchar(100) default NULL,
  `billing_address_postalcode` varchar(20) default NULL,
  `billing_address_country` varchar(255) default NULL,
  `rating` varchar(25) default NULL,
  `phone_office` varchar(25) default NULL,
  `phone_alternate` varchar(25) default NULL,
  `website` varchar(255) default NULL,
  `ownership` varchar(100) default NULL,
  `employees` varchar(10) default NULL,
  `ticker_symbol` varchar(10) default NULL,
  `shipping_address_street` varchar(150) default NULL,
  `shipping_address_city` varchar(100) default NULL,
  `shipping_address_state` varchar(100) default NULL,
  `shipping_address_postalcode` varchar(20) default NULL,
  `shipping_address_country` varchar(255) default NULL,
  `parent_id` char(36) default NULL,
  `sic_code` varchar(10) default NULL,
  `campaign_id` char(36) default NULL,
  PRIMARY KEY  (`id`),
  KEY `idx_accnt_id_del` (`id`,`deleted`),
  KEY `idx_accnt_assigned_del` (`deleted`,`assigned_user_id`),
  KEY `idx_accnt_parent_id` (`parent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `accounts`
--


-- --------------------------------------------------------

--
-- Table structure for table `accounts_audit`
--

CREATE TABLE IF NOT EXISTS `accounts_audit` (
  `id` char(36) NOT NULL,
  `parent_id` char(36) NOT NULL,
  `date_created` datetime default NULL,
  `created_by` varchar(36) default NULL,
  `field_name` varchar(100) default NULL,
  `data_type` varchar(100) default NULL,
  `before_value_string` varchar(255) default NULL,
  `after_value_string` varchar(255) default NULL,
  `before_value_text` text,
  `after_value_text` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `accounts_audit`
--


-- --------------------------------------------------------

--
-- Table structure for table `accounts_bugs`
--

CREATE TABLE IF NOT EXISTS `accounts_bugs` (
  `id` varchar(36) NOT NULL,
  `account_id` varchar(36) default NULL,
  `bug_id` varchar(36) default NULL,
  `date_modified` datetime default NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_acc_bug_acc` (`account_id`),
  KEY `idx_acc_bug_bug` (`bug_id`),
  KEY `idx_account_bug` (`account_id`,`bug_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `accounts_bugs`
--


-- --------------------------------------------------------

--
-- Table structure for table `accounts_cases`
--

CREATE TABLE IF NOT EXISTS `accounts_cases` (
  `id` varchar(36) NOT NULL,
  `account_id` varchar(36) default NULL,
  `case_id` varchar(36) default NULL,
  `date_modified` datetime default NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_acc_case_acc` (`account_id`),
  KEY `idx_acc_acc_case` (`case_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `accounts_cases`
--


-- --------------------------------------------------------

--
-- Table structure for table `accounts_contacts`
--

CREATE TABLE IF NOT EXISTS `accounts_contacts` (
  `id` varchar(36) NOT NULL,
  `contact_id` varchar(36) default NULL,
  `account_id` varchar(36) default NULL,
  `date_modified` datetime default NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_account_contact` (`account_id`,`contact_id`),
  KEY `idx_contid_del_accid` (`contact_id`,`deleted`,`account_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `accounts_contacts`
--


-- --------------------------------------------------------

--
-- Table structure for table `accounts_opportunities`
--

CREATE TABLE IF NOT EXISTS `accounts_opportunities` (
  `id` varchar(36) NOT NULL,
  `opportunity_id` varchar(36) default NULL,
  `account_id` varchar(36) default NULL,
  `date_modified` datetime default NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_account_opportunity` (`account_id`,`opportunity_id`),
  KEY `idx_oppid_del_accid` (`opportunity_id`,`deleted`,`account_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `accounts_opportunities`
--


-- --------------------------------------------------------

--
-- Table structure for table `acl_actions`
--

CREATE TABLE IF NOT EXISTS `acl_actions` (
  `id` char(36) NOT NULL,
  `date_entered` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  `modified_user_id` char(36) NOT NULL,
  `created_by` char(36) default NULL,
  `name` varchar(150) default NULL,
  `category` varchar(100) default NULL,
  `acltype` varchar(100) default NULL,
  `aclaccess` int(3) default NULL,
  `deleted` tinyint(1) default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_aclaction_id_del` (`id`,`deleted`),
  KEY `idx_category_name` (`category`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `acl_actions`
--

INSERT INTO `acl_actions` (`id`, `date_entered`, `date_modified`, `modified_user_id`, `created_by`, `name`, `category`, `acltype`, `aclaccess`, `deleted`) VALUES
('8284f4a5-7fa8-8dfa-d839-4a6d7fffdbfa', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'access', 'Leads', 'module', 89, 0),
('82c299ba-cb5d-2753-07cf-4a6d7f2c5f43', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'view', 'Leads', 'module', 90, 0),
('82fad761-65e9-8d5e-291c-4a6d7fc69383', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'list', 'Leads', 'module', 90, 0),
('8338b656-9926-7ca5-dc60-4a6d7f3f88c6', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'edit', 'Leads', 'module', 90, 0),
('8370585c-e51f-cce1-ece1-4a6d7ff1f883', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'delete', 'Leads', 'module', 90, 0),
('83a80f59-ab70-74cc-d2ab-4a6d7fb9d6c0', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'import', 'Leads', 'module', 90, 0),
('83e0d579-5307-cb74-5eda-4a6d7fe1191b', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'export', 'Leads', 'module', 90, 0),
('8b545caf-4bdc-6d11-392b-4a6d7f86bd73', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'access', 'Contacts', 'module', 89, 0),
('8b8ef0ab-7f20-cce9-f363-4a6d7fafcd0c', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'view', 'Contacts', 'module', 90, 0),
('8bc6951d-362c-73c0-2751-4a6d7f2fcf8f', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'list', 'Contacts', 'module', 90, 0),
('8bfe1d4b-8d13-768f-3fa7-4a6d7fd849f0', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'edit', 'Contacts', 'module', 90, 0),
('8c35ac9b-8a88-2bc7-77d2-4a6d7f03d2b7', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'delete', 'Contacts', 'module', 90, 0),
('8c6da0e3-3460-12cd-7673-4a6d7feb62ee', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'import', 'Contacts', 'module', 90, 0),
('8cbc83ef-1699-3b84-60f4-4a6d7f33fd0d', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'export', 'Contacts', 'module', 90, 0),
('95234bfb-148f-5051-aaf9-4a6d7f0da2bd', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'access', 'Accounts', 'module', 89, 0),
('957d7039-118c-91eb-8fb9-4a6d7f8f3151', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'view', 'Accounts', 'module', 90, 0),
('95d48405-d4fc-ed42-ae36-4a6d7f1e6dd9', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'list', 'Accounts', 'module', 90, 0),
('962c044c-fa8d-eec1-8c92-4a6d7f6093ab', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'edit', 'Accounts', 'module', 90, 0),
('96846b1e-c346-2fd5-556f-4a6d7fd5a3b0', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'delete', 'Accounts', 'module', 90, 0),
('96dbc110-a57a-d3a9-743e-4a6d7f2e40cb', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'import', 'Accounts', 'module', 90, 0),
('9733b56b-96a8-8906-cd50-4a6d7f4e81dd', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'export', 'Accounts', 'module', 90, 0),
('9ff880b8-0747-9665-3102-4a6d7f3b229e', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'access', 'Opportunities', 'module', 89, 0),
('a056eec5-afbf-ba47-59f9-4a6d7f763b5f', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'view', 'Opportunities', 'module', 90, 0),
('a0afe459-3a1f-5e76-6425-4a6d7f5467bc', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'list', 'Opportunities', 'module', 90, 0),
('a10c8016-8624-6d50-4771-4a6d7f314726', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'edit', 'Opportunities', 'module', 90, 0),
('a166ec41-9790-3437-c7b6-4a6d7fc42a7f', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'delete', 'Opportunities', 'module', 90, 0),
('a1c049ee-2c2a-0a08-614d-4a6d7f827c33', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'import', 'Opportunities', 'module', 90, 0),
('a21a4015-68b4-366e-1fa1-4a6d7f9b41dd', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'export', 'Opportunities', 'module', 90, 0),
('a9ca1e0a-4ec5-b3ee-e683-4a6d7f04c333', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'access', 'Cases', 'module', 89, 0),
('aa255a30-4657-6639-c6fb-4a6d7fc6a740', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'view', 'Cases', 'module', 90, 0),
('aa7d3b95-9adc-c5ef-c6b5-4a6d7f33f3cf', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'list', 'Cases', 'module', 90, 0),
('aad870f8-c05f-618b-b572-4a6d7faeb2e0', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'edit', 'Cases', 'module', 90, 0),
('ab3151f1-afb5-8446-9503-4a6d7f167ebb', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'delete', 'Cases', 'module', 90, 0),
('ab892c62-9ee9-34d2-ecab-4a6d7f2f6c5e', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'import', 'Cases', 'module', 90, 0),
('abe2934c-f701-4d86-9673-4a6d7f1e9ef3', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'export', 'Cases', 'module', 90, 0),
('b28e1d50-831f-ec8a-05cf-4a6d7f731867', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'access', 'Notes', 'module', 89, 0),
('b2f061fd-56f1-638d-c7d5-4a6d7f8060f4', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'view', 'Notes', 'module', 90, 0),
('b35e897d-eccb-9161-613d-4a6d7f621f48', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'list', 'Notes', 'module', 90, 0),
('b3c591fb-7928-1225-6b9f-4a6d7f6e20ea', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'edit', 'Notes', 'module', 90, 0),
('b421ee96-6d86-3052-7d59-4a6d7f826041', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'delete', 'Notes', 'module', 90, 0),
('b47d221e-954d-c88b-5eca-4a6d7ffcb406', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'import', 'Notes', 'module', 90, 0),
('b4da8173-1a3c-42d9-5a97-4a6d7ffbdcae', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'export', 'Notes', 'module', 90, 0),
('b8efce60-6695-7c1a-0733-4a6d7fa5a43f', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'access', 'EmailTemplates', 'module', 89, 0),
('b96a5b5e-faca-efc4-7674-4a6d7fd6a7ca', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'view', 'EmailTemplates', 'module', 90, 0),
('b9a4f82d-7c32-91d3-3168-4a6d7f55a309', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'list', 'EmailTemplates', 'module', 90, 0),
('b9deb6ba-61d0-f642-5a99-4a6d7fcdb64e', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'edit', 'EmailTemplates', 'module', 90, 0),
('ba1be9bb-14d6-65a0-9f54-4a6d7f1c221a', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'delete', 'EmailTemplates', 'module', 90, 0),
('ba574d03-a377-2384-ca6d-4a6d7f537f88', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'import', 'EmailTemplates', 'module', 90, 0),
('ba9359e9-e721-725d-e0e6-4a6d7fc60c68', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'export', 'EmailTemplates', 'module', 90, 0),
('bfca0395-25c6-8a45-ccce-4a6d7f2e22fd', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'access', 'Calls', 'module', 89, 0),
('c006eed3-f24e-d93d-ab6d-4a6d7f76eedf', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'view', 'Calls', 'module', 90, 0),
('c03ffa5c-35d2-d0cb-0cd9-4a6d7f40abba', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'list', 'Calls', 'module', 90, 0),
('c079affa-fc84-ef3e-15a8-4a6d7f87e3ff', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'edit', 'Calls', 'module', 90, 0),
('c0b3960d-914e-87eb-b467-4a6d7fb051e4', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'delete', 'Calls', 'module', 90, 0),
('c0ef2eaa-3049-7ee1-0c23-4a6d7fc7e644', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'import', 'Calls', 'module', 90, 0),
('c129a517-fbc5-4911-24c2-4a6d7fcf400f', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'export', 'Calls', 'module', 90, 0),
('c609ec8c-843a-a37d-cc19-4a6d7fb40b9b', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'access', 'Emails', 'module', 89, 0),
('c64ac857-8ad1-3980-270a-4a6d7f7f5df0', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'view', 'Emails', 'module', 90, 0),
('c6897361-4720-eda7-b78a-4a6d7f2a3ede', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'list', 'Emails', 'module', 90, 0),
('c6c852af-025a-4051-ba8a-4a6d7f9e8f93', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'edit', 'Emails', 'module', 90, 0),
('c7137ab2-4dca-e706-ab82-4a6d7f6b72a4', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'delete', 'Emails', 'module', 90, 0),
('c76460fb-a93b-c98d-bd18-4a6d7fc2be0c', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'import', 'Emails', 'module', 90, 0),
('c7b54048-97ee-a275-787d-4a6d7f2aa9d3', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'export', 'Emails', 'module', 90, 0),
('ccd02770-de64-51da-396d-4a6d7f573064', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'access', 'Meetings', 'module', 89, 0),
('cd241c0c-e041-ab57-2bdb-4a6d7ff059ae', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'view', 'Meetings', 'module', 90, 0),
('cd76a4ff-b167-1302-79de-4a6d7f351a94', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'list', 'Meetings', 'module', 90, 0),
('cdc9a60d-063d-a599-f38a-4a6d7f4ff758', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'edit', 'Meetings', 'module', 90, 0),
('ce1ca16a-f501-4dc8-5df9-4a6d7f405821', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'delete', 'Meetings', 'module', 90, 0),
('ce6f3526-a0cc-5361-a570-4a6d7fbe4e80', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'import', 'Meetings', 'module', 90, 0),
('cec47625-9a11-14b8-b965-4a6d7f09fd81', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'export', 'Meetings', 'module', 90, 0),
('d695d887-a848-d695-dcaa-4a6d7f4b03d1', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'access', 'Tasks', 'module', 89, 0),
('d6ecc2f2-57ae-e36e-c392-4a6d7f7977dc', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'view', 'Tasks', 'module', 90, 0),
('d7412101-e0c6-7934-6a0f-4a6d7fde52f1', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'list', 'Tasks', 'module', 90, 0),
('d796eced-fe2a-4c32-1291-4a6d7f307a20', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'edit', 'Tasks', 'module', 90, 0),
('d7e9a126-8e36-0978-eae8-4a6d7f71d903', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'delete', 'Tasks', 'module', 90, 0),
('d836b6c3-6747-1378-ed34-4a6d7fc5015a', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'import', 'Tasks', 'module', 90, 0),
('d8838cd4-3adf-08e5-56e9-4a6d7f0ae90f', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'export', 'Tasks', 'module', 90, 0),
('e425a177-c6c9-0d20-4018-4a6d7fa62671', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'access', 'Trackers', 'Tracker', -99, 0),
('e48a4779-1471-601d-3ab8-4a6d7f787e4b', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'view', 'Trackers', 'Tracker', -99, 0),
('e4ead727-543b-0bd4-3812-4a6d7f6d29bc', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'list', 'Trackers', 'Tracker', -99, 0),
('e54bdc77-b5bc-aef2-1e0d-4a6d7fb3111c', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'edit', 'Trackers', 'Tracker', -99, 0),
('e5ade93f-91c1-0ab2-c083-4a6d7fc180fa', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'delete', 'Trackers', 'Tracker', -99, 0),
('e6148644-b2b6-8cc4-9046-4a6d7fdf5699', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'import', 'Trackers', 'Tracker', -99, 0),
('e6780256-1dc8-4a25-8be5-4a6d7fc23015', '2009-07-27 10:22:38', '2009-07-27 10:22:38', '1', NULL, 'export', 'Trackers', 'Tracker', -99, 0),
('1cff021b-781e-bf27-117a-4a6d7fc7c126', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'access', 'Bugs', 'module', 89, 0),
('21650ff4-d718-4469-9c35-4a6d7f01946a', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'view', 'Bugs', 'module', 90, 0),
('257c07ca-5e63-4122-a8ff-4a6d7fcef52d', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'list', 'Bugs', 'module', 90, 0),
('29960188-d81c-d1d1-5e4d-4a6d7fa08579', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'edit', 'Bugs', 'module', 90, 0),
('2d220b57-5f32-60ad-7bf5-4a6d7f5be0ef', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'delete', 'Bugs', 'module', 90, 0),
('30b00668-c4fe-5b9b-d06f-4a6d7f6bf646', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'import', 'Bugs', 'module', 90, 0),
('34410732-ebb2-02ed-c831-4a6d7f25c34d', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'export', 'Bugs', 'module', 90, 0),
('1ea67b8a-0f61-3aa6-32a7-4a6d7f21f0a3', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'access', 'Project', 'module', 89, 0),
('1eed0f76-1c87-adcf-df87-4a6d7f4047fb', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'view', 'Project', 'module', 90, 0),
('1f2f19c0-6bc0-0666-54f8-4a6d7fcd5e23', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'list', 'Project', 'module', 90, 0),
('1f7305d8-e133-0963-fff5-4a6d7f92aef0', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'edit', 'Project', 'module', 90, 0),
('1fb5eab7-a041-eea5-8f43-4a6d7f379027', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'delete', 'Project', 'module', 90, 0),
('1ffa5c71-6c23-c107-859e-4a6d7fadc050', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'import', 'Project', 'module', 90, 0),
('20482a61-3bda-5254-bbf7-4a6d7fc578b5', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'export', 'Project', 'module', 90, 0),
('2632b830-469d-0252-5a22-4a6d7f311d7c', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'access', 'ProjectTask', 'module', 89, 0),
('26a5b177-f6c5-0a7e-491c-4a6d7f15aed8', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'view', 'ProjectTask', 'module', 90, 0),
('27168689-be60-421d-950e-4a6d7fe6f602', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'list', 'ProjectTask', 'module', 90, 0),
('2787a408-a697-537f-a071-4a6d7fe959cc', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'edit', 'ProjectTask', 'module', 90, 0),
('27fa4cbd-aeb3-661a-c49b-4a6d7f251c9c', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'delete', 'ProjectTask', 'module', 90, 0),
('286c139b-c73e-d269-c196-4a6d7f6f748f', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'import', 'ProjectTask', 'module', 90, 0),
('28df07c5-0ffb-8880-2cdb-4a6d7f7810c3', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'export', 'ProjectTask', 'module', 90, 0),
('32eeef7e-35ea-3f25-2a36-4a6d7fc6976a', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'access', 'EmailMarketing', 'module', 89, 0),
('335d13ae-05ab-b689-a7d4-4a6d7fdaddbc', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'view', 'EmailMarketing', 'module', 90, 0),
('33c96446-dd0b-e467-6183-4a6d7f526492', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'list', 'EmailMarketing', 'module', 90, 0),
('34343cfb-ef5f-2280-a8d9-4a6d7f681cd5', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'edit', 'EmailMarketing', 'module', 90, 0),
('34aa57e5-1de0-4f84-e817-4a6d7f5162e6', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'delete', 'EmailMarketing', 'module', 90, 0),
('35153297-6558-969f-d2f9-4a6d7f3d4ade', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'import', 'EmailMarketing', 'module', 90, 0),
('35821c7e-8182-a968-78ea-4a6d7fff4855', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'export', 'EmailMarketing', 'module', 90, 0),
('3b973787-c5ee-fe76-01fe-4a6d7fb9a530', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'access', 'Campaigns', 'module', 89, 0),
('3c002d9c-3197-fb46-cf66-4a6d7f7769a1', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'view', 'Campaigns', 'module', 90, 0),
('3c66d77c-56e7-0ab8-750f-4a6d7f7de5ee', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'list', 'Campaigns', 'module', 90, 0),
('3cce44e5-e607-266f-2b46-4a6d7f478a95', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'edit', 'Campaigns', 'module', 90, 0),
('3d3518f2-5b80-9011-982f-4a6d7f1ba86e', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'delete', 'Campaigns', 'module', 90, 0),
('3d9dbccc-1769-dd44-fcdd-4a6d7f5555b1', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'import', 'Campaigns', 'module', 90, 0),
('3e064687-04e1-7909-6666-4a6d7f21422e', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'export', 'Campaigns', 'module', 90, 0),
('4695d8df-6acf-6468-b9bb-4a6d7f656a0b', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'access', 'ProspectLists', 'module', 89, 0),
('47086c2f-7620-c0a7-b67e-4a6d7fcf6c9f', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'view', 'ProspectLists', 'module', 90, 0),
('477a8c64-d808-cd63-0587-4a6d7f6dc574', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'list', 'ProspectLists', 'module', 90, 0),
('47ea2ac8-f1c9-9481-5bd9-4a6d7f8c6d7e', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'edit', 'ProspectLists', 'module', 90, 0),
('485d2e69-8fe7-92f5-5749-4a6d7fa0838f', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'delete', 'ProspectLists', 'module', 90, 0),
('48ccb2f6-4ba9-449c-3663-4a6d7f75cf07', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'import', 'ProspectLists', 'module', 90, 0),
('493d5657-b2f0-4c43-10e0-4a6d7f55878d', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'export', 'ProspectLists', 'module', 90, 0),
('4eeee8dc-9fd3-7720-17fb-4a6d7fb3880c', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'access', 'Prospects', 'module', 89, 0),
('4f61e81f-d35b-3268-1669-4a6d7feec5f4', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'view', 'Prospects', 'module', 90, 0),
('4fd306aa-b970-dadc-a660-4a6d7f6232a2', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'list', 'Prospects', 'module', 90, 0),
('50456832-7904-8db9-bf5e-4a6d7ffbda14', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'edit', 'Prospects', 'module', 90, 0),
('50b5f6b3-5f1f-08dc-58ba-4a6d7f3ad8bd', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'delete', 'Prospects', 'module', 90, 0),
('512fc4be-5ea4-e0be-deb0-4a6d7f90c4a4', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'import', 'Prospects', 'module', 90, 0),
('51a0a4a1-4ad1-6b9b-c191-4a6d7f4404b3', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'export', 'Prospects', 'module', 90, 0),
('57e7ae23-5fd3-14b8-ef9a-4a6d7f0e1095', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'access', 'Documents', 'module', 89, 0),
('5852acd1-fd27-2946-6c74-4a6d7fbf4355', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'view', 'Documents', 'module', 90, 0),
('58bb545f-95b5-7cb0-c8a1-4a6d7f8c0e18', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'list', 'Documents', 'module', 90, 0),
('5925119d-b322-17df-9723-4a6d7f3bfbf7', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'edit', 'Documents', 'module', 90, 0),
('598e934e-2ae5-67c1-7a8b-4a6d7f9f030e', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'delete', 'Documents', 'module', 90, 0),
('59f98dde-41c1-dac2-5d2a-4a6d7f803fe1', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'import', 'Documents', 'module', 90, 0),
('5a63252e-4d25-897f-567b-4a6d7f5022af', '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'export', 'Documents', 'module', 90, 0);

-- --------------------------------------------------------

--
-- Table structure for table `acl_roles`
--

CREATE TABLE IF NOT EXISTS `acl_roles` (
  `id` char(36) NOT NULL,
  `date_entered` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  `modified_user_id` char(36) NOT NULL,
  `created_by` char(36) default NULL,
  `name` varchar(150) default NULL,
  `description` text,
  `deleted` tinyint(1) default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_aclrole_id_del` (`id`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `acl_roles`
--


-- --------------------------------------------------------

--
-- Table structure for table `acl_roles_actions`
--

CREATE TABLE IF NOT EXISTS `acl_roles_actions` (
  `id` varchar(36) NOT NULL,
  `role_id` varchar(36) default NULL,
  `action_id` varchar(36) default NULL,
  `access_override` int(3) default NULL,
  `date_modified` datetime default NULL,
  `deleted` tinyint(1) default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_acl_role_id` (`role_id`),
  KEY `idx_acl_action_id` (`action_id`),
  KEY `idx_aclrole_action` (`role_id`,`action_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `acl_roles_actions`
--


-- --------------------------------------------------------

--
-- Table structure for table `acl_roles_users`
--

CREATE TABLE IF NOT EXISTS `acl_roles_users` (
  `id` varchar(36) NOT NULL,
  `role_id` varchar(36) default NULL,
  `user_id` varchar(36) default NULL,
  `date_modified` datetime default NULL,
  `deleted` tinyint(1) default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_aclrole_id` (`role_id`),
  KEY `idx_acluser_id` (`user_id`),
  KEY `idx_aclrole_user` (`role_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `acl_roles_users`
--


-- --------------------------------------------------------

--
-- Table structure for table `address_book`
--

CREATE TABLE IF NOT EXISTS `address_book` (
  `assigned_user_id` char(36) NOT NULL,
  `bean` varchar(50) NOT NULL,
  `bean_id` char(36) NOT NULL,
  KEY `ab_user_bean_idx` (`assigned_user_id`,`bean`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `address_book`
--


-- --------------------------------------------------------

--
-- Table structure for table `bugs`
--

CREATE TABLE IF NOT EXISTS `bugs` (
  `id` char(36) NOT NULL,
  `name` varchar(255) default NULL,
  `date_entered` datetime default NULL,
  `date_modified` datetime default NULL,
  `modified_user_id` char(36) default NULL,
  `created_by` char(36) default NULL,
  `description` text,
  `deleted` tinyint(1) default '0',
  `assigned_user_id` char(36) default NULL,
  `bug_number` int(11) NOT NULL auto_increment,
  `type` varchar(255) default NULL,
  `status` varchar(25) default NULL,
  `priority` varchar(25) default NULL,
  `resolution` varchar(255) default NULL,
  `work_log` text,
  `found_in_release` varchar(255) default NULL,
  `fixed_in_release` varchar(255) default NULL,
  `source` varchar(255) default NULL,
  `product_category` varchar(255) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `bugsnumk` (`bug_number`),
  KEY `bug_number` (`bug_number`),
  KEY `idx_bug_name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `bugs`
--


-- --------------------------------------------------------

--
-- Table structure for table `bugs_audit`
--

CREATE TABLE IF NOT EXISTS `bugs_audit` (
  `id` char(36) NOT NULL,
  `parent_id` char(36) NOT NULL,
  `date_created` datetime default NULL,
  `created_by` varchar(36) default NULL,
  `field_name` varchar(100) default NULL,
  `data_type` varchar(100) default NULL,
  `before_value_string` varchar(255) default NULL,
  `after_value_string` varchar(255) default NULL,
  `before_value_text` text,
  `after_value_text` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `bugs_audit`
--


-- --------------------------------------------------------

--
-- Table structure for table `calls`
--

CREATE TABLE IF NOT EXISTS `calls` (
  `id` char(36) NOT NULL,
  `name` varchar(50) default NULL,
  `date_entered` datetime default NULL,
  `date_modified` datetime default NULL,
  `modified_user_id` char(36) default NULL,
  `created_by` char(36) default NULL,
  `description` text,
  `deleted` tinyint(1) default '0',
  `assigned_user_id` char(36) default NULL,
  `duration_hours` int(2) default NULL,
  `duration_minutes` int(2) default NULL,
  `date_start` datetime default NULL,
  `date_end` date default NULL,
  `parent_type` varchar(25) default NULL,
  `status` varchar(25) default NULL,
  `direction` varchar(25) default NULL,
  `parent_id` char(36) default NULL,
  `reminder_time` int(4) default '-1',
  `outlook_id` varchar(255) default NULL,
  PRIMARY KEY  (`id`),
  KEY `idx_call_name` (`name`),
  KEY `idx_status` (`status`),
  KEY `idx_calls_date_start` (`date_start`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `calls`
--


-- --------------------------------------------------------

--
-- Table structure for table `calls_contacts`
--

CREATE TABLE IF NOT EXISTS `calls_contacts` (
  `id` varchar(36) NOT NULL,
  `call_id` varchar(36) default NULL,
  `contact_id` varchar(36) default NULL,
  `required` varchar(1) default '1',
  `accept_status` varchar(25) default 'none',
  `date_modified` datetime default NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_con_call_call` (`call_id`),
  KEY `idx_con_call_con` (`contact_id`),
  KEY `idx_call_contact` (`call_id`,`contact_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `calls_contacts`
--


-- --------------------------------------------------------

--
-- Table structure for table `calls_leads`
--

CREATE TABLE IF NOT EXISTS `calls_leads` (
  `id` varchar(36) NOT NULL,
  `call_id` varchar(36) default NULL,
  `lead_id` varchar(36) default NULL,
  `required` varchar(1) default '1',
  `accept_status` varchar(25) default 'none',
  `date_modified` datetime default NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_lead_call_call` (`call_id`),
  KEY `idx_lead_call_lead` (`lead_id`),
  KEY `idx_call_lead` (`call_id`,`lead_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `calls_leads`
--


-- --------------------------------------------------------

--
-- Table structure for table `calls_users`
--

CREATE TABLE IF NOT EXISTS `calls_users` (
  `id` varchar(36) NOT NULL,
  `call_id` varchar(36) default NULL,
  `user_id` varchar(36) default NULL,
  `required` varchar(1) default '1',
  `accept_status` varchar(25) default 'none',
  `date_modified` datetime default NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_usr_call_call` (`call_id`),
  KEY `idx_usr_call_usr` (`user_id`),
  KEY `idx_call_users` (`call_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `calls_users`
--


-- --------------------------------------------------------

--
-- Table structure for table `campaigns`
--

CREATE TABLE IF NOT EXISTS `campaigns` (
  `id` char(36) NOT NULL,
  `name` varchar(50) default NULL,
  `date_entered` datetime default NULL,
  `date_modified` datetime default NULL,
  `modified_user_id` char(36) default NULL,
  `created_by` char(36) default NULL,
  `deleted` tinyint(1) default '0',
  `assigned_user_id` char(36) default NULL,
  `tracker_key` int(11) NOT NULL auto_increment,
  `tracker_count` int(11) default '0',
  `refer_url` varchar(255) default 'http://',
  `tracker_text` varchar(255) default NULL,
  `start_date` date default NULL,
  `end_date` date default NULL,
  `status` varchar(25) default NULL,
  `impressions` int(11) default '0',
  `currency_id` char(36) default NULL,
  `budget` double default NULL,
  `expected_cost` double default NULL,
  `actual_cost` double default NULL,
  `expected_revenue` double default NULL,
  `campaign_type` varchar(25) default NULL,
  `objective` text,
  `content` text,
  `frequency` varchar(25) default NULL,
  PRIMARY KEY  (`id`),
  KEY `camp_auto_tracker_key` (`tracker_key`),
  KEY `idx_campaign_name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `campaigns`
--


-- --------------------------------------------------------

--
-- Table structure for table `campaigns_audit`
--

CREATE TABLE IF NOT EXISTS `campaigns_audit` (
  `id` char(36) NOT NULL,
  `parent_id` char(36) NOT NULL,
  `date_created` datetime default NULL,
  `created_by` varchar(36) default NULL,
  `field_name` varchar(100) default NULL,
  `data_type` varchar(100) default NULL,
  `before_value_string` varchar(255) default NULL,
  `after_value_string` varchar(255) default NULL,
  `before_value_text` text,
  `after_value_text` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `campaigns_audit`
--


-- --------------------------------------------------------

--
-- Table structure for table `campaign_log`
--

CREATE TABLE IF NOT EXISTS `campaign_log` (
  `id` char(36) NOT NULL,
  `campaign_id` char(36) default NULL,
  `target_tracker_key` varchar(36) default NULL,
  `target_id` varchar(36) default NULL,
  `target_type` varchar(25) default NULL,
  `activity_type` varchar(25) default NULL,
  `activity_date` datetime default NULL,
  `related_id` varchar(36) default NULL,
  `related_type` varchar(25) default NULL,
  `archived` tinyint(1) default '0',
  `hits` int(11) default '0',
  `list_id` char(36) default NULL,
  `deleted` tinyint(1) default '0',
  `date_modified` datetime default NULL,
  `more_information` varchar(100) default NULL,
  `marketing_id` char(36) default NULL,
  PRIMARY KEY  (`id`),
  KEY `idx_camp_tracker` (`target_tracker_key`),
  KEY `idx_camp_campaign_id` (`campaign_id`),
  KEY `idx_camp_more_info` (`more_information`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `campaign_log`
--


-- --------------------------------------------------------

--
-- Table structure for table `campaign_trkrs`
--

CREATE TABLE IF NOT EXISTS `campaign_trkrs` (
  `id` char(36) NOT NULL,
  `tracker_name` varchar(30) default NULL,
  `tracker_url` varchar(255) default 'http://',
  `tracker_key` int(11) NOT NULL auto_increment,
  `campaign_id` char(36) default NULL,
  `date_entered` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  `modified_user_id` char(36) default NULL,
  `created_by` char(36) default NULL,
  `is_optout` tinyint(1) NOT NULL default '0',
  `deleted` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `campaign_tracker_key_idx` (`tracker_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `campaign_trkrs`
--


-- --------------------------------------------------------

--
-- Table structure for table `cases`
--

CREATE TABLE IF NOT EXISTS `cases` (
  `id` char(36) NOT NULL,
  `name` varchar(255) default NULL,
  `date_entered` datetime default NULL,
  `date_modified` datetime default NULL,
  `modified_user_id` char(36) default NULL,
  `created_by` char(36) default NULL,
  `description` text,
  `deleted` tinyint(1) default '0',
  `assigned_user_id` char(36) default NULL,
  `case_number` int(11) NOT NULL auto_increment,
  `type` varchar(255) default NULL,
  `status` varchar(25) default NULL,
  `priority` varchar(25) default NULL,
  `resolution` text,
  `work_log` text,
  `account_id` char(36) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `casesnumk` (`case_number`),
  KEY `case_number` (`case_number`),
  KEY `idx_case_name` (`name`),
  KEY `idx_account_id` (`account_id`),
  KEY `idx_cases_stat_del` (`assigned_user_id`,`status`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `cases`
--


-- --------------------------------------------------------

--
-- Table structure for table `cases_audit`
--

CREATE TABLE IF NOT EXISTS `cases_audit` (
  `id` char(36) NOT NULL,
  `parent_id` char(36) NOT NULL,
  `date_created` datetime default NULL,
  `created_by` varchar(36) default NULL,
  `field_name` varchar(100) default NULL,
  `data_type` varchar(100) default NULL,
  `before_value_string` varchar(255) default NULL,
  `after_value_string` varchar(255) default NULL,
  `before_value_text` text,
  `after_value_text` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `cases_audit`
--


-- --------------------------------------------------------

--
-- Table structure for table `cases_bugs`
--

CREATE TABLE IF NOT EXISTS `cases_bugs` (
  `id` varchar(36) NOT NULL,
  `case_id` varchar(36) default NULL,
  `bug_id` varchar(36) default NULL,
  `date_modified` datetime default NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_cas_bug_cas` (`case_id`),
  KEY `idx_cas_bug_bug` (`bug_id`),
  KEY `idx_case_bug` (`case_id`,`bug_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `cases_bugs`
--


-- --------------------------------------------------------

--
-- Table structure for table `config`
--

CREATE TABLE IF NOT EXISTS `config` (
  `category` varchar(32) default NULL,
  `name` varchar(32) default NULL,
  `value` text,
  KEY `idx_config_cat` (`category`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `config`
--

INSERT INTO `config` (`category`, `name`, `value`) VALUES
('notify', 'fromaddress', 'do_not_reply@example.com'),
('notify', 'fromname', 'SugarCRM'),
('notify', 'send_by_default', '1'),
('notify', 'on', '0'),
('notify', 'send_from_assigning_user', '0'),
('info', 'sugar_version', '5.2.0'),
('MySettings', 'tab', ''),
('portal', 'on', '0'),
('Update', 'CheckUpdates', 'automatic'),
('system', 'name', 'SugarCRM'),
('license', 'msg_admin', ''),
('license', 'msg_all', ''),
('license', 'last_validation', 'success'),
('license', 'latest_versions', 'YToxOntpOjA7YToyOntzOjc6InZlcnNpb24iO3M6NjoiNS4yLjBmIjtzOjExOiJkZXNjcmlwdGlvbiI7czoxNjM6IlRoZSBsYXRlc3QgdmVyc2lvbiBvZiBTdWdhckNSTSBpcyA1LjIuMGYuICBQbGVhc2UgdmlzaXQgPGEgaHJlZj0iaHR0cDovL3N1cHBvcnQuc3VnYXJjcm0uY29tIiB0YXJnZXQ9Il9uZXciPnN1cHBvcnQuc3VnYXJjcm0uY29tPC9hPiB0byBhY3F1aXJlIHRoZSBsYXRlc3QgdmVyc2lvbi4iO319'),
('Update', 'last_check_date', '1248690203');

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE IF NOT EXISTS `contacts` (
  `id` char(36) NOT NULL,
  `date_entered` datetime default NULL,
  `date_modified` datetime default NULL,
  `modified_user_id` char(36) default NULL,
  `created_by` char(36) default NULL,
  `description` text,
  `deleted` tinyint(1) default '0',
  `assigned_user_id` char(36) default NULL,
  `salutation` varchar(5) default NULL,
  `first_name` varchar(100) default NULL,
  `last_name` varchar(100) default NULL,
  `title` varchar(100) default NULL,
  `department` varchar(255) default NULL,
  `do_not_call` tinyint(1) default '0',
  `phone_home` varchar(25) default NULL,
  `phone_mobile` varchar(25) default NULL,
  `phone_work` varchar(25) default NULL,
  `phone_other` varchar(25) default NULL,
  `phone_fax` varchar(25) default NULL,
  `primary_address_street` varchar(150) default NULL,
  `primary_address_city` varchar(100) default NULL,
  `primary_address_state` varchar(100) default NULL,
  `primary_address_postalcode` varchar(20) default NULL,
  `primary_address_country` varchar(255) default NULL,
  `alt_address_street` varchar(150) default NULL,
  `alt_address_city` varchar(100) default NULL,
  `alt_address_state` varchar(100) default NULL,
  `alt_address_postalcode` varchar(20) default NULL,
  `alt_address_country` varchar(255) default NULL,
  `assistant` varchar(75) default NULL,
  `assistant_phone` varchar(25) default NULL,
  `lead_source` varchar(100) default NULL,
  `reports_to_id` char(36) default NULL,
  `birthdate` date default NULL,
  `portal_name` varchar(255) default NULL,
  `portal_active` tinyint(1) NOT NULL default '0',
  `portal_app` varchar(255) default NULL,
  `campaign_id` char(36) default NULL,
  PRIMARY KEY  (`id`),
  KEY `idx_cont_last_first` (`last_name`,`first_name`,`deleted`),
  KEY `idx_contacts_del_last` (`deleted`,`last_name`),
  KEY `idx_cont_del_reports` (`deleted`,`reports_to_id`,`last_name`),
  KEY `idx_reports_to_id` (`reports_to_id`),
  KEY `idx_del_id_user` (`deleted`,`id`,`assigned_user_id`),
  KEY `idx_cont_assigned` (`assigned_user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `contacts`
--


-- --------------------------------------------------------

--
-- Table structure for table `contacts_audit`
--

CREATE TABLE IF NOT EXISTS `contacts_audit` (
  `id` char(36) NOT NULL,
  `parent_id` char(36) NOT NULL,
  `date_created` datetime default NULL,
  `created_by` varchar(36) default NULL,
  `field_name` varchar(100) default NULL,
  `data_type` varchar(100) default NULL,
  `before_value_string` varchar(255) default NULL,
  `after_value_string` varchar(255) default NULL,
  `before_value_text` text,
  `after_value_text` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `contacts_audit`
--


-- --------------------------------------------------------

--
-- Table structure for table `contacts_bugs`
--

CREATE TABLE IF NOT EXISTS `contacts_bugs` (
  `id` varchar(36) NOT NULL,
  `contact_id` varchar(36) default NULL,
  `bug_id` varchar(36) default NULL,
  `contact_role` varchar(50) default NULL,
  `date_modified` datetime default NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_con_bug_con` (`contact_id`),
  KEY `idx_con_bug_bug` (`bug_id`),
  KEY `idx_contact_bug` (`contact_id`,`bug_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `contacts_bugs`
--


-- --------------------------------------------------------

--
-- Table structure for table `contacts_cases`
--

CREATE TABLE IF NOT EXISTS `contacts_cases` (
  `id` varchar(36) NOT NULL,
  `contact_id` varchar(36) default NULL,
  `case_id` varchar(36) default NULL,
  `contact_role` varchar(50) default NULL,
  `date_modified` datetime default NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_con_case_con` (`contact_id`),
  KEY `idx_con_case_case` (`case_id`),
  KEY `idx_contacts_cases` (`contact_id`,`case_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `contacts_cases`
--


-- --------------------------------------------------------

--
-- Table structure for table `contacts_users`
--

CREATE TABLE IF NOT EXISTS `contacts_users` (
  `id` varchar(36) NOT NULL,
  `contact_id` varchar(36) default NULL,
  `user_id` varchar(36) default NULL,
  `date_modified` datetime default NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_con_users_con` (`contact_id`),
  KEY `idx_con_users_user` (`user_id`),
  KEY `idx_contacts_users` (`contact_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `contacts_users`
--


-- --------------------------------------------------------

--
-- Table structure for table `currencies`
--

CREATE TABLE IF NOT EXISTS `currencies` (
  `id` char(36) NOT NULL,
  `name` varchar(36) NOT NULL,
  `symbol` varchar(36) NOT NULL,
  `iso4217` varchar(3) NOT NULL,
  `conversion_rate` double NOT NULL default '0',
  `status` varchar(25) default NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  `date_entered` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  `created_by` char(36) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `idx_currency_name` (`name`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `currencies`
--


-- --------------------------------------------------------

--
-- Table structure for table `custom_fields`
--

CREATE TABLE IF NOT EXISTS `custom_fields` (
  `bean_id` varchar(36) default NULL,
  `set_num` int(11) default '0',
  `field0` varchar(255) default NULL,
  `field1` varchar(255) default NULL,
  `field2` varchar(255) default NULL,
  `field3` varchar(255) default NULL,
  `field4` varchar(255) default NULL,
  `field5` varchar(255) default NULL,
  `field6` varchar(255) default NULL,
  `field7` varchar(255) default NULL,
  `field8` varchar(255) default NULL,
  `field9` varchar(255) default NULL,
  `deleted` tinyint(1) default '0',
  KEY `idx_beanid_set_num` (`bean_id`,`set_num`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `custom_fields`
--


-- --------------------------------------------------------

--
-- Table structure for table `dashboards`
--

CREATE TABLE IF NOT EXISTS `dashboards` (
  `id` char(36) NOT NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  `date_entered` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  `modified_user_id` char(36) NOT NULL,
  `assigned_user_id` char(36) default NULL,
  `created_by` char(36) default NULL,
  `name` varchar(100) default NULL,
  `description` text,
  `content` text,
  PRIMARY KEY  (`id`),
  KEY `idx_dashboard_name` (`name`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `dashboards`
--


-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE IF NOT EXISTS `documents` (
  `id` char(36) NOT NULL,
  `date_entered` datetime default NULL,
  `date_modified` datetime default NULL,
  `modified_user_id` char(36) default NULL,
  `created_by` char(36) default NULL,
  `description` text,
  `deleted` tinyint(1) default '0',
  `document_name` varchar(255) NOT NULL,
  `active_date` date default NULL,
  `exp_date` date default NULL,
  `category_id` varchar(25) default NULL,
  `subcategory_id` varchar(25) default NULL,
  `status_id` varchar(25) default NULL,
  `document_revision_id` varchar(36) default NULL,
  `related_doc_id` char(36) default NULL,
  `related_doc_rev_id` char(36) default NULL,
  `is_template` tinyint(1) default '0',
  `template_type` varchar(25) default NULL,
  PRIMARY KEY  (`id`),
  KEY `idx_doc_cat` (`category_id`,`subcategory_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `documents`
--


-- --------------------------------------------------------

--
-- Table structure for table `document_revisions`
--

CREATE TABLE IF NOT EXISTS `document_revisions` (
  `id` varchar(36) NOT NULL,
  `change_log` varchar(255) default NULL,
  `document_id` varchar(36) default NULL,
  `date_entered` datetime default NULL,
  `created_by` char(36) default NULL,
  `filename` varchar(255) NOT NULL,
  `file_ext` varchar(25) default NULL,
  `file_mime_type` varchar(100) default NULL,
  `revision` varchar(25) default NULL,
  `deleted` tinyint(1) default '0',
  `date_modified` datetime default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `document_revisions`
--


-- --------------------------------------------------------

--
-- Table structure for table `emailman`
--

CREATE TABLE IF NOT EXISTS `emailman` (
  `date_entered` datetime default NULL,
  `date_modified` datetime default NULL,
  `user_id` char(36) default NULL,
  `id` int(11) NOT NULL auto_increment,
  `campaign_id` char(36) default NULL,
  `marketing_id` char(36) default NULL,
  `list_id` char(36) default NULL,
  `send_date_time` datetime default NULL,
  `modified_user_id` char(36) default NULL,
  `in_queue` tinyint(1) default '0',
  `in_queue_date` datetime default NULL,
  `send_attempts` int(11) default '0',
  `deleted` tinyint(1) default '0',
  `related_id` char(36) default NULL,
  `related_type` varchar(100) default NULL,
  PRIMARY KEY  (`id`),
  KEY `idx_eman_list` (`list_id`,`user_id`,`deleted`),
  KEY `idx_eman_campaign_id` (`campaign_id`),
  KEY `idx_eman_relid_reltype_id` (`related_id`,`related_type`,`campaign_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `emailman`
--


-- --------------------------------------------------------

--
-- Table structure for table `emails`
--

CREATE TABLE IF NOT EXISTS `emails` (
  `id` char(36) NOT NULL,
  `date_entered` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  `assigned_user_id` char(36) default NULL,
  `modified_user_id` char(36) default NULL,
  `created_by` char(36) default NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  `date_sent` datetime default NULL,
  `message_id` varchar(255) default NULL,
  `name` varchar(255) default NULL,
  `type` varchar(25) default NULL,
  `status` varchar(25) default NULL,
  `flagged` tinyint(1) default '0',
  `reply_to_status` tinyint(1) default '0',
  `intent` varchar(25) default 'pick',
  `mailbox_id` char(36) default NULL,
  `parent_type` varchar(25) default NULL,
  `parent_id` char(36) default NULL,
  PRIMARY KEY  (`id`),
  KEY `idx_email_name` (`name`),
  KEY `idx_message_id` (`message_id`),
  KEY `idx_email_parent_id` (`parent_id`),
  KEY `idx_email_assigned` (`assigned_user_id`,`type`,`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `emails`
--


-- --------------------------------------------------------

--
-- Table structure for table `emails_beans`
--

CREATE TABLE IF NOT EXISTS `emails_beans` (
  `id` varchar(36) NOT NULL,
  `email_id` varchar(36) default NULL,
  `bean_id` varchar(36) default NULL,
  `bean_module` varchar(36) default NULL,
  `campaign_data` text,
  `date_modified` datetime default NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_emails_beans_bean_id` (`bean_id`),
  KEY `idx_emails_beans_email_bean` (`email_id`,`bean_id`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `emails_beans`
--


-- --------------------------------------------------------

--
-- Table structure for table `emails_email_addr_rel`
--

CREATE TABLE IF NOT EXISTS `emails_email_addr_rel` (
  `id` char(36) NOT NULL,
  `email_id` char(36) NOT NULL,
  `address_type` varchar(4) NOT NULL,
  `email_address_id` char(36) NOT NULL,
  `deleted` tinyint(1) default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_eearl_email_id` (`email_id`,`address_type`),
  KEY `idx_eearl_address_id` (`email_address_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `emails_email_addr_rel`
--


-- --------------------------------------------------------

--
-- Table structure for table `emails_text`
--

CREATE TABLE IF NOT EXISTS `emails_text` (
  `email_id` varchar(36) NOT NULL,
  `from_addr` varchar(255) default NULL,
  `reply_to_addr` varchar(255) default NULL,
  `to_addrs` text,
  `cc_addrs` text,
  `bcc_addrs` text,
  `description` longtext,
  `description_html` longtext,
  `raw_source` longtext,
  `deleted` tinyint(1) default '0',
  PRIMARY KEY  (`email_id`),
  KEY `emails_textfromaddr` (`from_addr`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `emails_text`
--


-- --------------------------------------------------------

--
-- Table structure for table `email_addresses`
--

CREATE TABLE IF NOT EXISTS `email_addresses` (
  `id` char(36) NOT NULL,
  `email_address` varchar(255) NOT NULL,
  `email_address_caps` varchar(255) NOT NULL,
  `invalid_email` tinyint(1) default '0',
  `opt_out` tinyint(1) default '0',
  `date_created` datetime default NULL,
  `date_modified` datetime default NULL,
  `deleted` tinyint(1) default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_ea_caps_opt_out_invalid` (`email_address_caps`,`opt_out`,`invalid_email`),
  KEY `idx_ea_opt_out_invalid` (`email_address`,`opt_out`,`invalid_email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `email_addresses`
--


-- --------------------------------------------------------

--
-- Table structure for table `email_addr_bean_rel`
--

CREATE TABLE IF NOT EXISTS `email_addr_bean_rel` (
  `id` char(36) NOT NULL,
  `email_address_id` char(36) NOT NULL,
  `bean_id` char(36) NOT NULL,
  `bean_module` varchar(25) NOT NULL,
  `primary_address` tinyint(1) default '0',
  `reply_to_address` tinyint(1) default '0',
  `date_created` datetime default NULL,
  `date_modified` datetime default NULL,
  `deleted` tinyint(1) default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_email_address_id` (`email_address_id`),
  KEY `idx_bean_id` (`bean_id`,`bean_module`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `email_addr_bean_rel`
--


-- --------------------------------------------------------

--
-- Table structure for table `email_cache`
--

CREATE TABLE IF NOT EXISTS `email_cache` (
  `ie_id` char(36) NOT NULL,
  `mbox` varchar(60) NOT NULL,
  `subject` varchar(255) default NULL,
  `fromaddr` varchar(100) default NULL,
  `toaddr` varchar(255) default NULL,
  `senddate` datetime default NULL,
  `message_id` varchar(255) default NULL,
  `mailsize` int(10) unsigned NOT NULL,
  `imap_uid` int(10) unsigned NOT NULL,
  `msgno` int(10) unsigned default NULL,
  `recent` tinyint(4) NOT NULL,
  `flagged` tinyint(4) NOT NULL,
  `answered` tinyint(4) NOT NULL,
  `deleted` tinyint(4) NOT NULL,
  `seen` tinyint(4) NOT NULL,
  `draft` tinyint(4) NOT NULL,
  KEY `idx_ie_id` (`ie_id`),
  KEY `idx_mail_date` (`ie_id`,`mbox`,`senddate`),
  KEY `idx_mail_from` (`ie_id`,`mbox`,`fromaddr`),
  KEY `idx_mail_subj` (`subject`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `email_cache`
--


-- --------------------------------------------------------

--
-- Table structure for table `email_marketing`
--

CREATE TABLE IF NOT EXISTS `email_marketing` (
  `id` char(36) NOT NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  `date_entered` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  `modified_user_id` char(36) default NULL,
  `created_by` char(36) default NULL,
  `name` varchar(255) default NULL,
  `from_name` varchar(100) default NULL,
  `from_addr` varchar(100) default NULL,
  `reply_to_name` varchar(100) default NULL,
  `reply_to_addr` varchar(100) default NULL,
  `inbound_email_id` varchar(36) default NULL,
  `date_start` datetime default NULL,
  `template_id` char(36) NOT NULL,
  `status` varchar(25) NOT NULL,
  `campaign_id` char(36) default NULL,
  `all_prospect_lists` tinyint(1) default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_emmkt_name` (`name`),
  KEY `idx_emmkit_del` (`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `email_marketing`
--


-- --------------------------------------------------------

--
-- Table structure for table `email_marketing_prospect_lists`
--

CREATE TABLE IF NOT EXISTS `email_marketing_prospect_lists` (
  `id` varchar(36) NOT NULL,
  `prospect_list_id` varchar(36) default NULL,
  `email_marketing_id` varchar(36) default NULL,
  `date_modified` datetime default NULL,
  `deleted` tinyint(1) default '0',
  PRIMARY KEY  (`id`),
  KEY `email_mp_prospects` (`email_marketing_id`,`prospect_list_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `email_marketing_prospect_lists`
--


-- --------------------------------------------------------

--
-- Table structure for table `email_templates`
--

CREATE TABLE IF NOT EXISTS `email_templates` (
  `id` char(36) NOT NULL,
  `date_entered` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  `modified_user_id` char(36) default NULL,
  `created_by` varchar(36) default NULL,
  `published` varchar(3) default NULL,
  `name` varchar(255) default NULL,
  `description` text,
  `subject` varchar(255) default NULL,
  `body` text,
  `body_html` text,
  `deleted` tinyint(1) NOT NULL default '0',
  `text_only` tinyint(1) default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_email_template_name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `email_templates`
--


-- --------------------------------------------------------

--
-- Table structure for table `feeds`
--

CREATE TABLE IF NOT EXISTS `feeds` (
  `id` char(36) NOT NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  `date_entered` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  `modified_user_id` char(36) NOT NULL,
  `assigned_user_id` char(36) default NULL,
  `created_by` char(36) default NULL,
  `title` varchar(100) default NULL,
  `description` text,
  `url` varchar(255) default NULL,
  PRIMARY KEY  (`id`),
  KEY `idx_feed_name` (`title`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `feeds`
--

INSERT INTO `feeds` (`id`, `deleted`, `date_entered`, `date_modified`, `modified_user_id`, `assigned_user_id`, `created_by`, `title`, `description`, `url`) VALUES
('e8bc00cd-cbb7-280c-0aa5-41e2df9c988d', 0, '2005-01-10 12:01:58', '2005-01-10 12:01:58', '1', '1', NULL, 'Linux Today', NULL, 'http://linuxtoday.com/backend/biglt.rss'),
('a93b3610-0f3c-a4cb-6985-41e2df65b1c8', 0, '2005-01-10 12:02:07', '2005-01-10 12:02:07', '1', '1', NULL, 'MacCentral News', NULL, 'http://maccentral.macworld.com/mnn.cgi'),
('dde361bb-7d4a-cc59-8cce-41e2dfcf3c1b', 0, '2005-01-10 12:02:21', '2005-01-10 12:02:21', '1', '1', NULL, 'MacMerc.com', NULL, 'http://macmerc.com/backend.php'),
('925ef6eb-7bbf-2766-cc7a-41e2df228578', 0, '2005-01-10 12:02:30', '2005-01-10 12:02:30', '1', '1', NULL, 'ABC News: Business', NULL, 'http://my.abcnews.go.com/rsspublic/business_rss20.xml'),
('dbab5f6c-d0da-4e67-478c-41e2df8b122c', 0, '2005-01-10 12:02:34', '2005-01-10 12:02:34', '1', '1', NULL, 'ABC News: Entertainment', NULL, 'http://my.abcnews.go.com/rsspublic/entertainment_rss20.xml'),
('7eb0e2e7-28c5-21cc-31f9-41e2dfbf3713', 0, '2005-01-10 12:02:39', '2005-01-10 12:02:39', '1', '1', NULL, 'ABC News: GMA', NULL, 'http://my.abcnews.go.com/rsspublic/gma_rss20.xml'),
('e8066ab5-3b23-fe0d-614e-41e2df558ec8', 0, '2005-01-10 12:02:43', '2005-01-10 12:02:43', '1', '1', NULL, 'ABC News: Health', NULL, 'http://my.abcnews.go.com/rsspublic/health_rss20.xml'),
('36b67446-880f-d6a5-e16e-41e2df3bd857', 0, '2005-01-10 12:02:48', '2005-01-10 12:02:48', '1', '1', NULL, 'ABC News: Nightline', NULL, 'http://my.abcnews.go.com/rsspublic/nightline_rss20.xml'),
('83235e59-0a98-1777-2a9c-41e2df9a09ce', 0, '2005-01-10 12:02:52', '2005-01-10 12:02:52', '1', '1', NULL, 'ABC News: Politics', NULL, 'http://my.abcnews.go.com/rsspublic/politics_rss20.xml'),
('c2e3db4d-c176-53bf-1aac-41e2df1a1450', 0, '2005-01-10 12:02:56', '2005-01-10 12:02:56', '1', '1', NULL, 'ABC News: Primetime', NULL, 'http://my.abcnews.go.com/rsspublic/primetime_rss20.xml'),
('4550e09a-b93e-092f-80ec-41e2dfc550f7', 0, '2005-01-10 12:03:01', '2005-01-10 12:03:01', '1', '1', NULL, 'ABC News: Technology', NULL, 'http://my.abcnews.go.com/rsspublic/scitech_rss20.xml'),
('a164cc3f-435e-0a59-3e8c-41e2dfebd62f', 0, '2005-01-10 12:03:05', '2005-01-10 12:03:05', '1', '1', NULL, 'ABC News: Travel', NULL, 'http://my.abcnews.go.com/rsspublic/travel_rss20.xml'),
('28572e36-b2fa-cd0e-fbec-41e2df1b84c0', 0, '2005-01-10 12:03:15', '2005-01-10 12:03:15', '1', '1', NULL, 'ABC News: ThisWeek', NULL, 'http://my.abcnews.go.com/rsspublic/tw_rss20.xml'),
('a50a67a3-73fd-c3b7-5ee6-41e2dffe5ec5', 0, '2005-01-10 12:03:19', '2005-01-10 12:03:19', '1', '1', NULL, 'ABC News: US', NULL, 'http://my.abcnews.go.com/rsspublic/us_rss20.xml'),
('ef110198-40fe-1799-363a-41e2df5c1007', 0, '2005-01-10 12:03:24', '2005-01-10 12:03:24', '1', '1', NULL, 'ABC News: WNT', NULL, 'http://my.abcnews.go.com/rsspublic/wnt_rss20.xml'),
('5b71af07-470d-95c3-8afd-41e2df76f015', 0, '2005-01-10 12:03:28', '2005-01-10 12:03:28', '1', '1', NULL, 'ABC News: International', NULL, 'http://my.abcnews.go.com/rsspublic/world_rss20.xml'),
('16c540cc-c02a-4311-d64c-41e2dfdbd596', 0, '2005-01-10 12:03:29', '2005-01-10 12:03:29', '1', '1', NULL, 'BBC News | Africa | World Edition', NULL, 'http://news.bbc.co.uk/rss/newsonline_world_edition/africa/rss091.xml'),
('bc2c7c1f-220d-400a-c383-41e2dfc79697', 0, '2005-01-10 12:03:29', '2005-01-10 12:03:29', '1', '1', NULL, 'BBC News | Americas | World Edition', NULL, 'http://news.bbc.co.uk/rss/newsonline_world_edition/americas/rss091.xml'),
('81bb8625-d0c5-07f1-04da-41e2df5fc5e2', 0, '2005-01-10 12:03:30', '2005-01-10 12:03:30', '1', '1', NULL, 'BBC News | Asia-Pacific | World Edition', NULL, 'http://news.bbc.co.uk/rss/newsonline_world_edition/asia-pacific/rss091.xml'),
('3a68ade5-eba9-dcf7-b8bc-41e2df75bea6', 0, '2005-01-10 12:03:31', '2005-01-10 12:03:31', '1', '1', NULL, 'BBC News | Business | World Edition', NULL, 'http://news.bbc.co.uk/rss/newsonline_world_edition/business/rss091.xml'),
('c5ae7e2a-c2c8-a261-8f4f-41e2dfad01f6', 0, '2005-01-10 12:03:31', '2005-01-10 12:03:31', '1', '1', NULL, 'BBC News | Entertainment | World Edition', NULL, 'http://news.bbc.co.uk/rss/newsonline_world_edition/entertainment/rss091.xml'),
('7da60a3e-c6b3-4d4a-0338-41e2df90838a', 0, '2005-01-10 12:03:32', '2005-01-10 12:03:32', '1', '1', NULL, 'BBC News | Europe | World Edition', NULL, 'http://news.bbc.co.uk/rss/newsonline_world_edition/europe/rss091.xml'),
('3191a790-8894-ea0a-fad6-41e2df8b7d62', 0, '2005-01-10 12:03:33', '2005-01-10 12:03:33', '1', '1', NULL, 'BBC News | News Front Page | World Edition', NULL, 'http://news.bbc.co.uk/rss/newsonline_world_edition/front_page/rss091.xml'),
('d0089e80-9da9-93d5-6999-41e2df85a074', 0, '2005-01-10 12:03:33', '2005-01-10 12:03:33', '1', '1', NULL, 'BBC News | Health | World Edition', NULL, 'http://news.bbc.co.uk/rss/newsonline_world_edition/health/rss091.xml'),
('9582dea7-c0dd-c5cb-07eb-41e2df38591c', 0, '2005-01-10 12:03:34', '2005-01-10 12:03:34', '1', '1', NULL, 'BBC News | Middle East | World Edition', NULL, 'http://news.bbc.co.uk/rss/newsonline_world_edition/middle_east/rss091.xml'),
('1c853682-6da0-1ebf-5a4b-41e2df0c6ae0', 0, '2005-01-10 12:03:35', '2005-01-10 12:03:35', '1', '1', NULL, 'BBC News | Programmes | World Edition', NULL, 'http://news.bbc.co.uk/rss/newsonline_world_edition/programmes/rss091.xml'),
('bd3d3b2a-3b2b-1b1c-b833-41e2dfed10b2', 0, '2005-01-10 12:03:35', '2005-01-10 12:03:35', '1', '1', NULL, 'BBC News | Science/Nature | World Edition', NULL, 'http://news.bbc.co.uk/rss/newsonline_world_edition/science/nature/rss091.xml'),
('77ea97e9-f224-ae60-e2f3-41e2dfa83f37', 0, '2005-01-10 12:03:36', '2005-01-10 12:03:36', '1', '1', NULL, 'BBC News | South Asia | World Edition', NULL, 'http://news.bbc.co.uk/rss/newsonline_world_edition/south_asia/rss091.xml'),
('15d960df-ef6b-bb2a-6bc7-41e2df301151', 0, '2005-01-10 12:03:37', '2005-01-10 12:03:37', '1', '1', NULL, 'BBC News | Have Your Say | World Edition', NULL, 'http://news.bbc.co.uk/rss/newsonline_world_edition/talking_point/rss091.xml'),
('a441427a-dded-20ec-14c0-41e2df00b0ca', 0, '2005-01-10 12:03:37', '2005-01-10 12:03:37', '1', '1', NULL, 'BBC News | Technology | World Edition', NULL, 'http://news.bbc.co.uk/rss/newsonline_world_edition/technology/rss091.xml'),
('4c8f056f-3eeb-7c81-89df-41e2dfdc1c14', 0, '2005-01-10 12:03:38', '2005-01-10 12:03:38', '1', '1', NULL, 'BBC News | UK News | Magazine | World Edition', NULL, 'http://news.bbc.co.uk/rss/newsonline_world_edition/uk_news/magazine/rss091.xml'),
('3a000f75-1443-8f26-018e-41e2df41bb7e', 0, '2005-01-10 12:03:39', '2005-01-10 12:03:39', '1', '1', NULL, 'BBC News | UK | World Edition', NULL, 'http://news.bbc.co.uk/rss/newsonline_world_edition/uk_news/rss091.xml'),
('b18a157a-9129-126a-3ae6-41e2df5bf062', 0, '2005-01-10 12:03:39', '2005-01-10 12:03:39', '1', '1', NULL, 'BBC News | Week at a Glance | World Edition', NULL, 'http://news.bbc.co.uk/rss/newsonline_world_edition/week_at-a-glance/rss091.xml'),
('5a354efc-6fd6-e3a9-a39d-41e2dfed604c', 0, '2005-01-10 12:03:45', '2005-01-10 12:03:45', '1', '1', NULL, 'Scotsman.com News - Alcohol & binge drinking', NULL, 'http://news.scotsman.com/topics.cfm?tid=585&format=rss'),
('52a9de11-f2fe-5a6d-539c-41e2dff841e5', 0, '2005-01-10 12:03:46', '2005-01-10 12:03:46', '1', '1', NULL, 'CNET News.com - Personal Technology', NULL, 'http://rss.com.com/2547-1040-0-5.xml'),
('e4a450fe-6602-2a1e-3ef2-41e2df356543', 0, '2005-01-10 12:03:46', '2005-01-10 12:03:46', '1', '1', NULL, 'Yahoo! News: Technology - Apple/Macintosh', NULL, 'http://rss.news.yahoo.com/rss/applecomputer'),
('573d09a7-7e57-b158-56bf-41e2df105ef4', 0, '2005-01-10 12:03:47', '2005-01-10 12:03:47', '1', '1', NULL, 'Yahoo! News: Entertainment - Arts and Stage', NULL, 'http://rss.news.yahoo.com/rss/arts'),
('a52e6677-1fa7-85fa-e4c6-41e2df9fbcca', 0, '2005-01-10 12:03:47', '2005-01-10 12:03:47', '1', '1', NULL, 'Yahoo! News: Business', NULL, 'http://rss.news.yahoo.com/rss/business'),
('a66852ab-d691-b6be-a3e0-41e2dfdb2fd7', 0, '2005-01-10 12:03:48', '2005-01-10 12:03:48', '1', '1', NULL, 'Yahoo! News: Cellular Phones', NULL, 'http://rss.news.yahoo.com/rss/cellphones'),
('211ea34b-c571-6bbc-22b0-41e2df130f37', 0, '2005-01-10 12:03:49', '2005-01-10 12:03:49', '1', '1', NULL, 'Yahoo! News: Entertainment - Dear Abby', NULL, 'http://rss.news.yahoo.com/rss/dearabby'),
('8d740c93-ee6b-36d6-a86d-41e2df99cf90', 0, '2005-01-10 12:03:49', '2005-01-10 12:03:49', '1', '1', NULL, 'Yahoo! News: Digital Photography', NULL, 'http://rss.news.yahoo.com/rss/digitalimaging'),
('7bc75358-93c7-5aeb-88f6-41e2df228bca', 0, '2005-01-10 12:03:50', '2005-01-10 12:03:50', '1', '1', NULL, 'Yahoo! News: Digital Music', NULL, 'http://rss.news.yahoo.com/rss/digitalmusic'),
('4537c91f-a52d-a98d-448a-41e2df099679', 0, '2005-01-10 12:03:51', '2005-01-10 12:03:51', '1', '1', NULL, 'Yahoo! News: Digital Video/TV Technology', NULL, 'http://rss.news.yahoo.com/rss/digitalvideo'),
('152a8211-c6a2-13b7-e8b6-41e2dfbb8c86', 0, '2005-01-10 12:03:52', '2005-01-10 12:03:52', '1', '1', NULL, 'Yahoo! News: Business - Earnings', NULL, 'http://rss.news.yahoo.com/rss/earnings'),
('a6050911-0292-5625-9ce2-41e2dfebccef', 0, '2005-01-10 12:03:53', '2005-01-10 12:03:53', '1', '1', NULL, 'Yahoo! News: Business - Economy', NULL, 'http://rss.news.yahoo.com/rss/economy'),
('a8ad7517-8eaa-08ea-f162-41e2dfd39386', 0, '2005-01-10 12:03:53', '2005-01-10 12:03:53', '1', '1', NULL, 'Yahoo! News: Presidential Election', NULL, 'http://rss.news.yahoo.com/rss/elections'),
('a3240865-6431-2f39-2057-41e2dfa726a0', 0, '2005-01-10 12:03:54', '2005-01-10 12:03:54', '1', '1', NULL, 'Yahoo! News: Entertainment - Industry', NULL, 'http://rss.news.yahoo.com/rss/enindustry'),
('4b590300-8679-61c0-cc2f-41e2dfddd876', 0, '2005-01-10 12:03:55', '2005-01-10 12:03:55', '1', '1', NULL, 'Yahoo! News: Technology - Enterprise', NULL, 'http://rss.news.yahoo.com/rss/enterprise'),
('a9cf9f87-69b2-717a-a8d2-41e2dffffa44', 0, '2005-01-10 12:03:55', '2005-01-10 12:03:55', '1', '1', NULL, 'Yahoo! News: Entertainment', NULL, 'http://rss.news.yahoo.com/rss/entertainment'),
('c48dea5a-b340-21da-b590-41e2dfadee26', 0, '2005-01-10 12:03:56', '2005-01-10 12:03:56', '1', '1', NULL, 'Yahoo! News: Business - European Economy', NULL, 'http://rss.news.yahoo.com/rss/eurobiz'),
('4d575816-f907-80dd-5fe5-41e2dff30297', 0, '2005-01-10 12:03:57', '2005-01-10 12:03:57', '1', '1', NULL, 'Yahoo! News: Fashion', NULL, 'http://rss.news.yahoo.com/rss/fashion'),
('9d01947b-4fa4-530b-ae22-41e2df953c95', 0, '2005-01-10 12:03:57', '2005-01-10 12:03:57', '1', '1', NULL, 'Yahoo! News: Health', NULL, 'http://rss.news.yahoo.com/rss/health'),
('186244ef-6a28-e5d3-f85f-41e2df10fc8e', 0, '2005-01-10 12:03:58', '2005-01-10 12:03:58', '1', '1', NULL, 'Yahoo! News: Reader Ratings', NULL, 'http://rss.news.yahoo.com/rss/highestrated'),
('d0c4a1f1-4235-1e44-137b-41e2dfd0f653', 0, '2005-01-10 12:03:58', '2005-01-10 12:03:58', '1', '1', NULL, 'Yahoo! News: Technology - Internet', NULL, 'http://rss.news.yahoo.com/rss/internet'),
('4ae094bf-947f-304a-4659-41e2dfa5d909', 0, '2005-01-10 12:04:00', '2005-01-10 12:04:00', '1', '1', NULL, 'Yahoo! News: Iraq', NULL, 'http://rss.news.yahoo.com/rss/iraq'),
('e7f2de1f-52ee-a6d6-4df2-41e2df2a1d06', 0, '2005-01-10 12:04:00', '2005-01-10 12:04:00', '1', '1', NULL, 'Yahoo! News: Linux/Open Source', NULL, 'http://rss.news.yahoo.com/rss/linux'),
('942cac2d-f97a-91b9-4073-41e2df6c80d5', 0, '2005-01-10 12:04:02', '2005-01-10 12:04:02', '1', '1', NULL, 'Yahoo! News: Microsoft', NULL, 'http://rss.news.yahoo.com/rss/microsoft'),
('bf300aab-0041-415e-788e-41e2df06cba4', 0, '2005-01-10 12:04:03', '2005-01-10 12:04:03', '1', '1', NULL, 'Yahoo! News: Most Emailed', NULL, 'http://rss.news.yahoo.com/rss/mostemailed'),
('8368b72f-f4e5-c4c5-f288-41e2df962d3a', 0, '2005-01-10 12:04:03', '2005-01-10 12:04:03', '1', '1', NULL, 'Yahoo! News: Most Viewed', NULL, 'http://rss.news.yahoo.com/rss/mostviewed'),
('1c3c47e4-4be0-7987-4ea9-41e2df97209b', 0, '2005-01-10 12:04:05', '2005-01-10 12:04:05', '1', '1', NULL, 'Yahoo! News: Entertainment - Movies', NULL, 'http://rss.news.yahoo.com/rss/movies'),
('6f988256-8ca1-1933-3049-41e2df3e60cc', 0, '2005-01-10 12:04:06', '2005-01-10 12:04:06', '1', '1', NULL, 'Yahoo! News: Entertainment - Music', NULL, 'http://rss.news.yahoo.com/rss/music'),
('ccd00fec-47d3-7624-d16e-41e2df044894', 0, '2005-01-10 12:04:07', '2005-01-10 12:04:07', '1', '1', NULL, 'Yahoo! News: Oddly Enough', NULL, 'http://rss.news.yahoo.com/rss/oddlyenough'),
('a4f2137a-b861-6975-c126-41e2dfb115db', 0, '2005-01-10 12:04:07', '2005-01-10 12:04:07', '1', '1', NULL, 'Yahoo! News: Op/Ed', NULL, 'http://rss.news.yahoo.com/rss/oped'),
('94f3d97c-9124-f5ca-4863-41e2dff1e0f1', 0, '2005-01-10 12:04:08', '2005-01-10 12:04:08', '1', '1', NULL, 'Yahoo! News: Business - Personal Finance', NULL, 'http://rss.news.yahoo.com/rss/personalfinance'),
('e67a951a-e02a-5e57-cbfe-41e2df1cb9eb', 0, '2005-01-10 12:04:10', '2005-01-10 12:04:10', '1', '1', NULL, 'Yahoo! News: Technology - Personal Technology', NULL, 'http://rss.news.yahoo.com/rss/personaltech'),
('53e42a4a-aad1-d0cc-00bc-41e2df76fe22', 0, '2005-01-10 12:04:11', '2005-01-10 12:04:11', '1', '1', NULL, 'Yahoo! News: Politics', NULL, 'http://rss.news.yahoo.com/rss/politics'),
('1c5392ff-668f-7a40-70b7-41e2dfca5009', 0, '2005-01-10 12:04:12', '2005-01-10 12:04:12', '1', '1', NULL, 'Yahoo! News: Entertainment - Reviews', NULL, 'http://rss.news.yahoo.com/rss/reviews'),
('a048463f-b2d8-a68b-e532-41e2df0a664a', 0, '2005-01-10 12:04:12', '2005-01-10 12:04:12', '1', '1', NULL, 'Yahoo! News: RSS & Blogging', NULL, 'http://rss.news.yahoo.com/rss/rssblog'),
('9aa02754-2c0e-e86b-9710-41e2df2e00b9', 0, '2005-01-10 12:04:13', '2005-01-10 12:04:13', '1', '1', NULL, 'Yahoo! News: Science', NULL, 'http://rss.news.yahoo.com/rss/science'),
('bf91dda9-fe5c-b0f9-dcdd-41e2dfa2fcc8', 0, '2005-01-10 12:04:14', '2005-01-10 12:04:14', '1', '1', NULL, 'Yahoo! News: Portals and Search Engines', NULL, 'http://rss.news.yahoo.com/rss/search'),
('a789b41c-d7d5-211c-5d76-41e2df3eb898', 0, '2005-01-10 12:04:15', '2005-01-10 12:04:15', '1', '1', NULL, 'Yahoo! News: Computer Security & Viruses', NULL, 'http://rss.news.yahoo.com/rss/security'),
('70f3c59d-f85f-66aa-a42f-41e2df696f49', 0, '2005-01-10 12:04:16', '2005-01-10 12:04:16', '1', '1', NULL, 'Yahoo! News: Semiconductor Industry & Servers', NULL, 'http://rss.news.yahoo.com/rss/semiconductor'),
('8b800cfb-41f6-5740-02bd-41e2df801fd9', 0, '2005-01-10 12:04:17', '2005-01-10 12:04:17', '1', '1', NULL, 'Yahoo! News: Technology - Software', NULL, 'http://rss.news.yahoo.com/rss/software'),
('b82277c3-a5bd-e2a2-313a-41e2dff0822e', 0, '2005-01-10 12:04:17', '2005-01-10 12:04:17', '1', '1', NULL, 'Yahoo! News: Technology - Spam', NULL, 'http://rss.news.yahoo.com/rss/spam'),
('26498c47-05ac-260e-414b-41e2df9ed30a', 0, '2005-01-10 12:04:18', '2005-01-10 12:04:18', '1', '1', NULL, 'Yahoo! News: Sports', NULL, 'http://rss.news.yahoo.com/rss/sports'),
('8d9cbe09-2776-3142-3c67-41e2dfc11a61', 0, '2005-01-10 12:04:18', '2005-01-10 12:04:18', '1', '1', NULL, 'Yahoo! News: Business - Stock Markets', NULL, 'http://rss.news.yahoo.com/rss/stocks'),
('df2f6c82-2eb8-80a4-6827-41e2df880575', 0, '2005-01-10 12:04:18', '2005-01-10 12:04:18', '1', '1', NULL, 'Yahoo! News: Technology', NULL, 'http://rss.news.yahoo.com/rss/tech'),
('5cf1cf76-42d6-61ba-3889-41e2df406926', 0, '2005-01-10 12:04:19', '2005-01-10 12:04:19', '1', '1', NULL, 'Yahoo! News: Top Stories', NULL, 'http://rss.news.yahoo.com/rss/topstories'),
('d9dcddc7-2d6b-df2f-7577-41e2df55a413', 0, '2005-01-10 12:04:20', '2005-01-10 12:04:20', '1', '1', NULL, 'Yahoo! News: Entertainment - Television', NULL, 'http://rss.news.yahoo.com/rss/tv'),
('561f4dd0-2359-391b-d126-41e2df4e5438', 0, '2005-01-10 12:04:21', '2005-01-10 12:04:21', '1', '1', NULL, 'Yahoo! News: U.S. National', NULL, 'http://rss.news.yahoo.com/rss/us'),
('18874c7d-9299-93cb-2ba2-41e2df1995a8', 0, '2005-01-10 12:04:22', '2005-01-10 12:04:22', '1', '1', NULL, 'Yahoo! News: Technology - Video Games', NULL, 'http://rss.news.yahoo.com/rss/videogames'),
('324f0fd4-0eb6-12e2-ecaf-41e2df28bcb9', 0, '2005-01-10 12:04:24', '2005-01-10 12:04:24', '1', '1', NULL, 'Yahoo! News: Wireless and Mobile Technology', NULL, 'http://rss.news.yahoo.com/rss/wireless'),
('6493d8af-027e-3cca-c2d6-41e2dfb9aa84', 0, '2005-01-10 12:04:24', '2005-01-10 12:04:24', '1', '1', NULL, 'Yahoo! News: World', NULL, 'http://rss.news.yahoo.com/rss/world'),
('eb881ccf-3494-a043-381a-41e2df52c73f', 0, '2005-01-10 12:04:29', '2005-01-10 12:04:29', '1', '1', NULL, 'PCWorld.com - Most Popular Downloads of the Week', NULL, 'http://rss.pcworld.com/rss/downloads.rss?period=week'),
('6bf251a7-1139-4c31-3d90-41e2df7ee6d3', 0, '2005-01-10 12:04:34', '2005-01-10 12:04:34', '1', '1', NULL, 'PCWorld.com - Latest News Stories', NULL, 'http://rss.pcworld.com/rss/latestnews.rss'),
('391c061c-5472-f753-ba89-41e2df7adaef', 0, '2005-01-10 12:04:39', '2005-01-10 12:04:39', '1', '1', NULL, 'eWEEK Database', NULL, 'http://rssnewsapps.ziffdavis.com/eweekdatabase.xml'),
('687e89bc-e055-7cc8-fee6-41e2df89541f', 0, '2005-01-10 12:04:44', '2005-01-10 12:04:44', '1', '1', NULL, 'eWEEK Developer', NULL, 'http://rssnewsapps.ziffdavis.com/eweekdeveloper.xml'),
('61ddc6c4-15c2-1e2e-b559-41e2df9ccbd9', 0, '2005-01-10 12:04:49', '2005-01-10 12:04:49', '1', '1', NULL, 'eWEEK Linux', NULL, 'http://rssnewsapps.ziffdavis.com/eweeklinux.xml'),
('388d5242-789e-015b-389e-41e2dfa00dc7', 0, '2005-01-10 12:04:54', '2005-01-10 12:04:54', '1', '1', NULL, 'eWEEK Web Services', NULL, 'http://rssnewsapps.ziffdavis.com/eweekwebservices.xml'),
('f01c7eac-7b4e-4742-2287-41e2df47daaf', 0, '2005-01-10 12:04:58', '2005-01-10 12:04:58', '1', '1', NULL, 'eWEEK Windows', NULL, 'http://rssnewsapps.ziffdavis.com/eweekwindows.xml'),
('cf40c1b2-e2fb-8641-2bb7-41e2dff46a8f', 0, '2005-01-10 12:05:03', '2005-01-10 12:05:03', '1', '1', NULL, 'eWEEK Technology News', NULL, 'http://rssnewsapps.ziffdavis.com/tech.xml'),
('65a0f073-85fe-9426-4a06-41e2dfe5a004', 0, '2005-01-10 12:05:04', '2005-01-10 12:05:04', '1', '1', NULL, 'Seattle Post-Intelligencer: Arts & Entertainment', NULL, 'http://seattlepi.nwsource.com/rss/ae.rss'),
('f00846ce-dd14-7fb7-9226-41e2df5041f0', 0, '2005-01-10 12:05:04', '2005-01-10 12:05:04', '1', '1', NULL, 'Seattle Post-Intelligencer: Books', NULL, 'http://seattlepi.nwsource.com/rss/books.rss'),
('64f66a69-0df0-3188-49bf-41e2dfa7fcf9', 0, '2005-01-10 12:05:05', '2005-01-10 12:05:05', '1', '1', NULL, 'Seattle Post-Intelligencer: Business News', NULL, 'http://seattlepi.nwsource.com/rss/business.rss'),
('bbd43fee-d439-694d-44e9-41e2df39ca30', 0, '2005-01-10 12:05:05', '2005-01-10 12:05:05', '1', '1', NULL, 'Seattle Post-Intelligencer: Classical Music', NULL, 'http://seattlepi.nwsource.com/rss/classical.rss'),
('2bf6471a-aca7-ad06-07a2-41e2df156469', 0, '2005-01-10 12:05:06', '2005-01-10 12:05:06', '1', '1', NULL, 'Seattle Post-Intelligencer: Cougar Football', NULL, 'http://seattlepi.nwsource.com/rss/cougars.rss'),
('813bddc2-bf31-dad5-1b09-41e2dff8520f', 0, '2005-01-10 12:05:06', '2005-01-10 12:05:06', '1', '1', NULL, 'Seattle Post-Intelligencer: Dining', NULL, 'http://seattlepi.nwsource.com/rss/dining.rss'),
('d15c347f-dcce-83f5-3dba-41e2df339a4f', 0, '2005-01-10 12:05:06', '2005-01-10 12:05:06', '1', '1', NULL, 'Seattle Post-Intelligencer: Food', NULL, 'http://seattlepi.nwsource.com/rss/food.rss'),
('439337e2-9bc7-465b-4f0e-41e2dfda55c8', 0, '2005-01-10 12:05:07', '2005-01-10 12:05:07', '1', '1', NULL, 'Seattle Post-Intelligencer: Gardening', NULL, 'http://seattlepi.nwsource.com/rss/gardening.rss'),
('8858e7a9-9e84-ee76-1486-41e2df13c205', 0, '2005-01-10 12:05:07', '2005-01-10 12:05:07', '1', '1', NULL, 'Seattle Post-Intelligencer: Health and Fitness', NULL, 'http://seattlepi.nwsource.com/rss/health.rss'),
('ca29844f-e128-1e28-a2fd-41e2dfeba1b6', 0, '2005-01-10 12:05:07', '2005-01-10 12:05:07', '1', '1', NULL, 'Seattle Post-Intelligencer: Husky Football', NULL, 'http://seattlepi.nwsource.com/rss/huskies.rss'),
('32892ebf-9522-7358-8e3b-41e2dff2b85c', 0, '2005-01-10 12:05:08', '2005-01-10 12:05:08', '1', '1', NULL, 'Seattle Post-Intelligencer: John Levesque', NULL, 'http://seattlepi.nwsource.com/rss/levesque.rss'),
('887bcac4-2f06-7430-894f-41e2df3ae78f', 0, '2005-01-10 12:05:08', '2005-01-10 12:05:08', '1', '1', NULL, 'Seattle Post-Intelligencer: Lifestyle', NULL, 'http://seattlepi.nwsource.com/rss/lifestyle.rss'),
('ead74e28-ee83-4f0d-c681-41e2df62d666', 0, '2005-01-10 12:05:08', '2005-01-10 12:05:08', '1', '1', NULL, 'Seattle Post-Intelligencer: Local News', NULL, 'http://seattlepi.nwsource.com/rss/local.rss'),
('5308fdd0-fe86-a7d4-6b56-41e2df0aee0f', 0, '2005-01-10 12:05:09', '2005-01-10 12:05:09', '1', '1', NULL, 'Seattle Post-Intelligencer: Mariners', NULL, 'http://seattlepi.nwsource.com/rss/mariners.rss'),
('e6ca8ca3-5734-d264-de97-41e2df6ae7da', 0, '2005-01-10 12:05:09', '2005-01-10 12:05:09', '1', '1', NULL, 'Seattle Post-Intelligencer: Jim Moore', NULL, 'http://seattlepi.nwsource.com/rss/moore.rss'),
('78a04e91-dd06-a9d5-33d8-41e2df6aa326', 0, '2005-01-10 12:05:10', '2005-01-10 12:05:10', '1', '1', NULL, 'Seattle Post-Intelligencer: Movies', NULL, 'http://seattlepi.nwsource.com/rss/movies.rss'),
('d6e1961e-025e-03bc-c2eb-41e2df215811', 0, '2005-01-10 12:05:10', '2005-01-10 12:05:10', '1', '1', NULL, 'Seattle Post-Intelligencer: Music', NULL, 'http://seattlepi.nwsource.com/rss/music.rss'),
('44ac9e37-caa2-74f1-f2b2-41e2df8451fd', 0, '2005-01-10 12:05:11', '2005-01-10 12:05:11', '1', '1', NULL, 'Seattle Post-Intelligencer: Opinion', NULL, 'http://seattlepi.nwsource.com/rss/opinion.rss'),
('b59a1298-cecc-56fd-78af-41e2dfbd7ac3', 0, '2005-01-10 12:05:11', '2005-01-10 12:05:11', '1', '1', NULL, 'Seattle Post-Intelligencer: High School Sports', NULL, 'http://seattlepi.nwsource.com/rss/preps.rss'),
('daa10bd9-2c50-667b-ad93-41e2df16b642', 0, '2005-01-10 12:05:12', '2005-01-10 12:05:12', '1', '1', NULL, 'Seattle Post-Intelligencer: Seahawks', NULL, 'http://seattlepi.nwsource.com/rss/seahawks.rss'),
('896a8ce4-0a22-f2e4-cb46-41e2df90b5d5', 0, '2005-01-10 12:05:12', '2005-01-10 12:05:12', '1', '1', NULL, 'Seattle Post-Intelligencer: Sonics', NULL, 'http://seattlepi.nwsource.com/rss/sonics.rss'),
('456ffec6-3c8a-c963-4a45-41e2df7c5b60', 0, '2005-01-10 12:05:13', '2005-01-10 12:05:13', '1', '1', NULL, 'Seattle Post-Intelligencer: Theater', NULL, 'http://seattlepi.nwsource.com/rss/theater.rss'),
('92184695-82da-6b2e-0c48-41e2dfbbecac', 0, '2005-01-10 12:05:13', '2005-01-10 12:05:13', '1', '1', NULL, 'Seattle Post-Intelligencer: Art Thiel', NULL, 'http://seattlepi.nwsource.com/rss/thiel.rss'),
('e10d1442-e58c-4a7a-cc76-41e2df151d40', 0, '2005-01-10 12:05:13', '2005-01-10 12:05:13', '1', '1', NULL, 'Seattle Post-Intelligencer: TV & Radio', NULL, 'http://seattlepi.nwsource.com/rss/tv.rss'),
('52c2c540-9646-b6b4-0690-41e2dfd3c33c', 0, '2005-01-10 12:05:14', '2005-01-10 12:05:14', '1', '1', NULL, 'Seattle Post-Intelligencer: Video Games', NULL, 'http://seattlepi.nwsource.com/rss/videogames.rss'),
('1e3e0549-9ae4-d6e4-c152-41e2df2c0b60', 0, '2005-01-10 12:05:15', '2005-01-10 12:05:15', '1', '1', NULL, 'Seattle Post-Intelligencer: Wheels', NULL, 'http://seattlepi.nwsource.com/rss/wheels.rss'),
('83921b19-86dd-0a41-74b3-41e2df9dc09c', 0, '2005-01-10 12:05:19', '2005-01-10 12:05:19', '1', '1', NULL, 'Slashdot: Developers', NULL, 'http://slashdot.org/developers.rdf'),
('e10afa8d-ec55-fb12-7db5-41e2e01be5c7', 0, '2005-01-10 12:05:23', '2005-01-10 12:05:23', '1', '1', NULL, 'Slashdot:', NULL, 'http://slashdot.org/slashdot.rss'),
('b7809dbf-4e01-b6e2-fa54-41e2e0b93efd', 0, '2005-01-10 12:05:28', '2005-01-10 12:05:28', '1', '1', NULL, 'Slate Magazine', NULL, 'http://slate.msn.com/rss/'),
('25016a8b-3c84-d029-d4f0-41e2e0c4a154', 0, '2005-01-10 12:05:34', '2005-01-10 12:05:34', '1', '1', NULL, 'SourceForge.net Project News', NULL, 'http://sourceforge.net/export/rss_sfnews.php'),
('f40305df-0c9b-bd01-a424-41e2e06687ee', 0, '2005-01-10 12:05:34', '2005-01-10 12:05:34', '1', '1', NULL, 'Boston Globe -- Business News', NULL, 'http://syndication.boston.com/news/globe/business?mode=rss_10'),
('4c7e5a97-0790-4a84-1c34-41e2e0395694', 0, '2005-01-10 12:05:36', '2005-01-10 12:05:36', '1', '1', NULL, 'Boston Globe -- City/Region News', NULL, 'http://syndication.boston.com/news/globe/city_region?mode=rss_10'),
('54949bd8-5a1d-9a47-7249-41e2e055c4db', 0, '2005-01-10 12:05:37', '2005-01-10 12:05:37', '1', '1', NULL, 'Boston Globe -- Ideas Section', NULL, 'http://syndication.boston.com/news/globe/ideas?mode=rss_10'),
('4f7b5f7c-0e5c-6f17-3a7b-41e2e0ddfc44', 0, '2005-01-10 12:05:38', '2005-01-10 12:05:38', '1', '1', NULL, 'Boston Globe -- Living / Arts News', NULL, 'http://syndication.boston.com/news/globe/living?mode=rss_10'),
('228dcfab-6c30-2cf6-f7e3-41e2e0488a17', 0, '2005-01-10 12:05:39', '2005-01-10 12:05:39', '1', '1', NULL, 'Boston Globe -- National News', NULL, 'http://syndication.boston.com/news/globe/nation?mode=rss_10'),
('ea614325-af09-f851-f120-41e2e0490fa2', 0, '2005-01-10 12:05:39', '2005-01-10 12:05:39', '1', '1', NULL, 'Boston Globe -- Front Page', NULL, 'http://syndication.boston.com/news/globe/pageone?mode=rss_10'),
('4b850ec7-8c12-16fa-e99f-41e2e01c0942', 0, '2005-01-10 12:05:41', '2005-01-10 12:05:41', '1', '1', NULL, 'Boston Globe -- Sports News', NULL, 'http://syndication.boston.com/news/globe/sports?mode=rss_10'),
('158909fe-4028-662e-c57d-41e2e0cb2054', 0, '2005-01-10 12:05:42', '2005-01-10 12:05:42', '1', '1', NULL, 'Boston Globe -- World News', NULL, 'http://syndication.boston.com/news/globe/world?mode=rss_10'),
('ec7000ad-6b84-81b2-c632-41e2e0e9f21c', 0, '2005-01-10 12:05:42', '2005-01-10 12:05:42', '1', '1', NULL, 'Odds and Ends', NULL, 'http://syndication.boston.com/news/odd?mode=rss_10'),
('44f8d79e-0fae-a328-d8f8-41e2e0abc373', 0, '2005-01-10 12:05:44', '2005-01-10 12:05:44', '1', '1', NULL, 'Boston.com / News', NULL, 'http://syndication.boston.com/news?mode=rss_10'),
('d208a0cd-fb70-a0f2-fa96-41e2e01c4cf5', 0, '2005-01-10 12:05:48', '2005-01-10 12:05:48', '1', '1', NULL, 'Techdirt', NULL, 'http://techdirt.com/techdirt_rss.xml'),
('8d018850-7e9b-e970-cbd1-41e2e0c1a765', 0, '2005-01-10 12:05:53', '2005-01-10 12:05:53', '1', '1', NULL, 'Techno File', NULL, 'http://time.blogs.com/technofile/index.rdf'),
('eaf51d0c-834a-5593-ee8c-41e2e05e449f', 0, '2005-01-10 12:05:54', '2005-01-10 12:05:54', '1', '1', NULL, 'BBC News | News Front Page | UK Edition', NULL, 'http://www.bbc.co.uk/syndication/feeds/news/ukfs_news/front_page/rss091.xml'),
('28193b0e-fb4e-3197-70fb-41e2e0da0a44', 0, '2005-01-10 12:05:56', '2005-01-10 12:05:56', '1', '1', NULL, 'BBC News | Technology | UK Edition', NULL, 'http://www.bbc.co.uk/syndication/feeds/news/ukfs_news/technology/rss091.xml'),
('8610b5a9-75cc-c84c-fadc-41e2e088b0df', 0, '2005-01-10 12:05:57', '2005-01-10 12:05:57', '1', '1', NULL, 'BBC News | UK | UK Edition', NULL, 'http://www.bbc.co.uk/syndication/feeds/news/ukfs_news/uk/rss091.xml'),
('c313e1b3-db1d-26ec-84af-41e2e00160d9', 0, '2005-01-10 12:06:01', '2005-01-10 12:06:01', '1', '1', NULL, 'InfoWorld: Application development', NULL, 'http://www.infoworld.com/rss/appdev.xml'),
('2ff65a56-6bd5-c60c-9f76-41e2e0df96b8', 0, '2005-01-10 12:06:06', '2005-01-10 12:06:06', '1', '1', NULL, 'InfoWorld: Java', NULL, 'http://www.infoworld.com/rss/appdev_java.xml'),
('df955186-98c3-2983-2135-41e2e01ab985', 0, '2005-01-10 12:06:10', '2005-01-10 12:06:10', '1', '1', NULL, 'InfoWorld: Web Applications', NULL, 'http://www.infoworld.com/rss/appdev_webapp.xml'),
('3c0798b8-fb41-2fb1-3d23-41e2e01b8d2e', 0, '2005-01-10 12:06:15', '2005-01-10 12:06:15', '1', '1', NULL, 'InfoWorld: Wireless Applications', NULL, 'http://www.infoworld.com/rss/appdev_wireapps.xml'),
('7e235e26-b2f2-e691-b6be-41e2e07e66b9', 0, '2005-01-10 12:06:19', '2005-01-10 12:06:19', '1', '1', NULL, 'InfoWorld: XML', NULL, 'http://www.infoworld.com/rss/appdev_xml.xml'),
('ba005fe6-b497-6dd0-2ead-41e2e047f344', 0, '2005-01-10 12:06:23', '2005-01-10 12:06:23', '1', '1', NULL, 'InfoWorld: Applications', NULL, 'http://www.infoworld.com/rss/applications.xml'),
('2d0effdd-a0df-b611-fe99-41e2e01d157f', 0, '2005-01-10 12:06:28', '2005-01-10 12:06:28', '1', '1', NULL, 'InfoWorld: Application Management', NULL, 'http://www.infoworld.com/rss/apps_appmgmt.xml'),
('8848e6de-196b-a37f-acc1-41e2e0bded0b', 0, '2005-01-10 12:06:32', '2005-01-10 12:06:32', '1', '1', NULL, 'InfoWorld: Collaboration', NULL, 'http://www.infoworld.com/rss/apps_collab.xml'),
('d78da415-8df2-c73f-3706-41e2e03e5563', 0, '2005-01-10 12:06:36', '2005-01-10 12:06:36', '1', '1', NULL, 'InfoWorld: CRM', NULL, 'http://www.infoworld.com/rss/apps_crm.xml'),
('31114cb7-c113-29bf-9f20-41e2e0a5687a', 0, '2005-01-10 12:06:41', '2005-01-10 12:06:41', '1', '1', NULL, 'InfoWorld: Enterprise Integration', NULL, 'http://www.infoworld.com/rss/apps_einteg.xml'),
('7ea784e8-46ad-8979-dd73-41e2e0241926', 0, '2005-01-10 12:06:45', '2005-01-10 12:06:45', '1', '1', NULL, 'InfoWorld: ERP', NULL, 'http://www.infoworld.com/rss/apps_erp.xml'),
('d75c137d-0aae-3fbd-2906-41e2e0de0fe0', 0, '2005-01-10 12:06:49', '2005-01-10 12:06:49', '1', '1', NULL, 'InfoWorld: Columnists', NULL, 'http://www.infoworld.com/rss/columnists.xml'),
('288dd54e-4460-658f-f7c3-41e2e031d2f8', 0, '2005-01-10 12:06:54', '2005-01-10 12:06:54', '1', '1', NULL, 'InfoWorld: Business', NULL, 'http://www.infoworld.com/rss/ebizstra.xml'),
('5fb588ad-8d0a-c82e-920d-41e2e02745bf', 0, '2005-01-10 12:06:58', '2005-01-10 12:06:58', '1', '1', NULL, 'InfoWorld: Business to Business', NULL, 'http://www.infoworld.com/rss/ebustrat_btob.xml'),
('9794dfd2-c784-d326-2ad7-41e2e00e2a81', 0, '2005-01-10 12:07:02', '2005-01-10 12:07:02', '1', '1', NULL, 'InfoWorld: Business to Consumer', NULL, 'http://www.infoworld.com/rss/ebustrat_btoc.xml'),
('f162f572-b6da-7189-90be-41e2e02042de', 0, '2005-01-10 12:07:06', '2005-01-10 12:07:06', '1', '1', NULL, 'InfoWorld: Portals', NULL, 'http://www.infoworld.com/rss/ebustrat_portal.xml'),
('3817838b-4dbf-84b6-1549-41e2e0b0995a', 0, '2005-01-10 12:07:11', '2005-01-10 12:07:11', '1', '1', NULL, 'InfoWorld: Handheld Devices', NULL, 'http://www.infoworld.com/rss/eusrhw_handdevc.xml'),
('66f7b3eb-104a-dba5-7a66-41e2e065f261', 0, '2005-01-10 12:07:15', '2005-01-10 12:07:15', '1', '1', NULL, 'InfoWorld: Mobile PC', NULL, 'http://www.infoworld.com/rss/eusrhw_mobilepc.xml'),
('9e00fb2a-6efe-1d1f-ad34-41e2e03a4ca6', 0, '2005-01-10 12:07:19', '2005-01-10 12:07:19', '1', '1', NULL, 'InfoWorld: PCs', NULL, 'http://www.infoworld.com/rss/eusrhw_pc.xml'),
('d4c6cbba-d698-5f1f-d06a-41e2e05ba76d', 0, '2005-01-10 12:07:23', '2005-01-10 12:07:23', '1', '1', NULL, 'InfoWorld: Processors & Components', NULL, 'http://www.infoworld.com/rss/eusrhw_proccomp.xml'),
('1c467be4-a384-2226-b896-41e2e07a8bf5', 0, '2005-01-10 12:07:28', '2005-01-10 12:07:28', '1', '1', NULL, 'InfoWorld: Hardware', NULL, 'http://www.infoworld.com/rss/hardware.xml'),
('5915c10c-ea6e-ed29-b748-41e2e0feb9f0', 0, '2005-01-10 12:07:32', '2005-01-10 12:07:32', '1', '1', NULL, 'InfoWorld: Grid Computing', NULL, 'http://www.infoworld.com/rss/netwking_gridcmp.xml'),
('916d98b1-a0df-59e6-d252-41e2e0a0fce5', 0, '2005-01-10 12:07:36', '2005-01-10 12:07:36', '1', '1', NULL, 'InfoWorld: Network Infrastructure', NULL, 'http://www.infoworld.com/rss/netwking_netinfra.xml'),
('dfc0f4c7-69e2-3310-0a2a-41e2e0195d74', 0, '2005-01-10 12:07:40', '2005-01-10 12:07:40', '1', '1', NULL, 'InfoWorld: Network Management', NULL, 'http://www.infoworld.com/rss/netwking_netmgmt.xml'),
('29007367-92cc-7976-501d-41e2e037017b', 0, '2005-01-10 12:07:45', '2005-01-10 12:07:45', '1', '1', NULL, 'InfoWorld: Utilities Component', NULL, 'http://www.infoworld.com/rss/netwking_utilcomp.xml'),
('6abf823f-4b0d-e9a8-635c-41e2e039d4bf', 0, '2005-01-10 12:07:49', '2005-01-10 12:07:49', '1', '1', NULL, 'InfoWorld: Networking', NULL, 'http://www.infoworld.com/rss/networking.xml'),
('e8f2ad3b-48dd-d577-2362-41e2e0e96eec', 0, '2005-01-10 12:07:57', '2005-01-10 12:07:57', '1', '1', NULL, 'InfoWorld: Top News', NULL, 'http://www.infoworld.com/rss/news.rdf'),
('36a48d6e-be52-025b-aff8-41e2e0a360c5', 0, '2005-01-10 12:08:02', '2005-01-10 12:08:02', '1', '1', NULL, 'InfoWorld: Top News', NULL, 'http://www.infoworld.com/rss/news.xml'),
('74662412-16b7-140f-bf08-41e2e088ee14', 0, '2005-01-10 12:08:06', '2005-01-10 12:08:06', '1', '1', NULL, 'InfoWorld: Platforms', NULL, 'http://www.infoworld.com/rss/platforms.xml'),
('adf6ac68-003f-395e-c410-41e2e0e5463f', 0, '2005-01-10 12:08:10', '2005-01-10 12:08:10', '1', '1', NULL, 'InfoWorld: Application Servers', NULL, 'http://www.infoworld.com/rss/platform_appserv.xml'),
('ebb409b9-3336-e5da-16d1-41e2e0a346ab', 0, '2005-01-10 12:08:14', '2005-01-10 12:08:14', '1', '1', NULL, 'InfoWorld: Databases', NULL, 'http://www.infoworld.com/rss/platform_database.xml'),
('2c602f5d-5c2e-2b0a-558d-41e2e016671c', 0, '2005-01-10 12:08:19', '2005-01-10 12:08:19', '1', '1', NULL, 'InfoWorld: Open Source', NULL, 'http://www.infoworld.com/rss/platform_opensrc.xml'),
('64050dae-dd95-8a54-a7f4-41e2e027805a', 0, '2005-01-10 12:08:23', '2005-01-10 12:08:23', '1', '1', NULL, 'InfoWorld: Platforms', NULL, 'http://www.infoworld.com/rss/platform_os.xml'),
('9866d35a-406f-e916-f8c9-41e2e0214c5d', 0, '2005-01-10 12:08:27', '2005-01-10 12:08:27', '1', '1', NULL, 'InfoWorld: Server Hardware', NULL, 'http://www.infoworld.com/rss/platform_servhw.xml'),
('d244ac22-d651-84d4-9726-41e2e029cbde', 0, '2005-01-10 12:08:31', '2005-01-10 12:08:31', '1', '1', NULL, 'InfoWorld: Test Center Reviews', NULL, 'http://www.infoworld.com/rss/reviews.xml'),
('173d9c3d-a132-efbc-3653-41e2e07f665f', 0, '2005-01-10 12:08:36', '2005-01-10 12:08:36', '1', '1', NULL, 'InfoWorld: Security', NULL, 'http://www.infoworld.com/rss/security.xml'),
('6362dd39-39e6-dd5e-96a5-41e2e07c3534', 0, '2005-01-10 12:08:40', '2005-01-10 12:08:40', '1', '1', NULL, 'InfoWorld: Firewall', NULL, 'http://www.infoworld.com/rss/security_firewall.xml'),
('a8f67506-9b3c-92bc-046d-41e2e06b7fbc', 0, '2005-01-10 12:08:44', '2005-01-10 12:08:44', '1', '1', NULL, 'InfoWorld: Security Appliances', NULL, 'http://www.infoworld.com/rss/security_secapp.xml'),
('dc99ca3c-a068-afcc-41f0-41e2e06d71e5', 0, '2005-01-10 12:08:48', '2005-01-10 12:08:48', '1', '1', NULL, 'InfoWorld: VPN', NULL, 'http://www.infoworld.com/rss/security_vpn.xml'),
('29ac7be2-6a12-5710-435f-41e2e03416e1', 0, '2005-01-10 12:08:53', '2005-01-10 12:08:53', '1', '1', NULL, 'InfoWorld: Vulnerability Management', NULL, 'http://www.infoworld.com/rss/security_vulmgmt.xml'),
('6776e391-4544-b6e6-caf4-41e2e07f46b9', 0, '2005-01-10 12:08:57', '2005-01-10 12:08:57', '1', '1', NULL, 'InfoWorld: Wireless Security', NULL, 'http://www.infoworld.com/rss/security_wiresec.xml'),
('a24dcc9a-3fda-e158-9bcc-41e2e049a496', 0, '2005-01-10 12:09:01', '2005-01-10 12:09:01', '1', '1', NULL, 'InfoWorld: Standards', NULL, 'http://www.infoworld.com/rss/standards.xml'),
('f336c24c-3349-c630-9cbd-41e2e08442a9', 0, '2005-01-10 12:09:05', '2005-01-10 12:09:05', '1', '1', NULL, 'InfoWorld: Application Development', NULL, 'http://www.infoworld.com/rss/stanprot_appdev.xml'),
('300c6790-d2a3-75f0-8fa0-41e2e08b960b', 0, '2005-01-10 12:09:10', '2005-01-10 12:09:10', '1', '1', NULL, 'InfoWorld: Internet', NULL, 'http://www.infoworld.com/rss/stanprot_inet.xml'),
('69babb1f-fe7c-c364-83ab-41e2e0e2b049', 0, '2005-01-10 12:09:14', '2005-01-10 12:09:14', '1', '1', NULL, 'InfoWorld: Networking', NULL, 'http://www.infoworld.com/rss/stanprot_netwking.xml'),
('b97c4759-c783-6990-f94a-41e2e00fca1d', 0, '2005-01-10 12:09:18', '2005-01-10 12:09:18', '1', '1', NULL, 'InfoWorld: Security', NULL, 'http://www.infoworld.com/rss/stanprot_security.xml'),
('27b8f326-6eba-c459-8a66-41e2e0c88453', 0, '2005-01-10 12:09:23', '2005-01-10 12:09:23', '1', '1', NULL, 'InfoWorld: Storage', NULL, 'http://www.infoworld.com/rss/stanprot_storage.xml'),
('5ede0b55-899c-ccf0-a22b-41e2e0473f50', 0, '2005-01-10 12:09:27', '2005-01-10 12:09:27', '1', '1', NULL, 'InfoWorld: Wireless', NULL, 'http://www.infoworld.com/rss/stanprot_wireless.xml'),
('9ba85759-7a84-79d2-5d5b-41e2e0215d1e', 0, '2005-01-10 12:09:31', '2005-01-10 12:09:31', '1', '1', NULL, 'InfoWorld: Storage', NULL, 'http://www.infoworld.com/rss/storage.xml'),
('d9cde4fd-28f6-faed-2555-41e2e04de5cb', 0, '2005-01-10 12:09:35', '2005-01-10 12:09:35', '1', '1', NULL, 'InfoWorld: Business Continuity', NULL, 'http://www.infoworld.com/rss/storage_buscont.xml'),
('2bdd0ae4-0ddc-8c7c-6bbe-41e2e1f0faeb', 0, '2005-01-10 12:09:40', '2005-01-10 12:09:40', '1', '1', NULL, 'InfoWorld: Networked Storage', NULL, 'http://www.infoworld.com/rss/storage_netstor.xml'),
('68d12716-8c75-08d5-0785-41e2e1f8d408', 0, '2005-01-10 12:09:44', '2005-01-10 12:09:44', '1', '1', NULL, 'InfoWorld: Storage Hardware', NULL, 'http://www.infoworld.com/rss/storage_storhw.xml'),
('a6502372-e900-cd75-587f-41e2e1a348db', 0, '2005-01-10 12:09:48', '2005-01-10 12:09:48', '1', '1', NULL, 'InfoWorld: Storage Management', NULL, 'http://www.infoworld.com/rss/storage_stormgmt.xml'),
('e494037a-c6c4-8793-108b-41e2e15f0f95', 0, '2005-01-10 12:09:52', '2005-01-10 12:09:52', '1', '1', NULL, 'InfoWorld: Telecom', NULL, 'http://www.infoworld.com/rss/telecomm.xml'),
('42778fab-3037-1280-059b-41e2e149966a', 0, '2005-01-10 12:09:57', '2005-01-10 12:09:57', '1', '1', NULL, 'InfoWorld: Broadband', NULL, 'http://www.infoworld.com/rss/telecom_broadband.xml'),
('82a29d82-9ad5-65ca-b74d-41e2e1b9cc06', 0, '2005-01-10 12:10:01', '2005-01-10 12:10:01', '1', '1', NULL, 'InfoWorld: Telephony', NULL, 'http://www.infoworld.com/rss/telecom_telephony.xml'),
('d05fc611-e074-96ea-4186-41e2e1577cea', 0, '2005-01-10 12:10:05', '2005-01-10 12:10:05', '1', '1', NULL, 'InfoWorld: xSPs', NULL, 'http://www.infoworld.com/rss/telecom_xsp.xml'),
('21f3db54-63ce-67b8-999d-41e2e11b3dfb', 0, '2005-01-10 12:10:10', '2005-01-10 12:10:10', '1', '1', NULL, 'InfoWorld: Web services', NULL, 'http://www.infoworld.com/rss/webservices.xml'),
('586629ae-ecab-f5d5-41f9-41e2e1eb8af6', 0, '2005-01-10 12:10:14', '2005-01-10 12:10:14', '1', '1', NULL, 'InfoWorld: Web Services Applications', NULL, 'http://www.infoworld.com/rss/websvcs_websvcap.xml'),
('8a5a7213-cc37-924c-1fef-41e2e1607a6a', 0, '2005-01-10 12:10:18', '2005-01-10 12:10:18', '1', '1', NULL, 'InfoWorld: Web Services Development', NULL, 'http://www.infoworld.com/rss/websvcs_websvcdv.xml'),
('d30ec90d-86e4-6b11-05dd-41e2e155d05a', 0, '2005-01-10 12:10:22', '2005-01-10 12:10:22', '1', '1', NULL, 'InfoWorld: Web Services Integration', NULL, 'http://www.infoworld.com/rss/websvcs_websvcin.xml'),
('22f69fbf-65ef-2790-262f-41e2e17a93e2', 0, '2005-01-10 12:10:27', '2005-01-10 12:10:27', '1', '1', NULL, 'InfoWorld: Web Management', NULL, 'http://www.infoworld.com/rss/websvcs_websvcmg.xml'),
('7ce83cab-8e9d-f80f-0013-41e2e17e3a32', 0, '2005-01-10 12:10:31', '2005-01-10 12:10:31', '1', '1', NULL, 'InfoWorld: Wireless', NULL, 'http://www.infoworld.com/rss/wireless.xml'),
('bcaa2681-9bf1-9ef1-6be9-41e2e1c982e2', 0, '2005-01-10 12:10:35', '2005-01-10 12:10:35', '1', '1', NULL, 'InfoWorld: Wireless Applications', NULL, 'http://www.infoworld.com/rss/wireless_wireapps.xml'),
('53e80be9-2d4c-d3d4-9e83-41e2e1a6d6be', 0, '2005-01-10 12:10:40', '2005-01-10 12:10:40', '1', '1', NULL, 'InfoWorld: Wireless Network Infrastructure', NULL, 'http://www.infoworld.com/rss/wireless_wirenetinfra.xml'),
('53714c90-4807-b021-8e0a-41e2e16e168f', 0, '2005-01-10 12:10:44', '2005-01-10 12:10:44', '1', '1', NULL, 'InfoWorld: Wireless Network Management', NULL, 'http://www.infoworld.com/rss/wireless_wirenetmgmt.xml'),
('a63fa605-f6f5-fdb5-9b4b-41e2e1f0b3dd', 0, '2005-01-10 12:10:48', '2005-01-10 12:10:48', '1', '1', NULL, 'Linux Magazine', NULL, 'http://www.linux-mag.com/lm.rss'),
('5bd9c58c-b0a7-bdef-ebca-41e2e150faf6', 0, '2005-01-10 12:10:57', '2005-01-10 12:10:57', '1', '1', NULL, 'Linux Journal - The Original Magazine of the Linux Community', NULL, 'http://www.linuxjournal.com/news.rss'),
('49e7d772-950e-9f18-b39e-41e2e1344dd3', 0, '2005-01-10 12:11:06', '2005-01-10 12:11:06', '1', '1', NULL, 'macdailynews.com', NULL, 'http://www.macdailynews.com/rss/rss.xml'),
('b15d917f-6220-4792-f3cf-41e2e1295e2d', 0, '2005-01-10 12:11:06', '2005-01-10 12:11:06', '1', '1', NULL, 'MacRumors', NULL, 'http://www.macrumors.com/macrumors.xml'),
('90634cab-75d2-4b3f-4424-41e2e18d34f1', 0, '2005-01-10 12:11:11', '2005-01-10 12:11:11', '1', '1', NULL, 'GoogleGuy Says - Google Ranking Info', NULL, 'http://www.markcarey.com/googleguy-says/index.rdf'),
('3b548447-e8e8-b3d1-c4c7-41e2e1c46b85', 0, '2005-01-10 12:11:12', '2005-01-10 12:11:12', '1', '1', NULL, 'CBS MarketWatch.com - Financial Services Industry News', NULL, 'http://www.marketwatch.com/rss/financial/'),
('dff224ac-4ccf-aed7-43b9-41e2e194fb1e', 0, '2005-01-10 12:11:12', '2005-01-10 12:11:12', '1', '1', NULL, 'CBS MarketWatch.com - Internet Industry News', NULL, 'http://www.marketwatch.com/rss/internet/'),
('9abe4795-81da-da4b-c27d-41e2e1f12209', 0, '2005-01-10 12:11:13', '2005-01-10 12:11:13', '1', '1', NULL, 'CBS MarketWatch.com - Personal Finance News', NULL, 'http://www.marketwatch.com/rss/pf/'),
('4dfb388a-1305-c6a6-bb1b-41e2e1ec0ea0', 0, '2005-01-10 12:11:14', '2005-01-10 12:11:14', '1', '1', NULL, 'CBS MarketWatch.com - Software Industry News', NULL, 'http://www.marketwatch.com/rss/software/'),
('5a554d53-5f02-0030-fa51-41e2e1c4ec0b', 0, '2005-01-10 12:11:15', '2005-01-10 12:11:15', '1', '1', NULL, 'CBS MarketWatch.com - Top Stories', NULL, 'http://www.marketwatch.com/rss/topstories/'),
('70a60db8-b993-ffdc-c7a2-41e2e1f9f2cc', 0, '2005-01-10 12:11:16', '2005-01-10 12:11:16', '1', '1', NULL, 'Reuters: Business', NULL, 'http://www.microsite.reuters.com/rss/businessNews'),
('8bd7e961-fade-e048-969b-41e2e15a0523', 0, '2005-01-10 12:11:16', '2005-01-10 12:11:16', '1', '1', NULL, 'Reuters: US Domestic News', NULL, 'http://www.microsite.reuters.com/rss/domesticNews'),
('157fe8ea-d139-355e-b64d-41e2e1d60dd1', 0, '2005-01-10 12:11:17', '2005-01-10 12:11:17', '1', '1', NULL, 'Reuters: Politics', NULL, 'http://www.microsite.reuters.com/rss/ElectionCoverage'),
('92d037ee-06a3-418f-74c4-41e2e19c22f5', 0, '2005-01-10 12:11:17', '2005-01-10 12:11:17', '1', '1', NULL, 'Reuters: Entertainment', NULL, 'http://www.microsite.reuters.com/rss/Entertainment'),
('1e69fa33-09c2-f086-1f49-41e2e130cd40', 0, '2005-01-10 12:11:18', '2005-01-10 12:11:18', '1', '1', NULL, 'Reuters: Health', NULL, 'http://www.microsite.reuters.com/rss/healthNews'),
('9f9d5276-26a0-aadc-1e03-41e2e13d5fc3', 0, '2005-01-10 12:11:18', '2005-01-10 12:11:18', '1', '1', NULL, 'Reuters: Life & Leisure', NULL, 'http://www.microsite.reuters.com/rss/lifeAndLeisureNews'),
('22ae3feb-5b25-0c61-7d1c-41e2e134a898', 0, '2005-01-10 12:11:19', '2005-01-10 12:11:19', '1', '1', NULL, 'Reuters: Oddly Enough', NULL, 'http://www.microsite.reuters.com/rss/oddlyEnoughNews'),
('a446695a-c6d2-e970-0d13-41e2e10b4f0a', 0, '2005-01-10 12:11:19', '2005-01-10 12:11:19', '1', '1', NULL, 'Reuters: Politics', NULL, 'http://www.microsite.reuters.com/rss/politicsNews'),
('4360b39e-9f38-94ab-68f5-41e2e1f8ebfc', 0, '2005-01-10 12:11:20', '2005-01-10 12:11:20', '1', '1', NULL, 'Reuters: Science', NULL, 'http://www.microsite.reuters.com/rss/scienceNews'),
('bdbfe8e9-980b-c873-e3b0-41e2e1426554', 0, '2005-01-10 12:11:20', '2005-01-10 12:11:20', '1', '1', NULL, 'Reuters: Sports', NULL, 'http://www.microsite.reuters.com/rss/sportsNews'),
('56773914-9860-cc40-5763-41e2e126f83f', 0, '2005-01-10 12:11:21', '2005-01-10 12:11:21', '1', '1', NULL, 'Reuters: Technology', NULL, 'http://www.microsite.reuters.com/rss/technologyNews'),
('d098cf04-c313-867c-11d9-41e2e1fd4fe3', 0, '2005-01-10 12:11:21', '2005-01-10 12:11:21', '1', '1', NULL, 'Reuters: Top News', NULL, 'http://www.microsite.reuters.com/rss/topNews'),
('563ae3f7-df64-9e60-d02a-41e2e182eacb', 0, '2005-01-10 12:11:22', '2005-01-10 12:11:22', '1', '1', NULL, 'Reuters: World', NULL, 'http://www.microsite.reuters.com/rss/worldNews'),
('e52e8793-7b64-d778-4382-41e2e11bda85', 0, '2005-01-10 12:11:26', '2005-01-10 12:11:26', '1', '1', NULL, 'NYT > Arts', NULL, 'http://www.nytimes.com/services/xml/rss/nyt/Arts.xml'),
('76395a02-40cb-98c1-7e38-41e2e1cfef20', 0, '2005-01-10 12:11:31', '2005-01-10 12:11:31', '1', '1', NULL, 'NYT > Automobiles', NULL, 'http://www.nytimes.com/services/xml/rss/nyt/Automobiles.xml'),
('f329defa-0a0b-72bd-874b-41e2e1d870e6', 0, '2005-01-10 12:11:35', '2005-01-10 12:11:35', '1', '1', NULL, 'NYT > Books', NULL, 'http://www.nytimes.com/services/xml/rss/nyt/Books.xml'),
('7bd169d6-5a81-5486-a70b-41e2e1bdb372', 0, '2005-01-10 12:11:40', '2005-01-10 12:11:40', '1', '1', NULL, 'NYT > Business', NULL, 'http://www.nytimes.com/services/xml/rss/nyt/Business.xml'),
('32da2be6-a2be-a445-baeb-41e2e10be2b4', 0, '2005-01-10 12:11:59', '2005-01-10 12:11:59', '1', '1', NULL, 'NYT > Circuits', NULL, 'http://www.nytimes.com/services/xml/rss/nyt/Circuits.xml'),
('abc4b934-4df7-b840-2c71-41e2e1b5c624', 0, '2005-01-10 12:12:03', '2005-01-10 12:12:03', '1', '1', NULL, 'NYT > Fashion and Style', NULL, 'http://www.nytimes.com/services/xml/rss/nyt/FashionandStyle.xml'),
('3e368e6c-a9c8-9db5-8c58-41e2e13063cc', 0, '2005-01-10 12:12:08', '2005-01-10 12:12:08', '1', '1', NULL, 'NYT > Health', NULL, 'http://www.nytimes.com/services/xml/rss/nyt/Health.xml'),
('ad6f00c5-4d44-a7f3-3956-41e2e13cb440', 0, '2005-01-10 12:12:12', '2005-01-10 12:12:12', '1', '1', NULL, 'NYT > Home Page', NULL, 'http://www.nytimes.com/services/xml/rss/nyt/HomePage.xml'),
('3243b29a-5bf0-73e7-ff62-41e2e10d3f1e', 0, '2005-01-10 12:12:17', '2005-01-10 12:12:17', '1', '1', NULL, 'NYT > International', NULL, 'http://www.nytimes.com/services/xml/rss/nyt/International.xml'),
('c0a95969-5b4c-0813-5070-41e2e1dc098d', 0, '2005-01-10 12:12:21', '2005-01-10 12:12:21', '1', '1', NULL, 'NYT > Magazine', NULL, 'http://www.nytimes.com/services/xml/rss/nyt/Magazine.xml'),
('b26227be-c4ac-d0db-3534-41e2e1e3c2bd', 0, '2005-01-10 12:12:31', '2005-01-10 12:12:31', '1', '1', NULL, 'NYT > Media and Advertising', NULL, 'http://www.nytimes.com/services/xml/rss/nyt/MediaandAdvertising.xml'),
('949123f7-e6b3-9c9b-6d3f-41e2e1ac24ca', 0, '2005-01-10 12:12:41', '2005-01-10 12:12:41', '1', '1', NULL, 'NYT > Movie Reviews', NULL, 'http://www.nytimes.com/services/xml/rss/nyt/Movies.xml'),
('1daf4a0b-1a1a-3f5b-9114-41e2e1ac5bd2', 0, '2005-01-10 12:12:46', '2005-01-10 12:12:46', '1', '1', NULL, 'NYT > Multimedia', NULL, 'http://www.nytimes.com/services/xml/rss/nyt/Multimedia.xml'),
('93f6049a-0b8c-368f-84db-41e2e10cf8dc', 0, '2005-01-10 12:12:50', '2005-01-10 12:12:50', '1', '1', NULL, 'NYT > National', NULL, 'http://www.nytimes.com/services/xml/rss/nyt/National.xml'),
('162fe62d-82ad-3acb-e40b-41e2e1ca3a42', 0, '2005-01-10 12:12:55', '2005-01-10 12:12:55', '1', '1', NULL, 'NYT > New York Region', NULL, 'http://www.nytimes.com/services/xml/rss/nyt/NYRegion.xml'),
('8b3b6aa8-afe3-f354-6934-41e2e1289783', 0, '2005-01-10 12:12:59', '2005-01-10 12:12:59', '1', '1', NULL, 'NYT > Opinion', NULL, 'http://www.nytimes.com/services/xml/rss/nyt/Opinion.xml'),
('16f15af7-8097-d8ff-e71b-41e2e1e3e56d', 0, '2005-01-10 12:13:04', '2005-01-10 12:13:04', '1', '1', NULL, 'NYT > Most E-mailed Articles', NULL, 'http://www.nytimes.com/services/xml/rss/nyt/pop_top.xml'),
('8c824e5a-a19e-3d18-f0b3-41e2e1347681', 0, '2005-01-10 12:13:08', '2005-01-10 12:13:08', '1', '1', NULL, 'NYT > Real Estate', NULL, 'http://www.nytimes.com/services/xml/rss/nyt/RealEstate.xml'),
('18a8f2f9-a4bb-40a0-c2fa-41e2e1a22c5b', 0, '2005-01-10 12:13:13', '2005-01-10 12:13:13', '1', '1', NULL, 'NYT > Science', NULL, 'http://www.nytimes.com/services/xml/rss/nyt/Science.xml'),
('8b5a69c3-7872-7621-978d-41e2e16ed94a', 0, '2005-01-10 12:13:17', '2005-01-10 12:13:17', '1', '1', NULL, 'NYT > Sports', NULL, 'http://www.nytimes.com/services/xml/rss/nyt/Sports.xml'),
('2c248cf4-cbb0-1ae3-98dc-41e2e1f4b908', 0, '2005-01-10 12:13:22', '2005-01-10 12:13:22', '1', '1', NULL, 'NYT > Technology', NULL, 'http://www.nytimes.com/services/xml/rss/nyt/Technology.xml'),
('b87e7042-bb54-8c71-eb00-41e2e1fe56f3', 0, '2005-01-10 12:13:26', '2005-01-10 12:13:26', '1', '1', NULL, 'NYT > Theater', NULL, 'http://www.nytimes.com/services/xml/rss/nyt/Theater.xml'),
('56a41258-299c-b87a-f11f-41e2e1dea02e', 0, '2005-01-10 12:13:40', '2005-01-10 12:13:40', '1', '1', NULL, 'NYT > Travel', NULL, 'http://www.nytimes.com/services/xml/rss/nyt/Travel.xml'),
('cad9e735-4edb-cf76-fa57-41e2e1bd9573', 0, '2005-01-10 12:13:44', '2005-01-10 12:13:44', '1', '1', NULL, 'NYT > Washington', NULL, 'http://www.nytimes.com/services/xml/rss/nyt/Washington.xml'),
('6c4ffca7-d6e0-762b-7319-41e2e1942650', 0, '2005-01-10 12:13:49', '2005-01-10 12:13:49', '1', '1', NULL, 'NYT > Week in Review', NULL, 'http://www.nytimes.com/services/xml/rss/nyt/WeekinReview.xml'),
('8fc8560f-f3b2-2f3f-7a8e-41e2e2b89d5a', 0, '2005-01-10 12:13:54', '2005-01-10 12:13:54', '1', '1', NULL, 'Telegraph Arts | Books', NULL, 'http://www.telegraph.co.uk/newsfeed/rss/arts_books.xml'),
('5ed58c84-963b-dc9e-9192-41e2e286de6b', 0, '2005-01-10 12:13:59', '2005-01-10 12:13:59', '1', '1', NULL, 'Telegraph Arts', NULL, 'http://www.telegraph.co.uk/newsfeed/rss/arts_main.xml'),
('3f8d6369-2cb1-34ff-2a84-41e2e2ad2838', 0, '2005-01-10 12:14:04', '2005-01-10 12:14:04', '1', '1', NULL, 'Telegraph Connected', NULL, 'http://www.telegraph.co.uk/newsfeed/rss/connected.xml'),
('26c18e81-99a1-dd1d-2971-41e2e2b1f154', 0, '2005-01-10 12:14:09', '2005-01-10 12:14:09', '1', '1', NULL, 'Telegraph Education', NULL, 'http://www.telegraph.co.uk/newsfeed/rss/education.xml'),
('f09bdcc6-8e18-f9b6-a868-41e2e26e388c', 0, '2005-01-10 12:14:13', '2005-01-10 12:14:13', '1', '1', NULL, 'Telegraph Expat', NULL, 'http://www.telegraph.co.uk/newsfeed/rss/expat.xml'),
('caa98477-7101-c99e-018d-41e2e2c7c022', 0, '2005-01-10 12:14:18', '2005-01-10 12:14:18', '1', '1', NULL, 'Telegraph Fashion', NULL, 'http://www.telegraph.co.uk/newsfeed/rss/fashion.xml'),
('abf7648d-d5c0-31cd-b450-41e2e294e388', 0, '2005-01-10 12:14:23', '2005-01-10 12:14:23', '1', '1', NULL, 'Telegraph Gardening', NULL, 'http://www.telegraph.co.uk/newsfeed/rss/gardening.xml');
INSERT INTO `feeds` (`id`, `deleted`, `date_entered`, `date_modified`, `modified_user_id`, `assigned_user_id`, `created_by`, `title`, `description`, `url`) VALUES
('a1762049-8d34-762a-49ff-41e2e23d820e', 0, '2005-01-10 12:14:28', '2005-01-10 12:14:28', '1', '1', NULL, 'Telegraph Health', NULL, 'http://www.telegraph.co.uk/newsfeed/rss/health.xml'),
('3f266fb5-3e33-91e2-73be-41e2e2dd9764', 0, '2005-01-10 12:14:33', '2005-01-10 12:14:33', '1', '1', NULL, 'Telegraph Opinion | Leaders', NULL, 'http://www.telegraph.co.uk/newsfeed/rss/leaders.xml'),
('108906cd-58af-9a2e-a04e-41e2e23db4a1', 0, '2005-01-10 12:14:38', '2005-01-10 12:14:38', '1', '1', NULL, 'Telegraph Opinion | Letters', NULL, 'http://www.telegraph.co.uk/newsfeed/rss/letters.xml'),
('b4eff965-86bf-e571-fef0-41e2e234081f', 0, '2005-01-10 12:14:42', '2005-01-10 12:14:42', '1', '1', NULL, 'Telegraph Money | Markets', NULL, 'http://www.telegraph.co.uk/newsfeed/rss/money_markets.xml'),
('9bebccf6-65fe-7240-9802-41e2e2a5d2b6', 0, '2005-01-10 12:14:47', '2005-01-10 12:14:47', '1', '1', NULL, 'Telegraph Money | Personal Finance', NULL, 'http://www.telegraph.co.uk/newsfeed/rss/money_pf.xml'),
('6268c9b4-82b3-74b5-be2d-41e2e280945c', 0, '2005-01-10 12:14:52', '2005-01-10 12:14:52', '1', '1', NULL, 'Telegraph Business | Small Business', NULL, 'http://www.telegraph.co.uk/newsfeed/rss/money_smallbus.xml'),
('6313eb76-e22b-cb06-b80b-41e2e25b1270', 0, '2005-01-10 12:14:57', '2005-01-10 12:14:57', '1', '1', NULL, 'Telegraph Motoring', NULL, 'http://www.telegraph.co.uk/newsfeed/rss/motoring.xml'),
('42131d7e-df56-90a2-6ff1-41e2e21b63d4', 0, '2005-01-10 12:15:02', '2005-01-10 12:15:02', '1', '1', NULL, 'Telegraph News | Breaking News', NULL, 'http://www.telegraph.co.uk/newsfeed/rss/news_breaking.xml'),
('13dd2763-7cff-361b-6864-41e2e2f367cb', 0, '2005-01-10 12:15:07', '2005-01-10 12:15:07', '1', '1', NULL, 'Telegraph News | International News', NULL, 'http://www.telegraph.co.uk/newsfeed/rss/news_international.xml'),
('e00bd292-8c0c-4f96-49b0-41e2e2ff2f9a', 0, '2005-01-10 12:15:11', '2005-01-10 12:15:11', '1', '1', NULL, 'Telegraph News | Front Page News', NULL, 'http://www.telegraph.co.uk/newsfeed/rss/news_main.xml'),
('b2da8888-120f-bfb7-67d6-41e2e28c5532', 0, '2005-01-10 12:15:16', '2005-01-10 12:15:16', '1', '1', NULL, 'Telegraph News | UK News', NULL, 'http://www.telegraph.co.uk/newsfeed/rss/news_uk.xml'),
('885e54fd-09ba-9cdc-a9e3-41e2e2ebb292', 0, '2005-01-10 12:15:21', '2005-01-10 12:15:21', '1', '1', NULL, 'Telegraph Opinion', NULL, 'http://www.telegraph.co.uk/newsfeed/rss/opinion.xml'),
('689b064d-8821-4b66-9788-41e2e2e48838', 0, '2005-01-10 12:15:26', '2005-01-10 12:15:26', '1', '1', NULL, 'Telegraph Property', NULL, 'http://www.telegraph.co.uk/newsfeed/rss/property.xml'),
('c36c0a13-8f54-a6c5-6b0b-41e2e27050c6', 0, '2005-01-10 12:15:31', '2005-01-10 12:15:31', '1', '1', NULL, 'Telegraph Sport | Cricket', NULL, 'http://www.telegraph.co.uk/newsfeed/rss/sport_cricket.xml'),
('4ecabb98-a3cd-4b96-d288-41e2e2943a21', 0, '2005-01-10 12:15:36', '2005-01-10 12:15:36', '1', '1', NULL, 'Telegraph Sport | Football', NULL, 'http://www.telegraph.co.uk/newsfeed/rss/sport_football.xml'),
('e911ad05-13d6-869d-6e85-41e2e24224d4', 0, '2005-01-10 12:15:40', '2005-01-10 12:15:40', '1', '1', NULL, 'Telegraph Sport | Golf', NULL, 'http://www.telegraph.co.uk/newsfeed/rss/sport_golf.xml'),
('ba80d353-4126-d39e-363e-41e2e29b93b6', 0, '2005-01-10 12:15:45', '2005-01-10 12:15:45', '1', '1', NULL, 'Telegraph Sport', NULL, 'http://www.telegraph.co.uk/newsfeed/rss/sport_main.xml'),
('bd5e4012-976e-3cdb-bacb-41e2e2409362', 0, '2005-01-10 12:15:50', '2005-01-10 12:15:50', '1', '1', NULL, 'Telegraph Sport | Rugby Union', NULL, 'http://www.telegraph.co.uk/newsfeed/rss/sport_rugu.xml'),
('c7d5ddd0-3e83-5b31-5158-41e2e2c8eeaf', 0, '2005-01-10 12:15:55', '2005-01-10 12:15:55', '1', '1', NULL, 'Telegraph Travel', NULL, 'http://www.telegraph.co.uk/newsfeed/rss/travel_main.xml'),
('933a0c4c-b2b9-cef9-a3a1-41e2e2afe273', 0, '2005-01-10 12:16:00', '2005-01-10 12:16:00', '1', '1', NULL, 'Telegraph Wine', NULL, 'http://www.telegraph.co.uk/newsfeed/rss/wine.xml'),
('3ac7981d-0c22-8f87-72c3-41e2e2965536', 0, '2005-01-10 12:16:05', '2005-01-10 12:16:05', '1', '1', NULL, 'washingtonpost.com - Business', NULL, 'http://www.washingtonpost.com/wp-srv/business/rssheadlines.xml'),
('c09c8abc-7992-2dc4-face-41e2e268cfb9', 0, '2005-01-10 12:16:09', '2005-01-10 12:16:09', '1', '1', NULL, 'washingtonpost.com - Education', NULL, 'http://www.washingtonpost.com/wp-srv/education/rssheadlines.xml'),
('6de7e3cf-20a5-8ae9-9b91-41e2e2531c32', 0, '2005-01-10 12:16:14', '2005-01-10 12:16:14', '1', '1', NULL, 'washingtonpost.com - Health', NULL, 'http://www.washingtonpost.com/wp-srv/health/rssheadlines.xml'),
('e6962597-5923-ce09-9db4-41e2e2864635', 0, '2005-01-10 12:16:18', '2005-01-10 12:16:18', '1', '1', NULL, 'washingtonpost.com - Metro', NULL, 'http://www.washingtonpost.com/wp-srv/metro/rssheadlines.xml'),
('644ba5b6-6a86-c78c-9fc7-41e2e213bcc8', 0, '2005-01-10 12:16:23', '2005-01-10 12:16:23', '1', '1', NULL, 'washingtonpost.com - Nation', NULL, 'http://www.washingtonpost.com/wp-srv/nation/rssheadlines.xml'),
('e06ad79d-0a57-7366-1e30-41e2e2e06c22', 0, '2005-01-10 12:16:27', '2005-01-10 12:16:27', '1', '1', NULL, 'washingtonpost.com - Opinion', NULL, 'http://www.washingtonpost.com/wp-srv/opinion/rssheadlines.xml'),
('b04689c2-b068-7223-d8af-41e2e2687b82', 0, '2005-01-10 12:16:32', '2005-01-10 12:16:32', '1', '1', NULL, 'washingtonpost.com - 2004 Election', NULL, 'http://www.washingtonpost.com/wp-srv/politics/elections/2004/rssheadlines.xml'),
('4660f0e9-37bf-af65-dbb3-41e2e2e3aef3', 0, '2005-01-10 12:16:37', '2005-01-10 12:16:37', '1', '1', NULL, 'washingtonpost.com - Politics', NULL, 'http://www.washingtonpost.com/wp-srv/politics/rssheadlines.xml'),
('d549ff0c-6666-a153-9804-41e2e2dea5de', 0, '2005-01-10 12:16:41', '2005-01-10 12:16:41', '1', '1', NULL, 'washingtonpost.com - Real Estate', NULL, 'http://www.washingtonpost.com/wp-srv/realestate/rssheadlines.xml'),
('6204cd5b-0bf2-eda1-1c1b-41e2e2475997', 0, '2005-01-10 12:16:46', '2005-01-10 12:16:46', '1', '1', NULL, 'washingtonpost.com - Sports', NULL, 'http://www.washingtonpost.com/wp-srv/sports/rssheadlines.xml'),
('227a0fa6-21fb-a275-66f8-41e2e2b91da7', 0, '2005-01-10 12:16:51', '2005-01-10 12:16:51', '1', '1', NULL, 'washingtonpost.com - Technology', NULL, 'http://www.washingtonpost.com/wp-srv/technology/rssheadlines.xml'),
('b8ef9e66-846e-8c8a-5e37-41e2e2139fb6', 0, '2005-01-10 12:16:55', '2005-01-10 12:16:55', '1', '1', NULL, 'washingtonpost.com - World', NULL, 'http://www.washingtonpost.com/wp-srv/world/rssheadlines.xml'),
('1426c66e-f338-2f01-6c37-41e2e250f9c6', 0, '2005-01-10 12:17:09', '2005-01-10 12:17:09', '1', '1', NULL, 'Wired News', NULL, 'http://www.wired.com/news_drop/netcenter/netcenter.rdf'),
('dad0faed-5e63-a3d3-2600-41e2e25b3432', 0, '2005-01-10 12:17:13', '2005-01-10 12:17:13', '1', '1', NULL, 'African News from World Press Review', NULL, 'http://www.worldpress.org/feeds/Africa.xml'),
('975ea85d-b5e3-147d-9a4e-41e2e2bd803c', 0, '2005-01-10 12:17:18', '2005-01-10 12:17:18', '1', '1', NULL, 'Latin American and Canadian News from World Press Review', NULL, 'http://www.worldpress.org/feeds/Americas.xml'),
('5c4cc320-8083-64c3-3e26-41e2e2f6b657', 0, '2005-01-10 12:17:23', '2005-01-10 12:17:23', '1', '1', NULL, 'Asian News from World Press Review', NULL, 'http://www.worldpress.org/feeds/Asia.xml'),
('2b354b70-9a4f-f7ce-a98e-41e2e21f0c8f', 0, '2005-01-10 12:17:28', '2005-01-10 12:17:28', '1', '1', NULL, 'European News from World Press Review', NULL, 'http://www.worldpress.org/feeds/Europe.xml'),
('dc142b87-1213-1748-38c8-41e2e23eb448', 0, '2005-01-10 12:17:32', '2005-01-10 12:17:32', '1', '1', NULL, 'Middle Eastern News from World Press Review', NULL, 'http://www.worldpress.org/feeds/Mideast.xml'),
('ade6b2d5-1c1d-f17c-564c-41e2e20caaa3', 0, '2005-01-10 12:17:37', '2005-01-10 12:17:37', '1', '1', NULL, 'Top Headlines from World Press Review', NULL, 'http://www.worldpress.org/feeds/topstories.xml'),
('3c13d9ed-6b2e-ffc0-432a-41e2e2bd099b', 0, '2005-01-10 12:17:42', '2005-01-10 12:17:42', '1', '1', NULL, 'Breaking News Headlines from Around the World, Powered by Worldpress.org', NULL, 'http://www.worldpress.org/feeds/worldpresswire.xml'),
('cb27f088-bcd4-6378-d754-41e2e2c815b8', 0, '2005-01-10 12:17:50', '2005-01-10 12:17:50', '1', '1', NULL, 'Linux Journal - The Original Magazine of the Linux Community', NULL, 'http://www3.linuxjournal.com/news.rss'),
('d3b812de-b09b-d19d-1ef7-41e2e2c7175b', 0, '2005-01-10 12:17:51', '2005-01-10 12:17:51', '1', '1', NULL, 'ZDNet News - Front Door', NULL, 'http://zdnet.com.com/2036-2_2-0.xml'),
('4a1301d9-c4fe-f6be-4edb-41e2e22b41b0', 0, '2005-01-10 12:17:52', '2005-01-10 12:17:52', '1', '1', NULL, 'ZDNet News - Hardware', NULL, 'http://zdnet.com.com/2509-1103_2-0-10.xml'),
('b5cc579b-0de1-f1fb-2f4c-41e2e23dc415', 0, '2005-01-10 12:17:52', '2005-01-10 12:17:52', '1', '1', NULL, 'ZDNet News - Software', NULL, 'http://zdnet.com.com/2509-1104_2-0-10.xml'),
('36085453-2d39-d3b9-1b9c-41e2e2ab4ee7', 0, '2005-01-10 12:17:53', '2005-01-10 12:17:53', '1', '1', NULL, 'ZDNet News - Security', NULL, 'http://zdnet.com.com/2509-1105_2-0-10.xml'),
('ae0619d9-4e7e-f462-6d85-41e2e21bfd46', 0, '2005-01-10 12:17:53', '2005-01-10 12:17:53', '1', '1', NULL, 'ZDNet News - Commentary', NULL, 'http://zdnet.com.com/2509-1107_2-0-10.xml'),
('30d86812-bc01-d5d3-faca-41e2e2d9bd0d', 0, '2005-01-10 12:17:54', '2005-01-10 12:17:54', '1', '1', NULL, 'ZDNet News - Latest Headlines', NULL, 'http://zdnet.com.com/2509-11_2-0-20.xml'),
('90df24f4-9717-7a1b-ed5b-41e77fbcfba7', 0, '2005-01-14 00:16:00', '2005-01-14 00:16:00', '1', '1', '1', 'SourceForge.net: SF.net Recent Project Donations: SugarCRM', NULL, 'http://sourceforge.net/export/rss2_projdonors.php?group_id=107819'),
('4bbca87f-2017-5488-d8e0-41e7808c2553', 0, '2005-01-14 00:17:00', '2005-01-14 00:17:00', '1', '1', '1', 'SourceForge.net: SF.net Project News: SugarCRM', NULL, 'http://sourceforge.net/export/rss2_projnews.php?group_id=107819'),
('c03b64eb-d0d4-a188-e203-41e7804677bd', 0, '2005-01-14 00:17:00', '2005-01-14 00:17:00', '1', '1', '1', 'SourceForge.net: SF.net Project News: SugarCRM  (including full news text)', NULL, 'http://sourceforge.net/export/rss2_projnews.php?group_id=107819&rss_fulltext=1'),
('42210fc0-d21c-cff6-2c72-41e780195bfc', 0, '2005-01-14 00:17:00', '2005-01-14 00:17:00', '1', '1', '1', 'SourceForge.net: Project File Releases: SugarCRM', NULL, 'http://sourceforge.net/export/rss2_projfiles.php?group_id=107819'),
('d410145c-e217-8ad3-0e99-41e78038cf4a', 0, '2005-01-14 00:17:00', '2005-01-14 00:17:00', '1', '1', '1', 'SourceForge.net: Project Documentation (DocManager) Updates: SugarCRM', NULL, 'http://sourceforge.net/export/rss2_projdocs.php?group_id=107819'),
('db197b9c-9158-d779-0be3-41e780eda0f6', 0, '2005-01-14 00:17:00', '2005-01-14 00:17:00', '1', '1', '1', 'SourceForge.net: Project Summary: SugarCRM  (sugarcrm project)', NULL, 'http://sourceforge.net/export/rss2_projsummary.php?group_id=107819');

-- --------------------------------------------------------

--
-- Table structure for table `fields_meta_data`
--

CREATE TABLE IF NOT EXISTS `fields_meta_data` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) default NULL,
  `vname` varchar(255) default NULL,
  `comments` varchar(255) default NULL,
  `help` varchar(255) default NULL,
  `custom_module` varchar(255) default NULL,
  `type` varchar(255) default NULL,
  `len` int(11) default NULL,
  `required` tinyint(1) default '0',
  `default_value` varchar(255) default NULL,
  `date_modified` datetime default NULL,
  `deleted` tinyint(1) default '0',
  `audited` tinyint(1) default '0',
  `massupdate` tinyint(1) default '0',
  `duplicate_merge` smallint(6) default '0',
  `reportable` tinyint(1) default '1',
  `importable` varchar(255) default NULL,
  `ext1` varchar(255) default NULL,
  `ext2` varchar(255) default NULL,
  `ext3` varchar(255) default NULL,
  `ext4` text,
  PRIMARY KEY  (`id`),
  KEY `idx_meta_id_del` (`id`,`deleted`),
  KEY `idx_meta_cm_del` (`custom_module`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `fields_meta_data`
--


-- --------------------------------------------------------

--
-- Table structure for table `folders`
--

CREATE TABLE IF NOT EXISTS `folders` (
  `id` char(36) NOT NULL,
  `name` varchar(25) NOT NULL,
  `folder_type` varchar(25) default NULL,
  `parent_folder` char(36) default NULL,
  `has_child` tinyint(1) default '0',
  `is_group` tinyint(1) default '0',
  `is_dynamic` tinyint(1) default '0',
  `dynamic_query` text,
  `assign_to_id` char(36) default NULL,
  `created_by` char(36) NOT NULL,
  `modified_by` char(36) NOT NULL,
  `deleted` tinyint(1) default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_parent_folder` (`parent_folder`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `folders`
--


-- --------------------------------------------------------

--
-- Table structure for table `folders_rel`
--

CREATE TABLE IF NOT EXISTS `folders_rel` (
  `id` char(36) NOT NULL,
  `folder_id` char(36) NOT NULL,
  `polymorphic_module` varchar(25) NOT NULL,
  `polymorphic_id` char(36) NOT NULL,
  `deleted` tinyint(1) default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_poly_module_poly_id` (`polymorphic_module`,`polymorphic_id`),
  KEY `idx_folders_rel_folder_id` (`folder_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `folders_rel`
--


-- --------------------------------------------------------

--
-- Table structure for table `folders_subscriptions`
--

CREATE TABLE IF NOT EXISTS `folders_subscriptions` (
  `id` char(36) NOT NULL,
  `folder_id` char(36) NOT NULL,
  `assigned_user_id` char(36) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `idx_folder_id_assigned_user_id` (`folder_id`,`assigned_user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `folders_subscriptions`
--


-- --------------------------------------------------------

--
-- Table structure for table `iframes`
--

CREATE TABLE IF NOT EXISTS `iframes` (
  `id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `placement` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL default '0',
  `deleted` tinyint(1) NOT NULL default '0',
  `date_entered` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  `created_by` char(36) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `idx_cont_name` (`name`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `iframes`
--


-- --------------------------------------------------------

--
-- Table structure for table `import_maps`
--

CREATE TABLE IF NOT EXISTS `import_maps` (
  `id` char(36) NOT NULL,
  `name` varchar(254) NOT NULL,
  `source` varchar(36) NOT NULL,
  `enclosure` varchar(1) NOT NULL default ' ',
  `delimiter` varchar(1) NOT NULL default ',',
  `module` varchar(36) NOT NULL,
  `content` blob,
  `default_values` blob,
  `has_header` tinyint(1) NOT NULL default '1',
  `deleted` tinyint(1) NOT NULL default '0',
  `date_entered` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  `assigned_user_id` char(36) default NULL,
  `is_published` varchar(3) NOT NULL default 'no',
  PRIMARY KEY  (`id`),
  KEY `idx_owner_module_name` (`assigned_user_id`,`module`,`name`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `import_maps`
--


-- --------------------------------------------------------

--
-- Table structure for table `inbound_email`
--

CREATE TABLE IF NOT EXISTS `inbound_email` (
  `id` varchar(36) NOT NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  `date_entered` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  `modified_user_id` char(36) default NULL,
  `created_by` char(36) default NULL,
  `name` varchar(255) default NULL,
  `status` varchar(25) NOT NULL default 'Active',
  `server_url` varchar(100) NOT NULL,
  `email_user` varchar(100) NOT NULL,
  `email_password` varchar(100) NOT NULL,
  `port` int(5) NOT NULL,
  `service` varchar(50) NOT NULL,
  `mailbox` text NOT NULL,
  `delete_seen` tinyint(1) default '0',
  `mailbox_type` varchar(10) default NULL,
  `template_id` char(36) default NULL,
  `stored_options` text,
  `group_id` char(36) default NULL,
  `is_personal` tinyint(1) NOT NULL default '0',
  `groupfolder_id` char(36) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `inbound_email`
--


-- --------------------------------------------------------

--
-- Table structure for table `inbound_email_autoreply`
--

CREATE TABLE IF NOT EXISTS `inbound_email_autoreply` (
  `id` char(36) NOT NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  `date_entered` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  `autoreplied_to` varchar(100) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `idx_ie_autoreplied_to` (`autoreplied_to`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `inbound_email_autoreply`
--


-- --------------------------------------------------------

--
-- Table structure for table `inbound_email_cache_ts`
--

CREATE TABLE IF NOT EXISTS `inbound_email_cache_ts` (
  `id` varchar(255) NOT NULL,
  `ie_timestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `inbound_email_cache_ts`
--


-- --------------------------------------------------------

--
-- Table structure for table `leads`
--

CREATE TABLE IF NOT EXISTS `leads` (
  `id` char(36) NOT NULL,
  `date_entered` datetime default NULL,
  `date_modified` datetime default NULL,
  `modified_user_id` char(36) default NULL,
  `created_by` char(36) default NULL,
  `description` text,
  `deleted` tinyint(1) default '0',
  `assigned_user_id` char(36) default NULL,
  `salutation` varchar(5) default NULL,
  `first_name` varchar(100) default NULL,
  `last_name` varchar(100) default NULL,
  `title` varchar(100) default NULL,
  `department` varchar(100) default NULL,
  `do_not_call` tinyint(1) default '0',
  `phone_home` varchar(25) default NULL,
  `phone_mobile` varchar(25) default NULL,
  `phone_work` varchar(25) default NULL,
  `phone_other` varchar(25) default NULL,
  `phone_fax` varchar(25) default NULL,
  `primary_address_street` varchar(150) default NULL,
  `primary_address_city` varchar(100) default NULL,
  `primary_address_state` varchar(100) default NULL,
  `primary_address_postalcode` varchar(20) default NULL,
  `primary_address_country` varchar(255) default NULL,
  `alt_address_street` varchar(150) default NULL,
  `alt_address_city` varchar(100) default NULL,
  `alt_address_state` varchar(100) default NULL,
  `alt_address_postalcode` varchar(20) default NULL,
  `alt_address_country` varchar(255) default NULL,
  `assistant` varchar(75) default NULL,
  `assistant_phone` varchar(25) default NULL,
  `converted` tinyint(1) NOT NULL default '0',
  `refered_by` varchar(100) default NULL,
  `lead_source` varchar(100) default NULL,
  `lead_source_description` text,
  `status` varchar(100) default NULL,
  `status_description` text,
  `reports_to_id` char(36) default NULL,
  `account_name` varchar(255) default NULL,
  `account_description` text,
  `contact_id` char(36) default NULL,
  `account_id` char(36) default NULL,
  `opportunity_id` char(36) default NULL,
  `opportunity_name` varchar(255) default NULL,
  `opportunity_amount` varchar(50) default NULL,
  `campaign_id` char(36) default NULL,
  `portal_name` varchar(255) default NULL,
  `portal_app` varchar(255) default NULL,
  PRIMARY KEY  (`id`),
  KEY `idx_lead_acct_name_first` (`account_name`,`deleted`),
  KEY `idx_lead_last_first` (`last_name`,`first_name`,`deleted`),
  KEY `idx_lead_del_stat` (`last_name`,`status`,`deleted`,`first_name`),
  KEY `idx_lead_opp_del` (`opportunity_id`,`deleted`),
  KEY `idx_leads_acct_del` (`account_id`,`deleted`),
  KEY `idx_del_user` (`deleted`,`assigned_user_id`),
  KEY `idx_lead_assigned` (`assigned_user_id`),
  KEY `idx_lead_contact` (`contact_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `leads`
--


-- --------------------------------------------------------

--
-- Table structure for table `leads_audit`
--

CREATE TABLE IF NOT EXISTS `leads_audit` (
  `id` char(36) NOT NULL,
  `parent_id` char(36) NOT NULL,
  `date_created` datetime default NULL,
  `created_by` varchar(36) default NULL,
  `field_name` varchar(100) default NULL,
  `data_type` varchar(100) default NULL,
  `before_value_string` varchar(255) default NULL,
  `after_value_string` varchar(255) default NULL,
  `before_value_text` text,
  `after_value_text` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `leads_audit`
--


-- --------------------------------------------------------

--
-- Table structure for table `linked_documents`
--

CREATE TABLE IF NOT EXISTS `linked_documents` (
  `id` varchar(36) NOT NULL,
  `parent_id` varchar(36) default NULL,
  `parent_type` varchar(25) default NULL,
  `document_id` varchar(36) default NULL,
  `document_revision_id` varchar(36) default NULL,
  `date_modified` datetime default NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_parent_document` (`parent_type`,`parent_id`,`document_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `linked_documents`
--


-- --------------------------------------------------------

--
-- Table structure for table `meetings`
--

CREATE TABLE IF NOT EXISTS `meetings` (
  `id` char(36) NOT NULL,
  `name` varchar(50) default NULL,
  `date_entered` datetime default NULL,
  `date_modified` datetime default NULL,
  `modified_user_id` char(36) default NULL,
  `created_by` char(36) default NULL,
  `description` text,
  `deleted` tinyint(1) default '0',
  `assigned_user_id` char(36) default NULL,
  `location` varchar(50) default NULL,
  `duration_hours` int(2) default NULL,
  `duration_minutes` int(2) default NULL,
  `date_start` datetime default NULL,
  `date_end` date default NULL,
  `parent_type` varchar(25) default NULL,
  `status` varchar(25) default NULL,
  `parent_id` char(36) default NULL,
  `reminder_time` int(11) default '-1',
  `outlook_id` varchar(255) default NULL,
  PRIMARY KEY  (`id`),
  KEY `idx_mtg_name` (`name`),
  KEY `idx_meet_par_del` (`parent_id`,`parent_type`,`deleted`),
  KEY `idx_meet_stat_del` (`assigned_user_id`,`status`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `meetings`
--


-- --------------------------------------------------------

--
-- Table structure for table `meetings_contacts`
--

CREATE TABLE IF NOT EXISTS `meetings_contacts` (
  `id` varchar(36) NOT NULL,
  `meeting_id` varchar(36) default NULL,
  `contact_id` varchar(36) default NULL,
  `required` varchar(1) default '1',
  `accept_status` varchar(25) default 'none',
  `date_modified` datetime default NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_con_mtg_mtg` (`meeting_id`),
  KEY `idx_con_mtg_con` (`contact_id`),
  KEY `idx_meeting_contact` (`meeting_id`,`contact_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `meetings_contacts`
--


-- --------------------------------------------------------

--
-- Table structure for table `meetings_leads`
--

CREATE TABLE IF NOT EXISTS `meetings_leads` (
  `id` varchar(36) NOT NULL,
  `meeting_id` varchar(36) default NULL,
  `lead_id` varchar(36) default NULL,
  `required` varchar(1) default '1',
  `accept_status` varchar(25) default 'none',
  `date_modified` datetime default NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_lead_meeting_meeting` (`meeting_id`),
  KEY `idx_lead_meeting_lead` (`lead_id`),
  KEY `idx_meeting_lead` (`meeting_id`,`lead_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `meetings_leads`
--


-- --------------------------------------------------------

--
-- Table structure for table `meetings_users`
--

CREATE TABLE IF NOT EXISTS `meetings_users` (
  `id` varchar(36) NOT NULL,
  `meeting_id` varchar(36) default NULL,
  `user_id` varchar(36) default NULL,
  `required` varchar(1) default '1',
  `accept_status` varchar(25) default 'none',
  `date_modified` datetime default NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_usr_mtg_mtg` (`meeting_id`),
  KEY `idx_usr_mtg_usr` (`user_id`),
  KEY `idx_meeting_users` (`meeting_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `meetings_users`
--


-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE IF NOT EXISTS `notes` (
  `id` char(36) NOT NULL,
  `date_entered` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  `modified_user_id` char(36) default NULL,
  `created_by` char(36) default NULL,
  `name` varchar(255) default NULL,
  `filename` varchar(255) default NULL,
  `file_mime_type` varchar(100) default NULL,
  `parent_type` varchar(25) default NULL,
  `parent_id` char(36) default NULL,
  `contact_id` char(36) default NULL,
  `portal_flag` tinyint(1) NOT NULL default '0',
  `embed_flag` tinyint(1) NOT NULL default '0',
  `description` text,
  `deleted` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_note_name` (`name`),
  KEY `idx_notes_parent` (`parent_id`,`parent_type`),
  KEY `idx_note_contact` (`contact_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `notes`
--


-- --------------------------------------------------------

--
-- Table structure for table `opportunities`
--

CREATE TABLE IF NOT EXISTS `opportunities` (
  `id` char(36) NOT NULL,
  `name` varchar(50) default NULL,
  `date_entered` datetime default NULL,
  `date_modified` datetime default NULL,
  `modified_user_id` char(36) default NULL,
  `created_by` char(36) default NULL,
  `description` text,
  `deleted` tinyint(1) default '0',
  `assigned_user_id` char(36) default NULL,
  `opportunity_type` varchar(255) default NULL,
  `campaign_id` char(36) default NULL,
  `lead_source` varchar(50) default NULL,
  `amount` double default NULL,
  `amount_usdollar` double default NULL,
  `currency_id` char(36) default NULL,
  `date_closed` date default NULL,
  `next_step` varchar(100) default NULL,
  `sales_stage` varchar(25) default NULL,
  `probability` double default NULL,
  PRIMARY KEY  (`id`),
  KEY `idx_opp_name` (`name`),
  KEY `idx_opp_assigned` (`assigned_user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `opportunities`
--


-- --------------------------------------------------------

--
-- Table structure for table `opportunities_audit`
--

CREATE TABLE IF NOT EXISTS `opportunities_audit` (
  `id` char(36) NOT NULL,
  `parent_id` char(36) NOT NULL,
  `date_created` datetime default NULL,
  `created_by` varchar(36) default NULL,
  `field_name` varchar(100) default NULL,
  `data_type` varchar(100) default NULL,
  `before_value_string` varchar(255) default NULL,
  `after_value_string` varchar(255) default NULL,
  `before_value_text` text,
  `after_value_text` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `opportunities_audit`
--


-- --------------------------------------------------------

--
-- Table structure for table `opportunities_contacts`
--

CREATE TABLE IF NOT EXISTS `opportunities_contacts` (
  `id` varchar(36) NOT NULL,
  `contact_id` varchar(36) default NULL,
  `opportunity_id` varchar(36) default NULL,
  `contact_role` varchar(50) default NULL,
  `date_modified` datetime default NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_con_opp_con` (`contact_id`),
  KEY `idx_con_opp_opp` (`opportunity_id`),
  KEY `idx_opportunities_contacts` (`opportunity_id`,`contact_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `opportunities_contacts`
--


-- --------------------------------------------------------

--
-- Table structure for table `outbound_email`
--

CREATE TABLE IF NOT EXISTS `outbound_email` (
  `id` char(36) NOT NULL,
  `name` varchar(50) NOT NULL,
  `type` varchar(6) NOT NULL default 'user',
  `user_id` char(36) NOT NULL,
  `mail_sendtype` varchar(8) NOT NULL default 'sendmail',
  `mail_smtpserver` varchar(100) default NULL,
  `mail_smtpport` int(5) default NULL,
  `mail_smtpuser` varchar(100) default NULL,
  `mail_smtppass` varchar(100) default NULL,
  `mail_smtpauth_req` tinyint(1) default '0',
  `mail_smtpssl` tinyint(1) default '0',
  PRIMARY KEY  (`id`),
  KEY `oe_user_id_idx` (`id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `outbound_email`
--

INSERT INTO `outbound_email` (`id`, `name`, `type`, `user_id`, `mail_sendtype`, `mail_smtpserver`, `mail_smtpport`, `mail_smtpuser`, `mail_smtppass`, `mail_smtpauth_req`, `mail_smtpssl`) VALUES
('7baf223a-cb40-78a4-0764-4a6d7fef4cf1', 'system', 'system', '1', 'SMTP', '', 25, '', '', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `project`
--

CREATE TABLE IF NOT EXISTS `project` (
  `id` char(36) NOT NULL,
  `date_entered` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  `assigned_user_id` char(36) default NULL,
  `modified_user_id` char(36) default NULL,
  `created_by` char(36) default NULL,
  `name` varchar(50) NOT NULL,
  `description` text,
  `deleted` tinyint(1) NOT NULL default '0',
  `estimated_start_date` date NOT NULL,
  `estimated_end_date` date NOT NULL,
  `status` varchar(255) default NULL,
  `priority` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `project`
--


-- --------------------------------------------------------

--
-- Table structure for table `projects_accounts`
--

CREATE TABLE IF NOT EXISTS `projects_accounts` (
  `id` varchar(36) NOT NULL,
  `account_id` varchar(36) default NULL,
  `project_id` varchar(36) default NULL,
  `date_modified` datetime default NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_proj_acct_proj` (`project_id`),
  KEY `idx_proj_acct_acct` (`account_id`),
  KEY `projects_accounts_alt` (`project_id`,`account_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `projects_accounts`
--


-- --------------------------------------------------------

--
-- Table structure for table `projects_bugs`
--

CREATE TABLE IF NOT EXISTS `projects_bugs` (
  `id` varchar(36) NOT NULL,
  `bug_id` varchar(36) default NULL,
  `project_id` varchar(36) default NULL,
  `date_modified` datetime default NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_proj_bug_proj` (`project_id`),
  KEY `idx_proj_bug_bug` (`bug_id`),
  KEY `projects_bugs_alt` (`project_id`,`bug_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `projects_bugs`
--


-- --------------------------------------------------------

--
-- Table structure for table `projects_cases`
--

CREATE TABLE IF NOT EXISTS `projects_cases` (
  `id` varchar(36) NOT NULL,
  `case_id` varchar(36) default NULL,
  `project_id` varchar(36) default NULL,
  `date_modified` datetime default NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_proj_case_proj` (`project_id`),
  KEY `idx_proj_case_case` (`case_id`),
  KEY `projects_cases_alt` (`project_id`,`case_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `projects_cases`
--


-- --------------------------------------------------------

--
-- Table structure for table `projects_contacts`
--

CREATE TABLE IF NOT EXISTS `projects_contacts` (
  `id` varchar(36) NOT NULL,
  `contact_id` varchar(36) default NULL,
  `project_id` varchar(36) default NULL,
  `date_modified` datetime default NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_proj_con_proj` (`project_id`),
  KEY `idx_proj_con_con` (`contact_id`),
  KEY `projects_contacts_alt` (`project_id`,`contact_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `projects_contacts`
--


-- --------------------------------------------------------

--
-- Table structure for table `projects_opportunities`
--

CREATE TABLE IF NOT EXISTS `projects_opportunities` (
  `id` varchar(36) NOT NULL,
  `opportunity_id` varchar(36) default NULL,
  `project_id` varchar(36) default NULL,
  `date_modified` datetime default NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_proj_opp_proj` (`project_id`),
  KEY `idx_proj_opp_opp` (`opportunity_id`),
  KEY `projects_opportunities_alt` (`project_id`,`opportunity_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `projects_opportunities`
--


-- --------------------------------------------------------

--
-- Table structure for table `projects_products`
--

CREATE TABLE IF NOT EXISTS `projects_products` (
  `id` varchar(36) NOT NULL,
  `product_id` varchar(36) default NULL,
  `project_id` varchar(36) default NULL,
  `date_modified` datetime default NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_proj_prod_project` (`project_id`),
  KEY `idx_proj_prod_product` (`product_id`),
  KEY `projects_products_alt` (`project_id`,`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `projects_products`
--


-- --------------------------------------------------------

--
-- Table structure for table `project_task`
--

CREATE TABLE IF NOT EXISTS `project_task` (
  `id` char(36) NOT NULL,
  `date_entered` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  `project_id` char(36) default NULL,
  `project_task_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `status` varchar(255) default NULL,
  `description` text,
  `predecessors` text,
  `date_start` date default NULL,
  `time_start` int(11) default NULL,
  `time_finish` int(11) default NULL,
  `date_finish` date default NULL,
  `duration` int(11) NOT NULL,
  `duration_unit` text NOT NULL,
  `actual_duration` int(11) default NULL,
  `percent_complete` int(11) default NULL,
  `parent_task_id` int(11) default NULL,
  `assigned_user_id` char(36) default NULL,
  `modified_user_id` char(36) default NULL,
  `priority` varchar(255) default NULL,
  `created_by` char(36) default NULL,
  `milestone_flag` tinyint(1) default '0',
  `order_number` int(11) default '1',
  `task_number` int(11) default NULL,
  `estimated_effort` int(11) default NULL,
  `actual_effort` int(11) default NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  `utilization` int(11) default '100',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `project_task`
--


-- --------------------------------------------------------

--
-- Table structure for table `project_task_audit`
--

CREATE TABLE IF NOT EXISTS `project_task_audit` (
  `id` char(36) NOT NULL,
  `parent_id` char(36) NOT NULL,
  `date_created` datetime default NULL,
  `created_by` varchar(36) default NULL,
  `field_name` varchar(100) default NULL,
  `data_type` varchar(100) default NULL,
  `before_value_string` varchar(255) default NULL,
  `after_value_string` varchar(255) default NULL,
  `before_value_text` text,
  `after_value_text` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `project_task_audit`
--


-- --------------------------------------------------------

--
-- Table structure for table `prospects`
--

CREATE TABLE IF NOT EXISTS `prospects` (
  `id` char(36) NOT NULL,
  `date_entered` datetime default NULL,
  `date_modified` datetime default NULL,
  `modified_user_id` char(36) default NULL,
  `created_by` char(36) default NULL,
  `description` text,
  `deleted` tinyint(1) default '0',
  `assigned_user_id` char(36) default NULL,
  `salutation` varchar(5) default NULL,
  `first_name` varchar(100) default NULL,
  `last_name` varchar(100) default NULL,
  `title` varchar(100) default NULL,
  `department` varchar(255) default NULL,
  `do_not_call` tinyint(1) default '0',
  `phone_home` varchar(25) default NULL,
  `phone_mobile` varchar(25) default NULL,
  `phone_work` varchar(25) default NULL,
  `phone_other` varchar(25) default NULL,
  `phone_fax` varchar(25) default NULL,
  `primary_address_street` varchar(150) default NULL,
  `primary_address_city` varchar(100) default NULL,
  `primary_address_state` varchar(100) default NULL,
  `primary_address_postalcode` varchar(20) default NULL,
  `primary_address_country` varchar(255) default NULL,
  `alt_address_street` varchar(150) default NULL,
  `alt_address_city` varchar(100) default NULL,
  `alt_address_state` varchar(100) default NULL,
  `alt_address_postalcode` varchar(20) default NULL,
  `alt_address_country` varchar(255) default NULL,
  `assistant` varchar(75) default NULL,
  `assistant_phone` varchar(25) default NULL,
  `tracker_key` int(11) NOT NULL auto_increment,
  `birthdate` date default NULL,
  `lead_id` char(36) default NULL,
  `account_name` varchar(150) default NULL,
  `campaign_id` char(36) default NULL,
  PRIMARY KEY  (`id`),
  KEY `prospect_auto_tracker_key` (`tracker_key`),
  KEY `idx_prospects_last_first` (`last_name`,`first_name`,`deleted`),
  KEY `idx_prospecs_del_last` (`last_name`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `prospects`
--


-- --------------------------------------------------------

--
-- Table structure for table `prospect_lists`
--

CREATE TABLE IF NOT EXISTS `prospect_lists` (
  `id` char(36) NOT NULL,
  `name` varchar(50) default NULL,
  `list_type` varchar(25) default NULL,
  `date_entered` datetime default NULL,
  `date_modified` datetime default NULL,
  `modified_user_id` char(36) default NULL,
  `assigned_user_id` char(36) default NULL,
  `created_by` char(36) default NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  `description` text,
  `domain_name` varchar(255) default NULL,
  PRIMARY KEY  (`id`),
  KEY `idx_prospect_list_name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `prospect_lists`
--


-- --------------------------------------------------------

--
-- Table structure for table `prospect_lists_prospects`
--

CREATE TABLE IF NOT EXISTS `prospect_lists_prospects` (
  `id` varchar(36) NOT NULL,
  `prospect_list_id` varchar(36) default NULL,
  `related_id` varchar(36) default NULL,
  `related_type` varchar(25) default NULL,
  `date_modified` datetime default NULL,
  `deleted` tinyint(1) default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_plp_pro_id` (`prospect_list_id`),
  KEY `idx_plp_rel_id` (`related_id`,`related_type`,`prospect_list_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `prospect_lists_prospects`
--


-- --------------------------------------------------------

--
-- Table structure for table `prospect_list_campaigns`
--

CREATE TABLE IF NOT EXISTS `prospect_list_campaigns` (
  `id` varchar(36) NOT NULL,
  `prospect_list_id` varchar(36) default NULL,
  `campaign_id` varchar(36) default NULL,
  `date_modified` datetime default NULL,
  `deleted` tinyint(1) default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_pro_id` (`prospect_list_id`),
  KEY `idx_cam_id` (`campaign_id`),
  KEY `idx_prospect_list_campaigns` (`prospect_list_id`,`campaign_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `prospect_list_campaigns`
--


-- --------------------------------------------------------

--
-- Table structure for table `relationships`
--

CREATE TABLE IF NOT EXISTS `relationships` (
  `id` char(36) NOT NULL,
  `relationship_name` varchar(150) NOT NULL,
  `lhs_module` varchar(100) NOT NULL,
  `lhs_table` varchar(64) NOT NULL,
  `lhs_key` varchar(64) NOT NULL,
  `rhs_module` varchar(100) NOT NULL,
  `rhs_table` varchar(64) NOT NULL,
  `rhs_key` varchar(64) NOT NULL,
  `join_table` varchar(64) default NULL,
  `join_key_lhs` varchar(64) default NULL,
  `join_key_rhs` varchar(64) default NULL,
  `relationship_type` varchar(64) default NULL,
  `relationship_role_column` varchar(64) default NULL,
  `relationship_role_column_value` varchar(50) default NULL,
  `reverse` tinyint(1) default '0',
  `deleted` tinyint(1) default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_rel_name` (`relationship_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `relationships`
--

INSERT INTO `relationships` (`id`, `relationship_name`, `lhs_module`, `lhs_table`, `lhs_key`, `rhs_module`, `rhs_table`, `rhs_key`, `join_table`, `join_key_lhs`, `join_key_rhs`, `relationship_type`, `relationship_role_column`, `relationship_role_column_value`, `reverse`, `deleted`) VALUES
('86760683-eb6d-6ca1-59b1-4a6d7f07fc95', 'leads_modified_user', 'Users', 'users', 'id', 'Leads', 'leads', 'modified_user_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('868fccdc-b460-78f3-b8e6-4a6d7fca3cd6', 'leads_created_by', 'Users', 'users', 'id', 'Leads', 'leads', 'created_by', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('86a9954e-7145-898b-f181-4a6d7f5c6cce', 'leads_assigned_user', 'Users', 'users', 'id', 'Leads', 'leads', 'assigned_user_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('86c2db97-9ffd-a7c8-9957-4a6d7f4920f8', 'leads_email_addresses_primary', 'Leads', 'leads', 'id', 'EmailAddresses', 'email_addresses', 'id', 'email_addr_bean_rel', 'bean_id', 'email_address_id', 'many-to-many', NULL, NULL, 0, 0),
('86dcbba2-7192-3b36-2182-4a6d7f9298d7', 'lead_direct_reports', 'Leads', 'leads', 'id', 'Leads', 'leads', 'reports_to_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('86f5feff-c747-97d5-f685-4a6d7fa90a40', 'lead_tasks', 'Leads', 'leads', 'id', 'Tasks', 'tasks', 'parent_id', NULL, NULL, NULL, 'one-to-many', 'parent_type', 'Leads', 0, 0),
('870fdb04-6977-2f4f-518a-4a6d7f12899f', 'lead_notes', 'Leads', 'leads', 'id', 'Notes', 'notes', 'parent_id', NULL, NULL, NULL, 'one-to-many', 'parent_type', 'Leads', 0, 0),
('8729bb79-d601-cd20-f9d8-4a6d7fdbf326', 'lead_meetings', 'Leads', 'leads', 'id', 'Meetings', 'meetings', 'parent_id', NULL, NULL, NULL, 'one-to-many', 'parent_type', 'Leads', 0, 0),
('8743b675-a087-9b3c-4654-4a6d7f092229', 'lead_calls', 'Leads', 'leads', 'id', 'Calls', 'calls', 'parent_id', NULL, NULL, NULL, 'one-to-many', 'parent_type', 'Leads', 0, 0),
('875da1c0-4ee1-1860-1ef2-4a6d7f2e0bcc', 'lead_emails', 'Leads', 'leads', 'id', 'Emails', 'emails', 'parent_id', NULL, NULL, NULL, 'one-to-many', 'parent_type', 'Leads', 0, 0),
('8777a90f-f03c-5963-0888-4a6d7fdd963c', 'lead_campaign_log', 'Leads', 'leads', 'id', 'CampaignLog', 'campaign_log', 'target_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('8f2a6857-a598-97ea-814b-4a6d7f5d9b70', 'contacts_modified_user', 'Users', 'users', 'id', 'Contacts', 'contacts', 'modified_user_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('8f52f304-7571-8626-e85e-4a6d7fe35b69', 'contacts_created_by', 'Users', 'users', 'id', 'Contacts', 'contacts', 'created_by', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('8f7a956b-6c62-1654-8974-4a6d7f56123b', 'contacts_assigned_user', 'Users', 'users', 'id', 'Contacts', 'contacts', 'assigned_user_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('8fa27f1d-7b3f-c001-3ec9-4a6d7f797d6f', 'contacts_email_addresses_primary', 'Contacts', 'contacts', 'id', 'EmailAddresses', 'email_addresses', 'id', 'email_addr_bean_rel', 'bean_id', 'email_address_id', 'many-to-many', NULL, NULL, 0, 0),
('8fcb6088-ba9f-99e7-f492-4a6d7fa2a262', 'contact_direct_reports', 'Contacts', 'contacts', 'id', 'Contacts', 'contacts', 'reports_to_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('8ff35ac5-7aa9-b3f9-49dc-4a6d7f5f970b', 'contact_leads', 'Contacts', 'contacts', 'id', 'Leads', 'leads', 'contact_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('901b325a-c41f-0936-560c-4a6d7f412255', 'contact_notes', 'Contacts', 'contacts', 'id', 'Notes', 'notes', 'contact_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('90436980-70e0-bd62-a0c0-4a6d7f47c3e8', 'contact_tasks', 'Contacts', 'contacts', 'id', 'Tasks', 'tasks', 'contact_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('906bf39e-d5c7-a876-1f8f-4a6d7fadb300', 'contact_campaign_log', 'Contacts', 'contacts', 'id', 'CampaignLog', 'campaign_log', 'target_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('99d8dd8e-282b-6d8f-c75b-4a6d7f8e6f7a', 'accounts_modified_user', 'Users', 'users', 'id', 'Accounts', 'accounts', 'modified_user_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('9a022a6a-660e-b973-a570-4a6d7f56237f', 'accounts_created_by', 'Users', 'users', 'id', 'Accounts', 'accounts', 'created_by', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('9a29a780-ae43-f6bc-d46e-4a6d7fd44c6e', 'accounts_assigned_user', 'Users', 'users', 'id', 'Accounts', 'accounts', 'assigned_user_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('9a52755c-e41b-a697-0cde-4a6d7f610ca0', 'accounts_email_addresses_primary', 'Accounts', 'accounts', 'id', 'EmailAddresses', 'email_addresses', 'id', 'email_addr_bean_rel', 'bean_id', 'email_address_id', 'many-to-many', NULL, NULL, 0, 0),
('9a7c6ae5-6ba3-a82f-2333-4a6d7fcc0943', 'member_accounts', 'Accounts', 'accounts', 'id', 'Accounts', 'accounts', 'parent_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('9aa6985a-d887-9d67-f6d5-4a6d7f6dde7d', 'account_cases', 'Accounts', 'accounts', 'id', 'Cases', 'cases', 'account_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('9ace3a30-9336-074e-d6bb-4a6d7fb891b9', 'account_tasks', 'Accounts', 'accounts', 'id', 'Tasks', 'tasks', 'parent_id', NULL, NULL, NULL, 'one-to-many', 'parent_type', 'Accounts', 0, 0),
('9af700df-9030-c4ba-5554-4a6d7f10b880', 'account_notes', 'Accounts', 'accounts', 'id', 'Notes', 'notes', 'parent_id', NULL, NULL, NULL, 'one-to-many', 'parent_type', 'Accounts', 0, 0),
('9b1f85b9-a02b-4e3d-079e-4a6d7f0bb710', 'account_meetings', 'Accounts', 'accounts', 'id', 'Meetings', 'meetings', 'parent_id', NULL, NULL, NULL, 'one-to-many', 'parent_type', 'Accounts', 0, 0),
('9b49232f-b081-e454-98dd-4a6d7f4b2a58', 'account_calls', 'Accounts', 'accounts', 'id', 'Calls', 'calls', 'parent_id', NULL, NULL, NULL, 'one-to-many', 'parent_type', 'Accounts', 0, 0),
('9b7aa9e3-87a0-9ee3-210d-4a6d7fc36c72', 'account_emails', 'Accounts', 'accounts', 'id', 'Emails', 'emails', 'parent_id', NULL, NULL, NULL, 'one-to-many', 'parent_type', 'Accounts', 0, 0),
('9ba4f1ed-8fb2-f252-9406-4a6d7fbf4e3d', 'account_leads', 'Accounts', 'accounts', 'id', 'Leads', 'leads', 'account_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('a44934b6-9711-fe13-6380-4a6d7f8e4bc1', 'opportunities_modified_user', 'Users', 'users', 'id', 'Opportunities', 'opportunities', 'modified_user_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('a4749d35-79e8-5751-4a03-4a6d7fc1b73f', 'opportunities_created_by', 'Users', 'users', 'id', 'Opportunities', 'opportunities', 'created_by', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('a49f06f8-6e5b-1e4e-56cf-4a6d7f9195f9', 'opportunities_assigned_user', 'Users', 'users', 'id', 'Opportunities', 'opportunities', 'assigned_user_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('a4c9ab67-743d-49d4-05b5-4a6d7ff60dae', 'opportunity_calls', 'Opportunities', 'opportunities', 'id', 'Calls', 'calls', 'parent_id', NULL, NULL, NULL, 'one-to-many', 'parent_type', 'Opportunities', 0, 0),
('a4f8599b-53ee-b659-5c97-4a6d7f730e7e', 'opportunity_meetings', 'Opportunities', 'opportunities', 'id', 'Meetings', 'meetings', 'parent_id', NULL, NULL, NULL, 'one-to-many', 'parent_type', 'Opportunities', 0, 0),
('a5247269-2998-ec6f-5306-4a6d7f601a4d', 'opportunity_tasks', 'Opportunities', 'opportunities', 'id', 'Tasks', 'tasks', 'parent_id', NULL, NULL, NULL, 'one-to-many', 'parent_type', 'Opportunities', 0, 0),
('a55225d1-e38d-b968-dc7c-4a6d7fc3c4fc', 'opportunity_notes', 'Opportunities', 'opportunities', 'id', 'Notes', 'notes', 'parent_id', NULL, NULL, NULL, 'one-to-many', 'parent_type', 'Opportunities', 0, 0),
('a57dd9e5-51f0-3f53-4584-4a6d7f2a1dfd', 'opportunity_emails', 'Opportunities', 'opportunities', 'id', 'Emails', 'emails', 'parent_id', NULL, NULL, NULL, 'one-to-many', 'parent_type', 'Opportunities', 0, 0),
('a5aa4a81-4742-53ab-e56c-4a6d7fff0d18', 'opportunity_leads', 'Opportunities', 'opportunities', 'id', 'Leads', 'leads', 'opportunity_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('a5d58693-0c94-043f-5ee1-4a6d7f250534', 'opportunity_currencies', 'Opportunities', 'opportunities', 'currency_id', 'Currencies', 'currencies', 'id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('a60173fc-9a41-f336-9ffc-4a6d7f06b465', 'opportunities_campaign', 'campaigns', 'campaigns', 'id', 'Opportunities', 'opportunities', 'campaign_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('ae7a9e4b-7b91-eb25-2ada-4a6d7f344f60', 'cases_modified_user', 'Users', 'users', 'id', 'Cases', 'cases', 'modified_user_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('aea517c9-5a2f-b3a9-fd2a-4a6d7f901c2b', 'cases_created_by', 'Users', 'users', 'id', 'Cases', 'cases', 'created_by', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('aed17038-ceb4-15bc-8aef-4a6d7f4f1762', 'cases_assigned_user', 'Users', 'users', 'id', 'Cases', 'cases', 'assigned_user_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('aefa655a-51bc-79fc-91f4-4a6d7fcce401', 'case_calls', 'Cases', 'cases', 'id', 'Calls', 'calls', 'parent_id', NULL, NULL, NULL, 'one-to-many', 'parent_type', 'Cases', 0, 0),
('af24918e-27db-312a-2c45-4a6d7f74fd4a', 'case_tasks', 'Cases', 'cases', 'id', 'Tasks', 'tasks', 'parent_id', NULL, NULL, NULL, 'one-to-many', 'parent_type', 'Cases', 0, 0),
('af4f0329-7c1f-6d88-e176-4a6d7f479cb0', 'case_notes', 'Cases', 'cases', 'id', 'Notes', 'notes', 'parent_id', NULL, NULL, NULL, 'one-to-many', 'parent_type', 'Cases', 0, 0),
('af7a1db0-ab11-03bf-773d-4a6d7f2c8e64', 'case_meetings', 'Cases', 'cases', 'id', 'Meetings', 'meetings', 'parent_id', NULL, NULL, NULL, 'one-to-many', 'parent_type', 'Cases', 0, 0),
('afa40dc9-3e8e-ec07-4649-4a6d7f21e41e', 'case_emails', 'Cases', 'cases', 'id', 'Emails', 'emails', 'parent_id', NULL, NULL, NULL, 'one-to-many', 'parent_type', 'Cases', 0, 0),
('b5686054-6cc2-e003-89c2-4a6d7f01cd0e', 'notes_modified_user', 'Users', 'users', 'id', 'Notes', 'notes', 'modified_user_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('b595b63c-05af-ff6a-59e8-4a6d7fae3cab', 'notes_created_by', 'Users', 'users', 'id', 'Notes', 'notes', 'created_by', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('c1b0315d-fef0-fee7-00a2-4a6d7f777728', 'calls_modified_user', 'Users', 'users', 'id', 'Calls', 'calls', 'modified_user_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('c1cc174e-6582-4cac-0376-4a6d7f7c3edb', 'calls_created_by', 'Users', 'users', 'id', 'Calls', 'calls', 'created_by', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('c1e7ccde-2a7c-76a1-76c9-4a6d7ffb0725', 'calls_assigned_user', 'Users', 'users', 'id', 'Calls', 'calls', 'assigned_user_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('c20256d7-4462-0ec2-c3fe-4a6d7f0bd597', 'calls_notes', 'Calls', 'calls', 'id', 'Notes', 'notes', 'parent_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('c83ffae2-b903-8e9a-03ed-4a6d7f4dbac1', 'emails_assigned_user', 'Users', 'users', 'id', 'Emails', 'emails', 'assigned_user_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('c865cb76-b6cc-9809-ce61-4a6d7f7596d6', 'emails_modified_user', 'Users', 'users', 'id', 'Emails', 'emails', 'modified_user_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('c88ab519-d4c8-5763-6ff2-4a6d7fd10302', 'emails_created_by', 'Users', 'users', 'id', 'Emails', 'emails', 'created_by', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('c8afab87-7224-84af-8c85-4a6d7f0c46df', 'emails_notes_rel', 'Emails', 'emails', 'id', 'Notes', 'notes', 'parent_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('cf6e8d59-e7c0-0ba0-c644-4a6d7f62b5fa', 'meetings_modified_user', 'Users', 'users', 'id', 'Meetings', 'meetings', 'modified_user_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('cf955265-ba4b-46a8-2865-4a6d7f162464', 'meetings_created_by', 'Users', 'users', 'id', 'Meetings', 'meetings', 'created_by', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('cfbb3a23-e3e7-7051-ba40-4a6d7f67c499', 'meetings_assigned_user', 'Users', 'users', 'id', 'Meetings', 'meetings', 'assigned_user_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('cfe1499c-67be-aab3-b7a0-4a6d7f5de273', 'meetings_notes', 'Meetings', 'meetings', 'id', 'Notes', 'notes', 'parent_id', NULL, NULL, NULL, 'one-to-many', 'parent_type', 'Meetings', 0, 0),
('d91b189b-aa23-c318-bbaf-4a6d7ffeb51b', 'tasks_modified_user', 'Users', 'users', 'id', 'Tasks', 'tasks', 'modified_user_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('d94018a1-3554-ad30-290b-4a6d7f42c0d6', 'tasks_created_by', 'Users', 'users', 'id', 'Tasks', 'tasks', 'created_by', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('d96467d8-7e0c-dc4c-008e-4a6d7f15eb03', 'tasks_assigned_user', 'Users', 'users', 'id', 'Tasks', 'tasks', 'assigned_user_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('ddde442c-3323-7037-d1ec-4a6d7f692bf2', 'user_direct_reports', 'Users', 'users', 'id', 'Users', 'users', 'reports_to_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('5b990a4f-0427-24b7-6867-4a6d7fa724f7', 'bugs_modified_user', 'Users', 'users', 'id', 'Bugs', 'bugs', 'modified_user_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('5d69072e-e4a2-06ba-d33c-4a6d7f3e2b0f', 'bugs_created_by', 'Users', 'users', 'id', 'Bugs', 'bugs', 'created_by', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('5f1d0b4c-9675-9286-1526-4a6d7f3ae1c3', 'bugs_assigned_user', 'Users', 'users', 'id', 'Bugs', 'bugs', 'assigned_user_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('60d804f4-7046-673a-2c7a-4a6d7ff93fe8', 'bug_tasks', 'Bugs', 'bugs', 'id', 'Tasks', 'tasks', 'parent_id', NULL, NULL, NULL, 'one-to-many', 'parent_type', 'Bugs', 0, 0),
('62920cae-c4d2-f455-61b8-4a6d7f6f58b4', 'bug_meetings', 'Bugs', 'bugs', 'id', 'Meetings', 'meetings', 'parent_id', NULL, NULL, NULL, 'one-to-many', 'parent_type', 'Bugs', 0, 0),
('64510449-0573-699c-d3e4-4a6d7fcbb841', 'bug_calls', 'Bugs', 'bugs', 'id', 'Calls', 'calls', 'parent_id', NULL, NULL, NULL, 'one-to-many', 'parent_type', 'Bugs', 0, 0),
('66100de3-5cef-f90f-5ac5-4a6d7f5141ae', 'bug_emails', 'Bugs', 'bugs', 'id', 'Emails', 'emails', 'parent_id', NULL, NULL, NULL, 'one-to-many', 'parent_type', 'Bugs', 0, 0),
('67e10b6e-9365-2446-0039-4a6d7ff16de6', 'bug_notes', 'Bugs', 'bugs', 'id', 'Notes', 'notes', 'parent_id', NULL, NULL, NULL, 'one-to-many', 'parent_type', 'Bugs', 0, 0),
('699f0e18-61a8-a21e-a027-4a6d7f82d15e', 'bugs_release', 'Releases', 'releases', 'id', 'Bugs', 'bugs', 'found_in_release', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('6b57023c-347f-6aa7-669a-4a6d7f4f7ac2', 'bugs_fixed_in_release', 'Releases', 'releases', 'id', 'Bugs', 'bugs', 'fixed_in_release', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('1bd05203-f080-e509-e95f-4a6d7fb50ddf', 'feeds_assigned_user', 'Users', 'users', 'id', 'Feeds', 'feeds', 'assigned_user_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('1bf1031d-ccbb-c4fa-4225-4a6d7fd94369', 'feeds_modified_user', 'Users', 'users', 'id', 'Feeds', 'feeds', 'modified_user_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('1c16cd22-40a5-be77-751e-4a6d7f9b7f2d', 'feeds_created_by', 'Users', 'users', 'id', 'Feeds', 'feeds', 'created_by', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('20b4ce68-e8f7-c070-80ad-4a6d7f970df2', 'projects_notes', 'Project', 'project', 'id', 'Notes', 'notes', 'parent_id', NULL, NULL, NULL, 'one-to-many', 'parent_type', 'Project', 0, 0),
('20d95c2a-713a-c9a4-e829-4a6d7f85cf7b', 'projects_tasks', 'Project', 'project', 'id', 'Tasks', 'tasks', 'parent_id', NULL, NULL, NULL, 'one-to-many', 'parent_type', 'Project', 0, 0),
('20fab335-8f2d-6935-276f-4a6d7f5b1d88', 'projects_meetings', 'Project', 'project', 'id', 'Meetings', 'meetings', 'parent_id', NULL, NULL, NULL, 'one-to-many', 'parent_type', 'Project', 0, 0),
('211dd14e-e0e2-c2db-f286-4a6d7f786d38', 'projects_calls', 'Project', 'project', 'id', 'Calls', 'calls', 'parent_id', NULL, NULL, NULL, 'one-to-many', 'parent_type', 'Project', 0, 0),
('213ffb8d-6a6b-4d4d-3584-4a6d7fd3e848', 'projects_emails', 'Project', 'project', 'id', 'Emails', 'emails', 'parent_id', NULL, NULL, NULL, 'one-to-many', 'parent_type', 'Project', 0, 0),
('215f7ac9-2271-cae3-55e3-4a6d7f4f276e', 'projects_project_tasks', 'Project', 'project', 'id', 'ProjectTask', 'project_task', 'project_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('217e9614-19b0-d33f-f1af-4a6d7fecd3c6', 'projects_assigned_user', 'Users', 'users', 'id', 'Project', 'project', 'assigned_user_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('219d872c-7d48-e47b-2689-4a6d7f76a1cc', 'projects_modified_user', 'Users', 'users', 'id', 'Project', 'project', 'modified_user_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('21bc6a5e-3c8b-23de-0c93-4a6d7f71b6ef', 'projects_created_by', 'Users', 'users', 'id', 'Project', 'project', 'created_by', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('2b70f754-5c2a-5331-38f8-4a6d7f683f70', 'project_tasks_notes', 'ProjectTask', 'project_task', 'id', 'Notes', 'notes', 'parent_id', NULL, NULL, NULL, 'one-to-many', 'parent_type', 'ProjectTask', 0, 0),
('2ba49f6a-2dac-7e6d-8327-4a6d7f26216c', 'project_tasks_tasks', 'ProjectTask', 'project_task', 'id', 'Tasks', 'tasks', 'parent_id', NULL, NULL, NULL, 'one-to-many', 'parent_type', 'ProjectTask', 0, 0),
('2bd7f56d-3ffa-bf93-302c-4a6d7fd7f81a', 'project_tasks_meetings', 'ProjectTask', 'project_task', 'id', 'Meetings', 'meetings', 'parent_id', NULL, NULL, NULL, 'one-to-many', 'parent_type', 'ProjectTask', 0, 0),
('2c0bd498-d355-28a1-51eb-4a6d7f7880fd', 'project_tasks_calls', 'ProjectTask', 'project_task', 'id', 'Calls', 'calls', 'parent_id', NULL, NULL, NULL, 'one-to-many', 'parent_type', 'ProjectTask', 0, 0),
('2c3e9183-9340-b732-08e6-4a6d7fb74507', 'project_tasks_emails', 'ProjectTask', 'project_task', 'id', 'Emails', 'emails', 'parent_id', NULL, NULL, NULL, 'one-to-many', 'parent_type', 'ProjectTask', 0, 0),
('2c717d8b-33ae-80c8-0d48-4a6d7f4e10c0', 'project_tasks_assigned_user', 'Users', 'users', 'id', 'ProjectTask', 'project_task', 'assigned_user_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('2ca58b30-3617-e90c-f5f5-4a6d7fb182b7', 'project_tasks_modified_user', 'Users', 'users', 'id', 'ProjectTask', 'project_task', 'modified_user_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('2cd855e7-50b7-5c6a-90c8-4a6d7f3dc1cf', 'project_tasks_created_by', 'Users', 'users', 'id', 'ProjectTask', 'project_task', 'created_by', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('360457a1-a3f6-a76c-64a4-4a6d7f53f310', 'email_template_email_marketings', 'EmailTemplates', 'email_templates', 'id', 'EmailMarketing', 'email_marketing', 'template_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('410f26e4-b9c3-6d7e-1593-4a6d7f39c7a3', 'campaigns_modified_user', 'Users', 'users', 'id', 'Campaigns', 'campaigns', 'modified_user_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('413d013b-3ba3-b15e-e10f-4a6d7ffc6ef2', 'campaigns_created_by', 'Users', 'users', 'id', 'Campaigns', 'campaigns', 'created_by', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('416b2888-2d7f-92c8-cd62-4a6d7f07b8f0', 'campaigns_assigned_user', 'Users', 'users', 'id', 'Campaigns', 'campaigns', 'assigned_user_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('4198e2b0-39a8-b00e-e590-4a6d7fa46416', 'campaign_accounts', 'Campaigns', 'campaigns', 'id', 'Accounts', 'accounts', 'campaign_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('41c6ad04-aae2-fe91-0af8-4a6d7fe16977', 'campaign_contacts', 'Campaigns', 'campaigns', 'id', 'Contacts', 'contacts', 'campaign_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('41f4450c-0c26-3e19-3882-4a6d7f666c7d', 'campaign_leads', 'Campaigns', 'campaigns', 'id', 'Leads', 'leads', 'campaign_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('422222ab-8500-b0a2-ace2-4a6d7fb94428', 'campaign_prospects', 'Campaigns', 'campaigns', 'id', 'Prospects', 'prospects', 'campaign_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('42511156-c1b5-c2e5-c08b-4a6d7f98aaab', 'campaign_opportunities', 'Campaigns', 'campaigns', 'id', 'Opportunities', 'opportunities', 'campaign_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('427f5271-1658-8072-5886-4a6d7f0597ff', 'campaign_email_marketing', 'Campaigns', 'campaigns', 'id', 'EmailMarketing', 'email_marketing', 'campaign_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('42ad8c20-5d55-1c89-99b4-4a6d7fbf2730', 'campaign_emailman', 'Campaigns', 'campaigns', 'id', 'EmailMan', 'emailman', 'campaign_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('42dce6e1-a669-d34f-8e9a-4a6d7f115e53', 'campaign_campaignlog', 'Campaigns', 'campaigns', 'id', 'CampaignLog', 'campaign_log', 'campaign_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('430b3f98-bc76-74cc-08ed-4a6d7f083457', 'campaign_assigned_user', 'Users', 'users', 'id', 'Campaigns', 'campaigns', 'assigned_user_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('4339e9c5-e659-d4f3-81aa-4a6d7f8cdf13', 'campaign_modified_user', 'Users', 'users', 'id', 'Campaigns', 'campaigns', 'modified_user_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('52982259-8d52-304f-e972-4a6d7f931fe3', 'prospects_modified_user', 'Users', 'users', 'id', 'Prospects', 'prospects', 'modified_user_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('52c89f9b-1233-685b-907e-4a6d7fcac022', 'prospects_created_by', 'Users', 'users', 'id', 'Prospects', 'prospects', 'created_by', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('52fac604-e1ec-6571-4706-4a6d7f066461', 'prospects_assigned_user', 'Users', 'users', 'id', 'Prospects', 'prospects', 'assigned_user_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('532af6aa-5d19-38be-1041-4a6d7f6fd76b', 'prospects_email_addresses_primary', 'Prospects', 'prospects', 'id', 'EmailAddresses', 'email_addresses', 'id', 'email_addr_bean_rel', 'bean_id', 'email_address_id', 'many-to-many', NULL, NULL, 0, 0),
('535bfd6a-931a-0e81-c4e6-4a6d7fbfadf4', 'prospect_campaign_log', 'Prospects', 'prospects', 'id', 'CampaignLog', 'campaign_log', 'target_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('5b2213a6-7cbe-c6cb-f419-4a6d7f44e978', 'documents_modified_user', 'Users', 'users', 'id', 'Documents', 'documents', 'modified_user_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('5b52c46a-cb93-a3ca-0242-4a6d7fa14cd1', 'documents_created_by', 'Users', 'users', 'id', 'Documents', 'documents', 'created_by', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('5b836e2a-e7fe-1103-11b7-4a6d7f75e028', 'document_revisions', 'Documents', 'documents', 'id', 'Documents', 'document_revisions', 'document_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('5f3cc71e-cc5a-714e-3a4f-4a6d7f7396fd', 'revisions_created_by', 'Users', 'users', 'id', 'DocumentRevisions', 'document_revisions', 'created_by', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('65655967-db98-ae35-33a9-4a6d7f62af6e', 'schedulers_created_by_rel', 'Users', 'users', 'id', 'Schedulers', 'schedulers', 'created_by', NULL, NULL, NULL, 'one-to-one', NULL, NULL, 0, 0),
('6598ddbc-1a18-0279-ebb8-4a6d7fff3567', 'schedulers_modified_user_id_rel', 'Users', 'users', 'id', 'Schedulers', 'schedulers', 'modified_user_id', NULL, NULL, NULL, 'one-to-one', NULL, NULL, 0, 0),
('65c8f553-c134-501e-a753-4a6d7f058498', 'schedulers_jobs_rel', 'Schedulers', 'schedulers', 'id', 'SchedulersJobs', 'schedulers_times', 'scheduler_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('6b12de15-0bef-97d7-843e-4a6d7fb1d32e', 'inbound_email_created_by', 'Users', 'users', 'id', 'InboundEmail', 'inbound_email', 'created_by', NULL, NULL, NULL, 'one-to-one', NULL, NULL, 0, 0),
('6b3fb939-c5ae-4985-5fb4-4a6d7fc276e6', 'inbound_email_modified_user_id', 'Users', 'users', 'id', 'InboundEmail', 'inbound_email', 'modified_user_id', NULL, NULL, NULL, 'one-to-one', NULL, NULL, 0, 0),
('6e71e2cb-bfde-238c-dc26-4a6d7fa9f825', 'campaignlog_contact', 'CampaignLog', 'campaign_log', 'related_id', 'Contacts', 'contacts', 'id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('6ea04e71-dc8a-9e57-cadc-4a6d7f69fd90', 'campaignlog_lead', 'CampaignLog', 'campaign_log', 'related_id', 'Leads', 'leads', 'id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('725f7468-ae25-ad7b-592e-4a6d7f298f9d', 'dashboards_assigned_user', 'Users', 'users', 'id', 'Dashboard', 'dashboards', 'assigned_user_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('72a503e2-1a30-8a76-f812-4a6d7fd49804', 'dashboards_modified_user', 'Users', 'users', 'id', 'Dashboard', 'dashboards', 'modified_user_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('72e64b60-bca2-a116-ab40-4a6d7f45e7db', 'dashboards_created_by', 'Users', 'users', 'id', 'Dashboard', 'dashboards', 'created_by', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('7674af28-802e-35cf-c5e6-4a6d7f970f51', 'campaign_campaigntrakers', 'Campaigns', 'campaigns', 'id', 'CampaignTrackers', 'campaign_trkrs', 'campaign_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('7a56ebaa-85a0-0360-1da1-4a6d7f0961a0', 'saved_search_assigned_user', 'Users', 'users', 'id', 'SavedSearch', 'saved_search', 'assigned_user_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('86075eed-6242-e132-e6a2-4a6d7ffd1884', 'sugarfeed_modified_user', 'Users', 'users', 'id', 'SugarFeed', 'sugarfeed', 'modified_user_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('8719290d-ba13-962a-60e7-4a6d7f041798', 'sugarfeed_created_by', 'Users', 'users', 'id', 'SugarFeed', 'sugarfeed', 'created_by', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('8756c4df-e97e-52c0-279b-4a6d7fe4bd70', 'sugarfeed_assigned_user', 'Users', 'users', 'id', 'SugarFeed', 'sugarfeed', 'assigned_user_id', NULL, NULL, NULL, 'one-to-many', NULL, NULL, 0, 0),
('92d83b60-8aa0-670b-0358-4a6d7f4c2972', 'accounts_bugs', 'Accounts', 'accounts', 'id', 'Bugs', 'bugs', 'id', 'accounts_bugs', 'account_id', 'bug_id', 'many-to-many', NULL, NULL, 0, 0),
('9679221c-463d-262f-32d2-4a6d7f392536', 'accounts_contacts', 'Accounts', 'accounts', 'id', 'Contacts', 'contacts', 'id', 'accounts_contacts', 'account_id', 'contact_id', 'many-to-many', NULL, NULL, 0, 0),
('9850d74f-8ef3-cba5-e987-4a6d7f6f31e4', 'accounts_opportunities', 'Accounts', 'accounts', 'id', 'Opportunities', 'opportunities', 'id', 'accounts_opportunities', 'account_id', 'opportunity_id', 'many-to-many', NULL, NULL, 0, 0),
('99e98ca0-ce47-5dd6-bc5c-4a6d7f66d62c', 'acl_roles_actions', 'ACLRoles', 'acl_roles', 'id', 'ACLActions', 'acl_actions', 'id', 'acl_roles_actions', 'role_id', 'action_id', 'many-to-many', NULL, NULL, 0, 0),
('9b5cbd95-5292-4459-c135-4a6d7ff1e2bd', 'acl_roles_users', 'ACLRoles', 'acl_roles', 'id', 'Users', 'users', 'id', 'acl_roles_users', 'role_id', 'user_id', 'many-to-many', NULL, NULL, 0, 0),
('9cc65127-0c7a-2d38-26d9-4a6d7f3fa0d7', 'calls_contacts', 'Calls', 'calls', 'id', 'Contacts', 'contacts', 'id', 'calls_contacts', 'call_id', 'contact_id', 'many-to-many', NULL, NULL, 0, 0),
('9e3ffac3-e6f7-868f-345c-4a6d7f420497', 'calls_leads', 'Calls', 'calls', 'id', 'Leads', 'leads', 'id', 'calls_leads', 'call_id', 'lead_id', 'many-to-many', NULL, NULL, 0, 0),
('9fd6d662-ca4d-0544-67a6-4a6d7f532d4c', 'calls_users', 'Calls', 'calls', 'id', 'Users', 'users', 'id', 'calls_users', 'call_id', 'user_id', 'many-to-many', NULL, NULL, 0, 0),
('a12fce81-c4d0-d945-9e91-4a6d7f6515a2', 'cases_bugs', 'Cases', 'cases', 'id', 'Bugs', 'bugs', 'id', 'cases_bugs', 'case_id', 'bug_id', 'many-to-many', NULL, NULL, 0, 0),
('a2ab43f5-9a5b-328b-a2fe-4a6d7f8f71aa', 'contacts_bugs', 'Contacts', 'contacts', 'id', 'Bugs', 'bugs', 'id', 'contacts_bugs', 'contact_id', 'bug_id', 'many-to-many', NULL, NULL, 0, 0),
('a4533490-1eee-cd13-1c3b-4a6d7f610222', 'contacts_cases', 'Contacts', 'contacts', 'id', 'Cases', 'cases', 'id', 'contacts_cases', 'contact_id', 'case_id', 'many-to-many', NULL, NULL, 0, 0),
('a5a2a278-c0d3-124e-a555-4a6d7fd2a227', 'contacts_users', 'Contacts', 'contacts', 'id', 'Users', 'users', 'id', 'contacts_users', 'contact_id', 'user_id', 'many-to-many', NULL, NULL, 0, 0),
('a7dc04bf-2c4a-342d-8408-4a6d7f85a323', 'accounts_email_addresses', 'Accounts', 'accounts', 'id', 'EmailAddresses', 'email_addresses', 'id', 'email_addr_bean_rel', 'bean_id', 'email_address_id', 'many-to-many', 'bean_module', 'Accounts', 0, 0),
('a8099c11-a1ec-7972-108e-4a6d7f918383', 'contacts_email_addresses', 'Contacts', 'contacts', 'id', 'EmailAddresses', 'email_addresses', 'id', 'email_addr_bean_rel', 'bean_id', 'email_address_id', 'many-to-many', 'bean_module', 'Contacts', 0, 0),
('a83788d8-0c10-62af-83cd-4a6d7fb9e343', 'leads_email_addresses', 'Leads', 'leads', 'id', 'EmailAddresses', 'email_addresses', 'id', 'email_addr_bean_rel', 'bean_id', 'email_address_id', 'many-to-many', 'bean_module', 'Leads', 0, 0),
('a8651aa7-cb77-7464-4690-4a6d7f84aa85', 'prospects_email_addresses', 'Prospects', 'prospects', 'id', 'EmailAddresses', 'email_addresses', 'id', 'email_addr_bean_rel', 'bean_id', 'email_address_id', 'many-to-many', 'bean_module', 'Prospects', 0, 0),
('a8968783-7bb8-db6b-ab71-4a6d7f09c251', 'users_email_addresses', 'Users', 'users', 'id', 'EmailAddresses', 'email_addresses', 'id', 'email_addr_bean_rel', 'bean_id', 'email_address_id', 'many-to-many', 'bean_module', 'Users', 0, 0),
('a8b73cb8-1d85-17aa-cd32-4a6d7fa3c6cb', 'users_email_addresses_primary', 'Users', 'users', 'id', 'EmailAddresses', 'email_addresses', 'id', 'email_addr_bean_rel', 'bean_id', 'email_address_id', 'many-to-many', 'primary_address', '1', 0, 0),
('ad0a41d3-3239-7090-1019-4a6d7ff37abd', 'email_marketing_prospect_lists', 'EmailMarketing', 'email_marketing', 'id', 'ProspectLists', 'prospect_lists', 'id', 'email_marketing_prospect_lists', 'email_marketing_id', 'prospect_list_id', 'many-to-many', NULL, NULL, 0, 0),
('aed8021f-adb4-9460-d6c5-4a6d7fe2934a', 'emails_accounts_rel', 'Emails', 'emails', 'id', 'Accounts', 'accounts', 'id', 'emails_beans', 'email_id', 'bean_id', 'many-to-many', 'bean_module', 'Accounts', 0, 0),
('af0c91cd-36a6-a405-71cb-4a6d7fea147f', 'emails_bugs_rel', 'Emails', 'emails', 'id', 'Bugs', 'bugs', 'id', 'emails_beans', 'email_id', 'bean_id', 'many-to-many', 'bean_module', 'Bugs', 0, 0),
('af413655-2f84-c649-60d2-4a6d7f02120b', 'emails_cases_rel', 'Emails', 'emails', 'id', 'Cases', 'cases', 'id', 'emails_beans', 'email_id', 'bean_id', 'many-to-many', 'bean_module', 'Cases', 0, 0),
('af766686-2dcb-f6aa-e369-4a6d7fb45460', 'emails_contacts_rel', 'Emails', 'emails', 'id', 'Contacts', 'contacts', 'id', 'emails_beans', 'email_id', 'bean_id', 'many-to-many', 'bean_module', 'Contacts', 0, 0),
('afab30c4-9fdb-71b5-a160-4a6d7fcfe51b', 'emails_leads_rel', 'Emails', 'emails', 'id', 'Leads', 'leads', 'id', 'emails_beans', 'email_id', 'bean_id', 'many-to-many', 'bean_module', 'Leads', 0, 0),
('afdd53e4-9e9f-ca77-a87d-4a6d7fbe0fc7', 'emails_opportunities_rel', 'Emails', 'emails', 'id', 'Opportunities', 'opportunities', 'id', 'emails_beans', 'email_id', 'bean_id', 'many-to-many', 'bean_module', 'Opportunities', 0, 0),
('b00e070a-8c90-1569-71f5-4a6d7f30d49f', 'emails_tasks_rel', 'Emails', 'emails', 'id', 'Tasks', 'tasks', 'id', 'emails_beans', 'email_id', 'bean_id', 'many-to-many', 'bean_module', 'Tasks', 0, 0),
('b03ba24e-22ad-add6-16af-4a6d7f49d344', 'emails_users_rel', 'Emails', 'emails', 'id', 'Users', 'users', 'id', 'emails_beans', 'email_id', 'bean_id', 'many-to-many', 'bean_module', 'Users', 0, 0),
('b06a061c-2d41-9b54-c92b-4a6d7f0456f3', 'emails_project_task_rel', 'Emails', 'emails', 'id', 'ProjectTask', 'project_task', 'id', 'emails_beans', 'email_id', 'bean_id', 'many-to-many', 'bean_module', 'ProjectTask', 0, 0),
('b097b39b-dbcc-fb0d-e476-4a6d7f11012f', 'emails_projects_rel', 'Emails', 'emails', 'id', 'Project', 'project', 'id', 'emails_beans', 'email_id', 'bean_id', 'many-to-many', 'bean_module', 'Project', 0, 0),
('b0c5ce39-dcaa-b77f-b603-4a6d7f0fa1fb', 'emails_prospects_rel', 'Emails', 'emails', 'id', 'Prospects', 'prospects', 'id', 'emails_beans', 'email_id', 'bean_id', 'many-to-many', 'bean_module', 'Prospects', 0, 0),
('b885963e-8d81-69d8-87d1-4a6d7f0bc753', 'leads_documents', 'Leads', 'leads', 'id', 'Documents', 'documents', 'id', 'linked_documents', 'parent_id', 'document_id', 'many-to-many', 'parent_type', 'Leads', 0, 0),
('ba703432-c19f-a056-7afc-4a6d7f9b2ec2', 'meetings_contacts', 'Meetings', 'meetings', 'id', 'Contacts', 'contacts', 'id', 'meetings_contacts', 'meeting_id', 'contact_id', 'many-to-many', NULL, NULL, 0, 0),
('bc3ee039-4a02-846e-1d83-4a6d7f6403d0', 'meetings_leads', 'Meetings', 'meetings', 'id', 'Leads', 'leads', 'id', 'meetings_leads', 'meeting_id', 'lead_id', 'many-to-many', NULL, NULL, 0, 0),
('be8c892f-ef09-1fcc-64b8-4a6d7ff0ee0e', 'meetings_users', 'Meetings', 'meetings', 'id', 'Users', 'users', 'id', 'meetings_users', 'meeting_id', 'user_id', 'many-to-many', NULL, NULL, 0, 0),
('c090e876-aec9-67dc-efc1-4a6d7f51290e', 'opportunities_contacts', 'Opportunities', 'opportunities', 'id', 'Contacts', 'contacts', 'id', 'opportunities_contacts', 'opportunity_id', 'contact_id', 'many-to-many', NULL, NULL, 0, 0),
('c316b58f-09e8-f3aa-4ee7-4a6d7f96d0d5', 'projects_accounts', 'Project', 'project', 'id', 'Accounts', 'accounts', 'id', 'projects_accounts', 'project_id', 'account_id', 'many-to-many', NULL, NULL, 0, 0),
('c5bf4a2c-c21d-46b5-2e71-4a6d7f959290', 'projects_bugs', 'Project', 'project', 'id', 'Bugs', 'bugs', 'id', 'projects_bugs', 'project_id', 'bug_id', 'many-to-many', NULL, NULL, 0, 0),
('c8dd7318-6cdc-6a16-3e04-4a6d7f879e7d', 'projects_cases', 'Project', 'project', 'id', 'Cases', 'cases', 'id', 'projects_cases', 'project_id', 'case_id', 'many-to-many', NULL, NULL, 0, 0),
('cb72cce2-d3ca-e778-4d31-4a6d7fe96190', 'projects_contacts', 'Project', 'project', 'id', 'Contacts', 'contacts', 'id', 'projects_contacts', 'project_id', 'contact_id', 'many-to-many', NULL, NULL, 0, 0),
('cdf3f72b-1cea-70fc-d98a-4a6d7f92973a', 'projects_opportunities', 'Project', 'project', 'id', 'Opportunities', 'opportunities', 'id', 'projects_opportunities', 'project_id', 'opportunity_id', 'many-to-many', NULL, NULL, 0, 0),
('d260f6d2-e934-2127-e7de-4a6d7fd04d6d', 'prospect_list_campaigns', 'ProspectLists', 'prospect_lists', 'id', 'Campaigns', 'campaigns', 'id', 'prospect_list_campaigns', 'prospect_list_id', 'campaign_id', 'many-to-many', NULL, NULL, 0, 0),
('d4f047ac-417a-9c63-1b0c-4a6d7f5dc972', 'prospect_list_contacts', 'ProspectLists', 'prospect_lists', 'id', 'Contacts', 'contacts', 'id', 'prospect_lists_prospects', 'prospect_list_id', 'related_id', 'many-to-many', 'related_type', 'Contacts', 0, 0),
('d551e3d0-0df4-b5fa-0877-4a6d7fe8d6c7', 'prospect_list_prospects', 'ProspectLists', 'prospect_lists', 'id', 'Prospects', 'prospects', 'id', 'prospect_lists_prospects', 'prospect_list_id', 'related_id', 'many-to-many', 'related_type', 'Prospects', 0, 0),
('d5b03e45-723b-e662-a4bf-4a6d7fe1f577', 'prospect_list_leads', 'ProspectLists', 'prospect_lists', 'id', 'Leads', 'leads', 'id', 'prospect_lists_prospects', 'prospect_list_id', 'related_id', 'many-to-many', 'related_type', 'Leads', 0, 0),
('d61092bc-ae75-b32a-1150-4a6d7fe27205', 'prospect_list_users', 'ProspectLists', 'prospect_lists', 'id', 'Users', 'users', 'id', 'prospect_lists_prospects', 'prospect_list_id', 'related_id', 'many-to-many', 'related_type', 'Users', 0, 0),
('daabf8ad-814e-e701-51aa-4a6d7f2b88f0', 'roles_users', 'Roles', 'roles', 'id', 'Users', 'users', 'id', 'roles_users', 'role_id', 'user_id', 'many-to-many', NULL, NULL, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `releases`
--

CREATE TABLE IF NOT EXISTS `releases` (
  `id` char(36) NOT NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  `date_entered` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  `modified_user_id` char(36) NOT NULL,
  `created_by` char(36) default NULL,
  `name` varchar(50) NOT NULL,
  `list_order` int(4) default NULL,
  `status` varchar(25) default NULL,
  PRIMARY KEY  (`id`),
  KEY `idx_releases` (`name`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `releases`
--


-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE IF NOT EXISTS `roles` (
  `id` char(36) NOT NULL,
  `date_entered` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  `modified_user_id` char(36) NOT NULL,
  `created_by` char(36) default NULL,
  `name` varchar(150) default NULL,
  `description` text,
  `modules` text,
  `deleted` tinyint(1) default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_role_id_del` (`id`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `roles`
--


-- --------------------------------------------------------

--
-- Table structure for table `roles_modules`
--

CREATE TABLE IF NOT EXISTS `roles_modules` (
  `id` varchar(36) NOT NULL,
  `role_id` varchar(36) default NULL,
  `module_id` varchar(36) default NULL,
  `allow` tinyint(1) default '0',
  `date_modified` datetime default NULL,
  `deleted` tinyint(1) default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_role_id` (`role_id`),
  KEY `idx_module_id` (`module_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `roles_modules`
--


-- --------------------------------------------------------

--
-- Table structure for table `roles_users`
--

CREATE TABLE IF NOT EXISTS `roles_users` (
  `id` varchar(36) NOT NULL,
  `role_id` varchar(36) default NULL,
  `user_id` varchar(36) default NULL,
  `date_modified` datetime default NULL,
  `deleted` tinyint(1) default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_ru_role_id` (`role_id`),
  KEY `idx_ru_user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `roles_users`
--


-- --------------------------------------------------------

--
-- Table structure for table `saved_search`
--

CREATE TABLE IF NOT EXISTS `saved_search` (
  `id` char(36) NOT NULL,
  `name` varchar(150) default NULL,
  `search_module` varchar(150) default NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  `date_entered` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  `assigned_user_id` char(36) default NULL,
  `contents` text,
  `description` text,
  PRIMARY KEY  (`id`),
  KEY `idx_desc` (`name`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `saved_search`
--


-- --------------------------------------------------------

--
-- Table structure for table `schedulers`
--

CREATE TABLE IF NOT EXISTS `schedulers` (
  `id` varchar(36) NOT NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  `date_entered` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  `created_by` char(36) default NULL,
  `modified_user_id` char(36) default NULL,
  `name` varchar(255) NOT NULL,
  `job` varchar(255) NOT NULL,
  `date_time_start` datetime NOT NULL,
  `date_time_end` datetime default NULL,
  `job_interval` varchar(100) NOT NULL,
  `time_from` time default NULL,
  `time_to` time default NULL,
  `last_run` datetime default NULL,
  `status` varchar(25) default NULL,
  `catch_up` tinyint(1) default '1',
  PRIMARY KEY  (`id`),
  KEY `idx_schedule` (`date_time_start`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `schedulers`
--

INSERT INTO `schedulers` (`id`, `deleted`, `date_entered`, `date_modified`, `created_by`, `modified_user_id`, `name`, `job`, `date_time_start`, `date_time_end`, `job_interval`, `time_from`, `time_to`, `last_run`, `status`, `catch_up`) VALUES
('e508d91f-a2d5-92f0-83bb-4a6d7f31ba15', 0, '2009-07-27 10:22:39', '2009-07-27 10:22:39', NULL, '1', 'Prune tracker tables', 'function::trimTracker', '2005-01-01 15:30:00', '2020-12-31 21:59:00', '0::2::1::*::*', NULL, NULL, NULL, 'Active', 1),
('e766e010-2241-5890-6336-4a6d7f2da460', 0, '2009-07-27 10:22:39', '2009-07-27 10:22:39', NULL, '1', 'Check Inbound Mailboxes', 'function::pollMonitoredInboxes', '2005-01-01 16:45:00', '2020-12-31 21:59:00', '*::*::*::*::*', NULL, NULL, NULL, 'Active', 0),
('e9aa149c-0975-a4db-8bb4-4a6d7fd6e60b', 0, '2009-07-27 10:22:39', '2009-07-27 10:22:39', NULL, '1', 'Run Nightly Process Bounced Campaign Emails', 'function::pollMonitoredInboxesForBouncedCampaignEmails', '2005-01-01 17:45:00', '2020-12-31 21:59:00', '0::2-6::*::*::*', NULL, NULL, NULL, 'Active', 1),
('ebecbb53-5a63-e836-7882-4a6d7f23e91e', 0, '2009-07-27 10:22:39', '2009-07-27 10:22:39', NULL, '1', 'Run Nightly Mass Email Campaigns', 'function::runMassEmailCampaign', '2005-01-01 12:45:00', '2020-12-31 21:59:00', '0::2-6::*::*::*', NULL, NULL, NULL, 'Active', 1),
('ee2f9ede-ddaf-1839-ef01-4a6d7f2c0f1e', 0, '2009-07-27 10:22:39', '2009-07-27 10:22:39', NULL, '1', 'Prune Database on 1st of Month', 'function::pruneDatabase', '2005-01-01 10:30:00', '2020-12-31 21:59:00', '0::4::1::*::*', NULL, NULL, NULL, 'Inactive', 0);

-- --------------------------------------------------------

--
-- Table structure for table `schedulers_times`
--

CREATE TABLE IF NOT EXISTS `schedulers_times` (
  `id` char(36) NOT NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  `date_entered` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  `scheduler_id` char(36) NOT NULL,
  `execute_time` datetime NOT NULL,
  `status` varchar(25) NOT NULL default 'ready',
  PRIMARY KEY  (`id`),
  KEY `idx_scheduler_id` (`scheduler_id`,`execute_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `schedulers_times`
--


-- --------------------------------------------------------

--
-- Table structure for table `sugarfeed`
--

CREATE TABLE IF NOT EXISTS `sugarfeed` (
  `id` char(36) NOT NULL,
  `name` varchar(255) default NULL,
  `date_entered` datetime default NULL,
  `date_modified` datetime default NULL,
  `modified_user_id` char(36) default NULL,
  `created_by` char(36) default NULL,
  `description` varchar(255) default NULL,
  `deleted` tinyint(1) default '0',
  `assigned_user_id` char(36) default NULL,
  `related_module` varchar(100) default NULL,
  `related_id` char(36) default NULL,
  `link_url` varchar(255) default NULL,
  `link_type` varchar(30) default NULL,
  PRIMARY KEY  (`id`),
  KEY `sgrfeed_date` (`date_entered`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sugarfeed`
--


-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE IF NOT EXISTS `tasks` (
  `id` char(36) NOT NULL,
  `name` varchar(50) default NULL,
  `date_entered` datetime default NULL,
  `date_modified` datetime default NULL,
  `modified_user_id` char(36) default NULL,
  `created_by` char(36) default NULL,
  `description` text,
  `deleted` tinyint(1) default '0',
  `assigned_user_id` char(36) default NULL,
  `status` varchar(25) default NULL,
  `date_due_flag` tinyint(1) default '1',
  `date_due` datetime default NULL,
  `date_start_flag` tinyint(1) default '1',
  `date_start` datetime default NULL,
  `parent_type` varchar(25) default NULL,
  `parent_id` char(36) default NULL,
  `contact_id` char(36) default NULL,
  `priority` varchar(25) default NULL,
  PRIMARY KEY  (`id`),
  KEY `idx_tsk_name` (`name`),
  KEY `idx_task_con_del` (`contact_id`,`deleted`),
  KEY `idx_task_par_del` (`parent_id`,`parent_type`,`deleted`),
  KEY `idx_task_assigned` (`assigned_user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tasks`
--


-- --------------------------------------------------------

--
-- Table structure for table `tracker`
--

CREATE TABLE IF NOT EXISTS `tracker` (
  `id` int(11) NOT NULL auto_increment,
  `monitor_id` char(36) NOT NULL,
  `user_id` varchar(36) default NULL,
  `module_name` varchar(255) default NULL,
  `item_id` varchar(36) default NULL,
  `item_summary` varchar(255) default NULL,
  `date_modified` datetime default NULL,
  `action` varchar(255) default NULL,
  `session_id` varchar(36) default NULL,
  `visible` tinyint(1) default '0',
  `deleted` tinyint(1) default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_tracker_iid` (`item_id`),
  KEY `idx_tracker_userid_vis_id` (`user_id`,`visible`,`id`),
  KEY `idx_tracker_userid_itemid_vis` (`user_id`,`item_id`,`visible`),
  KEY `idx_tracker_monitor_id` (`monitor_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `tracker`
--

INSERT INTO `tracker` (`id`, `monitor_id`, `user_id`, `module_name`, `item_id`, `item_summary`, `date_modified`, `action`, `session_id`, `visible`, `deleted`) VALUES
(1, '81cf82cb-a5b1-84a2-f72d-4a6d7f021a49', NULL, 'Users', NULL, NULL, '2009-07-27 10:22:45', 'login', '78a3f0161dd933036b69c7f41f719d79', 0, 0),
(2, '79910923-14e3-0236-b71c-4a6d807b5d77', NULL, 'Users', NULL, NULL, '2009-07-27 10:22:56', 'login', '78a3f0161dd933036b69c7f41f719d79', 0, 0),
(3, '583e7a01-2076-6ec9-422f-4a6d80b6c675', NULL, 'Users', NULL, NULL, '2009-07-27 10:23:02', 'authenticate', '78a3f0161dd933036b69c7f41f719d79', 0, 0),
(4, '7e765a78-a9cb-7e00-0b34-4a6d8088bbd8', NULL, 'Users', NULL, NULL, '2009-07-27 10:23:02', 'login', '78a3f0161dd933036b69c7f41f719d79', 0, 0),
(5, '8744e685-c4eb-c663-a759-4a6d803301e6', NULL, 'Users', NULL, NULL, '2009-07-27 10:23:23', 'authenticate', '78a3f0161dd933036b69c7f41f719d79', 0, 0),
(6, '85bdfc5d-3b10-6f5f-32b2-4a6d80da6ba4', '1', 'Users', NULL, NULL, '2009-07-27 10:23:25', 'settimezone', '78a3f0161dd933036b69c7f41f719d79', 0, 0),
(7, '291e0fb1-c1c0-4e8f-0adb-4a6d8086757a', '1', 'Users', NULL, NULL, '2009-07-27 10:23:26', 'settimezone', '78a3f0161dd933036b69c7f41f719d79', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `upgrade_history`
--

CREATE TABLE IF NOT EXISTS `upgrade_history` (
  `id` char(36) NOT NULL,
  `filename` varchar(255) default NULL,
  `md5sum` varchar(32) default NULL,
  `type` varchar(30) default NULL,
  `status` varchar(50) default NULL,
  `version` varchar(10) default NULL,
  `name` varchar(255) default NULL,
  `description` text,
  `id_name` varchar(255) default NULL,
  `manifest` text,
  `date_entered` datetime NOT NULL,
  `enabled` tinyint(1) default '1',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `upgrade_history_md5_uk` (`md5sum`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `upgrade_history`
--


-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` char(36) NOT NULL,
  `user_name` varchar(60) default NULL,
  `user_hash` varchar(32) default NULL,
  `authenticate_id` varchar(100) default NULL,
  `sugar_login` tinyint(1) default '1',
  `first_name` varchar(30) default NULL,
  `last_name` varchar(30) default NULL,
  `reports_to_id` char(36) default NULL,
  `is_admin` tinyint(1) default '0',
  `receive_notifications` tinyint(1) default '1',
  `description` text,
  `date_entered` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  `modified_user_id` char(36) default NULL,
  `created_by` char(36) default NULL,
  `title` varchar(50) default NULL,
  `department` varchar(50) default NULL,
  `phone_home` varchar(50) default NULL,
  `phone_mobile` varchar(50) default NULL,
  `phone_work` varchar(50) default NULL,
  `phone_other` varchar(50) default NULL,
  `phone_fax` varchar(50) default NULL,
  `status` varchar(25) default NULL,
  `address_street` varchar(150) default NULL,
  `address_city` varchar(100) default NULL,
  `address_state` varchar(100) default NULL,
  `address_country` varchar(25) default NULL,
  `address_postalcode` varchar(9) default NULL,
  `user_preferences` text,
  `deleted` tinyint(1) NOT NULL default '0',
  `portal_only` tinyint(1) default '0',
  `employee_status` varchar(25) default NULL,
  `messenger_id` varchar(25) default NULL,
  `messenger_type` varchar(25) default NULL,
  `is_group` tinyint(1) default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_user_name` (`user_name`,`is_group`,`status`,`last_name`,`first_name`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_name`, `user_hash`, `authenticate_id`, `sugar_login`, `first_name`, `last_name`, `reports_to_id`, `is_admin`, `receive_notifications`, `description`, `date_entered`, `date_modified`, `modified_user_id`, `created_by`, `title`, `department`, `phone_home`, `phone_mobile`, `phone_work`, `phone_other`, `phone_fax`, `status`, `address_street`, `address_city`, `address_state`, `address_country`, `address_postalcode`, `user_preferences`, `deleted`, `portal_only`, `employee_status`, `messenger_id`, `messenger_type`, `is_group`) VALUES
('1', 'admin', '5ebe2294ecd0e0f08eab7690d2a6ee69', NULL, 1, NULL, 'Administrator', NULL, 1, 1, NULL, '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'Administrator', NULL, NULL, NULL, NULL, NULL, NULL, 'Active', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `users_feeds`
--

CREATE TABLE IF NOT EXISTS `users_feeds` (
  `user_id` varchar(36) default NULL,
  `feed_id` varchar(36) default NULL,
  `rank` int(11) default NULL,
  `date_modified` datetime default NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  KEY `idx_ud_user_id` (`user_id`,`feed_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users_feeds`
--

INSERT INTO `users_feeds` (`user_id`, `feed_id`, `rank`, `date_modified`, `deleted`) VALUES
('1', '4bbca87f-2017-5488-d8e0-41e7808c2553', 1, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `users_last_import`
--

CREATE TABLE IF NOT EXISTS `users_last_import` (
  `id` char(36) NOT NULL,
  `assigned_user_id` char(36) default NULL,
  `import_module` varchar(36) default NULL,
  `bean_type` varchar(36) default NULL,
  `bean_id` char(36) default NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_user_id` (`assigned_user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users_last_import`
--


-- --------------------------------------------------------

--
-- Table structure for table `users_signatures`
--

CREATE TABLE IF NOT EXISTS `users_signatures` (
  `id` char(36) NOT NULL,
  `date_entered` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  `user_id` varchar(36) default NULL,
  `name` varchar(255) default NULL,
  `signature` text,
  `signature_html` text,
  PRIMARY KEY  (`id`),
  KEY `idx_usersig_uid` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users_signatures`
--


-- --------------------------------------------------------

--
-- Table structure for table `user_preferences`
--

CREATE TABLE IF NOT EXISTS `user_preferences` (
  `id` char(36) NOT NULL,
  `category` varchar(50) default NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  `date_entered` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  `assigned_user_id` char(36) NOT NULL,
  `contents` text,
  PRIMARY KEY  (`id`),
  KEY `idx_userprefnamecat` (`assigned_user_id`,`category`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `user_preferences`
--


-- --------------------------------------------------------

--
-- Table structure for table `vcals`
--

CREATE TABLE IF NOT EXISTS `vcals` (
  `id` char(36) NOT NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  `date_entered` datetime default NULL,
  `date_modified` datetime default NULL,
  `user_id` char(36) NOT NULL,
  `type` varchar(25) default NULL,
  `source` varchar(25) default NULL,
  `content` text,
  PRIMARY KEY  (`id`),
  KEY `idx_vcal` (`type`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `vcals`
--


-- --------------------------------------------------------

--
-- Table structure for table `versions`
--

CREATE TABLE IF NOT EXISTS `versions` (
  `id` char(36) NOT NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  `date_entered` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  `modified_user_id` char(36) NOT NULL,
  `created_by` char(36) default NULL,
  `name` varchar(255) NOT NULL,
  `file_version` varchar(255) NOT NULL,
  `db_version` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `idx_version` (`name`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `versions`
--

INSERT INTO `versions` (`id`, `deleted`, `date_entered`, `date_modified`, `modified_user_id`, `created_by`, `name`, `file_version`, `db_version`) VALUES
('efe42ec9-6ef6-ad3c-6256-4a6d7f2ec757', 0, '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'Chart Data Cache', '3.5.1', '3.5.1'),
('f0aba2d6-9cf4-f3ad-4aa0-4a6d7f85abde', 0, '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'htaccess', '3.5.1', '3.5.1'),
('f175c752-4996-5029-d80e-4a6d7f483613', 0, '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'Rebuild Relationships', '4.0.0', '4.0.0'),
('f23dd85a-823a-82be-87b2-4a6d7fa72182', 0, '2009-07-27 10:22:39', '2009-07-27 10:22:39', '1', NULL, 'Rebuild Extensions', '4.0.0', '4.0.0');

