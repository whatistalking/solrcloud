<?php
//数据库配置
$cfg['database']['host']='192.168.1.103';
$cfg['database']['port']='3306';
$cfg['database']['user']='caixh';
$cfg['database']['password']='caixh123';
$cfg['database']['dbname']='solrcloud_db';

$cfg['database_log']['host']='192.168.1.103';
$cfg['database_log']['port']='3306';
$cfg['database_log']['user']='caixh';
$cfg['database_log']['password']='caixh123';
$cfg['database_log']['dbname']='solrcloud_log_db';

$cfg['database_chart_log']['host']='10.10.8.35';
$cfg['database_chart_log']['port']='3306';
$cfg['database_chart_log']['user']='ops_monitor';
$cfg['database_chart_log']['password']='ops_monitor';
$cfg['database_chart_log']['dbname']='OPS_Monitor';

define('SERVICE_RELOAD',1);
define('SERVICE_RECONFIGURE',2);
define('INSTANCE_START',3);
define('INSTANCE_STOP',4);
define('INSTANCE_DEPLOY',5);
define('INSTANCE_RECONFIGURE_SCHEMA',6);
define('INSTANCE_RECONFIGURE_SOLRCONF',7);
define('INSTANCE_RECONFIGURE_JETTY',8);
define('INSTANCE_OPTIMIZE',9);
define('XMLRPC_RELOAD',10);
define('XMLRPC_RECONFIGURE',11);
define('CREATE_ZK',20);

$cfg['instance_status'][0]='new';
$cfg['instance_status'][1]='ready';
$cfg['instance_status'][2]='running';
$cfg['instance_status'][3]='stoped';

$cfg['monitor_status'][0]='fine';
$cfg['monitor_status'][01]='fine  Instance select fail: zero result!';
$cfg['monitor_status'][1]='error';
$cfg['monitor_status'][11]='error  Instance not exists';
$cfg['monitor_status'][12]='error  Instance select fail: connect timed out!';
$cfg['monitor_status'][13]='error  Instance select fail: http status code is not 200!';
$cfg['monitor_status'][15]='error  Instance select fail: OutOfMemoryError! increase memory automatically';
$cfg['monitor_status'][16]='error  Instance select fail: could not connect to host! restart automatically';
$cfg['monitor_status'][2]='warning';
$cfg['monitor_status'][21]='warning  empty param';
$cfg['monitor_status'][22]='warning  Instance select fail: slow query!';

$cfg['queue_status']['-1'] = array('pending', 'black');
$cfg['queue_status']['0'] = array('processing', 'blue');
$cfg['queue_status']['1'] = array('success', 'green');
$cfg['queue_status']['2'] = array('failure', 'red');
$cfg['queue_status']['3'] = array('skiped', 'gray');

$cfg['hash_type'][0]='Default Hash';
$cfg['hash_type'][1]='Consistent Hash';

define('GOOGLE_CHART', 'http://chart.apis.google.com/chart?');

$cfg['puppet']['enable'] = false;
$cfg['puppet']['host'] = '10.10.6.131';
$cfg['puppet']['port'] = '1080';
$cfg['puppet']['uri'] = '/RPC2';

$cfg['idc'] = 'sysdev';
$cfg['default_host'] = -1;

// service  detail 页面的数据读取时主机的ID，和IDX10-003最后的数字对应。和config.sh中的$host的值对应
$cfg['host'] = -1;

$cfg['all_log_hosts'] = array(-1);

// scripts/status.docnumber.php 服务文档数量监控使用的url 还有webapp/service_detail.phtml使用的搜索URL
$cfg['search_url'] = 'http://sysdev.dev.anjuke.com:8983';

$cfg['execute_cron'] = array('client');

$cfg['access_log_path'] = '/data1/logs/nginx';
$cfg['lb_host'] = array(0=>'1');

$cfg['oauth']['client_id'] = 'solrcloud_cx';
$cfg['oauth']['client_secret'] = 'b8db3fb7';
$cfg['oauth']['oauth_url'] = 'https://auth.corp.anjuke.com';

$cfg['AuthCookieName'] = "ajk_sc2_auth_info";
$cfg['sc_url'] = "http://solrcloud.chenxiang.dev.anjuke.com";

$cfg['cookie_time'] = 604800;
$cfg['auto_instance_mem'] = '1024';

$cfg['zk_host_id'] = 1;


#solr_version
$cfg['solr_version'] = array(
    '1' => array('name' => '1.4x', 'dir' => '7700', 'disable' => true),
    '2' => array('name' => '3.5x', 'dir' => '7700-35'),
    '3' => array('name' => '3.6x', 'dir' => '7700-36'),
    '4' => array('name' => '4.0x', 'dir' => '7700-40'),
    '5' => array('name' => '4.1x', 'dir' => '7700-41'),
    '6' => array('name' => '4.2x', 'dir' => '7700-42'),//4.2.1+
);
