<?php
/*
 * 监控报警配置
 * hwm上限，keeptime持续时间
 * percent波动百分比，period一段时间内的波动百分比
 * 
 * 注意：
 * 当存在hwm&&keeptime 监控 一段时间一直>=hwm
 * 当存在lwm&&keeptime 监控 一段时间一直>=hwm
 * 当存在percent&&period 监控 与昨天此时相同的时间段period对比平均数 
 * 由于5min采样一次，所以实际报警时当参数keeptime和period不满足5min时，取5min
 *
 * 具体default值还需要审核下，暂时先这么用
 * by Jessie
 * */
$cfg['alert'] = array(
    '11' => array(
        'name'=>'CPUload',
        'target_type'=>'host',
        'default'=>array('hwm'=>'15','lwm'=>'0','keeptime'=>'10'),
        'table'=>'status_log_host_loadaverage',
        'field'=>'loadaverage',
    ),
    '12' => array(
        'name'=>'机器内存',
        'target_type'=>'host',
        'default'=>array('hwm'=>'28959','lwm'=>'0','keeptime'=>'10'),
        'table'=>'status_log_host_memoryused',
        'field'=>'memoryused',
    ),
    '13' => array(
        'name'=>'Instance的Select数',
        'target_type'=>'host-instance',
        'default'=>array(),
        'table'=>'status_log_instance_access',
        'field'=>'select',
    ),
    '14' => array(
        'name'=>'Instance的90%查询时间',
        'target_type'=>'host-instance',
        'default'=>array(),
        'table'=>'status_log_instance_access',
        'field'=>'millisecond_90',
    ),
    
    '21' => array(
        'name'=>'Select数',
        'target_type'=>'service',
        'default'=>array('percent'=>'30','period'=>'30'),
        'table'=>'status_log_service_access',
        'field'=>'select',
    ),
    '22' => array(
        'name'=>'Update数',
        'target_type'=>'service',
		'default'=>array('percent'=>'50','period'=>'30'),
        'table'=>'status_log_service_access',
        'field'=>'update',
    ),
    '23' => array(
        'name'=>'90%访问时间',
        'target_type'=>'service',
        'default'=>array('hwm'=>'500','lwm'=>'0','keeptime'=>'5','percent'=>'50','period'=>'30'),
        'table'=>'status_log_service_calculate',
        'field'=>'millisecond_90',
    ),
    '24' => array(
        'name'=>'大于100ms查询数',
        'target_type'=>'service',
        'default'=>array('percent'=>'50','period'=>'30'),
        'table'=>'status_log_service_calculate',
        'field'=>'percent_100',
    ),
    '25' => array(
        'name'=>'文档数',
        'target_type'=>'service',
        'default'=>array('percent'=>'20','period'=>'30'),
        'table'=>'status_log_service_docnumber',
        'field'=>'docnumber',
    ),
    '31' => array(
        'name'=>'索引大小',
        'target_type'=>'instance',
        'default'=>array(),
        'table'=>'status_log_service_indexsize',
        'field'=>'indexsize',
    ),
    '32' => array(
        'name'=>'Instance的Select数',
        'target_type'=>'instance',
        'default'=>array('percent'=>'30','period'=>'30'),
        'table'=>'status_log_instance_access',
        'field'=>'select',
    ),
    '33' => array(
        'name'=>'Instance的90%访问时间',
        'target_type'=>'instance',
        'default'=>array('hwm'=>'500','lwm'=>'0','keeptime'=>'5','percent'=>'50','period'=>'30'),
        'table'=>'status_log_instance_access',
        'field'=>'millisecond_90',
    ),
    '34' => array(
        'name'=>'JVM内存',
        'target_type'=>'instance',
        'default'=>array('hwm'=>'80','lwm'=>'50','keeptime'=>'10'),
        'table'=>'status_log_instance_jvmmemory',
        'field'=>'jvmmem',
    ),
    '35' => array(
        'name'=>'queryResultCache命中率',
        'target_type'=>'instance',
        'default'=>array(),
        'table'=>'status_log_instance_hits',
        'field'=>'queryResultCache',
    ), 
    '36' => array(
        'name'=>'fieldCache命中率',
        'target_type'=>'instance',
        'default'=>array(),
        'table'=>'status_log_instance_hits',
        'field'=>'fieldValueCache',
    ), 
    '37' => array(
        'name'=>'documentCache命中率',
        'target_type'=>'instance',
        'default'=>array(),
        'table'=>'status_log_instance_hits',
        'field'=>'documentCache',
    ), 
    '38' => array(
        'name'=>'fieldValueCache命中率',
        'target_type'=>'instance',
        'default'=>array(),
        'table'=>'status_log_instance_hits',
        'field'=>'fieldValueCache',
    ), 
    '39' => array(
        'name'=>'filterCache命中率',
        'target_type'=>'instance',
        'default'=>array(),
        'table'=>'status_log_instance_hits',
        'field'=>'filterCache',
    ), 
    '41' => array(
        'name'=>'全站文档数',
        'target_type'=>'global',
        'table'=>'status_log_global',
        'field'=>'docnumber',
    ),
   '42' => array(
        'name'=>'全站Select数',
        'target_type'=>'global',
        'table'=>'status_log_global',
        'field'=>'select',
    ),
   '43' => array(
        'name'=>'全站Update数',
        'target_type'=>'global',
        'table'=>'status_log_global',
        'field'=>'update',
    ),
);

$cfg['smtp']['smtpserver'] = '10.11.6.150';
$cfg['smtp']['smtpserverport'] = 25;
$cfg['smtp']['smtpuser'] = '';
$cfg['smtp']['smtppass'] = '';
$cfg['smtp']['smtp_usermail'] = 'solr@sc10-001.a.ajkdns.com';
