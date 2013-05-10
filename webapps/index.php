<?php
require_once './libraries/common.lib.php';

/*************overview*************/
$service_count = get_service_count($pdo);
$on_service_count = get_service_count($pdo,true);
$instance_count = get_instance_count($pdo);
$running_instance_count = get_instance_count($pdo,true);
$host_count = get_host_count($pdo);

/*************Nginx Status*************/
$last_nginx_reload = pdo_get_last_nginx_reload($pdo);
$ago = intval((time() - strtotime($last_nginx_reload["queue_time"])) / 86400);

/*************Request*************/
/*$urlupd=$urlslt="";
$sa = pdo_get_cloud_access($pdo, 13);
if(is_array($sa)){
	krsort($sa);
	foreach ($sa as $i => $a) {
	    $min = date("i", strtotime($a["log_time"]));
	    $chdslt[$i] = array($min, $a["num_selects"]);
	    $chdupd[$i] = array($min, $a["num_updates"]);
	}
	$urlupd = build_line_chart_lc($chdupd, 100, "340x180");
	$urlslt = build_line_chart_lc($chdslt, 500, "340x180", "A43708");
}*/


$current_nav='index';
$template = 'index';
require_once './libraries/decorator.inc.php';
