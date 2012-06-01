SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- --------------------------------------------------------

--
-- Table structure for table `newsletters`
--

CREATE TABLE IF NOT EXISTS `newsletters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  `html` text COLLATE utf8_unicode_ci,
  `template` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cache_file` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_boxes`
--

CREATE TABLE IF NOT EXISTS `newsletter_boxes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `newsletter_id` int(11) NOT NULL,
  `zone` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `order` int(11) NOT NULL DEFAULT '0',
  `template` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `data` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `multimedia` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_emails`
--

CREATE TABLE IF NOT EXISTS `newsletter_emails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `sendlist_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sex` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `town` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `postalcode` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_sended`
--

CREATE TABLE IF NOT EXISTS `newsletter_sended` (
  `id` int(11) NOT NULL auto_increment,
  `newsletter_id` int(11) NOT NULL,
  `email_id` int(11) default NULL,
  `sendlist_id` int(11) default NULL,
  `email` varchar(255) collate utf8_unicode_ci NOT NULL,
  `view` int(11) NOT NULL default '0',
  `sending_id` int(11) default NULL,
  `status` varchar(255) collate utf8_unicode_ci default NULL,
  `error` text collate utf8_unicode_ci default NULL,
  `date` datetime default NULL,
  `active` tinyint(1) default NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `newsletter_id` (`newsletter_id`),
  KEY `email` (`email`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_sendings`
--
CREATE TABLE IF NOT EXISTS `newsletter_sendings` (
  `id` int(11) NOT NULL auto_increment,
  `newsletter_id` int(11) default NULL,
  `selected_lists` text collate utf8_unicode_ci,
  `additional_emails` text collate utf8_unicode_ci,
  `check_sended` tinyint(1) default NULL,
  `date` datetime default NULL,
  `html` text collate utf8_unicode_ci,
  `sender_name` varchar(255) collate utf8_unicode_ci default NULL,
  `sender_email` varchar(255) collate utf8_unicode_ci default NULL,
  `self_sending` tinyint(1) default NULL,
  `status` varchar(255) collate utf8_unicode_ci default NULL,
  `started` tinyint(1) default NULL,
  `confirm` tinyint(1) default NULL,
  `active` tinyint(1) default NULL,
  `last_process_time` datetime default NULL,
  `console` text collate utf8_unicode_ci,
  `created` datetime default NULL,
  `modified` datetime default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;


-- --------------------------------------------------------

--
-- Table structure for table `newsletter_sendlists`
--

CREATE TABLE IF NOT EXISTS `newsletter_sendlists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_stats`
--

CREATE TABLE IF NOT EXISTS `newsletter_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sended_id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip_address` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sended_id` (`sended_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;
