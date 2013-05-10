<?php

$base =  dirname(__FILE__);
$solr_root = substr($base, 0, strrpos($base,'scripts'));
$script_root = $solr_root."/scripts";
include_once($script_root . "/common.php");


if (count($argv) < 2 || !preg_match("/^[0-9]+$/", $argv[1])) {
    echo "<!-- # Usage: ${argv[0]} instance_id -->\n";
    exit(2);
}

$instance_id = $argv[1];
$instance = pdo_get_instance_byid($link, $instance_id);

if (!$instance) {
    echo "<!-- # Instance not exists -->\n";
    exit(1);
}

$service = get_service_list_by_mapping($link, $instance);
if (!$service) {
    echo "<!-- # Service mapping not exists -->\n";
    exit(1);
}

$config = pdo_get_solr_config($link, $service[0]["service_id"]);
if (!$config) {
    echo "<!-- # Solr config not exists -->\n";
    exit(1);
}
if ($service[0]["config_type"] == 1) {
    /*优先到自己的instance路径找tpl文件，找不到则采用公共的文件(只兼容3.5x)*/
    //$template = file_get_contents(build_path_configtpl($solr_root, $instance['port_num'])."/solrconfig.xml.tpl");
    if(!get_version_dir($service[0]["solr_version"])){
        exit(1);
    }
    $template = file_get_contents($base .'/'. get_version_dir($service[0]["solr_version"]).'/solrconfig.xml.tpl');
    if(!$template){
        $template = file_get_contents($base . "/solrconfig.xml.tpl");
    }
    $json = json_decode($config["config_json"], true);
}else {
    $template = $config["config_json"];
}

if(empty($template)){
    exit(2);
}

//$json["masterUrl"] = "";
//if ($instance["writable"] == 0) {
//    $master = pdo_get_master_complex($link, $service[0]["service_id"]);
//    $json["masterUrl"] = build_url_replication($master["host_ip"], $master["port_num"]);
//    if($json["pollInterval"]){
//        $json["pollInterval"] = $json["pollInterval"]; 
//    }else{
//        $json["pollInterval"] = "00:01:00";
//    }
//} else {
//    $json["pollInterval"] = "00:00:00";
//}

$json["master"] = "";
$json["slave"] = "";
$json["masterUrl"] = "";

$json['dataimport-config'] = "";
$dataimport = pdo_get_dataimport($link, $service[0]["service_id"]);
$config_path = $solr_root.'/cloud/'.$instance['port_num'].'/idx/conf/db-data-config.xml';
unlink($config_path);

if($instance["writable"] == 1){/*master*/
    $json['master'] = '<lst name="master"><str name="replicateAfter">commit</str><str name="replicateAfter">startup</str><str name="confFiles">schema.xml,stopwords.txt</str></lst>';
    $json["pollInterval"] = "00:00:00";
    if(!empty($dataimport)){
        $fp = fopen($config_path, 'w');
        fwrite($fp, $dataimport);
        fclose($fp);
        $json['dataimport-config'] = 'db-data-config.xml';/*指向dataimport的配置文件，若db-data-config.xml为空则此项置空*/
    }
}
else{/*slave*/
    $master = pdo_get_master_complex($link, $service[0]["service_id"]);
    $json["masterUrl"] = build_url_replication($master["host_ip"], $master["port_num"]);
    if($json["pollInterval"]){
        $json["pollInterval"] = $json["pollInterval"]; 
    }else{
        $json["pollInterval"] = "00:01:00";
    }
 
    $json['slave'] = '<lst name="slave"><str name="masterUrl">${masterUrl}</str><str name="pollInterval">${pollInterval}</str></lst>';
    foreach ($json as $k => $v) {
        $reg = sprintf('/\${%s}/', $k);
        $json['slave'] = preg_replace($reg, $v, $json['slave']);
    }
}

foreach ($json as $k => $v) {
    $reg = sprintf('/\${%s}/', $k);
    $template = preg_replace($reg, $v, $template);
}
echo $template;
