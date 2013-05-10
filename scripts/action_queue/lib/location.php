<?php

$base =  dirname(__FILE__);
$solr_root = substr($base, 0, strrpos($base,'scripts'));
$script_root = $solr_root."/scripts";
include_once($script_root . "/common.php");

if (count($argv) < 2 || !preg_match("/^[0-9]+$/", $argv[1])) {
    echo " # Usage: ${argv[0]} service_id\n";
    exit(2);
}

$service_id = $argv[1];
$service = pdo_get_service_byid($link, $service_id);
if (!$service) {
    echo " # Service not exists\n";
    exit(1);
}

$mapping = pdo_get_mapping_info($link, $service_id);
if (!$mapping) exit(1);

foreach ($mapping as $m) {
    if ($m["writable"] == 1) {
        echo out_location($service);
        break;
    }
}

foreach ($mapping as $m) {
    if ($m["readable"] == 1) {
        echo out_location($service, "select");
        echo out_location($service, "clustering", "select");
        break;
    }
}

//echo out_location_admin($service);

