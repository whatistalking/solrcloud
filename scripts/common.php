<?php
$solr_root = realpath($script_root . "/..");
include_once($solr_root . "/webapps/libraries/config.inc.php");
if (file_exists($solr_root . "/config.prod.php")) {
    include_once($solr_root . "/config.prod.php");
}
include_once($solr_root . "/webapps/libraries/functions.lib.php");

$link = SolrDb::getLink(
    $cfg['database']['host'],
    $cfg['database']['user'],
    $cfg['database']['password'],
    $cfg['database']['dbname']
);

$link_log = SolrDb::getLink(
    $cfg['database_log']['host'],
    $cfg['database_log']['user'],
    $cfg['database_log']['password'],
    $cfg['database_log']['dbname']
);

$link_chart_log = SolrDb::getLink(
    $cfg['database_chart_log']['host'],
    $cfg['database_chart_log']['user'],
    $cfg['database_chart_log']['password'],
    $cfg['database_chart_log']['dbname']
);
