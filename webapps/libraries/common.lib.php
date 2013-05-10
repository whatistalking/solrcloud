<?php
if(isset($_COOKIE['debug'])){
    ini_set("display_errors", "On");
    error_reporting(E_ALL);
}
define('SC_PATH',realpath(dirname(__FILE__).'/../'));

require_once SC_PATH.'/libraries/config.inc.php';
require_once SC_PATH.'/libraries/config.alert.php';
if (file_exists(SC_PATH.'/../config.prod.php')) {
    require_once SC_PATH.'/../config.prod.php';
}

require_once SC_PATH.'/libraries/functions.lib.php';

$pdo = SolrDb::getLink(
    $cfg['database']['host'],
    $cfg['database']['user'],
    $cfg['database']['password'],
    $cfg['database']['dbname']
);//实例化pdo链接

$pdo_log = SolrDb::getLink(
    $cfg['database_log']['host'],
    $cfg['database_log']['user'],
    $cfg['database_log']['password'],
    $cfg['database_log']['dbname']
);//实例化pdo_log链接

$params = get_params();

$uname = get_username();

if(!isset($_COOKIE['queue_session_id'])||!$_COOKIE['queue_session_id']){
    setcookie('queue_session_id',rand_string(12));
}
