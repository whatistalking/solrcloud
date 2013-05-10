<?php
require_once './libraries/common.lib.php';

$action = isset($params['action'])?$params['action']:"";
function service_redirect($msg=''){
    $referer_url = $_SERVER['HTTP_REFERER'];
    ($referer_url)?msg_redirect($referer_url,$msg):msg_redirect("report.php",$msg);
}
$req_url = $cfg['sc_url'].$_SERVER["REQUEST_URI"];
$def_url = $cfg['sc_url'].'/report.php';
switch($action){
    
    case 'global':

    $template = 'report_global';
    break;
    
    case 'delete':
    $u_info = check_login($req_url);
    $uname = get_username();
    $id = isset($params['r_id'])? $params['r_id'] : 0;
    if($id){
        $user_setting = pdo_get_user_setting($pdo, $uname);
        $report_setting = json_decode($user_setting['report_setting'], true);
        unset($report_setting[$id]);
        $new_setting = json_encode($report_setting);
        $res = update_report_setting($pdo,$uname,$new_setting);
    }
    msg_redirect("report.php");
    break;

    default:
    $u_info = check_login($req_url);
    $uname = get_username();   
    $id = isset($params['r_id'])? $params['r_id'] : 0;
    
    $from1 = date('Y-m-d');
    $to1 = date('Y-m-d');
    $from2 = date('Y-m-d', strtotime('-1 day'));
    $to2 = date('Y-m-d', strtotime('-1 day'));
    
    $user_setting = pdo_get_user_setting($pdo, $uname);
    $report_setting = array();
    if($user_setting['report_setting']){
        $report_setting = json_decode($user_setting['report_setting'], true);
    }
    
    $template = 'report';
    break;
}

require_once './libraries/decorator.inc.php';
