<?php
$base =  dirname(__FILE__);
$solr_root = substr($base, 0, strrpos($base,'scripts'));
$script_root = $solr_root."/scripts";
include_once($solr_root . "/webapps/libraries/config.alert.php");
include_once($script_root . "/common.php");
include_once($script_root . "/alert/SMTP.php");

class Alert {
    public $default_alert = false;
    public $custom_alert_list = false;
    public $pdo = false;
    public $link_log = false;
    public $alert = false;
    public $alert_def = false;

    public function init($default_alert, $smtp, $pdo, $link_log){
        $this->default_alert = $default_alert;
        $this->pdo = $pdo;
        $this->link_log = $link_log;
        $this->smtp = $smtp;
    }
    public function get_custom_alert_list(){
        $custom_alert = get_custom_alert_list($this->pdo);
        foreach($custom_alert as $c){
            $this->custom_alert_list[$c['alert_type_id']][$c['target_id']][] = $c;
        }
    }
    public function start(){
        $this->get_custom_alert_list();/*用户自定义报警*/
        /*逐条检查*/
        foreach($this->custom_alert_list as $alert_type_id=>$alert_type_val){
            if(empty($this->default_alert[$alert_type_id])) continue;
            $default_alert = $this->default_alert[$alert_type_id];
            $default_alert_setting_json = json_encode($default_alert['default']);

            foreach($alert_type_val as $target_id=>$items){
                foreach($items as $item){
//print_r($item);
                    $alert_setting_json = $item['alert_setting'];
                    $alert_setting = json_decode($item['alert_setting'],true); 

                    if($alert_setting_json === $default_alert_setting_json && isset($this->alert_def[$alert_type_id][$target_id])){
                        $this->alert[$item['username']] = $this->alert_def[$alert_type_id][$target_id];
                        continue;
                    }

                    $end = date('Y-m-d H:i:00');
                    if(isset($default_alert['default']['keeptime']) 
                        &&  $alert_setting['keeptime'] && (isset($alert_setting['hwm']) || isset($alert_setting['lwm']))){/*监控阈值*/
                        /*由于5min采样一次，不足5min就补足5min*/
                        if(floatval($alert_setting['keeptime'])<5)$alert_setting['keeptime'] = 5;
                        $start = date('Y-m-d H:i:00',time()-floatval($alert_setting['keeptime'])*60);
                        $data = $this->get_chartdata($default_alert['table'],$default_alert['field'],$target_id, $start);
                          
//echo "---检查hwm------\n";    
                        $check = $this->check_alert_hwm($data,$alert_setting['hwm']);
                        $this->alert[$item['username']] .= $check?$default_alert['name'].'('.$default_alert['target_type'].$target_id.")持续高于警戒线\t":''; 
//echo "---检查lwm------\n";    
                        $check = $this->check_alert_lwm($data,$alert_setting['lwm']);
                        $this->alert[$item['username']] .= $check?$default_alert['name'].'('.$default_alert['target_type'].$target_id.")持续低于警戒线\t":''; 
                    }
                    if(isset($default_alert['default']['percent'])
                        && isset($alert_setting['percent'])){/*监控波动*/
                         /*由于5min采样一次，不足5min就补足5min*/
                        if(floatval($alert_setting['period'])<5)$alert_setting['period'] = 5;
                        $start = date('Y-m-d H:i:00',time()-floatval($alert_setting['period'])*60);
                        $data = $this->get_chartdata($default_alert['table'],$default_alert['field'],$target_id, $start);
                        
                        $old_start = date('Y-m-d H:i:00',strtotime($start)-86400);
                        $old_end = date('Y-m-d H:i:00',strtotime($end)-86400);
                        $old_data = $this->get_chartdata($default_alert['table'],$default_alert['field'],$target_id, $old_start, $old_end);
                        
//echo "---检查range------\n";    
                        $check = $this->check_alert_range($data, $old_data,$alert_setting['percent']);
                        $this->alert[$item['username']] .= $check?$default_alert['name'].'('.$default_alert['target_type'].$target_id.")比昨天波动率大于".$alert_setting['percent']."%\t":'';
                    }
                    
                    if($alert_setting_json === $default_alert_setting_json){
                        $this->alert_def[$alert_type_id][$target_id] = $this->alert[$item['username']];
                    }
                }
            
            } 
        }
        print_r($this->alert);
        $this->notify();
    }

    private function get_user_info($name){
        return get_user_info($this->pdo, $name);
    }
    /*发通知*/
    private function notify(){
        $smtpserver = $this->smtp['smtpserver'];
        $smtpserverport = $this->smtp['smtpserverport'];
        $smtpuser = $this->smtp['smtpuser'];
        $smtppass = $this->smtp['smtppass']; 
        $smtp_usermail= $this->smtp['smtp_usermail'];
        
        $smtp = new SMTP($smtpserver, $smtpserverport, false, $smtpuser, $smtppass);
        $smtp->set_from("SearchCloud", "solr@sc10-001.a.ajkdns.com");
        $smtp->debug = false;
        $cc = '';
        $bcc = '';

        foreach($this->alert as $name=>$msg){
            if(empty($msg)) continue;
            $userinfo = $this->get_user_info($name);
            print_r($userinfo);
            if(!empty($userinfo['email'])){
                /*发Email*/
                $sent = $smtp->sendmail($userinfo['email'], $smtp_usermail, 'SearchCloud报警邮件', $msg, 'HTML', $cc, $bcc);
                print_r($sent);
            }
            if(!empty($userinfo['mobile'])){
                /*发SMS*/
                $sent = $smtp->sendmail($userinfo['mobile'].'@139.com', $smtp_usermail, 'SearchCloud报警邮件', $msg, 'HTML', $cc, $bcc);
                print_r($sent);
            }
        }
    }


    /*
     * 检查一段时间内的平均数与old数据比变化率是否超标
     * 超标则返回true  
     */
    private function check_alert_range($data, $old_data, $range){
        if(empty($data) || empty($old_data)){
            return false;
        }
        $avg = $this->avg_data($data);
        $old_avg = $this->avg_data($old_data);
        
        if($avg === false || $old_avg === false) return false;
        $ratio = abs(($avg - $old_avg)/$old_avg * 100);
echo $ratio.'~'.$range;
        if($ratio > $range) return true;

        return false;
    }
    /*
     * 求data的log_value平均数
     */
    private function avg_data($data){
        if(!empty($data) && is_array($data)){
            foreach($data as $v){
                $log_value = $v['log_value'];
                if($v['log_name'] == 'jvmmem'){
                    $a = explode('/',$log_value);
                    $log_value = $a[0]/$a[1];
                }
                if(!is_numeric($log_value)) continue;
                $array[] = $log_value;
            }
            if($array){
                return array_sum($array)/count($array);
            }
        }
        return false;

    }
    /*
     * 检查一段时间内的所有数据都高于HWM  
     */
    private function check_alert_hwm($data, $hwm){
        $flag = true;
        if(empty($data)) return $flag = false;
        foreach($data as $val){
            if($val['log_value'] < $hwm){ $flag = false;}
        }
        return $flag;
    }
    /*
     * 检查一段时间内的所有数据都低于LWM  
     */
    private function check_alert_lwm($data, $lwm){
        $flag = true;
        if(empty($data)) return $flag = false;
        foreach($data as $val){
           if($val['log_value'] > $lwm){ $flag = false;}
        }
        return $flag;
    }

    private function get_chartdata($table, $field, $target_id, $start, $end=false){
        return get_chartdata($this->link_log,$table, $field, $target_id, $start, $end);
    }
}

if(!empty($cfg['alert'])){
    $o = new Alert();
    $o->init($cfg['alert'], $cfg['smtp'], $link, $link_log);
    $o->start();
}

