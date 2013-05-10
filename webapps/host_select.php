<?php
require_once './libraries/common.lib.php';

$mem = '121111111111';
$service_id = 14;
$host_list_select = get_host_list_select($pdo, $mem, $service_id);

var_dump($host_list_select);
