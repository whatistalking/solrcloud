<?php
require_once './libraries/common.lib.php';

$service_id = intval($params['service_id']);
if (!$service_id) {
    msg_redirect('index.php');
}
$tab = (isset($params['tab']) && $params['tab'] < 6)? $params['tab'] : '1';
$service_info = pdo_get_service_byid($pdo,$service_id);
if($tab == '5'){
    if(!empty($params['date']) && date('Y-m-d', strtotime($params['date'])) !== '1970-01-01'){
        $date = date('Y-m-d', strtotime($params['date']));
    }else {
        $date = date('Y-m-d',strtotime('-1 day'));
    }
    if(isset($params['query_type']) && $params['query_type'] =='frequent') {
        $query_type = 'frequent';
        $query_list = select_frequentquery($pdo_log, $date,$service_info['service_name']);
    }
    else {
        $query_type = 'slow';
        $query_list = select_slowquery($pdo_log, $date, $service_info['service_name']);
    }

}

//queue list & solr config - tab1
if($tab == '1'){
    $service_mapping = pdo_get_mapping($pdo, $service_id);
    if($service_mapping){
        foreach($service_mapping as $v){
            $qlp['instance_id'][] = $v['instance_id'];
        }
    }
    $qlp['service_id']=array($service_id);
    $queue_list = get_queue_list_complex2($pdo, 0, 15, $qlp);
    
    $solr_config = pdo_get_solr_config($pdo, $service_id);
    $solr_config = json_decode($solr_config['config_json'],true);

    $dataimport = pdo_get_dataimport($pdo, $service_id);
}

//instance list & host list - tab2
if(in_array($tab, array('2','4'))){
    $instance_list = get_instance_list_complex($pdo,array(
            'service_id' => $service_id,
            'with_unbind' => '1'
    ));
    $host_list = change_array_key(get_host_list($pdo), 'host_id');
}

//solr_schema - tab1/2
if(in_array($tab, array('1','3'))){
    $solr_schema = pdo_get_solr_schema($pdo, $service_id);
    if($service_info['schema_type'] == 1){
	    $solr_schema = json_decode($solr_schema['schema_json'],true);
    }elseif($service_info['schema_type'] == 2){
	    $schema_info = $solr_schema['schema_json'];
	    $doc = new DomDocument();
	    $doc->loadXML($schema_info);
	    $xpath = new DomXPath($doc);
	
	    $query = '//field';
	    $nodelist = $xpath->query($query);
	    foreach($nodelist as $key => $node){
		    // var_dump ($node->attributes);
		    foreach($node->attributes as $attr){
			    $menu[$key][$attr->name]=$attr->value;
			    // var_dump($attr->name .'=' . $attr->value);
		    }
	    }
	    $solr_schema['fields']=$menu;
	
	    $query='//dynamicField';
	    $nodelist=$xpath->query($query);
	    $dymenu = array();
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
}

//type list - tab3
if($tab == '3'){
    $type_list = get_schema_type_list($pdo);
}




$template='service_detail';
$current_nav='service';
require_once './libraries/decorator.inc.php';
