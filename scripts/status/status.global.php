<?php
$base =  dirname(__FILE__);
$solr_root = substr($base, 0, strrpos($base,'scripts'));
$script_root = $solr_root."/scripts";
include_once($script_root . "/common.php");

/*从status.current获得global数据，放到表status_global*/
$global = array('service-docnumber','service-select','service-update');
$hour = date('Y-m-d H:i:00');
foreach($global as $g){
    $g = explode('-',$g);
    $log_type = $g[0];
    $log_name = $g[1];

    if($log_type && $log_name){
        $sql = "select sum(log_value) from status_current where log_type='$log_type' and log_name='$log_name'";
        $log_val = pdo_fetch_column($link_log, $sql);
        add_status_log($link_log,$cfg['idc'],$cfg['default_host'],'global', $log_name, 0, $log_val,$hour, 'status_log_global'); 
    }


}

