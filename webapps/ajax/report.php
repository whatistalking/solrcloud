<?php
require_once '../libraries/common.lib.php';
include_once '../libraries/config.alert.php';

class report {
    private $cfg_alert;
    private $pdo_log;
    private $pdo;

    private $alert;
    private $chart;
    private $i;
    private $graph;
    private $chart_Data;
    private $ret = array();

    public function _init_($cfg_alert, $pdo_log, $pdo){
        $this->cfg_alert = $cfg_alert;
        $this->pdo_log = $pdo_log;
        $this->pdo = $pdo;
    }
    public function start($charts){
        /*循环每次一张图*/
        foreach ($charts as $chart){
            $this->chart = $chart;
            $this->i = 0;
            $this->graph = array();
            $this->chartData = array();
            /*参数检查*/
            if(!isset($this->cfg_alert[$chart['report_id']])) continue;
            $this->alert = $this->cfg_alert[$chart['report_id']];
            /*当类型为global时，target_id置1,
             * 当类型为instance时，target_id替换成instance_id,*/
            switch($this->alert['target_type']){
            case 'global':
                $chart['target_id'] = 1;break;
            case 'instance':/*某个service下的所有readable/wirtable的instance情况*/
                if(empty($chart['target_id'])) continue;
                $service_id = $chart['target_id'];
                $chart['target_id'] = array();
                foreach($service_id as $target){
                    $instances = pdo_get_mapping_info_by_service($this->pdo, $target);
                    if(!empty($instances)){ foreach ($instances as $i){array_push($chart['target_id'],$i['instance_id'] );} }
                }
                break;
            case 'host-instance':/*某个host上的所有instance的情况*/
                if(empty($chart['target_id'])) continue;
                $host_id = $chart['target_id'];
                $chart['target_id'] = array();
                foreach($host_id as $target){
                    $instances = get_instance_list_complex($this->pdo, array('host_id'=>$target));/*某host上所有的instance*/
                    if(!empty($instances)){ foreach ($instances as $i){array_push($chart['target_id'],$i['instance_id'] );} }
                }
            }
            /**/
            if(empty($chart['target_id'])) continue;
            if(is_array($chart['target_id'])){
                /*当target_id为数组时*/
                foreach($chart['target_id'] as $target_id) $this->draw($target_id);
            }else{
                /*当target_id不为数组时*/
                $this->draw($chart['target_id']);
            }
            $this->ret[] = array(
                'num' => count($this->graph),
                'data' => array(
                'div'=>$this->chart['div_id'],
                'name'=>$this->alert['name'],
                'graph'=>$this->graph,
                'chartData'=>$this->merge_key_val($this->chartData)
                )
            ); 
        }
        //print_r($this->ret);
        return $this->ret;
    }

    private function merge_key_val($data){
        $chartData = array();
        ksort($data);
        foreach($data as $time=>$d)
            $chartData[] = array_merge(array('date'=>$time),$d);
        return $chartData;
    } 
    /*每个target_id的数据*/
    public function draw($target_id){
        /*取时间范围，获取数据，绘图*/
        if(empty($this->chart['select_date'])){$this->chart['select_date'][] = array('from'=>date('Y-m-d H:i:s',time()-86400));}/*默认显示24h数据*/
        if(!empty($this->chart['select_date'][0]['to'])) {$this->chart['select_date'][0]['to'] = date('Y-m-d',strtotime($this->chart['select_date'][0]['to'])+86400);}
        if(!empty($this->chart['select_date'][1]['to'])) {$this->chart['select_date'][1]['to'] = date('Y-m-d',strtotime($this->chart['select_date'][1]['to'])+86400);}

        $select_date = $this->chart['select_date'][0];
        $this->init_chartData($this->init_data(get_status_log($this->pdo_log,'idc10','-1',$this->alert['target_type'], $this->alert['field'], $target_id, $this->alert['table'], 0, array('min'=>$select_date['from'],'max'=>$select_date['to']))));
        $this->graph['v'.$this->i] = array('title'=>$this->get_title($this->alert['target_type'], $target_id), 'color'=>$this->get_color($this->i));

        $old_select_date = isset($this->chart['select_date'][1])?$this->chart['select_date'][1]:false;
        if(!empty($old_select_date)){
            $intval = strtotime($select_date['from']) - strtotime($old_select_date['from']);
            $this->init_chartData($this->init_data(get_status_log($this->pdo_log,'idc10','-1',$this->alert['target_type'], $this->alert['field'], $target_id, $this->alert['table'], 0, array('min'=>$old_select_date['from'],'max'=>$old_select_date['to'])), $intval));
            $this->graph['v'.$this->i] = array('title'=>$this->get_title($this->alert['target_type'],$target_id)."old", 'color'=>$this->get_color($this->i));
        }
        
    }
    /*整理*/
    public function init_chartData($data){
        /*整理数据*/
        $this->i++;
        $valname = 'v'.$this->i;
        foreach($data as $d){
            $this->chartData[$d['log_time']][$valname] = $d['log_value'];
        }
    }
    /*整理data数据,$intval用于将老数据修改时间与新数据一起显示*/
    public function init_data($data, $intval=0){
        krsort($data);
        if($this->alert['field'] == 'jvmmem'){
            foreach($data as &$val){
                $d = explode('/', $val['log_value']);
                $val['log_value'] = $d[0]/$d[1];
            }
        }
        foreach($data as &$val){
            $val['log_time'] = strtotime($val['log_time'])+ $intval;
        }
        return $data;
    }
    private function get_title($target_type, $target_id){
        switch ($target_type){
            case 'service':
                if(empty($this->title['service'][$target_id])){
                    $ret = pdo_get_service_byid($this->pdo, $target_id);
                    if($ret) $this->title['service'][$target_id] = $ret['service_name'];
                }
                return $this->title['service'][$target_id];
                break;
            case 'instance':
                if(empty($this->title['instance'][$target_id])){
                    $ret = pdo_get_instance_byid($this->pdo, $target_id);
                    if($ret) $this->title['instance'][$target_id] = $ret['service_name'].':'.$ret['port_num'];
                }
                return $this->title['instance'][$target_id];
                break;
            case 'host':
                if(empty($this->title['service'][$target_id])){
                    $ret = get_host_info($this->pdo, $target_id);
                    if($ret) $this->title['host'][$target_id] = $ret['host_name'];
                }
                return $this->title['host'][$target_id];
                break;
            case 'host-instance':
                if(empty($this->title['instance'][$target_id])){
                    $ret = pdo_get_instance_byid($this->pdo, $target_id);
                    if($ret) $this->title['instance'][$target_id] = $ret['service_name'].':'.$ret['port_num'];
                }
                return $this->title['instance'][$target_id];
                break;
            case 'global':
                return 'global';
                break;
        }
    }
    private function get_color($i){
        $lib = array('#333333','#ff6600','#fcd202','#b0de09','#0d8ecf','#21CF12','#666699','#003333','#990066','#009966','#EB2D2D','#4A57E9','#2B0EA0','#587A01');
        return (!empty($lib[$i]))?$lib[$i]:$lib[$i%10];
    }
}
$param = json_decode($params['param'], true);
$r = new report();
$r->_init_($cfg['alert'], $pdo_log, $pdo);
$ret = $r->start($param);

require_once SC_PATH.'/ajax/report.phtml';
