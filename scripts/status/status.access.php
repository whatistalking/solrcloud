<?php
$base =  dirname(__FILE__);
$solr_root = substr($base, 0, strrpos($base,'scripts'));
$script_root = $solr_root."/scripts";
include_once($script_root . "/common.php");

/*
 * 从storm读取分析的日志
 * */

class Charts {
    private $link;
    private $knowing_url;
    public function __construct($link, $link_log, $link_chart_log){
        $this->link = $link;
        $this->link_log = $link_log;
        $this->link_chart_log = $link_chart_log;
        $this->knowing_url = 'http://10.10.3.43:9075/api/solr/add';
        $this->delete_knowing_url = 'http://10.10.3.43:9075/api/solr/delete/';
    }
    /*
     * 每3min读取一次storm，根据>=lasttime字段
     * */
    public function select(){
        $this->select_data('service');
        $this->select_data('instance');
    }

    public function select_data($target_type){
        $sql = "select * from knowing_charts where target_type='$target_type'";
        $charts = pdo_fetch_all($this->link, $sql);
        $this->time = date('Y-m-d H:i:s',time()-60);
        foreach($charts as $chart){
            $sql = "select * from t_chartdata_".date('Ymd')." where f_ds_id=".$chart['chart_id']." and f_time>='".$chart['lasttime']."'";
            $chart_logs = pdo_fetch_all($this->link_chart_log, $sql);
            if(!$chart_logs) {echo "Warning : $sql\n"; continue;}
            $this->insert_log($chart, $chart_logs);
            $this->update_lasttime($chart['chart_id']);
        }
    }
    
    private function update_lasttime($chart_id){
        $sql = "update knowing_charts set lasttime='".$this->time."' where chart_id=$chart_id";
        echo "$sql\n";
        $sth = $this->link->prepare($sql);
        $sth ->execute(array());
    }

    public function insert_log($chart, $chart_logs){
        switch($chart['log_name']){
            case 'select':
                switch($chart['target_type']){
                    case 'service':
                        $table = 'status_log_service_access';$log_name = 'select';break;
                    case 'instance':
                        $table = 'status_log_instance_access';$log_name = 'select';break;
                }
                break;
            case 'update':
                switch($chart['target_type']){
                    case 'service':
                        $table = 'status_log_service_access';$log_name = 'update';break;
                }
                break;
            case '90':
                switch($chart['target_type']){
                    case 'service':
                        $table = 'status_log_service_calculate';$log_name = 'millisecond_90';break;
                    case 'instance':
                        $table = 'status_log_instance_access';$log_name = 'millisecond_90';break;
                }
                break;
            case '100ms':
                switch($chart['target_type']){
                    case 'service':
                        $table = 'status_log_service_calculate';$log_name = 'percent_100';break;
                }
                break;
        }
        if($table && $log_name)
            foreach($chart_logs as $chart_log){
                add_status_log($this->link_log,'office','-1',$chart['target_type'],$log_name,$chart['target_id'],$chart_log['f_data'],$chart_log['f_time'],$table);
            }
    }
    
    /*
     * 每10min检查一次running中的service/instance，若没有knowing_charts则创建图
     * */
    public function add(){
        $service_list = get_service_list_running($this->link);
        foreach($service_list as $service){
            $instances = pdo_get_mapping_info_by_service($this->link,$service['service_id']);
            if($instances){
                $this->check_service_knowing_chart($service['service_id'], $service['service_name']);
                foreach($instances as $instance){
                    $this->check_instance_knowing_chart($instance['instance_id'], $instance['host_ip'],$instance['port_num'], $service['service_name']);/*db读取出来没有instance_id字段*/
                }
            }
        }
    }
    /*
     * 每天02点检查knowing_charts，若service/instance不在running则删除图
     * */
    public function delete(){
        $service_list = get_service_list_running($this->link);
        $service_ids = $this->get_field('service_id',$service_list);
        $instance_ids = array();
        foreach($service_list as $service){
            $instances = pdo_get_mapping_info_by_service($this->link,$service['service_id']);
            $instance_ids = array_merge($instance_ids, $this->get_field('instance_id',$instances));
        }

        $sql = "select * from knowing_charts where target_type='service'";
        $charts = pdo_fetch_all($this->link, $sql);
        foreach($charts as $chart){
            if(!in_array($chart['target_id'],$service_ids)){
                echo 'delete service '.$chart['target_id'].'--'.$chart['chart_id']."\n";
                $this->delete_knowing_chart($chart['chart_id']);
            }
        }
        $sql = "select * from knowing_charts where target_type='instance'";
        $charts = pdo_fetch_all($this->link, $sql);
        foreach($charts as $chart){
            if(!in_array($chart['target_id'],$instance_ids)){
                echo 'delete instance '.$chart['target_id'].'--'.$chart['chart_id']."\n";
                $this->delete_knowing_chart($chart['chart_id']);
            }
        }
    }
    /*
     * 获取某数组的某列
     * return array
     * */
    private function get_field($field_name, $array){
        $ret = array();
        if($array && is_array($array)){
            foreach($array as $a){
                $ret[] = $a["$field_name"];
            }
        }
        return $ret;
    }
    /* table knowing_charts
     * chart_id, target_id, target_type, log_name, ,lasttime
     *  24444  ,   255    ,   service  ,  select ,
     *  24445  ,   255    ,   service  ,  update ,
     *  24446  ,   255    ,   service  ,  90% ,
     *  24447  ,   255    ,   service  ,  100 ,
     *  777  ,     2345   ,   instance ,  select ,
     *  779  ,     2345   ,   instance ,  90% ,
     * */
    private function check_service_knowing_chart($service_id, $service_name){
        $sql = "select * from knowing_charts where target_type='service' and target_id='$service_id'";
        $charts = pdo_fetch_all($this->link, $sql);
        if(!$charts){
            $this->add_service_knowing_chart($service_id, $service_name);
        }
    }
    
    private function add_service_knowing_chart($service_id,$service_name){
        $data = new stdClass();
        $data->title = "/$service_name/select";
        $data->type = 'count';
        $data->filters = json_encode(array(array('request_url','startswith',"/$service_name/select")));
        $id = $this->add_knowing($data);
        if(intval($id))
            $this->add_chart($id, $service_id, 'service', 'select');
        
        $data->title = "/$service_name/update";
        $data->type = 'count';
        $data->filters = json_encode(array(array('request_url','startswith',"/$service_name/update")));
        $id = $this->add_knowing($data);
        if(intval($id))
            $this->add_chart($id, $service_id, 'service', 'update'); 
        
        $data->title = "/$service_name/90";
        $data->type = 'ninety';
        $data->filters = json_encode(array(array('request_url','startswith',"/$service_name/select")));
        $id = $this->add_knowing($data);
        if(intval($id))
            $this->add_chart($id, $service_id, 'service', '90');
 
        $data->title = "/$service_name/100ms";
        $data->type = 'count';
        $data->filters = json_encode(array(array('request_url','startswith',"/$service_name/select"), array('request_time','gte',0.1)));
        $id = $this->add_knowing($data);
        if(intval($id))
            $this->add_chart($id, $service_id, 'service', '100ms'); 
    }

    private function delete_knowing_chart($chart_id){
        $ret = $this->delete_knowing($chart_id);
        if($ret)
           $this->delete_chart($chart_id); 
    }


    private function check_instance_knowing_chart($instance_id, $host, $port, $service_name){
        $sql = "select * from knowing_charts where target_type='instance' and target_id='$instance_id'";
        $charts = pdo_fetch_all($this->link, $sql);
        if(!$charts){
            $this->add_instance_knowing_chart($instance_id, $host, $port, $service_name);
        }
       
    }
    
    private function add_instance_knowing_chart($instance_id, $host, $port, $service_name){
        $data = new stdClass();
        $data->title = "/$service_name/select/$host:$port";
        $data->type = 'count';
        $data->filters = json_encode(array(array('request_url','startswith',"/$service_name/select"),array('upstream_addr','equals',"$host:$port")));
        $id = $this->add_knowing($data);
        if(intval($id))
            $this->add_chart($id, $instance_id, 'instance', 'select');
       
        $data->title = "/$service_name/90/$host:$port";
        $data->type = 'ninety';
        $data->filters = json_encode(array(array('request_url','startswith',"/$service_name/select"),array('upstream_addr','equals',"$host:$port")));
        $id = $this->add_knowing($data);
        if(intval($id))
            $this->add_chart($id, $instance_id, 'instance', '90');
    }


    private function add_knowing($data){
        echo 'add '.json_encode($data)."\n";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->knowing_url);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $ret = curl_exec($ch);
        var_dump($ret);
        if($ret){
            $ret = json_decode($ret, true);
            if($ret['status']=="ok")
                return $ret['id'];
        } 
        return false; 
    }
    private function delete_knowing($chart_id){
        echo 'delete '.$chart_id;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->delete_knowing_url.$chart_id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $ret = curl_exec($ch);
        var_dump($ret);
        if($ret){
            $ret = json_decode($ret, true);
            if($ret['status']=="ok")
                return true;
        } 
        return false; 
    }

    private function add_chart($chart_id, $target_id, $target_type, $log_name){
        $sql = "insert into knowing_charts set chart_id=?, target_id=?, target_type=?, log_name=?, lasttime=?";
        $sth = $this->link->prepare($sql);
        $sth ->execute(array($chart_id, $target_id, $target_type, $log_name, date('Y-m-d H:i:s')));
        return $this->link->lastInsertId();
    }
    private function delete_chart($chart_id){
        $sql = "delete from knowing_charts where chart_id=$chart_id";
        $sth = $this->link->prepare($sql);
        $sth ->execute(array());
    }

}


echo "=========".date('Y-m-d H:i:s')."=====".$argv[1]."===\n";
$o = new Charts($link, $link_log, $link_chart_log);
switch($argv[1]){
    case 'add':
        $o->add();
        break;
    case 'delete':
        $o->delete();
        break;
    default:
        $o->select();
}

