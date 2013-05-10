<?php 
$base =  dirname(__FILE__);
$solr_root = substr($base, 0, strrpos($base,'scripts'));
$script_root = $solr_root."/scripts";
include_once($script_root . "/common.php");

delete_status_current($link_log,'instance','jvmmem');
/*所有instance的jvmmem*/
$instance_list_running = get_instance_list_complex($link,array());
if($instance_list_running){
    foreach($instance_list_running as $instance){
        $url = "http://".$instance['host_ip'].":".$instance['port_num']."/solr/admin/system?wt=json";
        $result = curl_get_content($url);
        $result = json_decode($result,true);
        $memory = isset($result['jvm']['memory'])?$result['jvm']['memory']:null;
        if($memory){
            $usedarray = explode(')',array_pop(explode('%',$memory['used'])));
            $percent = $usedarray[0]; 
            $total = $instance['use_memory'];
            $used = intval($total*$percent/100);
            //echo $percent.' '.$used.'/'.$total."\n";
            add_status_log($link_log,$cfg['idc'],$cfg['default_host'],'instance','jvmmem',$instance['instance_id'],$used.'/'.$total,date('Y-m-d H:i:00'), 'status_log_instance_jvmmemory');//MB
            add_status_current($link_log,'instance','jvmmem',$instance['instance_id'],$used.'/'.$total,date('Y-m-d H:i:00'));//MB
        }
    }
}
