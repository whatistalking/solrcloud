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

$config = pdo_get_solr_schema($link, $service[0]["service_id"]);
if (!$config) {
    echo "<!-- # Solr schema not exists -->\n";
    exit(1);
}
if($service[0]['schema_type'] ==1){
	$json = json_decode($config["schema_json"], true);
        if(!get_version_dir($service[0]["solr_version"])){
            exit(1);
        }
        $template = file_get_contents($base .'/'. get_version_dir($service[0]["solr_version"]).'/schema.xml.tpl');
        //$template = file_get_contents(build_path_configtpl($solr_root, $instance['port_num'])."/schema.xml.tpl");
        if(!$template){
            $template = file_get_contents($base . "/schema.xml.tpl");
        } 
	foreach ($json as $k => $v) {
	    if (is_array($v)) continue;
	    $reg = sprintf('/\${%s}/', $k);
	    $template = preg_replace($reg, $v, $template);
	}
	
	$xml = "";
	foreach ($json["fields"] as $field) {
	    $xml .= "        <field ";
	    foreach ($field as $k => $v) {
	        $xml .= sprintf("%s=\"%s\" ", $k, $v);
	    }
	    $xml .= "/>\n";
	}
	
	if (isset($json["dynamicFields"])) {
	    foreach ($json["dynamicFields"] as $field) {
	        $xml .= "        <dynamicField ";
	        foreach ($field as $k => $v) {
	            $xml .= sprintf("%s=\"%s\" ", $k, $v);
	        }
	        $xml .= "/>\n";
	    }
	}
	
	$template = preg_replace('/\${fields}/', trim($xml), $template);
}else{
	$template = $config["schema_json"];
}

if(empty($template)){
        exit(2);
}

echo $template;
