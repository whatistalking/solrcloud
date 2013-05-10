<?php
require_once './libraries/common.lib.php';

//$queue_list = get_queue_list_complex($pdo, 100);
$action = isset($params['action'])?$params['action']:"";

if($action == 'confirm'){
    confirm_action_queue($pdo);
    setcookie('queue_session_id','');
    msg_redirect('queue.php');
}else{
    if (isset($params['service_id']) && $params['service_id']) {
        $service_id = $params['service_id'];
        $service_mapping=pdo_get_mapping($pdo,$service_id);
        if($service_mapping){
            foreach($service_mapping as $v){
                $qlp['instance_id'][]=$v['instance_id'];
            }
        }
    
        $qlp['service_id']=array(
            $service_id
        );
        $queue_list=get_queue_list_complex2($pdo,0,300,$qlp);
    } else {
        $queue_list = get_queue_list_complex($pdo);   
    }  
}

$template = 'queue';
require_once './libraries/decorator.inc.php';
