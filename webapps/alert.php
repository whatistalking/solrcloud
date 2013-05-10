<?php
require_once './libraries/common.lib.php';

$action = isset($params['action'])?$params['action']:"";
function service_redirect($msg=''){
    $referer_url = $_SERVER['HTTP_REFERER'];
    ($referer_url)?msg_redirect($referer_url,$msg):msg_redirect("alert.php",$msg);
}
$req_url = $cfg['sc_url'].$_SERVER["REQUEST_URI"];
$def_url = $cfg['sc_url'].'/alert.php';

switch($action){

    case 'user':
        $u_info = check_login($req_url);
        $uname = get_username();
        $user_setting = pdo_get_user_setting($pdo, $uname);
        if (empty($user_setting)) {
            $ret = default_user_setting($pdo, $uname);
            if ($ret) {
                $token = get_token();
                $oauth_url = $cfg['oauth']['oauth_url'];
                $info = get_info_from_oauth($token, $oauth_url);
                if ($info) {
                    $arr = json_decode($info, true);
                    $email = $arr['email'];
                    $input = array('mobile'=>'', 'email'=>$email);
                    update_user_setting($pdo, $uname, $input);
                }    
            }
            $user_setting = pdo_get_user_setting($pdo, $uname);
        }
        $template = 'alert_setting';
        break;
    case 'user_edit':
        $u_info = check_login($def_url."?action=user");
        $username = $params['username'];
        $mobile = $params['phone'];
        $email = $params['email'];
        $input = array('mobile'=>$mobile, 'email'=>$email);
        update_user_setting($pdo, $username, $input);
        msg_redirect("alert.php?action=user");
        break;
    case 'edit':
        $u_info = check_login($req_url); 
        $alert_id = $params['alert_id'];
        $alert_info = pdo_get_alert_by_id($pdo,$alert_id);
        $alert_info['alert_setting'] = json_decode($alert_info['alert_setting'], true);
        $template = 'alert_edit';
        break;
    case 'do_edit':
        $u_info = check_login($req_url);
        $alert_id = $params['alert_id'];   
        $alert_attr['hwm'] = $params['hwm'];
        $alert_attr['lwm'] = $params['lwm'];
        $alert_attr['keeptime'] = $params['keeptime'];
        $alert_attr['percent'] = $params['percent'];
        $alert_attr['period'] = $params['period'];
        $alert_str = json_encode($alert_attr);
        $res = update_alert_info($pdo, $alert_id, $alert_str);  
        msg_redirect("alert.php");
        break;
    case 'alert':
        $u_info = check_login($def_url);
        $alert_id = $params['alert_id'];
        $ret = able_alert_info($pdo, $alert_id);
        msg_redirect("alert.php");
        break;
    case 'donot':
        $u_info = check_login($def_url);
        $alert_id = $params['alert_id'];
        $ret = disable_alert_info($pdo, $alert_id);
        msg_redirect("alert.php");
        break;        
    case 'delete':
        $u_info = check_login($def_url);
        $alert_id = $params['alert_id'];
        $ret = delete_alert_info($pdo, $alert_id);
        msg_redirect("alert.php");
        break;
    default:
        $u_info = check_login($req_url);
        $uname = get_username();
        $alert_info = pdo_get_alert_info_list($pdo, $uname);
        $alert_info_list = array();
        if(!empty($alert_info)){
            foreach($alert_info as $row){
                $row['alert_setting'] = json_decode($row['alert_setting'], true);
                $alert_info_list[$row["target_type"]][$row["target_id"]][] = $row;
            }
        }
        $target_list = array();
        if(isset($alert_info_list['service'])){
            //service
            $service_list = get_service_list($pdo);
            foreach ($service_list as $s){
                $target_list['service'][$s['service_id']] = $s['service_name'];
            }
        }
        if(isset($alert_info_list['instance'])){
            //instance
            $instance_list = get_instance_list($pdo);
            foreach ($instance_list as $i){
                $target_list['instance'][$i['instance_id']] = $i['port_num'];
            }
        }
        if(isset($alert_info_list['host'])){
            //host
            $host_list = get_host_list($pdo);
            foreach ($host_list as $h){
                $target_list['host'][$h['host_id']] = $h['host_name'];
            }
        }   
        $target_type = array('service' => 'Service', 'instance' => 'Instance', 'host' => 'Host');
        $template = 'alert';
        break;
}

require_once './libraries/decorator.inc.php';
