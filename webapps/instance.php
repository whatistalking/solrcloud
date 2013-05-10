<?php
require_once './libraries/common.lib.php';
$action = isset($params['action'])?$params['action']:"";

function instance_redirect($msg=''){
    $referer_url = $_SERVER['HTTP_REFERER'];
    ($referer_url)?msg_redirect($referer_url,$msg):msg_redirect("instance.php",$msg);
}
$req_url = $cfg['sc_url'].$_SERVER["REQUEST_URI"];
$def_url = $cfg['sc_url'].'/instance.php';

switch($action){
    case "add":   
        $u_info = check_login($req_url);
        $sc_url = $cfg['sc_url'];
        $service_id = @intval($params['service_id']);
        $template = 'instance_add';
        #$host_list = get_host_list($pdo); 
        $host_list = get_host_list_instance_add($pdo, get_host_list($pdo));
        if(!$service_id) {
            $service_list_tmp = get_service_list($pdo);
        } else {
            $service_list_tmp[] = pdo_get_service_byid($pdo, $service_id);
        }
        $service_list = array();
        foreach($service_list_tmp as $row){
            $service_list[$row['department']][] = $row;
        }
        $depart = array();
        foreach($service_list as $key => $value){
            $depart[] = $key;
        }
    break;
    case "do_add":
        $username = check_login($req_url);
        if (!isset($params['service_id']) || $params['service_id'] == '') {
            msg_redirect("back",'Please Bind Service!');
        }
        $service_id = $params['service_id'];
        $service_info = pdo_get_service_byid($pdo, $service_id);
        $port_num = get_host_max_port_num($pdo, $params['host_id']);
        if(!$port_num){
            //$port_num = 7701;
            $port_num = 7801;
        } else {
            $port_num = $port_num+1;
        }
        if(get_instance_by_port($pdo,$params['host_id'],$port_num)){
            msg_redirect("back",'The port num has been exist in this host!');
        }
        $instance_attr['host_id'] = $params['host_id'];
        $instance_attr['solr_version'] = $service_info['solr_version'];
        $instance_attr['port_num'] = $port_num;
        $instance_attr['use_memory'] = $params['use_memory'];
        $instance_id = insert_instance($pdo, $instance_attr);
        if($instance_id){
            $jetty_attr['jetty.port'] = $port_num;
            $jetty_attr['instance_id'] = $instance_id;
            insert_jetty_config($pdo, $jetty_attr);
        }
        if(lock_instance($pdo,$instance_id)){
            insert_action_queue($pdo,INSTANCE_DEPLOY,$instance_id,$username);
            insert_action_queue($pdo,INSTANCE_RECONFIGURE_JETTY,$instance_id,$username);
        }    
        //bind service      
        $lb_weight = $params['lb_weight'];
        update_instance_lb_weight($pdo,$instance_id,$lb_weight);
        $max_fails = $params['max_fails'];
        update_instance_max_fails($pdo,$instance_id,$max_fails);

        add_instance_into_service($pdo,$instance_id,$service_id,$username);
        
        if (isset($params['f']) && $params['f'] == 's') {
            msg_redirect("service_detail.php?service_id=".$service_id."&tab=2");
        } else {
            msg_redirect("instance.php?from=new#t".$instance_id);
        }
    break;
    case "edit":
        $u_info = check_login($req_url);
        $template = 'instance_edit';
        $instance_id = $params['instance_id'];
        $instance_info = pdo_get_instance_byid($pdo,$instance_id);
    break;
    case "do_edit":
        $username = check_login($def_url);
        $instance_id = $params['instance_id'];
        if(update_instance_lb_weight($pdo,$instance_id,$params['lb_weight']) || update_instance_max_fails($pdo,$instance_id,$params['max_fails'])){
            reload_service_by_instance($pdo, $instance_id, $username);
        }
        if(update_instance_use_memory($pdo,$instance_id,$params['use_memory'])){
            //TODO
        }
        msg_redirect("instance.php");
    break;
    case 'stop':
        $username = check_login($def_url);
        $instance_id = $params['instance_id'];
        if(lock_instance($pdo,$instance_id)){
            insert_action_queue($pdo,INSTANCE_STOP,$instance_id,$username);
        }
        instance_redirect();
    break;
    case 'start':
        $username = check_login($def_url);
        $instance_id = $params['instance_id'];
        if(lock_instance($pdo,$instance_id)){
            insert_action_queue($pdo,INSTANCE_START,$instance_id,$username);
        }
        instance_redirect();
    break;
    case 'restart':
        $username = check_login($def_url);
        $instance_id = $params['instance_id'];
        if(lock_instance($pdo,$instance_id)){
            insert_action_queue($pdo,INSTANCE_RECONFIGURE_JETTY,$instance_id,$username);
            insert_action_queue($pdo,INSTANCE_RECONFIGURE_SCHEMA,$instance_id,$username);
            insert_action_queue($pdo,INSTANCE_RECONFIGURE_SOLRCONF,$instance_id,$username);
            insert_action_queue($pdo,INSTANCE_STOP,$instance_id,$username);
            insert_action_queue($pdo,INSTANCE_START,$instance_id,$username);
        }
        instance_redirect();
    break;
    case 'deploy':
        $username = check_login($def_url);
        $instance_id = $params['instance_id'];
        if(lock_instance($pdo,$instance_id)){
            insert_action_queue($pdo,INSTANCE_DEPLOY,$instance_id,$username);
            insert_action_queue($pdo,INSTANCE_RECONFIGURE_JETTY,$instance_id,$username);
        }
        instance_redirect();
    break;
    case 'bind':
        $u_info = check_login($req_url);
        $template = 'instance_bind';
        $instance_id = $params['instance_id'];
        if(isset($params['service_id'])) {
            $service_list[] = pdo_get_service_byid($pdo, $params['service_id']);
        } else {
            $service_list = get_service_list($pdo);
        }
    break;
    case 'do_bind':
        $username = check_login($req_url);
        $instance_id = $params['instance_id'];
        $service_id = $params['service_id'];
        $lb_weight = $params['lb_weight'];
        update_instance_lb_weight($pdo,$instance_id,$lb_weight);
        add_instance_into_service($pdo,$instance_id,$service_id,$username);   
        if (isset($params['f']) && $params['f'] == 's') {
            msg_redirect("service_detail.php?service_id=".$service_id."&tab=2");
        } else {
            msg_redirect("instance.php");
        }
        
        msg_redirect("service_detail.php?service_id=".$service_id);
    break;
    case 'unbind':
        $username = check_login($def_url);
        $instance_id = $params['instance_id'];
        $instance_info = pdo_get_instance_byid($pdo,$instance_id);
        $service_info = pdo_get_service_byid($pdo,$instance_info['service_id']);
        if($service_info['service_status']==1){
            if($instance_info['writable']&&$service_info['service_status']){
                msg_redirect("back",'Can not unbind master instance from running service!');
            }
            if($instance_info["writable"]==1||$instance_info["readable"]==1)
                msg_redirect("back",'Can not unbind instance when it\\\'s writable or readable!');
        }
        remove_instance_from_service($pdo,$instance_id,$username);
        instance_redirect();
    break;
    case 'writable':
        $username = check_login($def_url);
        $instance_id = $params['instance_id'];
        $service_mapping = pdo_get_mapping_by_instance_id($pdo, $instance_id);
        $service_info = pdo_get_service_byid($pdo,$service_mapping['service_id']);
        /*非zk的只能有一个writable*/
        if(!isset_zk_service($pdo, $service_mapping['service_id'])){
            $writbale_instance = get_instance_list_complex($pdo,array('service_id'=>$service_mapping['service_id'],'writable'=>'1'));
            if($writbale_instance)msg_redirect("back",'Writable instance should be unique before 4.0!');
        }
        change_instance_writable($pdo,$instance_id);
        reload_service_by_instance($pdo, $instance_id, $username);
        instance_redirect();
    break;
    case 'unwritable':
        $username = check_login($def_url);
        $instance_id = $params['instance_id'];
        $service_mapping = pdo_get_mapping_by_instance_id($pdo, $instance_id);
        $service_info = pdo_get_service_byid($pdo,$service_mapping['service_id']);
        
        $writbale_instance = get_instance_list_complex($pdo,array('service_id'=>$service_mapping['service_id'],'writable'=>'1'));
        $writbale_instance_count = count($writbale_instance);
        if($writbale_instance_count<2)msg_redirect("back",'At least one writable instance in one service!');
        
        change_instance_unwritable($pdo,$instance_id);
        reload_service_by_instance($pdo, $instance_id, $username);
        instance_redirect();
    break;
    case 'readable':
        $username = check_login($def_url);
        $instance_id = $params['instance_id'];
        change_instance_readable($pdo,$instance_id);
        reload_service_by_instance($pdo, $instance_id, $username);
        instance_redirect();
    break;
    case 'unreadable':
        $username = check_login($def_url);
        $instance_id = $params['instance_id'];
        $service_mapping = pdo_get_mapping_by_instance_id($pdo, $instance_id);
        $service_info = pdo_get_service_byid($pdo,$service_mapping['service_id']);
        if($service_info['service_status']==1){
            $readbale_instance = get_instance_list_complex($pdo,array('service_id'=>$service_mapping['service_id'],'readable'=>'1'));
            $readbale_instance_count = count($readbale_instance);
            if($readbale_instance_count<2)msg_redirect("back",'At least one readable instance in one service!');
        }
        change_instance_unreadable($pdo,$instance_id);
        reload_service_by_instance($pdo, $instance_id, $username);
        instance_redirect();
    break;
    case 'list_ajax':
        $mem = $params['mem'];
        $service_id = isset($params['s_id'])? $params['s_id'] : 0;
        $host_list_select = get_host_list_select($pdo, $mem, $service_id);    
        echo json_encode($host_list_select);
        exit;      
    break;
    default:
        $service_id=@intval($params['service_id']);
        $instance_list = get_instance_list_complex($pdo,array('service_id'=>$service_id, 'with_unbind'=>'1'));
        $host_list = get_host_list($pdo);
        $host_list_tmp = array();
        if($host_list){
	        foreach($host_list as $v){
	            $host_list_tmp[$v['host_id']]=$v;
	        }
        }
        $host_list = $host_list_tmp;
        $from = isset($params['from'])? $params['from'] : '';
        $template = 'instance';
    break;
}

require_once './libraries/decorator.inc.php';
