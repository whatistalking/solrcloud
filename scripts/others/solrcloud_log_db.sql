#记录log
CREATE TABLE `status_current` (
      `log_type` varchar(20) collate utf8_unicode_ci NOT NULL default '',
      `log_name` varchar(20) collate utf8_unicode_ci NOT NULL default '',
      `log_value` varchar(30) collate utf8_unicode_ci NOT NULL default '',
      `log_time` datetime NOT NULL default '0000-00-00 00:00:00',
      `target_id` int(11) NOT NULL default '0',
      KEY `idx_2` (`log_type`,`log_name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
#记录每小时的全局log
CREATE TABLE `status_log_global` (
      `id` int(10) unsigned NOT NULL auto_increment,
      `log_idc` varchar(10) collate utf8_unicode_ci NOT NULL default 'idc10',
      `log_host` int(11) NOT NULL default '-1',
      `log_type` varchar(20) collate utf8_unicode_ci NOT NULL default '',
      `log_name` varchar(20) collate utf8_unicode_ci NOT NULL default '',
      `log_value` varchar(30) collate utf8_unicode_ci NOT NULL default '',
      `log_time` datetime NOT NULL default '0000-00-00 00:00:00',
      `target_id` int(11) NOT NULL default '0',
      PRIMARY KEY  (`id`),
      UNIQUE KEY `idx_1` (`log_idc`,`log_host`,`log_type`,`log_name`,`target_id`,`log_time`),
      KEY `log_time` (`log_time`),
      KEY `idx_2` (`target_id`,`log_name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

insert into `solrcloud_db`.`job_scheduler` values('','server','status/status.global.php','','5','','',1);
/*slowQuery*/
CREATE TABLE `frequentquery` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `service` varchar(40) collate utf8_unicode_ci NOT NULL default '',
  `url` text collate utf8_unicode_ci,
  `avg` float NOT NULL default '0',
  `count` int(11) NOT NULL default '0',
  `log_time` date NOT NULL default '0000-00-00',
  PRIMARY KEY  (`id`),
  KEY `idx_lsc` (`log_time`,`service`,`count`),
  KEY `idx_lsa` (`log_time`,`service`,`avg`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_c;
CREATE TABLE `slowquery` (
   `id` int(10) unsigned NOT NULL auto_increment,
   `service` varchar(40) collate utf8_unicode_ci NOT NULL default '',
   `url` text collate utf8_unicode_ci,
   `avg` float NOT NULL default '0',
   `count` int(11) NOT NULL default '0',
   `log_time` date NOT NULL default '0000-00-00',
   PRIMARY KEY  (`id`),
   KEY `idx_lsc` (`log_time`,`service`,`count`),
   KEY `idx_lsa` (`log_time`,`service`,`avg`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
