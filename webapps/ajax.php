<?php
require_once './libraries/common.lib.php';
header("Content-type: text/html; charset=utf-8");
$action = @$params['action'];
$name = @$params['name'];

switch($action){
    case 'get_schema_field_support_options':
        $options = get_schema_field_support_options($pdo,$name);
        echo json_encode($options);
    break;
    case 'createreport':
    	include_once('../scripts/status.get.php');
    	echo json_encode(array('status'=>'ok'));
    break;
    case 'edit_schema':
        $service_id = $params['service_id'];
        $name = $params['name'];
        $type_list = get_schema_type_list($pdo);
        $field = get_schema_field($pdo,$service_id,$name);
        //if(!$field)msg_redirect('schema_fields.php?action=list&service_id='.$service_id,'The field is not exist!');
        $support_types = get_schema_field_support_options($pdo, $field['type']);
        $res = array('type_list' => $type_list,
                     'field' => $field,
                     'support_types' => $support_types);
        echo json_encode($res);     
    break;
    case 'get_select':
        global $cfg;
        $service_list = get_service_list($pdo);
        $host_list = get_host_list($pdo);
        $report = $cfg['alert'];
        $res = array('l_service' => $service_list,
                'l_host' => $host_list,
                'report' => $report);
        echo json_encode($res);
        break;
    case 'modify_report_seeting':
        
        $update_id = $params['update_id'];
        if(empty($update_id)|| $update_id == '0') break;
        $uname = get_username();
        $report_name = $params['report_name'];
        
        $user_setting = pdo_get_user_setting($pdo, $uname);
        if(!empty($user_setting['report_setting'])){
            echo $user_setting['report_setting'];
            $old_setting = json_decode($user_setting['report_setting'], true);
            $old_setting[$update_id]['name'] = $report_name;
        }   
        $new_setting = json_encode($old_setting);
        $res = update_report_setting($pdo,$uname,$new_setting);
        echo $new_setting;
        break;
    case 'save_select':
        $report_name = $params['report_name'];
        $uname = get_username();
        $cookie = $params['setting'];
        $update_id = $params['update_id'];
        
        $setting = array();
        if(!empty($cookie)){
            $cook_arr = json_decode($cookie, true);
            
            if(!empty($cook_arr['target_s'])){
                foreach($cook_arr['target_s'] as $row){
                    $setting['service']['target'][] = $row;
                }
            }else{
                $setting['service']['target'] = array();
            }
            
            if(!empty($cook_arr['target_h'])){
                foreach($cook_arr['target_h'] as $row){
                    $setting['host']['target'][] = $row;
                }
            }else{
                $setting['host']['target'] = array();
            }
            
            if(!empty($cook_arr['report_s'])){
                foreach($cook_arr['report_s'] as $row){
                    $setting['service']['report'][] = $row;
                }
            }else{
                $setting['service']['report'] = array();
            }
            
            if(!empty($cook_arr['report_h'])){
                foreach($cook_arr['report_h'] as $row){
                    $setting['host']['report'][] = $row;
                }
            }else{
                $setting['host']['report'] = array();
            }
            
            if(!empty($cook_arr['report_g'])){
                foreach($cook_arr['report_g'] as $row){
                    $setting['global']['report'][] = $row;
                }
            }else{
                $setting['global']['report'] = array();
            }    
        }
        $tmp = array();
        $tmp['id'] = 0;
        $tmp['name'] = $report_name;
        $tmp['setting'] = $setting;
        
        $user_setting = pdo_get_user_setting($pdo, $uname);
        if(!empty($user_setting['report_setting'])){
            $old_setting = json_decode($user_setting['report_setting'], true);
        }else{
            $old_setting = array();
        }
        
        if($update_id != '0'){
            $tmp['id'] = $update_id;
            $old_setting[$update_id] = $tmp;
        }else{
            if(!empty($old_setting)){
                $key = array_keys($old_setting);
                rsort($key);
                $new_id = $key[0]+1;          
                $tmp['id'] = $new_id;
                $old_setting[$new_id] = $tmp;
            }else{ 
                $tmp['id'] = 1;
                $old_setting['1'] = $tmp;
            }
        }
        
        $new_setting = json_encode($old_setting);
        $res = update_report_setting($pdo,$uname,$new_setting);
        $result = array();
        if($res){
            $user_setting = pdo_get_user_setting($pdo, $uname);
            $report_setting = array();
            if($user_setting['report_setting']){
                $result['res'] = 'ok';
                $result['id'] = $tmp['id'];
                $result['data'] = json_decode($user_setting['report_setting'], true);
            } else {
                $result['res'] = 'no-data';
                $result['id'] = $tmp['id'];
            }
        }else{
           $result['res'] = 'error';
        }
        echo json_encode($result);
    break;
    case 'get_report_setting':
        $uname = get_username();
        $setting_id = $params['setting_id'];   
        $user_setting = pdo_get_user_setting($pdo, $uname);
        $report_setting = array();
        if($user_setting['report_setting']){
            $report_setting = json_decode($user_setting['report_setting'], true);
        }
        $result = array();
        if(isset($report_setting[$setting_id])){
            $result['res'] = 'ok';
            $result['data'] = $report_setting[$setting_id];
        }else{
            $result['res'] = 'error';           
        }
        echo json_encode($result);
    break;
    case 'add_alert':
        global $cfg;
        $uname = get_username();
        $alert_id = $params['alert_id'];
        $target_id = $params['target_id'];
        $alert_info = $cfg['alert'][$alert_id];
        
        $result = array();
        if(!empty($uname)){   
            $info = array();
            $info['username'] = $uname;
            $info['target_type'] = $alert_info['target_type'];
            $info['target_id'] = $target_id;
            $info['alert_type_id'] = $alert_id;
            $info['alert_type_name'] = $alert_info['name'];
            $info['alert_setting'] = json_encode($alert_info['default']);
            $res = insert_alert_info($pdo, $info);     
            $result['res'] = 'ok';
        } else { 
            $result['res'] = 'error';   
        }
        echo json_encode($result);
    break;
}
