<?php
require_once './libraries/common.lib.php';

$service_id = @intval($params['service_id']);
$action = @$params['action'];

switch($action){ 
    case 'add':
        $type_list = get_schema_type_list($pdo);              
        $template = 'schema_fields_add';
    break;
    case 'edit':
        $name = $params['name'];        
        $type_list = get_schema_type_list($pdo);
        $field = get_schema_field($pdo,$service_id,$name);
        if(!$field)msg_redirect('schema_fields.php?action=list&service_id='.$service_id,'The field is not exist!');
        $support_types = get_schema_field_support_options($pdo,$field['type']);                      
        $template = 'schema_fields_edit';
    break;  
    case 'update_field':
        $service_attr['schema_type'] = $params['schema_type'];
        $res = update_service_schema_type($pdo, $service_id, $service_attr);
        if($params['schema_type'] ==1){
            $field['name'] = $params['name'];
            $field['type'] = $params['type'];
            $field['indexed'] = (!isset($params['indexed']) || (isset($params['indexed']) && $params['indexed']=='false'))? 'false' : 'true';
            $field['stored'] = (!isset($params['stored']) || (isset($params['stored']) && $params['stored']=='false'))? 'false' : 'true';
            if(isset($params['default'])&&$params['default'])$field['default'] = $params['default'];
            if(isset($params['required'])&&$params['required'])$field['required'] = $params['required'];
            if(isset($params['compressed'])&&$params['compressed'])$field['compressed'] = $params['compressed'];
            if(isset($params['multiValued'])&&$params['multiValued'])$field['multiValued'] = $params['multiValued'];
            $is_dynamic_field =(boolean)isset($params['dynamic_field']);
            update_schema_fields($pdo,$field,$service_id,$is_dynamic_field);
        } else {
            $solr_schema_attr = $params['hand_config_schema'];
            update_schema($pdo,$service_id,$solr_schema_attr);
        }
//      msg_redirect('schema_fields.php?action=list&service_id='.$service_id);
        msg_redirect('service_detail.php?service_id='.$service_id.'&tab=3');
    break;
    case 'delete':
        $name = $params['name'];
        $schema_config = pdo_get_solr_schema($pdo, $service_id);
        $schema_config = json_decode($schema_config['schema_json'],true);
        if($schema_config['uniqueKey']==$name)msg_redirect('back',"This field is used by \'uniqueKey\'");
        if($schema_config['defaultSearchField']==$name)msg_redirect('back',"This field is used by \'defaultSearchField\'");
        delete_schema_fields($pdo,$name,$service_id);
//        msg_redirect('schema_fields.php?action=list&service_id='.$service_id);
        msg_redirect('service_detail.php?service_id='.$service_id.'&tab=3');
    break;
    case 'list':
        $template = 'schema_fields_list';
        $schema_config = pdo_get_solr_schema($pdo, $service_id);
        $schema_config['config'] = json_decode($schema_config['schema_json'],true);
    break;       
}


$current_nav='service';
require_once './libraries/decorator.inc.php';