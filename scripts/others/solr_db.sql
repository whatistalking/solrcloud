CREATE TABLE `user_setting` (

  `id` int(11) NOT NULL auto_increment,

  `username` varchar(50) NOT NULL COMMENT '域账户名',

  `mobile` varchar(11) NOT NULL default '' COMMENT '手机号',

  `email` varchar(100) NOT NULL default '' COMMENT '邮箱',

  `report_setting` text NOT NULL COMMENT '报表设置',

  PRIMARY KEY  (`id`)

) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;



CREATE TABLE `alert_info` (

  `alert_id` int(11) NOT NULL auto_increment,

  `username` varchar(50) NOT NULL COMMENT '域账户名',

  `target_type` varchar(20) NOT NULL COMMENT '目标类型，service/instance/host',

  `target_id` smallint(5) NOT NULL COMMENT '目标id',

  `alert_type_id` tinyint(3) NOT NULL COMMENT '报警种类id',

  `alert_type_name` varchar(50) NOT NULL default '' COMMENT '报警种类名称',

  `alert_setting` text NOT NULL COMMENT '报警设置',

  `is_disabled` tinyint(4) NOT NULL default '0' COMMENT '是否开启',

  `is_deleted` tinyint(4) NOT NULL default '0' COMMENT '是否删除',

  `last_update` timestamp NOT NULL default CURRENT_TIMESTAMP COMMENT '最近更新',

  PRIMARY KEY  (`alert_id`)

) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

#多master，zk字段有数据才使用多master
alter table service add column zk varchar(300) NOT NULL DEFAULT ''；
insert into action values('20', 'create_zk', 'service2', 'action_queue/solr.zk.sh')；
# max_fails
alter table instance add column max_fails varchar(20) NOT NULL
