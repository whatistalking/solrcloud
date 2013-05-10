CREATE TABLE `action` (
  `action_id` tinyint(3) unsigned NOT NULL,
  `action_name` varchar(20) NOT NULL default '',
  `action_type` varchar(20) NOT NULL default '',
  `script_name` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`action_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `action_queue` (
  `queue_id` int(11) NOT NULL auto_increment,
  `queue_time` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `queue_status` tinyint(4) NOT NULL default '0',
  `action_id` tinyint(3) unsigned NOT NULL default '0',
  `target_id` smallint(5) unsigned NOT NULL default '0',
  `session_id` char(12) NOT NULL,
  PRIMARY KEY  (`queue_id`),
  KEY `queue_status` (`queue_status`,`action_id`,`target_id`,`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `host` (
  `host_id` int(10) unsigned NOT NULL auto_increment,
  `host_name` varchar(30) NOT NULL default ' ',
  `host_ip` varchar(30) NOT NULL default ' ',
  PRIMARY KEY  (`host_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `instance` (
  `instance_id` smallint(5) unsigned NOT NULL auto_increment,
  `host_id` tinyint(3) unsigned NOT NULL default '0',
  `port_num` smallint(5) unsigned NOT NULL default '0',
  `instance_status` tinyint(3) unsigned NOT NULL default '0',
  `is_locked` tinyint(3) unsigned NOT NULL default '0',
  `writable` tinyint(3) unsigned NOT NULL default '0',
  `readable` tinyint(3) unsigned NOT NULL default '0',
  `lb_weight` varchar(20) NOT NULL,
  PRIMARY KEY  (`instance_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `jetty_config` (
  `instance_id` smallint(5) unsigned NOT NULL default '0',
  `config_json` text NOT NULL,
  PRIMARY KEY  (`instance_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `log_service_access` (
  `service_id` smallint(6) NOT NULL default '0',
  `log_time` datetime NOT NULL default '2010-09-01 00:00:00',
  `num_updates` int(11) NOT NULL default '0',
  `num_selects` int(11) NOT NULL default '0',
  PRIMARY KEY  (`service_id`,`log_time`),
  KEY `log_time` (`log_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `schema_field_options` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(30) NOT NULL,
  `value` varchar(30) NOT NULL,
  `memo` varchar(300) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `schema_type` (
  `name` varchar(30) NOT NULL,
  `support_field_options` varchar(300) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `service` (
  `service_id` smallint(5) unsigned NOT NULL auto_increment,
  `service_name` varchar(20) NOT NULL default '',
  `url_regex` varchar(50) NOT NULL default '',
  `description` varchar(1000) NOT NULL,
  `service_status` tinyint(3) unsigned NOT NULL default '0',
  `is_locked` tinyint(3) unsigned NOT NULL default '0',
  `hash_type` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`service_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `service_mapping` (
  `service_id` smallint(5) unsigned NOT NULL default '0',
  `instance_id` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`service_id`,`instance_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `solr_config` (
  `service_id` smallint(5) unsigned NOT NULL default '0',
  `config_json` text NOT NULL,
  PRIMARY KEY  (`service_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `solr_schema` (
  `service_id` smallint(5) unsigned NOT NULL default '0',
  `schema_json` text NOT NULL,
  PRIMARY KEY  (`service_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `action` VALUES
(1,'reload','service','nginx.reload.sh'),
(2,'reconfigure','service','nginx.reconfigure.sh'),
(3,'start','instance','solr.start.sh'),
(4,'stop','instance','solr.stop.sh'),
(5,'deploy','instance','solr.deploy.sh'),
(6,'reconfigure schema','instance','solr.reconfigure.schema.sh'),
(7,'reconfigure solrconf','instance','solr.reconfigure.solrconfig.sh'),
(8,'reconfigure jetty','instance','solr.reconfigure.jetty.sh'),
(9,'optimize','instance','solr.optimize.sh');

INSERT INTO `schema_field_options` VALUES
(1,'default','','如果为空，填入的默认值。'),
(2,'required','true|false','是否必须'),
(3,'indexed','true|false','是否索引'),
(4,'stored','true|false','是否存储'),
(5,'compressed','true|false','是否使用gzip压缩'),
(6,'multiValued','true|false','是否存储或索引多个值');

INSERT INTO `schema_type` VALUES
('string','1|2|5|6'),
('boolean','1|2|6'),
('int','1|2|6'),
('float','1|2|6'),
('long','1|2|6'),
('double','1|2|6'),
('tint','1|2|6'),
('tfloat','1|2|6'),
('tlong','1|2|6'),
('tdouble','1|2|6'),
('sint','1|2|6'),
('sfloat','1|2|6'),
('slong','1|2|6'),
('sdouble','1|2|6'),
('date','1|2|6'),
('tdate','1|2|6'),
('text','1|2|5|6'),
('ignored','');