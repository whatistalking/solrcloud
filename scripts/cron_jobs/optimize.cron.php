<?php

$base =  dirname(__FILE__);
$solr_root = substr($base, 0, strrpos($base,'scripts'));
$script_root = $solr_root."/scripts";
include_once($script_root . "/common.php");

$now_hour = date("G");

$services = get_service_list_running($link);
if (!$services) exit();

foreach ($services as $s) {
    if (empty($s["optimize_time"])) continue;

    $hours = explode(",", $s["optimize_time"]);
    foreach ($hours as $i => $h) {
        $hours[$i] = intval($h);
    }
    if (!in_array($now_hour, $hours)) continue;

#    $instances = pdo_get_instances($link, $s["service_id"]);
#    if (!$instances) continue;
#
#    foreach ($instances as $i) {
#        if ($i["writable"]==0) continue;
#        
#        insert_action_queue($link, INSTANCE_OPTIMIZE, $i["instance_id"], 'Robot');
#    }
#   考虑到多master的情况（选第一个writable=1的instance）
    $master = pdo_get_master_complex($link, $s["service_id"]);
    if (!$master) continue;
    insert_action_queue($link, INSTANCE_OPTIMIZE, $master["instance_id"], 'Robot');
}
