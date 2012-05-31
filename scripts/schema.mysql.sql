CREATE TABLE IF NOT EXISTS `emailhash` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cookie` varchar(64) NOT NULL,
  `email` varchar(64) NOT NULL,
  `hash` varchar(64) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (id),
  KEY `emailhash_index` (`hash`),
  KEY `emailhash_created_index` (`created`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `urlcache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain` varchar(255) NOT NULL,
  `path` varchar(255)  NOT NULL,
  `title` text  NOT NULL,
  `postcount` int(11) NOT NULL DEFAULT 0,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (id),
  KEY `urlcache_domain_index` (`domain`),
  KEY `urlcache_path_index` (`path`),
  KEY `created_index` (`created`),
  KEY `updated_index` (`updated`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `cookie` (
  `id` varchar(64) NOT NULL,
  `nick` varchar(64),
  `email` varchar(255),
  `password` varchar(255),
  `twitter` text,
  `facebook` text,
  `ccemail` text,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `cookie_email_index` (`email`),
  KEY `created_index` (`created`),
  KEY `updated_index` (`updated`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain` varchar(255) NOT NULL DEFAULT '',
  `path` varchar(255)  NOT NULL,
  `cookie` varchar(64) NOT NULL,
  `nick` varchar(64),
  `email` varchar(255),
  `ip` varchar(16),
  `content` text,
  `reply_to` int(11),
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `domain_index` (`domain`),
  KEY `path_index` (`path`),
  KEY `cookie_index` (`cookie`),
  KEY `email_index` (`email`),
  KEY `ip_index` (`ip`),
  KEY `reply_to_index` (`reply_to`),
  KEY `created_index` (`created`),
  KEY `updated_index` (`updated`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


