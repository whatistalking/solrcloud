<?php
$base =  dirname(__FILE__);
$solr_root = substr($base, 0, strrpos($base,'scripts'));
$script_root = $solr_root."/scripts";
include_once($script_root . "/common.php");


if (count($argv) < 2 || !preg_match("/^[0-9]+$/", $argv[1])) {
    echo "<!-- Usage: ${argv[0]} instance_id -->\n";
    exit(2);
}

$instance_id = $argv[1];
$instance = pdo_get_instance_byid($link, $instance_id);
if (!$instance) {
    echo "<!--  # Instance not exists -->\n";
    exit(1);
}

$config = pdo_get_jetty_config($link, $instance_id);
if (!$config) {
    echo "<!-- # Jetty config not exists -->\n";
    exit(1);
}

$json = json_decode($config["config_json"], true);
/*优先到自己的instance路径找tpl文件，找不到则采用公共的文件(只兼容3.5x)*/
if(!get_version_dir($instance["solr_version"])){
    exit(1);
}
$template = file_get_contents($base .'/'. get_version_dir($instance["solr_version"]).'/solr.xml.tpl');
//$template = file_get_contents(build_path_configtpl($solr_root, $instance['port_num'])."/solr.xml.tpl");
if(!$template){
    $template = file_get_contents($base . "/solr.xml.tpl");
}
foreach ($json as $k => $v) {
    $reg = sprintf('/\${%s}/', $k);
    $template = preg_replace($reg, $v, $template);
}
echo $template;
