<?php
require_once './libraries/common.lib.php';

$action = isset($params['action'])?$params['action']:"";
function service_redirect($msg=''){
    $referer_url = $_SERVER['HTTP_REFERER'];
    ($referer_url)?msg_redirect($referer_url,$msg):msg_redirect("service.php",$msg);
}
$req_url = $cfg['sc_url'].$_SERVER["REQUEST_URI"];
$def_url = $cfg['sc_url'].'/service.php';

switch($action){

    case 'add':
        $u_info = check_login($req_url);
        $template = 'service_add';
        break;
    case 'do_add':
        $username = check_login($req_url);
        if(pdo_get_service($pdo, $params['service_name'])){
            msg_redirect("back",'This service name has been exist!');
        }
        
        $service_attr['service_name'] = $params['service_name'];
        $service_attr['url_regex'] = '/'.$params['service_name'].'/';
        $service_attr['description'] = $params['description'];
        $service_attr['hash_type'] = $params['hash_type'];
        $service_attr['config_type'] = $params['config_type'];
        $service_attr['optimize_time'] = $params['optimize_time'];
        $service_attr['department'] = $params['department'];
        $service_attr['solr_version'] = $params['solr_version'];
        $service_id = insert_service($pdo,$service_attr);

        if($service_id){
        	if($params['config_type'] == 1){
	            $solr_config_attr['maxDocs'] = intval($params['maxDocs']);
	            $solr_config_attr['maxTime'] = intval($params['maxTime']);
	            $solr_config_attr['pollInterval'] = $params['pollInterval'];
	            $solr_config_attr['config_type'] = $params['config_type'];

        	}else{
        		$solr_config_attr['config_json'] = $params['hand_config'];
                $solr_config_attr['service_id'] = $service_id;
                $solr_config_attr['config_type'] = $params['config_type'];
        	}
        	$solr_config_attr['service_id'] = $service_id;
            insert_solr_config($pdo, $solr_config_attr);
            
            init_schema($pdo,$service_id);
            
            msg_redirect('service.php?action=schema_add&service_id='.$service_id);
            //msg_redirect('service_detail.php?service_id='.$service_id);
        }else{
            msg_redirect('service.php','add service failed');
        }
        break;
    case 'edit':
        $u_info = check_login($req_url);
        $template = 'service_edit';
        $service_id = $params['service_id'];
        $service_info = pdo_get_service_byid($pdo,$service_id);
        $fields_list = get_schema_fields_list($pdo,$service_id);
        $solr_config = pdo_get_solr_config($pdo, $service_id);
        if($service_info['config_type'] == 1){
        	$solr_config = json_decode($solr_config['config_json'],true);
        }else{
        	$solr_config = $solr_config['config_json'];
        }
        $solr_schema = pdo_get_solr_schema($pdo, $service_id);
		if($service_info['schema_type'] == 1){
            $solr_schema = json_decode($solr_schema['schema_json'],true);
        }else{
            $solr_schema = $solr_schema['schema_json'];
        }
        $solr_dataimport = pdo_get_dataimport($pdo, $service_id);

        break;
    case 'do_edit':
        $service_id = $params['service_id'];
        //$check_service_info = pdo_get_service($pdo, $params['service_name']);
        /*if($check_service_info){
            $service_info = pdo_get_service_byid($pdo,$service_id);
            if($check_service_info['service_id']!=$service_info['service_id']){
                msg_redirect("back",'This service name has been exist!');
            }
        }*/
        $service_attr['description'] = $params['description'];
        $service_attr['hash_type'] = $params['hash_type'];
        $service_attr['optimize_time'] = $params['optimize_time'];
        $service_attr['config_type'] = $params['config_type'];
        $service_attr['schema_type'] = $params['schema_type'];
		$service_attr['schema_type'] = $params['schema_type'];
        $res = update_service($pdo,$service_id,$service_attr);
        if($params['config_type'] == 1){
	        $solr_config_attr['maxDocs'] = intval($params['maxDocs']);
	        $solr_config_attr['maxTime'] = intval($params['maxTime']);
	        $solr_config_attr['pollInterval'] = $params['pollInterval'];
        }else{
        	$solr_config_attr = $params['hand_config'];
        }
        update_solr_config($pdo, $service_id,$solr_config_attr);

		if($params['schema_type'] ==1){
            $solr_schema_attr['uniqueKey'] = $params['uniqueKey'];
            $solr_schema_attr['defaultSearchField'] = $params['defaultSearchField'];
            $solr_schema_attr['defaultOperator'] = $params['defaultOperator'];
        }else{
            $solr_schema_attr = $params['hand_config_schema'];
        }
        update_schema($pdo,$service_id,$solr_schema_attr);
        
        update_dataimport($pdo,$service_id,$params['dataimport']);
        msg_redirect('service_detail.php?service_id='.$service_id);
        break;
    case 'stop':
        $username = check_login($def_url);
        $service_id = $params['service_id'];
        if(lock_service($pdo,$service_id)){
            stop_service($pdo,$service_id);
            
       	 	foreach ($cfg['lb_host'] as $key=>$v){
                $target_id = $v;
                insert_action_queue_new($pdo,SERVICE_RECONFIGURE,$service_id,$target_id,$username);
                insert_action_queue_new($pdo,SERVICE_RELOAD,$service_id,$target_id,$username);
            }

            if ($cfg["puppet"]["enable"]) {
                insert_action_queue($pdo,XMLRPC_RECONFIGURE,0,$username);
                insert_action_queue($pdo,XMLRPC_RELOAD,0,$username);
            }
        }
        service_redirect();
    break;
    case 'start':
        $username = check_login($def_url);
        $service_id = $params['service_id'];
        if(lock_service($pdo,$service_id)){
            start_service($pdo,$service_id);
        	foreach ($cfg['lb_host'] as $key=>$v){
                $target_id = $v;
                insert_action_queue_new($pdo,SERVICE_RECONFIGURE,$service_id,$target_id,$username);
                insert_action_queue_new($pdo,SERVICE_RELOAD,$service_id,$target_id,$username);
            }

            if ($cfg["puppet"]["enable"]) {
                insert_action_queue($pdo,XMLRPC_RECONFIGURE,0,$username);
                insert_action_queue($pdo,XMLRPC_RELOAD,0,$username);
            }
        }
        service_redirect();
    break;
    case 'reload':
        $username = check_login($def_url);
        $service_id = $params['service_id'];
        if(lock_service($pdo,$service_id) || $service_id==0){
        	foreach ($cfg['lb_host'] as $key=>$v){
                $target_id = $v;
                insert_action_queue_new($pdo,SERVICE_RECONFIGURE,$service_id,$target_id,$username);
                insert_action_queue_new($pdo,SERVICE_RELOAD,$service_id,$target_id,$username);
            }

            if ($cfg["puppet"]["enable"]) {
                insert_action_queue($pdo,XMLRPC_RECONFIGURE,0,$username);
                insert_action_queue($pdo,XMLRPC_RELOAD,0,$username);
            }
        }
        service_redirect();
    break;
    case 'restart_all':
        $username = check_login($def_url);
        $service_id = $params['service_id'];
        $service_mapping = pdo_get_mapping($pdo, $service_id);
        if($service_mapping){
            foreach($service_mapping as $v){
                $instance_id = $v['instance_id'];
                if(lock_instance($pdo,$instance_id)){
                    insert_action_queue($pdo,INSTANCE_RECONFIGURE_SCHEMA,$instance_id,$username);
                    insert_action_queue($pdo,INSTANCE_RECONFIGURE_SOLRCONF,$instance_id,$username);
                    insert_action_queue($pdo,INSTANCE_STOP,$instance_id,$username);
                    insert_action_queue($pdo,INSTANCE_START,$instance_id,$username);
                }
            }
        }
        service_redirect();
    break;
    case 'reconfigure_master':
        $username = check_login($def_url);
        $service_id = $params['service_id'];
        $master_info = pdo_get_master_complex($pdo,$service_id);
        $instance_id = $master_info['instance_id'];
        if($instance_id && lock_instance($pdo,$instance_id)){
            insert_action_queue($pdo,INSTANCE_RECONFIGURE_SCHEMA,$instance_id,$username);
            insert_action_queue($pdo,INSTANCE_RECONFIGURE_SOLRCONF,$instance_id,$username);
            insert_action_queue($pdo,INSTANCE_STOP,$instance_id,$username);
            insert_action_queue($pdo,INSTANCE_START,$instance_id,$username);
        }
        service_redirect();
    break;
    case 'schema_add':
        $u_info = check_login($req_url);
        $service_id = $params['service_id'];
        $type_list = get_schema_type_list($pdo);
        $service_info = pdo_get_service_byid($pdo,$service_id);
        $solr_schema = pdo_get_solr_schema($pdo,$service_id);
        if($service_info['schema_type'] == 1){
            $solr_schema = json_decode($solr_schema['schema_json'],true);
        } elseif ($service_info['schema_type'] == 2) {
            $schema_info=$solr_schema['schema_json'];
            $doc=new DomDocument();
            $doc->loadXML($schema_info);
            $xpath=new DomXPath($doc);
            $query='//field';
            $nodelist=$xpath->query($query);
            foreach($nodelist as $key=>$node){
                foreach($node->attributes as $attr){
                    $menu[$key][$attr->name]=$attr->value;
                }
            }
            $solr_schema['fields']=$menu;
            
	        $query='//dynamicField';
	        $nodelist=$xpath->query($query);
	        foreach($nodelist as $key=>$node){
	            foreach($node->attributes as $attr){
	                $dymenu[$key][$attr->name]=$attr->value;
	            }
	        }
	        $solr_schema['dynamicFields']=$dymenu;
        
            $query='//defaultSearchField';
            $nodelist=$xpath->query($query);
            foreach($nodelist as $key=>$node){
                $solr_schema['defaultSearchField']=$node->nodeValue;
            }
        
            $query='//uniqueKey';
            $nodelist=$xpath->query($query);
            foreach($nodelist as $key=>$node){
                $solr_schema['uniqueKey']=$node->nodeValue;
            }
        
            $query='//solrQueryParser';
            $nodelist=($xpath->query($query));
            foreach($nodelist as $key=>$node){
                foreach($node->attributes as $attr){
                    if('defaultOperator'==$attr->name){
                        $solr_schema['defaultOperator']=$attr->value;
                    }
                }
            }
        }        
        $template = 'service_schema';
    break;
    case 'update_field':
        $service_id = $params['service_id'];   
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
        msg_redirect('service.php?action=schema_add&service_id='.$service_id);
        break;
    case 'schema_edit':
        $service_id = $params['service_id'];
        $name = $params['name'];        
        $type_list = get_schema_type_list($pdo);
        $field = get_schema_field($pdo,$service_id,$name);
        if(!$field)msg_redirect('schema_fields.php?action=list&service_id='.$service_id,'The field is not exist!');
        $support_types = get_schema_field_support_options($pdo,$field['type']);                   
        $template = 'schema_fields_edit';
        break;        
    case 'schema_delete':
        $service_id = $params['service_id'];
        $name = $params['name'];
        $schema_config = pdo_get_solr_schema($pdo, $service_id);
        $schema_config = json_decode($schema_config['schema_json'], true);
        if($schema_config['uniqueKey']==$name)msg_redirect('back',"This field is used by \'uniqueKey\'");
        if($schema_config['defaultSearchField']==$name)msg_redirect('back',"This field is used by \'defaultSearchField\'");
        delete_schema_fields($pdo,$name,$service_id);       
        msg_redirect('service.php?action=schema_add&service_id='.$service_id);
        break;
    case 'schema_done':
        $service_id = $params['service_id'];
        $instance_list = get_instance_list_complex($pdo,array('service_id' => $service_id));
        $service_info = pdo_get_service_byid($pdo, $service_id);
        $host_list = change_array_key(get_host_list($pdo),'host_id');
        
        if (!$instance_list){
            //bind instance auto
            $username = check_login($req_url);
            /*zk*/
            $zk_host = get_host_name($pdo,$cfg['zk_host_id']);
            if($zk_host){
                $zk = $zk_host.":10".$service_id;
                if(set_zk($pdo, $service_id, $zk)){
                    insert_action_queue_new($pdo,CREATE_ZK,$service_id,$cfg['zk_host_id'],$username);
                }
            }
            $host_list_select = get_host_list_select($pdo, '1024');
            $host_id = $host_list_select[0]['host_id'];
            $port_num = get_host_max_port_num($pdo, $host_id);
            if(!$port_num){
                $port_num = 7801;
            } else {
                $port_num = $port_num + 1;
            }
            if(get_instance_by_port($pdo, $host_id, $port_num)){
                msg_redirect("back",'The port num has been exist in this host!');
            }
            $instance_attr['host_id'] = $host_id;
            $instance_attr['solr_version'] = $service_info['solr_version'];
            $instance_attr['port_num'] = $port_num;
            $instance_attr['use_memory'] = $cfg['auto_instance_mem'];
            $instance_id = insert_instance($pdo, $instance_attr);
            if($instance_id){
                $jetty_attr['jetty.port'] = $port_num;
                $jetty_attr['instance_id'] = $instance_id;
                insert_jetty_config($pdo, $jetty_attr);
            }
            if(lock_instance($pdo, $instance_id)){
                insert_action_queue($pdo,INSTANCE_DEPLOY,$instance_id,$username);
                insert_action_queue($pdo,INSTANCE_RECONFIGURE_JETTY,$instance_id,$username);
            }
        
            //bind service
            $lb_weight = '';
            update_instance_lb_weight($pdo,$instance_id,$lb_weight);
            add_instance_into_service($pdo,$instance_id,$service_id,$username);
            
            $instance_list = get_instance_list_complex($pdo,array('service_id' => $service_id));
        }

        $template = 'service_done';
        //msg_redirect('service_detail.php?service_id='.$service_id);
        break;
    case 'change_mode'://service.php?action=change_mode&service_id=$service_id
		$service_id = $params['service_id'];
		$username = check_login($req_url);
		$service_info = pdo_get_service_byid($pdo, $service_id);
		$zk_host = get_host_name($pdo,$cfg['zk_host_id']);
        if($zk_host && $service_info){
			if($service_info['zk']){
                $zk = '';
			}else{
				$zk = $zk_host.":10".$service_id;
			}
			set_mode($pdo, $service_id, $zk);
            if($zk){
                insert_action_queue_new($pdo,CREATE_ZK,$service_id,$cfg['zk_host_id'],$username);
            }
        }
		$service_list = get_service_list($pdo);
		$template = 'service';
		break;
    default:
        $service_list = get_service_list($pdo);
        $template = 'service';
    break;
}

require_once './libraries/decorator.inc.php';
