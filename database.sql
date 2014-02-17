SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- --------------------------------------------------------

--
-- Structure de la table `newsletters`
--

CREATE TABLE IF NOT EXISTS `newsletters` (
  `id` int(11) NOT NULL auto_increment,
  `active` tinyint(1) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `title` varchar(255) collate utf8_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  `lang` varchar(255) collate utf8_unicode_ci default NULL,
  `sender` varchar(255) collate utf8_unicode_ci default NULL,
  `html` longtext collate utf8_unicode_ci,
  `template` varchar(255) collate utf8_unicode_ci default NULL,
  `tested` tinyint(1) NOT NULL,
  `external_key` varchar(255) collate utf8_unicode_ci default NULL,
  `cache_file` varchar(255) collate utf8_unicode_ci default NULL,
  `last_sync` datetime default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `newsletter_assocs`
--

CREATE TABLE IF NOT EXISTS `newsletter_assocs` (
  `id` int(11) NOT NULL auto_increment,
  `type` varchar(255) collate utf8_unicode_ci default NULL,
  `my_newsletter_id` int(11) default NULL,
  `newsletter_id` int(11) default NULL,
  `created` datetime default NULL,
  `modified` datetime default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `newsletter_boxes`
--

CREATE TABLE IF NOT EXISTS `newsletter_boxes` (
  `id` int(11) NOT NULL auto_increment,
  `active` tinyint(1) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `newsletter_id` int(11) NOT NULL,
  `zone` varchar(255) collate utf8_unicode_ci NOT NULL,
  `order` int(11) NOT NULL default '0',
  `template` varchar(255) collate utf8_unicode_ci NOT NULL,
  `data` text collate utf8_unicode_ci,
  `multimedia` text collate utf8_unicode_ci,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `newsletter_emails`
--

CREATE TABLE IF NOT EXISTS `newsletter_emails` (
  `id` int(11) NOT NULL auto_increment,
  `active` tinyint(1) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `name` varchar(255) collate utf8_unicode_ci default NULL,
  `email` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `newsletter_events`
--

CREATE TABLE IF NOT EXISTS `newsletter_events` (
  `id` int(11) NOT NULL auto_increment,
  `sended_id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `action` varchar(255) collate utf8_unicode_ci default NULL,
  `url` varchar(255) collate utf8_unicode_ci default NULL,
  `ip_address` varchar(255) collate utf8_unicode_ci default NULL,
  `user_agent` varchar(255) collate utf8_unicode_ci default NULL,
  `processed` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `sended_id` (`sended_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `newsletter_sended`
--

CREATE TABLE IF NOT EXISTS `newsletter_sended` (
  `id` int(11) NOT NULL auto_increment,
  `newsletter_id` int(11) NOT NULL,
  `newsletter_variant_id` int(11) default NULL,
  `email_id` int(11) default NULL,
  `tabledlist_id` int(11) default NULL,
  `email` varchar(255) collate utf8_unicode_ci NOT NULL,
  `name` varchar(255) collate utf8_unicode_ci default NULL,
  `view` int(11) NOT NULL default '0',
  `sending_id` int(11) default NULL,
  `status` varchar(255) collate utf8_unicode_ci default NULL,
  `error` text collate utf8_unicode_ci,
  `date` datetime default NULL,
  `active` tinyint(1) default NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `newsletter_id` (`newsletter_id`),
  KEY `email` (`email`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `newsletter_sendings`
--

CREATE TABLE IF NOT EXISTS `newsletter_sendings` (
  `id` int(11) NOT NULL auto_increment,
  `newsletter_id` int(11) default NULL,
  `selected_lists` text collate utf8_unicode_ci,
  `additional_emails` text collate utf8_unicode_ci,
  `check_sended` tinyint(1) default NULL,
  `date` datetime default NULL,
  `scheduled` tinyint(1) default NULL,
  `html` text collate utf8_unicode_ci,
  `sender_name` varchar(255) collate utf8_unicode_ci default NULL,
  `sender_email` varchar(255) collate utf8_unicode_ci default NULL,
  `data` text collate utf8_unicode_ci,
  `wrapper` varchar(255) collate utf8_unicode_ci default NULL,
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `newsletter_sendlists`
--

CREATE TABLE IF NOT EXISTS `newsletter_sendlists` (
  `id` int(11) NOT NULL auto_increment,
  `active` tinyint(1) NOT NULL,
  `subscriptable` tinyint(1) NOT NULL,
  `order` int(11) default NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `title` varchar(255) collate utf8_unicode_ci NOT NULL,
  `description` text collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Dumping data for table `newsletter_sendlists`
--

INSERT INTO `newsletter_sendlists` (`id`, `active`, `subscriptable`, `order`, `created`, `modified`, `title`, `description`) VALUES
(1, 1, 1, NULL, '2013-10-24 13:49:00', '2013-10-24 13:49:10', 'Default Sendlist', '');

--
-- Structure de la table `newsletter_sendlists_emails`
--

CREATE TABLE IF NOT EXISTS `newsletter_sendlists_emails` (
  `id` int(11) NOT NULL auto_increment,
  `newsletter_sendlist_id` int(11) default NULL,
  `newsletter_email_id` int(11) default NULL,
  `active` tinyint(1) default '1',
  `created` datetime default NULL,
  `modified` datetime default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `newsletter_stats`
--

CREATE TABLE IF NOT EXISTS `newsletter_stats` (
  `id` int(11) NOT NULL auto_increment,
  `newsletter_id` int(11) default NULL,
  `date` datetime default NULL,
  `name` varchar(255) collate utf8_unicode_ci default NULL,
  `val` varchar(255) collate utf8_unicode_ci default NULL,
  `context` varchar(255) collate utf8_unicode_ci default NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `newsletter_id` (`newsletter_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `newsletter_variants`
--

CREATE TABLE IF NOT EXISTS `newsletter_variants` (
  `id` int(11) NOT NULL auto_increment,
  `newsletter_id` int(11) default NULL,
  `code` varchar(63) collate utf8_unicode_ci default NULL,
  `conditions` text collate utf8_unicode_ci,
  `html` text collate utf8_unicode_ci,
  `active` tinyint(1) default NULL,
  `created` datetime default NULL,
  `modified` datetime default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
