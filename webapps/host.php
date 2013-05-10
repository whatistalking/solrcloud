<?php

require_once './libraries/common.lib.php';
$action = isset($params['action'])?$params['action']:"";
$req_url = $cfg['sc_url'].$_SERVER["REQUEST_URI"];

include_once './php-ofc-library-2.2/open-flash-chart.php';

switch($action){
    case 'edit':
        $u_info = check_login($req_url);
        $host_info = get_host_info($pdo,$params['host_id']);
        $template = 'host_edit';
    break;
    case 'do_edit':
        update_host($pdo,$params['host_id'],
            array('host_name'=>$params['host_name'],
                  'host_ip'=>$params['host_ip'],
                  'host_memory'=>$params['host_memory']));
        msg_redirect("host.php");
    break;
    case 'detail':
    	$host_info = get_host_info($pdo,$params['host_id']);
    	$host_memory = round($host_info['host_memory']/1024,2);

        /*某host上面的所有instance*/
        $host_instance_list = get_instance_list_complex($pdo, array('host_id'=>$params['host_id'], 'with_unbind'=>true));
        if($host_instance_list){
            /*取近期10min以内的最后一条数据*/
            foreach($host_instance_list as &$instance){
                    $jvmmem_logs=get_status_log($pdo_log,$cfg['idc'],$cfg['default_host'],'instance','jvmmem',$instance['instance_id'],'status_log_instance_jvmmemory',1,array('min'=>date('Y-m-d H:i:00', time()-10*60)));
                    if($jvmmem_logs){
                        $instance['jvmmem'] = $jvmmem_logs[0]['log_value'];
                    }
            }
        }
        $json_mem_instance = stacked_bar_chart($host_instance_list);

        //load
        $load_logs = get_status_log($pdo_log,$cfg['idc'],$cfg['default_host'],'host','loadaverage',$params['host_id'],'status_log_host_loadaverage',150);
		$load_logs_chart = build_chart_data($load_logs,false);
		$chart_load['title']="cpu load";
		$chart_load['legend_y']="percent";
		$chart_load['legend_x']="";
		$chart_load['values']=array($load_logs_chart['data']);
		$chart_load['values_key']=array('load');
		$chart_load['range_max']=max($load_logs_chart['max']+0.5,0.1);
		$chart_load['range_step']=$chart_load['range_max']/10;
		$chart_load['show_step']=4;
		$json_load=create_chart_data($chart_load);
	//memory
	$memory_logs = get_status_log($pdo_log,$cfg['idc'],$cfg['default_host'],'host','memoryused',$params['host_id'],'status_log_host_memoryused',150);
		if($memory_logs){
			foreach($memory_logs as $k=>$v){
				$memory_logs[$k]['log_value']=round($v['log_value']/1024,2);
			}
		}		
		$memory_logs_chart = build_chart_data($memory_logs,$host_memory*0.9);
		$chart_mem['title']="memory used";
		$chart_mem['legend_y']="used(GB)";
		$chart_mem['legend_x']="";
		$chart_mem['values']=array($memory_logs_chart['data'],$memory_logs_chart['deadline']);
		$chart_mem['values_key']=array('memory used','deadline');
		$chart_mem['range_max']=max($memory_logs_chart['max']+1,0.1);
		$chart_mem['range_step']=$chart_mem['range_max']/10;
		$chart_mem['show_step']=4;
		$json_mem=create_chart_data($chart_mem);
    	
    	$template = 'host_detail';
   	break;
    default:
        $host_list = get_host_list($pdo);
        $host_instance_map = classify_instance(get_instance_list_complex($pdo));
        if(!$host_list)$host_list=array();
        foreach ($host_list as $i =>$host) {
            $host_list[$i]["host_memory_gb"] = round($host["host_memory"] / 1024, 2);
            $instances = isset($host_instance_map[$host["host_id"]])?
                               $host_instance_map[$host["host_id"]]:array();

            $host_list[$i]["used_memory"] = 0;
            foreach ($instances as $instance) {
                if($instance['instance_status']==2){
                    $host_list[$i]["used_memory"] += $instance["use_memory"];
                }
            }

            $host_list[$i]["available_memory"] = $host_list[$i]["host_memory_gb"] - $host_list[$i]["used_memory"];

            if ($host_list[$i]["used_memory"] == 0) {
                $host_list[$i]["used_pct"] = 0;
            } elseif ($host_list[$i]["used_memory"] > $host_list[$i]["host_memory_gb"] ) {
                $host_list[$i]["used_pct"] = 100;
            } else {
                $host_list[$i]["used_pct"] = round($host_list[$i]["used_memory"] / $host_list[$i]["host_memory_gb"] * 100, 2);
            }

            $host_list[$i]["avail1"] = floor($host_list[$i]["available_memory"]);
            $host_list[$i]["avail4"] = floor($host_list[$i]["available_memory"] / 4);
        }

        $template = 'host';
    break;
}

function stacked_bar_chart($host_instance_list) {
    $title = new title( '所有instance实际内存使用' );
    $title->set_style( "{color: #567300; font-size: 16px; font-weight:bold;}" );
    $bar_stack = new bar_stack();
    $bar_stack->set_colours( array( '#C4D318', '#7D7B6A' ) );

	$max = 64;
	foreach ($host_instance_list as $i=>$instance){
			$jvmmem = isset($instance['jvmmem'])?explode('/',$instance['jvmmem']):array();
			if($jvmmem){
						foreach($jvmmem as &$j){$j = intval($j);}
						$max = ($max < $jvmmem[1])?$jvmmem[1]:$max;
						$jvmmem[1] = $jvmmem[1]-$jvmmem[0];
			}
			$bar_stack->append_stack($jvmmem);
			$lables[] = $instance['port_num'];
			$services[$i] = $instance['service_name'];
	}
	$bar_stack->set_keys(
			array(
						new bar_stack_key( '#C4D318', 'used', 13 ),
						new bar_stack_key( '#7D7B6A', 'total', 13 ),
			)
	);
	$bar_stack->set_on_click('(function(x){var services='.json_encode($services).';alert(services[x]);})');//js
	$bar_stack->set_tooltip( '#val#M,共#total#M' );
	$y = new y_axis();
	$y->set_range( 0, $max+32, 256);

	$x = new x_axis();
	$x->set_labels_from_array($lables);

    $tooltip = new tooltip();
    $tooltip->set_hover();

    $chart = new open_flash_chart();
    $chart->set_title( $title );
    $chart->add_element( $bar_stack );
    $chart->set_x_axis( $x );
    $chart->add_y_axis( $y );
    $chart->set_tooltip( $tooltip );

    return $chart->toPrettyString();
}

require_once './libraries/decorator.inc.php';
