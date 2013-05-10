<?php
/***************************************************
 * 数据库操作
 **************************************************/
class SolrDb {
    private function SolrDb() {}
    private static $link = array();
    public static function getLink($host, $user, $pass, $name) {
    	$key = md5($host.$user.$pass.$name);
        if (isset(self::$link[$key])) {
            return self::$link[$key];
        }
        $link = new PDO("mysql:host=$host;dbname=$name;", $user, $pass);
        $link ->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $link->exec('SET CHARACTER SET utf8');
        $link->exec('SET NAMES utf8');
        self::$link[$key]=$link;
        return self::$link[$key];
    }
}

function pdo_fetch($pdo, $sql, $params=array()) {
    $rst = pdo_fetch_all($pdo, $sql, $params);
    if ($rst) return $rst[0];
    return false;
}

function pdo_fetch_column($pdo, $sql, $params=array()) {
    $rst = pdo_fetch_all($pdo, $sql, $params);
    if ($rst) return reset($rst[0]);
    return false;
}

function pdo_fetch_all($pdo, $sql, $params=array()) {
    $sth = $pdo->prepare($sql);
    $sth->execute($params);
    $sth->setFetchMode(PDO::FETCH_ASSOC);
    $rst = $sth->fetchAll();
    if (empty($rst)) return false;
    return $rst;
}

/***************************************************
 * db host
 **************************************************/
function get_host_list($pdo){
    return pdo_fetch_all($pdo, 'select * from host');
}

function get_host_list_select($pdo, $mem, $service_id=false){
    $hosts = get_host_list($pdo);
    if(!$hosts) return false;
    foreach($hosts as $k => $v){
        $sql = "SELECT sum(use_memory) as use_memory FROM instance WHERE host_id='$v[host_id]' AND instance_status=2";
        $use_mem = pdo_fetch_all($pdo,$sql);
        $hosts[$k]['use_mem'] = $use_mem?$use_mem[0]['use_memory']:0;
        $hosts[$k]['use_mem'] = round($hosts[$k]['use_mem'],2);
        $hosts[$k]['free_mem'] = round(($hosts[$k]['host_memory'] - $hosts[$k]['use_mem']),2);
        $hosts[$k]['host_mem'] = round($hosts[$k]['host_memory'],2);
        $hosts[$k]['sort'] = 0;
        if($mem > $hosts[$k]['free_mem']){
            $hosts[$k]['msg'] = '错误：此host空间不足！';
            $hosts[$k]['sort'] = 9;
            continue;
        }
        if($service_id){
            $mapping = pdo_get_mapping_info_by_service($pdo, $service_id);
            $host_ids = array();
            foreach($mapping as $m){
                $host_ids[] = $m['host_id'];
            }
            if(in_array($v['host_id'], $host_ids)){
                $hosts[$k]['msg'] = '提醒：同一个service的instance建议分配到不同的host！';
                $hosts[$k]['sort'] = 5;
            }
        }
    }
    /*根据free_mem和sort排序*/
    foreach ($hosts as $k=>$v){
        $a[] = $v['free_mem'];
        $b[] = $v['sort'];
    }
    array_multisort($a, SORT_DESC, $b, SORT_ASC, $hosts);
    return $hosts;
}

function get_host_list_instance_add($pdo, $hosts){
    if(!$hosts) return false;
    foreach($hosts as $k => $v){
        $sql = "SELECT use_memory FROM instance WHERE host_id='$v[host_id]' AND instance_status=2";
        $mem_instance = pdo_fetch_all($pdo,$sql);
        if (!$mem_instance) {
            $hosts[$k]['instance_mem'] = 0;
            $hosts[$k]['use_mem'] = 0;
        } else {
            $hosts[$k]['instance_mem'] = $mem_instance;
            $hosts[$k]['use_mem'] = 0;
            foreach($hosts[$k]['instance_mem'] as $val){
                $hosts[$k]['use_mem'] += $val['use_memory']; 
            }
        }
        $hosts[$k]['use_mem'] = round($hosts[$k]['use_mem']/1024,2);
        $hosts[$k]['host_memory'] = round($hosts[$k]['host_memory']/1024,2);
    }
    return $hosts;
}

function get_host_count($pdo) {
    $sql="select count('x') as c from host";
    return pdo_fetch_column($pdo, $sql);
}

function get_host_info($pdo,$host_id){
    return pdo_fetch($pdo,'select * from host where host_id=?',array($host_id));
}

function update_host($pdo,$host_id,$params){
    $sql = "update host set host_name=?,host_ip=?,host_memory=? where host_id=?";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($params['host_name'],$params['host_ip'],$params['host_memory'],$host_id));
    return $sth->rowCount();
}

function classify_instance($instances){
	if(!$instances)return false;
    foreach($instances as $v){
        //if($v['instance_status']!=2)continue;
        $res[$v['host_id']][]=$v;
    }
    foreach($res as $tk=>$tv){
        $memory = 0;
        foreach($tv as $tk2=>$tv2){
            $memory+=$tv2['use_memory'];
            $res[$tk][$tk2]['use_memory'] = sprintf("%01.2f",round($tv2['use_memory']/1024,2));
        }
        $res[$tk]['use_memory']=round($memory/1024,2);
    }
    return $res;
}

/***************************************************
 * db service
 **************************************************/

function get_service_count($pdo,$on_serivce=null){
    $sql="select count('x') as c from service";
    if($on_serivce===true)$sql.=' where service_status=1';
    if($on_serivce===false)$sql.=' where service_status=0';
    return pdo_fetch_column($pdo, $sql);
}

function pdo_get_service($pdo, $service_name) {
    $sql = "select * from service where service_name=?";
    return pdo_fetch($pdo, $sql, array($service_name));
}

function pdo_get_service_byid($pdo, $service_id) {
    $sql = "select * from service where service_id=?";
    return pdo_fetch($pdo, $sql, array($service_id));
}

function pdo_get_service_id($pdo, $service_name) {
    $sql = "select service_id from service where service_name=? and service_status=1";
    return pdo_fetch_column($pdo, $sql, array($service_name));
}

function get_service_list($pdo){
    $sql = "select * from service order by service_id";
    return pdo_fetch_all($pdo, $sql);
}

function get_service_list_running($pdo){
    $sql = "select * from service where service_status=1 order by service_id";
    return pdo_fetch_all($pdo, $sql);
}

function get_service_list_by_mapping($pdo,$params){
    $sql="SELECT *
                FROM service_mapping AS a
                LEFT JOIN service AS b ON a.service_id = b.service_id
                WHERE 1";
   if(isset($params['instance_id']))$sql.=" and instance_id=".$params['instance_id'];
   return pdo_fetch_all($pdo, $sql);
}

function get_service_list_by_urlregex($pdo,$url_regex){
    $sql = "select * from service where url_regex=?";
    return pdo_fetch($pdo, $sql, array($url_regex));
}

function insert_service($pdo,$params){
    $sql="insert into service set service_name=?,url_regex=?,description=?,hash_type=?,optimize_time=?,service_status=0,config_type=?,department=?,solr_version=?";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($params['service_name'],$params['url_regex'],$params['description'],$params['hash_type'],$params['optimize_time'],$params['config_type'],$params['department'],$params['solr_version']));
    return $pdo->lastInsertId();
}

function update_service($pdo,$service_id,$params){
    $sql="update service set description=?,hash_type=?,optimize_time=?, config_type=?, schema_type=? where service_id = ?";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($params['description'],$params['hash_type'],$params['optimize_time'],$params['config_type'],$params['schema_type'], $service_id));
    return $sth->rowCount();
}

function update_service_schema_type($pdo,$service_id,$params){
    $sql="update service set schema_type=? where service_id = ?";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($params['schema_type'], $service_id));
    return $sth->rowCount();
}

function lock_service($pdo,$service_id){
    $sql="update service set is_locked=1 where service_id = ?";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($service_id));
    return $sth->rowCount();
}

function stop_service($pdo,$service_id){
    $sql="update service set service_status=0 where service_id = ?";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($service_id));
    return $sth->rowCount();
}

function start_service($pdo,$service_id){
    $sql="update service set service_status=1 where service_id = ?";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($service_id));
    return $sth->rowCount();
}

/***************************************************
 * db instance
 **************************************************/

function get_instance_count($pdo,$running=null){
    $sql="select count('x') as c from instance";
    if($running===true)$sql.=' where instance_status=2';
    return pdo_fetch_column($pdo, $sql);
}

function get_instance_list($pdo){
    $sql = "select * from instance";
    return pdo_fetch_all($pdo, $sql);
}

function get_host_max_port_num($pdo,$host_id){
    $sql = "select max(port_num) from instance where host_id=?";
    return pdo_fetch_column($pdo, $sql, array($host_id));
}

function get_instance_list_complex($pdo,$params=array()){
    $where = "WHERE 1";
    if(isset($params['service_id'])&&$params['service_id']) $where .=" and b.service_id=".$params['service_id'];
    if(isset($params['host_id'])&&$params['host_id']) $where .=" and h.host_id=".$params['host_id'];
    if(isset($params['readable'])) $where .=" and a.readable=".$params['readable'];/*readable的instance*/
    if(isset($params['writable'])) $where .=" and a.writable=".$params['writable'];/*writable的instance*/
    if(isset($params['running'])) $where .=" and a.instance_status=2";/*运行中的instance*/
    if(!isset($params['with_unbind'])) $where .=" and b.is_disabled=0";/*默认查询bind的，带参数with_unbind时全部查询*/
    $sql = "SELECT a.*, c.service_id, c.service_name, c.service_status, c.department, h.host_name, h.host_ip, b.is_disabled 
                FROM instance a
                LEFT JOIN service_mapping b ON a.instance_id = b.instance_id
                LEFT JOIN service c ON b.service_id = c.service_id
                LEFT JOIN host h ON h.host_id = a.host_id
                $where
                ORDER BY a.host_id asc,a.instance_id asc";
    return pdo_fetch_all($pdo, $sql);
}

function pdo_get_instance_byid($pdo, $instance_id) {
    $sql = "select a.*, b.service_id, c.service_name from instance a 
            left join service_mapping b on a.instance_id=b.instance_id 
            left join service c on b.service_id=c.service_id 
            where a.instance_id=?";
    return pdo_fetch($pdo, $sql, array($instance_id));
}

function get_instance_by_port($pdo,$host_id,$port_num){
    $sql = "select * from instance where host_id=? and port_num=?";
    return  pdo_fetch($pdo, $sql, array($host_id,$port_num));
}

function pdo_get_instances($pdo, $service_id) {
    $instances = array();
    $mapping = pdo_get_mapping($pdo, $service_id);
    if (empty($mapping)) return false;

    foreach ($mapping as $m) {
        $instance = pdo_get_instance_byid($pdo, $m["instance_id"]);
        if (!$instance) continue;
        $instances[] = $instance;
    }

    return $instances;
}

function pdo_get_mapping_info($pdo, $service_id) {
    $sql = "select service_name, url_regex,
            host_name, host_ip, c.port_num,
            writable, readable, lb_weight, max_fails,
            monitor_status, instance_status
            from service_mapping a
            left join service b on a.service_id = b.service_id
            left join instance c on a.instance_id = c.instance_id
            left join host d on c.host_id = d.host_id
            where a.service_id = ${service_id} AND (c.writable<>0 OR c.readable<>0)";
    return pdo_fetch_all($pdo, $sql);
}
/*
 * 某service_id绑定的readable/writeable的instance
 */
function pdo_get_mapping_info_by_service($pdo, $service_id) {
    $sql = "select c.instance_id as instance_id, d.host_id as host_id, service_name, url_regex,
            host_name, host_ip, c.port_num,
            writable, readable, lb_weight,
            monitor_status, instance_status
            from service_mapping a
            left join service b on a.service_id = b.service_id
            left join instance c on a.instance_id = c.instance_id
            left join host d on c.host_id = d.host_id
            where a.service_id = ${service_id} AND (c.writable<>0 OR c.readable<>0) AND a.is_disabled=0";
    return pdo_fetch_all($pdo, $sql);
}


function pdo_get_master_complex($pdo, $service_id) {
    $sql = "SELECT b.*, c.host_name, c.host_ip
            FROM service_mapping a
            LEFT JOIN instance b ON a.instance_id=b.instance_id
            LEFT JOIN host c ON b.host_id=c.host_id
            WHERE service_id=$service_id AND writable=1 limit 1;";
    return pdo_fetch($pdo, $sql);
}

function insert_instance($pdo,$params){
    $sql="insert into  instance set host_id=?,solr_version=?,port_num=?,use_memory=?";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($params['host_id'],$params['solr_version'],$params['port_num'],$params['use_memory']));
    return $pdo->lastInsertId();
}

function update_instance($pdo,$instance_id,$params){
    $sql="update instance set host_id=?,port_num=? where instance_id=?";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($params['host_id'],$params['port_num'],$instance_id));
    return $sth->rowCount();
}

function update_instance_rw($pdo,$instance_id,$params){
    $sql="update instance set writable=?,readable=? where instance_id=?";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($params['writable'],$params['readable'],$instance_id));
    return $sth->rowCount();
}

function update_instance_lb_weight($pdo,$instance_id,$lb_weight){
    $sql="update instance set lb_weight=? where instance_id=?";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($lb_weight,$instance_id));
    return $sth->rowCount();
}
function update_instance_max_fails($pdo,$instance_id,$max_fails){
    $sql="update instance set max_fails=? where instance_id=?";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($max_fails,$instance_id));
    return $sth->rowCount();
}
function update_instance_use_memory($pdo,$instance_id,$use_memory){
    $sql="update instance set use_memory=? where instance_id=?";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($use_memory,$instance_id));
    return $sth->rowCount();
}

function change_instance_readable($pdo,$instance_id){
    $sql="update instance set readable=1 where instance_id=?";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($instance_id));
    return $sth->rowCount();
}
function change_instance_writable($pdo,$instance_id){
    $sql="update instance set writable=1 where instance_id=?";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($instance_id));
    return $sth->rowCount();
}
function change_instance_unreadable($pdo,$instance_id){
    $sql="update instance set readable=0 where instance_id=?";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($instance_id));
    return $sth->rowCount();
}
function change_instance_unwritable($pdo,$instance_id){
    $sql="update instance set writable=0 where instance_id=?";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($instance_id));
    return $sth->rowCount();
}
function lock_instance($pdo,$instance_id){
    $sql="update instance set is_locked=1 where instance_id = ?";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($instance_id));
    return $sth->rowCount();
}

function add_instance_into_service($pdo,$instance_id,$service_id,$username){
    $service_mapping = pdo_get_mapping_by_service_id($pdo,$service_id);
    if(!$service_mapping){
        $instance_attr['writable'] = 1;
        $instance_attr['readable'] = 1;
        update_instance_rw($pdo,$instance_id,$instance_attr);
        insert_action_queue($pdo,INSTANCE_RECONFIGURE_SCHEMA,$instance_id,$username);
    }else{
        $instance_attr['writable'] = 0;
        $instance_attr['readable'] = 0;
        update_instance_rw($pdo,$instance_id,$instance_attr);
    }
    
    $instance_mapping = pdo_get_mapping_by_instance_id($pdo,$instance_id);
    if (empty($instance_mapping)){
        $mapping_attr['service_id'] = $service_id;
        $mapping_attr['instance_id'] = $instance_id;
        $mapping_attr['updated_by'] = $username;
        insert_service_mapping($pdo,$mapping_attr);        
    } else {
        rebind_service_mapping($pdo,$instance_id,$username);
    }
    
    lock_instance($pdo, $instance_id);
    insert_action_queue($pdo,INSTANCE_RECONFIGURE_SCHEMA,$instance_id,$username);
    insert_action_queue($pdo,INSTANCE_RECONFIGURE_SOLRCONF,$instance_id,$username);
    insert_action_queue($pdo,INSTANCE_START,$instance_id,$username);
}

function remove_instance_from_service($pdo,$instance_id,$username){

    $service_mapping = pdo_get_mapping_by_instance_id($pdo, $instance_id);

    $instance_attr['writable'] = 0;
    $instance_attr['readable'] = 0;
    update_instance_rw($pdo,$instance_id,$instance_attr);

    delete_service_mapping($pdo,$instance_id,$username);
    lock_instance($pdo, $instance_id);
    insert_action_queue($pdo,INSTANCE_STOP,$instance_id,$username);
}

function reload_service_by_instance($pdo,$instance_id,$uname=''){
    global $cfg;
    /*readable/writable被修改，需要修改instance的solrconfig.重启成功后再修改nginx*/
    reload_instance($pdo,$instance_id,$uname);

    $service_mapping = pdo_get_mapping_by_instance_id($pdo, $instance_id);
    lock_service($pdo, $service_mapping['service_id']);
	foreach ($cfg['lb_host'] as $key=>$v){
    	$target_id = $v;
	    insert_action_queue_new($pdo,SERVICE_RECONFIGURE,$service_mapping['service_id'],$target_id,$uname);
	    insert_action_queue_new($pdo,SERVICE_RELOAD,$service_mapping['service_id'],$target_id,$uname);
    }
}

function reload_instance($pdo,$instance_id,$username=''){
    if(lock_instance($pdo,$instance_id)){
        insert_action_queue($pdo,INSTANCE_RECONFIGURE_JETTY,$instance_id,$username);
        insert_action_queue($pdo,INSTANCE_RECONFIGURE_SCHEMA,$instance_id,$username);
        insert_action_queue($pdo,INSTANCE_RECONFIGURE_SOLRCONF,$instance_id,$username);
        insert_action_queue($pdo,INSTANCE_STOP,$instance_id,$username);
        insert_action_queue($pdo,INSTANCE_START,$instance_id,$username);
    }
}
/***************************************************
 * db service_mapping
 **************************************************/

function pdo_get_mapping($pdo, $service_id) {
    $sql = "select * from service_mapping where service_id=?";
    return pdo_fetch_all($pdo, $sql, array($service_id));
}

function pdo_get_mapping_by_instance_id($pdo, $instance_id) {
    $sql = "select * from service_mapping where instance_id=?";
    return pdo_fetch($pdo, $sql, array($instance_id));
}

function pdo_get_mapping_by_service_id($pdo, $service_id) {
    $sql = "select * from service_mapping where service_id=?";
    return pdo_fetch($pdo, $sql, array($service_id));
}

function insert_service_mapping($pdo,$params){
    $sql = "insert into service_mapping set service_id=?,instance_id=?,updated_by=?";
    $sth = $pdo ->prepare($sql);
    $sth->execute(array($params['service_id'],$params['instance_id'],$params['updated_by']));
    return $sth->rowCount();
}

function update_service_mapping($pdo,$instance_id,$params){
    $sql = "update service_mapping set service_id=? where instance_id=?";
    $sth = $pdo ->prepare($sql);
    $sth->execute(array($params['service_id'],$instance_id));
    return $sth->rowCount();
}

function delete_service_mapping($pdo,$instance_id,$username){
    $sql = "update service_mapping set is_disabled=1, updated_by=? where instance_id=?";
    $sth = $pdo ->prepare($sql);
    $sth->execute(array($username,$instance_id));
    return $sth->rowCount();
}

function rebind_service_mapping($pdo,$instance_id,$username){
    $sql = "update service_mapping set is_disabled=0, updated_by=? where instance_id=?";
    $sth = $pdo ->prepare($sql);
    $sth->execute(array($username,$instance_id));
    return $sth->rowCount();
}

/***************************************************
 * db jetty_config
 **************************************************/
function pdo_get_jetty_config($pdo, $instance_id) {
    $sql = "select * from jetty_config where instance_id=?";
    return pdo_fetch($pdo, $sql, array($instance_id));
}

function insert_jetty_config($pdo, $params){
    $config['jetty.port'] = $params['jetty.port'];
    $config = json_encode($config);
    $sql = "insert into  jetty_config set config_json =?,instance_id=?";
    $sth = $pdo->prepare($sql);
    $sth -> execute(array($config,$params['instance_id']));
    return $sth->rowCount();
}

function update_jetty_config($pdo, $instance_id,$params){
    $config['jetty.port'] = $params['jetty.port'];
    $config = json_encode($config);
    $sql = "update jetty_config set config_json = ? where instance_id=?";
    $sth = $pdo->prepare($sql);
    $sth -> execute(array($config,$instance_id));
    return $sth->rowCount();
}

/***************************************************
 * db solr_config
 **************************************************/
function pdo_get_solr_config($pdo, $service_id) {
    $sql = "select * from solr_config where service_id=?";
    return pdo_fetch($pdo, $sql, array($service_id));
}

function _encode_solr_config($params){
    $config['maxDocs'] = $params['maxDocs'];
    $config['maxTime'] = $params['maxTime'];
    $config['pollInterval'] = $params['pollInterval'];
    return  json_encode($config);
}

function insert_solr_config($pdo, $params){
	if($params['config_type'] == 1){
	    $config = _encode_solr_config($params);
	    $sql = "insert into solr_config set config_json = ? , service_id=?";
	    $sth = $pdo->prepare($sql);
	    $sth -> execute(array($config,$params['service_id']));
	    return $sth->rowCount();
	}else{
		$sql = "insert into solr_config set config_json = ? , service_id=?";
		$sth = $pdo->prepare($sql);
		$sth -> execute(array($params['config_json'],$params['service_id']));
		return $sth->rowCount();
	}
}

function update_solr_config($pdo, $service_id,$params){
	if(is_array($params)){
    	$config = _encode_solr_config($params);
	}else{
		$config = $params;
	}
    $sql = "update solr_config set config_json = ? where service_id=?";
    $sth = $pdo->prepare($sql);
    $sth -> execute(array($config,$service_id));
    return $sth->rowCount();
}

/*db solr_dataimport*/
function update_dataimport($pdo,$service_id,$config){
    $sql = "replace into solr_dataimport values(?,?)";
    $sth = $pdo->prepare($sql);
    $sth->execute(array($service_id, $config));
    return $sth->rowCount();
}
function pdo_get_dataimport($pdo, $service_id) {
    $sql = "select dataimport_json from solr_dataimport where service_id=?";
    return pdo_fetch_column($pdo, $sql, array($service_id));
}


/***************************************************
 * db solr_schema
 **************************************************/
function pdo_get_solr_schema($pdo, $service_id) {
    $sql = "select * from solr_schema where service_id=?";
    return pdo_fetch($pdo, $sql, array($service_id));
}

function init_schema($pdo,$service_id){
    $config['uniqueKey'] = '';
    $config['defaultSearchField'] = '';
    $config['defaultOperator'] = "AND";
    $config['fields'] = array();
    $config['dynamicFields'] = array();
    $config = json_encode($config);
    $sql = "insert into  solr_schema set schema_json=? , service_id=?";
    $sth = $pdo->prepare($sql);
    $sth->execute(array($config,$service_id));
    return $sth->rowCount();
}

function update_schema_config($pdo,$service_id,$config){
    $sql = "update solr_schema set schema_json=? where service_id=?";
    $sth = $pdo->prepare($sql);
    $sth->execute(array($config,$service_id));
    return $sth->rowCount();
}

function update_schema($pdo,$service_id,$params){
    $schema_config = pdo_get_solr_schema($pdo, $service_id);
    $service_info = pdo_get_service_byid($pdo, $service_id);
    if($service_info['schema_type'] ==1){
	    $config = json_decode($schema_config['schema_json'],true);
	    $config['uniqueKey'] = $params['uniqueKey'];
	    $config['defaultSearchField'] = $params['defaultSearchField'];
	    $config['defaultOperator'] = $params['defaultOperator'];
	    $config = json_encode($config);
    }else{
    	$config = $params;
    }
    update_schema_config($pdo,$service_id,$config);
}

function get_schema_field($pdo,$service_id,$name){
    $schema_config = pdo_get_solr_schema($pdo, $service_id);
    $config = json_decode($schema_config['schema_json'],true);
    $fields = isset($config['fields'])?$config['fields']:false;
    if($fields){
        foreach($fields as $field){
            if($field['name']==$name){
               $field['is_dynamic_field'] = false;
               return $field;
            }
        }
    }
    $fields = isset($config['dynamicFields'])?$config['dynamicFields']:false;
    if($fields){
        foreach($fields as $field){
            if($field['name']==$name){
                $field['is_dynamic_field'] = true;
               return $field;
            }
        }
    }
    return false;
}

function get_schema_fields_list($pdo,$service_id){
    $schema_config = pdo_get_solr_schema($pdo, $service_id);
    $config = json_decode($schema_config['schema_json'],true);
    $fields = isset($config['fields'])?$config['fields']:array();
    $dynamicFields = isset($config['dynamicFields'])?$config['dynamicFields']:array();
    $res = array_merge($fields,$dynamicFields);
    return $res;
}

function remove_field_from_schema_json($config,$field_name){

    $config_fields = @$config['fields'];
    if($config_fields){
        foreach($config_fields as $k=>$v){
            if($v['name']==$field_name){
               unset($config_fields[$k]);
            }
        }
        $config['fields'] =  array_values($config_fields);
    }

    $config_fields = @$config['dynamicFields'];
    if($config_fields){
        foreach($config_fields as $k=>$v){
            if($v['name']==$field_name){
               unset($config_fields[$k]);
            }
        }
        $config['dynamicFields'] =  array_values($config_fields);
    }

    return $config;
}

function update_schema_fields($pdo,$field,$service_id,$is_dynamic=false){
    $schema_config = pdo_get_solr_schema($pdo, $service_id);
    $config = json_decode($schema_config['schema_json'],true);

    $config = remove_field_from_schema_json($config,$field['name']);
    if($is_dynamic){
        if(!isset($config['dynamicFields']))$config['dynamicFields']=array();
        $config['dynamicFields'][] =  $field;
    }else{
        if(!isset($config['fields']))$config['fields']=array();
        $config['fields'][] =  $field;
    }

    $config = json_encode($config);
    update_schema_config($pdo,$service_id,$config);
}

function delete_schema_fields($pdo,$name,$service_id){
    $schema_config = pdo_get_solr_schema($pdo, $service_id);
    $config = json_decode($schema_config['schema_json'],true);

    $config = remove_field_from_schema_json($config,$name);

    $config = json_encode($config);
    update_schema_config($pdo,$service_id,$config);
}

/***************************************************
 * db schema_type
 **************************************************/
function get_schema_type_list($pdo){
    $sql="select * from schema_type";
    return pdo_fetch_all($pdo, $sql);
}

function get_schema_type_by_name($pdo,$type_name){
    $sql="select * from schema_type where name=?";
    return pdo_fetch($pdo, $sql,array($type_name));
}

/***************************************************
 * db schema_field_options
 **************************************************/
function get_schema_option_by_ids($pdo,$ids){
     $sql="select * from schema_field_options where id in (".implode(',',$ids).")";
     return pdo_fetch_all($pdo, $sql);
}

function get_schema_field_support_options($pdo,$type_name){
    $type = get_schema_type_by_name($pdo,$type_name);
    $support_field_option_ids=explode("|",$type['support_field_options']);
    $option_list = get_schema_option_by_ids($pdo,$support_field_option_ids);
    return $option_list;
}

/***************************************************
 * db action queue
 **************************************************/
function insert_action_queue($pdo,$action_id,$target_id,$uname="",$session_id=""){

    if ($session_id=="" && isset($_COOKIE['queue_session_id'])) {
        $session_id = $_COOKIE['queue_session_id'];
    }

    $sql="insert into action_queue set queue_time=?,queue_status=0,action_id=?,target_id=?,session_id=?,updated_by=?";
    $sth = $pdo->prepare($sql);
    $sth->execute(array(date("Y-m-d H::i:s"),$action_id,$target_id,$session_id,$uname));
    return $sth->rowCount();
}

function insert_action_queue_new($pdo,$action_id,$target_id,$host_id,$uname="",$session_id=""){

    if ($session_id=="" && isset($_COOKIE['queue_session_id'])) {
        $session_id = $_COOKIE['queue_session_id'];
    }

    $sql="insert into action_queue set queue_time=?,queue_status=0,action_id=?,target_id=?,host_id=?,session_id=?,updated_by=?";
    $sth = $pdo->prepare($sql);
    $sth->execute(array(date("Y-m-d H::i:s"),$action_id,$target_id,$host_id,$session_id,$uname));
    return $sth->rowCount();
}

function check_action_queue($pdo,$queue_status,$action_id,$target_id){
    if(!is_array($queue_status)) $queue_status = array($queue_status);

    $in_sql = '';
    $i=0;
    while($i<count($queue_status)){
        $in_sql .= $queue_status[$i];
        $i++;
        if($i<count($queue_status)) $in_sql .= ',';
    }       

    $sql = "select * from action_queue where queue_status in ($in_sql) and action_id=? and target_id=? and queue_time>? limit 1";
    return pdo_fetch_all($pdo, $sql, array($action_id,$target_id,date("Y-m-d H:00:00")));
}

function get_queue_list_complex($pdo, $limit=50, $start=0){
    $sql="SELECT a.*, b.* FROM action_queue a
          LEFT JOIN action b on a.action_id = b.action_id
          ORDER BY queue_id desc
          LIMIT $start, $limit";
    return pdo_fetch_all($pdo, $sql);
}

function get_queue_list_complex2($pdo,$start=0,$limit=50,$params=array()){
    $where = '';
    if(isset($params['service_id']))$str[]="(b.action_type='service' and a.target_id in (".implode(',',$params['service_id']).'))';
    if(isset($params['instance_id']))$str[]="(b.action_type='instance' and a.target_id in (".implode(',',$params['instance_id']).'))';
    if(isset($str))$where = 'WHERE '.implode(' or ',$str);
    $sql="SELECT a.*, b.* FROM action_queue a
          LEFT JOIN action b on a.action_id = b.action_id
          $where
          ORDER BY queue_id desc
          LIMIT $start, $limit";
    return pdo_fetch_all($pdo, $sql);
}

function confirm_action_queue($pdo){
    $sql="update action_queue set queue_status=0 where queue_status=-1 and session_id=?";
    $sth = $pdo->prepare($sql);
    $sth->execute(array($_COOKIE['queue_session_id']));
    return $sth->rowCount();
}

function pdo_get_last_nginx_reload($pdo) {
    $sql="select * from action_queue where queue_status=1 and action_id=1 order by queue_id desc limit 1;";
    $sth=$pdo->prepare($sql);
    $sth->execute();
    return pdo_fetch($pdo, $sql);
}

/***************************************************
 * db alert info
**************************************************/
function insert_alert_info($pdo, $info) {
    $sql="insert into alert_info set username=?,target_type=?,target_id=?,alert_type_id=?,alert_type_name=?,alert_setting=?,is_disabled=0,is_deleted=0";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($info['username'],$info['target_type'],$info['target_id'],$info['alert_type_id'],$info['alert_type_name'],$info['alert_setting']));
    return $pdo->lastInsertId();
}

function pdo_get_alert_info_list($pdo, $username) {
    $sql="select * from alert_info where username=? and is_deleted = 0 order by alert_id";
    return pdo_fetch_all($pdo, $sql, array($username));
}

function pdo_get_alert_by_id($pdo, $alert_id) {
    $sql = "select * from alert_info where alert_id=?";
    return pdo_fetch($pdo, $sql, array($alert_id));
}

function update_alert_info($pdo, $alert_id, $alert_str){
    $sql = "update alert_info set alert_setting=? where alert_id=?";
    $sth = $pdo ->prepare($sql);
    $sth->execute(array($alert_str,$alert_id));
    return $sth->rowCount();
}

function able_alert_info($pdo, $alert_id){
    $sql = "update alert_info set is_disabled=0 where alert_id=?";
    $sth = $pdo ->prepare($sql);
    $sth->execute(array($alert_id));
    return $sth->rowCount();
}

function disable_alert_info($pdo, $alert_id){
    $sql = "update alert_info set is_disabled=1 where alert_id=?";
    $sth = $pdo ->prepare($sql);
    $sth->execute(array($alert_id));
    return $sth->rowCount();
}

function delete_alert_info($pdo, $alert_id){  
    $sql = "update alert_info set is_deleted=1 where alert_id=?";
    $sth = $pdo ->prepare($sql);
    $sth->execute(array($alert_id));
    return $sth->rowCount();
}

/***************************************************
 * db user setting
**************************************************/
function pdo_get_user_setting($pdo, $username) {
    $sql="select * from user_setting where username=?";
    return pdo_fetch($pdo, $sql, array($username));
}

function default_user_setting($pdo, $uname) {
    $sql="insert into user_setting set username=?";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($uname));
    return $pdo->lastInsertId();  
}

function update_user_setting($pdo,$username,$input){
    $sql="update user_setting set mobile=?, email=? where username=?";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($input['mobile'],$input['email'],$username));
    return $sth->rowCount();
}

function update_report_setting($pdo,$username,$setting){
    $sql="update user_setting set report_setting=? where username=?";
    $sth = $pdo->prepare($sql);
    $sth ->execute(array($setting,$username));
    return $sth->rowCount();
}

/***************************************************
 * log_service_access
 **************************************************/
function pdo_get_service_access($pdo, $service_id, $limit=25, $start=0) {
    $sql="select * from log_service_access where service_id=? order by log_time desc limit $start, $limit";
    return pdo_fetch_all($pdo, $sql, array($service_id));
}

function pdo_get_cloud_access($pdo, $limint=25, $start=0) {
    $sql="select log_time, sum(num_updates) as num_updates, sum(num_selects) as num_selects from log_service_access group by log_time desc limit $start, $limint;";
    return pdo_fetch_all($pdo, $sql);
}

/***************************************************
 * current status
 **************************************************/
function get_current_status($pdo,$status_type,$status_name){
	$sql="select * from current_status where status_type=? and status_name=?";
	return pdo_fetch_all($pdo, $sql, array($status_type,$status_name));
}

function add_current_status($pdo,$type,$name,$target_id,$value,$time){
    $sql="replace into current_status set status_type=?,status_name=?,target_id=?,status_value=?,status_time=?";
    $sth = $pdo->prepare($sql);
    $sth->execute(array($type,$name,$target_id,$value,$time));
    return $sth->rowCount();
}

/***************************************************
 * job scheduler
 **************************************************/
function get_job_list($pdo){
	$sql="select * from job_scheduler where status=1 order by plan_run_order asc";
	return pdo_fetch_all($pdo, $sql);
}

function upd_job_run_time($pdo,$job_id,$time){
	$sql="update job_scheduler set last_run_time=? where job_id=?";
	$sth = $pdo->prepare($sql);
    $sth->execute(array($time,$job_id));
    return $sth->rowCount();
}


/***************************************************
 * log_service_access
 **************************************************/
function get_service_speed_log($pdo_log,$idc,$service_id,$limit=60){
	$sql="select * from log_service_speed where service_id=? and idc=? order by log_time desc limit ".$limit;
	return pdo_fetch_all($pdo_log, $sql, array($service_id,$idc));
}


function get_status_log($pdo_log,$log_idc,$log_host,$log_type,$log_name,$target_id,$table,$limit, $times=false){
    $logs = array();

    if(!is_array($log_host)) $log_host = array($log_host);
    $where  = '';
    if($times){
        if(isset($times['min'])) $where .= " AND log_time>'".$times['min']."'";
        if(isset($times['max'])) $where .= " AND log_time<'".$times['max']."'";
    }  
    $order = ($limit)?" order by log_time desc limit $limit":'';

    foreach($log_host as $h){ 
        $sql="select * from ${table} where log_name=? and target_id=? ".$where.$order;
        $ret = pdo_fetch_all($pdo_log, $sql, array($log_name,$target_id));
        //echo "select * from ${table} where log_name='${log_name}' and target_id=${target_id} ".$where.$order;
        if($ret) $logs=array_merge($logs,$ret);
    }

    return $logs;
}

function add_status_log($pdo_log,$log_idc,$log_host,$log_type,$log_name,$target_id,$log_value,$log_time,$table){
    $sql = "replace into ${table} set log_idc=?,log_host=?,log_type=?,log_name=?,target_id=?,log_value=?,log_time=?";
    $sth = $pdo_log->prepare($sql);
    $sth->execute(array($log_idc,$log_host,$log_type,$log_name,$target_id,$log_value,$log_time));
    return $sth->rowCount();
}

function add_status_current($pdo_log,$log_type,$log_name,$target_id,$log_value,$log_time){
    $sql = "replace into status_current set log_type=?,log_name=?,target_id=?,log_value=?,log_time=?";
    $sth = $pdo_log->prepare($sql);
    $sth->execute(array($log_type,$log_name,$target_id,$log_value,$log_time));
    return $sth->rowCount();
}
function delete_status_current($pdo_log,$log_type,$log_name){
    $sql = "delete from status_current where log_type=? and log_name=?";
    $sth = $pdo_log->prepare($sql);
    return $sth->execute(array($log_type,$log_name));
}

/***************************************************
 * common functions
 **************************************************/
function out_location($service, $path="update", $proxy=false) {
    global $cfg;
    $fix = '';
    if($path == "select"){/*select时禁用分布式*/
        $fix = '?distrib=false';
    }
    $str  = "location %s%s {\n";
    $str .= "    access_log ".$cfg['access_log_path']."/access.log gzip;\n";
    $str .= "    rewrite    %s%s(.*)$ /solr/%s$1".$fix." break;\n";
    $str .= "    proxy_pass http://%s-%s;\n";
    $str .= "}\n";
    if(!$proxy){
        $proxy = $path;
    }
    return sprintf(
            $str,
            $service["url_regex"],
            $path,
            $service["url_regex"],
            $path,
            $path,
            $service["service_name"],
            $proxy
    );
}

/**
 * out_location_admin currently only fileschema can be read
 * 
 * @param mixed $service 
 * @param string $path 
 * @access public
 * @return void
 */
function out_location_admin($service) {
    global $cfg;
    $str  = "location %sadmin {\n";
    $str .= "    access_log ".$cfg['access_log_path']."/access.log gzip;\n";
    $str .= "    rewrite    %s(.*)$ /solr/$1 break;\n";
    //$str .= "    rewrite    %s(.*)$ /solr/$1 last;\n";
    $str .= "    proxy_pass http://%s-update;\n";
    $str .= "}\n";
    return sprintf(
            $str,
            $service["url_regex"],
            $service["url_regex"],
            $service["service_name"]
    );   
}

function out_upstream($instances, $type, $hash_type) {
    $service_name = $instances[0]["service_name"];
    $type=="writable"?$path="update" : $path="select";
    $str = "upstream $service_name-$path {\n";

    if ($hash_type == 1) {
        //$str .= '    consistent_hash $request_uri;'."\n";
    }

    foreach ($instances as $i) {
        if ($i[$type] != 1) continue;
        if ($hash_type == 1) {
            $str .= sprintf("    server %s:%s;\n", $i["host_ip"], $i["port_num"]);
        } else {
            $str .= sprintf("    server %s:%s %s %s;\n", $i["host_ip"], $i["port_num"], $i["lb_weight"], $i['max_fails']);
        }
    }
    $str .= "}\n";
    return $str;
}

function build_path_jettyconfig($solr_root, $port_num) {
    return $solr_root . "/cloud/" . $port_num . "/server/etc/solr.xml";
}

function build_path_configtpl($solr_root, $port_num) {
    return $solr_root . "/cloud/" . $port_num . "/idx/conf";
}
function build_path_solrconfig($solr_root, $port_num) {
    return $solr_root . "/cloud/" . $port_num . "/idx/conf/solrconfig.xml";
}

function build_path_solrschema($solr_root, $port_num) {
    return $solr_root . "/cloud/" . $port_num . "/idx/conf/schema.xml";
}

function build_url_replication($host, $port) {
    return sprintf("http://%s:%s/solr/replication", $host, $port);
}

function build_url_instance($host,$port_num){
    $url = "http://%s:%s/solr/select/?q=*:*";
    return sprintf($url,$host,$port_num);
}

function build_str_interval($sec) {
    return "00:00:" . build_str_fixwidth($sec);
}

function build_str_fixwidth($str, $l=2, $p="0") {
    $sl = strlen((string)$str);
    if ($sl < $l) {
        $str = str_repeat($p, $l-$sl) . $str;
    }
    return $str;
}

function msg_redirect($url,$msg=""){
    header("Content-type: text/html; charset=utf-8");
    $script="<script>";
    if($msg){
        $script.="alert('".$msg."');";
    }
    if($url=='back'){
        $script.= "history.go(-1);";
    }else{
        $script.="window.location ='".$url."';";
    }
    $script.="</script>";
    echo $script;exit;
}

function get_params(){
    $params = array_merge($_GET,$_POST);
    foreach($params as $params_key=>$params_value){
        $params[$params_key]=is_array($params_value)?$params_value:trim($params_value);
    }
    return $params;
}

function rand_string($len=6,$type='',$addChars='') {
    $str ='';
    switch($type) {
        case 0:
            $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.$addChars;
            break;
        case 1:
            $chars= str_repeat('0123456789',3);
            break;
        case 2:
            $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZ'.$addChars;
            break;
        case 3:
            $chars='abcdefghijklmnopqrstuvwxyz'.$addChars;
            break;
        default :
            // 默认去掉了容易混淆的字符oOLl和数字01，要添加请使用addChars参数
            $chars='ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789'.$addChars;
            break;
    }
    if($len>10 ) {//位数过长重复字符串一定次数
        $chars= $type==1? str_repeat($chars,$len) : str_repeat($chars,5);
    }
    $chars   =   str_shuffle($chars);
    $str     =   substr($chars,0,$len);
    return $str;
}

function change_array_key($array,$key){
	if(!$array)return array();
    foreach($array as $v){
        $array_tmp[$v[$key]]=$v;
    }
    return $array_tmp;
}

function p(){
    echo "<pre>";
    foreach(func_get_args() as $v){
        print_r($v);
    }
    echo "</pre>";
}

function pe(){
    foreach(func_get_args() as $v){
        p($v);
    }
    exit;
}

function build_line_chart_lc($data, $level=200, $size="320x125", $chco="224499") {
    foreach ($data as $i => $a) {
        $raw[$i] = $a[1];

        if ($i%2 == 0) {
            $chxl[0][$i] = $a[0];
        } else {
            $chxl[0][$i] = "";
        }
    }

    $max = max($raw);
    $min = min($raw);

    $roof = $max + $level - ($max % $level);
    $floor = $min - ($min % $level);
    $height = $roof - $floor;

    foreach ($raw as $i => $d) {
        $chd[$i] = intval(($d - $floor) / $height * 100);
    }

    $step = 4;
    $intval = $height / $step;
    for ($i=0;$i<$step+1;$i++) {
        if ($i == 0) {
            $chxl[1][$i] = "";
        } else {
            $chxl[1][$i] = $i * $intval + $floor;
        }
    }

    $p["cht"]  = "lc";
    $p["chs"]  = $size;
    $p["chco"] = $chco;
    $p["chxt"] = "x,y";
    $p["chxl"] = "0:|" . implode("|", $chxl[0]);
    $p["chxl"].= "|1:|" . implode("|", $chxl[1]);
    $p["chd"]  = "t:" . implode(",", $chd);
    $p["chm"]  = "B,EEEEEE,0,0,0";

    $url = GOOGLE_CHART;
    foreach ($p as $k => $v) {
        $url .= $k . "=" . urlencode($v) . "&";
        #$url .= $k . "=" . $v . "&";
    }

    $url = substr($url, 0, -1);

    return $url;
}

function do_call($host, $port, $uri, $request) {
    $fp = fsockopen($host, $port, $errno, $errstr);
    $query = "POST $uri HTTP/1.0\nHost: $host\nContent-Type: text/xml\nContent-Length: ".strlen($request)."\n\n$request\n";

    if (!fputs($fp, $query, strlen($query))) {
        return false;
    }

    $contents = '';
    while (!feof($fp)) {
        $contents .= fgets($fp);
    }

    fclose($fp);
    return $contents;
}

function extract_xml($content) {
    $s = strstr($content, '<');
    return $s;
}

function cat_confd($path, $ptn=null) {
    if (! is_dir($path)) return "";
    if (! $dh = opendir($path)) return "";

    $content = "";
    while (($file = readdir($dh)) !== false) {

        if (!empty($ptn) && !preg_match($ptn, $file)) {
            continue;
        }

        if (preg_match("/\.conf$/", $file)) {
            $content .= file_get_contents($path . "/" . $file);
        }
    }
    closedir($dh);

    return $content;
}

/**
 * $array['title']
 * $array['legend_y']
 * $array['legend_x']
 * $array['values']
 * $array['values_key']
 * $array['range_max']
 * $array['range_step']
 * @param $array
 * @return unknown_type
 */
function create_chart_data($array){
	if(!$array) return;

	require_once('OFC/OFC_Chart.php');
    $chart = new OFC_Chart();
    $chart -> set_bg_colour('#ffffff');

    $title = new OFC_Elements_Title( $array['title'] );
	$title -> set_style('{color: #567300; font-size: 16px; font-weight:bold;}');
	$chart->set_title( $title );

	$yl = new OFC_Elements_Legend_Y( $array['legend_y'] );
	$yl -> set_style('{font-size:18px;}');
	$chart->set_y_legend($yl);

	$xl = new OFC_Elements_Legend_X($array['legend_x']);
	$xl -> set_style('{font-size:18px;color:#Ff0}');
	$chart->set_x_legend($xl);

	$elements=array();
	$colors=array('','#CC00AA','#9C48F0','#b0de09','#0d8ecf','#ff6600','#fcd202','#E2EBFF','#AAAAAA');

	foreach($array['values'] as $k=>$v){
		ksort($v,SORT_STRING);
		$line = new OFC_Charts_Line();
		$line->set_key( $array['values_key'][$k], 12 );
		$colors[$k]?$line->set_colour($colors[$k]):'';
		$line->set_values( array_values($v) );
		$default_dot = new OFC_Charts_Line_Dot();
    	$default_dot -> tooltip('#x_label#<br>#val#');
    	$line->set_default_dot_style($default_dot);
    	$elements[]=$line;
    	$array['values'][$k]=&$v;
	}

	foreach($elements as $element){
		$chart->add_element( $element );
	}

	$x = new OFC_Elements_Axis_X();
	$x->colour = '#909090';
	$x_axis_labels = new OFC_Elements_Axis_X_Label_Set();
	$x->set_steps($array['show_step']);
	$x_axis_labels->set_steps($array['show_step']);

	if(is_array($array['values'][0])) $keys=array_keys($array['values'][0]);
	else $keys=array_keys($array['values']);
	$x_axis_labels->set_labels( $keys );
	$x_axis_labels->set_size(12);
	$x_axis_labels->set_colour('#Ff0');
	$x_axis_labels->set_rotate('-45');
	$x->set_labels( $x_axis_labels );
	$chart->set_x_axis( $x );

	$y = new OFC_Elements_Axis_Y();
	$range_min=isset($array['range_min'])?$array['range_min']:0;
	$y ->set_range( $range_min, $array['range_max'], $array['range_step'] );
	$chart->set_y_axis( $y );

	return $chart->toPrettyString();
}

function build_chart_data($logs,$deadline_value=100){
	if(!$logs){
		$res['max']=0;
		$res['min']=0;
		$res['data']=array();
		$res['deadline']=array();
	}else{
		$max = 0;
		$min = 999999999;
		foreach($logs as $v){
                    $key = substr($v['log_time'],5,11);
                    if(!isset($data[$key])){
                        $data[$key] = 0.0;
                    }
                    $data[$key] += (float)$v['log_value'];
		    $deadline[$key]=$deadline_value;
			
		    $max = max($max,$data[$key]);
		    $min = min($min,$data[$key]);
		}
		if(!$deadline_value){
			$res['max']=$max;
		}else{
			$res['max']=max($max,$deadline_value);
		}
		$res['min']=$min;
                $res['data']=$data;
                $res['deadline']=$deadline;
	}	
	return $res;
}

function curl_get_content($url){
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_HTTPHEADER,array("Content-type:text/xml; charset=utf-8"));
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,20);
	curl_setopt($ch,CURLOPT_TIMEOUT,20);
	curl_setopt($ch,CURLOPT_URL,$url);
	$res = curl_exec($ch);
	return $res;
}

/***************************************************
 * oauth_login
**************************************************/
function check_login($req_url) {
    global $cfg;
    $cookiename = $cfg['AuthCookieName'];
    $cookie = @$_COOKIE[$cookiename];
    if ($cookie) {
        $name_token_array = is_auth($cookie);
        if ($name_token_array) {
            $username = urldecode($name_token_array[1]);
            return $username;
        }
    } else {
        login_oauth($req_url);
    }
}

function is_auth($cookie) {
    
    if ($cookie) {
        $cookieStr = $cookie;
        $cookieArr = explode("\t", $cookieStr);
        @list($username, $chinese_name, $token, $cookietime) = $cookieArr;
        $ret = array($username, $chinese_name, $token);
        return $ret;
    }
}

function login_oauth($custom_url) {
    
    global $cfg;
    $oauth_config = $cfg['oauth'];
    $client_id = $oauth_config['client_id'];
    $client_secret = $oauth_config['client_secret'];
    $oauth_url = $oauth_config['oauth_url'];
    $user = login_with_oauth($client_id, $client_secret, $oauth_url, $custom_url);
    return $user;
}

function get_username() {
    global $cfg;
    $cookiename = $cfg['AuthCookieName'];
    $cookie = @$_COOKIE[$cookiename];
    if ($cookie) {
        $name_token_array = is_auth($cookie);
        if ($name_token_array) {
            $username = urldecode($name_token_array[0]);
            return $username;
        }
    } else {
        return "";
    }
}

function get_token() {
    global $cfg;
    $cookiename = $cfg['AuthCookieName'];
    $cookie = @$_COOKIE[$cookiename];
    if ($cookie) {
        $name_token_array = is_auth($cookie);
        if ($name_token_array) {
            $token = urldecode($name_token_array[2]);
            return $token;
        }
    } else {
        return "";
    }
}

/**
 * 调用oauth登录
 * return json格式的用户基本信息
 */
function login_with_oauth($client_id, $client_secret, $oauth_url, $custom_url) {
    
    if (isset($_REQUEST['access_token']) && $_REQUEST['access_token']) {
        /*3、用AccessToken,获取info*/
        $access_token = $_REQUEST['access_token'];
        $data = array(
                "oauth_token" => $access_token,
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $oauth_url."/resource.php");
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $info = curl_exec($ch);
        //var_dump('!!!!!!!',$info);exit;
        if ($info) return $info;
        else return false;
        exit();
    }
    
    /*1、获取临时令牌RequestToken*/
    $array = array(
            "client_id" => $client_id,
            "response_type" => "code",/*默认*/
            "curl" => true,/*使用curl还是使用redirect*/
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $oauth_url."/authorize.php");
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($array));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    $info = json_decode(curl_exec($ch),true);
    
    if (isset($info['code']) && $info['code']) {
        /*2、用临时令牌，申请访问令牌*/
        $data = array(
                "client_id" => $client_id,
                "client_secret" => $client_secret,
                "grant_type" => 'authorization_code',/*默认*/
                "code" => $info['code'],/*临时令牌*/
                "custom" => $custom_url,
        );
        
        header("HTTP/1.1 302 Found");
        header("Location: " . $oauth_url.'/token.php?'.http_build_query($data));
        exit();
    }
}

/**
 * 用户注册流程，
 * 用$access_token到oauth获取用户详细信息
 */
function get_info_from_oauth($access_token, $oauth_url) {

    $data = array(
            "oauth_token" => $access_token,
            "getinfo" => true,
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $oauth_url."/resource.php");
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    $info = curl_exec($ch);
    if($info) return $info;
    else return false;
}

/**
 * 版本号获得cloud目录的对应关系
 * */
function get_version_dir($solr_version) {
    global $cfg;
    $dir = $cfg['solr_version'][$solr_version]['dir'];
    if(!$dir) $dir = false;

    return $dir;
}

function get_host_name($pdo, $host_id){
    $sql="select host_name from host where host_id=${host_id}";
    return pdo_fetch_column($pdo, $sql);
}
function set_zk($pdo, $service_id, $zk){
    $sql="select solr_version from service where service_id=${service_id}";
    $solr_version = pdo_fetch_column($pdo, $sql);
    if($solr_version>3 && $zk){
        $sql="update service set zk='${zk}' where service_id=${service_id}";
	$sth = $pdo->prepare($sql);
        $sth->execute(array());
        return $sth->rowCount();
    }
    return false;
}
function set_mode($pdo, $service_id, $zk){
    $sql="select solr_version from service where service_id=${service_id}";
    $solr_version = pdo_fetch_column($pdo, $sql);
    if($solr_version>3){
        $sql="update service set zk='${zk}' where service_id=${service_id}";
		$sth = $pdo->prepare($sql);
        $sth->execute(array());
        return $sth->rowCount();
    }
    return false;
}
function isset_zk_service($pdo, $service_id) {
    $sql="select zk from service where service_id=${service_id}";
    $zk = pdo_fetch_column($pdo, $sql);
    if($zk){
        return $zk;
    }
    return false;
}
function isset_zk_instance($pdo, $instance_id) {
    $service_id = get_service_id_by_instance_id($pdo, $instance_id);
    if($service_id){
        return isset_zk_service($pdo, $service_id);
    }
    return false;
}
function get_service_id_by_instance_id($pdo, $instance_id){
    $sql = "select service_id from service_mapping where instance_id=$instance_id";
    return pdo_fetch_column($pdo, $sql);
}

function get_custom_alert_list($pdo){
    $sql='SELECT * FROM alert_info WHERE is_disabled=0 AND is_deleted=0';
    return pdo_fetch_all($pdo, $sql);
}
function get_chartdata($pdo_log, $table, $field, $target_id, $start, $end=false){
    $sql = "select * from ${table} where log_name=? and target_id=? and log_time>?";
    if(!empty($end)) $sql .=" and log_time<'$end'";
    return pdo_fetch_all($pdo_log, $sql, array($field, $target_id, $start));
}
function get_user_info($pdo,$name){
    $sql = "select * from user_setting where username='${name}' limit 1";
    return pdo_fetch($pdo, $sql);
}

/*solrcloud_log_db: slowquery*/
function select_slowquery($pdo_log, $datetime, $service=false){
    if(!empty($service)) $service = " and service='$service' ";
    else $service = '';
    $sql = "select * from slowquery where log_time=?".$service." order by count desc limit 20";
    return pdo_fetch_all($pdo_log, $sql, array($datetime));
}
function insert_slowquery($pdo_log,$service, $url, $avg, $count, $datetime){
    $sql = "insert into slowquery set service=?,url=?,avg=?,count=?,log_time=?";
    $sth = $pdo_log->prepare($sql);
    $sth->execute(array($service, $url, $avg, $count, $datetime));
    return $sth->rowCount();
}
function delete_slowquery($pdo_log, $datetime){
    $sql = "delete from slowquery where log_time=?";
    $sth = $pdo_log->prepare($sql);
    return $sth->execute(array($datetime));
}
function delete_old_slowquery($pdo_log){
    $old_date = date('Y-m-d',strtotime('-10 day'));
    $sql = "delete from slowquery where log_time<?";
    $sth = $pdo_log->prepare($sql);
    return $sth->execute(array($old_date));
}
function select_frequentquery($pdo_log, $datetime, $service=false){
    if(!empty($service)) $service = " and service='$service' ";
    else $service = '';
    $sql = "select * from frequentquery where log_time=?".$service." order by count desc limit 20";
    return pdo_fetch_all($pdo_log, $sql, array($datetime));
}
function insert_frequentquery($pdo_log,$service, $url, $avg, $count, $datetime){
    $sql = "insert into frequentquery set service=?,url=?,avg=?,count=?,log_time=?";
    $sth = $pdo_log->prepare($sql);
    $sth->execute(array($service, $url, $avg, $count, $datetime));
    return $sth->rowCount();
}
function delete_frequentquery($pdo_log, $datetime){
    $sql = "delete from frequentquery where log_time=?";
    $sth = $pdo_log->prepare($sql);
    return $sth->execute(array($datetime));
}
function delete_old_frequentquery($pdo_log){
    $old_date = date('Y-m-d',strtotime('-10 day'));
    $sql = "delete from frequentquery where log_time<?";
    $sth = $pdo_log->prepare($sql);
    return $sth->execute(array($old_date));
}
