<?php
require_once './libraries/common.lib.php';

$instances = array();

$services = get_service_list_running($pdo);
foreach ($services as $s) {
    $service_id = $s["service_id"];
    $instances = array_merge($instances, pdo_get_mapping_info($pdo, $service_id));
}

if (empty($instances)) {
    exit();
}

header("Content-Type:Text/Plain");
foreach ($instances as $ins) {
    printf("%s\t%s\t%s\t%s\n", $ins["service_name"], $ins["host_name"], $ins["port_num"], $cfg['monitor_status'][$ins["monitor_status"]]);
}
# service_name host_name port_num monitor_status
