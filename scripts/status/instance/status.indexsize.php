<?php 
$base =  dirname(__FILE__);
$solr_root = substr($base, 0, strrpos($base,'scripts'));
$script_root = $solr_root."/scripts";
include_once($script_root . "/common.php");

$instance_list_running = get_instance_list_complex($link,array());
if($instance_list_running){
    foreach($instance_list_running as $instance){
        $url = "http://".$instance['host_ip'].":".$instance['port_num']."/solr/replication?command=details&wt=json";
        $result = curl_get_content($url);
        $result = json_decode($result,true);
        $indexSize = isset($result['details']['indexSize'])?$result['details']['indexSize']:null;

        if($indexSize){
            add_status_log($link_log,$cfg['idc'],$cfg['default_host'],'instance','indexsize',$instance['instance_id'],to_m($indexSize),date('Y-m-d H:i:00'), 'status_log_service_indexsize');
        }
    }
}

/*单位换算*/
function to_m($str){
    preg_match("/[0-9.]+/",$str,$i);
    if(!$i) return false;

    $i = array_pop($i);
    if(strpos($str,'M') !== false){
        return $i;
    }
    if(strpos($str,'G') !== false){ 
        return $i*1024;
    }
    if(strpos($str,'K') !=false){
        return round($i/1024,2);
    }
    if(strpos($str,'bytes') !=false){
        return round($i/1024/1024,2);
    }
    return false;
}
