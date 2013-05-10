<?php 
// 所有running & readable 的instance 

$base =  dirname(__FILE__);
$solr_root = substr($base, 0, strrpos($base,'scripts'));
$script_root = $solr_root."/scripts";
include_once($script_root . "/common.php");

/*running的readable的instance的命中率*/
$instance_list_running = get_instance_list_complex($link,array());
if($instance_list_running){
    foreach($instance_list_running as $instance){
        $url = "http://".$instance['host_ip'].":".$instance['port_num']."/solr/admin/mbeans?stats=true&wt=json";
        $result = curl_get_content($url);
        $result = json_decode($result,true);
        $cache = isset($result['solr-mbeans'][7])?$result['solr-mbeans'][7]:null;
        if($cache){
            foreach($cache as $k=>$c){
                if(isset($c['stats']['hitratio'])){
                    $hitratio = $c['stats']['hitratio'];
                    add_status_log($link_log,$cfg['idc'],$cfg['default_host'],'instance',$k,$instance['instance_id'],$hitratio,date('Y-m-d H:i:00'), 'status_log_instance_hits');
                }
            }
        }
    }
}
